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
		
		$this->setNoTranslate('accessible_maps', array());
		if( userLevel() == 2 ){
			$sql = "SELECT * FROM fair_user_relation WHERE user = ? AND fair = ?";
			$prep = $this->db->prepare($sql);
			$prep->execute(array($_SESSION['user_id'], $fairId));
			$result = $prep->fetch();
			$this->setNoTranslate('accessible_maps', explode('|', $result['map_access']));
			if(!$result)
				$this->setNoTranslate('hasRights', false);
			else
				$this->setNoTranslate('hasRights', true);
		}elseif( userLevel()  == 3 ){
			$sql = "SELECT * FROM fair WHERE created_by = ? AND id = ?";
			$prep = $this->db->prepare($sql);
			$prep->execute(array($_SESSION['user_id'], $fairId));
			$result = $prep->fetchAll();
			if(!$result)
				$this->setNoTranslate('hasRights', false);
			else
				$this->setNoTranslate('hasRights', true);
		} else if (userLevel() == 4) {
			$this->setNoTranslate('hasRights', true);
		} else {
			$this->setNoTranslate('hasRights', false);
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
				
				$this->setNoTranslate('fair', $fair);
				$this->set('connect', 'Connect to fair');
				$this->set('create_position', 'New stand space');
				$this->set('opening_time', 'Opening time');
				$this->set('closing_time', 'Closing time');
				$this->setNoTranslate('notfound', false);
				$this->setNoTranslate('fair_url', $fair->get('url'));
				($position === null || $position == 'none') ?  $this->set('position', '\'false\'') : $this->set('position', $position) ;
				($map === null ) ?  $this->set('myMap', '\'false\'') : $this->set('myMap', (int)$map) ;
				
				($reserve === null || $reserve == 'none') ?  $this->set('reserve', '\'false\'') : $this->set('reserve', $position);
				
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
				$this->setNoTranslate('notfound', true);
			}
		} else {
			$this->setNoTranslate('notfound', true);
		}


	}

	function print_position($map_id = null, $position_id = null) {

		if (is_numeric($map_id) && is_numeric($position_id)) {

			$map = new FairMap();
			$map->load($map_id, 'id');

			if ($map->wasLoaded()) {
				$map_position = null;

				foreach ($map->get('positions') as $position) {
					if ($position->get('id') == $position_id) {
						$map_position = $position;
						break;
					}
				}

				if ($map_position) {

					$category_names = array();
					foreach ($map_position->get('exhibitor')->get('exhibitor_categories') as $category) {
						$category_obj = new ExhibitorCategory();
						$category_obj->load($category, 'id');
						$category_names[] = $category_obj->get('name');
					}

					$this->setNoTranslate('map', $map);
					$this->setNoTranslate('position', $map_position);
					$this->setNoTranslate('exhibitor', $map_position->get('exhibitor'));
					$this->setNoTranslate('category_names', implode(', ', $category_names));
					$this->set('label_status', 'Status');
					$this->set('label_area', 'Area');
					$this->set('label_by', 'by');
					$this->set('label_reserved_until', 'Reserved until');
					$this->set('label_presentation', 'Presentation');
					$this->set('label_commodity', 'Commodity');
					$this->set('label_categories', 'Categories');
					$this->set('label_no_presentation_text', 'The company has not specified any information.');
					$this->set('label_website', 'Website');
				} else {
					$this->set('error', 'The map position could not be found.');
				}

			} else {
				$this->set('error', 'The map could not be found.');
			}

			$this->setNoTranslate('noView', true);
			$this->setNoTranslate('onlyContent', true);
		}
	}

	function chooseLang() {
		
	}

}

?>
