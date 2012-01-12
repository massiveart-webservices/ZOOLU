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
 * @package    application.website.default.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * EventHelper
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-04-20: Cornelius Hansjakob
 * 
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class EventHelper {
  
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
   * getList 
   * @param object $objRowset
   * @return string $strOutput
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getList($arrEvents, $quarter, $year, $strImageFolder = '80x80'){
    $this->core->logger->debug('website->views->helpers->EventHelper->getList()');
    
    $arrDaysShort = array('So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa');
    
    $strOutput = '';
    if(count($arrEvents) > 0){
    	foreach($arrEvents as $key => $objPageContainer){
        if(count($objPageContainer) > 0){
        
	        $arrEventEntries = $objPageContainer->getEntries();
	        
	        foreach($arrEventEntries as $objEventEntry){
	          $datetime = strtotime($objEventEntry->datetime);
	        	
		        $strDescription = '';
	          if($objEventEntry->shortdescription != ''){
	            $strDescription = htmlentities($objEventEntry->shortdescription, ENT_COMPAT, $this->core->sysConfig->encoding->default); 
	          }else if($objEventEntry->description != ''){
	            if(strlen($objEventEntry->description) > 120){
	              $strDescription = strip_tags(substr($objEventEntry->description, 0, strpos($objEventEntry->description, ' ', 120))).' ...'; 
	            }else{
	              $strDescription = strip_tags($objEventEntry->description); 
	            }   
	          }
	          
		        $strEventStatus = '';
	          if($objEventEntry->event_status == $this->core->config->eventstatus->full->id){
	            $strEventStatus = '
	                    <div class="divEventCalItemShortInfo smaller">Leider keine Pl&auml;tze mehr verf&uuml;gbar.</div>
	            ';  
	          }else if($objEventEntry->event_status == $this->core->config->eventstatus->rest->id){
	            $strEventStatus = '
	                    <div class="divEventCalItemShortInfo smaller">Achtung: Nur noch wenige Restpl&auml;tze verf&uuml;gbar.</div>
	                    <a href="'.$objEventEntry->url.'" class="red smaller">Jetzt Anmelden!</a>'; 
	          }else{
	            $strEventStatus = '
	                    <a href="'.$objEventEntry->url.'" class="red smaller">Jetzt Anmelden!</a>'; 
	          }
	          
	          $strOutput .= '
	                <div class="divEventCalItem">
	                  <div class="divEventCalItemLeft">
	                    <div class="divEventCalItemDateBoxTop"></div>
	                    <div class="divEventCalItemDateBoxMiddle">
	                      <div class="divEventCalDate">'.$arrDaysShort[date('w', $datetime)].', '.date('d.m.', $datetime).'</div>
	                      <div class="divEventCalTime">Beginn: '.date('H:i', $datetime).' Uhr</div>
	                    </div>
	                    <div class="divEventCalItemDateBoxBottom"></div>
	                    <div class="clear"></div>
	                  </div>
	                  <div class="divEventCalItemCenter">
	                    <div class="divEventCalItemText">                      
	                      <h2 class="padding0"><a href="'.$objEventEntry->url.'">'.htmlentities($objEventEntry->title, ENT_COMPAT, $this->core->sysConfig->encoding->default).'</a></h2>
	                      '.$strDescription.'
	                      <div><a href="'.$objEventEntry->url.'">Mehr Informationen</a></div>
	                    </div>';
            if($objEventEntry->filename != ''){
                  $strOutput .= '
                      <div class="divEventCalItemImage">
                        <a href="'.$objEventEntry->url.'">
                          <img title="'.$objEventEntry->filetitle.'" alt="'.$objEventEntry->filetitle.'" src="'.$this->core->sysConfig->media->paths->imgbase.$objEventEntry->filepath.$strImageFolder.'/'.$objEventEntry->filename.'?v='.$objEventEntry->fileversion.'"/>
                        </a>
                      </div>';
            }        
            $strOutput .= '
	                    <div class="clear"></div>
	                  </div>
	                  <div class="divEventCalItemRight">
	                    '.$strEventStatus.'
	                    <div class="clear"></div>
	                  </div>
	                  <div class="clear"></div>
	                </div>';
	        } 
	      }
	    }	    	
    }else{
      $strOutput = '<div class="divEventCalListEmpty">In diesem Zeitraum finden keine Veranstaltungen statt.</div>';	
    }
    return $strOutput.$this->getQuarterHeadline($quarter, $year);
  }
  
  /**
   * getQuarterHeadline 
   * @param integer $intQuarter
   * @param integer $intYear
   * @return string $strHtmlOutput
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function getQuarterHeadline($intQuarter = 0, $intYear = 0){
    $timestamp = time();
    $year = ($intYear > 0) ? $intYear : date('Y', $timestamp);
    $quarter = ($intQuarter > 0 && $intQuarter <= 4) ? $intQuarter : ceil(date('m', $timestamp) / 3);
    
    $arrQuarterText = array(1 => 'Jänner '.$year.' bis März '.$year,
                            2 => 'April '.$year.' bis Juni '.$year,
                            3 => 'Juli '.$year.' bis September '.$year,
                            4 => 'Oktober '.$year.' bis Dezember '.$year);
  
    $strHeadline = utf8_encode($arrQuarterText[$quarter]);
    
    $strHtmlOutput = '<div id="divQuarterHeadline_Q'.$quarter.'_'.$year.'" style="display:none;">'.$strHeadline.'</div>';
    
    return $strHtmlOutput;
  }
}