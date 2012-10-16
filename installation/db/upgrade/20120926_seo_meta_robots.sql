-- Seo Meta Robots
INSERT INTO `fields` (`id`, `idFieldTypes`, `name`, `idSearchFieldTypes`, `idRelationPage`, `idCategory`, `sqlSelect`, `columns`, `height`, `isCoreField`, `isKeyField`, `isSaveField`, `isRegionTitle`, `isDependentOn`, `showDisplayOptions`, `options`, `copyValue`, `validators`)
    VALUES (NULL, '3', 'seo_metarobots', '1', NULL, NULL, 'SELECT tbl.id AS id, categoryTitles.title AS title FROM categories AS tbl INNER JOIN categoryTitles ON categoryTitles.idCategories = tbl.id AND categoryTitles.idLanguages = %LANGUAGE_ID%, categories AS rootCat WHERE rootCat.id = 676 AND tbl.idRootCategory = rootCat.idRootCategory AND tbl.lft BETWEEN ( rootCat.lft +1 ) AND rootCat.rgt %WHERE_ADDON% ORDER BY tbl.lft, categoryTitles.title', '0', '0', '0', '0', '1', '0', NULL, '0', NULL, '0', '');

-- Region Fields
INSERT INTO `regionFields` (`id`, `idRegions`, `idFields`, `order`) VALUES (NULL, '99', '263', '5');

-- Field Titles
INSERT INTO `fieldTitles` (`id`, `idFields`, `idLanguages`, `title`, `description`) VALUES (NULL, '263', '1', 'Meta Robots', NULL);
