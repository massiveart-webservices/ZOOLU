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
 * @package    application.zoolu.modules.global.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Webhooks_ContactsController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-01-05: Thomas Schedler
 *
 * @author Thomas Schedler <ths@massiveart.com>
 * @version 1.0
 */

class Webhooks_ContactsController extends Zend_Controller_Action {

  /**
   * @var Core
   */
  protected $core;

  /**
   * @var CommandChain
   */
  protected $objCommandChain;
  
  /**
   * @var Model_Subscribers
   */
  public $objModelSubscribers;
  
  /**
   * init
   * @author Thomas Schedler <ths@massiveart.com>
   */
  public function init(){
    $this->core = Zend_Registry::get('Core');
    $this->initCommandChain();
  }
  
  /**
   * init command chain
   * @author Thomas Schedler <tsh@massiveart.com>
   * @return void
   */
  private function initCommandChain(){
    $this->core->logger->debug('webhooks.contacts.initCommandChain()');
    $this->objCommandChain = new CommandChain();
    $this->objCommandChain->addCommand(new ContactReplicationCommand());
  }
  
  /**
   * subscriberAction
   * @author Thomas Schedler <ths@massiveart.com>
   */
  public function subscriberAction(){
    $this->core->logger->debug('webhooks.contacts.subscriber()');
    
    $this->_helper->viewRenderer->setNoRender();
    
    $strMethod = $this->getRequest()->getParam('type').'Subscriber';
    if(method_exists($this, $strMethod)) {
      $this->$strMethod($this->getRequest()->getParam('data', array()));
    }    
  }
  
