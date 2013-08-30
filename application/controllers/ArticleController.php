<?php 
// Artikelsidan
class ArticleController extends Controller{
	
	public function create($id, $list, $sublist){
		if(!isset($id)){
			header('Location: '.BASE_URL.'user/login');
		}

		/* Om man sparar */
		if(isset($_POST['save'])) :
			foreach($_POST['article'] as $article):
				if(isset($article['ArticleId'])) :
					$stmt = $this->db->prepare('UPDATE article SET ArticleName = ?, ArticlePrice = ?, ArticleNum = ? WHERE ArticleId = ?');
					$stmt->execute(array($article['ArticleName'], $article['ArticlePrice'], $article['ArticleNum'], $article['ArticleId']));
				else :
				/*$art = new Article();
				$art->set('ArticleName', $article['ArticleName']);
				$art->set('ArticlePrice', $article['ArticlePrice']);
				$art->set('ArticleNum', $article['ArticleNum']);
				$art->set('ArticleSubCategory', $sublist);
				$art->set('ArticleCategory', $list);
				$art->save();*/
					$stmt = $this->db->prepare('INSERT INTO article(ArticleCategory, ArticleSubCategory, ArticleName, ArticlePrice, ArticleNum) VALUES(?, ?, ?, ?, ?)');
					$stmt->execute(array($list, $sublist, $article['ArticleName'], $article['ArticlePrice'], $article['ArticleNum']));
				endif;
			endforeach;
		endif;
		/* Hämta alla artiklar under denna subkategori */
		$stmt = $this->db->prepare('SELECT * FROM article WHERE ArticleCategory = ? AND ArticleSubCategory = ?');
		$stmt->execute(array($list, $sublist));
		$result = $stmt->fetchAll();


		$this->setNoTranslate('articles', $result);
		$this->set('headline', 'New article');
		$this->set('create_link', 'Add article');
		$this->set('input_articletype_name', 'Articletype name');
		$this->set('th_articlenr', 'Articlenumber');
		$this->set('th_name', 'Name');
		$this->set('th_price', 'Cost');
		$this->set('th_edit', 'Edit');
		$this->set('th_delete', 'Delete');
		$this->set('button_back', 'Go Back');
		$this->set('button_save', 'Save articles');
		$this->setNoTranslate('fair', $id);
		$this->setNoTranslate('list', $list);	
		$this->setNoTranslate('sublist', $sublist);
	}

	public function delete($id, $list, $hid, $aid){
		$stmt = $this->db->prepare('DELETE FROM article WHERE ArticleId = ?');
		$stmt->execute(array($aid));
		header('Location: '.BASE_URL.'article/create/'.$id.'/'.$list.'/'.$hid.'/');
	}
}
?>
