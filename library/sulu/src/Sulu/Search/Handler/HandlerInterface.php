<?php

/*
 * This file is part of the Search package.
 *
 * (c) Cornelius Hansjakob <cha@massiveart.com>
 *
 */

namespace Sulu\Search\Handler;

/**
 * Interface that all Search Handlers must implement
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 */
interface HandlerInterface
{

    /**
     * add document to index
     * @param $key
     * @param $data
     */
    public function add($key, $data);

    /**
     * delete indexed document
     * @param $key
     */
    public function delete($key);

    /**
     * add where statement
     * @param $value
     * @param null $field
     * @param int $group
     * @param bool $bool
     */
    public function where($value, $field = null, $group = 0, $bool = true);

    /**
     * filter search result by
     * @param $field
     * @param null $value
     */
    public function filter($field, $value = null);

    /**
     * fetch query result
     * @return array|null
     */
    public function fetch();

}
