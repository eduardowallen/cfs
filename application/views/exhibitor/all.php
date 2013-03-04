<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $headline; ?></h1>

<p><a class="button add" href="user/edit/new/1"><?php echo $create_link; ?></a></p>

<table class="std_table">
	<thead>
		<tr>
			<th><?php echo $th_company ?></th>
			<th><?php echo $th_orgnr ?></th>
			<th><?php echo $th_name ?></th>
			<th><?php echo $th_phone ?></th>
			<th><?php echo $th_email ?></th>
			<th><?php echo $th_fairs ?></th>
			<th><?php echo $th_bookings ?></th>
			<th><?php echo $th_last_login ?></th>
			<th><?php echo $th_created ?></th>
			<th><?php echo $th_edit ?></th>
			<th><?php echo $th_delete ?></th>
			<th><?php echo $th_resend ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($users as $user): ?>
		<tr>
			<td><?php echo $user->get('company'); ?></td>
			<td><?php echo $user->get('orgnr'); ?></td>
			<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>"><?php echo $user->get('name'); ?></a></td>
			<td><?php echo $user->get('phone1'); ?></td>
			<td><?php echo $user->get('email'); ?></td>
			<td class="center"><?php echo $user->get('fair_count'); ?></td>
			<td class="center"><?php echo $user->get('ex_count'); ?></td>
			<td><?php echo date('d/m/y', $user->get('last_login')); ?></td>
			<td><?php echo date('d/m/y', $user->get('created')); ?></td>
			<td class="center"><a href="user/edit/<?php echo $user->get('id') ?>"><img src="images/icons/pencil.png" alt="" title="<?php echo $translator->{'Edit'} ?>"/></a></td>
			<td class="center"><a onclick="return confirm('<?php echo $translator->{'Really delete?'} ?>');" href="exhibitor/deleteAccount/<?php echo $user->get('id') ?>"><img src="images/icons/delete.png" alt=""/></a></td>
			<td class="center"><a onclick="return confirm('<?php echo $translator->{'Really reset user password?'} ?>');" href="user/resendDetails/<?php echo $user->get('id') ?>"><img src="images/icons/delete.png" alt=""/></a></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>