<?php

class UserController extends Controller {

	public function index() {
		
		//echo $this->User->bCrypt('54yun2gch1', 'arrangör');
		
	}

	function overview($type=0) {

		setAuthLevel(3);

		$this->set('th_level', 'Level');
		$this->set('th_user', 'Master');
		$this->set('th_email', 'E-mail');
		$this->set('th_phone', 'Phone number');
		$this->set('th_edit', 'Edit');
		$this->set('th_delete', 'Delete');
		$this->set('headline', 'Master overview');
		$this->set('create_link', 'Create new master');
		$createLinkType = 0;
		switch(userLevel()) {

			case 4:
				$sql = "SELECT id FROM user ";
				if ((int)$type > 0) {
					$sql.= "WHERE level = ?";
					$createLinkType = $type;
				}
				$sql.= "ORDER BY level, name";
				$params = array($type);
				break;
			case 3:
				$sql= "SELECT id FROM user WHERE level = 2 AND fair = ? ORDER BY level, name";
				$params = array($_SESSION['user_fair']);
				break;
			default:
				toLogin();
				break;
		}
		$this->setNoTranslate('createLinkType', $createLinkType);

		$stmt = $this->User->db->prepare($sql);
		$stmt->execute($params);
		$res = $stmt->fetchAll();
		$users = array();
		if ($res > 0) {

			foreach ($res as $result) {
				$u = new User;
				$u->load($result['id'], 'id');
				$users[] = $u;
			}
			$this->set('users', $users);
			$this->setNoTranslate('createLinkType', $createLinkType);
		}

	}

