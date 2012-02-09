--
-- disable foreign key checks
--
SET FOREIGN_KEY_CHECKS=0;

--
-- create table `segments`
--
CREATE TABLE IF NOT EXISTS `segments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- default data for table `segments`
--
INSERT INTO `segments` (`id`, `code`, `name`) VALUES
(1, 's', 'summer'),
(2, 'w', 'winter');

--
-- create table `segmentTitles`
--
CREATE TABLE IF NOT EXISTS `segmentTitles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idSegments` int(10) unsigned NOT NULL,
  `idLanguages` int(10) unsigned NOT NULL DEFAULT '1',
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idSegments` (`idSegments`),
  KEY `idSegments_2` (`idSegments`,`idLanguages`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- default data for table `segmentTitles`
--
INSERT INTO `segmentTitles` (`id`, `idSegments`, `idLanguages`, `title`) VALUES
(1, 1, 1, 'Sommer'),
(2, 1, 2, 'Summer'),
(3, 2, 1, 'Winter'),
(4, 2, 2, 'Winter');

--
-- add constraints for table `segmentTitles`
--
ALTER TABLE `segmentTitles`
  ADD CONSTRAINT `segmentTitles_ibfk_1` FOREIGN KEY (`idSegments`) REFERENCES `segments` (`id`) ON DELETE CASCADE;

--
-- create table `rootLevelSegments`
--
CREATE TABLE IF NOT EXISTS `rootLevelSegments` (
  `idRootLevels` bigint(20) unsigned NOT NULL,
  `idSegments` int(10) unsigned NOT NULL,
  PRIMARY KEY (`idRootLevels`,`idSegments`),
  KEY `idRootLevel` (`idRootLevels`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- add constraints for table `rootLevelSegments`
--
ALTER TABLE `rootLevelSegments`
  ADD CONSTRAINT `rootLevelSegments_ibfk_2` FOREIGN KEY (`idSegments`) REFERENCES `segments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rootLevelSegments_ibfk_1` FOREIGN KEY (`idRootLevels`) REFERENCES `rootLevels` (`id`) ON DELETE CASCADE;

--
-- alter table `pages`
--
ALTER TABLE `pages` ADD `idSegments` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `version`;

--
-- alter table `rootLevels`
--
ALTER TABLE `rootLevels` ADD `hasSegments` BOOLEAN NOT NULL DEFAULT '0' AFTER `isSecure`;

--
-- enable foreign key checks
--
SET FOREIGN_KEY_CHECKS=1;