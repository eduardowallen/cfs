/* Funktioner för att visa popuper under newReservations! Hämtar data från tabellen och placerar i en popup! */
var ask_before_leave = false;

function showPopup(type, activator){

	var row = $(activator).parent().parent().parent();

	if(type == "reserve"){
		var link = row.data("reserveurl");
		reservePopup(row, link, 'confirm');
	}

	if(type == "book"){
		var link = row.data("approveurl");
		bookPopup(row, link, 'confirm');
	}

	if(type == "prel"){
		var link = row.data("reviewurl");
		prelPopup(row, link, 'confirm');
	}

}

//Open multiform
openForm = function(id) {
	$("#overlay").show(0, function() {
		$(this).css({
			height: $(document).height() + 'px'
		});
		$("#" + id).show();
	});

}

function reservePopup($row, link, action) {
	var formId = "reserve_position_form";
	var form = $('#' + formId);
	dialogue = '#reserve_position_form ';
	$('#' + formId + ' ul#progressbar li').removeClass('active');
	$('#' + formId + ' fieldset').css({
		'transform': 'scale(1)',
		'display': 'none',
		'opacity': '0',
	});
	$('#' + formId + ' ul#progressbar li:first-child').attr('class', 'active');
	$('#' + formId + ' fieldset:first-of-type').css({
		'transform': 'scale(1)',
		'display': 'block',
		'opacity': '1',
	});

	$('#reserve_category_scrollbox > tbody > tr > td > input').prop('checked', false);
	$('#reserve_option_scrollbox > tbody > tr > td > input').prop('checked', false);
	$('#reserve_article_scrollbox > tbody > tr > td > div > input').val(0);
	openForm('reserve_position_form');
	$('.confirm, .edit', form).hide();
	$('.' + action, form).show();
	positionDialogue('reserve_position_form');
	form.css('display', 'block');
	$('#overlay').show();

	var categories = ($row.data("categoriesid") + "").split("|");
	var options = ($row.data("options") + "").split("|");
	var articles = ($row.data("articles") + "").split("|");
	var amount = ($row.data("amount") + "").split("|");
	var i;

// Categories
	for(var i = 0; i < categories.length; i++){
		$('#reserve_category_scrollbox > tbody > tr > td').each(function(){
			var value = $(this).children().val();
			
			if (typeof categories[i] === "string") {
				 if (value == categories[i]) {
				 	$(this).children().prop("checked", true);
				 }
			} else {
				if(value == categories[i].category_id){
						$(this).children().prop('checked', true);
				}
			}
		});
	}

// Extra Options
	for(var i = 0; i < options.length; i++){
		$('#reserve_option_scrollbox > tbody > tr > td').each(function(){
			var value = $(this).children().val();
			
			if (typeof options[i] === "string") {
				 if (value == options[i]) {
				 	$(this).children().prop("checked", true);
				 }
			} else {
				if(value == options[i].option_id){
						$(this).children().prop('checked', true);
				}
			}
		});
	}

// Articles
	for (var i = 0; i < articles.length; i++){
		$('#reserve_article_scrollbox > tbody > tr > td > div').each(function() {
			if($(this).children().attr('id') == articles[i]) {
				$(this).children().val(amount[i]);
			}
		});
	}

	
	$('form').prop('action', link);
	$('.position-name', form).text($row.data("posname"));
	$('#reserve_id').val($row.data('id'));
	$('#reserve_user').text($row.data("company"));
	$('#reserve_commodity_input').val($row.data("commodity"));
	$('#reserve_message_input').val($row.data("message"));
	if (action == 'edit') {
		$('#reserve_expires_input').val($row.data("expires").replace(/ GMT[+-]\d*/, ''));
	}

	$('form').on('keyup keypress', function(e) {
	  var code = e.keyCode || e.which;
	  if (code == 13) { 
	    e.preventDefault();
	    return false;
	  }
	});

	$('#reserve_review').click(function(e) {
		var catnames = [];
		var optcids = [];
		var optnames = [];
		var optprices = [];
		var optvats = [];
		var artcids = [];
		var artnames = [];
		var artprices = [];
		var artvats = [];
		var artamounts = [];
		var count = 0;

		$('#reserve_category_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				catnames[count] = $(this).children('input:checked').parent().siblings('td').text();
				count = count+1;
			}
		});

		var catnamesStr = '';

		for (var j=0; j<catnames.length; j++) {
			if(catnames[j] != ""){
				catnamesStr += '|' + catnames[j];
			}
		}

		count = 0;

		$('#reserve_option_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				optcids[count] = $(this).children('input:checked').parent().siblings('td').eq(0).text();
				optnames[count] = $(this).children('input:checked').parent().siblings('td').eq(1).text();
				optprices[count] = $(this).children('input:checked').parent().siblings('td').eq(2).text();
				optvats[count] = $(this).children('input:checked').parent().siblings('td').eq(3).children().val();
				count = count+1;
			}
		});


		var optcidsStr = '';
		var optnamesStr = '';
		var optpricesStr = '';
		var optvatsStr = '';

		for (var j=0; j<optnames.length; j++) {
			if(optnames[j] != ""){
				optcidsStr += '|' + optcids[j];
				optnamesStr += '|' + optnames[j];
				optpricesStr += '|' + optprices[j];
				optvatsStr += '|' + optvats[j];
			}
		}

		count = 0;

		$('#reserve_article_scrollbox > tbody > tr > td > div').each(function(){
			if ($(this).children().val() > 0) {
				artcids[count] = $(this).parent().siblings('td').eq(0).text();
				artnames[count] = $(this).parent().siblings('td').eq(1).text();
				artprices[count] = $(this).parent().siblings('td').eq(2).text();
				artvats[count] = $(this).parent().siblings('td').eq(3).children().val();
				artamounts[count] = $(this).children().val();

				count = count+1;
			}
		});

		var artcidsStr = '';
		var artnamesStr = '';
		var artpricesStr = '';
		var artvatsStr = '';
		var artqntsStr = '';

		for (var j=0; j<artnames.length; j++) {
			if(artnames[j] != ""){
				artcidsStr += '|' + artcids[j];
				artnamesStr += '|' + artnames[j];
				artpricesStr += '|' + artprices[j];
				artvatsStr += '|' + artvats[j];
				artqntsStr += '|' + artamounts[j];
			}
		}

		catname = catnamesStr.split('|');
		optcid = optcidsStr.split('|');
		optname = optnamesStr.split('|');
		optprice = optpricesStr.split('|');
		optvat = optvatsStr.split('|');
		artcid = artcidsStr.split('|');
		artname = artnamesStr.split('|');
		artprice = artpricesStr.split('|');
		artvat = artvatsStr.split('|');
		artqnt = artqntsStr.split('|');

		var totalPrice = 0;
		var VatPrice0 = 0;
		var VatPrice12 = 0;
		var VatPrice18 = 0;
		var VatPrice25 = 0;
		var excludeVatPrice0 = 0;
		var excludeVatPrice12 = 0;
		var excludeVatPrice18 = 0;
		var excludeVatPrice25 = 0;

		$(dialogue + '#review_category_list').html("");
		for (i = 0; i < catname.length; i++) {
			if (catname[i] != "") {
				$(dialogue + '#review_category_list').append(catname[i] + '<br/>');
			}
		}

		$(dialogue + '#review_list').html("");
		$(dialogue + '#review_list2').html("");
		html = '<thead>';
			html += '<tr style="background-color:#efefef;">';
				html += '<th>ID</th>';
				html += '<th class="left">' + lang.description + '</th>';
				html += '<th class="left">' + lang.price + '</th>';
				html += '<th>' + lang.amount + '</th>';
				html += '<th>' + lang.tax + '</th>';
				html += '<th class="total">' + lang.subtotal + '</th>';
			html += '</tr>';
		html += '</thead>';

		html += '<tbody>';
		html += '<tr style="height:12px"></tr>;<tr><td></td><td class="left"><b>' + lang.space + '</b></td><td></td><td></td></tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + $row.data("posname") + '</td>';
			html += '<td class="left price">' + $row.data("posprice") + '</td>';
			html += '<td class="amount">1</td>';
			if ($row.data("posvat")) {
				html += '<td class="moms">' + $row.data("posvat") + '%</td>';
			} else {
				html += '<td class="moms">0%</td>';
			}
			html += '<td class="total">' + parseFloat($row.data("posprice")).toFixed(2) + '</td>';
		html += '</tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + $row.data("posinfo") + '</td>';
			html += '<td class="left price"></td>';
			html += '<td class="amount"></td>';
			html += '<td class="moms"></td>';
			html += '<td class="total"></td>';
		html += '</tr>';

		if ($row.data("posprice")) {
			if (parseFloat($row.data("posvat")) == 25) {
				excludeVatPrice25 += parseFloat($row.data("posprice"));
			} else if (parseFloat($row.data("posvat")) == 18) {
				excludeVatPrice18 += parseFloat($row.data("posprice"));
			} else {
				excludeVatPrice0 += parseFloat($row.data("posprice"));
			}
		}

		if (optname != "") {
			html += '<tr style="height:12px"></tr><tr><td></td><td class="left"><b>' + lang.options + '</b></td><td></td><td></td></tr>';
			for (i = 0; i < optname.length; i++) {
				html += '<tr>';
					html += '<td class="id">' + optcid[i] + '</td>';
					html += '<td class="left name">' + optname[i] + '</td>';
					html += '<td class="left price">' + optprice[i] + '</td>';
					if (optprice[i]) {
						html += '<td class="amount">1</td>';
					} else {
						html += '<td class="amount"></td>';
					}
					if (optvat[i]) {
						html += '<td class="moms">' + optvat[i] + '%</td>';
					} else {
						html += '<td class="moms"></td>';	
					}

				if ((optprice[i]) && (optvat[i])) {
					html += '<td class="total">' + parseFloat(optprice[i]).toFixed(2) + '</td>';
					//totalprice += parseFloat(optPrice[i]);
					if (optvat[i] == 25) {
						excludeVatPrice25 += parseFloat(optprice[i]);
					}
					if (optvat[i] == 18) {
						excludeVatPrice18 += parseFloat(optprice[i]);
					}
					if (optvat[i] == 12) {
						excludeVatPrice12 += parseFloat(optprice[i]);
					}
					if (optvat[i] == 0) {
						excludeVatPrice0 += parseFloat(optprice[i]);
					}										
				}
				html += '</tr>';
			}
		}
	if (artname != "") {
		html += '<tr style="height:12px"></tr><tr><td></td><td class="left"><b>' + lang.articles + '</b></td><td></td><td></td></tr>';
		for (i = 0; i < artname.length; i++) {
			html += '<tr>';
				html += '<td class="id">' + artcid[i] + '</td>';
				html += '<td class="left name">' + artname[i] + '</td>';
				html += '<td class="left price">' + artprice[i] + '</td>';
				html += '<td class="amount">' + artqnt[i] + '</td>';
				if (artvat[i]) {
					html += '<td class="moms">' + artvat[i] + '%</td>';	
				} else {
					html += '<td class="moms"></td>';	
				}
				if ((artprice[i]) && (artqnt[i])) {
					html += '<td class="total">' + parseFloat(artprice[i] * artqnt[i]).toFixed(2) + '</td>';
				}
			html += '</tr>';

			if ((artprice[i]) && (artvat[i])) {
				if (artvat[i] == 25) {
					excludeVatPrice25 += parseFloat(artprice[i] * artqnt[i]);
				}
				if (artvat[i] == 18) {
					excludeVatPrice18 += parseFloat(artprice[i] * artqnt[i]);
				}
				if (artvat[i] == 12) {
					excludeVatPrice12 += parseFloat(artprice[i] * artqnt[i]);
				}
				if (artvat[i] == 0) {
					excludeVatPrice0 += parseFloat(artprice[i] * artqnt[i]);
				}
			}
		}
	}
	html += '<tr style="height:12px"></tr>';
html += '</tbody>';

// return integer part - may be negative
Math.trunc = function(n) {
    return (n < 0) ? Math.ceil(n) : Math.floor(n);
}
Math.frac = function(n) {
    return n - Math.trunc(n);
}
VatPrice0 = parseFloat(excludeVatPrice0);
VatPrice12 = parseFloat(excludeVatPrice12*0.12);
VatPrice18 = parseFloat(excludeVatPrice18*0.18);
VatPrice25 = parseFloat(excludeVatPrice25*0.25);
totalPrice += parseFloat(excludeVatPrice25 + excludeVatPrice18 + excludeVatPrice12 + VatPrice12 + VatPrice18 + VatPrice25 + VatPrice0);

totalPriceRounded = Math.trunc(totalPrice);
cents = (totalPriceRounded - totalPrice);
if (cents < -0.49) {
	cents += 1;
	totalPriceRounded += 1;
}

html2 = '<thead>';
	html2 += '<tr>';
		html2 += '<th></th>';
		html2 += '<th></th>';
		html2 += '<th></th>';
		html2 += '<th></th>';
		html2 += '<th></th>';
		html2 += '<th></th>';
	html2 += '</tr>';
html2 += '</thead>';

html2 += '<tbody>';
	html2 += '<tr style="height:12px">';					
	html2 += '</tr>';
	html2 += '<tr>';
		html2 += '<td>' + lang.net + ':</td>';
		html2 += '<td>' + lang.tax + ' %</td>';
		html2 += '<td>' + lang.tax + ':</td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
	html2 += '</tr>';