	public function edit($id='', $level=0) {
		setAuthLevel(2);

		if ($id == '') {
			$id = $_SESSION['user_id'];
		}

		if ($id != $_SESSION['user_id'] && userLevel() != 4) {
			$user = new User;
			$user->load($id, 'id');

			if ($user->wasLoaded()) {
				if (userLevel() == 3) {
					if ($user->get('owner') != $_SESSION['user_id']) {
						toLogin();
					}
				} else {
					toLogin();
				}
			} else {
				$this->set('user_message', 'The user does not exist.');
				$halt = true;
				$this->set('error', true);
			}
		}

		if (!empty($id)) {

			if ($id == 'new') {
				$this->set('edit_headline', 'New user');

			} else {
				$this->set('edit_headline', 'Edit user');
				$this->User->load($id, 'id');
			}

			if (isset($_POST['save'])) {
				
				if (preg_match('/^new/', $id)) {
				
					$this->User->set('email', $_POST['email']);
					$this->User->set('alias', $_POST['alias']);
					
					if ($this->User->emailExists()) {
						$this->set('user_message', 'The email address already exists in our system. Please choose another one.');
						$halt = true;
						$this->set('error', true);
					}
						
					if ($this->User->aliasExists()) {
						$this->set('user_message', 'The alias already exists in our system. Please choose another one.');
						$halt = true;
						$this->set('error', true);
					}
				
				} else if ($this->User->wasLoaded()) {

					if ($this->User->get('email') != $_POST['email']) {
						$this->User->set('email', $_POST['email']);
						if ($this->User->emailExists()) {
							$this->set('user_message', 'The email address already exists in our system. Please choose another one.');
							$halt = true;
							$this->set('error', true);
						}
					} else {
						$this->User->set('email', $_POST['email']);
					}
				}

				if (!preg_match('/\d+/', $_POST['zipcode'])) {
					$this->set('user_message', 'The ZIP code should only contain digits.');
					$halt = true;
					$this->set('error', true);
				}
				
				if (!isset($halt)) { 
					$this->User->set('customer_nr', $_POST['customer_nr']);
					$this->User->set('company', $_POST['company']);
					$this->User->set('name', $_POST['name']);
					$this->User->set('orgnr', $_POST['orgnr']);
					$this->User->set('address', $_POST['address']);
					$this->User->set('zipcode', $_POST['zipcode']);
					$this->User->set('city', $_POST['city']);
					$this->User->set('country', $_POST['country']);
					$this->User->set('phone1', $_POST['phone1']);
					$this->User->set('phone2', $_POST['phone2']);
					$this->User->set('phone3', $_POST['phone3']);
					$this->User->set('fax', $_POST['fax']);
					$this->User->set('website', $_POST['website']);
					$this->User->set('invoice_company', $_POST['invoice_company']);
					$this->User->set('invoice_address', $_POST['invoice_address']);
					$this->User->set('invoice_zipcode', $_POST['invoice_zipcode']);
					$this->User->set('invoice_city', $_POST['invoice_city']);
					$this->User->set('invoice_email', $_POST['invoice_email']);
					$this->User->set('locked', $_POST['locked']);
					$this->User->set('presentation', $_POST['presentation']);

					if (preg_match('/^new/', $id)) {
						if (userLevel() == 4 && $level != 0) {
							$this->User->set('level', $level);
						}
						// Generate a pw
						$pw_arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
						shuffle($pw_arr);
						$password = substr(implode('', $pw_arr), 0, 13);
						// End of gen
	
						$this->User->setPassword($password);
						
						switch($this->User->get('level')) {
							case 4:
								$lvl = 'Master';
								break;
							case 3:
								$lvl = 'Organizer';
								break;
							case 2:
								$lvl = 'Administrator';
								break;
							default:
								$lvl = 'Exhibitor';
								break;
						}
						$str = 'Welcome to Chartbooker'."\r\n\r\n";
						$str.= 'Username: '.$_POST['alias']."\r\n";
						$str.= 'Password: '.$password."\r\n";
						$str.= 'Access level: '.$lvl;
						sendMail($_POST['email'], 'Your user account', $str);
					}
	
					$iid = $this->User->save();
					if($iid > 0){
						// Succsess
						$this->setNoTranslate('js_confirm', true);
						$this->set('js_confirm_text', 'The user '.$_POST['name'].' have been saved!');
					}else{
						// FAIL
						$this->setNoTranslate('js_confirm', true);
						$this->set('js_confirm_text', 'An error has occurred!'."\r\n".'Could not save user to database');
					}
				
				}

			}
 			
 			if ($id != 'new' && userLevel() == 4) {
 				$this->setNoTranslate('openFields', true);
 			} else {
 				$this->setNoTranslate('openFields', false);
 			}
 			
			$this->setNoTranslate('edit_id', $id);
			$this->setNoTranslate('edit_level', $level);
			$this->set('user', $this->User);
			
			$this->set('copy_label', 'Copy from company details');
			$this->set('alias_label', 'Alias');
			$this->set('company_label', 'Company');
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
			$this->set('password_label', 'Password');
			$this->set('password_repeat_label', 'Password again (repeat to confirm)');
			$this->set('presentation_label', 'Presentation');
			$this->set('save_label', 'Save');
			$this->set('invoice_company_label', 'Company');
			$this->set('invoice_address_label', 'Address');
			$this->set('invoice_zipcode_label', 'Zip code');
			$this->set('invoice_city_label', 'City');
			$this->set('invoice_email_label', 'E-mail');
			$this->set('locked_label', 'Account locked');
			$this->set('locked_label0', 'No');
			$this->set('locked_label1', 'Yes');
			

		}
	}

	function logout() {

		if (isset($_SESSION['user_fair'])) {
			$stmt = $this->db->prepare("SELECT url FROM fair WHERE `id` = ?");
			$stmt->execute(array($_SESSION['user_fair']));
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			session_unset();
			session_destroy();
			if ($result) {
				header('Location: '.BASE_URL.$result['url']);
				exit();
			} else {
				toLogin();
			}
		} else {
			session_unset();
			session_destroy();
			toLogin();
		}
		exit;

	}

