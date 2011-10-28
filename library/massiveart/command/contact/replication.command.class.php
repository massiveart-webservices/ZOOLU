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
 * ContactCommand
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-05-09: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.command
 * @subpackage ContactCommand
 */

require_once(dirname(__FILE__).'/../command.interface.php');

class ContactReplicationCommand implements CommandInterface {

  /**
   * @var Core
   */
  protected $core;
  
  /**
   * @var Zend_Loader_PluginLoader
   */
  protected static $objPluginLoader;

  /**
   * @var array
   */
  private $arrReplications = array();

  /**
   * Constructor
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  }

  /**
   * onCommand
   * @param string $strName
   * @param array $arrArgs
   * @return boolean
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function onCommand($strName, $arrArgs){
    
    $arrReplications = $this->core->sysConfig->contact->replications->toArray();
    $arrReplications = is_array($arrReplications['replication']) ? $arrReplications['replication'] : array($arrReplications['replication']);
    
    foreach($arrReplications as $strReplicationClass){
      $this->getReplicationHelper($strReplicationClass);
    }
    
    if(count($this->arrReplications) > 0) {
      switch($strName){
        case 'added':
          return $this->added($arrArgs);
        case 'updated':
          return $this->updated($arrArgs);
        case 'deleted':
          return $this->deleted($arrArgs);
        default:
          return true;
      }
    }else{
      return true;
    }
  }
  
  /**
   * contact has been added
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private function added($arrArgs) {
    foreach($this->arrReplications as $objReplication){
      $objReplication->add($arrArgs);
    }
  }
  
  /**
   * contact has been updated
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private function updated($arrArgs) {
    foreach($this->arrReplications as $objReplication){
      $objReplication->update($arrArgs);
    }    
  }
  
  /**
   * contact has been deleted
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private function deleted($arrArgs) {
    foreach($this->arrReplications as $objReplication){
      $objReplication->delete($arrArgs);
    }
  }
  
  /**
   * getReplicationHelper 
   * @return ContactReplicationInterface
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private function getReplicationHelper($strReplicationClass){
    try{
      if(!array_key_exists($strReplicationClass, $this->arrReplications) || !$this->arrReplications[$strReplicationClass] instanceof ContactReplicationInterface){
        
        try{
          $strClass = $this->getPluginLoader()->load($strReplicationClass);          
        }catch (Zend_Loader_PluginLoader_Exception $e){            
          throw new Exception('Replication Helper by name ' . $strReplicationClass . ' not found: ' . $strClass);
        }

        $this->arrReplications[$strReplicationClass] = new $strClass();

        if(!$this->arrReplications[$strReplicationClass] instanceof ContactReplicationInterface){
          throw new Exception('Replication name ' . $strReplicationClass . ' -> class ' . $strClass . ' is not of type ClientHelper');
        }  

        return $this->arrReplications[$strReplicationClass];
      }else{
        $this->arrReplications[$strReplicationClass];
      }   
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
          'ContactReplication' => GLOBAL_ROOT_PATH.'library/massiveart/contact/replication/',
      ));
    }
    return self::$objPluginLoader;
  }
}