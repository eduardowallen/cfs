<h1><?php echo $headline; ?></h1>

<p><a class="button add" href="fairMap/create/<?php echo $fair->get('id'); ?>"><?php echo $create_link; ?></a></p>

<table class="std_table">
	<thead>
		<tr>
			<th><?php echo $th_name; ?></th>
			<th><?php echo $th_file; ?></th>
			<th><?php echo $th_view; ?></th>
			<th><?php echo $th_edit; ?></th>
			<th><?php echo $th_delete; ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($fair->get('maps') as $map): ?>
		<tr>
			<td><?php echo $map->get('name'); ?></td>
			<td><?php echo $map->get("file_name"); ?></td>
			<td class="center"><a href="mapTool/map/<?php echo $fair->get('id'); ?>/none/<?php echo $map->get('id') ?>"><img src="images/icons/map_go.png" alt="" title="View map"/></a></td>
			<td class="center"><a href="fairMap/edit/<?php echo $map->get('id'); ?>/<?php echo $map->get('fair'); ?>"><img src="images/icons/map_edit.png" alt="" title="Edit map"/></a></td>
			<td class="center"><a onclick="return confirm('Really delete?');" href="fairMap/delete/<?php echo $fair->get('id'); ?>/<?php echo $map->get('id'); ?>"><img src="images/icons/delete.png" alt="" title="Delete map"/></a></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
