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
 * @package    library.massiveart.generic.fields.Document.forms.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * Form_Helper_FormDocument
 * 
 * Helper to generate a "add document" element
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-28: Cornelius Hansjakob
 * 
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormDocument
 */

class Form_Helper_FormDocument extends Zend_View_Helper_FormElement {

  /**
   * formDocument
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function formDocument($name, $value = null, $attribs = null){
    $info = $this->_getInfo($name, $value, $attribs);
    $core = Zend_Registry::get('Core');
    extract($info); // name, value, attribs, options, listsep, disable

    /**
     * is it disabled?
     */ 
    $disabled = '';
    if ($disable) {
      $disabled = ' disabled="disabled"';
    }

    /**
     * build the element
     */
    $strOutput = '<div class="docwrapper">
                    <div class="doctop">'.$core->translate->_('Add_documents').': <img src="/zoolu-statics/images/icons/icon_addmedia.png" width="16" height="16" onclick="myForm.getAddDocumentOverlay(\'divDocumentContainer_'.$this->view->escape($id).'\'); return false;"/></div>
                    <div id="divDocumentContainer_'.$this->view->escape($id).'"'.$disabled.' class="'.$attribs['class'].'">
                    </div>
                    <input type="hidden" id="'.$this->view->escape($id).'" name="'.$this->view->escape($name).'" isCoreField="'.$attribs['isCoreField'].'" fieldId="'.$attribs['fieldId'].'" value="'.$this->view->escape($value).'"/>
                  </div>';
    
    
    return $strOutput;
  }
}

?>