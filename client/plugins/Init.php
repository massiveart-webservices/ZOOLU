<?php

/**
 * Client_Init
 *
 * Client specific Init
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2011-05-03: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package client.plugins
 * @subpackage Client_Init
 */

class Client_Init implements ClientHelperInterface  {

  /**
   * @var Core
   */
  protected $core;
  
  /**
   * __construct
   * @author Cornelius Hansjakob <cha@massiveart.com>
   */
  public function __construct() {
    $this->core = Zend_Registry::get('Core');
  }
  
  /**
   * init
   * @param $objController Zend_Controller_Action
   * @return void
   */
  public function init($objController){
     
  }
}