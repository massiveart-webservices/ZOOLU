<?php

/*
 * This file is part of the Search package.
 *
 * (c) Cornelius Hansjakob <cha@massiveart.com>
 *
 */

namespace Sulu\Search\Handler;

class ElasticaHandler extends AbstractHandler implements HandlerInterface
{

    /**
     * @var \Elastica\Index
     */
    private $index;

    public function add($key, $data)
    {
        if ($this->getIndex() !== false) {


        }
    }

    public function delete($key)
    {
        if ($this->getIndex(false) !== false) {


        }
    }

    protected function getIndex()
    {

        if (!is_object($this->index) || !($this->index instanceof \Zend_Search_Lucene)) {

        }

        return $this->index;
    }
}
