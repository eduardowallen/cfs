<?php

$parts = explode('/', dirname(dirname(__FILE__)));
$parts = array_slice($parts, 0, -1);
define('ROOT', implode('/', $parts).'/');


session_start();
require_once ROOT.'config/config.php';
require_once ROOT.'lib/functions.php';

$lang = (isset($_COOKIE['language'])) ? $_COOKIE['language'] : 'eng';
define('LANGUAGE', $lang);
$translator = new Translator($lang);

$globalDB = new Database;
global $globalDB;

function __autoload($className) {
	if (file_exists(ROOT.'lib/classes/'.$className.'.php')) {
		require_once(ROOT.'lib/classes/'.$className.'.php');

	} else if (file_exists(ROOT.'application/models/'.$className.'.php')) {
		require_once(ROOT.'application/models/'.$className.'.php');
	}
}

function userHasAccess() {
	
}

if (isset($_POST['markPositionAsBeingEdited'])) {
	$pos = new FairMapPosition;
	$pos->load($_POST['markPositionAsBeingEdited'], 'id');
	$pos->set('being_edited', $_SESSION['user_id']);
	$pos->set('edit_started', time());
	$pos->save();
	exit;
	
}

if (isset($_POST['markPositionAsNotBeingEdited'])) {
	$pos = new FairMapPosition;
	$pos->load($_POST['markPositionAsNotBeingEdited'], 'id');
	$pos->set('being_edited', 0);
	$pos->set('edit_started', 0);
	$pos->save();
	exit;
	
}

if (isset($_POST['delete_copied_fairreg'])) {
	$fair_registration = new FairRegistration();
	$fair_registration->load($_POST['delete_copied_fairreg'], 'id');
	if ($fair_registration->wasLoaded()) {
		unset($_SESSION['copied_fair_registration']);
		$fair_registration->delete();
	}
}

if (isset($_GET['getBusyStatus'])) {
	$pos = new FairMapPosition();
	$pos->load($_GET['getBusyStatus'], 'id');

	$map = new FairMap;
	$map->load($pos->get('map'), 'id');

	$num_prel_bookings = 0;

	if (userLevel() > 1 && userCanAdminFair($map->get('fair'), $map->get('id'))) {
		// Prepare a SQL statement for getting the number of prelimnary bookings for a position
		$num_prel_booking_stmt = $globalDB->prepare("SELECT COUNT(*) AS cnt FROM preliminary_booking WHERE position = ?");

		// Fetch any preliminary bookings for this position
		$num_prel_booking_stmt->execute(array($pos->get('id')));
		$num_prel_result = $num_prel_booking_stmt->fetchObject();

		if (isset($num_prel_result->cnt)) {
			$num_prel_bookings = $num_prel_result->cnt;
		}

	} else if (userLevel() == 1) {
		// Check if this Exhibitor has preliminary booked this position
		$user = new User;
		$user->load($_SESSION['user_id'], 'id');
		$preliminaries = $user->getPreliminaries();
		foreach ($preliminaries as $p) {
			if ($p['position'] == $pos->get('id')) {
				++$num_prel_bookings;
				break;
			}
		}
	}

	header('Content-type: application/json; charset=utf-8');
	echo json_encode(array(
		'position' => $pos->get('id'),
		'being_edited' => ($pos->get('being_edited') > 0),
		'edit_started' => $pos->get('edit_started'),
		'statusText' => ($num_prel_bookings > 0 && $pos->get('status') == 0 ? 'applied' : $pos->getStatusText())
	));

	exit;
}

if (isset($_POST['getPreliminary'])) {
	
	$query = $globalDB->query("SELECT prel.*, user.* FROM preliminary_booking AS prel LEFT JOIN user ON prel.user = user.id WHERE position = '".$_POST['getPreliminary']."'");
	$result = $query->fetch(PDO::FETCH_ASSOC);
	
	$result['categories'] = explode('|', $result['categories']);
	
	//die(json_encode($result));
	
}

if (isset($_POST['init'])) {

	$map = new FairMap();
	$map->load($_POST['init'], 'id');

	$fair = new Fair();
	$fair->loadsimple($map->get('fair'), 'id');

	$fairInvoice = new FairInvoice();
	$fairInvoice->load($map->get('fair'), 'fair');

	$prels = array();
	if (userLevel() == 1) {
		$user = new User;
		$user->load($_SESSION['user_id'], 'id');
		$preliminaries = $user->getPreliminaries();
		foreach ($preliminaries as $p) {
			$prels[] = $p['position'];
		}

	}

	$userId = (userLevel() > 0) ? $_SESSION['user_id'] : 0;
	$ret = array(
		'userlevel' => userLevel(),
		'user_id' =>  $userId,
		'preliminaries' => $prels,
		'id'=> $map->get('id'),
		'fair'=> $map->get('fair'),
		'currency'=> $fair->get('currency'),
		'name'=>$map->get('name'),
		'image'=>$map->get('large_image'),
		'large_image'=>$map->get('large_image'),
		'positions'=>array()
	);

	if (userLevel() > 1) {
		// Prepare a SQL statement for getting the number of prelimnary bookings for a position
		$num_prel_booking_stmt = $globalDB->prepare("SELECT COUNT(*) AS cnt FROM preliminary_booking WHERE position = ?");
	}

	foreach ($map->get('positions') as $pos) {
		$ex = $pos->get('exhibitor');
		$cats = array();
		$opts = array();
		$arts = array();
		$num_prel = 0;
		$applied = 0;
		unset($ex->password);
		if (is_object($ex)) {
			$ex->set('commodity', $ex->get('spot_commodity'));
			foreach ($ex->get('exhibitor_categories') as $cat) {
				$c = new ExhibitorCategory;
				$c->load($cat, 'id');
				if ($c->wasLoaded()) {
					$c->set('category_id', $cat);
					$cats[] = $c;
				}
			}
			
			$ex->set('categories', $cats);

			foreach ($ex->get('exhibitor_options') as $opt) {
				$o = new FairExtraOption;
				$o->load($opt, 'id');
				if ($o->wasLoaded()) {
					$o->set('option_id', $opt);
					$opts[] = $o;
				}
			}

			$ex->set("options", $opts);
			$articles = $ex->get('exhibitor_articles');
			$amount = $ex->get('exhibitor_articles_amount');
			foreach (array_combine($articles, $amount) as $art => $qt) {
				$a = new FairArticle;
				$a->load($art, 'id');
				if ($a->wasLoaded()) {
					$a->set('article_id', $art);
					$a->set('amount', $qt);
					$arts[] = $a;
									
				}
			}

			$ex->set("articles", $arts);
		} else if (userLevel() > 1) {

			$applied = 0;
			if (userCanAdminFair($map->get('fair'), $map->get('id'))) {

				// Fetch any preliminary bookings for this position
				$num_prel_booking_stmt->execute(array($pos->get('id')));
				$num_prel_result = $num_prel_booking_stmt->fetchObject();

				if ($num_prel_result->cnt > 0) {
					$applied = 1;
					$num_prel = $num_prel_result->cnt;
				}
			}

		} else if (in_array($pos->get('id'), $prels)) {
			$applied = 1;

		}

		// If this position has been edited for a very long time, reset the flags!
		// Maybe the user just shut down the page or some error occured?
		if ($pos->get('being_edited') > 0 && $pos->get('edit_started')+60*3 < time()) {
			$pos->set('being_edited', 0);
			$pos->set('edit_started', 0);
			$pos->save();
		}

		$length = array_push($ret["positions"], array(
			'id' => $pos->get('id'),
			'x' => $pos->get('x'),
			'y' => $pos->get('y'),
			'name' => $pos->get('name'),
			'area' => $pos->get('area'),
			'price' => $pos->get('price'),
			'vat' => $fairInvoice->get('pos_vat'),
			'information' => $pos->get('information'),
			'status' => $pos->get('status'),
			'statusText' => $pos->getStatusText(),
			'exhibitor' => $ex,
			'expires' => date('d-m-Y H:i', strtotime($pos->get('expires'))),
			'applied' => $applied,
			'num_prel_bookings' => $num_prel,
			'being_edited' => $pos->get('being_edited'),
			'edit_started' => $pos->get('edit_started')
		));
		if (isset($user)) {
			$ret["positions"][$length - 1]["preliminaries"] = $user->getPreliminaries();
		}
	}

	echo json_encode($ret);
	exit;

}

if (isset($_POST['deleteMarker'])) {

	if (userLevel() < 2)
		exit;

	$pos = new FairMapPosition;
	$pos->load($_POST['deleteMarker'], 'id');
	$pos->delete();
	exit;

}


