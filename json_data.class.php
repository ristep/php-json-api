<?php

require "./vendor/autoload.php";
use \Firebase\JWT\JWT;

/**
 * get
 */
class get {
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
	function __construct($inp, $conn) {
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
	public function process(){
		$data = [];
		$parArr = [];
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
				$parArr['recordID']= $this->inp->id;
			}
			else{
					if(isset($this->inp->key)){
						$whereArr = array();
						foreach( $this->inp->key as $key => $val ){
							array_push($whereArr, "$key=:$key");
							$parArr[$key] = $val;
						}
						$where = "WHERE " . implode(' and ', $whereArr); 
					}else
						if(isset($this->inp->filter)){
							if(is_string($this->inp->filter))
								$where = "WHERE ".$this->inp->filter; // Security to do: SQL injection preventing
					}

					if(isset($this->inp->sort)){
						$sorting = " ORDER BY ".implode(',',$this->inp->sort);
					}

					if( isset($this->inp->page) ){
						$parArr['limit']  = (int) $this->inp->page->limit;
						$parArr['offset'] = (int) $this->inp->page->offset;
						$pagination = "LIMIT :limit OFFSET :offset";
						$this->conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
					}

					$sth = $this->conn->prepare("SELECT $fields FROM $table $where $sorting $pagination;");
			}

		try{
			$sth->execute($parArr);
			if(isset($this->inp->id))
				$result = $sth->fetch(PDO::FETCH_OBJ);
			else
				$result = $sth->fetchAll(PDO::FETCH_OBJ);
			
			$this->output = [];
			
			if(is_array($result))
				foreach( $result as $row ){
					$dt['type'] = $this->inp->type;
					if(isset($row->id))
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
		
	/**
	 * result
	 *
	 * @return Data object
	 */
	public function result(){
		//$this->process();
		return ($this->output);
	}

}

/**
 * post
 */
class post {
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
	function __construct($inp, $conn) {
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
	public function process(){
			
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
	
	/**
	 * Method result
	 *
	 * @return [output object]
	 */
	public function result(){
		return ($this->output);
	}
	
}

/**
 * patch
 */
class patch {
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
	function __construct($inp, $conn) {
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
	public function process(){
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
	
	/**
	 * Method result
	 *
	 * @return [resulting object]
	 */
	public function result(){
		return ($this->output);
	}
	
}

/**
 * delete
 */
class delete {

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
	function __construct($inp, $conn) {
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

	public function process(){
		if(isset($this->inp->id)){
			
			$parArr = array();
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
		return $this;	
	}
			
	/**
	 * Method result
	 *
	 * @return [result object]
	 */
	public function result(){
		return ($this->output);
	}
	
}

/**
 * getToken 
 * request for user token
 */
class getToken {
	private $inp;
	private $output;
	private $conn;

	/**
	 * Method __construct
	 *
	 * @param $inp  [input object from post data]
	 * @param $conn  [ PDO connection
	 *
	 * @return void
	 */
	function __construct($inp, $conn) {
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
	 * @return [this object]
	 */
	public function process(){
		if(isset($this->inp->username) && isset($this->inp->password) ){
			
			$parArr = [];
			$sth = $this->conn->prepare("select id,name,email,first_name,second_name,role FROM `users`  WHERE name = :username and password = :password;");
			try{
				$parArr['password'] = $this->inp->password;
				$parArr['username'] = $this->inp->username;
				$sth->execute($parArr);
				$result = $sth->fetch(PDO::FETCH_OBJ);

				if($sth->rowCount()==1){
					$token = array(
						"id" => $result->id,
						"name" => $result->name,
						"email" => $result->email,
						"first_name" => $result->first_name,
						"second_name" => $result->second_name,
						// "address" => $result->address,
						// "state" => $result->state,
						// "place" => $result->place,
						"role" => $result->role,
						// 'time' => date("ymdHms"),
						"jti" => 'deca-meca-'.date("ymdhms").'-jade-'.mt_rand().'-'
					);
					
					$jwt = JWT::encode($token, md5("FMyNTYiLCJ0eX5".date("ymd")));
					// sleep(2);

					$token['jti'] = date("y-m-d H:m:s");
					$token['auToken'] = $jwt;

					$this->output = [];
					$this->output['meta'] = [
						'OK' => true,
						'error' => false,
						'count' => $sth->rowCount(),
						'message' => "User record found! User password OK! UserToken generated!",
					];
					$this->output['data'] = $token;
				}else{
					$this->output = [];
					$this->output['meta'] = [
						'OK' => false,
						'error' => true,
						'errorCode' => 401,
						'count' => 0,
						'message' => "Wrong username or password!!! Cant get UserToken!",
					];
					$this->output['data'] = false;
				}

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
			$this->output["message"]  = "Attribute password and username must be specified!!";
			$this->output["errorType"] = "Missing key parameter in request!";
			$this->output["code"] = 508;
		}	
		return $this;	
	}
		
	/**
	 * Method result
	 *
	 * @return [token data object]
	 */
	public function result(){
		return ($this->output);
	}
}

?>