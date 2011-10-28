<?php

/**
 * Client_Datareceiver
 *
 * Client specific Datareceiver
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2011-06-16: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package client.plugins
 * @subpackage Client_Datareceiver
 */
class Client_Datareceiver implements ClientHelperInterface  {

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
}