<?php
if ($notfound)
	die('Fair not found');



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

<?php 
	$visible = 'true';
	if($fair->get('hidden') == 1) :
		
		if (userLevel() < 2 && !userIsConnectedTo($fair->get('id'))):
			$visible = 'false';
		endif;
	endif;
	

	if($visible == 'false') : ?>
	
			<script>
			
				function connectToFair(id){
					$.ajax({
						url: 'ajax/maptool.php',
						type: 'POST',
						data: 'connectToFair=1&fairId=' + id,
						success: function(response) {
							res = JSON.parse(response);
							alert(res.message);
							if (res.success) {
								$("#connect")[0].remove();
								window.location = '<?php echo $fair->get('url')?>';
							}
						}
					});
				}
			</script>

			<div id="right_sidebar">
				<div>
				<?php $link = $fair->get('id')?>
				<?php if(userLevel() > 0) : ?>
					<p><a onclick="connectToFair(<?php echo $fair->get('id')?>)" id="connect"><?php echo $connect; ?></a></p>
				<?php else :?>
					<p><a href="user/login/<?php echo $fair->get('url')?>" id="connect"><?php echo $connect; ?></a></p>
				<?php endif;?>
				</div>
			</div>
		<?php
	else :
?>
<div id="fullscreen">
	<p id="fullscreen_controls">
		<a class="button delete" href="javascript:void(0)" id="closeFullscreen"><?php echo $translator->{'Leave fullscreen'} ?></a>
	</p>
</div>

<!--<h1 class="inline-block"><?php echo $fair->get('name'); ?>
	<span style="color:#000"> &ndash; <?php echo $translator->{'Available maps'} ?>: </span>
	<ul id="map_nav">
		<?php foreach ($fair->get('maps') as $map): ?>
			<li id="map_link_<?php echo $map->get('id'); ?>"><a href="javascript:void(0);" class="button map"><?php echo $map->get('name'); ?></a></li>
		<?php endforeach; ?>
	</ul>
</h1>-->

<script type="text/javascript">

	lang.bookStandSpace = '<?php echo $translator->{"Book stand space"} ?>';
	lang.editStandSpace = '<?php echo $translator->{"Edit stand space"} ?>';
	lang.moveStandSpace = '<?php echo $translator->{"Move stand space"} ?>';
	lang.deleteStandSpace = '<?php echo $translator->{"Delete stand space"} ?>';
	lang.reserveStandSpace = '<?php echo $translator->{"Reserve stand space"} ?>';
	lang.preliminaryBookStandSpace = '<?php echo $translator->{"Preliminary book stand space"} ?>';
	lang.cancelPreliminaryBooking = '<?php echo $translator->{"Cancel preliminary booking"} ?>';
	lang.editBooking = '<?php echo $translator->{"Edit booking"} ?>';
	lang.cancelBooking = '<?php echo $translator->{"Cancel booking"} ?>';
	lang.pasteExhibitor = '<?php echo $translator->{"Paste exhibitor"} ?>';
	lang.moreInfo = '<?php echo $translator->{"More info"} ?>';
	lang.space = '<?php echo $translator->{"Space"} ?>';
	lang.status = '<?php echo $translator->{"Status"} ?>';
	lang.area = '<?php echo $translator->{"Area"} ?>';
	lang.reservedUntil = '<?php echo $translator->{"Reserved until"} ?>';
	lang.by = '<?php echo $translator->{"by"} ?>';
	lang.commodity = '<?php echo $translator->{"commodity"} ?>';
	lang.clickToReserveStandSpace = '<?php echo $translator->{"Click to reserve stand space"} ?>';
	lang.presentation = '<?php echo $translator->{"Presentation"} ?>';
	lang.info = '<?php echo $translator->{"Info"} ?>';
	lang.deleteConfirm = '<?php echo $translator->{"Are you sure you want to delete this marker?"} ?>';
	lang.website = '<?php echo $translator->{"Website"} ?>';
	lang.print = '<?php echo $translator->{"Print"} ?>';
	lang.category = '<?php echo $translator->{"Categories"} ?>';
	lang.noPlaceRights = '<?php echo $translator->{"You do not have administrative rights on this map"} ?>';

	lang.StatusText = function(str) {
		if (str == 'open')
			return '<?php echo $translator->{"open"} ?>';
		else if (str == 'reserved')
			return '<?php echo $translator->{"reserved"} ?>';
		else if (str == 'booked')
			return '<?php echo $translator->{"booked"} ?>';
		else if (str == 'applied')
			return '<?php echo $translator->{"preliminary booked"} ?>';
	}
	
	<?php if ($reserve != 'false'): ?>
	var reserveId = <?php echo $reserve; ?>
	<?php else: ?>
	var reserveId = null;
	<?php endif; ?>
	
	<?php if ($position != 'false'): ?>
	var prePosId = <?php echo $position; ?>;
	<?php else: ?>
	var prePosId = null;
	<?php endif; ?>

	<?php if ($hasRights): ?>
	var hasRights = true;
	<?php else: ?>
	var hasRights = false;
	<?php endif; ?>

	var accessibleMaps = new Array;
	<?php foreach($accessible_maps as $map): ?>
		accessibleMaps.push(<?php echo $map ?>);
	<?php endforeach; ?>

	$(document).ready(function() {
		<?php 
			$id = "";
			if(!empty($myMap)){
				if($myMap == '\'false\''){
					$id = reset($fair->get('maps'))->get('id');
				} else {
					$id = $myMap;
				}
			}
			echo 'maptool.init('.$id.');';
			
		?>
		
		<?php if (isset($_SESSION['copied_exhibitor'])): ?>
		copiedExhibitor = "<?php echo $_SESSION['copied_exhibitor'] ?>";
		<?php endif; ?>
	});
