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
 * @package    application.zoolu.modules.cms.controllers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * PortalController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2014-01-10: Alexander Schranz
 *
 * @author Alexander Schranz <alexander.schranz@massiveart.com>
 * @version 1.0
 */

class Cms_PortalController extends AuthControllerAction
{

    /**
     * @var int
     */
    protected $rootLevelId;

    /**
     * @var int
     */
    protected $languageId;

    /**
     * init
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     * @return void
     */
    public function init()
    {
        parent::init();

        if (!Security::get()->isAllowed('portals', Security::PRIVILEGE_VIEW)) {
            $blnCrossSidePrivilege = ($this->getRequest()->isXmlHttpRequest() && Security::get()->isAllowed('global', Security::PRIVILEGE_VIEW) && strpos($this->getRequest()->getActionName(), 'get') === 0) ? true : false;
            if (!$blnCrossSidePrivilege) {
                $this->_redirect('/zoolu');
            }
        }
    }

    /**
     * exportAction
     */
    public function exportAction()
    {
        $this->core->logger->debug('cms->controllers->PortalController->exportAction()');
        $this->_helper->viewRenderer->setNoRender();
        $this->rootLevelId = intval($this->getRequest()->getParam('rootLevelId'));
        $this->languageId = intval($this->getRequest()->getParam('languageId'));

        try {
            if ($this->rootLevelId == 0) {
                throw new Exception ('RootLevel not found');
            }
            if ($this->languageId == 0) {
                throw new Exception ('Language not found');
            }
            $exporter = new PortalExporter($this->core, $this->rootLevelId, $this->languageId);
            $csv = $exporter->getAsCsv();

            header("content-type: application/csv-tab-delimited-table");
            header("content-length: ".strlen($csv));
            header("content-disposition: attachment; filename=\"PortalExport_".$this->rootLevelId."_".$this->languageId."_".date('Y-m-d-Hi').".csv\"");
            echo $csv;
        } catch (Exception $e) {
            $this->core->logger->err($e->getMessage());
        }
    }
}