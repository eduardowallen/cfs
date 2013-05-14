<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $fair->get('name'); ?></h1>
<script type="text/javascript">
	$(document).ready(function(){
		var headerd = $('.tblHeader');
		headerd.css('display', 'none');
		setTimeout(function(){
			
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
				$('#h'+tblarr[i]+' > thead').css('visibility', 'hidden');
				if(i == 2){headerd.css('display', 'block');}
			}
		}, 500);
	});
</script>

<?php if ($hasRights): ?>
<?php if(count($positions) > 0) : ?>
<div class="tbld">
<h2 class="tblsite" style="margin-top:20px"><?php echo $headline; ?></h2>
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
<div class="scrolltbl">
<table class="std_table" id="booked" style="width: 100%; padding-right: 16px;">
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
<?php endif; ?>
<div class="tbld">
<?php if(count($rpositions) > 0) : ?>

<h2 class="tblsite" style="margin-top:20px"><?php echo $rheadline; ?></h2>

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
<div class="scrolltbl">
<table class="std_table" id="reserved" style="width: 100%; padding-right: 16px;">
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
<?php endif; ?>



<?php if(count($prelpos) > 0) : ?>
<div class="tbld">
<h2 class="tblsite" style="margin-top:20px"><?php echo $prel_table; ?></h2>
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
<div class="scrolltbl" style="width:100%;">
<table class="std_table" id="prem" style="width: 100%; padding-right: 16px;">
<thead">
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
</tbody>
</table>
</div>
</div>
<?php endif; ?>

<?php else: ?>

<p>Du är inte behörig att administrera den här mässan.</p>

<?php endif; ?>
