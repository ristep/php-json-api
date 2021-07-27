<?php
// initializing headers and debug

// uncomment for debugging messages 
/*
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);
*/

/* 
** this Headers are not tested quite well yet
*/
header("Content-Type: application/vnd.api+json");
header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
//Only post request is valid
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, X-Requested-With');

// this prevent errors from some browsers preflight OPTIONS request
// some illuminations here https://smanzary.sman.cloud/cors-nightmare-in-spa-applications/
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') die();
