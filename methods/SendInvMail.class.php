<?php

/**
 * get
 */
class SendInvMail
{
	private $inp;
	private $output;
	private $conn;
  private $rs_token;
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
   
		if (isset($this->inp->email)) {
				$email = $this->inp->email;
				$username = $this->inp->username;
				$password = $this->inp->password;
		}else{
			$this->output["message"]  = "Attribute email must be specified!!";
			$this->output["errorType"] = "Missing email addres!";
			$this->output["code"] = 528;
			return $this;
		}

		// file_put_contents('inputDump.json',json_encode($parArr) , FILE_APPEND);
		try {
  
      $sth = $this->conn->prepare("SELECT count(1) as recordCount FROM `users` WHERE `email`=:email" );
			$sth->execute([ ':email' => $email ]);
			$count = $sth->fetch(PDO::FETCH_COLUMN);
// file_put_contents('inputDump.json', "Prvo OK" , FILE_APPEND);
			if( $count > 0 ){
				$sth = $this->conn->prepare("UPDATE users SET remember_token = UUID(), token_ts = CURRENT_TIMESTAMP WHERE users.email=:email");
				$sth->execute([ ':email' => $email ]);
			}else{
				$sth = $this->conn->prepare("INSERT INTO users( email, remember_token, token_ts, name, password ) values( :email , UUID(), CURRENT_TIMESTAMP, :username, :password)");
				$sth->execute([ 
					':email' => $email, 
					':username' => $username,
					':password' => $password
				]);
			}
// file_put_contents('inputDump.json', "Vtoro Tamam" , FILE_APPEND);

			$sth = $this->conn->prepare("SELECT remember_token as token FROM users WHERE users.email=:email" );
			$sth->execute([ ':email' => $this->inp->email ]);
			$this->rs_token = $sth->fetch(PDO::FETCH_COLUMN);

// file_put_contents('inputDump.json', "Treto OK" , FILE_APPEND);

			$this->output = [
				'OK' => true,
        'count' => $count,
				'email' => $this->inp->email,
				'username' => $this->inp->username,
				'password' => $this->inp->password,
				// 'token'    => $this->rs_token, // only for debuging
				'siteMessage' => $this->inp->siteMessage 
			];
		} catch (PDOException $e) {

			$this->output = [
				'OK' => false,
				'errorType' => 'DataBase',
				'code' => 416,
				'message' => "Data Base Error!",
				'sql' => $sth,
				'PDO' => $e
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
		$to      =  $this->output['email'];
		$subject = 'New user, or new password';
		$message = 'Click on this link for password reset or create new user: https://arso.us.to:3000/#/user_reset/'.$this->rs_token;
		$headers = 'From: webmaster@smanzy.cloud' . "\r\n" .
    'Reply-To: webmaster@smanzy.cloud' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

		$send = mail($to, $subject, $message, $headers);

		// file_put_contents('inputDump.json',  , FILE_APPEND);
	
		return ($this->output);
	}
}
