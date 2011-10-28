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
 * @package    library.massiveart.generic.elements
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * GenericElementField
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-20: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.elements
 * @subpackage GenericElementField
 */

require_once(dirname(__FILE__).'/generic.element.abstract.class.php');

class GenericElementField extends GenericElementAbstract {
  
  /**
   * mixed value of the element field
   * @var mixed
   */
  protected $value = null;
  
  
  /**
   * mixed value of the element field
   * @var array
   */
  protected $instanceValues = array();
  
  /**
   * properties of the element instance
   * @var array
   */
  protected $arrInstanceProperties = array();
  
  /**
   * setValue
   * @param mixed $value
   */
  public function setValue($value){    
    $this->value = $value;
  }

  /**
   * getValue
   * @param mixed $value
   */
  public function getValue(){
    return ((is_null($this->value)) ? $this->defaultValue : $this->value);
  }
  
  /**
   * setInstanceValue
   * @param integer $intRegionInstanceId
   * @param mixed $value
   */
  public function setInstanceValue($intInstanceId, $value){
    $this->instanceValues[$intInstanceId] = $value;
  }
  
  /**
   * getInstanceValue
   * @param integer $intRegionInstanceId
   * @param mixed $value
   */
  public function getInstanceValue($intInstanceId){
    if(array_key_exists($intInstanceId, $this->instanceValues)){
      return $this->instanceValues[$intInstanceId];  
    }else{
      return $this->defaultValue;
    }
  }

  /**
   * setInstanceProperty
   * @param integer $intInstanceId
   * @param string $strName
   * @param mixed $mixedValue
   */
  public function setInstanceProperty($intInstanceId, $strName, $mixedValue) {
    if (!array_key_exists($intInstanceId, $this->arrInstanceProperties)) {
      $this->arrInstanceProperties[$intInstanceId] = array();
    }
    $this->arrInstanceProperties[$intInstanceId][$strName] = $mixedValue;
  }

  /**
   * getInstanceProperty
   * @param integer $intInstanceId
   * @param string $strName
   * @return mixed $mixedValue
   */
  public function getInstanceProperty($intInstanceId, $strName) {
    if (array_key_exists($intInstanceId, $this->arrInstanceProperties)) {
      if (array_key_exists($strName, $this->arrInstanceProperties[$intInstanceId])) {
        return $this->arrInstanceProperties[$intInstanceId][$strName];
      }
      return null;
    }
    return null;
  }

  /**
   * getProperties
   * @return array
   */
  public function getProperties() {
    return $this->arrProperties;
  }

  /**
   * getProperties
   * @param integer $intInstanceId
   * @return array
   */
  public function getInstanceProperties($intInstanceId) {
    if (array_key_exists($intInstanceId, $this->arrInstanceProperties)) {
      return $this->arrInstanceProperties[$intInstanceId];
    }
    return array();
  }
}

?>