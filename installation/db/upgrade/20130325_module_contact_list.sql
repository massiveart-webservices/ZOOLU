--
-- create generic form for contact company type
--
INSERT INTO  `genericForms` (
`id` ,
`idUsers` ,
`genericFormId` ,
`version` ,
`created` ,
`changed` ,
`idGenericFormTypes` ,
`mandatoryUpgrade`
)
VALUES (
'50',  '3',  'DEFAULT_CONTACT_COMPANY',  '1', NOW( ) ,
CURRENT_TIMESTAMP ,  '5',  '0'
);

INSERT INTO  `tabs` (
`id` ,
`color` ,
`action`
)
VALUES (
50 , NULL , NULL
);

INSERT INTO `tabRegions` (`id`, `idTabs`, `idRegions`, `order`)
VALUES (NULL, '50', '106', '10'),
(NULL, '50', '61', '20'),
(NULL, '50', '21', '30');

INSERT INTO  `genericFormTabs` (
`id` ,
`idGenericForms` ,
`idTabs` ,
`order`
)
VALUES (
NULL ,  '50',  '50',  '1'
);

--
-- add new rootlevel type with id 20
--
INSERT INTO  `rootLevelTypes` (
`id` ,
`title`
)
VALUES (
20 ,  'contactcompanies'
);

--
-- add rootlevel with id 60
--
INSERT INTO `rootLevels` (`id`, `idRootLevelTypes`, `idRootLevelGroups`, `idModules`, `isSecure`, `hasSegments`, `hasPortalGate`, `landingPages`, `href`, `idThemes`, `languageDefinitionType`, `idCustomerRegistrationStatus`, `registrationStrategy`, `order`, `active`) VALUES
('60', '20', '4', '6', '0', '0', '0', '0', '/zoolu/contacts/index/list', NULL, NULL, '1', NULL, '2', '1');

INSERT INTO  `rootLevelTitles` (
`id` ,
`idRootLevels` ,
`idLanguages` ,
`title`
)
VALUES (
NULL ,  '60',  '1',  'Firmen'
), (
NULL ,  '60',  '2',  'Companies'
);

INSERT INTO  `rootLevelGroupTitles` (
`id` ,
`idRootLevelGroups` ,
`idLanguages` ,
`title`
)
VALUES (
NULL ,  '4',  '1',  'Kontakte'
), (
NULL ,  '4',  '2',  'Contacts'
);

INSERT INTO  `rootLevelPermissions` (
`idRootLevels` ,
`zone` ,
`idGroups`
)
VALUES (
'60',  '1',  '1'
), (
'60',  '1',  '155'
), (
'60',  '1',  '156'
);

--
-- add column idContactTypes to contacts table
--
ALTER TABLE  `contacts` ADD  `idContactTypes` INT( 10 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `id`;

--
-- add table contact types
--
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `contactTypes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `contactTypes` (`id`, `title`) VALUES
(1, 'contact'),
(2, 'company');

SET FOREIGN_KEY_CHECKS=1;
--
-- END: add table contact types
--

--
-- add table contact-DEFAULT_CONTACT_COMPANY-1-InstanceFiles
--
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `contact-DEFAULT_CONTACT_COMPANY-1-InstanceFiles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idContacts` bigint(20) UNSIGNED NOT NULL,
  `sortPosition` int(10) UNSIGNED NOT NULL,
  `idFiles` bigint(20) UNSIGNED NOT NULL,
  `idFields` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idContacts`(idContacts),
  INDEX `idFiles`(idFiles),
  CONSTRAINT `contact-DEFAULT_CONTACT_COMPANY-1-InstanceFiles_ibfk_1` FOREIGN KEY (`idContacts`) REFERENCES `contacts` (`id`)  ON DELETE CASCADE,
  CONSTRAINT `contact-DEFAULT_CONTACT_COMPANY-1-InstanceFiles_ibfk_2` FOREIGN KEY (`idFiles`) REFERENCES `files` (`id`)  ON DELETE CASCADE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

SET FOREIGN_KEY_CHECKS=1;

--
-- END: add table contact-DEFAULT_CONTACT_COMPANY-1-InstanceFiles
--