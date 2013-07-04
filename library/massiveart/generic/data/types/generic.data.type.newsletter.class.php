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
 * GenericDataTypeNewsletter extends GenericDataTypeAbstract
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-05-03: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data.types
 * @subpackage GenericDataTypeNewsletter
 */

require_once(dirname(__FILE__) . '/generic.data.type.abstract.class.php');

class GenericDataTypeNewsletter extends GenericDataTypeAbstract
{

    /**
     * @var Model_Newsletters
     */
    protected $objModelNewsletters;

    /**
     * save
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function save()
    {
        $this->core->logger->debug('massiveart->generic->data->GenericDataTypeNewsletter->save()');
        try {
            $objAuth = Zend_Auth::getInstance();
            $objAuth->setStorage(new Zend_Auth_Storage_Session('zoolu'));
            $intUserId = $objAuth->getIdentity()->id;

            $arrCoreData = array(
                'idRootLevels'           => $this->setup->getRootLevelId(),
                'idGenericForms'         => $this->setup->getGenFormId(),
                'idTemplates'            => $this->setup->getTemplateId(),
                'idUsers'                => $intUserId,
                'creator'                => $intUserId,
                'created'                => date('Y-m-d H:i:s')
            );

            /**
             * add|edit|newVersion core and instance data
             */
            switch ($this->setup->getActionType()) {
                case $this->core->sysConfig->generic->actions->add:

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
                     * add newsletter
                     */
                    $this->setup->setElementId($this->getModelNewsletters()->add($this->setup, $arrCoreData));
                    $this->insertFileData('newsletter', array('Id' => $this->setup->getElementId()));
                    $this->insertMultiFieldData('newsletter', array('Id' => $this->setup->getElementId()));
                    $this->insertInstanceData('newsletter', array('Id' => $this->setup->getElementId()));
                    $this->insertMultiplyRegionData('newsletter', array('Id' => $this->setup->getElementId()));

                    break;
                case $this->core->sysConfig->generic->actions->edit:
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
                     * edit newsletter
                     */
                    $this->getModelNewsletters()->update($this->setup, $arrCoreData);
                    $this->updateFileData('newsletter', array('Id' => $this->setup->getElementId()));
                    $this->updateMultiFieldData('newsletter', array('Id' => $this->setup->getElementId()));
                    $this->updateInstanceData('newsletter', array('Id' => $this->setup->getElementId()));
                    $this->updateMultiplyRegionData('newsletter', array('Id' => $this->setup->getElementId()));
                    break;

                case $this->core->sysConfig->generic->actions->change_template :
                    $objNewsletter = $this->objModelNewsletters->load($this->setup->getElementId());

                    if (count($objNewsletter) > 0) {
                        $objNewsletter = $objNewsletter->current();

                        $this->objModelNewsletters->update($this->setup, $arrCoreData);

                        if ($this->blnHasLoadedFileData) {
                            $this->updateFileData('newsletter', array('Id' => $this->setup->getElementId()));
                        } else {
                            $this->insertFileData('newsletter', array('Id' => $this->setup->getElementId()));
                        }

                        if ($this->blnHasLoadedMultiFieldData) {
                            $this->updateMultiFieldData('newsletter', array('Id' => $this->setup->getElementId()));
                        } else {
                            $this->insertMultiFieldData('newsletter', array('Id' => $this->setup->getElementId()));
                        }

                        if ($this->blnHasLoadedInstanceData) {
                            $this->updateInstanceData('newsletter', array('Id' => $this->setup->getElementId()));
                        } else {
                            $this->insertInstanceData('newsletter', array('Id' => $this->setup->getElementId()));
                        }

                        if ($this->blnHasLoadedMultiplyRegionData) {
                            $this->updateMultiplyRegionData('newsletter', array('Id' => $this->setup->getElementId()));
                        } else {
                            $this->insertMultiplyRegionData('newsletter', array('Id' => $this->setup->getElementId()));
                        }
                    }
                    break;

                case $this->core->sysConfig->generic->actions->change_template_id :
                    $this->getModelNewsletters()->update($this->setup, $arrCoreData);
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
        $this->core->logger->debug('massiveart->generic->data->GenericDataTypeNewsletter->load()');
        try {

            $objNewslettersData = $this->getModelNewsletters()->load($this->setup->getElementId());

            if (count($objNewslettersData) > 0) {
                $objNewsletter = $objNewslettersData->current();

                /**
                 * set some metainformations of current newsletter to get them in the output
                 */
                $this->setup->setMetaInformation($objNewsletter);

                /**
                 * for each core field set field data
                 */
                if (count($this->setup->CoreFields()) > 0) {
                    foreach ($this->setup->CoreFields() as $strField => $objField) {
                        if (isset($objNewsletter->$strField)) {
                            $objField->setValue($objNewsletter->$strField);
                        }
                    }
                }

                $this->loadFileData('newsletter', array('Id' => $this->setup->getElementId()));
                $this->loadMultiFieldData('newsletter', array('Id' => $this->setup->getElementId()));
                $this->loadInstanceData('newsletter', array('Id' => $this->setup->getElementId()));
                $this->loadMultiplyRegionData('newsletter', array('Id' => $this->setup->getElementId()));

                /**
                 * now load all data from the special fields
                 */
                if (count($this->setup->SpecialFields()) > 0) {
                    foreach ($this->setup->SpecialFields() as $objField) {
                        $objField->setGenericSetup($this->setup);
                        $objField->load($this->setup->getElementId(), 'newsletter');
                    }
                }
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getModelNewsletters
     * @return Model_Newsletters
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelNewsletters()
    {
        if (null === $this->objModelNewsletters) {
            /**
             * autoload only handles "library" components.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'newsletters/models/Newsletters.php';
            $this->objModelNewsletters = new Model_Newsletters();
        }
        return $this->objModelNewsletters;
    }
}

?>