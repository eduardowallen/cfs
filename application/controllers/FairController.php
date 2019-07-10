<?php

class FairController extends Controller {

	public function index() {
		
	}

	public function search() {
		

		$stmt_open_fairs = $this->db->prepare("SELECT * FROM fair AS f
				WHERE f.approved = 1
				AND f.hidden_search = 0
				AND f.event_stop > UNIX_TIMESTAMP()
				GROUP BY f.id
				ORDER BY f.name
		");
		$stmt_open_fairs->execute();

		$fairs = $stmt_open_fairs->fetchAll(PDO::FETCH_CLASS);

		$this->setNoTranslate('fairs', $fairs);
		$this->set('label_headline', 'Eventsearch');
		$this->set('label_fairname', 'Fair name');
		$this->set('label_closing_date', 'Closing date');
		$this->set('label_homepage', 'Homepage');
		$this->set('label_bookings', 'Bookings');
		$this->set('label_registration', 'Registration');
		$this->set('label_go_to_event', 'Go to event');
		$this->set('label_hidden', 'Event hidden');
		$this->set('label_open', 'Event open');
		$this->set('label_no_fairs', 'Found no fairs.');
	}

	function overview($param='') {
		setAuthLevel(3);
		$this->setNoTranslate('param', $param);
		$this->set('headline', 'Fair overview');
		$this->set('create_link', 'Create new fair');
		$this->set('th_created', 'Created');
		$this->set('th_closed', 'Closed');
		$this->set('th_event_start', 'Opening time');
		$this->set('th_event_stop', 'Closing time');
		$this->set('th_created', 'Created');
		$this->set('th_total', 'Stand spaces');
		$this->set('th_booked', 'Booked spots');
		$this->set('th_reserved', 'Reserved');
		$this->set('th_available', 'Available spots');
		$this->set('th_arranger_name', 'Organizer');
		$this->set('th_fair', 'Name');
		$this->set('th_organizer', 'Organizer');
		$this->set('th_approved', 'Approved');
		$this->set('th_maps', 'Maps');
		$this->set('th_rules', 'Rules');
		$this->set('th_page_views', 'Page views');
		$this->set('th_categories', 'Categories');
		$this->set('th_extraOptions', 'Options');
		$this->set('th_articles', 'Articles');
		$this->set('th_admins', 'Administrators');
		$this->set('th_exhibitors', 'Exhibitors');
		$this->set('th_settings', 'Settings');
		$this->set('th_RDsettings', 'RainDance settings');
		$this->set('th_mailSettings', 'Mail settings');
		$this->set('th_modules', 'Modules');
		$this->set('th_invoiceSettings', 'Invoice settings');
		$this->set('th_delete', 'Delete');
		$this->set('th_clone', 'Clone');
		$this->set('th_lock', 'Lock');
		$this->set('lock_event_title', 'Lock event');
		$this->set('lock_event_content', 'Are you sure to lock the event');
		$this->set('lock_event_explain', 'The event will not be editable while locked and is only unlockable by the Chartbooker support team.');
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

		$sql = "SELECT f.id, f.name, f.windowtitle, approved, modules, creation_time, page_views, f.event_start, f.event_stop, f.created_by,
				COUNT(fmap.id) AS maps_cnt,
				u.company AS arranger_name,
				(SELECT COUNT(*) FROM fair_map_position AS fmp WHERE fmp.map = fmap.id AND status = 2) AS booked_cnt,
				(SELECT COUNT(*) FROM fair_map_position AS fmp WHERE fmp.map = fmap.id AND status = 1) AS reserved_cnt,
				(SELECT COUNT(*) FROM fair_map_position AS fmp WHERE fmp.map = fmap.id) AS total_cnt
				FROM fair AS f
				LEFT JOIN fair_map AS fmap ON fmap.fair = f.id
				LEFT JOIN user AS u ON f.created_by = u.id";

		switch (userLevel()) {
			case 4:
				$params = array();

				if ($param == 'new') {
					$sql .= " WHERE approved = ?";
					array_push($params, '0');
				} else if ((int) $param > 0) {
					$sql .= " WHERE f.created_by = ?";
					array_push($params, $param);
				}

				break;

			case 3:
				$sql .= " WHERE f.created_by = ?";
				$params = array($_SESSION['user_id']);
				break;

			default:
				toLogin();
				break;
		}

		$sql .= " GROUP BY f.id ORDER BY approved, name";

		$stmt = $this->Fair->db->prepare($sql);
		$stmt->execute($params);
		$fairs = $stmt->fetchAll(PDO::FETCH_CLASS);

		$this->setNoTranslate('fairs', $fairs);
	}


	public function articles($fairId, $do='', $item=0) {
		setAuthLevel(3);
		$this->Fair->load($fairId, 'id');
		if ($this->Fair->wasLoaded() && ($this->Fair->get('created_by') == $_SESSION['user_id'] || userLevel() == 4)) {
			if (!$this->Fair->isLocked()) {
				if ($do == 'delete') {
					$fa = new FairArticle;
					$fa->load($item, 'id');
					if ($fa->wasLoaded() && $fa->get('fair') == $fairId) {
						$fa->delete();
					}
				}
				
				$this->setNoTranslate('do', $do);
				$this->setNoTranslate('item', $item);
				
				if ($do == 'edit') {
					$this->set('form_headline', 'Edit articles');
					$fa = new FairArticle;
					$fa->load($item, 'id');
					$this->setNoTranslate('current_cid', $fa->get('custom_id'));
					$this->setNoTranslate('current_text', $fa->get('text'));
					$this->setNoTranslate('current_price', $fa->get('price'));
					$this->setNoTranslate('required_status', $fa->get('required'));
					if ($fa->get('vat') == 25)
					$this->setNoTranslate('vat', 25);
					if ($fa->get('vat') == 18)
					$this->setNoTranslate('vat', 18);
					if ($fa->get('vat') == 12)
					$this->setNoTranslate('vat', 12);
					if ($fa->get('vat') == 0)
					$this->setNoTranslate('vat', 0);

				} else
					$this->set('form_headline', 'New article');
				
				$this->set('headline', 'Fair articles');
				$this->setNoTranslate('fair_id', $this->Fair->get('id'));
				$this->setNoTranslate('fair', $this->Fair);
				$this->set('id_label', 'ID');
				$this->set('name_label', 'Text');
				$this->set('price_label', 'Price (without VAT)');
				$this->set('vat_label', 'Select VAT for this article');
				$this->set('no_vat_label', 'No VAT (0%)');
	//			$this->set('required_label', 'Required');
				$this->set('hidden_label', 'Only available for admin');
				$this->set('yes_label', 'Yes');
				$this->set('no_label', 'No');
				$this->set('save_label', 'Save');
				$this->set('th_id', 'ID');
				$this->set('th_name', 'Article');
				$this->set('th_edit', 'Edit');
				$this->set('th_price', 'Price');
	//			$this->set('th_required', 'Required');
				$this->set('th_vat', 'Vat');
				$this->set('th_delete', 'Delete');
				$this->set('confirm_delete', 'Do you really want to delete this article? All bookings that are related to this article will have their relation to this article removed.');

				if (isset($_POST['save'])) {
					$fa = new FairArticle;
					if ($do == 'edit')
					$fa->load($item, 'id');
					$fa->set('custom_id', $_POST['custom_id']);
					$fa->set('text', $_POST['text']);
					$fa->set('price', $_POST['price']);
					$fa->set('required', $_POST['required']);
					$fa->set('vat', $_POST['vat']);
					$fa->set('fair', $this->Fair->get('id'));
					$fa->save();
					
					if ($do == 'edit') {
						header('Location: '.BASE_URL.'fair/articles/'.$fairId);
						exit;
					}
					
				}

				$this->Fair->load($fairId, 'id');
				$this->setNoTranslate('articles', $this->Fair->get('articles'));

			} else {
				$this->setNoTranslate('event_locked', true);
			}
		}
	}



	public function extraOptions($fairId, $do='', $item=0) {
		setAuthLevel(3);
		$this->Fair->load($fairId, 'id');
		if ($this->Fair->wasLoaded() && ($this->Fair->get('created_by') == $_SESSION['user_id'] || userLevel() == 4)) {
			if (!$this->Fair->isLocked()) {
				if ($do == 'delete') {
					$feo = new FairExtraOption;
					$feo->load($item, 'id');
					if ($feo->wasLoaded() && $feo->get('fair') == $fairId) {
						$feo->delete();
					}
				}
				
				$this->setNoTranslate('do', $do);
				$this->setNoTranslate('item', $item);
				
				if ($do == 'edit') {
					$this->set('form_headline', 'Edit extra options');
					$feo = new FairExtraOption;
					$feo->load($item, 'id');
					$this->setNoTranslate('current_cid', $feo->get('custom_id'));
					$this->setNoTranslate('current_text', $feo->get('text'));
					$this->setNoTranslate('current_price', $feo->get('price'));
					$this->setNoTranslate('required_status', $feo->get('required'));

					if ($feo->get('vat') == 25)
					$this->setNoTranslate('vat', 25);
					if ($feo->get('vat') == 18)
					$this->setNoTranslate('vat', 18);
					if ($feo->get('vat') == 12)
					$this->setNoTranslate('vat', 12);
					if ($feo->get('vat') == 0)
					$this->setNoTranslate('vat', 0);

				} else
					$this->set('form_headline', 'New extra option');
				if ($this->Fair->isLocked()) {
					$this->setNoTranslate('event_locked', true);
				}
				$this->set('headline', 'Fair options');
				$this->setNoTranslate('fair_id', $this->Fair->get('id'));
				$this->setNoTranslate('fair', $this->Fair);
				$this->set('id_label', 'ID');
				$this->set('name_label', 'Text');
				$this->set('price_label', 'Price (without VAT)');
				$this->set('vat_label', 'Select VAT for this option');
				$this->set('no_vat_label', 'No VAT (0%)');
				$this->set('required_label', 'Required');
				$this->set('requiredyes_label', 'Yes');
				$this->set('requiredno_label', 'No');	
				$this->set('save_label', 'Save');
				$this->set('th_id', 'ID');
				$this->set('th_name', 'Option');
				$this->set('th_edit', 'Edit');
				$this->set('th_price', 'Price');
				$this->set('th_required', 'Required');
				$this->set('th_vat', 'Vat');
				$this->set('th_delete', 'Delete');
				$this->set('confirm_delete', 'Do you really want to delete this option? All bookings that are related to this option will have their relation to this option removed.');

				if (isset($_POST['save'])) {
					$feo = new FairExtraOption;
					if ($do == 'edit')
					$feo->load($item, 'id');
					$feo->set('custom_id', $_POST['custom_id']);
					$feo->set('text', $_POST['text']);
					$feo->set('price', $_POST['price']);
					$feo->set('required', $_POST['required']);
					$feo->set('vat', $_POST['vat']);
					$feo->set('fair', $this->Fair->get('id'));
					$feo->save();
					
					if ($do == 'edit') {
						header('Location: '.BASE_URL.'fair/extraOptions/'.$fairId);
						exit;
					}
					
				}

				$this->Fair->load($fairId, 'id');
				$this->setNoTranslate('extraOptions', $this->Fair->get('extraOptions'));
			} else {
				$this->setNoTranslate('event_locked', true);
			}
		}

	}
/*
	public function fairgroups($owner) {

		setAuthLevel(3);
		$this->setNoTranslate('owner', $owner);
		$this->set('headline', 'Event groups overview');
		$this->set('th_fair', 'Name');
		$this->set('th_organizer', 'Organizer');
		$this->set('th_delete', 'Delete');

		$sql = "SELECT f.id, f.name, f.windowtitle, approved, modules, creation_time, page_views, f.event_start, f.event_stop, f.created_by,
				COUNT(fmap.id) AS maps_cnt,
				u.company AS arranger_name,
				(SELECT COUNT(*) FROM fair_map_position AS fmp WHERE fmp.map = fmap.id AND status = 2) AS booked_cnt,
				(SELECT COUNT(*) FROM fair_map_position AS fmp WHERE fmp.map = fmap.id AND status = 1) AS reserved_cnt,
				(SELECT COUNT(*) FROM fair_map_position AS fmp WHERE fmp.map = fmap.id) AS total_cnt
				FROM fair AS f
				LEFT JOIN fair_map AS fmap ON fmap.fair = f.id
				LEFT JOIN user AS u ON f.created_by = u.id";

		switch (userLevel()) {
			case 4:
				$params = array();

				if ($param == 'new') {
					$sql .= " WHERE approved = ?";
					array_push($params, '0');
				} else if ((int) $param > 0) {
					$sql .= " WHERE f.created_by = ?";
					array_push($params, $param);
				}

				break;

			case 3:
				$sql .= " WHERE f.created_by = ?";
				$params = array($_SESSION['user_id']);
				break;

			default:
				toLogin();
				break;
		}

		$sql .= " GROUP BY f.id ORDER BY approved, name";

		$stmt = $this->Fair->db->prepare($sql);
		$stmt->execute($params);
		$fairs = $stmt->fetchAll(PDO::FETCH_CLASS);

		$this->setNoTranslate('fairs', $fairs);
	}
	*/
	public function categories($fairId, $do='', $item=0) {
		setAuthLevel(3);
		$this->Fair->load($fairId, 'id');
		if ($this->Fair->wasLoaded() && ($this->Fair->get('created_by') == $_SESSION['user_id'] || userLevel() == 4)) {
			if (!$this->Fair->isLocked()) {
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
				if ($this->Fair->isLocked()) {
					$this->setNoTranslate('event_locked', true);
				}
				$this->set('headline', 'Exhibitor categories for');
				$this->setNoTranslate('fair_id', $this->Fair->get('id'));
				$this->setNoTranslate('fair', $this->Fair);
				$this->set('name_label', 'Name');
				$this->set('save_label', 'Save');
				$this->set('th_name', 'Category');
				$this->set('th_edit', 'Edit');
				$this->set('th_delete', 'Delete');
				$this->set('confirm_delete', 'Do you really want to delete this category? All bookings that are related to this category will have their relation to this category removed.');

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
			} else {
				$this->setNoTranslate('event_locked', true);
			}
		}

	}


	public function invoiceSettings($id) {

		setAuthLevel(3);

		$this->Fair->load($id, 'id');
		if ($this->Fair->wasLoaded() && ($this->Fair->get('created_by') == $_SESSION['user_id'] || userLevel() == 4)) {

			$fairInvoice = new FairInvoice();
			$fairInvoice->load($id, 'fair');

			$this->setNoTranslate('fair_id', $id);
			if (isset($_POST['save'])) {

				$fairInvoice->set('reference', $_POST['reference']);
				$fairInvoice->set('company_name', $_POST['company_name']);
				$fairInvoice->set('address', $_POST['address']);
				$fairInvoice->set('zipcode', $_POST['zipcode']);
				$fairInvoice->set('city', $_POST['city']);
				$fairInvoice->set('country', $_POST['country']);
				$fairInvoice->set('orgnr', $_POST['orgnr']);
				$fairInvoice->set('bank_no', $_POST['bank_no']);
				$fairInvoice->set('postgiro', $_POST['postgiro']);
				$fairInvoice->set('vat_no', $_POST['vat_no']);
				$fairInvoice->set('iban_no', $_POST['iban_no']);
				$fairInvoice->set('swift_no', $_POST['swift_no']);
				$fairInvoice->set('swish_no', $_POST['swish_no']);
				$fairInvoice->set('phone', $_POST['phone']);
				$fairInvoice->set('email', $_POST['invoice_email']);
				$fairInvoice->set('fair', $id);
				$fairInvoice->set('invoice_id_start', $_POST['invoice_id_start']);
				$fairInvoice->set('credit_invoice_id_start', $_POST['credit_invoice_id_start']);
				$fairInvoice->set('pos_vat', $_POST['pos_vat']);
				$fairInvoice->set('default_expirationdate', strtotime($_POST['default_expirationdate']));
				$fairInvoice->set('website', $_POST['website']);

				$fairInvoice->save();

				header("Location: ".BASE_URL."fair/invoiceSettings/".$id);

			}
			if ($fairInvoice->get('default_expirationdate')) {
				$date = date('d-m-Y', $fairInvoice->get('default_expirationdate'));
			} else {
				$date = '';
			}
			$this->set('invoice_headline', ' Invoice information and settings');
			$this->set('save_label', 'Save');
			$this->set('reference_label', 'Reference');
			$this->set('company_name_label', 'Company name');
			$this->set('address_label', 'Address');
			$this->set('zipcode_label', 'Zipcode');
			$this->set('city_label', 'City');
			$this->set('country_label', 'Country');
			$this->set('orgnr_label', 'Organization number');
			$this->set('bank_no_label', 'Bank number');
			$this->set('postgiro_label', 'Postgiro');
			$this->set('vat_no_label', ' VAT number');
			$this->set('iban_no_label', 'IBAN');
			$this->set('swift_no_label', 'SWIFT');
			$this->set('swish_no_label', 'SWISH');
			$this->set('phone_label', 'phone');
			$this->set('email_label', 'Email');
			$this->set('website_label', 'Website');
			$this->set('default_expirationdate_label', 'Default expirationdate for invoices');
			$this->setNoTranslate('fair', $id);
			$this->setNoTranslate('fairname', $this->Fair->get('name'));
			$this->set('invoice_id_start_label', 'Starting number for the first invoice');
			$this->set('credit_invoice_id_start_label', 'Starting number for the first credited invoice');
			$this->set('pos_vat_label', 'Position VAT');
			$this->set('no_vat_label', 'No VAT (0%)');
			$this->setNoTranslate('reference', $fairInvoice->get('reference'));
			$this->setNoTranslate('company_name', $fairInvoice->get('company_name'));
			$this->setNoTranslate('address', $fairInvoice->get('address'));
			$this->setNoTranslate('zipcode', $fairInvoice->get('zipcode'));
			$this->setNoTranslate('city', $fairInvoice->get('city'));
			$this->setNoTranslate('country', $fairInvoice->get('country'));
			$this->setNoTranslate('orgnr', $fairInvoice->get('orgnr'));
			$this->setNoTranslate('bank_no', $fairInvoice->get('bank_no'));
			$this->setNoTranslate('postgiro', $fairInvoice->get('postgiro'));
			$this->setNoTranslate('vat_no', $fairInvoice->get('vat_no'));
			$this->setNoTranslate('iban_no', $fairInvoice->get('iban_no'));
			$this->setNoTranslate('swift_no', $fairInvoice->get('swift_no'));
			$this->setNoTranslate('swish_no', $fairInvoice->get('swish_no'));
			$this->setNoTranslate('phone', $fairInvoice->get('phone'));
			$this->setNoTranslate('invoice_email', $fairInvoice->get('email'));
			$this->setNoTranslate('website', $fairInvoice->get('website'));
			$this->setNoTranslate('default_expirationdate', $date);
			$this->setNoTranslate('fair', $fairInvoice->get('fair'));
			$this->setNoTranslate('invoice_id_start', $fairInvoice->get('invoice_id_start'));
			$this->setNoTranslate('credit_invoice_id_start', $fairInvoice->get('credit_invoice_id_start'));

			if ($fairInvoice->get('pos_vat') == 25)
			$this->setNoTranslate('pos_vat', 25);
			if ($fairInvoice->get('pos_vat') == 18)
			$this->setNoTranslate('pos_vat', 18);
			if ($fairInvoice->get('pos_vat') == 0)
			$this->setNoTranslate('pos_vat', 0);
			
			} else {
				toLogin();
			}
		}


	public function rules($id) {

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
			$this->set('rules_headline', 'Edit rules for event');
			$this->Fair->load($id, 'id');
			
			if (userLevel() == 3 && $this->Fair->get('created_by') != $_SESSION['user_id'])
				toLogin();

			if (isset($_POST['save'])) {

				$this->Fair->set('rules', $_POST['rules']);
				$fId = $this->Fair->save();

				header("Location: ".BASE_URL."fair/rules/".$id);
				exit;
			}

			$this->setNoTranslate('edit_id', $id);
			$this->setNoTranslate('fair', $this->Fair);

			$this->set('rules_label', 'Rules for this event');
			$this->set('save_label', 'Save');
			$this->set('cancel_label', 'Cancel');
		}
	}

