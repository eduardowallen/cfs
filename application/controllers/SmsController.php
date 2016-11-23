<?php
class SmsController extends Controller {

	public function __construct($model, $controller, $action) {
		parent::__construct($model, $controller, $action);

		setAuthLevel(2);
	}

	public function index() {
		setAuthLevel(2);

		if (userLevel() == 4) {
			$stmt_sent_sms = $this->db->prepare("SELECT 
				sms.*, COUNT(sr.sms_id) AS num_recipients, fair.name AS fair_name, user.name AS author_name
				FROM sms
				LEFT JOIN sms_recipient AS sr ON sr.sms_id = sms.id
				LEFT JOIN fair ON fair.id = fair_id
				LEFT JOIN user ON user.id = author_user_id
				GROUP BY sms.id
				ORDER BY sent_time DESC
			");
			$stmt_sent_sms->execute();
			$this->setNoTranslate('sent_sms', $stmt_sent_sms->fetchAll(PDO::FETCH_OBJ));
		}
		if (userLevel() == 3) {
			$stmt_sent_sms = $this->db->prepare("SELECT 
				sms.*, COUNT(sr.sms_id) AS num_recipients, 
				fair.name AS fair_name, 
				user.name AS author_name
				FROM sms
				LEFT JOIN sms_recipient AS sr ON sr.sms_id = sms.id
				LEFT JOIN fair ON fair.id = fair_id
				LEFT JOIN user ON user.id = author_user_id
				WHERE `fair`.`created_by` = ?
				GROUP BY sms.id
				ORDER BY sent_time DESC
			");
			$stmt_sent_sms->execute(array($_SESSION['user_id']));
			$this->setNoTranslate('sent_sms', $stmt_sent_sms->fetchAll(PDO::FETCH_OBJ));
		}
		if (userLevel() == 2) {
			$stmt_sent_sms = $this->db->prepare("SELECT 
				sms.*, COUNT(sr.sms_id) AS num_recipients, fair.name AS fair_name, user.name AS author_name, fair_user_relation.user AS fur_user
				FROM sms
				LEFT JOIN sms_recipient AS sr ON sr.sms_id = sms.id
				LEFT JOIN fair ON fair.id = fair_id
				LEFT JOIN user ON user.id = author_user_id
				LEFT JOIN fair_user_relation ON fair.id = fair_user_relation.fair
				WHERE `fair_user_relation`.`user` = ?
				GROUP BY sms.id
				ORDER BY sent_time DESC
			");
			$stmt_sent_sms->execute(array($_SESSION['user_id']));
			$this->setNoTranslate('sent_sms', $stmt_sent_sms->fetchAll(PDO::FETCH_OBJ));
		}
		// Labels
		$this->set('label_sms_stats', 'SMS statistics');
		$this->set('label_fair', 'Fair');
		$this->set('label_from', 'From');
		$this->set('label_sms', 'SMS');
		$this->set('label_num_recipients', 'Number of recipients');
		$this->set('label_num_texts', 'Number of texts');
		$this->set('label_sent_time', 'Sent time');
		$this->set('label_details', 'Details 2');
	}

	public function details($id) {
		setAuthLevel(2);

			$sms = new Sms();
			$sms->load($id, 'id');

			$fair = new Fair();
			$fair->load($sms->get('fair_id'), 'id');

		if (userLevel() == 3) {
			if ($fair->wasLoaded() && $fair->get('created_by') != $_SESSION['user_id']) {
				toLogin();
			}
		}
		if (userLevel() == 2) {
			$sql = "SELECT * FROM fair_user_relation WHERE user = ? AND fair = ?";
			$prep = $this->db->prepare($sql);
			$prep->execute(array($_SESSION['user_id'], $fair->get('id')));
			$result = $prep->fetch(PDO::FETCH_ASSOC);
			$this->setNoTranslate('accessible_maps', explode('|', $result['map_access']));
			if(!$result) {
				toLogin();
			}
		}
		$this->Sms->load($id, 'id');
		$this->setNoTranslate('sms', $this->Sms);
		// Labels
		$this->set('label_sms_details', 'SMS details');
		$this->set('label_fair', 'Fair');
		$this->set('label_from', 'From');
		$this->set('label_sms', 'SMS');
		$this->set('label_num_recipients', 'Number of recipients');
		$this->set('label_num_texts', 'Number of texts');
		$this->set('label_sent_time', 'Sent time');
		$this->set('label_details', 'Details 2');
		$this->set('label_recipient_company', 'Recipient company');
		$this->set('label_recipient_name', 'Recipient name');
		$this->set('label_phone', 'Phone');
		$this->set('label_status', 'Status');
	}

	public function send() {
		if ($this->is_ajax) {
			$this->createJsonResponse();
		}

		try {
			if (!isset($_POST['sms_text']) || $_POST['sms_text'] == '') {
				throw new Exception('You must provide a SMS text.', 1);
			}

			if (!userCanAdminFair($_POST['fair'], 0)) {
				throw new Exception('You are not authorized to use this service.', 2);
			}

			if (!isset($_POST['user']) || !is_array($_POST['user'])) {
				throw new Exception('You must select at least one recipient.', 3);
			}

			$this->Sms->set('fair_id', $_POST['fair']);
			$this->Sms->set('author_user_id', $_SESSION['user_id']);
			$this->Sms->set('text', $_POST['sms_text']);
			$this->Sms->set('num_texts', ceil(mb_strlen($_POST['sms_text'], 'UTF-8') / 160));
			$this->Sms->set('sent_time', time());
			$this->Sms->save();

			if (!$this->Sms->get('id')) {
				throw new Exception('Error when trying to store SMS in database.', 3);
			}

			$mosms = new MoSmsAPI(true, MOSMS_USERNAME, MOSMS_PASSWORD, MOSMS_USE_CUSTOM_SENDER);
			$num_sent = 0;
			$errors = array();

			$_POST['user'] = array_unique($_POST['user']);

			foreach ($_POST['user'] as $user_id) {
				$user = new User();
				$user->load($user_id, 'id');

				if ($user->wasLoaded() && $user->get('contact_phone2') != '') {
					$phone = MoSmsAPI::parsePhone($user->get('contact_phone2'));
					if ($phone !== false) {
						$send_result = $mosms->sendSms($_POST['sms_text'], $phone);

						$this->Sms->addRecipient($user->get('id'), $phone, $send_result);

						if ($send_result > 0) {
							// Error
							$errors[] = sprintf($this->translate->{'Technical error when sending to %s'}, $user->get('company'));

						} else {
							// Success
							++$num_sent;
						}
					} else {
						$errors[] = sprintf($this->translate->{'The phone number for the user %s has the wrong format.'}, $user->get('company'));
					}

				} else {
					$errors[] = sprintf($this->translate->{'The phone number for the user %s is missing.'}, $user->get('company'));
				}
			}

			// Be sure to call the destructor by doing the following:
			$mosms = null;

			$this->setNoTranslate('num_sent', $num_sent);

			if (count($errors) > 0) {
				$this->setNoTranslate('errors', $errors);
			}

		} catch (Exception $ex) {
			$this->set('error', $ex->getMessage());
			$this->setNoTranslate('code', $ex->getCode());
		}
	}
}
?>