	function login($fUrl='', $status = null) {
		$this->set('error', '');
		$this->setNoTranslate('fair_url', $fUrl);
		if( $status !== null){
			$this->set('first_time_msg', 'An email has been sent to the specified email addresses that were entered into during the registration prossesen');
		}
		
		if( $fUrl == 'confirmed'){
			$this->set('confirmed_msg', 'Your account has been activated. Please log in to proceed.');
		}
		
		if (isset($_POST['login'])) {

			if ($this->User->login($_POST['user'], $_POST['pass'])) {
				$_SESSION['user_id'] = $this->User->get('id');
				$_SESSION['user_level'] = $this->User->get('level');
				$_SESSION['user_password_changed'] = $this->User->get('password_changed');

				if ($fUrl != '') {
					$fair = new Fair;
					$fair->load($fUrl, 'url');
					$_SESSION['user_fair'] = $fair->get('id');
					$_SESSION['fair_windowtitle'] = $fair->get('windowtitle');

					/*
					if (userLevel() == 1) {
						
						$stmt = $this->db->prepare("SELECT fair FROM fair_user_relation WHERE user = ? AND fair = ?");
						$stmt->execute(array($this->User->get('id'), $fair->get('id')));
						$result = $stmt->fetch(PDO::FETCH_ASSOC);
						if (!$result && $fair->wasLoaded()) {
							$stmt = $this->db->prepare("INSERT INTO fair_user_relation (user, fair) VALUES (?, ?)");
							$stmt->execute(array($this->User->get('id'), $fair->get('id')));
						}
					}
					*/

				} else {
					if ($this->User->get('level') == 3) {
						$stmt = $this->db->prepare("SELECT id, windowtitle FROM fair WHERE created_by = ? ORDER BY creation_time DESC LIMIT 0,1");
						$stmt->execute(array($this->User->get('id')));
						$result = $stmt->fetch();
						$_SESSION['user_fair'] = $result['id'];
						$_SESSION['fair_windowtitle'] = $result['windowtitle'];
					} else {

						$stmt = $this->db->prepare("SELECT rel.fair, fair.windowtitle FROM fair_user_relation AS rel LEFT JOIN fair ON rel.fair = fair.id WHERE rel.user = ? ORDER BY fair.id DESC LIMIT 0,1");
						$stmt->execute(array($this->User->get('id')));
						$result = $stmt->fetch();
						$_SESSION['user_fair'] = $result['fair'];
						$_SESSION['fair_windowtitle'] = $result['windowtitle'];
					}
				}

				$timediff = time() - $this->User->get('password_changed');
				$days = $timediff/60/60/24;

				if ($days > 72) {
					header("Location: ".BASE_URL."user/changePassword/remind");
				} else {
					$fair = new Fair;
					$fair->load($_SESSION['user_fair'], 'id');
					if ($fair->wasLoaded()) {
						if (userLevel() > 1) {
							header("Location: ".BASE_URL.'mapTool/map/'.$fair->get('id'));
						} else {
							header("Location: ".BASE_URL.$fair->get('url'));
						}
					} else {
						header("Location: ".BASE_URL."page/loggedin");
					}
				}
				exit;
			} else {
				$this->set('error', 'Log in failed.');
			}

		}



		$this->set('headline', 'Log in');
		$this->set('user_name', 'Username');
		$this->set('password', 'Password');
		$this->set('button', 'Log in');
		$this->set('forgotlink', 'Forgot your password?');
		$this->set('usernamelink', 'Forgot your username?');

	}

	function changePassword($info='') {

		setAuthLevel(1);

		require_once ROOT.'application/models/Fair.php';
		require_once ROOT.'application/models/FairMap.php';
		require_once ROOT.'application/models/FairMapPosition.php';
		require_once ROOT.'application/models/Exhibitor.php';
		require_once ROOT.'application/models/PreliminaryBooking.php';
		require_once ROOT.'application/models/ExhibitorCategory.php';

		$this->set('headline', 'Change your password');
		$this->set('error', '');
		$this->set('ok', '');
		$this->set('password_label', 'New password');
		$this->set('password_repeat_label', 'Confirm new password');
		$this->set('password_old_label', 'Current password');
		$this->set('save_label', 'Save');
		$this->set('pass_standard', 'Your password has to be at least 8 characters long, contain at least 2 numeric characters and 1 capital letter.');

		if ($info == 'remind')
			$this->set('info', "It has been more than a month since you last changed your password. It is recommended that you change it now.");
		else
			$this->setNoTranslate('info', '');

		if (isset($_POST['save'])) {

			if ($_POST['password'] == $_POST['password_repeat']) {


				$this->User->load($_SESSION['user_id'], 'id');

				if ($this->User->wasLoaded()) {

					if ($this->User->login($this->User->get('alias'), $_POST['password_old'])) {
						$this->User->setPassword($_POST['password']);
						$this->User->save();
						$this->set('ok', 'Password changed');
						sendMail($this->User->email, 'Your password has been changed', "Dear user\r\n\r\nYour password has been changed.\r\n\r\nThank you for attention,\r\nChartbooker International");
					} else {
						$this->set('error', 'Your current password was wrong.');
					}

				}
			} else {
				$this->set('error', 'The passwords did not match.');
			}

		}

	}

