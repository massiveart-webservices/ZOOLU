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
 * @package    library.massiveart.utilities
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Password Helper Class - static function container
 *
 * Version history (please keep backward compatible):
 * 1.0, 2011-04-29: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.utilities
 * @subpackage PasswordHelper
 */

class PasswordHelper
{

    /**
     * generatePassword
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public function generatePassword($intLength = 8)
    {
        $dummy = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'), array('#', '&', '@', '$', '_', '%', '?', '+'));

        // shuffle array
        mt_srand((double) microtime() * 1000000);

        for ($i = 1; $i <= (count($dummy) * 2); $i++) {
            $swap = mt_rand(0, count($dummy) - 1);
            $tmp = $dummy[$swap];
            $dummy[$swap] = $dummy[0];
            $dummy[0] = $tmp;
        }

        return substr(implode('', $dummy), 0, $intLength);
    }
}

?>