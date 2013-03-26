<?php

function userIsConnectedTo($fairId) {

	if (!isset($_SESSION['user_id'])) {
		return false;
	}

	global $globalDB;

	$stmt = $globalDB->prepare("SELECT * FROM fair_user_relation WHERE fair_user_relation.user=? AND fair_user_relation.fair=$fairId");
	$stmt->execute(array($_SESSION['user_id']));
	$result = $stmt->fetch();

	if ($result) {
		return true;
	} else {
		return false;
	}
}

function getTableName($classname) {
	$tbl = '';
	foreach(str_split($classname) as $char) {
		if ($char == mb_strtoupper($char, 'UTF-8'))
			$tbl .= '_';
		$tbl .= $char;

	}
	return strtolower(substr($tbl, 1));
}

function userLevel() {

	if (isset($_SESSION['user_level']))
		return $_SESSION['user_level'];
	else
		return 0;

}

function toLogin() {
	header("HTTP/1.1 303 See Other");
	header("Location: ".BASE_URL."user/login");
	exit;
}

function setAuthLevel($lvl) {

	if (userLevel() < $lvl)
		toLogin();

}

function sendMail($to, $subject, $msg, $from='') {

	/*mail($to, $subject, $msg, 'From:Chartbooker<no-reply@chartbooker.com>');
	return;*/

	require_once ROOT.'lib/Swift-4.1.7/swift_required.php';

	$transport = Swift_SmtpTransport::newInstance(SMTP_SERVER, SMTP_PORT)
		->setUsername(SMTP_USER)
		->setPassword(SMTP_PASS)
	;

	if (!is_array($from))
		$from = array(EMAIL_FROM_ADDRESS => EMAIL_FROM_NAME);

	$mailer = Swift_Mailer::newInstance($transport);
	$message = Swift_Message::newInstance($subject)
		->setFrom($from)
		->setTo($to)
		->setBody($msg)
	;
	$result = $mailer->send($message);
	return $result;
}

function makeUrl($str, $ignoreCaps=true) {
	if ($ignoreCaps)
		$str = mb_strtolower($str, 'UTF-8');

	//Common chars
	$search = array('å', 'ä', 'ö', 'é', '&', ' ', '/');
	$replace = array('a', 'a', 'o', 'e', 'och', '-', '-');

	//Replace the common chars
	$str = str_replace($search, $replace, $str);

	//Eliminate other forbidden chars
	$str = preg_replace('/([^-a-z0-9._])/i', '', $str);

	//Don't allow more than one dash in a row for aesthetic reasons
	$str = preg_replace('/-{2,}/', '-', $str);

	return $str;
}

function makeOptions($db, $table, $sel=0, $where='') {
	$sql = 'SELECT id, name FROM `'.$table.'`';
	if ($where != '')
		$sql.= ' WHERE '.$where;
	$sql.= ' ORDER BY name';

	//echo $sql;
	$stmt = $db->prepare($sql);
	$stmt->execute(array());
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$ret = '';
	foreach ($result as $res) {
		$chk = ($sel == $res['id']) ? ' selected="selected"' : '';
		$ret.= '<option value="'.$res['id'].'"'.$chk.'>'.$res['name'].'</option>';
	}
	return $ret;
}

function logToDB($db, $action, $data) {
	$data = (is_array($data)) ? json_encode($data) : $data;
	$stmt = $db->prepare("INSERT INTO log (user, action, `time`, `data`) VALUES (?, ?, ?, ?)");
	$stmt->execute(array($_SESSION['user_id'], $action, time(), $data));
}

function tiny_mce($path='js/tiny_mce/tiny_mce.js') {
	
	if (userLevel() < 2) {
		$toolbar = 'theme_advanced_buttons1 : "undo,redo,|,bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink,|,justifyleft,justifycenter,justifyright,justifyfull,|,cut,copy,paste",
	        		theme_advanced_buttons2 : "hr,charmap,|,outdent,indent,|,insertdate,inserttime,|,preview,fullscreen",
	        		theme_advanced_buttons3 : "",
	        		theme_advanced_buttons4 : "",';
	} else {
		$toolbar = 'theme_advanced_buttons1 : "undo,redo,|,bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink,|,justifyleft,justifycenter,justifyright,justifyfull,|,cut,copy,paste",
	        		theme_advanced_buttons2 : "tablecontrols,|,hr,charmap,|,outdent,indent,|,insertdate,inserttime,|,preview,fullscreen,code",
	        		theme_advanced_buttons3 : "",
	        		theme_advanced_buttons4 : "",';
	}
	
	echo '<script language="javascript" type="text/javascript" src="'.$path.'"></script>';
	echo '<script language="javascript" type="text/javascript">

	  tinyMCE.init({
	        //General options
	        mode : "textareas",
	        theme : "advanced",
	        plugins : "style,table,advimage,advlink,inlinepopups,insertdatetime,preview,paste,fullscreen,noneditable,visualchars,xhtmlxtras",

	        // Theme options
	        '.$toolbar.'
	        theme_advanced_toolbar_location : "top",
	        theme_advanced_toolbar_align : "left",
	        theme_advanced_statusbar_location : "bottom",
	        theme_advanced_resizing : false,
	        paste_text_sticky: true,
			paste_text_sticky_default: true,

	        /*content_css : "../css/tiny.css",*/

	        //Drop lists for link/image/media/template dialogs
	        template_external_list_url : "js/template_list.js",
	        external_link_list_url : "js/link_list.js",
	        external_image_list_url : "js/image_list.js",
	        media_external_list_url : "js/media_list.js",
		});
		function toggle(folderID) {
			var folder = document.getElementById(folderID);
			if (folder.style.display == \'block\') {
				folder.style.display = \'none\';
			} else {
				folder.style.display = \'block\';
			}
		}
		function returnImage(path) {
			document.getElementById(\'sound\').value = path;
		}
	  </script>';
}

?>