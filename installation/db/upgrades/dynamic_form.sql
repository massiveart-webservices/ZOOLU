-- phpMyAdmin SQL Dump
-- version 3.2.2.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 22. November 2011 um 11:38
-- Server Version: 5.1.37
-- PHP-Version: 5.2.10-2ubuntu6.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `zo-ivoclarvivadent`
--

-- --------------------------------------------------------

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=4 ;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `page-DEFAULT_FORM-1-Region100-InstanceMultiFields`
--
ALTER TABLE `page-DEFAULT_FORM-1-Region100-InstanceMultiFields`
  ADD CONSTRAINT `page-DEFAULT_FORM-1-Region100-InstanceMultiFields_ibfk_1` FOREIGN KEY (`idRegionInstances`) REFERENCES `page-DEFAULT_FORM-1-Region100-Instances` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `page-DEFAULT_FORM-1-Region100-InstanceMultiFields_ibfk_2` FOREIGN KEY (`pageId`) REFERENCES `pages` (`pageId`) ON DELETE CASCADE;
