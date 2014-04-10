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
 * Webhooks_NewsletterStatisticsController
 *
 * Version history (please keep backward compatible):
 *
 * @author Raphael Stocker <rst@massiveart.com>
 * @version 1.0
 */

class Webhooks_NewsletterStatisticsController extends Zend_Controller_Action
{

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var CommandChain
     */
    protected $objCommandChain;

    /**
     * init
     */
    public function init()
    {
        $this->core = Zend_Registry::get('Core');
        $this->initCommandChain();
    }

    /**
     * init command chain
     * @return void
     */
    private function initCommandChain()
    {
        $this->objCommandChain = new CommandChain();
        $this->objCommandChain->addCommand(new NewsletterStatisticsCommand());
    }
    
    /**
     * indexAction
     */
    public function indexAction() {
        $this->_helper->viewRenderer->setNoRender();
    }
    
    /**
     * trackAction
     */
    public function trackAction() {
        try {
            $this->_helper->viewRenderer->setNoRender();
            $this->core->logger->debug(var_export($this->getRequest()->getParams(), true));
            $this->objCommandChain->runCommand('newsletter:statistics:track', array('request' => $this->getRequest()->getParams()));
        } catch(Exception $e) {
            $this->core->logger->err($e);
        }
    }
}