	public function lock($id) {
		setAuthLevel(3);
		if (!empty($id)) {
			$this->Fair->load($id, 'id');
			if (userLevel() == 3 && $this->Fair->get('created_by') != $_SESSION['user_id'])
				toLogin();
			$this->Fair->set('approved', 2);
			$this->Fair->save();
		}
		header("Location: ".BASE_URL."fair/overview");
		exit;
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
				
				if (userLevel() == 3 && $this->Fair->get('created_by') != $_SESSION['user_id'])
					toLogin();
			}

			if (isset($_POST['save'])) {
				if (($id == 'new') || (userLevel() == 4)){
					if (isset($_POST['name']))
						$this->Fair->set('name', $_POST['name']);
				}
				$this->Fair->set('windowtitle', $_POST['windowtitle']);
				$this->Fair->set('contact_info', $_POST['contact_info']);
				$this->Fair->set('contact_phone', $_POST['contact_phone']);
				$this->Fair->set('website', $_POST['website']);
				$this->Fair->set('contact_email', $_POST['contact_email']);
				$this->Fair->set('contact_name', $_POST['contact_name']);
				$this->Fair->set('hidden_info', $_POST['hidden_info']);
				$this->Fair->set('event_start', strtotime($_POST['event_start']));
				$this->Fair->set('event_stop', strtotime($_POST['event_stop']));
				$this->Fair->set('accepted_clone_date', strtotime($_POST['accepted_clone_date']));
				$this->Fair->set('default_reservation_date', strtotime($_POST['default_reservation_date']));
				if (userLevel() == 4) {
					$this->Fair->set('approved', $_POST['approved']);
					$this->Fair->set('created_by', $_POST['arranger']);	
				} else {
					$this->Fair->set('created_by', $_SESSION['user_id']);
					$this->Fair->set('approved', 1);
				}
				$this->Fair->set('currency', $_POST['currency']);
				$this->Fair->set('hidden', $_POST['hidden']);
				$this->Fair->set('allow_registrations', $_POST['allow_registrations']);
				$this->Fair->set('hidden_search', $_POST['hidden_search']);
				$this->Fair->set('reminder_day1', $_POST['reminder_day1']);
				$this->Fair->set('reminder_note1', $_POST['reminder_note1']);
				$fId = $this->Fair->save();

				$now = time();
				$target_dir = ROOT.'public/images/fairs/'.$fId.'/logotype/';
				$target_file = $target_dir.'/'.$fId.'_logo.png';

				if (!file_exists(ROOT.'public/images/fairs/'.$fId.'/logotype')) {
					mkdir(ROOT.'public/images/fairs/'.$fId.'/logotype');
					chmod(ROOT.'public/images/fairs/'.$fId.'/logotype', 0775);
				}

				if (is_uploaded_file($_FILES['image']['tmp_name'])) {
					$im = new ImageMagick;
					array_map('unlink', glob(ROOT.'public/images/fairs/'.$fId.'/logotype/*'));
					move_uploaded_file($_FILES['image']['tmp_name'], ROOT.'public/images/tmp/'.$now.'.png');
					chmod(ROOT.'public/images/tmp/'.$now.'.png', 0775);
					$im->IMEventLogo(ROOT.'public/images/tmp/'.$now.'.png', $target_dir, 140);
					chmod($target_dir, 0775);
					unlink(ROOT.'public/images/tmp/'.$now.'.png');
				}

				$this->updateAliases();

				if ($id == 'new') {
					$_SESSION['user_fair'] = $fId;
					$user = new User();
					$user->load2($_SESSION['user_id'], 'id');
					/* Preparing to send the mail */
					$from = array(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
					$recipient = array('info@chartbooker.com', 'Chartbooking admins');
					/* UPDATED TO FIT MAILJET */
					$mail = new Mail();
					$mail->setTemplate('new_fair');
					$mail->setFrom($from);
					$mail->setRecipient($recipient);
					/* Setting mail variables */
					$mail->setMailVar('creator_name', $user->get('company'));
					$mail->setMailVar('event_name', $this->Fair->get('name'));
					$mail->sendMessage();
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
			} else if ($this->Fair->get('approved') == 1) {
				$this->setNoTranslate('app_sel0', '');
				$this->setNoTranslate('app_sel1', ' selected="selected"');
				$this->setNoTranslate('app_sel2', '');
				$this->setNoTranslate('disable', '');
			} else if ($this->Fair->get('approved') == 2){
				$this->setNoTranslate('app_sel0', '');
				$this->setNoTranslate('app_sel1', '');
				$this->setNoTranslate('app_sel2', ' selected="selected"');
			}
			//$this->Fair->get('hidden') == 1 ? $this->setNoTranslate('hidden_val', 'checked') : $this->setNoTranslate('hidden_val', '');
			if ($this->Fair->get('hidden') == 1)
				$this->setNoTranslate('hidden_val', 'checked');
			else
				$this->setNoTranslate('hidden_val', '');
			if ($this->Fair->get('allow_registrations') == 1)
				$this->setNoTranslate('allow_registrations_val', 'checked');
			else
				$this->setNoTranslate('allow_registrations_val', '');
			if ($this->Fair->get('hidden_search') == 1) 
				$this->setNoTranslate('hidden_search_val', 'checked');
			else
				$this->setNoTranslate('hidden_search_val', '');
			if (userLevel() < 3)
				$this->setNoTranslate('disable', 'disabled="disabled"');
			else
				$this->setNoTranslate('disable', '');
			$this->setNoTranslate('image_path', '../images/fairs/'.$id.'/logotype');
			$this->set('currency_label', 'Currency');
			$this->set('approved_label', 'Status');
			$this->set('arranger_label', 'Organizer');
			$this->set('app_opt0', 'Not approved');
			$this->set('app_opt1', 'Approved');
			$this->set('app_opt2', 'Locked');
			$this->set('name_label', 'Name');
			$this->set('window_title_label', 'Window title');
			$this->set('email_label', 'E-mail address');
			$this->set('logo_label', 'Logotype');
			$this->set('contact_label', 'Contact information');
			$this->set('contact_email_label', 'Contact Email');
			$this->set('contact_name_label', 'Contact Name');
			$this->set('website_label', 'Website');
			$this->set('contact_phone_label', 'Contact Phone');
			$this->set('hidden_info_label', 'Information when event is hidden');
			$this->set('event_start', 'Event opening date');
			$this->set('event_stop', 'Event closing date');
			$this->set('accepted_cloned_reservations', 'Date for accepted cloned reservations');
			$this->set('default_reservation_date', 'Default date for new reservations');
			$this->set('interval_reminders_label', 'Interval for reminders');
			$this->set('reminder_1_label', '1st reminder');
			$this->set('no_reminder_label', 'No reminder');
			$this->set('edit_label', 'Edit');
			$this->set('delete_label', 'Delete');
			$this->set('edit_note_1_label', 'Edit message for 1st reminder');
			$this->set('save_label', 'Save');
			$this->set('cancel_label', 'Cancel');
			$this->set('hide_fair_for_label', 'Hide fair for unauthorized accounts');
			$this->set('hide_fair_search_label', 'Hide fair in the "fair search"');
			$this->set('allow_registrations_label', 'Allow registrations when fair is hidden');
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

			if (userLevel() == 3 && $this->Fair->get('created_by') != $_SESSION['user_id'])
				toLogin();

			if (isset($_POST['name'])) {

				$auto_close_reserved = date('Y-m-d-H:i', strtotime($_POST['auto_close_reserved']));

				$fair_clone = new Fair();
				$fair_clone->set('name', $_POST['name']);
				$fair_clone->set('windowtitle', $_POST['windowtitle']);
				$fair_clone->set('email', '');
				$fair_clone->set('contact_info', $_POST['contact_info']);
				$fair_clone->set('contact_phone', $_POST['contact_phone']);
				$fair_clone->set('website', $_POST['website']);
				$fair_clone->set('contact_email', $_POST['contact_email']);
				$fair_clone->set('contact_name', $_POST['contact_name']);
				$fair_clone->set('hidden_info', $this->Fair->get('hidden_info'));
				$fair_clone->set('created_by', $this->Fair->get('created_by'));
				$fair_clone->set('page_views', 0);
				$fair_clone->set('approved', 1);
				$fair_clone->set('event_start', strtotime($_POST['event_start']));
				$fair_clone->set('event_stop', strtotime($_POST['event_stop']));
				$fair_clone->set('accepted_clone_date', strtotime($_POST['accepted_clone_date']));
				$fair_clone->set('default_reservation_date', strtotime($_POST['default_reservation_date']));
				$fair_clone->set('hidden', $this->Fair->get('hidden'));
				$fair_clone->set('reminder_day1', $this->Fair->get('reminder_day1'));
				$fair_clone->set('reminder_note1', $this->Fair->get('reminder_note1'));
				$fair_clone->set('mail_settings', $this->Fair->get('mail_settings'));
				$fair_clone_id = $fair_clone->save();

				/* Kopiera även Logotypen */
				$logo_path_old = ROOT.'public/images/fairs/'.$this->Fair->get('id').'/logotype/';
				$logo_path_new = ROOT.'public/images/fairs/'.$fair_clone_id.'/logotype/';
				foreach (glob($logo_path_old.'*') as $filename) {
					copy($logo_path_old. '/' .basename($filename), $logo_path_new. '/' .basename($filename));
					chmod($logo_path_new. '/' .basename($filename), 0775);
				}

				/* Hämta alla kartor */
				$statement = $this->db->prepare('SELECT * FROM fair_map WHERE fair = ?');
				$statement->execute(array($this->Fair->get('id')));
				$maps = $statement->fetchAll(PDO::FETCH_ASSOC);
				$position_ids = array();

				foreach ($maps as $map) {
					/* Kopiera kartan */
					$statement = $this->db->prepare("INSERT INTO fair_map (fair, name, file_name, grid_settings, sortorder) VALUES (?, ?, ?, ?, ?)");
					$statement->execute(array($fair_clone_id, $map['name'], $map['file_name'], $map['grid_settings'], $map['sortorder']));
					$map_clone_id = $this->db->lastInsertId();

					/* Kopiera även kartbilderna */
					$image_path_old = ROOT.'public/images/fairs/'.$this->Fair->get('id').'/maps/';
					$image_path_new = ROOT.'public/images/fairs/'.$fair_clone_id.'/maps/';

					if(file_exists($image_path_old . $map['id'] . '.jpg')) {
						copy($image_path_old . $map['id'] . '.jpg', $image_path_new . $map_clone_id . '.jpg');
						chmod($image_path_new . $map_clone_id . '.jpg', 0775);
					}

					if(file_exists($image_path_old . $map['id'] . '_large.jpg')) {
						copy($image_path_old . $map['id'] . '_large.jpg', $image_path_new . $map_clone_id . '_large.jpg');
						chmod($image_path_new . $map_clone_id . '_large.jpg', 0775);
					}

					/* Hämta alla ståndspositioner */
					$statement = $this->db->prepare('SELECT * FROM fair_map_position WHERE map = ?');
					$statement->execute(array($map['id']));
					$positions = $statement->fetchAll(PDO::FETCH_ASSOC);

					foreach ($positions as $position) {
						/* Kopiera platserna */
						$statement = $this->db->prepare("INSERT INTO fair_map_position (map, x, y, area, name, information, price, status, expires, created_by, being_edited, edit_started) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
						$new_status = ($position['status'] == 2 ? 1 : 0);
						$statement->execute(array($map_clone_id, $position['x'], $position['y'], $position['area'], $position['name'], $position['information'], $position['price'], $new_status, $auto_close_reserved, $position['created_by'], $position['being_edited'], $position['edit_started']));
						$position_ids[$position['id']] = $this->db->lastInsertId();
					}
				}

				/* Hämta alla kopplingar mellan användare och utställningar */
				$statement = $this->db->prepare('SELECT * FROM fair_user_relation WHERE fair = ?');
				$statement->execute(array($this->Fair->get('id')));
				$user_relations = $statement->fetchAll(PDO::FETCH_ASSOC);

				foreach ($user_relations as $relation) {
					/* Kopiera kopplingen */
					$statement = $this->db->prepare("INSERT INTO fair_user_relation (fair, user, map_access, connected_time) VALUES (?, ?, ?, ?)");
					$statement->execute(array($fair_clone_id, $relation['user'], $relation['map_access'], $relation['connected_time']));
				}

				/* Hämta alla preliminärbokningar */
				/*$statement = $this->db->prepare('SELECT * FROM preliminary_booking WHERE fair = ?');
				$statement->execute(array($this->Fair->get('id')));
				$preliminary_booking = $statement->fetchAll(PDO::FETCH_ASSOC);

				foreach ($preliminary_booking as $booking) {
					/* Kopiera bokningen *//*
					$statement = $this->db->prepare("INSERT INTO preliminary_booking (user, fair, position, categories, commodity, arranger_message, booking_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
					$statement->execute(array($booking['user'], $fair_clone_id, $position_ids[$booking['position']], $booking['categories'], $booking['commodity'], $booking['arranger_message'], $booking['booking_time']));
				}*/

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

				/* Hämta alla extra tillval */
				$statement = $this->db->prepare('SELECT * FROM fair_extra_option WHERE fair = ?');
				$statement->execute(array($this->Fair->get('id')));
				$fair_options = $statement->fetchAll(PDO::FETCH_ASSOC);
				$ex_option_ids = array();

				foreach ($fair_options as $option) {
					/* Kopiera tillvalet */
					$statement = $this->db->prepare("INSERT INTO fair_extra_option (custom_id, text, fair, price, required, vat) VALUES (?, ?, ?, ?, ?, ?)");
					$statement->execute(array($option['custom_id'], $option['text'], $fair_clone_id, $option['price'], $option['required'], $option['vat']));
					$ex_option_ids[$option['id']] = $this->db->lastInsertId();
				}

				/* Hämta faktureringsinställningarna */
				$statement = $this->db->prepare('SELECT * FROM fair_invoice WHERE fair = ?');
				$statement->execute(array($this->Fair->get('id')));
				$fair_invoice = $statement->fetchAll(PDO::FETCH_ASSOC);

				$stmt_invoiceid = $this->db->prepare("SELECT id FROM exhibitor_invoice as id WHERE fair = ? order by id desc limit 1");
				$stmt_invoiceid->execute(array($this->Fair->get('id')));
				$res = $stmt_invoiceid->fetch(PDO::FETCH_ASSOC);
				$invoice_id = $res['id'];
				$invoice_id += 1;

				$stmt_creditinvoiceid = $this->db->prepare("SELECT `cid` FROM `exhibitor_invoice_credited` WHERE `fair` = ? order by id desc limit 1");
				$stmt_creditinvoiceid->execute(array($this->Fair->get('id')));
				$res1 = $stmt_creditinvoiceid->fetch(PDO::FETCH_ASSOC);
				$credit_invoice_id = $res1['cid'];
				$credit_invoice_id += 1;

				foreach ($fair_invoice as $invoice) {
					/* Kopiera faktureringsinställningarna */
					$statement = $this->db->prepare("INSERT INTO fair_invoice (reference, company_name, address, zipcode, city, country, orgnr, bank_no, postgiro, vat_no, iban_no, swift_no, swish_no, phone, fair, invoice_id_start, credit_invoice_id_start, pos_vat, default_expirationdate, website) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
					$statement->execute(array($invoice['reference'], $invoice['company_name'], $invoice['address'], $invoice['zipcode'], $invoice['city'], $invoice['country'], $invoice['orgnr'], $invoice['bank_no'], $invoice['postgiro'], $invoice['vat_no'], $invoice['iban_no'], $invoice['swift_no'], $invoice['swish_no'], $invoice['phone'], $fair_clone_id, $invoice_id, $credit_invoice_id, $invoice['pos_vat'], $invoice['default_expirationdate'], $invoice['website']));
				}

				/* Hämta alla artiklar */
				$statement = $this->db->prepare('SELECT * FROM fair_article WHERE fair = ?');
				$statement->execute(array($this->Fair->get('id')));
				$fair_articles = $statement->fetchAll(PDO::FETCH_ASSOC);
				$ex_article_ids = array();

				foreach ($fair_articles as $article) {
					/* Kopiera tillvalet */
					$statement = $this->db->prepare("INSERT INTO fair_article (custom_id, text, fair, price, required, vat) VALUES (?, ?, ?, ?, ?, ?)");
					$statement->execute(array($article['custom_id'], $article['text'], $fair_clone_id, $article['price'], $article['required'], $article['vat']));
					$ex_article_ids[$article['id']] = $this->db->lastInsertId();
				}

				/* Hämta de utställare som är BOKADE på eventet */
				$statement = $this->db->prepare('SELECT ex.* FROM exhibitor AS ex INNER JOIN fair_map_position AS fmp ON fmp.id = ex.position WHERE ex.fair = ? AND fmp.status = 2');
				$statement->execute(array($this->Fair->get('id')));
				$exhibitors = $statement->fetchAll(PDO::FETCH_ASSOC);

				foreach ($exhibitors as $exhibitor) {
					/* Kopiera bokningen */
					if ($exhibitor['recurring'] == 1) {
						$statement = $this->db->prepare("UPDATE fair_map_position SET status = 2 WHERE id = ?");
						$statement->execute(array($position_ids[$exhibitor['position']]));
						$statement = $this->db->prepare("INSERT INTO exhibitor (user, fair, position, commodity, arranger_message, booking_time, clone, status, recurring) VALUES (?, ?, ?, ?, ?, ?, 0, 2, 1)");
						$statement->execute(array($exhibitor['user'], $fair_clone_id, $position_ids[$exhibitor['position']], $exhibitor['commodity'],  $exhibitor['arranger_message'], $exhibitor['booking_time']));
						$exhibitor_clone_id = $this->db->lastInsertId();
					} else {
						$statement = $this->db->prepare("INSERT INTO exhibitor (user, fair, position, commodity, arranger_message, booking_time, clone, status) VALUES (?, ?, ?, ?, ?, ?, 1, 1)");
						$statement->execute(array($exhibitor['user'], $fair_clone_id, $position_ids[$exhibitor['position']], $exhibitor['commodity'],  $exhibitor['arranger_message'], $exhibitor['booking_time']));
						$exhibitor_clone_id = $this->db->lastInsertId();
					}

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

					/* Hämta alla kopplingar mellan utställare och extra tillval */
					$statement = $this->db->prepare('SELECT * FROM exhibitor_option_rel WHERE exhibitor = ?');
					$statement->execute(array($exhibitor['id']));
					$ex_option_relations = $statement->fetchAll(PDO::FETCH_ASSOC);

					foreach ($ex_option_relations as $relation) {
						/* Kopiera kopplingen */
						if (isset($ex_option_ids[$relation['option']])) {
							$statement = $this->db->prepare("INSERT INTO exhibitor_option_rel (`exhibitor`, `option`) VALUES (?, ?)");
							$statement->execute(array($exhibitor_clone_id, $ex_option_ids[$relation['option']]));
						}
					}

					/* Hämta alla kopplingar mellan utställare och artiklar */
					$statement = $this->db->prepare('SELECT * FROM exhibitor_article_rel WHERE exhibitor = ?');
					$statement->execute(array($exhibitor['id']));
					$ex_article_relations = $statement->fetchAll(PDO::FETCH_ASSOC);

					foreach ($ex_article_relations as $relation) {
						/* Kopiera kopplingen */
						if (isset($ex_article_ids[$relation['article']])) {
							$statement = $this->db->prepare("INSERT INTO exhibitor_article_rel (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
							$statement->execute(array($exhibitor_clone_id, $ex_article_ids[$relation['article']], $relation['amount']));
						}
					}

				}
				$this->updateAliases();
				$_SESSION['user_fair'] = $fair_clone_id;
				$user = new User();
				$user->load2($_SESSION['user_id'], 'id');
				/* Preparing to send the mail */
				$from = array(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
				$recipient = array('info@chartbooker.com', 'Chartbooking admins');
				/* UPDATED TO FIT MAILJET */
				$mail = new Mail();
				$mail->setTemplate('new_fair');
				$mail->setFrom($from);
				$mail->setRecipient($recipient);
				/* Setting mail variables */
				$mail->setMailVar('creator_name', $user->get('company'));
				$mail->setMailVar('event_name', $fair_clone->get('name'));
				$mail->sendMessage();
				header("Location: ".BASE_URL."fair/overview/cloning_complete");
				exit;
			}

			$this->setNoTranslate('edit_id', $id);
			$this->setNoTranslate('fair', $this->Fair);

			$this->set('name_label', 'Name');
			$this->set('window_title_label', 'Window title');
			$this->set('auto_close_reserved_label', 'Reservation date for stand spaces');
			$this->set('event_start', 'Event opening date');
			$this->set('event_stop', 'Event closing date');
			$this->set('accepted_cloned_reservations', 'Date for accepted cloned reservations');
			$this->set('default_reservation_date', 'Default date for new reservations');
			$this->set('contact_label', 'Contact information');
			$this->set('website_label', 'Website');
			$this->set('contact_email_label', 'Contact Email');
			$this->set('contact_name_label', 'Contact Name');
			$this->set('contact_phone_label', 'Contact Phone');
			$this->set('hidden_info_label', 'Information when event is hidden');
			$this->set('clone_label', 'Complete cloning');
			$this->set('dialog_clone_complete_info', 'In connection with completing the cloning of your event, you will be billed according to the agreed contractual.');

		}
	}

	public function publicView($url) {

		$this->Fair->load($url, 'url');
		$this->setNoTranslate('fair', $this->Fair);

	}
	/*
	public function editExpirationDate($id) {
		setAuthLevel(3);

		if (!empty($id)) {

			$this->Fair->load2($id, 'id');
			if (userLevel() == 3 && $this->Fair->get('created_by') != $_SESSION['user_id'])
				toLogin();

			// Load all maps on this fair to prepare the next step
			foreach ($this->Fair->get('maps') as $map) {
				$stmt = $this->db->prepare("SELECT DISTINCT `expires` FROM `fair_map_position` WHERE `map` = ? AND `expires` != '0000-00-00 00:00:00'");
				$stmt->execute(array($map->get('id')));
				$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

				foreach ($positions as $pos) {
					$stmt = $this->db->prepare("SELECT COUNT(*) FROM `fair_map_position` WHERE `map` = ? AND `expires` = ?");
					$stmt->execute(array($map->get('id'), $pos["expires"]));
					$rpositions = $stmt->fetch(PDO::FETCH_ASSOC);
					$reserved = array();
					var_dump($rpositions);
					if (count($rpositions) > 0) {
						//for ($row=0; $row<count($rpositions[$row]); $row++) {
							foreach ($rpositions as $res) {
								$reserved[] = $res;
							}
						//}
					}
				}
			}
			
			if (isset($_POST['save'])) {

			}
			$this->set('headline', 'Map overview');
			$this->set('view_reserved', 'View reserved stand spaces');
			$this->set('th_name', 'Map name');
			$this->set('th_view', 'View map');
			$this->set('th_edit', 'Edit expiration date');
			$this->setNoTranslate('fair', $this->Fair);
			$this->setNoTranslate('reserved', $reserved);
		}
	}
	*/
	public function maps($id) {

		setAuthLevel(3);

		if (!empty($id)) {

			$this->Fair->load($id, 'id');
			if (userLevel() == 3 && $this->Fair->get('created_by') != $_SESSION['user_id'])
				toLogin();

			if (isset($_POST['save'])) {
				
			}
			if ($this->Fair->isLocked()) {
				$this->setNoTranslate('event_locked', true);
			}
			$this->set('headline', 'Map overview for');
			$this->setNoTranslate('fair', $this->Fair);
			$this->set('create_link', 'New map');
			$this->set('th_name', 'Map name');
			$this->set("th_file", "File");
			$this->set('th_view', 'View map');
			$this->set('th_edit', 'Edit map');
			$this->set('th_delete', 'Delete');
			$this->set('th_move_up', 'Move up');
			$this->set('th_move_down', 'Move down');

		}
	}

	public function move_map($direction = null, $fair_id = null, $map_id = null) {
		setAuthLevel(3);

		$this->Fair->load($fair_id, 'id');
		if (!$this->Fair->wasLoaded()) {
			header('Location: ' . BASE_URL . 'fair');
			die();
		}

		switch ($direction) {
			case 'up':
				$this->Fair->moveMapUp($map_id);
				break;

			case 'down':
				$this->Fair->moveMapDown($map_id);
				break;
		}

		header('Location: ' . BASE_URL . 'fair/maps/' . $this->Fair->get('id'));
		die();
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

				$this->updateAliases();

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

	public function modules($id = null) {
		setAuthLevel(4);
	
		$fair = new Fair();
		$fair->load($id, "id");
		
		if ($fair->wasLoaded()) {
			if (isset($_POST["save"])) {
				unset($_POST["save"]);
				$fair->set("modules", json_encode($_POST));
				$fair->save();
			}
			$this->set("headline", "Module settings for");
			$this->set("smsFunction", "Enable SMS-module for this fair");
			$this->set("invoiceFunction", "Enable Invoice-module for this fair");
			$this->set("raindanceFunction", "Enable RainDance-module for this fair");
			$this->set("economyFunction", "Enable Economy-module for this fair");
			$this->set("recurringFunction", "Enable Recurring-module for this fair");
			$this->set("active", "active");
			$this->set("save", "Save");
			
			$modules = json_decode($fair->get("modules"));
			if (!is_object($modules)) {
				$modules = new stdClass();
			}

			$this->setNoTranslate("modules", $modules);
			$this->setNoTranslate("id", $id);
			$this->setNoTranslate("fair", $fair);
			
		}
	}

	public function event_mail($id = NULL) {
		setAuthLevel(3);

		$fair = new Fair();
		$fair->load($id, "id");

		if ($fair->wasLoaded() && !$fair->isLocked()) {
			if (isset($_POST["save"])) {
				unset($_POST["save"]);
				$fair->set("mail_settings", json_encode($_POST));
				$fair->save();
			}
			$this->setNoTranslate("fair", $fair);
			$this->set("headline", "Mail settings");
			$this->set("heading", "Automatically send a mail:");
			$this->set("ToMyself", "To myself");
			$this->set("ToExhibitor", "To the Exhibitor");
			$this->set("ToCurrentUser", "To the currently administrating user");
			$this->set("BookingCreated", "When I create a booking or reservation");
			$this->set("BookingEdited", "When I edit a booking or reservation");
			$this->set("BookingCancelled", "When I cancel a booking or reservation");
			$this->set("RecievePreliminaryBooking", "When I recieve a request for stand");
			$this->set("AcceptPreliminaryBooking", "When I accept a request for stand");
			$this->set("CancelPreliminaryBooking", "When I cancel a request for stand");
			$this->set("RecieveRegistration", "When an exhibitor applies for stand");
			$this->set("RegistrationCancelled", "When I cancel an application for stand");
			$this->set("ReservationReminders", "When reminders are active for expiring reservations");
			$this->set("save", "Save");

			$mailSettings = json_decode($fair->get("mail_settings"));
			if (!is_object($mailSettings)) {
				$mailSettings = new stdClass();
				$mailSettings->BookingCreated = null;
				$mailSettings->BookingEdited = null;
				$mailSettings->BookingCancelled = null;
				$mailSettings->RecievePreliminaryBooking = null;
				$mailSettings->AcceptPreliminaryBooking = null;
				$mailSettings->CancelPreliminaryBooking = null;
				$mailSettings->RecieveRegistration = null;
				$mailSettings->RegistrationCancelled = null;
				$mailSettings->ReservationReminders = null;
			}
			$this->setNoTranslate("mailSettings", $mailSettings);
			$this->setNoTranslate("id", $id);
		} else {
			header('Location: '.BASE_URL.'fair/overview');
			$this->setNoTranslate('event_locked', true);
			exit;
		}
	}

	public function updateAliases() {
		$result = $this->Fair->db->query("SELECT `fair`.`url`, `fair`.`contact_email`
			FROM `fair`
			ORDER BY `fair`.`url` ASC
			");

		while (($fair = $result->fetch(PDO::FETCH_ASSOC))) {
			Alias::add($fair['url'], array($fair['contact_email']));
		}

		Alias::commit();
	}

	public function exportToRainDance($id) {

		setAuthLevel(2);

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
		$this->set('invoices_notfound', 'No data to export.');

		$time_now = date('Ymd');	
		$this->setNoTranslate('time_now', $time_now);

		$rd = new Raindance();
		$rd->load($id, 'fair');

		$options = array();
		$articles = array();

		if ($fair->get('id') > 1) {
			$stmt = $this->db->prepare("SELECT ex_i.id AS id, ex.status FROM exhibitor AS ex, exhibitor_invoice AS ex_i WHERE ex_i.fair = ? AND ex.status = 2 AND ex_i.exhibitor = ex.id");
			$stmt->execute(array($id));
			$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$invoiceposprice = 0;
			$invoiceposamount = 0;
			foreach ($invoices as $invoice) {
				$invoice_ids[] = $invoice['id'];

				$invoice_ids[] = $invoice['id'];

				// Get all positions and their price. Add the price to a variable and use it in the $_POST field in the export.
				$stmt2 = $this->db->prepare("SELECT eir.price FROM exhibitor_invoice_rel as eir WHERE `invoice` = ? AND `fair` = ? AND type = 'space'");
				$stmt2->execute(array($invoice['id'], $id));
				$invoicepospriceresult = $stmt2->fetch(PDO::FETCH_ASSOC);

				$invoiceposprice += $invoicepospriceresult['price'];
			}

			if (isset($invoice_ids)) {
				$this->setNoTranslate('invoiceids', $invoice_ids);
				$stmt = $this->db->prepare("SELECT DISTINCT(text) FROM exhibitor_invoice_rel AS eir WHERE eir.fair = ? AND eir.type = 'option'");
				$stmt->execute(array($id));
				$optiontexts = $stmt->fetchAll(PDO::FETCH_ASSOC);

				foreach ($optiontexts as $opttext) {
					$stmt = $this->db->prepare("SELECT eir.text AS text, eir.price AS price, COUNT(amount) FROM exhibitor_invoice_rel AS eir LEFT JOIN exhibitor_invoice AS ex_invoice ON eir.invoice = ex_invoice.id WHERE eir.text = ? AND eir.type = 'option' AND eir.fair = ? AND ex_invoice.fair = ? AND ex_invoice.id IN (" . implode(',', $invoice_ids) . ")");
					$stmt->execute(array($opttext['text'], $id, $id));
					$options[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
				}

				$stmt = $this->db->prepare("SELECT DISTINCT(text) FROM exhibitor_invoice_rel AS eir WHERE eir.fair = ? AND eir.type = 'article'");
				$stmt->execute(array($id));
				$articletexts = $stmt->fetchAll(PDO::FETCH_ASSOC);

				foreach ($articletexts as $arttext) {
					$stmt = $this->db->prepare("SELECT eir.text AS text, eir.price AS price, COUNT(amount) FROM exhibitor_invoice_rel AS eir LEFT JOIN exhibitor_invoice AS ex_invoice ON eir.invoice = ex_invoice.id WHERE eir.text = ? AND eir.type = 'article' AND eir.fair = ? AND ex_invoice.fair = ? AND ex_invoice.id IN (" . implode(',', $invoice_ids) . ")");
					$stmt->execute(array($arttext['text'], $id, $id));
					$articles[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
				}
			}

			$this->setNoTranslate('options', $options);
			$this->setNoTranslate('articles', $articles);
			$this->setNoTranslate('invoiceposprice', $invoiceposprice);
			$this->setNoTranslate('fairId', $id);
			$this->setNoTranslate('rd', $rd);
			$this->set('part_one_label', 'Code part 1');
			$this->set('part_two_label', 'Code part 2');
			$this->set('part_three_label', 'Code part 3');
			$this->set('part_four_label', 'Code part 4');
			$this->set('part_five_label', 'Code part 5');
			$this->set('part_six_label', 'Code part 6');
			$this->set('part_seven_label', 'Code part 7');
			$this->set('part_eight_label', 'Code part 8');
			$this->set('amount_label', 'Amount');
			$this->set('accountingtext_label', 'Accounting text');
			$this->set('accountingdate_label', 'Accounting date');
			$this->set('accrualkey_label', 'Accrual Key');
			$this->set('positions_label', 'All positions');
			

			if (isset($_POST['submit'])) {
				$blank = ' ';

				if (isset($invoiceposprice)) {
						$blanks = 0;
						$count = 0;
						$pos_part_one = '';
						$pos_part_two = '';
						$pos_part_three = '';
						$pos_part_four = '';
						$pos_part_five = '';
						$pos_part_six = '';
						$pos_part_seven = '';
						$pos_part_eight = '';
						$pos_amount = '';
						$pos_accountingtext = '';
						$pos_accountingdate = '';
						$pos_accrualkey = '';
						if (strlen($_POST['pos_part_one']) < 10) {
							$count = strlen(utf8_decode($_POST['pos_part_one']));
							$blanks = (10 - $count);
							$pos_part_one = str_repeat($blank, $blanks);
							$blanks = 0;
							$count = 0;
						}
						if (strlen($_POST['pos_part_two']) < 10) {
							$count = strlen(utf8_decode($_POST['pos_part_two']));
							$blanks = (10 - $count);
							$pos_part_two = str_repeat($blank, $blanks);
							$blanks = 0;
							$count = 0;
						}
						if (strlen($_POST['pos_part_three']) < 10) {
							$count = strlen(utf8_decode($_POST['pos_part_three']));
							$blanks = (10 - $count);
							$pos_part_three = str_repeat($blank, $blanks);
							$blanks = 0;
							$count = 0;
						}
						if (strlen($_POST['pos_part_four']) < 10) {
							$count = strlen(utf8_decode($_POST['pos_part_four']));
							$blanks = (10 - $count);
							$pos_part_four = str_repeat($blank, $blanks);
							$blanks = 0;
							$count = 0;
						}
						if (strlen($_POST['pos_part_five']) < 10) {
							$count = strlen(utf8_decode($_POST['pos_part_five']));
							$blanks = (10 - $count);
							$pos_part_five = str_repeat($blank, $blanks);
							$blanks = 0;
							$count = 0;
						}
						if (strlen($_POST['pos_part_six']) < 10) {
							$count = strlen(utf8_decode($_POST['pos_part_six']));
							$blanks = (10 - $count);
							$pos_part_six = str_repeat($blank, $blanks);
							$blanks = 0;
							$count = 0;
						}
						if (strlen($_POST['pos_part_seven']) < 10) {
							$count = strlen(utf8_decode($_POST['pos_part_seven']));
							$blanks = (10 - $count);
							$pos_part_seven = str_repeat($blank, $blanks);
							$blanks = 0;
							$count = 0;
						}
						if (strlen($_POST['pos_part_eight']) < 10) {
							$count = strlen(utf8_decode($_POST['pos_part_eight']));
							$blanks = (10 - $count);
							$pos_part_eight = str_repeat($blank, $blanks);
							$blanks = 0;
							$count = 0;
						}
						if (strlen($_POST['pos_amount']) < 15) {
							$count = strlen(utf8_decode($_POST['pos_amount']));
							$blanks = (15 - $count);
							$pos_amount = str_repeat($blank, $blanks);
							$blanks = 0;
							$count = 0;
						}
						if (strlen($_POST['pos_accountingtext']) < 30) {
							$count = strlen(utf8_decode($_POST['pos_accountingtext']));
							$blanks = (30 - $count);
							$pos_accountingtext = str_repeat($blank, $blanks);
							$blanks = 0;
							$count = 0;
						}
						if (strlen($_POST['pos_accountingdate']) < 8) {
							$count = strlen(utf8_decode($_POST['pos_accountingdate']));
							$blanks = (8 - $count);
							$pos_accountingdate = str_repeat($blank, $blanks);
							$blanks = 0;
							$count = 0;
						}
						if (strlen($_POST['pos_accrualkey']) < 10) {
							$count = strlen(utf8_decode($_POST['pos_accrualkey']));
							$blanks = 10 - $count;
							$pos_accrualkey = str_repeat($blank, $blanks);
							$blanks = 0;
							$count = 0;
						}
						$positiondata[] = array($_POST['pos_part_one'], $pos_part_one, $_POST['pos_part_two'], $pos_part_two, $_POST['pos_part_three'], $pos_part_three, $_POST['pos_part_four'], $pos_part_four, $_POST['pos_part_five'], $pos_part_five, $_POST['pos_part_six'], $pos_part_six, $_POST['pos_part_seven'], $pos_part_seven, $_POST['pos_part_eight'], $pos_part_eight, $pos_amount, $_POST['pos_amount'], '+', $_POST['pos_accountingtext'], $pos_accountingtext, $_POST['pos_accountingdate'], $pos_accountingdate, $_POST['pos_accrualkey'], $pos_accrualkey);
					}

				if (isset($options)) {
					for($i = 0; $i < count($options); $i++) {
						$blanks = 0;
						$count = 0;
						$opt_part_one = '';
						$opt_part_two = '';
						$opt_part_three = '';
						$opt_part_four = '';
						$opt_part_five = '';
						$opt_part_six = '';
						$opt_part_seven = '';
						$opt_part_eight = '';
						$opt_amount = '';
						$opt_accountingtext = '';
						$opt_accountingdate = '';
						$opt_accrualkey = '';
						foreach ($options[$i] as $option) {
							if ($option['COUNT(amount)'] > 0) {
								if (strlen($_POST['opt_part_one'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['opt_part_one'.$i]));
									$blanks = (10 - $count);
									$opt_part_one = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['opt_part_two'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['opt_part_two'.$i]));
									$blanks = (10 - $count);
									$opt_part_two = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['opt_part_three'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['opt_part_three'.$i]));
									$blanks = (10 - $count);
									$opt_part_three = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['opt_part_four'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['opt_part_four'.$i]));
									$blanks = (10 - $count);
									$opt_part_four = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['opt_part_five'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['opt_part_five'.$i]));
									$blanks = (10 - $count);
									$opt_part_five = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['opt_part_six'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['opt_part_six'.$i]));
									$blanks = (10 - $count);
									$opt_part_six = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['opt_part_seven'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['opt_part_seven'.$i]));
									$blanks = (10 - $count);
									$opt_part_seven = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['opt_part_eight'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['opt_part_eight'.$i]));
									$blanks = (10 - $count);
									$opt_part_eight = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['opt_amount'.$i]) < 15) {
									$count = strlen(utf8_decode($_POST['opt_amount'.$i]));
									$blanks = (15 - $count);
									$opt_amount = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['opt_accountingtext'.$i]) < 30) {
									$count = strlen(utf8_decode($_POST['opt_accountingtext'.$i]));
									$blanks = (30 - $count);
									$opt_accountingtext = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['opt_accountingdate'.$i]) < 8) {
									$count = strlen(utf8_decode($_POST['opt_accountingdate'.$i]));
									$blanks = (8 - $count);
									$opt_accountingdate = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['opt_accrualkey'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['opt_accrualkey'.$i]));
									$blanks = 10 - $count;
									$opt_accrualkey = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								$optiondata[] = array($_POST['opt_part_one'.$i], $opt_part_one, $_POST['opt_part_two'.$i], $opt_part_two, $_POST['opt_part_three'.$i], $opt_part_three, $_POST['opt_part_four'.$i], $opt_part_four, $_POST['opt_part_five'.$i], $opt_part_five, $_POST['opt_part_six'.$i], $opt_part_six, $_POST['opt_part_seven'.$i], $opt_part_seven, $_POST['opt_part_eight'.$i], $opt_part_eight, $opt_amount, $_POST['opt_amount'.$i], '+', $_POST['opt_accountingtext'.$i], $opt_accountingtext, $_POST['opt_accountingdate'.$i], $opt_accountingdate, $_POST['opt_accrualkey'.$i], $opt_accrualkey);
							}
						}
					}
				}

				if (isset($articles)) {
					for($i = 0; $i < count($articles); $i++) {
						$blanks = 0;
						$count = 0;
						$art_part_one = '';
						$art_part_two = '';
						$art_part_three = '';
						$art_part_four = '';
						$art_part_five = '';
						$art_part_six = '';
						$art_part_seven = '';
						$art_part_eight = '';
						$art_amount = '';
						$art_accountingtext = '';
						$art_accountingdate = '';
						$art_accrualkey = '';
						foreach ($articles[$i] as $article) {
							if ($article['COUNT(amount)'] > 0) {
								if (strlen($_POST['art_part_one'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['art_part_one'.$i]));
									$blanks = (10 - $count);
									$art_part_one = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['art_part_two'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['art_part_two'.$i]));
									$blanks = (10 - $count);
									$art_part_two = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['art_part_three'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['art_part_three'.$i]));
									$blanks = (10 - $count);
									$art_part_three = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['art_part_four'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['art_part_four'.$i]));
									$blanks = (10 - $count);
									$art_part_four = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['art_part_five'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['art_part_five'.$i]));
									$blanks = (10 - $count);
									$art_part_five = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['art_part_six'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['art_part_six'.$i]));
									$blanks = (10 - $count);
									$art_part_six = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['art_part_seven'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['art_part_seven'.$i]));
									$blanks = (10 - $count);
									$art_part_seven = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['art_part_eight'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['art_part_eight'.$i]));
									$blanks = (10 - $count);
									$art_part_eight = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['art_amount'.$i]) < 15) {
									$count = strlen(utf8_decode($_POST['art_amount'.$i]));
									$blanks = (15 - $count);
									$art_amount = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['art_accountingtext'.$i]) < 30) {
									$count = strlen(utf8_decode($_POST['art_accountingtext'.$i]));
									$blanks = (30 - $count);
									$art_accountingtext = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['art_accountingdate'.$i]) < 8) {
									$count = strlen(utf8_decode($_POST['art_accountingdate'.$i]));
									$blanks = (8 - $count);
									$art_accountingdate = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								if (strlen($_POST['art_accrualkey'.$i]) < 10) {
									$count = strlen(utf8_decode($_POST['art_accrualkey'.$i]));
									$blanks = 10 - $count;
									$art_accrualkey = str_repeat($blank, $blanks);
									$blanks = 0;
									$count = 0;
								}
								$articledata[] = array($_POST['art_part_one'.$i], $art_part_one, $_POST['art_part_two'.$i], $art_part_two, $_POST['art_part_three'.$i], $art_part_three, $_POST['art_part_four'.$i], $art_part_four, $_POST['art_part_five'.$i], $art_part_five, $_POST['art_part_six'.$i], $art_part_six, $_POST['art_part_seven'.$i], $art_part_seven, $_POST['art_part_eight'.$i], $art_part_eight, $art_amount, $_POST['art_amount'.$i], '+', $_POST['art_accountingtext'.$i], $art_accountingtext, $_POST['art_accountingdate'.$i], $art_accountingdate, $_POST['art_accrualkey'.$i], $art_accrualkey);
							}
						}
					}
				}
				$now = time();
				$stmt = $this->db->prepare("INSERT INTO raindance_export (fair, time, amount) VALUES (?, ?, ?)");
				$stmt->execute(array($id, $now, count($invoice_ids)));
				$rd_id = $this->db->lastInsertId();

				foreach ($invoice_ids as $inv_id) {
					$stmt = $this->db->prepare("INSERT INTO raindance_export_invoices (rdid, invoice) VALUES (?, ?)");
					$stmt->execute(array($rd_id, $inv_id));
				}

				file_put_contents(ROOT.'public/rd/mydata.txt', '');
				if (isset($optiondata)) {
					foreach ($optiondata as $optdata) {
					    $data = implode("", $optdata);
					    $ret = file_put_contents(ROOT.'public/rd/mydata.txt', $data."\r\n", FILE_APPEND | LOCK_EX);
					}
				}
				if (isset($articledata)) {
					foreach ($articledata as $artdata) {
					    $data = implode("", $artdata);
					    $ret = file_put_contents(ROOT.'public/rd/mydata.txt', $data."\r\n", FILE_APPEND | LOCK_EX);
					}
				}
				if (isset($positiondata)) {
				    $data = implode("", $positiondata[0]);
				    $ret = file_put_contents(ROOT.'public/rd/mydata.txt', $data."\r\n", FILE_APPEND | LOCK_EX);
				}
				$file_url = ROOT.'public/rd/mydata.txt';
				header('Content-Type: application/octet-stream');
				header("Content-Transfer-Encoding: Binary"); 
				header("Content-disposition: attachment; filename=\"" . basename('CFStoRainDance'.$time_now.'.txt') . "\"");
				readfile($file_url);
			    exit;
			}
		}
	}

	public function RDsettings($id) {

		setAuthLevel(2);

		$this->Fair->load($id, 'id');
		if (userLevel() == 3) {
			if ($this->Fair->wasLoaded() && $this->Fair->get('created_by') != $_SESSION['user_id']) {
				toLogin();
			}
		}

		if (userLevel() == 2) {
			$stmt = $this->db->prepare('SELECT * FROM fair_user_relation WHERE user=? AND fair=?');
			$stmt->execute(array($_SESSION['user_id'], $id));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (!$result) {
				$this->setNoTranslate('hasRights', false);
				return;
			}
		}

		$this->setNoTranslate('hasRights', true);

		if ($this->Fair->wasLoaded()) {

			$rd = new Raindance();
			$rd->load($id, 'fair');

		
			$this->setNoTranslate('rd', $rd);
			$this->setNoTranslate('fairId', $id);
			$this->setNoTranslate('fair', $this->Fair);
			$this->set('headline', 'Raindance settings for');
			$this->set('part_one_label', 'Code part 1');
			$this->set('part_two_label', 'Code part 2');
			$this->set('part_three_label', 'Code part 3');
			$this->set('part_four_label', 'Code part 4');
			$this->set('part_five_label', 'Code part 5');
			$this->set('part_six_label', 'Code part 6');
			$this->set('part_seven_label', 'Code part 7');
			$this->set('part_eight_label', 'Code part 8');
			$this->set('accrualkey_label', 'Accrual Key');
			$this->set('save_label', 'Save');
			$this->set('extractions_headline', 'Extractions');
			$this->set('tr_time', 'Time of extraction');
			$this->set('tr_amount', 'Amount of invoices');
			$this->set('tr_view', 'View invoices');



			if (isset($_POST['save'])) {
				$rd->set('fair', $id);
				$rd->set('part_one', $_POST['part_one']);
				$rd->set('part_two', $_POST['part_two']);
				$rd->set('part_three', $_POST['part_three']);
				$rd->set('part_four', $_POST['part_four']);
				$rd->set('part_five', $_POST['part_five']);
				$rd->set('part_six', $_POST['part_six']);
				$rd->set('part_seven', $_POST['part_seven']);
				$rd->set('part_eight', $_POST['part_eight']);
				$rd->set('accrualkey', $_POST['accrualkey']);
				$rd->save();

				header("Location: ".BASE_URL."fair/RDsettings/".$id);
			}

			$stmt = $this->db->prepare("SELECT * FROM raindance_export WHERE fair = ?");
			$stmt->execute(array($id));
			$extractions = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			$this->setNoTranslate('extractions', $extractions);

		}
	}

	public function economy($id) {

		setAuthLevel(2);

		$fair = new Fair();
		$this->Fair->loadsimple($id, 'id');

		if (userLevel() == 3) {
			if ($this->Fair->wasLoaded() && $this->Fair->get('created_by') != $_SESSION['user_id']) {
				toLogin();
			}
		}

		if (userLevel() == 2) {
			$stmt = $this->db->prepare('SELECT * FROM fair_user_relation WHERE user=? AND fair=?');
			$stmt->execute(array($_SESSION['user_id'], $id));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (!$result) {
				$this->setNoTranslate('hasRights', false);
				return;
			}
		}

		$this->setNoTranslate('hasRights', true);

		if ($this->Fair->wasLoaded()) {

			if (isset($_POST['submit'])) {
				if ($_POST['invoicestatus'] == 4) {
					$stmt = $this->db->prepare("SELECT * FROM `exhibitor_invoice_history` WHERE fair = ? AND status = ? AND expires BETWEEN ? AND ? ORDER BY id ASC");
				} else {
					$stmt = $this->db->prepare("SELECT * FROM `exhibitor_invoice` WHERE fair = ? AND status = ? AND expires BETWEEN ? AND ? ORDER BY id ASC");
				}

				$stmt->execute(array($id, $_POST['invoicestatus'], date("Y-m-d", strtotime($_POST['expires_from'])), date("Y-m-d", strtotime($_POST['expires_to']))));
				$invoice_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$invoices = array();
				$invoiceposprice = 0;
				$invoiceposamount = 0;

				foreach ($invoice_result as $invoice) {
					$invoiceposname = array();
					$invoice_ids[] = $invoice['id'];

					$stmt = $this->db->prepare("SELECT text FROM exhibitor_invoice_rel as eir WHERE `invoice` = ? AND `fair` = ? AND type = 'space'");
					$stmt->execute(array($invoice['id'], $invoice['fair']));
					$invoiceposname = $stmt->fetch(PDO::FETCH_ASSOC);

					$stmt2 = $this->db->prepare("SELECT eir.price FROM exhibitor_invoice_rel as eir WHERE `invoice` = ? AND `fair` = ? AND type = 'space'");
					$stmt2->execute(array($invoice['id'], $invoice['fair']));
					$invoicepospriceresult = $stmt2->fetch(PDO::FETCH_ASSOC);

					$invoice['invoiceposname'] = implode('|', $invoiceposname);
					$invoiceposprice += $invoicepospriceresult['price'];
					$invoiceposamount += 1;
					$invoices[$invoice['id']] = $invoice;
				}

				if (isset($invoice_ids)) {
					if ($_POST['invoicestatus'] == 4) {
						$stmt = $this->db->prepare("SELECT eir.price AS price FROM exhibitor_invoice_rel AS eir LEFT JOIN exhibitor_invoice_history AS ex_invoice ON eir.invoice = ex_invoice.id WHERE eir.type = 'space' AND eir.fair = ? AND ex_invoice.fair = ? AND ex_invoice.id IN (" . implode(',', $invoice_ids) . ")");
					} else {
						$stmt = $this->db->prepare("SELECT eir.price AS price FROM exhibitor_invoice_rel AS eir LEFT JOIN exhibitor_invoice AS ex_invoice ON eir.invoice = ex_invoice.id WHERE eir.type = 'space' AND eir.fair = ? AND ex_invoice.fair = ? AND ex_invoice.id IN (" . implode(',', $invoice_ids) . ")");
					}
					$stmt->execute(array($id, $id));
					$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

					$stmt = $this->db->prepare("SELECT DISTINCT(text) FROM exhibitor_invoice_rel AS eir WHERE eir.fair = ? AND eir.type = 'option' ORDER BY text ASC");
					$stmt->execute(array($id));
					$optiontexts = $stmt->fetchAll(PDO::FETCH_ASSOC);

					if (count($optiontexts) > 0) {
						foreach ($optiontexts as $opttext) {
							if ($_POST['invoicestatus'] == 4) {
								$stmt = $this->db->prepare("SELECT eir.text AS text, eir.price AS price, COUNT(amount) FROM exhibitor_invoice_rel AS eir LEFT JOIN exhibitor_invoice_history AS ex_invoice ON eir.invoice = ex_invoice.id WHERE eir.text = ? AND eir.type = 'option' AND eir.fair = ? AND ex_invoice.fair = ? AND ex_invoice.id IN (" . implode(',', $invoice_ids) . ")");
							} else {
								$stmt = $this->db->prepare("SELECT eir.text AS text, eir.price AS price, COUNT(amount) FROM exhibitor_invoice_rel AS eir LEFT JOIN exhibitor_invoice AS ex_invoice ON eir.invoice = ex_invoice.id WHERE eir.text = ? AND eir.type = 'option' AND eir.fair = ? AND ex_invoice.fair = ? AND ex_invoice.id IN (" . implode(',', $invoice_ids) . ")");
							}
							$stmt->execute(array($opttext['text'], $id, $id));
							$options[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
						}
						$this->setNoTranslate('options', $options);
					}

					$stmt = $this->db->prepare("SELECT DISTINCT(text) FROM exhibitor_invoice_rel AS eir WHERE eir.fair = ? AND eir.type = 'article'");
					$stmt->execute(array($id));
					$articletexts = $stmt->fetchAll(PDO::FETCH_ASSOC);

					if (count($articletexts) > 0) {
						foreach ($articletexts as $arttext) {
							if ($_POST['invoicestatus'] == 4) {
								$stmt = $this->db->prepare("SELECT DISTINCT(eir.text) AS text, eir.price AS price FROM exhibitor_invoice_rel AS eir LEFT JOIN exhibitor_invoice_history AS ex_invoice ON eir.invoice = ex_invoice.id WHERE eir.text = ? AND eir.type = 'article' AND eir.fair = ? AND ex_invoice.fair = ? AND ex_invoice.id IN (" . implode(',', $invoice_ids) . ")");
								$stmt2 = $this->db->prepare("SELECT eir.amount AS amount FROM exhibitor_invoice_rel AS eir LEFT JOIN exhibitor_invoice_history AS ex_invoice ON eir.invoice = ex_invoice.id WHERE eir.text = ? AND eir.type = 'article' AND eir.fair = ? AND ex_invoice.fair = ? AND ex_invoice.id IN (" . implode(',', $invoice_ids) . ")");

							} else {
								$stmt = $this->db->prepare("SELECT DISTINCT(eir.text) AS text, eir.price AS price FROM exhibitor_invoice_rel AS eir LEFT JOIN exhibitor_invoice AS ex_invoice ON eir.invoice = ex_invoice.id WHERE eir.text = ? AND eir.type = 'article' AND eir.fair = ? AND ex_invoice.fair = ? AND ex_invoice.id IN (" . implode(',', $invoice_ids) . ")");
								$stmt2 = $this->db->prepare("SELECT SUM(eir.amount) AS amount FROM exhibitor_invoice_rel AS eir LEFT JOIN exhibitor_invoice AS ex_invoice ON eir.invoice = ex_invoice.id WHERE eir.text = ? AND eir.type = 'article' AND eir.fair = ? AND ex_invoice.fair = ? AND ex_invoice.id IN (" . implode(',', $invoice_ids) . ")");
							}
							$stmt->execute(array($arttext['text'], $id, $id));
							$stmt2->execute(array($arttext['text'], $id, $id));
							$articles[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
							$articleamounts[] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
						}

						for($i = 0; $i < count($articles); $i++) {
							$arts[] = array(
								'text' => $articles[$i][0]['text'], 
								'price' => $articles[$i][0]['price'], 
								'amount' => $articleamounts[$i][0]['amount']
							);
						}

						$this->setNoTranslate('articles', $arts);
					}
					
					$this->setNoTranslate('positions', $positions);
					$this->setNoTranslate('invoices', $invoices);
					$this->setNoTranslate('invoiceposamount', $invoiceposamount);
					$this->setNoTranslate('invoiceposprice', $invoiceposprice);
				}
			}

			$this->setNoTranslate('fairId', $id);
			$this->setNoTranslate('fair', $this->Fair);
			$this->set('headline', 'Economy overview for');
			$this->set('no_result_found', 'No results found for this time period.');
			$this->set('invoice_type', 'Invoice type');
			$this->set('active', 'Active');
			$this->set('payed', 'Payed');
			$this->set('credited', 'Credited');
			$this->set('debased', 'Debased');
			$this->set('expires_from', 'Expiration date from');
			$this->set('expires_to', 'Expiration date to');
			$this->set('show_label', 'Show');
			$this->set('result_headline', 'Result');
			$this->set('tr_type', 'Type');
			$this->set('tr_amount', 'Amount');
			$this->set('tr_sum', 'Sum');
			$this->set('tr_option', 'Extra option');
			$this->set('tr_article', 'Article');
			$this->set('tr_name', 'Name');
			$this->set('result_invoices_headline', 'Invoices for result');
			$this->set('tr_id', 'ID');
			$this->set('tr_position', 'Position');
			$this->set('tr_positions', 'Positions');
			$this->set('tr_allpositions', 'All positions');
			$this->set('tr_exhibitor', 'Exhibitor');
			$this->set('tr_view', 'View');
			$this->set('tr_viewinvoice', 'View invoice');
		}
	}
}

?>
