<?php
class Model implements JsonSerializable {

	protected $_model;
	public $db;

	protected $id;
	protected $table_name;
	private $db_keys = array();
	private $loaded = false;


	function __construct() {
		global $globalDB;
		$this->db = $globalDB;
			
		$this->table_name = $this->getTableName(get_class($this));
	}
	
	public function setDB($dbHandle) {
		$this->db = $dbHandle;
	}

	public function getTableName($classname) {
		$tbl = '';
		foreach(str_split($classname) as $char) {
			if ($char == mb_strtoupper($char, 'UTF-8'))
				$tbl .= '_';
			$tbl .= $char;

		}
		return strtolower(substr($tbl, 1));
	}

	public function jsonSerialize() {
		$values = array(
			'id' => $this->id
		);

		foreach (get_object_vars($this) as $key => $value) {
			if ($key != 'db' && $key != 'table_name' && $key != 'db_keys' && $key != 'loaded' && $key != '_model') {
				$values[$key] = $value;
			}
		}

		return $values;
	}
	public function loadself($key, $by) {
		$stmt = $this->db->prepare("SELECT * FROM ".$this->table_name." WHERE `".$by."` = ?");
		//echo "SELECT * FROM ".$this->table_name." WHERE `".$by."` = ".$key;
		$stmt->execute(array($key));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($res > 0) {

			foreach ($res as $property=>$value) {
				$this->$property = $value;
				$this->db_keys[] = $property;
			}

			$this->loaded = true;
			return true;
		} else {
			$this->loaded = false;
			return false;
		}
	}
	public function load($key, $by) {
		$stmt = $this->db->prepare("SELECT * FROM ".$this->table_name." WHERE `".$by."` = ?");
		//echo "SELECT * FROM ".$this->table_name." WHERE `".$by."` = ".$key;
		$stmt->execute(array($key));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($res > 0) {

			foreach ($res as $property=>$value) {
				$this->$property = $value;
				$this->db_keys[] = $property;
			}

			$this->loaded = true;
			return true;
		} else {
			$this->loaded = false;
			return false;
		}
	}
	public function loadRealInvoiceId() {
		$stmt_invoiceid1 = $this->db->prepare("SELECT id FROM exhibitor_invoice as id WHERE fair = ? order by id desc limit 1");
		$stmt_invoiceid1->execute(array($this->id));
		$res = $stmt_invoiceid1->fetch(PDO::FETCH_ASSOC);
		$invoice_id1 = $res['id'];
		$stmt_invoiceid2 = $this->db->prepare("SELECT id FROM exhibitor_invoice_history as id WHERE fair = ? order by id desc limit 1");
		$stmt_invoiceid2->execute(array($this->id));
		$res2 = $stmt_invoiceid2->fetch(PDO::FETCH_ASSOC);
		$invoice_id_history = $res2['id'];

		if ($invoice_id1 > $invoice_id_history) {
			$invoice_id = $invoice_id1;
		} else if ($invoice_id1 < $invoice_id_history) {
			$invoice_id = $invoice_id_history;
		} else {
			$invoice_id = 0;
		}
		return $invoice_id;
	}
	public function loadRealCreditInvoiceId() {
		$stmt = $this->db->prepare("SELECT cid FROM exhibitor_invoice_credited as cid WHERE fair = ? order by cid desc limit 1");
		$stmt->execute(array($this->id));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($res['cid'] > 0) {
			return $res['cid'];
		} else {
			return 0;
		}
	}
	public function loadmsg($key, $by) {
		$stmt = $this->db->prepare("SELECT `id`, `arranger_message` FROM ".$this->table_name." WHERE `".$by."` = ?");
		//echo "SELECT * FROM ".$this->table_name." WHERE `".$by."` = ".$key;
		$stmt->execute(array($key));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($res > 0) {

			foreach ($res as $property=>$value) {
				$this->$property = $value;
				$this->db_keys[] = $property;
			}

			$this->loaded = true;
			return true;
		} else {
			$this->loaded = false;
			return false;
		}
	}

