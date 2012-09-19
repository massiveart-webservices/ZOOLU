-- add new field type `seo`
INSERT INTO `fieldtypes` (`id`, `idDecorator`, `sqlType`, `size`, `title`, `defaultValue`, `idFieldTypeGroup`) VALUES ('35', '0', '', '0', 'seo', '', '4');

-- update seo_description, seo_keywords, seo_title, seo_canonical fields types to `seo`
UPDATE `fields` SET `idFieldTypes` = '35' WHERE `fields`.`id` = 247;
UPDATE `fields` SET `idFieldTypes` = '35' WHERE `fields`.`id` = 248;
UPDATE `fields` SET `idFieldTypes` = '35' WHERE `fields`.`id` = 249;
UPDATE `fields` SET `idFieldTypes` = '35' WHERE `fields`.`id` = 250;

-- update seo fields `options`
UPDATE `fields` SET `options` = '{"textbox":"textarea", "seoname":"meta description", "charslimit":"156"}'
                                WHERE `fields`.`id` = 247;
UPDATE `fields` SET `options` = '{"textbox":"textarea", "seoname":"meta keywords", "charslimit":"156"}'
                                WHERE `fields`.`id` =248;
UPDATE `fields` SET `options` = '{"textbox":"textarea", "seoname":"meta keywords", "charslimit":"156"}'
                                WHERE `fields`.`id` =249;
UPDATE `fields` SET `options` = '{"textbox":"text", "charslimit":"70"}'
                                WHERE `fields`.`id` =250;
UPDATE `fields` SET `options` = '{"textbox":"text"}'
                                WHERE `fields`.`id` =251;