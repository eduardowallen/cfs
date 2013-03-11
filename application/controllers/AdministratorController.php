<?php

class AdministratorController extends Controller {

	function overview($fair=0) {

		setAuthLevel(3);

		$thisFair = new Fair($this->Administrator->db);
		$thisFair->load($fair, 'id');

		$this->setNoTranslate('thisFair', $fair);

		if (userLevel() == 3) {
			$arr = new Arranger;
			$arr->load($_SESSION['user_id'], 'id');
			$myFairs = array();
			foreach ($arr->get('fairs') as $f) {
				$myFairs[] = $f->get('id');
			}
		}

		if (userLevel() == 4) {
			$_SESSION['user_fair'] = $fair;
		}

		$this->set('headline', 'Administrator overview');
		$this->set('create_link', 'Create new administrator');
		$this->set('fair', $fair);

		$this->set('th_user', 'User');
		$this->set('status_locked', 'Locked');
		$this->set('status_active', 'Active');
		$this->set('th_account_status', 'Account status');
		$this->set('th_account_created', 'Account created');
		$this->set('th_spots_created', 'Created stand spaces');
		$this->set('th_last_login', 'Last login');
		$this->set('th_total_logins', 'Logins total');
		$this->set('th_edit', 'Edit');
		$this->set('th_delete', 'Delete');

		$sql = "SELECT fair, user FROM fair_user_relation WHERE fair = ?";

		$stmt = $this->Administrator->db->prepare($sql);
		$stmt->execute(array($fair));
		$res = $stmt->fetchAll();
		$users = array();
		if ($res > 0) {

			foreach ($res as $result) {
				if (userLevel() == 4 || in_array($result['fair'], $myFairs)) {
					$u = new User;
					$u->load($result['user'], 'id');
					if($u->get('level') == 2) {

						$stmt = $this->Administrator->db->prepare("SELECT COUNT(*) AS pos_count FROM fair_map_position WHERE created_by = ?");
						$stmt->execute(array($u->get('id')));
						$res = $stmt->fetch();
						$u->set('spots_created', $res['pos_count']);
						$users[] = $u;

					}
				}
			}
			$this->set('users', $users);
		}

	}

	public function exhibitors($param='', $value='') {

		if ($param == 'copy') {

			$_SESSION['copied_exhibitor'] = $value;
			header('Location: '.BASE_URL.'administrator/exhibitors');
			exit;

		}

		setAuthLevel(2);

		$this->set('headline', 'Exhibitor overview');
		$this->set('create_link', 'Create new exhibitor');

		$this->set('th_status', 'Status');
		$this->set('th_name', 'Stand space');
		$this->set('th_company', 'Company');
		$this->set('th_branch', 'Branch');
		$this->set('th_phone', 'Phone number');
		$this->set('th_contact', 'Name');
		$this->set('th_email', 'E-mail');
		$this->set('th_website', 'Website');
		$this->set('th_profile', 'Details');
		$this->set('th_copy', 'Copy to map');

		//$this->Administrator->load($_SESSION['user_id'], 'id');

		$fair = new Fair;
		$fair->load($_SESSION['user_fair'], 'id');
		$this->set('fair', $fair);

	}