	public function loaddelmsg($key, $by) {
		$stmt = $this->db->prepare("SELECT `id`, `deletion_message` FROM ".$this->table_name." WHERE `".$by."` = ?");
		//echo "SELECT * FROM ".$this->table_name." WHERE `".$by."` = ".$key;
		$stmt->execute(array($key));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($res > 0) {

			foreach ($res as $property=>$value) {
				$this->$property = $value;
				$this->db_keys[] = $property;
			}

			$this->loaded = true;
			return true;
		} else {
			$this->loaded = false;
			return false;
		}
	}

	public function loadfair($key, $by) {
		$stmt = $this->db->prepare("SELECT `fair` FROM ".$this->table_name." WHERE `".$by."` = ?");
		$stmt->execute(array($key));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($res > 0) {

			foreach ($res as $property=>$value) {
				$this->$property = $value;
				$this->db_keys[] = $property;
			}

			$this->loaded = true;
			return true;
		} else {
			$this->loaded = false;
			return false;
		}
	}

	public function loadid($key, $by) {
		$stmt = $this->db->prepare("SELECT `id` FROM ".$this->table_name." WHERE `".$by."` = ?");
		//echo "SELECT * FROM ".$this->table_name." WHERE `".$by."` = ".$key;
		$stmt->execute(array($key));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($res > 0) {

			foreach ($res as $property=>$value) {
				$this->$property = $value;
				$this->db_keys[] = $property;
			}

			$this->loaded = true;
			return true;
		} else {
			$this->loaded = false;
			return false;
		}
	}

	public function loadsafe($key, $by, $key2, $by2) {
		$stmt = $this->db->prepare("SELECT * FROM ".$this->table_name." WHERE `".$by."` = ? AND `".$by2."` = ?");
		$stmt->execute(array($key, $key2));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($res > 0) {

			foreach ($res as $property=>$value) {
				$this->$property = $value;
				$this->db_keys[] = $property;
			}

			$this->loaded = true;
			return true;
		} else {
			$this->loaded = false;
			return false;
		}
	}

