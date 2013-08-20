<?php
	class Comment extends Model{
		public function load($fair, $exhibitor, $position){
			$stmt = $this->db->prepare("SELECT * FROM comment WHERE fair=? AND exhibitor=? AND (position=? OR position=?)");
			$stmt->execute(array($fair, $exhibitor, $position, 0));

			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $res;
		}

		public function set($fair, $exhibitor, $author, $date, $text, $position){
			$stmt = $this->db->prepare("INSERT INTO comment(fair, exhibitor, position, comment, author, date) VALUES(?, ?, ?, ?, ?, ?)");
			$stmt->execute(array($fair, $exhibitor, $position, $text, $author, $date));
		}	
	}
?>
