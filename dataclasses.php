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
			'errorType' => 'Some undefined server ERROR!',
			'code' => 500,
			'message' => "Internal RPC server error!"
		];
	}
	
	function process(){
		$data = [];
		$attrib = '*';
		$pagination = '';
		$sorting = '';
		
		if(isset($this->inp->attributes)){
			if( in_array( 'id', $this->inp->attributes ) ) 
				$attrib = implode(',',$this->inp->attributes);
			else
				$attrib =	'id,'.implode(',',$this->inp->attributes);
		}	

		if(isset($this->inp->type)){ 
			if(isset($this->inp->id)){ 
				$sth = $this->conn->prepare("SELECT $attrib FROM ".$this->inp->type." WHERE `id` = :userId");
				$sth->bindParam('userId', $this->inp->id);
			}	
			else{
					if(isset($this->inp->sort)){
						$sorting = " ORDER BY ".implode(',',$this->inp->sort);
					}
					if( isset($this->inp->page) ){
						$pagination = " LIMIT ".$this->inp->page->limit." OFFSET ".$this->inp->page->offset;
					}
					$sth = $this->conn->prepare("SELECT $attrib FROM ".$this->inp->type." WHERE 1 ".$sorting." ".$pagination  );
			}
			
		try{
			$sth->execute();
			if(isset($this->inp->id))
				$result = $sth->fetch(PDO::FETCH_OBJ);
			else
				$result = $sth->fetchAll(PDO::FETCH_OBJ);
			$sth2 = $this->conn->prepare('SHOW FIELDS FROM users');
			$sth2->execute();
			
			$this->output = [];
			
			foreach( $result as $row ){
				$dt['type'] = $this->inp->type;
				$dt['id'] = $row->id;
				$dt['attributes'] = $row;
				array_push ( $data, $dt );
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

?>