	function resetPassword($action='', $param1='', $param2='', $param3='') {

		$this->set('error', '');
		$this->set('ok', '');

		if ($action == 'confirm') {

			$this->User->load($param1, 'id');

			if ($this->User->wasLoaded()) {

				//confirm hash is correct
				if (md5($this->User->get('email').BASE_URL.$this->User->get('id')) == $param2) {
					
					if (time() - $param3 < 60*60) {

						$arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
						shuffle($arr);
						$str = substr(implode('', $arr), 0, 13);
						
						$this->User->setPassword($str);
						$this->User->save();
						$this->setNoTranslate('new_pass', $str);

						$emstr = "Dear user,\r\n\r\nYour new password is: ".$str;
						$emstr.= "\r\n\r\nBest regards,\r\nChartbooker International";
						sendMail($this->User->email, 'Your new password', $emstr);
					} else {
						die('timeout');
					}

				} else {
					die('hash mismatch');
				}

			} else {
				die('user not found');
			}
			//$2a$12$aXQFm.9gR/JCe.HQe2285uSSep4cd0Gufg12tcEQcbs1Xwxn273tS
			//$2a$12$aXQFm.9gR/JCe.HQe2285uc/jnlCu9Lw.hRK8dbkgtgo6Azi1TAIe
		}

		if (isset($_POST['send'])) {

			$this->User->load($_POST['user'], 'alias');

			if ($this->User->wasLoaded()) {

				$hash = md5($this->User->get('email').BASE_URL.$this->User->get('id'));
				$str = "Dear user\r\n\r\nWe recieved a request to change your password. Click on the link below to set a new password:\r\n";
				$str.= BASE_URL.'user/resetPassword/confirm/'.$this->User->get('id').'/'.$hash.'/'.time()."\r\n\r\n";
				$str.= 'The link expires in 60 minutes.';
				$str.= "\r\n\r\nThanks,\r\nChartbooker International";

				sendMail($this->User->email, 'Reset password', $str);
				$this->set('ok', 'A new password has been sent to '.$this->User->email);

			} else {
				$this->set('error', 'E-mail address not found.');
			}

		}

		$this->set('headline', 'Reset password');
		$this->set('user_name', 'Username');
		$this->set('button', 'Reset');
		$this->set('forgotlink', 'Forgot your password?');

	}

	function forgotUsername() {
		setAuthLevel(0);

		if (isset($_POST['remindme'])) {
			$this->User->load($_POST['email'], 'email');
			if ($this->User->wasLoaded()) {
				sendMail($this->User->email, 'Chartbooker International', "Someone requested a username reminder for this account, if it was not you you can ignore this message.\r\n\r\nYour username is: " . $this->User->get('alias') . "\r\n\r\nBest regards\r\nChartbooker International");
				$this->set('usermessage', 'An e-mail has been sent to the provided e-mail address.');
			} else {
				$this->set('error', true);
				$this->set('usermessage', 'Sorry, we do not recognize that e-mail address.');
			}
		}

		$this->set('email', 'E-mail');
		$this->set('remindme', 'Remind me');
		$this->set('headline', 'Request username reminder');
	}

