<?php
  echo Form::Load("userdata_administrator",
      array(
          'headline'=>$translator->{($edit_id=='new'?'New administrator':'Edit administrator')},
          'action'=>"administrator/edit/".$edit_id,
          'user'=>$user,
          'user_maps'=>@$user_maps,
          'user_fairs'=>@$user_fairs,
          'fairs'=>@$fairs,
          'error'=>@$user_message
        )
    );
  return;
?>

<script type="text/javascript">
	
	function filterFairs() {
		
		var str = $("#search_input").val();
		var hits = new Array;
		var hit_count = 0;
		
		$("#permission_box .fair strong").each(function() {
			if ($(this).text().toLowerCase().indexOf(str.toLowerCase()) >= 0) {
				//console.log($(this).parent());
				hits.push($(this).parent());
				//$(this).parent().hide();
			}
		});
		$("#permission_box .fair").hide();
		for (i=0; i<hits.length; i++) {
			hits[i].show();
		}
		
		$("#permission_box .fair").each(function() {
			if ($(this).is(":visible")) {
				hit_count++;
			}
		});
		
		//$("#search_results").text(hit_count + ' matching rows.');
		
	}
	
	$(document).ready(function() {
		
		$('p', $('#permission_box .fair input.fair_input:checked').parent()).show();
		
		$('#permission_box .fair input.fair_input').change(function() {
			if ($(this).is(':checked')) {
				$('p', $(this).parent()).slideDown(function() {
					/*$('input', $(this)).each(function() {
						$(this).attr('checked', 'checked');
					});*/
				});
			} else {
				$('p', $(this).parent()).slideUp(function() {
					$('input', $(this)).each(function() {
						$(this).removeAttr('checked');
					});
				});
			}
		});
		
		$("#search_button").click(function() {
			filterFairs();
		});
		$("#search_input").keyup(function(e) {
			if (e.keyCode == 13) {
				filterFairs();
			}
		});
				
	});
</script>

<h1><?php echo $headline; ?></h1>
<?php if (isset($user_message)): ?>
<p><?php echo $user_message ?></p>
<?php endif; ?>
<p id="permission_search"><input type="text" id="search_input"/>
			<input type="button" id="search_button" /></p>
<form action="administrator/edit/<?php echo $edit_id; ?>" method="post">
	<div id="permission_box">
		<h2><?php echo $permissions_headline ?></h2>
		<div id="permission_search">
			
		</div>
		<p style="text-align:center;"><strong style="margin-right:10px;"><?php echo $event_without_maps_obs?></strong><?php echo $event_without_maps_label?></p>
		<?php foreach ($fairs as $fair): ?>
			<?php if(count($fair->get('maps')) > 1) :?>
			<div class="fair">
				
				<input<?php if(is_array($user_fairs) && in_array($fair->get('id'), $user_fairs)) { echo ' checked="checked"'; } ?> type="checkbox" name="fair_permission[]" value="<?php echo $fair->get('id') ?>" class="fair_input"/>
				<strong><?php echo $fair->get('name'); ?></strong>
				<p>
				
					<?php foreach($fair->get('maps') as $map): ?>
						<input<?php if(is_array($user_maps) && in_array($map->get('id'), $user_maps)) { echo ' checked="checked"'; } ?> type="checkbox" name="maps[<?php echo $fair->get('id') ?>][]" value="<?php echo $map->get('id'); ?>" id="mapbox<?php echo $map->get('id'); ?>"/>
						<label style="font-weight:normal;" for="mapbox<?php echo $map->get('id'); ?>" class="inline-block"><?php echo $map->get('name'); ?></label>
					<?php endforeach; ?>
				
				</p>
				
			</div>
			<?php endif?>
		<?php endforeach; ?>
		
	</div>
	
	<label for="#"><?php echo $locked_label; ?></label>
	<input<?php echo ($user->get('locked') == 0) ? ' checked="checked"' : ''; ?> type="radio" name="locked" value="0" id="locked0"/><label for="locked0" class="inline-block"><?php echo $locked_label0; ?></label>
	<input<?php echo ($user->get('locked') == 1) ? ' checked="checked"' : ''; ?> type="radio" name="locked" value="1" id="locked1"/><label for="locked1" class="inline-block"><?php echo $locked_label1; ?></label>
	
	<label for="alias"><?php echo $alias_label; ?> *</label>
	<input type="text" name="alias" id="alias" value="<?php echo $user->get('alias'); ?>"<?php if ($edit_id != 'new') { echo 'disabled="disabled"'; } ?>/>
	
	<!--<label for="password"><?php echo $password_label; ?> *</label>
	<input type="password" name="password" id="password" class="hasIndicator"/>
	
	<label for="password_repeat"><?php echo $password_repeat_label; ?> *</label>
	<input type="password" name="password_repeat" id="password_repeat"/>-->
	
	<label for="name"><?php echo $contact_label; ?> *</label>
	<input type="text" name="name" id="name" value="<?php echo $user->get('name'); ?>"/>

	<label for="phone1"><?php echo $phone1_label; ?> *</label>
	<input type="text" name="phone1" id="phone1" value="<?php echo $user->get('phone1'); ?>"/>

	<label for="phone2"><?php echo $phone2_label; ?></label>
	<input type="text" name="phone2" id="phone2" value="<?php echo $user->get('phone2'); ?>"/>

	<label for="phone3"><?php echo $phone3_label; ?></label>
	<input type="text" name="phone3" id="phone3" value="<?php echo $user->get('contact_phone'); ?>"/>

	<label for="email"><?php echo $email_label; ?> *</label>
	<input type="text" name="email" id="email" value="<?php echo $user->get('email'); ?>"/>

	<!--<label for="maps"><?php echo $maps_label; ?></label>-->

	<?php if (userLevel() == 4): ?>
	<!--<select name="owner">
		<option value="">här får man välja en arrangör eller?</option>
	</select>-->
	<?php else: ?>
	<input type="hidden" name="owner" value="<?php echo $_SESSION['user_id']; ?>"/>
	<?php endif; ?>


	<p><input type="submit" name="save" value="<?php echo $save_label; ?>" class="save-btn" /></p>

</form>
