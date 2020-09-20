<?php

/**
 * get
 */
class MetaList
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
    $whereArr = [];
    $filterArr = [];

		if (isset($this->inp->attributes)) {
			$fields =	implode(',', $this->inp->attributes);
		}

		if (isset($this->inp->key)) {
			foreach ($this->inp->key as $key => $val) {
				array_push($whereArr, "$key=:$key");
				$parArr[$key] = $val;
			}
			$where = "WHERE " . implode(' and ', $whereArr);
		} elseif (isset($this->inp->filter)) {
			if (is_string($this->inp->filter)){
				$where = "WHERE " . $this->inp->filter; 
			}elseif(is_object($this->inp->filter)){ // Security to do: SQL injection preventing, safe escaping $this->inp->filter->template
				$where = 	"WHERE " . $this->inp->filter->template;
				foreach($this->inp->filter->params as $key => $val){ 
          $parArr[$key] = $val;
          $filterArr[$key] = $val;
        }
			}	
		} elseif (isset($this->inp->search)){
				// to be done some time in the future if needed 
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

		try {

      $sth = $this->conn->prepare("SHOW FULL COLUMNS FROM $table;" );
			$sth->execute();
			$fieldTypes = $sth->fetch(PDO::FETCH_OBJ);
      $fieldCount = $sth->rowCount();

      $sth = $this->conn->prepare("SELECT count(1) as recordCount FROM $table $where;" );
      // echo "SELECT count(1) as recordCount FROM $table $where;";
      // print_r($filterArr);
			$sth->execute($filterArr);
			$recordCount = $sth->fetch(PDO::FETCH_COLUMN);

			$sth = $this->conn->prepare("SELECT $fields FROM $table $where $sorting $pagination;");
			$sth->execute($parArr);
			$result = $sth->fetchAll(PDO::FETCH_OBJ);
	
			$this->output = [];

			foreach ($result as $row) {
					array_push($data, $row);
				}

			$this->output = [
				'OK' => true,
        'count' => count($data),
        'recordCount' => $recordCount,
        'fieldCount' => $fieldCount,
        'fieldTypes' => $fieldTypes
			];
			if (isset($this->inp->key)) 
				$this->output['key'] = $this->inp->key;
			if (isset($this->inp->filter)) 
				$this->output['filter'] = $this->inp->filter;
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
