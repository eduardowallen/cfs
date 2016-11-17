<?php	
class ArticlelistController extends Controller{
	public function overview($id){
		if(!isset($id))
			header('Location: /user/login');

		$this->set('headline', 'Article list - New Category');
		$this->set('create_link', 'Create article category');

		$this->set('th_catNumber', 'Category number');
		$this->set('th_catName', 'Name');
		$this->set('th_catOptional', 'Mandatory');
		$this->set('th_catEdit', 'Edit');
		$this->set('th_catDelete', 'Delete');

		$this->set('button_back', 'Go Back');

		$this->set('desc', 'Categories in this event:');
		$this->set('fair', $id);
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
}
?>
