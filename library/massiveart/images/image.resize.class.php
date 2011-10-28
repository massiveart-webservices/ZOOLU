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
 * @package    library.massiveart.images
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ImageResizeClass Class
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-04-3: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.images
 * @subpackage ImageResizeClass
 */

class ImageResizeClass {
  
  /**
   * @var Core core object (dbh, logger, ...)
   */
  protected $core;
  
  public $imageName;
  public $resizedImageName;
  public $extension;

  public $newWidth;
  public $newHeight;
  public $src_image;
  public $dest_image2;
  public $dest_image;
  public $imgName;
  public $destPath;
  public $compression;
  public $arrImageSizes = array();
  
  /**
   * Method ImageResizeClass::resizeImage()
   *
   * { Description :-
   * This method resizes the image.
   * }
   */

  public function calculateAllImages() {
    
    foreach ($this->arrImageSizes as $arrImageSize) {
      $imgSize = $arrImageSize["size"];
      $imgFolder = $arrImageSize["folder"];
      $imgMode = $arrImageSize["mode"];
      $watermark = $arrImageSize["watermark"];
      $this->compression = $arrImageSize["compression"];
      
      // debug information
      $this->core->logger->debug("calculateAllImages imgSize:" . $imgSize);
      $this->core->logger->debug("calculateAllImages imgFolder:" . $imgFolder);
      $this->core->logger->debug("calculateAllImages imgMode:" . $imgMode);
      $this->core->logger->debug("calculateAllImages compression:" . $this->compression);
      
      $this->newWidth = $imgSize;
      $this->newHeight = $imgSize;
      if ($imgFolder != '') {       
        $this->resizedImageName = $this->destPath . $imgFolder . "/" . $this->imgName .  "." . $this->extension;
        if (!is_dir($this->destPath . $imgFolder)) {
          mkdir($this->destPath . $imgFolder, 0775, true);
        }
      } else {
        $this->resizedImageName = $this->destPath . $this->imgName . "." . $this->extension;
      }
      //Square, Prop
      if ($imgMode == "SquareCrop") {
        $this->resizeImageSquareCrop($watermark);
        $this->saveImage();
      } else if ($imgMode == "SquareScal") {
        $this->resizeImageSquareScal($watermark);
        $this->saveImage();
      } else if ($imgMode == "PropX"){
        $this->resizeImageX($watermark);
        $this->saveImage();
      } else if ($imgMode == "PropY"){
        $this->resizeImageY($watermark);
        $this->saveImage();
      }else {
        $this->core->logger->warn("calculateAllImages: wrong imagemode for calculation set:" . $imgMode);
      }
    }
  }
  
  protected function doWatermark() {
    if(file_exists(GLOBAL_ROOT_PATH .'/public/zoolu-statics/images/main/watermark.png')){
      $foreground = imagecreatefrompng(GLOBAL_ROOT_PATH .'/public/zoolu-statics/images/main/watermark.png');
      $insertWidth = imagesx($foreground);
      $insertHeight = imagesy($foreground);
  
      $imageWidth = imagesx($this->dest_image);
      $imageHeight = imagesy($this->dest_image);
  
      $overlapX = $imageWidth-$insertWidth-5;
      $overlapY = $imageHeight-$insertHeight-5;
      imagecopy($this->dest_image, $foreground, $overlapX,$overlapY,0,0,$insertWidth,$insertHeight); 
    }
  }

  protected function resizeImageX($watermark){    
    $old_x = imagesx($this->src_image);
    $old_y = imagesy($this->src_image);
    $new_X = $this->newWidth;

    if ($old_x < $this->newWidth) {
      $new_X = $old_x;
    }
    $this->core->logger->debug("resizeImageX Old x:" . $old_x . " Old y:" . $old_y  . " New_X:" . $new_X);

    $xFact = $new_X/$old_x;
    $new_Y = ceil($old_y*$xFact);
    $this->core->logger->debug("resizeImageX ImgName:".$this->imgName." NewWidth:" . $new_X . " NewHeight:" . $new_Y);
    $this->dest_image = imagecreatetruecolor($new_X, $new_Y);
    imagecopyresampled($this->dest_image, $this->src_image, 0, 0, 0, 0, $new_X, $new_Y, $old_x, $old_y);

    //add watermark image
    if ($watermark) $this->doWatermark();

    $this->core->logger->debug("resizeImageX: done");
  }
  
