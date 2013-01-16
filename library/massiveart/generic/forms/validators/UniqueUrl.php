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
 * @package    library.massiveart.generic.forms.validators
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

require_once(dirname(__FILE__) . '/Abstract.php');

/**
 * Form_Validator_UniqueUrl
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-09-20: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */
class Form_Validator_UniqueUrl extends Form_Validator_Abstract
{
    /**
     * @var Model_Urls
     */
    protected $objModelUrls;

    /**
     * @var Model_Globals
     */
    protected $objModelGlobals;

    /**
     * @var Model_Pages
     */
    protected $objModelPages;

    /**
     * @var Model_Folders
     */
    protected $objModelFolders;

    /**
     * @var Model_Utilities
     */
    protected $objModelUtilities;

    /**
     * @var array
     */
    protected $_arrMessages;

    /**
     * getMessages
     * @see Zend_Validate_Interface
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getMessages()
    {
        return $this->_arrMessages;
    }

    /**
     * addMessage
     * @param string $strKey
     * @param string $strMessage
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function addMessage($strKey, $strMessage)
    {
        $this->_arrMessages[$strKey] = $strMessage;
    }

    /**
     * isValid
     * @see Zend_Validate_Interface
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function isValid($value)
    {
        $isValid = true;
        $path = strtolower($value);
        $pathWithoutLanguage = ltrim(preg_replace('/^\/([a-zA-Z]{2}|[a-zA-Z]{2}\-[a-zA-Z]{2})\//', '', $value), '/');

        //Load data
        $objItemData = null;
        $strType = null;
        $intElementId = ($this->Setup()->getElementLinkId() && $this->Setup()->getElementLinkId() != -1) ? $this->Setup()->getElementLinkId() : $this->Setup()->getElementId();
        if ($intElementId) {
            switch ($this->Setup()->getFormTypeId()) {
                case $this->core->sysConfig->form->types->page:
                    $strType = 'page';
                    $objItemData = $this->getModelPages()->load($intElementId);
                    break;
                case $this->core->sysConfig->form->types->global:
                    $strType = 'global';
                    $objItemData = $this->getModelGlobals()->load($intElementId);
                    break;
            }
        }

        $resourceLocator = new UniformResourceLocator($this->core->config->url_layout, $this->getModelUrls());
        $resourceLocator->setReplacers($this->getModelUtilities()->loadPathReplacers())
            ->setRootLevelId($this->Setup()->getRootLevelId())
            ->setFormType($this->Setup()->getFormType())
            ->setParents($this->getParentFolders($this->Setup()->getParentId()))
            ->setIsStartElement($this->Setup()->getIsStartElement(false))
            ->setParentId($this->Setup()->getParentId());


        if (count($resourceLocator->getParents()) > 0) {
            $resourceLocator->setPrefix($this->getParentUrl($this->Setup()->getParentId()));
        }

        if ($this->core->config->url_layout == UniformResourceLocator::LAYOUT_TREE) {
            $resourceLocator->setPath(str_replace($resourceLocator->getPrefix(), '', $pathWithoutLanguage));
        } else {
            $resourceLocator->setPath($pathWithoutLanguage);
        }
        //Check if the url existed and has changed
        if (isset($objItemData) && count($objItemData) > 0) {
            $objItem = $objItemData->current();
            $objUrlData = $this->getModelUrls()->loadUrl($objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType);

            // update url
            if (count($objUrlData) > 0) {

                $objUrl = $objUrlData->current();

                // url has changed?
                if (strcmp($resourceLocator->get(false), $objUrl->url) !== 0) {
                    $isValid = $resourceLocator->checkUniqueness($resourceLocator->get(false));
                } else {
                    $isValid = true;
                }

            } else {
                if (!empty($pathWithoutLanguage)) {
                    $isValid = $resourceLocator->checkUniqueness($resourceLocator->get(false));
                } else {
                    $isValid = $this->validateNewNodeUrl($resourceLocator);
                }
            }

        } else {
            if (!empty($pathWithoutLanguage)) {
                $isValid = $resourceLocator->checkUniqueness($resourceLocator->get(false));
            } else {
                $isValid = $this->validateNewNodeUrl($resourceLocator);
            }
        }

        // if url is not valid, make a suggestion
        if (!$isValid) {
            $this->addMessage('errMessage', $this->core->translate->_('Err_existing_url'));
            if ($this->Setup()->getLanguageDefinitionType() == $this->core->config->language_definition->folder) {
                //$resourceLocator->setLanguageCode($this->getLanguageCode());
                $this->addMessage('suggestion', $resourceLocator->get(true));
            } else {
                $this->addMessage('suggestion', $resourceLocator->get(true));
            }
        }

        return $isValid;
    }

    /**
     * @param UniformResourceLocator $resourceLocator
     * @return bool
     */
    private function validateNewNodeUrl(UniformResourceLocator $resourceLocator)
    {
        if (!($this->Setup()->getIsStartElement(false) && $this->Setup()->getParentId() === null)) {
            $objFieldData = $this->Setup()->getModelGenericForm()->loadFieldsWithPropery($this->core->sysConfig->fields->properties->url_field, $this->Setup()->getGenFormId());

            if (count($objFieldData) > 0) {
                foreach ($objFieldData as $objField) {
                    if ($this->Setup()->getRegion($objField->regionId)->getField($objField->name)->getValue() != '') {
                        $resourceLocator->setPath(str_replace('/', '-', $this->Setup()->getRegion($objField->regionId)->getField($objField->name)->getValue()));
                        break;
                    }
                }
            }
        }

        return $resourceLocator->checkUniqueness($resourceLocator->get(false));
    }