if (excludeVatPrice0) {
	html2 += '<tr>';
		html2 += '<td class="vat">' + parseFloat(excludeVatPrice0).toFixed(2) + '</td>';
		html2 += '<td class="vat">0.00</td>';
		html2 += '<td class="vat">0.00</td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
	html2 += '</tr>';
}

if (excludeVatPrice12 != 0) {
	html2 += '<tr>';
		html2 += '<td class="vat">' + parseFloat(excludeVatPrice12).toFixed(2) + '</td>';
		html2 += '<td class="vat">12.00</td>';
		html2 += '<td class="vat">' + parseFloat(VatPrice12).toFixed(2) + '</td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
	html2 += '</tr>';
}

if (excludeVatPrice18 != 0) {
	html2 += '<tr>';
		html2 += '<td class="vat">' + parseFloat(excludeVatPrice18).toFixed(2) + '</td>';
		html2 += '<td class="vat">18.00</td>';
		html2 += '<td class="vat">' + parseFloat(VatPrice18).toFixed(2) + '</td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
	html2 += '</tr>';
}

if (excludeVatPrice25 != 0) {
	html2 += '<tr>';
		html2 += '<td class="vat">' + parseFloat(excludeVatPrice25).toFixed(2) + '</td>';
		html2 += '<td class="vat">25.00</td>';
		html2 += '<td class="vat">' + parseFloat(VatPrice25).toFixed(2) + '</td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
	html2 += '</tr>';
}
	html2 += '<tr>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td class="cents">' + lang.rounding + ': ' + parseFloat(cents).toFixed(2) + '</td>';
	html2 += '</tr>';

	html2 += '<tr>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td class="totalprice">' + lang.currency + ' ' + lang.to_pay + '&nbsp;&nbsp;' + parseFloat(totalPriceRounded).toFixed(2) + '</td>';
	html2 += '</tr>';

	$(dialogue + '#review_list').append(html);
	$(dialogue + '#review_list2').append(html2);
	$(dialogue + '#review_commodity_input').html("");
	$(dialogue + '#review_commodity_input').append($("#reserve_commodity_input").val());
	if ($(dialogue + '#review_commodity_input').html().length == 0) {
		$(dialogue + '#review_commodity_input').append(lang.no_commodity);
	}
	$(dialogue + '#review_message').html("");
	$(dialogue + '#review_message').append($("#reserve_message_input").val());
	if ($(dialogue + '#review_message').html().length == 0) {
		$(dialogue + '#review_message').append(lang.no_message);
	}
	$(dialogue + '#review_user').html("");
	$(dialogue + '#review_user').append($('#reserve_user_input').find(":selected").text());

	});
	
	$('form').submit(function(e) {
		$('#reserve_article_scrollbox > tbody > tr > td > div').each(function() {
			if ($(this).children().val() > 0) {
				$(this).siblings().val($(this).children().attr('id'));
			} else {
				$(this).siblings().val(0);
			}
		});
	});
}

function bookPopup($row, link, action) {
	var formId = "book_position_form";
	var form = $('#' + formId);
	dialogue = '#book_position_form ';
	$('#' + formId + ' ul#progressbar li').removeClass('active');
	$('#' + formId + ' fieldset').css({
		'transform': 'scale(1)',
		'display': 'none',
		'opacity': '0',
	});				
	$('#' + formId + ' ul#progressbar li:first-child').attr('class', 'active');
	$('#' + formId + ' fieldset:first-of-type').css({
		'transform': 'scale(1)',
		'display': 'block',
		'opacity': '1',
	});

	$('#book_category_scrollbox > tbody > tr > td > input').prop('checked', false);
	$('#book_option_scrollbox > tbody > tr > td > input').prop('checked', false);
	$('#book_article_scrollbox > tbody > tr > td > div > input').val(0);
	openForm('book_position_form');
	$('.confirm, .edit, .close', form).hide();
	$('.' + action, form).show();
	positionDialogue('book_position_form');
	form.css('display', 'block');
	$('#overlay').show();

	var categories = ($row.data("categoriesid") + "").split("|");
	var options = ($row.data("options") + "").split("|");
	var articles = ($row.data("articles") + "").split("|");
	var amount = ($row.data("amount") + "").split("|");
	var i;

// Categories
	for(var i = 0; i < categories.length; i++){
		$('#book_category_scrollbox > tbody > tr > td').each(function(){
			var value = $(this).children().val();
			
			if (typeof categories[i] === "string") {
				 if (value == categories[i]) {
				 	$(this).children().prop("checked", true);
				 }
			} else {
				if(value == categories[i].category_id){
						$(this).children().prop('checked', true);
				}
			}
		});
	}

// Extra Options
	for(var i = 0; i < options.length; i++){
		$('#book_option_scrollbox > tbody > tr > td').each(function(){
			var value = $(this).children().val();
			
			if (typeof options[i] === "string") {
				 if (value == options[i]) {
				 	$(this).children().prop("checked", true);
				 }
			} else {
				if(value == options[i].option_id){
						$(this).children().prop('checked', true);
				}
			}
		});
	}

// Articles
	for (var i = 0; i < articles.length; i++){
		$('#book_article_scrollbox > tbody > tr > td > div').each(function() {
			if($(this).children().attr('id') == articles[i]) {
				$(this).children().val(amount[i]);
			}
		});
	}

	
	$('form').prop('action', link);
	$('.position-name', form).text($row.data("posname"));
	$('#book_id').val($row.data('id'));
	$('#book_user').text($row.data("company"));
	$('#book_commodity_input').val($row.data("commodity"));
	$('#book_message_input').val($row.data("message"));	

	$('form').on('keyup keypress', function(e) {
	  var code = e.keyCode || e.which;
	  if (code == 13) { 
	    e.preventDefault();
	    return false;
	  }
	});

	$('#book_review').click(function(e) {
		var catnames = [];
		var optcids = [];
		var optnames = [];
		var optprices = [];
		var optvats = [];
		var artcids = [];
		var artnames = [];
		var artprices = [];
		var artvats = [];
		var artamounts = [];
		var count = 0;


		$('#book_category_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				catnames[count] = $(this).children('input:checked').parent().siblings('td').text();
				count = count+1;
			}
		});
		
		var catnamesStr = '';

		for (var j=0; j<catnames.length; j++) {
			if(catnames[j] != ""){
				catnamesStr += '|' + catnames[j];
			}
		}

		count = 0;

		$('#book_option_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				optcids[count] = $(this).children('input:checked').parent().siblings('td').eq(0).text();
				optnames[count] = $(this).children('input:checked').parent().siblings('td').eq(1).text();
				optprices[count] = $(this).children('input:checked').parent().siblings('td').eq(2).text();
				optvats[count] = $(this).children('input:checked').parent().siblings('td').eq(3).children().val();
				count = count+1;
			}
		});

		var optcidsStr = '';
		var optnamesStr = '';
		var optpricesStr = '';
		var optvatsStr = '';

		for (var j=0; j<optnames.length; j++) {
			if(optnames[j] != ""){
				optcidsStr += '|' + optcids[j];
				optnamesStr += '|' + optnames[j];
				optpricesStr += '|' + optprices[j];
				optvatsStr += '|' + optvats[j];
			}
		}

		count = 0;

		$('#book_article_scrollbox > tbody > tr > td > div').each(function(){
			if ($(this).children().val() > 0) {
				artcids[count] = $(this).parent().siblings('td').eq(0).text();
				artnames[count] = $(this).parent().siblings('td').eq(1).text();
				artprices[count] = $(this).parent().siblings('td').eq(2).text();
				artvats[count] = $(this).parent().siblings('td').eq(3).children().val();
				artamounts[count] = $(this).children().val();
				
				count = count+1;
			}
		});

		var artcidsStr = '';
		var artnamesStr = '';
		var artpricesStr = '';
		var artvatsStr = '';
		var artqntsStr = '';

		for (var j=0; j<artnames.length; j++) {
			if(artnames[j] != ""){
				artcidsStr += '|' + artcids[j];
				artnamesStr += '|' + artnames[j];
				artpricesStr += '|' + artprices[j];
				artvatsStr += '|' + artvats[j];
				artqntsStr += '|' + artamounts[j];
			}
		}

		catname = catnamesStr.split('|');
		optcid = optcidsStr.split('|');
		optname = optnamesStr.split('|');
		optprice = optpricesStr.split('|');
		optvat = optvatsStr.split('|');
		artcid = artcidsStr.split('|');
		artname = artnamesStr.split('|');
		artprice = artpricesStr.split('|');
		artvat = artvatsStr.split('|');
		artqnt = artqntsStr.split('|');



		var totalPrice = 0;
		var VatPrice0 = 0;
		var VatPrice12 = 0;
		var VatPrice18 = 0;
		var VatPrice25 = 0;
		var excludeVatPrice0 = 0;
		var excludeVatPrice12 = 0;
		var excludeVatPrice18 = 0;
		var excludeVatPrice25 = 0;


		$(dialogue + '#review_category_list').html("");
		for (i = 0; i < catname.length; i++) {
			if (catname[i] != "") {
				$(dialogue + '#review_category_list').append(catname[i] + '<br/>');
			}
		}

		$(dialogue + '#review_list').html("");
		$(dialogue + '#review_list2').html("");
		html = '<thead>';
			html += '<tr style="background-color:#efefef;">';
				html += '<th>ID</th>';
				html += '<th class="left">' + lang.description + '</th>';
				html += '<th class="left">' + lang.price + '</th>';
				html += '<th>' + lang.amount + '</th>';
				html += '<th>' + lang.tax + '</th>';
				html += '<th class="total">' + lang.subtotal + '</th>';
			html += '</tr>';
		html += '</thead>';

		html += '<tbody>';
		html += '<tr style="height:12px"></tr>;<tr><td></td><td class="left"><b>' + lang.space + '</b></td><td></td><td></td></tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + $row.data("posname") + '</td>';
			html += '<td class="left price">' + $row.data("posprice") + '</td>';
			html += '<td class="amount">1</td>';
			if ($row.data("posvat")) {
				html += '<td class="moms">' + $row.data("posvat") + '%</td>';
			} else {
				html += '<td class="moms">0%</td>';
			}
			html += '<td class="total">' + parseFloat($row.data("posprice")).toFixed(2) + '</td>';
		html += '</tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + $row.data("posinfo") + '</td>';
			html += '<td class="left price"></td>';
			html += '<td class="amount"></td>';
			html += '<td class="moms"></td>';
			html += '<td class="total"></td>';
		html += '</tr>';		
		if ($row.data("posprice")) {
			if (parseFloat($row.data("posvat")) == 25) {
				excludeVatPrice25 += parseFloat($row.data("posprice"));
			} else if (parseFloat($row.data("posvat")) == 18) {
				excludeVatPrice18 += parseFloat($row.data("posprice"));
			} else {
				excludeVatPrice0 += parseFloat($row.data("posprice"));
			}
		}

		if (optname != "") {
			html += '<tr style="height:12px"></tr><tr><td></td><td class="left"><b>' + lang.options + '</b></td><td></td><td></td></tr>';
			for (i = 0; i < optname.length; i++) {
				html += '<tr>';
					html += '<td class="id">' + optcid[i] + '</td>';
					html += '<td class="left name">' + optname[i] + '</td>';
					html += '<td class="left price">' + optprice[i] + '</td>';
					if (optprice[i]) {
						html += '<td class="amount">1</td>';
					} else {
						html += '<td class="amount"></td>';
					}
					if (optvat[i]) {
						html += '<td class="moms">' + optvat[i] + '%</td>';
					} else {
						html += '<td class="moms"></td>';	
					}

				if ((optprice[i]) && (optvat[i])) {
					html += '<td class="total">' + parseFloat(optprice[i]).toFixed(2) + '</td>';
					//totalprice += parseFloat(optPrice[i]);
					if (optvat[i] == 25) {
						excludeVatPrice25 += parseFloat(optprice[i]);
					}
					if (optvat[i] == 18) {
						excludeVatPrice18 += parseFloat(optprice[i]);
					}
					if (optvat[i] == 12) {
						excludeVatPrice12 += parseFloat(optprice[i]);
					}
					if (optvat[i] == 0) {
						excludeVatPrice0 += parseFloat(optprice[i]);
					}										
				}
				html += '</tr>';
			}
		}
	if (artname != "") {
		html += '<tr style="height:12px"></tr><tr><td></td><td class="left"><b>' + lang.articles + '</b></td><td></td><td></td></tr>';
		for (i = 0; i < artname.length; i++) {
			html += '<tr>';
				html += '<td class="id">' + artcid[i] + '</td>';
				html += '<td class="left name">' + artname[i] + '</td>';
				html += '<td class="left price">' + artprice[i] + '</td>';
				html += '<td class="amount">' + artqnt[i] + '</td>';
				if (artvat[i]) {
					html += '<td class="moms">' + artvat[i] + '%</td>';	
				} else {
					html += '<td class="moms"></td>';	
				}
				if ((artprice[i]) && (artqnt[i])) {
					html += '<td class="total">' + parseFloat(artprice[i] * artqnt[i]).toFixed(2) + '</td>';
				}
			html += '</tr>';

			if ((artprice[i]) && (artvat[i])) {
				if (artvat[i] == 25) {
					excludeVatPrice25 += parseFloat(artprice[i] * artqnt[i]);
				}
				if (artvat[i] == 18) {
					excludeVatPrice18 += parseFloat(artprice[i] * artqnt[i]);
				}
				if (artvat[i] == 12) {
					excludeVatPrice12 += parseFloat(artprice[i] * artqnt[i]);
				}
				if (artvat[i] == 0) {
					excludeVatPrice0 += parseFloat(artprice[i] * artqnt[i]);
				}
			}
		}
	}
	html += '<tr style="height:12px"></tr>';
