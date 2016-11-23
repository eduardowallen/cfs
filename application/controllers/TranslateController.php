<?php

class TranslateController extends Controller {
	
	function language($lang) {
		
		setcookie('language', $lang, time()+60*60*24*30, '/');
		header('Location: '.$_SERVER['HTTP_REFERER']);
		exit;
		
	}

	function toggle() {
		global $translator;
		$translator->setTranslatorConfig('translator_db', !$translator->getTranslatorConfig('translator_db'));
		header('Location: ' . BASE_URL . 'translate/all');
	}
	
	function all() {
		
		setAuthLevel(4);
		
		if (isset($_POST['save'])) {
			
			$json = array();
			$delete_stmt = $this->db->prepare("DELETE FROM language_string WHERE `group` = ? AND lang != 'en'");
			
			foreach ($_POST['string'] as $group=>$lang) {
				
				$delete_stmt->execute(array($group));
				
				$insert_stmt = $this->db->prepare("INSERT INTO language_string (`value`, `lang`, `group`) VALUES (?, ?, ?)");
				foreach ($lang as $langId=>$str) {
					if ($str != '' && $langId != 'en') {
						$json[$langId][$_POST['string'][$group]['en']] = $str;
						$insert_stmt->execute(array($str, $langId, $group));
					}
				}
			}
			
			foreach ($json as $lang=>$jLang) {
				file_put_contents(ROOT.'application/lang/'.$lang.'.json', json_encode($jLang));
			}
			
		}
		
		$this->set('headline', 'Translate (always have an extra window or tab open with the map of an even so that you are not logged out during translation!)');
		
		$stmt = $this->db->prepare("SELECT * FROM language ORDER BY `default` DESC, id ASC");
		$stmt->execute(array());
		$langs = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$stmt = $this->db->prepare("SELECT * FROM language_string");
		$stmt->execute(array());
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$strings = array();
		foreach ($res as $str) {
			$strings[$str['group']][$str['lang']] = $str['value'];
		}

		global $translator;
		
		$this->setNoTranslate('langs', $langs);
		$this->setNoTranslate('strings', $strings);
		$this->setNoTranslate('translation_on', $translator->getTranslatorConfig('translator_db'));
		$this->set('save_label', 'Save');
		$this->set('translation_on_label', 'Turn translation ON');
		$this->set('translation_off_label', 'Turn translation OFF');
		$this->set('translation_toggle_question_label', 'Are you sure you want to change the state of translation functions?');
		
	}
	
}

?>