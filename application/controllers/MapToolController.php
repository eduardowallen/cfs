<?php
class MapToolController extends Controller {

	function map($fairId, $position=null, $map=null, $reserve=null) {

		function makeUserOptions($db){
			$stmt = $db->prepare("SELECT rel.user, user.company, user.name FROM fair_user_relation AS rel LEFT JOIN user ON rel.user = user.id WHERE rel.fair = ? AND user.level = '1'");
			$stmt->execute(array($fairId));
			$result = $stmt->fetchAll();
			$opts = '';
			foreach ($result as $res) {
				$opts.= '<option value="'.$res['user'].'">'.$res['company'].', '.$res['name'].'</option>';
			}
			return $opts;
		}
		$fair = new Fair();
		if (preg_match('/^\d+$/', $fairId)) {
			$fair->load($fairId, 'id');
		} else if (!$fairId) {
			header('Location: /fair/search');
		} else {
			$fair->load($fairId, 'url');
		}

		if ($fair->wasLoaded()) {
			$saveVisit = true;
			//Update session to selected fair
			$_SESSION['user_fair'] = $fair->get('id');
			$_SESSION['fair_windowtitle'] = $fair->get('windowtitle');
			$sql = "SELECT COUNT(required) FROM fair_article WHERE fair = ? AND required = 0";
			$prep = $this->db->prepare($sql);
			$prep->execute(array($_SESSION['user_fair']));
			$result = $prep->fetch();
			$this->setNoTranslate('available_articles', $result[0]);

			//Save latest visited fair
			if (!empty($_SESSION["user_id"]) && $saveVisit) {
				setcookie($_SESSION["user_id"] . "_last_fair", $_SESSION["user_fair"], time() + 3600 * 24 * 365, "/");
			}

			//contextmenu
			$hasRights = false;
			$this->setNoTranslate('accessible_maps', array());

			if (userLevel() == 2) {
				$sql = "SELECT * FROM fair_user_relation WHERE user = ? AND fair = ?";
				$prep = $this->db->prepare($sql);
				$prep->execute(array($_SESSION['user_id'], $fairId));
				$result = $prep->fetch();
				$this->setNoTranslate('accessible_maps', explode('|', $result['map_access']));
				if (!$result) {
					$this->setNoTranslate('hasRights', false);
					$saveVisit = false;
				} else {
					$this->setNoTranslate('hasRights', true);
					$hasRights = true;
				}
			}
			if (userLevel() == 3) {
				$sql = "SELECT * FROM fair WHERE created_by = ? AND id = ?";
				$prep = $this->db->prepare($sql);
				$prep->execute(array($_SESSION['user_id'], $fairId));
				$result = $prep->fetchAll();
				if (!$result) {
					$this->setNoTranslate('hasRights', false);
					$saveVisit = false;
				} else {
					$this->setNoTranslate('hasRights', true);
					$hasRights = true;
				}
			}
			if (userLevel() == 4) {
				$this->setNoTranslate('hasRights', true);
				$hasRights = true;
			} 
			if (userLevel() <= 1) {
				$this->setNoTranslate('hasRights', false);
			}

			$this->setNoTranslate('fair', $fair);
			$this->set('opening_time', 'Opening time');
			$this->set('closing_time', 'Closing time');
			$this->setNoTranslate('notfound', false);
			$this->setNoTranslate('currency', $fair->get('currency'));
			$this->setNoTranslate('fair_url', $fair->get('url'));
			if ($fair->get('allow_registrations') == 1 && userLevel() == 1) {
				// Look for any previous made registrations
				$stmt_registrations = $this->db->prepare("SELECT COUNT(*) AS cnt FROM fair_registration WHERE fair = ? AND user = ?");
				$stmt_registrations->execute(array($fair->get('id'), $_SESSION['user_id']));
				$prev_registrations = $stmt_registrations->fetchObject();
				$this->setNoTranslate('has_prev_registrations', ($prev_registrations->cnt > 0));
			}

			($position === null || $position == 'none') ?  $this->setNoTranslate('position', '\'false\'') : $this->setNoTranslate('position', $position) ;
			($map === null ) ?  $this->setNoTranslate('myMap', '\'false\'') : $this->setNoTranslate('myMap', (int)$map) ;
			($reserve === null || $reserve == 'none') ?  $this->setNoTranslate('reserve', '\'false\'') : $this->setNoTranslate('reserve', $position);
			if ($fair->isLocked() && userLevel() != 4) {
				$this->setNoTranslate('event_locked', true);
			} else {
				$this->setNoTranslate('event_locked', false);
			}
			if (userLevel() > 1 && $hasRights && !$fair->isLocked() || userLevel() == 4) {
				if (isset($_SESSION['copied_fair_registration'])) {
					$fair_registration = new FairRegistration();
					$fair_registration->load($_SESSION['copied_fair_registration'], 'id');
					if ($fair_registration->wasLoaded()) {
						$this->setNoTranslate('copied_fair_registration', $fair_registration);
					}
				}
				$this->set('create_position', 'New stand space');				
				/*if (userLevel() == 1) {1
					$stmt = $this->db->prepare("SELECT * FROM user_ban WHERE user = ? AND organizer = ?");
					$stmt->execute(array($_SESSION['user_id'], $fair->get('created_by')));
					$result = $stmt->fetch();
					if ($result !== false) {
						$this->setNoTranslate('isBanned', true);
						$this->setNoTranslate('ban_msg', $result['reason']);
					} else {
						$this->setNoTranslate('isBanned', false);
					}
						
				}*/
			} else if (!$hasRights && $fair->get('hidden') == 1) {
				$this->setNoTranslate('event_hidden', true);
			} else if (!$hasRights && $fair->get('hidden') == 0) {
				$this->setNoTranslate('event_hidden', false);
			}
		} else {
			$this->setNoTranslate('notfound', true);
		}

	}

