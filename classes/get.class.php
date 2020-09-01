<?php

/**
 * get
 */
class get
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
		$pagination = '';
		$sorting = '';
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
				} else
						if (isset($this->inp->filter)) {
					if (is_string($this->inp->filter))
						$where = "WHERE " . $this->inp->filter; // Security to do: SQL injection preventing
				}

				if (isset($this->inp->sort)) {
					$sorting = " ORDER BY " . implode(',', $this->inp->sort);
				}

				if (isset($this->inp->page)) {
					$parArr['limit']  = (int) $this->inp->page->limit;
					$parArr['offset'] = (int) $this->inp->page->offset;
					$pagination = "LIMIT :limit OFFSET :offset";
					$this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
				}

				$sth = $this->conn->prepare("SELECT $fields FROM $table $where $sorting $pagination;");
			}

			try {
				$sth->execute($parArr);
				if (isset($this->inp->id))
					$result = $sth->fetch(PDO::FETCH_OBJ);
				else
					$result = $sth->fetchAll(PDO::FETCH_OBJ);

				$this->output = [];

				if (is_array($result))
					foreach ($result as $row) {
						$dt['type'] = $this->inp->type;
						if (isset($row->id))
							$dt['id'] = $row->id;
						unset($row->id);
						$dt['attributes'] = $row;
						array_push($data, $dt);
					}
				else {
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
	public function result()
	{
		//$this->process();
		return ($this->output);
	}
}
