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
 * GenericFormTypePage im
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-16: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data.types
 * @subpackage GenericFormTypePage
 */

require_once(dirname(__FILE__) . '/generic.data.type.interface.php');

abstract class GenericDataTypeAbstract implements GenericDataTypeInterface
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
     *
     * @return GenericSetup $setup
     */
    public function Setup()
    {
        return $this->setup;
    }

    /**
     * @var Array
     */
    private $arrDbIdFields = array();

    /**
     * @var Model_GenericData
     */
    protected $objModelGenericData;

    /**
     * @var Model_Files
     */
    protected $objModelFiles;

    protected $blnHasLoadedFileData = false;
    protected $blnHasLoadedMultiFieldData = false;
    protected $blnHasLoadedInstanceData = false;
    protected $blnHasLoadedMultiplyRegionData = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * save
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    abstract public function save();

    /**
     * load
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    abstract public function load();

    /**
     * insertCoreData
     *
     * @param string $strType,
     * @param string $strTypeId
     * @param int $intTypeVersion
     *
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    final protected function insertCoreData($strType, $strTypeId, $intTypeVersion)
    {
        if (count($this->setup->CoreFields()) > 0) {

            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

            /**
             * for each core field, try to insert into the secondary table
             */
            foreach ($this->setup->CoreFields() as $strField => $objField) {

                $objGenTable = $this->getModelGenericData()->getGenericTable($strType . str_replace('_', '', ((substr($strField, strlen($strField) - 1) == 'y') ? ucfirst(rtrim($strField, 'y')) . 'ies' : ucfirst($strField) . 's')));

                if ($objField->getValue() != '') {
                    if ($objField->getProperty('type') === 'media') {

                        $objGenTable = $this->getModelGenericData()->getGenericTable($strType . 'Files');

                        $strTmpFileIds = trim($objField->getValue(), '[]');
                        $arrFileIds = array();
                        $arrFileIds = explode('][', $strTmpFileIds);

                        // start transaction
                        $this->core->dbh->beginTransaction();
                        try {
                            $strWhere = $objGenTable->getAdapter()->quoteInto($strType . 'Id = ?', $strTypeId);
                            $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND version = ?', $intTypeVersion);
                            $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->setup->getLanguageId());
                            // delete
                            $objGenTable->delete($strWhere);

                            $strDisplayOption = $objField->getProperty('display_option');

                            // insert data
                            foreach ($arrFileIds as $key => $value) {
                                $arrCoreData = array(
                                    $strType . 'Id' => $strTypeId,
                                    'version'       => $intTypeVersion,
                                    'idLanguages'   => $this->setup->getLanguageId(),
                                    'idFiles'       => $value,
                                    'idFields'      => $objField->id,
                                    'sortPosition'  => $key + 1,
                                    'displayOption' => $strDisplayOption
                                );
                                $objGenTable->insert($arrCoreData);
                            }

                            /**
                             * commit transaction
                             */
                            $this->core->dbh->commit();
                        } catch (Exception $exc) {
                            /**
                             * roll back
                             */
                            $this->core->dbh->rollBack();
                            $this->core->logger->err($exc);
                        }
                    } else {
                        /**
                         * if field has already been loaded, update data ( -> e.g. change template)
                         */
                        if ($objField->blnHasLoadedData === true) {
                            if (is_array($objField->getValue())) {

                                /**
                                 * start transaction
                                 */
                                $this->core->dbh->beginTransaction();
                                try {
                                    $strWhere = $objGenTable->getAdapter()->quoteInto($strType . 'Id = ?', $strTypeId);
                                    $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND version = ?', $intTypeVersion);
                                    $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->setup->getLanguageId());

                                    /**
                                     * delete data
                                     */
                                    $objGenTable->delete($strWhere);

                                    /**
                                     * insert data
                                     */
                                    foreach ($objField->getValue() as $key => $value) {
                                        $arrCoreData = array(
                                            $strType . 'Id' => $strTypeId,
                                            'version'       => $intTypeVersion,
                                            'idLanguages'   => $this->setup->getLanguageId(),
                                            $strField       => $value,
                                            'idUsers'       => $intUserId,
                                            'creator'       => $intUserId,
                                            'created'       => date('Y-m-d H:i:s')
                                        );

                                        $objGenTable->insert($arrCoreData);
                                    }

                                    /**
                                     * commit transaction
                                     */
                                    $this->core->dbh->commit();
                                } catch (Exception $exc) {
                                    /**
                                     * roll back
                                     */
                                    $this->core->dbh->rollBack();
                                    $this->core->logger->err($exc);
                                }
                            } else {
                                $arrCoreData = array(
                                    $strField => $objField->getValue(),
                                    'idUsers' => $intUserId
                                );

                                $strWhere = $objGenTable->getAdapter()->quoteInto($strType . 'Id = ?', $strTypeId);
                                $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND version = ?', $intTypeVersion);
                                $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->setup->getLanguageId());

                                $objGenTable->update($arrCoreData, $strWhere);
                            }
                        } else {
                            if (is_array($objField->getValue())) {
                                foreach ($objField->getValue() as $key => $value) {
                                    $arrCoreData = array(
                                        $strType . 'Id' => $strTypeId,
                                        'version'       => $intTypeVersion,
                                        'idLanguages'   => $this->setup->getLanguageId(),
                                        $strField       => $value,
                                        'idUsers'       => $intUserId,
                                        'creator'       => $intUserId,
                                        'created'       => date('Y-m-d H:i:s')
                                    );

                                    $objGenTable->insert($arrCoreData);
                                }
                            } else {
                                $arrCoreData = array(
                                    $strType . 'Id' => $strTypeId,
                                    'version'       => $intTypeVersion,
                                    'idLanguages'   => $this->setup->getLanguageId(),
                                    $strField       => $objField->getValue(),
                                    'idUsers'       => $intUserId,
                                    'creator'       => $intUserId,
                                    'created'       => date('Y-m-d H:i:s')
                                );

                                $objGenTable->insert($arrCoreData);
                            }
                        }
                    }

                    /**
                     * add title for zoolu gui fallback with language id = 0
                     */
                    if ($strField == 'title') {
                        $this->saveZooluFallbackTitle($objField->getValue(), $strType, $strTypeId, $intTypeVersion, $objGenTable);
                    }
                }
            }
        }
    }

    /**
     * insertFileData
     *
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    final protected function insertFileData($strType, $arrTypeProperties)
    {
        if (count($this->setup->FileFields()) > 0) {

            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

            /**
             * insert into the file instances table
             */
            foreach ($this->setup->FileFields() as $strFieldName => $objField) {

                $intFieldId = $objField->id;

                $strTmpFileIds = trim($objField->getValue(), '[]');
                $arrFileIds = array();
                $arrFileIds = explode('][', $strTmpFileIds);

                $strDisplayOption = $objField->getProperty('display_option');

                if (count($arrFileIds) > 0) {
                    foreach ($arrFileIds as $intSortPosition => $intFileId) {
                        if ($intFileId != '') {
                            if (isset($arrTypeProperties['Version'])) {
                                $arrFileData = array(
                                    $strType . 'Id' => $arrTypeProperties['Id'],
                                    'version'       => $arrTypeProperties['Version'],
                                    'idLanguages'   => $this->setup->getLanguageId(),
                                    'sortPosition'  => $intSortPosition + 1,
                                    'idFiles'       => $intFileId,
                                    'idFields'      => $intFieldId
                                );
                            } else {
                                $arrFileData = array(
                                    $this->getDbIdFieldForType($strType) => $arrTypeProperties['Id'],
                                    'idFiles'                            => $intFileId,
                                    'idFields'                           => $intFieldId,
                                    'sortPosition'                       => $intSortPosition + 1
                                );
                            }

                            if ($strDisplayOption != '') {
                                $arrFileData['displayOption'] = $strDisplayOption;
                            }

                            $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-InstanceFiles')->insert($arrFileData);
                        }
                    }
                }
            }
        }
    }

    /**
     * insertMultiFieldData
     *
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Thomas Shedler <tsh@massiveart.com>
     * @version 1.0
     */
    final protected function insertMultiFieldData($strType, $arrTypeProperties)
    {

        if (count($this->setup->MultiFields()) > 0) {

            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

            /**
             * insert into the multi fields instances table
             */
            foreach ($this->setup->MultiFields() as $strFieldName => $objField) {

                $intFieldId = $objField->id;

                if (is_array($objField->getValue()) && count($objField->getValue()) > 0) {
                    foreach ($objField->getValue() as $intRelationId) {
                        if ($intRelationId != '') {
                            if (isset($arrTypeProperties['Version'])) {
                                $arrFileData = array(
                                    $strType . 'Id' => $arrTypeProperties['Id'],
                                    'version'       => $arrTypeProperties['Version'],
                                    'idLanguages'   => $this->setup->getLanguageId(),
                                    'idRelation'    => $intRelationId,
                                    //'value'       => '', TODO ::  load value, if copyValue is true
                                    'idFields'      => $intFieldId
                                );
                            } else {
                                $arrFileData = array(
                                    $this->getDbIdFieldForType($strType) => $arrTypeProperties['Id'],
                                    'idRelation'                         => $intRelationId,
                                    //'value'                              => '', TODO ::  load value, if copyValue is true
                                    'idFields'                           => $intFieldId
                                );
                            }

                            $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-InstanceMultiFields')->insert($arrFileData);
                        }
                    }
                }
            }
        }
    }

    /**
     * insertInstanceData
     *
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    final protected function insertInstanceData($strType, $arrTypeProperties)
    {

        if (count($this->setup->InstanceFields()) > 0) {

            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

            if (isset($arrTypeProperties['Version'])) {
                $arrInstanceData = array(
                    $strType . 'Id' => $arrTypeProperties['Id'],
                    'version'       => $arrTypeProperties['Version'],
                    'idLanguages'   => $this->setup->getLanguageId(),
                    'idUsers'       => $intUserId,
                    'creator'       => $intUserId,
                    'created'       => date('Y-m-d H:i:s')
                );
            } else {
                $arrInstanceData = array(
                    $this->getDbIdFieldForType($strType) => $arrTypeProperties['Id'],
                    'idUsers'                            => $intUserId,
                    'creator'                            => $intUserId,
                    'created'                            => date('Y-m-d H:i:s')
                );
            }


            /**
             * for each instance field, add to instance data array
             */
            foreach ($this->setup->InstanceFields() as $strField => $objField) {
                if (is_array($objField->getValue())) {
                    $arrInstanceData[$strField] = json_encode($objField->getValue());
                } else {
                    $arrInstanceData[$strField] = $objField->getValue();
                }
            }

            $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-Instances')->insert($arrInstanceData);
        }
    }

    /**
     * insertMultiplyRegionData
     *
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    final protected function insertMultiplyRegionData($strType, $arrTypeProperties)
    {
        try {
            if (count($this->setup->MultiplyRegionIds()) > 0) {
                /**
                 * start transaction
                 */
                $this->core->dbh->beginTransaction();
                try {
                    /**
                     * for each multiply region, insert data
                     */
                    $this->core->logger->debug('insertMultiplyRegionData');
                    foreach ($this->setup->MultiplyRegionIds() as $intRegionId) {
                        $objRegion = $this->setup->getRegion($intRegionId);

                        if ($objRegion instanceof GenericElementRegion) {
                            $intRegionPosition = 0;
                            foreach ($objRegion->RegionInstanceIds() as $intRegionInstanceId) {
                                $intRegionPosition++;
                                $this->insertMultiplyRegionInstanceData($objRegion, $intRegionInstanceId, $intRegionPosition, $strType, $arrTypeProperties);
                            }
                        }
                    }

                    /**
                     * commit transaction
                     */
                    $this->core->dbh->commit();
                } catch (Exception $exc) {
                    /**
                     * roll back
                     */
                    $this->core->dbh->rollBack();
                    $this->core->logger->err($exc);
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * saveZooluFallbackTitle
     *
     * @param string $stTitle
     * @param string $strType
     * @param string $strTypeId
     * @param integer $intTypeVersion
     * @param Model_Table_Generics $objGenTable
     *
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    final private function saveZooluFallbackTitle($stTitle, $strType, $strTypeId, $intTypeVersion, Model_Table_Generics $objGenTable)
    {
        try {
            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

            $objGenItem = $objGenTable->fetchRow($objGenTable->select()
                ->where($strType . 'Id = ?', $strTypeId)
                ->where('version = ?', $intTypeVersion)
                ->where('idLanguages = ?', 0));
            if (count($objGenItem) == 0) {
                $this->core->logger->info('insert zoolu gui fallback title');
                $arrCoreData = array(
                    $strType . 'Id' => $strTypeId,
                    'version'       => $intTypeVersion,
                    'idLanguages'   => 0,
                    'title'         => $stTitle,
                    'idUsers'       => $intUserId,
                    'creator'       => $intUserId,
                    'created'       => date('Y-m-d H:i:s')
                );
                $objGenTable->insert($arrCoreData);
            } else {
                $this->core->logger->info('update zoolu gui fallback title');
                $strWhere = $objGenTable->getAdapter()->quoteInto($strType . 'Id = ?', $strTypeId);
                $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND version = ?', $intTypeVersion);
                $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', 0);
                $arrCoreData = array(
                    'title'   => $stTitle,
                    'idUsers' => $intUserId
                );
                $objGenTable->update($arrCoreData, $strWhere);
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * updateCoreData
     *
     * @param string $strType
     * @param string $strTypeId
     * @param int $intTypeVersion
     *
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    final protected function updateCoreData($strType, $strTypeId, $intTypeVersion)
    {

        if (count($this->setup->CoreFields()) > 0) {

            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

            /**
             * for each core field, try to insert into the secondary table
             */
            foreach ($this->setup->CoreFields() as $strField => $objField) {

                $objGenTable = $this->getModelGenericData()->getGenericTable($strType . str_replace('_', '', ((substr($strField, strlen($strField) - 1) == 'y') ? ucfirst(rtrim($strField, 'y')) . 'ies' : ucfirst($strField) . 's')));

                if ($objField->getValue() != '') {
                    if ($objField->getProperty('type') === 'media') {
                        $objGenTable = $this->getModelGenericData()->getGenericTable($strType . 'Files');

                        $strTmpFileIds = trim($objField->getValue(), '[]');
                        $arrFileIds = array();
                        $arrFileIds = explode('][', $strTmpFileIds);


                        // start transaction
                        $this->core->dbh->beginTransaction();
                        try {
                            $strWhere = $objGenTable->getAdapter()->quoteInto($strType . 'Id = ?', $strTypeId);
                            $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND version = ?', $intTypeVersion);
                            $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->setup->getLanguageId());
                            $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idFields = ?', $objField->id);

                            // delete
                            $objGenTable->delete($strWhere);

                            $strDisplayOption = $objField->getProperty('display_option');

                            // insert data
                            foreach ($arrFileIds as $key => $value) {
                                $arrCoreData = array(
                                    $strType . 'Id' => $strTypeId,
                                    'version'       => $intTypeVersion,
                                    'idLanguages'   => $this->setup->getLanguageId(),
                                    'idFiles'       => $value,
                                    'idFields'      => $objField->id,
                                    'sortPosition'  => $key + 1,
                                    'displayOption' => $strDisplayOption
                                );
                                $objGenTable->insert($arrCoreData);
                            }

                            /**
                             * commit transaction
                             */
                            $this->core->dbh->commit();
                        } catch (Exception $exc) {
                            /**
                             * roll back
                             */
                            $this->core->dbh->rollBack();
                            $this->core->logger->err($exc);
                        }
                    } else {
                        if (is_array($objField->getValue())) {

                            /**
                             * start transaction
                             */
                            $this->core->dbh->beginTransaction();
                            try {
                                $strWhere = $objGenTable->getAdapter()->quoteInto($strType . 'Id = ?', $strTypeId);
                                $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND version = ?', $intTypeVersion);
                                $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->setup->getLanguageId());

                                /**
                                 * delete
                                 */
                                $objGenTable->delete($strWhere);

                                /**
                                 * insert data
                                 */
                                foreach ($objField->getValue() as $key => $value) {
                                    $arrCoreData = array(
                                        $strType . 'Id' => $strTypeId,
                                        'version'       => $intTypeVersion,
                                        'idLanguages'   => $this->setup->getLanguageId(),
                                        $strField       => $value,
                                        'idUsers'       => $intUserId,
                                        'creator'       => $intUserId,
                                        'created'       => date('Y-m-d H:i:s')
                                    );

                                    $objGenTable->insert($arrCoreData);
                                }

                                /**
                                 * commit transaction
                                 */
                                $this->core->dbh->commit();
                            } catch (Exception $exc) {
                                /**
                                 * roll back
                                 */
                                $this->core->dbh->rollBack();
                                $this->core->logger->err($exc);
                            }
                        } else {
                            $arrCoreData = array(
                                $strField => $objField->getValue(),
                                'idUsers' => $intUserId,
                                'changed' => date('Y-m-d H:i:s')
                            );

                            $strWhere = $objGenTable->getAdapter()->quoteInto($strType . 'Id = ?', $strTypeId);
                            $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND version = ?', $intTypeVersion);
                            $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->setup->getLanguageId());

                            $intNumOfEffectedRows = $objGenTable->update($arrCoreData, $strWhere);

                            if ($intNumOfEffectedRows == 0 && $objField->getValue() != '') {
                                $arrCoreData = array(
                                    $strType . 'Id' => $strTypeId,
                                    'version'       => $intTypeVersion,
                                    'idLanguages'   => $this->setup->getLanguageId(),
                                    $strField       => $objField->getValue(),
                                    'idUsers'       => $intUserId,
                                    'creator'       => $intUserId,
                                    'created'       => date('Y-m-d H:i:s')
                                );

                                $objGenTable->insert($arrCoreData);
                            }
                        }
                    }

                    /**
                     * update title for zoolu gui fallback with language id = 0
                     */
                    if ($strField == 'title') {
                        $this->saveZooluFallbackTitle($objField->getValue(), $strType, $strTypeId, $intTypeVersion, $objGenTable);
                    }
                } else {
                    if ($objField->getProperty('type') === 'media') {
                        $objGenTable = $this->getModelGenericData()->getGenericTable($strType . 'Files');
                        $strWhere = $objGenTable->getAdapter()->quoteInto($strType . 'Id = ?', $strTypeId);
                        $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND version = ?', $intTypeVersion);
                        $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->setup->getLanguageId());
                        $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idFields = ?', $objField->id);

                        /**
                         * delete
                         */
                        $objGenTable->delete($strWhere);
                    } else {
                        $strWhere = $objGenTable->getAdapter()->quoteInto($strType . 'Id = ?', $strTypeId);
                        $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND version = ?', $intTypeVersion);
                        $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->setup->getLanguageId());

                        /**
                         * delete
                         */
                        $objGenTable->delete($strWhere);
                    }
                }
            }
        }
    }

    /**
     * updateFileData
     *
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    final protected function updateFileData($strType, $arrTypeProperties)
    {
        if (count($this->setup->FileFields()) > 0) {

            $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-InstanceFiles');

            if (isset($arrTypeProperties['Version'])) {
                $strWhere = $objGenTable->getAdapter()->quoteInto($strType . 'Id = ?', $arrTypeProperties['Id']);
                $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND version = ?', $arrTypeProperties['Version']);
                $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->setup->getLanguageId());
            } else {
                $strWhere = $objGenTable->getAdapter()->quoteInto($this->getDbIdFieldForType($strType) . ' = ?', $arrTypeProperties['Id']);
            }

            $objGenTable->delete($strWhere);

            /**
             * update the file instances table
             */
            foreach ($this->setup->FileFields() as $strField => $objField) {
                $intFieldId = $objField->id;

                $strTmpFileIds = trim($objField->getValue(), '[]');
                $arrFileIds = array();
                $arrFileIds = explode('][', $strTmpFileIds);

                $strDisplayOption = $objField->getProperty('display_option');

                if (count($arrFileIds) > 0) {
                    foreach ($arrFileIds as $intSortPosition => $intFileId) {
                        if ($intFileId != '') {
                            if (isset($arrTypeProperties['Version'])) {
                                $arrFileData = array(
                                    $strType . 'Id' => $arrTypeProperties['Id'],
                                    'version'       => $arrTypeProperties['Version'],
                                    'idLanguages'   => $this->setup->getLanguageId(),
                                    'sortPosition'  => $intSortPosition + 1,
                                    'idFiles'       => $intFileId,
                                    'idFields'      => $intFieldId
                                );
                            } else {
                                $arrFileData = array(
                                    $this->getDbIdFieldForType($strType) => $arrTypeProperties['Id'],
                                    'idFiles'                            => $intFileId,
                                    'idFields'                           => $intFieldId,
                                    'sortPosition'                       => $intSortPosition + 1,
                                );
                            }

                            if ($strDisplayOption != '') {
                                $arrFileData['displayOption'] = $strDisplayOption;
                            }

                            $objGenTable->insert($arrFileData);
                        }
                    }
                }
            }
        }
    }

    /**
     * updateMultiFieldData
     *
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Thomas Schedler <cha@massiveart.com>
     * @version 1.0
     */
    final protected function updateMultiFieldData($strType, $arrTypeProperties)
    {

        if (count($this->setup->MultiFields()) > 0) {

            $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-InstanceMultiFields');

            if (isset($arrTypeProperties['Version'])) {
                $strWhere = $objGenTable->getAdapter()->quoteInto($strType . 'Id = ?', $arrTypeProperties['Id']);
                $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND version = ?', $arrTypeProperties['Version']);
                $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->setup->getLanguageId());
            } else {
                $strWhere = $objGenTable->getAdapter()->quoteInto($this->getDbIdFieldForType($strType) . ' = ?', $arrTypeProperties['Id']);
            }
            $objGenTable->delete($strWhere);

            /**
             * update the file instances table
             */
            foreach ($this->setup->MultiFields() as $strField => $objField) {

                $intFieldId = $objField->id;

                if (is_array($objField->getValue()) && count($objField->getValue()) > 0) {
                    foreach ($objField->getValue() as $intRelationId) {
                        if ($intRelationId != '') {
                            if (isset($arrTypeProperties['Version'])) {
                                $arrFileData = array(
                                    $strType . 'Id' => $arrTypeProperties['Id'],
                                    'version'       => $arrTypeProperties['Version'],
                                    'idLanguages'   => $this->setup->getLanguageId(),
                                    'idRelation'    => $intRelationId,
                                    //'value'     => '', TODO ::  load value, if copyValue is true
                                    'idFields'      => $intFieldId
                                );
                            } else {
                                $arrFileData = array(
                                    $this->getDbIdFieldForType($strType) => $arrTypeProperties['Id'],
                                    'idRelation'                         => $intRelationId,
                                    //'value'                              => '', TODO ::  load value, if copyValue is true
                                    'idFields'                           => $intFieldId
                                );
                            }

                            $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-InstanceMultiFields')->insert($arrFileData);
                        }
                    }
                }
            }
        }
    }


    /**
     * updateInstanceData
     *
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    final protected function updateInstanceData($strType, $arrTypeProperties)
    {

        if (count($this->setup->InstanceFields()) > 0) {

            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

            $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-Instances');

            $arrInstanceData = array(
                'idUsers' => $intUserId,
                'changed' => date('Y-m-d H:i:s')
            );

            /**
             * for each instance field, add to instance data array
             */
            foreach ($this->setup->InstanceFields() as $strField => $objField) {
                if (is_array($objField->getValue())) {
                    $arrInstanceData[$strField] = json_encode($objField->getValue());
                } else {
                    $arrInstanceData[$strField] = $objField->getValue();
                }
            }

            if (isset($arrTypeProperties['Version'])) {
                $strWhere = $objGenTable->getAdapter()->quoteInto($strType . 'Id = ?', $arrTypeProperties['Id']);
                $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND version = ?', $arrTypeProperties['Version']);
                $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->setup->getLanguageId());
            } else {
                $strWhere = $objGenTable->getAdapter()->quoteInto($this->getDbIdFieldForType($strType) . ' = ?', $arrTypeProperties['Id']);
            }

            $intNumOfEffectedRows = $objGenTable->update($arrInstanceData, $strWhere);

            if ($intNumOfEffectedRows == 0) {
                $this->insertInstanceData($strType, $arrTypeProperties);
            }
        }
    }

    /**
     * updateMultiplyRegionData
     *
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    final protected function updateMultiplyRegionData($strType, $arrTypeProperties)
    {
        try {
            if (count($this->setup->MultiplyRegionIds()) > 0) {
                /**
                 * start transaction
                 */
                $this->core->dbh->beginTransaction();
                try {
                    /**
                     * for each multiply region, insert data
                     */
                    foreach ($this->setup->MultiplyRegionIds() as $intRegionId) {
                        $objRegion = $this->setup->getRegion($intRegionId);

                        $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-Region' . $objRegion->getRegionId() . '-Instances');

                        if (isset($arrTypeProperties['Version'])) {
                            $strWhere = $objGenTable->getAdapter()->quoteInto($strType . 'Id = ?', $arrTypeProperties['Id']);
                            $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND version = ?', $arrTypeProperties['Version']);
                            $strWhere .= $objGenTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->setup->getLanguageId());
                        } else {
                            $strWhere = $objGenTable->getAdapter()->quoteInto($this->getDbIdFieldForType($strType) . ' = ?', $arrTypeProperties['Id']);
                        }
                        $objGenTable->delete($strWhere);

                        if ($objRegion instanceof GenericElementRegion) {
                            $intRegionPosition = 0;
                            foreach ($objRegion->RegionInstanceIds() as $intRegionInstanceId) {
                                $intRegionPosition++;
                                $arrTypeProperties['regionUniqueId'] = $objRegion->getRegionUniqueId($intRegionPosition);
                                $this->insertMultiplyRegionInstanceData($objRegion, $intRegionInstanceId, $intRegionPosition, $strType, $arrTypeProperties);
                            }
                        }
                    }

                    /**
                     * commit transaction
                     */
                    $this->core->dbh->commit();
                } catch (Exception $exc) {
                    /**
                     * roll back
                     */
                    $this->core->dbh->rollBack();
                    $this->core->logger->err($exc);
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * insertMultiplyRegionInstanceData
     *
     * @param GenericElementRegion $objRegion
     * @param integer $intRegionInstanceId
     * @param integer $intRegionPosition
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    final protected function insertMultiplyRegionInstanceData(GenericElementRegion $objRegion, $intRegionInstanceId, $intRegionPosition, $strType, $arrTypeProperties)
    {
        try {
            if (isset($arrTypeProperties['Version'])) {
                $arrInstanceData = array(
                    $strType . 'Id' => $arrTypeProperties['Id'],
                    'version'       => $arrTypeProperties['Version'],
                    'idLanguages'   => $this->setup->getLanguageId()
                );
            } else {
                $arrInstanceData = array(
                    $this->getDbIdFieldForType($strType) => $arrTypeProperties['Id']
                );
            }

            $arrInstanceData = array_merge(
                $arrInstanceData,
                array(
                    'sortPosition' => $intRegionPosition
                )
            );

            if ($objRegion->getRegionTypeId() == $this->core->sysConfig->region_types->unique) {
                $uniqueId = $arrTypeProperties['regionUniqueId'];
                if ($uniqueId == null) {
                    $uniqueId = uniqid();
                    $objRegion->addRegionUniqueId($intRegionInstanceId, $uniqueId);
                }
                $arrInstanceData = array_merge(
                    $arrInstanceData,
                    array(
                        'uniqueId' => $uniqueId
                    )
                );
            }


            /**
             * for each instance field, add to instance data array
             */
            foreach ($objRegion->InstanceFieldNames() as $strFieldName) {
                if (is_array($objRegion->getField($strFieldName)->getInstanceValue($intRegionInstanceId))) {
                    $arrInstanceData[$strFieldName] = json_encode($objRegion->getField($strFieldName)->getInstanceValue($intRegionInstanceId));
                } else {
                    $arrInstanceData[$strFieldName] = $objRegion->getField($strFieldName)->getInstanceValue($intRegionInstanceId);
                }
            }

            $idRegionInstance = $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-Region' . $objRegion->getRegionId() . '-Instances')->insert($arrInstanceData);

            if (count($objRegion->FileFieldNames()) > 0) {

                /**
                 * insert into the file instances table
                 */
                foreach ($objRegion->FileFieldNames() as $strFieldName) {
                    $objField = $objRegion->getField($strFieldName);

                    $intFieldId = $objField->id;

                    $strTmpFileIds = trim($objField->getInstanceValue($intRegionInstanceId), '[]');
                    $strDisplayOption = $objField->getInstanceProperty($intRegionInstanceId, 'display_option');

                    $arrFileIds = array();
                    $arrFileIds = explode('][', $strTmpFileIds);

                    if (count($arrFileIds) > 0) {
                        foreach ($arrFileIds as $intFileId) {
                            if ($intFileId != '') {
                                if (isset($arrTypeProperties['Version'])) {
                                    $arrFileData = array(
                                        $strType . 'Id' => $arrTypeProperties['Id'],
                                        'version'       => $arrTypeProperties['Version'],
                                        'idLanguages'   => $this->setup->getLanguageId()
                                    );
                                } else {
                                    $arrFileData = array(
                                        $this->getDbIdFieldForType($strType) => $arrTypeProperties['Id']
                                    );
                                }

                                $arrFileData = array_merge(
                                    $arrFileData,
                                    array(
                                        'idRegionInstances' => $idRegionInstance,
                                        'idFiles'           => $intFileId,
                                        'displayOption'     => $strDisplayOption,
                                        'idFields'          => $intFieldId
                                    )
                                );

                                $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-Region' . $objRegion->getRegionId() . '-InstanceFiles')->insert($arrFileData);
                            }
                        }
                    }
                }
            }

            if (count($objRegion->FileFilterFieldNames()) > 0) {
                /**
                 * insert into the file filter instances table
                 */
                foreach ($objRegion->FileFilterFieldNames() as $strFieldName) {
                    $objField = $objRegion->getField($strFieldName);

                    $intFieldId = $objField->id;

                    $objFilters = $objField->getInstanceValue($intRegionInstanceId);

                    if (isset($objFilters->filters)) {
                        foreach ($objFilters->filters as $objFilter) {
                            if (!is_array($objFilter->referenceIds)) {
                                $objFilter->referenceIds = array($objFilter->referenceIds);
                            }

                            foreach ($objFilter->referenceIds as $intReferenceId) {
                                if (is_numeric($intReferenceId) && $intReferenceId > 0) {

                                    if (isset($arrTypeProperties['Version'])) {
                                        $arrFileFilterData = array(
                                            $strType . 'Id' => $arrTypeProperties['Id'],
                                            'version'       => $arrTypeProperties['Version'],
                                            'idLanguages'   => $this->setup->getLanguageId()
                                        );
                                    } else {
                                        $arrFileFilterData = array(
                                            $this->getDbIdFieldForType($strType) => $arrTypeProperties['Id']
                                        );
                                    }

                                    $arrFileFilterData = array_merge(
                                        $arrFileFilterData,
                                        array(
                                            'idRegionInstances' => $idRegionInstance,
                                            'idFilterTypes'     => $objFilter->typeId,
                                            'referenceId'       => $intReferenceId,
                                            'idFields'          => $intFieldId
                                        )
                                    );

                                    $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-Region' . $objRegion->getRegionId() . '-InstanceFileFilters')->insert($arrFileFilterData);
                                }
                            }
                        }
                    }
                }
            }

            if (count($objRegion->MultiFieldNames()) > 0) {
                /**
                 * insert into the multi fields instances table
                 */
                foreach ($objRegion->MultiFieldNames() as $strFieldName) {
                    $objField = $objRegion->getField($strFieldName);

                    $intFieldId = $objField->id;

                    if (is_array($objField->getInstanceValue($intRegionInstanceId)) && count($objField->getInstanceValue($intRegionInstanceId)) > 0) {
                        foreach ($objField->getInstanceValue($intRegionInstanceId) as $intRelationId) {
                            if ($intRelationId != '') {

                                if (isset($arrTypeProperties['Version'])) {
                                    $arrFileData = array(
                                        $strType . 'Id' => $arrTypeProperties['Id'],
                                        'version'       => $arrTypeProperties['Version'],
                                        'idLanguages'   => $this->setup->getLanguageId()
                                    );
                                } else {
                                    $arrFileData = array(
                                        $this->getDbIdFieldForType($strType) => $arrTypeProperties['Id']
                                    );
                                }

                                $arrFileData = array_merge(
                                    $arrFileData,
                                    array(
                                        'idRegionInstances' => $idRegionInstance,
                                        'idRelation'        => $intRelationId,
                                        //'value'            => '', TODO ::  load value, if copyValue is true
                                        'idFields'          => $intFieldId
                                    )
                                );

                                $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-Region' . $objRegion->getRegionId() . '-InstanceMultiFields')->insert($arrFileData);
                            }
                        }
                    }
                }
            }

            if (count($objRegion->SpecialFieldNames()) > 0) {
                foreach ($objRegion->SpecialFieldNames() as $strFieldName) {
                    $objField = $objRegion->getField($strFieldName);

                    $intFieldId = $objField->id;

                    $objField->setGenericSetup($this->Setup());
                    $objField->saveInstanceData($strType, $arrTypeProperties['Id'], $objRegion, $idRegionInstance, $intRegionInstanceId, $arrTypeProperties['Version']);
                }
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * loadCoreData
     *
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    final protected function loadGenericData($strType, $arrTypeProperties)
    {

        /**
         * laod all generic data
         */
        $this->loadCoreData($strType, $arrTypeProperties);
        $this->loadFileData($strType, $arrTypeProperties);
        $this->loadMultiFieldData($strType, $arrTypeProperties);
        $this->loadInstanceData($strType, $arrTypeProperties);
        $this->loadMultiplyRegionData($strType, $arrTypeProperties);

    }

    /**
     * loadCoreData
     *
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    final protected function loadCoreData($strType, $arrTypeProperties)
    {
        try {
            /**
             * generic form core fields
             */
            if (count($this->setup->CoreFields()) > 0) {
                /**
                 * for each core field, try to select the secondary table
                 */
                foreach ($this->setup->CoreFields() as $strField => $objField) {

                    if ($objField->getProperty('type') === 'media') {
                        $objGenTable = $this->getModelGenericData()->getGenericTable($strType . 'Files');
                        $objSelect = $objGenTable->select();
                        $objSelect->setIntegrityCheck(false);

                        $objSelect->from($strType . 'Files', array('idFiles', 'sortPosition', 'displayOption'));
                        $objSelect->join('fields', 'fields.id = ' . $strType . 'Files.idFields', array('name'));
                        $objSelect->where($strType . 'Id = ?', $arrTypeProperties['Id']);
                        $objSelect->where('version = ?', $arrTypeProperties['Version']);
                        $objSelect->where('idLanguages = ?', $this->Setup()->getLanguageId());
                        $objSelect->where('idFields = ?', $objField->id);
                        $objSelect->order(array('sortPosition ASC'));

                        $arrGenFormsData = $objGenTable->fetchAll($objSelect)->toArray();
                        if (count($arrGenFormsData) > 0) {
                            $objField->blnHasLoadedData = true;
                            foreach ($arrGenFormsData as $arrGenRowFormsData) {
                                if ($this->setup->getField($arrGenRowFormsData['name']) !== null) {
                                    $strFileIds = $this->setup->getField($arrGenRowFormsData['name'])->getValue() . '[' . $arrGenRowFormsData['idFiles'] . ']';
                                    $this->setup->getField($arrGenRowFormsData['name'])->setValue($strFileIds);
                                }
                            }
                        }
                    } else {
                        $objGenTable = $this->getModelGenericData()->getGenericTable($strType . str_replace('_', '', ((substr($strField, strlen($strField) - 1) == 'y') ? ucfirst(rtrim($strField, 'y')) . 'ies' : ucfirst($strField) . 's')));
                        $objSelect = $objGenTable->select();

                        $objSelect->from($objGenTable->info(Zend_Db_Table_Abstract::NAME), array($strField));
                        $objSelect->where($strType . 'Id = ?', $arrTypeProperties['Id']);
                        $objSelect->where('version = ?', $arrTypeProperties['Version']);
                        $objSelect->where('idLanguages = ?', $this->Setup()->getLanguageId());

                        $arrGenFormsData = $objGenTable->fetchAll($objSelect)->toArray();

                        if (count($arrGenFormsData) > 0) {
                            $objField->blnHasLoadedData = true;
                            if (count($arrGenFormsData) > 1) {
                                $arrFieldData = array();
                                foreach ($arrGenFormsData as $arrRowGenFormData) {
                                    foreach ($arrRowGenFormData as $column => $value) {
                                        array_push($arrFieldData, $value);
                                    }
                                }
                                if ($column == $strField) {
                                    $objField->setValue($arrFieldData);
                                } else {
                                    $objField->$column = $arrFieldData;
                                }
                            } else {
                                foreach ($arrGenFormsData as $arrRowGenFormData) {
                                    foreach ($arrRowGenFormData as $column => $value) {
                                        if ($column == $strField) {
                                            $objField->setValue($value);
                                        } else {
                                            $objField->$column = $value;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * loadFileData
     *
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    final protected function loadFileData($strType, $arrTypeProperties)
    {
        try {
            /**
             * generic form file fields
             */
            if (count($this->setup->FileFields()) > 0) {

                $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-InstanceFiles');
                $strTableName = $objGenTable->info(Zend_Db_Table_Abstract::NAME);

                $objSelect = $objGenTable->select();
                $objSelect->setIntegrityCheck(false);

                $objSelect->from($objGenTable->info(Zend_Db_Table_Abstract::NAME), array('idFiles', 'sortPosition', 'displayOption'));
                $objSelect->join('fields', 'fields.id = `' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.idFields', array('name'));
                if (isset($arrTypeProperties['Version'])) {
                    $objSelect->where($strType . 'Id = ?', $arrTypeProperties['Id']);
                    $objSelect->where('version = ?', $arrTypeProperties['Version']);
                    $objSelect->where('idLanguages = ?', $this->Setup()->getLanguageId());
                } else {
                    $objSelect->where($this->getDbIdFieldForType($strType) . ' = ?', $arrTypeProperties['Id']);
                }
                $objSelect->order(array('sortPosition ASC'));

                $arrGenFormsData = $objGenTable->fetchAll($objSelect)->toArray();

                if (count($arrGenFormsData) > 0) {
                    $this->blnHasLoadedFileData = true;
                    foreach ($arrGenFormsData as $arrGenRowFormsData) {
                        if ($this->setup->getFileField($arrGenRowFormsData['name']) !== null) {
                            $strFileIds = $this->setup->getFileField($arrGenRowFormsData['name'])->getValue() . '[' . $arrGenRowFormsData['idFiles'] . ']';
                            $this->setup->getFileField($arrGenRowFormsData['name'])->setValue($strFileIds);
                            if (array_key_exists('displayOption', $arrGenRowFormsData)) $this->setup->getFileField($arrGenRowFormsData['name'])->setProperty('display_option', $arrGenRowFormsData['displayOption']);
                        }
                    }
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * loadMultiFieldData
     *
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    final protected function loadMultiFieldData($strType, $arrTypeProperties)
    {
        try {
            /**
             * generic form multi fields
             */
            if (count($this->setup->MultiFields()) > 0) {

                $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-InstanceMultiFields');
                $strTableName = $objGenTable->info(Zend_Db_Table_Abstract::NAME);

                $objSelect = $objGenTable->select();
                $objSelect->setIntegrityCheck(false);

                $objSelect->from($objGenTable->info(Zend_Db_Table_Abstract::NAME), array('idRelation', 'value'));
                $objSelect->join('fields', 'fields.id = `' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.idFields', array('name'));
                if (isset($arrTypeProperties['Version'])) {
                    $objSelect->where($strType . 'Id = ?', $arrTypeProperties['Id']);
                    $objSelect->where('version = ?', $arrTypeProperties['Version']);
                    $objSelect->where('idLanguages = ?', $this->Setup()->getLanguageId());
                } else {
                    $objSelect->where($this->getDbIdFieldForType($strType) . ' = ?', $arrTypeProperties['Id']);
                }
                $arrGenFormsData = $objGenTable->fetchAll($objSelect);

                if (count($arrGenFormsData) > 0) {
                    $this->blnHasLoadedMultiFieldData = true;
                    foreach ($arrGenFormsData as $arrGenRowFormsData) {
                        $field = $this->setup->getMultiField($arrGenRowFormsData->name);
                        if ($field) {
                            $arrTmpRelationIds = $field->getValue();
                            if (is_array($arrTmpRelationIds)) {
                                array_push($arrTmpRelationIds, $arrGenRowFormsData->idRelation);
                            } else {
                                $arrTmpRelationIds = array($arrGenRowFormsData->idRelation);
                            }
                            $field->setValue($arrTmpRelationIds);
                        }
                    }
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * loadInstanceData
     *
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    final protected function loadInstanceData($strType, $arrTypeProperties)
    {
        try {
            /**
             * generic form instance fields
             */
            if (count($this->setup->InstanceFields()) > 0) {
                $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-Instances');
                $objSelect = $objGenTable->select();

                $arrSelectFields = array();

                /**
                 * for each instance field, add to select array data array
                 */
                foreach ($this->setup->InstanceFields() as $strField => $objField) {
                    $arrSelectFields[] = $strField;
                }

                $objSelect->from($objGenTable->info(Zend_Db_Table_Abstract::NAME), $arrSelectFields);
                if (isset($arrTypeProperties['Version'])) {
                    $objSelect->where($strType . 'Id = ?', $arrTypeProperties['Id']);
                    $objSelect->where('version = ?', $arrTypeProperties['Version']);
                    $objSelect->where('idLanguages = ?', $this->Setup()->getLanguageId());
                } else {
                    $objSelect->where($this->getDbIdFieldForType($strType) . ' = ?', $arrTypeProperties['Id']);
                }

                $arrGenFormsData = $objGenTable->fetchAll($objSelect)->toArray();

                if (count($arrGenFormsData) > 0) {
                    $this->blnHasLoadedInstanceData = true;
                    foreach ($arrGenFormsData as $arrRowGenFormData) {
                        foreach ($arrRowGenFormData as $column => $value) {
                            if (is_array(json_decode($value))) {
                                $this->setup->getInstanceField($column)->setValue(json_decode($value));
                            } else {
                                $this->setup->getInstanceField($column)->setValue($value);
                            }
                        }
                    }
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * loadMultiplyRegionData
     *
     * @param string $strType
     * @param array $arrTypeProperties
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    final protected function loadMultiplyRegionData($strType, $arrTypeProperties)
    {
        try {
            /**
             * if the generic form, has multiply regions
             */
            if (count($this->setup->MultiplyRegionIds()) > 0) {

                /**
                 * for each multiply region, load region data
                 */
                foreach ($this->setup->MultiplyRegionIds() as $intRegionId) {
                    $objRegion = $this->setup->getRegion($intRegionId);

                    $arrRegionInstanceIds = array();
                    $intRegionInstanceCounter = 0;

                    $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-Region' . $objRegion->getRegionId() . '-Instances');
                    $objSelect = $objGenTable->select();

                    $arrSelectFields = array('id');
                    /**
                     * for each instance field, add to select array data array
                     */
                    foreach ($objRegion->InstanceFieldNames() as $strField) {
                        $arrSelectFields[] = $strField;
                    }

                    if ($objRegion->getRegionTypeId() == $this->core->sysConfig->region_types->unique) {
                        $arrSelectFields[] = 'uniqueId';
                    }

                    $objSelect->from($objGenTable->info(Zend_Db_Table_Abstract::NAME), $arrSelectFields);
                    if (isset($arrTypeProperties['Version'])) {
                        $objSelect->where($strType . 'Id = ?', $arrTypeProperties['Id']);
                        $objSelect->where('version = ?', $arrTypeProperties['Version']);
                        $objSelect->where('idLanguages = ?', $this->Setup()->getLanguageId());
                    } else {
                        $objSelect->where($this->getDbIdFieldForType($strType) . ' = ?', $arrTypeProperties['Id']);
                    }
                    $objSelect->order(array('sortPosition'));

                    $arrGenFormsData = $objGenTable->fetchAll($objSelect)->toArray();

                    if (count($arrGenFormsData) > 0) {
                        $this->blnHasLoadedMultiplyRegionData = true;

                        foreach ($arrGenFormsData as $arrRowGenFormData) {
                            $intRegionInstanceCounter++;
                            $intRegionInstanceId = $arrRowGenFormData['id'];
                            $arrRegionInstanceIds[$intRegionInstanceCounter] = $intRegionInstanceId;

                            $objRegion->addRegionInstanceId($intRegionInstanceCounter);
                            foreach ($arrRowGenFormData as $column => $value) {
                                if ($column == 'uniqueId') {
                                    $objRegion->addRegionUniqueId($intRegionInstanceCounter, $value);
                                } else if ($column != 'id') {
                                    if (is_array(json_decode($value))) {
                                        $objRegion->getField($column)->setInstanceValue($intRegionInstanceCounter, json_decode($value));
                                    } else {
                                        $objRegion->getField($column)->setInstanceValue($intRegionInstanceCounter, $value);
                                    }
                                }
                            }
                        }
                    }
                    /**
                     * generic multipy region file fields
                     */
                    if (count($objRegion->FileFieldNames()) > 0) {

                        $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-Region' . $objRegion->getRegionId() . '-InstanceFiles');
                        $strTableName = $objGenTable->info(Zend_Db_Table_Abstract::NAME);

                        $objSelect = $objGenTable->select();
                        $objSelect->setIntegrityCheck(false);

                        $objSelect->from($objGenTable->info(Zend_Db_Table_Abstract::NAME), array('idFiles', 'idRegionInstances', 'displayOption'));
                        $objSelect->join('fields', 'fields.id = `' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.idFields', array('name'));
                        if (isset($arrTypeProperties['Version'])) {
                            $objSelect->where($strType . 'Id = ?', $arrTypeProperties['Id']);
                            $objSelect->where('version = ?', $arrTypeProperties['Version']);
                            $objSelect->where('idLanguages = ?', $this->Setup()->getLanguageId());
                        } else {
                            $objSelect->where($this->getDbIdFieldForType($strType) . ' = ?', $arrTypeProperties['Id']);
                        }

                        $arrGenFormsData = $objGenTable->fetchAll($objSelect)->toArray();

                        if (count($arrGenFormsData) > 0) {
                            $this->blnHasLoadedMultiplyRegionData = true;

                            foreach ($arrGenFormsData as $arrGenRowFormsData) {
                                $intRegionInstanceId = $arrGenRowFormsData['idRegionInstances'];
                                $intRegionPos = array_search($intRegionInstanceId, $arrRegionInstanceIds);
                                if ($intRegionPos !== false) {
                                    if (is_object($objRegion->getField($arrGenRowFormsData['name']))) {
                                        $strFileIds = $objRegion->getField($arrGenRowFormsData['name'])->getInstanceValue($intRegionPos) . '[' . $arrGenRowFormsData['idFiles'] . ']';
                                        $objRegion->getField($arrGenRowFormsData['name'])->setInstanceValue($intRegionPos, $strFileIds);
                                        $objRegion->getField($arrGenRowFormsData['name'])->setInstanceProperty($intRegionPos, 'display_option', $arrGenRowFormsData['displayOption']);
                                    }
                                }
                            }
                        }
                    }

                    /**
                     * generic multipy region file filter fields
                     */
                    if (count($objRegion->FileFilterFieldNames()) > 0) {
                        $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-Region' . $objRegion->getRegionId() . '-InstanceFileFilters');
                        $strTableName = $objGenTable->info(Zend_Db_Table_Abstract::NAME);

                        $objSelect = $objGenTable->select();
                        $objSelect->setIntegrityCheck(false);

                        $objSelect->from($objGenTable->info(Zend_Db_Table_Abstract::NAME), array('idFilterTypes', 'referenceId', 'idRegionInstances'));
                        $objSelect->join('fields', 'fields.id = `' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.idFields', array('name'));
                        if (isset($arrTypeProperties['Version'])) {
                            $objSelect->where($strType . 'Id = ?', $arrTypeProperties['Id']);
                            $objSelect->where('version = ?', $arrTypeProperties['Version']);
                            $objSelect->where('idLanguages = ?', $this->Setup()->getLanguageId());
                        } else {
                            $objSelect->where($this->getDbIdFieldForType($strType) . ' = ?', $arrTypeProperties['Id']);
                        }

                        $arrGenFormsData = $objGenTable->fetchAll($objSelect)->toArray();

                        if (count($arrGenFormsData) > 0) {
                            $this->blnHasLoadedMultiplyRegionData = true;
                            $arrFilteredFileData = array();

                            foreach ($arrGenFormsData as $arrGenRowFormsData) {
                                $intRegionInstanceId = $arrGenRowFormsData['idRegionInstances'];
                                $intRegionPos = array_search($intRegionInstanceId, $arrRegionInstanceIds);
                                if ($intRegionPos !== false) {
                                    if (array_key_exists($arrGenRowFormsData['name'] . '_' . $intRegionPos, $arrFilteredFileData)) {
                                        $objFilters = $arrFilteredFileData[$arrGenRowFormsData['name'] . '_' . $intRegionPos];
                                    } else {
                                        $objFilters = new stdClass();
                                        $objFilters->filters = array();
                                    }

                                    if (array_key_exists('ft' . $arrGenRowFormsData['idFilterTypes'], $objFilters->filters)) {
                                        $objFilter = $objFilters->filters['ft' . $arrGenRowFormsData['idFilterTypes']];
                                    } else {
                                        $objFilter = new stdClass();
                                        $objFilter->typeId = $arrGenRowFormsData['idFilterTypes'];
                                        $objFilter->referenceIds = array();
                                    }

                                    $objFilter->referenceIds[] = $arrGenRowFormsData['referenceId'];
                                    $objFilters->filters['ft' . $arrGenRowFormsData['idFilterTypes']] = $objFilter;

                                    $arrTmp = array($arrGenRowFormsData['name'] . '_' . $intRegionPos => $objFilters);
                                    $arrFilteredFileData += $arrTmp;
                                }
                            }
                            foreach ($arrFilteredFileData as $strIndex => $objFilters) {
                                $arrIndex = explode('_', $strIndex);
                                $objRegion->getField($arrIndex[0])->setInstanceValue($arrIndex[1], $objFilters);
                            }
                        }
                    }

                    /**
                     * generic multipy region multi fields
                     */
                    if (count($objRegion->MultiFieldNames()) > 0) {

                        $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $this->setup->getFormId() . '-' . $this->setup->getFormVersion() . '-Region' . $objRegion->getRegionId() . '-InstanceMultiFields');
                        $strTableName = $objGenTable->info(Zend_Db_Table_Abstract::NAME);

                        $objSelect = $objGenTable->select();
                        $objSelect->setIntegrityCheck(false);

                        $objSelect->from($objGenTable->info(Zend_Db_Table_Abstract::NAME), array('idRelation', 'value', 'idRegionInstances'));
                        $objSelect->join('fields', 'fields.id = `' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.idFields', array('name'));
                        if (isset($arrTypeProperties['Version'])) {
                            $objSelect->where($strType . 'Id = ?', $arrTypeProperties['Id']);
                            $objSelect->where('version = ?', $arrTypeProperties['Version']);
                            $objSelect->where('idLanguages = ?', $this->Setup()->getLanguageId());
                        } else {
                            $objSelect->where($this->getDbIdFieldForType($strType) . ' = ?', $arrTypeProperties['Id']);
                        }

                        $arrGenFormsData = $objGenTable->fetchAll($objSelect);

                        if (count($arrGenFormsData) > 0) {
                            $this->blnHasLoadedMultiplyRegionData = true;

                            foreach ($arrGenFormsData as $arrGenRowFormsData) {
                                $intRegionInstanceId = $arrGenRowFormsData->idRegionInstances;
                                $intRegionPos = array_search($intRegionInstanceId, $arrRegionInstanceIds);

                                $arrTmpRelationIds = $objRegion->getField($arrGenRowFormsData->name)->getInstanceValue($intRegionPos);
                                if (is_array($arrTmpRelationIds)) {
                                    array_push($arrTmpRelationIds, $arrGenRowFormsData->idRelation);
                                } else {
                                    $arrTmpRelationIds = array($arrGenRowFormsData->idRelation);
                                }
                                $objRegion->getField($arrGenRowFormsData->name)->setInstanceValue($intRegionPos, $arrTmpRelationIds);
                            }
                        }
                    }

                    /**
                     * generic multiply region special fields
                     */
                    if (count($objRegion->SpecialFieldNames() > 0)) {
                        foreach ($objRegion->SpecialFieldNames() as $strFieldName) {
                            $objField = $objRegion->getField($strFieldName);
                            $objField->setGenericSetup($this->Setup());
                            $arrInstanceData = $objField->loadInstanceData($strType, $arrTypeProperties['Id'], $objRegion, $arrTypeProperties['Version']);
                            if (count($arrInstanceData) > 0) {
                                foreach ($arrInstanceData as $intInstanceId => $arrInstanceDataRow) {
                                    $objRegion->getField($arrInstanceDataRow['name'])->setInstanceValue($intInstanceId, $arrInstanceDataRow['value']);
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * addToIndex
     *
     * @param $strKey
     * @param $type
     * @param $languageId
     * @param null $objParentPageContainer
     * @param array $arrParentFolderIds
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    final protected function addToIndex($strKey, $type, $languageId, $objParentPageContainer = null, $arrParentFolderIds = array())
    {
        try {
            $this->core->logger->debug('massiveart->generic->data->types->GenericDataTypeAbstract->addToIndex(' . $strKey . ', ' . $type . ', ' . $languageId . ')');

            $data = array();

            if ($this->setup->getLanguageFallbackId() > 0 && $this->setup->getLanguageFallbackId() != $this->setup->getLanguageId()) {
                $data['languageId'] = array('value' => $this->setup->getLanguageFallbackId(), 'params' => array('searchFieldTypeId' => \Sulu\Search\Search::FIELD_TYPE_KEYWORD));
            } else {
                $data['languageId'] = array('value' => $this->setup->getLanguageId(), 'params' => array('searchFieldTypeId' => \Sulu\Search\Search::FIELD_TYPE_KEYWORD));
            }

            $data['rootLevelId'] = array('value' => $this->setup->getRootLevelId(), 'params' => array('searchFieldTypeId' => \Sulu\Search\Search::FIELD_TYPE_KEYWORD));
            $data['templateId'] = array('value' => $this->setup->getTemplateId(), 'params' => array('searchFieldTypeId' => \Sulu\Search\Search::FIELD_TYPE_KEYWORD));
            $data['date'] = array('value' => $this->setup->getPublishDate('d.m.Y'), 'params' => array('searchFieldTypeId' => \Sulu\Search\Search::FIELD_TYPE_UNINDEXED));
            $data['elementTypeId'] = array('value' => $this->setup->getElementTypeId(), 'params' => array('searchFieldTypeId' => \Sulu\Search\Search::FIELD_TYPE_UNINDEXED));
            $data['segmentId'] = array('value' => $this->setup->getSegmentId(), 'params' => array('searchFieldTypeId' => \Sulu\Search\Search::FIELD_TYPE_UNINDEXED));

            if ($objParentPageContainer !== null && $objParentPageContainer instanceof PageContainer) {
                if (count($objParentPageContainer->getEntries()) > 0) {
                    $data['parentPages'] = array('value' => base64_encode(serialize($objParentPageContainer->getEntries())), 'params' => array('searchFieldTypeId' => \Sulu\Search\Search::FIELD_TYPE_UNINDEXED));
                    $data['rootLevelId'] = array('value' => end($objParentPageContainer->getEntries())->rootLevelId, 'params' => array('searchFieldTypeId' => \Sulu\Search\Search::FIELD_TYPE_KEYWORD));
                }
            }

            if (is_array($arrParentFolderIds) && count($arrParentFolderIds) > 0) {
                $data['parentFolderId'] = array('value' => $arrParentFolderIds[0], 'params' => array('searchFieldTypeId' => \Sulu\Search\Search::FIELD_TYPE_UNINDEXED));
                $data['parentFolderIds'] = array('value' => implode(',', $arrParentFolderIds), 'params' => array('searchFieldTypeId' => \Sulu\Search\Search::FIELD_TYPE_UNINDEXED));
            }

            // index fields
            foreach ($this->setup->FieldNames() as $strField => $intFieldType) {
                $objField = $this->setup->getField($strField);
                if (is_object($objField) && $objField->idSearchFieldTypes != \Sulu\Search\Search::FIELD_TYPE_NONE) {
                    $data = $this->indexFieldNow($objField, $strField, $intFieldType, $objField->getValue(), $data);
                }
            }

            // index multiply region fields
            foreach ($this->setup->MultiplyRegionIds() as $intRegionId) {
                $objRegion = $this->setup->getRegion($intRegionId);

                if ($objRegion instanceof GenericElementRegion) {
                    $intRegionPosition = 0;
                    foreach ($objRegion->RegionInstanceIds() as $intRegionInstanceId) {
                        $intRegionPosition++;
                        foreach ($objRegion->FieldNames() as $strField => $intFieldType) {
                            $objField = $objRegion->getField($strField);
                            if (is_object($objField) && $objField->idSearchFieldTypes != \Sulu\Search\Search::FIELD_TYPE_NONE) {
                                $data = $this->indexFieldNow($objField, $objField->name . '_' . $intRegionPosition, $intFieldType, $objField->getInstanceValue($intRegionInstanceId), $data);
                            }
                        }
                    }
                }
            }

            $search = new \Sulu\Search\Search($this->core->sysConfig->search->toArray(), $type, $languageId);
            $search->getIndex()->add($strKey, $data);

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * indexFieldNow
     *
     * @param GenericElementField $objField
     * @param string $strField
     * @param integer $intFieldType
     * @param string|array|object $mixedFieldValue
     * @param array $data
     *
     * @return string
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    final protected function indexFieldNow($objField, $strField, $intFieldType, $mixedFieldValue, array $data)
    {
        try {

            $this->core->logger->debug($strField);

            $strValue = '';
            $strValueIds = '';
            $blnReturnValue = false;
            if ($objField->typeId == GenericSetup::FIELD_TYPE_ID_TAG) {
                $mixedValue = $mixedFieldValue;
                if (is_object($mixedValue) || is_array($mixedValue)) {
                    foreach ($mixedValue as $objTag) {
                        $strValue .= $objTag->title . ', ';
                        $strValueIds .= '[' . $objTag->id . ']';
                    }
                    $strValue = rtrim($strValue, ', ');
                }
            } elseif (!is_object($mixedFieldValue) && $objField->sqlSelect != '') {
                $sqlSelect = $objField->sqlSelect;

                $arrIds = array();

                if (is_array($mixedFieldValue)) {
                    $arrIds = $mixedFieldValue;
                } else if ($mixedFieldValue != '') {
                    if (strpos($mixedFieldValue, '[') !== false) {
                        $mixedFieldValue = trim($mixedFieldValue, '[]');
                        $arrIds = explode('][', $mixedFieldValue);
                    } else {
                        $arrIds = array($mixedFieldValue);
                    }
                }

                if (is_array($arrIds)) {
                    if (count($arrIds) > 0) {
                        $strReplaceWhere = '';
                        foreach ($arrIds as $strId) {
                            $strReplaceWhere .= $strId . ',';
                        }
                        $strReplaceWhere = trim($strReplaceWhere, ',');

                        $objReplacer = new Replacer();
                        $sqlSelect = $objReplacer->sqlReplacer($sqlSelect, $this->setup->getLanguageId(), $this->setup->getRootLevelId(), ' AND tbl.id IN (' . $strReplaceWhere . ')');
                        $objCategoriesData = $this->core->dbh->query($sqlSelect)->fetchAll(Zend_Db::FETCH_OBJ);

                        if (count($objCategoriesData) > 0) {
                            foreach ($objCategoriesData as $objCategories) {
                                $strValue .= $objCategories->title . ', ';
                                $strValueIds .= '[' . $objCategories->id . ']';
                            }
                            $strValue = rtrim($strValue, ', ');
                        }
                    }
                }
            } else {
                $strValue = html_entity_decode($mixedFieldValue, ENT_COMPAT, $this->core->sysConfig->encoding->default);
            }

            if (is_string($strValue) && $strValue != '') {

                if ($intFieldType == GenericSetup::FILE_FIELD || $intFieldType == GenericSetup::CORE_FIELD && $objField->getProperty('type') == 'media') {
                    $objFiles = $this->getModelFiles()->loadFilesById($strValue);
                    $arrValues = array();
                    if (count($objFiles) > 0) {
                        foreach ($objFiles as $objFile) {
                            $arrValues[] = array('path' => $objFile->path, 'filename' => $objFile->filename, 'version' => $objFile->version);
                        }
                    }
                    $strValueIds = $strValue;
                    $strValue = serialize($arrValues);
                }

                if ($strValueIds != '') {
                    if ($objField->idSearchFieldTypes == \Sulu\Search\Search::FIELD_TYPE_KEYWORD) {
                        $data[$strField . 'Ids'] = array('value' => $strValueIds, 'params' => array('searchFieldTypeId' => \Sulu\Search\Search::FIELD_TYPE_KEYWORD));
                    } else {
                        $data[$strField . 'Ids'] = array('value' => $strValueIds, 'params' => array('searchFieldTypeId' => \Sulu\Search\Search::FIELD_TYPE_UNINDEXED));
                    }
                }

                // decide to return value or not
                /*if ($objField->idSearchFieldTypes == \Sulu\Search\Search::FIELD_TYPE_KEYWORD || $objField->idSearchFieldTypes == \Sulu\Search\Search::FIELD_TYPE_TEXT || $objField->idSearchFieldTypes == \Sulu\Search\Search::FIELD_TYPE_SUMMARY_INDEXED || $objField->idSearchFieldTypes == \Sulu\Search\Search::FIELD_TYPE_UNSTORED)   {
                    $blnReturnValue = true;
                }*/

                $data[$strField] = array('value' => $strValue, 'params' => array('searchFieldTypeId' => $objField->idSearchFieldTypes));
            }

            //return $blnReturnValue ? $strValue : '';
            return $data;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * updateIndex
     *
     * @param $strKey
     * @param $type
     * @param $languageId
     * @param null $objParentPageContainer
     * @param array $arrParentFolderIds
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    final public function updateIndex($strKey, $type, $languageId, $objParentPageContainer = null, $arrParentFolderIds = array())
    {
        try {
            $this->removeFromIndex($strKey, $type, $languageId);

            $this->addToIndex($strKey, $type, $languageId, $objParentPageContainer, $arrParentFolderIds);

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * removeFromIndex
     *
     * @param $strKey
     * @param $type
     * @param $languageId
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    final protected function removeFromIndex($strKey, $type, $languageId)
    {
        try {
            $this->core->logger->debug('massiveart->generic->data->types->GenericDataTypeAbstract->removeFromIndex(' . $strKey . ', ' . $type . ', ' . $languageId . ')');

            $search = new \Sulu\Search\Search($this->core->sysConfig->search->toArray(), $type, $languageId);
            $search->getIndex()->delete($strKey);

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getDbIdFieldForType
     *
     * @param string $strType
     *
     * @return string
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    final protected function getDbIdFieldForType($strType)
    {
        if (!array_key_exists($strType, $this->arrDbIdFields)) {
            $this->arrDbIdFields[$strType] = 'id' . ((substr($strType, strlen($strType) - 1) == 'y') ? ucfirst(rtrim($strType, 'y')) . 'ies' : ucfirst($strType) . 's');
        }
        return $this->arrDbIdFields[$strType];
    }

    /**
     * getModelGenericData
     *
     * @return Model_GenericData
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelGenericData()
    {
        if (null === $this->objModelGenericData) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/GenericData.php';
            $this->objModelGenericData = new Model_GenericData();
        }

        return $this->objModelGenericData;
    }

    /**
     * getModelFiles
     *
     * @author Cornelius Hansjakob <cha@massiveart.com>
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
            $this->objModelFiles->setLanguageId($this->setup->getLanguageId());
        }

        return $this->objModelFiles;
    }

    /**
     * setGenericSetup
     *
     * @param GenericSetup $objGenericSetup
     *
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setGenericSetup(GenericSetup $objGenericSetup)
    {
        $this->setup = $objGenericSetup;
    }
}
