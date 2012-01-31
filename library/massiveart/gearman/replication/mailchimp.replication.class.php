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
 * @package    library.massiveart.command
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ContactReplication_MailChimp
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-11-29: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.gearman.replication
 * @subpackage GearmanReplicationMailChimp
 */

// MailChimp API Class v1.3
require_once(GLOBAL_ROOT_PATH.'library/MailChimp/MCAPI.class.php');

// ZOOLU MailChimp integration
require_once(GLOBAL_ROOT_PATH.'library/massiveart/newsletter/mailchimp/MailChimpConfig.php');
require_once(GLOBAL_ROOT_PATH.'library/massiveart/newsletter/mailchimp/MailChimpList.php');
require_once(GLOBAL_ROOT_PATH.'library/massiveart/newsletter/mailchimp/MailChimpMember.php');

class GearmanReplicationMailChimp {
  
  /**
   * @var Core
   */
  private static $core;
  
  /**
   * @var MailChimpConfig
   */
  private static $objMailChimpConfig;
  
  private static $job;
  private static $workload;
  
  private static $exceptions = array();
  
  /**
   * init
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private static function init($job){    
       
    self::$job = $job;
    self::$workload = unserialize($job->workload());
    
    if(empty(self::$core)){
      self::$core = Zend_Registry::get('Core');
    }
    
    if(empty(self::$objMailChimpConfig)){
      self::$objMailChimpConfig = new MailChimpConfig();
      self::$objMailChimpConfig->setApiKey(self::$core->sysConfig->mail_chimp->api_key)
                               ->setListId(self::$core->sysConfig->mail_chimp->list_id);
    }
  }
  
  /**
   * handleException
   * @param Exception exc
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private static function handleException(Exception $exc){      
    if($exc->getCode() == MailChimpList::API_ERROR_CODE_TIMEOUT){   
      if(self::$workload->retry >= 0){
        self::$workload->retry--;
        
        echo date('Y-m-d H:i:s')." WARNING - retry\n";
        sleep((3 - self::$workload->retry) * 5);
        
        $client= new GearmanClient();
        $client->addServer();
        $client->doHighBackground(self::$job->functionName(), serialize(self::$workload));
      }else{
        self::$exceptions[] = $exc;
        //self::sendExceptionMail($exc);
      }      
    }else{
      self::$exceptions[] = $exc;
      //self::sendExceptionMail($exc);
    }
  }
  
  /**
   * handleException
   * @param Exception exc
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private static function sendExceptionMail(Exception $exc){
    
    $mail = new Zend_Mail('utf-8');
    
    $mail->setSubject('MailChimp EXCEPTION '.$exc->getCode());
    $mail->setBodyHtml(nl2br($exc->getMessage().'<pre>'.var_export(self::$workload->args, true).'</pre>'));
    
    $mail->setFrom(self::$core->config->mail->from->address, self::$core->config->mail->from->name);
    
    $mail->addTo(self::$core->config->mail->ma_recipient->address, self::$core->config->mail->ma_recipient->name);
    
    //set header for sending mail
    $mail->addHeader('Sender', 'websitemail@zoolucms.com');
    
    $mail->send();
    
    echo date('Y-m-d H:i:s')." EXCEPTION ".$exc->getCode()."\n";
    echo $exc->getMessage()."\n";
  }
  
  /**
   * add contact
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public static function add($job) {
    
    self::init($job);
        
    try{
      //Only subscribe if the flag is set
      if(self::$workload->args['Subscribed'] == self::$core->sysConfig->mail_chimp->mappings->subscribe){
        $objMailChimpList = new MailChimpList(self::$objMailChimpConfig);
        $objMailChimpList->subscribe(new MailChimpMember(self::$workload->args));
      }
      
      echo date('Y-m-d H:i:s')." INFO - added\n";
    }catch(SubscriberException $se){
      self::handleException($se);
    }catch(MailChimpException $mce){
      self::handleException($mce);
    }
  }
  
  /**
   * update contact
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public static function update($job) {
    
    self::init($job);
        
    try{
      $objMailChimpList = new MailChimpList(self::$objMailChimpConfig);
      $blnSubscribe = (self::$workload->args['Subscribed'] == self::$core->sysConfig->mail_chimp->mappings->subscribe);
      $objMember = new MailChimpMember(self::$workload->args);
      $objMailChimpList->update($objMember, $blnSubscribe);
      
      echo date('Y-m-d H:i:s')." INFO - updated\n";
    }catch(SubscriberException $se){
      self::handleException($se);
    }catch(MailChimpException $mce){
      self::handleException($mce);
    }
  }
  
  /**
   * delete contact
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public static function delete($job) {
    
    self::init($job);    
    
    try{
      $objMailChimpList = new MailChimpList(self::$objMailChimpConfig);
      $objMailChimpList->unsubscribe(new MailChimpMember(self::$workload->args), true);
      
      echo date('Y-m-d H:i:s')." INFO - deleted\n";
    }catch(MailChimpException $mce){
      self::handleException($mce);
    }
  }
  
  /**
   * replication done
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public static function done($job){
        
    try{

      $data = unserialize($job->workload());
      echo json_encode($data);
      
      $email = $data->email;
      
      $mail = new Zend_Mail('utf-8');
      
      $mail->setSubject('MailChimp Import Done');
      
      echo json_encode(self::$exceptions);
      
      if(count($data->errors) == 0 && count($data->warnings) == 0){
        $mail->setBodyHtml('Dear ZOOLU-User,<br/>your last import was successfully transferred to MailChimp.<br/>RockOn, your ZOOLU team');
      }else{
        $strBody = 'Dear ZOOLU-User,<br/>your last import produced some errors:<br/>';
        //Add Errors and Warnings
        $strBody .= '<h2>Errors</h2>';
        $strBody .= '<ul>';
        foreach($data->errors as $error){
          $strBody .= '<li>'.$error.'</li>';
        }
        $strBody .= '</ul>';

        $strBody .= '<h2>Warnings</h2>';
        $strBody .= '<ul>';
        foreach($data->warnings as $warning){
          $strBody .= '<li>'.$warning.'</li>';
        }
        $strBody .= '</ul>';
        $strBody .= '<br/>RockOn, your ZOOLU team';
        $mail->setBodyHtml($strBody);
      }
      
      $mail->setFrom('noreply@zoolucms.com', 'Noreply');
      
      $mail->addTo($email);
      
      //set header for sending mail
      $mail->addHeader('Sender', 'websitemail@zoolucms.com');
      
      $mail->send();
    
      self::$exceptions = array();
      echo date('Y-m-d H:i:s')." INFO - done\n";
    }catch(Exception $mce){
      self::handleException($mce);
    }
  }
  
}