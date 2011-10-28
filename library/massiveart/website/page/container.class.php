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
 * PageContainer
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-20: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.website.page
 * @subpackage PageContainer
 */

class PageContainer {

  protected $strContainerTitle = '';
  protected $intContainerKey = 0;
  protected $intEntryNumber = 0;
  protected $intContainerSortType = 0;
  protected $intContainerSortOrder = 0;
  protected $intContainerLabel = 0;
  protected $intContainerDepth = 0;
  protected $intEntryViewType = 0;

  protected $arrEntries = array();

  /**
   * construct
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function __construct() { }

  /**
   * addPageEntry
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function addPageEntry(PageEntry $objItem, $strName = null){
    $this->arrEntries[$strName] = $objItem;
  }
  
  /**
   * removePageEntry
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function removePageEntry($strName){
    if(array_key_exists($strName, $this->arrEntries)){
      unset($this->arrEntries[$strName]);
    }
  }
  
  /**
   * sortEntries
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function sortEntries(){
    ksort($this->arrEntries);
  }

  /**
   * shuffleEntries
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function shuffleEntries(){
    shuffle($this->arrEntries);
  }
  
  /**
   * getPageEntry
   * @return PageEntry
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getPageEntry($strName){
    return $this->arrEntries[$strName];
  }

  /**
   * setContainerTitle
   * @param string $strContainerTitle
   */
  public function setContainerTitle($strContainerTitle){
    $this->strContainerTitle = $strContainerTitle;
  }

  /**
   * getContainerTitle
   * @return string $strContainerTitle
   */
  public function getContainerTitle(){
    return $this->strContainerTitle;
  }

  /**
   * setContainerKey
   * @param integer $intContainerKey
   */
  public function setContainerKey($intContainerKey){
    $this->intContainerKey = $intContainerKey;
  }

  /**
   * getContainerKey
   * @return integer $intContainerKey
   */
  public function getContainerKey(){
    return $this->intContainerKey;
  }

  /**
   * setEntryNumber
   * @param integer $intEntryNumber
   */
  public function setEntryNumber($intEntryNumber){
    $this->intEntryNumber = $intEntryNumber;
  }

  /**
   * getEntryNumber
   * @return integer $intEntryNumber
   */
  public function getEntryNumber(){
    return $this->intEntryNumber;
  }

  /**
   * setContainerSortType
   * @param integer $intContainerSortType
   */
  public function setContainerSortType($intContainerSortType){
    $this->intContainerSortType = $intContainerSortType;
  }

  /**
   * getContainerSortType
   * @return integer $intContainerSortType
   */
  public function getContainerSortType(){
    return $this->intContainerSortType;
  }

  /**
   * setContainerSortOrder
   * @param integer $intContainerSortOrder
   */
  public function setContainerSortOrder($intContainerSortOrder){
    $this->intContainerSortOrder = $intContainerSortOrder;
  }

  /**
   * getContainerSortOrder
   * @return integer $intContainerSortOrder
   */
  public function getContainerSortOrder(){
    return $this->intContainerSortOrder;
  }

  /**
   * setContainerLabel
   * @param integer $intContainerLabel
   */
  public function setContainerLabel($intContainerLabel){
    $this->intContainerLabel = $intContainerLabel;
  }

  /**
   * getContainerLabel
   * @return integer $intContainerLabel
   */
  public function getContainerLabel(){
    return $this->intContainerLabel;
  }

  /**
   * setContainerDepth
   * @param integer $intContainerDepth
   */
  public function setContainerDepth($intContainerDepth){
    $this->intContainerDepth = $intContainerDepth;
  }

  /**
   * getContainerDepth
   * @return integer $intContainerDepth
   */
  public function getContainerDepth(){
    return $this->intContainerDepth;
  }

  /**
   * setEntryViewType
   * @param integer $intEntryViewType
   */
  public function setEntryViewType($intEntryViewType){
    $this->intEntryViewType = $intEntryViewType;
  }

  /**
   * getEntryViewType
   * @return integer $intEntryViewType
   */
  public function getEntryViewType(){
    return $this->intEntryViewType;
  }

  /**
   * setEntries
   * @param array $arrEntries
   */
  public function setEntries($arrEntries){
    $this->arrEntries = $arrEntries;
  }

  /**
   * getEntries
   * @return array $arrEntries
   */
  public function getEntries(){
    return $this->arrEntries;
  }
}

?>