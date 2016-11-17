<!DOCTYPE html>
<html>
<head>
<?php
/*
if ($_SERVER['REMOTE_ADDR'] == '81.230.8.34' || $_SERVER['REMOTE_ADDR'] == '83.253.71.13') {
}else {
die('We are currently updating Chartbooker Fair System to its newest release. The update will be finished at 23:00 Swedish time.');
}
*/
	$unique = '?ver=' . APP_VERSION;
?>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<!--<meta name="viewport" content="width=1300, initial-scale=0.7, maximum-scale=1.2">-->
<meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE" />
<title><?php echo (isset($_SESSION['fair_windowtitle'])) ? $_SESSION['fair_windowtitle'].' - ' : ''; ?>ChartBooker</title>
<base href="<?php echo BASE_URL; ?>" />


<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
<!--<link rel="stylesheet" type="text/css" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" />-->
<link rel="stylesheet" type="text/css" href="css/jquery-ui.min.css<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/jquery-ui.structure.min.css<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/jquery-ui.theme.min.css<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/jquery-ui-timepicker-addon.min.css<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/jquery-multi-step-form.css<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/jquery-confirm.min.css<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/generic.css<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/main.css<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/map.css<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/tip-yellowsimple.css<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/print.css<?php echo $unique?>" media="print" />
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400,600,700" />
<link rel="stylesheet" type="text/css" href="css/main_mobile.css<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/map_mobile.css<?php echo $unique?>" />
<link rel="stylesheet" type="text/css" href="css/component.css<?php echo $unique?>" />
<!--[if IE ]>
  <link href="css/iexplore.css<?php echo $unique?>" rel="stylesheet" type="text/css">
  <link href="css/iexplore_generic.css<?php echo $unique?>" rel="stylesheet" type="text/css">
<![endif]-->
<script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
<!--<script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>-->
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-timepicker-addon.min.js"></script>
<?php if (isset($_COOKIE['language']) && $_COOKIE['language'] == 'sv'): ?>
<script type="text/javascript" src="js/jquery-ui-timepicker-sv.js<?php echo $unique?>"></script>
<?php endif; ?>
<?php if (isset($_COOKIE['language']) && $_COOKIE['language'] == 'es'): ?>
<script type="text/javascript" src="js/jquery-ui-timepicker-es.js<?php echo $unique?>"></script>
<?php endif; ?>
<script type="text/javascript" src="js/jquery-confirm.min.js"></script>
<script type="text/javascript" src="js/jquery.easing.min.js"></script>
<script type="text/javascript" src="js/jquery-multi-step-form.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="js/jquery.poshytip.min.js"></script>
<script type="text/javascript" src="js/jquery.floatThead.min.js"></script>
<script type="text/javascript" src="js/jquery.form.min.js"></script>
<script type="text/javascript" src="js/jquery.mobile-events.min.js"></script>
<script type="text/javascript" src="js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/bootstrap-formhelpers.min.js"></script>
<script type="text/javascript" src="js/formchecker.js<?php echo $unique?>"></script>
<script type="text/javascript" src="js/passwd_meter.js<?php echo $unique?>"></script>
<script type="text/javascript" src="js/mobilecheck.js<?php echo $unique?>"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.6/hammer.min.js"></script>
<script type="text/javascript" src="js/chartbooker.js<?php echo $unique?>"></script>
<script type="text/javascript" src="js/maptool.js<?php echo $unique?>"></script>
<script type="text/javascript" src="js/tiny_mce/tiny_mce.js<?php echo $unique?>"></script>
<script type="text/javascript" src="js/modernizr.custom.js<?php echo $unique?>"></script>
<script type="text/javascript" src="js/alphanum.js<?php echo $unique?>"></script>

