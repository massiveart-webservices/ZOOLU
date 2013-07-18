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
 * @package    library.massiveart.generic.data.types
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * GenericDataTypeCompany extends GenericDataTypeAbstract
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-01-20: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data.types
 * @subpackage GenericDataTypeCompany
 */

require_once(dirname(__FILE__) . '/generic.data.type.abstract.class.php');

class GenericDataTypeCompany extends GenericDataTypeAbstract
{

    /**
     * @var Model_Members
     */
    protected $objModelCompanies;

    /**
     * save
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function save()
    {
        $this->core->logger->debug('massiveart->generic->data->GenericDataTypeCompany->save()');
        try {

            $this->getModelCompanies()->setLanguageId($this->setup->getLanguageId());

            $objAuth = Zend_Auth::getInstance();
            $objAuth->setStorage(new Zend_Auth_Storage_Session('zoolu'));
            $intUserId = $objAuth->getIdentity()->id;

            /**
             * add|edit|newVersion core and instance data
             */
            switch ($this->setup->getActionType()) {
                case $this->core->sysConfig->generic->actions->add:

                    $arrCoreData = array(
                        'idRootLevels'     => $this->setup->getRootLevelId(),
                        'idGenericForms'   => $this->setup->getGenFormId(),
                        'idUsers'          => $intUserId,
                        'creator'          => $intUserId,
                        'created'          => date('Y-m-d H:i:s'),
                        'lastReset'        => date('Y-m-d H:i:s')
                    );

                    if (count($this->setup->CoreFields()) > 0) {
                        foreach ($this->setup->CoreFields() as $strField => $obField) {
                            if ($strField == 'password') {
                                if ($obField->getValue() != '') $arrCoreData[$strField] = Crypt::encrypt($this->core, $this->core->config->crypt->key, $obField->getValue());
                            } else {
                                $arrCoreData[$strField] = $obField->getValue();
                            }
                        }
                    }

                    /**
                     * add contact
                     */
                    $this->setup->setElementId($this->objModelCompanies->addCompany($arrCoreData));
                    break;
                case $this->core->sysConfig->generic->actions->edit :

                    $arrCoreData = array('idUsers' => $intUserId);

                    if (count($this->setup->CoreFields()) > 0) {
                        foreach ($this->setup->CoreFields() as $strField => $obField) {
                            if ($strField == 'password') {
                                if ($obField->getValue() != '') $arrCoreData[$strField] = Crypt::encrypt($this->core, $this->core->config->crypt->key, $obField->getValue());
                            } else {
                                $arrCoreData[$strField] = $obField->getValue();
                            }
                        }
                    }

                    /**
                     * add contact
                     */
                    $this->objModelCompanies->editCompany($this->setup->getElementId(), $arrCoreData);
                    break;
            }

            return $this->setup->getElementId();
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * load
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function load()
    {
        $this->core->logger->debug('massiveart->generic->data->GenericDataTypeCompany->load()');
        try {

            $this->getModelCompanies()->setLanguageId($this->setup->getLanguageId());
            $objCompaniesData = $this->getModelCompanies()->loadCompany($this->setup->getElementId());

            if (count($objCompaniesData) > 0) {
                $objCompanyData = $objCompaniesData->current();

                if (count($this->setup->CoreFields()) > 0) {
                    /**
                     * for each core field set field data
                     */
                    foreach ($this->setup->CoreFields() as $strField => $objField) {
                        if (isset($objCompanyData->$strField)) {
                            $objField->setValue($objCompanyData->$strField);
                        }
                    }
                }
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getModelCompanies
     * @return Model_Companies
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
        }
        return $this->objModelCompanies;
    }
}

?>