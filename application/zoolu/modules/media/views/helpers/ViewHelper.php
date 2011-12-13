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
 * @package    application.zoolu.modules.core.media.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ViewHelper
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-19: Cornelius Hansjakob
 * 
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class ViewHelper {
  
  /**
   * @var Core
   */
  private $core;
  
  /**
   * Constructor 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  }
  
  /**
   * getThumbView
   * @param object $rowset 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getThumbView($objRowset, $intSliderValue){
    $this->core->logger->debug('media->views->helpers->ViewHelper->getThumbView()');
  	
    $strOutput = '';
    if(count($objRowset) > 0){
	    foreach ($objRowset as $objRow) {
	      
	      $strStartWidth = 100;
	      $strStyleOutput = 'width:'.$intSliderValue.'px;';     
	      $strDivThumbPosImgStyle = ''; //position:absolute; bottom:0; ';
	      if($objRow->isImage && ($objRow->xDim < $objRow->yDim)){
	        /**
	         * calculate width of upright format images
	         */
	        $dblPercentHeight = ($intSliderValue * 100)/$objRow->yDim;
	        $intRoundedWidth = round(($objRow->xDim * $dblPercentHeight)/100);
	        
	        /**
	         * calculate width of upright format images
	         */
	        $dblPercentStartHeight = ($strStartWidth * 100)/$objRow->yDim;
	        $intRoundedStartWidth = round(($objRow->xDim * $dblPercentStartHeight)/100);
	        
	        /**
	         * set values to variables
	         */
	        $strStyleOutput = 'width:'.$intRoundedWidth.'px;';
	        $strStartWidth = $intRoundedStartWidth;
	        $strDivThumbPosImgStyle = '';
	      }
	      
	      $intDisplayLanuage = ($objRow->idLanguages == '') ? (isset($objRow->alternativLanguageId) && $objRow->alternativLanguageId != '')  ? $objRow->alternativLanguageId : $this->core->intZooluLanguageId : $objRow->idLanguages;
	      
	      if(strpos($objRow->mimeType, 'image/') !== false){
	        
	        /**
	         * image output
	         */
	        $strOutput .= '<div id="divThumbContainerImg'.$objRow->id.'" class="thumbcontainer" style="height:'.($intSliderValue + 10).'px; width:'.($intSliderValue + 10).'px;">
	                        <table>
	                          <tr>
	                            <td width="5" height="5" style="font-size:0;line-height:0;"><img src="/zoolu-statics/images/main/corner_thumbhov_top_left.png" width="5" height="5"/></td>
	                            <td height="5" style="background-color:#e4e4e4;font-size:0;line-height:0;">&nbsp;</td>
	                            <td width="5" height="5" style="font-size:0;line-height:0;"><img src="/zoolu-statics/images/main/corner_thumbhov_top_right.png" width="5" height="5"/></td>
	                          </tr>
	                          <tr>
	                            <td width="5" style="background-color:#e4e4e4;">&nbsp;</td>
	                            <td id="tdThumbImg'.$objRow->id.'" fileid="'.$objRow->id.'" class="tdthumbcontainer" valign="middle" align="center" style="width:'.$intSliderValue.'px; height:'.$intSliderValue.'px;">
	                              <div id="divThumbPosImg'.$objRow->id.'" class="thumbimgcontainer" style="'.$strDivThumbPosImgStyle.$strStyleOutput.'" ondblclick="myMedia.getSingleFileEditForm('.$objRow->id.','.$intDisplayLanuage.');">
	                                <table>
	                                  <tr>
	                                    <td><img id="Img'.$objRow->id.'" src="'.sprintf($this->core->sysConfig->media->paths->thumb, $objRow->path).$objRow->filename.'?v='.$objRow->version.'" style="'.$strStyleOutput.'" class="thumb" startWidth="'.$strStartWidth.'"/></td>
	                                    <!--<td class="thumbshadowright">&nbsp;</td>-->
	                                  </tr>
	                                  <!--<tr>
	                                    <td class="thumbshadowbottom">&nbsp;</td>
	                                    <td class="thumbshadowcorner">&nbsp;</td>
	                                  </tr>-->
	                                </table>
	                              </div>
	                            </td>
	                            <td width="5" style="background-color:#e4e4e4;">&nbsp;</td>
	                          </tr>
	                          <tr>
	                            <td width="5" height="5" style="font-size:0;line-height:0;"><img src="/zoolu-statics/images/main/corner_thumbhov_bttm_left.png" width="5" height="5"/></td>
	                            <td height="5" style="background-color:#e4e4e4;font-size:0;line-height:0;">&nbsp;</td>
	                            <td width="5" height="5" style="font-size:0;line-height:0;"><img src="/zoolu-statics/images/main/corner_thumbhov_bttm_right.png" width="5" height="5"/></td>
	                          </tr>
	                        </table>                                   
	                      </div>';
	
	      }else{
	        
	        /**
	         * document output with icon
	         */
	        $strOutput .= '<div id="divThumbContainerDoc'.$objRow->id.'" class="thumbcontainer" style="height:'.($intSliderValue + 10).'px; width:'.($intSliderValue + 10).'px;">
	                        <table>
	                          <tr>
	                            <td width="5" height="5" style="font-size:0;line-height:0;"><img src="/zoolu-statics/images/main/corner_thumbhov_top_left.png" width="5" height="5"/></td>
	                            <td height="5" style="background-color:#e4e4e4;font-size:0;line-height:0;">&nbsp;</td>
	                            <td width="5" height="5" style="font-size:0;line-height:0;"><img src="/zoolu-statics/images/main/corner_thumbhov_top_right.png" width="5" height="5"/></td>
	                          </tr>
	                          <tr>
	                            <td width="5" style="background-color:#e4e4e4;">&nbsp;</td>
	                            <td id="tdThumbDoc'.$objRow->id.'" fileid="'.$objRow->id.'" class="tdthumbcontainer" valign="middle" align="center" style="width:'.$intSliderValue.'px; height:'.$intSliderValue.'px;">
	                              <div id="divThumbPosDoc'.$objRow->id.'" class="thumbimgcontainer" style="'.$strDivThumbPosImgStyle.'width:'.$strStartWidth.'px;" ondblclick="myMedia.getSingleFileEditForm('.$objRow->id.','.$intDisplayLanuage.');">
	                                <table>
	                                  <tr>
	                                    <td><img id="Doc'.$objRow->id.'" src="'.$this->getDocIcon($objRow->extension, 32).'" style="width:'.$strStartWidth.'px;" class="thumb" startWidth="'.$strStartWidth.'"/></td>
	                                    <!--<td class="thumbshadowright">&nbsp;</td>-->
	                                  </tr>
	                                  <!--<tr>
	                                    <td class="thumbshadowbottom">&nbsp;</td>
	                                    <td class="thumbshadowcorner">&nbsp;</td>
	                                  </tr>-->
	                                </table>
	                              </div>
	                            </td>
	                            <td width="5" style="background-color:#e4e4e4;">&nbsp;</td>
	                          </tr>
	                          <tr>
	                            <td width="5" height="5" style="font-size:0;line-height:0;"><img src="/zoolu-statics/images/main/corner_thumbhov_bttm_left.png" width="5" height="5"/></td>
	                            <td height="5" style="background-color:#e4e4e4;font-size:0;line-height:0;">&nbsp;</td>
	                            <td width="5" height="5" style="font-size:0;line-height:0;"><img src="/zoolu-statics/images/main/corner_thumbhov_bttm_right.png" width="5" height="5"/></td>
	                          </tr>
	                        </table>                                   
	                      </div>';
	      }     
	    }	
    }else{
      $strOutput = 'Noch keine Medien vorhanden.';	
    }
    
    return $strOutput;
  }
  
  /**
   * getListHead
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function getListHead($strOrderColumn, $strOrderSort) {
  	$this->core->logger->debug('media->views->helpers->ViewHelper->getListHead()');
  	
  	$strOutput = '<tr>
          <th class="topcornerleft"></th>
          <th class="topcheckbox"><input id="listSelectAll" type="checkbox" name="listSelectAll" /></th>
          <th class="topicon"></th>
          <th class="toptitle'.(('alternativTitle' == $strOrderColumn) ? ' sort' : '').'" onclick="myList.sort(\'alternativTitle\''.(('alternativTitle' == $strOrderColumn && $strOrderSort == 'asc') ? ', \'desc\'' : ', \'asc\'').')">
            <div'.(('alternativTitle' == $strOrderColumn) ? ' class="'.$strOrderSort.'"' : '').'>'.$this->core->translate->_('title').'</div>
          </th>
          <th class="toptags'.(('tags' == $strOrderColumn) ? ' sort' : '').'" onclick="myList.sort(\'tags\''.(('tags' == $strOrderColumn && $strOrderSort == 'asc') ? ', \'desc\'' : ', \'asc\'').')">
            <div'.(('tags' == $strOrderColumn) ? ' class="'.$strOrderSort.'"' : '').'>'.$this->core->translate->_('Tags').'</div>
          </th>
          <th class="toplanguages'.(('languages' == $strOrderColumn) ? ' sort' : '').'" onclick="myList.sort(\'languages\''.(('languages' == $strOrderColumn && $strOrderSort == 'asc') ? ', \'desc\'' : ', \'asc\'').')">
            <div'.(('languages' == $strOrderColumn) ? ' class="'.$strOrderSort.'"' : '').'>'.$this->core->translate->_('Languages').'</div>
          </th>
          <th class="toplanguagespecific'.(('isLanguageSpecific' == $strOrderColumn) ? ' sort' : '').'" onclick="myList.sort(\'isLanguageSpecific\''.(('isLanguageSpecific' == $strOrderColumn && $strOrderSort == 'asc') ? ', \'desc\'' : ', \'asc\'').')">
            <div'.(('isLanguageSpecific' == $strOrderColumn) ? ' class="'.$strOrderSort.'"' : '').'>'.$this->core->translate->_('Language_specific').'</div>
          </th>
          <th class="topauthor'.(('creator' == $strOrderColumn) ? ' sort' : '').'" onclick="myList.sort(\'creator\''.(('creator' == $strOrderColumn && $strOrderSort == 'asc') ? ', \'desc\'' : ', \'asc\'').')">
            <div'.(('creator' == $strOrderColumn) ? ' class="'.$strOrderSort.'"' : '').'>'.$this->core->translate->_('Author').'</div>
          </th>
          <th class="topcreated'.(('created' == $strOrderColumn) ? ' sort' : '').'" onclick="myList.sort(\'created\''.(('created' == $strOrderColumn && $strOrderSort == 'asc') ? ', \'desc\'' : ', \'asc\'').')">
            <div'.(('created' == $strOrderColumn) ? ' class="'.$strOrderSort.'"' : '').'>'.$this->core->translate->_('uploaded').'</div>
          </th>
          <th class="topcornerright"></th>
        </tr>';
  	return $strOutput;
  }
  
  /**
   * getListTitle
   * @param string $strSearchValue
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function getListTitle($objPaginator, $strSearchValue = '') {
  	$strOutput = '';
  	if($strSearchValue != '') {
  	  if(count($objPaginator) > 0){
        $strOutput = '
            <div class="formsubtitle searchtitle">'.sprintf($this->core->translate->_('Search_for_'), $strSearchValue).'</div>'; 
      }else{
        $strOutput = '
            <div class="formsubtitle searchtitle">'.sprintf($this->core->translate->_('No_search_results_for_'), $strSearchValue).'</div>';   
      }
      $strOutput .= '
            <div class="bttnSearchReset" onclick="myList.resetSearch();">
              <div class="button17leftOff"></div>
              <div class="button17centerOff">
                <div>'.$this->core->translate->_('Reset').'</div>
                <div class="clear"></div>
              </div>
              <div class="button17rightOff"></div>
              <div class="clear"></div>
            </div>
            <div class="clear"></div>';
  	}
  	return $strOutput;
  }
  
  /**
   * getListView
   * @param object $rowset  
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getListView($objRowset){
    $this->core->logger->debug('media->views->helpers->ViewHelper->getListView()');
    
    $strOutput = '';
    if(count($objRowset) > 0){
	    foreach ($objRowset as $objRow) {
	      
	      $created = new DateTime($objRow->created);
	      
	      if(strpos($objRow->mimeType, 'image/') !== false){
	        $strFileIconSrc = sprintf($this->core->sysConfig->media->paths->icon32, $objRow->path).$objRow->filename.'?v='.$objRow->version; 
	      }else{        
	        $strFileIconSrc = $this->getDocIcon($objRow->extension, 32);
	      }
	      
	      $intDisplayLanuage = ($objRow->idLanguages == '') ? (isset($objRow->alternativLanguageId) && $objRow->alternativLanguageId != '')  ? $objRow->alternativLanguageId : $this->core->intZooluLanguageId : $objRow->idLanguages;
	      
	      /**
	       * list row entry
	       */
	      $strOutput .= '<tr id="Row'.$objRow->id.'" class="listrow" fileid="'.$objRow->id.'">
	                      <td colspan="2" class="rowcheckbox"><input type="checkbox" id="listSelect'.$objRow->id.'" name="listSelect'.$objRow->id.'" value="'.$objRow->id.'" class="listSelectRow"/></td>
	                      <td class="rowicon"><img width="32" height="32" src="'.$strFileIconSrc.'" alt="'.htmlentities($objRow->description, ENT_COMPAT, $this->core->sysConfig->encoding->default).'" ondblclick="myMedia.getSingleFileEditForm('.$objRow->id.','.$intDisplayLanuage.');"/></td>
	                      <td class="rowtitle">'.htmlentities((($objRow->title == '' && (isset($objRow->alternativTitle) || isset($objRow->fallbackTitle))) ? ((isset($objRow->alternativTitle) && $objRow->alternativTitle != '') ? $objRow->alternativTitle : $objRow->fallbackTitle) : $objRow->title), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</td>
	                      <td class="rowtags">'.$objRow->tags.'</td>
	                      <td class="rowlanguages">'.$objRow->languages.'</td>
	                      <td class="rowlanguagespecific">'.($objRow->isLanguageSpecific ? $this->core->translate->_('yes') : $this->core->translate->_('no')).'</td>
	                      <td class="rowauthor">'.$objRow->creator.'</td>
	                      <td colspan="2" class="rowcreated">'.$created->format('d.m.y, H:i').'</td>
	                    </tr>';
	    } 
    }
    
    return $strOutput;
  }
  
  /**
   * getDashboardListOutput 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getDashboardListOutput($objRowset){
    $this->core->logger->debug('media->views->helpers->ViewHelper->getDashboardListOutput()');

    $strOutput = '';
    if(count($objRowset) > 0){
      foreach ($objRowset as $objRow){
	      $created = new DateTime($objRow->created);
	      
	      if(strpos($objRow->mimeType, 'image/') !== false){
	        $strFileIconSrc = sprintf($this->core->sysConfig->media->paths->icon32, $objRow->path).$objRow->filename.'?v='.$objRow->version;  
	      }else{        
	        $strFileIconSrc = $this->getDocIcon($objRow->extension, 32);
	      }
	      
	      $strOutput .= '
	                      <tr class="listrow" id="Row'.$objRow->id.'">
	                        <td class="rowcheckbox" colspan="2"><input type="checkbox" class="listSelectRow" value="'.$objRow->id.'" name="listSelect'.$objRow->id.'" id="listSelect'.$objRow->id.'"/></td>
	                        <td class="rowicon"><img width="32" height="32" src="'.$strFileIconSrc.'" alt="'.htmlentities($objRow->description, ENT_COMPAT, $this->core->sysConfig->encoding->default).'" ondblclick="myMedia.getSingleFileEditForm('.$objRow->id.');"/></td>
	                        <td class="rowtitle"><a href="#" onclick="myNavigation.loadNavigationTree('.$objRow->idParent.', \'folder\'); return false;">'.htmlentities((($objRow->title == '' && (isset($objRow->alternativTitle) || isset($objRow->fallbackTitle))) ? ((isset($objRow->alternativTitle) && $objRow->alternativTitle != '') ? $objRow->alternativTitle : $objRow->fallbackTitle) : $objRow->title), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a></td>
	                        <td class="rowauthor">'.$objRow->creator.'</td>
	                        <td class="rowcreated" colspan="2">'.$created->format('d.m.y, H:i').'</td>
	                      </tr>'; 
	    }	
    }  
    return $strOutput;    
  }
  
  /**
   * getEditForm
   * @param object $rowset 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getEditForm($rowset){
  	$this->core->logger->debug('media->views->helpers->ViewHelper->getEditForm()');
  	
  	$strOutput = '';
  	
  	foreach ($rowset as $row) {
      
  	  if(strpos($row->mimeType, 'image/') !== false){
        $strFileIconSrc = sprintf($this->core->sysConfig->media->paths->icon32, $row->path).$row->filename.'?v='.$row->version;  
      }else{        
        $strFileIconSrc = $this->getDocIcon($row->extension, 32);
      }

      if($row->description != ''){
        $strDescription = $row->description;
        $strTextareaCss = ' class="textarea"';      
      }else{
        $strDescription = $this->core->translate->_('Add_description_');
        $strTextareaCss = '';
      }
            
      // build the element
      $strTags = '';
      
      if($row->idLanguages != ''){
        require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Tags.php'; // FIXME : quick and dirty solution
        $objModelTags = new Model_Tags(); 
        $objModelTags->setLanguageId($row->idLanguages);     
        $objTags = $objModelTags->loadTypeTags('file', $row->id, 1); // TODO : version      
        
      	if(count($objTags) > 0){
          foreach($objTags as $objTag){ 
            $strTags .= '<li value="'.$objTag->id.'">'.htmlentities($objTag->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</li>';        
          }
        }
      }
      
      $strLanguageSpecificChecked = ($row->isLanguageSpecific == 1) ? ' checked="checked"' : '';
       
  		$strOutput .= '<div class="mediacontainer">
                       <div class="mediaicon"><img width="32" height="32" src="'.$strFileIconSrc.'"/></div>
                       <div class="mediainfos">
                         <div class="mediainfotitle"><input type="text" value="'.$row->title.'" id="FileTitle'.$row->id.'" name="FileTitle'.$row->id.'"/></div>
                         <div class="mediainfodescription"><textarea onfocus="myMedia.setFocusTextarea(this.id); return false;" id="FileDescription'.$row->id.'" name="FileDescription'.$row->id.'"'.$strTextareaCss.'>'.$strDescription.'</textarea></div>
                         <div class="mediainfotags">
                           <ol>        
                             <li class="autocompletList input-text">
                               <input type="text" value="" id="FileTags'.$row->id.'" name="FileTags'.$row->id.'"/>
                               <div id="FileTags'.$row->id.'_autocompleter" class="autocompleter">
                                 <div class="default">'.$this->core->translate->_('Search_or_add_tags').'</div> 
                                 <ul class="feed">
                                   '.$strTags.'  
                                 </ul>
                               </div>
                             </li>
                           </ol>
                           <script type="text/javascript" language="javascript">//<![CDATA[
                             FileTags'.$row->id.'_list = new FacebookList(\'FileTags'.$row->id.'\', \'FileTags'.$row->id.'_autocompleter\',{ newValues: true, regexSearch: true });
                             '.$this->getAllTagsForAutocompleter('FileTags'.$row->id).'
                             //]]>
                           </script>                           
                         </div>
                         <div class="mediainfolanguagespecific">
                           <div class="field">
                             <label for="FileIsLanguageSpecific'.$row->id.'"><input type="checkbox"'.$strLanguageSpecificChecked.' class="multiCheckbox" value="1" id="FileIsLanguageSpecific'.$row->id.'" name="FileIsLanguageSpecific'.$row->id.'"> '.$this->core->translate->_('Medium_is_language_specific').'</label>                            
                           </div>
                         </div>
                         <div class="clear"></div>  
                       </div>
                       <div class="clear"></div> 
                     </div>';  		
  	}
  	return $strOutput;
  }
  
  /**
   * getSingleEditForm
   * @param Zend_Db_Table_Rowset_Abstract $objFileData
   * @param array $arrImagesSizes 
   * @author Thomas Schedler <tsh@massiveart.com>   
   */
  public function getSingleEditForm(Zend_Db_Table_Rowset_Abstract $objFileData, $arrImagesSizes, $strDestinationOptions, $strGroupOptions, $objFileVersions = null, $blnAuthorizedToUpdate = true){
    $this->core->logger->debug('media->views->helpers->ViewHelper->getSingleEditForm()');
    
    $strOutput = '';
    $strMediaType = '';
    
    if(count($objFileData) == 1) {
      $objFile = $objFileData->current();
      
      if(strpos($objFile->mimeType, 'image/') !== false){
        $blnIsImage = true;
        $strFileIconSrc = sprintf($this->core->sysConfig->media->paths->thumb, $objFile->path).$objFile->filename.'?v='.$objFile->version;
        $strDownloadLink = '/zoolu-website/media/image/'.$objFile->id.'/'.urlencode(str_replace('.', '-', $objFile->title)); 
        $strBasePath = (($this->core->config->domains->static->components != '') ? $this->core->config->domains->static->components : 'http://'.$_SERVER['HTTP_HOST']).$this->core->sysConfig->media->paths->imgbase.$objFile->path;
      }else if (strpos($objFile->mimeType, 'video/') !== false) {
        $blnIsImage = false; 
        $strMediaType = 'video';       
        $strFileIconSrc = $this->getDocIcon($objFile->extension, 128);
        $strDownloadLink = '/zoolu-website/media/video/'.$objFile->id.'/'.urlencode(str_replace('.', '-', $objFile->title));
        $strBasePath = (($this->core->config->domains->static->components != '') ? $this->core->config->domains->static->components : 'http://'.$_SERVER['HTTP_HOST']).$this->core->sysConfig->media->paths->vidbase.$objFile->path;  
      }else{
        $blnIsImage = false;  
        $strMediaType = 'document';      
        $strFileIconSrc = $this->getDocIcon($objFile->extension, 128); 
        $strDownloadLink = '/zoolu-website/media/document/'.$objFile->id.'/'.urlencode(str_replace('.', '-', $objFile->title));
        $strBasePath = (($this->core->config->domains->static->components != '') ? $this->core->config->domains->static->components : 'http://'.$_SERVER['HTTP_HOST']).$this->core->sysConfig->media->paths->docbase.$objFile->path;
      }

      if($objFile->description != ''){
        $strDescription = $objFile->description;
        $strTextareaCss = ' class="textarea"';      
      }else{
        $strDescription = $this->core->translate->_('Add_description_');
        $strTextareaCss = '';
      }
            
      // build the element
      $strTags = '';
      
      if($objFile->idLanguages != ''){
        require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Tags.php'; // FIXME : quick and dirty solution
        $objModelTags = new Model_Tags(); 
        $objModelTags->setLanguageId($objFile->idLanguages);     
        $objTags = $objModelTags->loadTypeTags('file', $objFile->id, 1); // TODO : version      
        
        if(count($objTags) > 0){
          foreach($objTags as $objTag){ 
            $strTags .= '<li value="'.$objTag->id.'">'.htmlentities($objTag->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</li>';        
          }
        }
      }
      
      $strLanguageSpecificChecked = ($objFile->isLanguageSpecific == 1) ? ' checked="checked"' : '';
      $strDestinationeSpecificChecked = ($objFile->idDestination > 0) ? ' checked="checked"' : '';
      $strGroupSpecificChecked = ($objFile->idGroup > 0) ? ' checked="checked"' : '';
       
      $strOutput .= '<div class="mediacontainer">
                       <div class="mediaicon">
                          <a href="'.$strDownloadLink.'" target="_blank"><img width="128" src="'.$strFileIconSrc.'"/></a><br/>';
      if($blnIsImage == false){
        $strOutput .= '<div class="spacer2" style="text-align:center">'.$objFile->downloadCounter.' Downloads</div>';
      }
      
      $strOutput .= '
                         <div class="field">
                           <label for="FileIsLanguageSpecific'.$objFile->id.'"><input type="checkbox"'.$strLanguageSpecificChecked.' class="multiCheckbox" value="1" id="FileIsLanguageSpecific'.$objFile->id.'" name="FileIsLanguageSpecific'.$objFile->id.'"> '.$this->core->translate->_('Language_specific').'</label>                            
                         </div>';
      
      if($blnIsImage == false){
        $strOutput .= '
                         <div class="field">
                           <label for="FileIsDestinationSpecific'.$objFile->id.'"><input type="checkbox"'.$strDestinationeSpecificChecked.' onclick="myMedia.toggleDestinationOptions(this, \''.$objFile->id.'\');" class="multiCheckbox" value="1" id="FileIsDestinationSpecific'.$objFile->id.'" name="FileIsDestinationSpecific'.$objFile->id.'"> '.$this->core->translate->_('Region_specific').'</label>
                           <div id="shownDestinationOptions'.$objFile->id.'"'.(($objFile->idDestination == 0) ? ' style="display:none;"' : '').'>
                             <input type="hidden" value="'.$objFile->idDestination.'" name="FileDestinationId'.$objFile->id.'" id="FileDestinationId'.$objFile->id.'"/>
                             <select name="selectFileDestinationId'.$objFile->id.'" id="selectFileDestinationId'.$objFile->id.'" onchange="$(\'FileDestinationId'.$objFile->id.'\').value = this.value;">
                               '.$strDestinationOptions.'
                             </select>                          
                           </div>
                         </div>';
        
        $strOutput .= '
                         <div class="field">
                           <label for="FileIsGroupSpecific'.$objFile->id.'"><input type="checkbox"'.$strGroupSpecificChecked.' onclick="myMedia.toggleGroupOptions(this, \''.$objFile->id.'\');" class="multiCheckbox" value="1" id="FileIsGroupSpecific'.$objFile->id.'" name="FileIsGroupSpecific'.$objFile->id.'"> '.$this->core->translate->_('Group_specific').'</label>
                           <div id="shownGroupOptions'.$objFile->id.'"'.(($objFile->idGroup == 0) ? ' style="display:none;"' : '').'>
                             <input type="hidden" value="'.$objFile->idGroup.'" name="FileGroupId'.$objFile->id.'" id="FileGroupId'.$objFile->id.'"/>
                             <select name="selectFileGroupId'.$objFile->id.'" id="selectFileGroupId'.$objFile->id.'" onchange="$(\'FileGroupId'.$objFile->id.'\').value = this.value;">
                               '.$strGroupOptions.'
                             </select>                          
                           </div>
                         </div>';
      }
      
      $strOutput .= '    
                       </div>
                       <div class="mediainfos">
                         <div class="mediainfotitle"><label for="FileTitle'.$objFile->id.'" class="gray666 bold">'.$this->core->translate->_('Title').'</label><br/><input type="text" value="'.$objFile->title.'" id="FileTitle'.$objFile->id.'" name="FileTitle'.$objFile->id.'"/></div>
                         <div class="mediainfodescription"><textarea onfocus="myMedia.setFocusTextarea(this.id); return false;" id="FileDescription'.$objFile->id.'" name="FileDescription'.$objFile->id.'"'.$strTextareaCss.'>'.$strDescription.'</textarea></div>
                         <div class="mediainfotags">
                           <label for="FileTitle'.$objFile->id.'" class="gray666 bold">'.$this->core->translate->_('Tags').'</label><br/>
                           <ol>        
                             <li class="autocompletList input-text">
                               <input type="text" value="" id="FileTags'.$objFile->id.'" name="FileTags'.$objFile->id.'"/>
                               <div id="FileTags'.$objFile->id.'_autocompleter" class="autocompleter">
                                 <div class="default">'.$this->core->translate->_('Search_or_add_tags').'</div> 
                                 <ul class="feed">
                                   '.$strTags.'  
                                 </ul>
                               </div>
                             </li>
                           </ol>
                           <script type="text/javascript" language="javascript">//<![CDATA[
                             FileTags'.$objFile->id.'_list = new FacebookList(\'FileTags'.$objFile->id.'\', \'FileTags'.$objFile->id.'_autocompleter\',{ newValues: true, regexSearch: true });
                             '.$this->getAllTagsForAutocompleter('FileTags'.$objFile->id).'
                             //]]>
                           </script>                           
                         </div>                         
                         <div class="clear"></div>';
      if($blnIsImage == false) {
        $strOutput .= '
                         <div class="mediafield">
                           <label class="gray666 bold">'.$this->core->translate->_('Preview_image').'</label>
                           <div class="mediatop">
                             '.$this->core->translate->_('Add_medias').': <img src="/zoolu-statics/images/icons/icon_addmedia.png" width="16" height="16" onclick="myMedia.getAddMediaOverlay(\'divMediaContainer_'.$strMediaType.'pic\')" />
                           </div>
                           <div id="divMediaContainer_'.$strMediaType.'pic" class="media">
                           </div>
                           <input type="hidden" name="'.$strMediaType.'pic" id="'.$strMediaType.'pic" value="'.(($objFile->idFiles != null && $objFile->idFiles > 0) ? '['.$objFile->idFiles.']' : '').'" /> 
                         </div>'; //FIXME: use syntax for applying one file
      }
      if($blnIsImage == true){
        $strOutput .= '
                       </div>
                       <div class="clear"></div>
                       <div class="spacer1"></div>';
      }
                  
      $strMediaUrl = '';
      if($blnIsImage == true){
        $strOutput .= '
                       <div class="mediasizes">
                         <select id="mediaSizes" onchange="$(\'singleMediaUrl\').value = $F(\'singleFileBasePath\') + this.value + $F(\'singleFileName\')">';
        foreach($arrImagesSizes as $arrImageSize){
          if(isset($arrImageSize['display']) && isset($arrImageSize['display']['single_edit'])){
            if($strMediaUrl == '') $strMediaUrl = $strBasePath.$arrImageSize['folder'].'/'.$objFile->filename.'?v='.$objFile->version;
            $strOutput .= '
                           <option value="'.$arrImageSize['folder'].'/">'.$arrImageSize['folder'].'</option>';
          }
        }
        $strOutput .= '
                         </select>
                       </div>';
      }else{
        $strMediaUrl = 'http://'.$_SERVER['HTTP_HOST'].$strDownloadLink;
      }
      
      $strOutput .= '
                       <div class="medialink">
                         <input type="text" id="singleMediaUrl" readonly="readonly" value="'.$strMediaUrl.'" onclick="this.select()"/><br/>
                         <div id="d_clip_container" style="float:right; margin:0 10px 0 0; position:relative;">
                           <div id="d_clip_button">[copy to clipboard]</div>
                         </div>
                         <div class="clear"></div> 
                         <input type="hidden" id="singleFileName" value="'.$objFile->filename.'?v='.$objFile->version.'"/>
                         <input type="hidden" id="singleFileBasePath" value="'.$strBasePath.'"/>
                       </div>                       
                       <div class="clear"></div>
                       
                       <div class="spacer1"></div>
                       <div class="mediasingleupload">';
      if($blnAuthorizedToUpdate == true){
        $strOutput .= '
                         <label for="txtFileName" class="gray666 bold">'.$this->core->translate->_('New_version').'</label><br/>
                         <div>
                           <input type="text" id="txtFileName" disabled="true" />
                           <span id="spanButtonPlaceholder"></span>
                         </div>
                         <div id="fsUploadProgress"></div>';
      }
      
      if($blnIsImage == false){
        $strOutput .= '
                       </div>';
      }
      
      if($objFileVersions != null && $objFileVersions instanceof Zend_Db_Table_Rowset_Abstract){
        $objCreated = DateTimeHelper::getDateObject();
        $objArchived = DateTimeHelper::getDateObject();
        
        $strOutput .= '
                         <div class="spacer1"></div>
                         <label for="txtFileName" class="gray666 bold">'.$this->core->translate->_('Version_list').'</label><br/>
                         <div class="mediaversions">';
        foreach($objFileVersions as $objFileVersion){
          if($objFileVersion->created != null && $objFileVersion->archived != null){
            $objCreated->set($objFileVersion->created);
            $objArchived->set($objFileVersion->archived);
            $strOutput .= '
                           <div class="version"><a href="/zoolu-website/media/download/'.$objFileVersion->idFiles.'/'.urlencode(str_replace('.', '-', $objFileVersion->title)).'?v='.$objFileVersion->version.'" target="_blank">'.$this->core->translate->_('Version').' '.$objFileVersion->version.'</a> ('.$objCreated->get(Zend_Date::DATETIME_MEDIUM).' - '.$objArchived->get(Zend_Date::DATETIME_MEDIUM).')</div>';
          }  
        }

        $strOutput .= '
                         </div>';
      }
      
      $strOutput .= '
                       </div>
                       <div class="clear"></div>
                     </div>';
    }
    return $strOutput;
  }
  
  /**
   * getAllTagsForAutocompleter
   * @param string $strElementId
   * @return string $strAllTags
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getAllTagsForAutocompleter($strElementId){
    require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/Tags.php';
    $objModelTags = new Model_Tags();      
    $objAllTags = $objModelTags->loadAllTags();
    
    $strAllTags = '';
    if(count($objAllTags) > 0){
      $strAllTags .= 'var '.$strElementId.'_json = [';
      foreach($objAllTags as $objTag){
        $strAllTags .= '{"caption":"'.htmlentities($objTag->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'","value":'.$objTag->id.'},';
      }
      $strAllTags = trim($strAllTags, ',');
      $strAllTags .= '];';
      $strAllTags .= $strElementId.'_json.each(function(t){'.$strElementId.'_list.autoFeed(t)})';   
    }
    return $strAllTags;
  }
  
  /**
   * getDocIcon
   * @param string $strDocumentExtension, integer $intSize 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getDocIcon($strDocumentExtension, $intSize){
    
  	switch(strtolower($strDocumentExtension)){
      case 'docx' :
      case 'doc' :
      case 'rtf' :
        //$strDocIcon = '/zoolu-statics/images/icons/docs/icon_word_'.$intSize.'.png';
        $strDocIcon = '/zoolu-statics/images/icons/docs/icon_word.png';
        break;
      case 'xlsx' :
      case 'xls' :
        //$strDocIcon = '/zoolu-statics/images/icons/docs/icon_excel_'.$intSize.'.png';
        $strDocIcon = '/zoolu-statics/images/icons/docs/icon_excel.png';
        break;
      case 'pdf' :
        //$strDocIcon = '/zoolu-statics/images/icons/docs/icon_pdf_'.$intSize.'.png';
        $strDocIcon = '/zoolu-statics/images/icons/docs/icon_pdf.png';
        break;
      case 'ppt' :
      case 'pps' :
      case 'pptx' :
      case 'ppsx' :
      case 'ppz' :
      case 'pot' :
        //$strDocIcon = '/zoolu-statics/images/icons/docs/icon_ppt_'.$intSize.'.png';
        $strDocIcon = '/zoolu-statics/images/icons/docs/icon_ppt.png';
        break;
      case 'zip' :
      case 'rar' :
      case 'tar' :
      case 'ace' :
        //$strDocIcon = '/zoolu-statics/images/icons/docs/icon_zip_'.$intSize.'.png';
        $strDocIcon = '/zoolu-statics/images/icons/docs/icon_compressed.png';
        break;
      case 'avi' :
      case 'mov' :
      case 'mp4' :
      case 'swf' :
      case 'mpg' :
      case 'mpeg' :
      case 'wmv' :
      case 'f4v' :
      case 'flv' :
      	$strDocIcon = '/zoolu-statics/images/icons/docs/icon_movie.png';
      	break;
      case 'mp3' :
      case 'wav' :
      case 'f4a' :
      case 'wma' :
      case 'aif' :
        $strDocIcon = '/zoolu-statics/images/icons/docs/icon_audio.png';
      	break;
      default :
        //$strDocIcon = '/zoolu-statics/images/icons/docs/icon_default_'.$intSize.'.png';
        $strDocIcon = '/zoolu-statics/images/icons/docs/icon_unknown.png';
        break;
    }
    return $strDocIcon;
    
  }
}

?>