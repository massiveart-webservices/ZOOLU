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
    protected $type = '';

    /**
     * @var int
     */
    protected $languageId = null;

    /**
     * @param \Sulu\Search\Config $config
     */
    public function __construct($config)
    {
        $this->config = $config;

        // define type and language id from config
        $this->type = $this->config->getValue('dataType');
        $this->languageId = $this->config->getValue('languageId');
    }

    /**
     * @return \Sulu\Search\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return int
     */
    public function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


}
