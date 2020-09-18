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
		$parArr = []; 
		$where = '';
		
		if (isset($this->inp->filter)) {
			if (is_string($this->inp->filter)){
				$where = "WHERE " . $this->inp->filter; 
			}elseif(is_object($this->inp->filter)){ // Security to do: SQL injection preventing, safe escaping $this->inp->filter->template
				$where = 	"WHERE " . $this->inp->filter->template;
				foreach($this->inp->filter->params as $key => $val) 
					$parArr[$key] = $val;
			}	
		}	

		try {
  
      $sth = $this->conn->prepare("SHOW FULL COLUMNS FROM ".$this->inp->type.";" );
			$sth->execute();
			$result = $sth->fetch(PDO::FETCH_OBJ);
      $fieldCount = $sth->rowCount();

			$sth = $this->conn->prepare("SELECT count(1) as recordCount FROM ".$this->inp->type." $where;" );
			$sth->execute($parArr);
			$count = $sth->fetch(PDO::FETCH_COLUMN);

			$this->output = [];

			$this->output = [
				'OK' => true,
        'fieldsCount' => $fieldCount,
        'recordCount' => $count
			];
			$this->output['dataTypes'] = $result;
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
