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

					if (e.target.id === "new_option_input") {
						bookingOptions.createNewOption();
					} else if ($(e.target).hasClass("optionTextInput")) {
						bookingOptions.saveExtraOption.call($(e.target).closest("li").children(".saveExtraOption")[0]);
					}
				}
			});
	});
</script>
<?php tiny_mce(); ?>

<h1><?php echo $clone_headline; ?></h1>

<form action="fair/makeclone/<?php echo $edit_id; ?>" method="post" id="fair_clone_form">
	<div class="form_column floatleft">
		<label for="name"><?php echo $name_label; ?> *</label>
		<input autocomplete="off" type="text" name="name" id="name" />
		<label style="font-style:italic; width:400px;" id="name_preview"><?php echo BASE_URL ?><span></span></label>
		
		<label for="windowtitle"><?php echo $window_title_label; ?> *</label>
		<input type="text" name="windowtitle" id="windowtitle" value="<?php echo $fair->get('windowtitle'); ?>"/>

<<<<<<< HEAD
		<label for="auto_publish"><?php echo $auto_publish_label; ?> (DD-MM-YYYY HH:MM) *</label>
		<input class="datetime datepicker" type="text" name="auto_publish" id="auto_publish" value="<?php echo date('d-m-Y', $fair->get('auto_publish')); ?>"/>
		
		<label for="auto_close"><?php echo $auto_close_label; ?> (DD-MM-YYYY HH:MM) *</label>
		<input class="datetime datepicker" type="text" name="auto_close" id="auto_close" value="<?php echo date('d-m-Y', $fair->get('auto_close')); ?>"/>

		<label for="auto_close_reserved"><?php echo $auto_close_reserved_label; ?> (DD-MM-YYYY HH:MM) *</label>
=======
		<label for="auto_publish"><?php echo $auto_publish_label; ?> (DD-MM-YYYY HH:MM <?php echo TIMEZONE; ?>) *</label>
		<input class="datetime datepicker" type="text" name="auto_publish" id="auto_publish" value="<?php echo date('d-m-Y', $fair->get('auto_publish')); ?>"/>
		
		<label for="auto_close"><?php echo $auto_close_label; ?> (DD-MM-YYYY HH:MM <?php echo TIMEZONE; ?>) *</label>
		<input class="datetime datepicker" type="text" name="auto_close" id="auto_close" value="<?php echo date('d-m-Y', $fair->get('auto_close')); ?>"/>

		<label for="auto_close_reserved"><?php echo $auto_close_reserved_label; ?> (DD-MM-YYYY HH:MM <?php echo TIMEZONE; ?>) *</label>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
		<input class="datetime datepicker" type="text" name="auto_close_reserved" id="auto_close_reserved" />

		<label for="contact_info"><?php echo $contact_label; ?> *</label>
		<textarea name="contact_info" id="contact_info"><?php echo $fair->get('contact_info'); ?></textarea>

		<p><input type="submit" name="save" value="<?php echo $clone_label; ?>"/></p>
	</div>
	<?php /*<div class="optionsWhenBooking">
		<h2><?php echo $options_when_booking_label; ?></h2>
		<label for="new_option_input"><?php echo $new_option_label; ?></label>
		<input type="text" id="new_option_input" data-fair="new" />
		<img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo ""; ?>" />
		<input type="button" id="new_option_button" value="Ok" />
		<ul id="optionList">
			<?php
			if (!empty($options_when_booking)) {
				foreach ($options_when_booking as $option) {
					if (strlen($option["text"]) > 18) {
						$text = substr($option["text"], 0, 15) . "...";
					} else {
						$text = $option["text"];
					}
					echo "<li>
						<span title=\"{$option["text"]}\" class=\"optionText\">{$text}</span>
						</span><input type=\"hidden\" value=\"{$option["text"]}\" name=\"options[]\" class=\"optionTextHidden\" />
						<img src=\"images/icons/pencil.png\" class=\"icon editExtraOption\" data-id=\"new\" />
						<img src=\"images/icons/delete.png\" class=\"icon deleteExtraOption\" data-id=\"new\" />
					</li>";
				}
			}
			?>
		</ul>
	</div>*/ ?>
</form>
