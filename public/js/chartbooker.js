function ajaxContent(e, handle) {
	e.preventDefault();
	e.stopPropagation();
	$.ajax({
		url: 'ajax/page.php',
		type: 'POST',
		data: 'ajaxContent=' + handle,
		success: function(response) {
			if (response != '') {
				
				$('#popupform').remove();
				maptool.closeDialogues();
				
				$('#overlay').show();
				var html = '<div class="dialogue" style="display:block;"><img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>' + response + '</div>';
				$('body').prepend(html);
				
				$(".closeDialogue").click(function() {
					$('.dialogue').remove();
					$('#overlay').hide();
				});
				
			}
			
		}
	});
	return false;
}

$(document).ready(function() {
	
	$('#languages a.selected').attr('href', 'javascript:void(0)').append('&nbsp;&nbsp;<img src="images/arrow_down.png" alt=""/>').prependTo('#languages');
	
	$('.loginlink').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		$('#overlay').show();
		
		var url = $(this).attr('href');
		var html = '<form action="' + url + '" method="post" id="popupform">'
				 + 		'<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0 0 0 268px;"/>'
				 + 		'<p><label for="user">' + lang.login_username + '</label>'
				 + 		'<input type="text" name="user" id="user"/></p>'
				 + 		'<p><label for="pass">' + lang.login_password + '</label>'
				 + 		'<input type="password" name="pass" id="pass"/></p>'
				 + 		'<p><input type="submit" name="login" value="Sign in"/></p>'
				 + 	'</form>';
		
		$('body').prepend(html);
		$(".closeDialogue").click(function() {
			$('#popupform').remove();
			$('#overlay').hide();
		});
		
		return false;
		
	});
	
	$('.registerlink').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		$('#overlay').show();
		
		var url = $(this).attr('href');
		var html = '<form action="' + url + '" method="post" id="popupform_register">'
				 + 		'<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:-17px 0 0 764px;"/>'
				 + 		'<div class="form_column">'
				 +				'<h3>' + lang.company_section + '</h3>'
							
				 +				'<label for="orgnr">' + lang.orgnr_label + ' *</label>'
				 +				'<input type="text" name="orgnr" id="orgnr"/>'
							
				 +				'<label for="company">' + lang.company_label + ' *</label>'
				 +				'<input type="text" name="company" id="company"/>'
							
				 +				'<label for="commodity">' + lang.commodity_label + ' *</label>'
				 +				'<input type="text" name="commodity" id="commodity"/>'
							
				 +				'<label for="address">' + lang.address_label + ' *</label>'
				 +				'<input type="text" name="address" id="address"/>'
							
				 +				'<label for="zipcode">' + lang.zipcode_label + ' *</label>'
				 +				'<input type="text" name="zipcode" id="zipcode"/>'
							
				 +				'<label for="city">' + lang.city_label + ' *</label>'
				 +				'<input type="text" name="city" id="city"/>'
							
				 +				'<label for="country">' + lang.country_label + ' *</label>'
				 +				'<input type="text" name="country" id="country"/>'
							
				 +				'<label for="phone1">' + lang.phone1_label + ' *</label>'
				 +				'<input type="text" name="phone1" id="phone1"/>'
							
				 +				'<label for="phone2">' + lang.phone2_label + '</label>'
				 +				'<input type="text" name="phone2" id="phone2"/>'
							
				 +				'<label for="phone3">' + lang.phone3_label + '</label>'
				 +				'<input type="text" name="phone3" id="phone3"/>'
							
				 +				'<label for="fax">' + lang.fax_label + '</label>'
				 +				'<input type="text" name="fax" id="fax"/>'
							
				 +				'<label for="email">' + lang.email_label + ' *</label>'
				 +				'<input type="text" name="email" id="email"/>'
							
				 +				'<label for="website">' + lang.website_label + '</label>'
				 +				'<input type="text" name="website" id="website"/>'
							
				 +				'<label for="presentation">' + lang.presentation_label + '</label>'
				 +				'<textarea name="presentation" id="presentation"></textarea>'
				 +				'</div>'
							
				 +				'<div class="form_column">'
							
				 +				'<h3>' + lang.invoice_section + '</h3>'
								
				 +				'<input type="checkbox" id="copy"/><label class="inline-block" for="copy">' + lang.copy_label + '</label>'
							
				 +				'<label for="invoice_company">' + lang.invoice_company_label + ' *</label>'
				 +				'<input type="text" name="invoice_company" id="invoice_company"/>'
							
				 +				'<label for="invoice_address">' + lang.invoice_address_label + ' *</label>'
				 +				'<input type="text" name="invoice_address" id="invoice_address"/>'
							
				 +				'<label for="invoice_zipcode">' + lang.invoice_zipcode_label + ' *</label>'
				 +				'<input type="text" name="invoice_zipcode" id="invoice_zipcode"/>'
							
				 +				'<label for="invoice_city">' + lang.invoice_city_label + ' *</label>'
				 +				'<input type="text" name="invoice_city" id="invoice_city"/>'
							
				 +				'<label for="invoice_email">' + lang.invoice_email_label + ' *</label>'
				 +				'<input type="text" name="invoice_email" id="invoice_email"/>'
							
				 +				'<h3 style="margin-top:20px">' + lang.contact_section + '</h3>'
							
				 +				'<label for="username">' + lang.alias_label + ' *</label>'
				 +				'<input type="text" name="username" id="username"/>'
							
				 +				'<label for="name">' + lang.contact_label + ' *</label>'
				 +				'<input type="text" name="name" id="name"/>'
							
				 +				'<label for="password">' + lang.password_label + ' *</label>'
				 +				'<input type="password" name="password" id="password" class="hasIndicator"/>'
							
				 +				'<label for="password_repeat">' + lang.password_repeat_label + ' *</label>'
				 +				'<input type="password" name="password_repeat" id="password_repeat"/>'
		
				 +				'<p><input type="submit" name="save" value="' + lang.save_label + '"/></p>'
				 +			'</div>'
				 +			'<p style="display:inline-block; width:160px; background:#efefef; border:1px solid #b1b1b1; padding:10px; margin-right:0px;">' + lang.password_standard + '</p>'
				 + 	'</form>';
		
		$('body').prepend(html);

		hookUpPasswdMeter();
		prepFormChecker();
		
		$("#copy").change(function() {
			if ($(this).is(":checked")) {
				$('#invoice_company').val($('#company').val());
				$('#invoice_address').val($('#address').val());
				$('#invoice_zipcode').val($('#zipcode').val());
				$('#invoice_city').val($('#city').val());
				$('#invoice_email').val($('#email').val());
			} else {
				$('#invoice_company').val("");
				$('#invoice_address').val("");
				$('#invoice_zipcode').val("");
				$('#invoice_city').val("");
				$('#invoice_email').val("");
			}
		});
		
		$(".closeDialogue").click(function() {
			$('#popupform_register').remove();
			$('#overlay').hide();
		});
		
		return false;
		
	});
	
	$('a img').each(function() {
		
		var icon = $(this);
		
		if (icon.attr("src").indexOf('pencil') > 0)
			var str = 'Edit';
		else if (icon.attr("src").indexOf('delete') > 0)
			var str = 'Delete';
		else if (icon.attr("src").indexOf('map_go') > 0)
			var str = 'View';
		else if (icon.attr("src").indexOf('user') > 0)
			var str = 'Details';
		else if (icon.attr("src").indexOf('map_edit') > 0)
			var str = 'Edit';
		else if (icon.attr("src").indexOf('print') > 0)
			var str = 'Print';
		else
			var str = '';
		
		if (str != '') {
			$.ajax({
				url: 'ajax/translate.php',
				type: 'POST',
				dataType : 'html',
				data: {'query':str},
				success: function(result){
					icon.attr('title', result);
				}
			});
		}
		
	});
	
	$(document).ready(function() {
	$("#copy").change(function() {
		if ($(this).is(":checked")) {
			$('#invoice_company').val($('#company').val());
			$('#invoice_address').val($('#address').val());
			$('#invoice_zipcode').val($('#zipcode').val());
			$('#invoice_city').val($('#city').val());
			$('#invoice_email').val($('#email').val());
		} else {
			$('#invoice_company').val("");
			$('#invoice_address').val("");
			$('#invoice_zipcode').val("");
			$('#invoice_city').val("");
			$('#invoice_email').val("");
		}
	});
});
	
});