if (isset($_POST['bookPosition'])) {

	if (userLevel() < 1)
		exit;

	$map = new FairMap();
	$map->load($_POST['map'], 'id');

	$pos = new FairMapPosition();
	$pos->load($_POST['bookPosition'], 'id');
	
	//Delete existing exhibitor if position is booked
	if ($pos->get('status') === 0) {
		$exhibitor = new Exhibitor;
	} else {
		$exhibitor = new Exhibitor;
		$exhibitor->load($pos->get('id'), 'position');
	}

	if (isset($_POST['user']) && userLevel() > 1) {
		$exhibitor->set('user', $_POST['user']);
	} else {
		$exhibitor->set('user', $_SESSION['user_id']);
	}
		
	$exhibitor->set('position', $_POST['bookPosition']);
	$exhibitor->set('map', $_POST['map']);
	$exhibitor->set('fair', $map->get('fair'));
	$exhibitor->set('commodity', $_POST['commodity']);
	$exhibitor->set('arranger_message', $_POST['message']);
	$exhibitor->set('edit_time', 0);
	$exhibitor->set('clone', 0);
	$exhibitor->set('status', 2);
	$exId = $exhibitor->save();

	$categoryNames = array();

	if (isset($_POST['categories']) && is_array($_POST['categories'])) {
		$stmt = $pos->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
		foreach ($_POST['categories'] as $cat) {
			$stmt->execute(array($exId, $cat));
			$category = new ExhibitorCategory();
			$category->load($cat, "id");
			if ($category->wasLoaded()) {
				$categoryNames[] = $category->get("name");
			}
		}
	}


	$options = array();
	if (isset($_POST['options']) && is_array($_POST['options'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");
		foreach ($_POST['options'] as $opt) {
			$stmt->execute(array($exId, $opt));
			$ex_option = new FairExtraOption();
			$ex_option->load($opt, 'id');
			if ($ex_option->wasLoaded()) {
				$option_id[] = $ex_option->get('custom_id');
				$option_text[] = $ex_option->get('text');
				$option_price[] = $ex_option->get('price');
				$option_vat[] = $ex_option->get('vat');
			}
		}
		$options = array($option_id, $option_text, $option_price, $option_vat);
	}
	

	$articles = array();
	
	if (isset($_POST['articles']) && is_array($_POST['articles'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
		$arts = $_POST['articles'];
		$amounts = $_POST['artamount'];

		foreach (array_combine($arts, $amounts) as $art => $amount) {
			$stmt->execute(array($exId, $art, $amount));
			$arts = new FairArticle();
			$arts->load($art, 'id');
			if ($arts->wasLoaded()) {
				$art_id[] = $arts->get('custom_id');
				$art_text[] = $arts->get('text');
				$art_amount[] = $amount;
				$art_price[] = $arts->get('price');
				$art_vat[] = $arts->get('vat');
			}
		}
		$articles = array($art_id, $art_text, $art_price, $art_amount, $art_vat);
	}

	$status = 2;
	$pos->set('status', $status);
	$pos->set('expires', '0000-00-00 00:00:00');
	$pos->save();

	$stmt = $pos->db->prepare("UPDATE exhibitor_invoice SET status = 2 WHERE exhibitor = ? AND status <= 2");
	$stmt->execute(array($exId));

	// Send mail
	$categories = implode('<br/> ', $categoryNames);
	$time_now = date('d-m-Y H:i');
	
	$pos = new FairMapPosition();
	$pos->load($exhibitor->get('position'), 'id');

	$fair = new Fair();
	$fair->load($exhibitor->get('fair'), 'id');
	
	$organizer = new User();
	$organizer->load($fair->get('created_by'), 'id');

	$fairInvoice = new FairInvoice();
	$fairInvoice->load($exhibitor->get('fair'), 'fair');

	$user = new User();
	$user->load($exhibitor->get('user'), 'id');
	$userId = $user->get('id');


/*********************************************************************************/
/*********************************************************************************/
/*****************     SENDER ADDRESS AND PAYMENT OPTIONS        *****************/
/*********************************************************************************/
/*********************************************************************************/


				$sender_billing_reference = $fairInvoice->get('reference');
				$sender_billing_company_name = $fairInvoice->get('company_name');
				$sender_billing_address = $fairInvoice->get('address');
				$sender_billing_zipcode = $fairInvoice->get('zipcode');
				$sender_billing_city = $fairInvoice->get('city');
				$sender_billing_country = $fairInvoice->get('country');
				$sender_billing_orgnr = $fairInvoice->get('orgnr');
				$sender_billing_phone = $fairInvoice->get('phone');
				$sender_billing_website = $fairInvoice->get('website');


				$rec_billing_company_name = $user->get('invoice_company');
				$rec_billing_address = $user->get('invoice_address');
				$rec_billing_zipcode = $user->get('invoice_zipcode');
				$rec_billing_city = $user->get('invoice_city');
				$rec_billing_country = $user->get('invoice_country');

				if ($rec_billing_country == 'Sweden')
					$rec_billing_country = 'Sverige';

				if ($rec_billing_country == 'Norway')
					$rec_billing_country = 'Norge';


				$printdate_label = $translator->{'Print date'};
				$required_at_payment_label = $translator->{'must be stated at payment'};
				$orgnr_label = $translator->{'Org.no'};
				$vat_label = $translator->{'TAX.no'};
				$description_label = $translator->{'Description'};
				$price_label = $translator->{'Price'};
				$amount_label = $translator->{'Amount'};
				$booked_space_label = $translator->{'Booked stand'};
				$options_label = $translator->{'Options'};
				$articles_label = $translator->{'Articles'};
				$tax_label = $translator->{'Tax'};
				$parttotal_label = $translator->{'Subtotal'};
				$net_label = $translator->{'Net'};
				$rounding_label = $translator->{'Rounding'};
				$to_pay_label = $translator->{'to pay:'};
				$address_contact_label = $translator->{'Address & Contact'};
				$organization_label = $translator->{'Organization'};
				$payment_info_label = $translator->{'Payment information'};
				$s_reference_label = $translator->{'Our reference'};
				$r_reference_label = $translator->{'Your reference'};
				$st_label = $translator->{'st'};


				$current_user = new User();
				$current_user->load($_SESSION['user_id'], 'id');



/*************************************************************/
/*************************************************************/
/*****************     PRICES AND AMOUNTS        *****************
/*************************************************************/
/*************************************************************/

				$fairId = $fair->get('id');
				$fairname = $fair->get('name');
				$fairurl = $fair->get('url');
				$totalPrice = 0;
				$VatPrice0 = 0;
				$VatPrice12 = 0;
				$VatPrice25 = 0;
				$excludeVatPrice0 = 0;
				$excludeVatPrice12 = 0;
				$excludeVatPrice25 = 0;
				$position_vat = 0;
				$currency = $fair->get('currency');
				$position_name = $pos->get('name');
				$position_price = $pos->get('price');
				$position_vat = $fairInvoice->get('pos_vat');
				$exhibitor_company_name = $user->get('company');
				$exhibitor_name = $user->get('name');



/*********************************************************************************************/
/*********************************************************************************************/
/*****************    					SET MAIL CONTENT 	  				******************/
/*********************************************************************************************/
/*********************************************************************************************/

$html = '<style>
* {
	box-sizing:border-box;
}
hr {
	width:690px;
	text-align:left;
}
tr .normal {
	width: 150px;
}
tr .normal2 {
	width:250px;
}
tr .normal3 {
	width:160px;
}

.short {
	width: 31px;
}
.id {
	width: 80px;
}
.name {
	width: 300px;
}
.price{
	width: 80px;
	text-align: right;
	padding-right: 12px;
}
.amount {
	width: 100px;
	text-align:center;
}
.moms {
	width:50px;
}
.center {
	text-align:center;
}
.left {
	text-align:left;
}
.right {
	text-align:right;
}
.vat {
	width: 80px;
	text-align: left;
}
.dark {
	background-color: #D4D4D4;
}
.totalprice {
	width: 445;
	text-align: right;
	font-size: 20px;
}
.totalprice2 {
	width: 400;
	text-align: right;
	font-size: 20px;
}
.pennys {
	width: 400;
	text-align: right;
	font-size: 16px;
}
</style>

<table>
	<thead>
	    <tr class="dark">
	    	<th class="id">ID</th>
	        <th class="name">'.$description_label.'</th>
	        <th class="price">'.$price_label.'</th>
	        <th class="amount">'.$amount_label.'</th>
	        <th class="moms right">'.$tax_label.'</th>
	        <th class="price">'.$parttotal_label.'</th>
	    </tr>
    </thead>
    <tbody>';

$html .= '<tr><td></td></tr><tr><td class="id"></td><td class="name"><b>'.$booked_space_label.'</b></td></tr>
<tr>
	<td class="id"></td>
    <td class="name">' . $position_name . '</td>
    <td class="price">' . $position_price . '</td>
	<td class="amount">1 '.$st_label.'</td>
	<td class="moms right">' . $position_vat . '%</td>
	<td class="price right">' . number_format($position_price, 2, ',', ' ') . '</td>
</tr>';

	if ($position_vat == 25) {
		$excludeVatPrice25 += $position_price;
	} else {
		$excludeVatPrice0 += $position_price;
	}

if (!empty($_POST['options']) && is_array($_POST['options'])) {
	$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$options_label.'</b></td></tr>';

	for ($row=0; $row<count($options[1]); $row++) {
	    $html .= '<tr>
	    	<td class="id">' . $options[0][$row] . '</td>
	        <td class="name">' . $options[1][$row] . '</td>
	        <td class="price">' . $options[2][$row] . '</td>
	        <td class="amount">1 '.$st_label.'</td>
	        <td class="moms right">' . $options[3][$row] . '%</td>
	        <td class="price right">' . str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')) . '</td>
	        </tr>';
    }
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	
	$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$articles_label.'</b></td></tr>';
	for ($row=0; $row<count($articles[1]); $row++) {
	    $html .= '<tr>
	    	<td class="id">' . $articles[0][$row] . '</td>
	        <td class="name">' . $articles[1][$row] . '</td>
	        <td class="price">' . str_replace('.', ',', $articles[2][$row]) . '</td>
	        <td class="amount center">' . $articles[3][$row] . ' '.$st_label.'</td>
	        <td class="moms right">' . $articles[4][$row] . '%</td>
	        <td class="price right">' . str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')) . '</td>
	        </tr>';
	        $articles[2][$row] = str_replace(',', '.', $articles[2][$row]);
    }
}


if (!empty($_POST['options']) && is_array($_POST['options'])) {
	for ($row=0; $row<count($options[1]); $row++) {

		if ($options[3][$row] == 25) {
			$excludeVatPrice25 += $options[2][$row];
		}
		if ($options[3][$row] == 12) {
			$excludeVatPrice12 += $options[2][$row];
		}		
		if ($options[3][$row] == 0) {
			$excludeVatPrice0 += $options[2][$row];
		}		
	}
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	for ($row=0; $row<count($articles[1]); $row++) {

		if ($articles[4][$row] == 25) {
			$excludeVatPrice25 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}		
		if ($articles[4][$row] == 12) {
			$excludeVatPrice12 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 0) {
			$excludeVatPrice0 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}		
	}
}

$VatPrice0 = $excludeVatPrice0;
$VatPrice12 = $excludeVatPrice12*0.12;
$VatPrice25 = $excludeVatPrice25*0.25;
$totalPrice += $excludeVatPrice12 + $excludeVatPrice25 + $VatPrice12 + $VatPrice25 + $VatPrice0;

$totalPriceRounded = round($totalPrice);
$pennys = ($totalPriceRounded - $totalPrice);

$html .= '
</tbody></table>
<hr>
<table>
	<thead>
	    <tr>
	        <th class="vat"></th>
	        <th class="vat"></th>
	        <th class="vat"></th>
	        <th class="totalprice"></th>
	    </tr>
    </thead>
    <tbody>
	<tr>
		<td class="vat">'.$net_label.'</td>
		<td class="vat">'.$tax_label.' %</td>
		<td class="vat">'.$tax_label.':</td>
		<td class="totalprice"></td>
	</tr>';

if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
	$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
	$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');
}
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice12) . '</td>
		<td class="vat">12,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice12) . '</td>
		<td class="pennys">'.$rounding_label.':&nbsp;&nbsp;'
		. str_replace('.', ',', number_format($pennys, 2, ',', ' ')) . 
		'</td>
		</tr>';

if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
	$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
	$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
}
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice25) . '</td>
		<td class="vat">25,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice25) . '</td>
		<td class="totalprice2">'.$currency.' '.$to_pay_label.'&nbsp;&nbsp;'
		. str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')) . 
		'</td>
		</tbody></table>';



