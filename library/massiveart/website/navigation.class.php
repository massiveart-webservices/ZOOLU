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
 * @package    library.massiveart.website
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Navigation
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-09: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.website
 * @subpackage Navigation
 */

class Navigation {

  /**
   * @var Core
   */
  protected $core;

  /**
   * @var Model_Folders
   */
  protected $objModelFolders;

  /**
   * @var Page
   */
  protected $objPage;
  /**
   * @return Page
   */
  public function Page(){
    return $this->objPage;
  }

  /**
   * @var Zend_Db_Table_Row_Abstract
   */
  protected $objBaseUrl;

  /**
   * @var Zend_Db_Table_Rowset_Abstract
   */
  protected $objMainNavigation;
  public function MainNavigation(){
    return $this->objMainNavigation;
  }

  /**
   * @var NavigationTree
   */
  protected $objSubNavigation;
  public function SubNavigation(){
    return $this->objSubNavigation;
  }

  /**
   * @var Zend_Db_Table_Rowset_Abstract
   */
  protected $objParentFolders;
  public function ParentFolders(){
    if($this->objParentFolders === null && $this->objPage->getParentTypeId() == $this->core->sysConfig->parent_types->folder){
      $this->objParentFolders = $this->getModelFolders()->loadParentFolders($this->objPage->getParentId());
    }
    return $this->objParentFolders;
  }

  /**
   * @var Zend_Db_Table_Rowset_Abstract
   */
  protected $objGlobalParentFolders;
  public function GlobalParentFolders(){
    return $this->objGlobalParentFolders;
  }

  protected $intRootLevelId;
  protected $intRootFolderId = 0;
  protected $strRootFolderId = '';
  protected $intLanguageId;  
  protected $blnHasUrlPrefix;
  protected $strUrlPrefix;  
  protected $blnHasSegments;
  protected $intSegmentId;
  protected $strSegmentCode;

  /**
   * Constructor
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  }

  /**
   * loadMainNavigation
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadMainNavigation(){
    try{
      $this->getModelFolders();

      $this->evaluateRootFolderId();

      $this->objMainNavigation = $this->objModelFolders->loadWebsiteRootNavigation($this->intRootLevelId);

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * loadNavigation
   * @param integer $intDepth
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadNavigation($intDepth = 1, $blnLoadFilter = false){
    try{
      $this->getModelFolders();

      $this->evaluateRootFolderId();

      $objNavigationTree = new NavigationTree();
      $objNavigationTree->setId(0);

      if($this->intRootLevelId > 0){
        $objNavigationData = $this->objModelFolders->loadWebsiteRootLevelChilds($this->intRootLevelId, $intDepth);

        $intTreeId = 0;
        foreach($objNavigationData as $objNavigationItem){

          if($objNavigationItem->isStartPage == 1 && $objNavigationItem->depth == 0 && $objNavigationItem->idFolder > 0){

            /**
             * add to parent tree
             */
            if(isset($objTree) && is_object($objTree) && $objTree instanceof NavigationTree){
              $objNavigationTree->addToParentTree($objTree, 'tree_'.$objTree->getId());
            }

