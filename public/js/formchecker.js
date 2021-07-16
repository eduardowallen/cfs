var aliasTimer;

function isValidEmailAddress(emailAddress) {
	var pattern = new RegExp(/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i);
	return pattern.test(emailAddress);
}
$(document).ready(function()  {
	prepFormChecker();
});

function noWhiteSpaces() {
	if(!/[0-9a-zA-Z-]/.test(String.fromCharCode(e.which)))
	return false;
}
/*
function forceLower(string) 
{
return string.value.toLowerCase();
}​
*/
function removeSpaces(string) {
 return string.split(' ').join('');
}

function prepFormChecker() {

$(".column33 #price").poshytip({
		className: 'tip-yellowsimple',
		showOn: 'focus',
		alignTo: 'target',
		alignX: 'right',
		alignY: 'center',
		offsetX: 10,
		showTimeout: 100
});
$("#position_price_input").poshytip({
		className: 'tip-yellowsimple',
		showOn: 'focus',
		alignTo: 'target',
		alignX: 'right',
		alignY: 'center',
		offsetX: 10,
		showTimeout: 100
});
  
	$('#form_column1 :input, #form_column2 :input, #form_column3 :input, #form_column4 :input, #form_column5 :input, #form_column6 :input').poshytip({
		className: 'tip-yellowsimple',
		showOn: 'focus',
		alignTo: 'target',
		alignX: 'right',
		alignY: 'center',
		offsetX: 10,
		showTimeout: 100
	});
		
	$('fieldset > #column > :input, fieldset > :input').poshytip({
		className: 'tip-yellowsimple',
		showOn: 'focus',
		alignTo: 'target',
		alignX: 'left',
		alignY: 'center',
		offsetX: 10,
		showTimeout: 100
	});
	
	$("form #email").data('valid', true);

	$("#popupform_register").keydown(function(e) {
		if (e.keyCode == 13) {
			e.preventDefault();
		}
	});

	$("form #email").on('keyup focus', function() {
		if (isValidEmailAddress($(this).val())) {
			$(this).removeClass("input_error");
			var input = $(this);
			input.data('valid', true);

			if (!$(this).hasClass('nocheckdb')) {
				if (input.val() != input.attr('value')) {
					$.ajax({
						url: 'ajax/maptool.php',
						type: 'POST',
						data: 'emailExists=1&email=' + input.val(),
						success: function(response) {
							var ans = JSON.parse(response);
							if (ans.emailExists) {
								input.poshytip('update', lang.email_exists, true);
								input.removeClass("input_ok");
								input.addClass("input_error emailExists");
								input.data('valid', false);
							} else {
								input.poshytip('update');
								input.removeClass("input_error emailExists");
								input.addClass("input_ok");
								input.data('valid', true);
							}
						}
					});
				}
			}
		} else {
			$(this).removeClass("input_ok");
			$(this).addClass("input_error");
			$(this).data('valid', false);
		}
	});

	$("#alias").data('valid', true);
	
	$("#alias").on('keyup focus', function() {
		var input = $(this);
		input.removeClass('input_error');
		input.addClass('input_ok');
		input.data('valid', true);
		input.val($(this).val().toLowerCase());

		if (typeof aliasTimer !== "undefined") {
			window.clearTimeout(aliasTimer);
		}

		//Wait 250ms in case of more inputs
		aliasTimer = window.setTimeout(function () {
			$.ajax({
				url: "ajax/maptool.php",
				type: "POST",
				data: {
					aliasExists: 1,
					alias: input.val()
				},
				dataType: "json",
				success: function (response) {
					if (response.aliasExists) {
						$('#alias').poshytip('update', lang.alias_exists, true);
						input.removeClass('input_ok');
						input.addClass('input_error');
						input.data("valid", false);
					} else if(!/^[a-z-_0-9]+$/.test(input.val())) {
						$('#alias').poshytip('update', lang.alias_err, true);
						input.removeClass('input_ok');
						input.addClass('input_error');
						input.data("valid", false);
					} else if(input.val().length < 4) {
						$('#alias').poshytip('update', lang.alias_short_err, true);
						input.removeClass('input_ok');
						input.addClass('input_error');
						input.data("valid", false);
					} else {
						$('#alias').poshytip('update');
						input.removeClass('input_error');
						input.addClass('input_ok');
						input.data('valid', true);
					}
				}
			});

		}, 250);
	});
	$("form #invoice_email, form #email, form #alias, form #contact_email").on('paste', function() {
		var element = $(this);
		setTimeout(function() {
			var pasteFixed = element.val();
			element.val(pasteFixed.replace(/\s/g,''));
		}, 0);
	});
	$("form #invoice_email").data('valid', true);
	$("form #invoice_email").on('keyup focus', function() {
		if (isValidEmailAddress($(this).val())) {
			$(this).removeClass("input_error");
			$(this).addClass("input_ok");
			var input = $(this);

			input.data('valid', true);
		} else {
			$(this).removeClass("input_ok");
			$(this).addClass("input_error");

			$(this).data('valid', false);
		}
	});


	
	$("form #contact_email").data('valid', true);
	$("form #contact_email").on('keyup focus', function() {
		if (isValidEmailAddress($(this).val())) {
			$(this).removeClass("input_error");
			$(this).addClass("input_ok");
			var input = $(this);

			input.data('valid', true);
		} else {
			$(this).removeClass("input_ok");
			$(this).addClass("input_error");

			$(this).data('valid', false);
		}
	});

	$("form").submit(function() {
		
		var thisForm = $(this);
		thisForm.data('valid', true);
		var errors = [];
		var error_list = '';
		
		$("label", thisForm).each(function() {
			
			//Exclude hidden fields
			if ($(this).parent().parent().is(":visible")) {
				
				var input = $("#" + $(this).attr("for"));
				var label = $(this).text();
				//Reset all fields to ok
				input.removeClass("input_error");
				input.addClass("input_ok");

				if (label.substring(label.length-1) == '*') {
					
					input = $("#" + $(this).attr("for"));
					if (input.attr('name') == 'password_repeat') {
						if ($('#password_repeat').val() != $('#password').val()) {
							//Mark empty
							input.removeClass("input_ok");
							input.addClass("input_error");
							errors.push($(this).attr("for"));
							error_list += lang.passwd_match_err + '<br/>';
						}
					}

					//Text and password inputs
					if (((input.attr("type") == 'text') || input.attr("type") == 'password') && input.val() == '' && input.attr('name') != 'invoice_email') {
						//Mark empty
						input.removeClass("input_ok");
						input.addClass("input_error");
						errors.push($(this).attr("for"));

						if (input.attr('name') == 'name')
							error_list += lang.name_err + '<br/>';

						if (input.attr('name') == 'orgnr')
							error_list += lang.orgnr_err + '<br/>';

						if (input.attr('name') == 'company')
							error_list += lang.company_err + '<br/>';

						if (input.attr('name') == 'address')
							error_list += lang.address_err + '<br/>';

						if (input.attr('name') == 'zipcode')
							error_list += lang.zipcode_err + '<br/>';

						if (input.attr('name') == 'city')
							error_list += lang.city_err + '<br/>';

						if (input.attr('name') == 'invoice_company')
							error_list += lang.invoice_company_err + '<br/>';

						if (input.attr('name') == 'invoice_address')
							error_list += lang.invoice_address_err + '<br/>';

						if (input.attr('name') == 'invoice_zipcode')
							error_list += lang.invoice_zipcode_err + '<br/>';

						if (input.attr('name') == 'invoice_city')
							error_list += lang.invoice_city_err + '<br/>';

						if (input.attr('name') == 'password')
							error_list += lang.passwd_empty_err + '<br/>';

						if (input.attr('name') == 'password_repeat')
							error_list += lang.passwd_repeat_err + '<br/>';
					}
					
					//Email addresses)
					if (input.attr("name") == "email" && (!isValidEmailAddress(input.val()) || !input.data('valid'))) {
						input.removeClass("input_ok");
						input.addClass("input_error");
						errors.push($(this).attr("for"));
						error_list += lang.email_err + '<br/>';
					}
					
					//Email addresses 2
					if (input.attr("name") == "invoice_email" && (!isValidEmailAddress(input.val()) || !input.data('valid'))) {
						input.removeClass("input_ok");
						input.addClass("input_error");
						errors.push($(this).attr("for"));
						error_list += lang.invoice_email_err + '<br/>';
					}

					//Email addresses 3
					if (input.attr("name") == "contact_email" && (!isValidEmailAddress(input.val()) || !input.data('valid'))) {
						input.removeClass("input_ok");
						input.addClass("input_error");
						errors.push($(this).attr("for"));
						error_list += lang.contact_email_err + '<br/>';
					}

					//Alias input
					if (input.attr("name") == "alias" && !input.data('valid') && !input.prop('disabled')) {
						input.removeClass("input_ok");
						input.addClass("input_error");
						errors.push($(this).attr("for"));
						if (input.val().length < 4) {
							error_list += lang.alias_short_err + '<br/>';
						} else {
							error_list += lang.alias_err + '<br/>';
						}
					}

					//Date inputs
					if (input.hasClass('date') && !input.val().match(/^(\d\d-\d\d-\d\d\d\d)$/)) {
						input.removeClass("input_ok");
						input.addClass("input_error");
						errors.push($(this).attr("for"));
						if (input.val().length < 1) {
							error_list += lang.date_missing_err + '<br/>';
						} else {
							error_list += lang.date_err + '<br/>';
						}
					}
					
					//Checkboxes
					if (input.attr("type") == 'checkbox' && !input.is(":checked")) {
						//Mark empty
						input.removeClass("input_ok");
						input.addClass("input_error");
						errors.push($(this).attr("for"));
					}
					
					//Textareas
					if (input.is('textarea') && input.val() == '') {
						//Mark empty
						input.removeClass("input_ok");
						input.addClass("input_error");
						errors.push($(this).attr("for"));
						error_list += lang.commodity_err + '<br/>';
					}
					
					//Selects
					if (input.is('select') && (input.val() == 0 || input.val() == '')) {
						//Mark empty
						input.removeClass("input_ok");
						input.addClass("input_error");
						errors.push($(this).attr("for"));
						error_list += lang.country_err + '<br/>';
					}

					if (input.hasClass('phone-val') && !/^\+?[\d]{5,20}$/.test(input.val())) {
						input.removeClass("input_ok");
						input.addClass("input_error");
						errors.push($(this).attr("for"));
						error_list += lang.phone_err + '<br/>';
					}
				}
			}
			
		});
		
		if (errors.length > 0) {
			thisForm.data('valid', false);
			console.log(error_list);
			console.log(errors.length);
			$.alert({
			    title: lang.form_err.replace('#', errors.length),
			    content: error_list,
			});
			return false;
		} else {
			return true;
		}
		
	});

}
