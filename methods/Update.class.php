<?php

/**
 * Update
 */
class Update
{
	private $inp;
	private $output;
	private $conn;

	/**
	 * Method __construct
	 *
	 * @param $inp  [input object from post data]
	 * @param $conn  [ PDO connection ]
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
	 * @return this class
	 */
	public function process()
	{
		if (isset($this->inp->id)) {

			$setArr = array();
			foreach ($this->inp->attributes as $key => $val)
				array_push($setArr, " $key=:$key");
			$sth = $this->conn->prepare("UPDATE " . $this->inp->type . " SET " . implode(',', $setArr) . " WHERE `id` = :recordID ;");

			try {
				$parArr = (array)($this->inp->attributes);
				$parArr['recordID'] = $this->inp->id;
				
				// file_put_contents('inputDump.json', implode(",",$parArr), FILE_APPEND); // uncomment for debugging

				$sth->execute($parArr);
				$this->output = [];
				$this->output = [
					'OK' => true,
					'count' => $sth->rowCount(),
					'message' => "Patched!",
				];
			} catch (PDOException $e) {

				$this->output = [
					'OK' => false,
					'errorType' => 'DataBase',
					'code' => 416,
					'message' => "Data Base Error!",
					'PDO' => $e
				];
			}
		} else {
			$this->output["message"]  = "Attribute ID must be specified!!";
			$this->output["errorType"] = "Missing key parameter in request!";
			$this->output["code"] = 508;
		}
		return $this;
	}

	/**
	 * Method result
	 *
	 * @return [resulting object]
	 */
	public function result()
	{
		return ($this->output);
	}
}

