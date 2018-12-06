<?php
/*
 * As a work of the United States government, this project is in the public domain within the United States.
 */

/*
    Workflow
    Date Created: December 12, 2011

*/

class Workflow
{
    public $siteRoot = '';

    private $db;

    private $login;

    private $workflowID;

    private $eventFolder = './scripts/events/';

    public function __construct($db, $login, $workflowID = 0)
    {
        $this->db = $db;
        $this->login = $login;
        $this->setWorkflowID($workflowID);

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        $this->siteRoot = "{$protocol}://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['REQUEST_URI']) . '/';
    }

    public function setWorkflowID($workflowID)
    {
        $this->workflowID = is_numeric($workflowID) ? $workflowID : 0;
    }

    public function getSteps()
    {
        $vars = array(':workflowID' => $this->workflowID);
        $res = $this->db->prepared_query('SELECT * FROM workflow_steps
                                            LEFT JOIN workflows USING (workflowID)
                                            LEFT JOIN step_modules USING (stepID)
        									WHERE workflowID=:workflowID', $vars);

        $out = [];
        foreach($res as $item) {
            $out[$item['stepID']] = $item;
            unset($out[$item['stepID']]['moduleName']);
            unset($out[$item['stepID']]['moduleConfig']);
            if($item['moduleName'] != '') {
                $out[$item['stepID']]['stepModules'][] = array('moduleName' => $item['moduleName'],
                                                               'moduleConfig' => $item['moduleConfig']);
            }
        }

        return $out;
    }

    public function deleteStep($stepID)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        $vars = array(':stepID' => $stepID);
        $res = $this->db->prepared_query('SELECT * FROM records_workflow_state
    										WHERE stepID = :stepID', $vars);
        if (count($res) > 0)
        {
            return 'Requests currently on this step need to be moved first.';
        }

        $res = $this->db->prepared_query('DELETE FROM step_dependencies
    										WHERE stepID = :stepID', $vars);
        $res = $this->db->prepared_query('DELETE FROM route_events
    										WHERE stepID = :stepID', $vars);
        $res = $this->db->prepared_query('DELETE FROM workflow_routes
    										WHERE stepID = :stepID', $vars);
        $res = $this->db->prepared_query('DELETE FROM workflow_routes
    										WHERE nextStepID = :stepID', $vars);
        $res = $this->db->prepared_query('DELETE FROM workflow_steps
    										WHERE stepID = :stepID', $vars);

        return 1;
    }

    public function getAllSteps()
    {
        $vars = array();
        $res = $this->db->prepared_query('SELECT * FROM workflow_steps
    										LEFT JOIN workflows USING (workflowID)
    										ORDER BY description, stepTitle', $vars);

        return $res;
    }

    public function getRoutes()
    {
        $vars = array(':workflowID' => $this->workflowID);
        $res = $this->db->prepared_query('SELECT * FROM workflow_routes
        										LEFT JOIN actions USING (actionType)
            									WHERE workflowID=:workflowID', $vars);

        return $res;
    }

    public function getAllUniqueWorkflows()
    {
        $vars = array();
        $res = $this->db->prepared_query('SELECT * FROM workflows ORDER BY description ASC', $vars);

        return $res;
    }

    public function getCategories()
    {
        $vars = array(':workflowID' => $this->workflowID);
        $res = $this->db->prepared_query("SELECT * FROM categories
                                                WHERE workflowID = :workflowID
        										ORDER BY categoryName", $vars);

        return $res;
    }

    public function getAllCategories()
    {
        $res = $this->db->prepared_query("SELECT * FROM categories
                                                WHERE workflowID>0 AND parentID=''
        											AND disabled = 0
        										ORDER BY categoryName", null);

        return $res;
    }

    public function getAllCategoriesUnabridged()
    {
        $res = $this->db->prepared_query("SELECT * FROM categories
                                                WHERE parentID=''
    												AND disabled = 0
    											ORDER BY categoryName", null);

        return $res;
    }

    public function getAllDependencies()
    {
        $vars = array();
        $res = $this->db->prepared_query('SELECT * FROM dependencies ORDER BY description', $vars);

        return $res;
    }

    public function getDependencies($stepID)
    {
        $vars = array(':stepID' => $stepID);
        $res = $this->db->prepared_query('SELECT * FROM step_dependencies
                                            LEFT JOIN dependencies USING (dependencyID)
                                            LEFT JOIN dependency_privs USING (dependencyID)
                                            LEFT JOIN groups USING (groupID)
        									LEFT JOIN workflow_steps USING (stepID)
                                            WHERE stepID = :stepID', $vars);

        return $res;
    }

    public function getEvents($stepID, $action)
    {
        $vars = array(':workflowID' => $this->workflowID,
                      ':stepID' => $stepID,
                      ':action' => $action, );

        $res = $this->db->prepared_query('SELECT * FROM route_events
                LEFT JOIN events USING (eventID)
                WHERE workflowID = :workflowID
                    AND stepID = :stepID
                    AND actionType = :action', $vars);

        return $res;
    }

    public function setEditorPosition($stepID, $x, $y)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }
        $vars = array(':workflowID' => $this->workflowID,
                      ':stepID' => $stepID,
                      ':x' => $x,
                      ':y' => $y, );
        $res = $this->db->prepared_query('UPDATE workflow_steps
                                            SET posX=:x, posY=:y
        									WHERE workflowID=:workflowID
    											AND stepID=:stepID', $vars);

        return true;
    }

    public function deleteAction($stepID, $nextStepID, $action)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }
        // clear out route events
        $vars = array(':workflowID' => $this->workflowID,
                      ':stepID' => $stepID,
                      ':action' => $action, );
        $res = $this->db->prepared_query('DELETE FROM route_events
    										WHERE workflowID=:workflowID
    											AND stepID=:stepID
    											AND actionType=:action', $vars);

        // clear out routes
        $vars = array(':workflowID' => $this->workflowID,
                ':stepID' => $stepID,
                ':nextStepID' => $nextStepID,
                ':action' => $action, );
        $res = $this->db->prepared_query('DELETE FROM workflow_routes
    										WHERE workflowID=:workflowID
    											AND stepID=:stepID
    											AND nextStepID=:nextStepID
    											AND actionType=:action', $vars);

        return true;
    }

    public function createAction($stepID, $nextStepID, $action)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        $vars = array(':workflowID' => $this->workflowID,
                      ':stepID' => $stepID,
                      ':nextStepID' => $nextStepID,
                      ':action' => $action,
                      ':displayConditional' => '',
        );
        $res = $this->db->prepared_query('INSERT INTO workflow_routes (workflowID, stepID, nextStepID, actionType, displayConditional)
    										VALUES (:workflowID, :stepID, :nextStepID, :action, :displayConditional)', $vars);

        return true;
    }

    public function getAllEvents()
    {
        $vars = array();
        $res = $this->db->prepared_query('SELECT * FROM events', $vars);

        return $res;
    }

    public function getActions()
    {
        $vars = array();
        $res = $this->db->prepared_query('SELECT * FROM actions ORDER BY actionText', $vars);

        return $res;
    }

    public function setInitialStep($stepID)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        if ($stepID < 0)
        {
            return 'Invalid Action';
        }

        $vars = array(':workflowID' => $this->workflowID,
                      ':stepID' => $stepID,
        );
        $res = $this->db->prepared_query('UPDATE workflows SET initialStepID=:stepID
    										WHERE workflowID=:workflowID', $vars);

        if ($stepID != 0)
        {
            $this->deleteAction(-1, 0, 'submit');
        }

        return true;
    }

    /**
     * @param string $stepTitle
     * @param string $bgColor
     * @param string $fontColor
     * @return int The newly created stepID
     */
    public function createStep($stepTitle, $bgColor, $fontColor)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        $vars = array(':workflowID' => $this->workflowID,
                        ':stepTitle' => $stepTitle,
                        ':jsSrc' => '',
        );
        $res = $this->db->prepared_query('INSERT INTO workflow_steps (workflowID, stepTitle, jsSrc)
    										VALUES (:workflowID, :stepTitle, :jsSrc)', $vars);

        return $this->db->getLastInsertID();
    }

    /**
     * @param string $stepTitle
     * @param string $bgColor
     * @param string $fontColor
     * @return int The newly created stepID
     */
    public function updateStep($stepID, $stepTitle, $bgColor = '', $fontColor = '')
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        $vars = array(':stepID' => $stepID,
                      ':stepTitle' => $stepTitle,
        );
        $res = $this->db->prepared_query('UPDATE workflow_steps
    										SET stepTitle=:stepTitle
    										WHERE stepID=:stepID', $vars);

        return 1;
    }

    /**
     * Set an inline indicator for a particular step
     *
     * @param int $stepID
     * @param int $indicatorID
     *
     * @return int
     */
    public function setStepInlineIndicator($stepID, $indicatorID) {
        $indicatorID = (int)$indicatorID;
        if(!$this->login->checkGroup(1)) {
            return 'Admin access required.';
        }
        
        if($indicatorID < 1) {
            $vars = array(
                ':stepID' => (int)$stepID
            );
            $res = $this->db->prepared_query(
                'DELETE FROM step_modules
                    WHERE stepID = :stepID
                        AND moduleName="LEAF_workflow_indicator"',
                $vars);
        }
        else {
            $vars = array(
                ':stepID' => (int)$stepID,
                ':config' => json_encode(array('indicatorID' => $indicatorID))
            );
            $res = $this->db->prepared_query(
                'INSERT INTO step_modules (stepID, moduleName, moduleConfig)
                    VALUES (:stepID, "LEAF_workflow_indicator", :config)
                    ON DUPLICATE KEY UPDATE moduleConfig=:config',
                $vars);
        }
        
        return 1;
    }

    /**
     * Set whether the specified step for the current Workflow requires a digital signature.
     * Uses the workflowID that was set with setWorkflowID(workflowID).
     *
     * @param int $stepID 				the step id to require a signature for
     * @param int $requiresSignature 	whether a signature is required
     *
     * @return int if the query was successful
     */
    public function requireDigitalSignature($stepID, $requireSignature) {
        if(!$this->login->checkGroup(1)) {
            return 'Admin access required.';
        }
        
        $vars = array(
            ':stepID' => (int)$stepID,
            ':requiresSignature' => $requireSignature
        );
        
        $res = $this->db->prepared_query(
            'UPDATE `workflow_steps` SET `requiresDigitalSignature` = :requiresSignature WHERE `stepID` = :stepID',
            $vars);
        
        return $res > 0;
    }

    public function linkDependency($stepID, $dependencyID)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        $vars = array(':stepID' => $stepID,
                      ':dependencyID' => $dependencyID,
        );
        $res = $this->db->prepared_query('INSERT INTO step_dependencies (stepID, dependencyID)
    										VALUES (:stepID, :dependencyID)', $vars);

        // populate records_dependencies so we can filter on items immediately
        $this->db->prepared_query('INSERT IGNORE INTO records_dependencies (recordID, dependencyID, filled)
    									SELECT recordID, :dependencyID as dependencyID, 0 as filled FROM workflow_steps
    										LEFT JOIN categories USING (workflowID)
    										LEFT JOIN category_count USING (categoryID)
    										WHERE stepID=:stepID AND count > 0', $vars);

        return true;
    }

    public function unlinkDependency($stepID, $dependencyID)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        $vars = array(':stepID' => $stepID,
                      ':dependencyID' => $dependencyID,
        );
        $res = $this->db->prepared_query('DELETE FROM step_dependencies
    										WHERE stepID=:stepID
    											AND dependencyID=:dependencyID', $vars);

        // clean up database
        $this->db->prepared_query('DELETE records_dependencies FROM records_dependencies
    								INNER JOIN category_count USING (recordID)
    								INNER JOIN categories USING (categoryID)
    								INNER JOIN workflow_steps USING (workflowID)
    								WHERE stepID=:stepID
    									AND dependencyID=:dependencyID
    									AND filled=0
    									AND records_dependencies.time IS NULL', $vars);

        return true;
    }

    public function updateDependency($dependencyID, $description)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        $vars = array(':dependencyID' => $dependencyID,
                      ':description' => $description,
        );
        $res = $this->db->prepared_query('UPDATE dependencies
    										SET description=:description
    										WHERE dependencyID=:dependencyID', $vars);

        return 1;
    }

    public function addDependency($description)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        $vars = array(':description' => $description,
        );
        $res = $this->db->prepared_query('INSERT INTO dependencies (description)
    										VALUES (:description)', $vars);

        return $this->db->getLastInsertID();
    }

    public function grantDependencyPrivs($dependencyID, $groupID)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        $vars = array(':dependencyID' => $dependencyID,
                      ':groupID' => $groupID,
        );
        $res = $this->db->prepared_query('INSERT INTO dependency_privs (dependencyID, groupID)
    										VALUES (:dependencyID, :groupID)', $vars);

        return true;
    }

    public function revokeDependencyPrivs($dependencyID, $groupID)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        $vars = array(':dependencyID' => $dependencyID,
                      ':groupID' => $groupID,
        );
        $res = $this->db->prepared_query('DELETE FROM dependency_privs
    										WHERE dependencyID=:dependencyID
    											AND groupID=:groupID', $vars);

        return true;
    }

    public function linkEvent($stepID, $actionType, $eventID)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        $vars = array(':workflowID' => $this->workflowID,
                      ':stepID' => $stepID,
                      ':actionType' => $actionType,
                      ':eventID' => $eventID,
        );
        $res = $this->db->prepared_query('INSERT INTO route_events (workflowID, stepID, actionType, eventID)
    										VALUES (:workflowID, :stepID, :actionType, :eventID)', $vars);

        return true;
    }

    public function unlinkEvent($stepID, $actionType, $eventID)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        $vars = array(':workflowID' => $this->workflowID,
                ':stepID' => $stepID,
                ':actionType' => $actionType,
                ':eventID' => $eventID,
        );
        $res = $this->db->prepared_query('DELETE FROM route_events
    										WHERE workflowID=:workflowID
    											AND stepID=:stepID
    											AND actionType=:actionType
    											AND eventID=:eventID', $vars);

        return true;
    }

    public function deleteWorkflow($workflowID)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        $vars = array(':workflowID' => $workflowID);
        $res = $this->db->prepared_query('SELECT * FROM workflow_steps
    										WHERE workflowID = :workflowID', $vars);
        if (count($res) > 0)
        {
            return 'Steps within the workflow must be deleted first.';
        }

        $res = $this->db->prepared_query('SELECT * FROM categories
    										WHERE workflowID = :workflowID
    											AND disabled=0', $vars);
        if (count($res) > 0)
        {
            return 'Forms must be disconnected from this workflow first.';
        }

        $res = $this->db->prepared_query('DELETE FROM workflows
    										WHERE workflowID = :workflowID', $vars);

        return true;
    }

    public function newWorkflow($description)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }

        $vars = array(':description' => $description,
        );
        $res = $this->db->prepared_query('INSERT INTO workflows (description)
    										VALUES (:description)', $vars);

        return $this->db->getLastInsertID();
    }

    public function setDynamicApprover($stepID, $indicatorID)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }
        $vars = array(':stepID' => $stepID,
                      ':indicatorID' => $indicatorID, );
        $this->db->prepared_query('UPDATE workflow_steps
                                            SET indicatorID_for_assigned_empUID=:indicatorID
        									WHERE stepID=:stepID', $vars);

        $vars = array(':indicatorID' => $indicatorID);
        $this->db->prepared_query('UPDATE indicators
    										SET required=1
    										WHERE indicatorID=:indicatorID', $vars);

        return true;
    }

    public function setDynamicGroupApprover($stepID, $indicatorID)
    {
        if (!$this->login->checkGroup(1))
        {
            return 'Admin access required.';
        }
        $vars = array(':stepID' => $stepID,
                ':indicatorID' => $indicatorID, );
        $this->db->prepared_query('UPDATE workflow_steps
                                            SET indicatorID_for_assigned_groupID=:indicatorID
        									WHERE stepID=:stepID', $vars);

        $vars = array(':indicatorID' => $indicatorID);
        $this->db->prepared_query('UPDATE indicators
    										SET required=1
    										WHERE indicatorID=:indicatorID', $vars);

        return true;
    }

    /**
     * Retrieve a high level map of the workflow (if valid) to show how steps are routed forwards
     * (a valid workflow is one that has an end)
     * @return array In-order steps of a workflow
     */
    public function getSummaryMap()
    {
        $summary = array();
        $steps = $this->getSteps();
        if (!isset($steps[0]))
        {
            return 0;
        }
        $initialStepID = $steps[0]['initialStepID'];

        $stepData = array();
        foreach ($steps as $step)
        {
            $stepData[$step['stepID']] = $step['stepTitle'];
        }

        $routes = $this->getRoutes();
        $routeData = array();
        foreach ($routes as $route)
        {
            if ($route['fillDependency'] != 0)
            {
                $routeData[$route['stepID']]['routes'][]['nextStepID'] = $route['nextStepID'];
                $routeData[$route['stepID']]['stepTitle'] = $stepData[$route['stepID']];
                if ($initialStepID == $route['stepID'])
                {
                    $routeData[$route['stepID']]['isInitialStep'] = 1;
                }
            }
        }

        $routeData = $this->pruneRoutes($initialStepID, $routeData);
        $stepIDs = implode(',', array_keys($routeData));

        $resStepDependencies = $this->db->prepared_query("SELECT * FROM step_dependencies
    												LEFT JOIN dependencies USING (dependencyID)
    												WHERE stepID IN ({$stepIDs})", array());

        if ($resStepDependencies != null)
        {
            foreach ($resStepDependencies as $stepDependency)
            {
                $routeData[$stepDependency['stepID']]['dependencies'][$stepDependency['dependencyID']] = $stepDependency['description'];
            }
        }

        $routeData[0]['routes'][0]['nextStepID'] = $initialStepID;
        $routeData[0]['stepTitle'] = 'Request Submitted';
        $routeData[0]['dependencies'][5] = 'Request Submitted';

        return $routeData;
    }

    // traverses routes to the end of a workflow, deletes dead ends
    // $routePath tracks routes that have already been checked
    private function checkRoute($stepID, $originStepID, &$routeData, $routePath = array())
    {
        if (!isset($routeData[$stepID]))
        {
            return 0;
        }
        foreach ($routeData[$stepID]['routes'] as $key => $route)
        {
            if ($route['nextStepID'] == $stepID
                || $routePath[$route['nextStepID']] == 1)
            {
                unset($routeData[$stepID]['routes'][$key]);
                continue;
            }

            $routeData[$stepID]['triggerCount']++;
            if ($route['nextStepID'] != 0)
            {
                if ($originStepID == $route['nextStepID'])
                {
                    unset($routeData[$stepID]['routes'][$key]);
                    continue;
                }
                if (!isset($routeData[$route['nextStepID']]['triggerCount']))
                {
                    if ($originStepID != 0)
                    {
                        $routePath[$originStepID] = 1;
                    }
                    $this->checkRoute($route['nextStepID'], $stepID, $routeData, $routePath);
                }
            }
        }
    }

    // removes routes that don't lead to the end
    private function pruneRoutes($initialStepID, &$routeData)
    {
        $this->checkRoute($initialStepID, 0, $routeData);
        $hasEnd = false;
        foreach ($routeData as $key => $route)
        {
            if (!isset($route['triggerCount']))
            {
                unset($routeData[$key]);
            }
            else
            {
                if (!isset($route['routes']))
                {
                    unset($routeData[$key]);
                }
            }
        }

        foreach ($routeData as $key => $route)
        {
            foreach ($route['routes'] as $stepKey => $step)
            {
                if ($step['nextStepID'] == 0)
                {
                    $hasEnd = true;
                }
                if (!isset($routeData[$step['nextStepID']])
                        && $step['nextStepID'] != 0)
                {
                    unset($routeData[$key]['routes'][$stepKey]);
                }
            }
        }
        if ($hasEnd == false)
        {
            return array();
        }

        return $routeData;
    }
}
