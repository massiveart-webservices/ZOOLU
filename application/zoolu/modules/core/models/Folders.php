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
 * Model_Folders
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-17: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Model_Folders extends ModelAbstract
{

    private $intLanguageId;
    private $intContentLanguageId = 1;
    private $intSegmentId;

    /**
     * @var Model_Table_Folders
     */
    protected $objFolderTable;

    /**
     * @var Model_Table_FolderProperties
     */
    protected $objFolderPropertyTable;

    /**
     * @var Model_Table_FolderPermissions
     */
    protected $objFolderPermissionTable;

    /**
     * @var Model_Table_RootLevels
     */
    protected $objRootLevelTable;

    /**
     * @var Model_Table_RootLevelUrls
     */
    protected $objRootLevelUrlTable;

    /**
     * @var Model_Table_RootLevelPermissions
     */
    protected $objRootLevelPermissionTable;

    /**
     * @var Core
     */
    private $core;

    private $GLOBAL_ENTRY_GENFORMS = array('DEFAULT_PRODUCT_TREE');

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
     * load
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function load($intElementId)
    {
        $this->core->logger->debug('core->models->Model_Folders->load(' . $intElementId . ')');

        $objSelect = $this->getFolderTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('folders', array('id', 'folderId', 'relationId' => 'folderId', 'idSegments', 'idRootLevels', 'version'));
        $objSelect->joinLeft('folderProperties', 'folderProperties.folderId = folders.folderId AND folderProperties.version = folders.version AND folderProperties.idLanguages = ' . $this->intLanguageId, array('idFolderTypes', 'showInNavigation', 'hideInSitemap', 'showInWebsite', 'showInTablet', 'showInMobile', 'idStatus', 'isUrlFolder', 'published', 'changed', 'creator'));
        $objSelect->joinLeft(array('ub' => 'users'), 'ub.id = folderProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'));
        $objSelect->joinLeft(array('uc' => 'users'), 'uc.id = folderProperties.idUsers', array('changeUser' => 'CONCAT(uc.fname, \' \', uc.sname)'));
        $objSelect->where('folders.id = ?', $intElementId);

        return $this->getFolderTable()->fetchAll($objSelect);
    }

    /**
     * add
     * @param GenericSetup $objGenericSetup
     * @return stdClass Folder
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function add(GenericSetup $objGenericSetup)
    {
        $this->core->logger->debug('cms->models->Model_Folders->add()');

        $objFolder = new stdClass();
        $objFolder->folderId = uniqid();
        $objFolder->version = 1;
        $objFolder->sortPosition = GenericSetup::DEFAULT_SORT_POSITION;
        $objFolder->parentId = $objGenericSetup->getParentId();
        $objFolder->rootLevelId = $objGenericSetup->getRootLevelId();

        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

        /**
         * check if parent element is rootlevel or folder and get sort position
         */
        if ($objGenericSetup->getParentId() != '' && $objGenericSetup->getParentId() > 0) {
            $objData = $this->countChilds($objFolder->parentId);
        } else {
            $objData = $this->countRootChilds($objFolder->rootLevelId);
        }

        if (count($objData) == 1) {
            $objFolder->sortPosition = current($objData)->counter + 1;
        }

        /**
         * insert main data
         */
        $arrMainData = array(
            'folderId'          => $objFolder->folderId,
            'version'           => $objFolder->version,
            'idSegments'        => $objGenericSetup->getSegmentId(),
            'sortPosition'      => $objFolder->sortPosition,
            'sortTimestamp'     => date('Y-m-d H:i:s'),
            'idUsers'           => $intUserId,
            'creator'           => $objGenericSetup->getCreatorId(),
            'created'           => date('Y-m-d H:i:s')
        );
        /**
         * add folder node to the "Nested Set Model"
         */
        $objFolder->id = $this->addFolderNode($objFolder->rootLevelId,
            $objFolder->parentId,
            $arrMainData);

        /**
         * insert language specific properties
         */
        $arrProperties = array(
            'folderId'           => $objFolder->folderId,
            'version'            => $objFolder->version,
            'idLanguages'        => $this->intLanguageId,
            'idGenericForms'     => $objGenericSetup->getGenFormId(),
            'idFolderTypes'      => $this->core->sysConfig->folder_types->folder,
            'idUsers'            => $intUserId,
            'creator'            => $objGenericSetup->getCreatorId(),
            'publisher'          => $intUserId,
            'created'            => date('Y-m-d H:i:s'),
            'published'          => $objGenericSetup->getPublishDate(),
            'idStatus'           => $objGenericSetup->getStatusId(),
            'isUrlFolder'        => $objGenericSetup->getUrlFolder(),
            'showInNavigation'   => $objGenericSetup->getShowInNavigation(),
            'showInWebsite'      => $objGenericSetup->getShowInWebsite(),
            'showInTablet'       => $objGenericSetup->getShowInTablet(),
            'showInMobile'       => $objGenericSetup->getShowInMobile(),
            'hideInSitemap'      => $objGenericSetup->getHideInSitemap()
        );
        $this->getFolderPropertyTable()->insert($arrProperties);

        return $objFolder;
    }

    /**
     * update
     * @param GenericSetup $objGenericSetup
     * @param object Folder
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function update(GenericSetup $objGenericSetup, $objFolder)
    {
        $this->core->logger->debug('cms->models->Model_Folders->update()');

        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

        $strWhere = $this->getFolderTable()->getAdapter()->quoteInto('folderId = ?', $objFolder->folderId);
        $strWhere .= $this->getFolderTable()->getAdapter()->quoteInto(' AND version = ?', $objFolder->version);

        $this->getFolderTable()->update(array(
                                             'idUsers'    => $intUserId,
                                             'idSegments' => $objGenericSetup->getSegmentId(),
                                             'changed'    => date('Y-m-d H:i:s')
                                        ), $strWhere);
        /**
         * update language specific folder properties
         */
        $strWhere .= $this->getFolderTable()->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);
        $intNumOfEffectedRows = $this->getFolderPropertyTable()->update(array(
                                                                             'idGenericForms'   => $objGenericSetup->getGenFormId(),
                                                                             'idUsers'          => $intUserId,
                                                                             'creator'          => $objGenericSetup->getCreatorId(),
                                                                             'idStatus'         => $objGenericSetup->getStatusId(),
                                                                             'isUrlFolder'      => $objGenericSetup->getUrlFolder(),
                                                                             'showInNavigation' => $objGenericSetup->getShowInNavigation(),
                                                                             'showInWebsite'    => $objGenericSetup->getShowInWebsite(),
                                                                             'showInTablet'     => $objGenericSetup->getShowInTablet(),
                                                                             'showInMobile'     => $objGenericSetup->getShowInMobile(),
                                                                             'hideInSitemap'    => $objGenericSetup->getHideInSitemap(),
                                                                             'published'        => $objGenericSetup->getPublishDate(),
                                                                             'changed'          => date('Y-m-d H:i:s')
                                                                        ), $strWhere);

        /**
         * insert language specific folder properties
         */
        if ($intNumOfEffectedRows == 0) {
            $arrProperties = array(
                'folderId'         => $objFolder->folderId,
                'version'          => $objFolder->version,
                'idLanguages'      => $this->intLanguageId,
                'idGenericForms'   => $objGenericSetup->getGenFormId(),
                'idFolderTypes'    => $this->core->sysConfig->folder_types->folder,
                'idUsers'          => $intUserId,
                'creator'          => $objGenericSetup->getCreatorId(),
                'publisher'        => $intUserId,
                'created'          => date('Y-m-d H:i:s'),
                'published'        => $objGenericSetup->getPublishDate(),
                'idStatus'         => $objGenericSetup->getStatusId(),
                'isUrlFolder'      => $objGenericSetup->getUrlFolder(),
                'showInNavigation' => $objGenericSetup->getShowInNavigation(),
                'hideInSitemap'    => $objGenericSetup->getHideInSitemap(),
                'showInWebsite'    => $objGenericSetup->getShowInWebsite(),
                'showInTablet'     => $objGenericSetup->getShowInTablet(),
                'showInMobile'     => $objGenericSetup->getShowInMobile()
            );
            $this->getFolderPropertyTable()->insert($arrProperties);
        }
    }

    /**
     * loadAllRootLevels
     * @param integer $intRootLevelType
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadAllRootLevels($intRootLevelModule, $intRootLevelType = -1, $intEnvironment = null)
    {
        $this->core->logger->debug('core->models->Folders->loadAllRootLevels(' . $intRootLevelModule . ', ' . $intRootLevelType . ')');

        $objSelect = $this->getRootLevelTable()->select();
        $objSelect->setIntegrityCheck(false);

        /**
         * SELECT rootLevels.id, rootLevels.idRootLevelTypes, rootLevelTitles.title FROM rootLevels
         * INNER JOIN rootLevelTitles ON rootLevelTitles.idRootLevels = rootLevels.id
         * WHERE rootLevelTitles.idLanguages = ?
         *  AND rootLevels.idModules = ?
         *  AND rootLevels.idRootLevelTypes = ?
         */
        $strAppEnv = APPLICATION_ENV;
        $intEnvironment = ($intEnvironment == null) ? $this->core->sysConfig->environments->$strAppEnv : $intEnvironment;
        
        $objSelect->from('rootLevels', array('id', 'idRootLevelTypes', 'landingPages', 'href', 'order', 'languageDefinitionType'));
        $objSelect->join('rootLevelTitles', 'rootLevelTitles.idRootLevels = rootLevels.id', array('title'));
        $objSelect->joinLeft('rootLevelUrls', 'rootLevelUrls.idRootLevels = rootLevels.id AND idEnvironments = '.$intEnvironment.' AND isMain = 1', array('idLanguages AS idDefaultLanguage'));
        $objSelect->joinLeft('rootLevelLanguages', 'rootLevelLanguages.idRootLevels = rootLevels.id AND rootLevelLanguages.isFallback = 1', array('rootLevelLanguageId' => 'idLanguages'));
        $objSelect->joinLeft(array('rLGuiLanguages' => 'rootLevelLanguages'), 'rLGuiLanguages.idRootLevels = rootLevels.id AND rLGuiLanguages.idLanguages = ' . $this->intContentLanguageId, array('rootLevelGuiLanguageId' => 'rLGuiLanguages.idLanguages'));
        $objSelect->where('rootLevelTitles.idLanguages = ?', $this->intLanguageId);
        $objSelect->where('rootLevels.idModules = ?', $intRootLevelModule);
        $objSelect->where('rootLevels.active = 1');
        if ($intRootLevelType != -1) {
            $objSelect->where('rootLevels.idRootLevelTypes = ?', $intRootLevelType);
        }
        $objSelect->order('rootLevels.order');
        $objSelect->order('rootLevels.id');
        
        return $this->getRootLevelTable()->fetchAll($objSelect);
    }

    /**
     * loadAllRootLevelsWithGroups
     * @param integer $intRootLevelModule
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function loadAllRootLevelsWithGroups($intRootLevelModule)
    {
        $this->core->logger->debug('core->models->Folders->loadAllRootLevels(' . $intRootLevelModule . ')');

        $objSelect = $this->getRootLevelTable()->select();
        $objSelect->setIntegrityCheck(false);

        /**
         * SELECT rootLevels.id, rootLevels.idRootLevelTypes, rootLevelTitles.title FROM rootLevels
         * INNER JOIN rootLevelTitles ON rootLevelTitles.idRootLevels = rootLevels.id
         * WHERE rootLevelTitles.idLanguages = ?
         *  AND rootLevels.idModules = ?
         *  AND rootLevels.idRootLevelTypes = ?
         */
        $objSelect->from('rootLevels', array('id', 'idRootLevelTypes', 'href', 'idRootLevelGroups'));
        $objSelect->join('rootLevelTitles', 'rootLevelTitles.idRootLevels = rootLevels.id', array('title'));
        $objSelect->join('rootLevelGroups', 'rootLevelGroups.id = rootLevels.idRootLevelGroups', array('name'));
        $objSelect->joinLeft('rootLevelGroupTitles', 'rootLevelGroupTitles.idRootLevelGroups = rootLevelGroups.id AND rootLevelGroupTitles.idLanguages = ' . $this->intLanguageId, array('rootLevelGroupTitle' => 'title'));
        $objSelect->joinLeft('rootLevelLanguages', 'rootLevelLanguages.idRootLevels = rootLevels.id AND rootLevelLanguages.isFallback = 1', array('rootLevelLanguageId' => 'idLanguages'));
        $objSelect->joinLeft(array('rLGuiLanguages' => 'rootLevelLanguages'), 'rLGuiLanguages.idRootLevels = rootLevels.id AND rLGuiLanguages.idLanguages = ' . $this->intContentLanguageId, array('rootLevelGuiLanguageId' => 'rLGuiLanguages.idLanguages'));
        $objSelect->where('rootLevelTitles.idLanguages = ?', $this->intLanguageId);
        $objSelect->where('rootLevels.idModules = ?', $intRootLevelModule);
        $objSelect->where('rootLevels.active = 1');
        $objSelect->order('idRootLevelGroups');
        $objSelect->order('rootLevels.order');
        $objSelect->order('rootLevels.id');

        return $this->getRootLevelTable()->fetchAll($objSelect);
    }

    /**
     * getThemeByDomain
     * @param string $strDomain
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getThemeByDomain($strDomain)
    {
        $this->core->logger->debug('core->models->Folders->getThemeByDomain(' . $strDomain . ')');

        $objSelect = $this->getRootLevelUrlTable()->select();
        $objSelect->setIntegrityCheck(false);
        if (strpos($strDomain, 'www.') !== false) {
            $strDomain = str_replace('www.', '', $strDomain);
        }
        $objSelect->from('rootLevelUrls', array('id', 'url', 'isMain', 'urlPath' => 'path', 'hostPrefix', 'idRootLevels', 'idLanguages', 'analyticsKey', 'mapsKey'));
        $objSelect->join('rootLevels', 'rootLevels.id = rootLevelUrls.idRootLevels', array('idRootLevelGroups', 'isSecure', 'hasPortalGate', 'hasSegments', 'languageDefinitionType'));
        $objSelect->join('languages', 'languages.id = rootLevelUrls.idLanguages', array('languageCode'));
        $objSelect->joinLeft('rootLevelTitles', 'rootLevelTitles.idRootLevels = rootLevels.id AND rootLevelTitles.idLanguages = ' . $this->intLanguageId, array('title'));
        $objSelect->joinLeft(array('rLTDefault' => 'rootLevelTitles'), 'rLTDefault.idRootLevels = rootLevels.id AND rLTDefault.idLanguages = rootLevelUrls.idLanguages', array('defaultTitle' => 'title'));
        if (isset($this->core->config->languages->alternative->id)) {
            $objSelect->joinLeft(array('alternativeRootLevelTitles' => 'rootLevelTitles'), 'alternativeRootLevelTitles.idRootLevels = rootLevels.id AND alternativeRootLevelTitles.idLanguages = ' . $this->core->config->languages->alternative->id, array('alternativeTitle' => 'title'));
        }
        $objSelect->joinLeft('rootLevelLanguages', 'rootLevelLanguages.idRootLevels = rootLevels.id AND rootLevelLanguages.idLanguages = ' . $this->intLanguageId, array('rootLevelLanguageId' => 'idLanguages'));
        $objSelect->joinLeft('localizations', 'localizations.id = rootLevelLanguages.idLocalizations', array('localization'));
        $objSelect->join('themes', 'themes.id = rootLevels.idThemes', array('path'));
        $objSelect->where('rootLevelUrls.url = ?', $strDomain);

        return $this->getRootLevelUrlTable()->fetchAll($objSelect);
    }

    /**
     * loadRootNavigation
     * @param integer $intRootId
     * @param string $strSortTimestampOrderType = 'DESC'
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadRootNavigation($intRootId, $strSortTimestampOrderType = 'DESC')
    {
        $this->core->logger->debug('core->models->Folders->loadRootNavigation(' . $intRootId . ')');

        $sqlStmt = $this->core->dbh->query("SELECT id, title, guiTitle, genericFormId, version, templateId, folderType, pageType, type, elementType, isStartPage AS isStartElement, isStartPage, sortPosition, sortTimestamp, idStatus, pageLinkTitle, pageGuiLinkTitle
                                                                          FROM (SELECT folders.id, folderTitles.title, fGuiTitles.title AS guiTitle, genericForms.genericFormId, genericForms.version, -1 AS templateId, folderProperties.idFolderTypes AS folderType, -1 AS pageType, folderTypes.title As type, 'folder' AS elementType, -1 AS isStartPage, folders.sortPosition, folders.sortTimestamp, folderProperties.idStatus,
                                                                                       -1 AS pageLinkTitle, -1 AS pageGuiLinkTitle
                                                                                  FROM folders
                                                                                    LEFT JOIN folderProperties ON folderProperties.folderId = folders.folderId
                                                                                      AND folderProperties.version = folders.version
                                                                                      AND folderProperties.idLanguages = ?
                                                                                    LEFT JOIN folderTitles ON folderTitles.folderId = folders.folderId
                                                                                      AND folderTitles.version = folders.version AND folderTitles.idLanguages = ?
                                                                                    LEFT JOIN folderTitles AS fGuiTitles ON fGuiTitles.folderId = folders.folderId
                                                                                      AND fGuiTitles.version = folders.version AND fGuiTitles.idLanguages = 0
                                                                                    LEFT JOIN genericForms ON genericForms.id = folderProperties.idGenericForms
                                                                                    LEFT JOIN folderTypes ON folderTypes.id = folderProperties.idFolderTypes
                                                                                   WHERE folders.idRootLevels = ? AND
                                                                                         folders.idParentFolder = 0
                                                                                UNION
                                                                                SELECT pages.id, pageTitles.title, pGuiTitles.title AS guiTitle, genericForms.genericFormId, genericForms.version, pageProperties.idTemplates  AS templateId, -1 AS folderType, pageProperties.idPageTypes AS pageType, pageTypes.title As type, 'page' AS elementType, pages.isStartPage, pages.sortPosition, pages.sortTimestamp, pageProperties.idStatus,
                                                                                       (SELECT pt.title FROM pageLinks, pages AS p LEFT JOIN pageTitles AS pt ON pt.pageId = p.pageId AND pt.version = p.version AND pt.idLanguages = ? WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1) AS pageLinkTitle,
                                                                                       (SELECT pt.title FROM pageLinks, pages AS p LEFT JOIN pageTitles AS pt ON pt.pageId = p.pageId AND pt.version = p.version AND pt.idLanguages = 0 WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1) AS pageGuiLinkTitle
                                                                                  FROM pages
                                                                                    LEFT JOIN pageProperties ON pageProperties.pageId = pages.pageId
                                                                                      AND pageProperties.version = pages.version
                                                                                      AND pageProperties.idLanguages = ?
                                                                                    LEFT JOIN pageTitles ON pageTitles.pageId = pages.pageId
                                                                                      AND pageTitles.version = pages.version
                                                                                      AND pageTitles.idLanguages = ?
                                                                                    LEFT JOIN pageTitles AS pGuiTitles ON pGuiTitles.pageId = pages.pageId
                                                                                      AND pGuiTitles.version = pages.version
                                                                                      AND pGuiTitles.idLanguages = 0
                                                                                    LEFT JOIN pageTypes ON pageTypes.id = pageProperties.idPageTypes
                                                                                    LEFT JOIN genericForms ON genericForms.id = pageProperties.idGenericForms
                                                                                  WHERE pages.idParent = ? AND
                                                                                        pages.idParentTypes = ? AND
                                                                                        pages.id = (SELECT p.id FROM pages p WHERE p.pageId = pages.pageId ORDER BY p.version DESC LIMIT 1))
                                                                          AS tbl
                                                                          ORDER BY sortPosition ASC, sortTimestamp $strSortTimestampOrderType, id ASC", array($this->intLanguageId, $this->intLanguageId, $intRootId, $this->intLanguageId, $this->intLanguageId, $this->intLanguageId, $intRootId, $this->core->sysConfig->parent_types->rootlevel));

        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * countRootChilds
     * @param integer $intRootId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function countRootChilds($intRootId)
    {
        $this->core->logger->debug('core->models->Folders->countRootChilds(' . $intRootId . ')');

        $sqlStmt = $this->core->dbh->query("SELECT COUNT(id) counter
                                        FROM (SELECT folders.id FROM folders WHERE folders.idRootLevels = ? AND folders.idParentFolder = 0
                                              UNION
                                              SELECT pages.id FROM pages WHERE pages.isStartPage != 1 AND pages.idParent = ? AND pages.idParentTypes = ? AND pages.id = (SELECT p.id FROM pages p WHERE p.pageId = pages.pageId ORDER BY p.version DESC LIMIT 1))
                                        AS tbl",
            array($intRootId, $intRootId, $this->core->sysConfig->parent_types->rootlevel));
        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadGlobalRootNavigation
     * @param integer $intRootLevelId
     * @param integer $intRootLevelGroupId
     * @param string $strSortTimestampOrderType = 'DESC'
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadGlobalRootNavigation($intRootLevelId, $intRootLevelGroupId, $strSortTimestampType = 'DESC')
    {
        $this->core->logger->debug('core->models->Folders->loadGlobalRootNavigation(' . $intRootLevelId . ',' . $intRootLevelGroupId . ', ' . $strSortTimestampType . ')');

        $objFolderSelect = $this->core->dbh->select();
        $objFolderSelect->from('folders', array('id', 'elementType' => new Zend_Db_Expr("'folder'"), 'templateId' => new Zend_Db_Expr('-1'), 'folderType' => 'folderProperties.idFolderTypes', 'globalType' => new Zend_Db_Expr('-1'), 'isStartElement' => new Zend_Db_Expr('-1'), 'isStartGlobal' => new Zend_Db_Expr('-1'), 'sortPosition', 'sortTimestamp', 'folderProperties.idStatus', 'linkGlobalId' => new Zend_Db_Expr('-1')));
        $objFolderSelect->joinLeft('folderProperties', 'folderProperties.folderId = folders.folderId AND folderProperties.version = folders.version AND folderProperties.idLanguages = ' . $this->intLanguageId, array());
        $objFolderSelect->joinLeft('folderTitles', 'folderTitles.folderId = folders.folderId AND folderTitles.version = folders.version AND folderTitles.idLanguages = ' . $this->intLanguageId, array('title'));
        $objFolderSelect->joinLeft(array('fGuiTitles' => 'folderTitles'), 'fGuiTitles.folderId = folders.folderId AND fGuiTitles.version = folders.version AND fGuiTitles.idLanguages = 0', array('guiTitle' => 'fGuiTitles.title'));
        $objFolderSelect->joinLeft('genericForms', 'genericForms.id = folderProperties.idGenericForms', array('genericFormId', 'version'));
        $objFolderSelect->joinLeft('folderTypes', 'folderTypes.id = folderProperties.idFolderTypes', array('title AS type'));
        $objFolderSelect->where('folders.idRootLevels = ? AND folders.idParentFolder = 0', $intRootLevelId);

        if ($intRootLevelGroupId == $this->core->sysConfig->root_level_groups->product) {
            $objGlobalSelect = $this->core->dbh->select();
            $objGlobalSelect->from('globals', array('id', 'elementType' => new Zend_Db_Expr("'global'"), 'templateId' => 'globalProperties.idTemplates', 'folderType' => new Zend_Db_Expr('-1'), 'globalType' => 'globalProperties.idGlobalTypes', 'isStartElement' => 'isStartGlobal', 'isStartGlobal', 'lP.sortPosition', 'lP.sortTimestamp', 'globalProperties.idStatus', 'linkGlobalId' => 'lP.id'));
            $objGlobalSelect->join('globalLinks', 'globalLinks.globalId = globals.globalId', array());
            $objGlobalSelect->join(array('lP' => 'globals'), 'lP.id = globalLinks.idGlobals', array());
            $objGlobalSelect->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $this->intLanguageId, array());
            $objGlobalSelect->joinLeft('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = ' . $this->intLanguageId, array('title'));
            $objGlobalSelect->joinLeft(array('gGuiTitles' => 'globalTitles'), 'gGuiTitles.globalId = globals.globalId AND gGuiTitles.version = globals.version AND gGuiTitles.idLanguages = 0', array('guiTitle' => 'gGuiTitles.title'));
            $objGlobalSelect->joinLeft('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId', 'version'));
            $objGlobalSelect->joinLeft('globalTypes', 'globalTypes.id = globalProperties.idGlobalTypes', array('title AS type'));
            $objGlobalSelect->where('lP.idParent = ?', $intRootLevelId);
            $objGlobalSelect->where('lP.idParentTypes = ?', $this->core->sysConfig->parent_types->rootlevel);
            $objGlobalSelect->where('globals.id = (SELECT p.id FROM globals p WHERE p.globalId = globals.globalId ORDER BY p.version DESC LIMIT 1)');
        } else {
            $objGlobalSelect = $this->core->dbh->select();
            $objGlobalSelect->from('globals', array('id', 'elementType' => new Zend_Db_Expr("'global'"), 'templateId' => 'globalProperties.idTemplates', 'folderType' => new Zend_Db_Expr('-1'), 'globalType' => 'globalProperties.idGlobalTypes', 'isStartElement' => 'isStartGlobal', 'isStartGlobal', 'sortPosition', 'sortTimestamp', 'globalProperties.idStatus', 'linkGlobalId' => new Zend_Db_Expr('-1')));
            $objGlobalSelect->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $this->intLanguageId, array());
            $objGlobalSelect->joinLeft('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = ' . $this->intLanguageId, array('title'));
            $objGlobalSelect->joinLeft(array('gGuiTitles' => 'globalTitles'), 'gGuiTitles.globalId = globals.globalId AND gGuiTitles.version = globals.version AND gGuiTitles.idLanguages = 0', array('guiTitle' => 'gGuiTitles.title'));
            $objGlobalSelect->joinLeft('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId', 'version'));
            $objGlobalSelect->joinLeft('globalTypes', 'globalTypes.id = globalProperties.idGlobalTypes', array('title AS type'));
            $objGlobalSelect->where('globals.idParent = ?', $intRootLevelId);
            $objGlobalSelect->where('globals.idParentTypes = ?', $this->core->sysConfig->parent_types->rootlevel);
        }

        $objSelect = $this->getFolderTable()->select()
            ->union(array($objFolderSelect, $objGlobalSelect))
            ->order(array('sortPosition', 'sortTimestamp ' . $strSortTimestampType, 'id'));
        return $this->getFolderTable()->fetchAll($objSelect);
    }

    /**
     * countGlobalRootChilds
     * @param integer $intRootLevelId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function countGlobalRootChilds($intRootId)
    {
        $this->core->logger->debug('core->models->Folders->countGlobalRootChilds(' . $intRootId . ')');

        $sqlStmt = $this->core->dbh->query("SELECT COUNT(id) counter
                                        FROM (SELECT folders.id FROM folders WHERE folders.idRootLevels = ? AND folders.idParentFolder = 0
                                              UNION
                                              SELECT globals.id FROM globals WHERE globals.idParent = ? AND globals.idParentTypes = ? AND globals.id = (SELECT g.id FROM globals g WHERE g.globalId = globals.globalId ORDER BY g.version DESC LIMIT 1))
                                        AS tbl",
            array($intRootId, $intRootId, $this->core->sysConfig->parent_types->rootlevel));
        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadWebsiteRootNavigation
     * @param integer $intRootId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadWebsiteRootNavigation($intRootId)
    {
        $this->core->logger->debug('core->models->Folders->loadWebsiteRootNavigation(' . $intRootId . ')');

        $strFolderFilter = '';
        $strPageFilter = '';
        if (!isset($_SESSION['sesTestMode']) || (isset($_SESSION['sesTestMode']) && $_SESSION['sesTestMode'] == false)) {
            $strFolderFilter = ' AND folderProperties.idStatus = ' . $this->core->sysConfig->status->live;
            $strPageFilter = ' AND pageProperties.idStatus = ' . $this->core->sysConfig->status->live;
        }

        if (!empty($this->intSegmentId)) {
            $strPageFilter .= ' AND (pages.idSegments = 0 OR pages.idSegments = ' . $this->core->dbh->quote($this->intSegmentId, Zend_Db::INT_TYPE) . ')';
            $strFolderFilter .= ' AND (folders.idSegments = 0 OR folders.idSegments = ' . $this->core->dbh->quote($this->intSegmentId, Zend_Db::INT_TYPE) . ')';
        }

        $sqlStmt = $this->core->dbh->query('SELECT id, title, idStatus, url, pageId, folderId, sortPosition, sortTimestamp, isStartPage, (SELECT languageCode FROM languages WHERE id = ?) AS languageCode, target
                                        FROM (SELECT DISTINCT folders.id, folderTitles.title, folderProperties.idStatus,
                                                              IF(pageProperties.idPageTypes = ?,
                                                                 (SELECT pU.url FROM pageLinks, pages AS p LEFT JOIN urls AS pU ON pU.relationId = p.pageId AND pU.version = p.version AND pU.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND pU.idLanguages = ? AND pU.isMain = 1 WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1),
                                                                 (SELECT pU.url FROM urls AS pU WHERE pU.relationId = pages.pageId AND pU.version = pages.version AND pU.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND pU.idLanguages = ? AND pU.isMain = 1 ORDER BY pU.version DESC LIMIT 1)) AS url,
                                                              IF(pageProperties.idPageTypes = ?,
                                                                 (SELECT p.pageId FROM pages AS p, pageLinks WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1),
                                                                 pages.pageId) AS pageId,
                                                              folders.folderId, folders.sortPosition, folders.sortTimestamp, -1 AS isStartPage, pageTargets.target
                                              FROM folders
                                                INNER JOIN folderProperties ON 
                                                  folderProperties.folderId = folders.folderId AND 
                                                  folderProperties.version = folders.version AND 
                                                  folderProperties.idLanguages = ?
                                                INNER JOIN folderTitles ON
                                                  folderTitles.folderId = folders.folderId AND
                                                  folderTitles.version = folders.version AND
                                                  folderTitles.idLanguages = ?
                                                INNER JOIN pages ON
                                                  pages.idParent = folders.id AND
                                                  pages.idParentTypes = ? AND
                                                  pages.isStartPage = 1
                                                INNER JOIN pageProperties ON 
                                                  pageProperties.pageId = pages.pageId AND 
                                                  pageProperties.version = pages.version AND 
                                                  pageProperties.idLanguages = ?
                                                LEFT JOIN pageTargets ON 
                                                  pageTargets.pageId = pages.pageId AND 
                                                  pageTargets.version = pages.version AND 
                                                  pageTargets.idLanguages = ?
                                                WHERE folders.idRootLevels = ? AND
                                                      folders.idParentFolder = 0 AND
                                                      folderProperties.showInNavigation = 1
                                                      ' . $strFolderFilter . '
                                              UNION
                                              SELECT DISTINCT pages.id, pageTitles.title, pageProperties.idStatus,
                                                              IF(pageProperties.idPageTypes = ?,
                                                                 (SELECT pU.url FROM pageLinks, pages AS p LEFT JOIN urls AS pU ON pU.relationId = p.pageId AND pU.version = p.version AND pU.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND pU.idLanguages = ? AND pU.isMain = 1  WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1),
                                                                 (SELECT pU.url FROM urls AS pU WHERE pU.relationId = pages.pageId AND pU.version = pages.version AND pU.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND pU.idLanguages = ? AND pU.isMain = 1 ORDER BY pU.version DESC LIMIT 1)) AS url,
                                                              IF(pageProperties.idPageTypes = ?,
                                                                 (SELECT p.pageId FROM pages AS p, pageLinks WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1),
                                                                 pages.pageId) AS pageId,
                                                              -1 AS folderId, pages.sortPosition, pages.sortTimestamp, pages.isStartPage, pageTargets.target
                                              FROM pages
                                                INNER JOIN pageProperties ON 
                                                  pageProperties.pageId = pages.pageId AND 
                                                  pageProperties.version = pages.version AND 
                                                  pageProperties.idLanguages = ?
                                                LEFT JOIN pageTitles ON
                                                  pageTitles.pageId = pages.pageId AND
                                                  pageTitles.version = pages.version AND
                                                  pageTitles.idLanguages = ?
                                                LEFT JOIN pageTargets ON 
                                                  pageTargets.pageId = pages.pageId AND 
                                                  pageTargets.version = pages.version AND 
                                                  pageTargets.idLanguages = ?
                                                WHERE pages.idParent = ? AND
                                                      pages.idParentTypes = ? AND
                                                      pageProperties.showInNavigation = 1 AND
                                                      pages.id = (SELECT p.id FROM pages p WHERE p.pageId = pages.pageId ORDER BY p.version DESC LIMIT 1)
                                                      ' . $strPageFilter . ')
                                        AS tbl
                                        ORDER BY sortPosition ASC, sortTimestamp DESC, id ASC', array(
                                                                                                     $this->intLanguageId,
                                                                                                     $this->core->sysConfig->page_types->link->id,
                                                                                                     $this->intLanguageId,
                                                                                                     $this->intLanguageId,
                                                                                                     $this->core->sysConfig->page_types->link->id,
                                                                                                     $this->intLanguageId,
                                                                                                     $this->intLanguageId,
                                                                                                     $this->core->sysConfig->parent_types->folder,
                                                                                                     $this->intLanguageId,
                                                                                                     $this->intLanguageId,
                                                                                                     $intRootId,
                                                                                                     $this->core->sysConfig->page_types->link->id,
                                                                                                     $this->intLanguageId,
                                                                                                     $this->intLanguageId,
                                                                                                     $this->core->sysConfig->page_types->link->id,
                                                                                                     $this->intLanguageId,
                                                                                                     $this->intLanguageId,
                                                                                                     $this->intLanguageId,
                                                                                                     $intRootId,
                                                                                                     $this->core->sysConfig->parent_types->rootlevel
                                                                                                ));


        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadWebsiteStaticSubNavigation
     * @param integer $intFolderId
     * @param integer $intDepth
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadWebsiteStaticSubNavigation($intFolderId, $intDepth)
    {
        $this->core->logger->debug('core->models->Folders->loadWebsiteSubNavigation(' . $intFolderId . ',' . $intDepth . ')');

        $strFolderFilter = '';
        $strPageFilter = '';
        if (!isset($_SESSION['sesTestMode']) || (isset($_SESSION['sesTestMode']) && $_SESSION['sesTestMode'] == false)) {
            $strFolderFilter = 'AND folderProperties.idStatus = ' . $this->core->sysConfig->status->live;
            $strPageFilter = 'AND pageProperties.idStatus = ' . $this->core->sysConfig->status->live;
        }

        if (!empty($this->intSegmentId)) {
            $strPageFilter .= ' AND (pages.idSegments = 0 OR pages.idSegments = ' . $this->core->dbh->quote($this->intSegmentId, Zend_Db::INT_TYPE) . ')';
            $strFolderFilter .= ' AND (folders.idSegments = 0 OR folders.idSegments = ' . $this->core->dbh->quote($this->intSegmentId, Zend_Db::INT_TYPE) . ')';
        }

        $sqlStmt = $this->core->dbh->query('SELECT folders.id AS idFolder, folders.folderId, folders.idParentFolder as parentId, folderTitles.title AS folderTitle, folderProperties.idStatus AS folderStatus, folders.depth, folders.sortPosition as folderOrder,
                                               pages.id AS idPage, pages.pageId, pageTitles.title AS pageTitle, pages.isStartPage, pageProperties.idStatus AS pageStatus, pages.sortPosition as pageOrder,
                                               IF(pageProperties.idPageTypes = ?,
                                                  (SELECT pU.url FROM pageLinks, pages AS p LEFT JOIN urls AS pU ON pU.relationId = p.pageId AND pU.version = p.version AND pU.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND pU.idLanguages = ? AND pU.isMain = 1 WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1),
                                                  (SELECT pU.url FROM urls AS pU WHERE pU.relationId = pages.pageId AND pU.version = pages.version AND pU.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND pU.idLanguages = ? AND pU.isMain = 1 ORDER BY pU.version DESC LIMIT 1)) AS url,
                                               (SELECT languageCode FROM languages WHERE id = ?) AS languageCode
                                          FROM folders
                                            INNER JOIN folderProperties ON 
                                              folderProperties.folderId = folders.folderId AND 
                                              folderProperties.version = folders.version AND 
                                              folderProperties.idLanguages = ?
                                            INNER JOIN folderTitles ON
                                              folderTitles.folderId = folders.folderId AND
                                              folderTitles.version = folders.version AND
                                              folderTitles.idLanguages = ?
                                            LEFT JOIN pages ON
                                              pages.idParent = folders.id AND
                                              pages.idParentTypes = ?
                                            LEFT JOIN pageProperties ON 
                                              pageProperties.pageId = pages.pageId AND 
                                              pageProperties.version = pages.version AND 
                                              pageProperties.idLanguages = ?                                              
                                              ' . $strPageFilter . '
                                            LEFT JOIN pageTitles ON
                                              pageTitles.pageId = pages.pageId AND
                                              pageTitles.version = pages.version AND
                                              pageTitles.idLanguages = ?
                                          ,folders AS parent
                                           WHERE parent.id = ? AND
                                                 folders.lft BETWEEN parent.lft AND parent.rgt AND
                                                 folders.idRootLevels = parent.idRootLevels AND
                                                 folders.depth <= ? AND
                                                 folderProperties.showInNavigation = 1 AND
                                                 pageProperties.showInNavigation = 1
                                                 ' . $strFolderFilter . '
                                             ORDER BY folders.lft, pages.isStartPage DESC, pages.sortPosition ASC, pages.sortTimestamp DESC, pages.id ASC', array($this->core->sysConfig->page_types->link->id, $this->intLanguageId, $this->intLanguageId, $this->intLanguageId, $this->intLanguageId, $this->intLanguageId, $this->core->sysConfig->parent_types->folder, $this->intLanguageId, $this->intLanguageId, $intFolderId, $intDepth));

        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadFolderContentById
     * @param integer $intFolderId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadFolderContentById($intFolderId, $strSearch = '', $strOrderColumn, $strOrderSort)
    {
        $this->core->logger->debug('core->models->Folders->loadFolderContentById(' . $intFolderId . ')');

        //Pages
        $objPageSelect = $this->getFolderTable()->select();
        $objPageSelect->setIntegrityCheck(false);

        $objPageSelect->from('pages', array('id', 'pageId AS relationId', 'isStartPage', 'sortPosition', 'changed', 'elementType' => new Zend_Db_Expr('"page"'), 'version'));
        $objPageSelect->joinLeft('pageProperties',
            $objPageSelect->getAdapter()->quoteInto('pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version AND pageProperties.idLanguages = ?', $this->intLanguageId),
            array('idStatus', 'idTemplates'));
        $objPageSelect->joinLeft('pageTitles',
            $objPageSelect->getAdapter()->quoteInto('pages.pageId = pageTitles.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = ?', $this->intLanguageId),
            array());
        $objPageSelect->joinLeft('pageTitles AS pGuiTitles',
            $objPageSelect->getAdapter()->quoteInto('pages.pageId = pGuiTitles.pageId AND pGuiTitles.version = pages.version AND pGuiTitles.idLanguages = ?', 0),
            array('title' => new Zend_Db_Expr('IF(pageTitles.title IS NULL, pGuiTitles.title, pageTitles.title)')));
        $objPageSelect->joinLeft('users',
            'pages.idUsers = users.id',
            array('CONCAT(users.fname, \' \', users.sname) AS author'));
        $objPageSelect->joinLeft('genericForms', 'genericForms.id = pageProperties.idGenericForms', array('genericFormId'));
        if ($strSearch != '') {
            $objPageSelect->where('pageTitles.title LIKE ? OR pGuiTitles.title LIKE ?', '%' . $strSearch . '%');
        }
        $objPageSelect->where('pages.idParent = ?', $intFolderId);
        $objPageSelect->where('pages.idParentTypes = ?', $this->core->sysConfig->parent_types->folder);

        //Folders
        $objFolderSelect = $this->getFolderTable()->select();
        $objFolderSelect->setIntegrityCheck(false);

        $objFolderSelect->from('folders', array('id', 'folderId AS relationId', 'isStartPage' => new Zend_Db_Expr('-1'), 'sortPosition', 'changed', 'elementType' => new Zend_Db_Expr('"folder"'), 'version'));
        $objFolderSelect->joinLeft('folderProperties',
            $objFolderSelect->getAdapter()->quoteInto('folderProperties.folderId = folders.folderId AND folderProperties.version = folders.version AND folderProperties.idLanguages = ?', $this->intLanguageId),
            array('idStatus', 'idTemplates' => new Zend_Db_Expr('-1')));
        $objFolderSelect->joinLeft('folderTitles',
            $objFolderSelect->getAdapter()->quoteInto('folders.folderId = folderTitles.folderId AND folderTitles.version = folders.version AND folderTitles.idLanguages = ?', $this->intLanguageId),
            array());
        $objFolderSelect->joinLeft('folderTitles AS fGuiTitles',
            $objFolderSelect->getAdapter()->quoteInto('folders.folderId = fGuiTitles.folderId AND fGuiTitles.version = folders.version AND fGuiTitles.idLanguages = ?', 0),
            array('title' => new Zend_Db_Expr('IF(folderTitles.title IS NULL, fGuiTitles.title, folderTitles.title)')));
        $objFolderSelect->joinLeft('users',
            'folders.idUsers = users.id',
            array('CONCAT(users.fname, \' \', users.sname) AS author'));
        $objFolderSelect->joinLeft('genericForms', 'genericForms.id = folderProperties.idGenericForms', array('genericFormId'));
        if ($strSearch != '') {
            $objFolderSelect->where('folderTitles.title LIKE ? OR fGuiTitles.title LIKE ?', '%' . $strSearch . '%');
        }
        $objFolderSelect->where('folders.idParentFolder = ?', $intFolderId);

        $objSelect = $this->getFolderTable()->select()->union(array($objPageSelect, $objFolderSelect), Zend_Db_Select::SQL_UNION_ALL);
        if ($strOrderColumn != '' && $strOrderSort != '') {
            $objSelect->order($strOrderColumn . ' ' . $strOrderSort);
        } else {
            $objSelect->order('sortPosition');
        }

        return $objSelect;
    }

    /**
     * loadChildNavigation
     * @param integer $intFolderId
     * @param string $strSortTimestampOrderType = 'DESC'
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadChildNavigation($intFolderId, $strSortTimestampOrderType = 'DESC')
    {
        $this->core->logger->debug('core->models->Folders->loadChildNavigation(' . $intFolderId . ')');

        $sqlStmt = $this->core->dbh->query("SELECT id, title, guiTitle, genericFormId, version, templateId, folderType, pageType, type, elementType, isStartPage AS isStartElement, isStartPage, sortPosition, sortTimestamp, idStatus, pageLinkTitle, pageGuiLinkTitle
                                                                          FROM (SELECT folders.id, folderTitles.title, fGuiTitles.title AS guiTitle, genericForms.genericFormId, genericForms.version, -1 AS templateId, folderProperties.idFolderTypes AS folderType, -1 AS pageType, folderTypes.title AS type, 'folder' AS elementType, -1 AS isStartPage, folders.sortPosition, folders.sortTimestamp, folderProperties.idStatus,
                                                                                       -1 AS pageLinkTitle, -1 AS pageGuiLinkTitle
                                                                                FROM folders
                                                                                LEFT JOIN folderProperties ON
                                                  folderProperties.folderId = folders.folderId AND 
                                                  folderProperties.version = folders.version AND 
                                                  folderProperties.idLanguages = ?
                                                                                LEFT JOIN folderTitles ON folderTitles.folderId = folders.folderId
                                                                                  AND folderTitles.version = folders.version AND folderTitles.idLanguages = ?
                                                                                LEFT JOIN folderTitles AS fGuiTitles ON fGuiTitles.folderId = folders.folderId
                                                AND fGuiTitles.version = folders.version AND fGuiTitles.idLanguages = 0
                                                                                LEFT JOIN genericForms ON genericForms.id = folderProperties.idGenericForms
                                                                                LEFT JOIN folderTypes ON folderTypes.id = folderProperties.idFolderTypes
                                                                                WHERE folders.idParentFolder = ?
                                                                                UNION
                                                                                SELECT pages.id, pageTitles.title, pGuiTitles.title AS guiTitle, genericForms.genericFormId, genericForms.version, pageProperties.idTemplates  AS templateId, -1 AS folderType, pageProperties.idPageTypes AS pageType, pageTypes.title AS type, 'page' AS elementType, pages.isStartPage, pages.sortPosition, pages.sortTimestamp, pageProperties.idStatus,
                                                                                       (SELECT pt.title FROM pageLinks, pages AS p LEFT JOIN pageTitles AS pt ON pt.pageId = p.pageId AND pt.version = p.version AND pt.idLanguages = ? WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1) AS pageLinkTitle,
                                                                                       (SELECT pt.title FROM pageLinks, pages AS p LEFT JOIN pageTitles AS pt ON pt.pageId = p.pageId AND pt.version = p.version AND pt.idLanguages = 0 WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1) AS pageGuiLinkTitle
                                                                                FROM pages
                                                                                LEFT JOIN pageProperties ON pageProperties.pageId = pages.pageId
                                               AND pageProperties.version = pages.version 
                                               AND pageProperties.idLanguages = ?
                                                                                LEFT JOIN pageTitles ON pageTitles.pageId = pages.pageId
                                                                                  AND pageTitles.version = pages.version
                                                                                  AND pageTitles.idLanguages = ?
                                                                                LEFT JOIN pageTitles AS pGuiTitles ON pGuiTitles.pageId = pages.pageId
                                                AND pGuiTitles.version = pages.version
                                                AND pGuiTitles.idLanguages = 0
                                                                                LEFT JOIN pageTypes ON pageTypes.id = pageProperties.idPageTypes
                                                                                LEFT JOIN genericForms ON genericForms.id = pageProperties.idGenericForms
                                                                                WHERE pages.idParent = ?
                                                                                  AND pages.idParentTypes = ?
                                                                                  AND pages.id = (SELECT p.id FROM pages p WHERE p.pageId = pages.pageId ORDER BY p.version DESC LIMIT 1))
                                                                          AS tbl
                                                                          ORDER BY sortPosition ASC, sortTimestamp $strSortTimestampOrderType, id ASC", array($this->intLanguageId, $this->intLanguageId, $intFolderId, $this->intLanguageId, $this->intLanguageId, $this->intLanguageId, $intFolderId, $this->core->sysConfig->parent_types->folder));

        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * Loads all the pages and globals from the given folder
     * @param integer $intFolderId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadChildElements($intFolderId)
    {
        $this->core->logger->debug('core->models->Folders->loadChildElements(' . $intFolderId . ')');

        $objPageSelect = $this->getFolderTable()->select()->setIntegrityCheck(false);
        $objPageSelect->from('folders', array())
            ->join('pages', 'pages.idParent = folders.id AND idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array('id', 'relationId' => 'pageId', 'isStartElement' => 'isStartPage', 'type' => new Zend_Db_Expr('"page"')))
            ->join('pageProperties', 'pageProperties.pageId = pages.pageId AND pageProperties.idLanguages = ' . $this->intLanguageId . ' AND pageProperties.version = pages.version', array('idStatus'))
            ->joinLeft('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = ' . $this->intLanguageId, array('title'))
            ->joinLeft(array('alternativeTitle' => 'pageTitles'), 'alternativeTitle.pageId = pages.pageId AND alternativeTitle.idLanguages = ' . $this->intLanguageId, array('alternativeTitle' => 'title'))
            ->joinLeft(array('fallbackTitle' => 'pageTitles'), 'fallbackTitle.pageId = pages.pageId AND fallbackTitle.idLanguages = 0', array('fallbackTitle' => 'title'))
            ->where('folders.id = ?', $intFolderId);

        $objGlobalSelect = $this->getFolderTable()->select()->setIntegrityCheck(false);
        $objGlobalSelect->from('folders', array())
            ->join('globals', 'globals.idParent = folders.id AND globals.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array('id', 'relationId' => 'globalId', 'isStartElement' => 'isStartGlobal', 'type' => new Zend_Db_Expr('"global"')))
            ->join('globalLinks', 'globalLinks.idGlobals = globals.id', array())
            ->join(array('gL' => 'globals'), 'gL.globalId = globalLinks.globalId', array())
            ->join('globalProperties', 'globalProperties.globalId = gL.globalId AND globalProperties.idLanguages = ' . $this->intLanguageId . ' AND globalProperties.version = gL.version', array('idStatus'))
            ->joinLeft('globalTitles', 'globalTitles.globalId = gL.globalId AND globalTitles.version = gL.version AND globalTitles.idLanguages = ' . $this->intLanguageId, array('title'))
            ->joinLeft(array('alternativeTitle' => 'globalTitles'), 'alternativeTitle.globalId = gL.globalId AND alternativeTitle.idLanguages = ' . $this->intLanguageId, array('alternativeTitle' => 'title'))
            ->joinLeft(array('fallbackTitle' => 'globalTitles'), 'fallbackTitle.globalId = gL.globalId AND fallbackTitle.idLanguages = 0', array('fallbackTitle' => 'title'))
            ->where('folders.id = ?', $intFolderId);

        $objSelect = $this->getFolderTable()->select()->setIntegrityCheck(false)->union(array($objPageSelect, $objGlobalSelect));

        return $this->getFolderTable()->fetchAll($objSelect);
    }

    /**
     * countChilds
     * @param integer $intFolderId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function countChilds($intFolderId)
    {
        $this->core->logger->debug('core->models->Folders->countChilds(' . $intFolderId . ')');

        $sqlStmt = $this->core->dbh->query("SELECT COUNT(id) AS counter
                                        FROM (SELECT folders.id FROM folders WHERE folders.idParentFolder = ?
                                              UNION
                                              SELECT pages.id FROM pages WHERE pages.isStartPage != 1 AND pages.idParent = ? AND pages.idParentTypes = ? AND pages.id = (SELECT p.id FROM pages p WHERE p.pageId = pages.pageId ORDER BY p.version DESC LIMIT 1))
                                        AS tbl",
            array($intFolderId, $intFolderId, $this->core->sysConfig->parent_types->folder));
        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadGlobalChildNavigation
     * @param integer $intFolderId
     * @param integer $intRootLevelGroupId
     * @param string $strSortTimestampOrderType = 'DESC'
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadGlobalChildNavigation($intFolderId, $intRootLevelGroupId, $strSortTimestampOrderType = 'DESC')
    {
        $this->core->logger->debug('core->models->Folders->loadGlobalChildNavigation(' . $intFolderId . ',' . $intRootLevelGroupId . ',' . $strSortTimestampOrderType . ')');

        $objFolderSelect = $this->core->dbh->select();
        $objFolderSelect->from('folders', array('id', 'elementType' => new Zend_Db_Expr("'folder'"), 'templateId' => new Zend_Db_Expr('-1'), 'folderType' => 'folderProperties.idFolderTypes', 'globalType' => new Zend_Db_Expr('-1'), 'isStartElement' => new Zend_Db_Expr('-1'), 'isStartGlobal' => new Zend_Db_Expr('-1'), 'sortPosition', 'sortTimestamp', 'folderProperties.idStatus', 'linkGlobalId' => new Zend_Db_Expr('-1')));
        $objFolderSelect->joinLeft('folderProperties', 'folderProperties.folderId = folders.folderId AND folderProperties.version = folders.version AND folderProperties.idLanguages = ' . $this->intLanguageId, array());
        $objFolderSelect->joinLeft('folderTitles', 'folderTitles.folderId = folders.folderId AND folderTitles.version = folders.version AND folderTitles.idLanguages = ' . $this->intLanguageId, array('title'));
        $objFolderSelect->joinLeft(array('fGuiTitles' => 'folderTitles'), 'fGuiTitles.folderId = folders.folderId AND fGuiTitles.version = folders.version AND fGuiTitles.idLanguages = 0', array('guiTitle' => 'fGuiTitles.title'));
        $objFolderSelect->joinLeft('genericForms', 'genericForms.id = folderProperties.idGenericForms', array('genericFormId', 'version'));
        $objFolderSelect->joinLeft('folderTypes', 'folderTypes.id = folderProperties.idFolderTypes', array('title AS type'));
        $objFolderSelect->where('folders.idParentFolder  = ?', $intFolderId);

        if ($intRootLevelGroupId == $this->core->sysConfig->root_level_groups->product) {
            $objGlobalSelect = $this->core->dbh->select();
            $objGlobalSelect->from('globals', array('id', 'elementType' => new Zend_Db_Expr("'global'"), 'templateId' => 'globalProperties.idTemplates', 'folderType' => new Zend_Db_Expr('-1'), 'globalType' => 'globalProperties.idGlobalTypes', 'isStartElement' => 'isStartGlobal', 'isStartGlobal', 'lP.sortPosition', 'lP.sortTimestamp', 'globalProperties.idStatus', 'linkGlobalId' => 'lP.id'));
            $objGlobalSelect->join('globalLinks', 'globalLinks.globalId = globals.globalId', array());
            $objGlobalSelect->join(array('lP' => 'globals'), 'lP.id = globalLinks.idGlobals', array());
            $objGlobalSelect->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $this->intLanguageId, array());
            $objGlobalSelect->joinLeft('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = ' . $this->intLanguageId, array('title'));
            $objGlobalSelect->joinLeft(array('gGuiTitles' => 'globalTitles'), 'gGuiTitles.globalId = globals.globalId AND gGuiTitles.version = globals.version AND gGuiTitles.idLanguages = 0', array('guiTitle' => 'gGuiTitles.title'));
            $objGlobalSelect->joinLeft('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId', 'version'));
            $objGlobalSelect->joinLeft('globalTypes', 'globalTypes.id = globalProperties.idGlobalTypes', array('title AS type'));
            $objGlobalSelect->where('lP.idParent = ?', $intFolderId);
            $objGlobalSelect->where('lP.idParentTypes = ?', $this->core->sysConfig->parent_types->folder);
            $objGlobalSelect->where('globals.id = (SELECT p.id FROM globals p WHERE p.globalId = globals.globalId ORDER BY p.version DESC LIMIT 1)');
        } else {
            $objGlobalSelect = $this->core->dbh->select();
            $objGlobalSelect->from('globals', array('id', 'elementType' => new Zend_Db_Expr("'global'"), 'templateId' => 'globalProperties.idTemplates', 'folderType' => new Zend_Db_Expr('-1'), 'globalType' => 'globalProperties.idGlobalTypes', 'isStartElement' => 'isStartGlobal', 'isStartGlobal', 'sortPosition', 'sortTimestamp', 'globalProperties.idStatus', 'linkGlobalId' => new Zend_Db_Expr('-1')));
            $objGlobalSelect->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $this->intLanguageId, array());
            $objGlobalSelect->joinLeft('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = ' . $this->intLanguageId, array('title'));
            $objGlobalSelect->joinLeft(array('gGuiTitles' => 'globalTitles'), 'gGuiTitles.globalId = globals.globalId AND gGuiTitles.version = globals.version AND gGuiTitles.idLanguages = 0', array('guiTitle' => 'gGuiTitles.title'));
            $objGlobalSelect->joinLeft('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId', 'version'));
            $objGlobalSelect->joinLeft('globalTypes', 'globalTypes.id = globalProperties.idGlobalTypes', array('title AS type'));
            $objGlobalSelect->where('idParent = ?', $intFolderId);
            $objGlobalSelect->where('idParentTypes = ?', $this->core->sysConfig->parent_types->folder);
        }

        $objSelect = $this->getFolderTable()->select()
            ->union(array($objFolderSelect, $objGlobalSelect))
            ->order(array('sortPosition', 'sortTimestamp ' . $strSortTimestampOrderType, 'id'));

        return $this->getFolderTable()->fetchAll($objSelect);
    }

    /**
     * countGlobalChilds
     * @param integer $intFolderId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function countGlobalChilds($intFolderId)
    {
        $this->core->logger->debug('core->models->Folders->countGlobalChilds(' . $intFolderId . ')');

        $sqlStmt = $this->core->dbh->query("SELECT COUNT(id) AS counter
                                        FROM (SELECT folders.id FROM folders WHERE folders.idParentFolder = ?
                                              UNION
                                              SELECT globals.id FROM globals WHERE globals.idParent = ? AND globals.idParentTypes = ? AND globals.id = (SELECT g.id FROM globals g WHERE g.globalId = globals.globalId ORDER BY g.version DESC LIMIT 1))
                                        AS tbl",
            array($intFolderId, $intFolderId, $this->core->sysConfig->parent_types->folder));
        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadFolderChildPages
     * @param integer $intFolderId
     * @param integer $intCategoryId
     * @param integer $intLabelId
     * @param integer $intLimitNumber
     * @param integer $intSortTypeId
     * @param integer $intSortOrderId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadFolderChildPages($intFolderId, $intCategoryId = 0, $intLabelId = 0, $intLimitNumber = 0, $intSortTypeId = 0, $intSortOrderId = 0)
    {
        $this->core->logger->debug('core->models->Folders->loadFolderChildPages(' . $intFolderId . ',' . $intCategoryId . ',' . $intLabelId . ',' . $intLimitNumber . ',' . $intSortTypeId . ',' . $intSortOrderId . ')');

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

        $strSqlOrderBy = ' ORDER BY folders.lft';
        if ($intSortTypeId > 0 && $intSortTypeId != '') {
            switch ($intSortTypeId) {
                case $this->core->sysConfig->sort->types->manual_sort->id:
                    $strSqlOrderBy = ' ORDER BY pages.sortPosition ' . $strSortOrder . ', pages.sortTimestamp ' . (($strSortOrder == 'DESC') ? 'ASC' : 'DESC');
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
                    $strSqlOrderBy = ' ORDER BY title ' . $strSortOrder;
            }
        }

        $strJoinCategory = '';
        if ($intCategoryId > 0 && $intCategoryId != '') {
            $strJoinCategory = ' INNER JOIN pageCategories ON
                            pageCategories.pageId = pages.pageId AND
                            pageCategories.version = pages.version AND
                            pageCategories.idLanguages = pageProperties.idLanguages AND
                            pageCategories.category = ' . $intCategoryId;
        }

        $strJoinLabel = '';
        if ($intLabelId > 0 && $intLabelId != '') {
            $strJoinLabel = ' INNER JOIN pageLabels ON
                          pageLabels.pageId = pages.pageId AND
                          pageLabels.version = pages.version AND
                          pageLabels.idLanguages = pageProperties.idLanguages AND
                          pageLabels.label = ' . $intLabelId;
        }

        $strSqlLimit = '';
        if ($intLimitNumber > 0 && $intLimitNumber != '') {
            $strSqlLimit = ' LIMIT ' . $intLimitNumber;
        }

        $strFolderFilter = '';
        $strPageFilter = '';
        $strFolderPublishedFilter = '';
        $strPagePublishedFilter = '';
        if (!isset($_SESSION['sesTestMode']) || (isset($_SESSION['sesTestMode']) && $_SESSION['sesTestMode'] == false)) {
            $timestamp = time();
            $now = date('Y-m-d H:i:s', $timestamp);

            $strFolderFilter = 'AND folderProperties.idStatus = ' . $this->core->sysConfig->status->live;
            $strPageFilter = 'AND pageProperties.idStatus = ' . $this->core->sysConfig->status->live;
            $strPagePublishedFilter = ' AND pageProperties.published <= \'' . $now . '\'';
        }

        $sqlStmt = $this->core->dbh->query('SELECT DISTINCT folders.id AS idFolder, folderProperties.idStatus AS folderStatus, folders.depth,
                                              pages.id AS idPage, pageTitles.title AS title, genericForms.genericFormId, genericForms.version, pageProperties.showInNavigation,
                                              pageProperties.idStatus AS pageStatus, urls.url, languageCode, pageProperties.idPageTypes, pageProperties.created AS pageCreated,
                                              pageProperties.changed AS pageChanged, pageProperties.published AS pagePublished, pageProperties.idDestination, pageProperties.hideInSitemap, 
                                              CONCAT(users.fname, \' \', users.sname) AS creator, pageTargets.target
                                          FROM folders
                                            INNER JOIN folderProperties ON 
                                              folderProperties.folderId = folders.folderId AND 
                                              folderProperties.version = folders.version AND 
                                              folderProperties.idLanguages = ?
                                            INNER JOIN pages ON
                                              pages.idParent = folders.id AND
                                              pages.idParentTypes = ?
                                            INNER JOIN pageProperties ON 
                                              pageProperties.pageId = pages.pageId AND 
                                              pageProperties.version = pages.version AND 
                                              pageProperties.idLanguages = ?
                                              ' . $strPageFilter . '
                                              ' . $strPagePublishedFilter . '
                                              ' . $strJoinCategory . '
                                              ' . $strJoinLabel . '
                                            LEFT JOIN pageTargets ON 
                                              pageTargets.pageId = pages.pageId AND 
                                              pageTargets.version = pages.version AND 
                                              pageTargets.idLanguages = ?                                           
                                            INNER JOIN genericForms ON
                                              genericForms.id = pageProperties.idGenericForms
                                            INNER JOIN pageTitles ON
                                              pageTitles.pageId = pages.pageId AND
                                              pageTitles.version = pages.version AND
                                              pageTitles.idLanguages = ?
                                            INNER JOIN urls ON
                                              urls.relationId = pages.pageId AND
                                              urls.version = pages.version AND
                                              urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND
                                              urls.idLanguages = ? AND
                                              urls.isMain = 1
                                            LEFT JOIN users ON
                                              users.id = pageProperties.creator
                                            LEFT JOIN languages  ON
                                              languages.id = ?
                                          ,folders AS parent
                                           WHERE parent.id = ? AND
                                                 folders.lft BETWEEN parent.lft AND parent.rgt AND
                                                 folders.idRootLevels = parent.idRootLevels
                                                 ' . $strFolderFilter . '
                                           ' . $strSqlOrderBy . '
                                           ' . $strSqlLimit, array(
                                                                    $this->intLanguageId,
                                                                    $this->core->sysConfig->parent_types->folder,
                                                                    $this->intLanguageId,
                                                                    $this->intLanguageId,
                                                                    $this->intLanguageId,
                                                                    $this->intLanguageId,
                                                                    $this->intLanguageId,
                                                                    $intFolderId
                                                               ));
        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadOverallFolderChildPages
     * @param integer $intCategoryId
     * @param integer $intLabelId
     * @param integer $intLimitNumber
     * @param integer $intSortTypeId
     * @param integer $intSortOrderId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadOverallFolderChildPages($intCategoryId = 0, $intLabelId = 0, $intLimitNumber = 0, $intSortTypeId = 0, $intSortOrderId = 0)
    {
        $this->core->logger->debug('core->models->Folders->loadOverallFolderChildPages(' . $intCategoryId . ',' . $intLabelId . ',' . $intLimitNumber . ',' . $intSortTypeId . ',' . $intSortOrderId . ')');

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

        $strSqlOrderBy = ' ORDER BY folders.lft';
        if ($intSortTypeId > 0 && $intSortTypeId != '') {
            switch ($intSortTypeId) {
                case $this->core->sysConfig->sort->types->manual_sort->id:
                    $strSqlOrderBy = ' ORDER BY pages.sortPosition ' . $strSortOrder . ', pages.sortTimestamp ' . (($strSortOrder == 'DESC') ? 'ASC' : 'DESC');
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
                    $strSqlOrderBy = ' ORDER BY title ' . $strSortOrder;
            }
        }

        $strJoinCategory = '';
        if ($intCategoryId > 0 && $intCategoryId != '') {
            $strJoinCategory = ' INNER JOIN pageCategories ON
                            pageCategories.pageId = pages.pageId AND
                            pageCategories.version = pages.version AND
                            pageCategories.category = ' . $intCategoryId;
        }

        $strJoinLabel = '';
        if ($intLabelId > 0 && $intLabelId != '') {
            $strJoinLabel = ' INNER JOIN pageLabels ON
                          pageLabels.pageId = pages.pageId AND
                          pageLabels.version = pages.version AND
                          pageLabels.label = ' . $intLabelId;
        }

        $strSqlLimit = '';
        if ($intLimitNumber > 0 && $intLimitNumber != '') {
            $strSqlLimit = ' LIMIT ' . $intLimitNumber;
        }

        $strFolderFilter = '';
        $strPageFilter = '';
        $strFolderPublishedFilter = '';
        $strPagePublishedFilter = '';
        if (!isset($_SESSION['sesTestMode']) || (isset($_SESSION['sesTestMode']) && $_SESSION['sesTestMode'] == false)) {
            $timestamp = time();
            $now = date('Y-m-d H:i:s', $timestamp);

            $strFolderFilter = 'AND folderProperties.idStatus = ' . $this->core->sysConfig->status->live;
            $strPageFilter = 'AND pageProperties.idStatus = ' . $this->core->sysConfig->status->live;
            $strPagePublishedFilter = ' AND pageProperties.published <= \'' . $now . '\'';
        }

        $sqlStmt = $this->core->dbh->query('SELECT DISTINCT folders.id AS idFolder, folderProperties.idStatus AS folderStatus, folders.depth,
                                              pages.id AS idPage, pageTitles.title AS title, genericForms.genericFormId, genericForms.version, pageProperties.showInNavigation,
                                              pageProperties.idStatus AS pageStatus, urls.url, languageCode, pageProperties.idPageTypes, pageProperties.created AS pageCreated,
                                              pageProperties.changed AS pageChanged, pageProperties.published AS pagePublished, pageProperties.idDestination, pageProperties.hideInSitemap,
                                              rootLevelTitles.title AS rootTitle, CONCAT(users.fname, \' \', users.sname) AS creator, pageTargets.target
                                          FROM folders
                                            INNER JOIN folderProperties ON 
                                              folderProperties.folderId = folders.folderId AND 
                                              folderProperties.version = folders.version AND 
                                              folderProperties.idLanguages = ?
                                            INNER JOIN pages ON
                                              pages.idParent = folders.id AND
                                              pages.idParentTypes = ?
                                            INNER JOIN pageProperties ON 
                                              pageProperties.pageId = pages.pageId AND 
                                              pageProperties.version = pages.version AND 
                                              pageProperties.idLanguages = ?
                                              ' . $strPageFilter . '
                                              ' . $strPagePublishedFilter . '
                                              ' . $strJoinCategory . '
                                              ' . $strJoinLabel . '
                                            LEFT JOIN pageTargets ON 
                                              pageTargets.pageId = pages.pageId AND 
                                              pageTargets.version = pages.version AND 
                                              pageTargets.idLanguages = ?                                            
                                            INNER JOIN genericForms ON
                                              genericForms.id = pageProperties.idGenericForms
                                            INNER JOIN pageTitles ON
                                              pageTitles.pageId = pages.pageId AND
                                              pageTitles.version = pages.version AND
                                              pageTitles.idLanguages = ?
                                            INNER JOIN urls ON
                                              urls.relationId = pages.pageId AND
                                              urls.version = pages.version AND
                                              urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND
                                              urls.idLanguages = ? AND
                                              urls.isMain = 1
                                            INNER JOIN rootLevelTitles ON
                                              rootLevelTitles.idRootLevels = folders.idRootLevels AND
                                              rootLevelTitles.idLanguages = ?
                                            LEFT JOIN users ON
                                              users.id = pageProperties.creator
                                            LEFT JOIN languages  ON
                                              languages.id = ?
                                          ,folders AS parent
                                           WHERE folders.lft BETWEEN parent.lft AND parent.rgt AND
                                                 folders.idRootLevels = parent.idRootLevels
                                                 ' . $strFolderFilter . '
                                           ' . $strSqlOrderBy . '
                                           ' . $strSqlLimit, array(
                                                                  $this->intLanguageId,
                                                                  $this->core->sysConfig->parent_types->folder,
                                                                  $this->intLanguageId,
                                                                  $this->intLanguageId,
                                                                  $this->intLanguageId,
                                                                  $this->intLanguageId,
                                                                  $this->intLanguageId,
                                                                  $this->intLanguageId
                                                             ));
        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadRootLevelChilds
     * @param integer $intRootLevelId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadRootLevelChilds($intRootLevelId)
    {
        $this->core->logger->debug('core->models->Folders->loadRootLevelChilds(' . $intRootLevelId . ')');

        $sqlStmt = $this->core->dbh->query('SELECT folders.id AS folderId, folderTitles.title AS folderTitle, folderProperties.idStatus AS folderStatus, folders.depth,
                                               pages.id AS idPage, pages.pageId, pageTitles.title AS pageTitle, pages.isStartPage, pageProperties.idStatus AS pageStatus
                                              FROM folders
                                                INNER JOIN folderProperties ON 
                                                  folderProperties.folderId = folders.folderId AND 
                                                  folderProperties.version = folders.version AND 
                                                  folderProperties.idLanguages = ?
                                                INNER JOIN folderTitles ON
                                                  folderTitles.folderId = folders.folderId AND
                                                  folderTitles.version = folders.version AND
                                                  folderTitles.idLanguages = ?
                                                LEFT JOIN pages ON
                                                  pages.idParent = folders.id AND
                                                  pages.idParentTypes = ?
                                                LEFT JOIN pageProperties ON 
                                                  pageProperties.pageId = pages.pageId AND 
                                                  pageProperties.version = pages.version AND 
                                                  pageProperties.idLanguages = ? AND
                                                  pageProperties.idPageTypes != ? AND
                                                  pageProperties.idPageTypes != ?
                                                LEFT JOIN pageTitles ON pageTitles.pageId = pages.pageId
                                                  AND pageTitles.version = pages.version
                                                  AND pageTitles.idLanguages = ?
                                              WHERE folders.idRootLevels = ?
                                                ORDER BY folders.lft, pages.isStartPage DESC, pages.sortPosition ASC, pages.sortTimestamp DESC, pages.id ASC', array($this->intLanguageId, $this->intLanguageId, $this->core->sysConfig->parent_types->folder, $this->intLanguageId, $this->core->sysConfig->page_types->link->id, $this->core->sysConfig->page_types->external->id, $this->intLanguageId, $intRootLevelId));

        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadGlobalRootLevelChilds
     * @param integer $intRootLevelId
     * @param integer $intRootLevelGroupId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadGlobalRootLevelChilds($intRootLevelId, $intRootLevelGroupId = 0)
    {
        $this->core->logger->debug('core->models->Folders->loadGlobalRootLevelChilds(' . $intRootLevelId . ', ' . $intRootLevelGroupId . ')');

        $objSelect = $this->getFolderTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objFolderTable, array('folderId' => 'id', 'depth'))
            ->join('folderProperties', 'folderProperties.folderId = folders.folderId AND
                                          folderProperties.version = folders.version AND
                                          folderProperties.idLanguages = ' . $this->intLanguageId, array('folderStatus' => 'idStatus'))
            ->join('folderTitles', 'folderTitles.folderId = folders.folderId AND
                                      folderTitles.version = folders.version AND
                                      folderTitles.idLanguages = ' . $this->intLanguageId, array('folderTitle' => 'title'));
        if ($intRootLevelGroupId == $this->core->sysConfig->root_level_groups->product) {
            $objSelect->join('globals AS lP', 'lP.idParent = folders.id AND
                                         lP.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array('idGlobal' => 'id', 'globalId', 'isStartGlobal'))
                ->join('globalLinks', 'globalLinks.idGlobals = lP.id', array())
                ->join('globals', 'globals.globalId = globalLinks.globalId', array());
        } else {
            $objSelect->join('globals', 'globals.idParent = folders.id AND
                                   globals.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array('idGlobal' => 'id', 'globalId', 'isStartGlobal'));
        }
        $objSelect->join('globalProperties', 'globalProperties.globalId = globals.globalId AND
                                          globalProperties.version = globals.version AND
                                          globalProperties.idLanguages = ' . $this->intLanguageId, array('globalStatus' => 'idStatus'))
            ->joinLeft('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = ' . $this->intLanguageId, array('globalTitle' => 'title'))
            ->where('folders.idRootLevels = ?', $intRootLevelId)
            ->where('globals.id = (SELECT p.id FROM globals p WHERE p.globalId = globals.globalId ORDER BY p.version DESC LIMIT 1)')
            ->order('folders.lft')
            ->order('globals.isStartGlobal DESC')
            ->order('globals.sortPosition ASC')
            ->order('globals.sortTimestamp DESC')
            ->order('globals.id ASC');

        return $this->objFolderTable->fetchAll($objSelect);
    }

    /**
     * loadWebsiteRootLevelChilds
     * @param integer $intRootLevelId
     * @param integer $intDepth
     * @param integer $intDisplayOptionId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadWebsiteRootLevelChilds($intRootLevelId, $intDepth = 1, $intDisplayOptionId = 1, $blnLoadFilter = false, $blnLoadSitemap = false, $blnFilterDisplayEnvironment = true)
    {
        $this->core->logger->debug('core->models->Folders->loadWebsiteRootLevelChilds(' . $intRootLevelId . ',' . $intDepth . ',' . $intDisplayOptionId . ')');

        $strFolderFilter = '';
        $strPageFilter = '';
        if (!isset($_SESSION['sesTestMode']) || (isset($_SESSION['sesTestMode']) && $_SESSION['sesTestMode'] == false)) {
            $strFolderFilter = ' AND folderProperties.idStatus = ' . $this->core->sysConfig->status->live;
            $strPageFilter = ' AND pageProperties.idStatus = ' . $this->core->sysConfig->status->live;
        }

        $objSelect1 = $this->getFolderTable()->select();
        $objSelect1->setIntegrityCheck(false);

        $objSelect1->from('folders', array(
                                          'idFolder'                                                                                                                                                                                                               => 'id', 'folderId', 'folderTitle' => 'folderTitles.title', 'depth', 'folderOrder' => 'sortPosition', 'parentId' => new Zend_Db_Expr('IF(folders.idParentFolder = 0, pages.idParent, folders.idParentFolder)'),
                                          'idPage'                                                                                                                                                                                                                 => 'pages.id', 'pageId' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', pl.pageId, pages.pageId)'), 'pages.isStartPage', 'pageOrder' => 'pages.sortPosition', 'url' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plUrls.url, urls.url)'), 'external' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plExternals.external, pageExternals.external)'), 'target' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plTargets.target, pageTargets.target)'), 'title' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plTitle.title, pageTitles.title)'),
                                          'pageProperties.idPageTypes', 'pageProperties.changed', 'languages.languageCode', 'rootLevels.idRootLevelGroups', 'folders.lft', 'folders.sortPosition', 'folders.sortTimestamp', 'pages.idParentTypes', 'genericFormId' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plGenericForms.genericFormId, genericForms.genericFormId)')
                                     ))
            ->join('folderProperties', 'folderProperties.folderId = folders.folderId AND folderProperties.version = folders.version AND folderProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE) . $strFolderFilter, array())
            ->join('rootLevels', 'folders.idRootLevels = rootLevels.id', array())
            ->join('folderTitles', 'folderTitles.folderId = folders.folderId AND folderTitles.version = folders.version AND folderTitles.idLanguages = folderProperties.idLanguages', array())
            ->joinLeft('languages', 'languages.id = folderProperties.idLanguages', array())
            ->joinLeft('pages', 'pages.idParent = folders.id AND pages.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array())
            ->joinLeft('pageProperties', 'pageProperties.pageId = pages.pageId AND  pageProperties.version = pages.version AND pageProperties.idLanguages = folderProperties.idLanguages' . $strPageFilter, array())
            ->joinLeft('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = pageProperties.idLanguages', array())
            ->joinLeft('urls', 'urls.relationId = pages.pageId AND urls.version = pages.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND urls.idLanguages = pageProperties.idLanguages AND urls.isMain = 1', array())
            ->joinLeft('pageExternals', 'pageExternals.pageId = pages.pageId AND pageExternals.version = pages.version AND pageExternals.idLanguages = pageProperties.idLanguages', array())
            ->joinLeft('pageTargets', 'pageTargets.pageId = pages.pageId AND pageTargets.version = pages.version AND pageTargets.idLanguages = pageProperties.idLanguages', array())
            ->joinLeft('pageLinks', 'pageLinks.idPages = pages.id', array())
            ->joinLeft(array('pl' => 'pages'), 'pl.id = (SELECT p.id FROM pages AS p WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1)', array())
            ->joinLeft(array('plProperties' => 'pageProperties'), 'plProperties.pageId = pl.pageId AND  plProperties.version = pl.version AND plProperties.idLanguages = folderProperties.idLanguages' . (($blnLoadSitemap) ? ' AND plProperties.hideInSitemap = 0' : '') . $strPageFilter, array())
            ->joinLeft(array('plTitle' => 'pageTitles'), 'plTitle.pageId = pl.pageId AND plTitle.version = pl.version AND plTitle.idLanguages = plProperties.idLanguages', array())
            ->joinLeft(array('plUrls' => 'urls'), 'plUrls.relationId = pl.pageId AND plUrls.version = pl.version AND plUrls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND plUrls.idLanguages = plProperties.idLanguages AND plUrls.isMain = 1', array())
            ->joinLeft(array('plExternals' => 'pageExternals'), 'plExternals.pageId = pl.pageId AND plExternals.version = pl.version AND plExternals.idLanguages = plProperties.idLanguages', array())
            ->joinLeft(array('plTargets' => 'pageTargets'), 'plTargets.pageId = pl.pageId AND plTargets.version = pl.version AND plTargets.idLanguages = plProperties.idLanguages', array())
            ->joinLeft(array('plGenericForms' => 'genericForms'), 'plGenericForms.id = plProperties.idGenericForms', array())
            ->joinLeft(array('genericForms'), 'genericForms.id = pageProperties.idGenericForms', array())
            ->where('folders.idRootLevels = ?', $intRootLevelId)
            ->where('folders.depth <= ?', $intDepth);

        if ($blnFilterDisplayEnvironment) {
            switch ($this->core->strDisplayType) {
                case $this->core->sysConfig->display_type->website:
                    $objSelect1->where('folderProperties.showInWebsite = 1')
                        ->where('pageProperties.showInWebsite = 1');
                    break;
                case $this->core->sysConfig->display_type->tablet:
                    $objSelect1->where('folderProperties.showInTablet = 1')
                        ->where('pageProperties.showInTablet = 1');
                    break;
                case $this->core->sysConfig->display_type->mobile:
                    $objSelect1->where('folderProperties.showInMobile = 1')
                        ->where('pageProperties.showInMobile = 1');
                    ;
                    break;
            }
        }

        if ($intDisplayOptionId > 0) {
            $objSelect1->where('((folderProperties.showInNavigation = ? AND folders.depth = 0) OR (folderProperties.showInNavigation = 1 AND folders.depth > 0))', $intDisplayOptionId)
                ->where('((pageProperties.showInNavigation = ? AND folders.depth = 0) OR (pageProperties.showInNavigation = 1 AND folders.depth > 0))', $intDisplayOptionId);
        } elseif ($intDisplayOptionId != -1) {
            $objSelect1->where('folderProperties.showInNavigation > 0')
                ->where('pageProperties.showInNavigation > 0');
        }

        if (!empty($this->intSegmentId)) {
            $objSelect1->where('pages.idSegments = 0 OR pages.idSegments = ?', $this->intSegmentId);
            $objSelect1->where('folders.idSegments = 0 OR folders.idSegments = ?', $this->intSegmentId);
        }

        if ($blnLoadSitemap) {
            $objSelect1->where('folderProperties.hideInSitemap = 0')
                ->where('pageProperties.hideInSitemap = 0');
        }

        $objSelect2 = $this->getRootLevelTable()->select();
        $objSelect2->setIntegrityCheck(false);

        $objSelect2->from('pages', array(
                                        'idFolder'                                                                                                              => new Zend_Db_Expr('-1'), 'folderId' => new Zend_Db_Expr('""'), 'folderTitle' => new Zend_Db_Expr('""'), 'depth'  => new Zend_Db_Expr('0'), 'folderOrder' => new Zend_Db_Expr('-1'), 'parentId' => 'pages.idParent',
                                        'idPage'                                                                                                                => 'pages.id', 'pageId' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', pl.pageId, pages.pageId)'), 'pages.isStartPage', 'pageOrder' => 'pages.sortPosition', 'url' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plUrls.url, urls.url)'), 'external' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plExternals.external, pageExternals.external)'), 'target' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plTargets.target, pageTargets.target)'), 'title' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plTitle.title, pageTitles.title)'),
                                        'pageProperties.idPageTypes', 'pageProperties.changed', 'languages.languageCode', 'rootLevels.idRootLevelGroups', 'lft' => new Zend_Db_Expr('0'), 'pages.sortPosition', 'pages.sortTimestamp', 'pages.idParentTypes', 'genericFormId' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plGenericForms.genericFormId, genericForms.genericFormId)')
                                   ))
            ->join('rootLevels', 'pages.idParent = rootLevels.id', array())
            ->join('pageProperties', 'pageProperties.pageId = pages.pageId AND  pageProperties.version = pages.version AND pageProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE) . $strPageFilter, array())
            ->join('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = pageProperties.idLanguages', array())
            ->joinLeft('languages', 'languages.id = pageProperties.idLanguages', array())
            ->joinLeft('urls', 'urls.relationId = pages.pageId AND urls.version = pages.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND urls.idLanguages = pageProperties.idLanguages AND urls.isMain = 1', array())
            ->joinLeft('pageExternals', 'pageExternals.pageId = pages.pageId AND pageExternals.version = pages.version AND pageExternals.idLanguages = pageProperties.idLanguages', array())
            ->joinLeft('pageTargets', 'pageTargets.pageId = pages.pageId AND pageTargets.version = pages.version AND pageTargets.idLanguages = pageProperties.idLanguages', array())
            ->joinLeft('pageLinks', 'pageLinks.idPages = pages.id', array())
            ->joinLeft(array('pl' => 'pages'), 'pl.id = (SELECT p.id FROM pages AS p WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1)', array())
            ->joinLeft(array('plProperties' => 'pageProperties'), 'plProperties.pageId = pl.pageId AND  plProperties.version = pl.version AND plProperties.idLanguages = pageProperties.idLanguages' . (($blnLoadSitemap) ? ' AND plProperties.hideInSitemap = 0' : '') . $strPageFilter, array())
            ->joinLeft(array('plTitle' => 'pageTitles'), 'plTitle.pageId = pl.pageId AND plTitle.version = pl.version AND plTitle.idLanguages = plProperties.idLanguages', array())
            ->joinLeft(array('plUrls' => 'urls'), 'plUrls.relationId = pl.pageId AND plUrls.version = pl.version AND plUrls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND plUrls.idLanguages = plProperties.idLanguages AND plUrls.isMain = 1', array())
            ->joinLeft(array('plExternals' => 'pageExternals'), 'plExternals.pageId = pl.pageId AND plExternals.version = pl.version AND plExternals.idLanguages = plProperties.idLanguages', array())
            ->joinLeft(array('plTargets' => 'pageTargets'), 'plTargets.pageId = pl.pageId AND plTargets.version = pl.version AND plTargets.idLanguages = plProperties.idLanguages', array())
            ->joinLeft(array('plGenericForms' => 'genericForms'), 'plGenericForms.id = plProperties.idGenericForms', array())
            ->joinLeft(array('genericForms'), 'genericForms.id = pageProperties.idGenericForms', array())
            ->where('pages.idParent = ?', $intRootLevelId)
            ->where('pages.idParentTypes = ?', $this->core->sysConfig->parent_types->rootlevel);

        if ($blnFilterDisplayEnvironment) {
            switch ($this->core->strDisplayType) {
                case $this->core->sysConfig->display_type->website:
                    $objSelect2->where('pageProperties.showInWebsite = 1');
                    break;
                case $this->core->sysConfig->display_type->tablet:
                    $objSelect2->where('pageProperties.showInTablet = 1');
                    break;
                case $this->core->sysConfig->display_type->mobile:
                    $objSelect2->where('pageProperties.showInMobile = 1');
                    break;
            }
        }

        if ($intDisplayOptionId > 0) {
            $objSelect2->where('pageProperties.showInNavigation = ?', $intDisplayOptionId);
        } elseif ($intDisplayOptionId != -1) {
            $objSelect2->where('pageProperties.showInNavigation > 0');
        }

        if (!empty($this->intSegmentId)) {
            $objSelect2->where('pages.idSegments = 0 OR pages.idSegments = ?', $this->intSegmentId);
        }

        if ($blnLoadSitemap) {
            $objSelect2->where('pageProperties.hideInSitemap = 0');
        }

        if ($blnLoadFilter) {
            $objSelect1->joinLeft(array('filter' => 'page-DEFAULT_PRODUCT_TREE-1-Instances'), 'filter.pageId = pages.pageId AND filter.version = pages.version AND filter.idLanguages = pageProperties.idLanguages', array('entry_label', 'entry_category', 'entry_point', 'entry_sorttype'));
            $objSelect2->joinLeft(array('filter' => 'page-DEFAULT_PRODUCT_TREE-1-Instances'), 'filter.pageId = pages.pageId AND filter.version = pages.version AND filter.idLanguages = pageProperties.idLanguages', array('entry_label', 'entry_category', 'entry_point', 'entry_sorttype'));
        }

        $objSelect = $this->getRootLevelTable()->select()
        ->distinct()
        ->union(array($objSelect1, $objSelect2))
        ->order('lft')
        ->order('isStartPage DESC')
        ->order('sortPosition ASC')
        ->order('sortTimestamp DESC');


        return $this->getRootLevelTable()->fetchAll($objSelect);
    }

    /**
     * loadWebsiteGlobalTree
     * @param integer $intParentId
     * @param array $arrFilterOptions
     * @param integer $intRootLevelGroupId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function loadWebsiteGlobalTree($intParentId, $arrFilterOptions = array(), $intRootLevelGroupId = 0, $intDepth = 99){
        $this->core->logger->debug('core->models->Folders->loadWebsiteGlobalTree('.$intParentId.','.$arrFilterOptions.', '.$intRootLevelGroupId.', '.$intDepth.')');

        $strFolderFilter = '';
        $strGlobalFilter = '';
        if(!isset($_SESSION['sesTestMode']) || (isset($_SESSION['sesTestMode']) && $_SESSION['sesTestMode'] == false)){
            $strFolderFilter = 'AND folderProperties.idStatus = '.$this->core->sysConfig->status->live;
            $strGlobalFilter = 'AND globalProperties.idStatus = '.$this->core->sysConfig->status->live;
        }

        $objSelect = $this->getFolderTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objFolderTable, array('idFolder' => 'id', 'folderId', 'parentId' => 'idParentFolder', 'depth', 'folderOrder' => 'sortPosition'))
        ->join(array('parent' => 'folders'), 'parent.id = '.$this->core->dbh->quote($intParentId, Zend_Db::INT_TYPE), array())
        ->join('folderProperties', 'folderProperties.folderId = folders.folderId AND
                                          folderProperties.version = folders.version AND
                                          folderProperties.idLanguages = '.$this->intLanguageId
        .' '.$strFolderFilter,array('folderStatus' => 'idStatus'))
        ->join('folderTitles', 'folderTitles.folderId = folders.folderId AND
                                      folderTitles.version = folders.version AND
                                      folderTitles.idLanguages = '.$this->intLanguageId, array('folderTitle' => 'title'))
        ->join('languages', 'languages.id = folderTitles.idLanguages',array('languageCode'));
        if($intRootLevelGroupId == $this->core->sysConfig->root_level_groups->product){
            $objSelect->join('globals AS lP', 'lP.idParent = folders.id AND
                                        lP.idParentTypes = '.$this->core->sysConfig->parent_types->folder, array('idGlobal' => 'id', 'globalId', 'isStartGlobal', 'globalOrder' => 'sortPosition'))
            ->join('globalLinks', 'globalLinks.idGlobals = lP.id', array())
            ->join('globals', 'globals.globalId = globalLinks.globalId', array('id'))
            ->joinLeft('urls', 'urls.relationId = lP.globalId AND urls.version = lP.version AND urls.idUrlTypes = '.$this->core->sysConfig->url_types->global.' AND urls.idLanguages = '.$this->intLanguageId.' AND urls.isMain = 1', array('url'));
        }else{
            $objSelect->join('globals', 'globals.idParent = folders.id AND
                                   globals.idParentTypes = '.$this->core->sysConfig->parent_types->folder, array('id', 'idGlobal' => 'id', 'globalId', 'isStartGlobal', 'globalOrder' => 'sortPosition'))
            ->joinLeft('urls', 'urls.relationId = globals.globalId AND urls.version = globals.version AND urls.idUrlTypes = '.$this->core->sysConfig->url_types->global.' AND urls.idLanguages = '.$this->intLanguageId.' AND urls.isMain = 1', array('url'));

        }

        $strShowInNavigation = ' AND globalProperties.showInNavigation = 1';
        if (array_key_exists('IgnoreShowInNavigation', $arrFilterOptions) && $arrFilterOptions['IgnoreShowInNavigation'] === true) {
            $strShowInNavigation = ' ';
        }

        $strTypeFilter = '';
        if (array_key_exists('PageType', $arrFilterOptions) && $arrFilterOptions['PageType'] > 0 && $arrFilterOptions['PageType'] != '') {
            $strTypeFilter = ' AND globalProperties.idGlobalTypes = '.$arrFilterOptions['PageType'].' ';
        }

        $objSelect->join('globalProperties', 'globalProperties.globalId = globals.globalId AND
                                          globalProperties.version = globals.version AND
                                          globalProperties.idLanguages = '.$this->intLanguageId.
                                          ' '.$strShowInNavigation.' '.$strGlobalFilter.' '.$strTypeFilter, array('globalStatus' => 'idStatus', 'idGlobalTypes', 'idLanguageFallbacks', 'changed', 'published'))              
        ->join('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId', 'genericFormVersion' => 'version'))
        ->joinLeft('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = '.$this->intLanguageId, array('globalTitle' => 'title'))
        ->joinLeft(array('fallbackTitles' => 'globalTitles'), 'fallbackTitles.globalId = globals.globalId AND fallbackTitles.version = globals.version AND fallbackTitles.idLanguages = globalProperties.idLanguageFallbacks', array('fallbackTitle' => 'title'))
        ->joinLeft(array('fallbackProperties' => 'globalProperties'), 'fallbackProperties.globalId = globals.globalId AND fallbackProperties.version = globals.version AND fallbackProperties.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array())
        ->joinLeft(array('fallbackGenericForms' => 'genericForms'), 'fallbackGenericForms.id = fallbackProperties.idGenericForms', array('fallbackGenericFormId' => 'genericFormId', 'fallbackGenericFormVersion' => 'version', 'fallbackGenericFormTypeId' => 'idGenericFormTypes'))
        ->where('folders.lft BETWEEN parent.lft AND parent.rgt')
        ->where('folders.idRootLevels = parent.idRootLevels')
        ->where('folders.depth < parent.depth + ' . $intDepth)
        ->where('globals.id = (SELECT p.id FROM globals p WHERE p.globalId = globals.globalId ORDER BY p.version DESC LIMIT 1)');


        if(array_key_exists('CategoryId', $arrFilterOptions) && $arrFilterOptions['CategoryId'] > 0 && $arrFilterOptions['CategoryId'] != ''){
            $objSelect->join('globalCategories', 'globalCategories.globalId = globals.globalId AND globalCategories.version = globals.version AND globalCategories.idLanguages = globalProperties.idLanguages', array())
            ->where('globalCategories.category = ?', $arrFilterOptions['CategoryId']);
        }

        if(array_key_exists('LabelId', $arrFilterOptions) && $arrFilterOptions['LabelId'] > 0 && $arrFilterOptions['LabelId']  != ''){
            $objSelect->join('globalLabels', 'globalLabels.globalId = globals.globalId AND globalLabels.version = globals.version AND globalLabels.idLanguages = globalProperties.idLanguages', array())
            ->where('globalLabels.label = ?', $arrFilterOptions['LabelId'] );
        }

        if(array_key_exists('SortType', $arrFilterOptions) && $arrFilterOptions['SortType'] > 0 && $arrFilterOptions['SortType'] != ''){
            $strSortOrder = '';

            if(array_key_exists('SortOrder', $arrFilterOptions) && $arrFilterOptions['SortOrder'] > 0 && $arrFilterOptions['SortOrder'] != ''){
                switch($arrFilterOptions['SortOrder']){
                    case $this->core->sysConfig->sort->orders->asc->id:
                        $strSortOrder = ' ASC';
                        break;
                    case $this->core->sysConfig->sort->orders->desc->id:
                        $strSortOrder = ' DESC';
                        break;
                }
            }

            switch($arrFilterOptions['SortType']){
                case $this->core->sysConfig->sort->types->manual_sort->id:
                    $objSelect->order(array('folders.sortPosition'.$strSortOrder, 'globals.sortPosition'.$strSortOrder, 'globals.sortTimestamp'.(($strSortOrder == 'DESC') ? ' ASC' : ' DESC')));
                    break;
                case $this->core->sysConfig->sort->types->created->id:
          $objSelect->order(array('globalProperties.created'.$strSortOrder));
          break;
        case $this->core->sysConfig->sort->types->changed->id:
          $objSelect->order(array('globalProperties.changed'.$strSortOrder));
          break;
        case $this->core->sysConfig->sort->types->published->id:
          $objSelect->order(array('globalProperties.published'.$strSortOrder));
          break;
        case $this->core->sysConfig->sort->types->alpha->id:
          $objSelect->order(array('globalTitles.title'.$strSortOrder)); 
          break;
      }
    }else{
      $objSelect->order('folders.lft')
                ->order('globals.isStartGlobal DESC')
                ->order('globals.sortPosition ASC')
                ->order('globals.sortTimestamp DESC')
                ->order('globals.id ASC');
    }
    
    if(array_key_exists('Number', $arrFilterOptions) && $arrFilterOptions['Number'] > 0 && $arrFilterOptions['Number'] != ''){
      $objSelect->limit($arrFilterOptions['Number']);
    }
    
    return $this->objFolderTable->fetchAll($objSelect);    
  }

    /**
     * loadLimitedRootLevelChilds
     * @param integer $intRootLevelId
     * @param integer $intLimitNumber = 10
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadLimitedRootLevelChilds($intRootLevelId, $intLimitNumber = 10)
    {
        $this->core->logger->debug('core->models->Folders->loadRootLevelChilds(' . $intRootLevelId . ',' . $intLimitNumber . ')');

        /*$objSelect = $this->getPageTable()->select();
        $objSelect->setIntegrityCheck(false);
        $objSelect->from($this->getPageTable(), array(idPage => 'id', 'idParent', 'idParentTypes', 'pageTitle' => 'IF(displayTitle.title <> \'\', displayTitle.title, fallbackTitle.title)'))
                  ->joinInner('pageProperties', 'pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version', array())
                  ->joinInner('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = pageProperties.idLanguages', array())
                  ->joinInner('languages', 'languages.id = pageTitles.idLanguages', array('languageCodes' => 'GROUP_CONCAT(languages.languageCode SEPARATOR \', \')'))
                  ->joinLeft(array('displayTitle' => 'pageTitles'), 'displayTitle.pageId = pages.pageId AND displayTitle.version = pages.version AND displayTitle.idLanguages = '.Zend_Auth::getInstance()->getIdentity()->languageId, array())
                  ->joinInner(array('fallbackTitle' => 'pageTitles'), 'fallbackTitle.pageId = pages.pageId AND fallbackTitle.version = pages.version AND fallbackTitle.idLanguages = 0', array())
                  ->joinLeft(array('editor' => 'users'), 'editor.id = pageProperties.idUsers', array('editor' => 'CONCAT(`editor`.`fname`, \' \', `editor`.`sname`)', 'pageProperties.changed'))
                  ->where('idParent = ?', $intRootLevelId)
                  ->where('idParentTypes = ?', $this->core->sysConfig->parent_types->rootlevel)
                  //->where('isStartPage = 0')
                  ->group('pages.pageId')
                  ->order($strOrderColumn.' '.strtoupper($strSortOrder))
                  ->limit($intLimitNumber);

        echo $objSelect;

        exit();*/


        $sqlStmt = $this->core->dbh->query("SELECT pages.id AS idPage, pages.idParent, pages.idParentTypes, IF(displayTitle.title <> '', displayTitle.title, fallbackTitle.title) AS pageTitle,
                                                                                         genericForms.genericFormId, genericForms.version, pageProperties.idPageTypes AS pageType, pageTypes.title As type, 'page' AS elementType,
                                                                                       pages.isStartPage, pageProperties.idStatus AS pageStatus, pageProperties.created, pageProperties.changed, pageProperties.idTemplates  AS templateId,
                                                                                       GROUP_CONCAT(languages.languageCode SEPARATOR ', ') AS languageCodes,
                                                                                       CONCAT(`editor`.`fname`, ' ', `editor`.`sname`) AS changeUser,
                                                                                       (SELECT pt.title FROM pageLinks, pages AS p LEFT JOIN pageTitles AS pt ON pt.pageId = p.pageId AND pt.version = p.version AND pt.idLanguages = " . $this->intLanguageId . " WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1) AS pageLinkTitle,
                                                                                 (SELECT pt.title FROM pageLinks, pages AS p LEFT JOIN pageTitles AS pt ON pt.pageId = p.pageId AND pt.version = p.version AND pt.idLanguages = 0 WHERE pageLinks.idPages = pages.id AND pageLinks.pageId = p.pageId ORDER BY p.version DESC LIMIT 1) AS pageGuiLinkTitle
                                                                            FROM pages
                                                                                LEFT JOIN folders ON folders.idRootLevels = " . $intRootLevelId . "
                                                                                INNER JOIN pageProperties ON
                                                                                            pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version
                                                                                        INNER JOIN pageTitles ON
                                                                                            pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = pageProperties.idLanguages
                                                                                        INNER JOIN languages ON languages.id = pageTitles.idLanguages
                                                                                        LEFT JOIN pageTitles AS displayTitle ON
                                                                                            displayTitle.pageId = pages.pageId AND displayTitle.version = pages.version AND displayTitle.idLanguages = " . Zend_Auth::getInstance()->getIdentity()->languageId . "
                                                                                        INNER JOIN pageTitles As fallbackTitle ON
                                                                                            fallbackTitle.pageId = pages.pageId AND fallbackTitle.version = pages.version AND fallbackTitle.idLanguages = 0
                                                                                        LEFT JOIN users AS editor ON editor.id = pageProperties.idUsers
                                                                                        LEFT JOIN pageTypes ON pageTypes.id = pageProperties.idPageTypes
                                                                                        LEFT JOIN genericForms ON genericForms.id = pageProperties.idGenericForms
                                                                              WHERE (pages.idParent = folders.id AND pages.idParentTypes = " . $this->core->sysConfig->parent_types->folder . ") OR
                                                                                (pages.idParent = " . $intRootLevelId . " AND pages.idParentTypes = " . $this->core->sysConfig->parent_types->rootlevel . ")
                                                                              ORDER BY pageProperties.changed DESC,
                                                         pageProperties.created DESC
                                            LIMIT " . $intLimitNumber, array());

        //print_r($sqlStmt);

        //exit();

        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadRootLevelLanguages
     * @param integer $intRootLevelId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadRootLevelLanguages($intRootLevelId)
    {
        $this->core->logger->debug('core->models->Folders->loadRootLevelLanguages(' . $intRootLevelId . ')');
        $sqlStmt = $this->core->dbh->query('SELECT languages.id, languages.languageCode, languages.title, rootLevelLanguages.isFallback
                                              FROM rootLevelLanguages
                                                INNER JOIN languages ON 
                                                  languages.id = rootLevelLanguages.idLanguages                                                
                                              WHERE rootLevelLanguages.idRootLevels = ?
                                                ORDER BY rootLevelLanguages.order', array($intRootLevelId));

        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadRootLevelFolders
     * @param integer $intRootLevelId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadRootLevelFolders($intRootLevelId, $intRootLevelLanguageId = null)
    {
        $this->core->logger->debug('core->models->Folders->loadRootLevelFolders(' . $intRootLevelId . ',' . $intRootLevelLanguageId . ')');

        $intRootLevelLanguageId = ($intRootLevelLanguageId == null) ? $this->intLanguageId : $intRootLevelLanguageId;

        $sqlStmt = $this->core->dbh->query('SELECT folders.id, folderTitles.title, folderProperties.idStatus, folders.depth, folders.idRootLevels, rootLevelTitles.title AS rootLevelTitle
                                              FROM folders
                                                INNER JOIN folderProperties ON 
                                                  folderProperties.folderId = folders.folderId AND 
                                                  folderProperties.version = folders.version AND 
                                                  folderProperties.idLanguages = ?
                                                INNER JOIN folderTitles ON
                                                  folderTitles.folderId = folders.folderId AND
                                                  folderTitles.version = folders.version AND
                                                  folderTitles.idLanguages = ?
                                                INNER JOIN rootLevels ON
                                                  rootLevels.id = folders.idRootLevels
                                                INNER JOIN rootLevelTitles ON
                                                  rootLevelTitles.idRootLevels = rootLevels.id AND
                                                  rootLevelTitles.idLanguages = ?
                                              WHERE folders.idRootLevels = ?
                                                ORDER BY folders.lft, folders.sortPosition ASC, folders.sortTimestamp DESC', array($this->intLanguageId, $this->intLanguageId, $intRootLevelLanguageId, $intRootLevelId));

        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadParentFolders
     * @param integer $intFolderId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadParentFolders($intFolderId)
    {
        $this->core->logger->debug('core->models->Folders->loadParentFolders(' . $intFolderId . ')');

        $sqlStmt = $this->core->dbh->query('SELECT folders.id, folders.folderId, folders.isSecure, folderProperties.isUrlFolder, folderTitles.title,
                                               urls.url, (SELECT languageCode FROM languages WHERE id = ?) AS languageCode
                                          FROM folders
                                            LEFT JOIN folderProperties ON 
                                              folderProperties.folderId = folders.folderId AND 
                                              folderProperties.version = folders.version AND 
                                              folderProperties.idLanguages = ?
                                            INNER JOIN pages ON
                                              pages.idParent = folders.id AND
                                              pages.idParentTypes = ? AND
                                              pages.isStartPage = 1
                                            LEFT JOIN urls ON
                                              urls.relationId = pages.pageId AND
                                              urls.version = pages.version AND
                                              urls.idUrlTypes = ' . $this->core->sysConfig->url_types->page . ' AND
                                              urls.idLanguages = ? AND
                                              urls.isMain = 1
                                            LEFT JOIN folderTitles ON
                                              folderTitles.folderId = folders.folderId AND
                                              folderTitles.version = folders.version AND
                                              folderTitles.idLanguages = ?
                                          ,folders AS parent
                                           WHERE parent.id = ? AND
                                                 folders.lft <= parent.lft AND
                                                 folders.rgt >= parent.rgt AND
                                                 folders.idRootLevels = parent.idRootLevels
                                             ORDER BY folders.rgt', array(
                                                                         $this->intLanguageId,
                                                                         $this->intLanguageId,
                                                                         $this->core->sysConfig->parent_types->folder,
                                                                         $this->intLanguageId,
                                                                         $this->intLanguageId,
                                                                         $intFolderId
                                                                    ));

        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadMediaParentFolders
     * @param integer $intFolderId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadMediaParentFolders($intFolderId)
    {
        $this->core->logger->debug('core->models->Folders->loadParentFolders(' . $intFolderId . ')');

        $sqlStmt = $this->core->dbh->query('SELECT DISTINCT folders.id, folders.folderId, folders.isSecure, folderProperties.isUrlFolder, folderTitles.title,
                                            (SELECT languageCode FROM languages WHERE id = ?) AS languageCode
                                          FROM folders
                                            LEFT JOIN folderProperties ON 
                                              folderProperties.folderId = folders.folderId AND 
                                              folderProperties.version = folders.version AND 
                                              folderProperties.idLanguages = ?
                                            INNER JOIN files ON
                                              files.idParent = folders.id AND
                                              files.idParentTypes = ?
                                            LEFT JOIN folderTitles ON
                                              folderTitles.folderId = folders.folderId AND
                                              folderTitles.version = folders.version AND
                                              folderTitles.idLanguages = ?
                                          ,folders AS parent
                                          WHERE parent.id = ? AND
                                              folders.lft <= parent.lft AND
                                              folders.rgt >= parent.rgt AND
                                              folders.idRootLevels = parent.idRootLevels
                                            ORDER BY folders.rgt', array(
                                                                        $this->intLanguageId,
                                                                        $this->intLanguageId,
                                                                        $this->core->sysConfig->parent_types->folder,
                                                                        $this->intLanguageId,
                                                                        $intFolderId
                                                                   ));
        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
    }

    /**
     * loadGlobalParentFolders
     * @param integer $intFolderId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadGlobalParentFolders($intFolderId, $intRootLevelGroupId = 0)
    {
        $this->core->logger->debug('core->models->Folders->loadGlobalParentFolders(' . $intFolderId . ',' . $intRootLevelGroupId . ')');

        $objSelect = $this->getFolderTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objFolderTable, array('id', 'folderId', 'isSecure'))
            ->joinLeft('folderProperties', 'folderProperties.folderId = folders.folderId AND
                                          folderProperties.version = folders.version AND
                                          folderProperties.idLanguages = ' . $this->intLanguageId, array('isUrlFolder'))
            ->join(array('parent' => 'folders'), 'parent.id = ' . $this->core->dbh->quote($intFolderId, Zend_Db::INT_TYPE), array())
            ->joinLeft('folderTitles', 'folderTitles.folderId = folders.folderId AND
                                      folderTitles.version = folders.version AND
                                      folderTitles.idLanguages = ' . $this->intLanguageId, array('title'))
            ->join('languages', 'languages.id = folderTitles.idLanguages', array('languageCode'));
        if ($intRootLevelGroupId == $this->core->sysConfig->root_level_groups->product) {
            $objSelect->join('globals AS lP', 'lP.idParent = folders.id AND
                                         lP.idParentTypes = ' . $this->core->sysConfig->parent_types->folder . ' AND
                                         lP.isStartGlobal = 1', array())
                ->join('globalLinks', 'globalLinks.idGlobals = lP.id', array())
                ->join('globals', 'globals.globalId = globalLinks.globalId', array())
                ->joinLeft('urls', 'urls.relationId = lP.globalId AND urls.version = lP.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->global . ' AND urls.idLanguages = ' . $this->intLanguageId . ' AND urls.isMain = 1', array('url'));
        } else {
            $objSelect->join('globals', 'globals.idParent = folders.id AND
                                   globals.idParentTypes = ' . $this->core->sysConfig->parent_types->folder . ' AND
                                   globals.isStartGlobal = 1', array())
                ->joinLeft('urls', 'urls.relationId = globals.globalId AND urls.version = globals.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->global . ' AND urls.idLanguages = ' . $this->intLanguageId . ' AND urls.isMain = 1', array('url'));
        }
        $objSelect->where('folders.lft <= parent.lft')
            ->where('folders.rgt >= parent.rgt')
            ->where('folders.idRootLevels = parent.idRootLevels')
            ->where('globals.id = (SELECT p.id FROM globals p WHERE p.globalId = globals.globalId ORDER BY p.version DESC LIMIT 1)')
            ->order('folders.rgt');

        return $this->objFolderTable->fetchAll($objSelect);
    }

    /**
     * loadRootFolders
     * @param integer $intRootId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadRootFolders($intRootId)
    {
        $this->core->logger->debug('core->models->Folders->loadRootFolders(' . $intRootId . ')');

        $objSelect = $this->getFolderTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('folders', array('id'));
        $objSelect->join('folderProperties', 'folderProperties.folderId = folders.folderId AND
                                          folderProperties.version = folders.version AND
                                          folderProperties.idLanguages = ' . $this->intLanguageId, array('idGenericForms'));
        $objSelect->join('folderTitles', 'folderTitles.folderId = folders.folderId AND folderTitles.version = folders.version AND folderTitles.idLanguages = ' . $this->intLanguageId, array('title'));
        $objSelect->join('genericForms', 'genericForms.id = folderProperties.idGenericForms', array('genericFormId', 'version'));
        $objSelect->where('folders.idRootLevels  = ? AND folders.idParentFolder = 0', $intRootId);

        return $this->getFolderTable()->fetchAll($objSelect);
    }

    /**
     * loadChildFolders
     * @param integer $intFolderId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadChildFolders($intFolderId)
    {
        $this->core->logger->debug('core->models->Folders->loadChildFolders(' . $intFolderId . ')');

        $objSelect = $this->getFolderTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('folders', array('id'));
        $objSelect->join('folderProperties', 'folderProperties.folderId = folders.folderId AND
                                          folderProperties.version = folders.version AND
                                          folderProperties.idLanguages = ' . $this->intLanguageId, array('idGenericForms'));
        $objSelect->join('folderTitles', 'folderTitles.folderId = folders.folderId AND folderTitles.version = folders.version AND folderTitles.idLanguages = ' . $this->intLanguageId, array('title'));
        $objSelect->join('genericForms', 'genericForms.id = folderProperties.idGenericForms', array('genericFormId', 'version'));
        $objSelect->where('folders.idParentFolder = ?', $intFolderId);

        return $this->getFolderTable()->fetchAll($objSelect);
    }


    /**
     * Load all the child folder (cms and global) for the sitemaplink fieldtype
     * @param integer $intFolderId
     * @param string $strGenFormId
     * @param integer $intGenFormVersion
     */
    public function loadChildFoldersForSitemap($intFolderId, $strGenFormId, $intGenFormVersion)
    {
        $this->core->logger->debug('core->models->Folders->loadChildFoldersForSitemap(' . $intFolderId . ', ' . $strGenFormId . ', ' . $intGenFormVersion . ')');

        $objCmsSelect = $this->getFolderTable()->select();
        $objCmsSelect->setIntegrityCheck(false);

        $objCmsSelect->from('folders', array('folders.id', 'folderProperties.idGenericForms', 'folderTitles.title', 'startpageGenForms.genericFormId', 'startpageGenForms.version', 'type' => new Zend_Db_Expr('"page"')));
        $objCmsSelect->join('folderProperties', 'folderProperties.folderId = folders.folderId AND
                                          folderProperties.version = folders.version AND
                                          folderProperties.idLanguages = ' . $this->intLanguageId, array());
        $objCmsSelect->join('folderTitles', 'folderTitles.folderId = folders.folderId AND folderTitles.version = folders.version AND folderTitles.idLanguages = ' . $this->intLanguageId, array());
        $objCmsSelect->join(array('startpages' => 'pages'), 'startpages.idParent = folders.id AND startpages.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array());
        $objCmsSelect->join('pageProperties', 'pageProperties.pageId = startpages.pageId AND pageProperties.version = startpages.version AND pageProperties.idLanguages = ' . $this->intLanguageId, array());
        $objCmsSelect->join(array('startpageGenForms' => 'genericForms'), 'startpageGenForms.id = pageProperties.idGenericForms', array());
        $objCmsSelect->where('startpages.isStartPage = ?', 1);
        $objCmsSelect->where('folders.idParentFolder = ?', $intFolderId);

        $objSelect = null;

        if (array_search($strGenFormId, $this->GLOBAL_ENTRY_GENFORMS) !== false) {
            $objGlobalSelect = $this->getFolderTable()->select();
            $objGlobalSelect->setIntegrityCheck(false);

            $objGlobalSelect->from(array('pageInstances' => 'page-' . $strGenFormId . '-' . $intGenFormVersion . '-Instances'), array('folders.id', 'folderProperties.idGenericForms', 'folderTitles.title', 'genericForms.genericFormId', 'genericForms.version', 'type' => new Zend_Db_Expr('"global"')));
            $objGlobalSelect->join('pages', 'pages.pageId = pageInstances.pageId', array());
            $objGlobalSelect->join('pageProperties', 'pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version AND pageProperties.idLanguages = ' . $this->intLanguageId, array());
            $objGlobalSelect->join('folders', 'folders.id = pageInstances.entry_point', array());
            $objGlobalSelect->join('folderProperties', 'folderProperties.folderId = folders.folderId AND
                                              folderProperties.version = folders.version AND
                                              folderProperties.idLanguages = ' . $this->intLanguageId, array());
            $objGlobalSelect->join('genericForms', 'genericForms.id = folderProperties.idGenericForms', array());
            $objGlobalSelect->join('folderTitles', 'folderTitles.folderId = folders.folderId AND folderTitles.version = folders.version AND folderTitles.idLanguages = ' . $this->intLanguageId, array());
            $objGlobalSelect->where('pages.idParent = ?', $intFolderId);
            $objGlobalSelect->where('pages.idParentTypes = ?', $this->core->sysConfig->parent_types->folder);

            $this->core->logger->debug(strval($objGlobalSelect));

            $objSelect = $this->getFolderTable()->select()->union(array($objCmsSelect, $objGlobalSelect));
        } else {
            $objSelect = $objCmsSelect;
        }

        return $this->getFolderTable()->fetchAll($objSelect);
    }

    /**
     * loadLastRootFolder
     * @param integer $intRootId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadLastRootFolder($intRootId)
    {
        $this->core->logger->debug('core->models->Folders->loadLastRootFolder(' . $intRootId . ')');

        $objSelect = $this->getFolderTable()->select();
        $objSelect->setIntegrityCheck(false);

        /**
         * SELECT `folders`.`id`
         *    FROM `folders`
         *    WHERE (folders.idRootLevels = '?' AND folders.idParentFolder = 0)
         *    ORDER BY folders.lft
         *      LIMIT 1
         */
        $objSelect->from('folders', array('id'));
        $objSelect->where('folders.idRootLevels  = ? AND folders.idParentFolder = 0', $intRootId);
        $objSelect->order(array('folders.lft DESC'));
        $objSelect->limit(1);

        return $this->getFolderTable()->fetchAll($objSelect);
    }

    /**
     * updateSortPosition
     * @param integer $intElementId
     * @param string $strElementType
     * @param integer $intSortPosition
     * @param integer $intRootLevelId = 0
     * @param integer $intParentId = 0
     * @param integer $intRootLevelTypeId = 0
     * @param integer $intRootLevelGroupId = 0
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function updateSortPosition($intElementId, $strElementType, $intSortPosition, $intRootLevelId = 0, $intParentId = 0, $intRootLevelTypeId = 0, $intRootLevelGroupId = 0)
    {
        $this->core->logger->debug('core->models->Folders->updateSortPosition(' . $intElementId . ',' . $strElementType . ',' . $intSortPosition . ',' . $intRootLevelId . ',' . $intParentId . ')');

        try {
            if ($intElementId != '' && $intSortPosition > 0) {
                $strTable = $strElementType . 's';
                if ($strElementType == 'link') {
                    $strTable = 'pages';
                }


                $sqlStmt = $this->core->dbh->query('SELECT ' . $strTable . '.sortPosition FROM ' . $strTable . ' WHERE ' . $strTable . '.id = ?', array($intElementId));
                $this->core->logger->debug('SELECT ' . $strTable . '.sortPosition FROM ' . $strTable . ' WHERE ' . $strTable . '.id = ?');
                $objElement = $sqlStmt->fetch(Zend_Db::FETCH_OBJ);

                $strSortTimestampType = ($objElement->sortPosition >= $intSortPosition) ? 'DESC' : 'ASC';

                $this->core->logger->debug('update: ' . $strTable . ' / elementId: ' . $intElementId . ' / sortPosition: ' . $intSortPosition);
                $this->core->dbh->update($strTable, array('sortPosition' => $intSortPosition, 'sortTimestamp' => date('Y-m-d H:i:s')), $strTable . '.id = ' . $intElementId);

                if ($intParentId > 0) {
                    if ($intRootLevelTypeId == $this->core->sysConfig->root_level_types->global) {
                        $objNaviData = $this->loadGlobalChildNavigation($intParentId, $intRootLevelGroupId, $strSortTimestampType);
                    } else {
                        $objNaviData = $this->loadChildNavigation($intParentId, $strSortTimestampType);
                    }
                } else if ($intRootLevelId > 0) {
                    if ($intRootLevelTypeId == $this->core->sysConfig->root_level_types->global) {
                        $objNaviData = $this->loadGlobalRootNavigation($intParentId, $intRootLevelGroupId, $strSortTimestampType);
                    } else {
                        $objNaviData = $this->loadRootNavigation($intRootLevelId, $strSortTimestampType);
                    }
                } else {
                    throw new Exception('Not able to load navigation, because rootLevelId and parentId are empty or 0!');
                }

                if (count($objNaviData) > 0) {
                    $counter = 1;
                    foreach ($objNaviData as $objNavItem) {
                        if ($objNavItem->isStartElement != 1) {
                            if ($objNavItem->elementType == 'folder') {
                                $this->core->logger->debug('update: ' . $objNavItem->elementType . ' / id: ' . $objNavItem->id . ' / counter: ' . $counter);
                                $this->core->dbh->update('folders', array('sortPosition' => $counter, 'sortTimestamp' => date('Y-m-d H:i:s')), 'folders.id = ' . $objNavItem->id); //('UPDATE folders SET folders.sortPosition = ? WHERE folders.id = ?', array($intSortPosition, $objNavItem->id));
                            } else if ($objNavItem->elementType == 'page') {
                                $this->core->logger->debug('update: ' . $objNavItem->elementType . ' / id: ' . $objNavItem->id . ' / counter: ' . $counter);
                                $this->core->dbh->update('pages', array('sortPosition' => $counter, 'sortTimestamp' => date('Y-m-d H:i:s')), 'pages.id = ' . $objNavItem->id); //('UPDATE pages SET pages.sortPosition = ? WHERE pages.id = ?', array($intSortPosition, $objNavItem->id));
                            } else if ($objNavItem->elementType == 'global') {
                                $intElementId = ($objNavItem->linkGlobalId > 0) ? $objNavItem->linkGlobalId : $objNavItem->id;
                                $this->core->logger->debug('update: ' . $objNavItem->elementType . ' / id: ' . $intElementId . ' / counter: ' . $counter);
                                $this->core->dbh->update('globals', array('sortPosition' => $counter, 'sortTimestamp' => date('Y-m-d H:i:s')), 'globals.id = ' . $intElementId); //('UPDATE globals SET globals.sortPosition = ? WHERE globals.id = ?', array($intSortPosition, $intElementId));
                            }
                            $counter++;
                        }
                    }
                }
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * addFolderNode
     * @param integer $intRootId,
     * @param integer $intParentId
     * @param array $arrData
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addFolderNode($intRootId, $intParentId, $arrData = array())
    {
        try {
            $intFolderId = null;

            $this->getFolderTable();

            $objNestedSet = new NestedSet($this->objFolderTable);
            $objNestedSet->setDBFParent('idParentFolder');
            $objNestedSet->setDBFRoot('idRootLevels');

            /**
             * if $intParentId > 0, has parent folder id
             */
            if ($intParentId != '' && $intParentId > 0) {
                $intFolderId = $objNestedSet->newLastChild($intParentId, $arrData);
            } else {
                $objLastRootFolderData = $this->loadLastRootFolder($intRootId);
                if (count($objLastRootFolderData) > 0) {
                    $objLastRootFolder = $objLastRootFolderData->current();
                    $intFolderId = $objNestedSet->newNextSibling($objLastRootFolder->id, $arrData);
                } else {
                    $intFolderId = $objNestedSet->newRootNodeWithExistingRootId($intRootId, $arrData);
                }
            }

            return $intFolderId;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * deleteFolder
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @param integer $intElementId
     * @version 1.0
     */
    public function deleteFolder($intElementId)
    {
        $this->core->logger->debug('core->models->Folders->deleteFolder(' . $intElementId . ')');

        $this->getFolderTable();

        /**
         * delete folder with $intElementId
         */
        $strWhere = $this->objFolderTable->getAdapter()->quoteInto('id = ?', $intElementId);
        return $this->objFolderTable->delete($strWhere);

        //FIXME:: delete sub folder and content (pages, files)?
    }

    /**
     * deleteFolderNode
     * @param integer $intFolderId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function deleteFolderNode($intFolderId)
    {
        $this->core->logger->debug('core->models->Folders->deleteFolderNode(' . $intFolderId . ')');

        $this->getFolderTable();

        $objNestedSet = new NestedSet($this->objFolderTable);
        $objNestedSet->setDBFParent('idParentFolder');
        $objNestedSet->setDBFRoot('idRootLevels');

        $objNestedSet->deleteNode($intFolderId);

        //FIXME:: delete sub folder and content (pages, files, folders, ...) ???
    }

    /**
     * moveFolderToLastChildOf
     * @param integer $intFolderId
     * @param integer $intParentFolderId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function moveFolderToLastChildOf($intFolderId, $intParentFolderId)
    {
        $this->core->logger->debug('core->models->Folders->moveFolderToLastChildOf(' . $intFolderId . ',' . $intParentFolderId . ')');

        $this->getFolderTable();

        $objNestedSet = new NestedSet($this->objFolderTable);
        $objNestedSet->setDBFParent('idParentFolder');
        $objNestedSet->setDBFRoot('idRootLevels');

        $objNestedSet->moveToLastChild($intFolderId, $intParentFolderId);
    }

    /**
     * moveFolderToLastChildOfRootFolder
     * @param integer $intFolderId
     * @param integer $intRootFolderId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function moveFolderToLastChildOfRootFolder($intFolderId, $intRootFolderId)
    {
        $this->core->logger->debug('core->models->Folders->moveFolderToLastChildOfRootFolder(' . $intFolderId . ',' . $intRootFolderId . ')');

        $this->getFolderTable();

        $objNestedSet = new NestedSet($this->objFolderTable);
        $objNestedSet->setDBFParent('idParentFolder');
        $objNestedSet->setDBFRoot('idRootLevels');

        $objLastRootFolderData = $this->loadLastRootFolder($intRootFolderId);

        if (count($objLastRootFolderData) > 0) {
            $objLastRootFolder = $objLastRootFolderData->current();
            $objNestedSet->moveToNextSibling($intFolderId, $objLastRootFolder->id);
        }
    }

    /**
     * updateFolderSecurity
     * @param integer $intFolderId
     * @param integer $arrGroups
     * @param integer $intZone
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function updateFolderSecurity($intFolderId, $arrGroups, $intZone)
    {
        $this->core->logger->debug('core->models->Folders->updateFolderSecurity(' . $intFolderId . ', ' . $arrGroups . ', ' . $intZone . ')');

        try {
            $this->getFolderPermissionTable();

            /**
             * delete data
             */
            $strWhere = $this->objFolderPermissionTable->getAdapter()->quoteInto('idFolders = ?', $intFolderId);
            $strWhere .= $this->objFolderPermissionTable->getAdapter()->quoteInto('AND zone = ?', $intZone);
            $this->objFolderPermissionTable->delete($strWhere);

            if (count($arrGroups) > 0) {
                foreach ($arrGroups as $intGroupId) {
                    $arrData = array(
                        'idFolders'    => $intFolderId,
                        'zone'         => $intZone,
                        'idGroups'     => $intGroupId
                    );
                    $this->objFolderPermissionTable->insert($arrData);
                }

                //update flage
                if ($intZone == $this->core->sysConfig->zone->website) {
                    $this->getFolderTable()->update(array('isSecure' => 1), 'id =' . $intFolderId);
                }
            } else {
                //update flage
                if ($intZone == $this->core->sysConfig->zone->website) {
                    $this->getFolderTable()->update(array('isSecure' => 0), 'id =' . $intFolderId);
                }
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getFolderSecurity
     * @param integer $intFolderId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getFolderSecurity($intFolderId)
    {
        $this->core->logger->debug('core->models->Folders->getFolderSecurity(' . $intFolderId . ')');

        $objSelect = $this->getFolderPermissionTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objFolderPermissionTable, array('idGroups', 'zone'))
            ->joinInner('groups', 'groups.id = folderPermissions.idGroups', array('id', 'title', 'key'))
            ->where('idFolders = ?', $intFolderId);
        return $this->objFolderPermissionTable->fetchAll($objSelect);
    }

    /**
     * getFoldersPermissions
     * @param integer $intZoneId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getFoldersPermissions($intZoneId = null)
    {
        $this->core->logger->debug('core->models->Folders->getFoldersPermissions()');

        $objSelect = $this->getFolderPermissionTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objFolderPermissionTable, array('idGroups', 'zone'))
            ->joinInner('folders', 'folders.id = folderPermissions.idFolders', array('id'))
            ->joinInner('groups', 'groups.id = folderPermissions.idGroups', array('id AS groupId', 'title AS groupTitle', 'key AS groupKey'))
            ->joinInner('groupPermissions', 'groupPermissions.idGroups = groups.id', array('idLanguages AS languageId'))
            ->joinInner('permissions', 'permissions.id = groupPermissions.idPermissions', array('id AS permissionId', 'title AS permissionTitle'));

        if ($intZoneId != null) {
            $objSelect->where('zone = ?', $intZoneId);
        }

        return $this->objFolderPermissionTable->fetchAll($objSelect);
    }

    /**
     * getRootLevelsPermissions
     * @param integer $intModuleId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getRootLevelsPermissions($intModuleId, $intZoneId = null)
    {
        $this->core->logger->debug('core->models->Folders->getRootLevelsPermissions(' . $intModuleId . ')');

        $objSelect = $this->getRootLevelPermissionTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objRootLevelPermissionTable, array('idGroups', 'zone'))
            ->joinInner('rootLevels', 'rootLevels.id = rootLevelPermissions.idRootLevels', array('id'))
            ->joinInner('groups', 'groups.id = rootLevelPermissions.idGroups', array('id AS groupId', 'title AS groupTitle', 'key AS groupKey'))
            ->joinInner('groupPermissions', 'groupPermissions.idGroups = groups.id', array('idLanguages AS languageId'))
            ->joinInner('permissions', 'permissions.id = groupPermissions.idPermissions', array('id AS permissionId', 'title AS permissionTitle'))
            ->where('rootLevels.idModules = ?', $intModuleId);

        if ($intZoneId != null) {
            $objSelect->where('zone = ?', $intZoneId);
        }

        return $this->objRootLevelPermissionTable->fetchAll($objSelect);
    }

    /**
     * getRootLevelMainUrl
     * @param integer|array $intRootLevelId
     * @param integer $intEnvironment
     * @param boolean $blnForLanguage
     * @return string
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getRootLevelMainUrl($mixedRootLevelId, $intEnvironment = null, $blnForLanguage = false, $blnAsObject = false)
    {
        $this->core->logger->debug('core->models->Folders->getRootLevelMainUrl()');

        $objSelect = $this->getRootLevelUrlTable()->select();
        $objSelect->setIntegrityCheck(false);

        $strAppEnv = APPLICATION_ENV;
        $intEnvironment = ($intEnvironment == null) ? $this->core->sysConfig->environments->$strAppEnv : $intEnvironment;

        $objSelect->from($this->objRootLevelUrlTable, array('url', 'idRootLevels', 'path', 'hostPrefix', 'idLanguages AS defaultLanguage'))
            ->where('rootLevelUrls.idEnvironments = ?', $intEnvironment)
            ->where('rootLevelUrls.isMain = 1')
            ->limit(1);

        if (is_array($mixedRootLevelId)) {
            $objSelect->where('rootLevelUrls.idRootLevels IN (?)', new Zend_Db_Expr(implode(', ', $mixedRootLevelId)));
        } else {
            $objSelect->where('rootLevelUrls.idRootLevels = ?', $mixedRootLevelId);
        }

        if ($blnForLanguage) {
            $objSelect->joinInner('rootLevelLanguages', 'rootLevelLanguages.idRootLevels = rootLevelUrls.idRootLevels')
                ->where('rootLevelLanguages.idLanguages = ?', $this->getLanguageId());
        }

        $objData = $this->objRootLevelUrlTable->fetchAll($objSelect);
        $url = '';
        $arrShopRootLevelIds = (!empty($this->core->config->shop))? $this->core->config->shop->root_level_ids->id->toArray() : array();
        if(count($arrShopRootLevelIds) > 0 && $intEnvironment == $this->core->sysConfig->environments->production && in_array($objData->current()->idRootLevels, $arrShopRootLevelIds)) {
            $url =  'https://'.$objData->current()->url;
        }else if(count($objData) == 1){
            
            if($objData->current()->path != ''){
                $url =  'http://'.$objData->current()->url.'/'.$objData->current()->path;
            }else{
                $url =  'http://'.$objData->current()->url;
            }
        }
        if (count($objData) == 1) {
            if ($blnAsObject) {
                $objReturn = new stdClass(); 
                $objReturn->url = $url;
                $objReturn->hostPrefix = $objData->current()->hostPrefix;
                $objReturn->defaultLanguage = $objData->current()->defaultLanguage;
                return $objReturn;
            }
        }
        
        return $url;
    }

    /**
     * getFolderTable
     * @return Model_Table_Folders
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getFolderTable()
    {

        if ($this->objFolderTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/Folders.php';
            $this->objFolderTable = new Model_Table_Folders();
        }

        return $this->objFolderTable;
    }

    /**
     * getFolderPropertyTable
     * @return Model_Table_Folders
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getFolderPropertyTable()
    {

        if ($this->objFolderPropertyTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/FolderProperties.php';
            $this->objFolderPropertyTable = new Model_Table_FolderProperties();
        }

        return $this->objFolderPropertyTable;
    }

    /**
     * getFolderPermissionTable
     * @return Model_Table_FolderPermissions
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getFolderPermissionTable()
    {

        if ($this->objFolderPermissionTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/FolderPermissions.php';
            $this->objFolderPermissionTable = new Model_Table_FolderPermissions();
        }

        return $this->objFolderPermissionTable;
    }


    /**
     * getRootLevelTable
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getRootLevelTable()
    {

        if ($this->objRootLevelTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/RootLevels.php';
            $this->objRootLevelTable = new Model_Table_RootLevels();
        }

        return $this->objRootLevelTable;
    }

    /**
     * getRootLevelUrlTable
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getRootLevelUrlTable()
    {

        if ($this->objRootLevelUrlTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/RootLevelUrls.php';
            $this->objRootLevelUrlTable = new Model_Table_RootLevelUrls();
        }

        return $this->objRootLevelUrlTable;
    }

    /**
     * getRootLevelPermissionTable
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getRootLevelPermissionTable()
    {

        if ($this->objRootLevelPermissionTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/RootLevelPermissions.php';
            $this->objRootLevelPermissionTable = new Model_Table_RootLevelPermissions();
        }

        return $this->objRootLevelPermissionTable;
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
     * setContentLanguageId
     * @param integer $intContentLanguageId
     */
    public function setContentLanguageId($intContentLanguageId)
    {
        $this->intContentLanguageId = $intContentLanguageId;
    }

    /**
     * getContentLanguageId
     * @param integer $intContentLanguageId
     */
    public function getContentLanguageId()
    {
        return $this->intContentLanguageId;
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