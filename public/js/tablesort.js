$(document).ready(function() {
	
	var table = $('.std_table');
	var rows = $('tr', table);
	var sorted = null;
	
	$('th').click(function() {
		var colIndex = $(this).index();
		console.log(rows);
		rows.sort(function(a, b) {
			var keyA = $('td:eq(' + colIndex + ')', a).text();
	        var keyB = $('td:eq(' + colIndex + ')', b).text();
			console.log('test');
	        return (keyA > keyB) ? 1 : 0;
			
	        rows.each(function(index, row) {
	        	console.log(index);
	        	table.append(row);
	        	
	        });
			
		});
	});
	
});


/*$('.link-sort-table').click(function(e) {
    var $sort = this;
    var $table = $('#sort-table');
    var $rows = $('tbody &gt; tr',$table);
    $rows.sort(function(a, b){
        var keyA = $('td:eq(0)',a).text();
        var keyB = $('td:eq(0)',b).text();
        if($($sort).hasClass('asc')){
            return (keyA &gt; keyB) ? 1 : 0;
        } else {
            return (keyA &lt; keyB) ? 1 : 0;
        }
    });
    $.each($rows, function(index, row){
      $table.append(row);
    });
    e.preventDefault();
});*/