html += '</tbody>';

// return integer part - may be negative
Math.trunc = function(n) {
    return (n < 0) ? Math.ceil(n) : Math.floor(n);
}
Math.frac = function(n) {
    return n - Math.trunc(n);
}
VatPrice0 = parseFloat(excludeVatPrice0);
VatPrice12 = parseFloat(excludeVatPrice12*0.12);
VatPrice18 = parseFloat(excludeVatPrice18*0.18);
VatPrice25 = parseFloat(excludeVatPrice25*0.25);
totalPrice += parseFloat(excludeVatPrice25 + excludeVatPrice18 + excludeVatPrice12 + VatPrice12 + VatPrice18 + VatPrice25 + VatPrice0);

totalPriceRounded = Math.trunc(totalPrice);
cents = (totalPriceRounded - totalPrice);
if (cents < -0.49) {
	cents += 1;
	totalPriceRounded += 1;
}

html2 = '<thead>';
	html2 += '<tr>';
		html2 += '<th></th>';
		html2 += '<th></th>';
		html2 += '<th></th>';
		html2 += '<th></th>';
		html2 += '<th></th>';
		html2 += '<th></th>';
	html2 += '</tr>';
html2 += '</thead>';
html2 += '<tbody>';

	html2 += '<tr style="height:12px"></tr>';
	html2 += '<tr>';
		html2 += '<td>' + lang.net + ':</td>';
		html2 += '<td>' + lang.tax + ' %</td>';
		html2 += '<td>' + lang.tax + ':</td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
	html2 += '</tr>';
if (excludeVatPrice0) {
	html2 += '<tr>';
		html2 += '<td class="vat">' + parseFloat(excludeVatPrice0).toFixed(2) + '</td>';
		html2 += '<td class="vat">0.00</td>';
		html2 += '<td class="vat">0.00</td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
	html2 += '</tr>';
}

if (excludeVatPrice12 != 0) {
	html2 += '<tr>';
		html2 += '<td class="vat">' + parseFloat(excludeVatPrice12).toFixed(2) + '</td>';
		html2 += '<td class="vat">12.00</td>';
		html2 += '<td class="vat">' + parseFloat(VatPrice12).toFixed(2) + '</td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
	html2 += '</tr>';
}

if (excludeVatPrice18 != 0) {
	html2 += '<tr>';
		html2 += '<td class="vat">' + parseFloat(excludeVatPrice18).toFixed(2) + '</td>';
		html2 += '<td class="vat">18.00</td>';
		html2 += '<td class="vat">' + parseFloat(VatPrice18).toFixed(2) + '</td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
	html2 += '</tr>';
}

if (excludeVatPrice25 != 0) {
	html2 += '<tr>';
		html2 += '<td class="vat">' + parseFloat(excludeVatPrice25).toFixed(2) + '</td>';
		html2 += '<td class="vat">25.00</td>';
		html2 += '<td class="vat">' + parseFloat(VatPrice25).toFixed(2) + '</td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
	html2 += '</tr>';
}
	html2 += '<tr>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td class="cents">' + lang.rounding + ': ' + parseFloat(cents).toFixed(2) + '</td>';
	html2 += '</tr>';

	html2 += '<tr>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td></td>';
		html2 += '<td class="totalprice">' + lang.currency + ' ' + lang.to_pay + '&nbsp;&nbsp;' + parseFloat(totalPriceRounded).toFixed(2) + '</td>';
	html2 += '</tr>';

	$(dialogue + '#review_list').append(html);
	$(dialogue + '#review_list2').append(html2);
	$(dialogue + '#review_commodity_input').html("");
	$(dialogue + '#review_commodity_input').append($("#book_commodity_input").val());
	if ($(dialogue + '#review_commodity_input').html().length == 0) {
		$(dialogue + '#review_commodity_input').append(lang.no_commodity);
	}	
	$(dialogue + '#review_message').html("");
	$(dialogue + '#review_message').append($("#book_message_input").val());
	if ($(dialogue + '#review_message').html().length == 0) {
		$(dialogue + '#review_message').append(lang.no_message);
	}
	$(dialogue + '#review_user').html("");
	$(dialogue + '#review_user').append($('#book_user_input').find(":selected").text());
	});

	$('form').submit(function(e) {
		$('#book_article_scrollbox > tbody > tr > td > div').each(function() {
			if ($(this).children().val() > 0) {
				$(this).siblings().val($(this).children().attr('id'));
			} else {
				$(this).siblings().val(0);
			}
		});
	});
}

function prelPopup($row, link, action) {
	var dialogueId = "review_prel_dialogue";
	review_dialogue = '#review_prel_dialogue ';
	var dialogue = $('#' + dialogueId);
	$(review_dialogue + '#review_user').html("");
	$(review_dialogue + '#review_commodity_input').html("");
	$(review_dialogue + '#review_message').html("");
	$(review_dialogue + '#review_registration_area').html("");
	$(review_dialogue + '#review_area_label').css('display', 'block');
	$(review_dialogue + '#review_registration_area').css('display', 'block');	
	$('.' + action, dialogue).show();
	dialogue.css('display', 'block');
	positionDialogue('review_prel_dialogue');
	$('#overlay').show();

	var catArr = ($row.data("categories") + "").split("|");

	var optId = ($row.data("optionid") + "").split("|");
	var optText = ($row.data("optiontext") + "").split("|");
	var optPrice = ($row.data("optionprice") + "").split("|");
	var optVat = ($row.data("optionvat") + "").split("|");

	var artId = ($row.data("articleid") + "").split("|");
	var artText = ($row.data("articletext") + "").split("|");
	var artPrice = ($row.data("articleprice") + "").split("|");
	var artVat = ($row.data("articlevat") + "").split("|");
	var artAmount = ($row.data("articleamount") + "").split("|");
	var i;
	var totalPrice = 0;
	var VatPrice0 = 0;
	var VatPrice12 = 0;
	var VatPrice18 = 0;
	var VatPrice25 = 0;
	var excludeVatPrice0 = 0;
	var excludeVatPrice12 = 0;
	var excludeVatPrice18 = 0;
	var excludeVatPrice25 = 0;

	if ($row.data("posstatus") == 1) {
		dialogue.css('border-top', '5em solid #3258CD');
		$('.standSpaceName', dialogue).text(lang.reservation + ' ' + $row.data("posname"));
	} else if ($row.data("posstatus") == 3) {
		dialogue.css('border-top', '5em solid #47A547');
		$('.standSpaceName', dialogue).text(lang.preliminary + ' ' + $row.data("posname"));
	} else if ($row.data("posstatus") == 2) {
		dialogue.css('border-top', '5em solid #d21d1d');
		$('.standSpaceName', dialogue).text(lang.booking + ' ' + $row.data("posname"));
	} else {
		dialogue.css('border-top', '5em solid #47A547');
		$('.standSpaceName', dialogue).text(lang.registration);		
	}

	$(review_dialogue + '#review_category_list').html("");
	for (i = 0; i < catArr.length; i++) {
		$(review_dialogue + '#review_category_list').append(catArr[i] + '<br/>');
	}
	//totalprice += parseFloat($row.data("posprice"));
	$(review_dialogue + '#review_list').html("");
	$(review_dialogue + '#review_list2').html("");
	html = '<thead>';
		html += '<tr style="background-color:#efefef;">';
			html += '<th>ID</th>';
			html += '<th class="left">' + lang.description + '</th>';
			html += '<th class="left">' + lang.price + '</th>';
			html += '<th>' + lang.amount + '</th>';
			html += '<th>' + lang.tax + '</th>';
			html += '<th class="total">' + lang.subtotal + '</th>';
		html += '</tr>';
	html += '</thead>';
	html += '<tbody>';

	if ($row.data("posname")) {
		html += '<tr style="height:12px"></tr>;<tr><td></td><td class="left"><b>' + lang.space + '</b></td><td></td><td></td></tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + $row.data("posname") + '</td>';
			html += '<td class="left price">' + $row.data("posprice") + '</td>';
			html += '<td class="amount">1</td>';
			if ($row.data("posvat")) {
				html += '<td class="moms">' + $row.data("posvat") + '%</td>';
			} else {
				html += '<td class="moms">0%</td>';
			}
			html += '<td class="total">' + parseFloat($row.data("posprice")).toFixed(2) + '</td>';
		html += '</tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + $row.data("posinfo") + '</td>';
			html += '<td class="left price"></td>';
			html += '<td class="amount"></td>';
			html += '<td class="moms"></td>';
			html += '<td class="total"></td>';
		html += '</tr>';
	}

	if ($row.data("posprice")) {
		if (parseFloat($row.data("posvat")) == 25) {
			excludeVatPrice25 += parseFloat($row.data("posprice"));
		} else if (parseFloat($row.data("posvat")) == 18) {
			excludeVatPrice18 += parseFloat($row.data("posprice"));
		} else {
			excludeVatPrice0 += parseFloat($row.data("posprice"));
		}
	}

	if (optText != "") {
		html += '<tr style="height:12px"></tr><tr><td></td><td class="left"><b>' + lang.options + '</b></td><td></td><td></td></tr>';
		for (i = 0; i < optId.length; i++) {
			html += '<tr>';
				html += '<td class="id">' + optId[i] + '</td>';
				html += '<td class="left name">' + optText[i] + '</td>';
				html += '<td class="left price">' + optPrice[i] + '</td>';
				if (optPrice[i]) {
					html += '<td class="amount">1</td>';
				} else {
					html += '<td class="amount"></td>';
				}
				if (optVat[i]) {
					html += '<td class="moms">' + optVat[i] + '%</td>';
				} else {
					html += '<td class="moms"></td>';
				}

			if ((optPrice[i]) && (optVat[i])) {
				html += '<td class="total">' + parseFloat(optPrice[i]).toFixed(2) + '</td>';
				//totalprice += parseFloat(optPrice[i]);
				if (optVat[i] == 25) {
					excludeVatPrice25 += parseFloat(optPrice[i]);
				}
				if (optVat[i] == 18) {
					excludeVatPrice18 += parseFloat(optPrice[i]);
				}
				if (optVat[i] == 12) {
					excludeVatPrice12 += parseFloat(optPrice[i]);
				}
				if (optVat[i] == 0) {
					excludeVatPrice0 += parseFloat(optPrice[i]);
				}
			}
			html += '</tr>';
		}
	}

	if (artText != "") {
		html += '<tr style="height:12px"></tr><tr><td></td><td class="left"><b>' + lang.articles + '</b></td><td></td><td></td></tr>';
		for (i = 0; i < artText.length; i++) {
			html += '<tr>';
				html += '<td class="id">' + artId[i] + '</td>';
				html += '<td class="left name">' + artText[i] + '</td>';
				html += '<td class="left price">' + artPrice[i] + '</td>';
				html += '<td class="amount">' + artAmount[i] + '</td>';
				if (artVat[i]) {
					html += '<td class="moms">' + artVat[i] + '%</td>';	
				} else {
					html += '<td class="moms"></td>';	
				}
				if ((artPrice[i]) && (artAmount[i])) {
					html += '<td class="total">' + parseFloat(artPrice[i] * artAmount[i]).toFixed(2) + '</td>';
				}
			html += '</tr>';

			if ((artPrice[i]) && (artVat[i])) {
				//totalprice += parseFloat(artPrice[i]);
				if (artVat[i] == 25) {
					excludeVatPrice25 += parseFloat(artPrice[i] * artAmount[i]);
				}
				if (artVat[i] == 18) {
					excludeVatPrice18 += parseFloat(artPrice[i] * artAmount[i]);
				}
				if (artVat[i] == 12) {
					excludeVatPrice12 += parseFloat(artPrice[i] * artAmount[i]);
				}
				if (artVat[i] == 0) {
					excludeVatPrice0 += parseFloat(artPrice[i] * artAmount[i]);
				}										
			}
		}
	}
		html += '<tr style="height:12px"></tr>';
		html += '</tbody>';
/*
*/

// return integer part - may be negative
Math.trunc = function(n) {
    return (n < 0) ? Math.ceil(n) : Math.floor(n);
}
Math.frac = function(n) {
    return n - Math.trunc(n);
}

VatPrice0 = parseFloat(excludeVatPrice0);
VatPrice12 = parseFloat(excludeVatPrice12*0.12);
VatPrice18 = parseFloat(excludeVatPrice18*0.18);
VatPrice25 = parseFloat(excludeVatPrice25*0.25);
totalPrice += parseFloat(excludeVatPrice25 + excludeVatPrice18 + excludeVatPrice12 + VatPrice12 + VatPrice18 + VatPrice25 + VatPrice0);

totalPriceRounded = Math.trunc(totalPrice);
cents = (totalPriceRounded - totalPrice);
if (cents < -0.49) {
	cents += 1;
	totalPriceRounded += 1;
}

html2 = '<thead>';
	html2 += '<tr>';
		html2 += '<th></th>';
		html2 += '<th></th>';
		html2 += '<th></th>';
		html2 += '<th></th>';
		html2 += '<th></th>';
		html2 += '<th></th>';
	html2 += '</tr>';
html2 += '</thead>';
html2 += '<tbody>';

		html2 += '<tr style="height:12px">';
		html2 += '</tr>';

		html2 += '<tr>';
			html2 += '<td>' + lang.net + ':</td>';
			html2 += '<td>' + lang.tax + ' %</td>';
			html2 += '<td>' + lang.tax + ':</td>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td></td>';
		html2 += '</tr>';
if (excludeVatPrice0) {
html2 += '<tr>';
	html2 += '<td class="vat">' + parseFloat(excludeVatPrice0).toFixed(2) + '</td>';
	html2 += '<td class="vat">0.00</td>';
	html2 += '<td class="vat">0.00</td>';
	html2 += '<td></td>';
	html2 += '<td></td>';
	html2 += '<td></td>';
html2 += '</tr>';
}

if (excludeVatPrice12 != 0) {
html2 += '<tr>';
	html2 += '<td class="vat">' + parseFloat(excludeVatPrice12).toFixed(2) + '</td>';
	html2 += '<td class="vat">12.00</td>';
	html2 += '<td class="vat">' + parseFloat(VatPrice12).toFixed(2) + '</td>';
	html2 += '<td></td>';
	html2 += '<td></td>';
	html2 += '<td></td>';
html2 += '</tr>';
}

if (excludeVatPrice18 != 0) {
html2 += '<tr>';
	html2 += '<td class="vat">' + parseFloat(excludeVatPrice18).toFixed(2) + '</td>';
	html2 += '<td class="vat">18.00</td>';
	html2 += '<td class="vat">' + parseFloat(VatPrice18).toFixed(2) + '</td>';
	html2 += '<td></td>';
	html2 += '<td></td>';
	html2 += '<td></td>';
html2 += '</tr>';
}

if (excludeVatPrice25 != 0) {
html2 += '<tr>';
	html2 += '<td class="vat">' + parseFloat(excludeVatPrice25).toFixed(2) + '</td>';
	html2 += '<td class="vat">25.00</td>';
	html2 += '<td class="vat">' + parseFloat(VatPrice25).toFixed(2) + '</td>';
	html2 += '<td></td>';
	html2 += '<td></td>';
	html2 += '<td></td>';
html2 += '</tr>';
}

if (excludeVatPrice25 == 0 && excludeVatPrice18 == 0 && excludeVatPrice12 == 0 && excludeVatPrice0 == 0) {
html2 += '<tr>';
	html2 += '<td class="vat">0.00</td>';
	html2 += '<td class="vat">0.00</td>';
	html2 += '<td class="vat">0.00</td>';
	html2 += '<td></td>';
	html2 += '<td></td>';
	html2 += '<td></td>';
html2 += '</tr>';	
}
		html2 += '<tr>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td class="cents">' + lang.rounding + ': ' + parseFloat(cents).toFixed(2) + '</td>';
		html2 += '</tr>';

		html2 += '<tr>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td class="totalprice">' + lang.currency + ' ' + lang.to_pay + '&nbsp;&nbsp;' + parseFloat(totalPriceRounded).toFixed(2) + '</td>';
		html2 += '</tr>';
if ($row.data("type") == 'registration') {
		html2 += '<tr>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td class="preliminary_totalprice">' + lang.amount_no_standspace + '</td>';
		html2 += '</tr>';
}

	$(review_dialogue + '#review_list').append(html);
	$(review_dialogue + '#review_list2').append(html2);

	//$(review_dialogue + '#review_id').val($row.data('id'));
	$(review_dialogue + '#review_user').append($row.data("company"));
	$(review_dialogue + '#review_commodity_input').append($row.data("commodity"));
	if($(review_dialogue + '#review_commodity_input').html().length == 0) {
		$(review_dialogue + '#review_commodity_input').append(lang.no_commodity);
	}	
	$(review_dialogue + '#review_message').append($row.data("message"));
	if($(review_dialogue + '#review_message').html().length == 0) {
		$(review_dialogue + '#review_message').append(lang.no_message);
	}

	if ($row.data("area")) {
		$(review_dialogue + '#review_registration_area').append($row.data("area"));
	} else {
		$(review_dialogue + '#review_registration_area').css('display', 'none');
		$(review_dialogue + '#review_area_label').css('display', 'none');
	}
}

