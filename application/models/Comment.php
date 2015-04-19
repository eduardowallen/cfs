<?php
class Comment extends Model {
	private $exhibitor_obj;
	private $fair_obj;

	public $exhibitor_name = '';
	public $fair_name = '';

	public function load($key, $by) {
		parent::load($key, $by);

		if ($this->wasLoaded()) {
			$this->exhibitor_obj = new User();
			$this->exhibitor_obj->load($this->exhibitor, 'id');

			if ($this->exhibitor_obj->wasLoaded()) {
				$this->exhibitor_name = $this->exhibitor_obj->get('company');
			}

			$this->fair_obj = new Fair();
			$this->fair_obj->load($this->fair, 'id');

			if ($this->fair_obj->wasLoaded()) {
				$this->fair_name = $this->fair_obj->get('name');
			}
		}
	}

	public function save() {
		$this->id = parent::save();

		return $this->id;
	}

	/* Static methods */

	public static function fetchAll($mode, $author_id, $author_owner_id, $user_id, $fair_id = 0, $position_id = 0) {
		global $globalDB;

		$sql = "SELECT au.name AS author, eu.company AS exhibitor_name, fmp.name AS position_name, c.*, f.name AS fair_name
				FROM comment AS c
				LEFT JOIN user AS au ON au.id = c.author
				LEFT JOIN user AS eu ON eu.id = c.exhibitor
				LEFT JOIN fair AS f ON f.id = c.fair
				LEFT JOIN fair_map_position AS fmp ON fmp.id = c.position";

		$sql_wheres = array();

		switch ($mode) {
			case 1:
				// Fetch all comments on exhibitor on any fair
				$sql_wheres[] = "c.exhibitor = ?";

				$params = array($user_id);
				break;

			case 2:
				// Fetch all comments on any exhibitor on fair
				$sql_wheres[] = "c.fair = ?";

				$params = array($fair_id);
				break;

			case 3:
				// Fetch all comments on user on any position AND that fair
<<<<<<< HEAD
				$sql_wheres[] = "c.exhibitor = ? AND (c.fair = ? OR c.fair >= 0)";
=======
				$sql_wheres[] = "c.exhibitor = ? AND (c.fair = ? OR c.fair = 0)";
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217

				$params = array($user_id, $fair_id);
				break;

			case 4:
				// Fetch all comments on any exhibitor on any fair with same author owner
				$params = array();
				break;
		}

		if ($position_id > 0) {
<<<<<<< HEAD
			$sql_wheres[] = "(c.position = ? OR c.position >= 0)";
=======
			$sql_wheres[] = "(c.position = ? OR c.position = 0)";
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
			$params[] = $position_id;
		}

		if ($author_owner_id != 0) {
			$sql_wheres[] = "c.author_owner = ?";
			$params[] = $author_owner_id;
		}

		if (count($sql_wheres) > 0) {
			$sql .= " WHERE " . implode(" AND ", $sql_wheres);
		}

		$stmt_comments = $globalDB->prepare($sql . " ORDER BY date DESC");
		$stmt_comments->execute($params);

		return $stmt_comments->fetchAll(PDO::FETCH_CLASS, 'Comment');
	}
}
?>