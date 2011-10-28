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

class Form_Helper_FormCollapsableInternalLinks extends Zend_View_Helper_FormElement {

/**
   * formInternalLinks
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function formCollapsableInternalLinks($name, $value = null, $attribs = null, $options = null, Form_Element_CollapsableInternalLinks $element){
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

    $strItemInternalLinks = '';
    if(isset($element->objItemInternalLinks)){
      foreach($element->objItemInternalLinks as $objItemInternalLink){
        $strItemInternalLinks .= '
                      <div class="elementitem" id="'.$this->view->escape($name).'_item_'.$objItemInternalLink->relationId.'" itemid="'.$objItemInternalLink->relationId.'">
                        <div class="olpageleft"></div>
                        <div id="Remove'.$objItemInternalLink->id.'" class="itemremovelist" onclick="myForm.removeItem(\''.$this->view->escape($name).'\', \''.$this->view->escape($name).'_item_'.$objItemInternalLink->relationId.'\', \''.$objItemInternalLink->relationId.'\')"></div>
                        <div class="icon olpageicon img_'.(($objItemInternalLink->isStartItem == 1) ? 'startpage' : 'page').'_'.(($objItemInternalLink->idStatus == $core->sysConfig->status->live) ? 'on' : 'off').'"></div>
                        <div class="olpageitemtitle">'.htmlentities($objItemInternalLink->title, ENT_COMPAT, $core->sysConfig->encoding->default).'</div>
                        <div class="olpageright"></div>
                        <div class="clear"></div>
                      </div>';
      }
    }elseif(isset($element->objInstanceInternalLinks)){
      foreach($element->objInstanceInternalLinks as $objItemInternalLink){
        if(trim($attribs['regionExtension'], '_') == $objItemInternalLink->sortPosition){
          $strItemInternalLinks .= '
                      <div class="elementitem" id="'.$this->view->escape($name).'_item_'.$objItemInternalLink->relationId.'" itemid="'.$objItemInternalLink->relationId.'">
                        <div class="olpageleft"></div>
                        <div id="Remove'.$objItemInternalLink->id.'" class="itemremovelist" onclick="myForm.removeItem(\''.$this->view->escape($name).'\', \''.$this->view->escape($name).'_item_'.$objItemInternalLink->relationId.'\', \''.$objItemInternalLink->relationId.'\')"></div>
                        <div class="icon olpageicon img_'.(($objItemInternalLink->isStartItem == 1) ? 'startpage' : 'page').'_'.(($objItemInternalLink->idStatus == $core->sysConfig->status->live) ? 'on' : 'off').'"></div>
                        <div class="olpageitemtitle">'.htmlentities($objItemInternalLink->title, ENT_COMPAT, $core->sysConfig->encoding->default).'</div>
                        <div class="olpageright"></div>
                        <div class="clear"></div>
                      </div>';
        }
      }
    }

    /**
     * build the element
     */
    $strOutput = '<div class="internallinkswrapper">
                    <div class="top">'.$core->translate->_('Add_internal_links').': <img src="/zoolu-statics/images/icons/icon_addmedia.png" width="16" height="16" onclick="myForm.getAddTreeOverlay(\'divInternalLinksContainer_'.$this->view->escape($id).'\'); return false;"/></div>
                    <div id="divInternalLinksContainer_'.$this->view->escape($id).'"'.$disabled.' class="'.$attribs['class'].'">
                    '.$strItemInternalLinks.'
                    <div id="divClear_'.$this->view->escape($name).'" class="clear"></div>
                    </div>
                    <input type="hidden" id="'.$this->view->escape($id).'" name="'.$this->view->escape($name).'" isCoreField="'.$attribs['isCoreField'].'" fieldId="'.$attribs['fieldId'].'" value="'.$this->view->escape($value).'"/>
                  </div>';
                  
    /**
     * build the element
     */
    $strOutput = '<div class="internallinkswrapper">
                    <div class="top">'.$core->translate->_('Add_internal_links').': <img src="/zoolu-statics/images/icons/icon_addmedia.png" width="16" height="16" onclick="myForm.getAddInternalLinksOverlay(\'divInternalLinksContainer_'.$this->view->escape($id).'\'); return false;"/></div>
                    <div id="divInternalLinksContainer_'.$this->view->escape($id).'"'.$disabled.' class="'.$attribs['class'].'">
                    '.$strItemInternalLinks.'
                    </div>
                    <input type="hidden" id="'.$this->view->escape($id).'" name="'.$this->view->escape($name).'" isCoreField="'.$attribs['isCoreField'].'" fieldId="'.$attribs['fieldId'].'" value="'.$this->view->escape($value).'"/>
                  </div>';
    
    /**
     * add the scriptaculous sortable funcionality to the parent container
     */
     $strOutput .= '<script type="text/javascript" language="javascript">/* <![CDATA[ */
     myForm.initSortable(\''.$this->view->escape($id).'\', \'divInternalLinksContainer_'.$this->view->escape($id).'\', \'elementitem\', \'div\', \'itemid\', \'vertical\');
     /* ]]> */</script>';
   
      

    return $strOutput;
  }
}

?>