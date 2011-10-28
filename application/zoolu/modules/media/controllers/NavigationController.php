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
 * @package    application.zoolu.modules.core.media.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Media_NavigationController
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-06: Thomas Schedler
 * 
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Media_NavigationController extends AuthControllerAction {

  private $intPortalId;
  private $intFolderId;
  
  private $intParentId;
  private $intParentTypeId;
  
  private $intLanguageId;
  
  /**
   * @var Model_Folders
   */
  protected $objModelFolders;
  
  /**
   * init
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   * @return void
   */
  public function init(){
    parent::init();
    Security::get()->addFoldersToAcl($this->getModelFolders());
    Security::get()->addRootLevelsToAcl($this->getModelFolders(), $this->core->sysConfig->modules->media);
  }
  
  /**
   * indexAction
   */
  public function indexAction(){
    $objMediaRootLevels = $this->getModelFolders()->loadAllRootLevels($this->core->sysConfig->modules->media);
    
    $this->view->assign('mediaTypes', $objMediaRootLevels);
    $this->view->assign('folderFormDefaultId', $this->core->sysConfig->form->ids->folders->default);
    $this->view->assign('fileDefaultDescription', $this->core->translate->_('Add_description_', false));
    $this->view->assign('currLevel', 1);
  }
  
  /**
   * rootnavigationAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function rootnavigationAction(){
    $this->core->logger->debug('media->controllers->NavigationController->rootnavigationAction()');
    
    $objRequest = $this->getRequest();
    $intCurrLevel = $objRequest->getParam("currLevel");
    $this->setPortalId($objRequest->getParam("rootLevelId"));
    
    /**
     * get navigation
     */
    $this->getModelFolders();
    $objRootelements = $this->objModelFolders->loadRootNavigation($this->intPortalId);
    
    $this->view->assign('rootelements', $objRootelements);
    $this->view->assign('currLevel', $intCurrLevel);
    
  }
  
  /**
   * childnavigationAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function childnavigationAction(){
    $this->core->logger->debug('media->controllers->NavigationController->childnavigationAction()');
    
    $objRequest = $this->getRequest();
    $intCurrLevel = $objRequest->getParam("currLevel");
    $this->setFolderId($objRequest->getParam("folderId"));
    
    /**
     * get childnavigation
     */
    $this->getModelFolders();
    $objChildelements = $this->objModelFolders->loadChildNavigation($this->intFolderId);
    
    $this->view->assign('childelements', $objChildelements);
    $this->view->assign('currLevel', $intCurrLevel);    
  }
  
  /**
   * parentFoldersAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function parentFoldersAction(){
    $this->core->logger->debug('media->controllers->NavigationController->parentFoldersAction()');
    $this->_helper->viewRenderer->setNoRender();
    
    $intId = $this->getRequest()->getParam('id', null);
    $intParentId = $this->getRequest()->getParam('parentId', null);
    
    $arrReturn = array();
    if($intId !== null && $intId > 0){
      if($intParentId !== null && $intParentId > 0){
        $arrParentFolders = $this->getModelFolders()->loadMediaParentFolders($intParentId);
        
        $arrParentFolders = array_reverse($arrParentFolders);
        
        if(count($arrParentFolders) > 0){
          foreach($arrParentFolders as $objParentFolder){
            $arrReturn['folders'][] = $objParentFolder->id;  
          }
        }
      }else{
        // no parent folders
        $arrReturn['folders'][] = '';  
      }      
    }
    
    $this->_response->setBody(json_encode($arrReturn));
  }
  
  /**
   * getModelFolders
   * @return Model_Folders
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
      $this->objModelFolders->setLanguageId($this->core->intZooluLanguageId);
    }
    
    return $this->objModelFolders;
  }
  
  /**
   * setPortalId
   * @param integer $intPortalId
   */
  public function setPortalId($intPortalId){
    $this->intPortalId = $intPortalId;  
  }
  
  /**
   * getPortalId
   * @param integer $intPortalId
   */
  public function getPortalId(){
    return $this->intPortalId;  
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
  
  /**
   * setParentId
   * @param integer $intParentId
   */
  public function setParentId($intParentId){
    $this->intParentId = $intParentId;  
  }
  
  /**
   * getParentId
   * @param integer $intParentId
   */
  public function getParentId(){
    return $this->intParentId;  
  }
  
  /**
   * setParentTypeId
   * @param integer $intParentTypeId
   */
  public function setParentTypeId($intParentTypeId){
    $this->intParentTypeId = $intParentTypeId;  
  }
  
  /**
   * getParentTypeId
   * @param integer $intParentTypeId
   */
  public function getParentTypeId(){
    return $this->intParentTypeId;  
  }
  
  /**
   * setLanguageId
   * @param integer $intLanguageId
   */
  public function setLanguageId($intLanguageId){
    $this->intLanguageId = $intLanguageId;  
  }
  
  /**
   * getLanguageId
   * @param integer $intLanguageId
   */
  public function getLanguageId(){
    return $this->intLanguageId;  
  }
}

?>