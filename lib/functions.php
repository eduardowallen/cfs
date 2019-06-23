<?php

function userIsConnectedTo($fairId) {

	if (!isset($_SESSION['user_id'])) {
		return false;
	}

	global $globalDB;

	$stmt = $globalDB->prepare("SELECT * FROM fair_user_relation WHERE fair_user_relation.user = ? AND fair_user_relation.fair = ?");
	$stmt->execute(array($_SESSION['user_id'], $fairId));
	$result = $stmt->fetch();

	if ($result) {
		return true;
	} else {
		return false;
	}
}
function loadRealInvoiceId($fairId) {
	global $globalDB;
	$stmt_invoiceid1 = $globalDB->prepare("SELECT id FROM exhibitor_invoice as id WHERE fair = ? order by id desc limit 1");
	$stmt_invoiceid1->execute(array($fairId));
	$res = $stmt_invoiceid1->fetch(PDO::FETCH_ASSOC);
	$invoice_id1 = $res['id'];
	$stmt_invoiceid2 = $globalDB->prepare("SELECT id FROM exhibitor_invoice_history as id WHERE fair = ? order by id desc limit 1");
	$stmt_invoiceid2->execute(array($fairId));
	$res2 = $stmt_invoiceid2->fetch(PDO::FETCH_ASSOC);
	$invoice_id_history = $res2['id'];

	if ($invoice_id1 > $invoice_id_history) {
		$invoice_id = $invoice_id1;
	} else if ($invoice_id1 < $invoice_id_history) {
		$invoice_id = $invoice_id_history;
	} else {
		$invoice_id = 0;
	}
	return $invoice_id;
}

function userCanAdminFair($fair_id, $map_id) {

	$user_level = userLevel();
	global $globalDB;

	if ($user_level == 4) {
		return true;

	} else if ($user_level == 3) {

		$stmt = $globalDB->prepare("SELECT id FROM fair WHERE created_by = ? AND id = ?");
		$stmt->execute(array($_SESSION['user_id'], $fair_id));
		return ($stmt->fetch(PDO::FETCH_OBJ) ? true : false);

	} else if ($user_level == 2) {

		$stmt = $globalDB->prepare("SELECT map_access FROM fair_user_relation WHERE user = ? AND fair = ?");
		$stmt->execute(array($_SESSION['user_id'], $fair_id));
		$result = $stmt->fetch(PDO::FETCH_OBJ);

		if ($result) {

			$accessible_maps = explode('|', $result->map_access);
			if ($map_id == 0 || in_array($map_id, $accessible_maps)) {
				return true;
			}
		}

	}

	return false;
}

