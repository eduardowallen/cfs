<?php
// MailController controls the mail editing interface in the administrative section.
class MailController extends Controller {
	
	function edit($mail='', $lang='') {
		
		setAuthLevel(4);
		
		$this->setNoTranslate('mail', $mail);
		$this->setNoTranslate('lang', $lang);
		
		if ($mail != '') {
      // Editing a specific mail
			
			$this->set('headline', 'Edit mail');
			$this->set('subject_label', 'Subject');
			$this->set('content_label', 'Content');
			$this->set('save_label', 'Save');
			
			if (isset($_POST['save'])) {
				
				$stmt = $this->db->prepare("UPDATE mail_content SET subject = ?, content = ? WHERE mail = ? AND language = ?");
				$stmt->execute(array($_POST['mail_subject'], $_POST['mail_content'], $mail, $lang));
				
			}
			
			$stmt = $this->Mail->db->prepare("SELECT * FROM mail_content WHERE mail = ? AND language = ?");
			$stmt->execute(array($mail, $lang));
			$mailContent = $stmt->fetch(PDO::FETCH_ASSOC);
			$this->setNoTranslate('mail_subject', $mailContent['subject']);
			$this->setNoTranslate('mail_content', $mailContent['content']);
			
		} else {
			// Showing a list of mails to edit
      
			$this->set('headline', 'Edit mails');
			$this->set('th_mail', 'Mail');
			$this->set('th_subject', 'Subject');
			
      // Fetch the available languages
			$stmt = $this->Mail->db->prepare("SELECT * FROM language ORDER BY `default` DESC, id ASC");
			$stmt->execute(array());
			$langs = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
      // Fetch the list of mails
			$stmt = $this->Mail->db->prepare("SELECT mail, subject FROM mail_content WHERE language = ?");
			$stmt->execute(array($langs[0]['id']));
			$mails = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			$this->set('langs', $langs);
			$this->set('mails', $mails);
			
		}
		
	}
}

?>