	function pasteRegistration($fair = '', $registration_id = '') {
		if ($fair != '' && $registration_id != '') {
			$_SESSION['copied_fair_registration'] = $registration_id;
			header('Location: /mapTool/map/' . $fair);
			die();
		}
	}

	function print_position($map_id = null, $position_id = null) {

		if (is_numeric($map_id) && is_numeric($position_id)) {
			$map = new FairMap();
			$map->load($map_id, 'id');
			$fairId = null;
			$fairId = $map->get('fair');
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
					$option_texts = array();
					foreach ($map_position->get('exhibitor')->get('exhibitor_options') as $option) {
						$option_obj = new FairExtraOption();
						$option_obj->load($option, 'id');
						$option_texts[] = $option_obj->get('text');
					}
					$this->setNoTranslate('map', $map);
					$this->setNoTranslate('position', $map_position);
					$this->setNoTranslate('exhibitor', $map_position->get('exhibitor'));
					$this->setNoTranslate('category_names', implode(', ', $category_names));
					$this->setNoTranslate('option_texts', implode(', ', $option_texts));
					
				if (userLevel() == 2) {
					$fair = new Fair();
					$fair->load($fairId, 'id');
					$sql = "SELECT * FROM fair_user_relation WHERE user = ? AND fair = ?";
					$prep = $this->db->prepare($sql);
					$prep->execute(array($_SESSION['user_id'], $fairId));
					$result = $prep->fetch();
					$this->setNoTranslate('accessible_maps', explode('|', $result['map_access']));
					if ($result) {
						$this->setNoTranslate('option_texts', implode(', ', $option_texts));
					}
				} 
				if (userLevel() == 3) {
					$fair = new Fair();
					$fair->load($fairId, 'id');
					$sql = "SELECT * FROM fair WHERE created_by = ? AND id = ?";
					$prep = $this->db->prepare($sql);
					$prep->execute(array($_SESSION['user_id'], $fairId));
					$result = $prep->fetchAll();
					if ($result) {
						$this->setNoTranslate('option_texts', implode(', ', $option_texts));
					}
				}
				if (userLevel() == 4) {
						$this->setNoTranslate('option_texts', implode(', ', $option_texts));
				}
					$this->set('label_status', 'Status');
					$this->set('label_area', 'Area');
					$this->set('label_by', 'by');
					$this->set('label_reserved_until', 'Reserved until');
					$this->set('label_presentation', 'Presentation text');
					$this->set('label_commodity', 'Commodity');
					$this->set('label_categories', 'Categories');
					$this->set('label_options', 'Extra options');
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
