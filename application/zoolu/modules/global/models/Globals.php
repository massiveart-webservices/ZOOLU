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
 * @package    application.zoolu.modules.globals.models
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Model_Globals
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-03-10: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Model_Globals extends ModelAbstract
{

    private $intLanguageId;
    private $intSegmentId;

    /**
     * @var Model_Folders
     */
    protected $objModelFolders;

    /**
     * @var Model_Table_Globals
     */
    protected $objGlobalTable;

    /**
     * @var Model_Table_Folders::
     */
    protected $objFolderTable;

    /**
     * @var Model_Table_GlobalProperties
     */
    protected $objGlobalPropertyTable;

    /**
     * @var Model_Table_Urls
     */
    protected $objGlobalUrlTable;

    /**
     * @var Model_Table_GlobalLinks
     */
    protected $objGlobalLinkTable;

    /**
     * @var Model_Table_GlobalInternalLinks
     */
    protected $objGlobalInternalLinkTable;

    /**
     * @var Model_Table_GlobalArticles
     */
    protected $objGlobalArticleTable;
    
    /**
     * @var Model_Table_GlobalVideos
     */
    protected $objGlobalVideoTable;

    /**
     * @var Model_Table_GlobalVideosTable
     */
    protected $objGlobalContactsTable;

    /**
     * @var Model_Globals
     */
    protected $objModelGlobals;

    /**
     * @var Model_Table_GlobalDates
     */
    protected $objGlobalDatetimesTable;

    /**
     * @var Core
     */
    protected $core;


    /**
     * Constructor
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * load
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset_Abstract Global
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function load($intElementId)
    {
        $this->core->logger->debug('global->models->Model_Globals->load(' . $intElementId . ')');

        $objSelect = $this->getGlobalTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('globals', array('id', 'globalId', 'relationId' => 'globalId', 'version', 'isStartGlobal', 'idParent', 'idParentTypes', 'globalProperties.idGenericForms', 'globalProperties.idTemplates', 'globalProperties.idGlobalTypes', 'globalProperties.showInNavigation', 'globalProperties.idLanguageFallbacks', 'globalProperties.published', 'globalProperties.changed', 'globalProperties.idStatus', 'globalProperties.creator', 'globalTitles.title'));
        $objSelect->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->joinLeft('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = '. $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->joinLeft(array('ub' => 'users'), 'ub.id = globalProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'));
        $objSelect->joinLeft(array('uc' => 'users'), 'uc.id = globalProperties.idUsers', array('changeUser' => 'CONCAT(uc.fname, \' \', uc.sname)'));
        $objSelect->where('globals.id = ?', $intElementId);

        return $this->getGlobalTable()->fetchAll($objSelect);
    }

    /**
     * loadByGlobalId
     * @param string $strGlobalId
     */
    public function loadByGlobalId($strGlobalId)
    {
        $this->core->logger->debug('global->models->Model_Globals->loadByGlobalId(' . $strGlobalId . ')');

        $objSelect = $this->getGlobalTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('globals', array('id', 'globalId', 'relationId' => 'globalId', 'version', 'isStartGlobal', 'idParent', 'idParentTypes', 'globalProperties.idGlobalTypes', 'globalProperties.showInNavigation', 'globalProperties.idLanguageFallbacks', 'globalProperties.published', 'globalProperties.changed', 'globalProperties.idStatus', 'globalProperties.creator'));
        $objSelect->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->joinLeft(array('ub' => 'users'), 'ub.id = globalProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'));
        $objSelect->joinLeft(array('uc' => 'users'), 'uc.id = globalProperties.idUsers', array('changeUser' => 'CONCAT(uc.fname, \' \', uc.sname)'));
        $objSelect->where('globals.globalId = ?', $strGlobalId);

        return $this->getGlobalTable()->fetchAll($objSelect);
    }

    /**
     * loadByGlobalId
     * @param string $strGlobalId
     */
    public function loadLinkByGlobalId($strGlobalId)
    {
        $this->core->logger->debug('global->models->Model_Globals->loadLinkByGlobalId(' . $strGlobalId . ')');

        $objSelect = $this->getGlobalTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from(array('origin' => 'globals'), array('globals.id', 'globals.globalId', 'relationId' => 'globals.globalId', 'globals.version', 'globals.isStartGlobal', 'globals.idParent', 'globals.idParentTypes', 'globalProperties.idGenericForms', 'globalProperties.idGlobalTypes', 'globalProperties.showInNavigation', 'globalProperties.idLanguageFallbacks', 'globalProperties.published', 'globalProperties.changed', 'globalProperties.idStatus', 'globalProperties.creator'));
        $objSelect->joinLeft('globalLinks', 'globalLinks.globalId = origin.globalId', array());
        $objSelect->joinLeft('globals', 'globalLinks.idGlobals = globals.id', array());
        $objSelect->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->joinLeft(array('ub' => 'users'), 'ub.id = globalProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'));
        $objSelect->joinLeft(array('uc' => 'users'), 'uc.id = globalProperties.idUsers', array('changeUser' => 'CONCAT(uc.fname, \' \', uc.sname)'));
        $objSelect->where('origin.globalId = ?', $strGlobalId);

        return $this->getGlobalTable()->fetchAll($objSelect);
    }

    /**
     * loadLinkGlobal
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadLinkGlobal($intElementId, $languageId = 0)
    {
        if ($languageId == 0) {
            $languageId = $this->intLanguageId;
        }

        $this->core->logger->debug('global->models->Model_Globals->loadLinkGlobal(' . $intElementId . ')');

        $objSelect = $this->getGlobalTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from(array('origin' => 'globals'), array('originId' => 'id'));
        $objSelect->join('globalLinks', 'globalLinks.idGlobals = origin.id', array());
        $objSelect->join('globals', 'globals.globalId = globalLinks.globalId', array('id', 'globalId', 'version', 'isStartGlobal', 'idParent'));
        $objSelect->join('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = ' . $languageId, array('title'));
        $objSelect->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $languageId, array('idGenericForms', 'idTemplates'));
        $objSelect->joinleft('urls', 'urls.relationId = globals.globalId AND urls.version = globals.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->global . ' AND urls.idLanguages = ' . $languageId . ' AND urls.isMain = 1 AND urls.idParent IS NULL', array('url'));
        $objSelect->joinleft('languages', 'languages.id = urls.idLanguages', array('languageCode'));
        $objSelect->where('origin.id = ?', $intElementId);

        return $this->getGlobalTable()->fetchAll($objSelect);
    }

    public function loadStartelementByParentId($intFolderId) {
        $this->core->logger->debug('global->models->Model_Globals->loadLinkGlobal('.$intFolderId.')');

        $objSelect = $this->getGlobalTable()->select()->setIntegrityCheck(false);

        $objSelect->from(array('origin' => 'globals'), array('originId' => 'id'));
        $objSelect->join('globalLinks', 'globalLinks.idGlobals = origin.id', array());
        $objSelect->join('globals', 'globals.globalId = globalLinks.globalId', array('id', 'globalId', 'relationId' => 'globalId', 'version', 'isStartGlobal', 'idParent', 'idParentTypes', 'globalProperties.idGenericForms', 'globalProperties.idTemplates', 'globalProperties.idGlobalTypes', 'globalProperties.showInNavigation', 'globalProperties.idLanguageFallbacks', 'globalProperties.published', 'globalProperties.changed', 'globalProperties.idStatus', 'globalProperties.creator', 'globalTitles.title'));
        $objSelect->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->joinLeft('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = '. $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->joinLeft(array('ub' => 'users'), 'ub.id = globalProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'));
        $objSelect->joinLeft(array('uc' => 'users'), 'uc.id = globalProperties.idUsers', array('changeUser' => 'CONCAT(uc.fname, \' \', uc.sname)'));
        $objSelect->where('origin.idParent = ?', $intFolderId);
        $objSelect->where('origin.isStartGlobal = ?', 1);

        return $this->getGlobalTable()->fetchAll($objSelect);
    }

    /**
     * loadProperties
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadProperties($intElementId, $intLanguageId = null)
    {
        $this->core->logger->debug('global->models->Model_Globals->loadProperties(' . $intElementId . ')');

        if ($intLanguageId == null) {
            $intLanguageId = $this->intLanguageId;
        }

        $objSelect = $this->getGlobalPropertyTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('globalProperties', array('templateId' => 'idTemplates'));
        $objSelect->join('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId', 'genericFormVersion' => 'version', 'genericFormType' => 'idGenericFormTypes'));
        $objSelect->join('globals', 'globals.globalId = globalProperties.globalId AND globals.version = globalProperties.version', array());
        $objSelect->where('globals.id = ?', $intElementId);
        $objSelect->where('globalProperties.idLanguages = ?', $intLanguageId);

        return $this->getGlobalTable()->fetchAll($objSelect);
    }

    /**
     * loadByIdAndVersion
     * @param string $strGlobalId
     * @param integer $intVersion
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadByIdAndVersion($strGlobalId, $intVersion)
    {
        $this->core->logger->debug('global->models->Model_Globals->loadByIdAndVersion(' . $strGlobalId . ', ' . $intVersion . ')');

        $objSelect = $this->getGlobalTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('globals', array('id', 'globalId', 'relationId' => 'globalId', 'version', 'isStartElement' => 'isStartGlobal', 'idParent', 'idParentTypes', 'globalProperties.idTemplates', 'globalProperties.idGlobalTypes', 'globalProperties.showInNavigation', 'globalProperties.idLanguageFallbacks', 'globalProperties.published', 'globalProperties.changed', 'globalProperties.created', 'globalProperties.idStatus'));
        $objSelect->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->joinLeft(array('ub' => 'users'), 'ub.id = globalProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'));
        $objSelect->joinLeft(array('uc' => 'users'), 'uc.id = globalProperties.idUsers', array('changeUser' => 'CONCAT(uc.fname, \' \', uc.sname)'));
        $objSelect->joinLeft(array('ucr' => 'users'), 'ucr.id = globalProperties.creator', array('creator' => 'CONCAT(ucr.fname, \' \', ucr.sname)'));
        $objSelect->join('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId', 'version', 'idGenericFormTypes'));
        $objSelect->join('templates', 'templates.id = globalProperties.idTemplates', array('filename', 'cacheLifetime', 'renderScript'));
        $objSelect->where('globals.globalId = ?', $strGlobalId)
            ->where('globals.version = ?', $intVersion);

        return $this->getGlobalTable()->fetchAll($objSelect);
    }

    /**
     * loadGlobalsByfilter
     * @param integer $intParentFolderId
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadGlobalsByFilter($intParentFolderId, $arrTagIds = array(), $intRootLevelGroupId = 0)
    {
        $this->core->logger->debug('global->models->Model_Globals->loadGlobalsByFilter(' . $intParentFolderId . ', ' . $arrTagIds . ', ' . $intRootLevelGroupId . ')');

        $strTagIds = '';
        if (count($arrTagIds) > 0) {
            $strTagIds = implode(',', $arrTagIds);
        }

        $objSelect = $this->getGlobalTable()->select()->setIntegrityCheck(false);

        if ($intRootLevelGroupId > 0 && $intRootLevelGroupId == $this->core->sysConfig->root_level_groups->product) {
            $objSelect->from('globals', array('id', 'globalId', 'isStartGlobal', 'linkId' => 'lP.id', 'linkGlobalId' => 'lP.globalId'))
                ->join('globalLinks', 'globalLinks.globalId = globals.globalId', array())
                ->join(array('lP' => 'globals'), 'lP.id = globalLinks.idGlobals', array())
                ->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.idLanguages = ' . $this->intLanguageId, array('idStatus'))
                ->joinLeft('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.idLanguages = ' . $this->intLanguageId, array('title'))
                ->joinLeft(array('alternativeTitle' => 'globalTitles'), 'alternativeTitle.globalId = globals.globalId AND alternativeTitle.idLanguages = ' . $this->intLanguageId, array('alternativeTitle' => 'title'))
                ->joinLeft(array('fallbackTitle' => 'globalTitles'), 'fallbackTitle.globalId = globals.globalId AND fallbackTitle.idLanguages = 0', array('fallbackTitle' => 'title'));
            if (trim($strTagIds, ',') != '') {
                $objSelect->join('tagGlobals', 'tagGlobals.globalId = pages.globalId AND tagGlobals.idTags IN (' . trim($strTagIds, ',') . ')', array());
            }
            $objSelect->where('lP.idParent = ?', $intParentFolderId)
                ->where('lP.idParentTypes = ?', $this->core->sysConfig->parent_types->folder)
                ->order('lP.sortPosition');
        } else {
            $objSelect->from('globals', array('id', 'globalId', 'isStartGlobal'))
                ->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.idLanguages = ' . $this->intLanguageId, array('idStatus'))
                ->joinLeft('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.idLanguages = ' . $this->intLanguageId, array('title'))
                ->joinLeft(array('alternativeTitle' => 'globalTitles'), 'alternativeTitle.globalId = globals.globalId AND alternativeTitle.idLanguages = ' . $this->intLanguageId, array('alternativeTitle' => 'title'))
                ->joinLeft(array('fallbackTitle' => 'globalTitles'), 'fallbackTitle.globalId = globals.globalId AND fallbackTitle.idLanguages = 0', array('fallbackTitle' => 'title'));
            if (trim($strTagIds, ',') != '') {
                $objSelect->join('tagGlobals', 'tagGlobals.globalId = pages.globalId AND tagGlobals.idTags IN (' . trim($strTagIds, ',') . ')', array());
            }
            $objSelect->where('globals.idParent = ?', $intParentFolderId)
                ->where('globals.idParentTypes = ?', $this->core->sysConfig->parent_types->folder)
                ->order('globals.sortPosition');
        }


        return $this->getGlobalTable()->fetchAll($objSelect);
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
        $this->core->logger->debug('global->models->Model_Globals->loadFormAndTemplateById(' . $intElementId . ')');

        $objSelect = $this->getGlobalTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('globals', array('globalProperties.idGenericForms', 'globalProperties.idTemplates', 'globalProperties.idGlobalTypes', 'globalProperties.showInNavigation', 'globalProperties.idLanguageFallbacks'));
        $objSelect->join('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
        $objSelect->join('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId'));
        $objSelect->where('globals.id = ?', $intElementId);

        return $this->getGlobalTable()->fetchAll($objSelect);
    }

    /**
     * loadByParentId
     * @param integer $intParentId
     * @param integer $intTypeId
     * @param boolean $blnOnlyStartGlobal
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadByParentId($intParentId, $intTypeId, $blnOnlyStartGlobal = false)
    {
        $this->core->logger->debug('global->models->Model_Globals->loadByParentId(' . $intParentId . ', ' . $intTypeId . ', ' . $blnOnlyStartGlobal . ')');

        $objSelect = $this->getGlobalTable()->select();
        $objSelect->setIntegrityCheck(false);

        if ($intTypeId == $this->core->sysConfig->page_types->product_tree->id) {
            $objSelect->from('globals', array('id', 'globalId', 'relationId' => 'globalId', 'linkId' => 'lP.id', 'version', 'isStartElement' => 'isStartGlobal', 'idParent', 'idParentTypes', 'globalProperties.idTemplates', 'globalProperties.idGlobalTypes', 'globalProperties.showInNavigation', 'globalProperties.idLanguageFallbacks', 'globalProperties.published', 'globalProperties.changed', 'globalProperties.created', 'globalProperties.idStatus'));
            $objSelect->join('globalLinks', 'globalLinks.globalId = globals.globalId', array());
            $objSelect->join(array('lP' => 'globals'), 'lP.id = globalLinks.idGlobals', array());
            $objSelect->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
            $objSelect->joinLeft(array('ub' => 'users'), 'ub.id = globalProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'));
            $objSelect->joinLeft(array('uc' => 'users'), 'uc.id = globalProperties.idUsers', array('changeUser' => 'CONCAT(uc.fname, \' \', uc.sname)'));
            $objSelect->joinLeft(array('ucr' => 'users'), 'ucr.id = globalProperties.creator', array('creator' => 'CONCAT(ucr.fname, \' \', ucr.sname)'));
            $objSelect->join('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId', 'version', 'idGenericFormTypes'));
            $objSelect->join('templates', 'templates.id = globalProperties.idTemplates', array('filename', 'cacheLifetime', 'renderScript'));
            $objSelect->where('lP.idParent = ?', $intParentId)
                ->where('lP.idParentTypes = ?', $this->core->sysConfig->parent_types->folder);

            if ($blnOnlyStartGlobal == true) {
                $objSelect->where('lP.isStartGlobal = 1');
            }
        } else {
            $objSelect->from('globals', array('id', 'globalId', 'relationId' => 'globalId', 'linkId' => new Zend_Db_Expr('-1'), 'version', 'isStartElement' => 'isStartGlobal', 'idParent', 'idParentTypes', 'globalProperties.idTemplates', 'globalProperties.idGlobalTypes', 'globalProperties.showInNavigation', 'globalProperties.idLanguageFallbacks', 'globalProperties.published', 'globalProperties.changed', 'globalProperties.created', 'globalProperties.idStatus'));
            $objSelect->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
            $objSelect->joinLeft(array('ub' => 'users'), 'ub.id = globalProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'));
            $objSelect->joinLeft(array('uc' => 'users'), 'uc.id = globalProperties.idUsers', array('changeUser' => 'CONCAT(uc.fname, \' \', uc.sname)'));
            $objSelect->joinLeft(array('ucr' => 'users'), 'ucr.id = globalProperties.creator', array('creator' => 'CONCAT(ucr.fname, \' \', ucr.sname)'));
            $objSelect->join('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId', 'version', 'idGenericFormTypes'));
            $objSelect->join('templates', 'templates.id = globalProperties.idTemplates', array('filename', 'cacheLifetime', 'renderScript'));
            $objSelect->where('idParent = ?', $intParentId)
                ->where('idParentTypes = ?', $this->core->sysConfig->parent_types->folder);

            if ($blnOnlyStartGlobal == true) {
                $objSelect->where('isStartGlobal = 1');
            }
        }

        return $this->getGlobalTable()->fetchAll($objSelect);
    }

    /**
     * loadFolderContentById
     * @param number $intFolderId
     * @param string $strSearchValue
     * @param string $strOrderColumn
     * @param string $strOrderSort
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadFolderContentById($intFolderId, $strSearch = '', $strOrderColumn = '', $strOrderSort = '', $strRootLevelGroupKey = '')
    {
        $this->core->logger->debug('global->models->Model_Globals->loadFolderContentById(' . $intFolderId . ',' . $strSearch . ',' . $strOrderColumn . ',' . $strOrderSort . ')');

        if ($strRootLevelGroupKey == 'product') { //FIXME: don't use hardcode
            //contains also globalLinks
            $objGlobalSelect = $this->getGlobalTable()->select();
            $objGlobalSelect->setIntegrityCheck(false);
            $objGlobalSelect->from('globals', array());
            $objGlobalSelect->joinLeft('globalLinks', 'globals.id = globalLinks.idGlobals', array('linkId' => 'globalLinks.idGlobals'));
            $objGlobalSelect->joinLeft(array('globalParent' => 'globals'), 'globalLinks.globalId = globalParent.globalId', array('id', 'isStartGlobal', 'idUsers', 'globals.sortPosition', 'changed', 'elementType' => new Zend_Db_Expr('"global"')));
            $objGlobalSelect->joinLeft('globalTitles',
                $objGlobalSelect->getAdapter()->quoteInto('globalTitles.globalId = globalParent.globalId AND globalTitles.idLanguages = ?', $this->intLanguageId),
                array());
            $objGlobalSelect->joinLeft(array('gGuiTitles' => 'globalTitles'),
                $objGlobalSelect->getAdapter()->quoteInto('gGuiTitles.globalId = globalParent.globalId AND gGuiTitles.idLanguages = ?', 0),
                array('title' => new Zend_Db_Expr('IF(globalTitles.title IS NULL, gGuiTitles.title, globalTitles.title)')));
            $objGlobalSelect->joinLeft('globalProperties',
                $objGlobalSelect->getAdapter()->quoteInto('globalProperties.globalId = globalParent.globalId AND globalProperties.version = globalParent.version AND globalProperties.idLanguages = ?', $this->intLanguageId),
                array('idStatus', 'version', 'idTemplates'));
            $objGlobalSelect->joinLeft('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId'));
            $objGlobalSelect->joinLeft('users', 'users.id = globals.idUsers', array('author' => 'CONCAT(users.fname, \' \', users.sname)'));
            $objGlobalSelect->where('globals.idParentTypes = ?', $this->core->sysConfig->parent_types->folder);
            if ($strSearch != '') {
                $objGlobalSelect->where('globalTitles.title LIKE ? OR gGuiTitles.title LIKE ?', '%' . $strSearch . '%');
            }
            $objGlobalSelect->where('globals.idParent = ?', $intFolderId);
        } else {
            //Without globalLinks
            $objGlobalSelect = $this->getGlobalTable()->select();
            $objGlobalSelect->setIntegrityCheck(false);
            $objGlobalSelect->from('globals', array('id', 'linkId' => new Zend_Db_Expr('-1'), 'isStartGlobal', 'idUsers', 'sortPosition', 'changed', 'elementType' => new Zend_Db_Expr('"global"')));
            $objGlobalSelect->joinLeft('globalTitles',
                $objGlobalSelect->getAdapter()->quoteInto('globalTitles.globalId = globals.globalId AND globalTitles.idLanguages = ?', $this->intLanguageId),
                array());
            $objGlobalSelect->joinLeft(array('gGuiTitles' => 'globalTitles'),
                $objGlobalSelect->getAdapter()->quoteInto('gGuiTitles.globalId = globals.globalId AND gGuiTitles.idLanguages = ?', 0),
                array('title' => new Zend_Db_Expr('IF(globalTitles.title IS NULL, gGuiTitles.title, globalTitles.title)')));
            $objGlobalSelect->joinLeft('globalProperties',
                $objGlobalSelect->getAdapter()->quoteInto('globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ?', $this->intLanguageId),
                array('idStatus', 'version', 'idTemplates'));
            $objGlobalSelect->joinLeft('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId'));
            $objGlobalSelect->joinLeft('users', 'users.id = globals.idUsers', array('author' => 'CONCAT(users.fname, \' \', users.sname)'));
            $objGlobalSelect->where('globals.idParentTypes = ?', $this->core->sysConfig->parent_types->folder);
            if ($strSearch != '') {
                $objGlobalSelect->where('globalTitles.title LIKE ? OR gGuiTitles.title LIKE ?', '%' . $strSearch . '%');
            }
            $objGlobalSelect->where('globals.idParent = ?', $intFolderId);
        }

        //Folders
        $objFolderSelect = $this->getFolderTable()->select();
        $objFolderSelect->setIntegrityCheck(false);
        $objFolderSelect->from('folders', array('linkId' => new Zend_Db_Expr('-1'), 'id', 'isStartGlobal' => new Zend_Db_Expr(-1), 'idUsers', 'sortPosition', 'changed', 'elementType' => new Zend_Db_Expr('"folder"')));
        $objFolderSelect->joinLeft('folderTitles',
            $objFolderSelect->getAdapter()->quoteInto('folderTitles.folderId = folders.folderId AND folderTitles.idLanguages = ?', $this->intLanguageId), array());
        $objFolderSelect->joinLeft(array('fGuiTitles' => 'folderTitles'),
            $objFolderSelect->getAdapter()->quoteInto('fGuiTitles.folderId = folders.folderId AND fGuiTitles.idLanguages = ?', 0),
            array('title' => new Zend_Db_Expr('IF(folderTitles.title IS NULL, fGuiTitles.title, folderTitles.title)')));
        $objFolderSelect->joinLeft('folderProperties',
            $objFolderSelect->getAdapter()->quoteInto('folderProperties.folderId = folders.folderId AND folderProperties.version = folders.version AND folderProperties.idLanguages = ?', $this->intLanguageId),
            array('idStatus', 'version', 'idTemplates' => new Zend_Db_Expr(-1)));
        $objFolderSelect->joinLeft('genericForms', 'genericForms.id = folderProperties.idGenericForms', array('genericFormId'));
        $objFolderSelect->joinLeft('users', 'users.id = folders.idUsers', array('author' => 'CONCAT(users.fname, \' \', users.sname)'));
        if ($strSearch != '') {
            $objFolderSelect->where('folderTitles.title LIKE ? OR fGuiTitles.title LIKE ?', '%' . $strSearch . '%');
        }
        $objFolderSelect->where('folders.idParentFolder = ?', $intFolderId);

        //Union
        $objSelect = $this->getFolderTable()->select()->union(array($objGlobalSelect, $objFolderSelect), Zend_Db_Select::SQL_UNION_ALL);

        if ($strOrderColumn != '') {
            $objSelect->order(array($strOrderColumn . ' ' . $strOrderSort, 'isStartGlobal DESC'));
        } else {
            $objSelect->order(array('sortPosition ASC', 'isStartGlobal DESC'));
        }
        return $objSelect;
    }

    /**
     * loadAllPublicGlobals
     * @param integer $intRootLevelId
     * @param integer $intLanguageId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadAllPublicGlobals($intRootLevelId = null, $intLanguageId = null)
    {
        $this->core->logger->debug('global->models->Model_Globals->loadAllPublicGlobals()');

        $objSelect1 = $this->getGlobalUrlTable()->select()->distinct();
        $objSelect1->setIntegrityCheck(false);

        $objSelect1->from($this->objGlobalUrlTable, array('globals.globalId', 'idLink' => 'lG.id', 'version', 'idLanguages'));
        $objSelect1->join(array('lG' => 'globals'), 'lG.globalId = urls.relationId AND lG.version = urls.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->global, array('idParent'));
        $objSelect1->joinleft('folders', 'lG.idParent = folders.id AND lG.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array('idRootLevels'));
        $objSelect1->join('globalLinks', 'globalLinks.idGlobals = lG.id', array());
        $objSelect1->join('globals', 'globals.id = (SELECT g.id FROM globals AS g WHERE g.globalId = globalLinks.globalId ORDER BY g.version DESC LIMIT 1)', array());
        $objSelect1->join('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version', array());
        $objSelect1->where('globalProperties.idStatus = ?', $this->core->sysConfig->status->live);


        $objSelect2 = $this->getGlobalUrlTable()->select()->distinct();
        $objSelect2->setIntegrityCheck(false);

        $objSelect2->from($this->objGlobalUrlTable, array('globals.globalId', 'idLink' => new Zend_Db_Expr('-1'), 'version', 'idLanguages'));
        $objSelect2->join('globals', 'globals.globalId = urls.relationId AND globals.version = urls.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->global, array('idParent'));
        $objSelect2->joinleft('folders', 'globals.idParent = folders.id AND globals.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array('idRootLevels'));
        $objSelect2->join('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version', array());
        $objSelect2->where('globalProperties.idStatus = ?', $this->core->sysConfig->status->live);


        if ($intRootLevelId != null) {
            $objSelect1->where('(folders.idRootLevels = ?', $intRootLevelId);
            $objSelect1->orWhere('lG.idParentTypes = ' . $this->core->sysConfig->parent_types->rootlevel . ' AND lG.idParent = ?)', $intRootLevelId);
            $objSelect2->where('(folders.idRootLevels = ?', $intRootLevelId);
            $objSelect2->orWhere('globals.idParentTypes = ' . $this->core->sysConfig->parent_types->rootlevel . ' AND globals.idParent = ?)', $intRootLevelId);
        }

        if ($intLanguageId != null) {
            $objSelect1->where('globalProperties.idLanguages = ?', $intLanguageId);
            $objSelect1->where('urls.idLanguages = ?', $intLanguageId);
            $objSelect2->where('globalProperties.idLanguages = ?', $intLanguageId);
            $objSelect2->where('urls.idLanguages = ?', $intLanguageId);
        }

        $objSelect = $this->getGlobalTable()->select()
            ->distinct()
            ->union(array($objSelect1, $objSelect2));

        $this->core->logger->debug($objSelect);
        return $this->objGlobalUrlTable->fetchAll($objSelect);
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
     * @param array $arrGlobalIds
     * @param boolean $blnOnlyItems load only items, no start items
     * @param boolean $blnOnlyShowInNavigation load only items with property "showInNavigation"
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
  public function loadItems($mixedType, $intParentId, $intCategoryId = 0, $intLabelId = 0, $intEntryNumber = 0, $intSortTypeId = 0, $intSortOrderId = 0, $intEntryDepthId = 0, $arrGlobalIds = array(), $blnOnlyItems = false, $blnOnlyShowInNavigation = false){
    $this->core->logger->debug('cms->models->Model_Globals->loadItems('.$intParentId.','.$intCategoryId.','.$intLabelId.','.$intEntryNumber.','.$intSortTypeId.','.$intSortOrderId.','.$intEntryDepthId.','.$arrGlobalIds.')');

    if(!is_array($mixedType)){
      $mixedType = array('id' => $mixedType);
    }
    
    $intTypeId = (array_key_exists('id', $mixedType)) ? $mixedType['id'] : -1;
    $strType = (array_key_exists('key', $mixedType)) ? $mixedType['key'].'_types' : 'page_types';
    
    $strSortOrder = '';
    if($intSortOrderId > 0 && $intSortOrderId != ''){
      switch($intSortOrderId){
        case $this->core->sysConfig->sort->orders->asc->id:
          $strSortOrder = ' ASC';
          break;
        case $this->core->sysConfig->sort->orders->desc->id:
          $strSortOrder = ' DESC';
          break;
      }
    }    

    $objSelect1 = $this->core->dbh->select();
    
    if((isset($this->core->sysConfig->$strType->product_tree) && $intTypeId == $this->core->sysConfig->$strType->product_tree->id) || (isset($this->core->sysConfig->$strType->product_overview) && $intTypeId == $this->core->sysConfig->$strType->product_overview->id)){
      $objSelect1->from('globals', array('id', 'globalId', 'relationId' => 'globalId', 'plId' => 'lP.id', 'isStartElement' => 'isStartGlobal', 'idParent', 'idParentTypes', 'sortPosition' => 'folders.sortPosition', 'sortTimestamp' => 'folders.sortTimestamp', 'globalProperties.idGlobalTypes', 'globalProperties.idLanguageFallbacks', 'globalProperties.published', 'globalProperties.changed', 'globalProperties.created', 'globalProperties.idStatus'))
                 ->join('globalLinks', 'globalLinks.globalId = globals.globalId', array())
                 ->join(array('lP' => 'globals'), 'lP.id = globalLinks.idGlobals', array('plParentId' => 'idParent'))
                 ->join('folders', 'folders.id = lP.idParent AND lP.idParentTypes = '.$this->core->sysConfig->parent_types->folder, array())
                 ->join('folders AS parent', 'parent.id = '.$intParentId, array())        
                 ->join('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array())
                 ->join('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId', 'version', 'idGenericFormTypes'))
                 ->joinLeft(array('ub' => 'users'), 'ub.id = globalProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'))
                 ->join('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('title'))
                 ->joinLeft(array('fallbackTitles' => 'globalTitles'), 'fallbackTitles.globalId = globals.globalId AND fallbackTitles.version = globals.version AND fallbackTitles.idLanguages = globalProperties.idLanguageFallbacks', array('fallbackTitle' => 'title'))
                 ->joinLeft(array('fallbackProperties' => 'globalProperties'), 'fallbackProperties.globalId = globals.globalId AND fallbackProperties.version = globals.version AND fallbackProperties.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array())
                 ->joinLeft(array('fallbackGenericForms' => 'genericForms'), 'fallbackGenericForms.id = fallbackProperties.idGenericForms', array('fallbackGenericFormId' => 'genericFormId', 'fallbackGenericFormVersion' => 'version', 'fallbackGenericFormTypeId' => 'idGenericFormTypes'))
                 ->join('urls', 'urls.relationId = lP.globalId AND urls.version = lP.version AND urls.idUrlTypes = '.$this->core->sysConfig->url_types->global.' AND urls.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE).' AND urls.idParent IS NULL AND urls.isMain = 1', array('url'))
                 ->joinLeft('languages', 'languages.id = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('languageCode'))
                 ->where('folders.lft BETWEEN parent.lft AND parent.rgt')
                 ->where('folders.idRootLevels = parent.idRootLevels');
                 
      if($blnOnlyItems === true){
        $objSelect1->where('lP.isStartGlobal = 0');
      }
      
      switch($intEntryDepthId){
        case $this->core->sysConfig->filter->depth->all:
          $objSelect1->where('folders.depth > parent.depth');
          break;
        case $this->core->sysConfig->filter->depth->first:
        default:
          $objSelect1->where('lP.isStartGlobal = 1')
                     ->where('folders.depth = (parent.depth + 1)');        
          break;
      }
      
      
      $objSelect2 = $this->core->dbh->select();
      $objSelect2->from('globals', array('id', 'globalId', 'relationId' => 'globalId', 'plId' => 'lP.id', 'isStartElement' => 'isStartGlobal', 'idParent', 'idParentTypes', 'sortPosition' => 'lP.sortPosition', 'sortTimestamp' => 'lP.sortTimestamp', 'globalProperties.idGlobalTypes', 'globalProperties.idLanguageFallbacks', 'globalProperties.published', 'globalProperties.changed', 'globalProperties.created', 'globalProperties.idStatus'))
                 ->join('globalLinks', 'globalLinks.globalId = globals.globalId', array())
                 ->join(array('lP' => 'globals'), 'lP.id = globalLinks.idGlobals', array('plParentId' => 'idParent'))
                 ->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array())
                 ->join('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId', 'version', 'idGenericFormTypes'))
                 ->joinLeft(array('ub' => 'users'), 'ub.id = globalProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'))
                 ->join('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('title'))
                 ->joinLeft(array('fallbackTitles' => 'globalTitles'), 'fallbackTitles.globalId = globals.globalId AND fallbackTitles.version = globals.version AND fallbackTitles.idLanguages = globalProperties.idLanguageFallbacks', array('fallbackTitle' => 'title'))
                 ->joinLeft(array('fallbackProperties' => 'globalProperties'), 'fallbackProperties.globalId = globals.globalId AND fallbackProperties.version = globals.version AND fallbackProperties.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array())
                 ->joinLeft(array('fallbackGenericForms' => 'genericForms'), 'fallbackGenericForms.id = fallbackProperties.idGenericForms', array('fallbackGenericFormId' => 'genericFormId', 'fallbackGenericFormVersion' => 'version', 'fallbackGenericFormTypeId' => 'idGenericFormTypes'))
                 ->join('urls', 'urls.relationId = lP.globalId AND urls.version = lP.version AND urls.idUrlTypes = '.$this->core->sysConfig->url_types->global.' AND urls.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE).' AND urls.idParent IS NULL AND urls.isMain = 1', array('url'))
                 ->joinLeft('languages', 'languages.id = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('languageCode'))
                 ->where('lP.idParent = ?', $intParentId)
                 ->where('lP.isStartGlobal = 0')
                 ->where('lP.idParentTypes = ?', $this->core->sysConfig->parent_types->folder);
      
      if($blnOnlyShowInNavigation === true){
        $objSelect1->where('globalProperties.showInNavigation = 1');
        $objSelect2->where('globalProperties.showInNavigation = 1');
      }
    }else{
      $objSelect1->from('globals', array('id', 'globalId', 'relationId' => 'globalId', 'plId' => new Zend_Db_Expr('-1'), 'isStartElement' => 'isStartGlobal', 'idParent', 'idParentTypes', 'sortPosition' => 'folders.sortPosition', 'sortTimestamp' => 'folders.sortTimestamp', 'globalProperties.idGlobalTypes', 'globalProperties.idLanguageFallbacks', 'globalProperties.published', 'globalProperties.changed', 'globalProperties.created', 'globalProperties.idStatus'))
                 ->join('folders', 'folders.id = globals.idParent AND globals.idParentTypes = '.$this->core->sysConfig->parent_types->folder, array())
                 ->join('folders AS parent', 'parent.id = '.$intParentId, array())        
                 ->join('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array())
                 ->join('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId', 'version', 'idGenericFormTypes'))
                 ->joinLeft(array('ub' => 'users'), 'ub.id = globalProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'))
                 ->join('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('title'))
                 ->joinLeft(array('fallbackTitles' => 'globalTitles'), 'fallbackTitles.globalId = globals.globalId AND fallbackTitles.version = globals.version AND fallbackTitles.idLanguages = globalProperties.idLanguageFallbacks', array('fallbackTitle' => 'title'))
                 ->joinLeft(array('fallbackProperties' => 'globalProperties'), 'fallbackProperties.globalId = globals.globalId AND fallbackProperties.version = globals.version AND fallbackProperties.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array())
                 ->joinLeft(array('fallbackGenericForms' => 'genericForms'), 'fallbackGenericForms.id = fallbackProperties.idGenericForms', array('fallbackGenericFormId' => 'genericFormId', 'fallbackGenericFormVersion' => 'version', 'fallbackGenericFormTypeId' => 'idGenericFormTypes'))
                 ->join('urls', 'urls.relationId = globals.globalId AND urls.version = globals.version AND urls.idUrlTypes = '.$this->core->sysConfig->url_types->global.' AND urls.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE).' AND urls.idParent IS NULL AND urls.isMain = 1', array('url'))
                 ->joinLeft('languages', 'languages.id = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('languageCode'))
                 ->where('folders.lft BETWEEN parent.lft AND parent.rgt')
                 ->where('folders.idRootLevels = parent.idRootLevels');
                 
      if($blnOnlyItems === true){
        $objSelect1->where('globals.isStartGlobal = 0');
      }
      
      switch($intEntryDepthId){
        case $this->core->sysConfig->filter->depth->all:
          $objSelect1->where('folders.depth > parent.depth');
          break;
        case $this->core->sysConfig->filter->depth->first:
        default:
          $objSelect1->where('globals.isStartGlobal = 1')
                     ->where('folders.depth = (parent.depth + 1)');        
          break;
      }
            
      $objSelect2 = $this->core->dbh->select();
      $objSelect2->from('globals', array('id', 'globalId', 'relationId' => 'globalId', 'plId' => new Zend_Db_Expr('-1'), 'isStartElement' => 'isStartGlobal', 'idParent', 'idParentTypes', 'sortPosition' => 'globals.sortPosition', 'sortTimestamp' => 'globals.sortTimestamp', 'globalProperties.idGlobalTypes', 'globalProperties.idLanguageFallbacks', 'globalProperties.published', 'globalProperties.changed', 'globalProperties.created', 'globalProperties.idStatus'))
                 ->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array())
                 ->join('genericForms', 'genericForms.id = globalProperties.idGenericForms', array('genericFormId', 'version', 'idGenericFormTypes'))
                 ->joinLeft(array('ub' => 'users'), 'ub.id = globalProperties.publisher', array('publisher' => 'CONCAT(ub.fname, \' \', ub.sname)'))
                 ->join('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('title'))
                 ->joinLeft(array('fallbackTitles' => 'globalTitles'), 'fallbackTitles.globalId = globals.globalId AND fallbackTitles.version = globals.version AND fallbackTitles.idLanguages = globalProperties.idLanguageFallbacks', array('fallbackTitle' => 'title'))
                 ->joinLeft(array('fallbackProperties' => 'globalProperties'), 'fallbackProperties.globalId = globals.globalId AND fallbackProperties.version = globals.version AND fallbackProperties.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array())
                 ->joinLeft(array('fallbackGenericForms' => 'genericForms'), 'fallbackGenericForms.id = fallbackProperties.idGenericForms', array('fallbackGenericFormId' => 'genericFormId', 'fallbackGenericFormVersion' => 'version', 'fallbackGenericFormTypeId' => 'idGenericFormTypes'))
                 ->join('urls', 'urls.relationId = globals.globalId AND urls.version = globals.version AND urls.idUrlTypes = '.$this->core->sysConfig->url_types->global.' AND urls.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE).' AND urls.idParent IS NULL AND urls.isMain = 1', array('url'))
                 ->joinLeft('languages', 'languages.id = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('languageCode'))
                 ->where('globals.idParent = ?', $intParentId)
                 ->where('globals.isStartGlobal = 0')
                 ->where('globals.idParentTypes = ?', $this->core->sysConfig->parent_types->folder);
      
      if($blnOnlyShowInNavigation === true){
        $objSelect1->where('globalProperties.showInNavigation = 1');
        $objSelect2->where('globalProperties.showInNavigation = 1');
      }
    }
    

    if(count($arrGlobalIds) > 0){
      $objSelect1->where('globals.id NOT IN ('.implode(',', $arrGlobalIds).')');
      $objSelect2->where('globals.id NOT IN ('.implode(',', $arrGlobalIds).')');
    }

    if($intCategoryId > 0 && $intCategoryId != ''){
      $objSelect1->join('globalCategories', 'globalCategories.globalId = globals.globalId AND globalCategories.version = globals.version AND globalCategories.idLanguages = globalProperties.idLanguages', array())
                 ->where('globalCategories.category = ?', $intCategoryId);
      $objSelect2->join('globalCategories', 'globalCategories.globalId = globals.globalId AND globalCategories.version = globals.version AND globalCategories.idLanguages = globalProperties.idLanguages', array())
                 ->where('globalCategories.category = ?', $intCategoryId);
    }

    if($intLabelId > 0 && $intLabelId != ''){
      $objSelect1->joinLeft('globalLabels', 'globalLabels.globalId = globals.globalId AND globalLabels.version = globals.version AND globalLabels.idLanguages = globalProperties.idLanguages', array())
                 ->where('globalLabels.label = ?', $intLabelId);
      $objSelect2->joinLeft('globalLabels', 'globalLabels.globalId = globals.globalId AND globalLabels.version = globals.version AND globalLabels.idLanguages = globalProperties.idLanguages', array())
                 ->where('globalLabels.label = ?', $intLabelId);
    }
    
    if(!isset($_SESSION['sesTestMode']) || (isset($_SESSION['sesTestMode']) && $_SESSION['sesTestMode'] == false)){
      $objSelect1->where('globalProperties.idStatus = ?', $this->core->sysConfig->status->live)
                 ->where('globalProperties.published <= ?', date('Y-m-d H:i:s'));
      $objSelect2->where('globalProperties.idStatus = ?', $this->core->sysConfig->status->live)
                 ->where('globalProperties.published <= ?', date('Y-m-d H:i:s'));
    }

    $objSelect = $this->getGlobalTable()->select()
                                         ->distinct()
                                         ->union(array($objSelect1, $objSelect2));
                        
    if($intSortTypeId > 0 && $intSortTypeId != ''){
      switch($intSortTypeId){
        case $this->core->sysConfig->sort->types->manual_sort->id:
          $objSelect->order(array('sortPosition'.$strSortOrder, 'sortTimestamp'.(($strSortOrder == 'DESC') ? ' ASC' : ' DESC')));
          break;
        case $this->core->sysConfig->sort->types->created->id:
          $objSelect->order(array('created'.$strSortOrder));
          break;
        case $this->core->sysConfig->sort->types->changed->id:
          $objSelect->order(array('changed'.$strSortOrder));
          break;
        case $this->core->sysConfig->sort->types->published->id:
          $objSelect->order(array('published'.$strSortOrder));
          break;
        case $this->core->sysConfig->sort->types->alpha->id:
          $objSelect->order(array('title'.$strSortOrder)); 
          break;
      }
    }
    
    if($intEntryNumber > 0 && $intEntryNumber != ''){
      $objSelect->limit($intEntryNumber);
    }
    
    return $this->getGlobalTable()->fetchAll($objSelect);
  }


    /**
     * changeParentFolderId
     * @param integer $intGlobalId
     * @param integer $intParentFolderId
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function changeParentFolderId($intGlobalId, $intParentFolderId)
    {
        $this->core->logger->debug('global->models->Model_Globals->changeParentFolderId(' . $intGlobalId . ',' . $intParentFolderId . ')');
        try {
            $strWhere = $this->getGlobalTable()->getAdapter()->quoteInto('id = ?', $intGlobalId);
            $this->getGlobalTable()->update(array('idParent' => $intParentFolderId, 'idParentTypes' => $this->core->sysConfig->parent_types->folder), $strWhere);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * changeParentRootFolderId
     * @param integer $intGlobalId
     * @param integer $intRootFolderId
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function changeParentRootFolderId($intGlobalId, $intRootFolderId)
    {
        $this->core->logger->debug('global->models->Model_Globals->changeParentRootFolderId(' . $intGlobalId . ',' . $intRootFolderId . ')');
        try {
            $strWhere = $this->getGlobalTable()->getAdapter()->quoteInto('id = ?', $intGlobalId);
            $this->getGlobalTable()->update(array('idParent' => $intRootFolderId, 'idParentTypes' => $this->core->sysConfig->parent_types->rootlevel), $strWhere);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * loadItemInstanceDataByIds
     * @param string $strGenForm
     * @param array $arrGlobalIds
     * @param integer $intImgFilterTag
     * @param string $strImgFieldIds
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadItemInstanceDataByIds($strGenForm, $arrGlobalIds, $intImgFilterTag = 0, $strImgFieldIds = '5,55')
    {
        $this->core->logger->debug('global->models->Model_Globals->loadItemInstanceDataByIds(' . $strGenForm . ', ' . $arrGlobalIds . ')');

        // FIXME : !!! CHANGE INSTANCE FIELDS DEFINTION
        // FIXME : !!! iFl.idFields IN (5,55) -> define
        if ($strGenForm != '' && $strGenForm != '-' && strpos($strGenForm, $this->core->sysConfig->global_types->product_link->default_formId) === false) {

            $strSqlAddon = '';
            $strSqlInstanceFields = '';
            if (strpos($strGenForm, $this->core->sysConfig->form->ids->press->default) !== false) {
                $strSqlInstanceFields = ' `global-' . $strGenForm . '-Instances`.shortdescription,
                                  `global-' . $strGenForm . '-Instances`.description,
                                  `globalDatetimes`.datetime,';
            } elseif (strpos($strGenForm, $this->core->sysConfig->form->ids->event->default) !== false) {
                $strSqlInstanceFields = ' `global-' . $strGenForm . '-Instances`.shortdescription,
                                  `global-' . $strGenForm . '-Instances`.description,
                                  `global-' . $strGenForm . '-Instances`.start_datetime,
                                  `global-' . $strGenForm . '-Instances`.end_datetime,
                                   globalExternals.external,
                                   categoryTitles.title AS category,
                                   categoryTitles.idCategories AS categoryId,';
                $strSqlAddon .= '
                                          LEFT JOIN globalExternals ON
                                            globalExternals.globalId = globals.globalId AND
                                            globalExternals.version = globals.version AND
                                            globalExternals.idLanguages = ' . $this->intLanguageId . '
                                          LEFT JOIN globalCategories ON
                                            globalCategories.globalId = globals.globalId AND
                                            globalCategories.version = globals.version AND
                                            globalCategories.idLanguages = ' . $this->intLanguageId . '
                                          LEFT JOIN categoryTitles ON
                                            categoryTitles.idCategories = globalCategories.category AND
                                            categoryTitles.idLanguages = globalCategories.idLanguages';
            } elseif (strpos($strGenForm, $this->core->sysConfig->form->ids->product->default) !== false) {
                $strSqlInstanceFields = ' `global-' . $strGenForm . '-Instances`.shortdescription,
                                  `global-' . $strGenForm . '-Instances`.description,
                                  `global-' . $strGenForm . '-Instances`.slogan,';
            } elseif (strpos($strGenForm, $this->core->sysConfig->form->ids->course->default) !== false) {
                $strSqlInstanceFields = ' `global-' . $strGenForm . '-Instances`.shortdescription,
                                  `global-' . $strGenForm . '-Instances`.description,
                                  `global-' . $strGenForm . '-Region56-Instances`.sortPosition AS courseId,
                                  `global-' . $strGenForm . '-Region56-Instances`.event_title AS courseTitle,
                                  `global-' . $strGenForm . '-Region56-Instances`.start_datetime,
                                   locations.name AS location,
                                   CONCAT(contacts.fname, \' \', contacts.sname) AS speaker,
                                   contacts.id AS speakerId,
                                   categoryTitles.title AS category,
                                   categoryTitles.idCategories AS categoryId,';
                $strSqlAddon .= '
                                          LEFT JOIN `global-' . $strGenForm . '-Region56-Instances` ON
                                            `global-' . $strGenForm . '-Region56-Instances`.globalId = globals.globalId AND
                                            `global-' . $strGenForm . '-Region56-Instances`.version = globals.version AND
                                            `global-' . $strGenForm . '-Region56-Instances`.idLanguages = ' . $this->intLanguageId . '
                                          LEFT JOIN locations ON
                                            locations.id = `global-' . $strGenForm . '-Region56-Instances`.location
                                          LEFT JOIN `global-' . $strGenForm . '-Region56-InstanceMultiFields` ON
                                            `global-' . $strGenForm . '-Region56-InstanceMultiFields`.globalId = globals.globalId AND
                                            `global-' . $strGenForm . '-Region56-InstanceMultiFields`.version = globals.version AND
                                            `global-' . $strGenForm . '-Region56-InstanceMultiFields`.idLanguages = ' . $this->intLanguageId . ' AND
                                            `global-' . $strGenForm . '-Region56-InstanceMultiFields`.idRegionInstances = `global-' . $strGenForm . '-Region56-Instances`.id AND
                                            `global-' . $strGenForm . '-Region56-InstanceMultiFields`.idFields = 176
                                          LEFT JOIN contacts ON
                                            contacts.id = `global-' . $strGenForm . '-Region56-InstanceMultiFields`.idRelation
                                          LEFT JOIN globalCategories ON
                                            globalCategories.globalId = globals.globalId AND
                                            globalCategories.version = globals.version AND
                                            globalCategories.idLanguages = ' . $this->intLanguageId . '
                                          LEFT JOIN categoryTitles ON
                                            categoryTitles.idCategories = globalCategories.category AND
                                            categoryTitles.idLanguages = globalCategories.idLanguages';
            } else {
                $strSqlInstanceFields = ' `global-' . $strGenForm . '-Instances`.shortdescription,
                                  `global-' . $strGenForm . '-Instances`.description,';
            }

            $strSqlWhereGlobalIds = '';
            if (count($arrGlobalIds) > 0) {
                $strSqlWhereGlobalIds = ' WHERE globals.id IN (' . implode(',', $arrGlobalIds) . ')';
            }

            if ($intImgFilterTag > 0) {
                $strSqlInstanceFields .= ' tagFile.filename AS tagfilename, tagFile.version AS tagfileversion, tagFile.path AS tagfilepath, tagFileTitles.title AS tagfiletitle,';
                $strSqlAddon .= '
                                          LEFT JOIN `global-' . $strGenForm . '-InstanceFiles` AS iTagFiles ON
                                            iTagFiles.id = (SELECT iTagFl.id FROM `global-' . $strGenForm . '-InstanceFiles` AS iTagFl
                                                         INNER JOIN tagFiles ON tagFiles.fileId = iTagFl.idFiles AND tagFiles.idTags = ' . $intImgFilterTag . '
                                                         WHERE iTagFl.globalId = globals.globalId AND iTagFl.version = globals.version AND iTagFl.idLanguages = ' . $this->intLanguageId . ' AND iTagFl.idFields IN (' . $strImgFieldIds . ')
                                                         ORDER BY iTagFl.idFields DESC LIMIT 1)
                                          LEFT JOIN files AS tagFile ON
                                            tagFile.id = iTagFiles.idFiles AND
                                            tagFile.isImage = 1
                                          LEFT JOIN fileTitles AS tagFileTitles ON
                                            tagFileTitles.idFiles = tagFile.id AND
                                            tagFileTitles.idLanguages = ' . $this->intLanguageId;
            }

            $sqlStmt = $this->core->dbh->query('SELECT globals.id,
                                            ' . $strSqlInstanceFields . '
                                            files.filename, files.version AS fileversion, files.path AS filepath, fileTitles.title AS filetitle
                                          FROM globals
                                          LEFT JOIN globalDatetimes ON
                                            globalDatetimes.globalId = globals.globalId AND
                                            globalDatetimes.version = globals.version AND
                                            globalDatetimes.idLanguages = ?
                                          LEFT JOIN `global-' . $strGenForm . '-Instances` ON
                                            `global-' . $strGenForm . '-Instances`.globalId = globals.globalId AND
                                            `global-' . $strGenForm . '-Instances`.version = globals.version AND
                                            `global-' . $strGenForm . '-Instances`.idLanguages = ?
                                          ' . $strSqlAddon . '
                                          LEFT JOIN `global-' . $strGenForm . '-InstanceFiles` AS iFiles ON
                                            iFiles.id = (SELECT iFl.id FROM `global-' . $strGenForm . '-InstanceFiles` AS iFl
                                                         WHERE iFl.globalId = globals.globalId AND iFl.version = globals.version AND iFl.idLanguages = ? AND iFl.idFields IN (' . $strImgFieldIds . ')
                                                         ORDER BY iFl.idFields DESC LIMIT 1)
                                          LEFT JOIN files ON
                                            files.id = iFiles.idFiles AND
                                            files.isImage = 1
                                          LEFT JOIN fileTitles ON
                                            fileTitles.idFiles = files.id AND
                                            fileTitles.idLanguages = ?
                                          ' . $strSqlWhereGlobalIds, array($this->intLanguageId, $this->intLanguageId, $this->intLanguageId, $this->intLanguageId));

            return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
        }
    }

    /**
     * search
     * @param string $strSearchValue
     * @return Zend_Db_Table_Rowset_Abstract Global
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     *
     */
    public function search($strSearchValue)
    {

        $objSelect = $this->getGlobalTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('globals', array('id', 'globalId', 'version', 'title' => 'IF(displayTitle.title <> \'\', displayTitle.title, fallbackTitle.title)', 'isStartGlobal', 'idParent', 'idParentTypes', 'globalProperties.idGlobalTypes', 'globalProperties.showInNavigation', 'globalProperties.idLanguageFallbacks', 'globalProperties.published', 'globalProperties.changed', 'globalProperties.idStatus', 'globalProperties.creator'))
            ->joinInner('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version', array())
            ->joinInner('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = globalProperties.idLanguages', array())
            ->joinInner('languages', 'languages.id = globalTitles.idLanguages', array('glLanguages' => 'GROUP_CONCAT(languages.languageCode SEPARATOR \', \')'))
            ->joinLeft(array('displayTitle' => 'globalTitles'), 'displayTitle.globalId = globals.globalId AND displayTitle.version = globals.version AND displayTitle.idLanguages = ' . $this->intLanguageId, array())
            ->joinInner(array('fallbackTitle' => 'globalTitles'), 'fallbackTitle.globalId = globals.globalId AND fallbackTitle.version = globals.version AND fallbackTitle.idLanguages = 0', array())
            ->where('globalTitles.title LIKE ?', '%' . $strSearchValue . '%')
            ->where('idParent = ?', $this->core->sysConfig->product->rootLevels->list->id)
            ->where('idParentTypes = ?', $this->core->sysConfig->parent_types->rootlevel)
            ->where('isStartGlobal = 0')
            ->group('globals.globalId')
            ->order('globalTitles.title');

        return $this->getGlobalTable()->fetchAll($objSelect);
    }

    /**
     * add
     * @param GenericSetup $objGenericSetup
     * @return stdClass Global
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function add(GenericSetup $objGenericSetup)
    {
        $this->core->logger->debug('global->models->Model_Globals->add()');

        $objGlobal = new stdClass();
        $objGlobal->globalId = uniqid();
        $objGlobal->version = 1;
        $objGlobal->sortPosition = GenericSetup::DEFAULT_SORT_POSITION;
        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

        if ($objGenericSetup->getRootLevelGroupId() == $this->core->sysConfig->root_level_groups->product) {
            $objGlobal->parentId = $this->core->sysConfig->product->rootLevels->list->id;
            $objGlobal->parentTypeId = $this->core->sysConfig->parent_types->rootlevel;
        } else {
            /**
             * check if parent element is rootlevel or folder and get sort position
             */
            if ($objGenericSetup->getParentId() != '' && $objGenericSetup->getParentId() > 0) {
                $objGlobal->parentId = $objGenericSetup->getParentId();
                $objGlobal->parentTypeId = $this->core->sysConfig->parent_types->folder;
                $objData = $this->getModelFolders()->countGlobalChilds($objGlobal->parentId);
            } else {
                if ($objGenericSetup->getRootLevelId() != '' && $objGenericSetup->getRootLevelId() > 0) {
                    $objGlobal->parentId = $objGenericSetup->getRootLevelId();
                } else {
                    $this->core->logger->err('zoolu->modules->global->models->Model_Globals->add(): intRootLevelId is empty!');
                }
                $objGlobal->parentTypeId = $this->core->sysConfig->parent_types->rootlevel;
                $objData = $this->getModelFolders()->countGlobalRootChilds($objGlobal->parentId);
            }

            if (count($objData) == 1) {
                $objGlobal->sortPosition = current($objData)->counter;
            }
        }

        /**
         * insert main data
         */
        $arrMainData = array(
            'idParent'         => $objGlobal->parentId,
            'idParentTypes'    => $objGlobal->parentTypeId,
            'isStartGlobal'    => $objGenericSetup->getIsStartElement(),
            'idUsers'          => $intUserId,
            'sortPosition'     => $objGlobal->sortPosition,
            'sortTimestamp'    => date('Y-m-d H:i:s'),
            'globalId'         => $objGlobal->globalId,
            'version'          => $objGlobal->version,
            'creator'          => $objGenericSetup->getCreatorId(),
            'created'          => date('Y-m-d H:i:s')
        );
        $objGlobal->id = $this->getGlobalTable()->insert($arrMainData);

        /**
         * insert language specific properties
         */
        $arrProperties = array(
            'globalId'             => $objGlobal->globalId,
            'version'              => $objGlobal->version,
            'idLanguages'          => $this->intLanguageId,
            'idLanguageFallbacks'  => $objGenericSetup->getLanguageFallbackId(),
            'idGenericForms'       => $objGenericSetup->getGenFormId(),
            'idTemplates'          => $objGenericSetup->getTemplateId(),
            'idGlobalTypes'        => $objGenericSetup->getElementTypeId(),
            'showInNavigation'     => $objGenericSetup->getShowInNavigation(),
            'idUsers'              => $intUserId,
            'creator'              => $objGenericSetup->getCreatorId(),
            'publisher'            => $intUserId,
            'created'              => date('Y-m-d H:i:s'),
            'published'            => $objGenericSetup->getPublishDate(),
            'idStatus'             => $objGenericSetup->getStatusId()
        );
        $this->getGlobalPropertyTable()->insert($arrProperties);

        /**
         * if is tree add, make alis now
         */
        if ($objGenericSetup->getRootLevelId() == $this->core->sysConfig->product->rootLevels->tree->id) {
            $objGlobal->parentId = $objGenericSetup->getParentId();
            $objGlobal->rootLevelId = $objGenericSetup->getRootLevelId();
            $objGlobal->rootLevelGroupId = $objGenericSetup->getRootLevelGroupId();
            $objGlobal->isStartElement = $objGenericSetup->getIsStartElement();
            $this->addLink($objGlobal);
        }

        return $objGlobal;
    }

    /**
     * addLink
     * @param stdClass $objGlobal
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addLink($objGlobal)
    {
        $this->core->logger->debug('global->models->Model_Globals->addLink()');

        $objGlobal->linkGlobalId = uniqid();
        $objGlobal->linkVersion = 1;
        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

        /**
         * check if parent element is rootlevel or folder and get sort position
         */
        if ($objGlobal->parentId != '' && $objGlobal->parentId > 0) {
            $objGlobal->parentTypeId = $this->core->sysConfig->parent_types->folder;
            $objData = $this->getModelFolders()->countGlobalChilds($objGlobal->parentId);
        } else {
            if ($objGlobal->rootLevelId != '' && $objGlobal->rootLevelId > 0) {
                $objGlobal->parentId = $objGlobal->rootLevelId;
            } else {
                $this->core->logger->err('zoolu->modules->global->models->Model_Globals->addLink(): intRootLevelId is empty!');
            }
            $objGlobal->parentTypeId = $this->core->sysConfig->parent_types->rootlevel;
            $objData = $this->getModelFolders()->countGlobalRootChilds($objGlobal->parentId);
        }

        if (count($objData) == 1) {
            $objGlobal->sortPosition = current($objData)->counter;
        }

        /**
         * insert main data
         */
        $arrMainData = array(
            'idParent'         => $objGlobal->parentId,
            'idParentTypes'    => $objGlobal->parentTypeId,
            'isStartGlobal'    => $objGlobal->isStartElement,
            'idUsers'          => $intUserId,
            'sortPosition'     => $objGlobal->sortPosition,
            'sortTimestamp'    => date('Y-m-d H:i:s'),
            'globalId'         => $objGlobal->linkGlobalId,
            'version'          => $objGlobal->linkVersion,
            'creator'          => $intUserId,
            'created'          => date('Y-m-d H:i:s')
        );
        $objGlobal->linkId = $this->getGlobalTable()->insert($arrMainData);

        $arrLinkedGlobal = array(
            'idGlobals'  => $objGlobal->linkId,
            'globalId'   => $objGlobal->globalId
        );
        $this->getGlobalLinkTable()->insert($arrLinkedGlobal);
    }

    /**
     * update
     * @param GenericSetup $objGenericSetup
     * @param object Global
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function update(GenericSetup $objGenericSetup, $objGlobal)
    {
        $this->core->logger->debug('global->models->Model_Globals->update()');

        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

        $strWhere = $this->getGlobalTable()->getAdapter()->quoteInto('globalId = ?', $objGlobal->globalId);
        $strWhere .= $this->getGlobalTable()->getAdapter()->quoteInto(' AND version = ?', $objGlobal->version);

        $this->getGlobalTable()->update(array(
                                             'idUsers'  => $intUserId,
                                             'changed'  => date('Y-m-d H:i:s')
                                        ), $strWhere);
        /**
         * update language specific global properties
         */
        $strWhere .= $this->getGlobalTable()->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);
        $intNumOfEffectedRows = $this->getGlobalPropertyTable()->update(array(
                                                                             'idGenericForms'      => $objGenericSetup->getGenFormId(),
                                                                             'idTemplates'         => $objGenericSetup->getTemplateId(),
                                                                             'idGlobalTypes'       => $objGenericSetup->getElementTypeId(),
                                                                             'showInNavigation'    => $objGenericSetup->getShowInNavigation(),
                                                                             'idLanguageFallbacks' => $objGenericSetup->getLanguageFallbackId(),
                                                                             'idUsers'             => $intUserId,
                                                                             'creator'             => $objGenericSetup->getCreatorId(),
                                                                             'idStatus'            => $objGenericSetup->getStatusId(),
                                                                             'published'           => $objGenericSetup->getPublishDate(),
                                                                             'changed'             => date('Y-m-d H:i:s')
                                                                        ), $strWhere);

        /**
         * insert language specific global properties
         */
        if ($intNumOfEffectedRows == 0) {
            $arrProperties = array(
                'globalId'             => $objGlobal->globalId,
                'version'              => $objGlobal->version,
                'idLanguages'          => $this->intLanguageId,
                'idLanguageFallbacks'  => $objGenericSetup->getLanguageFallbackId(),
                'idGenericForms'       => $objGenericSetup->getGenFormId(),
                'idTemplates'          => $objGenericSetup->getTemplateId(),
                'idGlobalTypes'        => $objGenericSetup->getElementTypeId(),
                'showInNavigation'     => $objGenericSetup->getShowInNavigation(),
                'idUsers'              => $intUserId,
                'creator'              => $objGenericSetup->getCreatorId(),
                'publisher'            => $intUserId,
                'created'              => date('Y-m-d H:i:s'),
                'published'            => $objGenericSetup->getPublishDate(),
                'idStatus'             => $objGenericSetup->getStatusId()
            );
            $this->getGlobalPropertyTable()->insert($arrProperties);
        }

    }

    /**
     * updateFolderStartGlobal
     * @param integer $intFolderId
     * @param array $arrProperties
     * @param string $arrTitle
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function updateFolderStartGlobal($intFolderId, $arrProperties, $arrTitle, $intRootLevelGroupId, $intDefaultTemplateId, $rootLevelId)
    {
        $objSelect = $this->getGlobalTable()->select()->setIntegrityCheck(false);
        $objSelect->from('globals', array('globalId', 'version'));

        if ($intRootLevelGroupId == $this->core->sysConfig->root_level_groups->product) {
            $objSelect->join('globalLinks', 'globalLinks.globalId = globals.globalId', array('linkId' => 'idGlobals'));
            $objSelect->join(array('lP' => 'globals'), 'lP.id = globalLinks.idGlobals', array());
            $objSelect->where('lP.idParent = ?', $intFolderId)
                ->where('lP.idParentTypes = ?', $this->core->sysConfig->parent_types->folder)
                ->where('lP.isStartGlobal = 1');
            $objSelect->order(array('lP.version DESC'));
        } else {
            $objSelect->where('idParent = ?', $intFolderId)
                ->where('idParentTypes = ?', $this->core->sysConfig->parent_types->folder)
                ->where('isStartGlobal = 1');
            $objSelect->order(array('version DESC'));
        }
        $objSelect->limit(1);

        $objStartGlobal = $this->objGlobalTable->fetchAll($objSelect);

        if (count($objStartGlobal) > 0) {
            $objStartGlobal = $objStartGlobal->current();

            $strWhere = $this->getGlobalPropertyTable()->getAdapter()->quoteInto('globalId = ?', $objStartGlobal->globalId);
            $strWhere .= $this->objGlobalPropertyTable->getAdapter()->quoteInto(' AND version = ?', $objStartGlobal->version);
            $strWhere .= $this->objGlobalPropertyTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);

            $intNumOfEffectedRows = $this->objGlobalPropertyTable->update($arrProperties, $strWhere);
            if ($intNumOfEffectedRows == 0) {
                $arrProperties = array_merge($arrProperties, array('globalId' => $objStartGlobal->globalId, 'version' => $objStartGlobal->version, 'idLanguages' => $this->intLanguageId, 'idTemplates' => $intDefaultTemplateId));
                $this->objGlobalPropertyTable->insert($arrProperties);
            }

            $intNumOfEffectedRows = $this->core->dbh->update('globalTitles', $arrTitle, $strWhere);

            if ($intNumOfEffectedRows == 0) {
                $arrTitle = array_merge($arrTitle, array('globalId' => $objStartGlobal->globalId, 'version' => $objStartGlobal->version, 'idLanguages' => $this->intLanguageId));
                $this->core->dbh->insert('globalTitles', $arrTitle);
            }
            
            if ($arrProperties['idStatus'] == $this->core->sysConfig->status->live) {
                $strIndexGlobalFilePath = GLOBAL_ROOT_PATH . 'cli/IndexGlobal.php';
                if (file_exists($strIndexGlobalFilePath)) {
                    exec("php " . $strIndexGlobalFilePath . " --globalId='" . $objStartGlobal->globalId . "' --linkId='" . $objStartGlobal->linkId . "' --version=" . $objStartGlobal->version . " --languageId=" . $this->intLanguageId . " --rootLevelId=" . $rootLevelId . " --env=" . APPLICATION_ENV . " > /dev/null &#038;");
                }
            } else {
                $objIndex = new Index();
                $objIndex->indexRemoveGlobals($objStartGlobal->globalId . '_'. $this->intLanguageId . '_r*');
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
    public function delete($intElementId, $bnlIsLink = false, $rootLevelId)
    {
        $this->core->logger->debug('global->models->Model_Globals->delete()');
        $objGlobal = $this->load($intElementId);
        
        if (count($objGlobal) == 1) {
            $objGlobal = $objGlobal->current();
            $strGlobalId = $objGlobal->globalId;

            if ($objGlobal->idParent == $this->core->sysConfig->product->rootLevels->list->id &&
                $objGlobal->idParentTypes == $this->core->sysConfig->parent_types->rootlevel
            ) {
                //TODO:: delet all link globals
            }
            
            $indexToRemove = null;
            if ($bnlIsLink) {
                $objSelect = $this->getGlobalLinkTable()->select();
                $objSelect->where('idGlobals = ?', $intElementId);
                $globalLink = $this->getGlobalTable()->fetchRow($objSelect);
                if ($globalLink !== null) {
                    $indexToRemove = $globalLink->globalId; 
                }
            } else {
                $indexToRemove = $strGlobalId;
            }
            
            if ($indexToRemove !== null) {
                $objIndex = new Index();
                $objIndex->indexRemoveGlobals($indexToRemove . '_*');
                $this->indexOtherProduktLinks($intElementId, $indexToRemove, $rootLevelId);
            }
            $strWhere = $this->objGlobalTable->getAdapter()->quoteInto('relationId = ?', $strGlobalId);
            $strWhere .= $this->objGlobalTable->getAdapter()->quoteInto(' AND idUrlTypes = ?', $this->core->sysConfig->url_types->global);
            $this->getGlobalUrlTable()->delete($strWhere);
        }
        $strWhere = $this->getGlobalTable()->getAdapter()->quoteInto('id = ?', $intElementId);
        return $this->objGlobalTable->delete($strWhere);
    }
    
    /**
     * indexOtherProduktLinks
     * @param int $intElementId
     * @param String $strGlobalId
     */
    public function indexOtherProduktLinks($intElementId, $strGlobalId, $rootLevelId) {
        $objSelect = $this->getGlobalLinkTable()->select()->setIntegrityCheck(false);
        $objSelect->from('globalLinks', array('idGlobals'));
        $objSelect->join('globalProperties', 'globalProperties.globalId = globalLinks.globalId AND globalProperties.idStatus = ' . $this->core->sysConfig->status->live, array('idLanguages', 'version'));
        $objSelect->where('globalLinks.globalId = ?', $strGlobalId);
        $objSelect->where('globalLinks.idGlobals != ?', $intElementId);
        $globalLinks = $this->getGlobalTable()->fetchAll($objSelect);
        
        $arrIndexedLanguages = array();
        foreach ($globalLinks as $globalLink) {
            $strIndexGlobalFilePath = GLOBAL_ROOT_PATH . 'cli/IndexGlobal.php';
            if (file_exists($strIndexGlobalFilePath)) {
                if (!in_array($globalLink->idLanguages, $arrIndexedLanguages)) {
                    exec("php " . $strIndexGlobalFilePath . " --globalId='" . $strGlobalId . "' --linkId='" . $globalLink->idGlobals . "' --version=" . $globalLink->version . " --languageId=" . $globalLink->idLanguages . " --rootLevelId=" . $rootLevelId . " --env=" . APPLICATION_ENV . " > /dev/null &#038;");
                    $arrIndexedLanguages[] = $globalLink->idLanguages;
                }
            }
        }
    }

    /**
     * loadParentFolders
     * @param integer $intElementId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadParentFolders($intElementId, $languageId = 0)
    {
        if ($languageId == 0) {
            $languageId = $this->intLanguageId;
        }
        $this->core->logger->debug('global->models->Model_Globals->loadParentFolders(' . $intElementId . ')');

        $sqlStmt = $this->core->dbh->query('SELECT folders.id, folders.folderId, folderProperties.isUrlFolder, folderTitles.title
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
                                            INNER JOIN globals ON
                                              globals.id = ? AND
                                              parent.id = globals.idParent AND
                                              globals.idParentTypes = ?
                                           WHERE folders.lft <= parent.lft AND
                                                 folders.rgt >= parent.rgt AND
                                                 folders.idRootLevels = parent.idRootLevels
                                             ORDER BY folders.rgt', array($languageId, $languageId, $intElementId, $this->core->sysConfig->parent_types->folder));
        return $sqlStmt->fetchAll(Zend_Db::FETCH_OBJ);
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
        $this->core->logger->debug('global->models->Model_Globals->loadContacts(' . $intElementId . ',' . $intFieldId . ')');

        $objSelect = $this->getGlobalContactsTable()->select();
        $objSelect->from($this->objGlobalContactsTable, array('idContacts'));
        $objSelect->join('globals', 'globals.globalId = globalContacts.globalId AND globals.version = globalContacts.version AND globalContacts.idLanguages = ' . $this->intLanguageId, array());
        $objSelect->where('globals.id = ?', $intElementId)
            ->where('idFields = ?', $intFieldId);

        $arrGlobalContactData = $this->objGlobalContactsTable->fetchAll($objSelect);

        $strContactIds = '';
        foreach ($arrGlobalContactData as $objGlobalContact) {
            $strContactIds .= '[' . $objGlobalContact->idContacts . ']';
        }

        return $strContactIds;
    }

    /**
     * Loads all the languages in which the global with the given id exists
     * @param $intElementId The id of the page
     * @return Zend_Db_Table_Select
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function loadLanguages($intElementId)
    {
        $this->core->logger->debug('cms->models->Model_Pages->loadLanguages('.$intElementId.')');

        $objSelect = $this->getGlobalTable()->select()->setIntegrityCheck(false);
        $objSelect->from($this->getGlobalTable(), array());
        $objSelect->join('globalProperties', 'globalProperties.globalId = globals.globalId', array('idLanguages'));
        $objSelect->where('idLanguages != ?', 0);
        $objSelect->where('globals.id = ?', $intElementId);

        return $this->getGlobalTable()->fetchAll($objSelect);
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
        $this->core->logger->debug('global->models->Model_Globals->addContact(' . $intElementId . ',' . $strContactIds . ',' . $intFieldId . ')');

        $objGlobalData = $this->load($intElementId);

        if (count($objGlobalData) > 0) {
            $objGlobal = $objGlobalData->current();

            $this->getGlobalContactsTable();

            $strWhere = $this->objGlobalContactsTable->getAdapter()->quoteInto('globalId = ?', $objGlobal->globalId);
            $strWhere .= 'AND ' . $this->objGlobalContactsTable->getAdapter()->quoteInto('version = ?', $objGlobal->version);
            $strWhere .= 'AND ' . $this->objGlobalContactsTable->getAdapter()->quoteInto('idLanguages = ?', $this->intLanguageId);
            $strWhere .= 'AND ' . $this->objGlobalContactsTable->getAdapter()->quoteInto('idFields = ?', $intFieldId);
            $this->objGlobalContactsTable->delete($strWhere);

            $strContactIds = trim($strContactIds, '[]');
            $arrContactIds = explode('][', $strContactIds);

            $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

            foreach ($arrContactIds as $intContactId) {
                $arrData = array(
                    'globalId'     => $objGlobal->globalId,
                    'version'      => $objGlobal->version,
                    'idLanguages'  => $this->intLanguageId,
                    'idContacts'   => $intContactId,
                    'idFields'     => $intFieldId,
                    'creator'      => $intUserId
                );
                $this->objGlobalContactsTable->insert($arrData);
            }
        }
    }

    /**
     * addInternalLinks
     * @param string $strLinkedGlobalIds
     * @param string $strElementId
     * @param integer $intVersion
     * @param integer $intFieldId
     * @return integer
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addInternalLinks($strLinkedGlobalIds, $strElementId, $intVersion, $intFieldId)
    {
        $this->core->logger->debug('global->models->Model_Globals->addInternalLinks(' . $strLinkedGlobalIds . ', ' . $strElementId . ', ' . $intVersion . ', ' . $intFieldId . ')');

        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

        $arrData = array(
            'globalId'     => $strElementId,
            'version'      => $intVersion,
            'idLanguages'  => $this->intLanguageId,
            'idFields'     => $intFieldId,
            'idUsers'      => $intUserId,
            'creator'      => $intUserId,
            'created'      => date('Y-m-d H:i:s')
        );

        $strTmpLinkedGlobalIds = trim($strLinkedGlobalIds, '[]');
        $arrLinkedGlobalIds = explode('][', $strTmpLinkedGlobalIds);

        if (count($arrLinkedGlobalIds) > 0) {
            foreach ($arrLinkedGlobalIds as $sortPosition => $strLinkedGlobalId) {
                $arrData['linkedGlobalId'] = $strLinkedGlobalId;
                $arrData['sortPosition'] = $sortPosition + 1;
                $this->getGlobalInternalLinkTable()->insert($arrData);
            }
        }
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
    public function loadInternalLinks($strElementId, $intVersion, $intFieldId, $intRootLevelId = null)
    {
        $this->core->logger->debug('global->models->Model_Globals->loadInternalLinks(' . $strElementId . ',' . $intVersion . ',' . $intFieldId . ',' . $intRootLevelId . ')');

        $objSelect = $this->getGlobalInternalLinkTable()->select();
        $objSelect->setIntegrityCheck(false);

        //if rootlevel is products, load globalLinks
        if (!empty($intRootLevelId) && $intRootLevelId != $this->core->sysConfig->product->rootLevels->list->id && $intRootLevelId != $this->core->sysConfig->product->rootLevels->tree->id) {
            $objSelect->from('globals', array('globals.id', 'relationId' => 'globals.globalId', 'globals.globalId', 'globals.version', 'globalProperties.idGlobalTypes', 'isStartItem' => 'globals.isStartGlobal', 'globals.isStartGlobal', 'globalProperties.idStatus'));
            $objSelect->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('idTemplates'));
            $objSelect->joinLeft('urls', 'urls.relationId = globals.globalId AND urls.version = globals.version AND urls.idUrlTypes = '.$this->core->sysConfig->url_types->global.' AND urls.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE).' AND urls.isMain = 1 AND urls.idParent IS NULL', array('url'));
            $objSelect->joinLeft('languages', 'languages.id = urls.idLanguages', array('languageCode'));
            $objSelect->join('globalInternalLinks', 'globalInternalLinks.linkedGlobalId = globals.globalId AND globalInternalLinks.globalId = '.$this->core->dbh->quote($strElementId).' AND globalInternalLinks.version = '.$this->core->dbh->quote($intVersion, Zend_Db::INT_TYPE).' AND globalInternalLinks.idFields = '.$this->core->dbh->quote($intFieldId, Zend_Db::INT_TYPE).' AND globalInternalLinks.idLanguages = '.$this->intLanguageId, array('sortPosition'));
            $objSelect->join('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('title'));
            $objSelect->joinLeft('globalFiles', 'globalFiles.id = (SELECT iFl.id FROM globalFiles AS iFl WHERE iFl.globalId = globals.globalId AND iFl.version = globals.version AND iFl.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE).' AND iFl.idFields IN (174, 5, 55) ORDER BY iFl.idFields DESC LIMIT 1)', array()); //FIXME
            $objSelect->joinLeft('files', 'files.id = globalFiles.idFiles AND files.isImage = 1', array('filename', 'fileversion' => 'version', 'filepath' => 'path'));
            $objSelect->joinLeft('fileTitles', 'fileTitles.idFiles = files.id AND fileTitles.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('filetitle' => 'title'));
            $objSelect->order('globalInternalLinks.sortPosition ASC');
        } else {
            $objSelect->from('globals', array('globals.id', 'relationId' => 'globals.globalId', 'globals.globalId', 'globals.version', 'globalProperties.idGlobalTypes', 'isStartItem' => 'globals.isStartGlobal', 'globals.isStartGlobal', 'globalProperties.idStatus'));
            $objSelect->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array());
            $objSelect->join('globalLinks', 'globalLinks.globalId = globals.globalId', array());
            $objSelect->join(array('lP' => 'globals'), 'lP.id = globalLinks.idGlobals', array('lPId' => 'globalId'));
            $objSelect->joinLeft('urls', 'urls.relationId = lP.globalId AND urls.version = lP.version AND urls.idUrlTypes = '.$this->core->sysConfig->url_types->global.' AND urls.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE).' AND urls.isMain = 1 AND urls.idParent IS NULL', array('url'));
            $objSelect->joinLeft('languages', 'languages.id = urls.idLanguages', array('languageCode'));
            $objSelect->join('globalInternalLinks', 'globalInternalLinks.linkedGlobalId = lP.globalId AND globalInternalLinks.globalId = '.$this->core->dbh->quote($strElementId).' AND globalInternalLinks.version = '.$this->core->dbh->quote($intVersion, Zend_Db::INT_TYPE).' AND globalInternalLinks.idFields = '.$this->core->dbh->quote($intFieldId, Zend_Db::INT_TYPE).' AND globalInternalLinks.idLanguages = '.$this->intLanguageId, array('sortPosition'));
            $objSelect->join('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('title'));
            $objSelect->joinLeft(array('iFiles' => 'global-DEFAULT_PRODUCT-1-InstanceFiles'), 'iFiles.id = (SELECT iFl.id FROM `global-DEFAULT_PRODUCT-1-InstanceFiles` AS iFl WHERE iFl.globalId = globals.globalId AND iFl.version = globals.version AND iFl.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE).' AND iFl.idFields IN (174, 5, 55) ORDER BY iFl.idFields DESC LIMIT 1)', array()); //FIXME
            $objSelect->joinLeft('files', 'files.id = iFiles.idFiles AND files.isImage = 1', array('filename', 'fileversion' => 'version', 'filepath' => 'path'));
            $objSelect->joinLeft('fileTitles', 'fileTitles.idFiles = files.id AND fileTitles.idLanguages = '.$this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('filetitle' => 'title'));
            $objSelect->order('globalInternalLinks.sortPosition ASC');
        }

        return $this->objGlobalInternalLinkTable->fetchAll($objSelect);
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
        $this->core->logger->debug('global->models->Model_Globals->deleteInternalLinks(' . $strElementId . ',' . $intVersion . ',' . $intFieldId . ')');

        $strWhere = $this->getGlobalInternalLinkTable()->getAdapter()->quoteInto('globalId = ?', $strElementId);
        $strWhere .= $this->objGlobalInternalLinkTable->getAdapter()->quoteInto(' AND version = ?', $intVersion);
        $strWhere .= $this->objGlobalInternalLinkTable->getAdapter()->quoteInto(' AND idFields = ?', $intFieldId);
        $strWhere .= $this->objGlobalInternalLinkTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);

        return $this->objGlobalInternalLinkTable->delete($strWhere);
    }

    /**
     * addArticles
     * @param array $arrArticles
     * @param string $strElementId
     * @param integer $intVersion
     * @param integer $intFieldId
     * @return integer
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function addArticles($arrArticles, $strElementId, $intVersion, $intFieldId)
    {
        $this->core->logger->debug('global->models->Model_Globals->addArticles(' . $arrArticles . ', ' . $strElementId . ', ' . $intVersion . ', ' . $intFieldId . ')');

        $intUserId = Zend_Auth::getInstance()->getIdentity()->id;

        $arrData = array(
            'globalId'     => $strElementId,
            'version'      => $intVersion,
            'idLanguages'  => $this->intLanguageId,
            'idFields'     => $intFieldId,
            'idUsers'      => $intUserId,
            'creator'      => $intUserId,
            'created'      => date('Y-m-d H:i:s')
        );

        if (count($arrArticles) > 0) {
            foreach ($arrArticles as $sortPosition => $objArticle) {
                $this->getGlobalArticleTable()->insert(array_merge(array(
                                                                        'size'           => $objArticle->size,
                                                                        'price'          => $objArticle->price,
                                                                        'discount'       => $objArticle->discount,
                                                                        'weight'         => $objArticle->weight,
                                                                        'article_number' => $objArticle->article_number,
                                                                        'sortPosition'   => $sortPosition + 1
                                                                   ), $arrData));
            }
        }
    }

    /**
     * loadArticles
     * @param string $strElementId
     * @param integer $intVersion
     * @param integer $intFieldId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadArticles($strElementId, $intVersion, $intFieldId)
    {
        $this->core->logger->debug('global->models->Model_Globals->loadArticles(' . $strElementId . ',' . $intVersion . ',' . $intFieldId . ')');

        $objSelect = $this->getGlobalArticleTable()->select();

        $objSelect->from('globalArticles', array('size', 'price', 'discount', 'weight', 'article_number'))
            ->where('globalId = ?', $strElementId)
            ->where('version = ?', $intVersion)
            ->where('idLanguages = ?', $this->intLanguageId)
            ->where('idFields = ?', $intFieldId)
            ->order('sortPosition ASC');

        return $this->objGlobalArticleTable->fetchAll($objSelect);
    }
    
    /**
     * deleteArticles
     * @param string $strElementId
     * @param integer $intVersion
     * @param integer $intFieldId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function deleteArticles($strElementId, $intVersion, $intFieldId)
    {
        $this->core->logger->debug('global->models->Model_Globals->deleteArticles(' . $strElementId . ',' . $intVersion . ',' . $intFieldId . ')');

        $strWhere = $this->getGlobalArticleTable()->getAdapter()->quoteInto('globalId = ?', $strElementId);
        $strWhere .= $this->objGlobalArticleTable->getAdapter()->quoteInto(' AND version = ?', $intVersion);
        $strWhere .= $this->objGlobalArticleTable->getAdapter()->quoteInto(' AND idFields = ?', $intFieldId);
        $strWhere .= $this->objGlobalArticleTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);

        return $this->objGlobalArticleTable->delete($strWhere);
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
        $this->core->logger->debug('global->models->Model_Globals->loadVideo(' . $intElementId . ')');

        $objSelect = $this->getGlobalVideoTable()->select();
        $objSelect->from($this->objGlobalVideoTable, array('userId', 'videoId', 'idVideoTypes', 'thumb', 'title'));
        $objSelect->join('globals', 'globals.globalId = globalVideos.globalId AND globals.version = globalVideos.version', array());
        $objSelect->where('globals.id = ?', $intElementId)
            ->where('idLanguages = ?', $this->getLanguageId());

        return $this->objGlobalVideoTable->fetchAll($objSelect);
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
        $this->core->logger->debug('global->models->Model_Globals->addVideo(' . $intElementId . ',' . $mixedVideoId . ',' . $intVideoTypeId . ',' . $strVideoUserId . ',' . $strVideoThumb . ',' . $strVideoTitle . ')');

        $objGlobalData = $this->load($intElementId);

        if (count($objGlobalData) > 0) {
            $objGlobal = $objGlobalData->current();

            $this->getGlobalVideoTable();

            $strWhere = $this->objGlobalVideoTable->getAdapter()->quoteInto('globalId = ?', $objGlobal->globalId);
            $strWhere .= 'AND ' . $this->objGlobalVideoTable->getAdapter()->quoteInto('version = ?', $objGlobal->version);
            $this->objGlobalVideoTable->delete($strWhere);

            if ($mixedVideoId != '') {
                $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
                $arrData = array(
                    'globalId'      => $objGlobal->globalId,
                    'version'       => $objGlobal->version,
                    'idLanguages'   => $this->intLanguageId,
                    'userId'        => $strVideoUserId,
                    'videoId'       => $mixedVideoId,
                    'idVideoTypes'  => $intVideoTypeId,
                    'thumb'         => $strVideoThumb,
                    'title'         => $strVideoTitle,
                    'creator'       => $intUserId
                );
                return $objSelect = $this->objGlobalVideoTable->insert($arrData);
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
        $this->core->logger->debug('global->models->Model_Globals->removeVideo(' . $intElementId . ')');

        $objGlobalData = $this->load($intElementId);

        if (count($objGlobalData) > 0) {
            $objGlobal = $objGlobalData->current();

            $this->getGlobalVideoTable();

            $strWhere = $this->objGlobalVideoTable->getAdapter()->quoteInto('globalId = ?', $objGlobal->globalId);
            $strWhere .= 'AND ' . $this->objGlobalVideoTable->getAdapter()->quoteInto('version = ?', $objGlobal->version);
            $strWhere .= 'AND ' . $this->objGlobalVideoTable->getAdapter()->quoteInto('idLanguages = ?', $this->intLanguageId);

            return $this->objGlobalVideoTable->delete($strWhere);
        }
    }

    /**
     * loadParentUrl
     * @param integer $intGlobalId
     * @param boolean $blnIsStartElement
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadParentUrl($intGlobalId, $blnIsStartElement)
    {
        $this->core->logger->debug('global->models->Model_Globals->loadParentUrl(' . $intGlobalId . ',' . $blnIsStartElement . ')');

        $objSelect = $this->getGlobalUrlTable()->select();
        $objSelect->setIntegrityCheck(false);

        if ($blnIsStartElement == true) {
            $objSelect->from($this->objGlobalUrlTable, array('url', 'id'));
            $objSelect->join('globals', 'globals.globalId = urls.relationId', array('globalId', 'version', 'isStartglobal'));
            $objSelect->join('folders', 'folders.id = (SELECT idParent FROM globals WHERE id = ' . $intGlobalId . ')', array());
            $objSelect->where('urls.version = globals.version')
                ->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->global)
                ->where('urls.idLanguages = ?', $this->intLanguageId)
                ->where('urls.isMain = 1')
                ->where('globals.idParentTypes = ?', $this->core->sysConfig->parent_types->folder)
                ->where('globals.idParent = folders.idParentFolder')
                ->where('globals.isStartGlobal = 1');
        } else {
            $objSelect->from($this->objGlobalUrlTable, array('url', 'id'));
            $objSelect->join('globals', 'globals.globalId = urls.relationId', array('globalId', 'version', 'isStartglobal'));
            $objSelect->where('urls.version = globals.version')
                ->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->global)
                ->where('urls.idLanguages = ?', $this->intLanguageId)
                ->where('urls.isMain = 1')
                ->where('globals.idParentTypes = ?', $this->core->sysConfig->parent_types->folder)
                ->where('globals.idParent = (SELECT idParent FROM globals WHERE id = ' . $intGlobalId . ')')
                ->where('globals.isStartGlobal = 1');
        }

        return $this->objGlobalUrlTable->fetchAll($objSelect);
    }

    /**
     * loadUrlHistory
     * @param str $strGlobalId
     * @param integer $intLanguageId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Dominik Mößlang <dmo@massiveart.com>
     * @version 1.0
     */
    public function loadUrlHistory($intGlobalId, $intLanguageId)
    {
        $this->core->logger->debug('global->models->Model_Globals->loadUrlHistory(' . $intGlobalId . ', ' . $intLanguageId . ')');

        $objSelect = $this->getGlobalTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objGlobalTable, array('globalId', 'relationId' => 'globalId', 'version', 'isStartglobal'))
            ->join('urls', 'urls.relationId = globals.globalId AND urls.version = globals.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->global . ' AND urls.idLanguages = ' . $intLanguageId . ' AND urls.isMain = 0 AND urls.idParent IS NULL', array('id', 'url'))
            ->join('languages', 'languages.id = urls.idLanguages', array('languageCode'))
            ->joinLeft('folders', 'folders.id = globals.idParent AND globals.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array('idRootLevels'))
            ->joinLeft('rootLevels', 'rootLevels.id = folders.idRootLevels', array('languageDefinitionType'))
             ->joinLeft(array('rl' => 'rootLevels'), 'rl.id = globals.idParent AND globals.idParentTypes = ' . $this->core->sysConfig->parent_types->rootlevel, array('languageDefinitionType AS altLanguageDefinitionType'))
            ->where('globals.id = ?', $intGlobalId);

        return $this->objGlobalTable->fetchAll($objSelect);
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

        $objSelect = $this->getGlobalTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objGlobalTable, array('id', 'globalId', 'relationId' => 'globalId', 'version'))
            ->join('urls', 'urls.relationId = globals.globalId AND urls.version = globals.version AND urls.idUrlTypes = ' . $this->core->sysConfig->url_types->global . ' AND urls.idLanguages = ' . $this->intLanguageId . ' AND urls.isMain = 1', array('id', 'url'))
            ->join('folders AS parent', 'parent.id = ' . $intParentId, array())
            ->join('folders', 'folders.lft BETWEEN parent.lft AND parent.rgt AND folders.idRootLevels = parent.idRootLevels', array())
            ->where('globals.idParent = folders.id')
            ->where('globals.idParentTypes = ?', $this->core->sysConfig->parent_types->folder);

        return $this->objGlobalTable->fetchAll($objSelect);
    }

    /**
     * getElementsByIds
     * @param string $strElementIds
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getElementsByIds($strElementIds, $intRootLevelGroupId = 0)
    {
        $this->core->logger->debug('global->models->Model_Globals->getElementsByIds("' . $strElementIds . '")');

        $objSelect = $this->getGlobalTable()->select();
        $objSelect->setIntegrityCheck(false);

        if ($intRootLevelGroupId > 0 && $intRootLevelGroupId == $this->core->sysConfig->root_level_groups->product) {

            $objSelect->from('globals', array('id', 'relationId' => 'globalId', 'linkId' => 'lP.id', 'version', 'idParent', 'linkIdParent' => 'lP.idParent', 'idParentTypes', 'linkIdParentTypes' => 'lP.idParentTypes', 'isStartElement' => 'isStartGlobal', 'elementType' => new Zend_Db_Expr('"global"')))
                ->join('globalLinks', 'globalLinks.globalId = globals.globalId', array())
                ->join(array('lP' => 'globals'), 'lP.id = globalLinks.idGlobals', array())
                ->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('created', 'changed', 'published', 'idStatus'))
                ->joinLeft('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('title'))
                ->joinLeft(array('alternativeTitle' => 'globalTitles'), 'alternativeTitle.globalId = globals.globalId AND alternativeTitle.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('alternativeTitle' => 'title'))
                ->joinLeft(array('fallbackTitle' => 'globalTitles'), 'fallbackTitle.globalId = globals.globalId AND fallbackTitle.idLanguages = 0', array('fallbackTitle' => 'title'))
                ->joinleft('folders', 'folders.id = globals.idParent AND globals.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array('idRootLevels'));
            if (strpos($strElementIds, ',') !== false) {
                $objSelect->where('lP.id IN (' . $strElementIds . ')');
            } else {
                $objSelect->where('lP.id = ?', (int) $strElementIds);
            }

        } else {
            $objSelect->from('globals', array('id', 'relationId' => 'globalId', 'version', 'idParent', 'idParentTypes', 'isStartElement' => 'isStartGlobal', 'elementType' => new Zend_Db_Expr('"global"')))
                ->joinLeft('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('created', 'changed', 'published', 'idStatus', 'idLanguageFallbacks'))
                ->joinLeft('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('title'))
                ->joinLeft(array('alternativeTitle' => 'globalTitles'), 'alternativeTitle.globalId = globals.globalId AND alternativeTitle.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('alternativeTitle' => 'title'))
                ->joinLeft(array('fallbackTitle' => 'globalTitles'), 'fallbackTitle.globalId = globals.globalId AND fallbackTitle.idLanguages = 0', array('fallbackTitle' => 'title'))
                ->joinleft('folders', 'folders.id = globals.idParent AND globals.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array('idRootLevels'));
            if (strpos($strElementIds, ',') !== false) {
                $objSelect->where('globals.id IN (' . $strElementIds . ')');
            } else {
                $objSelect->where('globals.id = ?', (int) $strElementIds);
            }
        }

        return $this->objGlobalTable->fetchAll($objSelect);
    }



    /**
     * deleteDatetimes
     * @param $strElementId
     * @param $intVersion
     * @param $intFieldId
     * @return int
     * @author Alexander Schranz <alexander.schranz@massiveart.com>
     * @version 1.0
     */
    public function deleteDatetimes($strElementId, $intVersion, $intFieldId)
    {
        $this->core->logger->debug('global->models->Model_Globals->deleteDatetimes(' . $strElementId . ',' . $intVersion . ',' . $intFieldId . ')');

        $strWhere = $this->getGlobalDatetimesTable()->getAdapter()->quoteInto('globalId = ?', $strElementId);
        $strWhere .= $this->objGlobalDatetimesTable->getAdapter()->quoteInto(' AND version = ?', $intVersion);
        $strWhere .= $this->objGlobalDatetimesTable->getAdapter()->quoteInto(' AND idFields = ?', $intFieldId);
        $strWhere .= $this->objGlobalDatetimesTable->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);

        return $this->objGlobalDatetimesTable->delete($strWhere);
    }

    /**
     * loadDatetimes
     * @param string $strElementId
     * @param integer $intVersion
     * @param integer $intFieldId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadDatetimes($strElementId, $intVersion, $intFieldId)
    {
        $this->core->logger->debug('global->models->Model_Globals->loadDatetimes(' . $strElementId . ',' . $intVersion . ',' . $intFieldId . ')');

        $objSelect = $this->getGlobalDatetimesTable()->select();

        $objSelect->from('globalDates', array('from_date', 'from_time', 'to_date', 'to_time', 'fulltime', 'repeat', 'repeat_frequency', 'repeat_interval', 'repeat_type', 'end', 'end_date'))
            ->where('globalId = ?', $strElementId)
            ->where('version = ?', $intVersion)
            ->where('idLanguages = ?', $this->intLanguageId)
            ->where('idFields = ?', $intFieldId);

        return $this->objGlobalDatetimesTable->fetchRow($objSelect);
    }

    /**
     * getPagesDatetimesTable
     * @return Zend_Db_Table_Abstract
     * @author Alexander Schranz <alexander.schranz@massiveart.com>
     * @version 1.0
     */
    public function getGlobalDatetimesTable()
    {
        if ($this->objGlobalDatetimesTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/tables/GlobalDates.php';
            $this->objGlobalDatetimesTable = new Model_Table_GlobalDates();
        }

        return $this->objGlobalDatetimesTable;
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
     * getGlobalTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getGlobalTable()
    {

        if ($this->objGlobalTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/tables/Globals.php';
            $this->objGlobalTable = new Model_Table_Globals();
        }

        return $this->objGlobalTable;
    }

    /**
     * getGlobalPropertyTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getGlobalPropertyTable()
    {

        if ($this->objGlobalPropertyTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/tables/GlobalProperties.php';
            $this->objGlobalPropertyTable = new Model_Table_GlobalProperties();
        }

        return $this->objGlobalPropertyTable;
    }

    /**
     * getGlobalUrlTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getGlobalUrlTable()
    {

        if ($this->objGlobalUrlTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/Urls.php';
            $this->objGlobalUrlTable = new Model_Table_Urls();
        }

        return $this->objGlobalUrlTable;
    }

    /**
     * getGlobalLinkTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getGlobalLinkTable()
    {

        if ($this->objGlobalLinkTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/tables/GlobalLinks.php';
            $this->objGlobalLinkTable = new Model_Table_GlobalLinks();
        }

        return $this->objGlobalLinkTable;
    }

    /**
     * getGlobalInternalLinkTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getGlobalInternalLinkTable()
    {

        if ($this->objGlobalInternalLinkTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/tables/GlobalInternalLinks.php';
            $this->objGlobalInternalLinkTable = new Model_Table_GlobalInternalLinks();
        }

        return $this->objGlobalInternalLinkTable;
    }

    /**
     * getGlobalArticleTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getGlobalArticleTable()
    {
        if ($this->objGlobalArticleTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/tables/GlobalArticles.php';
            $this->objGlobalArticleTable = new Model_Table_GlobalArticles();
        }

        return $this->objGlobalArticleTable;
    }

    /**
     * getGlobalVideoTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getGlobalVideoTable()
    {

        if ($this->objGlobalVideoTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/tables/GlobalVideos.php';
            $this->objGlobalVideoTable = new Model_Table_GlobalVideos();
        }

        return $this->objGlobalVideoTable;
    }

    /**
     * getGlobalContactsTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getGlobalContactsTable()
    {

        if ($this->objGlobalContactsTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/tables/GlobalContacts.php';
            $this->objGlobalContactsTable = new Model_Table_GlobalContacts();
        }

        return $this->objGlobalContactsTable;
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
        require_once(GLOBAL_ROOT_PATH . 'application/plugins/' . $type . '/data/models/Global' . $type . '.php');
        $strClass = 'Model_Table_Global' . $type;
        return new $strClass();
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
