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
		$this->set('th_phone', 'Phone 1');
		$this->set('th_fairs', 'Fairs');
		$this->set('th_bookings', 'Bookings');
		$this->set('th_last_login', 'Last login');
		$this->set('th_created', 'Created');
		$this->set('th_edit', 'Edit');
		$this->set('th_delete', 'Delete');
		$this->set('th_resend', 'Reset');
		$this->setNoTranslate('fairs', $fairs);
		//$this->set('', $fairs);
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
		$this->set('th_name', 'Name');
		$this->set('th_fairs', 'Fairs');
		$this->set('th_bookings', 'Bookings');
		$this->set('th_last_login', 'Last login');
		$this->set('th_connect_time', 'Connected to fair on');
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
		$this->set('th_phone', 'Phone number');
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
				$stmt = $this->db->prepare("SELECT exhibitor.*, user.*, COUNT(user.id) AS ex_count, (SELECT COUNT(*) FROM fair_user_relation WHERE user = exhibitor.user) AS fair_count FROM user, exhibitor WHERE user.id = exhibitor.user AND user.level = 1 AND exhibitor.fair = ? AND exhibitor.user IN (" . implode(',', $_POST['rows']) . ") GROUP BY user.id ORDER BY fair, user.company");
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
				'ex_count' => $this->translate->{'Bookings'},
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

	function profile($id) {

		setAuthLevel(2);

		$u = new User;
		$u->load($id, 'id');

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
				`user`.`company`
				FROM `exhibitor` INNER JOIN `fair_map_position` ON `exhibitor`.`position` = `fair_map_position`.`id` INNER JOIN `fair_map` ON `fair_map_position`.`map` = `fair_map`.`id` INNER JOIN `user` ON `exhibitor`.`user` = `user`.`id` WHERE `exhibitor`.`user` = ?");
			$stmt->execute(array($u->get('id')));

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
				FROM `preliminary_booking` INNER JOIN `fair_map_position` ON `preliminary_booking`.`position` = `fair_map_position`.`id` INNER JOIN `fair_map` ON `preliminary_booking`.`fair` = `fair_map`.`fair` INNER JOIN `user` ON `preliminary_booking`.`user` = `user`.`id` WHERE `user` = ?
			");
			$stmtPreliminary->execute(array($u->get("id")));

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
				`user`.`company`
				FROM `exhibitor`
				INNER JOIN `fair_map_position` ON `exhibitor`.`position` = `fair_map_position`.`id`
				INNER JOIN `fair_map` ON `fair_map_position`.`map` = `fair_map`.`id`
				INNER JOIN `user` ON `exhibitor`.`user` = `user`.`id`
				INNER JOIN `fair_user_relation` ON `fair_map`.`fair` = `fair_user_relation`.`fair`
				WHERE `exhibitor`.`user` = ? AND `fair_user_relation`.`user` = ?
			");
			$stmt->execute(array($u->get('id'), $_SESSION['user_id']));

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
				INNER JOIN `fair_map` ON `preliminary_booking`.`fair` = `fair_map`.`fair`
				INNER JOIN `user` ON `preliminary_booking`.`user` = `user`.`id`
				INNER JOIN `fair_user_relation` ON `fair_map`.`fair` = `fair_user_relation`.`fair`
				WHERE `preliminary_booking`.`user` = ? AND `fair_user_relation`.`user` = ?
			");
			$stmtPreliminary->execute(array($u->get("id"), $_SESSION["user_fair"]));
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
				`user`.`company`
				FROM `exhibitor`
				INNER JOIN `fair_map_position` ON `exhibitor`.`position` = `fair_map_position`.`id`
				INNER JOIN `fair_map` ON `fair_map_position`.`map` = `fair_map`.`id`
				INNER JOIN `user` ON `exhibitor`.`user` = `user`.`id`
				INNER JOIN `fair` ON `fair_map`.`fair` = `fair`.`id`
				WHERE `exhibitor`.`user` = ? AND `fair`.`created_by` = ?
			");
			$stmt->execute(array($u->get('id'), $_SESSION['user_id']));

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
				INNER JOIN `fair_map` ON `preliminary_booking`.`fair` = `fair_map`.`fair`
				INNER JOIN `user` ON `preliminary_booking`.`user` = `user`.`id`
				INNER JOIN `fair` ON `fair_map`.`fair` = `fair`.`id`
				WHERE `preliminary_booking`.`user` = ? AND `fair`.`created_by` = ?
			");
			$stmtPreliminary->execute(array($u->get("id"), $_SESSION["user_fair"]));
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
			`user`.`company`
			FROM `exhibitor` INNER JOIN `fair_map_position` ON `exhibitor`.`position` = `fair_map_position`.`id` INNER JOIN `fair_map` ON `fair_map_position`.`map` = `fair_map`.`id` INNER JOIN `user` ON `exhibitor`.`user` = `user`.`id` WHERE `exhibitor`.`user` = ? AND `exhibitor`.`fair` = ?");
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
			FROM `preliminary_booking` INNER JOIN `fair_map_position` ON `preliminary_booking`.`position` = `fair_map_position`.`id` INNER JOIN `fair_map` ON `preliminary_booking`.`fair` = `fair_map`.`fair` INNER JOIN `user` ON `preliminary_booking`.`user` = `user`.`id` WHERE `preliminary_booking`.`user` = ? AND `preliminary_booking`.`fair` = ?
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
		$this->set('tr_event', 'Fair');
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

	function myBookings() {
		$this->set('headline', 'My bookings');
		$this->set('rheadline', 'Reservations');

		setAuthLevel(1);

		$u = new User;
		$u->load($_SESSION['user_id'], 'id');

		$stmt = $u->db->prepare("SELECT exhibitor.position FROM exhibitor LEFT JOIN fair_map_position AS pos ON exhibitor.position = pos.id WHERE exhibitor.user = ? AND pos.status = ?");
		$stmt->execute(array($_SESSION['user_id'], 2));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$positions = array();

		foreach ($result as $res) {
			$pos = new FairMapPosition;
			$pos->load($res['position'], 'id');
			$positions[] = $pos;
		}
		
		$stmt = $u->db->prepare("SELECT exhibitor.position FROM exhibitor LEFT JOIN fair_map_position AS pos ON exhibitor.position = pos.id WHERE exhibitor.user = ? AND pos.status = ?");
		$stmt->execute(array($_SESSION['user_id'], 1));
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
			$pos->set('preliminary_id', $prel['id']);
			$prelpos[] = $pos;
		}

		$this->setNoTranslate('positions', $positions);
		$this->setNoTranslate('rpositions', $rpositions);
		$this->setNoTranslate('prelpos', $prelpos);

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
		$this->set('tr_reserved_until', 'Reserved until');
		$this->set('tr_message', 'Message to organizer');
		$this->set('tr_view', 'View');
		$this->set('tr_delete', 'Delete');

		$this->set('confirm_delete', 'Are you sure?');
		$this->set('ok_label', 'OK');
	}

	function edit($fair, $id) {
		setAuthLevel(1);
		$this->set('headline', 'Edit exhibitor');
		$this->set('cat_label', 'Category');
		$this->set('save_label', 'Save');
		$this->setNoTranslate('fairId', $fair);
		$this->setNoTranslate('userId', $id);

		if (isset($_POST['save'])) {
			$stmt = $this->Exhibitor->db->prepare("UPDATE exhibitor SET category = ? WHERE id = ?");
			$stmt->execute(array($_POST['category'], $id));
		}

		$u = new Exhibitor();
		$u->load($id, 'id');

		$opts = makeOptions($this->Exhibitor->db, 'exhibitor_category', $u->get('category'), 'fair='.$fair);
		$this->setNoTranslate('cat_options', $opts);


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

	function delete($id, $user_id, $position){
		setAuthLevel(2);
		$this->Exhibitor->del_booking($id, $user_id, $position);
		header('Location: '.BASE_URL.'exhibitor/myBookings');
	}
}
?>
