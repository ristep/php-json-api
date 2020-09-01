<?php

/**
 * delete
 */
class delete
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
	 * @return [this class]
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

	public function process()
	{
		if (isset($this->inp->id)) {

			$parArr = array();
			$sth = $this->conn->prepare("DELETE FROM " . $this->inp->type . " WHERE `id` = :recordID ;");
			try {
				$parArr['recordID'] = $this->inp->id;
				$sth->execute($parArr);
				$this->output = [];
				$this->output['meta'] = [
					'OK' => true,
					'count' => $sth->rowCount(),
					'message' => $sth->rowCount() . " deleted records!"
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
	 * @return [result object]
	 */
	public function result()
	{
		return ($this->output);
	}
}
