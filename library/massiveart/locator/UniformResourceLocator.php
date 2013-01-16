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
 * 1.0, 2012-11-13: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.locator
 * @subpackage Url
 */
class UniformResourceLocator
{
    const LAYOUT_TREE = 'tree';
    const LAYOUT_SHORT = 'short';

    /**
     * @var Model_Urls
     */
    private $modelUrls;

    /**
     * @var int
     */
    protected $rootLevelId;

    /**
     * @var string
     */
    protected $formType;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    protected $layout;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $languageCode;

    /**
     * @var int
     */
    protected $parentId;

    /**
     * @var array
     */
    protected $parents;

    /**
     * @var array
     */
    protected $replacers = array();

    /**
     * @var bool
     */
    protected $isStartElement;

    /**
     * @param $layout
     * @param $modelUrls
     */
    public function __construct($layout, $modelUrls)
    {
        $this->layout = $layout;
        $this->modelUrls = $modelUrls;
    }

    /**
     * @param bool $makeUnique
     */
    public function build($makeUnique = true)
    {
        switch ($this->layout) {
            case self::LAYOUT_SHORT:

                $this->url = $this->makeConform($this->path);

                if ($makeUnique) {
                    if (!$this->checkUniqueness($this->url)) {
                        $this->url = $this->makeShortUnique($this->url);
                    }
                }

                break;

            case self::LAYOUT_TREE:
            default:

                if (count($this->parents) > 1 || (count($this->parents) > 0 && !$this->isStartElement)) {
                    $this->url = $this->makeConform($this->prefix . $this->path);
                } else {
                    $this->url = $this->makeConform($this->path);
                }

                if ($makeUnique) {
                    $this->url = $this->makeUnique($this->url);
                }
        }
    }

    /**
     * @param $url
     * @param int $addon
     * @return string
     */
    public function makeUnique($url, $addon = 0)
    {
        if (rtrim($url, '/') != $url) {
            $newUrl = ($addon > 0) ? rtrim($url, '/') . '-' . $addon . '/' : $url;
        } else {
            $newUrl = ($addon > 0) ? $url . '-' . $addon : $url;
        }

        if (!$this->checkUniqueness($newUrl)) {
            return $this->makeUnique($url, $addon + 1);
        } else {
            return $newUrl;
        }
    }

    /**
     * @param $url
     * @return string
     */
    public function makeShortUnique($url)
    {

        $first = true;

        foreach ($this->parents as $parentFolder) {
            if (!($first && $this->isStartElement)) {

                $url = $this->makeConform(strtolower($parentFolder->title)) . '/' . $url;

                if ($this->checkUniqueness($url)) {
                    break;
                }
            }

            $first = false;
        }

        if (!$this->checkUniqueness($url)) {
            $url = $this->makeUnique($url);
        }

        return $url;
    }

    /**
     * @param $url
     * @return bool
     */
    public function checkUniqueness($url)
    {
        $urls = $this->modelUrls->loadByUrl($this->rootLevelId, $url, $this->formType);

        if (isset($urls->url) && count($urls->url) > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $path
     * @return mixed
     */
    public function makeConform($path)
    {
        $path = strtolower($path);

        // replace problematic characters
        if (count($this->replacers) > 0) {
            foreach ($this->replacers as $rePlacer) {
                $path = str_replace($rePlacer->from, $rePlacer->to, $path);
            }
        }

        $path = strtolower($path);

        // delete problematic characters
        $path = str_replace('%2F', '/', urlencode(preg_replace('/([^A-za-z0-9\s-_\/])/', '', $path)));

        $path = str_replace('+', '-', $path);

        // replace multiple minus with one
        $path = preg_replace('/([-]+)/', '-', $path);

        // delete minus at the beginning or end
        $path = preg_replace('/^([-])/', '', $path);
        $path = preg_replace('/([-])$/', '', $path);

        return $path;
    }

    /**
     * @param bool $makeUnique
     * @param bool $withLanguageCode
     * @return string
     */
    public function get($makeUnique = true, $withLanguageCode = false)
    {
        // is folder
        if ($this->isStartElement && !empty($this->parentId)) {
            $this->path = rtrim($this->path, '/') . '/';
        }

        $this->build($makeUnique);

        if (!empty($this->languageCode) && $withLanguageCode) {
            return $this->languageCode . '/' . $this->url;
        } else {
            return $this->url;
        }

    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param string $path
     * @return UniformResourceLocator
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $prefix
     * @return UniformResourceLocator
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }


    /**
     * @param string $languageCode
     * @return UniformResourceLocator
     */
    public function setLanguageCode($languageCode)
    {
        $this->languageCode = $languageCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * @param array $parents
     * @return UniformResourceLocator
     */
    public function setParents($parents)
    {
        $this->parents = $parents;
        return $this;
    }

    /**
     * @return array
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * @param int $parentId
     * @return UniformResourceLocator
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param bool $isStartElement
     * @return UniformResourceLocator
     */
    public function setIsStartElement($isStartElement)
    {
        $this->isStartElement = $isStartElement;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsStartElement()
    {
        return $this->isStartElement;
    }

    /**
     * @param array $replacers
     * @return UniformResourceLocator
     */
    public function setReplacers($replacers)
    {
        $this->replacers = $replacers;
        return $this;
    }

    /**
     * @return array
     */
    public function getReplacers()
    {
        return $this->replacers;
    }

    /**
     * @param int $rootLevelId
     * @return UniformResourceLocator
     */
    public function setRootLevelId($rootLevelId)
    {
        $this->rootLevelId = $rootLevelId;
        return $this;
    }

    /**
     * @return int
     */
    public function getRootLevelId()
    {
        return $this->rootLevelId;
    }

    /**
     * @param int $type
     * @return UniformResourceLocator
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $formType
     * @return UniformResourceLocator
     */
    public function setFormType($formType)
    {
        $this->formType = $formType;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormType()
    {
        return $this->formType;
    }

}
