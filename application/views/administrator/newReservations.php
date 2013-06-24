<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $fair->get('name'); ?></h1> 
<script type="text/javascript">
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
			resizeNewRes();
		}, 500);
	});
</script>

<?php if ($hasRights): ?>

<div class="tbld">
<h2 class="tblsite" style="margin-top:20px"><?php echo $headline; ?><a hid="0" style="cursor:pointer;" onclick="hider(this,'booked')"><img style="width:30x; height:15px; margin-left:20px;" src="<?php echo BASE_URL."public/images/icons/min.png";?>" alt="" /></a></h2>
<?php if(count($positions) > 0){ ?>
<div class="tblHeader" id="hbooked">
	<ul>
		<li><?php echo $tr_pos; ?></li>
		<li><?php echo $tr_area; ?> (m<sup>2</sup>)</li>
		<li><?php echo $tr_booker; ?></li>
		<li><?php echo $tr_field; ?></li>
		<li><?php echo $tr_time; ?></li>
		<li><?php echo $tr_message; ?></li>
		<li><?php echo $tr_view; ?></li>
		<li><?php echo $tr_delete; ?></li>
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
	</tr>
</thead>
<tbody>
<?php //$page = 1; $count = 0;?>
<?php foreach($positions as $pos):?>
	<tr <?php //if($page>1){echo 'style="display:none;"';}?>>
		<td><?php echo $pos['name']; ?></td>
		<td class="center"><?php echo $pos['area']; ?></td>
		<td class="center"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>"><?php echo $pos['company']; ?></a></td>
		<td class="center"><?php echo $pos['commodity']; ?></td>
		<td ><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
		<td title="<?php echo $pos['arranger_message']; ?>"><?php echo substr($pos['arranger_message'], 0, 50); ?></td>
		<td class="center">
		<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position']; ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
		<td class="center">
			<a href="<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>" title="<?php echo $tr_view; ?>">
				<img src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_view; ?>" />
			</a>
		</td>
	</tr>
<?php //$count +=1; ?>
<?php //if($count == 5):
	//$page +=1; $count = 0;
//endif; ?>
<?php endforeach; ?>
</tbody>
</table>
<?php //$d = 1; ?>
<?php //if(count($positions) > 5){ ?>
<!--<p class="pagercomment"> Sida : </p>
<div id="pager1" class="pager">-->
<?php /*for($i=0; $i<count($positions); $i=$i+5): ?>
	<?php 
	
	if($d == 1){echo '<p style="font-weight:bold; color:#128913;" class="'.$d.'"onclick="showPage('.$d.', \'booked\', \'pager1\')">['.$d.']</p>'; }
	else {echo '<p class="'.$d.'"onclick="showPage('.$d.', \'booked\', \'pager1\')">'.$d.'</p>';}
	$d +=1;
	*/?>

<!--</div>-->
	</div>
</div>
<?php } else { ?>
<p style="width:100%; float:left;"> <?php echo $booked_notfound?> </p>
<?php }?>



