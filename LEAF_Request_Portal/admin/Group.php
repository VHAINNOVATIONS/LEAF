<?php
/*
 * As a work of the United States government, this project is in the public domain within the United States.
 */

/*
    Group
    Date Created: April 29, 2010

    Handler for user groups for the resource management web app
*/

if(!class_exists('DataActionLogger'))
{
    require_once dirname(__FILE__) . '/../../libs/logger/dataActionLogger.php';
}

class Group
{
    private $db;

    private $login;

    private $dataActionLogger;

    public function __construct($db, $login)
    {
        $this->db = $db;
        $this->login = $login;
        $this->dataActionLogger = new \DataActionLogger($db, $login);
    }

    public function importGroup($groupName, $groupDesc = '', $parentGroupID = null)
    {
        // Log group imports
        $this->dataActionLogger->logAction(\DataActions::IMPORT, \LoggableTypes::PORTAL_GROUP, [
            new \LogItem("users", "groupID", $groupName, $groupName)
        ]);
    }

    public function addGroup($groupName, $groupDesc = '', $parentGroupID = null)
    {
        /*$vars = array(':groupName' => $groupName,
                      ':groupDesc' => $groupDesc,
                      ':parentGroupID' => $parentGroupID, );
        $res = $this->db->prepared_query('INSERT INTO groups (name, groupDescription, parentGroupID)
                                            VALUES (:groupName, :groupDesc, :parentGroupID)', $vars);*/

        // Log group creates
        $this->dataActionLogger->logAction(\DataActions::ADD, \LoggableTypes::PORTAL_GROUP, [
            new \LogItem("groups", "name", $groupName, $groupName)
        ]);


    }

    public function removeGroup($groupID)
    {
        if ($groupID != 1)
        {
            $vars = array(':groupID' => $groupID);
            $res = $this->db->prepared_query('SELECT * FROM `groups` WHERE groupID=:groupID', $vars);

            if (isset($res[0])
                && $res[0]['parentGroupID'] == null)
            {
                // Log group deletes
                $this->dataActionLogger->logAction(\DataActions::DELETE, \LoggableTypes::PORTAL_GROUP, [
                    new \LogItem("users", "groupID", $groupID, $this->getGroupName($groupID))
                ]);

                $this->db->prepared_query('DELETE FROM users WHERE groupID=:groupID', $vars);
                $this->db->prepared_query('DELETE FROM `groups` WHERE groupID=:groupID', $vars);

                return 1;
            }
        }

        return 'Cannot remove group.';
    }

    // return array of userIDs
    public function getMembers($groupID)
    {
        if (!is_numeric($groupID))
        {
            return;
        }
        $vars = array(':groupID' => $groupID);
        $res = $this->db->prepared_query('SELECT * FROM users WHERE groupID=:groupID ORDER BY userID', $vars);

        $members = array();
        if (count($res) > 0)
        {
            require_once '../VAMC_Directory.php';
            $dir = new VAMC_Directory();
            foreach ($res as $member)
            {
                $dirRes = $dir->lookupLogin($member['userID']);

                if (isset($dirRes[0]))
                {
                    if($groupID == 1)
                    {
                      $dirRes[0]['primary_admin'] = $member['primary_admin'];  
                    }
                    if($member['locallyManaged'] == 1) {
                        $dirRes[0]['backupID'] = null;
                    } else {
                        $dirRes[0]['backupID'] = $member['backupID'];
                    }
                    $dirRes[0]['locallyManaged'] = $member['locallyManaged'];
                    $dirRes[0]['active'] = $member['active'];
                    
                    $members[] = $dirRes[0];
                }
            }
        }

        return $members;
    }

