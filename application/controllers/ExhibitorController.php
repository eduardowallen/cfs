<?php
class ExhibitorController extends Controller {


	function overview($fair=0) {

		setAuthLevel(2);

		$this->set('headline', 'Exhibitor overview');
		$this->set('create_link', 'Create new exhibitor');

		if ((int)$fair > 0) {


			$this->set('headline', 'Exhibitor overview');
			$this->set('create_link', 'Create new exhibitor');

			$sql = "SELECT ";

			$stmt = $this->Exhibitor->db->prepare($sql);
			$stmt->execute(array($fair, 2));
			$res = $stmt->fetchAll();
			$users = array();
			if ($res > 0) {

				foreach ($res as $result) {
					$u = new User;
					$u->load($result['id'], 'id');
					$u->set('fair_name', $result['name']);
					$users[] = $u;
				}
				$this->set('users', $users);
			}
		}

	}
	
	public function all() {
		
		setAuthLevel(4);
		
		//$stmt = $this->Exhibitor->db->prepare("SELECT user.id, exhibitor.fair, COUNT(exhibitor.id) AS ex_count FROM user LEFT JOIN exhibitor ON user.id = exhibitor.user WHERE user.level = ? ORDER BY ?");
		$stmt = $this->Exhibitor->db->prepare("SELECT user.id, exhibitor.fair FROM user LEFT JOIN exhibitor ON user.id = exhibitor.user WHERE user.level = ? ORDER BY ?");
		$stmt->execute(array(1, 'exhibitor.fair'));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$exhibitors = array();
		$fairs = 0;
		$currentFair = 0;
		$counter = array();
		foreach ($result as $res) {
			if (intval($res['id']) > 0) {
				$ex = new User;
				$ex->load($res['id'], 'id');
				//$ex->set('ex_count', $res['ex_count']);
				
				$stmt2 = $this->Exhibitor->db->prepare("SELECT COUNT(*) AS fair_count FROM fair_user_relation WHERE user = ?");
				$stmt2->execute(array($res['id']));
				$result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
				$ex->set('fair_count', $result2['fair_count']);
				
				$exhibitors[] = $ex;
				if ($res['fair'] != $currentFair) {
					$fairs++;
					$currentFair = $res['fair'];
				}
				if (array_key_exists($res['id'], $counter))
					$counter[$res['id']] += 1;
				else
					$counter[$res['id']] = 1;
			}
		}
		
		$unique = array();
		for ($i=0; $i<count($exhibitors); $i++) {
			$exhibitors[$i]->set('ex_count', $counter[$exhibitors[$i]->get('id')]);
			if (!array_key_exists($exhibitors[$i]->get('id'), $unique))
				$unique[$exhibitors[$i]->get('id')] = $exhibitors[$i];
		}
		
		$this->set('headline', 'Exhibitors');
		$this->set('create_link', 'New exhibitor');
		$this->set('th_name', 'Name');
		$this->set('th_fairs', 'Fairs');
		$this->set('th_bookings', 'Bookings');
		$this->set('th_last_login', 'Last login');
		$this->set('th_edit', 'Edit');
		$this->set('th_delete', 'Delete');
		$this->set('fairs', $fairs);
		$this->set('', $fairs);
		$this->set('users', $unique);
		
	}
	
