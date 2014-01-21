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
 * NewsletterStatisticsCommand
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-05-09: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.command
 * @subpackage NewsletterStatisticsCommand
 */

require_once(dirname(__FILE__) . '/../command.interface.php');

class NewsletterStatisticsCommand implements CommandInterface
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
    private $statistics = null;

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
        $statisticsClass = $this->core->sysConfig->newsletter->statistics;
        if ($statisticsClass != '') {
            $this->getInstance($statisticsClass);    
            if ($this->statistics instanceof NewsletterStatisticsInterface) {
                switch ($strName) {
                    case 'newsletter:statistics:track':
                        return $this->statistics->track($arrArgs);
                        break;
                }
            }
        }
    }
    
    /**
     * getInstance
     * @return NewsletterStatisticsInterface
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    private function getInstance($strStatisticsClass)
    {
        try {
            if ($this->statistics == null) {
                try {
                    $strClass = $this->getPluginLoader($strStatisticsClass)->load($strStatisticsClass);
                } catch (Zend_Loader_PluginLoader_Exception $e) {
                    throw new Exception('Newsletter Helper by name ' . $strStatisticsClass . ' not found: ' . $e);
                }
                $this->statistics = new $strClass();

                if (!$this->statistics instanceof NewsletterStatisticsInterface) {
                    throw new Exception('Newsletter name ' . $strStatisticsClass . ' -> class ' . $strClass . ' is not of type NewsletterStatisticsInterface');
                }
            }
            return $this->statistics;
        } catch (Exception $exc) {
            $this->core->logger->warn($exc);
        }
    }

    /**
     * getPluginLoader
     * @return Zend_Loader_PluginLoader
     */
    private static function getPluginLoader($strStatisticsClass)
    {
        if (null === self::$objPluginLoader) {
            self::$objPluginLoader = new Zend_Loader_PluginLoader(array(
                'NewsletterStatistics' => GLOBAL_ROOT_PATH . 'library/massiveart/newsletter/statistics/' . strtolower($strStatisticsClass),
            ));
        }
        return self::$objPluginLoader;
    }
}