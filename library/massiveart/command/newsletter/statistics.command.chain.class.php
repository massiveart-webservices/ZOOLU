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
 * StatisticsCommandChain
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2009-11-06: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.command
 * @subpackage CommandChain
 */

class StatisticsCommandChain extends CommandChain
{

    /**
     * runCommand
     * @param string $strName
     * @param array $arrArgs
     * @return boolean
     * @author Thomas Schedler <tsh@massiveart.com>
     * @version 1.0
     */
    public function runCommand($strName, $arrArgs)
    {
        foreach ($this->getCommands() as $objCommand) {
            $ret = $objCommand->onCommand($strName, $arrArgs);
            if ($ret !== false && $ret !== null) {
                return $ret;
            } else {
                return false;
            }
        }
        return false;
    }
}

?>