	public function load2($key, $by) {
		$stmt = $this->db->prepare("SELECT * FROM ".$this->table_name." WHERE `".$by."` = ?");
		$stmt->execute(array($key));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if ($res > 0) {

			foreach ($res as $property=>$value) {
				$this->$property = $value;
				$this->db_keys[] = $property;
			}

			$this->loaded = true;
			return true;
		} else {
			$this->loaded = false;
			return false;
		}
	}
	protected function fetchExternal($class, $attribute, $joinedOn, $key, $order_by = null, $order_dir = null) {

		$query = "SELECT id FROM ".$this->getTableName($class)." WHERE `".$joinedOn."` = ?";
		if ($order_by !== null) {
			$query .= " ORDER BY `" . $order_by . "`";

			if ($order_dir !== null && ($order_dir == 'ASC' || $order_dir == 'DESC')) {
				$query .= " " . $order_dir;
			}
		}

		$stmt = $this->db->prepare($query);

		$stmt->execute(array($key));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$ret = array();

		if ($result > 0) {
			foreach($result as $res) {
				$obj = new $class;
				$obj->load($res['id'], 'id');
				$ret[] = $obj;
			}
		}
		$this->$attribute = $ret;

	}
	protected function fetchExternalFair($class, $attribute, $joinedOn, $key, $order_by = null, $order_dir = null) {

		$query = "SELECT `fair` FROM ".$this->getTableName($class)." WHERE `".$joinedOn."` = ?";
		if ($order_by !== null) {
			$query .= " ORDER BY `" . $order_by . "`";

			if ($order_dir !== null && ($order_dir == 'ASC' || $order_dir == 'DESC')) {
				$query .= " " . $order_dir;
			}
		}

		$stmt = $this->db->prepare($query);

		$stmt->execute(array($key));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$ret = array();

		if ($result > 0) {
			foreach($result as $res) {
				$obj = new $class;
				$obj->loadself($res['fair'], 'id');
				$ret[] = $obj;
			}
		}
		$this->$attribute = $ret;

	}
	protected function fetchExternalGroup($class, $attribute, $joinedOn, $key, $order_by = null, $order_dir = null) {

		$query = "SELECT `group` FROM ".$this->getTableName($class)." WHERE `".$joinedOn."` = ?";
		if ($order_by !== null) {
			$query .= " ORDER BY `" . $order_by . "`";

			if ($order_dir !== null && ($order_dir == 'ASC' || $order_dir == 'DESC')) {
				$query .= " " . $order_dir;
			}
		}

		$stmt = $this->db->prepare($query);

		$stmt->execute(array($key));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$ret = array();

		if ($result > 0) {
			foreach($result as $res) {
				$obj = new $class;
				$obj->loadself($res['group'], 'id');
				$ret[] = $obj;
			}
		}
		$this->$attribute = $ret;

	}
	protected function fetchExternalSimple($class, $attribute, $joinedOn, $key, $order_by = null, $order_dir = null) {

		$query = "SELECT id FROM ".$this->getTableName($class)." WHERE `".$joinedOn."` = ?";
		if ($order_by !== null) {
			$query .= " ORDER BY `" . $order_by . "`";

			if ($order_dir !== null && ($order_dir == 'ASC' || $order_dir == 'DESC')) {
				$query .= " " . $order_dir;
			}
		}

		$stmt = $this->db->prepare($query);

		$stmt->execute(array($key));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$ret = array();

		if ($result > 0) {
			foreach($result as $res) {
				$obj = new $class;
				$obj->loadself($res['id'], 'id');
				$ret[] = $obj;
			}
		}
		$this->$attribute = $ret;

	}
	protected function fetchExternalAll($class, $attribute, $joinedOn, $key, $order_by = null, $order_dir = null) {

		$query = "SELECT * FROM ".$this->getTableName($class)." WHERE `".$joinedOn."` = ?";
		if ($order_by !== null) {
			$query .= " ORDER BY `" . $order_by . "`";

			if ($order_dir !== null && ($order_dir == 'ASC' || $order_dir == 'DESC')) {
				$query .= " " . $order_dir;
			}
		}

		$stmt = $this->db->prepare($query);

		$stmt->execute(array($key));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$this->$attribute = $result;

	}
	public function save() {

		$vals = array();

		if ($this->id > 0) {
			//Save existing entry
			$updateString = '';
			foreach ($this->db_keys as $key) {
				$vals[] = $this->$key;
				$updateString .= "`".$key."` = ?, ";
			}
			$sql = "UPDATE ".$this->table_name." SET ".substr($updateString, 0, -2)." WHERE id = ?";
			$vals[] = $this->id;

		} else {
			//Save new entry
			$propertyString = '`'.implode("`, `", $this->db_keys).'`';
			$valueString = '';
			foreach ($this->db_keys as $key) {
				$vals[] = $this->$key;
				$valueString .= "?, ";
			}
			$sql = "INSERT INTO ".$this->table_name." (".$propertyString.") VALUES (".substr($valueString, 0, -2).")";
		}
		
		$stmt = $this->db->prepare($sql);
		$stmt->execute($vals);

		$inserted_id = $this->db->lastInsertId();
		return ($this->id > 0) ? $this->id : $inserted_id;

	}

	public function deleteToHistory() {

		$stmt_history = $this->db->prepare("INSERT INTO ".$this->table_name.'_history'." SELECT FROM ".$this->table_name." WHERE id = ?");
		$stmt_history->execute(array($this->id));
		$stmt = $this->db->prepare("DELETE FROM ".$this->table_name." WHERE id = ?");
		$stmt->execute(array($this->id));

	}
	public function delete() {

		$stmt = $this->db->prepare("DELETE FROM ".$this->table_name." WHERE id = ?");
		$stmt->execute(array($this->id));

	}
	public function wasLoaded() {
		return $this->loaded;
	}

	public function set($property, $value) {

		$this->$property = $value;
		if (!in_array($property, $this->db_keys))
			$this->db_keys[] = $property;

	}

	public function get($property) {
		if ($this->id == 0 && !in_array($property, $this->db_keys))
			return '';
		return $this->$property;

	}

	private function getFields() {
		$fields = array();
		if (isset($this->table_name)) {
			$stmt = $this->db->prepare("SHOW COLUMNS FROM ". $this->table_name);
			$stmt->execute();
			$result = $stmt->fetchAll();
			foreach ($result as $res) {
				$fields[] = $res['Field'];
			}
		}
		return $fields;
	}

	public function loadFromArray($data) {
		$fields = $this->getFields();

		if (!empty($fields)) {
			foreach ($data as $key => $value) {
				if (in_array($key, $fields)) {
					$this->set($key, $value);
				}
			}
		} else {
			echo "No fields in table " + $this->table_name;
		}
	}
}
