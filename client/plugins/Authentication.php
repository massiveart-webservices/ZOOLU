<?php

/**
 * Client_Authentication
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2010-04-22: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package client.plugins
 * @subpackage Client_Authentication
 */

class Client_Authentication implements ClientHelperInterface  {

  /**
   * @var Core
   */
  protected $core;
  
  /**
   * __construct
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function __construct() {
    $this->core = Zend_Registry::get('Core');
  }
  
  /**
   * isActive
   * @author Thomas Schedler <tsh@massiveart.com>
   * @return boolean
   */
  public function isActive(){
    return false;  
  }
  
  /**
   * getAdapter
   * @author Thomas Schedler <tsh@massiveart.com>
   * @return Zend_Auth_Adapter_Interface
   */
  public function getAdapter(){ }
  
  /**
   * getUserData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @return stdClass
   */
  public function getUserData(){ }
  
  /**
   * getUserRoleProvider
   * @author Thomas Schedler <tsh@massiveart.com>
   * @return RoleProvider
   */
  public function getUserRoleProvider(){ }
  
}