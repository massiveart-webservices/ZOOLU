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
 * @package    library.massiveart.website
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Page
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-09: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.website
 * @subpackage Page
 */
class Page
{

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Model_Pages|Model_Global
     */
    protected $objModel;

    /**
     * @var Model_Pages
     */
    protected $objModelPages;

    /**
     * @var Model_Global
     */
    protected $objModelGlobals;

    /**
     * @var Model_Folders
     */
    protected $objModelFolders;

    /**
     * @var Model_Contacts
     */
    protected $objModelContacts;

    /**
     * @var Model_Locations
     */
    protected $objModelLocations;

    /**
     * @var Model_Categories
     */
    protected $objModelCategories;

    /**
     * @var Model_Files
     */
    protected $objModelFiles;

    /**
     * @var Model_Tags
     */
    protected $objModelTags;

    /**
     * @var Model_Urls
     */
    protected $objModelUrls;

    /**
     * @var GenericData
     */
    protected $objGenericData;

    /**
     * property of the generic data object
     * @return GenericData $objGenericData
     */
    public function GenericData()
    {
        return $this->objGenericData;
    }

    /**
     * @var Page
     */
    protected $objParentPage;

    /**
     * property of the parent page
     * @return Page $objParentPage
     */
    public function ParentPage()
    {
        return $this->objParentPage;
    }

    /**
     * @var Page
     */
    protected $objFallbackPage;

    /**
     * property of the parent page
     * @return Page $objFallbackPage
     */
    public function FallbackPage()
    {
        return $this->objFallbackPage;
    }

    /**
     * @var Page
     */
    protected $objChildPage;

    /**
     * property of the child page
     * @return Page $objChildPage
     */
    public function ChildPage()
    {
        return $this->objChildPage;
    }

    /**
     * @var Zend_Db_Table_Row_Abstract
     */
    protected $objBaseUrl;

    public function BaseUrl()
    {
        return $this->objBaseUrl;
    }

    /**
     * @var Zend_Db_Table_Rowset_Abstract
     */
    protected $objPortalLanguages;

    public function PortalLanguages()
    {
        return $this->objPortalLanguages;
    }

    /**
     * property of page urls
     * @return array $arrPageUrls
     */
    protected $arrPageUrls;

    public function PageUrls()
    {
        return $this->arrPageUrls;
    }

    /**
     * @var array
     */
    protected $arrContactsData = array();

    /**
     * @var array
     */
    protected $arrCategoriesData = array();

    /**
     * @var array
     */
    protected $arrTagsData = array();

    /**
     * @var array
     */
    protected $arrFileData = array();
    protected $arrDisplayOptions = array();

    protected $intRootLevelId;
    protected $strRootLevelTitle;
    protected $strRootLevelAlternativeTitle;
    protected $intRootLevelGroupId;
    protected $intElementId;
    protected $intElementLinkId;
    protected $strPageId;
    protected $strPageLinkId;
    protected $intPageVersion;
    protected $intLanguageId;
    protected $strLanguageCode;
    protected $blnHasUrlPrefix;
    protected $strUrlPrefix;
    protected $intLanguageDefinitionType;
    protected $blnHasSegments;
    protected $intSegmentId;
    protected $strSegmentCode;
    protected $strType;
    protected $strTemplateFile;
    protected $intTemplateId;
    protected $intTemplateCacheLifetime;
    protected $strTemplateRenderScript;

    protected $strPublisherName;
    protected $strChangeUserName;
    protected $strCreatorName;

    protected $objPublishDate;
    protected $objChangeDate;
    protected $objCreateDate;

    protected $intTypeId;
    protected $blnIsStartPage;
    protected $blnShowInNavigation;
    protected $intStatus;
    protected $intParentId;
    protected $intParentTypeId;
    protected $intNavParentId;
    protected $intNavParentTypeId;

