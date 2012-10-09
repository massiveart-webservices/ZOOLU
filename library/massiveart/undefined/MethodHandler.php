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
 * @package    library.massiveart.undefined
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * HandleUndefinedMethod
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2012-01-31: Daniel Rotter
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.undefined
 * @subpackage UndefinedMethodHandler
 */
class UndefinedMethodHandler
{

    /**
     * @var UndefinedMethodHandler
     */
    private static $instance;

    /**
     * @var array
     */
    protected $listeners = array();

    protected function __construct()
    {
        $directory = new DirectoryIterator(GLOBAL_ROOT_PATH . 'client/listeners/undefined-methods');
        foreach ($directory as $fileInfo) {
            /** @var $fileInfo SplFileInfo */
            if ($fileInfo->isFile()) {
                $this->listeners[] = require_once $fileInfo->getPath() . '/' . $fileInfo->getFilename();
            }
        }
    }

    /**
     * @param $method
     * @param UndefinedMethod $handle
     */
    public function dispatch($method, UndefinedMethod $handle)
    {
        foreach ($this->listeners as $listener) {
            /** @var $listener UndefinedMethodListener */
            $listener->notify($method, $handle);
        }
    }

    /**
     * getInstance
     * @return UndefinedMethodHandler
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new UndefinedMethodHandler();
        }
        return self::$instance;
    }


}