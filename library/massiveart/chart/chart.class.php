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
 * @package    library.massiveart.chart
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * ClientHelper
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-04-22: Daniel Rotter
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 * @package massiveart.chart
 * @subpackage Chart
 */

require_once(GLOBAL_ROOT_PATH . "library/pChart/class/pData.class.php");
require_once(GLOBAL_ROOT_PATH . "library/pChart/class/pDraw.class.php");
require_once(GLOBAL_ROOT_PATH . "library/pChart/class/pImage.class.php");

class Chart extends pImage
{

    public function __construct($XSize, $YSize, $Values, $Labels, $Margin = 30, $Min = 0, $Max = 100)
    {
        $DataSet = new pData();
        $DataSet->addPoints($Values);
        $DataSet->addPoints($Labels, 'Labels');
        $DataSet->setSerieDescription('Labels');
        $DataSet->setAbscissa('Labels');

        parent::__construct($XSize, $YSize, $DataSet);

        $this->setFontProperties(array('FontName' => GLOBAL_ROOT_PATH . 'library/pChart/fonts/calibri.ttf', 'FontSize' => 8));
        $this->setGraphArea($Margin, $Margin, $XSize - $Margin, $YSize - $Margin);
        $this->drawScale(array('Mode' => SCALE_MODE_MANUAL, 'ManualScale' => array(0 => array('Min' => $Min, 'Max' => $Max))));
    }
}

?>
