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
 * @package    application.zoolu.modules.core.views.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * LanguageHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-09-14: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

class LanguageHelper
{

    /**
     * @var Core
     */
    private $core;

    public function getCopyLanguages($arrLanguages)
    {
        $strReturn = '';
        $objAuth = Zend_Auth::getInstance();
        $objAuth->setStorage(new Zend_Auth_Storage_Session('zoolu'));
        $intUserId = $objAuth->getIdentity()->id;

        foreach ($arrLanguages as $arrLanguage) {
            $strReturn .= '
                    <div class="olnavrootitem">
                      <a href="#" onclick="myForm.copyLanguage(' . $arrLanguage['id'] . ', ' . $intUserId . ')">
                        <div class="icon"></div>
                        ' . $arrLanguage['title'] . '
                      </a>
                    </div>';
        }

        return $strReturn;
    }

    /**
     * Constructor
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }
}

?>