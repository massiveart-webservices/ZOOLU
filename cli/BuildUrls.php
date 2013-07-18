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
 * @package    cli
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

define('APPLICATION_ENV', 'development');

/**
 * include general (autoloader, config)
 */
require_once(dirname(__FILE__) . '/../sys_config/general.inc.php');
require_once GLOBAL_ROOT_PATH . $core->sysConfig->path->zoolu_modules . 'core/models/Folders.php';
require_once GLOBAL_ROOT_PATH . $core->sysConfig->path->zoolu_modules . 'core/models/RootLevels.php';
require_once GLOBAL_ROOT_PATH . $core->sysConfig->path->zoolu_modules . 'core/models/Urls.php';
require_once GLOBAL_ROOT_PATH . 'library/massiveart/generic/forms/validators/UniqueUrl.php';

try {
    //Load Options
    $objOpts = new Zend_Console_Getopt(
        array(
             'rootLevelId|r=i' => 'RootLevelId',
             'folderId|f=i'    => 'FolderId',
             'languageId|l=i'  => 'LanguageId',
             'delete-urls|d'   => 'Delete URLs from the given RootLevel'
        )
    );
    $objOpts->parse();

    // simulate user auth
    $obj = new stdClass();
    $obj->id = 3; //user id
    $objAuth = Zend_Auth::getInstance();
    $objAuth->setStorage(new Zend_Auth_Storage_Session('zoolu'));
    $objAuth->getStorage()->write($obj);

    //Load RootLevel Information
    $objModelRootLevels = new Model_RootLevels();
    $objModelRootLevels->setLanguageId($objOpts->languageId);

    $objRootLevels = $objModelRootLevels->loadRootLevelById($objOpts->rootLevelId);
    $objRootLevel = null;
    if (count($objRootLevels) > 0) {
        $objRootLevel = $objRootLevels->current();
    } else {
        throw new Exception('RootLevel not found!');
    }

    if (isset($objOpts->d)) {
        echo "Deleting old URLs...\n";
        $objModelUrls = new Model_Urls();
        $objModelUrls->deleteUrlsByRootLevelIdAndLanguage($objRootLevel->id, $objOpts->languageId);
        echo "Done!\n";
    }

    //Load Elements
    $objModelFolders = new Model_Folders();
    $objModelFolders->setLanguageId($objOpts->languageId);

    $objElements = null;
    switch ($objRootLevel->idRootLevelTypes) {
        case $core->sysConfig->root_level_types->portals:
            if (isset($objOpts->folderId) && $objOpts->folderId > 0) {
                $objElements = $objModelFolders->loadChildNavigation($objOpts->folderId);
            } elseif (isset($objOpts->rootLevelId) && $objOpts->rootLevelId > 0) {
                $objElements = $objModelFolders->loadRootNavigation($objOpts->rootLevelId);
            }
            break;
        case $core->sysConfig->root_level_types->global:
            if (isset($objOpts->folderId) && $objOpts->folderId > 0) {
                $objElements = $objModelFolders->loadGlobalChildNavigation($objOpts->folderId, $objRootLevel->idRootLevelGroups);
            } elseif (isset($objOpts->rootLevelId) && $objOpts->rootLevelId > 0) {
                $objElements = $objModelFolders->loadGlobalRootNavigation($objOpts->rootLevelId, $objRootLevel->idRootLevelGroups);
            }
            break;
    }

    echo "Start building URLs...\n";

    //Build the URLs by traversing the tree
    if (isset($objOpts->folderId)) {
        buildTree($objElements, 0, $objOpts->folderId);
    } else {
        buildTree($objElements, 0);
    }

    echo "Finished!\n";

} catch (Zend_Console_Getopt_Exception $exc) {
    echo $exc->getUsageMessage();
    exit;
} catch (Exception $exc) {
    echo $exc;
}

/**
 * Functions for building the URLs
 */
