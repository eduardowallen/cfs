<!DOCTYPE html>
<html>
<head>
<?php
	$unique = '?ver=' . APP_VERSION;
?>
<meta charset="utf-8" />
<meta name="viewport" content="width=1300, initial-scale=0.7, maximum-scale=1.2">
<title><?php echo (isset($_SESSION['fair_windowtitle'])) ? $_SESSION['fair_windowtitle'].' - ' : ''; ?>ChartBooker</title>
<base href="<?php echo BASE_URL; ?>" />
<link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" type="text/css" href="css/generic.css<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/main.css<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" media="print" href="css/print.css<?php echo $unique?>" />
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>
<script type="text/javascript" src="//code.jquery.com/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="//code.jquery.com/ui/1.10.4/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js<?php echo $unique?>"></script>
<script type="text/javascript" src="js/formchecker.js<?php echo $unique?>"></script>
<script type="text/javascript" src="js/passwd_meter.js<?php echo $unique?>"></script>
<script type="text/javascript" src="js/jquery.tablesorter.min.js<?php echo $unique?>"></script>
<script type="text/javascript" src="js/jquery.floatThead.min.js"></script>
<script type="text/javascript" src="js/chartbooker.js<?php echo $unique?>"></script>
<script type="text/javascript" src="js/tiny_mce/tiny_mce.js<?php echo $unique?>"></script>

<script type="text/javascript">
	$(document).ready(function() {
    $( document ).tooltip(); // Initialize jQueryUI tooltips
	});
	var lang = {};
	lang.login_username = '<?php echo ujs($translator->{"Username"}); ?>';
	lang.login_password = '<?php echo ujs($translator->{"Password"}); ?>';
	lang.company_label = '<?php echo ujs($translator->{"Company"}); ?>';
	lang.commodity_label = '<?php echo ujs($translator->{"Commodity"}); ?>';
	lang.presentation_label = '<?php echo ujs($translator->{"Presentation"}); ?>';
	lang.category_label = '<?php echo ujs($translator->{"Category"}); ?>';
	lang.customer_nr_label = '<?php echo ujs($translator->{"Customer number"}); ?>';
	lang.contact_label = '<?php echo ujs($translator->{"Contact person"}); ?>';
	lang.orgnr_label = '<?php echo ujs($translator->{"Organization number"}); ?>';
	lang.address_label = '<?php echo ujs($translator->{"Address"}); ?>';
	lang.zipcode_label = '<?php echo ujs($translator->{"Zip code"}); ?>';
	lang.city_label = '<?php echo ujs($translator->{"City"}); ?>';
	lang.invoice_company_label = '<?php echo ujs($translator->{"Company"}); ?>';
	lang.invoice_address_label = '<?php echo ujs($translator->{"Address"}); ?>';
	lang.invoice_zipcode_label = '<?php echo ujs($translator->{"Zip code"}); ?>';
	lang.invoice_city_label = '<?php echo ujs($translator->{"City"}); ?>';
	lang.invoice_email_label = '<?php echo ujs($translator->{"E-mail"}); ?>';
	lang.country_label = '<?php echo ujs($translator->{"Country"}); ?>';
	lang.phone1_label = '<?php echo ujs($translator->{"Phone 1"}); ?>';
	lang.phone2_label = '<?php echo ujs($translator->{"Phone 2"}); ?>';
	lang.phone3_label = '<?php echo ujs($translator->{"Phone 3"}); ?>';
	lang.phone4_label = '<?php echo ujs($translator->{"Phone 4"}); ?>';
	lang.fax_label = '<?php echo ujs($translator->{"Fax number"}); ?>';
	lang.website_label = '<?php echo ujs($translator->{"Website"}); ?>';
	lang.email_label = '<?php echo ujs($translator->{"E-mail"}); ?>';
	lang.password_label = '<?php echo ujs($translator->{"Password"}); ?>';
	lang.password_repeat_label = '<?php echo ujs($translator->{"Password again (repeat to confirm)"}); ?>';
	lang.password_standard = '<?php echo ujs($translator->{"Your password has to be at least 8 characters long, contain at least 2 numeric characters and 1 capital letter."}); ?>';
	lang.passwd_superstrong = '<?php echo ujs($translator->{"Superstrong"}); ?>';
	lang.passwd_strong = '<?php echo ujs($translator->{"Strong"}); ?>';
	lang.passwd_medium = '<?php echo ujs($translator->{"Medium"}); ?>';
	lang.passwd_weak = '<?php echo ujs($translator->{"Weak"}); ?>';
	lang.forgot_pass = '<?php echo ujs($translator->{"Forgot your password?"}); ?>';
	lang.forgot_user = '<?php echo ujs($translator->{"Forgot your username?"}); ?>';
	lang.save_label = '<?php echo ujs($translator->{"Save"}); ?>';
	lang.send_label = '<?php echo ujs($translator->{"Send"}); ?>';
	lang.alias_label = '<?php echo ujs($translator->{"Username"}); ?>';
	lang.company_section = '<?php echo ujs($translator->{"Company"}); ?>';
	lang.invoice_section = '<?php echo ujs($translator->{"Billing address"}); ?>';
	lang.contact_section = '<?php echo ujs($translator->{"Contact"}); ?>';
	lang.contact_email = '<?php echo ujs($translator->{"Contact Email"}); ?>';
	lang.copy_label = '<?php echo ujs($translator->{"Copy from company details"}); ?>';
	lang.email_exists_label = '<?php echo ujs($translator->{"The email address already exists in our system"}); ?>';
	lang.timezone = '<?php echo TIMEZONE; ?>';
	lang.messageToOrganizer = '<?php echo ujs($translator->{"Message to organizer"}); ?>';
	lang.ok = '<?php echo ujs($translator->{"OK"}); ?>';
	lang.cancel = '<?php echo ujs($translator->{"Cancel"}); ?>';
	lang.search = '<?php echo ujs($translator->{"Search"}); ?>';
	lang.edit = '<?php echo ujs($translator->{"Edit"}); ?>';
	lang.delete = '<?php echo ujs($translator->{"Delete"}); ?>';
	lang.validation_error = '<?php echo ujs($translator->{"There are # errors in the form. You have to enter information in all the fields marked with a *"}); ?>';
	lang.export_headline = '<?php echo ujs($translator->{'Please choose other fields to export if necessary:'}); ?>';
	lang.export_excel = '<?php echo ujs($translator->{'Export as Excel document'}); ?>';
	lang.sms_enter_message = '<?php echo ujs($translator->{'Enter your message'}); ?>';
	lang.sms_max_chars = '<?php echo ujs($translator->{'Max 640 characters'}); ?>';
	lang.sms_log = '<?php echo ujs($translator->{'SMS log'}); ?>';
	lang.errors = '<?php echo ujs($translator->{'Errors'}); ?>';
	lang.sms_sent_correct = '<?php echo ujs($translator->{'SMS successfully sent!'}); ?>';
	lang.sms_num_recipients = '<?php echo ujs($translator->{'Number of recipients for this dispatch'}); ?>';
	lang.sms_estimated_cost = '<?php echo ujs($translator->{'Estimated cost'}); ?>';
	lang.select_all = '<?php echo ujs($translator->{'Select all'}); ?>';
	lang.ask_before_leave = '<?php echo ujs($translator->{'Are you sure you want to leave this dialog? Any unsaved changes will be lost.'}); ?>';
