<?php
/**
 * ZOOLU - Content Management System
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
 * @package    library.massiveart.utilities
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * HtmlTranslate extends Zend_Translate
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-10-03: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package com.massiveart.utilities
 * @subpackage HtmlTranslate
 */

class HtmlTranslate extends Zend_Translate {

  /**
   * Translates the given string
   * returns the translation
   *
   * @param  string             $messageId Translation string
   * @param  boolean            $htmlEncoded user nl2br and htmlentities 
   * @param  string|Zend_Locale $locale    (optional) Locale/Language to use, identical with locale
   *                                       identifier, @see Zend_Locale for more information
   * @return string
   */
  public function _($messageId, $htmlEncoded = true, $locale = null) {
    return ($htmlEncoded == true) ? nl2br(htmlentities(parent::getAdapter()->_($messageId, $locale), ENT_COMPAT, Zend_Registry::get('Core')->sysConfig->encoding->default, false)) : parent::getAdapter()->_($messageId, $locale);
  }

}

?>