	public function newExhibitor() {

		setAuthLevel(2);

		$error = '';
		$user = new User;

		if (isset($_POST['save'])) {
			
			$user->set('alias', $_POST['username']);
			$user->set('company', $_POST['company']);
			$user->set('name', $_POST['name']);
			$user->set('orgnr', $_POST['orgnr']);
			$user->set('address', $_POST['address']);
			$user->set('zipcode', $_POST['zipcode']);
			$user->set('city', $_POST['city']);
			$user->set('country', $_POST['country']);
			$user->set('phone1', $_POST['phone1']);
			$user->set('phone2', $_POST['phone2']);
			$user->set('phone3', $_POST['phone3']);
			$user->set('fax', $_POST['fax']);
			$user->set('website', $_POST['website']);
			$user->set('email', $_POST['email']);
			$user->set('invoice_company', $_POST['invoice_company']);
			$user->set('invoice_address', $_POST['invoice_address']);
			$user->set('invoice_zipcode', $_POST['invoice_zipcode']);
			$user->set('invoice_city', $_POST['invoice_city']);
			$user->set('invoice_email', $_POST['invoice_email']);
			$user->set('commodity', $_POST['commodity']);
			$user->set('presentation', $_POST['presentation']);
			$user->set('level', 1);
			$user->set('locked', 0);

			if ($user->aliasExists()) {
				$error.= 'The username already exists in our system.';
			} else if ($user->emailExists()) {
				$error.= 'The email address already exists in our system.';
			} else {
				$arr = array_merge(range(0, 9), range('a', 'z'));
				shuffle($arr);
				$str = substr(implode('', $arr), 0, 10);
				
				$msg = "An organizer has created an account for you on his/her event ".BASE_URL.$_SESSION['outside_fair_url']."\r\n\r\nUsername: ".$_POST['username']."\r\nPassword: ".$str;
				
				$user->setPassword($str);
				$userId = $user->save();
				sendMail($user->email, 'Your password', $msg);

				//require_once ROOT.'application/models/FairUserRelation.php';
				$stmt = $this->Administrator->db->prepare("INSERT INTO fair_user_relation (fair, user) VALUES (?, ?)");
				$stmt->execute(array($_POST['fair'], $userId));

				//header('Location: '.BASE_URL.'administrator/exhibitors');
				header('Location: '.BASE_URL.'exhibitor/forFair');
				exit;

			}

		}

		$this->Administrator->load($_SESSION['user_id'], 'id');
		$fairs = array();

		//require_once ROOT.'application/models/Fair.php';

		foreach ($this->Administrator->get('fairs') as $fId) {
			$fair = new Fair;
			$fair->load($fId, 'id');
			$fairs[] = $fair;
		}
		$this->set('fairs', $fairs);

		$this->set('error', $error);
		$this->set('user', $user);
		
		$this->set('alias_label', 'Username');
		$this->set('copy_label', 'Copy from company details');
		$this->set('headline', 'New exhibitor');
		$this->set('invoice_section', 'Billing address');
		$this->set('fair_label', 'Fair');
		$this->set('company_label', 'Company');
		$this->set('category_label', 'Category');
		$this->set('presentation_label', 'Presentation');
		$this->set('commodity_label', 'Commodity');
		$this->set('customer_nr_label', 'Customer number');
		$this->set('contact_label', 'Contact person');
		$this->set('orgnr_label', 'Organization number');
		$this->set('address_label', 'Address');
		$this->set('zipcode_label', 'Zip code');
		$this->set('city_label', 'City');
		$this->set('country_label', 'Country');
		$this->set('phone1_label', 'Phone 1');
		$this->set('phone2_label', 'Phone 2');
		$this->set('phone3_label', 'Phone 3');
		$this->set('fax_label', 'Fax number');
		$this->set('website_label', 'Website');
		$this->set('email_label', 'E-mail');
		$this->set('invoice_company_label', 'Company');
		$this->set('invoice_address_label', 'Address');
		$this->set('invoice_zipcode_label', 'Zip code');
		$this->set('invoice_city_label', 'City');
		$this->set('invoice_email_label', 'E-mail');
		$this->set('save_label', 'Save');

	}

