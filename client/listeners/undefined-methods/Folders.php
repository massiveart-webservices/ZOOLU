<?php

/**
 * Client_Listeners_UndefinedMethods_ModelFolders
 *
 * Client specific listeners
 *
 * Version history (please keep backward compatible):
 * 1.0, 2012-09-17: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package client.listeners
 * @subpackage Client_Listeners_UndefinedMethods_ModelFolders
 */

class Client_Listeners_UndefinedMethods_ModelFolders implements UndefinedMethodListener
{

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Model_Folders
     */
    protected $subject;

    /**
     * @var array
     */
    protected static $methods = array(
        'Model_Folders::loadWebsiteRootLevelChildsWithPreviewImg' => 'loadWebsiteRootLevelChildsWithPreviewImg'
    );

    /**
     * __construct
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * @param $method
     * @param UndefinedMethod $handle
     */
    public function notify($method, UndefinedMethod $handle)
    {
        if (array_key_exists($method, self::$methods) && method_exists($this, self::$methods[$method])) {
            $this->subject = $handle->getSubject();
            $handle->setReturnValue(call_user_func_array(array($this, self::$methods[$method]), $handle->getArguments()));
        }
    }

    protected function loadWebsiteRootLevelChildsWithPreviewImg($intRootLevelId, $intDepth = 1, $intDisplayOptionId = 1, $blnLoadFilter = false, $blnLoadSitemap = false, $blnFilterDisplayEnvironment = true)
    {
        $strFolderFilter = '';
        $strPageFilter = '';
        if (!isset($_SESSION['sesTestMode']) || (isset($_SESSION['sesTestMode']) && $_SESSION['sesTestMode'] == false)) {
            $strFolderFilter = ' AND folderProperties.idStatus = ' . $this->core->sysConfig->status->live;
            $strPageFilter = ' AND pageProperties.idStatus = ' . $this->core->sysConfig->status->live;
        }

        $objSelect1 = $this->subject->getFolderTable()->select();
        $objSelect1->setIntegrityCheck(false);

        $objSelect1->from('folders', array(
                                          'idFolder'                                                                                                                                                                                                               => 'id', 'folderId', 'folderTitle' => 'folderTitles.title', 'depth', 'folderOrder' => 'sortPosition', 'parentId' => new Zend_Db_Expr('IF(folders.idParentFolder = 0, pages.idParent, folders.idParentFolder)'),
                                          'idPage'                                                                                                                                                                                                                 => 'pages.id', 'pageId' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', pl.pageId, pages.pageId)'), 'pages.isStartPage', 'pageOrder' => 'pages.sortPosition', 'url' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plUrls.url, urls.url)'), 'external' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plExternals.external, pageExternals.external)'), 'target' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plTargets.target, pageTargets.target)'), 'title' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plTitle.title, pageTitles.title)'),
                                          'pageProperties.idPageTypes', 'pageProperties.changed', 'languages.languageCode', 'rootLevels.idRootLevelGroups', 'folders.lft', 'folders.sortPosition', 'folders.sortTimestamp', 'pages.idParentTypes', 'genericFormId' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plGenericForms.genericFormId, genericForms.genericFormId)')
                                     ))
            ->join('folderProperties', 'folderProperties.folderId = folders.folderId AND folderProperties.version = folders.version AND folderProperties.idLanguages = ' . $this->core->dbh->quote($this->subject->getLanguageId(), Zend_Db::INT_TYPE) . $strFolderFilter, array())
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

        if ($this->subject->getSegmentId() !== null) {
            $objSelect1->where('pages.idSegments = 0 OR pages.idSegments = ?', $this->subject->getSegmentId());
            $objSelect1->where('folders.idSegments = 0 OR folders.idSegments = ?', $this->subject->getSegmentId());
        }

        if ($blnLoadSitemap) {
            $objSelect1->where('folderProperties.hideInSitemap = 0')
                ->where('pageProperties.hideInSitemap = 0');
        }

        $objSelect2 = $this->subject->getRootLevelTable()->select();
        $objSelect2->setIntegrityCheck(false);

        $objSelect2->from('pages', array(
                                        'idFolder'                                                                                                              => new Zend_Db_Expr('-1'), 'folderId' => new Zend_Db_Expr('""'), 'folderTitle' => new Zend_Db_Expr('""'), 'depth'  => new Zend_Db_Expr('0'), 'folderOrder' => new Zend_Db_Expr('-1'), 'parentId' => 'pages.idParent',
                                        'idPage'                                                                                                                => 'pages.id', 'pageId' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', pl.pageId, pages.pageId)'), 'pages.isStartPage', 'pageOrder' => 'pages.sortPosition', 'url' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plUrls.url, urls.url)'), 'external' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plExternals.external, pageExternals.external)'), 'target' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plTargets.target, pageTargets.target)'), 'title' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plTitle.title, pageTitles.title)'),
                                        'pageProperties.idPageTypes', 'pageProperties.changed', 'languages.languageCode', 'rootLevels.idRootLevelGroups', 'lft' => new Zend_Db_Expr('0'), 'pages.sortPosition', 'pages.sortTimestamp', 'pages.idParentTypes', 'genericFormId' => new Zend_Db_Expr('IF(pageProperties.idPageTypes = ' . $this->core->sysConfig->page_types->link->id . ', plGenericForms.genericFormId, genericForms.genericFormId)')
                                   ))
            ->join('rootLevels', 'pages.idParent = rootLevels.id', array())
            ->join('pageProperties', 'pageProperties.pageId = pages.pageId AND  pageProperties.version = pages.version AND pageProperties.idLanguages = ' . $this->core->dbh->quote($this->subject->getLanguageId(), Zend_Db::INT_TYPE) . $strPageFilter, array())
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

        if ($this->subject->getSegmentId() !== null) {
            $objSelect2->where('pages.idSegments = 0 OR pages.idSegments = ?', $this->subject->getSegmentId());
        }

        if ($blnLoadSitemap) {
            $objSelect2->where('pageProperties.hideInSitemap = 0');
        }

        if ($blnLoadFilter) {
            $objSelect1->joinLeft(array('filter' => 'page-DEFAULT_PRODUCT_TREE-1-Instances'), 'filter.pageId = pages.pageId AND filter.version = pages.version AND filter.idLanguages = pageProperties.idLanguages', array('entry_label', 'entry_category', 'entry_point', 'entry_sorttype'));
            $objSelect2->joinLeft(array('filter' => 'page-DEFAULT_PRODUCT_TREE-1-Instances'), 'filter.pageId = pages.pageId AND filter.version = pages.version AND filter.idLanguages = pageProperties.idLanguages', array('entry_label', 'entry_category', 'entry_point', 'entry_sorttype'));
        }

        $objSelect = $this->subject->getRootLevelTable()->select()
            ->distinct()
            ->union(array($objSelect1, $objSelect2))
            ->order('lft')
            ->order('isStartPage DESC')
            ->order('sortPosition ASC')
            ->order('sortTimestamp DESC');

        return $this->subject->getRootLevelTable()->fetchAll($objSelect);
    }
}

// return object instance
return new Client_Listeners_UndefinedMethods_ModelFolders();