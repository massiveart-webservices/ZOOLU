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
          <div class="subnav">
            <!-- Sub Navigation -->
            <?php include dirname(__FILE__).'/../includes/subnavigation.inc.php'; ?>
            &nbsp;
          </div>
          <div class="content">
            <h1><?php get_title(); ?></h1>
            <?php get_image_main('220x', true, true, '660x', 'imgLeft'); ?>
            <?php get_description(); ?>
            <?php get_sitemap(); ?>
            <div class="clear"></div>
          </div>
          <div class="sidebar">
            <?php get_contact(); ?>
          </div>          
          <div class="clear"></div>
        </div>
        <div class="clear"></div>
      </div> <!-- /.content -->