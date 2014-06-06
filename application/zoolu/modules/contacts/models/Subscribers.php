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
 * Model_Subscribers
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-05-04: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
class Model_Subscribers {

    private $intLanguageId;

    /**
     * @var Model_Table_Subscribers
     */
    protected $objSubscriberTable;

    /**
     * @var Model_GenericData
     */
    protected $objModelGenericData;

    /**
     * @var Model_RootLevels
     */
    protected $objModelRootLevels;

    /**
     * @var Core
     */
    private $core;

    /**
     * Constructor
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function __construct() {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * load
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset_Abstract Subscriber
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function load($intElementId) {
        $this->core->logger->debug('subscribers->models->Model_Subscribers->load(' . $intElementId . ')');

        $objSelect = $this->getSubscriberTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('subscribers')
                ->joinLeft(array('uc' => 'users'), 'uc.id = subscribers.idUsers', array('changeUser' => 'CONCAT(uc.fname, \' \', uc.sname)'))
                ->where('subscribers.id = ?', $intElementId);

        return $this->getSubscriberTable()->fetchAll($objSelect);
    }

    /**
     * load by dirty status
     * @param boolean $blnDirty
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadByDirtyStatus($blnDirty = true) {
        $this->core->logger->debug('subscribers->models->Model_Subscribers->loadByDirtyStatus(' . $blnDirty . ')');

        $objSelect = $this->getSubscriberTable()->select()->setIntegrityCheck(false);

        $objSelect->from('subscribers', array('fname', 'sname', 'email', 'created' => 'UNIX_TIMESTAMP(created)', 'subscribed' => 'categoryTitles.title'))
                ->joinLeft('categoryTitles', 'categoryTitles.idCategories = subscribers.subscribed AND categoryTitles.idLanguages = ' . $this->intLanguageId, array())
                ->where('subscribers.dirty = ?', $blnDirty ? $this->core->sysConfig->mail_chimp->mappings->dirty : $this->core->sysConfig->mail_chimp->mappings->dirty);

        return $this->getSubscriberTable()->fetchAll($objSelect);
    }

    /**
     * load by email
     * @param string $strEmail
     * @return Zend_Db_Table_Rowset_Abstract Subscriber
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadByEmail($strEmail) {
        $this->core->logger->debug('subscribers->models->Model_Subscribers->loadByEmail(' . $strEmail . ')');

        $objSelect = $this->getSubscriberTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('subscribers')
                ->joinLeft(array('uc' => 'users'), 'uc.id = subscribers.idUsers', array('changeUser' => 'CONCAT(uc.fname, \' \', uc.sname)'))
                ->where('subscribers.email = ?', $strEmail);

        return $this->getSubscriberTable()->fetchAll($objSelect);
    }

    /**
     * loadByRootLevelFilter
     * @param number $intRootLevelId
     * @param number $intRootLevelFilterId
     * @param string $strSearchValue
     * @param string $strSortOrder
     * @param string $strOrderColumn
     * @param boolean $blnReturnSelect
     * @return Zend_Db_Table_Select | Zend_Db_Table_Rowset
     */
    public function loadByRootLevelFilter($intRootLevelId, $intRootLevelFilterId, $strSearchValue = '', $strSortOrder = 'ASC', $strOrderColumn = 'sname', $blnReturnSelect = false, $blnExtendSelect = false, $filterReachable = false) {
        
        $this->core->logger->debug('subscribers->models->Model_Subscribers->loadByRootLevelFilter(' . $intRootLevelId . ', ' . $intRootLevelFilterId . ')');

        $objTableFields = new Zend_Db_Table('fields');

        if ($intRootLevelFilterId != null) {
            $objRootLevelFilterValues = $this->getModelRootLevels()->loadRootLevelFilterValues($intRootLevelFilterId);
        }

        //Build the query
        $objSelect = $this->getSubscriberTable()->select();
        $objSelect->setIntegrityCheck(false);
        $arrValues = array();
        if ($blnExtendSelect) {
            $arrValues = array('id', 'salutation' => 'csat.title', 'title', 'fname', 'sname', 'email', 'phone', 'mobile', 'fax', 'website', 'street', 'city', 'state', 'zip', 'changed', 'type' => new Zend_Db_Expr("'subscriber'"));
        } else {
            $arrValues = array('id', 'fname', 'sname', 'email', 'subscribed' => 'csut.title', 'bounced' => 'cbt.title', 'created', 'changed', 'type' => new Zend_Db_Expr("'subscriber'"));
        }

        $objSelect->from(array('s' => 'subscribers'), $arrValues);
        $objSelect->joinInner(array('gf' => 'genericForms'), 'gf.id = s.idGenericForms', array('gf.genericFormId', 'gf.version'));
        $objSelect->joinLeft(array('csa' => 'categories'), 'csa.id = s.salutation', array());
        $objSelect->joinLeft(array('csat' => 'categoryTitles'), 'csat.idCategories = csa.id AND csat.idLanguages = ' . $this->intLanguageId, array());
        $objSelect->joinLeft(array('cd' => 'categories'), 'cd.id = s.dirty', array());
        $objSelect->joinLeft(array('cdt' => 'categoryTitles'), 'cdt.idCategories = cd.id AND cdt.idLanguages = ' . $this->intLanguageId, array());
        $objSelect->joinLeft(array('csu' => 'categories'), 'csu.id = s.subscribed', array());
        $objSelect->joinLeft(array('csut' => 'categoryTitles'), 'csut.idCategories = csu.id AND csut.idLanguages = ' . $this->intLanguageId, array());
        $objSelect->joinLeft(array('cb' => 'categories'), 'cb.id = s.bounced', array());
        $objSelect->joinLeft(array('cbt' => 'categoryTitles'), 'cbt.idCategories = cb.id AND cbt.idLanguages = ' . $this->intLanguageId, array());

        //Apply rootLevelFilters
        if ($intRootLevelFilterId != null) {
            foreach ($objRootLevelFilterValues as $objRootLevelFilterValue) {
                //Load FieldInformation
                $strField = $objRootLevelFilterValue->field;

                $objSelectFields = $objTableFields->select();
                $objSelectFields->from('fields')
                        ->where('id = ?', $this->core->sysConfig->contact->field_mappings->$strField);

                $objField = $objTableFields->fetchRow($objSelectFields);

                $objField->sqlSelect = str_replace('%WHERE_ADDON%', '', $objField->sqlSelect);
                $objField->sqlSelect = str_replace('%LANGUAGE_ID%', $this->core->intZooluLanguageId, $objField->sqlSelect);

                //Build Subselect
                $strSubselect = 'SELECT f.id FROM (' . $objField->sqlSelect . ') AS f
                                      	INNER JOIN `subscriber-DEFAULT_SUBSCRIBER-1-InstanceMultiFields` AS simf ON simf.idRelation = f.id
                                      	WHERE simf.idSubscribers = s.id
                                      		AND simf.idFields = ' . $objField->id;

                $strOperator = '';
                $arrValues = explode(',', $objRootLevelFilterValue->value);
                switch ($objRootLevelFilterValue->operator) {
                    case 'one':
                        $strOperator = 'IN';
                        $blnFirst = true;
                        foreach ($arrValues as $intCount => $strValue) {
                            if (count($arrValues) == 1) {
                                $objSelect->Where('(\'' . $strValue . '\' ' . $strOperator . ' (' . $strSubselect . '))');
                            } else {
                                $blnLast = count($arrValues) == ($intCount + 1);
                                if ($blnFirst) {
                                    $objSelect->Where('(\'' . $strValue . '\' ' . $strOperator . ' (' . $strSubselect . ')');
                                } else {
                                    if ($blnLast) {
                                        $objSelect->orWhere('\'' . $strValue . '\' ' . $strOperator . ' (' . $strSubselect . '))');
                                    } else {
                                        $objSelect->orWhere('\'' . $strValue . '\' ' . $strOperator . ' (' . $strSubselect . ')');
                                    }
                                }
                                $blnFirst = false;
                            }
                        }
                        break;
                    case 'none':
                        if ($strOperator == '')
                            $strOperator = 'NOT IN';
                        foreach ($arrValues as $strValue) {
                            $objSelect->where('\'' . $strValue . '\' ' . $strOperator . ' (' . $strSubselect . ')');
                        }
                        break;
                    case 'all':
                        $strOperator = 'IN';
                        foreach ($arrValues as $strValue) {
                            $objSelect->where('\'' . $strValue . '\' ' . $strOperator . ' (' . $strSubselect . ')');
                        }
                        break;
                }
            }
        }
        $objSelect->where('s.idRootLevels = ?', $intRootLevelId);

        if ($strSearchValue != '') {
            $objSelect->where('s.fname LIKE ?', '%' . $strSearchValue . '%');
            $objSelect->orWhere('s.sname LIKE ?', '%' . $strSearchValue . '%');
        }
        
        if ($filterReachable) {
            $objSelect->where('s.subscribed = ?', $this->core->sysConfig->contact->subscribed);
            $objSelect->where('s.bounced != \''. $this->core->sysConfig->contact->bounce_mapping->hard . '\' OR s.bounced IS NULL');
        }
        
        $objSelect->order($strOrderColumn . ' ' . strtoupper($strSortOrder));

        if ($blnReturnSelect) {
            return $objSelect;
        } else {
            return $this->getSubscriberTable()->fetchAll($objSelect);
        }

    }

