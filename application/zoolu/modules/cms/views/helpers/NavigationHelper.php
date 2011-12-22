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
 * @package    application.zoolu.modules.cms.views
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */


/**
 * NavigationHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-16: Cornelius Hansjakob
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
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  function getMainNavigation(NavigationTree $rootLevelNavigation, $arrRootLevelMaintenances = array()) {
    $this->core->logger->debug('cms->views->helpers->NavigationHelper->getMainNavigation()');

  	$strOutput = '';
    
    if($arrRootLevelMaintenances == null) $arrRootLevelMaintenances = array();
  	
    foreach ($rootLevelNavigation as $objNavigationTree) {
    
      if(Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$objNavigationTree->getItemId(), Security::PRIVILEGE_VIEW, true, false)){
          
        $strOutput .= '
      <div class="portalcontainer">
        <div id="portal'.$objNavigationTree->getItemId().'" class="portal" onclick="myNavigation.selectPortal('.$objNavigationTree->getItemId().'); myNavigation.loadDashboard(); return false;">
          <div class="portalicon"></div>
          <div id="divRootLevelTitle_'.$objNavigationTree->getItemId().'" class="portaltitle">'.htmlentities($objNavigationTree->getTitle(), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
          <div class="clear"></div>          
        </div>
        <div id="portal'.$objNavigationTree->getItemId().'menu" style="display:none;" class="portalmenu">';
    
        foreach($objNavigationTree as $objNavigation){
          if(Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX.$objNavigation->getId(), Security::PRIVILEGE_VIEW)){                        
            $strOutput .= '  
            <div class="portalmenulink">
              <div class="portalcontenticon"></div>
              <div class="portalmenutitle">';
            if(!empty($this->core->sysConfig->root_level_types->shops) && $objNavigation->getTypeId() == $this->core->sysConfig->root_level_types->shops){
              $strOutput .= '            		
              	<div class="portaledit" onclick="myNavigation.loadMaintenanceOverlay('.$objNavigation->getItemId().'); return false;"></div>';  
            }
            //$strOutput .= '
            //  	<a href="#" onclick="myNavigation.selectPortal('.$objNavigation->getItemId().','.$objNavigationTree->getItemId().'); return false;">'.(($objNavigation->getTypeId() == $this->core->sysConfig->root_level_types->shops) ?  $this->core->translate->_('Shop') : $this->core->translate->_('Web_content')).'</a>';
            $strUrlAddon = '';
            if($objNavigation->getUrl() != null){
              $strUrlAddon .= ', \''.$objNavigation->getUrl().'\'';
            }
            $strOutput .= '
                <a href="#" onclick="myNavigation.selectRootLevel('.$objNavigation->getItemId().','.$objNavigationTree->getItemId().$strUrlAddon.'); return false;">'.((!empty($this->core->sysConfig->root_level_types->shops) && $objNavigation->getTypeId() == $this->core->sysConfig->root_level_types->shops) ?  $this->core->translate->_('Shop') : $this->core->translate->_('Web_content')).'</a>';
            if(!empty($this->core->sysConfig->root_level_types->shops) && $objNavigation->getTypeId() == $this->core->sysConfig->root_level_types->shops){ 
              $strSpanAddon = '';
              if(!in_array($objNavigation->getItemId(), $arrRootLevelMaintenances)){
                $strSpanAddon = ' style="display:none;"';    
              }  
              $strOutput .= '
                	<span id="spanMaintenanceStatus_'.$objNavigation->getItemId().'" class="red"'.$strSpanAddon.'>Offline</span>';
            }          
            $strOutput .= '
              </div>
              <input type="hidden" value="'.$objNavigation->getLanguageId().'" id="rootLevelLanguageId'.$objNavigation->getItemId().'"/>
              <div class="clear"></div>
            </div>';
            
            if($objNavigation->hasLandingPages()){
              $strOutput .= '
                <div class="portalmenulink">
                  <div class="portalcontenticon"></div>
                  <div class="portalmenutitle">
                    <a href="#" onclick="myNavigation.selectRootLevel('.$objNavigation->getItemId().','.$objNavigationTree->getItemId().', \'/zoolu/cms/index/list\'); return false;">'.$this->core->translate->_('Landing_pages').'</a>
                  </div>
                  <input type="hidden" id="rootLevelLanguageId1" value="1">
                  <div class="clear"></div>
                </div>';
            }
          }
        }
        
        $strOutput .= '
          <!--<div class="portalmenulink">
            <div class="portalwidgetsicon"></div>
            <div class="portalmenutitle"><a href="#" onclick="return false;">'.$this->core->translate->_('Widgets').'</a></div>
            <div class="clear"></div>
          </div>
          <div class="portalmenulink">
            <div class="portalsettingsicon"></div>
            <div class="portalmenutitle"><a href="#" onclick="return false;">'.$this->core->translate->_('Settings').'</a></div>
            <div class="clear"></div>
          </div>-->
          <div class="clear"></div>
        </div>
        <div class="clear"></div>
      </div>';
    
      }      
    }
    
    return $strOutput;
  }

  /**
   * getNavigationElements
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  function getNavigationElements($objRowset, $currLevel) {
  	$this->core->logger->debug('cms->views->helpers->NavigationHelper->getNavigationElements()');

    $strOutput = '';
    $strOutputStartpage = '';

    $counter = 1;

    if(count($objRowset) > 0){
    	foreach ($objRowset as $objRow){

    		$strPageTitle = $objRow->title;
    		$strFolderTitle = $objRow->title;
    		
    		if($objRow->pageLinkTitle != -1 && $objRow->pageType == $this->core->sysConfig->page_types->link->id){
    		  $strPageTitle = ($objRow->pageLinkTitle != '') ? $objRow->pageLinkTitle : (($objRow->pageGuiLinkTitle != '') ? $objRow->pageGuiLinkTitle : $objRow->title); 
    		}
        
    		// gui fallback title
    		if($strPageTitle == '' && $objRow->elementType == 'page'){
    		  $strPageTitle = $objRow->guiTitle;
          $objRow->pageType = $this->core->sysConfig->page_types->page->id;
          $objRow->type = 'page';
          $objRow->genericFormId = '';
          $objRow->version = 'null';
          $objRow->templateId = ($objRow->isStartPage == 1) ? $this->core->sysConfig->page_types->page->startpage_templateId : $this->core->sysConfig->page_types->page->default_templateId;
        }
        
    	  // gui fallback title
        if($strFolderTitle == '' && $objRow->elementType == 'folder'){
          $strFolderTitle = $objRow->guiTitle;
          $objRow->type = 'folder';
          $objRow->genericFormId = $this->core->sysConfig->form->ids->folders->default;
          $objRow->version = 'null';
          $objRow->templateId = -1;
        }
        
        $objRow->version = ($objRow->version != '') ? $objRow->version : 'null';
        $objRow->templateId = ($objRow->templateId != '') ? $objRow->templateId : -1;
        
    		if($objRow->isStartPage == 1){
    		  /**
           * overwrite type with 'page'
           */
          $objRow->type = 'page';

          $strTitleAddonClass = '';
          $strTitleAddon = '';
          if($objRow->pageType == $this->core->sysConfig->page_types->link->id){
            $strTitleAddonClass = ' italic';
            $strTitleAddon = '&infin; ';
          }

    			/**
           * get values of the row and create startpage output
           */
          $strOutputStartpage .= '<div id="'.$objRow->type.$objRow->id.'" class="'.$objRow->type.'">
            <div class="icon img_startpage_'.(($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off').'"></div>
            <div id="divNavigationTitle_'.$objRow->type.$objRow->id.'" class="title'.$strTitleAddonClass.'" onclick="myNavigation.getEditForm('.$objRow->id.',\''.$objRow->type.'\',\''.$objRow->genericFormId.'\','.$objRow->version.','.$objRow->templateId.'); return false;">'.$strTitleAddon.htmlentities($strPageTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
          </div>';
    		}else if($objRow->pageType == $this->core->sysConfig->page_types->page->id){
    		  /**
           * get values of the row and create page output
           */
          $strOutput .= '<div id="'.$objRow->type.$objRow->id.'" class="'.$objRow->type.'">
            <div class="icon img_'.$objRow->type.'_'.(($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off').'"></div>
            <div class="navsortpos"><input class="iptsortpos" type="text" name="pos_'.$objRow->type.'_'.$objRow->id.'" id="pos_'.$objRow->type.'_'.$objRow->id.'" value="'.$counter.'" onfocus="myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false;" onkeyup="if(event.keyCode==13){ myNavigation.updateSortPosition(\'pos_'.$objRow->type.'_'.$objRow->id.'\',\''.$objRow->type.'\','.$currLevel.'); myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false; }" onblur="myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false;" /></div>
            <div id="divNavigationTitle_'.$objRow->type.$objRow->id.'" class="title" onclick="myNavigation.getEditForm('.$objRow->id.',\''.$objRow->type.'\',\''.$objRow->genericFormId.'\','.$objRow->version.','.$objRow->templateId.'); return false;">'.htmlentities($strPageTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
          </div>';

          $counter++;

    		}else if($objRow->pageType == $this->core->sysConfig->page_types->link->id){
    		  /**
    		   * overwrite type with 'page'
    		   */
    		  $objRow->type = 'page';

          /**
           * get values of the row and create page output
           */
          $strOutput .= '<div id="'.$objRow->type.$objRow->id.'" class="'.$objRow->type.'">
            <div class="icon img_'.$objRow->type.'_'.(($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off').'"></div>
            <div class="navsortpos"><input class="iptsortpos" type="text" name="pos_'.$objRow->type.'_'.$objRow->id.'" id="pos_'.$objRow->type.'_'.$objRow->id.'" value="'.$counter.'" onfocus="myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false;" onkeyup="if(event.keyCode==13){ myNavigation.updateSortPosition(\'pos_'.$objRow->type.'_'.$objRow->id.'\',\''.$objRow->type.'\','.$currLevel.'); myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false; }" onblur="myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false;" /></div>
            <div id="divNavigationTitle_'.$objRow->type.$objRow->id.'" class="title italic" title="'.$strPageTitle.'" onclick="myNavigation.getEditForm('.$objRow->id.',\''.$objRow->type.'\',\''.$objRow->genericFormId.'\','.$objRow->version.','.$objRow->templateId.'); return false;">&infin; '.htmlentities($strPageTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
          </div>';

          $counter++;

        }else if($objRow->pageType == $this->core->sysConfig->page_types->external->id){
          /**
           * overwrite type with 'page'
           */
          $objRow->type = 'page';

          /**
           * get values of the row and create page output
           */
          $strOutput .= '<div id="'.$objRow->type.$objRow->id.'" class="'.$objRow->type.'">
            <div class="icon img_'.$objRow->type.'_'.(($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off').'"></div>
            <div class="navsortpos"><input class="iptsortpos" type="text" name="pos_'.$objRow->type.'_'.$objRow->id.'" id="pos_'.$objRow->type.'_'.$objRow->id.'" value="'.$counter.'" onfocus="myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false;" onkeyup="if(event.keyCode==13){ myNavigation.updateSortPosition(\'pos_'.$objRow->type.'_'.$objRow->id.'\',\''.$objRow->type.'\','.$currLevel.'); myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false; }" onblur="myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false;" /></div>
            <div id="divNavigationTitle_'.$objRow->type.$objRow->id.'" class="title" title="'.$strPageTitle.'" onclick="myNavigation.getEditForm('.$objRow->id.',\''.$objRow->type.'\',\''.$objRow->genericFormId.'\','.$objRow->version.','.$objRow->templateId.'); return false;">'.htmlentities($strPageTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
          </div>';

          $counter++;
        }else if($objRow->pageType == $this->core->sysConfig->page_types->sitemap->id){
          /**
           * overwrite type with 'page'
           */
          $objRow->type = 'page';

          /**
           * get values of the row and create page output
           */
          $strOutput .= '<div id="'.$objRow->type.$objRow->id.'" class="'.$objRow->type.'">
            <div class="icon img_'.$objRow->type.'_'.(($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off').'"></div>
            <div class="navsortpos"><input class="iptsortpos" type="text" name="pos_'.$objRow->type.'_'.$objRow->id.'" id="pos_'.$objRow->type.'_'.$objRow->id.'" value="'.$counter.'" onfocus="myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false;" onkeyup="if(event.keyCode==13){ myNavigation.updateSortPosition(\'pos_'.$objRow->type.'_'.$objRow->id.'\',\''.$objRow->type.'\','.$currLevel.'); myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false; }" onblur="myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false;" /></div>
            <div id="divNavigationTitle_'.$objRow->type.$objRow->id.'" class="title" title="'.$strPageTitle.'" onclick="myNavigation.getEditForm('.$objRow->id.',\''.$objRow->type.'\',\''.$objRow->genericFormId.'\','.$objRow->version.','.$objRow->templateId.'); return false;">'.htmlentities($strPageTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
          </div>';

          $counter++;
        }else if($objRow->pageType == $this->core->sysConfig->page_types->iframe->id){
          /**
           * overwrite type with 'page'
           */
          $objRow->type = 'page';

          /**
           * get values of the row and create page output
           */
          $strOutput .= '<div id="'.$objRow->type.$objRow->id.'" class="'.$objRow->type.'">
            <div class="icon img_'.$objRow->type.'_'.(($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off').'"></div>
            <div class="navsortpos"><input class="iptsortpos" type="text" name="pos_'.$objRow->type.'_'.$objRow->id.'" id="pos_'.$objRow->type.'_'.$objRow->id.'" value="'.$counter.'" onfocus="myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false;" onkeyup="if(event.keyCode==13){ myNavigation.updateSortPosition(\'pos_'.$objRow->type.'_'.$objRow->id.'\',\''.$objRow->type.'\','.$currLevel.'); myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false; }" onblur="myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false;" /></div>
            <div id="divNavigationTitle_'.$objRow->type.$objRow->id.'" class="title" title="'.$strPageTitle.'" onclick="myNavigation.getEditForm('.$objRow->id.',\''.$objRow->type.'\',\''.$objRow->genericFormId.'\','.$objRow->version.','.$objRow->templateId.'); return false;">'.htmlentities($strPageTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
          </div>';

          $counter++;
        }else if($objRow->pageType == $this->core->sysConfig->page_types->process->id){
          /**
           * overwrite type with 'page'
           */
          $objRow->type = 'page';

          /**
           * get values of the row and create page output
           */
          $strOutput .= '<div id="'.$objRow->type.$objRow->id.'" class="'.$objRow->type.'">
            <div class="icon img_'.$objRow->type.'_'.(($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off').'"></div>
            <div class="navsortpos"><input class="iptsortpos" type="text" name="pos_'.$objRow->type.'_'.$objRow->id.'" id="pos_'.$objRow->type.'_'.$objRow->id.'" value="'.$counter.'" onfocus="myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false;" onkeyup="if(event.keyCode==13){ myNavigation.updateSortPosition(\'pos_'.$objRow->type.'_'.$objRow->id.'\',\''.$objRow->type.'\','.$currLevel.'); myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false; }" onblur="myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false;" /></div>
            <div id="divNavigationTitle_'.$objRow->type.$objRow->id.'" class="title" title="'.$strPageTitle.'" onclick="myNavigation.getEditForm('.$objRow->id.',\''.$objRow->type.'\',\''.$objRow->genericFormId.'\','.$objRow->version.','.$objRow->templateId.'); return false;">'.htmlentities($strPageTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
          </div>';

          $counter++;
        }else{
          if(Security::get()->isAllowed(Security::RESOURCE_FOLDER_PREFIX.$objRow->id, Security::PRIVILEGE_VIEW)){
      			/**
  	         * get values of the row and create default output
  	         */
  	        $strOutput .= '<div id="'.$objRow->type.$objRow->id.'" class="'.$objRow->type.'">
              <div id="divNavigationEdit_'.$objRow->id.'" class="icon img_'.$objRow->type.'_'.(($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off').'" ondblclick="myNavigation.getEditForm('.$objRow->id.',\''.$objRow->type.'\',\''.$objRow->genericFormId.'\','.$objRow->version.','.$objRow->templateId.'); return false;"></div>
              <div class="navsortpos"><input class="iptsortpos" type="text" name="pos_'.$objRow->type.'_'.$objRow->id.'" id="pos_'.$objRow->type.'_'.$objRow->id.'" value="'.$counter.'" onfocus="myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false;" onkeyup="if(event.keyCode==13){ myNavigation.updateSortPosition(\'pos_'.$objRow->type.'_'.$objRow->id.'\',\''.$objRow->type.'\','.$currLevel.'); myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false; }" onblur="myNavigation.toggleSortPosBox(\'pos_'.$objRow->type.'_'.$objRow->id.'\'); return false;"/></div>
              <div id="divNavigationTitle_'.$objRow->type.$objRow->id.'" class="title" title="'.$strPageTitle.'" onclick="myNavigation.selectNavigationItem('.$currLevel.', \''.$objRow->type.'\','.$objRow->id.'); return false;">'.htmlentities($strFolderTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
            </div>';
          }

          $counter++;
    		}
    	}

    	if($strOutputStartpage != ''){
    	  $strOutputStartpage .= '<div class="linegray"></div>';
      }
    }

    return $strOutputStartpage.$strOutput;

  }

}

?>