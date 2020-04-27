CREATE TABLE IF NOT EXISTS `note` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(127) NULL,
  `text` TEXT NULL,
  PRIMARY KEY (`id`))
  ENGINE = InnoDB;

ALTER TABLE `scenario` ADD `order`         INT          NULL;
ALTER TABLE `object`   ADD `image`         MEDIUMTEXT   NULL;
ALTER TABLE `view`     ADD `image`         MEDIUMTEXT   NULL;
ALTER TABLE `view`     ADD `configuration` TEXT         NULL;
ALTER TABLE `eqLogic`  ADD `tags`          VARCHAR(255) NULL;

ALTER TABLE `cmd` add `html` mediumtext COLLATE utf8_unicode_ci;

CREATE INDEX `tags` ON eqLogic (`tags` ASC);

UPDATE `update` SET source = 'github', configuration = '{"user":"NextDom","repository":"nextdom-core","version":"master"}' WHERE type = 'core';

INSERT INTO `message` (`date`, `logicalId`, `plugin`, `message`, `action`) SELECT now(), 'AlternativeMarketForJeedom:callRemove', `name`, 'Nous vous proposons de supprimer ce plugin car il est intégré dans nextdom.', '' FROM `update` WHERE `name` = 'AlternativeMarketForJeedom';
INSERT INTO `message` (`date`, `logicalId`, `plugin`, `message`, `action`) SELECT now(), 'musiccast:callRemove', `name`, 'Nous vous proposons de supprimer ce plugin car une autre version existe dans le market nextdom .', '' FROM `update` WHERE `name` = 'musiccast';

UPDATE `config` SET `value` = 'https://www.jeedom.com/market/' WHERE `plugin` = 'core' AND `key` = 'market::address';
