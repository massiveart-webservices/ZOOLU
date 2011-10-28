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
 * @package    application.website.default.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * SweepstakeController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-03-30: Cornelius Hansjakob

 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

require_once(GLOBAL_ROOT_PATH.'/library/IP2Location/ip2location.class.php');

class SweepstakeController extends Zend_Controller_Action {
  
  /**
   * @var Core
   */
  private $core;   

  /**
   * @var integer
   */
  private $intLanguageId;

  /**
   * @var string
   */
  private $strLanguageCode;

  /**
   * @var HtmlTranslate
   */
  private $translate;
  
  /**
   * @var Zend_Session_Namespace
   */
  public $objSweepstakeSession;
  
  /**
   * @var Model_SweepstakeCodes
   */
  protected $objModelSweepstakeCodes;
  
  /**
   * @var Zend_Config_Xml
   */
  protected $sweepstakeConfig;
  
  protected $intSweepstakeId = 0;
  
  protected $strBasePath;
  protected $strSweepstakeFile;
  protected $strFormFile;
  
  protected $arrCurrSweepstake = array();
  
  protected $arrFormData = array();
  protected $arrMailRecipients = array();
  
  private $arrFormFields = array();
  
  protected $blnOnlySweepstake = false;
  protected $intRootLevelId = 0;
  protected $strLanguage = '';
  protected $intSweepstakeCounter = 0;
  
  /**
   * init index controller and get core obj
   */
  public function init(){
    $this->core = Zend_Registry::get('Core');
    $this->sweepstakeConfig = new Zend_Config_Xml(GLOBAL_ROOT_PATH.'/sys_config/sweepstakes.xml', APPLICATION_ENV);
  }  

  /**
	 * indexAction
	 * @author Cornelius Hansjakob <cha@massiveart.com>
	 * @version 1.0
	 */
  public function indexAction(){ 
    $this->_helper->viewRenderer->setNoRender();    
  }

  /**
   * checkAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function checkAction(){    
    $blnShowSweepstake = true;
    
    $intRootLevelId = (($this->_hasParam('rootLevelId')) ? $this->getRequest()->getParam('rootLevelId') : $_SESSION['Sweepstake']['rootLevelId']);
    $strIPAddress = (($this->_hasParam('ipaddress')) ? $this->getRequest()->getParam('ipaddress') : '');
    $strLanguage = (($this->_hasParam('language')) ? $this->getRequest()->getParam('language') : '');
    $intSweepstakeCounter = (($this->_hasParam('counter')) ? $this->getRequest()->getParam('counter') : $_SESSION['Sweepstake']['sweepstakeCounter']);
        
    $arrSweepstakes = array();
    $arrSweepstakes = $this->sweepstakeConfig->sweepstakes->sweepstake->toArray();
    
    if(count($arrSweepstakes) > 0){
      //echo '<!--IP: #'.$strIPAddress.'#-->';
      $strCountryShort = $this->getCountryShortByIP($strIPAddress);
      $_SESSION['Sweepstake']['strCountryShort'] = $strCountryShort;      
      //echo '<!--COUNTRYCODE: #'.$_SESSION['Sweepstake']['strCountryShort'].'#-->';
      
      foreach($arrSweepstakes as $mxKey => $mxSweepstake){         
        if(is_array($mxSweepstake) && array_key_exists('countries', $mxSweepstake)){
          foreach($mxSweepstake['countries'] as $key => $arrCountry){
            if(trim(strtoupper($mxSweepstake['countries'][$key]['code'])) === trim(strtoupper($strCountryShort))){                
              $this->intSweepstakeId = $mxSweepstake['id'];
              // write id to session
              $_SESSION['Sweepstake']['intSweepstakeId'] = $this->intSweepstakeId;  
              if(array_key_exists('language', $mxSweepstake['countries'][$key])){                  
                $this->strLanguageCode = $mxSweepstake['countries'][$key]['language'];  
              }
              if($strLanguage != ''){
                $this->strLanguageCode = $strLanguage;   
              }            
              $this->arrCurrSweepstake = $mxSweepstake;
              $_SESSION['Sweepstake']['arrCurrSweepstake'] = $this->arrCurrSweepstake;
              $_SESSION['Sweepstake']['strLanguageCode'] = $this->strLanguageCode;                              
            }    
          }  
        }else if($mxKey == 'countries'){
          foreach($arrSweepstakes['countries'] as $key => $arrCountry){
            if(trim(strtoupper($arrSweepstakes['countries'][$key]['code'])) === trim(strtoupper($strCountryShort))){                
              $this->intSweepstakeId = $arrSweepstakes['id'];
              // write id to session
              $_SESSION['Sweepstake']['intSweepstakeId'] = $this->intSweepstakeId;  
              if(array_key_exists('language', $arrSweepstakes['countries'][$key])){                  
                $this->strLanguageCode = $arrSweepstakes['countries'][$key]['language'];  
              }
              if($strLanguage != ''){
                $this->strLanguageCode = $strLanguage;   
              }            
              $this->arrCurrSweepstake = $arrSweepstakes;
              $_SESSION['Sweepstake']['arrCurrSweepstake'] = $this->arrCurrSweepstake;
              $_SESSION['Sweepstake']['strLanguageCode'] = $this->strLanguageCode;                              
            }    
          }  
        }
      }      
      
      $blnShowPopup = false;      
      if($this->intSweepstakeId > 0 && count($this->arrCurrSweepstake) > 0){          
        $this->strBasePath = $this->arrCurrSweepstake['path'];
        $this->strSweepstakeFile = $this->arrCurrSweepstake['files']['sweepstake'];
        $this->strFormFile = $this->arrCurrSweepstake['files']['form'];
        // write path and file to session
        $_SESSION['Sweepstake']['strBasePath'] = $this->strBasePath;          
        $_SESSION['Sweepstake']['strFormFile'] = $this->strFormFile;
        
        /**
         * check if portals exists and if the sweepstake should appear in the current portal
         */
        if(array_key_exists('portals', $this->arrCurrSweepstake)){
          $arrPortals = array();
          $arrPortals = $this->arrCurrSweepstake['portals'];            
          if(array_search($intRootLevelId, $arrPortals) === false){
            $blnShowSweepstake = false;
          }
        }
        
