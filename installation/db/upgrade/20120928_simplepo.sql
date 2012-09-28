-- phpMyAdmin SQL Dump
-- version 3.3.2deb1ubuntu1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 28. September 2012 um 10:32
-- Server Version: 5.1.63
-- PHP-Version: 5.3.2-1ubuntu4.18

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `zo-neutrik`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `simplepo_catalogues`
--

DROP TABLE IF EXISTS `simplepo_catalogues`;
CREATE TABLE IF NOT EXISTS `simplepo_catalogues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `simplepo_messages`
--

DROP TABLE IF EXISTS `simplepo_messages`;
CREATE TABLE IF NOT EXISTS `simplepo_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catalogue_id` int(11) NOT NULL,
  `msgid` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `msgstr` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `comments` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `extracted_comments` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `reference` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `flags` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `is_obsolete` tinyint(1) NOT NULL,
  `is_header` tinyint(1) NOT NULL,
  `previous_untranslated_string` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=558 ;
SET FOREIGN_KEY_CHECKS=1;