    /**
     * loadBounced
     * @return Zend_Db_Table_Select | Zend_Db_Table_Rowset
     */
    public function loadBounced($bouncetype, $intRootLevelId, $strSearchValue = '', $strSortOrder = 'ASC', $strOrderColumn = 'sname', $blnReturnSelect = false, $blnExtendSelect = false) {
        $this->core->logger->debug('subscribers->models->Model_Subscribers->loadBounced(' . $bouncetype . ', ' . $intRootLevelId . ')');

        //Build the query
        $objSelect = $this->getSubscriberTable()->select();
        $objSelect->setIntegrityCheck(false);

        $arrValues = array();
        if ($blnExtendSelect) {
            $arrValues = array('id', 'salutation' => 'csat.title', 'title', 'fname', 'sname', 'email', 'phone', 'mobile', 'fax', 'website', 'street', 'city', 'state', 'zip', 'type' => new Zend_Db_Expr("'subscriber'"));
        } else {
            $arrValues = array('id', 'fname', 'sname', 'email', 'subscribed' => 'csut.title', 'dirty' => 'cdt.title', 'bounced' => 'cbt.title', 'created', 'type' => new Zend_Db_Expr("'subscriber'"));
        }

        $objSelect->from(array('s' => 'subscribers'), $arrValues);
        $objSelect->joinInner(array('gf' => 'genericForms'), 'gf.id = s.idGenericForms', array('gf.genericFormId', 'gf.version'));
        $objSelect->joinLeft(array('csa' => 'categories'), 'csa.id = s.salutation', array());
        $objSelect->joinLeft(array('csat' => 'categoryTitles'), 'csat.idCategories = csa.id AND csat.idLanguages = ' . $this->intLanguageId, array());
        $objSelect->joinLeft(array('cd' => 'categories'), 'cd.id = s.dirty', array());
        $objSelect->joinLeft(array('cdt' => 'categoryTitles'), 'cdt.idCategories = cd.id AND cdt.idLanguages = ' . $this->intLanguageId, array());
        $objSelect->joinLeft(array('csu' => 'categories'), 'csu.id = s.subscribed', array());
        $objSelect->joinLeft(array('csut' => 'categoryTitles'), 'csut.idCategories = csu.id AND csut.idLanguages = ' . $this->intLanguageId, array());
        $objSelect->joinLeft(array('cb' => 'categories'), 'cb.id = s.bounced', array());
        $objSelect->joinLeft(array('cbt' => 'categoryTitles'), 'cbt.idCategories = cb.id AND cbt.idLanguages = ' . $this->intLanguageId, array());

        $objSelect->where('s.idRootLevels = ?', $intRootLevelId);
        $objSelect->where('s.bounced = ?', $bouncetype);
        if ($strSearchValue != '') {
            $objSelect->where('s.fname LIKE ?', '%' . $strSearchValue . '%');
            $objSelect->orWhere('s.sname LIKE ?', '%' . $strSearchValue . '%');
        }
        $objSelect->order($strOrderColumn . ' ' . strtoupper($strSortOrder));
        $this->core->logger->debug(strval($objSelect));
        if ($blnReturnSelect) {
            return $objSelect;
        } else {
            return $this->getSubscriberTable()->fetchAll($objSelect);
        }
    }

