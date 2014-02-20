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
	$visible = null;

	$f = new Fair;
	if (userLevel() > 0) {
		$f->load($_SESSION['user_fair'], 'id');
	} else {
		$f->load($_SESSION['outside_fair_url'], 'url');
	}
	
	// Hämta ut fältet hidden för att se om mässan är dold eller ej.
	if($f->get('hidden') == 0) :
		$visible = 'true';
	else:
		$visible = 'false';
	endif;
		
	if($visible == 'false' && !(userLevel() > 2)) : ?>
		<script type="text/javascript">
			$().ready(function(){
				alert("<?php echo $translator->{'This fair is hidden'}?>");
			});
		</script>
	<?php endif;

	// Om mässan är synlig
	if(($visible == 'false' && userLevel() > 2) || ($visible == 'false' && userLevel() == 2 && hasRights()) || $visible == 'true') :
		// Om användaren har nivå 1 men ej är ansluten till mässan
		if (userLevel() == 1 && !userIsConnectedTo($f->get('id'))):
			// Ajax-kod för att ansluta en användare till mässan ?>
			<script type="text/javascript">
				$().ready(function(){
					$.ajax({
					url: 'ajax/maptool.php',
					type: 'POST',
					data: 'connectToFair=1&fairId=' + <?php echo $f->get('id')?>,
					success: function(response) {
						res = JSON.parse(response);
						window.location = '<?php echo $fair->get('url')?>';
					}
				});
			});
			</script>
		<?php endif;?>
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
			<img  alt="" id="map_img"/>
		</div>
		
	</div>

<?php endif;?>
		
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
	lang.notes = '<?php echo $translator->{"Notes"} ?>';
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
	lang.clickToViewMoreInfo = '<?php echo $translator->{"Click to view more information"} ?>';
	lang.noPresentationText = '<?php echo $translator->{"The company has not specified any information."}?>';
	lang.insert_comment = '<?php echo $translator->{"Insert comment"}?>';
	
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

	var fair_url = '<?php echo $fair->get('url')?>';
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

<?php if (!isset($_SESSION['user_level']) && (!isset($_SESSION['visitor']) || !$_SESSION['visitor'])): ?>
<div id="nouser_dialogue" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0 0 0 268px;"/>

	<div class="clear floatleft panel">
		<p class="right"><a href="user/login" id="open_loginform" class="link-button"><?php echo $translator->{'I already have an account'} ?></a></p>
		<div id="user_login_dialogue">
			<form action="user/login" method="post">
				<p class="error"></p>
				<div><label for="user"><?php echo $translator->{"Username"} ?></label>
				<input type="text" name="user" id="user"/>
				<label for="pass"><?php echo $translator->{"Password"} ?></label>
				<input type="password" name="pass" id="pass"/>
				<p><a href="user/resetPassword/backref/<?php echo $fair->get('url'); ?>"><?php echo $translator->{"Forgot your password?"} ?></a></p>
				<p><input type="submit" name="login" value="<?php echo $translator->{"Log in"} ?>"/></p></div>
			</form>
		</div>
	</div>
	<div class="floatright panel">
		<p><a href="user/register" class="link-button registerlink"><?php echo $translator->{'Register new account'} ?></a></p>
		<p></p>
	</div>
</div>

<script>
	$(document).ready(function() {
		$('#overlay').show();
		$('#nouser_dialogue').show();

		ajaxLoginForm($('#user_login_dialogue form'));

		$('#open_loginform').click(function(e) {
			e.preventDefault();
			$('#user_login_dialogue').show();
		});

		$('.registerlink').click(function(e) {
			$(".closeDialogue").click(function() {
				if ($('#nouser_dialogue:visible').length) {
					$("#overlay").show();
				}
			});
		});
	});
