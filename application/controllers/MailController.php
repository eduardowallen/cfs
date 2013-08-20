<?php
// MailController controls the mail editing interface in the administrative section.
class MailController extends Controller {
	
	function edit($mail='', $lang='') {
		
		setAuthLevel(4);
		
		$this->setNoTranslate('mail', $mail);
		$this->setNoTranslate('lang', $lang);
		
    if ($mail == 'new') {
      // Creating a new mail template
			
			$this->set('headline', 'New mail template');
			$this->set('mail_label_label', 'Mail template name (used in code to refer to this template)');
			$this->set('subject_label', 'Subject');
			$this->set('content_label', 'Content');
			$this->set('save_label', 'Save');
      
			$this->setNoTranslate('mail_label', '');
			$this->setNoTranslate('mail_subject', '');
			$this->setNoTranslate('mail_content', '');
			
			if (isset($_POST['save'])) {
				
				//$stmt = $this->db->prepare("UPDATE mail_content SET subject = ?, content = ? WHERE mail = ? AND language = ?");
				$stmt = $this->db->prepare("INSERT INTO mail_content SET subject = ?, content = ?, mail = ?, language = ?");
				if($stmt->execute(array($_POST['mail_subject'], $_POST['mail_content'], $_POST['mail_label'], $lang)))
        {
          // On success, redirect to editing the new mail template
          header('Location: '.BASE_URL.'mail/edit/'.$_POST['mail_label'].'/'.$lang);
          exit;
        }
        
				// Else fill the form with passed in data
        $this->setNoTranslate('mail_label', $_POST['mail_label']);
        $this->setNoTranslate('mail_subject', $_POST['mail_subject']);
        $this->setNoTranslate('mail_content', $_POST['mail_content']);
			}
      
      
    }
		else if ($mail != '') {
      // Editing a specific mail
			
			$this->set('headline', 'Edit mail');
			$this->set('subject_label', 'Subject');
			$this->set('content_label', 'Content');
			$this->set('save_label', 'Save');
			
			if (isset($_POST['save'])) {

      // Attempt to update the mail, if it fails, assume the user is adding a translated version into the database
				$stmt = $this->db->prepare("UPDATE mail_content SET subject = ?, content = ? WHERE mail = ? AND language = ?");
				if(!$stmt->execute(array($_POST['mail_subject'], $_POST['mail_content'], $mail, $lang)))
        {
          $stmt = $this->db->prepare("INSERT INTO mail_content SET subject = ?, content = ?, mail = ?, language = ?");
          $stmt->execute(array($_POST['mail_subject'], $_POST['mail_content'], $mail, $lang));
        }
				
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
			$this->set('newmail_label', 'New Mail Template');
			
      // Fetch the available languages
			$stmt = $this->Mail->db->prepare("SELECT * FROM language ORDER BY `default` DESC, id ASC");
			$stmt->execute(array());
      $numcols = $stmt->rowCount(); // Get the number of language columns
			$langs = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
      // Fetch the list of mails
			$stmt = $this->Mail->db->prepare("SELECT mail, subject, content, language FROM mail_content LEFT JOIN language on mail_content.language = language.id ORDER BY mail ASC, `default` ASC");
			$stmt->execute(array());
			$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      $mails = array();
      foreach($results as $entry)
      {
        $mails[$entry['mail']]['mail'] = $entry['mail'];
        $mails[$entry['mail']]['subject'] = $entry['subject'];
        $mails[$entry['mail']]['content'] = $entry['content'];
        $mails[$entry['mail']][$entry['language']] = $entry['language'];
      }
      
      $numcols += 2; // Add columns for mail label and subject
			
			$this->setNoTranslate('langs', $langs);
			$this->setNoTranslate('mails', $mails);
			$this->setNoTranslate('numcols', $numcols);
			
		}
		
	}
}

?>