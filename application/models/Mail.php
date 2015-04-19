<?php
// Mail model is used to generate and send mails using editable mail templates.
class Mail extends Model {
	
  protected $to;
  protected $from;
  protected $fromName;
  protected $subject;
  protected $content;
	protected $variables = array();
  
  function __construct($to='', $mailtemplate='', $from='', $fromName = "")
  {
    parent::__construct();
    if($mailtemplate=='')
      return;
    
    $this->to = $to;
    $this->from = $from;
    $this->fromName = $fromName;
    
    // Attempts to get template according to currently selected language, if template exists for that language
    //  otherwise gets template for the default language
    $stmt = $this->db->prepare("SELECT * FROM mail_content LEFT JOIN language ON mail_content.language = language.id WHERE mail = ? AND (language = ? OR `default` = 1) ORDER BY `default` ASC LIMIT 1");
    $stmt->execute(array($mailtemplate, LANGUAGE));
    $mailContent = $stmt->fetch(PDO::FETCH_ASSOC);
    $this->subject = $mailContent['subject'];
    $this->content = $mailContent['content'];
  }
	
	//Setter
	function setMailVar($name,$value) {
		$this->variables[$name] = $value;
	}
  
  // Sends a mail using a predefined, editable mail template
  function send()
  {
    // Replace in-text variables with values
    foreach($this->variables as $key => $value){
      $this->subject = str_replace('$'.$key, $value, $this->subject);
      $this->content = str_replace('$'.$key, $value, $this->content);
    }

	if ($this->from == '') {
		$this->from = EMAIL_FROM_ADDRESS;
	}

  if ($this->fromName === "") {
    $this->fromName = EMAIL_FROM_NAME;
  }

    return sendMailHTML($this->to, $this->subject, $this->content, array($this->from => $this->fromName));
  }
	
}

?>