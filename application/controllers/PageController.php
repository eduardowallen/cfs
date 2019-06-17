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
		$this->set('dates', 'Dates');
		$this->set('openinghours', 'Opening hours');
		
		if ($fairUrl != '' && (int)$fairUrl == 0) {
			$stmt = $this->Page->db->prepare("SELECT event_start, event_stop, windowtitle, contact_info AS content FROM fair WHERE url = ?");
			$stmt->execute(array($fairUrl));
		} else if($fairUrl != '') {
			$stmt = $this->Page->db->prepare("SELECT event_start, event_stop, windowtitle, contact_info AS content FROM fair WHERE id = ?");
			$stmt->execute(array($fairUrl));
		} else {
			$this->setNoTranslate('no_event', true);
			$stmt = $this->Page->db->prepare("SELECT * FROM page_content WHERE page = ? AND language = ?");
			$stmt->execute(array('contact', LANGUAGE));
		}
		$pageContent = $stmt->fetch(PDO::FETCH_ASSOC);
		$content = $pageContent['content'];
		if ($fairUrl != '') {
			$name = $pageContent['windowtitle'];
			$eventstart = $pageContent['event_start'];
			$eventstop = $pageContent['event_stop'];
			$this->setNoTranslate('no_event', false);
			$this->setNoTranslate('eventName', $name);
			$this->setNoTranslate('eventstart', $eventstart);
			$this->setNoTranslate('eventstop', $eventstop);
		}
		$this->setNoTranslate('content', $content);
		setlocale(LC_ALL,"sv_SE.UTF-8");
		
	}

	function rules($fairUrl='') {

		if ($this->is_ajax) {
			$this->setNoTranslate('onlyContent', true);
		}

		$this->set('headline', 'Event rules');
		$this->set('dates', 'Dates');
		$this->set('openinghours', 'Opening hours');
		
		if ($fairUrl != '' && (int)$fairUrl == 0) {
			$stmt = $this->Page->db->prepare("SELECT event_start, event_stop, windowtitle, rules AS content FROM fair WHERE url = ?");
			$stmt->execute(array($fairUrl));
		} else if($fairUrl != '') {
			$stmt = $this->Page->db->prepare("SELECT event_start, event_stop, windowtitle, rules AS content FROM fair WHERE id = ?");
			$stmt->execute(array($fairUrl));
		}
		
		$pageContent = $stmt->fetch(PDO::FETCH_ASSOC);
		$content = $pageContent['content'];
		$name = $pageContent['windowtitle'];
		$eventstart = $pageContent['event_start'];
		$eventstop = $pageContent['event_stop'];
		setlocale(LC_ALL,"sv_SE.UTF-8");
		$this->setNoTranslate('content', $content);
		$this->setNoTranslate('eventName', $name);
		$this->setNoTranslate('eventstart', $eventstart);
		$this->setNoTranslate('eventstop', $eventstop);
		
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