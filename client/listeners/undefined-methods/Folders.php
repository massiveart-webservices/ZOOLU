<?php

/**
 * Client_Listeners_UndefinedMethods_ModelFolders
 *
 * Client specific listeners
 *
 * Version history (please keep backward compatible):
 * 1.0, 2012-09-17: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package client.listeners
 * @subpackage Client_Listeners_UndefinedMethods_ModelFolders
 */

class Client_Listeners_UndefinedMethods_ModelFolders implements UndefinedMethodListener
{

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Model_Folders
     */
    protected $subject;

    /**
     * @var array
     */
    protected static $methods = array(
    );

    /**
     * __construct
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * @param $method
     * @param UndefinedMethod $handle
     */
    public function notify($method, UndefinedMethod $handle)
    {
        if (array_key_exists($method, self::$methods) && method_exists($this, self::$methods[$method])) {
            $this->subject = $handle->getSubject();
            $handle->setReturnValue(call_user_func_array(array($this, self::$methods[$method]), $handle->getArguments()));
        }
    }
 }

// return object instance
return new Client_Listeners_UndefinedMethods_ModelFolders();