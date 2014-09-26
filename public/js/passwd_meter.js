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
			paddingRight: '5px',
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
				$(this).css('border', 'solid 1px #FF0000')
			} else {
				meter.css('color', 'red').text(lang.passwd_weak);
				$(this).css('border', 'solid 1px #FF0000')
			}
			
			
			if (strength > 3) {
				$('input[type="submit"]', input.parent()).removeAttr('disabled');
			} else {
				$('input[type="submit"]', input.parent()).attr('disabled', 'disabled');
			}
			
				
		});
		
	});
}
