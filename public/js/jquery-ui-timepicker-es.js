/* Spanish translation for the jQuery Timepicker Addon */
/* Written by Heastost */
(function($) {

        $.datepicker.regional['es'] = {
            closeText: 'Cierre',
            prevText: 'Anterior',
            nextText: 'Proximo',
            currentText: 'Ahora',
            monthNames: ['Enero','Febrero','Маrzo','Abril','Мayo','Junio',
            'Julio','Agosto','Setiembre','Оctubre','Noviembre','Diciembre'],
            monthNamesShort: ['Ene','Feb','Mar','Аbr','May','Jun',
            'Jul','Ago','Set','Оct','Nov','Dic'],
            dayNames: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],
            dayNamesShort: ['Dom','Lun','Mar','Mie','Jue','Vie','Sáb'],
            dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sa'],
            weekHeader: 'Semana',
            dateFormat: 'dd-mm-yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: ''
        };
        $.datepicker.setDefaults($.datepicker.regional['es']);

        $.timepicker.regional['es'] = {
            timeOnlyTitle: 'Escoger fecha',
            timeText: 'Fecha',
            hourText: 'Hora',
            minuteText: 'Minuto',
            secondText: 'Segundo',
            millisecText: 'Milisegundo',
            microsecText: 'Microsegundo',
            timezoneText: 'Zona De tiempo',
            currentText: 'Ahora',
            closeText: 'Cierre',
            timeFormat: 'HH:mm',
            timeSuffix: '',
            amNames: ['AM', 'A'],
            pmNames: ['PM', 'P'],
            isRTL: false,
        };
        $.timepicker.setDefaults($.timepicker.regional['es']);
})(jQuery);
