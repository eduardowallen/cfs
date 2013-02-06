<h1><?php echo $headline; ?></h1>

<p><a class="button add" href="administrator/edit/new"><?php echo $create_link; ?></a></p>

<table class="std_table">
	<thead>
		<tr>
			<th>User</th>
			<th>Fair</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($users as $user): ?>
		<tr>
			<td><a href="administrator/edit/<?php echo $user->get('id'); ?>"<?php if ($user->get('locked') == 1) { echo ' class="crossedout"'; } ?>><?php echo $user->get('name'); ?></a></td>
			<td><?php echo $user->get('fair_name'); ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>