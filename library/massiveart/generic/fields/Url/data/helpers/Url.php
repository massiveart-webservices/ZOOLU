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
 * @package    library.massiveart.generic.fields.Url.data.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * GenericDataHelperUrl
 *
 * Helper to save and load the "url" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-06: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data.helpers
 * @subpackage GenericDataHelper_Url
 */

require_once(dirname(__FILE__).'/../../../../data/helpers/Abstract.php');

class GenericDataHelper_Url extends GenericDataHelperAbstract  {

  /**
   * @var Model_Pages|Model_Products
   */
  private $objModel;
  
  /**
   * @var string
   */
  private $strType;

  /**
   * @var Model_Urls
   */
  private $objModelUrls;
  
  /**
   * @var Model_Utilities
   */
  private $objModelUtilities;

  /**
   * @var Zend_Db_Table_Rowset_Abstract
   */
  private $objPathReplacers;

  /**
   * @var string
   */
  private $strUrl;

  /**
   * @var string
   */
  private $strParentPageUrl;

  /**
   * save()
   * @param integer $intElementId
   * @param string $strType
   * @param string $strElementId
   * @param integet $intVersion
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function save($intElementId, $strType, $strElementId = null, $intVersion = null){
    try{
      $this->strType = $strType;


      $this->getModel();
      $this->getModelUrls();

      $objItemData = $this->objModel->load($intElementId);
            
      if(count($objItemData) > 0){
        $objItem = $objItemData->current();

        $strUrlNew = '';

        // get the new url
        if(isset($_POST[$this->objElement->name.'_EditableUrl'])){
        $this->strUrl = strtolower($_POST[$this->objElement->name.'_EditableUrl']);
        }
        if($this->strUrl == '' && !($this->objElement->Setup()->getIsStartElement(false) && $this->objElement->Setup()->getParentId() == null)){
          $objFieldData = $this->objElement->Setup()->getModelGenericForm()->loadFieldsWithPropery($this->core->sysConfig->fields->properties->url_field, $this->objElement->Setup()->getGenFormId());

          if(count($objFieldData) > 0){
            foreach($objFieldData as $objField){
              if($this->objElement->Setup()->getRegion($objField->regionId)->getField($objField->name)->getValue() != ''){
                $this->strUrl .= str_replace('/', '-', $this->objElement->Setup()->getRegion($objField->regionId)->getField($objField->name)->getValue());
                break;
              }
            }
          }
        }
        
        if($this->objElement->Setup()->getIsStartElement(false) && $this->objElement->Setup()->getParentId() != null){
          $this->strUrl = rtrim($this->strUrl, '/').'/';
        }
        
        $this->strUrl = $this->getModelUrls()->makeUrlConform($this->strUrl);
        //Check new URL 
        $objUrlData = $this->objModelUrls->loadUrl($objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);
        if(count($objUrlData) > 0){
          $objUrl = $objUrlData->current();
          if(strcmp($this->strUrl, $objUrl->url) !== 0){
            // Url have changed
            // set all page urls to isMain 0
            $this->objModelUrls->resetIsMainUrl($objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);
            $this->objModelUrls->insertUrl($this->strUrl, $objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);
          }
        }else{
          // set all page urls to isMain 0
          $this->objModelUrls->resetIsMainUrl($objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);
          $this->objModelUrls->insertUrl($this->strUrl, $objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);
        }
      }

      $this->load($intElementId, $strType, $strElementId, $intVersion);
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * removeUrlHistory()
   * @param integer $intElementId
   * @param string $strType
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function removeUrlHistory($intElementId, $strType){
    try{
      $this->strType = $strType;
      
      $this->getModel();
      $this->getModelUrls();
      
      $objItemData = $this->objModel->load($intElementId);
      if(count($objItemData) > 0){
        $objItem = $objItemData->current();        
        $this->getModelUrls()->removeUrlHistory($objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);
      }
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  

  /**
   * load()
   * @param integer $intElementId
   * @param string $strType
   * @param string $strElementId
   * @param integet $intVersion
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function load($intElementId, $strType, $strElementId = null, $intVersion = null){
    try{
      $this->strType = $strType;

      $this->getModel();
      $this->getModelUrls();

      $objItemData = $this->objModel->load($intElementId);

      if(count($objItemData) > 0){
        $objItem = $objItemData->current();

        $objUrlData = $this->objModelUrls->loadUrl($objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);

        if(count($objUrlData) > 0){
          $objUrl = $objUrlData->current();
          $this->objElement->setValue('/'.strtolower($objUrl->languageCode).'/'.$objUrl->url);
          $this->objElement->url = $objUrl->url;
          $this->objElement->languageCode = $objUrl->languageCode;

          $this->objElement->blnIsStartElement = $this->objElement->Setup()->getIsStartElement(false);
          $this->objElement->intParentId = $this->objElement->Setup()->getParentId();
        }
      }

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * checkUrlUniqueness()
   * @param string $strUrl
   * @param integer $intUrlAddon = 0
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function checkUrlUniqueness($strUrl, $intUrlAddon = 0){
    $this->getModelUrls();

    if(rtrim($strUrl, '/') != $strUrl){
    	$strNewUrl = ($intUrlAddon > 0) ? rtrim($strUrl, '/').'-'.$intUrlAddon.'/' : $strUrl;    	
    }else{
      $strNewUrl = ($intUrlAddon > 0) ? $strUrl.'-'.$intUrlAddon : $strUrl;	
    }    
    
    $objUrlsData = $this->objModelUrls->loadByUrl($this->objElement->Setup()->getRootLevelId(), $strNewUrl);

    if(isset($objUrlsData->url) && count($objUrlsData->url) > 0){
      return $this->checkUrlUniqueness($strUrl, $intUrlAddon + 1);
    }else{
      return $strNewUrl;
    }
  }
    
  /**
   * setType
   * @param string $strType   
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function setType($strType){
  	$this->strType = $strType;
  }

  /**
   * getModel
   * @return type Model
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModel(){
    if($this->objModel === null) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      $strModelFilePath = GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.$this->objElement->Setup()->getModelSubPath().((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')).'ies' : ucfirst($this->strType).'s').'.php';
      $this->core->logger->debug($strModelFilePath);
      if(file_exists($strModelFilePath)){
        require_once $strModelFilePath;
        $strModel = 'Model_'.((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')).'ies' : ucfirst($this->strType).'s');
        $this->objModel = new $strModel();
        $this->objModel->setLanguageId($this->objElement->Setup()->getLanguageId());
      }else{
        throw new Exception('Not able to load type specific model, because the file didn\'t exist! - strType: "'.$this->strType.'"');
      }
    }
    return $this->objModel;
  }

  /**
   * getModelUrls
   * @return Model_Urls
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelUrls(){
    if (null === $this->objModelUrls) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Urls.php';
      $this->objModelUrls = new Model_Urls();
      $this->objModelUrls->setLanguageId($this->objElement->Setup()->getLanguageId());
    }

    return $this->objModelUrls;
  }
}
?>