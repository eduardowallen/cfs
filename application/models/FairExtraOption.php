<?php

class FairExtraOption extends Model {
	
	public function load($value, $key, $value2 = NULL, $key2 = NULL) {
		if (!is_null($key2) && !is_null($value2)) {
			$stmt = $this->db->prepare("SELECT * FROM `fair_extra_option` WHERE `{$key}` = ? AND `{$key2}` = ?");
			$stmt->execute(array($value, $value2));
			$row = $stmt->fetch(PDO::FETCH_NUM);

			if (!empty($row)) {
				return parent::load($row[0], "id");
			} else {
				return $this;
			}
		} else {
			return parent::load($value, $key);
		}
	}

	public static function getOptionsForFair($fairId) {
		global $globalDB;
		$stmt = $globalDB->db->prepare("SELECT * FROM `fair_extra_option` WHERE `fair` = ?");
		$stmt->execute(array($fairId));

		$options = array();

		foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $row) {
			$options[] = $row[0];
		}

		return $options;
	}
}