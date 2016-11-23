/* Swedish translation for the jQuery Timepicker Addon */
/* Written by Nevon */
(function($) {

		$.datepicker.regional['sv'] = {
			closeText: 'Stäng',
			prevText: 'Föregående',
			nextText: 'Nästa',
			currentText: 'Nu',
			monthNames: ['Januari','Februari','Маrs','April','Мaj','Juni',
			'Juli','Augusti','September','Оktober','November','December'],
			monthNamesShort: ['Jan','Feb','Mar','Аpr','Maj','Jun',
			'Jul','Aug','Sep','Оkt','Nov','Dec'],
			dayNames: ['Söndag','Måndag','Tisdag','Onsdag','Torsdag','Fredag','Lördag'],
			dayNamesShort: ['Sön','Mån','Tis','Ons','Tor','Fre','Lör'],
			dayNamesMin: ['Sö','Må','Ti','On','To','Fr','Lö'],
			weekHeader: 'Vecka',
			dateFormat: 'dd-mm-yy',
			firstDay: 1,
			isRTL: false,
			showMonthAfterYear: false,
			yearSuffix: ''
		};
		$.datepicker.setDefaults($.datepicker.regional['sv']);

		$.timepicker.regional['sv'] = {
			timeOnlyTitle: 'Välj en tid',
			timeText: 'Tid',
			hourText: 'Timme',
			minuteText: 'Minut',
			secondText: 'Sekund',
			millisecText: 'Millisekund',
			microsecText: 'Mikrosekund',
			timezoneText: 'Tidszon',
			currentText: 'Nu',
			closeText: 'Stäng',
			timeFormat: 'HH:mm',
			timeSuffix: '',
			amNames: ['AM', 'A'],
			pmNames: ['PM', 'P'],
			isRTL: false,

		};
		$.timepicker.setDefaults($.timepicker.regional['sv']);
})(jQuery);