    /**
     * loadProperties
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadProperties($intElementId) {
        $this->core->logger->debug('subscribers->models->Model_Subscribers->loadProperties(' . $intElementId . ')');

        $objSelect = $this->getSubscriberTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('subscribers')
                ->join('genericForms', 'genericForms.id = subscribers.idGenericForms', array('genericFormId', 'genericFormVersion' => 'version', 'genericFormType' => 'idGenericFormTypes'))
                ->where('subscribers.id = ?', $intElementId);

        return $this->getSubscriberTable()->fetchAll($objSelect);
    }

    /**
     * loadByHash
     * @param String $hash
     * @author Raphael Stocker <raphael.stocker@massiveart.com>
     */
    public function loadByHash($hash) {
        $this->core->logger->debug('subscribers->models->Model_Subscribers->loadByHash(' . $hash . ')');
        $objSelect = $this->getSubscriberTable()->select();
        $objSelect->setIntegrityCheck(false);
        $objSelect->from('subscribers')
                  ->join('newsletterUnsubscribeHashes', 'newsletterUnsubscribeHashes.idSubscriber = subscribers.id', array('hashId' => 'id'))
                  ->where('newsletterUnsubscribeHashes.hash = ?', array($hash));
        return $this->getSubscriberTable()->fetchAll($objSelect);
    }
    
