		<h3><?php echo uh($label_headline); ?></h3>

		<form action="comment/delete/<?php echo $comment->get('id'); ?>" method="POST">
			<p>
				<strong><?php echo uh($label_delete_question); ?></strong>
			</p>
			<p>
				<?php echo uh($comment->get('comment')); ?>
			</p>
			<p>
<<<<<<< HEAD
				<button type="submit" name="save" value="1" class="dialog-buttons" id="confirm_yes"><?php echo uh($label_yes); ?></button>
				<a href="#" class="close-popup"><button class="dialog-buttons" id="confirm_no"><?php echo uh($label_no); ?></button></a>
=======
				<button name="save" value="1"><?php echo uh($label_yes); ?></button>
				<a href="#" class="link-button close-popup"><?php echo uh($label_no); ?></a>
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
			</p>
		</form>
