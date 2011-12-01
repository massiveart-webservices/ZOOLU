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
 * @package    application.zoolu.modules.core.models
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Model_Files
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2008-11-10: Thomas Schedler
 * 
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Model_Files {
  
  private $intLanguageId;
  private $intAlternativLanguageId;
  
  /**
   * @var Model_Table_Files
   */
  protected $objFileTable;
  
  /**
   * @var Model_Table_FileTitles
   */
  protected $objFileTitleTable;
  
  /**
   * @var Model_Table_FileAttributes
   */
  protected $objFileAttributeTable;
  
  /**
   * @var Model_Table_FileVersions
   */
  protected $objFileVersionTable;

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
   * loadFiles 
   * @param integer $intFolderId
   * @param integer $intLimitNumber = -1
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadFiles($intFolderId, $intLimitNumber = -1, $blnAddLanguageSpecificFilter = true, $blnReturnSelect = false, $strSearchValue = '', $strOrderColumn = 'alternativTitle', $strOrderSort = 'asc'){
    $this->core->logger->debug('core->models->Model_Files->loadFiles('.$intFolderId.','.$intLimitNumber.','.$blnAddLanguageSpecificFilter.','.$blnReturnSelect.','.$strSearchValue.','.$strOrderColumn.','.$strOrderSort.')');
    
    try{
	    $this->getFileTable();
	    
	    $objSelect = $this->objFileTable->select();   
	    $objSelect->setIntegrityCheck(false);
	    
	    /**
	     * SELECT files.id, files.fileId, files.filename, files.isImage, fileAttributes.xDim, fileAttributes.yDim, fileTitles.title, fileTitles.description,
	     *  CONCAT(users.fname, ' ', users.sname) AS creator, files.created, files.extension, files.mimeType
	     * FROM files
	     * LEFT JOIN fileAttributes ON fileAttributes.idFiles = files.id
	     * LEFT JOIN fileTitles ON fileTitles.idFiles = files.id AND fileTitles.idLanguages = ?
	     * INNER JOIN users ON users.id = files.creator  
	     * WHERE files.idParent = ?
	     */
	    $objSelect->from('files', array('id', 'fileId', 'version', 'idParent', 'idParentTypes', 'filename', 'isImage', 'created', 'changed', 'path', 'extension', 'mimeType', 'isLanguageSpecific'));
	    $objSelect->joinLeft('fileAttributes', 'fileAttributes.idFiles = files.id', array('xDim', 'yDim'));
	    $objSelect->joinLeft('fileTitles', 'fileTitles.idFiles = files.id AND fileTitles.idLanguages = '.$this->intLanguageId, array('title', 'description', 'idLanguages'));
	    $objSelect->joinLeft(array('fileTitleLanguages' => 'fileTitles'), 'fileTitleLanguages.idFiles = files.id', array());
	    $objSelect->joinLeft('languages', 'fileTitleLanguages.idLanguages = languages.id', array('languages' => new Zend_Db_Expr('GROUP_CONCAT(languages.languageCode SEPARATOR \', \')')));
      $objSelect->joinLeft('tagFiles', 'tagFiles.fileId = files.id AND tagFiles.idLanguages = '.$this->intLanguageId, array());
      $objSelect->joinLeft('tags', 'tags.id = tagFiles.idTags', array('tags' => new Zend_Db_Expr('GROUP_CONCAT(tags.title SEPARATOR \', \')')));
	    
      if($blnAddLanguageSpecificFilter == false){
        $objSelect->joinLeft('fileTitles AS alternativFileTitles', 'alternativFileTitles.idFiles = files.id AND alternativFileTitles.isDisplayTitle = 1', array('alternativTitle' => 'title', 'alternativDescription' => 'description', 'alternativLanguageId' => 'idLanguages'));
      }else if($this->intAlternativLanguageId > 0){
        $objSelect->joinLeft('fileTitles AS alternativFileTitles', 'alternativFileTitles.idFiles = files.id AND alternativFileTitles.idLanguages = '.$this->intAlternativLanguageId, array('alternativTitle' => 'title', 'alternativDescription' => 'description', 'alternativLanguageId' => 'idLanguages'));
      }
      
      $objSelect->joinLeft('fileTitles AS fallbackFileTitles', 'fallbackFileTitles.idFiles = files.id AND fallbackFileTitles.idLanguages = (SELECT fT.idLanguages FROM fileTitles AS fT WHERE fT.idFiles = files.id LIMIT 1)', array('fallbackTitle' => 'title', 'fallbackDescription' => 'description', 'fallbackLanguageId' => 'idLanguages'));
      
	    $objSelect->joinLeft('users', 'users.id = files.creator', array('CONCAT(users.fname, \' \', users.sname) AS creator'));
	    if($intFolderId != ''){
	      $objSelect->where('idParent = ?', $intFolderId);
	    }
	    if($strSearchValue != '') {
	    	//$objSelect->where('fileTitles.title LIKE ? OR alternativFileTitles.title LIKE ? OR fallbackFileTitles.title LIKE ?', '%'.$strSearchValue.'%');
	    	$objSelect->having('fileTitles.title LIKE ? OR alternativFileTitles.title LIKE ? OR fallbackFileTitles.title LIKE ? OR tags LIKE ?', '%'.$strSearchValue.'%');
	    }
	    if($strOrderColumn != '') {
	    	$objSelect->order($strOrderColumn.' '.$strOrderSort);
	    }
	    if($intLimitNumber != -1 && $intLimitNumber != ''){
	    	$objSelect->limit($intLimitNumber);	
	    }
	    
	    if($blnAddLanguageSpecificFilter == true) $objSelect->where('(files.isLanguageSpecific = 0) OR (files.isLanguageSpecific = 1 AND fileTitles.idLanguages IS NOT NULL)');
	    $objSelect->group('files.id');
	    $this->core->logger->debug(strval($objSelect));
	    if($blnReturnSelect) {
	    	return $objSelect;
	    } else {
	     return $this->objFileTable->fetchAll($objSelect);
	    } 
	  }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }  	
  }
  
  /**
   * loadFilesById 
   * @param string $strFileIds
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadFilesById($strFileIds){
    $this->core->logger->debug('core->models->Model_Files->loadFilesById('.$strFileIds.')');
    try{
	    $this->getFileTable();
	    
	    $strTmpFileIds = trim($strFileIds, '[]');
	    $arrFileIds = array();
	    $arrFileIds = explode('][', $strTmpFileIds);
	    
	    $objSelect = $this->objFileTable->select();   
	    $objSelect->setIntegrityCheck(false);
	    
	    /**
	     * SELECT files.id, files.fileId, files.filename, files.isImage, fileAttributes.xDim, fileAttributes.yDim, fileTitles.title, fileTitles.description,
	     *  CONCAT(users.fname, ' ', users.sname) AS creator, files.created, files.extension, files.mimeType
	     * FROM files
	     * LEFT JOIN fileAttributes ON fileAttributes.idFiles = files.id
	     * LEFT JOIN fileTitles ON fileTitles.idFiles = files.id AND fileTitles.idLanguages = ?
	     * INNER JOIN users ON users.id = files.creator  
	     * WHERE files.id = ? OR files.id = ? OR ...
	     */
	    
	    if(count($arrFileIds) > 0 && $strFileIds != '[]'){
	      $strIds = '';
	      foreach($arrFileIds as $intFileId){
	        $strIds .= $intFileId.',';
	      }
	    	
	    	$objSelect->from('files', array('id', 'fileId', 'version', 'filename', 'isLanguageSpecific', 'idDestination', 'idGroup', 'isImage', 'created', 'changed', 'path', 'extension', 'mimeType', 'size', 'stream', 'idFiles'));
	      $objSelect->joinLeft('fileAttributes', 'fileAttributes.idFiles = files.id', array('xDim', 'yDim'));
	      $objSelect->joinLeft('fileTitles', 'fileTitles.idFiles = files.id AND fileTitles.idLanguages = '.$this->intLanguageId, array('title', 'description', 'idLanguages'));
  	      
  	    if($this->intAlternativLanguageId > 0){
          $objSelect->joinLeft('fileTitles AS alternativFileTitles', 'alternativFileTitles.idFiles = files.id AND alternativFileTitles.idLanguages = '.$this->intAlternativLanguageId, array('alternativTitle' => 'title', 'alternativDescription' => 'description', 'alternativLanguageId' => 'idLanguages'));
        }
      
        $objSelect->joinLeft('fileTitles AS fallbackFileTitles', 'fallbackFileTitles.idFiles = files.id AND fallbackFileTitles.idLanguages = (SELECT fT.idLanguages FROM fileTitles AS fT WHERE fT.idFiles = files.id LIMIT 1)', array('fallbackTitle' => 'title', 'fallbackDescription' => 'description', 'fallbackLanguageId' => 'idLanguages'));
        
	      $objSelect->joinLeft('users', 'users.id = files.creator', array('CONCAT(users.fname, \' \', users.sname) AS creator'));  	
	      $objSelect->where('files.id IN ('.trim($strIds, ',').')');
	      $objSelect->order('FIND_IN_SET(files.id,\''.trim($strIds, ',').'\')');
	    
	      
	      return $this->objFileTable->fetchAll($objSelect);
	    }
	  }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }  
  }
  
  /**
   * getElementsByIds 
   * @param string $strFileIds
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getElementsByIds($strFileIds){
    $this->core->logger->debug('core->models->Model_Files->getElementsByIds('.$strFileIds.')');
    try{
	    $this->getFileTable();
	    
	    $objSelect = $this->objFileTable->select();   
	    $objSelect->setIntegrityCheck(false);
	    
	    $objSelect->from('files', array('id', 'relationId' => 'fileId', 'version', 'idParent', 'idParentTypes', 'filename', 'isLanguageSpecific', 'idDestination', 'idGroup', 'isImage', 'created', 'changed', 'path', 'extension', 'mimeType', 'size', 'stream', 'idFiles'));
      $objSelect->joinLeft('fileAttributes', 'fileAttributes.idFiles = files.id', array('xDim', 'yDim'));
      $objSelect->joinLeft('fileTitles', 'fileTitles.idFiles = files.id AND fileTitles.idLanguages = '.$this->intLanguageId, array('title', 'description', 'idLanguages'));
	    if($this->intAlternativLanguageId > 0){
        $objSelect->joinLeft('fileTitles AS alternativFileTitles', 'alternativFileTitles.idFiles = files.id AND alternativFileTitles.idLanguages = '.$this->intAlternativLanguageId, array('alternativTitle' => 'title', 'alternativDescription' => 'description', 'alternativLanguageId' => 'idLanguages'));
      }
      $objSelect->joinLeft('fileTitles AS fallbackFileTitles', 'fallbackFileTitles.idFiles = files.id AND fallbackFileTitles.idLanguages = (SELECT fT.idLanguages FROM fileTitles AS fT WHERE fT.idFiles = files.id LIMIT 1)', array('fallbackTitle' => 'title', 'fallbackDescription' => 'description', 'fallbackLanguageId' => 'idLanguages'));
      $objSelect->joinleft('folders', 'folders.id = files.idParent AND files.idParentTypes = '.$this->core->sysConfig->parent_types->folder, array('idRootLevels'));
      if(strpos($strFileIds, ',') !== false){
        $objSelect->where('files.id IN ('.$strFileIds.')');  
      }else{
        $objSelect->where('files.id = ?', $strFileIds);
      }
	    
	    return $this->objFileTable->fetchAll($objSelect);
	    
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * loadLatestFiles
   * @param integer $intRootLevelId
   * @param array $arrGroupFilters
   * @param array $arrLimitOffset
   * @param integer $intFilterLanguageId
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadLatestFiles($intRootLevelId = -1, $arrGroupFilters = array(), $arrLimitOffset = array(), $intFilterLanguageId = null){
    $this->core->logger->debug('core->models->Model_Files->loadLatestFiles('.$intRootLevelId.', '.var_export($arrGroupFilters, true).', '.var_export($arrLimitOffset, true).', '.$intFilterLanguageId.')');
    try{

      $objSelect = $this->getFileTable()->select();
      $objSelect->setIntegrityCheck(false);

      $strGroupIds = '';
      foreach($arrGroupFilters as $intGroupId){
        $strGroupIds .= $intGroupId.',';
      }
      
      $intLimit = 0;
      if(array_key_exists('limit', $arrLimitOffset) && $arrLimitOffset['limit'] > 0){
        $intLimit = $arrLimitOffset['limit']; 
        if(array_key_exists('offset', $arrLimitOffset) && $arrLimitOffset['offset'] > 0){
          $intLimit .= ', '.$arrLimitOffset['offset'];   
        }
      }
      
      $intFilterLanguageId = ($intFilterLanguageId == null) ? $this->intLanguageId : $intFilterLanguageId;

      $objSelect->distinct();
      $objSelect->from('files', array('id', 'fileId', 'version', 'filename', 'isImage', 'isLanguageSpecific', 'idDestination', 'idGroup', 'created', 'changed', 'path', 'extension', 'mimeType', 'size'));
      $objSelect->join('fileVersions', 'fileVersions.idFiles = files.id AND fileVersions.version = files.version', array('versionCreated' => 'created'));
      $objSelect->joinLeft('fileAttributes', 'fileAttributes.idFiles = files.id', array('xDim', 'yDim'));
      $objSelect->joinLeft('fileTitles', 'fileTitles.idFiles = files.id AND fileTitles.idLanguages = '.$intFilterLanguageId, array('title', 'description', 'idLanguages'));
      
      if($this->intAlternativLanguageId > 0){
        $objSelect->joinLeft('fileTitles AS alternativFileTitles', 'alternativFileTitles.idFiles = files.id AND alternativFileTitles.idLanguages = '.$this->intAlternativLanguageId, array('alternativTitle' => 'title', 'alternativDescription' => 'description', 'alternativLanguageId' => 'idLanguages'));
      }
      
      $objSelect->joinLeft('fileTitles AS fallbackFileTitles', 'fallbackFileTitles.idFiles = files.id AND fallbackFileTitles.idLanguages = (SELECT fT.idLanguages FROM fileTitles AS fT WHERE fT.idFiles = files.id LIMIT 1)', array('fallbackTitle' => 'title', 'fallbackDescription' => 'description', 'fallbackLanguageId' => 'idLanguages'));
      $objSelect->joinLeft('users', 'users.id = files.creator', array('CONCAT(users.fname, \' \', users.sname) AS creator'));      
      
      if($intRootLevelId > 0){
        $objSelect->join('folders', 'folders.id = files.idParent AND files.idParentTypes = '.$this->core->sysConfig->parent_types->folder.' AND folders.idRootLevels = '.$intRootLevelId, array());
      }   
         
      $objSelect->where('(files.isLanguageSpecific = 0) OR (files.isLanguageSpecific = 1 AND fileTitles.idLanguages IS NOT NULL)');
      if(trim($strGroupIds, ',') != ''){
        $objSelect->where('files.idGroup IN ('.trim($strGroupIds, ',').')');  
      }
      $objSelect->order('versionCreated DESC');
      
      if($intLimit != '' || $intLimit > 0){
        $objSelect->limit($intLimit);  
      }
            
      return $this->objFileTable->fetchAll($objSelect);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }  
  
  /**
   * loadFilesByStreamStatus
   * @param integer $intRootLevelId
   * @param boolean $blnStream
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadFilesByStreamStatus($intRootLevelId = -1, $blnStream = false, $blnIgnoreLanguage = false){
    $this->core->logger->debug('core->models->Model_Files->loadFilesByStreamStatus('.$intRootLevelId.', '.$blnStream.', '.$blnIgnoreLanguage.')');
    try{
      $objSelect = $this->getFileTable()->select();
      $objSelect->setIntegrityCheck(false); 

      $objSelect->distinct();
      $objSelect->from('files', array('id', 'fileId', 'version', 'filename', 'isImage', 'isLanguageSpecific', 'idDestination', 'idGroup', 'created', 'changed', 'path', 'extension', 'mimeType', 'size', 'stream'));
      $objSelect->joinLeft('fileAttributes', 'fileAttributes.idFiles = files.id', array('xDim', 'yDim'));
      if(!$blnIgnoreLanguage){
        $objSelect->joinLeft('fileTitles', 'fileTitles.idFiles = files.id AND fileTitles.idLanguages = '.$this->intLanguageId, array('title', 'description', 'idLanguages'));
        if($this->intAlternativLanguageId > 0){
          $objSelect->joinLeft('fileTitles AS alternativFileTitles', 'alternativFileTitles.idFiles = files.id AND alternativFileTitles.idLanguages = '.$this->intAlternativLanguageId, array('alternativTitle' => 'title', 'alternativDescription' => 'description', 'alternativLanguageId' => 'idLanguages'));
        }
        $objSelect->joinLeft('fileTitles AS fallbackFileTitles', 'fallbackFileTitles.idFiles = files.id AND fallbackFileTitles.idLanguages = (SELECT fT.idLanguages FROM fileTitles AS fT WHERE fT.idFiles = files.id LIMIT 1)', array('fallbackTitle' => 'title', 'fallbackDescription' => 'description', 'fallbackLanguageId' => 'idLanguages'));
      }
      $objSelect->joinLeft('users', 'users.id = files.creator', array('CONCAT(users.fname, \' \', users.sname) AS creator'));
      if($intRootLevelId > 0){
        $objSelect->join('folders', 'folders.id = files.idParent AND files.idParentTypes = '.$this->core->sysConfig->parent_types->folder.' AND folders.idRootLevels = '.$intRootLevelId, array());
      }   
      if(!$blnIgnoreLanguage){   
        $objSelect->where('(files.isLanguageSpecific = 0) OR (files.isLanguageSpecific = 1 AND fileTitles.idLanguages IS NOT NULL)');
      }
      $objSelect->where('files.stream = ?', (($blnStream) ? 1 : 0));
      
      return $this->objFileTable->fetchAll($objSelect);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }  
  }
  
  /**
   * loadFilesByFilter
   * @param integer $intRootLevelId
   * @param array $arrTagIds
   * @param array $arrFolderIds
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadFilesByFilter($intRootLevelId = -1, $arrTagIds = array(), $arrFolderIds = array(), $intFilterLanguageId = null, $arrOrder = array()){
    $this->core->logger->debug('core->models->Model_Files->loadFilesByFilter('.$intRootLevelId.', '.var_export($arrTagIds, true).', '.var_export($arrFolderIds, true).', '.$intFilterLanguageId.')');
    try{

      $objSelect = $this->getFileTable()->select();
      $objSelect->setIntegrityCheck(false);

      $strTagIds = '';
      foreach($arrTagIds as $intTagId){
        $strTagIds .= $intTagId.',';
      }

      $strFolderIds = '';
      foreach($arrFolderIds as $intFolderId){
        $strFolderIds .= $intFolderId.',';
      }
      
      $intFilterLanguageId = ($intFilterLanguageId == null) ? $this->intLanguageId : $intFilterLanguageId;

      $objSelect->distinct();
      $objSelect->from('files', array('id', 'fileId', 'version', 'filename', 'isImage', 'isLanguageSpecific', 'idDestination', 'idGroup', 'created', 'changed', 'path', 'extension', 'mimeType', 'size'));
      $objSelect->joinLeft('fileAttributes', 'fileAttributes.idFiles = files.id', array('xDim', 'yDim'));
      $objSelect->joinLeft('fileTitles', 'fileTitles.idFiles = files.id AND fileTitles.idLanguages = '.$intFilterLanguageId, array('title', 'description', 'idLanguages'));
      
      if($this->intAlternativLanguageId > 0){
        $objSelect->joinLeft('fileTitles AS alternativFileTitles', 'alternativFileTitles.idFiles = files.id AND alternativFileTitles.idLanguages = '.$this->intAlternativLanguageId, array('alternativTitle' => 'title', 'alternativDescription' => 'description', 'alternativLanguageId' => 'idLanguages'));
      }
      
      $objSelect->joinLeft('fileTitles AS fallbackFileTitles', 'fallbackFileTitles.idFiles = files.id AND fallbackFileTitles.idLanguages = (SELECT fT.idLanguages FROM fileTitles AS fT WHERE fT.idFiles = files.id LIMIT 1)', array('fallbackTitle' => 'title', 'fallbackDescription' => 'description', 'fallbackLanguageId' => 'idLanguages'));
      
      $objSelect->joinLeft('users', 'users.id = files.creator', array('CONCAT(users.fname, \' \', users.sname) AS creator'));

      if(trim($strTagIds, ',') != ''){
        $objSelect->join('tagFiles', 'tagFiles.fileId = files.id AND tagFiles.idTags IN ('.trim($strTagIds, ',').')', array());
      }
      
      if($intRootLevelId > 0 && trim($strFolderIds, ',') != ''){
        $objSelect->join('folders AS parent', 'parent.id = files.idParent AND parent.idRootLevels = '.$intRootLevelId.' AND parent.id IN ('.trim($strFolderIds, ',').')', array());
      }else if($intRootLevelId > 0){
        $objSelect->join('folders', 'folders.id = files.idParent AND files.idParentTypes = '.$this->core->sysConfig->parent_types->folder.' AND folders.idRootLevels = '.$intRootLevelId, array());
      }else if(trim($strFolderIds, ',') != ''){
        $objSelect->join('folders AS parent', 'parent.id IN ('.trim($strFolderIds, ',').')', array());
        $objSelect->join('folders', 'folders.id = files.idParent AND files.idParentTypes = '.$this->core->sysConfig->parent_types->folder.' AND folders.lft BETWEEN parent.lft AND parent.rgt AND folders.idRootLevels = parent.idRootLevels', array());
      }
      
      $objSelect->where('(files.isLanguageSpecific = 0) OR (files.isLanguageSpecific = 1 AND fileTitles.idLanguages IS NOT NULL)');
      if(array_key_exists('column', $arrOrder) && array_key_exists('type', $arrOrder)){
        $objSelect->order('files.'.$arrOrder['column'].' '.strtoupper($arrOrder['type']));    
      }else{
        $objSelect->order('title');  
      }
      
      return $this->objFileTable->fetchAll($objSelect);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * loadFileById 
   * @param integer $intFileId
   * @param integer $intVersion
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @return Zend_Db_Table_Rowset_Abstract
   * @version 1.0
   */
  public function loadFileById($intFileId, $intVersion = 0){
    $this->core->logger->debug('core->models->Model_Files->loadFileById('.$intFileId.','.$intVersion.')');
    try{
      $this->getFileTable();
      
      if($intFileId != '' && $intFileId > 0){      	
      	$objSelect = $this->objFileTable->select();   
        $objSelect->setIntegrityCheck(false);
      
        $objSelect->from('files', array('id', 'fileId', 'version', 'filename', 'isImage', 'isLanguageSpecific', 'idDestination', 'idGroup', 'created', 'changed', 'path', 'extension', 'mimeType', 'size', 'downloadCounter', 'idFiles'));
        $objSelect->joinLeft('fileAttributes', 'fileAttributes.idFiles = files.id', array('xDim', 'yDim'));
        $objSelect->joinLeft('fileTitles', 'fileTitles.idFiles = files.id AND fileTitles.idLanguages = '.$this->intLanguageId, array('title', 'description', 'idLanguages'));
        $objSelect->joinLeft('fileTitles AS fallbackFileTitles', 'fallbackFileTitles.idFiles = files.id AND fallbackFileTitles.idLanguages = (SELECT fT.idLanguages FROM fileTitles AS fT WHERE fT.idFiles = files.id LIMIT 1)', array('fallbackTitle' => 'title', 'fallbackDescription' => 'description', 'fallbackLanguageId' => 'idLanguages'));
        
        if($intVersion > 0){
          $objSelect->join('fileVersions', 'fileVersions.idFiles = files.id AND fileVersions.version = '.$intVersion, array('archiveVersion' => 'version', 'archiveExtension' => 'extension', 'archiveSize' => 'size', 'archived'));          
        }
        
        $objSelect->joinLeft('users', 'users.id = files.creator', array('CONCAT(users.fname, \' \', users.sname) AS creator'));   
        $objSelect->where('files.id = ?', $intFileId);
        
        return $this->objFileTable->fetchAll($objSelect);
      }
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }  
  }
  
  /**
   * loadFileVersions
   * @param integer $intFileId
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function loadFileVersions($intFileId){
    $this->core->logger->debug('core->models->Model_Files->loadFileVersions('.$intFileId.')');
    try{
      $this->getFileVersionTable();
      
        
      $objSelect = $this->objFileVersionTable->select();   
      $objSelect->setIntegrityCheck(false);
    
      $objSelect->from($this->objFileVersionTable, array('id', 'idFiles', 'fileId', 'version', 'filename', 'isImage', 'isLanguageSpecific', 'idDestination', 'idGroup', 'created', 'path', 'extension', 'mimeType', 'size', 'downloadCounter', 'archiver', 'archived'))
                ->joinLeft('users', 'users.id = fileVersions.archiver', array('CONCAT(users.fname, \' \', users.sname) AS archiver'))   
                ->joinLeft('fileTitles', 'fileTitles.idFiles = fileVersions.idFiles AND fileTitles.idLanguages = '.$this->intLanguageId, array('title', 'description', 'idLanguages'))
                ->where('fileVersions.idFiles = ?', $intFileId)
                ->order('archived DESC');

      return $this->objFileVersionTable->fetchAll($objSelect);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }  
  }
  
  /**
   * increaseDownloadCounter 
   * @param integer $intFileId
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function increaseDownloadCounter($intFileId){
    $this->core->logger->debug('core->models->Model_Files->increaseDownloadCounter('.$intFileId.')');
    try{
      $objFileData = $this->getFileTable()->find($intFileId);
      
      if(count($objFileData) == 1){
        $objFile = $objFileData->current();
        $objFile->downloadCounter++;
        $objFile->save();
      }
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }  
  }
  
  /**
   * loadFileByFileId 
   * @param string $strFileId
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadFileByFileId($strFileId){
    $this->core->logger->debug('core->models->Model_Files->loadFileByFileId('.$strFileId.')');
    try{
      $this->getFileTable();
      
      if($strFileId != ''){       
        $objSelect = $this->objFileTable->select();   
        $objSelect->setIntegrityCheck(false);
      
        $objSelect->from('files', array('id', 'fileId', 'version', 'filename', 'isImage', 'isLanguageSpecific', 'idDestination', 'idGroup', 'created', 'changed', 'path', 'extension', 'mimeType', 'size'));           
        $objSelect->where('files.fileId = ?', $strFileId);
        
        return $this->objFileTable->fetchAll($objSelect);
      }
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * getAllImageFiles 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getAllImageFiles(){
  	$this->core->logger->debug('core->models->Model_Files->getAllImageFiles()');    
    try{
      $this->getFileTable();
      
      $objSelect = $this->objFileTable->select();   
      $objSelect->setIntegrityCheck(false);
      
      /**
       * SELECT files.id, files.fileId, files.filename, files.created, files.extension, files.mimeType, files.size, fileAttributes.xDim, fileAttributes.yDim
       * FROM files
       *  LEFT JOIN fileAttributes ON
       *    fileAttributes.idFiles = files.id
       * WHERE files.isImage = 1
       */
      
      $objSelect->from('files', array('id', 'fileId', 'version', 'filename', 'created', 'changed', 'path', 'extension', 'mimeType', 'size'));
      $objSelect->joinLeft('fileAttributes', 'fileAttributes.idFiles = files.id', array('xDim', 'yDim'));   
      $objSelect->where('files.isImage = 1');
      
      return $this->objFileTable->fetchAll($objSelect);
    	
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    } 
  }
  
  /**
   * deleteFiles
   * @param string $strFiledIds
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function deleteFiles($strFileIds){
    $this->core->logger->debug('core->models->Model_Files->deleteFiles('.$strFileIds.')');  	
    try{
	  	
    	$this->getFileTable();
	  	
	  	$strTmpFileIds = trim($strFileIds, '[]');
	    $arrFileIds = array();
	    $arrFileIds = explode('][', $strTmpFileIds);
	    
	    $strWhere = '';
	    $intCounter = 0;
	    
	    if(count($arrFileIds) > 0){
	    	foreach($arrFileIds as $intFileId){
	    		if($intFileId != ''){
	    		  $intCounter++;
	    		  if($intCounter == 1){
	    			  $strWhere .= $this->objFileTable->getAdapter()->quoteInto('id = ?', $intFileId);
	    		  }else{
	    		  	$strWhere .= $this->objFileTable->getAdapter()->quoteInto(' OR id = ?', $intFileId);
	    		  }	
	    		}
	    	}
	    }
	    
	    /**
	     * delete files
	     */
	    if($strWhere != ''){
	      return $this->objFileTable->delete($strWhere);	
	    }
	    return false;
	    
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }    
  }
  
  /**
   * changeParentFolderId 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function changeParentFolderId($strFileIds, $intParentFolderId){
    $this->core->logger->debug('core->models->Model_Files->changeParentFolderId('.$strFileIds.','.$intParentFolderId.')');    
    try{ 
      $this->getFileTable();
      
      $strTmpFileIds = trim($strFileIds, '[]');
      $arrFileIds = array();
      $arrFileIds = explode('][', $strTmpFileIds);
      
      $strWhere = '';
      $intCounter = 0;
      
      if(count($arrFileIds) > 0){
        foreach($arrFileIds as $intFileId){
        $intCounter++;
            if($intCounter == 1){
              $strWhere .= $this->objFileTable->getAdapter()->quoteInto('id = ?', $intFileId);
            }else{
              $strWhere .= $this->objFileTable->getAdapter()->quoteInto(' OR id = ?', $intFileId);
            } 
        }
        $this->objFileTable->update(array('idParent' => $intParentFolderId), $strWhere);
      }
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * changeFileStreamStatusById 
   * @param integer $intFileId
   * @param boolean $blnStatus  
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function changeFileStreamStatusById($intFileId, $blnStatus = false){
    $this->core->logger->debug('core->models->Model_Files->changeFileStreamStatusById('.$intFileId.','.$blnStatus.')');    
    try{ 
      $this->getFileTable();
      
      $arrData = array('stream'  => (($blnStatus) ? 1 : 0),
                       'changed' => date('Y-m-d H:i:s'));  
          
      $strWhere = $this->objFileTable->getAdapter()->quoteInto('id = ?', $intFileId);      
      
      return $this->objFileTable->update($arrData, $strWhere);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }  
  }
  
  /**
   * hasDisplayTitle 
   * @param integer $intFileId
   * @return boolean
   * @author Thomas Schedler <tsh@massiveart.com>   
   */
  public function hasDisplayTitle($intFileId){
     $this->core->logger->debug('core->models->Model_Files->hasDisplayTitle('.$intFileId.')');    
    try{ 
      $this->getFileTable();
      
      if($intFileId != '' && $intFileId > 0){       
        $objSelect = $this->objFileTable->select();   
        $objSelect->setIntegrityCheck(false);
      
        $objSelect->from('files', array('id'));
        $objSelect->join('fileTitles', 'fileTitles.idFiles = files.id AND fileTitles.isDisplayTitle = 1', array('title'));
        $objSelect->where('files.id = ?', $intFileId);
               
        $objRowset = $this->objFileTable->fetchAll($objSelect);
        
        return (count($objRowset) > 0) ? true : false;
      } 
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    } 
  }
    
  /**
   * getFileTable 
   * @return Model_Table_Files
   * @author Cornelius Hansjakob <cha@massiveart.com>   
   * @version 1.0
   */
  public function getFileTable(){
    
    if($this->objFileTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/Files.php';
      $this->objFileTable = new Model_Table_Files();
    }
    
    return $this->objFileTable;
  }
  
  /**
   * getFileTitleTable 
   * @return Model_Table_FileTitles
   * @author Cornelius Hansjakob <cha@massiveart.com>   
   * @version 1.0
   */
  public function getFileTitleTable(){
    
    if($this->objFileTitleTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/FileTitles.php';
      $this->objFileTitleTable = new Model_Table_FileTitles();
    }
    
    return $this->objFileTitleTable;
  }
  
  /**
   * getFileAttributeTable 
   * @return Model_Table_FileAttributes
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getFileAttributeTable(){
    
    if($this->objFileAttributeTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/FileAttributes.php';
      $this->objFileAttributeTable = new Model_Table_FileAttributes();
    }
    
    return $this->objFileAttributeTable;
  }
  
  /**
   * getFileVersionTable 
   * @return Model_Table_FileVersions
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getFileVersionTable(){
    
    if($this->objFileVersionTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/FileVersions.php';
      $this->objFileVersionTable = new Model_Table_FileVersions();
    }
    
    return $this->objFileVersionTable;
  }
  
  /**
   * setLanguageId
   * @param integer $intLanguageId
   */
  public function setLanguageId($intLanguageId){
    $this->intLanguageId = $intLanguageId;  
  }
  
  /**
   * getLanguageId
   * @param integer $intLanguageId
   */
  public function getLanguageId(){
    return $this->intLanguageId;  
  } 
  
  /**
   * setAlternativLanguageId
   * @param integer $intAlternativLanguageId
   */
  public function setAlternativLanguageId($intAlternativLanguageId){
    $this->intAlternativLanguageId = $intAlternativLanguageId;  
  }
  
  /**
   * getAlternativLanguageId
   * @param integer $intAlternativLanguageId
   */
  public function getAlternativLanguageId(){
    return $this->intAlternativLanguageId;  
  } 
}

?>