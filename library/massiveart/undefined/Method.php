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
 * HandleUndefinedMethod
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2012-09-17: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.undefined
 * @subpackage UndefinedMethodHandler
 */
class UndefinedMethod
{
    /**
     * @var object instance
     */
    protected $subject;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var mixed
     */
    protected $returnValue;

    /**
     * @var bool
     */
    protected $isProcessed = false;

    /**
     * @param $subject
     * @param $method
     * @param $arguments
     */
    public function __construct($subject, $method, $arguments)
    {
        $this->subject = $subject;
        $this->method = $method;
        $this->arguments = $arguments;
    }

    /**
     * @return object
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Sets the value to return
     */
    public function setReturnValue($value)
    {
        $this->returnValue = $value;
        $this->isProcessed = true;
    }

    public function getReturnValue()
    {
        return $this->returnValue;
    }

    /**
     * @return bool
     */
    public function isProcessed()
    {
        return $this->isProcessed;
    }

}