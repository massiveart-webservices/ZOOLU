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
 * @package    library.massiveart.generic.fields.Imagemap.forms.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * Form_Helper_FormImagemap
 *
 * Helper to generate a "add Imagemap" element
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-12: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_FormImagemap
 */

class Form_Helper_FormImagemap extends Zend_View_Helper_FormElement
{
    protected $core;
    
    private $arrMarkers;
    
    /**
     * formImagemap
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function formImagemap($name, $value = null, $attribs = null)
    {
        $strOutput = '';
        $info = $this->_getInfo($name, $value, $attribs);
        $this->core = Zend_Registry::get('Core');
        extract($info); // name, value, attribs, options, listsep, disable

        // XHTML or HTML end tag?
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag = '>';
        }
        
        $targetRegion = $this->getTargetRegion($attribs);
        
        $strOutput .= '<div class="imagemapWrapper">
                           <div class="imagemapTop">
            			        <div class="left">' . $this->core->translate->_('Size') . ': ' . $this->getSizeSelect( ($value != '' ? $value->dimensions[0] : ''), $this->view->escape($id)) . '</div>
            			        <div class="right">' . $this->core->translate->_('Edit backgorund image') . ': <img src="/zoolu-statics/images/icons/icon_addmedia.png" width="16" height="16" onclick="myForm.getAddMediaOverlay(\'divImagemap_' . $this->view->escape($id) . '\', true, $(\'' . $this->view->escape($id) . '_size\' ).value, \'imagemap\'); return false;"' . $endTag . '</div>
            			        <div class="clear"></div>
            			    </div>
            			    <div class="imagemapContainer">
                                <div id="divImagemap_' . $this->view->escape($id) . '" class="imagemap" style="width: ' . ($value != '' ? $value->dimensions[0] : '') . 'px;">';
        $strMarkerInstances = '';
        if ($value != '') {    
            $strOutput .= '         <img id="' . $this->view->escape($id) . '_img" src="/website/uploads/images/' . $value->path . $value->size . '/' . $value->filename .'" ' .$endTag;
            if ($value->markers != '') {
                $this->arrMarkers = json_decode($value->markers);
                if (is_array($this->arrMarkers) && count($this->arrMarkers) > 0 ) {
                    foreach ($this->arrMarkers as $marker) {
                        $strMarkerInstances .= '[' . $marker->id . ']';
                        $xAbsolute = round($value->dimensions[0] * $marker->x - 16);
                        $yAbsolute = round($value->dimensions[1] * $marker->y - 16);
                        $strOutput .= ' 
                                    <div id="' . $this->view->escape($id) . '_marker_' . $marker->id . '" class="marker" style="left: ' . $xAbsolute .'px; top: ' . $yAbsolute . 'px;"></div>';
                    }
                }
            }
        }
        $strOutput .= '         </div>
            					<textarea style="display:none;" name="' . $this->view->escape($id) . '_markers" id="' . $this->view->escape($id) . '_markers">' . ($value != '' ? $value->markers : '') . '</textarea>		     	  
                                <input type="hidden" value="' . ($value != '' ? $value->file : '') .  '" name="' . $this->view->escape($id) . '_file" id="' . $this->view->escape($id) . '_file" ' .  $endTag .'
                                <input type="hidden" value="' . $strMarkerInstances .  '" name="' . $this->view->escape($id) . '_markerInstances" id="' . $this->view->escape($id) . '_markerInstances" ' .  $endTag .'
            					<div class="itemremovethumb" onclick="myForm.removeImagemapValues(\'' . $this->view->escape($id) . '\'); return false;" id="' . $this->view->escape($id) . '_remove" ' . ($value != '' ? '' : 'style="display: none;"') . '></div>
                            </div>
                        </div>';

        // init dragable markers
        $strOutput .= ' <script type="text/javascript">
        					// remove overlays which was open before this site was loaded
        					$$(\'.fieldOverlayWrapper\').each(function(elem) { elem.remove();});
        					$(\'divImagemap_' . $this->view->escape($id) .'\').ondblclick = function(event) {
        						myForm.getAddMarkerOverlay(event, \'divImagemap_' . $this->view->escape($id) .'\', \'' . $this->view->escape($id) . '\', ' . $targetRegion . ');
        						event.stop();
                    		};
                      ';
        if (is_array($this->arrMarkers) && count($this->arrMarkers) > 0 ) {
            foreach ($this->arrMarkers as $marker) {
      
                $strOutput .= '	$(\'' . $this->view->escape($id) . '_marker_' . $marker->id . '\').ondblclick = function(event) {
                					myForm.getEditMarkerOverlay(' . $marker->id . ', \'' . $this->view->escape($id) . '\', ' . $targetRegion . ');
                					event.stop();
                    			};';
                $strOutput .= '	new Draggable(\'' . $this->view->escape($id) . '_marker_' . $marker->id . '\', { 
                			   		onEnd: function(drag) {
            							myForm.setMarkerPosition(drag, \'' . $this->view->escape($id) . '\', ' . $marker->id . ');
            						},
            						onStart: function(drag) {
            							myForm.originMarkerPos = [drag.element.offsetLeft, drag.element.offsetTop];
            						}
                			    }); ';
            }    
        }					        
        $strOutput .= ' </script>';
        
        //edit marker overlay
        $strOutput .= $this->getMarkerOverlay($this->view->escape($id));
        
        return $strOutput;
    }
    
    private function getSizeSelect($strSelectedSize, $id) {
        $strReturn = '
        <select name="' . $id . '_size" id="' . $id . '_size" onclick="myForm.oldImageSize = this.value;" onchange="myForm.changeImageSize(\'' . $id . '\' , this.value, \'divImagemap_' . $id . '\', \'imagemap\')">';
        $arrImagesSizes = Zend_Registry::get('Core')->sysConfig->upload->images->default_sizes->default_size->toArray();
        foreach ($arrImagesSizes as $arrImageSize) {
            if (isset($arrImageSize['display']) && isset($arrImageSize['display']['imagemap']) && $arrImageSize['display']['imagemap'] !== 'false') {
                if ($arrImageSize['display']['imagemap'] == 'default' && $strSelectedSize == '') {
                    $strSelectedSize = $arrImageSize['folder'];
                }
                if ($arrImageSize['folder'] == $strSelectedSize) {
                    $strSelected =  'selected="selected"';
                    
                } else {
                    $strSelected =  '';
                }          
                $strReturn .= '<option value="' . $arrImageSize['folder'] . '"' . $strSelected . '>' . $arrImageSize['folder'] . '</option>';
            } 
        }
        $strReturn .= '
        </select>';
        return $strReturn;
    }
    
    private function getMarkerOverlay($id) {
        $strReturn = '';
        $strReturn .= '
        	<div class="overlaycontentwrapper2" id="' . $id . '_fieldOverlayWrapper" style="display:none;">
                <table>
                    <thead>
                    <tr>
                        <td class="cornertopleft"></td>
                        <td class="topcenter"></td>
                        <td class="cornertopright"></td>
                    </tr>
                    <tr>
                        <td class="headerleft"></td>
                        <td class="headercenter handle">
                            <div onclick="myOverlay.close(\'' . $id . '_fieldOverlayWrapper\'); return false;" class="headerclose"></div>
                            <div class="clear"></div>
                        </td>
                        <td class="headerright"></td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="contentleft"></td>
                        <td class="contentcenter">
                            <!-- content -->
                            <div class="overlaycontent">
                            	'. $this->core->translate->_('Corresponding region') .':&nbsp; 
                            	<select id="' . $id . '_regionSelect">
                            		<option class="default" value="">'. $this->core->translate->_('Please_choose') .'
                            	</select>
                            </div>
                            <!-- buttons -->
                            <div class="fieldOverlayButtons overlayButtons">
                              	<div class="buttoncancel"><a href="#" onclick="myOverlay.close(\'' . $id . '_fieldOverlayWrapper\'); return false;">' . $this->core->translate->_('Cancel') .'</a></div>
                                <div class="fieldOverlayButton" id="' . $id . '_btnSaveMarker">
                                    <div class="button25leftOn"></div>
                                    <div class="button25centerOn">
                                        <div>' . $this->core->translate->_('OK') .'</div>
                                    </div>
                                    <div class="button25rightOn"></div>
                                    <div class="clear"></div>
                                </div>
                                <div class="fieldOverlayButton" id="' . $id .'_btnDeleteMarker" style="display:none;">
                                    <div class="button25leftOff"></div>
                                    <div class="button25centerOff">
                                    	<img class="icondelete" width="11" height="14" src="/zoolu-statics/images/icons/icon_delete_white.png">
                                        <div>' . $this->core->translate->_('Delete') .'</div>
                                    </div>
                                    <div class="button25rightOff"></div>
                                    <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                            </div>
                        </td>
                        <td class="contentright"></td>
                    </tr>
                    <tr>
                        <td class="cornerbottomleft"></td>
                        <td class="bottomcenter"></td>
                        <td class="cornerbottomright"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        ';
        return $strReturn;
    }
    
    /**
     * getTargetRegion
     */
    private function getTargetRegion($attribs) {
        $fieldOptions = json_decode($attribs['fieldOptions']);
        $idTargetRegion = $fieldOptions->idTargetRegion;
        return $idTargetRegion; 
    }

}

?>