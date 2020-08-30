<?php
// this vars should be changed as needed 
class connection{
	private $servername = "localhost";        // MySQL server address
	private $username   = "es_admin";         // user for connecting to database
	private $password   = "oOWCN58udB24oGg8"; // password
	private $dbname     = "euro_spisok";      // database name
	public $conn;
	
	function __construct(){
		try {
			$this->conn = new PDO("mysql:host=$this->servername;charset=utf8mb4;dbname=$this->dbname", $this->username, $this->password);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			echo "Connection failed: " . $e->getMessage();
		}
	}

}
