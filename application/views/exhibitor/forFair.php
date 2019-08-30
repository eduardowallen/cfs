<?php
  global $translator;
  if(!$hasRights):
?>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>start/home'"><?php echo uh($translator->{'Go back'}); ?></button>
	<p><?php echo uh($translator->{'You are not authorized to administer this fair.'}); ?></p>
<?php
    return;
  endif;

$general_column_info = array(
	$translator->{"Company"} => array(
		'orgnr' => $translator->{'Organization number'},
		'company' => $translator->{'Company'},
		'commodity' => $translator->{'Commodity'},
		'address' => $translator->{'Address'},
		'zipcode' => $translator->{'Zip code'},
		'city' => $translator->{'City'},
		'country' => $translator->{'Country'},
		'phone1' => $translator->{'Phone 1'},
		'phone2' => $translator->{'Phone 2'},
		'email' => $translator->{'E-mail'},
		'website' => $translator->{'Website'}
	),
	$translator->{"Billing address"} => array(
		'invoice_company' => $translator->{'Company'},
		'invoice_address' => $translator->{'Address'},
		'invoice_zipcode' => $translator->{'Zip code'},
		'invoice_city' => $translator->{'City'},
		'invoice_country' => $translator->{'Country'},
		'invoice_email' => $translator->{'E-mail'}
	),
	$translator->{"Contact person"} => array(
		'name' => $translator->{'Contact person'},
		'contact_phone' => $translator->{'Contact Phone'},
		'contact_phone2' => $translator->{'Contact Phone 2'},
		'contact_email' => $translator->{'Contact Email'}
	)
);

$bookings_columns = array(
	$translator->{"Exhibitor"} => array(
		'status' => $translator->{'Status'},
		'fair_count' => $th_fairs,
		'last_login' => $th_last_login
	)
);
$bookings_columns = array_merge($bookings_columns, $general_column_info);

$connected_columns = array(
	$translator->{"Exhibitor"} => array(
		'status' => $translator->{'Status'},
		'fair_count' => $th_fairs,
		'last_login' => $th_last_login,
		'connected_time' => $th_connect_time
	)
);
$connected_columns = array_merge($connected_columns, $general_column_info);
?>

<script type="text/javascript" src="js/tablesearch.js<?php echo $unique?>"></script>
<script type="text/javascript">
	var export_fields = {
		booked: <?php echo json_encode($bookings_columns); ?>,

		connected: <?php echo json_encode($connected_columns); ?>

	};
</script>
<?php if (!empty($mail_errors)): ?>
  <script>
  showInfoDialog('<?php echo implode('<br>', $mail_errors); ?>', '<?php echo $error_title; ?>');
  </script>
<?php endif; ?>
<?php if (!empty($success)): ?>
  <script>
  showInfoDialog('<?php echo $created_success; ?>', '<?php echo $success_title; ?>');
  </script>
<?php endif; ?>
<style>
	#content {max-width: 1280px;}
</style>
<script type="text/javascript">
$(document).ready(function() {
	$('form #email_check').poshytip({
		className: 'tip-yellowsimple',
		showOn: 'focus',
		alignTo: 'target',
		alignX: 'left',
		alignY: 'center',
		offsetX: 10,
		showTimeout: 100
	});

$("form #email_check").on('keyup focus blur', function() {
		$(this).removeClass("input_error");
		if (isValidEmailAddress($(this).val())) {
			var input = $(this);
			input.data('valid', true);
			if (input.val() != input.attr('value')) {
				$.ajax({
					url: 'ajax/maptool.php',
					type: 'POST',
					data: 'emailExists=1&email=' + input.val(),
					success: function(response) {
						var ans = JSON.parse(response);
						if (ans.emailExists) {
							input.poshytip('update', lang.email_exists_err, true);
							input.removeClass("input_ok");
							input.addClass("input_error emailExists");
							input.data('valid', false);
						} else {
							input.poshytip('update', lang.email_ok, true);
							input.removeClass("input_error emailExists");
							input.addClass("input_ok");
							input.data('valid', true);
						}
					}
				});
			}
		}
});
});
</script>
<?php
	$fair = new Fair;
	$fair->loadsimple($_SESSION['user_fair'], 'id');
