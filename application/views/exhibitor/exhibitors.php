<?php
  global $translator;
  
  // Create an array used in generating the popup form when exporting data
  // Probably will be moved to ExhibitorControlled to be re-used to validate the received export request
  $column_info = array(
      $translator->{"Select all:"}." ".$translator->{"Company"} => array(
          'orgnr' => $translator->{'Organization number'},
          'company' => $translator->{'Company'},
          'commodity' => $translator->{'Commodity'},
          // 'customer_nr' => $translator->{'Customer number'},
          'address' => $translator->{'Address'},
          'zipcode' => $translator->{'Zip code'},
          'city' => $translator->{'City'},
          'country' => $translator->{'Country'},
          'phone1' => $translator->{'Phone 1'},
          'phone2' => $translator->{'Phone 2'},
          'fax' => $translator->{'Fax number'},
          'email' => $translator->{'E-mail'},
          'website' => $translator->{'Website'},
          //'presentation' => $translator->{'Presentation'},
        ),
      $translator->{"Select all:"}." ".$translator->{"Billing address"} => array(
          'invoice_company' => $translator->{'Company'},
          'invoice_address' => $translator->{'Address'},
          'invoice_zipcode' => $translator->{'Zip code'},
          'invoice_city' => $translator->{'City'},
          'invoice_country' => $translator->{'Country'},
          'invoice_email' => $translator->{'E-mail'},
        ),
      $translator->{"Select all:"}." ".$translator->{"Contact person"} => array(
          //'alias' => $translator->{'Username'},
          'name' => $translator->{'Contact person'},
          'contact_phone' => $translator->{'Contact Phone'},
          'contact_phone2' => $translator->{'Contact Phone 2'},
          'contact_email' => $translator->{'Contact Email'},
        )
    );
?>
<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $headline; ?></h1>

<?php if (userLevel() > 2): ?>
<script type="text/javascript">
	$(document).ready(function(){
		$('#markAll').click(function(){
			var check = $('#markAll').prop('checked');
			var test = $('.data');
			$('.data').children().each(function(){
				var checkBox = $('input', $(this));
				if(checkBox.prop('checked') && check == false){
					checkBox.prop('checked', false);
				} else if(checkBox.prop('checked') == false && check == true) {
					checkBox.prop('checked', true);
				}
			});
		});
	});
  function confirmRequest() {
		var count = 0;
		var rows = '';
    
    <?php // Loops through all rows and checks the checkboxes: if ticked in, add the data ID to the list of rows to export ?>
		$('tbody:last > tr').each(function(i){
    
			var checkBox = $(this).children(':last').children(':first');
			
			if(checkBox.prop('checked')){
				var cBoxId = checkBox.attr('id').replace("exp_row_","");
        // Prepend a semicolon only if not first element
				rows+=(rows==''?'':';')+cBoxId;
				count++;
			}	
		});
    
		if(count < 1){
    
			alert('<?php echo $row_export_err?>');
      return;
		}
    
    var countCol = 0;
    var data = '';
    
    var html = '<form action="exhibitor/export2/<?php echo $fairId;?>" method="POST" id="popupform_register" style="width: 650px;">'
      + '<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>'
      + '<h1><?php echo $translator->{'Please choose other fields to export if necessary:'}; ?></h1><br class="clear">';
    
    <?php
      // Loop through the array of columns defined at the start of this document (or in ExhibitorController if I have moved it)
      // Generate a checkbox form from the array
      $fieldcolumn = 0;
      foreach($column_info as $column => $fields):
        $fieldcolumn++;
    ?>
      html += '<div class="form_column" style="width: 200px;">'
        <?php // Select-all checkbox with jQuery to alter every checkbox in this column to the same state as this (select-all) checkbox ?>
        + '<p><input type="checkbox" onclick="$(\'input[column=<?php echo $fieldcolumn; ?>]\').prop(\'checked\', $(this).prop(\'checked\'));"/>'
        + '<label class="inline-block"><?php echo $column; ?></label></p>';
        
      <?php foreach($fields as $field_name => $field_label): ?>
        html += '<div><input type="checkbox" column="<?php echo $fieldcolumn; ?>" name="field_<?php echo $field_name; ?>"/>'
          + '<label class="inline-block"><?php echo htmlspecialchars($field_label); ?></label></div>';
      <?php endforeach; ?>
        
      html += '</div>';
    <?php endforeach; ?>
        
      html += '<p class="clear" style="text-align: right;">'
        + '<input type="submit" id="button_cancel" value="<?php echo uh($translator->{"Cancel"}); ?>"/>'
        + '<input type="submit" id="button_export" value="<?php echo uh($translator->{"Export as Excel document"}); ?>"/></p>'
        + '<input type="hidden" name="rows" value="'+rows+'"/>';
    html += '</form>';
    
    $('#overlay').show();
    $('body').prepend(html);
    
    // Loop through all available columns
    $('input[id^="expc_"]').each( function() {
        if($(this).prop('checked')) {
          var name = $(this).prop('id').replace("expc_", "field_");
          if($('input[name="'+name+'"]').length !== 0)
            $('input[name="'+name+'"]').prop('checked', true);
          else
            $('form#popupform_register').prepend('<input type="hidden" name="'+name+'" value="true"/>');
        }
      });
      
    var closePopup = function(e) {
      e.preventDefault();
      e.stopPropagation();
			$('#popupform_register').remove();
			$('#overlay').hide();
		};
		$(".closeDialogue").click(closePopup);
		$("#button_cancel").click(closePopup);
    
    // if(countCol > 0){
    
      // document.location.href='exhibitor/export/<?php echo $fairId;?>'+data+rows;
      
    // } else {
    
      // alert('<?php echo $col_export_err?>');
    // }
  };
  
	function sendRequest(){
		var count = 0;
		var rows = '/';
		$('tbody:last > tr').each(function(i){
			var checkBox = $(this).children(':last').children(':first');
			
			if(checkBox.prop('checked')){
				var cBoxId = checkBox.attr('id').replace("exp_row_","");
				rows+=cBoxId+';';
				count++;
			}	
		});
		if(count < 1){
			alert('<?php echo $row_export_err?>');
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
				alert('<?php echo $col_export_err?>');
			}
		}
	}
  // function requestExport(e) {
		// e.preventDefault();
		// e.stopPropagation();
		// $('#overlay').show();
