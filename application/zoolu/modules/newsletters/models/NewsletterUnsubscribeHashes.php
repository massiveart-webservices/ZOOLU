<?php
/**
 * ZOOLU - Content Management System
 * Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
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
 * @package    application.zoolu.modules.newsletters.models
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Model_Newsletters
 *
 * @author Christian Durak <cdu@massiveart.com>
 * @version 1.0
 */

class Model_NewsletterUnsubscribeHashes {

  /**
   * @var Core
   */
  private $core;
  
  private $objNewsletterHashesTable;

  /**
   * Constructor
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function __construct(){
    $this->core = Zend_Registry::get('Core');
  }
  
  /**
   * load
   * @param integer $intElementId
   * @return Zend_Db_Table_Rowset_Abstract Newsletter
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function load($intElementId){
    $this->core->logger->debug('newsletters->models->Model_NewsletterUnsubscribeHashes->load('.$intElementId.')');

    $objSelect = $this->getNewsletterUnsubcribeHashesTable()->select();
    $objSelect->setIntegrityCheck(false);

    $objSelect->from('newsletterUnsubscribeHashes')
              ->where('id = ?', $intElementId);
    
    return $this->getNewsletterUnsubcribeHashesTable()->fetchAll($objSelect);
  }
  
  /**
   * load
   * @param integer $intElementId
   * @return Zend_Db_Table_Rowset_Abstract Newsletter
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadBySubscriberId($subscriberId){
      $this->core->logger->debug('newsletters->models->Model_NewsletterUnsubscribeHashes->loadBySubscriberId('.$subscriberId.')');
  
      $objSelect = $this->getNewsletterUnsubcribeHashesTable()->select();
      $objSelect->setIntegrityCheck(false);
  
      $objSelect->from('newsletterUnsubscribeHashes')
      ->where('idSubscriber = ?', $subscriberId);
  
      return $this->getNewsletterUnsubcribeHashesTable()->fetchAll($objSelect);
  }
  
  /**
   * load
   * @param integer $intElementId
   * @return Zend_Db_Table_Rowset_Abstract Newsletter
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function loadByHash($hash){
      $this->core->logger->debug('newsletters->models->Model_NewsletterUnsubscribeHashes->loadByHash('.$hash.')');
  
      $objSelect = $this->getNewsletterUnsubcribeHashesTable()->select();
      $objSelect->setIntegrityCheck(false);
  
      $objSelect->from('newsletterUnsubscribeHashes')
      ->where('hash = ?', $hash);
  
      return $this->getNewsletterUnsubcribeHashesTable()->fetchAll($objSelect);
  }
  
  /**
   * update
   * @param GenericSetup $objGenericSetup
   * @param Array $arrData
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function update(GenericSetup &$objGenericSetup, $arrData){
    $this->core->logger->debug('newsletters->models->Model_Newsletters->update()');

    $objAuth = Zend_Auth::getInstance();
    $objAuth->setStorage(new Zend_Auth_Storage_Session('Zoolu'));
    $intUserId = $objAuth->getIdentity()->id;

    $strWhere = $this->getNewsletterTable()->getAdapter()->quoteInto('id = ?', $objGenericSetup->getElementId());

    $arrData = array_merge(
      $arrData,
      array(
        'idUsers'  => $intUserId,
        'changed' => date('Y-m-d H:i:s')
      )
    );
    
    return $this->getNewsletterUnsubcribeHashesTable()->update($arrData, $strWhere);
  }

  /**
   * delete
   * @param integer $intElementId
   * @return the number of rows deleted
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function delete($intElementId){
    $this->core->logger->debug('newsletters->models->Model_Newsletters->delete()');
    $strWhere = $this->getNewsletterTable()->getAdapter()->quoteInto('id = ?', $intElementId);
    return $this->objNewsletterTable->delete($strWhere);
  }
  
  /**
   * getNewsletterTable
   * @return Zend_Db_Table_Abstract
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  public function getNewsletterUnsubcribeHashesTable(){
  
      if($this->objNewsletterHashesTable === null) {
          require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'newsletters/models/tables/NewsletterUnsubscribeHashes.php';
          $this->objNewsletterHashesTable = new Model_Table_NewsletterUnsubscribeHashes();
      }
  
      return $this->objNewsletterHashesTable;
  }

}