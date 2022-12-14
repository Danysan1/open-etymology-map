<?php

namespace App\Query\PostGIS\Stats;

require_once(__DIR__ . "/../BBoxPostGISQuery.php");
require_once(__DIR__ . "/../../BBoxJSONQuery.php");
require_once(__DIR__ . "/../../../result/JSONQueryResult.php");
require_once(__DIR__ . "/../../../result/JSONLocalQueryResult.php");

use \App\Query\BBoxJSONQuery;
use \App\Query\PostGIS\BBoxPostGISQuery;
use \App\Result\JSONQueryResult;
use \App\Result\JSONLocalQueryResult;
use App\Result\QueryResult;

class BBoxSourceStatsPostGISQuery extends BBoxPostGISQuery implements BBoxJSONQuery
{
    public function send(): QueryResult
    {
        $stRes = $this->getDB()->prepare($this->getQuery());
        $stRes->execute([
            "min_lon" => $this->getBBox()->getMinLon(),
            "max_lon" => $this->getBBox()->getMaxLon(),
            "min_lat" => $this->getBBox()->getMinLat(),
            "max_lat" => $this->getBBox()->getMaxLat(),
        ]);
        if ($this->hasServerTiming())
            $this->getServerTiming()->add("stats-query");
        return new JSONLocalQueryResult(true, $stRes->fetchColumn());
    }

    public function sendAndGetJSONResult(): JSONQueryResult
    {
        $out = $this->send();
        if (!$out instanceof JSONQueryResult)
            throw new \Exception("sendAndGetJSONResult(): can't get JSON result");
        return $out;
    }

    public function getQuery(): string
    {
        return
            "SELECT COALESCE(JSON_AGG(JSON_BUILD_OBJECT(
                'count', count,
                'color', source_color,
                'name', source_name
            )), '[]'::JSON)
        FROM (
            SELECT
                COUNT(DISTINCT et_wd_id) AS count,
                et_source_color(etymology) AS source_color,
                et_source_name(etymology) AS source_name
            FROM oem.element
            JOIN oem.etymology ON et_el_id = el_id
            WHERE el_geometry @ ST_MakeEnvelope(:min_lon, :min_lat, :max_lon, :max_lat, 4326)
            GROUP BY source_color, source_name
            ORDER BY count DESC
        ) AS ele";
    }
}
