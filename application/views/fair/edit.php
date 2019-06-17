<?php
  global $translator;
  $currency_list = array("SEK", "EUR", "USD", "PEN");
?>
<script type="text/javascript">
	$(document).ready(function() {
		$("#name").keyup(function() {
			var val = $(this).val();
			$.ajax({
				url: 'ajax/fair.php',
				type: 'POST',
				data: 'checkName=' + val,
				success: function(response) {
					res = JSON.parse(response);
					$("#name_preview span").text(res.url);
					if (res.status == 'ok') {
						$("#name_preview span").css("color", "green");
					} else {
						$("#name_preview span").css("color", "red");
					}
				}
			});
		});
/*
	$("form").submit(function() {
		var thisForm = $(this);
		thisForm.data('valid', true);
		var errors = new Array();		
		$("label", thisForm).each(function() {
			//Reset all fields to ok
			$(this).css("color", "#000000");
			//Exclude hidden fields
			if ($(this).parent().parent().is(":visible")) {
				var label = $(this).text();
				if (label.substring(label.length-1) == '*') {
					var input = $("#" + $(this).attr("for"));
					if (input.hasClass('phone-val') && !/^\+?[\d]{5,20}$/.test(input.val())) {
						$(this).css("color", "red");
						errors.push($(this).attr("for"));
					}
				}
			}
		});
		if (errors.length > 0) {
			thisForm.data('valid', false);
			alert(lang.validation_error.replace('#', errors.length));
			return false;
		} else {
			return true;
		}
	});
*/


		// Bind contact_info så att info blir obligatorisk
		var check = setInterval(function(){
			if(strcmp(tinyMCE.get('contact_info'), "undefined")){
				bindMce();
			}
		}, 1);

		function strcmp(a, b)
		{   
		    return (a<b?-1:(a>b?1:0));  
		}
		function bindMce(){
			clearInterval(check);
			tinyMCE.get('contact_info').onKeyUp.add(function(ed, e) {
				$('#contact_info').html(tinyMCE.get('contact_info').getContent());
			});
			tinyMCE.get('hidden_info').onKeyUp.add(function(ed, e) {
				$('#hidden_info').html(tinyMCE.get('hidden_info').getContent());	
			});	
		}

		lang.edit_note_1_label = '<?php echo $edit_note_1_label; ?>';

		(function() {
			var current_id = null;

			// Open dialog when user clicks edit link
			$('.edit-reminder-text').click(function(e) {
				e.preventDefault();
				$('#overlay').show();
				current_id = $(this).data('id');

				open_dialogue = $('#reminder_dialog').show();

				$('#dialog_reminder_note').val($('#reminder_note1').val());
				$('#reminder_dialog_header').text(lang['edit_note_1_label']);
			});

			// Move dialog textarea's content to form textarea's content
			$('#reminder_save_btn').click(function(e) {
				e.preventDefault();
				$('#reminder_note1').val($('#dialog_reminder_note').val());
				closeDialogue();
				$('#overlay').hide();
			});

			$('#reminder_cancel_btn').click(function(e) {
				e.preventDefault();
				closeDialogue();
				$('#overlay').hide();
			});

			// Initial state: hide dialog and note's textareas
			$('.reminder-note').hide();
		}());
	});

</script>
<style> 

.mceEditor > table {
	width: 26em !important;
	height: 15em !important;
}
#contact_info_ifr, #hidden_info_ifr{
	height: 100% !important;
}
span {
	max-width: 35em;
}
#reminder_dialog {
	width: auto !important;
	max-width: 44.5em;
}
/*
#content {
	min-width: 70em !important;
}*/
#dialog_reminder_note {
	width: 100%;
}
#reminder_dialog_header {
	padding: 0em 0em 3em 1em;
	color: #FFF;
	margin-left: -1.2em;
	margin-top: -3.9em;
	word-wrap: break-word;
	overflow: hidden;
}
.dialogue {
	padding-bottom: 0.66em;
}
</style>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>
<br />
<h1><?php echo $edit_headline; ?> <?php echo $fair->get('name'); ?></h1>

<form action="fair/edit/<?php echo $edit_id; ?>" method="post" enctype="multipart/form-data">
	<div class="floatleft">
		<label for="name"><?php echo $name_label; ?><?php echo ($fair_id == 'new') ? ' *' : ''; ?></label>
		<input <?php echo $disable; ?> autocomplete="off"<?php if (userLevel() != 4): ?> <?php echo ($fair_id == 'new') ? '' : ' disabled="disabled"' ?><?php endif;?> type="text" name="name" id="name" value="<?php echo $fair->get('name'); ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'This is the name of the event, which is used to create a link to your event, as seen below the text box.'}); ?>" />
		<label for="" style="font-style:italic; max-width:33.33em;" id="name_preview"><?php echo BASE_URL ?><span><?php echo $fair->get('url'); ?></span></label>

		<label for="windowtitle"><?php echo $window_title_label; ?> *</label>
		<input <?php echo $disable; ?> type="text" name="windowtitle" id="windowtitle" value="<?php echo $fair->get('windowtitle'); ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'This is the title which will be shown as the title of the website for your visitors and exhibitors.'}); ?>" />
		
