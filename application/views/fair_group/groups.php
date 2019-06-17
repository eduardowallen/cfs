<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>
	<br />
	<h1><?php echo $headline; ?></h1>

	<p><a class="button add" href="fairGroup/create"><?php echo $create_link; ?></a></p>
<?php	if (isset($groups)) { ?>
	<table class="std_table">
		<thead>
			<tr>
				<th><?php echo $th_group; ?></th>
				<th><?php echo $th_events; ?></th>
				<th><?php echo $th_invoice; ?></th>
				<th><?php echo $th_edit; ?></th>
				<th><?php echo $th_delete; ?></th>
			</tr>
		</thead>
		<tbody>
<?php		foreach ($groups as $group): ?>
			<tr>
				<td><?php echo $group->get('name'); ?></td>
				<td><?php echo count($group->get('fairs_rel')); ?></td>
				<td><?php echo $group->get('invoice_no'); ?></td>
				<td class="center"><a href="fairGroup/edit/<?php echo $group->get('id'); ?>"><img src="images/icons/pencil.png" class="icon_img" alt="" title="<?php echo $th_edit; ?>" /></a></td>
				<td class="center"><a style="cursor:pointer;" onclick="confirmDialog('<?php echo uh($translator->{"This will delete the group "}); echo uh($group->get("name")); ?>','<?php echo uh($translator->{"Delete group?"}); ?>', 'red', '<?php echo BASE_URL."fairGroup/delete/".$group->get("id"); ?>')"><img src="images/icons/delete.png" class="icon_img" alt="" title="<?php echo $th_delete; ?>" /></a></td>
			</tr>
	<?php endforeach; ?>
		</tbody>
	</table>
<?php	} else {	echo uh($no_groups_created); } ?>