function showInfoDialog(info_text, title) {
    $.alert({
        title: title,
        content: info_text,
        confirmButton: 'Ok',
        backgroundDismiss: true,
    });
}

function closeDialogue(e) {
	if (e) {
		e.preventDefault();
	}

	if ($(".popup:visible").length > 0) {
		if ((ask_before_leave) && (ask_before_leave = true)) {
		    $.confirm({
		        title: lang.hide_dialog_confirm,
		        content: lang.hide_dialog_info,
    			escapeKey: true,
		        confirm: function(){
					// Hide the last visible dialog
					$(".popup:visible").last().hide(0, function() {
						// Hide the overlay if no more dialogs are visible
						if ($(".popup:visible, .form:visible").length === 0) {
							$("#overlay").fadeOut();
						}
						ask_before_leave = false;
					});
		        },
		        cancel: function(){}
		    });
		} else {
			if ($(".jconfirm-box:visible").length < 1) { 
				$(".popup:visible").last().hide(0, function() {
					if ($(".popup:visible, .form:visible").length === 0) {
						$("#overlay").fadeOut();
					}
					ask_before_leave = false;
				});
			}
		}
	}
}

function closeForm(e) {
	if (e) {
		e.preventDefault();
	}
	if ($(".form:visible").length > 0 && $("#form_register:visible").length === 0) {
		if ((ask_before_leave) && (ask_before_leave = true)) {
		    $.confirm({
		        title: lang.hide_dialog_confirm,
		        content: lang.hide_dialog_info,
    			escapeKey: true,
		        confirm: function(){
					// Hide the last visible form
					$(".form:visible").last().hide(0, function() {
						// Hide the overlay if no more dialogs are visible
						if ($(".popup:visible, .form:visible").length === 0) {
							$("#overlay").fadeOut();
						}

						ask_before_leave = false;
					});
		        },
		        cancel: function(){}
		    });
		} else {
			if ($(".jconfirm-box:visible").length < 1) {
				// Hide the last visible dialog
				$(".form:visible").last().hide(0, function() {
					// Hide the overlay if no more dialogs are visible
					if ($(".popup:visible, .form:visible").length === 0) {
						$("#overlay").fadeOut();
					}

					ask_before_leave = false;
				});
			}
		}
	}
}
function markAsSent(link, id) {
		$.ajax({
			url : link,
			type: 'POST',
			data: 'id='+id
		}).success(function(response){
			document.location.reload();
		});
}
function sendInvoice(link, comment){
		$.ajax({
			url : link,
			type: 'POST',
			data: 'comment='+comment+'&ajax=1'
		}).success(function(response){
			window.location = '/administrator/invoices';
		});
}
function cancelMyself(link){
		$.ajax({
			url : link,
			type: 'POST',
			data: '&ajax=1'
		}).success(function(response){
			window.location = '/exhibitor/myBookings';
		});
}
function denyPosition(link, comment, position, status, clicked){
		$.ajax({
			url : link,
			type: 'POST',
			data: 'comment='+comment+'&positionName='+position+'&status='+status + '&ajax=1'
		}).success(function(response){
			if (clicked) {
				$(clicked.parentNode.parentNode).remove();
				maptool.update();
			} else {
				window.location = '/administrator/newReservations';
			}
		});
}
function denyRegistration(link, comment){
		$.ajax({
			url : link,
			type: 'POST',
			data: 'comment='+comment
		}).success(function(response){
			window.location = '/administrator/newReservations';
		});
}

function ajaxContent(e, handle) {
	e.preventDefault();
	e.stopPropagation();
	$.ajax({
		url: 'ajax/page.php',
		type: 'POST',
		data: 'ajaxContent=' + handle,
		success: function(response) {
			if (response != '') {
				
				$('#popupform').remove();
				maptool.closeDialogues();
				
				$('#overlay').show();
				var html = '<div class="dialogue popup" style="display:block;"><img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>' + response + '</div>';
				$('body').prepend(html);
				
			}
			
		}
	});
	return false;
}

function ajaxLoginForm(form) {
	if (!form.data('ajax-form')) {

		form.data('ajax-form', true);

		form.submit(function(e) {
			e.preventDefault();

			$.ajax({
				url: form.prop('action'),
				type: 'POST',
				data: form.serialize() + '&login=true',
				success: function(response) {
					if (response.error) {
						$('.error', form).text(response.error);
					} else if (response.redirect) {
						window.location.href = response.redirect;
					}
				}
			});
		});
	}
}

function positionDialogue(id) {
	var dialogue = $('#' + id);
	var viewportWidth = Math.max(window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth || 0);
	var viewportHeight = Math.max(window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight || 0);
	var popupMaxWidth = Math.max(448, viewportWidth * .47);
	var popupMaxHeight = Math.max(328, viewportHeight * .80);
	//console.log(id);
	//Exceptions to width and height
	switch (id) {
		case "showUserDialogue":
			popupMaxWidth = Math.max(950, viewportWidth * .80);
			popupMaxHeight = Math.max(328, viewportHeight * .80);
			break;		
		case "export_popup":
			popupMaxWidth = Math.max(950, viewportWidth * .70);
			popupMaxHeight = Math.max(328, viewportHeight * .80);
			break;
		case "innerPopup":
			popupMaxWidth = Math.max(550, viewportWidth * .50);
			break;	
		case "book_position_form":
		case "reserve_position_form":
		case "fair_registration_form":
		case "apply_mark_form":
		case "review_prel_dialogue":
		case "popupform_register":
//		console.log(viewportHeight);
//		console.log(viewportWidth);
			popupMaxWidth = 900;
			popupMaxHeight = Math.max(328, viewportHeight * .90);
			break;
		case "edit_position_dialogue":
		case 'fair_registration_paste_type_dialogue':
			popupMaxWidth = 400;
			break;
		case "note_dialogue":
			popupMaxWidth = 600;
			break;
		case "preliminaryConfirm":
		case "fairRegistrationConfirm":
		case "contactPopup":
		case "rulesPopup":
			popupMaxWidth = 480;
			break;
		case "more_info_dialogue":
			popupMaxWidth = 600;
			break;
		case "preliminary_bookings_dialogue":
			//popupMaxWidth = Math.max(900, viewportWidth * .70);
			popupMaxHeight = Math.max(328, viewportHeight * .90);
			popupMaxWidth = 900;
			break;
//		case "popupform_register":
			//popupMaxWidth = Math.max(910, viewportWidth * .70);
			//popupMaxHeight = Math.max(720, viewportHeight * .90);
/*			popupMaxWidth = 910;
			popupMaxHeight = 720;
			break;	*/
	}

	if (id == "innerPopup") {
		dialogue.css({
			'max-height': popupMaxHeight/15 + 'em'
		});
	} else if ((id == "book_position_form") || (id == "reserve_position_form") || (id == "apply_mark_form") || (id == "fair_registration_form") || (id == "popupform_register")) {
		dialogue.css({
			'top': (viewportHeight/10)/12 + 'vh',
			'max-height': popupMaxHeight/12 + 'em',
			'left': (viewportWidth/6)/12 + 'vw',
		});
	} else if (id == "preliminary_bookings_dialogue") {
		dialogue.css({
			'max-height': popupMaxHeight/12 + 'em',
			'margin-left': '-' + (popupMaxWidth / 2)/12 + 'em',
			'margin-top': '-' + ((popupMaxHeight) / 3)/12 + 'em',
			'width': 'auto',
			'max-width': popupMaxWidth/12 + 'em',
		});		
	} else if (id == "review_prel_dialogue") {
		dialogue.css({
			'height': 'auto',
			'top': (window.pageYOffset + (popupMaxHeight / 2.5))/12 + 'em',
			'left': (viewportWidth/4)/12 + 'vw',
			'width': popupMaxWidth/12 + 'em',
		});
	} else if (id == "export_popup") {
		dialogue.css ({
			'top': (window.pageYOffset + (popupMaxHeight / 2))/12 + 'em',
			'width': popupMaxWidth/12 + 'em',
			'max-width': 870/12 + 'em',
			'margin-left': '-' + (popupMaxWidth / 3)/12 + 'em',
			'margin-top': '-' + ((popupMaxHeight) / 3)/12 + 'em'
		});
	} else if (id == "showUserDialogue") {
		if(jQuery.browser.mobile){
			dialogue.css ({
				'top': (window.pageYOffset + (popupMaxHeight / 2))/12 + 'em',
				'width': popupMaxWidth/12 + 'em',
				'max-height': 34 + 'em',
				'margin-left': '-' + (popupMaxWidth / 2)/12 + 'em',
				'margin-top': '-' + ((popupMaxHeight) / 3)/12 + 'em'
			});
		} else {
			dialogue.css ({
				'top': (window.pageYOffset + (popupMaxHeight / 2))/12 + 'em',
				'width': 80 + '%',
				'max-width': 1200/12 + 'em',
				'max-height': popupMaxHeight/12 + 'em',
				'margin-left': '-' + (popupMaxWidth / 2.5)/12 + 'em',
				'margin-top': '-' + ((popupMaxHeight) / 3)/12 + 'em'
			});
		}
	} else {
		dialogue.css({
			'top': (window.pageYOffset + (popupMaxHeight / 2))/12 + 'em',
			'width': popupMaxWidth/12 + 'em',
			'max-height': popupMaxHeight/12 + 'em',
			'margin-left': '-' + (popupMaxWidth / 2)/12 + 'em',
			'margin-top': '-' + ((popupMaxHeight) / 3)/12 + 'em'
		});
	}

};

