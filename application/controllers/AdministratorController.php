<?php

class AdministratorController extends Controller {

	function overview($fair=0) {

		setAuthLevel(3);

		$thisFair = new Fair($this->Administrator->db);
		$thisFair->load($fair, 'id');

		$this->setNoTranslate('thisFair', $fair);

		if (userLevel() == 3) {
			$arr = new Arranger();
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
		$this->setNoTranslate('fairId', $fair);
		$this->setNoTranslate('fair', $thisFair);

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
					$u = new User();
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

		$fair = new Fair();
		$fair->load($_SESSION['user_fair'], 'id');
		$this->setNoTranslate('fair', $fair);

	}

	public function newExhibitor() {

		setAuthLevel(2);

		$error = '';
		$user = new User();

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
				$fair = new Fair();
				$fair->load($_POST['fair'], 'id');
				$this->setNoTranslate('d', $fair->get('url'));
				//$msg = "An organizer has created an account for you on his/her event ".BASE_URL.$fair->get('url')."\r\n\r\nUsername: ".$_POST['username']."\r\nPassword: ".$str;
				$user->setPassword($str);
				$userId = $user->save();

				$me = new User();
				$me->load($_SESSION['user_id'], 'id');

				$mail = new Mail($user->email, 'event_account');
				$mail->setMailVar('url', BASE_URL.$fair->get('url'));
				$mail->setMailVar('alias', $_POST['alias']);
				$mail->setMailVar('exhibitor_name', $_POST['company']);
				$mail->setMailVar('event_name', $fair->get('name'));
				$mail->setMailVar('event_email', $fair->get('contact_email'));
				$mail->setMailVar('event_phone', $fair->get('contact_phone'));
				$mail->setMailVar('event_website', $fair->get('website'));
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
				$fair = new Fair();
				$fair->load($fId, 'id');
				$fairs[] = $fair;
			}

		} else if (userLevel() == 3) {
			$stmt = $this->db->prepare("SELECT id FROM fair WHERE created_by = ?");
			$stmt->execute(array($_SESSION['user_id']));
			$owned_fairs = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ($owned_fairs as $fair_data) {
				$fair = new Fair();
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


	public function sendInvoices($tbl){
		setAuthLevel(2);
		$this->setNoTranslate('noView', true);

		if (isset($_POST['rows']) && is_array($_POST['rows'])) {

			/* Samla relevant information till en array
			beroende på vilken tabell som är vald */
			$u = new User;
			$u->load($_SESSION['user_id'], 'id');

			$fair = new Fair;
			$fair->loadsimple($_SESSION['user_fair'], 'id');

			$organizer = new User;
			$organizer->load2($fair->get('created_by'), 'id');

			if ($tbl != 1)
				return;

			$stmt = $u->db->prepare("SELECT ex_invoice.r_name AS r_name, ex_invoice.exhibitor AS exhibitor, ex_invoice.fair AS fair, ex_invoice.id AS id, user.invoice_email AS invoice_email, pos.text AS posname
				FROM user, exhibitor_invoice AS ex_invoice, exhibitor_invoice_rel AS pos
				WHERE ex_invoice.ex_user = user.id
				AND ex_invoice.id = pos.invoice
				AND pos.type = 'space'
				AND ex_invoice.fair = ?
				AND pos.fair = ?
				AND ex_invoice.status = ?
				AND ex_invoice.id IN (" . implode(',', $_POST['rows']) . ")");
			$stmt->execute(array($_SESSION['user_fair'], $_SESSION['user_fair'], 1));
			$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$invoice_files = array();

			foreach ($data_rows as $row) {
				$this->markAsSent($row['exhibitor']);

				$posname = str_replace('/', '-', $row['posname']);
				$invoice_files[] = ROOT.'public/invoices/fairs/'.$row['fair'].'/exhibitors/'.$row['exhibitor'].'/'.str_replace('/', '-', $row['r_name']) . '-' . $posname . '-' . $row['id'] . '.pdf';
			}

			try {
				$arranger_message = $_POST['invoice_mail_comment'];
				if ($arranger_message == '') {
					$arranger_message = $this->translate->{'No message was given.'};
				}

				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN => $fair->get("name"));
				$recipients = array($row['invoice_email'] => $row['invoice_email']);

				$mail_user = new Mail();
				$mail_user->setTemplate('send_invoice');
				$mail_user->setFrom($from);
				$mail_user->setRecipients($recipients);
				$mail_user->setMailvar("exhibitor_company_name", $row['r_name']);
				$mail_user->setMailvar("event_name", $fair->get("name"));
				$mail_user->setMailVar('event_email', $fair->get('contact_email'));
				$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
				$mail_user->setMailVar('event_website', $fair->get('website'));
				$mail_user->setMailvar("arranger_name", $organizer->get("company"));
				$mail_user->setMailvar("arranger_message", $arranger_message);

				foreach($invoice_files as $file) {
					if(!file_exists($file))
						throw new Exception("Kan inte bifoga fil");
					if(!is_readable($file))
						throw new Exception("Kan inte öppna bifogad fil för läsning");

					$mail_user->attachFile($file);
				}

				//$mail_user->setMailVar("url", BASE_URL . $fair->get("url"));
				if(!$mail_user->send()) {
					// Kunde inte skicka mail
				}
			} catch(Swift_RfcComplianceException $ex) {
				// Felaktig epost-adress
			} catch(Exception $ex) {
				// Okänt fel
			}
		}

		header("Location: ".BASE_URL."administrator/invoices");
		exit;
	}

	function mailVerifyCloned() {
		setAuthLevel(2);
		$this->setNoTranslate('noView', true);

		if (userLevel() == 3) {
			$fair = new Fair();
			$fair->load($_SESSION['user_fair'], 'id');
			if ($fair->wasLoaded() && $fair->get('created_by') != $_SESSION['user_id']) {
				toLogin();
			}
		}

		if (userLevel() == 2) {
			$stmt = $this->db->prepare('SELECT * FROM fair_user_relation WHERE user=? AND fair=?');
			$stmt->execute(array($_SESSION['user_id'], $_SESSION['user_fair']));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (!$result) {
				$this->setNoTranslate('hasRights', false);
				return;
			}
		}

		$this->setNoTranslate('hasRights', true);

		if (isset($_POST['exid']) && is_array($_POST['exid'])) {
				$stmt = $this->db->prepare("SELECT ex.*, 
					user.id AS uid, 
					user.alias AS alias, 
					user.company AS company,
					pos.name AS posname, 
					pos.area AS posarea, 
					pos.information AS posinfo, 
					pos.expires AS expirationdate, 
					pos.id AS posid,  
					ex.id AS id, 
					fair.name AS fairname,
					fair.contact_email AS fmail,
					fair.contact_phone AS fphone,
					fair.website AS fwebsite
						FROM user, 
						fair, 
						exhibitor AS ex, 
						fair_map_position AS pos 
							WHERE user.id = ex.user 
							AND ex.position = pos.id 
							AND ex.fair = ? 
							AND fair.id = ?
							AND pos.status = 1 
							AND ex.clone = 1 
							AND ex.id IN (" . implode(',', $_POST['exid']) . ")");
				$stmt->execute(array($_SESSION['user_fair'], $_SESSION['user_fair']));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

				foreach ($data_rows as $row) {

						$pos = new FairMapPosition;
						$pos->load2($row['posid'], 'id');

						$user = new User;
						$user->load2($row['uid'], 'id');

						$now = time();

						$hash1 = md5($row['id'].BASE_URL.$row['alias']);
						$accepturl = BASE_URL.'exhibitor/verifyReservation/'.$row['id'].'/'.$hash1.'/accept';
						$denyurl = BASE_URL.'exhibitor/verifyReservation/'.$row['id'].'/'.$hash1.'/deny';
						$alreadysentstmt = $this->db->prepare("SELECT `exhibitor` FROM `exhibitor_link` WHERE `exhibitor` = ?");
						$alreadysentstmt->execute(array($row['id']));
						$alreadySent = $alreadysentstmt->fetchAll(PDO::FETCH_ASSOC);
						if (count($alreadySent) == 0) {
							$stmt_insert = $this->db->prepare("INSERT INTO `exhibitor_link` (`exhibitor`, `link`, `status`, `linkdate`) VALUES (?, ?, ?, ?)");
							$stmt_insert->execute(array($row['id'], $hash1, 1, $now));
						} else {
							$stmt_insert = $this->db->prepare("UPDATE `exhibitor_link` SET `linkdate` = ? WHERE `exhibitor` = ?");
							$stmt_insert->execute(array($now, $row['id']));
						}


					    $mail = new Mail($user->get('contact_email'), 'confirm_cloned_reservation');
					    $mail->setMailVar('position_name', $row['posname']);
					    $mail->setMailVar('position_information', $row['posinfo']);
					    $mail->setMailVar('position_area', $row['posarea']);
					  	$mail->setMailVar('fairname', $row['fairname']);
						$mail->setMailVar('event_email', $row['fmail']);
						$mail->setMailVar('event_phone', $row['fphone']);
						$mail->setMailVar('event_website', $row['fwebsite']);
					  	$mail->setMailVar('expirationdate', $row['expirationdate']);
					  	$mail->setMailVar('exhibitor_name', $row['company']);
					    $mail->setMailVar('accepturl', $accepturl);
					    $mail->setMailVar('denyurl', $denyurl);
					    $mail->send();
				}
			}
		}

	public function exportNewReservations($tbl){
		setAuthLevel(2);
		$this->setNoTranslate('noView', true);

		if (isset($_POST['rows'], $_POST['field']) && is_array($_POST['rows']) && is_array($_POST['field'])) {

			/* Samla relevant information till en array
			beroende på vilken tabell som är vald */
			$u = new User;
			$u->load($_SESSION['user_id'], 'id');

			if ($tbl == 1) {
				$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, user.*, pos.name AS position, pos.area, pos.information, ex.id AS id FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ? AND ex.id IN (" . implode(',', $_POST['rows']) . ")");
				$stmt->execute(array($_SESSION['user_fair'], 2));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 2) {
				$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, user.*, pos.name AS position, pos.area, pos.information, pos.expires, ex.id AS id FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ? AND ex.clone = 0 AND ex.id IN (" . implode(',', $_POST['rows']) . ")");
				$stmt->execute(array($_SESSION['user_fair'], 1));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 3) {
				$stmt = $u->db->prepare("SELECT prel.*, user.id as userid, user.*, pos.area, pos.information, pos.name AS position, prel.id AS id FROM user, preliminary_booking AS prel, fair_map_position AS pos WHERE prel.fair = ? AND pos.id = prel.position AND user.id = prel.user AND prel.id IN (" . implode(',', $_POST['rows']) . ")");
				$stmt->execute(array($_SESSION['user_fair']));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				
			} else if ($tbl == 4) {
				$stmt = $u->db->prepare("SELECT prel.*, user.id as userid, user.*, pos.area, pos.information, pos.name AS position, prel.id AS id FROM user, preliminary_booking AS prel, fair_map_position AS pos WHERE prel.fair = ? AND pos.id = prel.position AND user.id = prel.user AND prel.id IN (" . implode(',', $_POST['rows']) . ")");
				$stmt->execute(array($_SESSION['user_fair']));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 5) {
				$stmt = $u->db->prepare("SELECT fr.*, u.id AS userid, u.* FROM fair_registration AS fr LEFT JOIN user AS u ON u.id = fr.user WHERE fr.fair = ? AND fr.id IN (" . implode(',', $_POST['rows']) . ")");
				$stmt->execute(array($_SESSION['user_fair']));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			} else if ($tbl == 6) {
				$stmt = $u->db->prepare("SELECT prel.*, user.id as userid, user.*, pos.area, pos.information, pos.name AS position, prel.id AS id FROM user, preliminary_booking_history AS prel, fair_map_position AS pos WHERE prel.fair = ? AND pos.id = prel.position AND user.id = prel.user AND prel.id IN (" . implode(',', $_POST['rows']) . ")");
				$stmt->execute(array($_SESSION['user_fair']));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 7) {
				$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, user.*, pos.name AS position, pos.area, pos.information, ex.id AS id FROM user, exhibitor_history AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND ex.id IN (" . implode(',', $_POST['rows']) . ")");
				$stmt->execute(array($_SESSION['user_fair']));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 8) {
				$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, user.*, pos.name AS position, pos.area, pos.information, pos.expires, ex.id AS id FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ? AND ex.clone = 1 AND ex.id IN (" . implode(',', $_POST['rows']) . ")");
				$stmt->execute(array($_SESSION['user_fair'], 1));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 9) {
				$stmt = $u->db->prepare("SELECT frh.*, u.id AS userid, u.* FROM fair_registration_history AS frh LEFT JOIN user AS u ON u.id = frh.user WHERE frh.fair = ? AND frh.id IN (" . implode(',', $_POST['rows']) . ")");
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
			} else if ($tbl == 4) {
				$filename = "PreliminaryBookingsInactive.xlsx";
				$label_status = $this->translate->{'Preliminary booked (inactive)'};
			} else if ($tbl == 5) {
				$filename = "FairRegistrations.xlsx";
				$label_status = $this->translate->{'Registration'};
			} else if ($tbl == 6) {
				$filename = "PreliminaryBookingsDeleted.xlsx";
				$label_status = $this->translate->{'Preliminary booked (deleted)'};
			} else if ($tbl == 7) {
				$filename = "DeletedBookings.xlsx";
				$label_status = $this->translate->{'Booking (deleted)'};
			} else if ($tbl == 8) {
				$filename = "ReservedClonedStandSpaces.xlsx";
				$label_status = $this->translate->{'Reservation (cloned)'};
			} else if ($tbl == 9) {
				$filename = "FairRegistrationsDeleted.xlsx";
				$label_status = $this->translate->{'Registration (deleted)'};
			}

			if ($tbl < 3 || $tbl == 7 || $tbl == 8) {
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
				'information' => $this->translate->{'Information about stand space'},
				'commodity' => $this->translate->{'Trade'},
				'extra_options' => $this->translate->{'Extra options'},
				'booking_time' => $this->translate->{'Time of booking'},
				'edit_time' => $this->translate->{'Last edited'},
				'arranger_message' => $this->translate->{'Message to organizer'},
				'expires' => $this->translate->{'Reserved until'}
			);

			//Prelbooking does not have `Last edited`
			if ($tbl == 3 || $tbl == 6) {
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
						if ($tbl >= 3 && $tbl != 7 && $tbl != 8) {
							$option_texts = array();
							$options = explode('|', $row['options']);
							foreach ($options as $option_id) {
								$option = new FairExtraOption();
								$option->load($option_id, 'id');
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

	// Helper function used in /invoices when changing current fair
  public function invoicesChangeFair($fairId=0) {
    $_SESSION['user_fair'] = $fairId;
		$this->setNoTranslate('noView', true);
    header("Location: ".BASE_URL."administrator/invoices");
  }


	public function invoices($action='', $param='') {

		setAuthLevel(2);

		$fair = new Fair();
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


		$u = new User();
		$u->load($_SESSION['user_id'], 'id');

		/* Active invoices */
		$stmt = $u->db->prepare("SELECT exhibitor_invoice.* FROM exhibitor_invoice WHERE exhibitor_invoice.fair = ? AND exhibitor_invoice.status = 1");
		$stmt->execute(array($_SESSION['user_fair']));
		$active_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$ainvoices = array();


		foreach ($active_invoices as $invoice) {

			/* Get positions */
			$stmt = $u->db->prepare('SELECT `text` FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ? AND type = "space"');
			$stmt->execute(array($invoice['id'], $invoice['fair']));
			$posnames = $stmt->fetchAll(PDO::FETCH_ASSOC);

			
			$positions = array();
			if (count($posnames) > 0) {
				foreach ($posnames as $pos) {
					$positions[] = $pos;					
				}
			}
			//var_dump($positions);

			$invoice['posname'] = implode('|', $positions[0]);			

			$ainvoices[] = $invoice;
		}

		/* Paid invoices */
		$stmt = $u->db->prepare("SELECT exhibitor_invoice.* FROM exhibitor_invoice WHERE exhibitor_invoice.fair = ? AND exhibitor_invoice.status = 2");
		$stmt->execute(array($_SESSION['user_fair']));
		$paid_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$pinvoices = array();


		foreach ($paid_invoices as $invoice) {

			/* Get positions */
			$stmt = $u->db->prepare('SELECT `text` FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ? AND type = "space"');
			$stmt->execute(array($invoice['id'], $invoice['fair']));
			$posnames = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			
			$positions = array();
			if (count($posnames) > 0) {
				foreach ($posnames as $pos) {
					$positions[] = $pos;					
				}
			}
			//var_dump($positions);

			$invoice['posname'] = implode('|', $positions[0]);			

			$pinvoices[] = $invoice;
		}


		/* Credited invoices */
		$stmt = $u->db->prepare("SELECT exhibitor_invoice.* FROM exhibitor_invoice WHERE exhibitor_invoice.fair = ? AND exhibitor_invoice.status = 3");
		$stmt->execute(array($_SESSION['user_fair']));
		$credited_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$cinvoices = array();


		foreach ($credited_invoices as $invoice) {

			/* Get positions */
			$stmt = $u->db->prepare('SELECT `text` FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ? AND type = "space"');
			$stmt->execute(array($invoice['id'], $invoice['fair']));
			$posnames = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			
			$positions = array();
			if (count($posnames) > 0) {
				foreach ($posnames as $pos) {
					$positions[] = $pos;					
				}
			}

			$invoice['posname'] = implode('|', $positions[0]);

			/* Get credited invoices */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_invoice_credited WHERE invoice = ? AND fair = ?');
			$stmt->execute(array($invoice['id'], $invoice['fair']));
			$cids = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			
			$icredited_id = array();
			$icredited_created = array();
			if (count($cids) > 0) {
				foreach ($cids as $cid) {
					$icredited_id[] = $cid['cid'];
					$icredited_created[] = $cid['created'];
				}
			}

			$invoice['cid'] = implode('|', $icredited_id);
			$invoice['cidcreated'] = implode('|', $icredited_created);


			$cinvoices[] = $invoice;
		}

		/* Cancelled invoices */
		$stmt = $u->db->prepare("SELECT * FROM exhibitor_invoice_history WHERE fair = ? AND status = 4");
		$stmt->execute(array($_SESSION['user_fair']));
		$cancelled_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$minvoices = array();


		foreach ($cancelled_invoices as $invoice) {

			/* Get positions */
			$stmt = $u->db->prepare('SELECT `text` FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ? AND type = "space"');
			$stmt->execute(array($invoice['id'], $invoice['fair']));
			$posnames = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			
			$positions = array();
			if (count($posnames) > 0) {
				foreach ($posnames as $pos) {
					$positions[] = $pos;					
				}
			}

			$invoice['posname'] = implode('|', $positions[0]);

			/* Get cancelled invoices */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_invoice_cancelled WHERE invoice = ? AND fair = ?');
			$stmt->execute(array($invoice['id'], $_SESSION['user_fair']));
			$cancelids = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			
			$icancelled_id = array();
			$icancelled_created = array();
			if (count($cancelids) > 0) {
				foreach ($cancelids as $cid) {
					$icancelled_id[] = $cid['invoice'];
					$icancelled_created[] = $cid['created'];
				}
			}

			$invoice['cid'] = implode('|', $icancelled_id);
			$invoice['cidcreated'] = implode('|', $icancelled_created);


			$minvoices[] = $invoice;
		}
		$this->set('aheadline', 'Active invoices');
		$this->set('pheadline', 'Paid invoices');
		$this->set('cheadline', 'Credited invoices');
		$this->set('dheadline', 'Cancelled invoices');
		$this->setNoTranslate('ainvoices', $ainvoices);
		$this->setNoTranslate('pinvoices', $pinvoices);
		$this->setNoTranslate('cinvoices', $cinvoices);
		$this->setNoTranslate('minvoices', $minvoices);
		$this->set('confirm_mark_as_sent', 'Mark invoice as sent for');
		$this->set('confirm_send_invoices', 'Are you sure that you want to send these invoices?');
		$this->set('send_invoice_comment', 'Enter a message for this mail batch');
		$this->set('confirm_credit_invoice', 'Credit invoice for');
		$this->set('confirm_cancel_invoice', 'Cancel invoice for');
		$this->set('iactive', '');
		$this->set('ipaid', '');
		$this->set('icredited', '');
		$this->set('icancelled', '');
		$this->set('tr_id', 'ID');
		$this->set('tr_company', 'Company name');
		$this->set('tr_created', 'Created');
		$this->set('tr_view', 'View');
		$this->set('tr_sent', 'Sent');
		$this->set('tr_posname', 'Position');
		$this->set('tr_expires', 'Expires');
		$this->set('tr_credit', 'Credit');
		$this->set('tr_cancel', 'Makulera');
		$this->set('confirmcredit', 'Are you sure that you want to credit this invoice?');
		$this->set('confirmcancel', 'Are you sure that you want to cancel this invoice?');

	}




public function exportedFile() {
	$time = time();
	$fixedLinks = $_POST['fileLink'];
	$files = explode('|', $fixedLinks);
	$zipname = 'invoices/tmp/'.$time.'.zip';
	$zip = new ZipArchive;
	$zip->open( $zipname, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE );
	foreach ($files as $file) {
		if (file_exists($file)) {
	  		$zip->addFile($file, iconv("UTF-8", "CP865//IGNORE", pathinfo( $file, PATHINFO_BASENAME )));
		}
	}
	$zip->close();
}

public function downloadInvoices($filename) {
	if (isset($filename)) {

		header('Content-Type: application/zip; charset=UTF-8');
		header('Content-disposition: attachment; filename='.$filename.'.zip');
		header('Content-Length: ' . filesize('invoices/tmp/'.$filename.'.zip'));
		readfile('invoices/tmp/'.$filename.'.zip');
	}
}
public function exportFiles() {
	if (isset($_POST['fileLink'])) {
	$filename = $this->translate->{"Invoices"}.' '.date('Y-m-d H-i-s');
	$files = explode('|', $_POST['fileLink']);
	$zipfile = 'invoices/tmp/'.$filename.'.zip';
	$zip = new ZipArchive;
	$zip->open( $zipfile, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE );
	foreach ($files as $file) {
		$file = str_replace(':', '_', $file);
		if (file_exists($file)) {
			$fixedname = preg_replace ( '/Å/' , 'A' , $file );
	  		$zip->addFile( $file, iconv("UTF-8", "CP865", pathinfo( $fixedname, PATHINFO_BASENAME )));
		}
	}
	$zip->close();
	echo $filename;
	}
}

  // Helper function, used in /newReservations when changing current fair
  public function reservationsChangeFair($fairId=0) {
    $_SESSION['user_fair'] = $fairId;
		$this->setNoTranslate('noView', true);
    header("Location: ".BASE_URL."administrator/newReservations");
  }

	public function newReservations($action='', $param='') {

		setAuthLevel(2);

		$fair = new Fair();
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
		if ($action == 'deny') {/*
			$pb = new PreliminaryBooking();
			$pb->load($param, 'id');
			
			$u = new User();
			$u->load($pb->get('user'), 'id');
			
			//Check mail settings and send only if setting is set
			if ($fair->wasLoaded()) {
				$mailSettings = json_decode($fair->get("mail_settings"));
				if (is_array($mailSettings->reservationCancelled) && in_array("1", $mailSettings->reservationCancelled)) {
					$mail = new Mail($u->get('email'), 'reservation_cancelled', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
					$mail->send();
				}
			}
			
			$pb->delete();
			header("Location: ".BASE_URL."administrator/newReservations");
			exit;// Slutar här
		*/} else if ($action == 'approve' && isset($_POST['id'])) {

			$pb = new PreliminaryBooking();
			$pb->load($_POST['id'], 'id');

			if ($pb->wasLoaded()) {
				$pos = new FairMapPosition();
				$pos->load($pb->get('position'), 'id');
				$booking_time = $pb->get('booking_time');

				$previous_status = 3;
				$status = 2;
				$pos->set('status', $status);
				$pos->set('expires', '0000-00-00 00:00:00');

				$ex = new Exhibitor();
				$ex->set('user', $pb->get('user'));
				$ex->set('fair', $pb->get('fair'));
				$ex->set('position', $pb->get('position'));
				$ex->set('commodity', $_POST['commodity']);
				$ex->set('arranger_message', $_POST['arranger_message']);
				$ex->set('edit_time', time());
				$ex->set('clone', 0);
				$ex->set('status', 2);
				
				$exId = $ex->save();
				$pos->save();
				$pb->delete();

				$categoryNames = array();

				if (isset($_POST['categories']) && is_array($_POST['categories'])) {
					$stmt = $pos->db->prepare("INSERT INTO `exhibitor_category_rel` (`exhibitor`, `category`) VALUES (?, ?)");
					foreach ($_POST['categories'] as $cat) {
						$stmt->execute(array($exId, $cat));
						$category = new ExhibitorCategory();
						$category->load($cat, "id");
						if ($category->wasLoaded()) {
							$categoryNames[] = $category->get("name");
						}
					}
				}


				$options = array();
				if (isset($_POST['options']) && is_array($_POST['options'])) {
					$stmt = $pos->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");

					foreach ($_POST['options'] as $opt) {								
						$stmt->execute(array($exId, $opt));
						$ex_option = new FairExtraOption();
						$ex_option->load($opt, 'id');
						if ($ex_option->wasLoaded()) {
							$option_id[] = $ex_option->get('custom_id');
							$option_text[] = $ex_option->get('text');
							$option_price[] = $ex_option->get('price');
							$option_vat[] = $ex_option->get('vat');
						}
					}

					$options = array($option_id, $option_text, $option_price, $option_vat);
				}


				$articles = array();
				if (isset($_POST['articles']) && is_array($_POST['articles'])) {
					$stmt = $pos->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
					$arts = $_POST['articles'];
					$amounts = $_POST['artamount'];

					foreach (array_combine($arts, $amounts) as $art => $amount) {
						$stmt->execute(array($exId, $art, $amount));
						$arts = new FairArticle();
						$arts->load($art, 'id');
						if ($arts->wasLoaded()) {
							$art_id[] = $arts->get('custom_id');
							$art_text[] = $arts->get('text');
							$art_amount[] = $amount;
							$art_price[] = $arts->get('price');
							$art_vat[] = $arts->get('vat');
						}								
					}
					$articles = array($art_id, $art_text, $art_price, $art_amount, $art_vat);
				}

				$categories = implode('<br/> ', $categoryNames);

				$time_now = date('d-m-Y H:i');
				
				$fair = new Fair();
				$fair->load($pb->get('fair'), 'id');
				
				$organizer = new User();
				$organizer->load($fair->get('created_by'), 'id');

				$user = new User();
				$user->load($ex->get('user'), 'id');

				$fairInvoice = new FairInvoice();
				$fairInvoice->load($pb->get('fair'), 'fair');

				/*********************************************************************************/
				/*********************************************************************************/
				/*****************     SENDER ADDRESS AND PAYMENT OPTIONS        *****************/
				/*********************************************************************************/
				/*********************************************************************************/


				$sender_billing_reference = $fairInvoice->get('reference');
				$sender_billing_company_name = $fairInvoice->get('company_name');
				$sender_billing_address = $fairInvoice->get('address');
				$sender_billing_zipcode = $fairInvoice->get('zipcode');
				$sender_billing_city = $fairInvoice->get('city');
				$sender_billing_country = $fairInvoice->get('country');
				$sender_billing_orgnr = $fairInvoice->get('orgnr');
				$sender_billing_phone = $fairInvoice->get('phone');
				$sender_billing_email = $fairInvoice->get('email');
				$sender_billing_website = $fairInvoice->get('website');


				$rec_billing_company_name = $user->get('invoice_company');
				$rec_billing_address = $user->get('invoice_address');
				$rec_billing_zipcode = $user->get('invoice_zipcode');
				$rec_billing_city = $user->get('invoice_city');
				$rec_billing_country = $user->get('invoice_country');

				if ($rec_billing_country == 'Sweden')
					$rec_billing_country = 'Sverige';

				if ($rec_billing_country == 'Norway')
					$rec_billing_country = 'Norge';


				$description_label = $this->translate->{'Description'};
				$price_label = $this->translate->{'Price'};
				$amount_label = $this->translate->{'Quantity'};
				$booked_space_label = $this->translate->{'Booked stand'};
				$options_label = $this->translate->{'Options'};
				$articles_label = $this->translate->{'Articles'};
				$tax_label = $this->translate->{'Tax'};
				$parttotal_label = $this->translate->{'Subtotal'};
				$net_label = $this->translate->{'Net'};
				$rounding_label = $this->translate->{'Rounding'};
				$to_pay_label = $this->translate->{'to pay:'};
				$st_label = $this->translate->{'st'};


				$current_user = new User();
				$current_user->load($_SESSION['user_id'], 'id');



				/*************************************************************/
				/*************************************************************/
				/*****************     PRICES AND AMOUNTS        *****************
				/*************************************************************/
				/*************************************************************/

				$fairId = $fair->get('id');
				$fairname = $fair->get('name');
				$fairurl = $fair->get('url');
				$totalPrice = 0;
				$VatPrice0 = 0;
				$VatPrice12 = 0;
				$VatPrice18 = 0;
				$VatPrice25 = 0;
				$excludeVatPrice0 = 0;
				$excludeVatPrice12 = 0;
				$excludeVatPrice18 = 0;
				$excludeVatPrice25 = 0;
				$position_vat = 0;
				$currency = $fair->get('currency');
				$author = $current_user->get('name');
				$position_name = $pos->get('name');
				$position_price = $pos->get('price');
				$position_vat = $fairInvoice->get('pos_vat');
				$exhibitor_company_name = $user->get('company');
				$exhibitor_name = $user->get('name');



					/*********************************************************************************************/
					/*********************************************************************************************/
					/*****************    					SET MAIL CONTENT 	  				******************/
					/*********************************************************************************************/
					/*********************************************************************************************/

					$html = '<style>
					* {
						box-sizing:border-box;
					}
					hr {
						width:690px;
						text-align:left;
					}
					.id {
						width: 80px;
					}
					.name {
						width: 300px;
					}
					.price{
						width: 80px;
						text-align: right;
						padding-right: 12px;
					}
					.amount {
						width: 100px;
						text-align:center;
					}
					.moms {
						width:50px;
					}
					.center {
						text-align:center;
					}
					.right {
						text-align:right;
					}
					.vat {
						width: 80px;
						text-align: left;
					}
					.dark {
						background-color: #D4D4D4;
					}
					.totalprice {
						width: 445;
						text-align: right;
						font-size: 20px;
					}
					.totalprice2 {
						width: 400;
						text-align: right;
						font-size: 20px;
					}
					.pennys {
						width: 400;
						text-align: right;
						font-size: 16px;
					}
					</style>

					<table>
						<thead>
						    <tr class="dark">
						    	<th class="id">ID</th>
						        <th class="name">'.$description_label.'</th>
						        <th class="price">'.$price_label.'</th>
						        <th class="amount">'.$amount_label.'</th>
						        <th class="moms right">'.$tax_label.'</th>
						        <th class="price">'.$parttotal_label.'</th>
						    </tr>
					    </thead>
					    <tbody>';

					$html .= '<tr><td></td></tr><tr><td class="id"></td><td class="name"><b>'.$booked_space_label.'</b></td></tr>
					<tr>
						<td class="id"></td>
					    <td class="name">' . $position_name . '</td>
					    <td class="price">' . $position_price . '</td>
						<td class="amount">1 '.$st_label.'</td>
						<td class="moms right">' . $position_vat . '%</td>
						<td class="price right">' . number_format($position_price, 2, ',', ' ') . '</td>
					</tr>';

					if ($position_vat == 25) {
						$excludeVatPrice25 += $position_price;
					} else if ($position_vat == 18) {
						$excludeVatPrice18 += $position_price;
					} else {
						$excludeVatPrice0 += $position_price;
					}

					if (!empty($_POST['options']) && is_array($_POST['options'])) {
						$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$options_label.'</b></td></tr>';

						for ($row=0; $row<count($options[1]); $row++) {
						    $html .= '<tr>
						    	<td class="id">' . $options[0][$row] . '</td>
						        <td class="name">' . $options[1][$row] . '</td>
						        <td class="price">' . $options[2][$row] . '</td>
						        <td class="amount">1 '.$st_label.'</td>
						        <td class="moms right">' . $options[3][$row] . '%</td>
						        <td class="price right">' . str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')) . '</td>
						        </tr>';
					    }
					}

					if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
						
						$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$articles_label.'</b></td></tr>';
						for ($row=0; $row<count($articles[1]); $row++) {
						    $html .= '<tr>
						    	<td class="id">' . $articles[0][$row] . '</td>
						        <td class="name">' . $articles[1][$row] . '</td>
						        <td class="price">' . str_replace('.', ',', $articles[2][$row]) . '</td>
						        <td class="amount center">' . $articles[3][$row] . ' '.$st_label.'</td>
						        <td class="moms right">' . $articles[4][$row] . '%</td>
						        <td class="price right">' . str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')) . '</td>
						        </tr>';
						        $articles[2][$row] = str_replace(',', '.', $articles[2][$row]);
					    }
					}


					if (!empty($_POST['options']) && is_array($_POST['options'])) {
						for ($row=0; $row<count($options[1]); $row++) {

							if ($options[3][$row] == 25) {
								$excludeVatPrice25 += $options[2][$row];
							}
							if ($options[3][$row] == 18) {
								$excludeVatPrice18 += $options[2][$row];
							}
							if ($options[3][$row] == 12) {
								$excludeVatPrice12 += $options[2][$row];
							}
							if ($options[3][$row] == 0) {
								$excludeVatPrice0 += $options[2][$row];
							}
						}
					}

					if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
						for ($row=0; $row<count($articles[1]); $row++) {

							if ($articles[4][$row] == 25) {
								$excludeVatPrice25 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
							}
							if ($articles[4][$row] == 18) {
								$excludeVatPrice18 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
							}
							if ($articles[4][$row] == 12) {
								$excludeVatPrice12 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
							}
							if ($articles[4][$row] == 0) {
								$excludeVatPrice0 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
							}
						}
					}

					$VatPrice0 = $excludeVatPrice0;
					$VatPrice12 = $excludeVatPrice12*0.12;
					$VatPrice18 = $excludeVatPrice18*0.18;
					$VatPrice25 = $excludeVatPrice25*0.25;
					$totalPrice += $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25 + $VatPrice12 + $VatPrice18 + $VatPrice25 + $VatPrice0;

					$totalPriceRounded = round($totalPrice);
					$pennys = ($totalPriceRounded - $totalPrice);

					$html .= '
					</tbody></table>
					<hr>
					<table>
						<thead>
						    <tr>
						        <th class="vat"></th>
						        <th class="vat"></th>
						        <th class="vat"></th>
						        <th class="totalprice"></th>
						    </tr>
					    </thead>
					    <tbody>
						<tr>
							<td class="vat">'.$net_label.'</td>
							<td class="vat">'.$tax_label.' %</td>
							<td class="vat">'.$tax_label.':</td>
							<td class="totalprice"></td>
						</tr>';

					if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
						$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
						$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');
					
						$html .= '<tr>
								<td class="vat">' . str_replace('.', ',', $excludeVatPrice12) . '</td>
								<td class="vat">12,00</td>
								<td class="vat">' . str_replace('.', ',', $VatPrice12) . '</td>
								<td class="pennys">'.$rounding_label.':&nbsp;&nbsp;'
								. str_replace('.', ',', number_format($pennys, 2, ',', ' ')) . 
								'</td>
								</tr>';
					}
					if (!empty($excludeVatPrice18) && !empty($VatPrice18)) {
						$excludeVatPrice18 = number_format($excludeVatPrice18, 2, ',', ' ');
						$VatPrice18 = number_format($VatPrice18, 2, ',', ' ');
					
						$html .= '<tr>
								<td class="vat">' . str_replace('.', ',', $excludeVatPrice18) . '</td>
								<td class="vat">18,00</td>
								<td class="vat">' . str_replace('.', ',', $VatPrice18) . '</td>
								<td class="pennys">'.$rounding_label.':&nbsp;&nbsp;'
								. str_replace('.', ',', number_format($pennys, 2, ',', ' ')) . 
								'</td>
								</tr>';
					}
					if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
						$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
						$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
					
					$html .= '<tr>
							<td class="vat">' . str_replace('.', ',', $excludeVatPrice25) . '</td>
							<td class="vat">25,00</td>
							<td class="vat">' . str_replace('.', ',', $VatPrice25) . '</td>
							<td class="totalprice2">'.$currency.' '.$to_pay_label.'&nbsp;&nbsp;'
							. str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')) . 
							'</td>';
					}



					$html .= '</tbody></table>';

					$arranger_message = $_POST['arranger_message'];
					if ($arranger_message == '') {
						$arranger_message = $this->translate->{'No message was given.'};
					}
					$exhibitor_commodity = $_POST['commodity'];
					if ($exhibitor_commodity == '') {
						$exhibitor_commodity = $this->translate->{'No commodity was entered.'};
					}


				//Check mail settings and send only if setting is set
				if ($fair->wasLoaded()) {
					$mailSettings = json_decode($fair->get("mail_settings"));
					if (is_array($mailSettings->acceptPreliminaryBooking)) {
						$previous_status = posStatusToText($previous_status);
						$status = posStatusToText($status);

						if (in_array("0", $mailSettings->acceptPreliminaryBooking)) {
							$mail_organizer = new Mail($organizer->get('email'), 'booking_approved_confirm', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
							$mail_organizer->setMailVar('booking_table', $html);
							$mail_organizer->setMailVar('status', $status);
							$mail_organizer->setMailVar('company_name', $user->get('company'));
							$mail_organizer->setMailvar("exhibitor_name", $user->get("name"));
							$mail_organizer->setMailvar("event_name", $fair->get("name"));
							$mail_organizer->setMailVar('position_name', $pos->get('name'));
							$mail_organizer->setMailVar("booking_time", date('d-m-Y H:i:s', intval($booking_time)));
							$mail_organizer->setMailVar("url", BASE_URL . $fair->get("url"));
							$mail_organizer->setMailVar('position_information', $pos->get('information'));
							$mail_organizer->setMailVar('position_area', $pos->get('area'));
							$mail_organizer->setMailVar('arranger_message', $arranger_message);
							$mail_organizer->setMailVar('exhibitor_commodity', $exhibitor_commodity);
							$mail_organizer->setMailVar('exhibitor_category', $categories);
							$mail_organizer->setMailVar('edit_time', $time_now);
							$mail_organizer->send();
						}
						if (in_array("1", $mailSettings->acceptPreliminaryBooking)) {
							$mail_user = new Mail($user->get('email'), 'booking_approved_receipt', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
							$mail_user->setMailVar('booking_table', $html);
							$mail_user->setMailVar('status', $status);
							$mail_user->setMailVar('company_name', $user->get('company'));
							$mail_user->setMailvar("exhibitor_name", $user->get("name"));
							$mail_user->setMailvar("event_name", $fair->get("name"));
							$mail_user->setMailVar('event_email', $fair->get('contact_email'));
							$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
							$mail_user->setMailVar('event_website', $fair->get('website'));
							$mail_user->setMailVar('position_name', $pos->get('name'));
							$mail_user->setMailVar("booking_time", date('d-m-Y H:i:s', intval($booking_time)));
							$mail_user->setMailVar("url", BASE_URL . $fair->get("url"));
							$mail_user->setMailVar('position_information', $pos->get('information'));
							$mail_user->setMailVar('position_area', $pos->get('area'));
							$mail_user->setMailVar('arranger_message', $arranger_message);
							$mail_user->setMailVar('exhibitor_commodity', $exhibitor_commodity);
							$mail_user->setMailVar('exhibitor_category', $categories);
							$mail_user->setMailVar('edit_time', $time_now);
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

		$u = new User();
		$u->load($_SESSION['user_id'], 'id');
		$fairInvoice = new FairInvoice();
		$fairInvoice->load($_SESSION['user_fair'], 'fair');

		/* Bookings */
		$stmt = $u->db->prepare("SELECT ex.*, user.id as userid, 
			user.company, 
			pos.id AS position, 
			pos.name, 
			pos.information, 
			pos.area, 
			pos.map, 
			pos.price, 
			ex.id AS posid, 
			pos.expires 
				FROM user, 
				exhibitor AS ex, 
				fair_map_position AS pos 
					WHERE user.id = ex.user 
					AND ex.position = pos.id 
					AND ex.fair = ? 
					AND pos.status = ?
					ORDER BY ex.booking_time DESC");
		$stmt->execute(array($_SESSION['user_fair'], 2));
		$positions_unfinished = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$positions = array();

		foreach ($positions_unfinished as $pos) {
			/* Get categories */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_category_rel WHERE exhibitor = ? AND category > 0');
			$stmt->execute(array($pos['id']));
			$poscats = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$categories = array();
			$categoriesid = array();
			if (count($poscats) > 0) {
				foreach ($poscats as $cat) {
					$categoriesid[] = $cat['category'];
					$ex_category = new ExhibitorCategory();
					$ex_category->load($cat['category'], 'id');
					$categories[] = $ex_category->get('name');					
				}
			}

			$pos['categories'] = implode('|', $categories);
			$pos['categoriesid'] = implode('|', $categoriesid);

			/* Get extra options */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_option_rel WHERE exhibitor = ? AND `option` > 0');
			$stmt->execute(array($pos['id']));
			$posoptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$options = array();
			$option_id = array();
			$option_text = array();
			$option_price = array();
			$option_vat = array();

			if (count($posoptions) > 0) {
				foreach ($posoptions as $option) {
					$options[] = $option['option'];
					$ex_option = new FairExtraOption();
					$ex_option->load($option['option'], 'id');
						if ($ex_option->wasLoaded()) {
							$option_id[] = $ex_option->get('custom_id');
							$option_text[] = $ex_option->get('text');
							$option_price[] = $ex_option->get('price');
							$option_vat[] = $ex_option->get('vat');
						}	
				}
			}

			$pos['options'] = implode('|', $options);
			$pos['optionid'] = implode('|', $option_id);
			$pos['optiontext'] = implode('|', $option_text);
			$pos['optionprice'] = implode('|', $option_price);
			$pos['optionvat'] = implode('|', $option_vat);

			/* Get articles and amounts */
			$stmt = $this->db->prepare("SELECT * FROM exhibitor_article_rel AS ear LEFT JOIN fair_article AS fa ON ear.article = fa.id WHERE exhibitor = ? AND ear.amount != 0");
			$stmt->execute(array($pos['id']));
			$posarticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$articles = array();
			$amount = array();
			$article_id = array();
			$article_text = array();
			$article_price = array();
			$article_vat = array();
			$article_amount = array();

			if (count($posarticles) > 0) {
				foreach ($posarticles as $res) {
					$articles[] = $res['article'];
					$amount[] = $res['amount'];
		  			$ex_article = new FairArticle();
		  			$ex_article->load($res['article'], 'id');
						if ($ex_article->wasLoaded()) {
							$article_id[] = $ex_article->get('custom_id');
							$article_text[] = $ex_article->get('text');
							$article_price[] = $ex_article->get('price');
							$article_vat[] = $ex_article->get('vat');
							$article_amount[] = $res['amount'];
						}
				}
			}

			$pos['articles'] = implode('|', $articles);
			$pos['amount'] = implode('|', $amount);
			$pos['articleid'] = implode('|', $article_id);
			$pos['articletext'] = implode('|', $article_text);
			$pos['articleprice'] = implode('|', $article_price);
			$pos['articlevat'] = implode('|', $article_vat);
			$pos['articleamount'] = implode('|', $article_amount);
			$pos['vat'] = $fairInvoice->get('pos_vat');

			$positions[$pos['position']] = $pos;
		}

		/* Reservations */
		$stmt = $u->db->prepare("SELECT ex.*, 
			user.id as userid, 
			user.company, 
			pos.id AS position, 
			pos.name, 
			pos.information, 
			pos.area, 
			pos.map, 
			pos.price, 
			ex.id AS posid, 
			pos.expires 
				FROM user, 
				exhibitor AS ex, 
				fair_map_position AS pos 
					WHERE user.id = ex.user 
					AND ex.position = pos.id 
					AND ex.fair = ? 
					AND pos.status = ? 
					AND ex.clone = 0
					ORDER BY ex.booking_time DESC");
		$stmt->execute(array($_SESSION['user_fair'], 1));
		$rpositions_unfinished = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$rpositions = array();

		foreach ($rpositions_unfinished as $pos) {
			/* Get categories */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_category_rel WHERE exhibitor = ? AND category > 0');
			$stmt->execute(array($pos['id']));
			$poscats = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$categories = array();
      		$categoriesid = array();
			if (count($poscats) > 0) {
				foreach ($poscats as $cat) {
					$categoriesid[] = $cat['category'];
					$ex_category = new ExhibitorCategory();
					$ex_category->load($cat['category'], 'id');
					$categories[] = $ex_category->get('name');					
				}
			}

			$pos['categories'] = implode('|', $categories);
      		$pos['categoriesid'] = implode('|', $categoriesid);

			/* Get extra options */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_option_rel WHERE exhibitor = ? AND `option` > 0');
			$stmt->execute(array($pos['id']));
			$posoptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    	  	$options = array();
			$option_id = array();
			$option_text = array();
			$option_price = array();
			$option_vat = array();

			if (count($posoptions) > 0) {
				foreach ($posoptions as $option) {
          			$options[] = $option['option'];
					$ex_option = new FairExtraOption();
					$ex_option->load($option['option'], 'id');
						if ($ex_option->wasLoaded()) {
							$option_id[] = $ex_option->get('custom_id');
							$option_text[] = $ex_option->get('text');
							$option_price[] = $ex_option->get('price');
							$option_vat[] = $ex_option->get('vat');
						}	
				}
			}

      		$pos['options'] = implode('|', $options);
			$pos['optionid'] = implode('|', $option_id);
			$pos['optiontext'] = implode('|', $option_text);
			$pos['optionprice'] = implode('|', $option_price);
			$pos['optionvat'] = implode('|', $option_vat);

			/* Get invoice id */
			$stmt = $this->db->prepare("SELECT id, fair, status, sent, r_name FROM exhibitor_invoice WHERE exhibitor = ?");
			$stmt->execute(array($pos['id']));
			$posinvoiceid = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$invoicecompany = array();
			$invoiceid = array();
			$invoiceposname = array();
			$invoicestatus = array();
			$invoicesent = array();
			$invoice_credited_id = array();
			if (count($posinvoiceid) > 0) {
				$arrlength = count($posinvoiceid);
				for($x = 0; $x < $arrlength; $x++) {
					 $invoicecompany[] = $posinvoiceid[$x]['r_name'];
				     $invoiceid[] = $posinvoiceid[$x]['id'];
				     $invoicestatus[] = $posinvoiceid[$x]['status'];
				     $invoicesent[] = $posinvoiceid[$x]['sent'];
				     $invoice_credited = new ExhibitorInvoiceCredited();
				     $invoice_credited->load($posinvoiceid[$x]['id'], 'invoice');
				     if ($invoice_credited->wasLoaded()) {
				     	$invoice_credited_id[] = $invoice_credited->get('cid');
				     }
					$stmt = $this->db->prepare("SELECT text FROM exhibitor_invoice_rel WHERE `invoice` = ? AND `fair` = ? AND type = 'space'");
					$stmt->execute(array($posinvoiceid[$x]['id'], $posinvoiceid[$x]['fair']));
					$invoiceposname = $stmt->fetch(PDO::FETCH_ASSOC);
				}
			}

			/* Get articles and amounts */
			$stmt = $this->db->prepare("SELECT * FROM exhibitor_article_rel AS ear LEFT JOIN fair_article AS fa ON ear.article = fa.id WHERE exhibitor = ? AND ear.amount != 0");
			$stmt->execute(array($pos['id']));
			$posarticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

      		$articles = array();
      		$amount = array();
			$article_id = array();
			$article_text = array();
			$article_price = array();
			$article_vat = array();
			$article_amount = array();

			if (count($posarticles) > 0) {
				foreach ($posarticles as $res) {
          			$articles[] = $res['article'];
          			$amount[] = $res['amount'];
			  		$ex_article = new FairArticle();
			  		$ex_article->load($res['article'], 'id');
						if ($ex_article->wasLoaded()) {
							$article_id[] = $ex_article->get('custom_id');
							$article_text[] = $ex_article->get('text');
							$article_price[] = $ex_article->get('price');
							$article_vat[] = $ex_article->get('vat');
							$article_amount[] = $res['amount'];
						}
				}
			}

			$pos['articles'] = implode('|', $articles);
			$pos['amount'] = implode('|', $amount);
			$pos['articleid'] = implode('|', $article_id);
			$pos['articletext'] = implode('|', $article_text);
			$pos['articleprice'] = implode('|', $article_price);
			$pos['articlevat'] = implode('|', $article_vat);
			$pos['articleamount'] = implode('|', $article_amount);
			$pos['vat'] = $fairInvoice->get('pos_vat');
			$pos['invoicecompany'] = implode('|', $invoicecompany);
			$pos['invoiceposname'] = implode('|', $invoiceposname);
			$pos['invoiceid'] = implode('|', $invoiceid);
			$pos['invoicestatus'] = implode('|', $invoicestatus);
			$pos['invoicesent'] = implode('|', $invoicesent);
			$pos['invoicecreditedid'] = implode('|', $invoice_credited_id);

			$rpositions[$pos['position']] = $pos;
		}

		/* Cloned reservations */
		$stmt = $u->db->prepare("SELECT ex.*, 
			user.id as userid, 
			user.company, 
			pos.id AS position, 
			pos.name, 
			pos.information, 
			pos.area, 
			pos.map, 
			pos.price, 
			ex.id AS posid, 
			pos.expires 
				FROM user, 
				exhibitor AS ex, 
				fair_map_position AS pos 
					WHERE user.id = ex.user 
					AND ex.position = pos.id 
					AND ex.fair = ? 
					AND pos.status = ? 
					AND ex.clone = 1
					ORDER BY ex.booking_time DESC");
		$stmt->execute(array($_SESSION['user_fair'], 1));
		$rcpositions_unfinished = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$rcpositions = array();

		foreach ($rcpositions_unfinished as $pos) {
			/* Get categories */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_category_rel WHERE exhibitor = ? AND category > 0');
			$stmt->execute(array($pos['id']));
			$poscats = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$categories = array();
      		$categoriesid = array();
			if (count($poscats) > 0) {
				foreach ($poscats as $cat) {
					$categoriesid[] = $cat['category'];
					$ex_category = new ExhibitorCategory();
					$ex_category->load($cat['category'], 'id');
					$categories[] = $ex_category->get('name');					
				}
			}

			$pos['categories'] = implode('|', $categories);
      		$pos['categoriesid'] = implode('|', $categoriesid);

			/* Get extra options */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_option_rel WHERE exhibitor = ? AND `option` > 0');
			$stmt->execute(array($pos['id']));
			$posoptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    	  	$options = array();
			$option_id = array();
			$option_text = array();
			$option_price = array();
			$option_vat = array();

			if (count($posoptions) > 0) {
				foreach ($posoptions as $option) {
          			$options[] = $option['option'];
					$ex_option = new FairExtraOption();
					$ex_option->load($option['option'], 'id');
						if ($ex_option->wasLoaded()) {
							$option_id[] = $ex_option->get('custom_id');
							$option_text[] = $ex_option->get('text');
							$option_price[] = $ex_option->get('price');
							$option_vat[] = $ex_option->get('vat');
						}	
				}
			}

      		$pos['options'] = implode('|', $options);
			$pos['optionid'] = implode('|', $option_id);
			$pos['optiontext'] = implode('|', $option_text);
			$pos['optionprice'] = implode('|', $option_price);
			$pos['optionvat'] = implode('|', $option_vat);

			// Get invoice id
			$stmt = $this->db->prepare("SELECT id, status, sent FROM exhibitor_invoice WHERE exhibitor = ?");
			$stmt->execute(array($pos['id']));
			$posinvoiceid = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$invoiceid = array();
			$invoicestatus = array();
			$invoicesent = array();
			$invoice_credited_id = array();
			if (count($posinvoiceid) > 0) {
				$arrlength = count($posinvoiceid);
				for($x = 0; $x < $arrlength; $x++) {
				     $invoiceid[] = $posinvoiceid[$x]['id'];
				     $invoicestatus[] = $posinvoiceid[$x]['status'];
				     $invoicesent[] = $posinvoiceid[$x]['sent'];
				     $invoice_credited = new ExhibitorInvoiceCredited();
				     $invoice_credited->loadids($posinvoiceid[$x]['id'], 'invoice');
				     if ($invoice_credited->wasLoaded()) {
				     	$invoice_credited_id[] = $invoice_credited->get('cid');
				     }
				}
			}
			// Get confirmation links

			$confirmlink = new ExhibitorLink();
			$confirmlink->load($pos['id'], 'exhibitor');

			// Get articles and amounts
			$stmt = $this->db->prepare("SELECT * FROM exhibitor_article_rel AS ear LEFT JOIN fair_article AS fa ON ear.article = fa.id WHERE exhibitor = ? AND ear.amount != 0");
			$stmt->execute(array($pos['id']));
			$posarticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

      		$articles = array();
      		$amount = array();
			$article_id = array();
			$article_text = array();
			$article_price = array();
			$article_vat = array();
			$article_amount = array();

			if (count($posarticles) > 0) {
				foreach ($posarticles as $res) {
          			$articles[] = $res['article'];
          			$amount[] = $res['amount'];
			  		$ex_article = new FairArticle();
			  		$ex_article->load($res['article'], 'id');
						if ($ex_article->wasLoaded()) {
							$article_id[] = $ex_article->get('custom_id');
							$article_text[] = $ex_article->get('text');
							$article_price[] = $ex_article->get('price');
							$article_vat[] = $ex_article->get('vat');
							$article_amount[] = $res['amount'];
						}
				}
			}

			$pos['articles'] = implode('|', $articles);
			$pos['amount'] = implode('|', $amount);
			$pos['articleid'] = implode('|', $article_id);
			$pos['articletext'] = implode('|', $article_text);
			$pos['articleprice'] = implode('|', $article_price);
			$pos['articlevat'] = implode('|', $article_vat);
			$pos['articleamount'] = implode('|', $article_amount);
			$pos['linkstatus'] = $confirmlink->get('status');
			$pos['linkdate'] = $confirmlink->get('linkdate');
			$pos['vat'] = $fairInvoice->get('pos_vat');
			$pos['invoiceid'] = implode('|', $invoiceid);
			$pos['invoicestatus'] = implode('|', $invoicestatus);
			$pos['invoicesent'] = implode('|', $invoicesent);
			$pos['invoicecreditedid'] = implode('|', $invoice_credited_id);

			$rcpositions[$pos['position']] = $pos;
		}

		/* History of deleted boookings and reservations */
		$stmt = $u->db->prepare("SELECT ex.*, 
			user.id as userid, 
			user.company, 
			pos.id AS position, 
			pos.name, 
			pos.information, 
			pos.area, 
			pos.price, 
			pos.map 
				FROM user, 
				exhibitor_history AS ex, 
				fair_map_position AS pos 
					WHERE user.id = ex.user 
					AND ex.position = pos.id 
					AND ex.fair = ?
					ORDER BY ex.booking_time DESC");
		$stmt->execute(array($_SESSION['user_fair']));
		$positions_deleted = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$del_positions = array();

		foreach ($positions_deleted as $pos) {
			/* Get categories */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_category_rel WHERE exhibitor = ? AND category > 0');
			$stmt->execute(array($pos['id']));
			$poscats = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$categories = array();
			if (count($poscats) > 0) {
				foreach ($poscats as $cat) {
					$ex_category = new ExhibitorCategory();
					$ex_category->load($cat['category'], 'id');
					$categories[] = $ex_category->get('name');					
				}
			}

			$pos['categories'] = implode('|', $categories);

			/* Get extra options */
			$stmt = $u->db->prepare('SELECT * FROM exhibitor_option_rel WHERE exhibitor = ? AND `option` > 0');
			$stmt->execute(array($pos['id']));
			$posoptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$option_id = array();
			$option_text = array();
			$option_price = array();
			$option_vat = array();

			if (count($posoptions) > 0) {
				foreach ($posoptions as $option) {
					$ex_option = new FairExtraOption();
					$ex_option->load($option['option'], 'id');
						if ($ex_option->wasLoaded()) {
							$option_id[] = $ex_option->get('custom_id');
							$option_text[] = $ex_option->get('text');
							$option_price[] = $ex_option->get('price');
							$option_vat[] = $ex_option->get('vat');
						}	
				}
			}

			$pos['optionid'] = implode('|', $option_id);
			$pos['optiontext'] = implode('|', $option_text);
			$pos['optionprice'] = implode('|', $option_price);
			$pos['optionvat'] = implode('|', $option_vat);

			/* Get articles and amounts */
			$stmt = $this->db->prepare("SELECT * FROM exhibitor_article_rel AS ear LEFT JOIN fair_article AS fa ON ear.article = fa.id WHERE exhibitor = ? AND ear.amount != 0");
			$stmt->execute(array($pos['id']));
			$posarticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$article_id = array();
			$article_text = array();
			$article_price = array();
			$article_vat = array();
			$article_amount = array();

			if (count($posarticles) > 0) {
				foreach ($posarticles as $res) {
						$ex_article = new FairArticle();
						$ex_article->load($res['article'], 'id');
						if ($ex_article->wasLoaded()) {
							$article_id[] = $ex_article->get('custom_id');
							$article_text[] = $ex_article->get('text');
							$article_price[] = $ex_article->get('price');
							$article_vat[] = $ex_article->get('vat');
							$article_amount[] = $res['amount'];
						}
				}
			}

			$pos['articleid'] = implode('|', $article_id);
			$pos['articletext'] = implode('|', $article_text);
			$pos['articleprice'] = implode('|', $article_price);
			$pos['articlevat'] = implode('|', $article_vat);
			$pos['articleamount'] = implode('|', $article_amount);
			$pos['vat'] = $fairInvoice->get('pos_vat');

			$del_positions[$pos['position']] = $pos;
		}


	// History of deleted Preliminary bookings
	$stmt = $u->db->prepare("SELECT prel.*, 
		user.id as userid, 
		user.company, 
		pos.id AS position, 
		pos.name, 
		pos.information, 
		pos.area, 
		pos.price, 
		pos.map 
			FROM user, 
			preliminary_booking_history AS prel, 
			fair_map_position AS pos 
				WHERE user.id = prel.user 
				AND prel.position = pos.id 
				AND prel.fair = ?");
	$stmt->execute(array($_SESSION['user_fair']));
	$prel_del = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$del_prelpos = array();

	foreach ($prel_del as $pos) {
		/* Get categories */
		$poscats = explode('|', $pos['categories']);

		$categories = array();
		if (count($poscats) > 0) {
			foreach ($poscats as $cat) {
				$ex_category = new ExhibitorCategory();
				$ex_category->load($cat, 'id');
				$categories[] = $ex_category->get('name');					
			}
		}


		/* Get extra options */
		$posoptions = explode('|', $pos['options']);

		$option_id = array();
		$option_text = array();
		$option_price = array();
		$option_vat = array();

		if (count($posoptions) > 0) {
			foreach ($posoptions as $option) {
				$ex_option = new FairExtraOption();
				$ex_option->load($option, 'id');
					if ($ex_option->wasLoaded()) {
						$option_id[] = $ex_option->get('custom_id');
						$option_text[] = $ex_option->get('text');
						$option_price[] = $ex_option->get('price');
						$option_vat[] = $ex_option->get('vat');
					}	
			}
		}

		$pos['optionid'] = implode('|', $option_id);
		$pos['optiontext'] = implode('|', $option_text);
		$pos['optionprice'] = implode('|', $option_price);
		$pos['optionvat'] = implode('|', $option_vat);

		/* Get articles and amounts */
		$posarticles = explode('|', $pos['articles']);
		$posamounts = explode('|', $pos['amount']);

		$posamounts_length = count($posamounts);
		$posarticles_length = count($posarticles);

		$article_id = array();
		$article_text = array();
		$article_price = array();
		$article_vat = array();
		$article_amount = array();

		if ($posamounts_length == $posarticles_length) {
			foreach (array_combine($posarticles, $posamounts) as $article => $amount) {
					$ex_article = new FairArticle();
					$ex_article->load($article, 'id');
					if ($ex_article->wasLoaded()) {
						$article_id[] = $ex_article->get('custom_id');
						$article_text[] = $ex_article->get('text');
						$article_price[] = $ex_article->get('price');
						$article_vat[] = $ex_article->get('vat');
						$article_amount[] = $amount;
					}
			}
		}

		$pos['articleid'] = implode('|', $article_id);
		$pos['articletext'] = implode('|', $article_text);
		$pos['articleprice'] = implode('|', $article_price);
		$pos['articlevat'] = implode('|', $article_vat);
		$pos['articleamount'] = implode('|', $article_amount);
		$pos['vat'] = $fairInvoice->get('pos_vat');
		$pos['categories'] = implode('|', $categories);
		$del_prelpos[$pos['position']] = $pos;
	}

		
		// Active Preliminary bookings
	$stmt = $u->db->prepare("SELECT prel.*, 
		user.id as userid, 
		user.company, 
		pos.id AS position, 
		pos.name, 
		pos.information, 
		pos.area, 
		pos.price, 
		pos.map, 
		prel.id AS id
			FROM user, 
			preliminary_booking AS prel, 
			fair_map_position AS pos 
				WHERE user.id = prel.user 
				AND prel.position = pos.id 
				AND pos.status = 0 
				AND prel.fair = ?
				ORDER BY prel.booking_time DESC");
	$stmt->execute(array($_SESSION['user_fair']));
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$prelpos = array();

	foreach ($result as $pos) {
		/* Get categories */
		$poscats = explode('|', $pos['categories']);

		$categories = array();
		if (count($poscats) > 0) {
			foreach ($poscats as $cat) {
				$ex_category = new ExhibitorCategory();
				$ex_category->load($cat, 'id');
				$categories[] = $ex_category->get('name');					
			}
		}


		/* Get extra options */
		$posoptions = explode('|', $pos['options']);

		$option_id = array();
		$option_text = array();
		$option_price = array();
		$option_vat = array();

		if (count($posoptions) > 0) {
			foreach ($posoptions as $option) {
				$ex_option = new FairExtraOption();
				$ex_option->load($option, 'id');
					if ($ex_option->wasLoaded()) {
						$option_id[] = $ex_option->get('custom_id');
						$option_text[] = $ex_option->get('text');
						$option_price[] = $ex_option->get('price');
						$option_vat[] = $ex_option->get('vat');
					}	
			}
		}

		$pos['optionid'] = implode('|', $option_id);
		$pos['optiontext'] = implode('|', $option_text);
		$pos['optionprice'] = implode('|', $option_price);
		$pos['optionvat'] = implode('|', $option_vat);

		/* Get articles and amounts */
		$posarticles = explode('|', $pos['articles']);
		$posamounts = explode('|', $pos['amount']);

		$posamounts_length = count($posamounts);
		$posarticles_length = count($posarticles);

		$article_id = array();
		$article_text = array();
		$article_price = array();
		$article_vat = array();
		$article_amount = array();

		if ($posamounts_length == $posarticles_length) {
			foreach (array_combine($posarticles, $posamounts) as $article => $amount) {
					$ex_article = new FairArticle();
					$ex_article->load($article, 'id');
					if ($ex_article->wasLoaded()) {
						$article_id[] = $ex_article->get('custom_id');
						$article_text[] = $ex_article->get('text');
						$article_price[] = $ex_article->get('price');
						$article_vat[] = $ex_article->get('vat');
						$article_amount[] = $amount;
					}
			}
		}

		$pos['articleid'] = implode('|', $article_id);
		$pos['articletext'] = implode('|', $article_text);
		$pos['articleprice'] = implode('|', $article_price);
		$pos['articlevat'] = implode('|', $article_vat);
		$pos['articleamount'] = implode('|', $article_amount);
		$pos['vat'] = $fairInvoice->get('pos_vat');
		$pos['categoriesid'] = $pos['categories'];
		$pos['categories'] = implode('|', $categories);

		$prelpos[$pos['id']] = $pos;
	}

		// Inactive Preliminary bookings
	$stmt = $u->db->prepare("SELECT prel.*, 
		user.id as userid, 
		user.company, 
		pos.id AS position, 
		pos.name, 
		pos.information, 
		pos.area, 
		pos.price, 
		pos.map,
		prel.id AS id
			FROM user, 
			preliminary_booking AS prel, 
			fair_map_position AS pos 
				WHERE user.id = prel.user 
				AND prel.position = pos.id 
				AND pos.status <> 0 
				AND prel.fair = ?
				ORDER BY prel.booking_time DESC");
	$stmt->execute(array($_SESSION['user_fair']));
	$prel_inactive = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$iprelpos = array();

	foreach ($prel_inactive as $pos) {
		/* Get categories */
		$poscats = explode('|', $pos['categories']);

		$categories = array();
		if (count($poscats) > 0) {
			foreach ($poscats as $cat) {
				$ex_category = new ExhibitorCategory();
				$ex_category->load($cat, 'id');
				$categories[] = $ex_category->get('name');					
			}
		}


		/* Get extra options */
		$posoptions = explode('|', $pos['options']);

		$option_id = array();
		$option_text = array();
		$option_price = array();
		$option_vat = array();

		if (count($posoptions) > 0) {
			foreach ($posoptions as $option) {
				$ex_option = new FairExtraOption();
				$ex_option->load($option, 'id');
					if ($ex_option->wasLoaded()) {
						$option_id[] = $ex_option->get('custom_id');
						$option_text[] = $ex_option->get('text');
						$option_price[] = $ex_option->get('price');
						$option_vat[] = $ex_option->get('vat');
					}	
			}
		}

		$pos['optionid'] = implode('|', $option_id);
		$pos['optiontext'] = implode('|', $option_text);
		$pos['optionprice'] = implode('|', $option_price);
		$pos['optionvat'] = implode('|', $option_vat);

		/* Get articles and amounts */
		$posarticles = explode('|', $pos['articles']);
		$posamounts = explode('|', $pos['amount']);

		$posamounts_length = count($posamounts);
		$posarticles_length = count($posarticles);

		$article_id = array();
		$article_text = array();
		$article_price = array();
		$article_vat = array();
		$article_amount = array();

		if ($posamounts_length == $posarticles_length) {
			foreach (array_combine($posarticles, $posamounts) as $article => $amount) {
					$ex_article = new FairArticle();
					$ex_article->load($article, 'id');
					if ($ex_article->wasLoaded()) {
						$article_id[] = $ex_article->get('custom_id');
						$article_text[] = $ex_article->get('text');
						$article_price[] = $ex_article->get('price');
						$article_vat[] = $ex_article->get('vat');
						$article_amount[] = $amount;
					}
			}
		}

		$pos['articleid'] = implode('|', $article_id);
		$pos['articletext'] = implode('|', $article_text);
		$pos['articleprice'] = implode('|', $article_price);
		$pos['articlevat'] = implode('|', $article_vat);
		$pos['articleamount'] = implode('|', $article_amount);
		$pos['vat'] = $fairInvoice->get('pos_vat');
		$pos['categoriesid'] = $pos['categories'];
		$pos['categories'] = implode('|', $categories);

		$iprelpos[$pos['id']] = $pos;
	}


		/* Fair registrations */
		$stmt_fregistrations = $this->db->prepare("SELECT fa.*, u.company FROM fair_registration AS fa LEFT JOIN user AS u ON u.id = fa.user WHERE fa.fair = ? ORDER BY fa.booking_time DESC");
		$stmt_fregistrations->execute(array($_SESSION['user_fair']));
		$result = $stmt_fregistrations->fetchAll(PDO::FETCH_ASSOC);
		$fair_registrations = array();

		foreach ($result as $pos) {
			/* Get categories */
			$poscats = explode('|', $pos['categories']);

			$categories = array();
			if (count($poscats) > 0) {
				foreach ($poscats as $cat) {
					$ex_category = new ExhibitorCategory();
					$ex_category->load($cat, 'id');
					$categories[] = $ex_category->get('name');					
				}
			}


			/* Get extra options */
			$posoptions = explode('|', $pos['options']);

			$option_id = array();
			$option_text = array();
			$option_price = array();
			$option_vat = array();

			if (count($posoptions) > 0) {
				foreach ($posoptions as $option) {
					$ex_option = new FairExtraOption();
					$ex_option->load($option, 'id');
						if ($ex_option->wasLoaded()) {
							$option_id[] = $ex_option->get('custom_id');
							$option_text[] = $ex_option->get('text');
							$option_price[] = $ex_option->get('price');
							$option_vat[] = $ex_option->get('vat');
						}	
				}
			}

			$pos['optionid'] = implode('|', $option_id);
			$pos['optiontext'] = implode('|', $option_text);
			$pos['optionprice'] = implode('|', $option_price);
			$pos['optionvat'] = implode('|', $option_vat);

			/* Get articles and amounts */
			$posarticles = explode('|', $pos['articles']);
			$posamounts = explode('|', $pos['amount']);

			$posamounts_length = count($posamounts);
			$posarticles_length = count($posarticles);

			$article_id = array();
			$article_text = array();
			$article_price = array();
			$article_vat = array();
			$article_amount = array();

			if ($posamounts_length == $posarticles_length) {
				foreach (array_combine($posarticles, $posamounts) as $article => $amount) {
						$ex_article = new FairArticle();
						$ex_article->load($article, 'id');
						if ($ex_article->wasLoaded()) {
							$article_id[] = $ex_article->get('custom_id');
							$article_text[] = $ex_article->get('text');
							$article_price[] = $ex_article->get('price');
							$article_vat[] = $ex_article->get('vat');
							$article_amount[] = $amount;
						}
				}
			}

			$pos['articleid'] = implode('|', $article_id);
			$pos['articletext'] = implode('|', $article_text);
			$pos['articleprice'] = implode('|', $article_price);
			$pos['articlevat'] = implode('|', $article_vat);
			$pos['articleamount'] = implode('|', $article_amount);
			$pos['categories'] = implode('|', $categories);

			$fair_registrations[] = $pos;
		}


		/* Deleted fair registrations */
		$stmt_fdregistrations = $this->db->prepare("SELECT fa.*, u.company FROM fair_registration_history AS fa LEFT JOIN user AS u ON u.id = fa.user WHERE fa.fair = ? ORDER BY fa.booking_time DESC");
		$stmt_fdregistrations->execute(array($_SESSION['user_fair']));
		$result = $stmt_fdregistrations->fetchAll(PDO::FETCH_ASSOC);
		$fair_registrations_deleted = array();

		foreach ($result as $pos) {
			/* Get categories */
			$poscats = explode('|', $pos['categories']);

			$categories = array();
			if (count($poscats) > 0) {
				foreach ($poscats as $cat) {
					$ex_category = new ExhibitorCategory();
					$ex_category->load($cat, 'id');
					$categories[] = $ex_category->get('name');					
				}
			}


			/* Get extra options */
			$posoptions = explode('|', $pos['options']);

			$option_id = array();
			$option_text = array();
			$option_price = array();
			$option_vat = array();

			if (count($posoptions) > 0) {
				foreach ($posoptions as $option) {
					$ex_option = new FairExtraOption();
					$ex_option->load($option, 'id');
						if ($ex_option->wasLoaded()) {
							$option_id[] = $ex_option->get('custom_id');
							$option_text[] = $ex_option->get('text');
							$option_price[] = $ex_option->get('price');
							$option_vat[] = $ex_option->get('vat');
						}	
				}
			}

			$pos['optionid'] = implode('|', $option_id);
			$pos['optiontext'] = implode('|', $option_text);
			$pos['optionprice'] = implode('|', $option_price);
			$pos['optionvat'] = implode('|', $option_vat);

			/* Get articles and amounts */
			$posarticles = explode('|', $pos['articles']);
			$posamounts = explode('|', $pos['amount']);

			$posamounts_length = count($posamounts);
			$posarticles_length = count($posarticles);

			$article_id = array();
			$article_text = array();
			$article_price = array();
			$article_vat = array();
			$article_amount = array();

			if ($posamounts_length == $posarticles_length) {
				foreach (array_combine($posarticles, $posamounts) as $article => $amount) {
						$ex_article = new FairArticle();
						$ex_article->load($article, 'id');
						if ($ex_article->wasLoaded()) {
							$article_id[] = $ex_article->get('custom_id');
							$article_text[] = $ex_article->get('text');
							$article_price[] = $ex_article->get('price');
							$article_vat[] = $ex_article->get('vat');
							$article_amount[] = $amount;
						}
				}
			}

			$pos['articleid'] = implode('|', $article_id);
			$pos['articletext'] = implode('|', $article_text);
			$pos['articleprice'] = implode('|', $article_price);
			$pos['articlevat'] = implode('|', $article_vat);
			$pos['articleamount'] = implode('|', $article_amount);
			$pos['categories'] = implode('|', $categories);

			$fair_registrations_deleted[] = $pos;
		}

			$this->setNoTranslate('positions', $positions);
			$this->setNoTranslate('rpositions', $rpositions);
			$this->setNoTranslate('rcpositions', $rcpositions);
			$this->setNoTranslate('prelpos', $prelpos);
			$this->setNoTranslate('iprelpos', $iprelpos);
			$this->setNoTranslate('fair_registrations', $fair_registrations);
			$this->setNoTranslate('del_positions', $del_positions);
			$this->setNoTranslate('del_prelpos', $del_prelpos);
			$this->setNoTranslate('fair_registrations_deleted', $fair_registrations_deleted);
			if ($fairInvoice->get('default_expirationdate')) {
				$date = date('d-m-Y', $fairInvoice->get('default_expirationdate'));
			} else {
				$date = '';
			}
			$this->setNoTranslate('default_invoice_date', $date);
			$this->set('deletion_comment', 'Enter comment about deletion');
			$this->set('deletion_comment_placeholder', 'You can leave this field empty if you want.');
			$this->set('booked_notfound', 'No booked booths was found.');
			$this->set('reserv_notfound', 'No reservations was found.');
			$this->set('reserv_cloned_notfound', 'No cloned reservations was found.');
			$this->set('prel_notfound', 'No preliminary bookings was found.');
			$this->set('del_prel_notfound', 'No previously deleted preliminary bookings was found.');
			$this->set('headline', 'Booked stand spaces');
			$this->set('rheadline', 'Reservations');
			$this->set('rcheadline', 'Cloned reservations');
			$this->set('prel_table', 'Preliminary bookings (active)');
			$this->set('prel_table_inactive', 'Preliminary bookings (inactive)');
			$this->set('prel_table_deleted', 'Preliminary bookings (deleted)');
			$this->set('fair_registrations_headline', 'Registrations');
			$this->set('fregistrations_notfound', 'No registrations was found.');
			$this->set('fair_registrations_deleted_headline', 'Registrations (deleted)');
			$this->set('fregistrations_deleted_notfound', 'No deleted registrations was found.');
			$this->set('booked_label', 'Booked');
			$this->set('reserved_label', 'Reserved');
			$this->set('unknown_label', 'Unknown');
			$this->set('tr_fair', 'Fair');
			$this->set('tr_status', 'Status');
			$this->set('tr_pos', 'Stand space');
			$this->set('tr_area', 'Area');
			$this->set('tr_booker', 'Booked by');
			$this->set('tr_field', 'Trade');
			$this->set('tr_time', 'Time of booking');
			$this->set('tr_last_edited', 'Last edited');
			$this->set('tr_reserved_until', 'Reserved until');
			$this->set('tr_message', 'Message to organizer in list');
			$this->set('tr_view', 'View on map');
			$this->set('tr_viewinvoice', 'View invoice');
			$this->set('tr_created', 'Created');
			$this->set('tr_sent', 'Sent');
			$this->set('tr_credited', 'Krediterad');
			$this->set('tr_invoicestatus', 'Invoice');
			$this->set('tr_linkstatus', 'Confirmation link');
			$this->set('tr_createinvoice', 'Create invoice');
			$this->set('tr_creditinvoice', 'Credit invoice');
			$this->set('tr_cancelinvoice', 'Cancel invoice');
			$this->set('tr_mark_as_sent', 'Mark invoice as sent');
			$this->set('tr_copy', 'Copy to map');
			$this->set('tr_edit', 'Edit');
			$this->set('tr_review', 'Review');
			$this->set('tr_delete', 'Delete');
			$this->set('tr_approve', 'Approve');
			$this->set('tr_deny', 'Deny');
			$this->set('tr_reserve', 'Reserve stand space');
			$this->set('tr_comments', 'Notes');
			$this->set('tr_alternatives', 'Alternatives');
			$this->set('never_edited_label', 'Never edited');
			$this->set('confirm_delete', 'Are you sure that you want to remove stand space');
			$this->set('confirm_create_invoice', 'Create invoice for');
			$this->set('confirm_credit_invoice', 'Credit invoice for');
			$this->set('confirm_cancel_invoice', 'Cancel invoice for');
			$this->set('confirm_mark_as_sent', 'Mark invoice as sent for');
			$this->set('confirm_delete_registration', 'Are you sure that you want to remove the registration for');
			$this->set('export', 'Export to Excel');
			$this->set('col_export_err', 'Select at least one column in order to export!');
			$this->set('row_export_err', 'Select at least one row in order to export!');
			$this->set('send_sms_label', 'Send SMS to selected Exhibitors');
			$this->set('send_cloned_mail', 'Sends confirm emails the checked exhibitors');
			$this->set('ok_label', 'OK');
		}

	public function markAsSent($id) {
		setAuthLevel(2);

		$ex_invoice = new ExhibitorInvoice();
		$ex_invoice->load($id, 'exhibitor');

		$fair = new Fair();
		$fair->load($ex_invoice->get('fair'), 'id');

		$this->setNoTranslate('fair', $fair);
		
		if( userLevel() == 2 ){
			$sql = "SELECT * FROM fair_user_relation WHERE user = ? AND fair = ?";
			$prep = $this->db->prepare($sql);
			$prep->execute(array($_SESSION['user_id'], $fair->get('id')));
			$result = $prep->fetch(PDO::FETCH_ASSOC);
			if(!$result) {
				$this->setNoTranslate('hasRights', false);
				$hasRights = false;
			} else {
				$this->setNoTranslate('hasRights', true);
				$hasRights = true;
			}

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

		} else {

			$this->setNoTranslate('hasRights', true);
			$hasRights = true;
		}

		if (!$hasRights)
			return;

		$now = time();
		$fairId = $fair->get('id');
		$invoice_id = $ex_invoice->get('id');

		// Update the invoice sent status in the database
		$stmt_invoice = $this->db->prepare("UPDATE exhibitor_invoice SET sent = ? WHERE exhibitor = ? AND id = ? AND fair = ?");
		$stmt_invoice->execute(array($now, $id, $invoice_id, $fairId));

	}

	public function arrangerMessage($type = '', $id = 0) {

		setAuthLevel(1);

		if ($type !== '' && $id > 0) {

			$message = '';

			if ($type == 'preliminary') {
				$prel_booking = new PreliminaryBooking();
				$prel_booking->load($id, 'id');
				$message = $prel_booking->get('arranger_message');
			} else if ($type == 'registration') {
				$fair_registration = new FairRegistration();
				$fair_registration->load($id, 'id');
				$message = $fair_registration->get('arranger_message');
			} else if ($type == 'history_registration') {
				$fair_registration_history = new FairRegistrationHistory();
				$fair_registration_history->load($id, 'id');
				$message = $fair_registration_history->get('arranger_message');
			} else if ($type == 'history_preliminary') {
				$prel_booking_history = new PreliminaryBookingHistory();
				$prel_booking_history->load($id, 'id');
				$message = $prel_booking_history->get('arranger_message');
			}  else if ($type == 'history_deleted') {
				$exhibitor = new Exhibitor();
				$exhibitor->loadDeleted($id, 'id');
				$message = $exhibitor->get('arranger_message');
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
		$this->set('th_contactperson', 'Contact person');
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

			$arr = new Arranger();
			$arr->load($_SESSION['user_id'], 'id');
			$this->setNoTranslate('arranger', $arr);
			$this->setNoTranslate('fairs', $arr->get('fairs'));

		} else {

			$adminFair = new Fair();
			$adminFair->load($_SESSION['user_fair'], 'id');
			$this->setNoTranslate('adminowner', $adminFair->get('created_by'));

			$stmt = $this->Administrator->db->prepare("SELECT id FROM fair WHERE created_by = ?");
			$stmt->execute(array($adminFair->get('created_by')));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$fairs = array();

			foreach ($result as $res) {

				$f = new Fair();
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

					$adminFair = new Fair();
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
							$this->set('user_message', 'You have to select at least one map for each selected fair.');
							$this->setNoTranslate('error', true);
							$errors = true;
							break;
						}
					}

					if (!$errors) {
						//If no errors occurred, proceed with creation
						$aId = $this->Administrator->save();
						$oldful = new FairUserRelation();
						$oldful->load($aId, 'user');

						$stmt = $this->Administrator->db->prepare("DELETE FROM fair_user_relation WHERE user = ?");
						$stmt->execute(array($aId));

						foreach ($_POST['fair_permission'] as $fairId) {
							$rel = new FairUserRelation();
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

	// Fair Registrations
	public function deleteRegistration($id = 0) {
		setAuthLevel(2);

		$registration = new FairRegistration();
		$registration->load($id, 'id');

		$fair = new Fair();
		$fair->load2($_SESSION['user_fair'], 'id');

		$u = new User();
		$u->load2($registration->get('user'), 'id');

		$current_user = new User();
		$current_user->load2($_SESSION['user_id'], 'id');

		$organizer = new User();
		$organizer->load2($fair->get('created_by'), 'id');

		$stmt_history = "INSERT INTO `fair_registration_history` SELECT * FROM `fair_registration` WHERE id = '{$id}'";
		$query3 = $this->db->query($stmt_history);
		$sql = "DELETE FROM `fair_registration` WHERE id = '{$id}'";
		$query = $this->db->query($sql);

		$time_now = date('d-m-Y H:i');

		$comment = $_POST['comment'];
		if ($comment == '') {
			$comment = $this->translate->{'No message was given.'};
		}

		//Check mail settings and send only if setting is set
		if ($fair->wasLoaded()) {
			$mailSettings = json_decode($fair->get("mail_settings"));
			if (is_array($mailSettings->registrationCancelled)) {

				if (in_array("1", $mailSettings->registrationCancelled)) {
					$mail_exhibitor = new Mail($u->get('email'), 'registration_cancelled_receipt', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
					$mail_exhibitor->setMailVar('exhibitor_name', $u->get('name'));
					$mail_exhibitor->setMailVar('company_name', $u->get('company'));
					$mail_exhibitor->setMailVar('cancelled_name', $current_user->get('name'));
					$mail_exhibitor->setMailVar('event_name', $fair->get('name'));
					$mail_exhibitor->setMailVar('event_email', $fair->get('contact_email'));
					$mail_exhibitor->setMailVar('event_phone', $fair->get('contact_phone'));
					$mail_exhibitor->setMailVar('event_website', $fair->get('website'));
					$mail_exhibitor->setMailVar("url", BASE_URL . $fair->get("url"));
					$mail_exhibitor->setMailVar('edit_time', $time_now);
					$mail_exhibitor->setMailVar('comment', $comment);
					$mail_exhibitor->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
					$mail_exhibitor->send();
				}
				if ($current_user->get('email') != $organizer->get('email')) {
					if (in_array("2", $mailSettings->registrationCancelled)) {
						$mail_user = new Mail($current_user->get('email'), 'registration_cancelled_confirm', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
						$mail_user->setMailVar('exhibitor_name', $u->get('name'));
						$mail_user->setMailVar('company_name', $u->get('company'));
						$mail_user->setMailVar('cancelled_name', $current_user->get('name'));
						$mail_user->setMailVar('event_name', $fair->get('name'));
						$mail_user->setMailVar("url", BASE_URL . $fair->get("url"));
						$mail_user->setMailVar('edit_time', $time_now);
						$mail_user->setMailVar('comment', $comment);
						$mail_user->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
						$mail_user->send();
					}
				}

				if (in_array("0", $mailSettings->registrationCancelled)) {
					$mail_organizer = new Mail($organizer->get('email'), 'registration_cancelled_confirm', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
					$mail_organizer->setMailVar('exhibitor_name', $u->get('name'));
					$mail_organizer->setMailVar('company_name', $u->get('company'));
					$mail_organizer->setMailVar('event_name', $fair->get('name'));
					$mail_organizer->setMailVar("url", BASE_URL . $fair->get("url"));
					$mail_organizer->setMailVar('edit_time', $time_now);
					$mail_organizer->setMailVar('comment', $comment);
					$mail_organizer->setMailVar('cancelled_name', $current_user->get('name'));
					$mail_organizer->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
					$mail_organizer->send();
				}
			}
		}
	}

	public function creditInvoicePDF($id) {
		setAuthLevel(2);

		require_once ROOT.'lib/tcpdf/tcpdf.php';

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$ex_invoice = new ExhibitorInvoice();
		$ex_invoice->load($id, 'exhibitor');

		$fair = new Fair();
		$fair->load($ex_invoice->get('fair'), 'id');

		$this->setNoTranslate('fair', $fair);
		
		if( userLevel() == 2 ){
			$sql = "SELECT * FROM fair_user_relation WHERE user = ? AND fair = ?";
			$prep = $this->db->prepare($sql);
			$prep->execute(array($_SESSION['user_id'], $fair->get('id')));
			$result = $prep->fetch(PDO::FETCH_ASSOC);
			if(!$result) {
				$this->setNoTranslate('hasRights', false);
				$hasRights = false;
			} else {
				$this->setNoTranslate('hasRights', true);
				$hasRights = true;
			}

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

		} else {

			$this->setNoTranslate('hasRights', true);
			$hasRights = true;
		}

		if (!$hasRights)
			return;



/*********************************************************************************/
/*********************************************************************************/
/*****************     SENDER ADDRESS AND PAYMENT OPTIONS        *****************/
/*********************************************************************************/
/*********************************************************************************/
				$sender_billing_reference = $ex_invoice->get('s_reference');
				$sender_billing_company_name = $ex_invoice->get('s_name');
				$sender_billing_address = $ex_invoice->get('s_address');
				$sender_billing_zipcode = $ex_invoice->get('s_zipcode');
				$sender_billing_city = $ex_invoice->get('s_city');
				$sender_billing_country = $ex_invoice->get('s_country');
				$sender_billing_website = $ex_invoice->get('s_website');
				$sender_billing_phone = $ex_invoice->get('s_phone');
				$sender_billing_email = $ex_invoice->get('s_email');
				$sender_billing_orgnr = $ex_invoice->get('orgnr');
				$sender_billing_bank_no = $ex_invoice->get('bank_no');
				$sender_billing_postgiro = $ex_invoice->get('postgiro');
				$sender_billing_vat_no = $ex_invoice->get('vat_no');
				$sender_billing_iban_no = $ex_invoice->get('iban_no');
				$sender_billing_swift_no = $ex_invoice->get('swift_no');


				$rec_billing_company_name = $ex_invoice->get('r_name');
				$rec_billing_address = $ex_invoice->get('r_address');
				$rec_billing_zipcode = $ex_invoice->get('r_zipcode');
				$rec_billing_city = $ex_invoice->get('r_city');
				$rec_billing_country = $ex_invoice->get('r_country');

				if ($rec_billing_country == 'Sweden')
					$rec_billing_country = 'Sverige';

				if ($rec_billing_country == 'Norway')
					$rec_billing_country = 'Norge';


				$invoice_for_label = $this->translate->{'Invoice for'};
				$printdate_label = $this->translate->{'Print date'};
				$postgiro_label = $this->translate->{'Postgiro'};
				$iban_label = $this->translate->{'IBAN'};
				$swift_label = $this->translate->{'SWIFT'};
				$orgnr_label = $this->translate->{'Org.no'};
				$vat_label = $this->translate->{'TAX.no'};
				$bankgiro_label = $this->translate->{'Bank number'};
				$description_label = $this->translate->{'Description'};
				$price_label = $this->translate->{'Price'};
				$phone_label = $this->translate->{'Phone'};
				$email_label = $this->translate->{'Email'};
				$amount_label = $this->translate->{'Quantity'};
				$booked_space_label = $this->translate->{'Booked stand'};
				$options_label = $this->translate->{'Options'};
				$articles_label = $this->translate->{'Articles'};
				$tax_label = $this->translate->{'Tax'};
				$parttotal_label = $this->translate->{'Subtotal'};
				$net_label = $this->translate->{'Net'};
				$rounding_label = $this->translate->{'Rounding'};
				$credited_label = $this->translate->{'Credited'};
				$credit_invoice_label = $this->translate->{'Credit note'};
				$to_pay_label = $this->translate->{'to pay:'};
				$address_label = $this->translate->{'Address'};
				$organization_label = $this->translate->{'Organization'};
				$payment_info_label = $this->translate->{'Payment information'};
				$s_reference_label = $this->translate->{'Our reference'};
				$r_reference_label = $this->translate->{'Your reference'};
				$invoice_no_label = $this->translate->{'Credits invoice'};
				$invoice_date_label = $this->translate->{'Invoice date'};
				$invoice_expirationdate_label = $this->translate->{'Expiration date'};
				$st_label = $this->translate->{'st'};


				if ($sender_billing_postgiro == '')
					$postgiro_label = '';

				if ($sender_billing_iban_no == '')
					$iban_label = '';

				if ($sender_billing_swift_no == '')
					$swift_label = '';
				$current_user = new User();
				$current_user->load($_SESSION['user_id'], 'id');
		//		$fairInvoiceExpDate = date('Y-m-d');


/*************************************************************/
/*************************************************************/
/*****************     PRICES AND AMOUNTS        *****************
/*************************************************************/
/*************************************************************/

				$fairId = $fair->get('id');
				$totalPrice = 0;
				$VatPrice0 = 0;
				$VatPrice12 = 0;
				$VatPrice18 = 0;
				$VatPrice25 = 0;
				$excludeVatPrice0 = 0;
				$excludeVatPrice12 = 0;
				$excludeVatPrice18 = 0;
				$excludeVatPrice25 = 0;
				$position_vat = 0;
				$currency = $fair->get('currency');
				$author = $current_user->get('name');


				// Positions

				$stmt = $this->db->prepare("SELECT text, price, vat, information FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ? AND type = 'space'");
				$stmt->execute(array($ex_invoice->get('id'), $fairId));
				$invoice_position = $stmt->fetch(PDO::FETCH_ASSOC);
				
				$position_name = $invoice_position['text'];
				$position_information = $invoice_position['information'];
				$position_price = $invoice_position['price'];
				$position_vat = $invoice_position['vat'];


				// Options

				$stmt = $this->db->prepare("SELECT custom_id, text, price, vat FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ? AND type = 'option'");
				$stmt->execute(array($ex_invoice->get('id'), $fairId));
				$invoice_options = $stmt->fetchAll(PDO::FETCH_ASSOC);

				$options = array();

				
				foreach ($invoice_options as $opts) {
							$option_id[] = $opts['custom_id'];
							$option_text[] = $opts['text'];
							$option_price[] = $opts['price'];
							$option_vat[] = $opts['vat'];
				}
					
				$options = array($option_id, $option_text, $option_price, $option_vat);
				//die(var_dump($options));


				// Articles

				$stmt = $this->db->prepare("SELECT custom_id, text, price, amount, vat FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ? AND type = 'article'");
				$stmt->execute(array($ex_invoice->get('id'), $fairId));
				$invoice_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

//				die(var_dump($invoice_articles));

				$articles = array();

					foreach ($invoice_articles as $arts) {
							$art_id[] = $arts['custom_id'];
							$art_text[] = $arts['text'];
							$art_price[] = $arts['price'];
							$art_amount[] = $arts['amount'];
							$art_vat[] = $arts['vat'];
					}

				$articles = array($art_id, $art_text, $art_price, $art_amount, $art_vat);
				
				//die(var_dump($articles));

				$exhibitor_company_name = $ex_invoice->get('r_name');
				$exhibitor_name = $ex_invoice->get('r_reference');
				$date = date('d-m-Y');
				$now = time();
				$expirationdate = $ex_invoice->get('expires');
				$invoice_id = $ex_invoice->get('id');


				$stmt_invoiceid = $this->db->prepare("SELECT cid FROM exhibitor_invoice_credited as cid WHERE fair = ? order by cid desc limit 1");
				$stmt_invoiceid->execute(array($fairId));
				$res = $stmt_invoiceid->fetch(PDO::FETCH_ASSOC);
				$credit_invoice_id = $res['cid'];


/******************************************************************************/
/******************************************************************************/
/*****************     FIND OUT WHAT INVOICE ID TO USE        *****************/
/******************************************************************************/
/******************************************************************************/
				if (is_null($credit_invoice_id)) {
					$stmt_invoiceid2 = $this->db->prepare("SELECT credit_invoice_id_start as cid FROM fair_invoice WHERE fair = ?");
					$stmt_invoiceid2->execute(array($fairId));
					$res = $stmt_invoiceid2->fetch();
					$credit_invoice_id = $res['cid'];

					// uppdatera exhibitor tabellen
/*
					$sql = "INSERT INTO exhibitor_invoice (id, ex_user, fair, created, author, exhibitor, expires, r_name, r_address, r_zipcode, r_city, r_country, s_name, s_address, s_zipcode, s_city, s_country, s_website, s_phone, orgnr, bank_no, postgiro, vat_no, iban_no, swift_no) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
					$params = array();
					
					$stmt_invoice = $this->db->prepare("INSERT INTO exhibitor_invoice as id WHERE fair = ? order by id desc limit 1");
					$stmt_invoice->execute(array($fairId));
					$res = $stmt_invoice->fetch(PDO::FETCH_ASSOC);
					$invoice_id = $res['id'];
					*/
					if (is_null($credit_invoice_id)){
						$credit_invoice_id += 1;
					}
				} else {

					$credit_invoice_id += 1;

				}
				// Insert the invoice data to database
				$stmt_invoice = $this->db->prepare("INSERT INTO exhibitor_invoice_credited (cid, fair, created, author, invoice) VALUES (?, ?, ?, ?, ?)");
				$stmt_invoice->execute(array($credit_invoice_id, $fairId, $now, $author, $invoice_id));

				// Update the active invoice to credited in the database
				$stmt_invoice_parent = $this->db->prepare("UPDATE exhibitor_invoice SET `status` = 3 WHERE id = ? AND fair = ?");
				$stmt_invoice_parent->execute(array($invoice_id, $fairId));

				$logo_name = array();
				foreach(glob(ROOT.'public/images/fairs/'.$fairId.'/logotype/*') as $filename) {
					$logo_name[] = (basename($filename) . "\n");
				}

				if (!$logo_name) {
					$logo_name = BASE_URL.'/images/fairs/cfslogo.png';
				} else {
					$logo_name = BASE_URL.'/images/fairs/'. $fairId . '/logotype/' . $logo_name[0];
				}

//die(var_dump($articles[]));
/*********************************************************************************************/
/*********************************************************************************************/
/*****************    				SET DOCUMENT INFORMATION   				******************/
/*********************************************************************************************/
/*********************************************************************************************/

		$pdf->SetCreator('Chartbooker Fair System');
		$pdf->SetAuthor($author);
		$pdf->SetTitle($invoice_for_label . ' ' . $exhibitor_company_name);
		//$pdf->SetSubject('TCPDF Tutorial');
		//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

		$pdf->setHtmlHeader('
			<table>
				<tr>
					<td style="width:335px;">
						<img style="height:70px;" src="'. $logo_name . '"/>
					</td>
					<td>
						<br/><br/><b style="font-size:23px; text-alight:right;">' . $credit_invoice_label . ' ' . $credit_invoice_id . '</b><br>' . $printdate_label . ': ' . $date . '
					</td>
				</tr>
			</table>');

		$pdf->setHtmlFooter('
			<hr>
			<br/>
			<table>
				<tr>
					<td style="width:200px;"><b>'. $address_label .'</b></td>
					<td style="width:200px;"><b>'. $organization_label .'</b></td>
					<td style="width:200px;"><b>'. $payment_info_label .'</b></td>
				</tr>
				<tr>
					<td style="width:200px;">' . $sender_billing_company_name . '</td>
					<td style="width:200px;">' . $orgnr_label . ' &nbsp; ' . $sender_billing_orgnr . '</td>
					<td style="width:200px;">' . $bankgiro_label . ' &nbsp;' . $sender_billing_bank_no . '</td>
				</tr>
				<tr>
					<td style="width:200px;"><br>' . $sender_billing_address . '</td>
					<td style="width:200px;">' . $vat_label . ' &nbsp;' . $sender_billing_vat_no . '</td>
					<td style="width:200px;">' . $postgiro_label . ' &nbsp;' . $sender_billing_postgiro . '</td>
				</tr>
				<tr>
					<td style="width:200px;">' . $sender_billing_zipcode . ' ' . $sender_billing_city . '</td>
					<td style="width:200px;">' . $phone_label . ': ' . $sender_billing_phone . '</td>
					<td style="width:200px;">' . $iban_label . ' &nbsp;' . $sender_billing_iban_no . '</td>
				</tr>
				<tr>
					<td style="width:200px;">' . $sender_billing_website . '</td>
					<td style="width:200px;">' . $email_label . ': ' . $sender_billing_email . '</td>
					<td style="width:200px;">' . $swift_label . ' &nbsp;' . $sender_billing_swift_no . '</td>
				</tr>
			<br>
			</table>');

//		$pdf->setFooterData($tc=array(0,64,0), $lc=array(0,64,128));

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(30);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


		// set default font subsetting mode
		$pdf->setFontSubsetting(true);

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('helvetica', '', 11, '', true);

		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage();

		// set text shadow effect
		//$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>0.5, 'blend_mode'=>'Normal'));


$html = '<style>
tr .normal {
	width: 150px;
}
tr .normal2 {
	width:250px;
}
</style>
<table>
		<tr class="normal">
			<td class="normal"></td>
			<td class="normal"></td>
			<td class="short"></td>
			<td class="normal"></td>
		</tr>
		<tr class="normal">
			<td class="normal"><b>'.$s_reference_label.':</b></td>
			<td class="normal">' . $sender_billing_reference . '</td>
			<td class="short"></td>
			<td class="normal2">' . $rec_billing_company_name . '</td>
		</tr>
		<tr class="normal">
			<td class="normal"><b>'.$r_reference_label.':</b></td>
			<td class="normal">' . $exhibitor_name . '</td>
			<td class="short"></td>
			<td class="normal2">' . $rec_billing_address . '</td>
		</tr>
		<tr class="normal">
			<td class="normal"><b>'.$invoice_no_label.':</b></td>
			<td class="normal">' . $invoice_id . '</td>
			<td class="short"></td>
			<td class="normal2">' . $rec_billing_zipcode . ' ' . $rec_billing_city . '</td>
		</tr>
		<tr class="normal">
			<td class="normal"><b>'.$invoice_date_label.':</b></td>
			<td class="normal">' . $date . '</td>
			<td class="short"></td>
			<td class="normal2">' . $rec_billing_country . '</td>
		</tr>
</table>


<br /><br /><br />
';
$html .= '<style>
* {
	box-sizing:border-box;
}
.short {
	width: 31px;
}
.id {
	width: 80px;
}
.name {
	width: 300px;
}
.price{
	width: 80px;
	text-align: right;
}
.amount {
	width: 70px;
}
.moms {
	width:50px;
}
.center {
	text-align:center;
}
.left {
	text-align:left;
}
.right {
	text-align:right;
}
.vat {
	width: 80px;
	text-align: left;
}
.dark {
	background-color: #D4D4D4;
}
.totalprice {
	width: 350;
	text-align: right;
	font-size: 20px;
}
.totalprice2 {
	width: 400;
	text-align: right;
	font-size: 20px;
}
.pennys {
	width: 400;
	text-align: right;
	font-size: 16px;
}
</style>

<table>
	<thead>
	    <tr class="dark">
	    	<th class="id">ID</th>
	        <th class="name">'.$description_label.'</th>
	        <th class="price">'.$price_label.'</th>
	        <th class="amount right">'.$amount_label.'</th>
	        <th class="moms right">'.$tax_label.'</th>
	        <th class="price">'.$parttotal_label.'</th>
	    </tr>
    </thead>
    <tbody>';

$html .= '<tr><td></td></tr><tr><td class="id"></td><td class="name"><b>'.$booked_space_label.'</b></td></tr>
<tr>
	<td class="id"></td>
    <td class="name">' . $position_name . '</td>
    <td class="price">' . $position_price . '</td>
	<td class="amount right">1 '.$st_label.'</td>
	<td class="moms right">' . $position_vat . '%</td>
	<td class="price right">' . number_format($position_price, 2, ',', ' ') . '</td>
</tr>
<tr>
	<td class="id"></td>
    <td class="name">' . $position_information . '</td>
    <td class="price"></td>
	<td class="amount right"></td>
	<td class="moms right"></td>
	<td class="price right"></td>
</tr>';

	if ($position_vat == 25) {
		$excludeVatPrice25 += $position_price;
	} else if ($position_vat == 18) {
		$excludeVatPrice18 += $position_price;
	} else {
		$excludeVatPrice0 += $position_price;
	}

if (!empty($invoice_options) && is_array($invoice_options)) {
	$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$options_label.'</b></td></tr>';

	for ($row=0; $row<count($options[1]); $row++) {
	    $html .= '<tr>
	    	<td class="id">' . $options[0][$row] . '</td>
	        <td class="name">' . $options[1][$row] . '</td>
	        <td class="price">' . $options[2][$row] . '</td>
	        <td class="amount right">1 '.$st_label.'</td>
	        <td class="moms right">' . $options[3][$row] . '%</td>
	        <td class="price right">' . str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')) . '</td>
	        </tr>';
    }
}

if (!empty($invoice_articles) && is_array($invoice_articles)) {
	
	$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$articles_label.'</b></td></tr>';
	for ($row=0; $row<count($articles[1]); $row++) {
	    $html .= '<tr>
	    	<td class="id">' . $articles[0][$row] . '</td>
	        <td class="name">' . $articles[1][$row] . '</td>
	        <td class="price">' . str_replace('.', ',', $articles[2][$row]) . '</td>
	        <td class="amount right">' . $articles[3][$row] . ' '.$st_label.'</td>
	        <td class="moms right">' . $articles[4][$row] . '%</td>
	        <td class="price right">' . str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')) . '</td>
	        </tr>';
	        $articles[2][$row] = str_replace(',', '.', $articles[2][$row]);
    }
}


if (!empty($invoice_options) && is_array($invoice_options)) {
	for ($row=0; $row<count($options[1]); $row++) {

		if ($options[3][$row] == 25) {
			$excludeVatPrice25 += $options[2][$row];
		}
		if ($options[3][$row] == 18) {
			$excludeVatPrice18 += $options[2][$row];
		}
		if ($options[3][$row] == 12) {
			$excludeVatPrice12 += $options[2][$row];
		}
		if ($options[3][$row] == 0) {
			$excludeVatPrice0 += $options[2][$row];
		}
	}
}

if (!empty($invoice_articles) && is_array($invoice_articles)) {
	for ($row=0; $row<count($articles[1]); $row++) {

		if ($articles[4][$row] == 25) {
			$excludeVatPrice25 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 18) {
			$excludeVatPrice18 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 12) {
			$excludeVatPrice12 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 0) {
			$excludeVatPrice0 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
	}
}

$VatPrice0 = $excludeVatPrice0;
$VatPrice12 = $excludeVatPrice12*0.12;
$VatPrice18 = $excludeVatPrice18*0.18;
$VatPrice25 = $excludeVatPrice25*0.25;
$totalPrice += $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25 + $VatPrice12 + $VatPrice18 + $VatPrice25 + $VatPrice0;

$totalPriceRounded = round($totalPrice);
$pennys = ($totalPriceRounded - $totalPrice);

$html .= '
	<hr>
	<br />
	<tr>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="totalprice"></td>
	</tr>	
	<tr>
		<td class="vat">'.$net_label.'</td>
		<td class="vat">'.$tax_label.' %</td>
		<td class="vat">'.$tax_label.':</td>
		<td class="totalprice"></td>
	</tr>';

if (!empty($excludeVatPrice0) && !empty($VatPrice0)) {
	$excludeVatPrice0 = number_format($excludeVatPrice0, 2, ',', ' ');
	$VatPrice0 = number_format($VatPrice0, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice0) . '</td>
		<td class="vat">0,00</td>
		<td class="vat">0,00</td>	
		<td class="totalprice"></td>
	</tr>';
}

if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
	$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
	$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice12) . '</td>
		<td class="vat">12,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice12) . '</td>	
		<td class="totalprice"></td>
	</tr>';
}

if (!empty($excludeVatPrice18) && !empty($VatPrice18)) {
	$excludeVatPrice18 = number_format($excludeVatPrice18, 2, ',', ' ');
	$VatPrice18 = number_format($VatPrice18, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice18) . '</td>
		<td class="vat">18,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice18) . '</td>	
		<td class="totalprice"></td>
	</tr>';
}

if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
	$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
	$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice25) . '</td>
		<td class="vat">25,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice25) . '</td>	
		<td class="totalprice"></td>
	</tr>';
}
$html .= '
	<tr>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="pennys">'.$rounding_label.':&nbsp;&nbsp;'
		. str_replace('.', ',', number_format($pennys, 2, ',', ' ')) . 
		'</td>
	</tr>
	<tr>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="totalprice2">'.$currency.' '.$credited_label.':&nbsp;&nbsp;'
		. str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')) . 
		'</td>
	</tr>';



$html .= '</tbody></table>';



// Print text using writeHTMLCell()
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->lastPage();

// ---------------------------------------------------------
if (!file_exists(ROOT.'public/invoices/fairs/'.$fairId)) {
	mkdir(ROOT.'public/invoices/fairs/'.$fairId);
	chmod(ROOT.'public/invoices/fairs/'.$fairId, 0775);
	mkdir(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors');
	chmod(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors', 0775);
}

if (!file_exists(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$ex_invoice->get('ex_user'))) {	
	mkdir(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$ex_invoice->get('ex_user'));
	chmod(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$ex_invoice->get('ex_user'), 0775);
}

$exhibitor_company_name = str_replace('/', '-', $exhibitor_company_name);
//Close and output PDF document
$pdf->Output(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$ex_invoice->get('exhibitor').'/'.$exhibitor_company_name . '-' . $position_name . '-' . $credit_invoice_id . '_credited.pdf', 'F');

header('Location: '.BASE_URL.'invoices/fairs/'.$fairId.'/exhibitors/'.$ex_invoice->get('exhibitor').'/'.$exhibitor_company_name . '-' . $position_name . '-' . $credit_invoice_id . '_credited.pdf');
//============================================================+
// END OF FILE
//============================================================+

} 



	public function cancelInvoicePDF($id) {
		setAuthLevel(2);

		require_once ROOT.'lib/tcpdf/tcpdf.php';

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$ex_invoice = new ExhibitorInvoice();
		$ex_invoice->load($id, 'exhibitor');

		$fair = new Fair();
		$fair->load($ex_invoice->get('fair'), 'id');

		$this->setNoTranslate('fair', $fair);
		
		if( userLevel() == 2 ){
			$sql = "SELECT * FROM fair_user_relation WHERE user = ? AND fair = ?";
			$prep = $this->db->prepare($sql);
			$prep->execute(array($_SESSION['user_id'], $fair->get('id')));
			$result = $prep->fetch(PDO::FETCH_ASSOC);
			if(!$result) {
				$this->setNoTranslate('hasRights', false);
				$hasRights = false;
			} else {
				$this->setNoTranslate('hasRights', true);
				$hasRights = true;
			}

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

		} else {

			$this->setNoTranslate('hasRights', true);
			$hasRights = true;
		}

		if (!$hasRights)
			return;


/*********************************************************************************/
/*********************************************************************************/
/*****************     SENDER ADDRESS AND PAYMENT OPTIONS        *****************/
/*********************************************************************************/
/*********************************************************************************/
				$sender_billing_reference = $ex_invoice->get('s_reference');
				$sender_billing_company_name = $ex_invoice->get('s_name');
				$sender_billing_address = $ex_invoice->get('s_address');
				$sender_billing_zipcode = $ex_invoice->get('s_zipcode');
				$sender_billing_city = $ex_invoice->get('s_city');
				$sender_billing_country = $ex_invoice->get('s_country');
				$sender_billing_website = $ex_invoice->get('s_website');
				$sender_billing_phone = $ex_invoice->get('s_phone');
				$sender_billing_email = $ex_invoice->get('s_email');
				$sender_billing_orgnr = $ex_invoice->get('orgnr');
				$sender_billing_bank_no = $ex_invoice->get('bank_no');
				$sender_billing_postgiro = $ex_invoice->get('postgiro');
				$sender_billing_vat_no = $ex_invoice->get('vat_no');
				$sender_billing_iban_no = $ex_invoice->get('iban_no');
				$sender_billing_swift_no = $ex_invoice->get('swift_no');


				$rec_billing_company_name = $ex_invoice->get('r_name');
				$rec_billing_address = $ex_invoice->get('r_address');
				$rec_billing_zipcode = $ex_invoice->get('r_zipcode');
				$rec_billing_city = $ex_invoice->get('r_city');
				$rec_billing_country = $ex_invoice->get('r_country');

				if ($rec_billing_country == 'Sweden')
					$rec_billing_country = 'Sverige';

				if ($rec_billing_country == 'Norway')
					$rec_billing_country = 'Norge';


				$invoice_for_label = $this->translate->{'Invoice for'};
				$printdate_label = $this->translate->{'Print date'};
				$postgiro_label = $this->translate->{'Postgiro'};
				$iban_label = $this->translate->{'IBAN'};
				$swift_label = $this->translate->{'SWIFT'};
				$orgnr_label = $this->translate->{'Org.no'};
				$vat_label = $this->translate->{'TAX.no'};
				$bankgiro_label = $this->translate->{'Bank number'};
				$description_label = $this->translate->{'Description'};
				$price_label = $this->translate->{'Price'};
				$phone_label = $this->translate->{'Phone'};
				$email_label = $this->translate->{'Email'};
				$amount_label = $this->translate->{'Quantity'};
				$booked_space_label = $this->translate->{'Booked stand'};
				$options_label = $this->translate->{'Options'};
				$articles_label = $this->translate->{'Articles'};
				$tax_label = $this->translate->{'Tax'};
				$parttotal_label = $this->translate->{'Subtotal'};
				$net_label = $this->translate->{'Net'};
				$rounding_label = $this->translate->{'Rounding'};
				$cancelled_label = $this->translate->{'Cancelled'};
				$cancel_invoice_label = $this->translate->{'cancel note'};
				$to_pay_label = $this->translate->{'to pay:'};
				$address_label = $this->translate->{'Address'};
				$organization_label = $this->translate->{'Organization'};
				$payment_info_label = $this->translate->{'Payment information'};
				$s_reference_label = $this->translate->{'Our reference'};
				$r_reference_label = $this->translate->{'Your reference'};
				$invoice_cancels_label = $this->translate->{'cancels invoice'};
				$invoice_date_label = $this->translate->{'Invoice date'};
				$invoice_expirationdate_label = $this->translate->{'Expiration date'};
				$st_label = $this->translate->{'st'};


				if ($sender_billing_postgiro == '')
					$postgiro_label = '';

				if ($sender_billing_iban_no == '')
					$iban_label = '';

				if ($sender_billing_swift_no == '')
					$swift_label = '';
				$current_user = new User();
				$current_user->load($_SESSION['user_id'], 'id');
		//		$fairInvoiceExpDate = date('Y-m-d');


/*************************************************************/
/*************************************************************/
/*****************     PRICES AND AMOUNTS        *****************
/*************************************************************/
/*************************************************************/

				$fairId = $fair->get('id');
				$totalPrice = 0;
				$VatPrice0 = 0;
				$VatPrice12 = 0;
				$VatPrice18 = 0;
				$VatPrice25 = 0;
				$excludeVatPrice0 = 0;
				$excludeVatPrice12 = 0;
				$excludeVatPrice18 = 0;
				$excludeVatPrice25 = 0;
				$position_vat = 0;
				$currency = $fair->get('currency');
				$author = $current_user->get('name');


				// Positions

				$stmt = $this->db->prepare("SELECT text, price, vat, information FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ? AND type = 'space'");
				$stmt->execute(array($ex_invoice->get('id'), $fairId));
				$invoice_position = $stmt->fetch(PDO::FETCH_ASSOC);
				
				$position_name = $invoice_position['text'];
				$position_information = $invoice_position['information'];
				$position_price = $invoice_position['price'];
				$position_vat = $invoice_position['vat'];


				// Options

				$stmt = $this->db->prepare("SELECT custom_id, text, price, vat FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ? AND type = 'option'");
				$stmt->execute(array($ex_invoice->get('id'), $fairId));
				$invoice_options = $stmt->fetchAll(PDO::FETCH_ASSOC);

				$options = array();

				
				foreach ($invoice_options as $opts) {
							$option_id[] = $opts['custom_id'];
							$option_text[] = $opts['text'];
							$option_price[] = $opts['price'];
							$option_vat[] = $opts['vat'];
				}
					
				$options = array($option_id, $option_text, $option_price, $option_vat);
				//die(var_dump($options));


				// Articles

				$stmt = $this->db->prepare("SELECT custom_id, text, price, amount, vat FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ? AND type = 'article'");
				$stmt->execute(array($ex_invoice->get('id'), $fairId));
				$invoice_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

//				die(var_dump($invoice_articles));

				$articles = array();

					foreach ($invoice_articles as $arts) {
							$art_id[] = $arts['custom_id'];
							$art_text[] = $arts['text'];
							$art_price[] = $arts['price'];
							$art_amount[] = $arts['amount'];
							$art_vat[] = $arts['vat'];
					}

				$articles = array($art_id, $art_text, $art_price, $art_amount, $art_vat);
				
				//die(var_dump($articles));

				$exhibitor_company_name = $ex_invoice->get('r_name');
				$exhibitor_name = $ex_invoice->get('r_reference');
				$date = date('d-m-Y');
				$now = time();
				$expirationdate = $ex_invoice->get('expires');
				$invoice_id = $ex_invoice->get('id');


				$cancel_invoice_id = $invoice_id;

				// Update the active invoice to cancelled in the database
				$stmt_invoice_parent = $this->db->prepare("UPDATE exhibitor_invoice SET `status` = 4 WHERE exhibitor = ?");
				$stmt_invoice_parent->execute(array($id));
				$stmt_invoice_parent2 = $this->db->prepare("INSERT INTO `exhibitor_invoice_history` SELECT * FROM `exhibitor_invoice` WHERE exhibitor = ?");
				$stmt_invoice_parent2->execute(array($id));
				$stmt_invoice_parent3 = $this->db->prepare("DELETE FROM `exhibitor_invoice` WHERE exhibitor = ?");
				$stmt_invoice_parent3->execute(array($id));
				// Insert the invoice data to database
				$stmt_invoice = $this->db->prepare("INSERT INTO exhibitor_invoice_cancelled (fair, created, author, invoice) VALUES (?, ?, ?, ?)");
				$stmt_invoice->execute(array($fairId, $now, $author, $invoice_id));

				$logo_name = array();
				foreach(glob(ROOT.'public/images/fairs/'.$fairId.'/logotype/*') as $filename) {
					$logo_name[] = (basename($filename) . "\n");
				}

				if (!$logo_name) {
					$logo_name = BASE_URL.'/images/fairs/cfslogo.png';
				} else {
					$logo_name = BASE_URL.'/images/fairs/'. $fairId . '/logotype/' . $logo_name[0];
				}

//die(var_dump($articles[]));
/*********************************************************************************************/
/*********************************************************************************************/
/*****************    				SET DOCUMENT INFORMATION   				******************/
/*********************************************************************************************/
/*********************************************************************************************/

		$pdf->SetCreator('Chartbooker Fair System');
		$pdf->SetAuthor($author);
		$pdf->SetTitle($invoice_for_label . ' ' . $exhibitor_company_name);
		//$pdf->SetSubject('TCPDF Tutorial');
		//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

		$pdf->setHtmlHeader('
			<table>
				<tr>
					<td style="width:335px;">
						<img style="height:70px;" src="'. $logo_name . '"/>
					</td>
					<td>
						<br/><br/><b style="font-size:23px; text-alight:right;">' . $cancel_invoice_label . ' ' . $cancel_invoice_id . '</b><br>' . $printdate_label . ': ' . $date . '
					</td>
				</tr>
			</table>');

		$pdf->setHtmlFooter('
			<hr>
			<br/>
			<table>
				<tr>
					<td style="width:200px;"><b>'. $address_label .'</b></td>
					<td style="width:200px;"><b>'. $organization_label .'</b></td>
					<td style="width:200px;"><b>'. $payment_info_label .'</b></td>
				</tr>
				<tr>
					<td style="width:200px;">' . $sender_billing_company_name . '</td>
					<td style="width:200px;">' . $orgnr_label . ' &nbsp; ' . $sender_billing_orgnr . '</td>
					<td style="width:200px;">' . $bankgiro_label . ' &nbsp;' . $sender_billing_bank_no . '</td>
				</tr>
				<tr>
					<td style="width:200px;"><br>' . $sender_billing_address . '</td>
					<td style="width:200px;">' . $vat_label . ' &nbsp;' . $sender_billing_vat_no . '</td>
					<td style="width:200px;">' . $postgiro_label . ' &nbsp;' . $sender_billing_postgiro . '</td>
				</tr>
				<tr>
					<td style="width:200px;">' . $sender_billing_zipcode . ' ' . $sender_billing_city . '</td>
					<td style="width:200px;">' . $phone_label . ': ' . $sender_billing_phone . '</td>
					<td style="width:200px;">' . $iban_label . ' &nbsp;' . $sender_billing_iban_no . '</td>
				</tr>
				<tr>
					<td style="width:200px;">' . $sender_billing_website . '</td>
					<td style="width:200px;">' . $email_label . ': ' . $sender_billing_email . '</td>
					<td style="width:200px;">' . $swift_label . ' &nbsp;' . $sender_billing_swift_no . '</td>
				</tr>
			<br>
			</table>');

//		$pdf->setFooterData($tc=array(0,64,0), $lc=array(0,64,128));

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(30);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


		// set default font subsetting mode
		$pdf->setFontSubsetting(true);

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('helvetica', '', 11, '', true);

		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage();

		// set text shadow effect
		//$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>0.5, 'blend_mode'=>'Normal'));


$html = '<style>
tr .normal {
	width: 150px;
}
tr .normal2 {
	width:250px;
}
</style>
<table>
		<tr class="normal">
			<td class="normal"></td>
			<td class="normal"></td>
			<td class="short"></td>
			<td class="normal"></td>
		</tr>
		<tr class="normal">
			<td class="normal"><b>'.$s_reference_label.':</b></td>
			<td class="normal">' . $sender_billing_reference . '</td>
			<td class="short"></td>
			<td class="normal2">' . $rec_billing_company_name . '</td>
		</tr>
		<tr class="normal">
			<td class="normal"><b>'.$r_reference_label.':</b></td>
			<td class="normal">' . $exhibitor_name . '</td>
			<td class="short"></td>
			<td class="normal2">' . $rec_billing_address . '</td>
		</tr>
		<tr class="normal">
			<td class="normal"><b>'.$invoice_cancels_label.':</b></td>
			<td class="normal">' . $invoice_id . '</td>
			<td class="short"></td>
			<td class="normal2">' . $rec_billing_zipcode . ' ' . $rec_billing_city . '</td>
		</tr>
		<tr class="normal">
			<td class="normal"><b>'.$invoice_date_label.':</b></td>
			<td class="normal">' . $date . '</td>
			<td class="short"></td>
			<td class="normal2">' . $rec_billing_country . '</td>
		</tr>
</table>


<br /><br /><br />
';
$html .= '<style>
* {
	box-sizing:border-box;
}
.short {
	width: 31px;
}
.id {
	width: 80px;
}
.name {
	width: 300px;
}
.price{
	width: 80px;
	text-align: right;
}
.amount {
	width: 70px;
}
.moms {
	width:50px;
}
.center {
	text-align:center;
}
.left {
	text-align:left;
}
.right {
	text-align:right;
}
.vat {
	width: 80px;
	text-align: left;
}
.dark {
	background-color: #D4D4D4;
}
.totalprice {
	width: 350;
	text-align: right;
	font-size: 20px;
}
.totalprice2 {
	width: 400;
	text-align: right;
	font-size: 20px;
}
.pennys {
	width: 400;
	text-align: right;
	font-size: 16px;
}
</style>

<table>
	<thead>
	    <tr class="dark">
	    	<th class="id">ID</th>
	        <th class="name">'.$description_label.'</th>
	        <th class="price">'.$price_label.'</th>
	        <th class="amount right">'.$amount_label.'</th>
	        <th class="moms right">'.$tax_label.'</th>
	        <th class="price">'.$parttotal_label.'</th>
	    </tr>
    </thead>
    <tbody>';

$html .= '<tr><td></td></tr><tr><td class="id"></td><td class="name"><b>'.$booked_space_label.'</b></td></tr>
<tr>
	<td class="id"></td>
    <td class="name">' . $position_name . '</td>
    <td class="price">' . $position_price . '</td>
	<td class="amount right">1 '.$st_label.'</td>
	<td class="moms right">' . $position_vat . '%</td>
	<td class="price right">' . number_format($position_price, 2, ',', ' ') . '</td>
</tr>
<tr>
	<td class="id"></td>
    <td class="name">' . $position_information . '</td>
    <td class="price"></td>
	<td class="amount right"></td>
	<td class="moms right"></td>
	<td class="price right"></td>
</tr>';

	if ($position_vat == 25) {
		$excludeVatPrice25 += $position_price;
	} else if ($position_vat == 18) {
		$excludeVatPrice18 += $position_price;
	} else {
		$excludeVatPrice0 += $position_price;
	}

if (!empty($invoice_options) && is_array($invoice_options)) {
	$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$options_label.'</b></td></tr>';

	for ($row=0; $row<count($options[1]); $row++) {
	    $html .= '<tr>
	    	<td class="id">' . $options[0][$row] . '</td>
	        <td class="name">' . $options[1][$row] . '</td>
	        <td class="price">' . $options[2][$row] . '</td>
	        <td class="amount right">1 '.$st_label.'</td>
	        <td class="moms right">' . $options[3][$row] . '%</td>
	        <td class="price right">' . str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')) . '</td>
	        </tr>';
    }
}

if (!empty($invoice_articles) && is_array($invoice_articles)) {
	
	$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$articles_label.'</b></td></tr>';
	for ($row=0; $row<count($articles[1]); $row++) {
	    $html .= '<tr>
	    	<td class="id">' . $articles[0][$row] . '</td>
	        <td class="name">' . $articles[1][$row] . '</td>
	        <td class="price">' . str_replace('.', ',', $articles[2][$row]) . '</td>
	        <td class="amount right">' . $articles[3][$row] . ' '.$st_label.'</td>
	        <td class="moms right">' . $articles[4][$row] . '%</td>
	        <td class="price right">' . str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')) . '</td>
	        </tr>';
	        $articles[2][$row] = str_replace(',', '.', $articles[2][$row]);
    }
}


if (!empty($invoice_options) && is_array($invoice_options)) {
	for ($row=0; $row<count($options[1]); $row++) {

		if ($options[3][$row] == 25) {
			$excludeVatPrice25 += $options[2][$row];
		}
		if ($options[3][$row] == 18) {
			$excludeVatPrice18 += $options[2][$row];
		}
		if ($options[3][$row] == 12) {
			$excludeVatPrice12 += $options[2][$row];
		}
		if ($options[3][$row] == 0) {
			$excludeVatPrice0 += $options[2][$row];
		}
	}
}

if (!empty($invoice_articles) && is_array($invoice_articles)) {
	for ($row=0; $row<count($articles[1]); $row++) {

		if ($articles[4][$row] == 25) {
			$excludeVatPrice25 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 18) {
			$excludeVatPrice18 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 12) {
			$excludeVatPrice12 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 0) {
			$excludeVatPrice0 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
	}
}

$VatPrice0 = $excludeVatPrice0;
$VatPrice12 = $excludeVatPrice12*0.12;
$VatPrice18 = $excludeVatPrice18*0.18;
$VatPrice25 = $excludeVatPrice25*0.25;
$totalPrice += $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25 + $VatPrice12 + $VatPrice18 + $VatPrice25 + $VatPrice0;

$totalPriceRounded = round($totalPrice);
$pennys = ($totalPriceRounded - $totalPrice);

$html .= '
	<hr>
	<br />
	<tr>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="totalprice"></td>
	</tr>	
	<tr>
		<td class="vat">'.$net_label.'</td>
		<td class="vat">'.$tax_label.' %</td>
		<td class="vat">'.$tax_label.':</td>
		<td class="totalprice"></td>
	</tr>';

if (!empty($excludeVatPrice0) && !empty($VatPrice0)) {
	$excludeVatPrice0 = number_format($excludeVatPrice0, 2, ',', ' ');
	$VatPrice0 = number_format($VatPrice0, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice0) . '</td>
		<td class="vat">0,00</td>
		<td class="vat">0,00</td>	
		<td class="totalprice"></td>
	</tr>';
}

if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
	$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
	$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice12) . '</td>
		<td class="vat">12,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice12) . '</td>	
		<td class="totalprice"></td>
	</tr>';
}

if (!empty($excludeVatPrice18) && !empty($VatPrice18)) {
	$excludeVatPrice18 = number_format($excludeVatPrice18, 2, ',', ' ');
	$VatPrice18 = number_format($VatPrice18, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice18) . '</td>
		<td class="vat">18,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice18) . '</td>	
		<td class="totalprice"></td>
	</tr>';
}

if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
	$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
	$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice25) . '</td>
		<td class="vat">25,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice25) . '</td>	
		<td class="totalprice"></td>
	</tr>';
}
$html .= '
	<tr>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="pennys">'.$rounding_label.':&nbsp;&nbsp;'
		. str_replace('.', ',', number_format($pennys, 2, ',', ' ')) . 
		'</td>
	</tr>
	<tr>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="totalprice2">'.$currency.' '.$cancelled_label.':&nbsp;&nbsp;'
		. str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')) . 
		'</td>
	</tr>';



$html .= '</tbody></table>';



// Print text using writeHTMLCell()
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->lastPage();

// ---------------------------------------------------------
if (!file_exists(ROOT.'public/invoices/fairs/'.$fairId)) {
	mkdir(ROOT.'public/invoices/fairs/'.$fairId);
	chmod(ROOT.'public/invoices/fairs/'.$fairId, 0775);
	mkdir(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors');
	chmod(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors', 0775);
}

if (!file_exists(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$ex_invoice->get('ex_user'))) {	
	mkdir(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$ex_invoice->get('ex_user'));
	chmod(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$ex_invoice->get('ex_user'), 0775);
}

$exhibitor_company_name = str_replace('/', '-', $exhibitor_company_name);
//Close and output PDF document
$pdf->Output(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$ex_invoice->get('exhibitor').'/'.$exhibitor_company_name . '-' . $position_name . '-' . $cancel_invoice_id . '_cancelled.pdf', 'F');

header('Location: '.BASE_URL.'invoices/fairs/'.$fairId.'/exhibitors/'.$ex_invoice->get('exhibitor').'/'.$exhibitor_company_name . '-' . $position_name . '-' . $cancel_invoice_id . '_cancelled.pdf');
//============================================================+
// END OF FILE
//============================================================+

}


	public function deleteBooking($id = 0, $posId = 0) {
		setAuthLevel(2);

		$status = $_POST['status'];

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
			$pb = new PreliminaryBooking();
			$pb->load($id, 'id');
			
			$u = new User();
			$u->load($pb->get('user'), 'id');
			$stmt_history = $this->db->prepare("INSERT INTO preliminary_booking_history SELECT * FROM preliminary_booking WHERE id = ? AND position = ?");
			$stmt_history->execute(array($id, $posId));
			$pb->delete();

			$mail_type = 'booking';
			$status = 3;

		} else {
			$exhib = new Exhibitor();
			$exhib->load($id, 'id');

			$u = new User();
			$u->load($exhib->get('user'), 'id');
			$stmt_history = $this->db->prepare("INSERT INTO exhibitor_history SELECT * FROM exhibitor WHERE id = ? AND position = ?");
			$stmt_history->execute(array($id, $posId));
			$stmt = $this->db->prepare("DELETE FROM exhibitor WHERE id = ? AND position = ?");
			$stmt->execute(array($id, $posId));

			$position->set('status', 0);
			$position->save();

			if ($status == 'Reservation') {
				$mail_type = 'reservation';
				$status = 1;
			} else {
				$mail_type = 'booking';
				$status = 2;
			}
		}

		$time_now = date('d-m-Y H:i');

		$mailSetting = $mail_type . "Cancelled";

		$comment = $_POST['comment'];
		if ($comment == '') {
			$comment = $this->translate->{'No message was given.'};
		}

		//Check mail settings and send only if setting is set
		if ($fair->wasLoaded()) {
			$mailSettings = json_decode($fair->get("mail_settings"));
			if (is_array($mailSettings->bookingCancelled)) {
				$status = posStatusToText($status);

				if (in_array("1", $mailSettings->bookingCancelled)) {
					$mail_exhibitor = new Mail($u->get('email'), $mail_type . '_cancelled_receipt', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
					$mail_exhibitor->setMailVar('previous_status', $status);
					$mail_exhibitor->setMailVar('position_name', $position->get('name'));
					$mail_exhibitor->setMailVar('exhibitor_name', $u->get('name'));
					$mail_exhibitor->setMailVar('company_name', $u->get('company'));
					$mail_exhibitor->setMailVar('cancelled_name', $current_user->get('name'));
					$mail_exhibitor->setMailVar('event_name', $fair->get('name'));
					$mail_exhibitor->setMailVar('event_email', $fair->get('contact_email'));
					$mail_exhibitor->setMailVar('event_phone', $fair->get('contact_phone'));
					$mail_exhibitor->setMailVar('event_website', $fair->get('website'));
					$mail_exhibitor->setMailVar("url", BASE_URL . $fair->get("url"));
					$mail_exhibitor->setMailVar('edit_time', $time_now);
					$mail_exhibitor->setMailVar('comment', $comment);
					$mail_exhibitor->setMailVar('cancelled_name', $current_user->get('name'));
					$mail_exhibitor->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
					$mail_exhibitor->send();
				}
				if ($current_user->get('email') != $organizer->get('email')) {
					if (in_array("2", $mailSettings->bookingCancelled)) {
						$mail_user = new Mail($current_user->get('email'), $mail_type . '_cancelled_confirm', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
						$mail_user->setMailVar('previous_status', $status);
						$mail_user->setMailVar('position_name', $position->get('name'));
						$mail_user->setMailVar('exhibitor_name', $u->get('name'));
						$mail_user->setMailVar('company_name', $u->get('company'));
						$mail_user->setMailVar('cancelled_name', $current_user->get('name'));
						$mail_user->setMailVar('event_name', $fair->get('name'));
						$mail_user->setMailVar("url", BASE_URL . $fair->get("url"));
						$mail_user->setMailVar('edit_time', $time_now);
						$mail_user->setMailVar('comment', $comment);
						$mail_user->setMailVar('cancelled_name', $current_user->get('name'));
						$mail_user->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
						$mail_user->send();
					}
				}

				if (in_array("0", $mailSettings->bookingCancelled)) {
					$mail_organizer = new Mail($organizer->get('email'), $mail_type . '_cancelled_confirm', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
					$mail_organizer->setMailVar('previous_status', $status);
					$mail_organizer->setMailVar('position_name', $position->get('name'));
					$mail_organizer->setMailVar('exhibitor_name', $u->get('name'));
					$mail_organizer->setMailVar('company_name', $u->get('company'));
					$mail_organizer->setMailVar('cancelled_name', $current_user->get('name'));
					$mail_organizer->setMailVar('event_name', $fair->get('name'));
					$mail_organizer->setMailVar("url", BASE_URL . $fair->get("url"));
					$mail_organizer->setMailVar('edit_time', $time_now);
					$mail_organizer->setMailVar('comment', $comment);
					$mail_organizer->setMailVar('cancelled_name', $current_user->get('name'));
					$mail_organizer->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
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
				$exhibitor->set('arranger_message', $_POST['arranger_message']);
				$exhibitor->set('clone', 0);
				$exId = $exhibitor->save();


				// Remove old categories for this booking
				$stmt = $this->db->prepare("DELETE FROM exhibitor_category_rel WHERE exhibitor = ?");
				$stmt->execute(array($exId));

				// Set new categories for this booking
				$categoryNames = array();

				if (isset($_POST['categories']) && is_array($_POST['categories'])) {
					$stmt = $this->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
					foreach ($_POST['categories'] as $cat) {
						$stmt->execute(array($exId, $cat));
						$category = new ExhibitorCategory();
						$category->load($cat, "id");
						if ($category->wasLoaded()) {
							$categoryNames[] = $category->get("name");
						}
					}
				}

				// Remove old options for this booking
				$stmt = $this->db->prepare("DELETE FROM exhibitor_option_rel WHERE exhibitor = ?");
				$stmt->execute(array($exId));

				// Set new options for this booking
				$options = array();
				if (isset($_POST['options']) && is_array($_POST['options'])) {
					$stmt = $this->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");

					foreach ($_POST['options'] as $opt) {
						$stmt->execute(array($exId, $opt));
						$ex_option = new FairExtraOption();
						$ex_option->load($opt, 'id');
						if ($ex_option->wasLoaded()) {
							$option_id[] = $ex_option->get('custom_id');
							$option_text[] = $ex_option->get('text');
							$option_price[] = $ex_option->get('price');
							$option_vat[] = $ex_option->get('vat');
						}
					}

					$options = array($option_id, $option_text, $option_price, $option_vat);
				}


				// Remove old articles for this booking
				$stmt = $this->db->prepare("DELETE FROM exhibitor_article_rel WHERE exhibitor = ?");
				$stmt->execute(array($exId));

				// Set new articles for this booking
				$articles = array();
				if (isset($_POST['articles']) && is_array($_POST['articles'])) {
					$stmt = $this->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
					$arts = $_POST['articles'];
					$amounts = $_POST['artamount'];

					foreach (array_combine($arts, $amounts) as $art => $amount) {
						$stmt->execute(array($exId, $art, $amount));
						$arts = new FairArticle();
						$arts->load($art, 'id');
						if ($arts->wasLoaded()) {
							$art_id[] = $arts->get('custom_id');
							$art_text[] = $arts->get('text');
							$art_amount[] = $amount;
							$art_price[] = $arts->get('price');
							$art_vat[] = $arts->get('vat');
						}								
					}
					$articles = array($art_id, $art_text, $art_price, $art_amount, $art_vat);
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
					$previous_status = $pos->get('status');
					$pos->set('status', $set_status);
					$stmt = $this->db->prepare("UPDATE exhibitor_invoice SET status = 2 WHERE exhibitor = ?");
					$stmt->execute(array($exId));
					$stmt2 = $this->db->prepare("UPDATE exhibitor SET status = 2 WHERE id = ?");
					$stmt2->execute(array($exId));
				}
				if ($pos->get('status') == 2) {
					$pos->set('expires', '0000-00-00 00:00:00');
				}
				$status = $pos->get('status');			
				$time_now = date('d-m-Y H:i');

				$mailSetting = $mail_type . "Edited";

				$categories = implode('<br/> ', $categoryNames);

				$fairInvoice = new FairInvoice();
				$fairInvoice->load($exhibitor->get('fair'), 'fair');

				$user = new User();
				$user->load($exhibitor->get('user'), 'id');
				$userId = $user->get('id');


/*********************************************************************************/
/*********************************************************************************/
/*****************     SENDER ADDRESS AND PAYMENT OPTIONS        *****************/
/*********************************************************************************/
/*********************************************************************************/


				$sender_billing_reference = $fairInvoice->get('reference');
				$sender_billing_company_name = $fairInvoice->get('company_name');
				$sender_billing_address = $fairInvoice->get('address');
				$sender_billing_zipcode = $fairInvoice->get('zipcode');
				$sender_billing_city = $fairInvoice->get('city');
				$sender_billing_country = $fairInvoice->get('country');
				$sender_billing_orgnr = $fairInvoice->get('orgnr');
				$sender_billing_phone = $fairInvoice->get('phone');
				$sender_billing_email = $fairInvoice->get('email');
				$sender_billing_website = $fairInvoice->get('website');


				$rec_billing_company_name = $user->get('invoice_company');
				$rec_billing_address = $user->get('invoice_address');
				$rec_billing_zipcode = $user->get('invoice_zipcode');
				$rec_billing_city = $user->get('invoice_city');
				$rec_billing_country = $user->get('invoice_country');

				if ($rec_billing_country == 'Sweden')
					$rec_billing_country = 'Sverige';

				if ($rec_billing_country == 'Norway')
					$rec_billing_country = 'Norge';


				$description_label = $this->translate->{'Description'};
				$price_label = $this->translate->{'Price'};
				$amount_label = $this->translate->{'Quantity'};
				$booked_space_label = $this->translate->{'Booked stand'};
				$options_label = $this->translate->{'Options'};
				$articles_label = $this->translate->{'Articles'};
				$tax_label = $this->translate->{'Tax'};
				$parttotal_label = $this->translate->{'Subtotal'};
				$net_label = $this->translate->{'Net'};
				$rounding_label = $this->translate->{'Rounding'};
				$to_pay_label = $this->translate->{'to pay:'};
				$st_label = $this->translate->{'st'};


				$current_user = new User();
				$current_user->load($_SESSION['user_id'], 'id');



/*************************************************************/
/*************************************************************/
/*****************     PRICES AND AMOUNTS        *****************
/*************************************************************/
/*************************************************************/

				$fairId = $fair->get('id');
				$fairname = $fair->get('name');
				$fairurl = $fair->get('url');
				$totalPrice = 0;
				$VatPrice0 = 0;
				$VatPrice12 = 0;
				$VatPrice18 = 0;
				$VatPrice25 = 0;
				$excludeVatPrice0 = 0;
				$excludeVatPrice12 = 0;
				$excludeVatPrice18 = 0;
				$excludeVatPrice25 = 0;
				$position_vat = 0;
				$currency = $fair->get('currency');
				$position_name = $pos->get('name');
				$position_price = $pos->get('price');
				$position_vat = $fairInvoice->get('pos_vat');
				$exhibitor_company_name = $user->get('company');
				$exhibitor_name = $user->get('name');



/*********************************************************************************************/
/*********************************************************************************************/
/*****************    					SET MAIL CONTENT 	  				******************/
/*********************************************************************************************/
/*********************************************************************************************/

$html = '<style>
* {
	box-sizing:border-box;
}
hr {
	width:690px;
	text-align:left;
}
tr .normal {
	width: 150px;
}
tr .normal2 {
	width:250px;
}
tr .normal3 {
	width:160px;
}

.short {
	width: 31px;
}
.id {
	width: 80px;
}
.name {
	width: 300px;
}
.price{
	width: 80px;
	text-align: right;
	padding-right: 12px;
}
.amount {
	width: 100px;
	text-align:center;
}
.moms {
	width:50px;
}
.center {
	text-align:center;
}
.left {
	text-align:left;
}
.right {
	text-align:right;
}
.vat {
	width: 80px;
	text-align: left;
}
.dark {
	background-color: #D4D4D4;
}
.totalprice {
	width: 445;
	text-align: right;
	font-size: 20px;
}
.totalprice2 {
	width: 400;
	text-align: right;
	font-size: 20px;
}
.pennys {
	width: 400;
	text-align: right;
	font-size: 16px;
}
</style>

<table>
	<thead>
	    <tr class="dark">
	    	<th class="id">ID</th>
	        <th class="name">'.$description_label.'</th>
	        <th class="price">'.$price_label.'</th>
	        <th class="amount">'.$amount_label.'</th>
	        <th class="moms right">'.$tax_label.'</th>
	        <th class="price">'.$parttotal_label.'</th>
	    </tr>
    </thead>
    <tbody>';

$html .= '<tr><td></td></tr><tr><td class="id"></td><td class="name"><b>'.$booked_space_label.'</b></td></tr>
<tr>
	<td class="id"></td>
    <td class="name">' . $position_name . '</td>
    <td class="price">' . $position_price . '</td>
	<td class="amount">1 '.$st_label.'</td>
	<td class="moms right">' . $position_vat . '%</td>
	<td class="price right">' . number_format($position_price, 2, ',', ' ') . '</td>
</tr>';

	if ($position_vat == 25) {
		$excludeVatPrice25 += $position_price;
	} else if ($position_vat == 18) {
		$excludeVatPrice18 += $position_price;
	} else {
		$excludeVatPrice0 += $position_price;
	}

if (!empty($_POST['options']) && is_array($_POST['options'])) {
	$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$options_label.'</b></td></tr>';

	for ($row=0; $row<count($options[1]); $row++) {
	    $html .= '<tr>
	    	<td class="id">' . $options[0][$row] . '</td>
	        <td class="name">' . $options[1][$row] . '</td>
	        <td class="price">' . $options[2][$row] . '</td>
	        <td class="amount">1 '.$st_label.'</td>
	        <td class="moms right">' . $options[3][$row] . '%</td>
	        <td class="price right">' . str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')) . '</td>
	        </tr>';
    }
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	
	$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$articles_label.'</b></td></tr>';
	for ($row=0; $row<count($articles[1]); $row++) {
	    $html .= '<tr>
	    	<td class="id">' . $articles[0][$row] . '</td>
	        <td class="name">' . $articles[1][$row] . '</td>
	        <td class="price">' . str_replace('.', ',', $articles[2][$row]) . '</td>
	        <td class="amount center">' . $articles[3][$row] . ' '.$st_label.'</td>
	        <td class="moms right">' . $articles[4][$row] . '%</td>
	        <td class="price right">' . str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')) . '</td>
	        </tr>';
	        $articles[2][$row] = str_replace(',', '.', $articles[2][$row]);
    }
}


if (!empty($_POST['options']) && is_array($_POST['options'])) {
	for ($row=0; $row<count($options[1]); $row++) {

		if ($options[3][$row] == 25) {
			$excludeVatPrice25 += $options[2][$row];
		}
		if ($options[3][$row] == 18) {
			$excludeVatPrice18 += $options[2][$row];
		}
		if ($options[3][$row] == 12) {
			$excludeVatPrice12 += $options[2][$row];
		}
		if ($options[3][$row] == 0) {
			$excludeVatPrice0 += $options[2][$row];
		}
	}
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	for ($row=0; $row<count($articles[1]); $row++) {

		if ($articles[4][$row] == 25) {
			$excludeVatPrice25 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 18) {
			$excludeVatPrice18 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 12) {
			$excludeVatPrice12 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 0) {
			$excludeVatPrice0 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}		
	}
}

$VatPrice0 = $excludeVatPrice0;
$VatPrice12 = $excludeVatPrice12*0.12;
$VatPrice18 = $excludeVatPrice18*0.18;
$VatPrice25 = $excludeVatPrice25*0.25;
$totalPrice += $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25 + $VatPrice12 + $VatPrice18 + $VatPrice25 + $VatPrice0;

$totalPriceRounded = round($totalPrice);
$pennys = ($totalPriceRounded - $totalPrice);

$html .= '
	<hr>
	<br />
	<tr>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="totalprice"></td>
	</tr>	
	<tr>
		<td class="vat">'.$net_label.'</td>
		<td class="vat">'.$tax_label.' %</td>
		<td class="vat">'.$tax_label.':</td>
		<td class="totalprice"></td>
	</tr>';

if (!empty($excludeVatPrice0) && !empty($VatPrice0)) {
	$excludeVatPrice0 = number_format($excludeVatPrice0, 2, ',', ' ');
	$VatPrice0 = number_format($VatPrice0, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice0) . '</td>
		<td class="vat">0,00</td>
		<td class="vat">0,00</td>
		<td class="totalprice"></td>
	</tr>';
}

if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
	$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
	$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice12) . '</td>
		<td class="vat">12,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice12) . '</td>	
		<td class="totalprice"></td>
	</tr>';
}

if (!empty($excludeVatPrice18) && !empty($VatPrice18)) {
	$excludeVatPrice18 = number_format($excludeVatPrice18, 2, ',', ' ');
	$VatPrice18 = number_format($VatPrice18, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice18) . '</td>
		<td class="vat">18,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice18) . '</td>	
		<td class="totalprice"></td>
	</tr>';
}

if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
	$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
	$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice25) . '</td>
		<td class="vat">25,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice25) . '</td>	
		<td class="totalprice"></td>
	</tr>';
}
$html .= '
	<tr>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="pennys">'.$rounding_label.':&nbsp;&nbsp;'
		. str_replace('.', ',', number_format($pennys, 2, ',', ' ')) . 
		'</td>
	</tr>
	<tr>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="totalprice2">'.$currency.' '.$to_pay_label.'&nbsp;&nbsp;'
		. str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')) . 
		'</td>
	</tr>';



$html .= '</tbody></table>';

			$arranger_message = $_POST['arranger_message'];
			if ($arranger_message == '') {
				$arranger_message = $this->translate->{'No message was given.'};
			}
			$exhibitor_commodity = $_POST['commodity'];
			if ($exhibitor_commodity == '') {
				$exhibitor_commodity = $this->translate->{'No commodity was entered.'};
			}

				//Check mail settings and send only if setting is set
				if ($fair->wasLoaded()) {
					$mailSettings = json_decode($fair->get("mail_settings"));
					if (is_array($mailSettings->$mailSetting)) {
						if (isset($previous_status)) {
							$previous_status = posStatusToText($previous_status);
						}

						$status = posStatusToText($status);

						if (in_array("0", $mailSettings->$mailSetting)) {
							$mail_organizer = new Mail($organizer->get('email'), $mail_type . '_' . (isset($previous_status) ? 'approved' : 'edited') . '_confirm', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
							$mail_organizer->setMailVar('booking_table', $html);
							$mail_organizer->setMailVar('status', $status);
							$mail_organizer->setMailVar('event_name', $fair->get('name'));
							$mail_organizer->setMailvar('exhibitor_name', $exhibitor->get('name'));
							$mail_organizer->setMailVar('company_name', $exhibitor->get('company'));
							$mail_organizer->setMailVar('position_name', $pos->get('name'));
							$mail_organizer->setMailVar('position_information', $pos->get('information'));
							$mail_organizer->setMailVar('position_area', $pos->get('area'));
							$mail_organizer->setMailVar("booking_time", date('d-m-Y H:i:s', intval($exhibitor->get("booking_time"))));
							$mail_organizer->setMailVar('url', BASE_URL . $fair->get('url'));
							$mail_organizer->setMailVar('arranger_message', $arranger_message);
							$mail_organizer->setMailVar('exhibitor_commodity', $exhibitor_commodity);
							$mail_organizer->setMailVar('exhibitor_category', $categories);
							$mail_organizer->setMailVar('exhibitor_options', $options);
							$mail_organizer->setMailVar('edit_time', $time_now);

							if ($mail_type == 'reservation') {
								$mail_organizer->setMailVar('previous_status', $previous_status);
								$mail_organizer->setMailVar('date_expires', $_POST['expires']);
							}

							$mail_organizer->send();
						}

						if (in_array("1", $mailSettings->$mailSetting)) {
							$mail_user = new Mail($exhibitor->get('email'), $mail_type . '_' . (isset($previous_status) ? 'approved' : 'edited') . '_receipt', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
							$mail_user->setMailVar('status', $status);
							$mail_user->setMailVar('event_name', $fair->get('name'));
							$mail_user->setMailVar('event_email', $fair->get('contact_email'));
							$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
							$mail_user->setMailVar('event_website', $fair->get('website'));
							$mail_user->setMailvar('exhibitor_name', $exhibitor->get('name'));
							$mail_user->setMailVar('company_name', $exhibitor->get('company'));
							$mail_user->setMailVar('position_name', $pos->get('name'));
							$mail_user->setMailVar('position_information', $pos->get('information'));
							$mail_user->setMailVar('position_area', $pos->get('area'));
							$mail_user->setMailVar("booking_time", date('d-m-Y H:i:s', intval($exhibitor->get("booking_time"))));
							$mail_user->setMailVar('url', BASE_URL . $fair->get('url'));
							$mail_user->setMailVar('arranger_message', $arranger_message);
							$mail_user->setMailVar('exhibitor_commodity', $exhibitor_commodity);
							$mail_user->setMailVar('exhibitor_category', $categories);
							$mail_user->setMailVar('exhibitor_options', $options);
							$mail_user->setMailVar('edit_time', $time_now);							

							if ($mail_type == 'reservation') {
								$mail_user->setMailVar('previous_status', $previous_status);
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
	public function reviewPrelBooking() {
		setAuthLevel(2);
		}


public function reservePrelBooking() {
		setAuthLevel(2);

			$pb = new PreliminaryBooking();
			$pb->load($_POST['id'], 'id');

			if ($pb->wasLoaded()) {
				$pos = new FairMapPosition();
				$pos->load($pb->get('position'), 'id');
				$booking_time = $pb->get('booking_time');

				$previous_status = 3;
				$status = 1;
				$pos->set('status', $status);
				$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));

				$ex = new Exhibitor();
				$ex->set('user', $pb->get('user'));
				$ex->set('fair', $pb->get('fair'));
				$ex->set('position', $pb->get('position'));
				$ex->set('commodity', $_POST['commodity']);
				$ex->set('arranger_message', $_POST['arranger_message']);
				$ex->set('edit_time', time());
				$ex->set('clone', 0);
				$ex->set('status', 1);
				
				$exId = $ex->save();
				$pos->save();
				$pb->delete();



				$categoryNames = array();

				if (isset($_POST['categories']) && is_array($_POST['categories'])) {
					$stmt = $pos->db->prepare("INSERT INTO `exhibitor_category_rel` (`exhibitor`, `category`) VALUES (?, ?)");
					foreach ($_POST['categories'] as $cat) {
						$stmt->execute(array($exId, $cat));
						$category = new ExhibitorCategory();
						$category->load($cat, "id");
						if ($category->wasLoaded()) {
							$categoryNames[] = $category->get("name");
						}
					}
				}


				$options = array();
				if (isset($_POST['options']) && is_array($_POST['options'])) {
					$stmt = $pos->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");

					foreach ($_POST['options'] as $opt) {								
						$stmt->execute(array($exId, $opt));
						$ex_option = new FairExtraOption();
						$ex_option->load($opt, 'id');
						if ($ex_option->wasLoaded()) {
							$option_id[] = $ex_option->get('custom_id');
							$option_text[] = $ex_option->get('text');
							$option_price[] = $ex_option->get('price');
							$option_vat[] = $ex_option->get('vat');
						}
					}

					$options = array($option_id, $option_text, $option_price, $option_vat);
				}


				$articles = array();
				if (isset($_POST['articles']) && is_array($_POST['articles'])) {
					$stmt = $pos->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
					$arts = $_POST['articles'];
					$amounts = $_POST['artamount'];

					foreach (array_combine($arts, $amounts) as $art => $amount) {
						$stmt->execute(array($exId, $art, $amount));
						$arts = new FairArticle();
						$arts->load($art, 'id');
						if ($arts->wasLoaded()) {
							$art_id[] = $arts->get('custom_id');
							$art_text[] = $arts->get('text');
							$art_amount[] = $amount;
							$art_price[] = $arts->get('price');
							$art_vat[] = $arts->get('vat');
						}								
					}
					$articles = array($art_id, $art_text, $art_price, $art_amount, $art_vat);
				}

				$categories = implode('<br/> ', $categoryNames);

				$time_now = date('d-m-Y H:i');
				
				$fair = new Fair();
				$fair->load($pb->get('fair'), 'id');
				
				$organizer = new User();
				$organizer->load($fair->get('created_by'), 'id');

				$user = new User();
				$user->load($ex->get('user'), 'id');

				$fairInvoice = new FairInvoice();
				$fairInvoice->load($pb->get('fair'), 'fair');

/*********************************************************************************/
/*********************************************************************************/
/*****************     SENDER ADDRESS AND PAYMENT OPTIONS        *****************/
/*********************************************************************************/
/*********************************************************************************/


				$sender_billing_reference = $fairInvoice->get('reference');
				$sender_billing_company_name = $fairInvoice->get('company_name');
				$sender_billing_address = $fairInvoice->get('address');
				$sender_billing_zipcode = $fairInvoice->get('zipcode');
				$sender_billing_city = $fairInvoice->get('city');
				$sender_billing_country = $fairInvoice->get('country');
				$sender_billing_orgnr = $fairInvoice->get('orgnr');
				$sender_billing_phone = $fairInvoice->get('phone');
				$sender_billing_email = $fairInvoice->get('email');
				$sender_billing_website = $fairInvoice->get('website');


				$rec_billing_company_name = $user->get('invoice_company');
				$rec_billing_address = $user->get('invoice_address');
				$rec_billing_zipcode = $user->get('invoice_zipcode');
				$rec_billing_city = $user->get('invoice_city');
				$rec_billing_country = $user->get('invoice_country');

				if ($rec_billing_country == 'Sweden')
					$rec_billing_country = 'Sverige';

				if ($rec_billing_country == 'Norway')
					$rec_billing_country = 'Norge';


				$description_label = $this->translate->{'Description'};
				$price_label = $this->translate->{'Price'};
				$amount_label = $this->translate->{'Quantity'};
				$booked_space_label = $this->translate->{'Booked stand'};
				$options_label = $this->translate->{'Options'};
				$articles_label = $this->translate->{'Articles'};
				$tax_label = $this->translate->{'Tax'};
				$parttotal_label = $this->translate->{'Subtotal'};
				$net_label = $this->translate->{'Net'};
				$rounding_label = $this->translate->{'Rounding'};
				$to_pay_label = $this->translate->{'to pay:'};
				$st_label = $this->translate->{'st'};


				$current_user = new User();
				$current_user->load($_SESSION['user_id'], 'id');



/*************************************************************/
/*************************************************************/
/*****************     PRICES AND AMOUNTS        *****************
/*************************************************************/
/*************************************************************/

				$fairId = $fair->get('id');
				$fairname = $fair->get('name');
				$fairurl = $fair->get('url');
				$totalPrice = 0;
				$VatPrice0 = 0;
				$VatPrice12 = 0;
				$VatPrice18 = 0;
				$VatPrice25 = 0;
				$excludeVatPrice0 = 0;
				$excludeVatPrice12 = 0;
				$excludeVatPrice18 = 0;
				$excludeVatPrice25 = 0;
				$position_vat = 0;
				$currency = $fair->get('currency');
				$position_name = $pos->get('name');
				$position_price = $pos->get('price');
				$position_vat = $fairInvoice->get('pos_vat');
				$exhibitor_company_name = $user->get('company');
				$exhibitor_name = $user->get('name');



/*********************************************************************************************/
/*********************************************************************************************/
/*****************    					SET MAIL CONTENT 	  				******************/
/*********************************************************************************************/
/*********************************************************************************************/

$html = '<style>
* {
	box-sizing:border-box;
}
hr {
	width:690px;
	text-align:left;
}
tr .normal {
	width: 150px;
}
tr .normal2 {
	width:250px;
}
tr .normal3 {
	width:160px;
}

.short {
	width: 31px;
}
.id {
	width: 80px;
}
.name {
	width: 300px;
}
.price{
	width: 80px;
	text-align: right;
	padding-right: 12px;
}
.amount {
	width: 100px;
	text-align:center;
}
.moms {
	width:50px;
}
.center {
	text-align:center;
}
.left {
	text-align:left;
}
.right {
	text-align:right;
}
.vat {
	width: 80px;
	text-align: left;
}
.dark {
	background-color: #D4D4D4;
}
.totalprice {
	width: 445;
	text-align: right;
	font-size: 20px;
}
.totalprice2 {
	width: 400;
	text-align: right;
	font-size: 20px;
}
.pennys {
	width: 400;
	text-align: right;
	font-size: 16px;
}
</style>

<table>
	<thead>
	    <tr class="dark">
	    	<th class="id">ID</th>
	        <th class="name">'.$description_label.'</th>
	        <th class="price">'.$price_label.'</th>
	        <th class="amount">'.$amount_label.'</th>
	        <th class="moms right">'.$tax_label.'</th>
	        <th class="price">'.$parttotal_label.'</th>
	    </tr>
    </thead>
    <tbody>';

$html .= '<tr><td></td></tr><tr><td class="id"></td><td class="name"><b>'.$booked_space_label.'</b></td></tr>
<tr>
	<td class="id"></td>
    <td class="name">' . $position_name . '</td>
    <td class="price">' . $position_price . '</td>
	<td class="amount">1 '.$st_label.'</td>
	<td class="moms right">' . $position_vat . '%</td>
	<td class="price right">' . number_format($position_price, 2, ',', ' ') . '</td>
</tr>';

	if ($position_vat == 25) {
		$excludeVatPrice25 += $position_price;
	} else if ($position_vat == 18) {
		$excludeVatPrice18 += $position_price;
	} else {
		$excludeVatPrice0 += $position_price;
	}

if (!empty($_POST['options']) && is_array($_POST['options'])) {
	$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$options_label.'</b></td></tr>';

	for ($row=0; $row<count($options[1]); $row++) {
	    $html .= '<tr>
	    	<td class="id">' . $options[0][$row] . '</td>
	        <td class="name">' . $options[1][$row] . '</td>
	        <td class="price">' . $options[2][$row] . '</td>
	        <td class="amount">1 '.$st_label.'</td>
	        <td class="moms right">' . $options[3][$row] . '%</td>
	        <td class="price right">' . str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')) . '</td>
	        </tr>';
    }
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	
	$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$articles_label.'</b></td></tr>';
	for ($row=0; $row<count($articles[1]); $row++) {
	    $html .= '<tr>
	    	<td class="id">' . $articles[0][$row] . '</td>
	        <td class="name">' . $articles[1][$row] . '</td>
	        <td class="price">' . str_replace('.', ',', $articles[2][$row]) . '</td>
	        <td class="amount center">' . $articles[3][$row] . ' '.$st_label.'</td>
	        <td class="moms right">' . $articles[4][$row] . '%</td>
	        <td class="price right">' . str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')) . '</td>
	        </tr>';
	        $articles[2][$row] = str_replace(',', '.', $articles[2][$row]);
    }
}


if (!empty($_POST['options']) && is_array($_POST['options'])) {
	for ($row=0; $row<count($options[1]); $row++) {

		if ($options[3][$row] == 25) {
			$excludeVatPrice25 += $options[2][$row];
		}
		if ($options[3][$row] == 18) {
			$excludeVatPrice18 += $options[2][$row];
		}
		if ($options[3][$row] == 12) {
			$excludeVatPrice12 += $options[2][$row];
		}
		if ($options[3][$row] == 0) {
			$excludeVatPrice0 += $options[2][$row];
		}
	}
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	for ($row=0; $row<count($articles[1]); $row++) {

		if ($articles[4][$row] == 25) {
			$excludeVatPrice25 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 18) {
			$excludeVatPrice18 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 12) {
			$excludeVatPrice12 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 0) {
			$excludeVatPrice0 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}		
	}
}

$VatPrice0 = $excludeVatPrice0;
$VatPrice12 = $excludeVatPrice12*0.12;
$VatPrice18 = $excludeVatPrice18*0.18;
$VatPrice25 = $excludeVatPrice25*0.25;
$totalPrice += $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25 + $VatPrice12 + $VatPrice18 + $VatPrice25 + $VatPrice0;

$totalPriceRounded = round($totalPrice);
$pennys = ($totalPriceRounded - $totalPrice);

$html .= '
	<hr>
	<br />
	<tr>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="totalprice"></td>
	</tr>	
	<tr>
		<td class="vat">'.$net_label.'</td>
		<td class="vat">'.$tax_label.' %</td>
		<td class="vat">'.$tax_label.':</td>
		<td class="totalprice"></td>
	</tr>';

if (!empty($excludeVatPrice0) && !empty($VatPrice0)) {
	$excludeVatPrice0 = number_format($excludeVatPrice0, 2, ',', ' ');
	$VatPrice0 = number_format($VatPrice0, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice0) . '</td>
		<td class="vat">0,00</td>
		<td class="vat">0,00</td>
		<td class="totalprice"></td>
	</tr>';
}

if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
	$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
	$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice12) . '</td>
		<td class="vat">12,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice12) . '</td>	
		<td class="totalprice"></td>
	</tr>';
}

if (!empty($excludeVatPrice18) && !empty($VatPrice18)) {
	$excludeVatPrice18 = number_format($excludeVatPrice18, 2, ',', ' ');
	$VatPrice18 = number_format($VatPrice18, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice18) . '</td>
		<td class="vat">18,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice18) . '</td>	
		<td class="totalprice"></td>
	</tr>';
}

if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
	$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
	$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice25) . '</td>
		<td class="vat">25,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice25) . '</td>	
		<td class="totalprice"></td>
	</tr>';
}
$html .= '
	<tr>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="pennys">'.$rounding_label.':&nbsp;&nbsp;'
		. str_replace('.', ',', number_format($pennys, 2, ',', ' ')) . 
		'</td>
	</tr>
	<tr>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="vat"></td>
		<td class="totalprice2">'.$currency.' '.$to_pay_label.'&nbsp;&nbsp;'
		. str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')) . 
		'</td>
	</tr>';



