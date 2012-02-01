--
-- Tabellenstruktur für Tabelle `pageDynForm`
--

CREATE TABLE IF NOT EXISTS `pageDynForm` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `idPages` bigint(20) NOT NULL,
  `idRootLevels` bigint(20) NOT NULL,
  `content` text NOT NULL COMMENT 'json',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idRootLevels` (`idRootLevels`),
  KEY `idPages` (`idPages`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Tabellenstruktur für Tabelle `page-DEFAULT_FORM-1-Region100-Instances`
--

CREATE TABLE IF NOT EXISTS `page-DEFAULT_FORM-1-Region100-Instances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pageId` varchar(32) NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `idLanguages` int(10) unsigned NOT NULL DEFAULT '1',
  `sortPosition` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `field_type` bigint(20) NOT NULL,
  `mandatory` bigint(20) NOT NULL,
  `validation` bigint(20) NOT NULL,
  `display` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pageId` (`pageId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ;

--
-- Tabellenstruktur für Tabelle `page-DEFAULT_FORM-1-Region100-InstanceMultiFields`
--

CREATE TABLE IF NOT EXISTS `page-DEFAULT_FORM-1-Region100-InstanceMultiFields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pageId` varchar(32) NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `idLanguages` int(10) unsigned NOT NULL DEFAULT '1',
  `idRegionInstances` bigint(20) unsigned NOT NULL,
  `idRelation` bigint(20) unsigned NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `idFields` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pageId` (`pageId`),
  KEY `idRegionInstances` (`idRegionInstances`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ;

--
-- Constraints der exportierten Tabellen
--

ALTER TABLE `page-DEFAULT_FORM-1-Region100-Instances` 
  ADD FOREIGN KEY ( `pageId` ) REFERENCES `pages` (`pageId`) ON DELETE CASCADE ;

ALTER TABLE `page-DEFAULT_FORM-1-Region100-InstanceMultiFields`
  ADD FOREIGN KEY (`idRegionInstances`) REFERENCES `page-DEFAULT_FORM-1-Region100-Instances` (`id`) ON DELETE CASCADE,
  ADD FOREIGN KEY (`pageId`) REFERENCES `pages` (`pageId`) ON DELETE CASCADE;
