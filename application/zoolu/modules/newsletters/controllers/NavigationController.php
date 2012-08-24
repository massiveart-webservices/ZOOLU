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
 * Newsletters_NavigationController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-04-21: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

class Newsletters_NavigationController extends AuthControllerAction
{

    private $intRootLevelId;
    private $intFolderId;

    /**
     * @var Model_Newsletters
     */
    protected $objModelNewsletters;

    /**
     * @var Model_Folders
     */
    protected $objModelFolders;

    /**
     * init
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     * @return void
     */
    public function init()
    {
        parent::init();
        Security::get()->addFoldersToAcl($this->getModelFolders());
        Security::get()->addRootLevelsToAcl($this->getModelFolders(), $this->core->sysConfig->modules->newsletters);
    }

    /**
     * indexAction
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function indexAction()
    {
        $this->getModelFolders();
        $objRootLevels = $this->objModelFolders->loadAllRootLevelsWithGroups($this->core->sysConfig->modules->newsletters);

        $objRootLevelNavigation = new NavigationTree();
        if (count($objRootLevels) > 0) {
            $intOrder = 0;
            foreach ($objRootLevels as $objRootLevel) {
                $intOrder++;

                if (!$objRootLevelNavigation->hasSubTree($objRootLevel->name)) {
                    $objNavGroup = new NavigationTree();
                    $objNavGroup->setId($objRootLevel->idRootLevelGroups);
                    $objNavGroup->setItemId($objRootLevel->name);
                    $objNavGroup->setTypeId($objRootLevel->idRootLevelGroups);
                    $objNavGroup->setTitle($objRootLevel->rootLevelGroupTitle);
                    $objNavGroup->setUrl($objRootLevel->href);
                    $objNavGroup->setLanguageId(((int) $objRootLevel->rootLevelGuiLanguageId > 0 ? $objRootLevel->rootLevelGuiLanguageId : $objRootLevel->rootLevelLanguageId));

                    $objRootLevelNavigation->addTree($objNavGroup, $objRootLevel->name);
                }

                $objNavItem = new NavigationItem();
                $objNavItem->setId($objRootLevel->id);
                $objNavItem->setItemId($objRootLevel->name);
                $objNavItem->setTypeId($objRootLevel->idRootLevelTypes);
                $objNavItem->setTitle($objRootLevel->title);
                $objNavItem->setUrl($objRootLevel->href);
                $objNavItem->setOrder($intOrder);
                $objNavItem->setParentId($objRootLevel->idRootLevelGroups);
                $objNavItem->setLanguageId(((int) $objRootLevel->rootLevelGuiLanguageId > 0 ? $objRootLevel->rootLevelGuiLanguageId : $objRootLevel->rootLevelLanguageId));

                $objRootLevelNavigation->addToParentTree($objNavItem, $objRootLevel->name . '_' . $objRootLevel->id);
            }
        }

        $this->view->assign('rootLevelNavigation', $objRootLevelNavigation);
        $this->view->assign('rootLevelId', $this->getRequest()->getParam('rootLevelId'));
        $this->view->assign('rootLevelGroupId', $this->getRequest()->getParam('rootLevelGroupId'));
        $this->view->assign('rootLevelGroupKey', $this->getRequest()->getParam('rootLevelGroupKey'));
        $this->view->assign('rootLevelType', $this->getRequest()->getParam('rootLevelType'));

        $this->renderScript('navigation/list.phtml');
    }

    /**
     * getModelNewsletters
     * @return Model_Newsletters
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelNewsletters()
    {
        if (null === $this->objModelNewsletters) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Newsletters.php';
            $this->objModelNewsletters = new Model_Newsletters();
            $this->objModelNewsletters->setLanguageId(1);
        }

        return $this->objModelNewsletters;
    }

    /**
     * getModelFolders
     * @return Model_Folders
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    protected function getModelFolders()
    {
        if (null === $this->objModelFolders) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Folders.php';
            $this->objModelFolders = new Model_Folders();
            $this->objModelFolders->setLanguageId($this->core->intZooluLanguageId);
            $this->objModelFolders->setContentLanguageId(Zend_Auth::getInstance()->getIdentity()->contentLanguageId);
        }

        return $this->objModelFolders;
    }

    /**
     * setRootLevelId
     * @param integer $intRootLevelId
     */
    public function setRootLevelId($intRootLevelId)
    {
        $this->intRootLevelId = $intRootLevelId;
    }

    /**
     * getRootLevelId
     * @param integer $intRootLevelId
     */
    public function getRootLevelId()
    {
        return $this->intRootLevelId;
    }

    /**
     * setFolderId
     * @param integer $intFolderId
     */
    public function setFolderId($intFolderId)
    {
        $this->intFolderId = $intFolderId;
    }

    /**
     * getFolderId
     * @param integer $intFolderId
     */
    public function getFolderId()
    {
        return $this->intFolderId;
    }
}