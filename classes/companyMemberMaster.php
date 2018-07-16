<?php
/*--------------------------
Author: Anoop Santhanam
Date created: 30/6/18 23:43
Last modified: 30/6/18 23:43
Comments: Main class file for
company_member_master table.
--------------------------*/
class companyMemberMaster extends companyMaster
{
    public $app = NULL;
    public $companyMemberValid = false;
    private $company_member_id = NULL;
    function __construct($companyMemberID = NULL)
    {
        $this->app = $GLOBALS['app'];
        if ($companyMemberID != NULL) {
            $this->company_member_id = addslashes(htmlentities($companyMemberID));
            $this->companyMemberValid = $this->verifyCompanyMember();
        }
    }

    function verifyCompanyMember()
    {
        if ($this->company_member_id != NULL) {
            $app = $this->app;
            $companyMemberID = $this->company_member_id;
            $cmm = "SELECT company_master_idcompany_master, user_master_iduser_master FROM company_member_master WHERE stat = '1' AND idcompany_member_master = '$companyMemberID'";
            $cmm = $app['db']->fetchAssoc($cmm);
            if (!empty($cmm)) {
                $companyID = $cmm['company_master_idcompany_master'];
                companyMaster::__construct($companyID);
                if ($this->companyValid) {
                    $userID = $cmm['user_master_iduser_master'];
                    userMaster::__construct($userID);
                    if ($this->userValid) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    function getCompanyMember()
    {
        if ($this->companyMemberValid) {
            $app = $this->app;
            $companyMemberID = $this->company_member_id;
            $cmm = "SELECT * FROM company_member_master WHERE stat = '1' AND idcompany_member_master = '$companyMemberID'";
            $cmm = $app['db']->fetchAssoc($cmm);
            if (!empty($cmm)) {
                $companyID = $cmm['company_master_idcompany_master'];
                companyMaster::__construct($companyID);
                $company = companyMaster::getCompany();
                if (is_array($company)) {
                    $cmm['company_master_idcompany_master'] = $company;
                }
                $userID = $cmm['user_master_iduser_master'];
                userMaster::__construct($userID);
                $user = userMaster::getUser();
                if (is_array($user)) {
                    $cmm['user_master_iduser_master'] = $user;
                }
                return $cmm;
            }
        }
        return "INVALID_COMPANY_MEMBER_ID";
    }

    function getCompanyMembers($companyID)
    {
        $companyID = addslashes(htmlentities($companyID));
        companyMaster::__construct($companyID);
        if ($this->companyValid) {
            $app = $this->app;
            $cmm = "SELECT idcompany_member_master FROM company_member_master WHERE stat = '1' AND company_master_idcompany_master = '$companyID'";
            $cmm = $app['db']->fetchAll($cmm);
            $companyMemberArray = [];
            foreach ($cmm as $companyMemberData) {
                $companyMemberID = $companyMemberData['idcompany_member_master'];
                $this->__construct($companyMemberID);
                $companyMember = $this->getCompanyMember();
                if (is_array($companyMember)) {
                    array_push($companyMemberArray, $companyMember);
                }
            }
            if (empty($companyMemberArray)) {
                return "NO_COMPANY_MEMBERS_FOUND";
            }
            return $companyMemberArray;
        }
        return "INVALID_COMPANY_ID";
    }

    function addCompanyMember($companyID, $userID)
    {
        $app = $this->app;
        $companyID = addslashes(htmlentities($companyID));
        companyMaster::__construct($companyID);
        if ($this->companyValid) {
            $userID = addslashes(htmlentities($userID));
            userMaster::__construct($userID);
            if ($this->userValid) {
                $cmm = "SELECT idcompany_member_master FROM company_member_master WHERE stat = '1' AND company_master_idcompany_master = '$companyID' AND user_master_iduser_master = '$userID'";
                $cmm = $app['db']->fetchAssoc($cmm);
                if (empty($cmm)) {
                    $in = "INSERT INTO company_member_master (timestamp, company_master_idcompany_master, user_master_iduser_master) VALUES (NOW(), '$companyID', '$userID')";
                    $in = $app['db']->executeQuery($in);
                    return "COMPANY_MEMBER_ADDED";
                }
                return "USER_ALREADY_ADDED";
            }
            return "INVALID_USER_ID";
        }
        return "INVALID_COMPANY_ID";
    }

    function getCompanyFromUserID($userID)
    {
        $userID = addslashes(htmlentities($userID));
        userMaster::__construct($userID);
        if ($this->userValid) {
            $app = $this->app;
            $cm = "SELECT company_master_idcompany_master FROM company_member_master WHERE user_master_iduser_master = '$userID' AND stat = '1' ORDER BY idcompany_member_master DESC LIMIT 1";
            $cm = $app['db']->fetchAssoc($cm);
            if (!empty($cm)) {
                $companyID = $cm['company_master_idcompany_master'];
                companyMaster::__construct($companyID);
                $company = companyMaster::getCompany();
                if (is_array($company)) {
                    return $company;
                }
                return "INVALID_COMPANY_ID";
            }
        }
        return "INVALID_USER_ID";
    }
}
?>