    public function addMember($member, $groupID)
    {
        include_once __DIR__ . '/../' . Config::$orgchartPath . '/sources/Employee.php';

        $config = new Config();
        $db_phonebook = new DB($config->phonedbHost, $config->phonedbUser, $config->phonedbPass, $config->phonedbName);
        $employee = new Orgchart\Employee($db_phonebook, $this->login);

        if (is_numeric($groupID)) {
            $vars = array(':userID' => $member,
                ':groupID' => $groupID,);

            // Update on duplicate keys
            $res = $this->db->prepared_query('INSERT INTO users (userID, groupID, backupID, locallyManaged, active)
                                                    VALUES (:userID, :groupID, null, 1, 1)
                                                    ON DUPLICATE KEY UPDATE userID=:userID, groupID=:groupID, backupID=null, locallyManaged=1, active=1', $vars);

            $this->dataActionLogger->logAction(\DataActions::ADD, \LoggableTypes::EMPLOYEE, [
                new \LogItem("users", "userID", $member, $this->getEmployeeDisplay($member)),
                new \LogItem("users", "groupID", $groupID, $this->getGroupName($groupID))
            ]);

            // include the backups of employees
            $emp = $employee->lookupLogin($member);
            $backups = $employee->getBackups($emp[0]['empUID']);
            foreach ($backups as $backup) {
                $vars = array(':userID' => $backup['userName'],
                    ':groupID' => $groupID,
                    ':backupID' => $emp[0]['userName'],);

                $res = $this->db->prepared_query('SELECT * FROM users WHERE userID=:userID AND groupID=:groupID', $vars);

                // Check for locallyManaged users
                if ($res[0]['locallyManaged'] == 1) {
                    // Add backupID check for updates
                    $this->db->prepared_query('INSERT INTO users (userID, groupID, backupID)
                                                    VALUES (:userID, :groupID, :backupID)
                                                    ON DUPLICATE KEY UPDATE userID=:userID, groupID=:groupID, backupID=null', $vars);
                } else {
                    // Add backupID check for updates
                    $this->db->prepared_query('INSERT INTO users (userID, groupID, backupID)
                                                    VALUES (:userID, :groupID, :backupID)
                                                    ON DUPLICATE KEY UPDATE userID=:userID, groupID=:groupID, backupID=:backupID', $vars);
                }
            }
        }
    }

    public function removeMember($member, $groupID)
    {
        include_once __DIR__ . '/../' . Config::$orgchartPath . '/sources/Employee.php';

        $config = new Config();
        $db_phonebook = new DB($config->phonedbHost, $config->phonedbUser, $config->phonedbPass, $config->phonedbName);
        $employee = new Orgchart\Employee($db_phonebook, $this->login);

        if (is_numeric($groupID) && $member != '')
        {
            $vars = array(':userID' => $member,
                          ':groupID' => $groupID, );

            $this->dataActionLogger->logAction(\DataActions::DELETE, \LoggableTypes::EMPLOYEE, [
                new \LogItem("users", "userID", $member, $this->getEmployeeDisplay($member)),
                new \LogItem("users", "groupID", $groupID, $this->getGroupName($groupID))
            ]);

            $this->db->prepared_query('DELETE FROM users WHERE userID=:userID AND groupID=:groupID', $vars);

            // include the backups of employee
            $emp = $employee->lookupLogin($member);
            $backups = $employee->getBackups($emp[0]['empUID']);
            foreach ($backups as $backup) {
                $vars = array(':userID' => $backup['userName'],
                    ':groupID' => $groupID,
                    ':backupID' => $member,);

                $res = $this->db->prepared_query('SELECT * FROM users WHERE userID=:userID AND groupID=:groupID AND backupID=:backupID', $vars);

                // Check for locallyManaged users
                if ($res[0]['locallyManaged'] == 0) {
                    $this->db->prepared_query('DELETE FROM users WHERE userID=:userID AND groupID=:groupID AND backupID=:backupID', $vars);
                }
            }
        }
    }

    // exclude: 0 (no group), 24, (everyone), 16 (service chief)
    public function getGroups()
    {
        $res = $this->db->prepared_query('SELECT * FROM `groups` WHERE groupID != 0 ORDER BY name ASC', array());

        return $res;
    }

    public function getGroupsAndMembers()
    {
        $groups = $this->getGroups();

        $list = array();
        foreach ($groups as $group)
        {
            if ($group['groupID'] > 0)
            {
                $group['members'] = $this->getMembers($group['groupID']);
                $list[] = $group;
            }
        }

        return $list;
    }

    /**
     * Returns formatted group name.
     * @param string $groupID       The group id to find the formatted name of
     * @return string 
     */
    public function getGroupName($groupId)
    {
        $vars = array(":groupID" => $groupId);
        $res = $this->db->prepared_query('SELECT * FROM `groups` WHERE groupID = :groupID', $vars);
        if($res[0] != null){
            return $res[0]["name"];
        }
        return "";
    }
    
    /**
     * Returns formatted Employee name.
     * @param string $employeeID        The id to create the display name of.
     * @return string 
     */
    private function getEmployeeDisplay($employeeID)
    {
        require_once '../VAMC_Directory.php';
     
        $dir = new VAMC_Directory();
        $dirRes = $dir->lookupLogin($employeeID);

        $empData = $dirRes[0];
        $empDisplay =$empData["firstName"]." ".$empData["lastName"];
        
        return $empDisplay;
    }

    /**
     * Returns Portal Group logs.
     * 
     * @param string $filterById        The id of the Group to find the logs of
     *
     * @return array 
     */
    public function getHistory($filterById)
    {
        return $this->dataActionLogger->getHistory($filterById, "groupID", \LoggableTypes::PORTAL_GROUP);
    }

    /**
     * Returns all history ids for all groups
     * 
     * @return array all history ids for all groups
     */
    public function getAllHistoryIDs()
    {
        return $this->dataActionLogger->getAllHistoryIDs("groupID", \LoggableTypes::PORTAL_GROUP);
    }
}
