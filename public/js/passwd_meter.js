$(document).ready(function() {
	hookUpPasswdMeter();	
});

function hookUpPasswdMeter() {
	$('.hasIndicator').each(function() {
		
		$(this).removeClass('hasIndicator');
		var input = $(this);
		var meter = $('<span class="passwd_meter"/>');
		var help_icon = input.siblings('.helpicon_map');

		if (help_icon.length > 0) {
			help_icon.after(meter);
		} else {
			input.after(meter);
		}
		
		meter.css({
<<<<<<< HEAD
			paddingRight: '10px',
=======
			paddingRight: '5px',
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
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
			
			if (strength > 4) {
				meter.css('color', 'green').text(lang.passwd_superstrong);
				$(this).css('border', 'solid 1px #00FF00')
			} else if (strength > 3) {
				meter.css('color', 'green').text(lang.passwd_strong);
				$(this).css('border', 'solid 1px #00FF00')
			} else if (strength > 2) {
				meter.css('color', 'orange').text(lang.passwd_medium);
<<<<<<< HEAD
				$(this).css('border', 'solid 1px #00FF00')
			} else {
				meter.css('color', 'red').text(lang.passwd_weak);
				$(this).css('border', 'solid 1px #00FF00')
=======
				$(this).css('border', 'solid 1px #FF0000')
			} else {
				meter.css('color', 'red').text(lang.passwd_weak);
				$(this).css('border', 'solid 1px #FF0000')
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
			}
			
			
			if (strength > 1) {
				$('input[type="submit"]', input.parent()).removeAttr('disabled');
			} else {
				$('input[type="submit"]', input.parent()).attr('disabled', 'disabled');
			}
			
				
		});
		
	});
}
