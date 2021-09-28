<?php

namespace App\Query\Overpass;

require_once(__DIR__ . "/BaseOverpassQuery.php");
require_once(__DIR__ . "/../GeoJSONQuery.php");
require_once(__DIR__ . "/../../result/overpass/OverpassEtymologyQueryResult.php");
require_once(__DIR__ . "/../../result/QueryResult.php");
require_once(__DIR__ . "/../../result/GeoJSONQueryResult.php");

use \App\Query\Overpass\BaseOverpassQuery;
use \App\Query\GeoJSONQuery;
use \App\Result\Overpass\OverpassEtymologyQueryResult;
use \App\Result\QueryResult;
use \App\Result\GeoJSONQueryResult;

/**
 * OverpassQL query that retrieves all the details of any item which has an etymology in the vicinity of a central point.
 * 
 * @author Daniele Santini <daniele@dsantini.it>
 */
class CenterEtymologyOverpassQuery extends BaseOverpassQuery implements GeoJSONQuery
{
    /**
     * @var float
     */
    private $lat, $lon, $radius;

    /**
     * @param float $lat
     * @param float $lon
     * @param float $radius
     * @param string $endpointURL
     * @param boolean $nodes
     * @param boolean $ways
     * @param boolean $relations
     */
    public function __construct($lat, $lon, $radius, $endpointURL, $nodes, $ways, $relations)
    {
        parent::__construct(
            'name:etymology:wikidata',
            "around:$radius,$lat,$lon",
            "out body; >; out skel qt;",
            $endpointURL,
            $nodes,
            $ways,
            $relations
        );
        $this->lat = $lat;
        $this->lon = $lon;
        $this->radius = $radius;
    }

    /**
     * @return float
     */
    public function getCenterLat(): float
    {
        return $this->lat;
    }

    /**
     * @return float
     */
    public function getCenterLon(): float
    {
        return $this->lon;
    }

    /**
     * @return float
     */
    public function getRadius(): float
    {
        return $this->radius;
    }

    /**
     * @return GeoJSONQueryResult
     */
    public function send(): QueryResult
    {
        $res = $this->sendAndRequireResult();
        return new OverpassEtymologyQueryResult($res->isSuccessful(), $res->getArray());
    }
}
