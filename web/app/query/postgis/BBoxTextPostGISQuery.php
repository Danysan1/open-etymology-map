<?php

namespace App\Query\PostGIS;

require_once(__DIR__ . "/../../BoundingBox.php");
require_once(__DIR__ . "/../../BaseStringSet.php");
require_once(__DIR__ . "/../../ServerTiming.php");
require_once(__DIR__ . "/../BBoxQuery.php");
require_once(__DIR__ . "/BBoxPostGISQuery.php");
require_once(__DIR__ . "/../wikidata/EtymologyIDListJSONWikidataQuery.php");

use \PDO;
use \App\BoundingBox;
use \App\BaseStringSet;
use \App\ServerTiming;
use \App\Query\PostGIS\BBoxPostGISQuery;
use \App\Query\Wikidata\EtymologyIDListJSONWikidataQuery;

abstract class BBoxTextPostGISQuery extends BBoxPostGISQuery
{
    /**
     * @var string $language
     */
    private $language;

    /**
     * @var string $wikidataEndpointURL
     */
    private $wikidataEndpointURL;

    /**
     * @param BoundingBox $bbox
     * @param string $language
     * @param PDO $db
     * @param string $wikidataEndpointURL
     * @param ServerTiming|null $serverTiming
     */
    public function __construct($bbox, $language, $db, $wikidataEndpointURL, $serverTiming = null)
    {
        parent::__construct($bbox, $db, $serverTiming);
        $this->language = $language;
        $this->wikidataEndpointURL = $wikidataEndpointURL;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getWikidataEndpointURL(): string
    {
        return $this->wikidataEndpointURL;
    }

    /**
     * @return void
     */
    protected function downloadMissingText()
    {
        $queryParams = [
            "min_lon" => $this->getBBox()->getMinLon(),
            "max_lon" => $this->getBBox()->getMaxLon(),
            "min_lat" => $this->getBBox()->getMinLat(),
            "max_lat" => $this->getBBox()->getMaxLat(),
            "lang" => $this->getLanguage(),
        ];

        $sthMissingWikidata = $this->getDB()->prepare(
            "WITH wikidata_etymology AS (
                    SELECT DISTINCT wd_id, wd_wikidata_cod, wd_gender_id, wd_instance_id
                    FROM etymology
                    JOIN wikidata ON et_wd_id = wd_id
                    JOIN element ON et_el_id = el_id
                    WHERE el_geometry @ ST_MakeEnvelope(:min_lon, :min_lat, :max_lon, :max_lat, 4326)
                )
            SELECT wd_wikidata_cod
            FROM wikidata_etymology
            WHERE wd_id NOT IN (SELECT wdt_wd_id FROM wikidata_text WHERE wdt_language = :lang)"
        );
        $sthMissingWikidata->execute($queryParams);
        if ($this->getServerTiming() != null)
            $this->getServerTiming()->add("missing-wikidata-query");

        $missingWikidataText = $sthMissingWikidata->fetchAll(PDO::FETCH_NUM);
        if (!empty($missingWikidataText)) {
            //error_log("missingWikidataText=" . json_encode($missingWikidataText));
            $searchArray = array_column($missingWikidataText, 0);
            $searchSet = new BaseStringSet($searchArray);
            $wikidataQuery = new EtymologyIDListJSONWikidataQuery(
                $searchSet,
                $this->language,
                $this->wikidataEndpointURL
            );
            $wikidataResult = $wikidataQuery->send();
            if ($this->getServerTiming() != null)
                $this->getServerTiming()->add("wikidata-text-download");

            //error_log("wikidataResult=$wikidataResult");

            $stInsertGender = $this->getDB()->prepare(
                "INSERT INTO wikidata (wd_wikidata_cod)
                SELECT DISTINCT REPLACE(value->'genderID'->>'value', 'http://www.wikidata.org/entity/', '')
                FROM json_array_elements((:result::JSON)->'results'->'bindings')
                LEFT JOIN wikidata ON wd_wikidata_cod = REPLACE(value->'genderID'->>'value', 'http://www.wikidata.org/entity/', '')
                WHERE LEFT(value->'genderID'->>'value', 31) = 'http://www.wikidata.org/entity/'
                AND wd_id IS NULL
                UNION
                SELECT DISTINCT REPLACE(value->'instanceID'->>'value', 'http://www.wikidata.org/entity/', '')
                FROM json_array_elements((:result::JSON)->'results'->'bindings')
                LEFT JOIN wikidata ON wd_wikidata_cod = REPLACE(value->'instanceID'->>'value', 'http://www.wikidata.org/entity/', '')
                WHERE LEFT(value->'instanceID'->>'value', 31) = 'http://www.wikidata.org/entity/'
                AND wd_id IS NULL"
            );
            $stInsertGender->execute(["result" => $wikidataResult->getJSON()]);
            if ($this->getServerTiming() != null)
                $this->getServerTiming()->add("wikidata-insert-gender");

            $stInsertGenderText = $this->getDB()->prepare(
                "INSERT INTO wikidata_text (wdt_wd_id, wdt_language, wdt_name)
                SELECT DISTINCT wd.wd_id, :lang::VARCHAR, value->'gender'->>'value'
                FROM json_array_elements((:result::JSON)->'results'->'bindings') AS res
                JOIN wikidata AS wd ON wd.wd_wikidata_cod = REPLACE(value->'genderID'->>'value', 'http://www.wikidata.org/entity/', '')
                WHERE wd.wd_id NOT IN (SELECT wdt_wd_id FROM wikidata_text WHERE wdt_language=:lang::VARCHAR)"
            );
            $stInsertGenderText->execute(["lang" => $this->language, "result" => $wikidataResult->getJSON()]);
            if ($this->getServerTiming() != null)
                $this->getServerTiming()->add("wikidata-insert-gender-text");

            $stInsert = $this->getDB()->prepare(
                "UPDATE wikidata 
                SET wd_position = ST_GeomFromText(response->'wkt_coords'->>'value'),
                    wd_event_date = translateTimestamp(response->'event_date'->>'value'),
                    wd_event_date_precision = (response->'event_date_precision'->>'value')::INT,
                    wd_start_date = translateTimestamp(response->'start_date'->>'value'),
                    wd_start_date_precision = (response->'start_date_precision'->>'value')::INT,
                    wd_end_date = translateTimestamp(response->'end_date'->>'value'),
                    wd_end_date_precision = (response->'end_date_precision'->>'value')::INT,
                    wd_birth_date = translateTimestamp(response->'birth_date'->>'value'),
                    wd_birth_date_precision = (response->'birth_date_precision'->>'value')::INT,
                    wd_death_date = translateTimestamp(response->'death_date'->>'value'),
                    wd_death_date_precision = (response->'death_date_precision'->>'value')::INT,
                    wd_commons = response->'commons'->>'value',
                    wd_gender_id = gender.wd_id,
                    wd_instance_id = instance.wd_id,
                    wd_download_date = NOW()
                FROM json_array_elements((:result::JSON)->'results'->'bindings') AS response
                LEFT JOIN wikidata AS gender ON gender.wd_wikidata_cod = REPLACE(response->'genderID'->>'value', 'http://www.wikidata.org/entity/', '')
                LEFT JOIN wikidata AS instance ON instance.wd_wikidata_cod = REPLACE(response->'instanceID'->>'value', 'http://www.wikidata.org/entity/', '')
                WHERE wikidata.wd_download_date IS NULL
                AND wikidata.wd_wikidata_cod = REPLACE(response->'wikidata'->>'value', 'http://www.wikidata.org/entity/', '')"
            );
            $stInsert->execute(["result" => $wikidataResult->getJSON()]);
            if ($this->getServerTiming() != null)
                $this->getServerTiming()->add("wikidata-insert-wikidata");

            $stInsertPicture = $this->getDB()->prepare(
                "INSERT INTO wikidata_picture (wdp_wd_id, wdp_picture)
                SELECT wd.wd_id, pic.picture
                FROM wikidata AS wd
                JOIN (
                    SELECT
                        REPLACE(response->'wikidata'->>'value', 'http://www.wikidata.org/entity/', '') AS wikidata_cod,
                        REGEXP_SPLIT_TO_TABLE(response->'pictures'->>'value', '`') AS picture
                    FROM json_array_elements((:result::JSON)->'results'->'bindings') AS response
                ) AS pic ON pic.wikidata_cod = wd.wd_wikidata_cod
                LEFT JOIN wikidata_picture AS wdp ON wd.wd_id = wdp.wdp_wd_id
                WHERE pic.picture IS NOT NULL
                AND pic.picture != ''
                AND wdp.wdp_id IS NULL"
            );
            $stInsertPicture->execute(["result" => $wikidataResult->getJSON()]);
            if ($this->getServerTiming() != null)
                $this->getServerTiming()->add("wikidata-picture-insert");

            $stInsertText = $this->getDB()->prepare(
                "INSERT INTO wikidata_text (
                    wdt_wd_id,
                    wdt_language,
                    wdt_name,
                    wdt_description,
                    wdt_wikipedia_url,
                    wdt_occupations,
                    wdt_citizenship,
                    wdt_prizes,
                    wdt_event_place,
                    wdt_birth_place,
                    wdt_death_place
                )
                SELECT
                    wd_id,
                    :lang,
                    response->'name'->>'value',
                    response->'description'->>'value',
                    response->'wikipedia'->>'value',
                    response->'occupations'->>'value',
                    response->'citizenship'->>'value',
                    response->'prizes'->>'value',
                    response->'event_place'->>'value',
                    response->'birth_place'->>'value',
                    response->'death_place'->>'value'
                FROM json_array_elements((:result::JSON)->'results'->'bindings') AS response
                JOIN wikidata ON wd_wikidata_cod = REPLACE(response->'wikidata'->>'value', 'http://www.wikidata.org/entity/', '')"
            );
            $stInsertText->execute(["lang" => $this->language, "result" => $wikidataResult->getJSON()]);
            if ($this->getServerTiming() != null)
                $this->getServerTiming()->add("wikidata-text-insert");
        }
    }

    public function __toString(): string
    {
        return get_class($this) . ": " . $this->getBBox() . " / " . $this->getLanguage();
    }
}
