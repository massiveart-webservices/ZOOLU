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
 * @package    library.massiveart.generic.fields.InternalLink.forms.helpers
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */

/**
 * Form_Helper_SitemapLink
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 * @package massiveart.forms.helpers
 * @subpackage Form_Helper_SitemapLink
 */
class Form_Helper_FormSitemapLink extends Zend_View_Helper_FormElement
{
    public function formSitemapLink($name, $value = null, $attribs = null, $options = null, Form_Element_SitemapLink $element)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        $core = Zend_Registry::get('Core');
        extract($info);

        $strOutput = '
                <div class="linkedpage" id="sitemapLink_' . $id . '">
                  <span class="big" id="sitemapLinkBreadcrumb_' . $id . '">' . $this->view->escape($element->strLinkedPageBreadcrumb) . '</span><span class="bold big" id="sitemapLinkTitle_' . $id . '">' . $this->view->escape($element->strLinkedPageTitle) . '</span> (<a href="#" onclick="myForm.getAddSitemapLinkOverlay(\'' . $id . '\'); return false;">' . $core->translate->_('Select_page') . '</a>)<br/>
                  <!--<span class="small" id="sitemapLinkUrl_' . $id . '"><a href="' . $element->strLinkedPageUrl . '" target="_blank">' . $this->view->escape($element->strLinkedPageUrl) . '</a></span>-->
                  <input type="hidden" value="' . $this->view->escape($element->relationId) . '" id="sitemapLinkRelation_' . $id . '" name="sitemapLinkRelation_' . $id . '" />
                  <input type="hidden" value="' . $this->view->escape($element->intParentId) . '" id="sitemapLinkParent_' . $id . '" name="sitemapLinkParent_' . $id . '" />
                  <input type="hidden" value="' . $this->view->escape($element->strType) . '" id="sitemapLinkType_' . $id . '" name="sitemapLinkType_' . $id . '" />
                </div>';

        return $strOutput;
    }
}