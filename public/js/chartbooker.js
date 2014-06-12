/* Funktioner för att visa popuper under newReservations! Hämtar data från tabellen och placerar i en popup! */
var open_dialogue = null;

function showPopup(type, activator){
	
	var row = $(activator).parent().parent();

	if(type == "reserve"){
		var link = row.find('.reserve').text();
		reservePopup(row, link, 'confirm');
	}

	if(type == "book"){
		var link = row.find('.approve').text();
		bookPopup(row, link, 'confirm');
		
	}
}

function reservePopup(row, link, action) {
	var dialogueId = "reserve_position_dialogue";
	var dialogue = $('#' + dialogueId);

	$('.confirm, .edit', dialogue).hide();
	$('.' + action, dialogue).show();
	$('.closeDialogue', dialogue).click(closeDialogue);
	dialogue.css('display', 'block');
	$('#overlay').show();
	open_dialogue = dialogue;

	var data = row.children(), 
		catArr = data.eq(7).text().split("|"), 
		optArr = data.eq(8).text().split("|"), 
		i;

	$('#reserve_category_scrollbox input').prop('checked', false);
	for (i = 0; i < catArr.length; i++) {
		$('#reserve_category_scrollbox input[value=' + catArr[i] + ']').prop('checked', true);
	}

	$('#reserve_option_scrollbox input').prop('checked', false);
	for (i = 0; i < optArr.length; i++) {
		$('#reserve_option_scrollbox input[value=\'' + optArr[i] + '\']').prop('checked', true);
	}

	$('form', dialogue).prop('action', link);
	$('.position-name', dialogue).text(data.eq(0).text());
	$('#reserve_id').val(row.data('id'));
	$('#reserve_user').text(data.eq(2).text());
	$('#reserve_commodity_input').val(data.eq(3).text());
	$('#reserve_message_input').val(data.eq(6).prop('title'));

	if (action == 'edit') {
		$('#reserve_expires_input').val(data.eq(9).text().replace(/ GMT[+-]\d*/, ''));
	}

	positionDialogue(dialogueId, -310);
}

function bookPopup(row, link, action) {
	var dialogueId = "book_position_dialogue";

	var dialogue = $('#' + dialogueId);

	$('.confirm, .edit', dialogue).hide();
	$('.' + action, dialogue).show();
	$('.closeDialogue', dialogue).click(closeDialogue);
	dialogue.css('display', 'block');
	open_dialogue = dialogue;
	$('#overlay').show();
	
	var data = row.children(), 
		catArr = data.eq(7).text().split("|"),
		optArr = data.eq(8).text().split("|"), 
		i;

	$('#book_category_scrollbox input').prop('checked', false);
	for (i = 0; i < catArr.length; i++) {
		$('#book_category_scrollbox input[value=\'' + catArr[i] + '\']').prop('checked', true);
	}

	$('#book_option_scrollbox input').prop('checked', false);
	for (i = 0; i < optArr.length; i++) {
		$('#book_option_scrollbox input[value=\'' + optArr[i] + '\']').prop('checked', true);
	}

	$('form', dialogue).prop('action', link);
	$('.position-name', dialogue).text(data.eq(0).text());
	$('#book_id').val(row.data('id'));
	$('#book_user').text(data.eq(2).text());
	$('#book_commodity_input').val(data.eq(3).text());
	$('#book_message_input').val(data.eq(6).prop('title'));	

	positionDialogue(dialogueId, -310);
}

function closeDialogue(e) {
	if (e) {
		e.preventDefault();
	}

	if (open_dialogue !== null) {
		open_dialogue.hide();
		open_dialogue = null;
		$('#overlay').hide();
	}
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
				var html = '<div class="dialogue" style="display:block;"><img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>' + response + '</div>';
				$('body').prepend(html);
				
				$(".closeDialogue").click(function() {
					$('.dialogue').remove();
					$('#overlay').hide();
				});
				
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
	var viewportWidth = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
	var viewportHeight = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
	var popupMaxWidth = Math.max(448, viewportWidth * .47);
	var popupMaxHeight = Math.max(328, viewportHeight * .90);

	if (id === "showUserDialogue") {
		popupMaxWidth = Math.max(950, viewportWidth * .70);
		popupMaxHeight = Math.max(328, viewportHeight * .90);
	}

	dialogue.css({
		'top': window.pageYOffset + (popupMaxHeight / 2) + 'px',
		'width': popupMaxWidth + 'px',
		'max-height': popupMaxHeight + 'px',
		'margin-left': '-' + (popupMaxWidth / 2) + 'px',
		'margin-top': '-' + ((popupMaxHeight - 48) / 2) + 'px'
	});
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
				dialogue = $('<div class="dialogue" id="showUserDialogue"></div>');
				$('body').append(dialogue);
			}

			dialogue.html('<img src="images/icons/close_dialogue.png" class="closeDialogue" />' + response);
			dialogue.show();

			open_dialogue = dialogue;

			$(".closeDialogue").on("click", function () {
				dialogue.hide();
				$('#overlay').hide();
			});

			positionDialogue('showUserDialogue');
		}
	});
}

