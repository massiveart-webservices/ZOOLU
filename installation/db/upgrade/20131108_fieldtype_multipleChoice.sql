-- New Decorator ID 13
INSERT INTO `decorators` ( `id` , `title` )
VALUES (
NULL , 'MultipleChoice'
)

-- New Fieldtype ID 40
INSERT INTO `fieldTypes` (`id` ,
`idDecorator` ,
`sqlType` ,
`size` ,
`title` ,
`defaultValue` ,
`idFieldTypeGroup`
)
VALUES (NULL , '13', '', '0', 'multipleChoice', '', '4'
);




INSERT INTO `zo-zoolu`.`fields` (
`id` ,
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
VALUES (
NULL , '40', 'multipleChoice', '5', NULL , NULL , NULL , '12', '0', '0', '0', '1', '0', NULL , '0', NULL , '0', ''
);



INSERT INTO `zo-zoolu`.`fieldTitles` (
`id` ,
`idFields` ,
`idLanguages` ,
`title` ,
`description`
)
VALUES (
NULL , '276', '1', 'Antworten', NULL
), (
NULL , '276', '2', 'Answers', NULL
);


INSERT INTO `zo-zoolu`.`regions` (
`id` ,
`idRegionTypes` ,
`columns` ,
`isTemplate` ,
`collapsable` ,
`isCollapsed` ,
`position` ,
`isMultiply` ,
`multiplyRegion`
)
VALUES (
NULL , '1', '9', '0', '1', '1', NULL , '1', '1'
);

INSERT INTO `zo-zoolu`.`regionTitles` (
`id` ,
`idRegions` ,
`idLanguages` ,
`title`
)
VALUES (
NULL , '109', '1', 'MultipleChoice'
), (
NULL , '109', '2', 'MultipleChoice'
);



INSERT INTO `zo-zoolu`.`tabRegions` (
`id` ,
`idTabs` ,
`idRegions` ,
`order`
)
VALUES (
NULL , '20', '109', '180'
);




INSERT INTO `zo-zoolu`.`regionFields` (
`id` ,
`idRegions` ,
`idFields` ,
`order`
)
VALUES (
NULL , '109', '276', '50'
);


-- ############IMPORTANT:################
-- get InstancesMultipleChoice table (look it up in zoolu-db on bazinga)
