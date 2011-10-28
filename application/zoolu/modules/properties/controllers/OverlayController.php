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
 * @package    application.zoolu.modules.core.properties.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * OverlayController
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-15: Cornelius Hansjakob
 * 
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Properties_OverlayController extends AuthControllerAction {
	
	private $intRootLevelId;
  private $intFolderId;
  
  private $arrFileIds = array();
	
	/**
   * @var Model_Folders
   */
  protected $objModelFolders;
  
  /**
   * @var Model_Files
   */
  protected $objModelFiles;
	
	/**
   * indexAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function indexAction(){ }

  /**
   * mediaAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function mediaAction(){
    $this->core->logger->debug('properties->controllers->OverlayController->mediaAction()');    
    $this->loadRootNavigation();    
    $this->view->assign('overlaytitle', $this->core->translate->_('Assign_medias'));
    $this->view->assign('viewtype', $this->core->sysConfig->viewtypes->thumb);
    $this->renderScript('overlay/overlay.phtml');       
  }
  
  /**
   * documentAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function documentAction(){
    $this->core->logger->debug('properties->controllers->OverlayController->documentAction()');    
    $this->loadRootNavigation();    
    $this->view->assign('overlaytitle', $this->core->translate->_('Assign_documents'));
    $this->view->assign('viewtype', $this->core->sysConfig->viewtypes->list);
    $this->renderScript('overlay/overlay.phtml');       
  }
  
  /**
   * thumbviewAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function thumbviewAction(){
    $this->core->logger->debug('properties->controllers->OverlayController->thumbviewAction()');	
  	
    $objRequest = $this->getRequest();
    $intFolderId = $objRequest->getParam('folderId');
    $strFileIds = $objRequest->getParam('fileIds');

    $strTmpFileIds = trim($strFileIds, '[]');
    $this->arrFileIds = split('\]\[', $strTmpFileIds);
    
    /**
     * get files
     */
    $this->getModelFiles();
    $objFiles = $this->objModelFiles->loadFiles($intFolderId);
    
    $this->view->assign('objFiles', $objFiles);
    $this->view->assign('arrFileIds', $this->arrFileIds);    
  }
  
  /**
   * listviewAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function listviewAction(){
    $this->core->logger->debug('properties->controllers->OverlayController->listviewAction()'); 
    
    $objRequest = $this->getRequest();
    $intFolderId = $objRequest->getParam('folderId');
    $strFileIds = $objRequest->getParam('fileIds');

    $strTmpFileIds = trim($strFileIds, '[]');
    $this->arrFileIds = split('\]\[', $strTmpFileIds);
    
    /**
     * get files
     */
    $this->getModelFiles();
    $objFiles = $this->objModelFiles->loadFiles($intFolderId);
    
    $this->view->assign('objFiles', $objFiles);
    $this->view->assign('arrFileIds', $this->arrFileIds);
  }
  
  /**
   * childnavigationAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function childnavigationAction(){
    $this->core->logger->debug('properties->controllers->OverlayController->childnavigationAction()');
    
    $this->getModelFolders();
    
    $objRequest = $this->getRequest();
    $this->intFolderId = $objRequest->getParam("folderId");
    $viewtype = $objRequest->getParam("viewtype");
    
    /**
     * get childfolders
     */
    $objChildelements = $this->objModelFolders->loadChildFolders($this->intFolderId);
        
    $this->view->assign('elements', $objChildelements);
    $this->view->assign('intFolderId', $this->intFolderId);
    $this->view->assign('viewtype', $viewtype);
  }
  
  /**
   * loadRootNavigation
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function loadRootNavigation(){
    $this->core->logger->debug('properties->controllers->OverlayController->loadRootNavigation()');
    
    $this->getModelFolders();    
    $objMediaRootLevels = $this->objModelFolders->loadAllRootLevels($this->core->sysConfig->modules->media);
    
    if(count($objMediaRootLevels) == 1){
      $objMediaRootLevel = $objMediaRootLevels->current();
      $this->intRootLevelId = $objMediaRootLevel->id;
      $objRootelements = $this->objModelFolders->loadRootFolders($this->intRootLevelId);
      
      $this->view->assign('elements', $objRootelements);
      $this->view->assign('rootLevelId', $this->intRootLevelId);
    }else{
      //TODO : create media root levels navigation
    }	
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
      $this->objModelFolders->setLanguageId(1); // TODO : get language id
    }
    
    return $this->objModelFolders;
  }
  
  /**
   * getModelFiles
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelFiles(){
    if (null === $this->objModelFiles) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it 
       * from its modules path location.
       */ 
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Files.php';
      $this->objModelFiles = new Model_Files();
      $this->objModelFiles->setLanguageId(1); // TODO : get language id
    }
    
    return $this->objModelFiles;
  }
  
  /**
   * setRootLevelId
   * @param integer $intRootLevelId
   */
  public function setRootLevelId($intRootLevelId){
    $this->intRootLevelId = $intRootLevelId;  
  }
  
  /**
   * getRootLevelId
   * @param integer $intRootLevelId
   */
  public function getRootLevelId(){
    return $this->intRootLevelId;  
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
   * @param integer $intFolderId
   */
  public function getFolderId(){
    return $this->intFolderId;  
  }
	
}

?>