-- meta index, meta follow
ALTER TABLE `page-DEFAULT_PAGE_1-1-Instances` ADD `seo_metaindex` TEXT NOT NULL AFTER `seo_canonical`;
ALTER TABLE `page-DEFAULT_PAGE_1-1-Instances` ADD `seo_metafollow` TEXT NOT NULL AFTER `seo_metaindex`;

-- Seo Meta Index Field
INSERT INTO `fields` (`id`, `idFieldTypes`, `name`, `idSearchFieldTypes`, `idRelationPage`, `idCategory`, `sqlSelect`, `columns`, `height`, `isCoreField`, `isKeyField`, `isSaveField`, `isRegionTitle`, `isDependentOn`, `showDisplayOptions`, `options`, `copyValue`, `validators`)
    VALUES (NULL, '4', 'seo_metaindex', '1', NULL, NULL, 'SELECT categoryTitles.title AS id, categoryTitles.title AS title FROM categories AS tbl INNER JOIN categoryTitles ON categoryTitles.idCategories = tbl.id AND categoryTitles.idLanguages = %LANGUAGE_ID%, categories AS rootCat WHERE rootCat.id = 666 AND tbl.idRootCategory = rootCat.idRootCategory AND tbl.lft BETWEEN ( rootCat.lft +1 ) AND rootCat.rgt %WHERE_ADDON% ORDER BY tbl.lft, categoryTitles.title', '0', '0', '0', '0', '1', '0', NULL, '0', NULL, '0', '');
-- Seo Meta Follow Field
INSERT INTO `fields` (`id`, `idFieldTypes`, `name`, `idSearchFieldTypes`, `idRelationPage`, `idCategory`, `sqlSelect`, `columns`, `height`, `isCoreField`, `isKeyField`, `isSaveField`, `isRegionTitle`, `isDependentOn`, `showDisplayOptions`, `options`, `copyValue`, `validators`)
    VALUES (NULL, '4', 'seo_metafollow', '1', NULL, NULL, 'SELECT categoryTitles.title AS id, categoryTitles.title AS title FROM categories AS tbl INNER JOIN categoryTitles ON categoryTitles.idCategories = tbl.id AND categoryTitles.idLanguages = %LANGUAGE_ID%, categories AS rootCat WHERE rootCat.id = 669 AND tbl.idRootCategory = rootCat.idRootCategory AND tbl.lft BETWEEN ( rootCat.lft +1 ) AND rootCat.rgt %WHERE_ADDON% ORDER BY tbl.lft, categoryTitles.title', '0', '0', '0', '0', '1', '0', NULL, '0', NULL, '0', '');

-- Region Fields
INSERT INTO `regionFields` (`id`, `idRegions`, `idFields`, `order`) VALUES (NULL, '99', '263', '5');
INSERT INTO `regionFields` (`id`, `idRegions`, `idFields`, `order`) VALUES (NULL, '99', '264', '5');

-- Field Titles
INSERT INTO `fieldTitles` (`id`, `idFields`, `idLanguages`, `title`, `description`) VALUES (NULL, '263', '1', 'Meta Robots Index', NULL);
INSERT INTO `fieldTitles` (`id`, `idFields`, `idLanguages`, `title`, `description`) VALUES (NULL, '264', '1', 'Meta Robots Follow', NULL);

-- Categories
INSERT INTO `categories` (`id`, `idParentCategory`, `idRootCategory`, `idCategoryTypes`, `matchCode`, `lft`, `rgt`, `depth`) VALUES (NULL, '0', '666', '2', NULL, '1', '6', '0');
INSERT INTO `categories` (`id`, `idParentCategory`, `idRootCategory`, `idCategoryTypes`, `matchCode`, `lft`, `rgt`, `depth`) VALUES (NULL, '666', '666', '2', NULL, '2', '3', '1'),
                                                                                                                                    (NULL, '666', '666', '2', NULL, '4', '5', '1');

-- Child Categories
INSERT INTO `categories` (`id`, `idParentCategory`, `idRootCategory`, `idCategoryTypes`, `matchCode`, `lft`, `rgt`, `depth`) VALUES (NULL, '0', '669', '2', NULL, '1', '6', '0');
INSERT INTO `categories` (`id`, `idParentCategory`, `idRootCategory`, `idCategoryTypes`, `matchCode`, `lft`, `rgt`, `depth`) VALUES (NULL, '669', '669', '2', NULL, '2', '3', '1'),
                                                                                                                                    (NULL, '669', '669', '2', NULL, '4', '5', '1');

-- Category titles
INSERT INTO `categoryTitles` (`id`, `idCategories`, `idLanguages`, `title`, `idUsers`, `changed`) VALUES (NULL, '667', '1', 'index', '2', CURRENT_TIMESTAMP),
                                                                                                         (NULL, '668', '1', 'noindex', '2', CURRENT_TIMESTAMP);
INSERT INTO `categoryTitles` (`id`, `idCategories`, `idLanguages`, `title`, `idUsers`, `changed`) VALUES (NULL, '670', '1', 'follow', '2', CURRENT_TIMESTAMP),
                                                                                                         (NULL, '671', '1', 'nofollow', '2', CURRENT_TIMESTAMP);