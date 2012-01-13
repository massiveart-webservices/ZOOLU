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
 * @package    application.zoolu.modules.global.views.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * NavigationHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-04-26: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class NavigationHelper {

  /**
   * @var Core
   */
  private $core;

  /**
   * Constructor
   * @author Thomas Schedler <tsh@massiveart.com>
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
    $this->core->logger->debug('newsletters->views->helpers->NavigationHelper->getMainNavigation()');

    $strOutput = '';
    
    foreach ($rootLevelNavigation as $objNavigationTree) {
    
      if(count($objNavigationTree) == 1){        
        foreach($objNavigationTree as $objNavigation){
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
            <div id="naviitem'.$objNavigation->getId().'" class="naviitem'.$strSelected.'" onclick="myNavigation.selectRootLevel('.$objNavigation->getId().', '.$objNavigationTree->getTypeId().', \''.$objNavigation->getUrl().'\', true, \''.$strViewType.'\'); return false;">
              <div class="producticon"></div>
              <div id="divRootLevelTitle_'.$objNavigation->getId().'" class="itemtitle">'.htmlentities($objNavigation->getTitle(), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
              <div class="clear"></div>
              <input type="hidden" value="'.$objNavigationTree->getItemId().'" id="rootLevelGroupKey'.$objNavigationTree->getTypeId().'"/>
              <input type="hidden" value="'.$objNavigation->getLanguageId().'" id="rootLevelLanguageId'.$objNavigation->getId().'"/>              
            </div>
            <div class="clear"></div>
          </div>';
          }
        }
      }else{         
        $strSubNavi = '';
        $strDisplaySubNavi = ' style="display:none;"';
        $strSubNaviSelected = '';
        foreach($objNavigationTree as $objNavigation){
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
                <div class="portalcontenticon"></div>
                <div class="menutitle"><a onclick="myNavigation.selectRootLevel('.$objNavigation->getId().', '.$objNavigationTree->getTypeId().', \''.$objNavigation->getUrl().'\', true, \''.$strViewType.'\'); return false;" href="#">'.htmlentities($objNavigation->getTitle(), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a></div>
                <div class="clear"></div>
              </div>';
          }
        }
        
        if($strSubNavi != ''){
          $strOutput .= '
          <div class="naviitemcontainer">
            <div id="naviitem'.$objNavigationTree->getId().'" class="naviitem'.$strSubNaviSelected.' hasmenu" onclick="myNavigation.selectRootLevel('.$objNavigationTree->getId().', '.$objNavigationTree->getTypeId().', \'\', false, \''.$strViewType.'\'); return false;">
              <div class="producticon"></div>
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

}