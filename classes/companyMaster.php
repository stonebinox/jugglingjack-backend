<?php
/*----------------------------
Author: Anoop Santhanam
Date created: 30/6/18 23:30
Last modified: 30/6/18 23:30
Comments: Main class file for
company_master table.
----------------------------*/
class companyMaster extends userMaster
{
    public $app = NULL;
    public $companyValid = false;
    private $company_id = NULL;
    function __construct($companyID = NULL)
    {
        $this->app = $GLOBALS['app'];
        if ($this->company_id != NULL) {
            $this->company_id = addslashes(htmlentities($companyID));
            $this->companyValid = $this->verifyCompany();
        }
    }

    function verifyCompany()
    {
        if ($this->company_id != NULL) {
            $app = $this->app;
            $companyID = $this->company_id;
            $cm = "SELECT idcompany_master FROM company_master WHERE stat = '1' AND idcompany_master = '$companyID'";
            $cm = $app['db']->fetchAssoc($cm);
            if (!empty($cm)) {
                return true;
            }
        }
        return false;
    }

    function getCompany()
    {
        if ($this->companyValid) {
            $app = $this->app;
            $companyID = $this->company_id;
            $cm = "SELECT * FROM company_master WHERE idcompany_master = '$companyID'";
            $cm = $app['db']->fetchAssoc($cm);
            if (!empty($cm)) {
                return $cm;
            }
        }
        return "INVALID_COMPANY_ID";
    }

    function getCompanies()
    {
        $app = $this->app;
        $cm = "SELECT idcompany_master FROM company_master WHERE stat = '1' ORDER BY idcompany_master ASC";
        $cm = $app['db']->fetchAll($cm);
        $companyArray = [];
        foreach ($cm as $companyData) {
            $companyID = $companyData['idcompany_master'];
            $this->__construct($companyID);
            $company = $this->getCompany();
            if (is_array($company)) {
                array_push($companyArray, $company);
            }
        }
        if (empty($companyArray)) {
            return "NO_COMPANIES_FOUND";
        }
        return $companyArray;
    }
}
?>