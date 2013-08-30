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
	var defaultvalue='<?php echo $fair->get('default_currency');?>';
	var currentvalue = '<?php echo $fair->get('default_currency');?>';

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
	lang.clickToViewMoreInfo = '<?php echo $translator->{"Click to view more information"} ?>';
	lang.noPresentationText = '<?php echo $translator->{"The company has not specified any information."}?>';
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

		$('.valuta').change(function(value){
			$('.values').val($('.valuta').val());
			 convertAllPrices(currentvalue, $('.valuta').val());
		});

		$('.values').change(function(value){
			$('.valuta').val($('.values').val());
			 convertAllPrices(currentvalue, $('.values').val());
		});

		$('.valutaStand').change(function(){
			changeValue(from, to, 'bokningsrutan');
		});

		$('.search').keyup(function(){
			articleSearch($(this).val());
		});

		$('.add').bind('click', function(){
			var num = $(this).parent().parent().children('.numberOfArticles').text();
			num = parseFloat(num);
			num = num + 1;
			var price = parseFloat($(this).parent().parent().parent().children('.price').text());
			calcProductPrice(num, price, $(this));
			var totprice = 0;

			$(this).parent().parent().children('.numberOfArticles').text(num);
		});

		$('.dec').bind('click', function(){
			var num = $(this).parent().parent().children('.numberOfArticles').text();
			num = parseFloat(num);
			num-=1;
			if(num == -1){
				num = 0;
			}
			$(this).parent().parent().children('.numberOfArticles').text(num);
			var price = parseFloat($(this).parent().parent().parent().children('.price').text());
			var totprice = 0;
			calcProductPrice(num, price, $(this));
		});
	});

	function articleSearch(term){
		var count = 0;
		$('#article_dialogue ul li').each(function(){
			$(this).children('ul').children('li').children('table').children('tbody').children('tr').each(function(){
				var name = $(this).children('.name').text();
				if(name.indexOf(term) == -1){
					$(this).css('display', 'none');
				} else {
					$(this).css('display', 'table-row');
					count++;
				}
			});
			if(count < 1){
				$(this).css('display', 'none');
			} else {
				$(this).css('display', 'block');
			}
		});
	}
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

	<label for="position_price_input"><?php echo $translator->{'Price'} ?> *</label>
	<input type="text" class="dialogueInput"  name="position_price_input" id="position_price_input"/>

	<p class="area"><input type="text" placeholder="x"  class="dialogueInput x"  name="position_area_x" id="position_area_x"/>x<input type="text" placeholder="y" class="dialogueInput y"  name="position_area_y" id="position_area_y"/>=<input type="text" class="dialogueInput ans"  name="position_area_input" id="position_area_input"/>m²</p>
	
	<label for="position_info_input"><?php echo $translator->{'Information'} ?></label>
	<textarea name="position_info_input" id="position_info_input"></textarea>

	<input type="hidden" name="position_id_input" id="position_id_input" value=""/>

	<p><input type="button" id="post_position" value="<?php echo $translator->{'Save and close'} ?>"/></p>

</div>