?>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>start/home'"><?php echo uh($translator->{'Go back'}); ?></button>
	<form action="" method="post" class="floatright">
		<label for="email_check"><?php echo uh($translator->{'Check if e-mail is registered on account'}); ?></label>
		  <input type="text" autocomplete="off" name="email_check" id="email_check" title="<?php echo uh($translator->{"Insert an email address to check if it's registered within CFS."}); ?>" placeholder="<?php echo uh($translator->{"Check for email"}); ?>"/>
		  <input type="submit" disabled="disabled" style="opacity:0;">
	</form>
	<h1><?php echo $fair->get('name'); ?> - <?php echo $headline; ?></h1>

	<div role="tabpanel">
		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="javascript:void(0)" id="booked" class="tabs-tab" aria-controls="home" role="tab" data-toggle="tab"><?php echo uh($translator->{'Exhibitors with stands tab'}); ?> (<?php echo count($users); ?>)</a></li>
			<li role="presentation"><a href="javascript:void(0)" id="connected" class="tabs-tab" aria-controls="profile" role="tab" data-toggle="tab"><?php echo uh($translator->{'Connected Exhibitors tab'}); ?> (<?php echo count($connected); ?>)</a></li>
		</ul>
	</div>

  <!-- Tab panes -->
	<div class="tab-content">
		<div role="tabpanel" class="tab-pane active" id="booked">

		<script>

		$(document).ready(function() {
		    // go to the latest tab, if it exists:
		    var lastTab = localStorage.getItem('lastTabExhibitors');
		    if (lastTab) {
				var selected = lastTab;
				var div = 'div#' + selected;
				$('.tab-div').css('display', 'none');
				$('li').removeClass('active');
				$(this).parent().attr('class', 'active');
				$(div).css('display', 'block');
				if (!$(div + ' table').hasClass('scrolltable')) {
					useScrolltable($(div + ' table'));
				}
				$(selected).floatThead('reflow');
				$(selected).floatThead('getSizingRow');
				$('[id="' + lastTab + '"]').tab('show');

		    } else {
				var selected = 'booked';
				var div = 'div#' + selected;
				$('.tab-div').css('display', 'none');
				$('li').removeClass('active');
				$(this).parent().attr('class', 'active');
				$(div).css('display', 'block');
				if (!$(div + ' table').hasClass('scrolltable')) {
					useScrolltable($(div + ' table'));
				}
		    }
		});
			$('.tabs-tab').on("click", function() {
				var selected = $(this).attr('id');
				var div = 'div#' + selected;
				$('.tab-div').css('display', 'none');
				$('li').removeClass('active');
				$(this).parent().attr('class', 'active');
				$(div).css('display', 'block');
				if (!$(div + ' table').hasClass('scrolltable')) {
					useScrolltable($(div + ' table'));
				}
				localStorage.setItem('lastTabExhibitors', $(this).attr('id'));
			});

		</script>

		<div id="booked" style="display:none" class="tab-div tab-div-hidden">

		<h2 class="tblsite"><?php echo $table_exhibitors ?></h2>

		<?php if (count($users) > 0): ?>

		<form action="exhibitor/exportForFair/1" method="post">
			<div class="floatright right">
			<?php 
			if ($smsMod === 'active' && !isset($event_locked)) { ?>
				<button type="submit" class="open-sms-send" name="send_sms" title="<?php echo uh($send_sms_label); ?>" data-for="booked" data-fair="<?php echo $_SESSION['user_fair']; ?>"></button>
			<?php } ?>
				<button type="submit" class="open-excel-export" name="export_excel" title="<?php echo uh($export); ?>" data-for="booked"></button>
			</div>

			<table class="std_table use-scrolltable" id="booked">
			<?php if (userLevel() > 2): ?>

			<?php endif; ?>
				<thead>
					<tr>
						<th class="left"><?php echo $th_company ?></th>
						<th class="left"><?php echo $th_contactperson ?></th>
						<th class="left"><?php echo $th_commodity ?></th>
						<th><?php echo $th_fairs ?></th>
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $th_last_login ?></th>
						<th data-sorter="false"><?php echo $tr_comments; ?></th>
						<th data-sorter="false">
							<input type="checkbox" id="check-all-booked" class="check-all" data-group="rows-1" />
							<label class="squaredFour" for="check-all-booked" />
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($users as $user): ?>
						<tr>
							<td class="left"><a href="exhibitor/profile/<?php echo $user->get('id'); ?>" class="showProfileLink"><?php echo $user->get('company'); ?></a></td>
							<td class="left"><a href="exhibitor/profile/<?php echo $user->get('id'); ?>" class="showProfileLink"><?php echo $user->get('name'); ?></a></td>
							<td class="left"><?php echo $user->get('commodity'); ?></td>
							<td class="center"><?php echo $user->get('fair_count');?></td>
							<td><?php echo printTime($user->get('last_login'));?></td>
							<td class="center">
								<a href="#" class="js-show-comment-dialog" data-user="<?php echo $user->get('id'); ?>" title="<?php echo $tr_comments; ?>">
									<img src="<?php echo BASE_URL; ?>images/icons/notes.png" class="icon_img" alt="<?php echo $tr_comments; ?>" />
								</a>
							</td>
							<td class="center"><input type="checkbox" name="rows[]" value="<?php echo $user->get('id'); ?>" data-userid="<?php echo $user->get('id'); ?>" class="rows-1" /><label class="squaredFour" for="<?php echo $user->get('id'); ?>" /></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</form>

			<?php else : ?>
				<p><?php echo uh("There are no exhibitors with bookings for this fair.")?></p>
			<?php endif;?>
			</div>
		</div>

	<div role="tabpanel" class="tab-pane" id="connected">
		<div id="connected" style="display:none" class="tab-div tab-div-hidden">
		<h2 class="tblsite"><?php echo $table_connected ?></h2>

		<?php if(count($connected) > 0 ) : ?>

		<form action="exhibitor/exportForFair/2" method="post">
			<div class="floatright right">
			<?php 
			$fair = new Fair;
			$fair->loadsimple($_SESSION['user_fair'], 'id');
				if ($smsMod === 'active' && !isset($event_locked)) { ?>
				<button type="submit" class="open-sms-send" name="send_sms" title="<?php echo uh($send_sms_label); ?>" data-for="connected" data-fair="<?php echo $_SESSION['user_fair']; ?>"></button>
			<?php } ?>
				<button type="submit" class="open-excel-export" name="export_excel" title="<?php echo uh($export); ?>" data-for="connected"></button>
			</div>

			<table class="std_table use-scrolltable" id="connected">
				<thead>
					<tr>
						<th class="left"><?php echo $th_company ?></th>
						<th class="left"><?php echo $th_contactperson ?></th>
						<th class="left"><?php echo $th_commodity ?></th>
						<th><?php echo $th_fairs ?></th>
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $th_last_login ?></th>
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $th_connect_time ?></th>
						<th data-sorter="false"><?php echo $tr_comments; ?></th>
						<th data-sorter="false">
							<input type="checkbox" id="check-all-interested" class="check-all" data-group="rows-2" />
							<label class="squaredFour" for="check-all-interested" />
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($connected as $user): ?>
						<tr>
							<td class="left"><a href="exhibitor/profile/<?php echo $user->get('id'); ?>" class="showProfileLink"><?php echo $user->get('company'); ?></a></td>
							<td class="left"><a href="exhibitor/profile/<?php echo $user->get('id'); ?>" class="showProfileLink"><?php echo $user->get('name'); ?></a></td>
							<td class="left"><?php echo $user->get('commodity'); ?></td>
							<td class="center"><?php echo $user->get('fair_count'); ?></td>
							<td><?php echo printTime($user->get('last_login')); ?></td>
							<td><?php if ($user->get('connected_time')) echo printTime($user->get('connected_time')); else echo 'n/a'; ?></td>
							<td class="center">
								<a href="#" class="js-show-comment-dialog" data-user="<?php echo $user->get('id'); ?>" title="<?php echo $tr_comments; ?>">
									<img src="<?php echo BASE_URL; ?>images/icons/notes.png" class="icon_img" alt="<?php echo $tr_comments; ?>" />
								</a>
							</td>
							<td class="center"><input type="checkbox" name="rows[]" value="<?php echo $user->get('id'); ?>" data-userid="<?php echo $user->get('id'); ?>" class="rows-2" /><label class="squaredFour" for="<?php echo $user->get('id'); ?>" /></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</form>

		<?php else : ?>
			<p><?php echo uh("There are no other exhibitors connected.")?></p>
		<?php endif;?>

		</div>
	</div>
</div>