<?php

/**
 * Client_Mails
 *
 * Client specific Addon Mails
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-06-15: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package client.plugins
 * @subpackage Client_Mails
 */

class Client_Mails implements ClientHelperInterface
{

    /**
     * @var Core
     */
    protected $core;

    /**
     * __construct
     * @author Cornelius Hansjakob <cha@massiveart.com>
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }
}