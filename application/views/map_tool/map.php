<?php if ($notfound) { ?>
	<div id="wrong_url">
		<img src="images/images/wrong_url.png" style="padding-right:0.833em;" />
		<br />
		<p class="wrongurltext">
		<?php echo($translator->{'Fair not found'}); ?>
		</p></div>
<?php die();
	//die($translator->{'Fair not found'});
}


function makeUserOptions1($sel=0, $fair) {
	$users = User::getExhibitorsForFair($fair->get('id'));

	$ret = '';
	foreach ($users as $user) {
		$chk = ($sel == $user->get('id')) ? ' selected="selected"' : '';
		$ret.= '<option value="'.$user->get('id').'"'.$chk.'>'.$user->get('company').'</option>';
	}
	return $ret;
}

function makeUserOptions2($sel=0, $fair) {
	$users = User::getExhibitorsForFair($fair->get('id'));

	$ret = '';
	foreach ($users as $user) {
		$chk = ($sel == $user->get('id')) ? ' selected="selected"' : '';
		$ret.= '<li onclick="chooseThis(this)" value="'.$user->get('id').'"'.$chk.'>'.$user->get('company').'</li>';
	}
	return $ret;
}

function makeUserOptions3($sel=0, $fair) {
	$users = User::getExhibitorsForFair($fair->get('id'));

	$ret = '';
	foreach ($users as $user) {
		$chk = ($sel == $user->get('id')) ? ' selected="selected"' : '';
		$ret.= '<li onclick="chooseThisBook(this)" value="'.$user->get('id').'"'.$chk.'>'.$user->get('company').'</li>';
	}
	return $ret;
}

?>
<script type="text/javascript">
	$body = $("body");
/*
	$(document).on({
	    ajaxStart: function() { $body.addClass("loading");    },
	     ajaxStop: function() { $body.removeClass("loading"); }
	});
	*/
