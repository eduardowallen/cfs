				<li class="comment" data-model="Comment">

						<a href="comment/edit/<?php echo $comment->get('id'); ?>" class="js-comment-action action-edit" style="float:right">
							<?php echo uh($label_edit) . PHP_EOL; ?>
							<img src="images/icons/pencil.png" class="icon_img" alt="" />
						</a>
						<a href="comment/delete/<?php echo $comment->get('id'); ?>" class="js-comment-action action-delete" style="float:right; padding-top:1em;">
							<?php echo uh($label_delete) . PHP_EOL; ?>
							<img src="images/icons/delete.png" class="icon_img" alt="" />
						</a>

<?php		if ($comment->get('position_name') != ''): ?>
					<p><strong><?php echo uh('(' . $label_position . '): ' . $comment->get('position_name')); ?></strong></p>
<?php		elseif ($comment->get('fair_name') != ''): ?>
					<p><strong><?php echo uh('(' . $label_fair . '): ' . $comment->get('fair_name')); ?></strong></p>
<?php		else: ?>
					<p><strong>(<?php echo uh($label_generic); ?>)</strong></p>
<?php		endif; ?>
					<p>
						<strong><?php echo uh($label_comment_type); ?>:</strong>
						<span data-key="type"><?php
						switch ($comment->get('type')) {
							case -1:
								echo '<span class="comment-negative">' . uh($label_comment_negative);
								break;

							case 0:
								echo '<span class="comment-neutral">' . uh($label_comment_neutral);
								break;

							case 1:
								echo '<span class="comment-positive">' . uh($label_comment_positive);
								break;
						}
						?></span></span>
					</p>
					<p>
						<strong><?php echo uh($comment->get('author')) . "</strong>" . ' (' . $comment->get('date') . ')'; ?>:
								
					</p>

					<p data-key="comment"><?php echo uh($comment->get('comment')); ?></p>
				</li>
