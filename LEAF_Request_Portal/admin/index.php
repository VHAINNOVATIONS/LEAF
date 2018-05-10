<?php
/************************
    Index for everything
    Date Created: September 11, 2007

*/

error_reporting(E_ALL & ~E_NOTICE);

if(false) {
    echo '<img src="../../libs/dynicons/?img=dialog-error.svg&amp;w=96" alt="error" style="float: left" /><div style="font: 36px verdana">Site currently undergoing maintenance, will be back shortly!</div>';
    exit();
}

include '../globals.php';
include '../../libs/smarty/Smarty.class.php';
include '../Login.php';
include '../db_mysql.php';
include '../db_config.php';
include '../form.php';

// Enforce HTTPS
include_once '../enforceHTTPS.php';

$db_config = new DB_Config();
$config = new Config();

header('X-UA-Compatible: IE=edge');

$db = new DB($db_config->dbHost, $db_config->dbUser, $db_config->dbPass, $db_config->dbName);
$db_phonebook = new DB($config->phonedbHost, $config->phonedbUser, $config->phonedbPass, $config->phonedbName);
unset($db_config);

$login = new Login($db_phonebook, $db);
$login->setBaseDir('../');

$login->loginUser();
if(!$login->isLogin() || !$login->isInDB()) {
    echo 'Your computer login is not recognized.';
    exit;
}
if(!$login->checkGroup(1)) {
    echo 'You must be in the administrator group to access this section.';
    exit();
}

$main = new Smarty;
$t_login = new Smarty;
$t_menu = new Smarty;
$o_login = '';
$o_menu = '';
$tabText = '';

$action = isset($_GET['a']) ? $_GET['a'] : '';

function customTemplate($tpl) {
	return file_exists("./templates/custom_override/{$tpl}") ? "custom_override/{$tpl}" : $tpl;
}

// HQ logo
if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')) { // issue with dijit tabcontainer and ie6
    $main->assign('status', 'You appear to be using Microsoft Internet Explorer version 6. Some portions of this website may not display correctly unless you use Internet Explorer version 10 or higher.');
}

$main->assign('logo', '<img src="../images/VA_icon_small.png" style="width: 80px" alt="VA logo" />');

$t_login->assign('name', $login->getName());

$main->assign('useDojo', true);
$main->assign('useDojoUI', true);

