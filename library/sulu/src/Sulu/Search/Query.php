<?php

/*
 * This file is part of the Search package.
 *
 * (c) Cornelius Hansjakob <cha@massiveart.com>
 *
 */

namespace Sulu\Search;

class Query
{

    protected $handler;

    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    // parse
    // search / wildcard search
    // set limit of search
    //

}