<script type="text/javascript">
	var lang = {};
	// User form error labels
	lang.email_exists_err = '<?php echo ujs($translator->{"The email address you entered is already registered with another user."}); ?>';
	lang.email_exists = '<?php echo ujs($translator->{"The email address already exists in our systems."}); ?>';
	lang.email_err = '<?php echo ujs($translator->{"Insert a valid email."}); ?>';
	lang.email_insert = '<?php echo ujs($translator->{"Insert your email address."}); ?>';
	lang.name_err = '<?php echo ujs($translator->{"Insert a name."}); ?>';
	lang.orgnr_err = '<?php echo ujs($translator->{"Insert a organization number for your company."}); ?>';
	lang.company_err = '<?php echo ujs($translator->{"Insert a company name."}); ?>';
	lang.commodity_err = '<?php echo ujs($translator->{"Insert the commodity of your company."}); ?>';
	lang.address_err = '<?php echo ujs($translator->{"Insert an address."}); ?>';
	lang.zipcode_err = '<?php echo ujs($translator->{"Insert a zipcode."}); ?>';
	lang.city_err = '<?php echo ujs($translator->{"Insert the city your company resides in."}); ?>';
	lang.website_err = '<?php echo ujs($translator->{"Insert a website address."}); ?>';
	lang.country_err = '<?php echo ujs($translator->{"Select a country."}); ?>';
	lang.phone_err = '<?php echo ujs($translator->{"Insert a valid phone number."}); ?>';

	lang.invoice_email_err = '<?php echo ujs($translator->{"Insert a valid email for the invoice company."}); ?>';
	lang.invoice_company_err = '<?php echo ujs($translator->{"Insert a name for the invoice company."}); ?>';
	lang.invoice_address_err = '<?php echo ujs($translator->{"Insert an address for the invoice company."}); ?>';
	lang.invoice_zipcode_err = '<?php echo ujs($translator->{"Insert a zipcode for the invoice company."}); ?>';
	lang.invoice_city_err = '<?php echo ujs($translator->{"Insert a city for the invoice company."}); ?>';
	lang.invoice_country_err = '<?php echo ujs($translator->{"Select a country for the invoice company."}); ?>';

	lang.contact_email_err = '<?php echo ujs($translator->{"Insert a valid contact email."}); ?>';
	lang.alias_exists = '<?php echo ujs($translator->{"The username already exists"}); ?>';
	lang.alias_err = '<?php echo ujs($translator->{"The username can only exist of the symbols a-z, 0-9 and underscores"}); ?>';
	lang.alias_short_err = '<?php echo ujs($translator->{"The username has to be at least 4 characters long"}); ?>';
	lang.info_missing = '<?php echo ujs($translator->{"See info or contact the organizer"}); ?>';
	lang.form_err = '<?php echo ujs($translator->{"Form errors (#)"}); ?>';

	// Password strength labels
	lang.password_standard = '<?php echo ujs($translator->{"Your password has to be at least 8 characters long, contain at least 2 numeric characters and 1 capital letter."}); ?>';
	lang.passwd_superstrong = '<?php echo ujs($translator->{"Superstrong"}); ?>';
	lang.passwd_strong = '<?php echo ujs($translator->{"Strong"}); ?>';
	lang.passwd_medium = '<?php echo ujs($translator->{"Medium"}); ?>';
	lang.passwd_weak = '<?php echo ujs($translator->{"Weak"}); ?>';
	lang.passwd_empty_err = '<?php echo ujs($translator->{"Your password cannot be empty."}); ?>';
	lang.passwd_repeat_err = '<?php echo ujs($translator->{"You must repeat your password."}); ?>';
	lang.passwd_match_err = '<?php echo ujs($translator->{"Your passwords must match."}); ?>';

	// Login labels
	lang.logo_text = '<?php echo ujs($translator->{"Bookings for fairs and events"}); ?>';
	lang.sign_in = '<?php echo ujs($translator->{"Login"}); ?>';
	lang.login_username = '<?php echo ujs($translator->{"Username"}); ?>';
	lang.login_password = '<?php echo ujs($translator->{"Password"}); ?>';
	lang.forgot_pass = '<?php echo ujs($translator->{"Forgot your password?"}); ?>';
	lang.forgot_user = '<?php echo ujs($translator->{"Forgot your username?"}); ?>';

	// Global labels
	lang.confirm = '<?php echo ujs($translator->{"Confirm"}); ?>';
	lang.confirm_yes = '<?php echo ujs($translator->{"Yes"}); ?>';
	lang.confirm_no = '<?php echo ujs($translator->{"No"}); ?>';
	lang.ok = '<?php echo ujs($translator->{"OK"}); ?>';
	lang.hide_dialog_confirm = '<?php echo ujs($translator->{"Are you sure?"}); ?>';
	lang.hide_dialog_info = '<?php echo ujs($translator->{"All changes will be discarded if you close this dialog."}); ?>';
	lang.ask_before_leave = '<?php echo ujs($translator->{"Are you sure you want to leave this dialog? Any unsaved changes will be lost."}); ?>';
	lang.save_label = '<?php echo ujs($translator->{"Save"}); ?>';
	lang.menu_back = '<?php echo ujs($translator->{"Back"}); ?>';
	lang.cancel = '<?php echo ujs($translator->{"Cancel"}); ?>';
	lang.search = '<?php echo ujs($translator->{"Search"}); ?>';
	lang.edit = '<?php echo ujs($translator->{"Edit"}); ?>';
	lang.delete = '<?php echo ujs($translator->{"Delete"}); ?>';
	lang.timezone = '<?php echo TIMEZONE; ?>';

	// List labels
	lang.category = '<?php echo ujs($translator->{"Category"}); ?>';
	lang.select_all = '<?php echo ujs($translator->{"Select all"}); ?>';
	lang.cancel_booking_confirm_text = "<?php echo ujs($translator->{'Are you sure you want to cancel your booking?'}); ?>";
	lang.matching_rows = '<?php echo ujs($translator->{"matching rows."}); ?>';
	lang.amount_no_standspace = '<?php echo ujs($translator->{"(stand space price not included)"}); ?>';

	// MapTool labels
	lang.preliminary_amount = '<?php echo ujs($translator->{"Preliminary amount (SEK):"}); ?>';
	lang.website_label = '<?php echo ujs($translator->{"Website"}); ?>';
	lang.deletion_comment_placeholder = '<?php echo ujs($translator->{"You can leave this field empty if you want."}); ?>';

	// Other labels
	lang.confirmationLinkQuestion1 = '<?php echo ujs($translator->{"Send confirmation link to exhibitor email?"}); ?>';
	lang.confirmationLinkQuestion2 = '<?php echo ujs($translator->{"This will send an email to the selected exhibitors with cloned reservations."}); ?>';
	lang.commodity_label = '<?php echo ujs($translator->{"Commodity"}); ?>';
	lang.messageToOrganizer = '<?php echo ujs($translator->{"Message to organizer (ex: time of arrival, questions, other requests)"}); ?>';
	lang.messageFromExhibitor = '<?php echo ujs($translator->{"Message from exhibitor"}); ?>';
	lang.no_message = '<?php echo ujs($translator->{"No message was given."}); ?>';
	lang.no_commodity = '<?php echo ujs($translator->{"No description."}); ?>';
	lang.reservation = '<?php echo ujs($translator->{"Reservation:"}); ?>';
	lang.booking = '<?php echo ujs($translator->{"Booking:"}); ?>';
	lang.preliminary = '<?php echo ujs($translator->{"Preliminary booking:"}); ?>';
	lang.registration = '<?php echo ujs($translator->{"Registration"}); ?>';
	lang.description = '<?php echo ujs($translator->{"Description"}); ?>';
	lang.space = '<?php echo ujs($translator->{"Space"}); ?>';
	lang.search_exhibitor = '<?php echo ujs($translator->{"Search exhibitor"}); ?>';
	lang.options = '<?php echo ujs($translator->{"Options"}); ?>';
	lang.articles = '<?php echo ujs($translator->{"Articles"}); ?>';
	lang.amount = '<?php echo ujs($translator->{"Amount"}); ?>';
	lang.price = '<?php echo ujs($translator->{"Price"}); ?>';
	lang.net = '<?php echo ujs($translator->{"Net"}); ?>';
	lang.subtotal = '<?php echo ujs($translator->{"Subtotal"}); ?>';
	lang.tax = '<?php echo ujs($translator->{"Tax"}); ?>';
	lang.to_pay = '<?php echo ujs($translator->{"to pay:"}); ?>';
	lang.rounding = '<?php echo ujs($translator->{"Rounding"}); ?>';
	lang.validation_error = '<?php echo ujs($translator->{"There are # errors in the form. You have to enter information in all the fields marked with a *"}); ?>';
	lang.export_headline = '<?php echo ujs($translator->{"Please choose other fields to export if necessary:"}); ?>';
	lang.export_excel = '<?php echo ujs($translator->{"Export as Excel document"}); ?>';
	lang.date_missing_err = '<?php echo ujs($translator->{"Date field cannot be empty"}); ?>';
	lang.date_err = '<?php echo ujs($translator->{"Insert a valid date"}); ?>';

	// SMS Labels
	lang.sms_popup_title = '<?php echo ujs($translator->{"SMS-function"}); ?>';
	lang.sms_enter_message = '<?php echo ujs($translator->{"Enter your message"}); ?>';
	lang.sms_max_chars = '<?php echo ujs($translator->{"Max 640 characters"}); ?>';
	lang.sms_log = '<?php echo ujs($translator->{"SMS log"}); ?>';
	lang.send = '<?php echo ujs($translator->{"Send"}); ?>';
	lang.errors = '<?php echo ujs($translator->{"Errors"}); ?>';
	lang.sms_sent_correct = '<?php echo ujs($translator->{"SMS successfully sent!"}); ?>';
	lang.sms_num_recipients = '<?php echo ujs($translator->{"Number of recipients for this dispatch"}); ?>';
	lang.sms_estimated_cost = '<?php echo ujs($translator->{"Estimated cost"}); ?>';
	lang.sms_accept_before_send = '<?php echo ujs($translator->{"Are you sure that you want to send these text messages?"}); ?>';

	form_register = '<?php echo Form::LoadForJS("userdata", array('popup'=>true, "action"=>"user/register".(isset($fair_url)?'/'.$fair_url:''))); ?>';


  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-75713508-2', 'auto');
  ga('send', 'pageview');

