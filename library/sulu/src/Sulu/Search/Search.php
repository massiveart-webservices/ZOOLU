<?php

/*
 * This file is part of the Search package.
 *
 * (c) Cornelius Hansjakob <cha@massiveart.com>
 *
 */

namespace Sulu\Search;

use Sulu\Search\Query;
use Sulu\Search\Index;
use Sulu\Search\Handler\ZendLuceneHandler;
use Sulu\Search\Handler\ElasticaHandler;

class Search
{
    /**
     * define constants for search types
     */
    const TYPE_ZEND_LUCENE = 'ZendLucene';
    const TYPE_ELASTICA = 'Elastica';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Query
     */
    protected $query = null;

    /**
     * @var Index
     */
    protected $index = null;

    /**
     * different handlers
     * @var ZendLuceneHandler|ElasticaHandler
     */
    protected $handler = null;

    /**
     * @var array
     */
    protected $filters;

    /**
     * @param $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        if (null === $this->query) {
            $this->query = new Query();
        }

        return $this->query;
    }

    /**
     * @return Index
     */
    public function getIndex()
    {
        if (null === $this->index) {
            $this->index = new Index();
        }

        return $this->index;
    }

    public function getHandler()
    {
        if (null === $this->handler) {
            $this->setHandler();
        }

        return $this->handler;
    }

    protected function setHandler()
    {
        $handlerClass = $this->type . 'Handler';
        $this->handler = new $handlerClass();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return Search
     */
    public function addFilter($key, $value)
    {
        $this->filters[$key] = $value;
        return $this;
    }

    /**
     * @param array $filters
     *
     * @return Search
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return bool
     */
    public function hasFilters()
    {
        return count($this->filters) > 0;
    }
}