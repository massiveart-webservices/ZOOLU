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
 * @package    application.plugins.Gmaps.forms.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Form_Helper_FormGmaps
 * 
 * Helper to generate a google maps element
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-07-17: Florian Mathis
 * 
 * @author Florian Mathis <flo@massiveart.com>
 * @version 1.0
 * @package application.plugins.Gmaps.forms.helpers
 * @subpackage Plugin_FormHelper_FormGmaps
 */

class Plugin_FormHelper_FormGmaps extends Zend_View_Helper_FormElement {
  
  /**
   * formGmaps
   * @author Florian Mathis <flo@massiveart.com>
   * @param string $name
   * @param string $value
   * @param array $attribs
   * @param mixed $options   
   * @version 1.0
   */
  public function formGmaps($name, $value = null, $attribs = null, $options = null){
    $info = $this->_getInfo($name, $value, $attribs);
    extract($info); // name, value, attribs, options, listsep, disable
    $blnStdLatLng = true;

    // XHTML or HTML end tag
    $endTag = ' />';
    if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
      $endTag= '>';
    }
    
    $strOutput = '
    <script type="text/javascript">			
	    function initialize() {
        if(GBrowserIsCompatible()) {
          var map = new GMap2($("map_canvas"));';

    // Display Standard Marker to Bregenz AT if lat/lng is empty
    if(isset($value['latitude']) && isset($value['longitude'])) {
      if($value['latitude'] != '' && $value['longitude'] != '') {
      	$blnStdLatLng=false;
      }
    }
    
    if($blnStdLatLng) {
    	$value['latitude'] = '47.503042';
    	$value['longitude'] = '9.747067';
    	$strOutput .= '
    	    var center = new GLatLng(47.503042, 9.747067);
          map.setCenter(center, 8);';
    } else {
    	$strOutput .= '
          var center = new GLatLng('.$value['latitude'].', '.$value['longitude'].');
          map.setCenter(center, 8);';
    }
    
    $strOutput .= '
          var customUI = map.getDefaultUI();
          customUI.controls.scalecontrol = true;
          customUI.controls.largemapcontrol3d = true;
          customUI.controls.smallzoomcontrol3d = false;
          customUI.controls.maptypecontrol = true;        
          customUI.controls.menumaptypecontrol = false;
          customUI.maptypes.normal = true;
          customUI.maptypes.satellite = true;
          customUI.maptypes.hybrid = true;
          customUI.maptypes.physical = false;
          customUI.zoom.doubleclick = true; 
          customUI.zoom.scrollwheel = false;
          map.setUI(customUI);  
    
          var marker = new GMarker(center, {draggable: true});
          GEvent.addListener(marker, "drag", function() {
            var pos = marker.getLatLng();
            $("'.$this->view->escape($id).'Latitude").value = pos.lat();
            $("'.$this->view->escape($id).'Longitude").value = pos.lng();
          });
          map.addOverlay(marker);
          
          var bounds = new GLatLngBounds();
          bounds.extend(center);
          map.setCenter(bounds.getCenter(), map.getBoundsZoomLevel(bounds)-1);
	      }
	    }
	    initialize();
    </script>
    <div id="map_canvas" style="width: 100%; height: 300px"></div>';
    

    // Hidden Fields Latitude and Longitude
     $strOutput .= '<input type="hidden" value="'.$this->view->escape($value['latitude']).'" id="'.$this->view->escape($id).'Latitude" name="'.$this->view->escape($name).'Latitude"  '.$endTag.'
                    <input type="hidden" value="'.$this->view->escape($value['longitude']).'" id="'.$this->view->escape($id).'Longitude" name="'.$this->view->escape($name).'Longitude"  '.$endTag.'';
 
    return $strOutput;
  }
}

?>