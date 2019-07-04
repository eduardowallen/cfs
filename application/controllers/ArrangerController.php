<?php
class ArrangerController extends Controller {

	function overview() {

		setAuthLevel(4);

		$this->set('headline', 'Organizer overview');
		$this->set('create_link', 'Create new organizer');

		$this->set('th_eventcount', 'Events');
		$this->set('th_spots_free', 'Free stand spaces');
		$this->set('th_spots_booked', 'Booked stand spaces');
		$this->set('th_lastlogin', 'Last login');
		$this->set('th_user', 'User');
		$this->set('th_edit', 'Edit');
		$this->set('th_delete', 'Delete');

		$sql = "SELECT id FROM user WHERE level = ? ORDER BY name";

		$stmt = $this->Arranger->db->prepare($sql);
		$stmt->execute(array(3));
		$res = $stmt->fetchAll();
		$users = array();
		if ($res > 0) {

			$spots = array();
			foreach ($res as $result) {
				$u = new User;
				$u->load($result['id'], 'id');

				$stmt = $this->Arranger->db->prepare("SELECT COUNT(*) AS eventcount FROM fair WHERE created_by = ?");
				$stmt->execute(array($u->get('id')));
				$res = $stmt->fetch();
				$u->set('event_count', $res['eventcount']);

				$stmt = $this->Arranger->db->prepare("SELECT pos.* FROM fair
													LEFT JOIN fair_map AS map ON fair.id = map.fair
													LEFT JOIN fair_map_position AS pos ON map.id = pos.map
													WHERE fair.created_by = ?");

				$stmt->execute(array($u->get('id')));
				$res = $stmt->fetchAll();
				$spots[$u->get('id')] = array('open'=>0, 'booked'=>0);
				foreach ($res as $r) {
					if ($r['status'] == 0)
						$spots[$u->get('id')]['open']++;
					else
						$spots[$u->get('id')]['booked']++;

				}



				$users[] = $u;
			}
			$this->setNoTranslate('spots', $spots);
			$this->setNoTranslate('users', $users);
		}

	}

	function info($id) {

		setAuthLevel(4);

		if (isset($_SESSION['mail_errors']) && !empty($_SESSION['mail_errors'])) {
			$this->setNoTranslate('mail_errors', $_SESSION['mail_errors']);
			$this->setNoTranslate('error_title', 'An error occured');
			$_SESSION['mail_errors'] = '';
		} else {
			$this->setNoTranslate('mail_errors', '');
		}
		if (isset($_SESSION['mail_success']) && !empty($_SESSION['mail_success'])) {
			$this->setNoTranslate('mail_success', 1);
			$this->set('emails_sent', 'The organizer was created and welcome email was sent.');
			$this->set('success_title', 'Success');
			$_SESSION['mail_success'] = '';
		} else {
			$this->setNoTranslate('mail_success', '');
		}

		$this->setNoTranslate('user_id', $id);
		$this->Arranger->load($id, 'id');
		$this->setNoTranslate('user', $this->Arranger);

		$sql = "SELECT fair.id, fair.name, fair.approved, fair.url, fair.page_views, fair.creation_time, fair.event_start, fair.event_stop,
				SUM(IF(fmp.status=0, 1, 0)) AS free_spaces, SUM(IF(fmp.status>0, 1, 0)) AS occupied_spaces
				FROM fair
				LEFT JOIN fair_map AS fm ON (fm.fair = fair.id)
				LEFT JOIN fair_map_position AS fmp ON (fmp.map = fm.id)
				WHERE fair.created_by = ?
				GROUP BY fair.id
				ORDER BY creation_time DESC";
		$stmt = $this->Arranger->db->prepare($sql);
		$stmt->execute(array($id));
		$res = $stmt->fetchAll();
		$fairs = array();

		$sum_booked = 0;
		$sum_free = 0;
		$num_events = 0;
		if ($res > 0) {
			foreach ($res as $result) {
				$fair_id = $result['id'];
				$fairs[$fair_id] = $result;
				$sum_free += $result['free_spaces'];
				$sum_booked += $result['occupied_spaces'];
				$num_events++;
			}
		}
		$this->setNoTranslate('fairs', $fairs);
		$this->setNoTranslate('total_booked', $sum_booked);
		$this->setNoTranslate('total_free', $sum_free);
		$this->setNoTranslate('num_events', $num_events);

		$labels = array('header' => 'Profile for', 'company' => 'Company', 'num_events' => 'Number of events', 'total_booked' => 'Total booked places',
			'last_login' => 'Last login', 'name' => 'Contact person', 'orgnr' => 'Organization number', 'zipcode' => 'Zip/Postal Code', 'city' => 'City', 'country' => 'Country', 'phone1' => 'Phone 1',
			'phone2' => 'Phone 2', 'phone3' => 'Phone 3', 'website' => 'Website', 'email' => 'E-mail', 'fair_name' => 'Event name', 'fair_approved' => 'Status', 'fair_url' => 'Website',
			'fair_page_views' => 'Number of visitors', 'address' => 'Address', 'fair_occupied_spaces' => 'Number of occupied spaces', 'fair_free_spaces' => 'Number of free spaces',
			'fair_creation_time' => 'Creation time', 'fair_event_start' => 'Event start', 'fair_event_stop' => 'Event stop', 'total_free' => 'Total free places', 'alias'=>'Username');
		foreach ($labels as $key => $value) {
			$this->setNoTranslate('label_' . $key, $value);// do this to make it add a field to the translation list, adding only the array does not make it show up in translation.
		}
		$this->setNoTranslate('labels', $labels);
		$this->set('approved_active', 'Active');
		$this->set('approved_inactive', 'Inactive');
		$this->set('approved_locked', 'Locked');
		
	}

	public function accountSettings() {
		setAuthLevel(3);

		$this->set('headline', 'Account settings');
		$this->Arranger->load($_SESSION['user_id'], 'id');

		if (isset($_POST['save'])) {
			$this->Arranger->set('company', $_POST['company']);
			$this->Arranger->set('name', $_POST['name']);
			$this->Arranger->set('orgnr', $_POST['orgnr']);
			$this->Arranger->set('address', $_POST['address']);
			$this->Arranger->set('zipcode', $_POST['zipcode']);
			$this->Arranger->set('city', $_POST['city']);
			$this->Arranger->set('country', $_POST['country']);
			$this->Arranger->set('phone1', $_POST['phone1']);
			$this->Arranger->set('phone2', $_POST['phone2']);
			$this->Arranger->set('phone3', $_POST['phone3']);
			$this->Arranger->set('website', $_POST['website']);
			$this->Arranger->set('email', $_POST['email']);
			$this->Arranger->set('level', 3);

			if ($this->Arranger->emailExists()) {
				$this->set('user_message', 'The email address already exists in our system.');
				$this->setNoTranslate('error', true);
			} else {
				$iid = $this->Arranger->save();
			}
		}

		$this->setNoTranslate('locked0sel', '');

		$this->setNoTranslate('edit_id', $_SESSION['user_id']);
		$this->setNoTranslate('user', $this->Arranger);

		$this->set('alias_label', 'Username');
		$this->set('company_label', 'Company');
		$this->set('contact_label', 'Contact');
		$this->set('orgnr_label', 'Organization number');
		$this->set('address_label', 'Address');
		$this->set('zipcode_label', 'Zip code');
		$this->set('city_label', 'City');
		$this->set('country_label', 'Country');
		$this->set('phone1_label', 'Phone 1');
		$this->set('phone2_label', 'Phone 2');
		$this->set('phone3_label', 'Phone 3');
		$this->set('website_label', 'Website');
		$this->set('email_label', 'E-mail');
		$this->set('save_label', 'Save');

	}

	public function delete($id) {

		setAuthLevel(4);

		$this->Arranger->load($id, 'id');
		$this->Arranger->delete();
		header('Location: '.BASE_URL.'arranger/overview');
		exit;

	}

	public function edit($id=0) {

		setAuthLevel(4);

		if (empty($id))
      		return;

	    if ($id != 'new') {
	      $this->Arranger->load($id, 'id');
	    }

	    $this->setNoTranslate('locked0sel', '');

	    $this->setNoTranslate('edit_id', $id);
	    $this->setNoTranslate('user', $this->Arranger);
	    
	    $sql = "SELECT fair.id, fair.name, fair.max_positions, fair.approved, fair.url, fair.page_views, fair.creation_time, fair.event_start, fair.event_stop,
				SUM(IF(fmp.status=0, 1, 0)) AS free_spaces, SUM(IF(fmp.status>0, 1, 0)) AS occupied_spaces
				FROM fair
				LEFT JOIN fair_map AS fm ON (fm.fair = fair.id)
				LEFT JOIN fair_map_position AS fmp ON (fmp.map = fm.id)
				WHERE fair.created_by = ?
				GROUP BY fair.id
				ORDER BY creation_time DESC";
	    $stmt = $this->Arranger->db->prepare($sql);
	    $stmt->execute(array($id));
	    $res = $stmt->fetchAll();
	    $fairs = array();

	    $sum_booked = 0;
	    $sum_free = 0;
	    $num_events = 0;
	    if ($res > 0) {
	      foreach ($res as $result) {
	        $fair_id = $result['id'];
	        $fairs[$fair_id] = $result;
	        $sum_free += $result['free_spaces'];
	        $sum_booked += $result['occupied_spaces'];
	        $num_events++;
	      }
	    }
	    $this->setNoTranslate('fairs', $fairs);
	    $this->setNoTranslate('total_booked', $sum_booked);
	    $this->setNoTranslate('total_free', $sum_free);
	    $this->setNoTranslate('num_events', $num_events);

	    if (isset($_POST['save'])) {
	      
	      // Company Section
	      $this->Arranger->set('orgnr', $_POST['orgnr']);
	      $this->Arranger->set('company', $_POST['company']);
	      $this->Arranger->set('commodity', $_POST['commodity']);
	      $this->Arranger->set('address', $_POST['address']);
	      $this->Arranger->set('zipcode', $_POST['zipcode']);
	      $this->Arranger->set('city', $_POST['city']);
	      $this->Arranger->set('country', $_POST['country']);
	      $this->Arranger->set('phone1', $_POST['phone1']);
	      $this->Arranger->set('phone2', $_POST['phone2']);
	      $this->Arranger->set('email', $_POST['email']);
	      $this->Arranger->set('website', $_POST['website']);
	      
	      // Invoice Section
	      $this->Arranger->set('invoice_company', $_POST['invoice_company']);
	      $this->Arranger->set('invoice_address', $_POST['invoice_address']);
	      $this->Arranger->set('invoice_zipcode', $_POST['invoice_zipcode']);
	      $this->Arranger->set('invoice_city', $_POST['invoice_city']);
	      $this->Arranger->set('invoice_country', $_POST['invoice_country']);
	      $this->Arranger->set('invoice_email', $_POST['invoice_email']);
	      $this->Arranger->set('presentation', $_POST['presentation']);
	      
	      // Contact Section
	      // This field is only editable when creating new arranger and is taken care of further on in the code
	      //$this->Arranger->set('alias', $_POST['alias']);
	      $this->Arranger->set('name', $_POST['name']);
	      $this->Arranger->set('contact_phone', $_POST['phone3']);
	      $this->Arranger->set('contact_phone2', $_POST['phone4']);
	      $this->Arranger->set('contact_email', $_POST['contact_email']);
	      
	      $this->Arranger->set('level', 3);
	      $this->Arranger->set('locked', $_POST['locked']);


	      if ($id == 'new') {
	        $this->Arranger->set('alias', $_POST['alias']);
	        
	        if ($this->Arranger->aliasExists()) {
	          $this->set('user_message', 'The username already exists in our system.');
	          $this->setNoTranslate('error', true);
	          return;
	        }

	        if ($this->Arranger->emailExists()) {
	          $this->set('user_message', 'The email address already exists in our system.');
	          $this->setNoTranslate('error', true);
	          return;
	        }

	        $pw_arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
	        shuffle($pw_arr);
	        $password = substr(implode('', $pw_arr), 0, 13);

	        $this->Arranger->setPassword($password);
	        $id = $this->Arranger->save();
	        
			$me = new User();
			$me->load($_SESSION['user_id'], 'id');
			/* Prepare to send the mail */
			$from = array(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
			$recipient = array($_POST['contact_email'], $_POST['name']);
			/* UPDATED TO FIT MAILJET */
			$mail = new Mail();
			$mail->setTemplate('new_account');
			$mail->setFrom($from);
			$mail->setRecipient($recipient);
			/* Setting mail variables */
			$mail->setMailVar('exhibitor_company', $_POST['company']);
			$mail->setMailVar('username', $_POST['alias']);
			$mail->setMailVar('password', $password);
			$mail->setMailVar('accesslevel', $this->translate->{'Organizer'});
			$mail->sendMessage();

	        /* Redirect to organizer's new profile */
        	header('Location: '.BASE_URL.'arranger/info/'.$id);
	        exit;
	        
	      } else {
	        if ($this->Arranger->get('email') != $_POST['email'] && $this->Arranger->emailExists($_POST['email'])) {
	          $this->set('user_message', 'The email address already exists in our system.');
	          $this->setNoTranslate('error', true);
	          return;
	        }
	      }
	      /* All went well, save changes */
	      $this->Arranger->save();
	      
	      //header('Location: '.BASE_URL.'arranger/overview');
	      //exit;
	    }

	}

}

?>
