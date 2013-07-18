INSERT INTO `zo-zoolu`.`rootLevelTypes` (
`id` ,
`title`
)
VALUES (
NULL , 'tags'
);


INSERT INTO `zo-zoolu`.`rootLevels` (
`id` ,
`idRootLevelTypes` ,
`idRootLevelGroups` ,
`idModules` ,
`isSecure` ,
`hasSegments` ,
`hasPortalGate` ,
`landingPages` ,
`href` ,
`idThemes` ,
`languageDefinitionType` ,
`idCustomerRegistrationStatus` ,
`registrationStrategy` ,
`order` ,
`active`
)
VALUES (
NULL , '21', '3', '3', '0', '0', '0', '0', '/zoolu/cms/', NULL , NULL , '1', NULL , '6', '1'
);


INSERT INTO `zo-zoolu`.`rootLevelTitles` (
`id` ,
`idRootLevels` ,
`idLanguages` ,
`title`
)
VALUES (
NULL , '57', '1', 'Tags'
), (
NULL , '57', '2', 'Tags'
);


UPDATE `zo-zoolu`.`rootLevels` SET `href` = '/zoolu/properties/index/list' WHERE `rootLevels`.`id` =57;


INSERT INTO `zo-zoolu`.`rootLevelPermissions` (
`idRootLevels` ,
`zone` ,
`idGroups`
)
VALUES (
'57', '1', '1'
);

UPDATE `zo-zoolu`.`rootLevels` SET `href` = '/zoolu/properties/' WHERE `rootLevels`.`id` =4;

UPDATE `zo-zoolu`.`rootLevels` SET `href` = '/zoolu/properties/' WHERE `rootLevels`.`id` =6;

UPDATE `zo-zoolu`.`rootLevels` SET `href` = '/zoolu/properties/' WHERE `rootLevels`.`id` =7;
