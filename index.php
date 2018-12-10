<?php
namespace Dot\Crud;

ini_set( 'error_reporting', E_ALL );
ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
#ini_set('error_reporting', 0);
#ini_set('display_errors', 0);

date_default_timezone_set( 'Asia/Yekaterinburg' );
session_start();

define( 'ROOT_DIR', realpath( dirname(__FILE__) ) . '/' );
define( 'CURRENT_VERSION', 1 );
require( ROOT_DIR . 'Crud/System/Init.php' );

$config = new System\Config([
    'username' => '',
    'password' => '',
    'database' => '',
]);

$request = new System\Request();
$api = new Running\Api($config);

$response = $api->handle($request);
$response->output();
