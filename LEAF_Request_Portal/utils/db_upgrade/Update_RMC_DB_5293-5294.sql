START TRANSACTION;

ALTER TABLE `indicators` ADD `is_sensitive` TINYINT NOT NULL DEFAULT '0';

UPDATE `settings` SET `data` = '5294' WHERE `settings`.`setting` = 'dbversion';
COMMIT;
