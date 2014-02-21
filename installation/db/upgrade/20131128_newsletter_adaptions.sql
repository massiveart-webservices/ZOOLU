UPDATE `rootLevelTitles` SET `title` = 'Zoolu Newsletter' WHERE `rootLevelTitles`.`id` =93;

UPDATE `rootLevelTitles` SET `title` = 'Zoolu Newsletter' WHERE `rootLevelTitles`.`id` =94;


DELETE FROM `rootLevelTypeFilterTypes` WHERE `rootLevelTypeFilterTypes`.`id` = 4;

UPDATE `rootLevelTypeFilterTypes` SET `name` = 'portal' WHERE `rootLevelTypeFilterTypes`.`id` =1;

UPDATE `rootLevelTypeFilterTypes` SET `name` = 'interestgroup' WHERE `rootLevelTypeFilterTypes`.`id` =2;

UPDATE `rootLevelTypeFilterTypes` SET `name` = 'language',
`sqlSelect` = 'SELECT tbl.id AS id, languages.title AS title, languages.title AS altTitle FROM languages AS tbl ORDER BY tbl.title' WHERE `rootLevelTypeFilterTypes`.`id` =3;

ALTER TABLE `subscribers` ADD `hardbounce` TINYINT UNSIGNED NULL AFTER `dirty`;

ALTER TABLE `subscribers` CHANGE `salutation` `salutation` BIGINT( 20 ) UNSIGNED NULL DEFAULT NULL; 

UPDATE `fields` SET `idFieldTypes` = '9',
`sqlSelect` = 'SELECT tbl.id AS id, categoryTitles.title AS title FROM categories AS tbl INNER JOIN categoryTitles ON categoryTitles.idCategories = tbl.id AND categoryTitles.idLanguages = %LANGUAGE_ID%, categories AS rootCat WHERE rootCat.id = 640 AND tbl.idRootCategory = rootCat.idRootCategory AND tbl.lft BETWEEN ( rootCat.lft +1 ) AND rootCat.rgt %WHERE_ADDON% ORDER BY tbl.lft, categoryTitles.title' WHERE `fields`.`id` =226;

DELETE FROM `regionFields` WHERE `regionFields`.`id` = 321;

DELETE FROM `fields` WHERE `fields`.`id` = 246;

ALTER TABLE `subscribers` CHANGE `street` `street` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;


SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Datenbank: `zo-zoolu`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rootLevelTypeFilterTypes`
--

DROP TABLE IF EXISTS `rootLevelTypeFilterTypes`;
CREATE TABLE IF NOT EXISTS `rootLevelTypeFilterTypes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `idRootLevelTypes` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `operators` varchar(2000) NOT NULL,
  `sqlSelect` varchar(2000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idRootLevelTypes` (`idRootLevelTypes`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Daten für Tabelle `rootLevelTypeFilterTypes`
--

INSERT INTO `rootLevelTypeFilterTypes` (`id`, `idRootLevelTypes`, `name`, `operators`, `sqlSelect`) VALUES
(1, 18, 'portal', '["one", "none", "all"]', 'SELECT rootLevels.id AS id, tbl.title AS title, alternateRootLevelTitles.title AS altTitle FROM rootLevelTitles AS tbl INNER JOIN rootLevels ON rootLevels.id = tbl.idRootLevels LEFT JOIN rootLevelTitles AS alternateRootLevelTitles ON alternateRootLevelTitles.idRootLevels = rootLevels.id AND alternateRootLevelTitles.idLanguages = 2 WHERE rootLevels.idRootLevelTypes = 1 AND rootLevels.active = 1 AND tbl.idLanguages = %LANGUAGE_ID% ORDER BY tbl.title'),
(2, 18, 'interestgroup', '["one", "none", "all"]', 'SELECT tbl.id AS id, categoryTitles.title AS title, alternateCategoryTitles.title AS altTitle FROM categories AS tbl INNER JOIN categoryTitles ON categoryTitles.idCategories = tbl.id AND categoryTitles.idLanguages = %LANGUAGE_ID% LEFT JOIN categoryTitles AS alternateCategoryTitles ON alternateCategoryTitles.idCategories = tbl.id AND alternateCategoryTitles.idLanguages = 2, categories AS rootCat WHERE rootCat.id = 615 AND tbl.idRootCategory = rootCat.idRootCategory AND tbl.lft BETWEEN ( rootCat.lft +1 ) AND rootCat.rgt ORDER BY tbl.lft, categoryTitles.title'),
(3, 18, 'language', '["one", "none", "all"]', 'SELECT tbl.id AS id, categoryTitles.title AS title FROM categories AS tbl INNER JOIN categoryTitles ON categoryTitles.idCategories = tbl.id AND categoryTitles.idLanguages = %LANGUAGE_ID%, categories AS rootCat WHERE rootCat.id = 634 AND tbl.idRootCategory = rootCat.idRootCategory AND tbl.lft BETWEEN ( rootCat.lft +1 ) AND rootCat.rgt ORDER BY tbl.lft, categoryTitles.title');
SET FOREIGN_KEY_CHECKS=1;

INSERT INTO `rootLevelGroupTitles` (`id`, `idRootLevelGroups`, `idLanguages`, `title`) VALUES (NULL, '15', '1', 'Newsletter'), (NULL, '15', '2', 'Newsletters');

UPDATE `templates` SET `active` = '1' WHERE `templates`.`id` =40;

DELETE FROM `regionFields` WHERE `regionFields`.`id` = 307;

CREATE TABLE IF NOT EXISTS `newsletterUnsubscribeHashes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idSubscriber` bigint(20) unsigned NOT NULL,
  `hash` varchar(128) NOT NULL DEFAULT '',
  `used` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idSubscriber` (`idSubscriber`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=35 ;

ALTER TABLE `newsletterUnsubscribeHashes` ENGINE = InnoDB;
ALTER TABLE `newsletterUnsubscribeHashes` ADD INDEX ( `idSubscriber` ) ;

INSERT INTO `fields` (`id`, `idFieldTypes`, `name`, `idSearchFieldTypes`, `idRelationPage`, `idCategory`, `sqlSelect`, `columns`, `height`, `isCoreField`, `isKeyField`, `isSaveField`, `isRegionTitle`, `isDependentOn`, `showDisplayOptions`, `options`, `copyValue`, `validators`) VALUES (NULL, '9', 'baseportal', '1', NULL, NULL, 'SELECT tbl.id AS id, rootLevelTitles.title AS title FROM rootLevelTitles INNER JOIN rootLevels AS tbl ON tbl.id = rootLevelTitles.idRootLevels WHERE tbl.idRootLevelTypes = 1 AND tbl.active = 1 AND rootLevelTitles.idLanguages = %LANGUAGE_ID% %WHERE_ADDON% ORDER BY rootLevelTitles.title', '12', '0', '1', '1', '1', '0', NULL, '0', NULL, '0', '');

INSERT INTO `fieldTitles` (`id`, `idFields`, `idLanguages`, `title`, `description`) VALUES (NULL, '278', '1', 'Basisportal', NULL), (NULL, '278', '2', 'Base portal', NULL);

INSERT INTO `regionFields` (`id`, `idRegions`, `idFields`, `order`) VALUES (NULL, '96', '278', '40');

ALTER TABLE `newsletters` ADD `baseportal` INT UNSIGNED NULL DEFAULT NULL AFTER `idRootLevelFilters` ;

UPDATE `fields` SET `sqlSelect` = 'SELECT CONCAT(''{"rootlevel":'', tbl.id, '', "language":"'', languages.languageCode, ''"}'') AS id, CONCAT(rootLevelTitles.title, '' '', languages.languageCode) AS title FROM rootLevelTitles INNER JOIN rootLevels AS tbl ON tbl.id = rootLevelTitles.idRootLevels INNER JOIN rootLevelLanguages ON rootLevelLanguages.idRootLevels = tbl.id INNER JOIN languages ON languages.id = rootLevelLanguages.idLanguages WHERE tbl.idRootLevelTypes = 1 AND tbl.active = 1 AND rootLevelTitles.idLanguages = %LANGUAGE_ID% %WHERE_ADDON% ORDER BY rootLevelTitles.title' WHERE `fields`.`id` =278;

ALTER TABLE `newsletters` CHANGE `baseportal` `baseportal` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;


CREATE TABLE IF NOT EXISTS `newsletterStatistics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idNewsletter` bigint(20) unsigned NOT NULL,
  `idSubscriber` bigint(20) unsigned NOT NULL,
  `sent` bigint(1) unsigned NOT NULL,
  `hard_bounced` bigint(1) unsigned NOT NULL,
  `soft_bounced` bigint(1) unsigned NOT NULL,
  `opened` bigint(1) unsigned NOT NULL,
  `clicked` bigint(1) unsigned NOT NULL,
  `spam` bigint(1) unsigned NOT NULL,
  `unsubscribed` bigint(1) unsigned NOT NULL,
  `rejected` bigint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=58 ;


ALTER TABLE `newsletterStatistics` CHANGE `sent` `sent` TINYINT(1) UNSIGNED NOT NULL, CHANGE `hard_bounced` `hard_bounced` TINYINT(1) UNSIGNED NOT NULL, CHANGE `soft_bounced` `soft_bounced` TINYINT(1) UNSIGNED NOT NULL, CHANGE `opened` `opened` TINYINT(1) UNSIGNED NOT NULL, CHANGE `clicked` `clicked` TINYINT(1) UNSIGNED NOT NULL, CHANGE `spam` `spam` TINYINT(1) UNSIGNED NOT NULL, CHANGE `unsubscribed` `unsubscribed` TINYINT(1) UNSIGNED NOT NULL, CHANGE `rejected` `rejected` TINYINT(1) UNSIGNED NOT NULL;

ALTER TABLE `newsletterStatistics` ADD `json` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `rejected` ;

ALTER TABLE `subscribers` CHANGE `hardbounce` `bounced` VARCHAR( 4 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ; 

INSERT INTO `fields` (`id`, `idFieldTypes`, `name`, `idSearchFieldTypes`, `idRelationPage`, `idCategory`, `sqlSelect`, `columns`, `height`, `isCoreField`, `isKeyField`, `isSaveField`, `isRegionTitle`, `isDependentOn`, `showDisplayOptions`, `options`, `copyValue`, `validators`) VALUES (NULL, '9', 'bounced', '1', NULL, NULL, 'SELECT categoryCodes.code AS id, categoryTitles.title AS title FROM categories AS tbl INNER JOIN categoryTitles ON categoryTitles.idCategories = tbl.id AND categoryTitles.idLanguages = %LANGUAGE_ID% INNER JOIN categoryCodes ON categoryCodes.idCategories = tbl.id AND categoryCodes.idLanguages = %LANGUAGE_ID%, categories AS rootCat  WHERE rootCat.id = 691 AND tbl.idRootCategory = rootCat.idRootCategory AND tbl.lft BETWEEN (rootCat.lft + 1) AND rootCat.rgt %WHERE_ADDON% ORDER BY tbl.lft, categoryTitles.title', '3', '0', '1', '0', '1', '0', NULL, '0', NULL, '0', '');
INSERT INTO `fieldTitles` (`id`, `idFields`, `idLanguages`, `title`, `description`) VALUES (NULL, '279', '1', 'Bounced', NULL), (NULL, '279', '2', 'Bounced', NULL);
INSERT INTO `regionFields` (`id`, `idRegions`, `idFields`, `order`) VALUES (NULL, '90', '279', '110');

ALTER TABLE `newsletterStatistics` ENGINE = InnoDB;


ALTER TABLE `subscribers` ADD `reactivated` TIMESTAMP NULL DEFAULT NULL AFTER `changed` ;

ALTER TABLE `subscribers` ADD `optinkey` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `reactivated` 