<?php

require "init.php";
require "conn.php";

$cn = new connection(
  $servername = 'localhost',        // MySQL server address
  $dbname     = "euro_spisok",      // database name
  $username   = "es_admin",          // username
  $password   = "oOWCN58udB24oGg8"  // password
);

$method = $_SERVER['REQUEST_METHOD'];

$input  = file_get_contents("php://input");
//file_put_contents('inputDump.txt', $input, FILE_APPEND); // uncomment for debugging
$input = json_decode($input);

switch ($method) {
  case 'POST': // update, insert, delete and select 

    $type = Key($input);
    if (file_exists("./classes/$type.class.php")) {
      require "./classes/$type.class.php";
      $ret = (new $type($input->$type, $cn->conn))->process()->result();
    } else {
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
