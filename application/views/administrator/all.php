<script type="text/javascript" src="js/tablesearch.js<?php echo $unique?>"></script>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>start/home'"><?php echo uh($translator->{'Go back'}); ?></button>
<h1><?php echo $headline; ?></h1>
<?php
	//CurrencyCalculator::convertValues("SEK", "USD");
?>
<p><a class="button add" href="administrator/edit/new"><?php echo $create_link; ?></a></p>
<p><a class="button add" href="mailto:<?php
	$count=0;
	foreach ($admins as $user): 
		if($count == 0):
			echo "?bcc=".$user['email'];
		else:
			echo "&bcc=".$user['email'];
		endif;
		$count++;
	endforeach;?>"><?php echo uh($translator->{'Send mail'}); ?></a></p>
<div class="tbld">
	<table class="std_table" style="width:100%;">
		<thead>
			<tr>
				<th><?php echo $th_name; ?></th>
				<th><?php echo $th_email; ?></th>
				<th><?php echo $th_phone; ?></th>
				<th><?php echo $th_position_count; ?></th>
				<th><?php echo $th_positions_edited; ?></th>
				<th><?php echo $th_total_fairs; ?></th>
				<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $th_lastlogin; ?></th>
				<th><?php echo $th_locked; ?></th>
				<th><?php echo $th_edit; ?></th>
				<th><?php echo $th_delete; ?></th>
			</tr>
		</thead>
		<tbody>
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
				<td class="center"><a href="administrator/edit/<?php echo $admin['id']; ?>"><img src="images/icons/pencil.png" class="icon_img" alt="" title="<?php echo $th_edit; ?>" /></a></td>
				<td class="center"><a href="administrator/delete/<?php echo $admin['id']; ?>/no/all"><img src="images/icons/delete.png" class="icon_img" alt="" title="<?php echo $th_delete; ?>" /></a></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
