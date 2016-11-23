<?php
class StartController extends Controller {
	public function index() {
		if (isset($_SESSION['user_id'])) {
			header('Location: ' . BASE_URL . 'start/home');
		} else {
			header('Location: ' . BASE_URL . 'user/login');
		}

		die();
	}

	function home() {

		if (isset($_SESSION['user_id'])) {
			$user = new User();
			$user->load($_SESSION['user_id'], 'id');

			$fair = new Fair();
			$fair->load2($_SESSION['user_fair'], 'id');
			
			if ($fair->wasLoaded()) {
				$this->set("heading_fair", "Current event");
				$this->setNoTranslate("currentfair", $fair->get('name'));
				$this->setNoTranslate("currentfairname", $fair->get('windowtitle'));
			}

			$this->set("heading", "Logged in as");
			if ($user->wasLoaded()) {
				$this->setNoTranslate("name", $user->get('name'));
				$this->setNoTranslate("company", $user->get('company'));
			}
		} else {
			header('Location: ' . BASE_URL . 'user/login');
		}
	}
}
?>
