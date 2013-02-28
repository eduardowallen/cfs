function isValidEmailAddress(emailAddress) {
	var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
	return pattern.test(emailAddress);
};
$(document).ready(function()  {
	prepFormChecker();
});

function prepFormChecker() {
	$("form div #email").keyup(function() {
		if (isValidEmailAddress($(this).val())) {
			$(this).css('border', '1px solid #00FF00');
			var input = $(this);
			input.data('valid', true);
			if (input.val() != input.attr('value')) {
				$.ajax({
					url: 'ajax/maptool.php',
					type: 'POST',
					data: 'emailExists=1&email=' + input.val(),
					success: function(response) {
						var ans = JSON.parse(response);
						if (ans.emailExists) {
							input.css('border', '1px solid #FF0000');
							input.data('valid', false);
						} else {
							input.css('border', '1px solid #00FF00');
							input.data('valid', true);
						}
					}
				});
			}
		} else {
			$(this).css('border', '1px solid #FF0000');
			$(this).data('valid', false);
		}
	});

	$("form div #invoice_email").keyup(function() {
		if (isValidEmailAddress($(this).val())) {
			$(this).css('border', '1px solid #00FF00');
			var input = $(this);
			input.data('valid', true);
			if (input.val() != input.attr('value')) {
				$.ajax({
					url: 'ajax/maptool.php',
					type: 'POST',
					data: 'emailExists=1&email=' + input.val(),
					success: function(response) {
						var ans = JSON.parse(response);
						if (ans.emailExists) {
							input.css('border', '1px solid #FF0000');
							input.data('valid', false);
						} else {
							input.css('border', '1px solid #00FF00');
							input.data('valid', true);
						}
					}
				});
			}
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

					//ZIP input
					if (input.attr("name") == "zipcode" && !input.val().match(/^\d{3}(\s|-)\d+$/)) {
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
					if (input.is('textarea') && input.val() == '') {
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
			alert("Det finns " + errors.length + " fel i formuläret.\nDu måste fylla i alla fält markerade med *");
			return false;
		} else {
			return true;
		}
		
	});
}