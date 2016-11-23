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
				$this->setNoTranslate('users', $users);
			}
		}

	}
	public function exfind() {
		
		setAuthLevel(2);
		
		$stmt = $this->Exhibitor->db->prepare("SELECT user.id, exhibitor.fair FROM user LEFT JOIN exhibitor ON user.id = exhibitor.user WHERE user.level = ? ORDER BY ?");
		$stmt->execute(array(1, 'exhibitor.fair'));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$exhibitors = array();
				
		foreach ($result as $res) {
			if (intval($res['id']) > 0) {
				$ex = new User;
				$ex->load($res['id'], 'id');
								
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

		
		$unique = array();
		for ($i=0; $i<count($exhibitors); $i++) {
			$exhibitors[$i]->set('ex_count', $counter[$exhibitors[$i]->get('id')]);
			if (!array_key_exists($exhibitors[$i]->get('id'), $unique))
				$unique[$exhibitors[$i]->get('id')] = $exhibitors[$i];
		}
		
		$this->set('headline', 'Find Exhibitors');
		$this->set('th_company', 'Company');
		$this->set('th_orgnr', 'Organization number');
		$this->set('th_name', 'Name');
		$this->set('th_phone', 'Phone 1');
		$this->setNoTranslate('users', $unique);		
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
		$this->set('th_company', 'Company');
		$this->set('th_orgnr', 'Organization number');
		$this->set('th_name', 'Name');
		$this->set('th_email', 'E-mail');
		$this->set('th_phone', 'Cellphone');
		$this->set('th_fairs', 'Fairs');
		$this->set('th_bookings', 'Bookings');
		$this->set('th_last_login', 'Last login');
		$this->set('th_created', 'Created');
		$this->set('th_edit', 'Edit');
		$this->set('th_delete', 'Delete');
		$this->set('send_sms_label', 'Send SMS to selected Exhibitors');
		$this->set('th_resend', 'Reset');
		$this->setNoTranslate('fairs', $fairs);
		$this->setNoTranslate('users', $unique);		
	}
	
	public function forFair($param='', $value='') {
		
		setAuthLevel(2);

		if (userLevel() == 3) {
			$fair = new Fair;
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
		$canceled = array();
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
		
		$stmt = $this->Exhibitor->db->prepare("SELECT fair_user_relation.user, fair_user_relation.connected_time FROM fair_user_relation LEFT JOIN user ON fair_user_relation.user = user.id WHERE fair_user_relation.fair = ? AND user.level = ? ORDER BY user.company");
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
				$ex->set('connected_time', $res['connected_time']);
				$exhibitorId = $ex->get('id');


				$stmt3 = $this->Exhibitor->db->prepare("SELECT * FROM exhibitor_canceled WHERE fairId = ? AND exhibitorId = ?");
				$stmt3->execute(array($_SESSION['user_fair'], $exhibitorId));
				$result3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);

				if(count($result3)  == 0):
					$connected[] = $ex;
				endif;
				
			}
		}

		$stmt = $this->Exhibitor->db->prepare("SELECT * FROM exhibitor_canceled INNER JOIN user ON exhibitor_canceled.exhibitorId = user.id WHERE fairId = ?");
		$stmt->execute(array($_SESSION['user_fair']));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach($result as $unbooked):
			$stmt2 = $this->Exhibitor->db->prepare("SELECT * FROM exhibitor WHERE user = ? and fair = ?");
			$stmt2->execute(array($unbooked['exhibitorId'], $unbooked['fairId']));
			$result2 = $stmt2->fetch(PDO::FETCH_ASSOC);

			if(count($result2) == 0):
				$canceled[] = $unbooked;
			endif;
		endforeach;

		$this->set('table_exhibitors', 'Booked exhibitors');
		$this->set('table_connected', 'Connected exhibitors');
		$this->set('table_canceled', 'Canceled exhibitors');
		$this->set('headline', 'Exhibitors');
		$this->set('create_link', 'New exhibitor');
		$this->set('th_company', 'Company');
		$this->set('th_contactperson', 'Contact person');
		$this->set('th_name', 'Name');
		$this->set('th_fairs', 'Fairs');
		$this->set('th_last_login', 'Last login');
		$this->set('th_connect_time', 'Connected to fair on');
		$this->set('tr_comments', 'Notes');
		$this->set('th_edit', 'Edit');
		$this->set('th_delete', 'Delete');
		$this->setNoTranslate('fairs', $fairs);
		//$this->set('', $fairs);
		$this->setNoTranslate('users', $exhibitors);
		$this->setNoTranslate('connected', $connected);
		$this->setNoTranslate('canceled', $canceled);
		$this->set('th_copy', 'Copy to map');
		$this->set('send_sms_label', 'Send SMS to selected Exhibitors');
		$this->set('export', 'Export to Excel');
	}
	
	public function exhibitors($fairId=0) {

		setAuthLevel(2);

		if (userLevel() == 3) {
			$fair = new Fair;
			$fair->load($fairId, 'id');
			if ($fair->wasLoaded() && $fair->get('created_by') != $_SESSION['user_id']) {
				toLogin();
			}
		}

		if (userLevel() == 2) {
			$stmt = $this->db->prepare('SELECT * FROM fair_user_relation WHERE user=? AND fair=?');
			$stmt->execute(array($_SESSION['user_id'], $fairId));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (!$result) {
				toLogin();
			}
		}

		if (userLevel() == 4 && $fairId == 0) {
			$fairId = $_SESSION['user_fair'];
		}

		$this->set('headline', 'Exhibitor overview');
		$this->set('create_link', 'Create new exhibitor');
		$this->set('th_status', 'Status');
		$this->set('th_address', 'Address');
		$this->set('th_branch', 'Branch');
		$this->set('th_name', 'Stand space');
		$this->set('th_company', 'Company');
		$this->set('th_phone', 'Cellphone');
		$this->set('th_contact', 'Name');
		$this->set('th_email', 'E-mail');
		$this->set('th_website', 'Website');
		$this->set('th_view', 'View');
		$this->set('th_profile', 'View profile');
		$this->set('export_button', 'Export as excel');
		$this->set('send_sms_label', 'Send SMS to selected Exhibitors');
		$this->set('label_booked', 'booked');
		$this->set('label_reserved', 'reserved');
		$this->setNoTranslate('fairId', $fairId);
		$this->set('col_export_err', 'Select at least one column in order to export!');
		$this->set('row_export_err', 'Select at least one row in order to export!');
		$sql = 'SELECT user.*, exhibitor.position AS position, exhibitor.fair AS fair, exhibitor.commodity AS excommodity, pos.name AS posname, pos.status AS posstatus, pos.map AS posmap FROM exhibitor, user, fair_map_position AS pos WHERE exhibitor.fair = ? AND exhibitor.position = pos.id AND exhibitor.user = user.id';
		$stmt = $this->db->prepare($sql);
		$stmt->execute(array($fairId));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$this->setNoTranslate('exhibitors', $result);

	}
	
	public function exportForFair($tbl){
		setAuthLevel(2);

		$this->setNoTranslate('noView', true);

		if (isset($_POST['rows'], $_POST['field']) && is_array($_POST['rows']) && is_array($_POST['field'])) {

			/* Samla relevant information till en array
			beroende på vilken tabell som är vald */
			$u = new User;
			$u->load($_SESSION['user_id'], 'id');

			if ($tbl == 1) {
				$stmt = $this->db->prepare("SELECT exhibitor.*, user.*, (SELECT COUNT(*) FROM fair_user_relation WHERE user = exhibitor.user) AS fair_count FROM user, exhibitor WHERE user.id = exhibitor.user AND user.level = 1 AND exhibitor.fair = ? AND exhibitor.user IN (" . implode(',', $_POST['rows']) . ") GROUP BY user.id ORDER BY fair, user.company");
				$stmt->execute(array($_SESSION['user_fair']));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

			} else if ($tbl == 2) {
				$stmt = $this->db->prepare("SELECT outr_fur.user, outr_fur.connected_time, user.*, (SELECT COUNT(*) FROM fair_user_relation WHERE user = outr_fur.user) AS fair_count FROM fair_user_relation AS outr_fur LEFT JOIN user ON outr_fur.user = user.id WHERE outr_fur.fair = ? AND user.level = 1 AND user.id IN (" . implode(',', $_POST['rows']) . ") ORDER BY user.company");
				$stmt->execute(array($_SESSION['user_fair']));
				$data_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}

			/* Har nu tabellinformationen i en array, 
			sätt in informationen i ett exceldokument 
			och skicka i headern */
			
			if ($tbl == 1) {
				$filename = "BookedForFair.xlsx";
				$label_status = $this->translate->{'Booked'};
			} else if ($tbl == 2) {
				$filename = "ConnectedToFair.xlsx";
				$label_status = $this->translate->{'Connected'};
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
				'fair_count' => $this->translate->{'Fairs'},
				'last_login' => $this->translate->{'Last login'},
				'connected_time' => $this->translate->{'Connected to fair on'}
			);

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

					if ($fieldname == 'status') {
						$value = $label_status;

					} else if ($fieldname == 'last_login') {
						$value = ($row['last_login'] > 0 ? date('d-m-Y H:i:s', $row['last_login']) : '');

					} else if ($fieldname == 'connected_time') {
						$value = ($row['connected_time'] > 0 ? date('d-m-Y H:i:s', $row['connected_time']) : 'n/a');

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

	public function export($fairId=0, $st, $nm, $cp, $ad, $br, $ph, $co, $em, $wb, $selectedRows) {
		$rows = explode(";", $selectedRows);
		
		setAuthLevel(3);
		$this->setNoTranslate('noView', true);
		
		$fair = new Fair;
		
		if (!$fairId == 0) {
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
						//$commodity = (empty($commodity)) ? $pos->get('user')->get('commodity') : $pos->get('exhibitor')->get('commodity');
						if(in_array($pos->get('id'), $rows)){
							$data[] = array(
								$this->translate->{$pos->get('statusText')},
								$pos->get('name'),
								$pos->get('exhibitor')->get('company'),
								$pos->get('exhibitor')->get('address').' '.$pos->get('exhibitor')->get('city'),
								$pos->get('exhibitor')->get('commodity'),
								$pos->get('exhibitor')->get('phone1'),
								$pos->get('exhibitor')->get('name'),						
								$pos->get('exhibitor')->get('email'),
								$pos->get('exhibitor')->get('website'),
							);
						}
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
		header("Content-Transfer-Encoding: binary");
		require_once ROOT.'lib/PHPExcel-1.7.8/Classes/PHPExcel.php';
		
		$xls = new PHPExcel();
		
		$xls->setActiveSheetIndex(0);
		$count = 0;
		$alpha = range('A', 'Z');
		if($st == 1) {
			$stplace = $alpha[$count];
			$xls->getActiveSheet()->SetCellValue($stplace.'1', $this->translate->{'Status'});
			$count++;
		} if($nm == 1) {
			$nmplace = $alpha[$count];
			$xls->getActiveSheet()->SetCellValue($nmplace.'1', $this->translate->{'Stand space'});
			$count++;
		} if($cp == 1) {
			$cpplace = $alpha[$count];
			$xls->getActiveSheet()->SetCellValue($cpplace.'1', $this->translate->{'Company'});
			$count++;
		} if($ad == 1) {
			$adplace = $alpha[$count];
			$xls->getActiveSheet()->SetCellValue($adplace.'1', $this->translate->{'Address'});
			$count++;
		} if($br == 1) {
			$brplace = $alpha[$count];
			$xls->getActiveSheet()->SetCellValue($brplace.'1', $this->translate->{'Branch'});
			$count++;
		} if($ph == 1) {
			$phplace = $alpha[$count];
			$xls->getActiveSheet()->SetCellValue($phplace.'1', $this->translate->{'Phone'});
			$count++;
		} if($co == 1) {
			$coplace = $alpha[$count];
			$xls->getActiveSheet()->SetCellValue($coplace.'1', $this->translate->{'Name'});
			$count++;
		} if($em == 1) {
			$emplace = $alpha[$count];
			$xls->getActiveSheet()->SetCellValue($emplace.'1', $this->translate->{'Email'});
			$count++;
		} if($wb == 1) {
			$wbplace = $alpha[$count];
			$xls->getActiveSheet()->SetCellValue($wbplace.'1', $this->translate->{'Website'});
			$count++;
		}

		$i = 2;
		foreach ($data as $row) {
			if($st == 1)
				$xls->getActiveSheet()->SetCellValue($stplace.$i, $row[0]);
			if($nm == 1)
				$xls->getActiveSheet()->SetCellValue($nmplace.$i, $row[1]);
			if($cp == 1)
				$xls->getActiveSheet()->SetCellValue($cpplace.$i, $row[2]);
			if($ad == 1)
				$xls->getActiveSheet()->SetCellValue($adplace.$i, $row[3]);
			if($br == 1)
				$xls->getActiveSheet()->SetCellValue($brplace.$i, $row[4]);
			if($ph == 1)
				$xls->getActiveSheet()->SetCellValue($phplace.$i, $row[5], PHPEXcel_Cell_Datatype::TYPE_STRING);
			if($co == 1)
				$xls->getActiveSheet()->SetCellValueExplicit($coplace.$i, $row[6]);
			if($em == 1)
				$xls->getActiveSheet()->SetCellValue($emplace.$i, $row[7]);
			if($wb == 1)
				$xls->getActiveSheet()->SetCellValue($wbplace.$i, $row[8]);
			$i++;
		}
		
		$xls->getActiveSheet()->getStyle('A1:Z1')->applyFromArray(array(
			'font' => array('bold' => true)
		));
		
		$objWriter = new PHPExcel_Writer_Excel2007($xls);
		//$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
		$objWriter->save('php://output');
		
	}
  
	// Created a new function in case the old export is used somewhere else, which is likely
	public function export2($fairId=0) {

		setAuthLevel(3);
		$this->setNoTranslate('noView', true);

		if (isset($_POST['rows'], $_POST['field']) && is_array($_POST['rows']) && is_array($_POST['field'])) {
			$fair = new Fair;

			if (!$fairId == 0) {
				$fair->load($fairId, 'id');
			} else {
				if (isset($_SESSION['user_fair']))
					$fair->load($_SESSION['user_fair'], 'id');
				else if (isset($_SESSION['outside_fair_url']))
					$fair->load($_SESSION['outside_fair_url'], 'url');
			}

			$sql = "SELECT user.*, 
						exhibitor.*, 
						pos.name AS position, 
						pos.status, 
						pos.area,
						pos.information
					FROM exhibitor, user, fair_map_position AS pos 
					WHERE exhibitor.fair = ?
						AND exhibitor.position = pos.id
						AND exhibitor.user = user.id
						AND pos.id IN (".implode(',', $_POST['rows']).")
			";

			$stmt = $this->db->prepare($sql);
			$stmt->execute(array($fair->get('id')));
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

			$stmt_options = $this->db->prepare("SELECT GROUP_CONCAT(feo.text SEPARATOR ', ') AS texts FROM fair_extra_option AS feo INNER JOIN exhibitor_option_rel AS eor ON eor.option = feo.id WHERE exhibitor = ?");

			$column_names = array(
				//$this->translate->{"Select all:"}." ".$this->translate->{"Company"} => array(
				'orgnr' => $this->translate->{'Organization number'},
				'company' => $this->translate->{'Company'},
				'commodity' => $this->translate->{'Commodity'},
				// 'customer_nr' => $this->translate->{'Customer number'},
				'address' => $this->translate->{'Address'},
				'zipcode' => $this->translate->{'Zip code'},
				'city' => $this->translate->{'City'},
				'country' => $this->translate->{'Country'},
				'phone1' => $this->translate->{'Phone 1'},
				'phone2' => $this->translate->{'Phone 2'},
				'fax' => $this->translate->{'Fax number'},
				'email' => $this->translate->{'E-mail'},
				'website' => $this->translate->{'Website'},
				//'presentation' => $this->translate->{'Presentation'},
				//  ),
				//$this->translate->{"Select all:"}." ".$this->translate->{"Billing address"} => array(
				'invoice_company' => $this->translate->{'Company'},
				'invoice_address' => $this->translate->{'Address'},
				'invoice_zipcode' => $this->translate->{'Zip code'},
				'invoice_city' => $this->translate->{'City'},
				'invoice_country' => $this->translate->{'Country'},
				'invoice_email' => $this->translate->{'E-mail'},
				//  ),
				//$this->translate->{"Select all:"}." ".$this->translate->{"Contact person"} => array(
				//'alias' => $this->translate->{'Username'},
				'name' => $this->translate->{'Contact person'},
				'contact_phone' => $this->translate->{'Contact Phone'},
				'contact_phone2' => $this->translate->{'Contact Phone 2'},
				'contact_email' => $this->translate->{'Contact Email'},
				//  )
				'posstatus' => $this->translate->{'Status'},
				'posname' => $this->translate->{'Stand space'},
				'status' => $this->translate->{'Status'},
				'position' => $this->translate->{'Stand'},
				'area' => $this->translate->{'Area'},
				'information' => $this->translate->{'Information about stand space'},
				'commodity' => $this->translate->{'Trade'},
				'extra_options' => $this->translate->{'Extra options'},
				'booking_time' => $this->translate->{'Time of booking'},
				'edit_time' => $this->translate->{'Last edited'},
				'arranger_message' => $this->translate->{'Message to organizer'}
			);

			$label_booked = $this->translate->{'booked'};
			$label_reserved = $this->translate->{'reserved'};

			$filename = "exhibitors.xlsx";
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
			if (count($_POST['field']) > count($alpha)) {
				foreach ($alpha as $letter) {
					$alpha[] = 'A' . $letter;
				}
			}

			// Create column headers
			foreach ($_POST['field'] as $fieldname => $humbug) {
				if ($column_names[$fieldname]) {
					$stplace = $alpha[$count];
					$xls->getActiveSheet()->SetCellValue($stplace.'1', $column_names[$fieldname]);
					$count++;
				}
			}

			$i = 2;
			// Loop through data from database
			foreach ($data as $row) {
				$count = 0;

				foreach ($_POST['field'] as $fieldname => $humbug) {
					if ($column_names[$fieldname]) {
						// Special case taken from existing front-end code
						if ($fieldname == 'booking_time') {
						$value = date('d-m-Y H:i:s', $row['booking_time']);

						} else if ($fieldname == 'edit_time') {
							$value = ($row['edit_time'] > 0 ? date('d-m-Y H:i:s', $row['edit_time']) : '');

						} else if ($fieldname == 'status') {
							if ($row['status'] == 2) {
								$value = $label_booked;
							} else {
								$value = $label_reserved;
							}

						} else if ($fieldname == 'extra_options') {
							$value = '';

							$stmt_options->execute(array($row['id']));
							$options = $stmt_options->fetchObject();
							if ($options) {
								$value = $options->texts;
							}

						} else {
							$value = $row[$fieldname];
						}

						$stplace = $alpha[$count];
						$xls->getActiveSheet()->setCellValueExplicit($stplace.$i, $value);
						$count++;
					}
				}

				$i++;
			}

			$xls->getActiveSheet()->getStyle('A1:AZ1')->applyFromArray(array(
				'font' => array('bold' => true)
			));

			//$xls->getActiveSheet()->getStyle('A' . $i . ':Z' . $i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_GENERAL);

			$objWriter = new PHPExcel_Writer_Excel2007($xls);
			//$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
			$objWriter->save('php://output');
		}
	}

	public function deleteExhibitor($id, $confirmed='', $from='') {
		setAuthLevel(4);

		$this->set('headline', 'Delete exhibitor');
		$this->setNoTranslate('from', $from);

		$user = new User();

		$user->load($id, 'id');
		
		$this->setNoTranslate('exhibitor_id', $id);
		$this->set('warning', 'Do you really want to delete the exhibitor');
		$this->setNoTranslate('exhibitor', $user);
		$this->set('yes', 'Yes');
		$this->set('no', 'No');
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
			$this->User->set('invoice_email', $_POST['invoice_email']);
			$this->User->set('country', $_POST['country']);
			$this->User->set('phone1', $_POST['phone1']);
			$this->User->set('phone2', $_POST['phone2']);
			$this->User->set('phone3', $_POST['phone3']);
			$this->User->set('fax', $_POST['fax']);
			$this->User->set('website', $_POST['website']);
			$this->User->set('email', $_POST['email']);
			$this->User->set('presentation', $_POST['presentation']);
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

				$pw_arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
				shuffle($pw_arr);
				$password = substr(implode('', $pw_arr), 0, 13);
				
				$this->User->setPassword($password);
				$userId = $this->User->save();

				$hash = md5($this->User->get('email').BASE_URL.$userId);
				$url = BASE_URL.'user/confirm/'.$userId.'/'.$hash;

				if ($fairUrl != '') {
					$fair = new Fair($this->Exhibitor->db);
					$fair->load($fairUrl, 'url');

					$me = new User;
					$me->load($_SESSION['user_id'], 'id');

					$mail = new Mail($_POST['email'], 'new_account');
					$mail->setMailVar('alias', $_POST['alias']);
					$mail->setMailVar('password', $password);
					$mail->setMailVar('accesslevel', $this->translate->{'Exhibitor'});
					$mail->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
					$mail->setMailVar('creator_name', $me->get('name'));
					$mail->send();

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
						$ful->set('connected_time', time());
						$ful->save();
					}
				} else {
					$mail = new Mail($_POST['email'], 'welcome');
					$mail->setMailVar('alias', $_POST['alias']);
					$mail->setMailVar('password', $password);
					$mail->setMailVar('accesslevel', $this->translate->{'Exhibitor'});
					$mail->send();
				}
				$this->set('js_confirm_text', 'The user was created successfully.');

			}

		}

		$this->set('error', $error);
		$this->setNoTranslate('fair_url', $fairUrl);
		$this->setNoTranslate('user', $this->User);
		$fair = new Fair($this->User->db);
		$fair->load($_SESSION['outside_fair_url'], 'url');
		$this->setNoTranslate('fair', $fair);
		
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


	function printProfile($id) {

		setAuthLevel(2);

		$u = new User;
		$u->load($id, 'id');

		// Following will get this exhibitor's bookings on the "current" fair this user is logged in to
		$stmt = $u->db->prepare("SELECT
			`exhibitor`.`id` AS exhibitor_id,
			`exhibitor`.`commodity`,
			`exhibitor`.`arranger_message`,
			`exhibitor`.`booking_time`,
			`exhibitor`.`position`,
			`fair_map_position`.*,
			`fair_map`.`id` AS fair_map_id,
			`fair_map`.`fair`,
			`fair_map`.`name` AS fair_map_name,
			`user`.`company`
			FROM `exhibitor` 
      INNER JOIN `fair_map_position` ON `exhibitor`.`position` = `fair_map_position`.`id` 
      INNER JOIN `fair_map` ON `fair_map_position`.`map` = `fair_map`.`id` 
      INNER JOIN `user` ON `exhibitor`.`user` = `user`.`id` 
      WHERE `exhibitor`.`user` = ? AND `exhibitor`.`fair` = ?");
		$stmt->execute(array($u->get('id'), $_SESSION['user_fair']));

		$stmtPreliminary = $u->db->prepare("SELECT
			`preliminary_booking`.`id` AS exhibitor_id,
			`preliminary_booking`.`commodity`,
			`preliminary_booking`.`booking_time`,
			`preliminary_booking`.`arranger_message`,
			`fair_map_position`.*,
			`fair_map`.`id` AS fair_map_id,
			`fair_map`.`fair`,
			`fair_map`.`name` AS fair_map_name,
			`user`.`company`
			FROM `preliminary_booking` 
      INNER JOIN `fair_map_position` ON `preliminary_booking`.`position` = `fair_map_position`.`id` 
	  INNER JOIN `fair_map` ON `fair_map_position`.`map` = `fair_map`.`id` 
      INNER JOIN `user` ON `preliminary_booking`.`user` = `user`.`id` 
      WHERE `preliminary_booking`.`user` = ? AND `preliminary_booking`.`fair` = ?
		");
		$stmtPreliminary->execute(array($u->get("id"), $_SESSION["user_fair"]));

		$same_fair_positions = array_merge($stmt->fetchAll(PDO::FETCH_ASSOC), $stmtPreliminary->fetchAll(PDO::FETCH_ASSOC));
		$this->setNoTranslate('user', $u);
		$this->setNoTranslate('same_fair_positions', $same_fair_positions);
		$this->set('headline', 'Exhibitor profile');

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
		$this->set('profile_presentation_label', 'Company presentation');
    
		$this->set('contact_section', 'Contact person');
		$this->set('alias_label', 'Alias');
		$this->set('contact_label', 'Contact person');
		$this->set('phone3_label', 'Contact Phone');
		$this->set('phone4_label', 'Contact Phone 2');
		$this->set('contact_email', 'Contact Email');
		$this->set('contact_country', 'Contact Country');

		$this->set('password_label', 'Password');
		$this->set('password_repeat_label', 'Password again (repeat to confirm)');

		$this->set('customer_nr_label', 'Customer number');
		$this->set('customer_id', 'Customer Number');
		$this->set('save_customer_id', 'Save Customer Number');

		$this->set('bookings_section', 'Bookings on your other events');
		$this->set('bookings_samefair_section', 'Bookings for this event');
		$this->set('tr_map', 'Map for this booking');
		$this->set('tr_pos', 'Stand space');
		$this->set('tr_area', 'Area');
		$this->set('tr_booker', 'Booked by');
		$this->set('tr_field', 'Trade');
		$this->set('tr_time', 'Time of booking');
		$this->set('tr_message', 'Message to organizer in list');
		$this->set('ok_label', 'OK');
		$this->set('no_bookings_label', 'This exhibitor has not made any bookings yet.');

		if ($this->is_ajax) {
			$this->setNoTranslate('onlyContent', true);
		}
	}


	function profile($id) {

		setAuthLevel(2);

		$u = new User;
		$u->load($id, 'id');

		
		//Bokningar på dina andra event
		//Masters and exhibitors looking at their own profile get the full list of positions, lower levels get the ones for their fair
		if (userLevel() == 4 || $_SESSION["user_id"] == $id) {
			$stmt = $u->db->prepare("SELECT
				`exhibitor`.`id` AS exhibitor_id,
				`exhibitor`.`commodity`,
				`exhibitor`.`arranger_message`,
				`exhibitor`.`booking_time`,
				`exhibitor`.`position`,
				`fair_map_position`.*,
				`fair_map`.`id` AS fair_map_id,
				`fair_map`.`fair`,
				`fair_map`.`name` AS fair_map_name,
				`fair`.`name` AS fair_name,
				`user`.`company`
				FROM `exhibitor` 
		        INNER JOIN `fair_map_position` ON `exhibitor`.`position` = `fair_map_position`.`id` 
		        INNER JOIN `fair_map` ON `fair_map_position`.`map` = `fair_map`.`id` 
		        INNER JOIN `fair` ON `fair_map`.`fair` = `fair`.`id` 
		        INNER JOIN `user` ON `exhibitor`.`user` = `user`.`id` 
				WHERE `exhibitor`.`user` = ? AND `exhibitor`.`fair` <> ?");
			$stmt->execute(array($u->get('id'), $_SESSION["user_fair"]));

		

			$stmtPreliminary = $u->db->prepare("SELECT
				`preliminary_booking`.`id` AS exhibitor_id,
				`preliminary_booking`.`commodity`,
				`preliminary_booking`.`booking_time`,
				`preliminary_booking`.`arranger_message`,
				`fair_map_position`.*,
				`fair_map`.`id` AS fair_map_id,
				`fair_map`.`fair`,
				`fair_map`.`name` AS fair_map_name,
				`fair`.`name` AS fair_name,
				`user`.`company`
				FROM `preliminary_booking` 
        		INNER JOIN `fair_map_position` ON `preliminary_booking`.`position` = `fair_map_position`.`id` 
				INNER JOIN `fair_map` ON `fair_map_position`.`map` = `fair_map`.`id`
				INNER JOIN `fair` ON `fair_map`.`fair` = `fair`.`id`
        		INNER JOIN `user` ON `preliminary_booking`.`user` = `user`.`id` 
        		WHERE `user` = ? AND `preliminary_booking`.`fair` <> ?
			");
			$stmtPreliminary->execute(array($u->get("id"), $_SESSION["user_fair"]));

		//Administrators and Organizers can see the bookings linked to the events they have administrative privilegies to
		} else if (userLevel() == 2) {
			/*$sql = "SELECT `map_access` FROM fair_user_relation WHERE user = ? AND fair = ?";
			$stmt = $this->db->prepare($sql);
			$stmt->execute(array($_SESSION['user_id'], $fairId));
			$result = $stmt->fetch();
			$this->setNoTranslate('accessible_maps', explode('|', $result['map_access']));
			if ($result) {
				$hasRights = true;
			}*/
			
			$stmt = $u->db->prepare("SELECT
				`exhibitor`.`id` AS exhibitor_id,
				`exhibitor`.`commodity`,
				`exhibitor`.`arranger_message`,
				`exhibitor`.`booking_time`,
				`exhibitor`.`position`,
				`fair_map_position`.*,
				`fair_map`.`id` AS fair_map_id,
				`fair_map`.`fair`,
				`fair_map`.`name` AS fair_map_name,
				`fair`.`name` AS fair_name,
				`user`.`company`
				FROM `exhibitor`
				INNER JOIN `fair_map_position` ON `exhibitor`.`position` = `fair_map_position`.`id`
				INNER JOIN `fair_map` ON `fair_map_position`.`map` = `fair_map`.`id`
				INNER JOIN `user` ON `exhibitor`.`user` = `user`.`id`
				INNER JOIN `fair_user_relation` ON `fair_map`.`fair` = `fair_user_relation`.`fair`
				INNER JOIN `fair` ON `fair_map`.`fair` = `fair`.`id`
				WHERE `exhibitor`.`user` = ? AND `exhibitor`.`fair` <> ? AND `fair_user_relation`.`user` = ?
			");
			$stmt->execute(array($u->get('id'), $_SESSION["user_fair"], $_SESSION['user_id']));

			$stmtPreliminary = $u->db->prepare("SELECT
				`preliminary_booking`.`id` AS exhibitor_id,
				`preliminary_booking`.`commodity`,
				`preliminary_booking`.`booking_time`,
				`preliminary_booking`.`arranger_message`,
				`fair_map_position`.*,
				`fair_map`.`id` AS fair_map_id,
				`fair_map`.`fair`,
				`fair_map`.`name` AS fair_map_name,
				`fair`.`name` AS fair_name,
				`user`.`company`
				FROM `preliminary_booking`
				INNER JOIN `fair_map_position` ON `preliminary_booking`.`position` = `fair_map_position`.`id`
				INNER JOIN `fair_map` ON `preliminary_booking`.`fair` = `fair_map`.`fair`
				INNER JOIN `user` ON `preliminary_booking`.`user` = `user`.`id`
				INNER JOIN `fair_user_relation` ON `fair_map`.`fair` = `fair_user_relation`.`fair`
				INNER JOIN `fair` ON `fair_map`.`fair` = `fair`.`id`
				WHERE `preliminary_booking`.`user` = ? 
				AND `preliminary_booking`.`fair` <> ? 
				AND `fair_user_relation`.`user` = ?
			");
			$stmtPreliminary->execute(array($u->get("id"), $_SESSION["user_fair"], $_SESSION["user_fair"]));
		} else if (userLevel() == 3) {
			$stmt = $u->db->prepare("SELECT
				`exhibitor`.`id` AS exhibitor_id,
				`exhibitor`.`commodity`,
				`exhibitor`.`arranger_message`,
				`exhibitor`.`booking_time`,
				`exhibitor`.`position`,
				`fair_map_position`.*,
				`fair_map`.`id` AS fair_map_id,
				`fair_map`.`fair`,
				`fair_map`.`name` AS fair_map_name,
				`fair`.`name` AS fair_name,
				`user`.`company`
				FROM `exhibitor`
				INNER JOIN `fair_map_position` ON `exhibitor`.`position` = `fair_map_position`.`id`
				INNER JOIN `fair_map` ON `fair_map_position`.`map` = `fair_map`.`id`
				INNER JOIN `user` ON `exhibitor`.`user` = `user`.`id`
				INNER JOIN `fair` ON `fair_map`.`fair` = `fair`.`id`
				WHERE `exhibitor`.`user` = ? 
				AND `exhibitor`.`fair` <> ? 
				AND `fair`.`created_by` = ?
			");
			$stmt->execute(array($u->get('id'), $_SESSION["user_fair"], $_SESSION['user_id']));

			$stmtPreliminary = $u->db->prepare("SELECT
				`preliminary_booking`.`id` AS exhibitor_id,
				`preliminary_booking`.`commodity`,
				`preliminary_booking`.`booking_time`,
				`preliminary_booking`.`arranger_message`,
				`fair_map_position`.*,
				`fair_map`.`id` AS fair_map_id,
				`fair_map`.`fair`,
				`fair_map`.`name` AS fair_map_name,
				`fair`.`name` AS fair_name,
				`user`.`company`
				FROM `preliminary_booking`
				INNER JOIN `fair_map_position` ON `preliminary_booking`.`position` = `fair_map_position`.`id`
				INNER JOIN `fair_map` ON `preliminary_booking`.`fair` = `fair_map`.`fair`
				INNER JOIN `user` ON `preliminary_booking`.`user` = `user`.`id`
				INNER JOIN `fair` ON `fair_map`.`fair` = `fair`.`id`
				WHERE `preliminary_booking`.`user` = ? 
				AND `preliminary_booking`.`fair` <> ?
				AND `fair`.`created_by` = ?
			");
			$stmtPreliminary->execute(array($u->get("id"), $_SESSION["user_fair"], $_SESSION["user_id"]));
		}

		// $positions contains this exhibitor's all bookings
		$positions = array();

		while (($res = $stmtPreliminary->fetch(PDO::FETCH_ASSOC))) {
			$positions[] = $res;
		}

		while (($res = $stmt->fetch(PDO::FETCH_ASSOC))) {
			$positions[] = $res;
		}

		// Following will get this exhibitor's bookings on the "current" fair this user is logged in to
		$stmt = $u->db->prepare("SELECT
			`exhibitor`.`id` AS exhibitor_id,
			`exhibitor`.`commodity`,
			`exhibitor`.`arranger_message`,
			`exhibitor`.`booking_time`,
			`exhibitor`.`position`,
			`fair_map_position`.*,
			`fair_map`.`id` AS fair_map_id,
			`fair_map`.`fair`,
			`fair_map`.`name` AS fair_map_name,
			`fair`.`name` AS fair_name,
			`user`.`company`
			FROM `exhibitor` 
			INNER JOIN `fair_map_position` ON `exhibitor`.`position` = `fair_map_position`.`id` 
			INNER JOIN `fair_map` ON `fair_map_position`.`map` = `fair_map`.`id` 
			INNER JOIN `user` ON `exhibitor`.`user` = `user`.`id` 
			INNER JOIN `fair` ON `fair_map`.`fair` = `fair`.`id`
			WHERE `exhibitor`.`user` = ? 
			AND `exhibitor`.`fair` = ? 
		");
		$stmt->execute(array($u->get('id'), $_SESSION['user_fair']));

		$stmtPreliminary = $u->db->prepare("SELECT
			`preliminary_booking`.`id` AS exhibitor_id,
			`preliminary_booking`.`commodity`,
			`preliminary_booking`.`booking_time`,
			`preliminary_booking`.`arranger_message`,
			`fair_map_position`.*,
			`fair_map`.`id` AS fair_map_id,
			`fair_map`.`fair`,
			`fair_map`.`name` AS fair_map_name,
			`fair`.`name` AS fair_name,
			`user`.`company`
			FROM `preliminary_booking` 
			INNER JOIN `fair_map_position` ON `preliminary_booking`.`position` = `fair_map_position`.`id` 
			INNER JOIN `fair_map` ON `fair_map_position`.`map` = `fair_map`.`id` 
			INNER JOIN `user` ON `preliminary_booking`.`user` = `user`.`id` 
			INNER JOIN `fair` ON `fair_map`.`fair` = `fair`.`id`
			WHERE `preliminary_booking`.`user` = ? 
			AND `preliminary_booking`.`fair` = ? 
		");
		$stmtPreliminary->execute(array($u->get("id"), $_SESSION["user_fair"]));

		$same_fair_positions = array_merge($stmt->fetchAll(PDO::FETCH_ASSOC), $stmtPreliminary->fetchAll(PDO::FETCH_ASSOC));
		$this->setNoTranslate('user', $u);
		$this->setNoTranslate('positions', $positions);
		$this->setNoTranslate('same_fair_positions', $same_fair_positions);
		$this->set('headline', 'Exhibitor profile');

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
		$this->set('profile_presentation_label', 'Company presentation');
    
		$this->set('contact_section', 'Contact person');
		$this->set('alias_label', 'Alias');
		$this->set('contact_label', 'Contact person');
		$this->set('phone3_label', 'Contact Phone');
		$this->set('phone4_label', 'Contact Phone 2');
		$this->set('contact_email', 'Contact Email');
		$this->set('contact_country', 'Contact Country');

		$this->set('password_label', 'Password');
		$this->set('password_repeat_label', 'Password again (repeat to confirm)');

		$this->set('customer_nr_label', 'Customer number');
		$this->set('customer_id', 'Customer Number');
		$this->set('save_customer_id', 'Save Customer Number');

		$this->set('bookings_section', 'Bookings on your other events');
		$this->set('bookings_samefair_section', 'Bookings for this event');
		$this->set('tr_fairname', 'Event name');
		$this->set('tr_pos', 'Stand space');
		$this->set('tr_area', 'Area');
		$this->set('tr_booker', 'Booked by');
		$this->set('tr_field', 'Trade');
		$this->set('tr_time', 'Time of booking');
		$this->set('tr_message', 'Message to organizer in list');
		$this->set('ok_label', 'OK');
		$this->set('no_bookings_label', 'This exhibitor has not made any bookings yet.');

		if ($this->is_ajax) {
			$this->setNoTranslate('onlyContent', true);
		}
	}
	public function reviewPrelBooking() {
		setAuthLevel(1);
		}
	function myBookings() {
		$this->set('mainheadline', 'My bookings');
		$this->set('bheadline', 'My booked stand spaces');
		$this->set('rheadline', 'My reserved stand spaces');
		$this->set('prelheadline', 'Stand spaces I have applied for');
		$this->set('regheadline', 'Pending applications');
		$this->set('expiredheadline', 'Bookings on events that has expired');

		setAuthLevel(1);
		$today = time();
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
			pos.expires, 
			fair.name AS fairname
				FROM user, 
				fair, 
				exhibitor AS ex, 
				fair_map_position AS pos 
					WHERE user.id = ex.user 
					AND fair.id = ex.fair 
					AND ex.position = pos.id 
					AND user.id = ?
					AND pos.status = ?
					ORDER BY ex.booking_time DESC");

		$stmt->execute(array($_SESSION['user_id'], 2));
		$positions_unfinished = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$positions = array();

		foreach ($positions_unfinished as $pos) {
			// Get categories
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

			// Get extra options
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
			pos.expires, 
			fair.name AS fairname
				FROM user, 
				fair, 
				exhibitor AS ex, 
				fair_map_position AS pos 
					WHERE user.id = ex.user 
					AND fair.id = ex.fair 
					AND ex.position = pos.id 
					AND user.id = ?
					AND pos.status = ? 
					AND ex.clone = 0
					ORDER BY ex.booking_time DESC");
		$stmt->execute(array($_SESSION['user_id'], 1));
		$rpositions_unfinished = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$rpositions = array();

		foreach ($rpositions_unfinished as $pos) {
			// Get categories
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

			// Get extra options
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
			$pos['vat'] = $fairInvoice->get('pos_vat');

			$rpositions[$pos['position']] = $pos;
		}

		/* Cloned reservations */
		$stmt = $u->db->prepare("SELECT ex.*, 
			user.id as userid, 
			user.company, 
			ex_li.link AS link, 
			pos.id AS position, 
			pos.name, 
			pos.information, 
			pos.area, 
			pos.map, 
			pos.price, 
			ex.id AS posid, 
			pos.expires, 
			fair.name AS fairname
				FROM user, 
				exhibitor_link AS ex_li, 
				fair, 
				exhibitor AS ex, 
				fair_map_position AS pos 
					WHERE user.id = ex.user 
					AND fair.id = ex.fair 
					AND ex.position = pos.id 
					AND user.id = ?
					AND ex_li.exhibitor = ex.id 
					AND ex_li.status = 1
					AND pos.status = ? 
					AND ex.clone = 1
					ORDER BY ex.booking_time DESC");
		$stmt->execute(array($_SESSION['user_id'], 1));
		$rcpositions_unfinished = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$rcpositions = array();

		foreach ($rcpositions_unfinished as $pos) {
			// Get activation links
/*			$stmt = $u->db->prepare('SELECT * FROM exhibitor_link WHERE exhibitor = ? AND `status` = 1');
			$stmt->execute(array($pos['id']));
			$poslinks = $stmt->fetch(PDO::FETCH_ASSOC);

			$pos['link'] = $poslinks;*/


			// Get categories
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

			// Get extra options
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
			$pos['vat'] = $fairInvoice->get('pos_vat');

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
			pos.map, 
			fair.name AS fairname
				FROM user, 
				fair, 
				exhibitor_history AS ex, 
				fair_map_position AS pos 
					WHERE user.id = ex.user 
					AND fair.id = ex.fair 
					AND ex.position = pos.id 
					AND user.id = ?
					ORDER BY ex.booking_time DESC");
		$stmt->execute(array($_SESSION['user_id']));
		$positions_deleted = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$del_positions = array();

		foreach ($positions_deleted as $pos) {
			// Get categories
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

			// Get extra options
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

			// Get articles and amounts
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
		pos.map, 
		fair.name AS fairname
			FROM user, 
			fair, 
			preliminary_booking_history AS prel, 
			fair_map_position AS pos 
				WHERE user.id = prel.user 
				AND fair.id = prel.fair 
				AND prel.position = pos.id 
				AND user.id = ?
				ORDER BY prel.booking_time DESC");
	$stmt->execute(array($_SESSION['user_id']));
	$prel_del = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$del_prelpos = array();

	foreach ($prel_del as $pos) {
		// Get categories
		$poscats = explode('|', $pos['categories']);

		$categories = array();
		if (count($poscats) > 0) {
			foreach ($poscats as $cat) {
				$ex_category = new ExhibitorCategory();
				$ex_category->load($cat, 'id');
				$categories[] = $ex_category->get('name');					
			}
		}


		// Get extra options
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

		// Get articles and amounts
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
		fair.name AS fairname
			FROM user, 
			fair, 
			preliminary_booking AS prel, 
			fair_map_position AS pos
				WHERE user.id = prel.user 
				AND fair.id = prel.fair
				AND prel.position = pos.id 
				AND user.id = ?
				ORDER BY prel.booking_time DESC");
	$stmt->execute(array($_SESSION['user_id']));
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$prelpos = array();

	foreach ($result as $pos) {
		// Get categories
		$poscats = explode('|', $pos['categories']);

		$categories = array();
		if (count($poscats) > 0) {
			foreach ($poscats as $cat) {
				$ex_category = new ExhibitorCategory();
				$ex_category->load($cat, 'id');
				$categories[] = $ex_category->get('name');					
			}
		}


		// Get extra options
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

		// Get articles and amounts
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

		$prelpos[$pos['position']] = $pos;
	}
/*
		// Inactive Preliminary bookings ( this function is inactive because exhibitors are easily confused about what inactive preliminary bookings actually mean)
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
			preliminary_booking AS prel, 
			fair_map_position AS pos 
				WHERE user.id = prel.user 
				AND prel.position = pos.id 
				AND pos.status <> 0 
				AND user.id = ?
				ORDER BY prel.booking_time DESC");
	$stmt->execute(array($_SESSION['user_id']));
	$prel_inactive = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$iprelpos = array();

	foreach ($prel_inactive as $pos) {
		//Get categories
		$poscats = explode('|', $pos['categories']);

		$categories = array();
		if (count($poscats) > 0) {
			foreach ($poscats as $cat) {
				$ex_category = new ExhibitorCategory();
				$ex_category->load($cat, 'id');
				$categories[] = $ex_category->get('name');					
			}
		}


		// Get extra options
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

		// Get articles and amounts 
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

		$iprelpos[$pos['position']] = $pos;
	}
*/
		// Fair registrations
		$stmt_fregistrations = $this->db->prepare("SELECT fa.*,
			 u.id as userid, 
			 u.company as company, 
			 fair.name as fairname 
				 FROM fair_registration AS fa 
					 LEFT JOIN user AS u ON u.id = fa.user 
					 LEFT JOIN fair ON fair.id = fa.fair 
						 WHERE fa.user = ? 
						 ORDER BY fa.booking_time ASC ");
		$stmt_fregistrations->execute(array($_SESSION['user_id']));
		$result = $stmt_fregistrations->fetchAll(PDO::FETCH_ASSOC);
		$fair_registrations = array();

		foreach ($result as $pos) {
			// Get categories
			$poscats = explode('|', $pos['categories']);

			$categories = array();
			if (count($poscats) > 0) {
				foreach ($poscats as $cat) {
					$ex_category = new ExhibitorCategory();
					$ex_category->load($cat, 'id');
					$categories[] = $ex_category->get('name');					
				}
			}


			// Get extra options
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

			// Get articles and amounts
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


		// Deleted fair registrations
		$stmt_fdregistrations = $this->db->prepare("SELECT fa.*, 
			u.id as userid, 
			u.company as company, 
			fair.name as fairname 
				FROM fair_registration_history AS fa 
					LEFT JOIN user AS u ON u.id = fa.user 
					LEFT JOIN fair ON fair.id = fa.fair 
						WHERE fa.user = ? 
						ORDER BY fa.booking_time ASC ");
		$stmt_fdregistrations->execute(array($_SESSION['user_id']));
		$result = $stmt_fdregistrations->fetchAll(PDO::FETCH_ASSOC);
		$fair_registrations_deleted = array();

		foreach ($result as $pos) {
			// Get categories
			$poscats = explode('|', $pos['categories']);

			$categories = array();
			if (count($poscats) > 0) {
				foreach ($poscats as $cat) {
					$ex_category = new ExhibitorCategory();
					$ex_category->load($cat, 'id');
					$categories[] = $ex_category->get('name');					
				}
			}


			// Get extra options
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

			// Get articles and amounts
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
//		$this->setNoTranslate('iprelpos', $iprelpos);
		$this->setNoTranslate('fair_registrations', $fair_registrations);
		$this->setNoTranslate('del_positions', $del_positions);
		$this->setNoTranslate('del_prelpos', $del_prelpos);
		$this->setNoTranslate('fair_registrations_deleted', $fair_registrations_deleted);

		$this->set('booked_notfound', 'No payed booths was found.');
		$this->set('reserv_notfound', 'No reservations was found.');
		$this->set('prel_notfound', 'No applied stand spaces was found.');
		$this->set('reserv_cloned_notfound', 'No reservations to accept was found.');
		$this->set('del_prel_notfound', 'No deleted applications for stand spaces was found.');
		$this->set('search', 'Search');
		$this->set('bheadline', 'Payed stand spaces');
		$this->set('rheadline', 'Reservations, not payed');
		$this->set('rcheadline', 'Reservations that I am to accept');
		$this->set('prel_table', 'Stand spaces that I have applied for');
//		$this->set('prel_table_inactive', 'Preliminary bookings (inactive)');
		$this->set('prel_table_deleted', 'Deleted/denied stand space applications');
		$this->set('fair_registrations_headline', 'Events that I have applied for');
		$this->set('fregistrations_notfound', 'No event applications was found.');
		$this->set('fair_registrations_deleted_headline', 'Deleted/denied event applications');
		$this->set('fregistrations_deleted_notfound', 'No deleted event applications was found.');
		$this->set('more_info', 'Click for more info');
		$this->set('tr_fair', 'Fair');
		$this->set('tr_map', 'Map');
		$this->set('tr_hidden', 'Name hidden');
		$this->set('tr_pos', 'Stand space');
		$this->set('tr_area', 'Area');
		$this->set('tr_booker', 'Booked by');
		$this->set('tr_field', 'Trade');
		$this->set('tr_time', 'Time of booking');
		$this->set('tr_last_edited', 'Last edited');
		$this->set('tr_reserved_until', 'Reserved until');
		$this->set('tr_message', 'Message to organizer in list');
		$this->set('tr_view', 'View on map');
		$this->set('tr_created', 'Created');
		$this->set('tr_sent', 'Sent');
		$this->set('tr_credited', 'Krediterad');
		$this->set('tr_invoicestatus', 'Invoice');
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
		$this->set('tr_alternatives', 'Alternatives');
		$this->set('tr_deny_reservation', 'Deny space');
		$this->set('tr_confirm_reservation', 'Confirm space');
		$this->set('never_edited_label', 'Never edited');
		$this->set('confirm_cancel_prel', 'This will remove your application for the stand space');
		$this->set('confirm_cancel_reg', 'This will remove your application for the event');
		$this->set('export', 'Export to Excel');
		$this->set('col_export_err', 'Select at least one column in order to export!');
		$this->set('row_export_err', 'Select at least one row in order to export!');
		$this->set('ok_label', 'OK');
		$this->set('confirm_delete', 'Are you sure?');
	}


	public function exportBookingPDF($id, $dt) {
		require_once ROOT.'lib/tcpdf/tcpdf.php';

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

				$exhibitor = new Exhibitor();
				$exhibitor->load($id, 'id');
				$exId = $exhibitor->get('id');

				$pos = new FairMapPosition();
				$pos->load($exhibitor->get('position'), 'id');

				$fair = new Fair();
				$fair->load($exhibitor->get('fair'), 'id');

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
				$sender_billing_bank_no = $fairInvoice->get('bank_no');
				$sender_billing_postgiro = $fairInvoice->get('postgiro');
				$sender_billing_vat_no = $fairInvoice->get('vat_no');
				$sender_billing_iban_no = $fairInvoice->get('iban_no');
				$sender_billing_swift_no = $fairInvoice->get('swift_no');
				$sender_billing_swish_no = $fairInvoice->get('swish_no');
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


				$invoice_for_label = $this->translate->{'Invoice for'};
				$printdate_label = $this->translate->{'Print date'};
				$required_at_payment_label = $this->translate->{'must be stated at payment'};
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
				$email_label = $this->translate->{'Email'};
				$amount_label = $this->translate->{'Quantity'};
				$booked_space_label = $this->translate->{'Booked stand'};
				$options_label = $this->translate->{'Options'};
				$articles_label = $this->translate->{'Articles'};
				$tax_label = $this->translate->{'Tax'};
				$parttotal_label = $this->translate->{'Subtotal'};
				$net_label = $this->translate->{'Net'};
				$rounding_label = $this->translate->{'Rounding'};
				$invoice_label = $this->translate->{'Invoice'};
				$to_pay_label = $this->translate->{'to pay:'};
				$address_label = $this->translate->{'Address'};
				$organization_label = $this->translate->{'Organization'};
				$payment_info_label = $this->translate->{'Payment information'};
				$s_reference_label = $this->translate->{'Our reference'};
				$r_reference_label = $this->translate->{'Your reference'};
				$invoice_no_label = $this->translate->{'Invoice number'};
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
		//		$fairInvoiceExpDate = date('Y-m-d');


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
				$position_information = $pos->get('information');
				$position_price = $pos->get('price');
				$position_vat = $fairInvoice->get('pos_vat');
				$exhibitor_options = $exhibitor->get('exhibitor_options');
				$exhibitor_categories = $exhibitor->get('exhibitor_categories');
				$exhibitor_articles = $exhibitor->get('exhibitor_articles');
				$exhibitor_articles_amount = $exhibitor->get('exhibitor_articles_amount');
				$exhibitor_company_name = $user->get('company');
				$exhibitor_name = $user->get('name');
				$date = date('Y-m-d');
				$now = time();
				$expirationdate = date('Y-m-d', $dt);



				$stmt_invoiceid1 = $this->db->prepare("SELECT id FROM exhibitor_invoice as id WHERE fair = ? order by id desc limit 1");
				$stmt_invoiceid1->execute(array($fairId));
				$res = $stmt_invoiceid1->fetch(PDO::FETCH_ASSOC);
				$invoice_id1 = $res['id'];
				$stmt_invoiceid2 = $this->db->prepare("SELECT id FROM exhibitor_invoice_history as id WHERE fair = ? order by id desc limit 1");
				$stmt_invoiceid2->execute(array($fairId));
				$res2 = $stmt_invoiceid2->fetch(PDO::FETCH_ASSOC);
				$invoice_id_history = $res2['id'];

				if ($invoice_id1 > $invoice_id_history) {
					$invoice_id = $invoice_id1;
				} else if ($invoice_id1 < $invoice_id_history) {
					$invoice_id = $invoice_id_history;
				} else {
					$invoice_id = null;
				}

/******************************************************************************/
/******************************************************************************/
/*****************     FIND OUT WHAT INVOICE ID TO USE        *****************/
/******************************************************************************/
/******************************************************************************/
				if (is_null($invoice_id)) {
					$stmt_invoiceid2 = $this->db->prepare("SELECT invoice_id_start as id FROM fair_invoice WHERE fair = ?");
					$stmt_invoiceid2->execute(array($fairId));
					$res = $stmt_invoiceid2->fetch();
					$invoice_id = $res['id'];

					// uppdatera exhibitor tabellen
/*
					$sql = "INSERT INTO exhibitor_invoice (id, ex_user, fair, created, author, exhibitor, expires, r_name, r_address, r_zipcode, r_city, r_country, s_name, s_address, s_zipcode, s_city, s_country, s_website, s_phone, orgnr, bank_no, postgiro, vat_no, iban_no, swift_no) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
					$params = array();
					
					$stmt_invoice = $this->db->prepare("INSERT INTO exhibitor_invoice as id WHERE fair = ? order by id desc limit 1");
					$stmt_invoice->execute(array($fairId));
					$res = $stmt_invoice->fetch(PDO::FETCH_ASSOC);
					$invoice_id = $res['id'];
					*/
					if (is_null($invoice_id)){
						$invoice_id += 1;
					}
				} else {

					$invoice_id += 1;

				}
			
				// Insert the invoice data to database
				$stmt_invoice = $this->db->prepare("INSERT INTO exhibitor_invoice (id, ex_user, fair, created, author, exhibitor, expires, r_reference, r_name, r_address, r_zipcode, r_city, r_country, s_reference, s_name, s_address, s_zipcode, s_city, s_country, s_website, s_phone, s_email, orgnr, bank_no, postgiro, vat_no, iban_no, swift_no, swish_no, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
				$stmt_invoice->execute(array($invoice_id, $userId, $fairId, $now, $author, $id, $expirationdate, $exhibitor_name, $rec_billing_company_name, $rec_billing_address, $rec_billing_zipcode, $rec_billing_city, $rec_billing_country, $sender_billing_reference, $sender_billing_company_name, $sender_billing_address, $sender_billing_zipcode, $sender_billing_city, $sender_billing_country, $sender_billing_website, $sender_billing_phone, $sender_billing_email, $sender_billing_orgnr, $sender_billing_bank_no, $sender_billing_postgiro, $sender_billing_vat_no, $sender_billing_iban_no, $sender_billing_swift_no, $sender_billing_swish_no));


/*********************************************************************************************/
/*********************************************************************************************/
/*****************     SORT CATEGORIES, OPTIONS AND ARTICLES IN ARRAYS        ****************/
/*********************************************************************************************/
/*********************************************************************************************/


				$categoryNames = array();

				if (isset($exhibitor_category) && is_array($exhibitor_category)) {
					foreach ($exhibitor_category as $cat) {
						$category = new ExhibitorCategory();
						$category->load($cat, "id");
						if ($category->wasLoaded()) {
							$categoryNames[] = $category->get("name");
						}
					}
				}
				
	
				$options = array();

				if (!empty($exhibitor_options) && is_array($exhibitor_options)) {
					foreach ($exhibitor_options as $opt) {								
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

				if (!empty($exhibitor_articles) && is_array($exhibitor_articles)) {
					foreach ($exhibitor_articles as $art) {								
						$arts = new FairArticle();
						$arts->load($art, 'id');
						if ($arts->wasLoaded()) {
							$art_id[] = $arts->get('custom_id');
							$art_text[] = $arts->get('text');
							$art_price[] = $arts->get('price');
							$art_vat[] = $arts->get('vat');
						}								
					}
					$articles = array($art_id, $art_text, $art_price, $exhibitor_articles_amount, $art_vat);
				}

				$logo_name = array();
				foreach(glob(ROOT.'public/images/fairs/'.$fairId.'/logotype/*') as $filename) {
					$logo_name[] = (basename($filename) . "\n");
				}

				if (!$logo_name) {
					$logo_name = BASE_URL.'/images/fairs/cfslogo.png';
				} else {
					$logo_name = BASE_URL.'/images/fairs/'. $fairId . '/logotype/' . $logo_name[0];
				}

//die(var_dump($options));
//die(implode($exhibitor_articles, ', '));
		// set document information

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
					<td style="width:345px;">
						<img style="height:70px;" src="'.$logo_name. '"/>
					</td>
					<td>
						<br/><br/><b style="font-size:23px; text-alight:right;">' . $invoice_label . ' ' . $invoice_id . '</b><br>'.$printdate_label.': ' . $date . '
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
				<tr>
					<td style="width:200px;"></td>
					<td style="width:200px;"></td>
					<td style="width:200px;">' . $swish_label . ' &nbsp;' . $sender_billing_swish_no . '</td>
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
tr .normal3 {
	width:160px;
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
			<td class="normal3"><b>'.$s_reference_label.':</b></td>
			<td class="normal">' . $sender_billing_reference . '</td>
			<td class="short"></td>
			<td class="normal2">' . $rec_billing_company_name . '</td>
		</tr>
		<tr class="normal">
			<td class="normal3"><b>'.$r_reference_label.':</b></td>
			<td class="normal">' . $exhibitor_name . '</td>
			<td class="short"></td>
			<td class="normal2">' . $rec_billing_address . '</td>
		</tr>
		<tr class="normal">
			<td class="normal3"><b>'.$invoice_no_label.':</b></td>
			<td class="normal">' . $invoice_id . '</td>
			<td class="short"></td>
			<td class="normal2">' . $rec_billing_zipcode . ' ' . $rec_billing_city .'</td>
		</tr>
		<tr class="normal">
			<td class="normal3"><sup>('.$required_at_payment_label.')</sup></td>
			<td class="normal"></td>
			<td class="short"></td>
			<td class="normal2">' . $rec_billing_country . '</td>
		</tr>
		<tr class="normal">
			<td class="normal3"><b>' . $invoice_date_label . ':</b></td>
			<td class="normal">' . $date . '</td>
			<td class="short"></td>
			<td class="normal2"></td>
			
		</tr>
		<tr class="normal">
			<td class="normal3"><b>'.$invoice_expirationdate_label.':</b></td>
			<td class="normal"><b>' . $expirationdate . '</b></td>
			<td class="short"></td>
			<td class="normal2"></td>
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
	// Insert the invoice space data to database
	$stmt_invoice_rel1 = $this->db->prepare("INSERT INTO exhibitor_invoice_rel (invoice, fair, text, price, amount, vat, type, information) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
	$stmt_invoice_rel1->execute(array($invoice_id, $fairId, $position_name, $position_price, 1, $position_vat, 'space', $position_information));

if (!empty($exhibitor_options) && is_array($exhibitor_options)) {
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

if (!empty($exhibitor_articles) && is_array($exhibitor_articles)) {
	
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


if (!empty($exhibitor_options) && is_array($exhibitor_options)) {
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

if (!empty($exhibitor_articles) && is_array($exhibitor_articles)) {
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

if (!file_exists(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$id)) {	
	mkdir(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$id);
	chmod(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$id, 0775);
}

$rec_billing_company_name = str_replace('/', '-', $rec_billing_company_name);
//Close and output PDF document
$pdf->Output(ROOT.'public/invoices/fairs/'.$fairId.'/exhibitors/'.$id.'/'.$rec_billing_company_name . '-' . $position_name . '-' . $invoice_id . '.pdf', 'F');

header('Location: '.BASE_URL.'invoices/fairs/'.$fairId.'/exhibitors/'.$id.'/'.$rec_billing_company_name . '-' . $position_name . '-' . $invoice_id . '.pdf');
//============================================================+
// END OF FILE
//============================================================+

}


	
	function deleteAccount($user_id) {
		setAuthLevel(4);
		$user = new User;
		$user->load($user_id, 'id');
		if ($user->wasLoaded()) {
			// Avboka plats för utställare.
			$stmt = $user->db->prepare("SELECT position, id FROM exhibitor WHERE user = ?");
			$stmt->execute(array($user->get('id')));
			foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $res) {
				$upd = $user->db->prepare("UPDATE fair_map_position SET status = ? WHERE id = ?");
				$upd->execute(array(0, $res['position']));

				// Ta bort alla kommentarer om uställaren
				$statement = $user->db->prepare("DELETE FROM comment WHERE exhibitorId = ?");
				$statement->execute(array($res['id']));

				// Ta bort alla avbokade bokningar som är relaterade till utställaren
				$statement = $user->db->prepare("DELETE FROM exhibitor_cancelled WHERE exhibitorId = ?");
				$statement->execute(array($res['id']));

				// Ta bort alla kategorirelationer som är relaterade till utställaren
				$statement = $user->db->prepare("DELETE FROM exhibitor_category_rel WHERE exhibitor = ?");
				$statement->execute(array($res['id']));
			}

			// Ta bort exhibitor som är relaterad till user
			$del = $user->db->prepare("DELETE FROM exhibitor WHERE user = ?");
			$del->execute(array($user->get('id')));


			// Ta bort preliminära bokningar som är förknippade till user
			$del = $user->db->prepare("DELETE FROM preliminary_bookings WHERE user = ?");
			$del->execute(array($user->get('id')));

			// Ta bort användarens relationer till mässorna.
			$del = $user->db->prepare("DELETE FROM fair_user_relation WHERE user = ?");
			$del->execute(array($user->get('id')));

			// Ta bort användaren
			$user->delete();
			header('Location: '.BASE_URL.'exhibitor/all');
			exit;
		}
	}

	function saveCustomerId($id, $customerId){
		setAuthLevel(3);
		$this->setNoTranslate('noView', true);
		$user = new User;
		$user->load($id, 'id');


		if($user->wasLoaded()):
			$statement = $user->db->prepare('SELECT customer_nr FROM user WHERE customer_nr = ?');
			$statement->execute(array($customerId));
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);
			if(count($result) < 1):
				$user->set('customer_nr', $customerId);
				$user->save();
				echo $this->translate->{'Successfully saved customer number...'};
			else:
					echo $this->translate->{'Customer number already exists...'};
			endif;
		else:
			echo $this->translate->{'Could not load user with ID'}.": ".$id;
		endif;
	}

	function pre_delete($id, $user_id, $position){
		setAuthLevel(1);
		$this->Exhibitor->del_pre_booking($id, $user_id, $position);
		header('Location: '.BASE_URL.'exhibitor/myBookings');
	}

	function registration_delete($id) {
		setAuthLevel(1);
		$fair_registration = new FairRegistration();
		$fair_registration->load($id, 'id');
		if ($fair_registration->wasLoaded() && $fair_registration->get('user') == $_SESSION['user_id']) {
			$fair_registration->delete();
		}

		header('Location: ' . BASE_URL . 'exhibitor/myBookings');
		die();
	}

	function delete($id, $user_id, $position){
		setAuthLevel(2);
		$this->Exhibitor->del_booking($id, $user_id, $position);
		header('Location: '.BASE_URL.'exhibitor/myBookings');
	}

	function confirmReservation($type) {
		$this->setNoTranslate('type', $type);
		if ($type == 'accept') {
			$this->set('accepted', 'You have accepted the reservation for the space');
			$this->setNoTranslate('fairname', $_SESSION['fairname']);
			$this->setNoTranslate('position', $_SESSION['position_name']);
		} else if ($type == 'deny') {
			$this->set('denied', 'You have denied the reservation for the space');
			$this->setNoTranslate('fairname', $_SESSION['fairname']);
			$this->setNoTranslate('position', $_SESSION['position_name']);
		} else if ($type == 'linkused') {
			$this->set('linkused', 'The link is already used.');
		} else {
			$this->set('error', 'The reservation was not found.');
		}
	}

	function verifyReservation($exid, $hash, $type){

		$ex = new Exhibitor();
		$ex->load($exid, 'id');

		$exlink = new ExhibitorLink();
		$exlink->load2($exid, 'exhibitor');

		$pos = new FairMapPosition();
		$pos->load2($ex->get('position'), 'id');

		$fair = new Fair();
		$fair->loadself($ex->get('fair'), 'id');

		$linkstatus = $exlink->get('status');

		if ($ex->wasLoaded()) {
			if ($linkstatus == 1) {
				if ($type == 'accept') {
					$this->Exhibitor->verify_reservation($exid, $hash, $type);
					header('Location: '.BASE_URL.'exhibitor/confirmReservation/accept');
					$_SESSION['fairname'] = $fair->get('name');
					$_SESSION['position_name'] = $pos->get('name');
				} else if ($type == 'deny') {
					$this->Exhibitor->verify_reservation($exid, $hash, $type);
					header('Location: '.BASE_URL.'exhibitor/confirmReservation/deny');
					$_SESSION['fairname'] = $fair->get('name');
					$_SESSION['position_name'] = $pos->get('name');
				} else {
					header('Location: '.BASE_URL.'exhibitor/confirmReservation/error');
				}
			} else {
				header('Location: '.BASE_URL.'exhibitor/confirmReservation/linkused');
			}
		} else {
			header('Location: '.BASE_URL.'exhibitor/confirmReservation/error');
		}
	}
}
?>
