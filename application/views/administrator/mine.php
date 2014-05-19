<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $headline; ?></h1>
<a class="button add" href="administrator/edit/new/"><?php echo $button_new ?></a>
<table class="std_table" style="width:100%;">
	<thead>
		<tr>
			<th><?php echo $th_name; ?></th>
			<th><?php echo $th_email; ?></th>
			<th><?php echo $th_phone; ?></th>
			<th><?php echo $th_position_count; ?></th>
			<th><?php echo $th_positions_edited; ?></th>
			<th><?php echo $th_total_fairs; ?></th>
			<th><?php echo $th_lastlogin; ?></th>
			<th><?php echo $th_locked; ?></th>
			<th><?php echo $th_edit; ?></th>
			<th><?php echo $th_delete; ?></th>
		</tr>
	</thead>
	<tbody>
	<pre>
		<?php foreach ($admins as $admin): ?>
		<tr>
			<td><?php echo $admin['name']; ?></td>
			<td><?php echo $admin['email']; ?></td>
			<td><?php echo $admin['phone1']; ?></td>
			<td class="center"><?php echo $admin['position_count']; ?></td>
			<td class="center"><?php echo $admin['log_count']; ?></td>
			<td class="center"><?php echo $admin['fair_count']; ?></td>
			<td class="center"><?php if ($admin['last_login']) { echo date('d-m-Y H:i:s', $admin['last_login']); } ?></td>
			<td class="center"><?php echo ($admin['locked']) ? $locked_yes : $locked_no; ?></td>
			<td class="center"><a href="administrator/edit/<?php echo $admin['id']; ?>"><img src="images/icons/pencil.png" alt="" title="<?php echo $th_edit; ?>" /></a></td>
			<td class="center"><a href="administrator/delete/<?php echo $admin['id']; ?>/no/mine"><img src="images/icons/delete.png" alt="" title="<?php echo $th_delete; ?>" /></a></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
