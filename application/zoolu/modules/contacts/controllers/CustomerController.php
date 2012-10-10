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
 * Contacts_MemberController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-01-19: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Contacts_CustomerController extends AuthControllerAction
{
    /**
     * @var Zend_Form
     */
    protected $objForm;

    /**
     * @var Model_Customers
     */
    private $objModelCustomers;

    /**
     * @var array
     */
    protected $arrAddresses = array();

    public function init()
    {
        parent::init();
        if (!Security::get()->isAllowed('contact', Security::PRIVILEGE_VIEW)) {
            $this->_redirect('/zoolu');
        }
    }

    public function preDispatch()
    {
        /**
         * set default encoding to view
         */
        $this->view->setEncoding($this->core->sysConfig->encoding->default);

        /**
         * set translate obj
         */
        $this->view->translate = $this->core->translate;
    }

    public function listAction()
    {
        $this->core->logger->debug('contacts->controllers->CustomerController->listAction()');

        $strOrderColumn = (($this->getRequest()->getParam('order') != '') ? $this->getRequest()->getParam('order') : 'sname');
        $strSortOrder = (($this->getRequest()->getParam('sort') != '') ? $this->getRequest()->getParam('sort') : 'asc');
        $strSearchValue = (($this->getRequest()->getParam('search') != '') ? $this->getRequest()->getParam('search') : '');

        $objSelect = $this->getModelCustomers()->loadAll($strSearchValue, $strSortOrder, $strOrderColumn, true);

        $objAdapter = new Zend_Paginator_Adapter_DbTableSelect($objSelect);
        $objPaginator = new Zend_Paginator($objAdapter);
        $objPaginator->setItemCountPerPage((int)$this->getRequest()->getParam('itemsPerPage', $this->core->sysConfig->list->default->itemsPerPage));
        $objPaginator->setCurrentPageNumber($this->getRequest()->getParam('page'));
        $objPaginator->setView($this->view);

        $this->view->assign('paginator', $objPaginator);
        $this->view->assign('orderColumn', $strOrderColumn);
        $this->view->assign('sortOrder', $strSortOrder);
        $this->view->assign('searchValue', $strSearchValue);
    }

    public function addformAction()
    {
        $this->core->logger->debug('contacts->controllers->CustomerController->addformAction()');

        try {
            $this->arrAddresses = array();
            $this->initForm();
            $this->objForm->setAction('/zoolu/contacts/customer/add');

            $this->view->form = $this->objForm;
            $this->view->formTitle = $this->core->translate->_('New_Customer');

            $this->renderScript('form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    public function addAction()
    {
        $this->core->logger->debug('contacts->controllers->CustomerController->addAction()');

        try {
            $blnUsername = $this->checkUsername($this->getRequest()->getParam('username'));
            $blnEmail = $this->checkEmail($this->getRequest()->getParam('email'));

            $this->initForm();
            if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
                $arrFormData = $this->getRequest()->getPost();
                if ($this->objForm->isValid($arrFormData)
                    && $this->getRequest()->getParam('password') == $this->getRequest()->getParam('password_confirm')
                    && $this->getRequest()->getParam('password') != null
                    && $blnUsername
                    && $blnEmail
                ) {
                    //Set Action
                    $this->objForm->setAction('/zoolu/contacts/customer/edit');

                    //Add customer
                    $intCustomerId = $this->getModelCustomers()->add(array(
                            'username' => $arrFormData['username'],
                            'password' => md5($arrFormData['password']),
                            'email' => $arrFormData['email'],
                            'title' => $arrFormData['title'],
                            'fname' => $arrFormData['fname'],
                            'sname' => $arrFormData['sname'],
                            'company' => $arrFormData['company'],
                            'phone' => $arrFormData['phone'],
                            'mobile' => $arrFormData['mobile'],
                            'fax' => $arrFormData['fax'],
                            'idCustomerStatus' => $arrFormData['idCustomerStatus'],
                            'idCustomerSalutations' => $arrFormData['idCustomerSalutations'],
                            'idRootLevels' => 49, //FIXME don't hardcode rootlevel
                            'idUsers' => Zend_Auth::getInstance()->getIdentity()->id,
                            'creator' => Zend_Auth::getInstance()->getIdentity()->id
                        )
                    );

                    //Add customer addresses
                    $arrRegionInstanceIds = explode('][', trim($arrFormData['Region_Addresses_Instances'], '[]'));
                    $arrData = array();
                    foreach ($arrRegionInstanceIds as $intRegionInstanceId) {
                        $arrData[] = array(
                            'street' => $arrFormData['street_' . $intRegionInstanceId],
                            'zip' => $arrFormData['zip_' . $intRegionInstanceId],
                            'city' => $arrFormData['city_' . $intRegionInstanceId],
                            'state' => $arrFormData['state_' . $intRegionInstanceId],
                            'idCountries' => ($arrFormData['country_' . $intRegionInstanceId] != '') ? $arrFormData['country_' . $intRegionInstanceId] : null,
                            'idCustomerAddressTypes' => ($arrFormData['type_' . $intRegionInstanceId] != '') ? $arrFormData['type_' . $intRegionInstanceId] : null,
                        );
                    }
                    $this->getModelCustomers()->updateAddresses($arrData, $intCustomerId);

                    $this->getModelCustomers()->updateGroups($this->getRequest()->getParam('group'), $intCustomerId);

                    $this->_forward('list', 'customer', 'contacts');
                    $this->view->assign('blnShowFormAlert', true);
                } else {
                    if ($this->getRequest()->getParam('password') != $this->getRequest()->getParam('password_confirm')) {
                        $this->objForm->getElement('password_confirm')->addError('Passwords do not match');
                    } elseif ($this->getRequest()->getParam('password') == null) {
                        $this->objForm->getElement('password')->addError('Value is required and can\'t be empty');
                    }

                    if (!$blnUsername) {
                        $this->objForm->getElement('username')->addError('Username is already used');
                    }

                    if (!$blnEmail) {
                        $this->objForm->getElement('email')->addError('Email is already used');
                    }

                    $this->objForm->setAction('/zoolu/contacts/customer/add');
                    $this->view->assign('blnShowFormAlert', false);

                    $this->view->form = $this->objForm;
                    $this->view->formTitle = $this->core->translate->_('New_customer');

                    $this->renderScript('form.phtml');
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    public function editformAction()
    {
        $this->core->logger->debug('contacts->controllers->CustomerController->editformAction()');
        try {
            $objCustomerAddresses = $this->getModelCustomers()->loadAddresses($this->getRequest()->getParam('id'));
            foreach ($objCustomerAddresses as $objCustomerAddress) {
                $this->arrAddresses[] = array(
                    'type' => $objCustomerAddress->idCustomerAddressTypes,
                    'street' => $objCustomerAddress->street,
                    'zip' => $objCustomerAddress->zip,
                    'city' => $objCustomerAddress->city,
                    'state' => $objCustomerAddress->state,
                    'country' => $objCustomerAddress->idCountries
                );
            }

            $this->initForm();
            $this->objForm->setAction('/zoolu/contacts/customer/edit');

            $intCustomerId = $this->getRequest()->getParam('id');
            $objCustomer = $this->getModelCustomers()->load($intCustomerId)->current();

            foreach ($this->objForm->getElements() as $objElement) {
                $name = $objElement->getName();
                if (isset($objCustomer->$name)) {
                    $objElement->setValue($objCustomer->$name);
                }
            }

            $objCustomerGroups = $this->getModelCustomers()->loadGroups($intCustomerId);
            $arrMultiCheckbox = array();
            foreach($objCustomerGroups as $objCustomerGroup) {
                $arrMultiCheckbox[] = $objCustomerGroup->idGroups;
            }
            $this->objForm->getElement('group')->setValue($arrMultiCheckbox);

            $this->view->form = $this->objForm;
            $this->view->formTitle = $this->core->translate->_('Edit_Customer');

            $this->renderScript('form.phtml');
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    public function editAction()
    {
        $this->core->logger->debug('contacts->controllers->CustomerController->editAction()');

        try {
            $intCustomerId = $this->getRequest()->getParam('id');

            $blnUsername = $this->checkUsername($this->getRequest()->getParam('username'), $intCustomerId);
            $blnEmail = $this->checkEmail($this->getRequest()->getParam('email'), $intCustomerId);

            $this->initForm();

            if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
                $arrFormData = $this->getRequest()->getPost();
                if ($this->objForm->isValid($arrFormData)
                    && $this->getRequest()->getParam('password') == $this->getRequest()->getParam('password_confirm')
                    && $blnUsername
                    && $blnEmail
                ) {
                    $this->objForm->setAction('/zoolu/contacts/customer/edit');

                    //Edit customer
                    $arrCustomerData = array(
                        'username' => $arrFormData['username'],
                        'password' => md5($arrFormData['password']),
                        'email' => $arrFormData['email'],
                        'title' => $arrFormData['title'],
                        'fname' => $arrFormData['fname'],
                        'sname' => $arrFormData['sname'],
                        'company' => $arrFormData['company'],
                        'phone' => $arrFormData['phone'],
                        'mobile' => $arrFormData['mobile'],
                        'fax' => $arrFormData['fax'],
                        'idCustomerStatus' => $arrFormData['idCustomerStatus'],
                        'idCustomerSalutations' => $arrFormData['idCustomerSalutations'],
                        'idUsers' => Zend_Auth::getInstance()->getIdentity()->id
                    );

                    //Do not update password if it is empty
                    if($arrFormData['password'] == '') {
                        unset($arrCustomerData['password']);
                    }

                    $this->getModelCustomers()->edit($arrCustomerData, $intCustomerId);

                    //Edit customer addresses
                    $arrRegionInstanceIds = explode('][', trim($arrFormData['Region_Addresses_Instances'], '[]'));
                    $arrData = array();
                    foreach ($arrRegionInstanceIds as $intRegionInstanceId) {
                        $arrData[] = array(
                            'street' => $arrFormData['street_' . $intRegionInstanceId],
                            'zip' => $arrFormData['zip_' . $intRegionInstanceId],
                            'city' => $arrFormData['city_' . $intRegionInstanceId],
                            'state' => $arrFormData['state_' . $intRegionInstanceId],
                            'idCountries' => ($arrFormData['country_' . $intRegionInstanceId] != '') ? $arrFormData['country_' . $intRegionInstanceId] : null,
                            'idCustomerAddressTypes' => ($arrFormData['type_' . $intRegionInstanceId] != '') ? $arrFormData['type_' . $intRegionInstanceId] : null,
                        );
                    }
                    $this->getModelCustomers()->updateAddresses($arrData, $intCustomerId);

                    $this->getModelCustomers()->updateGroups($this->getRequest()->getParam('group'), $intCustomerId);

                    $this->_forward('list', 'customer', 'contacts');
                    $this->view->assign('blnShowFormAlert', true);
                } else {
                    if ($this->getRequest()->getParam('password') != $this->getRequest()->getParam('password_confirm')) {
                        $this->objForm->getElement('password_confirm')->addError('Passwords do not match');
                    }

                    if (!$blnUsername) {
                        $this->objForm->getElement('username')->addError('Username is already used');
                    }

                    if (!$blnEmail) {
                        $this->objForm->getElement('email')->addError('Email is already used');
                    }

                    $this->objForm->setAction('/zoolu/contacts/customer/edit');
                    $this->view->assign('blnShowFormAlert', false);

                    $this->view->form = $this->objForm;
                    $this->view->formTitle = $this->core->translate->_('Edit_Customer');

                    $this->renderScript('form.phtml');
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * checkUsername
     * @param $strUsername string
     * @param null $intCustomerId int
     * @return bool
     */
    private function checkUsername($strUsername, $intCustomerId = null)
    {
        $this->core->logger->debug('contacts->controllers->CustomerController->checkUsername(' . $strUsername . ', ' . $intCustomerId . ')');
        $blnIsValid = true;

        if ($strUsername != '') {
            $objCustomers = $this->getModelCustomers()->loadByUsername($strUsername);

            //If there are no results the username is valid
            if (count($objCustomers) > 0) {
                $blnIsValid = false;

                //Check if the username has changed
                if ($intCustomerId != null) {
                    $objActualCustomer = $this->getModelCustomers()->load($intCustomerId)->current();
                    if ($objCustomers->current()->username == $objActualCustomer->username) {
                        $blnIsValid = true;
                    }
                }
            }
        }

        return $blnIsValid;
    }

    /**
     * checkEmail
     * @param $strUsername string
     * @param null $intCustomerId int
     * @return bool
     */
    private function checkEmail($strEmail, $intCustomerId = null)
    {
        $this->core->logger->debug('contacts->controllers->CustomerController->checkUsername(' . $strEmail . ', ' . $intCustomerId . ')');
        $blnIsValid = true;

        if ($strEmail != '') {
            $objCustomers = $this->getModelCustomers()->loadByEmail($strEmail);

            //If there are no results the username is valid
            if (count($objCustomers) > 0) {
                $blnIsValid = false;

                //Check if the username has changed
                if ($intCustomerId != null) {
                    $objActualCustomer = $this->getModelCustomers()->load($intCustomerId)->current();
                    if ($objCustomers->current()->email == $objActualCustomer->email) {
                        $blnIsValid = true;
                    }
                }
            }
        }

        return $blnIsValid;
    }

    /**
     * deleteAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function deleteAction()
    {
        $this->core->logger->debug('contacts->controllers->CustomerController->deleteAction()');

        try {

            if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
                $this->getModelCustomers()->delete($this->getRequest()->getParam("id"));
            }

            $this->_forward('list', 'customer', 'contacts');
            $this->view->assign('blnShowFormAlert', true);

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * listdelete
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function listdeleteAction()
    {
        $this->core->logger->debug('contacts->controllers->CustomerController->listdeleteAction()');

        try {
            $strTmpIds = trim($this->getRequest()->getParam('values'), '[]');
            $arrIds = explode('][', $strTmpIds);
            foreach ($arrIds as $intCustomerId) {
                $this->getModelCustomers()->delete($intCustomerId);
            }

            $this->_forward('list', 'customer', 'contacts');
            $this->view->assign('blnShowFormAlert', true);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    protected function initForm()
    {
        $this->objForm = new GenericForm();

        $arrSalutationOptions = array();
        $arrSalutationOptions[''] = $this->core->translate->_('Please_choose', false);
        $sqlStmt = $this->core->dbh->query("SELECT `id`, `title` AS title FROM `customerSalutations` ORDER BY id")->fetchAll();
        foreach ($sqlStmt as $arrSql) {
            $arrSalutationOptions[$arrSql['id']] = $arrSql['title'];
        }

        $arrAddressTypeOptions = array();
        $arrAddressTypeOptions[''] = $this->core->translate->_('Please_choose', false);
        $sqlStmt = $this->core->dbh->query("SELECT `id`, `key` FROM `customerAddressTypes` ORDER BY id")->fetchAll();
        foreach ($sqlStmt as $arrSql) {
            $arrAddressTypeOptions[$arrSql['id']] = $this->core->translate->_($arrSql['key'], false);
        }

        $arrCountryOptions = array();
        $arrCountryOptions[''] = $this->core->translate->_('Please_choose', false);
        $sqlStmt = $this->core->dbh->query("SELECT `id`, `name` FROM `countries` ORDER BY name")->fetchAll();
        foreach ($sqlStmt as $arrSql) {
            $arrCountryOptions[$arrSql['id']] = $arrSql['name'];
        }

        $arrStatusOptions = array();
        $arrStatusOptions[''] = $this->core->translate->_('Please_choose', false);
        $sqlStmt = $this->core->dbh->query("SELECT `id`, `title` FROM `customerStatus` ORDER BY title")->fetchAll();
        foreach ($sqlStmt as $arrSql) {
            $arrStatusOptions[$arrSql['id']] = $this->core->translate->_($arrSql['title']);
        }
        $arrGroupOptions = array();
        $sqlStmt = $this->core->dbh->query("SELECT `groups`.`id`, `groups`.`title` FROM groups INNER JOIN groupGroupTypes ON groupGroupTypes.idGroups = groups.id WHERE groupGroupTypes.idGroupTypes = 1")->fetchAll();
        foreach ($sqlStmt as $arrSql) {
            $arrGroupOptions[$arrSql['id']] = $this->core->translate->_($arrSql['title']);
        }


        $this->objForm->addElement('hidden', 'id', array('decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'formType', array('value' => 'customer', 'decorators' => array('Hidden')));

        $this->objForm->addElement('select', 'idCustomerSalutations', array('label' => $this->core->translate->_('salutation', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'select', 'required' => true, 'MultiOptions' => $arrSalutationOptions));
        $this->objForm->addElement('text', 'title', array('label' => $this->core->translate->_('title', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'select'));
        $this->objForm->addElement('text', 'fname', array('label' => $this->core->translate->_('fname', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text keyfield', 'required' => true));
        $this->objForm->addElement('text', 'sname', array('label' => $this->core->translate->_('sname', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text keyfield', 'required' => true));
        $this->objForm->addElement('text', 'username', array('label' => $this->core->translate->_('username', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text', 'required' => true));
        $this->objForm->addElement('text', 'company', array('label' => $this->core->translate->_('company', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text'));
        $this->objForm->addElement('select', 'idCustomerStatus', array('label' => $this->core->translate->_('status', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'select', 'required' => true, 'MultiOptions' => $arrStatusOptions));

        $this->objForm->addDisplayGroup(array('idCustomerSalutations', 'title', 'fname', 'sname', 'username', 'email', 'company', 'idCustomerStatus'), 'main-group');
        $this->objForm->getDisplayGroup('main-group')->setLegend($this->core->translate->_('General_information', false));
        $this->objForm->getDisplayGroup('main-group')->setDecorators(array('FormElements', 'Region'));

        $this->objForm->addElement('password', 'password', array('label' => $this->core->translate->_('password', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text'));
        $this->objForm->addElement('password', 'password_confirm', array('label' => $this->core->translate->_('confirm_password', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text'));

        $this->objForm->addDisplayGroup(array('password', 'password_confirm'), 'password-group');
        $this->objForm->getDisplayGroup('password-group')->setLegend($this->core->translate->_('Password', false));
        $this->objForm->getDisplayGroup('password-group')->setDecorators(array('FormElements', 'Region'));

        $this->objForm->addElement('multiCheckbox', 'group', array('label' => $this->core->translate->_('groups', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'multiCheckbox', 'MultiOptions' => $arrGroupOptions));

        $this->objForm->addDisplayGroup(array('group'), 'group-group');
        $this->objForm->getDisplayGroup('group-group')->setLegend($this->core->translate->_('groups', false));
        $this->objForm->getDisplayGroup('group-group')->setDecorators(array('FormElements', 'Region'));

        $this->objForm->addElement('text', 'phone', array('label' => $this->core->translate->_('phone', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text'));
        $this->objForm->addElement('text', 'mobile', array('label' => $this->core->translate->_('mobile', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text'));
        $this->objForm->addElement('text', 'fax', array('label' => $this->core->translate->_('fax', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text'));
        $this->objForm->addElement('text', 'email', array('label' => $this->core->translate->_('email', false), 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text', 'required' => true, 'validators' => array('EmailAddress')));

        $this->objForm->addDisplayGroup(array('phone', 'mobile', 'fax', 'email'), 'contact-group');
        $this->objForm->getDisplayGroup('contact-group')->setLegend($this->core->translate->_('Contact_details', false));
        $this->objForm->getDisplayGroup('contact-group')->setDecorators(array('FormElements', 'Region'));

        //Multiply Region
        $strRegionInstances = '';
        $intRegionCounter = 0;
        /**
         * create group permisson regions
         */
        if (count($this->arrAddresses) > 0) {
            foreach ($this->arrAddresses as $arrAddress) {
                $intRegionCounter++;
                $this->buildAddressRegion($intRegionCounter, $arrAddressTypeOptions, $arrCountryOptions, $arrAddress);

                $strRegionInstances .= '[' . $intRegionCounter . ']';
            }
        } else {
            $this->buildAddressRegion(1, $arrAddressTypeOptions, $arrCountryOptions, array());
            $strRegionInstances = '[1]';
        }

        $this->buildAddressRegion('REPLACE_n', $arrAddressTypeOptions, $arrCountryOptions);

        $this->objForm->addElement('hidden', 'Region_Addresses_Order', array('decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'Region_Addresses_Instances', array('decorators' => array('Hidden'), 'value' => $strRegionInstances));
    }

    private function buildAddressRegion($mixedRegionCounter, $arrAddressTypeOptions, $arrCountryOptions, $arrValues = array())
    {
        $this->objForm->addElement('select', 'type_' . $mixedRegionCounter, array('label' => $this->core->translate->_('addresstype', false), 'value' => (isset($arrValues['type'])) ? $arrValues['type'] : '', 'decorators' => array('Input'), 'columns' => 6, 'class' => 'select', 'MultiOptions' => $arrAddressTypeOptions));
        $this->objForm->addElement('text', 'street_' . $mixedRegionCounter, array('label' => $this->core->translate->_('street', false), 'value' => (isset($arrValues['street'])) ? $arrValues['street'] : '', 'decorators' => array('Input'), 'columns' => 12, 'class' => 'text'));
        $this->objForm->addElement('text', 'zip_' . $mixedRegionCounter, array('label' => $this->core->translate->_('zip', false), 'value' => (isset($arrValues['zip'])) ? $arrValues['zip'] : '', 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text'));
        $this->objForm->addElement('text', 'city_' . $mixedRegionCounter, array('label' => $this->core->translate->_('city', false), 'value' => (isset($arrValues['city'])) ? $arrValues['city'] : '', 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text'));
        $this->objForm->addElement('text', 'state_' . $mixedRegionCounter, array('label' => $this->core->translate->_('state', false), 'value' => (isset($arrValues['state'])) ? $arrValues['state'] : '', 'decorators' => array('Input'), 'columns' => 6, 'class' => 'text'));
        $this->objForm->addElement('select', 'country_' . $mixedRegionCounter, array('label' => $this->core->translate->_('country', false), 'value' => (isset($arrValues['country'])) ? $arrValues['country'] : '', 'decorators' => array('Input'), 'columns' => 6, 'class' => 'select', 'MultiOptions' => $arrCountryOptions));

        $arrOptions = array(
            'regionId' => 'Addresses',
            'isMultiply' => true,
            'regionTypeId' => 1,
            'columns' => 12,
            'legend' => $this->core->translate->_('Addresses')
        );
        if ($mixedRegionCounter == 'REPLACE_n') {
            //Last, to copy region
            $arrOptions = array_merge($arrOptions, array(
                'regionExt' => 'REPLACE_n',
                'isEmptyWidget' => true,
            ));
        } else {
            //all other regions
            $arrOptions = array_merge($arrOptions, array(
                'regionCounter' => $mixedRegionCounter,
                'regionExt' => $mixedRegionCounter,
            ));
        }

        $this->objForm->addDisplayGroup(array('type_' . $mixedRegionCounter, 'street_' . $mixedRegionCounter, 'zip_' . $mixedRegionCounter, 'city_' . $mixedRegionCounter, 'state_' . $mixedRegionCounter, 'country_' . $mixedRegionCounter), 'Addresses_' . $mixedRegionCounter);
        $this->objForm->getDisplayGroup('Addresses_' . $mixedRegionCounter)->setLegend($this->core->translate->_('Addresses', false));
        $this->objForm->getDisplayGroup('Addresses_' . $mixedRegionCounter)->setDecorators(array('FormElements', 'Region'));
        $this->objForm->getDisplayGroup('Addresses_' . $mixedRegionCounter)->setAttribs($arrOptions);
    }

    /**
     * getModelCustomers
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    protected function getModelCustomers()
    {
        if (null === $this->objModelCustomers) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'contacts/models/Customers.php';
            $this->objModelCustomers = new Model_Customers();
        }

        return $this->objModelCustomers;
    }
}