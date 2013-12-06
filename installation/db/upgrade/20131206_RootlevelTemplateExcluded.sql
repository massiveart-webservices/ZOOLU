CREATE TABLE IF NOT EXISTS `rootLevelTemplateExcludedFields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idRootLevels` bigint(20) unsigned NOT NULL,
  `idTemplates` bigint(20) unsigned NOT NULL,
  `idFields` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rootLevelTemplateExcludedFields_ibfk_1` (`idTemplates`),
  KEY `rootLevelTemplateExcludedFields_ibfk_2` (`idRootLevels`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ;


ALTER TABLE `rootLevelTemplateExcludedFields`
  ADD CONSTRAINT `rootLevelTemplateExcludedFields_ibfk_1` FOREIGN KEY (`idTemplates`) REFERENCES `templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rootLevelTemplateExcludedFields_ibfk_2`  FOREIGN KEY (`idRootLevels`) REFERENCES `rootLevels` (`id`) ON DELETE CASCADE;





CREATE TABLE IF NOT EXISTS `rootLevelTemplateExcludedRegions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idRootLevels` bigint(20) unsigned NOT NULL,
  `idTemplates` bigint(20) unsigned NOT NULL,
  `idRegions` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rootLevelTemplateExcludedRegions_ibfk_1` (`idTemplates`),
  KEY `rootLevelTemplateExcludedRegions_ibfk_2` (`idRootLevels`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


ALTER TABLE `rootLevelTemplateExcludedRegions`
  ADD CONSTRAINT `rootLevelTemplateExcludedRegions_ibfk_1` FOREIGN KEY (`idTemplates`) REFERENCES `templates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rootLevelTemplateExcludedRegions_ibfk_2`  FOREIGN KEY (`idRootLevels`) REFERENCES `rootLevels` (`id`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS=1;