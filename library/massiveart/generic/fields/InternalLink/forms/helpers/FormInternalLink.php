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
 * @package    library.massiveart.generic.fields.InternalLink.forms.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * Form_Helper_FormInternalLink
 * 
 * Helper to generate a "tag" element
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-13: Thomas Schedler
 * 
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormInternalLink
 */

class Form_Helper_FormInternalLink extends Zend_View_Helper_FormElement {
  
  /**
   * formUrl
   * @author Thomas Schedler <tsh@massiveart.com>
   * @param string $name
   * @param string $value
   * @param array $attribs
   * @param mixed $options
   * @param Form_Element_InternalLink $element     
   * @version 1.0
   */
  public function formInternalLink($name, $value = null, $attribs = null, $options = null, Form_Element_InternalLink &$element){
    $info = $this->_getInfo($name, $value, $attribs);
    $core = Zend_Registry::get('Core');
    extract($info); // name, value, attribs, options, listsep, disable
    
    // XHTML or HTML end tag
    $endTag = ' />';
    if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
      $endTag= '>';
    }
    
    $strOutput = '';
  
    // build the element
    $strOutput = '
                <div class="linkedpage" id="divLinkedPage_'.$id.'">
                  <span class="big" id="spanLinkedPageBreadcrumb_'.$id.'">'.$this->view->escape($element->strLinkedPageBreadcrumb).'</span><span class="bold big" id="spanLinkedPageTitle_'.$id.'">'.$this->view->escape($element->strLinkedPageTitle).'</span> (<a href="#" onclick="myForm.getLinkedPageOverlay(\''.$id.'\'); return false;">'.$core->translate->_('Select_page').'</a>)<br/>
                  <span class="small" id="spanLinkedPageUrl_'.$id.'"><a href="'.$element->strLinkedPageUrl.'" target="_blank">'.$this->view->escape($element->strLinkedPageUrl).'</a></span>
                  <input type="hidden" value="'.$this->view->escape($value).'" id="'.$this->view->escape($id).'" name="'.$this->view->escape($name).'" '.$endTag.'
                </div>';
        
    return $strOutput;
  }
}

?>