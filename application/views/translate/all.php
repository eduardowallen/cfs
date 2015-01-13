<script>
	$(document).ready(function() {
		$('.toggle-translate-button').click(function(e) {
			confirmBox(e, '<?php echo $translation_toggle_question_label; ?>', $(this).attr('href'));
		});
	});
</script>

<h1><?php echo $headline; ?></h1>

<form action="translate/all" method="post">
<p>
	<input type="submit" name="save" value="<?php echo $save_label; ?>" class="save-btn" />
	<a href="translate/toggle" class="link-button toggle-translate-button"><?php echo ($translation_on ? $translation_off_label : $translation_on_label); ?></a>
</p>

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
				<td style="max-width:140px;"><?php echo uh($string[$lang['id']]); ?><input type="hidden" value="<?php echo uh($string[$lang['id']]); ?>" name="string[<?php echo $groupKey; ?>][<?php echo $lang['id']; ?>]"/></td>
			<?php else: ?>
				<td><textarea style="width:140px; height:auto;" class="translate_textarea" name="string[<?php echo $groupKey; ?>][<?php echo $lang['id']; ?>]"><?php echo (isset($string[$lang['id']])) ? uh($string[$lang['id']]) : ''; ?></textarea></td>
			<?php endif; ?>
			
		<?php endforeach; ?>
	</tr>
	
	<?php endforeach; ?>
	</tbody>
	
</table>

<p>
	<input type="submit" name="save" value="<?php echo $save_label; ?>" class="save-btn" />
	<a href="translate/toggle" class="link-button toggle-translate-button"><?php echo ($translation_on ? $translation_off_label : $translation_on_label); ?></a>
</p>

</form>