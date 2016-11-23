<?php

class PageController extends Controller {
	
	function edit($page='', $lang='') {
		
		setAuthLevel(4);
		
		$this->setNoTranslate('page', $page);
		$this->setNoTranslate('lang', $lang);
		
		if ($page != '') {
			
			$this->set('headline', 'Edit page');
			$this->set('content_label', 'Content');
			$this->set('save_label', 'Save');
			
			if (isset($_POST['save'])) {
				
				$stmt = $this->db->prepare("UPDATE page_content SET content = ? WHERE page = ? AND language = ?");
				$stmt->execute(array($_POST['page_content'], $page, $lang));
				
			}
			
			$stmt = $this->Page->db->prepare("SELECT * FROM page_content WHERE page = ? AND language = ?");
			$stmt->execute(array($page, $lang));
			$pageContent = $stmt->fetch(PDO::FETCH_ASSOC);
			$this->setNoTranslate('page_content', $pageContent['content']);
			
			
		} else {
			
			$this->set('headline', 'Edit pages');
			$this->set('th_page', 'Page');
			$this->set('edit_label', 'Edit');
			
			$stmt = $this->Page->db->prepare("SELECT * FROM language ORDER BY `default` DESC, id ASC");
			$stmt->execute(array());
			$langs = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			$stmt = $this->Page->db->prepare("SELECT DISTINCT(page) FROM page_content");
			$stmt->execute(array());
			$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
			
			$this->setNoTranslate('langs', $langs);
			$this->setNoTranslate('pages', $pages);
			
		}
		
	}
	
	function contact($fairUrl='') {

		if ($this->is_ajax) {
			$this->setNoTranslate('onlyContent', true);
		}

		$this->set('headline', 'Contact us');
		
		if ($fairUrl != '' && (int)$fairUrl == 0) {
			$stmt = $this->Page->db->prepare("SELECT email, contact_info AS content FROM fair WHERE url = ?");
			$stmt->execute(array($fairUrl));
		} else if($fairUrl != '') {
			$stmt = $this->Page->db->prepare("SELECT email, contact_info AS content FROM fair WHERE id = ?");
			$stmt->execute(array($fairUrl));
		} else {
			$stmt = $this->Page->db->prepare("SELECT * FROM page_content WHERE page = ? AND language = ?");
			$stmt->execute(array('contact', LANGUAGE));
		}
		$pageContent = $stmt->fetch(PDO::FETCH_ASSOC);
		
		$content = $pageContent['content'];
		if (is_array($pageContent) && array_key_exists('email', $pageContent))
			$content = '<h3><a href="mailto:'.$pageContent['email'].'">'.$pageContent['email'].'</a></h3>'.$content;
		
		$this->setNoTranslate('content', $content);
		
	}
	
	function help() {

		if ($this->is_ajax) {
			$this->setNoTranslate('onlyContent', true);
		}

		$this->set('headline', 'Help');
		
		$stmt = $this->Page->db->prepare("SELECT * FROM page_content WHERE page = ? AND language = ?");
		$stmt->execute(array('help', LANGUAGE));
		$pageContent = $stmt->fetch(PDO::FETCH_ASSOC);
		
		$this->setNoTranslate('content', $pageContent['content']);
		
	}
	
	function help_organizer() {

		if ($this->is_ajax) {
			$this->setNoTranslate('onlyContent', true);
		}

		$this->set('headline', 'Help for organizers');
		
		$stmt = $this->Page->db->prepare("SELECT * FROM page_content WHERE page = ? AND language = ?");
		$stmt->execute(array('help_organizer', LANGUAGE));
		$pageContent = $stmt->fetch(PDO::FETCH_ASSOC);
		
		$this->setNoTranslate('content', $pageContent['content']);
		
	}
	
	function loggedin($action='', $param='') {
		setAuthLevel(1);
		
		if ($action == 'setFair') {
			$_SESSION['user_fair'] = $param;
			
			$f = new Fair;
			$f->load($_SESSION['user_fair'], 'id');
			$_SESSION['fair_windowtitle'] = $f->get('windowtitle');
			// This decides the link for the event buttons 'opts'
			header("Location: ".BASE_URL."start/home");
			exit;
		}
		
		$this->set('headline', 'Home');
		$this->set('loggedin_message', 'You are now logged in');
		
		if ($_SESSION['user_fair'] > 0) {
			$f = new Fair;
			$f->load($_SESSION['user_fair'], 'id');
			$this->set('to', 'to');
			$this->setNoTranslate('fair_app', $f->get('name'));
		} else {
			$this->set('to', '');
			$this->setNoTranslate('fair_app', '');
		}
	}
	
}

?>