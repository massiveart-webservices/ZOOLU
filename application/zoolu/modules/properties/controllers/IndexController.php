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
 * @package    application.zoolu.modules.core.properties.controllers
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * IndexController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-01-15: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Properties_IndexController extends AuthControllerAction {

  /**
   * init
   */
  public function init(){
    parent::init();
    if(!Security::get()->isAllowed('settings', Security::PRIVILEGE_VIEW)){
      $this->_redirect('/zoolu');
    }
  }
  
  /**
   * The default action - show the home page
   */
  public function indexAction(){

    $this->_helper->viewRenderer->setNoRender();

    Zend_Layout::startMvc(array(
      'layout' => 'properties',
      'layoutPath' => '../application/zoolu/layouts'
    ));

    $objLayout = Zend_Layout::getMvcInstance();
    $objLayout->assign('navigation',$this->view->action('index', 'Navigation', 'properties'));
    $objLayout->assign('userinfo',$this->view->action('userinfo', 'User', 'users'));
    $objLayout->assign('modules', $this->view->action('navtop', 'Modules', 'core'));
    $objLayout->assign('modules', $this->view->action('navtop', 'Modules', 'core', array('module' => $this->core->sysConfig->modules->properties)));

    $this->view->assign('jsVersion', $this->core->sysConfig->version->js);
    $this->view->assign('cssVersion', $this->core->sysConfig->version->css);
    $this->view->assign('module', $this->core->sysConfig->modules->properties);
  }

}
