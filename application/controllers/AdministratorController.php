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
			
			$user->set('orgnr', $_POST['orgnr']);
			$user->set('company', $_POST['company']);
			$user->set('commodity', $_POST['commodity']);
			$user->set('address', $_POST['address']);
			$user->set('zipcode', $_POST['zipcode']);
			$user->set('city', $_POST['city']);
			$user->set('country', $_POST['country']);
			$user->set('phone1', $_POST['phone1']);
			$user->set('phone2', $_POST['phone2']);
			$user->set('fax', $_POST['fax']);
			$user->set('email', $_POST['email']);
			$user->set('website', $_POST['website']);
      
			$user->set('invoice_company', $_POST['invoice_company']);
			$user->set('invoice_address', $_POST['invoice_address']);
			$user->set('invoice_zipcode', $_POST['invoice_zipcode']);
			$user->set('invoice_city', $_POST['invoice_city']);
			$user->set('invoice_country', $_POST['invoice_country']);
			$user->set('invoice_email', $_POST['invoice_email']);
			$user->set('presentation', $_POST['presentation']);
      
			$user->set('alias', $_POST['alias']);
			$user->set('name', $_POST['name']);
			$user->set('contact_phone', $_POST['phone3']);
			$user->set('contact_phone2', $_POST['phone4']);
			$user->set('contact_email', $_POST['contact_email']);
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
				$fair = new Fair;
				$fair->load($_POST['fair'], 'id');
				$this->set('d', $fair->get('url'));
				//$msg = "An organizer has created an account for you on his/her event ".BASE_URL.$fair->get('url')."\r\n\r\nUsername: ".$_POST['username']."\r\nPassword: ".$str;
				$user->setPassword($str);
				$userId = $user->save();
        $mail = new Mail($user->email, 'event_account');
        $mail->setMailVar('url', BASE_URL.$fair->get('url'));
        $mail->setMailVar('alias', $_POST['alias']);
        $mail->setMailVar('password', $str);
        $mail->send();

				//require_once ROOT.'application/models/FairUserRelation.php';
				$stmt = $this->Administrator->db->prepare("INSERT INTO fair_user_relation (fair, user, connected_time) VALUES (?, ?, ?)");
				$stmt->execute(array($_POST['fair'], $userId, time()));

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
		
		//$this->set('category_label', 'Category');
		//$this->set('customer_nr_label', 'Customer number');
    
		$this->set('headline', 'New exhibitor');
    
		$this->set('fair_label', 'Fair');

		$this->set('company_section', 'Company');
    $this->set('orgnr_label', 'Organization number');
    $this->set('company_label', 'Company');
    $this->set('commodity_label', 'Commodity');
    $this->set('address_label', 'Address');
    $this->set('zipcode_label', 'Zip code');
    $this->set('city_label', 'City');
    $this->set('country_label', 'Country');
    $this->set('phone1_label', 'Phone 1');
    $this->set('phone2_label', 'Phone 2');
    $this->set('fax_label', 'Fax number');
    $this->set('email_label', 'E-mail');
    $this->set('website_label', 'Website');
    
		$this->set('invoice_section', 'Billing address');
		$this->set('copy_label', 'Copy from company details');
    $this->set('invoice_company_label', 'Company');
    $this->set('invoice_address_label', 'Address');
    $this->set('invoice_zipcode_label', 'Zip code');
    $this->set('invoice_city_label', 'City');
    $this->set('invoice_email_label', 'E-mail');
    $this->set('presentation_label', 'Presentation');
    
		$this->set('contact_section', 'Contact person');
    $this->set('alias_label', 'Alias');
    $this->set('contact_label', 'Contact person');
    $this->set('phone3_label', 'Contact Phone');
    $this->set('phone4_label', 'Contact Phone 2');
    $this->set('contact_email', 'Contact Email');
    
		$this->set('save_label', 'Save');
	}


	/* Exportering till Excel för sidan newReservations */
	public function exportNewReservations($tbl, $cols, $rows){
		setAuthLevel(2);

		$this->set('noView', true);
		$cols = explode('|', $cols);
		$rows = explode('|', $rows);
		
		/* Samla relevant information till en array
		beroende på vilken tabell som är vald */
		$u = new User;
		$u->load($_SESSION['user_id'], 'id');

		if($tbl == 1) :
			$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, user.company, pos.id AS position, pos.name, pos.area FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ?");
			$stmt->execute(array($_SESSION['user_fair'], 2));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$arr = $result;
		elseif($tbl == 2) : 
			$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, user.company, pos.id AS position, pos.name, pos.area FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ?");
			$stmt->execute(array($_SESSION['user_fair'], 1));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$arr = $result;
		elseif($tbl == 3) :
			$stmt = $u->db->prepare("SELECT prel.*, user.id as userid, pos.area, pos.name, user.company FROM user, preliminary_booking AS prel, fair_map_position AS pos WHERE prel.fair=? AND pos.id = prel.position AND user.id = prel.user");
			$stmt->execute(array($_SESSION['user_fair']));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$arr = $result;
		endif;

		/* Har nu tabellinformationen i en array, 
		sätt in informationen i ett exceldokument 
		och skicka i headern */
		
		if($tbl == 1 )
			$filename = "BookedStandSpaces.xlsx";
		else if($tbl == 2)
			$filename = "ReservedStandSpaces.xlsx";
		else if($tbl == 3)
			$filename = "PreliminaryBookings.xlsx";

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment;filename=".$filename);
		header("Content-Transfer-Encoding: binary");
				
		require_once ROOT.'lib/PHPExcel-1.7.8/Classes/PHPExcel.php';
		
		$xls = new PHPExcel();
		
		$xls->setActiveSheetIndex(0);
		$count = 0;
		$alpha = range('A', 'Z');
		$numcols = 0;
		
		$arr2 = array('d', $this->translate->{'Stand'}, $this->translate->{'Area'}, $this->translate->{'Booked by'}, $this->translate->{'Trade'}, $this->translate->{'Time of booking'}, $this->translate->{'Message to organizer'});
		

		if(!empty($cols[1])) : 
			$xls->getActiveSheet()->SetCellValue('A1', $arr2[$cols[1]]);
		endif;

		if(!empty($cols[2])) : 
			$xls->getActiveSheet()->SetCellValue('B1', $arr2[$cols[2]]);
		endif;

		if(!empty($cols[3])) : 
			$xls->getActiveSheet()->SetCellValue('C1', $arr2[$cols[3]]);
		endif;

		if(!empty($cols[4])) : 
			$xls->getActiveSheet()->SetCellValue('D1', $arr2[$cols[4]]);
		endif;
		
		if(!empty($cols[5])) :
			$xls->getActiveSheet()->SetCellValue('E1', $arr2[$cols[5]]);
		endif;

		if(!empty($cols[6])) :
			$xls->getActiveSheet()->SetCellValue('F1', $arr2[$cols[6]]);
		endif;

		$row = 2;

		foreach($arr as $arrChild) :
			if(in_array($arrChild['id'], $rows)):
				
				
				
				$arr = array('d', $arrChild['name'], $arrChild['area'], $arrChild['company'], $arrChild['commodity'], date('d-m-Y H:i:s', $arrChild['booking_time']), $arrChild['arranger_message']);
				
				if(!empty($cols[1])) : 	
					$xls->getActiveSheet()->SetCellValue('A'.$row, $arr[$cols[1]]); 
				endif;

				if(!empty($cols[2])) : 
					$xls->getActiveSheet()->SetCellValue('B'.$row, $arr[$cols[2]]);
				endif;

				if(!empty($cols[3])) : 
					$xls->getActiveSheet()->SetCellValue('C'.$row, $arr[$cols[3]]);
				endif;

				if(!empty($cols[4])) :  
					$xls->getActiveSheet()->SetCellValue('D'.$row, $arr[$cols[4]]);
				endif;

				if(!empty($cols[5])) : 
					$xls->getActiveSheet()->SetCellValue('E'.$row, $arr[$cols[5]]);
				endif;
	
				if(!empty($cols[6])) : 
					$xls->getActiveSheet()->SetCellValue('F'.$row, $arr[$cols[6]]);
				endif;
				$row++; 
			endif;
		endforeach;
		
			
		$xls->getActiveSheet()->getStyle('A1:Z1')->applyFromArray(array(
			'font' => array('bold' => true)
		));
		
		$objWriter = new PHPExcel_Writer_Excel2007($xls);
		$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
		$objWriter->save('php://output');
	}

  // Helper function, used in /newReservations when changing current fair
  public function reservationsChangeFair($fairId=0)
  {
    $_SESSION['user_fair'] = $fairId;
		$this->set('noView', true);
    header("Location: ".BASE_URL."administrator/newReservations");
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
        $hasRights = false;
			} else {
				$this->set('hasRights', true);
        $hasRights = true;
			}
      
      // Get all available fairs
			$stmt = $this->db->prepare("SELECT id, name FROM fair_user_relation AS fur LEFT JOIN fair ON fur.fair = fair.id WHERE user = ?");
      $stmt->execute(array($_SESSION['user_id']));
			$this->set('fairs_admin', $stmt->fetchAll(PDO::FETCH_ASSOC));
      
		} elseif( userLevel()  == 3 ) {
    
			$sql = "SELECT * FROM fair WHERE created_by = ? AND id = ?";
			$prep = $this->db->prepare($sql);
			$prep->execute(array($_SESSION['user_id'], $_SESSION['user_fair']));
			$result = $prep->fetchAll();
			if(!$result) {
				$this->set('hasRights', false);
        $hasRights = false;
			} else {
				$this->set('hasRights', true);
        $hasRights = true;
      }
      
      // Get all available fairs
			$stmt = $this->db->prepare("SELECT id, name FROM fair WHERE created_by = ?");
      $stmt->execute(array($_SESSION['user_id']));
			$this->set('fairs_admin', $stmt->fetchAll(PDO::FETCH_ASSOC));
      
		} else {
    
			$this->set('hasRights', true);
      $hasRights = true;
			$this->set('accessible_maps', array());
		}
    if(!$hasRights)
      return;

		if ($action == 'deny') {
			$pb = new PreliminaryBooking;
			$pb->load($param, 'id');
			
			$u = new User;
			$u->load($pb->get('user'), 'id');
			
      $mail = new Mail($u->get('email'), 'reservation_cancelled');
      $mail->send();
			
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

		$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, user.company, pos.id AS position, pos.name, pos.area, pos.map FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ?");
		$stmt->execute(array($_SESSION['user_fair'], 2));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$positions_unfinished = $result;

	
		$positions = array();

		foreach($positions_unfinished as $pos):
			$exCat = null;
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_category_rel WHERE exhibitor=?');

			$stmt->execute(array($pos['id']));
			$poscats = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$c = 0;
			if(count($poscats) > 0) : 
				foreach($poscats as $cat):
					if($cat['category'] != 0) : 
						if($c > 0):
							$exCat.= '|'.$cat['category'];
						else:
							$exCat= $cat['category'];
							$c++;
						endif;	
					endif;
					
				endforeach;

				$catarray = array('categories'=>$exCat);
				$pos = array_merge($pos, $catarray);
			endif;

			array_push($positions, $pos);
		endforeach;
		


		$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, user.company, pos.id AS position, pos.name, pos.area, pos.map, ex.id AS posid FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ?");
		$stmt->execute(array($_SESSION['user_fair'], 1));
		
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$rpositions_unfinished = $result;
		$rpositions = array();

		foreach($rpositions_unfinished  as $pos) : 
			$exCat = null;
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_category_rel WHERE exhibitor=?');

			$stmt->execute(array($pos['id']));
			$poscats = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$c = 0;
			if(count($poscats) > 0) : 
				foreach($poscats as $cat):
					if($cat['category'] != 0) : 
						if($c > 0):
							$exCat.= '|'.$cat['category'];
						else:
							$exCat= $cat['category'];
							$c++;
						endif;	
					endif;
					
				endforeach;

				$catarray = array('categories'=>$exCat);
				$pos = array_merge($pos, $catarray);
			endif;

			array_push($rpositions, $pos);
		endforeach;


		$stmt = $u->db->prepare("SELECT prel.*, user.id as userid, pos.area, pos.name, pos.map, user.company FROM user, preliminary_booking AS prel, fair_map_position AS pos WHERE prel.fair=? AND pos.id = prel.position AND user.id = prel.user");
		$stmt->execute(array($_SESSION['user_fair']));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$prelpos = $result;

		$this->set('positions', $positions);
		$this->set('rpositions', $rpositions);
		$this->set('prelpos', $prelpos);
		$this->set('deletion_comment', 'Enter comment about deletion');
		$this->set('booked_notfound', 'No booked booths was found.');
		$this->set('reserv_notfound', 'No reservations was found.');
		$this->set('prel_notfound', 'No preliminary bookings was found.');
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
		$this->set('export', 'Export to Excel');
		$this->set('col_export_err', 'Select at least one column in order to export!');
		$this->set('row_export_err', 'Select at least one row in order to export!');
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

			$stmt4 = $this->Administrator->db->prepare("SELECT COUNT(*) AS position_count FROM fair_map_position WHERE created_by = ?");
			$stmt4->execute(array($res['id']));
			$res4 = $stmt4->fetch(PDO::FETCH_ASSOC);
			$res['position_count'] = $res4['position_count'];

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

			$stmt4 = $this->Administrator->db->prepare("SELECT COUNT(*) AS position_count FROM fair_map_position WHERE created_by = ?");
			$stmt4->execute(array($res['id']));
			$res4 = $stmt4->fetch(PDO::FETCH_ASSOC);
			$res['position_count'] = $res4['position_count'];

			$as[] = $res;
			
		}
		$this->set('headline', 'My administrators');
		$this->set('create_link', 'Create new administrator');
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

		if (empty($id))
      return;

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

    if ($id != 'new') {
    
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
      $this->Administrator->set('contact_phone', $_POST['phone3']);
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
      
        if ((!$this->Administrator->aliasExists() || $id != 'new') && isset($_POST['fair_permission']) && isset($_POST['maps'])) {
          
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
        
      } else if( $this->Administrator->emailExists() ){
      
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
    
    $this->setNoTranslate('edit_id', $id);
    $this->set('user', $this->Administrator);
    $this->set('user_maps', $this->Administrator->get('maps'));
    $this->set('user_fairs', $this->Administrator->get('fairs'));
	}

	public function deleteBooking($id = 0, $posId = 0) {
		setAuthLevel(2);

		$positionName = $_POST['positionName'];
		$comment = $_POST['comment'];
		$status = $_POST['status'];

		if($status == "Preliminary Booking"):
			$pb = new PreliminaryBooking;
			$pb->load($id, 'id');
			
			$u = new User();
			$u->load($pb->get('user'), 'id');
			$pb->delete();
		else:
			$exhib = new Exhibitor;
			$exhib->load($id, 'id');

			$u = new User;
			$u->load($exhib->get('user'), 'id');

			$stmt = $this->db->prepare("DELETE FROM exhibitor WHERE id = ? AND position = ?");
			$stmt->execute(array($id, $posId));

			$stmt = $this->db->prepare("UPDATE fair_map_position SET `status`=0 WHERE id = ?");
			$stmt->execute(array($posId));
		endif;

		if(empty($comment)):
			$comment = " ";
		endif;
    
    $mail = new Mail($u->get('email'), 'booking_cancelled');
    $mail->send();

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