switch($action) {
    case 'mod_groups':
        $t_form = new Smarty;
        $t_form->left_delimiter = '<!--{';
        $t_form->right_delimiter= '}-->';

        $main->assign('javascripts', array('../' . Config::$orgchartPath . '/js/nationalEmployeeSelector.js',
        								   '../' . Config::$orgchartPath . '/js/groupSelector.js'
        ));

        $t_form->assign('orgchartPath', '../' . Config::$orgchartPath);
        $t_form->assign('CSRFToken', $_SESSION['CSRFToken']);
        $t_form->assign('orgchartImportTag', Config::$orgchartImportTags[0]);

        $main->assign('useUI', true);
        $main->assign('stylesheets', array('css/mod_groups.css', 
        								   '../' . Config::$orgchartPath . '/css/employeeSelector.css',
        								   '../' . Config::$orgchartPath . '/css/groupSelector.css'
        ));
        $main->assign('body', $t_form->fetch(customTemplate('mod_groups.tpl')));
        
        $tabText = 'User Access Groups';
        break;
    case 'mod_svcChief':
        $t_form = new Smarty;
        $t_form->left_delimiter = '<!--{';
        $t_form->right_delimiter= '}-->';

        $main->assign('useUI', true);

        $main->assign('javascripts', array('../' . Config::$orgchartPath . '/js/nationalEmployeeSelector.js'
        ));

        $t_form->assign('orgchartPath', '../' . Config::$orgchartPath);
        $t_form->assign('CSRFToken', $_SESSION['CSRFToken']);

        $main->assign('stylesheets', array('css/mod_groups.css',
        		'../' . Config::$orgchartPath . '/css/employeeSelector.css'
        ));
        $main->assign('body', $t_form->fetch(customTemplate('mod_svcChief.tpl')));

        $tabText = 'Service Chiefs';
        break;
    case 'workflow':
        $t_form = new Smarty;
        $t_form->left_delimiter = '<!--{';
        $t_form->right_delimiter= '}-->';

        $main->assign('useUI', true);

        $main->assign('javascripts', array(
			'../../libs/js/jsPlumb/dom.jsPlumb-min.js',
			'../' . Config::$orgchartPath . '/js/groupSelector.js',
			'../../libs/jsapi/portal/LEAFPortalAPI.js',
			'../../libs/js/LEAF/XSSHelpers.js'
        ));
        $main->assign('stylesheets', array('css/mod_workflow.css',
        			 					   '../' . Config::$orgchartPath . '/css/groupSelector.css'
        ));
        $t_form->assign('orgchartPath', '../' . Config::$orgchartPath);
        $t_form->assign('orgchartImportTags', Config::$orgchartImportTags);
        $t_form->assign('CSRFToken', $_SESSION['CSRFToken']);
        
        $main->assign('body', $t_form->fetch('mod_workflow.tpl'));
    
        $tabText = 'Workflow Editor';
        break;
    case 'form':
      	$t_form = new Smarty;
       	$t_form->left_delimiter = '<!--{';
       	$t_form->right_delimiter= '}-->';

        $main->assign('useUI', true);
        $main->assign('javascripts', array('../../libs/js/jquery/trumbowyg/plugins/colors/trumbowyg.colors.min.js',
        									'../../libs/js/filesaver/FileSaver.min.js',
        		                            '../../libs/js/codemirror/lib/codemirror.js',
							        		'../../libs/js/codemirror/mode/xml/xml.js',
							        		'../../libs/js/codemirror/mode/javascript/javascript.js',
							        		'../../libs/js/codemirror/mode/css/css.js',
							        		'../../libs/js/codemirror/mode/htmlmixed/htmlmixed.js',
											'../../libs/js/codemirror/addon/display/fullscreen.js',
											'../../libs/js/LEAF/XSSHelpers.js',
											'../../libs/jsapi/portal/LEAFPortalAPI.js'
        ));
        $main->assign('stylesheets', array('css/mod_form.css',
							        		'../../libs/js/jquery/trumbowyg/plugins/colors/ui/trumbowyg.colors.min.css',
							        		'../../libs/js/codemirror/lib/codemirror.css',
							        		'../../libs/js/codemirror/addon/display/fullscreen.css'
        ));
        
        $t_form->assign('CSRFToken', $_SESSION['CSRFToken']);
        $t_form->assign('APIroot', '../api/');
        $t_form->assign('referFormLibraryID', (int)$_GET['referFormLibraryID']);
        
        if(isset($_GET['form'])) {
        	$vars = array(':categoryID' => $_GET['form']);
        	$res = $db->prepared_query('SELECT * FROM categories WHERE categoryID=:categoryID', $vars);
        	if(count($res) > 0) {
        		$t_form->assign('form', $res[0]['categoryID']);
        	}
        }
        
        $main->assign('body', $t_form->fetch('mod_form.tpl'));
        
        $tabText = 'Form Editor';
        break;
    case 'mod_templates':
    case 'mod_templates_reports':
      	$t_form = new Smarty;
       	$t_form->left_delimiter = '<!--{';
       	$t_form->right_delimiter= '}-->';
       
       	$main->assign('useUI', true);
       	$main->assign('javascripts', array('../../libs/js/codemirror/lib/codemirror.js',
       									   '../../libs/js/codemirror/mode/xml/xml.js',
       									   '../../libs/js/codemirror/mode/javascript/javascript.js',
       			                           '../../libs/js/codemirror/mode/css/css.js',
       			                           '../../libs/js/codemirror/mode/htmlmixed/htmlmixed.js',
       									   '../../libs/js/codemirror/addon/search/search.js',
       			                           '../../libs/js/codemirror/addon/search/searchcursor.js',
       			                           '../../libs/js/codemirror/addon/dialog/dialog.js',
       			                           '../../libs/js/codemirror/addon/scroll/annotatescrollbar.js',
       			                           '../../libs/js/codemirror/addon/search/matchesonscrollbar.js',
       			                           '../../libs/js/codemirror/addon/display/fullscreen.js'
       	));
       	$main->assign('stylesheets', array('../../libs/js/codemirror/lib/codemirror.css',
       				                       '../../libs/js/codemirror/addon/dialog/dialog.css',
       			                           '../../libs/js/codemirror/addon/scroll/simplescrollbars.css',
       									   '../../libs/js/codemirror/addon/search/matchesonscrollbar.css',
       			                           '../../libs/js/codemirror/addon/display/fullscreen.css'
       	));
       
       	$t_form->assign('CSRFToken', $_SESSION['CSRFToken']);
       	$t_form->assign('APIroot', '../api/');
       
       	switch($action) {
       		case 'mod_templates':
       			$main->assign('body', $t_form->fetch('mod_templates.tpl'));
       			$tabText = 'Template Editor';
       			break;
       		case 'mod_templates_reports':
       			$main->assign('body', $t_form->fetch('mod_templates_reports.tpl'));
       			$tabText = 'Editor';
       			break;
       		default:
       			break;
       	}

        break;
    case 'admin_update_database':
        $t_form = new Smarty;
        $t_form->left_delimiter = '<!--{';
        $t_form->right_delimiter= '}-->';
    
        if($login->checkGroup(1)) {
            $main->assign('body', $t_form->fetch('admin_update_database.tpl'));
        }
        else {
            $main->assign('body', 'You require System Administrator level access to view this section.');
        }
    
        $tabText = 'System Administration';
        break;
    case 'admin_sync_services':
        $t_form = new Smarty;
        $t_form->left_delimiter = '<!--{';
        $t_form->right_delimiter= '}-->';
    
        if($login->checkGroup(1)) {
            $main->assign('body', $t_form->fetch('admin_sync_services.tpl'));
        }
        else {
            $main->assign('body', 'You require System Administrator level access to view this section.');
        }
    
        $tabText = 'System Administration';
        break;
    case 'formLibrary':
      	$t_form = new Smarty;
       	$t_form->left_delimiter = '<!--{';
       	$t_form->right_delimiter= '}-->';

       	$main->assign('useUI', true);

       	if($login->checkGroup(1)) {
       		$t_form->assign('LEAF_NEXUS_URL', LEAF_NEXUS_URL);

       		$main->assign('body', $t_form->fetch('view_form_library.tpl'));
       	}
       	else {
       		$main->assign('body', 'You require System Administrator level access to view this section.');
       	}
       	 
       	$tabText = 'Form Library';
       	break;
    case 'importForm':
    	$t_form = new Smarty;
    	$t_form->left_delimiter = '<!--{';
    	$t_form->right_delimiter= '}-->';
    	
    	if($login->checkGroup(1)) {
    		$main->assign('body', $t_form->fetch('admin_import_form.tpl'));
    	}
    	else {
    		$main->assign('body', 'You require System Administrator level access to view this section.');
    	}
    	
    	$tabText = 'Import Form';
    	break;
    case 'uploadFile':
    	$t_form = new Smarty;
    	$t_form->left_delimiter = '<!--{';
    	$t_form->right_delimiter= '}-->';

    	if($login->checkGroup(1)) {
    		$main->assign('body', $t_form->fetch('admin_upload_file.tpl'));
    	}
    	else {
    		$main->assign('body', 'You require System Administrator level access to view this section.');
    	}

   		$tabText = 'Upload File';
   		break;
    case 'mod_system':
    	$t_form = new Smarty;
    	$t_form->left_delimiter = '<!--{';
    	$t_form->right_delimiter= '}-->';

    	$main->assign('useUI', true);
//   		$t_form->assign('orgchartPath', '../' . Config::$orgchartPath);
   		$t_form->assign('CSRFToken', $_SESSION['CSRFToken']);
        $main->assign('javascripts', array( '../../libs/js/LEAF/XSSHelpers.js'));

   		$settings = $db->query_kv('SELECT * FROM settings', 'setting', 'data');
   		$t_form->assign('heading', $settings['heading'] == '' ? $config->title : $settings['heading']);
   		$t_form->assign('subheading', $settings['subheading'] == '' ? $config->city : $settings['subheading']);
   		$t_form->assign('requestLabel', $settings['requestLabel'] == '' ? 'Request' : $settings['requestLabel']);
   		$t_form->assign('timeZone', $settings['timeZone'] == '' ? 'America/New_York' : $settings['timeZone']);
   		
   		$t_form->assign('timeZones', DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, 'US'));
   		
   		$t_form->assign('importTags', $config::$orgchartImportTags);