</script>
<?php 

	$visible = null;
	$islocked = '';
	if($fair->get('hidden') == 0) {
		$visible = true;
	} else {
		$visible = false;
	}

	if (!$visible && !$hasRights && !$fair->isLocked()) { ?>
		<script type="text/javascript">
			$().ready(function(){
				<?php if ($event_locked) { ?>
					console.log('LOCKED');
				<?php } else { ?>
					console.log('NOT locked');
				<?php } ?>
		<?php	if (userLevel() > 1) { ?>
					$('#alertBox2').show();
					$('#overlay').show();
					var url2 = "start/home";
					$('#alertBox2 input').click(function() {
						$(location).attr('href', url2);
					});
		<?php	}
				if (userLevel() <= 1) { ?>
					var fakevent = {preventDefault: function() {}};
					function alertBox(evt, message) {
						evt.preventDefault();
						$('#overlay').show();
						$('#alertBox').show();
						$('#alertbox_event_msg').html(message);
					}
					function confirmedRegisterEvent() {
						confirmBox(fakevent, '<?php echo ujs($translator->{'You have already requested a stand space on this event. Do you want to make another one?'}); ?>', maptool.applyForFair, 'YES_NO', 'exhibitor/myBookings');
					}
<?php				if ($fair->get('allow_registrations') == 1 && userLevel() == 1) { ?>
						console.log('userlevel is 1 and allow reg is 1');
	<?php
						if ($has_prev_registrations) {
							if ($fair->get('hidden_info') != '') { ?>
							confirmBox(fakevent, <?php echo json_encode($fair->get('hidden_info')); ?>, confirmedRegisterEvent, 'OK_CANCEL');
					<?php } else { ?>
							confirmBox(fakevent, '<?php echo ujs($translator->{'This event is hidden! If you want to register for this event, press OK'}); ?>', maptool.applyForFair, 'OK_CANCEL');
					<?php } 
						} else {
							if ($fair->get('hidden_info') != '') { ?>
								confirmBox(fakevent, <?php echo json_encode($fair->get('hidden_info')); ?>, maptool.applyForFair, 'OK_CANCEL');
					<?php } else { ?>
								confirmBox(fakevent, '<?php echo ujs($translator->{'This event is hidden! If you want to register for this event, press OK'}); ?>', maptool.applyForFair, 'OK_CANCEL');
					<?php }
						}
					} else if ($fair->get('allow_registrations') == 0 && userLevel() == 1) { ?>
						$('#alertBox').show();
				<?php	if ($fair->get('hidden_info') != '') { ?>
						alertBox(fakevent, <?php echo json_encode($fair->get('hidden_info')); ?>);
				<?php } else { ?>
						alertBox(fakevent, <?php echo json_encode($translator->{'This fair is hidden and is not accepting applications right now.'}); ?>);
				<?php } ?>
						$('#overlay').show();
						var url = "exhibitor/myBookings";
						console.log('userlevel is 1 and allow reg is 0');
						$('#alertBox input').click(function() {
							$('#alertBox').hide();

							$(location).attr('href',url);
						});

			<?php	}
				}
				if ($fair->get('allow_registrations') == 1 && userLevel() == 0) { ?>
					console.log('userlevel is 0 and allow reg is 1');
					$('#alertBox input').click(function() {
						$('#alertBox').hide();
						$('#nouser_dialogue').show();
						$('#nouser_dialogue').css({'margin-top': 0});
					});
			<?php if ($fair->get('hidden_info') != '') { ?>
						console.log('hidden info is set');
						alertBox(fakevent, <?php echo json_encode($fair->get('hidden_info')); ?> + '<br>' + <?php echo json_encode($translator->{'Log in to apply for our event.'}); ?>);
			<?php	} else { ?>
						alertBox(fakevent, <?php echo json_encode($translator->{'If you want to register to this event you will need to login. If you do not yet have an account, click on "NEW USER" after clicking "OK".'}); ?>);
						console.log('hidden info is not set');
		<?php 	}
				} else if ($fair->get('allow_registrations') == 0 && userLevel() == 0) {
					if ($fair->get('hidden_info') != '') { ?>
						alertBox(fakevent, <?php echo json_encode($fair->get('hidden_info')); ?>);
						console.log('hidden info is set');
			<?php	} else { ?>
						alertBox(fakevent, <?php echo json_encode($translator->{'This fair is hidden and is not accepting applications right now.'}); ?>);
						console.log('hidden info is not set');
		<?php		} ?>
					console.log('userlevel is 0 and allow reg is 0');
					$('#alertBox input').click(function() {
						$('#alertBox').hide();
						$('#nouser_dialogue').show();
						$('#nouser_dialogue').css({'margin-top': 0});
					});
	<?php 	}	?>
			});
			</script>
	<?php	} else if (!$visible && !$hasRights && $fair->isLocked()) { ?>
		<script type="text/javascript">
				var fakevent = {preventDefault: function() {}};
				function alertBox(evt, message) {
					evt.preventDefault();
					$('#overlay').show();
					$('#alertBox p').html('');
					$('#alertbox_event_msg').css('margin-bottom', '0px');
					$('#alertbox_event_msg').html(message).parent().show();
				}
				alertBox(fakevent, <?php echo json_encode($translator->{'This event is both locked and hidden and cannot be interacted with'}); ?>);
				var url2 = "start/home";
				$('#alertBox input').click(function() {
					$(location).attr('href', url2);
				});
		</script>

	<?php } 

	// Om användaren har administrativa rättigheter eller om eventet är synligt 
	if ((!$visible && userLevel() > 1 && $hasRights) || $visible) {
		// Om användaren har nivå 1 men ej är ansluten till eventet
		if (userLevel() == 1 && !userIsConnectedTo($fair->get('id'))) {
			// Ajax-kod för att ansluta en användare till eventet ?>
			<script type="text/javascript">
				$().ready(function(){
					$.ajax({
					url: 'ajax/maptool.php',
					type: 'POST',
					data: 'connectToFair=1&fairId=' + <?php echo $fair->get('id')?>,
					success: function(response) {
						res = JSON.parse(response);
						window.location = '<?php echo $fair->get('url')?>';
					}
				});
			});
			</script>
		<?php } ?>

		<?php if ($hasRights && !$fair->isLocked()) { ?>
			<div id="maptoolbox">
				<h3 id="maptoolbox_header">
					<?php echo uh($translator->{'Map tools'}); ?>
					<img src="images/icons/grid_icon.png" id="grid_icon"/>
					<a href="#" id="maptoolbox_minimize" title="<?php echo uh($translator->{'Minimize'}); ?>"></a>
				</h3>

				<p id="zoombar">
					<img src="images/zoom_slider.png" alt=""/>
					<a href="javascript:void(0)" id="in"></a>
					<a href="javascript:void(0)" id="out"></a>
				</p>
				<div id="maptoolbox_controls">
					<label style="display: inline-block;">
						<input type="button" class="greenbutton" value="<?php echo uh($translator->{"Grid activated"}); ?>" id="maptool_grid_activated2" />
						<!--<img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'If this is unchecked, the grid will not be generated.'}); ?>" />-->
					</label>
						<input type="checkbox" id="maptool_grid_activated" style="display:none"/>
					<label style="display: inline-block;">
						<input type="button" class="greenbutton" value="<?php echo uh($translator->{'Change color'}); ?>" id="maptool_grid_white2" />
					</label>
					<input type="checkbox" id="maptool_grid_white" style="display:none"/>
					<h4 style="margin: 0px"><?php echo uh($translator->{'X-axis'}); ?></h4>
					<p><?php echo uh($translator->{'Show'}); ?></p>
					<input type="checkbox" id="maptool_grid_visible_x"></input>
					<label class="squaredFour" for="maptool_grid_visible_x"></label>
					<p><?php echo uh($translator->{'Snap stand spaces'}); ?></p>
					<input type="checkbox" id="maptool_grid_snap_markers_x" />
					<label class="squaredFour" for="maptool_grid_snap_markers_x"></label>
					<h4 style="margin: 1.66em 0px 0px 0px"><?php echo uh($translator->{'Y-axis'}); ?></h4>
						<p><?php echo uh($translator->{'Show'}); ?></p>
						<input type="checkbox" id="maptool_grid_visible_y" />
						<label class="squaredFour" for="maptool_grid_visible_y"></label>
						<p><?php echo uh($translator->{'Snap stand spaces'}); ?></p>
						<input type="checkbox" id="maptool_grid_snap_markers_y" />
						<label class="squaredFour" for="maptool_grid_snap_markers_y"></label>
					<label style="padding-top: 1.66em; padding-bottom: 0.833em;">
						<?php echo uh($translator->{'Opacity'}); ?>:
						<input type="range" id="maptool_grid_opacity" min="1" max="100" value="100" />
						<input type="text" id="maptool_grid_opacity_num" value="100" class="spinner" />
					</label>				
					<label style="padding-bottom: 1.66em;">
						<?php echo uh($translator->{'Move grid'}); ?>
						<input type="checkbox" id="maptool_grid_is_moving" />
						<label class="squaredFour" for="maptool_grid_is_moving"></label>
					</label>
					<span class="maptoolbox-label-row">
						<?php echo uh($translator->{'Coordinates'}); ?>
						<label style="margin: 0px; float:right; padding:0px 0.66em 0px 0px;"> 
							Y &nbsp
							<input type="text" id="maptool_grid_coord_y" value="0" class="spinner" />
						</label>					
						<label style="margin: 0px; float:right; padding:0px 0.833em 0px 0px;"> 
							X &nbsp
							<input type="text" id="maptool_grid_coord_x" value="0" class="spinner" />
						</label>

					</span>
					<br/>
						<label>
							<?php echo uh($translator->{'X-axis gap'}); ?>
							<input type="text" id="maptool_grid_height" value="20" class="spinner" />
						</label>
						<label>
							<?php echo uh($translator->{'Y-axis gap'}); ?>
							<input type="text" id="maptool_grid_width" value="20" class="spinner" />
						</label>
					<!--<label>
						<span class="maptoolbox-label"><?php echo uh($translator->{'W x H per cell'}); ?>:</span>
						<input type="text" id="maptool_grid_width_rat" value="20" class="spinner" />
						x
						<input type="text" id="maptool_grid_height_rat" value="20" class="spinner" />
					</label>-->
					<br/>
					<label style="display: inline-block;">
						<input type="button" class="greenbutton" id="maptool_grid_save" value="<?php echo uh($translator->{'Save'}); ?>" />
					</label>
					<label style="display: inline-block;">
						<input type="button" class="greenbutton" id="maptool_grid_reset" value="<?php echo uh($translator->{'Reset grid'}); ?>" />
					</label>
				</div>
			</div>

		<style id="maptool_grid_style">
		</style>

		<div id="edit_position_dialogue" class="dialogue popup">
			<img src="images/icons/close_dialogue.png" alt="" style="margin-top:-3.7em" class="closeDialogue"/>
			<h3 class="standSpaceName"><?php echo uh($translator->{'New/Edit stand space'}); ?></h3>

			<label for="position_name_input"><?php echo uh($translator->{'Name'}); ?> *</label>
			<input type="text" class="dialogueInput"  name="position_name_input" id="position_name_input"/>

			<label for="position_area_input"><?php echo uh($translator->{'Area'}); ?> </label>
			<input type="text" class="dialogueInput"  name="position_area_input" id="position_area_input"/>

			<label for="position_price_input"><?php echo uh($translator->{'Price (without VAT)'}); ?> </label>
			<input type="number" class="form-control bfh-number" name="position_price_input" min="0" value="0" step="0.01" id="position_price_input" title="<?php echo uh($translator->{"Comma as delimiter is not accepted. Please use dot instead (eg: 234.53 = 234,53)."}); ?>" style="width:10em; text-align:left;" />

			<label for="position_info_input"><?php echo uh($translator->{'Information'}); ?></label>
			<textarea name="position_info_input" id="position_info_input" placeholder="<?php echo uh($translator->{'Enter information about the stand space that would be interesting for the exhibitor to know, for example: This stand space is in the center of the IT-area and very well positioned for demonstration of your products.'}); ?>"></textarea>

			<input type="hidden" name="position_id_input" id="position_id_input" value=""/>

			<p><input type="button" class="greenbutton mediumbutton" id="post_position" value="<?php echo uh($translator->{'Save and close'}); ?>"/></p>

		</div>
		<div id="preliminary_bookings_dialogue" class="dialogue popup">
			<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -3.7em; margin-right: -0.5em;" />
			<h3 class="standSpaceName"><?php echo uh($translator->{"Preliminary bookings"}); ?></h3>
			<table class="std_table" id="prelBookingsList">
				<thead>
					<tr>
						<th><?php echo uh($translator->{"Stand space"}); ?></th>
						<th><?php echo uh($translator->{"Area"}); ?></th>
						<th><?php echo uh($translator->{'Booked by'}); ?></th>
						<th><?php echo uh($translator->{"Trade"}); ?></th>
						<th><?php echo uh($translator->{'Time of booking'}); ?></th>
						<th><?php echo uh($translator->{"Message to organizer in list"}); ?></th>
						<th><?php echo uh($translator->{"Deny"}); ?></th>
						<th><?php echo uh($translator->{"Approve (if already payed)"}); ?></th>
						<th><?php echo uh($translator->{'Reserve stand space (if not yet payed)'}); ?></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>

		<style>
		#reserve_position_form fieldset {
			padding-top: 0;
			padding-bottom: 2.5em;
			border-top: 5em solid #3258CD;
		}

		.review_prel_dialogue #column {
			padding-top:0;
		}

		</style>
		<!-- multistep form -->
		<form id="reserve_position_form" class="form booking_form popup">
		<!-- progressbar -->
		<ul class="progressbar progress-3">
			<li class="active"><?php echo uh($translator->{'Categories and assortment'}); ?></li>
			<li><?php echo uh($translator->{'Articles and extra options'}); ?></li>
			<li><?php echo uh($translator->{'Confirm booking'}); ?>
		</ul>
			<fieldset>
				<!-- FIELDSET NUMBER ONE -->
				<h3 class="standSpaceName"></h3>
				<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue popupCloseDialogue"/>
				<br />
			<div class="column">
				<div class="ssinfo"></div>
			</div>
			<div class="column">
				<!-- Search field to find users -->
				<label for="search_user_input"><?php echo uh($translator->{'Search exhibitor'}); ?></label>
				<input type="text" style="width:25em;" name="search_user_input" id="search_user_input" title="<?php echo uh($translator->{'While still having focus on the search field: press enter to insert the Exhibitors official commodity.'}); ?>"/>
				<!-- Drop-down to choose users from -->
				<label for="reserve_user_input"><?php echo uh($translator->{'Select Exhibitor'}); ?></label>
				<select  style="width:25em;" name="reserve_user_input" id="reserve_user_input">
					<?php echo makeUserOptions1(0, $fair); ?>
				</select>
				<label class="label_medium" for="reserve_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
				<textarea name="reserve_commodity_input" maxlength="200" class="commodity_big" id="reserve_commodity_input"></textarea>
			</div>

			    		<!-- Div with table to choose category from -->
				<label class="table_header" for="reserve_category_scrollbox"><?php echo uh($translator->{'Categories'}); ?> *</label>
				<div class="scrolltable-wrap" id="reserve_category_scrollbox_div">
					<table class="std_table std_booking_table" id="reserve_category_scrollbox">
						<thead>
							<tr>
								<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
								<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($fair->get('categories') as $cat){ ?>
							<tr>
								<td class="left"><?php echo $cat->get('name') ?></td>
								<td class="cfscheckbox">
									<input type="checkbox" id="<?php echo $cat->get('id') ?>" value="<?php echo $cat->get('id') ?>" />
									<label class="squaredFour" for="<?php echo $cat->get('id') ?>" />
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>	
				<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
				<input type="button" id="reserve_first_step" class="greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />
				<input type="button" name="next" class="reserve_review lastStep greenbutton mediumbutton nomargin floatright" value="<?php echo uh($translator->{'Go to summary'}); ?>" />

			</fieldset>
			<fieldset>
				<!-- FIELDSET NUMBER TWO -->
				<h3 class="standSpaceName"></h3>
				<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue popupCloseDialogue"/>
					<!--  Extra options -->
			<?php if ($fair->get('extraOptions')) { ?>
				<label class="table_header" for="reserve_option_scrollbox"><?php echo uh($translator->{'Extra options'}); ?></label>
				<div class="scrolltable-wrap">
					<table class="std_table std_booking_table" id="reserve_option_scrollbox">
						<thead>
							<tr>
								<th>ID</th>
								<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
								<th><?php echo uh($translator->{'Price'}); ?></th>
								<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($fair->get('extraOptions') as $extraOption) { ?>
								<tr>
									<td><?php echo $extraOption->get('custom_id') ?></td>
									<?php if ($extraOption->get('required') == 1) { ?>
										<td class="left"><?php echo $extraOption->get('text') ?>*</td>
									<?php } else { ?>
										<td class="left"><?php echo $extraOption->get('text') ?></td>
									<?php } ?>
									<td><?php echo $extraOption->get('price') ?></td>
									<td style="display:none;">
									<?php if ($extraOption->get('vat') == 25) { ?>
										<input hidden value="25" />
									<?php } else if ($extraOption->get('vat') == 18) { ?>
										<input hidden value="18" />
									<?php } else if ($extraOption->get('vat') == 12) { ?>
										<input hidden value="12" />
									<?php } else { ?>
										<input hidden value="0" />
									<?php } ?>
									</td>							
									<td class="cfscheckbox">
										<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" value="<?php echo $extraOption->get('id') ?>"/>
										<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } else { ?>
			<br/>
			<h2><?php echo uh($translator->{'Extra options'}); ?></h2>
			<?php echo uh($translator->{"There are no options to display"}); ?>
			<br />
			<?php } ?>
			<input type="hidden" name="reserve_message_input" class="msg_to_organizer" id="reserve_message_input">
					<!--  Articles  -->
			<?php if ($fair->get('articles') && $available_articles >= 1 || $hasRights) { ?>
				<label class="table_header" for="reserve_article_input"><?php echo uh($translator->{"Articles"}); ?></label>
				<div class="scrolltable-wrap">
					<table class="std_table" id="reserve_article_scrollbox">
						<thead>
							<tr>
								<th>ID</th>
								<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
								<th><?php echo uh($translator->{'Price'}); ?></th>
								<th style="text-indent:-3.9166em;" data-sorter="false"><?php echo uh($translator->{'Amount'}); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($fair->get('articles') as $article) { ?>
								<?php if ($article->get('required') != 1 || $hasRights) { ?>
									<tr>
										<td><?php echo $article->get('custom_id') ?></td>
										<td class="left"><?php echo $article->get('text') ?></td>
										<td><?php echo $article->get('price') ?></td>
										<td style="display:none;">
											<?php if ($article->get('vat') == 25) { ?>
												<input hidden value="25" />
											<?php } else if ($article->get('vat') == 18) { ?>
												<input hidden value="18" />
											<?php } else if ($article->get('vat') == 12) { ?>
												<input hidden value="12" />
											<?php } else { ?>
												<input hidden value="0" />
											<?php } ?>
										</td>						
										<td class="td-number-span"><input type="text" class="form-control bfh-number" min="0" value="0" id="<?php echo $article->get('id') ?>" /></td>
									</tr>
								<?php } ?>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } else { ?>
			<br/>
			<h2><?php echo uh($translator->{'Articles'}); ?></h2>
			<?php echo uh($translator->{"There are no articles to display"}); ?>
			<br />
			<br />
			<?php } ?>
				<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
				<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
				<input type="button" name="next" class="reserve_review next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />
				
			</fieldset>

			<fieldset>
				<!-- FIELDSET NUMBER THREE -->
				<h3 class="standSpaceName"></h3>
				<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue popupCloseDialogue"/>
				<br />
					<div id="review_reserve_dialogue">
						<br />
						<label for="review_user" style="font-size:1.7em; display:inline;"><?php echo uh($translator->{'Exhibitor:'}); ?> </label>
						<span style="font-size:1.7em;" id="review_user"></span>
						<br />			
						<div class="column review_column1">
							<label for="review_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
							<p name="commodity" id="review_commodity_input"></p>
						</div>
						<div class="column review_column2">
							<label for="review_category_list"><?php echo uh($translator->{'Categories'}); ?></label>
							<p id="review_category_list" style="width:100%; float:left;"></p>	
						</div>
						<div class="column review_column1">
							<label for="review_message" id="review_message_label"><?php echo uh($translator->{'Exhibitor message to Organizer'}); ?></label>
							<p name="arranger_message" id="review_message"></p>	
						</div>
						<div class="column review_column2">
							<!-- Kalenderfunktion för att välja platsens slutgiltiga reservationsdatum -->
							<label for="reserve_expires_input"><?php echo uh($translator->{'Reserved until'}); ?> (DD-MM-YYYY HH:MM)</label>
							<input type="text" class="dialogueInput datetime datepicker" name="expires" id="reserve_expires_input" title="<?php echo uh($translator->{'The date that you set here is the date when the reservation expires and the stand space is reopened (green) for preliminary bookings.'}); ?>" value=""/>
						</div>
						<div class="no-search" id="review_list_div" style="padding:1.66em 0em;">
							<div id="review_div">
								<table id="review_list" class="no-search" style="width:100%;">
								</table>
							</div>
							<table id="review_list2" class="no-search" style="width:100%;">
							</table>
						</div>
					</div>
				<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
				<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
				<input type="submit" name="submit" class="submit reserve_post greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Submit booking'}); ?>" />
				
			</fieldset>
		</form>

		<style>
		#book_position_form fieldset {
			padding-top: 0;
			padding-bottom: 2.5em;
			border-top: solid #d21d1d 5em;
		}
		    
		input[type="checkbox"] {
			margin: 0px 0px 0px -0.66em;
		}
		</style>
		<!-- multistep form -->
		<form id="book_position_form" class="form booking_form popup">
		<!-- progressbar -->
		<ul class="progressbar progress-3">
			<li class="active"><?php echo uh($translator->{'Categories and assortment'}); ?></li>
			<li><?php echo uh($translator->{'Articles and extra options'}); ?></li>
			<li><?php echo uh($translator->{'Confirm booking'}); ?>
		</ul>
			<fieldset>
				<!-- FIELDSET NUMBER ONE -->
				<h3 class="standSpaceName"></h3>
				<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue popupCloseDialogue popupCloseDialogue"/>
				<br />
			<div class="column" style="padding-right:2em;">
				<div class="ssinfo"></div>	
			</div>		
			<div class="column">
				<label for="search_user_input"><?php echo uh($translator->{'Search exhibitor'}); ?></label>
				<input type="text" style="width:25em;" name="search_user_input" id="search_user_input" title="<?php echo uh($translator->{'While still having focus on the search field: press enter to insert the Exhibitors official commodity.'}); ?>"/>
			  <!-- Drop-downlista för att välja användare att boka in -->	
				<label for="book_user_input"><?php echo uh($translator->{'Select Exhibitor'}); ?></label>
				<select  style="width:25em;" name="book_user_input" id="book_user_input">
					<?php echo makeUserOptions1(0, $fair); ?>
				</select>
				<label class="label_medium" for="book_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
				<textarea name="book_commodity_input" maxlength="200" class="commodity_big" id="book_commodity_input"></textarea>
			</div>

		    		<!-- Div för att välja kategori -->
			<label class="table_header" for="book_category_scrollbox"><?php echo uh($translator->{'Categories'}); ?> *</label>
			<div class="scrolltable-wrap" id="book_category_scrollbox_div">
			<table class="std_table std_booking_table" id="book_category_scrollbox">
				<thead>
					<tr>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('categories') as $cat){ ?>
					<tr>
						<td class="left"><?php echo $cat->get('name') ?></td>
						<td class="cfscheckbox">
							<input type="checkbox" id="<?php echo $cat->get('id') ?>" value="<?php echo $cat->get('id') ?>" />
							<label class="squaredFour" for="<?php echo $cat->get('id') ?>" />
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
			</div>	
			<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
			<input type="button" class="book_first_step greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />
			<input type="button" name="next" class="book_review lastStep greenbutton mediumbutton nomargin floatright" value="<?php echo uh($translator->{'Go to summary'}); ?>" />

			</fieldset>
			<fieldset>
				<!-- FIELDSET NUMBER TWO -->
				<h3 class="standSpaceName"></h3>
				<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue popupCloseDialogue"/>
					<!--  Extra tillval -->
			<?php if ($fair->get('extraOptions')) { ?>
				<label class="table_header" for="book_option_scrollbox"><?php echo uh($translator->{'Extra options'}); ?></label>
				<div class="scrolltable-wrap">
					<table class="std_table std_booking_table" id="book_option_scrollbox">
						<thead>
							<tr>
								<th>ID</th>
								<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
								<th><?php echo uh($translator->{'Price'}); ?></th>
								<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($fair->get('extraOptions') as $extraOption) { ?>
							<tr>
								<td><?php echo $extraOption->get('custom_id') ?></td>
									<?php if ($extraOption->get('required') == 1) { ?>
										<td class="left"><?php echo $extraOption->get('text') ?>*</td>
									<?php } else { ?>
										<td class="left"><?php echo $extraOption->get('text') ?></td>
									<?php } ?>
								<td><?php echo $extraOption->get('price') ?></td>
									<td style="display:none;">
									<?php if ($extraOption->get('vat') == 25) { ?>
										<input hidden value="25" />
									<?php } else if ($extraOption->get('vat') == 18) { ?>
										<input hidden value="18" />
									<?php } else if ($extraOption->get('vat') == 12) { ?>
										<input hidden value="12" />
									<?php } else { ?>
										<input hidden value="0" />
									<?php } ?>
									</td>						
								<td class="cfscheckbox">
									<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" value="<?php echo $extraOption->get('id') ?>" />
									<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>		
				</div>
			<?php } else { ?>
			<br/>
			<h2><?php echo uh($translator->{'Extra options'}); ?></h2>
			<?php echo uh($translator->{"There are no options to display"}); ?>
			<br />
			<?php } ?>
			<input type="hidden" name="book_message_input" class="msg_to_organizer" id="book_message_input">
					<!--  Artiklar  -->
			<?php if ($fair->get('articles') && $available_articles >= 1 || $hasRights) { ?>
				<label class="table_header" for="book_article_input"><?php echo uh($translator->{"Articles"}); ?></label>
				<div class="scrolltable-wrap">
					<table class="std_table" id="book_article_scrollbox">
						<thead>
							<tr>
								<th>ID</th>
								<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
								<th><?php echo uh($translator->{'Price'}); ?></th>
								<th style="text-indent:-3.9166em;" data-sorter="false"><?php echo uh($translator->{'Amount'}); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($fair->get('articles') as $article) { ?>
								<?php if ($article->get('required') != 1 || $hasRights) { ?>
									<tr>
										<td><?php echo $article->get('custom_id') ?></td>
										<td class="left"><?php echo $article->get('text') ?></td>
										<td><?php echo $article->get('price') ?></td>
										<td style="display:none;">
											<?php if ($article->get('vat') == 25) { ?>
												<input hidden value="25" />
											<?php } else if ($article->get('vat') == 18) { ?>
												<input hidden value="18" />
											<?php } else if ($article->get('vat') == 12) { ?>
												<input hidden value="12" />
											<?php } else { ?>
												<input hidden value="0" />
											<?php } ?>
										</td>						
										<td class="td-number-span"><input type="text" class="form-control bfh-number" min="0" value="0" id="<?php echo $article->get('id') ?>" /></td>
									</tr>
								<?php } ?>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } else { ?>
			<br/>
			<h2><?php echo uh($translator->{'Articles'}); ?></h2>
			<?php echo uh($translator->{"There are no articles to display"}); ?>
			<br />
			<br />
			<?php } ?>
				<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
				<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
				<input type="button" name="next" class="book_review next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />
				
			</fieldset>
			<fieldset>
				<!-- FIELDSET NUMBER THREE -->
				<h3 class="standSpaceName"></h3>
				<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue popupCloseDialogue"/>
				<br />
				<div id="review_book_dialogue">
					<br />
					<label for="review_user" style="font-size:1.7em; display:inline;"><?php echo uh($translator->{'Exhibitor:'}); ?> </label>
					<span style="font-size:1.7em;" id="review_user"></span>
					<br />			
					<div class="column review_column1">
						<label for="review_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
						<p name="commodity" id="review_commodity_input"></p>
					</div>
					<div class="column review_column2">
						<label for="review_category_list"><?php echo uh($translator->{'Categories'}); ?></label>
						<p id="review_category_list" style="width:100%; float:left;"></p>	
					</div>
					<div class="column review_column1">
						<label for="review_message" id="review_message_label"><?php echo uh($translator->{'Exhibitor message to Organizer'}); ?></label>
						<p name="arranger_message" id="review_message"></p>	
					</div>

					<div class="no-search" id="review_list_div" style="padding:1.66em 0em;">
						<div id="review_div">
							<table id="review_list" class="no-search" style="width:100%;">
							</table>
						</div>
						<table id="review_list2" class="no-search" style="width:100%;">
						</table>
					</div>
				</div>
				<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
				<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
				<input type="submit" name="submit" class="submit book_post greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Submit booking'}); ?>" />
				
			</fieldset>
		</form>
		<div id="fair_registration_paste_type_dialogue" class="dialogue popup" style="border-top: 5em solid #47A547;">
			<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top:-3.8em;" />
			<h3 class="standSpaceName"><?php echo uh($translator->{'Paste registration'}); ?></h3>
			<br/>
			<p>
				<label>
					<?php echo uh($translator->{'Set type of booking:'}); ?>
					<select id="paste_fair_registration_type">
						<option value="0"><?php echo uh($translator->{'Booking'}); ?></option>
						<option value="1"><?php echo uh($translator->{'Reservation'}); ?></option>
					</select>
				</label>
			</p>

			<p>
				<input type="button" class="greenbutton mediumbutton" id="paste_fair_registration" value="<?php echo uh($translator->{'Continue'}); ?>" />
			</p>
		</div>
		<script type="text/javascript">
			var hasRights = true;
			<?php if (isset($_SESSION['copied_exhibitor'])) { ?>
					copiedExhibitor = "<?php echo $_SESSION['copied_exhibitor'] ?>";
			<?php } ?>

			<?php if (isset($copied_fair_registration)) { ?>
					copiedFairRegistration = <?php echo JsonResponse::encode($copied_fair_registration); ?>;
			<?php } ?>

			var confirmDialogue = "<?php echo $translator->{'Are you sure that you want to remove stand space'}; ?>";
			var deletion = "<?php echo $translator->{'Enter comment about deletion'}; ?>";

			function denyPrepPosition(link, position, status, clicked){
				if(confirm(confirmDialogue.replace('%s', position))){
					var message = prompt(deletion, "");
					denyPosition(link, message, position, status, clicked);
				}
			}
		</script>

	<?php } else { ?>
		<?php if (!$detect->isMobile()) {
			if ($fair->get('rules') || ($fair->get('contact_info')) ) { ?>
			<div id="map_div_info">
				<h3 id="map_div_info_header">
					<?php echo uh($translator->{'More info'}); ?>
				</h3>
				<?php if ($fair->get('rules')) { ?>
				<label>
					<input type="button" class="greenbutton <?php if (userLevel() > 0) { echo $_SESSION['user_fair']; } else { echo $_SESSION['outside_fair_url']; } ?> mapdivbtn rulesLink" value="<?php echo uh($translator->{'Rules'}); ?>" />
				</label>
				<?php } ?>
				<?php if ($fair->get('contact_info')) { ?>
				<label>
					<input type="button" class="greenbutton <?php if (userLevel() > 0) { echo $_SESSION['user_fair']; } else { echo $_SESSION['outside_fair_url']; } ?> mapdivbtn contactLink" value="<?php echo uh($translator->{'Contact'}); ?>" />
				</label>
				<?php } ?>
			</div>
			<?php }
		} ?>
		<div id="preliminaryConfirm" class="dialogue popup">
			<img src="images/icons/close_dialogue.png" style="margin-top:-3.7em" class="closeDialogue"/>
			<h2 class="standSpaceName" style="margin-top:-3.15em; text-align:center;"><?php echo $translator->{"Thank you for your preliminary booking"}; ?></h2>
			<p><?php echo $translator->{"A receipt of your booking has been sent to your inbox and it is now up to the Organizer to do the rest of the work. You can preliminary book more stand spaces if you want in the same manner."}; ?></p>
			<input type="button" class="greenbutton mediumbutton closeDialogue" style="margin-bottom:1em;" value="Ok" />
		</div>

		<style>
		#apply_position_form fieldset {
			padding-top: 0;
			padding-bottom: 2.5em;
			border-top: solid #48A547 5em;
		}
		</style>
		<!-- multistep form -->
		<form id="apply_position_form" class="form booking_form popup">
		<!-- progressbar -->
		<ul class="progressbar">
			<li class="active"><?php echo uh($translator->{'Categories'}); ?></li>
			<li><?php echo uh($translator->{'Articles and extra options'}); ?></li>
			<li><?php echo uh($translator->{'Message and Assortment'}); ?></li>
			<li><?php echo uh($translator->{'Confirm booking'}); ?>
		</ul>
			<fieldset>
				<!-- FIELDSET NUMBER ONE -->
				<h3 class="standSpaceName"></h3>
				<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue popupCloseDialogue"/>
				<br />
				<div class="ssinfo"></div>
			    		<!-- Div för att välja kategori -->
				<label class="table_header" for="apply_category_scrollbox"><?php echo uh($translator->{'Categories'}); ?> *</label>
				<div class="scrolltable-wrap" id="apply_category_scrollbox_div">
					<table class="std_table std_booking_table" id="apply_category_scrollbox">
						<thead>
							<tr>
								<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
								<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($fair->get('categories') as $cat){ ?>
							<tr>
								<td class="left"><?php echo $cat->get('name') ?></td>
								<td class="cfscheckbox">
									<input type="checkbox" id="<?php echo $cat->get('id') ?>" value="<?php echo $cat->get('id') ?>" />
									<label class="squaredFour" for="<?php echo $cat->get('id') ?>" />
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>	
				<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
				<input type="button" id="prel_first_step" class="greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />

			</fieldset>
			<fieldset>
				<!-- FIELDSET NUMBER TWO -->
				<h3 class="standSpaceName"></h3>
				<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue popupCloseDialogue"/>
					<!--  Extra tillval -->
			<?php if ($fair->get('extraOptions')) { ?>
				<label class="table_header" for="apply_option_scrollbox"><?php echo uh($translator->{'Extra options'}); ?></label>
				<div class="scrolltable-wrap">
					<table class="std_table std_booking_table" id="apply_option_scrollbox">
						<thead>
							<tr>
								<th>ID</th>
								<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
								<th><?php echo uh($translator->{'Price'}); ?></th>
								<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($fair->get('extraOptions') as $extraOption) { ?>
								<?php if ($extraOption->get('required') == 1) { ?>
								<tr>
									<td><?php echo $extraOption->get('custom_id') ?></td>
									<td class="left"><?php echo $extraOption->get('text') ?>*</td>
									<td><?php echo $extraOption->get('price') ?></td>
									<td style="display:none;">
									<?php if ($extraOption->get('vat') == 25) { ?>
										<input value="25" />
									<?php } else if ($extraOption->get('vat') == 18) { ?>
										<input value="18" />
									<?php } else if ($extraOption->get('vat') == 12) { ?>
										<input value="12" />
									<?php } else { ?>
										<input value="0" />
									<?php } ?>
									</td>
									<td class="cfscheckbox">
										<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" value="<?php echo $extraOption->get('id') ?>" checked="checked" />
										<input type="checkbox" disabled="disabled" checked>
										<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
									</td>
								</tr>
							<?php } else { ?>
								<tr>
									<td><?php echo $extraOption->get('custom_id') ?></td>
									<td class="left"><?php echo $extraOption->get('text') ?></td>
									<td><?php echo $extraOption->get('price') ?></td>
									<td style="display:none;">
									<?php if ($extraOption->get('vat') == 25) { ?>
										<input hidden value="25" />
									<?php } else if ($extraOption->get('vat') == 18) { ?>
										<input hidden value="18" />
									<?php } else if ($extraOption->get('vat') == 12) { ?>
										<input hidden value="12" />
									<?php } else { ?>
										<input hidden value="0" />
									<?php } ?>
									</td>
									<td class="cfscheckbox">
										<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" value="<?php echo $extraOption->get('id') ?>"/>
										<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
									</td>
								</tr>
								<?php } ?>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } else { ?>
			<br/>
			<h2><?php echo uh($translator->{'Extra options'}); ?></h2>
			<?php echo uh($translator->{"There are no options to display"}); ?>
			<br />
			<?php } ?>
					<!--  Artiklar  -->
			<?php if ($fair->get('articles') && $available_articles >= 1 || $hasRights) { ?>
				<label class="table_header" for="apply_article_scrollbox"><?php echo uh($translator->{"Articles"}); ?></label>
				<div class="scrolltable-wrap">
					<table class="std_table" id="apply_article_scrollbox">
						<thead>
							<tr>
								<th>ID</th>
								<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
								<th><?php echo uh($translator->{'Price'}); ?></th>
								<th style="text-indent:-3.9166em;" data-sorter="false"><?php echo uh($translator->{'Amount'}); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($fair->get('articles') as $article) { ?>
								<?php if ($article->get('required') != 1 || $hasRights) { ?>
									<tr>
										<td><?php echo $article->get('custom_id') ?></td>
										<td class="left"><?php echo $article->get('text') ?></td>
										<td><?php echo $article->get('price') ?></td>
										<td style="display:none;">
											<?php if ($article->get('vat') == 25) { ?>
												<input hidden value="25" />
											<?php } else if ($article->get('vat') == 18) { ?>
												<input hidden value="18" />
											<?php } else if ($article->get('vat') == 12) { ?>
												<input hidden value="12" />
											<?php } else { ?>
												<input hidden value="0" />
											<?php } ?>
										</td>
										<td class="td-number-span"><input type="text" class="form-control bfh-number" min="0" value="0" id="<?php echo $article->get('id') ?>" /></td>
									</tr>
								<?php } ?>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } else { ?>
			<br/>
			<h2><?php echo uh($translator->{'Articles'}); ?></h2>
			<?php echo uh($translator->{"There are no articles to display"}); ?>
			<br />
			<br />
			<?php } ?>
				<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
				<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
				<input type="button" name="next" class="next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />
				
			</fieldset>
			<fieldset>
				<!-- FIELDSET NUMBER THREE -->
				<h3 class="standSpaceName"></h3>
				<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue popupCloseDialogue"/>
				<br />
				<label class="label_medium" for="apply_commodity_input"><?php echo uh($translator->{'Commodity'}); ?> *</label>
					<textarea name="apply_commodity_input" maxlength="200" class="commodity_big" id="apply_commodity_input"></textarea>


				<label class="label_long" for="apply_message_input"><?php echo uh($translator->{'Message to organizer'}); ?></label>
				<textarea name="apply_message_input" class="msg_to_organizer" id="apply_message_input"></textarea>
				<br />
				<br />

				<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
				<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
				<input type="button" name="next" class="apply_review next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />	
			</fieldset>
			<fieldset>
				<!-- FIELDSET NUMBER FOUR -->
				<h3 class="standSpaceName"></h3>
				<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue popupCloseDialogue"/>
				<br />
				<div class="review_prel_dialogue">
					<div class="column review_column1">
						<label for="review_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
						<p name="commodity" id="review_commodity_input"></p>
					</div>
					<div class="column review_column2">
						<label for="review_category_list"><?php echo uh($translator->{'Categories'}); ?></label>
						<p id="review_category_list" style="width:100%; float:left;"></p>		
					</div>
					<label for="review_message" id="review_message_label"><?php echo uh($translator->{'Message to organizer'}); ?></label>
					<p name="arranger_message" id="review_message"></p>	
					<div class="no-search" id="review_list_div" style="padding:1.66em 0px;">
						<div id="review_div">
							<table id="review_list" class="no-search" style="width:100%;">
							</table>
						</div>
						<table id="review_list2" class="no-search" style="width:100%;">
						</table>
					</div>
				</div>

				<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
				<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
				<input type="submit" name="submit" id="apply_confirm" class="submit greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Submit booking'}); ?>" />
				
			</fieldset>
		</form>

		<style>
			#maptool_grid {display:none;}
		</style>
		<p id="zoombar">
			<img src="images/zoom_slider.png" alt=""/>
			<a href="javascript:void(0)" id="in"></a>
			<a href="javascript:void(0)" id="out"></a>
		</p>
		<script type="text/javascript">
		var hasRights = false;
		</script>
	<?php } ?>

	<div id="more_info_dialogue" class="dialogue popup">
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -0.5em;"/>
		<br />
		<div id="column" style="width:21.66em;">
			<?php if ($_COOKIE['language'] == "sv") { ?>
			<img id="ex_logo" style="text-align:center;" src="../images/images/no_logo_Svenska.png" />
			<?php } else { ?>
			<img id="ex_logo" style="text-align:center;" src="../images/images/no_logo_English.png" />
			<?php } ?>
				<div style="display:inline-block; padding:2em 1em 1em 0; max-width:45%" id="status"></div>
				<div style="display:inline-block; float:right; width:55%; padding:2em 0em 1em 0em;" id="area"></div>
				<div style="display:none; padding-top: 1.5em" id="price"></div>
			
		</div>
		<div class="column">
			<div class="info">
				<div class="website_link"></div>
				<div style="display:inline-block; padding:1em 0 0 0; width:100%;" id="categories"></div>
				<div style="display:inline-block; padding:1em 0 1em 0; width:100%;" id="commodity"></div>
			</div>
		</div>
			<h4></h4>
			<div class="presentation"></div>
			<div id="more_info_print"></div>
	</div>

	<div id="mapHolder">
		<div id="map">
			<div id="maptool_grid"><div id="maptool_grid_frame"></div></div>
			<img alt="" id="map_img" />
		</div>
	</div>

<script type="text/javascript">
	
	<?php if ($reserve != 'false') { ?>
			var reserveId = <?php echo $reserve; ?>
	<?php } else { ?>
			var reserveId = null;
	<?php } ?>

	var fair_url = '<?php echo $fair->get('url')?>';
	var accessibleMaps = new Array;
	<?php foreach($accessible_maps as $map) { ?>
		accessibleMaps.push(<?php echo $map ?>);
	<?php } ?>

	$(document).ready(function() {
		<?php 
			$id = "";
			if(!empty($myMap)){
				if($myMap == '\'false\''){
					$maps = $fair->get('maps');
					$id = reset($maps)->get('id');
				} else {
					$id = $myMap;
				}
			}
			echo 'maptool.init('.$id.');';
			
		?>
	});

</script>

	<?php if (is_int($myMap)) { ?>
	<script>
		$(document).ready(function(){
			$('#map_link_<?php echo $myMap; ?>').click();
		});
	</script>
	<?php } ?>

<?php } ?>

<div id="fairRegistrationConfirm" class="dialogue popup">
	<img src="images/icons/close_dialogue.png" style="margin-top:-3.7em" class="closeDialogue"/>
	<h2 class="standSpaceName" style="margin-top:-3.15em; text-align:center;"><?php echo $translator->{"Thank you for your application"}; ?></h2>
	<p><?php echo $translator->{"A receipt of your application has been sent to your inbox and it is now up to the Organizer to do the rest of the work. You can apply for more stand spaces if you want in the same manner."}; ?></p>
	<input type="button" class="greenbutton mediumbutton closeDialogue" style="margin-bottom:1em;" value="Ok" />
</div>


<div id="todayDt" td="<?php echo time(); ?>"> </div>

<style>
#fair_registration_form fieldset {
	padding-top: 0;
	padding-bottom: 2.5em;
	border-top: solid #48A547 5em;
}
</style>
<!-- multistep form -->
<form id="fair_registration_form" class="form booking_form popup">
<!-- progressbar -->
<ul class="progressbar">
	<li class="active"><?php echo uh($translator->{'Categories and space'}); ?></li>
	<li><?php echo uh($translator->{'Articles and extra options'}); ?></li>
	<li><?php echo uh($translator->{'Message and Assortment'}); ?></li>
	<li><?php echo uh($translator->{'Confirm registration'}); ?>
</ul>
	<fieldset>
		<!-- FIELDSET NUMBER ONE -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue popupCloseDialogue"/>
		<br />
		<label for="registration_area_input"><?php echo uh($translator->{'Requested area'}); ?> *</label>
		<input type="text" name="area" id="registration_area_input" />
	    		<!-- Div för att välja kategori -->
		<label class="table_header" for="registration_category_scrollbox"><?php echo uh($translator->{'Categories'}); ?> *</label>
		<div class="scrolltable-wrap" id="registration_category_scrollbox_div">
			<table class="std_table std_booking_table" id="registration_category_scrollbox">
				<thead>
					<tr>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('categories') as $cat){ ?>
					<tr>
						<td class="left"><?php echo $cat->get('name') ?></td>
						<td class="cfscheckbox">
							<input type="checkbox" id="<?php echo $cat->get('id') ?>" value="<?php echo $cat->get('id') ?>" />
							<label class="squaredFour" for="<?php echo $cat->get('id') ?>" />
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>	
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" id="registration_first_step" class="greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />

	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER TWO -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue popupCloseDialogue"/>
			<!--  Extra tillval -->
	<?php if ($fair->get('extraOptions')) { ?>
		<label class="table_header" for="registration_option_scrollbox"><?php echo uh($translator->{'Extra options'}); ?></label>
		<div class="scrolltable-wrap">
			<table class="std_table std_booking_table" id="registration_option_scrollbox">
				<thead>
					<tr>
						<th>ID</th>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th><?php echo uh($translator->{'Price'}); ?></th>
						<th class="cfscheckbox" data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('extraOptions') as $extraOption) { ?>
						<?php if ($extraOption->get('required') == 1) { ?>
							<tr>
								<td><?php echo $extraOption->get('custom_id') ?></td>
								<td class="left"><?php echo $extraOption->get('text') ?>*</td>
								<td><?php echo $extraOption->get('price') ?></td>
								<td style="display:none;">
								<?php if ($extraOption->get('vat') == 25) { ?>
									<input value="25" />
								<?php } else if ($extraOption->get('vat') == 18) { ?>
									<input value="18" />
								<?php } else if ($extraOption->get('vat') == 12) { ?>
									<input value="12" />
								<?php } else { ?>
									<input value="0" />
								<?php } ?>
								</td>
								<td class="cfscheckbox">
									<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" value="<?php echo $extraOption->get('id') ?>" checked="checked" />
									<input type="checkbox" disabled="disabled" checked>
									<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
								</td>
							</tr>
						<?php } else { ?>
							<tr>
								<td><?php echo $extraOption->get('custom_id') ?></td>
								<td class="left"><?php echo $extraOption->get('text') ?></td>
								<td><?php echo $extraOption->get('price') ?></td>
								<td style="display:none;">
								<?php if ($extraOption->get('vat') == 25) { ?>
									<input hidden value="25" />
								<?php } else if ($extraOption->get('vat') == 18) { ?>
									<input hidden value="18" />
								<?php } else if ($extraOption->get('vat') == 12) { ?>
									<input hidden value="12" />
								<?php } else { ?>
									<input hidden value="0" />
								<?php } ?>
								</td>
								<td class="cfscheckbox">
									<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" value="<?php echo $extraOption->get('id') ?>"/>
									<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
								</td>
							</tr>
						<?php } ?>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php } else { ?>
	<br/>
	<h2><?php echo uh($translator->{'Extra options'}); ?></h2>
	<?php echo uh($translator->{"There are no options to display"}); ?>
	<br />
	<?php } ?>
			<!--  Artiklar  -->
	<?php if ($fair->get('articles') && $available_articles >= 1 || $hasRights) { ?>
		<label class="table_header" for="registration_article_scrollbox"><?php echo uh($translator->{"Articles"}); ?></label>
		<div class="scrolltable-wrap">
			<table class="std_table" id="registration_article_scrollbox">
				<thead>
					<tr>
						<th>ID</th>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th><?php echo uh($translator->{'Price'}); ?></th>
						<th style="text-indent:-3.9166em;" data-sorter="false"><?php echo uh($translator->{'Amount'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('articles') as $article) { ?>
						<?php if ($article->get('required') != 1 || $hasRights) { ?>
							<tr>
								<td><?php echo $article->get('custom_id') ?></td>
								<td class="left"><?php echo $article->get('text') ?></td>
								<td><?php echo $article->get('price') ?></td>
								<td style="display:none;">
									<?php if ($article->get('vat') == 25) { ?>
										<input hidden value="25" />
									<?php } else if ($article->get('vat') == 18) { ?>
										<input hidden value="18" />
									<?php } else if ($article->get('vat') == 12) { ?>
										<input hidden value="12" />
									<?php } else { ?>
										<input hidden value="0" />
									<?php } ?>
								</td>
								<td class="td-number-span"><input type="text" class="form-control bfh-number" min="0" value="0" id="<?php echo $article->get('id') ?>" /></td>
							</tr>
						<?php } ?>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php } else { ?>
	<br/>
	<h2><?php echo uh($translator->{'Articles'}); ?></h2>
	<?php echo uh($translator->{"There are no articles to display"}); ?>
	<br />
	<br />
	<?php } ?>
		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" name="next" class="next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />
		
	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER THREE -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue popupCloseDialogue"/>
		<br />
		<label class="label_medium" for="registration_commodity_input"><?php echo uh($translator->{'Commodity'}); ?> *</label>
			<textarea name="registration_commodity_input" maxlength="200" class="commodity_big" id="registration_commodity_input"></textarea>


		<label class="label_long" for="registration_message_input"><?php echo uh($translator->{'Message to organizer'}); ?></label>
		<textarea name="registration_message_input" class="msg_to_organizer" id="registration_message_input"></textarea>
		<br />
		<br />

		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" name="next" class="registration_review next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />	
	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER FOUR -->
		<h3 class="standSpaceName"></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue popupCloseDialogue"/>
		<br />
		<div id="review_prel_dialogue">
			<div class="column review_column1">
				<label for="review_registration_area"><?php echo uh($translator->{'Area'}); ?></label>
				<p name="review_registration_area" id="review_registration_area"></p>
				<label for="review_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
				<p name="commodity" id="review_commodity_input"></p>
			</div>
			<div class="column review_column2">
				<label for="review_category_list"><?php echo uh($translator->{'Categories'}); ?></label>
				<p id="review_category_list" style="width:100%; float:left;"></p>		
			</div>
			<label for="review_message" id="review_message_label"><?php echo uh($translator->{'Message to organizer'}); ?></label>
			<p name="arranger_message" id="review_message"></p>	
			<div class="no-search" id="review_list_div" style="padding:1.66em 0px;">
				<div id="review_div">
					<table id="review_list" class="no-search" style="width:100%;">
					</table>
				</div>
				<table id="review_list2" class="no-search" style="width:100%;">
				</table>
			</div>
		</div>

		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="submit" name="submit" id="registration_confirm" class="submit greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Submit booking'}); ?>" />
		
	</fieldset>
</form>

<?php if (!isset($_SESSION['user_level']) && (!isset($_SESSION['visitor']) || !$_SESSION['visitor'])) { ?>
<div id="nouser_dialogue" class="dialogue popup">
	<!--<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0 0 0 22.33em;" />-->
	
	<div id="inner" style="text-align: center;">
		<img src="images/logo/chartbooking_logo_small.png" alt="" class="nouser_cfslogo" />
		<p class="center">
			<?php echo ($translator->{'To use this service you need an account:'}); ?>
		</p>
	</div>

	<div class="clear floatleft cfspanel">
		<p class="right"><a class="link-button loginlink" href="user/login/<?php echo $_SESSION['outside_fair_url'] ?>" id="open_loginform"><span style="font-weight:600;"><?php echo uh($translator->{'I already have an account'}); ?></a></span></p>
	</div>	

	<div class="floatright cfspanel">
		<p><a href="user/register" class="link-button registerlink"><span style="font-weight:600;"><?php echo uh($translator->{'Register new account'}); ?></a></span></p>
	</div>
	
	<div>
		<p><a class="link-button helpLink"><span style="font-weight:600;"><?php echo uh($translator->{'I NEED HELP'}); ?></a></span></p>
		<p></p>
		<p>
			<a href="user/resetPassword/backref/<?php echo $fair->get('url'); ?>">
				<span class="backhref"><?php echo uh($translator->{"Forgot your password?"}); ?></span>
			</a>
		</p>
	</div>
</div>
<div class="modal"><!-- Place at bottom of page --></div>
<script>
	$(document).ready(function() {
		$('#overlay').show();
		$('#nouser_dialogue').show();

	if ( $('#alertBox').filter(':visible').length){
	    $('#nouser_dialogue').hide();
	} 

		ajaxLoginForm($('#user_login_dialogue form'));

		$('#open_loginform').click(function(e) {
			$('#user_login_dialogue').show();
		});
	});
</script>
<?php } ?>
<script>
 $( function() {
    $( "#map_div_info" ).draggable();
  } );
</script>