<?php
require "./vendor/autoload.php";
use \Firebase\JWT\JWT;

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
