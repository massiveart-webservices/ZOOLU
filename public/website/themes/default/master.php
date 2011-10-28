<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title><?php get_title(); ?></title>
  <?php get_meta_description(); ?>
  <?php get_meta_keywords(); ?>

  <!-- SCREEN CSS -->
  <link type="text/css" rel="stylesheet" href="<? get_static_component_domain() ?>/min/b=website/themes/default&amp;f=css/reset.css,css/screen.css,lightbox/css/lightbox.css" />
  
  <%template_css%>
  <%plugin_css%>

  <?php if(Zend_Auth::getInstance()->hasIdentity()) : ?>
  <link rel="stylesheet" type="text/css" media="screen" href="<? get_static_component_domain() ?>/website/themes/default/css/modus.css"></link>
  <?php endif; ?>

  <link rel="shortcut icon" href="<? get_static_component_domain() ?>/website/themes/default/favicon.ico" type="image/x-icon"></link>
  
  <script type="text/javascript" src="<? get_static_component_domain() ?>/min/b=website/themes/default&amp;f=js_incs/prototype/prototype.js,js_incs/script.aculous/builder.js,js_incs/script.aculous/effects.js,js_incs/script.aculous/controls.js,js_incs/script.aculous/fader.js,lightbox/js/lightbox.js,js_incs/default.js,flowplayer/flowplayer-3.2.2.min.js"></script>
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
    //]]>
  </script>
  
  <!-- @start, Google Analytics -->
  <script type="text/javascript">
    var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
    document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
  </script>
  <script type="text/javascript">
    try {
      var pageTracker = _gat._getTracker("<?php echo $this->analyticsKey; ?>");
      pageTracker._trackPageview();
    } catch(err) {}
  </script>
  <!-- @end, Google Analytics -->
</body>
</html>
