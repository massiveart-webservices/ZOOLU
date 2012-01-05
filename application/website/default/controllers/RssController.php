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
 * RssController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-08-03: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */
class RssController extends Zend_Controller_Action {
  
  /**
   * @var Core
   */
  protected $core;
  
  /**
   * Model_Subscribers
   */
  protected $objModelSubscribers;
  
  /**
   * @var Zend_Db_Table_Row_Abstract
   */
  private $objTheme;
  
  /**
   * @var Page
   */
  private $objPage;
  
  /**
   * default render scirpt
   * @var string
   */
  protected $strRenderScript = 'rss.php';
  
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
   * init rss controller and get core obj
   */
  public function init(){
    $this->core = Zend_Registry::get('Core');
    
    $this->intLanguageId = $this->core->intLanguageId;
    $this->strLanguageCode = $this->core->strLanguageCode;
  }
  
  /**
   * preDispatch
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function preDispatch(){
    $this->core = Zend_Registry::get('Core');
    
    //Set Language
    $strLanguageCode = $this->getRequest()->getParam('language');
    $intLanguageId = 0;
    $arrLanguages = $this->core->config->languages->language->toArray();
    foreach($arrLanguages as $arrLanguage){
      if($arrLanguage['code'] == $strLanguageCode){
        $this->core->intLanguageId = $arrLanguage['id'];
        break;
      }
    }
  }
  
  /**
   * indexAction
   * @author Cornelius Hansjakob <cornelius.hansjakob@massiveart.com>
   * @version 1.0
   */
  public function indexAction(){
    $this->_helper->viewRenderer->setNoRender();
    
    $this->objPage = $this->getRequest()->getParam('page');
    $this->objTheme = $this->getRequest()->getParam('theme');
    $this->translate = Zend_Registry::get('PageHelper')->getTranslate(); 
    
    $this->core->logger->debug('language: '.$this->objPage->getLanguageId());
    
    if(file_exists(GLOBAL_ROOT_PATH.'public/website/themes/'.$this->objTheme->path.'/helpers/RssHelper.php')){
      require_once(GLOBAL_ROOT_PATH.'public/website/themes/'.$this->objTheme->path.'/helpers/RssHelper.php');
      $strRssHelper = ucfirst($this->objTheme->path).'_RssHelper';
      $objRssHelper = new $strRssHelper();
    }else{
      require_once(dirname(__FILE__).'/../helpers/RssHelper.php');
      $objRssHelper = new RssHelper();
    }
    $objRssHelper->setPage($this->objPage);
    $objRssHelper->setTranslate($this->translate);
    Zend_Registry::set('RssHelper', $objRssHelper);
    
    $this->view->template = $this->objPage->getTemplateFile();
    
    $this->view->setScriptPath(GLOBAL_ROOT_PATH.'public/website/themes/'.$this->objTheme->path.'/');
    $this->renderScript($this->strRenderScript);
  }  
  
  /**
   * getTranslate
   * @return HtmlTranslate
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function getTranslate(){
    return $this->translate;
  }
  
  /**
   * dirtysubscribersAction
   * @author Daniel Rotter <daniel.rotter@massiveart.com>
   * @version 1.0
   */
  public function dirtysubscribersAction(){
    $this->_helper->viewRenderer->setNoRender();
    
    if(!isset($_SERVER['PHP_AUTH_USER'])){
    header('WWW-Authenticate: Basic realm="default"');
    header('HTTP/1.0 401 Unauthorized');
    die('<html>
          <head>
            <title>401 Authorization Required</title>
          </head>
          <body>
            <h1>Authorization Required</h1>
            <p>This server could not verify that you are authorized to access the document requested.  Either you supplied the wrong credentials (e.g., bad password), or your browser doesn\'t understand how to supply the credentials required.</p>
            <hr>
            <address>'.$_SERVER['SERVER_SOFTWARE'].' Server at '.$_SERVER['HTTP_HOST'].' Port '.$_SERVER['SERVER_PORT'].'</address>
          </body>
        </html>');
    }else{  
      if($_SERVER['PHP_AUTH_USER'] == $this->core->config->rss->dirtysubscribers->username
        && $_SERVER['PHP_AUTH_PW'] == $this->core->config->rss->dirtysubscribers->password){
        header("Content-type: text/xml");
        require_once 'Zend/Feed.php';
        //Send RSS-Feed
        $objDirtySubscribers = $this->getModelSubscribers()->loadByDirtyStatus(true);
        require_once(dirname(__FILE__).'/../views/helpers/DirtySubscriberBuilder.php');
        $dirtySubscriberFeed = Zend_Feed::importBuilder(new DirtySubscriberBuilder($objDirtySubscribers), 'rss');
        $dirtySubscriberFeed->send();
      }else{
        header('WWW-Authenticate: Basic realm="default"');
        header('HTTP/1.0 401 Unauthorized');
        die('<html>
               <head>
                 <title>401 Authorization Required</title>
               </head>
               <body>
                 <h1>Authorization Required</h1>
                 <p>This server could not verify that you are authorized to access the document requested.  Either you supplied the wrong credentials (e.g., bad password), or your browser doesn\'t understand how to supply the credentials required.</p>
                 <hr>
                 <address>'.$_SERVER['SERVER_SOFTWARE'].' Server at '.$_SERVER['HTTP_HOST'].' Port '.$_SERVER['SERVER_PORT'].'</address>
               </body>
             </html>');
      }
    }
  }
  
  /**
   * getModelSubscribers
   * @author Thomas Schedler <tsh@massiveart.com>
   * @version 1.0
   */
  protected function getModelSubscribers(){
    if (null === $this->objModelSubscribers) {
      /**
       * autoload only handles "library" compoennts.
       * Since this is an application model, we need to require it
       * from its modules path location.
       */
      require_once GLOBAL_ROOT_PATH.$this->core->sysConfig->path->zoolu_modules.'contacts/models/Subscribers.php';
      $this->objModelSubscribers = new Model_Subscribers();
      $this->objModelSubscribers->setLanguageId($this->core->intLanguageId);
    }

    return $this->objModelSubscribers;
  }
}
?>