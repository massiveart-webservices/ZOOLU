/* New Decorator ID 12 */
INSERT INTO `decorators` (`id` ,
`title`
)
VALUES (NULL , 'Datetimes'
);

/* New Fieldtype ID 39 */
INSERT INTO `fieldTypes` (`id` ,
`idDecorator` ,
`sqlType` ,
`size` ,
`title` ,
`defaultValue` ,
`idFieldTypeGroup`
)
VALUES (NULL , '12', '', '0', 'datetimes', '', '4'
);

/* Create Save Table */
CREATE TABLE IF NOT EXISTS `pageDates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pageId` varchar(32) NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `idLanguages` int(10) unsigned NOT NULL DEFAULT '1',
  `idFields` bigint(20) DEFAULT NULL,
  `from_date` date DEFAULT NULL,
  `from_time` time DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `to_time` time DEFAULT NULL,
  `fulltime` tinyint(1) DEFAULT NULL,
  `repeat` tinyint(1) DEFAULT NULL COMMENT 'recurring datetime?',
  `repeat_frequency` varchar(32) DEFAULT NULL COMMENT 'daily, weekly, monthly, yearly',
  `repeat_interval` int(10) DEFAULT NULL COMMENT '1 = every, 2 every second ...',
  `repeat_type` int(10) DEFAULT NULL COMMENT 'weekly(1,2,4,8,16,32,64), monthly(1,2)',
  `end` tinyint(1) DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `idUsers` bigint(20) unsigned NOT NULL,
  `creator` bigint(20) unsigned NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pageId` (`pageId`),
  KEY `version` (`version`),
  KEY `pageId_2` (`pageId`,`version`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `pageDates`
  ADD CONSTRAINT `pageDates_ibfk_1` FOREIGN KEY (`pageId`) REFERENCES `pages` (`pageId`) ON DELETE CASCADE;


/* ID 275 */
INSERT INTO `fields` (`id` ,
`idFieldTypes` ,
`name` ,
`idSearchFieldTypes` ,
`idRelationPage` ,
`idCategory` ,
`sqlSelect` ,
`columns` ,
`height` ,
`isCoreField` ,
`isKeyField` ,
`isSaveField` ,
`isRegionTitle` ,
`isDependentOn` ,
`showDisplayOptions` ,
`options` ,
`copyValue` ,
`validators`
)
VALUES (NULL , '39', 'date', '6', NULL , NULL , NULL , '12', '0', '1', '0', '1', '0', NULL , '0', NULL , '0', ''
);

INSERT INTO `fieldTitles` (`id` ,
`idFields` ,
`idLanguages` ,
`title` ,
`description`
)
VALUES (NULL , '275', '1', 'Termine', NULL
), (NULL , '275', '2', 'Datetimes', NULL
);



/* Region ID 107 */
INSERT INTO `regions` (`id` ,
`idRegionTypes` ,
`columns` ,
`isTemplate` ,
`collapsable` ,
`isCollapsed` ,
`position` ,
`isMultiply` ,
`multiplyRegion`
)
VALUES (NULL , '1', '9', '0', '0', '1', NULL , '0', '0'
);

INSERT INTO `regionTitles` (`id` ,
`idRegions` ,
`idLanguages` ,
`title`
)
VALUES (NULL , '107', '1', 'Termine'
), (NULL , '107', '2', 'Datetimes'
);


/* Add Region to Tab */
INSERT INTO `tabRegions` (`id` ,
`idTabs` ,
`idRegions` ,
`order`
)
VALUES (NULL , '11', '107', '10'
);


INSERT INTO `regionFields` (`id` ,
`idRegions` ,
`idFields` ,
`order`
)
VALUES (NULL , '107', '275', '10'
);




/* Update Default Event from global to page */
UPDATE `zo-zoolu`.`genericForms` SET `idGenericFormTypes` = '1' WHERE `genericForms`.`id` =11;
UPDATE `zo-zoolu`.`genericForms` SET `idGenericFormTypes` = '1' WHERE `genericForms`.`id` =12;

/* Activate Event Template */
UPDATE `zo-zoolu`.`templates` SET `active` = '1' WHERE `templates`.`id` =7;
UPDATE `zo-zoolu`.`templates` SET `active` = '1' WHERE `templates`.`id` =8;




/* Hide Template 7 Date Fields */
INSERT INTO `zo-zoolu`.`templateExcludedFields` (`id` ,
`idTemplates` ,
`idFields`
)
VALUES (NULL , '7', '139'
), (NULL , '7', '140'
), (NULL , '7', '67'
), (NULL , '7', '134'
);


/* FIX SEO TAB */
ALTER TABLE `page-DEFAULT_EVENT-1-Instances` ADD `seo_description` TEXT NULL AFTER `event_status` ,
ADD `seo_keywords` TEXT NULL AFTER `seo_description` ,
ADD `seo_title` VARCHAR( 255 ) NULL AFTER `seo_keywords` ,
ADD `seo_canonical` VARCHAR( 255 ) NOT NULL AFTER `seo_title`;

