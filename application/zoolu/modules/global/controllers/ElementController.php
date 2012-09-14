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
 * @package    application.zoolu.modules.global.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ElementController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-30: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Global_ElementController extends AuthControllerAction
{

    /**
     * @var GenericForm
     */
    protected $objForm;

    /**
     * @var integer
     */
    protected $intItemLanguageId;

    /**
     * @var string
     */
    protected $strItemLanguageCode;

    /**
     * request object instance
     * @var Zend_Controller_Request_Abstract
     */
    protected $objRequest;

    /**
     * @var Model_Globals
     */
    protected $objModelGlobals;

    /**
     * @var Model_Folders
     */
    protected $objModelFolders;

    /**
     * @var Model_Files
     */
    protected $objModelFiles;

    /**
     * @var Model_Contacts
     */
    protected $objModelContacts;

    /**
     * @var Model_Templates
     */
    protected $objModelTemplates;

    /**
     * @var Model_GenericForms
     */
    protected $objModelGenericForm;

    /**
     * @var Model_RootLevels
     */
    protected $objModelRootLevels;

    /**
     * init
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     * @return void
     */
    public function init()
    {
        parent::init();
        if (!Security::get()->isAllowed('global', Security::PRIVILEGE_VIEW)) {
            $this->_redirect('/zoolu');
        }
        $this->objRequest = $this->getRequest();
    }

    /**
     * The default action
     */
    public function indexAction()
    {
    }

    /**
     * listAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function listAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->listAction()');

        $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : 'title');
        $strSortOrder = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : 'asc');
        $strSearchValue = (($this->getRequest()->getParam('search') != '') ? $this->getRequest()->getParam('search') : '');

        $objSelect = $this->getModelGlobals()->getGlobalTable()->select();
        $objSelect->setIntegrityCheck(false);
        $objSelect->from($this->getModelGlobals()->getGlobalTable(), array('id', 'title' => 'IF(displayTitle.title <> \'\', displayTitle.title, fallbackTitle.title)'))
            ->joinInner('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version', array())
            ->joinInner('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = globalProperties.idLanguages', array())
            ->joinInner('languages', 'languages.id = globalTitles.idLanguages', array('languageCodes' => 'GROUP_CONCAT(languages.languageCode SEPARATOR \', \')'))
            ->joinLeft(array('displayTitle' => 'globalTitles'), 'displayTitle.globalId = globals.globalId AND displayTitle.version = globals.version AND displayTitle.idLanguages = ' . Zend_Auth::getInstance()->getIdentity()->languageId, array())
            ->joinInner(array('fallbackTitle' => 'globalTitles'), 'fallbackTitle.globalId = globals.globalId AND fallbackTitle.version = globals.version AND fallbackTitle.idLanguages = 0', array())
            ->joinLeft(array('editor' => 'users'), 'editor.id = globalProperties.idUsers', array('editor' => 'CONCAT(`editor`.`fname`, \' \', `editor`.`sname`)', 'globalProperties.changed'))
            ->where('idParent = ?', $this->getRequest()->getParam('rootLevelId'))
            ->where('idParentTypes = ?', $this->core->sysConfig->parent_types->rootlevel)
            ->where('isStartGlobal = 0');
        if ($strSearchValue != '') {
            $objSelect->where('globalTitles.title LIKE ?', '%' . $strSearchValue . '%');
        }
        $objSelect->group('globals.globalId');
        $objSelect->order($strOrderColumn . ' ' . strtoupper($strSortOrder));

        $objAdapter = new Zend_Paginator_Adapter_DbTableSelect($objSelect);
        $objGlobalsPaginator = new Zend_Paginator($objAdapter);
        $objGlobalsPaginator->setItemCountPerPage((int) $this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
        $objGlobalsPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
        $objGlobalsPaginator->setView($this->view);

        $this->view->assign('paginator', $objGlobalsPaginator);
        $this->view->assign('orderColumn', $strOrderColumn);
        $this->view->assign('sortOrder', $strSortOrder);
        $this->view->assign('searchValue', $strSearchValue);

        $this->getModelFolders();
        $objRootLevels = $this->objModelFolders->loadAllRootLevels($this->core->sysConfig->modules->global);

        $this->view->assign('rootLevels', $objRootLevels);
        $this->view->assign('folderFormDefaultId', $this->core->sysConfig->form->ids->folders->default);
        $this->view->assign('elementFormDefaultId', $this->core->sysConfig->global_types->product->default_formId);
        $this->view->assign('elementTemplateDefaultId', $this->core->sysConfig->global_types->product->default_templateId);
        $this->view->assign('elementTypeDefaultId', $this->core->sysConfig->global_types->product->id);
    }

    /**
     * treeAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function treeAction()
    {

        $this->getModelFolders();
        $objRootLevels = $this->objModelFolders->loadAllRootLevels($this->core->sysConfig->modules->global);

        $this->view->assign('rootLevels', $objRootLevels);
        $this->view->assign('folderFormDefaultId', $this->core->sysConfig->form->ids->folders->default);
        $this->view->assign('elementFormDefaultId', $this->core->sysConfig->global_types->product->default_formId);
        $this->view->assign('elementTemplateDefaultId', $this->core->sysConfig->global_types->product->default_templateId);
        $this->view->assign('elementTypeDefaultId', $this->core->sysConfig->global_types->product->id);
    }

    /**
     * getaddformAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getaddformAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->getaddformAction()');

        try {
            $this->getForm($this->core->sysConfig->generic->actions->add);
            $this->addGlobalSpecificFormElements();

            /**
             * set action
             */
            $this->objForm->setAction('/zoolu/global/element/add');

            /**
             * prepare form (add fields and region to the Zend_Form)
             */
            $this->objForm->prepareForm();

            /**
             * get form title
             */
            $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

            /**
             * output of metainformation to hidden div
             */
            $this->setViewMetaInfos();

            $this->view->form = $this->objForm;

            $this->renderScript('element/form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * addAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->addAction()');

        try {
            $this->getForm($this->core->sysConfig->generic->actions->add);
            $this->addGlobalSpecificFormElements();

            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {

                $arrFormData = $this->objRequest->getPost();
                $this->objForm->Setup()->setFieldValues($arrFormData);

                /**
                 * prepare form (add fields and region to the Zend_Form)
                 */
                $this->objForm->prepareForm();

                if ($this->objForm->isValid($arrFormData)) {
                    /**
                     * set action
                     */
                    $this->objForm->setAction('/zoolu/global/element/edit');

                    $intGlobalId = $this->objForm->saveFormData();
                    $this->objForm->Setup()->setElementId($intGlobalId);
                    $this->objForm->Setup()->setActionType($this->core->sysConfig->generic->actions->edit);
                    $this->objForm->getElement('id')->setValue($intGlobalId);
                    $this->objForm->getElement('linkId')->setValue($this->objForm->Setup()->getElementLinkId());

                    $this->view->assign('blnShowFormAlert', true);
                } else {
                    /**
                     * set action
                     */
                    $this->objForm->setAction('/zoolu/global/element/add');
                    $this->view->assign('blnShowFormAlert', false);
                }
            } else {

                /**
                 * prepare form (add fields and region to the Zend_Form)
                 */
                $this->objForm->prepareForm();
            }

            /**
             * update special field values
             */
            $this->objForm->updateSpecificFieldValues();

            /**
             * get form title
             */
            $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

            /**
             * output of metainformation to hidden div
             */
            $this->setViewMetaInfos();

            $this->view->form = $this->objForm;

            $this->renderScript('element/form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * geteditformAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function geteditformAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->geteditformAction()');

        try {
            $this->validateItemProperties();

            $this->getForm($this->core->sysConfig->generic->actions->edit);

            /**
             * load generic data
             */
            $this->objForm->loadFormData();
            $this->addGlobalSpecificFormElements();

            /**
             * set action
             */
            $this->objForm->setAction('/zoolu/global/element/edit');

            /**
             * prepare form (add fields and region to the Zend_Form)
             */
            $this->objForm->prepareForm();

            /**
             * get form title
             */
            $this->view->formtitle = $this->objForm->Setup()->getFormTitle();
            /**
             * Backlink?
             */
            $this->view->backLink = $this->getRequest()->getParam("backLink", false);
            $this->view->currLevel = $this->getRequest()->getParam('currLevel', null);
            $this->view->parentFolderId = $this->getRequest()->getParam('parentFolderId', null);

            /**
             * output of metainformation to hidden div
             */
            $this->setViewMetaInfos();

            $this->view->form = $this->objForm;

            $this->renderScript('element/form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * editAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function editAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->editAction()');

        try {
            $this->getForm($this->core->sysConfig->generic->actions->edit);
            $this->addGlobalSpecificFormElements();

            /**
             * get form title
             */
            $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {

                $arrFormData = $this->objRequest->getPost();
                $this->objForm->Setup()->setFieldValues($arrFormData);

                /**
                 * prepare form (add fields and region to the Zend_Form)
                 */
                $this->objForm->prepareForm();


                if ($this->objForm->isValid($arrFormData)) {
                    $this->objForm->saveFormData();
                    $this->view->assign('blnShowFormAlert', true);
                } else {
                    $this->view->assign('blnShowFormAlert', false);
                }
            } else {
                /**
                 * prepare form (add fields and region to the Zend_Form)
                 */
                $this->objForm->prepareForm();
            }

            /**
             * update special field values
             */
            $this->objForm->updateSpecificFieldValues();

            /**
             * set action
             */
            $this->objForm->setAction('/zoolu/global/element/edit');


            /**
             * output of metainformation to hidden div
             */
            $this->setViewMetaInfos();

            $this->view->form = $this->objForm;

            $this->renderScript('element/form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * copylanguageAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function copylanguageAction()
    {
        $this->renderScript('element/form.phtml');
    }

    /**
     * getpropertiescountAction
     *
     * For checking if there is already an Entry in a specified language
     *
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getpropertiescountAction()
    {
        $this->_helper->viewRenderer->setNoRender();

        $intElementId = $this->getRequest()->getParam('elementId');
        $intLanguageId = $this->getRequest()->getParam('languageId');

        $this->getResponse()->setHeader('Content-Type', 'text/html')->setBody(count($this->getModelGlobals()->loadProperties($intElementId, $intLanguageId)));
    }

    /**
     * setViewMetaInfos
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    private function setViewMetaInfos()
    {
        if (is_object($this->objForm) && $this->objForm instanceof GenericForm) {
            $this->view->version = $this->objForm->Setup()->getElementVersion();
            $this->view->publisher = $this->objForm->Setup()->getPublisherName();
            $this->view->showinnavigation = $this->objForm->Setup()->getShowInNavigation();
            $this->view->languagefallback = $this->objForm->Setup()->getLanguageFallbackId();
            $this->view->changeUser = $this->objForm->Setup()->getChangeUserName();
            $this->view->publishDate = $this->objForm->Setup()->getPublishDate('d. M. Y, H:i');
            $this->view->changeDate = $this->objForm->Setup()->getChangeDate('d. M. Y, H:i');
            $this->view->statusOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, (SELECT statusTitles.title AS DISPLAY FROM statusTitles WHERE statusTitles.idStatus = status.id AND statusTitles.idLanguages = ' . $this->objForm->Setup()->getFormLanguageId() . ') AS DISPLAY FROM status', $this->objForm->Setup()->getStatusId());
            $this->view->creatorOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, CONCAT(fname, \' \', sname) AS DISPLAY FROM users', $this->objForm->Setup()->getCreatorId());

            $this->core->logger->debug('getCreatorId: ' . $this->objForm->Setup()->getCreatorId());

            if ($this->objForm->Setup()->getIsStartElement(false) == true) {
                $this->view->typeOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, (SELECT globalTypeTitles.title AS DISPLAY FROM globalTypeTitles WHERE globalTypeTitles.idGlobalTypes = globalTypes.id AND globalTypeTitles.idLanguages = ' . $this->objForm->Setup()->getFormLanguageId() . ') AS DISPLAY FROM globalTypes WHERE startelement = 1 AND idRootLevelGroups = ' . $this->objForm->Setup()->getRootLevelGroupId() . ' ORDER BY DISPLAY', $this->objForm->Setup()->getElementTypeId());
            } else {
                $this->view->typeOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, (SELECT globalTypeTitles.title AS DISPLAY FROM globalTypeTitles WHERE globalTypeTitles.idGlobalTypes = globalTypes.id AND globalTypeTitles.idLanguages = ' . $this->objForm->Setup()->getFormLanguageId() . ') AS DISPLAY FROM globalTypes WHERE element = 1 AND idRootLevelGroups = ' . $this->objForm->Setup()->getRootLevelGroupId() . ' ORDER BY DISPLAY', $this->objForm->Setup()->getElementTypeId());
            }

            $this->view->arrPublishDate = DateTimeHelper::getDateTimeArray($this->objForm->Setup()->getPublishDate());
            $this->view->monthOptions = DateTimeHelper::getOptionsMonth(false, $this->objForm->Setup()->getPublishDate('n'));

            $this->view->blnIsStartGlobal = $this->objForm->Setup()->getIsStartElement(false);

            if ($this->objForm->Setup()->getField('url')) {
                $this->view->globalurl = $this->objForm->Setup()->getField('url')->getValue();

                //add shop preview url
                if ($this->objForm->Setup()->getRootLevelGroupId() == $this->core->sysConfig->root_level_groups->product && isset($this->core->config->shop->root_level_ids)) {
                    $strBaseUrl = $this->getModelFolders()->getRootLevelMainUrl($this->core->config->shop->root_level_ids->id->toArray(), null, true);
                    if ($strBaseUrl != '') {
                        if (substr_count($strBaseUrl, '.') <= 1) {
                            if (strpos($strBaseUrl, 'https://') !== false) {
                                $strBaseUrl = str_replace('https://', 'https://www.', $strBaseUrl);
                            } else {
                                $strBaseUrl = str_replace('http://', 'http://www.', $strBaseUrl);
                            }
                        }
                        $this->view->addonurls = array(
                            array(
                                'url'   => $strBaseUrl . $this->objForm->Setup()->getField('url')->getValue(),
                                'title' => $this->core->translate->_('Shop')
                            )
                        );
                    }
                    echo '###'.$strBaseUrl;
                }
            }

            $arrSecurityCheck = array();
            if (!Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_VIEW, false, false)) {
                $arrSecurityCheck = array(
                    'ResourceKey'           => Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId() . '_%d',
                    'Privilege'             => Security::PRIVILEGE_VIEW,
                    'CheckForAllLanguages'  => false,
                    'IfResourceNotExists'   => false
                );
            }

            $this->view->languageOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, languageCode AS DISPLAY FROM languages ORDER BY sortOrder, languageCode', $this->objForm->Setup()->getLanguageId(), $arrSecurityCheck);

            $blnGeneralDeleteAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_DELETE, false, false);
            $blnGeneralUpdateAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_UPDATE, false, false);

            $this->view->authorizedDelete = ($this->objForm->Setup()->getIsStartElement(false) == true || $this->objForm->Setup()->getActionType() == $this->core->sysConfig->generic->actions->add) ? false : (($blnGeneralDeleteAuthorization == true) ? $blnGeneralDeleteAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId() . '_' . $this->objForm->Setup()->getLanguageId(), Security::PRIVILEGE_DELETE, false, false));
            $this->view->authorizedUpdate = ($blnGeneralUpdateAuthorization == true) ? $blnGeneralUpdateAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId() . '_' . $this->objForm->Setup()->getLanguageId(), Security::PRIVILEGE_UPDATE, false, false);

            $this->view->languageFallbackOptions = HtmlOutput::getOptionsOfSQL($this->core, 'SELECT id AS VALUE, languageCode AS DISPLAY FROM languages WHERE isFallback = 1 AND id != ' . $this->objForm->Setup()->getLanguageId() . ' ORDER BY sortOrder, languageCode', $this->objForm->Setup()->getLanguageFallbackId());
        }
    }

    /**
     * getfilesAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getfilesAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->getfilesAction()');

        try {
            $strFileIds = $this->objRequest->getParam('fileIds');
            $strFieldName = $this->objRequest->getParam('fileFieldId');
            $strViewType = $this->objRequest->getParam('viewtype');

            /**
             * get files
             */
            $this->getModelFiles();
            $objFiles = $this->objModelFiles->loadFilesById($strFileIds);

            $this->view->assign('objFiles', $objFiles);
            $this->view->assign('fieldname', $strFieldName);
            $this->view->assign('viewtype', $strViewType);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * getfilteredglobalsAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getfilteredglobalsAction()
    {
        $this->core->logger->debug('cms->controllers->PageController->getfilteredglobalsAction()');
        try {

            $arrTagIds = explode(',', $this->objRequest->getParam('tagIds'));
            $arrFolderIds = explode('][', trim($this->objRequest->getParam('folderIds'), '[]'));
            $intRootLevelId = (int) $this->objRequest->getParam('rootLevelId', -1);
            $arrCurrFileIds = explode('][', trim($this->objRequest->getParam('fileIds'), '[]'));

            $objRootLevel = $this->getModelRootLevels()->loadRootLevelById($intRootLevelId)->current();

            $this->view->assign('fieldname', $this->objRequest->getParam('fileFieldId'));
            $this->view->assign('viewtype', $this->objRequest->getParam('viewtype'));
            $this->view->assign('isOverlay', (bool) $this->objRequest->getParam('isOverlay', false));
            $this->view->assign('globalIds', $arrCurrFileIds);

            if ($intRootLevelId > 0 || $arrFolderIds[0] > 0) {
                /**
                 * get files
                 */
                $objGlobals = $this->getModelGlobals()->loadGlobalsByFilter($arrFolderIds, $arrTagIds, $objRootLevel->idRootLevelGroups);
                $this->view->assign('globals', $objGlobals);
                $this->view->assign('rootLevelGroupId', $objRootLevel->idRootLevelGroups);
                $this->renderScript('overlay/listglobal.phtml');
            } else {
                $this->_helper->viewRenderer->setNoRender();
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * getcontactsAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getcontactsAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->getcontactsAction()');

        try {
            $strContactIds = $this->objRequest->getParam('contactIds');
            $strFieldName = $this->objRequest->getParam('fieldId');

            /**
             * get files
             */
            $this->getModelContacts();
            $objContacts = $this->objModelContacts->loadContactsById($strContactIds);

            $this->view->assign('elements', $objContacts);
            $this->view->assign('fieldname', $strFieldName);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * deleteAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function deleteAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->deleteAction()');

        try {
            $this->getModelGlobals();

            $blnGeneralDeleteAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objRequest->getParam("rootLevelId"), Security::PRIVILEGE_DELETE, false, false);
            $blnDeleteAuthorization = ($blnGeneralDeleteAuthorization == true) ? $blnGeneralDeleteAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objRequest->getParam("rootLevelId") . '_' . $this->objRequest->getParam("languageId"), Security::PRIVILEGE_DELETE, false, false);

            if ($blnDeleteAuthorization == true) {
                if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                    if (intval($this->objRequest->getParam('linkId', -1)) > 0) {
                        $this->objModelGlobals->delete($this->objRequest->getParam("linkId"));
                    } else {
                        $this->objModelGlobals->delete($this->objRequest->getParam("id"));
                    }

                    $this->view->blnShowFormAlert = true;
                }
            }

            $this->renderScript('element/form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * dashboardAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function dashboardAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->dashboardAction()');
        try {
            $this->getModelFolders();

            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $intRootLevelId = $this->objRequest->getParam('rootLevelId');
                $intLimitNumber = 10;

                /**
                 * check if select item in session
                 */
                if (isset($this->core->objCoreSession->selectItem) && count($this->core->objCoreSession->selectItem) > 0) {
                    $objSelectItem = $this->core->objCoreSession->selectItem;

                    $this->view->assign('objSelectItem', $objSelectItem);
                    $this->view->assign('isSelectItem', true);

                    unset($this->core->objCoreSession->selectItem);
                } else {
                    $objGlobals = $this->objModelFolders->loadLimitedRootLevelChilds($intRootLevelId, $intLimitNumber);

                    $this->view->assign('objGlobals', $objGlobals);
                    $this->view->assign('limit', $intLimitNumber);
                    $this->view->assign('isSelectItem', false);
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * changetemplateAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function changetemplateAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->changetemplateAction()');

        try {
            $objGenericData = new GenericData();

            $objGenericData->Setup()->setFormId($this->objRequest->getParam("formId"));
            $objGenericData->Setup()->setFormVersion($this->objRequest->getParam("formVersion"));
            $objGenericData->Setup()->setFormTypeId($this->objRequest->getParam("formTypeId"));
            $objGenericData->Setup()->setTemplateId($this->objRequest->getParam("templateId"));
            $objGenericData->Setup()->setElementId($this->objRequest->getParam("id"));
            $objGenericData->Setup()->setElementLinkId($this->objRequest->getParam("linkId", -1));
            $objGenericData->Setup()->setElementTypeId($this->objRequest->getParam("elementTypeId"));
            $objGenericData->Setup()->setParentTypeId($this->objRequest->getParam("parentTypeId"));
            $objGenericData->Setup()->setRootLevelId($this->objRequest->getParam("rootLevelId"));
            $objGenericData->Setup()->setRootLevelGroupId($this->objRequest->getParam("rootLevelGroupId"));
            $objGenericData->Setup()->setParentId($this->objRequest->getParam("parentFolderId"));
            $objGenericData->Setup()->setActionType($this->core->sysConfig->generic->actions->edit);
            $objGenericData->Setup()->setLanguageId($this->getItemLanguageId());
            $objGenericData->Setup()->setLanguageCode($this->getItemLanguageCode());
            $objGenericData->Setup()->setFormLanguageId($this->core->intZooluLanguageId);
            $objGenericData->Setup()->setModelSubPath('global/models/');

            /**
             * change Template
             */
            $objGenericData->changeTemplate($this->objRequest->getParam("newTemplateId"));

            $this->objRequest->setParam("formId", $objGenericData->Setup()->getFormId());
            $this->objRequest->setParam("templateId", $objGenericData->Setup()->getTemplateId());
            $this->objRequest->setParam("formVersion", $objGenericData->Setup()->getFormVersion());

            $this->getForm($this->core->sysConfig->generic->actions->edit);

            /**
             * load generic data
             */
            $this->objForm->setGenericSetup($objGenericData->Setup());
            $this->addGlobalSpecificFormElements();

            /**
             * set action
             */
            if (intval($this->objRequest->getParam('id')) > 0) {
                $this->objForm->setAction('/zoolu/global/element/edit');
            } else {
                $this->objForm->setAction('/zoolu/global/element/add');
            }

            /**
             * prepare form (add fields and region to the Zend_Form)
             */
            $this->objForm->prepareForm();

            /**
             * get form title
             */
            $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

            /**
             * output of metainformation to hidden div
             */
            $this->setViewMetaInfos();

            $this->view->form = $this->objForm;

            $this->renderScript('element/form.phtml');

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * changelanguageAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function changelanguageAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->changelanguageAction()');

        try {

            if (intval($this->objRequest->getParam('id')) > 0) {
                $objGlobalData = $this->getModelGlobals()->loadFormAndTemplateById($this->objRequest->getParam('id'));
                if (count($objGlobalData) == 1) {
                    $objGlobal = $objGlobalData->current();
                    if ((int) $objGlobal->idTemplates > 0) $this->objRequest->setParam('templateId', $objGlobal->idTemplates);
                    if ($objGlobal->genericFormId != '') $this->objRequest->setParam('formId', $objGlobal->genericFormId);
                }
                $this->_forward('geteditform');
            } else {
                $this->_forward('getaddform');
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * changetypeAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function changetypeAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->changetypeAction()');

        try {
            //Set backlink
            $this->objRequest->setParam('backLink', $this->objRequest->getParam('backLink', false));
            $strGroupKey = $this->objRequest->getParam('rootLevelGroupKey');
            $strGroupKeyLink = $strGroupKey . '_link';
            $strGroupKeyOverview = $strGroupKey . '_overview';
            if ($this->objRequest->getParam('elementTypeId') != '' && $this->objRequest->getParam('elementTypeId') > 0) {
                switch ($this->objRequest->getParam('elementTypeId')) {
                    case $this->core->sysConfig->global_types->$strGroupKey->id :
                        $this->objRequest->setParam('formId', '');
                        if ($this->objRequest->getParam('isStartGlobal') == 'true' && $this->objRequest->getParam('parentTypeId') == $this->core->sysConfig->parent_types->rootlevel) {
                            $this->objRequest->setParam('templateId', $this->core->sysConfig->global_types->$strGroupKey->default_templateId);
                        } else if ($this->objRequest->getParam('isStartGlobal') == 'true') {
                            $this->objRequest->setParam('templateId', $this->core->sysConfig->global_types->$strGroupKey->default_templateId);
                        } else {
                            $this->objRequest->setParam('templateId', $this->core->sysConfig->global_types->$strGroupKey->default_templateId);
                        }
                        break;
                    case $this->core->sysConfig->global_types->$strGroupKeyOverview->id :
                        $this->objRequest->setParam('formId', '');
                        $this->objRequest->setParam('templateId', $this->core->sysConfig->global_types->$strGroupKeyOverview->default_templateId);
                        break;
                    case $this->core->sysConfig->global_types->$strGroupKeyLink->id :
                        $this->objRequest->setParam('formId', $this->core->sysConfig->global_types->$strGroupKeyLink->default_formId);
                        break;
                }
            }

            $this->getForm($this->core->sysConfig->generic->actions->edit);

            /**
             * load generic data
             */
            $this->objForm->loadFormData();

            /**
             * overwrite now the global type
             */
            $this->objForm->Setup()->setElementTypeId($this->objRequest->getParam('elementTypeId'));
            $this->addGlobalSpecificFormElements();

            /**
             * set action
             */
            if (intval($this->objRequest->getParam('id')) > 0) {
                $this->objForm->setAction('/zoolu/global/element/edit');
            } else {
                $this->objForm->setAction('/zoolu/global/element/add');
            }

            /**
             * prepare form (add fields and region to the Zend_Form)
             */
            $this->objForm->prepareForm();

            /**
             * get form title
             */
            $this->view->formtitle = $this->objForm->Setup()->getFormTitle();
            $this->view->backLink = $this->objRequest->getParam('backLink', false);
            $this->view->parentFolderId = $this->objRequest->getParam('parentFolderId');

            /**
             * output of metainformation to hidden div
             */
            $this->setViewMetaInfos();

            $this->view->form = $this->objForm;

            $this->renderScript('element/form.phtml');

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * changeparentfolderAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function changeparentfolderAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->changeparentfolderAction()');

        $intElementId = $this->objRequest->getParam('elementId');
        $intParentFolderId = $this->objRequest->getParam('parentFolderId');

        if ($intElementId > 0 && $intParentFolderId > 0) {
            $this->getModelGlobals()->changeParentFolderId($intElementId, $intParentFolderId);
        }
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * changeparentrootfolderAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function changeparentrootfolderAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->changeparentrootfolderAction()');

        $intElementId = $this->objRequest->getParam('elementId');
        $intRootFolderId = $this->objRequest->getParam('rootFolderId');

        if ($intElementId > 0 && $intRootFolderId > 0) {
            $this->getModelGlobals()->changeParentRootFolderId($intElementId, $intRootFolderId);
        }
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * getoverlaysearchAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getoverlaysearchAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->getoverlaysearchAction()');

        $this->view->currLevel = $this->objRequest->getParam('currLevel');
        $this->view->overlaytitle = $this->core->translate->_('Search');
    }

    /**
     * overlaysearchAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function overlaysearchAction()
    {
        $this->core->logger->debug('global->controllers->ElementController->getoverlaysearchAction()');

        $strSearchValue = $this->objRequest->getParam('searchValue');

        if ($strSearchValue != '') {
            $this->view->searchResult = $this->getModelGlobals()->search($strSearchValue);
        }

        $this->view->searchValue = $strSearchValue;
        $this->view->currLevel = $this->objRequest->getParam('currLevel');
    }

    /**
     * addelementlinkAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addelementlinkAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);

        $objGlobal = new stdClass();
        $objGlobal->parentId = $this->objRequest->getParam('parentFolderId');
        $objGlobal->rootLevelId = $this->objRequest->getParam('rootLevelId');
        $objGlobal->isStartElement = $this->objRequest->getParam('isStartGlobal');
        $objGlobal->globalId = $this->objRequest->getParam('linkId');

        $this->getModelGlobals()->addLink($objGlobal);
    }

    /**
     * getForm
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    private function getForm($intActionType = null)
    {
        $this->core->logger->debug('global->controllers->ElementController->getForm(' . $intActionType . ')');

        try {

            $strFormId = $this->objRequest->getParam("formId");
            $intTemplateId = $this->objRequest->getParam("templateId");

            /**
             * if there is now formId, try to load form template
             */
            if ($strFormId == '') {

                if ($intTemplateId != '') {
                    /**
                     * get files
                     */
                    $this->getModelTemplates();
                    $objTemplateData = $this->objModelTemplates->loadTemplateById($intTemplateId);

                    if (count($objTemplateData) == 1) {
                        $objTemplate = $objTemplateData->current();

                        /**
                         * set form id from template
                         */
                        $strFormId = $objTemplate->genericFormId;
                    } else {
                        throw new Exception('Not able to create a form, because there is no form id!');
                    }
                } else {
                    throw new Exception('Not able to create a form, because there is no form id!');
                }
            }

            $intFormVersion = ($this->objRequest->getParam("formVersion") != '') ? $this->objRequest->getParam("formVersion") : null;
            $intElementId = ($this->objRequest->getParam("id") != '') ? $this->objRequest->getParam("id") : null;

            $objFormHandler = FormHandler::getInstance();
            $objFormHandler->setFormId($strFormId);
            $objFormHandler->setTemplateId($intTemplateId);
            $objFormHandler->setFormVersion($intFormVersion);
            $objFormHandler->setActionType($intActionType);
            $objFormHandler->setLanguageId($this->getItemLanguageId($intActionType));
            $objFormHandler->setFormLanguageId($this->core->intZooluLanguageId);
            $objFormHandler->setElementId($intElementId);

            $this->objForm = $objFormHandler->getGenericForm();

            /**
             * set element default & specific form values
             */
            $this->objForm->Setup()->setCreatorId((($this->objRequest->getParam("creator") != '') ? $this->objRequest->getParam("creator") : Zend_Auth::getInstance()->getIdentity()->id));
            $this->objForm->Setup()->setStatusId((($this->objRequest->getParam("idStatus") != '') ? $this->objRequest->getParam("idStatus") : $this->core->sysConfig->form->status->default));
            $this->objForm->Setup()->setRootLevelId((($this->objRequest->getParam("rootLevelId") != '') ? $this->objRequest->getParam("rootLevelId") : null));
            $this->objForm->Setup()->setRootLevelGroupId((($this->objRequest->getParam("rootLevelGroupId") != '') ? $this->objRequest->getParam("rootLevelGroupId") : 0));
            $this->objForm->Setup()->setParentId((($this->objRequest->getParam("parentFolderId") != '') ? $this->objRequest->getParam("parentFolderId") : null));
            $this->objForm->Setup()->setIsStartElement((($this->objRequest->getParam("isStartGlobal") != '') ? $this->objRequest->getParam("isStartGlobal") : 0));
            $this->objForm->Setup()->setPublishDate((($this->objRequest->getParam("publishDate") != '') ? $this->objRequest->getParam("publishDate") : date('Y-m-d H:i:s')));
            $this->objForm->Setup()->setShowInNavigation((($this->objRequest->getParam("showInNavigation") != '') ? $this->objRequest->getParam("showInNavigation") : 0));
            $this->objForm->Setup()->setLanguageFallbackId((($this->objRequest->getParam("languageFallback") != '') ? $this->objRequest->getParam("languageFallback") : 0));
            $this->objForm->Setup()->setElementTypeId((($this->objRequest->getParam("elementTypeId") != '') ? $this->objRequest->getParam("elementTypeId") : $this->core->sysConfig->global_types->product->id));
            $this->objForm->Setup()->setParentTypeId((($this->objRequest->getParam("parentTypeId") != '') ? $this->objRequest->getParam("parentTypeId") : (($this->objRequest->getParam("parentFolderId") != '') ? $this->core->sysConfig->parent_types->folder : $this->core->sysConfig->parent_types->rootlevel)));
            $this->objForm->Setup()->setModelSubPath('global/models/');
            $this->objForm->Setup()->setElementLinkId($this->objRequest->getParam("linkId", -1));

            /**
             * add currlevel hidden field
             */
            $this->objForm->addElement('hidden', 'currLevel', array('value' => $this->objRequest->getParam("currLevel"), 'decorators' => array('Hidden'), 'ignore' => true));

            /**
             * add elementTye hidden field (folder, element, ...)
             */
            $this->objForm->addElement('hidden', 'elementType', array('value' => $this->objRequest->getParam("elementType"), 'decorators' => array('Hidden'), 'ignore' => true));

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * addGlobalSpecificFormElements
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function addGlobalSpecificFormElements()
    {
        if (is_object($this->objForm) && $this->objForm instanceof GenericForm) {
            /**
             * add element specific hidden fields
             */
            $this->objForm->addElement('hidden', 'creator', array('value' => $this->objForm->Setup()->getCreatorId(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'idStatus', array('value' => $this->objForm->Setup()->getStatusId(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'rootLevelId', array('value' => $this->objForm->Setup()->getRootLevelId(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'rootLevelTypeId', array('value' => $this->objForm->Setup()->getRootLevelTypeId(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'rootLevelGroupId', array('value' => $this->objForm->Setup()->getRootLevelGroupId(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'parentFolderId', array('value' => $this->objForm->Setup()->getParentId(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'elementTypeId', array('value' => $this->objForm->Setup()->getElementTypeId(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'isStartGlobal', array('value' => $this->objForm->Setup()->getIsStartElement(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'publishDate', array('value' => $this->objForm->Setup()->getPublishDate('Y-m-d H:i:s'), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'showInNavigation', array('value' => $this->objForm->Setup()->getShowInNavigation(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'languageFallback', array('value' => $this->objForm->Setup()->getLanguageFallbackId(), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'parentTypeId', array('value' => $this->objForm->Setup()->getParentTypeId(), 'decorators' => array('Hidden')));

            /**
             * element link Id form the tree view
             */
            $this->objForm->addElement('hidden', 'linkId', array('value' => $this->objForm->Setup()->getElementLinkId(), 'decorators' => array('Hidden')));
        }
    }

    /**
     * validateItemProperties
     * @return void
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function validateItemProperties()
    {
        if ($this->objRequest->getParam('formId', '') == '' && $this->objRequest->getParam('formVersion', '') == '' && (int) $this->objRequest->getParam('id') > 0) {
            $objData = $this->getModelGlobals()->loadProperties($this->objRequest->getParam('id'));
            if (count($objData) > 0) {
                $objGlobalProperties = $objData->current();
                $this->objRequest->setParam('formId', $objGlobalProperties->genericFormId);
                $this->objRequest->setParam('formVersion', $objGlobalProperties->genericFormVersion);
                $this->objRequest->setParam('templateId', $objGlobalProperties->templateId);
            }
        }
    }

    /**
     * getItemLanguageId
     * @param integer $intActionType
     * @return integer
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getItemLanguageId($intActionType = null)
    {
        if ($this->intItemLanguageId == null) {
            if (!$this->objRequest->getParam("languageId")) {
                $this->intItemLanguageId = $this->objRequest->getParam("rootLevelLanguageId") != '' ? $this->objRequest->getParam("rootLevelLanguageId") : Zend_Auth::getInstance()->getIdentity()->contentLanguageId;

                $intRootLevelId = $this->objRequest->getParam("rootLevelId");
                $PRIVILEGE = ($intActionType == $this->core->sysConfig->generic->actions->add) ? Security::PRIVILEGE_ADD : Security::PRIVILEGE_UPDATE;

                $arrLanguages = $this->core->config->languages->language->toArray();
                foreach ($arrLanguages as $arrLanguage) {
                    if (Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $intRootLevelId . '_' . $this->intItemLanguageId, $PRIVILEGE, false, false)) {
                        break;
                    } else if (Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $intRootLevelId . '_' . $arrLanguage['id'], $PRIVILEGE, false, false)) {
                        $this->intItemLanguageId = $arrLanguage['id'];
                        break;
                    }
                }

            } else {
                $this->intItemLanguageId = $this->objRequest->getParam("languageId");
            }
        }

        return $this->intItemLanguageId;
    }

    /**
     * getItemLanguageCode
     * @return string
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getItemLanguageCode()
    {
        if ($this->strItemLanguageCode == null) {
            if (!$this->objRequest->getParam("languageCode")) {
                $arrLanguages = $this->core->config->languages->language->toArray();
                foreach ($arrLanguages as $arrLanguage) {
                    if ($arrLanguage['id'] == $this->getItemLanguageId()) {
                        $this->strItemLanguageCode = $arrLanguage['code'];
                        break;
                    }
                }
            } else {
                $this->strItemLanguageCode = $this->objRequest->getParam("languageCode");
            }
        }

        return $this->strItemLanguageCode;
    }

    /**
     * getModelGlobals
     * @return Model_Globals
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelGlobals()
    {
        if (null === $this->objModelGlobals) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/Globals.php';
            $this->objModelGlobals = new Model_Globals();
            $this->objModelGlobals->setLanguageId($this->getItemLanguageId());
        }

        return $this->objModelGlobals;
    }

    /**
     * getModelFolders
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelFolders()
    {
        if (null === $this->objModelFolders) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Folders.php';
            $this->objModelFolders = new Model_Folders();
            $this->objModelFolders->setLanguageId($this->getItemLanguageId());
            $this->objModelFolders->setContentLanguageId(Zend_Auth::getInstance()->getIdentity()->contentLanguageId);
        }

        return $this->objModelFolders;
    }

    /**
     * getModelFiles
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelFiles()
    {
        if (null === $this->objModelFiles) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Files.php';
            $this->objModelFiles = new Model_Files();
            $this->objModelFiles->setLanguageId($this->getItemLanguageId());
        }

        return $this->objModelFiles;
    }

    /**
     * getModelContacts
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelContacts()
    {
        if (null === $this->objModelContacts) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Contacts.php';
            $this->objModelContacts = new Model_Contacts();
            $this->objModelContacts->setLanguageId($this->getItemLanguageId());
        }

        return $this->objModelContacts;
    }

    /**
     * getModelTemplates
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelTemplates()
    {
        if (null === $this->objModelTemplates) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Templates.php';
            $this->objModelTemplates = new Model_Templates();
        }

        return $this->objModelTemplates;
    }

    /**
     * getModelGenericForm
     * @return Model_GenericForms
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelGenericForm()
    {
        if (null === $this->objModelGenericForm) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/GenericForms.php';
            $this->objModelGenericForm = new Model_GenericForms();
            $this->objModelGenericForm->setLanguageId($this->objRequest->getParam("languageId", $this->core->intZooluLanguageId));
        }

        return $this->objModelGenericForm;
    }

    /**
     * getModelRootLevels
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelRootLevels()
    {
        if (null === $this->objModelRootLevels) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/RootLevels.php';
            $this->objModelRootLevels = new Model_RootLevels();
        }

        return $this->objModelRootLevels;
    }
}

?>
