<?php 
// Artikelsidan
class ArticleController extends Controller{
	public function index(){
		
	}
	
	public function create($id){
		if(!isset($id)){
			header('Location: '.BASE_URL.'user/login');
		}
		for($i = 1; $i < $_POST['numrows']+1; $i++){
			$art = new Article();
			$name = $_POST['article_name_'.$i];
			$pricesek = $_POST['article_price_sek'.$i];
			$priceeur = $_POST['article_price_eur'.$i];
			$priceusd = $_POST['article_price_usd'.$i];
			$cat = $_POST['article_category_'.$i];

			if(empty($cat))
				$cat = "Ingen kategori";

			$aid = $_POST['article_id_'.$i];
			$art->set('name', $name);
			$art->set('price_sek', $pricesek);
			$art->set('price_eur', $priceeur);
			$art->set('price_usd', $priceusd);
			$art->set('cat', $cat);
			$art->set('id', $aid);
			$art->set('fair', $id);
			$art->set('type', "new");
			$art->save();
			header('Location: '.BASE_URL.'fair/lists/'.$id);
		}
		$this->set('headline', 'New article');
		$this->set('create_link', 'Add article row');
		$this->set('input_id', 'Article ID');
		$this->set('input_category', 'Category');
		$this->set('input_price', 'Price');
		$this->set('input_name', 'Article Name');
		$this->set('input_submit', 'Save article');
		$this->set('fair', $id);
	}
	
	public function category($id){
		$this->set('headline', '');
				$this->set('headline', 'Category');
	}

	public function edit($id, $aid){
		if(!isset($id)){
			header('Location: '.BASE_URL.'user/login');
		}
		if(isset($_POST['article_name_1'])){
			$art = new Article();
			$name = $_POST['article_name_1'];
			$priceeur = $_POST['article_price_eur_1'];
			$priceusd = $_POST['article_price_usd_1'];
			$pricesek = $_POST['article_price_sek_1'];
			$cat = $_POST['article_category_1'];

			if(empty($cat))
				$cat = "Ingen kategori";

			$aid = $_POST['article_id_1'];
			$art->set('name', $name);
			$art->set('price', $price);
			$art->set('cat', $cat);
			$art->set('id', $aid);
			$art->set('fair', $id);
			$art->update();
			header('Location: '.BASE_URL.'fair/lists/'.$id);
		} else {
			$article = new Article();
			$article->set('fair', $id);
			$article->set('id', $aid);
			$article->getArticleFromId($id, $aid);
		}
		$this->set('art_name', $article->get('name'));
		$this->set('art_price', $article->get('price'));
		$this->set('art_id', $article->get('id'));
		$this->set('art_cat', $article->get('cat'));
		$this->set('headline', 'Edit article');
		$this->set('input_id', 'Article ID');
		$this->set('input_category', 'Category');
		$this->set('input_price_sek', 'Price SEK');
		$this->set('input_price_eur', 'Price EUR');
		$this->set('input_price_usd', 'Price USD');
		$this->set('input_name', 'Article Name');
		$this->set('input_submit', 'Save article');
		$this->set('fair', $id);
	}

	public function delete($id, $aid){
		$art = new Article();
			$art->set('fair', $id);
			$art->set('id', $aid);
		$art->delete();
		header('Location: '.BASE_URL.'fair/lists/'.$id);
	}
}
?>
