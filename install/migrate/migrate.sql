CREATE TABLE IF NOT EXISTS `plan3dHeader` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(127) NULL,
  `configuration` TEXT NULL,
  PRIMARY KEY (`id`))
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `plan3d` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL,
  `plan3dHeader_id` INT NOT NULL,
  `link_type` VARCHAR(127) NULL,
  `link_id` VARCHAR(127) NULL,
  `position` TEXT NULL,
  `display` TEXT NULL,
  `css` TEXT NULL,
  `configuration` TEXT NULL,
  PRIMARY KEY (`id`),
  INDEX `name` (`name` ASC),
  INDEX `link_type_link_id` (`link_type` ASC, `link_id` ASC),
  INDEX `fk_plan3d_plan3dHeader1_idx` (`plan3dHeader_id` ASC),
  CONSTRAINT `fk_plan3d_plan3dHeader1`
  FOREIGN KEY (`plan3dHeader_id`)
  REFERENCES `plan3dHeader` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `note` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(127) NULL,
  `text` TEXT NULL,
  PRIMARY KEY (`id`))
  ENGINE = InnoDB;

ALTER TABLE `object` ADD `image` MEDIUMTEXT NULL;
ALTER TABLE view ADD `image` MEDIUMTEXT NULL;
ALTER TABLE view ADD `configuration` TEXT NULL;
ALTER TABLE eqLogic ADD tags VARCHAR(255) NULL;
CREATE INDEX `tags` ON eqLogic (`tags` ASC);
UPDATE `update` SET source = 'github', configuration = '{"user":"NextDom","repository":"nextdom-core","version":"master"}' WHERE type = 'core';
DELETE FROM `update` WHERE name = 'AlternativeMarketForJeedom';
DELETE FROM `config` WHERE `plugin` = 'AlternativeMarketForJeedom';