$html .= '</tbody></table>';


	$arranger_message = $_POST['arranger_message'];
	if ($arranger_message == '') {
		$arranger_message = $translator->{'No message was given.'};
	}
	$exhibitor_commodity = $_POST['commodity'];
	if ($exhibitor_commodity == '') {
		$exhibitor_commodity = $translator->{'No commodity was entered.'};
	}

	if ($fair->wasLoaded()) {
		$mailSettings = json_decode($fair->get("mail_settings"));
		if (is_array($mailSettings->bookingCreated)) {
			$status = posStatusToText($status);

			if (in_array("0", $mailSettings->bookingCreated)) {
				$mail_organizer = new Mail($organizer->get('email'), 'booking_created_confirm', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
				$mail_organizer->setMailVar('booking_table', $html);
				$mail_organizer->setMailvar('status', $status);
				$mail_organizer->setMailvar("event_name", $fair->get("name"));
				$mail_organizer->setMailvar("exhibitor_name", $user->get("name"));
				$mail_organizer->setMailvar("company_name", $user->get("company"));
				$mail_organizer->setMailVar("position_name", $pos->get("name"));
				$mail_organizer->setMailVar("position_information", $pos->get("information"));
				$mail_organizer->setMailVar("url", BASE_URL . $fair->get("url"));
				$mail_organizer->setMailVar('arranger_message', $arranger_message);
				$mail_organizer->setMailVar('exhibitor_commodity', $exhibitor_commodity);
				$mail_organizer->setMailVar("exhibitor_category", $categories);
				$mail_organizer->setMailVar('booking_time', $time_now);
				$mail_organizer->send();
			}
			if (in_array("1", $mailSettings->bookingCreated)) {
				$mail_user = new Mail($user->get('email'), 'booking_created_receipt', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
				$mail_user->setMailVar('booking_table', $html);
				$mail_user->setMailvar('status', $status);
				$mail_user->setMailvar("event_name", $fair->get("name"));
				$mail_user->setMailVar('event_email', $fair->get('contact_email'));
				$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
				$mail_user->setMailVar('event_website', $fair->get('website'));
				$mail_user->setMailvar("exhibitor_name", $user->get("name"));
				$mail_user->setMailvar("company_name", $user->get("company"));
				$mail_user->setMailVar("position_name", $pos->get("name"));
				$mail_user->setMailVar("position_information", $pos->get("information"));
				$mail_user->setMailVar("url", BASE_URL . $fair->get("url"));
				$mail_user->setMailVar('arranger_message', $arranger_message);
				$mail_user->setMailVar('exhibitor_commodity', $exhibitor_commodity);
				$mail_user->setMailVar("exhibitor_category", $categories);
				$mail_user->setMailVar('booking_time', $time_now);
				$mail_user->send();
			}
		}
	}

	exit;

}


if (isset($_POST['fairRegistration'])) {
	
		if (userLevel() == 1) {

			$fair = new Fair();
			$fair->load($_SESSION['user_fair'], 'id');

			$user = new User();
			$user->load($_SESSION['user_id'], 'id');

			$organizer = new User();
			$organizer->load($fair->get('created_by'), 'id');

			if ($fair->wasLoaded() && $user->wasLoaded()) {
				if(isset($_POST['fairRegistration'])) {

					$categories = '';
					$options = '';
					$articles = '';
					$artamount = '';

					if (isset($_POST['categories']) && is_array($_POST['categories'])) {
						$categories = implode('|', $_POST['categories']);
					}

					if (isset($_POST['options']) && is_array($_POST['options'])) {
						$options = implode('|', $_POST['options']);
					}

					if (isset($_POST['articles']) && isset($_POST['artamount'])) {
						$articles = implode('|', $_POST['articles']);
						$artamount = implode('|', $_POST['artamount']);
					}

					$registration = new FairRegistration();
					$registration->set('user', $user->get('id'));
					$registration->set('fair', $fair->get('id'));
					$registration->set('categories', $categories);
					$registration->set('options', $options);
					$registration->set('articles', $articles);
					$registration->set('amount', $artamount);
					$registration->set('commodity', $_POST['commodity']);
					$registration->set('area', $_POST['area']);
					$registration->set('arranger_message', $_POST['message']);
					$registration->set('booking_time', time());
					$registration->save();

					$time_now = date('d-m-Y H:i');


					$categories = array();
					if (isset($_POST['categories']) && is_array($_POST['categories'])) {
						foreach ($_POST['categories'] as $category_id) {
							$ex_category = new ExhibitorCategory();
							$ex_category->load($category_id, 'id');
							$categories[] = $ex_category->get('name');
						}
					}
					$categories = implode(', ', $categories);


					$options = array();
					if (isset($_POST['options']) && is_array($_POST['options'])) {
						foreach ($_POST['options'] as $option_id) {
							$ex_option = new FairExtraOption();
							$ex_option->load($option_id, 'id');
							$options[] = $ex_option->get('text');
						}
					}
					$options = implode(', ', $options);

					$articles = array();
					if (isset($_POST['articles']) && is_array($_POST['articles'])) {
						$arts = $_POST['articles'];
						$amounts = $_POST['artamount'];

						foreach (array_combine($arts, $amounts) as $art => $amount) {
							$arts = new FairArticle();
							$arts->load($art, 'id');
							if ($arts->wasLoaded()) {
								$art_id[] = $arts->get('custom_id');
								$art_text[] = $arts->get('text');
								$art_amount[] = $amount;
								$art_price[] = $arts->get('price');
								$art_vat[] = $arts->get('vat');
							}								
						}
						$articles = array($art_id, $art_text, $art_price, $art_amount, $art_vat);
					}

				// Connect user to fair
				if (!userIsConnectedTo($fair->get('id'))) {
					$stmt = $registration->db->prepare("INSERT INTO fair_user_relation (`fair`, `user`, `connected_time`) VALUES (?, ?, ?)");
					$stmt->execute(array($fair->get('id'), $user->get('id'), time()));
				}
		//Check mail settings and send only if setting is set
				if ($fair->wasLoaded()) {
					$mailSettings = json_decode($fair->get("mail_settings"));
					if (is_array($mailSettings->registerForFair)) {
						if (in_array("0", $mailSettings->registerForFair)) {
							$mail_organizer = new Mail($organizer->get('email'), 'new_fair_registration_confirm', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
							$mail_organizer->setMailVar('url', BASE_URL . $fair->get('url'));
							$mail_organizer->setMailVar('event_name', $fair->get('name'));
							$mail_organizer->setMailvar("company_name", $user->get("company"));
							$mail_organizer->setMailVar('booking_time', $time_now);
							$mail_organizer->setMailVar('arranger_message', $_POST['message']);
							$mail_organizer->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_organizer->setMailVar('exhibitor_category', $categories);
							$mail_organizer->setMailVar('exhibitor_options', $options);
							$mail_organizer->setMailVar('exhibitor_name', $user->get('name'));
							$mail_organizer->setMailVar('edit_time', $time_now);
							$mail_organizer->setMailVar('area', $_POST['area']);
							$mail_organizer->send();
						}
					}

							$mail_user = new Mail($user->get('email'), 'new_fair_registration_receipt', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
							$mail_user->setMailVar('url', BASE_URL . $fair->get('url'));
							$mail_user->setMailVar('event_name', $fair->get('name'));
							$mail_user->setMailVar('event_email', $fair->get('contact_email'));
							$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
							$mail_user->setMailVar('event_website', $fair->get('website'));
							$mail_user->setMailvar("company_name", $user->get("company"));
							$mail_user->setMailVar('booking_time', $time_now);
							$mail_user->setMailVar('arranger_message', $_POST['message']);
							$mail_user->setMailVar('exhibitor_commodity', $_POST['commodity']);
							$mail_user->setMailVar('exhibitor_options', $options);
							$mail_user->setMailVar('exhibitor_category', $categories);
							$mail_user->setMailVar('exhibitor_name', $user->get('name'));
							$mail_user->setMailVar('edit_time', $time_now);
							$mail_user->setMailVar('area', $_POST['area']);
							$mail_user->send();
						
					
				}
			}
		}
	}

	exit;
}

if (isset($_POST['reservePosition'])) {

	if (userLevel() < 1)
		exit;

	$map = new FairMap();
	$map->load($_POST['map'], 'id');

	$pos = new FairMapPosition();
	$pos->load($_POST['reservePosition'], 'id');
	
	//Create new exhibitor if position status was available, else load existing exhibitor.
	if ($pos->get('status') === 0) {
		$exhibitor = new Exhibitor;
	} else {
		$exhibitor = new Exhibitor;
		$exhibitor->load($pos->get('id'), 'position');

	}
	
	if (isset($_POST['user']) && userLevel() > 1) {
		$exhibitor->set('user', $_POST['user']);
	} else {
		$exhibitor->set('user', $_SESSION['user_id']);
	}
		
	$exhibitor->set('position', $_POST['reservePosition']);
	$exhibitor->set('map', $_POST['map']);
	$exhibitor->set('fair', $map->get('fair'));
	$exhibitor->set('commodity', $_POST['commodity']);
	$exhibitor->set('arranger_message', $_POST['message']);
	$exhibitor->set('edit_time', 0);
	$exhibitor->set('clone', 0);
	$exhibitor->set('status', 1);
	$exId = $exhibitor->save();

	$categoryNames = array();

	if (isset($_POST['categories']) && is_array($_POST['categories'])) {
		$stmt = $pos->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
		foreach ($_POST['categories'] as $cat) {
			$stmt->execute(array($exId, $cat));
			$category = new ExhibitorCategory();
			$category->load($cat, "id");
			if ($category->wasLoaded()) {
				$categoryNames[] = $category->get("name");
			}
		}
	}


	$options = array();
	if (isset($_POST['options']) && is_array($_POST['options'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");
		foreach ($_POST['options'] as $opt) {
			$stmt->execute(array($exId, $opt));
			$ex_option = new FairExtraOption();
			$ex_option->load($opt, 'id');
			if ($ex_option->wasLoaded()) {
				$option_id[] = $ex_option->get('custom_id');
				$option_text[] = $ex_option->get('text');
				$option_price[] = $ex_option->get('price');
				$option_vat[] = $ex_option->get('vat');
			}
		}
		$options = array($option_id, $option_text, $option_price, $option_vat);
	}
	

	$articles = array();
	
	if (isset($_POST['articles']) && is_array($_POST['articles'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
		$arts = $_POST['articles'];
		$amounts = $_POST['artamount'];

		foreach (array_combine($arts, $amounts) as $art => $amount) {
			$stmt->execute(array($exId, $art, $amount));
			$arts = new FairArticle();
			$arts->load($art, 'id');
			if ($arts->wasLoaded()) {
				$art_id[] = $arts->get('custom_id');
				$art_text[] = $arts->get('text');
				$art_amount[] = $amount;
				$art_price[] = $arts->get('price');
				$art_vat[] = $arts->get('vat');
			}
		}
		$articles = array($art_id, $art_text, $art_price, $art_amount, $art_vat);
	}

	$status = 1;
	$pos->set('status', $status);
	$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));
	$pos->save();
	
	$stmt = $pos->db->prepare("UPDATE exhibitor_invoice SET status = 1 WHERE exhibitor = ? AND status = 2");
	$stmt->execute(array($exId));

	// Send mail
	$categories = implode('<br/> ', $categoryNames);
	$time_now = date('d-m-Y H:i');
	
	$pos = new FairMapPosition();
	$pos->load($exhibitor->get('position'), 'id');

	$fair = new Fair();
	$fair->load($exhibitor->get('fair'), 'id');
	
	$organizer = new User();
	$organizer->load($fair->get('created_by'), 'id');

	$fairInvoice = new FairInvoice();
	$fairInvoice->load($exhibitor->get('fair'), 'fair');

	$user = new User();
	$user->load($exhibitor->get('user'), 'id');
	$userId = $user->get('id');



/*********************************************************************************/
/*********************************************************************************/
/*****************     SENDER ADDRESS AND PAYMENT OPTIONS        *****************/
/*********************************************************************************/
/*********************************************************************************/


				$sender_billing_reference = $fairInvoice->get('reference');
				$sender_billing_company_name = $fairInvoice->get('company_name');
				$sender_billing_address = $fairInvoice->get('address');
				$sender_billing_zipcode = $fairInvoice->get('zipcode');
				$sender_billing_city = $fairInvoice->get('city');
				$sender_billing_country = $fairInvoice->get('country');
				$sender_billing_orgnr = $fairInvoice->get('orgnr');
				$sender_billing_phone = $fairInvoice->get('phone');
				$sender_billing_website = $fairInvoice->get('website');


				$rec_billing_company_name = $user->get('invoice_company');
				$rec_billing_address = $user->get('invoice_address');
				$rec_billing_zipcode = $user->get('invoice_zipcode');
				$rec_billing_city = $user->get('invoice_city');
				$rec_billing_country = $user->get('invoice_country');

				if ($rec_billing_country == 'Sweden')
					$rec_billing_country = 'Sverige';

				if ($rec_billing_country == 'Norway')
					$rec_billing_country = 'Norge';


				$printdate_label = $translator->{'Print date'};
				$required_at_payment_label = $translator->{'must be stated at payment'};
				$orgnr_label = $translator->{'Org.no'};
				$vat_label = $translator->{'TAX.no'};
				$description_label = $translator->{'Description'};
				$price_label = $translator->{'Price'};
				$amount_label = $translator->{'Amount'};
				$booked_space_label = $translator->{'Booked stand'};
				$options_label = $translator->{'Options'};
				$articles_label = $translator->{'Articles'};
				$tax_label = $translator->{'Tax'};
				$parttotal_label = $translator->{'Subtotal'};
				$net_label = $translator->{'Net'};
				$rounding_label = $translator->{'Rounding'};
				$to_pay_label = $translator->{'to pay:'};
				$address_contact_label = $translator->{'Address & Contact'};
				$organization_label = $translator->{'Organization'};
				$payment_info_label = $translator->{'Payment information'};
				$s_reference_label = $translator->{'Our reference'};
				$r_reference_label = $translator->{'Your reference'};
				$st_label = $translator->{'st'};


				$current_user = new User();
				$current_user->load($_SESSION['user_id'], 'id');



/*************************************************************/
/*************************************************************/
/*****************     PRICES AND AMOUNTS        *****************
/*************************************************************/
/*************************************************************/

				$fairId = $fair->get('id');
				$fairname = $fair->get('name');
				$fairurl = $fair->get('url');
				$totalPrice = 0;
				$VatPrice0 = 0;
				$VatPrice12 = 0;
				$VatPrice25 = 0;
				$excludeVatPrice0 = 0;
				$excludeVatPrice12 = 0;
				$excludeVatPrice25 = 0;
				$position_vat = 0;
				$currency = $fair->get('currency');
				$position_name = $pos->get('name');
				$position_price = $pos->get('price');
				$position_vat = $fairInvoice->get('pos_vat');
				$exhibitor_company_name = $user->get('company');
				$exhibitor_name = $user->get('name');



/*********************************************************************************************/
/*********************************************************************************************/
/*****************    					SET MAIL CONTENT 	  				******************/
/*********************************************************************************************/
/*********************************************************************************************/

$html = '<style>
* {
	box-sizing:border-box;
}
hr {
	width:690px;
	text-align:left;
}
tr .normal {
	width: 150px;
}
tr .normal2 {
	width:250px;
}
tr .normal3 {
	width:160px;
}

.short {
	width: 31px;
}
.id {
	width: 80px;
}
.name {
	width: 300px;
}
.price{
	width: 80px;
	text-align: right;
	padding-right: 12px;
}
.amount {
	width: 100px;
	text-align:center;
}
.moms {
	width:50px;
}
.center {
	text-align:center;
}
.left {
	text-align:left;
}
.right {
	text-align:right;
}
.vat {
	width: 80px;
	text-align: left;
}
.dark {
	background-color: #D4D4D4;
}
.totalprice {
	width: 445;
	text-align: right;
	font-size: 20px;
}
.totalprice2 {
	width: 400;
	text-align: right;
	font-size: 20px;
}
.pennys {
	width: 400;
	text-align: right;
	font-size: 16px;
}
</style>

<table>
	<thead>
	    <tr class="dark">
	    	<th class="id">ID</th>
	        <th class="name">'.$description_label.'</th>
	        <th class="price">'.$price_label.'</th>
	        <th class="amount">'.$amount_label.'</th>
	        <th class="moms right">'.$tax_label.'</th>
	        <th class="price">'.$parttotal_label.'</th>
	    </tr>
    </thead>
    <tbody>';

$html .= '<tr><td></td></tr><tr><td class="id"></td><td class="name"><b>'.$booked_space_label.'</b></td></tr>
<tr>
	<td class="id"></td>
    <td class="name">' . $position_name . '</td>
    <td class="price">' . $position_price . '</td>
	<td class="amount">1 '.$st_label.'</td>
	<td class="moms right">' . $position_vat . '%</td>
	<td class="price right">' . number_format($position_price, 2, ',', ' ') . '</td>
</tr>';

	if ($position_vat == 25) {
		$excludeVatPrice25 += $position_price;
	} else {
		$excludeVatPrice0 += $position_price;
	}

if (!empty($_POST['options']) && is_array($_POST['options'])) {
	$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$options_label.'</b></td></tr>';

	for ($row=0; $row<count($options[1]); $row++) {
	    $html .= '<tr>
	    	<td class="id">' . $options[0][$row] . '</td>
	        <td class="name">' . $options[1][$row] . '</td>
	        <td class="price">' . $options[2][$row] . '</td>
	        <td class="amount">1 '.$st_label.'</td>
	        <td class="moms right">' . $options[3][$row] . '%</td>
	        <td class="price right">' . str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')) . '</td>
	        </tr>';
    }
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	
	$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$articles_label.'</b></td></tr>';
	for ($row=0; $row<count($articles[1]); $row++) {
	    $html .= '<tr>
	    	<td class="id">' . $articles[0][$row] . '</td>
	        <td class="name">' . $articles[1][$row] . '</td>
	        <td class="price">' . str_replace('.', ',', $articles[2][$row]) . '</td>
	        <td class="amount center">' . $articles[3][$row] . ' '.$st_label.'</td>
	        <td class="moms right">' . $articles[4][$row] . '%</td>
	        <td class="price right">' . str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')) . '</td>
	        </tr>';
	        $articles[2][$row] = str_replace(',', '.', $articles[2][$row]);
    }
}


if (!empty($_POST['options']) && is_array($_POST['options'])) {
	for ($row=0; $row<count($options[1]); $row++) {

		if ($options[3][$row] == 25) {
			$excludeVatPrice25 += $options[2][$row];
		}
		if ($options[3][$row] == 12) {
			$excludeVatPrice12 += $options[2][$row];
		}		
		if ($options[3][$row] == 0) {
			$excludeVatPrice0 += $options[2][$row];
		}		
	}
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	for ($row=0; $row<count($articles[1]); $row++) {

		if ($articles[4][$row] == 25) {
			$excludeVatPrice25 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}		
		if ($articles[4][$row] == 12) {
			$excludeVatPrice12 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 0) {
			$excludeVatPrice0 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}		
	}
}

$VatPrice0 = $excludeVatPrice0;
$VatPrice12 = $excludeVatPrice12*0.12;
$VatPrice25 = $excludeVatPrice25*0.25;
$totalPrice += $excludeVatPrice12 + $excludeVatPrice25 + $VatPrice12 + $VatPrice25 + $VatPrice0;

$totalPriceRounded = round($totalPrice);
$pennys = ($totalPriceRounded - $totalPrice);

$html .= '
</tbody></table>
<hr>
<table>
	<thead>
	    <tr>
	        <th class="vat"></th>
	        <th class="vat"></th>
	        <th class="vat"></th>
	        <th class="totalprice"></th>
	    </tr>
    </thead>
    <tbody>
	<tr>
		<td class="vat">'.$net_label.'</td>
		<td class="vat">'.$tax_label.' %</td>
		<td class="vat">'.$tax_label.':</td>
		<td class="totalprice"></td>
	</tr>';

if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
	$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
	$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');
}
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice12) . '</td>
		<td class="vat">12,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice12) . '</td>
		<td class="pennys">'.$rounding_label.':&nbsp;&nbsp;'
		. str_replace('.', ',', number_format($pennys, 2, ',', ' ')) . 
		'</td>
		</tr>';

