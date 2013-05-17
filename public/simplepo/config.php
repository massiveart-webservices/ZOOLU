<?php

require_once(dirname(__FILE__) . '/../../sys_config/general.inc.php');



$simplepo_config = array();
$simplepo_config['db_user'] = $core->sysConfig->database->params->username;
$simplepo_config['db_pass'] = $core->sysConfig->database->params->password;
$simplepo_config['db_host'] = $core->sysConfig->database->params->host;
$simplepo_config['db_name'] = $core->sysConfig->database->params->dbname;
$simplepo_config['ref_catalogue_id'] = 3;
$simplepo_config['suggestion_language'] = 'English';
$simplepo_config['table_prefix'] = "simplepo_";

MaConfig::setRefCatalogueId($simplepo_config['ref_catalogue_id']);
MaConfig::setSuggestionLanguage($simplepo_config['suggestion_language']);

/**
 * MaConfig
 * 
 * @author Dominik Matt
 */
class MaConfig 
{
	private static $catalogueId = null;
	private static $suggestionLanguage = null;
	
	/**
	 * 
	 * getRefCatalogouteId
	 * 
	 * @return int
	 * 
	 * @author Dominik Matt <dma@massiveart.com>
	 */
	public static function getRefCatalogueId()
	{
		return self::$catalogueId;
	}
	
	
	/**
	 *
	 * setRefCatalogouteId
	 *
	 * @return int
	 *
	 * @author Dominik Matt <dma@massiveart.com>
	 */
	public static function setRefCatalogueId($id = null)
	{
		if($id != null) {
			self::$catalogueId = $id;
			return true;
		}
		return false;
	}
	
	/**
	 *
	 * getSuggestionLanguage
	 *
	 * @author Dominik Matt <dma@massiveart.com>
	 */
	public static function getSuggestionLanguage()
	{
		return self::$suggestionLanguage;
	}
	
	/**
	 *
	 * setSuggestionLanguage
	 *
	 * @return int
	 *
	 * @author Dominik Matt <dma@massiveart.com>
	 */
	public static function setSuggestionLanguage($language = null)
	{
		if($language != null) {
			self::$suggestionLanguage = $language;
			return true;
		}
		return false;
	}
}
?>