function buildTree($objElements, $intLevel, $intParentId = null)
{
    global $core, $objRootLevel, $objModelFolders, $objOpts;
    //Walk through all Elements
    foreach ($objElements as $objElement) {
        switch ($objElement->elementType) {
            case 'folder':
                //Recursive Call if folder
                $objNewElements = null;
                switch ($objRootLevel->idRootLevelTypes) {
                    case $core->sysConfig->root_level_types->portals:
                        $objNewElements = $objModelFolders->loadChildNavigation($objElement->id);
                        break;
                    case $core->sysConfig->root_level_types->global:
                        $objNewElements = $objModelFolders->loadGlobalChildNavigation($objElement->id, $objRootLevel->idRootLevelGroups);
                        break;
                }
                buildTree($objNewElements, $intLevel + 1, $objElement->id);
                unset($objNewElements);
                break;
            default:
                //Build URL
                //$strPath = GLOBAL_ROOT_PATH . 'cli/BuildUrl.php';
                //exec('php ' . $strPath . ' --elementid=' . $objElement->id . ' --genericformid=' . $objElement->genericFormId . ' --templateid=' . $objElement->templateId . ' --version=' . $objElement->version . ' --languageid=' . $objOpts->languageId . ' --isstartelement=' . $objElement->isStartElement . ' --parentid=' . $intParentId . ' --rootlevelid=' . $objRootLevel->id . ' --rootleveltypeid=' . $objRootLevel->idRootLevelTypes . ' --rootlevelgroupid=' . $objRootLevel->idRootLevelGroups . ' ' . ((isset($objElement->linkGlobalId)) ? '--linkglobalid=' . $objElement->linkGlobalId : ' ') . ' --level=' . $intLevel);
                buildUrl($objElement, $intParentId, $objRootLevel->id, $intLevel);
                break;
        }
    }
    unset($objElements);
}

function buildUrl($objElement, $intParentId, $intRootLevelId, $intLevel)
{
    global $core, $objOpts, $objRootLevel;

    //Build Form
    $objForm = new GenericForm();
    $objForm->Setup()->setElementId($objElement->id);
    $objForm->Setup()->setFormId($objElement->genericFormId);
    $objForm->Setup()->setTemplateId($objElement->templateId);
    $objForm->Setup()->setFormVersion($objElement->version);
    $objForm->Setup()->setActionType($core->sysConfig->generic->actions->edit);
    $objForm->Setup()->setLanguageId($objOpts->languageId);
    $objForm->Setup()->setFormLanguageId($core->sysConfig->languages->default->id);
    $objForm->Setup()->setIsStartElement($objElement->isStartElement);
    $objForm->Setup()->setParentId($intParentId);
    $objForm->Setup()->setRootLevelId($intRootLevelId);

    //Link Global if product
    if ($objRootLevel->idRootLevelGroups == $core->sysConfig->root_level_groups->product) {
        $objForm->Setup()->setElementLinkId($objElement->linkGlobalId);
    }

    if ($objRootLevel->idRootLevelTypes == $core->sysConfig->root_level_types->portals) {
        $objForm->Setup()->setModelSubPath('cms/models/');
        $objForm->Setup()->setFormTypeId($core->sysConfig->form->types->page);
    }
    if ($objRootLevel->idRootLevelTypes == $core->sysConfig->root_level_types->global) {
        $objForm->Setup()->setModelSubPath('global/models/');
        $objForm->Setup()->setFormTypeId($core->sysConfig->form->types->global);
    }

    // load basic generic form
    $objForm->Setup()->loadGenericForm();

    // load generic form structur
    $objForm->Setup()->loadGenericFormStructure();

    // init data type object
    $objForm->initDataTypeObject();

    // load data
    $objForm->loadFormData();

    //rest url
    if ($objForm->Setup()->getField('url') && $objForm->Setup()->getField('title') && $objForm->Setup()->getField('title')->getValue() != '') {
        $strTitle = $objForm->Setup()->getField('title')->getValue();
        $_POST['title'] = $strTitle;
        $_POST['parentFolderId'] = $objForm->Setup()->getParentId();

        $strUrl = '';
        $blnUnique = true;
        //validate url
        $validator = new Form_Validator_UniqueUrl();
        $validator->setGenericSetup($objForm->Setup());
        if (!$validator->isValid($strUrl)) {
            $arrMessages = $validator->getMessages();
            $strUrl = $arrMessages['suggestion'];
            if (array_key_exists('buildMessage', $arrMessages)) {
                $blnUnique = false;
            }
        }

        if ($blnUnique) {
            $_POST['url_EditableUrl'] = $strUrl;
            if ($objRootLevel->idRootLevelTypes == $core->sysConfig->root_level_types->portals) {
                $objForm->Setup()->getField('url')->save($objElement->id, 'page');
            }
            if ($objRootLevel->idRootLevelTypes == $core->sysConfig->root_level_types->global) {
                if ($objRootLevel->idRootLevelGroups == $core->sysConfig->root_level_groups->product) {
                    $objForm->Setup()->getField('url')->save($objElement->linkGlobalId, 'global');
                } else {
                    $objForm->Setup()->getField('url')->save($objElement->id, 'global');
                }
            }
        }

        //Output
        for ($i = 0; $i < ($intLevel * 2); $i++) {
            echo "-";
        }

        if ($blnUnique) {
            echo $strTitle . "\n";
        } else {
            echo $strTitle . ': No unique URL found!' . "\n";
        }

        unset($validator);
    }
    unset($objElement);
    unset($objForm);
}
