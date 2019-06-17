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
		$this->set('th_lastlogin', 'Last login');
		$this->set('th_created', 'Created');
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
			$this->setNoTranslate('users', $users);
			$this->setNoTranslate('createLinkType', $createLinkType);
		}
	}

	public function edit($id='', $level=0) {

		setAuthLevel(2);

		if (empty($id))
    	  return;

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
				$this->setNoTranslate('error', true);
			}
		}
    
    if ($id != 'new') {

      $this->User->load($id, 'id');
    } 

    if (isset($_POST['save'])) {
      if (preg_match('/^new/', $id)) {
      
        $this->User->set('email', $_POST['email']);
        $this->User->set('alias', $_POST['alias']);
        
        if ($this->User->emailExists()) {

          $this->set('user_message', 'The email address already exists in our system. Please choose another one.');
          $halt = true;
          $this->setNoTranslate('error', true);
        }
          
        if ($this->User->aliasExists()) {

          $this->set('user_message', 'The alias already exists in our system. Please choose another one.');
          $halt = true;
          $this->setNoTranslate('error', true);
        }
      
      } else if ($this->User->wasLoaded()) {
      
        $this->User->set('email', $_POST['email']);

        if ($this->User->get('email') != $_POST['email']) {
          if ($this->User->emailExists()) {
          
            $this->set('user_message', 'The email address already exists in our system. Please choose another one.');
            $halt = true;
            $this->setNoTranslate('error', true);
          }
        }
      }
      
      if (!isset($halt)) { 
      
        // Company section
        $this->User->set('orgnr', $_POST['orgnr']);
        $this->User->set('company', $_POST['company']);
        $this->User->set('commodity', $_POST['commodity']);
        $this->User->set('address', $_POST['address']);
        $this->User->set('zipcode', $_POST['zipcode']);
        $this->User->set('city', $_POST['city']);
        $this->User->set('country', $_POST['country']);
        $this->User->set('phone1', $_POST['phone1']);
        $this->User->set('phone2', $_POST['phone2']);
        // Email is handled in the code above
        $this->User->set('website', $_POST['website']);
		$this->User->set('facebook', $_POST['facebook']);
		$this->User->set('twitter', $_POST['twitter']);
		$this->User->set('google_plus', $_POST['google_plus']);
		$this->User->set('youtube', $_POST['youtube']);
        
        // Billing address section
        $this->User->set('invoice_company', $_POST['invoice_company']);
        $this->User->set('invoice_address', $_POST['invoice_address']);
        $this->User->set('invoice_zipcode', $_POST['invoice_zipcode']);
        $this->User->set('invoice_city', $_POST['invoice_city']);
        $this->User->set('invoice_country', $_POST['invoice_country']);
        $this->User->set('invoice_email', $_POST['invoice_email']);
        $this->User->set('presentation', $_POST['presentation']);
        
        // Contact section
        // Alias field is disabled and should not be changed
        $this->User->set('name', $_POST['name']);
        $this->User->set('contact_phone', $_POST['phone3']);
        $this->User->set('contact_phone2', $_POST['phone4']);
        $this->User->set('contact_email', $_POST['contact_email']);
        
        if(isset($_POST['locked']))
          $this->User->set('locked', $_POST['locked']);

		$errors = array();
		$mail_errors = array();

        if (preg_match('/^new/', $id)) {
          if (userLevel() == 4 && $level != 0)
            $this->User->set('level', $level);
          
          // Generate a pw
          $pw_arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
          shuffle($pw_arr);
          $password = substr(implode('', $pw_arr), 0, 13);
          // End of gen

          $this->User->setPassword($password);
          
          switch($this->User->get('level')) :
            case 4: $lvl = 'Master'; break;
            case 3: $lvl = 'Organizer'; break;
            case 2: $lvl = 'Administrator'; break;
            default: $lvl = 'Exhibitor'; break;
          endswitch;

			$email = EMAIL_FROM_ADDRESS;
			$from = array($email => EMAIL_FROM_NAME);

			$recipients = array($_POST['contact_email'] => $_POST['name']);
			if (userLevel() > 1 && $level != 0) {
				$me = new User;
				$me->load($_SESSION['user_id'], 'id');

				try {
					$mail = new Mail();
					$mail->setTemplate('new_account');
					$mail->setPlainTemplate('new_account');
					$mail->setFrom($from);
					$mail->addReplyTo(EMAIL_FROM_NAME, $email);
					$mail->setRecipients($recipients);
					$mail->setMailvar('exhibitor_name', $_POST['name']);
					$mail->setMailVar('alias', $_POST['alias']);
					$mail->setMailVar('password', $password);
					$mail->setMailVar('accesslevel', $this->translate->{$lvl});
					//$mail->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
					//$mail->setMailVar('creator_name', $me->get('name'));
					if(!$mail->send()) {
						$errors[] = $_POST['company'];
					}
				} catch(Swift_RfcComplianceException $ex) {
					// Felaktig epost-adress
					$errors[] = $_POST['company'];
					$mail_errors[] = $ex->getMessage();

				} catch(Exception $ex) {
					// Okänt fel
					$errors[] = $_POST['company'];
					$mail_errors[] = $ex->getMessage();
				}
			} else {
				try {
					$mail = new Mail();
					$mail->setTemplate('welcome');
					$mail->setPlainTemplate('welcome');
					$mail->setFrom($from);
					$mail->addReplyTo(EMAIL_FROM_NAME, $email);
					$mail->setRecipients($recipients);
					$mail->setMailvar('user_name', $_POST['name']);
					$mail->setMailVar('alias', $_POST['alias']);
					$mail->setMailVar('password', $password);
					$mail->setMailVar('accesslevel', $lvl);
					if(!$mail->send()) {
						$errors[] = $_POST['company'];
					}
				} catch(Swift_RfcComplianceException $ex) {
					// Felaktig epost-adress
					$errors[] = $_POST['company'];
					$mail_errors[] = $ex->getMessage();

				} catch(Exception $ex) {
					// Okänt fel
					$errors[] = $_POST['company'];
					$mail_errors[] = $ex->getMessage();
				}
			}
        }

        $iid = $this->User->save();
        
        if($iid > 0){
        
          // Success
          $this->setNoTranslate('js_confirm', true);
          $this->setNoTranslate('js_confirm_text', 'The user '.$_POST['name'].' have been saved!');
          
        } else {
        
          // FAIL
          $this->setNoTranslate('js_confirm', true);
          $this->set('js_confirm_text', 'An error has occurred!'."\r\n".'Could not save user to database');
          if ($errors || $mail_errors) {
          	$this->setNoTranslate('user_message', $errors.'<br>'.$mail_errors);
          }
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
    $this->setNoTranslate('user', $this->User);
	}

	function logout() {

		if (isset($_SESSION['user_fair'])) {
			$stmt = $this->db->prepare("SELECT url FROM fair WHERE `id` = ?");
			$stmt->execute(array($_SESSION['user_fair']));
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$stmt2 = $this->db->prepare("UPDATE `user` SET `online` = 0 WHERE `id` = ?");
			$stmt2->execute(array($_SESSION['user_id']));
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
		$this->set('page_title', 'Login');
		global $translator;
		unset($_SESSION['visitor']);
		if (isset($_SESSION['user_id'])) :
			 header("Location: ".BASE_URL."start/home"); 
		endif;
		$this->setNoTranslate('error', '');
		$this->setNoTranslate('fair_url', $fUrl);
		if( $status !== null){
			$this->set('first_time_msg', $translator->{'Your account was successfully created.<br>You may now log in.'});
			$this->set('first_time_title', $translator->{'Welcome to Chartbooker'});
			/*$this->set('first_time_title', $translator->{'Activate your account to login'});
			$this->set('first_time_email_msg', $translator->{'An activation e-mail from Chartbooker has been sent to '}.$_SESSION['m']);
			$this->set('first_time_msg', $translator->{'To finalize the registration process, press the activation link inside. If you cannot find your email in your inbox, please check your junkbox.'});*/
			$_SESSION['m'] = "";
		}
		
		if( $fUrl == 'confirmed'){
			$this->set('confirmed_msg', $translator->{'Your account has been activated. Please log in to proceed.'});
		}
    elseif( $fUrl == 'alreadyactivated' ) {
      $this->set('confirmed_msg', $translator->{'Your account has already been activated.'});
    }

		if( $fUrl == 'ok') :
			$this->setNoTranslate('good', 'yes');
			$this->setNoTranslate('res_msg', $translator->{'A new password has been sent to '}.$_SESSION['m']);
			$_SESSION['m'] = "";

		elseif($fUrl == 'err') :
			$this->setNoTranslate('good', 'no');
			$this->set('res_msg', $translator->{'E-mail address or Username not found.'});
		endif;
		
		if(!empty($_SESSION['error'])) :
			if($_SESSION['error'] == true) :
				$this->set('error', 'Log in failed.');
				$_SESSION['error'] = false;
			endif;
		endif;

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

					/* // Old automatic connecting of exhibitors to the fair they logged on to without notice to the exhibitor.
					if (userLevel() == 1) {
						
						$stmt = $this->db->prepare("SELECT fair FROM fair_user_relation WHERE user = ? AND fair = ?");
						$stmt->execute(array($this->User->get('id'), $fair->get('id')));
						$result = $stmt->fetch(PDO::FETCH_ASSOC);
						if (!$result && $fair->wasLoaded()) {
							$stmt = $this->db->prepare("INSERT INTO fair_user_relation (user, fair, connected_time) VALUES (?, ?, ?)");
							$stmt->execute(array($this->User->get('id'), $fair->get('id'), time()));
						}
					}
					*/

				} else {
					if (!empty($_POST["outside_fair_url"])) {
						$stmt = $this->db->prepare("SELECT `id`, `windowtitle` FROM `fair` WHERE `url` = ? LIMIT 0, 1");
						$stmt->execute(array($_POST["outside_fair_url"]));
						$result = $stmt->fetch(PDO::FETCH_ASSOC);

						$_SESSION["user_fair"] = $result["id"];
						$_SESSION["fair_windowtitle"] = $result["windowtitle"];
					} else if (!empty($_COOKIE[$_SESSION["user_id"] . "_last_fair"])) {
						$stmt = $this->db->prepare("SELECT `windowtitle` FROM `fair` WHERE `id` = ? LIMIT 0, 1");
						$stmt->execute(array($_COOKIE[$_SESSION["user_id"] . "_last_fair"]));
						$result = $stmt->fetch(PDO::FETCH_ASSOC);

						$_SESSION["user_fair"] = $_COOKIE[$_SESSION["user_id"] . "_last_fair"];
						$_SESSION["fair_windowtitle"] = $result["windowtitle"];
					} else if ($this->User->get('level') == 3) {
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

				/*$timediff = time() - $this->User->get('password_changed');
				$days = $timediff/60/60/24;*/

				//if ($days > 72) {
				//	header("Location: ".BASE_URL."user/changePassword/remind");
				//} else {
				/*$fair = new Fair;
				$fair->load($_SESSION['user_fair'], 'id');

				if ($fair->wasLoaded()) {
					if (userLevel() > 1) {
						//$redirect_url = BASE_URL.'mapTool/map/'.$fair->get('id');
						$redirect_url = BASE_URL.'start/home';
					} else {
						$redirect_url = BASE_URL.'start/home';
						//$redirect_url = BASE_URL.$fair->get('url');
					}
				} else {
					$redirect_url = BASE_URL."start/home";
				}
				*/
				$redirect_url = BASE_URL."start/home";
				$user_terms = 'version:'.USER_TERMS;
				$user_pub = 'version:'.USER_PUB;
				// Check if user has approved the current User Terms
				// (Master level users don't have to approve anything)
				if (strpos($this->User->get('terms'), $user_terms) || $this->User->get('level') == 4) {
					$_SESSION['user_terms_approved'] = true;
				} else {
					// User terms NOT approved!
					$redirect_url = BASE_URL . 'user/terms?next=' . $redirect_url;
					$_SESSION['user_terms_approved'] = false;

					if ($this->is_ajax) {
						$this->createJsonResponse();
						$this->setNoTranslate('redirect', $redirect_url);
						return;
					} else {
						header("Location: " . $redirect_url);
					}
				}
				if (strpos($this->User->get('pub'), $user_pub) && $this->User->get('level') == 3 || $this->User->get('level') == 4) {
					$_SESSION['user_pub_approved'] = true;
				} else {
					// User PUB NOT approved!
					$redirect_url = BASE_URL . 'user/pub?next=' . $redirect_url;
					$_SESSION['user_pub_approved'] = false;

					if ($this->is_ajax) {
						$this->createJsonResponse();
						$this->setNoTranslate('redirect', $redirect_url);
						return;
					} else {
						header("Location: " . $redirect_url);
					}
				}

				if ($this->is_ajax) {
					$this->createJsonResponse();
					$this->setNoTranslate('redirect', $redirect_url);
					return;

				} else {
					header("Location: " . $redirect_url);
				}
				//}

				exit;

			} else {
				if ($this->is_ajax) {
					$this->createJsonResponse();
					$this->set('error', 'Log in failed.');
					return;

				} else {
					header("Location: ".BASE_URL."user/login");
					$_SESSION['error'] = true;
				}
			}
		}

		$this->setNoTranslate('headline', $translator->{'Log in'});
		$this->setNoTranslate('user_name', $translator->{'Username'});
		$this->setNoTranslate('password', $translator->{'Password'});
		$this->setNoTranslate('button', $translator->{'Log in'});
		$this->setNoTranslate('forgotlink', $translator->{'Forgot your password or Username?'});

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
		$this->setNoTranslate('error', '');
		$this->setNoTranslate('ok', '');
		$this->set('password_label', 'New password');
		$this->set('password_repeat_label', 'Confirm new password');
		$this->set('password_old_label', 'Current password');
		$this->set('save_label', 'Save');
		$this->set('pass_standard', 'Your password has to be at least 8 characters long, contain at least 2 numeric characters and 1 capital letter.');
		$this->setNoTranslate('info', '');
/*
		if ($info == 'remind')
			$this->set('info', "It has been more than a month since you last changed your password. It is recommended that you change it now.");
		else
			$this->setNoTranslate('info', '');*/
			
	$time_now = date('d-m-Y H:i');
	
		if (isset($_POST['save'])) {

			if ($_POST['password'] == $_POST['password_repeat']) {

				$this->User->load($_SESSION['user_id'], 'id');

				if ($this->User->wasLoaded()) {

					if ($this->User->login($this->User->get('alias'), $_POST['password_old'])) {

						$this->User->setPassword($_POST['password']);
						$this->User->save();
						$this->set('ok', 'Password changed');
						$email = EMAIL_FROM_ADDRESS;
						$from = array($email => EMAIL_FROM_NAME);

						$recipients = array($this->User->get('contact_email') => $this->User->get('name'));
						try {
							$mail = new Mail();
							$mail->setTemplate('password_changed');
							$mail->setPlainTemplate('password_changed');
							$mail->setFrom($from);
							$mail->addReplyTo(EMAIL_FROM_NAME, $email);
							$mail->setRecipients($recipients);
							$mail->setMailVar('user_name', $this->User->get('name'));
							$mail->setMailVar('edit_time', $time_now);
							if(!$mail->send()) {
								$errors[] = $this->User->get('email');
							} else {
								$this->set('usermessage', 'An e-mail has been sent to the provided e-mail address.');
							}
						} catch(Swift_RfcComplianceException $ex) {
							// Felaktig epost-adress
							$errors[] = $this->User->get('email');
							$mail_errors[] = $ex->getMessage();

						} catch(Exception $ex) {
							// Okänt fel
							$errors[] = $this->User->get('email');
							$mail_errors[] = $ex->getMessage();
						}

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

		$this->setNoTranslate('error', '');
		$this->setNoTranslate('ok', '');
		/*
		if ($action == 'confirm') {
			$this->User->load($param1, 'id');
			if ($this->User->wasLoaded()) {
				//confirm hash is correct
				if (md5($this->User->get('email').BASE_URL.$this->User->get('id')) == $param2) {
					if (time() - $param3 < 60*60) {
						$arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
						shuffle($arr);
						$str = substr(implode('', $arr), 0, 13);
						$email = EMAIL_FROM_ADDRESS;
						$from = array($email => EMAIL_FROM_NAME);

						$recipients = array($this->User->get('email') => $this->User->get('email'));
						$this->User->setPassword($str);
						$this->User->save();
						$this->setNoTranslate('new_pass', $str);

						try {
							$mail = new Mail();
							$mail->setTemplate('password_reset');
							$mail->setPlainTemplate('password_reset');
							$mail->setFrom($from);
							$mail->addReplyTo(EMAIL_FROM_NAME, $email);
							$mail->setRecipients($recipients);
							$mail->setMailVar('alias', $this->User->get('alias'));
							$mail->setMailVar('password', $str);
							$mail->setMailVar('exhibitor_name', $this->User->get('name'));
							if(!$mail->send()) {
								$errors[] = $this->User->get('email');
							} else {
								$this->set('usermessage', 'An e-mail has been sent to the provided e-mail address.');
							}
						} catch(Swift_RfcComplianceException $ex) {
							// Felaktig epost-adress
							$errors[] = $this->User->get('email');
							$mail_errors[] = $ex->getMessage();

						} catch(Exception $ex) {
							// Okänt fel
							$errors[] = $this->User->get('email');
							$mail_errors[] = $ex->getMessage();
						}

					} else {
						die('timeout');
					}
				} else {
					die('hash mismatch');
				}
			} else {
				die('user not found');
			}
		}
		*/
		if (isset($_POST['send'])) {

			$email = EMAIL_FROM_ADDRESS;
			$from = array($email => EMAIL_FROM_NAME);

			$this->User->load($_POST['user'], 'alias');
			if ($this->User->wasLoaded()) {
				$recipients = array($this->User->get('email') => $this->User->get('name'));
				$pass = md5(date('YmdHis'));
				$pass = substr($pass, -30, 6);
				$this->User->setPassword($pass);
				$this->User->save();

				try {
					$mail = new Mail();
					$mail->setTemplate('password_reset');
					$mail->setPlainTemplate('password_reset');
					$mail->setFrom($from);
					$mail->addReplyTo(EMAIL_FROM_NAME, $email);
					$mail->setRecipients($recipients);
					$mail->setMailVar('alias', $this->User->alias);
					$mail->setMailVar('password', $pass);
					$mail->setMailVar('user_name', $this->User->name);
					if(!$mail->send()) {
						$errors[] = $this->User->get('email');
					} else {
						$this->set('usermessage', 'An e-mail has been sent to the provided e-mail address.');
					}
				} catch(Swift_RfcComplianceException $ex) {
					// Felaktig epost-adress
					$errors[] = $this->User->get('email');
					$mail_errors[] = $ex->getMessage();

				} catch(Exception $ex) {
					// Okänt fel
					$errors[] = $this->User->get('email');
					$mail_errors[] = $ex->getMessage();
				}

				$_SESSION['m'] = $this->User->get('email');
				header('Location: '.BASE_URL.'user/login/ok');

			} else {

				$this->User->load($_POST['user'], 'email');
				$recipients = array($this->User->get('email') => $this->User->get('name'));
				if ($this->User->wasLoaded()) {
					$pass = md5(date('YmdHis'));
					$pass = substr($pass, -30, 6);
					$this->User->setPassword($pass);
					$this->User->save();

					try {
						$mail = new Mail();
						$mail->setTemplate('password_reset');
						$mail->setPlainTemplate('password_reset');
						$mail->setFrom($from);
						$mail->addReplyTo(EMAIL_FROM_NAME, $email);
						$mail->setRecipients($recipients);
						$mail->setMailVar('alias', $this->User->alias);
						$mail->setMailVar('password', $pass);
						$mail->setMailVar('user_name', $this->User->name);
						if(!$mail->send()) {
							$errors[] = $this->User->get('email');
						} else {
							$this->set('usermessage', 'An e-mail has been sent to the provided e-mail address.');
						}
					} catch(Swift_RfcComplianceException $ex) {
						// Felaktig epost-adress
						$errors[] = $this->User->get('email');
						$mail_errors[] = $ex->getMessage();

					} catch(Exception $ex) {
						// Okänt fel
						$errors[] = $this->User->get('email');
						$mail_errors[] = $ex->getMessage();
					}

					$_SESSION['m'] = $this->User->get('email');
					header('Location: '.BASE_URL.'user/login/ok');

				} else {
					header('Location: '.BASE_URL.'user/login/err');
				}
			}
		}

		// If referring page sets "backref" action, the "Go back" button will refer back to all $param#
		if ($action == 'backref') {
			$this->setNoTranslate('go_back_url', $param1 . ($param2 != '' ? '/' . $param2 : '') . ($param3 != '' ? '/' . $param3 : ''));
		} else {
			$this->setNoTranslate('go_back_url', 'user/login');
		}

		$this->set('headline', 'Request Username and Password');
		$this->set('user_name', 'Username');
		$this->set('email', 'E-Mail');
		$this->set('button', 'Reset');
		$this->set('goback', 'Go back');
		$this->set('forgotlink', 'Forgot your password or Username?');
		$this->set('line1', 'Write your username or e-mail adress in the field below.');
		$this->set('line2', 'An e-mail will then be sent to you containing your account\'s username and a new password.');
	}
/*
	function forgotUsername() {
		setAuthLevel(0);

		if (isset($_POST['remindme'])) {
			$this->User->load($_POST['email'], 'email');
			if ($this->User->wasLoaded()) {
				$email = EMAIL_FROM_ADDRESS;
				$from = array($email => EMAIL_FROM_NAME);
				$recipients = array($this->User->get('email') => $this->User->get('email'));
				try {
					$mail = new Mail();
					$mail->setTemplate('username_remind');
					$mail->setPlainTemplate('username_remind');
					$mail->setFrom($from);
					$mail->addReplyTo(EMAIL_FROM_NAME, $email);
					$mail->setRecipients($recipients);
					$mail->setMailVar('alias', $this->User->get('alias'));
					if(!$mail->send()) {
						$errors[] = $_POST['email'];
					} else {
						$this->set('usermessage', 'An e-mail has been sent to the provided e-mail address.');
					}
				} catch(Swift_RfcComplianceException $ex) {
					// Felaktig epost-adress
					$errors[] = $_POST['email'];
					$mail_errors[] = $ex->getMessage();

				} catch(Exception $ex) {
					// Okänt fel
					$errors[] = $_POST['email'];
					$mail_errors[] = $ex->getMessage();
				}
			} else {
				$this->setNoTranslate('error', true);
				$this->set('usermessage', 'Sorry, we do not recognize that e-mail address.');
			}
		}

		$this->set('email', 'E-mail');
		$this->set('remindme', 'Remind me');
		$this->set('headline', 'Request username reminder');
	}
*/
	function accountSettings() {
  
		setAuthLevel(1);

		$this->User->load($_SESSION['user_id'], 'id');


		if (isset($_POST['save'])) {
    
			$this->User->set('phone1', $_POST['phone1']);
			$this->User->set('phone2', $_POST['phone2']);
			$this->User->set('email', $_POST['email']);
			$this->User->set('name', $_POST['name']);
			$this->User->set('contact_phone', $_POST['phone3']);
			if (isset($_POST['newsletter'])) {
				$this->User->set('newsletter', '[accepted, IP:'.$_SERVER["REMOTE_ADDR"].', Date:'.date('d-m-Y H:i').']');
			} else {
				$this->User->set('newsletter', '[declined, IP:'.$_SERVER["REMOTE_ADDR"].', Date:'.date('d-m-Y H:i').']');
			}
			if (userLevel() != 2) {
      
        // Company section
				$this->User->set('orgnr', $_POST['orgnr']);
				$this->User->set('company', $_POST['company']);
				$this->User->set('commodity', $_POST['commodity']);
				$this->User->set('address', $_POST['address']);
				$this->User->set('zipcode', $_POST['zipcode']);
				$this->User->set('city', $_POST['city']);
				$this->User->set('country', $_POST['country']);
        // Phone1 and Phone2 are handled above

        // Email is handled above
				$this->User->set('website', $_POST['website']);
				$this->User->set('facebook', $_POST['facebook']);
				$this->User->set('twitter', $_POST['twitter']);
				$this->User->set('google_plus', $_POST['google_plus']);
				$this->User->set('youtube', $_POST['youtube']);
        
        // Billing address section
				$this->User->set('invoice_company', $_POST['invoice_company']);
				$this->User->set('invoice_address', $_POST['invoice_address']);
				$this->User->set('invoice_zipcode', $_POST['invoice_zipcode']);
				$this->User->set('invoice_city', $_POST['invoice_city']);
				$this->User->set('invoice_country', $_POST['invoice_country']);
				$this->User->set('invoice_email', $_POST['invoice_email']);
				$this->User->set('presentation', $_POST['presentation']);

        // Contact section
        // Alias field is disabled and should not be changed
        // Name and Contact_Phone are handled above
				$this->User->set('contact_phone2', $_POST['phone4']);
				$this->User->set('contact_email', $_POST['contact_email']);
			}

			$iid = $this->User->save();
		}

		$this->setNoTranslate('locked0sel', '');
		$this->setNoTranslate('edit_id', $_SESSION['user_id']);
		$this->setNoTranslate('user', $this->User);
	}

	public function uploadlogo() {

		setAuthLevel(1);
		$now = time();
		$this->User->load($_SESSION['user_id'], 'id');
		$this->set('error_notimg', '');
		$this->set('error_toobig', '');
		$this->set('error_wrongformat', '');
		$this->set('error_notuploaded', '');
		$this->set('error_whenuploaded', '');
		$this->set('img_wasuploaded', '');
		$this->set('headline', 'Upload logo');
		$this->setNoTranslate('image_path', '../images/exhibitors/'.$_SESSION['user_id']).'/';
		$this->set('name_label', 'Name');
		$this->setNoTranslate('user', $_SESSION['user_id']);
		$this->set('save_label', 'Save');
		$this->set('image_label', 'Image');
		$this->set('delete', 'Delete');

		if(isset($_POST["submit"])) {

			$target_dir = ROOT.'public/images/exhibitors/'.$_SESSION['user_id'].'/';
			$target_file = $target_dir.'/exhibitor_logo'.$now.'.png';
			/*
			$uploadOk = 1;
			$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);	
		    $check = getimagesize($_FILES["image"]["tmp_name"]);


		    if($check !== false) {
		    //    echo "File is an image - " . $check["mime"] . ".";
		        $uploadOk = 1;
		    } else {
		    	$this->set('error_notimg', 'File is not an image.');
		        $uploadOk = 0;
		    }
			// Check file size
			if ($_FILES["image"]["size"] > 50000000) {
				$this->set('error_toobig', 'Sorry, your file is too large.');
			    $uploadOk = 0;
			}
			// Allow certain file formats
			if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" 
				&& $imageFileType != "gif" ) {
				$this->set('error_wrongformat', 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.');
			    $uploadOk = 0;
			}
			// Check if $uploadOk is set to 0 by an error
			if ($uploadOk == 0) {
				$this->set('error_notuploaded', 'Your file was not uploaded.');
			// if everything is ok, try to upload file
			} else {
/*
			$inFile = $_FILES["image"]["tmp_name"];
			$outFile = $target_file;
			$image = new ImageMagick($inFile);
			$image->thumbnailImage(200, 200);
			$image->writeImage($outFile);*/
			if (userLevel() == 4) {
				
			}

		if (!file_exists(ROOT.'public/images/exhibitors/'.$_SESSION['user_id'])) {
			mkdir(ROOT.'public/images/exhibitors/'.$_SESSION['user_id']);
			chmod(ROOT.'public/images/exhibitors/'.$_SESSION['user_id'], 0775);
		}
				if (is_uploaded_file($_FILES['image']['tmp_name'])) {
					$im = new ImageMagick;
					$now = time();
					array_map('unlink', glob(ROOT.'public/images/exhibitors/'.$_SESSION['user_id'].'/*'));
					move_uploaded_file($_FILES['image']['tmp_name'], ROOT.'public/images/tmp/'.$now.'.png');
					chmod(ROOT.'public/images/tmp/'.$now.'.png', 0775);
					//print_r(ROOT.'public/images/tmp/'.$now.'.jpg');
					$im->IMlogo(ROOT.'public/images/tmp/'.$now.'.png', $target_dir, 84);
					chmod($target_dir, 0775);
					unlink(ROOT.'public/images/tmp/'.$now.'.png');
					/*
					$im->constrain(ROOT.'public/images/exhibitors/'.$_SESSION['user_id'].'/logotype/exhibitor_logo'.$now.'.jpg', ROOT.'public/images/exhibitors/'.$_SESSION['user_id'].'/logotype/exhibitor_logo'.$now.'_small.jpg', 253, 71);
					chmod(ROOT.'public/images/exhibitors/'.$_SESSION['user_id'].'/logotype/exhibitor_logo'.$now.'_small.jpg', 0775);*/
	/*
					array_map('unlink', glob(ROOT.'public/images/exhibitors/'.$_SESSION['user_id'].'/*'));
				    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
				    	
				        chmod(ROOT.'public/images/exhibitors/'.$_SESSION['user_id'].'/', 0775);
				        $this->set('img_wasuploaded', "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.");
				    } else {
				    	$this->set('error_whenuploaded', 'There was an error uploading your file.');
				    }*/
				}
		//	}
		}

	}

public function deletelogo() {
	setAuthLevel(1);
	$this->User->load($_SESSION['user_id'], 'id');
	if ($this->User->wasLoaded()) {
		if (file_exists(ROOT.'public/images/exhibitors/'.$_SESSION['user_id'])) {
			foreach(glob(ROOT.'public/images/exhibitors/'.$_SESSION['user_id'].'/*.*') as $file) {
			    if(is_file($file)) {
			        @unlink($file);
			    }
			}
			//unlink(ROOT.'public/images/exhibitors/'.$_SESSION['user_id'].'/.jpg');
		}
	}

	header("Location: ".BASE_URL."user/uploadlogo");
	exit;
}

	function register($fairUrl='') {

		$error = '';

		if (isset($_POST['save'])) {

      // Company section
			$this->User->set('orgnr', $_POST['orgnr']);
			$this->User->set('company', $_POST['company']);
			$this->User->set('commodity', $_POST['commodity']);
			$this->User->set('address', $_POST['address']);
			$this->User->set('zipcode', $_POST['zipcode']);
			$this->User->set('city', $_POST['city']);
			$this->User->set('country', $_POST['country']);
			$this->User->set('phone1', $_POST['phone1']);
			$this->User->set('phone2', $_POST['phone2']);
			$this->User->set('email', $_POST['email']);
			$this->User->set('website', $_POST['website']);
			$this->User->set('facebook', $_POST['facebook']);
			$this->User->set('twitter', $_POST['twitter']);
			$this->User->set('google_plus', $_POST['google_plus']);
			$this->User->set('youtube', $_POST['youtube']);
      // For popups, the presentation is located directly below the first section, not the second
			$this->User->set('presentation', $_POST['presentation']);
      
      // Billing address section
			$this->User->set('invoice_company', $_POST['invoice_company']);
			$this->User->set('invoice_address', $_POST['invoice_address']);
			$this->User->set('invoice_zipcode', $_POST['invoice_zipcode']);
			$this->User->set('invoice_city', $_POST['invoice_city']);
			$this->User->set('invoice_country', $_POST['invoice_country']);
			$this->User->set('invoice_email', $_POST['invoice_email']);
      
      // Contact section
			$this->User->set('alias', $_POST['alias']);
			$this->User->set('name', $_POST['name']);
			$this->User->set('contact_phone', $_POST['phone3']);
			$this->User->set('contact_phone2', $_POST['phone4']);
			$this->User->set('contact_email', $_POST['contact_email']);
      
			$this->User->set('level', 1);
			$this->User->set('locked', 0);

			if ($this->User->aliasExists()) {
      
				$error.= 'The username already exists in our system.';
        
			} else if ($this->User->emailExists()) {
      
				$error.= 'The email address already exists in our system.';
        
			} else if (!$this->validAlias($_POST["alias"])) {

				$error.= 'The username can only consist of numbers and lowercase letters.';
				
			} else {
      
				if (strlen($_POST['alias']) > 3) {
					if ($_POST['password'] == $_POST['password_repeat']) {

						$this->User->setPassword($_POST['password']);
						$userId = $this->User->save();
						//$hash = md5($this->User->get('email').BASE_URL.$userId);
						//$url = BASE_URL.'user/confirm/'.$userId.'/'.$hash;

          				try {
          					/*
							$email = EMAIL_FROM_ADDRESS;
							$from = array($email => EMAIL_FROM_NAME);
							$recipients = array($this->User->get('email') => $this->User->get('email'));
							$mail = new Mail();
							$mail->setTemplate('confirm_mail');
							$mail->setFrom($from);
							$mail->addReplyTo(EMAIL_FROM_NAME, $email);
							$mail->setRecipients($recipients);
							$mail->setMailVar('exhibitor_name', $this->User->get('name'));
							$mail->setMailVar('event_url', $url);
							*/
							$email = EMAIL_FROM_ADDRESS;
							$from = array($email => EMAIL_FROM_NAME);
							$recipients = array($this->User->get('email') => $this->User->get('email'));
							$mail = new Mail();
							$mail->setTemplate('activate_welcome');
							$mail->setPlainTemplate('activate_welcome');
							$mail->setFrom($from);
							$mail->addReplyTo(EMAIL_FROM_NAME, $email);
							$mail->setRecipients($recipients);
							$mail->setMailVar('exhibitor_name', $this->User->get('name'));
							$mail->setMailVar('alias', $this->User->get('alias'));
							$mail->setMailVar('accesslevel', accessLevelToText($this->User->get('level')));
							$mail->setMailVar('event_url', BASE_URL.$fairUrl);
							if(!$mail->send()) {
								$errors[] = $this->User->get('company');
							}

						} catch(Swift_RfcComplianceException $ex) {
							// Felaktig epost-adress
							$errors[] = $this->User->get('company');
							$mail_errors[] = $ex->getMessage();

						} catch(Exception $ex) {
							// Okänt fel
							$errors[] = $this->User->get('company');
							$mail_errors[] = $ex->getMessage();
						}

						if ($fairUrl != '') {
            
							require_once ROOT.'application/models/Exhibitor.php';
							require_once ROOT.'application/models/ExhibitorCategory.php';
							require_once ROOT.'application/models/Fair.php';
							require_once ROOT.'application/models/FairMap.php';
							require_once ROOT.'application/models/FairMapPosition.php';
							require_once ROOT.'application/models/PreliminaryBooking.php';
							require_once ROOT.'application/models/FairUserRelation.php';
							require_once ROOT.'application/models/FairExtraOption.php';
							require_once ROOT.'application/models/FairArticle.php';
              
							$fair = new Fair;
							$fair->load($fairUrl, 'url');
              
							if ($fair->wasLoaded()) {
              
								$fur = new FairUserRelation;
								$fur->set('user', $userId);
								$fur->set('fair', $fair->get('id'));
								$fur->set('connected_time', time());
								$fur->save();
							}

				            $this->setNoTranslate('noView', true);
				            $_SESSION['m'] = $this->User->get('email');
							if ($errors) {
								$_SESSION['mail_errors'] = $mail_errors;
								$_SESSION['errors'] = $errors;
							}
							header('Location: '.BASE_URL.'user/login/'.$fairUrl.'/new');
				            exit;

						} else {
				            $this->setNoTranslate('noView', true);
				            $_SESSION['m'] = $this->User->get('email');
							if ($errors) {
								$_SESSION['mail_errors'] = $mail_errors;
								$_SESSION['errors'] = $errors;
							}
							header('Location: '.BASE_URL.'user/login/nofair/new');
				            exit;
						}
            
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
		$this->setNoTranslate('user', $this->User);
		//$fair = new Fair($this->User->db);
		//$fair->loadsimple($_SESSION['outside_fair_url'], 'url');
		//$this->setNoTranslate('fair', $fair);
	}

	function confirm($user, $hash) {

		$this->User->load($user, 'id');
		if ($this->User->wasLoaded()) {
			$userHash = md5($this->User->get('email').BASE_URL.$this->User->get('id'));
			if ($userHash == $hash && $this->User->get('locked') > 0) {
				$this->User->set('locked', 0);
				$this->User->save();
        
        // Mail here
        $stmt = $this->db->prepare("SELECT fair.url FROM fair_user_relation AS rel LEFT JOIN fair ON rel.fair = fair.id WHERE rel.user = ? ORDER BY fair.id DESC LIMIT 0,1");
        $stmt->execute(array($this->User->get('id')));
        $fair = $stmt->fetch(PDO::FETCH_ASSOC);

        $recipients = array($this->User->get('email') => $this->User->get('name'));
		$email = EMAIL_FROM_ADDRESS;
		$from = array($email => EMAIL_FROM_NAME);
		try {
			$mail = new Mail();
			$mail->setTemplate('activate_welcome');
			$mail->setPlainTemplate('activate_welcome');
			$mail->setFrom($from);
			$mail->addReplyTo(EMAIL_FROM_NAME, $email);
			$mail->setRecipients($recipients);
			$mail->setMailVar('exhibitor_name', $this->User->get('name'));
			$mail->setMailVar('alias', $this->User->get('alias'));
			$mail->setMailVar('accesslevel', accessLevelToText($this->User->get('level')));
			$mail->setMailVar('event_url', BASE_URL.$fair['url']);
			if(!$mail->send()) {
				$errors[] = $this->User->get('email');
			} else {
				$this->set('usermessage', 'An e-mail has been sent to the provided e-mail address.');
			}
		} catch(Swift_RfcComplianceException $ex) {
			// Felaktig epost-adress
			$errors[] = $this->User->get('email');
			$mail_errors[] = $ex->getMessage();

		} catch(Exception $ex) {
			// Okänt fel
			$errors[] = $this->User->get('email');
			$mail_errors[] = $ex->getMessage();
		}
        // Log the user in
		$_SESSION['user_id'] = $this->User->get('id');
		$_SESSION['user_level'] = $this->User->get('level');
		$_SESSION['user_password_changed'] = $this->User->get('password_changed');
        
        // Get fair associated with the user
        $stmt = $this->db->prepare("SELECT rel.fair, fair.windowtitle, fair.url FROM fair_user_relation AS rel LEFT JOIN fair ON rel.fair = fair.id WHERE rel.user = ? ORDER BY fair.id DESC LIMIT 0,1");
        $stmt->execute(array($this->User->get('id')));
        $result = $stmt->fetch();
        $_SESSION['user_fair'] = $result['fair'];
        $_SESSION['fair_windowtitle'] = $result['windowtitle'];
        
        $this->setNoTranslate('noView', true);

		    header("Location: " . BASE_URL . $result["url"]);
		 		if (!$_SESSION['user_terms_approved']) {
					$url = $urlArray[0] . '/' . $action;
					// Whitelist URLs that can be accessed without approved terms
					if (!in_array($url, array('user/terms', 'translate/language'))) {
						header('Location: ' . BASE_URL . 'user/terms?next=' . $result["url"]);
						exit;
					}
				}
		 		if (!$_SESSION['user_pub_approved'] && $this->User->get('level') == 3) {
					$url = $urlArray[0] . '/' . $action;
					// Whitelist URLs that can be accessed without approved terms
					if (!in_array($url, array('user/pub', 'translate/language'))) {
						header('Location: ' . BASE_URL . 'user/pub?next=' . $result["url"]);
						exit;
					}
				}

				exit;
        
			} else if( $hash == $userHash ) {
      
				$this->setNoTranslate('noView', true);
				header('Location: '.BASE_URL.'user/login/alreadyactivated');
				exit;
			}
      
			$this->setNoTranslate('noView', true);
			header('Location: '.BASE_URL.'user/login/');
			exit;
		}
	}

	public function delete($id, $confirmed='') {

		setAuthLevel(4);

		$this->User->load($id, 'id');

		$this->set('headline', 'Delete super user');

		if ($confirmed == 'confirmed') {
			$userid = $id;	

			// Hämta användarens olika exhibitorId'n
			$statement = $this->db->prepare("SELECT id FROM exhibitor WHERE user = ?");
			$statement->execute(array($userid));
			$result = $statement->fetchAll();

			$this->User->delete();

			header("Location: ".BASE_URL."user/overview/");
	
			foreach($result as $exhibitor):
				//$statement = $this->db->prepare("DELETE * FROM exhibitor_category_rel WHERE exhibitor = ?");
				//$statement->execute(array($exhibitor->id));
				echo $exhibitor->id;
			endforeach;
			
		} else {
			$this->setNoTranslate('user_id', $id);
			$this->setNoTranslate('user', $this->User);
			$this->set('warning', 'Do you really want to delete this user?');
			$this->set('yes', 'Yes');
			$this->set('no', 'No');
		}
	}

	public function resendDetails($id) {

		setAuthLevel(4);

		$this->User->load($id, 'id');

		if ($this->is_ajax) {
			$this->createJsonResponse();
		}

		if ($this->User->wasLoaded()) {
			$pass = md5(date('YmdHis'));
			$pass = substr($pass, -30, 6);

			$this->User->setPassword($pass);
			$this->User->save();
			
			$mail_errors = array();
			$errors = array();
			try {
		        $recipients = array($this->User->get('email') => $this->User->get('name'));
				$email = EMAIL_FROM_ADDRESS;
				$from = array($email => EMAIL_FROM_NAME);
			    $mail = new Mail();
			    $mail->setTemplate('resend_details');
			    $mail->setPlainTemplate('resend_details');
			    $mail->setFrom($from);
			    $mail->addReplyTo(EMAIL_FROM_NAME, $email);
			    $mail->setRecipients($recipients);
				$mail->setMailVar('exhibitor_name', $this->User->get('name'));
				$mail->setMailVar('alias', $this->User->get('alias'));
				$mail->setMailVar('password', $pass);
				$this->set('result', 'The user\'s password was reset and a mail was sent.');
				if(!$mail->send()) {
					$errors[] = $this->User->get('email');
				}

			} catch(Swift_RfcComplianceException $ex) {
				// Felaktig epost-adress
				$errors[] = $this->User->get('email');
				$mail_errors[] = $ex->getMessage();

			} catch(Exception $ex) {
				// Okänt fel
				$errors[] = $this->User->get('email');
				$mail_errors[] = $ex->getMessage();
			}
		} else {
			$this->set('result', 'That user does not exist.');
		}
		if ($errors) {
			$this->setNoTranslate('mail_errors', $mail_errors);
			$this->setNoTranslate('errors', $errors);
		}
	}

	private function validAlias($string) {
		// Check if string only consists of numbers or any lowercase letter from any language.
		return preg_match("/^[a-z-_0-9]+$/", $string);
	}

	public function terms() {
		setAuthLevel(1);

		$this->User->load($_SESSION['user_id'], 'id');
		$next = (isset($_GET['next']) ? str_replace(BASE_URL, '', $_GET['next']) : 'page/loggedin');

		// When user has changed the application language, this will be true.
		// But we can't send the user back to TranslateController because they will
		// get stuck in an infinite loop.
		if ($next == 'translate/language') {
			$next = 'page/loggedin';
		}

		if (isset($_POST['approve'])) {
			$this->User->set('terms', '[version:'.USER_TERMS.', IP:'.$_SERVER["REMOTE_ADDR"].', Date:'.date('d-m-Y H:i').']');
			if (isset($_POST['newsletter']) > 0) {
				$this->User->set('newsletter', '[accepted, IP:'.$_SERVER["REMOTE_ADDR"].', Date:'.date('d-m-Y H:i').']');
			} else {
				$this->User->set('newsletter', '[declined, IP:'.$_SERVER["REMOTE_ADDR"].', Date:'.date('d-m-Y H:i').']');
			}
			$this->User->save();

			$_SESSION['user_terms_approved'] = true;

			header('Location: ' . BASE_URL . $next);
			exit;

		} else if (isset($_POST['decline'])) {
			$this->logout();
			exit;

		} //else {
			$stmt_content = $this->db->prepare("SELECT * FROM page_content WHERE page = ? AND language = ?");
			$stmt_content->execute(array('user_terms', LANGUAGE));
			$terms_row = $stmt_content->fetchObject();

			if (is_object($terms_row)) {
				$terms_content = $terms_row->content;

			} else {
				// Fetch the english version if terms is not translated yet
				$stmt_content = $this->db->prepare("SELECT * FROM page_content WHERE page = ? AND language = 'en'");
				$stmt_content->execute(array('user_terms'));
				$terms_row = $stmt_content->fetchObject();

				if (is_object($terms_row)) {
					$terms_content = $terms_row->content;
				} else {
					$terms_content = '';
				}
			}

			$this->setNoTranslate('next', $next);
			$this->setNoTranslate('terms_content', $terms_content);
			$this->set('label_headline', 'Approve our User Terms');
			$this->set('label_approve', 'Approve');
			$this->set('label_decline', 'Decline');
		//}
	}
	public function pub() {
		setAuthLevel(3);

		$this->User->load($_SESSION['user_id'], 'id');
		$next = (isset($_GET['next']) ? str_replace(BASE_URL, '', $_GET['next']) : 'page/loggedin');

		// When user has changed the application language, this will be true.
		// But we can't send the user back to TranslateController because they will
		// get stuck in an infinite loop.
		if ($next == 'translate/language') {
			$next = 'page/loggedin';
		}
		$user_pub = 'version:'.USER_PUB;
		if (isset($_POST['approve'])) {
			$this->User->set('pub', '[version:'.USER_PUB.', IP:'.$_SERVER["REMOTE_ADDR"].', Date:'.date('d-m-Y H:i').']');
			$this->User->save();

			$_SESSION['user_pub_approved'] = true;

			header('Location: ' . BASE_URL . $next);
			exit;

		} else if (isset($_POST['decline'])) {
			$this->logout();
			exit;

		} else {
			$stmt_content = $this->db->prepare("SELECT * FROM page_content WHERE page = ? AND language = ?");
			$stmt_content->execute(array('user_pub', LANGUAGE));
			$pub_row = $stmt_content->fetchObject();

			if (is_object($pub_row)) {
				$pub_content = $pub_row->content;

			} else {
				// Fetch the english version if pub is not translated yet
				$stmt_content = $this->db->prepare("SELECT * FROM page_content WHERE page = ? AND language = 'en'");
				$stmt_content->execute(array('user_pub'));
				$pub_row = $stmt_content->fetchObject();

				if (is_object($pub_row)) {
					$pub_content = $pub_row->content;
				} else {
					$pub_content = '';
				}
			}

			$this->setNoTranslate('next', $next);
			$this->setNoTranslate('pub_content', $pub_content);
			$this->set('label_headline', 'Approve our Personal Data Assistant Agreements');
			$this->set('label_approve', 'Approve');
			$this->set('label_decline', 'Decline');
		}
	}
}

?>
