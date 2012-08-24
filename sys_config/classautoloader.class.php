<?php

/**
 * ClassAutoLoader
 *
 * Version history (please keep backward compatible):
 * 1.0, 2008-10-09: Cornelius Hansjakob
 * 1.1, 2009-05-05: Cornelius Hansjakob (Zend 1.8 - new Autoloader)
 * 1.2, 2009-07-28: Daniel Rotter - Added own PluginLoader and abstract FormElement
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

/**
 * include class Zend_Loader
 */
require_once('Zend/Loader/Autoloader.php');

class ClassAutoLoader extends Zend_Loader_Autoloader {

  /**
   * class / class-path definition
   * @var  array
   */
  public static $arrClasses = array(
    'Core'                          => '/library/massiveart/core.class.php',
    'AuthControllerAction'          => '/library/massiveart/controllers/auth.controller.action.class.php',
    'ModuleControllerAction'        => '/library/massiveart/controllers/module.controller.action.class.php',
    'AjaxControllerAction'          => '/library/massiveart/controllers/ajax.controller.action.class.php',
    'Chart'                         => '/library/massiveart/chart/chart.class.php',
    'ClientHelper'                  => '/library/massiveart/client/client.helper.class.php',
    'ClientHelperInterface'         => '/library/massiveart/client/client.helper.interface.php',
    'HtmlOutput'                    => '/library/massiveart/utilities/htmlOutput.class.php',
    'Crypt'                         => '/library/massiveart/utilities/crypt.class.php',
    'FormHandler'                   => '/library/massiveart/generic/forms/form.handler.class.php',
    'GenericForm'                   => '/library/massiveart/generic/forms/generic.form.class.php',
    'GenericData'                   => '/library/massiveart/generic/data/generic.data.class.php',
    'GenericSetup'                  => '/library/massiveart/generic/generic.setup.class.php',
    'File'                          => '/library/massiveart/files/file.class.php',
    'Image'                         => '/library/massiveart/images/image.class.php',
    'ImageManipulation'             => '/library/massiveart/images/image.manipulation.class.php',
    'ImageValidator'                => '/library/massiveart/validators/image.validator.class.php',
    'ImageResizeFactory'            => '/library/massiveart/images/image.resize.factory.class.php',
    'Document'                      => '/library/massiveart/documents/document.class.php',
    'DocumentValidator'             => '/library/massiveart/validators/document.validator.class.php',
    'NestedSet'                     => '/library/massiveart/trees/nested.set.class.php',
    'Website'                       => '/library/massiveart/website.class.php',
    'Page'                          => '/library/massiveart/website/page.class.php',
    'PageContainer'                 => '/library/massiveart/website/page/container.class.php',
    'PageEntry'                     => '/library/massiveart/website/page/entry.class.php',
    'DownloadCenter'                => '/library/massiveart/website/download/center.class.php',
    'Navigation'                    => '/library/massiveart/website/navigation.class.php',
    'NavigationTree'                => '/library/massiveart/website/navigation/tree.class.php',
    'Search'                        => '/library/massiveart/website/search.class.php',
    'Index'                         => '/library/massiveart/website/index.class.php',
    'Sitemap'                       => '/library/massiveart/website/sitemap.class.php',
    'DateTimeHelper'                => '/library/massiveart/utilities/datetime.class.php',
    'PasswordHelper'                => '/library/massiveart/utilities/password.class.php',
    'Replacer'                      => '/library/massiveart/utilities/replacer.class.php',
    'HtmlTranslate'                 => '/library/massiveart/utilities/html.translate.class.php',
    'ReCaptchaService'              => '/library/massiveart/utilities/recaptcha.service.class.php', 
    'Security'                      => '/library/massiveart/security/security.class.php',
    'Acl'                           => '/library/massiveart/security/acl.class.php',
    'RoleProvider'                  => '/library/massiveart/security/role.provider.class.php',
    'CommandChain'                  => '/library/massiveart/command/command.chain.class.php',
    'PageCommand'                   => '/library/massiveart/command/page.command.class.php',
    'GlobalCommand'                 => '/library/massiveart/command/global.command.class.php',
    'ContactReplicationCommand'     => '/library/massiveart/command/contact/replication.command.class.php',
    'ContactReplicationInterface'   => '/library/massiveart/contact/replication/contact.replication.interface.php',
    'phMagick'                      => '/library/phmagick/phMagick.php',
    'PluginLoader'                  => '/library/massiveart/loader/pluginLoader.class.php',
    'FormElementXhtmlAbstract'      => '/library/massiveart/generic/forms/fields/form.element.xhtml.abstract.class.php',
    'FormElementMultiAbstract'      => '/library/massiveart/generic/forms/fields/form.element.multi.abstract.class.php',
    'Export'						=> '/library/massiveart/utilities/export.class.php',
    'Zip'                           => '/library/massiveart/utilities/zip.class.php',
    
    // Gearman
    'GearmanReplicationMailChimp'	  => '/library/massiveart/gearman/replication/mailchimp.replication.class.php',

    // Service
    'Service_Core'                  => '/library/massiveart/services/core.class.php',

  );

  /**
   * autoload
   * @author Cornelius Hansjakob <cha@massiveart.com>
   * @version 1.0
   */
  public static function autoload($class){
    try {
      $sysConfig = Zend_Registry::get('SysConfig');

      if(strpos($class,'Zend_') === false && strpos($class,'ZendX_') === false){
        /**
         * check if given $className exists and file exists
         */
        if(array_key_exists($class, self::$arrClasses)){
          if(file_exists(GLOBAL_ROOT_PATH.$sysConfig->path->root.self::$arrClasses[$class])){
            require_once(GLOBAL_ROOT_PATH.$sysConfig->path->root.self::$arrClasses[$class]);
          }
        }
      } else {
        /**
         * load Zend Class
         */
        parent::autoload($class);
      }

      return $class;
    }catch(Exception $e){
      return false;
    }
  }

  /**
   * Retrieve singleton instance
   *
   * @return Zend_Loader_Autoloader
   */
  public static function getInstance(){
    if(null == self::$_instance){
      self::$_instance = new self();
    }
    return self::$_instance;
  }

  /**
   * Constructor
   *
   * Registers instance with spl_autoload stack
   *
   * @return void
   */
  protected function __construct(){
    spl_autoload_register(array(__CLASS__, 'autoload'));
    $this->_internalAutoloader = array($this, '_autoload');
  }



}

/**
 * register class ClassAutoLoader
 */
#$autoloader = ClassAutoLoader::getInstance();
Zend_Loader_Autoloader::getInstance()->pushAutoloader(array('ClassAutoLoader', 'autoload'));

?>