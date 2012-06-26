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
      'deleteUrls|du=i'     => 'Delete Urls'
    )
  );
  
  echo "build tree language variant\n---------------------------\n";
  
  if(isset($objConsoleOpts->fromLanguageId) && isset($objConsoleOpts->toLanguageId)){
    
    echo "load first level of the tree ...\n";    
    
    require_once GLOBAL_ROOT_PATH.$core->sysConfig->path->zoolu_modules.'core/models/Folders.php';
    $objModelFolders = new Model_Folders();
    $objModelFolders->setLanguageId($objConsoleOpts->fromLanguageId);
    
    if(isset($objConsoleOpts->folderId) && $objConsoleOpts->folderId > 0){
      $objPages = $objModelFolders->loadChildNavigation($objConsoleOpts->folderId);
    }else if(isset($objConsoleOpts->rootLevelId) && $objConsoleOpts->rootLevelId > 0){
      $objPages = $objModelFolders->loadRootNavigation($objConsoleOpts->rootLevelId);
    }
    
    // simulate user auth
    $obj = new stdClass();
    $obj->id = 2; //user id
    Zend_Auth::getInstance()->getStorage()->write($obj);
    
    if(isset($objPages) && count($objPages)){
      buildTreeLanguageVariantNow($objPages);
    } 

    // delete urls
    if(isset($objConsoleOpts->deleteUrls) && $objConsoleOpts->deleteUrls == 1){
      deleteUrls();  
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
function buildTreeLanguageVariantNow($objPages, $intLevel = 0){
  global $objModelFolders, $core;
  
  foreach($objPages as $objPage){
    
    for($i = 0; $i < ($intLevel * 2); $i++){
      echo "-";
    }
    echo $objPage->id.' :: '.$objPage->elementType.' :: '.$objPage->title.'|'.$objPage->guiTitle.' :: '.$objPage->genericFormId.' :: '.$objPage->templateId."\n";
    
    switch($objPage->elementType){
      case 'folder':
        $objNewPage = $objModelFolders->loadChildNavigation($objPage->id);
        buildFolderLanguageVariant($objPage);
        buildTreeLanguageVariantNow($objNewPage, ($intLevel + 1));
        break;
      case 'page':
        buildPageLanguageVariant($objPage);
        break;
    }    
  }
}

/**
 * buildFolderLanguageVariant
 */
function buildFolderLanguageVariant($objFolder){
  global $objConsoleOpts, $core;
  
  // folder form
  $objFolderForm = new GenericForm();
  $objFolderForm->Setup()->setElementId($objFolder->id);
  $objFolderForm->Setup()->setFormId($objFolder->genericFormId);
  $objFolderForm->Setup()->setActionType($core->sysConfig->generic->actions->edit);
  $objFolderForm->Setup()->setLanguageId($objConsoleOpts->fromLanguageId);
  $objFolderForm->Setup()->setFormLanguageId($core->sysConfig->languages->default->id);
  
  // load basic generic form
  $objFolderForm->Setup()->loadGenericForm();

  // load generic form structur
  $objFolderForm->Setup()->loadGenericFormStructure();

  // init data type object
  $objFolderForm->initDataTypeObject();
  
  // load data
  $objFolderForm->loadFormData();
      
  // set new language
  $objFolderForm->Setup()->setLanguageId($objConsoleOpts->toLanguageId);
  
  // set new language
  $objFolderForm->saveFormData();
  
}
/**
 * buildPageLanguageVariant
 */
function buildPageLanguageVariant($objPage){
  global $objConsoleOpts, $core;
  
  // product form
  $objPageForm = new GenericForm();
  $objPageForm->Setup()->setElementId($objPage->id);
  $objPageForm->Setup()->setFormId($objPage->genericFormId);
  $objPageForm->Setup()->setTemplateId($objPage->templateId);
  $objPageForm->Setup()->setFormVersion($objPage->version);
  $objPageForm->Setup()->setActionType($core->sysConfig->generic->actions->edit);
  $objPageForm->Setup()->setLanguageId($objConsoleOpts->fromLanguageId);
  $objPageForm->Setup()->setFormLanguageId($core->sysConfig->languages->default->id);
  $objPageForm->Setup()->setModelSubPath('cms/models/');

  // load basic generic form
  $objPageForm->Setup()->loadGenericForm();

  // load generic form structur
  $objPageForm->Setup()->loadGenericFormStructure();

  // init data type object
  $objPageForm->initDataTypeObject();
  
  // load data
  $objPageForm->loadFormData();
    
  // set new language
  $objPageForm->Setup()->setLanguageId($objConsoleOpts->toLanguageId);
  
  //rest url
  if($objPageForm->Setup()->getField('url')){
    $objPageForm->Setup()->getField('url')->setValue('');
  }
  
  // set new language
  $objPageForm->saveFormData();
}
/**
 * deleteUrls
 */
function deleteUrls(){
  global $objConsoleOpts, $core;

  if(isset($objConsoleOpts->toLanguageId)){
    $sql = "DELETE FROM `urls` WHERE `urls`.`idLanguages` = ".$objConsoleOpts->toLanguageId." AND `urls`.`url` != '';";  
    $stmt = $core->dbh->prepare($sql);
    $stmt->execute();
    echo "---------------------------\nUrls deleted!\n";
  }
}

?>