  /**
   * subscription called by webhook
   * @param array $arrWebHookData
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  public function subscribeSubscriber($arrWebHookData) {
    $this->core->logger->debug('webhooks.contacts.subscribeSubscriber()');
    
    $objSubscribers = $this->getModelSubscribers()->loadByEmail($arrWebHookData['email']);
      
    if(count($objSubscribers) > 0) {
      foreach($objSubscribers as $objSubscriber){
        $arrData = array(
          'idRootLevels'     => $this->core->sysConfig->subscriber->default->rootLevelId,
          'idGenericForms'   => $this->core->sysConfig->subscriber->default->genericFormId,
          'idUsers'          => $this->core->sysConfig->subscriber->default->userId,
          'creator'          => $this->core->sysConfig->subscriber->default->userId, 
          'created'          => date('Y-m-d H:i:s'),
          'fname'            => $arrWebHookData['merges']['FNAME'],
          'sname'            => $arrWebHookData['merges']['LNAME'],
          'subscribed'       => $this->core->sysConfig->mail_chimp->mappings->subscribe,
          'dirty'            => $this->core->sysConfig->mail_chimp->mappings->dirty
        );
    
        $this->getModelSubscribers()->update($objSubscriber->id, $arrData);
        
        $this->updateInterests($objSubscriber->id, $arrWebHookData['merges']['GROUPINGS']);
      }
    }else{
      $arrData = array(
        'idRootLevels'     => $this->core->sysConfig->subscriber->default->rootLevelId,
        'idGenericForms'   => $this->core->sysConfig->subscriber->default->genericFormId,
        'idUsers'          => $this->core->sysConfig->subscriber->default->userId,
        'creator'          => $this->core->sysConfig->subscriber->default->userId, 
        'created'          => date('Y-m-d H:i:s'),
        'email'            => $arrWebHookData['email'],
        'fname'            => $arrWebHookData['merges']['FNAME'],
        'sname'            => $arrWebHookData['merges']['LNAME'],
        'subscribed'       => $this->core->sysConfig->mail_chimp->mappings->subscribe,
        'dirty'            => $this->core->sysConfig->mail_chimp->mappings->dirty
      );
      
      $intSubscriberId = $this->getModelSubscribers()->add($arrData);
      
      $this->updateInterests($intSubscriberId, $arrWebHookData['merges']['GROUPINGS']);
      
      $this->sendMail('subscribe', $arrWebHookData['email']);
    }
  }
    
  /**
   * unsubscription called by webhook
   * @param array $arrWebHookData
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private function unsubscribeSubscriber($arrWebHookData) {
    $this->core->logger->debug('webhooks.contacts.unsubscribeSubscriber()');
    
    $objSubscribers = $this->getModelSubscribers()->loadByEmail($arrWebHookData['email']);
      
    if(count($objSubscribers) > 0) {
      foreach($objSubscribers as $objSubscriber){
        $this->getModelSubscribers()->update($objSubscriber->id, array(
          'subscribed' => $this->core->sysConfig->mail_chimp->mappings->unsubscribe,
          'dirty' => $this->core->sysConfig->mail_chimp->mappings->dirty));
      }
      $this->sendMail('unsubscribe', $arrWebHookData['email']);
    }
  }
  
  /**
   * update subscriber profile
   * @param array $arrWebHookData
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private function profileSubscriber($arrWebHookData) {
    $this->core->logger->debug('webhooks.contacts.profileSubscriber()');
    
    $objSubscribers = $this->getModelSubscribers()->loadByEmail($arrWebHookData['email']);
      
    if(count($objSubscribers) > 0) {
      foreach($objSubscribers as $objSubscriber){   
        
        $arrData = array(
          'idUsers' => $this->core->sysConfig->subscriber->default->userId,
          'fname'   => $arrWebHookData['merges']['FNAME'],
          'sname'   => $arrWebHookData['merges']['LNAME'],
        );
        
        $this->getModelSubscribers()->update($objSubscriber->id, $arrData);
        
        $this->updateInterests($objSubscriber->id, $arrWebHookData['merges']['GROUPINGS']);    
      }
    }
    
  }
  
  /**
   * email address changes called by webhook
   * @param array $arrWebHookData
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private function upemailSubscriber($arrWebHookData) {
    $this->core->logger->debug('webhooks.contacts.upemailSubscriber()');
    
    $objSubscribers = $this->getModelSubscribers()->loadByEmail($arrWebHookData['old_email']);
      
    if(count($objSubscribers) > 0) {
      foreach($objSubscribers as $objSubscriber){   
        
        $arrData = array(
          'idUsers' => $this->core->sysConfig->subscriber->default->userId,
          'email'   => $arrWebHookData['new_email']
        );
        
        $this->getModelSubscribers()->update($objSubscriber->id, $arrData);
      }
    }
  }
  
  /**
   * cleaned Email called by webhook
   * @param array $arrWebHookData
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private function cleanedSubscriber($arrWebHookData) {
    $this->core->logger->debug('webhooks.contacts.cleanedSubscriber()');
    
    $objSubscribers = $this->getModelSubscribers()->loadByEmail($arrWebHookData['email']);
    
    if(count($objSubscribers) > 0) {
      foreach($objSubscribers as $objSubscriber){
        $this->getModelSubscribers()->update($objSubscriber->id, array(
              'hardbounce' => $this->core->sysConfig->mail_chimp->mappings->hardbounce,
              'dirty' => $this->core->sysConfig->mail_chimp->mappings->dirty));
      }
    }
  }
  
  /**
   * get foreign ids 
   * @param integer $intSubscriberId
   * @param array $arrGroupings
   * @return array
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private function updateInterests($intSubscriberId, $arrGroupings){
    // FIXME ... make it more generic
    $arrInterests = array(); 
    if(count($arrGroupings) > 0){
      foreach($arrGroupings as $arrGrouping){
        switch(str_replace(' ', '_', $arrGrouping['name'])){
          case 'Interested_in':
            $arrInterests['interest_group'] = $this->getForeignIds(
              $arrGrouping['groups'], 
              'SELECT categories.id FROM categories INNER JOIN categoryTitles ON categoryTitles.idCategories = categories.id AND categoryTitles.idLanguages = 2, categories AS rootCat WHERE rootCat.id = 615 AND categories.idRootCategory = rootCat.idRootCategory AND categories.lft BETWEEN (rootCat.lft + 1) AND rootCat.rgt AND categoryTitles.title = %TITLE%'
            );
            break;            
          case 'Portal':
            $arrInterests['portal'] = $this->getForeignIds(
              $arrGrouping['groups'], 
              'SELECT rootLevels.id FROM rootLevels INNER JOIN rootLevelTitles ON rootLevelTitles.idRootLevels = rootLevels.id AND rootLevelTitles.idLanguages = 2 WHERE rootLevels.idRootLevelTypes = 1 AND rootLevels.active = 1 AND rootLevelTitles.title = %TITLE%'
            );
            break;
          case 'Language':
            $arrInterests['language'] = $this->getForeignIds(
              $arrGrouping['groups'],
              'SELECT categories.id FROM categories INNER JOIN categoryTitles ON categoryTitles.idCategories = categories.id AND categoryTitles.idLanguages = 2, categories AS rootCat WHERE rootCat.id = 634 AND categories.idRootCategory = rootCat.idRootCategory AND categories.lft BETWEEN (rootCat.lft + 1) AND rootCat.rgt AND categoryTitles.title = %TITLE%'
            );
        }
      }
    }
    $this->getModelSubscribers()->updateInterests($intSubscriberId, $arrInterests);
  }

  /**
   * get foreign ids 
   * @param string $strTitles
   * @param string $strSqlSelect
   * @return array
   * @author Thomas Schedler <tsh@massiveart.com>
   */
  private function getForeignIds($strTitles, $strSqlSelect){
    $arrIds = array();
    
    $arrTitles = explode(',', $strTitles);
    foreach($arrTitles as $strTitle) {
      
      $sqlSelect = str_replace('%TITLE%', '\''.trim($strTitle).'\'', $strSqlSelect);
      
      $objData = $this->core->dbh->query($sqlSelect)->fetchAll(Zend_Db::FETCH_OBJ);

      if(count($objData) > 0){
        foreach($objData as $objItem){
          $arrIds[] = $objItem->id;  
        }
      }
    }
    
    return $arrIds;
  }