function showUser(e) {
	e.preventDefault();
	$('#overlay').show();

	$.ajax({
		url: $(this).prop('href'),
		type: "GET",
		success: function (response) {
			var dialogue = $('#showUserDialogue');
			if (dialogue.length === 0) {
				dialogue = $('<div class="dialogue popup" id="showUserDialogue"></div>');
				$('body').append(dialogue);
			}

			dialogue.html('<img src="images/icons/close_dialogue.png" class="closeDialogue" style="margin-top:-3.7em;" />' + response);
			dialogue.show();

			positionDialogue('showUserDialogue');
			positionDialogue('innerPopup');

			useScrolltable(dialogue.find("#profileBookings"));
			useScrolltable(dialogue.find("#profileBookingsCurrentFair"));
		}
	});
}

function positionExcelCheckboxes($checkboxes, $table) {
	var $tableHeaders = $table.find(".excelHeader");

	$checkboxes.each(function (i) {
		var $checkbox = $(this),
			$th = $tableHeaders.eq(i);

		$th.append($checkbox);
	});
}

function multiCheck(checkbox, box) {
	$('#'+box+' > tbody > tr').children(':last-child').children().prop('checked', $(checkbox).prop('checked'));
}

/* A function to collect data from a specified HTML table (the inparameter takes the ID of the table) */
function prepareTable() {
	var rowArray = new Array();
	var colArray = new Array();

	var tableId = $(this).data("table");

	var table = $('#' + tableId);
	var rows = 0;

	$("#" + tableId).find("th input").each(function () {
		var $this = $(this);

		if ($this.prop("checked")) {
			colArray.push($this.val());
		}
	});

	table.children(':last-child').children().each(function(){
		var thisRow = $(this);
		var thisRowCheckBox = thisRow.children(':last-child').children();

		if (thisRowCheckBox.prop('checked') == true){
			rowArray.push(thisRowCheckBox.attr('id'));
		}
	});
	
	if(tableId == "popupbooked"){
		exportTableToExcel(rowArray, colArray, 1);
	} else if(tableId == "popupreserved"){
		exportTableToExcel(rowArray, colArray, 2);
	} else if(tableId == "popupprem"){
		exportTableToExcel(rowArray, colArray, 3);
	} else if (tableId === "popupconnected") {
		exportTableToExcel(rowArray, colArray, 2);
	} else if(tableId == "popupiprem"){
		exportTableToExcel(rowArray, colArray, 4);		
	}
	
	rowArray = [];
	colArray = [];
}

function getHorizontalCenter($parent, $child) {
	return ($parent.width() / 2) - ($child.width() / 2);
}

function goBack() {
    window.history.back();
}

function showExportPopup(e) {
	e.preventDefault();
	$('#overlay').show();

	if ($('#export_popup').length > 0) {
		$('#export_popup').remove();
	}

	var html = '<div id="export_popup" class="dialogue popup" style="width: 500px; text-align: left;">' + '<h3>' + lang.export_headline + '</h3>' + '<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" />', 
		export_popup, 
		button = $(e.target), 
		field_groups = export_fields[button.data('for')], 
		i, j;

	for (i in field_groups) {
		html += '<div class="export-group"><strong>' + i;
		html += '<br /><label><input type="checkbox" id="excexpgrp-' + i + '" class="check-all" data-group="excexpgrp-' + i + '" /><label class="squaredFour" style="display:inline-block" for="excexpgrp-' + i + '" /> ' + lang.select_all + '</label>';

		for (j in field_groups[i]) {
			html += '<label><input type="checkbox" name="field[' + j + ']" class="excexpgrp-' + i + '" id="field[' + j + ']" /><label class="squaredFour" style="display:inline-block" for="field[' + j + ']" /> ' + field_groups[i][j] + '</label>';
		}

		html += '</strong></div>';
	}

	html += '<p class="right"><a href="#" class="redbutton mediumbutton close-popup">' + lang.cancel + '</a> <input type="submit" class="greenbutton mediumbutton" value="' + lang.export_excel + '" /></p></div>';

	export_popup = $(html);
	export_popup.show();
	$('.close-popup', export_popup).click(closeDialogue);
	$(button).before(export_popup);


	positionDialogue("export_popup");
}
function sendVerifyCloned(e) {
	e.preventDefault();

	var button = $(e.target);
	var table_form = $(button.prop('form'));

    $.confirm({
        title: lang.confirmationLinkQuestion1, 
        content: lang.confirmationLinkQuestion2, 
        confirm: function(){
			var selected_exhibitor_ids = [];

			$('input[name*=rows]:checked', table_form).each(function(index, input) {
				selected_exhibitor_ids.push('exid[]=' + $(input).data('exid'));
			});

			$.ajax({
				url: 'administrator/mailVerifyCloned',
				method: 'POST',
				data: '&' + selected_exhibitor_ids.join('&'),
				success: function(response) {
					$.alert({
					    content: lang.email_sent,
					    confirm: function() {
					    	document.location.reload();
					    }
					});
				}
			});

        },
        cancel: function(){}
    });
}

function showSmsSendPopup(e) {
	e.preventDefault();
	$('#overlay').show();
	ask_before_leave = true;

	if ($('#sms_send_popup').length > 0) {
		$('#sms_send_popup').remove();
	}

	var sms_price = 1.0;
	var button = $(e.target);
	var table_form = $(button.prop('form'));
	var num_recipients = [];
		$('input[name*=rows]:checked', table_form).each(function(index, input) {
			num_recipients.push($(input).data('userid'));
		});	
	jQuery.unique(num_recipients);
	var sms_send_popup = $('<form id="sms_send_popup" class="dialogue popup" style="width: 400px; position:fixed;"><h2><img src="images/icons/smsicon.png" alt="" class="icon_sms_popup" />' 
		+ lang.sms_popup_title + '</h2><img src="images/icons/close_dialogue.png" alt="" class="closeDialogue close-popup" />'
		+ '<p><strong>' + lang.sms_enter_message + '</strong><textarea style="width:350px" name="sms_text"></textarea></p>'
		+ '<p><strong>' + lang.sms_max_chars + '</strong><strong id="sms_send_chars_count"></strong></p>'
		+ '<p>' + lang.sms_num_recipients + ': <strong>' + num_recipients.length + '</strong><br />'
		+ lang.sms_estimated_cost + ': <strong id="sms_send_cost"></strong> kr ex moms</p>'
		+ '<p><button type="submit" class="greenbutton mediumbutton">' + lang.send + '</button></p>'
		+ '<ul class="dialog-tab-list"><li><a href="#sms_send_log" class="js-select-tab">'
		+ lang.sms_log + '</a></li><li><a href="#sms_send_errors" class="js-select-tab">' + lang.errors + ' (<span id="sms_send_errors_count">0</span>)</a></li></ul>'
		+ '<div class="dialog-tab" id="sms_send_log"><p></p></div>'
		+ '<div class="dialog-tab" id="sms_send_errors"><ul></ul></div></form>');

	var error_list = $('#sms_send_errors ul', sms_send_popup);

	sms_send_popup.show();

	sms_send_popup.on('submit', function(e) {
		e.preventDefault();

		if (confirm(lang.sms_accept_before_send)) {
			error_list.empty();
			$('#sms_send_errors_count').text(0);

			var selected_user_ids = [];

			$('input[name*=rows]:checked', table_form).each(function(index, input) {
				selected_user_ids.push('user[]=' + $(input).data('userid'));
			});

			$.ajax({
				url: 'sms/send',
				method: 'POST',
				data: sms_send_popup.serialize() + '&fair=' + button.data('fair') + '&' + selected_user_ids.join('&'),
				success: function(response) {
					if (response.error) {
						error_list.append($('<li></li>').text(response.error));
						$('.dialog-tab-list li:nth-child(2)').css("background-color", "red");

					} else if (response.errors) {
						for (var i = 0; i < response.errors.length; i++) {
							error_list.append($('<li></li>').text(response.errors[i]));
							$('.dialog-tab-list li:nth-child(2)').css("background-color", "red");
						}
					}

					if (response.num_sent > 0) {
						$('#sms_send_log p').text(lang.sms_sent_correct);
					}

					$('#sms_send_errors_count').text(error_list.children().length);
				}
			});
		}
	});

	$('body').append(sms_send_popup);
	$('.dialog-tab-list', sms_send_popup).tabs();

	var chars_count = $('#sms_send_chars_count');
	var cost_count = $('#sms_send_cost');
	var count_timer = null;
	$('textarea', sms_send_popup).on('keyup', function(e) {
		if (count_timer !== null) {
			clearTimeout(count_timer);
		}

		var input = e.target;
		count_timer = setTimeout(function() {
			updateCount(input);
			count_timer = null;
		}, 10);
	}).trigger('keyup');

	function updateCount(input) {
		var page = Math.max(1, Math.ceil(input.value.length / 160));
		var left = (160 * page) - input.value.length;

		if (input.value.length >= 640) {
			input.value = input.value.substring(0, 640);
			left = 0;
			page = 4;
		}

		chars_count.text(left + ' | ' + page);
		cost_count.text((sms_price * page * num_recipients.length).toFixed(2));
	}
}

var Comments = (function() {
	var current_modelview = null;
	var current_collectionview = null;
	var close_after_create = false;
	var template;

	function getModelView(reference) {
		return reference.parents('[data-model=Comment]');
	}

	function saveCommentListener(e, options) {
		var target = $(e.target);

		current_collectionview = $(options.collection_view_selector);
		close_after_create = options.close_dialog_after;
		template = options.template;

		$.ajax({
			url: e.target.action,
			method: 'POST',
			data: target.serialize() + '&save=1&template=' + template,
			success: function(response) {
				if (response.error) {
					alert(response.error);
				} else {
					if (response.saved) {
						$('#save_confirm').show();
						setTimeout(function() {
							$('#save_confirm').fadeOut('slow');
						}, 2000);
						$("#save_confirm input").click(function() {
							$(this).parent().parent().fadeOut("fast");
						});						

						if (current_modelview.length > 0) {
							$('[data-key=comment]', current_modelview).text(response.model.comment);
							$('[data-key=type]', current_modelview).html(response.model.type_html);
							closeDialogue();
						}

					} else if (response.deleted) {
						if (current_modelview) {
							current_modelview.remove();
						}
						$('#delete_confirm').show();						
						setTimeout(function() {
							$('#delete_confirm').fadeOut('slow');
						}, 2000);
						$("#delete_confirm input").click(function() {
							$(this).parent().parent().fadeOut("fast");
						});						


						closeDialogue();

					} else if (current_collectionview.length > 0) {
						$('.empty-placeholder', current_collectionview).remove();
						current_collectionview.prepend(response);

						// Reset form values
						e.target.reset();

						if (close_after_create) {
							closeDialogue();
						}
					}
				}
			}
		});
	}

	function initDialog(response, options) {
		if ($('#' + options.dialog_id).length > 0) {
			$('#' + options.dialog_id).remove();
		}

		if (response.error) {
			alert(response.error);
		} else {
			var note_dialogue = $('<div id="' + options.dialog_id + '" class="dialogue popup" style="min-width: 41.66em;">'
				+ response + '</div>');
			var user_select = $('.js-user-select', note_dialogue);

			$('form', note_dialogue).on('submit', function(e) {
				e.preventDefault();
				saveCommentListener(e, options);
			});

			if (user_select.length > 0) {
				var user_search = $('<input type="text" id="note_user_search" />');
				user_select.siblings('strong').before(user_search);
				user_search.wrap('<label>' + lang.search_exhibitor + '<br /></label><br />');

				user_search.on('keypress', function(e) {
					if (e.keyCode == 13) {
						e.preventDefault();
					} else {
						setTimeout(function() {
							var query = user_search.val().toLowerCase();
							var selectedFirst = false;
							if (query == '') {
									$('option', user_select).show();
							} else {
								$('option', user_select).each(function(index, option) {
									option = $(option);
									if (option.text().toLowerCase().indexOf(query) == -1) {
										option.prop('selected', false);
										option.hide();

									} else {
										if (!selectedFirst) {
											option.prop('selected', true);
											selectedFirst = true;
										}

										option.show();
									}
								});
							}
						}, 0);
					}
				});
			}

			$('body').append(note_dialogue);

			$('#overlay').show();
			note_dialogue.show();
		}
	}

	function showActionDialog(e) {
		e.preventDefault();
		var target = (e.target.nodeName === 'A' ? e.target : e.target.parentNode);
		var options = {
			dialog_id: 'note_edit_dialogue'
		};

		current_modelview = getModelView($(target));

		$.ajax({
			url: target.href,
			type: 'GET',
			success: function(response) {
				initDialog(response, options);
			}
		});
	}

	function showDialog(options) {
		options.dialog_id = 'note_dialogue';

		$.ajax({
			url: 'comment/dialog/' + options.user_id + '/' + options.fair_id + '/' + options.position_id,
			type: 'GET',
			success: function(response) {
				initDialog(response, options);
			}
		});
	}

	/* Init */
	$(function() {
		$('body')
			.on('click', '.js-comment-action', showActionDialog)
			.on('click', '.js-show-comment-dialog', function(e) {
				e.preventDefault();
				var target = $(e.target.nodeName === 'A' ? e.target : e.target.parentNode);

				showDialog({
					user_id: target.data('user') || 0,
					fair_id: target.data('fair') || 0,
					position_id: target.data('position') || 0,
					collection_view_selector: target.data('view') || '#note_dialogue .commentList ul',
					close_dialog_after: target.data('close') || false,
					template: target.data('template') || 'comment_item'
				});
			})
		;
	});

	return {
		showDialog: showDialog
	};
}());

