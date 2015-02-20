		<h3><?php echo uh($label_headline); ?></h3>

		<form action="comment/delete/<?php echo $comment->get('id'); ?>" method="POST">
			<p>
				<strong><?php echo uh($label_delete_question); ?></strong>
			</p>
			<p>
				<?php echo uh($comment->get('comment')); ?>
			</p>
			<p>
				<button name="save" value="1"><?php echo uh($label_yes); ?></button>
				<a href="#" class="link-button close-popup"><?php echo uh($label_no); ?></a>
			</p>
		</form>
