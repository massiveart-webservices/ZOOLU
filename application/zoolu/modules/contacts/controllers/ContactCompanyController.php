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
 * Contacts_ContactCompanyController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2013-03-27: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Contacts_ContactCompanyController extends AuthControllerAction
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
     * @var Model_Contacts
     */
    public $objModelContacts;

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

    public function preDispatch()
    {
        // set default encoding to view
        $this->view->setEncoding($this->core->sysConfig->encoding->default);

        // set translate obj
        $this->view->translate = $this->core->translate;
    }

    /**
     * The default action
     */
    public function indexAction()
    {
        $this->_helper->viewRenderer->setNoRender();
    }

    public function listAction()
    {
        $this->core->logger->debug('contacts->controllers->ContactCompanyController->listAction()');

        $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : 'name');
        $strSortOrder = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : 'asc');
        $strSearchValue = (($this->getRequest()->getParam('search') != '') ? $this->getRequest()->getParam('search') : '');

        $objSelect = $this->getModelContacts()->loadContacts($strSearchValue, $strSortOrder, $strOrderColumn, true, $this->core->sysConfig->contact_types->company);

        $objPaginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbTableSelect($objSelect));
        $objPaginator->setItemCountPerPage((int)$this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
        $objPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
        $objPaginator->setView($this->view);

        $this->view->assign('contactFormDefaultId', $this->core->sysConfig->form->ids->contactcompanies->default);
        $this->view->assign('contactTypeId', $this->core->sysConfig->contact_types->company);
        $this->view->assign('contactType', 'contact-company');

        $this->view->assign('paginator', $objPaginator);
        $this->view->assign('orderColumn', $strOrderColumn);
        $this->view->assign('sortOrder', $strSortOrder);
        $this->view->assign('searchValue', $strSearchValue);

        $this->renderScript('contact/list.phtml');
    }

    /**
     * addformAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function addformAction()
    {
        $this->core->logger->debug('contacts->controllers->ContactCompanyController->addformAction()');

        $this->getForm($this->core->sysConfig->generic->actions->edit);

        // set action
        $this->objForm->setAction('/zoolu/contacts/contact-company/add');

        // prepare form (add fields and region to the Zend_Form)
        $this->objForm->prepareForm();

        // get form title
        $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

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
        $this->core->logger->debug('contacts->controllers->ContactCompanyController->addAction()');

        $this->getForm($this->core->sysConfig->generic->actions->add);

        // set action
        $this->objForm->setAction('/zoolu/contacts/contact-company/add');

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            $arrFormData = $this->getRequest()->getPost();
            $this->objForm->Setup()->setFieldValues($arrFormData);

            // prepare form (add fields and region to the Zend_Form)
            $this->objForm->prepareForm();

            if ($this->objForm->isValid($arrFormData)) {

                // set action
                $this->objForm->setAction('/zoolu/contacts/contact-company/edit');

                // set rootlevelid and parentid for contact creation
                $this->objForm->Setup()->setRootLevelId($this->objForm->getElement('rootLevelId')->getValue());
                $this->objForm->Setup()->setParentId($this->objForm->getElement('parentId')->getValue());

                $intContactCompanyId = $this->objForm->saveFormData();
                $this->objForm->getElement('id')->setValue($intContactCompanyId);

                $this->view->blnShowFormAlert = true;
            }
        } else {

            // prepare form (add fields and region to the Zend_Form)
            $this->objForm->prepareForm();
        }

        // get form title
        $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

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
        $this->core->logger->debug('contacts->controllers->ElementController->editformAction()');

        $this->getForm($this->core->sysConfig->generic->actions->edit);

        // load generic data
        $this->objForm->loadFormData();

        //  set action
        $this->objForm->setAction('/zoolu/contacts/contact-company/edit');

        // prepare form (add fields and region to the Zend_Form)
        $this->objForm->prepareForm();

        // get form title
        $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

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
        $this->core->logger->debug('contacts->controllers->ContactCompanyController->editAction()');

        $this->getForm($this->core->sysConfig->generic->actions->edit);

        // get form title
        $this->view->formtitle = $this->objForm->Setup()->getFormTitle();

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            $arrFormData = $this->getRequest()->getPost();
            $this->objForm->Setup()->setFieldValues($arrFormData);

            // set action
            $this->objForm->setAction('/zoolu/contacts/contact-company/edit');

            // prepare form (add fields and region to the Zend_Form)
            $this->objForm->prepareForm();

            if ($this->objForm->isValid($arrFormData)) {
                $this->objForm->saveFormData();
                $this->view->blnShowFormAlert = true;
            }
        }

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
        $this->core->logger->debug('contacts->controllers->ContactCompanyController->deleteAction()');

        try {

            if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
                $this->getModelContacts()->deleteContact($this->getRequest()->getParam('id'));

                $this->_forward('list', 'contact-company', 'contacts');
                $this->view->assign('blnShowFormAlert', true);
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * listdeleteAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function listdeleteAction()
    {
        $this->core->logger->debug('contacts->controllers->ContactCompanyController->listdeleteAction()');

        try {
            if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

                $strTmpIds = trim($this->getRequest()->getParam('values'), '[]');
                $arrIds = explode('][', $strTmpIds);
                foreach ($arrIds as $intContactId) {
                    $this->getModelContacts()->deleteContact($intContactId);
                }

                $this->_forward('list', 'contact-company', 'contacts');
                $this->view->assign('blnShowFormAlert', true);

            }
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
        $this->core->logger->debug('contacts->controllers->ContactCompanyController->getForm(' . $intActionType . ')');

        try {
            $objRequest = $this->getRequest();

            $strFormId = $objRequest->getParam("formId");
            $intElementId = ($objRequest->getParam("id") != '') ? $objRequest->getParam("id") : null;
            $elementTypeId = ($objRequest->getParam("elementTypeId") != '') ? $objRequest->getParam("elementTypeId") : $this->core->sysConfig->contact_types->company;

            // if there is no formId use default contactcompanies form id
            if ($strFormId == '') {
                $strFormId = $this->core->sysConfig->form->ids->contactcompanies->default;
            }

            $objFormHandler = FormHandler::getInstance();
            $objFormHandler->setFormId($strFormId);
            $objFormHandler->setActionType($intActionType);
            $objFormHandler->setLanguageId($this->getItemLanguageId($intActionType));
            $objFormHandler->setFormLanguageId($this->core->intZooluLanguageId);
            $objFormHandler->setElementId($intElementId);

            $this->objForm = $objFormHandler->getGenericForm();

            // set elementTypeId
            if (!empty($elementTypeId)) {
                $this->objForm->Setup()->setElementTypeId($elementTypeId);
            }

            // add contact & unit specific hidden fields
            $this->objForm->addElement('hidden', 'rootLevelId', array('value' => $objRequest->getParam("rootLevelId"), 'decorators' => array('Hidden')));
            $this->objForm->addElement('hidden', 'parentId', array('value' => $objRequest->getParam("parentId"), 'decorators' => array('Hidden')));

            // add currlevel hidden field
            $this->objForm->addElement('hidden', 'currLevel', array('value' => $objRequest->getParam("currLevel"), 'decorators' => array('Hidden'), 'ignore' => true));

            // add elementType hidden field (folder, element, ...)
            $this->objForm->addElement('hidden', 'elementType', array('value' => $this->objRequest->getParam("elementType"), 'decorators' => array('Hidden'), 'ignore' => true));

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
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
     * getModelContacts
     * @author Cornelius Hansjakob <cha@massiveart.com>
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
}

?>