</script>

<?php if (isset($_SESSION['user_fair'])):
		$faircurrency = new Fair();
		$faircurrency->loadsimple($_SESSION['user_fair'], 'id');

		$module_settings = json_decode($faircurrency->get('modules'));
		if (!is_object($module_settings)) {
			$module_settings = new stdClass();
		}

		if (isset($module_settings->smsFunction))
			$smsMod = 'active';
		else
			$smsMod = 'inactive';

		if (isset($module_settings->invoiceFunction))
			$invoiceMod = 'active';
		else
			$invoiceMod = 'inactive';

		if (isset($module_settings->raindanceFunction))
			$raindanceMod = 'active';
		else
			$raindanceMod = 'inactive';
		
		if (isset($module_settings->economyFunction))
			$economyMod = 'active';
		else
			$economyMod = 'inactive';
?>

<?php endif; ?>

<?php if (userLevel() > 0): ?>
<script type="text/javascript">
	function confirmBox(evt, message, action_positive, type, action_negative) {
		evt.preventDefault();
		$('#overlay').show();
		$('#confirmBox .msg').html(message).parent().show();

		$('#confirmBox .dialog-buttons').hide();
		type = (typeof type === 'undefined' ? 'OK_CANCEL' : type);
		$('#confirmBox' + type).show();

		$('#confirm_' + (type === 'OK_CANCEL' ? 'abort' : 'no')).click(function() {
			closeConfirmBox();
			if (typeof action_negative === 'function') {
				action_negative();
			} else if (typeof action_negative === 'string') {
				document.location.href = '<?php echo BASE_URL ?>' + action_negative;
			}
		});
		$('#confirm_' + (type === 'OK_CANCEL' ? 'ok' : 'yes')).click(function() {
			closeConfirmBox();
			if (typeof action_positive === 'function') {
				action_positive();
			} else {
				document.location.href = '<?php echo BASE_URL ?>' + action_positive;
			}
		});
	}
	function closeConfirmBox() {
		$('#overlay').hide();
		$('#confirmBox .msg').html('').parent().hide();
		$('#confirmBox .dialog-text').remove();
	}
	
	function alertBox(evt, message) {
		evt.preventDefault();
		$('#overlay').show();
		$('#alertBox .msg').html(message).parent().show();
	}

	$(document).ready(function() {
		$(".std_table").tablesorter();
		$("#fair_select").change(function() {
			if ($(this).val() != '#')
				document.location.href = 'page/loggedin/setFair/' + $(this).val();
		});
		<?php if (isset($_POST['save']) && !isset($error)): ?>
		$("#save_confirm").show();
		<?php endif; ?>
		
		setTimeout(function() {
			$('#save_confirm').fadeOut('slow');
		}, 2000);
		$("#save_confirm input").click(function() {
			$(this).parent().parent().fadeOut("fast");
		});

		jconfirm.defaults = {
		    title: ' ',
		    content: 'Are you sure to continue?',
		    contentLoaded: function(){
		    },
		    icon: '',
		    confirmButton: lang.ok,
		    cancelButton: lang.cancel,
		    confirmButtonClass: 'btn-default',
		    cancelButtonClass: 'btn-default',
		    theme: 'white',
		    animation: 'zoom',
		    closeAnimation: 'scale',
		    animationSpeed: 500,
		    animationBounce: 1.2,
		    keyboardEnabled: false,
		    rtl: false,
		    confirmKeys: [13], // ENTER key
		    cancelKeys: [27], // ESC key
		    container: 'body',
		    confirm: function () {
		    },
		    cancel: function () {
		    },
		    backgroundDismiss: false,
		    autoClose: false,
		    closeIcon: null,
		    columnClass: 'col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1',
		    onOpen: function(){
		    },
		    onClose: function(){
		    },
		    onAction: function(){
		    }
		};

	});

