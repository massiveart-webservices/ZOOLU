<?php
/**
 * ZOOLU - Content Management System
 * Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 *
 * LICENSE
 *
 * This file is part of ZOOLU.
 *
 * ZOOLU is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ZOOLU is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ZOOLU. If not, see http://www.gnu.org/licenses/gpl-3.0.html.
 *
 * For further information visit our website www.getzoolu.org
 * or contact us at zoolu@getzoolu.org
 *
 * @category   ZOOLU
 * @package    library.massiveart.command
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ClientHelper
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-04-22: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.command
 * @subpackage ClientHelper
 */

class ClientHelper {
  
  /**
   * @var Core
   */
  protected $core;
  
  /**
   * @var Zend_Loader_PluginLoader
   */
  protected static $objPluginLoader;
  
  /**
   * @var ClientHelperAbstract
   */
  protected $objHelper;
  
  /**
   * @var string
   */
  private $strType;
  
  /**
   * @var array
   */
  private static $arrHelperTypes = array();
  
  /**
   * construct
   * @param $strType
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  protected function __construct($strType) {
    $this->core = Zend_Registry::get('Core');
    $this->strType = $strType;
  }
  
  private function __clone(){}
  
  /**
   * get
   * @param $strType
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public static function get($strType) {
    if(!array_key_exists($strType, self::$arrHelperTypes)){
      self::$arrHelperTypes[$strType] = new ClientHelper($strType);
    }
    return self::$arrHelperTypes[$strType];
  }

  /**
   * Method overloading
   *
   * @param  string $method
   * @param  array $args
   * @return mixed
   * @throws Zend_Controller_Action_Exception if helper does not have a direct() method
   */
  public function __call($strMethod, $arrArgs){
    try{
      $objHelper = $this->getHelper();
      if (!method_exists($objHelper, $strMethod)) {
        throw new Exception('Helper "' . $this->strType . '" does not support overloading via '.$strMethod.'()');
      }
      return call_user_func_array(array($objHelper, $strMethod), $arrArgs); 
      
    }catch (Exception $exc) {
      $this->core->logger->warn($exc);
    }
  }

  /**
   * getHelper 
   * @return ClientHelperInterface
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function getHelper(){
    try{
      if(!$this->objHelper instanceof ClientHelperInterface){
        
        try{
          $strClass = $this->getPluginLoader()->load($this->strType);          
        }catch (Zend_Loader_PluginLoader_Exception $e){            
          throw new Exception('Action Helper by name ' . $this->strType . ' not found: ' . $strClass);
        }

        $this->objHelper = new $strClass();

        if(!$this->objHelper instanceof ClientHelperInterface){
          throw new Exception('Helper name ' . $this->strType . ' -> class ' . $strClass . ' is not of type ClientHelper');
        }        
      }
      return $this->objHelper;      
    }catch (Exception $exc) {
      $this->core->logger->warn($exc);
    }
  }

  /**
   * getPluginLoader
   * @return Zend_Loader_PluginLoader
   */
  private static function getPluginLoader(){
    if(null === self::$objPluginLoader){
      self::$objPluginLoader = new Zend_Loader_PluginLoader(array(
          'Client' => GLOBAL_ROOT_PATH.'client/plugins/',
      ));
    }
    return self::$objPluginLoader;
  }
  
}