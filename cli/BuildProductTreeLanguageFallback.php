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
 * @package    cli
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

define('APPLICATION_ENV', 'development');

/**
 * include general (autoloader, config)
 */
require_once(dirname(__FILE__).'/../sys_config/general.inc.php');

try{
     
  $objConsoleOpts = new Zend_Console_Getopt(
    array(
      'folderId|f=i'        => 'Folder Id',
      'rootLevelId|r=i'     => 'RootLevel Id',
      'fromLanguageId|fl=i' => 'From Language Id',
      'toLanguageId|tl=i'   => 'To Language Id',
    )
  );
  
  echo "build tree language variant\n---------------------------\n";
  
  if(isset($objConsoleOpts->fromLanguageId) && isset($objConsoleOpts->toLanguageId)){
    
    echo "load fist level of the tree ...\n";    
    
    require_once GLOBAL_ROOT_PATH.$core->sysConfig->path->zoolu_modules.'core/models/Folders.php';
    $objModelFolders = new Model_Folders();
    $objModelFolders->setLanguageId($objConsoleOpts->fromLanguageId);
    
    if(isset($objConsoleOpts->folderId) && $objConsoleOpts->folderId > 0){
      $objProducts = $objModelFolders->loadGlobalChildNavigation($objConsoleOpts->folderId, $core->sysConfig->root_level_groups->product);
    }else if(isset($objConsoleOpts->rootLevelId) && $objConsoleOpts->rootLevelId > 0){
      $objProducts = $objModelFolders->loadGlobalRootNavigation($objConsoleOpts->rootLevelId, $core->sysConfig->root_level_groups->product);
    }
    
    // simulate user auth
    $obj = new stdClass();
    $obj->id = 3; //user id
    Zend_Auth::getInstance()->getStorage()->write($obj);
    
    if(isset($objProducts) && count($objProducts)){
      buildTreeLanguageVariantNow($objProducts);
    }    
    
  }
  echo "---------------------------\n";
  
}catch (Exception $exc) {
  echo($exc);
}

/*--------------------------------------------------------
 * some functions building the tree language variante
 *-------------------------------------------------------/

/**
 * buildTreeLanguageVariantNow
 */
function buildTreeLanguageVariantNow($objProducts, $intLevel = 0){
  global $objModelFolders, $core;
  
  foreach($objProducts as $objProduct){
    
    for($i = 0; $i < ($intLevel * 2); $i++){
      echo "-";
    }
    echo $objProduct->id.' :: '.$objProduct->elementType.' :: '.$objProduct->title.'|'.$objProduct->guiTitle.' :: '.$objProduct->genericFormId.' :: '.$objProduct->templateId."\n";
    
    switch($objProduct->elementType){
      case 'folder':
        $objNewProduct = $objModelFolders->loadGlobalChildNavigation($objProduct->id, $core->sysConfig->root_level_groups->product);
        buildTreeLanguageVariantNow($objNewProduct, ($intLevel + 1));
        break;
      case 'global':
        buildProdcutLanguageVariant($objProduct);
        break;
    }    
  }
}

/**
 * buildProdcutLanguageVariant
 */
function buildProdcutLanguageVariant($objProduct){
  global $objConsoleOpts, $core;
  
  // product form
  $objProductForm = new GenericForm();
  $objProductForm->Setup()->setElementId($objProduct->id);
  $objProductForm->Setup()->setElementLinkId($objProduct->linkGlobalId);
  $objProductForm->Setup()->setFormId($objProduct->genericFormId);
  $objProductForm->Setup()->setTemplateId($objProduct->templateId);
  $objProductForm->Setup()->setFormVersion($objProduct->version);
  $objProductForm->Setup()->setActionType($core->sysConfig->generic->actions->edit);
  $objProductForm->Setup()->setLanguageId($objConsoleOpts->fromLanguageId);
  $objProductForm->Setup()->setFormLanguageId($core->sysConfig->languages->default->id);
  $objProductForm->Setup()->setModelSubPath('global/models/');

  // load basic generic form
  $objProductForm->Setup()->loadGenericForm();

  // load generic form structur
  $objProductForm->Setup()->loadGenericFormStructure();

  // init data type object
  $objProductForm->initDataTypeObject();
  
  // load data
  $objProductForm->loadFormData();
    

  // set fallback language
  $objProductForm->Setup()->setLanguageFallbackId($objConsoleOpts->toLanguageId);
  
  // reset fields
  foreach($objProductForm->Setup()->FieldNames() as $strField => $intType){
    if($strField != 'title' && $strField != 'internal_links_title' && $strField != 'internal_links' && $strField != 'category' && $strField != 'label'){
      $objProductForm->Setup()->getField($strField)->setValue(null);
    }
  }
  
  // reset multi regions
  foreach($objProductForm->Setup()->MultiplyRegionIds() as $intRegionId){
    $objProductForm->Setup()->getRegion($intRegionId)->resetRegionInstances();
  }
    
  // set new language
  $objProductForm->saveFormData();
}

?>