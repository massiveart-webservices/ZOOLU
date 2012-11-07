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
 * ContentchooserController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-07-21: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */
class Core_ContentchooserController extends AuthControllerAction
{
    /**
     * @var Zend_Controller_Request_Abstract
     */
    private $objRequest;

    /**
     * @var Model_Modules
     */
    protected $objModelModules;

    /**
     * @var Model_RootLevels
     */
    protected $objModelRootLevels;

    /**
     * init
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->objRequest = $this->getRequest();
    }

    /**
     * overlayModulesAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function overlayModulesAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->overlayModulesAction()');
        try {
            $arrSelectedIds = array();

            $strRelationIds = $this->objRequest->getParam('relationIds');
            if ($strRelationIds != '') {
                $strTmpRelationIds = trim($strRelationIds, '[]');
                $arrSelectedIds = explode('][', $strTmpRelationIds);
            }

            $objModules = $this->getModelModules()->getModules();

            $this->view->assign('elements', $objModules);
            $this->view->assign('overlaytitle', $this->core->translate->_('Choose_module'));
            $this->view->assign('translate', $this->core->translate);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * overlayRootlevelsAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function overlayRootlevelsAction()
    {
        $this->core->logger->debug('core->controllers->DashboardController->overlayRootlevelsAction()');
        try {
            $intModuleId = $this->objRequest->getParam('moduleId');
            $objRootLevels = $this->getModelRootLevels()->loadRootLevelsByModuleId($intModuleId);

            $this->view->assign('elements', $objRootLevels);
            $this->view->assign('overlaytitle', $this->core->translate->_('Choose_main_area'));
            $this->view->assign('translate', $this->core->translate);
        } catch (Exception $exc) {
            $this->core->logger->err($exc);
            exit();
        }
    }

    /**
     * getModelModules
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelModules()
    {
        if (null === $this->objModelModules) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Modules.php';
            $this->objModelModules = new Model_Modules();
        }

        return $this->objModelModules;
    }

    /**
     * getModelRootLevels
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelRootLevels()
    {
        if (null === $this->objModelRootLevels) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/RootLevels.php';
            $this->objModelRootLevels = new Model_RootLevels();
            $this->objModelRootLevels->setLanguageId(1); // TODO Language from user
        }

        return $this->objModelRootLevels;
    }
}
