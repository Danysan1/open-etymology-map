<?php
require_once("./CachedBBoxQuery.php");
require_once("./BBoxEtymologyOverpassWikidataQuery.php");

/**
 * @author Daniele Santini <daniele@dsantini.it>
 */
class CachedBBoxEtymologyOverpassWikidataQuery extends CachedBBoxQuery {
    /**
     * @param float $minLat
     * @param float $minLon
     * @param float $maxLat
     * @param float $maxLon
     * @param string $overpassEndpointURL
     * @param string $wikidataEndpointURL
     * @param string $language
     * @param string $cacheFileBasePath
     * @param int $cacheTimeoutHours
     */
    public function __construct($minLat,$minLon,$maxLat,$maxLon,$overpassEndpointURL,$wikidataEndpointURL,$language,$cacheFileBasePath,$cacheTimeoutHours)
    {
        parent::__construct(
            new BBoxEtymologyOverpassWikidataQuery($minLat,$minLon,$maxLat,$maxLon,$overpassEndpointURL,$wikidataEndpointURL,$language),
            $cacheFileBasePath,
            $cacheTimeoutHours
        );
    }
}