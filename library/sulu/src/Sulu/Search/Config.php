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
     * @var string
     */
    protected $dataType;

    /**
     * @var int
     */
    protected $languageId;

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

    /**
     * @param string $dataType
     */
    public function setDataType($dataType)
    {
        if (!empty($dataType)) {
            $this->addData('dataType', $dataType);
        }
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->getValue('dataType');
    }

    /**
     * @param int $languageId
     */
    public function setLanguageId($languageId)
    {
        if (!empty($languageId)) {
            $this->addData('languageId', $languageId);
        }
    }

    /**
     * @return int
     */
    public function getLanguageId()
    {
        return $this->getValue('languageId');
    }

}
