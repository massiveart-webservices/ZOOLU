<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if IE 9]>    <html class="no-js lt-ie10" lang="en"> <![endif]-->
<!-- Consider adding a manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en">
<!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->translate->_('Search'); ?> - ZOOLU Default
    Theme</title>

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
<meta name="robots"
    content="<?php echo ((APPLICATION_ENV == 'production') ? 'noindex, follow' : 'noindex, nofollow'); ?>" />

<!-- SCREEN CSS -->
<link type="text/css" rel="stylesheet"
    href="<?php get_static_component_domain() ?>/website/themes/default/css/style.css?v=<?php echo getCoreObject()->config->version->css; ?>" />

<?php if(Zend_Auth::getInstance()->hasIdentity()) : ?>
<link rel="stylesheet" type="text/css" media="screen"
    href="<?php get_static_component_domain() ?>/website/themes/default/css/modus.css"></link>
<script type="text/javascript"
    src="<?php get_static_component_domain() ?>/website/themes/default/js/modus.js"></script>
<?php endif; ?>

<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<script src="/website/themes/default/js/libs/modernizr-2.5.3.min.js"></script>
</head>

<body>
    <?php get_zoolu_header(); ?>
    <div id="wrap">
        <div id="main" class="clearfix">      
        <!-- header and main navigation -->
        <?php include dirname(__FILE__).'/includes/header.inc.php'; ?>
            <div class="top">
                <div class="inner">
                    <!-- TODO : header image or flash -->
                    <div class="clear"></div>
                </div>
            </div> <!-- /.top -->
            <!-- Content -->
            <div class="contentContainer search-content">
                <div class="inner">
                    <h1 class="title">
                        <?php echo $this->translate->_('Search'); ?>
                    </h1>
                    <div class="column-cont">
                        <?php
                            require_once (GLOBAL_ROOT_PATH.'application/website/default/views/helpers/SearchHelper.php');
                            $objHelper = new SearchHelper();
                            if($this->hasSegments){
                                $objHelper->setSegmentId($this->segmentId);
                                $objHelper->setSegmentCode($this->segmentCode);
                            }
                            echo $objHelper->getSearchList($this->objHits, $this->strSearchValue, $this->translate);
                        ?>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <!-- /.content -->
            <div id="push">&nbsp;</div>
        </div>
        <!-- /#main -->
    </div>
    <!-- /#wrap -->

    <!-- Footer -->
    <?php include dirname(__FILE__).'/includes/footer.inc.php'; ?>

    <script
        src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="<?php get_static_component_domain() ?>/website/themes/default/js/libs/jquery-1.7.2.min.js?v=<?php echo getCoreObject()->sysConfig->version->js; ?>"><\/script>')</script>

    <script type="text/javascript"
        src="<?php get_static_component_domain() ?>/website/themes/default/js/libs.min.js?v=<?php echo getCoreObject()->sysConfig->version->js; ?>"></script>
    <script type="text/javascript"
        src="<?php get_static_component_domain() ?>/website/themes/default/js/script.min.js?v=<?php echo getCoreObject()->sysConfig->version->js; ?>"></script>

    <script type="text/javascript">
    
        $(document).ready(function() {
            jQuery('#searchField').liveSearch({
                url: '/zoolu-website/search/livesearch?theme=default&rootLevelId=<?php echo $this->rootLevelId; ?>&languageId=<?php echo $this->languageId; ?>&q='
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
