<!DOCTYPE html>
<html>
<head>
<?php
	$unique = substr(md5(date('YmdHis')), -10);
?>
<meta charset="utf-8" />
<meta name="viewport" content="width=1300, initial-scale=0.7, maximum-scale=1.2">
<title><?php echo (isset($_SESSION['fair_windowtitle'])) ? $_SESSION['fair_windowtitle'].' - ' : ''; ?>ChartBooker</title>
<base href="<?php echo BASE_URL; ?>" />
<link rel="stylesheet" type="text/css" href="css/generic.css?u=<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/main.css?u=<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/map.css?u=<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" media="print" href="css/print.css?u=<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/jquery-ui.css?u=<?php echo $unique?>" />
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>
<script type="text/javascript" src="js/jquery-1.9.1.min.js?u=<?php echo $unique?>"></script>
<script type="text/javascript" src="js/jquery-ui-1.10.1.min.js?u=<?php echo $unique?>"></script>
<script type="text/javascript" src="js/formchecker.js?u=<?php echo $unique?>"></script>
<script type="text/javascript" src="js/passwd_meter.js?u=<?php echo $unique?>"></script>
<script type="text/javascript" src="js/jquery.tablesorter.min.js?u=<?php echo $unique?>"></script>
<script type="text/javascript" src="js/chartbooker.js?u=<?php echo $unique?>"></script>
<script type="text/javascript" src="js/mobilecheck.js?u=<?php echo $unique?>"></script>
<script type="text/javascript" src="js/maptool.js?u=<?php echo $unique?>"></script>
<script language="javascript" type="text/javascript" src="js/tiny_mce/tiny_mce.js?u=<?php echo $unique?>"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('.std_table').tablesorter();
    $( document ).tooltip(); // Initialize jQueryUI tooltips
	});
	var lang = {};
	lang.login_username = '<?php echo $translator->{"Username"} ?>';
	lang.login_password = '<?php echo $translator->{"Password"} ?>';
	lang.company_label = '<?php echo $translator->{"Company"} ?>';
	lang.commodity_label = '<?php echo $translator->{"Commodity"} ?>';
	lang.presentation_label = '<?php echo $translator->{"Presentation"} ?>';
	lang.category_label = '<?php echo $translator->{"Category"} ?>';
	lang.customer_nr_label = '<?php echo $translator->{"Customer number"} ?>';
	lang.contact_label = '<?php echo $translator->{"Contact person"} ?>';
	lang.orgnr_label = '<?php echo $translator->{"Organization number"} ?>';
	lang.address_label = '<?php echo $translator->{"Address"} ?>';
	lang.zipcode_label = '<?php echo $translator->{"Zip code"} ?>';
	lang.city_label = '<?php echo $translator->{"City"} ?>';
	lang.invoice_company_label = '<?php echo $translator->{"Company"} ?>';
	lang.invoice_address_label = '<?php echo $translator->{"Address"} ?>';
	lang.invoice_zipcode_label = '<?php echo $translator->{"Zip code"} ?>';
	lang.invoice_city_label = '<?php echo $translator->{"City"} ?>';
	lang.invoice_email_label = '<?php echo $translator->{"E-mail"} ?>';
	lang.country_label = '<?php echo $translator->{"Country"} ?>';
	lang.phone1_label = '<?php echo $translator->{"Phone 1"} ?>';
	lang.phone2_label = '<?php echo $translator->{"Phone 2"} ?>';
	lang.phone3_label = '<?php echo $translator->{"Phone 3"} ?>';
	lang.phone4_label = '<?php echo $translator->{"Phone 4"} ?>';
	lang.fax_label = '<?php echo $translator->{"Fax number"} ?>';
	lang.website_label = '<?php echo $translator->{"Website"} ?>';
	lang.email_label = '<?php echo $translator->{"E-mail"} ?>';
	lang.password_label = '<?php echo $translator->{"Password"} ?>';
	lang.password_repeat_label = '<?php echo $translator->{"Password again (repeat to confirm)"} ?>';
	lang.password_standard = '<?php echo $translator->{"Your password has to be at least 8 characters long, contain at least 2 numeric characters and 1 capital letter."} ?>';
	lang.forgot_pass = '<?php echo $translator->{"Forgot your password?"} ?>';
	lang.forgot_user = '<?php echo $translator->{"Forgot your username?"} ?>';
	lang.save_label = '<?php echo $translator->{"Save"} ?>';
	lang.alias_label = '<?php echo $translator->{"Username"} ?>';
	lang.company_section = '<?php echo $translator->{"Company"} ?>';
	lang.invoice_section = '<?php echo $translator->{"Billing address"} ?>';
	lang.contact_section = '<?php echo $translator->{"Contact"} ?>';
	lang.contact_email = '<?php echo $translator->{"Contact Email"} ?>';
	lang.copy_label = '<?php echo $translator->{"Copy from company details"} ?>';
	lang.email_exists_label = '<?php echo $translator->{"The email address already exists in our system"} ?>';
  
  form_register = '<?php echo Form::LoadForJS("userdata", array('popup'=>true, "action"=>"user/register".(isset($fair_url)?'/'.$fair_url:''))); ?>';
