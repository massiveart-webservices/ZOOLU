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
class RegistrationStrategyDoubleOptIn extends RegistrationStrategyAbstract
{
    public function register()
    {
        if ($this->getRequest()->getParam('key', '') != '') {
            //Update customer to active
            $objRootLevel = $this->getModelRootLevels()->loadRootLevelById($this->getTheme()->idRootLevels)->current();
            $objCustomers = $this->getModelCustomers()->loadByRegistrationKey($this->getRequest()->getParam('key'));
            if (count($objCustomers) > 0) {
                $intCustomerId = $objCustomers->current()->id;
                $arrData = array(
                    'registrationKey' => new Zend_Db_Expr('NULL'),
                    'idCustomerStatus' => $objRootLevel->idCustomerRegistrationStatus //TODO Parameterize the status
                );
                $this->getModelCustomers()->edit($arrData, $intCustomerId);
                return 'keyConfirmation';
            } else {
                //The registration key is not valid
                return 'invalidKey';
            }
        } else {
            //Generate registrationKey
            $strRegistrationKey = uniqid('', true);

            //Insert customer in database as unverified
            $arrData = array(
                'registrationKey' => $strRegistrationKey,
                'username' => $this->getRequest()->getParam('username'),
                'password' => md5($this->getRequest()->getParam('password')),
                'email' => $this->getRequest()->getParam('email'),
                'fname' => $this->getRequest()->getParam('fname'),
                'sname' => $this->getRequest()->getParam('sname'),
                'idCustomerStatus' => $this->core->sysConfig->customerstatus->unverified,
                'idRootLevels' => 19, //TODO Do not hardcode
            );
            $this->getModelCustomers()->add($arrData);

            //Send registration link as email
            $this->sendRegistrationMail($arrData);
            return 'confirmation';
        }
    }

    private function sendRegistrationMail($arrMailData)
    {
        $objMail = new Zend_Mail('utf-8');

        $objTransport = null;
        if (!empty($this->core->config->mail->params->host)) {
            // config for SMTP with auth
            $arrConfig = array('auth' => 'login',
                'username' => $this->core->config->mail->params->username,
                'password' => $this->core->config->mail->params->password);

            // smtp
            $objTransport = new Zend_Mail_Transport_Smtp($this->core->config->mail->params->host, $arrConfig);
        }

        // set mail subject
        $objMail->setSubject('Registrierung');

        $strBody = '<a href="http://' . $_SERVER['HTTP_HOST'] . '/register?key=' . $arrMailData['registrationKey'] . '">Bestätigen</a>';

        // set body
        $objMail->setBodyHtml($strBody);

        // set mail from address
        $objMail->setFrom($this->core->config->mail->from->address, $this->core->config->mail->from->name);

        // add to address
        $objMail->addTo($arrMailData['email'], $arrMailData['username']);

        //set header for sending mail
        $objMail->addHeader('Sender', $this->core->config->mail->params->username);

        // send mail now
        if ($this->core->config->mail->transport == 'smtp') {
            $objMail->send($objTransport);
        } else {
            $objMail->send();
        }
    }
}

?>