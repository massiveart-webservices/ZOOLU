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

require_once(dirname(__FILE__) . '/registration.strategy.abstract.class.php');
/**
 * RegistrationStrategyDoubleOptIn
 *
 * Version history (please keep backward compatible):
 * 1.0, 2012-10-11 Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 * @package massiveart.website.customer
 * @subpackage RegistrationStrategyAbstract
 */
class RegistrationStrategySingleOptIn extends RegistrationStrategyAbstract
{
    public function register($strRedirectUrl = '/', $intRootlevelId = 1)
    {
        $arrGroups = $this->getModelPages()->loadAllowedGroups($intRootlevelId, 2, $strRedirectUrl);

        //Insert active customer in database
        $objRootLevel = $this->getModelRootLevels()->loadRootLevelById($this->getTheme()->idRootLevels)->current();
        $arrData = array(
            'username' => $this->getRequest()->getParam('username'),
            'password' => md5($this->getRequest()->getParam('password')),
            'email' => $this->getRequest()->getParam('email'),
            'fname' => $this->getRequest()->getParam('fname'),
            'sname' => $this->getRequest()->getParam('sname'),
            'idCustomerStatus' => $objRootLevel->idCustomerRegistrationStatus,
            'idRootLevels' => $intRootlevelId
        );
        $intCustomerId = $this->getModelCustomers()->add($arrData);
        $this->getModelCustomers()->updateGroups($arrGroups, $intCustomerId);

        return 'confirmation';
    }
}

?>