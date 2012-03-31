<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Search</title>
  
  <!-- SCREEN CSS -->
  <link type="text/css" rel="stylesheet" href="/min/b=website/themes/default&amp;f=css/reset.css,css/screen.css,lightbox/css/lightbox.css" />
  <link rel="shortcut icon" href="/website/themes/default/favicon.ico" type="image/x-icon"></link>
  
  <!-- js -->
  <script type="text/javascript" src="/min/b=website/themes/default&amp;f=js_incs/prototype/prototype.js,js_incs/script.aculous/builder.js,js_incs/script.aculous/effects.js,js_incs/script.aculous/controls.js,js_incs/script.aculous/fader.js,lightbox/js/lightbox.js,js_incs/default.js,flowplayer/flowplayer-3.2.2.min.js"></script>
  
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
	        <h1><?php echo $this->translate->_('Search'); ?></h1>
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
        <div class="clear"></div>
      </div> <!-- /.content -->
        
    </div> <!-- /#main -->
  </div> <!-- /#wrap --> 

  <!-- Footer -->
  <div id="footer">
    <div class="inner">
      <div class="left">&copy; <?php echo date('Y'); ?> Firmenname</div>
      <div class="right">&nbsp;</div>
      <div class="clear"></div>
    </div>      
  </div> <!-- /#footer -->
  
    
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