// #####
		// var url = $(this).attr('href');
		// var html = '<form action="exhibitor/export/<?php echo $fairId;?>" method="post" id="popupform">'
				// +   '<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0 0 0 268px;"/>'
        
        // +   '<div class="form_column">'
        // +     '<input type="checkbox" id="copy"/>'
        // +     '<label class="inline-block" for="copy"><?php echo $translator->{'Copy from company details'}; ?></label>'
        // +   '</div>'
        
				// +   '<p><input type="submit" id="button_cancel" value="<?php echo $translator->{'Cancel'};?>"/><input type="submit" name="export" value="<?php echo $translator->{'Export as Excel document'};?>"/></p></div>'
				// + '</form>';
		
		// $('body').prepend(html);
    
    // var closePopup = function(e) {
      // e.preventDefault();
      // e.stopPropagation();
			// $('#popupform').remove();
			// $('#overlay').hide();
		// };
		// $(".closeDialogue").click(closePopup);
		// $("#button_cancel").click(closePopup);
		
		// return false;
		
	// });
</script>


<?php endif; ?>
<style>
	.std_table tbody{border-top:0px;}
	.std_table tr.special th{background:#fff; border-left:1px solid #000; border-right:1px solid #000;}
</style>


<?php if(count($exhibitors) > 0): ?>
<p><a class="button add" href="mailto:<?php
	$count=0;
	foreach ($exhibitors as $user): 
		if($count == 0):
			echo "?bcc=".$user['email'];
		else:
			echo "&bcc=".$user['email'];
		endif;
		$count++;
	endforeach;?>"><?php echo uh($translator->{'Send mail'}); ?></a></p>
<div class="tbld" style="">
	<input type="button" value="<?php echo $export_button ?>" style="float:right;" onclick="confirmRequest();"/>
	<table class="std_table">
		<?php if (userLevel() > 2): ?>
		<tr class="special">
			<th style="border:0px;"><input type="checkbox" id="expc_posstatus" value="1" checked="checked" /></th>
			<th><input type="checkbox" id="expc_posname" value="2" checked="checked" /></th>
			<th><input type="checkbox" id="expc_company" value="3" checked="checked" /></th>
			<th><input type="checkbox" id="expc_address" value="5" checked="checked" /></th>
			<th><input type="checkbox" id="expc_commodity" value="6" checked="checked" /></th>
			<th><input type="checkbox" id="expc_phone1" value="7" checked="checked" /></th>
			<th><input type="checkbox" id="expc_name" value="8" checked="checked" /></th>
			<th><input type="checkbox" id="expc_email" value="9" checked="checked" /></th>
			<th><input type="checkbox" id="expc_website" value="10" checked="checked" /></th>
			<th></th>
			<?php if (userLevel() > 0): ?>
			<th></th>
			<th><input type="checkbox" id="markAll" checked="checked" /></th>
			<?php endif; ?>
		</tr>
		<?php endif; ?>
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
		<tbody class="data">
			<?php foreach ($exhibitors as $pos): ?>
			<tr>		
			
				<td><?php echo ($pos['posstatus'] == 2 ? $label_booked : ($pos['posstatus'] == 1 ? $label_reserved : '')); ?></td>
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
			
				<td><input type="checkbox" id="exp_row_<?php echo $pos['position']?>" checked="checked" /></td>
				<?php endif; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php else: ?>
	<div class="tbld">
		<p><?php echo "No exhibitors was found for this fair."?></p>	
	</div>
<?php endif; ?>
