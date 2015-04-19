		<h3><?php echo uh($label_headline); ?></h3>

		<form action="comment/delete/<?php echo $comment->get('id'); ?>" method="POST">
			<p>
				<strong><?php echo uh($label_delete_question); ?></strong>
			</p>
			<p>
				<?php echo uh($comment->get('comment')); ?>
			</p>
			<p>
				<button type="submit" name="save" value="1" class="dialog-buttons" id="confirm_yes"><?php echo uh($label_yes); ?></button>
				<a href="#" class="close-popup"><button class="dialog-buttons" id="confirm_no"><?php echo uh($label_no); ?></button></a>
			</p>
		</form>
