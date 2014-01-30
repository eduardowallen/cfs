<?php global $translator; ?>
<style>
	button{position:relative; top:-5px;}
	#content{max-width:1280px;}
</style>

<script type="text/javascript" src="js/tablesearch.js"></script>
<h1 style="float: left; margin-right: 60px;"><?php echo $fair->get('name'); ?></h1>

<?php if($fairs_admin): // If a list of accessible fairs is found, display a drop-down list to choose from ?>
  <label class="inline-block"><?php echo $translator->{'Switch to event: '}; ?></label>
  <select onchange="if(this.value) document.location.href=this.value;">
  <?php
    $own = false;
    $options = '';
    foreach($fairs_admin as $fa) {
      $active = $fair->get('id') == $fa['id'];
      $own = $own || $active;
      $options .= '<option value="'.BASE_URL.'administrator/reservationsChangeFair/'.$fa['id'].'"'.($active?" selected":"").'>'.$fa['name'].'</option>';
    }
    
    if(!$own) :
  ?>
    <option value selected><?php echo $fair->get('name'); ?></option>
  <?php
    endif;
    echo $options;
  ?>
  </select>
  <br class="clear">
<?php endif; ?>

<script type="text/javascript">
	var confirmDialogue = "<?php echo $confirm_delete?> %s?";
	var deletion = "<?php echo $deletion_comment?>";

	function denyPrepPosition(link, position, status){
		if(confirm(confirmDialogue.replace('%s', position))){
			var message = prompt(deletion, "");
			denyPosition(link, message, position, status);
		}
	}

	function hider(btn,elem){
		var element = $('#'+elem);
		var helement = $('#h'+elem);
	
		if($(btn).attr('hid') == "0"){
			element.css('display','none');
			helement.css('display','none');
			$(btn).attr('hid', '1');
			$(btn).children().attr('src', '<?php echo BASE_URL."public/images/icons/utv.png";?>');
		} else{
			element.css('display','table');
			helement.css('display','block');
			$(btn).attr('hid', '0');
			$(btn).children().attr('src', '<?php echo BASE_URL."public/images/icons/min.png";?>');
		}	
	}

	$(document).ready(function(){
		setTimeout(function(){
			anpassaTabeller();
			
			for(var i = 0; i<3; i++){
				var tblarr = new Array('booked', 'reserved', 'prem');
				var header = $('#h'+tblarr[i]+' > ul');
				var headertmp = $('#'+tblarr[i]+' > thead > tr');
			
				var headerarr = new Array();
				
				headertmp.children().each(function(i){
					headerarr[i] = $(this).width();
				
				});
			
				header.children().each(function(i){
					$(this).css('width', headerarr[i]);
				});

				var height = $('#'+tblarr[i]+' > thead').height();
				height = height * -1;

				$('#'+tblarr[i]).css('margin-top', height);
				$('#h'+tblarr[i]+' > thead').css('visibility', 'hidden');

				if(i == 2){header.css('display', 'block');}
			}
		}, 500);
	});

	function multiCheck(box){
		$('#'+box+' > tbody > tr').children(':last-child').children().prop('checked', $('#h'+box+' > ul > li:last-child > input:last-child').prop('checked'));
	}

	/* A function to collect data from a specified HTML table (the inparameter takes the ID of the table) */
	function prepareTable(tbl){
		var rowArray = new Array();
		var colArray = new Array();

		var table = $('#'+tbl);
		var rows = 0;

		$('#h'+tbl+' > ul > li').each(function(){
			if($(this).children('input').prop('checked')){
				colArray.push($(this).children('input').val());
			}
		});

		table.children(':last-child').children().each(function(){
			var thisRow = $(this);
			var thisRowCheckBox = thisRow.children(':last-child').children();

			if(thisRowCheckBox.prop('checked') == true){
				rowArray.push(thisRowCheckBox.attr('id'));
			}
		});
		
		
		if(tbl == "booked"){
			exportTableToExcel(rowArray, colArray, 1);
		} else if(tbl == "reserved"){
			exportTableToExcel(rowArray, colArray, 2);
		} else if(tbl == "prem"){
			exportTableToExcel(rowArray, colArray, 3);
		}
		

		rowArray = [];
		colArray = [];
	}

	/* Takes an array and sends it to the desired controller in order to export. */
	function exportTableToExcel(rowArray, colArray, tbl){
		/* Make the array passable in a url */
		var urlForColumns = "/dataColumns|";
		var urlForRows = "/dataRows|";
		if(colArray.length > 0){
		$(colArray).each(function(i){
				if(i == 0){
					urlForColumns = urlForColumns + colArray[i];
				} else if(i < (colArray.length - 1)){
					urlForColumns = urlForColumns + "|"+colArray[i];
				
				}
			});
		} else {
			alert('<?php echo $col_export_err?>');
			return 0;
		}

		if(rowArray.length > 0){
			$(rowArray).each(function(i){
				if(i == 0){
					urlForRows = urlForRows + rowArray[i];
				} else {
					urlForRows = urlForRows + "|"+rowArray[i];
				}
			});
		} else {
			alert('<?php echo $row_export_err?>');
			return 0;
		}
		var finishedUrl = '/' + tbl  + urlForColumns + urlForRows;
		window.location = '<?php echo BASE_URL."administrator/exportNewReservations"?>'+finishedUrl;
	}

	function anpassaTabeller(){
		var tbl1width = $('#booked').width();
		var tbl2width = $('#reserved').width();
		var tbl3width = $('#prem').width();

		$('.tbl1').css('width', tbl1width);
		$('.tbl2').css('width', tbl2width);
		$('.tbl3').css('width', tbl3width);
	}
