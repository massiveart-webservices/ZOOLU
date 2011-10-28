<?php

/**
 * Core Class - based on Singleton Pattern
 *
 * @author Rene Ponudic <rene.ponudic@fusonic.net>
 * @version 1.0
 * @package com.massiveart
 * @subpackage Core
 */

class Service_Core {
  /**
   * @var Core object instance
   */
  private static $instance = null;

  /**
   * @var Logger
   */
  public $logger;
  
  /**
   * @var Zend_Config_Xml
   */
  public $config = null;

  /**
   * Constructor
   */
  protected function __construct(){
    
    // get config for application itself
    $this->config = new Zend_Config_Xml(APPLICATION_PATH.'/configs/config.xml', APPLICATION_ENV);
  }

  private function __clone(){}

  /**
   * getInstance
   * @return MA_Service_Core
   */
  public static function getInstance(){
    if(self::$instance == null){
      self::$instance = new Service_Core();
    }
    return self::$instance;
  }

  
  /**
   * setDbh
   * @param Zend_Db_Adapter_Abstract $objDbh
   */
  public function setLogger($objLogger) {
    $this->logger = $objLogger;
  }
  
  
}
?>