<div id="book_position_dialogue" class="dialogue" style="width:750px; margin-left:-375px;">
	<div style="float:left;">
	
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

	<div style="float:right;">
		<img src="images/icons/close_dialogue.png" alt="" style="left:400px;" class="closeDialogue"/>
		<div style="width:400px; margin-top:20px;">
		
		</div>
		<div class="val"><p>
			<?php echo $translator->{'Currency'}?>
			<select class="values">
				<option value="SEK">SEK</option>
				<option value="USD">USD</option>
				<option value="GBP">GBP</option>
				<option value="EUR">EUR</option>
				<option value="PEN">PEN</option>
			</select>
			<p>
			<p style="font-weight:bold;">
				<span style="margin-left:15px;">Art. nr.</span>
				<span style="margin-left:10px;">Name</span>
				<span style="margin-left:95px;">Price</span>
				<span style="margin-left:20px;">Qty.</span>
				<span style="margin-left:20px;">Cost</span>
			</p>
		</div>
		
		<div class="position_chosen_articles" style="width:400px; height:200px; overflow-y:scroll; background-color:#eee; border:1px solid #ccc; overflow-x:hidden;">
			<div class="categoryarticles">
				<div class="title"></div>
			</div>
		</div>

		<div class="positionmoney" style="width:400px; height:115px; margin-top:80px; padding:5px; overflow-y:scroll; background-color:#eee; border:1px solid #ccc; overflow-x:hidden; ">
		<div class="total_price_row forposition"><?php echo $articles_cost.': <span class="priceTag">0</span> <span class="value_">'.$fair->get('default_currency')?></span></div>
		<div class="total_price_row"><?php echo $basic_cost_stand?>: <span class="standprice"></span> <span class="value_"><?php echo $fair->get('default_currency')?></span></div>
			<div class="customs">
			</div>
		</div>
		<div class="sum_for_position" style="padding:5px; background-color:#eee; border:1px solid #ccc; overflow-x:hidden;">
			<h2 style="color:#000;">Summary: <span class="summaryPrice"><?php echo $tot.'</span> <span class="value_">'.$fair->get('default_currency')?></span></h2>
		</div>
		<p style="text-align:center;"><input type="button" id="article_list_book" value="<?php echo $translator->{'Article list'} ?>" /></p>
	</div>
</div>

<div id="article_dialogue">
	<div style="width:790px; float:left; height:30px; padding:5px; border-radius:10px 10px 0px 0px; background-color:#128945;">
		<h2> <?php echo $translator->{'Article list'} ?> - <?php echo $name = (!empty($article_categories) == true) ?  $article_categories[0]['name'] : 'Empty list' ?></h2>
		<img src="images/icons/close_dialogue.png" alt="" style="float:right;"class="close"/>
		<input class="search" type="text" placeholder="<?php echo $translator->{'Search article'}?>" style="margin-top:8px; margin-right:10px;"></input>
	</div>
		<table>
			<thead>
				<tr style="height:50px; background-color:#bebebe;">
					<th style="width:15%;">Art.nr</th>
					<th style="width:50%;">Benämning</th>
					<th style="width:15%;">Pris</th>
					<th style="width:10%;">Antal</th>
					<th style="width:10%;">Total</th>
				</tr>
			</thead>
		</table>
	<ul>
		<?php foreach($article_categories as $articleCategory): ?>
		<li><h3><?php echo $articleCategory['CategoryName']?></h3>
			<ul>
				<li>
					<table style="overflow-x:hidden;">
						<tbody>
							<?php foreach($articles[$articleCategory['CategoryId']] as $article) : ?>
								<tr>
									<td class="artnr" style="width:15%;"><?php echo $article['ArticleNum']?></td>
									<td class="name" style="width:50%;"><?php echo $article['ArticleName']?></td>
									<td class="price" style="width:15%;"><?php echo $article['ArticlePrice']?></td>
									<td class="id" style="display:none"><?php echo $article['ArticleId']?></td>
									<td class="arts" style="width:10%;"><p class="numberOfArticles">0</p><div class="antalBox"><div class="raknare add"></div><div class="raknare dec"></div></div></td>
									<td class="totprice" style="width:10%;">0</td>
								</tr>
							<?php endforeach;?>
						</tbody>
					</table>
				</li>
			</ul>
		</li>
		<?php endforeach;?>
	</ul>
	<div style="width:100%; float:left; background-color:#a9a9a9; border-radius:0px 0px 10px 10px; ">
		<div style="float:left; width:117px; text-align:center; height:60px; text-align:center; border-right:2px solid #bebebe;">
			<p><?php echo $translator->{'Currency'}?>
			<br />
			<select class="valuta">
				<option value="SEK">SEK</option>
				<option value="USD">USD</option>
				<option value="GBP">GBP</option>
				<option value="EUR">EUR</option>
				<option value="PEN">PEN</option>
			</select></p>
			</div>
		
		<div style="float:left; width:50%; text-align:center;" class="totals">
			<p style="float:left; width:140px; margin-left:30px; font-weight:bold;"><?php echo $translator->{'Amount of articles'}?>
			<input  style="float:left;" type="text" class="totalamt" value="0" disabled></input></p>
			<p style="float:left; width:180px; margin-left:40px; font-weight:bold;"><?php echo $translator->{'Total article cost'}?>
			<input  style="float:left;" class="totalcost" type="text" value="0" disabled></input><span style="float:right; margin-top:2px;" class="selval"><?php echo $fair->get('default_currency')?></span></p>
		</div>
		<div style="float:left; width:25%; text-align:center;">
			<p style="float:right; margin:25px 0px 0px 0px;">
			<button class="cancel"> <?php echo $translator->{'Cancel'}?> </button>
			<button class="save"> <?php echo $translator->{'Save'}?> </button>
			</p>
		</div>
	</div>
