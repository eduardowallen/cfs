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
				$this->set('error', true);
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
          $this->set('error', true);
        }
          
        if ($this->User->aliasExists()) {
        
          $this->set('user_message', 'The alias already exists in our system. Please choose another one.');
          $halt = true;
          $this->set('error', true);
        }
      
      } else if ($this->User->wasLoaded()) {
      
        $this->User->set('email', $_POST['email']);

        if ($this->User->get('email') != $_POST['email']) {
          if ($this->User->emailExists()) {
          
            $this->set('user_message', 'The email address already exists in our system. Please choose another one.');
            $halt = true;
            $this->set('error', true);
          }
        }
      }
      
      if (!isset($halt)) { 
      
        // Company section
        $this->User->set('orgnr', $_POST['orgnr']);
        $this->User->set('company', $_POST['company']);
        $this->User->set('commodity', $_POST['commodity']);
        if(isset($_POST['customer_nr']))
          $this->User->set('customer_nr', $_POST['customer_nr']);
        $this->User->set('address', $_POST['address']);
        $this->User->set('zipcode', $_POST['zipcode']);
        $this->User->set('city', $_POST['city']);
        $this->User->set('country', $_POST['country']);
        $this->User->set('phone1', $_POST['phone1']);
        $this->User->set('phone2', $_POST['phone2']);
        $this->User->set('fax', $_POST['fax']);
        // Email is handled in the code above
        $this->User->set('website', $_POST['website']);
        
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
          
          switch($this->User->get('level')) :
            case 4: $lvl = 'Master'; break;
            case 3: $lvl = 'Organizer'; break;
            case 2: $lvl = 'Administrator'; break;
            default: $lvl = 'Exhibitor'; break;
          endswitch;
            
          $mail = new Mail($_POST['email'], 'welcome');
          $mail->setMailVar('alias', $_POST['alias']);
          $mail->setMailVar('password', $password);
          $mail->setMailVar('accesslevel', $lvl);
          $mail->send();
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
  
    global $translator;
  
		unset($_SESSION['visitor']);
		if(isset($_SESSION['user_id'])) :
			 header("Location: ".BASE_URL."page/loggedin"); 
		endif;

		$this->set('error', '');
		$this->setNoTranslate('fair_url', $fUrl);
    
		if( $status !== null){
			$this->set('first_time_msg', $translator->{'An email has been sent to the specified email addresses that were entered into during the registration prossesen'});
		}
		
		if( $fUrl == 'confirmed'){
			$this->set('confirmed_msg', $translator->{'Your account has been activated. Please log in to proceed.'});
		}
    elseif( $fUrl == 'alreadyactivated' ) {
      $this->set('confirmed_msg', $translator->{'Your account has already been activated.'});
    }

		if( $fUrl == 'ok') :
			$this->set('good', 'yes');
			$this->set('res_msg', $translator->{'A new password has been sent to '}.$_SESSION['m']);
			$_SESSION['m'] = "";
		elseif($fUrl == 'err') :
			$this->set('good', 'no');
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

					/*
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
				header("Location: ".BASE_URL."user/login");
				$_SESSION['error'] = true;
			}

		}



		$this->set('headline', $translator->{'Log in'});
		$this->set('user_name', $translator->{'Username'});
		
		$this->set('password', $translator->{'Password'});
		$this->set('button', $translator->{'Log in'});
		$this->set('forgotlink', $translator->{'Forgot your password or Username?'});

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
            $mail = new Mail($this->User->email, 'password_changed');
            $mail->send();
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

            $mail = new Mail($this->User->email, 'password_reset');
            $mail->setMailVar('password', $str);
            $mail->send();
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
				$pass = md5(date('YmdHis'));
				$pass = substr($pass, -30, 6);
				$this->User->setPassword($pass);
				$this->User->save();
        $mail = new Mail($this->User->email, 'password_reset2');
        $mail->setMailVar('alias', $this->User->get('alias'));
        $mail->setMailVar('password', $pass);
        $mail->send();
				$_SESSION['m'] = $this->User->email;
				header('Location: '.BASE_URL.'user/login/ok');
			} else {
				$this->User->load($_POST['user'], 'email');
				if ($this->User->wasLoaded()) {
					$pass = md5(date('YmdHis'));
					$pass = substr($pass, -30, 6);
					$this->User->setPassword($pass);
					$this->User->save();
          $mail = new Mail($this->User->email, 'password_reset2');
          $mail->setMailVar('alias', $this->User->alias);
          $mail->setMailVar('password', $pass);
          $mail->send();
					$_SESSION['m'] = $this->User->email;
					header('Location: '.BASE_URL.'user/login/ok');
				} else {
					header('Location: '.BASE_URL.'user/login/err');
				}
			}
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

	function forgotUsername() {
		setAuthLevel(0);

		if (isset($_POST['remindme'])) {
			$this->User->load($_POST['email'], 'email');
			if ($this->User->wasLoaded()) {
				$mail = new Mail($this->User->email, 'username_remind');
        $mail->setMailVar('alias', $this->User->get('alias'));
        $mail->send();
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

		$this->User->load($_SESSION['user_id'], 'id');

		if (isset($_POST['save'])) {
    
			$this->User->set('phone1', $_POST['phone1']);
			$this->User->set('phone2', $_POST['phone2']);
			$this->User->set('email', $_POST['email']);
			$this->User->set('name', $_POST['name']);
			$this->User->set('contact_phone', $_POST['phone3']);

			if (userLevel() != 2) {
      
        // Company section
				$this->User->set('orgnr', $_POST['orgnr']);
				$this->User->set('company', $_POST['company']);
				$this->User->set('commodity', $_POST['commodity']);
        if(isset($_POST['customer_nr']))
          $this->User->set('customer_nr', $_POST['customer_nr']);
				$this->User->set('address', $_POST['address']);
				$this->User->set('zipcode', $_POST['zipcode']);
				$this->User->set('city', $_POST['city']);
				$this->User->set('country', $_POST['country']);
        // Phone1 and Phone2 are handled above
				$this->User->set('fax', $_POST['fax']);
        // Email is handled above
				$this->User->set('website', $_POST['website']);
        
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

			//$this->User->set('category', $_POST['category']);
			//$this->User->set('level', 1);

			$iid = $this->User->save();
		}

		$this->setNoTranslate('locked0sel', '');

		$this->setNoTranslate('edit_id', $_SESSION['user_id']);
		$this->set('user', $this->User);
	}

	function register($fairUrl='') {

		$error = '';

		if (isset($_POST['save'])) {

      // Company section
			$this->User->set('orgnr', $_POST['orgnr']);
			$this->User->set('company', $_POST['company']);
			$this->User->set('commodity', $_POST['commodity']);
      // Customer_Nr should not appear here
			$this->User->set('address', $_POST['address']);
			$this->User->set('zipcode', $_POST['zipcode']);
			$this->User->set('city', $_POST['city']);
			$this->User->set('country', $_POST['country']);
			$this->User->set('phone1', $_POST['phone1']);
			$this->User->set('phone2', $_POST['phone2']);
			$this->User->set('fax', $_POST['fax']);
			$this->User->set('email', $_POST['email']);
			$this->User->set('website', $_POST['website']);
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
			//$this->User->set('category', $_POST['category']);
      
			$this->User->set('level', 1);
			$this->User->set('locked', 1);

			if ($this->User->aliasExists()) {
      
				$error.= 'The username already exists in our system.';
        
			} else if ($this->User->emailExists()) {
      
				$error.= 'The email address already exists in our system.';
        
			} else {
      
				if (strlen($_POST['alias']) > 3) {
					if ($_POST['password'] == $_POST['password_repeat']) {
          
						$this->User->setPassword($_POST['password']);
						$userId = $this->User->save();
						$hash = md5($this->User->get('email').BASE_URL.$userId);
						$url = BASE_URL.'user/confirm/'.$userId.'/'.$hash;
            $mail = new Mail($this->User->email, 'confirm_mail');
            $mail->setMailVar('url', $url);
            $mail->send();
            
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
            
            $this->set('noView', true);
						header('Location: '.BASE_URL.'user/login/'.$fairUrl.'/new');
            exit;
            
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
        
        $mail = new Mail($this->User->get('email'), 'activate_welcome');
        $mail->setMailVar('alias', $this->User->get('alias'));
        $mail->setMailVar('accesslevel', accessLevelToText($this->User->get('level')));
        $mail->setMailVar('url', BASE_URL.$fair['url']);
        $mail->send();
        
        // Log the user in
				$_SESSION['user_id'] = $this->User->get('id');
				$_SESSION['user_level'] = $this->User->get('level');
				$_SESSION['user_password_changed'] = $this->User->get('password_changed');
        
        // Get fair associated with the user
        $stmt = $this->db->prepare("SELECT rel.fair, fair.windowtitle FROM fair_user_relation AS rel LEFT JOIN fair ON rel.fair = fair.id WHERE rel.user = ? ORDER BY fair.id DESC LIMIT 0,1");
        $stmt->execute(array($this->User->get('id')));
        $result = $stmt->fetch();
        $_SESSION['user_fair'] = $result['fair'];
        $_SESSION['fair_windowtitle'] = $result['windowtitle'];
        
        $this->set('noView', true);
        
        header("Location: ".BASE_URL."page/loggedin");
				exit;
        
			} else if( $hash == $userHash ) {
      
        $this->set('noView', true);
				header('Location: '.BASE_URL.'user/login/alreadyactivated');
				exit;
				//echo $hash.'<br/>'.$userHash;
			}
      
      $this->set('noView', true);
      header('Location: '.BASE_URL.'user/login/');
      exit;

		}

	}

	public function delete($id, $confirmed='') {

		setAuthLevel(4);

		$this->set('headline', 'Delete super user');

		if ($confirmed == 'confirmed') {
			$this->User->load($id, 'id');
			$userid = $id;	

			// Hämta användarens olika exhibitorId'n
			$statement = $this->db->prepare("SELECT id FROM exhibitor WHERE user = ?");
			$statement->execute(array($userid));
			$result = $statement->fetchAll();
	
			foreach($result as $exhibitor):
				//$statement = $this->db->prepare("DELETE * FROM exhibitor_category_rel WHERE exhibitor = ?");
				//$statement->execute(array($exhibitor->id));
				echo $exhibitor->id;
			endforeach;

			//$this->User->delete();
			//header("Location: ".BASE_URL."user/overview/4");
			//exit;
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

      $mail = new Mail($this->User->get('email'), 'resend_details');
      $mail->setMailVar('alias', $this->User->get('alias'));
      $mail->setMailVar('password', $str);
      $mail->send();
			$this->set('user_message', 'The user\'s password was reset and a mail was sent.');
		} else {
			$this->set('error_message', 'That user does not exist.');
		}
	}

}

?>
