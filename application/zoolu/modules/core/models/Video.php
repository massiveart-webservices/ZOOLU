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
 * Video
 *
 * Version history (please keep backward compatible):
 * 1.0, 2015-05-05: Dominik Matt
 *
 * @author Dominik MAtt <dma@massiveart.com>
 * @version 1.0
 */
class Video {
    protected $thumbnails = array();
    protected $videoId = null;
    protected $title = null;
    protected $publishedAt = null;

    /**
     * @method setThumbnail
     * @param $thumbnail
     */
    public function setThumbnail($thumbnail) {
        $this->thumbnails = $thumbnail;
    }

    /**
     * @method getThumbnail
     * @param $size
     *
     * @return mixed
     */
    public function getThumbnail($size) {
        if(!isset($size)) {
            $size = 'default';
        }

        return $this->thumbnails->$size->url;
    }

    /**
     * @method getVideoThumbnails
     * @return array
     */
    public function getVideoThumbnails() {
        return $this->thumbnails;
    }

    /**
     * @method setVideoId
     * @param $id
     */
    public function setVideoId($id) {
        $this->videoId = $id;
    }

    /**
     * @method getVideoId
     * @return null
     */
    public function getVideoId() {
        return $this->videoId;
    }

    /**
     * @method setTitle
     * @param $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @method getTitle
     * @return null
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @method setVideoRecorded
     * @param $recorded
     */
    public function setVideoRecorded($recorded) {
        $this->publishedAt = $recorded;
    }

    /**
     * @method getVideoRecorded
     * @return null
     */
    public function getVideoRecorded() {
        return $this->publishedAt;
    }
}