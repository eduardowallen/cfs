<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>
	<br />
	<h1><?php echo $headline; ?></h1>
	<style>
		input[type=checkbox] {
			opacity:1;
			position:initial;
		}
		table#newRow {
		  display: none;
		}
	</style>
<script type="text/javascript">
			var rowCache;
			var tableCache;
			
			$(document).ready(function() {
				var dt = $('#example').dataTable();
				dt.fnDestroy();
			});		

			$(document).ready(function() {
				var dt = $('#example2').dataTable();
				dt.fnDestroy();
			});
			
			$(document).ready(function() {
				rowCache = [];
				tableCache = [];

				var example = $('#example').DataTable({
		
					rowReorder: true,
					columns: [{
					  data: 'name'
					}, {
					  data: 'invoice_no'
					}]
				});
				example.on('mousedown', 'tbody tr', function () {
				    var $row = $(this);
				    var addRow = example.row($row);
				    rowCache.push(addRow);
				    tableCache.push($(this).parents('table').attr('id'));
				});

				var example2 = $('#example2').DataTable({

					rowReorder: true,
					columns: [{
					  data: 'name'
					}, {
					  data: 'invoice_no'
					}]
				});
				example2.on('mousedown', 'tbody tr', function () {
					//var $row = $(this);
					//rowCache.push(example2.row($row));
				    var $row = $(this);
				    var addRow = example2.row($row);
				    rowCache.push(addRow);
				    tableCache.push($(this).parents('table').attr('id'));
				    console.log(tableCache[0]);
				    //example.row.add(addRow.data()).draw();
				    //addRow.remove().draw();
				});
			});


			function mouseUp(event)	{
				
				//var addRow = rowCache;
				//var addRow = example.row(rowCache);
				//example.row.add(addRow.data()).draw();
				//addRow.remove().draw();
				console.log('tableCache '+tableCache);	
				console.log('TARGET ID'+event.target);
				var origin_id = tableCache[0];
				var target_id = $(event.target).parents('table').attr('id');
				console.log('Origin ID: '+origin_id+'   Target ID: '+target_id);/*
				if (target_id != origin_id) {
					console.log('target_table: ' + rowCache[0]);
					rowCache[0].row.add(addRow.data()).draw();
    				addRow.remove().draw();
    			}*/
				rowCache = [];
				tableCache = [];
			}
			
			$(document).ready(function() {
				$('body').on('mouseenter', 'input', function() {
					$('.btn').prop('disabled', true);
				});
				$('body').on('mouseout', 'input', function() {
					$('.btn').prop('disabled', false);
				});
        $('body').mouseup(mouseUp);
			});	
</script>
<br>
<br>
<h1>
 table1
</h1><br>
<br>
<table id="example" class="display" width="100%" cellspacing="0">
  <thead>
    <tr>
      <th>Event name</th>
      <th>Invoice number</th>
    </tr>
  </thead>
  <tbody>
  	<tr>
  		<td>Sannamarken 2016</td>
  		<td>66</td>
  	</tr>
  </tbody>
</table>

<br>
<br>
<h1>
 table 2
</h1><br>
<br>
<table id="example2" class="display" width="100%" cellspacing="0">
  <thead>
    <tr>
      <th>Event name</th>
      <th>Invoice number</th>
    </tr>
  </thead>
  <tbody>
  	<tr>
  		<td>Sannamarken 2017</td>
  		<td>360</td>
  	</tr>
  </tbody>
</table>