function positionExcelCheckboxes($checkboxes, $table) {
	var $tableHeaders = $table.find(".excelHeader");

	$checkboxes.each(function (i) {
		var $checkbox = $(this),
			$th = $tableHeaders.eq(i);

		$th.append($checkbox);
			//thOffset = $th.position(),
			//center = getHorizontalCenter($th, $checkbox);

		/*$checkbox.css({
			position: "absolute",
			left: thOffset.left + center + "px",
			top: thOffset.top + $th.parent().height() - 40 + "px"
		});*/
	});

	/*$table.parent().on("scroll", function () {
		if (this.scrollTop > 0) {
			$checkboxContainer.hide();
		} else {
			$checkboxContainer.show();
		}
	});*/
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
	}

	rowArray = [];
	colArray = [];
}

function getHorizontalCenter($parent, $child) {
	return ($parent.width() / 2) - ($child.width() / 2);
}

var bookingOptions = {
	createNewOption: function (e) {
		if (e) {
			e.preventDefault();
		}

		var $input = $("#new_option_input");
		var value = $input.val();
		var fairId = $input.data("fair");

		if (value !== "") {
			//Can't insert into database if fair does not already exsist
			if (fairId === "new") {
				bookingOptions.appendToList(value);
			} else {
				$input.val("");
				$.ajax({
					url: "ajax/fair.php",
					type: "POST",
					data: {
						"newOption": fairId,
						"value": value
					},
					dataType: "json",
					success: function (response) {
						bookingOptions.appendToList(value, response);
					}
				});
			}
		}
	},

	appendToList: function (value, response) {
		if (typeof response ===  "undefined") {
			response = {
				id: "new"
			};
		}
		var $optionList = $("#optionList");
		var html = "<li><span class=\"optionText\">"
			+ value + 
			"</span><input type=\"hidden\" value=\""
			+ value +
			"\" name=\"options[]\" class=\"optionTextHidden\" /><img src=\"images/icons/pencil.png\" class=\"icon editExtraOption\" data-id=\""
			+ response.id + 
			"\" title=\"" + lang.edit + "\" /><img src=\"images/icons/delete.png\" class=\"icon deleteExtraOption\" data-id=\""
			+ response.id +
			"\" title=\"" + lang.delete + "\" />";

		$optionList.html($optionList.html() + html);
	},

	deleteExtraOption: function (e) {
		e.preventDefault();

		var li = this.parentNode;
		var id = $(this).data("id");

		if (id === "new") {
			li.parentNode.removeChild(li);
		} else {
			$.ajax({
				url: "ajax/fair.php",
				type: "POST",
				data: {
					deleteOption: id
				},
				success: function (response) {
					li.parentNode.removeChild(li);
				}
			});
		}
	},

	editExtraOption: function (e) {
		e.preventDefault();

		var $this = $(this);
		var $span = $this.parent().children(".optionText");
		var text = $span.text();

		$span.attr("title", "");

		$span.html("<input type=\"text\" value=\"" + text + "\" class=\"optionTextInput\" data-old-value=\"" + text + "\" />");

		this.src = "images/icons/save_32x32.png";

		$this.removeClass("editExtraOption");
		$this.addClass("saveExtraOption");
	},

	saveExtraOption: function (e) {
		if (e) {
			e.preventDefault();
		}

		var $this = $(this);
		var id = $this.data("id");
		var $parent = $this.parent();
		var $span = $parent.children(".optionText");
		var $hiddenInput = $parent.children(".optionTextHidden");
		var value = $span.children(".optionTextInput").val();

		if (value === "") {
			value = $span.children(".optionTextInput").data("old-value");
		} else {
			$span.attr("title", value);

			if (id !== "new") {
				$.ajax({
					url: "ajax/fair.php",
					type: "POST",
					data: {
						saveOption: $this.data("id"),
						value: value
					},
					success: function (response) {
					}
				});
			}
		}

		$span.html(value);
		$hiddenInput.val(value);

		this.src = "images/icons/pencil.png";

		$this.removeClass("saveExtraOption");
		$this.addClass("editExtraOption");
	}
};

/*function findScrolltables() {
	$('.use-scrolltable').each(function() {
		var table = $(this);

		table
			.removeClass('use-scrolltable')
			.wrap('<div class="scrolltable-wrap"><div class="scrolltable"></div></div>');

		$('thead th', table).each(function() {
			$(this).wrapInner('<span class="th"></span>');
		});
	});
}*/

