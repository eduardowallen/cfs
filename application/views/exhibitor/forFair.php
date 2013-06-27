<script type="text/javascript" src="js/tablesearch.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		var headerd = $('.tblHeader');
		headerd.css('display', 'none');
		setTimeout(function(){
			anpassaTabeller();
			for(var i = 0; i<3; i++){
				var tblarr = new Array('booked', 'connected');
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
	function anpassaTabeller(){
		var tbl1width = $('#booked').width();
		var tbl2width = $('#connected').width();
		
		$('.tbl1').css('max-width', tbl1width);
		$('.tbl2').css('max-width', tbl2width);
	}
</script>
<h1><?php echo $headline; ?></h1>
<p><a class="button add" href="administrator/newExhibitor"><?php echo $create_link; ?></a></p>

<h2 class="tblsite"><?php echo $table_exhibitors ?></h2>
<div class="tbld tbl1">
<a href="<?php echo BASE_URL.'exhibitor/exportForFair/'.$_SESSION['user_fair'].'/1'?>"><button style="float:right;"><?php echo $export?></button></a>
<div class="tblHeader" id="hbooked">
	<ul>
		<li><?php echo $th_company; ?></li>
		<li><?php echo $th_name; ?></li>
		<li><?php echo $th_fairs; ?></li>
		<li><?php echo $th_bookings; ?></li>
		<li><?php echo $th_last_login; ?></li>
	</ul>
</div>
<div class="scrolltbl">
<table class="std_table" id="booked">
	<thead>
		<tr>
			<th><?php echo $th_company ?></th>
			<th><?php echo $th_name ?></th>
			<th><?php echo $th_fairs ?></th>
			<th><?php echo $th_bookings ?></th>
			<th><?php echo $th_last_login ?></th>
			<!--<th><?php echo $th_edit ?></th>
			<th><?php echo $th_delete ?></th>-->
		</tr>
	</thead>
	<tbody>
		<?php foreach ($users as $user): ?>
		<tr>
			<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>"><?php echo $user->get('company'); ?></a></td>
			<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>"><?php echo $user->get('name'); ?></a></td>
			<td class="center"><?php echo $user->get('fair_count'); ?></td>
			<td class="center"><?php echo $user->get('ex_count'); ?></td>
			<td><?php echo date('d/m/y', $user->get('last_login')); ?></td>
			<!--<td class="center"><a href="user/edit/<?php echo $user->get('id') ?>"><img src="images/icons/pencil.png" alt="" title="<?php echo $translator->{'Edit'} ?>"/></a></td>
			<td class="center"><a onclick="return confirm('<?php echo $translator->{'Really delete?'} ?>');" href="exhibitor/deleteAccount/<?php echo $user->get('id') ?>"><img src="images/icons/delete.png" alt=""/></a></td>-->
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</div>
</div>
<h2 class="tblsite"><?php echo $table_connected ?></h2>

<div class="tbld tbl2">
<a href="<?php echo BASE_URL.'exhibitor/exportForFair/'.$_SESSION['user_fair'].'/2'?>"><button style="float:right; "><?php echo $export?></button></a>
<div class="tblHeader" id="hconnected">
	<ul>
		<li><?php echo $th_company; ?></li>
		<li><?php echo $th_name; ?></li>
		<li><?php echo $th_fairs; ?></li>
		<li><?php echo $th_bookings; ?></li>
		<li><?php echo $th_last_login; ?></li>
	</ul>
</div>
<div class="scrolltbl">
<table class="std_table"id="connected">
	<thead>
		<tr>
			<th><?php echo $th_company ?></th>
			<th><?php echo $th_name ?></th>
			<th><?php echo $th_fairs ?></th>
			<!--<th><?php echo $th_bookings ?></th>-->
			<th><?php echo $th_last_login ?></th>
			<th><?php echo $th_copy; ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($connected as $user): ?>
		<tr>
			<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>"><?php echo $user->get('company'); ?></a></td>
			<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>"><?php echo $user->get('name'); ?></a></td>
			<td class="center"><?php echo $user->get('fair_count'); ?></td>
			<!--<td class="center"><?php echo $user->get('ex_count'); ?></td>-->
			<td><?php echo date('d/m/y', $user->get('last_login')); ?></td>
			<td class="center"><a href="exhibitor/forFair/copy/<?php echo $user->get('id'); ?>"><img src="images/icons/user_go.png" alt=""/></a></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</div>
</div>
