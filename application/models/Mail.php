<?php
// Mail model is used to generate and send mails using editable mail templates.
class Mail extends Model {
	
  protected $to;
  protected $from;
  protected $subject;
  protected $content;
	protected $variables = array();
  
  function __construct($to='', $mailtemplate='', $from='')
  {
    parent::__construct();
    if($mailtemplate=='')
      return;
    
    $this->to = $to;
    $this->from = $from;
    
    // Fetch template
    $stmt = $this->db->prepare("SELECT * FROM mail_content WHERE mail = ? AND language = ?");
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
    
    sendMailHTML($this->to, $this->subject, $this->content, $this->from);
  }
	
}

?>