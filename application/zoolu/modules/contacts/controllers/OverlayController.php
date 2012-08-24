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
 * Contacts_OverlayController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-07-01: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 */

class Contacts_OverlayController extends AuthControllerAction
{

    /**
     * @var Model_RootLevelTypes
     */
    protected $objModelRootLevelTypes;

    /**
     * @var Model_RootLevels
     */
    protected $objModelRootLevels;

    /**
     * listFilterAction
     * @version 1.0
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function listfilterAction()
    {
        $this->core->logger->debug('contacts->controllers->OverlayController->listfilterAction()');

        $intRootLevelTypeId = $this->getRequest()->getParam('rootLevelTypeId');
        $intRootLevelId = $this->getRequest()->getParam('rootLevelId');
        $intRootLevelFilterId = $this->getRequest()->getParam('rootLevelFilterId', null);

        $objRootLevelTypeFilters = $this->getModelRootLevelTypes()->loadRootLevelTypeFilterTypes($intRootLevelTypeId);

        if ($intRootLevelFilterId != null) {
            $objRootLevelFilter = $this->getModelRootLevels()->loadRootLevelFilter($intRootLevelFilterId);
            $objRootLevelFilterValues = $this->getModelRootLevels()->loadRootLevelFilterValues($intRootLevelFilterId);
            $this->view->assign('rootLevelFilter', $objRootLevelFilter);
            $this->view->assign('rootLevelFilterValues', $objRootLevelFilterValues);
            $this->view->assign('filtertitle', $objRootLevelFilter->current()->filtertitle);
            $this->view->assign('rootLevelFilterId', $objRootLevelFilter->current()->id);
        }

        $this->view->assign('rootLevelTypeFilters', $objRootLevelTypeFilters);
        $this->view->assign('rootLevelId', $intRootLevelId);
    }

    /**
     * overviewfilterAction
     * @version 1.0
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function overviewfilterAction()
    {
        $this->core->logger->debug('contacts->controllers->OverlayController->overviewfilterAction()');

        $intRootLevelId = $this->getRequest()->getParam('rootLevelId');

        $objRootLevelFilters = $this->getModelRootLevels()->loadRootLevelFilters($intRootLevelId);

        $this->view->assign('rootLevelFilters', $objRootLevelFilters);
    }

    /**
     * saveFilterAction
     * @version 1.0
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     */
    public function savefilterAction()
    {
        $this->core->logger->debug('contacts->controllers->OverlayController->savefilterAction()');

        //Delete old Row if it is an update
        $intRootLevelFilterId = $this->getRequest()->getParam('rootLevelFilterEditId', null);
        if ($intRootLevelFilterId != null) {
            $this->getModelRootLevels()->deleteRootLevelFilter($intRootLevelFilterId);
        }

        //Read general filter information
        $strFilterTitle = $this->getRequest()->getParam('filtertitle');
        $intRootLevelId = $this->getRequest()->getParam('rootLevelId');
        $arrFilter = array(
            'filtertitle'  => $strFilterTitle,
            'idRootLevels' => $intRootLevelId
        );

        //Read the filters
        $arrFilterNr = explode('][', trim($this->getRequest()->getParam('lineInstances'), '[]'));
        $arrFilterValues = array();
        foreach ($arrFilterNr as $intFilterNr) {
            $arrFilterValues[] = array(
                'field'    => $this->getRequest()->getParam('filter_' . $intFilterNr),
                'operator' => $this->getRequest()->getParam('operator_' . $intFilterNr),
                'value'    => $this->getRequest()->getParam('value_' . $intFilterNr)
            );
        }

        $this->getModelRootLevels()->addRootLevelFilter($arrFilter, $arrFilterValues);

        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * deletefilterAction
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     */
    public function deletefilterAction()
    {
        $this->core->logger->debug('contacts->controllers->SubscriberController->deletefilterAction()');

        $intRootLevelFilterId = $this->getRequest()->getParam('rootLevelFilterId');
        $this->getModelRootLevels()->deleteRootLevelFilter($intRootLevelFilterId);

        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * getModelRootLevelTypes
     * @return Model_RootLevelTypes
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelRootLevelTypes()
    {
        if (null === $this->objModelRootLevelTypes) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/RootLevelTypes.php';
            $this->objModelRootLevelTypes = new Model_RootLevelTypes();
            $this->objModelRootLevelTypes->setLanguageId($this->core->intZooluLanguageId);
        }

        return $this->objModelRootLevelTypes;
    }

    /**
     * getModelRootLevel
     * @return Model_RootLevels
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
            $this->objModelRootLevels->setLanguageId($this->core->intZooluLanguageId);
        }

        return $this->objModelRootLevels;
    }
}

?>