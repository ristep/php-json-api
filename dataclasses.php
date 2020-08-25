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
				$sth = $this->conn->prepare("SELECT $fields FROM $table WHERE `id` = :recordID ;");
				$sth->bindParam('recordID', $this->inp->id);
			}
			else{
					if(isset($this->inp->key)){
						$whereArr = array();
						foreach( $this->inp->key as $key => $val )
							array_push($whereArr, "$key='$val'");
						$where = "WHERE " . implode(' and ', $whereArr); // Security to do: SQL injection preventing
						// print($where);
					}else
						if(isset($this->inp->filter)){
							if(is_string($this->inp->filter))
								$where = "WHERE ".$this->inp->filter; // Security to do: SQL injection preventing
					}

					if(isset($this->inp->sort)){
						$sorting = " ORDER BY ".implode(',',$this->inp->sort);
					}

					if( isset($this->inp->page) ){
						$lim = (int) $this->inp->page->limit;
						$off = (int) $this->inp->page->offset;
						$pagination = "LIMIT $lim OFFSET $off;";
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
			
			$atrArr = array();
			$parArr = array();

			foreach( $this->inp->attributes as $key => $val ){
				array_push($atrArr, $key );
				array_push($parArr,':'.$key );
			}	
			$sth = $this->conn->prepare("INSERT INTO ".$this->inp->type."(".implode(',', $atrArr).") VALUES(".implode( ',', $parArr).");");
			
			try{

				$sth->execute((array)($this->inp->attributes));
				$temp = 
				$this->output = [];
				$this->output['meta'] = [
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
		if(isset($this->inp->id)){
			
			$setArr = array();
			foreach( $this->inp->attributes as $key => $val )
				array_push($setArr, " $key=:$key");
			$sth = $this->conn->prepare("UPDATE ".$this->inp->type." SET ".implode(',', $setArr)." WHERE `id` = :recordID ;");
			try{
				$parArr = (array)($this->inp->attributes);
				$parArr['recordID'] = $this->inp->id;
				$sth->execute($parArr);
				$this->output = [];
				$this->output['meta'] = [
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
		}
		else{
			$this->output["message"]  = "Attribute ID must be specified!!";
			$this->output["errorType"] = "Missing key parameter in request!";
			$this->output["code"] = 508;
		}	
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
		if(isset($this->inp->id)){
			
			$setArr = array();
			$sth = $this->conn->prepare("DELETE FROM ".$this->inp->type." WHERE `id` = :recordID ;");
			try{
				$parArr['recordID'] = $this->inp->id;
				$sth->execute($parArr);
				$this->output = [];
				$this->output['meta'] = [
					'OK' => true,
					'count' => $sth->rowCount(),
					'message' => $sth->rowCount()." deleted records!"
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
		}
		else{
			$this->output["message"]  = "Attribute ID must be specified!!";
			$this->output["errorType"] = "Missing key parameter in request!";
			$this->output["code"] = 508;
		}	
		return $this;	}
	
	function result(){
		//$this->process();
		return ($this->output);
	}
	
}


?>