</script>

<?php if ($hasRights): ?>

<div id="reserve_position_dialogue" class="dialogue">
	<form action="" method="post">
		<img src="images/icons/close_dialogue.png" alt="" onclick="closeDialogue('reserve')" class="closeDialogue"/>
		<h3><?php echo $translator->{'Reserve stand space'} ?></h3>

		<div class="ssinfo"></div>
		
		<label for="reserve_category_input"><?php echo $translator->{'Category'} ?></label>
		<div id="reserve_category_scrollbox" style="width:300px; height:100px; overflow-y:scroll; background-color:#eee; border:1px solid #ccc; overflow-x:hidden;">
			<?php foreach($fair->get('categories') as $cat): ?>
				<p style="margin:0; width:100%; float:left;">
					<input type="checkbox" name="categories[]" value="<?php echo $cat->get('id') ?>" /><?php echo $cat->get('name') ?>
				</p>
			<?php endforeach; ?>
		</div>
		
		<label for="reserve_commodity_input"><?php echo $translator->{'Commodity'} ?></label>
		<input type="text" class="dialogueInput" name="commodity" id="reserve_commodity_input" />
		
		<label for="reserve_message_input"><?php echo $translator->{'Message to organizer'} ?></label>
		<textarea name="arranger_message" id="reserve_message_input"></textarea>

		<label for="reserve_user_input"><?php echo $translator->{'User'} ?></label>
		<select style="width:300px;" id="reserve_user_input" disabled="disabled">
			<option id="reserve_user"></option>
		</select>

		<label for="reserve_expires_input"><?php echo $translator->{'Reserved until'} ?> (dd-mm-yyyy hh:mm)</label>
		<input type="text" class="dialogueInput datetime datepicker" name="expires" id="reserve_expires_input" value="<?php echo date('d-m-Y H:m', (isset($fairCloses) && $fairCloses > 0 ? $fairCloses : time()));  ?>"/>

		<p>
			<input type="hidden" name="id" id="reserve_id" />
			<input type="submit" name="reserve" value="<?php echo $translator->{'Confirm reservation'} ?>" />
		</p>
	</form>
</div>

