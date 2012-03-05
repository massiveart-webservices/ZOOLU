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
 * @package    library.massiveart.command
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * MailChimpList
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-06-09: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.contact.replication.MailChimp
 * @subpackage MailChimpList
 */

class MailChimpList {
  
  // MailChimp API Error codes
  const API_ERROR_CODE_MEMBER_ALREADY_SUBSCRIBED = 214;
  const API_ERROR_CODE_MEMBER_DOES_NOT_BELONG_TO_LIST = 215;
  const API_ERROR_CODE_MEMBER_NOT_EXIST = 232;
  const API_ERROR_CODE_TIMEOUT = -98;
  
  /**
   * @var MailChimpConfig
   */
  private $objConfig;
  
  /**
   * @var Core
   */
  private $core;
  
  /**
   * Constructor
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function __construct(MailChimpConfig $objConfig = null){
    $this->objConfig = $objConfig;
    $this->core = Zend_Registry::get('Core');
  }
  
  /**
   * subscribe member   
   * @param $objMember
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function subscribe(MailChimpMember $objMember) {
    
    $objMailChimpApi = new MCAPI($this->objConfig->getApiKey());
    
    $arrMergeVars = array(
      'FNAME'     => $objMember->getFirstName(), 
      'LNAME'     => $objMember->getLastName(),
      'SALUTATION'=> $objMember->getSalutation(),
      'GROUPINGS' => $this->prepareInterestGroups($objMember->getInterestGroups())
    );
         
    $objRetval = $objMailChimpApi->listSubscribe($this->objConfig->getListId(), $objMember->getEmail(), $arrMergeVars, 'html', false);
 
    if($objMailChimpApi->errorCode
      && $objMailChimpApi->errorCode != self::API_ERROR_CODE_MEMBER_ALREADY_SUBSCRIBED){
      require_once(dirname(__FILE__).'/MailChimpException.php');
      throw new MailChimpException("\n\tUnable to subscribe member!\n\tCode=".$objMailChimpApi->errorCode."\n\tMsg=".$objMailChimpApi->errorMessage."\n", $objMailChimpApi->errorCode);
    }
  }
  
  /**
   * update list member
   * @param $objMember
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function update(MailChimpMember $objMember, $blnSubscribed) {
    
    $objMailChimpApi = new MCAPI($this->objConfig->getApiKey());
    
    $arrMergeVars = array();
    if($objMember->getFirstName()) $arrMergeVars['FNAME'] = $objMember->getFirstName();
    if($objMember->getLastName()) $arrMergeVars['LNAME'] = $objMember->getLastName();
    if($objMember->getSalutation()) $arrMergeVars['SALUTATION'] = $objMember->getSalutation();
    if($this->prepareInterestGroups($objMember->getInterestGroups())) $arrMergeVars['GROUPINGS'] = $this->prepareInterestGroups($objMember->getInterestGroups());
    
    $blnReplaceInterest = $objMember->getReplaceInterests();
    
    if($blnSubscribed){
      $this->subscribe($objMember);
    }else{
      $this->unsubscribe($objMember);
    }
    
    if((count($arrMergeVars) > 0) && $blnSubscribed){
      $objRetval = $objMailChimpApi->listUpdateMember($this->objConfig->getListId(), $objMember->getEmail(), $arrMergeVars, 'html', $blnReplaceInterest);
 
      if($objMailChimpApi->errorCode){
        if(($objMailChimpApi->errorCode == self::API_ERROR_CODE_MEMBER_NOT_EXIST
          || $objMailChimpApi->errorCode == self::API_ERROR_CODE_MEMBER_DOES_NOT_BELONG_TO_LIST)
          && $blnSubscribed) {
          $this->subscribe($objMember);
        }else{
          require_once(dirname(__FILE__).'/MailChimpException.php');
          throw new MailChimpException("\n\tUnable to update member info!\n\tCode=".$objMailChimpApi->errorCode."\n\tMsg=".$objMailChimpApi->errorMessage."\n", $objMailChimpApi->errorCode);
        }
      }
    }
  }
  
  /**
   * unsubscribe member   
   * @param $objMember
   * @param $blnDeleteMember
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function unsubscribe(MailChimpMember $objMember, $blnDeleteMember = false) {
    
    $objMailChimpApi = new MCAPI($this->objConfig->getApiKey());
             
    $objRetval = $objMailChimpApi->listUnsubscribe($this->objConfig->getListId(), $objMember->getEmail(), $blnDeleteMember);
 
    if($objMailChimpApi->errorCode 
      && $objMailChimpApi->errorCode != self::API_ERROR_CODE_MEMBER_NOT_EXIST                 //Ignore message when user doesn't exist
      && $objMailChimpApi->errorCode != self::API_ERROR_CODE_MEMBER_DOES_NOT_BELONG_TO_LIST   //Ignore Message when user already unsubscribed
    ){ 
      require_once(dirname(__FILE__).'/MailChimpException.php');
      throw new MailChimpException("\n\tUnable to unsubscribe member!\n\tCode=".$objMailChimpApi->errorCode."\n\tMsg=".$objMailChimpApi->errorMessage."\n", $objMailChimpApi->errorCode);
    }
  }
  
  /**
   * prepare interest groups
   * @param Array $arrInterestGroups
   * @return Array
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private function prepareInterestGroups($arrInterestGroups) {    
    $arrGroups = array();
    
    if(count($arrInterestGroups) > 0){
      foreach($arrInterestGroups as $strGroup => $arrInterests) {      
        $arrInterestTitles = array();      
        foreach($arrInterests as $arrInterest) {
          $arrInterestTitles[] = $arrInterest['title'];
        }
        
        $arrGroups[] = array(
          'name'    => $strGroup, 
          'groups'  => implode(',', $arrInterestTitles)
        ); 
      }
    }
    
    return $arrGroups;
  }
  
  /**
   * set config object
   * @param MailChimpConfig $objConfig
   * @return MailChimpList
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function setConfig(MailChimpConfig $objConfig) {
    $this->objConfig = $objConfig;
    return $this;
  }
  
  /**
   * get mail chimp config object
   * @return MailChimpConfig
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function getConfig(){
    return $this->objConfig;
  }
  
}