<?php
  global $translator;
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
		}

		lang.edit_note_1_label = '<?php echo $edit_note_1_label; ?>';
		lang.edit_note_2_label = '<?php echo $edit_note_2_label; ?>';
		lang.edit_note_3_label = '<?php echo $edit_note_3_label; ?>';

		(function() {
			var current_id = null;

			// Open dialog when user clicks edit link
			$('.edit-reminder-text').click(function(e) {
				e.preventDefault();
				current_id = $(this).data('id');

				open_dialogue = $('#reminder_dialog').show();

				$('#dialog_reminder_note').val($('#reminder_note' + current_id).val());
				$('#reminder_dialog_header').text(lang['edit_note_' + current_id + '_label']);
			});

			// Move dialog textarea's content to form textarea's content
			$('#reminder_save_btn').click(function(e) {
				e.preventDefault();
				$('#reminder_note' + current_id).val($('#dialog_reminder_note').val());
				closeDialogue();
			});

			$('#reminder_cancel_btn').click(function(e) {
				e.preventDefault();
				closeDialogue();
			});

			// Initial state: hide dialog and note's textareas
			$('.reminder-note').hide();
		}());
	});
</script>

<h1><?php echo $edit_headline; ?></h1>
<?php if ($edit_id != 'new'): ?>
<p><a class="button settings" href="fair/maps/<?php echo $edit_id; ?>"><?php echo $map_button_label; ?></a></p>
<?php endif; ?>

<form action="fair/edit/<?php echo $edit_id; ?>" method="post" enctype="multipart/form-data">
	<div class="floatleft">
		<label for="name"><?php echo $name_label; ?><?php echo ($fair_id == 'new') ? ' *' : ''; ?></label>
		<input <?php echo $disable; ?> autocomplete="off"<?php echo ($fair_id == 'new') ? '' : ' disabled="disabled"' ?> type="text" name="name" id="name" value="<?php echo $fair->get('name'); ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo htmlspecialchars($translator->{'This is the name of the event, which is used to create a link to your event, as seen below the text box.'}); ?>" />
		<label for="" style="font-style:italic; width:400px;" id="name_preview"><?php echo BASE_URL ?><span><?php echo $fair->get('url'); ?></span></label>
		
		<label for="max_positions"><?php echo $max_positions_label; ?><?php echo ($fair_id == 'new') ? ' *' : ''; ?></label>
		<input <?php echo $disable; ?> autocomplete="off"<?php echo ($fair_id == 'new') ? '' : ' ' ?> type="text" name="max_positions" id="max_positions" value="<?php echo $fair->get('max_positions'); ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo htmlspecialchars($translator->{'Enter the recommended number of stand spaces which should be available on the event.'}); ?>" />
		
		<label for="windowtitle"><?php echo $window_title_label; ?> *</label>
		<input <?php echo $disable; ?> type="text" name="windowtitle" id="windowtitle" value="<?php echo $fair->get('windowtitle'); ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo htmlspecialchars($translator->{'This is the title which will be shown as the title of the website for your visitors and exhibitors.'}); ?>" />
		
		<?php if (userLevel() == 4 || $edit_id == 'new') { $da = ''; } else { $da = ' disabled="true"'; } ?>
		<label for="auto_publish"><?php echo $auto_publish_label; ?> (DD-MM-YYYY HH:MM <?php echo TIMEZONE; ?>) *</label>
		<input class="datetime datepicker" <?php echo $da; ?> type="text" name="auto_publish" id="auto_publish" value="<?php if ($edit_id != 'new') { echo date('d-m-Y H:i', $fair->get('auto_publish')); } ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo htmlspecialchars($translator->{'Enter a date for when the event should become available for booking.'}); ?>" />
		
		<label for="auto_close"><?php echo $auto_close_label; ?> (DD-MM-YYYY HH:MM <?php echo TIMEZONE; ?>) *</label>
		<input class="datetime datepicker" <?php echo $da; ?> type="text" name="auto_close" id="auto_close" value="<?php if ($edit_id != 'new') { echo date('d-m-Y H:i', $fair->get('auto_close')); } ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo htmlspecialchars($translator->{'Enter a date for when the booking should no longer be available.'}); ?>" />
		
		<!--
		<label for="logo"><?php echo $logo_label; ?></label>
		<input <?php echo $disable; ?> type="file" name="logo" id="logo"/>
		-->

		<?php (empty($disable)) ? tiny_mce() : ''; ?>
		<div><label class="inline-block" for="contact_info"><?php echo $contact_label; ?> *</label>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo htmlspecialchars($translator->{'This is the information which will be shown to visitors and exhibitors when they press the "Contact Us" button from the navigation menu at the top.'}); ?>" /></div>
		<textarea <?php echo $disable; ?> name="contact_info" id="contact_info"><?php echo $fair->get('contact_info'); ?></textarea>

		<?php if (userLevel() == 4): ?>
		<label for="arranger"><?php echo $arranger_label; ?></label>
		<select name="arranger" id="arranger">
			<?php echo makeUserOptions($fair->db, $fair->get('created_by')); ?>
		</select> <?php if ($edit_id != 'new'): ?> <!--<a href="arranger/edit/<?php echo $fair->get('created_by'); ?>">View organizer</a>--> <?php endif; ?>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo htmlspecialchars($translator->{'Choose an organizer who will be treated as the creator of this event.'}); ?>" />
		<?php endif; ?>

		<?php if (userLevel() == 4): ?>
		<label for="approved"><?php echo $approved_label; ?></label>
		<select name="approved" id="approved">
			<option value="0"<?php echo $app_sel0; ?>><?php echo $app_opt0; ?></option>
			<option value="1"<?php echo $app_sel1; ?>><?php echo $app_opt1; ?></option>
			<option value="2"<?php echo $app_sel2; ?>><?php echo $app_opt2; ?></option>
		</select>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo htmlspecialchars($translator->{'Select the initial status of the event. You can immediately approve the new event or you can do it at a later time.'}); ?>" />
		<?php endif; ?>

		<label for="hidden"> Hide fair for unauthorized accounts </label>
		<select name="hidden" id="hidden">
			<option value="0"> false </option>
			<option value="1"> true </option>
		</select>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo htmlspecialchars($translator->{'Whether or not other users are able to access the event.'}); ?>" />
	</div>

	<div class="floatright">
		<h2><?php echo $interval_reminders_label; ?> <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo htmlspecialchars($translator->{'Select the amount of days that this reminder should be sent to the exhibitor, before the "reserved until"-date is reached for a reserved stand space.'}); ?>" /></h2>
