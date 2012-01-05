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
 * @package    application.zoolu.modules.cms.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * NavigationController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-15: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Cms_NavigationController extends AuthControllerAction {

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
   * @var Model_RootLevels
   */
  protected $objModelRootLevels;

  /**
   * init
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   * @return void
   */
  public function init(){
    parent::init();
    Security::get()->addFoldersToAcl($this->getModelFolders());
    Security::get()->addRootLevelsToAcl($this->getModelFolders(), $this->core->sysConfig->modules->cms);
  }

  /**
   * indexAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function indexAction(){

  	$this->getModelFolders();
    $objRootLevels = $this->objModelFolders->loadAllRootLevels($this->core->sysConfig->modules->cms);

    $objRootLevelNavigation = new NavigationTree();    
    if(count($objRootLevels) > 0){
      $intOrder = 0;
      foreach($objRootLevels as $objRootLevel){
        $intOrder++;
        
        if(!$objRootLevelNavigation->hasSubTree('order_'.$objRootLevel->order)){
          $objNavGroup = new NavigationTree();
          $objNavGroup->setId($objRootLevel->order);
          $objNavGroup->setItemId($objRootLevel->id);
          $objNavGroup->setTypeId($objRootLevel->idRootLevelTypes);
          $objNavGroup->setTitle($objRootLevel->title);
          $objNavGroup->setUrl($objRootLevel->href);
          $objNavGroup->setLanguageId(((int) $objRootLevel->rootLevelGuiLanguageId > 0 ? $objRootLevel->rootLevelGuiLanguageId : $objRootLevel->rootLevelLanguageId));
          
          $objRootLevelNavigation->addTree($objNavGroup, 'order_'.$objRootLevel->order);
        }
                  
        $objNavItem = new NavigationItem();
        $objNavItem->setId($objRootLevel->id);
        $objNavItem->setItemId($objRootLevel->id);
        $objNavItem->setTypeId($objRootLevel->idRootLevelTypes);
        $objNavItem->setTitle($objRootLevel->title);
        $objNavItem->setUrl($objRootLevel->href);
        $objNavItem->setOrder($intOrder);
        $objNavItem->setParentId($objRootLevel->order);
        $objNavItem->setLanguageId(((int) $objRootLevel->rootLevelGuiLanguageId > 0 ? $objRootLevel->rootLevelGuiLanguageId : $objRootLevel->rootLevelLanguageId));
        $objNavItem->setHasLandingPages($objRootLevel->landingPages);
        
        $objRootLevelNavigation->addToParentTree($objNavItem, 'rootLevelId_'.$objRootLevel->id);
      }
    }
    
    $this->view->assign('rootLevelNavigation', $objRootLevelNavigation);
    $this->view->assign('rootLevelMaintenances', $this->loadActiveMaintenances());
    
  	$this->view->assign('folderFormDefaultId', $this->core->sysConfig->form->ids->folders->default);
  	$this->view->assign('folderBlogFormDefaultId', $this->core->sysConfig->form->ids->folders->blog);
  	$this->view->assign('pageFormDefaultId', $this->core->sysConfig->page_types->page->default_formId);
  	$this->view->assign('pageTemplateDefaultId', $this->core->sysConfig->page_types->page->default_templateId);
  	$this->view->assign('pageTypeDefaultId', $this->core->sysConfig->page_types->page->id);
    
    $strRenderSciprt = ($this->getRequest()->getParam('layoutType') == 'list') ? 'list.phtml' : 'tree.phtml';
    $this->renderScript('navigation/'.$strRenderSciprt);
  }

  /**
   * rootnavigationAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function rootnavigationAction(){
    $this->core->logger->debug('cms->controllers->NavigationController->rootnavigationAction()');

    $objRequest = $this->getRequest();
    $intCurrLevel = $objRequest->getParam("currLevel");
    $this->setPortalId($objRequest->getParam("rootLevelId"));

    if(Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$this->intPortalId, Security::PRIVILEGE_VIEW)){
      /**
       * get navigation
       */
      $this->getModelFolders();
      $objRootelements = $this->objModelFolders->loadRootNavigation($this->intPortalId);
  
      $this->view->assign('rootelements', $objRootelements);
      $this->view->assign('currLevel', $intCurrLevel);
      $this->view->assign('return', true);
    }else{
      $this->view->assign('return', false);
    }
  }

  /**
   * childnavigationAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function childnavigationAction(){
    $this->core->logger->debug('cms->controllers->NavigationController->childnavigationAction()');

    if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
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
  }

  /**
   * updatepositionAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function updatepositionAction(){
    $this->core->logger->debug('cms->controllers->NavigationController->updatepositionAction()');

    $this->getModelFolders();

    if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
      $objRequest = $this->getRequest();
      $intElementId = $objRequest->getParam("id");
      $strElementType = $objRequest->getParam("elementType");
      $intRootLevelTypeId = $objRequest->getParam("rootLevelTypeId");
      $intRootLevelGroupId = $objRequest->getParam("rootLevelGroupId");
      $intSortPosition = $objRequest->getParam("sortPosition");
      $this->setPortalId($objRequest->getParam("rootLevelId"));
      $this->setParentId($objRequest->getParam("parentId"));

      $this->objModelFolders->updateSortPosition($intElementId, $strElementType, $intSortPosition, $this->intPortalId, $this->intParentId, $intRootLevelTypeId, $intRootLevelGroupId);
    }

    /**
     * no rendering
     */
    $this->_helper->viewRenderer->setNoRender();
  }
  
  /**
   * parentFoldersAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function parentFoldersAction(){
    $this->core->logger->debug('cms->controllers->NavigationController->parentFoldersAction()');
    $this->_helper->viewRenderer->setNoRender();
    
    $intId = $this->getRequest()->getParam('id', null);
    $intParentId = $this->getRequest()->getParam('parentId', null);
    
    $arrReturn = array();
    if($intId !== null && $intId > 0){
      if($intParentId !== null && $intParentId > 0){
        $arrParentFolders = $this->getModelFolders()->loadParentFolders($intParentId);
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
   * loadActiveMaintenances
   * @return void
   */
  protected function loadActiveMaintenances(){    
    $arrMaintenances = array();
    
    $objActiveMaintenanceData = $this->getModelRootLevels()->loadActiveMaintenances();
    
    if(count($objActiveMaintenanceData) > 0){
      foreach($objActiveMaintenanceData as $objActiveMaintenance){
        if($objActiveMaintenance->maintenance_startdate != '' && $objActiveMaintenance->maintenance_enddate != ''){          
          if(time() >= strtotime($objActiveMaintenance->maintenance_startdate) && time() <= strtotime($objActiveMaintenance->maintenance_enddate)){
            $arrMaintenances[] = $objActiveMaintenance->idRootLevels;  
          }
        }else if($objActiveMaintenance->maintenance_startdate != '' && $objActiveMaintenance->maintenance_enddate == ''){            
          if(time() >= strtotime($objActiveMaintenance->maintenance_startdate)){
            $arrMaintenances[] = $objActiveMaintenance->idRootLevels; 
          }
        }else if($objActiveMaintenance->maintenance_startdate == '' && $objActiveMaintenance->maintenance_enddate != ''){            
          if(time() <= strtotime($objActiveMaintenance->maintenance_enddate)){
            $arrMaintenances[] = $objActiveMaintenance->idRootLevels;  
          }  
        }else{
          $arrMaintenances[] = $objActiveMaintenance->idRootLevels;
        }  
      }
    }    
    return $arrMaintenances;
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
      $this->objModelFolders->setLanguageId($this->getRequest()->getParam("languageId", (($this->getRequest()->getParam("rootLevelLanguageId") != '') ? $this->getRequest()->getParam("rootLevelLanguageId") : $this->core->intZooluLanguageId)));
    }

    return $this->objModelFolders;
  }
  
  /**
   * getModelRootLevels
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelRootLevels(){
    if (null === $this->objModelRootLevels) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/RootLevels.php';
      $this->objModelRootLevels = new Model_RootLevels();
    }

    return $this->objModelRootLevels;
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
