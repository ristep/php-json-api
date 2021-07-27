<?php

/**
 * Get
 */
class Get
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
		$fields = '*';
		$where = " WHERE 1 ";
		$table = $this->inp->type;

		if (isset($this->inp->attributes)) {
			$fields =	implode(',', $this->inp->attributes);
		}

		if (isset($this->inp->type)) {

			if (isset($this->inp->id)) {
				$sth = $this->conn->prepare("SELECT $fields FROM $table WHERE `id` = :recordID ;");
				$parArr['recordID'] = $this->inp->id;
			} else {
				if (isset($this->inp->key)) {
					$whereArr = array();
					foreach ($this->inp->key as $key => $val) {
						array_push($whereArr, "$key=:$key");
						$parArr[$key] = $val;
					}
					$where = "WHERE " . implode(' and ', $whereArr);
				}
				$sth = $this->conn->prepare("SELECT $fields FROM $table $where;");
			}

			try {
				$sth->execute($parArr);
				$result = $sth->fetch(PDO::FETCH_OBJ);

				$this->output = [];

				$data = $result;

				$this->output = [
					'OK' => true,
					'count' => $sth->rowCount(),
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
