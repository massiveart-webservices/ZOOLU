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
 * @package    application.zoolu.modules.newsletters.models
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Model_Newsletters
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-04-28: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */
class Model_Newsletters {

    private $intLanguageId;

    /**
     * @var Model_Table_Newsletters
     */
    protected $objNewsletterTable;

    /**
     * @var Model_Table_NewsletterStatistics
     */
    protected $objNewsletterStatisticsTable;

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
     * @return Zend_Db_Table_Rowset_Abstract Newsletter
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function load($intElementId) {
        $this->core->logger->debug('newsletters->models->Model_Newsletters->load(' . $intElementId . ')');

        $objSelect = $this->getNewsletterTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('newsletters', array('id', 'idRootLevels', 'idRootLevelFilters', 'idGenericForms', 'idTemplates', 'title', 'newsletter_from_name', 'newsletter_from_email', 'remoteId', 'sent', 'recipients_on_delivery', 'baseportal', 'idUsers', 'delivered', 'creator', 'created', 'changed'))
                ->join(array('ni' => 'newsletter-DEFAULT_NEWSLETTER-1-Instances'), 'ni.idNewsletters = newsletters.id', array('languageCode' => 'language'))
                ->join('languages', 'languages.languageCode = ni.language', array('languageId' => 'id'))
                ->joinLeft(array('uc' => 'users'), 'uc.id = newsletters.idUsers', array('changeUser' => 'CONCAT(uc.fname, \' \', uc.sname)'))
                ->joinLeft('genericForms', 'genericForms.id = newsletters.idGenericForms', array('genericFormId', 'version', 'idGenericFormTypes'))
                ->joinLeft('rootLevelFilters', 'rootLevelFilters.id = newsletters.idRootLevelFilters', array('filtertitle'))
                ->where('newsletters.id = ?', $intElementId);

        return $this->getNewsletterTable()->fetchAll($objSelect);
    }

    /**
     * loadGenericForm
     * @param Zend_Db_Table_Row $objNewsletter
     * @return GenericData
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadGenericForm($objNewsletter) {
        $this->core->logger->debug('newsletters->models->Model_Newsletters->loadGenericForm()');
        //Load data from genericForm
        $objGenericData = new GenericData();
        $objGenericData->Setup()->setFormId($objNewsletter->genericFormId);
        $objGenericData->Setup()->setFormVersion($objNewsletter->version);
        $objGenericData->Setup()->setElementId($objNewsletter->id);
        $objGenericData->Setup()->setTemplateId($objNewsletter->idTemplates);
        $objGenericData->Setup()->setFormTypeId($objNewsletter->idGenericFormTypes);
        $objGenericData->Setup()->setActionType($this->core->sysConfig->generic->actions->edit);
        //FIXME correct language!
        $objGenericData->Setup()->setFormLanguageId($this->core->intLanguageId);
        $objGenericData->loadData();
        return $objGenericData;
    }

    /**
     * loadInstanceData
     * @param string $strGenForm
     * @param number $intElementId
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function loadInstanceData($strGenForm, $intElementId) {
        $this->core->logger->debug('newsletters->models->Model_Newsletters->loadInstanceData(' . $strGenForm . ', ' . $intElementId . ')');

        $strTableName = 'newsletter-' . $strGenForm . '-Instances';
        $objSelect = $this->getNewsletterTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($strTableName, array('article'))
                ->where('idNewsletters = ?', $intElementId);

        return $this->getNewsletterTable()->fetchAll($objSelect);
    }

    /**
     * loadProperties
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadProperties($intElementId) {
        $this->core->logger->debug('newsletters->models->Model_Newsletters->loadProperties(' . $intElementId . ')');

        $objSelect = $this->getNewsletterTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('newsletters', array())
                ->join('genericForms', 'genericForms.id = newsletters.idGenericForms', array('genericFormId', 'genericFormVersion' => 'version', 'genericFormType' => 'idGenericFormTypes'))
                ->where('newsletters.id = ?', $intElementId);

        return $this->getNewsletterTable()->fetchAll($objSelect);
    }

    /**
     * add
     * @param GenericSetup $objGenericSetup
     * @param Array $arrData
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function add(GenericSetup $objGenericSetup, $arrData) {
        $this->core->logger->debug('newsletters->models->Model_Newsletters->add()');

        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

        $arrData = array_merge(
                $arrData, array(
            'idUsers' => $intUserId,
            'changed' => date('Y-m-d H:i:s')
                )
        );

        return $this->getNewsletterTable()->insert($arrData);
    }

    /**
     * update
     * @param GenericSetup $objGenericSetup
     * @param Array $arrData
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function update(GenericSetup $objGenericSetup, $arrData) {
        $this->core->logger->debug('newsletters->models->Model_Newsletters->update()');

        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

        $strWhere = $this->getNewsletterTable()->getAdapter()->quoteInto('id = ?', $objGenericSetup->getElementId());

        $arrData = array_merge(
                $arrData, array(
            'idUsers' => $intUserId,
            'changed' => date('Y-m-d H:i:s')
                )
        );

        return $this->getNewsletterTable()->update($arrData, $strWhere);
    }

    /**
     * delete
     * @param integer $intElementId
     * @return the number of rows deleted
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function delete($intElementId) {
        $this->core->logger->debug('newsletters->models->Model_Newsletters->delete()');
        $strWhere = $this->getNewsletterTable()->getAdapter()->quoteInto('id = ?', $intElementId);
        return $this->objNewsletterTable->delete($strWhere);
    }

    /**
     * getNewsletterTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getNewsletterTable() {

        if ($this->objNewsletterTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'newsletters/models/tables/Newsletters.php';
            $this->objNewsletterTable = new Model_Table_Newsletters();
        }

        return $this->objNewsletterTable;
    }

    /**
     * Gets newsletter statistics.
     */
    public function loadNewsletterStatistics($idNewsletter) {
        $objSelect = $this->getModelNewsletterStatisticsTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('newsletterStatistics');
        $objSelect->where('newsletterStatistics.idNewsletter = ?', $idNewsletter);

        return $this->getModelNewsletterStatisticsTable()->fetchAll($objSelect);
    }

