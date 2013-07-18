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
 * GenericDataTypeSubscriber extends GenericDataTypeAbstract
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-05-09: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data.types
 * @subpackage GenericDataTypeSubscriber
 */

require_once(dirname(__FILE__) . '/generic.data.type.abstract.class.php');

class GenericDataTypeSubscriber extends GenericDataTypeAbstract
{

    /**
     * @var Model_Subscribers
     */
    protected $objModelSubscribers;

    /**
     * save
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function save()
    {
        $this->core->logger->debug('massiveart->generic->data->GenericDataTypeSubscriber->save()');
        try {
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
                        'created'          => date('Y-m-d H:i:s')
                    );

                    if (count($this->setup->CoreFields()) > 0) {
                        foreach ($this->setup->CoreFields() as $strField => $obField) {
                            if ($strField == 'password') {
                                if ($obField->getValue() != '') $arrCoreData[$strField] = md5($obField->getValue());
                            } else {
                                $arrCoreData[$strField] = $obField->getValue();
                            }
                        }
                    }

                    /**
                     * add subscriber
                     */
                    $this->setup->setElementId($this->getModelSubscribers()->add($arrCoreData));
                    $this->insertFileData('subscriber', array('Id' => $this->setup->getElementId()));
                    $this->insertMultiFieldData('subscriber', array('Id' => $this->setup->getElementId()));
                    $this->insertInstanceData('subscriber', array('Id' => $this->setup->getElementId()));
                    $this->insertMultiplyRegionData('subscriber', array('Id' => $this->setup->getElementId()));

                    break;
                case $this->core->sysConfig->generic->actions->edit :

                    $arrCoreData = array('idUsers' => $intUserId);

                    if (count($this->setup->CoreFields()) > 0) {
                        foreach ($this->setup->CoreFields() as $strField => $obField) {
                            if ($strField == 'password') {
                                if ($obField->getValue() != '') $arrCoreData[$strField] = md5($obField->getValue());
                            } else {
                                $arrCoreData[$strField] = $obField->getValue();
                            }
                        }
                    }

                    /**
                     * edit subscriber
                     */
                    $this->getModelSubscribers()->update($this->setup->getElementId(), $arrCoreData);
                    $this->updateFileData('subscriber', array('Id' => $this->setup->getElementId()));
                    $this->updateMultiFieldData('subscriber', array('Id' => $this->setup->getElementId()));
                    $this->updateInstanceData('subscriber', array('Id' => $this->setup->getElementId()));
                    $this->updateMultiplyRegionData('subscriber', array('Id' => $this->setup->getElementId()));
                    break;
            }

            return $this->setup->getElementId();
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * load
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function load()
    {
        $this->core->logger->debug('massiveart->generic->data->GenericDataTypeSubscriber->load()');
        try {

            $objSubscribersData = $this->getModelSubscribers()->load($this->setup->getElementId());

            if (count($objSubscribersData) > 0) {
                $objSubscriber = $objSubscribersData->current();

                /**
                 * set some metainformations of current subscriber to get them in the output
                 */
                $this->setup->setMetaInformation($objSubscriber);

                /**
                 * for each core field set field data
                 */
                if (count($this->setup->CoreFields()) > 0) {
                    foreach ($this->setup->CoreFields() as $strField => $objField) {
                        if (isset($objSubscriber->$strField)) {
                            $objField->setValue($objSubscriber->$strField);
                        }
                    }
                }

                $this->loadFileData('subscriber', array('Id' => $this->setup->getElementId()));
                $this->loadMultiFieldData('subscriber', array('Id' => $this->setup->getElementId()));
                $this->loadInstanceData('subscriber', array('Id' => $this->setup->getElementId()));
                $this->loadMultiplyRegionData('subscriber', array('Id' => $this->setup->getElementId()));

                /**
                 * now laod all data from the special fields
                 */
                if (count($this->setup->SpecialFields()) > 0) {
                    foreach ($this->setup->SpecialFields() as $objField) {
                        $objField->setGenericSetup($this->setup);
                        $objField->load($this->setup->getElementId(), 'subscriber');
                    }
                }
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getModelSubscribers
     * @return Model_Subscribers
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelSubscribers()
    {
        if (null === $this->objModelSubscribers) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'contacts/models/Subscribers.php';
            $this->objModelSubscribers = new Model_Subscribers();
        }
        return $this->objModelSubscribers;
    }
}

?>