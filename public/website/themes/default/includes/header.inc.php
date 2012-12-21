    <!-- Header -->
    <header class="header">
        <div class="inner">
            <div class="logo">Logospace</div>
            <div class="slogan">Slogan Platzhalter</div>
            <div class="search">
                <form id="searchForm" name="searchForm">
                    <a class="button" href="#" onclick="myDefault.search(); return false;"><div class='sprite loupe'></div></a>
                    <input id="searchField" type="text" name="q" class="" onclick="this.value='';this.onclick=function(){return false;}" autocomplete="off" value="<?php echo $this->translate->_('Search', false); ?>"/>
                </form>
            </div>
            <!--  
            <div class="search">
                <input type="text" name="iptSearch" id="iptSearch" value="Suche"/>
            </div> 
            -->
        </div>
    </header> <!-- /.header -->
    <!-- Navigation -->
    <nav class="nav">
        <div class="inner">
            <ul>
                <?php get_main_navigation('li', '', 'selected', true, false); ?>
            </ul>
            <div class="clear"></div>
        </div>
    </nav> <!-- /.nav -->