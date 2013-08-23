<?php
  global $translator;
  
  // Create an array used in generating the popup form when exporting data
  // Probably will be moved to ExhibitorControlled to be re-used to validate the received export request
  $column_info = array(
      $translator->{"Select all: Company"} => array(
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
      $translator->{"Select all: Billing address"} => array(
          'invoice_company' => $translator->{'Company'},
          'invoice_address' => $translator->{'Address'},
          'invoice_zipcode' => $translator->{'Zip code'},
          'invoice_city' => $translator->{'City'},
          'invoice_country' => $translator->{'Country'},
          'invoice_email' => $translator->{'E-mail'},
        ),
      $translator->{"Select all: Contact person"} => array(
          //'alias' => $translator->{'Username'},
          'name' => $translator->{'Contact person'},
          'phone3' => $translator->{'Contact Phone'},
          'phone4' => $translator->{'Contact Phone 2'},
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
  function requestExport(e) {
		e.preventDefault();
		e.stopPropagation();
		$('#overlay').show();
// #####
		var url = $(this).attr('href');
		var html = '<form action="exhibitor/export/<?php echo $fairId;?>" method="post" id="popupform">'
				+   '<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0 0 0 268px;"/>'
        
        +   '<div class="form_column">'
        +     '<input type="checkbox" id="copy"/>'
        +     '<label class="inline-block" for="copy"><?php echo $translator->{'Copy from company details'}; ?></label>'
        +   '</div>'
        
				+   '<p><input type="submit" id="button_cancel" value="<?php echo $translator->{'Cancel'};?>"/><input type="submit" name="export" value="<?php echo $translator->{'Export as Excel document'};?>"/></p></div>'
				+ '</form>';
		
		$('body').prepend(html);
    
    var closePopup = function(e) {
      e.preventDefault();
      e.stopPropagation();
			$('#popupform').remove();
			$('#overlay').hide();
		};
		$(".closeDialogue").click(closePopup);
		$("#button_cancel").click(closePopup);
		
		return false;
		
	});
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
	endforeach;?>"><?php echo $mail_link;?><?php echo $translator->{'Send mail'}?></a></p>
<div class="tbld" style="">
	<input type="button" value="<?php echo $export_button ?>" style="float:right;" onclick="sendRequest()"/>
	<table class="std_table">
		<?php if (userLevel() > 2): ?>
		<tr class="special">
			<th style="border:0px;"><input type="checkbox" id="exp_st" value="1" checked></input></th>
			<th><input type="checkbox" id="exp_nm" value="2" checked></input></th>
			<th><input type="checkbox" id="exp_cp" value="3" checked></input></th>
			<th><input type="checkbox" id="exp_ad" value="5" checked></input></th>
			<th><input type="checkbox" id="exp_br" value="6" checked></input></th>
			<th><input type="checkbox" id="exp_ph" value="7" checked></input></th>
			<th><input type="checkbox" id="exp_co" value="8" checked></input></th>
			<th><input type="checkbox" id="exp_em" value="9" checked></input></th>
			<th><input type="checkbox" id="exp_we" value="10" checked></input></th>
			<th></th>
			<?php if (userLevel() > 0): ?>
			<th></th>
			<th><input type="checkbox" id="markAll" checked></input></th>
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
			
				<td><input type="checkbox" id="exp_row_<?php echo $pos['position']?>" checked></input></td>
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
