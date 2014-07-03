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
 * @package    library.massiveart.export
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Export Class
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-11: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 * @package massiveart.export
 * @subpackage Export
 */

class Export
{

    /**
     * exports a Rowset into a Csv
     * @param Zend_Db_Table_Rowset $objRowset
     * @param string $delimiter
     * @return string
     */
    public static function exportRowsetInCsv($objRowset, $delimiter = ';')
    {
        //Create Headline
        $arrRowset = $objRowset->toArray();
        $arrColumns = $arrRowset[0];
        $arrColumns = array_keys($arrColumns);
        $strHeadLine = self::csvOutput($arrColumns, $delimiter);

        //Create Datalines
        $strData = '';
        foreach ($arrRowset as $arrRow) {
            $strData .=  self::csvOutput($arrRow, $delimiter);
        }
        return $strHeadLine . $strData;
    }

    /**
     * @param $data
     * @param string $delimiter
     * @return string
     */
    protected static function csvOutput($data, $delimiter = ';')
    {
        $output = fopen('php://output', 'w');
        ob_start();
        fputcsv($output, $data, $delimiter);
        fclose($output);
        return ob_get_clean();
    }
}
