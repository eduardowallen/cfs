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

	table.find("tbody td.container").each(function() {
		
		if ($(this).text().toLowerCase().indexOf(str.toLowerCase()) >= 0) {
			hits.push($(this).parent());
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

$(document).ready(function() {

	var html = '<input type="text" id="search_input"/>'
			 + '<input type="button" class="search_button" id="search_button" value="Search" /><span id="search_results" style="padding-left:10px;"></span>';

	$('.std_table').each(function() {
		var std_table = $(this);
		searchfield = $('<p></p>');
		searchfield = searchfield.prepend(html);
		$(this).before(searchfield);
		searchfield.find("#search_button").click(function() {
			filterTable(std_table, $(this).parent().find("#search_input").first().val(), $(this).parent().find("#search_results").first());
		});
		searchfield.find("#search_input").keyup(function(e) {
			if (e.keyCode == 13) {
				filterTable(std_table, $(this).val(), $(this).parent().find("#search_results").first());
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