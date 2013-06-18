<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $headline; ?></h1>

<?php if (userLevel() > 2): ?>
<script type="text/javascript">
	function sendRequest(){
		var count = 0;
		var rows = '/';
		$('tbody > tr').each(function(i){
			var checkBox = $(this).children(':last').children(':first');
			
			if(checkBox.prop('checked')){
				var cBoxId = checkBox.attr('id').replace("exp_row_","");
				rows+=cBoxId+';';
				count++;
			}	
		});
		if(count < 1){
			alert("Välj minst en rad att exportera!");
		} else {
			var countCol = 0;
			var data = '';

			if($('#exp_st').prop('checked')){
				data = data + "/1"; countCol++;
			} else {
				data = data + "/0";
			}

			if($('#exp_nm').prop('checked')){
				data = data + "/1"; countCol++;
			} else {
				data = data + "/0";
			}
			if($('#exp_cp').prop('checked')){
				data = data + "/1"; countCol++;
			} else {
				data = data + "/0";
			}
			if($('#exp_ad').prop('checked')){
				data = data + "/1"; countCol++;
			} else {
				data = data + "/0";
			}
			if($('#exp_br').prop('checked')){
				data = data + "/1"; countCol++;
			} else {
				data = data + "/0";
			}
			if($('#exp_ph').prop('checked')){
				data = data + "/1"; countCol++;
			} else {
				data = data + "/0";
			}
			if($('#exp_co').prop('checked')){
				data = data + "/1"; countCol++;
			} else {
				data = data + "/0";
			}
			if($('#exp_em').prop('checked')){
				data = data + "/1"; countCol++;
			} else {
				data = data + "/0";
			}
			if($('#exp_we').prop('checked')){
				data = data + "/1"; countCol++;
			} else {
				data = data + "/0";
			}
			if(countCol > 0){
				document.location.href='exhibitor/export/<?php echo $fairId;?>'+data+rows;
			} else {
				alert('Välj minst en kolumn att exportera!');
			}
		}
	}
</script>
<input type="button" value="<?php echo $export_button ?>" class="floatright" onclick="sendRequest()"/>

<?php endif; ?>
<style>
	.std_table tbody{border-top:0px;}
	.std_table tr.special th{background:#fff; border-left:1px solid #000; border-right:1px solid #000;}
</style>
<table class="std_table">
	<tr class="special">
		<th></th>
		<th style="border:0px;"><input type="checkbox" id="exp_st" value="1"></input></th>
		<th><input type="checkbox" id="exp_nm" value="2"></input></th>
		<th><input type="checkbox" id="exp_cp" value="3"></input></th>
		<th><input type="checkbox" id="exp_ad" value="5"></input></th>
		<th><input type="checkbox" id="exp_br" value="6"></input></th>
		<th><input type="checkbox" id="exp_ph" value="7"></input></th>
		<th><input type="checkbox" id="exp_co" value="8"></input></th>
		<th><input type="checkbox" id="exp_em" value="9"></input></th>
		<th><input type="checkbox" id="exp_we" value="10"></input></th>
		<th></th>
		<th></th>
	</tr>
	<thead>
		
		<tr>
			
			<th><?php echo $th_status; ?></th>
			<th><?php echo $th_name; ?></th>
			<th><?php echo $th_company; ?></th>
			<th><?php echo $th_address; ?></th>
			<th><?php echo $th_branch; ?></th>
			<th><?php echo $th_phone; ?></th>
			<th><?php echo $th_contact; ?></th>
			<th><?php echo $th_email; ?></th>
			<th><?php echo $th_website; ?></th>
			<th><?php echo $th_view; ?></th>
			<?php if (userLevel() > 0): ?>
			<th></th>
			<th></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($exhibitors as $pos): ?>
		<tr>		
			
			<td><?php echo ($pos['posstatus'] == 2 ? 'booked' : ($pos['posstatus'] == 1 ? 'reserved' : '')); ?></td>
			<td class="center"><?php echo $pos['posname']; ?></td>
			<td class="center"><?php echo $pos['company']; ?></td>
			<td class="center"><?php echo $pos['address']; ?></td>
			<td class="center">
			<?php
				$commodity = $pos['commodity'];
				echo ( empty( $commodity ) ) ? $pos['excommodity'] : $pos['commodity'] ;
			?>
			</td>
			<td class="center"><?php echo $pos['phone1']; ?></td>
			<td class="center"><a href="exhibitor/profile/<?php echo $pos['id']; ?>"><?php echo $pos['name']; ?></a></td>
			<td class="center"><?php echo $pos['email']; ?></td>
			<td class="center"><a target="_blank" href="<?php echo (stristr($pos['website'], 'http://') ? $pos['website'] : 'http://' . $pos['website']); ?>"><?php echo $pos['website']; ?></a></td>
			<td class="center"><a href="mapTool/map/<?php echo $pos['fair'].'/'.$pos['position'].'/'.$pos['posmap']; ?>"><img src="images/icons/map_go.png" alt=""/></a></td>
			<?php if (userLevel() > 0): ?>
			<td class="center"><a href="exhibitor/profile/<?php echo $pos['id']; ?>"><img src="images/icons/user.png" alt=""/></a></td>
			<?php endif; ?>
			<td><input type="checkbox" id="exp_row_<?php echo $pos['position']?>" checked></input></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
