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
 * @package    application.zoolu.modules.core.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * UrlHistoryController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-11-06: Dominik Mößlang
 * 1.1, 2009-12-07: Thomas Schedler
 *
 * @author Dominik Mößlang <dmo@massiveart.com>
 * @version 1.0
 */

class Core_UrlController extends AuthControllerAction {

  protected $strType;
  protected $strModelSubPath;
  protected $intItemId;

	/**
	 * @var Model_Urls
	 */
	protected $objModelUrls;
	
  /**
   * @var Model_Pages|Model_Products
   */
  protected $objModel;
    
  /**
   * indexAction
   * @author Dominik Mößlang <dmo@massiveart.com>
   * @version 1.0
   */
  public function indexAction(){
  }

  /**
   * geturlhistory
   * @author Dominik Mößlang <dmo@massiveart.com>
   * @version 1.0
   */
  public function geturlhistoryAction(){
   // $this->_helper->viewRenderer->setNoRender();
    $this->core->logger->debug('core->controllers->UrlController->geturlhistoryAction()');

    try{
      $objRequest = $this->getRequest();
      
      $strElementId = $objRequest->getParam('elementId');
      $intLanguageId = $objRequest->getParam('languageId');

      $this->evalModuleType();

      $this->view->objUrls = $this->getModel()->loadUrlHistory($this->intItemId, $intLanguageId);
      $this->view->strElementId = $strElementId;
            
    }catch (Exception $exc){
      $this->core->logger->err($exc);
      exit();
    }
  }
  
  /**
   * removeUrlHistoryEntry
   * @return Model_Modules
   * @author Dominik Mößlang <dmo@massiveart.com>
   * @version 1.0
   */
  public function removeurlhistoryentryAction(){
    $this->core->logger->debug('core->controllers->UrlController->removeUrlHistoryEntry()');

    $this->_helper->viewRenderer->setNoRender();
    try{
      $objRequest = $this->getRequest();
      $intUrlId = $objRequest->getParam('urlId');
      $strRelationId = $objRequest->getParam('relationId');
            
      return $this->getModelUrls()->removeUrlHistoryEntry($intUrlId, $strRelationId);
               
    }catch (Exception $exc){
      $this->core->logger->err($exc);
      exit();
    }
  }
  
  /**
   * evalModuleType
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.1
   */
  protected function evalModuleType(){
    switch($this->getRequest()->getParam('moduleId')){
      case $this->core->sysConfig->modules->cms:
        $this->strType = 'page';
        $this->strModelSubPath = 'cms/models/';
        $this->intItemId = $this->getRequest()->getParam('id');
        break;
      case $this->core->sysConfig->modules->global:
        $this->strType = 'global';
        $this->strModelSubPath = 'global/models/';
        $this->intItemId = ($this->getRequest()->getParam('linkId') > 0 ? $this->getRequest()->getParam('linkId') : $this->getRequest()->getParam('id'));
        break;
    }
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
    }

    return $this->objModelUrls;
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
      $strModelFilePath = GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.$this->strModelSubPath.((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')).'ies' : ucfirst($this->strType).'s').'.php';
      if(file_exists($strModelFilePath)){
        require_once $strModelFilePath;
        $strModel = 'Model_'.((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')).'ies' : ucfirst($this->strType).'s');
        $this->objModel = new $strModel();
        $this->objModel->setLanguageId($this->getRequest()->getParam("languageId", $this->core->intZooluLanguageId));
      }else{
        throw new Exception('Not able to load type specific model, because the file didn\'t exist! - strType: "'.$this->strType.'"');
      }
    }
    return $this->objModel;
  }
  
}

?>
