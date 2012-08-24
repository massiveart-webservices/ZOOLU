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
class Form_Validator_UniqueLandingPageUrl extends Form_Validator_Abstract
{
    /**
     * @var Model_Urls
     */
    protected $objModelUrls;
    /**
     * @var Model_Pages
     */
    protected $objModelPages;
    /**
     * @var Model_Globals
     */
    protected $objModelGlobals;
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
        $strValue = strtolower($value);

        $isValid = true;

        //Load data
        $objItemData;
        $strType;
        $intElementId = ($this->Setup()->getElementLinkId()) ? $this->Setup()->getElementLinkId() : $this->Setup()->getElementId();
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

        //Check if the url existed and has changed
        if (isset($objItemData) && count($objItemData) > 0) {
            $objItem = $objItemData->current();
            $objUrlData = $this->getModelUrls()->loadUrl($objItem->relationId, $objItem->version, $this->core->sysConfig->url_types->$strType, true);

            if (count($objUrlData) > 0) {
                $objUrl = $objUrlData->current();
                if (strcmp($strValue, $objUrl->url) !== 0) {
                    //If changed, check if new url is free
                    $isValid = $this->checkUniqueness($strValue);
                }
            } else {
                $isValid = $this->checkUniqueness($strValue);
            }
        }

        if (!$isValid) {
            $this->addMessage('errMessage', $this->core->translate->_('Err_existing_landingpageurl'));
        }

        return $isValid;
    }

    /**
     * checkUniqueness
     * @param string $strUrl
     * @return boolean
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    protected function checkUniqueness($strUrl)
    {
        $blnReturn = true;
        $objUrls = $this->getModelUrls()->loadByUrl($this->Setup()->getRootLevelId(), $this->getModelUrls()->makeUrlConform($strUrl), $this->Setup()->getFormType(), true, false);
        if (isset($objUrls->url) && count($objUrls->url) > 0) {
            $blnReturn = false;
        }
        return $blnReturn;
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
}

?>