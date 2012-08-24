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
 * Media_ServiceController
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-05-06: Cornelius Hansjakob
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

require_once(dirname(__FILE__) . '/../services/Media.php');

class Media_ServiceController extends Zend_Controller_Action
{

    /**
     * @var Core
     */
    private $core;

    /**
     * @var integer
     */
    protected $intLanguageId;

    /**
     * @var string
     */
    protected $strLanguageCode;

    /**
     * init index controller and get core obj
     */
    public function init()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * indexAction
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function indexAction()
    {
        $this->core->logger->debug('media->controllers->ServiceController->indexAction()');
        $this->_helper->viewRenderer->setNoRender();

        if ($this->getRequest()->getParam('wsdl') !== null) {
            $objWsdl = new Zend_Soap_AutoDiscover();
            $objWsdl->setClass('Service_Media');
            $objWsdl->handle();
        } else {
            $objServer = new Zend_Soap_Server('http://' . $_SERVER['HTTP_HOST'] . '/zoolu/media/service?wsdl');
            $objServer->setClass('Service_Media');
            $objServer->handle();
        }
    }
}

?>