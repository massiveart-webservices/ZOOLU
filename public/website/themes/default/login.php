<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if IE 9]>    <html class="no-js lt-ie10" lang="en"> <![endif]-->
<!-- Consider adding a manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Login - Lech Zürs Tourismus am Arlberg</title>

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
    <link type="text/css" rel="stylesheet" href="/website/themes/lechzuers/css/style.css"/>
    <link type="text/css" rel="stylesheet" href="/min/b=website/themes/lechzuers&amp;f=lightbox/css/jquery.lightbox-0.5.css,css/bootstrap.css,js/datepicker/css/custom-theme/jquery-ui-1.8.23.custom.css"/>

    <?php if(Zend_Auth::getInstance()->hasIdentity()) : ?>
    <link rel="stylesheet" type="text/css" media="screen" href="/website/themes/lechzuers/css/modus.css"></link>
    <script type="text/javascript" src="/website/themes/lechzuers/js/modus.js"></script>
    <?php endif; ?>

    <link rel="shortcut icon" href="/website/themes/lechzuers/favicon.ico" type="image/x-icon"/>

    <script src="/website/themes/lechzuers/js/libs/modernizr-2.5.3.min.js"></script>
</head>

<body>
    <div class="wrapper" id="wrap">
    	<header id="header">
	        <div id="fader">
	        	<div class="header-logo">
                    <a href="/"><img alt="Logo Lech Zürs" src="/website/themes/lechzuers/img/logo_lech_zuers.png"></a>
                </div>
	            <div id="fader-inner" class="fader-inner innerfade" style="position: relative; height: 100%;">
                	<img title="" alt="" src="/website/themes/lechzuers/img/tmp/header_balmalp.jpg">
                </div>
	        </div>
	        <div class="clear"></div>
	    </header>
        <!-- Content -->
        <div class="content detail">
            <div class="content-inner">
                <div class="login">
                    <h1>Login</h1>
                    <form accept-charset="utf-8" method="post">
                        <div class="txt-input">
                            <label for="username"><?php echo $this->translate->_('Username'); ?></label><br/>
                            <input type="text" value="" name="username" id="username" />
                        </div>
                        <?php if(!empty($this->strErrUsername)) :?>
                            <div class="errormsg">
                                <span class="missing"><?php echo $this->escape($this->strErrUsername); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="txt-input">
                            <label for="password"><?php echo $this->translate->_('Password'); ?></label><br/>
                            <input type="password" value="" name="password" id="password" />
                        </div>
                        <?php if(!empty($this->strErrPassword)) :?>
                            <div class="errormsg">
                                <span class="missing"><?php echo $this->escape($this->strErrPassword); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="submit">
                            <div class="button"><input class="submitbuttongraysmall" type="submit" value="<?php echo $this->translate->_('Login'); ?>"/></div>
                        </div>
                        <div class="clear"></div>
                    </form>
                </div>
            </div>
            <div class="clear"></div>
        </div> <!-- /.content -->
        <div id="push">&nbsp;</div>
    </div> <!-- /#wrap -->
	<footer class="footer">
		<div class="footer-inner">
		    <div class="footer-stay">
		        <div class="footer-cont">
		            <div class="footer-title"><?php echo $this->translate->_('Footer_Left_Headline'); ?></div>
		            <ul>
		                <li><a href="<?php echo $this->translate->_('Hotels and accomondation Lech URL');?>"><?php echo $this->translate->_('Hotels and accomondation Lech');?></a></li>
		                <li><a href="<?php echo $this->translate->_('Hotels and accomondation Zuers URL');?>"><?php echo $this->translate->_('Hotels and accomondation Zuers');?></a></li>
		            </ul>
		        </div>
		    </div>
		    <div class="footer-middle">
		        <div class="footer-services">
		            <div class="footer-cont">
		                <div class="footer-title"><?php echo $this->translate->_('Footer_Service_Headline'); ?></div>
		            </div>
		        </div>
		        <div class="footer-social-media">
		            <div class="footer-cont">
		                <div class="footer-title"><?php echo $this->translate->_('Footer_Social_Media_Headline'); ?></div>
		                <div><?php echo $this->translate->_('Footer_Social_Media_Text'); ?></div>
		                <ul class="social-conts">
		                    <li><a target="_blank" href="http://www.facebook.com/lechzuers"><img src="/website/themes/lechzuers/img/facebook.gif" alt=""/></a></li>
		                    <li><a target="_blank" href="https://twitter.com/Lech_Zuers"><img src="/website/themes/lechzuers/img/twitter.gif" alt=""/></a></li>
		                    <li><a target="_blank" href="http://www.youtube.com/user/lztg"><img src="/website/themes/lechzuers/img/youtube.gif" alt=""/></a></li>
		                    <li><a target="_blank" href="http://www.flickr.com/photos/lechzuers/"><img src="/website/themes/lechzuers/img/flickr.gif" alt=""/></a></li>
		                    <!-- <li><a target="_blank" href="http://pinterest.com/lechzuers/"><img src="/website/themes/lechzuers/img/pinterest.gif" alt=""/></a></li> -->
		                </ul>
		            </div>
		        </div>
		        <div class="clear"></div>
		        <div class="footer-logo">
		            <a href="/"><img src="/website/themes/lechzuers/img/logo_lech_zuers_footer.gif" alt="Logo Lech Zürs" /></a>
		            <div class="clear"></div>
		        </div>
		    </div>
		    <div class="footer-contact">
		        <div class="footer-cont">
		            <div class="footer-title"><?php echo $this->translate->_('Contact'); ?></div>
		            <div class="footer-contacts">
		                <div class="buero">
		                    <ul>
		                        <li><strong><?php echo $this->translate->_('Office')?> Lech</strong>, 6764 Lech</li>
		                        <li><img class="icon" src="/website/themes/lechzuers/img/icon_telefon.gif" alt="telephone icon"/>+43 (0) 5583 2161-0</li>
		                        <li><img class="icon" src="/website/themes/lechzuers/img/icon_fax.gif" alt="telephone icon"/>+43 (0) 5583 3155</li>
		                    </ul>
		                </div>
		                <div class="buero">
		                    <ul>
		                        <li><strong><?php echo $this->translate->_('Office')?> Zürs</strong>, 6763 Zürs</li>
		                        <li><img class="icon" src="/website/themes/lechzuers/img/icon_telefon.gif" alt="telephone icon"/>+43 (0) 5583 2245</li>
		                        <li><img class="icon" src="/website/themes/lechzuers/img/icon_fax.gif" alt="telephone icon"/>+43 (0) 5583 2982</li>
		                    </ul>
		                </div>
		                <div class="mail">
		                    <a href="mailto:info@lech-zuers.at">info@lech-zuers.at</a>
		                </div>
						<span class="copyright">&copy; <?php echo date('Y'); ?></span>
		            </div>
		        </div>
		    </div>
		    <div class="clear"></div>	    
		    <div class="partner-cont">
		    	<span class="item">
		    		<a target="_blank" href="http://www.mercedes-benz.de"><img src="/website/themes/lechzuers/img/partner/mercedes_benz_white.gif" alt="mercedes benz"/></a>
		    	</span>
		    	<span class="item">
		    		<a target="_blank" href="http://www.laureus.com"><img src="/website/themes/lechzuers/img/partner/laureus_white.gif" alt="laureus"/></a>
		    	</span>
		    	<span class="item">
		    		<a target="_blank" href="http://www.bestofthealps.com"><img src="/website/themes/lechzuers/img/partner/bestofthealps_white.gif" alt="bestofthealps"/></a>
		    	</span>
		    	<span class="item">
		    		<a target="_blank" href="http://www.vorarlberg.travel/"><img src="/website/themes/lechzuers/img/partner/vorarlberg_white.gif" alt="Vorarlberg"/></a>
		    	</span>
		    	<span class="item">
		    		<a target="_blank" href="http://www.skiarlberg.at"><img src="/website/themes/lechzuers/img/partner/skiarlberg_white.gif" alt="Ski Arlberg"/></a>
		    	</span>
		    	<div class="clear"></div>
		    </div>
		    <div class="clear"></div>
		</div>
		<div class="clear"></div>
	</footer>


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