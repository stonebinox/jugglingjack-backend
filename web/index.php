<?php

ini_set('display_errors', 1);
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../config/prod.php';
require __DIR__.'/../src/controllers.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!isset($_SERVER['HTTP_ORIGIN'])) {
    // This is not cross-domain request
    exit;
}

$wildcard = TRUE; // Set $wildcard to TRUE if you do not plan to check or limit the domains
$credentials = TRUE; // Set $credentials to TRUE if expects credential requests (Cookies, Authentication, SSL certificates)
$allowedOrigins = array('https://jugglingjack.herokuapp.com');
if (!in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins) && !$wildcard) {
    // Origin is not allowed
    exit;
}
$origin = $wildcard && !$credentials ? '*' : $_SERVER['HTTP_ORIGIN'];

header("Access-Control-Allow-Origin: " . $origin);
if ($credentials) {
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Origin");
header('P3P: CP="CAO PSA OUR"'); // Makes IE to support cookies

// Handling the Preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));
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

$app->register(new TH\Silex\CORS\CORSProvider);

$app->before(function(Request $request) use($app){
    $request->getSession()->start();
});

$app->get("/",function() use($app){
    return $app['twig']->render("index.html.twig");
});

$app->post("/api/login", function(Request $request) use ($app){
    var_dump($request);
    if (($request->get("email")) && ($request->get("password"))) {
        require("../classes/adminMaster.php");
        require("../classes/userMaster.php");
        $user = new userMaster;
        $response = $user->loginUser($request->get("email"), $request->get("password"));
        return $response;
    }
    return "INVALID_PARAMETERS";
});

$app->run();
?>
