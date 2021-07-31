<?php
require_once("./Configuration.php");
require_once("./OverpassQuery.php");
require_once("./QueryResult.php");
require_once("./funcs.php");
$conf = new Configuration();
prepareJSON($conf);

$minLat = (float)getFilteredParamOrError( "minLat", FILTER_VALIDATE_FLOAT );
$minLon = (float)getFilteredParamOrError( "minLon", FILTER_VALIDATE_FLOAT );
$maxLat = (float)getFilteredParamOrError( "maxLat", FILTER_VALIDATE_FLOAT );
$maxLon = (float)getFilteredParamOrError( "maxLon", FILTER_VALIDATE_FLOAT );

$overpassQuery = OverpassQuery::FromBoundingBox($minLat, $minLon, $maxLat, $maxLon);
$endpoint = (string)$conf->get('overpass-endpoint');
$result = $overpassQuery->send($endpoint);
if(!$result->success()) {
    http_response_code(500);
    $result->errorLogResponse("overpass");
    die('{"error":"Error getting result (overpass server error)"}');
} elseif (!$result->hasData() || !$result->isJSON()) {
    http_response_code(500);
    $result->errorLogResponse("overpass");
    die('{"error":"Error getting result (bad response)"}');
} else {
    echo json_encode((array)$result->parseJSONBody()["elements"]);
}




