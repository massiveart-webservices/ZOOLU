ALTER TABLE `page-DEFAULT_PAGE_1-1-Instances` ADD `seo_description` TEXT NOT NULL AFTER `contact` ,
ADD `seo_keywords` TEXT NOT NULL AFTER `seo_description` ,
ADD `seo_title` VARCHAR( 255 ) NOT NULL AFTER `seo_keywords` ,
ADD `seo_canonical` VARCHAR( 255 ) NOT NULL AFTER `seo_title` 

ALTER TABLE `page-DEFAULT_STARTPAGE-1-Instances` ADD `seo_description` TEXT NOT NULL AFTER `banner_description` ,
ADD `seo_keywords` TEXT NOT NULL AFTER `seo_description` ,
ADD `seo_title` VARCHAR( 255 ) NOT NULL AFTER `seo_keywords` ,
ADD `seo_canonical` VARCHAR( 255 ) NOT NULL AFTER `seo_title` 
