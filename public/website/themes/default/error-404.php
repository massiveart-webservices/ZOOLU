<!--%TIDY_DOCTYPE%-->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if IE 9]>    <html class="no-js lt-ie10" lang="en"> <![endif]-->
<!-- Consider adding a manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Lech Zürs Tourismus am Arlberg</title>

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

    <meta name="audience" content="alle" />
    <meta name="robots" content="noindex, nofollow" />

    <!-- SCREEN CSS -->
    <link type="text/css" rel="stylesheet" href="<?php get_static_component_domain() ?>/website/themes/default/css/style.css"/>
    <link type="text/css" rel="stylesheet" href="<?php get_static_component_domain() ?>/website/themes/lechzuers&amp;f=lightbox/css/jquery.lightbox-0.5.css,css/bootstrap.css,js/datepicker/css/custom-theme/jquery-ui-1.8.23.custom.css"/>

    <link rel="shortcut icon" href="<?php get_static_component_domain() ?>favicon.ico" type="image/x-icon"></link>

    <script src="/website/themes/default/js/libs/modernizr-2.5.3.min.js"></script>
</head>

<body>  
    <div class="wrapper error-404" id="wrap">
        <?php include dirname(__FILE__).'/includes/header.inc.php'; ?>

        <!-- Content --> 
        <div class="content detail">
            <div class="content-inner">
                <div class="content">
                    <?php echo $this->translate->_('Error 404', false); ?>
                    <p>
                        <?php echo $this->translate->_('Continue on')?> <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>"><?php echo $_SERVER['HTTP_HOST']; ?></a>.
                    </p>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
            </div>
            <div class="clear"></div>
        </div> <!-- /.content -->
        <div id="push">&nbsp;</div>
    </div> <!-- /#wrap -->

<?php include dirname(__FILE__).'/includes/footer.inc.php'; ?>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="<?php get_static_component_domain() ?>/website/themes/default/js/libs/jquery-1.7.2.min.js"><\/script>')</script>

<script type="text/javascript" src="<?php get_static_component_domain() ?>/website/themes/default/js/libs.min.js"></script>
<script type="text/javascript" src="<?php get_static_component_domain() ?>/website/themes/default/js/script.min.js"></script>

<script type="text/javascript">//<![CDATA[

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
    
    _gaq.push(['_trackEvent', 'Error', '404', 'Seite: ' + document.location.pathname + document.location.search + ' Verweis: ' + document.referrer]);

    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
    //]]>
</script>
</body>
</html>

