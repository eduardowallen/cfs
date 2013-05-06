<?php

class StartController extends Controller {
	
	public function index() {
		unset($_SESSION['visitor']);
		$this->set('error', '');
		$this->setNoTranslate('fair_url', $fUrl);
		
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
	
}

?>
