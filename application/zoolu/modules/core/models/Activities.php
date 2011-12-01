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
 * Model_Activities
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2011-08-08: Cornelius Hansjakob
 * 
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Model_Activities {
    
  /**
   * @var Core
   */
  private $core;
  
  /**
   * @var Model_Table_Activities
   */
  protected $objActivitiesTable;
  
  /**
   * @var Model_Table_ActivityUsers
   */
  protected $objActivityUsersTable;
  
  /**
   * @var Model_Table_ActivityLinks
   */
  protected $objActivityLinksTable;
  
  /**
   * @var Model_Table_ActivityComments
   */
  protected $objActivityCommentsTable;
  
  /**
   * Constructor 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  }
  
  /**
   * loadActivities
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadActivities($strFilterType = '', $intOffset = 0, $intLimit = 0){
    $this->core->logger->debug('core->models->Model_Activities->loadActivities()');
    
    $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
    
    $objSelect = $this->getActivitiesTable()->select();
    $objSelect->setIntegrityCheck(false);    
    
    $objSelect->from('activities', array('id', 'idUsersCreator' => 'idUsers', 'idActivityUserStatusCreator' => 'idActivityUserStatus', 'title', 'description', 'created', 'changed'));    
    $objSelect->joinLeft('users', 'users.id = activities.idUsers', array('fname', 'sname', 'email'));
    $objSelect->joinLeft('files', 'files.id = users.idFiles AND files.isImage = 1', array('fileId', 'path', 'filename', 'extension'));    
    if($strFilterType == 'MY'){
      $objSelect->joinLeft('activityUsers', 'activityUsers.idActivities = activities.id AND activityUsers.idUsers = '.$intUserId, array('idUsers', 'idActivityUserStatus'));
      $objSelect->where('activities.idUsers = ?', $intUserId);
      $objSelect->orWhere('activityUsers.idUsers = ?', $intUserId);
    }elseif($strFilterType == 'DONE'){
      $objSelect->joinLeft('activityUsers', 'activityUsers.idActivities = activities.id AND activityUsers.idUsers = '.$intUserId, array('idUsers', 'idActivityUserStatus'));
      $objSelect->where('(activities.idUsers = ?', $intUserId);
      $objSelect->where('activities.idActivityUserStatus = ?)', $this->core->sysConfig->activity_user_status->done);
      $objSelect->orWhere('(activityUsers.idUsers = ?', $intUserId); 
      $objSelect->where('activityUsers.idActivityUserStatus = ?)', $this->core->sysConfig->activity_user_status->done); 
    }else{
      $objSelect->joinLeft('activityUsers', 'activityUsers.idActivities = activities.id AND activityUsers.idUsers = '.$intUserId, array('idUsers', 'idActivityUserStatus'));
    }
    $objSelect->order(array('activities.created DESC'));
    if($intLimit > 0){
      $objSelect->limit($intLimit, $intOffset);  
    }
    
    /*echo '<pre>';
    var_dump($this->getActivitiesTable()->fetchAll($objSelect));
    echo '</pre>';*/
    
    return $this->getActivitiesTable()->fetchAll($objSelect);
  }
  
  /**
   * loadActivity
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadActivity($intActivityId){
    $this->core->logger->debug('core->models->Model_Activities->loadActivity('.$intActivityId.')');
    
    $objSelect = $this->getActivitiesTable()->select();
    $objSelect->setIntegrityCheck(false);    
    
    $objSelect->from('activities', array('id', 'idUsersCreator' => 'idUsers', 'idActivityUserStatusCreator' => 'idActivityUserStatus', 'title', 'description', 'created', 'changed'));    
    $objSelect->joinLeft('users', 'users.id = activities.idUsers', array('fname', 'sname', 'email'));
    $objSelect->joinLeft('files', 'files.id = users.idFiles AND files.isImage = 1', array('fileId', 'path', 'filename', 'extension'));    
    $objSelect->where('activities.id = ?', $intActivityId);
    
    return $this->getActivitiesTable()->fetchRow($objSelect);
  }
  
  /**
   * loadRecipientsByActivityId
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadRecipientsByActivityId($intActivityId){
    $this->core->logger->debug('core->models->Model_Activities->loadRecipientsByActivityId('.$intActivityId.')');

    $objSelect = $this->getActivityUsersTable()->select();
    $objSelect->setIntegrityCheck(false);    

    $objSelect->from('activityUsers', array('id', 'idActivities', 'idUsers', 'idActivityUserStatus'));    
    $objSelect->joinLeft('users', 'users.id = activityUsers.idUsers', array('fname', 'sname', 'email'));
    $objSelect->joinLeft('files', 'files.id = users.idFiles AND files.isImage = 1', array('fileId', 'path', 'filename', 'extension'));
    $objSelect->where('activityUsers.idActivities = ?', $intActivityId);
    
    return $this->getActivityUsersTable()->fetchAll($objSelect);  
  }
  
  /**
   * loadLinksByActivityId
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadLinksByActivityId($intActivityId){
    $this->core->logger->debug('core->models->Model_Activities->loadLinksByActivityId('.$intActivityId.')');

    $objSelect = $this->getActivityLinksTable()->select();    
    $objSelect->setIntegrityCheck(false);
    
    $objSelect->from('activityLinks', array('id', 'idActivities', 'idModules', 'idRootLevels', 'idRelation', 'idLink'));
    $objSelect->join('rootLevels', 'rootLevels.id = activityLinks.idRootLevels', array('idRootLevelTypes', 'idRootLevelGroups', 'href'));
    $objSelect->where('activityLinks.idActivities = ?', $intActivityId);
    
    return $this->getActivityLinksTable()->fetchAll($objSelect);
  }
  
  /**
   * loadCommentsByActivityId
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadCommentsByActivityId($intActivityId){
    $this->core->logger->debug('core->models->Model_Activities->loadCommentsByActivityId('.$intActivityId.')');

    $objSelect = $this->getActivityCommentsTable()->select();
    $objSelect->setIntegrityCheck(false);    

    $objSelect->from('activityComments', array('id', 'idActivities', 'idUsers', 'comment', 'changed', 'created'));    
    $objSelect->joinLeft('users', 'users.id = activityComments.idUsers', array('fname', 'sname', 'email'));
    $objSelect->joinLeft('files', 'files.id = users.idFiles AND files.isImage = 1', array('fileId', 'path', 'filename', 'extension'));
    $objSelect->where('activityComments.idActivities = ?', $intActivityId);
    $objSelect->order(array('activityComments.created ASC'));
    
    return $this->getActivityCommentsTable()->fetchAll($objSelect);  
  }
  
  /**
   * loadComment
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadComment($intCommentId){
    $this->core->logger->debug('core->models->Model_Activities->loadComment('.$intCommentId.')');

    $objSelect = $this->getActivityCommentsTable()->select();
    $objSelect->setIntegrityCheck(false);    

    $objSelect->from('activityComments', array('id', 'idActivities', 'idUsers', 'comment', 'changed', 'created'));    
    $objSelect->joinLeft('users', 'users.id = activityComments.idUsers', array('fname', 'sname', 'email'));
    $objSelect->joinLeft('files', 'files.id = users.idFiles AND files.isImage = 1', array('fileId', 'path', 'filename', 'extension'));
    $objSelect->where('activityComments.id = ?', $intCommentId);
    
    return $this->getActivityCommentsTable()->fetchRow($objSelect);  
  }
  
  /**
   * add
   * @param array $arrData
   * @return stdClass Activity
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function add($arrData){
    $this->core->logger->debug('core->models->Model_Activities->add()');
    
    $objActivity = new stdClass();
    $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
    
    if(count($arrData) > 0 && (array_key_exists('title', $arrData) && $arrData['title'] != '')){
      /**
       * set data to add
       */
      $objActivity->idUsers = $intUserId; 
      $objActivity->idActivityUserStatus = $this->core->sysConfig->activity_user_status->read;
      $objActivity->title = $arrData['title'];          
      $objActivity->description = ((array_key_exists('description', $arrData)) ? $arrData['description'] : '');
      
      /**
       * insert data
       */
      $arrMainData = array('idUsers'              => $objActivity->idUsers,
                           'idActivityUserStatus' => $objActivity->idActivityUserStatus, 
                           'title' 						    => $objActivity->title,
                           'description'			    => $objActivity->description,
                           'created'              => date('Y-m-d H:i:s'));      
      $objActivity->id = $this->getActivitiesTable()->insert($arrMainData);      
    }
    return $objActivity;
  }
  
  /**
   * delete
   * @param integer $intActivityId
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function delete($intActivityId){
    $this->core->logger->debug('core->models->Model_Activities->delete('.$intActivityId.')');
    $strWhere = $this->getActivitiesTable()->getAdapter()->quoteInto('id = ?', $intActivityId);
    return $this->getActivitiesTable()->delete($strWhere);  
  }
  
  /**
   * addComment
   * @param array $arrData
   * @return stdClass ActivityComment
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function addComment($arrData){
    $this->core->logger->debug('core->models->Model_Activities->addComment()');

    $objActivityComment = new stdClass();
    $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
    
    if(count($arrData) > 0 && array_key_exists('idActivities', $arrData) && array_key_exists('comment', $arrData)){
      /**
       * set data to add
       */
      $objActivityComment->idActivities = $arrData['idActivities'];
      $objActivityComment->idUsers = $intUserId;
      $objActivityComment->comment = $arrData['comment'];
      
      /**
       * insert data
       */
      $arrMainData = array('idActivities'   => $objActivityComment->idActivities,
      										 'idUsers'        => $objActivityComment->idUsers,
                           'comment'		    => $objActivityComment->comment,
                           'created'        => date('Y-m-d H:i:s'));      
      $objActivityComment->id = $this->getActivityCommentsTable()->insert($arrMainData);  
    }
    
    return $objActivityComment;
  }
  
  /**
   * deleteComment
   * @param integer $intCommentId
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function deleteComment($intCommentId){
    $this->core->logger->debug('core->models->Model_Activities->deleteComment('.$intCommentId.')');
    $strWhere = $this->getActivityCommentsTable()->getAdapter()->quoteInto('id = ?', $intCommentId);
    return $this->getActivityCommentsTable()->delete($strWhere);
  }
  
  /**
   * addActivityUsers
   * @param integer $intActivityId
   * @param string $strUserIds
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function addActivityUsers($intActivityId, $strUserIds){
    $this->core->logger->debug('core->models->Model_Activities->addActivityUsers('.$intActivityId.', '.$strUserIds.')');

    $arrData = array();
    $arrData['idActivities'] = $intActivityId;
    $arrData['idActivityUserStatus'] = $this->core->sysConfig->activity_user_status->not_read; 
    
    $strTmpUserIds = trim($strUserIds, '[]');
    $arrUserIds = explode('][', $strTmpUserIds);

    if(count($arrUserIds) > 0){
      foreach($arrUserIds as $key => $intUserId){
        $arrData['idUsers'] = $intUserId;
        $this->getActivityUsersTable()->insert($arrData);
      }
    }
  }
  
  /**
   * addActivityLinks
   * @param integer $intActivityId
   * @param string $strRelations
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function addActivityLinks($intActivityId, $strRelations){
    $this->core->logger->debug('core->models->Model_Activities->addActivityLinks('.$intActivityId.', '.$strRelations.')');

    $arrData = array();
    $arrData['idActivities'] = $intActivityId;
    
    $arrRelations = array();
    $arrRelations = json_decode($strRelations);
    
    if(count($arrRelations) > 0){
      foreach($arrRelations as $objRelation){
        $arrData['idModules'] = $objRelation->moduleId;
        $arrData['idRootLevels'] = $objRelation->rootLevelId;
        $arrData['idRelation'] = $objRelation->relationId; 
        if(isset($objRelation->linkId) && $objRelation->linkId > 0 && $objRelation->linkId != $objRelation->relationId) $arrData['idLink'] = $objRelation->linkId;
        $this->getActivityLinksTable()->insert($arrData); 
      }  
    }
  }
  
  /**
   * changeUserStatusByActivityId
   * @param integer $intActivityId
   * @param boolean $blnIsChecked 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function changeUserStatusByActivityId($intActivityId, $blnIsChecked = false){
    $this->core->logger->debug('core->models->Model_Activities->changeUserStatusByActivityId('.$intActivityId.', '.$blnIsChecked.')');
    
    $intUserId = Zend_Auth::getInstance()->getIdentity()->id;
    
    // change activity status of author
    $strWhere = $this->getActivitiesTable()->getAdapter()->quoteInto('id = ?', $intActivityId);
    $strWhere .= $this->getActivitiesTable()->getAdapter()->quoteInto(' AND idUsers = ?', $intUserId);
    
    $intEffectedRows = $this->getActivitiesTable()->update(array('idActivityUserStatus' => (($blnIsChecked) ? $this->core->sysConfig->activity_user_status->done : $this->core->sysConfig->activity_user_status->read)), $strWhere);
    
    // change activity status of users if user is not author
    if($intEffectedRows == 0){
      $strWhere = $this->getActivityUsersTable()->getAdapter()->quoteInto('idActivities = ?', $intActivityId);
      $strWhere .= $this->getActivityUsersTable()->getAdapter()->quoteInto(' AND idUsers = ?', $intUserId);
      
      $intEffectedRows = $this->getActivityUsersTable()->update(array('idActivityUserStatus' => (($blnIsChecked) ? $this->core->sysConfig->activity_user_status->done : $this->core->sysConfig->activity_user_status->read)), $strWhere);  
    }
    
    return $intEffectedRows;
  }
  
  /**
   * getActivitiesTable
   * @return Zend_Db_Table_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getActivitiesTable(){
    if($this->objActivitiesTable === null) {
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/Activities.php';
      $this->objActivitiesTable = new Model_Table_Activities();
    }
    return $this->objActivitiesTable;
  }
  
  /**
   * getActivityUsersTable
   * @return Zend_Db_Table_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getActivityUsersTable(){
    if($this->objActivityUsersTable === null) {
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/ActivityUsers.php';
      $this->objActivityUsersTable = new Model_Table_ActivityUsers();
    }
    return $this->objActivityUsersTable;
  }
  
  /**
   * getActivityLinksTable
   * @return Zend_Db_Table_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getActivityLinksTable(){
    if($this->objActivityLinksTable === null) {
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/ActivityLinks.php';
      $this->objActivityLinksTable = new Model_Table_ActivityLinks();
    }
    return $this->objActivityLinksTable;
  }
  
  /**
   * getActivityCommentsTable
   * @return Zend_Db_Table_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getActivityCommentsTable(){
    if($this->objActivityCommentsTable === null) {
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/ActivityComments.php';
      $this->objActivityCommentsTable = new Model_Table_ActivityComments();
    }
    return $this->objActivityCommentsTable;
  }
}

?>