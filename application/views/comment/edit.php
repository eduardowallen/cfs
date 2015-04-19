		<h3><?php echo uh($label_headline); ?></h3>

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
<<<<<<< HEAD
				<textarea name="comment" cols="30" class="insert_comment_text" rows="7"><?php echo uh($comment->get('comment')); ?></textarea>
			</p>
			<p>
				<button type="submit" class="comment-btn" name="save" value="1"><?php echo uh($label_comment_save); ?></button>
=======
				<textarea name="comment" cols="30" rows="7"><?php echo uh($comment->get('comment')); ?></textarea>
			</p>
			<p>
				<button name="save" value="1"><?php echo uh($label_comment_save); ?></button>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
			</p>
		</form>
