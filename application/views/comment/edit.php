<h3><?php echo uh($label_headline); ?></h3>
<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue close-popup" />

<?php if (isset($error)): ?>
		<p class="error"><?php echo uh($error); ?></p>
<?php endif; ?>

<?php if (isset($user)): ?>
		<p>
			<strong><?php echo uh($label_current_exhibitor); ?>:</strong> <?php echo uh($user->get('company')); ?>
		</p>
<?php endif; ?>

		<form action="comment/edit/<?php echo $comment->get('id'); ?>" method="POST">
			<p>
				<strong><?php echo uh($label_comment_type); ?></strong><br />
				<select name="type">
					<option value="1"<?php if ($comment->get('type') == 1) echo ' selected="selected"'; ?>><?php echo uh($label_comment_positive); ?></option>
					<option value="0"<?php if ($comment->get('type') == 0) echo ' selected="selected"'; ?>><?php echo uh($label_comment_neutral); ?></option>
					<option value="-1"<?php if ($comment->get('type') == -1) echo ' selected="selected"'; ?>><?php echo uh($label_comment_negative); ?></option>
				</select>
			</p>
			<p>
				<textarea name="comment" cols="30" class="insert_comment_text" rows="7"><?php echo uh($comment->get('comment')); ?></textarea>
			</p>
			<p>
				<button type="submit" class="greenbutton mediumbutton" name="save" value="1"><?php echo uh($label_comment_save); ?></button>
			</p>
		</form>