<?php for ($i = 1; $i <= 3; $i++): ?>
		<p class="form-one-row">
			<label for="reminder_day<?php echo $i; ?>"><?php echo ${'reminder_' . $i . '_label'}; ?></label>
			<select name="reminder_day<?php echo $i; ?>" id="reminder_day<?php echo $i; ?>">
				<option value="0"><?php echo $no_reminder_label; ?></option>
<?php	for ($j = 1; $j <= 365; $j++): ?>
				<option value="<?php echo $j; ?>"<?php if ($j == $fair->get('reminder_day' . $i)) echo ' selected="selected"'; ?>><?php echo $j; ?></option>
<?php	endfor; ?>
			</select>
			<textarea name="reminder_note<?php echo $i; ?>" id="reminder_note<?php echo $i; ?>" class="reminder-note no-editor" cols="50" rows="5"><?php echo htmlspecialchars($fair->get('reminder_note' . $i)); ?></textarea>
			<a href="#" class="edit-reminder-text" data-id="<?php echo $i; ?>"><img src="images/icons/pencil.png" alt="<?php $edit_label; ?>" /></a>
			<img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo htmlspecialchars($translator->{'Edit the message of the reminder'}); ?>" />
		</p>
<?php endfor; ?>
	</div>

	<p class="clear"><input <?php echo $disable; ?> type="submit" name="save" value="<?php echo $save_label; ?>"/></p>

</form>

<div id="reminder_dialog" class="dialogue">
	<div class="dialog-content">
		<h2 id="reminder_dialog_header"></h2>
		<p>
			<textarea id="dialog_reminder_note" class="no-editor" cols="50" rows="5"></textarea>
		</p>
		<p class="right">
			<button type="button" id="reminder_save_btn"><?php echo $save_label; ?></button>
			<button type="button" id="reminder_cancel_btn"><?php echo $cancel_label; ?></button>
		</p>
	</div>
</div>