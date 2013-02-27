<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $fair->get('name'); ?></h1>
<h2 style="margin-top:20px"><?php echo $headline; ?></h2>

<?php if ($hasRights): ?>

<table class="std_table" style="width: 100%;">
<thead>
	<tr>
		<th><?php echo $tr_pos; ?></th>
		<th><?php echo $tr_area; ?> (m<sup>2</sup>)</th>
		<th><?php echo $tr_booker; ?></th>
		<th><?php echo $tr_field; ?></th>
		<th><?php echo $tr_time; ?></th>
		<th><?php echo $tr_message; ?></th>
		<th><?php echo $tr_view; ?></th>
		<th><?php echo $tr_delete; ?></th>
	</tr>
</thead>
<tbody>
<?php foreach($positions as $pos): ?>
	<tr>
		<td><?php echo $pos['name']; ?></td>
		<td class="center"><?php echo $pos['area']; ?></td>
		<td class="center"><?php echo $pos['company']; ?></td>
		<td class="center"><?php echo $pos['commodity']; ?></td>
		<td><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
		<td title="<?php echo $pos['arranger_message']; ?>"><?php echo substr($pos['arranger_message'], 0, 50); ?></td>
		<td class="center">
			<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position']; ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>

<h2 style="margin-top:20px"><?php echo $rheadline; ?></h2>

<table class="std_table" style="width: 100%;">
<thead>
	<tr>
		<th><?php echo $tr_pos; ?></th>
		<th><?php echo $tr_area; ?> (m<sup>2</sup>)</th>
		<th><?php echo $tr_booker; ?></th>
		<th><?php echo $tr_field; ?></th>
		<th><?php echo $tr_time; ?></th>
		<th><?php echo $tr_message; ?></th>
		<th><?php echo $tr_view; ?></th>
		<th><?php echo $tr_deny; ?></th>
		<th><?php echo $tr_approve; ?></th>
	</tr>
</thead>
<tbody>
<?php foreach($rpositions as $pos): ?>
	<tr>
		<td><?php echo $pos['name']; ?></td>
		<td class="center"><?php echo $pos['area']; ?></td>
		<td class="center"><?php echo $pos['company']; ?></td>
		<td class="center"><?php echo $pos['commodity']; ?></td>
		<td><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
		<td title="<?php echo $pos['arranger_message']; ?>"><?php echo substr($pos['arranger_message'], 0, 50); ?></td>
		<td class="center">
			<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position']; ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>" title="<?php echo $tr_delete; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_delete; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'administrator/approveReservation/'.$pos['position'] ?>" title="<?php echo $tr_approve; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_approve; ?>" />
			</a>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>

<h2 style="margin-top:20px"><?php echo $prel_table; ?></h2>
<table class="std_table" style="width: 100%;">
<thead>
	<tr>
		<th><?php echo $tr_pos; ?></th>
		<th><?php echo $tr_area; ?> (m<sup>2</sup>)</th>
		<th><?php echo $tr_booker; ?></th>
		<th><?php echo $tr_field; ?></th>
		<th><?php echo $tr_time; ?></th>
		<th><?php echo $tr_message; ?></th>
		<th><?php echo $tr_view; ?></th>
		<th><?php echo $tr_deny; ?></th>
		<th><?php echo $tr_approve; ?></th>
		<th><?php echo $tr_reserve; ?></th>
	</tr>
</thead>
<tbody>
<?php foreach($prelpos as $pos): ?>
	<tr>
		<td><?php echo $pos['name']; ?></td>
		<td class="center"><?php echo $pos['area']; ?></td>
		<td class="center"><?php echo $pos['company']; ?></td>
		<td class="center"><?php echo $pos['commodity']; ?></td>
		<td class="center"><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
		<td title="<?php echo $pos['arranger_message']; ?>"><?php echo substr($pos['arranger_message'], 0, 50); ?></td>
		<td class="center">
			<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'] ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'administrator/newReservations/deny/'.$pos['id'] ?>" title="<?php echo $tr_deny; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_deny; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'administrator/newReservations/approve/'.$pos['id'] ?>" title="<?php echo $tr_approve; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_approve; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'administrator/reservePrelBooking/'.$pos['id'] ?>" title="<?php echo $tr_reserve; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_reserve; ?>" />
			</a>
		</td>

	</tr>
<?php endforeach; ?>
</tbody>
</table>


<?php else: ?>

<p>Du är inte behörig att administrera den här mässan.</p>

<?php endif; ?>