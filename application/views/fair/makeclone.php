<script type="text/javascript">
	$(document).ready(function() {
		var submit_allowed = false;

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

		$('#fair_clone_form').on('submit', function(e) {
			var form = $(this);

			if (!submit_allowed && form.data('valid')) {
				confirmBox(e, '<?php echo $dialog_clone_complete_info; ?>', function() {
					submit_allowed = true;
					form.submit();
				});
			}
		});

		$(document.body).on("keydown", function (e) {
				//Disable enter
				if (e.which === 13) {
					e.preventDefault();
				}
			});
	});
</script>
<?php tiny_mce(); ?>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>

<br />
<h1><?php echo $clone_headline; ?></h1>

<form action="fair/makeclone/<?php echo $edit_id; ?>" method="post" id="fair_clone_form">
	<div class="form_column floatleft">
		<label for="name"><?php echo $name_label; ?> *</label>
		<input autocomplete="off" type="text" name="name" id="name" />
		<label style="font-style:italic; width:400px;" id="name_preview"><?php echo BASE_URL ?><span></span></label>
		
		<label for="windowtitle"><?php echo $window_title_label; ?> *</label>
		<input type="text" name="windowtitle" id="windowtitle" value="<?php echo $fair->get('windowtitle'); ?>"/>

		<label for="event_start"><?php echo $event_start; ?> (DD-MM-YYYY HH:MM) *</label>
		<input class="datetime datepicker" type="text" name="event_start" id="event_start" />
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter a date for when the physical event starts.'}); ?>" />

		<label for="event_stop"><?php echo $event_stop; ?> (DD-MM-YYYY HH:MM) *</label>
		<input class="datetime datepicker" type="text" name="event_stop" id="event_stop" />
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter a date for when the physical event ends.'}); ?>" />

		<label for="auto_close_reserved"><?php echo $auto_close_reserved_label; ?> (DD-MM-YYYY HH:MM) *</label>
		<input class="datetime datepicker" type="text" name="auto_close_reserved" id="auto_close_reserved" />

		<label for="accepted_clone_date"><?php echo $accepted_cloned_reservations; ?> <br>(DD-MM-YYYY HH:MM) *</label>
		<input type="text" class="dialogueInput datetime datepicker" name="accepted_clone_date" id="accepted_clone_date" />
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter a date that cloned reservations are set to when accepted by the Exhibitor.'}); ?>" />

		<label for="default_reservation_date"><?php echo $default_reservation_date; ?> <br>(DD-MM-YYYY HH:MM) *</label>
		<input type="text" class="dialogueInput datetime datepicker" name="default_reservation_date" id="default_reservation_date" />
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter a date that will be used when creating new reservations.'}); ?>" />

		<label for="website"><?php echo $website_label; ?> *</label>
		<input type="text" name="website" id="website" value="<?php echo $fair->get('website'); ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter the website of your event. This will be used for automatically generated email (through CFS).'}); ?>" />

		<label for="contact_email"><?php echo $contact_email_label; ?> *</label>
		<input type="text" name="contact_email" id="contact_email" value="<?php echo $fair->get('contact_email'); ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter an email address that will be used for automatically generated email (through CFS).'}); ?>" />

		<label for="contact_name"><?php echo $contact_name_label; ?> *</label>
		<input type="text" name="contact_name" id="contact_name" value="<?php echo $fair->get('contact_name'); ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter the name of the contact person that will be used for automatically generated email (through CFS).'}); ?>" />

		<label for="contact_phone"><?php echo $contact_phone_label; ?> *</label>
		<input type="text" name="contact_phone" id="contact_phone" value="<?php echo $fair->get('contact_phone'); ?>"/>
	  <img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'Enter a phone number that will be used for automatically generated email (through CFS).'}); ?>" />
	  
		<label for="contact_info"><?php echo $contact_label; ?> *</label>
		<textarea name="contact_info" id="contact_info"><?php echo $fair->get('contact_info'); ?></textarea>

		<p><input type="submit" class="greenbutton mediumbutton" name="save" value="<?php echo $clone_label; ?>"/></p>
	</div>
</form>
