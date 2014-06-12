<?php
  global $translator;
  if(!$hasRights):
?>
	<p><?php echo uh($translator->{'You are not authorized to administer this fair.'}); ?></p>
<?php
    return;
  endif;

$general_column_info = array(
	$translator->{"Company"} => array(
		'orgnr' => $translator->{'Organization number'},
		'company' => $translator->{'Company'},
		'commodity' => $translator->{'Commodity'},
		'address' => $translator->{'Address'},
		'zipcode' => $translator->{'Zip code'},
		'city' => $translator->{'City'},
		'country' => $translator->{'Country'},
		'phone1' => $translator->{'Phone 1'},
		'phone2' => $translator->{'Phone 2'},
		'fax' => $translator->{'Fax number'},
		'email' => $translator->{'E-mail'},
		'website' => $translator->{'Website'}
	),
	$translator->{"Billing address"} => array(
		'invoice_company' => $translator->{'Company'},
		'invoice_address' => $translator->{'Address'},
		'invoice_zipcode' => $translator->{'Zip code'},
		'invoice_city' => $translator->{'City'},
		'invoice_country' => $translator->{'Country'},
		'invoice_email' => $translator->{'E-mail'}
	),
	$translator->{"Contact person"} => array(
		'name' => $translator->{'Contact person'},
		'contact_phone' => $translator->{'Contact Phone'},
		'contact_phone2' => $translator->{'Contact Phone 2'},
		'contact_email' => $translator->{'Contact Email'}
	)
);

$bookings_columns = array(
	'' => array(
		'status' => $translator->{'Status'},
		'fair_count' => $th_fairs,
		'ex_count' => $th_bookings,
		'last_login' => $th_last_login
	)
);
$bookings_columns = array_merge($bookings_columns, $general_column_info);

$connected_columns = array(
	'' => array(
		'status' => $translator->{'Status'},
		'fair_count' => $th_fairs,
		'last_login' => $th_last_login,
		'connected_time' => $th_connect_time
	)
);
$connected_columns = array_merge($connected_columns, $general_column_info);
?>

<script type="text/javascript" src="js/tablesearch.js"></script>
<script type="text/javascript">
	var export_fields = {
		booked: <?php echo json_encode($bookings_columns); ?>,

		connected: <?php echo json_encode($connected_columns); ?>

	};
</script>

<style>
	#content {
		max-width: 1280px;
	}
</style>

<h1><?php echo $headline; ?></h1>
<p><a class="button add" href="administrator/newExhibitor"><?php echo $create_link; ?></a></p>

<h2 class="tblsite"><?php echo $table_exhibitors ?></h2>

<?php if (count($users) > 0): ?>

	<form action="exhibitor/exportForFair/1" method="post">
		<button type="submit" class="open-excel-export" name="export_excel" data-for="booked" style="float:right;"><?php echo uh($export); ?></button>

		<table class="std_table use-scrolltable" id="booked">
		<?php if (userLevel() > 2): ?>

		<?php endif; ?>
			<thead>
				<tr>
					<th><?php echo $th_company ?></th>
					<th><?php echo $th_name ?></th>
					<th><?php echo $th_fairs ?></th>
					<th><?php echo $th_bookings ?></th>
					<th><?php echo $th_last_login ?></th>
					<th data-sorter="false"><input type="checkbox" class="check-all" data-group="rows-1" checked="checked" /></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($users as $user): ?>
					<tr>
						<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>" class="showProfileLink"><?php echo $user->get('company'); ?></a></td>
						<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>" class="showProfileLink"><?php echo $user->get('name'); ?></a></td>
						<td class="center"><?php echo $user->get('fair_count');?></td>
						<td class="center"><?php echo $user->get('ex_count');?></td>
						<td><?php echo date('d-m-Y H:i:s', $user->get('last_login'));?></td>
						<td><input type="checkbox" name="rows[]" value="<?php echo $user->get('id'); ?>" checked="checked" /></td>
						<!--<td class="center"><a href="user/edit/<?php echo $user->get('id') ?>"><img src="images/icons/pencil.png" alt="" title="<?php echo uh($translator->{'Edit'}); ?>"/></a></td>
						<td class="center"><a onclick="return confirm('<?php echo uh($translator->{'Really delete?'}); ?>');" href="exhibitor/deleteAccount/<?php echo $user->get('id') ?>"><img src="images/icons/delete.png" alt=""/></a></td>-->
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</form>

<?php else : ?>
	<p>Det finns inga inbokade utställare ännu.</p>
<?php endif;?>

<h2 class="tblsite"><?php echo $table_connected ?></h2>

<?php if(count($connected) > 0 ) : ?>

	<form action="exhibitor/exportForFair/2" method="post">
		<button type="submit" class="open-excel-export" name="export_excel" data-for="connected" style="float:right;"><?php echo uh($export); ?></button>

		<table class="std_table use-scrolltable" id="connected">
			<thead>
				<tr>
					<th><?php echo $th_company ?></th>
					<th><?php echo $th_name ?></th>
					<th><?php echo $th_fairs ?></th>
					<!--<th><?php echo $th_bookings ?></th>-->
					<th><?php echo $th_last_login ?></th>
					<th><?php echo $th_connect_time ?></th>
					<th data-sorter="false"><input type="checkbox" class="check-all" data-group="rows-2" checked="checked" /></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($connected as $user): ?>
					<tr>
						<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>" class="showProfileLink"><?php echo $user->get('company'); ?></a></td>
						<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>" class="showProfileLink"><?php echo $user->get('name'); ?></a></td>
						<td class="center"><?php echo $user->get('fair_count'); ?></td>
						<!--<td class="center"><?php echo $user->get('ex_count'); ?></td>-->
						<td><?php echo date('d-m-Y H:i:s', $user->get('last_login')); ?></td>
						<td><?php if ($user->get('connected_time')) echo date('d-m-Y H:i:s', $user->get('connected_time')); else echo 'n/a'; ?></td>
						<td><input type="checkbox" name="rows[]" value="<?php echo $user->get('id'); ?>" checked="checked" /></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</form>

<?php else : ?>
	<p>Det finns inga anslutna utställare.</p>
<?php endif;?>