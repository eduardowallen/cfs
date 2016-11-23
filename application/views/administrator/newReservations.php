<?php
global $translator;

$general_column_info = array(
	$translator->{"Company"} => array(
		'orgnr' => $translator->{'Organization number'},
		'company' => $translator->{'Company'},
		'commodity' => $translator->{'Commodity'},
		'address' => $translator->{'Address'},
		'zipcode' => $translator->{'Zip code'},
		'city' => $translator->{'City'},
		'country' => $translator->{'Country'},
		'phone1' => $translator->{'Phone 1'},
		'phone2' => $translator->{'Phone 2'},
		'fax' => $translator->{'Fax number'},
		'email' => $translator->{'E-mail'},
		'website' => $translator->{'Website'}
	),
	$translator->{"Billing address"} => array(
		'invoice_company' => $translator->{'Company'},
		'invoice_address' => $translator->{'Address'},
		'invoice_zipcode' => $translator->{'Zip code'},
		'invoice_city' => $translator->{'City'},
		'invoice_country' => $translator->{'Country'},
		'invoice_email' => $translator->{'E-mail'}
	),
	$translator->{"Contact person"} => array(
		'name' => $translator->{'Contact person'},
		'contact_phone' => $translator->{'Contact Phone'},
		'contact_phone2' => $translator->{'Contact Phone 2'},
		'contact_email' => $translator->{'Contact Email'}
	)
);