  protected function resizeImageY($watermark){    
    $old_x = imagesx($this->src_image);
    $old_y = imagesy($this->src_image);
    $new_Y = $this->newWidth;

    if ($old_y < $this->newWidth) {
      $new_Y = $old_y;
    }
    $this->core->logger->debug("resizeImageX Old x:" . $old_x . " Old y:" . $old_y  . " New_Y:" . $new_Y);

    $yFact = $new_Y/$old_y;
    $new_X = ceil($old_x*$yFact);
    $this->core->logger->debug("resizeImageX ImgName:".$this->imgName." NewWidth:" . $new_X . " NewHeight:" . $new_Y);
    $this->dest_image = imagecreatetruecolor($new_X, $new_Y);
    imagecopyresampled($this->dest_image, $this->src_image, 0, 0, 0, 0, $new_X, $new_Y, $old_x, $old_y);
    //add watermark image
    if ($watermark) $this->doWatermark();
    $this->core->logger->debug("resizeImageX: done");
  }
  
  protected function resizeImageSquareCrop($watermark){    
    // first get current dimension of picture
    $old_x = imagesx($this->src_image);
    $old_y = imagesy($this->src_image);
    $this->core->logger->debug("resizeImageSquare ImgName:".$this->imgName." Old x:" . $old_x . " old y:" . $old_y . " NewWidth:" . $this->newWidth ." NewHeight:" . $this->newHeight);
    $xFact = $old_x/$this->newWidth;
    $yFact = $old_y/$this->newHeight;
    $this->core->logger->debug("resizeImageSquare Factx:" . $xFact ." Facty:".$yFact);
    // then decide dimension to use for resize image
    // we want to get one dimension like set and the other
    // one at least as big as we want it to be
    $thumb_w = 0;
    $thumb_h = 0;
    if ($xFact < $yFact)
    {
      $thumb_w = $this->newWidth;
      $thumb_h = $old_y*($this->newHeight/$old_x);
    }
    else if($yFact < $xFact)
    {
      $thumb_w = $old_x*($this->newWidth/$old_y);
      $thumb_h = $this->newHeight;
    }

    if($old_x == $old_y)
    {
      $thumb_w = $this->newWidth;
      $thumb_h = $this->newHeight;
    }
    $thumb_w = ceil($thumb_w);
    $thumb_h = ceil($thumb_h);
    $this->core->logger->debug("resizeImageSquare thumb_w: ".$thumb_w." thumb_h:". $thumb_h);
    //echo("width used:".$thumb_w. " height used:".$thumb_h."<br/>");
    // after getting correct maximum dimension without image distortion,
    // create image
    $this->dest_image2 = imagecreatetruecolor($thumb_w, $thumb_h);
    imagecopyresampled($this->dest_image2, $this->src_image, 0, 0, 0, 0, ceil($thumb_w), ceil($thumb_h), $old_x, $old_y);
    //after resize we should cropimage to wanted height and width
    $this->dest_image = imagecreatetruecolor(ceil($this->newWidth), ceil($this->newHeight));

    $offX = ceil(($thumb_w - $this->newWidth) / 2);
    $offY = ceil(($thumb_h - $this->newHeight) / 2);
    imagecopyresampled($this->dest_image, $this->dest_image2, 0, 0, $offX, $offY, $thumb_w, $thumb_h, $thumb_w, $thumb_h);
    //add watermark image
    if ($watermark) $this->doWatermark();
  }
  
  protected function resizeImageSquareScal($watermark){    
    // first get current dimension of picture
    $old_x = imagesx($this->src_image);
    $old_y = imagesy($this->src_image);
    $this->core->logger->debug("resizeImageSquare ImgName:".$this->imgName." Old x:" . $old_x . " old y:" . $old_y . " NewWidth:" . $this->newWidth ." NewHeight:" . $this->newHeight);
    $xFact = $old_x/$this->newWidth;
    $yFact = $old_y/$this->newHeight;
    $this->core->logger->debug("resizeImageSquare Factx:" . $xFact ." Facty:".$yFact);
    // then decide dimension to use for resize image
    // we want to get one dimension like set and the other
    // one at least as big as we want it to be
    $thumb_w = 0;
    $thumb_h = 0;
    if ($xFact > $yFact)
    {
      $thumb_w = $this->newWidth - 4;
      $thumb_h = $old_y*(($this->newHeight - 4)/$old_x);
    }
    else if($yFact > $xFact)
    {
      $thumb_w = $old_x*(($this->newWidth - 4)/$old_y);
      $thumb_h = $this->newHeight - 4;
    }

    if($old_x == $old_y)
    {
      $thumb_w = $this->newWidth;
      $thumb_h = $this->newHeight;
    }
    $thumb_w = ceil($thumb_w);
    $thumb_h = ceil($thumb_h);
    $this->core->logger->debug("resizeImageSquare thumb_w: ".$thumb_w." thumb_h:". $thumb_h);
    $white = imagecolorallocate($this->dest_image, 255, 255, 255);
    
    //echo("width used:".$thumb_w. " height used:".$thumb_h."<br/>");
    // after getting correct maximum dimension without image distortion,
    // create image
    $this->dest_image2 = imagecreatetruecolor($thumb_w, $thumb_h);
    imagefilledrectangle($this->dest_image2,  0, 0, $thumb_w, $thumb_h, $white);
    
    imagecopyresampled($this->dest_image2, $this->src_image, 0, 0, 0, 0, ceil($thumb_w), ceil($thumb_h), $old_x, $old_y);
    //after resize we should cropimage to wanted height and width
    $this->dest_image = imagecreatetruecolor(ceil($this->newWidth), ceil($this->newHeight));
    imagefilledrectangle($this->dest_image,  0, 0, ceil($this->newWidth), ceil($this->newHeight), $white);

    $offX = ceil(($this->newWidth - $thumb_w) / 2);
    $offY = ceil(($this->newHeight - $thumb_h) / 2);
    
    imagecopyresampled($this->dest_image, $this->dest_image2, $offX, $offY, 0, 0, $thumb_w, $thumb_h, $thumb_w, $thumb_h);
    //add watermark image
    if ($watermark) $this->doWatermark();
  }
}

