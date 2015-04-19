<?php if ($page != ''): ?>

<?php tiny_mce(); ?>
<form action="page/edit/<?php echo $page; ?>/<?php echo $lang; ?>" method="post">
	<!--<label for="content"><?php echo $content_label; ?></label>-->
	<textarea name="page_content" id="page_content" style="width:100%; height:500px;"><?php echo $page_content; ?></textarea>
	<p><input type="submit" name="save" value="<?php echo $save_label; ?>" class="save-btn" /></p>
</form>

<?php else: ?>

<table class="std_table">
	<thead>
		<tr>
			<th><?php echo $th_page; ?></th>
			<?php foreach ($langs as $lang): ?>
			<th><?php echo  $lang['name']; ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($pages as $page): ?>
		<tr>
			<td><?php echo $page['page']; ?></td>
			
			<?php foreach ($langs as $lang): ?>
			<td class="center"><a href="page/edit/<?php echo $page['page']; ?>/<?php echo  $lang['id']; ?>"><img src="images/icons/pencil.png" alt="" title="<?php echo $edit_label; ?>" /></a></td>
			<?php endforeach; ?>
			
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php endif; ?> 
