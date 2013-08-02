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
 * @package    library.massiveart.generic.data
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * GenericData
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-19: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data
 * @subpackage GenericData
 */

class GenericData
{

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var GenericSetup
     */
    protected $setup;

    /**
     * property of the generic setup object
     * @return GenericSetup $setup
     */
    public function Setup()
    {
        return $this->setup;
    }

    /**
     * @var GenericDataTypeAbstract
     */
    protected $objDataType;

    /**
     * @var Model_Templates
     */
    protected $objModelTemplates;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');

        /**
         * new generic setup object
         */
        $this->setup = new GenericSetup();
    }

    /**
     * addFolderStartElement
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addFolderStartElement($strPageTitle, $arrPageAttributes = array())
    {
        $this->core->logger->debug('massiveart->generic->data->GenericData->addFolderStartElement()');
        try {
            /**
             * load the generic structure
             */
            $this->initDataTypeObject();
            $this->setup->loadGenericForm();
            $this->setup->loadGenericFormStructure();

            if ($this->setup->getCoreField('title')) {
                $this->setup->getCoreField('title')->setValue($strPageTitle);
            }

            if (array_key_exists('segmentId', $arrPageAttributes)) {
                $this->setup->setSegmentId($arrPageAttributes['segmentId']);
            }

            $this->setup->setIsStartElement(true);

            $this->setup->removeSpecialField('url');

            $this->objDataType->save();

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * loadData
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadData()
    {
        $this->core->logger->debug('massiveart->generic->data->GenericData->loadData()');
        try {

            /**
             * load the generic data
             */
            $this->initDataTypeObject();
            $this->setup->loadGenericForm();
            $this->setup->loadGenericFormStructure();
            $this->objDataType->load();

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * indexData
     * @param string $strKey
     * @param string $type
     * @param int $languageId
     * @param array $arrParentPageContainer
     * @param array $arrParentFolderIds
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function indexData($strKey, $type, $languageId, $arrParentPageContainer = array(), $arrParentFolderIds = array())
    {
        $this->core->logger->debug('massiveart->generic->data->GenericData->indexData(' . $strKey . ', ' . $type . ', ' . $languageId .')');

        if ($this->objDataType instanceof GenericDataTypeAbstract) {
            if (count($arrParentPageContainer) > 0) {
                foreach ($arrParentPageContainer as $intRootLevelId => $objParentPageContainer) {
                    if ($this->setup->getLanguageFallbackId() > 0 && $this->setup->getLanguageFallbackId() != $this->setup->getLanguageId()) {
                        $this->objDataType->updateIndex($strKey . '_' . $this->setup->getLanguageFallbackId() . '_r' . $intRootLevelId, $type, $languageId, $objParentPageContainer, $arrParentFolderIds); // TODO : check language ???
                    } else {
                        $this->objDataType->updateIndex($strKey . '_' . $this->setup->getLanguageId() . '_r' . $intRootLevelId, $type, $languageId, $objParentPageContainer, $arrParentFolderIds); // TODO : check language ???
                    }
                }
            } else {
                $this->objDataType->updateIndex($strKey . '_' . $this->setup->getLanguageId(), $type, $languageId);
            }
        }
    }

    /**
     * changeTemplate
     * @param int $intNewTemplateId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function changeTemplate($intNewTemplateId)
    {
        $this->core->logger->debug('massiveart->generic->data->GenericData->changeTemplate(' . $intNewTemplateId . ')');
        try {

            $objTemplateData = $this->getModelTemplates()->loadTemplateById($intNewTemplateId);

            if (count($objTemplateData) == 1) {
                $objTemplate = $objTemplateData->current();

                /**
                 * set form id from template
                 */
                $strNewFormId = $objTemplate->genericFormId;
                $intNewFormVersion = $objTemplate->version;
                $intNewFormTypeId = $objTemplate->formTypeId;
            } else {
                throw new Exception('Not able to change template, because there is no new form id!');
            }

            /**
             * check, if the new and the old form type are the same
             */
            if ($intNewFormTypeId != $this->setup->getFormTypeId()) {
                throw new Exception('Not able to change template, because there are different form types! ' . $intNewFormTypeId . '-' . $this->setup->getFormTypeId());
            }

            /**
             * load the "old" generic data
             */
            $this->initDataTypeObject();
            $this->setup->loadGenericForm();
            $this->setup->loadGenericFormStructure();
            $this->objDataType->load();

            /**
             * check, if the new template is based on another form
             */
            if ($strNewFormId != $this->setup->getFormId() || $intNewFormVersion != $this->setup->getFormVersion()) {

                /**
                 * clone the old generic setup object and change some properties
                 */
                $objNewGenericSetup = clone $this->setup;
                $objNewGenericSetup->setFormId($strNewFormId);
                $objNewGenericSetup->setFormVersion($intNewFormVersion);
                $objNewGenericSetup->setFormTypeId($intNewFormTypeId);
                $objNewGenericSetup->setTemplateId($intNewTemplateId);
                $objNewGenericSetup->setActionType($this->core->sysConfig->generic->actions->change_template);
                $objNewGenericSetup->loadGenericForm();
                $objNewGenericSetup->resetGenericStructure();
                $objNewGenericSetup->loadGenericFormStructure();

                /**
                 * get new data object
                 */
                $objNewDataType = GenericSetup::getDataTypeObject($objNewGenericSetup->getFormTypeId());
                $objNewDataType->setGenericSetup($objNewGenericSetup);
                $objNewDataType->load();

                /**
                 * now compare values of the fields
                 */
                $this->compareGenericFieldValues($objNewGenericSetup);

                if ($this->setup->getElementId() > 0) $objNewDataType->save();

                $this->setup = $objNewGenericSetup;
            } else {
                $this->setup->setActionType($this->core->sysConfig->generic->actions->change_template_id);
                $this->setup->setTemplateId($intNewTemplateId);
                $this->setup->resetGenericStructure();
                $this->setup->loadGenericFormStructure();
                $this->objDataType->load();
                if ($this->setup->getElementId() > 0) $this->objDataType->save();
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * compareGenericFieldValues
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function compareGenericFieldValues(GenericSetup $objGenericSetup)
    {
        $this->core->logger->debug('massiveart->generic->data->GenericData->compareGenericFieldValues()');
        try {
            if (count($objGenericSetup->CoreFields()) > 0) {
                /**
                 * for each core field, try to get the "old" value
                 */
                foreach ($objGenericSetup->CoreFields() as $strField => $objField) {
                    if (!is_null($this->setup->getCoreField($strField))) {
                        $objField->setValue($this->setup->getCoreField($strField)->getValue());
                    }
                }
            }

            if (count($objGenericSetup->FileFields()) > 0) {
                /**
                 * for each file field, try to get the "old" value
                 */
                foreach ($objGenericSetup->FileFields() as $strField => $objField) {
                    if (!is_null($this->setup->getFileField($strField))) {
                        $objField->setValue($this->setup->getFileField($strField)->getValue());
                    }
                }
            }

            if (count($objGenericSetup->InstanceFields()) > 0) {
                /**
                 * for each instance field, try to get the "old" values
                 */
                foreach ($objGenericSetup->InstanceFields() as $strField => $objField) {
                    if (!is_null($this->setup->getInstanceField($strField))) {
                        $objField->setValue($this->setup->getInstanceField($strField)->getValue());
                    }
                }
            }

            // TODO : compare special fields

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * initDataTypeObject
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function initDataTypeObject()
    {

        $this->objDataType = GenericSetup::getDataTypeObject($this->setup->getFormTypeId());

        if ($this->objDataType instanceof GenericDataTypeAbstract) {
            $this->objDataType->setGenericSetup($this->setup);
        }
    }

    /**
     * getModelTemplates
     * @author Thomas Schedler <tsh@massiveart.com>
     * @return Model_Templates $this->objModelTemplates
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
}

?>