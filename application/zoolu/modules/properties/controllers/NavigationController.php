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
 * NavigationController
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-15: Cornelius Hansjakob
 * 
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Properties_NavigationController extends AuthControllerAction {
  
	private $intRootLevelId;
	private $intFolderId;
	
	private $intParentId;	
	
	private $intLanguageId;
	
  /**
   * @var Model_Categories
   */
  protected $objModelCategories;
  
  /**
   * @var Model_Contacts
   */
  protected $objModelContacts;
  
  /**
   * @var Model_Locations
   */
  protected $objModelLocations;
  
  /**
   * @var Model_Folders
   */
  protected $objModelFolders;
  
  /**
   * indexAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function indexAction(){
    $objPropertiesRootLevels = $this->getModelFolders()->loadAllRootLevels($this->core->sysConfig->modules->properties);
    
    $this->view->assign('rootLevels', $objPropertiesRootLevels);
  	$this->view->assign('categoryFormDefaultId', $this->core->sysConfig->form->ids->categories->default);
  	$this->view->assign('unitFormDefaultId', $this->core->sysConfig->form->ids->units->default);
  	$this->view->assign('contactFormDefaultId', $this->core->sysConfig->form->ids->contacts->default);
  }

  /**
   * catnavigationAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function catnavigationAction(){
    $this->core->logger->debug('properties->controllers->NavigationController->rootnavigationAction()');
    
    $objRequest = $this->getRequest();
    $intCurrLevel = $objRequest->getParam('currLevel');
    $intCategoryTypeId = $objRequest->getParam('categoryTypeId');
    
    if($intCurrLevel == 1){
      $intItemId = 0;	
    }else{
      $intItemId = $objRequest->getParam("itemId");	
    }
    
    /**
     * get navigation
     */
    $this->getModelCategories();
    $objCatNavElements = $this->objModelCategories->loadCatNavigation($intItemId, $intCategoryTypeId);
    
    $this->view->assign('catelements', $objCatNavElements);
    $this->view->assign('currLevel', $intCurrLevel);
    $this->view->assign('categoryTypeId', $intCategoryTypeId);    
  }
  
  /**
   * contactnavigationAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function contactnavigationAction(){
    $this->core->logger->debug('properties->controllers->NavigationController->contactnavigationAction()');
    
    $objRequest = $this->getRequest();
    $intCurrLevel = $objRequest->getParam('currLevel');
    $intRootLevelId = $objRequest->getParam('rootLevelId');
    
    if($intCurrLevel == 1){
      $intItemId = 0; 
    }else{
      $intItemId = $objRequest->getParam("itemId"); 
    }
    
    /**
     * get navigation
     */
    $this->getModelContacts();
    $objContactNavElements = $this->objModelContacts->loadNavigation($intRootLevelId, $intItemId);
    
    $this->view->assign('elements', $objContactNavElements);
    $this->view->assign('currLevel', $intCurrLevel); 
  }
  
  /**
   * locationnavigationAction
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function locationnavigationAction(){
    $this->core->logger->debug('properties->controllers->NavigationController->locationnavigationAction()');
    
    $objRequest = $this->getRequest();
    $intCurrLevel = $objRequest->getParam('currLevel');
    $intRootLevelId = $objRequest->getParam('rootLevelId');
    
    if($intCurrLevel == 1){
      $intItemId = 0; 
    }else{
      $intItemId = $objRequest->getParam("itemId"); 
    }
    
    /**
     * get navigation
     */
    $this->getModelLocations();
    $objLocationNavElements = $this->objModelLocations->loadNavigation($intRootLevelId, $intItemId, true);
    
    $this->view->assign('elements', $objLocationNavElements);
    $this->view->assign('currLevel', $intCurrLevel);
    $this->view->assign('itemId', $intItemId);  
  }
  
  /**
   * getModelCategories
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelCategories(){
    if (null === $this->objModelCategories) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it 
       * from its modules path location.
       */ 
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Categories.php';
      $this->objModelCategories = new Model_Categories();
      $this->objModelCategories->setLanguageId(1); // TODO : get language id
    }
    
    return $this->objModelCategories;
  }
  
  /**
   * getModelContacts
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelContacts(){
    if (null === $this->objModelContacts) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it 
       * from its modules path location.
       */ 
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Contacts.php';
      $this->objModelContacts = new Model_Contacts();
      $this->objModelContacts->setLanguageId(1); // TODO : get language id
    }
    
    return $this->objModelContacts;
  }
  
  /**
   * getModelLocations
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  protected function getModelLocations(){
    if (null === $this->objModelLocations) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it 
       * from its modules path location.
       */ 
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Locations.php';
      $this->objModelLocations = new Model_Locations();
      $this->objModelLocations->setLanguageId(1); // TODO : get language id
    }
    
    return $this->objModelLocations;
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
