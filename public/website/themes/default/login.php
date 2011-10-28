<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Login</title>
  
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
  //]]>
  </script>
</body>
</html>