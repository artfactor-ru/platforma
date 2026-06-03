<?php

$login = 'admin';
$password = 'artfactor'; 

if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== $login || $_SERVER['PHP_AUTH_PW'] !== $password) {
    header('WWW-Authenticate: Basic realm="Restricted Access"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Доступ закрыт.';
    exit;
}

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
