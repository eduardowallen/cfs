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
		$this->set('th_reserved', 'Reserved');
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
		$this->set('th_clone', 'Clone');
		$this->set('app_yes', 'Yes');
		$this->set('app_no', 'No');
		$this->set('app_locked', 'Locked');
		$this->set('dialog_clone_question', 'Are you sure that you want to clone this event?');
		$this->set('dialog_clone_info_link', 'What does it mean to clone an event?');
		$this->set('dialog_clone_info', 'When you clone an event, all its stand spaces, bookings and settings are cloned to a new one. Before the cloning is done you will be prompted to change some settings for the cloned event. This is to change the date of when the bookings open and close for the new, cloned event. You will also be prompted to change the name for the cloned event. This is to avoid the old event name to collide with the new, cloned event name.');
		$this->set('dialog_clone_disabled', 'This event is locked and is therefore not available for cloning. Contact Chartbooker for more information.');

		if ($param == 'cloning_complete') {
			$this->set('msg_cloning_complete', 'Cloning of the event complete.');
		}

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
			$this->setNoTranslate('fairs', $fairs);
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
			$this->setNoTranslate('fair_id', $this->Fair->get('id'));
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
			$this->setNoTranslate('categories', $this->Fair->get('categories'));

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
					$this->Fair->set('approved', 1);
				}
				$this->Fair->set('hidden', $_POST['hidden']);
				for ($i = 1; $i <= 3; $i++) {
					$this->Fair->set('reminder_day' . $i, $_POST['reminder_day' . $i]);
					$this->Fair->set('reminder_note' . $i, $_POST['reminder_note' . $i]);
				}
				$fId = $this->Fair->save();
				if ($id == 'new') {
					$_SESSION['user_fair'] = $fId;
					$user = new User;
					$user->load($_SESSION['user_id'], 'id');

					if((strlen($fairmail) > 1)):
						Alias::addNew($fairmail, array('info'));
					endif;

					if (userLevel() == 3) {
						
						/* Alias */
						/*					
						$organizermail = $user->get('email');
						$fairmail = $_POST['name'];
						require('lib/classes/Alias.php');

						if((strlen($organizermail) > 1) && (strlen($fairmail) > 1)):
							Alias::addNew($fairmail, $organizermail);
						endif;
						*/

					    $mail = new Mail(EMAIL_FROM_ADDRESS, 'new_fair');
					    $mail->setMailVar('url', BASE_URL.$this->Fair->get('url'));
					    $mail->setMailVar('company', $user->get('company'));
					    $mail->send();
					}
					header("Location: ".BASE_URL."fair/overview");
					exit;
				} else {
					header("Location: ".BASE_URL."fair/overview");
					exit;
				}
			}

			$this->setNoTranslate('edit_id', $id);
			$this->setNoTranslate('fair', $this->Fair);

			if ($this->Fair->get('approved') == 0) {
				$this->setNoTranslate('app_sel0', ' selected="selected"');
				$this->setNoTranslate('app_sel1', '');
				$this->setNoTranslate('app_sel2', '');
				$this->setNoTranslate('disable', '');
			} elseif($this->Fair->get('approved') == 1) {
				$this->setNoTranslate('app_sel0', '');
				$this->setNoTranslate('app_sel1', ' selected="selected"');
				$this->setNoTranslate('app_sel2', '');
				$this->setNoTranslate('disable', '');

			} elseif($this->Fair->get('approved') == 2){
				$this->setNoTranslate('app_sel0', '');
				$this->setNoTranslate('app_sel1', '');
				$this->setNoTranslate('app_sel2', ' selected="selected"');
			}

			$this->setNoTranslate('hidden_sel0', ($this->Fair->get('hidden') == 0 ? '' : ' selected="selected"'));
			$this->setNoTranslate('hidden_sel1', ($this->Fair->get('hidden') == 1 ? ' selected="selected"' : ''));

			if(userLevel() < 3){
					$this->setNoTranslate('disable', 'disabled="disabled"');
				}else{
					$this->setNoTranslate('disable', '');
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
			$this->set('interval_reminders_label', 'Interval for reminders');
			$this->set('reminder_1_label', '1st reminder');
			$this->set('reminder_2_label', '2nd reminder');
			$this->set('reminder_3_label', '3rd reminder');
			$this->set('no_reminder_label', 'No reminder');
			$this->set('edit_label', 'Edit');
			$this->set('edit_note_1_label', 'Edit message for 1st reminder');
			$this->set('edit_note_2_label', 'Edit message for 2nd reminder');
			$this->set('edit_note_3_label', 'Edit message for 3rd reminder');
			$this->set('save_label', 'Save');
			$this->set('cancel_label', 'Cancel');
			$this->set('hide_fair_for_label', 'Hide fair for unauthorized accounts');
			$this->set('false_label', 'false');
			$this->set('true_label', 'true');
		}
	}

	public function makeclone($id = '') {

		setAuthLevel(3);

		if (!empty($id)) {

			$this->setNoTranslate('fair_id', $id);

			$this->set('clone_headline', 'Clone fair');
			$this->Fair->load($id, 'id');
			
			/*if ($this->Fair->get('approved') != 1) {
				header('Location: '.BASE_URL.'locked');
				exit;
			}*/

			if (userLevel() == 3 && $this->Fair->get('created_by') != $_SESSION['user_id'])
				toLogin();

			if (isset($_POST['name'])) {

				$auto_close_reserved = date('Y-m-d H:i', strtotime($_POST['auto_close_reserved']));

				$fair_clone = new Fair();
				$fair_clone->set('name', $_POST['name']);
				$fair_clone->set('logotype', '');
				$fair_clone->set('windowtitle', $_POST['windowtitle']);
				$fair_clone->set('email', '');
				$fair_clone->set('contact_info', $_POST['contact_info']);
				$fair_clone->set('created_by', $this->Fair->get('created_by'));
				$fair_clone->set('closing_time', $this->Fair->get('closing_time'));
				$fair_clone->set('page_views', 0);
				$fair_clone->set('approved', 1);
				$fair_clone->set('auto_publish', strtotime($_POST['auto_publish']));
				$fair_clone->set('auto_close', strtotime($_POST['auto_close']));
				$fair_clone->set('max_positions', $this->Fair->get('max_positions'));
				$fair_clone->set('hidden', $this->Fair->get('hidden'));
				$fair_clone_id = $fair_clone->save();

				/* Hämta alla kartor */
				$statement = $this->db->prepare('SELECT * FROM fair_map WHERE fair = ?');
				$statement->execute(array($this->Fair->get('id')));
				$maps = $statement->fetchAll(PDO::FETCH_ASSOC);
				$position_ids = array();

				foreach ($maps as $map) {
					/* Kopiera kartan */
					$statement = $this->db->prepare("INSERT INTO fair_map (fair, name) VALUES (?, ?)");
					$statement->execute(array($fair_clone_id, $map['name']));
					$map_clone_id = $this->db->lastInsertId();

					/* Kopiera även kartbilderna */
					$image_path_old = ROOT.'public/images/fairs/'.$this->Fair->get('id').'/maps/';
					$image_path_new = ROOT.'public/images/fairs/'.$fair_clone_id.'/maps/';
					@copy($image_path_old . $map['id'] . '.jpg', $image_path_new . $map_clone_id . '.jpg');
					@copy($image_path_old . $map['id'] . '_large.jpg', $image_path_new . $map_clone_id . '_large.jpg');
					@chmod($image_path_new . $map_clone_id . '.jpg', 0775);
					@chmod($image_path_new . $map_clone_id . '_large.jpg', 0775);

					/* Hämta alla ståndspositioner */
					$statement = $this->db->prepare('SELECT * FROM fair_map_position WHERE map = ?');
					$statement->execute(array($map['id']));
					$positions = $statement->fetchAll(PDO::FETCH_ASSOC);

					foreach ($positions as $position) {
						/* Kopiera ståndet */
						$statement = $this->db->prepare("INSERT INTO fair_map_position (map, x, y, area, name, information, status, expires, created_by, being_edited, edit_started) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
						$new_status = ($position['status'] == 2 ? 1 : 0);
						$statement->execute(array($map_clone_id, $position['x'], $position['y'], $position['area'], $position['name'], $position['information'], $new_status, $auto_close_reserved, $position['created_by'], $position['being_edited'], $position['edit_started']));
						$position_ids[$position['id']] = $this->db->lastInsertId();
					}
				}

				/* Hämta alla kopplingar mellan användare och utställningar */
				$statement = $this->db->prepare('SELECT * FROM fair_user_relation WHERE fair = ?');
				$statement->execute(array($this->Fair->get('id')));
				$user_relations = $statement->fetchAll(PDO::FETCH_ASSOC);

				foreach ($user_relations as $relation) {
					/* Kopiera kopplingen */
					$statement = $this->db->prepare("INSERT INTO fair_user_relation (fair, user, fair_presentation, map_access, connected_time) VALUES (?, ?, ?, ?, ?)");
					$statement->execute(array($fair_clone_id, $relation['user'], $relation['fair_presentation'], $relation['map_access'], $relation['connected_time']));
				}

				/* Hämta alla preliminärbokningar */
				$statement = $this->db->prepare('SELECT * FROM preliminary_booking WHERE fair = ?');
				$statement->execute(array($this->Fair->get('id')));
				$preliminary_booking = $statement->fetchAll(PDO::FETCH_ASSOC);

				foreach ($preliminary_booking as $booking) {
					/* Kopiera bokningen */
					$statement = $this->db->prepare("INSERT INTO preliminary_booking (user, fair, position, categories, commodity, arranger_message, booking_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
					$statement->execute(array($booking['user'], $fair_clone_id, $position_ids[$booking['position']], $booking['categories'], $booking['commodity'], $booking['arranger_message'], $booking['booking_time']));
				}

				/* Hämta alla utställarkategorier */
				$statement = $this->db->prepare('SELECT * FROM exhibitor_category WHERE fair = ?');
				$statement->execute(array($this->Fair->get('id')));
				$exhibitor_categories = $statement->fetchAll(PDO::FETCH_ASSOC);
				$ex_cat_ids = array();

				foreach ($exhibitor_categories as $category) {
					/* Kopiera utställarkatagorin */
					$statement = $this->db->prepare("INSERT INTO exhibitor_category (name, fair) VALUES (?, ?)");
					$statement->execute(array($category['name'], $fair_clone_id));
					$ex_cat_ids[$category['id']] = $this->db->lastInsertId();
				}

				/* Hämta alla utställare */
				$statement = $this->db->prepare('SELECT * FROM exhibitor WHERE fair = ?');
				$statement->execute(array($this->Fair->get('id')));
				$exhibitors = $statement->fetchAll(PDO::FETCH_ASSOC);

				foreach ($exhibitors as $exhibitor) {
					/* Kopiera utställaren */
					$statement = $this->db->prepare("INSERT INTO exhibitor (user, fair, position, category, presentation, commodity, arranger_message, approved, invoice_sent, invoice_message, booking_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
					$statement->execute(array($exhibitor['user'], $fair_clone_id, $position_ids[$exhibitor['position']],  $exhibitor['category'],  $exhibitor['presentation'],  $exhibitor['commodity'],  $exhibitor['arranger_message'],  $exhibitor['approved'],  $exhibitor['invoice_sent'],  $exhibitor['invoice_message'],  $exhibitor['booking_time']));
					$exhibitor_clone_id = $this->db->lastInsertId();

					/* Hämta alla kopplingar mellan utställare och kategorier */
					$statement = $this->db->prepare('SELECT * FROM exhibitor_category_rel WHERE exhibitor = ?');
					$statement->execute(array($exhibitor['id']));
					$ex_cat_relations = $statement->fetchAll(PDO::FETCH_ASSOC);

					foreach ($ex_cat_relations as $relation) {
						/* Kopiera kopplingen */
						if (isset($ex_cat_ids[$relation['category']])) {
							$statement = $this->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
							$statement->execute(array($exhibitor_clone_id, $ex_cat_ids[$relation['category']]));
						}
					}
				}

				$_SESSION['user_fair'] = $fair_clone_id;
				$user = new User;
				$user->load($_SESSION['user_id'], 'id');

				/* Alias */					
				$fairmail = $_POST['name'];

				if ((strlen($fairmail) > 1)) {
					Alias::addNew($fairmail, array($user->get('email')));
				}

				if (userLevel() == 3) {
				    $mail = new Mail(EMAIL_FROM_ADDRESS, 'new_fair');
				    $mail->setMailVar('url', BASE_URL.$this->Fair->get('url'));
				    $mail->setMailVar('company', $user->get('company'));
				    $mail->send();
				}

				header("Location: ".BASE_URL."fair/overview/cloning_complete");
				exit;
			}

			$this->setNoTranslate('edit_id', $id);
			$this->setNoTranslate('fair', $this->Fair);

			$this->set('name_label', 'Name');
			$this->set('window_title_label', 'Window title');
			$this->set('auto_publish_label', 'Publish date');
			$this->set('auto_close_label', 'Closing date');
			$this->set('auto_close_reserved_label', 'Reservation date for stand spaces');
			$this->set('contact_label', 'Contact information');
			$this->set('clone_label', 'Complete cloning');
			$this->set('dialog_clone_complete_info', 'In connection with completing the cloning of your event, you will be billed according to the agreed contractual.');
		}
	}

	public function publicView($url) {

		$this->Fair->load($url, 'url');
		$this->setNoTranslate('fair', $this->Fair);

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
			$this->setNoTranslate('fair', $this->Fair);

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
