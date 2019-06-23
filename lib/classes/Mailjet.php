<?php
/**
 * Created by PhpStorm.
 * User: trinaxrobin
 * Date: 2019-01-10
 * Time: 13:18
 */
require '/var/www/vendor/autoload.php';
use Mailjet\Resources as MjResources;

class Mailjet extends \Mailjet\Client {

	/**
	 * @var array
	 */
	protected $api = [
		'key' => '3f3b8980301c44dba983c3b5254c6cb5',
		'secret'  => '312d16952416be2022c02d1120cdf019',
		'version' => 'v3'
	];


	/**
	 * Mailjet constructor.
	 *
	 * @param $api_version 'v3', 'v3.1', 'v4'
	 */
	public function __construct($api_version) {
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
	 * @param string $message_type
	 * @param string $from
	 * @param string $from_name
	 * @param string $subject
	 * @param string $message
	 * @param array $recipients
	 * @return string
	 * @throws Exception
	 */
	public function sendMessage($message_type, $from = EMAIL_FROM_ADDRESS, $from_name = EMAIL_FROM_NAME, $subject = '', $message = '', $recipients) {

		if (
			empty($from)
			|| empty($from_name)
			|| empty($subject)
		) {
			throw new Exception('You have to supply From (email or number), FromName and Subject');
		}

		if ($message_type == 'email') {
			$email_body = [
				'FromEmail' 	=> $from,
				'FromName' 		=> $from_name,
				'Subject' 		=> $subject,
				'Text-part' 	=> 'hej hej',
				'Html-part' 	=> 'hej hej',
				'Recipients'	=>	[
					[
						'Email'	=>	$recipients
					]
				]
			];
/*
			if (is_array($recipients)) {
				$tmpArr = [];
				foreach ($recipients as $email_to) {
					$tmpArr['Email'] = $email_to;
					$email_body['Recipients'][] = $tmpArr;
				}
			} else {
				$email_body['Recipients'] = ['Email' => $recipients];
			}*/
			$response = $this->sendAsEmail($email_body);

		} else if ($message_type == 'sms') {

			$sms_body = [
				'From' => MAILJET_SMS_FROM,
				'To' => $recipients, 					/* Vet att funktionen säger array, men här skickar vi bara in enstaka mottagare */
				'Text'	=> strip_tags($message),
			];
			$response = $this->sendAsSMS($sms_body);
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