</script>

<!--<p id="zoomcontrols">
	<a href="javascript:void(0)" class="button fullscreen" id="full"><?php echo $translator->{'View full screen'} ?></a>
	<a href="javascript:void(0)" class="button zoomin" id="in"><?php echo $translator->{'Zoom in'} ?></a>
	<a href="javascript:void(0)" class="button zoomneutral" id="neutral"><?php echo $translator->{'Normal view'} ?></a>
	<a href="javascript:void(0)" class="button zoomout" id="out"><?php echo $translator->{'Zoom out'} ?></a>
</p>-->

<!--<p id="leftfloatingbar"><span style="font-size:1.2em; font-weight:bold; margin-left:20px" class="button"><span style="color:green"><?php echo $opening_time.'</span>: '.date('d.m.Y', $fair->get('auto_publish')) ?> <span style="margin-left:30px; color:red"><?php echo $closing_time.'</span>: '.date('d.m.Y', $fair->get('auto_close')) ?></span></p>-->


<div id="edit_position_dialogue" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>
	<h3><?php echo $translator->{'New/Edit stand space'} ?></h3>

	<label for="position_name_input"><?php echo $translator->{'Name'} ?> *</label>
	<input type="text"  class="dialogueInput"  name="position_name_input" id="position_name_input"/>

	<label for="position_area_input"><?php echo $translator->{'Area'} ?> </label>
	<input type="text"  class="dialogueInput"  name="position_area_input" id="position_area_input"/>

	<label for="position_info_input"><?php echo $translator->{'Information'} ?></label>
	<textarea name="position_info_input" id="position_info_input"></textarea>

	<input type="hidden" name="position_id_input" id="position_id_input" value=""/>

	<p><input type="button" id="post_position" value="<?php echo $translator->{'Save and close'} ?>"/></p>

</div>

