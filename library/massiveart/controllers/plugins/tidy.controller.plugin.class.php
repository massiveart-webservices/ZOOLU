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
 * @package    library.massiveart
 * @copyright  Copyright (c) 2008-2009 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Community_Controller_Plugin_Tidy
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2012-08-04: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package Community
 * @subpackage Controller.Plugin.Tidy
 */
class TidyControllerPlugin extends Zend_Controller_Plugin_Abstract
{

    /**
     * dispatch loop shutdown
     * @return void
     * @author Thomas Schedler <tsh@massiveart.com>
     */
    public function dispatchLoopShutdown()
    {
        $blnIsHtmlResponse = true;
        if (count($arrHeaders = $this->getResponse()->getHeaders()) > 0) {
            foreach ($arrHeaders as $arrHeader) {
                if ($arrHeader['name'] == 'Content-Type' && $arrHeader['value'] != 'text/html') {
                    $blnIsHtmlResponse = false;
                    break;
                }
            }
        }

        if (function_exists('tidy_parse_string') && !$this->getRequest()->isXmlHttpRequest() && $this->getResponse()->getBody() != '' && $blnIsHtmlResponse) {
            /*
             * Tidy is a binding for the Tidy HTML clean and repair utility which allows
             * you to not only clean and otherwise manipulate HTML documents,
             * but also traverse the document tree.
             */
            $arrConfig = array(
                'tidy-mark'           => false,
                'indent'              => true,
                'indent-spaces'       => 4,
                'new-blocklevel-tags' => 'article,aside,details,figcaption,figure,footer,header,hgroup,nav,section',
                'new-inline-tags'     => 'video,audio,canvas',
                'doctype'             => '<!doctype html>',
                'sort-attributes'     => 'alpha',
                'vertical-space'      => false,
                'output-xhtml'        => true,
                'wrap'                => 200,
                'wrap-attributes'     => false,
                'break-before-br'     => false,
            );

            $objTidy = tidy_parse_string($this->getResponse()->getBody(), $arrConfig, 'UTF8');
            $objTidy->cleanRepair();

            $objTidy = str_replace('<!--%TIDY_DOCTYPE%-->', '<!doctype html>', $objTidy);

            $this->getResponse()->setBody($objTidy);
        }
    }

}