<!--
	<?php if (userLevel() == 4 || $edit_id == 'new') { $da = ''; } else { $da = ' disabled="true"'; } ?>
-->

<?php $da = ''; ?>

		<label for="event_start"><?php echo $event_start; ?> (DD-MM-YYYY HH:MM) *</label>
		<input class="datetime datepicker" <?php echo $da; ?> type="text" name="event_start" id="event_start" value="<?php if ($edit_id != 'new') { echo date('d-m-Y H:i', $fair->get('event_start')); } ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter a date for when the physical event starts.'}); ?>" />

		<label for="event_stop"><?php echo $event_stop; ?> (DD-MM-YYYY HH:MM) *</label>
		<input class="datetime datepicker" <?php echo $da; ?> type="text" name="event_stop" id="event_stop" value="<?php if ($edit_id != 'new') { echo date('d-m-Y H:i', $fair->get('event_stop')); } ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter a date for when the physical event ends.'}); ?>" />

		<label for="accepted_clone_date"><?php echo $accepted_cloned_reservations; ?> <br>(DD-MM-YYYY HH:MM) *</label>
		<input type="text" class="dialogueInput datetime datepicker" name="accepted_clone_date" id="accepted_clone_date" value="<?php if ($edit_id != 'new') { echo date('d-m-Y H:i', $fair->get('accepted_clone_date')); } ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter a date that cloned reservations are set to when accepted by the Exhibitor.'}); ?>" />

		<label for="default_reservation_date"><?php echo $default_reservation_date; ?> <br>(DD-MM-YYYY HH:MM) *</label>
		<input type="text" class="dialogueInput datetime datepicker" name="default_reservation_date" id="default_reservation_date" value="<?php if ($edit_id != 'new') { echo date('d-m-Y H:i', $fair->get('default_reservation_date')); } ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter a date that will be used when creating new reservations.'}); ?>" />

		<label for="contact_email"><?php echo $contact_email_label; ?> *</label>
		<input type="text" name="contact_email" id="contact_email" value="<?php echo $fair->get('contact_email'); ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter an email address that will be used for automatically generated email (through CFS).'}); ?>" />

		<label for="contact_name"><?php echo $contact_name_label; ?> *</label>
		<input type="text" name="contact_name" id="contact_name" value="<?php echo $fair->get('contact_name'); ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter the name of the contact person that will be used for automatically generated email (through CFS).'}); ?>" />

		<label for="contact_phone"><?php echo $contact_phone_label; ?> *</label>
		<input type="text" name="contact_phone" id="contact_phone" value="<?php echo $fair->get('contact_phone'); ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter a phone number that will be used for automatically generated email (through CFS).'}); ?>" />

		<label for="website"><?php echo $website_label; ?> *</label>
		<input type="text" name="website" id="website" value="<?php echo $fair->get('website'); ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter the website of your event. This will be used for automatically generated email (through CFS).'}); ?>" />

		<?php (empty($disable)) ? tiny_mce() : ''; ?>
		<div><label class="inline-block" for="contact_info"><?php echo $contact_label; ?> *</label>
		<img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'This is the information which will be shown to visitors and exhibitors when they press the "Contact Us" button from the navigation menu at the top.'}); ?>" /></div>
		<textarea <?php echo $disable; ?> name="contact_info" id="contact_info"><?php echo $fair->get('contact_info'); ?></textarea>
	</div>

	<div style="margin-left: 30em;">
	<?php foreach(glob(ROOT.'public/images/fairs/'.$edit_id.'/logotype/*') as $filename) : ?>
	<img <?php if (userLevel() != 4): ?>style="margin-top:3.5em;"<?php endif; ?> src="<?php echo($image_path. '/' . basename($filename) . "\n"); ?>"/>
	<?php endforeach; ?>
		<label for="image"><?php echo $logo_label; ?> <?php echo uh($translator->{'(jpeg, jpg, gif, png, pdf - max 8mb)'}); ?></label>
		<input type="file" name="image" id="image"/>
		<p id="loading" style="display:none"><img src="images/icons/loading.gif" alt="loading..." style="width:8.33em;"/></p>
		<label><?php echo $interval_reminders_label; ?> <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Select the amount of days that this reminder should be sent to the exhibitor, before the "reserved until"-date is reached for a reserved stand space.'}); ?>" /></label>
			<select name="reminder_day1" id="reminder_day1">
				<option value="0"><?php echo $no_reminder_label; ?></option>