    protected $arrContainer = array();
    protected $arrGenForms = array();
    protected $arrFallbackGenForms = array();
    protected $arrPageEntries = array();
    protected $arrInstanceDataAddon = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * loadPage
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function loadPage($blnLoadByParentId = false, $blnLoadGlobalTreeStartPage = true)
    {
        try {
            $this->getModel();
            $objPageData = ($blnLoadByParentId == true) ? $this->objModel->loadByParentId($this->intParentId, $this->intTypeId, true) : $this->objModel->loadByIdAndVersion($this->strPageId, $this->intPageVersion);

            if (count($objPageData) > 0) {
                $objPage = $objPageData->current();

                $this->setPageId($objPage->relationId);
                $this->setPageVersion($objPage->version);
                $this->setElementId($objPage->id);
                $this->setTemplateFile($objPage->filename);
                $this->setTemplateId($objPage->idTemplates);
                $this->setTemplateCacheLifetime($objPage->cacheLifetime);
                $this->setTemplateRenderScript($objPage->renderScript);
                $this->setPublisherName($objPage->publisher);
                $this->setPublishDate($objPage->published);
                $this->setCreatorName($objPage->creator);
                $this->setCreateDate($objPage->created);
                $this->setChangeUserName($objPage->changeUser);
                $this->setChangeDate($objPage->changed);
                if (isset($objPage->idPageTypes)) $this->setTypeId($objPage->idPageTypes);
                if (isset($objPage->idGlobalTypes)) $this->setTypeId($objPage->idGlobalTypes);
                if (isset($objPage->linkId)) $this->setElementLinkId($objPage->linkId);
                $this->setIsStartElement($objPage->isStartElement);
                $this->setShowInNavigation($objPage->showInNavigation);
                $this->setStatus($objPage->idStatus);
                $this->setParentId($objPage->idParent);
                $this->setParentTypeId($objPage->idParentTypes);

                /**
                 * navigation parent properties
                 */
                if ($this->intNavParentId === null) {
                    $this->setNavParentId($objPage->idParent);
                    $this->setNavParentTypeId($objPage->idParentTypes);
                }

                $this->objGenericData = new GenericData();
                $this->objGenericData->Setup()->setRootLevelId($this->intRootLevelId);
                $this->objGenericData->Setup()->setRootLevelGroupId($this->intRootLevelGroupId);
                $this->objGenericData->Setup()->setFormId($objPage->genericFormId);
                $this->objGenericData->Setup()->setFormVersion($objPage->version);
                $this->objGenericData->Setup()->setFormTypeId($objPage->idGenericFormTypes);
                $this->objGenericData->Setup()->setTemplateId($objPage->idTemplates);
                $this->objGenericData->Setup()->setElementId($objPage->id);
                $this->objGenericData->Setup()->setElementLinkId($this->getElementLinkId());
                $this->objGenericData->Setup()->setActionType($this->core->sysConfig->generic->actions->edit);
                $this->objGenericData->Setup()->setFormLanguageId($this->core->sysConfig->languages->default->id);
                $this->objGenericData->Setup()->setLanguageId($this->intLanguageId);
                $this->objGenericData->Setup()->setLanguageCode($this->strLanguageCode);
                $this->objGenericData->Setup()->setParentId($this->getParentId());
                $this->objGenericData->Setup()->setParentTypeId($this->getParentTypeId());
                $this->objGenericData->Setup()->setModelSubPath($this->getModelSubPath());
                
                $this->objGenericData->loadData();

                 if ($this->objGenericData->Setup()->getLanguageFallbackId() > 0 && $this->objGenericData->Setup()->getLanguageFallbackId() != $this->getLanguageId()) {
                    $this->objFallbackPage = clone $this;
                    $this->setLanguageId($this->objGenericData->Setup()->getLanguageFallbackId());
                    $this->loadPage();
                } else {
                    if ($this->objFallbackPage instanceof Page) {
                        $this->objGenericData->Setup()->setLanguageFallbackId($this->objFallbackPage->getLanguageId());
                    }

                    /**
                     * page type based fallbacks
                     */
                    if (isset($objPage->idPageTypes)) {
                        switch ($objPage->idPageTypes) {
                            case $this->core->sysConfig->page_types->external->id:
                                $strTmpUrl = $this->getFieldValue('external');
                                if ((bool) preg_match("/https?:\/\//", $strTmpUrl) == true) {
                                    header('Location: '.$strTmpUrl);
                                }else {
                                    header('Location: http://'.$strTmpUrl);
                                }
                                exit();
                                /* PROBLEM : FILTER_VALIDATE_URL not correct, urls with "-" (Bugfix in PHP 5.3.3)
                                if (filter_var($this->getFieldValue('external'), FILTER_VALIDATE_URL)) {
                                    header('Location: ' . $this->getFieldValue('external'));
                                } else if (filter_var('http://' . $this->getFieldValue('external'), FILTER_VALIDATE_URL)) {
                                    header('Location: http://' . $this->getFieldValue('external'));
                                } else {
                                    header('Location: http://' . $_SERVER['HTTP_HOST']);
                                }
                                exit();*/
                            case $this->core->sysConfig->page_types->link->id:
                                header('Location: http://' . $_SERVER['HTTP_HOST'] . $this->getField('internal_link')->strLinkedPageUrl);
                                exit();
                            case $this->core->sysConfig->page_types->product_tree->id:
                            case $this->core->sysConfig->page_types->press_area->id:
                            case $this->core->sysConfig->page_types->courses->id:
                            case $this->core->sysConfig->page_types->events->id:
                                if ($blnLoadGlobalTreeStartPage == true) {
                                    $this->objParentPage = clone $this;

                                    $this->setType('global');
                                    $this->setModelSubPath('global/models/');
                                    $this->setParentId($this->getFieldValue('entry_point'));
                                    $this->setNavParentId($this->getFieldValue('entry_point'));
                                    $this->setParentTypeId($this->core->sysConfig->parent_types->folder);

                                    $this->objModel = null;
                                    $this->loadPage(true);
                                }
                                break;
                        }
                    }

                    if ($this->objBaseUrl instanceof Zend_Db_Table_Row_Abstract) {
                        
                        $this->objParentPage = new Page();
                        $this->objParentPage->setRootLevelId($this->intRootLevelId);
                        $this->objParentPage->setRootLevelTitle($this->strRootLevelTitle);
                        $this->objParentPage->setPageId($this->objBaseUrl->relationId);
                        $this->objParentPage->setPageVersion($this->objBaseUrl->version);
                        $this->objParentPage->setLanguageId($this->objBaseUrl->idLanguages);
                        $this->objParentPage->setType('page');
                        $this->objParentPage->setModelSubPath('cms/models/');
                        $this->objParentPage->loadPage(false, false);

                        $this->objParentPage->setChildPage($this);
                    }
                }

            } else {
                throw new Exception('Not able to load page, because no page found in database!');
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * loadPageUrls
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadPageUrls()
    {
        $this->core->logger->debug('massiveart->website->page->loadPageUrls()');

        if ($this->objGenericData instanceof GenericData) {
            $strUrlType = $this->objGenericData->Setup()->getFormType();
            $this->arrPageUrls = $this->getModelUrls()->loadUrls((($this->strPageLinkId != null) ? $this->strPageLinkId : $this->strPageId), $this->intPageVersion, $strUrlType, $this->objGenericData->Setup()->getElementTypeId());
        }
    }

    /**
     * loadDomainSettings
     * @author Raphael Stocker <raphael.stocker@massiveart.com>
     * @version 1.0
     */
    public function loadDomainSettings($strDomain)
    {
        return $this->getModelUrls()->loadDomainSettings($strDomain);
    }
    
    
    /**
     * loadPortalLanguages
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function loadPortalLanguages()
    {
        $this->core->logger->debug('massiveart->website->page->loadRootLevelLanguages()');
        $this->objPortalLanguages = $this->getModelFolders()->loadRootLevelLanguages($this->intRootLevelId);
    }

    /**
     * indexPage
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function indexPage()
    {
        $this->core->logger->debug('massiveart->website->page->indexPage()');
        try {
            if ($this->objGenericData instanceof GenericData) {
                $this->objGenericData->indexData($this->strPageId, 'page', $this->intLanguageId);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * indexGlobal
     * @param null $intLanguageId
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function indexGlobal($intLanguageId = null)
    {
        $this->core->logger->debug('massiveart->website->page->indexGlobal(' . $intLanguageId . ')');
        try {
            if ($this->objGenericData instanceof GenericData) {

                $this->getModelPages();
                if ($this->objFallbackPage instanceof Page) {
                    $this->objModelPages->setLanguageId($this->objFallbackPage->getLanguageId());
                    $this->objGenericData->Setup()->getField(GenericSetup::FIELD_TYPE_URL)->setValue($this->objFallbackPage->GenericData()->Setup()->getField(GenericSetup::FIELD_TYPE_URL)->getValue());
                }
                $objGlobalPageParents = $this->objModelPages->loadGlobalParentPages($this->intTypeId);

                if (count($objGlobalPageParents) > 0) {
                    $this->arrContainer = array();
                    $this->arrGenForms = array();
                    $this->arrPageEntries = array();

                    foreach ($objGlobalPageParents as $objGlobalPageParent) {
                        $objEntry = new PageEntry();

                        $objEntry->setEntryId($objGlobalPageParent->id);
                        $objEntry->title = $objGlobalPageParent->title;
                        $objEntry->pageId = $objGlobalPageParent->pageId;
                        $objEntry->rootLevelId = ((int) $objGlobalPageParent->idRootLevels > 0) ? $objGlobalPageParent->idRootLevels : $objGlobalPageParent->idParent;
                        $objEntry->url = $this->getUrlFor($objGlobalPageParent->languageCode, $objGlobalPageParent->url);
                        $objEntry->created = $objGlobalPageParent->created;
                        $objEntry->published = $objGlobalPageParent->published;

                        $this->arrGenForms[$objGlobalPageParent->genericFormId . '-' . $objGlobalPageParent->genericFormVersion][] = $objGlobalPageParent->id;

                        if (!array_key_exists($objEntry->rootLevelId, $this->arrContainer)) {
                            $this->arrContainer[$objEntry->rootLevelId] = new PageContainer();
                        }

                        $this->arrContainer[$objEntry->rootLevelId]->addPageEntry($objEntry, 'entry_' . $objGlobalPageParent->id);

                        $this->arrPageEntries[$objGlobalPageParent->id] = $objEntry->rootLevelId;
                    }

                    foreach ($this->arrGenForms as $key => $arrPageIds) {
                        $arrGenFormPageIds = self::getGenFormPageIds($arrPageIds);
                        $this->loadInstanceGlobalFilterData($key, $arrGenFormPageIds);
                    }

                    $arrParentFolderIds = array();
                    $arrParentFolderStrIds = array();
                    $objGlobaParentFolders = $this->getModelGlobals()->loadParentFolders(($this->intElementLinkId > 0 ? $this->intElementLinkId : $this->intElementId));
                    if (count($objGlobaParentFolders) > 0) {
                        foreach ($objGlobaParentFolders as $objGlobaParentFolder) {
                            $arrParentFolderIds[] = $objGlobaParentFolder->id;
                            $arrParentFolderStrIds[] = $objGlobaParentFolder->folderId;
                        }
                    }

                    $arrGlobaCategories = is_array($this->getFieldValue('category')) ? $this->getFieldValue('category') : array($this->getFieldValue('category'));
                    $arrGlobaLabels = is_array($this->getFieldValue('label')) ? $this->getFieldValue('label') : array($this->getFieldValue('label'));

                    $intRootLevelId = null;
                    $arrParentPageContainer = array();

                    foreach ($this->arrContainer as $objContainer) {
                        foreach ($objContainer->getEntries() as $objEntry) {

                            if (empty($intRootLevelId)) {
                                $intRootLevelId = $objEntry->rootLevelId;
                            }

                            if (array_search($objEntry->entry_point, $arrParentFolderIds) === false) {
                                $objContainer->removePageEntry('entry_' . $objEntry->getEntryId());
                            }

                            if ((int) $objEntry->entry_category > 0) {
                                if (array_search($objEntry->entry_category, $arrGlobaCategories) === false) {
                                    $objContainer->removePageEntry('entry_' . $objEntry->getEntryId());
                                }
                            }

                            if ((int) $objEntry->entry_label > 0) {
                                if (array_search($objEntry->entry_label, $arrGlobaLabels) === false) {
                                    $objContainer->removePageEntry('entry_' . $objEntry->getEntryId());
                                }
                            }
                        }

                        if (count($objContainer->getEntries()) > 0) {
                            $arrParentPageContainer[$intRootLevelId] = $objContainer;
                        }

                        // reset root level id
                        $intRootLevelId = null;
                    }

                    if (count($arrParentPageContainer) > 0) {
                        $this->objGenericData->indexData($this->strPageId, 'global', (($intLanguageId != null) ? $intLanguageId : $this->intLanguageId), $arrParentPageContainer, $arrParentFolderIds);
                    }
                }

                if ($this->objFallbackPage instanceof Page) $this->objModelPages->setLanguageId($this->intLanguageId);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * loadInstanceGlobalFilterData
     * @param string $strKey
     * @param array $arrGenFormPageIds
     * @return void
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    protected function loadInstanceGlobalFilterData($strKey, $arrGenFormPageIds)
    {

        $objPageRowset = $this->getModelPages()->loadItemInstanceGlobalFilterDataByIds($strKey, $arrGenFormPageIds);

        /**
         * overwrite page entries
         */
        if (isset($objPageRowset) && count($objPageRowset) > 0) {
            foreach ($objPageRowset as $objPageRow) {
                if (array_key_exists($objPageRow->id, $this->arrPageEntries)) {
                    if (is_array($this->arrPageEntries[$objPageRow->id])) {
                        $arrPageEntryContainers = $this->arrPageEntries[$objPageRow->id];
                    } else {
                        $arrPageEntryContainers = array($this->arrPageEntries[$objPageRow->id]);
                    }

                    foreach ($arrPageEntryContainers as $intContainerId) {
                        if (array_key_exists($intContainerId, $this->arrContainer)) {
                            $objPageEntry = $this->arrContainer[$intContainerId]->getPageEntry('entry_' . $objPageRow->id);

                            $objPageEntry->entry_point = $objPageRow->entry_point;
                            $objPageEntry->entry_category = $objPageRow->entry_category;
                            $objPageEntry->entry_label = $objPageRow->entry_label;

                            $this->arrContainer[$intContainerId]->addPageEntry($objPageEntry, 'entry_' . $objPageRow->id);
                        }
                    }
                }
            }
        }
    }

    /**
     * getRegion
     * @param integer $intRegionId
     * @return GenericElementRegion
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getRegion($intRegionId)
    {
        try {
            return $this->objGenericData->Setup()->getRegion($intRegionId);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getField
     * @param string $strFieldName
     * @return GenericElementField
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getField($strFieldName)
    {
        try {
            return $this->objGenericData->Setup()->getField($strFieldName);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }


    /**
     * getFieldValue
     * @param string $strFieldName
     * @return string field value
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getFieldValue($strFieldName)
    {
        try {
            $objField = $this->objGenericData->Setup()->getField($strFieldName);
            if (is_object($objField)) {
                return $objField->getValue();
            } else {
                return null;
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getFileFieldValue
     * @param string $strFileFieldName
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getFileFieldValue($strFileFieldName)
    {
        try {
            if (!array_key_exists($strFileFieldName, $this->arrFileData)) {
                $this->arrFileData[$strFileFieldName] = null;

                $strFileIds = $this->objGenericData->Setup()->getFileField($strFileFieldName)->getValue();

                if ($strFileIds != '') {
                    $this->getModelFiles();
                    $this->arrFileData[$strFileFieldName] = $this->objModelFiles->loadFilesById($strFileIds);
                }
            }
            return $this->arrFileData[$strFileFieldName];
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }
    
    /**
     * getCoreFileFieldValue
     * @param string $strFileFieldName
     * @author Raphael Stocker <raphael.stocker@massiveart.com>
     * @version 1.0
     */
    public function getCoreFileFieldValue($strFileFieldName)
    {
        try {
            if (!array_key_exists($strFileFieldName, $this->arrFileData)) {
                $this->arrFileData[$strFileFieldName] = null;
                $objField = $this->objGenericData->Setup()->getField($strFileFieldName);
                if (is_object($objField)) {
                    $strFileIds = $objField->getValue();
                    if ($strFileIds != '') {
                        $this->getModelFiles();
                        $this->arrFileData[$strFileFieldName] = $this->objModelFiles->loadFilesById($strFileIds);
                        $this->arrDisplayOptions[$strFileFieldName] = $objField->getProperty('display_option');
                    }
                }
            }
            return $this->arrFileData[$strFileFieldName];
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }
    
    /**
     * getCoreFileDisplayOptions
     * @param string $strFileFieldName
     * @author Raphael Stocker <raphael.stocker@massiveart.com>
     * @version 1.0
     */
    public function getCoreFileDisplayOptions($strFileFieldName)
    {
        try {
            if (!array_key_exists($strFileFieldName, $this->arrDisplayOptions)) {
                $this->arrDisplayOptions[$strFileFieldName] = null;
                $objField = $this->objGenericData->Setup()->getField($strFileFieldName);
                $this->arrDisplayOptions[$strFileFieldName] = $objField->getProperty('display_option');
            }
            return $this->arrDisplayOptions[$strFileFieldName];
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getFileFieldValueById
     * @param string $strFileIds
     * @return object $objFiles
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getFileFieldValueById($strFileIds)
    {
        try {
            if ($strFileIds != '') {
                $this->getModelFiles();
                $objFiles = $this->objModelFiles->loadFilesById($strFileIds);
                return $objFiles;
            } else {
                return '';
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getFileFieldValueById
     * @param stdClass $objFilter
     * @return object $objFiles
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getFileFilterFieldValue($objFilters, $intFilterLanguageId = null)
    {
        try {
            if ($objFilters instanceof stdClass) {

                $arrTagIds = array();
                if (array_key_exists('ft' . $this->core->sysConfig->filter_types->tags, $objFilters->filters)) {
                    $arrTagIds = $objFilters->filters['ft' . $this->core->sysConfig->filter_types->tags]->referenceIds;
                }

                $arrFolderIds = array();
                if (array_key_exists('ft' . $this->core->sysConfig->filter_types->folders, $objFilters->filters)) {
                    $arrFolderIds = $objFilters->filters['ft' . $this->core->sysConfig->filter_types->folders]->referenceIds;
                }

                $intRootLevelId = -1;
                if (array_key_exists('ft' . $this->core->sysConfig->filter_types->rootLevel, $objFilters->filters)) {
                    $intRootLevelId = current($objFilters->filters['ft' . $this->core->sysConfig->filter_types->rootLevel]->referenceIds);
                }

                $this->getModelFiles();
                $this->objModelFiles->setAlternativLanguageId((($intFilterLanguageId != null) ? $this->intLanguageId : $this->core->sysConfig->languages->default->id));
                if ($intRootLevelId > 0 || count($arrFolderIds) > 0) {
                    $objFiles = $this->objModelFiles->loadFilesByFilter($intRootLevelId, $arrTagIds, $arrFolderIds, $intFilterLanguageId);
                    return $objFiles;
                } else {
                    return '';
                }
            } else {
                return '';
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getContactsValues
     * @param string $strFieldName
     * @return object arrContactsData
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getContactsValues($strFieldName)
    {
        try {
            if (!array_key_exists($strFieldName, $this->arrContactsData)) {
                $this->arrContactsData[$strFieldName] = null;
                $mixedIds = self::getFieldValue($strFieldName);

                $this->arrContactsData[$strFieldName] = $this->getPageContacts($mixedIds);
            }
            return $this->arrContactsData[$strFieldName];

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getPageContacts
     * @param string|array $mixedContactIds
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getPageContacts($mixedContactIds)
    {
        try {
            $this->getModelContacts();

            $objContacts = $this->objModelContacts->loadContactsById($mixedContactIds);
            return $objContacts;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getCategoriesValues
     * @param string $strFieldName
     * @return object $objCategoriesData
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getCategoriesValues($strFieldName)
    {
        try {
            if (!array_key_exists($strFieldName, $this->arrCategoriesData)) {
                $this->arrCategoriesData[$strFieldName] = null;

                $mixedIds = self::getFieldValue($strFieldName);
                $sqlSelect = $this->objGenericData->Setup()->getField($strFieldName)->sqlSelect;

                if (is_array($mixedIds)) {
                    if (count($mixedIds) > 0) {
                        $strReplaceWhere = '';
                        foreach ($mixedIds as $strValue) {
                            $strReplaceWhere .= $strValue . ',';
                        }
                        $strReplaceWhere = trim($strReplaceWhere, ',');

                        $objReplacer = new Replacer();
                        $sqlSelect = $objReplacer->sqlReplacer($sqlSelect, $this->intLanguageId, $this->objGenericData->Setup()->getRootLevelId(), ' AND tbl.id IN (' . $strReplaceWhere . ')');
                        $this->arrCategoriesData[$strFieldName] = $this->core->dbh->query($sqlSelect)->fetchAll(Zend_Db::FETCH_OBJ);
                    }
                } else if ($mixedIds != '') {
                    $objReplacer = new Replacer();
                    $sqlSelect = $objReplacer->sqlReplacer($sqlSelect, $this->intLanguageId, $this->objGenericData->Setup()->getRootLevelId(), ' AND tbl.id = ' . $mixedIds);
                    $this->arrCategoriesData[$strFieldName] = $this->core->dbh->query($sqlSelect)->fetchAll(Zend_Db::FETCH_OBJ);
                }
            }

            return $this->arrCategoriesData[$strFieldName];

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getInternalLinks
     * @param $fieldId
     * @return Zend_Db_Table_Rowset_Abstract
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getInternalLinks($fieldId = '120')
    {
        try {
            $this->getModel();
            $this->objModel->setLanguageId($this->intLanguageId);
            return $this->objModel->loadInternalLinks($this->strPageId, $this->intPageVersion, $fieldId);

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * @param $intPageId
     * @param $strGenForm
     * @param $intVersion
     *
     * @return mixed
     */
    public function getInternalLinkInfo($intPageId, $strGenForm, $intVersion)
    {
        $objLinkInfo = $this->getModel()->loadInternalLinkInfo($intPageId, $strGenForm, $intVersion);

        if (!empty($objLinkInfo)) {
            return $objLinkInfo->current();
        }
        return false;
    }

    /**
     * getTagsValues
     * @param string $strFieldName
     * @return object $objTagsData
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getTagsValues($strFieldName)
    {
        try {
            if (!array_key_exists($strFieldName, $this->arrTagsData)) {
                $this->getModelTags();
                $this->objModelTags->setLanguageId($this->intLanguageId);
                $this->arrTagsData[$strFieldName] = $this->objModelTags->loadTypeTags($this->strType, $this->strPageId, $this->intPageVersion);
            }
            return $this->arrTagsData[$strFieldName];
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getOverviewContainer
     * @return array $arrContainer
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getOverviewContainer($blnOnlyPages = false, $blnOnlyShowInNavigation = false)
    {
        try {
            $this->arrContainer = array();
            $this->arrGenForms = array();
            $this->arrFallbackGenForms = array();
            $this->arrPageEntries = array();

            $arrPageIds = array();

            $counter = 0;

            $objMyMultiRegion = $this->getRegion(15); //15 is the default overview block region

            if ($objMyMultiRegion instanceof GenericElementRegion) {
                foreach ($objMyMultiRegion->RegionInstanceIds() as $intRegionInstanceId) {

                    $objContainer = new PageContainer();
                    $objContainer->setContainerKey($objMyMultiRegion->getField('entry_category')->getInstanceValue($intRegionInstanceId));
                    $objContainer->setContainerTitle($objMyMultiRegion->getField('entry_title')->getInstanceValue($intRegionInstanceId));
                    $objContainer->setEntryNumber($objMyMultiRegion->getField('entry_number')->getInstanceValue($intRegionInstanceId));
                    $objContainer->setEntryViewType($objMyMultiRegion->getField('entry_viewtype')->getInstanceValue($intRegionInstanceId));

                    if ($objContainer->getEntryNumber() > 0) {
                        $objContainer->setContainerLabel($objMyMultiRegion->getField('entry_label')->getInstanceValue($intRegionInstanceId));
                        $objContainer->setContainerSortType($objMyMultiRegion->getField('entry_sorttype')->getInstanceValue($intRegionInstanceId));
                        $objContainer->setContainerSortOrder($objMyMultiRegion->getField('entry_sortorder')->getInstanceValue($intRegionInstanceId));
                        $objContainer->setContainerDepth($objMyMultiRegion->getField('entry_depth')->getInstanceValue($intRegionInstanceId));

                        /**
                         * override category and label filter with the parent page settings
                         */
                        if ($this->objParentPage instanceof Page) {
                            if ($this->objParentPage->getField('entry_category') !== null && (int) $this->objParentPage->getFieldValue('entry_category') > 0) $objContainer->setContainerKey($this->objParentPage->getFieldValue('entry_category'));
                            if ($this->objParentPage->getField('entry_label') !== null && (int) $this->objParentPage->getFieldValue('entry_label') > 0) $objContainer->setContainerLabel($this->objParentPage->getFieldValue('entry_label'));
                            if ($this->objParentPage->getField('entry_sorttype') !== null && (int) $this->objParentPage->getFieldValue('entry_sorttype') > 0) $objContainer->setContainerSortType($this->objParentPage->getFieldValue('entry_sorttype'));
                        }

                        $objEntries = $this->getOverviewPages($objContainer->getContainerKey(), $objContainer->getContainerLabel(), $objContainer->getEntryNumber(), $objContainer->getContainerSortType(), $objContainer->getContainerSortOrder(), $objContainer->getContainerDepth(), $arrPageIds, $blnOnlyPages, $blnOnlyShowInNavigation);
                        if (count($objEntries) > 0) {
                            foreach ($objEntries as $objEntryData) {
                                $objEntry = new PageEntry();
                                $objEntry->destinationId = (isset($objEntryData->idDestination)) ? $objEntryData->idDestination : 0;
                                $objEntry->relationId = isset($objEntryData->relationId) ? $objEntryData->relationId : false;
                                $objEntry->parentId = isset($objEntryData->plParentId) ? $objEntryData->plParentId : false;
                                $objEntry->plId = isset($objEntryData->plId) ? $objEntryData->plId : false;
                                $objEntry->pageTypeId = isset($objEntryData->idPageTypes) ? $objEntryData->idPageTypes : false;
                                $objEntry->target = isset($objEntryData->target) ? $objEntryData->target : false;

                                if (isset($objEntryData->idPageTypes) && $objEntryData->idPageTypes == $this->core->sysConfig->page_types->link->id) {
                                    $objEntry->setEntryId($objEntryData->plId);
                                    $objEntry->title = $objEntryData->title;
                                    $objEntry->url = $this->getUrlFor($objEntryData->languageCode, $objEntryData->plUrl);

                                    if (isset($objEntryData->idLanguageFallbacks) && $objEntryData->idLanguageFallbacks > 0) {
                                        if (isset($objEntryData->fallbackTitle) && $objEntryData->fallbackTitle != '') $objEntry->title = $objEntryData->fallbackTitle;
                                    }

                                    $this->arrGenForms[$objEntryData->plGenericFormId . '-' . $objEntryData->plVersion][] = $objEntryData->plId;
                                    $this->arrPageEntries[$objEntryData->plId] = $counter;

                                    $objContainer->addPageEntry($objEntry, 'entry_' . $objEntryData->plId);
                                } else {
                                    $objEntry->setEntryId($objEntryData->id);
                                    $objEntry->title = $objEntryData->title;

                                    if ($this->objParentPage instanceof Page &&
                                        ($this->objParentPage->getTypeId() == $this->core->sysConfig->page_types->product_tree->id || $this->objParentPage->getTypeId() == $this->core->sysConfig->page_types->press_area->id || $this->objParentPage->getTypeId() == $this->core->sysConfig->page_types->courses->id || $this->objParentPage->getTypeId() == $this->core->sysConfig->page_types->events->id)
                                    ) {
                                        $objEntry->url = $this->objParentPage->getFieldValue('url') . $objEntryData->url;
                                    } else {
                                        $objEntry->url = $this->getUrlFor($objEntryData->languageCode, $objEntryData->url);
                                    }

                                    if (isset($objEntryData->idLanguageFallbacks) && $objEntryData->idLanguageFallbacks > 0) {
                                        $this->arrFallbackGenForms[$objEntryData->fallbackGenericFormId . '-' . $objEntryData->fallbackGenericFormVersion][$objEntryData->idLanguageFallbacks][] = $objEntryData->id;
                                        if (isset($objEntryData->fallbackTitle) && $objEntryData->fallbackTitle != '') $objEntry->title = $objEntryData->fallbackTitle;
                                    } else {
                                        $this->arrGenForms[$objEntryData->genericFormId . '-' . $objEntryData->version][] = $objEntryData->id;
                                    }

                                    $this->arrPageEntries[$objEntryData->id] = $counter;

                                    $objContainer->addPageEntry($objEntry, 'entry_' . $objEntryData->id);
                                }
                                array_push($arrPageIds, $objEntryData->id);
                            }
                        }
                    }
                    $this->arrContainer[$counter] = $objContainer;
                    $counter++;
                }
            }

            /**
             * get data of instance tables
             */
            if (count($this->arrGenForms) > 0) {
                $this->loadInstanceData();
            }

            /**
             * get fallback data of instance tables
             */
            if (count($this->arrFallbackGenForms) > 0) {
                $this->loadFallbackInstanceData();
            }

            return $this->arrContainer;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getCourseOverviewContainer
     * @return PageContainer
     * @author Thomas Sschedler<cha@massiveart.com>
     * @version 1.0
     */
    public function getCourseOverviewContainer()
    {
        try {

            $this->getOverviewContainer(true);
            $objContainer = new PageContainer();
            if (count($this->arrContainer) > 0) {
                foreach ($this->arrContainer as $objTmpContainer) {
                    foreach ($objTmpContainer->getEntries() as $objTmpEntry) {
                        if ($objTmpEntry->courses !== null) {
                            foreach ($objTmpEntry->courses->arr as $objCourse) {
                                $objEntry = clone $objTmpEntry;
                                unset($objEntry->courses);
                                $objEntry->course = $objCourse;
                                $objContainer->addPageEntry($objEntry, date('Ymd', $objCourse->start_datetime) . sprintf('%07d', $objTmpEntry->getEntryId()) . sprintf('%03d', $objCourse->id));
                            }
                        }
                    }
                }
            }
            $objContainer->sortEntries();

            return $objContainer;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getEventOverviewContainer
     * @return PageContainer
     * @author Thomas Sschedler<cha@massiveart.com>
     * @version 1.0
     */
    public function getEventOverviewContainer()
    {
        try {

            $this->getOverviewContainer(true);
            $objContainer = new PageContainer();
            if (count($this->arrContainer) > 0) {
                foreach ($this->arrContainer as $objTmpContainer) {
                    foreach ($objTmpContainer->getEntries() as $objTmpEntry) {

                        $strEventUrl = '';
                        if ((bool) preg_match("/https?:\/\//", $objTmpEntry->external) == true) {
                            $strEventUrl = $objTmpEntry->external;
                        }else {
                            $strEventUrl = 'http://' . $objTmpEntry->external;
                        }

                        if (strtotime($objTmpEntry->start_datetime)) {
                            $objEntry = clone $objTmpEntry;
                            $objEntry->start_datetime = strtotime($objTmpEntry->start_datetime);
                            $objEntry->end_datetime = strtotime($objTmpEntry->end_datetime);
                            $objEntry->eventUrl = $strEventUrl;
                            $objContainer->addPageEntry($objEntry, date('Ymd', $objEntry->start_datetime) . sprintf('%07d', $objTmpEntry->getEntryId()));
                        }
                    }
                }
            }
            $objContainer->sortEntries();

            return $objContainer;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * @param $year
     * @param $month
     * @param int $intFieldId
     * @return array
     */
    public function getMonthEvents ($year, $month, $intFieldId = 275)
    {
        $eventManager = new Sulu\Events\Manager();
        $from = strtotime($year . '-' . $month . '-01');
        $to = strtotime($year . '-' . $month . '-01 + 1 month') - 1;
        try {
            $this->getModel();
            $parentId = $this->getParentId();
            $category = $this->getFieldValue('entry_category');
            $label = $this->getFieldValue('entry_label');
            $depth = $this->getFieldValue('entry_depth');
            $rows = $this->objModel->loadMonthEvents($this->intRootLevelId, $parentId, $intFieldId, $category, $label, $depth);
            if (count($rows)) {
                // Get All Generic Forms
                foreach ($rows as $row) {
                    $data = array();
                    $image = new stdClass();
                    foreach ($row as $key => $value) {
                        if (in_array($key, array('fileversion', 'filename', 'filepath', 'filetitle'))) {
                            $image->$key = $value;
                        } elseif($key == 'url') {
                            $data[$key] = $this->getUrlFor($this->core->strLanguageCode, $value);
                        } else {
                            $data[$key] = $value;
                        }
                    }
                    $data['image'] = $image;
                    $event = new Sulu\Events\Event($data);
                    $eventManager->addEvent($event);
                }
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
        return $eventManager->getEvents($from, $to);
    }

    /**
     * getOverviewPages
     * @param integer $intCategoryId
     * @param integer $intEntryNumber
     * @param integer $intSortType
     * @param integer $intSortOrder
     * @param integer $intEntryDepth
     * @param array $arrPageIds
     * @param boolean $blnOnlyPages load only pages (items), no start elements
     * @param boolean $blnOnlyShowInNavigation load only pages (items) with property "showInNavigation"
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getOverviewPages($intCategoryId, $intLabelId, $intEntryNumber, $intSortType, $intSortOrder, $intEntryDepth, $arrPageIds, $blnOnlyPages = false, $blnOnlyShowInNavigation = false, $blnFilterDisplayEnvironment = true)
    {
        try {
            $this->getModel();
            if ($this->intNavParentId !== null && $this->intNavParentId > 0) {
                $objPages = $this->objModel->loadItems((($this->ParentPage() instanceof Page) ? array('id' => $this->ParentPage()->getTypeId(), 'key' => $this->ParentPage()->getType()) : array('id' => $this->intTypeId, 'key' => $this->strType)), $this->intNavParentId, $intCategoryId, $intLabelId, $intEntryNumber, $intSortType, $intSortOrder, $intEntryDepth, $arrPageIds, $blnOnlyPages, $blnOnlyShowInNavigation, $blnFilterDisplayEnvironment);
            } else {
                $objPages = $this->objModel->loadItems(array('id' => $this->intTypeId, 'key' => $this->strType), $this->intParentId, $intCategoryId, $intLabelId, $intEntryNumber, $intSortType, $intSortOrder, $intEntryDepth, $arrPageIds, $blnOnlyPages, $blnOnlyShowInNavigation, $blnFilterDisplayEnvironment);
            }
            
            return $objPages;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getCollectionContainer
     * @return PageContainer $objContainer
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getCollectionContainer()
    {
        try {
            $this->arrContainer = array();
            $this->arrGenForms = array();
            $this->arrPageEntries = array();
            $counter = 0;

            $objContainer = new PageContainer();
            $objContainer->setContainerTitle($this->getFieldValue('collection_title'));

            $objCollectionFieldElement = $this->getField('collection');

            if ($objCollectionFieldElement->objPageCollection instanceof Zend_Db_Table_Rowset_Abstract && count($objCollectionFieldElement->objPageCollection) > 0) {
                foreach ($objCollectionFieldElement->objPageCollection as $objEntryData) {
                    $objEntry = new PageEntry();
                    $objEntry->setEntryId($objEntryData->id);
                    $objEntry->title = $objEntryData->title;
                    $objEntry->url = $this->getUrlFor($objEntryData->languageCode, $objEntryData->url);

                    $this->arrGenForms[$objEntryData->genericFormId . '-' . $objEntryData->genericFormVersion][] = $objEntryData->id;

                    $objContainer->addPageEntry($objEntry, 'entry_' . $objEntryData->id);
                    $this->arrPageEntries[$objEntryData->idPage] = $counter;
                }
        
        $this->arrContainer[$counter] = $objContainer;

        /**
         * get data of instance tables
         */
        if (count($this->arrGenForms) > 0) {
            $this->loadInstanceData();
        }
      }

            return $objContainer;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getPagesContainer
     * @return array $arrContainer
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getPagesContainer()
    {
        try {
            $this->arrContainer = array();
            $this->arrGenForms = array();
            $this->arrPageEntries = array();
            $counter = 0;

            $objMyMultiRegion = $this->getRegion(17);

            if ($objMyMultiRegion instanceof GenericElementRegion) {
                foreach ($objMyMultiRegion->RegionInstanceIds() as $intRegionInstanceId) {

                    $objContainer = new PageContainer();
                    $objContainer->setContainerKey($objMyMultiRegion->getField('entry_nav_point')->getInstanceValue($intRegionInstanceId));
                    $objContainer->setContainerTitle($objMyMultiRegion->getField('entry_title')->getInstanceValue($intRegionInstanceId));
                    $objContainer->setEntryNumber($objMyMultiRegion->getField('entry_number')->getInstanceValue($intRegionInstanceId));

                    $intEntryCategory = $objMyMultiRegion->getField('entry_category')->getInstanceValue($intRegionInstanceId);
                    $intEntryLabel = $objMyMultiRegion->getField('entry_label')->getInstanceValue($intRegionInstanceId);
                    $intEntrySortType = $objMyMultiRegion->getField('entry_sorttype')->getInstanceValue($intRegionInstanceId);
                    $intEntrySortOrder = $objMyMultiRegion->getField('entry_sortorder')->getInstanceValue($intRegionInstanceId);

                    if ($objContainer->getContainerKey() > 0 && $objContainer->getEntryNumber() > 0) {

                        $objContainer->setContainerSortType($intEntrySortType);
                        $objContainer->setContainerSortOrder($intEntrySortOrder);

                        $objEntries = $this->getFolderChildPages($objContainer->getContainerKey(), $intEntryCategory, $intEntryLabel, $objContainer->getEntryNumber(), $objContainer->getContainerSortType(), $objContainer->getContainerSortOrder());

                        if (count($objEntries) > 0) {
                            foreach ($objEntries as $objEntryData) {
                                $objEntry = new PageEntry();

                                $objEntry->setEntryId($objEntryData->idPage);
                                $objEntry->title = $objEntryData->title;
                                $objEntry->url = $this->getUrlFor($objEntryData->languageCode, $objEntryData->url);
                                $objEntry->created = $objEntryData->pageCreated;
                                $objEntry->published = $objEntryData->pagePublished;
                                $objEntry->destinationId = $objEntryData->idDestination;
                                $objEntry->pageTypeId = $objEntryData->idPageTypes;
                                $objEntry->target = isset($objEntryData->target) ? $objEntryData->target : false;

                                $this->arrGenForms[$objEntryData->genericFormId . '-' . $objEntryData->version][] = $objEntryData->idPage;
                                if (isset($this->arrPageEntries[$objEntryData->idPage])) {
                                    if (is_array($this->arrPageEntries[$objEntryData->idPage])) {
                                        array_push($this->arrPageEntries[$objEntryData->idPage], $counter);
                                    } else {
                                        $this->arrPageEntries[$objEntryData->idPage] = array($this->arrPageEntries[$objEntryData->idPage], $counter);
                                    }
                                } else {
                                    $this->arrPageEntries[$objEntryData->idPage] = $counter;
                                }

                                $objContainer->addPageEntry($objEntry, 'entry_' . $objEntryData->idPage);
                            }
                        }
                    } else if ($objContainer->getEntryNumber() > 0) {
                        $objContainer->setContainerSortType($intEntrySortType);
                        $objContainer->setContainerSortOrder($intEntrySortOrder);

                        /**
                         * overall rootlevels
                         */
                        $objEntries = $this->getOverallFolderChildPages($intEntryCategory, $intEntryLabel, $objContainer->getEntryNumber(), $objContainer->getContainerSortType(), $objContainer->getContainerSortOrder());

                        if (count($objEntries) > 0) {
                            foreach ($objEntries as $objEntryData) {
                                $objEntry = new PageEntry();

                                $objEntry->setEntryId($objEntryData->idPage);
                                $objEntry->title = $objEntryData->title;
                                $objEntry->url = $this->getUrlFor($objEntryData->languageCode, $objEntryData->url);
                                $objEntry->created = $objEntryData->pageCreated;
                                $objEntry->published = $objEntryData->pagePublished;
                                $objEntry->rootTitle = $objEntryData->rootTitle;
                                $objEntry->destinationId = $objEntryData->idDestination;
                                $objEntry->pageTypeId = $objEntryData->idPageTypes;
                                $objEntry->target = isset($objEntryData->target) ? $objEntryData->target : false;

                                $this->arrGenForms[$objEntryData->genericFormId . '-' . $objEntryData->version][] = $objEntryData->idPage;
                                if (isset($this->arrPageEntries[$objEntryData->idPage])) {
                                    if (is_array($this->arrPageEntries[$objEntryData->idPage])) {
                                        array_push($this->arrPageEntries[$objEntryData->idPage], $counter);
                                    } else {
                                        $this->arrPageEntries[$objEntryData->idPage] = array($this->arrPageEntries[$objEntryData->idPage], $counter);
                                    }
                                } else {
                                    $this->arrPageEntries[$objEntryData->idPage] = $counter;
                                }

                                $objContainer->addPageEntry($objEntry, 'entry_' . $objEntryData->idPage);
                            }
                        }
                    }

                    $this->arrContainer[$counter] = $objContainer;
                    $counter++;
                }
            }

            /**
             * get data of instance tables
             */
            if (count($this->arrGenForms) > 0) {
                $this->loadInstanceData();
            }

            return $this->arrContainer;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getFormFields
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @return array
     * @version 1.0
     */
    public function getFormFields()
    {
        $objMyMultiRegion = $this->getRegion(100); //100 is the form_field-Region
        $arrFields = array();

        foreach ($objMyMultiRegion->RegionInstanceIds() as $intRegionInstanceId) {
            $objField = new stdClass;
            $objField->title = $objMyMultiRegion->getField('title')->getInstanceValue($intRegionInstanceId);
            $objField->type = $this->getModelCategories()->loadCategory($objMyMultiRegion->getField('field_type')->getInstanceValue($intRegionInstanceId))->current();
            $objField->mandatory = $objMyMultiRegion->getField('mandatory')->getInstanceValue($intRegionInstanceId);
            $objField->description = $objMyMultiRegion->getField('description')->getInstanceValue($intRegionInstanceId);
            $objField->validation = $this->getModelCategories()->loadCategory($objMyMultiRegion->getField('validation')->getInstanceValue($intRegionInstanceId))->current();
            $objField->display = $this->getModelCategories()->loadCategory($objMyMultiRegion->getField('display')->getInstanceValue($intRegionInstanceId))->current();
            $objField->options = preg_split('/\r\n|\r|\n/', $objMyMultiRegion->getField('options')->getInstanceValue($intRegionInstanceId));
            $objField->maxlength = $objMyMultiRegion->getField('maxlength')->getInstanceValue($intRegionInstanceId);
            $objField->other = $this->getModelCategories()->loadCategory($objMyMultiRegion->getField('other')->getInstanceValue($intRegionInstanceId))->current();

            $arrFields[] = $objField;
        }

        return $arrFields;
    }

    /**
     * getFilesForDownloadCenter
     * @return DownloadCenter
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function getFilesForDownloadCenter($blnSplitAlphabetic = true, $arrOrder = array())
    {
        try {

            $objDownloadCenter = new DownloadCenter();
            $objDownloadCenter->setTitle($this->getField('entry_title')->getValue());
            $objDownloadCenter->setFolderId($this->getField('entry_point')->getValue());
            $objDownloadCenter->setFilterTagId($this->getField('entry_file_tag')->getValue());

            if ($objDownloadCenter->getFolderId() > 0) {
                $arrTagFilter = ($objDownloadCenter->getFilterTagId() > 0) ? array($objDownloadCenter->getFilterTagId()) : array();
                $objFiles = $this->getModelFiles()->loadFilesByFilter(-1, $arrTagFilter, array($objDownloadCenter->getFolderId()), null, $arrOrder);

                if (count($objFiles) > 0) {
                    if ($blnSplitAlphabetic) {
                        foreach ($objFiles as $objFile) {
                            $objDownloadCenter->add($objFile);
                        }
                    } else {
                        $objDownloadCenter->setFiles($objFiles);
                    }
                }
            }

            return $objDownloadCenter;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getGlobalContainer
     * @return PageContainer $objContainer
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function getGlobalContainer()
    {
        try {
            $this->arrContainer = array();
            $this->arrGenForms = array();
            $this->arrFallbackGenForms = array();
            $this->arrPageEntries = array();
            $counter = 0;

            $objContainer = new PageContainer();
            $objContainer->setContainerKey($this->getField('global_entry_point')->getValue());
            $objContainer->setContainerTitle($this->getField('global_entry_title')->getValue());
            $objContainer->setEntryNumber(12);

            $arrFilterOptions = array(
                'CategoryId'  => $this->getField('global_entry_category')->getValue(),
                'LabelId'     => $this->getField('global_entry_label')->getValue()
            );

            $objEntries = $this->getModelFolders()->loadWebsiteGlobalTree($objContainer->getContainerKey(), $arrFilterOptions, $this->core->sysConfig->root_level_groups->product);

            $strBaseUrl = '';
            $intGlobaEntyPointId = $this->getField('global_entry_nav_point')->getValue();
            if ((int) $intGlobaEntyPointId > 0) {
                $objGlobaEntyPointData = $this->getModel()->load($intGlobaEntyPointId);
                if (count($objGlobaEntyPointData) == 1) {
                    $objGlobaEntyPoint = $objGlobaEntyPointData->current();
                    $objUrlData = $this->getModelUrls()->loadUrl($objGlobaEntyPoint->relationId, $objGlobaEntyPoint->version, $this->core->sysConfig->url_types->page);
                    if (count($objUrlData) > 0) {
                        $objUrl = $objUrlData->current();
                        $strBaseUrl = $this->getUrlFor($objUrl->languageCode, $objUrl->url);
                    }
                }
            }

            if (count($objEntries) > 0) {
                foreach ($objEntries as $objEntryData) {
                    $objEntry = new PageEntry();
                    $objEntry->setEntryId($objEntryData->id);
                    $objEntry->title = $objEntryData->globalTitle;

                    if ($strBaseUrl != '') {
                        $objEntry->url = $strBaseUrl . $objEntryData->url;
                    } else {
                        $objEntry->url = $this->getUrlFor($objEntryData->languageCode, $objEntryData->url);
                    }

                    if (isset($objEntryData->idLanguageFallbacks) && $objEntryData->idLanguageFallbacks > 0) {
                        $this->arrFallbackGenForms[$objEntryData->fallbackGenericFormId . '-' . $objEntryData->fallbackGenericFormVersion][$objEntryData->idLanguageFallbacks][] = $objEntryData->id;
                        if (isset($objEntryData->fallbackTitle) && $objEntryData->fallbackTitle != '') $objEntry->title = $objEntryData->fallbackTitle;
                    } else {
                        $this->arrGenForms[$objEntryData->genericFormId . '-' . $objEntryData->genericFormVersion][] = $objEntryData->id;
                    }

                    $objContainer->addPageEntry($objEntry, 'entry_' . $objEntryData->id);
                    $this->arrPageEntries[$objEntryData->id] = $counter;
                }
        
        $this->arrContainer[$counter] = $objContainer;

        
        $this->objModel = $this->getModelGlobals();
        
        /**
         * get data of instance tables
         */
        if (count($this->arrGenForms) > 0) {
            $this->loadInstanceData('174,5');
        }      
        
        /**
         * get fallback data of instance tables
         */
        if (count($this->arrFallbackGenForms) > 0) {
            $this->loadFallbackInstanceData('174,5');
        } 
        
        $this->getModel(true);
      }

            $objContainer->shuffleEntries();
            return $objContainer;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getGlobalContainers
     * @return PageContainer $objContainer
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function getGlobalContainers($intRootLevelGroupId)
    {
        try {
            $this->arrContainer = array();
            $this->arrGenForms = array();
            $this->arrFallbackGenForms = array();
            $this->arrPageEntries = array();
            $counter = 0;

            $objMyMultiRegion = $this->getRegion(84);

            if ($objMyMultiRegion instanceof GenericElementRegion) {
                foreach ($objMyMultiRegion->RegionInstanceIds() as $intRegionInstanceId) {

                    $objContainer = new PageContainer();
                    $objContainer->setContainerKey($objMyMultiRegion->getField('global_entry_point')->getInstanceValue($intRegionInstanceId));
                    $objContainer->setContainerTitle($objMyMultiRegion->getField('global_entry_title')->getInstanceValue($intRegionInstanceId));
                    $objContainer->setEntryNumber($objMyMultiRegion->getField('global_entry_number')->getInstanceValue($intRegionInstanceId));

                    $arrFilterOptions = array(
                        'CategoryId' => $objMyMultiRegion->getField('global_entry_category')->getInstanceValue($intRegionInstanceId),
                        'LabelId'    => $objMyMultiRegion->getField('global_entry_label')->getInstanceValue($intRegionInstanceId),
                        'Number'     => $objContainer->getEntryNumber(),
                        'SortType'   => $objMyMultiRegion->getField('global_entry_sorttype')->getInstanceValue($intRegionInstanceId),
                        'SortOrder'  => $objMyMultiRegion->getField('global_entry_sortorder')->getInstanceValue($intRegionInstanceId)
                    );

                    $objEntries = $this->getModelFolders()->loadWebsiteGlobalTree($objContainer->getContainerKey(), $arrFilterOptions, $intRootLevelGroupId);

                    $strBaseUrl = '';
                    $intGlobaEntyPointId = $objMyMultiRegion->getField('global_entry_nav_point')->getInstanceValue($intRegionInstanceId);
                    if ((int) $intGlobaEntyPointId > 0) {
                        $objGlobaEntyPointData = $this->getModel()->load($intGlobaEntyPointId);
                        if (count($objGlobaEntyPointData) == 1) {
                            $objGlobaEntyPoint = $objGlobaEntyPointData->current();
                            $objUrlData = $this->getModelUrls()->loadUrl($objGlobaEntyPoint->relationId, $objGlobaEntyPoint->version, $this->core->sysConfig->url_types->page);
                            if (count($objUrlData) > 0) {
                                $objUrl = $objUrlData->current();
                                $strBaseUrl = $this->getUrlFor($objUrl->languageCode, $objUrl->url);
                            }
                        }
                    }

                    if (count($objEntries) > 0) {
                        foreach ($objEntries as $objEntryData) {
                            $objEntry = new PageEntry();
                            $objEntry->setEntryId($objEntryData->id);
                            $objEntry->title = $objEntryData->globalTitle;

                            if ($strBaseUrl != '') {
                                $objEntry->url = $strBaseUrl . $objEntryData->url;
                            } else {
                                $objEntry->url = $this->getUrlFor($objEntryData->languageCode, $objEntryData->url);
                            }

                            if (isset($objEntryData->idLanguageFallbacks) && $objEntryData->idLanguageFallbacks > 0) {
                                $this->arrFallbackGenForms[$objEntryData->fallbackGenericFormId . '-' . $objEntryData->fallbackGenericFormVersion][$objEntryData->idLanguageFallbacks][] = $objEntryData->id;
                                if (isset($objEntryData->fallbackTitle) && $objEntryData->fallbackTitle != '') $objEntry->title = $objEntryData->fallbackTitle;
                            } else {
                                $this->arrGenForms[$objEntryData->genericFormId . '-' . $objEntryData->genericFormVersion][] = $objEntryData->id;
                            }

                            if (isset($this->arrPageEntries[$objEntryData->id])) {
                                if (is_array($this->arrPageEntries[$objEntryData->id])) {
                                    array_push($this->arrPageEntries[$objEntryData->id], $counter);
                                } else {
                                    $this->arrPageEntries[$objEntryData->id] = array($this->arrPageEntries[$objEntryData->id], $counter);
                                }
                            } else {
                                $this->arrPageEntries[$objEntryData->id] = $counter;
                            }

                            $objContainer->addPageEntry($objEntry, 'entry_' . $objEntryData->id);
                        }

                        $this->arrContainer[$counter] = $objContainer;
                        $counter++;
                    }
                }

                $this->objModel = $this->getModelGlobals();

                /**
                 * get data of instance tables
                 */
                if (count($this->arrGenForms) > 0) {
                    $this->loadInstanceData('174,5,55');
                }

                /**
                 * get fallback data of instance tables
                 */
                if (count($this->arrFallbackGenForms) > 0) {
                    $this->loadFallbackInstanceData('174,5,55');
                }

                $this->getModel(true);
            }

            return $this->arrContainer;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getSubPageContainer
     * @param string $strBaseUrl
     * @return PageContainer $objContainer
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function getSubPageContainer()
    {
        try {
            $this->arrContainer = array();
            $this->arrGenForms = array();
            $this->arrPageEntries = array();
            $counter = 0;

            $objContainer = new PageContainer();
            $objContainer->setContainerKey($this->getParentId());
            $objContainer->setEntryNumber(5);

            $objEntries = $this->getFolderChildPages($objContainer->getContainerKey());
            if (count($objEntries) > 0) {
                foreach ($objEntries as $objEntryData) {
                    $objEntry = new PageEntry();
                    $objEntry->setEntryId($objEntryData->idPage);
                    $objEntry->title = $objEntryData->title;
                    $objEntry->url = $this->getUrlFor($objEntryData->languageCode, $objEntryData->url);
                    $objEntry->showInNavigation = $objEntryData->showInNavigation;

                    $this->arrGenForms[$objEntryData->genericFormId . '-' . $objEntryData->version][] = $objEntryData->idPage;

                    $objContainer->addPageEntry($objEntry, 'entry_' . $objEntryData->idPage);
                    $this->arrPageEntries[$objEntryData->idPage] = $counter;
                }
        
        $this->arrContainer[$counter] = $objContainer;

        /**
         * get data of instance tables
         */
        if (count($this->arrGenForms) > 0) {
            $this->loadInstanceData();
        }
      }

            return $objContainer;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getFolderChildPages
     * @param integer $intFolderId
     * @param integer $intCategoryId
     * @param integer $intLimitNumber
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getFolderChildPages($intFolderId, $intCategoryId = 0, $intLabelId = 0, $intLimitNumber = 5, $strSortType = 0, $strSortOrder = 0)
    {
        try {
            $this->getModelFolders();

            $objPages = $this->objModelFolders->loadFolderChildPages($intFolderId, $intCategoryId, $intLabelId, $intLimitNumber, $strSortType, $strSortOrder);
            return $objPages;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getOverallFolderChildPages
     * @param integer $intCategoryId
     * @param integer $intLimitNumber
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getOverallFolderChildPages($intCategoryId, $intLabelId, $intLimitNumber, $strSortType, $strSortOrder)
    {
        try {
            $this->getModelFolders();

            $objPages = $this->objModelFolders->loadOverallFolderChildPages($intCategoryId, $intLabelId, $intLimitNumber, $strSortType, $strSortOrder);
            return $objPages;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getPagesByCategory
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getPagesByCategory()
    {
        try {
            $this->getModel();

            $intCategoryId = $this->objGenericData->Setup()->getField('top_category')->getValue();
            $intLabelId = $this->objGenericData->Setup()->getField('top_label')->getValue();
            $intLimitNumber = $this->objGenericData->Setup()->getField('top_number')->getValue();
            $intSortType = $this->objGenericData->Setup()->getField('top_sorttype')->getValue();
            $intSortOrder = $this->objGenericData->Setup()->getField('top_sortorder')->getValue();

            $objPages = $this->objModel->loadPagesByCategory($this->intRootLevelId, $intCategoryId, $intLabelId, $intLimitNumber, $intSortType, $intSortOrder);

            return $objPages;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getLocationById
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function getLocationById($intLocationId)
    {
        try {
            $this->getModelLocations();

            $objLocation = $this->objModelLocations->loadLocation($intLocationId);

            return $objLocation;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getLocationsByCountry
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getLocationsByCountry($strCountry, $strProvince = '')
    {
        try {
            $this->getModelLocations();

            $intUnitId = $this->objGenericData->Setup()->getField('entry_location')->getValue();
            $intTypeId = $this->objGenericData->Setup()->getField('entry_type')->getValue();

            $objLocations = $this->objModelLocations->loadLocationsByCountry($strCountry, $intUnitId, $intTypeId, $strProvince);

            return $objLocations;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getProvincesByCountry
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function getProvincesByCountry($strCountry)
    {
        try {
            $this->getModelLocations();

            $intUnitId = $this->objGenericData->Setup()->getField('entry_location')->getValue();
            $intTypeId = $this->objGenericData->Setup()->getField('entry_type')->getValue();

            $objProvinces = $this->objModelLocations->loadProvincesByCountry($strCountry, $intUnitId, $intTypeId);

            return $objProvinces;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getEventsContainer
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getEventsContainer($intQuarter = 0, $intYear = 0)
    {
        try {
            $this->arrContainer = array();
            $this->arrGenForms = array();
            $this->arrPageEntries = array();
            $arrPageIds = array();
            $counter = 0;

            $objContainer = new PageContainer();
            $objEntries = $this->getPagesByTemplate($this->core->sysConfig->page_types->page->event_templateId, $intQuarter, $intYear);

            if (count($objEntries) > 0) {
                foreach ($objEntries as $objEntryData) {
                    $objEntry = new PageEntry();
                    $objEntry->setEntryId($objEntryData->id);
                    $objEntry->title = $objEntryData->title;
                    $objEntry->url = $this->getUrlFor($objEntryData->languageCode, $objEntryData->url);
                    $objEntry->datetime = $objEntryData->datetime;

                    $this->arrGenForms[$objEntryData->genericFormId . '-' . $objEntryData->version][] = $objEntryData->id;
                    $this->arrPageEntries[$objEntryData->id] = $counter;

                    $objContainer->addPageEntry($objEntry, 'entry_' . $objEntryData->id);
                }
                $this->arrContainer[$counter] = $objContainer;
                $counter++;
            }

            /**
             * get data of instance tables
             */
            if (count($this->arrGenForms) > 0) {
                $this->loadInstanceData();
            }

            return $this->arrContainer;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getPagesByTemplate
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getPagesByTemplate($intTemplateId, $intQuarter = 0, $intYear = 0)
    {
        try {
            $this->getModel();
            $objPages = $this->objModel->loadPagesByTemplatedId($intTemplateId, $intQuarter, $intYear);
            return $objPages;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getPageInstanceDataById
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getPageInstanceDataById($intPageId, $strGenForm)
    {
        try {
            $this->getModel();

            $objPageRowset = $this->objModel->loadPageInstanceDataById($intPageId, $strGenForm);
            return $objPageRowset;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * getPageFilesDataById
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getPageFilesDataById($intPageId, $strGenForm)
    {
        try {
            $this->getModelPages();

            $objPageRowset = $this->objModelPages->loadPageFilesById($intPageId, $strGenForm);
            return $objPageRowset;
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * loadInstanceData
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    protected function loadInstanceData($strImgFieldIds = '5,55')
    {
        foreach ($this->arrGenForms as $key => $arrPageIds) {
            $arrGenFormPageIds = self::getGenFormPageIds($arrPageIds);
            $this->loadInstanceDataNow($key, $arrGenFormPageIds, $strImgFieldIds);
        }
    }

    /**
     * loadFallbackInstanceData
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    protected function loadFallbackInstanceData($strImgFieldIds = '5,55')
    {
        foreach ($this->arrFallbackGenForms as $key => $arrLanguageIds) {
            foreach ($arrLanguageIds as $intLanguageId => $arrPageIds) {
                $arrGenFormPageIds = self::getGenFormPageIds($arrPageIds);

                $this->objModel->setLanguageId($intLanguageId);
                $this->loadInstanceDataNow($key, $arrGenFormPageIds, $strImgFieldIds);
                $this->objModel->setLanguageId($this->intLanguageId);
            }
        }
    }

    /**
     * getGenFormPageIds
     * @param array $arrPageIds
     * @return array
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    protected static function getGenFormPageIds($arrPageIds)
    {
        $arrGenFormPageIds = array();
        if (count($arrPageIds) > 0) {
            foreach ($arrPageIds as $value) {
                array_push($arrGenFormPageIds, $value);
            }
        }
        return $arrGenFormPageIds;
    }

    /**
     * loadInstanceDataNow
     * @param string $strKey
     * @param array $arrGenFormPageIds
     * @return void
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    protected function loadInstanceDataNow($strKey, $arrGenFormPageIds, $strImgFieldIds = '5,55')
    {

        $intImgFilterTag = ($this->objParentPage instanceof Page && $this->objParentPage->getField('entry_pic_tag') !== null && (int) $this->objParentPage->getFieldValue('entry_pic_tag') > 0) ? $this->objParentPage->getFieldValue('entry_pic_tag') : 0;
        $objPageRowset = $this->objModel->loadItemInstanceDataByIds($strKey, $arrGenFormPageIds, $intImgFilterTag, $strImgFieldIds);

        /**
         * overwrite page entries
         */
        if (isset($objPageRowset) && count($objPageRowset) > 0) {
            foreach ($objPageRowset as $objPageRow) {
                if (array_key_exists($objPageRow->id, $this->arrPageEntries)) {
                    if (is_array($this->arrPageEntries[$objPageRow->id])) {
                        $arrPageEntryContainers = $this->arrPageEntries[$objPageRow->id];
                    } else {
                        $arrPageEntryContainers = array($this->arrPageEntries[$objPageRow->id]);
                    }

                    foreach ($arrPageEntryContainers as $intContainerId) {
                        if (array_key_exists($intContainerId, $this->arrContainer)) {
                            $objPageEntry = $this->arrContainer[$intContainerId]->getPageEntry('entry_' . $objPageRow->id);
                            $objPageEntry->datetime = (isset($objPageRow->datetime)) ? strtotime($objPageRow->datetime) : '';
                            $objPageEntry->shortdescription = (isset($objPageRow->shortdescription)) ? $objPageRow->shortdescription : '';
                            $objPageEntry->read_more_text = (isset($objPageRow->read_more_text)) ? $objPageRow->read_more_text : '';
                            $objPageEntry->description = (isset($objPageRow->description)) ? $objPageRow->description : '';
                            $objPageEntry->slogan = (isset($objPageRow->slogan)) ? $objPageRow->slogan : '';
                            $objPageEntry->filename = (isset($objPageRow->filename)) ? $objPageRow->filename : '';
                            $objPageEntry->fileversion = (isset($objPageRow->fileversion)) ? $objPageRow->fileversion : '';
                            $objPageEntry->filepath = (isset($objPageRow->filepath)) ? $objPageRow->filepath : '';
                            $objPageEntry->filetitle = (isset($objPageRow->filetitle)) ? $objPageRow->filetitle : '';
                            $objPageEntry->start_datetime = (isset($objPageRow->start_datetime)) ? $objPageRow->start_datetime : '';
                            $objPageEntry->end_datetime = (isset($objPageRow->end_datetime)) ? $objPageRow->end_datetime : '';
                            $objPageEntry->external = (isset($objPageRow->external)) ? $objPageRow->external : '';

                            if (isset($objPageRow->tagfilename) && $objPageRow->tagfilename !== null) $objPageEntry->filename = $objPageRow->tagfilename;
                            if (isset($objPageRow->tagfileversion) && $objPageRow->tagfileversion !== null) $objPageEntry->fileversion = $objPageRow->tagfileversion;
                            if (isset($objPageRow->tagfilepath) && $objPageRow->tagfilepath !== null) $objPageEntry->filepath = $objPageRow->tagfilepath;
                            if (isset($objPageRow->tagfiletitle) && $objPageRow->tagfiletitle !== null) $objPageEntry->filetitle = $objPageRow->tagfiletitle;

                            if (isset($objPageRow->categoryId) && $objPageRow->categoryId !== null) {
                                if ($objPageEntry->categories === null) {
                                    $objPageEntry->categories = new stdClass();
                                    $objPageEntry->categories->arr = array($objPageRow->categoryId => $objPageRow->category);
                                } else {
                                    $objPageEntry->categories->arr[$objPageRow->categoryId] = $objPageRow->category;
                                }
                            }

                            if (isset($objPageRow->courseId) && $objPageRow->courseId !== null && date('Ymd', strtotime($objPageRow->start_datetime)) >= date('Ymd')) {
                                if ($objPageEntry->courses === null) {
                                    $objPageEntry->courses = new stdClass();
                                    $objPageEntry->courses->arr = array();
                                }

                                if (!array_key_exists($objPageRow->courseId, $objPageEntry->courses->arr)) {
                                    $objCourse = new stdClass();
                                    $objCourse->id = $objPageRow->courseId;
                                    $objCourse->title = $objPageRow->courseTitle;
                                    $objCourse->start_datetime = strtotime($objPageRow->start_datetime);
                                    $objCourse->location = $objPageRow->location;
                                    $objCourse->speakers = array($objPageRow->speakerId => $objPageRow->speaker);
                                    $objCourse->categories = array($objPageRow->categoryId => $objPageRow->category);

                                    $objPageEntry->courses->arr[$objPageRow->courseId] = $objCourse;
                                } else {
                                    $objPageEntry->courses->arr[$objPageRow->courseId]->speakers[$objPageRow->speakerId] = $objPageRow->speaker;
                                    $objPageEntry->courses->arr[$objPageRow->courseId]->categories[$objPageRow->categoryId] = $objPageRow->category;
                                }
                            }

                            $this->arrContainer[$intContainerId]->addPageEntry($objPageEntry, 'entry_' . $objPageRow->id);
                        }
                    }
                }
            }
        }
    }

    /**
     * getUrlFor
     * @param string $strLanguageCode
     * @param string $strItemUrl
     * @param null|string $strSegmentCode
     * @param null|string $strUrlPrefix
     * @return string
     */
    public function getUrlFor($strLanguageCode, $strItemUrl, $strSegmentCode = null, $strUrlPrefix = null)
    {
        $strUrl = '';

        // url prefix
        if (!empty($strUrlPrefix)) {
            $strUrl .= '/' . strtolower($strUrlPrefix);
        } else if ($this->blnHasUrlPrefix) {
            $strUrl .= '/' . $this->strUrlPrefix;
        }

        // segmentation
        if (!empty($strSegmentCode)) {
            $strUrl .= '/' . strtolower($strSegmentCode);
        } else if ($this->blnHasSegments) {
            $strUrl .= '/' . $this->strSegmentCode;
        }
        $strLanguageFolder = '';
        if ($this->intLanguageDefinitionType == $this->core->config->language_definition->folder) {
            $strLanguageFolder = strtolower($strLanguageCode) . '/';
        } 
        $strUrl .= '/' .$strLanguageFolder . $strItemUrl;

        return $strUrl;
    }

    /**
     * getModel
     * @return Model_Pages|Model_Globals
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModel($blnReset = false, $forcedLanguage = null)
    {
        if ($forcedLanguage == null) {
            if ($this->objModel === null || $blnReset === true) {
                /**
                 * autoload only handles "library" compoennts.
                 * Since this is an application model, we need to require it
                 * from its modules path location.
                 */
                $strModelFilePath = GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . $this->getModelSubPath() . ((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')) . 'ies' : ucfirst($this->strType) . 's') . '.php';
                if (file_exists($strModelFilePath)) {
                    require_once $strModelFilePath;
                    $strModel = 'Model_' . ((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')) . 'ies' : ucfirst($this->strType) . 's');
                    $this->objModel = new $strModel();
                    $this->objModel->setLanguageId($this->intLanguageId);
                    if ($this->blnHasSegments) {
                        $this->objModel->setSegmentId($this->intSegmentId);
                    }
                } else {
                    throw new Exception('Not able to load type specific model, because the file didn\'t exist! - strType: "' . $this->strType . '"');
                }
            }
            return $this->objModel;
        } else {
            $strModelFilePath = GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . $this->getModelSubPath() . ((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')) . 'ies' : ucfirst($this->strType) . 's') . '.php';
            if (file_exists($strModelFilePath)) {
                require_once $strModelFilePath;
                $strModel = 'Model_' . ((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')) . 'ies' : ucfirst($this->strType) . 's');
                $objModel = new $strModel();
                $objModel->setLanguageId($forcedLanguage);
                if ($this->blnHasSegments) {
                    $objModel->setSegmentId($this->intSegmentId);
                }
                return $objModel;
            } else {
                throw new Exception('Not able to load type specific model, because the file didn\'t exist! - strType: "' . $this->strType . '"');
            }
        }
    }

    /**
     * getModelPages
     * @return Model_Pages
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelPages()
    {
        if (null === $this->objModelPages) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/Pages.php';
            $this->objModelPages = new Model_Pages();
            $this->objModelPages->setLanguageId($this->intLanguageId);
        }

        return $this->objModelPages;
    }

    /**
     * getModelGlobals
     * @return Model_Globals
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelGlobals()
    {
        if (null === $this->objModelGlobals) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'global/models/Globals.php';
            $this->objModelGlobals = new Model_Globals();
            $this->objModelGlobals->setLanguageId($this->intLanguageId);
        }

        return $this->objModelGlobals;
    }

    /**
     * getModelFolders
     * @return Model_Folders
     * @author Cornelius Hansjakob <cha@massiveart.com>
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
     * getModelContacts
     * @return Model_Contacts
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelContacts()
    {
        if (null === $this->objModelContacts) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Contacts.php';
            $this->objModelContacts = new Model_Contacts();
            $this->objModelContacts->setLanguageId($this->intLanguageId);
        }

        return $this->objModelContacts;
    }

    /**
     * getModelLocations
     * @return Model_Locations
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelLocations()
    {
        if (null === $this->objModelLocations) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Locations.php';
            $this->objModelLocations = new Model_Locations();
            $this->objModelLocations->setLanguageId($this->intLanguageId);
        }

        return $this->objModelLocations;
    }

    /**
     * getModelCategories
     * @return Model_Categories
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelCategories()
    {
        if (null === $this->objModelCategories) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Categories.php';
            $this->objModelCategories = new Model_Categories();
            $this->objModelCategories->setLanguageId($this->intLanguageId);
        }

        return $this->objModelCategories;
    }

    /**
     * getModelFiles
     * @return Model_Files
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelFiles()
    {
        if (null === $this->objModelFiles) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Files.php';
            $this->objModelFiles = new Model_Files();
            $this->objModelFiles->setLanguageId($this->intLanguageId);
        }

        return $this->objModelFiles;
    }

    /**
     * getModelTags
     * @return Model_Tags
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelTags()
    {
        if (null === $this->objModelTags) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Tags.php';
            $this->objModelTags = new Model_Tags();
            $this->objModelTags->setLanguageId($this->intLanguageId);
        }

        return $this->objModelTags;
    }

    /**
     * getModelUrls
     * @return Model_Urls
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelUrls()
    {
        if (null === $this->objModelUrls) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Urls.php';
            $this->objModelUrls = new Model_Urls();
            $this->objModelUrls->setLanguageId($this->intLanguageId);
        }

        return $this->objModelUrls;
    }

    /**
     * setRootLevelId
     * @param integer $intRootLevelId
     */
    public function setRootLevelId($intRootLevelId)
    {
        $this->intRootLevelId = $intRootLevelId;
    }

    /**
     * getRootLevelId
     * @param integer $intRootLevelId
     */
    public function getRootLevelId()
    {
        return $this->intRootLevelId;
    }

    /**
     * setRootLevelTitle
     * @param stirng $strRootLevelTitle
     */
    public function setRootLevelTitle($strRootLevelTitle)
    {
        $this->strRootLevelTitle = $strRootLevelTitle;
    }

    /**
     * getRootLevelTitle
     * @param string $strRootLevelTitle
     */
    public function getRootLevelTitle()
    {
        return $this->strRootLevelTitle;
    }

    /**
     * setRootLevelAlternativeTitle
     * @param string $strRootLevelAlternativeTitle
     */
    public function setRootLevelAlternativeTitle($strRootLevelAlternativeTitle)
    {
        $this->strRootLevelAlternativeTitle = $strRootLevelAlternativeTitle;
    }

    /**
     * getRootLevelAlternativeTitle
     * @return string
     */
    public function getRootLevelAlternativeTitle()
    {
        return $this->strRootLevelAlternativeTitle;
    }

    /**
     * setRootLevelGroupId
     * @param integer $intRootLevelGroupId
     */
    public function setRootLevelGroupId($intRootLevelGroupId)
    {
        $this->intRootLevelGroupId = $intRootLevelGroupId;
    }

    /**
     * getRootLevelGroupId
     * @param integer $intRootLevelGroupId
     */
    public function getRootLevelGroupId()
    {
        return $this->intRootLevelGroupId;
    }

    /**
     * setElementId
     * @param integer $intElementId
     */
    public function setElementId($intElementId)
    {
        $this->intElementId = $intElementId;
    }

    /**
     * getElementId
     * @param integer $intElementId
     */
    public function getElementId()
    {
        return $this->intElementId;
    }

    /**
     * setElementLinkId
     * @param integer $intElementLinkId
     */
    public function setElementLinkId($intElementLinkId)
    {
        $this->intElementLinkId = $intElementLinkId;
    }

    /**
     * getElementLinkId
     * @param integer $intElementLinkId
     */
    public function getElementLinkId()
    {
        return $this->intElementLinkId;
    }

    /**
     * setPageId
     * @param stirng $strPageId
     */
    public function setPageId($strPageId)
    {
        $this->strPageId = $strPageId;
    }

    /**
     * getPageId
     * @param string $strPageId
     */
    public function getPageId()
    {
        return $this->strPageId;
    }

    /**
     * setPageLinkId
     * @param string $strPageLinkId
     */
    public function setPageLinkId($strPageLinkId)
    {
        $this->strPageLinkId = $strPageLinkId;
    }

    /**
     * getPageLinkId
     * @param string $strPageLinkId
     */
    public function getPageLinkId()
    {
        return $this->strPageLinkId;
    }

    /**
     * setPageVersion
     * @param integer $intPageVersion
     */
    public function setPageVersion($intPageVersion)
    {
        $this->intPageVersion = $intPageVersion;
    }

    /**
     * getPageVersion
     * @param integer $intPageVersion
     */
    public function getPageVersion()
    {
        return $this->intPageVersion;
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
     * setHasSegments
     * @param boolean $blnHasSegments
     */
    public function setHasSegments($blnHasSegments, $blnValidate = true)
    {
        if ($blnValidate == true) {
            if ($blnHasSegments === true || $blnHasSegments === 'true' || $blnHasSegments == 1) {
                $this->blnHasSegments = true;
            } else {
                $this->blnHasSegments = false;
            }
        } else {
            $this->blnHasSegments = $blnHasSegments;
        }
    }

    /**
     * getHasSegments
     * @return boolean $blnHasSegments
     */
    public function getHasSegments($blnReturnAsNumber = true)
    {
        if ($blnReturnAsNumber == true) {
            if ($this->blnHasSegments == true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return $this->blnHasSegments;
        }
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

    /**
     * setSegmentCode
     * @param string $strSegmentCode
     */
    public function setSegmentCode($strSegmentCode)
    {
        $this->strSegmentCode = $strSegmentCode;
    }

    /**
     * getSegmentCode
     * @param string $strSegmentCode
     */
    public function getSegmentCode()
    {
        return $this->strSegmentCode;
    }

    /**
     * setHasUrlPrefix
     * @param boolean $blnHasUrlPrefix
     */
    public function setHasUrlPrefix($blnHasUrlPrefix, $blnValidate = true)
    {
        if ($blnValidate == true) {
            if ($blnHasUrlPrefix === true || $blnHasUrlPrefix === 'true' || $blnHasUrlPrefix == 1) {
                $this->blnHasUrlPrefix = true;
            } else {
                $this->blnHasUrlPrefix = false;
            }
        } else {
            $this->blnHasUrlPrefix = $blnHasUrlPrefix;
        }
    }

    /**
     * getHasUrlPrefix
     * @return boolean $blnHasUrlPrefix
     */
    public function getHasUrlPrefix($blnReturnAsNumber = true)
    {
        if ($blnReturnAsNumber == true) {
            if ($this->blnHasUrlPrefix == true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return $this->blnHasUrlPrefix;
        }
    }

    /**
     * setUrlPrefix
     * @param string $strUrlPrefix
     */
    public function setUrlPrefix($strUrlPrefix)
    {
        $this->strUrlPrefix = $strUrlPrefix;
    }

    /**
     * getUrlPrefix
     * @param string $strUrlPrefix
     */
    public function getUrlPrefix()
    {
        return $this->strUrlPrefix;
    }

    /**
     * setLanguageCode
     * @param string $strLanguageCode
     */
    public function setLanguageCode($strLanguageCode)
    {
        $this->strLanguageCode = $strLanguageCode;
    }

    /**
     * getLanguageCode
     * @param string $strLanguageCode
     */
    public function getLanguageCode()
    {
        return $this->strLanguageCode;
    }

    /**
     * setType
     * @param string $strType
     */
    public function setType($strType)
    {
        $this->strType = $strType;
    }

    /**
     * getType
     * @param string $strType
     */
    public function getType()
    {
        return $this->strType;
    }

    /**
     * setModelSubPath
     * @param string $strModelSubPath
     */
    public function setModelSubPath($strModelSubPath)
    {
        $this->strModelSubPath = $strModelSubPath;
    }

    /**
     * getModelSubPath
     * @param string $strModelSubPath
     */
    public function getModelSubPath()
    {
        return $this->strModelSubPath;
    }

    /**
     * setTemplateFile
     * @param stirng $strTemplateFile
     */
    public function setTemplateFile($strTemplateFile)
    {
        $this->strTemplateFile = $strTemplateFile;
    }

    /**
     * getTemplateFile
     * @return string $strTemplateFile
     */
    public function getTemplateFile()
    {
        return $this->strTemplateFile;
    }

    /**
     * setTemplateId
     * @param integer $intTemplateId
     */
    public function setTemplateId($intTemplateId)
    {
        $this->intTemplateId = $intTemplateId;
    }

    /**
     * getTemplateId
     * @return integer $intTemplateId
     */
    public function getTemplateId()
    {
        return $this->intTemplateId;
    }

    /**
     * setTemplateCacheLifetime
     * @param integer $intTemplateCacheLifetime
     */
    public function setTemplateCacheLifetime($intTemplateCacheLifetime)
    {
        $this->intTemplateCacheLifetime = $intTemplateCacheLifetime;
    }

    /**
     * getTemplateCacheLifetime
     * @return integer $intTemplateCacheLifetime
     */
    public function getTemplateCacheLifetime()
    {
        return $this->intTemplateCacheLifetime;
    }

    /**
     * setTemplateRenderScript
     * @param stirng $strTemplateRenderScript
     */
    public function setTemplateRenderScript($strTemplateRenderScript)
    {
        $this->strTemplateRenderScript = $strTemplateRenderScript;
    }

    /**
     * getTemplateRenderScript
     * @return string $strTemplateRenderScript
     */
    public function getTemplateRenderScript()
    {
        return $this->strTemplateRenderScript;
    }

    /**
     * setPublisherName
     * @param stirng $strPublisherName
     */
    public function setPublisherName($strPublisherName)
    {
        $this->strPublisherName = $strPublisherName;
    }

    /**
     * getPublisherName
     * @param string $strPublisherName
     */
    public function getPublisherName()
    {
        return $this->strPublisherName;
    }

    /**
     * setChangeUserName
     * @param stirng $strChangeUserName
     */
    public function setChangeUserName($strChangeUserName)
    {
        $this->strChangeUserName = $strChangeUserName;
    }

    /**
     * getChangeUserName
     * @param string $strChangeUserName
     */
    public function getChangeUserName()
    {
        return $this->strChangeUserName;
    }

    /**
     * setCreatorName
     * @param stirng $strCreatorName
     */
    public function setCreatorName($strCreatorName)
    {
        $this->strCreatorName = $strCreatorName;
    }

    /**
     * getCreatorName
     * @param string $strCreatorName
     */
    public function getCreatorName()
    {
        return $this->strCreatorName;
    }

    /**
     * setTypeId
     * @param integer $intTypeId
     */
    public function setTypeId($intTypeId)
    {
        $this->intTypeId = $intTypeId;
    }

    /**
     * getTypeId
     * @param integer $intTypeId
     */
    public function getTypeId()
    {
        return $this->intTypeId;
    }

    /**
     * setParentId
     * @param integer $intParentId
     */
    public function setParentId($intParentId)
    {
        $this->intParentId = $intParentId;
    }

    /**
     * getParentId
     * @param integer $intParentId
     */
    public function getParentId()
    {
        return $this->intParentId;
    }

    /**
     * setParentTypeId
     * @param integer $intParentTypeId
     */
    public function setParentTypeId($intParentTypeId)
    {
        $this->intParentTypeId = $intParentTypeId;
    }

    /**
     * getParentTypeId
     * @param integer $intParentTypeId
     */
    public function getParentTypeId()
    {
        return $this->intParentTypeId;
    }

    /**
     * setNavParentId
     * @param integer $intNavParentId
     */
    public function setNavParentId($intNavParentId)
    {
        $this->intNavParentId = $intNavParentId;
    }

    /**
     * getNavParentId
     * @param integer $intNavParentId
     */
    public function getNavParentId()
    {
        return $this->intNavParentId;
    }

    /**
     * setNavParentTypeId
     * @param integer $intNavParentTypeId
     */
    public function setNavParentTypeId($intNavParentTypeId)
    {
        $this->intNavParentTypeId = $intNavParentTypeId;
    }

    /**
     * getNavParentTypeId
     * @param integer $intNavParentTypeId
     */
    public function getNavParentTypeId()
    {
        return $this->intNavParentTypeId;
    }

    /**
     * setIsStartElement
     * @param boolean $blnIsStartPage
     */
    public function setIsStartElement($blnIsStartPage, $blnValidate = true)
    {
        if ($blnValidate == true) {
            if ($blnIsStartPage === true || $blnIsStartPage === 'true' || $blnIsStartPage == 1) {
                $this->blnIsStartPage = true;
            } else {
                $this->blnIsStartPage = false;
            }
        } else {
            $this->blnIsStartPage = $blnIsStartPage;
        }
    }

    /**
     * getIsStartElement
     * @return boolean $blnIsStartPage
     */
    public function getIsStartElement($blnReturnAsNumber = true)
    {
        if ($blnReturnAsNumber == true) {
            if ($this->blnIsStartPage == true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return $this->blnIsStartPage;
        }
    }

    /**
     * setShowInNavigation
     * @param boolean $blnShowInNavigation
     */
    public function setShowInNavigation($blnShowInNavigation, $blnValidate = true)
    {
        if ($blnValidate == true) {
            if ($blnShowInNavigation === true || $blnShowInNavigation === 'true' || $blnShowInNavigation == 1) {
                $this->blnShowInNavigation = true;
            } else {
                $this->blnShowInNavigation = false;
            }
        } else {
            $this->blnShowInNavigation = $blnShowInNavigation;
        }
    }

    /**
     * getShowInNavigation
     * @return boolean $blnShowInNavigation
     */
    public function getShowInNavigation($blnReturnAsNumber = true)
    {
        if ($blnReturnAsNumber == true) {
            if ($this->blnShowInNavigation == true) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return $this->blnShowInNavigation;
        }
    }

    /**
     * setStatus
     * @param integer $intStatus
     */
    public function setStatus($intStatus)
    {
        $this->intStatus = $intStatus;
    }

    /**
     * setStatus
     * @param integer $intStatus
     */
    public function getStatus()
    {
        return $this->intStatus;
    }

    /**
     * setPublishDate
     * @param string/obj $Date
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function setPublishDate($Date, $blnIsValidDateObj = false)
    {
        if ($blnIsValidDateObj == true) {
            $this->objPublishDate = $Date;
        } else {
            $arrTmpTimeStamp = explode(' ', $Date);
            if (count($arrTmpTimeStamp) > 1) {
                $arrTmpTime = explode(':', $arrTmpTimeStamp[1]);
                $arrTmpDate = explode('-', $arrTmpTimeStamp[0]);
                if (count($arrTmpDate) == 3) {
                    $this->objPublishDate = mktime($arrTmpTime[0], $arrTmpTime[1], $arrTmpTime[2], $arrTmpDate[1], $arrTmpDate[2], $arrTmpDate[0]);
                }
            }
        }
    }

    /**
     * getPublishDate
     * @param string $strFormat
     * @return string $strPublishDate
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getPublishDate($strFormat = 'd.m.Y', $blnGetDateObj = false)
    {
        if ($blnGetDateObj == true) {
            return $this->objPublishDate;
        } else {
            if ($this->objPublishDate != null) {
                return date($strFormat, $this->objPublishDate);
            } else {
                return null;
            }
        }
    }

    /**
     * setChangeDate
     * @param string/obj $Date
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function setChangeDate($Date, $blnIsValidDateObj = false)
    {
        if ($blnIsValidDateObj == true) {
            $this->objChangeDate = $Date;
        } else {
            $arrTmpTimeStamp = explode(' ', $Date);
            if (count($arrTmpTimeStamp) > 1) {
                $arrTmpTime = explode(':', $arrTmpTimeStamp[1]);
                $arrTmpDate = explode('-', $arrTmpTimeStamp[0]);
                if (count($arrTmpDate) == 3) {
                    $this->objChangeDate = mktime($arrTmpTime[0], $arrTmpTime[1], $arrTmpTime[2], $arrTmpDate[1], $arrTmpDate[2], $arrTmpDate[0]);
                }
            }
        }
    }

    /**
     * getChangeDate
     * @param string $strFormat
     * @return string $strChangeDate
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getChangeDate($strFormat = 'd.m.Y', $blnGetDateObj = false)
    {
        if ($blnGetDateObj == true) {
            return $this->objChangeDate;
        } else {
            if ($this->objChangeDate != null) {
                return date($strFormat, $this->objChangeDate);
            } else {
                return null;
            }
        }
    }

    /**
     * setCreateDate
     * @param string/obj $Date
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function setCreateDate($Date, $blnIsValidDateObj = false)
    {
        if ($blnIsValidDateObj == true) {
            $this->objCreateDate = $Date;
        } else {
            $arrTmpTimeStamp = explode(' ', $Date);
            if (count($arrTmpTimeStamp) > 1) {
                $arrTmpTime = explode(':', $arrTmpTimeStamp[1]);
                $arrTmpDate = explode('-', $arrTmpTimeStamp[0]);
                if (count($arrTmpDate) == 3) {
                    $this->objCreateDate = mktime($arrTmpTime[0], $arrTmpTime[1], $arrTmpTime[2], $arrTmpDate[1], $arrTmpDate[2], $arrTmpDate[0]);
                }
            }
        }
    }

    /**
     * getCreateDate
     * @param string $strFormat
     * @return string $strCreateDate
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function getCreateDate($strFormat = 'd.m.Y', $blnGetDateObj = false)
    {
        if ($blnGetDateObj == true) {
            return $this->objCreateDate;
        } else {
            if ($this->objCreateDate != null) {
                return date($strFormat, $this->objCreateDate);
            } else {
                return null;
            }
        }
    }

    /**
     * setChildPage
     * @param Page $objChildPage
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function setChildPage(Page $objChildPage)
    {
        $this->objChildPage = $objChildPage;
    }

    /**
     * setBaseUrl
     * @param $objBaseUrl
     */
    public function setBaseUrl(Zend_Db_Table_Row_Abstract $objBaseUrl)
    {
        $this->objBaseUrl = $objBaseUrl;
    }
    
    /**
     * setLanguageDefinitionType
     * @param int $intLanguageDefinitionType
     */
    public function setLanguageDefinitionType($intLanguageDefinitionType)
    {
        $this->intLanguageDefinitionType = $intLanguageDefinitionType;
    }

    /**
     * getLanguageDefinitionType
     * @return int intLanguageDefinitionType
     */
    public function getLanguageDefinitionType()
    {
        return $this->intLanguageDefinitionType;
    }

}

?>