</script>
<?php endif; ?>

</head>
<body>
	<?php include_once(ROOT.'application/views/analyticstracking.php') ?>
	<!--<a href="http://www.chartbooker.com/"><img src="images/logo_chartbooker.png" alt="Chartbooker International" id="logo"/></a><p id="logo-text">Fair system v<?php echo APP_VERSION; ?></p>-->
	<?php
/*	if (userLevel() > 0) {
		$me = new User;
		$me->load($_SESSION['user_id'], 'id');
		echo '<span id="loggedin_user"><a href="user/accountSettings"><img src="images/icons/icon_logga_in.png" alt=""/>'.reset(explode(' ', $me->get('name'))).'</a></span>';
	}*/
	?>
	<div id="overlay"></div>
	<div id="confirmBox">
		<h2><?php echo ujs($translator->{'This event is hidden'}); ?></h2><br />
		<p class="msg"></p>
		<p class="dialog-buttons" id="confirmBoxOK_CANCEL">
			<?php echo ujs($translator->{'If you want to register for this event, press "Apply"'}); ?>
			<br />
			<br />
			<input type="button" id="confirm_ok" class="greenbutton mediumbutton" value="<?php echo uh($translator->{'Apply'}); ?>"/>
			<input type="button" id="confirm_abort" class="redbutton mediumbutton" value="<?php echo uh($translator->{'Cancel'}); ?>"/>
		</p>
		<p class="dialog-buttons" id="confirmBoxYES_NO">
			<input type="button" id="confirm_yes" class="greenbutton mediumbutton" value="<?php echo uh($translator->{'Yes'}); ?>"/>
			<input type="button" id="confirm_no" class="redbutton mediumbutton" value="<?php echo uh($translator->{'No'}); ?>"/>
		</p>
	</div>
	<div id="save_confirm">
		<p><?php echo uh($translator->{'Changes saved'}); ?></p>
		<p><input type="button" value="OK"/></p>
	</div>
	<!--
	<div id="cfs_info_div">
		<h2><?php echo ujs($translator->{'Chartbooker Fair System Update'}); ?></h2><br />
		<p class="msg" style="word-break:normal;"></p>
				<p><?php echo uh($translator->{'Chartbooker Fair System (CFS) will be updated today 2016-02-14 between 19.00 and 23.00. CFS will be unavailable during this time.'}); ?></p>
				<br />
				<input type="button" id="cfs_info_ok" style="margin-bottom:2em;" class="greenbutton mediumbutton" value="OK"/>
	</div>-->
	<div id="alertBox">
		<h2><?php echo ujs($translator->{'This event is hidden'}); ?></h2><br />
		<p class="msg" style="word-break:normal;"></p>
				<p><?php echo uh($translator->{'If you want to register to this event you will need to login. If you do not yet have an account, click on "NEW USER" after clicking "OK".'}); ?></p>
				<br />
				<input type="button" id="confirm_ok" style="margin-bottom:2em;" class="greenbutton mediumbutton" value="OK"/>
	</div>
	<div id="alertBox2">
		<h2><?php echo ujs($translator->{'This event is hidden'}); ?></h2><br />
			<p style="word-break:normal;"><?php echo uh($translator->{'This fair is hidden and you are not allowed to administer this event. Therefore, you will be redirected to your profile.'}); ?></p>
			<br />
			<p class="dialog-buttons">
				<input type="button" class="greenbutton mediumbutton" id="confirm_ok" value="OK"/>
	</div>

	<?php
		$bookCount = '';
		$fairCount = '';
		$today = time();

		if (userLevel() == 1) {

			$db = new Database;
			$stmt = $db->prepare("SELECT rel.fair, fair.name FROM fair_user_relation AS rel LEFT JOIN fair ON rel.fair = fair.id WHERE rel.user = ? AND fair.event_stop > $today");
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

			$db = new Database;
			$stmt = $db->prepare("SELECT rel.fair, fair.name FROM fair_user_relation AS rel LEFT JOIN fair ON rel.fair = fair.id WHERE rel.user = ?");
			$stmt->execute(array($_SESSION['user_id']));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$opts = '';

			foreach ($result as $res) {
				$opts.= '<li><a href="page/loggedin/setFair/'.$res['fair'].'">'.$res['name'].'</a></li>';
			}

		} else if (userLevel() == 3) {

			$db = new Database;
			$stmt = $db->prepare("SELECT id, name FROM fair WHERE created_by = ?");
			$stmt->execute(array($_SESSION['user_id']));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$opts = '';

			foreach ($result as $res) {
				$opts.= '<li><a href="page/loggedin/setFair/'.$res['id'].'">'.$res['name'].'</a></li>';
			}

		} else if (userLevel() == 4) {

			$db = new Database;
			$stmt = $db->prepare("SELECT COUNT(*) AS fairs FROM fair WHERE approved = ?");
			$stmt->execute(array(0));
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$fairCount = ($result['fairs'] > 0) ? '('.$result['fairs'].')' : '';
		}
	?>

	<?php if (!isset($_SESSION['visitor']) || !$_SESSION['visitor']) { include_once ROOT.'application/views/mobile_navigation.php'; } ?>

	<div id="wrapper">
		<button id="new_header_show" class="ui-widget-content"><?php echo uh($translator->{"Main menu"}); ?></button>
		<button id="new_header_hide"><?php echo uh($translator->{"Hide"}); ?></button>
		<div id="new_header">
			<?php include_once ROOT.'application/views/navigation.php'; ?>
		</div>
		<div id="header">
	</div>
		<div id="content">
			<div id="map_content">
			<?php echo ( isset($locked_msg) ) ? '<h1>'.$locked_msg.'</h1>' : '' ; ?>
