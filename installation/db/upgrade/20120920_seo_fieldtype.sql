-- add new field type `seo`
INSERT INTO `fieldTypes` (`id`, `idDecorator`, `sqlType`, `size`, `title`, `defaultValue`, `idFieldTypeGroup`) VALUES ('35', '1', '', '0', 'seo', '', '5');

-- update seo_description, seo_keywords, seo_title, seo_canonical fields types to `seo`
UPDATE `fields` SET `idFieldTypes` = '35' WHERE `name` = 'seo_description';
UPDATE `fields` SET `idFieldTypes` = '35' WHERE `name` = 'seo_keywords';
UPDATE `fields` SET `idFieldTypes` = '35' WHERE `name` = 'seo_title';

-- update seo fields `options`
UPDATE `fields` SET `options` = '{"textbox":"textarea", "seoname":"meta description", "charslimit":"156"}' WHERE `name` = 'seo_description';

UPDATE `fields` SET `options` = '{"textbox":"textarea", "seoname":"meta keywords", "charslimit":"156"}' WHERE `name` = 'seo_keywords';

UPDATE `fields` SET `options` = '{"textbox":"text", "seoname":"page title", "charslimit":"70"}' WHERE `name` = 'seo_title';