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
 * @package    application.zoolu.modules.core.models
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Model_SweepstakeCodes
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-03-01: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Model_SweepstakeCodes
{

    /**
     * @var Model_Table_SweepstakeCodes
     */
    protected $objSweepstakeCodeTable;

    /**
     * @var Model_Table_SweepstakeCodeTypes
     */
    protected $objSweepstakeCodeTypeTable;

    /**
     * @var Core
     */
    private $core;

    protected $intSweepstakeCodeId;
    protected $intSweepstakeCodeTypeId;
    protected $strSweepstakeCodeTypeKey;
    protected $strSweepstakeCode;


    /**
     * Constructor
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * addCodeWithTypeKey
     * @return Zend_Db_Table_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function addCodeWithTypeKey($strCode, $strCodeTypeKey)
    {
        $this->core->logger->debug('core->models->SweepstakeCodes->addCodeWithTypeKey(' . $strCode . ', ' . $strCodeTypeKey . ')');

        // get sweepstake code type data
        $objCodeTypeData = $this->getCodeTypeByKey($strCodeTypeKey);
        if (count($objCodeTypeData) > 0) {
            $objCodeTypeData = $objCodeTypeData->current();
            $this->setSweepstakeCodeTypeId($objCodeTypeData->id);
            $this->setSweepstakeCodeTypeKey($objCodeTypeData->key);
        }

        // insert code if sweepstake code type id is not empty
        $intNumOfEffectedRows = 0;
        if ($this->intSweepstakeCodeTypeId > 0) {
            $arrData = array(
                'idSweepstakeCodeTypes'  => $this->intSweepstakeCodeTypeId,
                'code'                   => $strCode
            );

            $intNumOfEffectedRows = $this->getSweepstakeCodeTable()->insert($arrData);
        } else {
            $this->core->logger->debug('core->models->SweepstakeCodes->addCodeWithTypeKey(): No sweepstake code type id! (Code: ' . $strCode . ')');
        }

        return $intNumOfEffectedRows;
    }

    /**
     * findCode
     * @return Zend_Db_Table_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function findCode($strCode, $strCodeTypeKey)
    {
        $this->core->logger->debug('core->models->SweepstakeCodes->findCode(' . $strCode . ', ' . $strCodeTypeKey . ')');

        $objSelect = $this->getSweepstakeCodeTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('sweepstakeCodes', array('id', 'idSweepstakeCodeTypes', 'code'));
        $objSelect->join('sweepstakeCodeTypes', 'sweepstakeCodeTypes.key = \'' . $strCodeTypeKey . '\'', array());
        $objSelect->where('sweepstakeCodes.idSweepstakeCodeTypes = sweepstakeCodeTypes.id');
        $objSelect->where('sweepstakeCodes.code = ?', $strCode);

        return $this->getSweepstakeCodeTable()->fetchAll($objSelect);
    }

    /**
     * getCodeTypeByKey
     * @return Zend_Db_Table_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getCodeTypeByKey($strCodeTypeKey, $blnOnlyActive = true)
    {
        $this->core->logger->debug('core->models->SweepstakeCodes->getCodeTypeByKey(' . $strCodeTypeKey . ')');

        $objSelect = $this->getSweepstakeCodeTypeTable()->select();
        $objSelect->from('sweepstakeCodeTypes', array('id', 'title', 'key', 'active'));
        $objSelect->where('sweepstakeCodeTypes.key = ?', $strCodeTypeKey);
        if ($blnOnlyActive) {
            $objSelect->where('sweepstakeCodeTypes.active = 1');
        }

        return $this->getSweepstakeCodeTypeTable()->fetchAll($objSelect);
    }

    /**
     * getSweepstakeCodeTable
     * @return Zend_Db_Table_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getSweepstakeCodeTable()
    {

        if ($this->objSweepstakeCodeTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/SweepstakeCodes.php';
            $this->objSweepstakeCodeTable = new Model_Table_SweepstakeCodes();
        }

        return $this->objSweepstakeCodeTable;
    }

    /**
     * getSweepstakeCodeTypeTable
     * @return Zend_Db_Table_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getSweepstakeCodeTypeTable()
    {

        if ($this->objSweepstakeCodeTypeTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/SweepstakeCodeTypes.php';
            $this->objSweepstakeCodeTypeTable = new Model_Table_SweepstakeCodeTypes();
        }

        return $this->objSweepstakeCodeTypeTable;
    }

    /**
     * setSweepstakeCodeId
     * @param integer $intSweepstakeCodeId
     */
    public function setSweepstakeCodeId($intSweepstakeCodeId)
    {
        $this->intSweepstakeCodeId = $intSweepstakeCodeId;
    }

    /**
     * getSweepstakeCodeId
     * @return integer $intSweepstakeCodeId
     */
    public function getSweepstakeCodeId()
    {
        return $this->intSweepstakeCodeId;
    }

    /**
     * setSweepstakeCodeTypeId
     * @param integer $intSweepstakeCodeTypeId
     */
    public function setSweepstakeCodeTypeId($intSweepstakeCodeTypeId)
    {
        $this->intSweepstakeCodeTypeId = $intSweepstakeCodeTypeId;
    }

    /**
     * getSweepstakeCodeTypeId
     * @return integer $intSweepstakeCodeTypeId
     */
    public function getSweepstakeCodeTypeId()
    {
        return $this->intSweepstakeCodeTypeId;
    }

    /**
     * setSweepstakeCodeTypeKey
     * @param string $strSweepstakeCodeTypeKey
     */
    public function setSweepstakeCodeTypeKey($strSweepstakeCodeTypeKey)
    {
        $this->strSweepstakeCodeTypeKey = $strSweepstakeCodeTypeKey;
    }

    /**
     * getSweepstakeCodeTypeKey
     * @return string $strSweepstakeCodeTypeKey
     */
    public function getSweepstakeCodeTypeKey()
    {
        return $this->strSweepstakeCodeTypeKey;
    }

    /**
     * setSweepstakeCode
     * @param string $strSweepstakeCode
     */
    public function setSweepstakeCode($strSweepstakeCode)
    {
        $this->strSweepstakeCode = $strSweepstakeCode;
    }

    /**
     * getSweepstakeCode
     * @return string $strSweepstakeCode
     */
    public function getSweepstakeCode()
    {
        return $this->strSweepstakeCode;
    }
}

?>