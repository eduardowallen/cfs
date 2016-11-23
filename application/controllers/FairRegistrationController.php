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

		$organizer = new User();
		$organizer->load($fair->get('created_by'), 'id');

		if ($fair->get('allow_registrations') == 0) {
			header('Location: /');
			return;
		}

		if (isset($_POST['save'])) {
			$categories = '';
			$categoryNames = array();
			$options = '';
			$optionNames = array();
			$optionPrices = array();
			$articles = '';
			$articleNames = array();
			$artamount = '';
			$articleAmounts = array();
			$articlePrices = array();
			$quantity = '';
			if (isset($_POST['categories']) && is_array($_POST['categories'])) {
				foreach ($_POST['categories'] as $category_id) {
					$ex_category = new ExhibitorCategory();
					$ex_category->load($category_id, 'id');
					$categoryNames[] = $ex_category->get('name');
				}				
				$categories = implode('|', $_POST['categories']);
			}
			if (isset($_POST['options']) && is_array($_POST['options'])) {
				foreach ($_POST['options'] as $option_id) {
					$ex_option = new FairExtraOption();
					$ex_option->load($option_id, 'id');
					$optionNames[] = $ex_option->get('text');
					$optionsPrices[] = $ex_option->get('price');
				}				
				$options = implode('|', $_POST['options']);
			}
			if (!empty($_POST['articles']) && !empty($_POST['artamount'])) {
				$_POST['artamount'] = array_filter($_POST['artamount']);

				foreach (array_combine($_POST['articles'], $_POST['artamount']) as $art => $amount) {
					$ex_article = new FairArticle();
					$ex_article->load($art, 'id');
					$articleAmounts[] = $amount;
					$articleNames[] = $ex_article->get('text');
					$articlePrices[] = $ex_article->get('price');
				}
				$articles = implode('|', $_POST['articles']);
				$artamount = implode('|', $_POST['artamount']);				
			}			

			$this->FairRegistration->set('user', $me->get('id'));
			$this->FairRegistration->set('fair', $fair->get('id'));
			$this->FairRegistration->set('categories', $categories);
			$this->FairRegistration->set('options', $options);
			$this->FairRegistration->set('articles', $articles);
			$this->FairRegistration->set('amount', $artamount);
			$this->FairRegistration->set('commodity', $_POST['commodity']);
			$this->FairRegistration->set('arranger_message', $_POST['arranger_message']);
			$this->FairRegistration->set('area', $_POST['area']);
			$this->FairRegistration->set('booking_time', time());
			$this->FairRegistration->save();

			// Send mail
			$categories = implode(', ', $categoryNames);
			$options = implode(', ', $optionNames);
			$articles = implode(', ', $articleNames);
			$time_now = date('d-m-Y H:i');

			// Connect user to fair
			if (!userIsConnectedTo($fair->get('id'))) {
				$stmt = $this->db->prepare("INSERT INTO fair_user_relation (`fair`, `user`, `connected_time`) VALUES (?, ?, ?)");
				$stmt->execute(array($fair->get('id'), $me->get('id'), time()));
			}
				//Check mail settings and send only if setting is set
				if ($fair->wasLoaded()) {
					$mailSettings = json_decode($fair->get("mail_settings"));
					if (is_array($mailSettings->registerForFair)) {

						if (in_array("0", $mailSettings->registerForFair)) {
							$mail_organizer = new Mail($organizer->get('email'), 'new_fair_registration_confirm', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
							$mail_organizer->setMailvar("exhibitor_name", $me->get("name"));
							$mail_organizer->setMailVar('company_name', $me->get('company'));
							$mail_organizer->setMailvar("event_name", $fair->get("name"));
							$mail_organizer->setMailVar("url", BASE_URL . $fair->get("url"));
							$mail_organizer->setMailVar('arranger_message', $_POST['arranger_message']);
							$mail_organizer->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_organizer->setMailVar('exhibitor_category', $categories);
							$mail_organizer->setMailVar('exhibitor_options', $options);
							$mail_organizer->setMailVar('exhibitor_articles', $articles);
							$mail_organizer->setMailVar('booking_time', $time_now);
							$mail_organizer->setMailVar('area', $_POST['area']);
							$mail_organizer->send();
						}
						if (in_array("1", $mailSettings->registerForFair)) {
							$mail_user = new Mail($me->get('email'), 'new_fair_registration', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
							$mail_user->setMailvar("exhibitor_name", $me->get("name"));
							$mail_user->setMailVar('company_name', $me->get('company'));
							$mail_user->setMailvar("event_name", $fair->get("name"));
							$mail_user->setMailVar('event_email', $fair->get('contact_email'));
							$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
							$mail_user->setMailVar('event_website', $fair->get('website'));
							$mail_user->setMailVar("url", BASE_URL . $fair->get("url"));
							$mail_user->setMailVar('arranger_message', $_POST['arranger_message']);
							$mail_user->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_user->setMailVar('exhibitor_category', $categories);
							$mail_user->setMailVar('exhibitor_options', $options);
							$mail_user->setMailVar('exhibitor_articles', $articles);
							$mail_user->setMailVar('booking_time', $time_now);
							$mail_user->setMailVar('area', $_POST['area']);
							$mail_user->send();
						}
					}
				}
			header('Location: /fairRegistration/success');
			return;
		}

		$this->setNoTranslate('fair', $fair);
		$this->setNoTranslate('me', $me);

		// Labels
		$this->set('label_headline', 'Register for fair %s');
		$this->set('label_category', 'Category');
		$this->set('label_options', 'Extra options');
		$this->set('label_articles', 'Articles');
		$this->set('label_commodity', 'Commodity');
		$this->set('label_message_organizer', 'Message to organizer');
		$this->set('label_area', 'Requested area');
		$this->set('label_confirm', 'Confirm');
	}

	public function success() {
		$this->set('label_thanks', 'Thank you for your registration!');
		$this->set('label_ok', 'OK');
	}
}
?>