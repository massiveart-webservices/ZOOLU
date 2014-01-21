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
 * @package    library.massiveart.command
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * NewsletterCampaignCommand
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-05-09: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.command
 * @subpackage NewsletterCampaignCommand
 */

require_once(dirname(__FILE__) . '/../command.interface.php');

class NewsletterCampaignCommand implements CommandInterface
{

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Zend_Loader_PluginLoader
     */
    protected static $objPluginLoader;

    /**
     * @var array
     */
    private $campaign = null;

    /**
     * Constructor
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * onCommand
     * @param string $strName
     * @param array $arrArgs
     * @return boolean
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function onCommand($strName, $arrArgs)
    {
        $campaignClass = $this->core->sysConfig->newsletter->campaign;
        if ($campaignClass != '') {
            $this->getInstance($campaignClass);    
            if ($this->campaign instanceof NewsletterCampaignInterface) {
                switch ($strName) {
                    case 'campaign:init':
                        return $this->campaign->init($arrArgs);
                        break;
                    case 'campaign:update':
                        return $this->campaign->update($arrArgs);
                        break;
                    case 'newsletter:send':
                        return $this->campaign->send($arrArgs);
                        break;
                    case 'newsletter:sendTest':
                        return $this->campaign->sendTest($arrArgs);
                        break;
                    case 'recipients:count:get':
                        return $this->campaign->getRecipientsCount($arrArgs);
                        break;
                }
            }
        }
    }
    
    /**
     * getInstance
     * @return NewsletterCampaignInterface
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    private function getInstance($strCampaignClass)
    {
        try {
            if ($this->campaign == null) {
                try {
                    $strClass = $this->getPluginLoader($strCampaignClass)->load($strCampaignClass);
                } catch (Zend_Loader_PluginLoader_Exception $e) {
                    throw new Exception('Camapaign Helper by name ' . $strCampaignClass . ' not found: ' . $e);
                }
                $this->campaign = new $strClass();

                if (!$this->campaign instanceof NewsletterCampaignInterface) {
                    throw new Exception('Camapaign name ' . $strCampaignClass . ' -> class ' . $strClass . ' is not of type NewsletterCampaignInterface');
                }
            }
            return $this->campaign;
        } catch (Exception $exc) {
            $this->core->logger->warn($exc);
        }
    }

    /**
     * getPluginLoader
     * @return Zend_Loader_PluginLoader
     */
    private static function getPluginLoader($strCampaignClass)
    {
        if (null === self::$objPluginLoader) {
            self::$objPluginLoader = new Zend_Loader_PluginLoader(array(
                'NewsletterCampaign' => GLOBAL_ROOT_PATH . 'library/massiveart/newsletter/campaign/' . strtolower($strCampaignClass),
            ));
        }
        return self::$objPluginLoader;
    }
}