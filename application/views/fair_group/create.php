	<h1><?php echo $headline; ?></h1>
	<style>
		input[type=checkbox] {
			opacity:1;
			position:initial;
		}
		#events {
        margin-bottom: 1em;
        padding: 1em;
        background-color: #f6f6f6;
        border: 1px solid #999;
        border-radius: 3px;
        height: 100px;
        overflow: auto;
    }
	</style>
<script type="text/javascript">

	function moveRows(fromTable, toTable){
	  var events = $('#events');
	  var count = fromTable.rows({selected:true}).count();
		fromTable.rows({selected:true}).every( function ( rowIdx, tableLoop, rowLoop ) {
			toTable.row.add(this.data()).draw();
		});
		fromTable.rows({selected:true}).remove().draw();
		if (count > 0) {
			events.prepend( '<div>Moved '+count+' rows from Events table to Group table</div>' );
		}
	}
$(document).ready(function() {

	$('input[type="submit"]').click(function(e) {
		if ($('#groupname').val() != '') {
			$('#groupname').addClass('input_ok');
			$('#groupname').removeClass('input_error');
		} else {
			$('#groupname').addClass('input_error');
			$('#groupname').removeClass('input_ok');
			showInfoDialog('<?php echo uh($translator->{"You must provide a name for the group."}); ?>', '<?php echo uh($translator->{"Group name missing"}); ?>');
			return;
		}

		var invoice_ids = [];
		var selected_fairs = '';
		var share_invoice = '';
		var count = 0;
		group_table.rows().every( function (rowIdx, tableLoop, rowLoop ) {
			var data = this.cell(rowIdx, -1).node();
			var checked = $(data).find('input').prop('checked');
			selected_fairs += '&selected_fairs[]=' + this.cell(rowIdx, 0).data();
			if (checked == true) {
				invoice_ids[count] = this.cell(rowIdx, 2).data();
				share_invoice += '&share_invoice[]=1';
			} else {
				invoice_ids[count] = 0;
				share_invoice += '&share_invoice[]=0';
			}

			count++;
		});

		invoice_no = Math.max.apply(Math, invoice_ids);
		if ($('#invoice_no').val() < invoice_no) {
			$('#invoice_no').addClass('input_error');
			$('#invoice_no').removeClass('input_ok');
			showInfoDialog('<?php echo uh($invoice_explanation_text); ?>', '<?php echo uh($invoice_explanation_title); ?>');
			return;
		} else {
			$('#invoice_no').addClass('input_ok');
			$('#invoice_no').removeClass('input_error');
		}

		var dataString = 'newGroup=1'
					+	'&groupname=' + encodeURIComponent($("#groupname").val())
					+	'&invoice_no=' + $('#invoice_no').val()
					+	selected_fairs
					+	share_invoice;
					
		$.ajax({
			url: 'ajax/fairGroup.php',
			type: 'POST',
			data: dataString,
			success: function(response) {
				window.location = '/fairGroup/groups';
			}
		});
	});
	var group_table = $('#group_table').DataTable({
		dom: 'frtB',
		createdRow: function(row, data, dataIndex) {
			$(row).attr('id', 'row-' + dataIndex);
		},
		'columns': [
			{
				"render": function(data, type, row, meta){
					return row[0];
				}
			},
			{
				"render": function(data, type, row, meta){
					return row[1];
				}
			},
			{
				"render": function(data, type, row, meta){
					return row[2];
				}
			},
			{
				"render": function(data, type, row, meta){
					var checkbox = $("<input/>",{
						"type": "checkbox",
						"value": "1"
					});
					if(row[3] === "1"){
						checkbox.attr("checked", "checked");
						checkbox.addClass("checkbox_checked");
					} else {
						checkbox.removeAttr("checked");
						checkbox.addClass("checkbox_unchecked");
					}
					return checkbox.prop("outerHTML")
				}
			}
		],
		/*rowReorder: {
		  dataSrc: 0,
		},*/
		searching: false,
		select: {
			style: 'multi',
			blurable: true
		},
		buttons: [
			{
				text: lang.remove_from_group,
				className: 'btn-primary',
				action: function () {
					moveRows(group_table, events_table);
				}
			}
		]
    });
	var events_table = $('#events_table').DataTable({
		dom: 'frtB',
		createdRow: function(row, data, dataIndex) {
			$(row).attr('id', 'row-' + dataIndex);
		},
		'columns': [
			{
				"render": function(data, type, row, meta){
					return row[0];
				}
			},
			{
				"render": function(data, type, row, meta){
					return row[1];
				}
			},
			{
				"render": function(data, type, row, meta){
					return row[2];
				}
			},
			{
				"render": function(data, type, row, meta){
					var checkbox = $("<input/>",{
						"type": "checkbox"
					});
					if(row[3] === "1"){
						checkbox.attr("checked", "checked");
						checkbox.addClass("checkbox_checked");
					} else {
						checkbox.removeAttr("checked");
						checkbox.addClass("checkbox_unchecked");
					}
					return checkbox.prop("outerHTML")
				}
			}
		],
		/*rowReorder: {
		  dataSrc: 0,
		},*/
		searching: false,
		select: {
			style: 'multi',
			blurable: true
		},
		buttons: [
			{
				text: lang.add_to_group,
				className: 'btn-primary',
				action: function () {
					moveRows(events_table, group_table);
				}
			}
		]
    });
	events_table.columns([0, -1]).visible( false );
	group_table.columns(0).visible( false );
	group_table.buttons().container().css('display', 'inline');
	events_table.buttons().container().css('display', 'inline');
	$('.checkbox').change(function () {
		$(this).prop("checked") ? $(this).val("1") : $(this).val("0");
	});

});
</script>
<fieldset class="floatleft">
<label for="groupname"><?php echo $name_label; ?> *</label>
<input type="text" id="groupname" name="groupname"/>
</fieldset>
<fieldset style="padding-left:2em; display:inline;">
<label for="invoice_no"><?php echo $invoice_no_label; ?> *</label>
<input type="number" min="1" id="invoice_no" name="invoice_no"/>
</fieldset>
<br>
<br>
<br>
<fieldset style="width:49%; display:inline;">
<h1 class="nopadding"><?php echo uh($group_headline); ?></h1>
<?php if (isset($owner)) { ?>
<table id="group_table" class="display table table-striped table-border" width="100%" cellspacing="0">
  <thead class="greenBG">
    <tr>
    	<th>ID</th>
      <th><?php echo uh($th_event_name); ?></th>
      <th><?php echo uh($th_invoice_no); ?></th>
      <th><?php echo uh($th_share_invoice_no); ?></th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>
<?php } ?>
</fieldset>
<fieldset style="width:50%; display:inline; padding-left:3em">
<h1 class="nopadding"><?php echo uh($events_headline); ?></h1>
<?php if (isset($owner)) { ?>
<table id="events_table" class="display table table-striped table-border" width="100%" cellspacing="0">
  <thead class="greenBG">
    <tr>
    	<th>ID</th>
      <th><?php echo uh($th_event_name); ?></th>
      <th><?php echo uh($th_invoice_no); ?></th>
      <th><?php echo uh($th_share_invoice_no); ?></th>
    </tr>
  </thead>
  <tbody>
<?php if ($fairs_available) {
			foreach ($fairs_available as $fair) { ?>
  	<tr>
  		<td><?php echo uh($fair->get('id')); ?></td>
  		<td><?php echo uh($fair->get('name')); ?></td>
  		<td><?php echo uh($fair->loadRealInvoiceId()); ?></td>
  		<td></td>
  	</tr>
<?php } } ?>
  </tbody>
</table>
<?php } ?>
</fieldset>
<br>
<input type="submit" name="save" value="<?php echo $save_label; ?>" class="greenbutton bigbutton" style="margin-left:0;" />
<input type="button" name="cancel" class="redbutton bigbutton" value="<?php echo uh($cancel_label); ?>" onclick="location.href='<?php echo BASE_URL; ?>fairGroup/groups'"/>