if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
	$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
	$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
}
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice25) . '</td>
		<td class="vat">25,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice25) . '</td>
		<td class="totalprice2">'.$currency.' '.$to_pay_label.'&nbsp;&nbsp;'
		. str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')) . 
		'</td>
		</tbody></table>';



$html .= '</tbody></table>';


	$arranger_message = $_POST['message'];
	if ($arranger_message == '') {
		$arranger_message = $translator->{'No message was given.'};
	}
	$exhibitor_commodity = $_POST['commodity'];
	if ($exhibitor_commodity == '') {
		$exhibitor_commodity = $translator->{'No commodity was entered.'};
	}

	if ($options == '') {
		$options = $translator->{'No options selected.'};
	}
	if ($articles == '') {
		$articles = $translator->{'No articles selected.'};
	}

	if ($fair->wasLoaded()) {
		$mailSettings = json_decode($fair->get("mail_settings"));
		if (is_array($mailSettings->reservationCreated)) {
			$status = posStatusToText($status);

			if (in_array("0", $mailSettings->reservationCreated)) {
				$mail_organizer = new Mail($organizer->get('email'), 'reservation_created_confirm', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
				$mail_organizer->setMailVar('booking_table', $html);
				$mail_organizer->setMailvar('status', $status);
				$mail_organizer->setMailvar("event_name", $fair->get("name"));
				$mail_organizer->setMailvar("exhibitor_name", $user->get("name"));
				$mail_organizer->setMailvar("company_name", $user->get("company"));
				$mail_organizer->setMailVar("position_name", $pos->get("name"));
				$mail_organizer->setMailVar("position_information", $pos->get("information"));
				$mail_organizer->setMailVar("position_area", $pos->get("area"));
				$mail_organizer->setMailVar('date_expires', $_POST['expires']);
				$mail_organizer->setMailVar("url", BASE_URL . $fair->get("url"));
				$mail_organizer->setMailVar('arranger_message', $arranger_message);
				$mail_organizer->setMailVar('exhibitor_commodity', $exhibitor_commodity);
				$mail_organizer->setMailVar("exhibitor_category", $categories);
				$mail_organizer->setMailVar('edit_time', $time_now);
				$mail_organizer->send();
			}
			if (in_array("1", $mailSettings->reservationCreated)) {
				$mail_user = new Mail($user->get('email'), 'reservation_created_receipt', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
				$mail_user->setMailVar('booking_table', $html);
				$mail_user->setMailvar('status', $status);
				$mail_user->setMailvar("event_name", $fair->get("name"));
				$mail_user->setMailVar('event_email', $fair->get('contact_email'));
				$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
				$mail_user->setMailVar('event_website', $fair->get('website'));
				$mail_user->setMailvar("exhibitor_name", $user->get("name"));
				$mail_user->setMailvar("company_name", $user->get("company"));
				$mail_user->setMailVar("position_name", $pos->get("name"));
				$mail_user->setMailVar("position_information", $pos->get("information"));
				$mail_user->setMailVar("position_area", $pos->get("area"));
				$mail_user->setMailVar('date_expires', $_POST['expires']);
				$mail_user->setMailVar("url", BASE_URL . $fair->get("url"));
				$mail_user->setMailVar('arranger_message', $arranger_message);
				$mail_user->setMailVar('exhibitor_commodity', $exhibitor_commodity);
				$mail_user->setMailVar("exhibitor_category", $categories);
				$mail_user->setMailVar('exhibitor_options', $options);
				$mail_user->setMailVar('edit_time', $time_now);
				$mail_user->send();
			}
		}
	}

	exit;

}


if (isset($_POST['editBooking'])) {
	
	if (userLevel() < 1)
		exit;
	
	$map = new FairMap();
	$map->load($_POST['map'], 'id');

	$pos = new FairMapPosition();
	$pos->load($_POST['editBooking'], 'id');

	$fair = new Fair();
	$fair->load($map->get('fair'), 'id');

	$exhibitor = new Exhibitor;
	$exhibitor->load($_POST['exhibitor_id'], 'id');
	if (!$exhibitor->wasLoaded()) {
		die('exhibitor not found');
	}

	$organizer = new User();
	$organizer->load($fair->get('created_by'), 'id');

	if (isset($_POST['user']) && userLevel() > 1)
		$exhibitor->set('user', $_POST['user']);
	else
		$exhibitor->set('user', $_SESSION['user_id']);

	$exhibitor->set('commodity', $_POST['commodity']);
	$exhibitor->set('arranger_message', $_POST['message']);
	$exhibitor->set('clone', 0);
	
	$exId = $exhibitor->save();
	// Remove old categories for this booking
	$map->db->query("DELETE FROM exhibitor_category_rel WHERE exhibitor = '".intval($_POST['exhibitor_id'])."'");
	
	// Set new categories for this booking
	$categoryNames = array();

	if (isset($_POST['categories']) && is_array($_POST['categories'])) {
		$stmt = $pos->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
		foreach ($_POST['categories'] as $cat) {
			$stmt->execute(array($exId, $cat));
			$category = new ExhibitorCategory();
			$category->load($cat, "id");
			if ($category->wasLoaded()) {
				$categoryNames[] = $category->get("name");
			}
		}
	}

	// Remove old options for this booking	
	$map->db->query("DELETE FROM exhibitor_option_rel WHERE exhibitor = '".intval($_POST['exhibitor_id'])."'");

	// Set new options for this booking
	$options = array();
	if (isset($_POST['options']) && is_array($_POST['options'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");
		foreach ($_POST['options'] as $opt) {
			$stmt->execute(array($exId, $opt));
			$ex_option = new FairExtraOption();
			$ex_option->load($opt, 'id');
			if ($ex_option->wasLoaded()) {
				$option_id[] = $ex_option->get('custom_id');
				$option_text[] = $ex_option->get('text');
				$option_price[] = $ex_option->get('price');
				$option_vat[] = $ex_option->get('vat');
			}
		}
		$options = array($option_id, $option_text, $option_price, $option_vat);
	}
	
	// Remove old articles for this booking	
	$map->db->query("DELETE FROM exhibitor_article_rel WHERE exhibitor = '".intval($_POST['exhibitor_id'])."'");	
	
	// Set new articles for this booking
	$articles = array();
	
	if (isset($_POST['articles']) && is_array($_POST['articles'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
		$arts = $_POST['articles'];
		$amounts = $_POST['artamount'];

		foreach (array_combine($arts, $amounts) as $art => $amount) {
			$stmt->execute(array($exId, $art, $amount));
			$arts = new FairArticle();
			$arts->load($art, 'id');
			if ($arts->wasLoaded()) {
				$art_id[] = $arts->get('custom_id');
				$art_text[] = $arts->get('text');
				$art_amount[] = $amount;
				$art_price[] = $arts->get('price');
				$art_vat[] = $arts->get('vat');
			}
		}
		$articles = array($art_id, $art_text, $art_price, $art_amount, $art_vat);
	}

	// If this is a reservation (status is 1), then also set the expiry date
	if (isset($_POST['expires'])) {
		$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));
		$pos->save();
		$mail_type = 'reservation';

	} else {
		$mail_type = 'booking';
	}


	$status = posStatusToText($pos->get('status'));
	$time_now = date('d-m-Y H:i');

	$mailSetting = $mail_type . "Edited";

	$categories = implode('<br/> ', $categoryNames);

	$fairInvoice = new FairInvoice();
	$fairInvoice->load($exhibitor->get('fair'), 'fair');

	$user = new User();
	$user->load($exhibitor->get('user'), 'id');
	$userId = $user->get('id');


/*********************************************************************************/
/*********************************************************************************/
/*****************     SENDER ADDRESS AND PAYMENT OPTIONS        *****************/
/*********************************************************************************/
/*********************************************************************************/


				$sender_billing_reference = $fairInvoice->get('reference');
				$sender_billing_company_name = $fairInvoice->get('company_name');
				$sender_billing_address = $fairInvoice->get('address');
				$sender_billing_zipcode = $fairInvoice->get('zipcode');
				$sender_billing_city = $fairInvoice->get('city');
				$sender_billing_country = $fairInvoice->get('country');
				$sender_billing_orgnr = $fairInvoice->get('orgnr');
				$sender_billing_phone = $fairInvoice->get('phone');
				$sender_billing_website = $fairInvoice->get('website');


				$rec_billing_company_name = $user->get('invoice_company');
				$rec_billing_address = $user->get('invoice_address');
				$rec_billing_zipcode = $user->get('invoice_zipcode');
				$rec_billing_city = $user->get('invoice_city');
				$rec_billing_country = $user->get('invoice_country');

				if ($rec_billing_country == 'Sweden')
					$rec_billing_country = 'Sverige';

				if ($rec_billing_country == 'Norway')
					$rec_billing_country = 'Norge';


				$printdate_label = $translator->{'Print date'};
				$required_at_payment_label = $translator->{'must be stated at payment'};
				$orgnr_label = $translator->{'Org.no'};
				$vat_label = $translator->{'TAX.no'};
				$description_label = $translator->{'Description'};
				$price_label = $translator->{'Price'};
				$amount_label = $translator->{'Amount'};
				$booked_space_label = $translator->{'Booked stand'};
				$options_label = $translator->{'Options'};
				$articles_label = $translator->{'Articles'};
				$tax_label = $translator->{'Tax'};
				$parttotal_label = $translator->{'Subtotal'};
				$net_label = $translator->{'Net'};
				$rounding_label = $translator->{'Rounding'};
				$to_pay_label = $translator->{'to pay:'};
				$address_contact_label = $translator->{'Address & Contact'};
				$organization_label = $translator->{'Organization'};
				$payment_info_label = $translator->{'Payment information'};
				$s_reference_label = $translator->{'Our reference'};
				$r_reference_label = $translator->{'Your reference'};
				$st_label = $translator->{'st'};


				$current_user = new User();
				$current_user->load($_SESSION['user_id'], 'id');



/*************************************************************/
/*************************************************************/
/*****************     PRICES AND AMOUNTS        *****************
/*************************************************************/
/*************************************************************/

				$fairId = $fair->get('id');
				$fairname = $fair->get('name');
				$fairurl = $fair->get('url');
				$totalPrice = 0;
				$VatPrice0 = 0;
				$VatPrice12 = 0;
				$VatPrice25 = 0;
				$excludeVatPrice0 = 0;
				$excludeVatPrice12 = 0;
				$excludeVatPrice25 = 0;
				$position_vat = 0;
				$currency = $fair->get('currency');
				$position_name = $pos->get('name');
				$position_price = $pos->get('price');
				$position_vat = $fairInvoice->get('pos_vat');
				$exhibitor_company_name = $user->get('company');
				$exhibitor_name = $user->get('name');



/*********************************************************************************************/
/*********************************************************************************************/
/*****************    					SET MAIL CONTENT 	  				******************/
/*********************************************************************************************/
/*********************************************************************************************/

$html = '<style>
* {
	box-sizing:border-box;
}
hr {
	width:690px;
	text-align:left;
}
tr .normal {
	width: 150px;
}
tr .normal2 {
	width:250px;
}
tr .normal3 {
	width:160px;
}

.short {
	width: 31px;
}
.id {
	width: 80px;
}
.name {
	width: 300px;
}
.price{
	width: 80px;
	text-align: right;
	padding-right: 12px;
}
.amount {
	width: 100px;
	text-align:center;
}
.moms {
	width:50px;
}
.center {
	text-align:center;
}
.left {
	text-align:left;
}
.right {
	text-align:right;
}
.vat {
	width: 80px;
	text-align: left;
}
.dark {
	background-color: #D4D4D4;
}
.totalprice {
	width: 445;
	text-align: right;
	font-size: 20px;
}
.totalprice2 {
	width: 400;
	text-align: right;
	font-size: 20px;
}
.pennys {
	width: 400;
	text-align: right;
	font-size: 16px;
}
</style>

<table>
	<thead>
	    <tr class="dark">
	    	<th class="id">ID</th>
	        <th class="name">'.$description_label.'</th>
	        <th class="price">'.$price_label.'</th>
	        <th class="amount">'.$amount_label.'</th>
	        <th class="moms right">'.$tax_label.'</th>
	        <th class="price">'.$parttotal_label.'</th>
	    </tr>
    </thead>
    <tbody>';

$html .= '<tr><td></td></tr><tr><td class="id"></td><td class="name"><b>'.$booked_space_label.'</b></td></tr>
<tr>
	<td class="id"></td>
    <td class="name">' . $position_name . '</td>
    <td class="price">' . $position_price . '</td>
	<td class="amount">1 '.$st_label.'</td>
	<td class="moms right">' . $position_vat . '%</td>
	<td class="price right">' . number_format($position_price, 2, ',', ' ') . '</td>
</tr>';

	if ($position_vat == 25) {
		$excludeVatPrice25 += $position_price;
	} else {
		$excludeVatPrice0 += $position_price;
	}

if (!empty($_POST['options']) && is_array($_POST['options'])) {
	$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$options_label.'</b></td></tr>';

	for ($row=0; $row<count($options[1]); $row++) {
	    $html .= '<tr>
	    	<td class="id">' . $options[0][$row] . '</td>
	        <td class="name">' . $options[1][$row] . '</td>
	        <td class="price">' . $options[2][$row] . '</td>
	        <td class="amount">1 '.$st_label.'</td>
	        <td class="moms right">' . $options[3][$row] . '%</td>
	        <td class="price right">' . str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')) . '</td>
	        </tr>';
    }
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	
	$html .= '<tr><td></td></tr><tr><td class="id"></td><td><b>'.$articles_label.'</b></td></tr>';
	for ($row=0; $row<count($articles[1]); $row++) {
	    $html .= '<tr>
	    	<td class="id">' . $articles[0][$row] . '</td>
	        <td class="name">' . $articles[1][$row] . '</td>
	        <td class="price">' . str_replace('.', ',', $articles[2][$row]) . '</td>
	        <td class="amount center">' . $articles[3][$row] . ' '.$st_label.'</td>
	        <td class="moms right">' . $articles[4][$row] . '%</td>
	        <td class="price right">' . str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')) . '</td>
	        </tr>';
	        $articles[2][$row] = str_replace(',', '.', $articles[2][$row]);
    }
}


