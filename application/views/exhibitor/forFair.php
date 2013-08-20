<script type="text/javascript" src="js/tablesearch.js"></script>
<script type="text/javascript">
	$(document).ready(function(){
		var headerd = $('.tblHeader');
		headerd.css('display', 'none');
		setTimeout(function(){
			anpassaTabeller();
			for(var i = 0; i<3; i++){
				var tblarr = new Array('booked', 'connected', 'canceled');
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
				if(i == 2){headerd.css('display', 'block');}
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
		} else if(tbl == "connected"){
			exportTableToExcel(rowArray, colArray, 2);
		}  else if(tbl == "canceled"){
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
		window.location = '<?php echo BASE_URL."exhibitor/exportForFair"?>'+finishedUrl;
	}
	
	function anpassaTabeller(){
		var tbl1width = $('#booked').width();
		var tbl2width = $('#connected').width();
		var tbl3width = $('#canceled').width();

		$('.tbl1').css('max-width', tbl1width);
		$('.tbl2').css('max-width', tbl2width);
		$('.tbl3').css('max-width', tbl3width);
	}
</script>
<h1><?php echo $headline; ?></h1>
<p><a class="button add" href="administrator/newExhibitor"><?php echo $create_link; ?></a></p>
<h2 class="tblsite"><?php echo $table_exhibitors ?></h2>
<?php if(count($users) > 0) : ?>
<div class="tbld tbl1">
	<a onclick="prepareTable('booked')"><button style="float:left; margin-top:17px;"><?php echo $export?></button></a>
	<div class="tblHeader" id="hbooked">
		<ul class="special">
			<li><div class="tblrow1"><?php echo $th_company;?></div><input type="checkbox" value="1" checked></input></li>
			<li><div class="tblrow1"><?php echo $th_name; ?></div><input type="checkbox" value="2" checked></input></li>
			<li><div class="tblrow1"><?php echo $th_fairs; ?></div><input type="checkbox" value="3" checked></input></li>
			<li><div class="tblrow1"><?php echo $th_bookings; ?></div><input type="checkbox" value="4" checked></input></li>
			<li><div class="tblrow1"><?php echo $th_last_login; ?></div><input type="checkbox" value="5" checked></input></li>
			<li><div class="tblrow1"></div><input onclick="multiCheck('booked')" type="checkbox" style="padding-top:15px;" checked></input></li>
		</ul>
	</div>
	<div class="scrolltbl">
		<table class="std_table" id="booked">
		<?php if (userLevel() > 2): ?>

		<?php endif; ?>
			<thead>
				<tr>
					<th><?php echo $th_company ?></th>
					<th><?php echo $th_name ?></th>
					<th><?php echo $th_fairs ?></th>
					<th><?php echo $th_bookings ?></th>
					<th><?php echo $th_last_login ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($users as $user): ?>
					<tr>
						<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>"><?php echo $user->get('company'); ?></a></td>
						<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>"><?php echo $user->get('name'); ?></a></td>
						<td class="center"><?php echo $user->get('fair_count');?></td>
						<td class="center"><?php echo $user->get('ex_count');?></td>
						<td><?php echo date('d/m/y', $user->get('last_login'));?></td>
						<td><input type="checkbox" id="<?php echo $user->get('id'); ?>" checked></input></td>
						<!--<td class="center"><a href="user/edit/<?php echo $user->get('id') ?>"><img src="images/icons/pencil.png" alt="" title="<?php echo $translator->{'Edit'} ?>"/></a></td>
						<td class="center"><a onclick="return confirm('<?php echo $translator->{'Really delete?'} ?>');" href="exhibitor/deleteAccount/<?php echo $user->get('id') ?>"><img src="images/icons/delete.png" alt=""/></a></td>-->
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
<?php else : ?>
	<div class="tbld tbl2">
		<p>Det finns inga inbokade utställare ännu.</p>
	</div>
<?php endif;?>

<h2 class="tblsite"><?php echo $table_connected ?></h2>
<?php if(count($connected) > 0 ) : ?>
<div class="tbld tbl2">
<a onclick="prepareTable('connected')"><button style="float:left; margin-top:17px;"><?php echo $export?></button></a>
<div class="tblHeader" id="hconnected">
	<ul>
		<li><div class="tblrow1"><?php echo $th_company; ?></div><input type="checkbox" value="1" checked></input></li>
		<li><div class="tblrow1"><?php echo $th_name; ?></div><input type="checkbox" value="2" checked></input></li>
		<li><div class="tblrow1"><?php echo $th_fairs; ?></div><input type="checkbox" value="3" checked></input></li>
		<li><div class="tblrow1"><?php echo $th_last_login ;?></div><input type="checkbox" value="4" checked></input></li>
		<li><div class="tblrow1"></div><div style="padding-top:19px;"></div></li>
		<li><div class="tblrow1"></div><input onclick="multiCheck('connected')" type="checkbox" checked></input></li>
	</ul>
</div>
<div class="scrolltbl">
<table class="std_table"id="connected">

	<thead style="height:22px;">
		<tr>
			<th><?php echo $th_company ?></th>
			<th><?php echo $th_name ?></th>
			<th><?php echo $th_fairs ?></th>
			<!--<th><?php echo $th_bookings ?></th>-->
			<th><?php echo $th_last_login ?></th>
			<th><?php echo $th_copy; ?></th>
			<th></th>
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
				<td><input type="checkbox" id="<?php echo $user->get('id'); ?>" checked></input></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
</div>
</div>
<?php else : ?>
	<div class="tbld">
		<p>Det finns inga anslutna utställare.</p>
	</div>
<?php endif;?>
