<?php
class StartController extends Controller {
	public function index() {
		if (isset($_SESSION['user_id'])) {
			header('Location: ' . BASE_URL . 'page/loggedin');
		} else {
			header('Location: ' . BASE_URL . 'user/login');
		}

		die();
	}
}
?>
