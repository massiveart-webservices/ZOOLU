-- Seo Meta Robots
INSERT INTO `fields` (`id`, `idFieldTypes`, `name`, `idSearchFieldTypes`, `idRelationPage`, `idCategory`, `sqlSelect`, `columns`, `height`, `isCoreField`, `isKeyField`, `isSaveField`, `isRegionTitle`, `isDependentOn`, `showDisplayOptions`, `options`, `copyValue`, `validators`)
    VALUES (271, '3', 'seo_metarobots', '1', NULL, NULL, 'SELECT tbl.id AS id, categoryTitles.title AS title FROM categories AS tbl INNER JOIN categoryTitles ON categoryTitles.idCategories = tbl.id AND categoryTitles.idLanguages = %LANGUAGE_ID%, categories AS rootCat WHERE rootCat.id = 688 AND tbl.idRootCategory = rootCat.idRootCategory AND tbl.lft BETWEEN ( rootCat.lft +1 ) AND rootCat.rgt %WHERE_ADDON% ORDER BY tbl.lft, categoryTitles.title', '0', '0', '0', '0', '1', '0', NULL, '0', NULL, '0', '');

-- Region Fields
INSERT INTO `regionFields` (`id`, `idRegions`, `idFields`, `order`) VALUES (NULL, '99', '271', '5');

-- Field Titles
INSERT INTO `fieldTitles` (`id`, `idFields`, `idLanguages`, `title`, `description`) VALUES (NULL, '271', '1', 'Meta Robots', NULL);

INSERT INTO categories(id, idParentCategory, idRootCategory, idCategoryTypes, matchCode, lft, rgt, depth) VALUES
(688, 0, 688, 2, NULL, 1, 6, 0),
(689, 688, 688, 2, NULL, 2, 3, 1),
(690, 688, 688, 2, NULL, 4, 5, 1);

INSERT INTO categoryCodes(idCategories, idLanguages, `code`, idUsers, `changed`) VALUES( '688', '1', '', '1', '2012-11-15 15:08:33');
INSERT INTO categoryCodes(idCategories, idLanguages, `code`, idUsers, `changed`) VALUES('689', '1', '', '1', '2012-11-15 15:08:48');
INSERT INTO categoryCodes(idCategories, idLanguages, `code`, idUsers, `changed`) VALUES('690', '1', '', '1', '2012-11-15 15:08:54');

INSERT INTO categoryTitles(idCategories, idLanguages, title, idUsers, `changed`) VALUES('688', '1', 'Meta Robots', '1', '2012-11-15 15:08:32');
INSERT INTO categoryTitles(idCategories, idLanguages, title, idUsers, `changed`) VALUES('689', '1', 'no-index', '1', '2012-11-15 15:08:48');
INSERT INTO categoryTitles(idCategories, idLanguages, title, idUsers, `changed`) VALUES('690', '1', 'no-follow', '1', '2012-11-15 15:08:54');
