<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Error 404</title>
  
  <link type="text/css" rel="stylesheet" href="/min/b=website/themes/default&amp;f=css/reset.css,css/screen.css" />
  <link rel="shortcut icon" href="/website/themes/default/favicon.ico" type="image/x-icon"></link>
</head>

<body>  
  <div id="wrap">
    <div id="main" class="clearfix">      
      <!-- header and main navigation -->
      <?php include dirname(__FILE__).'/includes/header.inc.php'; ?>
      
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
          <div class="subnav">&nbsp;</div>
          <div class="content">
            <h1>404 Error Message</h1>
            <p>
              <strong>Page not Found!</strong><br/>
              We are sorry, the page you requested was not found at our website.<br/><br/>
              Continue on <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>"><?php echo $_SERVER['HTTP_HOST']; ?></a>.           
            </p>
            <div class="clear"></div>
          </div>
          <div class="sidebar">&nbsp;</div>          
          <div class="clear"></div>
        </div>
        <div class="clear"></div>
      </div> <!-- /.content -->
    </div> <!-- /#main -->
  </div> <!-- /#wrap -->
   
  <!-- Footer Section -->
  <?php include dirname(__FILE__).'/includes/footer.inc.php'; ?>
  
  <script type="text/javascript">//<![CDATA[
    var myDefault;
    document.observe('dom:loaded', function() {
      myDefault = new Default();
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
