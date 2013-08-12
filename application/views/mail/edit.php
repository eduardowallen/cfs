<?php if ($mail != ''): ?>

<?php tiny_mce(); ?>
<form action="mail/edit/<?php echo $mail; ?>/<?php echo $lang; ?>" method="post">
  <!--<label for="subject"><?php echo $subject_label; ?></label>-->
  <p><input name="mail_subject" id="mail_subject" style="width:100%;" value="<?php echo $mail_subject; ?>"/></p>
	<!--<label for="content"><?php echo $content_label; ?></label>-->
	<textarea name="mail_content" id="mail_content" style="width:100%; height:500px;"><?php echo $mail_content; ?></textarea>
	<p><input type="submit" name="save" value="<?php echo $save_label; ?>"/></p>
</form>

<?php else: ?>

<table class="std_table">
	<thead>
		<tr>
			<th><?php echo $th_mail; ?></th>
			<th><?php echo $th_subject; ?></th>
			<?php foreach ($langs as $lang): ?>
			<th><?php echo  $lang['name']; ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($mails as $mail): ?>
		<tr>
			<td><?php echo $mail['mail']; ?></td>
			<td><?php echo $mail['subject']; ?></td>
			
			<?php foreach ($langs as $lang): ?>
			<td class="center"><a href="mail/edit/<?php echo $mail['mail']; ?>/<?php echo $lang['id']; ?>"><img src="images/icons/pencil.png" alt=""/></a></td>
			<?php endforeach; ?>
			
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php endif; ?> 
