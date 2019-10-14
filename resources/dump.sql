-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema zipdev
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema zipdev
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `zipdev` DEFAULT CHARACTER SET utf8 ;
USE `zipdev` ;

-- -----------------------------------------------------
-- Table `zipdev`.`persons`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `zipdev`.`persons` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(45) NOT NULL,
  `surnames` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `zipdev`.`phones`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `zipdev`.`phones` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `phone` VARCHAR(15) NOT NULL,
  `persons_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `FK_persons` (`persons_id` ASC),
  UNIQUE INDEX `UQ_phone_persons` (`phone` ASC, `persons_id` ASC),
  CONSTRAINT `fk_phones_1`
    FOREIGN KEY (`persons_id`)
    REFERENCES `zipdev`.`persons` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `zipdev`.`emails`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `zipdev`.`emails` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL,
  `persons_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `FK_persons` (`persons_id` ASC),
  UNIQUE INDEX `UQ_email_person` (`email` ASC, `persons_id` ASC),
  CONSTRAINT `fk_emails_1`
    FOREIGN KEY (`persons_id`)
    REFERENCES `zipdev`.`persons` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
