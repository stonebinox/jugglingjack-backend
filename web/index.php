<?php

ini_set('display_errors', 1);
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../config/prod.php';
require __DIR__.'/../src/controllers.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
      'driver' => 'pdo_mysql',
      'dbname' => 'database',
      'user' => 'user',
      'password' => 'password',
      'host'=> "www.host.net",
    )
));
$app->register(new Silex\Provider\SessionServiceProvider, array(
    'session.storage.save_path' => dirname(__DIR__) . '/tmp/sessions'
));
$app->before(function(Request $request) use($app){
    $request->getSession()->start();
});
$app->get("/",function() use($app){
    return $app['twig']->render("index.html.twig");
});

$app->post("/api/login", function(Request $request) use ($app){
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