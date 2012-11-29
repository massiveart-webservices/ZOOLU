UPDATE `zo-zoolu`.`fieldTitles` SET `title`='Beschreibung' WHERE `id`='395';
UPDATE `zo-zoolu`.`fieldTitles` SET `title`='Vorschau Suchmaschinen Ergebnisseite' WHERE `id`='419';
UPDATE `zo-zoolu`.`fieldTitles` SET `title`='Titel' WHERE `id`='397';
UPDATE `zo-zoolu`.`fieldTitles` SET `title`='Canonical URL (Canonical Tag)' WHERE `id`='398';
UPDATE `zo-zoolu`.`fieldTitles` SET `title`='Robots Einstellungen (robots.txt)' WHERE `id`='420';

UPDATE `zo-zoolu`.`fields` SET `options`='{\"textbox\":\"textarea\", \"seoname\":\"Die Meta Beschreibung\", \"charslimit\":\"156\"}' WHERE `id`='247';
UPDATE `zo-zoolu`.`fields` SET `options`='{\"textbox\":\"text\", \"seoname\":\"Der Meta Titel\", \"charslimit\":\"70\"}' WHERE `id`='250';

UPDATE `zo-zoolu`.`categoryTitles` SET `title`='no-index (Seite wird nicht von der Suchmaschine indiziert)' WHERE `id`='5449';
UPDATE `zo-zoolu`.`categoryTitles` SET `title`='no-follow (Verlinkungen auf der Seite werden nicht vom Suchmaschinen Bot verfolgt)' WHERE `id`='5450';

UPDATE `zo-zoolu`.`fieldTitles` SET `description`='Wenn der Inhalt dieser Seite mit einer anderen identisch ist (Duplicate Content), können Sie hier einen Link auf die Original-Seite setzen.' WHERE `id`='398';

INSERT INTO `zo-zoolu`.`tabTitles` (`idTabs`, `idLanguages`, `title`) VALUES ('45', '2', 'SEO');
INSERT INTO `zo-zoolu`.`fieldTitles` (`idFields`, `idLanguages`, `title`) VALUES ('262', '2', 'Search Engine Result Page Preview');
INSERT INTO `zo-zoolu`.`fieldTitles` (`idFields`, `idLanguages`, `title`) VALUES ('247', '2', 'Description');
INSERT INTO `zo-zoolu`.`fieldTitles` (`idFields`, `idLanguages`, `title`) VALUES ('248', '2', 'Keywords');
INSERT INTO `zo-zoolu`.`fieldTitles` (`idFields`, `idLanguages`, `title`) VALUES ('250', '2', 'Title');
INSERT INTO `zo-zoolu`.`fieldTitles` (`idFields`, `idLanguages`, `title`) VALUES ('251', '2', 'Canonical URL (Canonical Tag)');

INSERT INTO `zo-zoolu`.`categoryTitles` (`idCategories`, `idLanguages`, `title`, `idUsers`, `changed`) VALUES ('689', '2', 'no-index (Page not indexed by search engines)', '1', '2012-11-29 15:06:08');
INSERT INTO `zo-zoolu`.`categoryTitles` (`idCategories`, `idLanguages`, `title`, `idUsers`, `changed`) VALUES ('690', '2', 'no-follow (Links on page not followed by search engine bots)', '1', '2012-11-29 15:06:08');

