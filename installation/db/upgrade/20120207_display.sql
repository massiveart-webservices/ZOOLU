ALTER TABLE `pageProperties` ADD `showInWebsite` BOOLEAN NOT NULL DEFAULT '1' AFTER `hideInSitemap` ,
ADD `showInTablet` BOOLEAN NOT NULL DEFAULT '1' AFTER `showInWebsite` ,
ADD `showInMobile` BOOLEAN NOT NULL DEFAULT '1' AFTER `showInTablet` 


ALTER TABLE `folderProperties` ADD `showInWebsite` BOOLEAN NOT NULL DEFAULT '1' AFTER `hideInSitemap` ,
ADD `showInTablet` BOOLEAN NOT NULL DEFAULT '1' AFTER `showInWebsite` ,
ADD `showInMobile` BOOLEAN NOT NULL DEFAULT '1' AFTER `showInTablet` 
