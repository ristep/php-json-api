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
 
require_once "conn.php";
require_once "json_data.class.php";

$cn = new connection; 

$method = $_SERVER['REQUEST_METHOD'];

$input  = file_get_contents("php://input");
//file_put_contents('inputDump.txt', $input, FILE_APPEND); // uncomment for debugging
$input = json_decode($input);

switch ($method) {
	case 'POST': // update, insert, delete and select 
    
      $type = Key($input);
      if (class_exists($type)) {
        $ret = (new $type($input->$type, $cn->conn))->process()->result();
      }else{  
        $ret = (object)[
          'OK' => false,
          'error' => true,
          'message' => "Undefined Request Method !!!",
          'data' => false
        ];
      };

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
