<?php

namespace App\Query\Wikidata\Stats;

require_once(__DIR__ . "/TypeStatsWikidataQuery.php");
require_once(__DIR__ . "/../../StringSetXMLQuery.php");
require_once(__DIR__ . "/../../StringSetXMLQueryFactory.php");
require_once(__DIR__ . "/../../../StringSet.php");

use \App\Query\StringSetXMLQuery;
use \App\Query\StringSetXMLQueryFactory;
use \App\StringSet;
use \App\Query\Wikidata\Stats\TypeStatsWikidataQuery;

class TypeStatsWikidataFactory implements StringSetXMLQueryFactory
{
    /**
     * @var string $language
     */
    private $language;

    /**
     * @var string $endpointURL
     */
    private $endpointURL;

    /**
     * @param string $language
     * @param string $endpointURL
     */
    public function __construct($language, $endpointURL)
    {
        $this->language = $language;
        $this->endpointURL = $endpointURL;
    }

    public function create(StringSet $input): StringSetXMLQuery
    {
        return new TypeStatsWikidataQuery($input, $this->language, $this->endpointURL);
    }
}
