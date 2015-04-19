<?php
class MoSmsAPI {

	private $endpoint = 'www.mosms.com/se/';
	private $username = '';
	private $password = '';
	private $custom_sender = false;
	private $curl = null;

	public function __construct($use_secure_endpoint, $username, $password, $custom_sender = false) {
		if ($use_secure_endpoint) {
			$this->endpoint = 'https://' . $this->endpoint;
		} else {
			$this->endpoint = 'http://' . $this->endpoint;
		}

		$this->username = $username;
		$this->password = $password;
		$this->custom_sender = $custom_sender;

		$this->curl = curl_init();
	}

	public function __destruct() {
		curl_close($this->curl);
	}

	private function makeCall($method, $params) {
		$params['username'] = $this->username;
		$params['password'] = $this->password;

		$query_string = array();

		foreach ($params as $key => $value) {
			$query_string[] = urlencode($key) . '=' . urlencode($value);
		}

		$query_string = implode('&', $query_string);

		curl_setopt_array($this->curl, array(
			CURLOPT_URL => $this->endpoint . $method . '.php?' . $query_string,
			CURLOPT_RETURNTRANSFER => true
		));

		return curl_exec($this->curl);
	}

	public function sendSms($text, $phone) {
		if (defined('TESTSERV') && TESTSERV) {
			$phone = '';
		}

		$params = array(
			'nr' => $phone,
			'type' => 'text',
			'data' => utf8_decode($text),
			'allowlong' => 1,
			'customsender' => ($this->custom_sender ? 1 : 0)
		);

		return $this->makeCall('sms-send', $params);
	}

	/* Static methods */

	public static function parsePhone($phone) {
		// Remove everything NOT a number
		$phone = preg_replace('/[^\d]/', '', $phone);

		if (strlen($phone) < 5) {
			return false;
		}

		return $phone;
	}
}
?>