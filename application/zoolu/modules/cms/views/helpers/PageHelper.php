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
 * @package    application.zoolu.modules.cms.views
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * PageHelper
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-09: Cornelius Hansjakob
 * 
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

require_once (dirname(__FILE__).'/../../../media/views/helpers/ViewHelper.php');

class PageHelper {
  
  /**
   * @var Core
   */
  private $core;
  
  /**
   * @var ViewHelper
   */
  private $objViewHelper;
  
  const NUMBER_OF_ENTRIES = 4;
  
  /**
   * Constructor 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  }
  
  /**
   * getFilesOutput 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getFilesOutput($rowset, $strFieldName, $strViewType){
    $this->core->logger->debug('cms->views->helpers->PageHelper->getFilesOutput()');
    
    $this->objViewHelper = new ViewHelper();
      
    $strOutput = '';
    if(count($rowset) > 0){
      foreach ($rowset as $row){
        if($strViewType != '' && $strViewType == $this->core->sysConfig->viewtypes->thumb){    
      	  if($row->isImage){
  	        if($row->xDim < $row->yDim){
  	          $strMediaSize = 'height="100"';
  	        }else{
  	          $strMediaSize = 'width="100"';  
  	        }  
            $strOutput .= '<div style="position: relative;" class="mediaitem" fileid="'.$row->id.'" id="'.$strFieldName.'_mediaitem_'.$row->id.'">
  	                         <table>
  	                           <tbody>
  	                             <tr>
  	                               <td>
  	                                 <img src="'.sprintf($this->core->sysConfig->media->paths->thumb, $row->path).$row->filename.'?v='.$row->version.'" id="Img'.$row->id.'" '.$strMediaSize.'/>
  	                               </td>
  	                             </tr>
  	                           </tbody>
  	                         </table>                      
  	                         <div class="itemremovethumb" id="'.$strFieldName.'_remove'.$row->id.'" onclick="myForm.removeItem(\''.$strFieldName.'\', \''.$strFieldName.'_mediaitem_'.$row->id.'\', '.$row->id.'); return false;"></div>
  	                       </div>';
          }
        }else{
        	if($row->isImage){
  	      	$strOutput .= '<div class="fileitem" fileid="'.$row->id.'" id="'.$strFieldName.'_fileitem_'.$row->id.'" style="position:relative;">
  						               <div class="olfileleft"></div>
  	      	                 <div class="itemremovelist" id="'.$strFieldName.'_remove'.$row->id.'" onclick="myForm.removeItem(\''.$strFieldName.'\', \''.$strFieldName.'_fileitem_'.$row->id.'\', '.$row->id.'); return false;"></div>  
  						               <div class="olfileitemicon"><img width="32" height="32" src="'.sprintf($this->core->sysConfig->media->paths->icon32, $row->path).$row->filename.'?v='.$row->version.'" id="File'.$row->id.'" alt="'.htmlentities($row->description, ENT_COMPAT, $this->core->sysConfig->encoding->default).'"/></div>
  						               <div class="olfileitemtitle">'.htmlentities((($row->title == '' && (isset($row->alternativTitle) || isset($row->fallbackTitle))) ? ((isset($row->alternativTitle) && $row->alternativTitle != '') ? $row->alternativTitle : $row->fallbackTitle) : $row->title), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
  						               <div class="clear"></div>
  						             </div>';
        	}else{
        	  $strOutput .= '<div class="fileitem" fileid="'.$row->id.'" id="'.$strFieldName.'_fileitem_'.$row->id.'" style="position:relative;">
                             <div class="olfileleft"></div>
        	                   <div class="itemremovelist" id="'.$strFieldName.'_remove'.$row->id.'" onclick="myForm.removeItem(\''.$strFieldName.'\', \''.$strFieldName.'_fileitem_'.$row->id.'\', '.$row->id.'); return false;"></div>  
                             <div class="olfileitemicon"><img width="32" height="32" src="'.$this->objViewHelper->getDocIcon($row->extension, 32).'" id="File'.$row->id.'" alt="'.htmlentities($row->description, ENT_COMPAT, $this->core->sysConfig->encoding->default).'"/></div>
                             <div class="olfileitemtitle">'.htmlentities((($row->title == '' && (isset($row->alternativTitle) || isset($row->fallbackTitle))) ? ((isset($row->alternativTitle) && $row->alternativTitle != '') ? $row->alternativTitle : $row->fallbackTitle) : $row->title), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>              
                             <div class="clear"></div>
                           </div>';		
        	}
        }
      }  
    }  
    return $strOutput.'<div id="divClear_'.$strFieldName.'" class="clear"></div>';
  }
  
  /**
   * getOverlayFilesOutput 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getOverlayFilesOutput($rowset, $strFieldName, $intViewType, $arrCurrFileIds = array()){
    $this->core->logger->debug('cms->views->helpers->PageHelper->getOverlayFilesOutput(fieldname: '.$strFieldName.' - viewtype: '.$intViewType.')');
    
    $strReturn = '';
    
    $this->objViewHelper = new ViewHelper();    
    
    if($intViewType == $this->core->sysConfig->viewtypes->thumb){

      $strOutputTop = '<div class="olmediacontainer">';
  
      /**
       * output of each thumb
       */
      $strOutput = '';
      foreach ($rowset as $row) {
      	if($row->isImage){
      		$strHidden = '';
  
      		if($row->xDim < $row->yDim){
  	        $strMediaSize = 'height="100"';
  	      }else{
  	        $strMediaSize = 'width="100"';
  	      }
  
          if(array_search($row->id, $arrCurrFileIds) !== false){
            $strHidden = ' style="display:none;"';
          }
  
  	      $strOutput .= '<div id="olMediaItem'.$row->id.'" class="olmediaitem" fileid="'.$row->id.'"'.$strHidden.'>
                           <table>
                             <tbody>
                               <tr>
                                 <td>
                                   <img onclick="myOverlay.addItemToThumbArea(\'olMediaItem'.$row->id.'\', '.$row->id.'); return false;" id="Img'.$row->id.'" alt="'.$row->title.'" title="'.$row->title.'" src="'.sprintf($this->core->sysConfig->media->paths->thumb, $row->path).$row->filename.'?v='.$row->version.'" '.$strMediaSize.'/>
                                 </td>
                               </tr>
                             </tbody>
                           </table>
                           <div id="Remove'.$row->id.'" class="itemremovethumb" style="display:none;"></div>
                         </div>';
  	    }
      }
  
