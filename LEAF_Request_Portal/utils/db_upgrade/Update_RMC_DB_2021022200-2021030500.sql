START TRANSACTION;

ALTER TABLE `email_templates`
    ADD COLUMN `emailTo` text NULL AFTER `emailTemplateID`,
    ADD COLUMN `emailCc` text NULL AFTER `emailTo`;

UPDATE `email_templates` SET `emailTo` = 'LEAF_notify_complete_email_to.tpl', `emailCc` = 'LEAF_notify_complete_email_cc.tpl' WHERE `emailTemplateID` = -3;
UPDATE `email_templates` SET `emailTo` = 'LEAF_notify_next_email_to.tpl', `emailCc` = 'LEAF_notify_next_email_cc.tpl' WHERE `emailTemplateID` = -2;
UPDATE `email_templates` SET `emailTo` = 'LEAF_send_back_email_to.tpl',	`emailCc` = 'LEAF_send_back_email_cc.tpl' WHERE `emailTemplateID` = -1;

UPDATE `settings` SET `data` = '2021030500' WHERE `settings`.`setting` = 'dbversion';

COMMIT;

/**** Revert ****

START TRANSACTION;

ALTER TABLE `email_templates`
  DROP COLUMN `emailTo`,
  DROP COLUMN `emailCC`;

COMMIT;

*/