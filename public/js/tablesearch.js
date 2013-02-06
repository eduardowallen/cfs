function filterTable() {
	
	if ($(".std_table").hasClass("of-tables")) {
		filterTableTable();
		return;
	}
	
	var str = $("#search_input").val();
	var hits = new Array;
	var hit_count = 0;

	$(".std_table tbody td").each(function() {

		if ($(this).text().toLowerCase().indexOf(str.toLowerCase()) >= 0) {
			//console.log($(this).parent());
			hits.push($(this).parent());
			//$(this).parent().hide();
		}
	});
	$(".std_table tbody tr").hide();
	for (i=0; i<hits.length; i++) {
		hits[i].show();
	}

	$(".std_table tbody tr").each(function() {
		if ($(this).is(":visible")) {
			hit_count++;
		}
	});

	$("#search_results").text(hit_count + ' matching rows.');

}

function filterTableTable() {
	var str = $("#search_input").val();
	var hits = new Array;
	var hit_count = 0;

	$(".std_table.of-tables tbody td.container").each(function() {
		
		if ($(this).text().toLowerCase().indexOf(str.toLowerCase()) >= 0) {
			hits.push($(this).parent());
		}
	});
	
	$(".std_table tbody tr.container").hide();
	for (i=0; i<hits.length; i++) {
		hits[i].show();
	}

	$(".std_table tbody tr.container").each(function() {
		if ($(this).is(":visible")) {
			hit_count++;
		}
	});

	$("#search_results").text(hit_count + ' matching rows.');
}

$(document).ready(function() {
	$.ajax({
		url: 'ajax/translate.php',
		type: 'POST',
		dataType : 'html',
		data: {'query':'Search'},
		success: function(result){
			$('#search_button').attr('value', result);
		}
	});

	var html = '<p><input type="text" id="search_input"/>'
			 + '<input type="button" id="search_button" value="Search" /><span id="search_results" style="padding-left:10px;"></<span></p>';

	$('.std_table').before(html);

	$("#search_button").click(function() {
		filterTable();
	});
	$("#search_input").keyup(function(e) {
		if (e.keyCode == 13) {
			filterTable();
		}
	});

});