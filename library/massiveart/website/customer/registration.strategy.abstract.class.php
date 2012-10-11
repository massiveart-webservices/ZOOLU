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
    private $_objRequest;

    public function __construct(Zend_Controller_Request_Abstract $objRequest)
    {
        $this->_objRequest = $objRequest;
    }

    /**
     * register
     * @return mixed
     */
    public abstract function register();

    protected function validate()
    {
        return $this->_objRequest->getParam('email', '') != ''
            && $this->_objRequest->getParam('username', '') != ''
            && $this->_objRequest->getParam('password', '') != ''
            && $this->_objRequest->getParam('password') == $this->_objRequest->getParam('passwordConfirm');
    }
}

?>