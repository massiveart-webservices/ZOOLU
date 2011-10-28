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
 * @package    library.massiveart.utilities
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * HtmlOutput Class - static function container
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-17: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.utilities
 * @subpackage HtmlOutput
 */

class HtmlOutput {

  /**
   * getOptionsOfSQL
	 * returns the result of a SQL-Statement in the valid output form
	 * <option value=[VALUE] >[DISPLAY]</option>
	 *
	 * Version history (please keep backward compatible):
   * 1.0, 2008-11-17: Cornelius Hansjakob
   *
   * @param Core $core
	 * @param string $strSQL SQL statment
	 * @param string $strSelectedValue
	 * @return string $strHtmlOutput
	 * @return array $arrSecurityCheck
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
	 */
	public static function getOptionsOfSQL(Core &$core, $strSQL, $strSelectedValue = '', $arrSecurityCheck = array()){
    $core->logger->debug('massiveart->utilities->HtmlOutput->getOptionsOfSQL: '.$strSQL);
    $strHtmlOutput = '';

    try {

    	foreach($core->dbh->query($strSQL)->fetchAll() as $arrSQLRow) {
    	  if(count($arrSecurityCheck) == 0 || Security::get()->isAllowed(sprintf($arrSecurityCheck['ResourceKey'], $arrSQLRow['VALUE']), $arrSecurityCheck['Privilege'], $arrSecurityCheck['CheckForAllLanguages'], $arrSecurityCheck['IfResourceNotExists'])){
      	  if($arrSQLRow['VALUE'] == $strSelectedValue){
            $strSelected = ' selected';
          }else{
            $strSelected = '';
          }
          $strHtmlOutput .= '<option value="'.$arrSQLRow['VALUE'].'"'.$strSelected.'>'.htmlentities($arrSQLRow['DISPLAY'], ENT_COMPAT, $core->sysConfig->encoding->default).'</option>'.chr(13);    	    
        }
      }

    } catch (Exception $exc) {
      $core->logger->err($exc);
    }

    return $strHtmlOutput;
	}	
  
  /**
   * getFormattedByteSize
   * @param integer $size
   * @param boolean $blnOnlySize
   * @return string
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public static function getFormattedByteSize($size, $blnOnlySize = false){
    $sizes = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i=0; $size >= 1024 && $i < 5; $i++) {
      $size /= 1024;
    }
    return round($size, 2).((!$blnOnlySize) ? ' '.$sizes[$i] : '');
  }

  /**
   * getIconByExtension
   * @param string $strExtension
   * @param string $strIconBase
   * @param string $strIconExtension
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public static function getIconByExtension($strExtension, $strIconBase = '', $strIconExtension = 'png'){
    $strIcon = '';
    switch(strtolower($strExtension)){
      case 'docx' :
      case 'doc' :
      case 'rtf' :
        if($strIconBase != ''){
          $strIcon = $strIconBase.'/icon_doc.'.$strIconExtension;
        }else{
          $strIcon = '/zoolu-statics/images/icons/docs/icon_doc.png';
        }
        break;
      case 'xlsx' :
      case 'xls' :
        if($strIconBase != ''){
          $strIcon = $strIconBase.'/icon_excel.'.$strIconExtension;
        }else{
          $strIcon = '/zoolu-statics/images/icons/docs/icon_excel.png';
        }
        break;
      case 'pdf' :
        if($strIconBase != ''){
          $strIcon = $strIconBase.'/icon_pdf.'.$strIconExtension;
        }else{
          $strIcon = '/zoolu-statics/images/icons/docs/icon_pdf.png';
        }
        break;
      case 'ppt' :
      case 'pps' :
      case 'pptx' :
      case 'ppsx' :
      case 'ppz' :
      case 'pot' :
        if($strIconBase != ''){
          $strIcon = $strIconBase.'/icon_ppt.'.$strIconExtension;
        }else{
          $strIcon = '/zoolu-statics/images/icons/docs/icon_ppt.png';
        }
        break;
      case 'zip' :
      case 'rar' :
      case 'tar' :
      case 'ace' :
        if($strIconBase != ''){
          $strIcon = $strIconBase.'/icon_compressed.'.$strIconExtension;
        }else{
          $strIcon = '/zoolu-statics/images/icons/docs/icon_compressed.png';
        }
        break;
      case 'avi' :
      case 'mov' :
      case 'swf' :
      case 'mpg' :
      case 'mpeg' :
      case 'wmv' :
      case 'f4v' :
        if($strIconBase != ''){
          $strIcon = $strIconBase.'/icon_movie.'.$strIconExtension;
        }else{
          $strIcon = '/zoolu-statics/images/icons/docs/icon_movie.png';
        }
        break;
      case 'mp3' :
      case 'wav' :
      case 'f4a' :
      case 'wma' :
      case 'aif' :
        if($strIconBase != ''){
          $strIcon = $strIconBase.'/icon_audio.'.$strIconExtension;
        }else{
          $strIcon = '/zoolu-statics/images/icons/docs/icon_audio.png';
        }
        break;
      case 'gif' :
      case 'jpg' :
      case 'jpeg' :
      case 'png' :
      case 'bmp' :
      case 'tif' :
      case 'tiff' :
      case 'eps' :
      case 'psd' :
      case 'ai' :
        if($strIconBase != ''){
          $strIcon = $strIconBase.'/icon_img.'.$strIconExtension;
        }else{
          $strIcon = '/zoolu-statics/images/icons/docs/icon_img.png';
        }
        break;
      default :
        if($strIconBase != ''){
          $strIcon = $strIconBase.'/icon_unknown.'.$strIconExtension;
        }else{
          $strIcon = '/zoolu-statics/images/icons/docs/icon_unknown.png';
        }
        break;
    }
    return $strIcon;
  }
}

?>