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
 * @package    application.zoolu.modules.core.models
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ModelAbstract
 *
 * Version history (please keep backward compatible):
 * 1.0, 2012-09-18: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 */

abstract class ModelAbstract
{

    public function __call($method, $arguments)
    {
        // create an undefined method handle
        $undefinedMethod = new UndefinedMethod($this, $method, $arguments);
        UndefinedMethodHandler::getInstance()->dispatch(get_class($this) . '::' . $method, $undefinedMethod);

        // no listener was able to process the $method? The method does not exist
        if (!$undefinedMethod->isProcessed()) {
            throw new Exception(sprintf('Call to undefined method %s::%s.', get_class($this), $method));
        }

        // return the listener returned value
        return $undefinedMethod->getReturnValue();
    }
}