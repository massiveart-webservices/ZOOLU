<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if IE 9]>    <html class="no-js lt-ie10" lang="en"> <![endif]-->
<!-- Consider adding a manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Login - ZOOLU Default Theme</title>

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
    <meta name="robots" content="<?php echo ((APPLICATION_ENV == 'production') ? 'noindex, follow' : 'noindex, nofollow'); ?>" />

    <!-- SCREEN CSS -->
    <link type="text/css" rel="stylesheet" href="/website/themes/default/css/style.css"/>

    <?php
    $objAuth = Zend_Auth::getInstance();
    $objAuth->setStorage(new Zend_Auth_Storage_Session('zoolu'));
    if($objAuth->hasIdentity()) : ?>
    <link rel="stylesheet" type="text/css" media="screen" href="/website/themes/default/css/modus.css"></link>
    <script type="text/javascript" src="/website/themes/default/js/modus.js"></script>
    <?php endif; ?>

    <link rel="shortcut icon" href="/website/themes/default/favicon.ico" type="image/x-icon"/>

    <script src="/website/themes/default/js/libs/modernizr-2.5.3.min.js"></script>
</head>

<body>
    <div id="wrap">
        <div id="main" class="clearfix">     
        <!-- Header -->
            <div class="header">
                <div class="inner">
                    <div class="logo">Logospace</div>
                    <div class="slogan">Slogan Platzhalter</div>          
                </div>
            </div> <!-- /.header -->
      
            <!-- Navigation -->
            <div class="nav">&nbsp;</div> <!-- /.nav -->
            <!-- Login Content -->
            <!-- Top Content -->
            <div class="top">
                <div class="inner">
                    <!-- TODO : header image or flash -->
                    <div class="clear"></div>
                </div>
            </div> <!-- /.top -->
            <!-- Content --> 
            <div class="contentContainer">
                <div class="inner"> 
                    <div class="login">
                        <form accept-charset="utf-8" method="post">
                            <div class="element">
                                <label for="username"><?php echo $this->translate->_('Username'); ?></label><br/>
                                <input type="text" value="" name="username" id="username" />
                                <?php if(!empty($this->strErrUsername)) :?>
                                    <div class="errormsg">
                                        <span class="missing"><?php echo $this->escape($this->strErrUsername); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="element">
                                <label for="password"><?php echo $this->translate->_('Password'); ?></label><br/>
                                <input type="password" value="" name="password" id="password" />
                                <?php if(!empty($this->strErrPassword)) :?>
                                    <div class="errormsg">
                                        <span class="missing"><?php echo $this->escape($this->strErrPassword); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="submit">
                                <div class="button"><input class="submitbuttongraysmall" type="submit" value="<?php echo $this->translate->_('Login'); ?>"/></div>
                                </div>
                            <div class="clear"></div>
                        </form>
                    </div>
                </div>
            <div class="clear"></div>
            </div> <!-- /.content -->
        
        </div> <!-- /#main -->
    </div> <!-- /#wrap --> 
    <!-- Footer -->
    <footer id="footer">
        <div class="inner">
            <div class="left">&copy; <?php echo date('Y'); ?> Firmenname</div>
            <div class="right">&nbsp;</div>
            <div class="clear"></div>
        </div>      
    </footer> <!-- /#footer -->


    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="/website/themes/lechzuers/js/libs/jquery-1.7.2.min.js"><\/script>')</script>

    <script type="text/javascript" src="/min/b=website/themes/lechzuers&amp;f=js/libs/jquery-91datepicker.js,js/libs/globalize/globalize.js,js/libs/globalize/cultures/globalize.culture.de.js,js/libs/lazyload.js,js/libs/jquery.liveSearch.js,lightbox/js/jquery.lightbox-0.5.js,js/plugins.js,js/datepicker/js/jquery-ui-1.8.23.custom.min.js,js/bootstrap.min.js,js/libs/infinite-ajax-scroll/jquery.ias.min.js?v=1.0"></script>
    <script type="text/javascript" src="/website/themes/lechzuers/js/script.min.js?v=1.0"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            Globalize.culture('de');

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
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
        })();
    </script>
</body>
</html>