function checkAll(e) {
	var check_all = $(e.target), 
		checkbox;

	$('input[type=checkbox]').each(function() {
		checkbox = $(this);

		if (checkbox.hasClass(check_all.data('group'))) {
			checkbox.filter(':visible').prop('checked', check_all.prop('checked'));
		}
	});
}

function useScrolltable(table) {
	table
		.removeClass('use-scrolltable')
		.addClass('scrolltable')
		.wrap('<div class="scrolltable-wrap"></div>')
		
		.floatThead({
			scrollContainer: function(table) {
				return table.parent();
			}
		});

	// add parser through the tablesorter addParser method 
	$.tablesorter.addParser({ 
	    // set a unique id 
	    id: 'ddMMMyy', 
	    is: function(s) { 
	        // return false so this parser is not auto detected 
	        return false; 
	    }, 
	    format: function(s) { 
	        // parse the string as a date and return it as a number 
	        return +new Date(s);
	    }, 
	    // set type, either numeric or text 
	    type: 'numeric' 
	}); 
	// Fix tablesorter
	table.parent().siblings('.floatThead-container').find('thead').on('click', 'th', function(e) {
		var target = $(e.target);

		if (typeof target.data('sorter') === 'undefined' && typeof target.parent().data('sorter') === 'undefined') {
			table.find('th:eq(' + target.index() + ')').trigger('sort');
		}
	});

	table.tablesorter()
		.on("sortStart", function () {
			var wrapper = this.parentNode;

			window.setTimeout(function () {
				wrapper.style.maxHeight = "599px";

				window.setTimeout(function () {
					wrapper.style.maxHeight = "600px";
				}, 10);
			}, 10);
		});
}