</div>

<div id="more_info_dialogue" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>
	<h3></h3>
	<p class="info"></p>
	<h4 style="margin-bottom: 0px;"></h4>
	<p class="presentation" style="margin-top:0px;"></p>
	<p class="website_link"></p>
</div>
<?php
	if((userLevel() == 2 && userIsConnectedTo($fair->get('id'))) || userLevel() > 2) : ?>
		<div id="note_dialogue" class="dialogue">
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" />
		<h2></h2>
		<h3>Kommentarer för </h3>
		<div class="commentList" style="max-height:280px; margin-bottom:30px; overflow-y:scroll;">
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
<div id="m2price" style="display:none;"><?php echo $fair->get('price_per_m2')?></div>
<div id="custom_prices" style="display:none;">
	<?php foreach($custom_fees as $fee):?>
		<div class="total_price_row"><span class="name"><?php echo $fee['name'];?></span>: <span class="amt"><?php echo $fee['amount'];?></span></span> <span class="value_"><?php echo $fair->get('default_currency')?></span></div>
	<?php endforeach?>
</div>
<div id="reserve_position_dialogue" class="dialogue" style="width:750px; margin-left:-375px;">
	<div style="float:left">
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

	<div style="float:right;">
		<img src="images/icons/close_dialogue.png" alt="" style="left:400px;" class="closeDialogue"/>
		<div style="width:400px; margin-top:20px;">
		
		</div>
		<div class="val"><p>
			<?php echo $translator->{'Currency'}?>
			<select class="values">
				<option value="SEK">SEK</option>
				<option value="USD">USD</option>
				<option value="GBP">GBP</option>
				<option value="EUR">EUR</option>
				<option value="PEN">PEN</option>
			</select>
			<p>
			<p style="font-weight:bold;">
				<span style="margin-left:15px;">Art. nr.</span>
				<span style="margin-left:10px;">Name</span>
				<span style="margin-left:95px;">Price</span>
				<span style="margin-left:20px;">Qty.</span>
				<span style="margin-left:20px;">Cost</span>
			</p>
		</div>
		
		<div class="position_chosen_articles" style="width:400px; height:200px; overflow-y:scroll; background-color:#eee; border:1px solid #ccc; overflow-x:hidden;">
			<div class="categoryarticles">
				<div class="title"></div>
			</div>
		</div>

		<div class="positionmoney" style="width:400px; height:115px; margin-top:80px; padding:5px; overflow-y:scroll; background-color:#eee; border:1px solid #ccc; overflow-x:hidden; ">
		<div class="total_price_row forposition"><?php echo $articles_cost.': <span class="priceTag">0</span> <span class="value_">'.$fair->get('default_currency')?></span></div>
		<div class="total_price_row"><?php echo $basic_cost_stand?>: <span class="standprice"></span> <span class="value_"><?php echo $fair->get('default_currency')?></span></div>
			<div class="customs">
			</div>
		</div>
		<div class="sum_for_position" style="padding:5px; background-color:#eee; border:1px solid #ccc; overflow-x:hidden;">
			<h2 style="color:#000;">Summary: <span class="summaryPrice"><?php echo $tot.'</span> <span class="value_">'.$fair->get('default_currency')?></span></h2>
		</div>
		<p style="text-align:center;"><input type="button" id="article_list_reserve" value="<?php echo $translator->{'Article list'} ?>" /></p>
	</div>
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