	public function forFair($param='', $value='') {
		
		setAuthLevel(2);
		
		if ($param == 'copy') {

			$_SESSION['copied_exhibitor'] = 'uid_'.$value;
			header('Location: '.BASE_URL.'mapTool/map/'.$_SESSION['user_fair']);
			exit;

		}
		
		$stmt = $this->Exhibitor->db->prepare("SELECT exhibitor.fair, user.id, COUNT(user.id) AS ex_count FROM user,exhibitor WHERE user.id = exhibitor.user AND user.level = ? AND exhibitor.fair = ? GROUP BY user.id ORDER BY ?");
		$stmt->execute(array(1, $_SESSION['user_fair'], 'fair, user.company'));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$exhibitors = array();
		$connected = array();
		$exIds = array();
		$fairs = 0;
		$currentFair = 0;
		foreach ($result as $res) {
			if (intval($res['id']) > 0) {
				array_push($exIds, $res['id']);
				$ex = new User;
				$ex->load($res['id'], 'id');
				$ex->set('ex_count', $res['ex_count']);
				
				$stmt2 = $this->Exhibitor->db->prepare("SELECT COUNT(*) AS fair_count FROM fair_user_relation WHERE user = ?");
				$stmt2->execute(array($res['id']));
				$result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
				$ex->set('fair_count', $result2['fair_count']);
				
				$exhibitors[] = $ex;
				if ($res['fair'] != $currentFair) {
					$fairs++;
					$currentFair = $res['fair'];
				}
			}
		}
		
		$stmt = $this->Exhibitor->db->prepare("SELECT fair_user_relation.user FROM fair_user_relation LEFT JOIN user ON fair_user_relation.user = user.id WHERE fair_user_relation.fair = ? AND user.level = ? ORDER BY user.company");
		$stmt->execute(array($_SESSION['user_fair'], 1));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $res) {
			if (!in_array($res['user'], $exIds)) {
				$ex = new User;
				$ex->load($res['user'], 'id');
				
				$stmt2 = $this->Exhibitor->db->prepare("SELECT COUNT(*) AS fair_count FROM fair_user_relation WHERE user = ?");
				$stmt2->execute(array($res['user']));
				$result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
				$ex->set('fair_count', $result2['fair_count']);
				
				$connected[] = $ex;
			}
		}
		