$bookings_columns = array(
	$translator->{"Booking"} => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'edit_time' => $translator->{'Last edited'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$bookings_columns = array_merge($bookings_columns, $general_column_info);

$reserved_columns = array(
	$translator->{"Reservation"} => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'expires' => $translator->{'Reserved until'},
		'booking_time' => $translator->{'Time of booking'},
		'edit_time' => $translator->{'Last edited'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$reserved_columns = array_merge($reserved_columns, $general_column_info);

$prelbookings_columns = array(
	$translator->{"Preliminary booking"} => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$prelbookings_columns = array_merge($prelbookings_columns, $general_column_info);

$prelbookings_inactive_columns = array(
	$translator->{"Preliminary booking (inactive)"} => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$prelbookings_inactive_columns = array_merge($prelbookings_inactive_columns, $general_column_info);

$fair_registrations_columns = array(
	$translator->{"Registration"} => array(
		'status' => $translator->{'Status'},
		'area' => $translator->{'Area'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$fair_registrations_columns = array_merge($fair_registrations_columns, $general_column_info);

$fair_registrations_deleted_columns = array(
	$translator->{"Registration (deleted)"} => array(
		'status' => $translator->{'Status'},
		'area' => $translator->{'Area'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$fair_registrations_deleted_columns = array_merge($fair_registrations_deleted_columns, $general_column_info);

$prelbookings_deleted_columns = array(
	$translator->{"Preliminary booking (deleted)"} => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$prelbookings_deleted_columns = array_merge($prelbookings_deleted_columns, $general_column_info);

$bookings_deleted_columns = array(
	$translator->{"Booking/reservation (deleted)"} => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'booking_time' => $translator->{'Time of booking'},
		'edit_time' => $translator->{'Last edited'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$bookings_deleted_columns = array_merge($bookings_deleted_columns, $general_column_info);

$reserved_cloned_columns = array(
	$translator->{"Reservation (cloned)"} => array(
		'status' => $translator->{'Status'},
		'position' => $translator->{'Stand'},
		'area' => $translator->{'Area'},
		'information' => $translator->{'Information about stand space'},
		'commodity' => $translator->{'Trade'},
		'extra_options' => $translator->{'Extra options'},
		'expires' => $translator->{'Reserved until'},
		'booking_time' => $translator->{'Time of booking'},
		'edit_time' => $translator->{'Last edited'},
		'arranger_message' => $translator->{'Message to organizer in list'}
	)
);
$reserved_cloned_columns = array_merge($reserved_cloned_columns, $general_column_info);
?>
<style>
	#content{max-width:1280px;}
	form, .std_table { clear: both; }
	.squaredFour{width:1.416em; height:1.416em;}
	.squaredFour:before{left:0.33em;top:0.33em;}
	.scrolltable-wrap{margin:0.5em 0em 1em 0em;}
	.no-search{max-height: 18em;}
	#review_list_div{max-height: 48em;}
</style>

<script type="text/javascript" src="js/tablesearch.js<?php echo $unique?>"></script>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>start/home'"><?php echo uh($translator->{'Go back'}); ?></button>
<h1><?php echo $fair->get('name'); ?></h1>

<?php if (isset($fairs_admin)): // If a list of accessible fairs is found, display a drop-down list to choose from ?>
  <label class="inline-block"><?php echo uh($translator->{'Switch to event: '}); ?>&nbsp</label>
  <select onchange="if(this.value) document.location.href=this.value;">
  <?php
    $own = false;
    $options = '';
    foreach($fairs_admin as $fa) {
      $active = $fair->get('id') == $fa['id'];
      $own = $own || $active;
      $options .= '<option value="'.BASE_URL.'administrator/reservationsChangeFair/'.$fa['id'].'"'.($active?" selected":"").'>'.$fa['name'].'</option>';
    }
    
    if(!$own) :
  ?>
    <option value selected><?php echo $fair->get('name'); ?></option>
  <?php
    endif;
    echo $options;
  ?>
  </select>
  <br class="clear">
  <br class="clear">
<?php endif; ?>

<script type="text/javascript">
$body = $("body");

$(document).on({
    ajaxStart: function() { $body.addClass("loading");    },
     ajaxStop: function() { $body.removeClass("loading"); }
});

$(document.body).on('click', '.open-create-invoices', createInvoices);
function createInvoices(e) {
	e.preventDefault();

	var button = $(e.target);
	var table_form = $(button.prop('form'));
	var html = '<label for="invoice_expirationdate"><?php echo uh($translator->{"Expiration date"}); ?> (DD-MM-YYYY) *</label> <input type="text" class="datepicker date" name="invoice_expirationdate" id="invoice_expirationdate" value="<?php echo $default_invoice_date; ?>"/>';
	count = 0;

	$('input[name*=rows]:checked', table_form).each(function(index, input) {
		count += $(input).data('invoiceid').length;
	});

	if (count === 0) {
		$.confirm({
			title: '<?php echo uh($translator->{"Set expiration date for these invoices"}); ?>',
			content: html + '<br/><br/><i><?php echo uh ($translator->{"You can change the date that is automatically entered in the field above at the Invoice Settings for this event."}); ?></i>',
		    confirm: function(){
		    	var date = this.$content.find('input');
		        var val = this.$content.find('input').val(); // get the input value.
				if (val.match(/^\d\d-\d\d-\d\d\d\d$/)) {
					var dateParts = val.split('-');
					dt = new Date(parseInt(dateParts[2], 10), parseInt(dateParts[1], 10)-1, parseInt(dateParts[0], 10));
					// Add one day, since it should be up to and including.
					dt.setDate(dt.getDate(+1));
					if (dt < new Date()) {
						date.css('border-color', 'red');
						return false;
					}
					//console.log(Math.round(dt.getTime()/1000)+43200);
				} else {
					date.css('border-color', 'red');
					return false;
				}
				$.confirm({
					title: '<?php echo $confirm_create_invoice; ?>',
					content: '<?php echo uh($translator->{"This will create invoices for all the selected spaces"}); ?> <?php echo uh($translator->{"with expirationdate"}); ?> ' + $('#invoice_expirationdate').val(),
					confirm: function(){

						$('input[name*=rows]:checked', table_form).each(function(index, input) {
							$.ajax({
								url: 'exhibitor/exportBookingPDF/' + $(input).val() + '/' + (Math.round(dt.getTime()/1000) + 43200),
								method: 'POST'
								});
							//console.log($(input).val());
						});
						$(document).on({
							ajaxStop: function() { 
								$.alert({
								    content: '<?php echo uh($translator->{"The invoices were successfully created."}); ?>',
								    confirm: function() {
								    	document.location.reload();
								    }
								});
							}
						});
					},
					cancel: function(){}
				});

			},
			cancel: function() {

			}
		});
	} else {
		$.alert({
		    content: '<?php echo uh($translator->{"You cannot create new invoices for reservations that already have invoices created for them."}); ?>',
		    confirm: function() {
		    	
		    }
		});
	}
}
		function confirmCreateInvoice(link, posname, company) {
			var html = '<label for="invoice_expirationdate"><?php echo uh($translator->{"Expiration date"}); ?> (DD-MM-YYYY) *</label> <input type="text" class="datepicker date" name="invoice_expirationdate" id="invoice_expirationdate" value="<?php echo $default_invoice_date; ?>"/>';
			$.confirm({
				title: '<?php echo uh($translator->{"Set expiration date for this invoice"}); ?>',
				content: html + '<br/><br/><i><?php echo uh ($translator->{"You can change the date that is automatically entered in the field above at the Invoice Settings for this event."}); ?></i>',
			    confirm: function(){
			    	var input = this.$content.find('input');
			        var val = this.$content.find('input').val(); // get the input value.
					if (val.match(/^\d\d-\d\d-\d\d\d\d$/)) {
						var dateParts = val.split('-');
						dt = new Date(parseInt(dateParts[2], 10), parseInt(dateParts[1], 10)-1, parseInt(dateParts[0], 10));
						// Add one day, since it should be up to and including.
						dt.setDate(dt.getDate(+1));
						if (dt < new Date()) {
							input.css('border-color', 'red');
							return false;
						}
						console.log(Math.round(dt.getTime()/1000)+43200);
					} else {
						input.css('border-color', 'red');
						return false;
					}
					$.confirm({
						title: '<?php echo $confirm_create_invoice; ?> ' + company,
						content: '<?php echo uh($translator->{"This will create an invoice for the exhibitor"}); ?> ' + company + ' (' + posname + ') ' + ' <?php echo uh($translator->{"with expirationdate"}); ?> ' + $('#invoice_expirationdate').val(),
						confirm: function(){
							setTimeout(function() {
								document.location.reload();
							}, 1000);
							window.open(link + '/' + (Math.round(dt.getTime()/1000) + 43200), '_blank');
							console.log(dt.getTime()/1000);
						},
						cancel: function(){}
					});
		
				},
				cancel: function() {

				}
			});
		}
		function confirmCreditInvoice(link, posname, company) {
			confirmBoxNewTab('<?php echo $confirm_credit_invoice; ?> ' + posname +' (' + company + ')?', link);
		}
		function confirmCancelInvoice(link, posname, company) {
			confirmBoxNewTab('<?php echo $confirm_cancel_invoice; ?> ' + posname +' (' + company + ')?', link);
		}
		function confirmMarkAsSent(link, posname, company, id) {
			confirmBoxNoTab('<?php echo $confirm_mark_as_sent; ?> ' + posname +' (' + company + ')?', link, id);
		}
	

	function denyPrepPosition(link, position, status){
		var confirmDialogue = "<?php echo $confirm_delete?> %s?";
		var deletion = "<?php echo $deletion_comment?>";
        $.confirm({
            title: ' ',
            content: deletion + '<textarea style="margin-top: 0.5em" cols="50" rows="5" placeholder="<?php echo $deletion_comment_placeholder ?>"></textarea>',
            confirm: function(){
            	var message = this.$content.find('textarea').val();
              $.confirm({
				title: ' ',
				content: confirmDialogue.replace('%s', position),
				confirm: function(){
				  denyPosition(link, message, position, status);
				},
				cancel: function() {
				}
              });
            },
            cancel: function() {
            }
        });
	}
	function denyPrepRegistration(link, company){
		var confirmDialogue = "<?php echo $confirm_delete_registration?> %s?";
		var deletion = "<?php echo $deletion_comment?>";
        $.confirm({
            title: ' ',
            content: deletion + '<textarea style="margin-top: 0.5em" cols="50" rows="5" placeholder="<?php echo $deletion_comment_placeholder ?>"></textarea>',
            confirm: function(){
            	var message = this.$content.find('textarea').val();
              $.confirm({
				title: ' ',
				content: confirmDialogue.replace('%s', company),
				confirm: function(){
				  denyRegistration(link, message, company);
				},
				cancel: function() {
				}
              });
            },
            cancel: function() {
            }
        });
	}


	var export_fields = {
		booked: <?php echo json_encode($bookings_columns); ?>,

		reserved: <?php echo json_encode($reserved_columns); ?>,

		reserved_cloned: <?php echo json_encode($reserved_cloned_columns); ?>,

		prem: <?php echo json_encode($prelbookings_columns); ?>,
		
		iprem: <?php echo json_encode($prelbookings_inactive_columns); ?>,

		fair_registrations: <?php echo json_encode($fair_registrations_columns); ?>,

		delprem: <?php echo json_encode($prelbookings_deleted_columns); ?>,

		delbookings: <?php echo json_encode($bookings_deleted_columns); ?>,

		delregistrations: <?php echo json_encode($fair_registrations_deleted_columns); ?>,

	};
</script>

<?php if ($hasRights): ?>


<style>
#book_position_form fieldset {
	padding-top: 0;
	padding-bottom: 2.5em;
	border-top: solid #d21d1d 5em;
}
</style>
<!-- multistep form -->
<form id="book_position_form" class="form booking_form" method="post" action="">
<!-- progressbar -->
<ul id="progressbar">
	<li class="active"><?php echo uh($translator->{'Categories and assortment'}); ?></li>
	<li><?php echo uh($translator->{'Articles and extra options'}); ?></li>
	<li><?php echo uh($translator->{'Message to organizer (optional)'}); ?></li>
	<li><?php echo uh($translator->{'Confirm booking'}); ?>
</ul>
	<fieldset>
		<!-- FIELDSET NUMBER ONE -->
		<h3 class="confirm standSpaceName"><?php echo uh($translator->{'Book requested stand space'}); ?></h3>
		<h3 class="edit standSpaceName"><?php echo uh($translator->{'Edit booking'}); ?></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -2em;"/>
		<br />
		<div class="ssinfo"></div>
	<div id="column">
	  <!-- Drop-downlista för att välja användare att boka in -->	
		<label for="book_user_input"><?php echo uh($translator->{'User'}); ?></label>
		<select style="width:25em;" id="book_user_input" disabled="disabled">
			<option id="book_user"></option>
		</select>
		<label class="label_medium" for="book_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
		<textarea name="commodity" maxlength="200" class="commodity_big" id="book_commodity_input"></textarea>		
	</div>
	    		<!-- Div för att välja kategori -->
		<label class="table_header" for="book_category_scrollbox"><?php echo uh($translator->{'Categories'}); ?> *</label>
		<div class="scrolltable-wrap no-search" id="book_category_scrollbox_div">
			<table class="std_table std_booking_table" id="book_category_scrollbox">
				<thead>
					<tr>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('categories') as $cat){ ?>
					<tr>
						<td class="left"><?php echo $cat->get('name') ?></td>
						<td>
							<input type="checkbox" id="<?php echo $cat->get('id') ?>" name="categories[]" value="<?php echo $cat->get('id') ?>" />
							<label class="squaredFour" for="<?php echo $cat->get('id') ?>" />
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" id="book_first_step" class="greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />

	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER TWO -->
		<h3 class="confirm standSpaceName"><?php echo uh($translator->{'Book requested stand space'}); ?></h3>
		<h3 class="edit standSpaceName"><?php echo uh($translator->{'Edit booking'}); ?></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -2em;"/>
			<!--  Extra tillval -->
	<?php if ($fair->get('extraOptions')): ?>
		<label class="table_header" for="book_option_scrollbox"><?php echo uh($translator->{'Extra options'}); ?></label>
		<div class="scrolltable-wrap no-search" id="book_option_scrollbox_div">
			<table class="std_table std_booking_table" id="book_option_scrollbox">
				<thead>
					<tr>
						<th>ID</th>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th><?php echo uh($translator->{'Price'}); ?></th>
						<th data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('extraOptions') as $extraOption) { ?>
						<tr>
							<td><?php echo $extraOption->get('custom_id') ?></td>
							<?php if ($extraOption->get('required') == 1): ?>
								<td class="left"><?php echo $extraOption->get('text') ?>*</td>
							<?php else : ?>
								<td class="left"><?php echo $extraOption->get('text') ?></td>
							<?php endif; ?>
							<td><?php echo $extraOption->get('price') ?></td>
							<td style="display:none;">
							<?php if ($extraOption->get('vat') == 25) { ?>
								<input hidden value="25" />
							<?php } else if ($extraOption->get('vat') == 12) { ?>
								<input hidden value="12" />
							<?php } else { ?>
								<input hidden value="0" />
							<?php } ?>
							</td>								
							<td>
								<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" name="options[]" value="<?php echo $extraOption->get('id') ?>"/>
								<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
			<!--  Artiklar  -->
	<?php if ($fair->get('articles')): ?>
		<label class="table_header" for="book_article_input"><?php echo uh($translator->{"Articles"}); ?></label>
		<div class="scrolltable-wrap no-search" id="book_article_scrollbox_div">
			<table class="std_table" id="book_article_scrollbox">
				<thead>
					<tr>
						<th>ID</th>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th><?php echo uh($translator->{'Price'}); ?></th>
						<th style="text-indent:-3.916em;" data-sorter="false"><?php echo uh($translator->{'Amount'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('articles') as $article) { ?>
					<tr>
						<td><?php echo $article->get('custom_id') ?></td>
						<td class="left"><?php echo $article->get('text') ?></td>
						<td><?php echo $article->get('price') ?></td>
						<td style="display:none;">
							<?php if ($article->get('vat') == 25) { ?>
								<input hidden value="25" />
							<?php } else if ($article->get('vat') == 12) { ?>
								<input hidden value="12" />
							<?php } else { ?>
								<input hidden value="0" />
							<?php } ?>
						</td>								
						<td class="td-number-span">
							<input type="text" class="form-control bfh-number" min="0" value="0" name="artamount[]" id="<?php echo $article->get('id') ?>" />
							<input type="hidden" name="articles[]" />
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" name="next" class="next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />
		
	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER THREE -->
		<h3 class="confirm standSpaceName"><?php echo uh($translator->{'Book requested stand space'}); ?></h3>
		<h3 class="edit standSpaceName"><?php echo uh($translator->{'Edit booking'}); ?></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -2em;"/>
		<br />

		<label class="label_long" for="book_message_input"><?php echo uh($translator->{'Message to organizer'}); ?></label>
		<textarea name="arranger_message" class="msg_to_organizer" id="book_message_input"></textarea>
		<br />
		<br />

		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" id="book_review" name="next" class="next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />	
	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER FOUR -->
		<h3 class="confirm standSpaceName" style="padding-bottom: 0.416em;"><?php echo uh($translator->{'Book requested stand space'}); ?></h3>
		<h3 class="edit standSpaceName" style="padding-bottom: 0.416em;"><?php echo uh($translator->{'Edit booking'}); ?></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -3.7em; margin-right: -2em;"/>
		<br />
		<div id="review_book_dialogue">
			<br />
			<label for="review_user" style="font-size:1.7em; display:inline;"><?php echo uh($translator->{'Exhibitor:'}); ?> </label>
			<span style="font-size:1.7em;" id="review_user"></span>
			<br />			
			<div id="column" class="review_column1">
				<label for="review_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
				<p name="commodity" id="review_commodity_input"></p>
			</div>
			<div id="column" class="review_column2">
				<label for="review_category_list"><?php echo uh($translator->{'Categories'}); ?></label>
				<p id="review_category_list" style="width:100%; float:left;"></p>		
			</div>
			<label for="review_message" id="review_message_label"><?php echo uh($translator->{'Exhibitor message to Organizer'}); ?></label>
			<p name="arranger_message" id="review_message"></p>	
			<div class="no-search" id="review_list_div" style="padding:1.66em 0em;">
				<div id="review_div">
					<table id="review_list" class="no-search" style="width:100%;">
					</table>
				</div>
				<table id="review_list2" class="no-search" style="width:100%;">
				</table>
			</div>
		</div>
		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="hidden" name="id" id="book_id" />
		<input type="submit" name="approve" class="submit edit greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Save'}); ?>" />
		<input type="submit" name="approve" class="submit close greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Close'}); ?>" />
		<input type="submit" name="approve" class="submit confirm greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Confirm booking'}); ?>" />
	</fieldset>
</form>



<style>
#reserve_position_form fieldset {
	padding-top: 0;
	padding-bottom: 2.5em;
	border-top: 5em solid #3258CD;
}
</style>
<!-- multistep form -->
<form id="reserve_position_form" class="form booking_form" method="post" action="">
<!-- progressbar -->
<ul id="progressbar">
	<li class="active"><?php echo uh($translator->{'Categories and assortment'}); ?></li>
	<li><?php echo uh($translator->{'Articles and extra options'}); ?></li>
	<li><?php echo uh($translator->{'Message to organizer (optional)'}); ?></li>
	<li><?php echo uh($translator->{'Confirm booking'}); ?>
</ul>
	<fieldset>
		<!-- FIELDSET NUMBER ONE -->
		<h3 class="confirm standSpaceName"><?php echo uh($translator->{'Reserve requested stand space'}); ?></h3>
		<h3 class="edit standSpaceName"><?php echo uh($translator->{'Edit reservation'}); ?></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -2em;"/>
		<br />
		<div class="ssinfo"></div>
	<div id="column">
		<!-- Kalenderfunktion för att välja platsens slutgiltiga reservationsdatum -->
		<label for="reserve_expires_input"><?php echo uh($translator->{'Reserved until'}); ?> (DD-MM-YYYY HH:MM)</label>
		<input type="text" class="dialogueInput datetime datepicker" name="expires" id="reserve_expires_input" title="<?php echo uh($translator->{'The date that you set here is the date when the reservation expires and the stand space is reopened (green) for preliminary bookings.'}); ?>" value=""/>
	</div>
	<div id="column">
	  <!-- Drop-downlista för att välja användare att boka in -->	
		<label for="reserve_user_input"><?php echo uh($translator->{'User'}); ?></label>
		<select style="width:25em;" id="reserve_user_input" disabled="disabled">
			<option id="reserve_user"></option>
		</select>
		<label class="label_medium" for="reserve_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
		<textarea name="commodity" maxlength="200" class="commodity_big" id="reserve_commodity_input"></textarea>		
	</div>
	    		<!-- Div för att välja kategori -->
		<label class="table_header" for="reserve_category_scrollbox"><?php echo uh($translator->{'Categories'}); ?> *</label>
		<div class="scrolltable-wrap no-search" id="reserve_category_scrollbox_div">
			<table class="std_table std_booking_table" id="reserve_category_scrollbox">
				<thead>
					<tr>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('categories') as $cat){ ?>
					<tr>
						<td class="left"><?php echo $cat->get('name') ?></td>
						<td>
							<input type="checkbox" id="<?php echo $cat->get('id') ?>" name="categories[]" value="<?php echo $cat->get('id') ?>" />
							<label class="squaredFour" for="<?php echo $cat->get('id') ?>" />
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" id="reserve_first_step" class="greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />

	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER TWO -->
		<h3 class="confirm standSpaceName"><?php echo uh($translator->{'Reserve requested stand space'}); ?></h3>
		<h3 class="edit standSpaceName"><?php echo uh($translator->{'Edit reservation'}); ?></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -2em;"/>
			<!--  Extra tillval -->
	<?php if ($fair->get('extraOptions')): ?>
		<label class="table_header" for="reserve_option_scrollbox"><?php echo uh($translator->{'Extra options'}); ?></label>
		<div class="scrolltable-wrap no-search" id="reserve_option_scrollbox_div">
			<table class="std_table std_booking_table" id="reserve_option_scrollbox">
				<thead>
					<tr>
						<th>ID</th>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th><?php echo uh($translator->{'Price'}); ?></th>
						<th data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('extraOptions') as $extraOption) { ?>
						<tr>
							<td><?php echo $extraOption->get('custom_id') ?></td>
							<?php if ($extraOption->get('required') == 1): ?>
								<td class="left"><?php echo $extraOption->get('text') ?>*</td>
							<?php else : ?>
								<td class="left"><?php echo $extraOption->get('text') ?></td>
							<?php endif; ?>
							<td><?php echo $extraOption->get('price') ?></td>
							<td style="display:none;">
							<?php if ($extraOption->get('vat') == 25) { ?>
								<input hidden value="25" />
							<?php } else if ($extraOption->get('vat') == 12) { ?>
								<input hidden value="12" />
							<?php } else { ?>
								<input hidden value="0" />
							<?php } ?>
							</td>									
							<td>
								<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" name="options[]" value="<?php echo $extraOption->get('id') ?>"/>
								<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
			<!--  Artiklar  -->
	<?php if ($fair->get('articles')): ?>
		<label class="table_header" for="reserve_article_input"><?php echo uh($translator->{"Articles"}); ?></label>
		<div class="scrolltable-wrap no-search" id="reserve_article_scrollbox_div">
			<table class="std_table" id="reserve_article_scrollbox">
				<thead>
					<tr>
						<th>ID</th>
						<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
						<th><?php echo uh($translator->{'Price'}); ?></th>
						<th style="text-indent:-3.916em;" data-sorter="false"><?php echo uh($translator->{'Amount'}); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($fair->get('articles') as $article) { ?>
					<tr <?php if ($article->get('required') == 1): ?>style="background-image: url('../images/hidden_background.jpg'); background-position: 500px;"<?php endif; ?>>
						<td><?php echo $article->get('custom_id') ?></td>
						<td class="left"><?php echo $article->get('text') ?></td>
						<td><?php echo $article->get('price') ?></td>
						<td style="display:none;">
							<?php if ($article->get('vat') == 25) { ?>
								<input hidden value="25" />
							<?php } else if ($article->get('vat') == 12) { ?>
								<input hidden value="12" />
							<?php } else { ?>
								<input hidden value="0" />
							<?php } ?>
						</td>								
						<td class="td-number-span">
							<input type="text" class="form-control bfh-number" min="0" value="0" name="artamount[]" id="<?php echo $article->get('id') ?>" />
							<input type="hidden" name="articles[]" />
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" name="next" class="next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />
		
	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER THREE -->
		<h3 class="confirm standSpaceName"><?php echo uh($translator->{'Reserve requested stand space'}); ?></h3>
		<h3 class="edit standSpaceName"><?php echo uh($translator->{'Edit reservation'}); ?></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -4em; margin-right: -2em;"/>
		<br />

		<label class="label_long" for="reserve_message_input"><?php echo uh($translator->{'Message to organizer'}); ?></label>
		<textarea name="arranger_message" class="msg_to_organizer" id="reserve_message_input"></textarea>
		<br />
		<br />

		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="button" id="reserve_review" name="next" class="next greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Next'}); ?>" />	
	</fieldset>
	<fieldset>
		<!-- FIELDSET NUMBER FOUR -->
		<h3 class="confirm standSpaceName" style="padding-bottom: 0.416em;"><?php echo uh($translator->{'Reserve requested stand space'}); ?></h3>
		<h3 class="edit standSpaceName" style="padding-bottom: 0.416em;"><?php echo uh($translator->{'Edit reservation'}); ?></h3>
		<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -3.7em; margin-right: -2em;"/>
		<br />
		<div id="review_reserve_dialogue">
			<br />
			<label for="review_user" style="font-size:1.7em; display:inline;"><?php echo uh($translator->{'Exhibitor:'}); ?> </label>
			<span style="font-size:1.7em;" id="review_user"></span>
			<br />			
			<div id="column" class="review_column1">
				<label for="review_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
				<p name="commodity" id="review_commodity_input"></p>
			</div>
			<div id="column" class="review_column2">
				<label for="review_category_list"><?php echo uh($translator->{'Categories'}); ?></label>
				<p id="review_category_list" style="width:100%; float:left;"></p>		
			</div>
			<label for="review_message" id="review_message_label"><?php echo uh($translator->{'Exhibitor message to Organizer'}); ?></label>
			<p name="arranger_message" id="review_message"></p>	
			<div class="no-search" id="review_list_div" style="padding:1.66em 0em;">
				<div id="review_div">
					<table id="review_list" class="no-search" style="width:100%;">
					</table>
				</div>
				<table id="review_list2" class="no-search" style="width:100%;">
				</table>
			</div>
		</div>
		<input type="button" name="previous" class="previous bluebutton mediumbutton nomargin" value="<?php echo uh($translator->{'Previous'}); ?>" />
		<input type="button" name="cancel" class="cancelbutton redbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Cancel'}); ?>" />
		<input type="hidden" name="id" id="reserve_id" />
		<input type="submit" name="reserve" class="submit confirm greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Confirm reservation'}); ?>" />
		<input type="submit" name="reserve" class="submit edit greenbutton mediumbutton nomargin" value="<?php echo uh($translator->{'Save'}); ?>" />

	</fieldset>
</form>

<div id="review_prel_dialogue" style="padding:0 2.5em 0.833em 2.5em" class="dialogue">
	<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin-top: -3.7em; margin-right: -0.5em;"/>
	<h3 class="review standSpaceName" style="padding-bottom:0.416em;"></h3>
	<br />
	<br />
	<label for="review_user" style="font-size:1.7em; display:inline;"><?php echo uh($translator->{'Exhibitor:'}); ?> </label>
	<span style="font-size:1.7em;" id="review_user"></span>
	<br />	
	<div id="column" class="review_column1">
		<label for="review_registration_area" id="review_area_label"><?php echo uh($translator->{'Requested area'}); ?></label>
		<p name="review_registration_area" id="review_registration_area"></p>
		<label for="review_commodity_input"><?php echo uh($translator->{'Commodity'}); ?></label>
		<p name="commodity" id="review_commodity_input"></p>
	</div>
	<div id="column" class="review_column2">
		<label for="review_category_list"><?php echo uh($translator->{'Categories'}); ?></label>
		<p id="review_category_list" style="width:100%; float:left;"></p>		
	</div>
	<label for="review_message" id="review_message_label"><?php echo uh($translator->{'Exhibitor message to Organizer'}); ?></label>
	<p name="arranger_message" id="review_message"></p>	
	<div class="no-search" id="review_list_div" style="padding:1.66em 0em;">
		<div id="review_div">
			<table id="review_list" class="no-search" style="width:100%;">
			</table>
		</div>
		<table id="review_list2" class="no-search" style="width:100%;">
		</table>
	</div>
</div>


	<div role="tabpanel">

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="javascript:void(0)" id="booked" class="tabs-tab" aria-controls="home" role="tab" data-toggle="tab"><img src="images/icons/marker_booked.png" class="tab_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Bookings tab'}); ?> (<?php echo count($positions); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="reserved" class="tabs-tab" aria-controls="profile" role="tab" data-toggle="tab"><img src="images/icons/marker_reserved.png" class="tab_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Reservations tab'}); ?> (<?php echo count($rpositions); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="reserved_cloned" class="tabs-tab" aria-controls="profile" role="tab" data-toggle="tab"><img src="images/icons/Reserverad-gray.png" class="tab_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Cloned reservations tab'}); ?> (<?php echo count($rcpositions); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="prel_bookings" class="tabs-tab" aria-controls="messages" role="tab" data-toggle="tab"><img src="images/icons/marker_applied.png" class="tab_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Preliminary bookings tab'}); ?> (<?php echo count($prelpos); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="fair_registrations" class="tabs-tab" aria-controls="settings" role="tab" data-toggle="tab"><img src="images/icons/script.png" class="tab_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Registrations tab'}); ?> (<?php echo count($fair_registrations); ?>)</a></li>    
    <li role="presentation"><a href="javascript:void(0)" id="prel_bookings_inactive" class="tabs-tab" aria-controls="settings" role="tab" data-toggle="tab"><img src="images/icons/marker_inactive.png" class="tab_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Preliminary bookings (inactive) tab'}); ?> (<?php echo count($iprelpos); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="fair_registrations_deleted" class="tabs-tab" aria-controls="profile" role="tab" data-toggle="tab"><img src="images/icons/recyclebin_reg.png" class="tab_img_wide" style="vertical-align:top;" /> <?php echo uh($translator->{'Deleted registrations history tab'}); ?> (<?php echo count($fair_registrations_deleted); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="bookings_deleted" class="tabs-tab" aria-controls="profile" role="tab" data-toggle="tab"><img src="images/icons/recyclebin_resbook.png" class="tab_img_wide" style="vertical-align:top;" /> <?php echo uh($translator->{'Deleted history tab'}); ?> (<?php echo count($del_positions); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="prel_bookings_deleted" class="tabs-tab" aria-controls="settings" role="tab" data-toggle="tab"><img src="images/icons/recyclebin_prel.png" class="tab_img_wide" style="vertical-align:top;" /> <?php echo uh($translator->{'Preliminary bookings (deleted) tab'}); ?> (<?php echo count($del_prelpos); ?>)</a></li>
  </ul>

  <!-- Tab panes -->
	  <div class="tab-content">
	    <div role="tabpanel" class="tab-pane active" id="booked">

	<script>

	$(document).ready(function() {
	    // go to the latest tab, if it exists:
	    var lastTab = localStorage.getItem('lastTab');
	    if (lastTab) {
			var selected = lastTab;
			var div = 'div#' + selected;
			$('.tab-div').css('display', 'none');
			$('li').removeClass('active');
			$(this).parent().attr('class', 'active');
			$(div).css('display', 'block');
			if (!$(div + ' table').hasClass('scrolltable')) {
				useScrolltable($(div + ' table'));
			}
			$(selected).floatThead('reflow');
			$(selected).floatThead('getSizingRow');
			$('[id="' + lastTab + '"]').tab('show');

	    } else {
			var selected = 'booked';
			var div = 'div#' + selected;
			$('.tab-div').css('display', 'none');
			$('li').removeClass('active');
			$(this).parent().attr('class', 'active');
			$(div).css('display', 'block');
			if (!$(div + ' table').hasClass('scrolltable')) {
				useScrolltable($(div + ' table'));
			}
	    }
	});
		$('.tabs-tab').on("click", function() {
			var selected = $(this).attr('id');
			var div = 'div#' + selected;
			$('.tab-div').css('display', 'none');
			$('li').removeClass('active');
			$(this).parent().attr('class', 'active');
			$(div).css('display', 'block');
			if (!$(div + ' table').hasClass('scrolltable')) {
				useScrolltable($(div + ' table'));
			}
			localStorage.setItem('lastTab', $(this).attr('id'));
		});

	</script>

	<div id="booked" style="display:none" class="tab-div tab-div-hidden">

	<?php if(count($positions) > 0){ ?>

		<form action="administrator/exportNewReservations/1" method="post">
			<h2 class="tblsite" style="display:inline;"><?php echo $headline; ?> </h2>
			<div class="floatright right">
				<?php if ($smsMod === 'active') { ?>
				<button type="submit" class="open-sms-send" name="send_sms" title="<?php echo uh($send_sms_label); ?>" data-for="booked" data-fair="<?php echo $fair->get('id'); ?>"></button>
				<?php } ?>
				<button type="submit" class="open-excel-export" title="<?php echo uh($export); ?>" name="export_excel" data-for="booked"></button>
			</div>

			<table class="std_table use-scrolltable" id="booked">
				<thead>
					<tr>
						<th class="left"><?php echo $tr_pos; ?></th>
						<th><?php echo $tr_area; ?></th>
						<th class="left"><?php echo $tr_booker; ?></th>
						<th class="left"><?php echo $tr_field; ?></th>
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_last_edited; ?></th>
						<th><?php echo $tr_message; ?></th>
						<th data-sorter="false"><?php echo $tr_review; ?></th>
						<th data-sorter="false"><?php echo $tr_alternatives ?></th>
						<th class="last" data-sorter="false">
							<input type="checkbox" id="check-all-bookings" class="check-all" data-group="rows-1" />
							<label class="squaredFour" for="check-all-bookings" />
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($positions as $pos):?>
					<tr
						data-id="<?php echo $pos['id']; ?>"
						data-revieweurl="<?php echo BASE_URL.'administrator/newReservations/reviewPrelBooking/'; ?>"
						data-categories="<?php echo uh($pos['categories']); ?>"
						data-options="<?php echo uh($pos['options']); ?>"
						data-articles="<?php echo uh($pos['articles']); ?>"
						data-amount="<?php echo uh($pos['amount']); ?>"
						data-categoriesid="<?php echo uh($pos['categoriesid']); ?>"
						data-optionid="<?php echo uh($pos['optionid']); ?>"
						data-optiontext="<?php echo uh($pos['optiontext']); ?>"
						data-optionprice="<?php echo uh($pos['optionprice']); ?>"
						data-optionvat="<?php echo uh($pos['optionvat']); ?>"
						data-articleid="<?php echo uh($pos['articleid']); ?>"
						data-articletext="<?php echo uh($pos['articletext']); ?>"
						data-articleprice="<?php echo uh($pos['articleprice']); ?>"
						data-articlevat="<?php echo uh($pos['articlevat']); ?>"
						data-articleamount="<?php echo uh($pos['articleamount']); ?>"
						data-posstatus="<?php echo uh('2'); ?>"
						data-posname="<?php echo uh($pos['name']); ?>"
						data-posprice="<?php echo uh($pos['price']); ?>"
						data-posinfo="<?php echo uh($pos['information']); ?>"
						data-posvat="<?php echo uh($pos['vat']); ?>"
						data-company="<?php echo uh($pos['company']); ?>"
						data-commodity="<?php echo uh($pos['commodity']); ?>"
						data-message="<?php echo uh($pos['arranger_message']); ?>"
					>
						<td class="left"><?php echo $pos['name']; ?></td>
						<td class="center"><?php echo $pos['area']; ?></td>
						<td class="left"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>" class="showProfileLink"><?php echo $pos['company']; ?></a></td>
						<td class="left"><?php echo $pos['commodity']; ?></td>
						<td><?php echo date('d-m-Y H:i', $pos['booking_time']); ?></td>
						<td><?php echo ($pos['edit_time'] > 0 ? date('d-m-Y H:i', $pos['edit_time']) : $never_edited_label); ?></td>
						<td class="center" title="<?php echo uh($pos['arranger_message']); ?>">
	<?php if (strlen($pos['arranger_message']) > 0): ?>
							<a href="administrator/arrangerMessage/exhibitor/<?php echo $pos['id']; ?>" class="open-arranger-message">
								<img src="<?php echo BASE_URL; ?>images/icons/script.png" class="icon_img" alt="<?php echo $tr_message; ?>" />
							</a>
	<?php endif; ?>
						</td>
						<td class="center">
							<a href="administrator/reviewPrelBooking/<?php echo $pos['id']; ?>" class="open-view-preliminary" title="<?php echo $tr_review; ?>">
								<img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_review; ?>" />
							</a>
						</td>
						<td class="center">
							<a href="#" class="open-list-menu" title="<?php echo $tr_alternatives; ?>">
								<img src="<?php echo BASE_URL; ?>images/icons/settings_32x32.png" class="icon_img" alt="<?php echo $tr_alternatives; ?>" />
							</a>
							<ul class="select-list-menu" style="display:none;">
								<a href="administrator/editBooking/<?php echo $pos['id']; ?>" class="open-edit-booking" title="<?php echo $tr_edit; ?>">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/pencil.png" class="icon_img" alt="<?php echo $tr_edit; ?>" /> <?php echo $tr_edit; ?>
								</li>
								</a>

								<a href="#" class="js-show-comment-dialog" data-user="<?php echo $pos['userid']; ?>" data-fair="<?php echo $pos['fair']; ?>" data-position="<?php echo $pos['position']; ?>" title="<?php echo $tr_comments; ?>">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/notes.png" class="icon_img" alt="<?php echo $tr_comments; ?>" /> <?php echo $tr_comments; ?>
								</li>
								</a>

								<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" target="_blank" title="<?php echo $tr_view; ?>">
								<li>
									<img style="width:2.66em; padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/map_go.png" class="icon_img" alt="<?php echo $tr_view; ?>" /> <?php echo $tr_view; ?>
								</li>
								</a>

								<a style="cursor:pointer;" title="<?php echo $tr_delete; ?>" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'booking')">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/delete.png" class="icon_img" alt="<?php echo $tr_delete; ?>" /> <?php echo $tr_delete; ?>
								</li>
								</a>
							</ul>
						</td>
						<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-1" /><label class="squaredFour" for="<?php echo $pos['id']; ?>" /></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</form>
	<?php } else { ?>
		<p> <?php echo $booked_notfound?> </p>
	<?php }?>
	</div>
	    </div>


	    <div role="tabpanel" class="tab-pane" id="reserved">

	<div id="reserved" style="display:none" class="tab-div tab-div-hidden">

		<?php if(count($rpositions) > 0){?>

		<form action="administrator/exportNewReservations/2" method="post">
			<h2 class="tblsite" style="display:inline;"><?php echo $rheadline; ?> </h2>
			<div class="floatright right">
				<?php if ($smsMod === 'active') { ?>
				<button type="submit" class="open-sms-send" name="send_sms" title="<?php echo uh($send_sms_label); ?>" data-for="reserved" data-fair="<?php echo $fair->get('id'); ?>"></button>
				<?php } ?>
				<button type="submit" class="open-excel-export" title="<?php echo uh($export); ?>" name="export_excel" data-for="reserved"></button>
				<?php if ($invoiceMod === 'active') { ?>
				<button type="submit" class="open-create-invoices" title="<?php echo uh($translator->{'Creates invoices for the selected spaces'}); ?>" name="create_invoices" data-for="reserved"></button>
				<?php } ?>
			</div>

			<table class="std_table use-scrolltable" id="reserved">
				<thead>
					<tr>
						<th class="left"><?php echo $tr_pos; ?></th>
						<th><?php echo $tr_area; ?></th>
						<th class="left"><?php echo $tr_booker; ?></th>
						<th class="left"><?php echo $tr_field; ?></th>
						<!--<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>-->
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_last_edited; ?></th>
						<th><?php echo $tr_message; ?></th>
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_reserved_until; ?></th>
					<?php if ($invoiceMod === 'active') { ?>
						<th><?php echo $tr_invoicestatus; ?></th>
					<?php } ?>
						<th data-sorter="false"><?php echo $tr_review; ?></th>
						<th data-sorter="false"><?php echo $tr_alternatives; ?></th>
						<th class="last" data-sorter="false">
							<input type="checkbox" id="check-all-reserved" class="check-all" data-group="rows-2" />
							<label class="squaredFour" for="check-all-reserved" />
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($rpositions as $pos): ?>
					<tr
						data-id="<?php echo $pos['id']; ?>"
						data-revieweurl="<?php echo BASE_URL.'administrator/newReservations/reviewPrelBooking/'; ?>"
						data-categories="<?php echo uh($pos['categories']); ?>"
						data-options="<?php echo uh($pos['options']); ?>"
						data-articles="<?php echo uh($pos['articles']); ?>"
						data-amount="<?php echo uh($pos['amount']); ?>"
						data-categoriesid="<?php echo uh($pos['categoriesid']); ?>"
						data-optionid="<?php echo uh($pos['optionid']); ?>"
						data-optiontext="<?php echo uh($pos['optiontext']); ?>"
						data-optionprice="<?php echo uh($pos['optionprice']); ?>"
						data-optionvat="<?php echo uh($pos['optionvat']); ?>"
						data-articleid="<?php echo uh($pos['articleid']); ?>"
						data-articletext="<?php echo uh($pos['articletext']); ?>"
						data-articleprice="<?php echo uh($pos['articleprice']); ?>"
						data-articlevat="<?php echo uh($pos['articlevat']); ?>"							
						data-articleamount="<?php echo uh($pos['articleamount']); ?>"
						data-posstatus="<?php echo uh('1'); ?>"
						data-posname="<?php echo uh($pos['name']); ?>"
						data-posprice="<?php echo uh($pos['price']); ?>"
						data-posinfo="<?php echo uh($pos['information']); ?>"
						data-posvat="<?php echo uh($pos['vat']); ?>"
						data-invoicesent="<?php echo uh($pos['invoicesent']); ?>"
						data-invoiceid="<?php echo uh($pos['invoiceid']); ?>"
						data-invoicecreditedid="<?php echo uh($pos['invoicecreditedid']); ?>"
						data-company="<?php echo uh($pos['company']); ?>"
						data-commodity="<?php echo uh($pos['commodity']); ?>"
						data-message="<?php echo uh($pos['arranger_message']); ?>"
						data-expires="<?php echo date('d-m-Y H:i', strtotime($pos['expires'])); ?>"
						data-approveurl="<?php echo BASE_URL.'administrator/approveReservation/'; ?>"
					>
						<td class="left"><?php echo $pos['name']; ?></td>
						<td class="center"><?php echo $pos['area']; ?></td>
						<td class="left"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>" class="showProfileLink"><?php echo $pos['company']; ?></a></td>
						<td class="left"><?php echo $pos['commodity']; ?></td>
						<!--<td><?php echo date('d-m-Y H:i', $pos['booking_time']); ?></td>-->
						<td><?php echo ($pos['edit_time'] > 0 ? date('d-m-Y H:i', $pos['edit_time']) : $never_edited_label); ?></td>
						<td class="center" title="<?php echo uh($pos['arranger_message']); ?>">
		<?php if (strlen($pos['arranger_message']) > 0): ?>
								<a href="administrator/arrangerMessage/exhibitor/<?php echo $pos['id']; ?>" class="open-arranger-message">
									<img src="<?php echo BASE_URL; ?>images/icons/script.png" class="icon_img" alt="<?php echo $tr_message; ?>" />
								</a>
		<?php endif; ?>
						</td>
						<td><?php echo date('d-m-Y H:i', strtotime($pos['expires'])); ?></td>
						<?php if ($invoiceMod === 'active') { ?>
						<td>
							<?php if ($pos['invoicestatus'] == 1): ?>
							<a href="<?php echo BASE_URL.'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$pos['id'].'/'.str_replace('/', '-', $pos['invoicecompany']) . '-' . $pos['invoiceposname'] . '-' . $pos['invoiceid'] . '.pdf'?>" target="_blank" title="<?php echo $tr_viewinvoice; ?>">
								<img style="width:1.833em;" src="<?php echo BASE_URL; ?>images/icons/invoice.png" class="icon_img" />
							</a>
							<?php endif; ?>
							<?php if ($pos['invoicesent'] > 0 && $pos['invoicestatus'] == 1): ?>
							<br><?php echo $tr_sent; ?>
							<?php endif; ?>
							<?php if ($pos['invoicestatus'] == 3): ?>
							<a href="<?php echo BASE_URL.'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$pos['id'].'/'.str_replace('/', '-', $pos['invoicecompany']) . '-' . $pos['invoiceposname'] . '-' . $pos['invoicecreditedid'] . '_credited.pdf'?>" target="_blank" title="<?php echo $tr_viewinvoice; ?>">
								<img style="width:1.833em;" src="<?php echo BASE_URL; ?>images/icons/invoice_credit.png" class="icon_img" />
							</a>
							<?php endif; ?>
							<?php if (!$pos['invoicestatus']): ?>
							-
							<?php endif; ?>
						</td>
						<?php } ?>
						<td class="center">
							<a href="administrator/reviewPrelBooking/<?php echo $pos['id']; ?>" class="open-view-preliminary" title="<?php echo $tr_review; ?>">
								<img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_review; ?>" />
							</a>
						</td>							
						<td class="center">
							<a href="#" class="open-list-menu" title="<?php echo $tr_alternatives; ?>">
								<img src="<?php echo BASE_URL; ?>images/icons/settings_32x32.png" class="icon_img" alt="<?php echo $tr_alternatives; ?>" />
							</a>
							<ul class="select-list-menu" style="display:none;">
								<a href="administrator/editBooking/<?php echo $pos['id']; ?>" class="open-edit-reservation" title="<?php echo $tr_edit; ?>">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/pencil.png" class="icon_img" alt="<?php echo $tr_edit; ?>" /> <?php echo $tr_edit; ?>
								</li>
								</a>

								<a href="#" class="js-show-comment-dialog" data-user="<?php echo $pos['userid']; ?>" data-fair="<?php echo $pos['fair']; ?>" data-position="<?php echo $pos['position']; ?>" title="<?php echo $tr_comments; ?>">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/notes.png" class="icon_img" alt="<?php echo $tr_comments; ?>" /> <?php echo $tr_comments; ?>
								</li>
								</a>

								<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" target="_blank" title="<?php echo $tr_view; ?>">
								<li>
									<img style="width:2.66em; padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/map_go.png" class="icon_img" alt="<?php echo $tr_view; ?>" /> <?php echo $tr_view; ?>
								</li>
								</a>
							<?php if ($invoiceMod === 'active') { ?>
								<?php if ($pos['invoicestatus'] == 0): ?>
								<a onclick="confirmCreateInvoice('<?php echo BASE_URL.'exhibitor/exportBookingPDF/'.$pos['id']?>', '<?php echo $pos["name"]?>', '<?php echo $pos["company"]?>')" title="<?php echo $tr_createinvoice; ?>">
								<li>
									<img style="width:2.66em; padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/invoice.png" class="icon_img" alt="<?php echo $tr_createinvoice; ?>" /> <?php echo $tr_createinvoice; ?>
								</li>
								</a>
								<?php endif; ?>
								<?php if ($pos['invoicestatus'] == 1): ?>
								<a onclick="confirmCreditInvoice('<?php echo BASE_URL.'administrator/creditInvoicePDF/'.$pos['id']?>', '<?php echo $pos["name"]?>', '<?php echo $pos["company"]?>')" title="<?php echo $tr_creditinvoice; ?>">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/invoice_credit.png" class="icon_img" alt="<?php echo $tr_creditinvoice; ?>" /> <?php echo $tr_creditinvoice; ?>
								</li>
								<?php if ($pos['invoicesent'] == 0) { ?>
								</a>
								<a onclick="confirmMarkAsSent('<?php echo BASE_URL.'administrator/markAsSent/'.$pos['id']?>', '<?php echo $pos["name"]?>', '<?php echo $pos["company"]?>', '<?php echo $pos["id"]?>')" title="<?php echo $tr_mark_as_sent; ?>">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/add_green.png" class="icon_img" alt="<?php echo $tr_mark_as_sent; ?>" /> <?php echo $tr_mark_as_sent; ?>
								</li>
								</a>
								<?php } ?>	
								<?php endif; ?>
								<?php if ($pos['invoicestatus'] == 3): ?>
								<a onclick="confirmCancelInvoice('<?php echo BASE_URL.'administrator/cancelInvoicePDF/'.$pos['id']?>', '<?php echo $pos["name"]?>', '<?php echo $pos["company"]?>')" title="<?php echo $tr_cancelinvoice; ?>">
								<li>
									<img style="padding-right:0.3125em;" src="<?php echo BASE_URL; ?>images/icons/invoice_cancel.png" class="icon_img" alt="<?php echo $tr_cancelinvoice; ?>" /> <?php echo $tr_cancelinvoice; ?>
								</li>
								</a>
								<?php endif; ?>
							<?php } ?>
								<a style="cursor:pointer;" title="<?php echo $tr_approve; ?>" onclick="showPopup('book',this)">
								<li>
									<img style="width:2.66em; padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/add.png" class="icon_img" alt="<?php echo $tr_approve; ?>" /> <?php echo $tr_approve; ?>
								</li>
								</a>

								<a style="cursor:pointer;" title="<?php echo $tr_delete; ?>" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'booking')">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/delete.png" class="icon_img" alt="<?php echo $tr_delete; ?>" /> <?php echo $tr_delete; ?>
								</li>
								</a>
							</ul>
						</td>
						<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-invoiceid="<?php echo $pos['invoiceid']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-2" /><label class="squaredFour" for="<?php echo $pos['id']; ?>" /></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</form>

	<?php } else { ?>
		<p> <?php echo $reserv_notfound?> </p>
	<?php }?>
	</div>
	</div>
	    <div role="tabpanel" class="tab-pane" id="reserved_cloned">

	<div id="reserved_cloned" style="display:none" class="tab-div tab-div-hidden">

		<?php if(count($rcpositions) > 0){?>

		<form action="administrator/exportNewReservations/8" method="post">
			<h2 class="tblsite" style="display:inline;"><?php echo $rcheadline; ?> </h2>
			<div class="floatright right">
				<?php if ($smsMod === 'active') { ?>
				<button type="submit" class="open-sms-send" name="send_sms" title="<?php echo uh($send_sms_label); ?>" data-for="reserved_cloned" data-fair="<?php echo $fair->get('id'); ?>"></button>
				<?php } ?>
				<button type="submit" class="open-send-cloned" name="send_cloned" title="<?php echo uh($send_cloned_mail); ?>" data-for="reserved_cloned"></button>
				<button type="submit" class="open-excel-export" title="<?php echo uh($export); ?>" name="export_excel" data-for="reserved_cloned"></button>
			</div>

			<table class="std_table use-scrolltable" id="reserved_cloned">
				<thead>
					<tr>
						<th class="left"><?php echo $tr_pos; ?></th>
						<th><?php echo $tr_area; ?></th>
						<th class="left"><?php echo $tr_booker; ?></th>
						<th class="left"><?php echo $tr_field; ?></th>
						<!--<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>-->
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_last_edited; ?></th>
						<th><?php echo $tr_message; ?></th>
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_reserved_until; ?></th>
						<th><?php echo $tr_linkstatus; ?></th>
						<th data-sorter="false"><?php echo $tr_review; ?></th>
						<th data-sorter="false"><?php echo $tr_alternatives; ?></th>
						<th class="last" data-sorter="false">
							<input type="checkbox" id="check-all-reserved_cloned" class="check-all" data-group="rows-8" />
							<label class="squaredFour" for="check-all-reserved_cloned" />
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($rcpositions as $pos): ?>
					<tr
						data-id="<?php echo $pos['id']; ?>"
						data-revieweurl="<?php echo BASE_URL.'administrator/newReservations/reviewPrelBooking/'; ?>"
						data-categories="<?php echo uh($pos['categories']); ?>"
						data-options="<?php echo uh($pos['options']); ?>"
						data-articles="<?php echo uh($pos['articles']); ?>"
						data-amount="<?php echo uh($pos['amount']); ?>"
						data-categoriesid="<?php echo uh($pos['categoriesid']); ?>"
						data-optionid="<?php echo uh($pos['optionid']); ?>"
						data-optiontext="<?php echo uh($pos['optiontext']); ?>"
						data-optionprice="<?php echo uh($pos['optionprice']); ?>"
						data-optionvat="<?php echo uh($pos['optionvat']); ?>"
						data-articleid="<?php echo uh($pos['articleid']); ?>"
						data-articletext="<?php echo uh($pos['articletext']); ?>"
						data-articleprice="<?php echo uh($pos['articleprice']); ?>"
						data-articlevat="<?php echo uh($pos['articlevat']); ?>"							
						data-articleamount="<?php echo uh($pos['articleamount']); ?>"
						data-posstatus="<?php echo uh('1'); ?>"
						data-posname="<?php echo uh($pos['name']); ?>"
						data-posprice="<?php echo uh($pos['price']); ?>"
						data-posinfo="<?php echo uh($pos['information']); ?>"
						data-posvat="<?php echo uh($pos['vat']); ?>"
						data-invoicesent="<?php echo uh($pos['invoicesent']); ?>"
						data-invoiceid="<?php echo uh($pos['invoiceid']); ?>"
						data-invoicecreditedid="<?php echo uh($pos['invoicecreditedid']); ?>"
						data-company="<?php echo uh($pos['company']); ?>"
						data-commodity="<?php echo uh($pos['commodity']); ?>"
						data-message="<?php echo uh($pos['arranger_message']); ?>"
						data-expires="<?php echo date('d-m-Y H:i', strtotime($pos['expires'])); ?>"
						data-approveurl="<?php echo BASE_URL.'administrator/approveReservation/'; ?>"
					>
						<td class="left"><?php echo $pos['name']; ?></td>
						<td class="center"><?php echo $pos['area']; ?></td>
						<td class="left"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>" class="showProfileLink"><?php echo $pos['company']; ?></a></td>
						<td class="left"><?php echo $pos['commodity']; ?></td>
						<!--<td><?php echo date('d-m-Y H:i', $pos['booking_time']); ?></td>-->
						<td><?php echo ($pos['edit_time'] > 0 ? date('d-m-Y H:i', $pos['edit_time']) : $never_edited_label); ?></td>
						<td class="center" title="<?php echo uh($pos['arranger_message']); ?>">
		<?php if (strlen($pos['arranger_message']) > 0): ?>
								<a href="administrator/arrangerMessage/exhibitor/<?php echo $pos['id']; ?>" class="open-arranger-message">
									<img src="<?php echo BASE_URL; ?>images/icons/script.png" class="icon_img" alt="<?php echo $tr_message; ?>" />
								</a>
		<?php endif; ?>
						</td>
						<td><?php echo date('d-m-Y H:i', strtotime($pos['expires'])); ?></td>
						<td>
							<?php if ($pos['linkstatus'] > 0): ?>
								<?php echo uh($translator->{'Sent'}); ?><br/><?php echo date('d-m-Y H:i', $pos['linkdate']); ?>
							<?php else: ?>
								<?php echo $pos['linkstatus']; ?>
							<?php endif; ?>
						</td>						
						<td class="center">
							<a href="administrator/reviewPrelBooking/<?php echo $pos['id']; ?>" class="open-view-preliminary" title="<?php echo $tr_review; ?>">
								<img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_review; ?>" />
							</a>
						</td>							
						<td class="center">
							<a href="#" class="open-list-menu" title="<?php echo $tr_alternatives; ?>">
								<img src="<?php echo BASE_URL; ?>images/icons/settings_32x32.png" class="icon_img" alt="<?php echo $tr_alternatives; ?>" />
							</a>
							<ul class="select-list-menu" style="display:none;">
								<a href="administrator/editBooking/<?php echo $pos['id']; ?>" class="open-edit-reservation" title="<?php echo $tr_edit; ?>">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/pencil.png" class="icon_img" alt="<?php echo $tr_edit; ?>" /> <?php echo $tr_edit; ?>
								</li>
								</a>

								<a href="#" class="js-show-comment-dialog" data-user="<?php echo $pos['userid']; ?>" data-fair="<?php echo $pos['fair']; ?>" data-position="<?php echo $pos['position']; ?>" title="<?php echo $tr_comments; ?>">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/notes.png" class="icon_img" alt="<?php echo $tr_comments; ?>" /> <?php echo $tr_comments; ?>
								</li>
								</a>

								<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" target="_blank" title="<?php echo $tr_view; ?>">
								<li>
									<img style="width:2.66em; padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/map_go.png" class="icon_img" alt="<?php echo $tr_view; ?>" /> <?php echo $tr_view; ?>
								</li>
								</a>
							<?php if ($invoiceMod === 'active') { ?>
								<?php if ($pos['invoicestatus'] == 0): ?>
								<a onclick="confirmCreateInvoice('<?php echo BASE_URL.'exhibitor/exportBookingPDF/'.$pos['id']?>', '<?php echo $pos["name"]?>', '<?php echo $pos["company"]?>')" title="<?php echo $tr_createinvoice; ?>">
								<li>
									<img style="width:2.66em; padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/invoice.png" class="icon_img" alt="<?php echo $tr_createinvoice; ?>" /> <?php echo $tr_createinvoice; ?>
								</li>
								</a>
								<?php endif; ?>
								<?php if ($pos['invoicestatus'] == 1): ?>
								<a onclick="confirmCreditInvoice('<?php echo BASE_URL.'administrator/creditInvoicePDF/'.$pos['id']?>', '<?php echo $pos["name"]?>', '<?php echo $pos["company"]?>')" title="<?php echo $tr_creditinvoice; ?>">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/invoice_credit.png" class="icon_img" alt="<?php echo $tr_creditinvoice; ?>" /> <?php echo $tr_creditinvoice; ?>
								</li>
								<?php if ($pos['invoicesent'] == 0) { ?>
								</a>
								<a onclick="confirmMarkAsSent('<?php echo BASE_URL.'administrator/markAsSent/'.$pos['id']?>', '<?php echo $pos["name"]?>', '<?php echo $pos["company"]?>', '<?php echo $pos["id"]?>')" title="<?php echo $tr_mark_as_sent; ?>">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/add_green.png" class="icon_img" alt="<?php echo $tr_mark_as_sent; ?>" /> <?php echo $tr_mark_as_sent; ?>
								</li>
								</a>
								<?php } ?>	
								<?php endif; ?>
								<?php if ($pos['invoicestatus'] == 3): ?>
								<a onclick="confirmCancelInvoice('<?php echo BASE_URL.'administrator/cancelInvoicePDF/'.$pos['id']?>', '<?php echo $pos["name"]?>', '<?php echo $pos["company"]?>')" title="<?php echo $tr_cancelinvoice; ?>">
								<li>
									<img style="padding-right:0.3125em;" src="<?php echo BASE_URL; ?>images/icons/invoice_cancel.png" class="icon_img" alt="<?php echo $tr_cancelinvoice; ?>" /> <?php echo $tr_cancelinvoice; ?>
								</li>
								</a>
								<?php endif; ?>
							<?php } ?>
								<a style="cursor:pointer;" title="<?php echo $tr_approve; ?>" onclick="showPopup('book',this)">
								<li>
									<img style="width:2.66em; padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/add.png" class="icon_img" alt="<?php echo $tr_approve; ?>" /> <?php echo $tr_approve; ?>
								</li>
								</a>

								<a style="cursor:pointer;" title="<?php echo $tr_delete; ?>" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'booking')">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/delete.png" class="icon_img" alt="<?php echo $tr_delete; ?>" /> <?php echo $tr_delete; ?>
								</li>
								</a>
							</ul>
						</td>
						<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" data-exid="<?php echo $pos['posid']; ?>" class="rows-8" /><label class="squaredFour" for="<?php echo $pos['id']; ?>" /></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</form>

	<?php } else { ?>
		<p> <?php echo $reserv_cloned_notfound?> </p>
	<?php }?>
	</div>
	</div>
	    <div role="tabpanel" class="tab-pane" id="prel_bookings">

	<div id="prel_bookings" style="display:none" class="tab-div tab-div-hidden">

	<?php if(count($prelpos) > 0){ ?>
		<form action="administrator/exportNewReservations/3" method="post">
			<h2 class="tblsite" style="display:inline;"><?php echo $prel_table; ?> </h2>
			<div class="floatright right">
				<?php if ($smsMod === 'active') { ?>
				<button type="submit" class="open-sms-send" name="send_sms" title="<?php echo uh($send_sms_label); ?>" data-for="prem" data-fair="<?php echo $fair->get('id'); ?>"></button>
				<?php } ?>
				<button type="submit" class="open-excel-export" title="<?php echo uh($export); ?>" name="export_excel" data-for="prem"></button>
			</div>

			<table class="std_table use-scrolltable" id="prem">
				<thead>
					<tr>
						<th class="left"><?php echo $tr_pos; ?></th>
						<th><?php echo $tr_area; ?></th>
						<th class="left"><?php echo $tr_booker; ?></th>
						<th class="left"><?php echo $tr_field; ?></th>
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>
						<th><?php echo $tr_message; ?></th>
						<th data-sorter="false"><?php echo $tr_review; ?></th>
						<th data-sorter="false"><?php echo $tr_alternatives; ?></th>
						<th class="last" data-sorter="false">
							<input type="checkbox" id="check-all-preliminary" class="check-all" data-group="rows-3" />
							<label class="squaredFour" for="check-all-preliminary" />
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($prelpos as $pos): ?>
					<tr
						id="prem" <?php if (isset($page) && $page > 1) echo 'style="display:none;"'; ?>
						data-id="<?php echo $pos['id']; ?>"
						data-approveurl="<?php echo BASE_URL.'administrator/newReservations/approve/'; ?>"
						data-reserveurl="<?php echo BASE_URL.'administrator/reservePrelBooking/'; ?>"
						data-revieweurl="<?php echo BASE_URL.'administrator/newReservations/reviewPrelBooking/'; ?>"
						data-categories="<?php echo uh($pos['categories']); ?>"
						data-options="<?php echo uh($pos['options']); ?>"
						data-articles="<?php echo uh($pos['articles']); ?>"
						data-amount="<?php echo uh($pos['amount']); ?>"
						data-categoriesid="<?php echo uh($pos['categoriesid']); ?>"
						data-optionid="<?php echo uh($pos['optionid']); ?>"
						data-optiontext="<?php echo uh($pos['optiontext']); ?>"
						data-optionprice="<?php echo uh($pos['optionprice']); ?>"
						data-optionvat="<?php echo uh($pos['optionvat']); ?>"
						data-articleid="<?php echo uh($pos['articleid']); ?>"
						data-articletext="<?php echo uh($pos['articletext']); ?>"
						data-articleprice="<?php echo uh($pos['articleprice']); ?>"
						data-articlevat="<?php echo uh($pos['articlevat']); ?>"							
						data-articleamount="<?php echo uh($pos['articleamount']); ?>"
						data-posstatus="<?php echo uh('3'); ?>"
						data-posname="<?php echo uh($pos['name']); ?>"
						data-posprice="<?php echo uh($pos['price']); ?>"
						data-posinfo="<?php echo uh($pos['information']); ?>"
						data-posvat="<?php echo uh($pos['vat']); ?>"
						data-company="<?php echo uh($pos['company']); ?>"
						data-commodity="<?php echo uh($pos['commodity']); ?>"
						data-message="<?php echo uh($pos['arranger_message']); ?>"
					>
						<td class="left"><?php echo $pos['name'];?></td>
						<td class="center"><?php echo $pos['area']; ?></td>
						<td class="left"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>" class="showProfileLink"><?php echo $pos['company']; ?></a></td>
						<td class="left"><?php echo $pos['commodity']; ?></td>
						<td class="center"><?php echo date('d-m-Y H:i', $pos['booking_time']); ?></td>
						<td class="center" title="<?php echo uh($pos['arranger_message']); ?>">
	<?php if (strlen($pos['arranger_message']) > 0): ?>
							<a href="administrator/arrangerMessage/preliminary/<?php echo $pos['id']; ?>" class="open-arranger-message">
								<img src="<?php echo BASE_URL; ?>images/icons/script.png" class="icon_img" alt="<?php echo $tr_message; ?>" />
							</a>
	<?php endif; ?>
						</td>
						<td class="center">
							<a href="administrator/reviewPrelBooking/<?php echo $pos['id']; ?>" class="open-view-preliminary" title="<?php echo $tr_review; ?>">
								<img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_review; ?>" />
							</a>
						</td>
						<td class="center">
							<a href="#" class="open-list-menu" title="<?php echo $tr_alternatives; ?>">
								<img src="<?php echo BASE_URL; ?>images/icons/settings_32x32.png" class="icon_img" alt="<?php echo $tr_alternatives; ?>" />
							</a>
							<ul class="select-list-menu" style="display:none;">
								<a href="#" class="js-show-comment-dialog" data-user="<?php echo $pos['userid']; ?>" data-fair="<?php echo $pos['fair']; ?>" data-position="<?php echo $pos['position']; ?>" title="<?php echo $tr_comments; ?>">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/notes.png" class="icon_img" alt="<?php echo $tr_comments; ?>" /> <?php echo $tr_comments; ?>
								</li>
								</a>

								<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" target="_blank" title="<?php echo $tr_view; ?>">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/map_go.png" class="icon_img" alt="<?php echo $tr_view; ?>" /> <?php echo $tr_view; ?>
								</li>
								</a>

								<a style="cursor:pointer;" title="<?php echo $tr_approve; ?>" onclick="showPopup('book', this)">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/add.png" class="icon_img" alt="<?php echo $tr_approve; ?>" /> <?php echo $tr_approve; ?>
								</li>
								</a>
								<a style="cursor:pointer;" title="<?php echo $tr_reserve; ?>" onclick="showPopup('reserve',this)">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/reserve.png" class="icon_img" alt="<?php echo $tr_reserve; ?>" /> <?php echo $tr_reserve; ?>
								</li>
								</a>

								<a style="cursor:pointer;" title="<?php echo $tr_deny; ?>" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'Preliminary Booking')">
								<li>
									<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/deny.png" class="icon_img" alt="<?php echo $tr_deny; ?>" /> <?php echo $tr_deny; ?>
								</li>
								</a>
							</ul>
						</td>
						<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-3" /><label class="squaredFour" for="<?php echo $pos['id']; ?>" /></td>
					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
		</form>
	<?php } else { ?>
		<p> <?php echo $prel_notfound?> </p>
	<?php }?>
	</div>
	</div>
	    

	    <div role="tabpanel" class="tab-pane" id="prel_bookings_inactive">

	<!-- prelbookings inactive -->
	<div id="prel_bookings_inactive" style="display:none" class="tab-div tab-div-hidden">

	<?php if(count($iprelpos) > 0){ ?>
		<form action="administrator/exportNewReservations/4" method="post">
			<h2 class="tblsite" style="display:inline; color:#000;"><?php echo $prel_table_inactive; ?> </h2>
			<div class="floatright right">
				<?php if ($smsMod === 'active') { ?>
				<button type="submit" class="open-sms-send" name="send_sms" title="<?php echo uh($send_sms_label); ?>" data-for="iprem" data-fair="<?php echo $fair->get('id'); ?>"></button>
				<?php } ?>
				<button type="submit" class="open-excel-export" name="export_excel" title="<?php echo uh($export); ?>" data-for="iprem"></button>
			</div>

			<table class="std_table use-scrolltable" id="iprem">
				<thead>
					<tr>
						<th class="left"><?php echo $tr_pos; ?></th>
						<th><?php echo $tr_area; ?></th>
						<th class="left"><?php echo $tr_booker; ?></th>
						<th class="left"><?php echo $tr_field; ?></th>
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>
						<th><?php echo $tr_message; ?></th>
						<th data-sorter="false"><?php echo $tr_review; ?></th>
						<th data-sorter="false"><?php echo $tr_view; ?></th>
						<th data-sorter="false"><?php echo $tr_deny; ?></th>
						<th class="last" data-sorter="false">
							<input type="checkbox" id="check-all-preliminary-inactive" class="check-all" data-group="rows-4" />
							<label class="squaredFour" for="check-all-preliminary-inactive" />
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach($iprelpos as $pos): ?>
					<tr
						id="iprem" <?php if (isset($page) && $page > 1) echo 'style="display:none;"'; ?>
						data-id="<?php echo $pos['id']; ?>"
						data-revieweurl="<?php echo BASE_URL.'administrator/newReservations/reviewPrelBooking/'; ?>"
						data-approveurl="<?php echo BASE_URL.'administrator/newReservations/approve/'; ?>"
						data-reserveurl="<?php echo BASE_URL.'administrator/reservePrelBooking/'; ?>"
						data-categories="<?php echo uh($pos['categories']); ?>"
						data-options="<?php echo uh($pos['options']); ?>"
						data-articles="<?php echo uh($pos['articles']); ?>"
						data-amount="<?php echo uh($pos['amount']); ?>"
						data-categoriesid="<?php echo uh($pos['categoriesid']); ?>"
						data-optionid="<?php echo uh($pos['optionid']); ?>"
						data-optiontext="<?php echo uh($pos['optiontext']); ?>"
						data-optionprice="<?php echo uh($pos['optionprice']); ?>"
						data-optionvat="<?php echo uh($pos['optionvat']); ?>"
						data-articleid="<?php echo uh($pos['articleid']); ?>"
						data-articletext="<?php echo uh($pos['articletext']); ?>"
						data-articleprice="<?php echo uh($pos['articleprice']); ?>"
						data-articlevat="<?php echo uh($pos['articlevat']); ?>"							
						data-articleamount="<?php echo uh($pos['articleamount']); ?>"
						data-posstatus="<?php echo uh('3'); ?>"
						data-posname="<?php echo uh($pos['name']); ?>"
						data-posprice="<?php echo uh($pos['price']); ?>"
						data-posinfo="<?php echo uh($pos['information']); ?>"
						data-posvat="<?php echo uh($pos['vat']); ?>"
						data-company="<?php echo uh($pos['company']); ?>"
						data-commodity="<?php echo uh($pos['commodity']); ?>"
						data-message="<?php echo uh($pos['arranger_message']); ?>"
					>
						<td class="left"><?php echo $pos['name'];?></td>
						<td class="center"><?php echo $pos['area']; ?></td>
						<td class="left"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>" class="showProfileLink"><?php echo $pos['company']; ?></a></td>
						<td class="left"><?php echo $pos['commodity']; ?></td>
						<td class="center"><?php echo date('d-m-Y H:i', $pos['booking_time']); ?></td>
						<td class="center" title="<?php echo uh($pos['arranger_message']); ?>">
	<?php if (strlen($pos['arranger_message']) > 0): ?>
							<a href="administrator/arrangerMessage/preliminary/<?php echo $pos['id']; ?>" class="open-arranger-message">
								<img src="<?php echo BASE_URL; ?>images/icons/script.png" class="icon_img" alt="<?php echo $tr_message; ?>" />
							</a>
	<?php endif; ?>
						</td>
						<td class="center">
							<a href="administrator/reviewPrelBooking/<?php echo $pos['id']; ?>" class="open-view-preliminary" title="<?php echo $tr_review; ?>">
								<img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_review; ?>" />
							</a>
						</td>
						<td class="center">
							<a href="<?php echo BASE_URL.'mapTool/map/'.$pos['fair'].'/'.$pos['position'].'/'.$pos['map']?>" target="_blank" title="<?php echo $tr_view; ?>">
								<img src="<?php echo BASE_URL; ?>images/icons/map_go.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
							</a>
						</td>						
						<td class="center">
							<a style="cursor:pointer;" title="<?php echo $tr_deny; ?>" onclick="denyPrepPosition('<?php echo BASE_URL.'administrator/deleteBooking/'.$pos['id'].'/'.$pos['position']; ?>', '<?php echo $pos['name']?>', 'Preliminary Booking')">
								<img style="padding:0em 0.416em 0em 0.416em" src="<?php echo BASE_URL; ?>images/icons/deny.png" class="icon_img" alt="<?php echo $tr_deny; ?>" />
							</a>
						</td>
						
						<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-4" /><label class="squaredFour" for="<?php echo $pos['id']; ?>" /></td>
					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
		</form>
	<?php } else { ?>
		<p> <?php echo $prel_notfound?> </p>
	<?php }?>
	</div>

	</div>

	<div role="tabpanel" class="tab-pane" id="prel_bookings_deleted">
	<!-- History of deleted Preliminary Bookings -->
		<div id="prel_bookings_deleted" style="display:none" class="tab-div tab-div-hidden">

		<?php if(count($del_prelpos) > 0){ ?>
			<form action="administrator/exportNewReservations/6" method="post">
				<h2 class="tblsite" style="display:inline; color:#FF0000;"><?php echo $prel_table_deleted; ?></h2>
				<div class="floatright right">
					<?php if ($smsMod === 'active') { ?>
					<button type="submit" class="open-sms-send" name="send_sms" title="<?php echo uh($send_sms_label); ?>" data-for="delprem" data-fair="<?php echo $fair->get('id'); ?>"></button>
					<?php } ?>
					<button type="submit" class="open-excel-export" name="export_excel" title="<?php echo uh($export); ?>" data-for="delprem"></button>
				</div>

				<table class="std_table use-scrolltable" id="delprem">
					<thead>
						<tr>
							<th class="left"><?php echo $tr_pos; ?></th>
							<th><?php echo $tr_area; ?></th>
							<th class="left"><?php echo $tr_booker; ?></th>
							<th class="left"><?php echo $tr_field; ?></th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>
							<th><?php echo $tr_message; ?></th>
							<th data-sorter="false"><?php echo $tr_review; ?></th>
							<th class="last" data-sorter="false">
								<input type="checkbox" id="check-all-preliminary-deleted" class="check-all" data-group="rows-6" />
								<label class="squaredFour" for="check-all-preliminary-deleted" />
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($del_prelpos as $pos): ?>
						<tr
							id="delprem" <?php if (isset($page) && $page > 1) echo 'style="display:none;"'; ?>
							data-id="<?php echo $pos['id']; ?>"
							data-categories="<?php echo uh($pos['categories']); ?>"
							data-optionid="<?php echo uh($pos['optionid']); ?>"
							data-optiontext="<?php echo uh($pos['optiontext']); ?>"
							data-optionprice="<?php echo uh($pos['optionprice']); ?>"
							data-optionvat="<?php echo uh($pos['optionvat']); ?>"
							data-articleid="<?php echo uh($pos['articleid']); ?>"
							data-articletext="<?php echo uh($pos['articletext']); ?>"
							data-articleprice="<?php echo uh($pos['articleprice']); ?>"
							data-articlevat="<?php echo uh($pos['articlevat']); ?>"							
							data-articleamount="<?php echo uh($pos['articleamount']); ?>"
							data-posstatus="<?php echo uh('3'); ?>"
							data-posname="<?php echo uh($pos['name']); ?>"
							data-posprice="<?php echo uh($pos['price']); ?>"
							data-posinfo="<?php echo uh($pos['information']); ?>"
							data-posvat="<?php echo uh($pos['vat']); ?>"
							data-company="<?php echo uh($pos['company']); ?>"
							data-commodity="<?php echo uh($pos['commodity']); ?>"
							data-message="<?php echo uh($pos['arranger_message']); ?>"
						>
							<td class="left"><?php echo $pos['name'];?></td>
							<td class="center"><?php echo $pos['area']; ?></td>
							<td class="left"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>" class="showProfileLink"><?php echo $pos['company']; ?></a></td>
							<td class="left"><?php echo $pos['commodity']; ?></td>
							<td class="center"><?php echo date('d-m-Y H:i', $pos['booking_time']); ?></td>
							<td class="center" title="<?php echo uh($pos['arranger_message']); ?>">
		<?php if (strlen($pos['arranger_message']) > 0): ?>
								<a href="administrator/arrangerMessage/history_preliminary/<?php echo $pos['id']; ?>" class="open-arranger-message">
									<img src="<?php echo BASE_URL; ?>images/icons/script.png" class="icon_img" alt="<?php echo $tr_message; ?>" />
								</a>
		<?php endif; ?>
							<td class="center">
								<a href="administrator/reviewPrelBooking/<?php echo $pos['id']; ?>" class="open-view-preliminary" title="<?php echo $tr_review; ?>">
									<img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_review; ?>" />
								</a>
							</td>		
							<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-6" /><label class="squaredFour" for="<?php echo $pos['id']; ?>" /></td>
					<?php endforeach;?>
					</tbody>
				</table>
			</form>
		<?php } else { ?>
			<p> <?php echo $del_prel_notfound?> </p>
		<?php }?>
		</div>
	</div>

		<div role="tabpanel" class="tab-pane" id="bookings_deleted">
		<div id="bookings_deleted" style="display:none" class="tab-div tab-div-hidden">

		<?php if(count($del_positions) > 0){ ?>

			<form action="administrator/exportNewReservations/7" method="post">
				<h2 class="tblsite" style="display:inline;"><?php echo uh($translator->{'Deleted history tab'}); ?> </h2>
				<div class="floatright right">
					<?php if ($smsMod === 'active') { ?>
					<button type="submit" class="open-sms-send" name="send_sms" title="<?php echo uh($send_sms_label); ?>" data-for="delbookings" data-fair="<?php echo $fair->get('id'); ?>"></button>
					<?php } ?>
					<button type="submit" class="open-excel-export" name="export_excel" title="<?php echo uh($export); ?>" data-for="delbookings"></button>
				</div>

				<table class="std_table use-scrolltable" id="delbookings">
					<thead>
						<tr>
							<th class="left"><?php echo $tr_status; ?></th>
							<th class="left"><?php echo $tr_pos; ?></th>
							<th><?php echo $tr_area; ?></th>
							<th class="left"><?php echo $tr_booker; ?></th>
							<th class="left"><?php echo $tr_field; ?></th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_last_edited; ?></th>
							<th><?php echo $tr_message; ?></th>
							<th data-sorter="false"><?php echo $tr_review; ?></th>	
							<th data-sorter="false"><?php echo $tr_comments; ?></th>
							<th class="last" data-sorter="false">
								<input type="checkbox" id="check-all-bookings-deleted" class="check-all" data-group="rows-7" />
								<label class="squaredFour" for="check-all-bookings-deleted" />
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($del_positions as $pos):?>
						<tr
							data-categories="<?php echo uh($pos['categories']); ?>"
							data-optionid="<?php echo uh($pos['optionid']); ?>"
							data-optiontext="<?php echo uh($pos['optiontext']); ?>"
							data-optionprice="<?php echo uh($pos['optionprice']); ?>"
							data-optionvat="<?php echo uh($pos['optionvat']); ?>"
							data-articleid="<?php echo uh($pos['articleid']); ?>"
							data-articletext="<?php echo uh($pos['articletext']); ?>"
							data-articleprice="<?php echo uh($pos['articleprice']); ?>"
							data-articlevat="<?php echo uh($pos['articlevat']); ?>"							
							data-articleamount="<?php echo uh($pos['articleamount']); ?>"
							data-posname="<?php echo uh($pos['name']); ?>"
							data-posprice="<?php echo uh($pos['price']); ?>"
							data-posinfo="<?php echo uh($pos['information']); ?>"
							data-posvat="<?php echo uh($pos['vat']); ?>"
							data-company="<?php echo uh($pos['company']); ?>"
							data-commodity="<?php echo uh($pos['commodity']); ?>"
							data-message="<?php echo uh($pos['arranger_message']); ?>"
						>
							<td class="left"><?php if ($pos['status'] === '2') echo $booked_label; else if ($pos['status'] === '1') echo $reserved_label; else echo $unknown_label; ?></td>
							<td class="left"><?php echo $pos['name']; ?></td>
							<td class="center"><?php echo $pos['area']; ?></td>
							<td class="left"><a href="exhibitor/profile/<?php echo $pos['userid']; ?>" class="showProfileLink"><?php echo $pos['company']; ?></a></td>
							<td class="left"><?php echo $pos['commodity']; ?></td>
							<td><?php echo date('d-m-Y H:i', $pos['booking_time']); ?></td>
							<td><?php echo ($pos['edit_time'] > 0 ? date('d-m-Y H:i', $pos['edit_time']) : $never_edited_label); ?></td>
							<td class="center" title="<?php echo uh($pos['arranger_message']); ?>">
		<?php if (strlen($pos['arranger_message']) > 0): ?>
								<a href="administrator/arrangerMessage/history_deleted/<?php echo $pos['id']; ?>" class="open-arranger-message">
									<img src="<?php echo BASE_URL; ?>images/icons/script.png" class="icon_img" alt="<?php echo $tr_message; ?>" />
								</a>
		<?php endif; ?>
							</td>
							<td class="center">
								<a href="administrator/reviewPrelBooking/<?php echo $pos['id']; ?>" class="open-view-preliminary" title="<?php echo $tr_review; ?>">
									<img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_review; ?>" />
								</a>
							</td>						
							<td class="center">
								<a href="#" class="js-show-comment-dialog" data-user="<?php echo $pos['userid']; ?>" data-fair="<?php echo $pos['fair']; ?>" data-position="<?php echo $pos['position']; ?>" title="<?php echo $tr_comments; ?>">
									<img src="<?php echo BASE_URL; ?>images/icons/notes.png" class="icon_img" alt="<?php echo $tr_comments; ?>" />
								</a>
							</td>
							<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $pos['id']; ?>" data-userid="<?php echo $pos['userid']; ?>" class="rows-7" /><label class="squaredFour" for="<?php echo $pos['id']; ?>" /></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</form>
		<?php } else { ?>
			<p> <?php echo $booked_notfound?> </p>
		<?php }?>
		</div>
	    </div>


	<div role="tabpanel" class="tab-pane" id="fair_registrations">

	<!-- Registrations -->
	<div id="fair_registrations" style="display:none" class="tab-div tab-div-hidden">

	<?php if (count($fair_registrations) > 0): ?>
		<form action="administrator/exportNewReservations/5" method="post">
			<h2 class="tblsite" style="display:inline;"><?php echo $fair_registrations_headline; ?> </h2>
			<div class="floatright right">
				<?php if ($smsMod === 'active') { ?>
				<button type="submit" class="open-sms-send" name="send_sms" title="<?php echo uh($send_sms_label); ?>" data-for="fair_registrations" data-fair="<?php echo $fair->get('id'); ?>"></button>
				<?php } ?>
				<button type="submit" class="open-excel-export" name="export_excel" title="<?php echo uh($export); ?>" data-for="fair_registrations"></button>
			</div>

			<table class="std_table use-scrolltable" id="fair_registrations">
				<thead>
					<tr>
						<th><?php echo $tr_area; ?></th>
						<th class="left"><?php echo $tr_booker; ?></th>
						<th class="left"><?php echo $tr_field; ?></th>
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>
						<th><?php echo $tr_message; ?></th>
						<th data-sorter="false"><?php echo $tr_review; ?></th>
						<th data-sorter="false"><?php echo $tr_alternatives; ?></th>
						<th class"last" data-sorter="false">
							<input type="checkbox" id="check-all-registrations" class="check-all" data-group="rows-5" />
							<label class="squaredFour" for="check-all-registrations" />
						</th>
					</tr>
				</thead>
				<tbody>
	<?php	foreach ($fair_registrations as $registration): ?>
					<tr data-id="<?php echo $registration['id']; ?>"
							data-revieweurl="<?php echo BASE_URL.'administrator/newReservations/reviewPrelBooking/'; ?>"
							data-categories="<?php echo uh($registration['categories']); ?>"
							data-optionid="<?php echo uh($registration['optionid']); ?>"
							data-optiontext="<?php echo uh($registration['optiontext']); ?>"
							data-optionprice="<?php echo uh($registration['optionprice']); ?>"
							data-optionvat="<?php echo uh($registration['optionvat']); ?>"
							data-articleid="<?php echo uh($registration['articleid']); ?>"
							data-area="<?php echo uh($registration['area']); ?>"
							data-articletext="<?php echo uh($registration['articletext']); ?>"
							data-articleprice="<?php echo uh($registration['articleprice']); ?>"
							data-articlevat="<?php echo uh($registration['articlevat']); ?>"							
							data-articleamount="<?php echo uh($registration['articleamount']); ?>"
							data-company="<?php echo uh($registration['company']); ?>"
							data-commodity="<?php echo uh($registration['commodity']); ?>"
							data-message="<?php echo uh($registration['arranger_message']); ?>"
						>
						<td class="center"><?php echo uh($registration['area']); ?></td>
						<td class="left"><a href="exhibitor/profile/<?php echo $registration['user']; ?>" class="showProfileLink"><?php echo uh($registration['company']); ?></a></td>
						<td class="left"><?php echo uh($registration['commodity']); ?></td>
						<td class="center"><?php echo date('d-m-Y H:i', $registration['booking_time']); ?></td>
						<td class="center" title="<?php echo uh($registration['arranger_message']); ?>">
	<?php		if (strlen($registration['arranger_message']) > 0): ?>
							<a href="administrator/arrangerMessage/registration/<?php echo $registration['id']; ?>" class="open-arranger-message">
								<img src="<?php echo BASE_URL; ?>images/icons/script.png" class="icon_img" alt="<?php echo $tr_message; ?>" />
							</a>
	<?php		endif; ?>
						</td>
							<td class="center">
								<a href="administrator/reviewPrelBooking/<?php echo $registration['id']; ?>" class="open-view-preliminary" title="<?php echo $tr_review; ?>">
									<img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_review; ?>" />
								</a>
							</td>
						<td class="center">
							<a href="#" class="open-list-menu" title="<?php echo $tr_alternatives; ?>">
								<img src="<?php echo BASE_URL; ?>images/icons/settings_32x32.png" class="icon_img" alt="<?php echo $tr_alternatives; ?>" />
							</a>
							<ul class="select-list-menu" style="display:none;">
								<a href="mapTool/pasteRegistration/<?php echo $registration['fair'] . '/' . $registration['id']; ?>" target="_blank" title="<?php echo $tr_copy; ?>">
									<li>
										<img src="<?php echo BASE_URL; ?>images/icons/map_ex_copy.png" class="icon_img" alt="<?php echo $tr_copy; ?>" /> <?php echo $tr_copy; ?>
									</li>
								</a>
								<a style="cursor:pointer;" title="<?php echo $tr_delete; ?>" onclick="denyPrepRegistration('<?php echo BASE_URL.'administrator/deleteRegistration/'.$registration['id']; ?>', '<?php echo $registration['company']; ?>')">
									<li>
										<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/deny.png" class="icon_img" alt="<?php echo $tr_delete; ?>" /> <?php echo $tr_delete; ?>
									</li>
								</a>
								<a href="#" class="js-show-comment-dialog" data-user="<?php echo $registration['user']; ?>" data-fair="<?php echo $registration['fair']; ?>" data-position="-1" title="<?php echo $tr_comments; ?>">
									<li>
										<img style="padding-right:0.416em;" src="<?php echo BASE_URL; ?>images/icons/notes.png" class="icon_img" alt="<?php echo $tr_comments; ?>" /> <?php echo $tr_comments; ?>
									</li>
								</a>
							</ul>
						</td>
						<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $registration['id']; ?>" data-userid="<?php echo $registration['user']; ?>" class="rows-5" /><label class="squaredFour" for="<?php echo $registration['id']; ?>" /></td>
					</tr>
	<?php	endforeach; ?>
				</tbody>
			</table>
		</form>
	<?php else: ?>
		<p><?php echo $fregistrations_notfound; ?></p>
	<?php endif; ?>
	</div>
	</div>
	</div>
</div>
	<div role="tabpanel" class="tab-pane" id="fair_registrations_deleted">

	<!-- Registrations -->
	<div id="fair_registrations_deleted" style="display:none" class="tab-div tab-div-hidden">

	<?php if (count($fair_registrations_deleted) > 0): ?>
		<form action="administrator/exportNewReservations/9" method="post">
			<h2 class="tblsite" style="display:inline;"><?php echo $fair_registrations_deleted_headline; ?> </h2>
			<div class="floatright right">
				<?php if ($smsMod === 'active') { ?>
				<button type="submit" class="open-sms-send" name="send_sms" title="<?php echo uh($send_sms_label); ?>" data-for="fair_registrations_deleted" data-fair="<?php echo $fair->get('id'); ?>"></button>
				<?php } ?>
				<button type="submit" class="open-excel-export" name="export_excel" title="<?php echo uh($export); ?>" data-for="fair_registrations_deleted"></button>
			</div>

			<table class="std_table use-scrolltable" id="fair_registrations_deleted">
				<thead>
					<tr>
						<th><?php echo $tr_area; ?></th>
						<th class="left"><?php echo $tr_booker; ?></th>
						<th class="left"><?php echo $tr_field; ?></th>
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_time; ?></th>
						<th><?php echo $tr_message; ?></th>
						<th data-sorter="false"><?php echo $tr_review; ?></th>
						<th data-sorter="false"><?php echo $tr_comments; ?></th>
						<th class"last" data-sorter="false">
							<input type="checkbox" id="check-all-registrations_deleted" class="check-all" data-group="rows-9" />
							<label class="squaredFour" for="check-all-registrations_deleted" />
						</th>
					</tr>
				</thead>
				<tbody>
	<?php	foreach ($fair_registrations_deleted as $registration): ?>
					<tr data-id="<?php echo $registration['id']; ?>"
							data-revieweurl="<?php echo BASE_URL.'administrator/newReservations/reviewPrelBooking/'; ?>"
							data-categories="<?php echo uh($registration['categories']); ?>"
							data-optionid="<?php echo uh($registration['optionid']); ?>"
							data-optiontext="<?php echo uh($registration['optiontext']); ?>"
							data-optionprice="<?php echo uh($registration['optionprice']); ?>"
							data-optionvat="<?php echo uh($registration['optionvat']); ?>"
							data-articleid="<?php echo uh($registration['articleid']); ?>"
							data-area="<?php echo uh($registration['area']); ?>"
							data-articletext="<?php echo uh($registration['articletext']); ?>"
							data-articleprice="<?php echo uh($registration['articleprice']); ?>"
							data-articlevat="<?php echo uh($registration['articlevat']); ?>"							
							data-articleamount="<?php echo uh($registration['articleamount']); ?>"
							data-company="<?php echo uh($registration['company']); ?>"
							data-commodity="<?php echo uh($registration['commodity']); ?>"
							data-message="<?php echo uh($registration['arranger_message']); ?>"
						>
						<td class="center"><?php echo uh($registration['area']); ?></td>
						<td class="left"><a href="exhibitor/profile/<?php echo $registration['user']; ?>" class="showProfileLink"><?php echo uh($registration['company']); ?></a></td>
						<td class="left"><?php echo uh($registration['commodity']); ?></td>
						<td class="center"><?php echo date('d-m-Y H:i', $registration['booking_time']); ?></td>
						<td class="center" title="<?php echo uh($registration['arranger_message']); ?>">
	<?php		if (strlen($registration['arranger_message']) > 0): ?>
							<a href="administrator/arrangerMessage/history_registration/<?php echo $registration['id']; ?>" class="open-arranger-message">
								<img src="<?php echo BASE_URL; ?>images/icons/script.png" class="icon_img" alt="<?php echo $tr_message; ?>" />
							</a>
	<?php		endif; ?>
						</td>
						<td class="center">
							<a href="administrator/reviewPrelBooking/<?php echo $registration['id']; ?>" class="open-view-preliminary" title="<?php echo $tr_review; ?>">
								<img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_review; ?>" />
							</a>
						</td>
						<td class="center">
							<a href="#" class="js-show-comment-dialog" data-user="<?php echo $registration['user']; ?>" data-fair="<?php echo $registration['fair']; ?>" data-position="-1" title="<?php echo $tr_comments; ?>">
								<img src="<?php echo BASE_URL; ?>images/icons/notes.png" class="icon_img" alt="<?php echo $tr_comments; ?>" />
							</a>
						</td>
						<td class="last"><input type="checkbox" name="rows[]" value="<?php echo $registration['id']; ?>" data-userid="<?php echo $registration['user']; ?>" class="rows-9" /><label class="squaredFour" for="<?php echo $registration['id']; ?>" /></td>
					</tr>
	<?php	endforeach; ?>
				</tbody>
			</table>
		</form>
	<?php else: ?>
		<p><?php echo $fregistrations_deleted_notfound; ?></p>
	<?php endif; ?>
	</div>
	</div>
	</div>
</div>

<?php else: ?>
	<p>Du är inte behörig att administrera den här mässan.</p>
<?php endif; ?>
<div class="modal">
  <div style="margin-top: 50vh;">
  <img src="../images/ajax-loader.gif" style="margin-bottom: 0.5em;">
	<p><?php echo uh($translator->{'Loading...'}); ?></p>
	<!-- Place at bottom of page -->
  </div>
</div>