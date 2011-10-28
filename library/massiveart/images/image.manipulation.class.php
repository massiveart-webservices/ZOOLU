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
 * @package    library.massiveart.images
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ImageManipulation
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-05-14: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.images
 * @subpackage ImageManipulation
 */

class ImageManipulation {

	/**
   * @var Core
   */
  protected $core;

	/**
   * @var ImageAdapterInterface
   */
	protected $objAdapter;

	/**
   * @var Zend_Loader_PluginLoader_Interface
   */
  protected static $objAdapterLoader;

  /**
   * @var string
   */
  protected $strAdapterType;

  /**
   * @var string
   */
  protected $strSourceFile;

  /**
   * Constructor
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function __construct($strSourceFile = ''){
    $this->strSourceFile = $strSourceFile;
    $this->core = Zend_Registry::get('Core');
  }

  /**
   * getAdapter
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getAdapter(){
    try{
      if(!$this->objAdapter instanceof ImageAdapterInterface){

        try {
          $class = $this->getAdapterLoader()->load($this->strAdapterType);
        } catch (Zend_Loader_PluginLoader_Exception $e) {
          throw new Exception('Image Manipulation Adapter by type ' . $this->strAdapterType . ' not found');
        }

        $this->objAdapter = new $class();

        if (!$this->objAdapter instanceof ImageAdapterInterface) {
            throw new Exception('Image Manipulation Adapter type ' . $this->strAdapterType . ' -> class ' . $class . ' is not of type ImageAdapterInterface');
        }
      }
      return $this->objAdapter;
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * getAdapterLoader
   * @return Zend_Loader_PluginLoader
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public static function getAdapterLoader(){
    if(null === self::$objAdapterLoader){
      require_once 'Zend/Loader/PluginLoader.php';
      self::$objAdapterLoader = new Zend_Loader_PluginLoader(array(
          'ImageAdapter' => dirname(__FILE__).'/adapter/',
      ));
    }
    return self::$objAdapterLoader;
  }

  /**
   * setAdapterType
   * @param string $strAdapterType
   */
  public function setAdapterType($strAdapterType){
    $this->strAdapterType = $strAdapterType;
  }

  /**
   * getAdapterType
   * @param string $strAdapterType
   */
  public function getAdapterType(){
    return $this->strAdapterType;
  }
}

?>