<?php	for ($j = 1; $j <= 365; $j++): ?>
				<option value="<?php echo $j; ?>"<?php if ($j == $fair->get('reminder_day1')) echo ' selected="selected"'; ?>><?php echo $j; ?></option>
<?php	endfor; ?>
			</select>
			<textarea name="reminder_note1" id="reminder_note1" class="reminder-note no-editor" cols="50" rows="5"><?php echo htmlspecialchars($fair->get('reminder_note1')); ?></textarea>
			<a href="#" class="edit-reminder-text" data-id="1"><img src="images/icons/pencil.png" alt="<?php echo $edit_label; ?>" title="<?php echo $edit_label; ?>" /></a>
			<img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Edit the message of the reminder'}); ?>" />

		<?php if (userLevel() == 4): ?>
		<label for="arranger"><?php echo $arranger_label; ?></label>
		<select name="arranger" id="arranger">
			<?php echo makeUserOptions($fair->db, $fair->get('created_by')); ?>
		</select> <?php if ($edit_id != 'new'): ?> <!--<a href="arranger/edit/<?php echo $fair->get('created_by'); ?>">View organizer</a>--> <?php endif; ?>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Choose an organizer who will be treated as the creator of this event.'}); ?>" />
		<?php endif; ?>

		<?php if (userLevel() == 4): ?>
		<label for="approved"><?php echo $approved_label; ?></label>
		<select name="approved" id="approved">
			<option value="0"<?php echo $app_sel0; ?>><?php echo $app_opt0; ?></option>
			<option value="1"<?php echo $app_sel1; ?>><?php echo $app_opt1; ?></option>
			<option value="2"<?php echo $app_sel2; ?>><?php echo $app_opt2; ?></option>
		</select>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Select the initial status of the event. You can immediately approve the new event or you can do it at a later time.'}); ?>" />
		<?php endif; ?>

		<label for="currency"><?php echo $currency_label; ?></label>
		<select name="currency" id="currency">
  		<?php foreach($currency_list as $currency) : ?>
  			<?php if($currency == $fair->get('currency')):?>
  				<option value="<?php echo $currency?>" selected><?php echo $currency?></option>
  			<?php else:?>
  				<option value="<?php echo $currency?>"><?php echo $currency?></option>
  			<?php endif?>
  		<?php endforeach; ?>
		</select>
		<br>
		<br>
		<label style="display:inline; margin-left:1em;"><?php echo $hide_fair_for_label; ?><img style="margin-left:1em;" src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Whether or not other users are able to access the event.'}); ?>" /></label>
		<input name="hidden" type="hidden" value="0"></input>
		<input name="hidden" id="hidden" type="checkbox" value="1" <?php echo ($hidden_val); ?>></input>
		<label class="squaredFour" style="float:left;"for="hidden"></label>
		<br>
		<br>
	  	<label style="display:inline; margin-left:1em;"><?php echo $allow_registrations_label; ?></label>
	  	<input name="allow_registrations" type="hidden" value="0"></input>
		<input name="allow_registrations" id="allow_registrations" type="checkbox" value="1" <?php echo ($allow_registrations_val); ?>></input>
		<label class="squaredFour" style="float:left;"for="allow_registrations"></label>
		<br>
		<br>
	  	<label style="display:inline; margin-left:1em;"><?php echo $hide_fair_search_label; ?><img style="margin-left:1em;" src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Whether or not exhibitors will be able to see this event in the Eventsearch page.'}); ?>" /></label>
	  	<input name="hidden_search" type="hidden" value="0"></input>
		<input name="hidden_search" id="hidden_search" type="checkbox" value="1" <?php echo ($hidden_search_val); ?>></input>
		<label class="squaredFour" style="float:left;"for="hidden_search"></label>
		<br />
		<br />
			<?php (empty($disable)) ? tiny_mce() : ''; ?>
			<div><label class="inline-block" for="hidden_info"><?php echo $hidden_info_label; ?></label>
			<img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'This is the information which will be shown to visitors and exhibitors when the fair is hidden.'}); ?>" /></div>
			<textarea <?php echo $disable; ?> name="hidden_info" id="hidden_info"><?php echo $fair->get('hidden_info'); ?></textarea>
		<input <?php echo $disable; ?> type="submit" style="margin-left: 0;" name="save" value="<?php echo $save_label; ?>" class="greenbutton bigbutton" />
	</div>
</form>

<div id="reminder_dialog" class="dialogue popup">
	<br>
	<div class="dialog-content">
		<h2 id="reminder_dialog_header"></h2>
		<p>
			<textarea id="dialog_reminder_note" class="no-editor" cols="50" rows="5"></textarea>
		</p>
		<p style="text-align:center;">
			<button type="button" id="reminder_save_btn" class="link-button greenbutton mediumbutton"><?php echo $save_label; ?></button>
			<button type="button" id="reminder_cancel_btn" class="cancelbutton redbutton mediumbutton"><?php echo $cancel_label; ?></button>
		</p>
	</div>
</div>