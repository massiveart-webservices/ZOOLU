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
 * FolderHelper
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-23: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class FolderHelper {

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
   * getFolderTree
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getFolderTree($objRowset, $intFolderId, $strActionKey) {
    $this->core->logger->debug('core->views->helpers->FolderHelper->getFolderTree()');    
    
    $strOutput = '';

    if(count($objRowset) > 0){
      
      $blnShowRootFolder = true;
      $strJsRootAction = '';
      switch($strActionKey){
        case 'MOVE_MEDIA' :
          $strJsRootAction = 'return false;';
          $blnShowRootFolder = false;
          break;
        case 'MOVE_PAGE' :
          $strJsRootAction = 'myPage.selectParentRootFolder('.$objRowset[0]->idRootLevels.'); return false;';
          $blnShowRootFolder = true;
          break;
        case 'MOVE_GLOBAL' :
          $strJsRootAction = 'myGlobal.selectParentRootFolder('.$objRowset[0]->idRootLevels.'); return false;';
          $blnShowRootFolder = true;
          break;
        default :
          $strJsRootAction = 'myFolder.selectParentRootFolder('.$objRowset[0]->idRootLevels.'); return false;';
          $blnShowRootFolder = true;
          break;
      }
      
      $blnFolderChilds = false;
      $intMainFolderDepth = 0;
      if($blnShowRootFolder){
        $strOutput .= '<div id="olnavitem'.$objRowset[0]->idRootLevels.'" class="olnavrootitem">
                         <div style="position:relative;">
                           <a href="#" onclick="'.$strJsRootAction.'"><div class="icon img_folder_on"></div>'.htmlentities($objRowset[0]->rootLevelTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a>
                         </div>
                       </div>';
      }

      foreach ($objRowset as $objRow){
        if($objRow->id == $intFolderId){
          $intMainFolderDepth = $objRow->depth;

          switch($strActionKey){
            case 'MOVE_MEDIA' :
              $intFolderDepth = $objRow->depth + 1;
              $blnFolderChilds = false; 
              $strOutput .= '<div id="olnavitem'.$objRow->id.'" class="olnavrootitem">
                               <div style="position:relative; padding-left:'.(20*$intFolderDepth).'px">
                                 <div class="icon img_folder_'.(($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off').'"></div><span style="background-color:#FFD300;">'.htmlentities($objRow->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</span>
                               </div>
                             </div>';
              break;
            case 'MOVE_PAGE' :
              $intFolderDepth = $objRow->depth + 1;
              $blnFolderChilds = false; 
              $strOutput .= '<div id="olnavitem'.$objRow->id.'" class="olnavrootitem">
                               <div style="position:relative; padding-left:'.(20*$intFolderDepth).'px">
                                 <div class="icon img_folder_'.(($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off').'"></div><span style="background-color:#FFD300;">'.htmlentities($objRow->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</span>
                               </div>
                             </div>';
              break;
            case 'MOVE_GLOBAL' :
              $intFolderDepth = $objRow->depth + 1;
              $blnFolderChilds = false; 
              $strOutput .= '<div id="olnavitem'.$objRow->id.'" class="olnavrootitem">
                               <div style="position:relative; padding-left:'.(20*$intFolderDepth).'px">
                                 <div class="icon img_folder_'.(($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off').'"></div><span style="background-color:#FFD300;">'.htmlentities($objRow->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</span>
                               </div>
                             </div>';
              break;
            default :
              $blnFolderChilds = true;
              break;
          }          
        }else if($blnFolderChilds == false || $objRow->depth <= $intMainFolderDepth){
          
          $strJsAction = '';
          switch($strActionKey){
            case 'MOVE_MEDIA' :
              $strJsAction = 'myMedia.selectParentFolder('.$objRow->id.'); return false;';
              break;
            case 'MOVE_PAGE' :
              $strJsAction = 'myPage.selectParentFolder('.$objRow->id.'); return false;';
              break;
            case 'MOVE_GLOBAL' :
              $strJsAction = 'myGlobal.selectParentFolder('.$objRow->id.'); return false;';
              break;
            default :
              $strJsAction = 'myFolder.selectParentFolder('.$objRow->id.'); return false;';
              break;
          }
          
          $blnFolderChilds = false;
          $intFolderDepth = $objRow->depth + 1;
          $strOutput .= '<div id="olnavitem'.$objRow->id.'" class="olnavrootitem">
                           <div style="position:relative; padding-left:'.(20*$intFolderDepth).'px">
                             <a href="#" onclick="'.$strJsAction.'"><div class="icon img_folder_'.(($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off').'"></div>'.htmlentities($objRow->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a>
                           </div>
                         </div>';
        }
      }
    }

    /**
     * return html output
     */
    return $strOutput;
  }

  /**
   * getFolderContentList
   * @param object $objRowset
   * @param integer $intSelectedFolderId
   * @param string $strSelectedFolderIds
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getFolderCheckboxTree($objRowset, $intSelectedFolderId, $strSelectedFolderIds){
    $this->core->logger->debug('core->views->helpers->FolderHelper->getFolderTree()');

    $strOutput = '';
    
    if(count($objRowset) > 0){

      $strRootLevelChecked = ($objRowset[0]->idRootLevels == $intSelectedFolderId) ? ' checked="checked"' : '';
      $strOutput .= '<div id="olnavitem'.$objRowset[0]->idRootLevels.'" class="olnavrootitem">
                       <div style="position:relative;">
                         <label style="white-space: nowrap;"><input type="checkbox"'.$strRootLevelChecked.' class="multiCheckbox" value="'.$objRowset[0]->idRootLevels.'" id="rootLevelFolderCheckboxTree" name="rootLevelFolderCheckboxTree"/><span id="rootLevelFolderCheckboxTreeTitle">'.htmlentities($objRowset[0]->rootLevelTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</span></lable>
                       </div>
                     </div>';

      foreach ($objRowset as $objRow){
        $intFolderDepth = $objRow->depth + 1;
        $strFolderChecked = (strpos($strSelectedFolderIds, '['.$objRow->id.']') !== false) ? ' checked="checked"' : '';
        $strOutput .= '<div id="olnavitem'.$objRow->id.'" class="olnavrootitem">
                         <div style="position:relative; padding-left:'.(20*$intFolderDepth).'px">
                           <label style="white-space: nowrap;"><input type="checkbox"'.$strFolderChecked.' class="multiCheckbox" value="'.$objRow->id.'" id="folderCheckboxTree-'.$objRow->id.'" name="folderCheckboxTree[]"/><span id="folderCheckboxTreeTitle-'.$objRow->id.'">'.htmlentities($objRow->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</span></lable>
                         </div>
                       </div>';
      }
    }

    /**
     * return html output
     */
    return $strOutput;
  }
  
  /**
   * getFolderContentList
   * @param object $objRowset
   * @param integer $intFolderId
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getFolderContentList($objPaginator, $intFolderId, $currLevel, $strOrderColumn = '', $strOrderSort = ''){
    $this->core->logger->debug('core->views->helpers->FolderHelper->getFolderContentList('.$currLevel.')');

    $strTbody = '';
    $strThead = '';

    /**
     * Tbody
     */
    $strTbody .= '<tbody>';
    
    if(count($objPaginator) > 0){
      foreach($objPaginator as $objRow){
      	$strStatus = ($objRow->idStatus == $this->core->sysConfig->status->live) ? 'on' : 'off' ;
      	$strPageTitle = $objRow->title;
      	
      	if($strPageTitle == '') {
      		$strPageTitle = $objRow->guiTitle;
      	}
      	
      	if($objRow->idTemplates == NULL) {
      		$objRow->idTemplates = '1';
      	}
        
        if($objRow->genericFormId == NULL && $objRow->elementType == 'folder') {
          $objRow->genericFormId = $this->core->sysConfig->form->ids->folders->default;
        }
      	
      	if($objRow->isStartPage == '-1')
      	{
      		$strTbody .= '
                        <tr class="listrow" id="Row'.$objRow->id.'">
                          <td class="rowcheckbox" colspan="2"><input type="checkbox" class="listSelectRow" value="'.$objRow->id.'" name="listSelect'.$objRow->id.'" id="listSelect'.$objRow->id.'"/></td>
                          <td class="rowicon"><div class="img_folder_'.$strStatus.'"></div></td>
                          <td class="rowsortpos"><input class="iptsortpos" name="listPos_'.$objRow->elementType.'_'.$objRow->id.'" id="listPos_'.$objRow->elementType.'_'.$objRow->id.'" onkeyup="if(event.keyCode==13){ myNavigation.updateSortPosition(\'listPos_'.$objRow->elementType.'_'.$objRow->id.'\',\''.$objRow->elementType.'\','.$currLevel.'); myNavigation.toggleSortPosBox(\'listPos_'.$objRow->elementType.'_'.$objRow->id.'\'); return false; }" type="text" value="'.$objRow->sortPosition.'" /></td>
                          <td class="rowtitle">
                            <a onclick="myNavigation.getEditForm('.$objRow->id.', \''.$objRow->elementType.'\', \''.$objRow->genericFormId.'\', '.$objRow->version.', '.$objRow->idTemplates.', null, true); return false;" href="#">'.htmlentities($strPageTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a>
                          </td>
                          <td class="rowauthor">'.$objRow->author.'</td>
                          <td class="rowchanged" colspan="2">'.$objRow->changed.'</td>
                        </tr>';
      	} elseif($objRow->isStartPage){
	          $strTbody .= '
	                      <tr class="listrow" id="Row'.$objRow->id.'">
	                        <td class="rowcheckbox" colspan="2"><input type="checkbox" class="listSelectRow" value="'.$objRow->id.'" name="listSelect'.$objRow->id.'" id="listSelect'.$objRow->id.'"/></td>
	                        <td class="rowicon"><div class="img_startpage_'.$strStatus.'"></div></td>
	                        <td class="rowsortpos"></td>
                          <td class="rowtitle">
                            <a onclick="myNavigation.getEditForm('.$objRow->id.', \''.$objRow->elementType.'\', \''.$objRow->genericFormId.'\', '.$objRow->version.', '.$objRow->idTemplates.', null, true); return false;" href="#">'.htmlentities($strPageTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a>
                          </td>
	                        <td class="rowauthor">'.$objRow->author.'</td>
	                        <td class="rowchanged" colspan="2">'.$objRow->changed.'</td>
	                      </tr>';
	      }else{
	        $strTbody .= '
	                    <tr class="listrow" id="Row'.$objRow->id.'">
	                      <td class="rowcheckbox" colspan="2"><input type="checkbox" class="listSelectRow" value="'.$objRow->id.'" name="listSelect'.$objRow->id.'" id="listSelect'.$objRow->id.'"/></td>
	                      <td class="rowicon"><div class="img_page_'.$strStatus.'"></div></td>
	                      <td class="rowsortpos"><input class="iptsortpos" name="listPos_'.$objRow->elementType.'_'.$objRow->id.'" id="listPos_'.$objRow->elementType.'_'.$objRow->id.'" onkeyup="if(event.keyCode==13){ myNavigation.updateSortPosition(\'listPos_'.$objRow->elementType.'_'.$objRow->id.'\',\''.$objRow->elementType.'\','.$currLevel.'); myNavigation.toggleSortPosBox(\'listPos_'.$objRow->elementType.'_'.$objRow->id.'\'); return false; }" type="text" value="'.$objRow->sortPosition.'" /></td>
                        <td class="rowtitle">
                          <a onclick="myNavigation.getEditForm('.$objRow->id.', \''.$objRow->elementType.'\', \''.$objRow->genericFormId.'\', '.$objRow->version.', '.$objRow->idTemplates.', null, true); return false;" href="#">'.htmlentities($strPageTitle, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a>
                        </td>
	                      <td class="rowauthor">'.$objRow->author.'</td>
	                      <td class="rowchanged" colspan="2">'.$objRow->changed.'</td>
	                    </tr>';
      	}

      }
    }
    $strTbody .= '</tbody>';

    /**
     * Thead
     */
    $strThead .= '<thead>
                     <tr>
                       <th class="topcornerleft"></th>
                       <th class="topcheckbox"></th>
                       <th class="topicon"></th>
                       <th class="topsortposition'.(('sortposition' == $strOrderColumn) ? ' sort' : '').'" onclick="myList.sort(\'sortposition\''.(('sortposition' == $strOrderColumn && $strOrderSort == 'asc') ? ', \'desc\'' : ', \'asc\'').')">
                         <div'.(('sortposition' == $strOrderColumn) ? ' class="'.$strOrderSort.'"' : '').' style="height:100%"></div>
                       </th>
                       <th class="toptitle'.(('title' == $strOrderColumn) ? ' sort' : '').'" onclick="myList.sort(\'title\''.(('title' == $strOrderColumn && $strOrderSort == 'asc') ? ', \'desc\'' : ', \'asc\'').')">
                         <div'.(('title' == $strOrderColumn) ? ' class="'.$strOrderSort.'"' : '').'>'.$this->core->translate->_('title').'</div>
                      </th>
                       <th class="topauthor'.(('author' == $strOrderColumn) ? ' sort' : '').'" onclick="myList.sort(\'author\''.(('author' == $strOrderColumn && $strOrderSort == 'asc') ? ', \'desc\'' : ', \'asc\'').')">
                         <div'.(('author' == $strOrderColumn) ? ' class="'.$strOrderSort.'"' : '').'>'.$this->core->translate->_('Author').'</div>
                       </th>
                       <th class="topchanged'.(('changed' == $strOrderColumn) ? ' sort' : '').'" onclick="myList.sort(\'changed\''.(('changed' == $strOrderColumn && $strOrderSort == 'asc') ? ', \'desc\'' : ', \'asc\'').')">
                         <div'.(('changed' == $strOrderColumn) ? ' class="'.$strOrderSort.'"' : '').'>'.$this->core->translate->_('changed').'</div>
                       </th>
                       <th class="topcornerright"></th>
                     </tr>
                   </thead>';

    /**
     * return html output
     */
    $strOutput = $strThead.$strTbody;
    return $strOutput;
  }

  /**
   * getListTitle
   * @param string $strSearchValue
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function getFolderContentListTitle($objPaginator, $strSearchValue = '') {
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
   * getFolderSecurity
   * @param Zend_Db_Table_Rowset_Abstract $objRowset
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getFolderSecurity($objRowset){
    $this->core->logger->debug('core->views->helpers->FolderHelper->getFolderSecurity()');

    $strOutput = '';

    $arrZooluSecurity = array();
    $arrWebsiteSecurity = array();
    foreach($objRowset as $objRow){
      if($this->core->sysConfig->zone->zoolu == $objRow->zone){
        $arrZooluSecurity[] = $objRow->id;
      }else if($this->core->sysConfig->zone->website == $objRow->zone){
        $arrWebsiteSecurity[] = $objRow->id;
      }
    }

    $arrGroups = array();
    $sqlStmt = $this->core->dbh->query("SELECT `id`, `title` FROM `groups` ORDER BY `title`")->fetchAll();
    foreach($sqlStmt as $arrSql){
      $arrGroups[$arrSql['id']] = $arrSql['title'];
    }

    $objZooluSecurityElement = new Zend_Form_Element_MultiCheckbox('ZooluSecurity', array(
        'value' => $arrZooluSecurity,
        'label' => $this->core->translate->_('groups', false),
        'multiOptions' => $arrGroups,
        'columns' => 12,
        'class' => 'multiCheckbox'
      ));
    $objZooluSecurityElement->addPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH.'library/massiveart/generic/forms/decorators/', 'decorator');
    $objZooluSecurityElement->setDecorators(array('Input'));

    $objWebsiteSecurityElement = new Zend_Form_Element_MultiCheckbox('WebsiteSecurity', array(
        'value' => $arrWebsiteSecurity,
        'label' => $this->core->translate->_('groups', false),
        'multiOptions' => $arrGroups,
        'columns' => 12,
        'class' => 'multiCheckbox'
      ));

    $objWebsiteSecurityElement->addPrefixPath('Form_Decorator', GLOBAL_ROOT_PATH.'library/massiveart/generic/forms/decorators/', 'decorator');
    $objWebsiteSecurityElement->setDecorators(array('Input'));

    $strOutput .= '
    <div id="divTab_ZOOLU">
      '.$objZooluSecurityElement->render().'
    </div>
    <div id="divTab_Website" style="display:none;">
      '.$objWebsiteSecurityElement->render().'
    </div>';


    /**
     * return html output
     */
    return $strOutput;
  }
}

?>