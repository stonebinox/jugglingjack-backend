<?php
/*------------------------------
Author: Anoop Santhanam
Date Created: 10/7/18 00:11
Last modified: 11/7/18 21:08
Comments: Main class file for
application_master table.
------------------------------*/
class applicationMaster extends companyMemberMaster
{
    public $app = NULL;
    public $applicationValid = FALSE;
    private $application_id = NULL;

    function __construct($applicationID = NULL)
    {
        $this->app = $GLOBALS['app'];
        if ($applicationID != NULL) {
            $this->application_id = addslashes(htmlentities($applicationID));
            $this->applicationValid = $this->verifyApplication();
        }
    }

    function verifyApplication()
    {
        if ($this->application_id != NULL) {
            $app = $this->app;
            $applicationID = $this->application_id;
            $am = "SELECT company_master_idcompany_master FROM application_master WHERE stat = '1' AND idapplication_master = '$applicationID'";
            $am = $app['db']->fetchAssoc($am);
            if (!empty($am)) {
                $companyID = $am['company_master_idcompany_master'];
                companyMaster::__construct($companyID);
                if ($this->companyValid) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    function getApplication()
    {
        if ($this->applicationValid) {
            $app = $this->app;
            $applicationID = $this->application_id;
            $am = "SELECT * FROM application_master WHERE idapplication_master = '$applicationID'";
            $am = $app['db']->fetchAssoc($am);
            if (!empty($am)) {
                $companyID = $am['company_master_idcompany_master'];
                companyMaster::__construct($companyID);
                $company = companyMaster::getCompany();
                if (is_array($company)) {
                    $am['company_master_idcompany_master'] = $company;
                }
                return $am;
            }
        }
        return "INVALID_APPLICATION_ID";
    }

    function getApplicationsFromCompany($companyID)
    {
        $companyID = addslashes(htmlentities($companyID));
        companyMaster::__construct($companyID);
        if ($this->companyValid) {
            $app = $this->app;
            $am = "SELECT idapplication_master FROM application_master WHERE stat = '1' AND company_master_idcompany_master = '$companyID'";
            $am = $app['db']->fetchAll($am);
            $applicationArray = [];
            foreach ($am as $application) {
                $applicationID = $application['idapplication_master'];
                $this->__construct($applicationID);
                $appicationData = $this->getApplication();
                if (is_array($applicationData)) {
                    array_push($applicationArray, $applicationData);
                }
            }
            echo count($applicationArray);
            if (!empty($applicationArray)) {
                return $applicationArray;
            }
            return "NO_APPLICATIONS_FOUND";
        }
        return "INVALID_COMPANY_ID";
    }

    function getAllActiveApplications($offset = 0)
    {
        $app = $this->app;
        $offset = addslashes(htmlentities($offset));
        if (($offset != NULL) && (is_numeric($offset)) && ($offset >= 0)) {
            $am = "SELECT idapplication_master FROM application_master WHERE stat = '1' ORDER BY idapplication_master DESC LIMIT $offset, 20";
            $am = $app['db']->fetchAll($am);
            $applicationArray = [];
            foreach ($am as $applicationData) {
                $applicationID = $applicationData['idapplication_master'];
                $this->__construct($applicationID);
                $application = $this->getApplication();
                if (is_array($application)) {
                    array_push($applicationArray, $application);
                }
            }
            if (!empty($applicationArray)) {
                return $applicationArray;
            }
            return "NO_APPLICATIONS_FOUND";
        }
        return "INVALID_OFFSET_VALUE";
    }

    function createApplication($companyID, $applicationTitle, $applicationDescription = "")
    {
        $companyID = addslashes(htmlentities($companyID));
        companyMaster::__construct($companyID);
        if ($this->companyValid) {
            $applicationTitle = trim(ucwords(strtolower(addslashes(htmlentities($applicationTitle)))));
            if ($applicationTitle != "") {
                $applicationDescription = trim(addslashes(htmlentities($applicationDescription)));
                $app = $this->app;
                $applications = $this->getApplicationsFromCompany($companyID);
                if (!is_array($applications)) { //ideally need to check plan and check the limit that they can post
                    $in = "INSERT INTO application_master (timestamp, company_master_idcompany_master, application_title, application_description) VALUES (NOW(), '$companyID', '$applicationTitle', '$applicationDescription')";
                    $in = $app['db']->executeQuery($in);
                    $r = companyMaster::updateApplicationFlag(true);
                    return "APPLICATION_CREATED";
                }
                return "APPLICATION_ALREADY_EXISTS";
            }
            return "INVALID_APPLICATION_TITLE";
        }
        return "INVALID_COMPANY_ID";
    }

    function getCompanyID()
    {
        if ($this->applicationValid) {
            $applicationID = $this->application_id;
            $app = $this->app;
            $am = "SELECT company_master_idcompany_master FROM application_master WHERE idapplication_master = '$applicationID'";
            $am = $app['db']->fetchAssoc($am);
            if (!empty($am)) {
                return $am['company_master_idcompany_master'];
            }
        }
        return "INVALID_APPLICATION_ID";
    }

    function deleteApplication()
    {
        if ($this->applicationValid) {
            $applicationID = $this->application_id;
            $app = $this->app;
            $companyID = $this->getCompanyID();
            $am = "UPDATE application_master SET stat = '0' WHERE idapplication_master = '$applicationID'";
            $am = $app['db']->executeUpdate($am);
            companyMaster::__construct($companyID);
            $r = companyMaster::updateApplicationFlag(false);
            return "APPLICATION_DELETED";
        }
        return "INVALID_APPLICATION_ID";
    }

    function deleteApplicationFromCompanyID($companyID)
    {
        $companyID = addslashes(htmlentities($companyID));
        companyMaster::__construct($companyID);
        if ($this->companyValid) {
            $app = $this->app;
            $am = "SELECT idapplication_master FROM application_master WHERE company_master_idcompany_master = '$companyID' AND stat = '1'";
            $am = $app['db']->fetchAssoc($am);
            if (!empty($am)) {
                $applicationID = $am['idapplication_master'];
                $this->__construct($applicationID);
                $r = $this->deleteApplication();
                return $r;
            }
            else {
                return "NO_COMPANIES_FOUND";
            }
        }
        return "INVALID_COMPANY_ID";
    }
}
