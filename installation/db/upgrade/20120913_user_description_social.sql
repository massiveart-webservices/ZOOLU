--
-- add new fields to users table
--
ALTER TABLE `users`  ADD `googlePlus` VARCHAR(255) NULL DEFAULT NULL AFTER `sname`,  
    ADD `twitter` VARCHAR(255) NULL DEFAULT NULL AFTER `googlePlus`,  
    ADD `facebook` VARCHAR(255) NULL DEFAULT NULL AFTER `twitter`,  
    ADD `description` TEXT NULL DEFAULT NULL AFTER `facebook` ;