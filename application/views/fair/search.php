			<script src="js/tablesearch.js"></script>

			<h1><?php echo uh($label_headline); ?></h1>

<?php if (count($fairs) > 0): ?>
			<table class="std_table use-scrolltable" id="fair_search">
				<thead>
					<tr>
						<th><?php echo uh($label_fairname); ?></th>
						<th><?php echo uh($label_closing_date); ?></th>
						<th><?php echo uh($label_homepage); ?></th>
						<th><?php echo uh($label_bookings); ?></th>
						<th><?php echo uh($label_registration); ?></th>
						<th data-sorter="false"><?php echo uh($label_go_to_event); ?></th>
					</tr>
				</thead>
				<tbody>
<?php	foreach ($fairs as $fair): ?>
					<tr>
						<td><?php echo uh($fair->name); ?></td>
						<td><?php echo date('d-m-Y H:i:s', $fair->auto_close) . ' ' . TIMEZONE; ?></td>
						<td><?php
							if ($fair->website != '') {
								echo '<a href="' . uh($fair->website) . '" target="_blank">' . uh($fair->website) . '</a>';
							}
						?></td>
						<td><?php echo $fair->cnt_exhibitors + $fair->cnt_prel_bookings; ?></td>
						<td><?php echo ($fair->hidden ? $label_hidden : $label_open); ?></td>
						<td>
							<a href="<?php echo $fair->url; ?>" target="_blank" title="<?php echo uh($label_go_to_event); ?>">
								<img src="images/icons/map_go.png" alt="<?php echo uh($label_go_to_event); ?>" />
							</a>
						</td>
					</tr>
<?php	endforeach; ?>
				</tbody>
			</table>
<?php else: ?>
			<p>
				<?php echo uh($label_no_fairs); ?>
			</p>
<?php endif; ?>