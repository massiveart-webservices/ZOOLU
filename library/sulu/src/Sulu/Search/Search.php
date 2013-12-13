<?php

/*
 * This file is part of the Search package.
 *
 * (c) Cornelius Hansjakob <cha@massiveart.com>
 *
 */

namespace Sulu\Search;

use Sulu\Search\Config;
use Sulu\Search\Handler\HandlerInterface;
use Sulu\Search\Query;
use Sulu\Search\Index;

class Search
{
    /**
     * constants for search types
     */
    const TYPE_ZEND_LUCENE = 'ZendLucene';
    const TYPE_ELASTICA = 'Elastica';

    /**
     * constants for search field types
     */
    const FIELD_TYPE_NONE = 1;
    const FIELD_TYPE_KEYWORD = 2;
    const FIELD_TYPE_UNINDEXED = 3;
    const FIELD_TYPE_BINARY = 4;
    const FIELD_TYPE_TEXT = 5;
    const FIELD_TYPE_UNSTORED = 6;
    const FIELD_TYPE_SUMMARY_INDEXED = 7;

    /**
     * constant for search field node summary
     */
    const NODE_SUMMARY = '_summary';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Config
     */
    protected $config;

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
     *
     * @var HandlerInterface
     */
    protected $handler = null;

    /**
     * @param array $configData
     * @param string|null $dataType
     * @param int|null $languageId
     */
    public function __construct(array $configData, $dataType = null, $languageId = null)
    {
        // set config
        $this->setConfig($configData);

        $this->getConfig()->setDataType($dataType);
        $this->getConfig()->setLanguageId($languageId);

        // set search type
        $this->setType();
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = new Config($config);
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        if (null === $this->query) {
            $this->query = new Query($this->getHandler());
        }

        return $this->query;
    }

    /**
     * @return Index
     */
    public function getIndex()
    {
        if (null === $this->index) {
            $this->index = new Index($this->getHandler());
        }

        return $this->index;
    }

    /**
     * @return HandlerInterface
     */
    protected function getHandler()
    {
        if (null === $this->handler) {
            $this->setHandler();
        }

        return $this->handler;
    }

    protected function setHandler()
    {
        $handlerClass = '\\Sulu\\Search\\Handler\\' . $this->type . 'Handler';
        $this->handler = new $handlerClass($this->config);
    }

    private function setType()
    {
        if (null !== $this->getConfig()->getValue('type')) {
            // define search type by config
            switch ($this->getConfig()->getValue('type')) {
                case self::TYPE_ELASTICA:
                    $this->type = self::TYPE_ELASTICA;
                    break;
                case self::TYPE_ZEND_LUCENE:
                default:
                    $this->type = self::TYPE_ZEND_LUCENE;
                    break;
            }
        } else {
            // define default search type
            $this->type = self::TYPE_ZEND_LUCENE;
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

}
