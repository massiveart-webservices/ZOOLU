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
 * @package    application.zoolu.modules.users.views.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * NavigationHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-01-05: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class NavigationHelper {

  /**
   * @var Core
   */
  private $core;

  /**
   * Constructor
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  }

	/**
   * getMainNavigation
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getMainNavigation(NavigationTree $rootLevelNavigation, $rootLevelId, $strViewType = '') {
    $this->core->logger->debug('contacts->views->helpers->NavigationHelper->getMainNavigation(): rootLevelId: '.$rootLevelId.' - strViewType: '.$strViewType);
    
  	$strOutput = '';
  	
    foreach ($rootLevelNavigation as $objNavigationTree) {
      
      if(count($objNavigationTree) == 1){        
        foreach($objNavigationTree as $objNavigation){
          
          /**
           * get values of the row and create output
           */
          $strJsClickFunc = '';
          $strRootLevelIconCss = '';
          $strRootLevelType = '';
          
        	switch ($objNavigation->getTypeId()) {
    				case $this->core->sysConfig->root_level_types->contacts:
  				    $strRootLevelType = 'contact';
    				  $strJsClickFunc = 'myNavigation.selectContacts('.$objNavigation->getId().', '.$objNavigationTree->getTypeId().', \''.$objNavigation->getUrl().'\', true, \''.$strViewType.'\'); return false;';
  				    $strRootLevelIconCss = 'usericon';
  				    break;
    				case $this->core->sysConfig->root_level_types->locations:
              $strRootLevelType = 'location'; 
    				  $strJsClickFunc = 'myNavigation.selectLocations('.$objNavigation->getId().', '.$objNavigationTree->getTypeId().', \''.$objNavigation->getUrl().'\', true, \''.$strViewType.'\'); return false;';
              $strRootLevelIconCss = 'locationicon'; 
              break; 
            case $this->core->sysConfig->root_level_types->members:
              $strRootLevelType = 'member';
              $strJsClickFunc = 'myNavigation.selectMembers('.$objNavigation->getId().', '.$objNavigationTree->getTypeId().', \''.$objNavigation->getUrl().'\', \''.$strViewType.'\', \''.$strRootLevelType.'\'); return false;';
              $strRootLevelIconCss = 'usericon';
              break;
            case $this->core->sysConfig->root_level_types->companies:
              $strRootLevelType = 'company';
              $strJsClickFunc = 'myNavigation.selectCompanies('.$objNavigation->getId().', '.$objNavigationTree->getTypeId().', \''.$objNavigation->getUrl().'\', \''.$strViewType.'\', \''.$strRootLevelType.'\'); return false;';
              $strRootLevelIconCss = 'locationicon';
              break;
            case $this->core->sysConfig->root_level_types->subscribers:
              $strRootLevelType = 'subscriber';
              $strJsClickFunc = 'myNavigation.selectSubscribers('.$objNavigation->getId().', '.$objNavigationTree->getTypeId().', \''.$objNavigation->getUrl().'\', \''.$strViewType.'\', \''.$strRootLevelType.'\'); return false;';
              $strRootLevelIconCss = 'usericon';
              break;
        	}
          
          if(Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$objNavigation->getId(), Security::PRIVILEGE_VIEW, true, false)){
            $strSelected = '';
            if($rootLevelId == $objNavigation->getId()){
              $strSelected = ' selected';
             
              $strOutput .= '
              <script type="text/javascript">//<![CDATA[
                var preSelectedNaviItem = \'naviitem'.$objNavigation->getId().'\';
              </script>';
            }      
            
            $strOutput .= '
              <div class="naviitemcontainer">
                <div id="naviitem'.$objNavigation->getId().'" class="naviitem'.$strSelected.'" onclick="'.$strJsClickFunc.'">
                  <div class="'.$strRootLevelIconCss.'"></div>
                  <div id="divRootLevelTitle_'.$objNavigation->getId().'" class="itemtitle">';
            if($objNavigation->getTypeId() ==  $this->core->sysConfig->root_level_types->subscribers){
              $strOutput .= '
                    <div class="gear" onclick="myNavigation.getRootLevelActions('.$objNavigation->getId().','.$objNavigation->getTypeId().', this); return false;"></div>';  
            }
            $strOutput .= htmlentities($objNavigation->getTitle(), ENT_COMPAT, $this->core->sysConfig->encoding->default).'
                  </div>
                  <div class="clear"></div>
                  <input type="hidden" value="'.$objNavigationTree->getItemId().'" id="rootLevelGroupKey'.$objNavigationTree->getTypeId().'"/>
                  <input type="hidden" value="'.$objNavigation->getLanguageId().'" id="rootLevelLanguageId'.$objNavigation->getId().'"/> 
                  <input type="hidden" value="'.$strRootLevelType.'" id="rootLevelType'.$objNavigation->getId().'"/>             
                </div>';
            if($objNavigation->getTypeId() ==  $this->core->sysConfig->root_level_types->subscribers){
              $strOutput .= '<div class="menu" id="naviitem'.$objNavigation->getId().'menu" style="display:none;"></div>';
            }
            $strOutput .= '
                <div class="clear"></div>
          	</div>';
          }
        }
      }else{         
        $strSubNavi = '';
        $strDisplaySubNavi = ' style="display:none;"';
        $strSubNaviSelected = '';
        foreach($objNavigationTree as $objNavigation){
          
          /**
           * get values of the row and create output
           */
          $strJsClickFunc = '';
          $strRootLevelIconCss = '';
          $strRootLevelType = '';
          
        	switch ($objNavigation->getTypeId()) {
    				case $this->core->sysConfig->root_level_types->contacts:
  				    $strRootLevelType = 'contact';
    				  $strJsClickFunc = 'myNavigation.selectContacts('.$objNavigation->getId().', '.$objNavigationTree->getTypeId().', \''.$objNavigation->getUrl().'\', true, \''.$strViewType.'\'); return false;';
  				    $strRootLevelIconCss = 'portalcontenticon';
  				    break;
    				case $this->core->sysConfig->root_level_types->locations:
              $strRootLevelType = 'location'; 
    				  $strJsClickFunc = 'myNavigation.selectLocations('.$objNavigation->getId().', '.$objNavigationTree->getTypeId().', \''.$objNavigation->getUrl().'\', true, \''.$strViewType.'\'); return false;';
              $strRootLevelIconCss = 'portalcontenticon'; 
              break; 
            case $this->core->sysConfig->root_level_types->members:
              $strRootLevelType = 'member';
              $strJsClickFunc = 'myNavigation.selectMembers('.$objNavigation->getId().', '.$objNavigationTree->getTypeId().', \''.$objNavigation->getUrl().'\', \''.$strViewType.'\', \''.$strRootLevelType.'\'); return false;';
              $strRootLevelIconCss = 'portalcontenticon';
              break;
            case $this->core->sysConfig->root_level_types->companies:
              $strRootLevelType = 'company';
              $strJsClickFunc = 'myNavigation.selectCompanies('.$objNavigation->getId().', '.$objNavigationTree->getTypeId().', \''.$objNavigation->getUrl().'\', \''.$strViewType.'\', \''.$strRootLevelType.'\'); return false;';
              $strRootLevelIconCss = 'portalcontenticon';
              break;
        	}
          
          if(Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$objNavigation->getId(), Security::PRIVILEGE_VIEW, true, false)){
            $strSelected = '';
            if($rootLevelId == $objNavigation->getId()){
              $strSelected = ' selected';
              $strSubNaviSelected = ' selected';
              $strDisplaySubNavi = '';
              
              $strSubNavi .= '
              <script type="text/javascript">//<![CDATA[
                var preSelectedNaviItem = \'naviitem'.$objNavigationTree->getId().'\';
                var preSelectedSubNaviItem = \'subnaviitem'.$objNavigation->getId().'\';
              </script>';
            }
            
            $strSubNavi .= '          
              <div id="subnaviitem'.$objNavigation->getId().'" class="menulink'.$strSelected.'">
                <div class="'.$strRootLevelIconCss.'"></div>
                <div class="menutitle">
                	<a id="subnaviitem'.$objNavigation->getId().'_link" onclick="'.$strJsClickFunc.'" href="#">'.htmlentities($objNavigation->getTitle(), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a>
               	  <input type="hidden" value="'.$strRootLevelType.'" id="rootLevelType'.$objNavigation->getId().'"/>  
                </div>
                <div class="clear"></div>
              </div>';
          }
        }
        
        if($strSubNavi != ''){
          $strOutput .= '
          <div class="naviitemcontainer">
            <div id="naviitem'.$objNavigationTree->getId().'" class="naviitem'.$strSubNaviSelected.'" onclick="myNavigation.selectRootLevel('.$objNavigationTree->getId().', '.$objNavigationTree->getTypeId().', \'\', false, \''.$strViewType.'\'); return false;">
              <div class="usericon"></div>
              <div id="divRootLevelTitle_'.$objNavigationTree->getId().'" class="itemtitle">'.htmlentities($objNavigationTree->getTitle(), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
              <div class="clear"></div>  
              <input type="hidden" value="'.$objNavigationTree->getItemId().'" id="rootLevelGroupKey'.$objNavigationTree->getTypeId().'"/>
              <input type="hidden" value="'.$objNavigationTree->getLanguageId().'" id="rootLevelLanguageId'.$objNavigationTree->getId().'"/>
              
            </div>
            <div id="naviitem'.$objNavigationTree->getId().'menu" class="menu"'.$strDisplaySubNavi.'>
            '.$strSubNavi.'
            </div>
            <div class="clear"></div>
          </div>';
        }        
      }  
    }
    
    return $strOutput;
  }
  
  /**
   * getContactNavElements 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  function getContactNavElements($objRowset, $currLevel) {
    $this->core->logger->debug('contacts->views->helpers->NavigationHelper->getContactNavElements()');
    
    $strOutput = '';
    $strOutputStartpage = '';
    
    $counter = 1;
    
    if(count($objRowset) > 0){
      foreach ($objRowset as $objRow){
        switch($objRow->type){
          case 'unit':
            $strOutput .= '
              <div id="'.$objRow->type.$objRow->id.'" class="'.$objRow->type.' hoveritem">
                <div id="divNavigationEdit_'.$objRow->id.'" class="icon img_'.$objRow->type.'" ondblclick="myNavigation.getEditForm('.$objRow->id.', \''.$objRow->type.'\', \''.$objRow->genericFormId.'\','.$objRow->version.'); return false;"></div>
                <div id="divNavigationTitle_'.$objRow->type.$objRow->id.'" class="title" onclick="myNavigation.selectNavigationItem('.$currLevel.', \''.$objRow->type.'\', '.$objRow->id.'); return false;">'.htmlentities($objRow->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
              </div>';
            break;
          case 'contact':
            $strOutput .= '
              <div id="'.$objRow->type.$objRow->id.'" class="'.$objRow->type.' hoveritem">
                <div class="icon img_'.$objRow->type.'"></div>
                <div id="divNavigationTitle_'.$objRow->type.$objRow->id.'" class="title" onclick="myNavigation.getEditForm('.$objRow->id.',\''.$objRow->type.'\',\''.$objRow->genericFormId.'\','.$objRow->version.'); return false;">'.htmlentities($objRow->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
              </div>';
            break;
        }
      }
    }
     
    return $strOutput;  
  }
  
  /**
   * getLocationNavElements 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  function getLocationNavElements($objRowset, $currLevel) {
    $this->core->logger->debug('contacts->views->helpers->NavigationHelper->getLocationNavElements()');
    
    $strOutput = '';
    $strOutputStartpage = '';
    
    $counter = 1;
    
    if(count($objRowset) > 0){
      foreach ($objRowset as $objRow){
        switch($objRow->type){
          case 'unit':
            $strOutput .= '
              <div id="'.$objRow->type.$objRow->id.'" class="'.$objRow->type.' hoveritem">
                <div id="divNavigationEdit_'.$objRow->id.'" class="icon img_'.$objRow->type.'" ondblclick="myNavigation.getEditForm('.$objRow->id.', \''.$objRow->type.'\', \''.$objRow->genericFormId.'\','.$objRow->version.'); return false;"></div>
                <div id="divNavigationTitle_'.$objRow->type.$objRow->id.'" class="title" onclick="myNavigation.selectNavigationItem('.$currLevel.', \''.$objRow->type.'\', '.$objRow->id.'); return false;">'.htmlentities($objRow->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
              </div>';
            break;
          case 'location':
            $strOutput .= '
              <div id="'.$objRow->type.$objRow->id.'" class="'.$objRow->type.' hoveritem">
                <div class="icon img_'.$objRow->type.'"></div>
                <div id="divNavigationTitle_'.$objRow->type.$objRow->id.'" class="title" onclick="myNavigation.getEditForm('.$objRow->id.',\''.$objRow->type.'\',\''.$objRow->genericFormId.'\','.$objRow->version.'); return false;">'.htmlentities($objRow->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
              </div>';
            break;
        }
      }
    }
     
    return $strOutput;  
  }
}

?>