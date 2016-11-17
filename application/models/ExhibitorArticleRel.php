<?php

class ExhibitorArticleRel extends Exhibitor {

	protected $exhibitor_article_amount;
	

	public function load($key, $by) {

		$stmt = $this->db->prepare("SELECT * FROM exhibitor_article_rel LEFT JOIN exhibitor ON exhibitor_article_rel.exhibitor = exhibitor.id WHERE `".$by."` = ?");
		$stmt->execute(array($key));
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($result > 0) {
			
			foreach ($result as $res) {
				$this->exhibitor_article_amount[] = $res['exhibitor'];
			}
		}

		return true;
	}


/*	public function load($value, $key, $value2 = NULL, $key2 = NULL) {
		if (!is_null($key2) && !is_null($value2)) {
			$stmt = $this->db->prepare("SELECT `*` FROM `exhibitor_article_rel` WHERE `{$key}` = ? AND `{$key2}` = ?");
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
	public static function getarticlesForFair($fairId) {
		$stmt = $this->db->prepare("SELECT `custom_id` `text`, `price`, `required` FROM `fair_extra_article` WHERE `fair` = ?");
		$stmt->execute(array($fairId));

		$articles = array();

		foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $row) {
			$articles[] = $row[0];
		}

		return $articles;
	}*/
}
	

?>