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
	});
</script>
<script type="text/javascript" src="public/js/eventfunctions.js"></script>
<div class="add_custom_fee_dialogue" class="dialogue">
	<h2><?php echo $add_custom_fee_title_label?></h2>
	<p><?php echo $add_custom_fee_name_label?></p>
	<p><input type="text" class="custom_fee_name" name="custom_fee_name"></input></p>
	<button><?php echo $save_label; ?></button>
</div>
<h1><?php echo $edit_headline; ?></h1>
<?php if ($edit_id != 'new'): ?>
<p><a class="button settings" href="fair/maps/<?php echo $edit_id; ?>"><?php echo $map_button_label; ?></a></p>
<p><a class="button settings" href="articlelist/lists/<?php echo $edit_id; ?>"><?php echo $lists_button_label; ?></a></p>
<?php endif; ?>
<form action="fair/edit/<?php echo $edit_id; ?>" method="post" enctype="multipart/form-data">
<div style="padding:10px; float:right; position:relative; top:28px; right:350px; width:320px; border:1px solid #000; max-height:350px; overflow-y:scroll;">
	<a class="button add addfees">Add other fees</a>
	<div id="custom_fees">
		<?php foreach($custom_fees as $cfee) :?>
			<div style="float:left" class="<?php echo str_replace(' ', '', $cfee['id'].$cfee['name'])?>">
			<p style="font-weight:bold; margin:3px 0 0 ;" class="name"><?php echo $cfee['name']?>:</p>
			<input style="float:left;" class="inp<?php echo $cfee['id'].str_replace(' ', '', $cfee['name'])?>" name="custom_fee['<?php echo str_replace('%', ' ', $cfee['name'])?>']" value="<?php echo $cfee['amount']?>" type="text" />
			<input class="id<?php echo $cfee['id'].str_replace(' ', '', $cfee['name'])?>" type="hidden" name="custom_fee_id['<?php echo str_replace('%', ' ', $cfee['name'])?>']" value="<?php echo $cfee['id']?>" type="text" />
			<p style="float:left; margin:5px 0 0 5px;" class="value"><?php echo $fair->get('default_currency')?></p>
			<p style="float:left; margin:0 5px; width:20px; font-size:9px;">edit<img class="edit" onclick="editPrice('add_custom_fee_dialogue', '<?php echo $cfee['id'].str_replace(' ', '', $cfee['name'])?>', '<?php echo $cfee['name']?>')" src="images/icons/pencil.png" alt="" /></p>
			<p style="float:left; margin:0 5px; width:20px; font-size:9px;">delete<img class="delete" onclick="removePrice('<?php echo $cfee['id'].str_replace(' ', '', $cfee['name'])?>')" src="images/icons/delete.png" alt="" /></p>
			</div>
		<?php endforeach;?>
	</div>
</div>
<div class="form_column">
	<label for="name"><?php echo $name_label; ?><?php echo ($fair_id == 'new') ? ' *' : ''; ?></label>
	<input <?php echo $disable; ?> autocomplete="off"<?php echo ($fair_id == 'new') ? '' : ' disabled="disabled"' ?> type="text" name="name" id="name" value="<?php echo $fair->get('name'); ?>"/>
	<label for="" style="font-style:italic; width:400px;" id="name_preview"><?php echo BASE_URL ?><span><?php echo $fair->get('url'); ?></span></label>
	
	<label for="max_positions"><?php echo $max_positions_label; ?><?php echo ($fair_id == 'new') ? ' *' : ''; ?></label>
	<input <?php echo $disable; ?> autocomplete="off"<?php echo ($fair_id == 'new') ? '' : ' ' ?> type="text" name="max_positions" id="max_positions" value="<?php echo $fair->get('max_positions'); ?>"/>
	
	<label for="windowtitle"><?php echo $window_title_label; ?> *</label>
	<input <?php echo $disable; ?> type="text" name="windowtitle" id="windowtitle" value="<?php echo $fair->get('windowtitle'); ?>"/>

	<label for="currency"><?php echo $default_currency?> *</label>
	<select name="currency" class="currency" id="currency">
		<option val="SEK" <?php echo ($fair->get('default_currency') == 'SEK' ? 'selected' : '')?>>SEK</option>
		<option val="USD" <?php echo ($fair->get('default_currency') == 'USD' ? 'selected' : '')?>>USD</option>
		<option val="EUR" <?php echo ($fair->get('default_currency') == 'EUR' ? 'selected' : '')?>>EUR</option>
		<option val="GBP" <?php echo ($fair->get('default_currency') == 'GBP' ? 'selected' : '')?>>GBP</option>
		<option val="PEN" <?php echo ($fair->get('default_currency') == 'PEN' ? 'selected' : '')?>>PEN</option>
	</select>

	<label for="pricekvm"><?php echo $default_price?> *</label>
	<input type="text" id="pricekvm" name="pricekvm" value="<?php echo $fair->get('price_per_m2')?>" />

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
	<label for="contact_info"><?php echo $contact_label; ?> *</label>
	<textarea <?php echo $disable; ?> name="contact_info" id="contact_info"><?php echo $fair->get('contact_info'); ?></textarea>

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

	<label for="hidden"> Hide fair for unauthorized accounts </label>
	<select name="hidden" id="hidden">
		<option value="0"> false </option>
		<option value="1"> true </option>
	</select>

	<p><input <?php echo $disable; ?> type="submit" name="save" value="<?php echo $save_label; ?>"/></p>
</div>
</form>
