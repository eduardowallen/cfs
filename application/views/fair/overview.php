<style>
.std_table tbody {
	border-bottom: none;
	border-right: none;
}
.std_table tbody tr:nth-child(2n+1) {
	background-color: #EFEFEF;
}
.std_table tbody tr:nth-child(2n) {
	background-color: #FDFDFD;
}
.std_table tr {
	border-right: none;
	border-left: none;
}

.mediumbutton, .blackbutton{
	font-size: 1em;
}
::-webkit-scrollbar {
	width:15px;
}
td {
	text-align: left;
}
</style>
<script>
function lockEvent(id, name) {
	$.confirm({
	  title: '<?php echo $lock_event_title; ?>',
	  content: '<?php echo $lock_event_content; ?> <br>' + name + '? <br>' + '<?php echo $lock_event_explain; ?>',
		escapeKey: true,
	  confirm: function(){
	  	window.location = 'fair/lock/' + id
	  },
	  cancel: function(){}
	});
}
</script>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>start/home'"><?php echo uh($translator->{'Go back'}); ?></button>
<br />
<script type="text/javascript" src="js/tablesearch.js<?php echo $unique?>"></script>
<h1><?php echo $headline; ?></h1>

<p><a class="button add" href="fair/edit/new"><?php echo $create_link; ?></a></p>

<table class="std_table of-tables" style="min-width:100%;">
	<tbody>
		<?php foreach ($fairs as $fair): ?>
		<tr class="container">
			<td class="container">
				<table style="min-width:100%; border: 1px solid rgba(155, 155, 155, 0.52);">
					<tr>
						<td><?php echo $th_fair ?>: <a href="page/loggedin/setFair/<?php echo $fair->id; ?>"><?php echo $fair->name; ?></a></td>
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
						<td><?php echo $th_total ?>: <?php echo $fair->total_cnt; ?></td>
					</tr>
					<tr>
						<td><?php echo $th_maps ?>: <a href="fair/maps/<?php echo $fair->id; ?>"><?php echo $fair->maps_cnt; ?></a></td>
						<td><?php echo $th_created ?>: <?php echo date('d-m-Y H:i:s', $fair->creation_time); ?></td>
						<td><?php echo $th_available ?>: <?php echo $fair->total_cnt - $fair->booked_cnt - $fair->reserved_cnt ?></td>
					</tr>
					<tr>
						<td><?php echo $th_page_views ?>: <?php echo $fair->page_views ?></td>
						<td><?php echo $th_event_start ?>: <?php echo date('d-m-Y H:i:s', $fair->event_start); ?></td>
						<td><?php echo $th_booked ?>: <?php echo $fair->booked_cnt; ?></td>
					</tr>
					<tr>
						<td>
						<?php if (userLevel() > 3): ?>
							<?php echo $th_arranger_name ?>: <a href="arranger/info/<?php echo $fair->created_by; ?>"><?php echo $fair->arranger_name; ?></a></td>
						<?php endif; ?>
						</td>
						<td><?php echo $th_event_stop ?>: <?php echo date('d-m-Y H:i:s', $fair->event_stop); ?></td>
						<td><?php echo $th_reserved ?>: <?php echo $fair->reserved_cnt; ?></td>
					</tr>
					<tr>
						<td colspan="3">
							<a class="td_button greenbutton mediumbutton" href="fair/maps/<?php echo $fair->id; ?>"><?php echo $th_maps ?></a>
							<a class="td_button greenbutton mediumbutton" href="fair/categories/<?php echo $fair->id; ?>"><?php echo $th_categories ?></a>
							<a class="td_button greenbutton mediumbutton" href="fair/extraOptions/<?php echo $fair->id; ?>"><?php echo $th_extraOptions ?></a>
							<a class="td_button greenbutton mediumbutton" href="fair/articles/<?php echo $fair->id; ?>"><?php echo $th_articles ?></a>
							<a class="td_button greenbutton mediumbutton" href="administrator/overview/<?php echo $fair->id; ?>"><?php echo $th_admins ?></a>
							<a class="td_button greenbutton mediumbutton" href="exhibitor/exhibitors/<?php echo $fair->id; ?>"><?php echo $th_exhibitors ?></a>
							<a class="td_button greenbutton mediumbutton" href="fair/edit/<?php echo $fair->id; ?>"><?php echo $th_settings ?></a>
							<a class="td_button greenbutton mediumbutton" href="fair/event_mail/<?php echo $fair->id; ?>"><?php echo $th_mailSettings; ?></a>
							<a class="td_button greenbutton mediumbutton" href="fair/rules/<?php echo $fair->id; ?>"><?php echo $th_rules ?></a>
							<?php
							$modules = json_decode($fair->modules);
							if (isset($modules->invoiceFunction)):
								if (is_array($modules->invoiceFunction) && in_array("1", $modules->invoiceFunction)) { ?>
								<a class="td_button greenbutton mediumbutton" href="fair/invoiceSettings/<?php echo $fair->id; ?>"><?php echo $th_invoiceSettings ?></a>
								<?php } 
							endif;
							/*
							if (isset($modules->raindanceFunction)):
								if (is_array($modules->raindanceFunction) && in_array("1", $modules->raindanceFunction)) { ?>
								<!--<a class="td_button greenbutton mediumbutton" href="fair/RDsettings/<?php echo $fair->id; ?>"><?php echo $th_RDsettings ?></a>-->
								<?php } ?>
							<?php endif; */?>
							<?php if (userLevel() == 4) : ?>
							<a class="td_button greenbutton mediumbutton" href="fair/modules/<?php echo $fair->id; ?>"><?php echo $th_modules ?></a>
							<a class="td_button redbutton mediumbutton" href="fair/delete/<?php echo $fair->id; ?>"><?php echo $th_delete ?></a>
							<?php endif; ?>
							<a href="<?php echo ($fair->approved == 2 ? '#' : 'fair/makeclone/' . $fair->id); ?>" style="margin-top:0.8em; float:right;" class="fair-clone-confirm blackbutton mediumbutton td_button"><?php echo $th_clone ?></a>
							<?php if ($fair->approved != 2) { ?>
								<button class="fair-clone-confirm redbutton mediumbutton td_button" onclick="lockEvent('<?php echo $fair->id; ?>', '<?php echo $fair->windowtitle; ?>')"><?php echo $th_lock ?></button>
							<?php } ?>
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
