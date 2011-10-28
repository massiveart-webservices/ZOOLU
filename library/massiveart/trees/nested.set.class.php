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
 * @package    library.massiveart.trees
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * NestedSet
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-02-04: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.trees
 * @subpackage NestedSet
 */

class NestedSet {
  
  /**
   * @var Core
   */
  private $core;
  
  /**
   * @var Zend_Db_Table_Abstract
   */
  private $objTable;
  
  /**
   * @var Zend_Db_Table_Rowset_Abstract
   */
  private $objNodeData;
  
  /**
   * @var Zend_Db_Table_Rowset_Abstract
   */
  private $objParentData;

  /**
   * @var Zend_Db_Table_Rowset_Abstract
   */
  private $objDestinationData;
  
  private $intNodeId;
  
  private $strDBFLft = 'lft';
  private $strDBFRgt = 'rgt';
  private $strDBFDepth = 'depth';
  
  private $strDBFParent = null;
  private $strDBFRoot = null;
  
  private $intLft;
  private $intRgt;
  private $intDepth;
  
  private $intParentId;
  private $intDestinationId;
  private $intRootId;
  
  /**
   * Constructor
   */
  public function __construct(Zend_Db_Table_Abstract &$objTable){    
    $this->objTable = $objTable;
    $this->core = Zend_Registry::get('Core');
  }
  
  /**
   * newRootNode   
   * @param array $arrData
   * @return integer $intNodeId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function newRootNode($arrData = array()){
    $this->intLft = 1;
    $this->intRgt = 2;
    $this->intDepth = 0;
    $this->intParentId = 0;
    
    $this->lockTable();
    
    /**
     * insert root node
     */
    $this->insertNode($arrData);
    
    if(!is_null($this->strDBFRoot)){
      /**
       * update root node id with his own id
       */
      $arrUpdateData = array($this->strDBFRoot => $this->intNodeId);
      $this->updateNode($arrUpdateData);
    }
    
    $this->unlockTable();
    