/**
 * ImageResizeJpeg Class extends ImageResizeClass
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-04-3: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.images
 * @subpackage ImageResizeJpeg
 */
class ImageResizeJpeg extends ImageResizeClass{
  /**
   * Method ImageResizeJpeg::ImageResizeJpeg()
   *
   * { Description :-
   * This method is a constructor for the ImageResizeJpeg (Subclass for JPEG image resizing).
   * }
   */

  public function ImageResizeJpeg($imageName, $arrImageSizes){
    $this->core = Zend_Registry::get('Core');
    $this->imageName = $imageName;
    $this->extension = "jpg";
    $this->arrImageSizes = $arrImageSizes;
  }

  /**
   * Method ImageResizeJpeg::creatgeResizedImage()
   *
   * { Description :-
   * This method puts the resized image in the specified destination.
   * }
   */

  public function createResizedImage($destPath, $imgName){    
    $this->src_image = imagecreatefromjpeg($this->imageName);
    $this->destPath = $destPath;
    $this->imgName = $imgName;
    $this->core->logger->debug("createResizedImage Src_Image:".$this->imageName);
    $this->calculateAllImages();
  }

  public function saveImage() {
    $this->core->logger->debug("Save image with compression :" . $this->compression);
    imagejpeg($this->dest_image, $this->resizedImageName,$this->compression);
  }
}

/**
 * ImageResizeGif Class extends ImageResizeClass
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-04-3: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.images
 * @subpackage ImageResizeGif
 */
class ImageResizeGif extends ImageResizeClass{
  /**
   * Method ImageResizeGif::ImageResizeGif()
   *
   * { Description :-
   * This method is a constructor for the ImageResizeGif (Subclass for JPEG image resizing).
   * }
   */

  public function ImageResizeGif($imageName, $arrImageSizes){
    $this->core = Zend_Registry::get('Core');
    $this->imageName = $imageName;
    $this->extension = "gif";
    $this->arrImageSizes = $arrImageSizes;
  }

  /**
   * Method ImageResizeJpeg::createResizedImage()
   *
   * { Description :-
   * This method puts the resized image in the specified destination.
   * }
   */

  public function createResizedImage($destPath, $imgName) {
    $this->src_image = imagecreatefromgif($this->imageName);
    $this->destPath = $destPath;
    $this->imgName = $imgName;
    $this->core->logger->debug("createResizedImage Src_Image:".$this->imageName);
    $this->calculateAllImages();
  }
  
  public function saveImage() {
    imagegif($this->dest_image, $this->resizedImageName);
  }
}

/**
 * ImageResizePng Class extends ImageResizeClass
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-04-3: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.images
 * @subpackage ImageResizePng
 */
class ImageResizePng extends ImageResizeClass{
  /**
   * Method ImageResizePng::ImageResizePng()
   *
   * { Description :-
   * This method is a constructor for the ImageResizePng (Subclass for Png image resizing).
   * }
   */

  public function ImageResizePng($imageName, $arrImageSizes){
    $this->core = Zend_Registry::get('Core');
    $this->imageName = $imageName;
    $this->extension = "png";
    $this->arrImageSizes = $arrImageSizes;
  }


  /**
   * Method ImageResizePng::createResizedImage()
   *
   * { Description :-
   * This method puts the resized image in the specified destination.
   * }
   */

  public function createResizedImage($destPath, $imgName){
    $this->src_image = imagecreatefrompng($this->imageName);
    $this->destPath = $destPath;
    $this->imgName = $imgName;
    $this->core->logger->debug("createResizedImage Src_Image:".$this->imageName);
    $this->calculateAllImages();
  }
  
  public function saveImage() {
    imagepng($this->dest_image, $this->resizedImageName);
  }
}
?>