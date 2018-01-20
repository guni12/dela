<?php
/**
 * Bootstrap the framework and handle the request.
 */

define("ANAX_INSTALL_PATH", realpath(__DIR__ . "/.."));
define("ANAX_APP_PATH", ANAX_INSTALL_PATH);

require ANAX_INSTALL_PATH . "/config/error_reporting.php";
require ANAX_INSTALL_PATH . "/vendor/autoload.php";

$di  = new \Anax\DI\DIFactoryConfig("di.php");

$di->get("router")->handle(
    $di->get("request")->getRoute(),
    $di->get("request")->getMethod()
);
