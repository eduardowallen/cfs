function filterTable(table, str, results) {
	if (table.hasClass("of-tables")) {
		filterTableTable(table, str, results);
		return;
	}

	if (table.hasClass('scrolltable')) {
		table.floatThead('reflow');
	}

	var hits = new Array;
	var hit_count = 0;

	table.find("tbody td").each(function() {

		if ($(this).text().toLowerCase().indexOf(str.toLowerCase()) >= 0) {
			hits.push($(this).parent());
		}
	});
	table.find("tbody tr").hide();
	for (i=0; i<hits.length; i++) {
		hits[i].show();
	}

	table.find("tbody tr").each(function() {
		if ($(this).is(":visible")) {
			hit_count++;
		}
	});
	results.text(hit_count + ' matching rows.');


}

function filterTableTable(table, str, results) {
	var hits = new Array;
	var hit_count = 0;
	
	table.find("tbody tr.container").each(function() {
			
		if ($(this).text().toLowerCase().indexOf(str.toLowerCase()) >= 0) {
			
			hits.push($(this));
		}
	});
	
	table.find("tbody tr.container").hide();

	for (i=0; i<hits.length; i++) {
		hits[i].show();
	}

	table.find("tbody tr.container").each(function() {
		if ($(this).is(":visible")) {
			hit_count++;
		}
	});

	results.text(hit_count + ' matching rows.');
}

	/*function resizeNewRes(){
		var headerd = $('.tblHeader');
		if(headerd.length > 0){
		headerd.css('display', 'none');
	
		for(var i = 0; i<3; i++){
				var tblarr = new Array('booked', 'reserved', 'prem');
				var header = $('#h'+tblarr[i]+' > ul');
				var headertmp = $('#'+tblarr[i]+' > thead > tr');
				var headerarr = new Array();

				headertmp.children().each(function(i){
					headerarr[i] = $(this).width();
				});

				header.children().each(function(i){
					$(this).css('width', headerarr[i]);
				});

				$('#h'+tblarr[i]+' > thead').css('visibility', 'hidden');
				if(i == 2){headerd.css('display', 'block');}
			}
		}
	}

	function resizeForFair(){
		var headerd = $('.tblHeader');
		if(headerd.length > 0){
		headerd.css('display', 'none');
	
		for(var i = 0; i<3; i++){
			var tblarr = new Array('booked', 'connected');
			var header = $('#h'+tblarr[i]+' > ul');
			var headertmp = $('#'+tblarr[i]+' > thead > tr');
			var headerarr = new Array();
	
			headertmp.children().each(function(i){
				headerarr[i] = $(this).width();
			});
			
			header.children().each(function(i){
				$(this).css('width', headerarr[i]);
			});

			$('#h'+tblarr[i]+' > thead').css('visibility', 'hidden');
			if(i == 2){headerd.css('display', 'block');}
			}
		}
	}*/
	$(document).ready(function() {
<<<<<<< HEAD
	var html = '<div style="width:600px; padding-bottom:10px; float:left;"><input type="text" id="search_input"/>'
=======
	var html = '<div style="width:400px; padding-bottom:10px; float:left;"><input type="text" id="search_input"/>'
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
			 + '<input type="button" class="search_button" id="search_button" value="' + lang.search + '" /><span id="search_results" style="padding-left:10px;"></span>';	
		$('.std_table').each(function() {
			var parstd_table = $(this);
			var std_table = parstd_table;
			if (!std_table.parent().hasClass('floatThead-container')) {
				searchfield = $('<p></p>');
				searchfield = searchfield.prepend(html);
				var url = document.URL;
				var site = "";

				
				if(url.indexOf('newReservations') > 0){
					site = "newRes";
				}
				if(url.indexOf('forFair')  > 0){
					site = "forFair";
				}
				
				if (parstd_table.hasClass('scrolltable')) {
					parstd_table.parent().parent().before(searchfield);

				} else {
					parstd_table.before(searchfield);
				}
				
				searchfield.find("#search_button").click(function() {
					filterTable(std_table, $(this).parent().find("#search_input").first().val(), $(this).parent().find("#search_results").first());

					/*if(site == "forFair"){
						resizeForFair();
					}
					if(site == "newRes"){
						resizeNewRes();
					}*/
				});

				searchfield.find("#search_input").keydown(function(e) {
					if (e.keyCode == 13) {
						e.preventDefault();

						filterTable(std_table, $(this).val(), $(this).parent().find("#search_results").first());

						/*if(site == "forFair"){
							resizeForFair();
						}

						if(site == "newRes"){
							resizeNewRes();
						}*/
					}
				});
			}
		});
});
