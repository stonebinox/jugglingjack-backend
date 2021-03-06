<?php
/*----------------------------------------
Author: Anoop Santhanam
Date Created: 22/10/17 18:41
Last Modified: 22/10/17 18:41
Comments: Main class file for user_master
table.
----------------------------------------*/
class userMaster extends planMaster
{
    public $app=NULL;
    public $userValid=false;
    private $user_id=NULL;
    function __construct($userID=NULL)
    {
        $this->app=$GLOBALS['app'];
        if($userID!=NULL)
        {
            $this->user_id=addslashes(htmlentities($userID));
            $this->userValid=$this->verifyUser();
        }
    }
    function verifyUser() //to verify a user
    {
        if($this->user_id!=NULL)
        {
            $userID=$this->user_id;
            $app=$this->app;
            $um="SELECT admin_master_idadmin_master, plan_master_idplan_master FROM user_master WHERE stat='1' AND iduser_master='$userID'";
            $um=$app['db']->fetchAssoc($um);
            if(($um!="")&&($um!=NULL))
            {
                $adminID=$um['admin_master_idadmin_master'];
                adminMaster::__construct($adminID);
                if($this->adminValid)
                {
                    $planID = $um['plan_master_idplan_master'];
                    planMaster::__construct($planID);
                    if ($this->planValid) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    function getUser() //to get a user's details
    {
        if($this->userValid)
        {
            $app=$this->app;
            $userID=$this->user_id;
            $um="SELECT * FROM user_master WHERE iduser_master='$userID'";
            $um=$app['db']->fetchAssoc($um);
            if (!empty($um))
            {
                $adminID=$um['admin_master_idadmin_master'];
                adminMaster::__construct($adminID);
                $admin=adminMaster::getAdmin();
                if(is_array($admin))
                {
                    $um['admin_master_idadmin_master']=$admin;
                }
                $planID = $um['plan_master_idplan_master'];
                planMaster::__construct($planID);
                $plan = planMaster::getPlan();
                if (is_array($plan)) {
                    $um['plan_master_idplan_master'] = $plan;
                }
                return $um;
            }
            else
            {
                return "INVALID_USER_ID";
            }
        }
        else
        {
            return "INVALID_USER_ID";
        }
    }
    function getUserIDFromEmail($userEmail)
    {
        $app=$this->app;
        $userEmail=addslashes(htmlentities($userEmail));
        $um="SELECT iduser_master FROM user_master WHERE stat='1' AND user_email='$userEmail'";
        $um=$app['db']->fetchAssoc($um);
        if(($um!="")&&($um!=NULL))
        {
            return $um['iduser_master'];
        }
        else
        {
            return "INVALID_USER_EMAIL";
        }
    }
    function getUserPassword()
    {
        if($this->userValid)
        {
            $app=$this->app;
            $userID=$this->user_id;
            $um="SELECT user_password FROM user_master WHERE iduser_master='$userID'";
            $um=$app['db']->fetchAssoc($um);
            if(($um!="")&&($um!=NULL))
            {
                return $um['user_password'];
            }
            else
            {
                return "INVALID_USER_ID";
            }
        }
        else
        {
            return "INVALID_USER_ID";
        }
    }
    function authenticateUser($userEmail,$userPassword) //to log a user in
    {
        $userEmail=addslashes(htmlentities($userEmail));
        $userID=$this->getUserIDFromEmail($userEmail);
        $app=$this->app;
        if(is_numeric($userID))
        {
            $this->__construct($userID);
            $userPassword=md5($userPassword);
            $storedPassword=$this->getUserPassword();
            if($userPassword==$storedPassword)
            {
                $up="UPDATE user_master SET online_flag='1' WHERE iduser_master='$userID'";
                $up=$app['db']->executeUpdate($up);
                $app['session']->set('uid',$userID);
                return "AUTHENTICATE_USER";
            }
            else
            {
                return "INVALID_USER_CREDENTIALS";
            }
        }
        else
        {
            return "INVALID_USER_CREDENTIALS";
        }
    }
    function createAccount($userName,$userEmail,$userPassword,$userPassword2,$adminID=32, $city, $country, $planID = 2) //to create an account
    {
        $app=$this->app;
        $userName=trim(addslashes(htmlentities($userName)));
        if(($userName!="")&&($userName!=NULL))
        {
            $userEmail=trim(addslashes(htmlentities($userEmail)));
            if(filter_var($userEmail, FILTER_VALIDATE_EMAIL)){
                if(strlen($userPassword)>=8)
                {
                    if($userPassword===$userPassword2)
                    {
                        $adminID=addslashes(htmlentities($adminID));
                        adminMaster::__construct($adminID);
                        if($this->adminValid)
                        {
                            $planID = addslashes(htmlentities($planID));
                            planMaster::__construct($planID);
                            if ($this->planValid) {
                                $city = addslashes(htmlentities($city));
                                $country = addslashes(htmlentities($country));
                                $um="SELECT iduser_master FROM user_master WHERE user_email='$userEmail' AND stat='1'";
                                $um=$app['db']->fetchAssoc($um);
                                if(($um=="")||($um==NULL))
                                {
                                    $hashPassword=md5($userPassword);
                                    $in="INSERT INTO user_master (timestamp,user_name,user_email,user_password, admin_master_idadmin_master, user_city, user_country, plan_master_idplan_master) VALUES (NOW(),'$userName','$userEmail','$hashPassword', '$adminID', '$city', '$country', '$planID')";
                                    $in=$app['db']->executeQuery($in);
                                    $um = "SELECT iduser_master FROM user_master WHERE stat = '1' AND user_email = '$userEmail'";
                                    $um = $app['db']->fetchAssoc($um);
                                    return "ACCOUNT_CREATED_" . $um['iduser_master'];
                                }
                                else
                                {
                                    return "ACCOUNT_ALREADY_EXISTS";
                                }
                            }
                            else {
                                return "INVALID_PLAN_ID";
                            }
                        }
                        else
                        {
                            return "INVALID_ADMIN_TYPE_ID";
                        }
                    }
                    else
                    {
                        return "PASSWORD_MISMATCH";
                    }
                }
                else
                {
                    return "INVALID_PASSWORD";
                }
            }
            else
            {
                return "INVALID_USER_EMAIL";
            }
        }
        else
        {
            return "INVALID_USER_NAME";
        }
    }
    function logout() //to log a user out
    {
        if($this->userValid)
        {
            $app=$this->app;
            $userID=$this->user_id;
            $um="UPDATE user_master SET online_flag='0' WHERE iduser_master='$userID'";
            $um=$app['db']->executeUpdate($um);
            $app['session']->remove("uid");
            return "USER_LOGGED_OUT";
        }
        else
        {
            return "INVALID_USER_ID";
        }
    }
    function getAdminType() //to get user's admin role
    {
        $app=$this->app;
        if($this->userValid)
        {
            $userID=$this->user_id;
            $um="SELECT admin_master_idadmin_master FROM user_master WHERE iduser_master='$userID'";
            $um=$app['db']->fetchAssoc($um);
            if(($um!="")&&($um!=NULL))
            {
                return $um['admin_master_idadmin_master'];
            }
            else
            {
                return "INVALID_USER_ID";
            }
        }
        else
        {
            return "INVALID_USER_ID";
        }
    }
}
?>
