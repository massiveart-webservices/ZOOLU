INSERT INTO `fieldTypes` (`id`, `idDecorator`, `sqlType`, `size`, `title`, `defaultValue`, `idFieldTypeGroup`)
          VALUES (37, '1', '', '0', 'snippetPreview', '', '5');

INSERT INTO `fields` (`id`, `idFieldTypes`, `name`, `idSearchFieldTypes`, `idRelationPage`, `idCategory`, `sqlSelect`, `columns`, `height`, `isCoreField`, `isKeyField`, `isSaveField`, `isRegionTitle`, `isDependentOn`, `showDisplayOptions`, `options`, `copyValue`, `validators`)
          VALUES (NULL, '37', 'seo_snippetpreview', '1', NULL, NULL, NULL, '12', '0', '0', '0', '0', '0', NULL, '0', NULL, '0', '');

INSERT INTO `regionFields` (`id`, `idRegions`, `idFields`, `order`) VALUES (NULL, '99', '270', '0');

INSERT INTO `fieldTitles` (`id`, `idFields`, `idLanguages`, `title`, `description`) VALUES (NULL, '270', '1', 'Snippet Preview', NULL);