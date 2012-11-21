
-- Field Type `SeoKeywords`
INSERT INTO `fieldTypes` ( `id` , `idDecorator` , `sqlType` , `size` , `title` , `defaultValue` , `idFieldTypeGroup`) VALUES ( NULL , '1', '', '0', 'seoKeywords', '', '5');

-- Update seo_keywords `idFieldTypes` and `options`
UPDATE `fields` SET `idFieldTypes` = '37', `options` = '', `validators` = '["SeoKeywords"]' WHERE `name` = 'seo_keywords';
