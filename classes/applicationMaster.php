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
                $applicationID = $am['idapplication_master'];
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
}