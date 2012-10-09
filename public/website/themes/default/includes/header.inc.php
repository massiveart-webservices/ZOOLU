      <!-- Header -->
      <div class="header">
        <div class="inner">
          <div class="logo">Logospace</div>
          <div class="slogan">Slogan Platzhalter</div>
          <div class="languages">
            <?php get_language_chooser(); ?>
          </div>
          <div class="search">
            <input type="text" name="iptSearch" id="iptSearch" value="Suche"/>
          </div>
        </div>
      </div> <!-- /.header -->
      <!-- Navigation -->
      <div class="nav">
        <div class="inner">
          <ul>
            <?php get_main_navigation('li', '', 'selected', true, false); ?>
          </ul>
          <div class="clear"></div>
        </div>
      </div> <!-- /.nav -->