    /**
     * loadByOptinkey
     * @param String $optinkey
     * @author Raphael Stocker <raphael.stocker@massiveart.com>
     */
    public function loadByOptinkey($optinkey) {
        $this->core->logger->debug('subscribers->models->Model_Subscribers->loadByOptinkey(' . $optinkey . ')');
        $objSelect = $this->getSubscriberTable()->select();
        $objSelect->setIntegrityCheck(false);
        $objSelect->from('subscribers')
                  ->where('subscribers.optinkey = ?', array($optinkey));
        return $this->getSubscriberTable()->fetchAll($objSelect);
    }

    /**
     * add
     * @param array $arrData
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function add($arrData) {
        $this->core->logger->debug('subscribers->models->Model_Subscribers->add()');

        $arrData = array_merge(
                $arrData, array(
            'changed' => date('Y-m-d H:i:s')
                )
        );

        return $this->getSubscriberTable()->insert($arrData);
    }

    /**
     * update
     * @param integer $intElementId
     * @param array $arrData
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function update($intElementId, $arrData) {
        $this->core->logger->debug('subscribers->models->Model_Subscribers->update(' . $intElementId . ')');

        $strWhere = $this->getSubscriberTable()->getAdapter()->quoteInto('id = ?', $intElementId);

        $arrData = array_merge(
                $arrData, array(
            'changed' => date('Y-m-d H:i:s')
                )
        );

        return $this->getSubscriberTable()->update($arrData, $strWhere);
    }
    
    /**
     * updateInterests
     * @param integer $intElementId
     * @param array $arrInterests
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function updateInterests($intElementId, $arrInterests) {
        $this->core->logger->debug('subscribers->models->Model_Subscribers->updateInterests()');

        $objGenTable = $this->getModelGenericData()->getGenericTable('subscriber-DEFAULT_SUBSCRIBER-1-InstanceMultiFields');

        $strWhere = $objGenTable->getAdapter()->quoteInto('idSubscribers = ?', $intElementId);

        $objGenTable->delete($strWhere);

        foreach ($arrInterests as $strField => $arrIds) {
            $fieldId = false;
            switch ($strField) {
                case 'portal':
                    $fieldId = $this->core->sysConfig->contact->field_mappings->portal;
                    break;
                case 'interest_group':
                    $fieldId = $this->core->sysConfig->contact->field_mappings->interestgroup;
                    break;
                case 'language':
                    $fieldId = $this->core->sysConfig->contact->field_mappings->language;
                    break;
            }

            if ($fieldId) {
                foreach ($arrIds as $intId) {
                    $arrData = array(
                        'idSubscribers' => $intElementId,
                        'idRelation' => $intId,
                        'idFields' => $fieldId
                    );

                    $objGenTable->insert($arrData);
                }
            }
        }
    }

    /**
     * delete
     * @param integer $intElementId
     * @return the number of rows deleted
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function delete($intElementId) {
        $this->core->logger->debug('subscribers->models->Model_Subscribers->delete()');
        $strWhere = $this->getSubscriberTable()->getAdapter()->quoteInto('id = ?', $intElementId);
        return $this->objSubscriberTable->delete($strWhere);
    }

    /**
     * deleteMultiple
     * @param array $arrElementIds
     * @return the number of rows deleted
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function deleteMultiple($arrElementIds) {
        $this->core->logger->debug('subscribers->models->Model_Subscribers->deleteMultiple()');

        try {
            $strWhere = '';
            $intCounter = 0;
            if (count($arrElementIds) > 0) {
                foreach ($arrElementIds as $intMemberId) {
                    if ($intMemberId != '') {
                        if ($intCounter == 0) {
                            $strWhere .= $this->getSubscriberTable()->getAdapter()->quoteInto('id = ?', $intMemberId);
                        } else {
                            $strWhere .= $this->getSubscriberTable()->getAdapter()->quoteInto(' OR id = ?', $intMemberId);
                        }
                        $intCounter++;
                    }
                }
            }
            $this->objSubscriberTable->delete($strWhere);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getSubscriberTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getSubscriberTable() {

        if ($this->objSubscriberTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'contacts/models/tables/Subscribers.php';
            $this->objSubscriberTable = new Model_Table_Subscribers();
        }

        return $this->objSubscriberTable;
    }

    /**
     * getModelGenericData
     * @return Model_GenericData
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    protected function getModelGenericData() {
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
     * getModelRootLevels
     * @return Model_RootLevels
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    protected function getModelRootLevels() {
        if (null === $this->objModelRootLevels) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/RootLevels.php';
            $this->objModelRootLevels = new Model_RootLevels();
            $this->objModelRootLevels->setLanguageId($this->core->intZooluLanguageId);
        }

        return $this->objModelRootLevels;
    }

    /**
     * setLanguageId
     * @param integer $intLanguageId
     */
    public function setLanguageId($intLanguageId) {
        $this->intLanguageId = $intLanguageId;
    }

    /**
     * getLanguageId
     * @param integer $intLanguageId
     */
    public function getLanguageId() {
        return $this->intLanguageId;
    }

}
