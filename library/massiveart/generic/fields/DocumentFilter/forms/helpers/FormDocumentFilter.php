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
 * @package    library.massiveart.generic.fields.DocumentFilter.forms.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * Form_Helper_FormDocumentFilter
 *
 * Helper to generate a "add DocumentFilter" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-12-16: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormDocumentFilter
 */

class Form_Helper_FormDocumentFilter extends Zend_View_Helper_FormElement {

  /**
   * formDocumentFilter
   * @param string $name
   * @param string $value
   * @param array $attribs
   * @param mixed $options
   * @param Zend_Db_Table_Rowset $objAllTags
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function formDocumentFilter($name, $objFilters = null, $attribs = null, $options = null, $regionId = null, $objAllTags){
    $info = $this->_getInfo($name, $objFilters, $attribs);
    $core = Zend_Registry::get('Core');
    extract($info); // name, value, attribs, options, listsep, disable

    // XHTML or HTML end tag
    $endTag = ' />';

    if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
      $endTag= '>';
    }

    // build the element
    $strTags = '';
    $strTagIds = '';
    $strFolderIds = '';
    $strRootLeveId = '';

    if($objFilters instanceof stdClass){

      if(array_key_exists('ft'.$core->sysConfig->filter_types->tags, $objFilters->filters)){
        $objFilter = $objFilters->filters['ft'.$core->sysConfig->filter_types->tags];
        foreach($objAllTags as $objTag){
          if(in_array($objTag->id, $objFilter->referenceIds)){
            $strTags .= '<li value="'.$objTag->id.'">'.htmlentities($objTag->title, ENT_COMPAT, $core->sysConfig->encoding->default).'</li>';
            $strTagIds .= $objTag->id.',';
          }
        }
      }

      if(array_key_exists('ft'.$core->sysConfig->filter_types->folders, $objFilters->filters)){
        $objFilter = $objFilters->filters['ft'.$core->sysConfig->filter_types->folders];
        foreach($objFilter->referenceIds as $intReferenceId){
          $strFolderIds .= '['.$intReferenceId.']';
        }
      }

      if(array_key_exists('ft'.$core->sysConfig->filter_types->rootLevel, $objFilters->filters)){
        $objFilter = $objFilters->filters['ft'.$core->sysConfig->filter_types->rootLevel];
        foreach($objFilter->referenceIds as $intReferenceId){
          $strRootLeveId = $intReferenceId;
        }
      }
    }
    
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
    $strOutput = '<div>
	                  <ol>
							        <li id="autocompletList_'.$this->view->escape($id).'" class="autocompletList input-text">
                        <label class="fieldtitle" for="'.$this->view->escape($id).'_Tags">'.$core->translate->_('Document_filtering_by_tags').'</label>
                        <input type="text" value="'.$this->view->escape(trim($strTagIds, ',')).'" onchange="myForm.loadFileFilterFieldContent(\''.$this->view->escape($id).'\', \'documentFilter\');" id="'.$this->view->escape($id).'_Tags" name="'.$this->view->escape($name).'_Tags"'.$endTag.'
							          <div id="'.$this->view->escape($id).'_Tags_autocompleter" class="autocompleter">
							            <div class="default">'.$core->translate->_('Search_tags').'</div>
							            <ul class="feed">
							              '.$strTags.'
							            </ul>
							          </div>
							        </li>
							      </ol>
						      </div>';
    /**
     * is empty element
     */
    $blnIsEmpty = false;
    if(array_key_exists('isEmptyField', $attribs) && $attribs['isEmptyField'] == 1){
      $blnIsEmpty = true;  
    }
    
    if($blnIsEmpty == true){
      $strOutput .= '
        <script type="text/javascript">//<![CDATA[ 
          myForm.addTag("'.$this->view->escape($id).'_Tags","'.$this->view->escape($regionId).'",'.$this->getAllTagsForAutocompleter($objAllTags).');
        //]]>
        </script>';
    }else{
      $strOutput .= '
        <script type="text/javascript">//<![CDATA[ 
          myForm.initTag("'.$this->view->escape($id).'_Tags",'.$this->getAllTagsForAutocompleter($objAllTags).');         
        //]]>
        </script>';
    } 
    /*
                  <script type="text/javascript" language="javascript">
                    '.$this->view->escape($id).'_list = new FacebookList(\''.$this->view->escape($id).'_Tags\', \''.$this->view->escape($id).'_autocompleter\',{ regexSearch: true });
                    '.$this->getAllTagsForAutocompleter($objAllTags, $id).'
                  </script>*/
    $strOutput .= '
                  <div style="display:none;">
                    <div>'.$core->translate->_('Select_folder').': <img src="/zoolu-statics/images/icons/icon_addmedia.png" width="16" height="16" onclick="myForm.getDocumentFolderChooserOverlay(\''.$this->view->escape($id).'_FoldersContainer\', \''.$this->view->escape($id).'\'); return false;"/></div>
                    <div id="'.$this->view->escape($id).'_FoldersContainer"></div>
                    <input type="hidden" value="'.$this->view->escape($strFolderIds).'" id="'.$this->view->escape($id).'_Folders" name="'.$this->view->escape($name).'_Folders"'.$endTag.'
                    <input type="hidden" value="'.$this->view->escape($strRootLeveId).'" id="'.$this->view->escape($id).'_RootLevel" name="'.$this->view->escape($name).'_RootLevel"'.$endTag.'
                  </div>						      
                  <div class="docwrapper">
                    <div class="doctop">'.$core->translate->_('Select_folder').': <img src="/zoolu-statics/images/icons/icon_addmedia.png" width="16" height="16" onclick="myForm.getDocumentFolderChooserOverlay(\''.$this->view->escape($id).'_FoldersContainer\', \''.$this->view->escape($id).'\'); return false;"/></div>
                    <div id="documentFilterContainer_'.$this->view->escape($id).'"'.$disabled.' class="'.$attribs['class'].'"></div>
                  </div>
                  <input type="hidden" id="'.$this->view->escape($id).'" name="'.$this->view->escape($name).'" isCoreField="'.$attribs['isCoreField'].'" fieldId="'.$attribs['fieldId'].'" value=""/>';

    return $strOutput;
  }

  /**
   * getAllTagsForAutocompleter
   * @return Zend_Db_Table_Rowset $objAllTags
   * @return string $strElementId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getAllTagsForAutocompleter($objAllTags){
  	$core = Zend_Registry::get('Core');
    $strAllTags = '[';
    if(count($objAllTags) > 0){
      foreach($objAllTags as $objTag){
        $strAllTags .= '{"caption":"'.htmlentities($objTag->title, ENT_COMPAT, $core->sysConfig->encoding->default).'","value":'.$objTag->id.'},';
      }
      $strAllTags = trim($strAllTags, ',');            
    }
    $strAllTags .= ']';
    return $strAllTags;
  }
}

?>