    /**
     * Gets subscribers newsletter statistics.
     */
    public function loadSubscribersNewsletterStatistics($idSubscribers, $idNewsletter) {
        
        $this->core->logger->debug('newsletters->models->Model_Newsletters->loadSubscribersNewsletterStatistics(' . $idSubscribers . ', ' . $idNewsletter .')');
        $objSelect = $this->getModelNewsletterStatisticsTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('newsletterStatistics');
        $objSelect->where('newsletterStatistics.idSubscriber = ?', $idSubscribers);
        $objSelect->where('newsletterStatistics.idNewsletter = ?', $idNewsletter);

        return $this->getModelNewsletterStatisticsTable()->fetchAll($objSelect);
    }
    
    /**
     * Saves newsletter statistics.
     * @param array $arrData
     */
    public function addNewsletterStatistics($arrData) {
        $this->core->logger->debug('newsletters->models->Model_Newsletters->addNewsletterStatistics()');
        return $this->getModelNewsletterStatisticsTable()->insert($arrData);
    }
    
    /**
     * Updates newsletter statistics.
     * @param array $arrData
     */
    public function updateNewsletterStatistics($id, $arrData) {
        $this->core->logger->debug('newsletters->models->Model_Newsletters->updateNewsletterStatistics()');
        $strWhere = $this->getModelNewsletterStatisticsTable()->getAdapter()->quoteInto('id = ?', $id);
        $this->getModelNewsletterStatisticsTable()->update($arrData, $strWhere);
    }

    /**
     * getModelNewsletterStatistics
     * @return Model_NewsletterStatistics
     * @author Christian Durak <cdu@massiveart.com>
     */
    protected function getModelNewsletterStatisticsTable() {
        if (null === $this->objNewsletterStatisticsTable) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'newsletters/models/tables/NewsletterStatistics.php';
            $this->objNewsletterStatisticsTable = new Model_Table_NewsletterStatistics();
        }
        return $this->objNewsletterStatisticsTable;
    }

}