            $objTree = new NavigationTree();
            $objTree->setTitle(($objNavigationItem->folderTitle != '' ? $objNavigationItem->folderTitle : $objNavigationItem->title));
            $objTree->setId($objNavigationItem->idFolder);
            $objTree->setParentId(0);
            $objTree->setTypeId($objNavigationItem->idPageTypes);
            $objTree->setItemId($objNavigationItem->folderId);
            $objTree->setOrder($objNavigationItem->folderOrder);
            $objTree->setUrl(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->external : $this->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url));
            $objTree->setTarget(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->target : '');

            $arrPageGlobaLinkTypes = array($this->core->sysConfig->page_types->product_tree->id, $this->core->sysConfig->page_types->press_area->id, $this->core->sysConfig->page_types->courses->id, $this->core->sysConfig->page_types->events->id);
            if(in_array($objNavigationItem->idPageTypes, $arrPageGlobaLinkTypes) && $this->objPage instanceof Page && $this->objPage->getElementId() == $objNavigationItem->idPage){
              
              $arrFilter = array();
              if($this->objPage->getElementId() == $objNavigationItem->idPage) {
                $arrFilter = array(
                      'CategoryId'  => $this->objPage->getFieldValue('entry_category'),
                      'LabelId'     => $this->objPage->getFieldValue('entry_label'),
                      'SorttypeId'  => $this->objPage->getFieldValue('entry_sorttype'),
                      'ParentId'    => $this->objPage->getFieldValue('entry_point'),
                );
              } elseif($blnLoadFilter == true) {
                $arrFilter = array(
                      'CategoryId'  => $objNavigationItem->entry_category,
                      'LabelId'     => $objNavigationItem->entry_label,
                      'SorttypeId'  => $objNavigationItem->entry_sorttype,
                      'ParentId'    => $objNavigationItem->entry_point,
                );
              }
              if(count($arrFilter) > 0) {
                $this->addGlobalTree($objTree, $objNavigationItem->idPageTypes, $arrFilter, $intDepth);
              }
            }

            $intTreeId = $objNavigationItem->idFolder;

          }elseif($objNavigationItem->idParentTypes == $this->core->sysConfig->parent_types->rootlevel && $objNavigationItem->isStartPage == 0){
            /**
             * Add Rootlevel pages
             */
            $objItem = new NavigationItem();
            $objItem->setTitle($objNavigationItem->title);
            $objItem->setUrl(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->external : '/'.strtolower($objNavigationItem->languageCode).'/'.$objNavigationItem->url);
            $objItem->setTarget(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->target : '');
            $objItem->setId($objNavigationItem->idPage);
            $objItem->setTypeId($objNavigationItem->idPageTypes);
            $objItem->setParentId($objNavigationItem->idFolder);
            $objItem->setItemId($objNavigationItem->pageId);
            $objItem->setOrder($objNavigationItem->pageOrder);
            $objNavigationTree->addItem($objItem, 'item_'.$objItem->getId());
          }else{
            if($intTreeId != $objNavigationItem->idFolder){

              /**
               * add to parent tree
               */
              if(isset($objTree) && is_object($objTree) && $objTree instanceof NavigationTree){
                $objNavigationTree->addToParentTree($objTree, 'tree_'.$objTree->getId());
              }

              $objTree = new NavigationTree();
              $objTree->setTitle($objNavigationItem->folderTitle);
              $objTree->setId($objNavigationItem->idFolder);
              $objTree->setParentId($objNavigationItem->parentId);
              $objTree->setItemId($objNavigationItem->folderId);
              $objTree->setOrder($objNavigationItem->folderOrder);
              $objTree->setUrl(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->external : $this->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url));
              $objTree->setTarget(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->target : '');

              $arrPageGlobaLinkTypes = array($this->core->sysConfig->page_types->product_tree->id, $this->core->sysConfig->page_types->press_area->id, $this->core->sysConfig->page_types->courses->id, $this->core->sysConfig->page_types->events->id);
              if(in_array($objNavigationItem->idPageTypes, $arrPageGlobaLinkTypes) && $this->objPage instanceof Page && $this->objPage->getElementId() == $objNavigationItem->idPage){

                $arrFilter = array();
                if($this->objPage->getElementId() == $objNavigationItem->idPage) {
                  $arrFilter = array(
	                      'CategoryId'  => $this->objPage->getFieldValue('entry_category'),
	                      'LabelId'     => $this->objPage->getFieldValue('entry_label'),
	                      'SorttypeId'  => $this->objPage->getFieldValue('entry_sorttype'),
	                      'ParentId'    => $this->objPage->getFieldValue('entry_point'),
                  );
                } elseif($blnLoadFilter == true) {
                  $arrFilter = array(
	                      'CategoryId'  => $objNavigationItem->entry_category,
	                      'LabelId'     => $objNavigationItem->entry_label,
	                      'SorttypeId'  => $objNavigationItem->entry_sorttype,
	                      'ParentId'    => $objNavigationItem->entry_point,
                  );
                }
                if(count($arrFilter) > 0) {
                  $this->addGlobalTree($objTree, $objNavigationItem->idPageTypes, $arrFilter, $intDepth);
                }

              }

              $intTreeId = $objNavigationItem->idFolder;
            }

            if($objNavigationItem->pageId != null){
              if($objNavigationItem->isStartPage == 1 && isset($objTree)){
                $objTree->setUrl(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->external : $this->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url));
                $objTree->setTarget(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->target : '');
              }else{
                $objItem = new NavigationItem();
                $objItem->setTitle($objNavigationItem->title);
                $objItem->setUrl(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->external : $this->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url));
                $objItem->setTarget(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->target : '');
                $objItem->setId($objNavigationItem->idPage);
                $objItem->setTypeId($objNavigationItem->idPageTypes);
                $objItem->setParentId($objNavigationItem->idFolder);
                $objItem->setItemId($objNavigationItem->pageId);
                $objItem->setOrder($objNavigationItem->pageOrder);
                if(isset($objTree)){
                  $objTree->addItem($objItem, 'item_'.$objItem->getId());
                }else{
                  $objNavigationTree->addItem($objItem, 'item_'.$objItem->getId());
                }
              }
            }
          }
        }
      }

      /**
       * add to parent tree
       */
      if(isset($objTree) && is_object($objTree) && $objTree instanceof NavigationTree){
        $objNavigationTree->addToParentTree($objTree, 'tree_'.$objTree->getId());
      }
      
      $this->objMainNavigation = $objNavigationTree;
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * loadNavigationByDisplayOption
   * @param integer $intDisplayOptionId
   * @param integer $intDepth
   * @param boolean $blnSetMainNavigation
   * @return NavigationTree
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function loadNavigationByDisplayOption($intDisplayOptionId, $intDepth = 99, $blnSetMainNavigation = true, $blnLoadFilter = false){
    try{

      $this->getModelFolders();

      $this->evaluateRootFolderId();

      $objNavigationTree = new NavigationTree();
      $objNavigationTree->setId(0);

      if($this->intRootLevelId > 0){

        $objNavigationData = $this->objModelFolders->loadWebsiteRootLevelChilds($this->intRootLevelId, $intDepth, $intDisplayOptionId, $blnLoadFilter);

        $intTreeId = 0;
        foreach($objNavigationData as $objNavigationItem){

          if($objNavigationItem->isStartPage == 1 && $objNavigationItem->depth == 0){

            /**
             * add to parent tree
             */
            if(isset($objTree) && is_object($objTree) && $objTree instanceof NavigationTree){
              $objNavigationTree->addToParentTree($objTree, 'tree_'.$objTree->getId());
            }

            $objTree = new NavigationTree();
            $objTree->setTitle(($objNavigationItem->folderTitle != '' ? $objNavigationItem->folderTitle : $objNavigationItem->title));
            $objTree->setId($objNavigationItem->idFolder);
            $objTree->setParentId(0);
            $objTree->setTypeId($objNavigationItem->idPageTypes);
            $objTree->setItemId($objNavigationItem->folderId);
            $objTree->setOrder($objNavigationItem->folderOrder);
            $objTree->setUrl(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->external : $this->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url));
            $objTree->setTarget(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->target : '');

            $arrPageGlobaLinkTypes = array($this->core->sysConfig->page_types->product_tree->id, $this->core->sysConfig->page_types->press_area->id, $this->core->sysConfig->page_types->courses->id, $this->core->sysConfig->page_types->events->id);
            if(in_array($objNavigationItem->idPageTypes, $arrPageGlobaLinkTypes) && $this->objPage instanceof Page){
              $arrFilter = array();
              if($this->objPage->getElementId() == $objNavigationItem->idPage) {
                $arrFilter = array(
                      'CategoryId'  => $this->objPage->getFieldValue('entry_category'),
                      'LabelId'     => $this->objPage->getFieldValue('entry_label'),
                      'SorttypeId'  => $this->objPage->getFieldValue('entry_sorttype'),
                      'ParentId'    => $this->objPage->getFieldValue('entry_point'),
                );
              } elseif($blnLoadFilter == true) {
                $arrFilter = array(
                      'CategoryId'  => $objNavigationItem->entry_category,
                      'LabelId'     => $objNavigationItem->entry_label,
                      'SorttypeId'  => $objNavigationItem->entry_sorttype,
                      'ParentId'    => $objNavigationItem->entry_point,
                );
              }
              if(count($arrFilter) > 0) {
                if(count($arrFilter) > 0) $this->addGlobalTree($objTree, $objNavigationItem->idPageTypes, $arrFilter, $intDepth);
              }
            }

            $intTreeId = $objNavigationItem->idFolder;

          }else{

            if($intTreeId != $objNavigationItem->idFolder){

              /**
               * add to parent tree
               */
              if(isset($objTree) && is_object($objTree) && $objTree instanceof NavigationTree){
                $objNavigationTree->addToParentTree($objTree, 'tree_'.$objTree->getId());
              }

              $objTree = new NavigationTree();
              $objTree->setTitle($objNavigationItem->folderTitle);
              $objTree->setId($objNavigationItem->idFolder);
              $objTree->setTypeId($objNavigationItem->idPageTypes);
              $objTree->setParentId($objNavigationItem->parentId);
              $objTree->setItemId($objNavigationItem->folderId);
              $objTree->setOrder($objNavigationItem->folderOrder);
              $objTree->setUrl(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->external : $this->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url));
              $objTree->setTarget(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->target : '');
              
              $arrPageGlobaLinkTypes = array($this->core->sysConfig->page_types->product_tree->id, $this->core->sysConfig->page_types->press_area->id, $this->core->sysConfig->page_types->courses->id, $this->core->sysConfig->page_types->events->id);
              if(in_array($objNavigationItem->idPageTypes, $arrPageGlobaLinkTypes) && $this->objPage instanceof Page){
                $arrFilter = array();
                if($this->objPage->getElementId() == $objNavigationItem->idPage) {
                  $arrFilter = array(
                      'CategoryId'  => $this->objPage->getFieldValue('entry_category'),
                      'LabelId'     => $this->objPage->getFieldValue('entry_label'),
                      'SorttypeId'  => $this->objPage->getFieldValue('entry_sorttype'),
                      'ParentId'    => $this->objPage->getFieldValue('entry_point'),
                  );
                } elseif($blnLoadFilter == true) {
                  $arrFilter = array(
                      'CategoryId'  => $objNavigationItem->entry_category,
                      'LabelId'     => $objNavigationItem->entry_label,
                      'SorttypeId'  => $objNavigationItem->entry_sorttype,
                      'ParentId'    => $objNavigationItem->entry_point,
                  );
                }
                if(count($arrFilter) > 0) {
                  $this->addGlobalTree($objTree, $objNavigationItem->idPageTypes, $arrFilter, $intDepth);
                }
              }

              $intTreeId = $objNavigationItem->idFolder;
            }

            if($objNavigationItem->pageId != null){
              if($objNavigationItem->isStartPage == 1 && isset($objTree)){
                $objTree->setUrl(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->external : $this->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url));
                $objTree->setTarget(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->target : '');
              }else{
                $objItem = new NavigationItem();
                $objItem->setTitle($objNavigationItem->title);
                $objItem->setUrl(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->external : $this->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url));
                $objItem->setTarget(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->target : '');
                $objItem->setId($objNavigationItem->idPage);
                $objItem->setTypeId($objNavigationItem->idPageTypes);
                $objItem->setParentId($objNavigationItem->idFolder);
                $objItem->setItemId($objNavigationItem->pageId);
                $objItem->setOrder($objNavigationItem->pageOrder);
                if(isset($objTree)){
                  $objTree->addItem($objItem, 'item_'.$objItem->getId());
                }else{
                  $objNavigationTree->addItem($objItem, 'item_'.$objItem->getId());
                }
              }
            }
          }
        }
      }

      /**
       * add to parent tree
       */
      if(isset($objTree) && is_object($objTree) && $objTree instanceof NavigationTree){
        $objNavigationTree->addToParentTree($objTree, 'tree_'.$objTree->getId());
      }

      if($blnSetMainNavigation) $this->objMainNavigation = $objNavigationTree;

      return $objNavigationTree;
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * addGlobalTree
   * @param NavigationTree $objNavigationTree
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private function addGlobalTree(NavigationTree &$objNavigationTree, $intPageTypeId, $arrFilter, $intDepth = 99){
    try{         
      $arrPageTypeRootLevelGroupIds = array($this->core->sysConfig->page_types->product_tree->id => $this->core->sysConfig->root_level_groups->product, $this->core->sysConfig->page_types->press_area->id => $this->core->sysConfig->root_level_groups->press, $this->core->sysConfig->page_types->courses->id => $this->core->sysConfig->root_level_groups->course, $this->core->sysConfig->page_types->events->id => $this->core->sysConfig->root_level_groups->event);

      $objNavigationData = $this->getModelFolders()->loadWebsiteGlobalTree($arrFilter['ParentId'], $arrFilter, $arrPageTypeRootLevelGroupIds[$intPageTypeId], $intDepth);

      if(count($objNavigationData) > 0){
        $intSortTypeId = ($this->objPage instanceof Page) ? $this->objPage->getFieldValue('entry_sorttype') : 0;
        $intTreeId = 0;

        foreach($objNavigationData as $objNavigationItem){

          if($intTreeId != $objNavigationItem->idFolder){

            /**
             * add to parent tree
             */
            if(isset($objTree) && is_object($objTree) && $objTree instanceof NavigationTree){
              $objNavigationTree->addToParentTree($objTree, 'tree_'.$objTree->getId());
            }

            $objTree = new NavigationTree();
            $objTree->setTitle($objNavigationItem->folderTitle);
            $objTree->setId($objNavigationItem->idFolder);
            $objTree->setTypeId($objNavigationItem->idGlobalTypes);
            $objTree->setParentId(($objNavigationItem->parentId == $arrFilter['ParentId']) ? $objNavigationTree->getId() : $objNavigationItem->parentId);
            $objTree->setItemId($objNavigationItem->folderId);
            $objTree->setChanged($objNavigationItem->changed);            
            if($intSortTypeId == $this->core->sysConfig->sort->types->alpha->id){
              $objTree->setOrder($objNavigationItem->folderTitle);
            }else{
              $objTree->setOrder($objNavigationItem->folderOrder);
            }
            $objTree->setUrl($objNavigationTree->getUrl().$objNavigationItem->url);
             
            $intTreeId = $objNavigationItem->idFolder;
          }

          if($objNavigationItem->globalId != null){
            if($objNavigationItem->isStartGlobal == 1){
              $objTree->setUrl($objNavigationTree->getUrl().$objNavigationItem->url);
            }else{
              $objItem = new NavigationItem();
              $objItem->setTitle($objNavigationItem->globalTitle);
              $objItem->setUrl($objNavigationTree->getUrl().$objNavigationItem->url);
              $objItem->setId($objNavigationItem->idGlobal);
              $objTree->setTypeId($objNavigationItem->idGlobalTypes);
              $objTree->setParentId(($objNavigationItem->parentId == $arrFilter['ParentId']) ? $objNavigationTree->getId() : $objNavigationItem->parentId);
              $objItem->setItemId($objNavigationItem->globalId);
              $objItem->setChanged($objNavigationItem->changed);
              if($intSortTypeId == $this->core->sysConfig->sort->types->alpha->id){
                $objItem->setOrder($objNavigationItem->globalTitle);
              }else{
                $objItem->setOrder($objNavigationItem->globalOrder);
              }
              $objTree->addItem($objItem, 'item_'.$objItem->getId());
            }
          }
        }

        /**
         * add to parent tree
         */
        if(isset($objTree) && is_object($objTree) && $objTree instanceof NavigationTree){
          $objNavigationTree->addToParentTree($objTree, 'tree_'.$objTree->getId());
        }
      }

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * loadStaticSubNavigation
   * @param integer $intDepth
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadStaticSubNavigation($intDepth = 2){
    try{
      $this->getModelFolders();

      $this->evaluateRootFolderId();

      $objNavigationTree = new NavigationTree();
      $objNavigationTree->setId($this->intRootFolderId);
      
      if($this->intRootFolderId > 0){
        $objSubNavigationData = $this->objModelFolders->loadWebsiteStaticSubNavigation($this->intRootFolderId, $intDepth);

        $intTreeId = 0;
        foreach($objSubNavigationData as $objSubNavigationItem){

          if($this->intRootFolderId == $objSubNavigationItem->idFolder){
            if($objSubNavigationItem->isStartPage == 1){
              $objNavigationTree->setTitle($objSubNavigationItem->folderTitle);
              $objNavigationTree->setUrl($this->getUrlFor($objSubNavigationItem->languageCode, $objSubNavigationItem->url));
            }else{
              if($objSubNavigationItem->pageId != null){
                $objItem = new NavigationItem();
                $objItem->setTitle($objSubNavigationItem->pageTitle);
                $objItem->setUrl($this->getUrlFor($objSubNavigationItem->languageCode, $objSubNavigationItem->url));
                $objItem->setId($objSubNavigationItem->idPage);
                $objItem->setParentId($objSubNavigationItem->idFolder);
                $objItem->setOrder($objSubNavigationItem->pageOrder);
                $objItem->setItemId($objSubNavigationItem->pageId);
                $objNavigationTree->addItem($objItem, 'item_'.$objItem->getId());
              }
            }
          }else{
            if($intTreeId != $objSubNavigationItem->idFolder){
              /**
               * add to parent tree
               */
              if(isset($objTree) && is_object($objTree) && $objTree instanceof NavigationTree){
                $objNavigationTree->addToParentTree($objTree, 'tree_'.$objTree->getId());
              }

              $objTree = new NavigationTree();
              $objTree->setTitle($objSubNavigationItem->folderTitle);
              $objTree->setId($objSubNavigationItem->idFolder);
              $objTree->setParentId($objSubNavigationItem->parentId);
              $objTree->setOrder($objSubNavigationItem->folderOrder);
              $objTree->setItemId($objSubNavigationItem->folderId);

              $intTreeId = $objSubNavigationItem->idFolder;
            }

            if($objSubNavigationItem->pageId != null){
              if($objSubNavigationItem->isStartPage == 1){
                $objTree->setUrl($this->getUrlFor($objSubNavigationItem->languageCode, $objSubNavigationItem->url));
                //$objTree->setItemId($objSubNavigationItem->pageId);
              }else{
                $objItem = new NavigationItem();
                $objItem->setTitle($objSubNavigationItem->pageTitle);
                $objItem->setUrl($this->getUrlFor($objSubNavigationItem->languageCode, $objSubNavigationItem->url));
                $objItem->setId($objSubNavigationItem->idPage);
                $objItem->setParentId($objSubNavigationItem->idFolder);
                $objItem->setOrder($objSubNavigationItem->pageOrder);
                $objItem->setItemId($objSubNavigationItem->pageId);
                $objTree->addItem($objItem, 'item_'.$objItem->getId());
              }
            }
          }
        }
      }

      /**
       * add to parent tree
       */
      if(isset($objTree) && is_object($objTree) && $objTree instanceof NavigationTree){
        $objNavigationTree->addToParentTree($objTree, 'tree_'.$objTree->getId());
      }

      $this->objSubNavigation = $objNavigationTree;
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * loadSitemap
   * @retrun NavigationTree
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadSitemap(){
    try{
      $this->getModelFolders();

      $this->evaluateRootFolderId();

      $objSitemap = new NavigationTree();
      $objSitemap->setId(0);

      if($this->intRootLevelId > 0){

        $objNavigationData = $this->objModelFolders->loadWebsiteRootLevelChilds($this->intRootLevelId, 99, -1, true, true);

        $intTreeId = 0;
        foreach($objNavigationData as $objNavigationItem){
          if($objNavigationItem->title != ''){
            if($objNavigationItem->isStartPage == 1 && $objNavigationItem->depth == 0){
  
              /**
               * add to parent tree
               */
              if(isset($objTree) && is_object($objTree) && $objTree instanceof NavigationTree){
                $objSitemap->addToParentTree($objTree, 'tree_'.$objTree->getId());
              }
  
              $objTree = new NavigationTree();
              $objTree->setTitle(($objNavigationItem->folderTitle != '' ? $objNavigationItem->folderTitle : $objNavigationItem->title));
              $objTree->setId($objNavigationItem->idFolder);
              $objTree->setParentId(0);
              $objTree->setTypeId($objNavigationItem->idPageTypes);
              $objTree->setItemId($objNavigationItem->folderId);
              $objTree->setOrder($objNavigationItem->folderOrder);
              $objTree->setUrl(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->external : $this->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url));
              $objTree->setTarget(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->target : '');
              $objTree->setChanged($objNavigationItem->changed);
  
              $arrPageGlobaLinkTypes = array($this->core->sysConfig->page_types->product_tree->id, $this->core->sysConfig->page_types->press_area->id, $this->core->sysConfig->page_types->courses->id, $this->core->sysConfig->page_types->events->id);
              if(in_array($objNavigationItem->idPageTypes, $arrPageGlobaLinkTypes)){
                $arrFilter = array();
                if($this->objPage instanceof Page && $this->objPage->getElementId() == $objNavigationItem->idPage) {
                  $arrFilter = array(
                        'CategoryId'  => $this->objPage->getFieldValue('entry_category'),
                        'LabelId'     => $this->objPage->getFieldValue('entry_label'),
                        'SorttypeId'  => $this->objPage->getFieldValue('entry_sorttype'),
                        'ParentId'    => $this->objPage->getFieldValue('entry_point'),
                  );
                }else{
                  $arrFilter = array(
                      'CategoryId'  => $objNavigationItem->entry_category,
                      'LabelId'     => $objNavigationItem->entry_label,
                      'SorttypeId'  => $objNavigationItem->entry_sorttype,
                      'ParentId'    => $objNavigationItem->entry_point,
                  );
                }
                if(count($arrFilter) > 0) {                  
                  $this->addGlobalTree($objTree, $objNavigationItem->idPageTypes, $arrFilter, 999);
                }
              }
  
              $intTreeId = $objNavigationItem->idFolder;
  
            }else{
  
              if($intTreeId != $objNavigationItem->idFolder){
  
                /**
                 * add to parent tree
                 */
                if(isset($objTree) && is_object($objTree) && $objTree instanceof NavigationTree){
                  $objSitemap->addToParentTree($objTree, 'tree_'.$objTree->getId());
                }
  
                $objTree = new NavigationTree();
                $objTree->setTitle($objNavigationItem->folderTitle);
                $objTree->setId($objNavigationItem->idFolder);
                $objTree->setTypeId($objNavigationItem->idPageTypes);
                $objTree->setParentId($objNavigationItem->parentId);
                $objTree->setItemId($objNavigationItem->folderId);
                $objTree->setOrder($objNavigationItem->folderOrder);
                $objTree->setUrl(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->external : $this->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url));
                $objTree->setTarget(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->target : '');
                $objTree->setChanged($objNavigationItem->changed);
                
                $arrPageGlobaLinkTypes = array($this->core->sysConfig->page_types->product_tree->id, $this->core->sysConfig->page_types->press_area->id, $this->core->sysConfig->page_types->courses->id, $this->core->sysConfig->page_types->events->id);
                if(in_array($objNavigationItem->idPageTypes, $arrPageGlobaLinkTypes)){
                  $arrFilter = array();
                  if($this->objPage instanceof Page && $this->objPage->getElementId() == $objNavigationItem->idPage) {
                    $arrFilter = array(
                        'CategoryId'  => $this->objPage->getFieldValue('entry_category'),
                        'LabelId'     => $this->objPage->getFieldValue('entry_label'),
                        'SorttypeId'  => $this->objPage->getFieldValue('entry_sorttype'),
                        'ParentId'    => $this->objPage->getFieldValue('entry_point'),
                    );
                  }else{
                    $arrFilter = array(
                        'CategoryId'  => $objNavigationItem->entry_category,
                        'LabelId'     => $objNavigationItem->entry_label,
                        'SorttypeId'  => $objNavigationItem->entry_sorttype,
                        'ParentId'    => $objNavigationItem->entry_point,
                    );
                  }
                  if(count($arrFilter) > 0) {
                    $this->addGlobalTree($objTree, $objNavigationItem->idPageTypes, $arrFilter, 999);
                  }
                }
  
                $intTreeId = $objNavigationItem->idFolder;
              }
  
              if($objNavigationItem->pageId != null){
                if($objNavigationItem->isStartPage == 1 && isset($objTree)){
                  $objTree->setUrl(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->external : $this->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url));
                  $objTree->setTarget(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->target : '');
                }else{
                  $objItem = new NavigationItem();
                  $objItem->setTitle($objNavigationItem->title);
                  $objItem->setUrl(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->external : $this->getUrlFor($objNavigationItem->languageCode, $objNavigationItem->url));
                  $objItem->setTarget(($objNavigationItem->idPageTypes == $this->core->sysConfig->page_types->external->id) ? $objNavigationItem->target : '');
                  $objItem->setId($objNavigationItem->idPage);
                  $objItem->setTypeId($objNavigationItem->idPageTypes);
                  $objItem->setParentId($objNavigationItem->idFolder);
                  $objItem->setItemId($objNavigationItem->pageId);
                  $objItem->setOrder($objNavigationItem->pageOrder);
                  $objItem->setChanged($objNavigationItem->changed);
                  if(isset($objTree)){
                    $objTree->addItem($objItem, 'item_'.$objItem->getId());
                  }else{
                    $objSitemap->addItem($objItem, 'item_'.$objItem->getId());
                  }
                }
              }
            }
          }
        }
      }

      /**
       * add to parent tree
       */
      if(isset($objTree) && is_object($objTree) && $objTree instanceof NavigationTree){
        $objSitemap->addToParentTree($objTree, 'tree_'.$objTree->getId());        
      }

      return $objSitemap;
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * evaluateRootFolderId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function evaluateRootFolderId(){
    if(isset($this->objPage) && is_object($this->objPage) && $this->intRootFolderId == 0){
      if($this->objPage->getParentTypeId() == $this->core->sysConfig->parent_types->folder){
        $this->objParentFolders = $this->ParentFolders();

        if(count($this->objParentFolders) > 0){
          $this->intRootFolderId = $this->objParentFolders[count($this->objParentFolders) - 1]->id;
          $this->strRootFolderId = $this->objParentFolders[count($this->objParentFolders) - 1]->folderId;
        }
      }
    }
  }

  /**
   * getParentFolderIds
   * @return array $arrParentFolderIds
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getParentFolderIds(){
    $arrParentFolderIds = array();

    if(count($this->objParentFolders) > 0){
      foreach($this->objParentFolders as $objParentFolder){
        $arrParentFolderIds[] = $objParentFolder->folderId;

      }
    }

    return $arrParentFolderIds;
  }

  /**
   * getGlobalParentFolderIds
   * @return array $arrGlobalParentFolderIds
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getGlobalParentFolderIds(){
    $arrGlobalParentFolderIds = array();
    if($this->objGlobalParentFolders === null && $this->objPage instanceof Page && $this->objPage->ChildPage() !== null){
      $this->objGlobalParentFolders = $this->getModelFolders()->loadGlobalParentFolders(($this->objPage->ChildPage()->getNavParentId() > 0 ? $this->objPage->ChildPage()->getNavParentId() : $this->objPage->ChildPage()->getParentId()), $this->objPage->ChildPage()->getRootLevelGroupId());
    }

    if(count($this->objGlobalParentFolders) > 0){
      foreach($this->objGlobalParentFolders as $objGlobalParentFolder){
        $arrGlobalParentFolderIds[] = $objGlobalParentFolder->folderId;
      }
    }

    return $arrGlobalParentFolderIds;
  }

  /**
   * secuirtyZoneCheck
   * @return boolean
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function secuirtyZoneCheck(){
    $blnSecureZoneCheck = false;

    if(count($this->ParentFolders()) > 0){
      foreach($this->ParentFolders() as $objParentFolderData){
        if($objParentFolderData->isSecure == 1){
          $blnSecureZoneCheck = true;
          break;
        }
      }
    }
    return $blnSecureZoneCheck;
  }

  /**
   * checkZonePrivileges
   * @return boolean
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function checkZonePrivileges(){
    $blnAuthorized = true;

    Security::get()->addFoldersToAcl($this->getModelFolders(), Security::ZONE_WEBSITE);

    if(count($this->ParentFolders()) > 0){
      foreach($this->ParentFolders() as $objParentFolderData){
        if($objParentFolderData->isSecure == 1 && !Security::get()->isAllowed(Security::RESOURCE_FOLDER_PREFIX.$objParentFolderData->id, Security::PRIVILEGE_VIEW, true, false, Security::ZONE_WEBSITE) && !Security::get()->isAllowed(Security::RESOURCE_FOLDER_PREFIX.$objParentFolderData->id.'_'.$this->intLanguageId, Security::PRIVILEGE_VIEW, true, false, Security::ZONE_WEBSITE)){
          $blnAuthorized = false;
          break;
        }
      }
    }
    return $blnAuthorized;
  }

  /**
   * getUrlFor
   * @param string $strLanguageCode
   * @param string $strItemUrl
   * @param null|string $strSegmentCode
   * @param null|string $strUrlPrefix
   * @return string
   */
  public function getUrlFor($strLanguageCode, $strItemUrl, $strSegmentCode = null, $strUrlPrefix = null){
    $strUrl = '';
    
    // url prefix
    if(!empty($strUrlPrefix)){
      $strUrl .= '/'.strtolower($strUrlPrefix);  
    }else if($this->blnHasUrlPrefix){
      $strUrl .= '/'.$this->strUrlPrefix;  
    }
    
    // segmentation
    if(!empty($strSegmentCode)){
      $strUrl .= '/'.strtolower($strSegmentCode);
    }else if($this->blnHasSegments){
      $strUrl .= '/'.$this->strSegmentCode;
    }
    
    $strUrl .= '/'.strtolower($strLanguageCode).'/'.$strItemUrl;

    return $strUrl;
  }

  /**
   * getModelFolders
   * @return Model_Folders
   * @author Thomas Schedler <tsh@massiveart.com>
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
      $this->objModelFolders->setLanguageId($this->intLanguageId);
      if($this->blnHasSegments){
        $this->objModelFolders->setSegmentId($this->intSegmentId);
      }
    }

    return $this->objModelFolders;
  }

  /**
   * setPage
   * @param Page $objPage
   */
  public function setPage(Page &$objPage){
    $this->objPage = $objPage;
  }

  /**
   * setBaseUrl
   * @param $objBaseUrl
   */
  public function setBaseUrl(Zend_Db_Table_Row_Abstract $objBaseUrl){
    $this->objBaseUrl = $objBaseUrl;
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
   * getRootFolderId
   * @param string $strRootFolderId
   */
  public function getRootFolderId(){
    return $this->strRootFolderId;
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

  /**
   * setHasSegments
   * @param boolean $blnHasSegments
   */
  public function setHasSegments($blnHasSegments, $blnValidate = true){
    if($blnValidate == true){
      if($blnHasSegments === true || $blnHasSegments === 'true' || $blnHasSegments == 1){
        $this->blnHasSegments = true;
      }else{
        $this->blnHasSegments = false;
      }
    }else{
      $this->blnHasSegments = $blnHasSegments;
    }
  }

  /**
   * getHasSegments
   * @return boolean $blnHasSegments
   */
  public function getHasSegments($blnReturnAsNumber = true){
    if($blnReturnAsNumber == true){
      if($this->blnHasSegments == true){
        return 1;
      }else{
        return 0;
      }
    }else{
      return $this->blnHasSegments;
    }
  }
    
  /**
   * setSegmentId
   * @param integer $intSegmentId
   */
  public function setSegmentId($intSegmentId){
    $this->intSegmentId = $intSegmentId;
  }

  /**
   * getSegmentId
   * @param integer $intSegmentId
   */
  public function getSegmentId(){
    return $this->intSegmentId;
  }

  /**
   * setSegmentCode
   * @param string $strSegmentCode
   */
  public function setSegmentCode($strSegmentCode){
    $this->strSegmentCode = $strSegmentCode;
  }

  /**
   * getSegmentCode
   * @param string $strSegmentCode
   */
  public function getSegmentCode(){
    return $this->strSegmentCode;
  }
  
  
  /**
   * setHasUrlPrefix
   * @param boolean $blnHasUrlPrefix
   */
  public function setHasUrlPrefix($blnHasUrlPrefix, $blnValidate = true){
    if($blnValidate == true){
      if($blnHasUrlPrefix === true || $blnHasUrlPrefix === 'true' || $blnHasUrlPrefix == 1){
        $this->blnHasUrlPrefix = true;
      }else{
        $this->blnHasUrlPrefix = false;
      }
    }else{
      $this->blnHasUrlPrefix = $blnHasUrlPrefix;
    }
  }

  /**
   * getHasUrlPrefix
   * @return boolean $blnHasUrlPrefix
   */
  public function getHasUrlPrefix($blnReturnAsNumber = true){
    if($blnReturnAsNumber == true){
      if($this->blnHasUrlPrefix == true){
        return 1;
      }else{
        return 0;
      }
    }else{
      return $this->blnHasUrlPrefix;
    }
  }
  
  /**
   * setUrlPrefix
   * @param string $strUrlPrefix
   */
  public function setUrlPrefix($strUrlPrefix){
    $this->strUrlPrefix = $strUrlPrefix;
  }

  /**
   * getUrlPrefix
   * @param string $strUrlPrefix
   */
  public function getUrlPrefix(){
    return $this->strUrlPrefix;
  }
}
?>