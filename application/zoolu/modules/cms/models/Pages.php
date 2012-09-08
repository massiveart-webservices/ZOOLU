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
 * @package    application.zoolu.modules.cms.models
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Model_Pages
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-06: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Model_Pages
{

    private $intLanguageId;
    private $intSegmentId;

    /**
     * @var Model_Folders
     */
    protected $objModelFolders;

    /**
     * @var Model_Table_Pages
     */
    protected $objPageTable;

    /**
     * @var Model_Table_PageProperties
     */
    protected $objPagePropertyTable;

    /**
     * @var Model_Table_Urls
     */
    protected $objPageUrlTable;

    /**
     * @var Model_Table_PageLinks
     */
    protected $objPageLinksTable;

    /**
     * @var Model_Table_PageInternalLinks
     */
    protected $objPageInternalLinksTable;

    /**
     * @var Model_Table_PageCollections
     */
    protected $objPageCollectionTable;

    /**
     * @var Model_Table_PageVideos
     */
    protected $objPageVideosTable;


    /**
     * @var Model_Table_PageContacts
     */
    protected $objPageContactsTable;

    /**
     * @var Model_Table_PageGroups
     */
    protected $objPageGroupsTable;

    /**
     * @var Model_Table_PageDynForms
     */
    protected $objPageDynFormsTable;

    /**
     * @var Model_Table_Groups
     */
    protected $groupsTable;

    /**
     * @var Core
     */
    protected $core;

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
     * loadPlugin
     * @param integer $intElementId
     * @param array $arrFields
     * @param string $strType
     * @return array
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadPlugin($intElementId, $arrFields, $strType)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadPlugin(' . $intElementId . ', ' . $arrFields . ', ' . $strType . ')');
        $objPagePluginTable = $this->getPluginTable($strType);

        $objSelect = $objPagePluginTable->select();
        $objSelect->from($objPagePluginTable, $arrFields);
        $objSelect->join('pages', 'pages.pageId = ' . $objPagePluginTable->info(Zend_Db_Table_Abstract::NAME) . '.pageId AND pages.version = ' . $objPagePluginTable->info(Zend_Db_Table_Abstract::NAME) . '.version', array());
        $objSelect->where('pages.id = ?', $intElementId)
            ->where('idLanguages = ?', $this->getLanguageId());

        return $objPagePluginTable->fetchAll($objSelect);
    }

    /**
     * addPlugin
     * @param integer $intElementId
     * @param array $arrValues
     * @param string $strType
     * @return mixed
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function addPlugin($intElementId, $arrValues, $strType)
    {
        $this->core->logger->debug('cms->models->Model_Pages->addPlugin(' . $arrValues . ',' . $strType . ')');

        $objPageData = $this->load($intElementId);

        if (count($objPageData) > 0) {
            $objPage = $objPageData->current();

            $objPagePluginTable = $this->getPluginTable($strType);

            $strWhere = $objPagePluginTable->getAdapter()->quoteInto('pageId = ?', $objPage->pageId);
            $strWhere .= ' AND ' . $objPagePluginTable->getAdapter()->quoteInto('version = ?', $objPage->version);
            $strWhere .= ' AND ' . $objPagePluginTable->getAdapter()->quoteInto('idLanguages = ?', $this->intLanguageId);
            $objPagePluginTable->delete($strWhere);

            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
            $arrData = array(
                'pageId'      => $objPage->pageId,
                'version'     => $objPage->version,
                'idLanguages' => $this->intLanguageId,
                'creator'     => $intUserId
            );
            $arrData = array_merge($arrData, $arrValues);
            return $objSelect = $objPagePluginTable->insert($arrData);
        }
    }

    /**
     * load
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function load($intElementId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->load(' . $intElementId . ')');

        $objSelect = $this->getPageTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('pages', array('id', 'pageId', 'relationId' => 'pageId', 'version', 'pageProperties.idPageTypes', 'isStartPage', 'pageProperties.showInNavigation', 'pageProperties.idDestination', 'pageProperties.hideInSitemap', 'pageProperties.showInWebsite', 'pageProperties.showInTablet', 'pageProperties.showInMobile', 'idParent', 'idParentTypes', 'idSegments', 'pageProperties.published', 'pageProperties.changed', 'pageProperties.idStatus', 'pageProperties.creator'));
        $objSelect->joinLeft('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('title'));
        $objSelect->joinLeft('pageProperties', 'pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version AND pageProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->joinLeft(array('ub' => 'users'), 'ub.id = pageProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'));
        $objSelect->joinLeft(array('uc' => 'users'), 'uc.id = pageProperties.idUsers', array('changeUser' => 'CONCAT(uc.fname, \' \', uc.sname)'));
        $objSelect->joinLeft('folders', 'folders.id = pages.idParent AND pages.idParentTypes = '.$this->core->sysConfig->parent_types->folder, array('idRootLevels'));
        $objSelect->joinLeft('rootLevels', 'rootLevels.id = folders.idRootLevels', array('languageDefinitionType'));
        $objSelect->joinLeft(array('rl' => 'rootLevels'), 'rl.id = pages.idParent AND pages.idParentTypes = '.$this->core->sysConfig->parent_types->rootlevel, array('languageDefinitionType AS altLanguageDefinitionType'));
        $objSelect->where('pages.id = ?', $intElementId);

        return $this->getPageTable()->fetchAll($objSelect);
    }

    /**
     * loadByPageId
     * @param string $strPageId
     */
    public function loadByPageId($strPageId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadByPageId(' . $strPageId . ')');

        $objSelect = $this->getPageTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('pages', array('id', 'pageId', 'relationId' => 'pageId', 'version', 'pageProperties.idPageTypes', 'isStartPage', 'pageProperties.showInNavigation', 'pageProperties.idDestination', 'pageProperties.hideInSitemap', 'pageProperties.showInWebsite', 'pageProperties.showInTablet', 'pageProperties.showInMobile', 'idParent', 'idParentTypes', 'idSegments', 'pageProperties.published', 'pageProperties.changed', 'pageProperties.idStatus', 'pageProperties.creator'));
        $objSelect->joinLeft('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('title'));
        $objSelect->joinLeft('pageProperties', 'pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version AND pageProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->joinLeft(array('ub' => 'users'), 'ub.id = pageProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'));
        $objSelect->joinLeft(array('uc' => 'users'), 'uc.id = pageProperties.idUsers', array('changeUser' => 'CONCAT(uc.fname, \' \', uc.sname)'));
        $objSelect->where('pages.pageId = ?', $strPageId);

        return $this->getPageTable()->fetchAll($objSelect);
    }


    /**
     * loadByIdAndVersion
     * @param string $strPageId
     * @param integer $intPageVersion
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadByIdAndVersion($strPageId, $intPageVersion)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadByIdAndVersion(' . $strPageId . ', ' . $intPageVersion . ')');

        $objSelect = $this->getPageTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('pages', array('id', 'relationId' => 'pageId', 'pageId', 'version', 'pageProperties.idTemplates', 'pageProperties.idStatus', 'pageProperties.published', 'pageProperties.changed', 'pageProperties.created', 'pageProperties.idPageTypes', 'isStartElement' => 'isStartPage', 'pageProperties.showInNavigation', 'idSegments', 'idParent', 'idParentTypes'));
        $objSelect->joinLeft('pageProperties', 'pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version AND pageProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->joinLeft(array('ub' => 'users'), 'ub.id = pageProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'));
        $objSelect->joinLeft(array('uc' => 'users'), 'uc.id = pageProperties.idUsers', array('changeUser' => 'CONCAT(uc.fname, \' \', uc.sname)'));
        $objSelect->joinLeft(array('ucr' => 'users'), 'ucr.id = pageProperties.creator', array('creator' => 'CONCAT(ucr.fname, \' \', ucr.sname)'));
        $objSelect->join('genericForms', 'genericForms.id = pageProperties.idGenericForms', array('genericFormId', 'version', 'idGenericFormTypes'));
        $objSelect->join('templates', 'templates.id = pageProperties.idTemplates', array('filename', 'cacheLifetime', 'renderScript'));
        $objSelect->where('pages.pageId = ?', $strPageId);
        $objSelect->where('pages.version = ?', $intPageVersion);

        return $this->getPageTable()->fetchAll($objSelect);
    }

    /**
     * loadFormAndTemplateById
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadFormAndTemplateById($intElementId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->load(' . $intElementId . ')');

        $objSelect = $this->getPageTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('pages', array('pageProperties.idGenericForms', 'pageProperties.idTemplates', 'pageProperties.idPageTypes', 'pageProperties.showInNavigation', 'idSegments'));
        $objSelect->join('pageProperties', 'pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version AND pageProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->join('genericForms', 'genericForms.id = pageProperties.idGenericForms', array('genericFormId'));
        $objSelect->where('pages.id = ?', $intElementId);

        return $this->getPageTable()->fetchAll($objSelect);
    }

    /**
     * add
     * @param GenericSetup $objGenericSetup
     * @return stdClass Page
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function add(GenericSetup &$objGenericSetup)
    {
        $this->core->logger->debug('cms->models->Model_Pages->add()');

        $objPage = new stdClass();
        $objPage->pageId = uniqid();
        $objPage->version = 1;
        $objPage->sortPosition = GenericSetup::DEFAULT_SORT_POSITION;
        $objPage->parentId = $objGenericSetup->getParentId();
        $objPage->rootLevelId = $objGenericSetup->getRootLevelId();
        $objPage->isStartElement = $objGenericSetup->getIsStartElement();

        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

        /**
         * check if parent element is rootlevel or folder and get sort position
         */
        if ($objPage->parentId != '' && $objPage->parentId > 0) {
            $objPage->parentTypeId = $this->core->sysConfig->parent_types->folder;
            $objData = $this->getModelFolders()->countChilds($objPage->parentId);
        } else {
            if ($objPage->rootLevelId != '' && $objPage->rootLevelId > 0) {
                $objPage->parentId = $objPage->rootLevelId;
            } else {
                $this->core->logger->err('zoolu->modules->cms->models->Model_Pages->add(): intRootLevelId is empty!');
            }
            $objPage->parentTypeId = $this->core->sysConfig->parent_types->rootlevel;
            $objData = $this->getModelFolders()->countRootChilds($objPage->rootLevelId);
        }

        if (count($objData) == 1) {
            $objPage->sortPosition = current($objData)->counter + 1;
        }

        /**
         * insert main data
         */
        $arrMainData = array(
            'idParent'         => $objPage->parentId,
            'idParentTypes'    => $objPage->parentTypeId,
            'isStartPage'      => $objPage->isStartElement,
            'idUsers'          => $intUserId,
            'sortPosition'     => $objPage->sortPosition,
            'sortTimestamp'    => date('Y-m-d H:i:s'),
            'pageId'           => $objPage->pageId,
            'version'          => $objPage->version,
            'idSegments'       => $objGenericSetup->getSegmentId(),
            'creator'          => $objGenericSetup->getCreatorId(),
            'created'          => date('Y-m-d H:i:s')
        );
        $objPage->id = $this->getPageTable()->insert($arrMainData);

        /**
         * insert language specific properties
         */
        $arrProperties = array(
            'pageId'           => $objPage->pageId,
            'version'          => $objPage->version,
            'idLanguages'      => $this->intLanguageId,
            'idGenericForms'   => $objGenericSetup->getGenFormId(),
            'idTemplates'      => $objGenericSetup->getTemplateId(),
            'idPageTypes'      => $objGenericSetup->getElementTypeId(),
            'showInNavigation' => $objGenericSetup->getShowInNavigation(),
            'idDestination'    => $objGenericSetup->getDestinationId(),
            'hideInSitemap'    => $objGenericSetup->getHideInSitemap(),
            'showInWebsite'    => $objGenericSetup->getShowInWebsite(),
            'showInTablet'     => $objGenericSetup->getShowInTablet(),
            'showInMobile'     => $objGenericSetup->getShowInMobile(),
            'idUsers'          => $intUserId,
            'creator'          => $objGenericSetup->getCreatorId(),
            'publisher'        => $intUserId,
            'created'          => date('Y-m-d H:i:s'),
            'published'        => $objGenericSetup->getPublishDate(),
            'idStatus'         => $objGenericSetup->getStatusId()
        );
        $this->getPagePropertyTable()->insert($arrProperties);

        return $objPage;
    }

    /**
     * update
     * @param GenericSetup $objGenericSetup
     * @param object Page
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function update(GenericSetup &$objGenericSetup, $objPage)
    {
        $this->core->logger->debug('cms->models->Model_Pages->update()');

        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

        $strWhere = $this->getPageTable()->getAdapter()->quoteInto('pageId = ?', $objPage->pageId);
        $strWhere .= $this->getPageTable()->getAdapter()->quoteInto(' AND version = ?', $objPage->version);

        $this->getPageTable()->update(array(
                                           'idSegments'    => $objGenericSetup->getSegmentId(),
                                           'idUsers'       => $intUserId,
                                           'changed'       => date('Y-m-d H:i:s')
                                      ), $strWhere);
        /**
         * update language specific page properties
         */
        $strWhere .= $this->getPageTable()->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);
        $intNumOfEffectedRows = $this->getPagePropertyTable()->update(array(
                                                                           'idGenericForms'      => $objGenericSetup->getGenFormId(),
                                                                           'idTemplates'         => $objGenericSetup->getTemplateId(),
                                                                           'idPageTypes'         => $objGenericSetup->getElementTypeId(),
                                                                           'showInNavigation'    => $objGenericSetup->getShowInNavigation(),
                                                                           'idDestination'       => $objGenericSetup->getDestinationId(),
                                                                           'hideInSitemap'       => $objGenericSetup->getHideInSitemap(),
                                                                           'showInWebsite'       => $objGenericSetup->getShowInWebsite(),
                                                                           'showInTablet'        => $objGenericSetup->getShowInTablet(),
                                                                           'showInMobile'        => $objGenericSetup->getShowInMobile(),
                                                                           'idUsers'             => $intUserId,
                                                                           'creator'             => $objGenericSetup->getCreatorId(),
                                                                           'idStatus'            => $objGenericSetup->getStatusId(),
                                                                           'published'           => $objGenericSetup->getPublishDate(),
                                                                           'changed'             => date('Y-m-d H:i:s')
                                                                      ), $strWhere);

        /**
         * insert language specific page properties
         */
        if ($intNumOfEffectedRows == 0) {
            $arrProperties = array(
                'pageId'              => $objPage->pageId,
                'version'             => $objPage->version,
                'idLanguages'         => $this->intLanguageId,
                'idGenericForms'      => $objGenericSetup->getGenFormId(),
                'idTemplates'         => $objGenericSetup->getTemplateId(),
                'idPageTypes'         => $objGenericSetup->getElementTypeId(),
                'showInNavigation'    => $objGenericSetup->getShowInNavigation(),
                'idDestination'       => $objGenericSetup->getDestinationId(),
                'hideInSitemap'       => $objGenericSetup->getHideInSitemap(),
                'showInWebsite'       => $objGenericSetup->getShowInWebsite(),
                'showInTablet'        => $objGenericSetup->getShowInTablet(),
                'showInMobile'        => $objGenericSetup->getShowInMobile(),
                'idUsers'             => $intUserId,
                'creator'             => $objGenericSetup->getCreatorId(),
                'publisher'           => $intUserId,
                'created'             => date('Y-m-d H:i:s'),
                'published'           => $objGenericSetup->getPublishDate(),
                'idStatus'            => $objGenericSetup->getStatusId()
            );
            $this->getPagePropertyTable()->insert($arrProperties);
        }
    }

    /**
     * addPageLink
     * @param string $strPageId
     * @param integer $intElementId
     * @return integer
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addPageLink($strPageId, $intElementId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->addPageLink(' . $strPageId . ', ' . $intElementId . ')');
        $arrData = array(
            'idPages' => $intElementId,
            'pageId'  => $strPageId
        );
        return $this->getPageLinksTable()->insert($arrData);
    }

    /**
     * addInternalLinks
     * @param string $strLinkedPageIds
     * @param string $strElementId
     * @param integer $intVersion
     * @param integer $intFieldId
     * @return integer
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addInternalLinks($strLinkedPageIds, $strElementId, $intVersion, $intFieldId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->addInternalLinks(' . $strLinkedPageIds . ', ' . $strElementId . ', ' . $intVersion . ', ' . $intFieldId . ')');

        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

        $arrData = array(
            'pageId'      => $strElementId,
            'version'     => $intVersion,
            'idLanguages' => $this->intLanguageId,
            'idFields'    => $intFieldId,
            'idUsers'     => $intUserId,
            'creator'     => $intUserId,
            'created'     => date('Y-m-d H:i:s')
        );

        $strTmpLinkedPageIds = trim($strLinkedPageIds, '[]');
        $arrLinkedPageIds = explode('][', $strTmpLinkedPageIds);

        if (count($arrLinkedPageIds) > 0) {
            foreach ($arrLinkedPageIds as $sortPosition => $strLinkedPageId) {
                $arrData['linkedPageId'] = $strLinkedPageId;
                $arrData['sortPosition'] = $sortPosition + 1;
                $this->getPageInternalLinksTable()->insert($arrData);
            }
        }
    }

    /**
     * addPageCollection
     * @param string $strCollectedPageIds
     * @param string $strPageId
     * @param integer $intElementId
     * @return integer
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addPageCollection($strCollectedPageIds, $strElementId, $intVersion)
    {
        $this->core->logger->debug('cms->models->Model_Pages->addPageCollection(' . $strCollectedPageIds . ', ' . $strElementId . ', ' . $intVersion . ')');

        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

        $arrData = array(
            'pageId'      => $strElementId,
            'version'     => $intVersion,
            'idLanguages' => $this->intLanguageId,
            'idUsers'     => $intUserId,
            'creator'     => $intUserId,
            'created'     => date('Y-m-d H:i:s')
        );

        $strTmpCollectedPageIds = trim($strCollectedPageIds, '[]');
        $arrCollectedPageIds = explode('][', $strTmpCollectedPageIds);

        if (count($arrCollectedPageIds) > 0) {
            foreach ($arrCollectedPageIds as $sortPosition => $strCollectedPageId) {
                $arrData['collectedPageId'] = $strCollectedPageId;
                $arrData['sortPosition'] = $sortPosition + 1;
                $this->getPageCollectionTable()->insert($arrData);
            }
        }
    }

    /**
     * addPageCollectionUrls
     * @param Zend_Db_Table_Rowset_Abstract $objPageCollection
     * @param integer $intParentId
     * @param integer $intParentTypeId
     * @param string $strBaseUrl
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addPageCollectionUrls(Zend_Db_Table_Rowset_Abstract &$objPageCollectionData, $intParentId, $intParentTypeId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->addPageCollectionUrls(Zend_Db_Table_Rowset_Abstract, ' . $intParentId . ', ' . $intParentTypeId . ')');

        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

        $arrData = array(
            'idLanguages'    => $this->intLanguageId,
            'idUsers'        => $intUserId,
            'idParent'       => $intParentId,
            'idParentTypes'  => $intParentTypeId,
            'creator'        => $intUserId,
            'created'        => date('Y-m-d H:i:s')
        );

        if (count($objPageCollectionData) > 0) {

            $objUrlHelper = new GenericDataHelper_Url();

            foreach ($objPageCollectionData as $objPageCollection) {

                $arrData['relationId'] = $objPageCollection->pageId;
                $arrData['version'] = $objPageCollection->version;
                $arrData['idUrlTypes'] = $this->core->sysConfig->url_types->page;
                $arrData['url'] = $objPageCollection->url;

                $this->getPageUrlTable()->insert($arrData);

            }
        }
    }

    /**
     * updateStartPageMainData
     * @param integer $intFolderId
     * @param array $arrProperties
     * @param array $arrTitle
     * @param array $arrPageAttributes
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function updateStartPageMainData($intFolderId, $arrProperties, $arrTitle, $arrPageAttributes)
    {
        $objSelect = $this->getPageTable()->select();
        $objSelect->from($this->objPageTable, array('pageId', 'version'));
        $objSelect->where('idParent = ?', $intFolderId)
            ->where('idParentTypes = ?', $this->core->sysConfig->parent_types->folder)
            ->where('isStartPage = 1');
        $objSelect->order(array('version DESC'));
        $objSelect->limit(1);

        $objStartPageData = $this->objPageTable->fetchAll($objSelect);

        if (count($objStartPageData) > 0) {
            $objStartPage = $objStartPageData->current();

            $strWhere = $this->getPagePropertyTable()->getAdapter()->quoteInto('pageId = ?', $objStartPage->pageId);

            $intNumOfEffectedRows = $this->core->dbh->update('pages', $arrPageAttributes, $strWhere);
            if ($intNumOfEffectedRows == 0) {
                $arrPageAttributes = array_merge($arrPageAttributes, array('pageId' => $objStartPage->pageId, 'version' => $objStartPage->version, 'idLanguages' => $this->intLanguageId));
                $this->core->dbh->insert('pages', $arrPageAttributes);
            }

            $strWhere .= $this->objPagePropertyTable->getAdapter()->quoteInto(' AND version = ?', $objStartPage->version);
            $strWhere .= $this->objPagePropertyTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);

            $intNumOfEffectedRows = $this->objPagePropertyTable->update($arrProperties, $strWhere);
            if ($intNumOfEffectedRows == 0) {
                $arrProperties = array_merge($arrProperties, array('pageId' => $objStartPage->pageId, 'version' => $objStartPage->version, 'idLanguages' => $this->intLanguageId));
                $this->objPagePropertyTable->insert($arrProperties);
            }

            $intNumOfEffectedRows = $this->core->dbh->update('pageTitles', $arrTitle, $strWhere);

            if ($intNumOfEffectedRows == 0) {
                $arrTitle = array_merge($arrTitle, array('pageId' => $objStartPage->pageId, 'version' => $objStartPage->version, 'idLanguages' => $this->intLanguageId));
                $this->core->dbh->insert('pageTitles', $arrTitle);
            }
        }
    }

    /**
     * changeParentFolderId
     * @param integer $intPageId
     * @param integer $intParentFolderId
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function changeParentFolderId($intPageId, $intParentFolderId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->changeParentFolderId(' . $intPageId . ',' . $intParentFolderId . ')');
        try {
            $this->getPageTable();
            $strWhere = $this->objPageTable->getAdapter()->quoteInto('id = ?', $intPageId);
            $this->objPageTable->update(array('idParent' => $intParentFolderId, 'idParentTypes' => $this->core->sysConfig->parent_types->folder), $strWhere);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * changeParentRootFolderId
     * @param integer $intPageId
     * @param integer $intRootFolderId
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function changeParentRootFolderId($intPageId, $intRootFolderId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->changeParentRootFolderId(' . $intPageId . ',' . $intRootFolderId . ')');
        try {
            $this->getPageTable();
            $strWhere = $this->objPageTable->getAdapter()->quoteInto('id = ?', $intPageId);
            $this->objPageTable->update(array('idParent' => $intRootFolderId, 'idParentTypes' => $this->core->sysConfig->parent_types->rootlevel), $strWhere);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * loadPageLink
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadPageLink($intElementId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadPageLink(' . $intElementId . ')');

        $objSelect = $this->getPageTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('pages', array('id', 'pageId', 'version'));
        $objSelect->join('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = ' . $this->intLanguageId, array('title'));
        $objSelect->joinleft('urls', 'urls.relationId = pages.pageId AND urls.version = pages.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND urls.idLanguages = ' . $this->intLanguageId . ' AND urls.isMain = 1 AND urls.idParent IS NULL', array('url'));
        $objSelect->joinleft('languages', 'languages.id = urls.idLanguages', array('languageCode'));
        $objSelect->where('pages.id = (SELECT p.id FROM pages AS p, pageLinks WHERE pageLinks.idPages = ? AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1)', $intElementId);

        return $this->objPageTable->fetchAll($objSelect);
    }

    /**
     * loadInternalLinks
     * @param string $strElementId
     * @param integer $intVersion
     * @param integer $intFieldId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadInternalLinks($strElementId, $intVersion, $intFieldId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadInternalLinks(' . $strElementId . ',' . $intVersion . ',' . $intFieldId . ')');

        $objSelect = $this->getPageInternalLinksTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('pages', array('id', 'relationId' => 'pageId', 'pageId', 'version', 'pageProperties.idPageTypes', 'isStartItem' => 'isStartPage', 'isStartPage', 'pageProperties.idStatus'));
        $objSelect->join('pageInternalLinks', 'pageInternalLinks.linkedPageId = pages.pageId AND pageInternalLinks.pageId = \'' . $strElementId . '\' AND pageInternalLinks.version = ' . $intVersion . ' AND pageInternalLinks.idFields = ' . $intFieldId . ' AND pageInternalLinks.idLanguages = ' . $this->intLanguageId, array('sortPosition'));
        $objSelect->join('pageProperties', 'pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version AND pageProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->join('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = ' . $this->intLanguageId, array('title'));
        $objSelect->joinleft('urls', 'urls.relationId = pages.pageId AND urls.version = pages.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND urls.idLanguages = ' . $this->intLanguageId . ' AND urls.isMain = 1 AND urls.idParent IS NULL', array('url'));
        $objSelect->joinleft('languages', 'languages.id = urls.idLanguages', array('languageCode'));
        $objSelect->where('pages.id = (SELECT p.id FROM pages AS p WHERE pages.pageId = p.pageId ORDER BY p.version DESC LIMIT 1)');
        $objSelect->order('pageInternalLinks.sortPosition ASC');

        return $this->objPageInternalLinksTable->fetchAll($objSelect);
    }

    /**
     * loadPageCollection
     * @param string $strElementId
     * @param integer $intVersion
     * @param integer $intParentId
     * @param integer $intParentTypeId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadPageCollection($strElementId, $intVersion, $intParentId, $intParentTypeId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadPageCollection(' . $strElementId . ')');

        $objSelect = $this->getPageCollectionTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('pages', array('id', 'pageId', 'version', 'pageProperties.idPageTypes', 'isStartPage', 'pageProperties.idStatus'));
        $objSelect->join('pageProperties', 'pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version AND pageProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->join('pageCollections', 'pageCollections.collectedPageId = pages.pageId AND pageCollections.pageId = \'' . $strElementId . '\' AND pageCollections.version = ' . $intVersion . ' AND pageCollections.idLanguages = ' . $this->intLanguageId, array('sortPosition'));
        $objSelect->join('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = ' . $this->intLanguageId, array('title'));
        $objSelect->join('genericForms', 'genericForms.id = pageProperties.idGenericForms', array('genericFormId', 'version AS genericFormVersion'));
        $objSelect->joinleft('urls', 'urls.relationId = pages.pageId AND urls.version = pages.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND urls.idLanguages = ' . $this->intLanguageId . ' AND urls.idParent = ' . $intParentId . ' AND urls.idParentTypes = ' . $intParentTypeId, array('url'));
        $objSelect->joinleft('languages', 'languages.id = urls.idLanguages', array('languageCode'));
        $objSelect->where('pages.id = (SELECT p.id FROM pages AS p WHERE pages.pageId = p.pageId ORDER BY p.version DESC LIMIT 1)');
        $objSelect->order('pageCollections.sortPosition ASC');

        return $this->objPageCollectionTable->fetchAll($objSelect);
    }

    /**
     * loadItems
     * @param integer|array $mixedType
     * @param integer $intParentId
     * @param integer $intCategoryId
     * @param integer $intLabelId
     * @param integer $intEntryNumber
     * @param integer $intSortTypeId
     * @param integer $intSortOrderId
     * @param integer $intEntryDepthId
     * @param array $arrPageIds
     * @param boolean $blnOnlyItems load only items, no start items
     * @param boolean $blnOnlyShowInNavigation load only items with property "showInNavigation"
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadItems($mixedType, $intParentId, $intCategoryId = 0, $intLabelId = 0, $intEntryNumber = 0, $intSortTypeId = 0, $intSortOrderId = 0, $intEntryDepthId = 0, $arrPageIds = array(), $blnOnlyItems = false, $blnOnlyShowInNavigation = false, $blnFilterDisplayEnvironment = true)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadItems(' . $intParentId . ',' . $intCategoryId . ',' . $intLabelId . ',' . $intEntryNumber . ',' . $intSortTypeId . ',' . $intSortOrderId . ',' . $intEntryDepthId . ',' . $arrPageIds . ')');

        if (!is_array($mixedType)) {
            $mixedType = array('id' => $mixedType);
        }

        $intTypeId = (array_key_exists('id', $mixedType)) ? $mixedType['id'] : -1;
        $strType = (array_key_exists('key', $mixedType)) ? $mixedType['key'] . '_types' : 'page_types';

        $strSortOrder = '';
        if ($intSortOrderId > 0 && $intSortOrderId != '') {
            switch ($intSortOrderId) {
                case $this->core->sysConfig->sort->orders->asc->id:
                    $strSortOrder = 'ASC';
                    break;
                case $this->core->sysConfig->sort->orders->desc->id:
                    $strSortOrder = 'DESC';
                    break;
            }
        }

        $strSqlOrderBy = '';
        if ($intSortTypeId > 0 && $intSortTypeId != '') {
            switch ($intSortTypeId) {
                case $this->core->sysConfig->sort->types->manual_sort->id:
                    $strSqlOrderBy = ' ORDER BY sortPosition ' . $strSortOrder . ', sortTimestamp ' . (($strSortOrder == 'DESC') ? 'ASC' : 'DESC');
                    break;
                case $this->core->sysConfig->sort->types->created->id:
                    $strSqlOrderBy = ' ORDER BY created ' . $strSortOrder;
                    break;
                case $this->core->sysConfig->sort->types->changed->id:
                    $strSqlOrderBy = ' ORDER BY changed ' . $strSortOrder;
                    break;
                case $this->core->sysConfig->sort->types->published->id:
                    $strSqlOrderBy = ' ORDER BY published ' . $strSortOrder;
                    break;
                case $this->core->sysConfig->sort->types->alpha->id:
                    $strSqlOrderBy = ' ORDER BY title ' . $strSortOrder;
            }
        }

        switch ($intEntryDepthId) {
            case $this->core->sysConfig->filter->depth->all:
                $strSqlPageDepth = ' AND folders.depth > parent.depth';
                break;
            case $this->core->sysConfig->filter->depth->first:
            default:
                $strSqlPageDepth = ' AND pages.isStartPage = 1
                             AND folders.depth = (parent.depth + 1)';
                break;
        }

        $strSqlPageIds = '';
        if (count($arrPageIds) > 0) {
            $strSqlPageIds = ' AND pages.id NOT IN (' . implode(',', $arrPageIds) . ')';
        }

        $strSqlCategory = '';
        if ($intCategoryId > 0 && $intCategoryId != '') {
            $strSqlCategory = ' AND (pageCategories.category = ' . $intCategoryId . ' OR plCategories.category = ' . $intCategoryId . ')';
        }

        $strSqlLabel = '';
        if ($intLabelId > 0 && $intLabelId != '') {
            $strSqlLabel = ' AND (pageLabels.label = ' . $intLabelId . ' OR plLabels.label = ' . $intLabelId . ')';
        }

        $strSqlLimit = '';
        if ($intEntryNumber > 0 && $intEntryNumber != '') {
            $strSqlLimit = ' LIMIT ' . $intEntryNumber;
        }

        $strPageFilter = '';
        $strFolderFilter = '';
        $strPublishedFilter = '';
        if (!isset($_SESSION['sesTestMode']) || (isset($_SESSION['sesTestMode']) && $_SESSION['sesTestMode'] == false)) {
            $timestamp = time();
            $now = date('Y-m-d H:i:s', $timestamp);
            $strPageFilter = ' AND pageProperties.idStatus = ' . $this->core->sysConfig->status->live;
            $strPublishedFilter = ' AND pageProperties.published <= \'' . $now . '\'';
        }

        $strDisplayPageFilter = ' AND ';
        if ($blnFilterDisplayEnvironment) {
            if ($blnFilterDisplayEnvironment) {
                switch ($this->core->strDisplayType) {
                    case $this->core->sysConfig->display_type->website:
                        $strDisplayPageFilter .= 'pageProperties.showInWebsite = 1';
                        break;
                    case $this->core->sysConfig->display_type->tablet:
                        $strDisplayPageFilter .= 'pageProperties.showInTablet = 1';
                        break;
                    case $this->core->sysConfig->display_type->mobile:
                        $strDisplayPageFilter .= 'pageProperties.showInMobile = 1';
                        break;
                }
            }
        }

        $strDisplayFolderFilter = ' AND ';
        if ($blnFilterDisplayEnvironment) {
            if ($blnFilterDisplayEnvironment) {
                switch ($this->core->strDisplayType) {
                    case $this->core->sysConfig->display_type->website:
                        $strDisplayFolderFilter .= 'folderProperties.showInWebsite = 1';
                        break;
                    case $this->core->sysConfig->display_type->tablet:
                        $strDisplayFolderFilter .= 'folderProperties.showInTablet = 1';
                        break;
                    case $this->core->sysConfig->display_type->mobile:
                        $strDisplayFolderFilter .= 'folderProperties.showInMobile = 1';
                        break;
                }
            }
        }

        if (!empty($this->intSegmentId)) {
            $strPageFilter .= ' AND (pages.idSegments = 0 OR pages.idSegments = ' . $this->core->dbh->quote($this->intSegmentId, Zend_Db::INT_TYPE) . ')';
            $strFolderFilter .= ' AND (folders.idSegments = 0 OR folders.idSegments = ' . $this->core->dbh->quote($this->intSegmentId, Zend_Db::INT_TYPE) . ')';
        }

        $sqlStmt = $this->core->dbh->query('SELECT DISTINCT id, plId, genericFormId, version, plGenericFormId, plVersion,
                                          url, plUrl, title, languageCode, idPageTypes, idDestination, hideInSitemap, sortPosition, sortTimestamp, created, changed, published, target
                                        FROM
                                          (SELECT pages.id, pl.id AS plId, genericForms.genericFormId, genericForms.version,
                                            plGenForm.genericFormId AS plGenericFormId, plGenForm.version AS plVersion, urls.url, lUrls.url AS plUrl, 
                                            IF(pageProperties.idPageTypes = ?, plTitle.title, pageTitles.title) as title, languageCode, pageProperties.idPageTypes, pageProperties.idDestination, pageProperties.hideInSitemap,
                                            pageProperties.created, pageProperties.changed, pageProperties.published, folders.sortPosition, folders.sortTimestamp, pageTargets.target
                                          FROM folders
                                          	INNER JOIN folderProperties ON
                                          	  folderProperties.folderId = folders.folderId AND
                                          	  folderProperties.version = folders.version AND
                                          	  folderProperties.idLanguages = ?,
                                          	pages INNER JOIN pageProperties ON 
                                              pageProperties.pageId = pages.pageId AND 
                                              pageProperties.version = pages.version AND 
                                              pageProperties.idLanguages = ?
                                            LEFT JOIN pageCategories ON
                                              pageCategories.pageId = pages.pageId AND
                                              pageCategories.version = pages.version AND
                                              pageCategories.idLanguages = pageProperties.idLanguages
                                            LEFT JOIN pageLabels ON
                                              pageLabels.pageId = pages.pageId AND
                                              pageLabels.version = pages.version AND
                                              pageLabels.idLanguages = pageProperties.idLanguages
                                            LEFT JOIN pageTargets ON
                                              pageTargets.pageId = pages.pageId AND
                                              pageTargets.version = pages.version AND
                                              pageTargets.idLanguages = pageProperties.idLanguages
                                            INNER JOIN genericForms ON
                                              genericForms.id = pageProperties.idGenericForms
                                            LEFT JOIN pageTitles ON
                                              pageTitles.pageId = pages.pageId AND
                                              pageTitles.version = pages.version AND
                                              pageTitles.idLanguages = ?
                                            LEFT JOIN urls ON
                                              urls.relationId = pages.pageId AND
                                              urls.version = pages.version AND
                                              urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND
                                              urls.idLanguages = ? AND
                                              urls.idParent IS NULL AND
                                              urls.isMain = 1
                                            LEFT JOIN pageLinks ON
                                              pageLinks.idPages = pages.id
                                            LEFT JOIN pages AS pl ON
                                              pl.id = (SELECT p.id FROM pages AS p WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1)
                                            LEFT JOIN pageProperties AS plProperties ON 
                                              plProperties.pageId = pl.pageId AND 
                                              plProperties.version = pl.version AND 
                                              plProperties.idLanguages = ?
                                            LEFT JOIN pageCategories AS plCategories ON
                                              plCategories.pageId = pl.pageId AND
                                              plCategories.version = pl.version AND
                                              plCategories.idLanguages = plProperties.idLanguages
                                            LEFT JOIN pageLabels AS plLabels ON
                                              plLabels.pageId = pl.pageId AND
                                              plLabels.version = pl.version AND
                                              plLabels.idLanguages = plProperties.idLanguages
                                            LEFT JOIN genericForms AS plGenForm ON
                                              plGenForm.id = plProperties.idGenericForms
                                            LEFT JOIN pageTitles AS plTitle ON
                                              plTitle.pageId = pl.pageId AND
                                              plTitle.version = pl.version AND
                                              plTitle.idLanguages = ?
                                            LEFT JOIN urls AS lUrls ON
                                              lUrls.relationId = pl.pageId AND
                                              lUrls.version = pl.version AND
                                              lUrls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND
                                              lUrls.idLanguages = ? AND
                                              lUrls.idParent IS NULL AND
                                              lUrls.isMain = 1
                                            LEFT JOIN languages ON
                                              languages.id = ?
                                            ,folders AS parent
                                          WHERE pages.idParent = folders.id AND
                                            pages.idParentTypes = ? AND
                                            parent.id = ? AND
                                            folders.lft BETWEEN parent.lft AND parent.rgt AND
                                            folders.idRootLevels = parent.idRootLevels
                                            ' . $strDisplayFolderFilter . '
                                            ' . $strSqlPageDepth . '
                                            ' . $strPageFilter . '
                                            ' . $strFolderFilter . '
                                            ' . $strPublishedFilter . '
                                            ' . $strSqlCategory . '
                                            ' . $strSqlLabel . '
                                            ' . $strSqlPageIds . '
                                          UNION
                                          SELECT pages.id, pl.id AS plId, genericForms.genericFormId, genericForms.version,
                                            plGenForm.genericFormId AS plGenericFormId, plGenForm.version AS plVersion, urls.url, lUrls.url AS plUrl,
                                            IF(pageProperties.idPageTypes = ?, plTitle.title, pageTitles.title) as title, languageCode, pageProperties.idPageTypes, pageProperties.idDestination, pageProperties.hideInSitemap,
                                            pageProperties.created, pageProperties.changed, pageProperties.published, pages.sortPosition, pages.sortTimestamp, pageTargets.target                                            
                                          FROM pages
                                            INNER JOIN pageProperties ON 
                                              pageProperties.pageId = pages.pageId AND 
                                              pageProperties.version = pages.version AND 
                                              pageProperties.idLanguages = ?
                                            LEFT JOIN pageCategories ON
                                              pageCategories.pageId = pages.pageId AND
                                              pageCategories.version = pages.version AND
                                              pageCategories.idLanguages = pageProperties.idLanguages
                                            LEFT JOIN pageLabels ON
                                              pageLabels.pageId = pages.pageId AND
                                              pageLabels.version = pages.version AND
                                              pageLabels.idLanguages = pageProperties.idLanguages
                                            LEFT JOIN pageTargets ON
                                              pageTargets.pageId = pages.pageId AND
                                              pageTargets.version = pages.version AND
                                              pageTargets.idLanguages = pageProperties.idLanguages
                                            INNER JOIN genericForms ON
                                              genericForms.id = pageProperties.idGenericForms
                                            LEFT JOIN pageTitles ON
                                              pageTitles.pageId = pages.pageId AND
                                              pageTitles.version = pages.version AND
                                              pageTitles.idLanguages = ?
                                            LEFT JOIN urls ON
                                              urls.relationId = pages.pageId AND
                                              urls.version = pages.version AND
                                              urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND
                                              urls.idLanguages = ? AND
                                              urls.idParent IS NULL AND
                                              urls.isMain = 1
                                            LEFT JOIN pageLinks ON
                                              pageLinks.idPages = pages.id
                                            LEFT JOIN pages AS pl ON
                                              pl.id = (SELECT p.id FROM pages AS p WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1)
                                            LEFT JOIN pageProperties AS plProperties ON 
                                              plProperties.pageId = pl.pageId AND 
                                              plProperties.version = pl.version AND 
                                              plProperties.idLanguages = ?
                                            LEFT JOIN pageCategories AS plCategories ON
                                              plCategories.pageId = pl.pageId AND
                                              plCategories.version = pl.version AND
                                              plCategories.idLanguages = plProperties.idLanguages
                                            LEFT JOIN pageLabels AS plLabels ON
                                              plLabels.pageId = pl.pageId AND
                                              plLabels.version = pl.version AND
                                              plLabels.idLanguages = plProperties.idLanguages
                                            LEFT JOIN genericForms AS plGenForm ON
                                              plGenForm.id = plProperties.idGenericForms
                                            LEFT JOIN pageTitles AS plTitle ON
                                              plTitle.pageId = pl.pageId AND
                                              plTitle.version = pl.version AND
                                              plTitle.idLanguages = ?
                                            LEFT JOIN urls AS lUrls ON
                                              lUrls.relationId = pl.pageId AND
                                              lUrls.version = pl.version AND
                                              lUrls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND
                                              lUrls.idLanguages = ? AND
                                              lUrls.idParent IS NULL AND
                                              lUrls.isMain = 1
                                            LEFT JOIN languages ON
                                              languages.id = ?
                                          WHERE pages.idParent = ? AND
                                            pages.isStartPage = 0 AND
                                            pages.idParentTypes = ?
                                            ' . $strDisplayPageFilter . '
                                            ' . $strPageFilter . '
                                            ' . $strPublishedFilter . '
                                            ' . $strSqlCategory . '
                                            ' . $strSqlLabel . '
                                            ' . $strSqlPageIds . ') AS tbl
                                        ' . $strSqlOrderBy . $strSqlLimit, array(
                                                                                $this->core->sysConfig->page_types->link->id,
                                                                                $this->intLanguageId,
                                                                                $this->intLanguageId,
                                                                                $this->intLanguageId,
                                                                                $this->intLanguageId,
                                                                                $this->intLanguageId,
                                                                                $this->intLanguageId,
                                                                                $this->intLanguageId,
                                                                                $this->intLanguageId,
                                                                                $this->core->sysConfig->parent_types->folder,
                                                                                $intParentId,
                                                                                $this->core->sysConfig->page_types->link->id,
                                                                                $this->intLanguageId,
                                                                                $this->intLanguageId,
                                                                                $this->intLanguageId,
                                                                                $this->intLanguageId,
                                                                                $this->intLanguageId,
                                                                                $this->intLanguageId,
                                                                                $this->intLanguageId,
                                                                                $intParentId,
                                                                                $this->core->sysConfig->parent_types->folder
                                                                               ));
                                                                               
        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadPagesByfilter
     * @param integer $intParentFolderId
     * @param array $arrTagIds
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadPagesByFilter($intParentFolderId, $arrTagIds = array())
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadPagesByFilter(' . $intParentFolderId . ')');

        $strTagIds = '';
        if (count($arrTagIds) > 0) {
            $strTagIds = implode(',', $arrTagIds);
        }

        $objSelect = $this->getPageTable()->select()->setIntegrityCheck(false);
        $objSelect->from('pages', array('id', 'pageId', 'isStartPage'))
            ->joinLeft('pageProperties', 'pageProperties.pageId = pages.pageId AND pageProperties.idLanguages = ' . $this->intLanguageId, array('idStatus'))
            ->joinLeft('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.idLanguages = ' . $this->intLanguageId, array('title'))
            ->joinLeft(array('alternativeTitle' => 'pageTitles'), 'alternativeTitle.pageId = pages.pageId AND alternativeTitle.idLanguages = ' . $this->intLanguageId, array('alternativeTitle' => 'title'))
            ->joinLeft(array('fallbackTitle' => 'pageTitles'), 'fallbackTitle.pageId = pages.pageId AND fallbackTitle.idLanguages = 0', array('fallbackTitle' => 'title'));
        if (trim($strTagIds, ',') != '') {
            $objSelect->join('tagPages', 'tagPages.pageId = pages.pageId AND tagPages.idTags IN (' . trim($strTagIds, ',') . ')', array());
        }
        $objSelect->where('pages.idParent = ?', $intParentFolderId)
            ->where('pages.idParentTypes = ?', $this->core->sysConfig->parent_types->folder)
            ->order('pages.sortPosition');

        $this->core->logger->debug('loadPagesByFilter: ' . strval($objSelect));

        return $this->getPageTable()->fetchAll($objSelect);
    }

    /**
     * loadItemInstanceDataByIds
     * @param string $strGenForm
     * @param array $arrPageIds
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadItemInstanceDataByIds($strGenForm, $arrPageIds)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadItemInstanceDataByIds(' . $strGenForm . ', ' . $arrPageIds . ')');

        // FIXME : !!! CHANGE INSTANCE FIELDS DEFINTION
        // FIXME : !!! iFl.idFields IN (5,55) -> define
        if ($strGenForm != '' && $strGenForm != '-' && strpos($strGenForm, $this->core->sysConfig->page_types->link->default_formId) === false) {
            $strSqlInstanceFields = '';
            if (strpos($strGenForm, $this->core->sysConfig->form->ids->pages->overview) !== false) {
                $strSqlInstanceFields = ' `page-' . $strGenForm . '-Instances`.shortdescription,
                                  `page-' . $strGenForm . '-Instances`.read_more_text,
                                  `page-' . $strGenForm . '-Instances`.description,';
            } else if (strpos($strGenForm, $this->core->sysConfig->form->ids->events->default . '-1') !== false) { // FIXME : genform-version (e.g. DEFAULT_EVENT-1)
                $strSqlInstanceFields = ' `page-' . $strGenForm . '-Instances`.shortdescription,
                                  `page-' . $strGenForm . '-Instances`.description,
                                  `page-' . $strGenForm . '-Instances`.read_more_text,
                                  `page-' . $strGenForm . '-Instances`.event_status,';
            } else {
                $strSqlInstanceFields = ' `page-' . $strGenForm . '-Instances`.shortdescription,
                                  `page-' . $strGenForm . '-Instances`.read_more_text,
                                  `page-' . $strGenForm . '-Instances`.description,';
            }

            $strSqlWherePageIds = '';
            if (count($arrPageIds) > 0) {
                $strSqlWherePageIds = ' WHERE pages.id IN (' . implode(',', $arrPageIds) . ')';
            }

            $sqlStmt = $this->core->dbh->query('SELECT pages.id,
                                            ' . $strSqlInstanceFields . '
                                            files.filename, files.version AS fileversion, files.path AS filepath, fileTitles.title AS filetitle
                                          FROM pages
                                          LEFT JOIN `page-' . $strGenForm . '-Instances` ON
                                            `page-' . $strGenForm . '-Instances`.pageId = pages.pageId AND
                                            `page-' . $strGenForm . '-Instances`.version = pages.version AND
                                            `page-' . $strGenForm . '-Instances`.idLanguages = ?
                                          LEFT JOIN `page-' . $strGenForm . '-InstanceFiles` AS iFiles ON
                                            iFiles.id = (SELECT iFl.id FROM `page-' . $strGenForm . '-InstanceFiles` AS iFl
                                                         WHERE iFl.pageId = pages.pageId AND iFl.version = pages.version AND iFl.idLanguages = ? AND iFl.idFields IN (5,55)
                                                         ORDER BY iFl.idFields DESC LIMIT 1)
                                          LEFT JOIN files ON
                                            files.id = iFiles.idFiles AND
                                            files.isImage = 1
                                          LEFT JOIN fileTitles ON
                                            fileTitles.idFiles = files.id AND
                                            fileTitles.idLanguages = ?
                                          ' . $strSqlWherePageIds, array($this->intLanguageId, $this->intLanguageId, $this->intLanguageId));

            return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
        }
    }

    /**
     * loadItemInstanceGlobalFilterDataByIds
     * @param string $strGenForm
     * @param array $arrPageIds
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadItemInstanceGlobalFilterDataByIds($strGenForm, $arrPageIds)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadItemInstanceGlobalFilterDataByIds(' . $strGenForm . ', ' . $arrPageIds . ')');

        $strSqlInstanceFields = ' `page-' . $strGenForm . '-Instances`.entry_point,
                              `page-' . $strGenForm . '-Instances`.entry_category,
                              `page-' . $strGenForm . '-Instances`.entry_label';

        $strSqlWherePageIds = '';
        if (count($arrPageIds) > 0) {
            $strSqlWherePageIds = ' WHERE pages.id IN (' . implode(',', $arrPageIds) . ')';
        }

        $sqlStmt = $this->core->dbh->query('SELECT pages.id,
                                            ' . $strSqlInstanceFields . '
                                          FROM pages
                                            INNER JOIN `page-' . $strGenForm . '-Instances` ON
                                              `page-' . $strGenForm . '-Instances`.pageId = pages.pageId AND
                                              `page-' . $strGenForm . '-Instances`.version = pages.version AND
                                              `page-' . $strGenForm . '-Instances`.idLanguages = ?                                         
                                            ' . $strSqlWherePageIds, array($this->intLanguageId));

        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }


    /**
     * loadPageInstanceDataById
     * @param integer $intPageId
     * @param string $strGenForm
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadPageInstanceDataById($intPageId, $strGenForm)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadPageInstanceDataById(' . $intPageId . ', ' . $strGenForm . ')');

        // FIXME : !!! iFl.idFields IN (5,55) -> define
        if ($strGenForm != '' && $strGenForm != '-' && strpos($strGenForm, $this->core->sysConfig->page_types->link->default_formId) === false) {
            $sqlStmt = $this->core->dbh->query('SELECT pages.id AS pId, `page-' . $strGenForm . '-Instances`.*,
                                            files.filename, fileTitles.title AS filetitle, urls.url
                                          FROM pages
                                          LEFT JOIN `page-' . $strGenForm . '-Instances` ON
                                            `page-' . $strGenForm . '-Instances`.pageId = pages.pageId AND
                                            `page-' . $strGenForm . '-Instances`.version = pages.version AND
                                            `page-' . $strGenForm . '-Instances`.idLanguages = ?
                                          LEFT JOIN `page-' . $strGenForm . '-InstanceFiles` AS iFiles ON
                                            iFiles.id = (SELECT iFl.id FROM `page-' . $strGenForm . '-InstanceFiles` AS iFl
                                                         WHERE iFl.pageId = pages.pageId AND iFl.version = pages.version AND iFl.idFields IN (5,55)
                                                         ORDER BY iFl.idFields DESC LIMIT 1)
                                          LEFT JOIN files ON
                                            files.id = iFiles.idFiles AND
                                            files.isImage = 1
                                          LEFT JOIN fileTitles ON
                                            fileTitles.idFiles = files.id AND
                                            fileTitles.idLanguages = ?
                                          LEFT JOIN urls ON
                                            urls.relationId = pages.pageId AND
                                            urls.version = pages.version AND
                                            urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND
                                            urls.idLanguages = ? AND
                                            urls.idParent IS NULL AND
                                            urls.isMain = 1
                                          WHERE pages.id = ?', array($this->intLanguageId, $this->intLanguageId, $this->intLanguageId, $intPageId));

            return $sqlStmt->fetch(Zend_Db::FETCH_OBJ);
        }
    }

    /**
     * loadPageFilesById
     * @param integer $intPageId
     * @param string $strGenForm
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadPageFilesById($intPageId, $strGenForm)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadPageFilesById(' . $intPageId . ',' . $strGenForm . ')');

        $sqlStmt = $this->core->dbh->query('SELECT files.id, files.filename, fileTitles.title AS filetitle
                                          FROM pages                                          
                                          LEFT JOIN `page-' . $strGenForm . '-InstanceFiles` AS iFiles ON
                                            iFiles.id IN (SELECT iFl.id FROM `page-' . $strGenForm . '-InstanceFiles` AS iFl
                                                          WHERE iFl.pageId = pages.pageId AND iFl.version = pages.version AND iFl.idFields = 5
                                                          ORDER BY iFl.idFields DESC)
                                          LEFT JOIN files ON
                                            files.id = iFiles.idFiles AND
                                            files.isImage = 1
                                          LEFT JOIN fileTitles ON
                                            fileTitles.idFiles = files.id AND
                                            fileTitles.idLanguages = ?
                                          WHERE pages.id = ?', array($this->intLanguageId, $intPageId));

        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * deletePageLink
     * @author Thomas Schedler <tsh@massiveart.com>
     * @param integer $intElementId
     * @version 1.0
     */
    public function deletePageLink($intElementId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->deletePageLink(' . $intElementId . ')');

        $this->getPageLinksTable();

        $strWhere = $this->objPageLinksTable->getAdapter()->quoteInto('idPages = ?', $intElementId);
        return $this->objPageLinksTable->delete($strWhere);
    }

    /**
     * deleteInternalLinks
     * @param string $strElementId
     * @param integer $intVersion
     * @param integer $intFieldId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @param integer $intElementId
     * @version 1.0
     */
    public function deleteInternalLinks($strElementId, $intVersion, $intFieldId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->deleteInternalLinks(' . $strElementId . ',' . $intVersion . ',' . $intFieldId . ')');

        $strWhere = $this->getPageInternalLinksTable()->getAdapter()->quoteInto('pageId = ?', $strElementId);
        $strWhere .= $this->objPageInternalLinksTable->getAdapter()->quoteInto(' AND version = ?', $intVersion);
        $strWhere .= $this->objPageInternalLinksTable->getAdapter()->quoteInto(' AND idFields = ?', $intFieldId);
        $strWhere .= $this->objPageInternalLinksTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);

        return $this->objPageInternalLinksTable->delete($strWhere);
    }

    /**
     * deletePageCollection
     * @param string $strElementId
     * @param integer $intVersion
     * @author Thomas Schedler <tsh@massiveart.com>
     * @param integer $intElementId
     * @version 1.0
     */
    public function deletePageCollection($strElementId, $intVersion)
    {
        $this->core->logger->debug('cms->models->Model_Pages->deletePageCollection(' . $strElementId . ',' . $intVersion . ')');

        $strWhere = $this->getPageCollectionTable()->getAdapter()->quoteInto('pageId = ?', $strElementId);
        $strWhere .= $this->objPageCollectionTable->getAdapter()->quoteInto(' AND version = ?', $intVersion);
        $strWhere .= $this->objPageCollectionTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);

        return $this->objPageCollectionTable->delete($strWhere);
    }

    /**
     * deletePageCollectionUrls
     * @param integer $intParentId
     * @param integer $intParentTypeId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @param integer $intElementId
     * @version 1.0
     */
    public function deletePageCollectionUrls($intParentId, $intParentTypeId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->deletePageCollectionUrls(' . $intParentId . ',' . $intParentTypeId . ')');

        $strWhere = $this->getPageUrlTable()->getAdapter()->quoteInto('idParent = ?', $intParentId);
        $strWhere .= $this->objPageUrlTable->getAdapter()->quoteInto(' AND idParentTypes = ?', $intParentTypeId);
        $strWhere .= $this->objPageUrlTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);

        return $this->objPageUrlTable->delete($strWhere);
    }

    /**
     * loadPageLink
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadLinkPage($intElementId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadLinkPage(' . $intElementId . ')');

        $objSelect = $this->getPageTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('pages', array('id', 'pageId', 'version'));
        $objSelect->join('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = ' . $this->intLanguageId, array('title'));
        $objSelect->joinleft('urls', 'urls.relationId = pages.pageId AND urls.version = pages.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND urls.idLanguages = ' . $this->intLanguageId . ' AND urls.isMain = 1 AND urls.idParent IS NULL', array('url'));
        $objSelect->joinleft('languages', 'languages.id = urls.idLanguages', array('languageCode'));
        $objSelect->where('pages.id = ?', $intElementId);

        return $this->objPageTable->fetchAll($objSelect);
    }

    /**
     * deletePage
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @param integer $intElementId
     * @version 1.0
     */
    public function deletePage($intElementId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->deletePage(' . $intElementId . ')');

        $this->getPageTable();

        $objPageData = $this->load($intElementId);

        if (count($objPageData) > 0) {
            $objPage = $objPageData->current();
            $strIndexPath = GLOBAL_ROOT_PATH . $this->core->sysConfig->path->search_index->page . '/' . sprintf('%02d', $this->intLanguageId);
            $strPageId = $objPage->pageId;

            if (count(scandir($strIndexPath)) > 2) {
                $this->objIndex = Zend_Search_Lucene::open($strIndexPath);

                $objTerm = new Zend_Search_Lucene_Index_Term($strPageId . '_*', 'key');
                $objQuery = new Zend_Search_Lucene_Search_Query_Wildcard($objTerm);

                $objHits = $this->objIndex->find($objQuery);

                foreach ($objHits as $objHit) {
                    $this->objIndex->delete($objHit->id);
                }

                $this->objIndex->commit();
            }

            $strWhere = $this->objPageTable->getAdapter()->quoteInto('relationId = ?', $strPageId);
            $strWhere .= $this->objPageTable->getAdapter()->quoteInto(' AND idUrlTypes = ?', $this->core->sysConfig->url_types->page);
            $this->getPageUrlTable()->delete($strWhere);
        }

        $strWhere = $this->objPageTable->getAdapter()->quoteInto('id = ?', $intElementId);
        return $this->objPageTable->delete($strWhere);
    }

    /**
     * loadUrl
     * @param string $strPageId
     * @param integer $intVersion
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadUrl($strPageId, $intVersion)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadUrl(' . $strPageId . ', ' . $intVersion . ')');

        $objSelect = $this->getPageUrlTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objPageUrlTable, array('url'));
        $objSelect->join('pages', 'pages.pageId = urls.relationId', array('isStartPage'));
        $objSelect->joinleft('folders', 'pages.idParent = folders.id AND pages.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array('depth', 'idParentFolder'));
        $objSelect->join('languages', 'languages.id = urls.idLanguages', array('languageCode'));
        $objSelect->where('urls.relationId = ?', $strPageId)
            ->where('urls.version = ?', $intVersion)
            ->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->page)
            ->where('urls.idLanguages = ?', $this->intLanguageId)
            ->where('urls.isMain = 1')
            ->where('urls.idParent IS NULL');

        return $this->objPageUrlTable->fetchAll($objSelect);
    }

    /**
     * loadUrlHistory
     * @param str $strPageId
     * @param integer $intLanguageId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Dominik Mößlang <dmo@massiveart.com>
     * @version 1.0
     */
    public function loadUrlHistory($intPageId, $intLanguageId, $blnLandingPages = false)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadPageUrlHistory(' . $intPageId . ', ' . $intLanguageId . ')');

        $objSelect = $this->getPageTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objPageTable, array('pageId', 'relationId' => 'pageId', 'version', 'isStartpage'))
            ->join('urls', 'urls.relationId = pages.pageId AND urls.version = pages.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND urls.idLanguages = ' . $intLanguageId . ' AND urls.isMain = 0 AND urls.idParent IS NULL', array('id', 'url'))
            ->join('languages', 'languages.id = urls.idLanguages', array('languageCode'))
            ->joinLeft('folders', 'folders.id = pages.idParent AND pages.idParentTypes = '.$this->core->sysConfig->parent_types->folder, array('idRootLevels'))
            ->joinLeft('rootLevels', 'rootLevels.id = folders.idRootLevels', array('languageDefinitionType'))
            ->joinLeft(array('rl' => 'rootLevels'), 'rl.id = pages.idParent AND pages.idParentTypes = '.$this->core->sysConfig->parent_types->rootlevel, array('languageDefinitionType AS altLanguageDefinitionType'))
            ->where('pages.id = ?', $intPageId)
            ->where('urls.isLandingPage = ?', (int) $blnLandingPages);

        return $this->objPageTable->fetchAll($objSelect);
    }

    /**
     * loadParentUrl
     * @param integer $intPageId
     * @param boolean $blnIsStartElement
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadParentUrl($intPageId, $blnIsStartElement)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadParentUrl(' . $intPageId . ',' . $blnIsStartElement . ')');

        $objSelect = $this->getPageUrlTable()->select();
        $objSelect->setIntegrityCheck(false);

        if ($blnIsStartElement == true) {
            $objSelect->from($this->objPageUrlTable, array('url', 'id'));
            $objSelect->join('pages', 'pages.pageId = urls.relationId', array('pageId', 'version', 'isStartpage'));
            $objSelect->join('folders', 'folders.id = (SELECT idParent FROM pages WHERE id = ' . $intPageId . ')', array());
            $objSelect->where('urls.version = pages.version')
                ->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->page)
                ->where('urls.idLanguages = ?', $this->intLanguageId)
                ->where('urls.isMain = 1')
                ->where('pages.idParentTypes = ?', $this->core->sysConfig->parent_types->folder)
                ->where('pages.idParent = folders.idParentFolder')
                ->where('pages.isStartPage = 1');
        } else {
            $objSelect->from($this->objPageUrlTable, array('url', 'id'));
            $objSelect->join('pages', 'pages.pageId = urls.relationId', array('pageId', 'version', 'isStartpage'));
            $objSelect->where('urls.version = pages.version')
                ->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->page)
                ->where('urls.idLanguages = ?', $this->intLanguageId)
                ->where('urls.isMain = 1')
                ->where('pages.idParentTypes = ?', $this->core->sysConfig->parent_types->folder)
                ->where('pages.idParent = (SELECT idParent FROM pages WHERE id = ' . $intPageId . ')')
                ->where('pages.isStartPage = 1');
        }

        return $this->objPageUrlTable->fetchAll($objSelect);
    }

    /**
     * getChildUrls
     * @param integer $intParentId
     * @return void
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getChildUrls($intParentId)
    {

        $objSelect = $this->getPageTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objPageTable, array('id', 'pageId', 'relationId' => 'pageId', 'version'))
            ->joinInner('urls', 'urls.relationId = pages.pageId AND urls.version = pages.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND urls.idLanguages = ' . $this->intLanguageId . ' AND urls.isMain = 1', array('id', 'url'))
            ->joinInner('folders AS parent', 'parent.id = ' . $intParentId, array())
            ->joinInner('folders', 'folders.lft BETWEEN parent.lft AND parent.rgt AND folders.idRootLevels = parent.idRootLevels', array())
            ->where('pages.idParent = folders.id')
            ->where('pages.idParentTypes = ?', $this->core->sysConfig->parent_types->folder);

        return $this->objPageTable->fetchAll($objSelect);
    }

    /**
     * loadByUrl
     * @param integer $intRootLevelId
     * @param string $strUrl
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadByUrl($intRootLevelId, $strUrl)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadByUrl(' . $intRootLevelId . ', ' . $strUrl . ')');

        $sqlStmt = $this->core->dbh->query('SELECT pages.pageId, pages.version, urls.idLanguages, urls.idParent, urls.idParentTypes
                                          FROM urls
                                            INNER JOIN pages ON
                                              pages.pageId = urls.relationId AND
                                              pages.version = urls.version AND
                                              pages.idParentTypes = ?
                                            INNER JOIN folders ON
                                              folders.id = pages.idParent
                                            WHERE urls.url = ? AND
                                              urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND
                                              urls.idLanguages = ? AND
                                              folders.idRootLevels = ?
                                        UNION
                                        SELECT pages.pageId, pages.version, urls.idLanguages, urls.idParent, urls.idParentTypes
                                          FROM urls
                                            INNER JOIN pages ON
                                              pages.pageId = urls.relationId AND
                                              pages.version = urls.version AND
                                              pages.idParentTypes = ?
                                            INNER JOIN rootLevels ON
                                              rootLevels.id = pages.idParent
                                            WHERE urls.url = ? AND
                                              urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND
                                              urls.idLanguages = ? AND
                                              rootLevels.id = ?', array(
                                                                       $this->core->sysConfig->parent_types->folder,
                                                                       $strUrl,
                                                                       $this->intLanguageId,
                                                                       $intRootLevelId,
                                                                       $this->core->sysConfig->parent_types->rootlevel,
                                                                       $strUrl,
                                                                       $this->intLanguageId,
                                                                       $intRootLevelId
                                                                  ));

        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadAllPublicPages
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadAllPublicPages()
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadAllPublicPages()');

        $objSelect = $this->getPageUrlTable()->select()->distinct();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objPageUrlTable, array('pages.pageId', 'version', 'idLanguages'));
        $objSelect->join('pages', 'pages.pageId = urls.relationId AND pages.version = urls.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page, array('idParent'));
        $objSelect->join('pageProperties', 'pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version AND pageProperties.idLanguages = urls.idLanguages', array());
        $objSelect->joinleft('folders', 'pages.idParent = folders.id AND pages.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array('idRootLevels'));
        $objSelect->where('pageProperties.idStatus = ?', $this->core->sysConfig->status->live)
            ->where('pageProperties.idPageTypes != ?', $this->core->sysConfig->page_types->link->id)
            ->where('pageProperties.idPageTypes != ?', $this->core->sysConfig->page_types->external->id);

        return $this->objPageUrlTable->fetchAll($objSelect);
    }

    /**
     * loadVideo
     * @param string $intElementId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadVideo($intElementId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadVideo(' . $intElementId . ')');

        $objSelect = $this->getPageVideosTable()->select();
        $objSelect->from($this->objPageVideosTable, array('userId', 'videoId', 'idVideoTypes', 'thumb', 'title'));
        $objSelect->join('pages', 'pages.pageId = pageVideos.pageId AND pages.version = pageVideos.version', array());
        $objSelect->where('pages.id = ?', $intElementId)
            ->where('idLanguages = ?', $this->intLanguageId);

        return $this->objPageVideosTable->fetchAll($objSelect);
    }

    /**
     * addVideo
     * @param  integer $intElementId
     * @param  mixed $mixedVideoId
     * @param  integer $intVideoTypeId
     * @param  string $strVideoUserId
     * @param  string $strVideoThumb
     * @param  string $strVideoTitle
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addVideo($intElementId, $mixedVideoId, $intVideoTypeId, $strVideoUserId, $strVideoThumb, $strVideoTitle)
    {
        $this->core->logger->debug('cms->models->Model_Pages->addVideo(' . $intElementId . ',' . $mixedVideoId . ',' . $intVideoTypeId . ',' . $strVideoUserId . ',' . $strVideoThumb . ',' . $strVideoTitle . ')');

        $objPageData = $this->load($intElementId);

        if (count($objPageData) > 0) {
            $objPage = $objPageData->current();

            $this->getPageVideosTable();

            $strWhere = $this->objPageVideosTable->getAdapter()->quoteInto('pageId = ?', $objPage->pageId);
            $strWhere .= ' AND ' . $this->objPageVideosTable->getAdapter()->quoteInto('version = ?', $objPage->version);
            $strWhere .= ' AND ' . $this->objPageVideosTable->getAdapter()->quoteInto('idLanguages = ?', $this->intLanguageId);
            $this->objPageVideosTable->delete($strWhere);

            if ($mixedVideoId != '') {
                $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
                $arrData = array(
                    'pageId'       => $objPage->pageId,
                    'version'      => $objPage->version,
                    'idLanguages'  => $this->intLanguageId,
                    'userId'       => $strVideoUserId,
                    'videoId'      => $mixedVideoId,
                    'idVideoTypes' => $intVideoTypeId,
                    'thumb'        => $strVideoThumb,
                    'title'        => $strVideoTitle,
                    'creator'      => $intUserId
                );
                return $objSelect = $this->objPageVideosTable->insert($arrData);
            }
        }
    }

    /**
     * removeVideo
     * @param  integer $intElementId
     * @return integer affected rows
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function removeVideo($intElementId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->removeVideo(' . $intElementId . ')');

        $objPageData = $this->load($intElementId);

        if (count($objPageData) > 0) {
            $objPage = $objPageData->current();

            $this->getPageVideosTable();

            $strWhere = $this->objPageVideosTable->getAdapter()->quoteInto('pageId = ?', $objPage->pageId);
            $strWhere .= ' AND ' . $this->objPageVideosTable->getAdapter()->quoteInto('version = ?', $objPage->version);
            $strWhere .= ' AND ' . $this->objPageVideosTable->getAdapter()->quoteInto('idLanguages = ?', $this->intLanguageId);

            return $this->objPageVideosTable->delete($strWhere);
        }
    }

    /**
     * loadContacts
     * @param string $intElementId
     * @param  integer $intFieldId
     * @return string
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadContacts($intElementId, $intFieldId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadContacts(' . $intElementId . ',' . $intFieldId . ')');

        $objSelect = $this->getPageContactsTable()->select();
        $objSelect->from($this->objPageContactsTable, array('idContacts'));
        $objSelect->join('pages', 'pages.pageId = pageContacts.pageId AND pages.version = pageContacts.version AND pageContacts.idLanguages = ' . $this->intLanguageId, array());
        $objSelect->where('pages.id = ?', $intElementId)
            ->where('idFields = ?', $intFieldId);

        $objSelect->order('pageContacts.id');

        $arrPageContactData = $this->objPageContactsTable->fetchAll($objSelect);

        $strContactIds = '';
        foreach ($arrPageContactData as $objPageContact) {
            $strContactIds .= '[' . $objPageContact->idContacts . ']';
        }

        return $strContactIds;
    }

    /**
     * loadGroups
     * @param string $intElementId
     * @param  integer $intFieldId
     * @return string
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadGroups($intElementId, $intFieldId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadContacts(' . $intElementId . ',' . $intFieldId . ')');

        $objSelect = $this->getPageGroupsTable()->select();
        $objSelect->from($this->objPageGroupsTable, array('idGroups'));
        $objSelect->join('pages', 'pages.pageId = pageGroups.pageId AND pages.version = pageGroups.version AND pageGroups.idLanguages = ' . $this->intLanguageId, array());
        $objSelect->where('pages.id = ?', $intElementId)
            ->where('idFields = ?', $intFieldId);

        $objSelect->order('pageGroups.id');

        $arrPageGroupData = $this->objPageGroupsTable->fetchAll($objSelect);

        $strGroupIds = '';
        foreach ($arrPageGroupData as $objPageGroup) {
            $strGroupIds .= '[' . $objPageGroup->idGroups . ']';
        }

        return $strGroupIds;
    }

    /**
     * addContact
     * @param  integer $intElementId
     * @param  string $strContactIds
     * @param  integer $intFieldId
     * @return Zend_Db_Table_Rowset_Abstract
     * @version 1.0
     */
    public function addContact($intElementId, $strContactIds, $intFieldId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->addContact(' . $intElementId . ',' . $strContactIds . ',' . $intFieldId . ')');

        $objPageData = $this->load($intElementId);

        if (count($objPageData) > 0) {
            $objPage = $objPageData->current();

            $this->getPageContactsTable();

            $strWhere = $this->objPageContactsTable->getAdapter()->quoteInto('pageId = ?', $objPage->pageId);
            $strWhere .= ' AND ' . $this->objPageContactsTable->getAdapter()->quoteInto('version = ?', $objPage->version);
            $strWhere .= ' AND ' . $this->objPageContactsTable->getAdapter()->quoteInto('idLanguages = ?', $this->intLanguageId);
            $strWhere .= ' AND ' . $this->objPageContactsTable->getAdapter()->quoteInto('idFields = ?', $intFieldId);
            $this->objPageContactsTable->delete($strWhere);

            $strContactIds = trim($strContactIds, '[]');
            $arrContactIds = explode('][', $strContactIds);

            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

            foreach ($arrContactIds as $sortPosition => $intContactId) {
                $arrData = array(
                    'pageId'       => $objPage->pageId,
                    'version'      => $objPage->version,
                    'idLanguages'  => $this->intLanguageId,
                    'sortPosition' => $sortPosition + 1,
                    'idContacts'   => $intContactId,
                    'idFields'     => $intFieldId,
                    'creator'      => $intUserId
                );
                $this->objPageContactsTable->insert($arrData);
            }
        }
    }

    /**
     * addGroup
     * @param  integer $intElementId
     * @param  string $strContactIds
     * @param  integer $intFieldId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function addGroup($intElementId, $strGroupIds, $intFieldId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->addGroup(' . $intElementId . ',' . $strGroupIds . ',' . $intFieldId . ')');

        $objPageData = $this->load($intElementId);

        if (count($objPageData) > 0 && $strGroupIds != '') {
            $objPage = $objPageData->current();

            $this->getPageGroupsTable();

            $strWhere = $this->objPageGroupsTable->getAdapter()->quoteInto('pageId = ?', $objPage->pageId);
            $strWhere .= ' AND ' . $this->objPageGroupsTable->getAdapter()->quoteInto('version = ?', $objPage->version);
            $strWhere .= ' AND ' . $this->objPageGroupsTable->getAdapter()->quoteInto('idLanguages = ?', $this->intLanguageId);
            $strWhere .= ' AND ' . $this->objPageGroupsTable->getAdapter()->quoteInto('idFields = ?', $intFieldId);
            $this->objPageGroupsTable->delete($strWhere);

            $strGroupIds = trim($strGroupIds, '[]');
            $arrGroupIds = explode('][', $strGroupIds);

            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

            foreach ($arrGroupIds as $sortPosition => $intGroupId) {
                $arrData = array(
                    'pageId'       => $objPage->pageId,
                    'version'      => $objPage->version,
                    'idLanguages'  => $this->intLanguageId,
                    'sortPosition' => $sortPosition + 1,
                    'idGroups'     => $intGroupId,
                    'idFields'     => $intFieldId,
                    'creator'      => $intUserId
                );
                $this->objPageGroupsTable->insert($arrData);
            }
        }
    }

    /**
     * loadParentFolders
     * @param integer $intPageId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadParentFolders($intPageId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadParentFolders(' . $intPageId . ')');

        $sqlStmt = $this->core->dbh->query('SELECT folders.id, folderProperties.isUrlFolder, folderTitles.title
                                          FROM folders
                                            INNER JOIN folderProperties ON 
                                                  folderProperties.folderId = folders.folderId AND 
                                                  folderProperties.version = folders.version AND 
                                                  folderProperties.idLanguages = ?
                                            INNER JOIN folderTitles ON
                                              folderTitles.folderId = folders.folderId AND
                                              folderTitles.version = folders.version AND
                                              folderTitles.idLanguages = ?
                                          ,folders AS parent
                                            INNER JOIN pages ON
                                              pages.id = ? AND
                                              parent.id = pages.idParent AND
                                              pages.idParentTypes = ?
                                           WHERE folders.lft <= parent.lft AND
                                                 folders.rgt >= parent.rgt AND
                                                 folders.idRootLevels = parent.idRootLevels
                                             ORDER BY folders.rgt', array($this->intLanguageId, $this->intLanguageId, $intPageId, $this->core->sysConfig->parent_types->folder));


        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadPagesByTemplatedId
     * @param integer $intTemplateId
     * @param integer $intQuarter
     * @param integer $intYear
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadPagesByTemplatedId($intTemplateId, $intQuarter = 0, $intYear = 0)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadPagesByTemplatedId(' . $intTemplateId . ')');

        $objSelect = $this->getPageTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('pages', array('id', 'pageId', 'version', 'pageProperties.created', 'pageProperties.changed', 'pageProperties.published'));
        $objSelect->join('pageProperties', 'pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version AND pageProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->join('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = ' . $this->intLanguageId, array('title'));
        $objSelect->join('genericForms', 'genericForms.id = pageProperties.idGenericForms', array('genericFormId', 'version AS genericFormVersion', 'idGenericFormTypes'));
        if ($intTemplateId == $this->core->sysConfig->page_types->page->event_templateId) {
            $objSelect->join('pageDatetimes', 'pageDatetimes.pageId = pages.pageId AND pageDatetimes.version = pages.version AND pageDatetimes.idLanguages = ' . $this->intLanguageId, array('datetime'));
        }
        $objSelect->joinleft('urls', 'urls.relationId = pages.pageId AND urls.version = pages.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND urls.idLanguages = ' . $this->intLanguageId . ' AND urls.isMain = 1 AND urls.idParent IS NULL', array('url'));
        $objSelect->joinleft('languages', 'languages.id = urls.idLanguages', array('languageCode'));
        $objSelect->where('pages.idTemplates = ?', $intTemplateId);
        if ($intTemplateId == $this->core->sysConfig->page_types->page->event_templateId) {
            $timestamp = time();
            if ($intQuarter > 0 && $intQuarter <= 4) {
                $intCurrQuarter = $intQuarter;
            } else {
                $intCurrQuarter = ceil(date('m', $timestamp) / 3);
            }

            if ($intYear > 0) {
                $intCurrYear = $intYear;
            } else {
                $intCurrYear = date('Y', $timestamp);
            }
            $objSelect->where('QUARTER(STR_TO_DATE(pageDatetimes.datetime, \'%d.%m.%Y\')) = ?', $intCurrQuarter);
            $objSelect->where('SUBSTRING(STR_TO_DATE(pageDatetimes.datetime, \'%d.%m.%Y\'),1,4) = ?', $intCurrYear);
        }
        if (!isset($_SESSION['sesTestMode']) || (isset($_SESSION['sesTestMode']) && $_SESSION['sesTestMode'] == false)) {
            $timestamp = time();
            $now = date('Y-m-d H:i:s', $timestamp);
            $objSelect->where('pageProperties.idStatus = ?', $this->core->sysConfig->status->live);
            $objSelect->where('pageProperties.published <= \'' . $now . '\'');
        }
        if ($intTemplateId == $this->core->sysConfig->page_types->page->event_templateId) {
            $objSelect->order('STR_TO_DATE(pageDatetimes.datetime, \'%d.%m.%Y\') ASC');
        }

        return $this->objPageTable->fetchAll($objSelect);
    }

    /**
     * loadPagesByCategory
     * @param integer $intCategoryId
     * @param integer $intLabelId
     * @param integer $intLimitNumber
     * @param integer $intSortTypeId
     * @param integer $intSortOrderId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadPagesByCategory($intRootLevelId, $intCategoryId = 0, $intLabelId = 0, $intLimitNumber = 0, $intSortTypeId = 0, $intSortOrderId = 0)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadPagesByCategory(' . $intRootLevelId . ',' . $intCategoryId . ',' . $intLabelId . ',' . $intLimitNumber . ',' . $intSortTypeId . ',' . $intSortOrderId . ')');

        $strSortOrder = '';
        if ($intSortOrderId > 0 && $intSortOrderId != '') {
            switch ($intSortOrderId) {
                case $this->core->sysConfig->sort->orders->asc->id:
                    $strSortOrder = 'ASC';
                    break;
                case $this->core->sysConfig->sort->orders->desc->id:
                    $strSortOrder = 'DESC';
                    break;
            }
        }

        $strSqlOrderBy = '';
        if ($intSortTypeId > 0 && $intSortTypeId != '') {
            switch ($intSortTypeId) {
                case $this->core->sysConfig->sort->types->manual_sort->id:
                    $strSqlOrderBy = ' ORDER BY pages.sortPosition ' . $strSortOrder . ', pages.sortTimestamp ' . (($strSortOrder == 'DESC') ? 'ASC' : 'DESC') . ', pages.id ASC';
                    break;
                case $this->core->sysConfig->sort->types->created->id:
                    $strSqlOrderBy = ' ORDER BY pageProperties.created ' . $strSortOrder;
                    break;
                case $this->core->sysConfig->sort->types->changed->id:
                    $strSqlOrderBy = ' ORDER BY pageProperties.changed ' . $strSortOrder;
                    break;
                case $this->core->sysConfig->sort->types->published->id:
                    $strSqlOrderBy = ' ORDER BY pageProperties.published ' . $strSortOrder;
                    break;
                case $this->core->sysConfig->sort->types->alpha->id:
                    $strSqlOrderBy = ' ORDER BY pageTitles.title ' . $strSortOrder;
            }
        }

        $strSqlCategoryTitle = '';
        $strSqlCategory = '';
        $strSqlCategoryTitleJoin = '';
        if ($intCategoryId > 0 && $intCategoryId != '') {
            $strSqlCategoryTitle = ' categoryTitles.title AS catTitle,';
            $strSqlCategory = ' INNER JOIN pageCategories ON
                            pageCategories.pageId = pages.pageId AND
                            pageCategories.version = pages.version AND
                            pageCategories.category = ' . $intCategoryId;
            $strSqlCategoryTitleJoin = '
    	                     LEFT JOIN categoryTitles ON
                             categoryTitles.idCategories = pageCategories.category';
        }

        $strSqlLabel = '';
        if ($intLabelId > 0 && $intLabelId != '') {
            $strSqlLabel = ' INNER JOIN pageLabels ON
                         pageLabels.pageId = pages.pageId AND
                         pageLabels.version = pages.version AND
                         pageLabels.label = ' . $intLabelId;
        }

        $strSqlLimit = '';
        if ($intLimitNumber > 0 && $intLimitNumber != '') {
            $strSqlLimit = ' LIMIT ' . $intLimitNumber;
        }

        $strPageFilter = '';
        $strPublishedFilter = '';
        if (!isset($_SESSION['sesTestMode']) || (isset($_SESSION['sesTestMode']) && $_SESSION['sesTestMode'] == false)) {
            $timestamp = time();
            $now = date('Y-m-d H:i:s', $timestamp);
            $strPageFilter = ' AND pageProperties.idStatus = ' . $this->core->sysConfig->status->live;
            $strPublishedFilter = ' AND pageProperties.published <= \'' . $now . '\'';
        }

        if ($intRootLevelId > 0 && $intRootLevelId != '') {
            $sqlStmt = $this->core->dbh->query('SELECT DISTINCT pages.id, pages.pageId, pages.version, pageProperties.created, pageProperties.published,
                                          genericForms.genericFormId, genericForms.version AS genericFormVersion, genericForms.idGenericFormTypes,
                                          pageVideos.videoId, pageVideos.thumb, pageVideos.title AS videoTitle, pageVideos.idVideoTypes,
                                          ' . $strSqlCategoryTitle . ' pageTitles.title
                                        FROM pages
                                        INNER JOIN pageProperties ON 
                                          pageProperties.pageId = pages.pageId AND 
                                          pageProperties.version = pages.version AND 
                                          pageProperties.idLanguages = ?                                        
                                        ' . $strSqlCategory . '
                                        ' . $strSqlLabel . '
                                        INNER JOIN folders ON
                                          folders.id = pages.idParent
                                        INNER JOIN genericForms ON
                                          genericForms.id = pageProperties.idGenericForms
                                        INNER JOIN pageVideos ON
                                          pageVideos.pageId = pages.pageId AND
                                          pageVideos.version = pages.version AND
                                          pageVideos.idLanguages = ?
                                        ' . $strSqlCategoryTitleJoin . '
                                        LEFT JOIN pageTitles ON
                                          pageTitles.pageId = pages.pageId AND
                                          pageTitles.version = pages.version AND
                                          pageTitles.idLanguages = ?
                                        WHERE NOT genericForms.genericFormId = ? AND
                                          folders.idRootLevels = ?
                                        ' . $strPageFilter . '
                                        ' . $strPublishedFilter . '
                                        ' . $strSqlOrderBy . '
                                        ' . $strSqlLimit, array(
                                                               $this->intLanguageId,
                                                               $this->intLanguageId,
                                                               $this->intLanguageId,
                                                               $this->core->sysConfig->page_types->link->default_formId,
                                                               $intRootLevelId
                                                          ));

            return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
        }
    }

    /**
     * loadGlobalParentPages
     * @param integer $intGlobalType
     * @author Thomas Schedler <tsh@massiveart.com>
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function loadGlobalParentPages($intGlobalType)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadGlobalParentPages(' . $intGlobalType . ')');

        $objSelect = $this->getPageTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('pages', array('id', 'pageId', 'version', 'idParent', 'pageProperties.created', 'pageProperties.changed', 'pageProperties.published'))
            ->join('globalTypePageTypes', 'globalTypePageTypes.idGlobalTypes = ' . $this->core->dbh->quote($intGlobalType, Zend_Db::INT_TYPE), array())
            ->join('pageProperties', 'globalTypePageTypes.idPageTypes = pageProperties.idPageTypes AND pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version AND pageProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array())
            ->join('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('title'))
            ->join('genericForms', 'genericForms.id = pageProperties.idGenericForms', array('genericFormId', 'version AS genericFormVersion', 'idGenericFormTypes'))
            ->join('urls', 'urls.relationId = pages.pageId AND urls.version = pages.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND urls.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE) . ' AND urls.isMain = 1 AND urls.idParent IS NULL', array('url'))
            ->joinleft('folders', 'pages.idParent = folders.id AND pages.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array('idRootLevels'))
            ->join('languages', 'languages.id = urls.idLanguages', array('languageCode'))
            ->where('pageProperties.idStatus = ?', $this->core->sysConfig->status->live)
            ->where('pageProperties.published <= \'' . date('Y-m-d H:i:s') . '\'');

        return $this->objPageTable->fetchAll($objSelect);
    }

    /**
     * getElementsByIds
     * @param string $strElementIds
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getElementsByIds($strElementIds)
    {
        $this->core->logger->debug('cms->models->Model_Pages->getElementsByRootLevelIdsAndElementIds("' . $strElementIds . '")');

        $objSelect = $this->getPageTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('pages', array('id', 'relationId' => 'pageId', 'version', 'idParent', 'idParentTypes', 'isStartElement' => 'isStartPage', 'elementType' => new Zend_Db_Expr('"page"')))
            ->joinLeft('pageProperties', 'pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version AND pageProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('created', 'changed', 'published', 'idStatus'))
            ->joinLeft('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('title'))
            ->joinLeft(array('alternativeTitle' => 'pageTitles'), 'alternativeTitle.pageId = pages.pageId AND alternativeTitle.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('alternativeTitle' => 'title'))
            ->joinLeft(array('fallbackTitle' => 'pageTitles'), 'fallbackTitle.pageId = pages.pageId AND fallbackTitle.idLanguages = 0', array('fallbackTitle' => 'title'))
            ->joinleft('folders', 'folders.id = pages.idParent AND pages.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array('idRootLevels'));
        if (strpos($strElementIds, ',') !== false) {
            $objSelect->where('pages.id IN (' . $strElementIds . ')');
        } else {
            $objSelect->where('pages.id = ?', (int) $strElementIds);
        }

        return $this->objPageTable->fetchAll($objSelect);
    }

    /**
     * loadDynFormEntries
     * @param number $intElementId
     * @param number $intLanguageId
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function loadDynFormEntries($intElementId, $blnReturnSelect = false, $intFrom = 0, $intTo = 0, $strStartDate = '', $strEndDate = '')
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadDynFormEntries(' . $intElementId . ')');

        $objSelect = $this->getPageDynFormTable()->select()->setIntegrityCheck(false);

        $objSelect->from('pageDynForm', array('id', 'content', 'created'))
            ->where('idPages = ?', $intElementId);
        if ($strStartDate != '' && $strEndDate != '') {
            $strStartDate = explode('.', $strStartDate);
            $strStartDate = $strStartDate[2] . '-' . $strStartDate[1] . '-' . $strStartDate[0];
            $strEndDate = explode('.', $strEndDate);
            $strEndDate = $strEndDate[2] . '-' . $strEndDate[1] . '-' . $strEndDate[0];

            $objSelect->where('created >= ?', $strStartDate);
            $objSelect->where('created <= ?', $strEndDate);
        }
        $objSelect->order('created DESC');
        if ($intFrom != 0 && $intTo != 0) {
            $objSelect->limit($intTo - $intFrom + 1, $intFrom - 1);
        }

        if (!$blnReturnSelect) {
            return $this->getPageDynFormTable()->fetchAll($objSelect);
        } else {
            return $objSelect;
        }
    }

    public function loadProperties($intElementId, $intLanguageId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->countPageProperties(' . $intElementId . ', ' . $intLanguageId . ')');

        $objSelect = $this->getPageTable()->select()->setIntegrityCheck(false);

        $objSelect->from('pages', array())
            ->join('pageProperties', 'pages.pageId = pageProperties.pageId')
            ->where('pages.id = ?', $intElementId)
            ->where('pageProperties.idLanguages = ?', $intLanguageId);

        return $this->getPageTable()->fetchAll($objSelect);
    }

    /**
     * getModelFolders
     * @return Model_Folders
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelFolders()
    {
        if (null === $this->objModelFolders) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Folders.php';
            $this->objModelFolders = new Model_Folders();
            $this->objModelFolders->setLanguageId($this->intLanguageId);
        }

        return $this->objModelFolders;
    }

    /**
     * getPageTable
     * @return Zend_Db_Table_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getPageTable()
    {

        if ($this->objPageTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/tables/Pages.php';
            $this->objPageTable = new Model_Table_Pages();
        }

        return $this->objPageTable;
    }

    /**
     * getPagePropertyTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getPagePropertyTable()
    {

        if ($this->objPagePropertyTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/tables/PageProperties.php';
            $this->objPagePropertyTable = new Model_Table_PageProperties();
        }

        return $this->objPagePropertyTable;
    }

    /**
     * getPageUrlTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getPageUrlTable()
    {

        if ($this->objPageUrlTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/Urls.php';
            $this->objPageUrlTable = new Model_Table_Urls();
        }

        return $this->objPageUrlTable;
    }

    /**
     * getPageLinksTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getPageLinksTable()
    {

        if ($this->objPageLinksTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/tables/PageLinks.php';
            $this->objPageLinksTable = new Model_Table_PageLinks();
        }

        return $this->objPageLinksTable;
    }

    /**
     * getPageInternalLinksTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getPageInternalLinksTable()
    {

        if ($this->objPageInternalLinksTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/tables/PageInternalLinks.php';
            $this->objPageInternalLinksTable = new Model_Table_PageInternalLinks();
        }

        return $this->objPageInternalLinksTable;
    }

    /**
     * getPageCollectionTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getPageCollectionTable()
    {

        if ($this->objPageCollectionTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/tables/PageCollections.php';
            $this->objPageCollectionTable = new Model_Table_PageCollections();
        }

        return $this->objPageCollectionTable;
    }

    /**
     * getPageVideosTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getPageVideosTable()
    {

        if ($this->objPageVideosTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/tables/PageVideos.php';
            $this->objPageVideosTable = new Model_Table_PageVideos();
        }

        return $this->objPageVideosTable;
    }

    /**
     * getPageContactsTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getPageContactsTable()
    {

        if ($this->objPageContactsTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/tables/PageContacts.php';
            $this->objPageContactsTable = new Model_Table_PageContacts();
        }

        return $this->objPageContactsTable;
    }

    /**
     * getPageGroupsTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getPageGroupsTable()
    {

        if ($this->objPageGroupsTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/tables/PageGroups.php';
            $this->objPageGroupsTable = new Model_Table_PageGroups();
        }

        return $this->objPageGroupsTable;
    }

    /**
     * getPageDynFormTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getPageDynFormTable()
    {

        if ($this->objPageDynFormsTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/tables/PageDynForms.php';
            $this->objPageDynFormsTable = new Model_Table_PageDynForms();
        }

        return $this->objPageDynFormsTable;
    }

    /**
     * getGroupsTable
     * @return Model_Table_Contacts $objContactsTable
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getGroupsTable()
    {

        if ($this->groupsTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'users/models/tables/Groups.php';
            $this->groupsTable = new Model_Table_Groups();
        }
        return $this->groupsTable;
    }

    /**
     * Returns a table for a plugin
     * @param string $type The type of the plugin
     * @return Zend_Db_Table_Abstract
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getPluginTable($type)
    {
        require_once(GLOBAL_ROOT_PATH . 'application/plugins/' . $type . '/data/models/Page' . $type . '.php');
        $strClass = 'Model_Table_Page' . $type;
        return new $strClass();
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

}

?>
