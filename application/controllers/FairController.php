<?php

class FairController extends Controller {

	public function index() {
		
	}

	function overview($param='') {
		
		setAuthLevel(3);
		
		$this->setNoTranslate('param', $param);
		
		$this->set('headline', 'Fair overview');
		$this->set('create_link', 'Create new fair');
		$this->set('th_max_positions', 'Maximum stand spaces');
		$this->set('th_created', 'Created');
		$this->set('th_closed', 'Closed');
		$this->set('th_auto_publish', 'Opening time');
		$this->set('th_auto_close', 'Closing time');
		$this->set('th_created', 'Created');
		$this->set('th_total', 'Stand spaces');
		$this->set('th_booked', 'Booked spots');
		$this->set('th_reserved', 'reserved');
		$this->set('th_available', 'Available spots');
		$this->set('th_arranger_name', 'Organizer');
		$this->set('th_arranger_cnr', 'Customer nr');
		$this->set('th_fair', 'Name');
		$this->set('th_organizer', 'Organizer');
		$this->set('th_approved', 'Approved');
		$this->set('th_maps', 'Maps');
		$this->set('th_page_views', 'Page views');
		$this->set('th_categories', 'Categories');
		$this->set('th_admins', 'Administrators');
		$this->set('th_exhibitors', 'Exhibitors');
		$this->set('th_settings', 'Settings');
		$this->set('th_delete', 'Delete');
		$this->set('app_yes', 'Yes');
		$this->set('app_no', 'No');
		$this->set('app_locked', 'Locked');

		switch(userLevel()) {

			case 4:
				$sql = "SELECT id FROM fair";
				$params = array();
				if ($param == 'new') {
					$sql.= " WHERE approved = ?";
					array_push($params, '0');
				} else if ((int) $param > 0) {
					$sql.= " WHERE created_by = ?";
					array_push($params, $param);
				}
			$sql.= " ORDER BY approved, name";
				break;
			case 3:
				$sql= "SELECT id FROM fair WHERE created_by = ?";
				$params = array($_SESSION['user_id']);
				break;
			default:
				toLogin();
				break;
		}

		$stmt = $this->Fair->db->prepare($sql);
		$stmt->execute($params);

		$res = $stmt->fetchAll();
		$fairs = array();

		if ($res > 0) {

			foreach ($res as $result) {
				$f = new Fair;
				$f->load($result['id'], 'id', true);

				$arr = new User;
				$arr->load($f->get('created_by'), 'id');

				$stmt = $f->db->prepare("SELECT pos.* FROM fair_map_position AS pos LEFT JOIN fair_map AS map ON pos.map = map.id WHERE map.fair = ?");
				$stmt->execute(array($f->get('id')));
				$positions = $stmt->fetchAll();
				$total = 0;
				$booked = 0;
				$reserved = 0;
				foreach ($positions as $pos) {
					$total++;
					if ($pos['status'] == 2) {
						$booked++;
					} else if ($pos['status'] == 1) {
						$reserved++;
					}
				}
				$f->set('booked', $booked);
				$f->set('reserved', $reserved);
				$f->set('total', $total);
				$f->set('arranger_name', $arr->get('name'));
				$f->set('arranger_cnr', $arr->get('customer_nr'));
				$fairs[] = $f;
			}
			$this->set('fairs', $fairs);
		}

	}

	public function categories($fairId, $do='', $item=0) {

		$this->Fair->load($fairId, 'id');
		if ($this->Fair->wasLoaded() && ($this->Fair->get('created_by') == $_SESSION['user_id'] || userLevel() == 4)) {

			if ($do == 'delete') {
				$cat = new ExhibitorCategory;
				$cat->load($item, 'id');
				if ($cat->wasLoaded() && $cat->get('fair') == $fairId) {
					$cat->delete();
				}
			}
			
			$this->setNoTranslate('do', $do);
			$this->setNoTranslate('item', $item);
			
			if ($do == 'edit') {
				$this->set('form_headline', 'Edit category');
				$cat = new ExhibitorCategory;
				$cat->load($item, 'id');
				$this->setNoTranslate('current_title', $cat->get('name'));
			} else
				$this->set('form_headline', 'New category');
			
			$this->set('headline', 'Exhibitor categories');
			$this->set('fair_id', $this->Fair->get('id'));
			$this->set('name_label', 'Name');
			$this->set('save_label', 'Save');

			$this->set('th_name', 'Category');
			$this->set('th_edit', 'Edit');
			$this->set('th_delete', 'Delete');

			$this->set('confirm_delete', 'Do you really want to delete this category?');

			if (isset($_POST['save'])) {
				$cat = new ExhibitorCategory;
				if ($do == 'edit')
					$cat->load($item, 'id');
				$cat->set('name', $_POST['name']);
				$cat->set('fair', $this->Fair->get('id'));
				$cat->save();
				
				if ($do == 'edit') {
					header('Location: '.BASE_URL.'fair/categories/'.$fairId);
					exit;
				}
				
			}
			$this->Fair->load($fairId, 'id');
			$this->set('categories', $this->Fair->get('categories'));

		}

	}

