function isValidEmailAddress(emailAddress) {
	var pattern = new RegExp(/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i);
	return pattern.test(emailAddress);
};
$(document).ready(function()  {
	prepFormChecker();
});

function prepFormChecker() {
	$("form #email").data('valid', true);
	$("form #email").keyup(function() {
		if (isValidEmailAddress($(this).val())) {
			$(this).css('border', '1px solid #00FF00');
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
								$('.email_err').remove();
								input.css('border', '1px solid #FF0000');
								input.after("<p class=\"email_err\">asdasd</p>");
								input.data('valid', false);
							} else {
								input.css('border', '1px solid #00FF00');
								$('#email_err').remove();
								input.data('valid', true);
							}
						}
					});
				}
			}
		} else {
			$(this).css('border', '1px solid #FF0000');
			$(this).data('valid', false);
		}
	});

	$("form #invoice_email").data('valid', true);
	$("form #invoice_email").keyup(function() {
		if (isValidEmailAddress($(this).val())) {
			$(this).css('border', '1px solid #00FF00');
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
								$('.email_err').remove();
								input.css('border', '1px solid #FF0000');
								input.after("<p class=\"email_err\">asdasd</p>");
								input.data('valid', false);
							} else {
								input.css('border', '1px solid #00FF00');
								input.data('valid', true);
							}
						}
					});
				}
			}
		} else {
			$(this).css('border', '1px solid #FF0000');
			$(this).data('valid', false);
		}

	});
	
	$("form #contact_email").data('valid', true);
	$("form #contact_email").keyup(function() {
		if (isValidEmailAddress($(this).val())) {
			$(this).css('border', '1px solid #00FF00');
			var input = $(this);

			input.data('valid', true);
		} else {
			$(this).css('border', '1px solid #FF0000');

			$(this).data('valid', false);
		}
	});

	$("form").submit(function() {
		
		var thisForm = $(this);
		var errors = new Array();		
		
		$("label", thisForm).each(function() {

			//Reset all fields to ok
			$(this).css("color", "#000000");
			
			//Exclude hidden fields
			if ($(this).parent().parent().is(":visible")) {
				
				var label = $(this).text();
				
				if (label.substring(label.length-1) == '*') {
					
					var input = $("#" + $(this).attr("for"));
					
					//Text and password inputs
					if ((input.attr("type") == 'text' || input.attr("type") == 'password') && input.val() == '') {
						//Mark empty
						$(this).css("color", "red");
						errors.push($(this).attr("for"));
					}
					
					//Email addresses)
					if (input.attr("name") == "email" && (!isValidEmailAddress(input.val()) || !input.data('valid'))) {
						$(this).css("color", "red");
						errors.push($(this).attr("for"));
					}
					
					//Email addresses 2
					if (input.attr("name") == "invoice_email" && (!isValidEmailAddress(input.val()) || !input.data('valid'))) {
						$(this).css("color", "red");
						errors.push($(this).attr("for"));
					}

					//Email addresses 3
					if (input.attr("name") == "contact_email" && (!isValidEmailAddress(input.val()) || !input.data('valid'))) {
						$(this).css("color", "red");
						errors.push($(this).attr("for"));
					}

					//Date inputs
					if (input.hasClass('date') && !input.val().match(/^(\d\d-\d\d-\d\d\d\d)$/)) {
						$(this).css("color", "red");
						errors.push($(this).attr("for"));
					}
					
					//Checkboxes
					if (input.attr("type") == 'checkbox' && !input.is(":checked")) {
						//Mark empty
						$(this).css("color", "red");
						errors.push($(this).attr("for"));
					}
					
					//Textareas
					if (input.is('textarea') && input.html() == '') {
						//Mark empty
						$(this).css("color", "red");
						errors.push($(this).attr("for"));
					}
					
					//Selects
					if (input.is('select') && (input.val() == 0 || input.val() == '')) {
						//Mark empty
						$(this).css("color", "red");
						errors.push($(this).attr("for"));
					}
					
				}
				
			}
			
		});
		
		if (errors.length > 0) {
			var str = "There are # errors in the form. You have to enter information in all the fields marked with a *";

			$.ajax({
				url: 'ajax/translate.php',
				type: 'POST',
				data: {'query':str},
				success: function(result){
					str = result;
					var err = str.replace('#', errors.length);
					alert(err);
				}
			});
			return false;
		} else {
			return true;
		}
		
	});
}
