
-- Field Type `SeoKeywords`
INSERT INTO `fieldTypes` ( `id` , `idDecorator` , `sqlType` , `size` , `title` , `defaultValue` , `idFieldTypeGroup`) VALUES ( 38 , '1', '', '0', 'seoKeywords', '', '5');

-- Update seo_keywords `idFieldTypes` and `options`
UPDATE `fields` SET `idFieldTypes` = '38', `options` = '', `validators` = '' WHERE `name` = 'seo_keywords';
