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
	});
</script>

<h1><?php echo $edit_headline; ?></h1>
<?php if ($edit_id != 'new'): ?>
<p><a class="button settings" href="fair/maps/<?php echo $edit_id; ?>"><?php echo $map_button_label; ?></a></p>
<?php endif; ?>

<form action="fair/edit/<?php echo $edit_id; ?>" method="post" enctype="multipart/form-data">
	<label for="name"><?php echo $name_label; ?><?php echo ($fair_id == 'new') ? ' *' : ''; ?></label>
	<input <?php echo $disable; ?> autocomplete="off"<?php echo ($fair_id == 'new') ? '' : ' disabled="disabled"' ?> type="text" name="name" id="name" value="<?php echo $fair->get('name'); ?>"/>
	<label for="" style="font-style:italic; width:400px;" id="name_preview"><?php echo BASE_URL ?><span><?php echo $fair->get('url'); ?></span></label>
	
	<label for="max_positions"><?php echo $max_positions_label; ?><?php echo ($fair_id == 'new') ? ' *' : ''; ?></label>
	<input <?php echo $disable; ?> autocomplete="off"<?php echo ($fair_id == 'new') ? '' : ' ' ?> type="text" name="max_positions" id="max_positions" value="<?php echo $fair->get('max_positions'); ?>"/>
	
	<label for="windowtitle"><?php echo $window_title_label; ?> *</label>
	<input <?php echo $disable; ?> type="text" name="windowtitle" id="windowtitle" value="<?php echo $fair->get('windowtitle'); ?>"/>
	
	<?php if (userLevel() == 4 || $edit_id == 'new') { $da = ''; } else { $da = ' disabled="true"'; } ?>
	<label for="auto_publish"><?php echo $auto_publish_label; ?> (dd-mm-yyyy) *</label>
	<input class="date datepicker" <?php echo $da; ?> type="text" name="auto_publish" id="auto_publish" value="<?php if ($edit_id != 'new') { echo date('d-m-Y', $fair->get('auto_publish')); } ?>"/>
	
	<label for="auto_close"><?php echo $auto_close_label; ?> (dd-mm-yyyy) *</label>
	<input class="date datepicker" <?php echo $da; ?> type="text" name="auto_close" id="auto_close" value="<?php if ($edit_id != 'new') { echo date('d-m-Y', $fair->get('auto_close')); } ?>"/>
	
	<!--
	<label for="logo"><?php echo $logo_label; ?></label>
	<input <?php echo $disable; ?> type="file" name="logo" id="logo"/>
	-->

	<?php (empty($disable)) ? tiny_mce() : ''; ?>
	<label for="contact_info"><?php echo $contact_label; ?></label>
	<textarea<?php echo $disable; ?> name="contact_info" id="contact_info"><?php echo $fair->get('contact_info'); ?></textarea>

	<?php if (userLevel() == 4): ?>
	<label for="arranger"><?php echo $arranger_label; ?></label>
	<select name="arranger" id="arranger">
		<?php echo makeUserOptions($fair->db, $fair->get('created_by')); ?>
	</select> <?php if ($edit_id != 'new'): ?> <!--<a href="arranger/edit/<?php echo $fair->get('created_by'); ?>">View organizer</a>--> <?php endif; ?>
	<?php endif; ?>

	<?php if (userLevel() == 4): ?>
	<label for="approved"><?php echo $approved_label; ?></label>
	<select name="approved" id="approved">
		<option value="0"<?php echo $app_sel0; ?>><?php echo $app_opt0; ?></option>
		<option value="1"<?php echo $app_sel1; ?>><?php echo $app_opt1; ?></option>
		<option value="2"<?php echo $app_sel2; ?>><?php echo $app_opt2; ?></option>
	</select>
	<?php endif; ?>

	<p><input<?php echo $disable; ?> type="submit" name="save" value="<?php echo $save_label; ?>"/></p>

</form>