if (!empty($_POST['options']) && is_array($_POST['options'])) {
	for ($row=0; $row<count($options[1]); $row++) {

		if ($options[3][$row] == 25) {
			$excludeVatPrice25 += $options[2][$row];
		}
		if ($options[3][$row] == 12) {
			$excludeVatPrice12 += $options[2][$row];
		}		
		if ($options[3][$row] == 0) {
			$excludeVatPrice0 += $options[2][$row];
		}		
	}
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	for ($row=0; $row<count($articles[1]); $row++) {

		if ($articles[4][$row] == 25) {
			$excludeVatPrice25 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}		
		if ($articles[4][$row] == 12) {
			$excludeVatPrice12 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}
		if ($articles[4][$row] == 0) {
			$excludeVatPrice0 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
		}		
	}
}

$VatPrice0 = $excludeVatPrice0;
$VatPrice12 = $excludeVatPrice12*0.12;
$VatPrice25 = $excludeVatPrice25*0.25;
$totalPrice += $excludeVatPrice12 + $excludeVatPrice25 + $VatPrice12 + $VatPrice25 + $VatPrice0;

$totalPriceRounded = round($totalPrice);
$pennys = ($totalPriceRounded - $totalPrice);

$html .= '
</tbody></table>
<hr>
<table>
	<thead>
	    <tr>
	        <th class="vat"></th>
	        <th class="vat"></th>
	        <th class="vat"></th>
	        <th class="totalprice"></th>
	    </tr>
    </thead>
    <tbody>
	<tr>
		<td class="vat">'.$net_label.'</td>
		<td class="vat">'.$tax_label.' %</td>
		<td class="vat">'.$tax_label.':</td>
		<td class="totalprice"></td>
	</tr>';

