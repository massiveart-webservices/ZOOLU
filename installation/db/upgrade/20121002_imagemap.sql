INSERT INTO `zo-divina`.`decorators` (`id`, `title`) VALUES ('11', 'Imagemap');

INSERT INTO `zo-hoval`.`fieldTypes` (`id`, `idDecorator`, `sqlType`, `size`, `title`, `defaultValue`, `idFieldTypeGroup`) VALUES ('35', '11', '', '0', 'imagemap', '', '4');

INSERT INTO `zo-hoval`.`regionTypes` (`id`, `title`) VALUES ('3', 'unique');

CREATE TABLE IF NOT EXISTS `pageImagemaps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pageId` varchar(32) NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `idLanguages` int(10) unsigned NOT NULL,
  `idFields` bigint(20) unsigned NOT NULL,
  `idFiles` bigint(20) unsigned NOT NULL,
  `size` varchar(32) DEFAULT NULL,
  `idTargetRegion` bigint(20) unsigned NOT NULL,
  `markers` text,
  PRIMARY KEY (`id`),
  KEY `pageId` (`pageId`,`idFields`,`idFiles`),
  KEY `idFiles` (`idFiles`),
  KEY `idFields` (`idFields`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=125 ;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `pageImagemaps`
--
ALTER TABLE `pageImagemaps`
  ADD CONSTRAINT `pageImagemaps_ibfk_3` FOREIGN KEY (`idFiles`) REFERENCES `files` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pageImagemaps_ibfk_1` FOREIGN KEY (`pageId`) REFERENCES `pages` (`pageId`) ON DELETE CASCADE,
  ADD CONSTRAINT `pageImagemaps_ibfk_2` FOREIGN KEY (`idFields`) REFERENCES `fields` (`id`) ON DELETE CASCADE;