<div id="book_position_dialogue" class="dialogue">
	<form action="" method="post">
		<img src="images/icons/close_dialogue.png" alt="" onclick="closeDialogue('book')" class="closeDialogue"/>
		<h3><?php echo $translator->{'Book stand space'} ?></h3>

		<div class="ssinfo"></div>
		
		<label for="book_category_input"><?php echo $translator->{'Category'} ?></label>
		<div id="book_category_scrollbox" style="width:300px; height:100px; overflow-y:scroll; background-color:#eee; border:1px solid #ccc; overflow-x:hidden;">
			<?php foreach($fair->get('categories') as $cat): ?>
			<p style="margin:0; width:100%; float:left;">
				<input type="checkbox" name="categories[]" value="<?php echo $cat->get('id') ?>" /><?php echo $cat->get('name') ?>
			</p>
			<?php endforeach; ?>
		</div>
		
		<label for="book_commodity_input"><?php echo $translator->{'Commodity'} ?></label>
		<input type="text" class="dialogueInput" name="commodity" id="book_commodity_input" />

		<label for="book_message_input"><?php echo $translator->{'Message to organizer'} ?></label>
		<textarea name="arranger_message" id="book_message_input"></textarea>

		<label for="book_user_input"><?php echo $translator->{'User'} ?></label>
		<select style="width:300px;" id="book_user_input" disabled="disabled">
			<option id="book_user"></option>
		</select>

		<p>
			<input type="hidden" name="id" id="book_id" />
			<input type="submit" name="approve" value="<?php echo $translator->{'Confirm booking'} ?>" />
		</p>
	</form>
</div>

<div id="arranger_message_popup" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue close-popup" />

	<h3><?php echo $tr_message; ?></h3>

	<p id="arranger_message_text"></p>

	<p class="center">
		<a href="#" class="link-button close-popup"><?php echo $ok_label; ?></a>
	</p>
</div>



<h2 class="tblsite" style="margin-top:20px"><?php echo $headline; ?><a hid="0" style="cursor:pointer;" onclick="hider(this,'booked')"><img style="width:30x; height:15px; margin-left:20px;" src="<?php echo BASE_URL."public/images/icons/min.png";?>" alt="" /></a></h2>
<div class="tbld tbl1">
<?php if(count($positions) > 0){ ?>
	
	<div class="tblHeader" id="hbooked">
		<a onclick="prepareTable('booked')"><button style="float:left; width:98%;"><?php echo $export?></button></a>
		<ul class="special">
			<li><div class="tblrow1"><?php echo $tr_pos; ?></div><input type="checkbox" value="1" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_area; ?> (m<sup>2</sup>)</div><input type="checkbox" value="2" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_booker; ?></div><input type="checkbox" value="3" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_field; ?></div><input type="checkbox" value="4" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_time; ?></div><input type="checkbox" value="5" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_message; ?></div><input type="checkbox" value="6" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_view; ?></div><div style="padding-top:19px;"></div></li>
			<li><div class="tblrow1"><?php echo $tr_delete; ?></div><div style="padding-top:19px;"></div></li>
			<li><div class="tblrow1"></div><input type="checkbox" onclick="multiCheck('booked')" checked="checked" /></li>
		</ul>
	</div>
	<div class="scrolltbl onlyfive">
		<table class="std_table" id="booked" style="float:left; padding-right: 16px;">
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
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($positions as $pos):?>
				<tr>
					<td><?php echo $pos['name']; ?></td>
					<td class="center"><?php echo $pos['area']; ?></td>
					<td class="center"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>"><?php echo $pos['company']; ?></a></td>
					<td class="center"><?php echo $pos['commodity']; ?></td>
					<td><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
					<td class="center" title="<?php echo htmlspecialchars($pos['arranger_message']); ?>">
<?php if (strlen($pos['arranger_message']) > 0): ?>
						<a href="administrator/arrangerMessage/exhibitor/<?php echo $pos['id']; ?>" class="open-arranger-message">
							<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
						</a>
<?php endif; ?>
					</td>
					<td style="display:none;"><?php echo $pos['categories']?> </td>
					<td>
					<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" title="<?php echo $tr_view; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>
					<td class="center">
						<a style="cursor:pointer;" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'booking')">
		
							<img style="padding:0px 5px 0px 5px" src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>
					<td><input type="checkbox" id="<?php echo $pos['id']; ?>" checked="checked" /></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
<?php } else { ?>
	<p style="width:100%; float:left;"> <?php echo $booked_notfound?> </p>
<?php }?>



	<h2 class="tblsite" style="margin-top:20px"><?php echo $rheadline; ?><a hid="0" style="cursor:pointer;" onclick="hider(this,'reserved')"><img style="width:30x; height:15px; margin-left:20px;" src="<?php echo BASE_URL.'public/images/icons/min.png';?>" alt="" /></a></h2>	