	function accountSettings() {
		setAuthLevel(1);

		$this->set('headline', 'Account settings');
		$this->User->load($_SESSION['user_id'], 'id');

		if (isset($_POST['save'])) {
			$this->User->set('company', $_POST['company']);
			$this->User->set('orgnr', $_POST['orgnr']);
			$this->User->set('address', $_POST['address']);
			$this->User->set('zipcode', $_POST['zipcode']);
			$this->User->set('city', $_POST['city']);
			if (userLevel() != 2) {
				$this->User->set('name', $_POST['name']);
				$this->User->set('invoice_address', $_POST['invoice_address']);
				$this->User->set('invoice_zipcode', $_POST['invoice_zipcode']);
				$this->User->set('invoice_city', $_POST['invoice_city']);
				$this->User->set('invoice_email', $_POST['invoice_email']);
				$this->User->set('presentation', $_POST['presentation']);
			}
			$this->User->set('country', $_POST['country']);
			$this->User->set('phone1', $_POST['phone1']);
			$this->User->set('phone2', $_POST['phone2']);
			$this->User->set('phone3', $_POST['phone3']);
			$this->User->set('fax', $_POST['fax']);
			$this->User->set('website', $_POST['website']);
			$this->User->set('email', $_POST['email']);
			$this->User->set('commodity', $_POST['commodity']);
			//$this->User->set('category', $_POST['category']);
			//$this->User->set('level', 1);

			$iid = $this->User->save();
		}

		$this->setNoTranslate('locked0sel', '');

		$this->setNoTranslate('edit_id', $_SESSION['user_id']);
		$this->set('user', $this->User);

		$this->set('copy_label', 'Copy from company details');
		$this->set('alias_label', 'Username');
		$this->set('category_label', 'Category');
		$this->set('commodity_label', 'Commodity');

		$this->set('company_section', 'Company');
		$this->set('invoice_section', 'Billing address');
		$this->set('contact_section', 'Contact person');
		$this->set('presentation_section', 'Presentation');

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
	}

