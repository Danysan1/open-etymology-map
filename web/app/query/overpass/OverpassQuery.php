<?php

namespace App\Query\Overpass;

require_once(__DIR__ . "/../BaseQuery.php");
require_once(__DIR__ . "/../../result/QueryResult.php");
require_once(__DIR__ . "/../../result/JSONRemoteQueryResult.php");

use \App\Query\BaseQuery;
use \App\Result\QueryResult;
use \App\Result\JSONRemoteQueryResult;

/**
 * Overpass query.
 * 
 * @author Daniele Santini <daniele@dsantini.it>
 */
class OverpassQuery extends BaseQuery
{
    /**
     * @return QueryResult
     */
    public function send(): QueryResult
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getEndpointURL());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getQuery());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $curlInfo = (array)curl_getinfo($ch);
        curl_close($ch);
        if (!$result)
            $result = null;
        else
            assert(is_string($result));
        return new JSONRemoteQueryResult($result, $curlInfo);
    }
}