$(document).ready(function() {
/*
	var cfsupdateinfo = localStorage.getItem('cfsupdateinfo2');
	if (cfsupdateinfo) {
		$('#cfs_info_div').css('display', 'none');
	} else {
		$('#overlay').show();
		$('#cfs_info_div').css('display', 'block');
	}
	$('#cfs_info_div #cfs_info_ok').on("click", function() {
		localStorage.setItem('cfsupdateinfo2', $(this).attr('id'));
		$('#overlay').hide();
		$('#cfs_info_div').css('display', 'none');	
	});
*/
var body = document.body,
	width = document.body.clientWidth,
    html = document.documentElement;

var height = Math.max( body.scrollHeight, body.offsetHeight, 
                       html.clientHeight, html.scrollHeight, html.offsetHeight );

if(width > 1224){
	var mainmenustate = localStorage.getItem('mainmenustate');
	if (mainmenustate) {
		var btnheight = $('#new_header_show').outerHeight();
		console.log(btnheight);
		var mainmenutopbtn = localStorage.getItem('mainmenutopbtn');
		if (mainmenutopbtn) {
			if (mainmenutopbtn > height - btnheight) {
				mainmenutopbtn = height - btnheight;
			}
			if (mainmenutopbtn < 0) {
				mainmenutopbtn = 0;
			}
			$("#new_header_show").css({top:mainmenutopbtn + "px"});
			$("#new_header_hide").css({top:mainmenutopbtn + "px"});
		}
		if (mainmenustate == 'active') {
			$( "#new_header" ).show();
			$( "#new_header_show" ).hide();
			$( "#new_header_hide" ).show();
		} else {
			$( "#new_header" ).hide();
			$( "#new_header_show" ).show();
			$( "#new_header_hide" ).hide();
		}
	} else {
			$( "#new_header" ).show();
			$( "#new_header_show" ).hide();
			$( "#new_header_hide" ).show();	
	}
	$("#new_header_show").click(function () {
	    if ($(this).hasClass('noclick')) {
	        $(this).removeClass('noclick');
	    }
	    else {
	        // actual click event code
			$( "#new_header" ).show("slide", { direction: "left" }, 500);
			$( "#new_header_show" ).hide();
			$( "#new_header_hide" ).delay("slow").fadeIn();
			localStorage.setItem('mainmenustate', 'active');
	    }		

	});
	$("#new_header_hide").click(function () {
		  $( "#new_header" ).hide("slide", { direction: "left" }, 500);
		  $( "#new_header_show" ).delay("slow").fadeIn();
		  $( "#new_header_hide" ).hide();
		  localStorage.setItem('mainmenustate', 'hidden');
	});
	$("#new_header a").click(function() {
		localStorage.setItem('mainmenustate', 'hidden');
	});

}
	$(function() {$("#new_header_show").draggable({
			cancel: false, 
			axis:"y",
			containment: "window",
			scroll: false,
			start: function(event, ui) {
	        	$(this).addClass('noclick');
	    	},
			stop: function(event, ui) {
	        	localStorage.setItem('mainmenutopbtn', $(this).offset().top);
	        	$("#new_header_hide").css({top:$(this).offset().top + "px"});
	    	}
		});
	});

//jQuery time
var current_fs, next_fs, previous_fs; //fieldsets
var left, opacity, scale; //fieldset properties which we will animate
var animating; //flag to prevent quick multi-click glitches

$(".next").click(function(){
	if(animating) return false;
	animating = true;

	current_fs = $(this).parent();
	next_fs = $(this).parent().next();
	
	//activate next step on progressbar using the index of next_fs
	$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
	
	//show the next fieldset
	next_fs.show(); 
	//hide the current fieldset with style
	current_fs.animate({opacity: 0}, {
		step: function(now, mx) {
			//as the opacity of current_fs reduces to 0 - stored in "now"
			//1. scale current_fs down to 80%
			scale = 1 - (1 - now) * 0.2;
			//2. bring next_fs from the right(50%)
			left = (now * 50)+"%";
			//3. increase opacity of next_fs to 1 as it moves in
			opacity = 1 - now;
			current_fs.css({'transform': 'scale('+scale+')'});
			next_fs.css({'left': left, 'opacity': opacity});
		}, 
		duration: 800, 
		complete: function(){
			current_fs.hide();
			animating = false;
		}, 
		//this comes from the custom easing plugin
		easing: 'easeOutSine'
	});
});

$("#reserve_first_step").click(function(){

	var count = 0;
	var cats = [];
	$('#reserve_category_scrollbox > tbody > tr > td').each(function(){
		var val = $(this).children('input:checked').val();
		if(val != null){
			cats[count] = val;
			count = count+1;
		}
	});
	if (count == 0) {
		$('#reserve_category_scrollbox_div').css('border', '0.166em solid #f00');
		animating = true;
	} else {
		$('#reserve_category_scrollbox_div').css('border', 'none');
		animating = false;
	}

	if ($("#reserve_expires_input").val().match(/^\d\d-\d\d-\d\d\d\d \d\d:\d\d$/)) {
		var dateParts = $("#reserve_expires_input").val().split('-');
		dt = new Date(parseInt(dateParts[2], 10), parseInt(dateParts[1], 10)-1, parseInt(dateParts[0], 10));
		// Add one day, since it should be up to and including.
		dt.setDate(dt.getDate(+1));
		if (dt < new Date()) {
			animating = false;
			$("#reserve_expires_input").css('border-color', 'red');
			return;
		}
	} else {
		animating = false;
		$("#reserve_expires_input").css('border-color', 'red');
		return;
	}

	if(animating) return false;
	animating = true;
	
	current_fs = $(this).parent();
	next_fs = $(this).parent().next();
	
	//activate next step on progressbar using the index of next_fs
	$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
	
	//show the next fieldset
	next_fs.show(); 
	//hide the current fieldset with style
	current_fs.animate({opacity: 0}, {
		step: function(now, mx) {
			//as the opacity of current_fs reduces to 0 - stored in "now"
			//1. scale current_fs down to 80%
			scale = 1 - (1 - now) * 0.2;
			//2. bring next_fs from the right(50%)
			left = (now * 50)+"%";
			//3. increase opacity of next_fs to 1 as it moves in
			opacity = 1 - now;
			current_fs.css({'transform': 'scale('+scale+')'});
			next_fs.css({'left': left, 'opacity': opacity});
		}, 
		duration: 800, 
		complete: function(){
			current_fs.hide();
			animating = false;
		}, 
		//this comes from the custom easing plugin
		easing: 'easeOutSine'
	});
});


$("#book_first_step").click(function(){

	var count = 0;
	var cats = [];
	$('#book_category_scrollbox > tbody > tr > td').each(function(){
		var val = $(this).children('input:checked').val();
		if(val != null){
			cats[count] = val;
			count = count+1;
		}
	});
	if (count == 0) {
		$('#book_category_scrollbox_div').css('border', '0.166em solid #f00');
		animating = true;
	} else {
		$('#book_category_scrollbox_div').css('border', 'none');
		animating = false;
	}

	if(animating) return false;
	animating = true;
	
	current_fs = $(this).parent();
	next_fs = $(this).parent().next();
	
	//activate next step on progressbar using the index of next_fs
	$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
	
	//show the next fieldset
	next_fs.show(); 
	//hide the current fieldset with style
	current_fs.animate({opacity: 0}, {
		step: function(now, mx) {
			//as the opacity of current_fs reduces to 0 - stored in "now"
			//1. scale current_fs down to 80%
			scale = 1 - (1 - now) * 0.2;
			//2. bring next_fs from the right(50%)
			left = (now * 50)+"%";
			//3. increase opacity of next_fs to 1 as it moves in
			opacity = 1 - now;
			current_fs.css({'transform': 'scale('+scale+')'});
			next_fs.css({'left': left, 'opacity': opacity});
		}, 
		duration: 800, 
		complete: function(){
			current_fs.hide();
			animating = false;
		}, 
		//this comes from the custom easing plugin
		easing: 'easeOutSine'
	});
});

$("#prel_first_step").click(function(){

	var count = 0;
	var cats = [];
	$('#apply_category_scrollbox > tbody > tr > td').each(function(){
		var val = $(this).children('input:checked').val();
		if(val != null){
			cats[count] = val;
			count = count+1;
		}
	});
	if (count == 0) {
		$('#apply_category_scrollbox_div').css('border', '0.166em solid #f00');
		animating = true;
	} else {
		$('#apply_category_scrollbox_div').css('border', 'none');
		animating = false;
	}

	if(animating) return false;
	animating = true;
	
	current_fs = $(this).parent();
	next_fs = $(this).parent().next();
	
	//activate next step on progressbar using the index of next_fs
	$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
	
	//show the next fieldset
	next_fs.show(); 
	//hide the current fieldset with style
	current_fs.animate({opacity: 0}, {
		step: function(now, mx) {
			//as the opacity of current_fs reduces to 0 - stored in "now"
			//1. scale current_fs down to 80%
			scale = 1 - (1 - now) * 0.2;
			//2. bring next_fs from the right(50%)
			left = (now * 50)+"%";
			//3. increase opacity of next_fs to 1 as it moves in
			opacity = 1 - now;
			current_fs.css({'transform': 'scale('+scale+')'});
			next_fs.css({'left': left, 'opacity': opacity});
		}, 
		duration: 800, 
		complete: function(){
			current_fs.hide();
			animating = false;
		}, 
		//this comes from the custom easing plugin
		easing: 'easeOutSine'
	});
});


$("#registration_first_step").click(function(){

	var count = 0;
	var cats = [];
	$('#registration_category_scrollbox > tbody > tr > td').each(function(){
		var val = $(this).children('input:checked').val();
		if(val != null){
			cats[count] = val;
			count = count+1;
		}
	});
	if ($('#registration_area_input').val()) {
		$('#registration_area_input').css('border', 'none');
		if (count == 0) {
			$('#registration_category_scrollbox_div').css('border', '1px solid #f00');
			animating = true;
		} else {
			$('#registration_category_scrollbox_div').css('border', 'none');
			animating = false;
		}		
	} else {
		if (count == 0) {
			$('#registration_category_scrollbox_div').css('border', '1px solid #f00');
		} else {
			$('#registration_category_scrollbox_div').css('border', 'none');
		}
		$('#registration_area_input').css('border', '1px solid #f00');
		animating = true;
	}



	if(animating) return false;
	animating = true;
	
	current_fs = $(this).parent();
	next_fs = $(this).parent().next();
	
	//activate next step on progressbar using the index of next_fs
	$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
	
	//show the next fieldset
	next_fs.show(); 
	//hide the current fieldset with style
	current_fs.animate({opacity: 0}, {
		step: function(now, mx) {
			//as the opacity of current_fs reduces to 0 - stored in "now"
			//1. scale current_fs down to 80%
			scale = 1 - (1 - now) * 0.2;
			//2. bring next_fs from the right(50%)
			left = (now * 50)+"%";
			//3. increase opacity of next_fs to 1 as it moves in
			opacity = 1 - now;
			current_fs.css({'transform': 'scale('+scale+')'});
			next_fs.css({'left': left, 'opacity': opacity});
		}, 
		duration: 800, 
		complete: function(){
			current_fs.hide();
			animating = false;
		}, 
		//this comes from the custom easing plugin
		easing: 'easeOutSine'
	});
});
$("#registration_review").click(function(){

	if ($('#registration_commodity_input').val()) {
		$('#registration_commodity_input').css('border', 'none');
		animating = false;		
	} else {
		$('#registration_commodity_input').css('border', '1px solid #f00');
		animating = true;		
	}

	if(animating) return false;
	animating = true;
	
	current_fs = $(this).parent();
	next_fs = $(this).parent().next();
	
	//activate next step on progressbar using the index of next_fs
	$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
	
	//show the next fieldset
	next_fs.show(); 
	//hide the current fieldset with style
	current_fs.animate({opacity: 0}, {
		step: function(now, mx) {
			//as the opacity of current_fs reduces to 0 - stored in "now"
			//1. scale current_fs down to 80%
			scale = 1 - (1 - now) * 0.2;
			//2. bring next_fs from the right(50%)
			left = (now * 50)+"%";
			//3. increase opacity of next_fs to 1 as it moves in
			opacity = 1 - now;
			current_fs.css({'transform': 'scale('+scale+')'});
			next_fs.css({'left': left, 'opacity': opacity});
		}, 
		duration: 800, 
		complete: function(){
			current_fs.hide();
			animating = false;
		}, 
		//this comes from the custom easing plugin
		easing: 'easeOutSine'
	});
});
$(".previous").click(function(){
	if(animating) return false;
	animating = true;
	
	current_fs = $(this).parent();
	previous_fs = $(this).parent().prev();
	
	//de-activate current step on progressbar
	$("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");
	
	//show the previous fieldset
	previous_fs.show(); 
	//hide the current fieldset with style
	current_fs.animate({opacity: 0}, {
		step: function(now, mx) {
			//as the opacity of current_fs reduces to 0 - stored in "now"
			//1. scale previous_fs from 80% to 100%
			scale = 0.8 + (1 - now) * 0.2;
			//2. take current_fs to the right(50%) - from 0%
			left = ((1-now) * 50)+"%";
			//3. increase opacity of previous_fs to 1 as it moves in
			opacity = 1 - now;
			current_fs.css({'left': left});
			previous_fs.css({'transform': 'scale('+scale+')', 'opacity': opacity});
		}, 
		duration: 800, 
		complete: function(){
			current_fs.hide();
			animating = false;
		}, 
		//this comes from the custom easing plugin
		easing: 'easeOutSine'
	});
});	

	jQuery('.datetime').datetimepicker({
		dateFormat:'dd-mm-yy'
	});
	jQuery('.datepicker.date').datetimepicker({
		showTimepicker:false,
		dateFormat:'dd-mm-yy',
	});

	$('.datepicker.date').datepicker('option', 'dateFormat', 'dd-mm-yy');
	//$('#languages a.selected').attr('href', 'javascript:void(0)').append('&nbsp;&nbsp;<img src="images/arrow_down.png" alt=""/>').prependTo('#languages');
	$('.loginlink').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		$('#overlay').show();

		var url = $(this).attr('href');
		var html = '<form action="' + url + '" method="post" id="popupform_login" class="dialogue2 popup" style="display:inline-block;">'
				 + 		'<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0 0 0 268px;"/>'
				 +		'<img src="images/button_icons/Chartbooker Fair System Logotype.png" alt="" class="nouser_cfslogo" />'
				 +		'<p class="logo_text">' + lang.logo_text + '</p>'
				 +		'<p class="error"></p>'
				 +		'<fieldset style="margin: 0 10em; max-width: 25em;">'
				 + 		'<label for="user" style="font-size: 1.33em;">' + lang.login_username + '</label>'
				 + 		'<input type="text" class="login_textfield" name="user" id="user" size="20"/>'
				 + 		'<label for="pass" style="font-size: 1.33em;">' + lang.login_password + '</label>'
				 + 		'<input type="password" class="login_textfield" name="pass" id="pass" size="20"/>'
				 +		'</fieldset>'
				 +		'<p class="forgot"><a href="user/resetPassword' + (typeof fair_url === 'string' ? '/backref/' + fair_url : '') + '">' + lang.forgot_pass + '</a></p>'
				 + 		'<p><input type="submit" value="' + lang.sign_in + '" name="login" class="greenbutton bigbutton"/></p>'
				 + 	'</form>';
		
			if ($("#popupform_login").length == 0) {
				$('body').append(html);
			} else {
				$("#popupform_login").show();
			}
		ajaxLoginForm($('#popupform_login'));

		
		return false;
		
	});
	
	$('.contactLink').click(function() {
		var splitted = $(this).attr('class').split(" ");
		$('#overlay').show();
		if(splitted[1] == null){
			var link = 'page/contact';
		} else {
			var link = 'page/contact/'+splitted[1];
		}

		var ajxReq = $.ajax({
			url : link,
			method : 'GET',
		}).done(function(reqResp){
			var html = '<div id="contactPopup" class="popup"></div>';
			if ($("#contactPopup").length == 0) {
				$('body').append(html);
			} else {
				$("#contactPopup").show();
			}
			var popupform = $('#contactPopup');
			popupform.html(reqResp);
			popupform.css('text-align', 'left');

			if(popupform.width() > 760){
				popupform.css('width', 760);
			}
			popupform.css('left', '50%');
			popupform.css('margin-left', (popupform.width() + 48)/-2);
		});	
	});

	$('.rulesLink').click(function() {
		var splitted = $(this).attr('class').split(" ");
		$('#overlay').show();
		if(splitted[1] == null){
			var link = 'page/rules';
		} else {
			var link = 'page/rules/'+splitted[1];
		}

		var ajxReq = $.ajax({
			url : link,
			method : 'GET',
		}).done(function(reqResp){
			var html = '<div id="rulesPopup" class="popup"></div>';
			if ($("#rulesPopup").length == 0) {
				$('body').append(html);
			} else {
				$("#rulesPopup").show();
			}
			var popupform = $('#rulesPopup');
			popupform.html(reqResp);
			popupform.css('text-align', 'left');

			if(popupform.width() > 760){
				popupform.css('width', 760);
			}
			popupform.css('left', '50%');
			popupform.css('margin-left', (popupform.width() + 48)/-2);
		});	
	});

	$('.helpLink').click(function(){
		$('#overlay').show();
		var ajxReq = $.ajax({
			url : 'page/help',
			method : 'GET',
		}).done(function(reqResp){
			var html = '<div id="popupform_help" class="popup"></div>';
			if ($("#popupform_help").length == 0) {
				$('body').append(html);
			} else {
				$("#popupform_help").show();
			}
			var popupform = $('#popupform_help');
			
			popupform.html(reqResp);

			if(popupform.width() > 800){
				popupform.css('width', 800);
			}

		});	
	});


	$('.helpOrgLink').click(function(){
		$('#overlay').show();
		var ajxReq = $.ajax({
			url : 'page/help_organizer',
			method : 'GET',
		}).done(function(reqResp){
			var html = '<div id="popupform_help" class="popup"></div>';
			if ($("#popupform_help").length == 0) {
				$('body').append(html);
			} else {
				$("#popupform_help").show();
			}
			var popupform = $('#popupform_help');
			
			popupform.html(reqResp);

			if(popupform.width() > 800){
				popupform.css('width', 800);
			}

		});
	});

	$('.registerlink').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		$('#overlay').show();
		var states = new Array("Sweden", "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burma", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo, Democratic Republic", "Congo, Republic of the", "Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Greenland", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Mongolia", "Morocco", "Monaco", "Mozambique", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Samoa", "San Marino", " Sao Tome", "Saudi Arabia", "Senegal", "Serbia and Montenegro", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "Spain", "Sri Lanka", "Sudan", "Suriname", "Swaziland", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe");


		var url = $(this).attr('href');
		if ($("#popupform_register").length == 0) {
		$('body').prepend(form_register);
		} else {
			$("#popupform_register").show();
		}

	  	tinyMCE.init({
	        //General options
	        mode : "specific_textareas",
			editor_selector : "presentation",
	        theme : "advanced",
	        skin : "o2k7",
	        skin_variant : "black",
	        height : 168,
	        plugins : "style,table,advimage,advlink,inlinepopups,insertdatetime,preview,paste,fullscreen,noneditable,visualchars,xhtmlxtras",
				theme_advanced_buttons1 : "bold,italic,underline,|,bullist,numlist,|,link,unlink,|,justifyleft,justifycenter,justifyright|,cut,copy,paste,code",
        		theme_advanced_buttons2 : "",
        		theme_advanced_buttons3 : "",
        		theme_advanced_buttons4 : "",

	        // Theme option
	        theme_advanced_toolbar_location : "top",
	        theme_advanced_toolbar_align : "left",
	        theme_advanced_statusbar_location : "bottom",
	        theme_advanced_resizing : false,
	        paste_text_sticky: true,
			paste_text_sticky_default: true,

	        /*content_css : "../css/tiny.css",*/

	        //Drop lists for link/image/media/template dialogs
	        template_external_list_url : "js/template_list.js",
	        external_link_list_url : "js/link_list.js",
	        external_image_list_url : "js/image_list.js",
	        media_external_list_url : "js/media_list.js",
		width : 565,
		});

		hookUpPasswdMeter();
		prepFormChecker();

		/* >>>>>>> MULTISTEP FORM FOR REGISTER FORM <<<<<<<<< */
		//jQuery time
		var current_fs, next_fs, previous_fs; //fieldsets
		var left, opacity, scale; //fieldset properties which we will animate
		var animating; //flag to prevent quick multi-click glitches

		$("#register_first_step").click(function(){

			var thisFieldset = $(this).parent();
			thisFieldset.data('valid', true);
			var errors = new Array();
			var error_items = '';
			
			$("label", thisFieldset).each(function() {
				
				//Exclude hidden fields
				if ($(this).parent().parent().is(":visible")) {
					
					var input = $("#" + $(this).attr("for"));
					var label = $(this).text();
					//Reset all fields to ok
					input.removeClass("input_error");
					input.addClass("input_ok");

					if (label.substring(label.length-1) == '*') {
						
						//Text and password inputs
						if ((input.attr("type") == 'text') && input.val() == '' && input.attr('name') != 'email' && input.attr('name') != 'phone1') {
							//Mark empty
							input.removeClass("input_ok");
							input.addClass("input_error");
							errors.push($(this).attr("for"));

							if (input.attr('name') == 'name')
								error_items += lang.name_err + '<br/>';

							if (input.attr('name') == 'orgnr')
								error_items += lang.orgnr_err + '<br/>';

							if (input.attr('name') == 'company')
								error_items += lang.company_err + '<br/>';

							if (input.attr('name') == 'address')
								error_items += lang.address_err + '<br/>';

							if (input.attr('name') == 'zipcode')
								error_items += lang.zipcode_err + '<br/>';

							if (input.attr('name') == 'city')
								error_items += lang.city_err + '<br/>';
						}
						
						//Email addresses
						if (input.attr("name") == "email") {
							if (input.hasClass('emailExists')) {
								errors.push($(this).attr("for"));
								error_items += lang.email_exists_err + '<br/>';
								input.removeClass("input_ok");
								input.addClass("input_error emailExists");
							}
							if (!isValidEmailAddress(input.val())) {
								input.removeClass("input_ok");
								input.addClass("input_error");
								errors.push($(this).attr("for"));
								error_items += lang.email_err + '<br/>';
							}
						}

						//Textareas
						if (input.is('textarea') && input.val() == '') {
							//Mark empty
							input.removeClass("input_ok");
							input.addClass("input_error");
							errors.push($(this).attr("for"));
							error_items += lang.commodity_err + '<br/>';
						}
						
						//Selects
						if (input.is('select') && (input.val() == 0 || input.val() == '')) {
							//Mark empty
							input.removeClass("input_ok");
							input.addClass("input_error");
							errors.push($(this).attr("for"));
						}

						if (input.hasClass('phone-val') && !/^\+?[\d]{5,20}$/.test(input.val())) {
							input.removeClass("input_ok");
							input.addClass("input_error");
							errors.push($(this).attr("for"));
							error_items += lang.phone_err + '<br/>';
						}
					}
				}
			});

			if (errors.length > 0) {
				$.alert({
				    title: lang.form_err.replace('#', errors.length),
				    content: error_items,
				});
				animating = true;
			} else {
				animating = false;
			}
		

			if(animating) return false;
			animating = true;

			current_fs = $(this).parent();
			next_fs = $(this).parent().next();
			
			//activate next step on progressbar using the index of next_fs
			$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
			
			//show the next fieldset
			next_fs.show(); 
			//hide the current fieldset with style
			current_fs.animate({opacity: 0}, {
				step: function(now, mx) {
					//as the opacity of current_fs reduces to 0 - stored in "now"
					//1. scale current_fs down to 80%
					scale = 1 - (1 - now) * 0.2;
					//2. bring next_fs from the right(50%)
					left = (now * 50)+"%";
					//3. increase opacity of next_fs to 1 as it moves in
					opacity = 1 - now;
					current_fs.css({'transform': 'scale('+scale+')'});
					next_fs.css({'left': left, 'opacity': opacity});
				}, 
				duration: 600, 
				complete: function(){
					current_fs.hide();
					animating = false;
				}, 
				//this comes from the custom easing plugin
				easing: 'easeOutSine'
			});
		});

		$("#register_second_step").click(function() {

			var thisFieldset = $(this).parent();
			thisFieldset.data('valid', true);
			var errors = new Array();
			var error_items = '';
			$("label", thisFieldset).each(function() {
				
				//Exclude hidden fields
				if ($(this).parent().parent().is(":visible")) {
					
					var input = $("#" + $(this).attr("for"));
					var label = $(this).text();
					//Reset all fields to ok
					input.removeClass("input_error");
					input.addClass("input_ok");

					if (label.substring(label.length-1) == '*') {
						
						//Text and password inputs
						if (((input.attr("type") == 'text') || input.attr("type") == 'password') && input.val() == '' && input.attr('name') != 'invoice_email') {
							//Mark empty
							input.removeClass("input_ok");
							input.addClass("input_error");
							errors.push($(this).attr("for"));

							if (input.attr('name') == 'invoice_company')
								error_items += lang.company_err + '<br/>';

							if (input.attr('name') == 'invoice_address')
								error_items += lang.address_err + '<br/>';

							if (input.attr('name') == 'invoice_zipcode')
								error_items += lang.zipcode_err + '<br/>';

							if (input.attr('name') == 'invoice_city')
								error_items += lang.city_err + '<br/>';

							if (input.attr('name') == 'password')
								error_items += lang.passwd_empty_err + '<br/>';

							if (input.attr('name') == 'password_repeat')
								error_items += lang.passwd_repeat_err + '<br/>';
						}

						//Email addresses 2
						if (input.attr("name") == "invoice_email" && (!isValidEmailAddress(input.val()))) {
							input.removeClass("input_ok");
							input.addClass("input_error");
							errors.push($(this).attr("for"));
							error_items += lang.email_err + '<br/>';
						}

						//Selects
						if (input.is("select") && $('#invoice_country option:selected').text() == '') {
							//Mark empty
							input.removeClass("input_ok");
							input.addClass("input_error");
							errors.push($(this).attr("for"));
							error_items += lang.country_err + '<br/>';
						}
					}
				}
			});
			
			if (errors.length > 0) {
				$.alert({
				    title: lang.form_err.replace('#', errors.length),
				    content: error_items,
				});
			} else {

				if(animating) return false;
				animating = true;

				current_fs = $(this).parent();
				next_fs = $(this).parent().next();
					
				//activate next step on progressbar using the index of next_fs
				$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
					
				//show the next fieldset
				next_fs.show();
				//hide the current fieldset with style
				current_fs.animate({opacity: 0}, {
					step: function(now, mx) {
						//as the opacity of current_fs reduces to 0 - stored in "now"
						//1. scale current_fs down to 80%
						scale = 1 - (1 - now) * 0.2;
						//2. bring next_fs from the right(50%)
						left = (now * 50)+"%";
						//3. increase opacity of next_fs to 1 as it moves in
						opacity = 1 - now;
						current_fs.css({'transform': 'scale('+scale+')'});
						next_fs.css({'left': left, 'opacity': opacity});
					}, 
					duration: 600, 
					complete: function(){
						current_fs.hide();
						animating = false;
					}, 
					//this comes from the custom easing plugin
					easing: 'easeOutSine'
				});
			}
		});

		$(".next").click(function(){
			if(animating) return false;
			animating = true;

			current_fs = $(this).parent();
			next_fs = $(this).parent().next();
			
			//activate next step on progressbar using the index of next_fs
			$("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
			
			//show the next fieldset
			next_fs.show();
			//hide the current fieldset with style
			current_fs.animate({opacity: 0}, {
				step: function(now, mx) {
					//as the opacity of current_fs reduces to 0 - stored in "now"
					//1. scale current_fs down to 80%
					scale = 1 - (1 - now) * 0.2;
					//2. bring next_fs from the right(50%)
					left = (now * 50)+"%";
					//3. increase opacity of next_fs to 1 as it moves in
					opacity = 1 - now;
					current_fs.css({'transform': 'scale('+scale+')'});
					next_fs.css({'left': left, 'opacity': opacity});
				}, 
				duration: 600, 
				complete: function(){
					current_fs.hide();
					animating = false;
				}, 
				//this comes from the custom easing plugin
				easing: 'easeOutSine'
			});
		});

		$(".previous").click(function(){

			if(animating) return false;
			animating = true;
			
			current_fs = $(this).parent();
			previous_fs = $(this).parent().prev();
			
			//de-activate current step on progressbar
			$("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");
			
			//show the previous fieldset
			previous_fs.show();
			//hide the current fieldset with style
			current_fs.animate({opacity: 0}, {
				step: function(now, mx) {
					//as the opacity of current_fs reduces to 0 - stored in "now"
					//1. scale previous_fs from 80% to 100%
					scale = 0.8 + (1 - now) * 0.2;
					//2. take current_fs to the right(50%) - from 0%
					left = ((1-now) * 50)+"%";
					//3. increase opacity of previous_fs to 1 as it moves in
					opacity = 1 - now;
					current_fs.css({'left': left});
					previous_fs.css({'transform': 'scale('+scale+')', 'opacity': opacity});
				}, 
				duration: 600, 
				complete: function(){
					current_fs.hide();
					animating = false;
				}, 
				//this comes from the custom easing plugin
				easing: 'easeOutSine'
			});
		});

	/* >>>>>>>>>> MULTISTEP FORM END <<<<<<<<<<<< */

		$("#copy").change(function() {
			if ($(this).is(":checked")) {
				$('#invoice_company').val($('#company').val());
				$('#invoice_address').val($('#address').val());
				$('#invoice_zipcode').val($('#zipcode').val());
				$('#invoice_city').val($('#city').val());
				$('#invoice_country').val($('#country').val());
				$('#invoice_email').val($('#email').val());
			} else {
				$('#invoice_company').val("");
				$('#invoice_address').val("");
				$('#invoice_zipcode').val("");
				$('#invoice_city').val("");
				$('#invoice_country').val("");
				$('#invoice_email').val("");
			}
		});

		return false;
	});

	/********  CLOSE POPUPS LISTENER FUNCTION *******/

	$(document).on("click",".closeDialogue", function(){
		closeDialogue();
	});

	$(document).on("click",".cancelbutton", function(){
		closeForm();
	});

	$(window).on('keyup', function(e) {
		if (e.keyCode == 27) {
			closeDialogue();
			closeForm();
		}
	});
    /*
    var header = $("#new_header");
    var headershow = $("#new_header_show");
    var headerhide = $("#new_header_hide");

    if (!header.is(e.target) // if the target of the click isn't the container...
        && header.has(e.target).length === 0 // ... nor a descendant of the container
      )
	    {
		$( "#new_header" ).hide("slide", { direction: "left" }, 500);
		$("#new_header_hide").hide();
		if (!headershow.is(e.target)) {
			$( "#new_header_show" ).delay("slow").fadeIn();
		}
	}
*/

	$(document).mouseup(function (e) {
	    var container = $(".select-list-menu");
    if (!container.is(e.target) // if the target of the click isn't the container...
        && container.has(e.target).length === 0) // ... nor a descendant of the container
    {
        container.hide();
    }
});
	$(document.body).on('click', '.open-edit-booking', function(e) {
		var link = $(this);
		e.preventDefault();
		$('#overlay').show();
		bookPopup(link.parent().parent().parent(), link.attr('href'), 'edit');

	}).on('click', '.open-list-menu', function(e) {
		e.preventDefault();
		var offsetY = $(this).offset().top;
		var scrollPosition = $(window).scrollTop();
		var top = $(window).height();
		var optionListHeight = $(this).siblings('ul').height();
		var optionListPosition = offsetY + optionListHeight - scrollPosition;

		if (optionListPosition > top) {
			$(this).siblings('ul').css('top', offsetY - scrollPosition - optionListHeight - 10);
		} else {
			$(this).siblings('ul').css('top', offsetY - scrollPosition + 30);
		}
		$(this).siblings('ul').show();
	}).on('click', '.open-edit-reservation', function(e) {
		var link = $(this);
		e.preventDefault();
		$('#overlay').show();
		reservePopup(link.parent().parent().parent(), link.attr('href'), 'edit');

	}).on('click', '.open-view-preliminary', function(e) {
		var link = $(this);
		e.preventDefault();
		$('#overlay').show();
		prelPopup(link.parent().parent(), link.attr('href'), 'review');

	}).on('click', '.open-view-this-preliminary', function(e) {
		var link = $(this);
		e.preventDefault();
		$('#overlay').show();
		prelPopup(link.parent(), link.attr('href'), 'review');

	}).on("click", ".showProfileLink", showUser)
	.on('click', '.open-excel-export', showExportPopup)
	.on('click', '.open-send-cloned', sendVerifyCloned)
	.on('click', '.open-sms-send', showSmsSendPopup)
	.on('click', '.check-all', checkAll)
	.on("click", "#exportToExcel", function (e) {
		e.preventDefault();
		prepareTable.call(this);
	})
	.on('click', '.open-arranger-message', function(e) {
		e.preventDefault();
		$('#overlay').show();
		var link = $(this), 
			arranger_message_popup = $('#arranger_message_popup');

		if (arranger_message_popup.length === 0) {
			arranger_message_popup = $('<div id="arranger_message_popup" class="dialogue popup">'
				+ '<h3>' + lang.messageFromExhibitor + '</h3>'
				+ '<p class="center" id="arranger_message_text"></p><p class="center">'
				+ '<a href="#" class="greenbutton mediumbutton close-ok">' + lang.ok + '</a></p></div>');

			$('body').append(arranger_message_popup);
			$('.close-ok', arranger_message_popup).click(closeDialogue);
		}

		$.ajax({
			url: link.attr('href'),
			method: 'get',
			success: function(response) {
				$('#arranger_message_text').text(response.message);
				arranger_message_popup.show();
			}
		});
	}).on('click', '.zip-invoices', zipInvoices);

	$("#copy").change(function() {
		if ($(this).is(":checked")) {
			$('#invoice_company').val($('#company').val());
			$('#invoice_address').val($('#address').val());
			$('#invoice_zipcode').val($('#zipcode').val());
			$('#invoice_city').val($('#city').val());
      		$('#invoice_country').val($('#country').val());
			$('#invoice_email').val($('#email').val());
		} else {
			$('#invoice_company').val("");
			$('#invoice_address').val("");
			$('#invoice_zipcode').val("");
			$('#invoice_city').val("");
     		$('#invoice_country').val("");
			$('#invoice_email').val("");
		}
	});

	$('.use-scrolltable').each(function() {
		if ($(this).parent().parent().hasClass("tab-div-hidden")) {
			// do nothing or the searchfield loading becomes really mad
		}
		else {
			useScrolltable($(this));
		}
	});

	$('.std_table:not(.scrolltable)').tablesorter();

});

