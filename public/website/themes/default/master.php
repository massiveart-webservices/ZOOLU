<!--%TIDY_DOCTYPE%-->
<html xmlns="http://www.w3.org/1999/xhtml">
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en">  <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang="en">  <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang="en">  <![endif]-->
<!--[if IE 9]>         <html class="no-js lt-ie10" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php get_meta_title(); ?></title>
    <?php get_meta_robots(); ?>
    <?php get_meta_description(); ?>
    <?php get_meta_keywords(); ?>
  
    <meta name="publisher" content="MASSIVE ART WebServices GmbH" />
    <meta name="author" content="ZOOLU Default Theme" />
    <meta name="copyright" content="ZOOLU Default Theme" />
    <meta name="DC.Title" content="ZOOLU Default Theme" />
    <meta name="DC.Publisher" content="MASSIVE ART WebServices GmbH" />
    <meta name="DC.Copyright" content="ZOOLU Default Theme" />
    
    <meta name="distribution" content="all" />
    <meta name="revisit-after" content="2 days" />
    
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="cache-control" content="private" />
    
    <meta name="viewport" content="width=device-width" />
    
    <meta name="audience" content="alle" />
    
    <?php get_canonical_tag(); ?>
    <?php //get_canonical_tag_for_segmentation(); - if segmentation is used, enable canonical tag ?>

    <!-- SCREEN CSS -->
    <link type="text/css" rel="stylesheet" href="<?php get_static_component_domain() ?>/website/themes/default/css/style.css" />

    <?php if(Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session())->hasIdentity()) : ?>
    <link rel="stylesheet" type="text/css" media="screen" href="<?php get_static_component_domain() ?>/website/themes/default/css/modus.css"></link>
    <script type="text/javascript" src="<?php get_static_component_domain() ?>/website/themes/default/js/modus.js"></script>
    <?php endif; ?>

    <link rel="shortcut icon" href="<?php get_static_component_domain() ?>/favicon.ico" type="image/x-icon"></link>
    <script src="<?php get_static_component_domain() ?>/website/themes/default/js/libs/modernizr-2.5.3.min.js?v=<?php echo getCoreObject()->sysConfig->version->js; ?>"></script>
</head>

<body>
    <?php get_zoolu_header(); ?>
    <div id="wrap">
        <div id="main" class="clearfix">      
        <!-- header and main navigation -->
        <?php include dirname(__FILE__).'/includes/header.inc.php'; ?>
      
        <!-- Template Content -->
        <?php get_content($this); ?>
        </div> <!-- /#main -->
    </div> <!-- /#wrap --> 

    <!-- Footer -->
    <?php include dirname(__FILE__).'/includes/footer.inc.php'; ?>
  
    <?php get_bottom_content(); ?>
  
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="<?php get_static_component_domain() ?>/website/themes/default/js/libs/jquery-1.7.2.min.js?v=<?php echo getCoreObject()->sysConfig->version->js; ?>"><\/script>')</script>

    <script type="text/javascript" src="<?php get_static_component_domain() ?>/website/themes/default/js/libs.min.js?v=<?php echo getCoreObject()->sysConfig->version->js; ?>"></script>
    <script type="text/javascript" src="<?php get_static_component_domain() ?>/website/themes/default/js/script.min.js?v=<?php echo getCoreObject()->sysConfig->version->js; ?>"></script>
  
    <script type="text/javascript">
    
        $(document).ready(function() {
            jQuery('#searchField').liveSearch({
                url: '/zoolu-website/search/livesearch?theme=default&rootLevelId=<?php echo $this->rootLevelId; ?>&languageId=<?php echo $this->languageId; ?>&languagecode=<?php echo $this->languageCode; ?>&languageDefinitionType=<?php echo get_language_definition_type(); ?>&searchBase=<?php get_search_action(); ?>&q=',
                defaultValue: '<?php echo $this->translate->_('Search', false); ?>'
            });
            <?php get_dom_loaded_js(); ?>
        });

        <?php /*if (APPLICATION_ENV == 'production'):
        var MTIProjectId = 'c03d4ed8-4caf-444d-9051-664f04ddd458';
        (function() {
            var mtiTracking = document.createElement('script');
            mtiTracking.type = 'text/javascript';
            mtiTracking.async = 'true';
            mtiTracking.src = ('https:' == document.location.protocol ? 'https:' : 'http:') + '//fast.fonts.com/t/trackingCode.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(mtiTracking);
        })();
        endif;*/ ?>

        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', '<?php echo $this->analyticsKey; ?>']);
        _gaq.push(['_setDomainName', '<?php echo $_SERVER['HTTP_HOST']; ?>']);
        _gaq.push(['_setAllowLinker', true]);
        _gaq.push(['_trackPageview']);

        (function() {
            var ga = document.createElement('script');
            ga.type = 'text/javascript';
            ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(ga, s);
        })();
    </script>  
</body>
</html>
