<?php

/**
 * Create an resource
 */
class Create
{
	private $inp;
	private $output;
	private $conn;

	/**
	 * Method __construct
	 *
	 * @param $inp  // Input object from post data
	 * @param $conn // Conection to be used
	 *
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
	 * Method process
	 *
	 * @return {this}
	 */
	public function process()
	{

		$atrArr = array();
		$parArr = array();

		foreach ($this->inp->attributes as $key => $val) {
			array_push($atrArr, $key);
			array_push($parArr, ':' . $key);
		}
		$sth = $this->conn->prepare("INSERT INTO " . $this->inp->type . "(" . implode(',', $atrArr) . ") VALUES(" . implode(',', $parArr) . ");");
		// file_put_contents('inputDump.json', implode(';',(array)($this->inp->attributes)), FILE_APPEND); // uncomment for debugging
		try {

			$sth->execute((array)($this->inp->attributes));
			$temp =
				$this->output = [];
			$this->output = [
				'OK' => true,
				'count' => $sth->rowCount(),
				'message' => "Inserted!",
				'recordID' => $this->conn->lastInsertId()
			];
		} catch (PDOException $e) {

			$this->output = [
				'OK' => false,
				'errorType' => 'DataBase',
				'code' => 416,
				'message' => "Data Base Error!",
				'PDO' => $e,
				"userData" => false
			];
		}
		return $this;
	}

	/**
	 * Method result
	 *
	 * @return [output object]
	 */
	public function result()
	{
		return ($this->output);
	}
}
