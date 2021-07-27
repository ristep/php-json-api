<?php

require "init.php";
require "conn.php";

$cn = new connection(
  $servername = 'localhost',        // MySQL server address
  $dbname     = "api_test",         // database name
  $username   = "api_test",         // username
  $password   = "57RTt6kXjjC0uyKL"  // password
);

$method = $_SERVER['REQUEST_METHOD'];

$input  = file_get_contents("php://input");
// file_put_contents('inputDump.json', $input.";", FILE_APPEND); // uncomment for debugging
$input = json_decode($input);

switch ($method) {
  case 'POST': // update, insert, delete and select 

    $method = Key($input);
    if (file_exists("./methods/$method.class.php")) {
      require "./methods/$method.class.php";
      $ret = (new $method($input->$method, $cn->conn))->process()->result();
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
