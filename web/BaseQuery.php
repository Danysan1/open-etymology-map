<?php
require_once("./Query.php");
require_once("./QueryResult.php");

/**
 * @author Daniele Santini <daniele@dsantini.it>
 */
abstract class BaseQuery implements Query {
    /**
     * @var string $query
     */
    private $query;

    /**
     * @var string $endpointURL
     */
    private $endpointURL;

    /**
     * @param string $query
     * @param string $endpointURL
     */
    public function __construct($query, $endpointURL) {
        $this->query = $query;
        $this->endpointURL = $endpointURL;
    }
    
    /**
     * @return string
     */
    public function getQuery() {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getEndpointURL() {
        return $this->endpointURL;
    }

    /**
     * @return QueryResult
     */
    public abstract function send();
}