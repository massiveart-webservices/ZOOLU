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
require_once GLOBAL_ROOT_PATH . 'library/massiveart/generic/forms/validators/UniqueUrl.php';

//
// update before running script
// UPDATE `pages` SET `sortPosition` = 0 WHERE `isStartPage` = 1;
// UPDATE `globals` SET `sortPosition` = 0 WHERE `isStartGlobal` = 1 AND `sortPosition` = 999999;
//

try {
    //Load Options
    $objOpts = new Zend_Console_Getopt(
        array(
             'elementid=i'        => 'Element Id',
             'genericformid=s'    => 'GenericFormId',
             'templateid=i'       => 'Template Id',
             'version=i'          => 'Version',
             'languageid=i'       => 'Language Id',
             'isstartelement=i'   => 'Is StartElement',
             'parentid=i'         => 'Parent Id',
             'rootlevelid=i'      => 'RootLevel Id',
             'rootlevelgroupid=i' => 'RootLevelGroup Id',
             'rootleveltypeid=i'  => 'RootLevelType Id',
             'linkglobalid=s'     => 'Linked Global Id',
             'level=i'            => 'Level'
        )
    );
    $objOpts->parse();

    // simulate user auth
    $obj = new stdClass();
    $obj->id = 3; //user id
    Zend_Auth::getInstance()->getStorage()->write($obj);

    //Start
    //Build Form
    $objForm = new GenericForm();
    $objForm->Setup()->setElementId($objOpts->elementid);
    $objForm->Setup()->setFormId($objOpts->genericformid);
    $objForm->Setup()->setTemplateId($objOpts->templateid);
    $objForm->Setup()->setFormVersion($objOpts->version);
    $objForm->Setup()->setActionType($core->sysConfig->generic->actions->edit);
    $objForm->Setup()->setLanguageId($objOpts->languageid);
    $objForm->Setup()->setFormLanguageId($core->sysConfig->languages->default->id);
    $objForm->Setup()->setIsStartElement($objOpts->isstartelement);
    $objForm->Setup()->setParentId($objOpts->parentid);
    $objForm->Setup()->setRootLevelId($objOpts->rootlevelid);

    //Link Global if product
    if ($objOpts->rootlevelgroupid == $core->sysConfig->root_level_groups->product) {
        $objForm->Setup()->setElementLinkId($objOpts->linkglobalid);
    }

    if ($objOpts->rootleveltypeid == $core->sysConfig->root_level_types->portals) {
        $objForm->Setup()->setModelSubPath('cms/models/');
        $objForm->Setup()->setFormTypeId($core->sysConfig->form->types->page);
    }
    if ($objOpts->rootleveltypeid == $core->sysConfig->root_level_types->global) {
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
            if ($objOpts->rootleveltypeid == $core->sysConfig->root_level_types->portals) {
                $objForm->Setup()->getField('url')->save($objElement->id, 'page');
            }
            if ($objOpts->rootleveltypeid == $core->sysConfig->root_level_types->global) {
                if ($objOpts->rootlevelgroupid == $core->sysConfig->root_level_groups->product) {
                    $objForm->Setup()->getField('url')->save($objOpts->linkglobalid, 'global');
                } else {
                    $objForm->Setup()->getField('url')->save($objOpts->elementid, 'global');
                }
            }
        }

        //Output
        $strOutput = '';
        for ($i = 0; $i < ($objOpts->level * 2); $i++) {
            $strOutput .= "-";
        }

        if ($blnUnique) {
            $strOutput .= $strTitle;
        } else {
            $strOutput .= $strTitle . ': No unique URL found!';
        }

        $this->core->logger->info($strOutput);
        unset($validator);
    }
    unset($objElement);
    unset($objForm);
    //End

} catch (Zend_Console_Getopt_Exception $exc) {
    echo $exc->getUsageMessage();
    exit;
} catch (Exception $exc) {
    echo $exc;
}