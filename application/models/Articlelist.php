<?php

class Articlelist extends Model {

	public function getHeadCategoriesForFair(){
		$stmt = $this->db->prepare("SELECT * FROM article_category WHERE CategoryFair=?");
		$stmt->execute(array($this->fair));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		$this->id = $res['CategoryId'];
		$this->num = $res['CategoryNum'];
		$this->name = $res['CategoryName'];
		$this->optional = $res['CategoryOptional'];
		$this->parentId = 0;
		$this->fair = $res['CategoryFair'];
	}

	public function getSubCategories(){
		$stmt = $this->db->prepare("SELECT * FROM article_category WHERE CategoryParent=?");
		$stmt->execute(array($this->fair));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		$this->id = $res['CategoryId'];
		$this->num = $res['CategoryNum'];
		$this->name = $res['CategoryName'];
		$this->parentId = $res['CategoryParent'];
		$this->fair = $res['CategoryFair'];
		$this->optional = 2;
		$stmt = $this->db->prepare("SELECT CategoryName FROM article_category WHERE CategoryId=?");
		$stmt->execute(array($this->parentId));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		$this->parentName = $res['CategoryName'];
	}

	public function save(){
		$stmt = $this->db->prepare("INSERT INTO article (CategoryNum, CategoryParent, CategoryFair, CategoryName, CategoryOptional) VALUES (?, ?, ?, ?, ?)");
		$stmt->execute(array($this->num, $this->parentId, $this->fair, $this->name, $this->optional));
	}

	public function update(){
		$stmt = $this->db->prepare("UPDATE article SET CategoryNum=?, CategoryParent=?, CategoryName=?, CategoryOptional=? WHERE ArticleId=? AND ArticleCategory=?");
		$stmt->execute(array($this->num, $this->parentId, $this->fair, $this->name, $this->optional, $this->id));		
	}

	public function delete(){
		$stmt = $this->db->prepare("DELETE FROM article_category WHERE CategoryId=? AND CategoryFair=?");
		$stmt->execute(array($this->id, $this->fair));
	}
}

?>