  private function sendMail($strType, $strEmail){
    $mail = new Zend_Mail();
    
    $config = array('auth'     => 'login',
                    'username' => $this->core->config->mail->params->username,
                    'password' => $this->core->config->mail->params->password);
                    
    $transport = new Zend_Mail_Transport_Smtp($this->core->config->mail->params->host, $config);
    $strHtmlBody = '
      <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
      <html>
        <head>
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
          <title></title>
          <style type="text/css">
            body { margin:0; padding:20px; color:#333333; width:100%; height:100%; font-size:12px; font-family: Arial, Sans-Serif; background-color:#ffffff; line-height:16px;}
            span { line-height:15px; font-size:12px; }
            h1 { color:#333333; font-weight:bold; font-size:16px; font-family: Arial, Sans-Serif; padding:0; margin: 20px 0 15px 0; }
            h2 { color:#333333; font-weight:bold; font-size:14px; font-family: Arial, Sans-Serif; padding:0; margin: 20px 0 15px 0; }
            h3 { color:#333333; font-weight:bold; font-size:12px; font-family: Arial, Sans-Serif; padding:0; margin: 20px 0 15px 0; }
            a { color:#000000; font-size:12px; text-decoration:underline; margin:0; padding:0; }
            a:hover { color:#000000; font-size:12px; text-decoration:underline; margin:0; padding:0; }
            p { margin:0 0 10px 0; padding:0; }
          </style>
        </head>
        <body>
          <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
              <td>';
    switch($strType){
      case 'subscribe':
        $strHtmlBody .= $strEmail.' has subscribed to our Newsletter.';
        break;
      case 'unsubscribe':
        $strHtmlBody .= $strEmail.' has unsubscribed from our Newsletter';
        break;
    }                
    $strHtmlBody .=
              '</td>
            </tr>
          </table>
        </body>
      </html>';
      
    switch($strType){
      case 'subscribe':
        $mail->setSubject('Subscribe Message');
        break;
      case 'unsubscribe':
        $mail->setSubject('Unsubscribe Message');
        break;
    }
            
    $mail->setBodyHtml($strHtmlBody);
    
    $mail->setFrom($this->core->config->mail->from->address, $this->core->config->mail->from->name);
    
    $arrRecipient = $this->core->config->mail->recipient->toArray();
    $mail->addTo($arrRecipient['address'], $arrRecipient['name']);
    
    $mail->send($transport);
  }
  
  /**
   * getModelSubscribers
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelSubscribers(){
    if (null === $this->objModelSubscribers) {
      /**
       * autoload only handles "library" components.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'contacts/models/Subscribers.php';
      $this->objModelSubscribers = new Model_Subscribers();
    }

    return $this->objModelSubscribers;
  }
}