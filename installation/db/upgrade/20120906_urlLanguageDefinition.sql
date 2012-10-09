-- define how the language is defined in url
ALTER TABLE `rootLevels` ADD `languageDefinitionType` INT( 1 ) UNSIGNED NULL DEFAULT '1' AFTER `idThemes`;

-- define the default prefix (www e.g.) use if languages are defined in subdomain and one language is mayor and uses this prefix (www.massiveart.com e.g.)
ALTER TABLE `rootLevelUrls` ADD `hostPrefix` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `isMain`; 