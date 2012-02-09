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
 * 1.0, 2010-01-19: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class NavigationHelper {
  
  /**
   * @var Core
   */
  protected $core;
  
  /**
   * @var Navigation
   */
  protected $objNavigation;
  
  /**
   * @var Zend_Translate
   */
  protected $objTranslate;
    
  /**
   * constructor
   * @author Thomas Schedler <tsh@massiveart.com>   
   */
  public function __construct($blnRequireFrunctionWrapper = true){
    $this->core = Zend_Registry::get('Core');
    
    /**
     * function call wrapper for NavigationHelper
     */
    if($blnRequireFrunctionWrapper == true){
      require_once(dirname(__FILE__).'/navigation.inc.php');
    }
  }
  
  /**
   * getMainNavigation
   * @param string $strElement
   * @param string|array $mixedElementProperties element css class or array with element properties
   * @param string $strSelectedClass
   * @param boolean $blnWithHomeLink
   * @param boolean $blnImageNavigation
   * @author Thomas Schedler <tsh@massiveart.com>
   * @return string 
   */ 
  public function getMainNavigation($strElement = 'li', $mixedElementProperties = '', $strSelectedClass = 'selected', $blnWithHomeLink = true, $blnImageNavigation = false){
    
    $strMainNavigation = '';
    $strHomeLink = '';
        
    $this->objNavigation->loadMainNavigation();
    
    $strPageId = '';  
    if(is_object($this->objNavigation->Page())){
      $strPageId = $this->objNavigation->Page()->getPageId();
    }   
    $strFolderId = $this->objNavigation->getRootFolderId();
    
    $strElementProperties = '';
    if(is_array($mixedElementProperties)){
      foreach($mixedElementProperties as $strProperty => $strValue){
       $strElementProperties .= ' '.$strProperty.'="'.$strValue.'"';  
      }
    }else if($mixedElementProperties != ''){
      $strElementProperties = ' class="'.$mixedElementProperties.'"';
    }
    
    if(count($this->objNavigation->MainNavigation()) > 0){    
      foreach($this->objNavigation->MainNavigation() as $objNavigationItem){
        
        $strSelectedItem = '';
        $strSelectedImg = 'off';
        if($strPageId == $objNavigationItem->pageId){
          $strSelectedItem = ' class="'.$strSelectedClass.'"';
          $strSelectedImg = 'on';
        }else if($strFolderId == $objNavigationItem->folderId){
          $strSelectedItem = ' class="'.$strSelectedClass.'"';
          $strSelectedImg = 'on';
        }
        
        $strImgFileTitle = strtolower($objNavigationItem->url);
        if(strpos($strImgFileTitle, '/') > -1){
          $strImgFileTitle = substr($strImgFileTitle, 0, strpos($strImgFileTitle, '/'));    
        }
        
        if($objNavigationItem->isStartPage == 1 && $blnWithHomeLink == true){
          if($blnImageNavigation){
            $strHomeLink = '<'.$strElement.$strElementProperties.$strSelectedItem.'><a href="'.$this->objNavigation->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url).'"'.(($objNavigationItem->target != '') ? ' target="'.$objNavigationItem->target.'"' : '').$strSelectedItem.'><img src="'.$this->core->config->domains->static->components.'/website/themes/default/images/navigation/home_'.$strSelectedImg.'.gif" alt="'.htmlentities($objNavigationItem->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'"/></a></'.$strElement.'>';
          }else{
            $strHomeLink = '<'.$strElement.$strElementProperties.$strSelectedItem.'><a href="'.$this->objNavigation->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url).'"'.(($objNavigationItem->target != '') ? ' target="'.$objNavigationItem->target.'"' : '').$strSelectedItem.'>'.htmlentities($objNavigationItem->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a></'.$strElement.'>';
          }
        }else{
          if($blnImageNavigation){
            $strMainNavigation  .= '<'.$strElement.$strElementProperties.$strSelectedItem.'><a href="'.$this->objNavigation->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url).'"'.(($objNavigationItem->target != '') ? ' target="'.$objNavigationItem->target.'"' : '').$strSelectedItem.'><img src="'.$this->core->config->domains->static->components.'/website/themes/default/images/navigation/'.$strImgFileTitle.'_'.$strSelectedImg.'.gif" alt="'.htmlentities($objNavigationItem->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'"/></a></'.$strElement.'>';
          }else{
            $strMainNavigation  .= '<'.$strElement.$strElementProperties.$strSelectedItem.'><a href="'.$this->objNavigation->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url).'"'.(($objNavigationItem->target != '') ? ' target="'.$objNavigationItem->target.'"' : '').$strSelectedItem.'>'.htmlentities($objNavigationItem->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a></'.$strElement.'>';
          }
        }
      }
    }
      
    return $strHomeLink.$strMainNavigation;
  }
  
  /**
   * getMainNavigationTitle
   * @return string $strReturn
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @return string
   */
  public function getMainNavigationTitle(){
    $strReturn = '';
    
    $this->objNavigation->loadMainNavigation();
    
    $strPageId = '';  
    if(is_object($this->objNavigation->Page())){
      $strPageId = $this->objNavigation->Page()->getPageId();
    }   
    $strFolderId = $this->objNavigation->getRootFolderId();
    
    if(count($this->objNavigation->MainNavigation()) > 0){    
      foreach($this->objNavigation->MainNavigation() as $objNavigationItem){
        
        $blnIsSelected = false;
        if($strPageId == $objNavigationItem->pageId){
          $blnIsSelected = true;
        }else if($strFolderId == $objNavigationItem->folderId){
          $blnIsSelected = true;
        }
              
        if($blnIsSelected){
          $strReturn .= htmlentities($objNavigationItem->title, ENT_COMPAT, $this->core->sysConfig->encoding->default);
        }
      }
    }    
    return $strReturn;
  }
  
  /**
   * getSubNavigation
   * @return string $strSubNavigation
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @return string
   */
  public function getSubNavigation($strElement, $mixedElementProperties, $strSelectedClass){
    $strSubNavigation = '';
      
    $this->objNavigation->loadStaticSubNavigation(2);
    
    $strPageId = '';  
    if(is_object($this->objNavigation->Page())){
      $strPageId = $this->objNavigation->Page()->getPageId();
    } 
    
    $arrFolderIds = $this->objNavigation->getParentFolderIds();
      
    $strElementProperties = '';
    $arrElementProperies = array();
    if(is_array($mixedElementProperties)){
      $arrElementProperies = $mixedElementProperties;
    }else{
      $arrElementProperies['class'] = $mixedElementProperties;
    }
    
    if(count($this->objNavigation->SubNavigation()) > 0){
      foreach($this->objNavigation->SubNavigation() as $objNavi){
        if($objNavi instanceof NavigationTree){
          $strSubNavigation  .= '
                <'.$strElement.'>
                  <a href="'.$objNavi->getUrl().'"'.(($objNavi->getTarget() != '') ? ' target="'.$objNavi->getTarget().'"' : '').'>'.htmlentities($objNavi->getTitle(), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a>';
          if(count($objNavi) > 0){
            $strSubNavigation  .= '<ul>';
            foreach($objNavi as $objSubNavi){           
              $strSubNavigation  .= '
                  <'.$strElement.'>
                    <a href="'.$objSubNavi->getUrl().'"'.(($objSubNavi->getTarget() != '') ? ' target="'.$objSubNavi->getTarget().'"' : '').'>'.htmlentities($objSubNavi->getTitle(), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a>
                  </'.$strElement.'>'; 
            } 
            $strSubNavigation  .= '</ul>';  
          }else{
            $strSubNavigation  .= '&nbsp;';     
          }
          $strSubNavigation  .= '
               </'.$strElement.'>';      
        }else{         
          $strSubNavigation  .= '
                <'.$strElement.'>              
                  <a href="'.$objNavi->getUrl().'"'.(($objNavi->getTarget() != '') ? ' target="'.$objNavi->getTarget().'"' : '').'>'.htmlentities($objNavi->getTitle(), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a>
                </'.$strElement.'>';
        }
      }    
    }
    
    echo $strSubNavigation;
  }
  
  /**
   * hasSubNavigation
   * @return boolean
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @return string
   */
  public function hasSubNavigation(){
    $this->objNavigation->evaluateRootFolderId();
    if($this->objNavigation->getRootFolderId() != ''){
      return true;
    }else{
      return false;
    }
  }
  
  /**
   * getSideNavigation
   * @author Thomas Schedler <tsh@massiveart.com>
   * @return string
   */
  public function getSideNavigation(){
    //TODO default side navigation
  }
  
  /**
   * getFooterNavigation
   * @author Thomas Schedler <tsh@massiveart.com>
   * @return string
   */
  public function getFooterNavigation($strCssClass = ''){
    $strOutput = '';
    
    $objNavigationTree = $this->objNavigation->loadNavigationByDisplayOption($this->core->sysConfig->navigation_options->bottom, 1, false);

    $intCounter = 0;
    $intTotal = count($objNavigationTree);
    if($intTotal > 0){
      $strOutput .= '<ul'. ($strCssClass ? ' class="' . $strCssClass . '"' : '') .'>';
      foreach($objNavigationTree as $objNavi){
        $intCounter++;
        $strOutput .= '<li><a href="'.$objNavi->getUrl().'"'.(($objNavi->getTarget() != '') ? ' target="'.$objNavi->getTarget().'"' : '').'>'.htmlentities($objNavi->getTitle(), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a></li>';
      }
      $strOutput .= '</ul>';
    } 
    
    return $strOutput;
  }
  
  /**
   * getBreadcrumb
   * @author Thomas Schedler <tsh@massiveart.com>
   * @return string
   */
  public function getBreadcrumb($blnHomeLink = false, $strHomeUrl = ''){
    $strBreadcrumb = '';
    if(count($this->objNavigation->ParentFolders()) > 0){
      $arrParentFolders = array_reverse($this->objNavigation->ParentFolders());
  
      if($blnHomeLink){
        if($strHomeUrl != ''){
          $strBreadcrumb .= '<a class="home" href="'.$strHomeUrl.'">Home</a> <span>/</span> ';
        }else{
          $strBreadcrumb .= '<a class="home" href="/">Home</a> <span>/</span> ';
        }
      }
  
      $intCounter = 0;
      foreach($arrParentFolders as $key => $objFolder){
        $intCounter++;
        $strBreadcrumb .= ($objFolder->id == $this->objNavigation->Page()->getNavParentId() && $this->objNavigation->Page()->getIsStartElement(false) == true) ? htmlentities($objFolder->title, ENT_COMPAT, $this->core->sysConfig->encoding->default) : '<a href="/'.strtolower($objFolder->languageCode).'/'.$objFolder->url.'">'.htmlentities($objFolder->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a>';
  
        if($intCounter < count($this->objNavigation->ParentFolders())){
          $strBreadcrumb .= ' <span>/</span> ';
        }
      }
    }
  
    if($this->objNavigation->Page()->getIsStartElement(false) == false){
      if($strBreadcrumb != '') $strBreadcrumb .= ' <span>/</span> ';
      $strBreadcrumb .=$this->objNavigation->Page()->getFieldValue('title');      
    }
  
    return $strBreadcrumb;  
  }  
  
  /**
   * returnHtmlAttributes
   * @param array $arrAttributes
   * @return string $strXhtml 
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function returnHtmlAttributes($arrAttributes){
    $strXhtml = '';
    
    foreach((array) $arrAttributes as $key => $val){
      if (strpos($val, '"') !== false) {
        $strXhtml .= " $key='$val'";
      } else {
        $strXhtml .= " $key=\"$val\"";
      }
    }
    return $strXhtml;
  }

  /**
   * getSubNavigationByLevel
   * @param integer $intLevel
   * @author Thomas Schedler <tsh@massiveart.com>
   * @return string
   */
  public function getSubNavigationByLevel($intLevel){
    //TODO default product overview
  }
  
  /**
   * getSubNavigationSelectByLevel
   * @param integer $intLevel
   * @author Thomas Schedler <tsh@massiveart.com>
   * @return string
   */
  public function getSubNavigationSelectByLevel($intLevel){
    //TODO default product overview
  }
  
  /**
   * getSitemap
   * @author Thomas Schedler <tsh@massiveart.com>
   * @return string
   */
  public function getSitemap(){
    //TODO default sitemap
  }
  
  /**
   * setNavigation    
   * @param Navigation $objNavigation   
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function setNavigation(Navigation $objNavigation){
    $this->objNavigation = $objNavigation;
  }
  
  /**
   * setTranslate    
   * @param Zend_Translate $objTranslate   
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function setTranslate(Zend_Translate $objTranslate){
    $this->objTranslate = $objTranslate;
  }
}