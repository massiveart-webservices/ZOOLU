<?php

/*
 * This file is part of the Search package.
 *
 * (c) Cornelius Hansjakob <cha@massiveart.com>
 *
 */

namespace Sulu\Search;

use Sulu\Search\Handler\ElasticaHandler;
use Sulu\Search\Handler\ZendLuceneHandler;

class Query
{

    const Q_AND = 'AND';
    const Q_OR = 'OR';
    const Q_REQUIRED = '+';
    const Q_PROHIBIT = '-';
    const Q_WILDCARD_MULTI = '*';
    const Q_WILDCARD_SINGLE = '?';
    const Q_FUZZY = '~';

    /**
     * @var ZendLuceneHandler|ElasticaHandler
     */
    protected $handler;

    /**
     * @param $handler
     */
    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    /**
     * @return array
     */
    public function fetch()
    {
        return $this->handler->fetch();
    }

    /**
     * @param $value
     * @param null $key
     * @param int $group
     *
     * @return $this
     */
    public function where($value, $key = null, $group = 0)
    {
        $this->handler->where($value, $key, $group, true);
        return $this;
    }

    /**
     * @param $value
     * @param null $key
     * @param int $group
     *
     * @return $this
     */
    public function orWhere($value, $key = null, $group = 0)
    {
        $this->handler->where($value, $key, $group, false);
        return $this;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function filterBy($key, $value)
    {
        $this->handler->filter($key, $value);
        return $this;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->handler->clear();
        return $this;
    }

}
