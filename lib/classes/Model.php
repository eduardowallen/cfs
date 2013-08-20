<?php
class Model {

	protected $_model;
	public $db;

	protected $id;
	protected $table_name;
	private $db_keys = array();
	private $key;
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

	protected function fetchExternal($class, $attribute, $joinedOn, $key) {

		$stmt = $this->db->prepare("SELECT id FROM ".$this->getTableName($class)." WHERE `".$joinedOn."` = ?");

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