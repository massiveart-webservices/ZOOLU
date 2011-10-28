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
 * @package    library.massiveart.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * AuthControllerAction
 *
 * Check authentification before starting controller actions
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-10: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class AuthControllerAction extends Zend_Controller_Action {

  /**
   * @var Core
   */
  protected $core;

  /**
   * Init
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public function init(){
    $this->core = Zend_Registry::get('Core');
  }

	/**
   * ensure that no other actions are accessible if you are not logged in
   */
  public function preDispatch(){

  	/**
  	 * set default encoding to view
  	 */
  	$this->view->setEncoding($this->core->sysConfig->encoding->default);

  	/**
  	 * set translate obj
  	 */
    $this->view->translate = $this->core->translate;

  	/**
     * check if user is authenticated, else redirect to login form
     */
    $objAuth = Zend_Auth::getInstance();
    
    if(!$objAuth->hasIdentity() || !isset($_SESSION['sesZooluLogin']) || $_SESSION['sesZooluLogin'] == false){
      if($this->getRequest()->isXmlHttpRequest()){
        echo '<script type="text/javascript">
              //<![CDATA[
                window.location.reload();
              //]]>
              </script>';
        exit();
      }else{
        $this->_redirect('/zoolu/users/user/login');
      }
    }
  }
}
?>