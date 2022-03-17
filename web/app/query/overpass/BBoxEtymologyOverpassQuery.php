<?php

namespace App\Query\Overpass;

require_once(__DIR__ . "/../../BoundingBox.php");
require_once(__DIR__ . "/BBoxOverpassQuery.php");
require_once(__DIR__ . "/OverpassConfig.php");
require_once(__DIR__ . "/../BBoxGeoJSONQuery.php");
require_once(__DIR__ . "/../../result/overpass/OverpassEtymologyQueryResult.php");
require_once(__DIR__ . "/../../result/QueryResult.php");
require_once(__DIR__ . "/../../result/GeoJSONQueryResult.php");

use \App\BoundingBox;
use \App\Query\Overpass\BBoxOverpassQuery;
use \App\Query\Overpass\OverpassConfig;
use \App\Query\BBoxGeoJSONQuery;
use \App\Result\Overpass\OverpassEtymologyQueryResult;
use \App\Result\QueryResult;
use \App\Result\JSONQueryResult;
use \App\Result\GeoJSONQueryResult;

/**
 * OverpassQL query that retrieves all the details of any item in a bounding box which has an etymology.
 * 
 * @author Daniele Santini <daniele@dsantini.it>
 */
class BBoxEtymologyOverpassQuery extends BBoxOverpassQuery implements BBoxGeoJSONQuery
{
    /**
     * @param BoundingBox $bbox
     * @param OverpassConfig $config
     */
    public function __construct($bbox, $config)
    {
        parent::__construct(
            ['name:etymology:wikidata', 'subject:wikidata'],
            $bbox,
            'out body; >; out skel qt;',
            $config
        );
    }

    public function send(): QueryResult
    {
        $res = parent::send();
        return new OverpassEtymologyQueryResult($res->isSuccessful(), $res->getArray());
    }

    public function sendAndGetJSONResult(): JSONQueryResult
    {
        $out = $this->send();
        if (!$out instanceof JSONQueryResult)
            throw new \Exception("sendAndGetJSONResult(): can't get JSON result");
        return $out;
    }

    public function sendAndGetGeoJSONResult(): GeoJSONQueryResult
    {
        $out = $this->send();
        if (!$out instanceof GeoJSONQueryResult)
            throw new \Exception("sendAndGetGeoJSONResult(): can't get GeoJSON result");
        return $out;
    }
}
