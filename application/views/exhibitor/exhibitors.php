<?php
global $translator;

$column_info = array(
	'' => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'edit_time' => $translator->{'Last edited'},
		'arranger_message' => $translator->{'Message to organizer'}
	),
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
?>
	<script type="text/javascript" src="js/tablesearch.js"></script>
	<h1><?php echo $headline; ?></h1>

<?php if (userLevel() > 2): ?>
	<script type="text/javascript">
		var export_fields = {
			exhibitors_list: <?php echo json_encode($column_info); ?>

		};
	</script>
<?php endif; ?>


<?php if(count($exhibitors) > 0): ?>
	<!--<p><a class="button add" href="mailto:<?php
	$count=0;
	foreach ($exhibitors as $user): 
		if($count == 0):
			echo "?bcc=".$user['email'];
		else:
			echo "&bcc=".$user['email'];
		endif;
		$count++;
	endforeach;?>"><?php echo uh($translator->{'Send mail'}); ?></a></p>-->

	<form action="exhibitor/export2/<?php echo $fairId; ?>" method="post">
		<div class="floatright right">
			<button type="submit" class="open-sms-send" name="send_sms" data-for="exhibitors_list" data-fair="<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($send_sms_label); ?></button><br />
			<button type="submit" class="open-excel-export" name="export_excel" data-for="exhibitors_list"><?php echo uh($export_button); ?></button>
		</div>

		<table class="std_table use-scrolltable" id="exhibitors_list">
			<thead>
				<tr>
					<th><?php echo $th_status; ?></th>
					<th><?php echo $th_name; ?></th>
					<th><?php echo $th_company; ?></th>
					<th><?php echo $th_address; ?></th>
					<th><?php echo $th_branch; ?></th>
					<th><?php echo $th_phone; ?></th>
					<th><?php echo $th_contact; ?></th>
					<th><?php echo $th_email; ?></th>
					<th><?php echo $th_website; ?></th>
					<th data-sorter="false"><?php echo $th_view; ?></th>
					<?php if (userLevel() > 0): ?>
					<th data-sorter="false"><?php echo $th_profile; ?></th>
					<th data-sorter="false"><input type="checkbox" class="check-all" data-group="rows" checked="checked" /></th>
					<?php endif; ?>
				
				</tr>
			</thead>
			<tbody>
<?php foreach ($exhibitors as $pos): ?>
				<tr>
					<td><?php echo ($pos['posstatus'] == 2 ? $label_booked : ($pos['posstatus'] == 1 ? $label_reserved : '')); ?></td>
					<td class="center"><?php echo $pos['posname']; ?></td>
					<td class="center"><?php echo $pos['company']; ?></td>
					<td class="center"><?php echo $pos['address']; ?></td>
					<td class="center">
					<?php
						$commodity = $pos['commodity'];
						echo ( empty( $commodity ) ) ? $pos['excommodity'] : $pos['commodity'] ;
					?>
					</td>
					<td class="center"><?php echo $pos['phone1']; ?></td>
					<td class="center"><a href="exhibitor/profile/<?php echo $pos['id']; ?>" class="showProfileLink"><?php echo $pos['name']; ?></a></td>
					<td class="center"><?php echo $pos['email']; ?></td>
					<td class="center"><a target="_blank" href="<?php echo (stristr($pos['website'], 'http://') ? $pos['website'] : 'http://' . $pos['website']); ?>"><?php echo $pos['website']; ?></a></td>
					<td class="center"><a href="mapTool/map/<?php echo $pos['fair'].'/'.$pos['position'].'/'.$pos['posmap']; ?>"><img src="images/icons/map_go.png" alt="" title="<?php echo $th_view; ?>" /></a></td>
					<?php if (userLevel() > 0): ?>
					<td class="center"><a href="exhibitor/profile/<?php echo $pos['id']; ?>" class="showProfileLink"><img src="images/icons/user.png" alt="" title="<?php echo $th_profile; ?>" /></a></td>
				
					<td><input type="checkbox" name="rows[]" class="rows" value="<?php echo $pos['position']; ?>" data-userid="<?php echo $pos['id']; ?>" checked="checked" /></td>
					<?php endif; ?>
				</tr>
<?php endforeach; ?>
			</tbody>
		</table>
	</form>

<?php else: ?>
	<p><?php echo "No exhibitors was found for this fair."?></p>
<?php endif; ?>
