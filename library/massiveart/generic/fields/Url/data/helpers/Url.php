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
 * @package    library.massiveart.generic.fields.Url.data.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * GenericDataHelperUrl
 *
 * Helper to save and load the "url" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-06: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.data.helpers
 * @subpackage GenericDataHelper_Url
 */

require_once(dirname(__FILE__) . '/../../../../data/helpers/Abstract.php');

class GenericDataHelper_Url extends GenericDataHelperAbstract
{
    /**
     * @var Model_Pages|Model_Globals
     */
    private $objModel;

    /**
     * @var string
     */
    private $strType;

    /**
     * @var Model_Urls
     */
    private $objModelUrls;

    /**
     * @var Model_Folders
     */
    private $objModelFolders;

    /**
     * @var Model_Utilities
     */
    private $objModelUtilities;

    /**
     * @var string
     */
    private $strUrl;

    /**
     * @var string
     */
    private $strUrlPrefix;

    /**
     * @var string
     */
    private $strParentPageUrl;

    /**
     * save()
     * @param integer $intElementId
     * @param string $strType
     * @param string $strElementId
     * @param int $intVersion
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function save($intElementId, $strType, $strElementId = null, $intVersion = null)
    {
        try {
            /*
             * fix for saving products in "All Products" View 
             */
            if (!isset($_POST[$this->objElement->name . '_PreventSaving']) || $_POST[$this->objElement->name . '_PreventSaving'] != 'true') {

                $this->strType = $strType;

                $this->getModel();
                $this->getModelUrls();

                $objItemData = $this->objModel->load($intElementId);

                if (count($objItemData) > 0) {
                    $objItem = $objItemData->current();

                    $resourceLocator = new UniformResourceLocator($this->core->config->url_layout, $this->getModelUrls());
                    $resourceLocator->setReplacers($this->getModelUtilities()->loadPathReplacers())
                        ->setRootLevelId($this->objElement->Setup()->getRootLevelId())
                        ->setFormType($this->objElement->Setup()->getFormType())
                        ->setParents($this->getParentFolders($objItem))
                        ->setPrefix($this->getParentUrl($objItem))
                        ->setIsStartElement($this->objElement->Setup()->getIsStartElement(false))
                        ->setParentId($this->objElement->Setup()->getParentId());

                    $objUrlData = $this->objModelUrls->loadUrl($objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);

                    // update url
                    if (count($objUrlData) > 0) {
                        // new desired url
                        if (isset($_POST[$this->objElement->name . '_EditableUrl'])) {
                            $resourceLocator->setPath(strtolower($_POST[$this->objElement->name . '_EditableUrl']));

                            $objUrl = $objUrlData->current();

                            if (strcmp($resourceLocator->get(false), $objUrl->url) !== 0) {

                                // set all page urls to isMain 0
                                $this->objModelUrls->resetIsMainUrl($objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);
                                $this->objModelUrls->insertUrl($resourceLocator->get(), $objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);

                                // change child urls if url layout is tree
                                if ($this->core->config->url_layout == UniformResourceLocator::LAYOUT_TREE) {
                                    var_dump($this->objElement->Setup()->getIsStartElement(false));
                                    if ($this->objElement->Setup()->getIsStartElement(false) == true) {
                                        $arrChildData = $this->getModel()->getChildUrls($this->objElement->Setup()->getParentId());
                                        if (count($arrChildData) > 0) {
                                            foreach ($arrChildData as $objChild) {
                                                if ($objChild->relationId != $objItem->relationId) {
                                                    $this->objModelUrls->resetIsMainUrl($objChild->relationId, $objChild->version, $this->core->sysConfig->url_types->$strType);
                                                    $this->objModelUrls->insertUrl($resourceLocator->makeUnique(str_replace($objUrl->url, $resourceLocator->get(), $objChild->url)), $objChild->relationId, $objChild->version, $this->core->sysConfig->url_types->$strType);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                    } else { // new url
                        if (!($this->objElement->Setup()->getIsStartElement(false) && $this->objElement->Setup()->getParentId() === null)) {
                            $objFieldData = $this->objElement->Setup()->getModelGenericForm()->loadFieldsWithPropery($this->core->sysConfig->fields->properties->url_field, $this->objElement->Setup()->getGenFormId());

                            if (count($objFieldData) > 0) {
                                foreach ($objFieldData as $objField) {
                                    if ($this->objElement->Setup()->getRegion($objField->regionId)->getField($objField->name)->getValue() != '') {
                                        $resourceLocator->setPath(str_replace('/', '-', $this->objElement->Setup()->getRegion($objField->regionId)->getField($objField->name)->getValue()));
                                        break;
                                    }
                                }
                            }
                        }

                        $this->objModelUrls->insertUrl($resourceLocator->get(), $objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);
                    }
                }

                $this->load($intElementId, $strType, $strElementId, $intVersion);

            } // end of dirty fix

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * removeUrlHistory()
     * @param integer $intElementId
     * @param string $strType
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function removeUrlHistory($intElementId, $strType)
    {
        try {
            $this->strType = $strType;

            $this->getModel();
            $this->getModelUrls();

            $objItemData = $this->objModel->load($intElementId);
            if (count($objItemData) > 0) {
                $objItem = $objItemData->current();
                $this->getModelUrls()->removeUrlHistory($objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);
            }
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * load()
     * @param integer $intElementId
     * @param string $strType
     * @param string $strElementId
     * @param int $intVersion
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function load($intElementId, $strType, $strElementId = null, $intVersion = null)
    {
        try {
            $this->strType = $strType;

            $this->getModel();
            $this->getModelUrls();

            $objItemData = $this->objModel->load($intElementId);

            if (count($objItemData) > 0) {
                $objItem = $objItemData->current();

                $objUrlData = $this->objModelUrls->loadUrl($objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);

                if (count($objUrlData) > 0) {
                    $objUrl = $objUrlData->current();

                    if ($this->objElement->Setup()->getLanguageDefinitionType() == $this->core->config->language_definition->folder) {
                        $this->objElement->setValue('/' . strtolower($objUrl->languageCode) . '/' . $objUrl->url);
                    } else {
                        $this->objElement->setValue('/' . $objUrl->url);
                    }

                    $this->objElement->url = $objUrl->url;
                    $this->objElement->languageCode = $objUrl->languageCode;
                }
            }

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
        }
    }

    /**
     * @param $objItem
     * @return array
     */
    protected function getParentFolders($objItem)
    {
        $objParentFolders = array();
        if ($objItem->idParentTypes == $this->core->sysConfig->parent_types->folder) {
            switch ($this->objElement->Setup()->getFormTypeId()) {
                case $this->core->sysConfig->form->types->page:
                    $objParentFolders = $this->getModelFolders()->loadParentFolders($objItem->idParent);
                    break;
                case $this->core->sysConfig->form->types->global:
                    $objParentFolders = $this->getModelFolders()->loadGlobalParentFolders($objItem->idParent, $this->objElement->Setup()->getRootLevelGroupId());

                    // convention: remove last (top) folder
                    $arrTmpParentFolders = array();
                    foreach ($objParentFolders as $objParentFolder) {
                        $arrTmpParentFolders[] = $objParentFolder;
                    }
                    array_pop($arrTmpParentFolders);
                    $objParentFolders = $arrTmpParentFolders;
                    break;
            }
        }

        return $objParentFolders;
    }

    /**
     * @param $objItem
     * @return array
     */
    protected function getParentUrl($objItem)
    {
        $strParentUrl = '';

        if ($objItem->idParentTypes == $this->core->sysConfig->parent_types->folder) {
            $objParentFolderData = $this->objModel->loadParentUrl($objItem->id, $this->objElement->Setup()->getIsStartElement(false));

            if (count($objParentFolderData) > 0) {
                $objParentFolderUrl = $objParentFolderData->current();
                $strParentUrl = $objParentFolderUrl->url;
            }
        }

        return $strParentUrl;
    }

    /**
     * setType
     * @param string $strType
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function setType($strType)
    {
        $this->strType = $strType;
    }

    /**
     * @return Model_Pages|Model_Products
     * @throws Exception
     */
    protected function getModel()
    {
        if ($this->objModel === null) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            $strModelFilePath = GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . $this->objElement->Setup()->getModelSubPath() . ((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')) . 'ies' : ucfirst($this->strType) . 's') . '.php';
            $this->core->logger->debug($strModelFilePath);
            if (file_exists($strModelFilePath)) {
                require_once $strModelFilePath;
                $strModel = 'Model_' . ((substr($this->strType, strlen($this->strType) - 1) == 'y') ? ucfirst(rtrim($this->strType, 'y')) . 'ies' : ucfirst($this->strType) . 's');
                $this->objModel = new $strModel();
                $this->objModel->setLanguageId($this->objElement->Setup()->getLanguageId());
            } else {
                throw new Exception('Not able to load type specific model, because the file didn\'t exist! - strType: "' . $this->strType . '"');
            }
        }
        return $this->objModel;
    }

    /**
     * @return Model_Urls
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
            $this->objModelUrls->setLanguageId($this->objElement->Setup()->getLanguageId());
        }

        return $this->objModelUrls;
    }

    /**
     * @return Model_Folders
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
            $this->objModelFolders->setLanguageId($this->objElement->Setup()->getLanguageId());
        }

        return $this->objModelFolders;
    }

    /**
     * getModelUtilities
     * @return Model_Utilities
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
            $this->objModelUtilities->setLanguageId($this->objElement->Setup()->getLanguageId());
        }

        return $this->objModelUtilities;
    }
}