	public function edit($id) {

		setAuthLevel(3);
		
		if (!empty($id)) {
			function makeUserOptions($db, $sel=0) {
				global $id;
				$stmt = $db->prepare("SELECT id, company, name FROM user WHERE level = '3'");
				$stmt->execute(array());
				$result = $stmt->fetchAll();
				$opts = '';
				foreach ($result as $res) {
					$s = ($sel == $res['id']) ? ' selected="selected"' : '';
					echo $sel.' - '.$res['id'].'<br/>';
					$opts.= '<option'.$s.' value="'.$res['id'].'">'.$res['company'].', '.$res['name'].'</option>';
				}
				return $opts;
			}

			$this->setNoTranslate('fair_id', $id);

			if ($id == 'new') {
				$this->set('edit_headline', 'New fair');

			} else {
				$this->set('edit_headline', 'Edit fair');
				$this->Fair->load($id, 'id');
				
				/*if ($this->Fair->get('approved') != 1) {
					header('Location: '.BASE_URL.'locked');
					exit;
				}*/
				
				if (userLevel() == 3 && $this->Fair->get('created_by') != $_SESSION['user_id'])
					toLogin();

			}

			if (isset($_POST['save'])) {
				if ($id == 'new') {
					if (isset($_POST['name']))
						$this->Fair->set('name', $_POST['name']);
					if (isset($_POST['max_positions']))
						$this->Fair->set('max_positions', (int)$_POST['max_positions']);
				}
				$this->Fair->set('windowtitle', $_POST['windowtitle']);
				$this->Fair->set('email', $_POST['email']);
				if (isset($_POST['auto_publish'])) {
					$this->Fair->set('auto_publish', strtotime($_POST['auto_publish']));
					$this->Fair->set('auto_close', strtotime($_POST['auto_close']));
				}
				$this->Fair->set('contact_info', $_POST['contact_info']);
				if (userLevel() == 4)
					$this->Fair->set('approved', $_POST['approved']);
				if (userLevel() == 4) {
					$this->Fair->set('created_by', $_POST['arranger']);
				} else {
					$this->Fair->set('created_by', $_SESSION['user_id']);
				}
				$fId = $this->Fair->save();
				/*
				if (is_uploaded_file($_FILES['logo']['tmp_name'])) {
					
					$im = new ImageMagick;
					$im->constrain($_FILES['logo']['tmp_name'], ROOT.'public/images/fairs/'.$fId.'/'.$fId.'.png', 350, 80);
					chmod(ROOT.'public/images/fairs/'.$fId.'/'.$fId.'.png', 0775);
				}
				*/				
				if ($id == 'new') {
					$_SESSION['user_fair'] = $fId;
					if (userLevel() == 3) {
						$user = new User;
						$user->load($_SESSION['user_id'], 'id');
						sendMail('info@chartbooker.com', 'Chartbooker International', 'A new fair '.BASE_URL.$this->Fair->get('url').' has been created by '.$user->get('company'));
					}
					header("Location: ".BASE_URL."fair/overview");
					exit;
				} else {
					header("Location: ".BASE_URL."fair/overview");
					exit;
				}
			}

			$this->setNoTranslate('edit_id', $id);
			$this->set('fair', $this->Fair);

			if ($this->Fair->get('approved') == 0) {
				$this->setNoTranslate('app_sel0', ' selected="selected"');
				$this->setNoTranslate('app_sel1', '');
				$this->setNoTranslate('app_sel2', '');
				$this->setNoTranslate('disable', '');
			} elseif($this->Fair->get('approved') == 1) {
				$this->setNoTranslate('app_sel0', '');
				$this->setNoTranslate('app_sel1', 'selected="selected"');
				$this->setNoTranslate('app_sel2', '');
				$this->setNoTranslate('disable', '');

			} elseif($this->Fair->get('approved') == 2){
				$this->setNoTranslate('app_sel0', '');
				$this->setNoTranslate('app_sel1', '');
				$this->setNoTranslate('app_sel2', 'selected="selected"');
				if(userLevel() != 4){
					$this->setNoTranslate('disable', ' disabled="disabled"');
				}else{
					$this->setNoTranslate('disable', '');
				}
			}

			$this->set('map_button_label', 'Handle maps');
			$this->set('approved_label', 'Status');
			$this->set('arranger_label', 'Organizer');
			$this->set('app_opt0', 'Not approved');
			$this->set('app_opt1', 'Approved');
			$this->set('app_opt2', 'Locked');
			$this->set('name_label', 'Name');
			$this->set('max_positions_label', 'Maximum stand spaces');
			$this->set('window_title_label', 'Window title');
			$this->set('email_label', 'E-mail address');
			//$this->set('logo_label', 'Logotype');
			$this->set('contact_label', 'Contact information');
			$this->set('auto_publish_label', 'Publish date');
			$this->set('auto_close_label', 'Closing date');
			$this->set('save_label', 'Save');

		}
	}

	public function publicView($url) {

		$this->Fair->load($url, 'url');
		$this->set('fair', $this->Fair);

	}

	public function maps($id) {

		setAuthLevel(3);

		if (!empty($id)) {

			$this->Fair->load($id, 'id');
			if (userLevel() == 3 && $this->Fair->get('created_by') != $_SESSION['user_id'])
				toLogin();

			if (isset($_POST['save'])) {
				
			}

			$this->set('headline', 'Map overview');
			$this->set('create_link', 'New map');
			$this->set('th_name', 'Map');
			$this->set('th_view', 'View');
			$this->set('th_edit', 'Edit');
			$this->set('th_delete', 'Delete');
			$this->set('fair', $this->Fair);

		}
	}

	public function delete($id, $confirmed='') {

		setAuthLevel(4);

		$this->set('headline', 'Delete event');

		if ($confirmed == 'confirmed') {
			$this->Fair->load($id, 'id');
			if (userLevel() == 3 && $this->Fair->get('created_by') != $_SESSION['user_id']) {
				toLogin();
			} else {
				$this->Fair->delete();
				header("Location: ".BASE_URL."fair/overview");
				exit;
			}
		} else {
			$this->setNoTranslate('fair_id', $id);
			$this->set('warning', 'Do you really want to delete this entire event?');
			$this->set('yes', 'Yes');
			$this->set('no', 'No');
		}

	}


}

?>