$html .= '</tbody></table>';

$arranger_message = $_POST['arranger_message'];
if ($arranger_message == '') {
	$arranger_message = $this->translate->{'No message was given.'};
}
$exhibitor_commodity = $_POST['commodity'];
if ($exhibitor_commodity == '') {
	$exhibitor_commodity = $this->translate->{'No commodity was entered.'};
}


				//Check mail settings and send only if setting is set
				if ($fair->wasLoaded()) {
					$mailSettings = json_decode($fair->get("mail_settings"));
					if (is_array($mailSettings->acceptPreliminaryBooking)) {
						$previous_status = posStatusToText($previous_status);
						$status = posStatusToText($status);

						if (in_array("0", $mailSettings->acceptPreliminaryBooking)) {
							$mail_organizer = new Mail($organizer->get('email'), 'reservation_approved_confirm', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
							$mail_organizer->setMailVar('previous_status', $previous_status);
							$mail_organizer->setMailVar('booking_table', $html);
							$mail_organizer->setMailVar('status', $status);
							$mail_organizer->setMailVar('date_expires', $_POST['expires']);
							$mail_organizer->setMailVar('company_name', $user->get('company'));
							$mail_organizer->setMailvar("exhibitor_name", $user->get("name"));
							$mail_organizer->setMailvar("event_name", $fair->get("name"));
							$mail_organizer->setMailVar('position_name', $pos->get('name'));
							$mail_organizer->setMailVar("booking_time", date('d-m-Y H:i:s', intval($booking_time)));
							$mail_organizer->setMailVar("url", BASE_URL . $fair->get("url"));
							$mail_organizer->setMailVar('position_information', $pos->get('information'));
							$mail_organizer->setMailVar('position_area', $pos->get('area'));
							$mail_organizer->setMailVar('arranger_message', $arranger_message);
							$mail_organizer->setMailVar('exhibitor_commodity', $exhibitor_commodity);
							$mail_organizer->setMailVar('exhibitor_category', $categories);
							$mail_organizer->setMailVar('exhibitor_options', $options);
							$mail_organizer->setMailVar('edit_time', $time_now);
							$mail_organizer->send();
						}
						if (in_array("1", $mailSettings->acceptPreliminaryBooking)) {
							$mail_user = new Mail($user->get('email'), 'reservation_approved_receipt', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
							$mail_user->setMailVar('previous_status', $previous_status);
							$mail_user->setMailVar('booking_table', $html);
							$mail_user->setMailVar('status', $status);
							$mail_user->setMailVar('date_expires', $_POST['expires']);
							$mail_user->setMailVar('company_name', $user->get('company'));
							$mail_user->setMailvar("exhibitor_name", $user->get("name"));
							$mail_user->setMailvar("event_name", $fair->get("name"));
							$mail_user->setMailVar('event_email', $fair->get('contact_email'));
							$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
							$mail_user->setMailVar('event_website', $fair->get('website'));
							$mail_user->setMailVar('position_name', $pos->get('name'));
							$mail_user->setMailVar("booking_time", date('d-m-Y H:i:s', intval($booking_time)));
							$mail_user->setMailVar("url", BASE_URL . $fair->get("url"));
							$mail_user->setMailVar('position_information', $pos->get('information'));
							$mail_user->setMailVar('position_area', $pos->get('area'));
							$mail_user->setMailVar('arranger_message', $arranger_message);
							$mail_user->setMailVar('exhibitor_commodity', $exhibitor_commodity);
							$mail_user->setMailVar('exhibitor_category', $categories);
							$mail_user->setMailVar('exhibitor_options', $options);
							$mail_user->setMailVar('edit_time', $time_now);
							$mail_user->send();
				}
			}
		}
	}

		header('Location: '.BASE_URL.'administrator/newReservations');
		exit;
	}

}

?>
