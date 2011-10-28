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
 * @package    library.massiveart.generic.fields.Collection.forms.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Form_Helper_FormCollection
 *
 * Helper to generate a "Collection" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-08-28: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.generic.fields.Collection.forms.helpers
 * @subpackage Form_Helper_FormCollection
 */

class Form_Helper_FormCollection extends Zend_View_Helper_FormElement {

  /**
   * formCollection
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function formCollection($name, $value = null, $attribs = null, $options = null, Form_Element_Collection &$element){
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

    $strPageCollection = '';
    if(isset($element->objPageCollection)){
      foreach($element->objPageCollection as $objPageInternalLink){
        $strPageCollection .= '
                      <div id="'.$this->view->escape($name).'_page_'.$objPageInternalLink->pageId.'" class="pageitem" pageid="'.$objPageInternalLink->pageId.'">
                        <div id="'.$this->view->escape($name).'_remove'.$objPageInternalLink->id.'" onclick="myForm.removeItem(\''.$this->view->escape($name).'\', \''.$this->view->escape($name).'_page_'.$objPageInternalLink->pageId.'\', \''.$objPageInternalLink->pageId.'\')" class="itemremovelist2"></div>
                        <div id="Page'.$objPageInternalLink->id.'">
                          <div class="icon img_'.(($objPageInternalLink->isStartPage == 1) ? 'startpage' : 'page').'_'.(($objPageInternalLink->idStatus == $core->sysConfig->status->live) ? 'on' : 'off').'"></div>'.htmlentities($objPageInternalLink->title, ENT_COMPAT, $core->sysConfig->encoding->default).'
                        </div>
                      </div>';
      }
    }

    /**
     * build the element
     */
    $strOutput = '<div class="collectionwrapper">
                    <div class="top">'.$core->translate->_('Add_page').': <img src="/zoolu-statics/images/icons/icon_addmedia.png" width="16" height="16" onclick="myForm.getAddTreeOverlay(\'divCollectionContainer_'.$this->view->escape($id).'\'); return false;"/></div>
                    <div id="divCollectionContainer_'.$this->view->escape($id).'"'.$disabled.' class="'.$attribs['class'].'">
                    '.$strPageCollection.'
                    <div id="divClear_'.$this->view->escape($name).'" class="clear"></div>
                    </div>
                    <input type="hidden" id="'.$this->view->escape($id).'" name="'.$this->view->escape($name).'" isCoreField="'.$attribs['isCoreField'].'" fieldId="'.$attribs['fieldId'].'" value="'.$this->view->escape($value).'"/>
                  </div>';
    
     /**
     * add the scriptaculous sortable funcionality to the parent containert
     */
     $strOutput .= '<script type="text/javascript" language="javascript">/* <![CDATA[ */
     myForm.initSortable(\''.$this->view->escape($id).'\', \'divCollectionContainer_'.$this->view->escape($id).'\', \'pageitem\', \'div\', \'pageid\', \'vertical\');
     /* ]]> */</script>';

    return $strOutput;
  }
}

?>