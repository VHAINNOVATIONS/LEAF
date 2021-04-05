<?php
/*
 * As a work of the United States government, this project is in the public domain within the United States.
 */

/*
    Telemetry
    Date Created: March 3, 2016

*/
$currDir = dirname(__FILE__);

include_once $currDir . '/../globals.php';

class Telemetry
{
    public $siteRoot = '';

    private $db;

    private $login;

    private const ROOT_UPLOAD = '/var/www/ERM_UPLOADS/';

    public function __construct($db, $login)
    {
        $this->db = $db;
        $this->login = $login;

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        $this->siteRoot = "{$protocol}://" . HTTP_HOST . dirname($_SERVER['REQUEST_URI']) . '/';
    }

    public function getRequestsSimple($from = 0, $to = 0)
    {
        $to = $to == 0 ? time() : $to;
        $vars = array(':from' => (int)$from,
                      ':to' => (int)$to,
        );
        $res = $this->db->prepared_query('SELECT recordID, categoryName, submitted FROM records
											LEFT JOIN category_count USING (recordID)
											LEFT JOIN categories USING (categoryID)
											WHERE submitted > 0
												AND deleted = 0
												AND workflowID > 0
												AND disabled = 0
												AND count >= 1
    											AND date >= :from
    											AND date <= :to', $vars);

        return $res;
    }


    /**
     * Purpose: Get data about number of Requests per in a given month
     * @return array
     */
    public function getRequestsPerMonth()
    {
        $vars = array();
        $resCategories = $this->db->prepared_query('SELECT * FROM categories
														WHERE workflowID > 0
															AND disabled = 0', $vars);
        $dataCategories = array();
        foreach ($resCategories as $category)
        {
            $dataCategories[$category['categoryID']]['name'] = $category['categoryName'];
            $dataCategories[$category['categoryID']]['description'] = $category['categoryDescription'];
        }

        // get all requests
        $res = $this->db->prepared_query('SELECT recordID, categoryID, date as initiated, submitted, stepID FROM records
											LEFT JOIN category_count USING (recordID)
											LEFT JOIN categories USING (categoryID)
											LEFT JOIN records_workflow_state USING (recordID)
											WHERE submitted > 0
												AND deleted = 0
												AND workflowID > 0
												AND disabled = 0
												AND count >= 1', $vars);

        // get requests' last action time
        $resActionTime = $this->db->prepared_query('SELECT recordID, filled, max(time) as time FROM records_dependencies
														GROUP BY recordID
														ORDER BY max(time)', $vars);

        $data_actionTime = array();
        foreach ($resActionTime as $item)
        {
            $data_actionTime[$item['recordID']] = $item;
        }

        $datum = array();
        foreach ($res as $item)
        {
            $datum[$item['recordID']] = $item;

            // find requests that have ended
            if ($item['stepID'] == null)
            {
                $datum[$item['recordID']]['completed'] = $data_actionTime[$item['recordID']]['time'];
            }
        }

        $data = array();
        foreach ($datum as $record)
        {
            $date = date('Y-m', $record['submitted']);
            if (!isset($data[$date][$record['categoryID']]))
            {
                $data[$date][$record['categoryID']]['count'] = 0;
                $data[$date][$record['categoryID']]['countResolved'] = 0;
                $data[$date][$record['categoryID']]['timeFillOut'] = 0;
                $data[$date][$record['categoryID']]['timeResolved'] = 0;
            }

            $data[$date][$record['categoryID']]['count']++;

            // find requests that have been resolved
            if ($record['stepID'] == null)
            {
                $timeFillOut = $data_actionTime[$record['recordID']]['time'] - $record['submitted'];
                $data[$date][$record['categoryID']]['countResolved']++;
                $data[$date][$record['categoryID']]['timeResolved'] += $timeFillOut;
            }

            $timeFillOut = $record['submitted'] - $record['initiated'];
            $data[$date][$record['categoryID']]['timeFillOut'] += $timeFillOut;
        }

        $output = array();
        $output['categories'] = $dataCategories;
        $output['records'] = $data;

        return $output;
    }


    /**
     * Purpose: Get total size of all uploads in portal folder
     * @param $visn
     * @param $facility
     * @param $name
     * @return string
     */
    public function getRequestUploadStorage($showAll = true, $visn = "", $facility = "", $name = "") {
        $runCommand = false;
        if ($showAll) {
            $command = 'du --max-depth=3 ' . self::ROOT_UPLOAD . ' | sort -rn';
            $runCommand = true;
        } else {
            if (!empty($visn) && !empty($facility) && !empty($name)) {
                $command = 'du ' . self::ROOT_UPLOAD . $visn . '/' . $facility . '/' . $name. ' | sort -rn';
                $runCommand = true;
            }
        }

        $output = false;
        if ($runCommand) {
            $output = shell_exec($command);
        }
        if ($output) {
            $rtnDirectories = array();
            $sizeOutput = explode("\n", $output);
            foreach($sizeOutput as $outputLine) {
                // Need to remove root directory from path and replace if neccessary
                $outputInfo = explode("\t", $outputLine);

                $outputInfo[1] = str_replace(self::ROOT_UPLOAD, "", $outputInfo[1]);

                // Need to include only portals not main directories
                $directoryLevel = explode("/", $outputInfo[1]);
                if (count($directoryLevel) === 3) {
                    // Add directory size and location to output
                    $tmpInfo = array(
                        'location' => (string)($outputInfo[1]),
                        'filesize' => $outputInfo[0]
                    );
                    if (!empty($outputInfo[0]) && !empty($outputInfo[1])) {
                        $rtnDirectories[] = $tmpInfo;
                    }
                }
            }
            return $rtnDirectories;

        } else {
            return '';
        }
    }

}
