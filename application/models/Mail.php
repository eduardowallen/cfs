<?php
/**
 * Send mail using editable mail templates.
 */
class Mail {
	public $db;
	protected $mail;

	protected $subject;
	protected $content;
	protected $variables = array();

	/**
	 * Skicka ett mail med template till $to
	 * @param array  $recipients   [ 'email' => 'name', ...]
	 * @param string $mailtemplate
	 * @param array  $from         [ 'email' => 'name' ]
	 */
	public function __construct() {
		global $globalDB;
		$this->db = $globalDB;

		$this->mail = new libMail();
	}

	public function setRecipients(array $recipients) {
		$this->mail->setRecipients($recipients);
	}

	public function setTemplate($mailtemplate) {
		// Attempts to get template according to currently selected language.
		// If no template exists for the selected language it gets one for the default language.
		$stmt = $this->db->prepare("SELECT * FROM `mail_content`
			LEFT JOIN `language` ON `mail_content`.`language` = `language`.`id`
			WHERE `mail` = ?
			AND (
				`language` = ?
				OR `default` = 1
				)
			ORDER BY `default` ASC");

		$stmt->execute(array($mailtemplate, LANGUAGE));
		$mailContent = $stmt->fetch(PDO::FETCH_ASSOC);
		$this->subject = $mailContent['subject'];
		$this->content = $mailContent['content'];
	}

	public function setFrom(array $from = null) {
		$this->mail->setFromArray($from);
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

	public function attachFile($filename) {
		$this->mail->addFileAttachment($filename);
	}
	public function attach($data, $filename, $contenttype) {
		$this->mail->addAttachment($data, $filename, $contenttype);
	}

	function send() {
		// Replace in-text variables with values
		$subject = $this->subject;
		$body = $this->content;
		foreach($this->variables as $key => $value){
			$subject = str_replace('$'.$key, $value, $subject);
			$body = str_replace('$'.$key, $value, $body);
		}

		$this->mail->setSubject($subject);
		$this->mail->setBody($body);

		return $this->mail->send() > 0;
	}
}

?>
