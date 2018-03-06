<?php


use Phinx\Seed\AbstractSeed;

/**
 * A base set of data to test against.
 */
class BaseTestSeed extends AbstractSeed
{
    public function run()
    {
        $this->execute("
            START TRANSACTION;
            SET FOREIGN_KEY_CHECKS = 0;

            INSERT INTO `action_history` 
            (`actionID`, `recordID`, `userID`, `stepID`, `dependencyID`, `actionType`, `actionTypeID`, `time`, `comment`) VALUES
            (1, 1, 'tester', 0, 5, 'submit', 6, 1520268930, '');

            INSERT INTO `categories` 
            (`categoryID`, `parentID`, `categoryName`, `categoryDescription`, `workflowID`, `sort`, `needToKnow`, `formLibraryID`, `visible`, `disabled`) VALUES
            ('form_f4687', '', 'Sample Form', 'A Simple Sample Form', 1, 0, 0, NULL, 1, 0);

            INSERT INTO `category_count` 
            (`recordID`, `categoryID`, `count`) VALUES
            (1, 'form_f4687', 1);

            INSERT INTO `data` 
            (`recordID`, `indicatorID`, `series`, `data`, `timestamp`, `userID`) VALUES
            (1, 2, 1, 'Bruce', 1520268869, 'tester'),
            (1, 3, 1, 'Wayne', 1520268875, 'tester'),
            (1, 4, 1, 'Vigilante Crime Fighter', 1520268925, 'tester'),
            (1, 5, 1, '<li>Fighting Crime</li><li>Wearing Capes</li><li>Ninja Stuff<br></li>', 1520268912, 'tester'),
            (1, 6, 1, '05/23/1934', 1520268896, 'tester');

            INSERT INTO `data_history` 
            (`recordID`, `indicatorID`, `series`, `data`, `timestamp`, `userID`) VALUES
            (1, 2, 1, 'Bruce', 1520268869, 'tester'),
            (1, 3, 1, 'Wayne', 1520268875, 'tester'),
            (1, 6, 1, '05/23/1934', 1520268896, 'tester'),
            (1, 5, 1, '<li>Fighting Crime</li><li>Wearing Capes</li><li>Ninja Stuff<br></li>', 1520268912, 'tester'),
            (1, 4, 1, 'Vigilante Crime Fighter', 1520268925, 'tester');

            INSERT INTO `indicators` 
            (`indicatorID`, `name`, `format`, `description`, `default`, `parentID`, `categoryID`, `html`, `htmlPrint`, `jsSort`, `required`, `sort`, `timeAdded`, `disabled`) VALUES
            (1, 'A Very Simple Form', '', '', '', NULL, 'form_f4687', NULL, NULL, NULL, 0, 1, '2018-03-05 16:52:15', 0),
            (2, 'First Name', 'text', 'First Name', '', NULL, 'form_f4687', NULL, NULL, NULL, 1, 1, '2018-03-05 16:52:40', 0),
            (3, 'Last Name', 'text', 'Last Name', '', NULL, 'form_f4687', NULL, NULL, NULL, 1, 1, '2018-03-05 16:52:54', 0),
            (4, 'Occupation', 'text', 'Occupation', '', NULL, 'form_f4687', NULL, NULL, NULL, 0, 1, '2018-03-05 16:53:06', 0),
            (5, 'Hobbies', 'textarea', 'Hobbies', '', NULL, 'form_f4687', NULL, NULL, NULL, 0, 1, '2018-03-05 16:53:30', 0),
            (6, 'Favorite Day', 'date', 'favorite day', '', NULL, 'form_f4687', NULL, NULL, NULL, 1, 1, '2018-03-05 16:53:52', 0);

            INSERT INTO `records` 
            (`recordID`, `date`, `serviceID`, `userID`, `title`, `priority`, `lastStatus`, `submitted`, `deleted`, `isWritableUser`, `isWritableGroup`) VALUES
            (1, 1520268853, 0, 'tester', 'My Request', 0, 'Submitted', 1520268930, 0, 0, 1);

            INSERT INTO `users` 
            (`userID`, `groupID`) VALUES
            ('tester', 1);

            INSERT INTO `workflows` 
            (`workflowID`, `initialStepID`, `description`) VALUES
            (1, 0, 'Sample Workflow');

            INSERT INTO `workflow_routes` (`workflowID`, `stepID`, `nextStepID`, `actionType`, `displayConditional`) VALUES
            (1, -1, 0, 'submit', '');

            SET FOREIGN_KEY_CHECKS = 1;
            COMMIT;
        ");
    }
}
