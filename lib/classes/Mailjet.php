<?php

require ROOT.'vendor/autoload.php';

class Mailjet extends \Mailjet\Client {


	/**
	 * @var array
	 */
	protected $api = [
		'key' => '3f3b8980301c44dba983c3b5254c6cb5',
		'secret'  => '312d16952416be2022c02d1120cdf019',
		'version' => 'v3.1'
	];

	/**
	 * Mailjet constructor.
	 *
	 * @param $api_version 'v3', 'v3.1', 'v4'
	 */
	public function __construct() {

		parent::__construct(
							$this->api['key'],
							$this->api['secret'],
							true,
							[
								'version' => $this->api['version']
							]
		);
	}
	public function send($body) {
		$response = parent::post(\Mailjet\Resources::$Email, ['body' => $body]);
		//print_r("success här");
		if ($response->success()) {
			//print_r('Success här');
			//error_log(var_dump($response->getData()));
			return $this->parseResponse($response);
		} else {
			print_r('Error här');
			var_dump($response->getData());
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
