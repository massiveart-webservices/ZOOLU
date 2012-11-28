SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Table `customerStatus`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `customerStatus` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `title` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `title_UNIQUE` (`title` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customerSalutations`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `customerSalutations` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `title` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `title_UNIQUE` (`title` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customers`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `customers` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `registrationKey` VARCHAR(64) NULL ,
  `resetPasswordKey` VARCHAR(64) NULL ,
  `username` VARCHAR(255) NOT NULL ,
  `password` CHAR(32) NOT NULL ,
  `email` VARCHAR(255) NOT NULL ,
  `title` VARCHAR(255) NULL ,
  `fname` VARCHAR(255) NOT NULL ,
  `sname` VARCHAR(255) NOT NULL ,
  `company` VARCHAR(255) NULL ,
  `phone` VARCHAR(255) NULL ,
  `mobile` VARCHAR(255) NULL ,
  `fax` VARCHAR(255) NULL ,
  `idCustomerStatus` BIGINT(20) UNSIGNED NOT NULL ,
  `idCustomerSalutations` BIGINT(20) UNSIGNED ,
  `idRootLevels` BIGINT(20) UNSIGNED NOT NULL ,
  `idUsers` INT(10) UNSIGNED ,
  `creator` INT(10) UNSIGNED ,
  `created` TIMESTAMP NULL ,
  `changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) ,
  INDEX `fk_customers_customerStatus_idx` (`idCustomerStatus` ASC) ,
  INDEX `fk_customers_customerSalutations1_idx` (`idcustomerSalutations` ASC) ,
  INDEX `fk_customers_rootLevels_idx` (`idRootLevels` ASC) ,
  INDEX `fk_customers_users_idx` (`idUsers` ASC) ,
  INDEX `fk_customers_creator_idx` (`creator` ASC),
  INDEX `password` (`password` ASC) ,
  CONSTRAINT `fk_customers_customerStatus`
    FOREIGN KEY (`idCustomerStatus` )
    REFERENCES `customerStatus` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customers_customerSalutations1`
    FOREIGN KEY (`idcustomerSalutations` )
    REFERENCES `customerSalutations` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customers_rootLevels`
    FOREIGN KEY (`idRootLevels` )
    REFERENCES `rootLevels` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customers_users`
    FOREIGN KEY (`idUsers` )
    REFERENCES `users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customers_creator`
    FOREIGN KEY (`creator` )
    REFERENCES `users` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customerAddressTypes`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `customerAddressTypes` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `key` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `key_UNIQUE` (`key` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customerAddresses`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `customerAddresses` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `street` VARCHAR(255) NOT NULL ,
  `zip` VARCHAR(255) NOT NULL ,
  `city` VARCHAR(255) NOT NULL ,
  `state` VARCHAR(255) NOT NULL ,
  `idCountries` INT(11) ,
  `idCustomers` BIGINT(20) UNSIGNED NOT NULL ,
  `idCustomerAddressTypes` BIGINT(20) UNSIGNED ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_customerAddresses_customers1_idx` (`idCustomers` ASC) ,
  INDEX `fk_customerAddresses_customerAddressTypes1_idx` (`idCustomerAddressTypes` ASC) ,
  INDEX `fk_customerAddresses_countries_idx` (`idCountries` ASC),
  CONSTRAINT `fk_customerAddresses_customers1`
    FOREIGN KEY (`idCustomers` )
    REFERENCES `customers` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerAddresses_customerAddressTypes1`
    FOREIGN KEY (`idCustomerAddressTypes` )
    REFERENCES `customerAddressTypes` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerAddresses_countries`
    FOREIGN KEY (`idCountries` )
    REFERENCES `countries` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customerLogTypes`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `customerLogTypes` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `title` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `title_UNIQUE` (`title` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `customerLog`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `customerLog` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `idCustomer` BIGINT(20) UNSIGNED NOT NULL ,
  `idCustomerLogType` BIGINT(20) UNSIGNED NOT NULL ,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  `message` VARCHAR(255) NULL ,
  PRIMARY KEY (`id`) ,

  INDEX `fk_customerLog_customers1_idx` (`idCustomer` ASC) ,
  INDEX `fk_customerLog_customerLogTypes1_idx` (`idCustomerLogType` ASC) ,
  CONSTRAINT `fk_customerLog_customers1`
    FOREIGN KEY (`idCustomer` )
    REFERENCES `customers` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerLog_customerLogTypes1`
    FOREIGN KEY (`idCustomerLogType` )
    REFERENCES `customerLogTypes` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE  TABLE IF NOT EXISTS `customerGroups` (
  `idCustomers` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `idGroups` BIGINT(20) UNSIGNED NOT NULL ,
  PRIMARY KEY (`idCustomers`, `idGroups`) ,
  CONSTRAINT `fk_customerGroups_customers1`
    FOREIGN KEY (`idCustomers` )
    REFERENCES `customers` (`id` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customerGroups_groups`
    FOREIGN KEY (`idGroups` )
    REFERENCES `groups` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

insert into rootLevels values('49', '19', '17', '6', '0', '0', '0', '0', '/zoolu/contacts/index/list', '0', '1', '5', '1');
insert into rootLevelTypes values('19', 'customers');
insert into rootLevelGroups values('17', 'customers');
insert into rootLevelTitles values('97', '49', '1', 'Kunden');
insert into rootLevelTitles values('98', '49', '2', 'Customers');
insert into rootLevelPermission values('49', '1', '1');

ALTER TABLE `rootLevels` 
	ADD COLUMN `idCustomerRegistrationStatus` BIGINT(20) UNSIGNED NOT NULL DEFAULT 1 
	AFTER `languageDefinitionType` ;

ALTER TABLE `rootLevels`
	ADD CONSTRAINT `fk_rootLevels_customerStatus`
	FOREIGN KEY (`idCustomerRegistrationStatus`)
	REFERENCES `customerStatus` (`id`)
	ON DELETE NO ACTION
	ON UPDATE NO ACTION;
	


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
