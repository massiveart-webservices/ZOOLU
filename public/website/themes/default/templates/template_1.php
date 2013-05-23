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
            <article class="content">
                <?php get_title('h1 class="title"'); ?>
                <?php get_image_main('220x', true, true, '660x', 'imgLeft'); ?>
                <?php get_description(); ?>
                <div class="clear"></div>
                
                <?php get_text_blocks('220x', true, true, '660x'); ?>
                <?php get_video('540', '304'); ?>
                <?php get_documents(); ?>
                <?php get_internal_links(); ?>
                <?php get_image_gallery(8, '140x140', true, true, '660x'); ?>
                
            </article>
            <aside class="sidebar">
                <?php get_contact(); ?>
                <?php get_sidebar(); ?>
            </aside>          
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div> <!-- /.content -->