<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title><?php get_meta_title(); ?></title>
  <?php get_meta_description(); ?>
  <?php get_meta_keywords(); ?>
  <?php get_canonical_tag(); ?>
  <?php // get_canonical_tag_for_segmentation(); - if segmentation is used, enable canonical tag ?>

  <!-- SCREEN CSS -->
  <link type="text/css" rel="stylesheet" href="<?php get_static_component_domain() ?>/min/b=website/themes/default&amp;f=css/reset.css,css/screen.css,lightbox/css/lightbox.css" />
  
  <%template_css%>
  <%plugin_css%>

  <?php if(Zend_Auth::getInstance()->hasIdentity()) : ?>
  <link rel="stylesheet" type="text/css" media="screen" href="<?php get_static_component_domain() ?>/website/themes/default/css/modus.css"></link>
  <script type="text/javascript" src="<?php get_static_component_domain() ?>/website/themes/default/js_incs/modus.js"></script>
  <?php endif; ?>

  <link rel="shortcut icon" href="<?php get_static_component_domain() ?>/website/themes/default/favicon.ico" type="image/x-icon"></link>
  
  <script type="text/javascript" src="<?php get_static_component_domain() ?>/min/b=website/themes/default&amp;f=js_incs/prototype/prototype.js,js_incs/script.aculous/builder.js,js_incs/script.aculous/effects.js,js_incs/script.aculous/controls.js,js_incs/script.aculous/fader.js,lightbox/js/lightbox.js,js_incs/default.js,flowplayer/flowplayer-3.2.2.min.js"></script>
  <%plugin_js%>
  <%template_js%>
</head>

<body>
  <?php get_zoolu_header(); ?>
  <div id="wrap">
    <div id="main" class="clearfix">      
      <!-- header and main navigation -->
      <?php include dirname(__FILE__).'/includes/header.inc.php'; ?>
      
      <!-- Template Content -->
      <?php include dirname(__FILE__).'/templates/'.get_template_file(); ?>
    </div> <!-- /#main -->
  </div> <!-- /#wrap --> 

  <!-- Footer -->
  <?php include dirname(__FILE__).'/includes/footer.inc.php'; ?>
  
  <?php get_bottom_content(); ?>
  
  <script type="text/javascript">//<![CDATA[
    var myDefault;
    document.observe('dom:loaded', function() {
      myDefault = new Default();
      
      <?php get_dom_loaded_js(); ?>
    });

    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '<?php echo $this->analyticsKey; ?>']);
    _gaq.push(['_trackPageview']);

    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
    //]]>
  </script>
</body>
</html>
