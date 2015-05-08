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
 * @package    application.zoolu.modules.core.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
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

class Core_VideoController extends AuthControllerAction
{

    /**
     * indexAction
     * @author Thomas Schedler <ths@massiveart.com>
     * @version 1.0
     */
    public function indexAction()
    {

    }

    /**
     * getvideoselectAction
     * @author Thomas Schedler <ths@massiveart.com>
     * @version 1.0
     */
    public function getvideoselectAction()
    {
        $this->core->logger->debug('core->controllers->VideoController->getvideoselectAction()');

        try {
            $objRequest = $this->getRequest();
            $intChannelId = $objRequest->getParam('channelId');
            $channelUser = $objRequest->getParam('channelUserId');
            $strElementId = $objRequest->getParam('elementId');
            $strValue = $objRequest->getParam('value');
            $searchQuery = $objRequest->getParam('searchString');

            $key = $this->core->zooConfig->youtube_api_key;
            $channelId = $this->getChannelId($key, $channelUser);

            switch ($intChannelId) {
                case $this->core->sysConfig->video_channels->youtube->id:
                    $videos = array();
                    $arrChannelUser = $this->core->sysConfig->video_channels->youtube->users->user->toArray();
                    $intVideoTypeId = 2;

                    if($channelId !== ''|| $searchQuery !== NULL) {
                        $videos = $this->getChannelVideos($key, $channelId, $searchQuery);
                    }

                    $this->view->channelUsers = (array_key_exists('id', $arrChannelUser)) ? array(0 => $arrChannelUser) : $this->core->sysConfig->video_channels->youtube->users->user->toArray();
            }

            $this->view->videoTypeId = $intVideoTypeId;
            $this->view->elements = $videos;
            $this->view->channelUserId = $channelUser;
            $this->view->value = $strValue;
            $this->view->elementId = $strElementId;
            $this->view->SearchQuery = $searchQuery;

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * @method getChannelId
     * @param $key
     * @param $channelUser
     *
     * @return bool
     */
    protected function getChannelId($key, $channelUser)
    {
        if($channelUser !== 'publicAccess') {
            if (!isset($this->core->sysConfig->video_channels->youtube->unique_id) || $this->core->sysConfig->video_channels->youtube->unique_id === 'false') {
                $url = 'https://www.googleapis.com/youtube/v3/channels?part=snippet&forUsername=' . $channelUser . '&key=' . $key;
                $channel = json_decode($this->connectYouTube($url));

                if (count($channel->items) > 0) {
                    return $channel->items[0]->id;
                }

                $this->core->logger->err('YouTube Channel ID for user "' . $channelUser . '" not found');
                return '';
            }

            return $channelUser;
        }

        return '';
    }

    /**
     * @method getChannelVideos
     * @param $key
     * @param $channelId
     * @param $searchQuery
     *
     * @return array
     */
    protected function getChannelVideos($key, $channelId, $searchQuery)
    {
        try {
            $url = 'https://www.googleapis.com/youtube/v3/search?key=' . $key . '&part=snippet&type=video';

            if(isset($channelId) && $channelId != '' && $channelId !== 'publicAccess') {
                $url .= '&channelId=' . urlencode($channelId);
            }

            if(isset($searchQuery) && $searchQuery != '') {
                $url .= '&q=' . urlencode($searchQuery);
            }

            if($channel = json_decode($this->connectYouTube($url))) {
                $videos = $this->prepareYouTubeVideoData($channel->items);
            }
        } catch( \Exception $e ) {
            $this->core->logger->err($e->getMessage());
            $videos = array();
        }


        return $videos;
    }

    /**
     * @method connectYouTube
     * @param $url
     *
     * @return mixed
     */
    protected function connectYouTube($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['SERVER_NAME']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);

        return $output;
    }

    /**
     * @method prepareYouTubeVideoData
     * @param $elements
     *
     * @return array
     */
    protected function prepareYouTubeVideoData($elements)
    {
        $videos = array();

        require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Video.php';

        if(count($elements) > 0) {
            foreach($elements as $video) {

                $element = new Video();
                $element->setThumbnail($video->snippet->thumbnails);
                if(is_object($video->id)) {
                    $element->setVideoId($video->id->videoId);
                } else {
                    $element->setVideoId($video->id);
                }
                $element->setTitle($video->snippet->title);
                $element->setVideoRecorded($video->snippet->publishedAt);

                $videos[] = $element;
            }
        }

        return $videos;
    }

    /**
     * getselectedvideoAction
     * @author Dominik Mößlang <dmo@massiveart.com>
     * @version 1.0
     */
    public function getselectedvideoAction()
    {
        $this->core->logger->debug('core->controllers->VideoController->getselectedvideoAction()');

        $strVideoTypeName = '';
        $intVideoTypeId = '';
        $objSelectedVideo = '';

        try {
            $objRequest = $this->getRequest();
            $intChannelId = $objRequest->getParam('channelId');
            $strElementId = $objRequest->getParam('elementId');
            $strValue = $objRequest->getParam('value');
            $strChannelUserId = $objRequest->getParam('channelUserId');
            $arrSelectedVideo = array();



            switch ($intChannelId) {
                // Youtube Controller
                case $this->core->sysConfig->video_channels->youtube->id :
                    $intVideoTypeId = 2;
                    $strVideoTypeName = "YouTube";
                    $key = $this->core->zooConfig->youtube_api_key;
                    $url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet&id=' . $strValue . '&key=' . $key;
                    $result = json_decode($this->connectYouTube($url));
                    
                    $videos = $this->prepareYouTubeVideoData($result->items);
                    if(count($videos) > 0) {
                        $selectedVideo = $videos[0];
                    }
                break;
            }

            $this->view->strVideoTypeName = $strVideoTypeName;
            $this->view->intVideoTypeId = $intVideoTypeId;
            $this->view->objSelectedVideo = $selectedVideo;
            $this->view->strValue = $strValue;
            $this->view->strElementId = $strElementId;
            $this->view->strChannelUserId = $strChannelUserId;

        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

}

?>
