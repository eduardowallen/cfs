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
	<script type="text/javascript" src="js/tablesearch.js<?php echo $unique?>"></script>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>	
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
		<?php 
		$fair = new Fair;
		$fair->load($_SESSION['user_fair'], 'id');
			if($fair->get('modules') === '{"smsFunction":["1"]}') { ?>		
			<button type="submit" class="open-sms-send" name="send_sms" title="<?php echo uh($send_sms_label); ?>" data-for="exhibitors_list" data-fair="<?php echo $fairId; ?>"></button>
		<?php } ?>
			<button type="submit" class="open-excel-export" name="export_excel" title="<?php echo uh($export_button); ?>" data-for="exhibitors_list"></button>
		</div>

		<table class="std_table use-scrolltable" id="exhibitors_list">
			<thead>
				<tr>
					<th><?php echo $th_status; ?></th>
					<th class="left"><?php echo $th_name; ?></th>
					<th class="left"><?php echo $th_company; ?></th>
<!--				<th><?php echo $th_address; ?></th>-->
					<th class="left"><?php echo $th_branch; ?></th>
					<th><?php echo $th_phone; ?></th>
					<th class="left"><?php echo $th_contact; ?></th>
					<th class="left"><?php echo $th_email; ?></th>
<!--				<th><?php echo $th_website; ?></th>-->
<!--					<th data-sorter="false"><?php echo $th_view; ?></th>-->
					<?php if (userLevel() > 0): ?>
<!--				<th data-sorter="false"><?php echo $th_profile; ?></th>-->
						<th class="last" data-sorter="false">
							<input type="checkbox" id="check-all" class="check-all" data-group="rows" />
							<label class="squaredFour" for="check-all" />
						</th>
					<?php endif; ?>
				
				</tr>
			</thead>
			<tbody>
<?php foreach ($exhibitors as $pos): ?>
				<tr>
					<td><?php echo ($pos['posstatus'] == 2 ? $label_booked : ($pos['posstatus'] == 1 ? $label_reserved : '')); ?></td>
					<td class="left"><?php echo $pos['posname']; ?></td>
					<td class="left"><?php echo $pos['company']; ?></td>
<!--				<td class="center"><?php echo $pos['address']; ?></td>-->
					<td class="left">
					<?php
						$commodity = $pos['commodity'];
						echo ( empty( $commodity ) ) ? $pos['excommodity'] : $pos['commodity'] ;
					?>
					</td>
					<td class="center"><?php echo $pos['contact_phone2']; ?></td>
					<td class="left"><a href="exhibitor/profile/<?php echo $pos['id']; ?>" class="showProfileLink"><?php echo $pos['name']; ?></a></td>
					<td class="left"><?php echo $pos['email']; ?></td>
<!--				<td class="center"><a target="_blank" href="<?php echo (stristr($pos['website'], 'http://') ? $pos['website'] : 'http://' . $pos['website']); ?>"><?php echo $pos['website']; ?></a></td>-->
<!--				<td class="center"><a href="mapTool/map/<?php echo $pos['fair'].'/'.$pos['position'].'/'.$pos['posmap']; ?>"><img src="images/icons/map_go.png" class="icon_img" alt="" title="<?php echo $th_view; ?>" /></a></td>-->
					<?php if (userLevel() > 0): ?>
<!--				<td class="center"><a href="exhibitor/profile/<?php echo $pos['id']; ?>" class="showProfileLink"><img src="images/icons/user.png" alt="" title="<?php echo $th_profile; ?>" /></a></td>-->
				
					<td><input type="checkbox" name="rows[]" class="rows" value="<?php echo $pos['position']; ?>" data-userid="<?php echo $pos['id']; ?>" /><label class="squaredFour" for="<?php echo $pos['id']; ?>" /></td>
					<?php endif; ?>
				</tr>
<?php endforeach; ?>
			</tbody>
		</table>
	</form>

<?php else: ?>
	<p><?php echo "No exhibitors was found for this fair."?></p>
<?php endif; ?>
