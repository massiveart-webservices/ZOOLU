<?php
/**
 * ZOOLU - Community Management System
 * Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
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
 * @package    application.website.default.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * SubscriberController
 *
 * Version History (please keep backward compatible):
 * 1.0, 2012-10-09: Daniel Rotter
 *
 * @author Raphael Stocker <raphael.stocker@massiveart.com>
 * @version 1.0
 */

class SubscriberHelper
{
    /**
     * @var Core
     */
    protected $core;
    private $translate;
    private $interesGroups;

    public function __construct($blnRequireFunctionWrapper = true)
    {
        $this->core = Zend_Registry::get('Core');

        if ($blnRequireFunctionWrapper == true) {
            require_once(dirname(__FILE__) . '/subscriber.inc.php');
        }
    }

    /**
     * getMetaTitle
     * @param string $strTag
     * @return string $strReturn
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getMetaTitle($strTag = '')
    {
        $strReturn = '';

        if ($this->strMetaTitle != '') {
            if ($strTag != '') $strReturn .= '<' . $strTag . '>';
            $strReturn .= htmlentities($this->strMetaTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default);
            if ($strTag != '') $strReturn .= '</' . $strTag . '>';
        }

        return $strReturn;
    }

    /**
     * setMetaTitle
     * @param string $strMetaTitle
     * @author Daniel Rotter
     * @version 1.0
     */
    public function setMetaTitle($strMetaTitle)
    {
        $this->strMetaTitle = $strMetaTitle;
    }
    
    /**
     * getContent
     * @param $objView
     * @return mixed
     */
    public function getContent($objView)
    {
        return $objView->layout()->content;
    }
    
    /**
     * getSubscribeForm
     */
    public function getSubscribeForm() {
        $strReturn = '';
        foreach ($this->interesGroups as $interestGroup) {
            $strReturn .= $interestGroup->title .'<br>';
        }
        return $strReturn;
    }
    
    /**
     * setTranslate
     */
    public function setTranslate($translate) {
        $this->translate = $translate;
    }
    
    /**
     * setInterestGroup
     */
    public function setInterestGroup($interesGroups) {
        $this->interesGroups = $interesGroups;
    }
}

?>