    /**
     * @param $intParentFolderId
     * @return array
     */
    protected function getParentFolders($intParentFolderId)
    {
        $objParentFolders = array();

        switch ($this->Setup()->getFormTypeId()) {
            case $this->core->sysConfig->form->types->page:
                $objParentFolders = $this->getModelFolders()->loadParentFolders($intParentFolderId);
                break;

            case $this->core->sysConfig->form->types->global:
                $objParentFolders = $this->getModelFolders()->loadGlobalParentFolders($intParentFolderId, $this->Setup()->getRootLevelGroupId());

                // convention: remove last (top) folder
                $arrTmpParentFolders = array();
                foreach ($objParentFolders as $objParentFolder) {
                    $arrTmpParentFolders[] = $objParentFolder;
                }
                array_pop($arrTmpParentFolders);
                $objParentFolders = $arrTmpParentFolders;
                break;
        }

        return $objParentFolders;
    }

    /**
     * @param $intFolderId
     * @return string
     */
    protected function getParentUrl($intFolderId)
    {

        //var_dump($this->getModelFolders());
        $objParentUrl = null;

        switch ($this->Setup()->getFormTypeId()) {
            case $this->core->sysConfig->form->types->page:
                $objParentUrl = $this->getModelFolders()->loadStartElementUrl($intFolderId, $this->Setup()->getIsStartElement(false));
                break;

            case $this->core->sysConfig->form->types->global:
                $objParentUrl = $this->getModelFolders()->loadGlobalStartElementUrl($intFolderId, $this->Setup()->getIsStartElement(false), $this->Setup()->getRootLevelGroupId());
                break;
        }

        if (!empty($objParentUrl)) {
            return $objParentUrl->url;
        } else {
            return '';
        }
    }

    protected function getLanguageCode()
    {
        $languages = $this->core->config->languages->language->toArray();
        foreach ($languages as $language) {
            if ($language['id'] == $this->Setup()->getLanguageId()) {
                return $language['code'];
            }
        }

        return null;
    }

    /**
     * getModelUrls
     * @return Model_Urls
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.1
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
            $this->objModelUrls->setLanguageId($this->Setup()->getLanguageId());
        }

        return $this->objModelUrls;
    }

    /**
     * getModelPages
     * @return Model_Pages
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
            $this->objModelPages->setLanguageId($this->Setup()->getLanguageId());
        }

        return $this->objModelPages;
    }

    /**
     * getModelGlobals
     * @author Cornelius Hansjakob <cha@massiveart.com>
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
            $this->objModelGlobals->setLanguageId($this->Setup()->getLanguageId());
        }

        return $this->objModelGlobals;
    }

    /**
     * getModelFolders
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
            $this->objModelFolders->setLanguageId($this->Setup()->getLanguageId());
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
            $this->objModelUtilities->setLanguageId($this->Setup()->getLanguageId());
        }

        return $this->objModelUtilities;
    }
}