	public function newReservations($action='', $param='') {

		setAuthLevel(2);

		$fair = new Fair;
		$fair->load($_SESSION['user_fair'], 'id');

		$this->set('fair', $fair);
		
		if( userLevel() == 2 ){
			$sql = "SELECT * FROM fair_user_relation WHERE user = ? AND fair = ?";
			$prep = $this->db->prepare($sql);
			$prep->execute(array($_SESSION['user_id'], $fair->get('id')));
			$result = $prep->fetch(PDO::FETCH_ASSOC);
			$this->set('accessible_maps', explode('|', $result['map_access']));
			if(!$result) {
				$this->set('hasRights', false);
				return;
			} else {
				$this->set('hasRights', true);
			}
		} else {
			$this->set('hasRights', true);
			$this->set('accessible_maps', array());
		}

		if ($action == 'deny') {
			$pb = new PreliminaryBooking;
			$pb->load($param, 'id');
			
			$u = new User;
			$u->load($pb->get('user'), 'id');
			
			$emstr = "Dear user,\r\n\r\nWe regret to inform you that your reservation has been cancelled by an administrator.";
			$emstr.= "\r\n\r\nBest regards,\r\nChartbooker International";
			sendMail($u->get('email'), 'Reservation cancelled', $emstr);
			
			$pb->delete();
			header("Location: ".BASE_URL."administrator/newReservations");
			exit;
		} else if ($action == 'approve') {

			$pb = new PreliminaryBooking;
			$pb->load($param, 'id');

			$pos = new FairMapPosition;
			$pos->load($pb->get('position'), 'id');
			$pos->set('status', 2);

			$ex = new Exhibitor;
			$ex->set('user', $pb->get('user'));
			$ex->set('fair', $pb->get('fair'));
			$ex->set('position', $pb->get('position'));
			$ex->set('category', 0);
			$ex->set('presentation', '');
			$ex->set('commodity', $pb->get('commodity'));
			$ex->set('arranger_message', $pb->get('arranger_message'));
			$ex->set('approved', 1);
			
			$exId = $ex->save();
			$pos->save();
			$pb->delete();

			$stmt = $pb->db->prepare("DELETE FROM preliminary_booking WHERE position = ?");
			$stmt->execute(array($pos->get('id')));
			
			$stmt = $pb->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
			foreach (explode('|', $pb->get('categories')) as $cat) {
				$stmt->execute(array($exId, $cat));
			}
			
			header("Location: ".BASE_URL."administrator/newReservations");
			exit;
			
		}

		$this->set('headline', 'Booked stand spaces');
		$this->set('rheadline', 'Reservations');

		$u = new User;
		$u->load($_SESSION['user_id'], 'id');

		$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, user.company, pos.id AS position, pos.name, pos.area FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ?");
		$stmt->execute(array($_SESSION['user_fair'], 2));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$positions = $result;

		
		$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, user.company, pos.id AS position, pos.name, pos.area FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ?");
		$stmt->execute(array($_SESSION['user_fair'], 1));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$rpositions = $result;
		

		$stmt = $u->db->prepare("SELECT prel.*, user.id as userid, pos.area, pos.name, user.company FROM user, preliminary_booking AS prel, fair_map_position AS pos WHERE prel.fair=? AND pos.id = prel.position AND user.id = prel.user");
		$stmt->execute(array($_SESSION['user_fair']));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$prelpos = $result;

		$this->set('positions', $positions);
		$this->set('rpositions', $rpositions);
		$this->set('prelpos', $prelpos);

		$this->set('prel_table', 'Preliminary bookings');
		$this->set('tr_fair', 'Fair');
		$this->set('tr_pos', 'Stand space');
		$this->set('tr_area', 'Area');
		$this->set('tr_booker', 'Booked by');
		$this->set('tr_field', 'Trade');
		$this->set('tr_time', 'Time of booking');
		$this->set('tr_message', 'Message to organizer');
		$this->set('tr_view', 'View');
		$this->set('tr_delete', 'Delete');
		$this->set('tr_approve', 'Approve');
		$this->set('tr_deny', 'Deny');
		$this->set('tr_reserve', 'Reserve stand space');

		$this->set('confirm_delete', 'Are you sure?');
	}