    return $this->intNodeId;
  }
  
  /**
   * newRootNodeWithExistingRootId   
   * @param integer $intRootId
   * @param array $arrData
   * @return integer $intNodeId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function newRootNodeWithExistingRootId($intRootId, $arrData = array()){
    $this->intLft = 1;
    $this->intRgt = 2;
    $this->intDepth = 0;
    $this->intParentId = 0;
    $this->intRootId = $intRootId;
    
    $this->lockTable();
    
    /**
     * insert root node with existing root id
     */
    $this->insertNode($arrData);
    
    $this->unlockTable();
    
    return $this->intNodeId;
  }
  
  /**
   * newFirstChild
   * @param interg $intParentId   
   * @param array $arrData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function newFirstChild($intParentId, $arrData = array()){
    $this->intParentId = $intParentId;    
    $this->loadParentData();
    
    if($this->objParentData instanceof Zend_Db_Table_Rowset_Abstract && count($this->objParentData) > 0){
      $arrParent = $this->objParentData->current()->toArray();
      
      $this->intLft = $arrParent[$this->strDBFLft] + 1;
      $this->intRgt = $arrParent[$this->strDBFLft] + 2;
      $this->intDepth = $arrParent[$this->strDBFDepth] + 1;
      
      if(!is_null($this->strDBFParent)) $this->intParentId = $intParentId;
      if(!is_null($this->strDBFRoot)) $this->intRootId = $arrParent[$this->strDBFRoot];
      
      $this->lockTable();
      
      /**
       * shift l & r values
       */
      $this->shiftLRValues($this->intLft, 2);
      
      /**
       * insert node
       */
      $this->insertNode($arrData);    

      $this->unlockTable();
    }    
    return $this->intNodeId;
  }
  
  /**
   * newLastChild
   * @param interg $intParentId   
   * @param array $arrData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function newLastChild($intParentId, $arrData = array()){
    $this->intParentId = $intParentId;    
    $this->loadParentData();
    
    if($this->objParentData instanceof Zend_Db_Table_Rowset_Abstract && count($this->objParentData) > 0){
      $arrParent = $this->objParentData->current()->toArray();
      
      $this->intLft = $arrParent[$this->strDBFRgt];
      $this->intRgt = $arrParent[$this->strDBFRgt] + 1;
      $this->intDepth = $arrParent[$this->strDBFDepth] + 1;
      
      if(!is_null($this->strDBFParent)) $this->intParentId = $intParentId;
      if(!is_null($this->strDBFRoot)) $this->intRootId = $arrParent[$this->strDBFRoot];
      
      $this->lockTable();
      
      /**
       * shift l & r values
       */
      $this->shiftLRValues($this->intLft, 2);
      
      /**
       * insert node
       */
      $this->insertNode($arrData);    

      $this->unlockTable();
    }    
    return $this->intNodeId;
  }
  
  /**
   * newNextSibling
   * @param integer $intNodeId
   * @param array $arrData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function newNextSibling($intNodeId, $arrData = array()){
    $this->intNodeId = $intNodeId;
    $this->loadNodeData();
    
    if($this->objNodeData instanceof Zend_Db_Table_Rowset_Abstract && count($this->objNodeData) > 0){
      $arrNode = $this->objNodeData->current()->toArray();
      
      
      $this->intLft = $arrNode[$this->strDBFRgt] + 1;
      $this->intRgt = $arrNode[$this->strDBFRgt] + 2;
      $this->intDepth = $arrNode[$this->strDBFDepth];
      
      if(!is_null($this->strDBFRoot)) $this->intRootId = $arrNode[$this->strDBFRoot];
      if(!is_null($this->strDBFParent)) $this->intParentId = $arrNode[$this->strDBFParent];
      
      $this->lockTable();
      
      /**
       * shift l & r values
       */
      $this->shiftLRValues($this->intLft, 2);
      
      /**
       * insert node
       */
      $this->insertNode($arrData); 
      
      $this->unlockTable();
    }
    
    return $this->intNodeId;
  }
  
  /**
   * deleteNode
   * @param integer $intNodeId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function deleteNode($intNodeId){
    $this->intNodeId = $intNodeId;
    $this->loadNodeData();
    
    if($this->objNodeData instanceof Zend_Db_Table_Rowset_Abstract && count($this->objNodeData) > 0){
      $arrNode = $this->objNodeData->current()->toArray();
      if(!is_null($this->strDBFRoot)) $this->intRootId = $arrNode[$this->strDBFRoot];
      
      $this->lockTable();
      
      $strSqlAddonRootId = '';
      if(!is_null($this->strDBFRoot) && $this->intRootId > 0) $strSqlAddonRootId = $this->strDBFRoot.' = '.$this->intRootId.' AND ';
      
      /**
       * delete categories
       */                
      $this->objTable->delete($strSqlAddonRootId.$this->strDBFLft.' BETWEEN '.$arrNode[$this->strDBFLft].' AND '.$arrNode[$this->strDBFRgt].'');
      
      $this->shiftLRValues($arrNode[$this->strDBFRgt] + 1, $arrNode[$this->strDBFLft] - $arrNode[$this->strDBFRgt] - 1);      
      
      $this->unlockTable();
    }
  }
  
  /**
   * moveToLastChild
   * @param integer $intNodeId   
   * @param integer $intDestinationId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function moveToLastChild($intNodeId, $intDestinationId){
    $this->intParentId = $intDestinationId;
    $this->loadParentData();
    
    if($this->objParentData instanceof Zend_Db_Table_Rowset_Abstract && count($this->objParentData) > 0){
      $arrDestination = $this->objParentData->current()->toArray();
      if(!is_null($this->strDBFRoot)) $this->intRootId = $arrDestination[$this->strDBFRoot];
      $this->lockTable();
      
      $this->moveSubtree($intNodeId, $arrDestination[$this->strDBFRgt], $arrDestination[$this->strDBFDepth] + 1);
            
      $arrUpdateData = array($this->strDBFDepth => $arrDestination[$this->strDBFDepth] + 1);
      if(!is_null($this->strDBFParent)) $arrUpdateData[$this->strDBFParent] = $intDestinationId;   
    
      $this->updateNode($arrUpdateData);
      
      
      $this->unlockTable();
    }
  }
  
  /**
   * moveToNextSibling
   * @param integer $intNodeId   
   * @param integer $intDestinationId
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function moveToNextSibling($intNodeId, $intDestinationId){
    $this->intDestinationId = $intDestinationId;
    $this->loadDestinationData();
    
    if($this->objDestinationData instanceof Zend_Db_Table_Rowset_Abstract && count($this->objDestinationData) > 0){
      $arrDestination = $this->objDestinationData->current()->toArray();
      if(!is_null($this->strDBFRoot)) $this->intRootId = $arrDestination[$this->strDBFRoot];
      $this->lockTable();
      
      $this->moveSubtree($intNodeId, $arrDestination[$this->strDBFRgt] + 1, $arrDestination[$this->strDBFDepth]);
            
      $arrUpdateData = array($this->strDBFDepth => $arrDestination[$this->strDBFDepth]);
      if(!is_null($this->strDBFParent)) $arrUpdateData[$this->strDBFParent] = $arrDestination[$this->strDBFParent];   
    
      $this->updateNode($arrUpdateData);
      
      
      $this->unlockTable();
    }
  }
    
  /**
   * shiftLRValues   
   * @param integer $intFirst
   * @param integer $intDelta
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  private function shiftLRValues($intFirst, $intDelta){
    try{
      
      $strSqlAddonRootId = '';
      if(!is_null($this->strDBFRoot) && $this->intRootId > 0) $strSqlAddonRootId = $this->strDBFRoot.' = '.$this->intRootId.' AND ';
      
      $this->objTable->getAdapter()->query('UPDATE '.$this->objTable->info(Zend_Db_Table_Abstract::NAME).' SET '.$this->strDBFLft.' = '.$this->strDBFLft.' + '.$intDelta.' WHERE '.$strSqlAddonRootId.$this->strDBFLft.' >= '.$intFirst.';');
      $this->objTable->getAdapter()->query('UPDATE '.$this->objTable->info(Zend_Db_Table_Abstract::NAME).' SET '.$this->strDBFRgt.' = '.$this->strDBFRgt.' + '.$intDelta.' WHERE '.$strSqlAddonRootId.$this->strDBFRgt.' >= '.$intFirst.';');          
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }

  /**
   * shiftLRRange   
   * @param integer $intFirst
   * @param integer $intLast
   * @param integer $intDelta
   * @param integer $intDepthDiff
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  private function shiftLRRange($intFirst, $intLast, $intDelta, $intDepthDiff){
    try{
      
      $strSqlAddonRootId = '';
      if(!is_null($this->strDBFRoot) && $this->intRootId > 0) $strSqlAddonRootId = $this->strDBFRoot.' = '.$this->intRootId.' AND ';
      
      $this->objTable->getAdapter()->query('UPDATE '.$this->objTable->info(Zend_Db_Table_Abstract::NAME).' SET '.$this->strDBFLft.' = '.$this->strDBFLft.' + '.$intDelta.' WHERE '.$strSqlAddonRootId.$this->strDBFLft.' >= '.$intFirst.' AND '.$strSqlAddonRootId.$this->strDBFLft.' <= '.$intLast.';');
      $this->objTable->getAdapter()->query('UPDATE '.$this->objTable->info(Zend_Db_Table_Abstract::NAME).' SET '.$this->strDBFRgt.' = '.$this->strDBFRgt.' + '.$intDelta.' WHERE '.$strSqlAddonRootId.$this->strDBFRgt.' >= '.$intFirst.' AND '.$strSqlAddonRootId.$this->strDBFRgt.' <= '.$intLast.';');          
      $this->objTable->getAdapter()->query('UPDATE '.$this->objTable->info(Zend_Db_Table_Abstract::NAME).' SET '.$this->strDBFDepth.' = '.$this->strDBFDepth.' + '.$intDepthDiff.' WHERE '.$strSqlAddonRootId.$this->strDBFLft.' BETWEEN '.($intFirst + $intDelta).' AND '.($intLast + $intDelta).';');
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }    
  }
  
  /**
   * moveSubtree   
   * @param integer $intNodeId  
   * @param integer $intDestination
   * @param integer $intDestinationDepth
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  private function moveSubtree($intNodeId, $intDestination, $intDestinationDepth){
    try{
      $this->intNodeId = $intNodeId;
      $this->loadNodeData();
      
      if($this->objNodeData instanceof Zend_Db_Table_Rowset_Abstract && count($this->objNodeData) > 0){
        $arrNode = $this->objNodeData->current()->toArray();
        
        $intTreeSize = $arrNode[$this->strDBFRgt] - $arrNode[$this->strDBFLft] + 1;
        $this->shiftLRValues($intDestination, $intTreeSize);
        
        if($arrNode[$this->strDBFLft] >= $intDestination){
          $arrNode[$this->strDBFLft] += $intTreeSize;
          $arrNode[$this->strDBFRgt] += $intTreeSize;
        }
        
        $intDepthDiff = $intDestinationDepth - $arrNode[$this->strDBFDepth];
        $this->shiftLRRange($arrNode[$this->strDBFLft], $arrNode[$this->strDBFRgt], $intDestination - $arrNode[$this->strDBFLft], $intDepthDiff);
        
        $this->shiftLRValues($arrNode[$this->strDBFRgt] + 1, - $intTreeSize);
      }
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }    
  }
  
  /**
   * insertNode
   * @param array $arrData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  private function insertNode($arrData){
    try{
      /**
       * add nested set fields and data
       */
      $arrData[$this->strDBFLft] = $this->intLft;
      $arrData[$this->strDBFRgt] = $this->intRgt;
      $arrData[$this->strDBFDepth] = $this->intDepth;
      if(!is_null($this->strDBFParent)) $arrData[$this->strDBFParent] = $this->intParentId;
      if(!is_null($this->strDBFRoot)) $arrData[$this->strDBFRoot] = $this->intRootId;
    
      $this->intNodeId = $this->objTable->insert($arrData);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }    
  }
  
  /**
   * updateNode  
   * @param array $arrUpdateData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  private function updateNode($arrUpdateData){
    try{
      $strWhere = $this->objTable->getAdapter()->quoteInto('id = ?', $this->intNodeId);
      $this->objTable->update($arrUpdateData, $strWhere);
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }    
  }
  
  /**
   * loadNodeData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  private function loadNodeData(){
    try{
      
      $objSelect = $this->objTable->select();   
      $objSelect->setIntegrityCheck(false);
      
      $arrFields = array($this->strDBFLft, $this->strDBFRgt, $this->strDBFDepth);
      
      if(!is_null($this->strDBFParent)) $arrFields[] = $this->strDBFParent;
      if(!is_null($this->strDBFRoot)) $arrFields[] = $this->strDBFRoot;
      
      $objSelect->from($this->objTable->info(Zend_Db_Table_Abstract::NAME), $arrFields);    
      $objSelect->where('id = ?', $this->intNodeId);
   
      $this->objNodeData = $this->objTable->fetchAll($objSelect);
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }    
  }
  
  /**
   * loadParentData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  private function loadParentData(){
    try{
      
      $objSelect = $this->objTable->select();   
      $objSelect->setIntegrityCheck(false);
      
      $arrFields = array($this->strDBFLft, $this->strDBFRgt, $this->strDBFDepth);
      
      if(!is_null($this->strDBFParent)) $arrFields[] = $this->strDBFParent;
      if(!is_null($this->strDBFRoot)) $arrFields[] = $this->strDBFRoot;
      
      $objSelect->from($this->objTable->info(Zend_Db_Table_Abstract::NAME), $arrFields);    
      $objSelect->where('id = ?', $this->intParentId);
   
      $this->objParentData = $this->objTable->fetchAll($objSelect);
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }    
  }
  
  /**
   * loadDestinationData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  private function loadDestinationData(){
    try{
      
      $objSelect = $this->objTable->select();   
      $objSelect->setIntegrityCheck(false);
      
      $arrFields = array($this->strDBFLft, $this->strDBFRgt, $this->strDBFDepth);
      
      if(!is_null($this->strDBFParent)) $arrFields[] = $this->strDBFParent;
      if(!is_null($this->strDBFRoot)) $arrFields[] = $this->strDBFRoot;
      
      $objSelect->from($this->objTable->info(Zend_Db_Table_Abstract::NAME), $arrFields);    
      $objSelect->where('id = ?', $this->intDestinationId);
   
      $this->objDestinationData = $this->objTable->fetchAll($objSelect);
      
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }    
  }
  
  /**
   * lockTable   
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function lockTable(){
    try{     
      $this->objTable->getAdapter()->query('LOCK TABLES '.$this->objTable->info(Zend_Db_Table_Abstract::NAME).' WRITE;');
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    }
  }
  
  /**
   * unlockTable   
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0 
   */
  public function unlockTable(){   
    try{     
      $this->objTable->getAdapter()->query('UNLOCK TABLES;');
    }catch (Exception $exc) {
      $this->core->logger->err($exc);
    } 
  } 
  
  /**
   * setDBFParent
   * @param string $strDBFRoot
   */
  public function setDBFParent($strDBFRoot){
    $this->strDBFParent = $strDBFRoot;  
  }
  
  /**
   * setDBFRoot
   * @param string $strDBFRoot
   */
  public function setDBFRoot($strDBFRoot){
    $this->strDBFRoot = $strDBFRoot;  
  }
}
?>