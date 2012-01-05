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
-- alter table `rootLevels`
--
ALTER TABLE `rootLevels` ADD `hasSegments` BOOLEAN NOT NULL DEFAULT '0' AFTER `isSecure`;

--
-- enable foreign key checks
--
SET FOREIGN_KEY_CHECKS=1;