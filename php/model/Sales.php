<?php

/**
 * Class   :  Sales
 * Author  :  Jerry Sihombing
 * Created :  2015-02-23
 * Desc    :  Data handler for Sales
 */

require_once(dirname(__FILE__) . "/../util/ConfigReader.php");

class Sales {
	private $_mysqli;
	private $_host;
	private $_dbname;
	private $_user;
	private $_pass;
	
	private $_id;
	private $_transDate;
	private $_transDateF;
	private $_brandName;
	private $_articleType;
	private $_quantity;
	private $_amount;
	private $_storeInit;
	
	private $_createdBy;
	private $_createdDate;
	private $_lastUser;
    private $_lastUpdate;
	
	private $_error;

	public function __construct() {
		$CR = new ConfigReader("db.conf.php");

		$this->_host = $CR->get("#host");
		$this->_dbname = $CR->get("#dbname");
		$this->_user = $CR->get("#user");
		$this->_pass = $CR->get("#pass");
	}
	
	private function makeEscapedString() {
		# -- note: mysqli::real_escape_string needs a valid connection opened -- #
		# -- so, call this function after makeConnection() called -- #
		
		try {
			$this->_brandName = $this->_mysqli->real_escape_string($this->_brandName);
			$this->_storeInit = $this->_mysqli->real_escape_string($this->_storeInit);
		}
		catch (Exception $e) {
			# do nothing
		}
		
	}	
	
	public function findAmountByBrandMap($brandName, $mapId) {
		$this->makeConnection();
		
		$sql = "SELECT sales_find_amount_by_brand_map('$brandName', $mapId)";	
		
		$res = $this->_mysqli->query($sql);
		$row = $res->fetch_row(); 
		$res->close();
		$this->closeConnection();
		
		return $row[0];
	}
	
	public function findQuantityByBrandMap($brandName, $mapId) {
		$this->makeConnection();
		
		$sql = "SELECT sales_find_quantity_by_brand_map('$brandName', $mapId)";	
			
		$res = $this->_mysqli->query($sql);
		$row = $res->fetch_row(); 
		$res->close();
		$this->closeConnection();
		
		return $row[0];
	}
	
	public function findAmountByBrandTypeMap($brandName, $articleType, $mapId) {
		$this->makeConnection();
		
		$sql = "SELECT sales_find_amount_by_brand_type_map('$brandName', $articleType, $mapId)";	
		
		$res = $this->_mysqli->query($sql);
		$row = $res->fetch_row(); 
		$res->close();
		$this->closeConnection();
		
		return $row[0];
	}
	
	public function findQuantityByBrandTypeMap($brandName, $articleType, $mapId) {
		$this->makeConnection();
		
		$sql = "SELECT sales_find_quantity_by_brand_type_map('$brandName', $articleType, $mapId)";	
			
		$res = $this->_mysqli->query($sql);
		$row = $res->fetch_row(); 
		$res->close();
		$this->closeConnection();
		
		return $row[0];
	}
	
	public function loadAll() {				
		$this->makeConnection();
		
		$sql = "CALL sales_load_all()";
		
		$res = $this->_mysqli->query($sql);
		
		$data = array();
		while ($row = $res->fetch_assoc()) {
			array_push($data, $row);
		}
		
		$res->close();
		$this->closeConnection();
		
		return $data;
	}
	
	public function load($id) {				
		$this->makeConnection();
		
		$sql = "CALL sales_load($id)";
		
		$res = $this->_mysqli->query($sql);

		if ($row = $res->fetch_assoc()) {
			$this->_id = $id;
			$this->_transDate = $row["trans_date"];
			$this->_transDateF = $row["trans_date_f"];
			$this->_brandName = $row["brand_name"];
			$this->_articleType = $row["article_type"];
			$this->_quantity = $row["quantity"];
			$this->_amount = $row["amount"];
			$this->_storeInit = $row["store_init"];
			$this->_createdBy = $row["created_by"];
			$this->_createdDate = $row["created_date"];
			$this->_lastUser = $row["last_user"];
			$this->_lastUpdate = $row["last_update"];
		}
		
		$res->close();
		$this->closeConnection();				
	}

