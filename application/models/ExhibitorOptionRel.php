<?php

class ExhibitorOptionRel extends Exhibitor {

	protected $exhibitor_option_amount;
	

	public function load($key, $by) {

		$stmt = $this->db->prepare("SELECT * FROM exhibitor_option_rel LEFT JOIN exhibitor ON exhibitor_option_rel.exhibitor = exhibitor.id WHERE `".$by."` = ?");
		$stmt->execute(array($key));
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($result > 0) {
			
			foreach ($result as $res) {
				$this->exhibitor_option_amount[] = $res['exhibitor'];
			}
		}

		return true;
	}


/*	public function load($value, $key, $value2 = NULL, $key2 = NULL) {
		if (!is_null($key2) && !is_null($value2)) {
			$stmt = $this->db->prepare("SELECT `*` FROM `exhibitor_option_rel` WHERE `{$key}` = ? AND `{$key2}` = ?");
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
/*
	public static function getOptionsForFair($fairId) {
		$stmt = $this->db->prepare("SELECT `custom_id` `text`, `price`, `required` FROM `fair_extra_option` WHERE `fair` = ?");
		$stmt->execute(array($fairId));

		$options = array();

		foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $row) {
			$options[] = $row[0];
		}

		return $options;
	}*/
}
	

?>