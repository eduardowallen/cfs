<?php
class MapToolController extends Controller {

	function map($fairId, $position=null, $map=null, $reserve=null) {

		function makeUserOptions($db){
			$stmt = $db->prepare("SELECT rel.user, user.company, user.name FROM fair_user_relation AS rel LEFT JOIN user ON rel.user = user.id WHERE rel.fair = ? AND user.level = '1'");
			$stmt->execute(array($_SESSION['user_fair']));
			$result = $stmt->fetchAll();
			$opts = '';
			foreach ($result as $res) {
				$opts.= '<option value="'.$res['user'].'">'.$res['company'].', '.$res['name'].'</option>';
			}
			return $opts;
		}
		//contextmenu
		
		$this->set('accessible_maps', array());
		if( userLevel() == 2 ){
			$sql = "SELECT * FROM fair_user_relation WHERE user = ? AND fair = ?";
			$prep = $this->db->prepare($sql);
			$prep->execute(array($_SESSION['user_id'], $fairId));
			$result = $prep->fetch();
			$this->set('accessible_maps', explode('|', $result['map_access']));
			if(!$result)
				$this->set('hasRights', false);
			else
				$this->set('hasRights', true);
		}elseif( userLevel()  == 3 ){
			$sql = "SELECT * FROM fair WHERE created_by = ? AND id = ?";
			$prep = $this->db->prepare($sql);
			$prep->execute(array($_SESSION['user_id'], $fairId));
			$result = $prep->fetchAll();
			if(!$result)
				$this->set('hasRights', false);
			else
				$this->set('hasRights', true);
		} else {
			$this->set('hasRights', true);
		}

		$fair = new Fair;
		if (preg_match('/^\d+$/', $fairId)) {
			$fair->load($fairId, 'id');
		} else {
			$fair->load($fairId, 'url');
		}

		if (userLevel() > 1 || $fair->get('approved') == 1 ) {

			if ($fair->wasLoaded()) {
				
				//Update session to selected fair
				$_SESSION['user_fair'] = $fair->get('id');
				$_SESSION['fair_windowtitle'] = $fair->get('windowtitle');
				
				$this->set('fair', $fair);
				$this->set('connect', 'Connect to fair');
				$this->set('create_position', 'New stand space');
				$this->set('opening_time', 'Opening time');
				$this->set('closing_time', 'Closing time');
				$this->set('notfound', false);
				$this->set('fair_url', $fair->get('url'));
				$this->set('basic_cost_stand', 'Basic cost stand');
				$this->set('articles_cost', 'Articles cost');
				($position === null || $position == 'none') ?  $this->set('position', '\'false\'') : $this->set('position', $position) ;
				($map === null ) ?  $this->set('myMap', '\'false\'') : $this->set('myMap', (int)$map) ;
				
				($reserve === null || $reserve == 'none') ?  $this->set('reserve', '\'false\'') : $this->set('reserve', $position);
				
				/***
					Hämta den valda mässans artiklar från databasen
				**/

				$statement = $this->db->prepare("SELECT * FROM article_list as al INNER JOIN article_category as ac ON ac.CategoryList = al.id WHERE al.active = 1 ORDER BY CategoryNum asc");
				$statement->execute(array($fair->get('id')));
				$result1 = $statement->fetchAll();

				foreach($result1 as $art_cat) : 
					$statement = $this->db->prepare("SELECT ArticleId, ArticleName, ArticlePrice, ArticleNum FROM article WHERE ArticleCategory = ?");
					$statement->execute(array($art_cat['CategoryId']));
					$result2 = $statement->fetchAll();
					$articles[$art_cat['CategoryId']] = $result2;
				endforeach;

				$statement = $this->db->prepare("SELECT name, amount FROM fair_custom_fees WHERE fair = ?");
				$statement->execute(array($fair->get('id')));
				$result2 = $statement->fetchAll();

				$this->setNoTranslate('custom_fees', $result2);
				$this->setNoTranslate('article_categories', $result1);
				$this->setNoTranslate('articles', $articles);
				/*
				if (userLevel() == 1) {1
					$stmt = $this->db->prepare("SELECT * FROM user_ban WHERE user = ? AND organizer = ?");
					$stmt->execute(array($_SESSION['user_id'], $fair->get('created_by')));
					$result = $stmt->fetch();
					if ($result !== false) {
						$this->set('isBanned', true);
						$this->setNoTranslate('ban_msg', $result['reason']);
					} else {
						$this->set('isBanned', false);
					}
						
				}*/
				
			} else {
				$this->set('notfound', true);
			}
		} else {
			$this->set('notfound', true);
		}


	}

}

?>