if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
	$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
	$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');
}
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice12) . '</td>
		<td class="vat">12,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice12) . '</td>
		<td class="pennys">'.$rounding_label.':&nbsp;&nbsp;'
		. str_replace('.', ',', number_format($pennys, 2, ',', ' ')) . 
		'</td>
		</tr>';

if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
	$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
	$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
}
$html .= '<tr>
		<td class="vat">' . str_replace('.', ',', $excludeVatPrice25) . '</td>
		<td class="vat">25,00</td>
		<td class="vat">' . str_replace('.', ',', $VatPrice25) . '</td>
		<td class="totalprice2">'.$currency.' '.$to_pay_label.'&nbsp;&nbsp;'
		. str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')) . 
		'</td>
		</tbody></table>';



$html .= '</tbody></table>';

	$arranger_message = $_POST['arranger_message'];
	if ($arranger_message == '') {
		$arranger_message = $translator->{'No message was given.'};
	}
	$exhibitor_commodity = $_POST['commodity'];
	if ($exhibitor_commodity == '') {
		$exhibitor_commodity = $translator->{'No commodity was entered.'};
	}


	//Check mail settings and send only if setting is set
	if ($fair->wasLoaded()) {
		$mailSettings = json_decode($fair->get("mail_settings"));
		if (is_array($mailSettings->$mailSetting)) {

			if (in_array("0", $mailSettings->$mailSetting)) {
				$mail_organizer = new Mail($organizer->get('email'), $mail_type . '_edited_confirm', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
				$mail_organizer->setMailVar('booking_table', $html);
				$mail_organizer->setMailvar('status', $status);
				$mail_organizer->setMailvar("event_name", $fair->get("name"));
				$mail_organizer->setMailvar("exhibitor_name", $exhibitor->get("name"));
				$mail_organizer->setMailvar("company_name", $exhibitor->get("company"));
				$mail_organizer->setMailVar("position_name", $pos->get("name"));
				$mail_organizer->setMailVar("position_information", $pos->get("information"));
				$mail_organizer->setMailVar("booking_time", date('d-m-Y H:i:s', intval($exhibitor->get("booking_time"))));
				$mail_organizer->setMailVar("url", BASE_URL . $fair->get("url"));
				$mail_organizer->setMailVar('arranger_message', $arranger_message);
				$mail_organizer->setMailVar('exhibitor_commodity', $exhibitor_commodity);
				$mail_organizer->setMailVar("exhibitor_category", $categories);
				$mail_organizer->setMailVar('edit_time', $time_now);

				if ($mail_type == 'reservation') {
					$mail_organizer->setMailVar('date_expires', $_POST['expires']);
				}

				$mail_organizer->send();
			}

			if (in_array("1", $mailSettings->$mailSetting)) {
				$mail_user = new Mail($exhibitor->get('email'), $mail_type . '_edited_receipt', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
				$mail_user->setMailVar('booking_table', $html);
				$mail_user->setMailvar('status', $status);
				$mail_user->setMailvar("event_name", $fair->get("name"));
				$mail_user->setMailVar('event_email', $fair->get('contact_email'));
				$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
				$mail_user->setMailVar('event_website', $fair->get('website'));
				$mail_user->setMailvar("exhibitor_name", $exhibitor->get("name"));
				$mail_user->setMailvar("company_name", $exhibitor->get("company"));
				$mail_user->setMailVar("position_name", $pos->get("name"));
				$mail_user->setMailVar("position_information", $pos->get("information"));
				$mail_user->setMailVar("booking_time", date('d-m-Y H:i:s', intval($exhibitor->get("booking_time"))));
				$mail_user->setMailVar("url", BASE_URL . $fair->get("url"));
				$mail_user->setMailVar('arranger_message', $arranger_message);
				$mail_user->setMailVar('exhibitor_commodity', $exhibitor_commodity);
				$mail_user->setMailVar("exhibitor_category", $categories);
				$mail_user->setMailVar('exhibitor_options', $options);
				$mail_user->setMailVar('edit_time', $time_now);

				if ($mail_type == 'reservation') {
					$mail_user->setMailVar('date_expires', $_POST['expires']);
				}

				$mail_user->send();
			}
		}
	}

	exit;
	
}

if (isset($_POST['preliminary'])) {
	
		if (userLevel() == 1) {

			$position = new FairMapPosition();
			$position->load($_POST['preliminary'], 'id');	

			$map = new FairMap();
			$map->load($position->get('map'), 'id');

			$fair = new Fair();
			$fair->load($map->get('fair'), 'id');

			$user = new User();
			$user->load($_SESSION['user_id'], 'id');

			$organizer = new User();
			$organizer->load($fair->get('created_by'), 'id');

			if ($fair->wasLoaded() && $user->wasLoaded()) {
				if(isset($_POST['preliminary'])) {

					$categories = '';
					$options = '';
					$articles = '';
					$artamount = '';
					if (isset($_POST['categories']) && is_array($_POST['categories'])) {
						$categories = implode('|', $_POST['categories']);
					}

					if (isset($_POST['options']) && is_array($_POST['options'])) {
						$options = implode('|', $_POST['options']);
					}

					if (isset($_POST['articles']) && isset($_POST['artamount'])) {
						$articles = implode('|', $_POST['articles']);
						$artamount = implode('|', $_POST['artamount']);
					}

					$pb = new PreliminaryBooking();
					$pb->set('user', $user->get('id'));
					$pb->set('fair', $fair->get('id'));
					$pb->set('position', $position->get('id'));
					$pb->set('categories', $categories);
					$pb->set('options', $options);
					$pb->set('articles', $articles);
					$pb->set('amount', $artamount);
					$pb->set('commodity', $_POST['commodity']);
					$pb->set('arranger_message', $_POST['message']);
					$pb->set('booking_time', time());
					$pb->save();

					$time_now = date('d-m-Y H:i');


					$categories = array();
					if (isset($_POST['categories']) && is_array($_POST['categories'])) {
						foreach ($_POST['categories'] as $category_id) {
							$ex_category = new ExhibitorCategory();
							$ex_category->load($category_id, 'id');
							$categories[] = $ex_category->get('name');
						}
					}
					$categories = implode('<br> ', $categories);


					$options = array();
					if (isset($_POST['options']) && is_array($_POST['options'])) {
						foreach ($_POST['options'] as $option_id) {
							$ex_option = new FairExtraOption();
							$ex_option->load($option_id, 'id');
							$options[] = $ex_option->get('text');
						}
					}
					$options = implode('<br> ', $options);


					$articles = array();
					if (isset($_POST['articles']) && is_array($_POST['articles'])) {
						$arts = $_POST['articles'];
						$amounts = $_POST['artamount'];

						foreach ($arts as $art) {
							$pb_article = new FairArticle();
							$pb_article->load($art, 'id');
							$articles[] = $pb_article->get('text');
						}
					}
					$articles = implode('<br> ', $articles);

					$status = posStatusToText(3);

		if ($options == '') {
			$options = $translator->{'No options selected.'};
		}
		if ($articles == '') {
			$articles = $translator->{'No articles selected.'};
		}
		$arranger_message = $_POST['message'];
		if ($arranger_message == '') {
			$arranger_message = $translator->{'No message was given.'};
		}
		$exhibitor_commodity = $_POST['commodity'];
		if ($exhibitor_commodity == '') {
			$exhibitor_commodity = $translator->{'No commodity was entered.'};
		}
		//Check mail settings and send only if setting is set
				if ($fair->wasLoaded()) {
					$mailSettings = json_decode($fair->get("mail_settings"));
					if (is_array($mailSettings->recievePreliminaryBooking)) {
						if (in_array("0", $mailSettings->recievePreliminaryBooking)) {
							$mail_organizer = new Mail($organizer->get('email'), 'preliminary_created_confirm', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
							$mail_organizer->setMailvar('status', $status);
							$mail_organizer->setMailVar('url', BASE_URL . $fair->get('url'));
							$mail_organizer->setMailVar('event_name', $fair->get('name'));
							$mail_organizer->setMailvar("company_name", $user->get("company"));
							$mail_organizer->setMailVar('position_name', $position->get('name'));
							$mail_organizer->setMailVar('position_information', $position->get('information'));
							$mail_organizer->setMailVar('position_area', $position->get('area'));
							$mail_organizer->setMailVar('booking_time', $time_now);
							$mail_organizer->setMailVar('arranger_message', $arranger_message);
							$mail_organizer->setMailVar('exhibitor_commodity', $exhibitor_commodity);
							$mail_organizer->setMailVar('exhibitor_category', $categories);
							$mail_organizer->setMailVar('exhibitor_options', $options);
							$mail_organizer->setMailVar('exhibitor_articles', $articles);
							$mail_organizer->setMailVar('exhibitor_name', $user->get('name'));
							$mail_organizer->setMailVar('edit_time', $time_now);
							$mail_organizer->send();
						}
					}

						
							$mail_user = new Mail($user->get('email'), 'preliminary_created_receipt', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
							$mail_user->setMailvar('status', $status);
							$mail_user->setMailVar('url', BASE_URL . $fair->get('url'));
							$mail_user->setMailVar('event_name', $fair->get('name'));
							$mail_user->setMailVar('event_email', $fair->get('contact_email'));
							$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
							$mail_user->setMailVar('event_website', $fair->get('website'));
							$mail_user->setMailvar("company_name", $user->get("company"));
							$mail_user->setMailVar('position_name', $position->get('name'));
							$mail_user->setMailVar('position_information', $position->get('information'));
							$mail_user->setMailVar('position_area', $position->get('area'));
							$mail_user->setMailVar('booking_time', $time_now);
							$mail_user->setMailVar('arranger_message', $arranger_message);
							$mail_user->setMailVar('exhibitor_commodity', $exhibitor_commodity);
							$mail_user->setMailVar('exhibitor_category', $categories);
							$mail_user->setMailVar('exhibitor_options', $options);
							$mail_user->setMailVar('exhibitor_articles', $articles);
							$mail_user->setMailVar('exhibitor_name', $user->get('name'));
							$mail_user->setMailVar('edit_time', $time_now);
							$mail_user->send();
						
					
				}
			}
		}
	}

	exit;
}

if (isset($_POST['cancelPreliminary'])) {

	if (userLevel() == 1) {

		$pb = new PreliminaryBooking;
		$stmt_history = $pb->db->prepare("INSERT INTO preliminary_booking_history SELECT * FROM preliminary_booking WHERE user = ? AND position = ?");
		$stmt_history->execute(array($_SESSION['user_id'], $_POST['cancelPreliminary']));
		$stmt = $pb->db->prepare("DELETE FROM preliminary_booking WHERE user = ? AND position = ?");
		$stmt->execute(array($_SESSION['user_id'], $_POST['cancelPreliminary']));

	}

	exit;

}

if (isset($_POST['cancelBooking'])) {

	if (userLevel() > 1) {

		$pos = new FairMapPosition();
		$pos->load($_POST['cancelBooking'], 'id');

		$previous_status = $pos->get('status');
		$pos->set('status', 0);
		$pos->save();

		$exhibitor = new Exhibitor();
		$exhibitor->load($_POST['cancelBooking'], 'position');
		$exhibitor->delete();

		//Get mail settings for fair
		$fairMap = new FairMap();
		$fairMap->load($pos->get("map"), "id");

		$fair = new Fair();
		$fair->load($fairMap->get('fair'), 'id');

		$organizer = new User();
		$organizer->load($fair->get('created_by'), 'id');
		
		$me = new User();
		$me->load($_SESSION['user_id'], 'id');

		$time_now = date('d-m-Y H:i');

		$comment = $_POST['comment'];
		if ($comment == '') {
			$comment = uh($translator->{'No message was given.'});
		}

		if ($fair->wasLoaded()) {
			//Check mail settings and send only if setting is set
			$mailSettings = json_decode($fair->get("mail_settings"));
			if (is_array($mailSettings->bookingCancelled)) {
				$previous_status = posStatusToText($previous_status);
				

				if (in_array("0", $mailSettings->bookingCancelled)) {
					$mail_organizer = new Mail($organizer->get('email'), 'booking_cancelled_confirm', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
					$mail_organizer->setMailVar('url', BASE_URL . $fair->get('url'));
					$mail_organizer->setMailVar('previous_status', $previous_status);
					$mail_organizer->setMailvar("event_name", $fair->get("name"));
					$mail_organizer->setMailVar('position_name', $pos->get('name'));
					$mail_organizer->setMailVar('exhibitor_name', $exhibitor->get('name'));
					$mail_organizer->setMailvar("company_name", $exhibitor->get("company"));
					$mail_organizer->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
					$mail_organizer->setMailVar('cancelled_name', $me->get('name'));
					$mail_organizer->setMailVar('edit_time', date('d-m-Y H:i'));
					$mail_organizer->setMailVar('comment', $comment);
					$mail_organizer->send();
				}
				if (in_array("1", $mailSettings->bookingCancelled)) {
					$mail_user = new Mail($exhibitor->get('email'), 'booking_cancelled_receipt', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
					$mail_user->setMailVar('url', BASE_URL . $fair->get('url'));
					$mail_user->setMailVar('previous_status', $previous_status);
					$mail_user->setMailvar("event_name", $fair->get("name"));
					$mail_user->setMailVar('event_email', $fair->get('contact_email'));
					$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
					$mail_user->setMailVar('event_website', $fair->get('website'));
					$mail_user->setMailVar('position_name', $pos->get('name'));
					$mail_user->setMailvar("company_name", $exhibitor->get("company"));
					$mail_user->setMailVar('exhibitor_name', $exhibitor->get('name'));
					$mail_user->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
					$mail_user->setMailVar('cancelled_name', $me->get('name'));
					$mail_user->setMailVar('edit_time', date('d-m-Y H:i'));
					$mail_user->setMailVar('comment', $comment);
					$mail_user->send();
				}
				if ($me->get('email') != $organizer->get('email')) {
					if (in_array("2", $mailSettings->bookingCancelled)) {
						$mail_currentuser = new Mail($me->get('email'), 'booking_cancelled_confirm', $fair->get('url') . EMAIL_FROM_DOMAIN, $fair->get('name'));
						$mail_currentuser->setMailVar('url', BASE_URL . $fair->get('url'));
						$mail_currentuser->setMailVar('previous_status', $previous_status);
						$mail_currentuser->setMailvar("event_name", $fair->get("name"));
						$mail_currentuser->setMailVar('position_name', $pos->get('name'));
						$mail_currentuser->setMailVar('exhibitor_name', $exhibitor->get('name'));
						$mail_currentuser->setMailvar("company_name", $exhibitor->get("company"));
						$mail_currentuser->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
						$mail_currentuser->setMailVar('cancelled_name', $me->get('name'));
						$mail_currentuser->setMailVar('edit_time', date('d-m-Y H:i'));
						$mail_currentuser->setMailVar('comment', $comment);
						$mail_currentuser->send();
					}
				}
			}
		}
	}

	exit;

}

if (isset($_POST['savePosition'])) {

	if (userLevel() < 2)
		exit;

	$pos = new FairMapPosition;
	if ((int)$_POST['savePosition'] > 0) {
		$pos->load((int)$_POST['savePosition'], 'id');
	} else {
		$pos->set('map', $_POST['map']);
		$pos->set('x', $_POST['x']);
		$pos->set('y', $_POST['y']);
		$pos->set('status', 0);
	}

	$pos->set('name', $_POST['name']);
	$pos->set('area', $_POST['area']);
	$pos->set('price', $_POST['price']);
	$pos->set('information', $_POST['information']);
	$pos->save();

	exit;

}

if (isset($_POST['movePosition'])) {

	$pos = new FairMapPosition;
	$pos->load((int)$_POST['movePosition'], 'id');
	$pos->set('x', $_POST['x']);
	$pos->set('y', $_POST['y']);
	$pos->save();

}

if (isset($_POST['getUserCommodity'])) {
	if (userLevel() < 1) {
		exit;
	}

	$user = new User;
	$user->load((int)$_POST['userId'], 'id');
	$answer = array('commodity' => '');
	if ($user->wasLoaded()) {
		$answer['commodity'] = $user->get('commodity');
	}
	echo json_encode($answer);
	exit;
}

if (isset($_POST['emailExists'])) {
	$user = new User;
	$user->load($_POST['email'], 'email');
	echo json_encode(array('emailExists' => $user->wasLoaded()));	
	exit;
}

if (isset($_POST["aliasExists"])) {
	$user = new User();
	$user->load($_POST["alias"], "alias");
	echo json_encode(array("aliasExists" => $user->wasLoaded()));
}

if (isset($_POST['connectToFair'])) {
	$response = array();
	if (isset($_SESSION['user_id']) && !userIsConnectedTo($_POST['fairId'])) {
		$sql = "INSERT INTO `fair_user_relation`(`fair`, `user`, `connected_time`) VALUES (?,?,?)";
		$stmt = $globalDB->prepare($sql);
		$stmt->execute(array($_POST['fairId'], $_SESSION['user_id'], time()));
		$fair = new Fair;
		$fair->load($_POST['fairId'], 'id');
		$response['message'] = $translator->{'Connected to fair'}.' '.$fair->get('name');
		$response['success'] = true;
	} else {
		$response['message'] = $translator->{'Unable to connect to fair.'};
		$response['success'] = false;
	}
	echo json_encode($response);
}

if (isset($_GET['prel_bookings_list'], $_GET['position'])) {

	$position = new FairMapPosition();
	$position->load($_GET['position'], 'id');

	if ($position->wasLoaded()) {

		$fair_map = new FairMap();
		$fair_map->load($position->get('map'), 'id');

		if ($fair_map->wasLoaded() && userCanAdminFair($fair_map->get('fair'), $fair_map->get('id'))) {

			$stmt = $globalDB->prepare("SELECT * FROM preliminary_booking WHERE position = ?");
			$stmt->execute(array($position->get('id')));
			$result = array();

			foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $prel_booking) {
				$stmt2 = $globalDB->prepare("SELECT `name`, `area`, `price` FROM `fair_map_position` WHERE `id` = ? LIMIT 0, 1");
				$stmt2->execute(array($prel_booking->position));
				$position = $stmt2->fetch(PDO::FETCH_ASSOC);

				$user = new User();
				$user->load($prel_booking->user, 'id');
				$prel_booking->company = $user->get('company');
				$prel_booking->booking_time = date('d-m-Y H:i', $prel_booking->booking_time);
				$prel_booking->standSpace = $position;
				$prel_booking->denyUrl = BASE_URL . 'administrator/deleteBooking/' . $prel_booking->id . "/" . $_GET["position"];
				$prel_booking->denyImgUrl = BASE_URL . 'images/icons/delete.png';
				$prel_booking->baseUrl = BASE_URL;
				$result[] = $prel_booking;
			}

			header('Content-type: application/json; charset=utf-8');
			echo json_encode($result);
		}
	}
}

if (isset($_POST['reserve_preliminary'])) {

	if (userLevel() < 1)
		exit;

	$map = new FairMap();
	$map->load($_POST['map'], 'id');
	
	$prel_booking = new PreliminaryBooking();
	$prel_booking->load($_POST['reserve_preliminary'], 'id');
	
	if ($prel_booking->wasLoaded()) {

		$pos = new FairMapPosition();
		$pos->load($prel_booking->get('position'), 'id');

		$ex = new Exhibitor();
		$ex->set('user', $_POST['user']);
		$ex->set('position', $pos->get('id'));
		$ex->set('map', $_POST['map']);
		$ex->set('fair', $prel_booking->get('fair'));		
		$ex->set('commodity', $_POST['commodity']);
		$ex->set('arranger_message', $_POST['message']);
		$ex->set('edit_time', time());
		$ex->set('booking_time', time());
		$ex->set('clone', 0);
		$ex->set('status', 1);
		$exId = $ex->save();

	$categoryNames = array();

	if (isset($_POST['categories']) && is_array($_POST['categories'])) {
		$stmt = $pos->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
		foreach ($_POST['categories'] as $cat) {
			$category = new ExhibitorCategory();
			$category->load($cat, "id");
			if ($category->wasLoaded()) {
				$categoryNames[] = $category->get("name");
			}

			$stmt->execute(array($exId, $cat));
		}
	}
	
	
	$options = array();

	if (isset($_POST['options']) && is_array($_POST['options'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");
		foreach ($_POST['options'] as $opt) {
			$stmt->execute(array($exId, $opt));

			$ex_option = new FairExtraOption();
			$ex_option->load($opt, 'id');
			$options[] = $ex_option->get('text');			
		}
	}

	$articles = array();

	if (isset($_POST['articles']) && is_array($_POST['articles'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
		$arts = $_POST['articles'];
		$amounts = $_POST['artamount'];

		foreach (array_combine($arts, $amounts) as $art => $amount) {
			$stmt->execute(array($exId, $art, $amount));

			$ex_article = new FairArticle();
			$ex_article->load($art, 'id');
			$articles[] = $ex_article->get('text');			
		}
	}

		$categories = implode(', ', $categoryNames);
		$options = implode(', ', $options);
		$time_now = date('d-m-Y H:i');
		
		$previous_status = 3;
		$status = 1;
		$pos->set('status', $status);
		$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));
		$pos->save();

		$prel_booking->delete();

		$fair = new Fair();
		$fair->load($prel_booking->get('fair'), 'id');

		$organizer = new User();
		$organizer->load($fair->get('created_by'), 'id');

		$me = new User();
		$me->load($_SESSION['user_id'], 'id');

		$ex_user = new User();
		$ex_user->load($ex->get('user'), 'id');
		
		//Check mail settings and send only if setting is set
		if ($fair->wasLoaded()) {
			$mailSettings = json_decode($fair->get("mail_settings"));
			if (is_array($mailSettings->acceptPreliminaryBooking)) {
				$previous_status = posStatusToText($previous_status);
				$status = posStatusToText($status);

				if (in_array("0", $mailSettings->acceptPreliminaryBooking)) {
					$mail_organizer = new Mail($organizer->get('email'), 'preliminary_approved_confirm', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
					$mail_organizer->setMailVar('previous_status', $previous_status);
					$mail_organizer->setMailVar('status', $status);
					$mail_organizer->setMailvar("exhibitor_name", $ex_user->get("name"));
					$mail_organizer->setMailvar("company_name", $ex_user->get("company"));
					$mail_organizer->setMailvar("event_name", $fair->get("name"));
					$mail_organizer->setMailVar("position_name", $pos->get("name"));
					$mail_organizer->setMailVar("booking_time", date('d-m-Y H:i:s', intval($ex->get("booking_time"))));
					$mail_organizer->setMailVar("url", BASE_URL . $fair->get("url"));
					$mail_organizer->setMailVar("position_information", $pos->get("information"));
					$mail_organizer->setMailVar("exhibitor_commodity", $_POST['commodity']);
					$mail_organizer->setMailVar("exhibitor_category", $categories);
					$mail_organizer->setMailVar('exhibitor_options', $options);
					$mail_organizer->setMailVar('arranger_message', $_POST['message']);
					$mail_organizer->setMailVar('edit_time', $time_now);
					$mail_organizer->setMailVar('date_expires', $_POST['expires']);
					$mail_organizer->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
					$mail_organizer->setMailVar('creator_name', $me->get('name'));
					$mail_organizer->send();
				}

				if (in_array("1", $mailSettings->acceptPreliminaryBooking)) {
					$mail_user = new Mail($ex_user->get('email'), 'preliminary_approved_receipt', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
					$mail_user->setMailVar('previous_status', $previous_status);
					$mail_user->setMailVar('status', $status);
					$mail_user->setMailvar("exhibitor_name", $ex_user->get("name"));
					$mail_user->setMailvar("company_name", $ex_user->get("company"));
					$mail_user->setMailvar("event_name", $fair->get("name"));
					$mail_user->setMailVar('event_email', $fair->get('contact_email'));
					$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
					$mail_user->setMailVar('event_website', $fair->get('website'));
					$mail_user->setMailVar("position_name", $pos->get("name"));
					$mail_user->setMailVar("booking_time", date('d-m-Y H:i:s', intval($ex->get("booking_time"))));
					$mail_user->setMailVar("url", BASE_URL . $fair->get("url"));
					$mail_user->setMailVar("position_information", $pos->get("information"));
					$mail_user->setMailVar("exhibitor_commodity", $_POST['commodity']);
					$mail_user->setMailVar("exhibitor_category", $categories);
					$mail_user->setMailVar('exhibitor_options', $options);
					$mail_user->setMailVar('arranger_message', $_POST['message']);
					$mail_user->setMailVar('edit_time', $time_now);
					$mail_user->setMailVar('date_expires', $_POST['expires']);
					$mail_user->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
					$mail_user->setMailVar('creator_name', $me->get('name'));
					$mail_user->send();
				}
			}
		}
	}
	exit;
}

if (isset($_POST['book_preliminary'])) {

	if (userLevel() < 1)
		exit;

	$map = new FairMap();
	$map->load($_POST['map'], 'id');
	
	$prel_booking = new PreliminaryBooking();
	$prel_booking->load($_POST['book_preliminary'], 'id');
	
	if ($prel_booking->wasLoaded()) {

		$pos = new FairMapPosition();
		$pos->load($prel_booking->get('position'), 'id');

		$ex = new Exhibitor();
		$ex->set('user', $_POST['user']);
		$ex->set('position', $pos->get('id'));
		$ex->set('map', $_POST['map']);
		$ex->set('fair', $prel_booking->get('fair'));		
		$ex->set('commodity', $_POST['commodity']);
		$ex->set('arranger_message', $_POST['message']);
		$ex->set('edit_time', time());
		$ex->set('booking_time', time());
		$ex->set('clone', 0);
		$ex->set('status', 2);
		$exId = $ex->save();

	$categoryNames = array();

	if (isset($_POST['categories']) && is_array($_POST['categories'])) {
		$stmt = $pos->db->prepare("INSERT INTO exhibitor_category_rel (exhibitor, category) VALUES (?, ?)");
		foreach ($_POST['categories'] as $cat) {
			$category = new ExhibitorCategory();
			$category->load($cat, "id");
			if ($category->wasLoaded()) {
				$categoryNames[] = $category->get("name");
			}

			$stmt->execute(array($exId, $cat));
		}
	}
	
	
	$options = array();

	if (isset($_POST['options']) && is_array($_POST['options'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_option_rel` (`exhibitor`, `option`) VALUES (?, ?)");
		foreach ($_POST['options'] as $opt) {
			$stmt->execute(array($exId, $opt));

			$ex_option = new FairExtraOption();
			$ex_option->load($opt, 'id');
			$options[] = $ex_option->get('text');			
		}
	}

	$articles = array();

	if (isset($_POST['articles']) && is_array($_POST['articles'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_article_rel` (`exhibitor`, `article`, `amount`) VALUES (?, ?, ?)");
		$arts = $_POST['articles'];
		$amounts = $_POST['artamount'];

		foreach (array_combine($arts, $amounts) as $art => $amount) {
			$stmt->execute(array($exId, $art, $amount));

			$ex_article = new FairArticle();
			$ex_article->load($art, 'id');
			$articles[] = $ex_article->get('text');			
		}
	}

		$categories = implode(', ', $categoryNames);
		$options = implode(', ', $options);
		$time_now = date('d-m-Y H:i');
		
		$previous_status = 3;
		$status = 2;
		$pos->set('status', $status);
		$pos->set('expires', '0000-00-00 00:00:00');
		$pos->save();

		$prel_booking->delete();

		$fair = new Fair();
		$fair->load($prel_booking->get('fair'), 'id');

		$organizer = new User();
		$organizer->load($fair->get('created_by'), 'id');

		$me = new User();
		$me->load($_SESSION['user_id'], 'id');

		$ex_user = new User();
		$ex_user->load($ex->get('user'), 'id');
		
		//Check mail settings and send only if setting is set
		if ($fair->wasLoaded()) {
			$mailSettings = json_decode($fair->get("mail_settings"));
			if (is_array($mailSettings->acceptPreliminaryBooking)) {
				$previous_status = posStatusToText($previous_status);
				$status = posStatusToText($status);

				if (in_array("0", $mailSettings->acceptPreliminaryBooking)) {
					$mail_organizer = new Mail($organizer->get('email'), 'preliminary_approved_confirm', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
					$mail_organizer->setMailVar('previous_status', $previous_status);
					$mail_organizer->setMailVar('status', $status);
					$mail_organizer->setMailvar("exhibitor_name", $ex_user->get("name"));
					$mail_organizer->setMailvar("company_name", $ex_user->get("company"));
					$mail_organizer->setMailvar("event_name", $fair->get("name"));
					$mail_organizer->setMailVar("position_name", $pos->get("name"));
					$mail_organizer->setMailVar("booking_time", date('d-m-Y H:i:s', intval($ex->get("booking_time"))));
					$mail_organizer->setMailVar("url", BASE_URL . $fair->get("url"));
					$mail_organizer->setMailVar("position_information", $pos->get("information"));
					$mail_organizer->setMailVar("exhibitor_commodity", $_POST['commodity']);
					$mail_organizer->setMailVar("exhibitor_category", $categories);
					$mail_organizer->setMailVar('exhibitor_options', $options);
					$mail_organizer->setMailVar('arranger_message', $_POST['message']);
					$mail_organizer->setMailVar('edit_time', $time_now);;
					$mail_organizer->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
					$mail_organizer->setMailVar('creator_name', $me->get('name'));
					$mail_organizer->send();
				}

				if (in_array("1", $mailSettings->acceptPreliminaryBooking)) {
					$mail_user = new Mail($ex_user->get('email'), 'preliminary_approved_receipt', $fair->get("url") . EMAIL_FROM_DOMAIN, $fair->get("name"));
					$mail_user->setMailVar('previous_status', $previous_status);
					$mail_user->setMailVar('status', $status);
					$mail_user->setMailvar("exhibitor_name", $ex_user->get("name"));
					$mail_user->setMailvar("company_name", $ex_user->get("company"));
					$mail_user->setMailvar("event_name", $fair->get("name"));
					$mail_user->setMailVar('event_email', $fair->get('contact_email'));
					$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
					$mail_user->setMailVar('event_website', $fair->get('website'));
					$mail_user->setMailVar("position_name", $pos->get("name"));
					$mail_user->setMailVar("booking_time", date('d-m-Y H:i:s', intval($ex->get("booking_time"))));
					$mail_user->setMailVar("url", BASE_URL . $fair->get("url"));
					$mail_user->setMailVar("position_information", $pos->get("information"));
					$mail_user->setMailVar("exhibitor_commodity", $_POST['commodity']);
					$mail_user->setMailVar("exhibitor_category", $categories);
					$mail_user->setMailVar('exhibitor_options', $options);
					$mail_user->setMailVar('arranger_message', $_POST['message']);
					$mail_user->setMailVar('edit_time', $time_now);
					$mail_user->setMailVar('creator_accesslevel', accessLevelToText(userLevel()));
					$mail_user->setMailVar('creator_name', $me->get('name'));
					$mail_user->send();
				}
			}
		}
	}
	exit;
}

if (isset($_POST["getGridSettings"])) {
	$id = $_POST["getGridSettings"];
	
	$map = new FairMap();
	$map->load($id, 'id');

	echo $map->get("grid_settings");
}
if (isset($_POST["setGridSettings"])) {
	$id = $_POST["setGridSettings"];
	$settings = $_POST["gridSettings"];

	$map = new FairMap();
	$map->load($id, "id");
	$map->set("grid_settings", $settings);
	$map->save();
}
if (isset($_POST["saveToolboxPosition"])) {
	setcookie("gridtoolbox_position", $_POST["saveToolboxPosition"], time() + (3600 * 24 * 365));
}
if (isset($_POST["getToolboxPosition"])) {
	echo $_COOKIE["gridtoolbox_position"];
}
?>