<div id="book_position_dialogue" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>
	<h3><?php echo $translator->{'Book stand space'} ?></h3>

	<div class="ssinfo"></div>
	
	<label for="book_category_input"><?php echo $translator->{'Category'} ?></label>
	<div id="book_category_scrollbox" style="width:300px; height:100px; overflow-y:scroll; background-color:#eee; border:1px solid #ccc; overflow-x:hidden;">
		<?php foreach($fair->get('categories') as $cat): ?>
		<p>
			<input type="checkbox" value="<?php echo $cat->get('id') ?>"><?php echo $cat->get('name') ?></input>
		</p>
		<?php endforeach; ?>
	</div>
	
	<?php /*
	<div id="hiddenExhibitorList_d">
		<ul>
			<?php echo makeUserOptions3(0, $fair)?>
		</ul>
	</div>
	

	<label for="search_user_input"><?php echo $translator->{'Search'}; ?></label>
	<input type="text" class="dialogueInput" name="search_user_input" id="search_user_input" />
	<p class="exhibitorNotFound" style="font-size:10px; font-weight:bold;"></p>
	<input type="hidden" id="book_user_input" />

	*/?>
	
	<label for="book_commodity_input"><?php echo $translator->{'Commodity'} ?></label>
	<input type="text" class="dialogueInput" name="book_commodity_input" id="book_commodity_input"/>

	<label for="book_message_input"><?php echo $translator->{'Message to organizer'} ?></label>
	<textarea name="book_message_input" id="book_message_input"></textarea>

	<label for="search_user_input"><?php echo $translator->{'Search'}; ?></label>
	<input type="text" style="width:300px;" name="search_user_input" id="search_user_input" />

	<label for="book_user_input"><?php echo $translator->{'User'} ?></label>
	<select  style="width:300px;" name="book_user_input" id="book_user_input">
		<?php echo makeUserOptions1(0, $fair); ?>
		<?php //echo makeUserOptions2($fair->db, 'user', 0, 'level=1', 'company'); ?>
	</select>

	<p><input type="button" id="book_post" value="<?php echo $translator->{'Confirm booking'} ?>"/></p>

</div>

<div id="more_info_dialogue" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>
	<h3></h3>
	<p class="info"></p>
	<h4 style="margin-bottom: 0px;"></h4>
	<p class="presentation" style="margin-top: 0px;"></p>
	<p class="website_link"></p>
</div>
<?php
	if((userLevel() == 2 && isConnectedToFair($fair->get('id'))) || userLevel() > 2) : ?>
		<div id="note_dialogue" class="dialogue">
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" />
		<h2></h2>
		<h3>Kommentarer för </h3>
		<div class="commentList">
			<ul>
				<li>
					<div class="comment">
						<ul>
							<li>Skrivet av: </li>
							<li>Datum: </li>
							<li>Kommentar: </li>
						</ul>
					</div>
				</li>
			</ul>
		</div>
		<textarea cols="30" rows="10" style="resize:none;"></textarea>
		<button>Skicka kommentar</button> <select id="commentOnSpace"><option value="0">För enbart denna platsen</option><option value="1">För utställarens alla platser</option></select>
	<?php endif?>
</div>

<div id="todayDt" td="<?php echo strtotime(date('d-m-Y'))?>"> </div>
<div id="closeDt" td="<?php echo $fair->get('auto_close')?>"> </div>
<div id="publishDt" td="<?php echo $fair->get('auto_publish')?>"> </div>

<div id="reserve_position_dialogue" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>
	<h3><?php echo $translator->{'Reserve stand space'} ?></h3>

	<div class="ssinfo"></div>
	
	<label for="reserve_category_input"><?php echo $translator->{'Category'} ?></label>
	<div id="reserve_category_scrollbox" style="width:300px; height:100px; overflow-y:scroll; background-color:#eee; border:1px solid #ccc; overflow-x:hidden;">
		<?php foreach($fair->get('categories') as $cat): ?>
			<p>
				<input type="checkbox" value="<?php echo $cat->get('id') ?>"><?php echo $cat->get('name') ?></input>
			</p>
		<?php endforeach; ?>
	</div>
	<?php /*
	<div id="hiddenExhibitorList">
		<ul>
			<?php echo makeUserOptions2(0, $fair)?>
		</ul>
	</div>

	<?php //print_r(makeUserOptions2(0, $fair)); ?>
	<label for="search_user_input"><?php echo $translator->{'Search'}; ?></label>
	<input type="text" class="dialogueInput" name="search_user_input" id="search_user_input" />
	<p class="exhibitorNotFound" style="font-size:10px; font-weight:bold;"></p>
	<input type="hidden" id="reserve_user_input" name="reserve_user_input" /> 
	*/?>
	
	<label for="reserve_commodity_input"><?php echo $translator->{'Commodity'} ?></label>
	<input type="text" class="dialogueInput" name="reserve_commodity_input" id="reserve_commodity_input"/>

	
	<label for="reserve_message_input"><?php echo $translator->{'Message to organizer'} ?></label>
	<textarea name="reserve_message_input" id="reserve_message_input"></textarea>

	<label for="search_user_input"><?php echo $translator->{'Search'}; ?></label>
	<input type="text" name="search_user_input" id="search_user_input" />

	<label for="reserve_user_input"><?php echo $translator->{'User'} ?></label>
	<select style="width:300px;" name="reserve_user_input" id="reserve_user_input">
		<?php echo makeUserOptions1(0, $fair); ?>
		<?php //echo makeUserOptions2($fair->db, 'user', 0, 'level=1', 'company'); ?>
	</select>

	<label for="reserve_expires_input"><?php echo $translator->{'Reserved until'} ?> (dd-mm-yyyy)</label>
	<input type="text" class="dialogueInput date datepicker" name="reserve_expires_input" id="reserve_expires_input" value="<?php if ($edit_id != 'new') { echo date('d-m-Y', $fair->get('auto_close')); } ?>"/>
	<p><input type="button" id="reserve_post" value="<?php echo $translator->{'Confirm reservation'} ?>"/></p>
