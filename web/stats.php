<?php
require_once("./app/ServerTiming.php");

use \App\ServerTiming;

$serverTiming = new ServerTiming();

require_once(__DIR__ . "/app/IniEnvConfiguration.php");
require_once(__DIR__ . "/app/BaseBoundingBox.php");
require_once(__DIR__ . "/app/PostGIS_PDO.php");
require_once(__DIR__ . "/app/query/postgis/stats/BBoxGenderStatsPostGISQuery.php");
require_once(__DIR__ . "/app/query/postgis/stats/BBoxTypeStatsPostGISQuery.php");
require_once(__DIR__ . "/app/query/postgis/stats/BBoxSourceStatsPostGISQuery.php");
require_once(__DIR__ . "/app/query/wikidata/stats/GenderStatsWikidataFactory.php");
require_once(__DIR__ . "/app/query/wikidata/stats/TypeStatsWikidataFactory.php");
require_once(__DIR__ . "/app/query/cache/CSVCachedBBoxJSONQuery.php");
require_once(__DIR__ . "/app/query/combined/BBoxStatsOverpassWikidataQuery.php");
require_once(__DIR__ . "/app/query/overpass/RoundRobinOverpassConfig.php");
require_once(__DIR__ . "/funcs.php");
$serverTiming->add("0_include");

use \App\IniEnvConfiguration;
use \App\BaseBoundingBox;
use \App\PostGIS_PDO;
use \App\Query\Cache\CSVCachedBBoxJSONQuery;
use \App\Query\Combined\BBoxStatsOverpassWikidataQuery;
use \App\Query\PostGIS\Stats\BBoxGenderStatsPostGISQuery;
use \App\Query\PostGIS\Stats\BBoxTypeStatsPostGISQuery;
use \App\Query\PostGIS\Stats\BBoxSourceStatsPostGISQuery;
use \App\Query\Wikidata\Stats\GenderStatsWikidataFactory;
use \App\Query\Wikidata\Stats\TypeStatsWikidataFactory;
use \App\Query\Overpass\RoundRobinOverpassConfig;

$conf = new IniEnvConfiguration();
$serverTiming->add("1_readConfig");

prepareJSON($conf);
$serverTiming->add("2_prepare");

$to = (string)getFilteredParamOrDefault("to", FILTER_UNSAFE_RAW, "geojson");
$language = (string)getFilteredParamOrDefault("language", FILTER_SANITIZE_SPECIAL_CHARS, (string)$conf->get('default_language'));
$overpassConfig = new RoundRobinOverpassConfig($conf);
$wikidataEndpointURL = (string)$conf->get('wikidata_endpoint');
$cacheFileBasePath = (string)$conf->get("cache_file_base_path");
$enableDB = $conf->getBool("db_enable");

if ($enableDB)
    $db = new PostGIS_PDO($conf);

// "en-US" => "en"
$langMatches = [];
if (!preg_match('/^([a-z]{2})(-[A-Z]{2})?$/', $language, $langMatches) || empty($langMatches[1])) {
    http_response_code(400);
    die('{"error":"Invalid language code."};');
}
$safeLanguage = $langMatches[1];
//error_log($language." => ".json_encode($langMatches)." => ".$safeLanguage);

$minLat = (float)getFilteredParamOrError("minLat", FILTER_VALIDATE_FLOAT);
$minLon = (float)getFilteredParamOrError("minLon", FILTER_VALIDATE_FLOAT);
$maxLat = (float)getFilteredParamOrError("maxLat", FILTER_VALIDATE_FLOAT);
$maxLon = (float)getFilteredParamOrError("maxLon", FILTER_VALIDATE_FLOAT);

$bbox = new BaseBoundingBox($minLat, $minLon, $maxLat, $maxLon);

if (!empty($db) && $db instanceof PDO) {
    if ($to == "genderStats") {
        $query = new BBoxGenderStatsPostGISQuery(
            $bbox,
            $safeLanguage,
            $db,
            $wikidataEndpointURL,
            $serverTiming
        );
    } elseif ($to == "typeStats") {
        $query = new BBoxTypeStatsPostGISQuery(
            $bbox,
            $safeLanguage,
            $db,
            $wikidataEndpointURL,
            $serverTiming
        );
    } elseif ($to == "sourceStats") {
        $query = new BBoxSourceStatsPostGISQuery(
            $bbox,
            $db,
            $serverTiming
        );
    } else {
        throw new Exception("Bad 'to' parameter");
    }
} else {
    if ($to == "genderStats") {
        $wikidataFactory = new GenderStatsWikidataFactory($safeLanguage, $wikidataEndpointURL);
    } elseif ($to == "typeStats") {
        $wikidataFactory = new TypeStatsWikidataFactory($safeLanguage, $wikidataEndpointURL);
    } else {
        throw new Exception("Bad 'to' parameter");
    }
    
    $baseQuery = new BBoxStatsOverpassWikidataQuery(
        $bbox,
        $overpassConfig,
        $wikidataFactory,
        $serverTiming
    );
    $query = new CSVCachedBBoxJSONQuery(
        $baseQuery,
        $cacheFileBasePath . $safeLanguage . "_",
        $conf,
        $serverTiming
    );
}

$serverTiming->add("3_init");

$result = $query->sendAndGetJSONResult();
$serverTiming->add("4_query");
if (!$result->isSuccessful()) {
    http_response_code(500);
    error_log("Query error: " . $result);
    $out = '{"error":"Error getting result (overpass/wikidata server error)"}';
} elseif (!$result->hasResult()) {
    http_response_code(500);
    error_log("Query no result: " . $result);
    $out = '{"error":"Error getting result (bad response)"}';
} elseif ($result->hasPublicSourcePath()) {
    if ($conf->getBool("redirect_to_cache_file")) {
        $out = "";
        header("Location: " . $result->getPublicSourcePath());
    } else {
        $out = $result->getJSON();
        header("Cache-Location: " . $result->getPublicSourcePath());
    }
} else {
    $out = $result->getJSON();
}

$serverTiming->add("5_output");
$serverTiming->sendHeader();
echo $out;
