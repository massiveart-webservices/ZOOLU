<?php   
 /* CAT:Misc */

 /* pChart library inclusions */
 include("../class/pData.class.php");
 include("../class/pDraw.class.php");
 include("../class/pImage.class.php");

 /* Create and populate the pData object */
 $MyData = new pData();
 for($i=0; $i<=10;$i=$i+.2)
  {
   $MyData->addPoints(log($i+1)*10,"Bounds 1");
   $MyData->addPoints(log($i+3)*10+rand(0,2)-1,"Probe");
   $MyData->addPoints(log($i+6)*10,"Bounds 2");
   $MyData->addPoints($i*10,"Labels");
  }
 $MyData->setAxisName(0,"Size (cm)");
 $MyData->setSerieDescription("Labels","Months");
 $MyData->setAbscissa("Labels");
 $MyData->setAbscissaName("Time (years)");

 /* Create the pChart object */
 $myPicture = new pImage(700,230,$MyData);

 /* Turn of Antialiasing */
 $myPicture->Antialias = FALSE;

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));
 
 /* Write the chart title */ 
 $myPicture->setFontProperties(array("FontName"=>"../fonts/Forgotte.ttf","FontSize"=>11));
 $myPicture->drawText(150,35,"Size by time generations",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

 /* Set the default font */
 $myPicture->setFontProperties(array("FontName"=>"../fonts/pf_arma_five.ttf","FontSize"=>6));

 /* Define the chart area */
 $myPicture->setGraphArea(40,40,680,200);

 /* Draw the scale */
 $scaleSettings = array("LabelSkip"=>4,"XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
 $myPicture->drawScale($scaleSettings);

 /* Turn on Antialiasing */
 $myPicture->Antialias = TRUE;

 /* Draw the line chart */
 $myPicture->drawZoneChart("Bounds 1","Bounds 2");
 $MyData->setSerieDrawable(array("Bounds 1","Bounds 2"),FALSE);

 /* Draw the line chart */
 $myPicture->drawStepChart();

 /* Write the chart legend */
 $myPicture->drawLegend(640,20,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput("pictures/example.drawZoneChart.png");
?>