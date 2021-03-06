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
 * @package    library.massiveart.website.customer
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * RegistrationStrategyAbstract
 *
 * Version history (please keep backward compatible):
 * 1.0, 2012-10-11 Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 * @package massiveart.website.customer
 * @subpackage RegistrationStrategyAbstract
 */
abstract class RegistrationStrategyAbstract
{
    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Zend_Controller_Request_Abstract
     */
    private $_objRequest;

    /**
     * @var Zend_Db_Table_Abstract
     */
    private $_objTheme;

    /**
     * @var Model_Customers
     */
    private $objModelCustomers;

    /**
     * @var Model_RootLevels
     */
    private $objModelRootLevels;

    /**
     * @var Model_Pages
     */
    private $objModelPages;

    /**
     * getRequest
     * @return Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        return $this->_objRequest;
    }

    /**
     * @param Zend_Controller_Request_Abstract $objRequest
     */
    public function __construct(Zend_Controller_Request_Abstract $objRequest, Zend_Db_Table_Row $objTheme)
    {
        $this->core = Zend_Registry::get('Core');
        $this->_objRequest = $objRequest;
        $this->_objTheme = $objTheme;
    }

    /**
     * register
     * @param string $strRedirectUrl
     * @return mixed
     */
    public abstract function register($strRedirectUrl = '/', $intRootlevelId = 1);

    protected function getTheme()
    {
        return $this->_objTheme;
    }

    /**
     * getModelCustomers
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     * @return Model_Customers
     */
    protected function getModelCustomers()
    {
        if (null === $this->objModelCustomers) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'contacts/models/Customers.php';
            $this->objModelCustomers = new Model_Customers();
        }

        return $this->objModelCustomers;
    }

    /**
     * getModelRootLevels
     * @author Daniel Rotter <daniel.rotter@massiveart.com>
     * @version 1.0
     * @return Model_RootLevels
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
        }

        return $this->objModelRootLevels;
    }

    /**
     * getModelPages
     * @return Model_Pages
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelPages()
    {
        if (null === $this->objModelPages) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'cms/models/Pages.php';
            $this->objModelPages = new Model_Pages();
            $this->objModelPages->setLanguageId($this->core->intLanguageId);
        }

        return $this->objModelPages;
    }
}

?>