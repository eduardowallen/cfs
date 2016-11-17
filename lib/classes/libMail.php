<?php 


class libMail {
	private $recipients = array();
	private $from = array();
	private $subject;
	private $body;
	private $body_contenttype;
	private $attachments = array();

	public function __construct() {
		require ROOT.'lib/Swift-4.1.7/swift_required.php';

		$this->from = array(EMAIL_FROM_ADDRESS => EMAIL_FROM_NAME);
	}

	public function addRecipient($name, $email) {
		$this->recipients[$email] = $name;
	}

	public function setRecipients($recipients) {
		$this->recipients = $recipients;
	}

	public function setFrom($name, $email) {
		$this->from = array($email => $name);
	}

	public function setFromArray(array $from) {
		$this->from = $from;
	}

	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function setBody($body, $contenttype = 'text/html') {
		$this->body = $body;
		$this->body_contenttype = $contenttype;
	}

	public function addFileAttachment($filename) {
		$this->attachments[] = Swift_Attachment::fromPath($filename);
	}

	public function addAttachment($data, $filename, $contenttype) {
		$this->attachments[] = Swift_Attachment::newInstance()
			->setBody($data)
			->setContentType($contenttype)
			->setFilename($filename)
			;
	}

	public function send() {
		// If the code runs on testserver, send ALL emails to example@chartbooking.com!
		if (defined('TESTSERV') && TESTSERV) {
			$this->recipients = array('example@chartbooking.com' => 'Chartbooker Development');
			$this->recipients = array('mattias@trinax.se' => 'Chartbooker Development');
		}

		$transport = Swift_SmtpTransport::newInstance(SMTP_SERVER, SMTP_PORT)
			->setUsername(SMTP_USER)
			->setPassword(SMTP_PASS)
		;

		$mailer = Swift_Mailer::newInstance($transport);
		$message = Swift_Message::newInstance($this->subject)
			->setFrom($this->from)
			->setTo($this->recipients)
			->setBody($this->body, $this->body_contenttype)
			;

		foreach($this->attachments as $attachment) {
			$message->attach($attachment);
		}

		return $mailer->send($message);
	}
}

?>
