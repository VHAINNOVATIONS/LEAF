START TRANSACTION;

ALTER TABLE `categories` ADD COLUMN `cloneGroupID` MEDIUMINT(9) NULL DEFAULT NULL AFTER `disabled`;

COMMIT;
