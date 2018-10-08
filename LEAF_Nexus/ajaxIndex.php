<?php
/*
 * As a work of the United States government, this project is in the public domain within the United States.
 */

/*
    Index for everything
    Date: September 11, 2007

*/

error_reporting(E_ALL & ~E_NOTICE);

include '../libs/smarty/Smarty.class.php';
include './sources/Login.php';
include 'db_mysql.php';
include 'config.php';
include './sources/Exception.php';

// Enforce HTTPS
include_once './enforceHTTPS.php';

$config = new Orgchart\Config();

$db = new DB($config->dbHost, $config->dbUser, $config->dbPass, $config->dbName);

$login = new Orgchart\Login($db, $db);

$login->loginUser();
if ($login)
{
}

$type = null;
switch ($_GET['categoryID']) {
    case 1:    // employee
        include './sources/Employee.php';
        $type = new OrgChart\Employee($db, $login);

        break;
    case 2:    // position
        include './sources/Position.php';
        $type = new OrgChart\Position($db, $login);

        break;
    case 3:    // group
        include './sources/Group.php';
        $type = new OrgChart\Group($db, $login);

        break;
    default:
        return false;

        break;
}

$action = isset($_GET['a']) ? $_GET['a'] : '';

switch ($action) {
    case 'doupload': // handle file upload
        $uploadOk = true;
        $uploadedFilename = '';
        foreach ($_FILES as $file)
        {
            if ($file['error'] != UPLOAD_ERR_OK)
            {
                $uploadOk = false;
            }
            $uploadedFilename = $file['name'];
        }

        // wrap output in html for dojo
        echo '<html><body><textarea>';

        if ($uploadOk)
        {
            try
            {
                if ($type->modify($_GET['UID']))
                {
                    echo "{$uploadedFilename} has been attached!";
                }
                else
                {
                    echo 'File extension may not be supported.';
                }
            }
            catch (Exception $e)
            {
                echo $e->getMessage();
            }
        }
        else
        {
            $errorCode = '';
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $errorCode = 'The uploaded file exceeds the maximum server filesize limit.';

                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $errorCode = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';

                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errorCode = 'The uploaded file was only partially uploaded, please try again.';

                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errorCode = 'No file was selected to be attached.';

                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errorCode = 'Missing a temporary folder';

                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errorCode = 'Failed to write file to disk';

                    break;
                case UPLOAD_ERR_EXTENSION:
                    $errorCode = 'File upload stopped by extension';

                    break;
                default:
                    $errorCode = 'Unknown upload error';

                    break;
            }
        }
        // wrap output in html for dojo
        echo '</textarea></body></html>';

        break;
    case 'deleteattachment':
        echo $type->deleteAttachment($_POST['categoryID'], $_POST['UID'], $_POST['indicatorID'], $_POST['file']);

        break;
        case 'keepAlive':
              //session can be extending by http request
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
