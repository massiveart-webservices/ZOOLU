<?php

/*
 * This file is part of the Search package.
 *
 * (c) Cornelius Hansjakob <cha@massiveart.com>
 *
 */

namespace Sulu\Search;

class Config
{

    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $key
     * @param $value
     */
    public function addData($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return array|string
     * @throws \Exception
     */
    public function getValue($key = '')
    {
        if (!array_key_exists($key, $this->data)) {
            throw new \Exception('Config key is not set: ' . $key);
        }

        return $this->data[$key];
    }
}