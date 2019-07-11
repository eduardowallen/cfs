<?php

class FairGroup extends Model {
	protected $fairs_rel = array();
	public function load($key, $by) {
		
		parent::load($key, $by);
		if ($this->wasLoaded()) {
			$stmt = $this->db->prepare("SELECT fgr.*, fair.name FROM fair_group_rel AS fgr LEFT JOIN fair ON fair.id = fgr.fair WHERE fgr.group = ?");
			$stmt->execute(array($this->id));
			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			//$this->fairs_rel[] = $res;
			if (count($res) > 0) {
				foreach ($res as $r) {
					$this->fairs_rel[] = $r;
				}
			}
			//$this->fetchExternalFair('FairGroupRel', 'fairs_rel', 'group', $this->id);
		}
	}
	public function delete() {
		$stmt = $this->db->prepare("DELETE FROM fair_group_rel WHERE `group` = ?");
		$stmt->execute(array($this->id));
		parent::delete();
	}
	public function save() {
		
		if ($this->wasLoaded()) {
			$sql = "UPDATE fair_group SET name = ?, invoice_no = ?, owner = ? WHERE id = ?";
			$params = array($this->name, $this->invoice_no, $this->owner, $this->id);
		} else {
			$sql = "INSERT INTO fair_group (name, invoice_no, owner) VALUES (?, ?, ?)";
			$params = array($this->name, $this->invoice_no, $this->owner);
		}
		
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
		
		return ($this->wasLoaded()) ? $this->id : $this->db->lastInsertId();
	}
}

?>