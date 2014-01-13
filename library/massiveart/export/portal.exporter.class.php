<?php

class PortalExporter {

    protected $core;

    /**
     * @var int
     */
    protected $rootLevelId;

    /**
     * @var int
     */
    protected $languageId;

    /**
     * @var GenericForm
     */
    protected $objForm;

    /**
     * @var Array
     */
    protected $fieldKeys = array();

    // Databasemodels

    /**
     * @var null|Model_Pages
     */
    protected $objModelPages = null;

    /**
     * @param $core
     * @param $rootLevelId
     * @param $languageId
     */
    public function __construct($core, $rootLevelId, $languageId)
    {
        $this->core = $core;
        $this->rootLevelId = $rootLevelId;
        $this->languageId = $languageId;
    }

    /**
     * @param bool $addAllKeys
     * @return array
     */
    public function getAllPages($addAllKeys = false)
    {
        $pageDatas = $this->getModelPages()->getPages($this->rootLevelId);

        $pages = array();
        foreach ($pageDatas as $pageData) {
            array_push($pages, $this->getPageAsArray($pageData));
        }

        if ($addAllKeys) {
            $pages = $this->addKeys($pages);
        }

        return $pages;
    }

    /**
     * @param $pageData
     * @return array
     */
    protected function getPageAsArray($pageData)
    {
        $this->loadPage($pageData);
        return $this->convertPageToFlatArray($pageData);
    }

    /**
     * @param $pageData
     * @return array
     */
    protected function loadPage($pageData)
    {
        $this->loadForm($pageData);
        $this->objForm->loadFormData();
        $this->addPageSpecificFormElements();
        $this->objForm->setAction('/zoolu/cms/page/edit');
        $this->objForm->prepareForm();

        return $this->objForm;

    }

    protected function loadForm($pageData)
    {
        $objFormHandler = FormHandler::getInstance();
        $objFormHandler->setFormId($pageData->genericFormId);
        $objFormHandler->setTemplateId($pageData->idTemplates);
        $objFormHandler->setFormVersion($pageData->genericFormVersion);
        $objFormHandler->setActionType($this->core->sysConfig->generic->actions->edit); // TODO reading = edit
        $objFormHandler->setLanguageId($pageData->languageId);
        $objFormHandler->setLanguageCode($pageData->languageCode);
        $objFormHandler->setFormLanguageId($pageData->languageId);
        $objFormHandler->setElementId($pageData->id);

        $this->objForm = $objFormHandler->getGenericForm();

        $this->objForm->Setup()->setCreatorId($pageData->creator);
        $this->objForm->Setup()->setStatusId($pageData->idStatus);
        $this->objForm->Setup()->setRootLevelId($this->rootLevelId);
        $this->objForm->Setup()->setParentId($pageData->parentId);
        $this->objForm->Setup()->setIsStartElement($pageData->isStartPage);
        $this->objForm->Setup()->setLanguageDefinitionType($pageData->languageDefinitionType);
        $this->objForm->Setup()->setPublishDate($pageData->published != null ? $pageData->published : date('Y-m-d H:i:s'));
        $this->objForm->Setup()->setShowInNavigation($pageData->showInNavigation);
        $this->objForm->Setup()->setDestinationId($pageData->idDestination);
        $this->objForm->Setup()->setSegmentId($pageData->idSegments);
        $this->objForm->Setup()->setHideInSitemap($pageData->hideInSitemap);
        $this->objForm->Setup()->setShowInWebsite($pageData->showInWebsite);
        $this->objForm->Setup()->setShowInTablet($pageData->showInTablet);
        $this->objForm->Setup()->setShowInMobile($pageData->showInMobile);
        $this->objForm->Setup()->setElementTypeId($pageData->idPageTypes);
        $this->objForm->Setup()->setParentTypeId($pageData->idParentTypes);
        $this->objForm->Setup()->setModelSubPath('cms/models/');
    }

