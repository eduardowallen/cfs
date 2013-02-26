<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $headline; ?></h1>

<?php if (userLevel() > 2): ?>

<input type="button" value="<?php echo $export_button ?>" class="floatright" onclick="document.location.href='exhibitor/export/<?php echo $fairId; ?>'"/>

<?php endif; ?>

<table class="std_table">
	<thead>
		<tr>
			<th><?php echo $th_status; ?></th>
			<th><?php echo $th_name; ?></th>
			<th><?php echo $th_company; ?></th>
			<th><?php echo $th_branch; ?></th>
			<th><?php echo $th_phone; ?></th>
			<th><?php echo $th_contact; ?></th>
			<th><?php echo $th_email; ?></th>
			<th><?php echo $th_website; ?></th>
			<th><?php echo $th_view; ?></th>
			<?php if (userLevel() > 0): ?>
			<th><?php echo $th_profile; ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($exhibitors as $pos): ?>
		<tr>
			<td><?php echo ($pos['posstatus'] == 2 ? 'booked' : ($pos['posstatus'] == 1 ? 'reserved' : '')); ?></td>
			<td class="center"><?php echo $pos['posname']; ?></td>
			<td class="center"><?php echo $pos['company']; ?></td>
			<td class="center">
				<?php
				$commodity = $pos['commodity'];
				echo ( empty( $commodity ) ) ? $pos['excommodity'] : $pos['commodity'] ;
				?>
			</td>
			<td class="center"><?php echo $pos['phone1']; ?></td>
			<td class="center"><?php echo $pos['name']; ?></td>
			<td class="center"><?php echo $pos['email']; ?></td>
			<td class="center"><a target="_blank" href="<?php echo (stristr($pos['website'], 'http://') ? $pos['website'] : 'http://' . $pos['website']); ?>"><?php echo $pos['website']; ?></a></td>
			<td class="center"><a href="mapTool/map/<?php echo $pos['fair'].'/'.$pos['position'].'/'.$pos['posmap']; ?>"><img src="images/icons/map_go.png" alt=""/></a></td>
			<?php if (userLevel() > 0): ?>
			<td class="center"><a href="exhibitor/profile/<?php echo $pos['id']; ?>"><img src="images/icons/user.png" alt=""/></a></td>
			<?php endif; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