/*********  ZIP-download script  **********/

function zipInvoices(e) {
  e.preventDefault();

  var button = $(e.target);
  var links = '';
  var table_form = $(button.prop('form'));

/*  count = 0;

  $('input[name*=rows]:checked', table_form).each(function(index, input) {
    count += $(input).data('ziplink').length;
  });

*/

  $('input[name*=rows]:checked', table_form).each(function(index, input) {
    links += encodeURIComponent($(input).data('ziplink')) + '|';
  });
  links = links.substr(0, links.length-1); 
//console.log(links);

    $.ajax({
      url: 'administrator/exportFiles',
      method: 'POST',
      data: 'fileLink=' + links,
      success: function(response) {
        window.location = 'administrator/downloadInvoices/' + response;
        console.log(response);
        //console.log(links);
      }
    });
    //console.log($(input).val());
}

var Tabs = (function($) {
	var Tabs = function(element) {
		var tab_nav = $(element);
		var self = this;

		self.current_tab = null;

		function open(tab) {
			if (self.current_tab !== null) {
				close(self.current_tab);
			}

			tab.addClass('current');
			$(tab[0].hash).show();

			self.current_tab = tab;
		}

		function close(tab) {
			tab.removeClass('current');
			$(tab[0].hash).hide();
		}

		/*
		 * Initiate
		 */
		tab_nav.on('click', '.js-select-tab', function(e) {
			e.preventDefault();
			open($(this));
		});

		$('.js-select-tab', tab_nav).each(function(index, tab) {
			if (index === 0) {
				open($(tab));
			} else {
				close($(tab));
			}
		});
	};

	$.fn.tabs = function() {
		return this.each(function(key, value){
			var element = $(this);
			// Return early if this element already has a plugin instance
			if (element.data('tabs')) {
				return element.data('tabs');
			}

			var tabs = new Tabs(this);
			// Store plugin object in this element's data
			element.data('tabs', tabs);
		});
	};
}(jQuery));


