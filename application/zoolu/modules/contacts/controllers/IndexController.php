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
 * Contacts_IndexController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-01-05: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

class Contacts_IndexController extends AuthControllerAction
{

    /**
     * init
     */
    public function init()
    {
        parent::init();
        if (!Security::get()->isAllowed('contacts', Security::PRIVILEGE_VIEW)) {
            $this->_redirect('/zoolu');
        }
    }

    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_forward('tree');
    }

    /**
     * listAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function listAction()
    {
        $this->_helper->viewRenderer->setNoRender();

        Zend_Layout::startMvc(array(
            'layout'     => 'contacts-list',
            'layoutPath' => '../application/zoolu/layouts'
        ));

        //Additional information for upload
        $strFileId = $this->getRequest()->getParam('fileId', '');
        if ($strFileId != '') {
            $this->view->assign('fileId', $strFileId);
        }

        $objLayout = Zend_Layout::getMvcInstance();
        $objLayout->assign('navigation', $this->view->action('index', 'Navigation', 'contacts', array('layoutType' => 'list')));
        $objLayout->assign('userinfo', $this->view->action('userinfo', 'User', 'users'));
        $objLayout->assign('modules', $this->view->action('navtop', 'Modules', 'core', array('module' => $this->core->sysConfig->modules->contacts)));

        $this->view->assign('jsVersion', $this->core->sysConfig->version->js);
        $this->view->assign('cssVersion', $this->core->sysConfig->version->css);
        $this->view->assign('rootLevelTypeId', $this->core->sysConfig->root_level_types->contacts);
        $this->view->assign('rootLevelId', $this->getRequest()->getParam('rootLevelId'));
        $this->view->assign('module', $this->core->sysConfig->modules->contacts);
    }

    /**
     * treeAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function treeAction()
    {
        $this->_helper->viewRenderer->setNoRender();

        Zend_Layout::startMvc(array(
            'layout'     => 'contacts-tree',
            'layoutPath' => '../application/zoolu/layouts'
        ));

        $objLayout = Zend_Layout::getMvcInstance();
        $objLayout->assign('navigation', $this->view->action('index', 'Navigation', 'contacts', array('layoutType' => 'tree')));
        $objLayout->assign('userinfo', $this->view->action('userinfo', 'User', 'users'));
        $objLayout->assign('modules', $this->view->action('navtop', 'Modules', 'core', array('module' => $this->core->sysConfig->modules->contacts)));

        $this->view->assign('jsVersion', $this->core->sysConfig->version->js);
        $this->view->assign('cssVersion', $this->core->sysConfig->version->css);
        $this->view->assign('rootLevelTypeId', $this->core->sysConfig->root_level_types->contacts);
        $this->view->assign('module', $this->core->sysConfig->modules->contacts);
    }
}

?>
