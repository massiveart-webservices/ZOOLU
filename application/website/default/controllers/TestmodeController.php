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
 * TestmodeController
 * 
 * Version history (please keep backward compatible):
 * 1.0, 2009-04-15: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class TestmodeController extends Zend_Controller_Action {

  /**
   * core object instance (logger, dbh, ...)
   * @var Core
   */
  protected $core; 
  
  /**
   * request object instacne
   * @var Zend_Controller_Request_Abstract
   */
  protected $request; 
    
  /**
   * preDispatch
   * Called before action method.
   * 
   * @return void  
   * @author Thomas Schedler <cha@massiveart.com>
   * @version 1.0   
   */
  public function preDispatch(){
    $this->_helper->viewRenderer->setNoRender();
    $this->core = Zend_Registry::get('Core');    
    $this->request = $this->getRequest();
  }
  
  /**
   * indexAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function indexAction(){
  	$this->core->logger->debug('website->controllers->TestmodeController->indexAction()');
  }
  
  /**
   * changeAction
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function changeAction(){
    $this->core->logger->debug('website->controllers->TestmodeController->changeAction()');
    
    $objAuth = Zend_Auth::getInstance();
    
    if($objAuth->hasIdentity()){
      if($this->request->getParam('TestMode') == 'on'){
        $_SESSION['sesTestMode'] = true;
      }else{
        $_SESSION['sesTestMode'] = false;
      }
    }
  }
}
?>