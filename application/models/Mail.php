<?php

require ROOT.'lib/classes/Mailjet.php';

class Mail {
	public $db;
	protected $mail;
	private $template;
	private $serverTemplate;
	private $variables = array();
	private $recipient = array();
	private $from = array(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
	private $replyTo = array();
	private $subject;
	private $body;
	private $attachment;
	/**
	 * Mailjet constructor.
	 *
	 * @param $api_version 'v3', 'v3.1', 'v4'
	 */
	public function __construct() {
		global $globalDB;
		$this->db = $globalDB;
		$this->mail = new Mailjet();
	}

	/**
	* @param string $mailtemplate
	*/
	public function setTemplate($mailtemplate) {
		// Attempts to get template according to currently selected language.
		$stmt = $this->db->prepare("SELECT mailjet_id FROM `mailjet_templates`
			LEFT JOIN `language` ON `mailjet_templates`.`language` = `language`.`id`
			WHERE `template_name` = ?
			AND (
				`language` = ?
				OR `default` = 'eng'
				)
			ORDER BY `default` ASC");

		$stmt->execute(array($mailtemplate, LANGUAGE));
		$mailTemplate = $stmt->fetch(PDO::FETCH_ASSOC);
		$this->template = $mailTemplate['mailjet_id'];
	}

	/**
	 * @param string $message
	 * @param string $template
	 * @param array $from
	 * @param array $recipient
	 * @return string
	 * @throws Exception
	 */
	public function sendMessage() {

		$this->body = [
			'Messages'	=>	[
				[
					'From'	=>	[
							'Email'	=>	$this->from[0],
							'Name'	=>	$this->from[1],
					],
					'To'	=>	[
						[
							'Email'	=>	$this->recipient[0],
							'Name'	=>	$this->recipient[1]
						]
					]
				]
			]
		];
		if (isset($this->template)) {
			$this->body['Messages'][0]['TemplateID'] = intval($this->template);
			$this->body['Messages'][0]['TemplateLanguage'] = true;

		}
		if (isset($this->serverTemplate)) {

			$this->body['Messages'][0]['Subject'] = "Noobfil";
			$this->body['Messages'][0]['TextPart'] = "Kolla här då din noob justeee.";
			$this->body['Messages'][0]['HTMLPart'] = "<h3>Juste noob här kommer en fil såatteeeh det funkar! yää</h3>";

		}
		
		if (isset($this->attachment)) {
			$this->body['Messages'][0]['Attachments'] = [[
				'ContentType' => mime_content_type($this->attachment),
				'Filename' => basename($this->attachment),
				'Base64Content' => base64_encode(file_get_contents($this->attachment))
			]];
		}
		//var_dump($this->body);
		$response = $this->mail->send($this->body);
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
	 public function setServerTemplate($mailtemplate) {
		$this->serverTemplate = $mailtemplate;
	}
	 public function setAttachment($attachment) {
		$this->attachment = $attachment;
	}
	public function set($name, $value) {
		$this->variables[$name] = $value;
	}

	public function setRecipient($recipient) {
		$this->recipient = $recipient;
	}

	public function setFrom($from) {
		$this->from = $from;
	}

	public function setSubject($subject) {
		$this->subject = $subject;
	}


	

}