      /**
       * return html output
       */
      if($strOutput != ''){
  	    $strReturn = $strOutputTop.$strOutput.'
  		           <div class="clear"></div>
  		         </div>';
      }
      
    }else{
      /**
       * create header of list output
       */
      $strOutputTop = '
              <div>
                <div class="olfiletopleft"></div>
                <div class="olfiletopitemicon"></div>
                <div class="olfiletopitemtitle bold">Titel</div>
                <div class="olfiletopright"></div>
                <div class="clear"></div>
              </div>';
      
      /**
       * output of list rows (elements)
       */
      $strOutput = '';
      $strOutputMedia = '';
      
      $blnIsImageView = false;
      
      if(count($rowset) > 0){
        $strOutput .= '  
              <div class="olfileitemcontainer">';
        foreach ($rowset as $row) {      	
        	if(!in_array($row->id, $arrCurrFileIds)){
            if($row->isImage){
          	  $blnIsImageView = true;
              if($row->xDim < $row->yDim){
                $strMediaSize = 'height="32"';
              }else{
                $strMediaSize = 'width="32"';
              }
            	$strOutputMedia .= '
                  <div class="olfileitem" id="olFileItem'.$row->id.'" onclick="myOverlay.addItemToThumbArea(\'olFileItem'.$row->id.'\', '.$row->id.'); return false;">
                    <div class="olfileleft"></div>
                    <div style="display:none;" id="Remove'.$row->id.'" class="itemremovelist"></div>
                    <div class="olfileitemicon"><img '.$strMediaSize.' id="File'.$row->id.'" src="'.sprintf($this->core->sysConfig->media->paths->icon32, $row->path).$row->filename.'?v='.$row->version.'" alt="'.$row->description.'"/></div>
                    <div class="olfileitemtitle">'.htmlentities((($row->title == '' && (isset($row->alternativTitle) || isset($row->fallbackTitle))) ? ((isset($row->alternativTitle) && $row->alternativTitle != '') ? $row->alternativTitle : $row->fallbackTitle) : $row->title), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
                    <div class="olfileright"></div>
                    <div class="clear"></div>
                  </div>';
            }else{
              $strOutputMedia .= '
                  <div class="olfileitem" id="olFileItem'.$row->id.'" onclick="myOverlay.addFileItemToListArea(\'olFileItem'.$row->id.'\', '.$row->id.'); return false;">
                    <div class="olfileleft"></div>
                    <div style="display:none;" id="Remove'.$row->id.'" class="itemremovelist"></div>
                    <div class="olfileitemicon"><img width="32" height="32" id="File'.$row->id.'" src="'.$this->objViewHelper->getDocIcon($row->extension, 32).'" alt="'.$row->description.'"/></div>
                    <div class="olfileitemtitle">'.htmlentities((($row->title == '' && (isset($row->alternativTitle) || isset($row->fallbackTitle))) ? ((isset($row->alternativTitle) && $row->alternativTitle != '') ? $row->alternativTitle : $row->fallbackTitle) : $row->title), ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
                    <div class="olfileright"></div>
                    <div class="clear"></div>
                  </div>';
            }
        	}
        }
        
        $strOutput .= $strOutputMedia.'
                <div class="clear"></div>
              </div>';
      }
      
      /**
       * list footer
       */
      $strOutputBottom = '
              <div>
                <div class="olfilebottomleft"></div>
                <div class="olfilebottomcenter"></div>
                <div class="olfilebottomright"></div>
                <div class="clear"></div>
              </div>';
  
      /**
       * return html output
       */
      if($strOutputMedia != ''){
        if($blnIsImageView){
          $strReturn = $strOutput.'<div class="clear"></div>';
        }else{
          $strReturn = $strOutputTop.$strOutput.$strOutputBottom.'<div class="clear"></div>';
        }    	
      }
    }
    
    return $strReturn;
  }
  
  /**
   * getContactOutput 
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getContactOutput($rowset, $strFieldName){
    $this->core->logger->debug('cms->views->helpers->PageHelper->getContactOutput()');
  
    $strOutput = '';
    foreach ($rowset as $row){ 
      $strOutput .= '<div class="contactitem" fileid="'.$row->id.'" id="'.$strFieldName.'_contactitem_'.$row->id.'">
                       <div class="olcontactleft"></div>
                       <div class="itemremovelist" id="'.$strFieldName.'_remove_'.$row->id.'" onclick="myForm.removeItem(\''.$strFieldName.'\', \''.$strFieldName.'_contactitem_'.$row->id.'\', '.$row->id.'); return false;"></div>  
                       <div class="olcontactitemicon">';
      if($row->filename != ''){
        $strOutput .= '<img width="32" height="32" src="'.sprintf($this->core->sysConfig->media->paths->icon32, $row->filepath).$row->filename.'?v='.$row->fileversion.'" id="Contact'.$row->id.'" alt="'.htmlentities($row->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'"/>';
      }
      $strOutput .= '  </div>
                       <div class="olcontactitemtitle">'.htmlentities($row->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
                       <div class="clear"></div>
                     </div>';
    }    
    
    /**
     * add the scriptaculous sortable funcionality
     */
     $strOutput .= '<script type="text/javascript" language="javascript"//<![CDATA[
                      myForm.initSortable(\''.$strFieldName.'\', \'divContactContainer_'.$strFieldName.'\', \'contactitem\', \'div\', \'itemid\', \'vertical\');
                    //]]></script>';
     
    return $strOutput.'<div id="divClear_'.$strFieldName.'" class="clear"></div>';
  }
  
  /**
   * getContactOutput
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getGroupOutput($rowset, $strFieldName){
    $this->core->logger->debug('cms->views->helpers->PageHelper->getGroupOutput()');
  
    $strOutput = '';
    foreach ($rowset as $row){
      $strOutput .= '<div class="contactitem" fileid="'.$row->id.'" id="'.$strFieldName.'_contactitem_'.$row->id.'">
                           <div class="olcontactleft"></div>
                           <div class="itemremovelist" id="'.$strFieldName.'_remove_'.$row->id.'" onclick="myForm.removeItem(\''.$strFieldName.'\', \''.$strFieldName.'_contactitem_'.$row->id.'\', '.$row->id.'); return false;"></div>
        									 <div class="olcontactitemtitle">'.htmlentities($row->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</div>
                           <div class="clear"></div>
                         </div>';
    }
  
    /**
     * add the scriptaculous sortable funcionality
     */
    $strOutput .= '<script type="text/javascript" language="javascript">/* <![CDATA[ */
         myForm.initSortable(\''.$strFieldName.'\', \'divContactContainer_'.$strFieldName.'\', \'contactitem\', \'div\', \'itemid\', \'vertical\');
         /* ]]> */</script>';
     
    return $strOutput.'<div id="divClear_'.$strFieldName.'" class="clear"></div>';
  }

  /**
   * getDashboardListOutput 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getDashboardListOutput($objRowset){
    $this->core->logger->debug('cms->views->helpers->PageHelper->getDashboardListOutput()');
    
//    echo '<pre>';
//    print_r($objRowset);
//    exit();
    
    $strOutput = '';
    foreach ($objRowset as $objRow){
      $strPageTitle = $objRow->pageTitle;  		
  		if($strPageTitle == ''){
  		  $strPageTitle = $objRow->pageGuiTitle;
      }      
      
    	$strOutput .= '
                      <tr class="listrow" id="Row'.$objRow->idPage.'">
                        <!--<td class="rowcheckbox" colspan="2"><input type="checkbox" class="listSelectRow" value="'.$objRow->idPage.'" name="listSelect'.$objRow->idPage.'" id="listSelect'.$objRow->idPage.'"/></td>-->
                        <td colspan="2" style="padding-left:22px;" class="rowtitle"><a href="#" onclick="myNavigation.getEdit('.$objRow->idPage.(($objRow->idParentTypes == $this->core->sysConfig->parent_types->folder) ? ', '.$objRow->idParent : '').'); return false;">'.htmlentities($strPageTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a></td>
                        <td class="rowauthor">'.$objRow->changeUser.'</td>
                        <td class="rowchanged">'.(($objRow->changed != '') ? date('d.m.y, H:i', strtotime($objRow->changed)) : '&nbsp;').'</td>
                        <td class="rowcreated" colspan="2">'.(($objRow->created != '') ? date('d.m.y, H:i', strtotime($objRow->created)) : '&nbsp;').'</td>
                      </tr>';	
    }    
    return $strOutput;
    
  }
  
  /**
   * getFormEntriesList
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   */
  public function getFormEntriesList($objRowset){
    $strOutput = '';
    
    $strOutput .= '<thead>
    		<tr>
    		<th class="topcornerleft"><div>&nbsp;</div></th>';
    
    $objFirstRow = current($objRowset->getCurrentItems());
    $objEntry = json_decode($objFirstRow['content'], true);
    $arrKeys = array_keys($objEntry);
    $i = 0;
    foreach($arrKeys as $strValue){
      if($i < self::NUMBER_OF_ENTRIES){
        $strOutput .= '<th class="top"><div>'.$strValue.'</div></th>';
        $i++;
      }else{
        break;
      }
    }
    
    $strOutput .= '
    		<th class="top"><div>'.$this->core->translate->_('created').'</div></th>
    		<th class="topcornerright"><div>&nbsp;</div></th>
    	</tr>
    </thead>';
       
    foreach($objRowset as $objRow){
      $objEntry = json_decode($objRow['content'], true);
      
      $strOutput .= '<tr class="listrow">
      	<td style="width:11px;"></td>';
      $i = 0;
      foreach($objEntry as $value){
        if($i < self::NUMBER_OF_ENTRIES){
          $strOutput .= '<td class="row">'.$value.'</td>';
          $i++;
        }else{
          break;
        }
      }
      $strOutput .= '
      	<td>'.$objRow['created'].'</td>
      	<td></td>
      </tr>';
    }
    
    
    return $strOutput;
  }
}

?>