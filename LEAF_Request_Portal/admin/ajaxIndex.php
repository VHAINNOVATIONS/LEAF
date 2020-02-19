<?php
/*
 * As a work of the United States government, this project is in the public domain within the United States.
 */

/*
    Index for legacy ajax endpoints
    Date Created: September 11, 2007

*/

/* TODO:
1. prevent double submits
2. clean up
*/
error_reporting(E_ALL & ~E_NOTICE);

include '../globals.php';
include '../../libs/smarty/Smarty.class.php';
include '../Login.php';
include '../db_mysql.php';
include '../db_config.php';

if (!class_exists('XSSHelpers'))
{
    include_once dirname(__FILE__) . '/../../libs/php-commons/XSSHelpers.php';
}

$db_config = new DB_Config();
$config = new Config();

$db = new DB($db_config->dbHost, $db_config->dbUser, $db_config->dbPass, $db_config->dbName);
$db_phonebook = new DB($config->phonedbHost, $config->phonedbUser, $config->phonedbPass, $config->phonedbName);
unset($db_config);

$settings = $db->query_kv('SELECT * FROM settings', 'setting', 'data');
if (isset($settings['timeZone']))
{
    date_default_timezone_set($settings['timeZone']);
}

$login = new Login($db_phonebook, $db);
$login->setBaseDir('../');

$login->loginUser();
if (!$login->checkGroup(1))
{
    echo 'You must be in the administrator group to access this section.';
    exit();
}

function checkToken()
{
    if ($_POST['CSRFToken'] != $_SESSION['CSRFToken'])
    {
        echo 'Invalid Token.';
        exit();
    }
}

$action = isset($_GET['a']) ? $_GET['a'] : '';

switch ($action) {
    case 'add_user_old':
        checkToken();
        require 'Group.php';

        $group = new Group($db, $login);
        $group->addMember($_POST['userID'], $_POST['groups']);

        break;
    case 'remove_user_old':
        checkToken();
        require 'Group.php';

        $deleteList = XSSHelpers::scrubObjectOrArray(json_decode($_POST['json'], true));

        $group = new Group($db, $login);
        foreach ($deleteList as $del)
        {
            $group->removeMember(XSSHelpers::xscrub($del['userID']), $del['groupID']);
        }

        break;
    case 'add_user':
          checkToken();
           require 'Group.php';

           $group = new Group($db, $login);
           $group->addMember($_POST['userID'], $_POST['groupID']);

           break;
    case 'remove_user':
           checkToken();
           require 'Group.php';

           $group = new Group($db, $login);
           $group->removeMember($_POST['userID'], $_POST['groupID']);

           break;
    case 'printview':
        if ($login->isLogin())
        {
            require '../form.php';
            $form = new Form($db, $login);

            $t_form = new Smarty;
            $t_form->left_delimiter = '<!--{';
            $t_form->right_delimiter = '}-->';
            $t_form->assign('recordID', (int)$_GET['recordID']);
            $t_form->assign('orgchartPath', Config::$orgchartPath);

            $t_form->assign('form', $form->getFormByCategory($_GET['categoryID']));
            $t_form->display('print_form_ajax.tpl');
            $tabText = 'Form Editor';
        }

        break;
    case 'importForm':
        require '../sources/FormStack.php';
        $formStack = new FormStack($db, $login);
        $result = $formStack->importForm();

        echo $result;

        break;
    case 'manualImportForm':
           require '../sources/FormStack.php';
           $formStack = new FormStack($db, $login);
           $result = $formStack->importForm();

        if ($result === true)
        {   session_write_close();
            header('Location: ./?a=form');
            exit();
        }
        else
        {
            echo $result;
        }

           break;
    case 'uploadFile':
           require '../sources/System.php';
           $system = new System($db, $login);
           $result = $system->newFile();
           if ($result === true)
           {   session_write_close();
               header('Location: ./?a=mod_file_manager');
               exit();
           }
           else
           {
               echo $result;
           }

           break;
    case 'gethistory':
        $typeName = $_GET['type'];
        $itemID = $_GET['id']; 

        $type = null;
        switch ($typeName) {
            case 'service':
                include '../sources/Service.php';
                $type = new \Service($db, $login);
                $title = $type->getServiceName($itemID);
                break;
            case 'form':
                include '../sources/FormEditor.php';
                $type = new \FormEditor($db, $login);
                $title = $type->getFormName($itemID);
                break;
            case 'group':
                include 'Group.php';
                $type = new \Group($db, $login);
                $title = $type->getGroupName($itemID);
                break;
        }

        if (!empty($itemID))
        {
            $t_form = new Smarty;
            $t_form->left_delimiter = '<!--{';
            $t_form->right_delimiter = '}-->';

            $resHistory = $type->getHistory($itemID);

            $t_form->assign('dataType', ucwords($typeName));
            $t_form->assign('dataID', $itemID);
            $t_form->assign('dataName', $title);

            $resHistory = $resHistory ?? array();

            $t_form->assign('history', $resHistory);

            $t_form->display('view_history.tpl');
        }

        break;           
    default:
        /*
        echo "Action: $action<br /><br />Catchall...<br /><br />POST: <pre>";
        print_r($_POST);
        echo "</pre><br /><br />GET:<pre>";
        print_r($_GET);
        echo "</pre><br /><br />FILES:<pre>";
        print_r($_FILES);
        echo "</pre>";
        */
        break;
}
