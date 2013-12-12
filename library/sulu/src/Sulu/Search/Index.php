<?php

/*
 * This file is part of the Search package.
 *
 * (c) Cornelius Hansjakob <cha@massiveart.com>
 *
 */

namespace Sulu\Search;

use Sulu\Search\Handler\ElasticaHandler;
use Sulu\Search\Handler\HandlerInterface;
use Sulu\Search\Handler\ZendLuceneHandler;

class Index
{

    /**
     * @var ZendLuceneHandler|ElasticaHandler
     */
    protected $handler;

    /**
     * @param $handler
     */
    public function __construct(HandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param $key
     * @param $data
     */
    public function add($key, $data)
    {
        $this->handler->add($key, $data);
    }

    /**
     * @param $key
     */
    public function delete($key)
    {
        $this->handler->delete($key);
    }

}
