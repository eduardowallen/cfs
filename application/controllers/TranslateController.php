<?php

class TranslateController extends Controller {
	
	function language($lang) {
		
		setcookie('language', $lang, time()+60*60*24*30, '/');
		header('Location: '.$_SERVER['HTTP_REFERER']);
		exit;
		
	}
	
	function all() {
		
		setAuthLevel(4);
		
		if (isset($_POST['save'])) {
			
			$json = array();
			
			foreach ($_POST['string'] as $group=>$lang) {
				
				$stmt = $this->db->prepare("DELETE FROM language_string WHERE `group` = ? AND lang != ?");
				$stmt->execute(array($group, 'en'));

				foreach ($lang as $langId=>$str) {
					if ($str != '') {
						$json[$langId][$_POST['string'][$group]['en']] = $str;
						$stmt = $this->db->prepare("INSERT IGNORE INTO language_string (`value`, `lang`, `group`) VALUES (?, ?, ?)");
						$stmt->execute(array($str, $langId, $group));
					}
				}
			}
			
			foreach ($json as $lang=>$jLang) {
				file_put_contents(ROOT.'application/lang/'.$lang.'.json', json_encode($jLang));
			}
			
		}
		
		$this->set('headline', 'Translate');
		
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
		
		$this->set('langs', $langs);
		$this->set('strings', $strings);
		$this->set('save_label', 'Save');
		
	}
	
}

?>