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
 * @package    library.massiveart.loader
 * @copyright  Copyright (c) 2008-2012 HID GmbH (http://www.hid.ag)
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, Version 3
 * @version    $Id: version.php
 */
/**
 * This PluginLoader holds other PluginLoaders from Zend, for managing them.
 *
 * @author Daniel Rotter <daniel.rotter@massiveart.com>
 * @version 1.0
 * @package massiveart.loader
 * @subpackage PluginLoader
 */
class PluginLoader extends Zend_Loader_PluginLoader
{
    const TYPE_FORM_HELPER = 'helper';
    const TYPE_FORM_ELEMENT = 'element';
    const TYPE_FORM_DECORATOR = 'decorator';
    const TYPE_FORM_VALIDATOR = 'validator';

    /**
     *
     * @var Zend_Loader_PluginLoader
     */
    private $objPluginLoader;

    /**
     * Defines the Objecttype
     * @var string
     */
    private $strType;

    /**
     * Defines the objects from the core of zoolu
     * @var array
     */
    private $arrFields = array(
        'Contact', 'Document', 'Dselect', 'Group', 'InternalLink',
        'Media', 'MultiCheckboxTree', 'SelectTree', 'Tab',
        'TabContainer', 'Tag', 'Template', 'Texteditor',
        'TextDisplay', 'Url', 'InternalLinks', 'Collection',
        'DocumentFilter', 'Video', 'CollapsableInternalLinks', 'LandingPageUrl',
        'SitemapLink', 'Articles', 'Imagemap'
    );

    /**
     * Returns the internal PluginLoader
     * @return Zend_Loader_PluginLoader
     */
    public function getPluginLoader()
    {
        if (!($this->objPluginLoader instanceof Zend_Loader_PluginLoader)) {
            $this->objPluginLoader = new Zend_Loader_PluginLoader();
        }
        return $this->objPluginLoader;
    }

    /**
     * Sets the internal PluginLoader
     * @param Zend_Loader_PluginLoader $objPluginLoader
     */
    public function setPluginLoader(Zend_Loader_PluginLoader &$objPluginLoader)
    {
        $this->objPluginLoader = $objPluginLoader;
    }

    /**
     * Sets the type of the PluginLoader
     * @param $strType
     */
    public function setPluginType($strType)
    {
        $this->strType = $strType;
    }

    /**
     * Add prefixed paths to the registry of paths
     *
     * @param string $prefix
     * @param string $path
     * @return Zend_Loader_PluginLoader
     */
    public function addPrefixPath($prefix, $path)
    {
        return $this->getPluginLoader()->addPrefixPath($prefix, $path);
    }


    /**
     * Remove a prefix (or prefixed-path) from the registry
     *
     * @param string $prefix
     * @param string $path OPTIONAL
     * @return Zend_Loader_PluginLoader
     */
    public function removePrefixPath($prefix, $path = null)
    {
        return $this->getPluginLoader()->removePrefixPath($prefix, $path);
    }

    /**
     * Whether or not a Helper by a specific name
     *
     * @param string $name
     * @return Zend_Loader_PluginLoader
     */
    public function isLoaded($name)
    {
        return $this->getPluginLoader()->isLoaded($name);
    }

    /**
     * Return full class name for a named helper
     *
     * @param string $name
     * @return string
     */
    public function getClassName($name)
    {
        return $this->getPluginLoader()->getClassName($name);
    }

    /**
     * Load a helper via the name provided
     *
     * @param string $name
     * @return string
     */
    public function load($name)
    {
        //change name for checking
        $strName = str_replace('Form', '', $name);
        if (in_array(ucfirst($strName), $this->arrFields)) {
            //Field
            $strPrefixField = '';
            switch ($this->strType) {
                case self::TYPE_FORM_HELPER:
                    $strPrefixField = 'Form_Helper';
                    break;
                case self::TYPE_FORM_ELEMENT:
                    $strPrefixField = 'Form_Element';
                    break;
                case self::TYPE_FORM_DECORATOR:
                    $strPrefixField = 'Form_Decorator';
                    break;
                default:
                    $strPrefixField = 'Field_DataHelper';
            }

            $strPathField = $this->getFieldPath($name);
            $this->addPrefixPath($strPrefixField, $strPathField);
            $strClassName = $this->getPluginLoader()->load($name);

            $this->removePrefixPath($strPrefixField);
        } else {
            //Plugin
            $strPrefixPlugin = '';
            switch ($this->strType) {
                case self::TYPE_FORM_HELPER:
                    $strPrefixPlugin = 'Plugin_FormHelper';
                    break;
                case self::TYPE_FORM_ELEMENT:
                    $strPrefixPlugin = 'Plugin_FormElement';
                    break;
                case self::TYPE_FORM_DECORATOR:
                    $strPrefixPlugin = 'Plugin_FormDecorator';
                    break;
                default:
                    $strPrefixPlugin = 'Plugin_DataHelper';
            }

            //Add Plugin and Field Path
            $strPathPlugin = $this->getPluginPath($name);
            $this->addPrefixPath($strPrefixPlugin, $strPathPlugin);
            $strClassName = $this->getPluginLoader()->load($name);

            //Remove the Paths
            $this->removePrefixPath($strPrefixPlugin);
        }
        //Return the loaded classname
        return $strClassName;
    }

    /**
     * Returns the Path for the Plugin
     * @param $strPlugin
     * @return string
     */
    private function getPluginPath($strPlugin)
    {
        $strSearch = '%PLUGIN%';
        switch ($this->strType) {
            case self::TYPE_FORM_HELPER:
                $strPath = 'application/plugins/%PLUGIN%/forms/helpers';
                $strName = str_replace('Form', '', $strPlugin);
                $strName = ucfirst($strName);
                $strPath = GLOBAL_ROOT_PATH . str_replace($strSearch, $strName, $strPath);
                break;
            case self::TYPE_FORM_ELEMENT:
                $strPath = 'application/plugins/%PLUGIN%/forms/elements';
                $strName = ucfirst($strPlugin);
                $strPath = GLOBAL_ROOT_PATH . str_replace($strSearch, $strName, $strPath);
                break;
            case self::TYPE_FORM_DECORATOR:
                $strPath = 'application/plugins/%PLUGIN%/forms/decorators';
                $strName = ucfirst($strPlugin);
                $strPath = GLOBAL_ROOT_PATH . str_replace($strSearch, $strName, $strPath);
                break;
            default:
                $strPath = 'application/plugins/%PLUGIN%/data/helpers';
                $strName = ucfirst($strPlugin);
                $strPath = GLOBAL_ROOT_PATH . str_replace($strSearch, $strName, $strPath);
        }

        return $strPath;
    }

    /**
     * Returns the Path for the Field
     * @param $strField
     * @return string
     */
    private function getFieldPath($strField)
    {
        $strSearch = '%FIELD%';
        switch ($this->strType) {
            case self::TYPE_FORM_HELPER:
                $strPath = 'library/massiveart/generic/fields/%FIELD%/forms/helpers';
                $strName = str_replace('Form', '', $strField);
                $strName = ucfirst($strName);
                $strPath = GLOBAL_ROOT_PATH . str_replace($strSearch, $strName, $strPath);
                break;
            case self::TYPE_FORM_ELEMENT:
                $strPath = 'library/massiveart/generic/fields/%FIELD%/forms/elements';
                $strName = ucfirst($strField);
                $strPath = GLOBAL_ROOT_PATH . str_replace($strSearch, $strName, $strPath);
                break;
            case self::TYPE_FORM_DECORATOR:
                $strPath = 'library/massiveart/generic/fields/%FIELD%/forms/decorators';
                $strName = ucfirst($strField);
                $strPath = GLOBAL_ROOT_PATH . str_replace($strSearch, $strName, $strPath);
                break;
            default:
                $strPath = 'library/massiveart/generic/fields/%FIELD%/data/helpers';
                $strName = ucfirst($strField);
                $strPath = GLOBAL_ROOT_PATH . str_replace($strSearch, $strName, $strPath);
        }

        return $strPath;
    }
}

?>
