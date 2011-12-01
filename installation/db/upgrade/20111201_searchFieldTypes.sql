SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Datenbank: `zo-zoolu`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `searchFieldTypes`
--

DROP TABLE IF EXISTS `searchFieldTypes`;
CREATE TABLE IF NOT EXISTS `searchFieldTypes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT AUTO_INCREMENT=8 ;

--
-- Daten für Tabelle `searchFieldTypes`
--

INSERT INTO `searchFieldTypes` (`id`, `title`, `description`) VALUES
(1, 'None', NULL),
(2, 'Keyword', 'Keyword fields are stored and indexed, meaning that they can be searched as well as displayed in search results. They are not split up into separate words by tokenization. Enumerated database fields usually translate well to Keyword fields in Zend_Search_Lucene.'),
(3, 'UnIndexed', 'UnIndexed fields are not searchable, but they are returned with search hits. Database timestamps, primary keys, file system paths, and other external identifiers are good candidates for UnIndexed fields.'),
(4, 'Binary', 'Binary fields are not tokenized or indexed, but are stored for retrieval with search hits. They can be used to store any data encoded as a binary string, such as an image icon.'),
(5, 'Text', 'Text fields are stored, indexed, and tokenized. Text fields are appropriate for storing information like subjects and titles that need to be searchable as well as returned with search results.'),
(6, 'UnStored', 'UnStored fields are tokenized and indexed, but not stored in the index. Large amounts of text are best indexed using this type of field. Storing data creates a larger index on disk, so if you need to search but not redisplay the data, use an UnStored field. UnStored fields are practical when using a Zend_Search_Lucene index in combination with a relational database. You can index large data fields with UnStored fields for searching, and retrieve them from your relational database by using a separate field as an identifier.'),
(7, 'SummaryIndexed', 'Stored and indexed in Summary, but not indexed individual. For example it is used for shortdescription.');
SET FOREIGN_KEY_CHECKS=1;


# PLEASE REGARD
# 
# Edit searchFieldTypes of fields to prevent of double indexed fields.
# Change searchFieldType from 5 to 7 when field should be stored but only
# indexed in the summary "body" field in the Searchindex.
#

