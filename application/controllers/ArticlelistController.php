<?php	
class ArticlelistController extends Controller{
	public function overview($id, $listid){
		if(!isset($id))
			header('Location: /user/login');
		if(isset($_POST['save'])){

			foreach($_POST['category'] as $category):
				if(!isset($category['CategoryId'])) : 
					$opt = ($category['CategoryOptional'] == "on" ? 1 :  0);
					$articlelist = new Articlelist;
					$articlelist->set('CategoryFair', $id);
					$articlelist->set('CategoryName',$category['CategoryName']);
					$articlelist->set('CategoryOptional', $opt);
					$articlelist->set('CategoryNum', $category['CategoryNum']);
					$articlelist->set('CategoryList', $listid);
					$articlelist->save();
				else:
					$statement = $this->db->prepare('UPDATE article_category SET CategoryFair = ?, CategoryName = ?, CategoryOptional = ?, CategoryNum = ? WHERE CategoryId = ?');
					$statement->execute(array($id, $category['CategoryName'], $opt, $category['CategoryNum'], $category['CategoryId']));
				endif;
			endforeach;
		}
		/* Hämta alla kategorier */
		$statement = $this->db->prepare('SELECT * FROM article_category WHERE CategoryFair = ? AND CategoryList = ?');
		$statement->execute(array($id, $listid));
		$categories = $statement->fetchAll();

		$this->setNoTranslate('list', $listid);
		$this->setNoTranslate('categories', $categories);
		$this->set('headline', 'Article list - New Category');
		$this->set('create_link', 'Create article category');
		$this->set('Yes', 'Yes');
		$this->set('No', 'No');
		$this->set('th_catNumber', 'Category number');
		$this->set('th_catName', 'Name');
		$this->set('th_catOptional', 'Mandatory');
		$this->set('th_subCategories', 'Sub Categories');
		$this->set('th_catEdit', 'Edit');
		$this->set('th_catDelete', 'Delete');
		$this->set('save_label', 'Save');
		$this->set('button_back', 'Go Back');
		$this->set('save_first', 'Save first');


		$this->set('desc', 'Available categories:');
		$this->setNoTranslate('fair', $id);
	}

	public function subcategories($fairid, $headlistid, $subid=null, $del=null, $list){
		if($del == "delete"):
			$statement = $this->db->prepare('DELETE FROM article_subcategory WHERE parentcategory = ? AND id = ?');
			$statement->execute(array($headlistid, $subid));
		endif;
		// spara subkategori		
		if(isset($_POST['save'])):
			foreach($_POST['subcategory'] as $subcategory):
				if(!isset($subcategory['category'])) : 
					$stmt = $this->db->prepare("INSERT INTO article_subcategory(name, fair, parentcategory) VALUES(?, ?, ?)");
					$stmt->execute(array($subcategory['name'], $fairid, $headlistid));
				else:
					$stmt = $this->db->prepare("UPDATE article_subcategory SET name=? WHERE fair=? AND parentcategory=? AND id=?");
					$stmt->execute(array($subcategory['name'], $fairid, $headlistid, $subcategory['category']));
				endif;
			endforeach;
		endif;
		/* Hämta ut subkategorier från databasen */
		$statement = $this->db->prepare('SELECT * FROM article_subcategory WHERE fair = ? AND parentcategory = ?');
		$statement->execute(array($fairid, $headlistid));
		$result = $statement->fetchAll();

		$this->setNoTranslate('subcategories', $result);

		$this->setNoTranslate('list', $list);
		$this->set('article_list', 'Article list');
		$this->set('headline', 'Create / Edit subcategory');
		$this->set('create_link', 'Add subcategory');
		$this->set('save_first', 'Save first');
		$this->set('th_catNumber', 'Subarticles');
		$this->set('articlenumber_error', 'This article number is already set!');
		$this->set('th_catName', 'Name');
		$this->set('th_catEdit', 'Edit');
		$this->set('th_catDelete', 'Delete');
		$this->set('button_save', 'Save');
		$this->set('button_back', 'Go Back');
		$this->set('fair', $fairid);
		$this->set('headlistid', $headlistid);
	}

	public function delete($id, $list){
		$statement = $this->db->prepare('DELETE FROM article_category WHERE CategoryFair = ? AND CategoryId = ?');
		$statement->execute(array($id, $list));

		$statement = $this->db->prepare('DELETE FROM article WHERE ArticleCategory = ?');
		$statement->execute(array($list));

		header('Location: '.BASE_URL.'/articlelist/overview/'.$id);
	}

	public function create($id){
		if(!isset($id))
			header('Location: /user/login');

		$this->set('headline', 'Article list - New Category');
		$this->set('create_link', 'Create article category');

		$this->set('th_catNumber', 'Category number');
		$this->set('th_catName', 'Name');
		$this->set('th_catOptional', 'Mandatory');
		$this->set('th_catEdit', 'Edit');
		$this->set('th_catDelete', 'Delete');
		$this->set('button_save', 'Save');
		$this->set('button_back', 'Go Back');
		$this->set('fair', $id);
	}

	public function deleteList($fair, $list){
		$statement = $this->db->prepare('DELETE FROM article_list WHERE id = ?');
		$statement->execute(array($list));

		header('Location: '.BASE_URL.'articlelist/lists/'.$fair);
	}

	public function lists($fair){
		if(!isset($fair))
			header('Location: /user/login');

		if(isset($_POST['save'])):
			$statement = $this->db->prepare('UPDATE article_list SET active = 0 WHERE active = 1');
			$statement->execute();
			if(!isset($_POST['list'])):
				$statement = $this->db->prepare('UPDATE article_list SET active = 1 WHERE id=?');
				$statement->execute(array($_POST['list_active']));
			else:
				foreach($_POST['list'] as $id => $list):
					$radio_id = $_POST['list_active'];
					$active = ($id == $radio_id ? 1 :  0);
					
					if(isset($list['id'])):
						$statement = $this->db->prepare('UPDATE article_list SET name = ?, active = ? WHERE id=?');
						$statement->execute(array($list['name'], $active, $id));
					else:
						$statement = $this->db->prepare('INSERT INTO article_list(fair, name, active) VALUES(?,?,?)');
						$statement->execute(array($fair, $list['name'], $active));
					endif;
				endforeach;
			endif;
		endif;

		/* Hämta artikellistor som är knutna till mässan */
		$statement = $this->db->prepare('SELECT * FROM article_list WHERE fair = ?');
		$statement->execute(array($fair));
		$result = $statement->fetchAll();

		$this->setNoTranslate('lists', $result);
		$this->setNoTranslate('fair', $fair);

		$this->set('headline', 'Manage Article lists');
		$this->set('subheadline', 'Available Article lists');

		$this->set('create_link', 'Create article list');

		$this->set('th_name', 'Name');
		$this->set('th_duplicate', 'Duplicate');
		$this->set('th_use', 'Use');
		$this->set('th_categories', 'Categories');
		$this->set('th_edit', 'Edit');
		$this->set('th_delete', 'Delete');

		$this->set('button_back', 'Go back');
		$this->set('button_save', 'Save');
	}
}
?>
