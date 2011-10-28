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
 * @package    application.zoolu.modules.core.properties.views.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * NavigationHelper
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-15: Cornelius Hansjakob
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
   * getRootLevels 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  function getRootLevels($rowset) {
    $this->core->logger->debug('properties->views->helpers->NavigationHelper->getRootLevels()');
  	
  	$strOutput = '';
  	
    foreach ($rowset as $row) {      
      /**
       * get values of the row and create output
       */
      $strJsClickFunc = '';
      $strRootLevelIconCss = '';
      
    	switch ($row->idRootLevelTypes) {
				case $this->core->sysConfig->root_level_types->contacts:
				    $strJsClickFunc = 'myNavigation.selectContacts('.$row->id.'); ';
				    $strRootLevelIconCss = 'usericon';
				    break;
				case $this->core->sysConfig->root_level_types->locations:
            $strJsClickFunc = 'myNavigation.selectLocations('.$row->id.'); ';
            $strRootLevelIconCss = 'locationicon';          
            break;  
				case $this->core->sysConfig->root_level_types->categories:
				    $strJsClickFunc = 'myNavigation.selectCategories('.$row->id.', '.$this->core->sysConfig->category_types->default.'); ';
				    $strRootLevelIconCss = 'categoryicon';					
				    break;
		    case $this->core->sysConfig->root_level_types->labels:
            $strJsClickFunc = 'myNavigation.selectCategories('.$row->id.', '.$this->core->sysConfig->category_types->label.'); ';
            $strRootLevelIconCss = 'labelicon';          
            break;
        case $this->core->sysConfig->root_level_types->systeminternals:
            $strJsClickFunc = 'myNavigation.selectCategories('.$row->id.', '.$this->core->sysConfig->category_types->system.'); ';
            $strRootLevelIconCss = 'sysinternicon';          
            break;			
    	}
    	
      $strOutput .= '<div class="portalcontainer">
        <div id="portal'.$row->id.'top" class="portaltop"><img src="/zoolu-statics/images/main/bg_box_230_top.png" width="230" height="4"/></div>
        <div id="portal'.$row->id.'" class="portal" onclick="'.$strJsClickFunc.'return false;">
          <div class="'.$strRootLevelIconCss.'"></div>
          <div id="divRootLevelTitle_'.$row->id.'" class="portaltitle">'.htmlentities($row->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
          <div class="clear"></div>
        </div>
        <div id="portal'.$row->id.'bottom" class="portalbottom"><img src="/zoolu-statics/images/main/bg_box_230_bottom.png" width="230" height="4"/></div>
        <div class="clear"></div>
      </div>';
    }
       
    return $strOutput;	
  	
  }
  
  /**
   * getCatNavElements 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  function getCatNavElements($rowset, $currLevel) {
  	$this->core->logger->debug('properties->views->helpers->NavigationHelper->getCatNavElements()');
  	
    $strOutput = '';
    
    foreach ($rowset as $row){
      
      /**
       * get values of the row and create output
       */
      $strIconCss = '';
      switch ($row->idCategoryTypes) {
        case $this->core->sysConfig->category_types->default:
            $strIconCss = 'img_category_on';
            break;
        case $this->core->sysConfig->category_types->label:
            $strIconCss = 'img_label_on';          
            break;
        case $this->core->sysConfig->category_types->system:
            $strIconCss = 'img_sysintern_on';          
            break;
      }
    	
    	$strOutput .= '<div id="category'.$row->id.'" class="category hoveritem">
							         <div class="icon '.$strIconCss.'" ondblclick="myNavigation.getEditForm('.$row->id.', \'category\', null , null, '.$row->idCategoryTypes.'); return false;"></div>
							         <div class="title" onclick="myNavigation.selectNavigationItem('.$currLevel.', \'category\', '.$row->id.', '.$row->idCategoryTypes.'); return false;">
							           '.htmlentities($row->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'
							         </div>
							       </div>';
    }
    return $strOutput; 
  }
  
  /**
   * getContactNavElements 
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  function getContactNavElements($objRowset, $currLevel) {
    $this->core->logger->debug('properties->views->helpers->NavigationHelper->getContactNavElements()');
    
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
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  function getLocationNavElements($objRowset, $currLevel) {
    $this->core->logger->debug('properties->views->helpers->NavigationHelper->getLocationNavElements()');
    
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