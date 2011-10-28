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
 * @package    application.zoolu.modules.core.views.helpers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * VideoHelper
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-03-04: Thomas Schedler
 * 1.1, 2009-07-30: Florian Mathis, Youtube Service
 * 1.2, 2009-10-23: Dominik Mößlang, bugfixes & fine-tuning
 * 
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class VideoHelper {
  
  /**
   * @var Core
   */
  private $core;
    
  /**
   * Constructor 
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  }
  
  /**
   * getVideoTree 
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getVideoSelect($objVideos, $mixedSelectedId, $strElementId, $intVideoTypeId) {

    $intCounter = 0;
    $strOutput='';
        
    foreach($objVideos as $objVideo) {
      $intCounter++;
            
      switch($intVideoTypeId) {
        // Vimeo Controller
        case $this->core->sysConfig->video_channels->vimeo->id :
          $objThumbnails = $objVideo->getThumbnails();
          $objThumbnail = current(current($objThumbnails));
              
          $strBgClass = ($intCounter % 2) ? ' bg2' : ' bg1';
                      
          $strOutput .= '
                  <div class="videoItem'.$strBgClass.'" id="div_'.$strElementId.'_'.$objVideo->getID().'"   >
                    <div class="videoThumb"><img src="'.$objThumbnail->getContent().'" width="100"/></div>
                    <input type="hidden" id="thumb_'.$strElementId.'_'.$objVideo->getID().'" name="thumb_'.$strElementId.'_'.$objVideo->getID().'" value="'.$objThumbnail->getContent().'"/>
                    <div class="videoInfos">
                     <div class="buttonSelectVideo" onclick="myForm.selectVideo(\''.$strElementId.'\', \''.$objVideo->getID().'\');">
                      <div>
                        <div class="button25leftOn"></div>
                        <div class="button25centerOn">
                          <div>Auswählen</div>
                        </div>
                        <div class="button25rightOn"></div>
                        <div class="clear"></div>
                      </div>
                     </div>
                     <div class="buttonUnselectVideo" style="display:none;" onclick="myForm.unselectVideo(\''.$strElementId.'\', \''.$objVideo->getID().'\');" >
                      <div class="button25leftOff"></div>
                      <div class="button25centerOff">
                        <div>Löschen</div>
                      </div>
                      <div class="button25rightOff"></div>
                      <div class="clear"></div>        
                     </div>
                     <strong>'.$objVideo->getTitle().'</strong>
                     <br/><span class="gray666">('.date('d.m.Y H:i', $objVideo->getUploadTimestamp()).')</span>
                     <input type="hidden" id="title_'.$strElementId.'_'.$objVideo->getID().'" name="title_'.$strElementId.'_'.$objVideo->getID().'" value="'.$objVideo->getTitle().'"/>
                    </div>
                   <div class="clear"></div>
                  </div>';  
         
        break;
        
        // Youtube Controller
        case $this->core->sysConfig->video_channels->youtube->id :
          
          $objThumbnails = $objVideo->getVideoThumbnails();
          $arrThumbnail = current($objThumbnails);
          $arrTags = array();
          $strBgClass = ($intCounter % 2) ? ' bg2' : ' bg1';
          $strOutput .= '
              <div class="videoItem'.$strBgClass.'" id="div_'.$strElementId.'_'.$objVideo->getVideoId().'"   >
                <div class="videoThumb"><img src="'.$arrThumbnail['url'].'" width="100"/></div>
                <input type="hidden" id="thumb_'.$strElementId.'_'.$objVideo->getVideoId().'" name="thumb_'.$strElementId.'_'.$objVideo->getVideoId().'" value="'.$arrThumbnail['url'].'"/>
                <div class="videoInfos">
                  <div class="buttonSelectVideo" onclick="myForm.selectVideo(\''.$strElementId.'\', \''.$objVideo->getVideoId().'\');">
                    <div class="button25leftOn"></div>
                    <div class="button25centerOn"><div>Auswählen</div></div>
                    <div class="button25rightOn"></div>
                    <div class="clear"></div>
                  </div>                 
                  <div class="buttonUnselectVideo" style="display:none;" onclick="myForm.unselectVideo(\''.$strElementId.'\', \''.$objVideo->getVideoId().'\');">
                    <div class="button25leftOff"></div>
                    <div class="button25centerOff"><div>Löschen</div></div>
                    <div class="button25rightOff"></div>
                    <div class="clear"></div>        
                  </div>
                  <strong>'.$objVideo->getTitle().'</strong>';
          // Check if VideoRecorded Date isnt null
          if($objVideo->getVideoRecorded() != null) {
            $strVideoUploadDate = date('d.m.Y H:i', strtotime($objVideo->getVideoRecorded()));
            $strOutput .= '<br/><span class="gray666">('.$strVideoUploadDate.')</span>';
          }
          $strOutput .='
                  <input type="hidden" id="title_'.$strElementId.'_'.$objVideo->getVideoId().'" name="title_'.$strElementId.'_'.$objVideo->getVideoId().'" value="'.$objVideo->getTitle().'"/>
                  <div class="clear"></div>
                </div>
                <div class="clear"></div>
              </div>';  
              
         break;
      }
    }
    /**
     * return html output
     */
    return $strOutput;
  }
  
  /**
   * getVideoEntity 
   * @author Dominik Moesslang <dmo@massiveart.com>
   * @version 1.0
   */
  public function getSelectedVideo($objVideoEntity, $intVideoTypeId, $strValue , $strElementId, $strVideoTypeName, $strChannelUserId){
    
    $strBgClass = ' bg2';
    $strOutput = '';
    $strOutput .= '
          <div id="'.$strElementId.'SelectedService" class="field-12">'.$strVideoTypeName.'/'.$strChannelUserId.'</div>';
            
    switch($intVideoTypeId) {
      // Vimeo Controller
      case $this->core->sysConfig->video_channels->vimeo->id :
    
        $objThumbnails = $objVideoEntity->getThumbnails();
        $objThumbnail = current(current($objThumbnails));
          
        $strOutput .='
            <div class="selectedVideo'.$strBgClass.'">
              <div id="div_selected'.$strElementId.'" >
                <div class="videoThumb"><img src="'.$objThumbnail->getContent().'" width="100"/></div>
                <input type="hidden" id="thumb_'.$strElementId.'_'.$objVideoEntity->getID().'" name="thumb_'.$strElementId.'_'.$objVideoEntity->getID().'" value="'.$objThumbnail->getContent().'"/>
                <div class="videoInfos"> 
                  <div  onclick="myForm.unselectVideo(\''.$strElementId.'\', \''.$objVideoEntity->getID().'\');" style="cursor:pointer; position:relative; float:right; padding-right:5px; padding-top:20px;">
                    <div class="button25leftOff"></div>
                    <div class="button25centerOff"><div>Löschen</div></div>
                    <div class="button25rightOff"></div>
                    <div class="clear"></div>
                  </div>
                </div>
                <strong>'.$objVideoEntity->getTitle().'</strong>
                <br/><span class="gray666">('.date('d.m.Y H:i', $objVideoEntity->getUploadTimestamp()).')</span>
                <input type="hidden" id="title_'.$strElementId.'_'.$objVideoEntity->getID().'" name="title_'.$strElementId.'_'.$objVideoEntity->getID().'" value="'.$objVideoEntity->getTitle().'"/>
              </div>
            </div>';   
        break;
        
      // Youtube Controller
      case $this->core->sysConfig->video_channels->youtube->id :
        
        $objThumbnails = $objVideoEntity->getVideoThumbnails();
        $arrThumbnail = current($objThumbnails);
        $arrTags = $objVideoEntity->getVideoTags();
      
        $strOutput .= '
            <div class="selectedVideo'.$strBgClass.'">
              <div id="div_selected'.$strElementId.'">
                <div class="videoThumb"><img src="'.$arrThumbnail['url'].'" width="100"/></div>
                <input type="hidden" id="thumb_'.$strElementId.'_'.$objVideoEntity->getVideoId().'" name="thumb_'.$strElementId.'_'.$objVideoEntity->getVideoId().'" value="'.$arrThumbnail['url'].'"/>
                <div class="videoInfos">              
                  <div onclick="myForm.unselectVideo(\''.$strElementId.'\', \''.$objVideoEntity->getVideoId().'\');" style="cursor:pointer; position:relative; float:right; padding-right:5px; padding-top:20px;">
                    <div class="button25leftOff"></div>
                      <div class="button25centerOff"><div>Löschen</div></div>
                      <div class="button25rightOff"></div>
                      <div class="clear"></div>
                    </div>
                    <strong>'.$objVideoEntity->getTitle().'</strong>';      
        // Check if VideoRecorded Date isnt null
        if($objVideoEntity->getVideoRecorded() != null) {
          $strVideoUploadDate = date('d.m.Y H:i', strtotime($objVideoEntity->getVideoRecorded()));
          $strOutput .= '
                    <br/><span class="gray666">('.$strVideoUploadDate.')</span>';
        }
        $strOutput .='
                    <input type="hidden" id="title_'.$strElementId.'_'.$objVideoEntity->getVideoId().'" name="title_'.$strElementId.'_'.$objVideoEntity->getVideoId().'" value="'.$objVideoEntity->getTitle().'"/>
                  </div>
                </div>
                <div class="clear"></div>
              </div>
            </div>';  
       break;
    }
    /**
     * return html output
     */
    return $strOutput;
  }
}

?>