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
 * Replacer Class - static function container
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-13: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.utilities
 * @subpackage Replacer
 */

class Replacer {
  
	/**
	 * @var Core
	 */
	protected $core;
	
	private $sqlStmt;	
	
	const SQL_LANGUAGE_ID = '%LANGUAGE_ID%';
	const SQL_ROOTLEVEL_LANGUAGE_ID = '%ROOTLEVEL_LANGUAGE_ID%';
	const SQL_ROOTLEVEL_ID = '%ROOTLEVEL_ID%';
	const SQL_WHERE = '%WHERE_ADDON%';
  const SQL_FIELDS = '%FIELDS_ADDON%';
  const SQL_JOIN = '%JOIN_ADDON%';  
  
  /**
   * Constructor
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');	
  }
  
  /**
   * sqlReplacer
   * @param string $strSQLStmt
   * @param integer|array $mixedReplaceLanguageId
   * @param integer $intReplaceRootLevelId
   * @param string $strReplaceWhere
   * @return string $sqlStmt
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
	public function sqlReplacer($strSQLStmt, $mixedReplaceLanguageId, $intReplaceRootLevelId = 0, $strReplaceWhere = '', $arrFields = array(), $arrJoins = array()){    
		try{
			if($strSQLStmt != ''){
	      $this->sqlStmt = $strSQLStmt;
	      
	      
	      $arrReplaceLanguageIds = (is_array($mixedReplaceLanguageId)) ? $mixedReplaceLanguageId : array('LANGUAGE_ID' => $mixedReplaceLanguageId);
	      
	      /**
		     * replace placeholder LANGUAGE_ID
		     */
	      $this->replaceLanguageIdPlaceholder($arrReplaceLanguageIds['LANGUAGE_ID']);
	      
	      /**
         * replace placeholder ROOTLEVEL_LANGUAGE_ID
         */
        $this->replaceRootLevelLanguageIdPlaceholder((array_key_exists('ROOTLEVEL_LANGUAGE_ID', $arrReplaceLanguageIds) ? $arrReplaceLanguageIds['ROOTLEVEL_LANGUAGE_ID'] : $arrReplaceLanguageIds['LANGUAGE_ID']));
	      
	      /**
         * replace placeholder ROOTLEVEL_ID
         */
        $this->replaceRootLevelIdPlaceholder($intReplaceRootLevelId);
	      
	      /**
		     * replace placeholder WHERE_ADDON
		     */
	      $this->replaceWherePlaceholder($strReplaceWhere);
	      
	      /**
         * replace placeholder FIELDS_ADDON
         */
        $this->replaceFieldsPlaceholder($arrFields);
        
			  /**
         * replace placeholder JOIN_ADDON
         */
        $this->replaceJoinsPlaceholder($arrJoins);
	    	
	    	return $this->sqlStmt; 	
	    }
		}catch (Exception $exc) {
      $this->core->logger->err($exc);
    }   
	}
	
	/**
   * replaceLanguageIdPlaceholder
   * @param integer $intReplaceLanguageId
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
	private function replaceLanguageIdPlaceholder($intReplaceLanguageId){
		try{
	    
			if(strpos($this->sqlStmt, self::SQL_LANGUAGE_ID) > -1){
	      if($intReplaceLanguageId != ''){
	        $this->sqlStmt = str_replace(self::SQL_LANGUAGE_ID, $intReplaceLanguageId, $this->sqlStmt);  
	      }else{
	        $this->sqlStmt = str_replace(self::SQL_LANGUAGE_ID, '1', $this->sqlStmt); // TODO : replace with default language  
	      }
	    }
	    
	  }catch (Exception $exc) {
      $this->core->logger->err($exc);
    } 	
	}
	
  /**
   * replaceRootLevelLanguageIdPlaceholder
   * @param integer $intReplaceLanguageId
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function replaceRootLevelLanguageIdPlaceholder($intReplaceLanguageId){
    try{
      
      if(strpos($this->sqlStmt, self::SQL_ROOTLEVEL_LANGUAGE_ID) > -1){
        if($intReplaceLanguageId != ''){
          $this->sqlStmt = str_replace(self::SQL_ROOTLEVEL_LANGUAGE_ID, $intReplaceLanguageId, $this->sqlStmt);  
        }else{
          $this->sqlStmt = str_replace(self::SQL_ROOTLEVEL_LANGUAGE_ID, '1', $this->sqlStmt); // TODO : replace with default language  
        }
      }
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }   
  }	
	
  /**
   * replaceRootLevelIdPlaceholder
   * @param integer $intReplaceRootLevelId
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function replaceRootLevelIdPlaceholder($intReplaceRootLevelId){
    try{
      
      if(strpos($this->sqlStmt, self::SQL_ROOTLEVEL_ID) > -1){
        if($intReplaceRootLevelId != ''){
          $this->sqlStmt = str_replace(self::SQL_ROOTLEVEL_ID, $intReplaceRootLevelId, $this->sqlStmt);  
        }else{
          // TODO : replace with standard rootlevel  
        }
      }
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }   
  }
	
	/**
   * replaceWherePlaceholder
   * @param string $strReplaceWhere
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function replaceWherePlaceholder($strReplaceWhere){    
  	try{		
  		if(strpos($this->sqlStmt, self::SQL_WHERE) > -1){
        $this->sqlStmt = str_replace(self::SQL_WHERE, $strReplaceWhere, $this->sqlStmt);     
      }    
  	}catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * replaceFieldsPlaceholder
   * @param array $arrReplaceFields
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function replaceFieldsPlaceholder($arrReplaceFields){    
    try{    
      if(strpos($this->sqlStmt, self::SQL_FIELDS) > -1){
        $strReplaceFields = '';
        if(count($arrReplaceFields) > 0){
          $strTblPrefix = '';
          if(array_key_exists('prefix', $arrReplaceFields)){
            $strTblPrefix = $arrReplaceFields['prefix'];          	
          }
          if(array_key_exists('fields', $arrReplaceFields)){
	          foreach($arrReplaceFields['fields'] as $value){
	            $strReplaceFields .= ', '.(($strTblPrefix != '') ? $strTblPrefix.'.' : '').$value;    
	          }	
          }
        }
      	$this->sqlStmt = str_replace(self::SQL_FIELDS, $strReplaceFields, $this->sqlStmt);     
      }    
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * replaceJoinsPlaceholder
   * @param array $arrReplaceJoins
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function replaceJoinsPlaceholder($arrReplaceJoins){    
    try{    
      if(strpos($this->sqlStmt, self::SQL_JOIN) > -1){
        $strReplaceJoins = '';
        if(count($arrReplaceJoins) > 0){
          foreach($arrReplaceJoins as $strJoin){
            $strReplaceJoins .= ' '.$strJoin.' ';    
          } 
        }
        $this->sqlStmt = str_replace(self::SQL_JOIN, $strReplaceJoins, $this->sqlStmt);     
      }    
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
	
}

?>