/* Funktioner för att visa popuper under newReservations! Hämtar data från tabellen och placerar i en popup! */
function showPopup(type, activator){
	
	var row = $(activator).parent().parent();

	if(type == "reserve"){
		var link = row.find('.reserve').text();
		reservePopup(row, link);
	}

	if(type == "book"){
		var link = row.find('.approve').text();
		bookPopup(row, link);
		
	}
}

function reservePopup(row, link){
	$('#reserve_position_dialogue').css('display', 'block');
	var infoArray = new Array(7);

	row.children().each(function(i){
		if(i < 7){
			infoArray[i] = $(this).text();
		}
	});
	var categories = infoArray[6];
	var catArr = categories.split("|");

	$('#reserve_category_scrollbox > p').each(function(){
		var categoryValue = $(this).children().val();
		for(var i = 0; i < catArr.length; i++){
			if(categoryValue == catArr[i]){
				$(this).children().attr('checked', 'checked');
			}
		}
	});

	$('#reserve_user').text(infoArray[2]);
	$('#reserve_commodity_input').val(infoArray[3]);
	$('#reserve_message_input').val(infoArray[5]);

	$('#reserve_post').attr('href', link);	
}

function bookPopup(row, link){
	$('#book_position_dialogue').css('display', 'block');
	
	var infoArray = new Array(8);

	row.children().each(function(i){
		if(i < 8){
			infoArray[i] = $(this).text();
		}
	});

	var categories = infoArray[6];
	
	var catArr = categories.split("|");

	$('#book_category_scrollbox > p').each(function(){
		var categoryValue = $(this).children().val();
		for(var i = 0; i < catArr.length; i++){
			if(categoryValue == catArr[i]){
				$(this).children().attr('checked', 'checked');
			}
		}
	});

	$('#book_user').text(infoArray[2]);
	$('#book_commodity_input').val(infoArray[3]);
	$('#book_message_input').val(infoArray[5]);	

	$('#book_post').attr('href', link);
}

function closeDialogue(pref){
	$('#'+pref+'_position_dialogue').css('display', 'none');
}