</script>
<?php endif; ?>

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
	<textarea name="position_info_input" id="position_info_input" placeholder="<?php echo htmlspecialchars($translator->{'Enter information about the stand space that would be interesting for the exhibitor to know, for example: This stand space is in the center of the IT-area and very well positioned for demonstration of your products.'}); ?>"></textarea>

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
			<input type="checkbox" value="<?php echo $cat->get('id') ?>" /><?php echo $cat->get('name') ?>
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
	<textarea rows="3" style="height:45px; resize:none;" type="text" class="dialogueInput" name="book_commodity_input" id="book_commodity_input"></textarea>

	<label for="book_message_input"><?php echo $translator->{'Message to organizer'} ?></label>
	<textarea name="book_message_input" style="resize:none;" id="book_message_input"></textarea>

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
	<div class="info"></div>
	<h4 style="margin-bottom: 0px;"></h4>
	<div class="presentation" style="margin-top: 0px;"></div>
	<p class="website_link"></p>
</div>
<?php
	if((userLevel() == 2 && userIsConnectedTo($fair->get('id'))) || userLevel() > 2) : ?>
		<div id="note_dialogue" class="dialogue">
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" />
		<h2></h2>
		<h3 data-text="<?php echo $translator->{'Notes on'}; ?>"></h3>
		<div class="commentList" style="max-height:280px; margin-bottom:30px; overflow-y:scroll;">
			<ul>
				<li>
					<div class="comment">
						<ul>
							<li><?php echo $translator->{'Written by'}; ?>: </li>
							<li><?php echo $translator->{'Date'}; ?>: </li>
							<li><?php echo $translator->{'Note'}; ?>: </li>
						</ul>
					</div>
				</li>
			</ul>
		</div>
		<textarea cols="30" rows="10" style="resize:none;"></textarea>
		<button><?php echo $translator->{'Insert comment'}; ?></button> <select id="commentOnSpace"><option value="0"><?php echo $translator->{'For this stand space only'}; ?></option><option value="1"><?php echo $translator->{'For all the stand spaces of the exhibitor'}; ?></option></select>
	<?php endif?>
</div>

<div id="todayDt" td="<?php echo time(); ?>"> </div>
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
				<input type="checkbox" value="<?php echo $cat->get('id') ?>" /><?php echo $cat->get('name') ?>
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

	<label for="reserve_expires_input"><?php echo $translator->{'Reserved until'} ?> (DD-MM-YYYY HH:MM <?php echo TIMEZONE; ?>)</label>
	<input type="text" class="dialogueInput datetime datepicker" name="reserve_expires_input" id="reserve_expires_input" value=""/>
	<p><input type="button" id="reserve_post" value="<?php echo $translator->{'Confirm reservation'} ?>"/></p>
</div>

<div id="apply_mark_dialogue" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>
	<h3><?php echo $translator->{'Apply for stand space'} ?></h3>
	
	<div class="ssinfo"></div>
	
	<label for="apply_category_scrollbox"><?php echo $translator->{'Category'} ?></label>
	<div style="width:300px; height:100px; overflow-y:scroll; background-color:#eee; border:1px solid #ccc; overflow-x:hidden;" id="apply_category_scrollbox">
		<?php foreach($fair->get('categories') as $cat): ?>
		<p>
			<input type="checkbox" value="<?php echo $cat->get('id') ?>" /><?php echo $cat->get('name') ?>
		</p>
		<?php endforeach; ?>
	</div>
	
	<label for="apply_commodity_input"><?php echo $translator->{'Commodity'} ?></label>
	<?php if(isset($me)) : ?>
		<input type="text" name="apply_commodity_input" id="apply_commodity_input" value="<?php echo $me->get('commodity')?>"/>
	<?php else : ?>
		<input type="text" name="apply_commodity_input" id="apply_commodity_input"/>
	<?php endif; ?>


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



<?php if( is_int($myMap) ) : ?>
<script>
	$(document).ready(function(){
		$('#map_link_<?php echo $myMap; ?>').click();
	});
</script>
<?php endif; ?>
