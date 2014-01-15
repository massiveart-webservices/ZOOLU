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
 * @package    application.zoolu.modules.core.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * IndexController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-09: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Zoolu_IndexController extends AuthControllerAction
{

    /**
     * The default action - show the home page
     */
    public function indexAction()
    {

        $this->_helper->viewRenderer->setNoRender();

        if ($this->core->getDashboard() !== true) {
            $this->_redirect('/zoolu/cms');
        } else {

            Zend_Layout::startMvc(array(
                                       'layout'     => 'index',
                                       'layoutPath' => '../application/zoolu/layouts'
                                  ));

            $objLayout = Zend_Layout::getMvcInstance();
            $objLayout->assign('navigation', '');
            $objLayout->assign('userinfo', $this->view->action('userinfo', 'User', 'users'));
            $objLayout->assign('modules', $this->view->action('navtop', 'Modules', 'core'));

            $this->view->assign('jsVersion', $this->core->sysConfig->version->js);
            $this->view->assign('cssVersion', $this->core->sysConfig->version->css);
        }
    }

}
