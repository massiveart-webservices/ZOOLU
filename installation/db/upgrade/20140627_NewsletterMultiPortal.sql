-- email is now not an unique key --
`ALTER TABLE subscribers DROP INDEX email`;

-- idRootLevels exists so we create idPortals --
ALTER TABLE  `subscribers` ADD  `portal` BIGINT( 20 ) UNSIGNED NULL AFTER  `idUsers`;


UPDATE  `fields` SET  `isCoreField` =  '1' WHERE  `fields`.`id` =229; -- portal field --
UPDATE  `fields` SET  `idFieldTypes` =  '20' WHERE  `fields`.`id` =229; -- portal field --