	function register($fairUrl='') {

		$error = '';

		if (isset($_POST['save'])) {

			$this->User->set('company', $_POST['company']);
			$this->User->set('name', $_POST['name']);
			$this->User->set('orgnr', $_POST['orgnr']);
			$this->User->set('alias', $_POST['username']);
			$this->User->set('address', $_POST['address']);
			$this->User->set('zipcode', $_POST['zipcode']);
			$this->User->set('city', $_POST['city']);
			$this->User->set('country', $_POST['country']);
			$this->User->set('phone1', $_POST['phone1']);
			$this->User->set('phone2', $_POST['phone2']);
			$this->User->set('phone3', $_POST['phone3']);
			$this->User->set('fax', $_POST['fax']);
			$this->User->set('website', $_POST['website']);
			$this->User->set('email', $_POST['email']);
			$this->User->set('presentation', $_POST['presentation']);
			$this->User->set('invoice_company', $_POST['invoice_company']);
			$this->User->set('invoice_address', $_POST['invoice_address']);
			$this->User->set('invoice_zipcode', $_POST['invoice_zipcode']);
			$this->User->set('invoice_city', $_POST['invoice_city']);
			$this->User->set('invoice_email', $_POST['invoice_email']);
			$this->User->set('commodity', $_POST['commodity']);
			//$this->User->set('category', $_POST['category']);
			$this->User->set('level', 1);
			$this->User->set('locked', 1);



			if (!preg_match('/\d{3}(\s|\-)?\d+/', $_POST['zipcode'])) {
				$error.= 'The ZIP code should be in the format xxx-xx';
			} else if ($this->User->aliasExists()) {
				$error.= 'The username already exists in our system.';
			} else if ($this->User->emailExists()) {
				$error.= 'The email address already exists in our system.';
			} else {
				
				if (strlen($_POST['username']) > 3) {
					
					if ($_POST['password'] == $_POST['password_repeat']) {
						$this->User->setPassword($_POST['password']);
						$userId = $this->User->save();
						$hash = md5($this->User->get('email').BASE_URL.$userId);
						$url = BASE_URL.'user/confirm/'.$userId.'/'.$hash;
						sendMail($this->User->email, 'Welcome to Chartbooker', "Welcome to Chartbooker\r\n\r\nTo confirm your account, click here: ".$url."\r\n\r\nBest regards\r\nChartbooker International");
						if ($fairUrl != '') {
							require_once ROOT.'application/models/Exhibitor.php';
							require_once ROOT.'application/models/ExhibitorCategory.php';
							require_once ROOT.'application/models/Fair.php';
							require_once ROOT.'application/models/FairMap.php';
							require_once ROOT.'application/models/FairMapPosition.php';
							require_once ROOT.'application/models/PreliminaryBooking.php';
							require_once ROOT.'application/models/FairUserRelation.php';
							$fair = new Fair;
							$fair->load($fairUrl, 'url');
							if ($fair->wasLoaded()) {
								$ful = new FairUserRelation;
								$ful->set('user', $userId);
								$ful->set('fair', $fair->get('id'));
								$ful->save();
							}
						}
						header('Location: '.BASE_URL.'user/login/'.$fairUrl.'/new');
					} else {
						$error.= 'The passwords did not match.';
					}
					
				} else {
					$error.= 'Your alias must contain at least four characters.';
				}
			}
		}

		$this->set('error', $error);
		$this->setNoTranslate('fair_url', $fairUrl);
		$this->set('user', $this->User);
		$fair = new Fair($this->User->db);
		$fair->load($_SESSION['outside_fair_url'], 'url');
		$this->set('fair', $fair);
		
		$this->set('pass_standard', 'Your password has to be at least 8 characters long, contain at least 2 numeric characters and 1 capital letter.');
		$this->set('copy_label', 'Copy from company details');
		$this->set('company_section', 'Company');
		$this->set('invoice_section', 'Billing address');
		$this->set('contact_section', 'Contact');
		$this->set('presentation_section', 'Presentation');

		$this->set('alias_label', 'Username');

		$this->set('headline', 'Register');
		$this->set('company_label', 'Company');
		$this->set('commodity_label', 'Commodity');
		$this->set('presentation_label', 'Presentation');
		$this->set('category_label', 'Category');
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
		$this->set('password_label', 'Password');
		$this->set('password_repeat_label', 'Password again (repeat to confirm)');
		$this->set('save_label', 'Save');
	}

	function confirm($user, $hash) {

		$this->User->load($user, 'id');
		if ($this->User->wasLoaded()) {
			$userHash = md5($this->User->get('email').BASE_URL.$this->User->get('id'));
			if ($userHash == $hash && $this->User->get('locked') > 0) {
				$this->User->set('locked', 0);
				$this->User->save();
				header('Location: '.BASE_URL.'user/login/confirmed');
				exit;
			} else {
				echo $hash.'<br/>'.$userHash;
			}

		}

	}

	public function delete($id, $confirmed='') {

		setAuthLevel(4);

		$this->set('headline', 'Delete super user');

		if ($confirmed == 'confirmed') {
			$this->User->load($id, 'id');
			$this->User->delete();
			header("Location: ".BASE_URL."user/overview/4");
			exit;
		} else {
			$this->setNoTranslate('user_id', $id);
			$this->set('warning', 'Do you really want to delete this super user?');
			$this->set('yes', 'Yes');
			$this->set('no', 'No');
		}

	}

	public function resendDetails($id) {

		setAuthLevel(4);

		$this->User->load($id, 'id');

		if ($this->User->wasLoaded()) {
			$arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
			shuffle($arr);
			$str = substr(implode('', $arr), 0, 13);
			
			$this->User->setPassword($str);
			$this->User->save();

			$str = 'Welcome to Chartbooker'."\r\n\r\n";
			$str.= 'Username: '.$this->User->get('alias')."\r\n";
			$str.= 'Password: '.$str."\r\n";

			sendMail($this->User->get('email'), 'Your user account', $str);

			$this->set('user_message', 'The user\'s password was reset and a mail was sent.');
		} else {
			$this->set('error_message', 'That user does not exist.');
		}
	}

}

?>