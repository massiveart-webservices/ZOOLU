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
 * @package    library.massiveart.generic.fields.Datetimes.data.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * GenericDataHelper_Datetimes
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-08-10: Thomas Schedler
 *
 * @author Alexander Schranz <alexander.schranz@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.fields.Datetimes.data.helpers
 * @subpackage GenericDataHelper_Datetimes
 */

require_once(dirname(__FILE__) . '/../../../../data/helpers/Abstract.php');

class GenericDataHelper_Datetimes extends GenericDataHelperAbstract
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
     * @author Alexander Schranz <alexander.schranz@massiveart.com>
     * @version 1.0
     */
    public function save($intElementId, $strType, $strElementId = null, $intVersion = null)
    {
        try {
            $this->strType = $strType;

            $this->getModel();

            $this->objModel->deleteDatetimes($strElementId, $intVersion, $this->objElement->id);

            if ($this->objElement->getValue() != '') $this->objModel->addDatetimes($this->objElement->getValue(), $strElementId, $intVersion, $this->objElement->id);

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
     * @author Alexander Schranz <alexander.schranz@massiveart.com>
     * @version 1.0
     */
    public function load($intElementId, $strType, $strElementId = null, $intVersion = null)
    {
        try {
            $this->strType = $strType;

            $this->getModel();

            $objDatetime = $this->objModel->loadDatetimes($strElementId, $intVersion, $this->objElement->id);

            if (count($objDatetime) > 0) {
                $this->objElement->objDatetimes = $objDatetime;

                $datetime = new stdClass();

                foreach($objDatetime as $key => $value) {
                    $datetime->$key = $objDatetime->$key;
                }

                $this->objElement->setValue($datetime);
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getModel
     * @return Model_Pages|Model_Products
     * @throws Exception
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
            $this->core->logger->debug($strModelFilePath);
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