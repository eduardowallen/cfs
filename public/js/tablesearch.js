function filterTable(table, str, results) {
	if (table.hasClass("of-tables")) {
		filterTableTable(table, str, results);
		return;
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

	function resizeNewRes(){
		var headerd = $('.tblHeader');
		if(headerd.length > 0){
		headerd.css('display', 'none');
	
		for(var i = 0; i<3; i++){
				var tblarr = new Array('booked', 'reserved', 'prem');
				var header = $('#h'+tblarr[i]+' > ul');
				var headertmp = $('#'+tblarr[i]+' > thead > tr');
				var headerarr = new Array();
				var headerarrHeight = new Array();


				headertmp.children().each(function(i){
					headerarr[i] = $(this).width();
					headerarrHeight[i] = $(this).outerHeight();
				});

				header.css('max-height', headerarrHeight[0]);

				header.children().each(function(i){
					$(this).css('width', headerarr[i]);
					$(this).css('height', headerarrHeight[i]);
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
	}
	$(document).ready(function() {
	var html = '<div style="width:400px; padding-bottom:10px; float:left;"><input type="text" id="search_input"/>'
			 + '<input type="button" class="search_button" id="search_button" value="Search" /><span id="search_results" style="padding-left:10px;"></span>';	
		$('.std_table').each(function() {
			var parstd_table = $(this);
			var std_table = parstd_table;
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
			
			if ($(this).parent().hasClass('scrolltbl')) {
				$(this).parent().prev().before(searchfield);

			} else {
				$(this).before(searchfield);
			}
			
			searchfield.find("#search_button").click(function() {
				filterTable(std_table, $(this).parent().find("#search_input").first().val(), $(this).parent().find("#search_results").first());

				if(site == "forFair"){
					resizeForFair();
				}
				if(site == "newRes"){
					resizeNewRes();
				}
			});

			searchfield.find("#search_input").keyup(function(e) {
				if (e.keyCode == 13) {
					filterTable(std_table, $(this).val(), $(this).parent().find("#search_results").first());

					if(site == "forFair"){
						resizeForFair();
					}

					if(site == "newRes"){
						resizeNewRes();
					}
				}
			});
		});

		$.ajax({
			url: 'ajax/translate.php',
			type: 'POST',
			dataType : 'html',
			data: {'query':'Search'},
			success: function(result){
				$('.search_button').attr('value', result);
			}
		});
});
