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
 * @package    library.massiveart.validators
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ImageValidator Class implements Validator - based on Singleton Pattern
 *
 * Version history (please keep backward compatible):
 * 1.0, 2007-12-06: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.validators
 * @subpackage ImageValidator
 */

require_once(dirname(__FILE__).'/validator.interface.php');

class ImageValidator implements Validator {

  protected static $instance = null;
  /**
   * @var Core core object (dbh, logger, ...)
   */
  private $core;

  private $isValid = true;
  private $strImageUploadPath;

  private static $arrAllowedImageExtensions = array();

	protected function __construct(){
    $this->core = Zend_Registry::get('Core');
  }

  private function __clone(){}

  /**
   * getInstance
	 * @param  Core $core
	 * @return instance of the object
	 * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
	 */
  public static function getInstance(){
    if(self::$instance == null){
      self::$instance = new ImageValidator();
    }
    return self::$instance;
  }

	/**
	 * isValid
	 * @param     array $arrDoc to be validated.
	 * @return    mixed TRUE if valid, error message otherwise
	 * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
	 */
	public function isValid($arrDoc){
    $this->core->logger->debug('massiveart.validators.ImageValidator.isValid()');
		$this->isValid = true;

		try{
			$this->core->logger->debug('doc name: '.$arrDoc['name'].'; size: '.$arrDoc['size'].'; error: '.$arrDoc['error']);

			$intErrValue = $arrDoc['error'];

			if($intErrValue == UPLOAD_ERR_OK){
				//upload is ok - so do the right thing
				$strUploadedFileName = $arrDoc['name'];
				$this->core->logger->debug('file: '.$strUploadedFileName);

				if($arrDoc['size'] > $this->core->sysConfig->upload->images->max_filesize){
					$this->isValid = 'Sorry, each image size cannot exceed the Limit<br>';
				}

				if(file_exists($this->getImageUploadPath().'/'.$strUploadedFileName)) {
					$this->isValid = 'The file named '.$strUploadedFileName.' is already present, please use another filename to upload your file.<br>';
				}

				// check, if we allow this type of upload
				// here, all we want is images of type defined in settings.inc
				$arrFileInfos = pathinfo($arrDoc['name']);
				$strExtension = $arrFileInfos['extension'];
				$this->core->logger->debug('extension:'.$strExtension);

  			if(count(self::$arrAllowedImageExtensions) > 0){
        	$boolValidExtension = false;
  				foreach(self::$arrAllowedImageExtensions as $strAllowedImageExtension) {  				  
  					if (strcasecmp(strtolower($strAllowedImageExtension), strtolower($strExtension)) == 0) {
  						$boolValidExtension = true;
  						break;
  					}
  				}
  			}
				
				if(!$boolValidExtension) {
					$this->isValid = "Sorry, files with .$strExtension extensions are not supported.<br>";
				}

			}else{
				$this->isValid = "Sorry, something went wrong during the upload!<br>";
			}
		}catch(Exception $exc){
      $this->core->logger->err($exc);
    }
		return $this->isValid;
	}

	/**
   * setAllowedImageExtensions
	 * @param array $arrAllowedImageExtensions
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
	 */
  public function setAllowedImageExtensions($arrAllowedImageExtensions){
  	$this->core->logger->debug('massiveart.validators.ImageValidator.setAllowedImageExtensions()');
    self::$arrAllowedImageExtensions = $arrAllowedImageExtensions;
    $this->core->logger->debug(self::$arrAllowedImageExtensions);
  }

	/**
   * setImageUploadPath
	 * @param string $strImageUploadPath
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
	 */
  public function setImageUploadPath($strImageUploadPath){
    $this->strImageUploadPath = $strImageUploadPath;
  }

  /**
   * getImageUploadPath
	 * @return string $strImageUploadPath
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
	 */
  public function getImageUploadPath(){
    return $this->strImageUploadPath;
  }
}

?>
