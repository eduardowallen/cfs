<?php
class FairRegistrationController extends Controller {

	public function __construct($model, $controller, $action) {
		parent::__construct($model, $controller, $action);

		setAuthLevel(1);
	}

	public function form($id) {
		$fair = new Fair();
		$fair->load($id, 'id');

		$me = new User();
		$me->load($_SESSION['user_id'], 'id');

		if ($fair->get('hidden') == 0 || $fair->get('allow_registrations') == 0) {
			header('Location: /');
			return;
		}

		if (isset($_POST['save'])) {
			$categories = '';
			$options = '';

			if (isset($_POST['categories']) && is_array($_POST['categories'])) {
				$categories = implode('|', $_POST['categories']);
			}

			if (isset($_POST['options']) && is_array($_POST['options'])) {
				$options = implode('|', $_POST['options']);
			}

			$this->FairRegistration->set('user', $me->get('id'));
			$this->FairRegistration->set('fair', $fair->get('id'));
			$this->FairRegistration->set('categories', $categories);
			$this->FairRegistration->set('options', $options);
			$this->FairRegistration->set('commodity', $_POST['commodity']);
			$this->FairRegistration->set('arranger_message', $_POST['arranger_message']);
			$this->FairRegistration->set('area', $_POST['area']);
			$this->FairRegistration->set('booking_time', time());
			$this->FairRegistration->save();

			header('Location: /fairRegistration/success');
			return;
		}

		$this->setNoTranslate('fair', $fair);
		$this->setNoTranslate('me', $me);

		// Labels
		$this->set('label_headline', 'Register for fair %s');
		$this->set('label_category', 'Category');
		$this->set('label_options', 'Extra options');
		$this->set('label_commodity', 'Commodity');
		$this->set('label_message_organizer', 'Message to organizer');
		$this->set('label_area', 'Area');
		$this->set('label_confirm', 'Confirm');
	}

	public function success() {
		$this->set('label_thanks', 'Thank you for your registration!');
		$this->set('label_ok', 'OK');
	}
}
?>