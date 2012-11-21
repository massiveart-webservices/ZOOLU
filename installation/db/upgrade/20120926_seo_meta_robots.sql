-- Seo Meta Robots
INSERT INTO `fields` (`id`, `idFieldTypes`, `name`, `idSearchFieldTypes`, `idRelationPage`, `idCategory`, `sqlSelect`, `columns`, `height`, `isCoreField`, `isKeyField`, `isSaveField`, `isRegionTitle`, `isDependentOn`, `showDisplayOptions`, `options`, `copyValue`, `validators`)
    VALUES (NULL, '3', 'seo_metarobots', '1', NULL, NULL, 'SELECT tbl.id AS id, categoryTitles.title AS title FROM categories AS tbl INNER JOIN categoryTitles ON categoryTitles.idCategories = tbl.id AND categoryTitles.idLanguages = %LANGUAGE_ID%, categories AS rootCat WHERE rootCat.id = 676 AND tbl.idRootCategory = rootCat.idRootCategory AND tbl.lft BETWEEN ( rootCat.lft +1 ) AND rootCat.rgt %WHERE_ADDON% ORDER BY tbl.lft, categoryTitles.title', '0', '0', '0', '0', '1', '0', NULL, '0', NULL, '0', '');

-- Region Fields
INSERT INTO `regionFields` (`id`, `idRegions`, `idFields`, `order`) VALUES (NULL, '99', '263', '5');

-- Field Titles
INSERT INTO `fieldTitles` (`id`, `idFields`, `idLanguages`, `title`, `description`) VALUES (NULL, '263', '1', 'Meta Robots', NULL);

INSERT INTO categories(id, idParentCategory, idRootCategory, idCategoryTypes, matchCode, lft, rgt, depth) VALUES('676', '0', '676', '2', NULL, '1', '6', '0');
INSERT INTO categories(id, idParentCategory, idRootCategory, idCategoryTypes, matchCode, lft, rgt, depth) VALUES('677', '676', '676', '2', NULL, '2', '3', '1');
INSERT INTO categories(id, idParentCategory, idRootCategory, idCategoryTypes, matchCode, lft, rgt, depth) VALUES('678', '676', '676', '2', NULL, '4', '5', '1');

INSERT INTO categoryCodes(idCategories, idLanguages, `code`, idUsers, `changed`) VALUES( '676', '1', '', '1', '2012-11-15 15:08:33');
INSERT INTO categoryCodes(idCategories, idLanguages, `code`, idUsers, `changed`) VALUES('677', '1', '', '1', '2012-11-15 15:08:48');
INSERT INTO categoryCodes(idCategories, idLanguages, `code`, idUsers, `changed`) VALUES('678', '1', '', '1', '2012-11-15 15:08:54');

INSERT INTO categoryTitles(idCategories, idLanguages, title, idUsers, `changed`) VALUES('676', '1', 'Meta Robots', '1', '2012-11-15 15:08:32');
INSERT INTO categoryTitles(idCategories, idLanguages, title, idUsers, `changed`) VALUES('677', '1', 'no-index', '1', '2012-11-15 15:08:48');
INSERT INTO categoryTitles(idCategories, idLanguages, title, idUsers, `changed`) VALUES('678', '1', 'no-follow', '1', '2012-11-15 15:08:54');
