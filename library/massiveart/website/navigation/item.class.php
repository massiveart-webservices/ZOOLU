<?php
/**
 * ZOOLU - Content Management System
 * Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
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
 * @package    library.massiveart.website.cache.navigation
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * NavigationItem
 * 
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-17: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.website.navigation
 * @subpackage NavigationItem
 */

class NavigationItem {
  
  protected $intOrder;
  
  protected $strTitle;
  protected $strUrl;
  protected $strTarget;
  
  protected $intId;
  protected $intTypeId;
  protected $intParentId;
  protected $strItemId;
  protected $intLanguageId;
  protected $objChanged;
  protected $blnLandingPages;
  
  /**
   * construct
   * @author Thomas Schedler <tsh@massiveart.com>   
   * @version 1.0
   */
  public function __construct() { }
  
  /**
   * setTitle
   * @param string $strTitle
   */
  public function setTitle($strTitle){
    $this->strTitle = $strTitle;
  }

  /**
   * getTitle
   * @param string $strTitle
   */
  public function getTitle(){
    return $this->strTitle;
  }
  
  /**
   * setUrl
   * @param string $strUrl
   */
  public function setUrl($strUrl){
    $this->strUrl = $strUrl;
  }

  /**
   * getUrl
   * @param string $strUrl
   */
  public function getUrl(){
    return $this->strUrl;
  }
  
  /**
   * setTarget
   * @param string $strTarget
   */
  public function setTarget($strTarget){
    $this->strTarget = $strTarget;
  }

  /**
   * getTarget
   * @param string $strTarget
   */
  public function getTarget(){
    return $this->strTarget;
  }
  
  /**
   * setOrder
   * @return integer $intOrder
   */
  public function setOrder($intOrder){
    $this->intOrder = $intOrder;
  }
  
  /**
   * getOrder
   * @return integer
   */
  public function getOrder(){
    return $this->intOrder;
  }
  
  /**
   * setId
   * @return integer $intId
   */
  public function setId($intId){
    $this->intId = $intId;
  }
  
  /**
   * getId
   * @return integer
   */
  public function getId(){
    return $this->intId;
  }
  
  /**
   * setTypeId
   * @return integer $intTypeId
   */
  public function setTypeId($intTypeId){
    $this->intTypeId = $intTypeId;
  }
  
  /**
   * getTypeId
   * @return integer
   */
  public function getTypeId(){
    return $this->intTypeId;
  }
  
  /**
   * setParentId
   * @return integer $intParentId
   */
  public function setParentId($intParentId){
    $this->intParentId = $intParentId;
  }
  
  /**
   * getParentId
   * @return integer
   */
  public function getParentId(){
    return $this->intParentId;
  }
  
  /**
   * setItemId
   * @param stirng $strItemId
   */
  public function setItemId($strItemId){
    $this->strItemId = $strItemId;
  }

  /**
   * getItemId
   * @param string $strItemId
   */
  public function getItemId(){
    return $this->strItemId;
  }
  
  /**
   * setLanguageId
   * @return integer $intLanguageId
   */
  public function setLanguageId($intLanguageId){
    $this->intLanguageId = $intLanguageId;
  }
  
  /**
   * getLanguageId
   * @return integer
   */
  public function getLanguageId(){
    return $this->intLanguageId;
  }
  
  /**
   * setChanged
   * @param string/obj $Date
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function setChanged($Date, $blnIsValidDateObj = false){
    if($blnIsValidDateObj == true){
      $this->objChanged = $Date;
    }else{
      $arrTmpTimeStamp = explode(' ', $Date);
      if(count($arrTmpTimeStamp) > 1){
        $arrTmpTime = explode(':', $arrTmpTimeStamp[1]);
        $arrTmpDate = explode('-', $arrTmpTimeStamp[0]);
        if(count($arrTmpDate) == 3){
          $this->objChanged =  mktime($arrTmpTime[0], $arrTmpTime[1], $arrTmpTime[2], $arrTmpDate[1], $arrTmpDate[2], $arrTmpDate[0]);
        }
      }
    }
  }

  /**
   * getChanged
   * @param string $strFormat
   * @return string $strChanged
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getChanged($strFormat = 'd.m.Y', $blnGetDateObj = false){
    if($blnGetDateObj == true){
      return $this->objChanged;
    }else{
      if($this->objChanged != null){
        return date($strFormat, $this->objChanged);
      }else{
        return null;
      }
    }
  }
  
  /**
   * setHasLandingPages
   * @param boolean $blnLandingPages
   * @return boolean
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function setHasLandingPages($blnLandingPages){
    $this->blnLandingPages = $blnLandingPages;
  }
  
  /**
   * hasLandingPages
   * @return boolean
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function hasLandingPages(){
    return $this->blnLandingPages;
  }
}
?>