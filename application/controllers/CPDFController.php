<?php

class MYPDFController extends Controller {
	
	public function exhibitor($id=0) {
		
		$ex = new Exhibitor;
		
		$ex->load($id, 'id');
		if ($ex->wasLoaded()) {

				$pos = new FairMapPosition();
				$pos->load2($ex->get('position'), 'id');

				$fair = new Fair();
				$fair->load2($ex->get('fair'), 'id');

				$user = new User();
				$user->load($ex->get('user'), 'id');

				$categoryNames = array();

				if (isset($_POST['categories']) && is_array($_POST['categories'])) {
					$stmt = $pos->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
					foreach ($_POST['categories'] as $cat) {
						$category = new ExhibitorCategory();
						$category->load($cat, "id");
						if ($category->wasLoaded()) {
							$categoryNames[] = $category->get("name");
						}

						$stmt->execute(array($exId, $cat));
					}
				}
				
				
				$options = array();	
				
				if (isset($_POST['options']) && is_array($_POST['options'])) {
					$stmt = $pos->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");
					foreach ($_POST['options'] as $opt) {
						$stmt->execute(array($exId, $opt));
						
						$ex_option = new FairExtraOption();
						$ex_option->load($opt, 'id');
						$options[] = $ex_option->get('text');					
					}
				}
				
				$articles = array();

				if (isset($_POST['articles']) && is_array($_POST['articles'])) {
					$stmt = $pos->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
					$arts = $_POST['articles'];
					$amounts = $_POST['artamount'];

					foreach (array_combine($arts, $amounts) as $art => $amount) {
						$stmt->execute(array($exId, $art, $amount));

						$ex_article = new FairArticle();
						$ex_article->load($art, 'id');
						$articles[] = $ex_article->get('text');			
					}
				}
			}		


			$this->setNoTranslate('headline', $ex->get('company'));

			$this->set('space', 'Space');
			$this->set('status', 'Status');
			$this->set('area', 'Area');
			$this->set('company', 'Company');
			$this->set('commodity', 'Commodity');
			$this->set('website', 'Website');
			$this->set('presentation', 'Presentation');
			$this->set('exhibitor', $ex);
			$this->set('position', $pos);
			
		} else {
			exit;
		}
		
	}

	public function preliminary($id) {
		
		$ex = new Exhibitor;
		
		$ex->load($id, 'id');
		if ($ex->wasLoaded()) {
			
			$pos = new FairMapPosition;
			$pos->load($ex->get('position'), 'id');
			
			$this->setNoTranslate('headline', $ex->get('company'));
			
			$this->set('space', 'Space');
			$this->set('status', 'Status');
			$this->set('area', 'Area');
			$this->set('company', 'Company');
			$this->set('commodity', 'Commodity');
			$this->set('website', 'Website');
			$this->set('exhibitor', $ex);
			$this->set('position', $pos);
			
		} else {
			exit;
		}
		
	}

	public function exhibitor($id) {
		
		$ex = new Exhibitor;
		
		$ex->load($id, 'id');
		if ($ex->wasLoaded()) {
			
			$pos = new FairMapPosition;
			$pos->load($ex->get('position'), 'id');
			
			$this->setNoTranslate('headline', $ex->get('company'));
			
			$this->set('space', 'Space');
			$this->set('status', 'Status');
			$this->set('area', 'Area');
			$this->set('company', 'Company');
			$this->set('commodity', 'Commodity');
			$this->set('website', 'Website');
			$this->set('exhibitor', $ex);
			$this->set('position', $pos);
			
		} else {
			exit;
		}
		
	}	
	
}

?>