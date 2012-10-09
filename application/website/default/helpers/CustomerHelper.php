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
 * CustomerController
 *
 * Version History (please keep backward compatible):
 * 1.0, 2012-10-09: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

class CustomerHelper
{
    /**
     * @var Core
     */
    protected $core;

    /**
     * @var string
     */
    private $strTitle;

    /**
     * @var string
     */
    private $strMetaKeywords;

    /**
     * @var string
     */
    private $strMetaDescription;

    public function __construct($blnRequireFunctionWrapper = true)
    {
        $this->core = Zend_Registry::get('Core');

        if ($blnRequireFunctionWrapper == true) {
            require_once(dirname(__FILE__) . '/customer.inc.php');
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

        if ($this->strTitle != '') {
            if ($strTag != '') $strReturn .= '<' . $strTag . '>';
            $strReturn .= htmlentities($this->strTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default);
            if ($strTag != '') $strReturn .= '</' . $strTag . '>';
        }

        return $strReturn;
    }

    /**
     * getMetaDescription
     * @return string
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getMetaDescription()
    {
        $strReturn = '';
        if ($this->strMetaDescription != '') {
            $strReturn .= '<meta name="description" content="' . htmlentities($this->strMetaDescription, ENT_COMPAT, $this->core->sysConfig->encoding->default) . '"/>';
        }
        return $strReturn;
    }

    /**
     * getMetaKeywords
     * @return string $strReturn
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function getMetaKeywords(){
        $strReturn = '';

        if($this->strMetaKeywords != ''){
            $strReturn .= '<meta name="keywords" content="'.trim($this->strMetaKeywords, ', ').'"/>';
        }
        return $strReturn;
    }

    public function getContent($objView)
    {
        return $objView->layout()->content;
    }
}
?>