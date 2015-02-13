/* Funktioner för att visa popuper under newReservations! Hämtar data från tabellen och placerar i en popup! */
var open_dialogue = null;
var ask_before_leave = false;

function showPopup(type, activator){

	var row = $(activator).parent().parent();

	if(type == "reserve"){
		var link = row.data("reserveurl");
		reservePopup(row, link, 'confirm');
	}

	if(type == "book"){
		var link = row.data("approveurl");
		bookPopup(row, link, 'confirm');
		
	}
}

function reservePopup($row, link, action) {
	var dialogueId = "reserve_position_dialogue";
	var dialogue = $('#' + dialogueId);

	$('.confirm, .edit', dialogue).hide();
	$('.' + action, dialogue).show();
	$('.closeDialogue', dialogue).click(closeDialogue);
	dialogue.css('display', 'block');
	open_dialogue = dialogue;
	$('#overlay').show();

	var catArr = ($row.data("categories") + "").split("|");
	var optArr = ($row.data("options") + "").split("|");
	var i;

	$('#reserve_category_scrollbox input').prop('checked', false);
	for (i = 0; i < catArr.length; i++) {
		$('#reserve_category_scrollbox input[value=\'' + catArr[i] + '\']').prop('checked', true);
	}

	$('#reserve_option_scrollbox input').prop('checked', false);
	for (i = 0; i < optArr.length; i++) {
		$('#reserve_option_scrollbox input[value=\'' + optArr[i] + '\']').prop('checked', true);
	}

	$('form', dialogue).prop('action', link);
	$('.position-name', dialogue).text($row.data("posname"));
	$('#reserve_id').val($row.data('id'));
	$('#reserve_user').text($row.data("company"));
	$('#reserve_commodity_input').val($row.data("commodity"));
	$('#reserve_message_input').val($row.data("message"));	

	if (action == 'edit') {
		$('#reserve_expires_input').val($row.data("expires").replace(/ GMT[+-]\d*/, ''));
	}

	positionDialogue(dialogueId, -310);
}

function bookPopup($row, link, action) {
	var dialogueId = "book_position_dialogue";

	var dialogue = $('#' + dialogueId);

	$('.confirm, .edit', dialogue).hide();
	$('.' + action, dialogue).show();
	$('.closeDialogue', dialogue).click(closeDialogue);
	dialogue.css('display', 'block');
	open_dialogue = dialogue;
	$('#overlay').show();

	var catArr = ($row.data("categories") + "").split("|");
	var optArr = ($row.data("options") + "").split("|");
	var i;

	$('#book_category_scrollbox input').prop('checked', false);
	for (i = 0; i < catArr.length; i++) {
		$('#book_category_scrollbox input[value=\'' + catArr[i] + '\']').prop('checked', true);
	}

	$('#book_option_scrollbox input').prop('checked', false);
	for (i = 0; i < optArr.length; i++) {
		$('#book_option_scrollbox input[value=\'' + optArr[i] + '\']').prop('checked', true);
	}

	$('form', dialogue).prop('action', link);
	$('.position-name', dialogue).text($row.data("posname"));
	$('#book_id').val($row.data('id'));
	$('#book_user').text($row.data("company"));
	$('#book_commodity_input').val($row.data("commodity"));
	$('#book_message_input').val($row.data("message"));	

	positionDialogue(dialogueId, -310);
}