function showExportPopup(e) {
	e.preventDefault();
	window.scrollTo(0, 0);

	if ($('#export_popup').length > 0) {
		$('#export_popup').remove();
	}

	var html = '<div id="export_popup" class="dialogue" style="width: 500px;"><img src="images/icons/close_dialogue.png" alt="" class="closeDialogue close-popup" />'
		+ '<h3>' + lang.export_headline + '</h3>', 
		export_popup, 
		button = $(e.target), 
		field_groups = export_fields[button.data('for')], 
		i, j;

	for (i in field_groups) {
		html += '<div class="export-group"><strong>' + i;
		html += '<label><input type="checkbox" class="check-all" data-group="excexpgrp-' + i + '" /> ' + lang.select_all + '</label>';

		for (j in field_groups[i]) {
			html += '<label><input type="checkbox" name="field[' + j + ']" class="excexpgrp-' + i + '" /> ' + field_groups[i][j] + '</label>';
		}

		html += '</strong></div>';
	}

	html += '<p class="right"><a href="#" class="link-button close-popup">' + lang.cancel + '</a> <input type="submit" value="' + lang.export_excel + '" /></p></div>';

	export_popup = $(html);
	export_popup.show();
	open_dialogue = export_popup;

	$(button).before(export_popup);
	$('.close-popup', export_popup).click(closeDialogue);
}

function checkAll(e) {
	var check_all = $(e.target), 
		checkbox;

	$('input[type=checkbox]').each(function() {
		checkbox = $(this);

		if (checkbox.hasClass(check_all.data('group'))) {
			checkbox.prop('checked', check_all.prop('checked'));
		}
	});
}

