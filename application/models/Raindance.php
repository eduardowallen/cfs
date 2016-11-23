<?php

class Raindance extends Model {
	
	public function save() {
		
		if ($this->wasLoaded()) {
			$sql = "UPDATE raindance SET part_one = ?, part_two = ?, part_three = ?, part_four = ?, part_five = ?, part_six = ?, part_seven = ?, part_eight = ?, accrualkey = ? WHERE fair = ?";
			$params = array($this->part_one, $this->part_two, $this->part_three, $this->part_four, $this->part_five, $this->part_six, $this->part_seven, $this->part_eight, $this->accrualkey, $this->fair);
		} else {
			$sql = "INSERT INTO raindance (fair, part_one, part_two, part_three, part_four, part_five, part_six, part_seven, part_eight, accrualkey) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$params = array($this->fair, $this->part_one, $this->part_two, $this->part_three, $this->part_four, $this->part_five, $this->part_six, $this->part_seven, $this->part_eight, $this->accrualkey);
		}
		
		$stmt = $this->db->prepare($sql);
		$stmt->execute($params);
	}
	
}

?>