	public function remove($id) {
		$this->makeConnection();
		
		$sql = "CALL sales_remove($id)";
		
		$ret = $this->_mysqli->query($sql);
		$this->generateErrorMessage($ret);
		$this->closeConnection();

		return $ret;
	}
	
	public function update($id) {
		$this->makeConnection();
		$this->makeEscapedString();
		
		$sql = "CALL sales_update(" .
					$id . ", '" .
					$this->_transDate . "', '" .
					$this->_brandName . "', '" .
					$this->_articleType . "', '" .
					$this->_quantity . "', '" .
					$this->_amount . "', '" .
					$this->_storeInit . "', '" .
					$this->_lastUser . "', '" .
					$this->_lastUpdate . 
				"')";
		
		$ret = $this->_mysqli->query($sql);
		$this->generateErrorMessage($ret);
		$this->closeConnection();

		return $ret;
	}
	
	public function addNew() {
		$this->makeConnection();		
		$this->makeEscapedString();
		
		$sql = "CALL sales_add('" . 
					$this->_transDate . "', '" .
					$this->_brandName . "', '" .
					$this->_articleType . "', '" .
					$this->_quantity . "', '" .
					$this->_amount . "', '" .
					$this->_storeInit . "', '" .
					$this->_createdBy . "', '" .
					$this->_createdDate . 
				"')";
		
		$ret = $this->_mysqli->query($sql);
		$this->generateErrorMessage($ret);
		$this->closeConnection();

		return $ret;
	}
	
	public function isExist($transDate, $brandName, $articleType, $storeInit) {
		$this->makeConnection();
		
		$sql = "SELECT sales_count('$transDate', '$brandName', $articleType, '$storeInit')";	
			
		$res = $this->_mysqli->query($sql);
		$row = $res->fetch_row(); 
		$res->close();
		$this->closeConnection();
		
		return ($row[0] > 0 ? true : false);
	}
	
	private function generateErrorMessage($success) {
		if ($success) {
			$this->_error = "";
		}
		else {
			$this->_error = "Error (" . $this->_mysqli->errno . "): " . $this->_mysqli->error . ".";
		}
	}
	
	private function closeConnection() {
		$this->_mysqli->close();
	}
		
	private function makeConnection() {
		$this->_mysqli = new mysqli("$this->_host", "$this->_user", "$this->_pass", "$this->_dbname");

		/* check connection */
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}

	}

	# --- accessor functions --- #

	public function getId() {
		return $this->_id;
	}	
	public function getTransDate() {
		return $this->_transDate;	
	}
	public function getTransDateF() {
		return $this->_transDateF;	
	}
	public function getBrandName() {
		return $this->_brandName;	
	}
	public function getArticleType() {
		return $this->_articleType;	
	}
	public function getQuantity() {
		return $this->_quantity;	
	}
	public function getAmount() {
		return $this->_amount;	
	}
	public function getStoreInit() {
		return $this->_storeInit;	
	}
	public function getCreatedDate() {
		return $this->_createdDate;
	}
	public function getCreatedBy() {
		return $this->_createdBy;
	}
	public function getLastUser() {
		return $this->_lastUser;
	}
	public function getLastUpdate() {
		return $this->_lastUpdate;
	}
	public function getError() {
		return $this->_error;
	}

	public function setId($v) {
		$this->_id = $v;
	}	
	public function setTransDate($v) {
		$this->_transDate = $v;	
	}
	public function setTransDateF($v) {
		$this->_transDateF = $v;	
	}
	public function setBrandName($v) {
		$this->_brandName = $v;	
	}
	public function setArticleType($v) {
		$this->_articleType = $v;	
	}
	public function setQuantity($v) {
		$this->_quantity = $v;	
	}
	public function setAmount($v) {
		$this->_amount = $v;	
	}
	public function setStoreInit($v) {
		$this->_storeInit = $v;	
	}
	public function setCreatedDate($v) {
		$this->_createdDate = $v;
	}
	public function setCreatedBy($v) {
		$this->_createdBy = $v;
	}
	public function setLastUser($v) {
		$this->_lastUser = $v;
	}
	public function setLastUpdate($v) {
		$this->_lastUpdate = $v;
	}
	public function setError($v) {
		$this->_error = $v;
	}

	# --- end of accessor --- #

}

?>