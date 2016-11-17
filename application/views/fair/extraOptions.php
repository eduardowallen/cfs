
<?php if ($do == 'edit'): ?>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/extraOptions/<?php echo $fair_id; ?>'"><?php echo uh($translator->{'Go back'}); ?></button>
<br />
<h1><?php echo $headline; ?> <?php echo $fair->get('name'); ?></h1>
<form action="fair/extraOptions/<?php echo $fair_id; ?>/edit/<?php echo $item; ?>" method="post" style="background:#efefef; border:1px solid #b1b1b1; padding:20px;">
	<h3><?php echo $form_headline; ?></h3>
	<label for="custom_id"><?php echo $id_label; ?> *</label>
	<input required="yes" type="text" name="custom_id" id="custom_id" value="<?php echo $current_cid ?>"/>
	<label for="text"><?php echo $name_label; ?> *</label>
	<input required="yes" type="text" name="text" id="text" value="<?php echo $current_text ?>"/>
	<label for="price"><?php echo $price_label; ?> *</label>
	<input required="yes" type="number" name="price" id="price" min="0" step="0.01" title="<?php echo ujs($translator->{"Comma as delimiter is not accepted. Please use dot instead (eg: 234.53 = 234,53)."}); ?>" value="<?php echo $current_price ?>"/>
	<label for="vat"><?php echo $vat_label; ?></label>
	<select name="vat" id="vat">
		<option value="0"><?php echo $no_vat_label; ?></option>
		<option value="12"<?php if ($vat == 12) echo ' selected="selected"'; ?>>12%</option>
		<option value="18"<?php if ($vat == 18) echo ' selected="selected"'; ?>>18%</option>
		<option value="25"<?php if ($vat == 25) echo ' selected="selected"'; ?>>25%</option>
	</select>
	<label for="required"><?php echo $required_label; ?> *</label>
	<input type="radio" required="yes" name="required" id="required" <?php if (isset($required_status) && $required_status == "1") echo "checked"; ?> value="1" ><?php echo $requiredyes_label; ?></input>
	<input type="radio" name="required" id="required" <?php if (isset($required_status) && $required_status == "0") echo "checked"; ?> value="0" ><?php echo $requiredno_label; ?></input>
	<br />
	<br />
	<input type="submit" class="greenbutton bigbutton" name="save" value="<?php echo $save_label; ?>" />
</form>

<?php else: ?>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>
<br />
<h1><?php echo $headline; ?> <?php echo $fair->get('name'); ?></h1>
<form action="fair/extraOptions/<?php echo $fair_id; ?>" method="post" style="background:#efefef; border:1px solid #b1b1b1; padding:20px;">
	<h3><?php echo $form_headline; ?></h3>
	<div class="column33">
		<label for="custom_id"><?php echo $id_label; ?> *</label>
		<input required="yes" type="text" name="custom_id" id="custom_id"/>
	</div>
	<div class="column33">
		<label for="text"><?php echo $name_label; ?> *</label>
		<input required="yes" type="text" name="text" id="text"/>
	</div>
	<div class="column33">
	<label for="price"><?php echo $price_label; ?> *</label>
	<input required="yes" type="number" name="price" id="price" min="0" step="0.01" title="<?php echo ujs($translator->{"Comma as delimiter is not accepted. Please use dot instead (eg: 234.53 = 234,53)."}); ?>" />
	</div>
	<div class="column33">
	<label for="vat"><?php echo $vat_label; ?></label>
	<select name="vat" id="vat" style="display:inline-block;">
		<option value="0"><?php echo $no_vat_label; ?></option>
		<option value="12">12%</option>
		<option value="18">18%</option>
		<option value="25">25%</option>
	</select>
	</div>
	<div class="column33">
	<label for="required"><?php echo $required_label; ?> *</label>
	<input type="radio" required="yes" name="required" id="required" value="1" ><?php echo $requiredyes_label; ?></input>
	<input type="radio" name="required" id="required" value="0" ><?php echo $requiredno_label; ?></input>
	</div>
	<br />
	<br />
	<input type="submit" class="greenbutton bigbutton" name="save" value="<?php echo $save_label; ?>" />
</form>

<?php if(count($extraOptions) > 0) : ?>
	<div class="tbld">
		<table class="std_table">
			<thead>
				<tr>
					<th class="left"><?php echo $th_id; ?></th>
					<th class="left"><?php echo $th_name; ?></th>
					<th class="left"><?php echo $th_price; ?></th>
					<th class="left"><?php echo $th_required; ?></th>
					<th class="left"><?php echo $th_vat; ?></th>
					<th><?php echo $th_edit; ?></th>
					<th><?php echo $th_delete; ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($extraOptions as $feo): ?>
				<tr>
					<td class="left"><?php echo $feo->get('custom_id'); ?></td>
					<td class="left"><?php echo $feo->get('text'); ?></td>
					<td class="left"><?php echo $feo->get('price'); ?></td>
					<td class="left"><?php if ($feo->get('required') == 0): echo $requiredno_label ?><?php else: echo $requiredyes_label ?><?php endif; ?></td>
					<td class="left"><?php echo $feo->get('vat'); ?>%</td>
					<td class="center"><a href="fair/extraOptions/<?php echo $fair_id; ?>/edit/<?php echo $feo->get('id'); ?>"><img src="images/icons/pencil.png" alt="" title="<?php echo $th_edit; ?>" /></a></td>
					<td class="center"><a onclick="return confirm('<?php echo $confirm_delete; ?>')" href="fair/extraOptions/<?php echo $fair_id; ?>/delete/<?php echo $feo->get('id'); ?>"><img src="images/icons/delete.png" alt="" title="<?php echo $th_delete; ?>" /></a></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>
<?php endif; ?>
