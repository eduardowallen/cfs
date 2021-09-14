<?php

require ROOT.'lib/classes/Mailjet.php';

class Mail {
	public $db;
	protected $mail;
	protected $subject;
	protected $variables = array();
	private $template;
	private $serverTemplate = array();
	private $recipient = array();
	private $from = array(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
	private $replyTo = array();
	private $body;
	private $attachment;
	private $filename;
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
				OR `default` = 'sv'
				)
			ORDER BY `default` ASC");

		$stmt->execute(array($mailtemplate, LANGUAGE));
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		$this->template = $result['mailjet_id'];
	}
	public function setServerTemplate($mailtemplate) {
		// Attempts to get template according to currently selected language.
		$stmt = $this->db->prepare("SELECT * FROM `server_templates`
			LEFT JOIN `language` ON `server_templates`.`language` = `language`.`id`
			WHERE `template_name` = ?
			AND (
				`language` = ?
				OR `default` = 'sv'
				)
			ORDER BY `default` ASC");

		$stmt->execute(array($mailtemplate, LANGUAGE));
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$this->serverTemplate = $result;
		//error_log(print_r($this->serverTemplate, TRUE));
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
		// Send messages to testmaster to avoid sending to exhibitors.
		if (DEV) {
			$this->recipient[0] = 'eduardo.wallen@chartbooker.com';
			$this->recipient[1] = 'Eduardo Testmaster';
		}
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
		if (isset($this->variables)) {
			foreach ($this->variables as $key => $value) {
				$this->body['Messages'][0]['Variables'][$key] = $value;
			}
			
		}
		if (isset($this->template)) {
			$this->body['Messages'][0]['TemplateID'] = intval($this->template);
			$this->body['Messages'][0]['TemplateLanguage'] = true;
		}
		if ($this->serverTemplate) {
			// Replace in-text variables with values
			$subject = $this->serverTemplate[0]['subject'];
			$textpart = $this->serverTemplate[0]['textpart'];
			$htmlpart = $this->serverTemplate[0]['htmlpart'];
			foreach($this->variables as $key => $value){
				$subject = str_replace('$'.$key, $value, $subject);
				$textpart = str_replace('$'.$key, $value, $textpart);
				$htmlpart = str_replace('$'.$key, $value, $htmlpart);
			}

			$this->body['Messages'][0]['Subject'] = $subject;
			$this->body['Messages'][0]['TextPart'] = $textpart;
			$this->body['Messages'][0]['HTMLPart'] = $htmlpart;
		}
		
		if (isset($this->attachment)) {
			$this->body['Messages'][0]['Attachments'] = [[
				'ContentType' => mime_content_type($this->attachment),
				'Filename' => $this->filename,
				'Base64Content' => base64_encode(file_get_contents($this->attachment))
			]];
		}
		//error_log(print_r($this->body['Messages'][0]['Variables'], TRUE));
		$response = $this->mail->send($this->body);
	}



	public function setAttachment($attachment) {
		$this->attachment = $attachment;
	}
	public function setRecipient($recipient) {
		$this->recipient = $recipient;
	}
	public function setFilename($filename) {
		$this->filename = $filename;
	}
	public function setFrom($from) {
		$this->from = $from;
	}
	public function setSubject($subject) {
		$this->subject = $subject;
	}


	/**
 	* Funktion för bakåtkompabilitet.
 	* @see Mail::set
 	* @param string $name
 	* @param        $value
 	*/
	public function setMailVar($name, $value) {
		$this->set($name, $value);
	}
	public function set($name, $value) {
		$this->variables[$name] = $value;
	}

}
