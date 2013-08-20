<h1><?php echo $headline; ?></h1>

<form action="translate/all" method="post">
<table class="std_table">
	<thead>
	<tr>
		<?php foreach ($langs as $lang): ?>
			<th><?php echo $lang['name']; ?></th>
		<?php endforeach; ?>
	</tr>
	</thead>
	
	<tbody>
	<?php foreach ($strings as $groupKey=>$string): ?>
	
	<tr>
		<?php foreach ($langs as $lang): ?>
			
			<?php if ($lang['default']): ?>
				<td style="max-width:140px;"><?php echo $string[$lang['id']]; ?><input type="hidden" value="<?php echo $string[$lang['id']]; ?>" name="string[<?php echo $groupKey; ?>][<?php echo $lang['id']; ?>]"/></td>
			<?php else: ?>
				<td><textarea style="width:140px; height:auto;" class="translate_textarea" name="string[<?php echo $groupKey; ?>][<?php echo $lang['id']; ?>]"><?php echo (isset($string[$lang['id']])) ? $string[$lang['id']] : ''; ?></textarea></td>
			<?php endif; ?>
			
		<?php endforeach; ?>
	</tr>
	
	<?php endforeach; ?>
	</tbody>
	
</table>
<p><input type="submit" name="save" value="<?php echo $save_label; ?>"/></p>
</form>