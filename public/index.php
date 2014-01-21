<?php
/**
 * index.php
 *
 * @author Cornelius Hansjakob <cha@massiveart.com>
 * @version 1.0
 */

/**
 * include general (autoloader, config)
 */
require_once(dirname(__FILE__).'/../sys_config/general.inc.php');

try{
    /**
     * Get the front controller instance
     */
    $front = Zend_Controller_Front::getInstance();
    $front->setControllerDirectory('../application/website/default/controllers');
    $front->addControllerDirectory('../application/zoolu/modules/core/controllers', 'zoolu');
    $front->addModuleDirectory('../application/zoolu/modules');

    /**
     * add helper path
     */
    $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer(new Zend_View());
    /**
     * Add the own plugin loader
     */
    $objLoader = new PluginLoader();
    $objLoader->setPluginLoader($viewRenderer->view->getPluginLoader(PluginLoader::TYPE_FORM_HELPER));
    $objLoader->setPluginType(PluginLoader::TYPE_FORM_HELPER);
    $viewRenderer->view->setPluginLoader($objLoader, PluginLoader::TYPE_FORM_HELPER);

    //$viewRenderer->view->addHelperPath('../library/massiveart/generic/forms/helpers', 'Form_Helper');
    Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

    /**
     * if log priority is DEBUG and firebug logging is true add firebug writer to logger
     * (needs an instance of Zend_Controller_Front)
     */
    if($sysConfig->logger->priority == Zend_Log::DEBUG && $sysConfig->logger->firebug == 'true'){
        $writerFireBug = new Zend_Log_Writer_Firebug();
        $core->logger->addWriter($writerFireBug);
    }

    /**
     * Routing for modules (cms, blog, ...)
     */
    $router = $front->getRouter();

    /**
     * default website routing regex
     */
    $route = new Zend_Controller_Router_Route_Regex('(?!(^zoolu))(.*)', array('controller' => 'Index',
                                                                              'action'     => 'index'));
    $router->addRoute('index', $route);

    /**
     * default zoolu routings
     */
    $route = new Zend_Controller_Router_Route('zoolu/:module');
    $router->addRoute('zoolu', $route);

    $route = new Zend_Controller_Router_Route('zoolu/:module/:controller');
    $router->addRoute('zooluController', $route);

    $route = new Zend_Controller_Router_Route('zoolu/:module/:controller/:action/*');
    $router->addRoute('zooluControllerAction', $route);
    
    /**
     * rss route
     */
    $route = new Zend_Controller_Router_Route_Regex('rss/(.*)/(.*)', array('controller'  => 'Rss'), array('language' => 1, 'action' => 2));
    $router->addRoute('rss', $route);

    /**
     * default zoolu-website routings
     */
    $route = new Zend_Controller_Router_Route('zoolu-website/:controller');
    $router->addRoute('zooluWebController', $route);

    $route = new Zend_Controller_Router_Route('zoolu-website/:controller/:action/*');
    $router->addRoute('zooluWebControllerAction', $route);

    $route = new Zend_Controller_Router_Route('zoolu-website/:controller/:action/:id/*');
    $router->addRoute('zooluWebControllerActionParams', $route);

    /**
     * default subscriber routings
     */
    $route = new Zend_Controller_Router_Route('subscribe', array('controller' => 'Subscriber', 'action' => 'subscribe'));
    $router->addRoute('subscriberWebControllerAction', $route);

    $route = new Zend_Controller_Router_Route('unsubscribe', array('controller' => 'Subscriber', 'action' => 'unsubscribe'));
    $router->addRoute('subscriberWebControllerActionParams', $route);

    /**
     * default customer routings
     */
    $route = new Zend_Controller_Router_Route_Static('login', array('controller' => 'Customer', 'action' => 'login'));
    $router->addRoute('customerWebControllerLogin', $route);

    $route = new Zend_Controller_Router_Route_Static('logout', array('controller' => 'Customer', 'action' => 'logout'));
    $router->addRoute('customerWebControllerLogout', $route);

    $route = new Zend_Controller_Router_Route_Static('register', array('controller' => 'Customer', 'action' => 'register'));
    $router->addRoute('customerWebControllerRegister', $route);

    $route = new Zend_Controller_Router_Route_Static('reset', array('controller' => 'Customer', 'action' => 'reset'));
    $router->addRoute('customerWebControllerReset', $route);

    /**
     * only throw exceptions in developement mode
     */
    if($sysConfig->show_errors === 'false'){
        $front->throwExceptions(false);
    } else {
        $front->throwExceptions(true);
    }

    /**
     * *** to debug ***
     * echo "<pre>";
     * print_r($_SESSION);
     * echo "</pre>";
     */

    /*
     $arrFrontendOptions = array(
     'lifetime' => null, // cache lifetime (in seconds), if set to null, the cache is valid forever.
     'default_options' => array(
     'cache_with_get_variables' => false,
     'cache_with_post_variables' => false,
     'cache_with_session_variables' => true,
     'cache_with_files_variables' => false,
     'cache_with_cookie_variables' => true,
     'make_id_with_get_variables' => true,
     'make_id_with_post_variables' => true,
     'make_id_with_session_variables' => false,
     'make_id_with_files_variables' => true,
     'make_id_with_cookie_variables' => false,
     'cache' => true,
     'specific_lifetime' => false,
     'tags' => array(),
     'priority' => null
     ),
     'automatic_serialization' => true
     );

     $arrBackendOptions = array(
     'cache_dir' => GLOBAL_ROOT_PATH.$this->core->sysConfig->path->cache->pages // Directory where to put the cache files
     );

     // getting a Zend_Cache_Core object
     $objCache = Zend_Cache::factory('Page',
     'File',
     $arrFrontendOptions,
     $arrBackendOptions);
     $objCache->start();
     */

    /**
     * Go Go Go!
     */
    $front->dispatch();


    /**
     * profiling sql queries

     if($core->sysConfig->logger->priority == Zend_Log::DEBUG){
     $objDbhProfiler = $core->dbh->getProfiler();
     $totalTime    = $objDbhProfiler->getTotalElapsedSecs();
     $queryCount   = $objDbhProfiler->getTotalNumQueries();
     $longestTime  = 0;
     $longestQuery = null;

     foreach ($objDbhProfiler->getQueryProfiles() as $query) {
     if($query->getElapsedSecs() > $longestTime){
     $longestTime  = $query->getElapsedSecs();
     $longestQuery = $query->getQuery();
     }
     }

     $core->logger->debug('Executed '.$queryCount.' queries in '.$totalTime.' seconds.');
     $core->logger->debug('Average query length: '.($totalTime / $queryCount).' seconds');
     $core->logger->debug('Queries per second: '.($queryCount / $totalTime));
     $core->logger->debug('Longest query length: '.$longestTime);
     $core->logger->debug('Longest query: '.$longestQuery);
     }
     */
}catch(Exception $exc){
    $core->logger->err($exc);
}

?>
