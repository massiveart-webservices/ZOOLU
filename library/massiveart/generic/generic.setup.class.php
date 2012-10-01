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
 * @package    library.massiveart.generic
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * GenericSetup
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-20: Thomas Schedler
 * 1.1, 2009-07-29: Florian Mathis, added fieldtypes to database
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic
 * @subpackage GenericSetup
 */

require_once(dirname(__FILE__) . '/elements/generic.element.tab.class.php');
require_once(dirname(__FILE__) . '/elements/generic.element.region.class.php');
require_once(dirname(__FILE__) . '/elements/generic.element.field.class.php');

class GenericSetup
{

    protected $intGenFormId;
    protected $intActionType;
    protected $strFormId;
    protected $intTemplateId;
    protected $intFormVersion = null;
    protected $strFormTitle;
    protected $intFormTypeId;
    protected $strFormType;
    protected $intFormLanguageId;

    protected $intRegionId;
    protected $strRegionTitle;
    protected $intRegionCols;
    protected $intRegionPosition;
    protected $blnRegionCollapsable = true;

    protected $intLanguageId;
    protected $strLanguageCode;
    protected $intLanguageFallbackId;
    protected $intDestinationId;
    protected $intSegmentId = 0;
    protected $blnHideInSitemap = false;
    protected $blnShowInWebsite = true;
    protected $blnShowInTablet = true;
    protected $blnShowInMobile = true;
    protected $intElementId;
    protected $intElementVersion = null;
    protected $intElementLinkId;

    protected $intParentId;
    protected $intParentTypeId;
    protected $intRootLevelId;
    protected $intRootLevelTypeId;
    protected $intRootLevelGroupId;
    protected $intElementTypeId;
    protected $blnIsStartElement;
    protected $intShowInNavigation = 0;
    protected $intUrlFolder;

    protected $strModelSubPath;

    protected $arrTabs = array();

    /**
     * property of the tabs array
     * @return Array $arrTabs
     */
    public function Tabs()
    {
        return $this->arrTabs;
    }

    protected $arrRegions = array();

    /**
     * property of the regions array
     * @return Array $arrRegions
     */
    public function Regions()
    {
        return $this->arrRegions;
    }

    protected $arrMultiplyRegionIds = array();

    /**
     * property of the multiply region ids array
     * @return Array $arrMultiplyRegionIds
     */
    public function MultiplyRegionIds()
    {
        return $this->arrMultiplyRegionIds;
    }

    protected $arrFieldNames = array();

    /**
     * property of the field name array
     * @return Array $arrFieldNames
     */
    public function FieldNames()
    {
        return $this->arrFieldNames;
    }

    protected $arrCoreFields = array();

    /**
     * property of the core fields array
     * @return Array $arrCoreFields
     */
    public function CoreFields()
    {
        return $this->arrCoreFields;
    }

    protected $arrFileFields = array();

    /**
     * property of the file fields array
     * @return Array $arrFileFields
     */
    public function FileFields()
    {
        return $this->arrFileFields;
    }

    protected $arrFileFilterFields = array();

    /**
     * property of the file filter fields array
     * @return Array $arrFileFilterFields
     */
    public function FileFilterFields()
    {
        return $this->arrFileFilterFields;
    }

    protected $arrMultiFields = array();

    /**
     * property of the multi fields array
     * @return Array $arrMultiFields
     */
    public function MultiFields()
    {
        return $this->arrMultiFields;
    }

    protected $arrInstanceFields = array();

    /**
     * property of the instance fields array
     * @return Array $arrInstanceFields
     */
    public function InstanceFields()
    {
        return $this->arrInstanceFields;
    }

    protected $arrSpecialFields = array();

    /**
     * property of the special fields array
     * @return Array $arrSpecialFields
     */
    public function SpecialFields()
    {
        return $this->arrSpecialFields;
    }

    protected $intCreatorId;
    protected $strPublisherName;
    protected $strChangeUserName;
    protected $strPublishDate;
    protected $strChangeDate;
    protected $objPublishDate;
    protected $objChangeDate;
    protected $intStatusId;

    const IS_CORE_FIELD = 'isCoreField';
    const IS_GENERIC_SAVE_FIELD = 'isGenericSaveField';
    const DEFAULT_SORT_POSITION = 999999;

    /**
     * form types
     */
    const TYPE_FOLDER = 1;
    const TYPE_PAGE = 2;
    const TYPE_CATEGORY = 3;
    const TYPE_UNIT = 4;
    const TYPE_CONTACT = 5;
    const TYPE_GLOBAL = 6;
    const TYPE_LOCATION = 7;
    const TYPE_MEMBER = 8;
    const TYPE_COMPANY = 9;
    const TYPE_NEWSLETTER = 10;
    const TYPE_SUBSCRIBER = 11;

    /**
     * field type container
     */
    const CORE_FIELD = 1;
    const SPECIAL_FIELD = 2;
    const FILE_FIELD = 3;
    const MULTI_FIELD = 4;
    const INSTANCE_FIELD = 5;
    const FILE_FILTER_FIELD = 6;

    /**
     * field types constants
     */
    const FIELD_TYPE_TEMPLATE = 'template';
    const FIELD_TYPE_TEXTEDITOR = 'texteditor';
    const FIELD_TYPE_INTERNALLINK = 'internalLink';
    const FIELD_TYPE_INTERNALLINKS = 'internalLinks';
    const FIELD_TYPE_COLLAPSABLEINTERNALLINKS = 'collapsableInternalLinks';
    const FIELD_TYPE_COLLECTION = 'collection';
    const FIELD_TYPE_URL = 'url';
    const FIELD_TYPE_SITEMAPLINK = 'sitemapLink';

    /*
    * FieldTypeGroups
    */
    const FIELD_TYPE_FILE_ID = 1;
    const FIELD_TYPE_SELECT_ID = 2;
    const FIELD_TYPE_MULTIFIELD_ID = 3;
    const FIELD_TYPE_SPECIALFIELD_ID = 4;
    const FIELD_TYPE_ZF_ID = 5;
    const FIELD_TYPE_FILE_FILTER_ID = 6;

