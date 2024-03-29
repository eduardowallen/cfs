<?php if (!isset($event_locked)) { ?>
	<?php if ($do == 'edit'): ?>
	<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/categories/<?php echo $fair_id; ?>'"><?php echo uh($translator->{'Go back'}); ?></button>
	<br />
	<h1><?php echo $headline; ?> <?php echo $fair->get('name'); ?></h1>
	<form action="fair/categories/<?php echo $fair_id; ?>/edit/<?php echo $item; ?>" method="post" style="background:#efefef; border:1px solid #b1b1b1; padding:20px;">
		<h3><?php echo $form_headline; ?></h3>
		<label for="name"><?php echo $name_label; ?> *</label>
		<input type="text" name="name" id="name" value="<?php echo $current_title ?>"/>
		<input type="submit" class="greenbutton mediumbutton" name="save" value="<?php echo $save_label; ?>" />
	</form>

	<?php else: ?>
	<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>
	<br />
	<h1><?php echo $headline; ?> <?php echo $fair->get('name'); ?></h1>
	<form action="fair/categories/<?php echo $fair_id; ?>" method="post" style="background:#efefef; border:1px solid #b1b1b1; padding:20px;">
		<h3><?php echo $form_headline; ?></h3>
		<label for="name"><?php echo $name_label; ?> *</label>
		<input type="text" name="name" id="name"/>
		<input type="submit" class="greenbutton mediumbutton" name="save" value="<?php echo $save_label; ?>" />
	</form>

	<?php if(count($categories) > 0) : ?>
		<div class="tbld">
			<table class="std_table">
				<thead>
					<tr>
						<th><?php echo $th_name; ?></th>
						<th><?php echo $th_edit; ?></th>
						<th><?php echo $th_delete; ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($categories as $cat): ?>
					<tr>
						<td><?php echo $cat->get('name'); ?></td>
						<td class="center"><a href="fair/categories/<?php echo $fair_id; ?>/edit/<?php echo $cat->get('id'); ?>"><img src="images/icons/pencil.png" alt="" title="<?php echo $th_edit; ?>" /></a></td>
						<td class="center"><a onclick="return confirm('<?php echo $confirm_delete; ?>')" href="fair/categories/<?php echo $fair_id; ?>/delete/<?php echo $cat->get('id'); ?>"><img src="images/icons/delete.png" alt="" title="<?php echo $th_delete; ?>" /></a></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
	<?php endif; ?>
<?php } else { ?>
	<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>
  <p><?php echo uh($translator->{'Event is locked and cannot be edited.'}); ?></p>
<?php } ?>