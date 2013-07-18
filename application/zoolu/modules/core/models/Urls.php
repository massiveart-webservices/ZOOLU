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
 * Model_Urls
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-12-04: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Model_Urls
{

    private $intLanguageId;

    /**
     * @var Model_Table_Urls
     */
    protected $objUrlTable;

    /**
     * @var Model_Utilities
     */
    protected $objModelUtilities;

    /**
     * @var Model_RootLevels
     */
    protected $objModelRootLevels;

    /**
     * @var Model_Table_RootLevelUrls
     */
    protected $objRootLevelUrlTable;


    protected $objPathReplacers;

    /**
     * @var Core
     */
    private $core;

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
     * loadUrl
     * @param string $strRelationId
     * @param integer $intVersion
     * @param integer $intUrlTypeId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadUrl($strRelationId, $intVersion, $intUrlTypeId, $blnLandingPage = false)
    {
        $this->core->logger->debug('core->models->Model_Urls->loadUrl(' . $strRelationId . ', ' . $intVersion . ', ' . $intUrlTypeId . ')');

        $objSelect = $this->getUrlTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from($this->objUrlTable, array('url'));
        $objSelect->join('languages', 'languages.id = urls.idLanguages', array('languageCode'));
        $objSelect->where('urls.relationId = ?', $strRelationId)
            ->where('urls.version = ?', $intVersion)
            ->where('urls.idUrlTypes = ?', $intUrlTypeId)
            ->where('urls.idLanguages = ?', $this->intLanguageId)
            ->where('urls.isMain = 1')
            ->where('urls.isLandingPage = ?', (int) $blnLandingPage)
            ->where('urls.idParent IS NULL');

        return $this->objUrlTable->fetchAll($objSelect);
    }

    /**
     * loadUrlById
     * @param integer $intUrlId
     */
    public function loadUrlById($intUrlId)
    {
        $this->core->logger->debug('core->models->Model_Urls->loadUrl(' . $intUrlId . ')');

        $objSelect = $this->getUrlTable()->select()->setIntegrityCheck(false);

        $objSelect->from('urls')
            ->where('urls.id = ?', $intUrlId);

        return $this->objUrlTable->fetchRow($objSelect);
    }

    /**
     * loadUrls
     * @param string $strRelationId
     * @param integer $intVersion
     * @param string $strUrlType
     * @param integer $intElementType
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadUrls($strRelationId, $intVersion, $strUrlType, $intElementType)
    {
        $this->core->logger->debug('core->models->Model_Urls->loadUrls(' . $strRelationId . ', ' . $intVersion . ', ' . $strUrlType . ', ' . $intElementType . ')');

        $objSelect = $this->getUrlTable()->select();
        $objSelect->setIntegrityCheck(false);

        $strDbTable = $strUrlType . 'Properties';
        $strRelationField = $strUrlType . 'Id';

        $objSelect->from($this->objUrlTable, array('idLanguages', 'url', 'idLanguages'));
        if ($strUrlType == 'global' && ($intElementType == $this->core->sysConfig->global_types->product->id || $intElementType == $this->core->sysConfig->global_types->product_overview->id)) {
            $objSelect->join(array('lG' => 'globals'), 'lG.globalId = urls.relationId AND lG.version = urls.version', array());
            $objSelect->join('globalLinks', 'globalLinks.idGlobals = lG.id', array());
            $objSelect->join('globals', 'globals.globalId = globalLinks.globalId', array());
            $objSelect->join('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = urls.idLanguages', array('idStatus'));
            $objSelect->where('globals.id = (SELECT p.id FROM globals p WHERE p.globalId = globals.globalId ORDER BY p.version DESC LIMIT 1)');
        } else {
            $objSelect->join($strDbTable, "$strDbTable.$strRelationField = urls.relationId AND $strDbTable.version = urls.version AND $strDbTable.idLanguages = urls.idLanguages", array('idStatus'));
        }
        $objSelect->join('languages', 'languages.id = urls.idLanguages', array('languageCode'));
        $objSelect->where('urls.relationId = ?', $strRelationId)
            ->where('urls.version = ?', $intVersion)
            ->where('urls.isMain = 1')
            ->where('urls.idParent IS NULL');

        return $this->core->dbh->query($objSelect->assemble())->fetchAll(Zend_Db::FETCH_OBJ | Zend_Db::FETCH_GROUP);
    }

    /**
     * loadDomainSettings
     * @param string $strDomain
     * @return string
     */
    public function loadDomainSettings($strDomain, $intEnvironment = null) {
        $this->core->logger->debug('core->models->Model_Urls->loadDomainSettings(' . $strDomain . ')');

        $strAppEnv = APPLICATION_ENV;
        $intEnvironment = ($intEnvironment == null) ? $this->core->sysConfig->environments->$strAppEnv : $intEnvironment;

        $objSelect = $this->getRootLevelUrlTable()->select(array('hostPrefix', 'idLanguages'));
        $objSelect->setIntegrityCheck(false);
        $objSelect->where('url = ?', $strDomain);
        $objSelect->where('idEnvironments = ?', $intEnvironment);
        $objSelect->limit(1);

        $objResult = $this->getRootLevelUrlTable()->fetchAll($objSelect);
        return $objResult->current();
    }

    /**
     * Loads all the URLs for a specific RootLevel
     * @param integer $intRootLevelId
     * @param string $strUrl
     * @return stdClass
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadUrlsByRootLevelForSitemapList($intRootLevelId, $blnLandingPage = false, $blnReturnSelect = false, $strOrderColumn = '', $strSortOrder = 'asc', $strSearchValue = '')
    {
        $this->core->logger->debug('core->models->Model_Urls->loadUrlsByRootLevelForSitemapList('.$intRootLevelId.', '.$blnLandingPage.')');

        $objFolderPageSelect = $this->core->dbh->select();
        $objFolderPageSelect->from('urls', array('id', 'url', 'targetpage' => new Zend_Db_Expr('IFNULL(pageTitles.title, alternativeTitle.title)'), 'targetlanguage' => 'languages.title', 'redirect' => new Zend_Db_Expr('IF(isMain = 1, "'.$this->core->translate->_('deactivated').'", "'.$this->core->translate->_('active').'")'), 'changeUser' => 'CONCAT(users.fname, \' \', users.sname)', 'created', 'changed'));
        $objFolderPageSelect->join('languages', 'languages.id = urls.idLanguages', array());
        $objFolderPageSelect->join('users', 'users.id = urls.idUsers', array());
        $objFolderPageSelect->join('pages', 'pages.pageId = urls.relationId AND pages.version = urls.version AND pages.idParentTypes = '.$this->core->sysConfig->parent_types->folder, array());
        $objFolderPageSelect->join('folders', 'folders.id = pages.idParent', array());
        $objFolderPageSelect->joinLeft('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = urls.idLanguages', array());
        $objFolderPageSelect->joinLeft(array('alternativeTitle' => 'pageTitles'), 'alternativeTitle.pageId = pages.pageId AND alternativeTitle.version = pages.version AND alternativeTitle.idLanguages = 0', array());
        $objFolderPageSelect->where('folders.idRootLevels = ?', $intRootLevelId);
        $objFolderPageSelect->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->page);
        $objFolderPageSelect->where('urls.isLandingPage = ?', (int) $blnLandingPage);
        if($strSearchValue != ''){
            $objFolderPageSelect->where('IFNULL(pageTitles.title, alternativeTitle.title) LIKE ? OR url LIKE ? OR languages.title LIKE ? OR CONCAT(users.fname, \' \', users.sname) LIKE ?', '%'.$strSearchValue.'%');
        }

        $objRootLevelPageSelect = $this->core->dbh->select();
        $objRootLevelPageSelect->from('urls', array('id','url', 'targetpage' => 'pageTitles.title', 'targetlanguage' => 'languages.title', 'redirect' => new Zend_Db_Expr('IF(isMain = 1, "'.$this->core->translate->_('deactivated').'", "'.$this->core->translate->_('active').'")'), 'changeUser' => 'CONCAT(users.fname, \' \', users.sname)', 'created', 'changed'));
        $objRootLevelPageSelect->join('languages', 'languages.id = urls.idLanguages', array());
        $objRootLevelPageSelect->join('users', 'users.id = urls.idUsers', array());
        $objRootLevelPageSelect->join('pages', 'pages.pageId = urls.relationId AND pages.version = urls.version AND pages.idParentTypes = '.$this->core->sysConfig->parent_types->rootlevel, array());
        $objRootLevelPageSelect->join('rootLevels', ' rootLevels.id = pages.idParent', array());
        $objRootLevelPageSelect->join('pageTitles', 'pageTitles.pageId = pages.pageId AND pageTitles.version = pages.version AND pageTitles.idLanguages = '.$this->intLanguageId, array());
        $objRootLevelPageSelect->where('rootLevels.id = ?', $intRootLevelId);
        $objRootLevelPageSelect->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->page);
        $objRootLevelPageSelect->where('urls.isLandingPage = ?', (int) $blnLandingPage);
        if($strSearchValue != ''){
            $objRootLevelPageSelect->where('pageTitles.title LIKE ? OR url LIKE ? OR languages.title LIKE ? OR CONCAT(users.fname, \' \', users.sname) LIKE ?', '%'.$strSearchValue.'%');
        }

        $objGlobalSelect = $this->core->dbh->select();
        $objGlobalSelect->from('urls', array('id', 'url', 'targetpage' => new Zend_Db_Expr('IFNULL(globalTitles.title, alternativeTitle.title)'), 'targetlanguage' => 'languages.title', 'redirect' => new Zend_Db_Expr('IF(isMain = 1, "'.$this->core->translate->_('deactivated').'", "'.$this->core->translate->_('active').'")'), 'changeUser' => 'CONCAT(users.fname, \' \', users.sname)', 'created', 'changed'));
        $objGlobalSelect->join('languages', 'languages.id = urls.idLanguages', array());
        $objGlobalSelect->join('users', 'users.id = urls.idUsers', array());
        $objGlobalSelect->join('globals', 'globals.globalId = urls.relationId AND globals.version = urls.version', array());
        $objGlobalSelect->join('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = urls.idLanguages', array());
        $objGlobalSelect->joinLeft('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = '.$this->intLanguageId, array());
        $objGlobalSelect->joinLeft(array('alternativeTitle' => 'globalTitles'), 'alternativeTitle.globalId = globals.globalId AND alternativeTitle.version = globals.version AND alternativeTitle.idLanguages = 0', array());
        $objGlobalSelect->joinLeft(array('parentFolder' => 'folders'), 'parentFolder.id = urls.idParent AND urls.idParentTypes = '.$this->core->sysConfig->parent_types->folder, array());
        $objGlobalSelect->joinLeft(array('parentRootLevel' => 'rootLevels'), 'parentRootLevel.id = urls.idParent AND urls.idParentTypes = '.$this->core->sysConfig->parent_types->rootlevel, array());
        $objGlobalSelect->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->global);
        //$objGlobalSelect->where('globals.id = (SELECT p.id FROM globals p WHERE p.globalId = globals.globalId ORDER BY p.version DESC LIMIT 1)');
        $objGlobalSelect->where('urls.isLandingPage = ?', (int) $blnLandingPage);
        $objGlobalSelect->where('parentFolder.idRootLevels = '.$intRootLevelId.' OR parentRootLevel.id = '.$intRootLevelId);
        if($strSearchValue != ''){
            $objGlobalSelect->where('IFNULL(globalTitles.title, alternativeTitle.title) LIKE ? OR url LIKE ? OR languages.title LIKE ? OR CONCAT(users.fname, \' \', users.sname) LIKE ?', '%'.$strSearchValue.'%');
        }

        $objGlobalLinksSelect = $this->core->dbh->select();
        $objGlobalLinksSelect->from('urls', array('id', 'url', 'targetpage' => new Zend_Db_Expr('IFNULL(globalTitles.title, alternativeTitle.title)'), 'targetlanguage' => 'languages.title', 'redirect' => new Zend_Db_Expr('IF(isMain = 1, "'.$this->core->translate->_('deactivated').'", "'.$this->core->translate->_('active').'")'), 'changeUser' => 'CONCAT(users.fname, \' \', users.sname)', 'created', 'changed'));
        $objGlobalLinksSelect->join('languages', 'languages.id = urls.idLanguages', array());
        $objGlobalLinksSelect->join('users', 'users.id = urls.idUsers', array());
        $objGlobalLinksSelect->join(array('lG' => 'globals'), 'lG.globalId = urls.relationId AND lG.version = urls.version', array());
        $objGlobalLinksSelect->join('globalLinks', 'globalLinks.idGlobals = lG.id', array());
        $objGlobalLinksSelect->join('globals', 'globals.globalId = globalLinks.globalId', array());
        $objGlobalLinksSelect->joinLeft('globalTitles', 'globalTitles.globalId = globals.globalId AND globalTitles.version = globals.version AND globalTitles.idLanguages = '.$this->intLanguageId, array());
        $objGlobalLinksSelect->joinLeft(array('alternativeTitle' => 'globalTitles'), 'alternativeTitle.globalId = globals.globalId AND alternativeTitle.version = globals.version AND alternativeTitle.idLanguages = 0', array());
        $objGlobalLinksSelect->joinLeft('folders', 'folders.id = urls.idParent AND urls.idParentTypes = '.$this->core->sysConfig->parent_types->folder, array());
        $objGlobalLinksSelect->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->global);
        $objGlobalLinksSelect->where('folders.idRootLevels = ?', $intRootLevelId);
        //$objGlobalLinksSelect->where('globals.id = (SELECT p.id FROM globals p WHERE p.globalId = globals.globalId ORDER BY p.version DESC LIMIT 1)');
        $objGlobalLinksSelect->where('urls.isLandingPage = ?', (int) $blnLandingPage);
        if($strSearchValue != ''){
            $objGlobalLinksSelect->where('IFNULL(globalTitles.title, alternativeTitle.title) LIKE ? OR url LIKE ? OR languages.title LIKE ? OR CONCAT(users.fname, \' \', users.sname) LIKE ?', '%'.$strSearchValue.'%');
        }

        $objExternalSelect = $this->core->dbh->select();
        $objExternalSelect->from('urls', array('id', 'url', 'targetpage' => 'external', 'targetlanguage' => 'languages.title', 'redirect' => new Zend_Db_Expr('IF(isMain = 1, "'.$this->core->translate->_('deactivated').'", "'.$this->core->translate->_('active').'")'), 'changeUser' => 'CONCAT(users.fname, \' \', users.sname)', 'created', 'changed'));
        $objExternalSelect->join('languages', 'languages.id = urls.idLanguages', array());
        $objExternalSelect->join('users', 'users.id = urls.idUsers', array());
        $objExternalSelect->where('urls.idParent = ?', $intRootLevelId);
        $objExternalSelect->where('urls.idParentTypes = ?', $this->core->sysConfig->parent_types->rootlevel);
        $objExternalSelect->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->external);
        $objExternalSelect->where('urls.isLandingPage = ?', (int) $blnLandingPage);
        if($strSearchValue != ''){
            $objExternalSelect->where('external LIKE ? OR url LIKE ? OR languages.title LIKE ? OR CONCAT(users.fname, \' \', users.sname) LIKE ?', '%'.$strSearchValue.'%');
        }

        $objSelect = $this->getUrlTable()->select()
            ->union(array($objFolderPageSelect, $objRootLevelPageSelect, $objGlobalSelect, $objGlobalLinksSelect, $objExternalSelect));
        if($strOrderColumn != '') {
            $objSelect->order($strOrderColumn.' '.$strSortOrder);
        }

        if ($blnReturnSelect) {
            return $objSelect;
        } else {
            return $this->objUrlTable->fetchAll($objSelect);
        }
    }

    /**
     * loadByUrl
     * @param integer $intRootLevelId
     * @param string $strUrl
     * @return stdClass
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadByUrl($intRootLevelId, $strUrl, $strType = null, $blnLandingPage = false, $blnLanguage = true)
    {
        $this->core->logger->debug('core->models->Model_Urls->loadByUrl(' . $intRootLevelId . ', ' . $strUrl . ', ' . $blnLandingPage . ')');
        $objUrlData = new stdClass();

        $objFolderPageSelect = $this->core->dbh->select();
        $objFolderPageSelect->from('urls', array('relationId' => 'pages.pageId', 'pages.version', 'urls.idLanguages', 'urls.isMain', 'urls.idParent', 'urls.idParentTypes', 'urls.idUrlTypes', 'urls.isLandingPage', 'urls.external', 'idLink' => new Zend_Db_Expr('-1'), 'linkId' => new Zend_Db_Expr('NULL'), 'idLinkParent' => new Zend_Db_Expr('-1')));
        $objFolderPageSelect->join('pages', 'pages.pageId = urls.relationId AND pages.version = urls.version AND pages.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array());
        $objFolderPageSelect->join('folders', 'folders.id = pages.idParent', array());
        $objFolderPageSelect->where('urls.url = ?', $strUrl)
            ->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->page);
        if ($blnLanguage) {
            $objFolderPageSelect->where('urls.idLanguages = ?', $this->intLanguageId);
        }
        if ($strType != 'global') {
            $objFolderPageSelect->where('folders.idRootLevels = ?', $intRootLevelId);
        }
        $objFolderPageSelect->where('urls.isLandingPage = ?', (int) $blnLandingPage);


        $objRootLevelPageSelect = $this->core->dbh->select();
        $objRootLevelPageSelect->from('urls', array('relationId' => 'pages.pageId', 'pages.version', 'urls.idLanguages', 'urls.isMain', 'urls.idParent', 'urls.idParentTypes', 'urls.idUrlTypes', 'urls.isLandingPage', 'urls.external', 'idLink' => new Zend_Db_Expr('-1'), 'linkId' => new Zend_Db_Expr('NULL'), 'idLinkParent' => new Zend_Db_Expr('-1')));
        $objRootLevelPageSelect->join('pages', 'pages.pageId = urls.relationId AND pages.version = urls.version AND pages.idParentTypes = ' . $this->core->sysConfig->parent_types->rootlevel, array());
        $objRootLevelPageSelect->join('rootLevels', ' rootLevels.id = pages.idParent', array());
        $objRootLevelPageSelect->where('urls.url = ?', $strUrl)
            ->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->page);
        if ($blnLanguage) {
            $objRootLevelPageSelect->where('urls.idLanguages = ?', $this->intLanguageId);
        }
        if ($strType != 'global') {
            $objRootLevelPageSelect->where('rootLevels.id = ?', $intRootLevelId);
        }
        $objRootLevelPageSelect->where('urls.isLandingPage = ?', (int) $blnLandingPage);

        $objRootLevelPageSelect = $this->core->dbh->select();
        $objRootLevelPageSelect->from('urls', array('relationId' => 'pages.pageId', 'pages.version', 'urls.idLanguages', 'urls.isMain', 'urls.idParent', 'urls.idParentTypes', 'urls.idUrlTypes', 'urls.isLandingPage', 'urls.external', 'idLink' => new Zend_Db_Expr('-1'), 'linkId' => new Zend_Db_Expr('NULL'), 'idLinkParent' => new Zend_Db_Expr('-1')));
        $objRootLevelPageSelect->joinLeft('pages', 'pages.pageId = urls.relationId AND pages.version = urls.version AND pages.idParentTypes = '.$this->core->sysConfig->parent_types->rootlevel, array());
        $objRootLevelPageSelect->join('rootLevels', ' rootLevels.id = pages.idParent OR (rootLevels.id = urls.idParent AND urls.idParentTypes = '.$this->core->sysConfig->parent_types->rootlevel.')', array());
        $objRootLevelPageSelect->where('urls.url = ?', $strUrl)
        				       ->where('urls.idUrlTypes IN ('.$this->core->sysConfig->url_types->page.','.$this->core->sysConfig->url_types->external.')');
        if($blnLanguage){
          $objRootLevelPageSelect->where('urls.idLanguages = ?', $this->intLanguageId);
        }
        if($strType != 'global'){
        	  $objRootLevelPageSelect->where('rootLevels.id = ?', $intRootLevelId);
        }
        $objRootLevelPageSelect->where('urls.isLandingPage = ?', (int) $blnLandingPage);

//     $objExternalPageSelect = $this->core->dbh->select();
//     $objExternalPageSelect->from('urls', array('relationId' => new Zend_Db_Expr('""'), 'version' => new Zend_Db_Expr('"1"'), 'urls.idLanguages', 'urls.isMain', 'urls.idParent', 'urls.idParentTypes', 'urls.idUrlTypes', 'urls.isLandingPage', 'urls.external', 'idLink' => new Zend_Db_Expr('-1'), 'linkId' => new Zend_Db_Expr('NULL'), 'idLinkParent' => new Zend_Db_Expr('-1')));
//     $objExternalPageSelect->where('urls.url = ?', $strUrl)
//     ->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->external);
//     if($blnLanguage){
//         $objExternalPageSelect->where('urls.idLanguages = ?', $this->intLanguageId);
//     }
//     if($strType != 'global'){
//         $objExternalPageSelect->where('urls.idParent = ?', $intRootLevelId);
//         $objExternalPageSelect->where('urls.idParentTypes = ?', $this->core->sysConfig->parent_types->rootlevel);
//     }
//     $objExternalPageSelect->where('urls.isLandingPage = ?', (int) $blnLandingPage);

        $objGlobalSelect = $this->core->dbh->select();
        $objGlobalSelect->from('urls', array('relationId' => 'globals.globalId', 'globals.version', 'urls.idLanguages', 'urls.isMain', 'urls.idParent', 'urls.idParentTypes', 'urls.idUrlTypes', 'urls.isLandingPage', 'urls.external', 'idLink' => new Zend_Db_Expr('-1'), 'linkId' => new Zend_Db_Expr('NULL'), 'idLinkParent' => new Zend_Db_Expr('-1')));
        $objGlobalSelect->join('globals', 'globals.globalId = urls.relationId AND globals.version = urls.version', array());
        $objGlobalSelect->join('globalProperties', 'globalProperties.globalId = globals.globalId AND globalProperties.version = globals.version AND globalProperties.idLanguages = urls.idLanguages', array());
        $objGlobalSelect->where('urls.url = ?', $strUrl)
                         ->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->global)
                         ->where('globals.id = (SELECT p.id FROM globals p WHERE p.globalId = globals.globalId ORDER BY p.version DESC LIMIT 1)');
        if($blnLanguage){
            $objGlobalSelect->joinLeft('folders', 'folders.id = urls.idParent AND urls.idParentTypes = '.$this->core->sysConfig->parent_types->folder, array());
        }
        $objGlobalSelect->where('urls.isLandingPage = ?', (int) $blnLandingPage);

        $objGlobalLinksSelect = $this->core->dbh->select();
        $objGlobalLinksSelect->from('urls', array('relationId' => 'globals.globalId', 'globals.version', 'urls.idLanguages', 'urls.isMain', 'urls.idParent', 'urls.idParentTypes', 'urls.idUrlTypes', 'urls.isLandingPage', 'urls.external', 'idLink' => 'lG.id', 'linkId' => 'lG.globalId', 'idLinkParent' => 'lG.idParent'));
        $objGlobalLinksSelect->join(array('lG' => 'globals'), 'lG.globalId = urls.relationId AND lG.version = urls.version', array());
        $objGlobalLinksSelect->join('globalLinks', 'globalLinks.idGlobals = lG.id', array());
        $objGlobalLinksSelect->join('globals', 'globals.globalId = globalLinks.globalId', array());
        $objGlobalLinksSelect->where('urls.url = ?', $strUrl)
                             ->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->global)
                             ->where('globals.id = (SELECT p.id FROM globals p WHERE p.globalId = globals.globalId ORDER BY p.version DESC LIMIT 1)');
        if($blnLanguage){
            $objGlobalLinksSelect->where('urls.idLanguages = ?', $this->intLanguageId);
        }
        if($blnLandingPage){
            $objGlobalLinksSelect->joinLeft('folders', 'folders.id = urls.idParent AND urls.idParentTypes = '.$this->core->sysConfig->parent_types->folder, array());
            $objGlobalLinksSelect->where('folders.idRootLevels = ?', $intRootLevelId);
        }
        $objGlobalLinksSelect->where('urls.isLandingPage = ?', (int) $blnLandingPage);

        $objSelect = $this->getUrlTable()->select()
                                         ->union(array($objFolderPageSelect, $objRootLevelPageSelect, $objGlobalSelect, $objGlobalLinksSelect));

        $objUrlData->url = $this->objUrlTable->fetchAll($objSelect);

        /**
         * check if url is global of a linkde global tree
         */
        if (count($objUrlData->url) == 0) {
            $objGlobalTreeBaseUrls = $this->loadGlobalTreeBaseUrls($intRootLevelId);
            foreach ($objGlobalTreeBaseUrls as $objBaseUrl) {
                if (strpos($strUrl, $objBaseUrl->url) === 0) {
                    $objUrlData->url = $this->loadGlobalByUrl(str_replace('|' . $objBaseUrl->url, '', '|' . $strUrl), $objBaseUrl->idPageTypes);
                    $objUrlData->baseUrl = $objBaseUrl;
                    break;
                }
            }
        }

        return $objUrlData;
    }

    /**
     * loadGlobalTreeBaseUrls
     * @param integer $intRootLevelId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function loadGlobalTreeBaseUrls($intRootLevelId)
    {
        $this->core->logger->debug('core->models->Model_Urls->loadGlobalTreeBaseUrls(' . $intRootLevelId . ')');

        $objSelect = $this->getUrlTable()->select();
        $objSelect->setIntegrityCheck(false);

        $objSelect->from('urls', array('url', 'relationId' => 'pages.pageId', 'pages.version', 'urls.idLanguages', 'urls.isMain', 'urls.idParent', 'urls.idParentTypes', 'urls.idUrlTypes', 'idLink' => new Zend_Db_Expr('-1')));
        $objSelect->join('pages', 'pages.pageId = urls.relationId AND pages.version = urls.version AND pages.idParentTypes = ' . $this->core->sysConfig->parent_types->folder, array());
        $objSelect->join('pageProperties', 'pageProperties.pageId = pages.pageId AND pageProperties.version = pages.version AND pageProperties.idLanguages = ' . $this->core->dbh->quote($this->intLanguageId, Zend_Db::INT_TYPE), array('pageProperties.idPageTypes'));
        $objSelect->join('folders', 'folders.id = pages.idParent', array());
        $objSelect->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->page)
            ->where('urls.idLanguages = ?', $this->intLanguageId)
            ->where('folders.idRootLevels = ?', $intRootLevelId)
            ->where('pageProperties.idPageTypes IN (' . $this->core->sysConfig->page_types->product_tree->id . ', ' . $this->core->sysConfig->page_types->press_area->id . ', ' . $this->core->sysConfig->page_types->courses->id . ', ' . $this->core->sysConfig->page_types->events->id . ')');

        return $this->objUrlTable->fetchAll($objSelect);
    }

    /**
     * loadGlobalByUrl
     * @param string $strUrl
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function loadGlobalByUrl($strUrl, $intPageTypeId)
    {
        $this->core->logger->debug('core->models->Model_Urls->loadGlobalByUrl(' . $strUrl . ',' . $intPageTypeId . ')');

        $objSelect = $this->getUrlTable()->select();
        $objSelect->setIntegrityCheck(false);

        if ($intPageTypeId == $this->core->sysConfig->page_types->product_tree->id) {
            $objSelect->from('urls', array('relationId' => 'globals.globalId', 'globals.version', 'urls.idLanguages', 'urls.isLandingPage', 'urls.isMain', 'urls.idParent', 'urls.idParentTypes', 'urls.idUrlTypes', 'idLink' => 'lG.id', 'linkId' => 'lG.globalId', 'idLinkParent' => 'lG.idParent'));
            $objSelect->join(array('lG' => 'globals'), 'lG.globalId = urls.relationId AND lG.version = urls.version', array());
            $objSelect->join('globalLinks', 'globalLinks.idGlobals = lG.id', array());
            $objSelect->join('globals', 'globals.globalId = globalLinks.globalId', array())
                ->where('globals.id = (SELECT p.id FROM globals p WHERE p.globalId = globals.globalId ORDER BY p.version DESC LIMIT 1)');
            ;
        } else {
            $objSelect->from('urls', array('relationId' => 'globals.globalId', 'globals.version', 'urls.idLanguages', 'urls.isLandingPage', 'urls.isMain', 'urls.idParent', 'urls.idParentTypes', 'urls.idUrlTypes', 'idLink' => new Zend_Db_Expr('-1'), 'linkId' => new Zend_Db_Expr('NULL'), 'idLinkParent' => new Zend_Db_Expr('-1')));
            $objSelect->join('globals', 'globals.globalId = urls.relationId AND globals.version  = urls.version', array());
        }
        $objSelect->where('urls.url = ?', $strUrl)
            ->where('urls.idUrlTypes = ?', $this->core->sysConfig->url_types->global)
            ->where('urls.idLanguages = ?', $this->intLanguageId);


        return $this->objUrlTable->fetchAll($objSelect);
    }

    /**
     * Loads the urls (hopefully only one) with the given url and rootlevel
     * @param string $strUrl
     * @param integere $intRootLevelId
     */
    public function loadUrlByUrlAndRootLevel($strUrl, $intRootLevelId, $blnLandingPage = false)
    {
        $this->core->logger->debug('core->models->Model_Urls->loadUrlByUrlAndRootLevel(' . $strUrl . ', ' . $intRootLevelId . ', ' . $blnLandingPage . ')');

        $objPageSelect = $this->getUrlTable()->select()->setIntegrityCheck(false);
        $objPageSelect->from('urls', array('id'))
            ->joinLeft('pages', 'pages.pageId = urls.relationId', array())
            ->joinLeft(array('pageFolders' => 'folders'), 'pageFolders.id = pages.idParent', array())
            ->joinLeft('globals', 'globals.globalId = urls.relationId', array())
            ->joinLeft(array('globalParentFolder' => 'folders'), 'globalParentFolder.id = urls.idParent AND urls.idParentTypes = '.$this->core->sysConfig->parent_types->folder, array())
            ->joinLeft(array('globalRootLevel' => 'rootLevels'), 'globalRootLevel.id = globalParentFolder.idRootLevels', array())
            ->joinLeft('rootLevels', 'rootLevels.id = urls.idParent AND urls.idParentTypes = '.$this->core->sysConfig->parent_types->rootlevel, array())
            ->where('urls.url = ?', $strUrl)
            ->where('urls.isLandingPage = ?', (int) $blnLandingPage)
            ->where('pageFolders.idRootLevels = ' . $intRootLevelId . ' OR globalRootLevel.id = ' . $intRootLevelId . ' OR rootLevels.id = ' . $intRootLevelId . ' OR globalRootLevel.id = ' . $intRootLevelId);

        return $this->getUrlTable()->fetchAll($objPageSelect);
    }

    /**
     * insertUrl
     * @param string $strUrl
     * @param string $strRelationId
     * @param integer $intVersion
     * @param integer $intUrlTypeId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function insertUrl($strUrl, $strRelationId, $intVersion, $intUrlTypeId, $blnLandingPage = false)
    {
        $this->core->logger->debug('core->models->Model_Urls->insertUrl(' . $strUrl . ', ' . $strRelationId . ', ' . $intVersion . ', ' . $intUrlTypeId . ')');

        $objAuth = Zend_Auth::getInstance();
        $objAuth->setStorage(new Zend_Auth_Storage_Session('zoolu'));
        $intUserId = $objAuth->getIdentity()->id;

        $arrDataInsert = array(
            'relationId'   => $strRelationId,
            'version'      => $intVersion,
            'idUrlTypes'   => $intUrlTypeId,
            'isMain'       => '1',
            'isLandingPage'=> (int) $blnLandingPage,
            'idLanguages'  => $this->intLanguageId,
            'url'          => $strUrl,
            'idUsers'      => $intUserId,
            'creator'      => $intUserId,
            'created'      => date('Y-m-d H:i:s')
        );

        return $objSelect = $this->getUrlTable()->insert($arrDataInsert);
    }

    /**
     * resetIsMainUrl
     * @param string $strRelationId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function resetIsMainUrl($strRelationId, $intVersion, $intUrlTypeId)
    {
        $this->core->logger->debug('core->models->Model_Urls->resetIsMainUrl(' . $strRelationId . ', ' . $intVersion . ', ' . $intUrlTypeId . ')');

        $arrDataUpdate = array('isMain' => 0);

        $strWhere = $this->getUrlTable()->getAdapter()->quoteInto('relationId = ?', $strRelationId);
        $strWhere .= $this->getUrlTable()->getAdapter()->quoteInto(' AND version = ?', $intVersion);
        $strWhere .= $this->getUrlTable()->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);
        $strWhere .= $this->getUrlTable()->getAdapter()->quoteInto(' AND idUrlTypes = ?', $intUrlTypeId);

        return $this->getUrlTable()->update($arrDataUpdate, $strWhere);
    }

    /**
     * removeUrlHistory
     * @param string $strRelationId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function removeUrlHistory($strRelationId, $intVersion, $intUrlTypeId)
    {
        $this->core->logger->debug('core->models->Model_Urls->removeUrlHistoryEntry(' . $strRelationId . ', ' . $intVersion . ',' . $intUrlTypeId . ')');

        $strWhere = $this->getUrlTable()->getAdapter()->quoteInto('relationId = ?', $strRelationId);
        $strWhere .= $this->getUrlTable()->getAdapter()->quoteInto(' AND version = ?', $intVersion);
        $strWhere .= $this->getUrlTable()->getAdapter()->quoteInto(' AND idLanguages = ?', $this->intLanguageId);
        $strWhere .= $this->getUrlTable()->getAdapter()->quoteInto(' AND idUrlTypes = ?', $intUrlTypeId);
        $strWhere .= ' AND isMain = 0';

        return $this->objUrlTable->delete($strWhere);
    }

    /**
     * removeUrlHistoryEntry
     * @param integer $intUrlId
     * @param string $strRelationId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function removeUrlHistoryEntry($intUrlId, $strRelationId)
    {
        $this->core->logger->debug('core->models->Model_Urls->removeUrlHistoryEntry(' . $intUrlId . ', ' . $strRelationId . ')');

        $strWhere = $this->getUrlTable()->getAdapter()->quoteInto('relationId = ?', $strRelationId);
        $strWhere .= $this->objUrlTable->getAdapter()->quoteInto(' AND id = ?', $intUrlId);

        return $this->objUrlTable->delete($strWhere);
    }

    /**
     * deleteUrls
     * @param string $strRelationId
     * @param boolean $blnLandingPage
     * @return number
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function deleteUrls($strRelationId, $blnLandingPage = false)
    {
        $this->core->logger->debug('core->models->Model_Urls->deleteUrls(' . $strRelationId . ', ' . $blnLandingPage . ')');

        $arrWhere = array();
        $arrWhere[] = $this->core->dbh->quoteInto('relationId = ?', $strRelationId);
        $arrWhere[] = $this->core->dbh->quoteInto('isLandingPage = ?', (int) $blnLandingPage);
        $arrWhere[] = $this->core->dbh->quoteInto('idLanguages = ?', $this->getLanguageId());

        return $this->getUrlTable()->delete($arrWhere);
    }

    /**
     * deleteUrlsbyRootLevelId
     * @param number $intRootLevelId
     * @return number
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function deleteUrlsByRootLevelId($intRootLevelId)
    {
        $this->core->logger->debug('core->models->Model_Urls->deleteUrlsByRootLevelId');

        $objRootLevels = $this->getModelRootLevels()->loadRootLevelById($intRootLevelId);
        if (count($objRootLevels) > 0) {
            $objRootLevel = $objRootLevels->current();

            $objSubSelect = $this->core->dbh->select();
            switch ($objRootLevel->idRootLevelTypes) {
                case $this->core->sysConfig->root_level_types->portals:
                    $objPageSelect = $this->core->dbh->select();
                    $objPageSelect->from('pages', array('relationId' => 'pageId'))
                        ->join('folders', 'folders.id = pages.idParent', array())
                        ->where('folders.idRootLevels = ?', $intRootLevelId)
                        ->where('idParentTypes = ?', $this->core->sysConfig->parent_types->folder);

                    $objRootPageSelect = $this->core->dbh->select();
                    $objRootPageSelect->from('pages', array('relationId' => 'pageId'))
                        ->where('idParentTypes = ?', $this->core->sysConfig->parent_types->rootlevel)
                        ->where('idParent = ?', $intRootLevelId);
                    $objSubSelect->union(array($objPageSelect, $objRootPageSelect));
                    break;
                case $this->core->sysConfig->root_level_types->global:
                    $objGlobalSelect = $this->core->dbh->select();
                    $objGlobalSelect->from('globals', array('relationId' => 'globalId'))
                        ->join('folders', 'folders.id = globals.idParent', array())
                        ->where('folders.idRootLevels = ?', $intRootLevelId)
                        ->where('idParentTypes = ?', $this->core->sysConfig->parent_types->folder);

                    $objRootGlobalSelect = $this->core->dbh->select();
                    $objRootGlobalSelect->from('globals', array('relationId' => 'globalId'))
                        ->where('idParentTypes = ?', $this->core->sysConfig->parent_types->rootlevel)
                        ->where('idParent = ?', $intRootLevelId);
                    $objSubSelect->union(array($objGlobalSelect, $objRootGlobalSelect));
                    break;
            }
            $strSelect = new Zend_Db_Expr($objSubSelect);
            return $this->getUrlTable()->delete('relationId IN (' . $strSelect . ')');
        }
    }

    /**
     * deleteUrlsByRootLevelIdAndLanguage
     * @param number $intRootLevelId
     * @param number $intLanguagesId
     * @return number
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function deleteUrlsByRootLevelIdAndLanguage($intRootLevelId, $intLanguagesId)
    {
        $this->core->logger->debug('core->models->Model_Urls->deleteUrlsByRootLevelId');

        $objRootLevels = $this->getModelRootLevels()->loadRootLevelById($intRootLevelId);
        if (count($objRootLevels) > 0) {
            $objRootLevel = $objRootLevels->current();

            $objSubSelect = $this->core->dbh->select();
            switch ($objRootLevel->idRootLevelTypes) {
                case $this->core->sysConfig->root_level_types->portals:
                    $objPageSelect = $this->core->dbh->select();
                    $objPageSelect->from('pages', array('relationId' => 'pageId'))
                        ->join('folders', 'folders.id = pages.idParent', array())
                        ->where('folders.idRootLevels = ?', $intRootLevelId)
                        ->where('idParentTypes = ?', $this->core->sysConfig->parent_types->folder);

                    $objRootPageSelect = $this->core->dbh->select();
                    $objRootPageSelect->from('pages', array('relationId' => 'pageId'))
                        ->where('idParentTypes = ?', $this->core->sysConfig->parent_types->rootlevel)
                        ->where('idParent = ?', $intRootLevelId);
                    $objSubSelect->union(array($objPageSelect, $objRootPageSelect));
                    break;
                case $this->core->sysConfig->root_level_types->global:
                    $objGlobalSelect = $this->core->dbh->select();
                    $objGlobalSelect->from('globals', array('relationId' => 'globalId'))
                        ->join('folders', 'folders.id = globals.idParent', array())
                        ->where('folders.idRootLevels = ?', $intRootLevelId)
                        ->where('idParentTypes = ?', $this->core->sysConfig->parent_types->folder);

                    $objRootGlobalSelect = $this->core->dbh->select();
                    $objRootGlobalSelect->from('globals', array('relationId' => 'globalId'))
                        ->where('idParentTypes = ?', $this->core->sysConfig->parent_types->rootlevel)
                        ->where('idParent = ?', $intRootLevelId);
                    $objSubSelect->union(array($objGlobalSelect, $objRootGlobalSelect));
                    break;
            }
            $strSelect = new Zend_Db_Expr($objSubSelect);
            return $this->getUrlTable()->delete('relationId IN (' . $strSelect . ') AND idLanguages = ' . $intLanguagesId);
        }
    }

    /**
     * Adds a new URL to the system
     * @param The data of the URL $arrData
     * @return int The id of the added row in the database
     */
    public function addUrl($arrData)
    {
        try {
            $objAuth = Zend_Auth::getInstance();
            $objAuth->setStorage(new Zend_Auth_Storage_Session('zoolu'));
            $intUserId = $objAuth->getIdentity()->id;
            $arrData['idUsers'] = $intUserId;
            $arrData['creator'] = $intUserId;
            $arrData['created'] = date('Y-m-d H:i:s');
            $arrData['changed'] = date('Y-m-d H:i:s');

            $this->getUrlTable()->insert($arrData);

            return $this->getUrlTable()->getAdapter()->lastInsertId();
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * Updates the url with the given id to the given data
     * @param int $intUrlId The id of the url to edit
     * @param array $arrData The data to write
     * @return int The number of affected rows
     */
    public function editUrl($intUrlId, $arrData)
    {
        $this->core->logger->debug('core->models->Model_Urls->editUrl(' . $intUrlId . ')');

        try {
            $strWhere = $this->getUrlTable()->getAdapter()->quoteInto('id = ?', $intUrlId);

            $objAuth = Zend_Auth::getInstance();
            $objAuth->setStorage(new Zend_Auth_Storage_Session('zoolu'));
            $intUserId = $objAuth->getIdentity()->id;
            $arrData['idUsers'] = $intUserId;
            $arrData['changed'] = date('Y-m-d H:i:s');

            return $this->getUrlTable()->update($arrData, $strWhere);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    public function deleteUrl($intUrlId)
    {
        $this->core->logger->debug('core->models->Model_Urls->deleteUrl(' . $intUrlId . ')');

        try {
            $strWhere = $this->getUrlTable()->getAdapter()->quoteInto('id = ?', $intUrlId);
            $this->getUrlTable()->delete($strWhere);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * makeUrlConform()
     * @param string $strUrlPart
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function makeUrlConform($strUrlPart)
    {

        $this->getPathReplacers();

        $strUrlPart = strtolower($strUrlPart);

        //Replace problematic characters
        if (count($this->objPathReplacers) > 0) {
            foreach ($this->objPathReplacers as $objPathReplacer) {
                $strUrlPart = str_replace($objPathReplacer->from, $objPathReplacer->to, $strUrlPart);
            }
        }

        $strUrlPart = strtolower($strUrlPart);

        //Delete problematic characters
        $strUrlPart = str_replace('%2F', '/', urlencode(preg_replace('/([^A-za-z0-9\s-_\/])/', '', $strUrlPart)));

        $strUrlPart = str_replace('+', '-', $strUrlPart);
        //Replace multiple minus with one
        $strUrlPart = preg_replace('/([-]+)/', '-', $strUrlPart);
        //Delete minus at the beginning or end
        $strUrlPart = preg_replace('/^([-])/', '', $strUrlPart);
        $strUrlPart = preg_replace('/([-])$/', '', $strUrlPart);

        return $strUrlPart;
    }

    /**
     * getPathReplacers
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    private function getPathReplacers()
    {
        if ($this->objPathReplacers === null) {
            $this->objPathReplacers = $this->getModelUtilities()->loadPathReplacers();
        }
    }

    /**
     * getModelUtilities
     * @return Model_Pages
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelUtilities()
    {
        if (null === $this->objModelUtilities) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Utilities.php';
            $this->objModelUtilities = new Model_Utilities();
            $this->objModelUtilities->setLanguageId($this->intLanguageId);
        }

        return $this->objModelUtilities;
    }

    /**
     * checks if url is unique
     * @param string url
     * @return boolean True if url is unique, otherwise false
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    protected function isUrlUnique($intRootLevelId, $strUrl)
    {
        $objUrlsData = $this->loadByUrl($intRootLevelId, $strUrl);
        return (count($objUrlsData) == 0);
    }

    /**
     * getUrlTable
     * @return Zend_Db_Table_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getUrlTable()
    {

        if ($this->objUrlTable === null) {
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/tables/Urls.php';
            $this->objUrlTable = new Model_Table_Urls();
        }

        return $this->objUrlTable;
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
     * getModelRootLevels
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelRootLevels()
    {
        if (null === $this->objModelRootLevels) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/RootLevels.php';
            $this->objModelRootLevels = new Model_RootLevels();
        }

        return $this->objModelRootLevels;
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
}

?>