    /*
    * FieldTypes
    */
    const FIELD_TYPE_ID_TAG = 16;
    const FIELD_TYPE_ID_ARTICLES = 34;

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Model_GenericForms
     */
    protected $objModelGenericForm;

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
    }

    /**
     * loadGenericForm
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadGenericForm()
    {
        try {
            $this->getModelGenericForm();

            /**
             * @var Zend_Db_Table_Rowset
             */
            $objFormData = $this->objModelGenericForm->loadForm($this->strFormId, $this->intActionType, $this->intFormVersion);

            if (count($objFormData) == 1) {
                $objForm = $objFormData->current();

                /**
                 * set values of the row
                 */
                $this->setGenFormId($objForm->id);
                $this->setFormTitle($objForm->title);
                $this->setFormVersion($objForm->version);
                $this->setFormTypeId($objForm->idGenericFormTypes);
                $this->setFormType($objForm->typeTitle);

            } else {
                throw new Exception('Not able to load form!');
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * resetGenericStructure
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function resetGenericStructure()
    {
        $this->core->logger->debug('massiveart->generic->GenericSetup->resetGenericStructure()');

        $this->arrTabs = array();
        $this->arrRegions = array();
        $this->arrMultiplyRegionIds = array();
        $this->arrCoreFields = array();
        $this->arrFileFields = array();
        $this->arrInstanceFields = array();
        $this->arrMultiFields = array();
        $this->arrSpecialFields = array();
    }

    /**
     * loadGenericFormStructure
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadGenericFormStructure()
    {
        $this->core->logger->debug('massiveart->generic->GenericSetup->loadGenericFormStructure()');
        try {

            /**
             * load the generic form model
             */
            $this->getModelGenericForm();

            $blnCacheGenForm = ($this->core->sysConfig->cache->generic_form_structure == 'true') ? true : false;

            $arrFrontendOptions = array(
                'caching'                 => $blnCacheGenForm,
                'lifetime'                => null, // cache lifetime (in seconds), if set to null, the cache is valid forever.
                'automatic_serialization' => true
            );

            $arrBackendOptions = array(
                'cache_dir' => GLOBAL_ROOT_PATH . $this->core->sysConfig->path->cache->gen_forms // Directory where to put the cache files
            );

            // getting a Zend_Cache_Core object
            $objCache = Zend_Cache::factory('Core',
                'File',
                $arrFrontendOptions,
                $arrBackendOptions);

            // see if a cache already exists:
            if (!$objFieldsAndRegionsData = $objCache->load('GenForm' . $this->intGenFormId . '_' . $this->intFormLanguageId)) {

                // cache miss; get generic form structure
                $objFieldsAndRegionsData = $this->objModelGenericForm->loadFieldsAndRegionsByFormId($this->intGenFormId);

                $objCache->save($objFieldsAndRegionsData, 'GenForm' . $this->intGenFormId . '_' . $this->intFormLanguageId);
            }

            $arrExcludedRegions = array();
            if ($this->getTemplateId() > 0) {
                $objTemplateExcludedRegionData = $this->getModelTemplates()->loadTemplateExcludedRegions($this->getTemplateId());
                if (count($objTemplateExcludedRegionData) > 0) {
                    foreach ($objTemplateExcludedRegionData as $objTemplateExcludedRegion) {
                        $arrExcludedRegions[] = $objTemplateExcludedRegion->idRegions;
                    }
                }
            }

            $arrExcludedFields = array();
            if ($this->getTemplateId() > 0) {
                $objTemplateExcludedFieldData = $this->getModelTemplates()->loadTemplateExcludedFields($this->getTemplateId());
                if (count($objTemplateExcludedFieldData) > 0) {
                    foreach ($objTemplateExcludedFieldData as $objTemplateExcludedField) {
                        $arrExcludedFields[] = $objTemplateExcludedField->idFields;
                    }
                }
            }

            $arrTemplateRegionProperties = array();
            if ($this->getTemplateId() > 0) {
                $objTemplateRegionPropertiesData = $this->getModelTemplates()->loadTemplateRegionProperties($this->getTemplateId());
                if (count($objTemplateRegionPropertiesData) > 0) {
                    foreach ($objTemplateRegionPropertiesData as $objTemplateRegionProperty) {
                        $arrTemplateRegionProperties[$objTemplateRegionProperty->idRegions] = array(
                            'order'       => $objTemplateRegionProperty->order,
                            'collapsable' => $objTemplateRegionProperty->collapsable,
                            'isCollapsed' => $objTemplateRegionProperty->isCollapsed
                        );
                    }
                }
            }

            /**
             * go through the fields and regions to prepare the generic structure
             */
            foreach ($objFieldsAndRegionsData as $objFieldRegionTagData) {

                if (!array_key_exists($objFieldRegionTagData->tabId, $this->arrTabs)) {
                    $objGenTab = new GenericElementTab();
                    $objGenTab->setTabId($objFieldRegionTagData->tabId);
                    $objGenTab->setTabTitle($objFieldRegionTagData->tabTitle);
                    $objGenTab->setTabOrder($objFieldRegionTagData->tabOrder);
                    $objGenTab->setAction($objFieldRegionTagData->tabAction);
                    $this->arrTabs[$objFieldRegionTagData->tabId] = $objGenTab;
                }

                if (!in_array($objFieldRegionTagData->regionId, $arrExcludedRegions) && $objFieldRegionTagData->regionId != null) {
                    if (!in_array($objFieldRegionTagData->id, $arrExcludedFields) && $objFieldRegionTagData->id != null) {

                        if (!array_key_exists($objFieldRegionTagData->regionId, $this->arrRegions)) {

                            $objGenRegion = new GenericElementRegion();
                            $objGenRegion->setRegionId($objFieldRegionTagData->regionId);
                            $objGenRegion->setRegionTitle($objFieldRegionTagData->regionTitle);
                            $objGenRegion->setRegionCols($objFieldRegionTagData->regionColumns);
                            if (array_key_exists($objFieldRegionTagData->regionId, $arrTemplateRegionProperties)) {
                                if (!is_null($arrTemplateRegionProperties[$objFieldRegionTagData->regionId]['order']) || $arrTemplateRegionProperties[$objFieldRegionTagData->regionId]['order'] != '')
                                    $objGenRegion->setRegionOrder($arrTemplateRegionProperties[$objFieldRegionTagData->regionId]['order']);
                                else
                                    $objGenRegion->setRegionOrder($objFieldRegionTagData->regionOrder);

                                if (!is_null($arrTemplateRegionProperties[$objFieldRegionTagData->regionId]['collapsable']) || $arrTemplateRegionProperties[$objFieldRegionTagData->regionId]['collapsable'] != '')
                                    $objGenRegion->setRegionCollapsable($arrTemplateRegionProperties[$objFieldRegionTagData->regionId]['collapsable']);
                                else
                                    $objGenRegion->setRegionCollapsable($objFieldRegionTagData->collapsable);

                                if (!is_null($arrTemplateRegionProperties[$objFieldRegionTagData->regionId]['isCollapsed']) || $arrTemplateRegionProperties[$objFieldRegionTagData->regionId]['isCollapsed'] != '')
                                    $objGenRegion->setRegionIsCollapsed($arrTemplateRegionProperties[$objFieldRegionTagData->regionId]['isCollapsed']);
                                else
                                    $objGenRegion->setRegionIsCollapsed($objFieldRegionTagData->isCollapsed);
                            } else {
                                $objGenRegion->setRegionOrder($objFieldRegionTagData->regionOrder);
                                $objGenRegion->setRegionCollapsable($objFieldRegionTagData->collapsable);
                                $objGenRegion->setRegionIsCollapsed($objFieldRegionTagData->isCollapsed);
                            }
                            $objGenRegion->setRegionTypeId($objFieldRegionTagData->idRegionTypes);
                            $objGenRegion->setRegionPosition($objFieldRegionTagData->position);
                            $objGenRegion->setRegionIsMultiply($objFieldRegionTagData->isMultiply);
                            $objGenRegion->setRegionMultiplyRegion($objFieldRegionTagData->multiplyRegion);
                            $this->arrRegions[$objFieldRegionTagData->regionId] = $objGenRegion;

                            if ($objGenRegion->getRegionIsMultiply() == true) {
                                $this->arrMultiplyRegionIds[] = $objFieldRegionTagData->regionId;
                            }

                            $this->getTab($objFieldRegionTagData->tabId)->addRegion($objGenRegion);
                        }

                        $objGenField = new GenericElementField();
                        $objGenField->id = $objFieldRegionTagData->id;
                        $objGenField->title = $objFieldRegionTagData->title;
                        $objGenField->name = $objFieldRegionTagData->name;
                        $objGenField->typeId = $objFieldRegionTagData->idFieldTypes;
                        $objGenField->type = $objFieldRegionTagData->type;
                        $objGenField->defaultValue = $objFieldRegionTagData->defaultValue;
                        $objGenField->sqlSelect = $objFieldRegionTagData->sqlSelect;
                        $objGenField->columns = $objFieldRegionTagData->columns;
                        $objGenField->order = $objFieldRegionTagData->order;
                        $objGenField->isCoreField = $objFieldRegionTagData->isCoreField;
                        $objGenField->isKeyField = $objFieldRegionTagData->isKeyField;
                        $objGenField->isSaveField = $objFieldRegionTagData->isSaveField;
                        $objGenField->isRegionTitle = $objFieldRegionTagData->isRegionTitle;
                        $objGenField->isDependentOn = $objFieldRegionTagData->isDependentOn;
                        $objGenField->showDisplayOptions = $objFieldRegionTagData->showDisplayOptions;
                        $objGenField->fieldOptions = $objFieldRegionTagData->options;
                        $objGenField->copyValue = $objFieldRegionTagData->copyValue;
                        $objGenField->decorator = $objFieldRegionTagData->decorator;
                        $objGenField->isMultiply = $objFieldRegionTagData->isMultiply;
                        $objGenField->idSearchFieldTypes = $objFieldRegionTagData->idSearchFieldTypes;
                        $objGenField->idFieldTypeGroup = $objFieldRegionTagData->idFieldTypeGroup;
                        $objGenField->validators = ($objFieldRegionTagData->validators != null) ? json_decode($objFieldRegionTagData->validators) : array();

                        /**
                         * select field container
                         */
                        if ($objGenField->isSaveField == 1) {
                            if ($objGenField->isMultiply == 1) {
                                if ($objGenField->idFieldTypeGroup == GenericSetup::FIELD_TYPE_SPECIALFIELD_ID) {
                                    $this->getRegion($objFieldRegionTagData->regionId)->addSpecialFieldName($objGenField->name);
                                    $this->getRegion($objFieldRegionTagData->regionId)->addFieldName($objGenField->name, self::SPECIAL_FIELD);
                                } else if ($objGenField->idFieldTypeGroup == GenericSetup::FIELD_TYPE_FILE_ID) {
                                    $this->getRegion($objFieldRegionTagData->regionId)->addFileFieldName($objGenField->name);
                                    $this->getRegion($objFieldRegionTagData->regionId)->addFieldName($objGenField->name, self::FILE_FIELD);
                                } else if ($objGenField->idFieldTypeGroup == GenericSetup::FIELD_TYPE_FILE_FILTER_ID) {
                                    $this->getRegion($objFieldRegionTagData->regionId)->addFileFilterFieldName($objGenField->name);
                                    $this->getRegion($objFieldRegionTagData->regionId)->addFieldName($objGenField->name, self::FILE_FILTER_FIELD);
                                } else if ($objGenField->idFieldTypeGroup == GenericSetup::FIELD_TYPE_MULTIFIELD_ID) {
                                    $this->getRegion($objFieldRegionTagData->regionId)->addMultiFieldName($objGenField->name);
                                    $this->getRegion($objFieldRegionTagData->regionId)->addFieldName($objGenField->name, self::MULTI_FIELD);
                                } else {
                                    $this->getRegion($objFieldRegionTagData->regionId)->addInstanceFieldName($objGenField->name);
                                    $this->getRegion($objFieldRegionTagData->regionId)->addFieldName($objGenField->name, self::INSTANCE_FIELD);
                                }
                            } else {
                                if ($objGenField->isCoreField == 1) {
                                    $this->arrCoreFields[$objGenField->name] = $objGenField;
                                    $this->arrFieldNames[$objGenField->name] = self::CORE_FIELD;
                                } else if ($objGenField->idFieldTypeGroup == GenericSetup::FIELD_TYPE_SPECIALFIELD_ID) {
                                    $this->arrSpecialFields[$objGenField->name] = $objGenField;
                                    $this->arrFieldNames[$objGenField->name] = self::SPECIAL_FIELD;
                                } else if ($objGenField->idFieldTypeGroup == GenericSetup::FIELD_TYPE_FILE_ID) {
                                    $this->arrFileFields[$objGenField->name] = $objGenField;
                                    $this->arrFieldNames[$objGenField->name] = self::FILE_FIELD;
                                } else if ($objGenField->idFieldTypeGroup == GenericSetup::FIELD_TYPE_FILE_FILTER_ID) {
                                    $this->arrFileFilterFields[$objGenField->name] = $objGenField;
                                    $this->arrFieldNames[$objGenField->name] = self::FILE_FILTER_FIELD;
                                } else if ($objGenField->idFieldTypeGroup == GenericSetup::FIELD_TYPE_MULTIFIELD_ID) {
                                    $this->arrMultiFields[$objGenField->name] = $objGenField;
                                    $this->arrFieldNames[$objGenField->name] = self::MULTI_FIELD;
                                } else {
                                    $this->arrInstanceFields[$objGenField->name] = $objGenField;
                                    $this->arrFieldNames[$objGenField->name] = self::INSTANCE_FIELD;
                                }
                            }
                        }

                        $this->getRegion($objFieldRegionTagData->regionId)->addField($objGenField);
                    }
                }
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getTab
     * @param integer $intTabId
     * @return GenericElementTab
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getTab($intTabId)
    {
        if (array_key_exists($intTabId, $this->arrTabs)) {
            return $this->arrTabs[$intTabId];
        }
        return null;
    }

    /**
     * getRegion
     * @param integer $intRegionId
     * @return GenericElementRegion
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getRegion($intRegionId)
    {
        if (array_key_exists($intRegionId, $this->arrRegions)) {
            return $this->arrRegions[$intRegionId];
        }
        return null;
    }

    /**
     * getField
     * @param string $strField
     * @return GenericElementField
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getField($strField)
    {
        if (array_key_exists($strField, $this->arrFieldNames)) {
            switch ($this->arrFieldNames[$strField]) {
                case self::CORE_FIELD:
                    return $this->getCoreField($strField);
                    break;
                case self::SPECIAL_FIELD:
                    return $this->getSpecialField($strField);
                    break;
                case self::FILE_FIELD:
                    return $this->getFileField($strField);
                    break;
                case self::FILE_FILTER_FIELD:
                    return $this->getFileFilterField($strField);
                    break;
                case self::MULTI_FIELD:
                    return $this->getMultiField($strField);
                    break;
                case self::INSTANCE_FIELD:
                    return $this->getInstanceField($strField);
                    break;
            }
        }
        return null;
    }

    /**
     * getCoreField
     * @param string $strField
     * @return GenericElementField
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getCoreField($strField)
    {
        if (array_key_exists($strField, $this->arrCoreFields)) {
            return $this->arrCoreFields[$strField];
        }
        return null;
    }

    /**
     * getFileField
     * @param string $strField
     * @return GenericElementField
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getFileField($strField)
    {
        if (array_key_exists($strField, $this->arrFileFields)) {
            return $this->arrFileFields[$strField];
        }
        return null;
    }

    /**
     * getFileFilterField
     * @param string $strField
     * @return GenericElementField
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getFileFilterField($strField)
    {
        if (array_key_exists($strField, $this->arrFileFilterFields)) {
            return $this->arrFileFilterFields[$strField];
        }
        return null;
    }

    /**
     * getMultiField
     * @param string $strField
     * @return GenericElementField
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getMultiField($strField)
    {
        if (array_key_exists($strField, $this->arrMultiFields)) {
            return $this->arrMultiFields[$strField];
        }
        return null;
    }

    /**
     * getInstanceField
     * @param string $strField
     * @return GenericElementField
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getInstanceField($strField)
    {
        if (array_key_exists($strField, $this->arrInstanceFields)) {
            return $this->arrInstanceFields[$strField];
        }
        return null;
    }

    /**
     * getSpecialField
     * @param string $strField
     * @return GenericElementField
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getSpecialField($strField)
    {
        if (array_key_exists($strField, $this->arrSpecialFields)) {
            return $this->arrSpecialFields[$strField];
        }
        return null;
    }

    /**
     * setFieldValues
     * @param array $arrValues
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setFieldValues($arrValues)
    {
        try {

            /**
             * go through the regions
             */
            foreach ($this->arrRegions as $objRegion) {

                if ($objRegion->getRegionIsMultiply() == true) {
                    if (isset($arrValues['Region_' . $objRegion->getRegionId() . '_Instances']) && isset($arrValues['Region_' . $objRegion->getRegionId() . '_Order'])) {
                        /*$strRegionInstanceIds = trim($arrValues['Region_'.$objRegion->getRegionId().'_Instances'], '[]');
                     $arrRegionInstanceIds = array();
                     $arrRegionInstanceIds = split('\]\[', $strRegionInstanceIds);*/

                        parse_str($arrValues['Region_' . $objRegion->getRegionId() . '_Order'], $arrRegionOrder);

                        /**
                         * go through region instances
                         */
                        if (array_key_exists('divRegion_' . $objRegion->getRegionId(), $arrRegionOrder)) {
                            foreach ($arrRegionOrder['divRegion_' . $objRegion->getRegionId()] as $intRegionInstanceId) {
                                if (is_numeric($intRegionInstanceId) && $intRegionInstanceId > 0) {
                                    $objRegion->addRegionInstanceId($intRegionInstanceId);

                                    /**
                                     * go through fields of the region
                                     */
                                    foreach ($objRegion->getFields() as $objField) {
                                        if ((int) $objField->idFieldTypeGroup == GenericSetup::FIELD_TYPE_FILE_FILTER_ID) {
                                            $objField->setInstanceValue($intRegionInstanceId, $this->getFileFilterObject($objField->name . '_' . $intRegionInstanceId, $arrValues));
                                        } else if ((int) $objField->typeId == GenericSetup::FIELD_TYPE_ID_ARTICLES) {
                                            $objField->setInstanceValue($intRegionInstanceId, $this->getArticlesObject($objField->name . '_' . $intRegionInstanceId, $arrValues));
                                        } else if (array_key_exists($objField->name . '_' . $intRegionInstanceId, $arrValues)) {
                                            $objField->setInstanceValue($intRegionInstanceId, $arrValues[$objField->name . '_' . $intRegionInstanceId]);
                                        }

                                        /**
                                         * is ther a display option for this field
                                         */
                                        if (array_key_exists($objField->name . '_' . $intRegionInstanceId . '_display_option', $arrValues)) {
                                            $objField->setInstanceProperty($intRegionInstanceId, 'display_option', $arrValues[$objField->name . '_' . $intRegionInstanceId . '_display_option']);
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    /**
                     * go through fields of the region
                     */
                    foreach ($objRegion->getFields() as $objField) {
                        if ($objField->idFieldTypeGroup == GenericSetup::FIELD_TYPE_FILE_FILTER_ID) {
                            $objField->setValue($this->getFileFilterObject($objField->name, $arrValues));
                        } else if (array_key_exists($objField->name, $arrValues)) {
                            $objField->setValue($arrValues[$objField->name]);
                        }

                        /**
                         * is ther a display option for this field
                         */
                        if (array_key_exists($objField->name . '_display_option', $arrValues)) {
                            $objField->display_option = $arrValues[$objField->name . '_display_option'];
                        }
                    }
                }
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getFileFilterObject
     * @param string $strFieldName
     * @param array $arrValues
     * @return $objFilters
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getFileFilterObject($strFieldName, &$arrValues)
    {
        $objFilters = new stdClass();
        $objFilters->filters = array();

        if (array_key_exists($strFieldName . '_Tags', $arrValues)) {
            $objTagFilter = new stdClass();
            $objTagFilter->typeId = $this->core->sysConfig->filter_types->tags;
            $objTagFilter->referenceIds = array_unique(explode(',', $arrValues[$strFieldName . '_Tags']));
            $objFilters->filters['ft' . $objTagFilter->typeId] = $objTagFilter;
        }

        if (array_key_exists($strFieldName . '_Folders', $arrValues)) {
            $objFoldersFilter = new stdClass();
            $objFoldersFilter->typeId = $this->core->sysConfig->filter_types->folders;
            $objFoldersFilter->referenceIds = array_unique(explode('][', trim($arrValues[$strFieldName . '_Folders'], '[]')));
            $objFilters->filters['ft' . $objFoldersFilter->typeId] = $objFoldersFilter;
        }

        if (array_key_exists($strFieldName . '_RootLevel', $arrValues)) {
            $objRootLeveFilter = new stdClass();
            $objRootLeveFilter->typeId = $this->core->sysConfig->filter_types->rootLevel;
            $objRootLeveFilter->referenceIds = array($arrValues[$strFieldName . '_RootLevel']);
            $objFilters->filters['ft' . $objRootLeveFilter->typeId] = $objRootLeveFilter;
        }

        return $objFilters;
    }

    /**
     * getArticlesObject
     * @param string $strFieldName
     * @param array $arrValues
     * @return $objArticles
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getArticlesObject($strFieldName, &$arrValues)
    {
        $articles = array();

        $arrArticleInstanceIds = array_unique(explode('][', trim($arrValues[$strFieldName . '_Instances'], '[]')));

        foreach ($arrArticleInstanceIds as $intInstanceId) {
            $objArticle = new stdClass();

            $objArticle->size = array_key_exists($strFieldName . '_size_' . $intInstanceId, $arrValues) ? $arrValues[$strFieldName . '_size_' . $intInstanceId] : null;
            $objArticle->price = array_key_exists($strFieldName . '_price_' . $intInstanceId, $arrValues) ? $arrValues[$strFieldName . '_price_' . $intInstanceId] : null;
            $objArticle->discount = array_key_exists($strFieldName . '_discount_' . $intInstanceId, $arrValues) ? $arrValues[$strFieldName . '_discount_' . $intInstanceId] : null;

            $articles[] = $objArticle;
        }

        return $articles;
    }

    /**
     * setMetaInformation
     * @param Zend_Db_Table_Row $objCurrElement
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setMetaInformation(Zend_Db_Table_Row $objCurrElement)
    {

        if (count($objCurrElement) > 0) {
            if (isset($objCurrElement->creator) && ($objCurrElement->creator != '' || !is_null($objCurrElement->creator))) {
                $this->setCreatorId($objCurrElement->creator);
            }
            if (isset($objCurrElement->publisher) && ($objCurrElement->publisher != '' || !is_null($objCurrElement->publisher))) {
                $this->setPublisherName($objCurrElement->publisher);
            }
            if (isset($objCurrElement->changeUser) && ($objCurrElement->changeUser != '' || !is_null($objCurrElement->changeUser))) {
                $this->setChangeUserName($objCurrElement->changeUser);
            }
            if (isset($objCurrElement->changed) && ($objCurrElement->changed != '' || !is_null($objCurrElement->changed))) {
                $this->setChangeDate($objCurrElement->changed);
            }
            if (isset($objCurrElement->published) && ($objCurrElement->published != '' || !is_null($objCurrElement->published))) {
                $this->setPublishDate($objCurrElement->published);
            }

            $this->setShowInNavigation((isset($objCurrElement->showInNavigation) ? $objCurrElement->showInNavigation : 0));
            $this->setLanguageFallbackId((isset($objCurrElement->idLanguageFallbacks) ? $objCurrElement->idLanguageFallbacks : 0));
            $this->setDestinationId((isset($objCurrElement->idDestination) ? $objCurrElement->idDestination : 0));
            $this->setSegmentId((isset($objCurrElement->idSegments) ? $objCurrElement->idSegments : 0));
            $this->setHideInSitemap((isset($objCurrElement->hideInSitemap) ? $objCurrElement->hideInSitemap : false));
            $this->setShowInWebsite((isset($objCurrElement->showInWebsite) ? $objCurrElement->showInWebsite : true));
            $this->setShowInTablet((isset($objCurrElement->showInTablet) ? $objCurrElement->showInTablet : true));
            $this->setShowInMobile((isset($objCurrElement->showInMobile) ? $objCurrElement->showInMobile : true));
            $this->setElementVersion((isset($objCurrElement->version) ? $objCurrElement->version : 0));
            $this->setStatusId((isset($objCurrElement->idStatus) ? $objCurrElement->idStatus : 0));
        }
    }

    /**
     * getDataTypeObject
     * @param integer $intFormTypeId
     * @return GenericDataTypeAbstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public static function getDataTypeObject($intFormTypeId)
    {
        switch ($intFormTypeId) {
            case GenericSetup::TYPE_PAGE :
                require_once(dirname(__FILE__) . '/data/types/generic.data.type.page.class.php');
                return new GenericDataTypePage();
            case GenericSetup::TYPE_FOLDER :
                require_once(dirname(__FILE__) . '/data/types/generic.data.type.folder.class.php');
                return new GenericDataTypeFolder();
            case GenericSetup::TYPE_CATEGORY :
                require_once(dirname(__FILE__) . '/data/types/generic.data.type.category.class.php');
                return new GenericDataTypeCategory();
            case GenericSetup::TYPE_UNIT :
                require_once(dirname(__FILE__) . '/data/types/generic.data.type.unit.class.php');
                return new GenericDataTypeUnit();
            case GenericSetup::TYPE_CONTACT :
                require_once(dirname(__FILE__) . '/data/types/generic.data.type.contact.class.php');
                return new GenericDataTypeContact();
            case GenericSetup::TYPE_GLOBAL :
                require_once(dirname(__FILE__) . '/data/types/generic.data.type.global.class.php');
                return new GenericDataTypeGlobal();
            case GenericSetup::TYPE_LOCATION :
                require_once(dirname(__FILE__) . '/data/types/generic.data.type.location.class.php');
                return new GenericDataTypeLocation();
            case GenericSetup::TYPE_MEMBER :
                require_once(dirname(__FILE__) . '/data/types/generic.data.type.member.class.php');
                return new GenericDataTypeMember();
            case GenericSetup::TYPE_COMPANY :
                require_once(dirname(__FILE__) . '/data/types/generic.data.type.company.class.php');
                return new GenericDataTypeCompany();
            case GenericSetup::TYPE_NEWSLETTER :
                require_once(dirname(__FILE__) . '/data/types/generic.data.type.newsletter.class.php');
                return new GenericDataTypeNewsletter();
            case GenericSetup::TYPE_SUBSCRIBER :
                require_once(dirname(__FILE__) . '/data/types/generic.data.type.subscriber.class.php');
                return new GenericDataTypeSubscriber();
        }
    }

    /**
     * getFormTypeHandle
     * @param integer $intFormTypeId
     * @return string
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public static function getFormTypeHandle($intFormTypeId)
    {
        switch ($intFormTypeId) {
            case GenericSetup::TYPE_PAGE :
                return 'page';
            case GenericSetup::TYPE_FOLDER :
                return 'folder';
            case GenericSetup::TYPE_CATEGORY :
                return 'category';
        }
    }

    /**
     * getModelGenericForm
     * @return Model_GenericForms
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getModelGenericForm()
    {
        if (null === $this->objModelGenericForm) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/GenericForms.php';
            $this->objModelGenericForm = new Model_GenericForms();
            $this->objModelGenericForm->setLanguageId($this->intFormLanguageId);
        }

        return $this->objModelGenericForm;
    }

    /**
     * getModelTemplates
     * @return Model_Templates
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getModelTemplates()
    {
        if (null === $this->objModelTemplates) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Templates.php';
            $this->objModelTemplates = new Model_Templates();
            $this->objModelTemplates->setLanguageId($this->intLanguageId);
        }
        return $this->objModelTemplates;
    }

    /**
     * setGenFormId
     * @param integer $intGenFormId
     */
    public function setGenFormId($intGenFormId)
    {
        $this->intGenFormId = $intGenFormId;
    }

    /**
     * getGenFormId
     * @param integer $intintGenFormId
     */
    public function getGenFormId()
    {
        return $this->intGenFormId;
    }

    /**
     * setFormId
     * @param string $strFormId
     */
    public function setFormId($strFormId)
    {
        $this->strFormId = $strFormId;
    }

    /**
     * getFormId
     * @param string $strFormId
     */
    public function getFormId()
    {
        return $this->strFormId;
    }

    /**
     * setTemplateId
     * @param integer $intTemplateId
     */
    public function setTemplateId($intTemplateId)
    {
        $this->intTemplateId = $intTemplateId;
    }

    /**
     * getTemplateId
     * @param integer $intTemplateId
     */
    public function getTemplateId()
    {
        return $this->intTemplateId;
    }

    /**
     * setFormVersion
     * @param integer $intFormVersion
     */
    public function setFormVersion($intFormVersion)
    {
        $this->intFormVersion = $intFormVersion;
    }

    /**
     * getFormVersion
     * @param integer $intFormVersion
     */
    public function getFormVersion()
    {
        return $this->intFormVersion;
    }

    /**
     * setFormTitle
     * @param string $strFormTitle
     */
    public function setFormTitle($strFormTitle)
    {
        $this->strFormTitle = $strFormTitle;
    }

    /**
     * getFormTitle
     * @param string $strFormTitle
     */
    public function getFormTitle()
    {
        return $this->strFormTitle;
    }

    /**
     * setFormTypeId
     * @param integer $intFormTypeId
     */
    public function setFormTypeId($intFormTypeId)
    {
        $this->intFormTypeId = $intFormTypeId;
    }

    /**
     * getFormTypeId
     * @return integer $intFormTypeId
     */
    public function getFormTypeId()
    {
        return $this->intFormTypeId;
    }

    /**
     * setFormType
     * @param string $strFormType
     */
    public function setFormType($strFormType)
    {
        $this->strFormType = $strFormType;
    }

    /**
     * getFormType
     * @return string $strFormType
     */
    public function getFormType()
    {
        return $this->strFormType;
    }

    /**
     * setActionType
     * @param integer $intActionType
     */
    public function setActionType($intActionType)
    {
        $this->intActionType = $intActionType;
    }

    /**
     * getActionType
     * @param integer $intActionType
     */
    public function getActionType()
    {
        return $this->intActionType;
    }

    /**
     * setRegionId
     * @param integer $intRegionId
     */
    public function setRegionId($intRegionId)
    {
        $this->intRegionId = $intRegionId;
    }

    /**
     * getRegionId
     * @param integer $intRegionId
     */
    public function getRegionId()
    {
        return $this->intRegionId;
    }

    /**
     * setRegionCols
     * @param integer $intRegionCols
     */
    public function setRegionCols($intRegionCols)
    {
        $this->intRegionCols = $intRegionCols;
    }

    /**
     * getRegionCols
     * @param integer $intRegionCols
     */
    public function getRegionCols()
    {
        return $this->intRegionCols;
    }

    /**
     * setRegionPosition
     * @param integer $intRegionPosition
     */
    public function setRegionPosition($intRegionPosition)
    {
        $this->intRegionPosition = $intRegionPosition;
    }

    /**
     * getRegionPosition
     * @param integer $intRegionPosition
     */
    public function getRegionPosition()
    {
        return $this->intRegionPosition;
    }

    /**
     * setRegionTitle
     * @param string $strRegionTitle
     */
    public function setRegionTitle($strRegionTitle)
    {
        $this->strRegionTitle = $strRegionTitle;
    }

    /**
     * getRegionTitle
     * @param string $strRegionTitle
     */
    public function getRegionTitle()
    {
        return $this->strRegionTitle;
    }

    /**
     * setRegionCollapsable
     * @param boolean $blnRegionCollapsable
     */
    public function setRegionCollapsable($blnRegionCollapsable)
    {
        $this->blnRegionCollapsable = $blnRegionCollapsable;
    }

    /**
     * getRegionCollapsable
     * @param boolean $blnRegionCollapsable
     */
    public function getRegionCollapsable()
    {
        return $this->blnRegionCollapsable;
    }

    /**
     * setLanguageId
     * @param integer $intLanguageId
     */
    public function setLanguageId($intLanguageId)
    {
        $this->intLanguageId = $intLanguageId;
    }

    /**
     * getLanguageId
     * @param integer $intLanguageId
     */
    public function getLanguageId()
    {
        return $this->intLanguageId;
    }

    /**
     * setLanguageCode
     * @param string $strLanguageCode
     */
    public function setLanguageCode($strLanguageCode)
    {
        $this->strLanguageCode = $strLanguageCode;
    }

    /**
     * getLanguageCode
     * @param string $strLanguageCode
     */
    public function getLanguageCode()
    {
        return $this->strLanguageCode;
    }

    /**
     * setLanguageFallbackId
     * @param integer $intLanguageFallbackId
     */
    public function setLanguageFallbackId($intLanguageFallbackId)
    {
        $this->intLanguageFallbackId = $intLanguageFallbackId;
    }

    /**
     * getLanguageFallbackId
     * @param integer $intLanguageFallbackId
     */
    public function getLanguageFallbackId()
    {
        return $this->intLanguageFallbackId;
    }

    /**
     * setDestinationId
     * @param integer $intDestinationId
     */
    public function setDestinationId($intDestinationId)
    {
        $this->intDestinationId = $intDestinationId;
    }

    /**
     * getDestinationId
     * @param integer $intDestinationId
     */
    public function getDestinationId()
    {
        return $this->intDestinationId;
    }

    /**
     * setSegmentId
     * @param integer $intSegmentId
     */
    public function setSegmentId($intSegmentId)
    {
        $this->intSegmentId = $intSegmentId;
    }

    /**
     * getSegmentId
     * @param integer $intSegmentId
     */
    public function getSegmentId()
    {
        return $this->intSegmentId;
    }

    /**
     * setHideInSitemap
     * @param boolean $blnHideInSitemap
     */
    public function setHideInSitemap($blnHideInSitemap, $blnValidate = true)
    {
        if ($blnValidate == true) {
            if ($blnHideInSitemap === true || $blnHideInSitemap === 'true' || $blnHideInSitemap == 1) {
                $this->blnHideInSitemap = true;
            } else {
                $this->blnHideInSitemap = false;
            }
        } else {
            $this->blnHideInSitemap = $blnHideInSitemap;
        }
    }

    /**
     * getHideInSitemap
     * @return boolean $blnHideInSitemap
     */
    public function getHideInSitemap($blnReturnAsNumber = true)
    {
        if ($blnReturnAsNumber == true) {
            if ($this->blnHideInSitemap == true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return $this->blnHideInSitemap;
        }
    }

    /**
     * setShowInWebsite
     * @param boolean $blnShowInWebsite
     */
    public function setShowInWebsite($blnShowInWebsite, $blnValidate = true)
    {
        if ($blnValidate == true) {
            if ($blnShowInWebsite === true || $blnShowInWebsite === 'true' || $blnShowInWebsite == 1) {
                $this->blnShowInWebsite = true;
            } else {
                $this->blnShowInWebsite = false;
            }
        } else {
            $this->blnShowInWebsite = $blnShowInWebsite;
        }
    }

    /**
     * getShowInWebsite
     * @return boolean $blnShowInWebsite
     */
    public function getShowInWebsite($blnReturnAsNumber = true)
    {
        if ($blnReturnAsNumber == true) {
            if ($this->blnShowInWebsite == true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return $this->blnShowInWebsite;
        }
    }

    /**
     * setShowInTablet
     * @param boolean $blnShowInTablet
     */
    public function setShowInTablet($blnShowInTablet, $blnValidate = true)
    {
        if ($blnValidate == true) {
            if ($blnShowInTablet === true || $blnShowInTablet === 'true' || $blnShowInTablet == 1) {
                $this->blnShowInTablet = true;
            } else {
                $this->blnShowInTablet = false;
            }
        } else {
            $this->blnShowInTablet = $blnShowInTablet;
        }
    }

    /**
     * getShowInTablet
     * @return boolean $blnShowInTablet
     */
    public function getShowInTablet($blnReturnAsNumber = true)
    {
        if ($blnReturnAsNumber == true) {
            if ($this->blnShowInTablet == true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return $this->blnShowInTablet;
        }
    }

    /**
     * setShowInMobile
     * @param boolean $blnShowInMobile
     */
    public function setShowInMobile($blnShowInMobile, $blnValidate = true)
    {
        if ($blnValidate == true) {
            if ($blnShowInMobile === true || $blnShowInMobile === 'true' || $blnShowInMobile == 1) {
                $this->blnShowInMobile = true;
            } else {
                $this->blnShowInMobile = false;
            }
        } else {
            $this->blnShowInMobile = $blnShowInMobile;
        }
    }

    /**
     * getShowInMobile
     * @return boolean $blnShowInTablet
     */
    public function getShowInMobile($blnReturnAsNumber = true)
    {
        if ($blnReturnAsNumber == true) {
            if ($this->blnShowInMobile == true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return $this->blnShowInMobile;
        }
    }

    /**
     * removeSpecialField
     * @param string $strKey
     */
    public function removeSpecialField($strKey)
    {
        if (array_key_exists($strKey, $this->arrSpecialFields)) {
            unset($this->arrSpecialFields[$strKey]);
        }
    }

    /**
     * setFormLanguageId
     * @param integer $intFormLanguageId
     */
    public function setFormLanguageId($intFormLanguageId)
    {
        $this->intFormLanguageId = $intFormLanguageId;
    }

    /**
     * getFormLanguageId
     * @param integer $intFormLanguageId
     */
    public function getFormLanguageId()
    {
        return $this->intFormLanguageId;
    }

    /**
     * setElementId
     * @param integer $intElementId
     */
    public function setElementId($intElementId)
    {
        $this->intElementId = $intElementId;
    }

    /**
     * getElementId
     * @param integer $intElementId
     */
    public function getElementId()
    {
        return $this->intElementId;
    }

    /**
     * setElementVersion
     * @param integer $intElementVersion
     */
    public function setElementVersion($intElementVersion)
    {
        $this->intElementVersion = $intElementVersion;
    }

    /**
     * getElementVersion
     * @param integer $intElementVersion
     */
    public function getElementVersion()
    {
        return $this->intElementVersion;
    }

    /**
     * setElementLinkId
     * @param integer $intElementLinkId
     */
    public function setElementLinkId($intElementLinkId)
    {
        $this->intElementLinkId = $intElementLinkId;
    }

    /**
     * getElementLinkId
     * @param integer $intElementLinkId
     */
    public function getElementLinkId()
    {
        return $this->intElementLinkId;
    }

    /**
     * setParentId
     * @param integer $intParentId
     */
    public function setParentId($intParentId)
    {
        $this->intParentId = $intParentId;
    }

    /**
     * getParentId
     * @param integer $intParentId
     */
    public function getParentId()
    {
        return $this->intParentId;
    }

    /**
     * setParentTypeId
     * @param integer $intParentTypeId
     */
    public function setParentTypeId($intParentTypeId)
    {
        $this->intParentTypeId = $intParentTypeId;
    }

    /**
     * getParentTypeId
     * @param integer $intParentTypeId
     */
    public function getParentTypeId()
    {
        return $this->intParentTypeId;
    }

    /**
     * setRootLevelId
     * @param integer $intRootLevelId
     */
    public function setRootLevelId($intRootLevelId)
    {
        $this->intRootLevelId = $intRootLevelId;
    }

    /**
     * getRootLevelId
     * @param integer $intRootLevelId
     */
    public function getRootLevelId()
    {
        return (int) $this->intRootLevelId;
    }

    /**
     * setRootLevelTypeId
     * @param integer $intRootLevelTypeId
     */
    public function setRootLevelTypeId($intRootLevelTypeId)
    {
        $this->intRootLevelTypeId = $intRootLevelTypeId;
    }

    /**
     * getRootLevelTypeId
     * @param integer $intRootLevelTypeId
     */
    public function getRootLevelTypeId()
    {
        return $this->intRootLevelTypeId;
    }

    /**
     * setRootLevelGroupId
     * @param integer $intRootLevelGroupId
     */
    public function setRootLevelGroupId($intRootLevelGroupId)
    {
        $this->intRootLevelGroupId = $intRootLevelGroupId;
    }

    /**
     * getRootLevelGroupId
     * @param integer $intRootLevelGroupId
     */
    public function getRootLevelGroupId()
    {
        return $this->intRootLevelGroupId;
    }

    /**
     * setElementTypeId
     * @param integer $intElementTypeId
     */
    public function setElementTypeId($intElementTypeId)
    {
        $this->intElementTypeId = $intElementTypeId;
    }

    /**
     * getElementTypeId
     * @param integer $intElementTypeId
     */
    public function getElementTypeId()
    {
        return $this->intElementTypeId;
    }

    /**
     * setIsStartElement
     * @param boolean $blnIsStartElement
     */
    public function setIsStartElement($blnIsStartElement, $blnValidate = true)
    {
        if ($blnValidate == true) {
            if ($blnIsStartElement === true || $blnIsStartElement === 'true' || $blnIsStartElement == 1) {
                $this->blnIsStartElement = true;
            } else {
                $this->blnIsStartElement = false;
            }
        } else {
            $this->blnIsStartElement = $blnIsStartElement;
        }
    }

    /**
     * getIsStartElement
     * @return boolean $blnIsStartElement
     */
    public function getIsStartElement($blnReturnAsNumber = true)
    {
        if ($blnReturnAsNumber == true) {
            if ($this->blnIsStartElement == true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return $this->blnIsStartElement;
        }
    }

    /**
     * setShowInNavigation
     * @param boolean $intShowInNavigation
     */
    public function setShowInNavigation($intShowInNavigation)
    {
        $this->intShowInNavigation = $intShowInNavigation;
    }

    /**
     * getShowInNavigation
     * @return boolean $intShowInNavigation
     */
    public function getShowInNavigation()
    {
        return $this->intShowInNavigation;
    }

    /**
     * setCreatorId
     * @param integer $intCreatorId
     */
    public function setCreatorId($intCreatorId)
    {
        $this->intCreatorId = $intCreatorId;
    }

    /**
     * getCreatorId
     * @param integer $intCreatorId
     */
    public function getCreatorId()
    {
        return $this->intCreatorId;
    }

    /**
     * setPublisherName
     * @param string $strPublisherName
     */
    public function setPublisherName($strPublisherName)
    {
        $this->strPublisherName = $strPublisherName;
    }

    /**
     * getPublisherName
     * @param string $strPublisherName
     */
    public function getPublisherName()
    {
        return $this->strPublisherName;
    }

    /**
     * setChangeUserName
     * @param string $strChangeUserName
     */
    public function setChangeUserName($strChangeUserName)
    {
        $this->strChangeUserName = $strChangeUserName;
    }

    /**
     * getChangeUserName
     * @param string $strChangeUserName
     */
    public function getChangeUserName()
    {
        return $this->strChangeUserName;
    }

    /**
     * setStatusId
     * @param integer $intStatusId
     */
    public function setStatusId($intStatusId)
    {
        $this->intStatusId = $intStatusId;
    }

    /**
     * getStatusId
     * @param integer $intStatusId
     */
    public function getStatusId()
    {
        return $this->intStatusId;
    }

    /**
     * setUrlFolder
     * @param integer $intUrlFolder
     */
    public function setUrlFolder($intUrlFolder)
    {
        $this->intUrlFolder = $intUrlFolder;
    }

    /**
     * getUrlFolder
     * @param integer $intUrlFolder
     */
    public function getUrlFolder()
    {
        return $this->intUrlFolder;
    }

    /**
     * setChangeDate
     * @param string/obj $Date
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setChangeDate($Date, $blnIsValidDateObj = false)
    {
        if ($blnIsValidDateObj == true) {
            $this->objChangeDate = $Date;
        } else {
            $arrTmpTimeStamp = explode(' ', $Date);
            if (count($arrTmpTimeStamp) > 0) {
                $arrTmpTime = explode(':', $arrTmpTimeStamp[1]);
                $arrTmpDate = explode('-', $arrTmpTimeStamp[0]);
                if (count($arrTmpDate) == 3) {
                    $this->objChangeDate = mktime($arrTmpTime[0], $arrTmpTime[1], $arrTmpTime[2], $arrTmpDate[1], $arrTmpDate[2], $arrTmpDate[0]);
                }
            }
        }
    }

    /**
     * getChangeDate
     * @param string $strFormat
     * @return string $strChangeDate
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getChangeDate($strFormat = 'Y-m-d', $blnGetDateObj = false)
    {
        if ($blnGetDateObj == true) {
            return $this->objChangeDate;
        } else {
            if ($this->objChangeDate != null) {
                return date($strFormat, $this->objChangeDate);
            } else {
                return null;
            }
        }
    }

    /**
     * setPublishDate
     * @param string/obj $Date
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setPublishDate($Date, $blnIsValidDateObj = false)
    {
        if ($blnIsValidDateObj == true) {
            $this->objPublishDate = $Date;
        } else {
            $arrTmpTimeStamp = explode(' ', $Date);
            if (count($arrTmpTimeStamp) > 0) {
                $arrTmpTime = explode(':', $arrTmpTimeStamp[1]);
                $arrTmpDate = explode('-', $arrTmpTimeStamp[0]);
                if (count($arrTmpDate) == 3) {
                    $this->objPublishDate = mktime($arrTmpTime[0], $arrTmpTime[1], $arrTmpTime[2], $arrTmpDate[1], $arrTmpDate[2], $arrTmpDate[0]);
                }
            }
        }
    }

    /**
     * getPublishDate
     * @param string $strFormat
     * @return string $strPublishDate
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getPublishDate($strFormat = 'Y-m-d H:i:s', $blnGetDateObj = false)
    {
        if ($blnGetDateObj == true) {
            return $this->objPublishDate;
        } else {
            if ($this->objPublishDate != null) {
                return date($strFormat, $this->objPublishDate);
            } else {
                return null;
            }
        }
    }

    /**
     * setModelSubPath
     * @param string $strModelSubPath
     */
    public function setModelSubPath($strModelSubPath)
    {
        $this->strModelSubPath = $strModelSubPath;
    }

    /**
     * getModelSubPath
     * @param string $strModelSubPath
     */
    public function getModelSubPath()
    {
        return $this->strModelSubPath;
    }
}

?>