    protected function addPageSpecificFormElements()
    {
        $this->objForm->addElement('hidden', 'creator', array('value' => $this->objForm->Setup()->getCreatorId(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'idStatus', array('value' => $this->objForm->Setup()->getStatusId(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'rootLevelId', array('value' => $this->objForm->Setup()->getRootLevelId(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'rootLevelTypeId', array('value' => $this->objForm->Setup()->getRootLevelTypeId(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'parentFolderId', array('value' => $this->objForm->Setup()->getParentId(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'pageTypeId', array('value' => $this->objForm->Setup()->getElementTypeId(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'isStartPage', array('value' => $this->objForm->Setup()->getIsStartElement(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'languageDefinitionType', array('value' => $this->objForm->Setup()->getLanguageDefinitionType(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'publishDate', array('value' => $this->objForm->Setup()->getPublishDate('Y-m-d H:i:s'), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'showInNavigation', array('value' => $this->objForm->Setup()->getShowInNavigation(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'destinationId', array('value' => $this->objForm->Setup()->getDestinationId(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'segmentId', array('value' => $this->objForm->Setup()->getSegmentId(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'hideInSitemap', array('value' => $this->objForm->Setup()->getHideInSitemap(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'showInWebsite', array('value' => $this->objForm->Setup()->getShowInWebsite(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'showInTablet', array('value' => $this->objForm->Setup()->getShowInTablet(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'showInMobile', array('value' => $this->objForm->Setup()->getShowInMobile(), 'decorators' => array('Hidden')));
        $this->objForm->addElement('hidden', 'parentTypeId', array('value' => $this->objForm->Setup()->getParentTypeId(), 'decorators' => array('Hidden')));
    }

    /**
     * @param $pageData
     * @return array
     */
    protected function convertPageToFlatArray($pageData)
    {
        $tabs =  $this->objForm->Setup()->Tabs();

        $page = array();
        if (count($tabs)) {
            /**
             * @var GenericElementTab $objTab
             */
            foreach ($tabs as $objTab)
            {
                if (count($objTab->Regions())) {
                    /**
                     * @var GenericElementRegion $objRegion
                     */
                    foreach ($objTab->Regions() as $objRegion) {
                        $regionId = $objRegion->getRegionId();
                        if ($objRegion->getRegionIsMultiply() == true) {
                            if (count($objRegion->RegionInstanceIds()) > 0) {
                                foreach ($objRegion->RegionInstanceIds() as $intRegionInstanceId) {
                                    /**
                                     * @var GenericElementField $objField
                                     */
                                    foreach ($objRegion->getFields() as $objField) {

                                        $value = $objField->getValue();
                                        if (is_string($value) || is_int($value)) {
                                            $key = 'MultiRegion_' . $regionId . '_' . $intRegionInstanceId . '_none' . '__' . $objField->name;
                                        } else {
                                            $key = 'MultiRegion_' . $regionId . '_' . $intRegionInstanceId . '_unserialized' . '__' . $objField->name;
                                            $value = serialize($value);
                                        }
                                        $page[$key] = $value;
                                        $this->addFieldKey($key);
                                    }
                                }
                            }
                        } else {
                            /**
                             * @var GenericElementField $objField
                             */
                            if (count($objRegion->getFields())) {
                                foreach ($objRegion->getFields() as $objField) {
                                    $value = $objField->getValue();
                                    if (is_string($value) || is_int($value)) {
                                        $key = 'Field_none__' .$objField->name;
                                    } else {
                                        $key = 'Field_unserialized__' .$objField->name;
                                        $value = serialize($value);
                                    }
                                    $page[$key] = $value;
                                    $this->addFieldKey($key);
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($page)) {
            foreach ($pageData as $key => $value) {
                $key = 'StaticField_none__' . $key;
                $page[$key] = $value;
                $this->addFieldKey($key);
            }
        }

        return $page;
    }

    /**
     * @param string $delimiter
     * @param string $enclosure
     * @return string
     */
    public function getAsCsv($delimiter = ',', $enclosure = '"')
    {
        $pages = $this->getAllPages(false);
        return $this->convertToCsv($pages, $delimiter, $enclosure);
    }

    /**
     * @param $pages
     * @return array
     */
    protected function addKeys($pages)
    {
        $pagesData = array();
        if (count($pages)) {
            foreach ($pages as $page)
            {
                $pagesData[] = $this->addKeysToPage($page);
            }
        }
        return $pagesData;
    }

    /**
     * @param $page
     * @return array
     */
    protected function addKeysToPage($page)
    {
        $pageData = array();
        foreach ($this->fieldKeys as $fieldKey) {
            $value = '';
            if (isset($page[$fieldKey])) {
                $value = $page[$fieldKey];
            }
            $pageData[$fieldKey] = $value;
        }
        return $pageData;
    }

    /**
     * @param $pages
     * @param string $delimiter
     * @param string $enclosure
     * @return string
     */
    protected function convertToCsv($pages, $delimiter = ',', $enclosure = '"')
    {
        $csv = '';

        if (count($pages)) {
            foreach ($this->fieldKeys as $key) {
                $csv .= $enclosure . addslashes($key) . $enclosure . $delimiter;
            }
            $csv = trim($csv, $delimiter) . "\r\n";
            foreach ($pages as $page) {
                $page = $this->addKeysToPage($page);
                foreach ($this->fieldKeys as $key) {
                    $csv .= $enclosure . addslashes($page[$key]) . $enclosure . $delimiter;
                }
                $csv = trim($csv, $delimiter) . "\r\n";
            }
        }

        return $csv;
    }

    protected function addFieldKey ($key)
    {
        if (!in_array($key, $this->fieldKeys)) {
            array_push ($this->fieldKeys, $key);
        }
    }

    /**
     * @param int $languageId
     * @return Exporter
     */
    public function setLanguageId($languageId)
    {
        $this->languageId = $languageId;
        return $this;
    }

    /**
     * @return int
     */
    public function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * @param int $rootLevelId
     * @return Exporter
     */
    public function setRootLevelId($rootLevelId)
    {
        $this->rootLevelId = $rootLevelId;
        return $this;
    }

    /**
     * @return int
     */
    public function getRootLevelId()
    {
        return $this->rootLevelId;
    }

    /**
     * getModelPages
     * @author Cornelius Hansjakob <cha@massiveart.com>
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
            $this->objModelPages->setLanguageId($this->languageId);
        }

        return $this->objModelPages;
    }
}