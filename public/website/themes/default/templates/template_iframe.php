<?php 
  $strUrl = $_SERVER['REQUEST_URI'];
  $strQueryString = parse_url($strUrl, PHP_URL_QUERY);
?>
      
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
            <div class="clear"></div>
            
            <?php get_iframe($strQueryString); ?>

            <?php get_text_blocks('220x', true, true, '660x'); ?>
            
            <?php get_internal_links(); ?>
            
            <?php get_video('540', '304'); ?>
            
            <?php get_image_gallery(8, '140x140', true, true, '660x'); ?>
               
            <?php get_documents(); ?>
          </div>
          <div class="sidebar">
            <?php get_contact(); ?>
            <?php get_sidebar(); ?>
          </div>          
          <div class="clear"></div>
        </div>
        <div class="clear"></div>
      </div> <!-- /.content -->