function denyPosition(link, comment){
	if(confirm(confirmDialogue)){
		$.ajax({
			url : link,
			type: 'POST',
			data: 'comment='+comment
		})
		window.location = 'administrator/newReservations';
	}
}

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
	$('.datepicker').datepicker();
	$('.datepicker').datepicker('option', 'dateFormat', 'dd-mm-yy');
	$('.datepicker').each(function() {
		$(this).datepicker('setDate', $(this).attr('value'));
	});
	$('#languages a.selected').attr('href', 'javascript:void(0)').append('&nbsp;&nbsp;<img src="images/arrow_down.png" alt=""/>').prependTo('#languages');	
	$('.loginlink').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		$('#overlay').show();

		var url = $(this).attr('href');
		var html = '<form action="' + url + '" method="post" id="popupform">'
				 + 		'<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0 0 0 268px;"/>'
				 + 		'<div><label for="user">' + lang.login_username + '</label>'
				 + 		'<input type="text" name="user" id="user"/>'
				 + 		'<label for="pass">' + lang.login_password + '</label>'
				 + 		'<input type="password" name="pass" id="pass"/>'
				 +		'<p style="width:101% !important;"><a href="user/resetPassword">' + lang.forgot_pass + '</a></p>'
				 + 		'<p><input type="submit" name="login" value="Sign in"/></p></div>'
				 + 	'</form>';
		
		$('body').prepend(html);
		$(".closeDialogue").click(function() {
			$('#popupform').remove();
			$('#overlay').hide();
		});
		
		return false;
		
	});
	
	$('.contactLink').click(function() {
		var splitted = $(this).attr('class').split(" ");
		$('#overlay').show();
		if(splitted[1] == null){
			var link = 'page/contact';
		} else {
			var link = 'page/contact/'+splitted[1];
		}

		var ajxReq = $.ajax({
			url : link,
			method : 'GET',
		}).done(function(reqResp){
			var html = '<div id="popupformTwo" style="height:auto; min-width:400px; width:auto; padding:20px; top:50px;"></div>';
			var filteredResponse = $(reqResp).find('#content').html();

			$('body').append(html);
			var popupform = $('#popupformTwo');
			popupform.html('<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0;"/>' + filteredResponse);
var closeButton = $('.closeDialogue');
			popupform.css('text-align', 'left');
			$('#popupformTwo > table > tbody > tr > td > p').css('text-align', 'left');
			
			$('.closeDialogue').click(function(){
				$(this).off('click');
				$(this).remove();
				$('#popupformTwo').remove();
				$('#overlay').hide();
			});

			if(popupform.width() > 760){
				popupform.css('width', 760);		
			}

			popupform.css('left', '50%');
			popupform.css('margin-left', (popupform.width() + 48)/-2);
			var d = popupform.width() - 15;
			closeButton.css('left', d);
		});	
	});

	$('.helpLink').click(function(){
		$('#overlay').show();
		var ajxReq = $.ajax({
			url : 'page/help',
			method : 'GET',
		}).done(function(reqResp){
			var html = '<div id="popupformTwo" style="padding:20px; margin:0 auto; top:30px;"></div>';
			var filteredResponse = $(reqResp).find('#content').html();
			$('body').append(html);
			var popupform = $('#popupformTwo');	
			
			popupform.html('<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0;"/>' + filteredResponse);
			var closeButton = $('.closeDialogue');
			$('#popupform > p').css('text-align', 'left');
			$('.closeDialogue').click(function(){
				$(this).off('click');
				$(this).remove();
				$('#popupformTwo').remove();
				$('#overlay').hide();
			});
			if(popupform.width() > 760){
				popupform.css('width', 760);		
			}
			popupform.css('left', '50%');
			popupform.css('margin-left', (popupform.width() + 48)/-2);
			var d = popupform.width() - 15;
			closeButton.css('left', d);

		});	
	});

	$('.helpOrgLink').click(function(){
		$('#overlay').show();
		var ajxReq = $.ajax({
			url : 'page/help_organizer',
			method : 'GET',
		}).done(function(reqResp){
			var html = '<div id="popupform" style="width:500px; height:auto; padding:20px; margin:0 0 0 -250px; top:50px; height:none;"></div>';
			var filteredResponse = $(reqResp).find('#content').html();
			$('body').append(html);
			$('#popupform').html('<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0;"/>' + filteredResponse);
			$('#popupform > p').css('text-align', 'left');
			$('.closeDialogue').click(function(){
				$(this).off('click');
				$('#popupform').remove();
				$('#overlay').hide();
			});
		});	
	});


	$('.registerlink').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		$('#overlay').show();
		var states = new Array("Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burma", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo, Democratic Republic", "Congo, Republic of the", "Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Greenland", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Mongolia", "Morocco", "Monaco", "Mozambique", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Samoa", "San Marino", " Sao Tome", "Saudi Arabia", "Senegal", "Serbia and Montenegro", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "Spain", "Sri Lanka", "Sudan", "Suriname", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe");


		var url = $(this).attr('href');
		var html = '<form action="' + url + '" method="post" id="popupform_register">'
				 + 		'<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:-17px 0 0 764px;"/>'
				 + 		'<div class="form_column">'
				 +				'<h3>' + lang.company_section + '</h3>'
							
				 +				'<label for="orgnr">' + lang.orgnr_label + ' *</label>'
				 +				'<input type="text" name="orgnr" id="orgnr"/>'
							
				 +				'<label for="company">' + lang.company_label + ' *</label>'
				 +				'<input type="text" name="company" id="company"/>'
							
				+				'<label for="commodity">'+ lang.commodity_label +'</label>'
				+				'<textarea rows="3" style="width:250px; height:40px; resize:none;" name="commodity" id="commodity"></textarea>'
							
				 +				'<label for="address">' + lang.address_label + ' *</label>'
				 +				'<input type="text" name="address" id="address"/>'
							
				 +				'<label for="zipcode">' + lang.zipcode_label + ' *</label>'
				 +				'<input type="text" name="zipcode" id="zipcode"/>'
							
				 +				'<label for="city">' + lang.city_label + ' *</label>'
				 +				'<input type="text" name="city" id="city"/>'
				 +				'<label for="country">' + lang.country_label + ' *</label>'
				+				'<select name="country" id="country" style="width:258px;">';
				for(var i = 0; i<states.length; i++){
				 html = html + '<option value="'+states[i]+'">'+states[i]+'</option>';
				 
				}
				  html = html +				'</select><label for="phone1">' + lang.phone1_label + ' *</label>'
				 +				'<input type="text" name="phone1" id="phone1"/>'
							
				 +				'<label for="phone2">' + lang.phone2_label + '</label>'
				 +				'<input type="text" name="phone2" id="phone2"/>'
							
				 +				'<label for="fax">' + lang.fax_label + '</label>'
				 +				'<input type="text" name="fax" id="fax"/>'
							
				 +				'<label for="email">' + lang.email_label + ' *</label>'
				 +				'<input type="text" name="email" id="email"/>'
							
				 +				'<label for="website">' + lang.website_label + '</label>'
				 +				'<input type="text" name="website" id="website"/>'
							
				+				'<div style="margin-top:30px;">'
				 +				'<label for="presentation">' + lang.presentation_label + '</label>'
				 +				'<textarea name="presentation" id="presentation" class="presentation"></textarea>'
				 +				'</div>'
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
				 
				 +				'<label for="phone3">' + lang.phone3_label + '</label>'
				 +				'<input type="text" name="phone3" id="phone3"/>'

				 +				'<label for="phone4">' + lang.phone4_label + '</label>'
				 +				'<input type="text" name="phone4" id="phone4"/>'

				 +				'<label for="contact_email">' + lang.contact_email + ' *</label>' 
				 +				'<input type="text" name="contact_email" id="contact_email"/>'
		
				 +				'<label for="password">' + lang.password_label + ' *</label>'
				 +				'<input type="password" name="password" id="password" class="hasIndicator"/>'
							
				 +				'<label for="password_repeat">' + lang.password_repeat_label + ' *</label>'
				 +				'<input type="password" name="password_repeat" id="password_repeat"/>'
		
				
				 +			'<p style="position:relative; left:280px; bottom:95px; display:inline-block; width:180px; background:#efefef; border:1px solid #b1b1b1; padding:10px; margin-right:0px;">'  + lang.password_standard + '</p>'
				 +			'</div>'
				 +				'<p><input type="submit" name="save" value="' + lang.save_label + '"/></p>'
				
				 + 	'</form>';
		
		$('body').prepend(html);

	  	tinyMCE.init({
	        //General options
	        mode : "specific_textareas",
		editor_selector : "presentation",
	        theme : "advanced",
	        plugins : "style,table,advimage,advlink,inlinepopups,insertdatetime,preview,paste,fullscreen,noneditable,visualchars,xhtmlxtras",

	        // Theme option
	        theme_advanced_toolbar_location : "top",
	        theme_advanced_toolbar_align : "left",
	        theme_advanced_statusbar_location : "bottom",
	        theme_advanced_resizing : false,
	        paste_text_sticky: true,
			paste_text_sticky_default: true,

	        /*content_css : "../css/tiny.css",*/

	        //Drop lists for link/image/media/template dialogs
	        template_external_list_url : "js/template_list.js",
	        external_link_list_url : "js/link_list.js",
	        external_image_list_url : "js/image_list.js",
	        media_external_list_url : "js/media_list.js",
		width : 565,
		});

		hookUpPasswdMeter();
		prepFormChecker();
		
		$('#password').keyup(function(){
			var password = $(this).val();

			var numbers = /\d{2,}/.test(password);
			var capitalLetter = /[A-ZÅÄÖ]{1,}/.test(password);

			if(numbers){
				if(capitalLetter){
					
					$(this).css('border', 'solid 1px #00FF00');
				} else {
					
					$(this).css('border', 'solid 1px #FF0000');
				}
			} else {
				$(this).css('border', 'solid 1px #FF0000');
			}
		});

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
			$("#overlay").hide();
			$("#popupform").remove();
			$("#popupform_register").remove();
			$('#popupformTwo').remove();
			$("#newMarkerIcon").remove();
		});
		
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
