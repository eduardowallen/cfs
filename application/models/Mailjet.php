<?php
/**
 * Created by PhpStorm.
 * User: trinaxrobin
 * Date: 2019-01-10
 * Time: 13:18
 */

require '/var/www/vendor/autoload.php';
use \Mailjet\Resources;

class Mailjet extends \Mailjet\Client {

	/**
	 * @var array
	 */
	protected $api = [
		'key' => '3f3b8980301c44dba983c3b5254c6cb5',
		'secret'  => '312d16952416be2022c02d1120cdf019',
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

	/**
	* @param string $mailtemplate
	*/
	public function setTemplate($mailtemplate) {
		//print_r(var_dump($this->db));
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
	 * @param string $message
	 * @param string $from
	 * @param string $from_name
	 * @param string $template
	 * @param array $recipients
	 * @param array $attachment
	 * @return string
	 * @throws Exception
	 */
	public function sendMessage($message_type, $from = EMAIL_FROM_ADDRESS, $from_name = EMAIL_FROM_NAME, $to = [], $attachment) {

		if (
			empty($from)
			|| empty($from_name)
		) {
			throw new Exception('You have to supply From (email or number) and FromName');
		}
		//print_r($from);
		//print_r($to['name']);
		if ($message_type === 'email') {
			//error_log('Message type lika med email');
			$email_body = [
				'Messages'	=>	[
					[
						'From'	=>	[
								'Email'	=>	$from,
								'Name'	=>	$from_name
						],
						'To'	=>	[
							[
								'Email'	=>	$to['email'],
								'Name'	=>	$to['name']
							]
						],
						'TemplateID'		=> 568886,
						'TemplateLanguage'	=> true
				        /*'Variables' => json_decode('{
				    		"firstname": "Default"
				  		}', true)*/
					]
				]
			];
				/*'Messages' => [
				      [
				        'From' => [
				          'Email' => "testserver@chartbookerdemo.com",
				          'Name' => "Test"
				        ],
				        'To' => [
				          [
				            'Email' => "passenger1@example.com",
				            'Name' => "passenger 1"
				          ]
				        ],
				        'TemplateID' => 568886,
				        'TemplateLanguage' => true,
				        'Subject' => "Test",
				        'Variables' => json_decode('{
				    "firstname": "Default"
				  }', true)
				      ]
			    ]
/*
			if ($reply_to) {
				$email_body['Headers'] = ['Reply-To' => $reply_to];
			}
*//*
			if ($attachment) {
				$email_body['Attachments'] = [[
					'Content-type' => mime_content_type($attachment),
					'Filename' => basename($attachment),
					'Base64Content' => file_get_contents($attachment)
				]];
			}
*/
			$response = $this->sendAsEmail($email_body);
			/*error_log('Response kommer nu');
			error_log($response);
			error_log('Email body kommer nu');*/

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

			$response = $this->sendAsEmail($email_body);

		}

/*		if ($response->success()) {
			error_log($this->parseResponse($response));
			return $this->parseResponse($response);
		} else {
			throw new Exception('Error: ' . print_r($response->getStatus()));
		}*/
	}

	/**
	 * @param $body
	 * @return string
	 * @throws Exception
	 */
	private function sendAsEmail($body) {
		
		$response = parent::post(\Mailjet\Resources::$Email, ['body' => $body]);

		if ($response->success()) {
			error_log('Success här');
			error_log(var_dump($response->getData()));
			//return $this->parseResponse($response);
		} else {
			error_log('Error här');
			error_log(var_dump($response->getData()));
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
