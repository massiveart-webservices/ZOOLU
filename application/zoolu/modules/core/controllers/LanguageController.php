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
 * @package    application.zoolu.modules.core.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * LanguageController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-09-14: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

class Core_LanguageController extends AuthControllerAction {
  
  /**
   * @var Model_Languages
   */
  protected $objModelLanguages;
  
  public function getcopylanguagesAction(){
    $intRootLevelId = $this->getRequest()->getParam('rootLevelId');
    $intModuleId = $this->getRequest()->getParam('moduleId');
    $intSrcLanguage = $this->getRequest()->getParam('srcLanguage');
    
    $objLanguages = null;
    if($intModuleId == $this->core->sysConfig->modules->global){
      $objLanguages = $this->getModelLanguages()->loadLanguages(null, array($intSrcLanguage));
    }elseif($intModuleId == $this->core->sysConfig->modules->cms){
      $objLanguages = $this->getModelLanguages()->loadLanguages($intRootLevelId, array($intSrcLanguage));
    }

    $arrSecurityCheck = array('ResourceKey'           => Security::RESOURCE_ROOT_LEVEL_PREFIX.$intRootLevelId.'_%d', 
                          'Privilege'             => Security::PRIVILEGE_VIEW, 
                          'CheckForAllLanguages'  => false,
                          'IfResourceNotExists'   => false);  
                          
    $blnGeneralUpdateAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$intRootLevelId, Security::PRIVILEGE_UPDATE, false, false);
    $arrLanguages = array();
    foreach($objLanguages as $objLanguage){
      if(($blnGeneralUpdateAuthorization) || Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$intRootLevelId.'_'.$objLanguage->id, Security::PRIVILEGE_UPDATE, false, false)){
        $arrLanguages[] = array('title' => $objLanguage->title, 'id' => $objLanguage->id);
      }
    }

    $this->view->assign('languages', $arrLanguages);
    $this->view->assign('overlaytitle', $this->core->translate->_('language_copy'));
  }
  
  /**
   * getModelLanguages
   * @return Model_Languages
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelLanguages(){
    if (null === $this->objModelLanguages) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Languages.php';
      $this->objModelLanguages = new Model_Languages();
    }

    return $this->objModelLanguages;
  }
}
?>