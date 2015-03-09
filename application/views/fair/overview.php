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
						<td><?php echo $th_fair ?>: <a href="mapTool/map/<?php echo $fair->id; ?>"><?php echo $fair->name; ?></a></td>
						<td><?php echo $th_approved ?>: 
							<?php
								if ($fair->approved == 2) {
									echo '<span class="error">'.$app_locked.'</span>';
								} else if ($fair->approved == 1) {
									echo '<span class="ok">'.$app_yes.'</span>';
			
								} else if($fair->approved == 0) {
									echo '<span class="error">'.$app_no.'</span>';
								}
							?>
						</td>
						<td><?php echo $th_booked ?>: <?php echo $fair->booked_cnt; ?></td>
					</tr>
					<tr>
						<td><?php echo $th_max_positions ?>: <?php echo $fair->max_positions ?></td>
						<td><?php echo $th_maps ?>: <a href="fair/maps/<?php echo $fair->id; ?>"><?php echo $fair->maps_cnt; ?></a></td>
						<td><?php echo $th_reserved ?>: <?php echo $fair->reserved_cnt; ?></td>
					</tr>
					<tr>
						<td><?php echo $th_page_views ?>: <?php echo $fair->page_views ?></td>
						<td><?php echo $th_available ?>: <?php echo $fair->total_cnt - $fair->booked_cnt - $fair->reserved_cnt ?></td>
						<td><?php echo $th_created ?>: <?php echo date('d-m-Y H:i:s', $fair->creation_time); ?></td>
						
					</tr>
					<tr>
						<td><?php echo $th_total ?>: <?php echo $fair->total_cnt; ?></td>
						<td><?php echo $th_auto_publish ?>: <?php echo date('d-m-Y H:i:s', $fair->auto_publish); ?></td>
						<td><?php echo $th_auto_close ?>: <?php echo date('d-m-Y H:i:s', $fair->auto_close); ?></td>
					</tr>
					<?php if (userLevel() > 3): ?>
					<tr>
						<td colspan="2"><?php echo $th_arranger_name ?>: <a href="arranger/info/<?php echo $fair->created_by; ?>"><?php echo $fair->arranger_name; ?></a></td>
						<td><?php echo $th_arranger_cnr ?>: <?php echo $fair->arranger_cnr; ?></td>
					</tr>
					<?php endif; ?>
					<tr>
						<td colspan="3">
							<span class="td_button"><a href="fair/categories/<?php echo $fair->id; ?>"><?php echo $th_categories ?></a></span>
							<span class="td_button"><a href="administrator/overview/<?php echo $fair->id; ?>"><?php echo $th_admins ?></a></span>
							<span class="td_button"><a href="exhibitor/exhibitors/<?php echo $fair->id; ?>"><?php echo $th_exhibitors ?></a></span>
							<span class="td_button"><a href="fair/edit/<?php echo $fair->id; ?>"><?php echo $th_settings ?></a></span>
							<span class="td_button"><a href="fair/maps/<?php echo $fair->id; ?>"><?php echo $th_maps ?></a></span>
							<span class="td_button"><a href="fair/event_mail/<?php echo $fair->id; ?>"><?php echo $th_mailSettings; ?></a></span>
							<?php if(/*$fair->approved != 2 && */userLevel() == 4) : ?>
							<span class="td_button"><a	href="fair/delete/<?php echo $fair->id; ?>"><?php echo $th_delete ?></a></span>
							<?php endif; ?>
							<span class="td_button floatright<?php if ($fair->approved == 2) echo ' td_button_disabled'; ?>"><a href="<?php echo ($fair->approved == 2 ? '#' : 'fair/makeclone/' . $fair->id); ?>" class="fair-clone-confirm"><?php echo $th_clone ?></a></span>
						</td>
					</tr>
				</table>
			</td>
			
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<script>
	$(document).ready(function() {
		$('.IsDisabled').click(function(e){
			e.preventDefault();
		});

		$('.td_button_disabled .fair-clone-confirm').click(function(e) {
			e.preventDefault();
			$('#save_confirm').show().children('p').eq(0).css('font-size', '0.9em').text('<?php echo $dialog_clone_disabled; ?>');
			$('#save_confirm input').click(function() {
				$('#save_confirm').hide();
			});
		});

		$('.td_button:not(.td_button_disabled) .fair-clone-confirm').click(function(e) {
			var insert_ref = $('#confirmBox .dialog-buttons').eq(0);

			confirmBox(e, '<?php echo $dialog_clone_question; ?>', $(this).attr('href'), 'YES_NO');

			if ($('#confirmBox').has('#clone_info_link').length === 0) {
				insert_ref.before('<a href="#" id="clone_info_link"><?php echo $dialog_clone_info_link; ?></a>');

				$('#clone_info_link').click(function(e) {
					e.preventDefault();
					$(this).remove();
					insert_ref.before('<p class="dialog-text"><?php echo $dialog_clone_info; ?></p>');
				});
			}
		});

<?php if (isset($msg_cloning_complete)): ?>
		$('#save_confirm').show().children('p').eq(0).text('<?php echo $msg_cloning_complete; ?>');
		$('#save_confirm input').click(function() {
			$('#save_confirm').hide();
		});
<?php endif; ?>
	});
</script>
