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
 * @package    library.massiveart.website.cache.page
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * PageEntry
 * 
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-20: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.website.page
 * @subpackage PageEntry
 */

class PageEntry {
  
	protected $intEntryId = 0;
	
	/**
   * properties of the element
   * @var Array
   */
  protected $arrProperties = array();
	
  /**
   * construct
   * @author Cornelius Hansjakob <cha@massiveart.com>   
   * @version 1.0
   */
  public function __construct() { }
  
  /**
   * __set
   * @param string $strName
   * @param mixed $mixedValue
   */
  public function __set($strName, $mixedValue) {      
    $this->arrProperties[$strName] = $mixedValue;
  }
  
  /**
   * __get
   * @param string $strName
   * @return mixed $mixedValue
   */
  public function __get($strName) {      
    if (array_key_exists($strName, $this->arrProperties)) {
      return $this->arrProperties[$strName];
    }
    return null;
  }
  
  /**
   * setEntryId
   * @param integer $intEntryId
   */
  public function setEntryId($intEntryId){
    $this->intEntryId = $intEntryId;
  }

  /**
   * getEntryId
   * @param integer $intEntryId
   */
  public function getEntryId(){
    return $this->intEntryId;
  }
  
}

?>