        /**
         * check if period exists and if the sweepstake should appear
         */
        if(array_key_exists('period', $this->arrCurrSweepstake)){
          $arrPeriod = array();
          $arrPeriod = $this->arrCurrSweepstake['period'];            
          $intCurrTime = time();
          
          $intStart = 0;
          if(array_key_exists('start', $arrPeriod)){
            $intStart = strtotime($arrPeriod['start']);  
          }
          $intEnd = 0;
          if(array_key_exists('end', $arrPeriod)){
            $intEnd = strtotime($arrPeriod['end']);  
          }
          
          if((($intStart > 0 && $intEnd > 0) && ($intCurrTime >= $intStart && $intCurrTime <= $intEnd)) || (($intStart > 0 && $intEnd == 0) && $intCurrTime >= $intStart) || (($intEnd > 0 && $intStart == 0) && $intCurrTime <= $intEnd)) {
            // do nothing
          }else{
            $blnShowSweepstake = false;
          }
        }
        
        /**
         * check if mode is live/test
         */
        if(!isset($_SESSION['sesTestMode']) || (isset($_SESSION['sesTestMode']) && $_SESSION['sesTestMode'] == false)){
          if(array_key_exists('mode', $this->arrCurrSweepstake)){
            if($this->arrCurrSweepstake['mode'] == 'test'){
              $blnShowSweepstake = false; 
            } 
          }
        }
        
        /**
         * check appearance counter
         */
        if(array_key_exists('appear_counter', $this->arrCurrSweepstake)){
          if($this->arrCurrSweepstake['appear_counter'] <= $intSweepstakeCounter){
            $blnShowSweepstake = false; 
          } 
        }
        