//   		$main->assign('stylesheets', array('css/mod_groups.css'));
   		$main->assign('body', $t_form->fetch(customTemplate('mod_system.tpl')));

   		$tabText = 'Site Settings';
   		break;
   	case 'mod_file_manager':
   			$t_form = new Smarty;
   			$t_form->left_delimiter = '<!--{';
   			$t_form->right_delimiter= '}-->';
   		
   			$main->assign('useUI', true);
   			//   		$t_form->assign('orgchartPath', '../' . Config::$orgchartPath);
   			$t_form->assign('CSRFToken', $_SESSION['CSRFToken']);
   		
   			$settings = $db->query_kv('SELECT * FROM settings', 'setting', 'data');
   			$t_form->assign('heading', $settings['heading'] == '' ? $config->title : $settings['heading']);
   			$t_form->assign('subheading', $settings['subheading'] == '' ? $config->city : $settings['subheading']);
   			$t_form->assign('requestLabel', $settings['requestLabel'] == '' ? 'Request' : $settings['requestLabel']);
   			$t_form->assign('importTags', $config::$orgchartImportTags);
   			//   		$main->assign('stylesheets', array('css/mod_groups.css'));
   			$main->assign('body', $t_form->fetch(customTemplate('mod_file_manager.tpl')));
   		
   			$tabText = 'File Manager';
   			break;
   	case 'disabled_fields':
   		$t_form = new Smarty;
   		$t_form->left_delimiter = '<!--{';
   		$t_form->right_delimiter= '}-->';
   		 
   		$main->assign('useUI', true);
   		$t_form->assign('CSRFToken', $_SESSION['CSRFToken']);
   		 
   		$main->assign('body', $t_form->fetch(customTemplate('view_disabled_fields.tpl')));
   		 
   		$tabText = 'Recover disabled fields';
   		break;
    default:
//        $main->assign('useDojo', false);
        if($login->isLogin()) {
            $o_login = $t_login->fetch('login.tpl');

            $t_form = new Smarty;
            $t_form->left_delimiter = '<!--{';
            $t_form->right_delimiter= '}-->';
            $t_form->assign('orgchartPath', Config::$orgchartPath);
            $t_form->assign('CSRFToken', $_SESSION['CSRFToken']);

            $main->assign('javascripts', array('../../libs/js/jquery/jquery.min.js',
                                           '../../libs/js/jquery/jquery-ui.custom.min.js',
                                           '../../libs/js/jsPlumb/dom.jsPlumb-min.js'));

            $main->assign('body', $t_form->fetch(customTemplate('view_admin_menu.tpl')));

            if($action != 'menu' && $action != '') {
                $main->assign('status', 'The page you are looking for does not exist or may have been moved. Please update your bookmarks.');
            }
        }
        else {
            $t_login->assign('name', '');
            $main->assign('status', 'Your login session has expired, You must log in again.');
        }
        $o_login = $t_login->fetch('login.tpl');
        break;
}

$main->assign('login', $t_login->fetch('login.tpl'));
$t_menu->assign('action', $action);
$t_menu->assign('orgchartPath', Config::$orgchartPath);
$o_menu = $t_menu->fetch('menu.tpl');
$main->assign('menu', $o_menu);
$tabText = $tabText == '' ? '' : $tabText . '&nbsp;';
$main->assign('tabText', $tabText);

$settings = $db->query_kv('SELECT * FROM settings', 'setting', 'data');
$main->assign('title', $settings['heading'] == '' ? $config->title : $settings['heading']);
$main->assign('city', $settings['subheading'] == '' ? $config->city : $settings['subheading']);
$main->assign('revision', $settings['version']);

if(!isset($_GET['iframe'])) {
	$main->display(customTemplate('main.tpl'));
}
else {
	$main->display(customTemplate('main_iframe.tpl'));
}
