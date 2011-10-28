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
      'languageId|l=i'      => 'Language Id',
      'urlLanguageId|ul=i'  => 'URL Language Id',
    )
  );
  
  echo "build product tree urls\n---------------------------\n";
  
  if(isset($objConsoleOpts->languageId) && isset($objConsoleOpts->urlLanguageId)){
    
    echo "load fist level of the tree ...\n";    
    
    require_once GLOBAL_ROOT_PATH.$core->sysConfig->path->zoolu_modules.'core/models/Folders.php';
    $objModelFolders = new Model_Folders();
    $objModelFolders->setLanguageId($objConsoleOpts->languageId);
    
    if(isset($objConsoleOpts->folderId) && $objConsoleOpts->folderId > 0){
      $objProducts = $objModelFolders->loadGlobalChildNavigation($objConsoleOpts->folderId, $core->sysConfig->root_level_groups->product);
    }else if(isset($objConsoleOpts->rootLevelId) && $objConsoleOpts->rootLevelId > 0){
      $objProducts = $objModelFolders->loadGlobalRootNavigation($objConsoleOpts->rootLevelId, $core->sysConfig->root_level_groups->product); //FIXME: Only loads folders!
    }
    
    // simulate user auth
    $obj = new stdClass();
    $obj->id = 3; //user id
    Zend_Auth::getInstance()->getStorage()->write($obj);
    
    if(isset($objProducts) && count($objProducts)){
      buildTreeUrlsNow($objProducts);
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
 * buildTreeUrlsNow
 */
function buildTreeUrlsNow($objProducts, $intLevel = 0){
  global $objModelFolders, $core;
  
  foreach($objProducts as $objProduct){
    
    for($i = 0; $i < ($intLevel * 2); $i++){
      echo "-";
    }
    echo $objProduct->id.' :: '.$objProduct->elementType.' :: '.$objProduct->title.'|'.$objProduct->guiTitle.' :: '.$objProduct->genericFormId.' :: '.$objProduct->templateId."\n";
    
    switch($objProduct->elementType){
      case 'folder':
        $objNewProduct = $objModelFolders->loadGlobalChildNavigation($objProduct->id, $core->sysConfig->root_level_groups->product);
        buildTreeUrlsNow($objNewProduct, ($intLevel + 1));
        break;
      case 'global':
        if($objProduct->genericFormId != '') buildProdcutUrl($objProduct);
        break;
    }
  }
}

/**
 * buildProdcutLanguageVariant
 */
function buildProdcutUrl($objProduct){
  global $objConsoleOpts, $core;
  
  // product form
  $objProductForm = new GenericForm();
  $objProductForm->Setup()->setElementId($objProduct->id);
  $objProductForm->Setup()->setElementLinkId($objProduct->linkGlobalId);
  $objProductForm->Setup()->setFormId($objProduct->genericFormId);
  $objProductForm->Setup()->setTemplateId($objProduct->templateId);
  $objProductForm->Setup()->setFormVersion($objProduct->version);
  $objProductForm->Setup()->setActionType($core->sysConfig->generic->actions->edit);
  $objProductForm->Setup()->setLanguageId($objConsoleOpts->languageId);
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
  
  
  // product url form
  $objProductUrlForm = new GenericForm();
  $objProductUrlForm->Setup()->setElementId($objProduct->id);
  $objProductUrlForm->Setup()->setElementLinkId($objProduct->linkGlobalId);
  $objProductUrlForm->Setup()->setFormId($objProduct->genericFormId);
  $objProductUrlForm->Setup()->setTemplateId($objProduct->templateId);
  $objProductUrlForm->Setup()->setFormVersion($objProduct->version);
  $objProductUrlForm->Setup()->setActionType($core->sysConfig->generic->actions->edit);
  $objProductUrlForm->Setup()->setLanguageId($objConsoleOpts->urlLanguageId);
  $objProductUrlForm->Setup()->setFormLanguageId($core->sysConfig->languages->default->id);
  $objProductUrlForm->Setup()->setModelSubPath('global/models/');

  // load basic generic form
  $objProductUrlForm->Setup()->loadGenericForm();

  // load generic form structur
  $objProductUrlForm->Setup()->loadGenericFormStructure();

  // init data type object
  $objProductUrlForm->initDataTypeObject();
  
  // load data
  $objProductUrlForm->loadFormData();
  
  //rest url
  if($objProductForm->Setup()->getField('url') && $objProductUrlForm->Setup()->getField('title') && $objProductUrlForm->Setup()->getField('title')->getValue() != ''){
    $_POST['url_EditableUrl'] = trim($objProductUrlForm->Setup()->getField('title')->getValue(),' _-+!?');
    $objProductForm->Setup()->getField('url')->save($objProduct->linkGlobalId, 'global');
    $objProductForm->Setup()->getField('url')->removeUrlHistory($objProduct->linkGlobalId, 'global');
  }
}

?>