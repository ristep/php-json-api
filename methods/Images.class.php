<?php

/**
 * get
 */
class Images
{
	private $inp;
	private $output;
	private $conn;

	/**
	 * __construct
	 *
	 * @param  mixed $inp
	 * @param  mixed $conn
	 * @return void
	 */
	function __construct($inp, $conn)
	{
		$this->inp = $inp;
		$this->conn = $conn;
		$this->output = [
			'OK' => false,
			'error' => true,
			'errorType' => 'Undefined server ERROR!',
			'code' => 500,
			'message' => "Internal RPC server error!"
		];
	}

	/**
	 * process
	 *
	 * @return {this}
	 */
	public function process()
	{
		$data = [];
		$parArr = []; 
		$where = '';
		
		try {
  
			$files = array_slice ( scandir("./img/".$this->inp->type."/".$this->inp->id ), 2 );

			foreach( $files as &$fl) 
				$fl = "img/".$this->inp->id."/".$fl;

			$this->output = [];
			$this->output = [
				'OK' => true,
				'type' => $this->inp->type,
				'id'   => $this->inp->id,
				'recordCount' => count($files),
				'imageList' => $files
			];

		} catch (PDOException $e) {

			$this->output = [
				'OK' => false,
				'errorType' => 'DataBase',
				'code' => 416,
				'message' => "Data Base Error!",
				'PDO' => $e,
			];
		}
		return $this;
	}

	/**
	 * result
	 *
	 * @return Data object
	 */
	public function result()
	{
		//$this->process();
		return ($this->output);
	}
}

// {
// 	"OK": true,
// 	"recordCount": 90,
// 	"fieldsCount": 23,
// 	"fieldTypes": {
// 			"Field": "id",
// 			"Type": "int",
// 			"Collation": null,
// 			"Null": "NO",
// 			"Key": "PRI",
// 			"Default": null,
// 			"Extra": "auto_increment",
// 			"Privileges": "select,insert,update,references",
// 			"Comment": ""
// 	}
// }