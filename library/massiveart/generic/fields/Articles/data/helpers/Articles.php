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
 * @package    library.massiveart.generic.fields.Articles.data.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * GenericDataHelper_Articles
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-08-10: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.fields.Articles.data.helpers
 * @subpackage GenericDataHelper_Articles
 */

require_once(dirname(__FILE__) . '/../../../../data/helpers/Abstract.php');

class GenericDataHelper_Articles extends GenericDataHelperAbstract
{

    /**
     * @var Model_Pages|Model_Products
     */
    private $objModel;

    /**
     * @var Model_GenericData
     */
    protected $objModelGenericData;

    private $strType;

    /**
     * save()
     * @param integer $intElementId
     * @param string $strType
     * @param string $strElementId
     * @param int $intVersion
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function save($intElementId, $strType, $strElementId = null, $intVersion = null)
    {
        try {
            $this->strType = $strType;

            $this->getModel();

            $this->objModel->deleteArticles($strElementId, $intVersion, $this->objElement->id);

            if ($this->objElement->getValue() != '') $this->objModel->addArticles($this->objElement->getValue(), $strElementId, $intVersion, $this->objElement->id);

            $this->load($intElementId, $strType, $strElementId, $intVersion);

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * load()
     * @param integer $intElementId
     * @param string $strType
     * @param string $strElementId
     * @param int $intVersion
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function load($intElementId, $strType, $strElementId = null, $intVersion = null)
    {
        try {
            $this->strType = $strType;

            $this->getModel();

            $objArticles = $this->objModel->loadArticles($strElementId, $intVersion, $this->objElement->id);

            if (count($objArticles) > 0) {
                $this->objElement->objArticles = $objArticles;

                $arrArticles = array();
                foreach ($objArticles as $objArticle) {
                    $arrArticles[] = array(
                        'size'           => $objArticle->size,
                        'price'          => $objArticle->price,
                        'discount'       => $objArticle->discount,
                        'weight'         => $objArticle->weight,
                        'article_number' => $objArticle->article_number
                    );
                }

                $this->objElement->setValue(json_encode($arrArticles));
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * saveInstanceData
     * @param string $strType
     * @param string $strElementId
     * @param GenericElementRegion $objRegion
     * @param number $idRegionInstance
     * @param number $intRegionInstanceId
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function saveInstanceData($strType, $strElementId, $objRegion, $idRegionInstance, $intRegionInstanceId, $intVersion)
    {
        try {

            $strGenForm = $this->objElement->Setup()->getFormId() . '-' . $this->objElement->Setup()->getFormVersion();
            $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $strGenForm . '-Region' . $objRegion->getRegionId() . '-InstanceArticles');

            // add instance values
            $arrValues = $this->objElement->getInstanceValue($intRegionInstanceId);

            if (is_array($arrValues)) {
                foreach ($arrValues as $objArticle) {
                    $arrFileData = array(
                        $strType . 'Id'         => $strElementId,
                        'version'               => $intVersion,
                        'idLanguages'           => $this->objElement->Setup()->getLanguageId(),
                        'idRegionInstances'     => $idRegionInstance,
                        'size'                  => $objArticle->size,
                        'price'                 => $objArticle->price,
                        'discount'              => $objArticle->discount,
                        'weight'                => $objArticle->weight,
                        'article_number'        => $objArticle->article_number,
                        'idFields'              => $this->objElement->id
                    );

                    $objGenTable->insert($arrFileData);
                }
            }

            // load instance data
            $this->loadInstanceData($strType, $strElementId, $objRegion, $intVersion);


        } catch (Excpetion $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * loadInstanceData
     * @param string $strType
     * @param string $strElementId
     * @param GenericElementRegion $objRegion
     * @param number $intVersion
     * @author Daniel Rotter
     * @return array
     * @version 1.0
     */
    public function loadInstanceData($strType, $strElementId, $objRegion, $intVersion)
    {
        try {
            $this->strType = $strType;

            $strGenForm = $this->objElement->Setup()->getFormId() . '-' . $this->objElement->Setup()->getFormVersion();
            $objGenTable = $this->getModelGenericData()->getGenericTable($strType . '-' . $strGenForm . '-Region' . $objRegion->getRegionId() . '-InstanceArticles');

            $objSelect = $objGenTable->select();
            $objSelect->setIntegrityCheck(false);

            $objSelect->from($objGenTable->info(Zend_Db_Table_Abstract::NAME), array('id', 'size', 'price', 'discount', 'weight', 'article_number', 'idFields'));
            $objSelect->join($strType . '-' . $this->objElement->Setup()->getFormId() . '-' . $this->objElement->Setup()->getFormVersion() . '-Region' . $objRegion->getRegionId() . '-Instances AS regionInstance', '`' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.idRegionInstances = regionInstance.id', array('sortPosition'));
            $objSelect->join('fields', 'fields.id = `' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.idFields', array('name'));
            $objSelect->where('`' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.' . $strType . 'Id = ?', $strElementId);
            $objSelect->where('`' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.' . 'version = ?', $intVersion);
            $objSelect->where('`' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.' . 'idLanguages = ?', $this->objElement->Setup()->getLanguageId());
            $objSelect->where('`' . $objGenTable->info(Zend_Db_Table_Abstract::NAME) . '`.' . 'idFields = ?', $this->objElement->id);


            $objRawInstanceData = $objGenTable->fetchAll($objSelect);

            if (count($objRawInstanceData) > 0) {
                $this->objElement->objInstanceArticles = $objRawInstanceData;
            }

            $arrRawInstanceData = $objRawInstanceData->toArray();
            $arrInstanceData = array();
            $arrInstanceFieldNames = array();

            foreach ($arrRawInstanceData as $arrInstanceDataRow) {
                $arrTmp = array($arrInstanceDataRow['sortPosition'] => array());
                $arrInstanceData += $arrTmp;
            }

            //Group the field values together (multiply instance)
            foreach ($arrRawInstanceData as $arrInstanceDataRow) {
                $arrInstanceData[$arrInstanceDataRow['sortPosition']][] = json_encode(array(
                                                                                           'size'           => $arrInstanceDataRow['size'],
                                                                                           'price'          => $arrInstanceDataRow['price'],
                                                                                           'discount'       => $arrInstanceDataRow['discount'],
                                                                                           'weight'         => $arrInstanceDataRow['weight'],
                                                                                           'article_number' => $arrInstanceDataRow['article_number'],
                                                                                      ));
                $arrInstanceFieldNames[$arrInstanceDataRow['sortPosition']] = $arrInstanceDataRow['name'];
            }

            $arrRawInstanceData = $arrInstanceData;
            $arrInstanceData = array();

            //Generate value-string array
            foreach ($arrRawInstanceData as $intInstanceDataId => $arrInstanceDataRow) {
                $strValue = implode('][', $arrInstanceDataRow);
                $arrInstanceData[$intInstanceDataId] = array('name' => $arrInstanceFieldNames[$intInstanceDataId], 'value' => '[' . $strValue . ']');
            }

            return $arrInstanceData;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getModel
     * @return type Model
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModel()
    {
        if ($this->objModel === null) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            $strModelFilePath = GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . $this->objElement->Setup()->getModelSubPath() . ((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')) . 'ies' : ucfirst($this->strType) . 's') . '.php';
            if (file_exists($strModelFilePath)) {
                require_once $strModelFilePath;
                $strModel = 'Model_' . ((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')) . 'ies' : ucfirst($this->strType) . 's');
                $this->objModel = new $strModel();
                $this->objModel->setLanguageId($this->objElement->Setup()->getLanguageId());
            } else {
                throw new Exception('Not able to load type specific model, because the file didn\'t exist! - strType: "' . $this->strType . '"');
            }
        }
        return $this->objModel;
    }

    /**
     * getModelGenericData
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
}