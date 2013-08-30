<?php

class Article extends Model{
	public $price;
	public $num;
	public $name;
	protected $id;

	function __construct($id){
		$this->id = $id;
	}

	function loadit($db){
		if(isset($this->id)):
			$stmt = $db->prepare("SELECT * FROM article WHERE ArticleId=?");
			$stmt->execute(array($this->id));
			$res  = $stmt->fetch(PDO::FETCH_ASSOC);
			$this->price = $res['ArticlePrice'];
			$this->num = $res['ArticleNum'];
			$this->name = $res['ArticleName'];
			$this->id = $res['ArticleId'];
		endif;
	}
	public function getArticleFromCategory(){
		$stmt = $this->db->prepare("SELECT * FROM article WHERE ArticleId=? AND ArticleCategory=?");
		$stmt->execute(array($this->id, $this->fair));
		$res = $stmt->fetch(PDO::FETCH_ASSOC);
		$this->cat = $res['kategori'];
		$this->name = $res['name'];
		$this->price = $res['price'];
	}

	public function save(){
		$stmt = $this->db->prepare("INSERT INTO article (ArticleNum, ArticleCategory, ArticleName, ArticlePriceSEK, ArticlePriceUSD, ArticlePriceEUR) VALUES (?, ?, ?, ?, ?)");
		$stmt->execute(array($this->id, $this->fair, $this->cat, $this->name, $this->price));
	}

	public function update(){
		$stmt = $this->db->prepare("UPDATE article SET ArticleNum=?, ArticleCategory=?,ArticleName=?, ArticlePrice=? WHERE ArticleNum=? AND ArticleCategory=?");
		$stmt->execute(array($this->id, $this->cat, $this->name, $this->price, $this->id, $this->fair));			
	}

	public function delete(){
		$stmt = $this->db->prepare("DELETE FROM article WHERE ArticleNum=? AND ArticleC=?");
		$stmt->execute(array($this->id, $this->fair));
	}
}
?>