</div>

<div id="apply_mark_dialogue" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>
	<h3><?php echo $translator->{'Apply for stand space'} ?></h3>
	
	<div class="mssinfo"></div>
	
	<label for="apply_category_input"><?php echo $translator->{'Category'} ?></label>
	<select style="width:300px;" name="apply_category_input[]" id="apply_category_input" multiple="multiple">
		<?php foreach($fair->get('categories') as $cat): ?>
		<option value="<?php echo $cat->get('id') ?>"><?php echo $cat->get('name') ?></option>
		<?php endforeach; ?>
	</select>
	
	<label for="apply_commodity_input"><?php echo $translator->{'Commodity'} ?></label>
	<input type="text" name="apply_commodity_input" id="apply_commodity_input"/>

	<label for="apply_message_input"><?php echo $translator->{'Message to organizer'} ?></label>
	<textarea name="apply_message_input" id="apply_message_input"></textarea>
	
	<p>
		<input type="button" id="apply_confirm" value="<?php echo $translator->{'Confirm'} ?>"/>
	</p>
</div>

<div id="apply_position_dialogue" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>
	<h3><?php echo $translator->{'Apply for stand space'} ?></h3>
	
	<div class="pssinfo"></div>
	
	<!--<label for="apply_category_input"><?php echo $translator->{'Category'} ?></label>
	<select name="apply_category_input[]" id="apply_category_input" multiple="multiple">
		<?php foreach($fair->get('categories') as $cat): ?>
		<option value="<?php echo $cat->get('id') ?>"><?php echo $cat->get('name') ?></option>
		<?php endforeach; ?>
	</select>
	
	<label for="apply_commodity_input"><?php echo $translator->{'Commodity'} ?></label>
	<input type="text" name="apply_commodity_input" id="apply_commodity_input"/>

	<label for="apply_message_input"><?php echo $translator->{'Message to organizer'} ?></label>
	<textarea name="apply_message_input" id="apply_message_input"></textarea>-->

	<p><input type="button" id="apply_post" value="<?php echo $translator->{'Confirm'} ?>"/></p>

</div>

<div id="pancontrols">
	<img src="images/icons/pan_left.png" id="panleft" alt=""/>
	<img src="images/icons/pan_up.png" id="panup" alt=""/>
	<img src="images/icons/pan_down.png" id="pandown" alt=""/>
	<img src="images/icons/pan_right.png" id="panright" alt=""/>
</div>

<p id="zoombar">
	<img src="images/zoom_marker_new.png" alt=""/>
	<a href="javascript:void(0)" id="in"></a>
	<a href="javascript:void(0)" id="out"></a>
</p>

<div id="mapHolder">
	<div id="map">
		<img src="<?php echo reset($fair->get('maps'))->get('image'); ?>" alt="" id="map_img"/>
	</div>
</div>
<?php if( is_int($myMap) ) : ?>
<script>
	$(document).ready(function(){
		$('#map_link_<?php echo $myMap; ?>').click();
	});
</script>
<?php endif; ?>
<?php endif; ?>
