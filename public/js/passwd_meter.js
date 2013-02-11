$(document).ready(function() {
	hookUpPasswdMeter();	
});

function hookUpPasswdMeter() {
	$('.hasIndicator').each(function() {
		
		var input = $(this);
		var meter = $('<span class="passwd_meter"/>');
		
		input.after(meter);
		
		meter.css({
			paddingLeft: '10px',
			fontWeight: 'bold'
		});
		
		input.keyup(function() {
			
			var passwd = input.val();
			var strength = 0;
			
			if (passwd.match(/[a-zåäö]+/)) {
				strength++;
			}
			if (passwd.match(/[A-ZÅÄÖ]+/)) {
				strength++;
			}
			if (passwd.match(/[\d]+/)) {
				strength++;
			}
			if (passwd.match(/[^a-zåäöA-ZÅÄÖ\d]+/)) {
				strength++;
			}
			if (passwd.length > 7) {
				strength++;
			}
			
			if (strength > 4)
				meter.css('color', 'green').text('Super strong');
			else if (strength > 3)
				meter.css('color', 'green').text('Strong');
			else if (strength > 2)
				meter.css('color', 'orange').text('Medium');
			else
				meter.css('color', 'red').text('Weak');
			
			
			if (strength > 3) {
				$('input[type="submit"]', input.parent()).removeAttr('disabled');
			} else {
				$('input[type="submit"]', input.parent()).attr('disabled', 'disabled');
			}
			
				
		});
		
	});
}