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
 * DocumentValidator Class implements Validator - based on Singleton Pattern
 *
 * Version history (please keep backward compatible):
 * 1.0, 2007-12-06: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.validators
 * @subpackage DocumentValidator
 */

require_once(dirname(__FILE__).'/validator.interface.php');

class DocumentValidator implements Validator {

  protected static $instance = null;
  /**
   * @var Core core object (dbh, logger, ...)
   */
  private $core;

  private $isValid = true;
  private $strDocumentUploadPath;

  private static $arrAllowedDocumentExtensions = array();
  private static $arrDeniedDocumentExtensions = array();

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
      self::$instance = new DocumentValidator();
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
    $this->core->logger->debug('massiveart.validators.DocumentValidator.isValid()');
		$this->isValid = true;

		try{
			$this->core->logger->debug('doc name: '.$arrDoc['name'].'; size: '.$arrDoc['size'].'; error: '.$arrDoc['error']);

			$intErrValue = $arrDoc['error'];

			if($intErrValue == UPLOAD_ERR_OK){
				//upload is ok - so do the right thing
				$strUploadedFileName = $arrDoc['name'];
				$this->core->logger->debug('file: '.$strUploadedFileName);

				if($arrDoc['size'] > $this->core->sysConfig->upload->documents->max_filesize){
					$this->isValid = 'Sorry, each file size cannot exceed the limit!';
				}

				if(file_exists($this->getDocumentUploadPath().'/'.$strUploadedFileName)) {
					$this->isValid = 'The file named '.$strUploadedFileName.' is already present, please use another filename to upload your file.<br>';
				}

				// check, if we allow this type of upload
				// here, all we want is images of type defined in settings.inc
				$arrFileInfos = pathinfo($arrDoc['name']);
				$strExtension = $arrFileInfos['extension'];
				$this->core->logger->debug('extension:'.$strExtension);

  			if(count(self::$arrAllowedDocumentExtensions) > 0){
        	$boolValidExtension = false;
  				foreach(self::$arrAllowedDocumentExtensions as $strAllowedDocumentExtension) {
  					if (strcasecmp(strtolower($strAllowedDocumentExtension), strtolower($strExtension)) == 0) {
  						$boolValidExtension = true;
  						break;
  					}
  				}
  			}
				if(count(self::$arrDeniedDocumentExtensions) > 0){
				  $boolValidExtension = true;

  				foreach(self::$arrDeniedDocumentExtensions as $strDeniedDocumentExtensions) {
  					if (strcasecmp(strtolower($strDeniedDocumentExtensions), strtolower($strExtension)) == 0) {
  						$boolValidExtension = false;
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
   * setAllowedDocumentExtensions
	 * @param array $arrAllowedDocumentExtensions
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
	 */
  public function setAllowedDocumentExtensions($arrAllowedDocumentExtensions){
  	$this->core->logger->debug('massiveart.validators.DocumentValidator.setAllowedDocumentExtensions()');
    self::$arrAllowedDocumentExtensions = $arrAllowedDocumentExtensions;
    $this->core->logger->debug(self::$arrAllowedDocumentExtensions);
  }

  /**
   * setDeniedDocumentExtensions
	 * @param array $arrDeniedDocumentExtensions
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
	 */
  public function setDeniedDocumentExtensions($arrDeniedDocumentExtensions){
  	$this->core->logger->debug('massiveart.validators.DocumentValidator.setDeniedDocumentExtensions()');
    self::$arrDeniedDocumentExtensions = $arrDeniedDocumentExtensions;
    $this->core->logger->debug(self::$arrDeniedDocumentExtensions);
  }

	/**
   * setDocumentUploadPath
	 * @param string $strDocumentUploadPath
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
	 */
  public function setDocumentUploadPath($strDocumentUploadPath){
    $this->strDocumentUploadPath = $strDocumentUploadPath;
  }

  /**
   * getDocumentUploadPath
	 * @return string $strDocumentUploadPath
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
	 */
  public function getDocumentUploadPath(){
    return $this->strDocumentUploadPath;
  }
}

?>