<div class="tbld tbl2">
	<?php if(count($rpositions) > 0){?>
	<div class="tblHeader" id="hreserved">
		<a onclick="prepareTable('reserved')"><button style="float:left; width:98%;"><?php echo $export?></button></a>
		<ul class="special">
			<li><div class="tblrow1"><?php echo $tr_pos; ?></div><input type="checkbox" value="1" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_area; ?> (m<sup>2</sup>)</div><input type="checkbox" value="2" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_booker; ?></div><input type="checkbox" value="3" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_field; ?></div><input type="checkbox" value="4" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_time; ?></div><input type="checkbox" value="5" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_message; ?></div><input type="checkbox" value="7" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_reserved_until; ?></div><input type="checkbox" value="6" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_view; ?></div><div style="padding-top:19px;"></div></li>
			<li><div class="tblrow1"><?php echo $tr_delete; ?></div><div style="padding-top:19px;"></div></li>
			<li><div class="tblrow1"><?php echo $tr_approve; ?></div><div style="padding-top:19px;"></div></li>
			<li><div class="tblrow1"></div><input type="checkbox" onclick="multiCheck('reserved')" checked="checked" /></li>
		</ul>
	</div>
	<div class="scrolltbl onlyfive">
		<table class="std_table" id="reserved" style="float:left; padding-right: 16px;">
		<thead>
			<tr>
				<th><?php echo $tr_pos; ?></th>
				<th><?php echo $tr_area; ?> (m<sup>2</sup>)</th>
				<th><?php echo $tr_booker; ?></th>
				<th><?php echo $tr_field; ?></th>
				<th><?php echo $tr_time; ?></th>
				<th><?php echo $tr_message; ?></th>
				<th><?php echo $tr_reserved_until; ?></th>
				<th><?php echo $tr_view; ?></th>
				<th><?php echo $tr_deny; ?></th>
				<th><?php echo $tr_approve; ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($rpositions as $pos): ?>
			<tr data-id="<?php echo $pos['id']; ?>">
				<td><?php echo $pos['name']; ?></td>
				<td class="center"><?php echo $pos['area']; ?></td>
				<td class="center"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>"><?php echo $pos['company']; ?></a></td>
				<td class="center"><?php echo $pos['commodity']; ?></td>
				<td><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
				<td class="center" title="<?php echo htmlspecialchars($pos['arranger_message']); ?>">
<?php if (strlen($pos['arranger_message']) > 0): ?>
						<a href="administrator/arrangerMessage/exhibitor/<?php echo $pos['id']; ?>" class="open-arranger-message">
							<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
						</a>
<?php endif; ?>
				</td>
				<td style="display:none;"><?php echo $pos['categories']?></td>
				<td><?php echo date('d-m-Y H:i', strtotime($pos['expires'])); ?></td>
				<td class="approve" style="display:none;"><?php echo BASE_URL.'administrator/approveReservation/'; ?></td>
				<td class="center">
					<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" title="<?php echo $tr_view; ?>">
						<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
					</a>
				</td>
				
				<td class="center">
					<a style="cursor:pointer;" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'Reservation')">
						<img style="padding:0px 5px 0px 5px" src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_delete; ?>" />
					</a>
				</td>
				<td class="center">
					<?php //echo "href=".BASE_URL.'administrator/approveReservation/'.$pos['position'] ?><?php //echo " title=".$tr_approve; ?>
					<a  style="cursor:pointer;"  onclick="showPopup('book',this)">
						<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_approve; ?>" />
					</a>
				</td>
				<td><input type="checkbox" id="<?php echo $pos['id']; ?>" checked="checked" /></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
	</div>
</div>

<?php } else { ?>
<p style="width:100%; float:left;"> <?php echo $reserv_notfound?> </p>
<?php }?>