function getMyFairs() {
	global $globalDB;

	if (userLevel() == 2) {
		$stmt = $globalDB->prepare("SELECT id, name
				FROM fair_user_relation AS fur
				LEFT JOIN fair ON fur.fair = fair.id
				WHERE user = ? ORDER BY name");

		$stmt->execute(array($_SESSION['user_id']));
		return $stmt->fetchAll(PDO::FETCH_OBJ);

	} else if (userLevel() == 3) {
		$stmt = $globalDB->prepare("SELECT id, name FROM fair WHERE created_by = ? ORDER BY name");
		$stmt->execute(array($_SESSION['user_id']));
		return $stmt->fetchAll(PDO::FETCH_OBJ);

	} else {
		$stmt = $globalDB->prepare("SELECT id, name FROM fair ORDER BY name");
		$stmt->execute(array($_SESSION['user_id']));
		return $stmt->fetchAll(PDO::FETCH_OBJ);
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
	/**
	 * @param string $from
	 * @param string $from_name
	 * @param string $template
	 * @param array $recipient
	 * @param string $attachment
	 * @return string
	 * @throws Exception
	 */

function sendMessage($from = EMAIL_FROM_ADDRESS, $from_name = EMAIL_FROM_NAME, $recipient = [], $attachment) {

	require_once '/var/www/classes/Mailjet.php';
	if (
		empty($from)
		|| empty($from_name)
	) {
		throw new Exception('You have to supply From (email or number) and FromName');
	}

	$email_body = [
		'Messages'	=>	[
			[
				'From'	=>	[
						'Email'	=>	$from,
						'Name'	=>	$from_name
				],
				'To'	=>	[
					[
						'Email'	=>	$recipient['email'],
						'Name'	=>	$recipient['name']
					]
				],
				'TemplateID'		=> 568886,
				'TemplateLanguage'	=> true
			]
		]
	];

	if ($attachment) {
		$email_body['Attachments'] = [[
			'Content-type' => mime_content_type($attachment),
			'Filename' => basename($attachment),
			'Base64Content' => file_get_contents($attachment)
		]];
	}

	$response = $this->sendAsEmail($email_body);
}


/*function sendMail($to, $subject, $msg, $from='') {

	// If the code runs on testserver, send ALL emails to example@chartbooking.com!
	if (defined('TESTSERV') && TESTSERV) {
		$to = 'example@chartbooking.com';
	}

	require_once ROOT.'lib/Swift-4.1.7/swift_required.php';

	if (MAIL_ENABLED) {
		$transport = Swift_SmtpTransport::newInstance();
	} else {
		$transport = Swift_SmtpTransport::newInstance(SMTP_SERVER, SMTP_PORT)
			->setUsername(SMTP_USER)
			->setPassword(SMTP_PASS)
		;
	}

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

// Sends mail in HTML format. Sets the content type and parses the message content to embed and
//  send images as part of the mail.
function sendMailHTML($to, $subject, $msg, $from='') {

	// If the code runs on testserver, send ALL emails to example@chartbooking.com!
	if (defined('TESTSERV') && TESTSERV) {
		$to = 'example@chartbooking.com';
	}

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
    ->setContentType('text/html')
	;
	$result = $mailer->send($message);
	return $result;
}
*/
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
/*
function logToDB($db, $action, $data) {
	$data = (is_array($data)) ? json_encode($data) : $data;
	$stmt = $db->prepare("INSERT INTO log (user, action, `time`, `data`) VALUES (?, ?, ?, ?)");
	$stmt->execute(array($_SESSION['user_id'], $action, time(), $data));
}*/

function tiny_mce($path='js/tiny_mce/tiny_mce.js', $width=null, $box=null) {
	
	/*if (userLevel() < 2) {
		$toolbar = 'theme_advanced_buttons1 : "undo,redo,|,bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink,|,justifyleft,justifycenter,justifyright,justifyfull,|,cut,copy,paste",
	        		theme_advanced_buttons2 : "hr,charmap,|,outdent,indent,|,insertdate,inserttime,|,preview,fullscreen",
	        		theme_advanced_buttons3 : "",
	        		theme_advanced_buttons4 : "",';
	} else {*/
		$toolbar = 'theme_advanced_buttons1 : "bold,italic,underline,|,bullist,numlist,|,link,unlink,|,justifyleft,justifycenter,justifyright|,cut,copy,paste,code",
	        		theme_advanced_buttons2 : "",
	        		theme_advanced_buttons3 : "",
	        		theme_advanced_buttons4 : "",';
	//}
	
	echo '<script language="javascript" type="text/javascript" src="'.$path.'"></script>';
	echo '<script language="javascript" type="text/javascript">

	  tinyMCE.init({
	        //General options
		';
		if(!$box == null) :
			echo 'mode : "specific_textareas",';
			echo 'editor_selector : "'.$box.'",';
		else :
			echo 'mode : "textareas",';
			echo 'editor_deselector : "no-editor",';
		endif;
		
	        echo '
	        skin : "o2k7",
	        skin_variant : "black",
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
	        media_external_list_url : "js/media_list.js",';
		if(!empty($width)) :

		echo '	
   			theme_advanced_resizing_use_cookie : false,
			width : '.$width.',';

		endif;

		echo'
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

function accessLevelToText($level)
{
  global $translator;
  
  switch($level):
    case 4: return $translator->{'Master'};
    case 3: return $translator->{'Organizer'};
    case 2: return $translator->{'Administrator'};
    default: return $translator->{'Exhibitor'};
  endswitch;
}

function printTime($time)
{
  if ($time > 0) {
  	date_default_timezone_set('Europe/Stockholm');
  	return date('d-m-Y H:i', $time);
  } else {
  	return '-';
  }
}


function posStatusToText($status) {
	global $translator;

	switch ($status) {
		case 0:
			return $translator->{'available'};
		case 1:
			return $translator->{'reserved'};
		case 2:
			return $translator->{'booked'};
		case 3:
			return $translator->{'preliminary booked'};
	}
}

function getGMToffset() {
	$gmt = date('O');
	$gmt_sign = substr($gmt, 0, 1);
	$gmt_offset = floatval(substr($gmt, 1, 2) . '.' . substr($gmt, 3, 4));

	return $gmt_sign . $gmt_offset;
}

// Encodes any HTML special chars to entities INCLUDING single quote
// Stands for "User input to JavaScript"
function ujs($str) {
 return substr(json_encode(htmlspecialchars($str, ENT_QUOTES, 'UTF-8')), 1, -1);
}

// Encodes any HTML special chars to entities EXCLUDING single quote
// Stands for "User input to HTML"
function uh($str) {
	return htmlspecialchars($str, ENT_COMPAT | ENT_HTML5, 'UTF-8');
}
?>
