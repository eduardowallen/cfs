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
		$this->setNoTranslate('fair', $fair);

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
			$this->setNoTranslate('users', $users);
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
		$this->setNoTranslate('fair', $fair);

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
				$this->setNoTranslate('d', $fair->get('url'));
				//$msg = "An organizer has created an account for you on his/her event ".BASE_URL.$fair->get('url')."\r\n\r\nUsername: ".$_POST['username']."\r\nPassword: ".$str;
				$user->setPassword($str);
				$userId = $user->save();

				$me = new User;
				$me->load($_SESSION['user_id'], 'id');

				$mail = new Mail($user->email, 'event_account');
				$mail->setMailVar('url', BASE_URL.$fair->get('url'));
				$mail->setMailVar('alias', $_POST['alias']);
				$mail->setMailVar('password', $str);
				$mail->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
				$mail->setMailVar('creator_name', $me->get('name'));
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

		if (userLevel() == 2) {
			foreach ($this->Administrator->get('fairs') as $fId) {
				$fair = new Fair;
				$fair->load($fId, 'id');
				$fairs[] = $fair;
			}

		} else if (userLevel() == 3) {
			$stmt = $this->db->prepare("SELECT id FROM fair WHERE created_by = ?");
			$stmt->execute(array($_SESSION['user_id']));
			$owned_fairs = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ($owned_fairs as $fair_data) {
				$fair = new Fair;
				$fair->load($fair_data['id'], 'id');
				$fairs[] = $fair;
			}
		}

		$this->setNoTranslate('fairs', $fairs);

		$this->setNoTranslate('error', $error);
		$this->setNoTranslate('user', $user);
		
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
	public function exportNewReservations($tbl){
		setAuthLevel(2);
		$this->setNoTranslate('noView', true);

		if (isset($_POST['rows'], $_POST['field']) && is_array($_POST['rows']) && is_array($_POST['field'])) {

			/* Samla relevant information till en array
			beroende på vilken tabell som är vald */
			$u = new User;
			$u->load($_SESSION['user_id'], 'id');

			if ($tbl == 1) {
				$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, user.*, pos.name AS position, pos.area FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ? AND ex.id IN (" . implode(',', $_POST['rows']) . ")");
				$stmt->execute(array($_SESSION['user_fair'], 2));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 2) {
				$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, user.*, pos.name AS position, pos.area, pos.expires FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ? AND ex.id IN (" . implode(',', $_POST['rows']) . ")");
				$stmt->execute(array($_SESSION['user_fair'], 1));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 3) {
				$stmt = $u->db->prepare("SELECT prel.*, user.id as userid, user.*, pos.area, pos.name AS position, user.company FROM user, preliminary_booking AS prel, fair_map_position AS pos WHERE prel.fair = ? AND pos.id = prel.position AND user.id = prel.user AND prel.id IN (" . implode(',', $_POST['rows']) . ")");
				$stmt->execute(array($_SESSION['user_fair']));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}

			/* Har nu tabellinformationen i en array, 
			sätt in informationen i ett exceldokument 
			och skicka i headern */
			
			if ($tbl == 1) {
				$filename = "BookedStandSpaces.xlsx";
				$label_status = $this->translate->{'Booked'};
			} else if ($tbl == 2) {
				$filename = "ReservedStandSpaces.xlsx";
				$label_status = $this->translate->{'Reserved'};
			} else if ($tbl == 3) {
				$filename = "PreliminaryBookings.xlsx";
				$label_status = $this->translate->{'Preliminary booked'};
			}

			if ($tbl < 3) {
				$stmt_options = $this->db->prepare("SELECT GROUP_CONCAT(feo.text SEPARATOR ', ') AS texts FROM fair_extra_option AS feo INNER JOIN exhibitor_option_rel AS eor ON eor.option = feo.id WHERE exhibitor = ?");
			}

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

			$alpha = range('A', 'Z');
			if (count($_POST['field']) > count($alpha)) {
				foreach ($alpha as $letter) {
					$alpha[] = 'A' . $letter;
				}
			}

			$column_names = array(
				'orgnr' => $this->translate->{'Organization number'},
				'company' => $this->translate->{'Company'},
				'commodity' => $this->translate->{'Commodity'},
				'address' => $this->translate->{'Address'},
				'zipcode' => $this->translate->{'Zip code'},
				'city' => $this->translate->{'City'},
				'country' => $this->translate->{'Country'},
				'phone1' => $this->translate->{'Phone 1'},
				'phone2' => $this->translate->{'Phone 2'},
				'fax' => $this->translate->{'Fax number'},
				'email' => $this->translate->{'E-mail'},
				'website' => $this->translate->{'Website'},
				'invoice_company' => $this->translate->{'Company'},
				'invoice_address' => $this->translate->{'Address'},
				'invoice_zipcode' => $this->translate->{'Zip code'},
				'invoice_city' => $this->translate->{'City'},
				'invoice_country' => $this->translate->{'Country'},
				'invoice_email' => $this->translate->{'E-mail'},
				'name' => $this->translate->{'Contact person'},
				'contact_phone' => $this->translate->{'Contact Phone'},
				'contact_phone2' => $this->translate->{'Contact Phone 2'},
				'contact_email' => $this->translate->{'Contact Email'},
				'status' => $this->translate->{'Status'},
				'position' => $this->translate->{'Stand'},
				'area' => $this->translate->{'Area'},
				'commodity' => $this->translate->{'Trade'},
				'extra_options' => $this->translate->{'Extra options'},
				'booking_time' => $this->translate->{'Time of booking'},
				'edit_time' => $this->translate->{'Last edited'},
				'arranger_message' => $this->translate->{'Message to organizer'},
				'expires' => $this->translate->{'Reserved until'}
			);

			//Prelbooking does not have `Last edited`
			if ($tbl !== 3) {
				$column_names[] = $this->translate->{'Last edited'};
			}

			$i = 0;
			foreach ($_POST['field'] as $fieldname => $humbug) {
				$xls->getActiveSheet()->SetCellValue($alpha[$i] . '1', $column_names[$fieldname]);
				++$i;
			}

			// Row 1 in the sheet is now done, continue with data on row 2
			$row_idx = 2;

			// Start outputing the actual booking data into the spreadsheet
			foreach ($data_rows as $row) {

				$i = 0;

				foreach ($_POST['field'] as $fieldname => $humbug) {

					if ($fieldname == 'booking_time') {
						$value = date('d-m-Y H:i:s', $row['booking_time']);

					} else if ($fieldname == 'edit_time') {
						$value = ($row['edit_time'] > 0 ? date('d-m-Y H:i:s', $row['edit_time']) : '');

					} else if ($fieldname == 'status') {
						$value = $label_status;

					} else if ($fieldname == 'extra_options') {
						$value = '';
						if ($tbl == 3) {
							$option_texts = array();
							$options = explode('|', $row['options']);

							foreach ($options as $option_id) {
								$option = new FairExtraOption($option_id);
								if ($option->wasLoaded()) {
									$option_texts[] = $option->get('text');
								}
							}

							$value = implode(', ', $option_texts);

						} else {
							$stmt_options->execute(array($row['id']));
							$options = $stmt_options->fetchObject();
							if ($options) {
								$value = $options->texts;
							}
						}

					} else {
						$value = $row[$fieldname];
					}

					$xls->getActiveSheet()->SetCellValue($alpha[$i] . $row_idx, $value);
					++$i;
				}

				// Next row in spreadsheet
				++$row_idx;
			}
			
				
			$xls->getActiveSheet()->getStyle('A1:AZ1')->applyFromArray(array(
				'font' => array('bold' => true)
			));
			
			$objWriter = new PHPExcel_Writer_Excel2007($xls);
			// $objWriter->save(str_replace('.php', '.xlsx', __FILE__));
			$objWriter->save('php://output');
		}
	}

  // Helper function, used in /newReservations when changing current fair
  public function reservationsChangeFair($fairId=0)
  {
    $_SESSION['user_fair'] = $fairId;
		$this->setNoTranslate('noView', true);
    header("Location: ".BASE_URL."administrator/newReservations");
  }

	public function newReservations($action='', $param='') {

		setAuthLevel(2);

		$fair = new Fair;
		$fair->load($_SESSION['user_fair'], 'id');

		$this->setNoTranslate('fair', $fair);
		
		if( userLevel() == 2 ){
			$sql = "SELECT * FROM fair_user_relation WHERE user = ? AND fair = ?";
			$prep = $this->db->prepare($sql);
			$prep->execute(array($_SESSION['user_id'], $fair->get('id')));
			$result = $prep->fetch(PDO::FETCH_ASSOC);
			$this->setNoTranslate('accessible_maps', explode('|', $result['map_access']));
			if(!$result) {
				$this->setNoTranslate('hasRights', false);
				$hasRights = false;
			} else {
				$this->setNoTranslate('hasRights', true);
				$hasRights = true;
			}

			// Get all available fairs
			$stmt = $this->db->prepare("SELECT id, name FROM fair_user_relation AS fur LEFT JOIN fair ON fur.fair = fair.id WHERE user = ?");
			$stmt->execute(array($_SESSION['user_id']));
			$this->setNoTranslate('fairs_admin', $stmt->fetchAll(PDO::FETCH_ASSOC));

		} elseif( userLevel()  == 3 ) {

			$sql = "SELECT * FROM fair WHERE created_by = ? AND id = ?";
			$prep = $this->db->prepare($sql);
			$prep->execute(array($_SESSION['user_id'], $_SESSION['user_fair']));
			$result = $prep->fetchAll();
			if(!$result) {
				$this->setNoTranslate('hasRights', false);
				$hasRights = false;
			} else {
				$this->setNoTranslate('hasRights', true);
				$hasRights = true;
			}

			// Get all available fairs
			$stmt = $this->db->prepare("SELECT id, name FROM fair WHERE created_by = ?");
			$stmt->execute(array($_SESSION['user_id']));
			$this->setNoTranslate('fairs_admin', $stmt->fetchAll(PDO::FETCH_ASSOC));

		} else {

			$this->setNoTranslate('hasRights', true);
			$hasRights = true;
			$this->setNoTranslate('accessible_maps', array());
		}

		if (!$hasRights)
			return;

		// Jag tror att den här koden inte används längre...
		if ($action == 'deny') {
			$pb = new PreliminaryBooking;
			$pb->load($param, 'id');
			
			$u = new User;
			$u->load($pb->get('user'), 'id');
			
			//Check mail settings and send only if setting is set
			if ($fair->wasLoaded()) {
				$mailSettings = json_decode($fair->get("mail_settings"));
				if (is_array($mailSettings->reservationCancelled) && in_array("1", $mailSettings->reservationCancelled)) {
					$mail = new Mail($u->get('email'), 'reservation_cancelled', $fair->get("url") . "@chartbooker.com", $fair->get("name"));
					$mail->send();
				}
			}
			
			$pb->delete();
			header("Location: ".BASE_URL."administrator/newReservations");
			exit;// Slutar här
		} else if ($action == 'approve' && isset($_POST['id'])) {

			$pb = new PreliminaryBooking;
			$pb->load($_POST['id'], 'id');

			if ($pb->wasLoaded()) {
				$pos = new FairMapPosition;
				$pos->load($pb->get('position'), 'id');
				$pos->set('status', 2);

				$ex = new Exhibitor;
				$ex->set('user', $pb->get('user'));
				$ex->set('fair', $pb->get('fair'));
				$ex->set('position', $pb->get('position'));
				$ex->set('category', 0);
				$ex->set('presentation', '');
				$ex->set('commodity', $_POST['commodity']);
				$ex->set('arranger_message', $_POST['message']);
				$ex->set('approved', 1);
				$ex->set('edit_time', time());
				
				$exId = $ex->save();
				$pos->save();
				$pb->delete();

				$stmt = $pb->db->prepare("DELETE FROM preliminary_booking WHERE position = ?");
				$stmt->execute(array($pos->get('id')));

				$categories = array();
				if (isset($_POST['categories']) && is_array($_POST['categories'])) {

					$stmt = $pb->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");

					foreach ($_POST['categories'] as $category_id) {
						$stmt->execute(array($exId, $category_id));

						$ex_category = new ExhibitorCategory();
						$ex_category->load($category_id, 'id');
						$categories[] = $ex_category->get('name');
					}
				}

				$options = array();
				if (isset($_POST['options']) && is_array($_POST['options'])) {

					$stmt = $pb->db->prepare("INSERT INTO exhibitor_option_rel (exhibitor, `option`) VALUES (?, ?)");

					foreach ($_POST['options'] as $option_id) {
						$stmt->execute(array($exId, $option_id));

						$ex_option = new FairExtraOption();
						$ex_option->load($option_id, 'id');
						$options[] = $ex_option->get('text');
					}
				}

				// Send mail
				$categories = implode(', ', $categories);
				$options = implode(', ', $options);
				$time_now = date('d-m-Y H:i');

				$organizer = new User();
				$organizer->load($fair->get('created_by'), 'id');

				$ex_user = new User();
				$ex_user->load($ex->get('user'), 'id');

				//Check mail settings and send only if setting is set
				if ($fair->wasLoaded()) {
					$mailSettings = json_decode($fair->get("mail_settings"));
					if (is_array($mailSettings->bookingEdited)) {
						if (in_array("0", $mailSettings->bookingEdited)) {
<<<<<<< HEAD
							$mail_organizer = new Mail($organizer->get('email'), 'preliminary_to_booked_confirm', $fair->get("url") . "@chartbooker.com", $fair->get("name"));
							$mail_organizer->setMailvar("exhibitor_name", $ex_user->get("name"));
							$mail_organizer->setMailvar("event_name", $fair->get("name"));
							$mail_organizer->setMailVar('position_name', $pos->get('name'));
							$mail_organizer->setMailVar("booking_time", date('d-m-Y H:i:s', intval($ex->get("booking_time"))));
							$mail_organizer->setMailVar("url", BASE_URL . $fair->get("url"));
							$mail_organizer->setMailVar('position_information', $pos->get('information'));
							$mail_organizer->setMailVar('arranger_message', $_POST['message']);
							$mail_organizer->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_organizer->setMailVar('exhibitor_category', $categories);
							$mail_organizer->setMailVar('exhibitor_options', $options);
							$mail_organizer->setMailVar('edit_time', $time_now);
=======
							$mail_organizer = new Mail($organizer->get('email'), 'booking_edited_confirm', $fair->get("url") . "@chartbooker.com", $fair->get("name"));
							$mail_organizer->setMailVar('position_name', $pos->get('name'));
							$mail_organizer->setMailVar('position_information', $pos->get('information'));
							$mail_organizer->setMailVar('edit_time', $time_now);
							$mail_organizer->setMailVar('arranger_message', $_POST['arranger_message']);
							$mail_organizer->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_organizer->setMailVar('exhibitor_category', $categories);
>>>>>>> 1d9c5429b5a4a56db505c4a90d4223ece15c71ae
							$mail_organizer->send();
						}
						if (in_array("1", $mailSettings->bookingEdited)) {
							$mail_user = new Mail($ex_user->get('email'), 'preliminary_to_booked', $fair->get("url") . "@chartbooker.com", $fair->get("name"));
							$mail_user->setMailvar("exhibitor_name", $ex_user->get("name"));
							$mail_user->setMailvar("event_name", $fair->get("name"));
							$mail_user->setMailVar('position_name', $pos->get('name'));
							$mail_user->setMailVar("booking_time", date('d-m-Y H:i:s', intval($ex->get("booking_time"))));
							$mail_user->setMailVar("url", BASE_URL . $fair->get("url"));
							$mail_user->setMailVar('position_information', $pos->get('information'));
<<<<<<< HEAD
							$mail_user->setMailVar('arranger_message', $_POST['message']);
							$mail_user->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_user->setMailVar('exhibitor_category', $categories);
							$mail_user->setMailVar('exhibitor_options', $options);
							$mail_user->setMailVar('edit_time', $time_now);
=======
							$mail_user->setMailVar('arranger_message', $_POST['arranger_message']);
							$mail_user->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_user->setMailVar('exhibitor_category', $categories);
>>>>>>> 1d9c5429b5a4a56db505c4a90d4223ece15c71ae
							$mail_user->send();
						}
					}
				}
			}
			
			if (isset($_POST["redirect"])) {
				header("location: {$_POST["redirect"]}");
			} else {
				header("Location: ".BASE_URL."administrator/newReservations");
			}
			exit;
			
		}

		$this->set('headline', 'Booked stand spaces');
		$this->set('rheadline', 'Reservations');

		$u = new User;
		$u->load($_SESSION['user_id'], 'id');

		/* Boookings */
		$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, user.company, pos.id AS position, pos.name, pos.area, pos.map FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ?");
		$stmt->execute(array($_SESSION['user_fair'], 2));
		$positions_unfinished = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$positions = array();

		foreach ($positions_unfinished as $pos) {
			/* Get categories */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_category_rel WHERE exhibitor = ? AND category > 0');
			$stmt->execute(array($pos['id']));
			$poscats = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$categories = array();
			if (count($poscats) > 0) {
				foreach ($poscats as $cat) {
					$categories[] = $cat['category'];
				}
			}
<<<<<<< HEAD

			$pos['categories'] = implode('|', $categories);

			/* Get extra options */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_option_rel WHERE exhibitor = ? AND `option` > 0');
			$stmt->execute(array($pos['id']));
			$posoptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$options = array();
			if (count($posoptions) > 0) {
				foreach ($posoptions as $option) {
					$options[] = $option['option'];
				}
			}

=======

			$pos['categories'] = implode('|', $categories);

			/* Get extra options */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_option_rel WHERE exhibitor = ? AND `option` > 0');
			$stmt->execute(array($pos['id']));
			$posoptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$options = array();
			if (count($posoptions) > 0) {
				foreach ($posoptions as $option) {
					$options[] = $option['option'];
				}
			}

>>>>>>> 1d9c5429b5a4a56db505c4a90d4223ece15c71ae
			$pos['options'] = implode('|', $options);
			$positions[$pos['position']] = $pos;
		}

		/* Reservations */
		$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, user.company, pos.id AS position, pos.name, pos.area, pos.map, ex.id AS posid, pos.expires FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ?");
		$stmt->execute(array($_SESSION['user_fair'], 1));
		$rpositions_unfinished = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$rpositions = array();

		foreach ($rpositions_unfinished  as $pos) {
			/* Get categories */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_category_rel WHERE exhibitor = ? AND category > 0');
			$stmt->execute(array($pos['id']));
			$poscats = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$categories = array();
			if (count($poscats) > 0) {
				foreach ($poscats as $cat) {
					$categories[] = $cat['category'];
				}
			}
<<<<<<< HEAD

			$pos['categories'] = implode('|', $categories);

			/* Get extra options */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_option_rel WHERE exhibitor = ? AND `option` > 0');
			$stmt->execute(array($pos['id']));
			$posoptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

=======

			$pos['categories'] = implode('|', $categories);

			/* Get extra options */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_option_rel WHERE exhibitor = ? AND `option` > 0');
			$stmt->execute(array($pos['id']));
			$posoptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

>>>>>>> 1d9c5429b5a4a56db505c4a90d4223ece15c71ae
			$options = array();
			if (count($posoptions) > 0) {
				foreach ($posoptions as $option) {
					$options[] = $option['option'];
				}
			}

			$pos['options'] = implode('|', $options);
			$rpositions[$pos['position']] = $pos;
		}

		/* Preliminary bookings */
		$stmt = $u->db->prepare("SELECT prel.*, user.id as userid, pos.area, pos.name, pos.map, user.company FROM user, preliminary_booking AS prel, fair_map_position AS pos WHERE prel.fair=? AND pos.id = prel.position AND user.id = prel.user");
		$stmt->execute(array($_SESSION['user_fair']));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$prelpos = array();

		// Check that only preliminary bookings on map positions 
		// NOT booked/reserved are listed
		foreach ($result as $preliminary_booking) {
			if (!isset($positions[$preliminary_booking['position']]) && !isset($rpositions[$preliminary_booking['position']])) {
				$prelpos[] = $preliminary_booking;
			}
		}

		$this->setNoTranslate('positions', $positions);
		$this->setNoTranslate('rpositions', $rpositions);
		$this->setNoTranslate('prelpos', $prelpos);
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
		$this->set('tr_last_edited', 'Last edited');
		$this->set('tr_reserved_until', 'Reserved until');
		$this->set('tr_message', 'Message to organizer');
		$this->set('tr_view', 'View');
		$this->set('tr_edit', 'Edit');
		$this->set('tr_delete', 'Delete');
		$this->set('tr_approve', 'Approve');
		$this->set('tr_deny', 'Deny');
		$this->set('tr_reserve', 'Reserve stand space');
		$this->set('never_edited_label', 'Never edited');
		$this->set('confirm_delete', 'Are you sure that you want to remove stand space');
		$this->set('export', 'Export to Excel');
		$this->set('col_export_err', 'Select at least one column in order to export!');
		$this->set('row_export_err', 'Select at least one row in order to export!');
		$this->set('ok_label', 'OK');
	}

	public function arrangerMessage($type = '', $id = 0) {

		setAuthLevel(1);

		if ($type !== '' && $id > 0) {

			$message = '';

			if ($type == 'preliminary') {
				$prel_booking = new PreliminaryBooking();
				$prel_booking->load($id, 'id');
				$message = $prel_booking->get('arranger_message');
			} else {
				$exhibitor = new Exhibitor();
				$exhibitor->load($id, 'id');
				$message = $exhibitor->get('arranger_message');
			}

			if ($this->is_ajax) {
				$this->createJsonResponse();
			}

			$this->setNoTranslate('message', $message);
		}
	}

	public function delete($id, $confirmed='', $from='') {

		setAuthLevel(3);

		$this->set('headline', 'Delete administrator');
		$this->setNoTranslate('from', $from);

		$this->Administrator->load($id, 'id');

		if ($confirmed == 'confirmed') {
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
			$this->set('warning', 'Do you really want to delete the administrator');
			$this->setNoTranslate('administrator', $this->Administrator);
			$this->set('yes', 'Yes');
			$this->set('no', 'No');
		}

	}

	public function mine() {
		setAuthLevel(3);
		
		$stmt = $this->Administrator->db->prepare("SELECT user.id, user.name, user.email, user.phone1, user.locked, user.last_login, COUNT(fair_map_position.id) AS position_count
			FROM user 
			LEFT JOIN fair_map_position ON user.id = fair_map_position.created_by
			WHERE user.owner = ? AND user.level = ?
			GROUP BY user.id");
		$stmt->execute(array($_SESSION['user_id'], 2));
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
		$this->setNoTranslate('admins', $as);
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
			$this->setNoTranslate('arranger', $arr);
			$this->setNoTranslate('fairs', $arr->get('fairs'));

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

			$this->setNoTranslate('fairs', $fairs);
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

			$errors = false;

			if ($this->Administrator->emailExists() && $id === 'new') {
				$this->set('user_message', 'The email address already exists in our system. Please choose another one.');
				$this->setNoTranslate("error", true);
				$errors = true;
			} else if ($this->Administrator->aliasExists() && $id === 'new') {
				$this->set('user_message', 'The alias already exists in our system. Please choose another one.');
				$this->setNoTranslate("error", true);
				$errors = true;
			} else if (empty($_POST['fair_permission'])) {
				$this->set('user_message', 'You have to select at least one fair and at least one map.');
				$this->setNoTranslate("error", true);
				$errors = true;
			} else {
				require_once ROOT.'application/models/FairUserRelation.php';

				if (isset($_POST['fair_permission'])) {

					//First loop through to see that there are no errors
					foreach ($_POST['fair_permission'] as $fairId) {

						if (!isset($_POST['maps'][$fairId])) {
							$this->set("user_message", "You have to select at least one map for each selected fair.");
							$this->setNoTranslate("error", true);
							$errors = true;
							break;
						}
					}

					if (!$errors) {
						//If no errors occurred, proceed with creation
						$aId = $this->Administrator->save();
						$oldful = new FairUserRelation;
						$oldful->load($aId, 'user');

						$stmt = $this->Administrator->db->prepare("DELETE FROM fair_user_relation WHERE user = ?");
						$stmt->execute(array($aId));

						foreach ($_POST['fair_permission'] as $fairId) {
							$rel = new FairUserRelation;
							$rel->set('fair', $fairId);
							$rel->set('user', $aId);
							$rel->set('map_access', implode('|', $_POST['maps'][$fairId]));
							$rel->set('connected_time', $oldful->get('connected_time'));
							$rel->save();
						}
					}
				}
			}

			unset($_POST);

			// Load the user again so we get the new permissions.
			$this->Administrator->load($this->Administrator->get('id'), 'id');

			if ($errors) {
				if ($id === "new") {
					$this->changeAction("edit", array("new"));
				} else {
					$this->changeAction("edit", array($id));
				}
			} else if (userLevel() == 4) {
				$this->changeAction('all');

			} else if (userLevel() == 3) {
				$this->changeAction('mine');
			}

			return;
		}

		$this->setNoTranslate('locked0sel', '');

		$this->setNoTranslate('edit_id', $id);
		$this->setNoTranslate('user', $this->Administrator);
		$this->setNoTranslate('user_maps', $this->Administrator->get('maps'));
		$this->setNoTranslate('user_fairs', $this->Administrator->get('fairs'));
	}

	public function deleteBooking($id = 0, $posId = 0) {
		setAuthLevel(2);

		$status = $_POST['status'];
		$comment = $_POST['comment'];

		$position = new FairMapPosition();
		$position->load($posId, 'id');

		$fairMap = new FairMap();
		$fairMap->load($position->get("map"), "id");

		$fair = new Fair();
		$fair->load($fairMap->get('fair'), 'id');

		$current_user = new User();
		$current_user->load($_SESSION['user_id'], 'id');

		$organizer = new User();
		$organizer->load($fair->get('created_by'), 'id');

		if ($status == "Preliminary Booking") {
			$pb = new PreliminaryBooking;
			$pb->load($id, 'id');
			
			$u = new User();
			$u->load($pb->get('user'), 'id');
			$pb->delete();

			$mail_type = 'booking';

		} else {
			$exhib = new Exhibitor;
			$exhib->load($id, 'id');

			$u = new User;
			$u->load($exhib->get('user'), 'id');

			$stmt = $this->db->prepare("DELETE FROM exhibitor WHERE id = ? AND position = ?");
			$stmt->execute(array($id, $posId));

			$position->set('status', 0);
			$position->save();

			if ($status == 'Reservation') {
				$mail_type = 'reservation';
			} else {
				$mail_type = 'booking';
			}
		}

<<<<<<< HEAD
		$time_now = date('d-m-Y H:i');
=======
		$current_date = date('d-m-Y H:i');
>>>>>>> 1d9c5429b5a4a56db505c4a90d4223ece15c71ae

		$mailSetting = $mail_type . "Cancelled";

		//Check mail settings and send only if setting is set
		if ($fair->wasLoaded()) {
			$mailSettings = json_decode($fair->get("mail_settings"));
			if (is_array($mailSettings->$mailSetting)) {
				if (in_array("1", $mailSettings->$mailSetting)) {
					$mail_exhibitor = new Mail($u->get('email'), $mail_type . '_cancelled', $fair->get("url") . "@chartbooker.com", $fair->get("name"));
					$mail_exhibitor->setMailVar('position_name', $position->get('name'));
<<<<<<< HEAD
					$mail_exhibitor->setMailVar('cancelled_exhibitor', $exhib->get('name'));
					$mail_exhibitor->setMailVar('cancelled_name', $current_user->get('name'));
					$mail_exhibitor->setMailVar('event_name', $fair->get('name'));
					$mail_exhibitor->setMailVar('edit_time', $time_now);
					$mail_exhibitor->setMailVar('comment', $comment);
					$mail_exhibitor->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
=======
					$mail_exhibitor->setMailVar('cancelled_name', $current_user->get('name'));
					$mail_exhibitor->setMailVar('event_name', $fair->get('name'));
					$mail_exhibitor->setMailVar('edit_time', $current_date);
					$mail_exhibitor->setMailVar('comment', $comment);
>>>>>>> 1d9c5429b5a4a56db505c4a90d4223ece15c71ae
					$mail_exhibitor->send();
				}

				if (in_array("0", $mailSettings->$mailSetting)) {
					$mail_user = new Mail($current_user->get('email'), $mail_type . '_cancelled_confirm', $fair->get("url") . "@chartbooker.com", $fair->get("name"));
					$mail_user->setMailVar('position_name', $position->get('name'));
<<<<<<< HEAD
					$mail_user->setMailVar('cancelled_exhibitor', $exhib->get('name'));
					$mail_user->setMailVar('cancelled_name', $current_user->get('name'));
					$mail_user->setMailVar('event_name', $fair->get('name'));
					$mail_user->setMailVar('edit_time', $time_now);
					$mail_user->setMailVar('comment', $comment);
					$mail_user->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
=======
					$mail_user->setMailVar('cancelled_name', $current_user->get('name'));
					$mail_user->setMailVar('event_name', $fair->get('name'));
					$mail_user->setMailVar('edit_time', $current_date);
					$mail_user->setMailVar('comment', $comment);
>>>>>>> 1d9c5429b5a4a56db505c4a90d4223ece15c71ae
					$mail_user->send();
				}

				if (in_array("0", $mailSettings->$mailSetting)) {
					$mail_organizer = new Mail($organizer->get('email'), $mail_type . '_cancelled_confirm', $fair->get("url") . "@chartbooker.com", $fair->get("name"));
					$mail_organizer->setMailVar('position_name', $position->get('name'));
<<<<<<< HEAD
					$mail_organizer->setMailVar('cancelled_exhibitor', $exhib->get('name'));
					$mail_organizer->setMailVar('cancelled_name', $current_user->get('name'));
					$mail_organizer->setMailVar('event_name', $fair->get('name'));
					$mail_organizer->setMailVar('edit_time', $time_now);
					$mail_organizer->setMailVar('comment', $comment);
					$mail_organizer->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
=======
					$mail_organizer->setMailVar('cancelled_name', $current_user->get('name'));
					$mail_organizer->setMailVar('event_name', $fair->get('name'));
					$mail_organizer->setMailVar('edit_time', $current_date);
					$mail_organizer->setMailVar('comment', $comment);
>>>>>>> 1d9c5429b5a4a56db505c4a90d4223ece15c71ae
					$mail_organizer->send();
				}
			}
		}

		if (!isset($_POST["ajax"])){
			header('Location: '.BASE_URL.'administrator/newReservations');
		}
	}

	public function approveReservation() {
		setAuthLevel(2);

		if (isset($_POST['id'])) {
			$this->editBooking($_POST['id'], 2);
		}

		header('Location: '.BASE_URL.'administrator/newReservations');
	}

	public function editBooking($exhibitor_id = 0, $set_status = null) {
		setAuthLevel(2);

		if ($exhibitor_id > 0) {

			$exhibitor = new Exhibitor();
			$exhibitor->load($exhibitor_id, 'id');

			if ($exhibitor->wasLoaded()) {
				$exhibitor->set('commodity', $_POST['commodity']);
				$exhibitor->set('arranger_message', $_POST['message']);
				$exhibitor->save();

				// Remove old categories for this booking
				$stmt = $this->db->prepare("DELETE FROM exhibitor_category_rel WHERE exhibitor = ?");
				$stmt->execute(array($exhibitor->get('exhibitor_id')));

				// Set new categories for this booking
				$categories = array();
				if (isset($_POST['categories']) && is_array($_POST['categories'])) {
					$stmt = $this->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");

					foreach ($_POST['categories'] as $category_id) {
						$stmt->execute(array($exhibitor->get('exhibitor_id'), $category_id));

						$ex_category = new ExhibitorCategory();
						$ex_category->load($category_id, 'id');
						$categories[] = $ex_category->get('name');
					}
				}

				// Remove old options for this booking
				$stmt = $this->db->prepare("DELETE FROM exhibitor_option_rel WHERE exhibitor = ?");
				$stmt->execute(array($exhibitor->get('exhibitor_id')));

				// Set new options for this booking
				$options = array();
				if (isset($_POST['options']) && is_array($_POST['options'])) {
					$stmt = $this->db->prepare("INSERT INTO exhibitor_option_rel (exhibitor, `option`) VALUES (?, ?)");

					foreach ($_POST['options'] as $option_id) {
						$stmt->execute(array($exhibitor->get('exhibitor_id'), $option_id));

						$ex_option = new FairExtraOption();
						$ex_option->load($option_id, 'id');
						$options[] = $ex_option->get('text');
					}
				}

				$pos = new FairMapPosition();
				$pos->load($exhibitor->get('position'), 'id');

				$fair = new Fair();
				$fair->load($exhibitor->get('fair'), 'id');

				$organizer = new User();
				$organizer->load($fair->get('created_by'), 'id');

				$mail_type = ($pos->get('status') == 1 ? 'reservation' : 'booking');

				if ($set_status == null) {
					// If this is a reservation (status is 1), then also set the expiry date
					if ($pos->wasLoaded() && $pos->get('status') == 1) {
						$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));
					}
				} else {
					$pos->set('status', $set_status);
				}

				$categories = implode(', ', $categories);
				$options = implode(', ', $options);				
				$time_now = date('d-m-Y H:i');
				
				$mailSetting = $mail_type . "Edited";

				//Check mail settings and send only if setting is set
				if ($fair->wasLoaded()) {
					$mailSettings = json_decode($fair->get("mail_settings"));
					if (is_array($mailSettings->$mailSetting)) {
						if (in_array("0", $mailSettings->$mailSetting)) {
							$mail_organizer = new Mail($organizer->get('email'), $mail_type . '_edited_confirm', $fair->get("url") . "@chartbooker.com", $fair->get("name"));
							$mail_organizer->setMailVar('event_name', $fair->get('name'));
							$mail_organizer->setMailVar('position_name', $pos->get('name'));
							$mail_organizer->setMailVar('position_information', $pos->get('information'));
							$mail_organizer->setMailVar('edit_time', $time_now);
							$mail_organizer->setMailVar('arranger_message', $_POST['message']);
							$mail_organizer->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_organizer->setMailVar('exhibitor_category', $categories);
							$mail_organizer->setMailVar('exhibitor_options', $options);

							if ($mail_type == 'reservation') {
								$mail_organizer->setMailVar('date_expires', $_POST['expires']);
							}

							$mail_organizer->send();
						}

<<<<<<< HEAD
=======
				$mailSetting = $mail_type . "Edited";

				//Check mail settings and send only if setting is set
				if ($fair->wasLoaded()) {
					$mailSettings = json_decode($fair->get("mail_settings"));
					if (is_array($mailSettings->$mailSetting)) {
						if (in_array("0", $mailSettings->$mailSetting)) {
							$mail_organizer = new Mail($organizer->get('email'), $mail_type . '_edited_confirm', $fair->get("url") . "@chartbooker.com", $fair->get("name"));
							$mail_organizer->setMailVar('event_name', $fair->get('name'));
							$mail_organizer->setMailVar('position_name', $pos->get('name'));
							$mail_organizer->setMailVar('position_information', $pos->get('information'));
							$mail_organizer->setMailVar('edit_time', $time_now);
							$mail_organizer->setMailVar('arranger_message', $_POST['arranger_message']);
							$mail_organizer->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_organizer->setMailVar('exhibitor_category', $categories);

							if ($mail_type == 'reservation') {
								$mail_organizer->setMailVar('date_expires', $_POST['expires']);
							}

							$mail_organizer->send();
						}

>>>>>>> 1d9c5429b5a4a56db505c4a90d4223ece15c71ae
						if (in_array("1", $mailSettings->$mailSetting)) {
							$mail_user = new Mail($exhibitor->get('email'), $mail_type . '_edited_receipt', $fair->get("url") . "@chartbooker.com", $fair->get("name"));
							$mail_user->setMailVar('event_name', $fair->get('name'));
							$mail_user->setMailVar('position_name', $pos->get('name'));
							$mail_user->setMailVar('position_information', $pos->get('information'));
							$mail_user->setMailVar('edit_time', $time_now);
<<<<<<< HEAD
							$mail_user->setMailVar('arranger_message', $_POST['message']);
							$mail_user->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_user->setMailVar('exhibitor_category', $categories);
							$mail_user->setMailVar('exhibitor_options', $options);							
=======
							$mail_user->setMailVar('arranger_message', $_POST['arranger_message']);
							$mail_user->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_user->setMailVar('exhibitor_category', $categories);
>>>>>>> 1d9c5429b5a4a56db505c4a90d4223ece15c71ae

							if ($mail_type == 'reservation') {
								$mail_user->setMailVar('date_expires', $_POST['expires']);
							}

							$mail_user->send();
						}
					}
				}

				$pos->save();
			}
		}

		header('Location: ' . BASE_URL . 'administrator/newReservations');
	}

	public function reservePrelBooking() {
		setAuthLevel(2);

		if (isset($_POST['id'])) {

			$prel = new PreliminaryBooking;
			$prel->load($_POST['id'], 'id');

			if ($prel->wasLoaded()) {
				$pos = new FairMapPosition;
				$pos->load($prel->get('position'), 'id');

				// Delete existing exhibitor if position is booked
				if ($pos->get('status') > 0) {
					$ex = new Exhibitor;
					$ex->load($pos->get('id'), 'position');

					if ($ex->wasLoaded()) {
						$ex->delete();
					}
				}

				$pos->set('status', 1);
				$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));

				$exhib = new Exhibitor;
				$exhib->set('user', $prel->get('user'));
				$exhib->set('fair', $prel->get('fair'));
				$exhib->set('position', $prel->get('position'));
				$exhib->set('category', 0);
				$exhib->set('presentation', '');
				$exhib->set('commodity', $_POST['commodity']);
				$exhib->set('arranger_message', $_POST['message']);
				$exhib->set('booking_time', $prel->get('booking_time'));
				$exhib->set('approved', 1);
				$exhib->set('edit_time', time());
				$exId = $exhib->save();
				$pos->save();

				$categories = array();
				if (isset($_POST['categories']) && is_array($_POST['categories'])) {

					$stmt = $exhib->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");

					foreach ($_POST['categories'] as $category_id) {
						$stmt->execute(array($exId, $category_id));

						$ex_category = new ExhibitorCategory();
						$ex_category->load($category_id, 'id');
						$categories[] = $ex_category->get('name');
					}
				}

				if (isset($_POST['options']) && is_array($_POST['options'])) {

					$stmt = $exhib->db->prepare("INSERT INTO exhibitor_option_rel (exhibitor, `option`) VALUES (?, ?)");

					foreach ($_POST['options'] as $option_id) {
						$stmt->execute(array($exId, $option_id));

						$ex_option = new FairExtraOption();
						$ex_option->load($option_id, 'id');
						$option[] = $ex_option->get('text');
					}
				}

				// Clean up preliminaries.
				$prel->delete();
				$stmt = $prel->db->prepare("DELETE FROM preliminary_booking WHERE position = ?");
				$stmt->execute(array($pos->get('id')));

				// Send mail
				$categories = implode(', ', $categories);
				$options = implode(', ', $options);
				$time_now = date('d-m-Y H:i');

				$fair = new Fair();
				$fair->load($exhib->get('fair'), 'id');

				$organizer = new User();
				$organizer->load($fair->get('created_by'), 'id');

				$exhib_user = new User();
				$exhib_user->load($exhib->get('user'), 'id');

				//Check mail settings and send only if setting is set
				if ($fair->wasLoaded()) {
					$mailSettings = json_decode($fair->get("mail_settings"));
					if (is_array($mailSettings->reservationEdited)) {
						if (in_array("0", $mailSettings->reservationEdited)) {
							$mail_organizer = new Mail($organizer->get('email'), 'reservation_edited_confirm', $fair->get("url") . "@chartbooker.com", $fair->get("name"));
<<<<<<< HEAD
							$mail_organizer->setMailvar("exhibitor_name", $exhib_user->get("name"));
							$mail_organizer->setMailvar("event_name", $fair->get("name"));							
							$mail_organizer->setMailVar('position_name', $pos->get('name'));
							$mail_organizer->setMailVar("booking_time", date('d-m-Y H:i:s', intval($exhib->get("booking_time"))));
							$mail_organizer->setMailVar("url", BASE_URL . $fair->get("url"));
							$mail_organizer->setMailVar('position_information', $pos->get('information'));
							$mail_organizer->setMailVar('edit_time', $time_now);
							$mail_organizer->setMailVar('arranger_message', $_POST['message']);
							$mail_organizer->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_organizer->setMailVar('exhibitor_category', $categories);
							$mail_organizer->setMailVar('exhibitor_options', $options);
							$mail_organizer->setMailVar('date_expires', $_POST['expires']);
							$mail_organizer->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));			
							$mail_organizer->send();
						}
						if (in_array("1", $mailSettings->reservationEdited)) {
							$mail_user = new Mail($exhib_user->get('email'), 'reservation_edited_receipt', $fair->get("url") . "@chartbooker.com", $fair->get("name"));
=======
							$mail_organizer->setMailVar('position_name', $pos->get('name'));
							$mail_organizer->setMailVar('position_information', $pos->get('information'));
							$mail_organizer->setMailVar('edit_time', $time_now);
							$mail_organizer->setMailVar('arranger_message', $_POST['arranger_message']);
							$mail_organizer->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_organizer->setMailVar('exhibitor_category', $categories);
							$mail_organizer->setMailVar('date_expires', $_POST['expires']);
							$mail_organizer->send();
						}
						if (in_array("1", $mailSettings->reservationEdited)) {
							$mail_user = new Mail($exhib_user->get('email'), 'preliminary_to_reserved', $fair->get("url") . "@chartbooker.com", $fair->get("name"));

>>>>>>> 1d9c5429b5a4a56db505c4a90d4223ece15c71ae
							$mail_user->setMailvar("exhibitor_name", $exhib_user->get("name"));
							$mail_user->setMailvar("event_name", $fair->get("name"));
							$mail_user->setMailVar('position_name', $pos->get('name'));
							$mail_user->setMailVar("booking_time", date('d-m-Y H:i:s', intval($exhib->get("booking_time"))));
							$mail_user->setMailVar("url", BASE_URL . $fair->get("url"));
							$mail_user->setMailVar('position_information', $pos->get('information'));
<<<<<<< HEAD
							$mail_user->setMailVar('edit_time', $time_now);
							$mail_user->setMailVar('arranger_message', $_POST['message']);
							$mail_user->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_user->setMailVar('exhibitor_category', $categories);
							$mail_user->setMailVar('exhibitor_options', $options);
							$mail_user->setMailVar('date_expires', $_POST['expires']);
							$mail_user->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));					
=======
							$mail_user->setMailVar('arranger_message', $_POST['arranger_message']);
							$mail_user->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_user->setMailVar('exhibitor_category', $categories);
>>>>>>> 1d9c5429b5a4a56db505c4a90d4223ece15c71ae
							$mail_user->send();
						}
					}
				}
			}
		}

		header('Location: '.BASE_URL.'administrator/newReservations');
		exit;
	}

}

?>
