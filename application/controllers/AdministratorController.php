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
					$user = new User();
					$user->load($result['user'], 'id');
					if($user->get('level') == 2) {

						$stmt = $this->Administrator->db->prepare("SELECT COUNT(*) AS pos_count FROM fair_map_position WHERE created_by = ?");
						$stmt->execute(array($user->get('id')));
						$res = $stmt->fetch(PDO::FETCH_ASSOC);
						$user->set('spots_created', $res['pos_count']);
						$users[] = $user;

					}
				}
			}
			$this->setNoTranslate('users', $users);
		}

	}

	public function sendInvoices() {

		setAuthLevel(2);
		$this->setNoTranslate('noView', true);

		$fair = new Fair;
		$fair->loadsimple($_SESSION['user_fair'], 'id');

		if (isset($_POST['invoice_id'])) {
			if (isset($_POST['msg']) && $_POST['msg'] !== '')
				$comment = htmlspecialchars_decode($_POST['msg']);
			else
				$comment = $this->translate->{'No message was given.'};

			$stmt = $this->Administrator->db->prepare("SELECT 
				ex_invoice.r_name AS r_name, 
				ex_invoice.r_reference AS r_reference, 
				ex_invoice.exhibitor AS exhibitor, 
				ex_invoice.row_id AS row_id, 
				ex_invoice.fair AS fair, 
				ex_invoice.id AS id, 
				user.invoice_email AS invoice_email, 
				pos.text AS posname
				FROM user, exhibitor_invoice AS ex_invoice, exhibitor_invoice_rel AS pos
				WHERE ex_invoice.ex_user = user.id
				AND ex_invoice.fair = pos.fair
				AND ex_invoice.id = pos.invoice
				AND pos.type = 'space'
				AND ex_invoice.row_id = ?");
			$stmt->execute(array($_POST['invoice_id']));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$replace_chars = array(
			'/' => '-',
			':' => '_'
			);
			foreach ($result as $res) {
				$r_name = strtr($res['r_name'], $replace_chars);
				$posname = strtr($res['posname'], $replace_chars);
				$invoice_file = ROOT.'public/invoices/fairs/'.$res['fair'].'/exhibitors/'.$res['exhibitor'].'/' . $r_name . '-' . $posname . '-' . $res['id'] . '.pdf';
				/* Prepare to send the mail */
				if ($fair->get('contact_name') == '')
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('windowtitle'));
				else
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('contact_name'));
				$recipient = array($res['invoice_email'], $res['r_reference']);
				/* UPDATED TO FIT MAILJET */
				$mail_user = new Mail();
				$mail_user->setFrom($from);
				$mail_user->setServerTemplate('send_invoice');
				$mail_user->setRecipient($recipient);
				$mail_user->setAttachment($invoice_file);
				$mail_user->setFilename($this->translate->{'Invoice no '}.$res['id'].'.pdf');
				/* Setting mail variables */
				$mail_user->setMailVar('exhibitor_company', $res['r_name']);
				$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
				$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
				$mail_user->setMailVar('event_email', $fair->get('contact_email'));
				$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
				$mail_user->setMailVar('event_website', $fair->get('website'));
				$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
				$mail_user->setMailVar('invoice_name', basename($invoice_file));
				$mail_user->setMailVar('position_name', $res['posname']);
				$mail_user->setMailVar('comment', $comment);
				$mail_user->sendMessage();

				if(!file_exists($invoice_file))
					throw new Exception($this->translate->{'Could not attatch file to email for invoice no.	'}.$res['id'].'.');

				if(!is_readable($invoice_file))
					throw new Exception($this->translate->{'Could not open and read attatched file for invoice no. '}.$res['id'].'.');
				$this->markAsSent($res['exhibitor'], $res['row_id']);
			}
		}
	}

	function setRecurring() {
		setAuthLevel(2);
		$this->setNoTranslate('noView', true);

		$fair = new Fair();
		$fair->loadsimple($_SESSION['user_fair'], 'id');

		if (userLevel() == 3) {
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
				$stmt = $this->db->prepare("UPDATE exhibitor AS ex SET ex.recurring = 1 WHERE ex.fair = ? AND ex.id IN (" . implode(',', $_POST['exid']) . ")");
				$stmt->execute(array($_SESSION['user_fair']));
		}
	}
	
	function unsetRecurring() {
		setAuthLevel(2);
		$this->setNoTranslate('noView', true);

		$fair = new Fair();
		$fair->loadsimple($_SESSION['user_fair'], 'id');

		if (userLevel() == 3) {
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
				$stmt = $this->db->prepare("UPDATE exhibitor AS ex SET ex.recurring = 0 WHERE ex.fair = ? AND ex.id IN (" . implode(',', $_POST['exid']) . ")");
				$stmt->execute(array($_SESSION['user_fair']));
		}
	}
	function mailVerifyCloned() {

		setAuthLevel(2);
		$this->setNoTranslate('noView', true);

		$fair = new Fair();
		$fair->loadsimple($_SESSION['user_fair'], 'id');

		if (userLevel() == 3) {
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
				user.contact_email AS contact_email, 
				user.email AS email, 
				user.alias AS alias, 
				user.company AS company,
				pos.name AS posname, 
				pos.area AS posarea, 
				pos.id AS posid,
				ex.id AS id 
					FROM user, 
					exhibitor AS ex, 
					fair_map_position AS pos 
						WHERE user.id = ex.user 
						AND ex.position = pos.id 
						AND ex.fair = ? 
						AND pos.status = 1 
						AND ex.clone = 1 
						AND ex.id IN (" . implode(',', $_POST['exid']) . ")");
			$stmt->execute(array($_SESSION['user_fair']));
			$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ($data_rows as $row) {
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

				/* Preparing to send the mail */
				if ($fair->get('contact_name') == '')
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('windowtitle'));
				else
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('contact_name'));
				if ($row['contact_email'] == '')
				$recipient = array($row['email'], $row['company']);
				else
				$recipient = array($row['contact_email'], $row['company']);
				/* UPDATED TO FIT MAILJET */
				$mail_user = new Mail();
				$mail_user->setTemplate('confirm_cloned');
				$mail_user->setFrom($from);
				$mail_user->setRecipient($recipient);
				/* Setting mail variables */
				$mail_user->setMailVar('exhibitor_company', $row['company']);
				$mail_user->setMailVar('position_name', $row['posname']);
				if ($row['posarea'] !== '')
				$mail_user->setMailVar('position_area', $row['posarea']);
				$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
				$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
				$mail_user->setMailVar('event_email', $fair->get('contact_email'));
				$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
				$mail_user->setMailVar('event_website', $fair->get('website'));
				$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
				$mail_user->setMailVar('accepturl', $accepturl);
				$mail_user->setMailVar('denyurl', $denyurl);
				$mail_user->sendMessage();
			}
		}
	}

	public function exportNewReservations($tbl){
		setAuthLevel(2);
		$this->setNoTranslate('noView', true);

		if (isset($_POST['rows'], $_POST['field']) && is_array($_POST['rows']) && is_array($_POST['field'])) {

			/* Samla relevant information till en array
			beroende på vilken tabell som är vald */

			if ($tbl == 1) {
				$stmt = $this->Administrator->db->prepare("SELECT ex.*, user.id as userid, user.*, pos.name AS position, pos.area, pos.information, ex.id AS id FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ? AND ex.id IN (" . implode(',', $_POST['rows']) . ") ORDER BY CAST(pos.name AS UNSIGNED), pos.name");
				$stmt->execute(array($_SESSION['user_fair'], 2));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 2) {
				$stmt = $this->Administrator->db->prepare("SELECT ex.*, user.id as userid, user.*, pos.name AS position, pos.area, pos.information, pos.expires, ex.id AS id FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ? AND ex.clone = 0 AND ex.id IN (" . implode(',', $_POST['rows']) . ") ORDER BY CAST(pos.name AS UNSIGNED), pos.name");
				$stmt->execute(array($_SESSION['user_fair'], 1));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 3) {
				$stmt = $this->Administrator->db->prepare("SELECT prel.*, user.id as userid, user.*, pos.area, pos.information, pos.name AS position, prel.id AS id FROM user, preliminary_booking AS prel, fair_map_position AS pos WHERE prel.fair = ? AND pos.id = prel.position AND user.id = prel.user AND prel.id IN (" . implode(',', $_POST['rows']) . ") ORDER BY CAST(pos.name AS UNSIGNED), pos.name");
				$stmt->execute(array($_SESSION['user_fair']));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
				
			} else if ($tbl == 4) {
				$stmt = $this->Administrator->db->prepare("SELECT prel.*, user.id as userid, user.*, pos.area, pos.information, pos.name AS position, prel.id AS id FROM user, preliminary_booking AS prel, fair_map_position AS pos WHERE prel.fair = ? AND pos.id = prel.position AND user.id = prel.user AND prel.id IN (" . implode(',', $_POST['rows']) . ") ORDER BY CAST(pos.name AS UNSIGNED), pos.name");
				$stmt->execute(array($_SESSION['user_fair']));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 5) {
				$stmt = $this->Administrator->db->prepare("SELECT fr.*, u.id AS userid, u.* FROM fair_registration AS fr LEFT JOIN user AS u ON u.id = fr.user WHERE fr.fair = ? AND fr.id IN (" . implode(',', $_POST['rows']) . ")");
				$stmt->execute(array($_SESSION['user_fair']));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			} else if ($tbl == 6) {
				$stmt = $this->Administrator->db->prepare("SELECT prel.*, user.id as userid, user.*, pos.area, pos.information, pos.name AS position, prel.id AS id FROM user, preliminary_booking_history AS prel, fair_map_position AS pos WHERE prel.fair = ? AND pos.id = prel.position AND user.id = prel.user AND prel.id IN (" . implode(',', $_POST['rows']) . ") ORDER BY CAST(pos.name AS UNSIGNED), pos.name");
				$stmt->execute(array($_SESSION['user_fair']));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 7) {
				$stmt = $this->Administrator->db->prepare("SELECT ex.*, user.id as userid, user.*, pos.name AS position, pos.area, pos.information, ex.id AS id FROM user, exhibitor_history AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND ex.id IN (" . implode(',', $_POST['rows']) . ") ORDER BY CAST(pos.name AS UNSIGNED), pos.name");
				$stmt->execute(array($_SESSION['user_fair']));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 8) {
				$stmt = $this->Administrator->db->prepare("SELECT ex.*, user.id as userid, user.*, pos.name AS position, pos.area, pos.information, pos.expires, ex.id AS id FROM user, exhibitor AS ex, fair_map_position AS pos WHERE user.id = ex.user AND ex.position = pos.id AND ex.fair = ? AND pos.status = ? AND ex.clone = 1 AND ex.id IN (" . implode(',', $_POST['rows']) . ") ORDER BY CAST(pos.name AS UNSIGNED), pos.name");
				$stmt->execute(array($_SESSION['user_fair'], 1));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 9) {
				$stmt = $this->Administrator->db->prepare("SELECT frh.*, u.id AS userid, u.* FROM fair_registration_history AS frh LEFT JOIN user AS u ON u.id = frh.user WHERE frh.fair = ? AND frh.id IN (" . implode(',', $_POST['rows']) . ")");
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
				$stmt_articles = $this->db->prepare("SELECT * FROM exhibitor_article_rel AS ear LEFT JOIN fair_article AS fa ON ear.article = fa.id WHERE exhibitor = ? AND ear.amount != 0");
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
				'articles' => $this->translate->{'Articles'},
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

					} else if ($fieldname == 'articles') {
						$value = '';
						if ($tbl >= 3 && $tbl != 7 && $tbl != 8) {
							$article_combo = array();
							$articles = explode('|', $row['articles']);
							foreach ($articles as $article_id) {
								$article = new FairArticle();
								$article->load($article_id, 'id');
								$article_amount = new ExhibitorArticleRel();
								$article_amount->loadAmount($row['id'], $article_id, 'exhibitor', 'article');
								if ($article->wasLoaded() && $article_amount->wasLoaded()) {
									$article_combo[] = $article->get('text') . '(' . $article_amount->get('amount') . ')';
								}
							}

							$value = implode(', ', $article_combo);

						} else {
							$stmt_articles->execute(array($row['id']));
							$row_articles = $stmt_articles->fetchAll(PDO::FETCH_ASSOC);
							$articles = '';
							if (count($row_articles) > 0) {
								foreach ($row_articles as $res) {
									$articles .= 'ID: '.$res['custom_id'].' '.$this->translate->{'Name'}.': '.$res['text'].': '.$this->translate->{'Price'}.': '.$res['price'].' '.$this->translate->{'Amount'}.': '. $res['amount']."\n";
								}
								error_log($articles);
							}

							if ($articles) {
								$value = $articles;
							}
						}

					} else {
						$value = $row[$fieldname];
					}

					$xls->getActiveSheet()->SetCellValue($alpha[$i] . $row_idx, $value);
					$xls->getActiveSheet()->getStyle($alpha[$i].'1:'.$alpha[$i].$row_idx)->getAlignment()->setWrapText(true);
					$xls->getActiveSheet()->getColumnDimension($alpha[$i])->setAutoSize(true);
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
		$fair->loadsimple($_SESSION['user_fair'], 'id');
		$this->set('headline', 'Invoice overview');
		$this->setNoTranslate('fair', $fair);
		if (isset($_SESSION['mail_errors']) && !empty($_SESSION['mail_errors'])) {
			$this->setNoTranslate('mail_errors', $_SESSION['mail_errors']);
			$this->setNoTranslate('error_title', 'An error occured');
			$_SESSION['mail_errors'] = '';
		} else {
			$this->setNoTranslate('mail_errors', '');
		}
		
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

		if ($fair->wasLoaded() && $hasRights) {

			/* Active invoices */
			$stmt = $this->db->prepare("SELECT ei.*, eir.text AS posname FROM exhibitor_invoice AS ei, exhibitor_invoice_rel AS eir 
												 WHERE ei.status = 1 AND eir.invoice = ei.id AND ei.fair = ? AND eir.fair = ? AND eir.type = 'space'");
			$stmt->execute(array($_SESSION['user_fair'], $_SESSION['user_fair']));
			$active_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

			/* Paid invoices */
			$stmt = $this->db->prepare("SELECT ei.*, eir.text AS posname FROM exhibitor_invoice AS ei, exhibitor_invoice_rel AS eir 
												 WHERE ei.status = 2 AND eir.invoice = ei.id AND ei.fair = ? AND eir.fair = ? AND eir.type = 'space'");
			$stmt->execute(array($_SESSION['user_fair'], $_SESSION['user_fair']));
			$paid_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

			/* Old Credited invoices */
			$stmt = $this->db->prepare("SELECT ei.*, eir.text AS posname, eic.cid, eic.created AS cidcreated
													FROM exhibitor_invoice AS ei, 
													exhibitor_invoice_rel AS eir,
													exhibitor_invoice_credited AS eic
													WHERE ei.status = 3 
													AND ei.fair = ? 
													AND eir.fair = ? 
													AND eir.type = 'space'
													AND eic.invoice = ei.id
													AND eir.invoice = ei.id 
													AND eic.fair = ei.fair");
			$stmt->execute(array($_SESSION['user_fair'], $_SESSION['user_fair']));
			$old_credited_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$old_credited_invoices_id = array();
			foreach ($old_credited_invoices AS $invoice) {
				$old_credited_invoices_id[] = $invoice['id'];
			}

			/* Credited invoices */
			$stmt = $this->db->prepare("SELECT ei.*, eir.text AS posname FROM exhibitor_invoice AS ei, exhibitor_invoice_rel AS eir 
												 WHERE ei.status = 3 AND eir.invoice = ei.id AND ei.fair = ? AND eir.fair = ? AND eir.type = 'space' AND ei.id NOT IN('".implode(',', $old_credited_invoices_id)."')");
			$stmt->execute(array($_SESSION['user_fair'], $_SESSION['user_fair']));
			$credited_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

			/* Old Cancelled invoices */
			$stmt = $this->db->prepare("SELECT eih.*, eir.text AS posname FROM exhibitor_invoice_history AS eih, exhibitor_invoice_rel AS eir 
												 WHERE eih.status = 4 AND eir.invoice = eih.id AND eih.fair = ? AND eir.fair = ? AND eir.type = 'space'");
			$stmt->execute(array($_SESSION['user_fair'], $_SESSION['user_fair']));
			$old_cancelled_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);


			/* Cancelled invoices */
			$stmt = $this->db->prepare("SELECT ei.*, eir.text AS posname FROM exhibitor_invoice AS ei, exhibitor_invoice_rel AS eir 
												 WHERE ei.status = 4 AND eir.invoice = ei.id AND ei.fair = ? AND eir.fair = ? AND eir.type = 'space'");
			$stmt->execute(array($_SESSION['user_fair'], $_SESSION['user_fair']));
			$cancelled_invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);


			if ($fair->isLocked()) {
				$this->setNoTranslate('event_locked', true);
				$this->set('event_locked_title', 'Event is locked');
				$this->set('event_locked_content', 'Event is locked and cannot be edited.');
			}

			$this->set('aheadline', 'Active invoices');
			$this->set('pheadline', 'Paid invoices');
			$this->set('cheadline', 'Credited invoices');
			$this->set('dheadline', 'Cancelled invoices');
			$this->setNoTranslate('active_invoices', $active_invoices);
			$this->setNoTranslate('paid_invoices', $paid_invoices);
			$this->setNoTranslate('credited_invoices', $credited_invoices);
			$this->setNoTranslate('old_credited_invoices', $old_credited_invoices);
			$this->setNoTranslate('cancelled_invoices', $cancelled_invoices);
			$this->setNoTranslate('old_cancelled_invoices', $old_cancelled_invoices);
			$this->set('confirm_mark_as_sent', 'Mark invoice as sent for');
			$this->set('confirm_send_invoices', 'Are you sure that you want to send these invoices?');
			$this->set('confirm_credit_invoices', 'Are you sure that you want to credit these invoices?');
			$this->set('send_invoice_comment', 'Enter a message for this mail batch');
			$this->set('confirm_credit_invoice', 'Credit invoice for');
			$this->set('confirm_cancel_invoice', 'Cancel invoice for');
			$this->set('tr_id', 'Invoice ID');
			$this->set('tr_company', 'Company name');
			$this->set('tr_created', 'Created');
			$this->set('tr_view', 'View');
			$this->set('tr_sent', 'Sent');
			$this->set('tr_posname', 'Position');
			$this->set('tr_expires', 'Expires');
			$this->set('tr_credit', 'Credit');
			$this->set('tr_cancel', 'Cancel invoice');
			$this->set('confirmcredit', 'Are you sure that you want to credit this invoice?');
			$this->set('confirmcancel', 'Are you sure that you want to cancel this invoice?');
		}
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
				$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
				                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
				                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
				                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
				                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', '´'=>'', '`'=>'');
				$fixedname = strtr( $file, $unwanted_array );
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
		$this->set('headline', 'Bookings overview');
		$this->setNoTranslate('fair', $fair);

		if (userLevel() == 2){
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

		} else if (userLevel() == 3) {
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
		// Check if fair is locked before loading further
		if ($fair->wasLoaded() && !$fair->isLocked()) {
			/* This function is used by the organizer when they want to approve an applied place directly as paid for whatever reason */
			if ($action == 'approve' && isset($_POST['id'])) {
				$pb = new PreliminaryBooking();
				$pb->load($_POST['id'], 'id');

				if ($pb->wasLoaded()) {
					$pos = new FairMapPosition();
					$pos->load($pb->get('position'), 'id');
					$pos->set('status', 2);
					$pos->set('expires', '0000-00-00 00:00:00');

					$ex = new Exhibitor();
					$ex->set('user', $pb->get('user'));
					$ex->set('fair', $pb->get('fair'));
					$ex->set('position', $pb->get('position'));
					$ex->set('commodity', $_POST['commodity']);
					$ex->set('arranger_message', $pb->get('arranger_message'));
					$ex->set('booking_time', $pb->get('booking_time'));
					$ex->set('edit_time', time());
					$ex->set('clone', 0);
					$ex->set('status', 2);
					
					$exId = $ex->save();
					$pos->save();
					$pb->accept();

					if (isset($_POST['categories']) && is_array($_POST['categories'])) {
						$stmt = $this->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
						foreach ($_POST['categories'] as $cat) {
							$stmt->execute(array($exId, $cat));
						}
					}
					if (isset($_POST['options']) && is_array($_POST['options'])) {
						$stmt = $this->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");
						foreach ($_POST['options'] as $opt) {								
							$stmt->execute(array($exId, $opt));
						}
					}

					if (isset($_POST['articles']) && is_array($_POST['articles'])) {
						$stmt = $this->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
						$arts = $_POST['articles'];
						$amounts = $_POST['artamount'];
						foreach (array_combine($arts, $amounts) as $art => $amount) {
							$stmt->execute(array($exId, $art, $amount));
						}
					}

					$fair = new Fair();
					$fair->loadsimple($ex->get('fair'), 'id');

					/* Check mail settings and send only if setting is set */
					if ($fair->wasLoaded()) {
						$mailSettings = json_decode($fair->get("mail_settings"));
						if (isset($mailSettings->PreliminaryToBooking) && is_array($mailSettings->PreliminaryToBooking)) {
							if (in_array('1', $mailSettings->PreliminaryToBooking)) {
								$user = new User();
								$user->load2($ex->get('user'), 'id');
			
								if ($fair->get('contact_name') == '')
								$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('windowtitle'));
								else
								$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('contact_name'));

								if ($user->get('contact_email') == '')
								$recipient = array($user->get('email'), $user->get('company'));
								else
								$recipient = array($user->get('contact_email'), $user->get('name'));
								/* UPDATED TO FIT MAILJET */
								$mail_user = new Mail();
								$mail_user->setTemplate('preliminary_to_booking_receipt');
								$mail_user->setFrom($from);
								$mail_user->setRecipient($recipient);
								/* Setting mail variables */
								$mail_user->setMailVar('exhibitor_company', $user->get('company'));
								$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
								$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
								$mail_user->setMailVar('event_email', $fair->get('contact_email'));
								$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
								$mail_user->setMailVar('event_website', $fair->get('website'));
								$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
								$mail_user->setMailVar('position_name', $pos->get('name'));
								$mail_user->setMailVar('position_area', $pos->get('area'));
								$mail_user->sendMessage();
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
		} else {
			$this->setNoTranslate('event_locked', true);
			$this->set('event_locked_title', 'Event is locked');
			$this->set('event_locked_content', 'Event is locked and cannot be edited.');
		}
		$user = new User();
		$user->load($_SESSION['user_id'], 'id');
		$fairInvoice = new FairInvoice();
		$fairInvoice->load($_SESSION['user_fair'], 'fair');

		/* Bookings */
		$stmt = $user->db->prepare("SELECT ex.*, 
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
					ORDER BY ex.booking_time DESC");
		$stmt->execute(array($_SESSION['user_fair'], 2));
		$positions_unfinished = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$positions = array();

		foreach ($positions_unfinished as $pos) {
			/* Get categories */
			$stmt = $user->db->prepare('SELECT * FROM exhibitor_category_rel WHERE exhibitor = ? AND category > 0');
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
			$stmt = $user->db->prepare('SELECT * FROM exhibitor_option_rel WHERE exhibitor = ? AND `option` > 0');
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
			$stmt = $this->db->prepare("SELECT row_id, id, fair, status, sent, r_name FROM exhibitor_invoice WHERE exhibitor = ? AND status IN (1, 2) ORDER BY created DESC LIMIT 1");
			$stmt->execute(array($pos['id']));
			$posinvoiceid = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$invoicecompany = array();
			$invoiceid = array();
			$invoicerowid = array();
			$invoiceposname = array();
			$invoicestatus = array();
			$invoicesent = array();
			$invoice_credited_id = array();
			if (count($posinvoiceid) > 0) {
				$arrlength = count($posinvoiceid);
				for($x = 0; $x < $arrlength; $x++) {
					 $invoicecompany[] = $posinvoiceid[$x]['r_name'];
				     $invoiceid[] = $posinvoiceid[$x]['id'];
				     $invoicerowid[] = $posinvoiceid[$x]['row_id'];
				     $invoicestatus[] = $posinvoiceid[$x]['status'];
				     $invoicesent[] = $posinvoiceid[$x]['sent'];
				     $invoice_credited = new ExhibitorInvoiceCredited();
				     $invoice_credited->load($posinvoiceid[$x]['id'], 'invoice');
				     if ($invoice_credited->wasLoaded()) {
				     	$invoice_credited_id[] = $invoice_credited->get('cid');
				     }
					$stmt = $this->db->prepare("SELECT text FROM exhibitor_invoice_rel WHERE `invoice` = ? AND `fair` = ? AND type = 'space' ORDER BY id DESC LIMIT 1");
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
			$pos['invoicerowid'] = implode('|', $invoicerowid);
			$pos['invoiceid'] = implode('|', $invoiceid);
			$pos['invoicestatus'] = implode('|', $invoicestatus);
			$pos['invoicesent'] = implode('|', $invoicesent);
			$pos['invoicecreditedid'] = implode('|', $invoice_credited_id);

			$positions[$pos['position']] = $pos;
		}

		/* Reservations */
		$stmt = $user->db->prepare("SELECT ex.*, 
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
			$stmt = $user->db->prepare('SELECT * FROM exhibitor_category_rel WHERE exhibitor = ? AND category > 0');
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
			$stmt = $user->db->prepare('SELECT * FROM exhibitor_option_rel WHERE exhibitor = ? AND `option` > 0');
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
			$stmt = $this->db->prepare("SELECT row_id, id, fair, status, sent, r_name FROM exhibitor_invoice WHERE exhibitor = ? AND status IN (1, 2) ORDER BY created DESC LIMIT 1");
			$stmt->execute(array($pos['id']));
			$posinvoiceid = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$invoicecompany = array();
			$invoiceid = array();
			$invoicerowid = array();
			$invoiceposname = array();
			$invoicestatus = array();
			$invoicesent = array();
			$invoice_credited_id = array();
			if (count($posinvoiceid) > 0) {
				$arrlength = count($posinvoiceid);
				for($x = 0; $x < $arrlength; $x++) {
					  $invoicecompany[] = $posinvoiceid[$x]['r_name'];
				     $invoiceid[] = $posinvoiceid[$x]['id'];
				     $invoicerowid[] = $posinvoiceid[$x]['row_id'];
				     $invoicestatus[] = $posinvoiceid[$x]['status'];
				     $invoicesent[] = $posinvoiceid[$x]['sent'];
				     $invoice_credited = new ExhibitorInvoiceCredited();
				     $invoice_credited->load($posinvoiceid[$x]['id'], 'invoice');
				     if ($invoice_credited->wasLoaded()) {
				     	$invoice_credited_id[] = $invoice_credited->get('cid');
				     }
					$stmt = $this->db->prepare("SELECT `text` FROM exhibitor_invoice_rel WHERE `invoice` = ? AND `fair` = ? AND type = 'space' ORDER BY id DESC LIMIT 1");
					$stmt->execute(array($posinvoiceid[$x]['id'], $posinvoiceid[$x]['fair']));
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					$invoiceposname[] = $result['text'];
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
			//print_r($invoiceposname);
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
			$pos['invoicerowid'] = implode('|', $invoicerowid);
			$pos['invoiceid'] = implode('|', $invoiceid);
			$pos['invoicestatus'] = implode('|', $invoicestatus);
			$pos['invoicesent'] = implode('|', $invoicesent);
			$pos['invoicecreditedid'] = implode('|', $invoice_credited_id);

			$rpositions[$pos['position']] = $pos;
		}

		/* Cloned reservations */
		$stmt = $user->db->prepare("SELECT ex.*, 
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
			$stmt = $user->db->prepare('SELECT * FROM exhibitor_category_rel WHERE exhibitor = ? AND category > 0');
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
			$stmt = $user->db->prepare('SELECT * FROM exhibitor_option_rel WHERE exhibitor = ? AND `option` > 0');
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
			$stmt = $this->db->prepare("SELECT row_id, id, fair, status, sent, r_name FROM exhibitor_invoice WHERE exhibitor = ? AND status IN (1, 2) ORDER BY created DESC LIMIT 1");
			$stmt->execute(array($pos['id']));
			$posinvoiceid = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$invoiceid = array();
			$invoicerowid = array();
			$invoicestatus = array();
			$invoicesent = array();
			$invoice_credited_id = array();
			if (count($posinvoiceid) > 0) {
				$arrlength = count($posinvoiceid);
				for($x = 0; $x < $arrlength; $x++) {
				     $invoiceid[] = $posinvoiceid[$x]['id'];
				     $invoicerowid[] = $posinvoiceid[$x]['row_id'];
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
			$pos['invoicerowid'] = implode('|', $invoicerowid);
			$pos['invoicestatus'] = implode('|', $invoicestatus);
			$pos['invoicesent'] = implode('|', $invoicesent);
			$pos['invoicecreditedid'] = implode('|', $invoice_credited_id);

			$rcpositions[$pos['position']] = $pos;
		}

		/* History of deleted boookings and reservations */
		$stmt = $user->db->prepare("SELECT ex.*, 
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
					ORDER BY ex.deletion_time DESC");
		$stmt->execute(array($_SESSION['user_fair']));
		$positions_deleted = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$del_positions = array();

		foreach ($positions_deleted as $pos) {
			/* Get categories */
			$stmt = $user->db->prepare('SELECT * FROM exhibitor_category_rel WHERE exhibitor = ? AND category > 0');
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
			$stmt = $user->db->prepare('SELECT * FROM exhibitor_option_rel WHERE exhibitor = ? AND `option` > 0');
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

			$del_positions[] = $pos;
		}


	// History of deleted Preliminary bookings
	$stmt = $user->db->prepare("SELECT prel.*, 
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
				AND prel.fair = ?
				ORDER BY prel.deletion_time DESC");
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
		$del_prelpos[] = $pos;
	}

		
		// Active Preliminary bookings
	$stmt = $user->db->prepare("SELECT prel.*, 
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
	$stmt = $user->db->prepare("SELECT prel.*, 
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
			$this->set('headline', 'Bookings overview');
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
			$this->set('bheadline', 'Booked stand spaces');
			$this->set('rheadline', 'Reservations');
			$this->set('rcheadline', 'Cloned reservations');
			$this->set('prel_table', 'Preliminary bookings (active)');
			$this->set('prel_table_inactive', 'Preliminary bookings (inactive)');
			$this->set('prel_table_deleted', 'Preliminary bookings (deleted)');
			$this->set('fair_registrations_headline', 'Registrations');
			$this->set('fregistrations_notfound', 'No registrations was found.');
			$this->set('fair_registrations_deleted_headline', 'Registrations (deleted)');
			$this->set('fregistrations_deleted_notfound', 'No deleted registrations was found.');
			$this->set('booked_label', 'booked');
			$this->set('reserved_label', 'reserved');
			$this->set('cloned_label', 'Reservation (cloned)');
			$this->set('unknown_label', 'Unknown');
			$this->set('tr_fair', 'Fair');
			$this->set('tr_status', 'Status');
			$this->set('tr_pos', 'Stand space');
			$this->set('tr_area', 'Area');
			$this->set('tr_booker', 'Booked by');
			$this->set('tr_field', 'Trade');
			$this->set('tr_accepted', 'Accepted');
			$this->set('tr_time', 'Time');
			$this->set('tr_deletiontime', 'Deleted');
			$this->set('tr_last_edited', 'Edited');
			$this->set('tr_reserved_until', 'Reserved until');
			$this->set('tr_message', 'Message to organizer in list');
			$this->set('tr_del_message', 'Deletion message');
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
			$this->set('tr_restore', 'Restore on map');
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

	public function markAsSent($ex_id, $row_id) {
		setAuthLevel(2);

		$ex_invoice = new ExhibitorInvoice();
		$ex_invoice->load($row_id, 'row_id');

		$fair = new Fair();
		$fair->load($ex_invoice->get('fair'), 'id');

		$this->setNoTranslate('fair', $fair);

		// Check if fair is locked before loading further
		if ($fair->wasLoaded() && !$fair->isLocked()) {
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
			$stmt = $this->db->prepare("UPDATE exhibitor_invoice SET sent = ? WHERE exhibitor = ? AND id = ?");
			$stmt->execute(array($now, $ex_id, $invoice_id));
		} else {
			$this->setNoTranslate('event_locked', true);
		}
	}

	public function arrangerMessage($type = '', $id = 0) {

		setAuthLevel(1);

		if ($type !== '' && $id > 0) {

			$message = '';

			if ($type == 'preliminary') {
				$prel_booking = new PreliminaryBooking();
				$prel_booking->loadmsg($id, 'id');
				$message = $prel_booking->get('arranger_message');
			} else if ($type == 'registration') {
				$fair_registration = new FairRegistration();
				$fair_registration->loadmsg($id, 'id');
				$message = $fair_registration->get('arranger_message');
			} else if ($type == 'history_registration') {
				$fair_registration_history = new FairRegistrationHistory();
				$fair_registration_history->loadmsg($id, 'id');
				$message = $fair_registration_history->get('arranger_message');
			} else if ($type == 'history_preliminary') {
				$prel_booking_history = new PreliminaryBookingHistory();
				$prel_booking_history->loadmsg($id, 'id');
				$message = $prel_booking_history->get('arranger_message');
			}  else if ($type == 'history_deleted') {
				$exhibitor = new ExhibitorHistory();
				$exhibitor->loadmsg($id, 'id');
				$message = $exhibitor->get('arranger_message');
			} else {
				$exhibitor = new Exhibitor();
				$exhibitor->loadmsg($id, 'id');
				$message = $exhibitor->get('arranger_message');
			}

			if ($this->is_ajax) {
				$this->createJsonResponse();
			}

			$this->setNoTranslate('message', $message);
		}
	}
	public function deletionMessage($type = '', $id = 0) {

		setAuthLevel(1);

		if ($type !== '' && $id > 0) {

			$message = '';

			if ($type == 'history_registration') {
				$fair_registration_history = new FairRegistrationHistory();
				$fair_registration_history->loaddelmsg($id, 'id');
				$message = $fair_registration_history->get('deletion_message');
			} else if ($type == 'history_preliminary') {
				$prel_booking_history = new PreliminaryBookingHistory();
				$prel_booking_history->loaddelmsg($id, 'id');
				$message = $prel_booking_history->get('deletion_message');
			}  else {
				$exhibitor = new ExhibitorHistory();
				$exhibitor->loaddelmsg($id, 'id');
				$message = $exhibitor->get('deletion_message');
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
		$this->setNoTranslate('admins', $as);
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
		$fair = new Fair();
		$fair->loadsimple($_SESSION['user_fair'], 'id');

		// Check if fair is locked before loading further
		if ($fair->wasLoaded() && !$fair->isLocked()) {
			$mailSettings = json_decode($fair->get("mail_settings"));

			$registration = new FairRegistration();
			$registration->load($id, 'id');
			
			/* Delete the Fair Registration */
			$registration->delete($_POST['comment']);
			if (isset($_POST['comment']) && $_POST['comment'] !== '')
				$comment = htmlspecialchars_decode($_POST['comment']);
			else
				$comment = $this->translate->{'No message was given.'};
				
			if (isset($mailSettings->RegistrationCancelled) && is_array($mailSettings->RegistrationCancelled)) {
				/* Prepare to send the mail */
				if ($fair->get('contact_name') == '')
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('windowtitle'));
				else
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('contact_name'));
				$user = new User();
				$user->load2($registration->get('user'), 'id');

				/* Check mail settings and send only if setting is set */
				if (in_array("1", $mailSettings->RegistrationCancelled)) {
					if ($user->get('contact_email') == '')
					$recipient = array($user->get('email'), $user->get('company'));
					else
					$recipient = array($user->get('contact_email'), $user->get('name'));
					/* UPDATED TO FIT MAILJET */
					$mail_user = new Mail();
					$mail_user->setTemplate('registration_cancelled_receipt');
					$mail_user->setFrom($from);
					$mail_user->setRecipient($recipient);
					/* Setting mail variables */
					$mail_user->setMailVar('exhibitor_company', $user->get('company'));
					$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
					$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
					$mail_user->setMailVar('event_email', $fair->get('contact_email'));
					$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
					$mail_user->setMailVar('event_website', $fair->get('website'));
					$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
					$mail_user->setMailVar('comment', $comment);
					$mail_user->sendMessage();
				}
			}
		} else {
			$this->setNoTranslate('event_locked', true);
		}
	}

	public function creditInvoicePDF($ex_id, $row_id) {
		setAuthLevel(2);

		require_once ROOT.'lib/tcpdf/tcpdf.php';

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$exhibitor = new Exhibitor();
		$exhibitor->load($ex_id, 'id');

		if ($exhibitor->wasLoaded()) {
			$exId = $exhibitor->get('exhibitor_id');
		} else {
			$exhibitor = new ExhibitorHistory();
			$exhibitor->load($ex_id, 'id');
			$exId = $exhibitor->get('id');
		}

		$ex_user = new User();
		$ex_user->load($exhibitor->get('user'), 'id');
		$ex_userId = $ex_user->get('id');

		$ex_invoice = new ExhibitorInvoice();
		$ex_invoice->load($row_id, 'row_id');

		$fair = new Fair();
		$fair->loadsimple($ex_invoice->get('fair'), 'id');

		$this->setNoTranslate('fair', $fair);
		// Check if fair is locked before loading further
		if ($fair->wasLoaded() && !$fair->isLocked()) {
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
			$sender_billing_orgnr = $ex_invoice->get('orgnr');
			$sender_billing_bank_no = $ex_invoice->get('bank_no');
			$sender_billing_postgiro = $ex_invoice->get('postgiro');
			$sender_billing_vat_no = $ex_invoice->get('vat_no');
			$sender_billing_iban_no = $ex_invoice->get('iban_no');
			$sender_billing_swift_no = $ex_invoice->get('swift_no');
			$sender_billing_swish_no = $ex_invoice->get('swish_no');
			$sender_billing_phone = $ex_invoice->get('s_phone');
			$sender_billing_email = $ex_invoice->get('s_email');
			$sender_billing_website = $ex_invoice->get('s_website');


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
			$swish_label = $this->translate->{'Swish'};
			$orgnr_label = $this->translate->{'Org.no'};
			$vat_label = $this->translate->{'TAX.no'};
			$bankgiro_label = $this->translate->{'Bank number'};
			$description_label = $this->translate->{'Description'};
			$price_label = $this->translate->{'Price'};
			$phone_label = $this->translate->{'phone'};
			if ($sender_billing_email != '') {
				$email_label = $this->translate->{'Email'};
			} else {
				$email_label = '';
			}
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

			if ($sender_billing_swish_no == '')
				$swish_label = '';

			$current_user = new User();
			$current_user->load($_SESSION['user_id'], 'id');


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

			if (!empty($invoice_options)) {
				foreach ($invoice_options as $opts) {
							$option_id[] = $opts['custom_id'];
							$option_text[] = $opts['text'];
							$option_price[] = $opts['price'];
							$option_vat[] = $opts['vat'];
				}
					
				$options = array($option_id, $option_text, $option_price, $option_vat);
			}

			// Articles

			$stmt = $this->db->prepare("SELECT custom_id, text, price, amount, vat FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ? AND type = 'article'");
			$stmt->execute(array($ex_invoice->get('id'), $fairId));
			$invoice_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$articles = array();
			if (!empty($invoice_articles)) {
				foreach ($invoice_articles as $arts) {
						$art_id[] = $arts['custom_id'];
						$art_text[] = $arts['text'];
						$art_price[] = $arts['price'];
						$art_amount[] = $arts['amount'];
						$art_vat[] = $arts['vat'];
				}

				$articles = array($art_id, $art_text, $art_price, $art_amount, $art_vat);
			}
			
			$exhibitor_company_name = $ex_invoice->get('r_name');
			$exhibitor_name = $ex_invoice->get('r_reference');
			$date = date('d-m-Y');
			$now = time();
			$expirationdate = $ex_invoice->get('expires');
			$parent_invoice_id = $ex_invoice->get('id');


	/******************************************************************************/
	/******************************************************************************/
	/*****************     FIND OUT WHAT INVOICE ID TO USE        *****************/
	/******************************************************************************/
	/******************************************************************************/


		// Check for the newest invoice id for this fair
		$stmt = $this->db->prepare("SELECT id FROM exhibitor_invoice as id WHERE fair = ? order by id desc limit 1");
		$stmt->execute(array($fairId));
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		$current_invoices_id = $result['id'];
		// Check for the newest invoice id for this fair in deleted invoices as well
		$stmt = $this->db->prepare("SELECT id FROM exhibitor_invoice_history as id WHERE fair = ? order by id desc limit 1");
		$stmt->execute(array($fairId));
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		$deleted_invoices_id = $result['id'];
		// Now that we know the invoice IDs, check which one is highest and save it to a new variable ($invoice_id), or if none was found, set the new variable to null
		if ($current_invoices_id > $deleted_invoices_id) {
			$invoice_id = $current_invoices_id;
		} else if ($current_invoices_id < $deleted_invoices_id) {
			$invoice_id = $deleted_invoices_id;
		} else {
			$invoice_id = null;
		}
		// Check if the arranger set a new invoice number in the invoice settings
		$stmt = $this->db->prepare("SELECT invoice_id_start as id FROM fair_invoice WHERE fair = ?");
		$stmt->execute(array($fairId));
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		$invoice_settings_id = $result['id'];
		$use_invoice_settings = false;

		// Compare the invoice ids and use the highest one.
		if ($invoice_id < $invoice_settings_id) {
			$invoice_id = $invoice_settings_id;
			$use_invoice_settings = true;
		}

		// Check if fair is part of any fairgroup and if it also shares invoice id with that group.
		$isGrouped = new FairGroupRel();
		$isGrouped->load($fairId, 'fair');
		if ($isGrouped->wasLoaded() && ($isGrouped->get('share_invoice') == 1)) {
			$fairGroup = new FairGroup();
			$fairGroup->loadself($isGrouped->get('group'), 'id');
			if ($fairGroup->wasLoaded()) {
				if ($invoice_id <= $fairGroup->get('invoice_no')) {
					$invoice_id = $fairGroup->get('invoice_no');
					$fairGroupInvoiceId = $fairGroup->get('invoice_no');
					$fairGroupInvoiceId++;
					$fairGroup->set('invoice_no', $fairGroupInvoiceId);
					$fairGroup->save();
				}
			}
		}
		if (!$use_invoice_settings) {
			$invoice_id++;
		}

		// Insert the invoice data to database
		$stmt = $this->db->prepare("INSERT INTO exhibitor_invoice (id, ex_user, fair, created, author, exhibitor, expires, r_reference, r_name, r_address, r_zipcode, r_city, r_country, s_reference, s_name, s_address, s_zipcode, s_city, s_country, s_website, s_phone, s_email, orgnr, bank_no, postgiro, vat_no, iban_no, swift_no, swish_no, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 3)");
		$stmt->execute(array($invoice_id, $ex_userId, $fairId, $now, $author, $exId, $expirationdate, $exhibitor_name, $rec_billing_company_name, $rec_billing_address, $rec_billing_zipcode, $rec_billing_city, $rec_billing_country, $sender_billing_reference, $sender_billing_company_name, $sender_billing_address, $sender_billing_zipcode, $sender_billing_city, $sender_billing_country, $sender_billing_website, $sender_billing_phone, $sender_billing_email, $sender_billing_orgnr, $sender_billing_bank_no, $sender_billing_postgiro, $sender_billing_vat_no, $sender_billing_iban_no, $sender_billing_swift_no, $sender_billing_swish_no));


			// Update the active invoice to credited in the database
			$stmt = $this->db->prepare("UPDATE exhibitor_invoice SET `status` = 5 WHERE exhibitor = ? AND id = ?");
			$stmt->execute(array($exId, $parent_invoice_id));

			$logo_name = 'file://' . ROOT . 'public/images/fairs/cfslogo.png';

			foreach (new DirectoryIterator(ROOT . 'public/images/fairs/' . $fairId . '/logotype/') as $file) {
			 if ($file->isDot()) {
			  continue;
			 }
			 
			 if (!$file->isFile()) {
			  continue;
			 }
			 
			 $logo_name = $file->getPathname();
			 break;
			}

	/*********************************************************************************************/
	/*********************************************************************************************/
	/*****************    				SET DOCUMENT INFORMATION   				******************/
	/*********************************************************************************************/
	/*********************************************************************************************/

			$pdf->SetCreator('Chartbooker Fair System');
			$pdf->SetAuthor($author);
			$pdf->SetTitle($invoice_for_label . ' ' . $exhibitor_company_name);

			$pdf->setHtmlHeader('
				<table>
					<tr>
						<td style="width:335px;">
							<img style="height:70px;" src="'. $logo_name . '"/>
						</td>
						<td>
							<br/><br/><b style="font-size:23px; text-alight:right;">' . $credit_invoice_label . ' ' . $invoice_id . '</b><br>' . $printdate_label . ': ' . $date . '
						</td>
					</tr>
				</table>');

			$pdf->setHtmlFooter('
				<hr>
				<br/>
				<table>
					<tr>
						<td colspan="1"><b>'. $address_label .'</b></td>
						<td colspan="1"><b>'. $organization_label .'</b></td>
						<td colspan="1"><b>'. $payment_info_label .'</b></td>
					</tr>
					<tr>
						<td colspan="1">' . $sender_billing_company_name . '</td>
						<td colspan="1">' . $orgnr_label . ' &nbsp; ' . $sender_billing_orgnr . '</td>
						<td colspan="1">' . $bankgiro_label . ' &nbsp;' . $sender_billing_bank_no . '</td>
					</tr>
					<tr>
						<td colspan="1"><br>' . $sender_billing_address . '</td>
						<td colspan="1">' . $vat_label . ' &nbsp;' . $sender_billing_vat_no . '</td>
						<td colspan="1">' . $swish_label . ' &nbsp;' . $sender_billing_swish_no . '</td>
					</tr>
					<tr>
						<td colspan="1">' . $sender_billing_zipcode . ' ' . $sender_billing_city . '</td>
						<td colspan="1">' . $phone_label . ' &nbsp;' . $sender_billing_phone . '</td>
						<td colspan="1">' . $postgiro_label . ' &nbsp;' . $sender_billing_postgiro . '</td>
					</tr>
					<tr>
						<td colspan="1">' . $sender_billing_website . '</td>
						<td colspan="1">' . $email_label . ' &nbsp;' . $sender_billing_email . '</td>
						<td colspan="1">' . $iban_label . ' &nbsp;' . $sender_billing_iban_no . '</td>
					</tr>
					<tr>
						<td colspan="1"></td>
						<td colspan="1"></td>
						<td colspan="1">' . $swift_label . ' &nbsp;' . $sender_billing_swift_no . '</td>
					</tr>
				<br>
				</table>');


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
				<td class="normal">' . $parent_invoice_id . '</td>
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
	.payment_instructions {
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
	// Insert the invoice space data to database
	$stmt_invoice_rel1 = $this->db->prepare("INSERT INTO exhibitor_invoice_rel (invoice, fair, text, price, amount, vat, type, information) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
	$stmt_invoice_rel1->execute(array($invoice_id, $fairId, $position_name, $position_price, 1, $position_vat, 'space', $position_information));

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

				// Insert the invoice option data to database
				$stmt_invoice_rel2 = $this->db->prepare("INSERT INTO exhibitor_invoice_rel (invoice, fair, custom_id, text, price, amount, vat, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
				$stmt_invoice_rel2->execute(array($invoice_id, $fairId, $options[0][$row], $options[1][$row], $options[2][$row], 1, $options[3][$row], 'option'));
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
				// Insert the invoice option data to database
				$stmt_invoice_rel3 = $this->db->prepare("INSERT INTO exhibitor_invoice_rel (invoice, fair, custom_id, text, price, amount, vat, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
				$stmt_invoice_rel3->execute(array($invoice_id, $fairId, $articles[0][$row], $articles[1][$row], $articles[2][$row], $articles[3][$row], $articles[4][$row], 'article'));
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
			<td colspan="4" class="payment_instructions" nobr="true" align="right">'.$currency.' '.$credited_label.':&nbsp;&nbsp;'
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
	$pdf->Output(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$ex_invoice->get('exhibitor').'/'.$exhibitor_company_name . '-' . $position_name . '-' . $invoice_id . '_credited.pdf', 'F');

	header('Location: '.BASE_URL.'invoices/fairs/'.$fairId.'/exhibitors/'.$ex_invoice->get('exhibitor').'/'.$exhibitor_company_name . '-' . $position_name . '-' . $invoice_id . '_credited.pdf');
	//============================================================+
	// END OF FILE
	//============================================================+
	} else {
		$this->setNoTranslate('event_locked', true);
	}
} 



	public function cancelInvoicePDF($ex_id, $row_id) {
		setAuthLevel(2);

		require_once ROOT.'lib/tcpdf/tcpdf.php';

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$ex_invoice = new ExhibitorInvoice();
		$ex_invoice->load($row_id, 'row_id');

		$fair = new Fair();
		$fair->load($ex_invoice->get('fair'), 'id');

		$this->setNoTranslate('fair', $fair);
		if ($fair->wasLoaded() && !$fair->isLocked() && $ex_invoice->get('exhibitor') == $ex_id) {
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
						$bankgiro_label = $this->translate->{'Bankgiro'};
						$description_label = $this->translate->{'Description'};
						$price_label = $this->translate->{'Price'};
						$phone_label = $this->translate->{'phone'};
						$email_label = $this->translate->{'Email'};
						$amount_label = $this->translate->{'Quantity'};
						$booked_space_label = $this->translate->{'Booked stand'};
						$options_label = $this->translate->{'Options'};
						$articles_label = $this->translate->{'Articles'};
						$tax_label = $this->translate->{'Tax'};
						$parttotal_label = $this->translate->{'Subtotal'};
						$total_label = $this->translate->{'total:'};
						$net_label = $this->translate->{'Net'};
						$rounding_label = $this->translate->{'Rounding'};
						$cancelled_label = $this->translate->{'Cancelled'};
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
						if (!empty($invoice_options)) {					
							foreach ($invoice_options as $opts) {
										$option_id[] = $opts['custom_id'];
										$option_text[] = $opts['text'];
										$option_price[] = $opts['price'];
										$option_vat[] = $opts['vat'];
							}
							
							$options = array($option_id, $option_text, $option_price, $option_vat);
						}

						// Articles

						$stmt = $this->db->prepare("SELECT custom_id, text, price, amount, vat FROM exhibitor_invoice_rel WHERE invoice = ? AND fair = ? AND type = 'article'");
						$stmt->execute(array($ex_invoice->get('id'), $fairId));
						$invoice_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

						$articles = array();
						if (!empty($invoice_articles)) {
							foreach ($invoice_articles as $arts) {
									$art_id[] = $arts['custom_id'];
									$art_text[] = $arts['text'];
									$art_price[] = $arts['price'];
									$art_amount[] = $arts['amount'];
									$art_vat[] = $arts['vat'];
							}

							$articles = array($art_id, $art_text, $art_price, $art_amount, $art_vat);
						}
						
						$exhibitor_company_name = $ex_invoice->get('r_name');
						$exhibitor_name = $ex_invoice->get('r_reference');
						$date = date('d-m-Y');
						$now = time();
						$expirationdate = $ex_invoice->get('expires');
						$invoice_id = $ex_invoice->get('id');


						$cancel_invoice_id = $invoice_id;

						// Update the active invoice to cancelled in the database
						$stmt = $this->db->prepare("UPDATE exhibitor_invoice SET `status` = 4 WHERE row_id = ? AND id = ? AND exhibitor = ?");
						$stmt->execute(array($row_id, $invoice_id, $ex_id));

						$logo_name = 'file://' . ROOT . 'public/images/fairs/cfslogo.png';

						foreach (new DirectoryIterator(ROOT . 'public/images/fairs/' . $fairId . '/logotype/') as $file) {
						 if ($file->isDot()) {
						  continue;
						 }
						 
						 if (!$file->isFile()) {
						  continue;
						 }
						 
						 $logo_name = $file->getPathname();
						 break;
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
								<br/><br/><b style="font-size:23px; text-alight:right;">' . $invoice_cancels_label . ' ' . $cancel_invoice_id . '</b><br>' . $printdate_label . ': ' . $date . '
							</td>
						</tr>
					</table>');

				$pdf->setHtmlFooter('
					<hr>
					<br/>
					<table>
						<tr>
							<td colspan="1"><b>'. $address_label .'</b></td>
							<td colspan="1"><b>'. $organization_label .'</b></td>
							<td colspan="1"><b>'. $payment_info_label .'</b></td>
						</tr>
						<tr>
							<td colspan="1">' . $sender_billing_company_name . '</td>
							<td colspan="1">' . $orgnr_label . ' &nbsp; ' . $sender_billing_orgnr . '</td>
							<td colspan="1">' . $bankgiro_label . ' &nbsp;' . $sender_billing_bank_no . '</td>
						</tr>
						<tr>
							<td colspan="1"><br>' . $sender_billing_address . '</td>
							<td colspan="1">' . $vat_label . ' &nbsp;' . $sender_billing_vat_no . '</td>
							<td colspan="1">' . $postgiro_label . ' &nbsp;' . $sender_billing_postgiro . '</td>
						</tr>
						<tr>
							<td colspan="1">' . $sender_billing_zipcode . ' ' . $sender_billing_city . '</td>
							<td colspan="1">' . $phone_label . ': ' . $sender_billing_phone . '</td>
							<td colspan="1">' . $iban_label . ' &nbsp;' . $sender_billing_iban_no . '</td>
						</tr>
						<tr>
							<td colspan="1">' . $sender_billing_website . '</td>
							<td colspan="1">' . $email_label . ': ' . $sender_billing_email . '</td>
							<td colspan="1">' . $swift_label . ' &nbsp;' . $sender_billing_swift_no . '</td>
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
		.payment_instructions {
			font-size: 20px;
		}
		.pennys {
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
				<td colspan="4" class="pennys" align="right" nobr="true">'.$rounding_label.':&nbsp;&nbsp;'
				. str_replace('.', ',', number_format($pennys, 2, ',', ' ')) . 
				'</td>
			</tr>
			<tr>
				<td colspan="4" class="payment_instructions" nobr="true" align="right">'.$currency.' '.$total_label.'&nbsp;&nbsp;0,00</td>
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
	} else {
		$this->setNoTranslate('event_locked', true);
	}
}


	public function deleteBooking($id = 0, $posId = 0) {

		setAuthLevel(2);

		$status = $_POST['status'];

		$position = new FairMapPosition();
		$position->load($posId, 'id');

		$fairMap = new FairMap();
		$fairMap->load($position->get("map"), "id");

		$fair = new Fair();
		$fair->loadsimple($fairMap->get('fair'), 'id');

		/* Check if fair is locked before loading further */
		if ($fair->wasLoaded() && !$fair->isLocked()) {
			if ($status == "preliminary") {
				$pb = new PreliminaryBooking();
				$pb->load($id, 'id');			
				$user = new User();
				$user->load2($pb->get('user'), 'id');
				$pb->delete($_POST['comment']);
				$mail_type = 'preliminary';
				$mailSetting = "PreliminaryCancelled";
			} else {
				$ex = new Exhibitor();
				$ex->load($id, 'id');
				$user = new User();
				$user->load2($ex->get('user'), 'id');
				$ex->delete($_POST['comment']);
				$position->set('status', 0);
				$position->save();
				$mail_type = 'booking';
				$mailSetting = "BookingCancelled";
			}

			/* Check mail settings and send only if setting is set */
			if ($fair->wasLoaded()) {
				$mailSettings = json_decode($fair->get("mail_settings"));
				if (isset($mailSettings->$mailSetting) && is_array($mailSettings->$mailSetting)) {
					/* Check mail settings and send only if setting is set */
					if (in_array("1", $mailSettings->$mailSetting)) {
						if (isset($_POST['comment']) && $_POST['comment'] !== '')
							$comment = htmlspecialchars_decode($_POST['comment']);
						else
							$comment = $this->translate->{'No message was given.'};
						/* Prepare to send the mail */
						if ($fair->get('contact_name') == '')
						$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('windowtitle'));
						else
						$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('contact_name'));

						if ($user->get('contact_email') == '')
						$recipient = array($user->get('email'), $user->get('company'));
						else
						$recipient = array($user->get('contact_email'), $user->get('name'));
						/* UPDATED TO FIT MAILJET */
						$mail_user = new Mail();
						$mail_user->setTemplate($mail_type . '_cancelled_receipt');
						$mail_user->setFrom($from);
						$mail_user->setRecipient($recipient);
						$mail_user->setMailVar('position_name', $position->get('name'));
						$mail_user->setMailVar('position_area', $position->get('area'));
						$mail_user->setMailVar('exhibitor_company', $user->get('company'));
						$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
						$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
						$mail_user->setMailVar('event_email', $fair->get('contact_email'));
						$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
						$mail_user->setMailVar('event_website', $fair->get('website'));
						$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
						$mail_user->setMailVar('comment', $comment);
						$mail_user->sendMessage();
					}
				}
			}
			if (!isset($_POST["ajax"])) {
				header('Location: '.BASE_URL.'administrator/newReservations');
			}
		} else {
			$this->setNoTranslate('event_locked', true);
		}
	}

	public function approveReservation() {
		setAuthLevel(2);

		if (isset($_POST['id'])) {
			$this->editBooking($_POST['id'], 2);
		}

		header('Location: '.BASE_URL.'administrator/newReservations');
	}
	public function bookingtoReservation() {
		setAuthLevel(2);

		if (isset($_POST['id'])) {
			$this->editBooking($_POST['id'], 1);
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
				$exhibitor->set('clone', 0);
				$exId = $exhibitor->save();


				// Remove old categories for this booking
				$stmt = $this->db->prepare("DELETE FROM exhibitor_category_rel WHERE exhibitor = ?");
				$stmt->execute(array($exId));

				// Set new categories for this booking
				if (isset($_POST['categories']) && is_array($_POST['categories'])) {
					$stmt = $this->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
					foreach ($_POST['categories'] as $cat) {
						$stmt->execute(array($exId, $cat));
					}
				}

				// Remove old options for this booking
				$stmt = $this->db->prepare("DELETE FROM exhibitor_option_rel WHERE exhibitor = ?");
				$stmt->execute(array($exId));

				// Set new options for this booking
				if (isset($_POST['options']) && is_array($_POST['options'])) {
					$stmt = $this->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");
					foreach ($_POST['options'] as $opt) {
						$stmt->execute(array($exId, $opt));
					}
				}

				// Remove old articles for this booking
				$stmt = $this->db->prepare("DELETE FROM exhibitor_article_rel WHERE exhibitor = ?");
				$stmt->execute(array($exId));

				// Set new articles for this booking
				if (isset($_POST['articles']) && is_array($_POST['articles'])) {
					$stmt = $this->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
					foreach (array_combine($_POST['articles'], $_POST['artamount']) as $art => $amount) {
						$stmt->execute(array($exId, $art, $amount));							
					}
				}

				$fair = new Fair();
				$fair->loadsimple($exhibitor->get('fair'), 'id');
				// Check if fair is locked before loading further
			if ($fair->wasLoaded() && !$fair->isLocked()) {

				$pos = new FairMapPosition();
				$pos->load($exhibitor->get('position'), 'id');

				if ($set_status == null) {
					// If this is a reservation (status is 1), then also set the expiry date
					if ($pos->wasLoaded() && $pos->get('status') == 1)
					$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));
				} else if ($set_status == 2) {
					$pos->set('status', $set_status);
					$stmt = $this->db->prepare("SELECT id FROM exhibitor_invoice WHERE exhibitor = ? AND status = 1");
					$stmt->execute(array($exId));
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					if ($result > 0) {
						$stmt = $this->db->prepare("UPDATE exhibitor_invoice SET status = 2 WHERE exhibitor = ? AND id = ?");
						$stmt->execute(array($exId, $result['id']));
					}
					$stmt2 = $this->db->prepare("UPDATE exhibitor SET status = 2 WHERE id = ?");
					$stmt2->execute(array($exId));
					$mailSetting = 'BookingCreated';
					$mail_type = 'booking';
					$pos->set('expires', '0000-00-00 00:00:00');
				} else {
					$pos->set('status', $set_status);
					$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));
					$stmt = $this->db->prepare("SELECT id FROM exhibitor_invoice WHERE exhibitor = ? AND status = 2");
					$stmt->execute(array($exId));
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					if ($result > 0) {
						$stmt = $this->db->prepare("UPDATE exhibitor_invoice SET status = 1 WHERE exhibitor = ? AND id = ?");
						$stmt->execute(array($exId, $result['id']));
					}
					$stmt2 = $this->db->prepare("UPDATE exhibitor SET status = 1 WHERE id = ?");
					$stmt2->execute(array($exId));
					$mailSetting = 'BookingCreated';
					$mail_type = 'reservation';
				}
				$pos->save();
				
				/* Check mail settings and send only if setting is set */
				$mailSettings = json_decode($fair->get("mail_settings"));
				if (isset($mailSettings->$mailSetting) && is_array($mailSettings->$mailSetting)) {
					$user = new User();
					$user->load2($exhibitor->get('user'), 'id');

					/* Prepare to send the mail */
					if ($fair->get('contact_name') == '')
					$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('windowtitle'));
					else
					$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('contact_name'));
						
					if (in_array("1", $mailSettings->$mailSetting)) {
						if ($user->get('contact_email') == '')
						$recipient = array($user->get('email'), $user->get('company'));
						else
						$recipient = array($user->get('contact_email'), $user->get('name'));
						/* UPDATED TO FIT MAILJET */
						$mail_user = new Mail();
						$mail_user->setTemplate($mail_type . '_created_receipt');
						$mail_user->setFrom($from);
						$mail_user->setRecipient($recipient);
						/* Setting mail variables */
						$mail_user->setMailVar('exhibitor_company', $user->get('company'));
						$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
						$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
						$mail_user->setMailVar('event_email', $fair->get('contact_email'));
						$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
						$mail_user->setMailVar('event_website', $fair->get('website'));
						$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
						$mail_user->setMailVar('position_name', $pos->get('name'));
						$mail_user->setMailVar('position_area', $pos->get('area'));
						if ($pos->wasLoaded() && $pos->get('status') == 1) {
							$mail_user->setMailVar('expirationdate', $_POST['expires']);
						}
						$mail_user->sendMessage();
					}
				}
			} else {
				$this->set('error_title', 'Error when loading the booking. Contact the support team to resolve the issue.');
			}
		} else {
			$this->set('error_title', 'A booking ID must be set.');
		}
		header('Location: ' . BASE_URL . 'administrator/newReservations');
	}
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

				$fair = new Fair();
				$fair->loadsimple($pb->get('fair'), 'id');

				// Check if fair is locked before loading further
				if ($fair->wasLoaded() && !$fair->isLocked()) {

					$previous_status = 3;
					$status = 1;
					$pos->set('status', $status);
					$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));

					$ex = new Exhibitor();
					$ex->set('user', $pb->get('user'));
					$ex->set('fair', $pb->get('fair'));
					$ex->set('position', $pb->get('position'));
					$ex->set('commodity', $_POST['commodity']);
					$ex->set('arranger_message', $pb->get('arranger_message'));
					$ex->set('booking_time', $pb->get('booking_time'));
					$ex->set('edit_time', time());
					$ex->set('clone', 0);
					$ex->set('status', 1);
					
					$exId = $ex->save();
					$pos->save();
					$pb->accept();

					if (isset($_POST['categories']) && is_array($_POST['categories'])) {
						$stmt = $this->db->prepare("INSERT INTO `exhibitor_category_rel` (`exhibitor`, `category`) VALUES (?, ?)");
						foreach ($_POST['categories'] as $cat) {
							$stmt->execute(array($exId, $cat));
						}
					}

					if (isset($_POST['options']) && is_array($_POST['options'])) {
						$stmt = $this->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");
						foreach ($_POST['options'] as $opt) {								
							$stmt->execute(array($exId, $opt));
						}
					}

					if (isset($_POST['articles']) && is_array($_POST['articles'])) {
						$stmt = $this->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
						foreach (array_combine($_POST['articles'], $_POST['artamount']) as $art => $amount) {
							$stmt->execute(array($exId, $art, $amount));						
						}
					}
					
				/* Check mail settings and send only if setting is set */
				$mailSettings = json_decode($fair->get("mail_settings"));
				if (isset($mailSettings->PreliminaryToReservation) && is_array($mailSettings->PreliminaryToReservation)) {
					$user = new User();
					$user->load2($ex->get('user'), 'id');

					/* Prepare to send the mail */
					if ($fair->get('contact_name') == '')
						$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('windowtitle'));
					else
						$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('contact_name'));
						
					if (in_array("1", $mailSettings->PreliminaryToReservation)) {
						if ($user->get('contact_email') == '')
						$recipient = array($user->get('email'), $user->get('company'));
						else
						$recipient = array($user->get('contact_email'), $user->get('name'));
						/* UPDATED TO FIT MAILJET */
						$mail_user = new Mail();
						$mail_user->setTemplate('preliminary_to_reservation_receipt');
						$mail_user->setFrom($from);
						$mail_user->setRecipient($recipient);
						$mail_user->setMailVar('exhibitor_company', $user->get('company'));
						$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
						$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
						$mail_user->setMailVar('event_email', $fair->get('contact_email'));
						$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
						$mail_user->setMailVar('event_website', $fair->get('website'));
						$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
						$mail_user->setMailVar('position_name', $pos->get('name'));
						$mail_user->setMailVar('position_area', $pos->get('area'));
						$mail_user->sendMessage();
					}
				}
			} else {
				$this->setNoTranslate('event_locked', true);
			}
		}

		header('Location: '.BASE_URL.'administrator/newReservations');
		exit;
	}

}

?>
