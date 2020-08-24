<?php

class get {
	private $inp;
	private $output;
	private $conn;
	private $tokenData;

	function __construct($inp, $conn, $tokenData) {
		$this->inp = $inp;
		$this->conn = $conn;
		$this->tokenData = $tokenData;
		$this->output = [
			'OK' => false,
			'error' => true,
			'errorType' => 'Undefined server ERROR!',
			'code' => 500,
			'message' => "Internal RPC server error!"
		];
	}
	
	function process(){
		$data = [];
		$fields = '*';
		$pagination = '';
		$sorting = '';
		$where = " WHERE 1 ";
		$table = $this->inp->type;

		if(isset($this->inp->attributes)){
			$fields =	implode(',',$this->inp->attributes);
		}	

		if(isset($this->inp->type)){ 
	
			if(isset($this->inp->id)){
				$sth = $this->conn->prepare("SELECT $fields FROM $table WHERE `id` = :userId ;");
				$sth->bindParam('userId', $this->inp->id);
			}
			else{
					if(isset($this->inp->key)){
						$whereArr = array();
						foreach( $this->inp->key as $key => $val )
							array_push($whereArr, "$key='$val'");
						$where = "WHERE " . implode(' and ', $whereArr);
						// print($where);
					}else
						if(isset($this->inp->filter)){
							if(is_string($this->inp->filter))
								$where = "WHERE ".$this->inp->filter;
					}

					if(isset($this->inp->sort)){
						$sorting = " ORDER BY ".implode(',',$this->inp->sort);
					}

					if( isset($this->inp->page) ){
						$pagination = " LIMIT ".$this->inp->page->limit." OFFSET ".$this->inp->page->offset;
					}
					$sth = $this->conn->prepare("SELECT id, $fields FROM $table $where $sorting $pagination;");
			}

		try{
			$sth->execute();
			if(isset($this->inp->id))
				$result = $sth->fetch(PDO::FETCH_OBJ);
			else
				$result = $sth->fetchAll(PDO::FETCH_OBJ);
			
			$this->output = [];
			
			if(is_array($result))
				foreach( $result as $row ){
					$dt['type'] = $this->inp->type;
					$dt['id'] = $row->id;
					unset($row->id);
					$dt['attributes'] = $row;
					array_push ( $data, $dt );
				}
			else{
				$data['type'] = $this->inp->type;
				$data['id'] = $this->inp->id;
				$data['attributes'] = $result;
			}

			$this->output['meta'] = [
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
					"userData" => false
				];

			}		
		}
		return $this;
	}
	
	function result(){
		//$this->process();
		return ($this->output);
	}

// Methods	

}

class post {
	private $inp;
	private $output;
	private $conn;
	private $tokenData;

	function __construct($inp, $conn, $tokenData) {
		$this->inp = $inp;
		$this->conn = $conn;
		$this->tokenData = $tokenData;
		$this->output = [
			'OK' => false,
			'error' => true,
			'errorType' => 'Undefined server ERROR!',
			'code' => 500,
			'message' => "Internal RPC server error!"
		];
	}

	function process(){
		$this->output = $this->inp;
		return $this;
	}
	
	function result(){
		//$this->process();
		return ($this->output);
	}
	
}

class patch {
	private $inp;
	private $output;
	private $conn;
	private $tokenData;

	function __construct($inp, $conn, $tokenData) {
		$this->inp = $inp;
		$this->conn = $conn;
		$this->tokenData = $tokenData;
		$this->output = [
			'OK' => false,
			'error' => true,
			'errorType' => 'Undefined server ERROR!',
			'code' => 500,
			'message' => "Internal RPC server error!"
		];
	}

	function process(){
		$this->output = $this->inp;
		return $this;
	}
	
	function result(){
		//$this->process();
		return ($this->output);
	}
	
}

class delete {
	private $inp;
	private $output;
	private $conn;
	private $tokenData;

	function __construct($inp, $conn, $tokenData) {
		$this->inp = $inp;
		$this->conn = $conn;
		$this->tokenData = $tokenData;
		$this->output = [
			'OK' => false,
			'error' => true,
			'errorType' => 'Undefined server ERROR!',
			'code' => 500,
			'message' => "Internal RPC server error!"
		];
	}

	function process(){
		$this->output = $this->inp;
		return $this;
	}
	
	function result(){
		//$this->process();
		return ($this->output);
	}
	
}


?>