</script>
<?php if (userLevel() > 0): ?>
<script type="text/javascript">
	
	function confirmBox(evt, message, url, type) {
		evt.preventDefault();
		$('#overlay').show();
		$('#confirmBox .msg').html(message).parent().show();

		$('#confirmBox .dialog-buttons').hide();
		type = (typeof type === 'undefined' ? 'OK_CANCEL' : type);
		$('#confirmBox' + type).show();

		$('#confirm_' + (type === 'OK_CANCEL' ? 'abort' : 'no')).click(function() {
			closeConfirmBox();
		});
		$('#confirm_' + (type === 'OK_CANCEL' ? 'ok' : 'yes')).click(function() {
			closeConfirmBox();
			if (typeof url === 'function') {
				url();
			} else {
				document.location.href = '<?php echo BASE_URL ?>' + url;
			}
		});
	}
	function closeConfirmBox() {
		$('#overlay').hide();
		$('#confirmBox .msg').html('').parent().hide();
		$('#confirmBox .dialog-text').remove();
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
	<a href="http://www.chartbooker.com/"><img src="images/logo_chartbooker.png" alt="Chartbooker International" id="logo"/></a><p id="logo-text">Fair system v<?php echo APP_VERSION; ?></p>
	<?php
/*	if (userLevel() > 0) {
		$me = new User;
		$me->load($_SESSION['user_id'], 'id');
		echo '<span id="loggedin_user"><a href="user/accountSettings"><img src="images/icons/icon_logga_in.png" alt=""/>'.reset(explode(' ', $me->get('name'))).'</a></span>';
	}*/
	?>
	<p id="languages">
		<a href="translate/language/eng"<?php if (LANGUAGE == 'eng') { echo ' class="selected"'; } ?>>English&nbsp;&nbsp;<img height="20" width="30" src="images/flag_english.png" alt="English"/></a>
		<a href="translate/language/sv"<?php if (LANGUAGE == 'sv') { echo ' class="selected"'; } ?>>Svenska&nbsp;&nbsp;<img height="20" width="30" src="images/flag_swedish.png" alt="Svenska"/></a>
		<a href="translate/language/de"<?php if (LANGUAGE == 'de') { echo ' class="selected"'; } ?>>Deutsch&nbsp;&nbsp;<img height="20" width="30" src="images/flag_german.png" alt="Deutsch"/></a>
		<a href="translate/language/es"<?php if (LANGUAGE == 'es') { echo ' class="selected"'; } ?>>Español&nbsp;&nbsp;<img height="20" width="30" src="images/flag_spanish.png" alt="Espanol"/></a>
	</p>
	<div id="overlay"></div>
	<div id="confirmBox">
		<p class="msg"></p>
		<p class="dialog-buttons" id="confirmBoxOK_CANCEL">
			<input type="button" id="confirm_ok" value="<?php echo uh($translator->{'OK'}); ?>"/>
			<input type="button" id="confirm_abort" value="<?php echo uh($translator->{'Cancel'}); ?>"/>
		</p>
		<p class="dialog-buttons" id="confirmBoxYES_NO">
			<input type="button" id="confirm_yes" value="<?php echo uh($translator->{'Yes'}); ?>"/>
			<input type="button" id="confirm_no" value="<?php echo uh($translator->{'No'}); ?>"/>
		</p>
	</div>
	<div id="save_confirm">
		<p><?php echo uh($translator->{'Changes saved'}); ?></p>
		<p><input type="button" value="<?php echo uh($translator->{'OK'}); ?>"/></p>
	</div>
	<div id="wrapper">
		<div id="header">
			
			<?php
				$bookCount = '';
				$fairCount = '';
	
				if (userLevel() == 1) {
				/*	echo '<style>
						#header ul li{background-color:#73d367;}
						#header ul li a, #header ul li span{background:none; border:0;}
						#header ul li a:hover, #header ul li span:hover{border-radius:0; background:#73d367; background-image:url(\'images/icons/hover.png\'); background-position:2px 2px; background-repeat:no-repeat;}					
					</style>';*/
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
				/*	echo '<style>
						#header ul li{background-color:#82cce4;}
						#header ul li a, #header ul li span{background:none; border:0;}
						#header ul li a:hover, #header ul li span:hover{border-radius:0; background:#82cce4; background-image:url(\'images/icons/hover.png\'); background-position:2px 2px; background-repeat:no-repeat;}					
					</style>';*/
					$db = new Database;
					$stmt = $db->prepare("SELECT rel.fair, fair.name FROM fair_user_relation AS rel LEFT JOIN fair ON rel.fair = fair.id WHERE rel.user = ?");
					$stmt->execute(array($_SESSION['user_id']));
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$opts = '';
					foreach ($result as $res) {
						$opts.= '<li><a href="page/loggedin/setFair/'.$res['fair'].'">'.$res['name'].'</a></li>';
					}
				} else if (userLevel() == 3) {
				/*	echo '<style>
						#header ul li{background-color:#f9c969;}
						#header ul li a, #header ul li span{background:none; border:0;}
						#header ul li a:hover, #header ul li span:hover{border-radius:0; background:#f9c969; background-image:url(\'images/icons/hover.png\'); background-position:2px 2px; background-repeat:no-repeat;}					
					</style>';*/
					$db = new Database;	
					$stmt = $db->prepare("SELECT id, name FROM fair WHERE created_by = ?");
					$stmt->execute(array($_SESSION['user_id']));
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$opts = '';
					foreach ($result as $res) {
						$opts.= '<li><a href="page/loggedin/setFair/'.$res['id'].'">'.$res['name'].'</a></li>';
					}
				} else if (userLevel() == 4) {
				/*	echo '<style>
						#header ul li{background-color:#c7c7c7;}
						#header ul li a, #header ul li span{background:none; border:0;}
						#header ul li a:hover, #header ul li span:hover{border-radius:0; background:#c7c7c7; background-image:url(\'images/icons/hover.png\'); background-position:2px 2px; background-repeat:no-repeat;}					
					</style>';*/
					$db = new Database;
					$stmt = $db->prepare("SELECT COUNT(*) AS fairs FROM fair WHERE approved = ?");
					$stmt->execute(array(0));
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					$fairCount = ($result['fairs'] > 0) ? '('.$result['fairs'].')' : '';
				}
			?>
			
			
			<?php include_once ROOT.'application/views/navigation.php'; ?>
			
		</div>
		<div id="content">
			<?php echo ( isset($locked_msg) ) ? '<h1>'.$locked_msg.'</h1>' : '' ; ?>
