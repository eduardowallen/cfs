					<tr data-model="Comment">
<<<<<<< HEAD
						<td data-key="author"><?php echo uh($comment->get('author')); ?></td>
=======
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
						<td><a href="exhibitor/profile/<?php echo $comment->get('exhibitor'); ?>" class="showProfileLink"><?php echo uh($comment->get('exhibitor_name')); ?></a></td>
						<td data-key="comment"><?php echo wordwrap(uh($comment->get('comment')), 50, '<br />'); ?></td>
						<td data-key="type">
							<?php
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
							?></span>
						</td>
<<<<<<< HEAD
						<td><?php echo date('d-m-Y H:i:s', strtotime($comment->get('date'))); ?></td>
						<td class="center">
						<a href="#" class="js-show-comment-dialog" data-user="<?php echo $comment->get('exhibitor'); ?>" title="<?php echo $label_headline; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/notes.png" alt="<?php echo $label_headline; ?>" /></a>
						</td>
						<td class="center">			
							<a href="comment/edit/<?php echo $comment->get('id'); ?>" class="js-comment-action" ><img src="images/icons/pencil.png" alt="<?php echo uh($label_edit); ?>" /></a>
						</td>
						<td class="center">
							<a href="comment/delete/<?php echo $comment->get('id'); ?>" class="js-comment-action" ><img src="images/icons/delete.png" alt="<?php echo uh($label_delete); ?>" /></a>
						</td>
						<td class="center">
							<input type="checkbox" name="rows[]" value="<?php echo $comment->get('id'); ?>" class="comment-rows" />
=======
						<td><?php echo date('d-m-Y H:i:s', strtotime($comment->get('date'))); ?> <?php echo TIMEZONE; ?></td>
						<td class="center">
							<a href="comment/edit/<?php echo $comment->get('id'); ?>" class="js-comment-action"><img src="images/icons/pencil.png" alt="<?php echo uh($label_edit); ?>" /></a>
						</td>
						<td class="center">
							<a href="comment/delete/<?php echo $comment->get('id'); ?>" class="js-comment-action"><img src="images/icons/delete.png" alt="<?php echo uh($label_delete); ?>" /></a>
						</td>
						<td class="center">
							<input type="checkbox" name="rows[]" value="<?php echo $comment->get('id'); ?>" class="comment-rows" checked="checked" />
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
						</td>
					</tr>
