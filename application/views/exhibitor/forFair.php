<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $headline; ?></h1>

<p><a class="button add" href="administrator/newExhibitor"><?php echo $create_link; ?></a></p>

<h2><?php echo $table_exhibitors ?></h2>
<table class="std_table">
	<thead>
		<tr>
			<th><?php echo $th_company ?></th>
			<th><?php echo $th_name ?></th>
			<th><?php echo $th_fairs ?></th>
			<th><?php echo $th_bookings ?></th>
			<th><?php echo $th_last_login ?></th>
			<!--<th><?php echo $th_edit ?></th>
			<th><?php echo $th_delete ?></th>-->
		</tr>
	</thead>
	<tbody>
		<?php foreach ($users as $user): ?>
		<tr>
			<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>"><?php echo $user->get('company'); ?></a></td>
			<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>"><?php echo $user->get('name'); ?></a></td>
			<td class="center"><?php echo $user->get('fair_count'); ?></td>
			<td class="center"><?php echo $user->get('ex_count'); ?></td>
			<td><?php echo date('d/m/y', $user->get('last_login')); ?></td>
			<!--<td class="center"><a href="user/edit/<?php echo $user->get('id') ?>"><img src="images/icons/pencil.png" alt="" title="<?php echo $translator->{'Edit'} ?>"/></a></td>
			<td class="center"><a onclick="return confirm('<?php echo $translator->{'Really delete?'} ?>');" href="exhibitor/deleteAccount/<?php echo $user->get('id') ?>"><img src="images/icons/delete.png" alt=""/></a></td>-->
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<h2><?php echo $table_connected ?></h2>
<table class="std_table">
	<thead>
		<tr>
			<th><?php echo $th_company ?></th>
			<th><?php echo $th_name ?></th>
			<th><?php echo $th_fairs ?></th>
			<!--<th><?php echo $th_bookings ?></th>-->
			<th><?php echo $th_last_login ?></th>
			<th><?php echo $th_copy; ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($connected as $user): ?>
		<tr>
			<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>"><?php echo $user->get('company'); ?></a></td>
			<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>"><?php echo $user->get('name'); ?></a></td>
			<td class="center"><?php echo $user->get('fair_count'); ?></td>
			<!--<td class="center"><?php echo $user->get('ex_count'); ?></td>-->
			<td><?php echo date('d/m/y', $user->get('last_login')); ?></td>
			<td class="center"><a href="exhibitor/forFair/copy/<?php echo $user->get('id'); ?>"><img src="images/icons/user_go.png" alt=""/></a></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>