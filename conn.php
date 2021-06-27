<?php
/**
 * connection class to MySQL server database
 */
class connection{
	public  $conn;
				
	/**
	 * Method __construct
	 *
	 * @return void
	 */
	function __construct( 
			$servername,	// MySQL server address
			$dbname,      // database name
			$username,		// username
			$password 		// password
	){
		try {
			$this->conn = new PDO( "mysql:host=$servername;charset=utf8mb4;dbname=$dbname", $username, $password );
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			echo "Connection failed: " . $e->getMessage();
		}
	}

}
