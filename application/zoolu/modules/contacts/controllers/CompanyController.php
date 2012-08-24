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
 * @package    application.zoolu.modules.core.properties.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Contacts_CompanyController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-01-20: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Contacts_CompanyController extends AuthControllerAction
{

    /**
     * @var GenericForm
     */
    protected $objForm;

    /**
     * @var inter
     */
    protected $intItemLanguageId;

    /**
     * request object instance
     * @var Zend_Controller_Request_Abstract
     */
    protected $objRequest;

    /**
     * @var Model_Companies
     */
    public $objModelCompanies;

    /**
     * @var Model_Units
     */
    public $objModelUnits;

    /**
     * init
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     * @return void
     */
    public function init()
    {
        parent::init();
        if (!Security::get()->isAllowed('contact', Security::PRIVILEGE_VIEW)) {
            $this->_redirect('/zoolu');
        }
        $this->objRequest = $this->getRequest();
    }

    /**
     * The default action
     */
    public function indexAction()
    {
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * listAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function listAction()
    {
        $this->core->logger->debug('contacts->controllers->CompanyController->listAction()');

        $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : 'name');
        $strSortOrder = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : 'asc');
        $strSearchValue = (($this->getRequest()->getParam('search') != '') ? $this->getRequest()->getParam('search') : '');

        $objSelect = $this->getModelCompanies()->getCompaniesTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->getModelCompanies()->getCompaniesTable(), array('id', 'name', 'status' => 'contactStatus.title', 'type' => new Zend_Db_Expr("'company'")));
        $objSelect->joinInner('genericForms', 'genericForms.id = companies.idGenericForms', array('genericForms.genericFormId', 'genericForms.version'));
        $objSelect->joinLeft('users', 'users.id = companies.idUsers', array('CONCAT(`users`.`fname`, \' \', `users`.`sname`) AS editor', 'companies.changed'));
        $objSelect->joinLeft('contactStatus', 'contactStatus.id = companies.status', array());
        $objSelect->where('companies.idRootLevels = ?', $this->getRequest()->getParam('rootLevelId'));
        if ($strSearchValue != '') {
            $objSelect->where('companies.name LIKE ?', '%' . $strSearchValue . '%');
            $objSelect->orWhere('contactStatus.title = ?', $strSearchValue);
        }
        $objSelect->order($strOrderColumn . ' ' . strtoupper($strSortOrder));

        $objAdapter = new Zend_Paginator_Adapter_DbTableSelect($objSelect);
        $objGroupsPaginator = new Zend_Paginator($objAdapter);
        $objGroupsPaginator->setItemCountPerPage((int) $this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
        $objGroupsPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
        $objGroupsPaginator->setView($this->view);

        $this->view->assign('paginator', $objGroupsPaginator);
        $this->view->assign('orderColumn', $strOrderColumn);
        $this->view->assign('sortOrder', $strSortOrder);
        $this->view->assign('searchValue', $strSearchValue);
    }

    /**
     * sendDataAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function sendDataAction()
    {
        $this->core->logger->debug('contacts->controllers->CompanyController->sendDataAction()');
        $this->_helper->viewRenderer->setNoRender();

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            $intRootLevelId = $this->getRequest()->getParam('rootLevelId', null);
            $intCompanyId = $this->getRequest()->getParam('id', null);

            if ($intRootLevelId != null && $intCompanyId != null) {
                $objCompany = $this->getModelCompanies()->loadCompany($intCompanyId);
                if (count($objCompany) > 0) $objCompany = $objCompany->current();

                try {
                    ClientHelper::get('Mails')->sendMail($objCompany, $intRootLevelId);
                } catch (Exception $exc) {
                    $this->core->logger->err($exc);
                }
            }
        }
    }

    /**
     * addformAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function addformAction()
    {
        $this->core->logger->debug('contacts->controllers->CompanyController->addformAction()');

        $this->getForm($this->core->sysConfig->generic->actions->edit);

        /**
         * set action
         */
        $this->objForm->setAction('/zoolu/contacts/company/add');

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
        //$this->setViewMetaInfos();

        $this->view->form = $this->objForm;
        $this->renderScript('form.phtml');
    }

    /**
     * addAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function addAction()
    {
        $this->core->logger->debug('contacts->controllers->CompanyController->addAction()');

        $this->getForm($this->core->sysConfig->generic->actions->add);

        /**
         * set action
         */
        $this->objForm->setAction('/zoolu/contacts/company/add');

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            $arrFormData = $this->getRequest()->getPost();
            $this->objForm->Setup()->setFieldValues($arrFormData);

            /**
             * prepare form (add fields and region to the Zend_Form)
             */
            $this->objForm->prepareForm();

            if ($this->objForm->isValid($arrFormData)) {

                /**
                 * set action
                 */
                $this->objForm->setAction('/zoolu/contacts/company/edit');

                /**
                 * set rootlevelid and parentid for location creation
                 */
                $this->objForm->Setup()->setRootLevelId($this->objForm->getElement('rootLevelId')->getValue());
                //$this->objForm->Setup()->setParentId($this->objForm->getElement('parentId')->getValue());

                $intCompanyId = $this->objForm->saveFormData();
                $this->objForm->getElement('id')->setValue($intCompanyId);

                $this->view->intId = $intCompanyId;
                $this->view->blnShowFormAlert = true;
            }
        } else {
            /**
             * prepare form (add fields and region to the Zend_Form)
             */
            $this->objForm->prepareForm();
        }

        /**
         * get form title
         */
        $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

        /**
         * output of metainformation to hidden div
         */
        //$this->setViewMetaInfos();

        $this->view->form = $this->objForm;
        $this->renderScript('form.phtml');
    }

    /**
     * editformAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function editformAction()
    {
        $this->core->logger->debug('contacts->controllers->CompanyController->editformAction()');

        $this->getForm($this->core->sysConfig->generic->actions->edit);

        /**
         * load generic data
         */
        $this->objForm->loadFormData();

        /**
         * set action
         */
        $this->objForm->setAction('/zoolu/contacts/company/edit');

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
        //$this->setViewMetaInfos();

        $this->view->form = $this->objForm;
        $this->renderScript('form.phtml');
    }

    /**
     * editAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function editAction()
    {
        $this->core->logger->debug('contacts->controllers->CompanyController->editAction()');

        $this->getForm($this->core->sysConfig->generic->actions->edit);

        /**
         * get form title
         */
        $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            $arrFormData = $this->getRequest()->getPost();
            $this->objForm->Setup()->setFieldValues($arrFormData);

            /**
             * set action
             */
            $this->objForm->setAction('/zoolu/contacts/company/edit');

            /**
             * prepare form (add fields and region to the Zend_Form)
             */
            $this->objForm->prepareForm();

            if ($this->objForm->isValid($arrFormData)) {
                $this->objForm->saveFormData();
                $this->view->blnShowFormAlert = true;
            }
        }

        /**
         * output of metainformation to hidden div
         */
        //$this->setViewMetaInfos();

        $this->view->form = $this->objForm;
        $this->renderScript('form.phtml');
    }

    /**
     * deleteAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function deleteAction()
    {
        $this->core->logger->debug('contacts->controllers->CompanyController->deleteAction()');

        $this->getModelCompanies();

        if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
            $this->objModelCompanies->deleteCompany($this->objRequest->getParam("id"));
            $this->view->blnShowFormAlert = true;
        }
        $this->renderScript('form.phtml');
    }

    /**
     * listdeleteAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function listdeleteAction()
    {
        $this->core->logger->debug('contacts->controllers->CompanyController->listdeleteAction()');

        try {
            if ($this->objRequest->isPost() && $this->objRequest->isXmlHttpRequest()) {
                $strTmpUserIds = trim($this->objRequest->getParam('values'), '[]');
                $arrCompaniesIds = array();
                $arrCompaniesIds = explode('][', $strTmpUserIds);

                if (count($arrCompaniesIds) > 1) {
                    $this->getModelCompanies()->deleteCompanies($arrCompaniesIds);
                } else {
                    $this->getModelCompanies()->deleteCompany($arrCompaniesIds[0]);
                }

            }
            $this->_forward('list', 'company', 'contacts');

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getForm
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    private function getForm($intActionType = null)
    {
        $this->core->logger->debug('contacts->controllers->CompanyController->getForm(' . $intActionType . ')');

        try {
            $strFormId = $this->objRequest->getParam("formId", $this->core->sysConfig->form->ids->companies->default);
            $intElementId = ($this->objRequest->getParam("id") != '') ? $this->objRequest->getParam("id") : null;

            /**
             * if there is no formId
             */
            if ($strFormId == '') {
                throw new Exception('Not able to create a form, because there is no form id!');
            }

            $objFormHandler = FormHandler::getInstance();
            $objFormHandler->setFormId($strFormId);
            $objFormHandler->setActionType($intActionType);
            $objFormHandler->setLanguageId($this->getItemLanguageId($intActionType));
            $objFormHandler->setFormLanguageId($this->core->intZooluLanguageId);
            $objFormHandler->setElementId($intElementId);

            $this->objForm = $objFormHandler->getGenericForm();

            /**
             * add location & unit specific hidden fields
             */
            $this->objForm->addElement('hidden', 'rootLevelId', array('value' => $this->objRequest->getParam("rootLevelId"), 'decorators' => array('Hidden')));
            //$this->objForm->addElement('hidden', 'parentId', array('value' => $this->objRequest->getParam("parentId"), 'decorators' => array('Hidden')));

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
     * setViewMetaInfos
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    private function setViewMetaInfos()
    {
        if (is_object($this->objForm) && $this->objForm instanceof GenericForm) {
            $arrSecurityCheck = array();
            if (!Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_VIEW, false, false)) {
                $arrSecurityCheck = array(
                    'ResourceKey'           => Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId() . '_%d',
                    'Privilege'             => Security::PRIVILEGE_VIEW,
                    'CheckForAllLanguages'  => false,
                    'IfResourceNotExists'   => false
                );
            }

            $blnGeneralDeleteAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_DELETE, false, false);
            $blnGeneralUpdateAuthorization = Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId(), Security::PRIVILEGE_UPDATE, false, false);

            $this->view->authorizedDelete = ($this->objForm->Setup()->getIsStartElement(false) == true || $this->objForm->Setup()->getActionType() == $this->core->sysConfig->generic->actions->add) ? false : (($blnGeneralDeleteAuthorization == true) ? $blnGeneralDeleteAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId() . '_' . $this->objForm->Setup()->getLanguageId(), Security::PRIVILEGE_DELETE, false, false));
            $this->view->authorizedUpdate = ($blnGeneralUpdateAuthorization == true) ? $blnGeneralUpdateAuthorization : Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $this->objForm->Setup()->getRootLevelId() . '_' . $this->objForm->Setup()->getLanguageId(), Security::PRIVILEGE_UPDATE, false, false);
        }
    }

    /**
     * getItemLanguageId
     * @param integer $intActionType
     * @return integer
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getItemLanguageId($intActionType = null)
    {
        if ($this->intItemLanguageId == null) {
            if (!$this->objRequest->getParam("languageId")) {
                $this->intItemLanguageId = $this->objRequest->getParam("rootLevelLanguageId") != '' ? $this->objRequest->getParam("rootLevelLanguageId") : $this->core->intZooluLanguageId;

                $intRootLevelId = $this->objRequest->getParam("rootLevelId");
                $PRIVILEGE = ($intActionType == $this->core->sysConfig->generic->actions->add) ? Security::PRIVILEGE_ADD : Security::PRIVILEGE_UPDATE;

                $arrLanguages = $this->core->config->languages->language->toArray();
                foreach ($arrLanguages as $arrLanguage) {
                    if (Security::get()->isAllowed(Security::RESOURCE_ROOT_LEVEL_PREFIX . $intRootLevelId . '_' . $arrLanguage['id'], $PRIVILEGE, false, false)) {
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
     * getModelCompanies
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelCompanies()
    {
        if (null === $this->objModelCompanies) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Companies.php';
            $this->objModelCompanies = new Model_Companies();
            $this->objModelCompanies->setLanguageId($this->getItemLanguageId());
        }

        return $this->objModelCompanies;
    }
}
