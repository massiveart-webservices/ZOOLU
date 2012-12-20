<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if IE 9]>    <html class="no-js lt-ie10" lang="en"> <![endif]-->
<!-- Consider adding a manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo $this->translate->_('Search'); ?> - Lech Zürs Tourismus am Arlberg</title>

    <meta name="publisher" content="MASSIVE ART WebServices GmbH" />
    <meta name="author" content="Lech Zürs am Arlberg" />
    <meta name="copyright" content="Lech Zürs am Arlberg" />
    <meta name="DC.Title" content="Lech Zürs am Arlberg" />
    <meta name="DC.Publisher" content="MASSIVE ART WebServices GmbH" />
    <meta name="DC.Copyright" content="Lech Zürs am Arlberg" />

    <meta name="distribution" content="all" />
    <meta name="revisit-after" content="2 days" />

    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="cache-control" content="private" />

    <meta name="audience" content="alle" />
    <meta name="robots" content="<?php echo ((APPLICATION_ENV == 'production') ? 'noindex, follow' : 'noindex, nofollow'); ?>" />

    <!-- SCREEN CSS -->
    <link type="text/css" rel="stylesheet" href="<?php get_static_component_domain() ?>/website/themes/lechzuers/css/style.css?v=<?php echo getCoreObject()->config->version->css; ?>"/>
    <link type="text/css" rel="stylesheet" href="<?php get_static_component_domain() ?>/min/b=website/themes/lechzuers&amp;f=lightbox/css/jquery.lightbox-0.5.css,css/bootstrap.css,js/datepicker/css/custom-theme/jquery-ui-1.8.23.custom.css?v=<?php echo getCoreObject()->config->version->css; ?>"/>

    <?php if(Zend_Auth::getInstance()->hasIdentity()) : ?>
    <link rel="stylesheet" type="text/css" media="screen" href="/website/themes/lechzuers/css/modus.css"></link>
    <script type="text/javascript" src="/website/themes/lechzuers/js/modus.js"></script>
    <?php endif; ?>

    <link rel="shortcut icon" href="<?php get_static_component_domain() ?>/website/themes/lechzuers/favicon.ico" type="image/x-icon"></link>

    <script src="/website/themes/lechzuers/js/libs/modernizr-2.5.3.min.js"></script>
</head>

<body>
    <div id="wrap" class="wrapper">
        <div id="main" class="clearfix">
            <header id="header">
                <nav class="nav-main">
                    <ul class="nav-main-inner">
                        <?php get_main_navigation('li','','active', true, false,  '', '80x80'); ?>
                        <li class="nav-main-cont">
                            <div class="nav-main-item">
                                <div class="languagechooser">
                                    <?php if(getPageHelperObject()) echo getPageHelperObject()->getLanguageChooser(); ?>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <div class="clear"></div>
                        </li>
                        <li class="nav-main-cont search-cont">
                            <div class="nav-main-item">
                                <form class="search" id="searchForm">
                                    <input class="searchField" type="text" name="q" placeholder="<?php echo $this->translate->_('Search term'); ?>" />
                                    <a href="#" onclick="LZ.Search.search();"><img src="/website/themes/lechzuers/img/search.png" alt="" class="searchicon" /></a>
                                    <div class="clear"></div>
                                </form>
                                <div class="clear"></div>
                            </div>
                            <div class="jquery-live-search"></div>
                            <div class="clear"></div>
                        </li>
                        <li class="nav-main-cont logo-small">
                            <div class="nav-main-item">
                                <a href="/"><img src="/website/themes/lechzuers/img/logo_lech_zuers_small.gif" alt="Logo Lech Zürs" /></a>
                            </div>
                            <div class="clear"></div>
                        </li>
                        <li class="clear" style="width: 0; height: 0;">&nbsp;</li>
                    </ul>
                    <div class="clear"></div>
                </nav>
                <div class="clear"></div>
                <!-- Fader -->
                <div class="clear"></div>
            </header>
            <!-- Content -->
            <div class="content search-content">
                <div class="content-inner">
                    <h1 class="title"><?php echo $this->translate->_('Search'); ?></h1>
                    <div class="column-cont">
                        <?php
                          require_once (GLOBAL_ROOT_PATH.'public/website/themes/lechzuers/helpers/SearchHelper.php');
                          $objHelper = new Lechzuers_SearchHelper();
                          if($this->hasSegments){
                            $objHelper->setSegmentId($this->segmentId);
                            $objHelper->setSegmentCode($this->segmentCode);
                          }
                          $objHelper->setTranslate($this->translate);
                          if(isset($_GET['rootLevelId'])  && $_GET['rootLevelId'] != ''){
                            echo $objHelper->getSearchListSingle($this->objHits, $this->strSearchValue);
                          }else{
                            echo $objHelper->getSearchList($this->objHits, $this->strSearchValue);
                          }
                        ?>
                    </div>
                </div>
                <div class="clear"></div>
            </div> <!-- /.content -->
            <div id="push">&nbsp;</div>
        </div> <!-- /#main -->
    </div> <!-- /#wrap -->

    <!-- Footer -->
    <?php include dirname(__FILE__).'/includes/footer.inc.php'; ?>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="<?php get_static_component_domain() ?>/website/themes/lechzuers/js/libs/jquery-1.7.2.min.js"><\/script>')</script>

    <script type="text/javascript" src="<?php get_static_component_domain() ?>/min/b=website/themes/lechzuers&amp;f=js/libs/jquery-91datepicker.js,js/libs/globalize/globalize.js,js/libs/globalize/cultures/globalize.culture.de.js,js/libs/lazyload.js,js/libs/jquery.liveSearch.js,lightbox/js/jquery.lightbox-0.5.js,js/plugins.js,js/datepicker/js/jquery-ui-1.8.23.custom.min.js,js/bootstrap.min.js,js/libs/infinite-ajax-scroll/jquery.ias.min.js?v=<?php echo getCoreObject()->config->version->js; ?>"></script>
    <script type="text/javascript" src="<?php get_static_component_domain() ?>/website/themes/lechzuers/js/script.min.js?v=<?php echo getCoreObject()->config->version->js; ?>"></script>

    <script type="text/javascript">

        $(document).ready(function() {
            jQuery('.searchField').liveSearch({
                url: '/zoolu-website/search/livesearch?theme=lechzuers&languageId=<?php echo $this->languageId; ?>&languageCode=<?php echo getCoreObject()->strLanguageCode; ?>&q='
            });
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