function closeDialogue(e) {
	if (e) {
		e.preventDefault();
	}

	if (open_dialogue !== null) {
		if (!ask_before_leave || (ask_before_leave && confirm(lang.ask_before_leave))) {
			open_dialogue.hide();
			open_dialogue = null;
			$('#overlay').hide();

			ask_before_leave = false;
		}
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

	//Exceptions to width and height
	switch (id) {
		case "showUserDialogue":
			popupMaxWidth = Math.max(950, viewportWidth * .70);
			popupMaxHeight = Math.max(328, viewportHeight * .90);
			break;
		case "edit_position_dialogue":
		case "book_position_dialogue":
		case "reserve_position_dialogue":
		case "apply_mark_dialogue":
		case 'fair_registration_paste_type_dialogue':
		case "note_dialogue":
			popupMaxWidth = 400;
			break;
		case "preliminaryConfirm":
			popupMaxWidth = Math.max(550, viewportWidth * .30);
			break;
		case "more_info_dialogue":
			popupMaxWidth = 600;
			break;
		case "preliminary_bookings_dialogue":
			popupMaxWidth = Math.max(900, viewportWidth * .70);
			popupMaxHeight = Math.max(328, viewportHeight * .90);
			popupMaxWidth = 800;
			break;
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

			useScrolltable(dialogue.find("#profileBookings"));
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
		var $input = $this.parent().children(".optionTextHidden");
		var text = $input.val();

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

function showExportPopup(e) {
	e.preventDefault();

	if ($('#export_popup').length > 0) {
		$('#export_popup').remove();
	}

	var html = '<div id="export_popup" class="dialogue" style="width: 500px; text-align: left;"><img src="images/icons/close_dialogue.png" alt="" class="closeDialogue close-popup" />'
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

	positionDialogue("export_popup");
}

function showSmsSendPopup(e) {
	e.preventDefault();
	ask_before_leave = true;

	if ($('#sms_send_popup').length > 0) {
		$('#sms_send_popup').remove();
	}

	var sms_price = 0.5;
	var button = $(e.target);
	var table_form = $(button.prop('form'));
	var num_recipients = $('input[name*=rows]:checked', table_form).length;
	var sms_send_popup = $('<form id="sms_send_popup" class="dialogue" style="width: 400px;"><img src="images/icons/close_dialogue.png" alt="" class="closeDialogue close-popup" />'
		+ '<h3>' + lang.sms_enter_message + '</h3>'
		+ '<p><textarea name="sms_text"></textarea></p>'
		+ '<p><strong>' + lang.sms_max_chars + '</strong><strong id="sms_send_chars_count"></strong></p>'
		+ '<p><button type="submit" class="save-btn">' + lang.send_label + '</button></p>'
		+ '<ul class="dialog-tab-list"><li><a href="#sms_send_log" class="js-select-tab">'
		+ lang.sms_log + '</a></li><li><a href="#sms_send_errors" class="js-select-tab">' + lang.errors + ' (<span id="sms_send_errors_count">0</span>)</a></li></ul>'
		+ '<div class="dialog-tab" id="sms_send_log"><p></p></div>'
		+ '<div class="dialog-tab" id="sms_send_errors"><ul></ul></div>'
		+ '<p>' + lang.sms_num_recipients + ': <strong>' + num_recipients + '</strong><br />'
		+ lang.sms_estimated_cost + ': <strong id="sms_send_cost"></strong> kr</p></form>');

	var error_list = $('#sms_send_errors ul', sms_send_popup);

	sms_send_popup.show();
	open_dialogue = sms_send_popup;

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

					} else if (response.errors) {
						for (var i = 0; i < response.errors.length; i++) {
							error_list.append($('<li></li>').text(response.errors[i]));
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
	$('.close-popup', sms_send_popup).click(closeDialogue);

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
		cost_count.text((sms_price * page * num_recipients).toFixed(2));
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

	function saveCommentListener(e) {
		e.preventDefault();
		var target = $(e.target);

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

						if (current_modelview.length > 0) {
							$('[data-key=comment]', current_modelview).text(response.model.comment);
							$('[data-key=type]', current_modelview).html(response.model.type_html);
							closeDialogue();
						}

					} else if (response.deleted) {
						if (current_modelview) {
							current_modelview.remove();
						}

						closeDialogue();

					} else if (current_collectionview.length > 0) {
						$('.empty-placeholder', current_collectionview).remove();
						current_collectionview.append(response);

						if (close_after_create) {
							closeDialogue();
						}
					}
				}
			}
		});
	}

	function initDialog(response, options) {
		if ($('#note_dialogue').length > 0) {
			$('#note_dialogue').remove();
		}

		if (response.error) {
			alert(response.error);
		} else {
			var note_dialogue = $('<div id="note_dialogue" class="dialogue" style="width: 400px;"><img src="images/icons/close_dialogue.png" alt="" class="closeDialogue close-popup" />'
				+ response + '</div>');
			var user_select = $('.js-user-select', note_dialogue);

			$('form', note_dialogue).on('submit', saveCommentListener);

			if (user_select.length > 0) {
				var user_search = $('<input type="text" id="note_user_search" />');
				user_select.siblings('strong').before(user_search);
				user_search.wrap('<label>' + lang.search_exhibitor + ':<br /></label><br />');

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
			$('.close-popup', note_dialogue).click(closeDialogue);

			current_collectionview = $(options.collection_view_selector);
			close_after_create = options.close_dialog_after;
			template = options.template;

			closeDialogue();

			$('#overlay').show();
			note_dialogue.show();
			open_dialogue = note_dialogue;
		}
	}

	function showActionDialog(e) {
		e.preventDefault();
		var target = (e.target.nodeName === 'A' ? e.target : e.target.parentNode);

		current_modelview = getModelView($(target));

		$.ajax({
			url: target.href,
			type: 'GET',
			success: initDialog
		});
	}

	function showDialog(options) {
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
			checkbox.prop('checked', check_all.prop('checked'));
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
				wrapper.style.maxHeight = "399px";

				window.setTimeout(function () {
					wrapper.style.maxHeight = "400px";
				}, 10);
			}, 10);
		});
}

$(document).ready(function() {
	// Ask before leave
	window.onbeforeunload = function() {
		return (ask_before_leave ? 'Är du säker?' : null);
	};

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
			if(popupform.width() > 800){
				popupform.css('width', 800);		
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
			if(popupform.width() > 800){
				popupform.css('width', 800);		
			}
			popupform.css('left', '50%');
			popupform.css('margin-left', (popupform.width() + 48)/-2);
			var d = popupform.width() - 15;
			closeButton.css('left', d);

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

	$('.use-scrolltable').each(function() {
		useScrolltable($(this));
	});

	$('.std_table:not(.scrolltable)').tablesorter();
	
});

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