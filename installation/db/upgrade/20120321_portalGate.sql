--
-- add field hasPortalGate in table rootLevels
--
ALTER TABLE `rootLevels` ADD `hasPortalGate` BOOLEAN NOT NULL DEFAULT '0' AFTER `hasSegments` ;