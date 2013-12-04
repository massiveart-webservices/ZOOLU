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
 * @package    application.zoolu.modules.subscribers.models
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Model_Customers
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-05-04: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

class Model_Customers
{
    /**
     * @var Core
     */
    private $core;

    /**
     * Constructor
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * @var Model_Table_Customers
     */
    private $objCustomerTable;

    /**
     * @var Model_Table_CustomerAddresses
     */
    private $objCustomerAddressTable;

    /**
     * @var Model_Table_CustomerGroups
     */
    private $objCustomerGroupTable;

    /**
     * load
     * @param $intCustomerId integer
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function load($intCustomerId)
    {
        $this->core->logger->debug('contacts->models->Model_Customers->load(' . $intCustomerId . ')');

        $objSelect = $this->getCustomerTable()->select();
        $objSelect->from('customers');
        $objSelect->where('customers.id = ?', $intCustomerId);

        return $this->getCustomerTable()->fetchAll($objSelect);
    }

    /**
     * loadByUsername
     * @param $strUsername string
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadByUsername($strUsername)
    {
        $this->core->logger->debug('contacts->models->Model_Customers->load('.$strUsername.')');

        $objSelect = $this->getCustomerTable()->select();
        $objSelect->from('customers');
        $objSelect->where('customers.username = ?', $strUsername);

        return $this->getCustomerTable()->fetchAll($objSelect);
    }

    /**
     * loadByEmail
     * @param $strEmail string
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadByEmail($strEmail)
    {
        $this->core->logger->debug('contacts->models->Model_Customers->load('.$strEmail.')');

        $objSelect = $this->getCustomerTable()->select();
        $objSelect->from('customers');
        $objSelect->where('customers.email = ?', $strEmail);

        return $this->getCustomerTable()->fetchAll($objSelect);
    }

    public function loadByRegistrationKey($strRegistrationKey)
    {
        $this->core->logger->debug('contacts->models->Model_Customers->load('.$strRegistrationKey.')');

        $objSelect = $this->getCustomerTable()->select();
        $objSelect->from('customers');
        $objSelect->where('customers.registrationKey = ?', $strRegistrationKey);

        return $this->getCustomerTable()->fetchAll($objSelect);
    }

    /**
     * Load by reset password key
     * @param $strKey string
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadByResetPasswordKey($strKey)
    {
        $this->core->logger->debug('contacts->models->Model_Customers->loadByResetPasswordKey('.$strKey.')');

        $objSelect = $this->getCustomerTable()->select();
        $objSelect->from('customers');
        $objSelect->where('customers.resetPasswordKey = ?', $strKey);

        return $this->getCustomerTable()->fetchAll($objSelect);
    }

    /**
     * loadAll
     * @param $strSearchValue string
     * @param $strSortOrder string
     * @param $strOrderColumn string
     * @param $blnReturnSelect boolean
     * @return Zend_Db_Table_Rowset_Abstract|Zend_Db_Table_Select
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadAll($strSearchValue, $strSortOrder, $strOrderColumn, $blnReturnSelect)
    {
        $this->core->logger->debug('contacts->models->Model_Customers->loadAll(' . $strSearchValue . ', ' . $strSortOrder . ', ' . $strOrderColumn . ', ' . $blnReturnSelect . ')');

        $objSelect = $this->getCustomerTable()->select();
        $objSelect->from('customers', array('id', 'username', 'fname', 'sname', 'email', 'type' => new Zend_Db_Expr("'customer'"), 'genericFormId' => new Zend_Db_Expr("'0'"), 'version' => new Zend_Db_Expr("'0'")));

        if ($blnReturnSelect) {
            return $objSelect;
        } else {
            return $this->getCustomerTable()->fetchAll($objSelect);
        }
    }

    /**
     * loadAddresses
     * @param $intCustomerId integer
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadAddresses($intCustomerId)
    {
        $this->core->logger->debug('contacts->models->Model_Customers->load('.$intCustomerId.')');

        $objSelect = $this->getCustomerAddressTable()->select();
        $objSelect->from('customerAddresses', array('street', 'zip', 'city', 'state', 'idCountries', 'idCustomerAddressTypes'));
        $objSelect->where('idCustomers = ?', $intCustomerId);

        return $this->getCustomerAddressTable()->fetchAll($objSelect);
    }

    /**
     * loadGroups
     * @param $intCustomerId integer
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadGroups($intCustomerId)
    {
        $this->core->logger->debug('contacts->models->Model_Customers->load('.$intCustomerId.')');

        $objSelect = $this->getCustomerGroupTable()->select()->setIntegrityCheck(false);
        $objSelect->from('customerGroups', array('idCustomers', 'idGroups'));
        $objSelect->join('groups', 'customerGroups.idGroups = groups.id', array('key'));
        $objSelect->where('idCustomers = ?', $intCustomerId);

        return $this->getCustomerGroupTable()->fetchAll($objSelect);
    }

    /**
     * add
     * @param $arrData array
     * @return mixed
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function add($arrData)
    {
        $arrInsertData = array_merge($arrData, array(
            'created' => date('Y-m-d H:i:s')
        ));
        return $this->getCustomerTable()->insert($arrInsertData);
    }

    /**
     * edit
     * @param $arrData array
     * @param $intCustomerId integer
     * @return integer
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function edit($arrData, $intCustomerId)
    {
        $strWhere = $this->getCustomerTable()->getAdapter()->quoteInto('id = ?', $intCustomerId);
        return $this->getCustomerTable()->update($arrData, $strWhere);
    }

    /**
     * delete
     * @param $intCustomerId integer
     * @return int
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function delete($intCustomerId)
    {
        $this->core->logger->debug('contacts->models->Model_Customers->delete('.$intCustomerId.')');
        $strWhere = $this->getCustomerTable()->getAdapter()->quoteInto('id = ?', $intCustomerId);
        return $this->getCustomerTable()->delete($strWhere);
    }

    /**
     * addAddress
     * @param $arrData array
     * @param $intCustomerId integer
     * @return mixed
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function updateAddresses($arrData, $intCustomerId)
    {
        $strWhere = $this->getCustomerAddressTable()->getAdapter()->quoteInto('idCustomers = ?', $intCustomerId);
        $this->getCustomerAddressTable()->delete($strWhere);

        foreach($arrData as $arrInsertData) {
            $arrInsertData = array_merge($arrInsertData, array(
                'idCustomers' => $intCustomerId
            ));
            $this->getCustomerAddressTable()->insert($arrInsertData);
        }
    }

    /**
     * addAddress
     * @param $arrData array
     * @param $intCustomerId integer
     * @return mixed
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function updateGroups($arrData, $intCustomerId) {
        $this->deleteGroups($intCustomerId);
        foreach ($arrData as $intGroupId) {
            $arrInsertData = array('idCustomers' => $intCustomerId, 'idGroups' => $intGroupId);
            $this->getCustomerGroupTable()->insert($arrInsertData);
        }
    }

    /**
     * addAddress
     * @param $intCustomerId integer
     * @return mixed
     * @author Alexander Schranz <alexander.schranz@massiveart.com>
     * @version 1.0
     */
    public function deleteGroups ($intCustomerId) {
        $strWhere = $this->getCustomerGroupTable()->getAdapter()->quoteInto('idCustomers = ?', $intCustomerId);
        $this->getCustomerGroupTable()->delete($strWhere);
    }

    /**
     * getCustomerTable
     * @return Zend_Db_Table_Abstract
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getCustomerTable()
    {

        if ($this->objCustomerTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'contacts/models/tables/Customers.php';
            $this->objCustomerTable = new Model_Table_Customers();
        }

        return $this->objCustomerTable;
    }

    /**
     * getCustomerAddressTable
     * @return Zend_Db_Table_Abstract
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getCustomerAddressTable()
    {

        if ($this->objCustomerAddressTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'contacts/models/tables/CustomerAddresses.php';
            $this->objCustomerAddressTable = new Model_Table_CustomerAddresses();
        }

        return $this->objCustomerAddressTable;
    }

    /**
     * getCustomerGroupTable
     * @return Zend_Db_Table_Abstract
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getCustomerGroupTable()
    {

        if ($this->objCustomerGroupTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'contacts/models/tables/CustomerGroups.php';
            $this->objCustomerGroupTable = new Model_Table_CustomerGroups();
        }

        return $this->objCustomerGroupTable;
    }
}