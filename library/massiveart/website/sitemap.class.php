<?php

/**
 * Sitemap
 *
 *
 * Version history (please keep backward compatible):
 * 1.0, 2010-08-03: Thomas Schedler
 *
 * @author Thomas Schedler <tsh@massiveart.com>
 * @version 1.0
 * @package massiveart.website
 * @subpackage Sitemap
 */

class Sitemap
{

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Model_Folders
     */
    private $objModelFolders;

    /**
     * @var DOMDocument
     */
    private $objDoc;

    /**
     * @var DOMElement
     */
    private $objUrlset;

    /**
     * @var String
     */
    private $strSitemapPath;

    /**
     * @var Array
     */
    private $arrUrls = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->core = Zend_Registry::get('Core');
    }

    /**
     * generate
     * @return void
     */
    public function generate()
    {
        $this->core->logger->debug('massiveart->website->sitemap->generate()');

        $objRootLevels = $this->getModelFolders()->loadAllRootLevels($this->core->sysConfig->modules->cms);
        
        if (count($objRootLevels) > 0) {
            foreach ($objRootLevels as $objRootLevel) {
                $objRootLevelLanguages = $this->getModelFolders()->loadRootLevelLanguages($objRootLevel->id);
                $objUrl = $this->getModelFolders()->getRootLevelMainUrl($objRootLevel->id, $this->core->sysConfig->environments->production, false, true);
                if ($objUrl != null) {
                    // create xml for rootlevel which contains the languages, cause language is not specified in subdomain
                    if ($objRootLevel->languageDefinitionType != $this->core->config->language_definition->subdomain) {
                        $strUrl = $this->makeUrlForSitemap($objUrl, $objRootLevel->languageDefinitionType);
                        $this->initXml($strUrl);
                    }
                    foreach ($objRootLevelLanguages as $objRootLevelLanguage) {
                        $strUrl = $this->makeUrlForSitemap($objUrl, $objRootLevel->languageDefinitionType, $objRootLevelLanguage, $objRootLevel->idDefaultLanguage);
                        
                        // create xml for every langauge per rootLevel, cause language is specified in subdomain
                        if ($objRootLevel->languageDefinitionType == $this->core->config->language_definition->subdomain) {
                            $this->initXml($strUrl);                        
                        }
                        
                        // get the sitemap 
                        $objNavigation = new Navigation();
                        $objNavigation->setRootLevelId($objRootLevel->id);
                        $objNavigation->setLanguageId($objRootLevelLanguage->id);
                        $objNavigation->setLanguageDefinitionType($objRootLevel->languageDefinitionType);
                        $this->addXmlUrlsetChilds($objNavigation->loadSitemap(), $strUrl, strtolower($objRootLevelLanguage->languageCode));
                        
                        // save xml for every langauge per rootLevel, cause language is specified in subdomain
                        if ($objRootLevel->languageDefinitionType == $this->core->config->language_definition->subdomain) {
                            $this->saveXml();
                        }
                    }
                    // save xml for rootlevel which contains the languages, cause language is not specified in subdomain
                    if ($objRootLevel->languageDefinitionType != $this->core->config->language_definition->subdomain) {
                        $this->saveXml();
                    }
                }
                
            }
        }
    }
    
    /**
     * makeUrlForSitemap
     * @param string $strUrl
     * @return string
     */
    private function makeUrlForSitemap($objUrl, $intLanguageDefinitionType, $objRootLevelLanguage = null , $intDefaultLanguageId = null) {
        $strMainUrl = $objUrl->url;
        $strMainUrl = str_replace('http://', '', $strMainUrl);
        
        if ($intLanguageDefinitionType == $this->core->config->language_definition->subdomain) {
            if ($objRootLevelLanguage->id == $intDefaultLanguageId && $objUrl->hostPrefix != '') {
                $strMainUrl = $objUrl->hostPrefix . '.' . $strMainUrl;
            } else {
                $strMainUrl = strtolower($objRootLevelLanguage->languageCode) . '.' . $strMainUrl;
            }
        } else {
              $arrUrlParts = explode('.', $strMainUrl);
              if(count($arrUrlParts) == 2){
                $strMainUrl = 'www.' . $strMainUrl;
              }   
        }
        return 'http://' . $strMainUrl;
    }

    /**
     * initXml
     * @param string $strUrl
     * @return void
     */
    private function initXml($strUrl)
    {
        $this->strSitemapPath = GLOBAL_ROOT_PATH . 'public/sitemaps/' . str_replace('http://', '', $strUrl);
        if (!is_dir($this->strSitemapPath)) {
            mkdir($this->strSitemapPath, 0775, true);
        }

        $this->objDoc = new DOMDocument('1.0', 'UTF-8');
        // we want a nice output
        $this->objDoc->formatOutput = true;

        $this->objUrlset = $this->objDoc->createElement('urlset');
        $objXmlns = $this->objDoc->createAttribute('xmlns');
        $objXmlnsValue = $this->objDoc->createTextNode('http://www.sitemaps.org/schemas/sitemap/0.9');
        $objXmlns->appendChild($objXmlnsValue);
        $this->objUrlset->appendChild($objXmlns);
        $this->objUrlset = $this->objDoc->appendChild($this->objUrlset);

        $this->arrUrls = array($strUrl);
        $objUrl = $this->objDoc->createElement('url');
        $objUrl->appendChild($this->objDoc->createElement('loc', $strUrl));
        $objUrl->appendChild($this->objDoc->createElement('lastmod', date('Y-m-d')));
        $objUrl->appendChild($this->objDoc->createElement('changefreq', 'daily'));
        $objUrl->appendChild($this->objDoc->createElement('priority', '1'));
        $objUrl = $this->objUrlset->appendChild($objUrl);
    }

    /**
     * saveXml
     * @return void
     */
    private function saveXml()
    {
        $this->objDoc->save($this->strSitemapPath . '/sitemap.xml');
    }

    /**
     * addXmlUrlsetChilds
     * @param NavigationItem|NavigationTree $objItem
     * @param string $strUrl
     * @param string $strLanguageCode
     * @return void
     */
    public function addXmlUrlsetChilds($objItem, $strUrl, $strLanguageCode, $intLevel = 0)
    {

        if ($objItem->getUrl() != '' && $objItem->getTypeId() != $this->core->sysConfig->page_types->external->id) {
            $strItemUrl = (strpos($objItem->getUrl(), 'http://') !== false) ? str_replace('&', '&amp;', $objItem->getUrl()) : $strUrl . str_replace('&', '&amp;', $objItem->getUrl());
            if (!array_search($strItemUrl, $this->arrUrls)) {
                $this->arrUrls[] = $strItemUrl;
                $objUrl = $this->objDoc->createElement('url');
                $objUrl->appendChild($this->objDoc->createElement('loc', $strItemUrl));
                if ($objItem->getUrl() == '/' . $strLanguageCode . '/') {
                    $objUrl->appendChild($this->objDoc->createElement('lastmod', date('Y-m-d')));
                } else if ($objItem->getChanged('', true) !== null) {
                    $objUrl->appendChild($this->objDoc->createElement('lastmod', $objItem->getChanged('Y-m-d')));
                }
                if ($objItem->getUrl() == '/' . $strLanguageCode . '/') {
                    $objUrl->appendChild($this->objDoc->createElement('changefreq', 'daily'));
                    $objUrl->appendChild($this->objDoc->createElement('priority', '0.8'));
                }
                $objUrl = $this->objUrlset->appendChild($objUrl);
            }
        }

        if ($objItem instanceof NavigationTree) {
            foreach ($objItem as $objChild) {
                $this->addXmlUrlsetChilds($objChild, $strUrl, $strLanguageCode, $intLevel + 1);
            }
        }
    }

    /**
     * getModelFolders
     * @return Model_Folders
     * @author Cornelius Hansjakob <cha@massiveart.com>
     * @version 1.0
     */
    protected function getModelFolders()
    {
        if (null === $this->objModelFolders) {
            /**
             * autoload only handles "library" compoennts.
             * Since this is an application model, we need to require it
             * from its modules path location.
             */
            require_once GLOBAL_ROOT_PATH . $this->core->sysConfig->path->zoolu_modules . 'core/models/Folders.php';
            $this->objModelFolders = new Model_Folders();
            $this->objModelFolders->setLanguageId($this->core->sysConfig->languages->default->id);
        }

        return $this->objModelFolders;
    }
}

?>