/* Fname Sname no keyfields */
UPDATE  `fields` SET  `isKeyField` =  '0' WHERE  `fields`.`id` =224;
UPDATE  `fields` SET  `isKeyField` =  '0' WHERE  `fields`.`id` =225;

/* Add 2 groups admin and manager for subscriber */

-- Change RootlevelPermission from Newsletter Manager to Subscriber Admin
UPDATE  `rootLevelPermissions` SET  `idGroups` =  '168' WHERE  `rootLevelPermissions`.`idRootLevels` =48 AND `rootLevelPermissions`.`idGroups` =160;
-- Add Newsletter Manager to Rootlevel subscriber --
INSERT INTO  `rootLevelPermissions` (
`idRootLevels` ,
`zone` ,
`idGroups`
)
VALUES (
'48',  '1',  '169'
);