$(document).ready(function() {
	$('.datepicker.date').datepicker();
	$('.datepicker.date').datepicker('option', 'dateFormat', 'dd-mm-yy');
	$('.datepicker.datetime').datetimepicker({timeFormat: 'HH:mm'});
	$('.datepicker.datetime').datetimepicker('option', 'dateFormat', 'dd-mm-yy');
	$('.datepicker.date').each(function() {
		$(this).datepicker('setDate', $(this).attr('value'));
	});
	$('.datepicker.datetime').each(function() {
		var $this = $(this),
			value = $this.attr("value");

		if (typeof value !== "undefined") {
			$this.datetimepicker('setDate', $.datepicker.parseDateTime("dd-mm-yy", "HH:mm", value));
		}
	});

	$('#languages a.selected').attr('href', 'javascript:void(0)').append('&nbsp;&nbsp;<img src="images/arrow_down.png" alt=""/>').prependTo('#languages');	
	$('.loginlink').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		$('#overlay').show();

		var url = $(this).attr('href');
		var html = '<form action="' + url + '" method="post" id="popupform">'
				 + 		'<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0 0 0 268px;"/>'
				 +		'<div><p class="error"></p>'
				 + 		'<label for="user">' + lang.login_username + '</label>'
				 + 		'<input type="text" name="user" id="user"/>'
				 + 		'<label for="pass">' + lang.login_password + '</label>'
				 + 		'<input type="password" name="pass" id="pass"/>'
				 +		'<p style="width:101% !important;"><a href="user/resetPassword' + (typeof fair_url === 'string' ? '/backref/' + fair_url : '') + '">' + lang.forgot_pass + '</a></p>'
				 + 		'<p><input type="submit" name="login" value="Sign in"/></p></div>'
				 + 	'</form>';
		
		$('body').prepend(html);
		ajaxLoginForm($('#popupform'));
		$(".closeDialogue").click(function() {
			$('#popupform').remove();
			$('#overlay').hide();
		});
		
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
			var html = '<div id="popupformTwo" style="max-height:80%; overflow-y:auto; min-width:400px; width:auto; padding:20px; top:50px;"></div>';

			$('body').append(html);
			var popupform = $('#popupformTwo');
			popupform.html('<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0;"/>' + reqResp);
			var closeButton = $('.closeDialogue');
			popupform.css('text-align', 'left');
			$('#popupformTwo > table > tbody > tr > td > p').css('text-align', 'left');
			
			$('.closeDialogue').click(function(){
				$(this).off('click');
				$(this).remove();
				$('#popupformTwo').remove();
				$('#overlay').hide();
			});

			if(popupform.width() > 760){
				popupform.css('width', 760);		
			}

			popupform.css('left', '50%');
			popupform.css('margin-left', (popupform.width() + 48)/-2);
			var d = popupform.width() - 15;
			closeButton.css('left', d);
		});	
	});

	$('.helpLink').click(function(){
		$('#overlay').show();
		var ajxReq = $.ajax({
			url : 'page/help',
			method : 'GET',
		}).done(function(reqResp){
			var html = '<div id="popupformTwo" style="width:500px; max-height:80%; overflow-y:auto; padding:20px; margin:0 0 0 -250px; top:50px;"></div>';
			$('body').append(html);
			var popupform = $('#popupformTwo');	
			
			popupform.html('<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0;"/>' + reqResp);
			var closeButton = $('.closeDialogue');
			$('#popupform > p').css('text-align', 'left');
			$('.closeDialogue').click(function(){
				$(this).off('click');
				$(this).remove();
				$('#popupformTwo').remove();
				$('#overlay').hide();
			});
			if(popupform.width() > 760){
				popupform.css('width', 760);		
			}
			popupform.css('left', '50%');
			popupform.css('margin-left', (popupform.width() + 48)/-2);
			var d = popupform.width() - 15;
			closeButton.css('left', d);

		});	
	});

	$('.helpOrgLink').click(function(){
		$('#overlay').show();
		var ajxReq = $.ajax({
			url : 'page/help_organizer',
			method : 'GET',
		}).done(function(reqResp){
			var html = '<div id="popupform" style="width:500px; max-height:80%; overflow-y:auto; padding:20px; margin:0 0 0 -250px; top:50px;"></div>';
			$('body').append(html);
			$('#popupform').html('<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" style="margin:0;"/>' + reqResp);
			$('#popupform > p').css('text-align', 'left');
			$('.closeDialogue').click(function(){
				$(this).off('click');
				$('#popupform').remove();
				$('#overlay').hide();
			});
		});	
	});


	$('.registerlink').click(function(e) {
		e.preventDefault();
		e.stopPropagation();
		$('#overlay').show();
		var states = new Array("Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burma", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo, Democratic Republic", "Congo, Republic of the", "Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Greenland", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Mongolia", "Morocco", "Monaco", "Mozambique", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Samoa", "San Marino", " Sao Tome", "Saudi Arabia", "Senegal", "Serbia and Montenegro", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "Spain", "Sri Lanka", "Sudan", "Suriname", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe");


		var url = $(this).attr('href');
		
		$('body').prepend(form_register);

	  	tinyMCE.init({
	        //General options
	        mode : "specific_textareas",
		editor_selector : "presentation",
	        theme : "advanced",
	        plugins : "style,table,advimage,advlink,inlinepopups,insertdatetime,preview,paste,fullscreen,noneditable,visualchars,xhtmlxtras",

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
		
		$('#password').keyup(function(){
			var password = $(this).val();

			var numbers = /\d{2,}/.test(password);
			var capitalLetter = /[A-ZÅÄÖ]{1,}/.test(password);

			if(numbers){
				if(capitalLetter){
					
					$(this).css('border', 'solid 1px #00FF00');
				} else {
					
					$(this).css('border', 'solid 1px #FF0000');
				}
			} else {
				$(this).css('border', 'solid 1px #FF0000');
			}
		});

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
		
		$(".closeDialogue").click(function() {
			$("#overlay").hide();
			$("#popupform").remove();
			$("#popupform_register").remove();
			$('#popupformTwo').remove();
			$("#newMarkerIcon").remove();
		});
		
			
		return false;
		
	});

	$(window).on('keyup', function(e) {
		if (e.keyCode == 27 && open_dialogue !== null) {
			closeDialogue();
			$('#overlay').hide();
		}
	});

	$(document.body).on('click', '.open-edit-booking', function(e) {
		var link = $(this);
		e.preventDefault();
		$('#overlay').show();
		bookPopup(link.parent().parent(), link.attr('href'), 'edit');

	}).on('click', '.open-edit-reservation', function(e) {
		var link = $(this);
		e.preventDefault();
		$('#overlay').show();
		reservePopup(link.parent().parent(), link.attr('href'), 'edit');
	}).on("click", ".showProfileLink", showUser)

	.on("click", "#new_option_button", bookingOptions.createNewOption)
	.on("click", ".deleteExtraOption", bookingOptions.deleteExtraOption)
	.on("click", ".editExtraOption", bookingOptions.editExtraOption)
	.on("click", ".saveExtraOption", bookingOptions.saveExtraOption)
	.on('click', '.open-excel-export', showExportPopup)
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
			arranger_message_popup = $('<div id="arranger_message_popup" class="dialogue"><img src="images/icons/close_dialogue.png" alt="" class="closeDialogue close-popup" />'
				+ '<h3>' + lang.messageToOrganizer + '</h3>'
				+ '<p id="arranger_message_text"></p><p class="center">'
				+ '<a href="#" class="link-button close-popup">' + lang.ok + '</a></p></div>');

			$('body').append(arranger_message_popup);
			$('.close-popup', arranger_message_popup).click(closeDialogue);
		}

		$.ajax({
			url: link.attr('href'),
			method: 'get',
			success: function(response) {
				$('#arranger_message_text').text(response.message);
				arranger_message_popup.show();
				open_dialogue = arranger_message_popup;
			}
		});
	});

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
	
});
