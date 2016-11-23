<?php

class FairArticle extends Model {
	
	public function load($value, $key, $value2 = NULL, $key2 = NULL) {
		if (!is_null($key2) && !is_null($value2)) {
			$stmt = $this->db->prepare("SELECT `*` FROM `fair_article` WHERE `{$key}` = ? AND `{$key2}` = ?");
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

	public static function getArticlesForFair($fairId) {
		$stmt = $this->db->prepare("SELECT * FROM `fair_article` WHERE `fair` = ?");
		$stmt->execute(array($fairId));

		$articles = array();

		foreach ($stmt->fetchAll(PDO::FETCH_NUM) as $row) {
			$articles[] = $row[0];
		}

		return $articles;
	}
}