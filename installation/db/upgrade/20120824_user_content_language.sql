--
-- disable foreign key checks
--
SET FOREIGN_KEY_CHECKS=0;

--
-- update for table `users`
--
ALTER TABLE `users` ADD `idContentLanguages` INT( 10 ) UNSIGNED NOT NULL DEFAULT '1' AFTER `idLanguages` ;

--
-- enable foreign key checks
--
SET FOREIGN_KEY_CHECKS=1;