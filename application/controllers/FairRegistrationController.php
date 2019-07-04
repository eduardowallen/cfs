<?php
class FairRegistrationController extends Controller {

	public function __construct($model, $controller, $action) {
		parent::__construct($model, $controller, $action);

		setAuthLevel(1);
	}

	public function form($id) {

		$fair = new Fair();
		$fair->load($id, 'id');

		$user = new User();
		$user->load2($_SESSION['user_id'], 'id');

		if ($fair->get('allow_registrations') == 0) {
			header('Location: /');
			return;
		}

		if ($fair->wasLoaded() && $user->wasLoaded()) {
			if (isset($_POST['save'])) {

				$category_ids = '';
				if (isset($_POST['categories']) && is_array($_POST['categories'])) {
					$category_ids = implode('|', $_POST['categories']);
				}

				$option_ids = '';
				if (isset($_POST['options']) && is_array($_POST['options'])) {
					$option_ids = implode('|', $_POST['options']);
				}

				$article_ids = '';
				$article_amounts = '';
				if (!empty($_POST['articles']) && !empty($_POST['artamount'])) {
						$article_ids = implode('|', $_POST['articles']);
						$article_amounts = implode('|', $_POST['artamount']);				
				}			

				$this->FairRegistration->set('user', $user->get('id'));
				$this->FairRegistration->set('fair', $fair->get('id'));
				$this->FairRegistration->set('categories', $category_ids);
				$this->FairRegistration->set('options', $option_ids);
				$this->FairRegistration->set('articles', $article_ids);
				$this->FairRegistration->set('amount', $article_amounts);
				$this->FairRegistration->set('commodity', $_POST['commodity']);
				$this->FairRegistration->set('arranger_message', $_POST['arranger_message']);
				$this->FairRegistration->set('area', $_POST['area']);
				$this->FairRegistration->set('booking_time', time());
				$this->FairRegistration->save();

				/* Connect user to fair */
				if (!userIsConnectedTo($fair->get('id'))) {
					$stmt = $this->db->prepare("INSERT INTO fair_user_relation (`fair`, `user`, `connected_time`) VALUES (?, ?, ?)");
					$stmt->execute(array($fair->get('id'), $user->get('id'), time()));
				}

				/* Preparing to send the mail */
				if ($fair->get('contact_name') == '')
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('windowtitle'));
				else
				$from = array($fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get('contact_name'));

				if ($user->get('contact_email') == '')
				$recipient = array($user->get('email'), $user->get('company'));
				else
				$recipient = array($user->get('contact_email'), $user->get('name'));
				/* UPDATED TO FIT MAILJET */
				$mail_user = new Mail();
				$mail_user->setTemplate('registration_created_receipt');
				$mail_user->setFrom($from);
				$mail_user->setRecipient($recipient);
				/* Setting mail variables */
				$mail_user->setMailVar('exhibitor_company', $user->get('company'));
				$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
				$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
				$mail_user->setMailVar('event_email', $fair->get('contact_email'));
				$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
				$mail_user->setMailVar('event_website', $fair->get('website'));
				$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
				$mail_user->setMailVar('position_area', $_POST['area']);
				$mail_user->sendMessage();

				$mailSettings = json_decode($fair->get("mail_settings"));
				if (is_array($mailSettings->recieveRegistration)) {
					/* Check mail settings and send only if setting is set */
					if (in_array("0", $mailSettings->recieveRegistration)) {
						/* Preparing to send the mail */
						$organizer = new User();
						$organizer->load2($fair->get('created_by'), 'id');
						if ($organizer->get('contact_email') == '')
						$recipient = array($organizer->get('email'), $organizer->get('company'));
						else
						$recipient = array($organizer->get('contact_email'), $organizer->get('name'));
						/* UPDATED TO FIT MAILJET */
						$mail_organizer = new Mail();
						$mail_organizer->setTemplate('registration_created_confirm');
						$mail_organizer->setFrom($from);
						$mail_organizer->setRecipient($recipient);
						/* Setting mail variables */
						$mail_organizer->setMailVar('exhibitor_company', $user->get('company'));
						$mail_organizer->setMailVar('event_name', $fair->get('windowtitle'));
						$mail_organizer->setMailVar('event_url', BASE_URL . $fair->get('url'));
						$mail_organizer->setMailVar('arranger_message', $_POST['arranger_message']);
						$mail_organizer->setMailVar('position_area', $_POST['area']);
						$mail_organizer->sendMessage();
					}
				}
				header('Location: /fairRegistration/success');
				return;
			}
		}

		$this->setNoTranslate('fair', $fair);
		$this->setNoTranslate('me', $user);

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