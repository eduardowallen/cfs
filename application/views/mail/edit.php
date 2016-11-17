<?php if ($mail != ''): ?>
	
<style>
	.mceEditor > table {
	width:560px !important;
	height:100px !important;
}
.mceEditor td.mceIframeContainer iframe {
	height: 500px !important;
	width: 1024px !important;
}

.defaultSkin * {
max-width:1024px !important;
}

label{
max-width:100% !important;
}

</style>
<button class="go_back" onclick="location.href='../mail/edit'"><?php echo uh($translator->{'Go back'}); ?></button>
<?php tiny_mce(); ?>
<form action="mail/edit/<?php echo $mail; ?>/<?php echo $lang; ?>" method="post">
  <?php if ($mail == 'new'): ?>
  <label for="maillabel"><?php echo $mail_label_label; ?></label>
  <p><input name="mail_label" id="mail_label" style="width:100%;" value="<?php echo $mail_label; ?>"/></p>
  <?php endif; ?>
  <label for="subject"><?php echo $subject_label; ?></label>
  <p><input name="mail_subject" id="mail_subject" style="width:100%;" value="<?php echo $mail_subject; ?>"/></p>
	<!--<label for="content"><?php echo $content_label; ?></label>-->
	<textarea name="mail_content" id="mail_content" style="width:100%; height:500px;"><?php echo $mail_content; ?></textarea>
	<p><input type="submit" name="save" value="<?php echo $save_label; ?>" class="greenbutton mediumbutton" /></p>
</form>

<?php else: ?>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>start/home'"><?php echo uh($translator->{'Go back'}); ?></button>
<p>
  <a class="button add" href="/mail/edit/new/<?php echo LANGUAGE; ?>"><?php echo $newmail_label; ?></a>
</p>
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
			<td onclick="$(this).parent().next().toggle();"><?php echo $mail['mail']; ?></td>
			<td onclick="$(this).parent().next().toggle();"><?php echo $mail['subject']; ?></td>
			
			<?php foreach ($langs as $lang): ?>
        <td class="center"><a href="mail/edit/<?php echo $mail['mail']; ?>/<?php echo $lang['id']; ?>">
          <?php if(isset($mail[$lang['id']])): ?>
            <img src="images/icons/pencil.png" class="icon_img" alt="" title="<?php echo $edit_label; ?>" />
          <?php else: ?>
            <img src="images/icons/add.png" class="icon_img" alt="" title="<?php echo $add_label; ?>" />
          <?php endif; ?>
        </a></td>
			<?php endforeach; ?>
			
		</tr>
    <tr class="expand-child" style="display: none;">
      <td colspan="<?php echo $numcols; ?>">
        <?php echo $mail['content']; ?>
      </td>
    </tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php endif; ?> 
