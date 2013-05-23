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
 * Zip Class - static function container
 *
 * Version history (please keep backward compatible):
 * 1.0, 2012-08-09: Cornelius Hansjakob
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 * @package massiveart.utilities
 * @subpackage Zip
 */

class Zip
{

    /**
     * createZip
     * creates a zip file out of the files in the array
     *
     * $files = array('FILENAME' => 'PATH TO FILE WITH FILENAME')
     * $files = array('xyz.dxf' => '/var/data/websites/bazinga.at/uploads/xyz.dxf.zip')
     *
     * Version history (please keep backward compatible):
     * 1.0, 2012-08-09: Cornelius Hansjakob
     *
     * @param Core $core
     * @param array $files
     * @param string $destination
     * @return boolean $overwrite
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    public static function createZip(Core $core, $files = array(), $destination = '', $overwrite = false)
    {
        $core->logger->debug('massiveart->utilities->Zip->createZip: ' . var_export($files, true));

        //if the zip file already exists and overwrite is false, return false
        if (file_exists($destination) && !$overwrite) {
            return false;
        }

        //vars
        $valid_files = array();

        //check if files of array exist
        if (is_array($files)) {
            //cycle through each file
            foreach ($files as $filename => $filepath) {
                //make sure the file exists
                if (file_exists($filepath)) {
                    $valid_files[$filename] = $filepath;
                }
            }
        }

        //if the files are valid 
        if (count($valid_files)) {

            //create the archive
            $objZip = new ZipArchive();
            if ($objZip->open($destination, (($overwrite === true) ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE)) !== true) {
                return false;
            }

            //add the files
            foreach ($valid_files as $filename => $filepath) {
                $objZip->addFile($filepath, $filename);
            }

            $core->logger->debug('massiveart->utilities->Zip->createZip: The zip archive contains ' . $objZip->numFiles . ' files with a status of ' . $objZip->status);

            //close the zip -- done!
            $objZip->close();

            //check to make sure the file exists
            return file_exists($destination);
        } else {
            return false;
        }
    }

}

?>