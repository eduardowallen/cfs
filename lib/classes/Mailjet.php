<?php
/**
 * Created by PhpStorm.
 * User: trinaxrobin
 * Date: 2019-01-10
 * Time: 13:18
 */

use Mailjet\Resources as MjResources;

class Mailjet extends \Mailjet\Client {

	/**
	 * @var array
	 */
	protected $api = [
		'key' => MAILJET_APIKEY,
		'secret'  => MAILJET_SECRETKEY,
		'version' => 'v3.1',
	];

	protected $template;
	protected $variables = array();

	/**
	 * Mailjet constructor.
	 *
	 * @param $api_version 'v3', 'v3.1', 'v4'
	 */
	public function __construct($api_version) {

		global $globalDB;
		$this->db = $globalDB;
		
		parent::__construct(
							$this->api['key'],
							$this->api['secret'],
							true,
							[
								'version' => isset($api_version) ? $api_version : $this->api['version']
							]
		);
	}
	
	/**
 	* Funktion för bakåtkompabilitet.
 	* @see Mail::set
 	* @param string $name
 	* @param        $value
 	*/
	 public function setMailVar($name,$value) {
		$this->set($name, $value);
	}

	public function set($name, $value) {
		$this->variables[$name] = $value;
	}

	public function setTemplate($template) {
		// Attempts to get template according to currently selected language.
		$stmt = $this->db->prepare("SELECT * FROM `mailjet_templates`
			LEFT JOIN `language` ON `mailjet_templates`.`language` = `language`.`id`
			WHERE `template_name` = ?
			AND (
				`language` = ?
				OR `default` = 1
				)
			ORDER BY `default` ASC");

		$stmt->execute(array($template, LANGUAGE));
		$mailTemplate = $stmt->fetch(PDO::FETCH_ASSOC);
		$this->template = $mailTemplate['mailjet_id'];
	}

	/**
	 * @param string $message_type
	 * @param string $from
	 * @param string $from_name
	 * @param string $template
	 * @param array $recipients
	 * @param array $attachment
	 * @return string
	 * @throws Exception
	 */
	public function sendMessage($message_type, $from = EMAIL_FROM_ADDRESS, $from_name = EMAIL_FROM_NAME, $recipients = [], $attachment = []) {

		if (
			empty($from)
			|| empty($from_name)
			|| empty($subject)
		) {
			throw new Exception('You have to supply From (email or number), FromName and Subject');
		}
		if ($message_type == 'email') {
			$email_body = [
				'FromEmail' 		=> $from,
				'FromName' 			=> $from_name,
				'TemplateID'		=> $template,
				'TemplateLanguage'	=> true,
				'Vars'				=> $variables
			];

			if (is_array($recipients)) {
				$tmpArr = [];
				foreach ($recipients as $email_to) {
					$tmpArr['Email'] = $email_to;
					$email_body['Recipients'][] = $tmpArr;
				}
			} else {
				$email_body['Recipients'] = ['Email' => $recipients];
			}
			if (is_array($reply_to)) {
				$email_body['Headers'] = ['Reply-To' => $reply_to];
			}
			if (is_array($attachment)) {
				foreach($attachment as $file)
				$email_body['Attachments'] => [[
					'Content-type' => $file['type'],
					'Filename' => $file['name'],
					'content' => $file['content']
				]];
			}
			$response = $this->sendAsEmail($email_body);

		} else if ($message_type == 'sms') {

			$sms_body = [
				'From' => MAILJET_SMS_FROM,
				'To' => $recipients, 					/* Vet att funktionen säger array, men här skickar vi bara in enstaka mottagare */
				'Text'	=> strip_tags($message),
			];
			$response = $this->sendAsSMS($sms_body);
		} else if ($message_type == 'custom_email') {
			$email_body = [
				'FromEmail' 	=> $from,
				'FromName' 		=> $from_name,
				'Subject' 		=> $subject,
				'Text-part' 	=> strip_tags($message),
				'Html-part' 	=> $message
			];

			if (is_array($recipients)) {
				$tmpArr = [];
				foreach ($recipients as $email_to) {
					$tmpArr['Email'] = $email_to;
					$email_body['Recipients'][] = $tmpArr;
				}
			} else {
				$email_body['Recipients'] = ['Email' => $recipients];
			}
			if (is_array($reply_to)) {
				$email_body['Headers'] = ['Reply-To' => $reply_to];
			}
			if (is_array($attachment)) {
				foreach($attachment as $file)
				$email_body['Attachments'] => [[
					'Content-type' => $file['type'],
					'Filename' => $file['name'],
					'content' => $file['content']
				]];
			}
			$response = $this->sendAsEmail($email_body);

		}

		if ($response->success()) {
			return $this->parseResponse($response);
		} else {
			throw new Exception('Error: ' . print_r($response->getStatus()));
		}
	}

	/**
	 * @param $body
	 * @return string
	 * @throws Exception
	 */
	private function sendAsEmail($body) {
		$response = parent::post(\Mailjet\Resources::$Email, ['body' => $body]);
		if ($response->success()) {
			return $this->parseResponse($response);
		} else {
			throw new Exception('Error: ' . print_r($response->getStatus()));
		}
	}

	/**
	 * @param $body
	 * @return string
	 * @throws Exception
	 */
	private function sendAsSMS($body) {
		$response = parent::post(\Mailjet\Resources::$Sms, ['body' => $body]);
		if ($response->success()) {
			return $this->parseResponse($response);
		} else {
			throw new Exception('Error: ' . print_r($response->getStatus()));
		}
	}

	/**
	 * @param $response
	 * @return string
	 */
	private function parseResponse($response) {
		return sprintf(
			'<pre>%s</pre>',
			json_encode($response->getData(), JSON_PRETTY_PRINT)
		);
	}


}