	public function delete($id, $confirmed='', $from='') {

		setAuthLevel(3);

		$this->set('headline', 'Delete administrator');
		$this->setNoTranslate('from', $from);

		if ($confirmed == 'confirmed') {
			$this->Administrator->load($id, 'id');
			if (userLevel() == 3 && $this->Administrator->get('owner') != $_SESSION['user_id']) {
				toLogin();
			} else {
				$this->Administrator->delete();
				if ($from == 'mine')
					header("Location: ".BASE_URL."administrator/mine");
				else
					header("Location: ".BASE_URL."administrator/overview/".$_SESSION['user_fair']);
				exit;
			}
		} else {
			$this->setNoTranslate('admin_id', $id);
			$this->set('warning', 'Do you really want to delete this administrator?');
			$this->set('yes', 'Yes');
			$this->set('no', 'No');
		}

	}

	public function mine() {
		setAuthLevel(3);
		
		$stmt = $this->Administrator->db->prepare("SELECT user.id, user.name, user.email, user.phone1, user.locked, user.last_login, COUNT(fair_map_position.id) AS position_count
FROM user 
LEFT JOIN fair_map_position ON user.id = fair_map_position.created_by
WHERE user.owner = ? AND user.level = ?");
		$stmt->execute(array($_SESSION['user_id'], 2));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$as = array();
		foreach ($result as $res) {
			// Temporary work around
			// If there are no admins we will get a row of null values.
			if (is_null($res['id'])) {
				continue;
			}

			$stmt2 = $this->Administrator->db->prepare("SELECT COUNT(*) AS fair_count FROM fair_user_relation WHERE user = ?");
			$stmt2->execute(array($res['id']));
			$res2 = $stmt2->fetch(PDO::FETCH_ASSOC);
			$res['fair_count'] = $res2['fair_count'];
			
			$stmt3 = $this->Administrator->db->prepare("SELECT COUNT(*) AS log_count FROM log WHERE user = ? AND action = ?");
			$stmt3->execute(array($res['id'], 'POSITION_UPDATED'));
			$res3 = $stmt3->fetch(PDO::FETCH_ASSOC);
			$res['log_count'] = $res3['log_count'];
			$as[] = $res;
		}
		
		$this->set('button_new', 'Create new administrator');
		$this->set('headline', 'My administrators');
		$this->set('th_name', 'User');
		$this->set('th_email', 'E-mail');
		$this->set('th_phone', 'phone');
		$this->set('th_locked', 'Status');
		$this->set('locked_yes', 'Locked');
		$this->set('locked_no', 'Active');
		$this->set('th_position_count', 'Created stand spaces');
		$this->set('th_positions_edited', 'Edited stand spaces');
		$this->set('th_total_fairs', 'Fairs');
		$this->set('th_lastlogin', 'Last login');
		$this->set('th_user', 'User');
		$this->set('th_edit', 'Edit');
		$this->set('th_delete', 'Delete');
		$this->set('admins', $as);
	}

	public function all() {
		setAuthLevel(4);

		$stmt = $this->Administrator->db->prepare("SELECT user.id, user.name, user.email, user.phone1, user.locked, user.last_login FROM user WHERE user.level = ?");
		$stmt->execute(array(2));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$as = array();
		foreach ($result as $res) {
			
			$stmt2 = $this->Administrator->db->prepare("SELECT COUNT(*) AS fair_count FROM fair_user_relation WHERE user = ?");
			$stmt2->execute(array($res['id']));
			$res2 = $stmt2->fetch(PDO::FETCH_ASSOC);
			$res['fair_count'] = $res2['fair_count'];
			
			$stmt3 = $this->Administrator->db->prepare("SELECT COUNT(*) AS log_count FROM log WHERE user = ? AND action = ?");
			$stmt3->execute(array($res['id'], 'POSITION_UPDATED'));
			$res3 = $stmt3->fetch(PDO::FETCH_ASSOC);
			$res['log_count'] = $res3['log_count'];
			
			$stmt4 = $this->Administrator->db->prepare("SELECT COUNT(*) AS position_count FROM fair_map_position WHERE created_by = ?");
			$stmt4->execute(array($res['id']));
			$res4 = $stmt4->fetch(PDO::FETCH_ASSOC);
			$res['position_count'] = $res4['position_count'];
			
			$as[] = $res;
		}
		$this->set('headline', 'My administrators');
		$this->set('th_name', 'User');
		$this->set('th_email', 'E-mail');
		$this->set('th_phone', 'phone');
		$this->set('th_locked', 'Status');
		$this->set('locked_yes', 'Locked');
		$this->set('locked_no', 'Active');
		$this->set('th_position_count', 'Created stand spaces');
		$this->set('th_positions_edited', 'Edited stand spaces');
		$this->set('th_total_fairs', 'Fairs');
		$this->set('th_lastlogin', 'Last login');
		$this->set('th_user', 'User');
		$this->set('th_edit', 'Edit');
		$this->set('th_delete', 'Delete');
		$this->set('admins', $as);
	}

	public function edit($id=0, $fair=0) {

		setAuthLevel(3);

		if (!empty($id)) {

			if (userLevel() == 3) {
				$arr = new Arranger;
				$arr->load($_SESSION['user_id'], 'id');
				$this->set('arranger', $arr);
				$this->set('fairs', $arr->get('fairs'));
			} else {
				$stmt = $this->Administrator->db->prepare("SELECT id FROM fair");
				$stmt->execute(array());
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$fairs = array();
				foreach ($result as $res) {
					$f = new Fair;
					$f->load($res['id'], 'id');
					$fairs[] = $f;
				}
				$this->set('fairs', $fairs);
			}

			if ($id == 'new') {
				$this->set('headline', 'New administrator');

			} else {
				$this->set('headline', 'Edit administrator');
				$this->Administrator->load($id, 'id');

				if (userLevel() == 3) {
					if ($this->Administrator->get('owner') != $_SESSION['user_id']) {
						toLogin();
					}
				}

			}

			if (isset($_POST['save'])) {
				
				$this->Administrator->set('name', $_POST['name']);
				$this->Administrator->set('phone1', $_POST['phone1']);
				$this->Administrator->set('phone2', $_POST['phone2']);
				$this->Administrator->set('phone3', $_POST['phone3']);
				$this->Administrator->set('email', $_POST['email']);
				$this->Administrator->set('level', 2);
				$this->Administrator->set('locked', $_POST['locked']);
				
				
				if ($id == 'new') {
					$this->Administrator->set('alias', $_POST['alias']);
					if (userLevel() == 4) {
						$adminFair = new Fair;
						$adminFair->load($_SESSION['user_fair'], 'id');
						$this->Administrator->set('owner', $adminFair->get('created_by'));
					} else {
						$this->Administrator->set('owner', $_SESSION['user_id']);
					}
				}
				
				if (!$this->Administrator->emailExists() || $id != 'new') {
					
					if ((!$this->Administrator->aliasExists() || $id != 'new') && isset($_POST['fair_permission'])) {
						
						$aId = $this->Administrator->save();
						require_once ROOT.'application/models/FairUserRelation.php';
	
						$stmt = $this->Administrator->db->prepare("DELETE FROM fair_user_relation WHERE user = ?");
						$stmt->execute(array($aId));
	
						if (isset($_POST['fair_permission'])) {
							foreach ($_POST['fair_permission'] as $fairId) {
								if (isset($_POST['maps'][$fairId])) {
									$rel = new FairUserRelation;
									$rel->set('fair', $fairId);
									$rel->set('user', $aId);
									$rel->set('map_access', implode('|', $_POST['maps'][$fairId]));
									$rel->save();
								}
							}
						}
					} else {
						unset($_POST);
						if (!isset($_POST['fair_permission'])) {
							$this->set('user_message', 'You have to assign permissions.');
						} else {
							$this->set('user_message', 'The alias already exists in our system. Please choose another one.');
						}
						$this->set('error', true);
					}
					//if ($id == 'new') {
						//header("Location: ".BASE_URL."administrator/mine");
						//exit;
					//}
				}elseif( $this->Administrator->emailExists() ){
					//$this->Administrator->addRelation($fair);
					//$this->Administrator->save();
					//header("Location: ".BASE_URL."administrator/mine");
					$this->set('user_message', 'The email address already exists in our system. Please choose another one.');
					$this->set('error', true);

				}
				// Load the user again so we get the new permissions.
				$this->Administrator->load($this->Administrator->get('id'), 'id');

			}

			$this->setNoTranslate('locked0sel', '');
			
			
			$this->set('alias_label', 'Alias');
			$this->set('permissions_headline', 'Permissions for user');
			$this->set('password_label', 'Password');
			$this->set('password_repeat_label', 'Password again (repeat to confirm)');
			
			$this->setNoTranslate('edit_id', $id);
			$this->set('user', $this->Administrator);
			$this->set('user_maps', $this->Administrator->get('maps'));
			$this->set('user_fairs', $this->Administrator->get('fairs'));

			$this->set('locked_label', 'Account locked');
			$this->set('locked_label0', 'No');
			$this->set('locked_label1', 'Yes');
			$this->set('contact_label', 'Name');
			$this->set('phone1_label', 'Phone 1');
			$this->set('phone2_label', 'Phone 2');
			$this->set('phone3_label', 'Phone 3');
			$this->set('email_label', 'E-mail');
			$this->set('maps_label', 'Map access');
			$this->set('save_label', 'Save');

		}
	}

	public function deleteBooking($id = 0, $posId = 0) {
		setAuthLevel(2);

		$exhib = new Exhibitor;
		$exhib->load($id, 'id');

		$u = new User;
		$u->load($exhib->get('user'), 'id');

		$stmt = $this->db->prepare("DELETE FROM exhibitor WHERE id = ? AND position = ?");
		$stmt->execute(array($id, $posId));

		$stmt = $this->db->prepare("UPDATE fair_map_position SET `status`=0 WHERE id = ?");
		$stmt->execute(array($posId));

		$emstr = "Dear user,\r\n\r\nWe regret to inform you that your booking has been cancelled by an administrator.";
		$emstr.= "\r\n\r\nBest regards,\r\nChartbooker International";
		sendMail($u->get('email'), 'Booking cancelled', $emstr);

		header('Location: '.BASE_URL.'administrator/newReservations');
	}

	public function approveReservation($posId = 0) {
		setAuthLevel(2);

		$stmt = $this->db->prepare("UPDATE fair_map_position SET `status`=2 WHERE `id`=?");
		$stmt->execute(array($posId));

		header('Location: '.BASE_URL.'administrator/newReservations');
	}

	public function reservePrelBooking($prelId) {
		setAuthLevel(2);

		$prel = new PreliminaryBooking;
		$prel->load($prelId, 'id');

		$pos = new FairMapPosition;
		$pos->load($prel->get('position'), 'id');

		// Delete existing exhibitor if position is booked
		if ($pos->get('status') > 0) {
			$stmt = $pos->db->prepare("DELETE FROM exhibitor WHERE position = ?");
			$stmt->execute(array($pos->get('id')));
		}

		$pos->set('status', 1);
		$pos->set('expires', date('Y-m-d', time() + 3600 * 24 * 14)); // Set expirytime to 14 days from now.

		$exhib = new Exhibitor;
		$exhib->set('user', $prel->get('user'));
		$exhib->set('fair', $prel->get('fair'));
		$exhib->set('position', $prel->get('position'));
		$exhib->set('category', 0);
		$exhib->set('presentation', '');
		$exhib->set('commodity', $prel->get('commodity'));
		$exhib->set('arranger_message', $prel->get('arranger_message'));
		$exhib->set('booking_time', $prel->get('booking_time'));
		$exhib->set('approved', 1);
		$exId = $exhib->save();
		$pos->save();

		$stmt = $prel->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
		foreach (explode('|', $prel->get('categories')) as $cat) {
			$stmt->execute(array($exId, $cat));
		}

		// Clean up preliminaries.
		$prel->delete();
		$stmt = $prel->db->prepare("DELETE FROM preliminary_booking WHERE position = ?");
		$stmt->execute(array($pos->get('id')));

		header('Location: '.BASE_URL.'administrator/newReservations');
		exit;
	}

}

?>