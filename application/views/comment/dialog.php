<?php if (!isset($exhibitors)): ?>
		<h3><?php echo uh(sprintf($label_headline, $label_headline_name)); ?></h3>
<?php endif; ?>

<?php if (isset($error)): ?>
		<p class="error"><?php echo uh($error); ?></p>
<?php endif; ?>

<?php if (isset($user)): ?>
		<p>
			<strong><?php echo uh($label_current_exhibitor); ?>:</strong> <?php echo uh($user->get('company')); ?>
		</p>
<?php endif; ?>

<?php if (isset($comments)): ?>
		<div class="commentList">
			<ul>
<?php	if (count($comments) > 0): ?>
<?php		foreach ($comments as $comment):
				require 'comment_item.php';
			endforeach; ?>
<?php	else: ?>
				<li class="empty-placeholder"><?php echo uh($label_no_comments); ?></li>
<?php	endif; ?>
			</ul>
		</div>
<?php endif; ?>

		<form action="comment/dialog/<?php echo uh($params); ?>" method="POST">
<?php if (isset($exhibitors)): ?>
			<h3><?php echo uh($label_comment_add_headline); ?></h3>

			<p>
				<strong><?php echo $label_exhibitor; ?>: </strong><br />
				<select name="exhibitor" class="js-user-select">
<?php	foreach ($exhibitors as $exhibitor): ?>
					<option value="<?php echo $exhibitor->get('id'); ?>"><?php echo uh($exhibitor->get('company')); ?></option>
<?php	endforeach; ?>
				</select>
			</P>
<?php endif; ?>
			<p>
				<strong><?php echo uh($label_comment_type_of); ?></strong><br />
				<select name="type">
					<option value="1"><?php echo uh($label_comment_positive); ?></option>
					<option value="0"><?php echo uh($label_comment_neutral); ?></option>
					<option value="-1"><?php echo uh($label_comment_negative); ?></option>
				</select>
			</p>
			<p>
				<strong><?php echo uh($label_comment_valid_for); ?></strong><br />

<?php if (isset($fairs)): ?>
				<input type="hidden" name="validfor" value="3" />
				<select name="fair">
					<option value="0"><?php echo uh($label_all_exhibitor_fairs); ?></option>
<?php	foreach ($fairs as $fair): ?>
					<option value="<?php echo $fair->id; ?>"><?php echo uh($fair->name); ?></option>
<?php	endforeach; ?>
				</select>
<?php else: ?>
				<select name="validfor">
					<option value="0"><?php echo uh($label_comment_pos_only); ?></option>
					<option value="1"><?php echo uh($label_comment_fair_only); ?></option>
					<option value="2"><?php echo uh($label_comment_all_fairs); ?></option>
				</select>
<?php endif; ?>
			</p>
			<p>
				<textarea name="comment" cols="30" rows="7"></textarea>
			</p>
			<p>
				<button name="save" value="1"><?php echo uh($label_comment_add); ?></button>
			</p>
		</form>