		$this->set('table_exhibitors', 'Booked exhibitors');
		$this->set('table_connected', 'Connected exhibitors');
		$this->set('headline', 'Exhibitors');
		$this->set('headline', 'Exhibitors');
		$this->set('create_link', 'New exhibitor');
		$this->set('th_company', 'Company');
		$this->set('th_name', 'Name');
		$this->set('th_fairs', 'Fairs');
		$this->set('th_bookings', 'Bookings');
		$this->set('th_last_login', 'Last login');
		$this->set('th_edit', 'Edit');
		$this->set('th_delete', 'Delete');
		$this->set('fairs', $fairs);
		$this->set('', $fairs);
		$this->set('users', $exhibitors);
		$this->set('connected', $connected);
		$this->set('th_copy', 'Copy to map');
		
	}
	
	public function exhibitors($fairId=0) {

		setAuthLevel(0);

		$this->set('headline', 'Exhibitor overview');
		$this->set('create_link', 'Create new exhibitor');
		$this->set('th_status', 'Status');
		$this->set('th_branch', 'Branch');
		$this->set('th_name', 'Stand space');
		$this->set('th_company', 'Company');
		$this->set('th_phone', 'Phone number');
		$this->set('th_contact', 'Name');
		$this->set('th_email', 'E-mail');
		$this->set('th_website', 'Website');
		$this->set('th_view', 'View');
		$this->set('th_profile', 'Details');
		$this->set('export_button', 'Export as excel');
		$this->set('fairId', $fairId);

		$sql = 'SELECT user.*, exhibitor.position AS position, exhibitor.fair AS fair, exhibitor.commodity AS excommodity, pos.name AS posname, pos.status AS posstatus, pos.map AS posmap FROM exhibitor, user, fair_map_position AS pos WHERE exhibitor.fair = ? AND exhibitor.position = pos.id AND exhibitor.user = user.id';
		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($fairId));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$this->set('exhibitors', $result);

	}
	
	public function export($fairId=0) {
		
		setAuthLevel(3);
		$this->set('noView', true);
		
		$fair = new Fair;
		
		if ($fairId > 0) {
			$fair->load($fairId, 'id');
		} else {
			if (isset($_SESSION['user_fair']))
				$fair->load($_SESSION['user_fair'], 'id');
			else if (isset($_SESSION['outside_fair_url']))
				$fair->load($_SESSION['outside_fair_url'], 'url');
		}
		
		$data = array();
		
		if(is_array($fair->get('maps'))) {
			foreach ($fair->get('maps') as $map) {
				foreach ($map->get('positions') as $pos) {
					
					if ($pos->get('exhibitor')) {
						
						$commodity = $pos->get('exhibitor')->get('commodity');
						$commodity = (empty($commodity)) ? $pos->get('user')->get('commodity') : $pos->get('exhibitor')->get('commodity');
						
						$data[] = array(
							$this->translate->{$pos->get('statusText')},
							$pos->get('name'),
							$pos->get('exhibitor')->get('company'),
							$pos->get('exhibitor')->get('name'),
							$commodity,
							$pos->get('exhibitor')->get('phone1'),
							$pos->get('exhibitor')->get('email'),
							$pos->get('exhibitor')->get('website')
						);
					}
					
				}
			}
		}
		
		$filename = "exhibitors.xlsx";
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment;filename=".$filename);
		header("Content-Transfer-Encoding: binary ");
		
		require_once ROOT.'lib/PHPExcel-1.7.8/Classes/PHPExcel.php';
		
		$xls = new PHPExcel();
		
		$xls->setActiveSheetIndex(0);
		
		$xls->getActiveSheet()->SetCellValue('A1', $this->translate->{'Status'});
		$xls->getActiveSheet()->SetCellValue('B1', $this->translate->{'Stand space'});
		$xls->getActiveSheet()->SetCellValue('C1', $this->translate->{'Company'});
		$xls->getActiveSheet()->SetCellValue('D1', $this->translate->{'Name'});
		$xls->getActiveSheet()->SetCellValue('E1', $this->translate->{'Commodity'});
		$xls->getActiveSheet()->SetCellValue('F1', $this->translate->{'Phone number'});
		$xls->getActiveSheet()->SetCellValue('G1', $this->translate->{'Email'});
		$xls->getActiveSheet()->SetCellValue('H1', $this->translate->{'Website'});
		
		$i = 2;
		foreach ($data as $row) {
			$xls->getActiveSheet()->SetCellValue('A'.$i, $row[0]);
			$xls->getActiveSheet()->SetCellValue('B'.$i, $row[1]);
			$xls->getActiveSheet()->SetCellValue('C'.$i, $row[2]);
			$xls->getActiveSheet()->SetCellValue('D'.$i, $row[3]);
			$xls->getActiveSheet()->SetCellValue('E'.$i, $row[4]);
			$xls->getActiveSheet()->SetCellValueExplicit('F'.$i, $row[5], PHPEXcel_Cell_Datatype::TYPE_STRING);
			$xls->getActiveSheet()->SetCellValue('G'.$i, $row[6]);
			$xls->getActiveSheet()->SetCellValue('H'.$i, $row[7]);
			$i++;
		}
		
		$xls->getActiveSheet()->getStyle('A1:H1')->applyFromArray(array(
			'font' => array('bold' => true)
		));
		
		$objWriter = new PHPExcel_Writer_Excel2007($xls);
		//$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
		$objWriter->save('php://output');
		
	}

	function createFromMap($fairUrl) {

		setAuthLevel(2);

		$error = '';

		$this->User = new User($this->Exhibitor->db);

		if (isset($_POST['save'])) {

			$this->User->set('company', $_POST['company']);
			$this->User->set('name', $_POST['name']);
			$this->User->set('orgnr', $_POST['orgnr']);
			$this->User->set('address', $_POST['address']);
			$this->User->set('zipcode', $_POST['zipcode']);
			$this->User->set('city', $_POST['city']);
			$this->User->set('invoice_company', $_POST['invoice_company']);
			$this->User->set('invoice_address', $_POST['invoice_address']);
			$this->User->set('invoice_zipcode', $_POST['invoice_zipcode']);
			$this->User->set('invoice_city', $_POST['invoice_city']);
			$this->User->set('country', $_POST['country']);
			$this->User->set('phone1', $_POST['phone1']);
			$this->User->set('phone2', $_POST['phone2']);
			$this->User->set('phone3', $_POST['phone3']);
			$this->User->set('fax', $_POST['fax']);
			$this->User->set('website', $_POST['website']);
			$this->User->set('email', $_POST['email']);
			$this->User->set('presentation', $_POST['presentation']);
			$this->User->set('invoice_email', $_POST['invoice_email']);
			$this->User->set('commodity', $_POST['commodity']);
			$this->User->set('category', 0);
			$this->User->set('level', 1);
			$this->User->set('locked', 0);
			$this->User->set('alias', $_POST['alias']);
			
			if (!preg_match('/\d{3}(\s|\-)?\d+/', $_POST['zipcode'])) {
				$error.= 'The ZIP code should be in the format xxx-xx';
			} else if ($this->User->aliasExists()) {
				$error.= 'The username already exists in our system.';
			} else if ($this->User->emailExists()) {
				$error.= 'The email address already exists in our system.';
			} else {

				$pw_arr = array_merge(range(0, 9), range('a', 'z'));
				shuffle($pw_arr);
				$password = substr(implode('', $pw_arr), 0, 10);

				$this->User->setPassword($password);
				$userId = $this->User->save();

				$hash = md5($this->User->get('email').BASE_URL.$userId);
				$url = BASE_URL.'user/confirm/'.$userId.'/'.$hash;


				if ($fairUrl != '') {
					$fair = new Fair($this->Exhibitor->db);
					$fair->load($fairUrl, 'url');

					$str = 'Welcome to Chartbooker'."\r\n\r\n";
					$str.= 'Someone has registered this e-mail address for the fair '.$fair->get('name')."\r\n";
					$str.= 'Username: '.$_POST['alias']."\r\n";
					$str.= 'Password: '.$password."\r\n";
					$str.= 'Access level: Participant'."\r\n";
					$str.= 'Please note that the opening date for bookings is '.date("Y-m-d h:m:s", $fair->get('auto_publish'));

					sendMail($_POST['email'], 'Your user account', $str);

					require_once ROOT.'application/models/Exhibitor.php';
					require_once ROOT.'application/models/ExhibitorCategory.php';
					require_once ROOT.'application/models/Fair.php';
					require_once ROOT.'application/models/FairMap.php';
					require_once ROOT.'application/models/FairMapPosition.php';
					require_once ROOT.'application/models/PreliminaryBooking.php';
					require_once ROOT.'application/models/FairUserRelation.php';
					if ($fair->wasLoaded()) {
						$ful = new FairUserRelation;
						$ful->set('user', $userId);
						$ful->set('fair', $fair->get('id'));
						$ful->save();
					}
				} else {
					$str = 'Welcome to Chartbooker'."\r\n\r\n";
					$str.= 'Username: '.$_POST['alias']."\r\n";
					$str.= 'Password: '.$password."\r\n";
					$str.= 'Access level: Participant';

					sendMail($_POST['email'], 'Your user account', $str);
				}

				header('Location: '.BASE_URL.'exhibitor/createFromMap/'.$fairUrl);

			}

		}

		$this->set('error', $error);
		$this->setNoTranslate('fair_url', $fairUrl);
		$this->set('user', $this->User);
		$fair = new Fair($this->User->db);
		$fair->load($_SESSION['outside_fair_url'], 'url');
		$this->set('fair', $fair);
		
		$this->set('alias_label', 'Username');
		$this->set('company_section', 'Company');
		$this->set('invoice_section', 'Billing address');
		$this->set('contact_section', 'Contact');
		$this->set('presentation_section', 'Presentation');

		$this->set('headline', 'Register');
		$this->set('company_label', 'Company');
		$this->set('commodity_label', 'Commodity');
		$this->set('presentation_label', 'Presentation');
		$this->set('customer_nr_label', 'Customer number');
		$this->set('contact_label', 'Contact person');
		$this->set('orgnr_label', 'Organization number');
		$this->set('address_label', 'Address');
		$this->set('zipcode_label', 'Zip code');
		$this->set('city_label', 'City');
		$this->set('invoice_company_label', 'Company');
		$this->set('invoice_address_label', 'Address');
		$this->set('invoice_zipcode_label', 'Zip code');
		$this->set('invoice_city_label', 'City');
		$this->set('invoice_email_label', 'E-mail');
		$this->set('country_label', 'Country');
		$this->set('phone1_label', 'Phone 1');
		$this->set('phone2_label', 'Phone 2');
		$this->set('phone3_label', 'Phone 3');
		$this->set('fax_label', 'Fax number');
		$this->set('website_label', 'Website');
		$this->set('email_label', 'E-mail');
		$this->set('save_label', 'Save');
		$this->set('copy_label', 'Copy from company details');

	}

	function profile($id) {

		setAuthLevel(1);

		$u = new User;
		$u->load($id, 'id');
		
		//Masters get the full list of positions, lower levels get the ones for their fair
		if (userLevel() == 4) {
			$stmt = $u->db->prepare("SELECT position FROM exhibitor WHERE user = ?");
			$stmt->execute(array($u->get('id')));
		} else {
			$stmt = $u->db->prepare("SELECT position FROM exhibitor WHERE user = ? AND fair = ?");
			$stmt->execute(array($u->get('id'), $_SESSION['user_fair']));
		}
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$positions = array();

		foreach($u->getPreliminaries() as $prel) {
			$pos = new FairMapPosition;
			$pos->load($prel['position'], 'id');
			$ex = new Exhibitor;
			$ex->set('commodity', $prel['commodity']);
			$ex->set('arranger_message', $prel['arranger_message']);
			$pos->set('exhibitor', $ex);
			$positions[] = $pos;
		}

		foreach ($result as $res) {
			$pos = new FairMapPosition;
			$pos->load($res['position'], 'id');
			$positions[] = $pos;
		}
		
		/*
		if (isset($_POST['ban_save'])) {
			
			$stmt = $u->db->prepare("DELETE FROM user_ban WHERE user = ? AND organizer = ?");
			$stmt->execute(array($u->get('id'), $_SESSION['user_id']));
			
			$ban = new UserBan;
			$ban->set('user', $u->get('id'));
			$ban->set('organizer', $_SESSION['user_id']);
			$ban->set('reason', $_POST['ban_msg']);
			$ban->save();
			
		}
		*/

		$this->set('user', $u);
		$this->set('positions', $positions);
		$this->set('headline', 'Exhibitor profile');

		$this->set('tr_pos', 'Stand space');
		$this->set('tr_area', 'Area');
		$this->set('tr_booker', 'Booked by');
		$this->set('tr_field', 'Trade');
		$this->set('tr_time', 'Time of booking');
		$this->set('tr_message', 'Message to organizer');

		$this->set('company_section', 'Company');
		$this->set('invoice_section', 'Billing address');
		$this->set('contact_section', 'Contact person');
		$this->set('presentation_section', 'Presentation');
		$this->set('bookings_section', 'Bookings');

		$this->set('invoice_address_label', 'Address');
		$this->set('invoice_zipcode_label', 'Zip code');
		$this->set('invoice_city_label', 'City');
		$this->set('invoice_email_label', 'E-mail');

		$this->set('company_label', 'Company');
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
		$this->set('presentation_label', 'Company presentation');
		$this->set('save_label', 'Save');
		
		//$this->set('ban_section_header', 'Ban user');
		//$this->set('ban_msg_label', 'Reason for ban');
		//$this->set('ban_save', 'Save');


	}

	function myBookings() {
		$this->set('headline', 'My bookings');
		$this->set('rheadline', 'Reservations');

		setAuthLevel(1);

		$u = new User;
		$u->load($_SESSION['user_id'], 'id');

		$stmt = $u->db->prepare("SELECT exhibitor.position FROM exhibitor LEFT JOIN fair_map_position AS pos ON exhibitor.position = pos.id WHERE exhibitor.user = ? AND exhibitor.fair = ? AND pos.status = ?");
		$stmt->execute(array($_SESSION['user_id'], $_SESSION['user_fair'], 2));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$positions = array();

		foreach ($result as $res) {
			$pos = new FairMapPosition;
			$pos->load($res['position'], 'id');
			$positions[] = $pos;
		}
		
		$stmt = $u->db->prepare("SELECT exhibitor.position FROM exhibitor LEFT JOIN fair_map_position AS pos ON exhibitor.position = pos.id WHERE exhibitor.user = ? AND exhibitor.fair = ? AND pos.status = ?");
		$stmt->execute(array($_SESSION['user_id'], $_SESSION['user_fair'], 1));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$rpositions = array();

		foreach ($result as $res) {
			$pos = new FairMapPosition;
			$pos->load($res['position'], 'id');
			$rpositions[] = $pos;
		}
		
		$prelpos = array();

		foreach($u->getPreliminaries() as $prel) {
			$pos = new FairMapPosition;
			$pos->load($prel['position'], 'id');
			$ex = new Exhibitor;
			$ex->set('commodity', $prel['commodity']);
			$ex->set('arranger_message', $prel['arranger_message']);
			$pos->set('exhibitor', $ex);
			$pos->get('exhibitor')->set('company', 'Myself');
			$prelpos[] = $pos;
		}

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

		$this->set('confirm_delete', 'Are you sure?');
	}

	function edit($fair, $id) {
		setAuthLevel(1);
		$this->set('headline', 'Edit exhibitor');
		$this->set('cat_label', 'Category');
		$this->set('save_label', 'Save');
		$this->set('fairId', $fair);
		$this->set('userId', $id);

		if (isset($_POST['save'])) {
			$stmt = $this->Exhibitor->db->prepare("UPDATE exhibitor SET category = ? WHERE id = ?");
			$stmt->execute(array($_POST['category'], $id));
		}

		$u = new Exhibitor();
		$u->load($id, 'id');

		$opts = makeOptions($this->Exhibitor->db, 'exhibitor_category', $u->get('category'), 'fair='.$fair);
		$this->set('cat_options', $opts);


	}
	
	function deleteAccount($user_id) {
		setAuthLevel(4);
		$user = new User;
		$user->load($user_id, 'id');
		if ($user->wasLoaded()) {
			
			$stmt = $user->db->prepare("SELECT position FROM exhibitor WHERE user = ?");
			$stmt->execute(array($user->get('id')));
			foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $res) {
				$upd = $user->db->prepare("UPDATE fair_map_position SET status = ? WHERE id = ?");
				$upd->execute(array(0, $res['position']));
			}
			$del = $user->db->prepare("DELETE FROM exhibitor WHERE user = ?");
			$del->execute(array($user->get('id')));
			
			$user->delete();
			header('Location: '.BASE_URL.'exhibitor/all');
			exit;
		}
	}

	function pre_delete($id, $user_id, $position){
		setAuthLevel(1);
		$this->Exhibitor->del_pre_booking($id, $user_id, $position);
		header('Location: '.BASE_URL.'exhibitor/myBookings');
	}

	function delete($id, $user_id, $position){
		setAuthLevel(2);
		$this->Exhibitor->del_booking($id, $user_id, $position);
		header('Location: '.BASE_URL.'exhibitor/myBookings');
	}
}
?>
