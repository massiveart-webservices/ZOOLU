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
 * @package    library.massiveart.website.cache.page
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * DownloadCenter
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-20: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.website.page
 * @subpackage DownloadCenter
 */

class DownloadCenter {

  protected $strTitle = '';
  protected $intFolderId = 0;
  protected $intFilterTagId = 0;
  
  protected $arrFiles = array();
  
  /**
   * construct
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function __construct() {
    $this->arrFiles['0-9'] = array(); 
    foreach(range('A','Z') as $strKey){
      $this->arrFiles[$strKey] = array();
    }
  }

  /**
   * add file
   * @param object $objFile
   */
  public function add($objFile){
    if($objFile->title != ''){
      $strKey = strtoupper($objFile->title[0]);
      if(!ctype_alpha($strKey)){
        $this->arrFiles['0-9'][] = $objFile;
      }elseif(array_key_exists($strKey, $this->arrFiles)){
        $this->arrFiles[$strKey][] = $objFile;
      }else{
        // TODO
      }
    }
  }
  
  /**
   * setTitle
   * @param string $strTitle
   */
  public function setTitle($strTitle){
    $this->strTitle = $strTitle;
  }

  /**
   * getTitle
   * @return string $strTitle
   */
  public function getTitle(){
    return $this->strTitle;
  }
  
  /**
   * setFolderId
   * @param integer $intFolderId
   */
  public function setFolderId($intFolderId){
    $this->intFolderId = $intFolderId;
  }

  /**
   * getFolderId
   * @return integer $intFolderId
   */
  public function getFolderId(){
    return $this->intFolderId;
  }
  
  /**
   * setFilterTagId
   * @param integer $intFilterTagId
   */
  public function setFilterTagId($intFilterTagId){
    $this->intFilterTagId = $intFilterTagId;
  }

  /**
   * getFilterTagId
   * @return integer $intFilterTagId
   */
  public function getFilterTagId(){
    return $this->intFilterTagId;
  }

  /**
   * setFiles
   * @param array $arrFiles
   */
  public function setFiles($arrFiles){
    $this->arrFiles = $arrFiles;
  }

  /**
   * getFiles
   * @return array $arrFiles
   */
  public function getFiles(){
    return $this->arrFiles;
  }
}