</script>
<?php if (userLevel() > 0): ?>
<script type="text/javascript">
	function confirmBox(evt, message, url) {
		evt.preventDefault();
		$('#overlay').show();
		$('#confirmBox .msg').html(message).parent().show();
		$('#confirm_abort').click(function() {
			closeConfirmBox();
		});
		$('#confirm_ok').click(function() {
			closeConfirmBox();
			document.location.href = '<?php echo BASE_URL ?>' + url;
		});
	}
	function closeConfirmBox() {
		$('#overlay').hide();
		$('#confirmBox .msg').html('').parent().hide();
	}
	
	$(document).ready(function() {
		
		$("#fair_select").change(function() {
			if ($(this).val() != '#')
				document.location.href = 'page/loggedin/setFair/' + $(this).val();
		});
		<?php if (isset($_POST['save']) && !isset($error)): ?>
		$("#save_confirm").show();
		$("#save_confirm input").click(function() {
			$(this).parent().parent().fadeOut("fast");
		});
		<?php endif; ?>
		
	});

	
</script>
<?php endif; ?>
</head>
<body>
	<a href="http://www.chartbooker.com/"><img src="images/logo_chartbooker.png" alt="Chartbooker International" id="logo"/></a>
	<?php
	if (userLevel() > 0) {
		$me = new User;
		$me->load($_SESSION['user_id'], 'id');
		echo '<span id="loggedin_user"><a href="user/accountSettings"><img src="images/icons/icon_logga_in.png" alt=""/>'.reset(explode(' ', $me->get('name'))).'</a></span>';
	}
	?>
	<p id="languages">
		<a href="translate/language/eng"<?php if (LANGUAGE == 'eng') { echo ' class="selected"'; } ?>><img src="images/flag_gb.png" alt="English"/>&nbsp;&nbsp;English</a>
		<a href="translate/language/sv"<?php if (LANGUAGE == 'sv') { echo ' class="selected"'; } ?>><img src="images/flag_swe.png" alt="Svenska"/>&nbsp;&nbsp;Svenska</a>
		<a href="translate/language/de"<?php if (LANGUAGE == 'de') { echo ' class="selected"'; } ?>><img src="images/flag_ger.png" alt="Deutsch"/>&nbsp;&nbsp;Deutsch</a>
		<a href="translate/language/es"<?php if (LANGUAGE == 'es') { echo ' class="selected"'; } ?>><img src="images/flag_esp.png" alt="Espanol"/>&nbsp;&nbsp;Español</a>
	</p>
	<div id="overlay"></div>
	<div id="confirmBox">
		<p class="msg"></p>
		<p>
			<input type="button" id="confirm_ok" value="OK"/>
			<input type="button" id="confirm_abort" value="Avbryt"/>
		</p>
	</div>
	<div id="save_confirm">
		<p><?php echo $translator->{'Changes saved'}; ?></p>
		<p><input type="button" value="<?php echo $translator->{'OK'}; ?>"/></p>
	</div>
	<div id="wrapper">
		<div id="header">
			
			<?php
				$bookCount = '';
				$fairCount = '';

				if (userLevel() == 1) {
					echo '<style>
						#header ul li{background-color:#82c979;}
						#header ul li a, #header ul li span{background:none; border:0;}
						#header ul li a:hover, #header ul li span:hover{border-radius:0; background:#82c979; background-image:url(\'images/icons/hover.png\'); background-position:2px 2px; background-repeat:no-repeat;}					
					</style>';
					$db = new Database;
					$stmt = $db->prepare("SELECT rel.fair, fair.name FROM fair_user_relation AS rel LEFT JOIN fair ON rel.fair = fair.id WHERE rel.user = ?");
					$stmt->execute(array($_SESSION['user_id']));
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$opts = '';

					foreach ($result as $res) {
						$opts.= '<li><a href="page/loggedin/setFair/'.$res['fair'].'">'.$res['name'].'</a></li>';
					}
	
					if (userLevel() == 2) {
						$db = new Database;
						$stmt = $db->prepare("SELECT COUNT(*) AS bookings FROM preliminary_booking WHERE fair = ?");
						$stmt->execute(array($_SESSION['user_fair']));
						$result = $stmt->fetch(PDO::FETCH_ASSOC);
						$bookCount = ($result['bookings'] > 0) ? '('.$result['bookings'].')' : '';
					}
				} else if (userLevel() == 2) {
					echo '<style>
						#header ul li{background-color:#82cce4;}
						#header ul li a, #header ul li span{background:none; border:0;}
						#header ul li a:hover, #header ul li span:hover{border-radius:0; background:#82cce4; background-image:url(\'images/icons/hover.png\'); background-position:2px 2px; background-repeat:no-repeat;}					
					</style>';
					$db = new Database;
					$stmt = $db->prepare("SELECT rel.fair, fair.name FROM fair_user_relation AS rel LEFT JOIN fair ON rel.fair = fair.id WHERE rel.user = ?");
					$stmt->execute(array($_SESSION['user_id']));
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$opts = '';
					foreach ($result as $res) {
						$opts.= '<li><a href="page/loggedin/setFair/'.$res['fair'].'">'.$res['name'].'</a></li>';
					}
				} else if (userLevel() == 3) {
					echo '<style>
						#header ul li{background-color:#f9c969;}
						#header ul li a, #header ul li span{background:none; border:0;}
						#header ul li a:hover, #header ul li span:hover{border-radius:0; background:#f9c969; background-image:url(\'images/icons/hover.png\'); background-position:2px 2px; background-repeat:no-repeat;}					
					</style>';
					$db = new Database;
					$stmt = $db->prepare("SELECT id, name FROM fair WHERE created_by = ?");
					$stmt->execute(array($_SESSION['user_id']));
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				} else if (userLevel() == 4) {
					echo '<style>
						#header ul li{background-color:#c7c7c7;}
						#header ul li a, #header ul li span{background:none; border:0;}
						#header ul li a:hover, #header ul li span:hover{border-radius:0; background:#c7c7c7; background-image:url(\'images/icons/hover.png\'); background-position:2px 2px; background-repeat:no-repeat;}					
					</style>';
					$db = new Database;
					$stmt = $db->prepare("SELECT COUNT(*) AS fairs FROM fair WHERE approved = ?");
					$stmt->execute(array(0));
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					$fairCount = ($result['fairs'] > 0) ? '('.$result['fairs'].')' : '';
				}
			?>
			
			
			<?php if (!isset($_SESSION['visitor']) || !$_SESSION['visitor']) { include_once ROOT.'application/views/navigation.php'; } ?>
			
		</div>
		<div id="content">
			<div id="map_content">
			<?php echo ( isset($locked_msg) ) ? '<h1>'.$locked_msg.'</h1>' : '' ; ?>
