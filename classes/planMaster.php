<?php
/*----------------------------
Author: Anoop Santhanam
Date created: 30/6/18 23:03
Last modified: 30/6/18 23:03
Comments: Main class file for
planMaster.php
----------------------------*/
class planMaster extends adminMaster
{
    public $app = NULL;
    public $planValid = false;
    private $plan_id = NULL;
    function __construct($planID = NULL)
    {
        $this->app = $GLOBALS['app'];
        if ($planID != NULL) {
            $this->plan_id = addslashes(htmlentities($planID));
            $this->$planValid = $this->verifyPlan();
        }
    }

    function verifyPlan()
    {
        if ($this->plan_id != NULL) {
            $app = $this->app;
            $planID = $this->plan_id;
            $pm = "SELECT idplan_master FROM plan_master WHERE stat = '1' AND idplan_master = '$planID'";
            $pm = $app['db']->fetchAssoc($pm);
            if (!empty($pm)) {
                echo "here";
                return true;
            }
        }
        return false;
    }

    function getPlan()
    {
        if ($this->planValid) {
            $app = $this->app;
            $planID = $this->plan_id;
            $pm = "SELECT * FROM plan_master WHERE idplan_master = '$planID'";
            $pm = $app['db']->fetchAssoc($pm);
            if (!empty($pm)) {
                return $pm;
            }
        }
        return "INVALID_PLAN_ID";
    }

    function getPlans()
    {
        $app = $this->app;
        $pm = "SELECT idplan_master FROM plan_master WHERE stat = '1'";
        $pm = $app['db']->fetchAll($pm);
        $planArray = [];
        foreach ($pm as $planData) {
            $planID = $planData['idplan_master'];
            $this->__construct($planID);
            $plan = $this->getPlan();
            if (is_array($plan)) {
                array_push($planArray, $plan);
            }
        }
        if (empty($planArray)) {
            return "NO_PLANS_FOUND";
        }
        return $planArray;
    }
}
?>