<h2 class="tblsite" style="margin-top:20px"><?php echo $prel_table; ?><a hid="0" style="cursor:pointer;" onclick="hider(this,'prem')"><img style="width:30x; height:15px; margin-left:20px;" src="<?php echo BASE_URL."public/images/icons/min.png";?>" alt="" /></a></h2>
<div class="tbld tbl3">
<?php if(count($prelpos) > 0){ ?>
	<div class="tblHeader" id="hprem">
		<a onclick="prepareTable('prem')"><button style="float:left; width:98%;"><?php echo $export?></button></a>
		<ul class="special">
			<li><div class="tblrow1"><?php echo $tr_pos; ?></div><input type="checkbox" value="1" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_area; ?> (m<sup>2</sup>)</div><input type="checkbox" value="2" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_booker; ?></div><input type="checkbox" value="3" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_field; ?></div><input type="checkbox" value="4" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_time; ?></div><input type="checkbox" value="5" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_message; ?></div><input type="checkbox" value="6" checked="checked" /></li>
			<li><div class="tblrow1"><?php echo $tr_view; ?></div><div style="padding-top:19px;"></div></li>
			<li><div class="tblrow1"><?php echo $tr_delete; ?></div><div style="padding-top:19px;"></div></li>
			<li><div class="tblrow1"><?php echo $tr_approve; ?></div><div style="padding-top:19px;"></div></li>
			<li><div class="tblrow1"><?php echo $tr_reserve; ?></div><div style="padding-top:19px;"></div></li>
			<li><div class="tblrow1"></div><input type="checkbox" onclick="multiCheck('prem')"  checked="checked" /></li>
		</ul>
	</div>
	<div class="scrolltbl onlyfive">
		<table class="std_table" id="prem" style="float:left; padding-right: 16px;">
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
				<th></th>
			</tr>
		</thead>
			<tbody>
			<?php foreach($prelpos as $pos): ?>
				<?php
					$hidden = 0;
					foreach($rpositions as $postemp):
						if($postemp['position'] == $pos['position']):
							$hidden = 1;
						endif;
					endforeach;

					foreach($positions as $postemp):
						if($postemp['position'] == $pos['position']):
							$hidden = 1;
						endif;
					endforeach;
				?>
				<?php if($hidden == 0) : ?>
				<tr id="prem" <?php if (isset($page) && $page > 1) echo 'style="display:none;"'; ?> data-id="<?php echo $pos['id']; ?>">
					<td><?php echo $pos['name'];?></td>
					<td class="center"><?php echo $pos['area']; ?></td>
					<td class="center"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>"><?php echo $pos['company']; ?></a></td>
					<td class="center"><?php echo $pos['commodity']; ?></td>
					<td class="center"><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
					<td class="center" title="<?php echo htmlspecialchars($pos['arranger_message']); ?>">
<?php if (strlen($pos['arranger_message']) > 0): ?>
						<a href="administrator/arrangerMessage/preliminary/<?php echo $pos['id']; ?>" class="open-arranger-message">
							<img src="<?php echo BASE_URL; ?>images/icons/script.png" alt="<?php echo $tr_message; ?>" />
						</a>
<?php endif; ?>
					</td>
					<td style="display:none;"><?php echo $pos['categories']?></td>
					<td class="approve" style="display:none;"><?php echo BASE_URL.'administrator/newReservations/approve/'; ?></td>
					<td class="reserve" style="display:none;"><?php echo BASE_URL.'administrator/reservePrelBooking/'; ?></td>
					<td class="center">
						<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" title="<?php echo $tr_view; ?>">
							<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
						</a>
					</td>
					<td class="center">
						<a style="cursor:pointer;" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'Preliminary Booking')">
							<img style="padding:0px 5px 0px 5px" src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_deny; ?>" />
						</a>
					</td>
					<td class="center">
						<?php //echo BASE_URL.'administrator/newReservations/approve/'.$pos['id'] ?><?php //echo $tr_approve; ?>
						<a style="cursor:pointer;" onclick="showPopup('book',this)">
							<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_approve; ?>" />
						</a>
					</td>
					<td class="center">
						<!-- <a href="<?php //echo BASE_URL.'administrator/reservePrelBooking/'.$pos['id'] ?>" title="<?php //echo $tr_reserve; ?>"> -->
						<a style="cursor:pointer;" onclick="showPopup('reserve',this)">
							<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_reserve; ?>" />
						</a>
					</td>
					<td><input type="checkbox" id="<?php echo $pos['id']; ?>" checked="checked" /></td>
				</tr>
				<?php endif?>
			<?php endforeach;?>
			</tbody>
		</table>
	</div>
</div>
<?php } else { ?>
	<p style="width:100%; float:left;"> <?php echo $prel_notfound?> </p>
<?php }?>
<?php else: ?>
	<p>Du är inte behörig att administrera den här mässan.</p>
<?php endif; ?>
