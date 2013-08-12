<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $headline; ?></h1>

<p><a class="button add" href="fair/edit/new"><?php echo $create_link; ?></a></p>

<table class="std_table of-tables" style="min-width:100%;">
	<!--<thead>
		<tr>
			<th><?php echo $th_created; ?></th>
			<th><?php echo $th_auto_publish; ?></th>
			<th><?php echo $th_auto_close; ?></th>
			<!--<th><?php echo $th_closed; ?></th>
			<?php if (userLevel() > 3): ?>
			<th><?php echo $th_arranger_name; ?></th>
			<?php endif; ?>
			<th><?php echo $th_arranger_cnr; ?></th>
			<th><?php echo $th_fair; ?></th>
			<th><?php echo $th_booked; ?></th>
			<th><?php echo $th_available; ?></th>
			<th><?php echo $th_approved; ?></th>
			<th><?php echo $th_maps; ?></th>
			<th><?php echo $th_categories; ?></th>
			<th><?php echo $th_admins; ?></th>
			<th><?php echo $th_exhibitors; ?></th>
			<th><?php echo $th_settings; ?></th>
			<th><?php echo $th_delete; ?></th>
		</tr>
	</thead>-->
	<tbody>
		<?php foreach ($fairs as $fair): ?>
		<tr class="container">
			<td class="container">
				<table style="min-width:100%;">
					<tr>
						<td><?php echo $th_fair ?>: <a href="mapTool/map/<?php echo $fair->get('id'); ?>"><?php echo $fair->get('name'); ?></a></td>
						<td><?php echo $th_approved ?>: 
							<?php
								if($fair->get('approved') == 2){
									echo '<span class="error">'.$app_locked.'</span>';
								}elseif($fair->get('approved') == 1){
									echo '<span class="ok">'.$app_yes.'</span>';
			
								}elseif($fair->get('approved') == 0){
									echo '<span class="error">'.$app_no.'</span>';
								}
							?>
						</td>
						<td><?php echo $th_booked ?>: <?php echo $fair->get('booked'); ?></td>
					</tr>
					<tr>
						<td><?php echo $th_max_positions ?>: <?php echo $fair->get('max_positions') ?></td>
						<td><?php echo $th_maps ?>: <a href="fair/maps/<?php echo $fair->get('id'); ?>"><?php echo count($fair->get('maps')); ?></a></td>
						<td><?php echo $th_reserved ?>: <?php echo $fair->get('reserved'); ?></td>
					</tr>
					<tr>
						<td><?php echo $th_page_views ?>: <?php echo $fair->get('page_views') ?></td>
						<td><?php echo $th_available ?>: <?php echo $fair->get('total') - $fair->get('booked') - $fair->get('reserved') ?></td>
						<td><?php echo $th_created ?>: <?php echo date('d-m-Y H:i:s', $fair->get('creation_time')); ?></td>
						
					</tr>
					<tr>
						<td><?php echo $th_total ?>: <?php echo $fair->get('total'); ?></td>
						<td><?php echo $th_auto_publish ?>: <?php echo date('d-m-Y H:i:s', $fair->get('auto_publish')); ?></td>
						<td><?php echo $th_auto_close ?>: <?php echo date('d-m-Y H:i:s', $fair->get('auto_close')); ?></td>
					</tr>
					<?php if (userLevel() > 3): ?>
					<tr>
						<td colspan="2"><?php echo $th_arranger_name ?>: <a href="arranger/info/<?php echo $fair->get('created_by')?>"><?php echo $fair->get('arranger_name'); ?></a></td>
						<td><?php echo $th_arranger_cnr ?>: <?php echo $fair->get('arranger_cnr'); ?></td>
					</tr>
					<?php endif; ?>
					<tr>
						<td colspan="3">
							<span class="td_button"><a href="fair/categories/<?php echo $fair->get('id'); ?>"><?php echo $th_categories ?></a></span>
							<span class="td_button"><a href="administrator/overview/<?php echo $fair->get('id'); ?>"><?php echo $th_admins ?></a></span>
							<span class="td_button"><a href="exhibitor/exhibitors/<?php echo $fair->get('id'); ?>"><?php echo $th_exhibitors ?></a></span>
							<span class="td_button"><a href="fair/edit/<?php echo $fair->get('id'); ?>"><?php echo $th_settings ?></a></span>
							<?php if(/*$fair->get('approved') != 2 && */userLevel() == 4) : ?>
							<span class="td_button"><a	href="fair/delete/<?php echo $fair->get('id'); ?>"><?php echo $th_delete ?></a></span>
							<?php endif; ?>
						</td>
					</tr>
				</table>
			</td>
			
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<script>
	$(document).ready(function(){
		$('.IsDisabled').click(function(e){
			e.preventDefault();
		});
	});
</script>