<div class="tbld">
	<h2 class="tblsite" style="margin-top:20px"><?php echo $rheadline; ?><a hid="0" style="cursor:pointer;" onclick="hider(this,'reserved')"><img style="width:30x; height:15px; margin-left:20px;" src="<?php echo BASE_URL.'public/images/icons/min.png';?>" alt="" /></a></h2>
	<?php if(count($rpositions) > 0){?>
	<div class="tblHeader" id="hreserved">
		<ul>
			<li><?php echo $tr_pos; ?></li>
			<li><?php echo $tr_area; ?> (m<sup>2</sup>)</li>
			<li><?php echo $tr_booker; ?></li>
			<li><?php echo $tr_field; ?></li>
			<li><?php echo $tr_time; ?></li>
			<li><?php echo $tr_message; ?></li>
			<li><?php echo $tr_view; ?></li>
			<li><?php echo $tr_deny; ?></li>
			<li><?php echo $tr_approve; ?></li>
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
				<th><?php echo $tr_view; ?></th>
				<th><?php echo $tr_deny; ?></th>
				<th><?php echo $tr_approve; ?></th>
			</tr>
		</thead>
		<tbody>
		<?php //$page = 1; $count = 0;?>
		<?php foreach($rpositions as $pos): ?>
			<tr>
				<td><?php echo $pos['name']; ?></td>
				<td class="center"><?php echo $pos['area']; ?></td>
				<td class="center"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>"><?php echo $pos['company']; ?></a></td>
				<td class="center"><?php echo $pos['commodity']; ?></td>
				<td><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
				<td title="<?php echo $pos['arranger_message']; ?>"><?php echo substr($pos['arranger_message'], 0, 50); ?></td>
				<td class="center">
					<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position']; ?>" title="<?php echo $tr_view; ?>">
						<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
					</a>
				</td>
				<td class="center">
					<a href="<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>" title="<?php echo $tr_delete; ?>">
						<img src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_delete; ?>" />
					</a>
				</td>
				<td class="center">
					<a href="<?php echo BASE_URL.'administrator/approveReservation/'.$pos['position'] ?>" title="<?php echo $tr_approve; ?>">
						<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_approve; ?>" />
					</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
	</div>
</div>

<?php } else { ?>
<p style="width:100%; float:left;"> <?php echo $reserv_notfound?> </p>
<?php }?>


<div class="tbld">
	<h2 class="tblsite" style="margin-top:20px"><?php echo $prel_table; ?><a hid="0" style="cursor:pointer;" onclick="hider(this,'prem')"><img style="width:30x; height:15px; margin-left:20px;" src="<?php echo BASE_URL."public/images/icons/min.png";?>" alt="" /></a></h2>
	<?php if(count($prelpos) > 0){ ?>
	<div class="tblHeader"  id="hprem">
		<ul>
			<li><?php echo $tr_pos; ?></li>
			<li><?php echo $tr_area; ?> (m<sup>2</sup>)</li>
			<li><?php echo $tr_booker; ?></li>
			<li><?php echo $tr_field; ?></li>
			<li><?php echo $tr_time; ?></li>
			<li><?php echo $tr_message; ?></li>
			<li><?php echo $tr_view; ?></li>
			<li><?php echo $tr_deny; ?></li>
			<li><?php echo $tr_approve; ?></li>
			<li><?php echo $tr_reserve; ?></li>
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
			</tr>
		</thead>
		<tbody>
		<?php foreach($prelpos as $pos): ?>
			<tr id="prem-<?php echo $page.'-'.$count; ?>" <?php if($page>1){echo 'style="display:none;"';}?>>
				<td><?php echo $pos['name']; ?></td>
				<td class="center"><?php echo $pos['area']; ?></td>
				<td class="center"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>"><?php echo $pos['company']; ?></a></td>
				<td class="center"><?php echo $pos['commodity']; ?></td>
				<td class="center"><?php echo date('d-m-Y H:i:s', $pos['booking_time']); ?></td>
				<td title="<?php echo $pos['arranger_message']; ?>"><?php echo substr($pos['arranger_message'], 0, 50); ?></td>
				<td class="center">
					<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'] ?>" title="<?php echo $tr_view; ?>">
						<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" alt="<?php echo $tr_view; ?>" />
					</a>
				</td>
				<td class="center">
					<a href="<?php echo BASE_URL.'administrator/newReservations/deny/'.$pos['id'] ?>" title="<?php echo $tr_deny; ?>">
						<img src="<?php echo BASE_URL; ?>images/icons/delete.png" alt="<?php echo $tr_deny; ?>" />
					</a>
				</td>
				<td class="center">
					<a href="<?php echo BASE_URL.'administrator/newReservations/approve/'.$pos['id'] ?>" title="<?php echo $tr_approve; ?>">
						<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_approve; ?>" />
					</a>
				</td>
				<td class="center">
					<a href="<?php echo BASE_URL.'administrator/reservePrelBooking/'.$pos['id'] ?>" title="<?php echo $tr_reserve; ?>">
						<img src="<?php echo BASE_URL; ?>images/icons/add.png" alt="<?php echo $tr_reserve; ?>" />
					</a>
				</td>

			</tr>
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
