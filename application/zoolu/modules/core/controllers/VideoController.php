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
 * @package    application.zoolu.modules.core.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * VideoController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-14: Thomas Schedler
 * 1.1, 2009-07-30: Florian Mathis, Youtube Service
 * 1.2, 2009-10-23: Dominik Mößlang, bugfixes & fine-tuning
 *
 * @author Thomas Schedler <ths@massiveart.com>
 * @version 1.0
 */

class Core_VideoController extends AuthControllerAction {

  /**
   * indexAction
   * @author Thomas Schedler <ths@massiveart.com>
   * @version 1.0
   */
  public function indexAction(){

  }

  /**
   * getvideoselectAction
   * @author Thomas Schedler <ths@massiveart.com>
   * @version 1.0
   */
  public function getvideoselectAction(){
    $this->core->logger->debug('core->controllers->VideoController->getvideoselectAction()');

    try{
      $arrVideos = array();
      $objRequest = $this->getRequest();
      $intChannelId = $objRequest->getParam('channelId');
      $strChannelUserId = $objRequest->getParam('channelUserId');
      $strElementId = $objRequest->getParam('elementId');
      $strValue = $objRequest->getParam('value');
      $strSearchQuery = $objRequest->getParam('searchString');
   
      switch($intChannelId){
      	/*
      	 * Vimeo Controller
      	 */
        case $this->core->sysConfig->video_channels->vimeo->id :
          /**
           * Requires simplevimeo base class
           */
          require_once(GLOBAL_ROOT_PATH.'library/vimeo/vimeo.class.php');

          $arrChannelUser = $this->core->sysConfig->video_channels->vimeo->users->user->toArray();
          $intVideoTypeId = 1;
          $arrVideos = array();
          /**
          * Get the vimeo video list
          */
          if($strChannelUserId !== '' && $strChannelUserId !== 'publicAccess' && $strSearchQuery == ''){
            if(is_array($arrChannelUser)){
              foreach($arrChannelUser AS $chUser){
                if($chUser['id'] == $strChannelUserId){
                 $objResponse = VimeoVideosRequest::getList($strChannelUserId);
                }
              }
            }
            $arrVideos = $objResponse->getVideos(); 
          }else if($strChannelUserId !== '' && isset($strSearchQuery)){  
            if($strChannelUserId == 'publicAccess'){
              $objResponse = VimeoVideosRequest::search($strSearchQuery);  
            }else{              
              $objResponse = VimeoVideosRequest::search($strSearchQuery, $strChannelUserId);   
            }
            $arrVideos = $objResponse->getVideos(); 
          }   
          // Set channel Users 
          $this->view->channelUsers = (array_key_exists('id', $arrChannelUser)) ? array(0 => $arrChannelUser) : $this->core->sysConfig->video_channels->vimeo->users->user->toArray();
         break;
      	
      	/**
      	 * Youtube Controller
      	 */
      	case $this->core->sysConfig->video_channels->youtube->id :
        	$arrChannelUser = $this->core->sysConfig->video_channels->youtube->users->user->toArray();
          $intVideoTypeId = 2;
                                  	
          $objResponse = new Zend_Gdata_YouTube();
  				$objResponse->setMajorProtocolVersion(2);
  				  
  				if($strChannelUserId !== '' && $strSearchQuery == '' && $strChannelUserId !== 'publicAccess'){
    				$arrVideos = $objResponse->getuserUploads($strChannelUserId);
    			}else if(isset($strChannelUserId) && isset($strSearchQuery)){
    			  if($strChannelUserId !== 'publicAccess'){
    				  $arrVideos = $objResponse->getVideoFeed('http://gdata.youtube.com/feeds/api/users/'.$strChannelUserId.'/uploads?q='.urlencode($strSearchQuery));
    				}else{
              $objQuery = $objResponse->newVideoQuery();
              $objQuery->setOrderBy('viewCount');
              $objQuery->setSafeSearch('none');
              $objQuery->setVideoQuery($strSearchQuery);
              $arrVideos = $objResponse->getVideoFeed($objQuery->getQueryUrl(2));
    				}
  				}
				 // Set Channel Users
				 $this->view->channelUsers = (array_key_exists('id', $arrChannelUser)) ? array(0 => $arrChannelUser) : $this->core->sysConfig->video_channels->youtube->users->user->toArray();
 				 break;    
      }

      $this->view->videoTypeId = $intVideoTypeId;
      $this->view->elements = $arrVideos;
      $this->view->channelUserId = $strChannelUserId;
      $this->view->value = $strValue;
      $this->view->elementId = $strElementId;
      $this->view->SearchQuery = $strSearchQuery;

    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }
  
  /**
   * getselectedvideoAction
   * @author Dominik Mößlang <dmo@massiveart.com>
   * @version 1.0
   */
  public function getselectedvideoAction(){
    $this->core->logger->debug('core->controllers->VideoController->getselectedvideoAction()');

    $strVideoTypeName = '';
    $intVideoTypeId = '';
    $objSelectedVideo = '';
    
    try{
      $objRequest = $this->getRequest();
      $intChannelId = $objRequest->getParam('channelId');
      $strElementId = $objRequest->getParam('elementId');
      $strValue = $objRequest->getParam('value');
      $strChannelUserId = $objRequest->getParam('channelUserId');
      $arrSelectedVideo = array();
      
      switch($intChannelId){
        /**
        * Vimeo Controller
        */
        case $this->core->sysConfig->video_channels->vimeo->id :
          require_once(GLOBAL_ROOT_PATH.'library/vimeo/vimeo.class.php');
          $intVideoTypeId = 1;
          $strVideoTypeName = "Vimeo";
          /**
           * Get the selected Video
           */
          if(isset($strValue)){
            $objResponse = VimeoVideosRequest::getInfo($strValue);
            $objSelectedVideo = $objResponse->getVideo(); 
          }
         break;
         /**
         * Youtube Controller
         */
        case $this->core->sysConfig->video_channels->youtube->id :
          $intVideoTypeId = 2;
          $strVideoTypeName = "YouTube";
                                  
          $objResponse = new Zend_Gdata_YouTube();
          $objResponse->setMajorProtocolVersion(2);          
           /**
           * Get the selected Video
           */
          if(isset($strValue)){
            $objSelectedVideo = $objResponse->getVideoEntry($strValue); 
          }
              
         break;
        
      }
      $this->view->strVideoTypeName = $strVideoTypeName;
      $this->view->intVideoTypeId = $intVideoTypeId;
      $this->view->objSelectedVideo = $objSelectedVideo;
      $this->view->strValue = $strValue;
      $this->view->strElementId = $strElementId;
      $this->view->strChannelUserId = $strChannelUserId;
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
      exit();
    }
  }
  
}

?>
