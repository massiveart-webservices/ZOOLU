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
 * Model_Members
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2011-01-19: Cornelius Hansjakob
 * 
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Model_Members {
  
  private $intLanguageId;
  
  /**
   * @var Model_Table_Members 
   */
  protected $objMembersTable;
  
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
   * loadNavigation
   * @param integer $intRootLevelId 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadNavigation($intRootLevelId){
    $this->core->logger->debug('core->models->Members->loadNavigation('.$intRootLevelId.')');    

    $objSelect = $this->getMembersTable()->select();
    $objSelect->setIntegrityCheck(false);
    
    $objSelect->from('members', array('id', 'title' => new Zend_Db_Expr("CONCAT(members.fname, ' ', members.sname)"), 'type' => new Zend_Db_Expr("'member'")))
              ->join('genericForms', 'genericForms.id = members.idGenericForms', array('genericFormId', 'version'))
              ->order('title');
    
    return $this->objMembersTable->fetchAll($objSelect); 
  }   
  
  /**
   * loadMember
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @param integer $intElementId
   * @version 1.0
   */
  public function loadMember($intElementId){
    $this->core->logger->debug('core->models->Members->loadMember('.$intElementId.')');
    
    $objSelect = $this->getMembersTable()->select();   
    $objSelect->setIntegrityCheck(false);
    
    $objSelect->from('members');
    $objSelect->where('members.id = ?', $intElementId);
        
    return $this->getMembersTable()->fetchAll($objSelect);    
  }
  
  /**
   * loadMemberGroupById
   * @param integer $intElementId
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadMemberGroupById($intElementId){
    $this->core->logger->debug('core->models->Members->loadMemberGroupById('.$intElementId.')');

    $objSelect = $this->getMembersTable()->select();   
    $objSelect->setIntegrityCheck(false);
    
    // FIXME : must be another solution -> memberGroups table ???
    //         -> only quick & dirty solution
    
    $objSelect->from('members', array());
    $objSelect->joinLeft('fields', 'fields.name = \'group\'', array('fieldId' => 'id'));
    $objSelect->joinLeft('member-DEFAULT_MEMBER-1-InstanceMultiFields', '`member-DEFAULT_MEMBER-1-InstanceMultiFields`.idMembers = members.id AND `member-DEFAULT_MEMBER-1-InstanceMultiFields`.idFields = fields.id', array('idRelation'));
    $objSelect->joinLeft('groups', 'groups.id = `member-DEFAULT_MEMBER-1-InstanceMultiFields`.idRelation', array('groupId' => 'id', 'title', 'key'));
    $objSelect->where('members.id = ?', $intElementId);
    
    return $this->getMembersTable()->fetchAll($objSelect);    
  }
  
  /**
   * loadMembersByLastLogin
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadMembersByLastLogin($intCompanyId = 0, $intMemberId = 0, $timestamp = null){
    $this->core->logger->debug('core->models->Members->loadMembersByLastLogin('.$intCompanyId.', '.$intMemberId.', '.$timestamp.')');

    $objSelect = $this->getMembersTable()->select();
    $objSelect->setIntegrityCheck(false);
    
    $objSelect->from('members', array('id', 'fname', 'sname', 'username', 'company', 'email', 'lastLogin'));
    $objSelect->joinLeft('companies', 'companies.id = members.company', array('companyName' => 'name', 'companyMail' => 'email'));
    // only members with status ACTIVE and company status ACTIVE
    $objSelect->where('members.status = ?', 1);
    $objSelect->where('companies.status = ?', 1);
    // filter company
    if($intCompanyId > 0) $objSelect->where('members.company = ?', $intCompanyId);
    // filter only member
    if($intMemberId > 0) $objSelect->where('members.id = ?', $intMemberId);
    // timestamp filter
    if($timestamp != null && $timestamp > 0) $objSelect->where('UNIX_TIMESTAMP(lastLogin) < ?', $timestamp);
    
    return $this->getMembersTable()->fetchAll($objSelect);
  }
  
  /**
   * loadMembersByCompanyId
   * @param integer $intCompanyId
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function loadMembersByCompanyId($intCompanyId){
    $this->core->logger->debug('core->models->Members->loadMembersByCompanyId('.$intCompanyId.')');
    
    $objSelect = $this->getMembersTable()->select();
    $objSelect->setIntegrityCheck(false);
    
    $objSelect->from('members', array('id', 'fname', 'sname', 'username', 'company', 'email', 'lastLogin', 'status'));
    $objSelect->joinLeft('contactStatus', 'contactStatus.id = members.status', array('statusTitle' => 'title'));
    $objSelect->where('members.company = ?', $intCompanyId);
    
    return $this->getMembersTable()->fetchAll($objSelect);
  }
  
  /**
   * addMember   
   * @param array $arrData
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0 
   */
  public function addMember($arrData){
   $this->core->logger->debug('core->models->Members->addMember('.var_export($arrData, true).')');
    try{ 
      // set lastLogin to current date at creation of member
      $arrData['lastLogin'] = date('Y-m-d H:i:s');      
      return $this->getMembersTable()->insert($arrData);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * editMember
   * @param integer $intMemberId   
   * @param array $arrData
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0 
   */
  public function editMember($intMemberId, $arrData){
    $this->core->logger->debug('core->models->Members->editMember('.$intMemberId.','.var_export($arrData, true).')');
    try{
      $this->getMembersTable();
      
      $strWhere = $this->objMembersTable->getAdapter()->quoteInto('id = ?', $intMemberId);
      
      return $this->objMembersTable->update($arrData, $strWhere);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * updateLastLogin
   * @param integer $intMemberId   
   * @param array $arrData
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0 
   */
  public function updateLastLogin($intMemberId){
    $this->core->logger->debug('core->models->Members->updateLastLogin('.$intMemberId.')');
    try{
      $this->getMembersTable();
      
      $arrData = array('changed'   => date('Y-m-d H:i:s'),
                       'lastLogin' => date('Y-m-d H:i:s'));
      
      $strWhere = $this->objMembersTable->getAdapter()->quoteInto('id = ?', $intMemberId);
      
      return $this->objMembersTable->update($arrData, $strWhere);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
    
  /**
   * deleteMember 
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @param integer $intMemberId
   * @version 1.0
   */
  public function deleteMember($intMemberId){
    $this->core->logger->debug('core->models->Members->deleteMember('.$intMemberId.')');
    
    $this->getMembersTable();
    
    /**
     * delete member
     */
    $strWhere = $this->objMembersTable->getAdapter()->quoteInto('id = ?', $intMemberId);  
    
    return $this->objMembersTable->delete($strWhere);
  }
  
  /**
   * deleteMembers
   * @param array $arrMemberIds
   * @return integer the number of rows deleted
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function deleteMembers($arrMemberIds){
    try{  
      $strWhere = '';
      $intCounter = 0;
      if(count($arrMemberIds) > 0){
        foreach($arrMemberIds as $intMemberId){
          if($intMemberId != ''){
            if($intCounter == 0){
              $strWhere .= $this->getMembersTable()->getAdapter()->quoteInto('id = ?', $intMemberId);
            }else{
              $strWhere .= $this->getMembersTable()->getAdapter()->quoteInto(' OR id = ?', $intMemberId);
            }
            $intCounter++;
          }
        }
      }   
      return $this->objMembersTable->delete($strWhere);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * changeMemberStatus
   * @param integer $intMemberId
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0 
   */
  public function changeMemberStatus($intMemberId, $intStatus = 1){
    try{
      $this->getMembersTable();
      
      $arrData = array('status'   => $intStatus, 
                       'changed'  => date('Y-m-d H:i:s'));
            
      $strWhere = $this->objMembersTable->getAdapter()->quoteInto('id = ?', $intMemberId);
      $strWhere .= ' AND '.$this->objMembersTable->getAdapter()->quoteInto('status != ?', 3); // 3 = status LOCKED
      
      return $this->objMembersTable->update($arrData, $strWhere);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    } 
  }
  
  /**
   * activateMemberByData
   * @param array $arrMemberData
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0 
   */
  public function activateMemberByData($arrMemberData){
    try{
      $this->getMembersTable();
      
      $arrData = array('status'    => 1,  // 1 = ACTIVE
                       'lastLogin' => date('Y-m-d H:i:s'),
                       'changed'   => date('Y-m-d H:i:s'));
            
      $strWhere = $this->objMembersTable->getAdapter()->quoteInto('id = ?', $arrMemberData[0]);
      $strWhere .= ' AND '.$this->objMembersTable->getAdapter()->quoteInto('username = ?', $arrMemberData[1]);
      $strWhere .= ' AND '.$this->objMembersTable->getAdapter()->quoteInto('lastLogin = ?', date('Y-m-d H:i:s', $arrMemberData[2]));
      $strWhere .= ' AND '.$this->objMembersTable->getAdapter()->quoteInto('status != ?', 3); // 3 = status LOCKED
      
      return $this->objMembersTable->update($arrData, $strWhere);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    } 
  }
  
  /**
   * setNewPassword
   * @param integer $intMemberId
   * @return string $strPassword
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0 
   */
  public function setNewPassword($intMemberId){
    try{
      $this->getMembersTable();
      
      /**
       * generate new password for user
       */
      $objPasswordHelper = new PasswordHelper();
      $strNewPassword = $objPasswordHelper->generatePassword();
      
      /**
       * update member entry
       */
      $arrData = array('password'   => Crypt::encrypt($this->core, $this->core->config->crypt->key, $strNewPassword),
                       'lastLogin'  => date('Y-m-d H:i:s'), 
                       'changed'    => date('Y-m-d H:i:s'));
            
      $strWhere = $this->objMembersTable->getAdapter()->quoteInto('id = ?', $intMemberId);
      
      $intEffectedRows = $this->objMembersTable->update($arrData, $strWhere);
      
      /**
       * check if a entry is updated
       */
      if($intEffectedRows == 0){
        $strNewPassword = '';  
      }
      
      return $strNewPassword;
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }  
  }
  
  /**
   * getMemberGroups
   * @param integer $intUserId
   * @return Zend_Db_Table_Rowset_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getMemberGroups($intMemberId){
    try{
      /*
       * TODO : build memberGroups table and field saving
       * 
      $objSelect = $this->getMemberGroupTable()->select();

      $objSelect->setIntegrityCheck(false);
      $objSelect->from($this->objMemberGroupTable, array('idMembers', 'idGroups'))
                ->joinInner('groups', 'groups.id = memberGroups.idGroups', array('key'))
                ->where('memberGroups.idMembers = ?', $intMemberId);
      return $this->objMemberGroupTable->fetchAll($objSelect);*/
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * getMembersTable
   * @return Model_Table_Members $objMemberTable
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0 
   */
  public function getMembersTable(){    
    if($this->objMembersTable === null){
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/Members.php';
      $this->objMembersTable = new Model_Table_Members();
    }
    
    return $this->objMembersTable;
  }
  
  /**
   * getMemberGroupTable
   * @return Zend_Db_Table_Abstract
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getMemberGroupTable(){
    if($this->objMemberGroupTable === null) {
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/tables/MemberGroups.php';
      $this->objMemberGroupTable = new Model_Table_MemberGroups();
    }
    
    return $this->objMemberGroupTable;
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
}
?>