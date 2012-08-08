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
 * @package    application.website.default.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * DatareceiverController
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2008-04-20: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class DatareceiverController extends Zend_Controller_Action {

  /**
   * @var Core
   */
  private $core;
  
  /**
   * @var HtmlTranslate
   */
  private $translate;
  
  /**
   * @var Model_GenericData
   */
  protected $objModelGenericData;
  
  protected $arrFormData = array();
  protected $arrFormDataReplacer = array();
  protected $arrFileData = array();
  protected $arrMailRecipients = array();
  
  protected $strRedirectUrl;
  
  protected $strSenderName;
  protected $strSenderMail;
  protected $strReceiverName;
  protected $strReceiverMail;
  protected $strMailSubject = '';
  protected $strSuccessMessage = '';
  
  protected $strUploadPath;
  protected $strAttachmentFile = '';
  
  protected $strUserFName;
  protected $strUserSName;
  protected $strUserMail;
  
  private $arrFormFields = array();
  
  /**
   * init index controller and get core obj
   */
  public function init(){
    $this->core = Zend_Registry::get('Core');
  }
  
  /**
   * indexAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function indexAction(){
    $this->core->logger->debug('website->controllers->DatareceiverController->indexAction()');

    if($this->getRequest()->isPost()) {
      $this->arrMailRecipients = array('Name'  => $this->core->config->mail->recipient->name,
      																 'Email' => $this->core->config->mail->recipient->address);

      $this->arrFormData = $this->getRequest()->getPost();
              
      if(isset($_FILES)){
        $this->arrFileData = $_FILES;
      }

      // set up zoolu translate obj
      if(file_exists(GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$this->core->strLanguageCode.'.mo')){
        $this->translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$this->core->strLanguageCode.'.mo');
      }else{
        $this->translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$this->core->sysConfig->languages->default->code.'.mo');
      }

      if(count($this->arrFormData) > 0){  
        
        if(array_key_exists('country', $this->arrFormData)){
          $objSelect = $this->getModelGenericData()->getGenericTable('categories')->select();
          $objSelect->setIntegrityCheck(false);
          $objSelect->from('categories', array('id'));
          $objSelect->join('categoryTitles', 'categoryTitles.idCategories = categories.id AND categoryTitles.idLanguages = '.$this->core->intLanguageId, array('title'));
          $objSelect->where('categories.id = ?', $this->arrFormData['country']);
          $objResult = $this->getModelGenericData()->getGenericTable('categories')->fetchAll($objSelect);
          $objData = $objResult->current();
          $this->arrFormDataReplacer['country'] = $objData->title;
        }

        if($this->core->sysConfig->helpers->client->datareceiver === 'enabled') ClientHelper::get('Datareceiver')->receive($this->arrFormData, $this->arrFormDataReplacer);
        
        foreach($this->arrFormData as $strKey => $strValue) {
          if(strpos($strKey, 'addon_') !== false) {
            unset($this->arrFormData[$strKey]);
          }  
        }
        
        $this->arrFormFields = array('salutation'         => $this->translate->_('Salutation'),
                                     'title'              => $this->translate->_('Title'),
                                     'fname'              => $this->translate->_('Fname'),
                                     'sname'              => $this->translate->_('Sname'),
                                     'company'            => $this->translate->_('Company'),
                                     'email'              => $this->translate->_('Email'),
                                     'phone'              => $this->translate->_('Phone'),
                                     'fax'                => $this->translate->_('Fax'),
                                     'function'           => $this->translate->_('Function'),
                                     'type'               => $this->translate->_('Type'),
                                     'street'             => $this->translate->_('Street'),
                                     'zip'                => $this->translate->_('Zip'),
                                     'city'               => $this->translate->_('City'),
                                     'state'              => $this->translate->_('State'),
                                     'country'            => $this->translate->_('Country'),
                                     'message'            => $this->translate->_('Message'),
                                     'attachment'         => $this->translate->_('Attachment'),
                                     'checkLegalnotes'    => $this->translate->_('Check_Legalnotes'));

        // set sender name and e-mail
        if(array_key_exists('sender_name', $this->arrFormData) && array_key_exists('sender_mail', $this->arrFormData)){
          $this->strSenderName = Crypt::decrypt($this->core, $this->core->config->crypt->key, $this->arrFormData['sender_name']);
          $this->strSenderMail = Crypt::decrypt($this->core, $this->core->config->crypt->key, $this->arrFormData['sender_mail']);
          unset($this->arrFormData['sender_name']);
          unset($this->arrFormData['sender_mail']); 
        }
        
        // set receiver name and e-mail
        if(array_key_exists('receiver_name', $this->arrFormData) && array_key_exists('receiver_mail', $this->arrFormData)){
          $this->strReceiverName = Crypt::decrypt($this->core, $this->core->config->crypt->key, $this->arrFormData['receiver_name']);
          $this->strReceiverMail = Crypt::decrypt($this->core, $this->core->config->crypt->key, $this->arrFormData['receiver_mail']);
          
          $this->arrMailRecipients = array('Name'  => $this->strReceiverName,
                                           'Email' => $this->strReceiverMail);
          
          unset($this->arrFormData['receiver_name']);
          unset($this->arrFormData['receiver_mail']);   
        }

        // set e-mail subject
        if(array_key_exists('subject', $this->arrFormData)){
          $this->strMailSubject = $this->arrFormData['subject'];
        }
        
        // set redirect url
        if(array_key_exists('redirectUrl', $this->arrFormData)){
          $this->strRedirectUrl = $this->arrFormData['redirectUrl'];
          unset($this->arrFormData['redirectUrl']); 
        }

        // set success message mail
        if(array_key_exists('success_message_mail', $this->arrFormData)){
          $this->strSuccessMessage = Crypt::decrypt($this->core, $this->core->config->crypt->key, $this->arrFormData['success_message_mail']);
          unset($this->arrFormData['success_message_mail']); 
        }

        // unset captcha fields
        if(array_key_exists('recaptcha_response_field', $this->arrFormData)) unset($this->arrFormData['recaptcha_response_field']);
        if(array_key_exists('recaptcha_challenge_field', $this->arrFormData)) unset($this->arrFormData['recaptcha_challenge_field']);
        
        // send mail
        if($this->core->config->mail->actions->sendmail->client == 'true'){          
          $this->sendMail();  
        }

        // save to database
        if($this->core->config->mail->actions->database == 'true'){
          $this->insertDatabase();
        } 
        
        $strUrl = (strpos($this->strRedirectUrl,'?') !== false) ? $this->strRedirectUrl.'&send=true' : $this->strRedirectUrl.'?send=true';
  	    $this->_redirect($strUrl);
      }
    }
  }

  /**
   * sendMail
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function sendMail($blnSpecialForm = false){
    $this->core->logger->debug('website->controllers->DatareceiverController->sendMail()');
    $mail = new Zend_Mail('utf-8');
    
    $transport = null;
    if(!empty($this->core->config->mail->params->host)){
      //config for SMTP with auth
      $config = array('auth'     => 'login',
                      'username' => $this->core->config->mail->params->username,
                      'password' => $this->core->config->mail->params->password);
      
      // smtp
      $transport = new Zend_Mail_Transport_Smtp($this->core->config->mail->params->host, $config);  
    }

    $strHtmlBody = '';

    if(count($this->arrFormData) > 0){
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
                <td>
                  '.(!$blnSpecialForm ? $this->getEmailBody($this->arrFormData['blnDynForm'] == 'true') : $this->getEmailBodySpecialForm()).'
                </td>
              </tr>
            </table>
          </body>
        </html>';
    }

    // Adding Attachment to Mail
    if(count($this->arrFileData) > 0){
      foreach($this->arrFileData as $arrFile){        
        if($arrFile['name'] != ''){
          // upload file
          $strFile = $this->upload($arrFile); 
  
          // add file to mail
          $objFile = $mail->createAttachment(file_get_contents($strFile));
          $objFile->type        = $arrFile['type'];
          $objFile->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
          $objFile->encoding    = Zend_Mime::ENCODING_BASE64;         
          $objFile->filename    = $arrFile['name'];
        }            
      }
    }


    // set mail subject
    $mail->setSubject($this->strMailSubject);
    // set html body
    $mail->setBodyHtml($strHtmlBody);
    // set default FROM address
    $mail->setFrom($this->strSenderMail, $this->strSenderName);
		
    // set TO address
    if($this->arrFormData['blnDynForm'] == 'true'){
      foreach($this->arrFormData as $key => $value){
        if(preg_match('/_type$/', $key)){
          if($value == 'email'){
            $index = str_replace('_type', '', $key);
            $this->strUserMail = $this->arrFormData[$index];
          }elseif($value == 'fname'){
            $index = str_replace('_type', '', $key);
            $this->strUserFName = $this->arrFormData[$index];
          }elseif($value == 'sname'){
            $index = str_replace('_type', '', $key);
            $this->strUserSName = $this->arrFormData[$index];
          }
        }
      }
    }else{
      if(array_key_exists('email', $this->arrFormData)){
        if(array_key_exists('fname', $this->arrFormData)) $this->strUserFName = $this->arrFormData['fname'];
        if(array_key_exists('sname', $this->arrFormData)) $this->strUserSName = $this->arrFormData['sname'];
        $this->strUserMail = $this->arrFormData['email'];
      }
    }
    
    if(count($this->arrMailRecipients) > 0){
      $mail->clearRecipients();
      $mail->addTo($this->arrMailRecipients['Email'], $this->arrMailRecipients['Name']);
      //set header for sending mail
      $mail->addHeader('Sender', $this->core->config->mail->params->username);
      // send mail if mail body is not empty
      if($strHtmlBody != ''){
        $mail->send($transport);		 
      }	
      if($this->core->config->mail->actions->sendmail->confirmation == 'true'){
        $this->sendConfirmationMail();  
      }	
    }
  }

  /**
   * getEmailBody
   * @return string
   */
  private function getEmailBody($blnDynForm = false){
    $strHtmlBody = '';
      foreach($this->arrFormData as $key => $value){
        $arrKey = explode('_', $key);
        if($value != ''){
          if(is_array($value)){
            //Replace other in arrays
            if(is_array($value)){
              $nr = array_search('other', $value);
              if($nr !== false){
                $value[$nr] = $this->arrFormData[$arrKey[0].'_'.$arrKey[1].'_other'];
              }
            }
            $value = implode(', ', $value);
            //$value = substr($value, 0, strlen($value) - 2);
          }
       	  if($key == 'idRootLevels' || $key == 'idPage' || $key == 'subject' || $key == 'blnDynForm' || preg_match('/type$/', $key) || preg_match('/other$/', $key)){
       	    //Do nothing
       	  }else if($key == 'country' && array_key_exists('country', $this->arrFormDataReplacer)){
            $strHtmlBody .= '
                  <strong>'.$this->arrFormFields[$key].':</strong> '.$this->arrFormDataReplacer['country'].'<br/>';

       	  }else{
       	    if($blnDynForm){
       	      if($value == 'other'){
       	        $value = $this->arrFormData[$arrKey[0].'_'.$arrKey[1].'_other'];
       	      }
       	      $strHtmlBody .= '<strong>'.$arrKey[0].':</strong>'.$value.'<br />';
       	    }else{
              $strHtmlBody .= '
                  <strong>'.$this->arrFormFields[$key].':</strong> '.$value.'<br/>';
            }
          }
        }
      }

      return $strHtmlBody;
  }

  /**
   * getEmailBodySpecialForm
   * @return string
   */
  private function getEmailBodySpecialForm(){
    $strHtmlBody = '';
    
    return $strHtmlBody;
  }

  /**
   * sendConfirmationMail
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function sendConfirmationMail(){
    $this->core->logger->debug('website->controllers->DatareceiverController->sendConfirmationMail()');
      
    $mail = new Zend_Mail('utf-8');
    
    $transport = null;
    if(!empty($this->core->config->mail->params->host)){
      //config for SMTP with auth
      $config = array('auth'     => 'login',
                      'username' => $this->core->config->mail->params->username,
                      'password' => $this->core->config->mail->params->password);
      
      // smtp
      $transport = new Zend_Mail_Transport_Smtp($this->core->config->mail->params->host, $config);  
    }
    
    $strHtmlBody = '';
    
    if($this->strUserFName != '' && $this->strUserSName != ''){
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
        <table cellpadding="0" cellspacing="0" style="width:650px; margin:auto;">
           <tr>
              <td style="padding:20px 15px 20px 15px;">
                 <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                       <td>
                         <h2>'.$this->translate->_('Dear').' '.$this->strUserFName.' '.$this->strUserSName.'</h2>                         
                       </td>
                    </tr>
                    <tr>
                      <td>
                        '.($this->strSuccessMessage != '' ? $this->strSuccessMessage : $this->translate->_('Success_message')).'
                      </td>
                    </tr>
                 </table>
              </td>
           </tr>
        </table>
        </body>
        </html>';
    }
      
    // set mail subject
    $mail->setSubject($this->strMailSubject);
    // set default FROM address
    $mail->setFrom($this->strSenderMail, $this->strSenderName);
    // set html body
    $mail->setBodyHtml($strHtmlBody);
    // set default FROM address
    if($this->strUserMail != ''){
      $mail->clearRecipients();
      $mail->addTo($this->strUserMail, $this->strUserFName.' '.$this->strUserSName);
      //set header for sending mail
      $mail->addHeader('Sender', $this->core->config->mail->params->username);
      // send mail if mail body is not empty
      if($strHtmlBody != ''){
        $this->core->logger->debug('Send Confirmation Mail!');
        $mail->send($transport);
      }
    }
  }
  
  /**
   * insertDatabase
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function insertDatabase($strDB = ''){
    $this->core->logger->debug('website->controllers->DatareceiverController->insertDatabase()');

    if(count($this->arrFormData) > 0){
      if(isset($this->core->config->mail->database) && $this->core->config->mail->database != ''){        

        if($strDB != ''){
          $objGenTable = $this->getModelGenericData()->getGenericTable($strDB);
        }else{
          $objGenTable = $this->getModelGenericData()->getGenericTable($this->core->config->mail->database);
        }
      	
      	$arrTableData = array();
        $arrFormData = $this->arrFormData;
        //Delete unneeded files
        unset($arrFormData['receiver_mail']);
        unset($arrFormData['receiver_name']);
        unset($arrFormData['sender_mail']);
        unset($arrFormData['sender_name']);
        unset($arrFormData['subject']);
        unset($arrFormData['redirectUrl']);
        unset($arrFormData['blnDynForm']);
        
        $arrTableData['idPages'] = $arrFormData['idPage'];
        $arrTableData['idRootLevels'] = $arrFormData['idRootLevels'];
        $arrTableData['content'] = $this->convertFormData($arrFormData);

	      $objGenTable->insert($arrTableData);
      }
    }
  }

  /**
   * convertFormData
   * @return array
   * @version 1.0
   */
  private function convertFormData($arrFormData){
    $arrValues = array();
    foreach($arrFormData as $key => $value){
      if($key == 'idRootLevels' || $key == 'idPage' || preg_match('/test$/', $key) || preg_match('/other$/', $key)){
        //Do nothing
      }else{
        $key = explode('_', $key);
        $newkey = $key[0];
        if($value == 'other'){
          $arrValues[$newkey] = $arrFormData[$key[0].'_'.$key[1].'_other'];
        }else{
          $arrValues[$newkey] = $value;
        }
        //Replace other in arrays
        if(is_array($value)){
          $nr = array_search('other', $value);
          if($nr !== false){
            $arrValues[$newkey][$nr] = $arrFormData[$key[0].'_'.$key[1].'_other'];
          }
        }
      }
    }
    
    return json_encode($arrValues);
  }
  
  /**
   * checkRecaptchaAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function checkRecaptchaAction(){
    $this->core->logger->debug('website->controllers->DatareceiverController->checkRecaptchaAction()');
    $this->_helper->viewRenderer->setNoRender();
    
    $arrReturn = array();
    
    if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
      $strResponse = $this->getRequest()->getParam('recaptcha_response_field');
      $strChallenge = $this->getRequest()->getParam('recaptcha_challenge_field'); 
      
      $objReCaptchaService = new ReCaptchaService($this->core->sysConfig->recaptcha->keys->public, $this->core->sysConfig->recaptcha->keys->private);
          
      $objReCaptchaAdapter = new Zend_Captcha_ReCaptcha();
      $objReCaptchaAdapter->setService($objReCaptchaService);
          
      if(empty($strChallenge)){
        $arrReturn = array('status' => 'empty-challenge');  
      }
          
      if(empty($strResponse)){
        $arrReturn = array('status' => 'empty-response'); 
      }
      
      if(count($arrReturn) == 0){
        $arrCaptcha = array('recaptcha_challenge_field' => $strChallenge, 
                            'recaptcha_response_field'  => $strResponse);
            
        $result = $objReCaptchaService->verify($arrCaptcha['recaptcha_challenge_field'], $arrCaptcha['recaptcha_response_field']);
            
        if($result->getStatus() == 'true'){
          $arrReturn = array('status' => 'ok');
        }else{
          $arrReturn = array('status' => 'not-valid');  
        }  
      }
    }
    
    $this->getResponse()->setHeader('Content-Type', 'application/json')
                        ->setBody(json_encode($arrReturn));
  }

  /**
   * upload
   * @return string
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function upload($_FILEDATA){
    if ($_FILEDATA['error'] > 0) {
      $this->core->logger->err('website->controllers->DatareceiverController->upload(): '.$_FILEDATA['error']);
    }else{ 
      $objFile = new File();
      $objFile->setLanguageId($this->core->intLanguageId);
      $objFile->setUploadPath(GLOBAL_ROOT_PATH.$this->core->sysConfig->upload->forms->path->local->private);
      $this->strUploadPath = GLOBAL_ROOT_PATH.$this->core->sysConfig->upload->forms->path->local->private;
      $objFile->checkUploadPath(); 
      
      $arrFileInfo = array();
      $arrFileInfo = pathinfo($this->strUploadPath.$_FILEDATA['name']);
      $strFileName = $arrFileInfo['filename'];
      $strExtension = $arrFileInfo['extension'];

      $strFileName = $objFile->makeFileIdConform($strFileName);
      $strFile = $strFileName.'_'.uniqid().'.'.$strExtension;
      
      if(file_exists($this->strUploadPath.$strFile)) {
        $this->core->logger->err('website->controllers->DatareceiverController->upload(): '.$strFile.' already exists.');
      }else{
        move_uploaded_file($_FILEDATA['tmp_name'], $this->strUploadPath.$strFile);
        $this->strAttachmentFile = $strFile;         
        return $this->strUploadPath.$strFile;
      }
    }
  }
  
  /**
   * getModelGenericData
   * @return Model_GenericData
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelGenericData(){
    if (null === $this->objModelGenericData) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/GenericData.php';
      $this->objModelGenericData = new Model_GenericData();
    }
    return $this->objModelGenericData;
  }
}
?>