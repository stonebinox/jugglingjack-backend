<?php

ini_set('display_errors', 1);
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../config/prod.php';
require __DIR__.'/../src/controllers.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!isset($_SERVER['HTTP_ORIGIN'])) {
    exit;
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Origin");
header('P3P: CP="CAO PSA OUR"');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));
// $app->register(new Silex\Provider\TwigServiceProvider(), array(
//     'twig.path' => __DIR__.'/views',
// ));
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
      'driver' => 'pdo_mysql',
      'dbname' => 'heroku_682d1c421c8dacf',
      'user' => 'bcf3878fae7bbc',
      'password' => '72339b63',
      'host'=> "us-cdbr-iron-east-04.cleardb.net",
    )
));
$app->register(new Silex\Provider\SessionServiceProvider, array(
    'session.storage.save_path' => dirname(__DIR__) . '/tmp/sessions'
));

$app->before(function(Request $request) use($app){
    $request->getSession()->start();
});

$app->get("/",function() use($app){
    return "INVALID_PARAMETERS";
});

$app->post("/api/login", function(Request $request) use($app){
    if (($request->get("email")) && ($request->get("password"))) {
        require("../classes/adminMaster.php");
        require("../classes/planMaster.php");
        require("../classes/userMaster.php");
        $user = new userMaster;
        $response = $user->authenticateUser($request->get("email"), $request->get("password"));
        return $response;
    }
    return "INVALID_PARAMETERS";
});

$app->post("/api/signup", function(Request $request) use($app){
    if (($request->get("name")) && ($request->get("email")) && ($request->get("password1")) && ($request->get("password2")) && ($request->get("admin_id")) && ($request->get("plan_id")) && ($request->get("country")) && ($request->get("city"))) {
        require("../classes/adminMaster.php");
        require("../classes/planMaster.php");
        require("../classes/userMaster.php");
        require("../classes/companyMaster.php");
        require("../classes/companyMemberMaster.php");
        $user = new userMaster;
        $response = $user->createAccount($request->get("name"), $request->get("email"), $request->get("password1"), $request->get("password2"), $request->get("admin_id"), $request->get("city"), $request->get("country"), $request->get("plan_id"));
        if (strpos($response, "ACCOUNT_CREATED_") !== false) {
            if ($request->get("admin_id") == 2) {
                $company = new companyMaster;
                $r2 = $company->createCompany($request->get("company"), $request->get("company_description"));
                if (is_numeric($r2)) {
                    $companyMember = new companyMemberMaster;
                    $e = explode("ACCOUNT_CREATED_", $response);
                    $userID = $e[1];
                    $r2 = $companyMember->addCompanyMember($r2, $userID);
                    if ($r2 != "COMPANY_MEMBER_ADDED") {
                        return $r2;
                    }
                }
                else {
                    return $r2;
                }
            }
        }
        return $response;
    }
    return "INVALID_PARAMETERS";
});

$app->get("/api/getActiveApplications", function(Request $request) use($app){
    require("../classes/adminMaster.php");
    require("../classes/planMaster.php");
    require("../classes/userMaster.php");
    require("../classes/companyMaster.php");
    require("../classes/companyMemberMaster.php");
    require("../classes/applicationMaster.php");
    $offset = 0;
    if ($request->get("offset")) {
        $offset = $request->get("offset");
    }
    $application = new applicationMaster;
    $response = $application->getAllActiveApplications($offset);
    return $response;
});

$app->post("/api/createApplication", function(Request $request) use($app) {
    if (($request->get("company_id")) && ($request->get("application_title"))) {
        require("../classes/adminMaster.php");
        require("../classes/planMaster.php");
        require("../classes/userMaster.php");
        require("../classes/companyMaster.php");
        require("../classes/companyMemberMaster.php");
        require("../classes/applicationMaster.php");
        $application = new applicationMaster;
        $description = "";
        if (($request->get("application_description")) && ($request->get("application_title") != "")){
            $description = $request->get("application_description");
        }
        $response = $application->createApplication($request->get("company_id"), $request->get("application_title"), $description);
        return $response;
    }
    return "INVALID_PARAMETERS";
});

$app->get("/api/getUserID", function(Request $request) use($app) {
    if ($request->get("user_email")) {
        require("../classes/adminMaster.php");
        require("../classes/planMaster.php");
        require("../classes/userMaster.php");
        $user = new userMaster;
        $userEmail = $user->getUserIDFromEmail($request->get("user_email"));
        return $userEmail;
    }
    return "INVALID_PARAMETERS";
});

$app->get("/api/getUser", function(Request $request) use($app) {
    if ($request->get("user_id")) {
        require("../classes/adminMaster.php");
        require("../classes/planMaster.php");
        require("../classes/userMaster.php");
        $user = new userMaster($request->get("user_id"));
        $userData = $user->getUser();
        if (is_array($userData)) {
            return json_encode($userData);
        }
        return $userData;
    }
    return "INVALID_PARAMETERS";
});

$app->get("/api/getCompanyFromUserID", function(Request $request) use($app){
    if ($request->get("user_id")) {
        require("../classes/adminMaster.php");
        require("../classes/planMaster.php");
        require("../classes/userMaster.php");
        require("../classes/companyMaster.php");
        require("../classes/companyMemberMaster.php");
        $company = new companyMemberMaster;
        $companyData = $company->getCompanyFromUserID($request->get("user_id"));
        if (is_array($companyData)) {
            return json_encode($companyData);
        }
        return $companyData;
    }
    return "INVALID_PARAMETERS";
});

$app->run();
?>
