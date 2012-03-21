CREATE TABLE IF NOT EXISTS `rootLevelTemplates` (
  `idRootLevels` bigint(20) unsigned NOT NULL,
  `idTemplates` bigint(20) unsigned NOT NULL,
  UNIQUE KEY `idRootLevels_2` (`idRootLevels`,`idTemplates`),
  KEY `idRootLevels` (`idRootLevels`),
  KEY `idTemplates` (`idTemplates`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;



ALTER TABLE `rootLevelTemplates`
  ADD CONSTRAINT `rootLevelTemplates_ibfk_1` FOREIGN KEY (`idRootLevels`) REFERENCES `rootLevels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rootLevelTemplates_ibfk_2` FOREIGN KEY (`idTemplates`) REFERENCES `templates` (`id`) ON DELETE CASCADE;
  
  
INSERT INTO `zo-zoolu`.`rootLevelTemplates` (`idRootLevels`, `idTemplates`) SELECT rootLevels.id, templates.id FROM rootLevels, templates WHERE rootLevels.idRootLevelTypes IN (1,11,14,17);


