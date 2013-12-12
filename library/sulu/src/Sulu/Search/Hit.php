<?php

/*
 * This file is part of the Search package.
 *
 * (c) Thomas Schedler <tsh@massiveart.com>
 *
 */

namespace Sulu\Search;

class Hit
{

    private $score;

    private $fields = array();

    public function __construct($score, array $fields)
    {
        $this->score = $score;
        $this->fields = $fields;
    }

    public function getFieldValue($key)
    {
        return array_key_exists($key, $this->fields) ? $this->fields[$key] : null;
    }

    public function getFieldNames()
    {
        return array_keys($this->fields);
    }

    public function score()
    {
        return $this->score;
    }

    public function __get($key)
    {
        return $this->getFieldValue($key);
    }

}
