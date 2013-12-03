UPDATE `zo-zoolu`.`rootLevelTitles` SET `title` = 'Zoolu Newsletter' WHERE `rootLevelTitles`.`id` =93;

UPDATE `zo-zoolu`.`rootLevelTitles` SET `title` = 'Zoolu Newsletter' WHERE `rootLevelTitles`.`id` =94;


DELETE FROM `zo-zoolu`.`rootLevelTypeFilterTypes` WHERE `rootLevelTypeFilterTypes`.`id` = 4;

UPDATE `zo-zoolu`.`rootLevelTypeFilterTypes` SET `name` = 'portal' WHERE `rootLevelTypeFilterTypes`.`id` =1;

UPDATE `zo-zoolu`.`rootLevelTypeFilterTypes` SET `name` = 'interestgroup' WHERE `rootLevelTypeFilterTypes`.`id` =2;

UPDATE `zo-zoolu`.`rootLevelTypeFilterTypes` SET `name` = 'language',
`sqlSelect` = 'SELECT tbl.id AS id, languages.title AS title, languages.title AS altTitle FROM languages AS tbl ORDER BY tbl.title' WHERE `rootLevelTypeFilterTypes`.`id` =3;

ALTER TABLE `subscribers` ADD `hardbounce` TINYINT UNSIGNED NULL AFTER `dirty`;

ALTER TABLE `subscribers` CHANGE `salutation` `salutation` BIGINT( 20 ) UNSIGNED NULL DEFAULT NULL; 

UPDATE `zo-zoolu`.`fields` SET `idFieldTypes` = '9',
`sqlSelect` = 'SELECT tbl.id AS id, categoryTitles.title AS title FROM categories AS tbl INNER JOIN categoryTitles ON categoryTitles.idCategories = tbl.id AND categoryTitles.idLanguages = %LANGUAGE_ID%, categories AS rootCat WHERE rootCat.id = 640 AND tbl.idRootCategory = rootCat.idRootCategory AND tbl.lft BETWEEN ( rootCat.lft +1 ) AND rootCat.rgt %WHERE_ADDON% ORDER BY tbl.lft, categoryTitles.title' WHERE `fields`.`id` =226;

DELETE FROM `zo-zoolu`.`regionFields` WHERE `regionFields`.`id` = 321;

DELETE FROM `zo-zoolu`.`fields` WHERE `fields`.`id` = 246;

ALTER TABLE `subscribers` CHANGE `street` `street` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;