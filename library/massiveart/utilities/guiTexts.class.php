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
 * GuiTexts Class - based on Singleton Pattern
 *
 * Needs a language code!
 *
 * Version history (please keep backward compatible):
 * 1.0, 2007-11-19: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package com.massiveart.utilities
 * @subpackage GuiText
 */

class GuiTexts {
  /**
   * object instance
   */
  private static $instance = null;
  private $logger;
  private $dbh;

  private $strLanguageCode = DEFAULT_LANGUAGE_CODE;
  private $arrGuiTexts = array();

  /**
   * Constructor
   */
  protected function __construct(Logger &$logger){
    $this->logger = $logger;    
    $this->arrGuiTexts = _loadGuiTexts();
    /*
    $this->logger = $logger;
    $this->dbh = $dbh;

    /**
     * check, if ther is a session based language code
     *
    if(isset($_SESSION['sesLanguageCode'])){
      $this->strLanguageCode = $_SESSION['sesLanguageCode'];
    }

    /**
     * load gui texts from db
     *
    try {
      $stmt = $this->dbh->prepare("SELECT guiTexts.guiId, guiTexts.description
                                      FROM guiTexts
                                        INNER JOIN languages ON
                                          languages.languageCode = ?
                                        WHERE guiTexts.idLanguanges = languages.id");
      $stmt->bindParam(1, $this->strLanguageCode, PDO::PARAM_STR);
      $stmt->execute();

      if($stmt->rowCount() > 0){
        $this->arrGuiTexts = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
      }
    }catch (PDOException $exc) {
      print $exc->getMessage();
    }
    */
  }

  private function __clone(){}

  /**
   * getInstance
   * @return object instance of the class
   */
  public static function getInstance(Logger &$logger){
    if(self::$instance == null){
      self::$instance = new GuiTexts($logger);
    }
    return self::$instance;
  }

  /**
   * getInstance
   * @param string $strLangCode lanuage code
   * @return string
   */
  public function getText($strGuiId){
    $strGuiText = '';

    /**
     * check, if guiId exists
     */
    if(array_key_exists($strGuiId, $this->arrGuiTexts)){
      $strGuiText = nl2br(htmlentities($this->arrGuiTexts[$strGuiId][0], ENT_COMPAT, DEFAULT_ENCODING));
    }

    return $strGuiText;
  }

  /**
   * getInstance
   * @param string $strLangCode lanuage code
   * @return string
   */
  public function getTextWithoutEncoding($strGuiId){
    $strGuiText = '';

    /**
     * check, if guiId exists
     */
    if(array_key_exists($strGuiId, $this->arrGuiTexts)){
      $strGuiText = $this->arrGuiTexts[$strGuiId][0];
    }

    return $strGuiText;
  }
}

?>