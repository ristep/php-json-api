<?php

// uncomment for debugging messages 
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

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
 
$cn = require "conn.php";
require_once "dataclasses.php";

$method = $_SERVER['REQUEST_METHOD'];

$input  = file_get_contents("php://input");
// file_put_contents('inputDump.txt', $input, FILE_APPEND); // uncomment for debugging
$input = json_decode($input);

$tokenData = false;
//$tokenData = require_once('tokening.php'); // for user validation uncomment

switch ($method) {
	case 'POST': // update, insert, delete and select 
    
    if(isset($input->get)){
      $ret = (new get($input->get, $cn, $tokenData))->process()->result();
    }
    elseif(isset($input->post)){
      $ret = (new post($input->post, $cn, $tokenData))->process()->result();
    }
    elseif(isset($input->patch)){
      $ret = (new patch($input->patch, $cn, $tokenData))->process()->result();
    }
    elseif(isset($input->delete)){
      $ret = (new delete($input->delete, $cn, $tokenData))->process()->result();
    }
    elseif(isset($input->getToken)){
      $ret = (new getToken($input->getToken, $cn, $tokenData))->process()->result();
    }

    else{
      $ret = (object)[
        'OK' => false,
        'error' => true,
        'message' => "Undefined Request Method !!!",
        'data' => false
      ];
    }
  
		//file_put_contents('inputDump.txt', 'In post method'.$input->phpFunction, FILE_APPEND);
		// sleep(2); // time delay for debugging in the clients, blur testing in reactjs :)
  break;
  case 'PUT':
	case 'GET':
  case 'DELETE':
  case 'PATCH':
  default:
  		http_response_code(417);
			$ret = ((object)[
	    	'error' => "$method method forbidden",
      	'code' => 417,
      	'message' => "$method - Not implemented use POST",
				'Info' => "This is test endpoint for RP_JSON-PHP-API. If you don't know what is this? You are here probably by mistake",
      	'InputData' => $input
    ]);
}
echo json_encode($ret, JSON_NUMERIC_CHECK + JSON_PRESERVE_ZERO_FRACTION);
