<?php

$parts = explode('/', dirname(dirname(__FILE__)));
$parts = array_slice($parts, 0, -1);
define('ROOT', implode('/', $parts).'/');

session_start();
require_once ROOT.'config/config.php';
require_once ROOT.'lib/functions.php';

//Autoload any classes that are required
function cb_autoload($className) {
	if (file_exists(ROOT.'lib/classes/'.$className.'.php')) {
		require_once(ROOT.'lib/classes/'.$className.'.php');
		return true;

	} else if (file_exists(ROOT.'application/controllers/'.$className.'.php')) {
		require_once(ROOT.'application/controllers/'.$className.'.php');
		return true;

	} else if (file_exists(ROOT.'application/models/'.$className.'.php')) {
		require_once(ROOT.'application/models/'.$className.'.php');
		return true;
	
	}
  
  return false;
}

spl_autoload_register( 'cb_autoload' );

$lang = (isset($_COOKIE['language'])) ? $_COOKIE['language'] : 'eng';
define('LANGUAGE', $lang);
$translator = new Translator($lang);

$globalDB = new Database;
global $globalDB;

function userHasAccess() {
	
}
if (isset($_GET['checkIfLocked'])) {
	$fair = new Fair();
	$fair->loadself($_GET['checkIfLocked'], 'id');
	if ($fair->isLocked()) {
		echo true;
	}
	exit;
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
		$fair_registration->accept();
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

/*
if (isset($_POST['getPreliminary'])) {
	
	$query = $globalDB->query("SELECT prel.*, user.* FROM preliminary_booking AS prel LEFT JOIN user ON prel.user = user.id WHERE position = '".$_POST['getPreliminary']."'");
	$result = $query->fetch(PDO::FETCH_ASSOC);
	
	$result['categories'] = explode('|', $result['categories']);
	
	//die(json_encode($result));
	
}
*/
if (isset($_POST['init'])) {

	$map = new FairMap();
	$map->load($_POST['init'], 'id');

	$fair = new Fair();
	$fair->loadsimple($map->get('fair'), 'id');
	if ($fair->wasLoaded()) {
		$fairInvoice = new FairInvoice();
		$fairInvoice->load($map->get('fair'), 'fair');
	} else {
		$fair->loadsimple($_SESSION['user_fair'], 'id');
		if ($fair->wasLoaded()) {
			$fairInvoice = new FairInvoice();
			$fairInvoice->load($fair->get('id'), 'fair');
		}
	}
	$prels = array();
	if (userLevel() == 1) {
		$user = new User();
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
		'fair'=> $fair->get('id'),
		'fairGroup'=> $fair->get('group'),
		'defaultreservationdate'=> date('d-m-Y H:i', $fair->get('default_reservation_date')),
		'currency'=> $fair->get('currency'),
		'name'=>$map->get('name'),
		'image'=>$map->get('large_image'),
		'large_image'=>$map->get('large_image'),
		'islocked'=>$fair->isLocked(),
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
				$c = new ExhibitorCategory();
				$c->load($cat, 'id');
				if ($c->wasLoaded()) {
					$c->set('category_id', $cat);
					$cats[] = $c;
				}
			}
			$ex->set('categories', $cats);
			foreach ($ex->get('exhibitor_options') as $opt) {
				$o = new FairExtraOption();
				$o->load($opt, 'id');
				if ($o->wasLoaded()) {
					$o->set('option_id', $opt);
					$opts[] = $o;
				}
			}
			$ex->set("options", $opts);
			$articles = $ex->get('exhibitor_articles');
			$amount = $ex->get('exhibitor_articles_amount');
			if (!empty($articles) && !empty($amount)) {
				foreach (array_combine($articles, $amount) as $art => $qt) {
					//file_put_contents('php://stderr', print_r($art, TRUE));
					$a = new FairArticle();
					$a->load($art, 'id');
					if ($a->wasLoaded()) {
						$a->set('article_id', $art);
						$a->set('amount', $qt);
						$arts[] = $a;
					}
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

	/// KONTROLLERAD MAILMALL

	if (userLevel() < 1)
		exit;

	$pos = new FairMapPosition();
	$pos->load($_POST['bookPosition'], 'id');
	
	//Delete existing exhibitor if position is booked
	if ($pos->get('status') === 0) {
		$exhibitor = new Exhibitor();
	} else {
		$exhibitor = new Exhibitor();
		$exhibitor->load($pos->get('id'), 'position');
	}

	if (isset($_POST['user']) && userLevel() > 1) {
		$exhibitor->set('user', $_POST['user']);
	} else {
		$exhibitor->set('user', $_SESSION['user_id']);
	}

	$exhibitor->set('fair', $_POST['fair']);
	$exhibitor->set('position', $_POST['bookPosition']);
	$exhibitor->set('commodity', $_POST['commodity']);
	$exhibitor->set('arranger_message', '');
	$exhibitor->set('booking_time', time());
	$exhibitor->set('edit_time', 0);
	$exhibitor->set('clone', 0);
	$exhibitor->set('status', 2);
	$exId = $exhibitor->save();

	$pos->set('status', 2);
	$pos->set('expires', '0000-00-00 00:00:00');
	$pos->save();

	if ($exhibitor->wasLoaded()) {
		$stmt = $pos->db->prepare("SELECT id FROM exhibitor_invoice WHERE exhibitor = ? AND status = 1");
		$stmt->execute(array($exId));
		$result = $stmt->fetch();
		if ($result > 0) {
			$stmt = $pos->db->prepare("UPDATE exhibitor_invoice SET status = 2 WHERE exhibitor = ? AND id = ?");
			$stmt->execute(array($exId, $result['id']));
		}
		// Remove old categories for this booking
		$stmt = $pos->db->prepare("DELETE FROM exhibitor_category_rel WHERE exhibitor = ?");
		$stmt->execute(array($exId));
		// Remove old options for this booking
		$stmt = $pos->db->prepare("DELETE FROM exhibitor_option_rel WHERE exhibitor = ?");
		$stmt->execute(array($exId));
		// Remove old articles for this booking
		$stmt = $pos->db->prepare("DELETE FROM exhibitor_article_rel WHERE exhibitor = ?");
		$stmt->execute(array($exId));
	}

	$categories = array();

	if (isset($_POST['categories']) && is_array($_POST['categories'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_category_rel` (`exhibitor`, `category`) VALUES (?, ?)");
		foreach ($_POST['categories'] as $cat) {
			$stmt->execute(array($exId, $cat));
			$category = new ExhibitorCategory();
			$category->load($cat, 'id');
			if ($category->wasLoaded()) {
				$categories[] = $category->get('name');
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
	
	$fair = new Fair();
	$fair->load($exhibitor->get('fair'), 'id');
	
	$organizer = new User();
	$organizer->load($fair->get('created_by'), 'id');

	$user = new User();
	$user->load($exhibitor->get('user'), 'id');

	$htmlcategoryNames = implode('<br>', $categories);
	$fairInvoice = new FairInvoice();
	$fairInvoice->load($exhibitor->get('fair'), 'fair');

	/*****************************************************************************************/
	/*****************************************************************************************/
	/************************				PREPARE MAIL START			  *************************/
	/*****************************************************************************************/
	/*****************************************************************************************/

	/*********************************************************************************/
	/*********************************************************************************/
	/********************************       LABELS       *****************************/
	/*********************************************************************************/
	/*********************************************************************************/

	$name_label = $translator->{'Name'};
	$price_label = $translator->{'Price'};
	$amount_label = $translator->{'Amount'};
	$vat_label = $translator->{'Vat'};
	$sum_label = $translator->{'Sum'};
	$booked_space_label = $translator->{'Stand'};
	$options_label = $translator->{'Options'};
	$articles_label = $translator->{'Articles'};
	$tax_label = $translator->{'Tax'};
	$parttotal_label = $translator->{'Subtotal'};
	$net_label = $translator->{'Net'};
	$rounding_label = $translator->{'Rounding'};
	$total_label = $translator->{'total:'};
	$st_label = $translator->{'st'};
	$nothing_selected_label = $translator->{'No articles or options selected.'};

	/*************************************************************/
	/*************************************************************/
	/*****************     PRICES AND AMOUNTS        *************/
	/*************************************************************/
	/*************************************************************/ 

	$totalPrice = 0;
	$totalNetPrice = 0;
	$VatPrice0 = 0;
	$VatPrice12 = 0;
	$VatPrice18 = 0;
	$VatPrice25 = 0;
	$excludeVatPrice0 = 0;
	$excludeVatPrice12 = 0;
	$excludeVatPrice18 = 0;
	$excludeVatPrice25 = 0;
	$currency = $fair->get('currency');
	$position_vat = 0;
	$position_name = $pos->get('name');
	$position_price = $pos->get('price');
	$position_vat = $fairInvoice->get('pos_vat');

		/*********************************************************************************************/
		/*********************************************************************************************/
		/**********************					MAIL BOOKING TABLE START			  ***********************/
		/*********************************************************************************************/
		/*********************************************************************************************/
$html = '<!-- SIX COLUMN HEADERS -->
			<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;padding:10px 0 0 0;">
			 <!-- ID -->
			 <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			     ID
			   </p>
			 </td>
			 <!-- NAME -->
			 <td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			     '.$name_label.'
			   </p>
			 </td>
			 <!-- PRICE -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
			     '.$price_label.'
			   </p>
			 </td>
			 <!-- AMOUNT -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
			     '.$amount_label.'
			   </p>
			 </td>
			 <!-- VAT % -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
			     '.$vat_label.'
			   </p>
			 </td>
			 <!-- SUM -->
			 <td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
			     '.$sum_label.'
			   </p>
			 </td>
			</tr>
			<!-- SPACER ROW -->
			<tr style="mso-yfti-irow:1;height:11.1pt">
			</tr>
			<!-- STAND SPACE ROW LABEL-->
			<tr style="mso-yfti-irow:1;height:25.1pt">
			 	<!-- ID -->
				<td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			<!-- NAME -->
				<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$booked_space_label.'
						</span>
					</p>
				</td>
				<!-- PRICE -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- AMOUNT -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- VAT % -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- SUM -->
				<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			</tr>
			<!-- STAND SPACE ROW INFO -->
			<tr style="mso-yfti-irow:1;height:25.1pt">
			 	<!-- ID -->
				<td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			<!-- NAME -->
				<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_name.'
						</span>
					</p>
				</td>
				<!-- PRICE -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_price.'
						</span>
					</p>
				</td>
				<!-- AMOUNT -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						1'.$st_label.'
						</span>
					</p>
				</td>
				<!-- VAT % -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_vat.'%
						</span>
					</p>
				</td>
				<!-- SUM -->
				<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.number_format($position_price, 2, ',', ' ').'
						</span>
					</p>
				</td>
			</tr>';

$html_sum = '<!-- TWO COLUMN VAT PRICE AND NET SUMMATION -->
				<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;">
					<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
						<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						</p>
					</td>
					<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
						<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						</p>
					</td>
				</tr>';

if ($position_vat == 25) {
	$excludeVatPrice25 += $position_price;
} else if ($position_vat == 18) {
	$excludeVatPrice18 += $position_price;
} else {
	$excludeVatPrice0 += $position_price;
}

if (!empty($_POST['options']) && is_array($_POST['options'])) {
	$html .= '<!-- SIX COLUMNS -->
               <tr style="mso-yfti-irow:1;height:25.1pt">
                	<!-- ID -->
                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
                  		</span>
                  	</p>
                  </td>
                  <!-- NAME -->
						<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.$options_label.'
								</span>
							</p>
						</td>
						<!-- PRICE -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- AMOUNT -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- VAT % -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- SUM -->
						<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
					</tr>';

	for ($row=0; $row<count($options[1]); $row++) {
		$html .= '<!-- SIX COLUMNS -->
	               <tr style="mso-yfti-irow:1;height:25.1pt">
	                	<!-- ID -->
	                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
	                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
	                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
	                  		'.$options[0][$row].'
	                  		</span>
	                  	</p>
	                  </td>
	                  <!-- NAME -->
							<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[1][$row].'
									</span>
								</p>
							</td>
							<!-- PRICE -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[2][$row].'
									</span>
								</p>
							</td>
							<!-- AMOUNT -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									1'.$st_label.'
									</span>
								</p>
							</td>
							<!-- VAT % -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[3][$row].'%
									</span>
								</p>
							</td>
							<!-- SUM -->
							<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')).'
									</span>
								</p>
							</td>
						</tr>';
	}
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	$html .= '<!-- SIX COLUMNS -->
               <tr style="mso-yfti-irow:1;height:25.1pt">
                	<!-- ID -->
                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
                  		</span>
                  	</p>
                  </td>
                  <!-- NAME -->
						<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.$articles_label.'
								</span>
							</p>
						</td>
						<!-- PRICE -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- AMOUNT -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- VAT % -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- SUM -->
						<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
					</tr>';

	for ($row=0; $row<count($articles[1]); $row++) {
		$html .= '<!-- SIX COLUMNS -->
	               <tr style="mso-yfti-irow:1;height:25.1pt">
	                	<!-- ID -->
	                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
	                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
	                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
	                  		'.$articles[0][$row].'
	                  		</span>
	                  	</p>
	                  </td>
	                  <!-- NAME -->
							<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[1][$row].'
									</span>
								</p>
							</td>
							<!-- PRICE -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', $articles[2][$row]).'
									</span>
								</p>
							</td>
							<!-- AMOUNT -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[3][$row].' '.$st_label.'
									</span>
								</p>
							</td>
							<!-- VAT % -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[4][$row].'%
									</span>
								</p>
							</td>
							<!-- SUM -->
							<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')).'
									</span>
								</p>
							</td>
						</tr>';
    }
}


if (!empty($_POST['options']) && is_array($_POST['options'])) {
	for ($row=0; $row<count($options[1]); $row++) {

		if ($options[3][$row] == 25) {
			$excludeVatPrice25 += $options[2][$row];
		}
		if ($options[3][$row] == 18) {
			$excludeVatPrice18 += $options[2][$row];
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
		if ($articles[4][$row] == 18) {
			$excludeVatPrice18 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
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
$VatPrice18 = $excludeVatPrice18*0.18;
$VatPrice25 = $excludeVatPrice25*0.25;
$totalPrice += $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25 + $VatPrice12 + $VatPrice18 + $VatPrice25 + $VatPrice0;
$totalNetPrice += $excludeVatPrice0 + $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25;

$totalPriceRounded = round($totalPrice);
$pennys = ($totalPriceRounded - $totalPrice);

if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
	$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
	$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');

	$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (12%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice12).'
							</p>
						</td>
					</tr>';

}
if (!empty($excludeVatPrice18) && !empty($VatPrice18)) {
	$excludeVatPrice18 = number_format($excludeVatPrice18, 2, ',', ' ');
	$VatPrice18 = number_format($VatPrice18, 2, ',', ' ');
	$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (18%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice18).'
							</p>
						</td>
					</tr>';
}
if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
	$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
	$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
	$html_sum  .=   '<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (25%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice25).'
							</p>
						</td>
					</tr>';
}
if (empty($excludeVatPrice25) && empty($VatPrice25) && empty($excludeVatPrice18) && empty($VatPrice18) && empty($excludeVatPrice12) && empty($VatPrice12)) {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								0,00
							</p>
						</td>
					</tr>';
} 
if (empty($totalPrice)) {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$net_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								0,00
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$currency.' '.$total_label.'&nbsp;&nbsp;</strong>0,00
							</p>
						</td>
					</tr>';
} else {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal align=left style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$net_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', number_format($totalNetPrice, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$currency.' '.$total_label.'&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')).'
							</p>
						</td>
					</tr>';
}

/*********************************************************************************************/
/*********************************************************************************************/
/**********************					MAIL BOOKING TABLE END				  ***********************/
/*********************************************************************************************/
/*********************************************************************************************/
	
	$position_information = $pos->get('information');
	if ($position_information == '')
		$position_information = $translator->{'None specified.'};

	$position_area = $pos->get('area');
	if ($position_area == '')
		$position_area = $translator->{'None specified.'};

/*	$arranger_message = $_POST['arranger_message'];
	if ($arranger_message == '')
		$arranger_message = $translator->{'No message was given.'};
*/	
	$exhibitor_commodity = $_POST['commodity'];
	if ($exhibitor_commodity == '')
		$exhibitor_commodity = $translator->{'No commodity was entered.'};


	//Check mail settings and send only if setting is set
	if ($fair->wasLoaded()) {
		$mailSettings = json_decode($fair->get("mail_settings"));
		if (isset($mailSettings->bookingCreated) && is_array($mailSettings->bookingCreated)) {
			$errors = array();
			$mail_errors = array();
			$email = $fair->get("url") . EMAIL_FROM_DOMAIN;
			$from = array($email => $fair->get("windowtitle"));

			if($fair->get('contact_name')) {
				$from = array($email => $fair->get('contact_name'));
			}

			if (in_array("0", $mailSettings->bookingCreated)) {
				try {
					if ($organizer->get('contact_email') == '')
						$recipients = array($organizer->get('email') => $organizer->get('company'));
					else
						$recipients = array($organizer->get('contact_email') => $organizer->get('name'));

					$mail_organizer = new Mail();
					$mail_organizer->setTemplate('booking_created_confirm');
					$mail_organizer->setPlainTemplate('booking_created_confirm');
					$mail_organizer->setFrom($from);
					$mail_organizer->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
					$mail_organizer->setRecipients($recipients);
						$mail_organizer->setMailVar('booking_table', $html);
						$mail_organizer->setMailVar('booking_sum', $html_sum);
						$mail_organizer->setMailVar('exhibitor_company_name', $user->get('company'));
						$mail_organizer->setMailvar('exhibitor_name', $user->get('name'));
						$mail_organizer->setMailVar('event_name', $fair->get('windowtitle'));
						$mail_organizer->setMailVar('event_url', BASE_URL . $fair->get('url'));
						$mail_organizer->setMailVar('position_name', $pos->get('name'));
						$mail_organizer->setMailVar('position_information', $position_information);
						$mail_organizer->setMailVar('position_area', $position_area);
						$mail_organizer->setMailVar('arranger_message', '');
						$mail_organizer->setMailVar('commodity', $exhibitor_commodity);
						$mail_organizer->setMailVar('html_categories', $htmlcategoryNames);
				
						if(!$mail_organizer->send()) {
							$errors[] = $organizer->get('company');
						}

					} catch(Swift_RfcComplianceException $ex) {
						// Felaktig epost-adress
						$errors[] = $organizer->get('company');
						$mail_errors[] = $ex->getMessage();

					} catch(Exception $ex) {
						// Okänt fel
						$errors[] = $organizer->get('company');
						$mail_errors[] = $ex->getMessage();
					}
				}
			if (in_array("1", $mailSettings->bookingCreated)) {
				try {
					if ($user->get('contact_email') == '')
						$recipients = array($user->get('email') => $user->get('company'));
					else
						$recipients = array($user->get('contact_email') => $user->get('name'));
					
					$mail_user = new Mail();
					$mail_user->setTemplate('booking_created_receipt');
					$mail_user->setPlainTemplate('booking_created_receipt');
					$mail_user->setFrom($from);
					$mail_user->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
					$mail_user->setRecipients($recipients);
						$mail_user->setMailVar('booking_table', $html);
						$mail_user->setMailVar('booking_sum', $html_sum);
						$mail_user->setMailVar('exhibitor_company_name', $user->get('company'));
						$mail_user->setMailvar('exhibitor_name', $user->get('name'));
						$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
						$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
						$mail_user->setMailVar('event_email', $fair->get('contact_email'));
						$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
						$mail_user->setMailVar('event_website', $fair->get('website'));
						$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
						$mail_user->setMailVar('position_name', $pos->get('name'));
						$mail_user->setMailVar('position_information', $position_information);
						$mail_user->setMailVar('position_area', $position_area);
						$mail_user->setMailVar('arranger_message', '');
						$mail_user->setMailVar('commodity', $exhibitor_commodity);
						$mail_user->setMailVar('html_categories', $htmlcategoryNames);

					if(!$mail_user->send()) {
						$errors[] = $user->get('company');
					}

				} catch(Swift_RfcComplianceException $ex) {
					// Felaktig epost-adress
					$errors[] = $user->get('company');
					$mail_errors[] = $ex->getMessage();

				} catch(Exception $ex) {
					// Okänt fel
					$errors[] = $user->get('company');
					$mail_errors[] = $ex->getMessage();
				}
			}
			if ($errors) {
				$_SESSION['mail_errors'] = $mail_errors;
			}
		}
	}
	exit;
}


if (isset($_POST['fairRegistration'])) {
	
	/// KONTROLLERAD MAILMALL

	if (userLevel() == 1) {

		if ($_POST['fair'] > 0) {
			$fair = new Fair();
			$fair->loadsimple($_POST['fair'], 'id');
		} else {
			$fair = new Fair();
			$fair->loadsimple($_SESSION['user_fair'], 'id');
		}
		$user = new User();
		$user->load($_SESSION['user_id'], 'id');

		$organizer = new User();
		$organizer->load($fair->get('created_by'), 'id');

		if ($fair->wasLoaded() && $user->wasLoaded()) {

				$category_ids = '';
				$categories = array();
				if (isset($_POST['categories']) && is_array($_POST['categories'])) {
					foreach ($_POST['categories'] as $cat) {
						$category = new ExhibitorCategory();
						$category->load($cat, 'id');
						if ($category->wasLoaded()) {
							$categories[] = $category->get('name');
						}
					}
					$category_ids = implode('|', $_POST['categories']);
				}

				$option_ids = '';
				$options = array();
				if (isset($_POST['options']) && is_array($_POST['options'])) {
					foreach ($_POST['options'] as $opt) {
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
					$option_ids = implode('|', $_POST['options']);
				}

				$article_ids = '';
				$article_amounts = '';
				$articles = array();
				if (!empty($_POST['articles']) && !empty($_POST['artamount'])) {
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
						$article_ids = implode('|', $_POST['articles']);
						$article_amounts = implode('|', $_POST['artamount']);				
				}			
				$registration = new FairRegistration();
				$registration->set('user', $user->get('id'));
				$registration->set('fair', $fair->get('id'));
				$registration->set('categories', $category_ids);
				$registration->set('options', $option_ids);
				$registration->set('articles', $article_ids);
				$registration->set('amount', $article_amounts);
				$registration->set('commodity', $_POST['commodity']);
				$registration->set('arranger_message', $_POST['arranger_message']);
				$registration->set('area', $_POST['area']);
				$registration->set('booking_time', time());
				$registration->save();

				// Connect user to fair
				if (!userIsConnectedTo($fair->get('id'))) {
					$stmt = $registration->db->prepare("INSERT INTO fair_user_relation (`fair`, `user`, `connected_time`) VALUES (?, ?, ?)");
					$stmt->execute(array($fair->get('id'), $user->get('id'), time()));
				}

				$htmlcategoryNames = implode('<br>', $categories);

				/*****************************************************************************************/
				/*****************************************************************************************/
				/************************				PREPARE MAIL START			  *************************/
				/*****************************************************************************************/
				/*****************************************************************************************/

				/*********************************************************************************/
				/*********************************************************************************/
				/********************************       LABELS       *****************************/
				/*********************************************************************************/
				/*********************************************************************************/
				$name_label = $translator->{'Name'};
				$price_label = $translator->{'Price'};
				$amount_label = $translator->{'Amount'};
				$vat_label = $translator->{'Vat'};
				$sum_label = $translator->{'Sum'};
				$options_label = $translator->{'Options'};
				$articles_label = $translator->{'Articles'};
				$tax_label = $translator->{'Tax'};
				$parttotal_label = $translator->{'Subtotal'};
				$net_label = $translator->{'Net'};
				$rounding_label = $translator->{'Rounding'};
				$to_pay_label = $translator->{'to pay:'};
				$estimated_label = $translator->{'Estimated'};
				$st_label = $translator->{'st'};
				$nothing_selected_label = $translator->{'No articles or options selected.'};
				$not_including_position_price = $translator->{'not including position price'};


				/*************************************************************/
				/*************************************************************/
				/*****************     PRICES AND AMOUNTS        *************/
				/*************************************************************/
				/*************************************************************/ 

				$totalPrice = 0;
				$totalNetPrice = 0;
				$VatPrice0 = 0;
				$VatPrice12 = 0;
				$VatPrice18 = 0;
				$VatPrice25 = 0;
				$excludeVatPrice0 = 0;
				$excludeVatPrice12 = 0;
				$excludeVatPrice18 = 0;
				$excludeVatPrice25 = 0;
				$currency = $fair->get('currency');

				/*********************************************************************************************/
				/*********************************************************************************************/
				/**********************					MAIL BOOKING TABLE START			  ***********************/
				/*********************************************************************************************/
				/*********************************************************************************************/
		$html = '<!-- SIX COLUMN HEADERS -->
					<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;padding:10px 0 0 0;">
					 <!-- ID -->
					 <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
					     ID
					   </p>
					 </td>
					 <!-- NAME -->
					 <td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
					     '.$name_label.'
					   </p>
					 </td>
					 <!-- PRICE -->
					 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
					     '.$price_label.'
					   </p>
					 </td>
					 <!-- AMOUNT -->
					 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
					     '.$amount_label.'
					   </p>
					 </td>
					 <!-- VAT % -->
					 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
					     '.$vat_label.'
					   </p>
					 </td>
					 <!-- SUM -->
					 <td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
					     '.$sum_label.'
					   </p>
					 </td>
					</tr>
					<!-- SPACER ROW -->
					<tr style="mso-yfti-irow:1;height:11.1pt">
					</tr>';
		$html_sum = '<!-- TWO COLUMN VAT PRICE AND NET SUMMATION -->
						<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;">
							<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
								<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								</p>
							</td>
							<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								</p>
							</td>
						</tr>';
		if (!empty($_POST['options']) && is_array($_POST['options'])) {
			$html .= '<!-- SIX COLUMNS -->
		               <tr style="mso-yfti-irow:1;height:25.1pt">
		                	<!-- ID -->
		                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
		                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
		                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
		                  		</span>
		                  	</p>
		                  </td>
		                  <!-- NAME -->
								<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										'.$options_label.'
										</span>
									</p>
								</td>
								<!-- PRICE -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- AMOUNT -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- VAT % -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- SUM -->
								<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
							</tr>';

			for ($row=0; $row<count($options[1]); $row++) {
				$html .= '<!-- SIX COLUMNS -->
			               <tr style="mso-yfti-irow:1;height:25.1pt">
			                	<!-- ID -->
			                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
			                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
			                  		'.$options[0][$row].'
			                  		</span>
			                  	</p>
			                  </td>
			                  <!-- NAME -->
									<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$options[1][$row].'
											</span>
										</p>
									</td>
									<!-- PRICE -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$options[2][$row].'
											</span>
										</p>
									</td>
									<!-- AMOUNT -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											1'.$st_label.'
											</span>
										</p>
									</td>
									<!-- VAT % -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$options[3][$row].'%
											</span>
										</p>
									</td>
									<!-- SUM -->
									<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')).'
											</span>
										</p>
									</td>
								</tr>';
			}
		}

		if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
			$html .= '<!-- SIX COLUMNS -->
		               <tr style="mso-yfti-irow:1;height:25.1pt">
		                	<!-- ID -->
		                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
		                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
		                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
		                  		</span>
		                  	</p>
		                  </td>
		                  <!-- NAME -->
								<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										'.$articles_label.'
										</span>
									</p>
								</td>
								<!-- PRICE -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- AMOUNT -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- VAT % -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- SUM -->
								<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
							</tr>';

			for ($row=0; $row<count($articles[1]); $row++) {
				$html .= '<!-- SIX COLUMNS -->
			               <tr style="mso-yfti-irow:1;height:25.1pt">
			                	<!-- ID -->
			                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
			                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
			                  		'.$articles[0][$row].'
			                  		</span>
			                  	</p>
			                  </td>
			                  <!-- NAME -->
									<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$articles[1][$row].'
											</span>
										</p>
									</td>
									<!-- PRICE -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.str_replace('.', ',', $articles[2][$row]).'
											</span>
										</p>
									</td>
									<!-- AMOUNT -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$articles[3][$row].' '.$st_label.'
											</span>
										</p>
									</td>
									<!-- VAT % -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$articles[4][$row].'%
											</span>
										</p>
									</td>
									<!-- SUM -->
									<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')).'
											</span>
										</p>
									</td>
								</tr>';
		    }
		}


		if (!empty($_POST['options']) && is_array($_POST['options'])) {
			for ($row=0; $row<count($options[1]); $row++) {

				if ($options[3][$row] == 25) {
					$excludeVatPrice25 += $options[2][$row];
				}
				if ($options[3][$row] == 18) {
					$excludeVatPrice18 += $options[2][$row];
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
				if ($articles[4][$row] == 18) {
					$excludeVatPrice18 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
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
		$VatPrice18 = $excludeVatPrice18*0.18;
		$VatPrice25 = $excludeVatPrice25*0.25;
		$totalPrice += $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25 + $VatPrice12 + $VatPrice18 + $VatPrice25 + $VatPrice0;
		$totalNetPrice += $excludeVatPrice0 + $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25;

		$totalPriceRounded = round($totalPrice);
		$pennys = ($totalPriceRounded - $totalPrice);

		if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
			$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
			$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');

			$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$tax_label.' (12%)
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.str_replace('.', ',', $VatPrice12).'
									</p>
								</td>
							</tr>';

		}
		if (!empty($excludeVatPrice18) && !empty($VatPrice18)) {
			$excludeVatPrice18 = number_format($excludeVatPrice18, 2, ',', ' ');
			$VatPrice18 = number_format($VatPrice18, 2, ',', ' ');
			$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$tax_label.' (18%)
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.str_replace('.', ',', $VatPrice18).'
									</p>
								</td>
							</tr>';
		}
		if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
			$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
			$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
			$html_sum  .=   '<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$tax_label.' (25%)
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.str_replace('.', ',', $VatPrice25).'
									</p>
								</td>
							</tr>';
		}
if (empty($excludeVatPrice25) && empty($VatPrice25) && empty($excludeVatPrice18) && empty($VatPrice18) && empty($excludeVatPrice12) && empty($VatPrice12)) {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								0,00
							</p>
						</td>
					</tr>';
} 
if (empty($totalPrice)) {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$net_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								0,00
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$estimated.' '.$currency.' '.$to_pay_label.'&nbsp;&nbsp;</strong>0,00<br>('.$not_including_position_price.')
							</p>
						</td>
					</tr>';
} else {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal align=left style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$net_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', number_format($totalNetPrice, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$estimated.' '.$currency.' '.$to_pay_label.'&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')).'<br>('.$not_including_position_price.')
							</p>
						</td>
					</tr>';
}

		if ($totalPriceRounded == 0 && empty($_POST['articles']) && empty($_POST['options'])) {
			$html = '<!-- ONE COLUMN -->
		               <tr style="mso-yfti-irow:1;height:25.1pt">
		                  <td width=100% valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:25.1pt" align=center>
		                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
		                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
		                  		'.$nothing_selected_label.'
		                  		</span>
		                  	</p>
		                  </td>
							</tr>';
			$html_sum = '';
		}

		/*********************************************************************************************/
		/*********************************************************************************************/
		/**********************					MAIL BOOKING TABLE END				  ***********************/
		/*********************************************************************************************/
		/*********************************************************************************************/

			$arranger_message = $_POST['arranger_message'];
			if ($arranger_message == '')
				$arranger_message = $translator->{'No message was given.'};
			
			$exhibitor_commodity = $_POST['commodity'];
			if ($exhibitor_commodity == '')
				$exhibitor_commodity = $translator->{'No commodity was entered.'};


				//Check mail settings and send only if setting is set
				$errors = array();
				$mail_errors = array();
				$email = $fair->get("url") . EMAIL_FROM_DOMAIN;
				$from = array($email => $fair->get("windowtitle"));
				$mailSettings = json_decode($fair->get("mail_settings"));

				if($fair->get('contact_name')) {
					$from = array($email => $fair->get('contact_name'));
				}

				try {
					if ($user->get('contact_email') == '')
						$recipients = array($user->get('email') => $user->get('company'));
					else
						$recipients = array($user->get('contact_email') => $user->get('name'));
					
					$mail_user = new Mail();
					$mail_user->setTemplate('registration_created_receipt');
					$mail_user->setPlainTemplate('registration_created_receipt');
					$mail_user->setFrom($from);
					$mail_user->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
					$mail_user->setRecipients($recipients);
						$mail_user->setMailVar('booking_table', $html);
						$mail_user->setMailVar('booking_sum', $html_sum);
						$mail_user->setMailVar('exhibitor_company_name', $user->get('company'));
						$mail_user->setMailvar('exhibitor_name', $user->get('name'));
						$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
						$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
						$mail_user->setMailVar('event_email', $fair->get('contact_email'));
						$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
						$mail_user->setMailVar('event_website', $fair->get('website'));
						$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
						$mail_user->setMailVar('arranger_message', $arranger_message);
						$mail_user->setMailVar('commodity', $exhibitor_commodity);
						$mail_user->setMailVar('html_categories', $htmlcategoryNames);
						$mail_user->setMailVar('area', $_POST['area']);
					
					if(!$mail_user->send()) {
						$errors[] = $user->get('company');
					}

				} catch(Swift_RfcComplianceException $ex) {
					// Felaktig epost-adress
					$errors[] = $user->get('company');
					$mail_errors[] = $ex->getMessage();

				} catch(Exception $ex) {
					// Okänt fel
					$errors[] = $user->get('company');
					$mail_errors[] = $ex->getMessage();
				}

				if (is_array($mailSettings->recieveRegistration)) {

					if (in_array("0", $mailSettings->recieveRegistration)) {
						try {
							if ($organizer->get('contact_email') == '')
								$recipients = array($organizer->get('email') => $organizer->get('company'));
							else
								$recipients = array($organizer->get('contact_email') => $organizer->get('name'));
							
							$mail_organizer = new Mail();
							$mail_organizer->setTemplate('registration_created_confirm');
							$mail_organizer->setPlainTemplate('registration_created_confirm');
							$mail_organizer->setFrom($from);
							$mail_organizer->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
							$mail_organizer->setRecipients($recipients);
								$mail_organizer->setMailVar('booking_table', $html);
								$mail_organizer->setMailVar('booking_sum', $html_sum);
								$mail_organizer->setMailVar('exhibitor_company_name', $user->get('company'));
								$mail_organizer->setMailvar('exhibitor_name', $user->get('name'));
								$mail_organizer->setMailVar('event_name', $fair->get('windowtitle'));
								$mail_organizer->setMailVar('event_url', BASE_URL . $fair->get('url'));
								$mail_organizer->setMailVar('arranger_message', $arranger_message);
								$mail_organizer->setMailVar('commodity', $exhibitor_commodity);
								$mail_organizer->setMailVar('html_categories', $htmlcategoryNames);
								$mail_organizer->setMailVar('area', $_POST['area']);
							if(!$mail_organizer->send()) {
								$errors[] = $organizer->get('company');
							}

						} catch(Swift_RfcComplianceException $ex) {
							// Felaktig epost-adress
							$errors[] = $organizer->get('company');
							$mail_errors[] = $ex->getMessage();

						} catch(Exception $ex) {
							// Okänt fel
							$errors[] = $organizer->get('company');
							$mail_errors[] = $ex->getMessage();
						}
					}
				}
				if ($errors) {
					$_SESSION['mail_errors'] = $mail_errors;
				}
		}
	}

	exit;
}

if (isset($_POST['reservePosition'])) {

	/// KONTROLLERAD MAILMALL

	if (userLevel() < 1)
		exit;

	$pos = new FairMapPosition();
	$pos->load($_POST['reservePosition'], 'id');
	
	//Delete existing exhibitor if position is booked
	if ($pos->get('status') === 0) {
		$exhibitor = new Exhibitor();
	} else {
		$exhibitor = new Exhibitor();
		$exhibitor->load($pos->get('id'), 'position');
	}

	if (isset($_POST['user']) && userLevel() > 1) {
		$exhibitor->set('user', $_POST['user']);
	} else {
		$exhibitor->set('user', $_SESSION['user_id']);
	}

	$exhibitor->set('fair', $_POST['fair']);
	$exhibitor->set('position', $_POST['reservePosition']);
	$exhibitor->set('commodity', $_POST['commodity']);
	$exhibitor->set('arranger_message', '');
	$exhibitor->set('booking_time', time());
	$exhibitor->set('edit_time', 0);
	$exhibitor->set('clone', 0);
	$exhibitor->set('status', 1);
	$exId = $exhibitor->save();

	$pos->set('status', 1);
	$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));
	$pos->save();

	if ($exhibitor->wasLoaded()) {
		$stmt = $pos->db->prepare("SELECT id FROM exhibitor_invoice WHERE exhibitor = ? AND status = 2");
		$stmt->execute(array($exId));
		$result = $stmt->fetch();
		if ($result > 0) {
			$stmt = $pos->db->prepare("UPDATE exhibitor_invoice SET status = 1 WHERE exhibitor= ? AND id = ?");
			$stmt->execute(array($exId, $result['id']));
		}
		// Remove old categories for this booking
		$stmt = $pos->db->prepare("DELETE FROM exhibitor_category_rel WHERE exhibitor = ?");
		$stmt->execute(array($exId));
		// Remove old options for this booking
		$stmt = $pos->db->prepare("DELETE FROM exhibitor_option_rel WHERE exhibitor = ?");
		$stmt->execute(array($exId));
		// Remove old articles for this booking
		$stmt = $pos->db->prepare("DELETE FROM exhibitor_article_rel WHERE exhibitor = ?");
		$stmt->execute(array($exId));
	}

	$categories = array();
	if (isset($_POST['categories']) && is_array($_POST['categories'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_category_rel` (`exhibitor`, `category`) VALUES (?, ?)");
		foreach ($_POST['categories'] as $cat) {
			$stmt->execute(array($exId, $cat));
			$category = new ExhibitorCategory();
			$category->load($cat, 'id');
			if ($category->wasLoaded()) {
				$categories[] = $category->get('name');
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
	
	$fair = new Fair();
	$fair->load($exhibitor->get('fair'), 'id');
	
	$organizer = new User();
	$organizer->load($fair->get('created_by'), 'id');

	$user = new User();
	$user->load($exhibitor->get('user'), 'id');

	$htmlcategoryNames = implode('<br>', $categories);
	$fairInvoice = new FairInvoice();
	$fairInvoice->load($exhibitor->get('fair'), 'fair');

	/*****************************************************************************************/
	/*****************************************************************************************/
	/************************				PREPARE MAIL START			  *************************/
	/*****************************************************************************************/
	/*****************************************************************************************/

	/*********************************************************************************/
	/*********************************************************************************/
	/********************************       LABELS       *****************************/
	/*********************************************************************************/
	/*********************************************************************************/

	$name_label = $translator->{'Name'};
	$price_label = $translator->{'Price'};
	$amount_label = $translator->{'Amount'};
	$vat_label = $translator->{'Vat'};
	$sum_label = $translator->{'Sum'};
	$booked_space_label = $translator->{'Stand'};
	$options_label = $translator->{'Options'};
	$articles_label = $translator->{'Articles'};
	$tax_label = $translator->{'Tax'};
	$parttotal_label = $translator->{'Subtotal'};
	$net_label = $translator->{'Net'};
	$rounding_label = $translator->{'Rounding'};
	$total_label = $translator->{'total:'};
	$st_label = $translator->{'st'};
	$nothing_selected_label = $translator->{'No articles or options selected.'};

	/*************************************************************/
	/*************************************************************/
	/*****************     PRICES AND AMOUNTS        *************/
	/*************************************************************/
	/*************************************************************/ 

	$totalPrice = 0;
	$totalNetPrice = 0;
	$VatPrice0 = 0;
	$VatPrice12 = 0;
	$VatPrice18 = 0;
	$VatPrice25 = 0;
	$excludeVatPrice0 = 0;
	$excludeVatPrice12 = 0;
	$excludeVatPrice18 = 0;
	$excludeVatPrice25 = 0;
	$currency = $fair->get('currency');
	$position_vat = 0;
	$position_name = $pos->get('name');
	$position_price = $pos->get('price');
	$position_vat = $fairInvoice->get('pos_vat');

		/*********************************************************************************************/
		/*********************************************************************************************/
		/**********************					MAIL BOOKING TABLE START			  ***********************/
		/*********************************************************************************************/
		/*********************************************************************************************/
$html = '<!-- SIX COLUMN HEADERS -->
			<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;padding:10px 0 0 0;">
			 <!-- ID -->
			 <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			     ID
			   </p>
			 </td>
			 <!-- NAME -->
			 <td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			     '.$name_label.'
			   </p>
			 </td>
			 <!-- PRICE -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
			     '.$price_label.'
			   </p>
			 </td>
			 <!-- AMOUNT -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
			     '.$amount_label.'
			   </p>
			 </td>
			 <!-- VAT % -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
			     '.$vat_label.'
			   </p>
			 </td>
			 <!-- SUM -->
			 <td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
			     '.$sum_label.'
			   </p>
			 </td>
			</tr>
			<!-- SPACER ROW -->
			<tr style="mso-yfti-irow:1;height:11.1pt">
			</tr>
			<!-- STAND SPACE ROW LABEL-->
			<tr style="mso-yfti-irow:1;height:25.1pt">
			 	<!-- ID -->
				<td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			<!-- NAME -->
				<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$booked_space_label.'
						</span>
					</p>
				</td>
				<!-- PRICE -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- AMOUNT -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- VAT % -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- SUM -->
				<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			</tr>
			<!-- STAND SPACE ROW INFO -->
			<tr style="mso-yfti-irow:1;height:25.1pt">
			 	<!-- ID -->
				<td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			<!-- NAME -->
				<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_name.'
						</span>
					</p>
				</td>
				<!-- PRICE -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_price.'
						</span>
					</p>
				</td>
				<!-- AMOUNT -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						1'.$st_label.'
						</span>
					</p>
				</td>
				<!-- VAT % -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_vat.'%
						</span>
					</p>
				</td>
				<!-- SUM -->
				<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.number_format($position_price, 2, ',', ' ').'
						</span>
					</p>
				</td>
			</tr>';

$html_sum = '<!-- TWO COLUMN VAT PRICE AND NET SUMMATION -->
				<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;">
					<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
						<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						</p>
					</td>
					<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
						<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						</p>
					</td>
				</tr>';

if ($position_vat == 25) {
	$excludeVatPrice25 += $position_price;
} else if ($position_vat == 18) {
	$excludeVatPrice18 += $position_price;
} else {
	$excludeVatPrice0 += $position_price;
}

if (!empty($_POST['options']) && is_array($_POST['options'])) {
	$html .= '<!-- SIX COLUMNS -->
               <tr style="mso-yfti-irow:1;height:25.1pt">
                	<!-- ID -->
                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
                  		</span>
                  	</p>
                  </td>
                  <!-- NAME -->
						<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.$options_label.'
								</span>
							</p>
						</td>
						<!-- PRICE -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- AMOUNT -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- VAT % -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- SUM -->
						<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
					</tr>';

	for ($row=0; $row<count($options[1]); $row++) {
		$html .= '<!-- SIX COLUMNS -->
	               <tr style="mso-yfti-irow:1;height:25.1pt">
	                	<!-- ID -->
	                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
	                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
	                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
	                  		'.$options[0][$row].'
	                  		</span>
	                  	</p>
	                  </td>
	                  <!-- NAME -->
							<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[1][$row].'
									</span>
								</p>
							</td>
							<!-- PRICE -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[2][$row].'
									</span>
								</p>
							</td>
							<!-- AMOUNT -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									1'.$st_label.'
									</span>
								</p>
							</td>
							<!-- VAT % -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[3][$row].'%
									</span>
								</p>
							</td>
							<!-- SUM -->
							<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')).'
									</span>
								</p>
							</td>
						</tr>';
	}
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	$html .= '<!-- SIX COLUMNS -->
               <tr style="mso-yfti-irow:1;height:25.1pt">
                	<!-- ID -->
                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
                  		</span>
                  	</p>
                  </td>
                  <!-- NAME -->
						<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.$articles_label.'
								</span>
							</p>
						</td>
						<!-- PRICE -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- AMOUNT -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- VAT % -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- SUM -->
						<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
					</tr>';

	for ($row=0; $row<count($articles[1]); $row++) {
		$html .= '<!-- SIX COLUMNS -->
	               <tr style="mso-yfti-irow:1;height:25.1pt">
	                	<!-- ID -->
	                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
	                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
	                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
	                  		'.$articles[0][$row].'
	                  		</span>
	                  	</p>
	                  </td>
	                  <!-- NAME -->
							<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[1][$row].'
									</span>
								</p>
							</td>
							<!-- PRICE -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', $articles[2][$row]).'
									</span>
								</p>
							</td>
							<!-- AMOUNT -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[3][$row].' '.$st_label.'
									</span>
								</p>
							</td>
							<!-- VAT % -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[4][$row].'%
									</span>
								</p>
							</td>
							<!-- SUM -->
							<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')).'
									</span>
								</p>
							</td>
						</tr>';
    }
}


if (!empty($_POST['options']) && is_array($_POST['options'])) {
	for ($row=0; $row<count($options[1]); $row++) {

		if ($options[3][$row] == 25) {
			$excludeVatPrice25 += $options[2][$row];
		}
		if ($options[3][$row] == 18) {
			$excludeVatPrice18 += $options[2][$row];
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
		if ($articles[4][$row] == 18) {
			$excludeVatPrice18 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
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
$VatPrice18 = $excludeVatPrice18*0.18;
$VatPrice25 = $excludeVatPrice25*0.25;
$totalPrice += $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25 + $VatPrice12 + $VatPrice18 + $VatPrice25 + $VatPrice0;
$totalNetPrice += $excludeVatPrice0 + $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25;

$totalPriceRounded = round($totalPrice);
$pennys = ($totalPriceRounded - $totalPrice);

if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
	$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
	$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');

	$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (12%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice12).'
							</p>
						</td>
					</tr>';

}
if (!empty($excludeVatPrice18) && !empty($VatPrice18)) {
	$excludeVatPrice18 = number_format($excludeVatPrice18, 2, ',', ' ');
	$VatPrice18 = number_format($VatPrice18, 2, ',', ' ');
	$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (18%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice18).'
							</p>
						</td>
					</tr>';
}
if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
	$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
	$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
	$html_sum  .=   '<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (25%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice25).'
							</p>
						</td>
					</tr>';
}
if (empty($excludeVatPrice25) && empty($VatPrice25) && empty($excludeVatPrice18) && empty($VatPrice18) && empty($excludeVatPrice12) && empty($VatPrice12)) {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								0,00
							</p>
						</td>
					</tr>';
} 
if (empty($totalPrice)) {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$net_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								0,00
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$currency.' '.$total_label.'&nbsp;&nbsp;</strong>0,00
							</p>
						</td>
					</tr>';
} else {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal align=left style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$net_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', number_format($totalNetPrice, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$currency.' '.$total_label.'&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')).'
							</p>
						</td>
					</tr>';
}

/*********************************************************************************************/
/*********************************************************************************************/
/**********************					MAIL BOOKING TABLE END				  ***********************/
/*********************************************************************************************/
/*********************************************************************************************/
	
	$position_information = $pos->get('information');
	if ($position_information == '')
		$position_information = $translator->{'None specified.'};

	$position_area = $pos->get('area');
	if ($position_area == '')
		$position_area = $translator->{'None specified.'};

/*	$arranger_message = $_POST['arranger_message'];
	if ($arranger_message == '')
		$arranger_message = $translator->{'No message was given.'};
*/	
	$exhibitor_commodity = $_POST['commodity'];
	if ($exhibitor_commodity == '')
		$exhibitor_commodity = $translator->{'No commodity was entered.'};


	//Check mail settings and send only if setting is set
	if ($fair->wasLoaded()) {
		$mailSettings = json_decode($fair->get("mail_settings"));
		if (isset($mailSettings->bookingCreated) && is_array($mailSettings->bookingCreated)) {
			$errors = array();
			$mail_errors = array();
			$email = $fair->get("url") . EMAIL_FROM_DOMAIN;
			$from = array($email => $fair->get("windowtitle"));

			if($fair->get('contact_name')) {
				$from = array($email => $fair->get('contact_name'));
			}

			if (in_array("0", $mailSettings->bookingCreated)) {
				try {
					if ($organizer->get('contact_email') == '')
						$recipients = array($organizer->get('email') => $organizer->get('company'));
					else
						$recipients = array($organizer->get('contact_email') => $organizer->get('name'));

					$mail_organizer = new Mail();
					$mail_organizer->setTemplate('reservation_created_confirm');
					$mail_organizer->setPlainTemplate('reservation_created_confirm');
					$mail_organizer->setFrom($from);
					$mail_organizer->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
					$mail_organizer->setRecipients($recipients);
						$mail_organizer->setMailVar('booking_table', $html);
						$mail_organizer->setMailVar('booking_sum', $html_sum);
						$mail_organizer->setMailVar('exhibitor_company_name', $user->get('company'));
						$mail_organizer->setMailvar('exhibitor_name', $user->get('name'));
						$mail_organizer->setMailVar('event_name', $fair->get('windowtitle'));
						$mail_organizer->setMailVar('event_url', BASE_URL . $fair->get('url'));
						$mail_organizer->setMailVar('position_name', $pos->get('name'));
						$mail_organizer->setMailVar('position_information', $position_information);
						$mail_organizer->setMailVar('position_area', $position_area);
						$mail_organizer->setMailVar('arranger_message', '');
						$mail_organizer->setMailVar('commodity', $exhibitor_commodity);
						$mail_organizer->setMailVar('html_categories', $htmlcategoryNames);
						$mail_organizer->setMailVar('expirationdate', $_POST['expires']);
				
						if(!$mail_organizer->send()) {
							$errors[] = $organizer->get('company');
						}

					} catch(Swift_RfcComplianceException $ex) {
						// Felaktig epost-adress
						$errors[] = $organizer->get('company');
						$mail_errors[] = $ex->getMessage();

					} catch(Exception $ex) {
						// Okänt fel
						$errors[] = $organizer->get('company');
						$mail_errors[] = $ex->getMessage();
					}
				}
			if (in_array("1", $mailSettings->bookingCreated)) {
				try {
					if ($user->get('contact_email') == '')
						$recipients = array($user->get('email') => $user->get('company'));
					else
						$recipients = array($user->get('contact_email') => $user->get('name'));

					$mail_user = new Mail();
					$mail_user->setTemplate('reservation_created_receipt');
					$mail_user->setPlainTemplate('reservation_created_receipt');
					$mail_user->setFrom($from);
					$mail_user->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
					$mail_user->setRecipients($recipients);
						$mail_user->setMailVar('booking_table', $html);
						$mail_user->setMailVar('booking_sum', $html_sum);
						$mail_user->setMailVar('exhibitor_company_name', $user->get('company'));
						$mail_user->setMailvar('exhibitor_name', $user->get('name'));
						$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
						$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
						$mail_user->setMailVar('event_email', $fair->get('contact_email'));
						$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
						$mail_user->setMailVar('event_website', $fair->get('website'));
						$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
						$mail_user->setMailVar('position_name', $pos->get('name'));
						$mail_user->setMailVar('position_information', $position_information);
						$mail_user->setMailVar('position_area', $position_area);
						$mail_user->setMailVar('arranger_message', '');
						$mail_user->setMailVar('commodity', $exhibitor_commodity);
						$mail_user->setMailVar('html_categories', $htmlcategoryNames);
						$mail_user->setMailVar('expirationdate', $_POST['expires']);

					if(!$mail_user->send()) {
						$errors[] = $user->get('company');
					}

				} catch(Swift_RfcComplianceException $ex) {
					// Felaktig epost-adress
					$errors[] = $user->get('company');
					$mail_errors[] = $ex->getMessage();

				} catch(Exception $ex) {
					// Okänt fel
					$errors[] = $user->get('company');
					$mail_errors[] = $ex->getMessage();
				}
			}
			if ($errors) {
				$_SESSION['mail_errors'] = $mail_errors;
			}
		}
	}
	exit;
}

if (isset($_POST['editBooking'])) {

	/// KONTROLLERAD MAILMALL

	if (userLevel() < 1)
		exit;

	$pos = new FairMapPosition();
	$pos->load($_POST['editBooking'], 'id');

	$exhibitor = new Exhibitor;
	$exhibitor->load($_POST['exhibitor_id'], 'id');
	if (!$exhibitor->wasLoaded()) {
		die('exhibitor not found');
	}

	if (isset($_POST['user']) && userLevel() > 1) {
		$exhibitor->set('user', $_POST['user']);
	} else {
		$exhibitor->set('user', $_SESSION['user_id']);
	}

	$exhibitor->set('commodity', $_POST['commodity']);
	//$exhibitor->set('arranger_message', $_POST['arranger_message']);
	$exhibitor->set('clone', 0);
	$exId = $exhibitor->save();

	// Remove old categories for this booking
	$stmt = $pos->db->prepare("DELETE FROM exhibitor_category_rel WHERE exhibitor = ?");
	$stmt->execute(array($exId));
	// Remove old options for this booking
	$stmt = $pos->db->prepare("DELETE FROM exhibitor_option_rel WHERE exhibitor = ?");
	$stmt->execute(array($exId));
	// Remove old articles for this booking
	$stmt = $pos->db->prepare("DELETE FROM exhibitor_article_rel WHERE exhibitor = ?");
	$stmt->execute(array($exId));

	$categories = array();

	if (isset($_POST['categories']) && is_array($_POST['categories'])) {
		$stmt = $pos->db->prepare("INSERT INTO `exhibitor_category_rel` (`exhibitor`, `category`) VALUES (?, ?)");
		foreach ($_POST['categories'] as $cat) {
			$stmt->execute(array($exId, $cat));
			$category = new ExhibitorCategory();
			$category->load($cat, 'id');
			if ($category->wasLoaded()) {
				$categories[] = $category->get('name');
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
	
	// If this is a reservation (status is 1), then also set the expiry date
	if (isset($_POST['expires'])) {
		$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));
		$pos->save();
		$mail_type = 'reservation';
	} else {
		$mail_type = 'booking';
	}

	$fair = new Fair();
	$fair->load($exhibitor->get('fair'), 'id');
	
	$organizer = new User();
	$organizer->load($fair->get('created_by'), 'id');

	$user = new User();
	$user->load($exhibitor->get('user'), 'id');

	$htmlcategoryNames = implode('<br>', $categories);
	$fairInvoice = new FairInvoice();
	$fairInvoice->load($exhibitor->get('fair'), 'fair');

	/*****************************************************************************************/
	/*****************************************************************************************/
	/************************				PREPARE MAIL START			  *************************/
	/*****************************************************************************************/
	/*****************************************************************************************/

	/*********************************************************************************/
	/*********************************************************************************/
	/********************************       LABELS       *****************************/
	/*********************************************************************************/
	/*********************************************************************************/

	$name_label = $translator->{'Name'};
	$price_label = $translator->{'Price'};
	$amount_label = $translator->{'Amount'};
	$vat_label = $translator->{'Vat'};
	$sum_label = $translator->{'Sum'};
	$booked_space_label = $translator->{'Stand'};
	$options_label = $translator->{'Options'};
	$articles_label = $translator->{'Articles'};
	$tax_label = $translator->{'Tax'};
	$parttotal_label = $translator->{'Subtotal'};
	$net_label = $translator->{'Net'};
	$rounding_label = $translator->{'Rounding'};
	$total_label = $translator->{'total:'};
	$st_label = $translator->{'st'};
	$nothing_selected_label = $translator->{'No articles or options selected.'};

	/*************************************************************/
	/*************************************************************/
	/*****************     PRICES AND AMOUNTS        *************/
	/*************************************************************/
	/*************************************************************/ 

	$totalPrice = 0;
	$totalNetPrice = 0;
	$VatPrice0 = 0;
	$VatPrice12 = 0;
	$VatPrice18 = 0;
	$VatPrice25 = 0;
	$excludeVatPrice0 = 0;
	$excludeVatPrice12 = 0;
	$excludeVatPrice18 = 0;
	$excludeVatPrice25 = 0;
	$currency = $fair->get('currency');
	$position_vat = 0;
	$position_name = $pos->get('name');
	$position_price = $pos->get('price');
	$position_vat = $fairInvoice->get('pos_vat');

		/*********************************************************************************************/
		/*********************************************************************************************/
		/**********************					MAIL BOOKING TABLE START			  ***********************/
		/*********************************************************************************************/
		/*********************************************************************************************/
$html = '<!-- SIX COLUMN HEADERS -->
			<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;padding:10px 0 0 0;">
			 <!-- ID -->
			 <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			     ID
			   </p>
			 </td>
			 <!-- NAME -->
			 <td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			     '.$name_label.'
			   </p>
			 </td>
			 <!-- PRICE -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
			     '.$price_label.'
			   </p>
			 </td>
			 <!-- AMOUNT -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
			     '.$amount_label.'
			   </p>
			 </td>
			 <!-- VAT % -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
			     '.$vat_label.'
			   </p>
			 </td>
			 <!-- SUM -->
			 <td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
			     '.$sum_label.'
			   </p>
			 </td>
			</tr>
			<!-- SPACER ROW -->
			<tr style="mso-yfti-irow:1;height:11.1pt">
			</tr>
			<!-- STAND SPACE ROW LABEL-->
			<tr style="mso-yfti-irow:1;height:25.1pt">
			 	<!-- ID -->
				<td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			<!-- NAME -->
				<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$booked_space_label.'
						</span>
					</p>
				</td>
				<!-- PRICE -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- AMOUNT -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- VAT % -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- SUM -->
				<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			</tr>
			<!-- STAND SPACE ROW INFO -->
			<tr style="mso-yfti-irow:1;height:25.1pt">
			 	<!-- ID -->
				<td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			<!-- NAME -->
				<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_name.'
						</span>
					</p>
				</td>
				<!-- PRICE -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_price.'
						</span>
					</p>
				</td>
				<!-- AMOUNT -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						1'.$st_label.'
						</span>
					</p>
				</td>
				<!-- VAT % -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_vat.'%
						</span>
					</p>
				</td>
				<!-- SUM -->
				<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.number_format($position_price, 2, ',', ' ').'
						</span>
					</p>
				</td>
			</tr>';

$html_sum = '<!-- TWO COLUMN VAT PRICE AND NET SUMMATION -->
				<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;">
					<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
						<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						</p>
					</td>
					<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
						<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						</p>
					</td>
				</tr>';

if ($position_vat == 25) {
	$excludeVatPrice25 += $position_price;
} else if ($position_vat == 18) {
	$excludeVatPrice18 += $position_price;
} else {
	$excludeVatPrice0 += $position_price;
}

if (!empty($_POST['options']) && is_array($_POST['options'])) {
	$html .= '<!-- SIX COLUMNS -->
               <tr style="mso-yfti-irow:1;height:25.1pt">
                	<!-- ID -->
                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
                  		</span>
                  	</p>
                  </td>
                  <!-- NAME -->
						<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.$options_label.'
								</span>
							</p>
						</td>
						<!-- PRICE -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- AMOUNT -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- VAT % -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- SUM -->
						<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
					</tr>';

	for ($row=0; $row<count($options[1]); $row++) {
		$html .= '<!-- SIX COLUMNS -->
	               <tr style="mso-yfti-irow:1;height:25.1pt">
	                	<!-- ID -->
	                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
	                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
	                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
	                  		'.$options[0][$row].'
	                  		</span>
	                  	</p>
	                  </td>
	                  <!-- NAME -->
							<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[1][$row].'
									</span>
								</p>
							</td>
							<!-- PRICE -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[2][$row].'
									</span>
								</p>
							</td>
							<!-- AMOUNT -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									1'.$st_label.'
									</span>
								</p>
							</td>
							<!-- VAT % -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[3][$row].'%
									</span>
								</p>
							</td>
							<!-- SUM -->
							<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')).'
									</span>
								</p>
							</td>
						</tr>';
	}
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	$html .= '<!-- SIX COLUMNS -->
               <tr style="mso-yfti-irow:1;height:25.1pt">
                	<!-- ID -->
                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
                  		</span>
                  	</p>
                  </td>
                  <!-- NAME -->
						<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.$articles_label.'
								</span>
							</p>
						</td>
						<!-- PRICE -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- AMOUNT -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- VAT % -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- SUM -->
						<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
					</tr>';

	for ($row=0; $row<count($articles[1]); $row++) {
		$html .= '<!-- SIX COLUMNS -->
	               <tr style="mso-yfti-irow:1;height:25.1pt">
	                	<!-- ID -->
	                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
	                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
	                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
	                  		'.$articles[0][$row].'
	                  		</span>
	                  	</p>
	                  </td>
	                  <!-- NAME -->
							<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[1][$row].'
									</span>
								</p>
							</td>
							<!-- PRICE -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', $articles[2][$row]).'
									</span>
								</p>
							</td>
							<!-- AMOUNT -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[3][$row].' '.$st_label.'
									</span>
								</p>
							</td>
							<!-- VAT % -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[4][$row].'%
									</span>
								</p>
							</td>
							<!-- SUM -->
							<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')).'
									</span>
								</p>
							</td>
						</tr>';
    }
}


if (!empty($_POST['options']) && is_array($_POST['options'])) {
	for ($row=0; $row<count($options[1]); $row++) {

		if ($options[3][$row] == 25) {
			$excludeVatPrice25 += $options[2][$row];
		}
		if ($options[3][$row] == 18) {
			$excludeVatPrice18 += $options[2][$row];
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
		if ($articles[4][$row] == 18) {
			$excludeVatPrice18 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
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
$VatPrice18 = $excludeVatPrice18*0.18;
$VatPrice25 = $excludeVatPrice25*0.25;
$totalPrice += $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25 + $VatPrice12 + $VatPrice18 + $VatPrice25 + $VatPrice0;
$totalNetPrice += $excludeVatPrice0 + $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25;

$totalPriceRounded = round($totalPrice);
$pennys = ($totalPriceRounded - $totalPrice);

if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
	$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
	$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');

	$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (12%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice12).'
							</p>
						</td>
					</tr>';

}
if (!empty($excludeVatPrice18) && !empty($VatPrice18)) {
	$excludeVatPrice18 = number_format($excludeVatPrice18, 2, ',', ' ');
	$VatPrice18 = number_format($VatPrice18, 2, ',', ' ');
	$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (18%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice18).'
							</p>
						</td>
					</tr>';
}
if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
	$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
	$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
	$html_sum  .=   '<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (25%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice25).'
							</p>
						</td>
					</tr>';
}
if (empty($excludeVatPrice25) && empty($VatPrice25) && empty($excludeVatPrice18) && empty($VatPrice18) && empty($excludeVatPrice12) && empty($VatPrice12)) {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								0,00
							</p>
						</td>
					</tr>';
} 
if (empty($totalPrice)) {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$net_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								0,00
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$currency.' '.$total_label.'&nbsp;&nbsp;</strong>0,00
							</p>
						</td>
					</tr>';
} else {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal align=left style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$net_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', number_format($totalNetPrice, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$currency.' '.$total_label.'&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')).'
							</p>
						</td>
					</tr>';
}
/*********************************************************************************************/
/*********************************************************************************************/
/**********************					MAIL BOOKING TABLE END				  ***********************/
/*********************************************************************************************/
/*********************************************************************************************/
	
	$position_information = $pos->get('information');
	if ($position_information == '')
		$position_information = $translator->{'None specified.'};

	$position_area = $pos->get('area');
	if ($position_area == '')
		$position_area = $translator->{'None specified.'};

	$arranger_message = $exhibitor->get('arranger_message');
	if ($arranger_message == '')
		$arranger_message = $translator->{'No message was given.'};
	
	$exhibitor_commodity = $_POST['commodity'];
	if ($exhibitor_commodity == '')
		$exhibitor_commodity = $translator->{'No commodity was entered.'};


	//Check mail settings and send only if setting is set
	if ($fair->wasLoaded()) {
		$mailSettings = json_decode($fair->get("mail_settings"));
		if (isset($mailSettings->bookingEdited) && is_array($mailSettings->bookingEdited)) {
			$errors = array();
			$mail_errors = array();
			$email = $fair->get("url") . EMAIL_FROM_DOMAIN;
			$from = array($email => $fair->get("windowtitle"));

			if($fair->get('contact_name')) {
				$from = array($email => $fair->get('contact_name'));
			}

			if (in_array("0", $mailSettings->bookingEdited)) {
				try {
					if ($organizer->get('contact_email') == '')
						$recipients = array($organizer->get('email') => $organizer->get('company'));
					else
						$recipients = array($organizer->get('contact_email') => $organizer->get('name'));
					
					$mail_organizer = new Mail();
					$mail_organizer->setTemplate($mail_type . '_edited_confirm');
					$mail_organizer->setPlainTemplate($mail_type . '_edited_confirm');
					$mail_organizer->setFrom($from);
					$mail_organizer->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
					$mail_organizer->setRecipients($recipients);
						$mail_organizer->setMailVar('booking_table', $html);
						$mail_organizer->setMailVar('booking_sum', $html_sum);
						$mail_organizer->setMailVar('exhibitor_company_name', $user->get('company'));
						$mail_organizer->setMailvar('exhibitor_name', $user->get('name'));
						$mail_organizer->setMailVar('event_name', $fair->get('windowtitle'));
						$mail_organizer->setMailVar('event_url', BASE_URL . $fair->get('url'));
						$mail_organizer->setMailVar('position_name', $pos->get('name'));
						$mail_organizer->setMailVar('position_information', $position_information);
						$mail_organizer->setMailVar('position_area', $position_area);
						$mail_organizer->setMailVar('arranger_message', $arranger_message);
						$mail_organizer->setMailVar('commodity', $exhibitor_commodity);
						$mail_organizer->setMailVar('html_categories', $htmlcategoryNames);

						if ($mail_type == 'reservation') {
							$mail_organizer->setMailVar('expirationdate', $_POST['expires']);
						}

						if(!$mail_organizer->send()) {
							$errors[] = $organizer->get('company');
						}

					} catch(Swift_RfcComplianceException $ex) {
						// Felaktig epost-adress
						$errors[] = $organizer->get('company');
						$mail_errors[] = $ex->getMessage();

					} catch(Exception $ex) {
						// Okänt fel
						$errors[] = $organizer->get('company');
						$mail_errors[] = $ex->getMessage();
					}
				}
			if (in_array("1", $mailSettings->bookingEdited)) {
				try {
					if ($user->get('contact_email') == '')
						$recipients = array($user->get('email') => $user->get('company'));
					else
						$recipients = array($user->get('contact_email') => $user->get('name'));
					
					$mail_user = new Mail();
					$mail_user->setTemplate($mail_type . '_edited_receipt');
					$mail_user->setPlainTemplate($mail_type . '_edited_receipt');
					$mail_user->setFrom($from);
					$mail_user->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
					$mail_user->setRecipients($recipients);
						$mail_user->setMailVar('booking_table', $html);
						$mail_user->setMailVar('booking_sum', $html_sum);
						$mail_user->setMailVar('exhibitor_company_name', $user->get('company'));
						$mail_user->setMailvar('exhibitor_name', $user->get('name'));
						$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
						$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
						$mail_user->setMailVar('event_email', $fair->get('contact_email'));
						$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
						$mail_user->setMailVar('event_website', $fair->get('website'));
						$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
						$mail_user->setMailVar('position_name', $pos->get('name'));
						$mail_user->setMailVar('position_information', $position_information);
						$mail_user->setMailVar('position_area', $position_area);
						$mail_user->setMailVar('arranger_message', $arranger_message);
						$mail_user->setMailVar('commodity', $exhibitor_commodity);
						$mail_user->setMailVar('html_categories', $htmlcategoryNames);
					
						if ($mail_type == 'reservation') {
							$mail_user->setMailVar('expirationdate', $_POST['expires']);
						}

						if(!$mail_user->send()) {
							$errors[] = $user->get('company');
						}

				} catch(Swift_RfcComplianceException $ex) {
					// Felaktig epost-adress
					$errors[] = $user->get('company');
					$mail_errors[] = $ex->getMessage();

				} catch(Exception $ex) {
					// Okänt fel
					$errors[] = $user->get('company');
					$mail_errors[] = $ex->getMessage();
				}
			}
			if ($errors) {
				$_SESSION['mail_errors'] = $mail_errors;
			}
		}
	}
	exit;
}

if (isset($_POST['preliminary'])) {
	
	/// KONTROLLERAD MAILMALL

	if (userLevel() == 1) {

		$pos = new FairMapPosition();
		$pos->load($_POST['preliminary'], 'id');	

		$map = new FairMap();
		$map->load($pos->get('map'), 'id');

		$fair = new Fair();
		$fair->load($map->get('fair'), 'id');

		$user = new User();
		$user->load($_SESSION['user_id'], 'id');

		$organizer = new User();
		$organizer->load($fair->get('created_by'), 'id');

		if ($fair->wasLoaded() && $user->wasLoaded()) {
				$category_ids = '';
				$categories = array();
				if (isset($_POST['categories']) && is_array($_POST['categories'])) {
					foreach ($_POST['categories'] as $cat) {
						$category = new ExhibitorCategory();
						$category->load($cat, 'id');
						if ($category->wasLoaded()) {
							$categories[] = $category->get('name');
						}
					}
					$category_ids = implode('|', $_POST['categories']);
				}

				$option_ids = '';
				$options = array();
				if (isset($_POST['options']) && is_array($_POST['options'])) {
					foreach ($_POST['options'] as $opt) {
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
					$option_ids = implode('|', $_POST['options']);
				}

				$article_ids = '';
				$article_amounts = '';
				$articles = array();
				if (!empty($_POST['articles']) && !empty($_POST['artamount'])) {
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
					$article_ids = implode('|', $_POST['articles']);
					$article_amounts = implode('|', $_POST['artamount']);
				}

				$pb = new PreliminaryBooking();
				$pb->set('user', $user->get('id'));
				$pb->set('fair', $fair->get('id'));
				$pb->set('position', $pos->get('id'));
				$pb->set('categories', $category_ids);
				$pb->set('options', $option_ids);
				$pb->set('articles', $article_ids);
				$pb->set('amount', $article_amounts);
				$pb->set('commodity', $_POST['commodity']);
				$pb->set('arranger_message', $_POST['arranger_message']);
				$pb->set('booking_time', time());
				$pb->save();

				$htmlcategoryNames = implode('<br>', $categories);
				$fairInvoice = new FairInvoice();
				$fairInvoice->load($pb->get('fair'), 'fair');

				/*****************************************************************************************/
				/*****************************************************************************************/
				/************************				PREPARE MAIL START			  *************************/
				/*****************************************************************************************/
				/*****************************************************************************************/

				/*********************************************************************************/
				/*********************************************************************************/
				/********************************       LABELS       *****************************/
				/*********************************************************************************/
				/*********************************************************************************/

				$name_label = $translator->{'Name'};
				$price_label = $translator->{'Price'};
				$amount_label = $translator->{'Amount'};
				$vat_label = $translator->{'Vat'};
				$sum_label = $translator->{'Sum'};
				$booked_space_label = $translator->{'Stand'};
				$options_label = $translator->{'Options'};
				$articles_label = $translator->{'Articles'};
				$tax_label = $translator->{'Tax'};
				$parttotal_label = $translator->{'Subtotal'};
				$net_label = $translator->{'Net'};
				$rounding_label = $translator->{'Rounding'};
				$total_label = $translator->{'total:'};
				$estimated_label = $translator->{'Estimated'};
				$st_label = $translator->{'st'};
				$nothing_selected_label = $translator->{'No articles or options selected.'};

				/*************************************************************/
				/*************************************************************/
				/*****************     PRICES AND AMOUNTS        *************/
				/*************************************************************/
				/*************************************************************/ 

				$totalPrice = 0;
				$totalNetPrice = 0;
				$VatPrice0 = 0;
				$VatPrice12 = 0;
				$VatPrice18 = 0;
				$VatPrice25 = 0;
				$excludeVatPrice0 = 0;
				$excludeVatPrice12 = 0;
				$excludeVatPrice18 = 0;
				$excludeVatPrice25 = 0;
				$currency = $fair->get('currency');
				$position_vat = 0;
				$position_name = $pos->get('name');
				$position_price = $pos->get('price');
				$position_vat = $fairInvoice->get('pos_vat');

				/*********************************************************************************************/
				/*********************************************************************************************/
				/**********************					MAIL BOOKING TABLE START			  ***********************/
				/*********************************************************************************************/
				/*********************************************************************************************/
		$html = '<!-- SIX COLUMN HEADERS -->
					<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;padding:10px 0 0 0;">
					 <!-- ID -->
					 <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
					     ID
					   </p>
					 </td>
					 <!-- NAME -->
					 <td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
					     '.$name_label.'
					   </p>
					 </td>
					 <!-- PRICE -->
					 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
					     '.$price_label.'
					   </p>
					 </td>
					 <!-- AMOUNT -->
					 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
					     '.$amount_label.'
					   </p>
					 </td>
					 <!-- VAT % -->
					 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
					     '.$vat_label.'
					   </p>
					 </td>
					 <!-- SUM -->
					 <td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
					     '.$sum_label.'
					   </p>
					 </td>
					</tr>
					<!-- SPACER ROW -->
					<tr style="mso-yfti-irow:1;height:11.1pt">
					</tr>
					<!-- STAND SPACE ROW LABEL-->
					<tr style="mso-yfti-irow:1;height:25.1pt">
					 	<!-- ID -->
						<td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
					<!-- NAME -->
						<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.$booked_space_label.'
								</span>
							</p>
						</td>
						<!-- PRICE -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- AMOUNT -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- VAT % -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- SUM -->
						<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
					</tr>
					<!-- STAND SPACE ROW INFO -->
					<tr style="mso-yfti-irow:1;height:25.1pt">
					 	<!-- ID -->
						<td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
					<!-- NAME -->
						<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.$position_name.'
								</span>
							</p>
						</td>
						<!-- PRICE -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.$position_price.'
								</span>
							</p>
						</td>
						<!-- AMOUNT -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								1'.$st_label.'
								</span>
							</p>
						</td>
						<!-- VAT % -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.$position_vat.'%
								</span>
							</p>
						</td>
						<!-- SUM -->
						<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.number_format($position_price, 2, ',', ' ').'
								</span>
							</p>
						</td>
					</tr>';

		$html_sum = '<!-- TWO COLUMN VAT PRICE AND NET SUMMATION -->
						<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;">
							<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
								<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								</p>
							</td>
							<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								</p>
							</td>
						</tr>';

		if ($position_vat == 25) {
			$excludeVatPrice25 += $position_price;
		} else if ($position_vat == 18) {
			$excludeVatPrice18 += $position_price;
		} else {
			$excludeVatPrice0 += $position_price;
		}

		if (!empty($_POST['options']) && is_array($_POST['options'])) {
			$html .= '<!-- SIX COLUMNS -->
		               <tr style="mso-yfti-irow:1;height:25.1pt">
		                	<!-- ID -->
		                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
		                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
		                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
		                  		</span>
		                  	</p>
		                  </td>
		                  <!-- NAME -->
								<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										'.$options_label.'
										</span>
									</p>
								</td>
								<!-- PRICE -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- AMOUNT -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- VAT % -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- SUM -->
								<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
							</tr>';

			for ($row=0; $row<count($options[1]); $row++) {
				$html .= '<!-- SIX COLUMNS -->
			               <tr style="mso-yfti-irow:1;height:25.1pt">
			                	<!-- ID -->
			                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
			                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
			                  		'.$options[0][$row].'
			                  		</span>
			                  	</p>
			                  </td>
			                  <!-- NAME -->
									<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$options[1][$row].'
											</span>
										</p>
									</td>
									<!-- PRICE -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$options[2][$row].'
											</span>
										</p>
									</td>
									<!-- AMOUNT -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											1'.$st_label.'
											</span>
										</p>
									</td>
									<!-- VAT % -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$options[3][$row].'%
											</span>
										</p>
									</td>
									<!-- SUM -->
									<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')).'
											</span>
										</p>
									</td>
								</tr>';
			}
		}

		if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
			$html .= '<!-- SIX COLUMNS -->
		               <tr style="mso-yfti-irow:1;height:25.1pt">
		                	<!-- ID -->
		                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
		                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
		                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
		                  		</span>
		                  	</p>
		                  </td>
		                  <!-- NAME -->
								<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										'.$articles_label.'
										</span>
									</p>
								</td>
								<!-- PRICE -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- AMOUNT -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- VAT % -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- SUM -->
								<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
							</tr>';

			for ($row=0; $row<count($articles[1]); $row++) {
				$html .= '<!-- SIX COLUMNS -->
			               <tr style="mso-yfti-irow:1;height:25.1pt">
			                	<!-- ID -->
			                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
			                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
			                  		'.$articles[0][$row].'
			                  		</span>
			                  	</p>
			                  </td>
			                  <!-- NAME -->
									<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$articles[1][$row].'
											</span>
										</p>
									</td>
									<!-- PRICE -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.str_replace('.', ',', $articles[2][$row]).'
											</span>
										</p>
									</td>
									<!-- AMOUNT -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$articles[3][$row].' '.$st_label.'
											</span>
										</p>
									</td>
									<!-- VAT % -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$articles[4][$row].'%
											</span>
										</p>
									</td>
									<!-- SUM -->
									<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')).'
											</span>
										</p>
									</td>
								</tr>';
		    }
		}


		if (!empty($_POST['options']) && is_array($_POST['options'])) {
			for ($row=0; $row<count($options[1]); $row++) {

				if ($options[3][$row] == 25) {
					$excludeVatPrice25 += $options[2][$row];
				}
				if ($options[3][$row] == 18) {
					$excludeVatPrice18 += $options[2][$row];
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
				if ($articles[4][$row] == 18) {
					$excludeVatPrice18 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
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
		$VatPrice18 = $excludeVatPrice18*0.18;
		$VatPrice25 = $excludeVatPrice25*0.25;
		$totalPrice += $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25 + $VatPrice12 + $VatPrice18 + $VatPrice25 + $VatPrice0;
		$totalNetPrice += $excludeVatPrice0 + $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25;

		$totalPriceRounded = round($totalPrice);
		$pennys = ($totalPriceRounded - $totalPrice);

		if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
			$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
			$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');

			$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$tax_label.' (12%)
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.str_replace('.', ',', $VatPrice12).'
									</p>
								</td>
							</tr>';

		}
		if (!empty($excludeVatPrice18) && !empty($VatPrice18)) {
			$excludeVatPrice18 = number_format($excludeVatPrice18, 2, ',', ' ');
			$VatPrice18 = number_format($VatPrice18, 2, ',', ' ');
			$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$tax_label.' (18%)
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.str_replace('.', ',', $VatPrice18).'
									</p>
								</td>
							</tr>';
		}
		if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
			$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
			$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
			$html_sum  .=   '<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$tax_label.' (25%)
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.str_replace('.', ',', $VatPrice25).'
									</p>
								</td>
							</tr>';
		}
		if (empty($excludeVatPrice25) && empty($VatPrice25) && empty($excludeVatPrice18) && empty($VatPrice18) && empty($excludeVatPrice12) && empty($VatPrice12)) {
			$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$tax_label.'
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										0,00
									</p>
								</td>
							</tr>';
		} 
		if (empty($totalPrice)) {
			$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$net_label.'
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										0,00
									</p>
								</td>
							</tr>
							<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									</p>
								</td>
								<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
									</p>
								</td>
							</tr>
							<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									</p>
								</td>
								<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										<strong>'.$currency.' '.$total_label.'&nbsp;&nbsp;</strong>0,00
									</p>
								</td>
							</tr>';
		} else {
			$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal align=left style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$net_label.'
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.str_replace('.', ',', number_format($totalNetPrice, 2, ',', ' ')).'
									</p>
								</td>
							</tr>
							<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									</p>
								</td>
								<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
									</p>
								</td>
							</tr>
							<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									</p>
								</td>
								<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										<strong>'.$currency.' '.$total_label.'&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')).'
									</p>
								</td>
							</tr>';
		}

		/*********************************************************************************************/
		/*********************************************************************************************/
		/**********************					MAIL BOOKING TABLE END				  ***********************/
		/*********************************************************************************************/
		/*********************************************************************************************/
			
			$position_information = $pos->get('information');
			if ($position_information == '')
				$position_information = $translator->{'None specified.'};

			$position_area = $pos->get('area');
			if ($position_area == '')
				$position_area = $translator->{'None specified.'};

			$arranger_message = $_POST['arranger_message'];
			if ($arranger_message == '')
				$arranger_message = $translator->{'No message was given.'};
			
			$exhibitor_commodity = $_POST['commodity'];
			if ($exhibitor_commodity == '')
				$exhibitor_commodity = $translator->{'No commodity was entered.'};

				//Check mail settings and send only if setting is set
				$errors = array();
				$mail_errors = array();
				$email = $fair->get("url") . EMAIL_FROM_DOMAIN;
				$from = array($email => $fair->get("windowtitle"));
				$mailSettings = json_decode($fair->get("mail_settings"));

				if($fair->get('contact_name')) {
					$from = array($email => $fair->get('contact_name'));
				}

			//Check mail settings and send only if setting is set
			if (isset($mailSettings->recievePreliminaryBooking) && is_array($mailSettings->recievePreliminaryBooking)) {
				if (in_array("0", $mailSettings->recievePreliminaryBooking)) {
					try {
						if ($organizer->get('contact_email') == '')
							$recipients = array($organizer->get('email') => $organizer->get('company'));
						else
							$recipients = array($organizer->get('contact_email') => $organizer->get('name'));

						$mail_organizer = new Mail();
						$mail_organizer->setTemplate('preliminary_created_confirm');
						$mail_organizer->setPlainTemplate('preliminary_created_confirm');
						$mail_organizer->setFrom($from);
						$mail_organizer->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
						$mail_organizer->setRecipients($recipients);
							$mail_organizer->setMailVar('booking_table', $html);
							$mail_organizer->setMailVar('booking_sum', $html_sum);
							$mail_organizer->setMailVar('exhibitor_company_name', $user->get('company'));
							$mail_organizer->setMailvar('exhibitor_name', $user->get('name'));
							$mail_organizer->setMailVar('event_name', $fair->get('windowtitle'));
							$mail_organizer->setMailVar('event_url', BASE_URL . $fair->get('url'));
							$mail_organizer->setMailVar('position_name', $pos->get('name'));
							$mail_organizer->setMailVar('position_information', $position_information);
							$mail_organizer->setMailVar('position_area', $position_area);
							$mail_organizer->setMailVar('arranger_message', $arranger_message);
							$mail_organizer->setMailVar('commodity', $exhibitor_commodity);
							$mail_organizer->setMailVar('html_categories', $htmlcategoryNames);

						if(!$mail_organizer->send()) {
							$errors[] = $organizer->get('company');
						}

					} catch(Swift_RfcComplianceException $ex) {
						// Felaktig epost-adress
						$errors[] = $organizer->get('company');
						$mail_errors[] = $ex->getMessage();

					} catch(Exception $ex) {
						// Okänt fel
						$errors[] = $organizer->get('company');
						$mail_errors[] = $ex->getMessage();
					}
				}
			}
			try {
				if ($user->get('contact_email') == '')
					$recipients = array($user->get('email') => $user->get('company'));
				else
					$recipients = array($user->get('contact_email') => $user->get('name'));
				
				$mail_user = new Mail();
				$mail_user->setTemplate('preliminary_created_receipt');
				$mail_user->setPlainTemplate('preliminary_created_receipt');
				$mail_user->setFrom($from);
				$mail_user->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
				$mail_user->setRecipients($recipients);
					$mail_user->setMailVar('booking_table', $html);
					$mail_user->setMailVar('booking_sum', $html_sum);
					$mail_user->setMailVar('exhibitor_company_name', $user->get('company'));
					$mail_user->setMailvar('exhibitor_name', $user->get('name'));
					$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
					$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
					$mail_user->setMailVar('event_email', $fair->get('contact_email'));
					$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
					$mail_user->setMailVar('event_website', $fair->get('website'));
					$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
					$mail_user->setMailVar('position_name', $pos->get('name'));
					$mail_user->setMailVar('position_information', $position_information);
					$mail_user->setMailVar('position_area', $position_area);
					$mail_user->setMailVar('arranger_message', $arranger_message);
					$mail_user->setMailVar('commodity', $exhibitor_commodity);
					$mail_user->setMailVar('html_categories', $htmlcategoryNames);

				if(!$mail_user->send()) {
					$errors[] = $user->get('company');
				}

			} catch(Swift_RfcComplianceException $ex) {
				// Felaktig epost-adress
				$errors[] = $user->get('company');
				$mail_errors[] = $ex->getMessage();

			} catch(Exception $ex) {
				// Okänt fel
				$errors[] = $user->get('company');
				$mail_errors[] = $ex->getMessage();
			}
		}
	}

	exit;
}
if (isset($_POST['cancelPreliminary'])) {

	if (userLevel() == 1) {
		$pb = new PreliminaryBooking();
		$pb->loadsafe($_POST['cancelPreliminary'], 'position', $_SESSION['user_id'], 'user');
		$pb->del_pre_booking($pb->get('id'), $_SESSION['user_id'], $_POST['cancelPreliminary']);
	}

	exit;

}

if (isset($_POST['cancelBooking'])) {

	/// KONTROLLERAD MAILMALL

	if (userLevel() > 1) {

		$position = new FairMapPosition();
		$position->load($_POST['cancelBooking'], 'id');

		$status = $position->get('status');

		$fairMap = new FairMap();
		$fairMap->load($position->get('map'), 'id');

		$fair = new Fair();
		$fair->load($fairMap->get('fair'), 'id');

		$current_user = new User();
		$current_user->load($_SESSION['user_id'], 'id');

		$organizer = new User();
		$organizer->load($fair->get('created_by'), 'id');

		$ex = new Exhibitor();
		$ex->load($position->get('id'), 'position');
		$user = new User();
		$user->load($ex->get('user'), 'id');
		$ex->delete($_POST['comment']);
		$position->set('status', 0);
		$position->set('expires', '0000-00-00 00:00:00');
		$position->save();
		if ($status = 1) {
			$mail_type = 'reservation';
		} else {
			$mail_type = 'booking';
		}

		$comment = $_POST['comment'];
		if ($comment != '') {
			$comment = '<br>'.$_POST['comment'];
		}
		$plain_comment = $_POST['comment'];
		if ($plain_comment == '') {
			$plain_comment = '<br>'.$_POST['comment'];
		}
		//Check mail settings and send only if setting is set
		if ($fair->wasLoaded()) {
			$mailSettings = json_decode($fair->get("mail_settings"));
			if (isset($mailSettings->bookingCancelled) && is_array($mailSettings->bookingCancelled)) {
				$errors = array();
				$mail_errors = array();

				$email = $fair->get("url") . EMAIL_FROM_DOMAIN;
				$from = array($email => $fair->get("windowtitle"));

				if($fair->get('contact_name')) {
					$from = array($email => $fair->get('contact_name'));
				}

				if (in_array("1", $mailSettings->bookingCancelled)) {
					try {
						if ($user->get('contact_email') == '')
							$recipients = array($user->get('email') => $user->get('company'));
						else
							$recipients = array($user->get('contact_email') => $user->get('name'));
						
						$mail_user = new Mail();
						$mail_user->setTemplate($mail_type . '_cancelled_receipt');
						$mail_user->setPlainTemplate($mail_type . '_cancelled_receipt');
						$mail_user->setFrom($from);
						$mail_user->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
						$mail_user->setRecipients($recipients);
							$mail_user->setMailVar('position_name', $position->get('name'));
							$mail_user->setMailVar('exhibitor_company_name', $user->get('company'));
							$mail_user->setMailvar('exhibitor_name', $user->get('name'));
							$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
							$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
							$mail_user->setMailVar('event_email', $fair->get('contact_email'));
							$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
							$mail_user->setMailVar('event_website', $fair->get('website'));
							$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
							$mail_user->setMailVar('comment', $comment);
							$mail_user->setMailVar('plain_comment', $plain_comment);

						if(!$mail_user->send()) {
							$errors[] = $user->get('company');
						}

					} catch(Swift_RfcComplianceException $ex) {
						// Felaktig epost-adress
						$errors[] = $user->get('company');
						$mail_errors[] = $ex->getMessage();

					} catch(Exception $ex) {
						// Okänt fel
						$errors[] = $user->get('company');
						$mail_errors[] = $ex->getMessage();
					}
				}

				if ($current_user->get('email') != $organizer->get('email')) {
					if (in_array("2", $mailSettings->bookingCancelled)) {
						try {
						if ($user->get('contact_email') == '')
							$recipients = array($current_user->get('email') => $current_user->get('company'));
						else
							$recipients = array($current_user->get('contact_email') => $current_user->get('name'));
						
							$mail_current_user = new Mail();
							$mail_current_user->setTemplate($mail_type . '_cancelled_confirm');
							$mail_current_user->setPlainTemplate($mail_type . '_cancelled_confirm');
							$mail_current_user->setFrom($from);
							$mail_current_user->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
							$mail_current_user->setRecipients($recipients);
								$mail_current_user->setMailVar('position_name', $position->get('name'));
								$mail_current_user->setMailVar('exhibitor_company_name', $user->get('company'));
								$mail_current_user->setMailvar('exhibitor_name', $user->get('name'));
								$mail_current_user->setMailVar('event_name', $fair->get('windowtitle'));
								$mail_current_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
								$mail_current_user->setMailVar('comment', $comment);
								$mail_current_user->setMailVar('plain_comment', $plain_comment);

							if(!$mail_current_user->send()) {
								$errors[] = $current_user->get('company');
							}

						} catch(Swift_RfcComplianceException $ex) {
							// Felaktig epost-adress
							$errors[] = $current_user->get('company');
							$mail_errors[] = $ex->getMessage();

						} catch(Exception $ex) {
							// Okänt fel
							$errors[] = $current_user->get('company');
							$mail_errors[] = $ex->getMessage();
						}
					}
				}

				if (in_array("0", $mailSettings->bookingCancelled)) {
					try {
						if ($organizer->get('contact_email') == '')
							$recipients = array($organizer->get('email') => $organizer->get('company'));
						else
							$recipients = array($organizer->get('contact_email') => $organizer->get('name'));

						$mail_organizer = new Mail();
						$mail_organizer->setTemplate($mail_type . '_cancelled_confirm');
						$mail_organizer->setPlainTemplate($mail_type . '_cancelled_confirm');
						$mail_organizer->setFrom($from);
						$mail_organizer->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
						$mail_organizer->setRecipients($recipients);
							$mail_organizer->setMailVar('position_name', $position->get('name'));
							$mail_organizer->setMailVar('exhibitor_company_name', $user->get('company'));
							$mail_organizer->setMailvar('exhibitor_name', $user->get('name'));
							$mail_organizer->setMailVar('event_name', $fair->get('windowtitle'));
							$mail_organizer->setMailVar('event_url', BASE_URL . $fair->get('url'));
							$mail_organizer->setMailVar('comment', $comment);
							$mail_organizer->setMailVar('plain_comment', $plain_comment);

						if(!$mail_organizer->send()) {
							$errors[] = $organizer->get('company');
						}

					} catch(Swift_RfcComplianceException $ex) {
						// Felaktig epost-adress
						$errors[] = $organizer->get('company');
						$mail_errors[] = $ex->getMessage();

					} catch(Exception $ex) {
						// Okänt fel
						$errors[] = $organizer->get('company');
						$mail_errors[] = $ex->getMessage();
					}
				}
				if ($errors) {
					$_SESSION['mail_errors'] = $mail_errors;
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
		$fair = new Fair;
		$fair->load($_POST['fairId'], 'id');
		if ($fair->wasLoaded()) {
			$sql = "INSERT INTO `fair_user_relation`(`fair`, `user`, `connected_time`) VALUES (?,?,?)";
			$stmt = $globalDB->prepare($sql);
			$stmt->execute(array($_POST['fairId'], $_SESSION['user_id'], time()));
			$response['message'] = $translator->{'Connected to fair'}.' '.$fair->get('windowtitle');
			$response['success'] = true;
		} else {
			$response['message'] = $translator->{'Unable to connect to fair.'};
			$response['success'] = false;
		}
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

	/// KONTROLLERAD MAILMALL

	if (userLevel() < 1)
		exit;

	$pb = new PreliminaryBooking();
	$pb->load($_POST['reserve_preliminary'], 'id');

	if ($pb->wasLoaded()) {
		$pos = new FairMapPosition();
		$pos->load($pb->get('position'), 'id');
		$booking_time = $pb->get('booking_time');

		$pos->set('status', 1);
		$pos->set('expires', date('Y-m-d H:i:s', strtotime($_POST['expires'])));

		$ex = new Exhibitor();
		$ex->set('user', $pb->get('user'));
		$ex->set('fair', $pb->get('fair'));
		$ex->set('position', $pb->get('position'));
		$ex->set('commodity', $_POST['commodity']);
		$ex->set('arranger_message', $pb->get('arranger_message'));
		$ex->set('edit_time', time());
		$ex->set('clone', 0);
		$ex->set('status', 1);
		
		$exId = $ex->save();
		$pos->save();
		$pb->accept();



		$categories = array();

		if (isset($_POST['categories']) && is_array($_POST['categories'])) {
			$stmt = $pos->db->prepare("INSERT INTO `exhibitor_category_rel` (`exhibitor`, `category`) VALUES (?, ?)");
			foreach ($_POST['categories'] as $cat) {
				$stmt->execute(array($exId, $cat));
				$category = new ExhibitorCategory();
				$category->load($cat, "id");
				if ($category->wasLoaded()) {
					$categories[] = $category->get("name");
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
		
		$fair = new Fair();
		$fair->load($ex->get('fair'), 'id');
		
		$organizer = new User();
		$organizer->load($fair->get('created_by'), 'id');

		$user = new User();
		$user->load($ex->get('user'), 'id');

		$htmlcategoryNames = implode('<br>', $categories);
		$fairInvoice = new FairInvoice();
		$fairInvoice->load($ex->get('fair'), 'fair');

		/*****************************************************************************************/
		/*****************************************************************************************/
		/************************				PREPARE MAIL START			  *************************/
		/*****************************************************************************************/
		/*****************************************************************************************/

		/*********************************************************************************/
		/*********************************************************************************/
		/********************************       LABELS       *****************************/
		/*********************************************************************************/
		/*********************************************************************************/

		$name_label = $translator->{'Name'};
		$price_label = $translator->{'Price'};
		$amount_label = $translator->{'Amount'};
		$vat_label = $translator->{'Vat'};
		$sum_label = $translator->{'Sum'};
		$booked_space_label = $translator->{'Stand'};
		$options_label = $translator->{'Options'};
		$articles_label = $translator->{'Articles'};
		$tax_label = $translator->{'Tax'};
		$parttotal_label = $translator->{'Subtotal'};
		$net_label = $translator->{'Net'};
		$rounding_label = $translator->{'Rounding'};
		$total_label = $translator->{'total:'};
		$st_label = $translator->{'st'};
		$nothing_selected_label = $translator->{'No articles or options selected.'};

		/*************************************************************/
		/*************************************************************/
		/*****************     PRICES AND AMOUNTS        *************/
		/*************************************************************/
		/*************************************************************/ 

		$totalPrice = 0;
		$totalNetPrice = 0;
		$VatPrice0 = 0;
		$VatPrice12 = 0;
		$VatPrice18 = 0;
		$VatPrice25 = 0;
		$excludeVatPrice0 = 0;
		$excludeVatPrice12 = 0;
		$excludeVatPrice18 = 0;
		$excludeVatPrice25 = 0;
		$currency = $fair->get('currency');
		$position_vat = 0;
		$position_name = $pos->get('name');
		$position_price = $pos->get('price');
		$position_vat = $fairInvoice->get('pos_vat');

		/*********************************************************************************************/
		/*********************************************************************************************/
		/**********************					MAIL BOOKING TABLE START			  ***********************/
		/*********************************************************************************************/
		/*********************************************************************************************/
$html = '<!-- SIX COLUMN HEADERS -->
			<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;padding:10px 0 0 0;">
			 <!-- ID -->
			 <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			     ID
			   </p>
			 </td>
			 <!-- NAME -->
			 <td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			     '.$name_label.'
			   </p>
			 </td>
			 <!-- PRICE -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
			     '.$price_label.'
			   </p>
			 </td>
			 <!-- AMOUNT -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
			     '.$amount_label.'
			   </p>
			 </td>
			 <!-- VAT % -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
			     '.$vat_label.'
			   </p>
			 </td>
			 <!-- SUM -->
			 <td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
			     '.$sum_label.'
			   </p>
			 </td>
			</tr>
			<!-- SPACER ROW -->
			<tr style="mso-yfti-irow:1;height:11.1pt">
			</tr>
			<!-- STAND SPACE ROW LABEL-->
			<tr style="mso-yfti-irow:1;height:25.1pt">
			 	<!-- ID -->
				<td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			<!-- NAME -->
				<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$booked_space_label.'
						</span>
					</p>
				</td>
				<!-- PRICE -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- AMOUNT -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- VAT % -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- SUM -->
				<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			</tr>
			<!-- STAND SPACE ROW INFO -->
			<tr style="mso-yfti-irow:1;height:25.1pt">
			 	<!-- ID -->
				<td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			<!-- NAME -->
				<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_name.'
						</span>
					</p>
				</td>
				<!-- PRICE -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_price.'
						</span>
					</p>
				</td>
				<!-- AMOUNT -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						1'.$st_label.'
						</span>
					</p>
				</td>
				<!-- VAT % -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_vat.'%
						</span>
					</p>
				</td>
				<!-- SUM -->
				<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.number_format($position_price, 2, ',', ' ').'
						</span>
					</p>
				</td>
			</tr>';

$html_sum = '<!-- TWO COLUMN VAT PRICE AND NET SUMMATION -->
				<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;">
					<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
						<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						</p>
					</td>
					<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
						<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						</p>
					</td>
				</tr>';

if ($position_vat == 25) {
	$excludeVatPrice25 += $position_price;
} else if ($position_vat == 18) {
	$excludeVatPrice18 += $position_price;
} else {
	$excludeVatPrice0 += $position_price;
}

if (!empty($_POST['options']) && is_array($_POST['options'])) {
	$html .= '<!-- SIX COLUMNS -->
               <tr style="mso-yfti-irow:1;height:25.1pt">
                	<!-- ID -->
                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
                  		</span>
                  	</p>
                  </td>
                  <!-- NAME -->
						<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.$options_label.'
								</span>
							</p>
						</td>
						<!-- PRICE -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- AMOUNT -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- VAT % -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- SUM -->
						<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
					</tr>';

	for ($row=0; $row<count($options[1]); $row++) {
		$html .= '<!-- SIX COLUMNS -->
	               <tr style="mso-yfti-irow:1;height:25.1pt">
	                	<!-- ID -->
	                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
	                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
	                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
	                  		'.$options[0][$row].'
	                  		</span>
	                  	</p>
	                  </td>
	                  <!-- NAME -->
							<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[1][$row].'
									</span>
								</p>
							</td>
							<!-- PRICE -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[2][$row].'
									</span>
								</p>
							</td>
							<!-- AMOUNT -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									1'.$st_label.'
									</span>
								</p>
							</td>
							<!-- VAT % -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[3][$row].'%
									</span>
								</p>
							</td>
							<!-- SUM -->
							<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')).'
									</span>
								</p>
							</td>
						</tr>';
	}
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	$html .= '<!-- SIX COLUMNS -->
               <tr style="mso-yfti-irow:1;height:25.1pt">
                	<!-- ID -->
                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
                  		</span>
                  	</p>
                  </td>
                  <!-- NAME -->
						<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.$articles_label.'
								</span>
							</p>
						</td>
						<!-- PRICE -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- AMOUNT -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- VAT % -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- SUM -->
						<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
					</tr>';

	for ($row=0; $row<count($articles[1]); $row++) {
		$html .= '<!-- SIX COLUMNS -->
	               <tr style="mso-yfti-irow:1;height:25.1pt">
	                	<!-- ID -->
	                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
	                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
	                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
	                  		'.$articles[0][$row].'
	                  		</span>
	                  	</p>
	                  </td>
	                  <!-- NAME -->
							<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[1][$row].'
									</span>
								</p>
							</td>
							<!-- PRICE -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', $articles[2][$row]).'
									</span>
								</p>
							</td>
							<!-- AMOUNT -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[3][$row].' '.$st_label.'
									</span>
								</p>
							</td>
							<!-- VAT % -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[4][$row].'%
									</span>
								</p>
							</td>
							<!-- SUM -->
							<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')).'
									</span>
								</p>
							</td>
						</tr>';
    }
}


if (!empty($_POST['options']) && is_array($_POST['options'])) {
	for ($row=0; $row<count($options[1]); $row++) {

		if ($options[3][$row] == 25) {
			$excludeVatPrice25 += $options[2][$row];
		}
		if ($options[3][$row] == 18) {
			$excludeVatPrice18 += $options[2][$row];
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
		if ($articles[4][$row] == 18) {
			$excludeVatPrice18 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
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
$VatPrice18 = $excludeVatPrice18*0.18;
$VatPrice25 = $excludeVatPrice25*0.25;
$totalPrice += $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25 + $VatPrice12 + $VatPrice18 + $VatPrice25 + $VatPrice0;
$totalNetPrice += $excludeVatPrice0 + $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25;

$totalPriceRounded = round($totalPrice);
$pennys = ($totalPriceRounded - $totalPrice);

if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
	$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
	$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');

	$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (12%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice12).'
							</p>
						</td>
					</tr>';

}
if (!empty($excludeVatPrice18) && !empty($VatPrice18)) {
	$excludeVatPrice18 = number_format($excludeVatPrice18, 2, ',', ' ');
	$VatPrice18 = number_format($VatPrice18, 2, ',', ' ');
	$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (18%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice18).'
							</p>
						</td>
					</tr>';
}
if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
	$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
	$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
	$html_sum  .=   '<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (25%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice25).'
							</p>
						</td>
					</tr>';
}
if (empty($excludeVatPrice25) && empty($VatPrice25) && empty($excludeVatPrice18) && empty($VatPrice18) && empty($excludeVatPrice12) && empty($VatPrice12)) {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								0,00
							</p>
						</td>
					</tr>';
} 
if (empty($totalPrice)) {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$net_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								0,00
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$currency.' '.$total_label.'&nbsp;&nbsp;</strong>0,00
							</p>
						</td>
					</tr>';
} else {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal align=left style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$net_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', number_format($totalNetPrice, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$currency.' '.$total_label.'&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')).'
							</p>
						</td>
					</tr>';
}

/*********************************************************************************************/
/*********************************************************************************************/
/**********************					MAIL BOOKING TABLE END				  ***********************/
/*********************************************************************************************/
/*********************************************************************************************/
	
		$position_information = $pos->get('information');
		if ($position_information == '')
			$position_information = $translator->{'None specified.'};

		$position_area = $pos->get('area');
		if ($position_area == '')
			$position_area = $translator->{'None specified.'};

		$arranger_message = $pb->get('arranger_message');
		if ($arranger_message == '')
			$arranger_message = $translator->{'No message was given.'};
		
		$exhibitor_commodity = $_POST['commodity'];
		if ($exhibitor_commodity == '')
			$exhibitor_commodity = $translator->{'No commodity was entered.'};


		//Check mail settings and send only if setting is set
		if ($fair->wasLoaded()) {
			$mailSettings = json_decode($fair->get("mail_settings"));
			if (isset($mailSettings->acceptPreliminaryBooking) && is_array($mailSettings->acceptPreliminaryBooking)) {
				$errors = array();
				$mail_errors = array();
				$email = $fair->get("url") . EMAIL_FROM_DOMAIN;
				$from = array($email => $fair->get("windowtitle"));

				if($fair->get('contact_name')) {
					$from = array($email => $fair->get('contact_name'));
				}

				if (in_array("0", $mailSettings->acceptPreliminaryBooking)) {
					try {
						if ($organizer->get('contact_email') == '')
							$recipients = array($organizer->get('email') => $organizer->get('company'));
						else
							$recipients = array($organizer->get('contact_email') => $organizer->get('name'));

						$mail_organizer = new Mail();
						$mail_organizer->setTemplate('preliminary_approved_confirm');
						$mail_organizer->setPlainTemplate('preliminary_approved_confirm');
						$mail_organizer->setFrom($from);
						$mail_organizer->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
						$mail_organizer->setRecipients($recipients);
							$mail_organizer->setMailVar('booking_table', $html);
							$mail_organizer->setMailVar('booking_sum', $html_sum);
							$mail_organizer->setMailVar('exhibitor_company_name', $user->get('company'));
							$mail_organizer->setMailvar('exhibitor_name', $user->get('name'));
							$mail_organizer->setMailVar('event_name', $fair->get('windowtitle'));
							$mail_organizer->setMailVar('event_url', BASE_URL . $fair->get('url'));
							$mail_organizer->setMailVar('position_name', $pos->get('name'));
							$mail_organizer->setMailVar('position_information', $position_information);
							$mail_organizer->setMailVar('position_area', $position_area);
							$mail_organizer->setMailVar('arranger_message', $arranger_message);
							$mail_organizer->setMailVar('commodity', $exhibitor_commodity);
							$mail_organizer->setMailVar('html_categories', $htmlcategoryNames);
							$mail_organizer->setMailVar('expirationdate', $_POST['expires']);
					
							if(!$mail_organizer->send()) {
								$errors[] = $organizer->get('company');
							}

						} catch(Swift_RfcComplianceException $ex) {
							// Felaktig epost-adress
							$errors[] = $organizer->get('company');
							$mail_errors[] = $ex->getMessage();

						} catch(Exception $ex) {
							// Okänt fel
							$errors[] = $organizer->get('company');
							$mail_errors[] = $ex->getMessage();
						}
				}
				if (in_array("1", $mailSettings->acceptPreliminaryBooking)) {
					try {
						if ($user->get('contact_email') == '')
							$recipients = array($user->get('email') => $user->get('company'));
						else
							$recipients = array($user->get('contact_email') => $user->get('name'));
						
						$mail_user = new Mail();
						$mail_user->setTemplate('preliminary_approved_receipt');
						$mail_user->setPlainTemplate('preliminary_approved_receipt');
						$mail_user->setFrom($from);
						$mail_user->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
						$mail_user->setRecipients($recipients);
							$mail_user->setMailVar('booking_table', $html);
							$mail_user->setMailVar('booking_sum', $html_sum);
							$mail_user->setMailVar('exhibitor_company_name', $user->get('company'));
							$mail_user->setMailvar('exhibitor_name', $user->get('name'));
							$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
							$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
							$mail_user->setMailVar('event_email', $fair->get('contact_email'));
							$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
							$mail_user->setMailVar('event_website', $fair->get('website'));
							$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
							$mail_user->setMailVar('position_name', $pos->get('name'));
							$mail_user->setMailVar('position_information', $position_information);
							$mail_user->setMailVar('position_area', $position_area);
							$mail_user->setMailVar('arranger_message', $arranger_message);
							$mail_user->setMailVar('commodity', $exhibitor_commodity);
							$mail_user->setMailVar('html_categories', $htmlcategoryNames);
							$mail_user->setMailVar('expirationdate', $_POST['expires']);

						if(!$mail_user->send()) {
							$errors[] = $user->get('company');
						}

					} catch(Swift_RfcComplianceException $ex) {
						// Felaktig epost-adress
						$errors[] = $user->get('company');
						$mail_errors[] = $ex->getMessage();

					} catch(Exception $ex) {
						// Okänt fel
						$errors[] = $user->get('company');
						$mail_errors[] = $ex->getMessage();
					}
				}
				if ($errors)
					$_SESSION['mail_errors'] = $mail_errors;
			}
		}
	}
	exit;
}

if (isset($_POST['book_preliminary'])) {

	/// KONTROLLERAD MAILMALL

	if (userLevel() < 1)
		exit;

	$pb = new PreliminaryBooking();
	$pb->load($_POST['book_preliminary'], 'id');

	if ($pb->wasLoaded()) {
		$pos = new FairMapPosition();
		$pos->load($pb->get('position'), 'id');
		$booking_time = $pb->get('booking_time');

		$pos->set('status', 2);
		$pos->set('expires', '0000-00-00 00:00:00');

		$ex = new Exhibitor();
		$ex->set('user', $pb->get('user'));
		$ex->set('fair', $pb->get('fair'));
		$ex->set('position', $pb->get('position'));
		$ex->set('commodity', $_POST['commodity']);
		$ex->set('arranger_message', $pb->get('arranger_message'));
		$ex->set('booking_time', $pb->get('booking_time'));
		$ex->set('edit_time', time());
		$ex->set('clone', 0);
		$ex->set('status', 2);
		
		$exId = $ex->save();
		$pos->save();
		$pb->accept();



		$categories = array();

		if (isset($_POST['categories']) && is_array($_POST['categories'])) {
			$stmt = $pos->db->prepare("INSERT INTO `exhibitor_category_rel` (`exhibitor`, `category`) VALUES (?, ?)");
			foreach ($_POST['categories'] as $cat) {
				$stmt->execute(array($exId, $cat));
				$category = new ExhibitorCategory();
				$category->load($cat, "id");
				if ($category->wasLoaded()) {
					$categories[] = $category->get("name");
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
		
		$fair = new Fair();
		$fair->load($ex->get('fair'), 'id');
		
		$organizer = new User();
		$organizer->load($fair->get('created_by'), 'id');

		$user = new User();
		$user->load($ex->get('user'), 'id');

		$htmlcategoryNames = implode('<br>', $categories);
		$fairInvoice = new FairInvoice();
		$fairInvoice->load($ex->get('fair'), 'fair');

		/*****************************************************************************************/
		/*****************************************************************************************/
		/************************				PREPARE MAIL START			  *************************/
		/*****************************************************************************************/
		/*****************************************************************************************/

		/*********************************************************************************/
		/*********************************************************************************/
		/********************************       LABELS       *****************************/
		/*********************************************************************************/
		/*********************************************************************************/

		$name_label = $translator->{'Name'};
		$price_label = $translator->{'Price'};
		$amount_label = $translator->{'Amount'};
		$vat_label = $translator->{'Vat'};
		$sum_label = $translator->{'Sum'};
		$booked_space_label = $translator->{'Stand'};
		$options_label = $translator->{'Options'};
		$articles_label = $translator->{'Articles'};
		$tax_label = $translator->{'Tax'};
		$parttotal_label = $translator->{'Subtotal'};
		$net_label = $translator->{'Net'};
		$rounding_label = $translator->{'Rounding'};
		$total_label = $translator->{'total:'};
		$st_label = $translator->{'st'};
		$nothing_selected_label = $translator->{'No articles or options selected.'};

		/*************************************************************/
		/*************************************************************/
		/*****************     PRICES AND AMOUNTS        *************/
		/*************************************************************/
		/*************************************************************/ 

		$totalPrice = 0;
		$totalNetPrice = 0;
		$VatPrice0 = 0;
		$VatPrice12 = 0;
		$VatPrice18 = 0;
		$VatPrice25 = 0;
		$excludeVatPrice0 = 0;
		$excludeVatPrice12 = 0;
		$excludeVatPrice18 = 0;
		$excludeVatPrice25 = 0;
		$currency = $fair->get('currency');
		$position_vat = 0;
		$position_name = $pos->get('name');
		$position_price = $pos->get('price');
		$position_vat = $fairInvoice->get('pos_vat');

		/*********************************************************************************************/
		/*********************************************************************************************/
		/**********************					MAIL BOOKING TABLE START			  ***********************/
		/*********************************************************************************************/
		/*********************************************************************************************/
$html = '<!-- SIX COLUMN HEADERS -->
			<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;padding:10px 0 0 0;">
			 <!-- ID -->
			 <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			     ID
			   </p>
			 </td>
			 <!-- NAME -->
			 <td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			     '.$name_label.'
			   </p>
			 </td>
			 <!-- PRICE -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
			     '.$price_label.'
			   </p>
			 </td>
			 <!-- AMOUNT -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
			     '.$amount_label.'
			   </p>
			 </td>
			 <!-- VAT % -->
			 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
			     '.$vat_label.'
			   </p>
			 </td>
			 <!-- SUM -->
			 <td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
			   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
			     '.$sum_label.'
			   </p>
			 </td>
			</tr>
			<!-- SPACER ROW -->
			<tr style="mso-yfti-irow:1;height:11.1pt">
			</tr>
			<!-- STAND SPACE ROW LABEL-->
			<tr style="mso-yfti-irow:1;height:25.1pt">
			 	<!-- ID -->
				<td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			<!-- NAME -->
				<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$booked_space_label.'
						</span>
					</p>
				</td>
				<!-- PRICE -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- AMOUNT -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- VAT % -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
				<!-- SUM -->
				<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			</tr>
			<!-- STAND SPACE ROW INFO -->
			<tr style="mso-yfti-irow:1;height:25.1pt">
			 	<!-- ID -->
				<td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						</span>
					</p>
				</td>
			<!-- NAME -->
				<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_name.'
						</span>
					</p>
				</td>
				<!-- PRICE -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_price.'
						</span>
					</p>
				</td>
				<!-- AMOUNT -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						1'.$st_label.'
						</span>
					</p>
				</td>
				<!-- VAT % -->
				<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.$position_vat.'%
						</span>
					</p>
				</td>
				<!-- SUM -->
				<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
					<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
						<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
						'.number_format($position_price, 2, ',', ' ').'
						</span>
					</p>
				</td>
			</tr>';

$html_sum = '<!-- TWO COLUMN VAT PRICE AND NET SUMMATION -->
				<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;">
					<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
						<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						</p>
					</td>
					<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
						<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
						</p>
					</td>
				</tr>';

if ($position_vat == 25) {
	$excludeVatPrice25 += $position_price;
} else if ($position_vat == 18) {
	$excludeVatPrice18 += $position_price;
} else {
	$excludeVatPrice0 += $position_price;
}

if (!empty($_POST['options']) && is_array($_POST['options'])) {
	$html .= '<!-- SIX COLUMNS -->
               <tr style="mso-yfti-irow:1;height:25.1pt">
                	<!-- ID -->
                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
                  		</span>
                  	</p>
                  </td>
                  <!-- NAME -->
						<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.$options_label.'
								</span>
							</p>
						</td>
						<!-- PRICE -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- AMOUNT -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- VAT % -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- SUM -->
						<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
					</tr>';

	for ($row=0; $row<count($options[1]); $row++) {
		$html .= '<!-- SIX COLUMNS -->
	               <tr style="mso-yfti-irow:1;height:25.1pt">
	                	<!-- ID -->
	                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
	                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
	                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
	                  		'.$options[0][$row].'
	                  		</span>
	                  	</p>
	                  </td>
	                  <!-- NAME -->
							<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[1][$row].'
									</span>
								</p>
							</td>
							<!-- PRICE -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[2][$row].'
									</span>
								</p>
							</td>
							<!-- AMOUNT -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									1'.$st_label.'
									</span>
								</p>
							</td>
							<!-- VAT % -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$options[3][$row].'%
									</span>
								</p>
							</td>
							<!-- SUM -->
							<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')).'
									</span>
								</p>
							</td>
						</tr>';
	}
}

if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
	$html .= '<!-- SIX COLUMNS -->
               <tr style="mso-yfti-irow:1;height:25.1pt">
                	<!-- ID -->
                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
                  		</span>
                  	</p>
                  </td>
                  <!-- NAME -->
						<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								'.$articles_label.'
								</span>
							</p>
						</td>
						<!-- PRICE -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- AMOUNT -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- VAT % -->
						<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
						<!-- SUM -->
						<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
								<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
								</span>
							</p>
						</td>
					</tr>';

	for ($row=0; $row<count($articles[1]); $row++) {
		$html .= '<!-- SIX COLUMNS -->
	               <tr style="mso-yfti-irow:1;height:25.1pt">
	                	<!-- ID -->
	                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
	                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
	                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
	                  		'.$articles[0][$row].'
	                  		</span>
	                  	</p>
	                  </td>
	                  <!-- NAME -->
							<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[1][$row].'
									</span>
								</p>
							</td>
							<!-- PRICE -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', $articles[2][$row]).'
									</span>
								</p>
							</td>
							<!-- AMOUNT -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[3][$row].' '.$st_label.'
									</span>
								</p>
							</td>
							<!-- VAT % -->
							<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.$articles[4][$row].'%
									</span>
								</p>
							</td>
							<!-- SUM -->
							<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
								<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
									<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
									'.str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')).'
									</span>
								</p>
							</td>
						</tr>';
    }
}


if (!empty($_POST['options']) && is_array($_POST['options'])) {
	for ($row=0; $row<count($options[1]); $row++) {

		if ($options[3][$row] == 25) {
			$excludeVatPrice25 += $options[2][$row];
		}
		if ($options[3][$row] == 18) {
			$excludeVatPrice18 += $options[2][$row];
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
		if ($articles[4][$row] == 18) {
			$excludeVatPrice18 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
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
$VatPrice18 = $excludeVatPrice18*0.18;
$VatPrice25 = $excludeVatPrice25*0.25;
$totalPrice += $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25 + $VatPrice12 + $VatPrice18 + $VatPrice25 + $VatPrice0;
$totalNetPrice += $excludeVatPrice0 + $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25;

$totalPriceRounded = round($totalPrice);
$pennys = ($totalPriceRounded - $totalPrice);

if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
	$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
	$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');

	$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (12%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice12).'
							</p>
						</td>
					</tr>';

}
if (!empty($excludeVatPrice18) && !empty($VatPrice18)) {
	$excludeVatPrice18 = number_format($excludeVatPrice18, 2, ',', ' ');
	$VatPrice18 = number_format($VatPrice18, 2, ',', ' ');
	$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (18%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice18).'
							</p>
						</td>
					</tr>';
}
if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
	$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
	$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
	$html_sum  .=   '<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.' (25%)
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', $VatPrice25).'
							</p>
						</td>
					</tr>';
}
if (empty($excludeVatPrice25) && empty($VatPrice25) && empty($excludeVatPrice18) && empty($VatPrice18) && empty($excludeVatPrice12) && empty($VatPrice12)) {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$tax_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								0,00
							</p>
						</td>
					</tr>';
} 
if (empty($totalPrice)) {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$net_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								0,00
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$currency.' '.$total_label.'&nbsp;&nbsp;</strong>0,00
							</p>
						</td>
					</tr>';
} else {
	$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
							<p class=MsoNormal align=left style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.$net_label.'
							</p>
						</td>
						<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								'.str_replace('.', ',', number_format($totalNetPrice, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
							</p>
						</td>
					</tr>
					<tr style="mso-yfti-irow:0;height:13.3pt">
						<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
							</p>
						</td>
						<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
							<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								<strong>'.$currency.' '.$total_label.'&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')).'
							</p>
						</td>
					</tr>';
}

/*********************************************************************************************/
/*********************************************************************************************/
/**********************					MAIL BOOKING TABLE END				  ***********************/
/*********************************************************************************************/
/*********************************************************************************************/
	
		$position_information = $pos->get('information');
		if ($position_information == '')
			$position_information = $translator->{'None specified.'};

		$position_area = $pos->get('area');
		if ($position_area == '')
			$position_area = $translator->{'None specified.'};

		$arranger_message = $pb->get('arranger_message');
		if ($arranger_message == '')
			$arranger_message = $translator->{'No message was given.'};
		
		$exhibitor_commodity = $_POST['commodity'];
		if ($exhibitor_commodity == '')
			$exhibitor_commodity = $translator->{'No commodity was entered.'};


		//Check mail settings and send only if setting is set
		if ($fair->wasLoaded()) {
			$mailSettings = json_decode($fair->get("mail_settings"));
			if (isset($mailSettings->acceptPreliminaryBooking) && is_array($mailSettings->acceptPreliminaryBooking)) {
				$errors = array();
				$mail_errors = array();
				$email = $fair->get("url") . EMAIL_FROM_DOMAIN;
				$from = array($email => $fair->get("windowtitle"));

				if($fair->get('contact_name')) {
					$from = array($email => $fair->get('contact_name'));
				}

				if (in_array("0", $mailSettings->acceptPreliminaryBooking)) {
					try {
						if ($organizer->get('contact_email') == '')
							$recipients = array($organizer->get('email') => $organizer->get('company'));
						else
							$recipients = array($organizer->get('contact_email') => $organizer->get('name'));

						$mail_organizer = new Mail();
						$mail_organizer->setTemplate('booking_approved_confirm');
						$mail_organizer->setPlainTemplate('booking_approved_confirm');
						$mail_organizer->setFrom($from);
						$mail_organizer->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
						$mail_organizer->setRecipients($recipients);
							$mail_organizer->setMailVar('booking_table', $html);
							$mail_organizer->setMailVar('booking_sum', $html_sum);
							$mail_organizer->setMailVar('exhibitor_company_name', $user->get('company'));
							$mail_organizer->setMailvar('exhibitor_name', $user->get('name'));
							$mail_organizer->setMailVar('event_name', $fair->get('windowtitle'));
							$mail_organizer->setMailVar('event_url', BASE_URL . $fair->get('url'));
							$mail_organizer->setMailVar('position_name', $pos->get('name'));
							$mail_organizer->setMailVar('position_information', $position_information);
							$mail_organizer->setMailVar('position_area', $position_area);
							$mail_organizer->setMailVar('arranger_message', $arranger_message);
							$mail_organizer->setMailVar('commodity', $exhibitor_commodity);
							$mail_organizer->setMailVar('html_categories', $htmlcategoryNames);
					
							if(!$mail_organizer->send()) {
								$errors[] = $organizer->get('company');
							}

						} catch(Swift_RfcComplianceException $ex) {
							// Felaktig epost-adress
							$errors[] = $organizer->get('company');
							$mail_errors[] = $ex->getMessage();

						} catch(Exception $ex) {
							// Okänt fel
							$errors[] = $organizer->get('company');
							$mail_errors[] = $ex->getMessage();
						}
				}
				if (in_array("1", $mailSettings->acceptPreliminaryBooking)) {
					try {
						if ($user->get('contact_email') == '')
							$recipients = array($user->get('email') => $user->get('company'));
						else
							$recipients = array($user->get('contact_email') => $user->get('name'));
						
						$mail_user = new Mail();
						$mail_user->setTemplate('booking_approved_receipt');
						$mail_user->setPlainTemplate('booking_approved_receipt');
						$mail_user->setFrom($from);
						$mail_user->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
						$mail_user->setRecipients($recipients);
							$mail_user->setMailVar('booking_table', $html);
							$mail_user->setMailVar('booking_sum', $html_sum);
							$mail_user->setMailVar('exhibitor_company_name', $user->get('company'));
							$mail_user->setMailvar('exhibitor_name', $user->get('name'));
							$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
							$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
							$mail_user->setMailVar('event_email', $fair->get('contact_email'));
							$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
							$mail_user->setMailVar('event_website', $fair->get('website'));
							$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
							$mail_user->setMailVar('position_name', $pos->get('name'));
							$mail_user->setMailVar('position_information', $position_information);
							$mail_user->setMailVar('position_area', $position_area);
							$mail_user->setMailVar('arranger_message', $arranger_message);
							$mail_user->setMailVar('commodity', $exhibitor_commodity);
							$mail_user->setMailVar('html_categories', $htmlcategoryNames);

						if(!$mail_user->send()) {
							$errors[] = $user->get('company');
						}

					} catch(Swift_RfcComplianceException $ex) {
						// Felaktig epost-adress
						$errors[] = $user->get('company');
						$mail_errors[] = $ex->getMessage();

					} catch(Exception $ex) {
						// Okänt fel
						$errors[] = $user->get('company');
						$mail_errors[] = $ex->getMessage();
					}
				}
				if ($errors)
					$_SESSION['mail_errors'] = $mail_errors;
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
