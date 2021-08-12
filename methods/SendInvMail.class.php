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
		$parArr = []; 
		$where = '';
    

		if (isset($this->inp->email)) {
				$where = "WHERE users.email='".$this->inp->email."'"; 
		}	
		// file_put_contents('inputDump.json',"SELECT count(1) as recordCount FROM users $where;" , FILE_APPEND);
		try {
  
      $sth = $this->conn->prepare("SELECT count(1) as recordCount FROM users $where;" );
			$sth->execute($parArr);
			$count = $sth->fetch(PDO::FETCH_COLUMN);
			if( $count > 0 ){
				$sth = $this->conn->prepare("UPDATE users SET remember_token = UUID(), token_ts = CURRENT_TIMESTAMP $where");
				$sth->execute($parArr);
			}

			$sth = $this->conn->prepare("SELECT remember_token as token FROM users $where;" );
			$sth->execute($parArr);
			$this->rs_token = $sth->fetch(PDO::FETCH_COLUMN);

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
