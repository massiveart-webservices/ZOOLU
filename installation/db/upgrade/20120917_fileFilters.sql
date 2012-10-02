SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `zo-zoolu`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `fileFilters`
--

DROP TABLE IF EXISTS `fileFilters`;
CREATE TABLE IF NOT EXISTS `fileFilters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idFiles` bigint(20) unsigned NOT NULL,
  `idCategories` bigint(20) unsigned NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idFiles` (`idFiles`,`idCategories`),
  KEY `idCategories` (`idCategories`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `fileFilters`
--
ALTER TABLE `fileFilters`
  ADD CONSTRAINT `fileFilters_ibfk_2` FOREIGN KEY (`idCategories`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fileFilters_ibfk_1` FOREIGN KEY (`idFiles`) REFERENCES `files` (`id`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
