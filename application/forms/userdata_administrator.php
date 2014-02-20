<?php
  /*  Form UserData_administrator used in /user/accountSettings, /administrator/edit explicitly for administrators
   *  Accepted variables:
   *    $header - if $popup is not defined, is written above the form
   *    $action - is used as the action field for the form, use this to direct form to the right location
   *    $user - if defined, provides existing information for the form
   *    $user_fairs - a list of fairs the user has been given access to
   *    $user_maps - a list of maps the user has been given access to
   *    $fairs - a list of available fairs, built based on the current user's access
   *    $error - contains the error to be output, if any
   *  Function userLevel() is used to check the user access level
   *    3+: some fields are only available to arrangers or masters, such as account lock and fair access
   */
  global $translator;
  
  if(!isset($user)) // If no user is defined...
    $user=new User(); // ...create a new empty user so that calling $user->get('<something>'); doesn't cause a fatal error
?>
<?php // This script is used for permission selection, therefore only needed when an arranger or higher edits the profile
  if (userLevel() >= 3):
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
<?php endif?>
<form action="<?php echo $action; ?>" method="post">

  <h1><?php echo $headline; ?></h1>

  <p class="error"><?php echo (isset($error)?$error:''); ?></p>
  
<?php if (userLevel() >= 3): ?>
  <p id="permission_search">
    <input type="text" id="search_input"/>
    <input type="button" id="search_button" value="<?php echo $translator->{'Search'} ?>"/>
  </p>
  <div id="permission_box">
    <h2><?php echo $translator->{'Permissions for user'}; ?></h2>
    <div id="permission_search">
    
    </div>
    <p style="text-align:center;">
      <strong style="margin-right:10px;"><?php echo $translator->{'OBS! '}?></strong><?php echo $translator->{'Events without maps are not listed.'}?>
    </p>
    <?php foreach ($fairs as $fair): ?>
      <?php if(count($fair->get('maps')) > 0) :?>
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
<?php endif?>
  
  <div class="form_column">
		<label for="alias"><?php echo $translator->{'Username'}; ?> *</label>
		<input type="text" name="alias" id="alias" value="<?php echo $user->get('alias'); ?>"<?php if ($user->get('id') != 0) { echo 'disabled="disabled"'; } ?>/>

		<label for="name"><?php echo $translator->{'Name'}; ?> *</label>
		<input type="text" name="name" id="name" value="<?php echo $user->get('name'); ?>"/>

		<label for="phone1"><?php echo $translator->{'Phone 1'}; ?> *</label>
		<input type="text" name="phone1" id="phone1" value="<?php echo $user->get('phone1'); ?>"/>
	
		<label for="phone2"><?php echo $translator->{'Phone 2'}; ?></label>
		<input type="text" name="phone2" id="phone2" value="<?php echo $user->get('phone2'); ?>"/>

		<label for="phone3"><?php echo $translator->{'Contact Phone'}; ?></label>
		<input type="text" name="phone3" id="phone3" value="<?php echo $user->get('contact_phone'); ?>"/>
		
		<label for="email"><?php echo $translator->{'E-mail'}; ?> *</label>
		<input type="text" name="email" id="email" value="<?php echo $user->get('email'); ?>"/>
  </div>
  
<?php if (userLevel() == 4): ?>
	<!--<select name="owner">
		<option value="">här får man välja en arrangör eller?</option>
	</select>-->
<?php elseif(userLevel() == 3): ?>
	<input type="hidden" name="owner" value="<?php echo $_SESSION['user_id']; ?>"/>
<?php endif; ?>
  
<?php if (userLevel() >= 3): ?>
	<label for="#"><?php echo $translator->{'Account locked'}; ?></label>
	<input<?php echo ($user->get('locked') == 0) ? ' checked="checked"' : ''; ?> type="radio" name="locked" value="0" id="locked0"/><label for="locked0" class="inline-block"><?php echo $translator->{'No'}; ?></label>
	<input<?php echo ($user->get('locked') == 1) ? ' checked="checked"' : ''; ?> type="radio" name="locked" value="1" id="locked1"/><label for="locked1" class="inline-block"><?php echo $translator->{'Yes'}; ?></label>
<?php endif; ?>

  <p>
    <input type="submit" name="save" value="<?php echo $translator->{'Save'}; ?>" class="save-btn" />
  </p>

</form>