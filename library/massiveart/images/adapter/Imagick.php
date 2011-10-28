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
 * @package    library.massiveart.images.adapter
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ImageAdapter_Imagick
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-05-14: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.images
 * @subpackage ImageAdapter_Imagick
 */

require_once(dirname(__FILE__).'/interface.class.php');

class ImageAdapter_Imagick extends phMagick implements ImageAdapterInterface {

  protected $intRawWidth;
  protected $intRawHeight;
  protected $strRenderTmpDir;

  /**
   * Constructor
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function __construct($strSourceFile = ''){
    parent::__construct($strSourceFile);
  }

  /**
   * scale
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function scale($intWidth, $intHeight){

    if($this->intRawWidth == null || $this->intRawHeight == null){
      $arrDimention = $this->getDimentions();
      $this->setRawWidth($arrDimention[0]);
      $this->setRawHeight($arrDimention[1]);      
    }

    $dblXFact = $this->intRawWidth / $intWidth;
    $dblYFact = $this->intRawHeight / $intHeight;


    if($dblXFact < $dblYFact){
      $this->resize($intWidth);
    }else{
      $this->resize('', $intHeight);
    }

    $this->crop($intWidth, $intHeight);
  }
  
  /**
   * watermark
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function watermark($watermarkImage, $gravity, $transparency = 50){
    
    $arrImgDimention = $this->getDimentions();
    
    $watermark = new phMagick();
    $watermark->setSource($watermarkImage);
    
    $arrWatermarkDimention = $watermark->getDimentions();
        
    $dblXFact = $arrImgDimention[0] / $arrWatermarkDimention[0]; // width
    $dblYFact = $arrImgDimention[1] / $arrWatermarkDimention[1]; // height
    
    if($dblXFact < 1 || $dblYFact < 1){
      $strDesintaion = $this->strRenderTmpDir.uniqid().'.'.pathinfo($watermarkImage, PATHINFO_EXTENSION);
      $watermark->setDestination($strDesintaion);        
      if($dblXFact < $dblYFact){
        $intWidth = (int) ($arrImgDimention[0] * 0.9);
        $watermark->resize($intWidth);
      }else{
        $intHeight = (int) ($arrImgDimention[1] * 0.9);
        $watermark->resize('', $intHeight);
      }
      
      $watermarkImage = $watermark->getDestination();
    }
    
    parent::watermark($watermarkImage, $gravity, $transparency);
    
    //remove tmp file
    if(isset($strDesintaion)) unlink($strDesintaion);
  }

  /**
   * grayscale
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function grayscale(){
    parent::toGrayScale();
  }

  /**
   * shadow
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function shadow(){
    parent::dropShaddow();
  }

  /**
   * invert
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function invert(){
    parent::invertColors();
  }

  /**
   * setRawWidth
   * @param integer $intRawWidth
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function setRawWidth($intRawWidth){
    $this->intRawWidth = $intRawWidth;
  }

  /**
   * setRawHeight
   * @param integer $intRawHeight
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function setRawHeight($intRawHeight){
    $this->intRawHeight = $intRawHeight;
  }
  
  /**
   * setRenderTmpDir
   * @param string $strRenderTmpDir
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function setRenderTmpDir($strRenderTmpDir){
    $this->strRenderTmpDir = $strRenderTmpDir;
  }

  /**
   * getRenderTmpDir
   * @return string $strRenderTmpDir
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getRenderTmpDir(){
    return $this->strRenderTmpDir;
  }
}

?>