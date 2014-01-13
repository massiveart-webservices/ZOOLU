<?php

/*
 * This file is part of the Search package.
 *
 * (c) Cornelius Hansjakob <cha@massiveart.com>
 *
 */

namespace Sulu\Search\Handler;

abstract class AbstractHandler
{
    const TYPE_PAGE = 'page';
    const TYPE_GLOBAL = 'global';

    /**
     * @var \Sulu\Search\Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var int
     */
    protected $languageId = null;

    /**
     * @var string
     */
    protected $nodeSummary;

    /**
     * @var array
     */
    protected $queries = array();

    /**
     * @var array
     */
    protected $filters = array();

    /**
     * @param \Sulu\Search\Config $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @return \Sulu\Search\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param bool $clear
     *
     * @return array|int|null|string
     */
    public function getLanguageId($clear = false)
    {
        if ($clear === true || null === $this->languageId) {
            $this->languageId = $this->config->getValue('languageId');
        }

        return $this->languageId;
    }

    /**
     * @param bool $clear
     *
     * @return array|null|string
     */
    public function getType($clear = false)
    {
        if ($clear === true || null === $this->type) {
            $this->type = $this->config->getValue('dataType');
        }

        return $this->type;
    }


}