        /**
         * set up translate obj for sweepstake
         */
        if(isset($this->strLanguageCode) && $this->strLanguageCode != ''){            
          if(file_exists(GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$this->strLanguageCode.'.mo')){
            $this->translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$this->strLanguageCode.'.mo');
          }
          $this->view->assign('language', $this->strLanguageCode);
          $this->view->assign('translate', $this->translate);  
        }

        $this->view->assign('pageTitle', $this->arrCurrSweepstake['title']);
        $this->view->assign('basePath', $this->strBasePath);
        $this->view->assign('file', $this->strSweepstakeFile);
        $this->view->assign('hasAppearCounter', ((array_key_exists('appear_counter', $this->arrCurrSweepstake)) ? true : false));
        
        if($_SESSION['Sweepstake']['onlySweepstake'] != null){
          $this->blnOnlySweepstake = $_SESSION['Sweepstake']['onlySweepstake'];
        }else{
          /**
           * check view type
           */
          if(array_key_exists('popup', $this->arrCurrSweepstake)){
            if($this->arrCurrSweepstake['popup'] != true || $this->arrCurrSweepstake['popup'] != 'true'){
              $blnShowSweepstake = false;  
            }
          }
        }
        $this->view->assign('onlySweepstake', $this->blnOnlySweepstake);
        $_SESSION['Sweepstake']['onlySweepstake'] = null;
      }else{
        $blnShowSweepstake = false;
      }
    }else{
      $blnShowSweepstake = false;
    }
    
    // PHP session id
    $this->view->assign('PHPSESSID', session_id());

    $this->view->blnSweepstake = true; 
    
    // set blnSweepstake to false if sweepstake 
    if(!$blnShowSweepstake){      
      $_SESSION['Sweepstake']['onlySweepstake'] = null;
      $this->view->blnSweepstake = false;
    }
  }
  
  /**
   * formAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function formAction(){
    if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {      
      
      $this->view->assign('basePath', $_SESSION['Sweepstake']['strBasePath']);
      $this->view->assign('form', $_SESSION['Sweepstake']['strFormFile']);
      
      $this->view->assign('intPromoMonza', $this->getRequest()->getParam('promoMonza'));
      $this->view->assign('strSloganLisbon', $this->getRequest()->getParam('sloganLisbon'));
      
      /**
       * set up translate obj for sweepstake
       */
      if(isset($_SESSION['Sweepstake']['strLanguageCode']) && $_SESSION['Sweepstake']['strLanguageCode'] != ''){            
        if(file_exists(GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$_SESSION['Sweepstake']['strLanguageCode'].'.mo')){
          $this->translate = new HtmlTranslate('gettext', GLOBAL_ROOT_PATH.'application/website/default/language/website-'.$_SESSION['Sweepstake']['strLanguageCode'].'.mo');
          
          $this->arrFormFields = array('salutation'         => $this->translate->_('Salutation'),
                                       'title'              => $this->translate->_('Title'),
                                       'fname'              => $this->translate->_('Firstname'),
                                       'sname'              => $this->translate->_('Surname'),
                                       'company'            => $this->translate->_('Company'),
                                       'email'              => $this->translate->_('Email'),
                                       'phone'              => $this->translate->_('Phone'),
                                       'fax'                => $this->translate->_('Fax'),
                                       'function'           => $this->translate->_('Function'),
                                       'type'               => $this->translate->_('Type_of_company'),
                                       'street'             => $this->translate->_('Street'),
                                       'zip'                => $this->translate->_('ZipCode'),
                                       'city'               => $this->translate->_('City'),
                                       'state'              => $this->translate->_('State'),
                                       'country'            => $this->translate->_('Country'),
                                       'checkLegalnotes'    => $this->translate->_('Check_Legalnotes'));
          
          $_SESSION['Sweepstake']['arrFormFields'] = $this->arrFormFields;
        }
        $this->view->assign('language', $_SESSION['Sweepstake']['strLanguageCode']);
        $this->view->assign('translate', $this->translate);
      }
    }  
  }
  
  /**
   * singlePageAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function singlePageAction(){
    $this->_helper->viewRenderer->setNoRender();   
    
    $_SESSION['Sweepstake']['rootLevelId'] = 1;
    $_SESSION['Sweepstake']['sweepstakeCounter'] = 0;
    $_SESSION['Sweepstake']['onlySweepstake'] = true;
     
    $this->_forward('check', 'Sweepstake');
  }
  
  /**
   * datareceiverAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function datareceiverAction(){
    if($this->getRequest()->isPost()){
      $this->arrFormData = $this->getRequest()->getPost();
      unset($this->arrFormData['_']); // safari fix

      if(count($this->arrFormData) > 0){
        /**
         * send mail
         */
        $this->sendMail();
      }
    }  
  }
  
  /**
   * sendMail
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function sendMail(){
    $arrMail = array();
    $arrFromMail = array();
    $arrFromTo = array();
    
    $mail = new Zend_Mail('utf-8');
    
    /**
     * config for SMTP with auth
     */
    $config = array('auth'     => 'login',
                    'username' => $this->core->config->mail->params->username,
                    'password' => $this->core->config->mail->params->password);
    
    /**
     * SMTP
     */
    $transport = new Zend_Mail_Transport_Smtp($this->core->config->mail->params->host, $config);
    
    /**
     * standard mail sender and recipients
     */
    if(array_key_exists('mail', $_SESSION['Sweepstake']['arrCurrSweepstake'])){      
      $arrMail = $_SESSION['Sweepstake']['arrCurrSweepstake']['mail'];
      
      $arrFromMail = ((array_key_exists('from', $arrMail)) ? $arrMail['from'] : array());
      $arrFromTo = ((array_key_exists('from', $arrMail)) ? $arrMail['to'] : array());

      $this->arrMailRecipients = array('Name'  => $arrFromTo['name'],
                                       'Email' => $arrFromTo['email']);
      
      if(array_key_exists('mail', $_SESSION['Sweepstake']['arrCurrSweepstake']['countries'][strtolower($_SESSION['Sweepstake']['strCountryShort'])])){
        $arrToMail = $_SESSION['Sweepstake']['arrCurrSweepstake']['countries'][strtolower($_SESSION['Sweepstake']['strCountryShort'])]['mail'];
        
        $this->arrMailRecipients = array('Name'  => $arrToMail['to']['name'],
                                         'Email' => $arrToMail['to']['email']);  
      }
    }
      
    $strHtmlBody = '';     
    if(count($this->arrFormData) > 0){
      $this->arrFormFields = $_SESSION['Sweepstake']['arrFormFields'];
      
      $strHtmlBody = '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
        <html>
        <head>
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
          <title></title>
          <style type="text/css">
            body { margin:0; padding:20px; color:#333333; width:100%; height:100%; font-size:12px; font-family:Arial, Sans-Serif; background-color:#ffffff; line-height:16px;}
            h1 { color:#333333; font-weight:bold; font-size:16px; font-family:Arial, Sans-Serif; padding:0; margin: 20px 0 15px 0; }
            h2 { color:#333333; font-weight:bold; font-size:14px; font-family:Arial, Sans-Serif; padding:0; margin: 20px 0 15px 0; }
            h3 { color:#333333; font-weight:bold; font-size:12px; font-family:Arial, Sans-Serif; padding:0; margin: 20px 0 15px 0; }
            a { color:#000; font-size:12px; text-decoration:underline; margin:0; padding:0; }
            a:hover { color:#000; font-size:12px; text-decoration:none; margin:0; padding:0; }
            p { margin:0 0 16px 0; padding:0;}
          </style>
        </head>
        <body>        
           <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                 <td>
                  <h1>'.$arrMail['title'].'</h1>';
      if($arrMail['intro'] != ''){
        $strHtmlBody .= '
                  <p>'.$arrMail['intro'].'</p>';
      }
      $strSpecialOutput = ''; 
      foreach($this->arrFormData as $key => $value){
        if($value != ''){         
          if($key == 'promoMonza'){
            $strHtmlBody .= '<strong>Tetric N-Ceram Intro Pack:</strong> '.$value.'<br/><br/>';    
          }else if($key == 'sloganLisbon'){
            $strHtmlBody .= '<strong>Tetric EvoFlow Slogan:</strong> '.$value.'<br/><br/>';   
          }else{
            $strHtmlBody .= '<strong>'.((array_key_exists($key, $this->arrFormFields)) ? $this->arrFormFields[$key] : ucfirst($key)).':</strong> '.$value.'<br/>';    
          }
        }          
      }   
      $strHtmlBody .= '
                </td>
              </tr>
           </table>
        </body>
        </html>';
    }

    /**
     * set mail subject
     */
    $mail->setSubject($arrMail['subject']);
    /**
     * set html body
     */
    $mail->setBodyHtml($strHtmlBody);
    /**
     * set default FROM address
     */
    $mail->setFrom($arrFromMail['email'], $arrFromMail['name']);
      
    if(count($this->arrMailRecipients) > 0){
      $mail->clearRecipients();
      $mail->addTo($this->arrMailRecipients['Email'], $this->arrMailRecipients['Name']);
      $this->core->logger->debug('Sweepstake e-mail to: '.$this->arrMailRecipients['Email'].'::'.$this->arrMailRecipients['Name']);

      /**
       * send mail if mail body is not empty
       */
      if($strHtmlBody != ''){
        $mail->send($transport);
      } 
    }
  } 
  
  /**
   * sessionAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function sessionAction(){
    $this->_helper->viewRenderer->setNoRender();
    
    if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
      $strValue = $this->getRequest()->getParam('value');
      $strSessionName = $this->getRequest()->getParam('session');
      $_SESSION['Sweepstake'][$strSessionName] = $strValue;
      echo 'true';
    }else{
      echo 'false';
    }
  }
  
  /**
   * checkCodeAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function checkCodeAction(){
    $this->_helper->viewRenderer->setNoRender();

    $arrReturn = array();   
    
    if($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {      
      $blnAllowCheck = true;
      
      $strSweepstakeCode = $this->getRequest()->getParam('code'); 
      $strSweepstakeCodeTypeKey = $this->getRequest()->getParam('sweepstakeType');
      $strSessionName = $this->getRequest()->getParam('session');
      
      if($strSweepstakeCode != ''){
        if($strSweepstakeCodeTypeKey != ''){
          
          // special case for n-cements sweepstake
          if($strSweepstakeCodeTypeKey == 'ncements'){
            // X7a00K3f56zQ to X7a02K3f55zQ - number must be between 00356 and 02355 
            $blnValidCode = (bool) preg_match('/X7a[0-9][0-9]K[0-9]f[0-9][0-9]zQ/', $strSweepstakeCode); 
            if($blnValidCode){
              $intSweepstakeCodeNr = str_replace(array('X7a', 'K', 'f', 'zQ'), '', $strSweepstakeCode);              
              if($intSweepstakeCodeNr >= '00356' && $intSweepstakeCodeNr <= '04355'){
                $blnAllowCheck = true;  
              }else{
                $this->core->logger->debug('application->website->default->controllers->SweepstakeController->checkCodeAction(): Code not valid - Nr.: '.$intSweepstakeCodeNr.'! ('.$strSweepstakeCode.')');
                $arrReturn = array('status' => 'not-ok', 'message' => 'The Code is not valid.');
                $blnAllowCheck = false;  
              }
            }else{
              $this->core->logger->debug('application->website->default->controllers->SweepstakeController->checkCodeAction(): Code not valid! ('.$strSweepstakeCode.')');
              $arrReturn = array('status' => 'not-ok', 'message' => 'The Code is not valid.');
              $blnAllowCheck = false;
            }
          }          
          
          if($blnAllowCheck){
            $objCodeData = $this->getModelSweepstakeCodes()->findCode($strSweepstakeCode, $strSweepstakeCodeTypeKey);
            if(count($objCodeData) > 0){
              $objCodeData = $objCodeData->current(); 
              if($objCodeData->code == $strSweepstakeCode){
                $this->core->logger->debug('application->website->default->controllers->SweepstakeController->checkCodeAction(): Code has already been used! ('.$strSweepstakeCode.')'); 
                $arrReturn = array('status' => 'not-ok', 'message' => 'The code has already been used.');
              }
            }else{
              // write code to session and add it to table in db
              $this->getModelSweepstakeCodes()->addCodeWithTypeKey($strSweepstakeCode, $strSweepstakeCodeTypeKey);
              $this->core->logger->debug('application->website->default->controllers->SweepstakeController->checkCodeAction(): Code inserted! ('.$strSweepstakeCode.')');
              // add code to session if sessionName is set
              if($strSessionName != ''){
                $_SESSION['Sweepstake'][$strSessionName] = $strSweepstakeCode;  
              }
              $arrReturn = array('status' => 'ok');
            }
          }
        }else{
          $this->core->logger->debug('application->website->default->controllers->SweepstakeController->checkCodeAction(): No sweepstake type defined!');
        }
      }else{
        $this->core->logger->debug('application->website->default->controllers->SweepstakeController->checkCodeAction(): Code is empty!');
        $arrReturn = array('status' => 'not-ok', 'message' => 'Code is empty.');
      }    
    }

    $this->_response->setBody(json_encode($arrReturn));
  }
  
  /**
   * getCountryShortByIP
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  private function getCountryShortByIP($strIPAddress = ''){
    if(file_exists(GLOBAL_ROOT_PATH.'library/IP2Location/IP-COUNTRY-REGION-CITY-LATITUDE-LONGITUDE.BIN')){      
      
      $ip = new ip2location;
      $ip->open(GLOBAL_ROOT_PATH.'library/IP2Location/IP-COUNTRY-REGION-CITY-LATITUDE-LONGITUDE.BIN');
      
      $ipAddress = ((strpos($_SERVER['HTTP_HOST'], 'area51') === false) ? $_SERVER['REMOTE_ADDR'] : '84.72.245.26');
      if($strIPAddress != ''){
        $ipAddress = $strIPAddress;
      }
      $countryShort = $ip->getCountryShort($ipAddress);
      $this->core->logger->debug('IP2Location->getCountryShort: ip - '.$ipAddress.' / '.$countryShort);
      
      return $countryShort;
    }
  }
  
  /**
   * getModelSweepstakeCodes
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  protected function getModelSweepstakeCodes(){
    if (null === $this->objModelSweepstakeCodes) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'core/models/SweepstakeCodes.php';
      $this->objModelSweepstakeCodes = new Model_SweepstakeCodes();
    }

    return $this->objModelSweepstakeCodes;
  }
 
}
?>