<?php

/**
 * get
 */
class Meta
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
		$parArr[":table"] = $this->inp->type; 

    // SHOW COLUMNS FROM foods;
    // SHOW FULL COLUMNS FROM foods;
    // select count(1) from foods;

		try {
  
      $sth = $this->conn->prepare("SHOW FULL COLUMNS FROM ".$this->inp->type.";" );
			$sth->execute();
			$result = $sth->fetchAll(PDO::FETCH_OBJ);
      $fieldCount = $sth->rowCount();
      
      $sth = $this->conn->prepare("SELECT count(1) as recordCount FROM ".$this->inp->type.";" );
			$sth->execute();
			$count = $sth->fetchAll(PDO::FETCH_COLUMN);

			$this->output = [];

			foreach ($result as $row) {
					array_push($data, $row);
				}

			$this->output = [
				'OK' => true,
        'fieldsCount' => $fieldCount,
        'recordCount' => $count[0]
			];
			$this->output['data'] = $data;
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
