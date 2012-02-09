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
 * @package    library.massiveart.generic.forms.validators
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

require_once(dirname(__FILE__).'/Abstract.php');

/**
 * Form_Validator_UniqueUrl
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2011-09-20: Daniel Rotter
 * 
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */
class Form_Validator_UniqueUrl extends Form_Validator_Abstract {
  /**
   * @var Model_Urls
   */
  protected $objModelUrls;
  /**
   * @var Model_Globals
   */
  protected $objModelGlobals;
  /**
   * @var Model_Pages
   */
  protected $objModelPages;
  /**
   * @var Model_Folders
   */
  protected $objModelFolders;
  /**
   * @var array
   */
  protected $_arrMessages;
  
  /**
   * getMessages
   * @see Zend_Validate_Interface
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function getMessages(){
    return $this->_arrMessages;
  }
  
  /**
   * addMessage
   * @param string $strKey
   * @param string $strMessage
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function addMessage($strKey, $strMessage){
    $this->_arrMessages[$strKey] = $strMessage;
  }
  
  /**
   * isValid
   * @see Zend_Validate_Interface
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function isValid($value){
    $strValue = strtolower($value);
    
    $isValid = true;
    
    //Cut language
    $strValueWithoutLanguage = preg_replace('/^\/[a-zA-Z\-]{2,5}\//', '', $strValue);
    
    //Load data
    $objItemData;
    $strType;
    $intElementId = ($this->Setup()->getElementLinkId()) ? $this->Setup()->getElementLinkId() : $this->Setup()->getElementId();
    if($intElementId){
      switch($this->Setup()->getFormTypeId()) {
        case $this->core->sysConfig->form->types->page:
          $strType = 'page';
          $objItemData = $this->getModelPages()->load($intElementId);
          break;
        case $this->core->sysConfig->form->types->global:
          $strType = 'global';
          $objItemData = $this->getModelGlobals()->load($intElementId);
          break;
      }
    }
    
    if($this->Setup()->getIsStartElement(false) && $strValueWithoutLanguage != '') $strValueWithoutLanguage = rtrim($strValueWithoutLanguage, '/').'/';
    
    //Check if the url existed and has changed
    if(isset($objItemData) && count($objItemData) > 0){
      $objItem = $objItemData->current();
      $objUrlData = $this->getModelUrls()->loadUrl($objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);
      
      if(count($objUrlData) > 0){
        $objUrl = $objUrlData->current();
        if(strcmp($strValueWithoutLanguage, $objUrl->url) !== 0){
          //If changed, check if new url is free
          $isValid = $this->checkUniqueness($strValueWithoutLanguage);
        }
      }else{
        //Page without URL, check with title as URL
        $isValid = $this->checkPageWithoutUrl($strValueWithoutLanguage);
      }
    }else{
      //New Page, check with title as URL
      $isValid = $this->checkPageWithoutUrl($strValueWithoutLanguage);
    }
    //If url is not valid, make a suggestion
    if(!$isValid){
      $this->addMessage('errMessage', $this->core->translate->_('Err_existing_url'));
      //Build suggestion
      $strSuggestion = $this->buildUniqueUrl($_POST['parentFolderId'], $strValueWithoutLanguage);
      $this->addMessage('suggestion', $strSuggestion);
    }

    return $isValid;
  }

  /**
   * checkpageWithoutUrl
   * @param string $strValue
   * @return boolean 
   */
  private function checkPageWithoutUrl(&$strValue){
    $strValue = ($strValue == '') ? str_replace('/', '-', $_POST['title']) : $strValue;
    if($this->Setup()->getIsStartElement(false)){
      $strValue = rtrim($strValue, '/').'/';
    }
    return $this->checkUniqueness($strValue);
  }
  
  /**
   * checkUniqueness
   * @param string $strUrl
   * @return boolean
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  protected function checkUniqueness($strUrl){
    $blnReturn = true;
    $objUrls = $this->getModelUrls()->loadByUrl($this->Setup()->getRootLevelId(), $this->getModelUrls()->makeUrlConform($strUrl), $this->Setup()->getFormType());
    if(isset($objUrls->url) && count($objUrls->url) > 0){
      $blnReturn = false;
    }
    return $blnReturn;
  }
  
  /**
   * buildUniqueUrl
   * @param number $intParentFolderId
   * @param string $strValue
   * @return Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  protected function buildUniqueUrl($intParentFolderId, $strValue){
    $strUrl = $this->getModelUrls()->makeUrlConform(strtolower($strValue));
    
    $objParentFolders;
    switch($this->Setup()->getFormTypeId()) {
      case $this->core->sysConfig->form->types->page:
        $objParentFolders = $this->getModelFolders()->loadParentFolders($intParentFolderId);
        break;
      case $this->core->sysConfig->form->types->global:
        $objParentFolders = $this->getModelFolders()->loadGlobalParentFolders($intParentFolderId, $this->Setup()->getRootLevelGroupId());
        break;
    }
    
    $blnFirst = true;
    
    foreach($objParentFolders as $objParentFolder){
      if(!($blnFirst && $this->Setup()->getIsStartElement(false))){
        $strUrl = $this->getModelUrls()->makeUrlConform(strtolower($objParentFolder->title)) . '/' . $strUrl;
        if($this->checkUniqueness($strUrl)){
          break;
        }
      }
      $blnFirst = false;
    }
    if(!$this->checkUniqueness($strUrl)){
      $this->addMessage('buildMessage', $this->core->translate->_('err_no_unique_url'));
    }
    return $strUrl;
  }
  
  /**
   * getModelUrls
   * @return Model_Urls
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.1
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
      $this->objModelUrls->setLanguageId($this->Setup()->getLanguageId());
    }

    return $this->objModelUrls;
  }
  
  /**
   * getModelPages
   * @return Model_Pages
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelPages(){
    if (null === $this->objModelPages) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'cms/models/Pages.php';
      $this->objModelPages = new Model_Pages();
      $this->objModelPages->setLanguageId($this->Setup()->getLanguageId());
    }

    return $this->objModelPages;
  }
  
  /**
   * getModelGlobals
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelGlobals(){
    if (null === $this->objModelGlobals) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'global/models/Globals.php';
      $this->objModelGlobals = new Model_Globals();
      $this->objModelGlobals->setLanguageId($this->Setup()->getLanguageId());
    }
  
    return $this->objModelGlobals;
  }
  
  /**
   * getModelFolders
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelFolders(){
    if (null === $this->objModelFolders) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Folders.php';
      $this->objModelFolders = new Model_Folders();
      $this->objModelFolders->setLanguageId($this->Setup()->getLanguageId());
    }

    return $this->objModelFolders;
  }
}
?>