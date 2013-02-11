var copiedExhibitor = null;
var canvasOriginalWidth = null;
var canvasOriginalHeight = null;
var canvasOriginalOffset = null;
var fullscreen = false;
var userIsEditing = 0;
var categoryFilter = 0;
var markedAsBooked = new Array;

//Some settings
var config = {
	maxZoom: 2, //maximum size of map, X * original
	zoomStep: 0.1, //how many times to increase/decrease map size on zoom
	panMovement: 200, //pixels, distance to pan
	panSpeed: 500, //animation speed for panning map
	iconOffset: 6, //pixels to adjust icon position (half the width/height of the icon)
	markerUpdateTime: 30 //marker update interval in seconds
};

//Prepare maptool object
var maptool = {};
maptool.map = {};
var updateTimer = null;
var update = true;

// If you come from My bookings with a id you want to hover over that position
function preHover(id){
	//if( ! isNaN(id) ){
		setTimeout(function() {
			$('#info-' + id).show();
		}, 1000);
	//}
}

//Check if a set of coordinates (from a click for example) is on the map or not
maptool.isOnMap = function(x, y) {
	if (x < maptool.map.canvasOffset.left ||
		x > maptool.map.canvasOffset.left + maptool.map.canvasWidth) {
		return false;
	}
	if (y < maptool.map.canvasOffset.top ||
		y > maptool.map.canvasOffset.top + maptool.map.canvasHeight) {
		return false;
	}

	return true;

}

//Open dialogue
maptool.openDialogue = function(id) {
	$("#overlay").show(0, function() {
		$(this).css({
			height: $(document).height() + 'px'
		});
		$("#" + id).show();
	});
}

//Close any open dialogues
maptool.closeDialogues = function() {
	if (userIsEditing > 0) {
		maptool.markPositionAsNotBeingEdited();
	}
	$(".dialogue").hide(0, function() {
		$("#overlay, #popupform").hide();
		$("#newMarkerIcon").remove();
	});
}

//Populate list of exhibitors
maptool.populateList = function() {
	
	$("#right_sidebar ul").html('');
	
	for (var i=0; i<maptool.map.positions.length; i++) {
		if (maptool.map.positions[i].exhibitor !== null) {
			
			var hide = false;
			
			if (categoryFilter > 0) {
				if (!maptool.map.positions[i].exhibitor) {
					hide = true;
				} else {
					var catMatched = false;
					for (var j=0; j<maptool.map.positions[i].exhibitor.categories.length; j++) {
						if (maptool.map.positions[i].exhibitor.categories[j].category_id == categoryFilter) {
							catMatched = true;
						}
					}
					if (!catMatched)
						hide = true;
				}
			}
			
			if ($('#search_filter').val() != '') {
				
				var str = $('#search_filter').val().toLowerCase();
				var matched = false;
				
				if (maptool.map.positions[i].exhibitor.company && maptool.map.positions[i].exhibitor.company.toLowerCase().indexOf(str) > -1) {
					matched = true;
				}
				if (maptool.map.positions[i].exhibitor.spot_commodity && maptool.map.positions[i].exhibitor.spot_commodity.toLowerCase().indexOf(str) > -1) {
					matched = true;
				}
				if (maptool.map.positions[i].name && maptool.map.positions[i].name.toLowerCase().indexOf(str) > -1) {
					matched = true;
				}
				if (!matched) {
					hide = true;
				}
			}
			
			if (!hide) {
				var item = $('<li id="map-li-' + maptool.map.positions[i].id + '">' + maptool.map.positions[i].exhibitor.company + '<p>' + maptool.map.positions[i].exhibitor.spot_commodity + '</p></li>');
				item.click(function() {
					$('#right_sidebar ul li').removeClass('selected');
					$(this).addClass('selected');
					var index = $(this).attr("id").replace("map-li-", "");
					//maptool.positionInfo(maptool.map.positions[index]);
					maptool.focusOn(index);
				});
				$("#right_sidebar ul").append(item);
			}
		}
	}
	
}

//Place pre-fetched markers on map
maptool.placeMarkers = function() {
	//Remove all markers before placing new ones
	maptool.clearMarkers();
	var freeSpots = 0;

	for (var i=0; i<maptool.map.positions.length; i++) {
		
		if (maptool.map.positions[i].applied) {
			maptool.map.positions[i].statusText = 'applied';
		}
		
		//Prepare HTML
		var markerId = 'pos-' + maptool.map.positions[i].id;
		var marker = $('<img src="images/icons/marker_' + maptool.map.positions[i].statusText + '.png" alt="" class="marker" id="' + markerId + '"/>');
		var tooltip = $('<div class="marker_tooltip" id="info-' + maptool.map.positions[i].id + '"/>');

		//Tooltip content
		var tt = '<h3>' + lang.space + ' ' + maptool.map.positions[i].name + ' </h3><p><strong>' + lang.status + ': </strong>' + lang.StatusText(maptool.map.positions[i].statusText) + '<br/><strong>' + lang.area + ' (m<sup>2</sup>):</strong> ' + maptool.map.positions[i].area + '</p>';
		tooltip.html(tt);
		
		if (maptool.map.positions[i].status > 0 && maptool.map.positions[i].exhibitor) { 
			tooltip.append('<p><strong>' + lang.StatusText(maptool.map.positions[i].statusText).charAt(0).toUpperCase() + lang.StatusText(maptool.map.positions[i].statusText).substr(1) + ' ' + lang.by + ':</strong> ' + maptool.map.positions[i].exhibitor.company + '</p>');
			if (maptool.map.positions[i].status == 1) {
				tooltip.append('<p><strong>' + lang.reservedUntil + ':</strong> ' + maptool.map.positions[i].expires + '</p>');
			}
			tooltip.append('<p><strong>' + lang.commodity + ':</strong> ' + maptool.map.positions[i].exhibitor.commodity + '</p>');
		} else {
			tooltip.append('<p class="info">' + maptool.map.positions[i].information.replace(/ /g, '&nbsp;').replace(/\n/g, '<br/>') + '</p>');
			if (maptool.map.userlevel > 0) {
				tooltip.append('<p><strong>' + lang.clickToReserveStandSpace + '</strong></p>');
			}
			freeSpots++;
		}
		
		//Calculate position on map
		var xMargin = ((maptool.map.positions[i].x / 100) * $("#map #map_img").width()) - config.iconOffset;
		var yMargin = ((maptool.map.positions[i].y / 100) * $("#map #map_img").height())  - config.iconOffset;
		
		//Set marker and tooltip margin
		marker.css({
			left: xMargin + 'px',
			top: yMargin + 'px'
		});
		
		/*marker.css({
			left: maptool.map.positions[i].x + '%',
			top: maptool.map.positions[i].y + '%'
		});*/
		
		var d = new Date();
		if (maptool.map.positions[i].being_edited > 0 && maptool.map.positions[i].being_edited != maptool.map.user_id && ((Math.round(d.getTime() / 1000) - maptool.map.positions[i].edit_started) < 60*20)) {
			marker.attr('src', 'images/icons/marker_busy.png').addClass('busy');
		}

		//Inject into DOM
		//markerHTML += marker.outerHTML;
		//tooltipHTML += tooltip.outerHTML;
		$("#mapHolder #map").prepend(marker);
		$("#mapHolder").prepend(tooltip);
		
		//Hide markers that are filtered out
		if (categoryFilter > 0) {
			if (!maptool.map.positions[i].exhibitor/* || maptool.map.positions[i].exhibitor.category != categoryFilter*/) {
				marker.css('display', 'none');
			} else {
				var cats = maptool.map.positions[i].exhibitor.categories
				marker.css('display', 'none');
				for (var j=0; j<cats.length; j++) {
					if (cats[j].category_id == categoryFilter)
						marker.css('display', 'inline');
				}
			}
		}
		
	}
	
	//Display tooltip on hover
	$(".marker").hover(function(e) {
		var tooltip = $("#info-" + $(this).attr("id").replace("pos-", ""));
		if (!tooltip.is(":visible")) {
			tooltip.css({
				left: $(this).offset().left,
				top: $(this).offset().top
			});
			$(".marker_tooltip").hide();
			tooltip.show();
		}
	}, function() {
		if ($('.contextmenu').length == 0) {
			$(".marker_tooltip").hide();
		} else {
			//maptool.pauseUpdate();
		}
	});
	
	//display dialogue on marker click
	$(".marker").click(function() {
		maptool.showContextMenu($(this).attr("id").replace('pos-', ''));
	});
	
	maptool.placeFocusArrow();
	
	if ($('#spots_free').text() == "") {
		$('#spots_free').text(freeSpots);
	}
	
}

//Remove all markers
maptool.clearMarkers = function() {
	$(".marker").remove();
	$(".marker_tooltip").remove();
}

//Display tooltip for marker
maptool.tooltip = function(index) {
	$("#info-" + index).show();
}

//Create context menu for markers
maptool.showContextMenu = function(position) {
	
	if ($('#pos-' + position).hasClass('busy'))
		return;

	maptool.tooltip(position);

	maptool.hideContextMenu();

	var objIndex = null;
	for (var i=0; i<maptool.map.positions.length; i++) {
		if (maptool.map.positions[i].id == position) {
			objIndex = i;
			break;
		}
	}

	var contextMenu = $('<ul id="cm-' + position + '" class="contextmenu"></ul>');

	if (maptool.map.positions[objIndex].status == 0 && maptool.map.userlevel > 1 && hasRights && maptool.ownsMap()) {
		contextMenu.append('<li id="cm_book">' + lang.bookStandSpace + '</li><li id="cm_reserve">' + lang.reserveStandSpace + '</li>');
		if (copiedExhibitor) {
			contextMenu.append('<li id="cm_paste">' + lang.pasteExhibitor + '</li>');
		}
	} else if (maptool.map.positions[objIndex].status == 0 && maptool.map.userlevel == 1 && !maptool.map.positions[objIndex].applied && hasRights && maptool.ownsMap()) {
		contextMenu.append('<li id="cm_apply">' + lang.preliminaryBookStandSpace + '</li>');
	} else if (maptool.map.positions[objIndex].status == 0 && maptool.map.userlevel == 1 && maptool.map.positions[objIndex].applied && hasRights && maptool.ownsMap()) {
		contextMenu.append('<li id="cm_cancel">' + lang.cancelPreliminaryBooking + '</li>');
	}
	
	if (maptool.map.userlevel > 1 && hasRights && maptool.ownsMap()) {
		contextMenu.append('<li id="cm_edit">' + lang.editStandSpace + '</li><li id="cm_move">' + lang.moveStandSpace + '</li><li id="cm_delete">' + lang.deleteStandSpace + '</li>');
	}
	
	contextMenu.append('<li id="cm_more">' + lang.moreInfo + '</li>');

	if (maptool.map.positions[objIndex].status > 0 && maptool.map.userlevel > 1 && hasRights && maptool.ownsMap()) {
		contextMenu.append('<li id="cm_edit_booking">' + lang.editBooking + '</li>');
		contextMenu.append('<li id="cm_cancel_booking">' + lang.cancelBooking + '</li>');
		if (maptool.map.positions[objIndex].status == 1) {
			contextMenu.append('<li id="cm_book">' + lang.bookStandSpace + '</li>');
		} else if (maptool.map.positions[objIndex].status == 2) {
			contextMenu.append('<li id="cm_reserve">' + lang.reserveStandSpace + '</li>');
		}
	}

	if ($("li", contextMenu).length > 0) {
		$("#mapHolder").prepend(contextMenu);

		contextMenu.css({
			left: $("#pos-" + position).offset().left + config.iconOffset,
			top: $("#pos-" + position).offset().top + config.iconOffset,
		}).show();

		//Clear click events
		//$(".contextmenu li").off("click");
		
		//click handlers for context menu
		$(".contextmenu li").click(function(e) {
			var positionId = $(this).parent().attr("id").replace("cm-", "");
			if (e.target.id == 'cm_delete') {
				maptool.deletePosition(positionId);
			} else if (e.target.id == 'cm_book') {
				maptool.markPositionAsBeingEdited(maptool.map.positions[objIndex]);
				maptool.bookPosition(maptool.map.positions[objIndex]);
			} else if (e.target.id == 'cm_reserve') {
				maptool.markPositionAsBeingEdited(maptool.map.positions[objIndex]);
				maptool.reservePosition(maptool.map.positions[objIndex]);
			} else if (e.target.id == 'cm_edit') {
				maptool.markPositionAsBeingEdited(maptool.map.positions[objIndex]);
				maptool.editPosition(maptool.map.positions[objIndex]);
			} else if (e.target.id == 'cm_move') {
				maptool.markPositionAsBeingEdited(maptool.map.positions[objIndex]);
				maptool.movePosition(e, maptool.map.positions[objIndex]);
			} else if (e.target.id == 'cm_more') {
				maptool.positionInfo(maptool.map.positions[objIndex]);
			} else if (e.target.id == 'cm_apply') {
				maptool.markForApplication(maptool.map.positions[objIndex]);
			} else if (e.target.id == 'cm_cancel') {
				maptool.cancelApplication(maptool.map.positions[objIndex]);
			} else if (e.target.id == 'cm_paste') {
				maptool.pasteExhibitor(maptool.map.positions[objIndex]);
			} else if (e.target.id == 'cm_edit_booking') {
				maptool.markPositionAsBeingEdited(maptool.map.positions[objIndex]);
				maptool.editBooking(maptool.map.positions[objIndex]);
			} else if (e.target.id == 'cm_cancel_booking') {
				maptool.cancelBooking(maptool.map.positions[objIndex]);
			}
		});
	}

}

maptool.markPositionAsBeingEdited = function(obj) {
	$.ajax({
		url: 'ajax/maptool.php',
		type: 'POST',
		data: 'markPositionAsBeingEdited=' + obj.id,
		success: function(response) {
			userIsEditing = obj.id;
		}
	});
}

maptool.markPositionAsNotBeingEdited = function() {
	$.ajax({
		url: 'ajax/maptool.php',
		type: 'POST',
		data: 'markPositionAsNotBeingEdited=' + userIsEditing,
		success: function(response) {
			userIsEditing = 0;
		}
	});
}

//End context menu
maptool.hideContextMenu = function() {
	$(".contextmenu").hide();
	$(".contextmenu").remove();
}

maptool.pasteExhibitor = function(positionObject) {

	$.ajax({
		url: 'ajax/maptool.php',
		type: 'POST',
		data: 'pasteExhibitor=' + positionObject.id,
		success: function(res) {
			copiedExhibitor = null;
			maptool.reload();
		}
	});

}

//Create new position
maptool.addPosition = function(clickEvent) {
	
	$("#position_name_input, #position_area_input, #position_info_input").val("");

	if (maptool.map.userlevel < 2)
		return;

	maptool.pauseUpdate();

	$("#post_position").off("click");

	$("body").prepend('<img src="images/icons/marker_open.png" alt="" id="newMarkerIcon" class="marker"/>');
	$("#newMarkerIcon").css({
		top: clickEvent.clientY - config.iconOffset,
		left: clickEvent.clientX - config.iconOffset
	});
	if (fullscreen) {
		$("#newMarkerIcon").css('z-index', '10000');
	}
	$(document).on('mousemove', 'body', maptool.traceMouse);
	$("#newMarkerIcon").click(function(e) {
		var x = e.clientX - maptool.map.canvasOffset.left;
		var y = e.clientY - maptool.map.canvasOffset.top;
		if (maptool.isOnMap(e.clientX, e.clientY)) {
			$(document).off('mousemove', 'body', maptool.traceMouse);
			$("#position_id_input").val("new");
			maptool.openDialogue("edit_position_dialogue");
			$("#post_position").click(function() {
				if ($("#position_name_input").val() != '') {
					maptool.savePosition();
					maptool.resumeUpdate();
					$('label[for="position_name_input"]').css("color", "#000000");
				} else {
					$('label[for="position_name_input"]').css("color", "red");
				}
			});
		}
	});

}

//Move position
maptool.movePosition = function(clickEvent, positionObject) {
	
	maptool.pauseUpdate();
	$(".marker_tooltip").hide();

	var marker = $("#pos-" + positionObject.id);
	var canAjax = true;
	marker.off("click");
	$(".marker").off("hover");
	marker.prependTo('body');
	
	marker.css({
		marginTop: '-' + config.iconOffset + 'px',
		marginLeft: '-' + config.iconOffset + 'px'
	});

	$(document).on('mousemove', 'body', function(e) {
		
		marker.css({
			top: e.clientY,
			left: e.clientX
		});
		marker.click(function(e) {
			marker.off("click");

			if (maptool.isOnMap(e.clientX, e.clientY)) {
				$(document).off('mousemove', 'body');

				var xOffset = parseFloat(marker.offset().left + config.iconOffset);
				var yOffset = parseFloat(marker.offset().top + config.iconOffset);

				var mapWidth = $("#map #map_img").width();
				var mapHeight = $("#map #map_img").height();

				xOffset = xOffset - maptool.map.canvasOffset.left + $("#mapHolder").scrollLeft();
				var xPercent = (xOffset / mapWidth) * 100;

				yOffset = yOffset - maptool.map.canvasOffset.top + $("#mapHolder").scrollTop();
				var yPercent = (yOffset / mapHeight) * 100;
				if (canAjax) {
					canAjax = false;
					$.ajax({
						url: 'ajax/maptool.php',
						type: 'POST',
						data: 'movePosition=' + positionObject.id + '&x=' + xPercent + '&y=' + yPercent,
						success: function(res) {
							maptool.markPositionAsNotBeingEdited();
							maptool.resumeUpdate();
							maptool.reload();
						}
					});
				}
			}
		});
	});

}

//Edit position
maptool.editPosition = function(positionObject) {

	//$("#edit_position_dialogue .closeDialogue").show();
	$("#post_position").off("click");

	$("#position_id_input").val(positionObject.id);
	$("#position_name_input").val(positionObject.name);
	$("#position_area_input").val(positionObject.area);
	$("#position_info_input").val(positionObject.information);
	maptool.openDialogue("edit_position_dialogue");
	//$("#post_position").click(function() {
	$("#post_position").on("click", function() {
		if ($("#position_name_input").val() != '') {
			maptool.savePosition();
			$('label[for="position_name_input"]').css("color", "#000000");
		} else {
			$('label[for="position_name_input"]').css("color", "red");
		}
	});

}

//Trace mouse movements with marker
maptool.traceMouse = function(e) {
	var scrollOffset = $('html').offset().top;
	var scrollOffsetLeft = $('html').offset().left;
	$("#newMarkerIcon").css({
		top: e.clientY - config.iconOffset - scrollOffset,
		left: e.clientX - config.iconOffset + scrollOffsetLeft
	});
}

//Book open position
maptool.bookPosition = function(positionObject) {
	
	$('#book_category_input').css('border-color', '#000000');
	
	if (maptool.map.userlevel < 2) {
		$('#book_user_input, label[for="book_user_input"]').hide();
	}
	
	$("#book_post").off("click");
	
	if (positionObject.status == 1) {
		$("#book_commodity_input").val(positionObject.exhibitor.commodity);
		$("#book_message_input").val(positionObject.exhibitor.arranger_message);
		$('#book_user_input option[value="' + positionObject.exhibitor.user + '"]').attr("selected", "selected");
	} else {
		$("#book_commodity_input, #book_message_input").val("");
	}
	
	maptool.openDialogue('book_position_dialogue');
	
	$('.ssinfo').html('<strong>' + lang.space + ' ' + positionObject.name + '<br/>' + lang.area + ':</strong> ' + positionObject.area + '<br/><strong>' + lang.info + ': </strong>' + positionObject.information);

	$("#book_post").click(function() {
		
		var cats = $('#book_category_input').val();
		if (cats === null) {
			$('#book_category_input').css('border-color', 'red');
			return;
		}
		
		var catStr = '';
		for (var j=0; j<cats.length; j++) {
			catStr += '&categories[]=' + cats[j];
		}
		
		var dataString = 'bookPosition=' + positionObject.id
				   + '&commodity=' + $("#book_commodity_input").val()
				   + '&message=' + $("#book_message_input").val()
				   + '&map=' + maptool.map.id
				   + catStr;
		
		if (maptool.map.userlevel > 1) {
			dataString += '&user=' + $("#book_user_input").val();
		}

		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: dataString,
			success: function(response) {
				maptool.markPositionAsNotBeingEdited();
				maptool.reload();
				maptool.closeDialogues();
				$('#book_position_dialogue input[type="text"], #book_position_dialogue textarea').val("");
				$('.ssinfo').html('');
			}
		});
	});
}

maptool.markForApplication = function(positionObject) {
	
	$('#apply_category_input, #apply_commodity_input').css('border-color', '#000000');
	$('#apply_mark_dialogue input[type="text"], #apply_mark_dialogue textarea, #apply_mark_dialogue select').val("");
	$('.mssinfo').html('<strong>' + lang.space + ' ' + positionObject.name + '<br/>' + lang.area + ':</strong> ' + positionObject.area + '<br/><strong>' + lang.info + ': </strong>' + positionObject.information);
	
	maptool.openDialogue('apply_mark_dialogue');
	
	$('#apply_choose_more').click(function() {
		
		
		if ($("#apply_commodity_input").val() == "") {
			$('#apply_commodity_input').css('border-color', 'red');
			return;
		}
		
		var cats = $('#apply_category_input').val();
		if (cats === null) {
			$('#apply_category_input').css('border-color', 'red');
			return;
		}
		
		var exists = false;
		for (var i=0; i<markedAsBooked.length; i++) {
			if (markedAsBooked[i].id == positionObject.id)
				exists = true;
		}
		
		if (!exists) {
			positionObject.user_commodity = $("#apply_commodity_input").val();
			positionObject.user_message = $("#apply_message_input").val();
			positionObject.user_categories = $("#apply_category_input").val();
			markedAsBooked.push(positionObject);
		}
		maptool.closeDialogues();
	});
	
	$('#apply_confirm').click(function() {
		
		if ($("#apply_commodity_input").val() == "") {
			$('#apply_commodity_input').css('border-color', 'red');
			return;
		}
		
		var cats = $('#apply_category_input').val();
		if (cats === null) {
			$('#apply_category_input').css('border-color', 'red');
			return;
		}
		
		var exists = false;
		for (var i=0; i<markedAsBooked.length; i++) {
			if (markedAsBooked[i].id == positionObject.id)
				exists = true;
		}
		
		if (!exists) {
			positionObject.user_commodity = $("#apply_commodity_input").val();
			positionObject.user_message = $("#apply_message_input").val();
			positionObject.user_categories = $("#apply_category_input").val();
			markedAsBooked.push(positionObject);
		}
		
		maptool.closeDialogues();
		maptool.applyForPositions();
	});
	
}

maptool.editMarking = function(id) {
	
	var obj = null;
	for (var i=0; i<markedAsBooked.length; i++) {
		if (markedAsBooked[i].id == id)
			obj = markedAsBooked[i];
	}
	
	$("#apply_commodity_input").val(obj.user_commodity);
	$("#apply_message_input").val(obj.user_message);
	$("#apply_category_input").val(obj.user_categories);
	
	$('.mssinfo').html('<strong>' + lang.space + ' ' + obj.name + '<br/>' + lang.area + ':</strong> ' + obj.area + '<br/><strong>' + lang.info + ': </strong>' + obj.information);
	maptool.openDialogue('apply_mark_dialogue');
	
}

maptool.applyForPositions = function() {
	if (markedAsBooked.length < 1)
		return;
	
	var html = '';
	for (var i=0; i<markedAsBooked.length; i++) {
		html += '<p><strong>' + lang.space + ' ' + markedAsBooked[i].name + '<br/>' + lang.area + ':</strong> ' + markedAsBooked[i].area + '<br/><strong>' + lang.info + ': </strong>' + markedAsBooked[i].information + '<br/><a class="ps_edit" id="ps_edit-' + markedAsBooked[i].id + '" href="javascript:void(0)">Edit</a> |<!-- <a href="javascript:void(0)" class="ps_move" id="ps_move-' + markedAsBooked[i].id + '">Go to stand space</a> |--> <a href="javascript:void(0)" class="ps_del" id="ps_del-' + markedAsBooked[i].id + '">Remove</a></p>';
	}
	$('.pssinfo').html(html);
	
	maptool.openDialogue('apply_position_dialogue');
	
	$('.ps_move').click(function() {
		maptool.focusOn($(this).attr("id").replace('ps_move-', ''));
		maptool.closeDialogues();
	});
	 
	$('.ps_edit').click(function() {
		maptool.closeDialogues();
		var id = $(this).attr("id").replace('ps_edit-', '');
		maptool.editMarking(id);
	});
	
	$('.ps_del').click(function() {
		var id = $(this).attr("id").replace('ps_del-', '');
		var arr = new Array;
		for (var i=0; i<markedAsBooked.length; i++) {
			if (markedAsBooked[i].id != id)
				arr.push(markedAsBooked[i]);
		}
		$(this).parent().remove();
	});
	
	$("#apply_post").click(function() {
		
		/*var cats = $('#apply_category_input').val();
		if (cats === null)
			return;
		var catStr = '';
		for (var i=0; i<cats.length; i++) {
			catStr += '&cat[]=' + cats[i];
		}*/
		
		var posStr = '';
		var msgStr = '';
		var catStr = '';
		var comStr = '';
		for (var i=0; i<markedAsBooked.length; i++) {
			
			posStr += 'preliminary[' + i + ']=' + markedAsBooked[i].id + '&';
			msgStr += 'message[' + i + ']=' + markedAsBooked[i].user_message + '&';
			comStr += 'commodity[' + i + ']=' + markedAsBooked[i].user_commodity + '&';
			
			for (var j=0; j<markedAsBooked[i].user_categories.length; j++) {
				catStr += 'categories[' + i + '][]=' + markedAsBooked[i].user_categories[j] + '&';
			}
			
		}
		
		var dataString = posStr
				   + msgStr
				   + comStr
				   + 'map=' + maptool.map.id
				   + '&' + catStr;
		
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: dataString,
			success: function(response) {
				maptool.reload();
				maptool.closeDialogues();
				$('#apply_position_dialogue input[type="text"], #apply_position_dialogue textarea').val("");
				markedAsBooked = new Array;
			}
		});
	});
	
}

maptool.applyForPosition = function(positionObject) {
	
	$('.pssinfo').html('<strong>' + lang.space + ' ' + positionObject.name + '<br/>' + lang.area + ':</strong> ' + positionObject.area + '<br/><strong>' + lang.info + ': </strong>' + positionObject.information);
	
	maptool.openDialogue('apply_position_dialogue');
	$("#apply_post").click(function() {
		
		var cats = $('#apply_category_input').val();
		if (cats === null)
			return;
		var catStr = '';
		for (var i=0; i<cats.length; i++) {
			catStr += '&cat[]=' + cats[i];
		}
		
		var dataString = 'preliminary=' + positionObject.id
				   + '&commodity=' + $("#apply_commodity_input").val()
				   + '&message=' + $("#apply_message_input").val()
				   + '&map=' + maptool.map.id
				   + catStr;
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: dataString,
			success: function(response) {
				maptool.reload();
				maptool.closeDialogues();
				$('#apply_position_dialogue input[type="text"], #apply_position_dialogue textarea').val("");
			}
		});
	});
}

maptool.cancelApplication = function(positionObject) {
	$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'cancelPreliminary=' + positionObject.id,
			success: function(response) {
				maptool.reload();
			}
		});
}

maptool.editBooking = function(positionObject) {
	
	if (positionObject.status == 2) {
		//booked
		//maptool.openDialogue('book_position_dialogue');
		var prefix = 'book';
	} else if (positionObject.status == 1) {
		//reserved
		//maptool.openDialogue('reserve_position_dialogue');
		var prefix = 'reserve';
		$('#' + prefix + '_expires_input').val(positionObject.expires);
	}
	maptool.openDialogue(prefix + '_position_dialogue');
	$('#' + prefix + '_commodity_input').val(positionObject.exhibitor.commodity);
	$('#' + prefix + '_message_input').val(positionObject.exhibitor.arranger_message);
	$('#' + prefix + '_user_input option[value="' + positionObject.exhibitor.user + '"]').attr('selected', 'selected');
	
	$('#' + prefix + '_category_input option').removeAttr("selected");
	
	for (var i=0; i<positionObject.exhibitor.categories.length; i++) {
		$('#' + prefix + '_category_input option[value="' + positionObject.exhibitor.categories[i].category_id + '"]').attr("selected", "selected");
	}
	
	$("#" + prefix + "_post").click(function() {

		var dataString = 'editBooking=' + positionObject.id
				   + '&commodity=' + $("#" + prefix + "_commodity_input").val()
				   + '&message=' + $("#" + prefix + "_message_input").val()
				   + '&exhibitor_id=' + positionObject.exhibitor.exhibitor_id
				   + '&map=' + maptool.map.id;
		
		var categories = $('#book_category_input').val();
		for (var i=0; i<categories.length; i++) {
			dataString += '&category[]=' + categories[i];
		}
		
		if (maptool.map.userlevel > 1) {
			dataString += '&user=' + $("#" + prefix + "_user_input").val();
		}
		
		if (positionObject.status == 1) {
			dataString += '&expires=' + $("#" + prefix + "_expires_input").val();
		}
		
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: dataString,
			success: function(response) {
				maptool.markPositionAsNotBeingEdited();
				maptool.reload();
				maptool.closeDialogues();
				$('#' + prefix + 'book_position_dialogue input[type="text"], #' + prefix + '_position_dialogue textarea').val("");
				$('.ssinfo').html('');
			}
		});
	});
	
}

maptool.cancelBooking = function(positionObject) {
	$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'cancelBooking=' + positionObject.id,
			success: function(response) {
				maptool.reload();
			}
		});
}

//Reserve open position
maptool.reservePosition = function(positionObject) {
	
	$('#reserve_category_input').css('border-color', '#000000');
	
	if (maptool.map.userlevel < 2) {
		$('#reserve_user_input, label[for="reserve_user_input"]').hide();
	}
	$("#reserve_post").off("click");
	if (positionObject.status == 2) {
		$("#reserve_commodity_input").val(positionObject.exhibitor.commodity);
		$("#reserve_message_input").val(positionObject.exhibitor.arranger_message);
		$('#reserve_user_input option[value="' + positionObject.exhibitor.user + '"]').attr("selected", "selected");
	} else {
		$("#reserve_commodity_input, #reserve_message_input, #reserve_expires_input").val("");
	}
	
	maptool.openDialogue('reserve_position_dialogue');
	$('.ssinfo').html('<strong>' + lang.space + ' ' + positionObject.name + '<br/>' + lang.area + ':</strong> ' + positionObject.area + '<br/><strong>' + lang.info + ': </strong>' + positionObject.information);
	
	$("#reserve_post").click(function() {
		
		var cats = $('#reserve_category_input').val();
		if (cats === null) {
			$('#reserve_category_input').css('border-color', 'red');
			return;
		} else {
			$('#reserve_category_input').css('border-color', '#000000');
		}
		
		if ($("#reserve_expires_input").val().match(/^\d\d-\d\d-\d\d\d\d$/)) {
			
			var catStr = '';
			for (var j=0; j<cats.length; j++) {
				catStr += '&categories[]=' + cats[j];
			}
			
			var dataString = 'reservePosition=' + positionObject.id
					   + '&commodity=' + $("#reserve_commodity_input").val()
					   + '&message=' + $("#reserve_message_input").val()
					   + '&expires=' + $("#reserve_expires_input").val()
					   + '&map=' + maptool.map.id
					   + catStr;
			
			if (maptool.map.userlevel > 1) {
				dataString += '&user=' + $("#reserve_user_input").val();
			}

			$.ajax({
				url: 'ajax/maptool.php',
				type: 'POST',
				data: dataString,
				success: function(response) {
					maptool.markPositionAsNotBeingEdited();
					maptool.reload();
					//document.location = document.location.replace('reserve', '');
					reserveId = false;					
					maptool.closeDialogues();
					$('#reserve_position_dialogue input[type="text"], #reserve_position_dialogue textarea').val("");
					$('.ssinfo').html('');
				}
			});

		} else {
			$("#reserve_expires_input").css('border-color', 'red');
		}

	});
}

//Save new position to database
maptool.savePosition = function() {
	
	if ($("#position_id_input").val() == 'new') {
		var xOffset = parseFloat($("#newMarkerIcon").offset().left + config.iconOffset);
		var yOffset = parseFloat($("#newMarkerIcon").offset().top + config.iconOffset);
		
		var mapWidth = $("#map #map_img").width();
		var mapHeight = $("#map #map_img").height();
		
		xOffset = xOffset - maptool.map.canvasOffset.left + $("#mapHolder").scrollLeft();
		var xPercent = (xOffset / mapWidth) * 100;
		
		yOffset = yOffset - maptool.map.canvasOffset.top + $("#mapHolder").scrollTop();
		var yPercent = (yOffset / mapHeight) * 100;
		
	} else {
		var xPercent = '';
		var yPercent = '';
	}
	
	var dataString = 'savePosition=' + $("#position_id_input").val()
				   + '&name=' + $("#position_name_input").val()
				   + '&area=' + $("#position_area_input").val()
				   + '&information=' + $("#position_info_input").val()
				   + '&x=' + xPercent
				   + '&y=' + yPercent
				   + '&map=' + maptool.map.id;

	$.ajax({
		url: 'ajax/maptool.php',
		type: 'POST',
		data: dataString,
		success: function(result) {
			$('#edit_position_dialogue input[type="text"], #edit_position_dialogue textarea').val("");
			maptool.markPositionAsNotBeingEdited();
			//maptool.reload();
			maptool.closeDialogues();
			maptool.placeMarkers();
			maptool.populateList();
		}
	});

}

//Delete position
maptool.deletePosition = function(id) {
	if (confirm(lang.deleteConfirm)) {
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'deleteMarker=' + id,
			success: function(res) {
				$("#pos-" + id).remove();
			}
		});
	}
}

//View more information about a certain position
maptool.positionInfo = function(positionObject) {
	
	if (positionObject.exhibitor)
		$("#more_info_dialogue h3").text(positionObject.name + ': ' + positionObject.exhibitor.company);
	else
		$("#more_info_dialogue h3").text(positionObject.name);

	var tt = '<p><strong>' + lang.status + ': </strong>' + lang.StatusText(positionObject.statusText) + '<br/><strong>' + lang.area + ' (m<sup>2</sup>):</strong> ' + positionObject.area + '</p>';
	var info = $("#more_info_dialogue .info");

	info.html(tt);
	if (positionObject.exhibitor)
		info.prepend('<p id="printLink"><a href="printerFriendly/exhibitor/' + positionObject.exhibitor.exhibitor_id + '" target="_blank">' + lang.print + '</a></p>');
	
	if (positionObject.exhibitor)
		info.append('<p><strong>' + lang.StatusText(positionObject.statusText).charAt(0).toUpperCase() + lang.StatusText(positionObject.statusText).substr(1) + ' ' + lang.by + ':</strong> ' + positionObject.exhibitor.company + '</p>');

	if (positionObject.status == 1) {
		info.append('<p><strong>' + lang.reservedUntil + ':</strong> ' + positionObject.expires + '</p>');
	}
	if (positionObject.exhibitor) {
		
		var categoryString = '';
		for (var i=0; i<positionObject.exhibitor.categories.length; i++) {
			categoryString += ', ' + positionObject.exhibitor.categories[i].name;
		}
		categoryString = categoryString.substring(2);
		
		$('#more_info_dialogue h4').text(lang.presentation);
		info.append('<p><strong>' + lang.commodity + ':</strong> ' + positionObject.exhibitor.commodity + '</p><p><strong>' + lang.category + ':</strong> ' + categoryString + '</p>');

		$("#more_info_dialogue p.presentation").text(positionObject.exhibitor.presentation);
		if (positionObject.exhibitor.website != '')
			$("#more_info_dialogue p.website_link").html('<strong>' + lang.website + ':</strong> <a href="' + positionObject.exhibitor.website + '" target="_blank">' + positionObject.exhibitor.website + '</a>');
		else
			$("#more_info_dialogue p.website_link").html('');
		
	} else {
		$("#more_info_dialogue p.presentation").html(positionObject.information.replace(/\n/g, '<br/>')); //replace(/ /g, '&nbsp;')
	}
	maptool.openDialogue('more_info_dialogue');

}

//Zoom to 0
maptool.zoomZero = function() {
	while (maptool.map.zoomlevel > 1) {
		maptool.zoomOut();
	}
}

//Zoom in on map to a certain level
maptool.zoomToLevel = function(e, level) {
	var currentWidth = $('#map #map_img').width();
	var currentHeight = $('#map #map_img').height();

	if (level > config.maxZoom) {
		level = config.maxZoom;
	}

	maptool.map.zoomlevel = level;
	newWidth = maptool.map.canvasWidth * maptool.map.zoomlevel;

	$("#mapHolder #map #map_img").css("height", "auto");
	$("#map #map_img").css({
		maxWidth: 'none',
		maxHeight: 'none',
		width: newWidth +"px"
	});
	maptool.adjustZoomMarker();
	maptool.reCalculatePositions();
	//if (e !== null)
	//	maptool.centerOn(e, currentWidth, currentHeight, 'in');
}

//Zoom in on map
maptool.zoomIn = function(e) {
	var currentWidth = $('#map #map_img').width();
	var currentHeight = $('#map #map_img').height();
	
	if (maptool.map.zoomlevel < config.maxZoom) {
		maptool.map.zoomlevel += config.zoomStep;
		newWidth = maptool.map.canvasWidth * maptool.map.zoomlevel;
		
		$("#mapHolder #map #map_img").css("height", "auto");
		$("#map #map_img").css({
			maxWidth: 'none',
			maxHeight: 'none',
			width: newWidth +"px"
		});
		maptool.adjustZoomMarker();
		if (e !== null)
			maptool.centerOn(e, currentWidth, currentHeight, 'in');
		maptool.reCalculatePositions();
	}
	
}

//Zoom out
maptool.zoomOut = function(e) {
	var currentWidth = $('#map #map_img').width();
	var currentHeight = $('#map #map_img').height();
	if (maptool.map.zoomlevel > 1) {
		maptool.map.zoomlevel -= config.zoomStep;
		newWidth = maptool.map.canvasWidth * maptool.map.zoomlevel;
		$("#map #map_img").css({
			maxWidth: 'none',
			maxHeight: 'none',
			width: newWidth+"px"
		});
		maptool.adjustZoomMarker();
		if (e !== null)
			maptool.centerOn(e, currentWidth, currentHeight, 'out');
		maptool.reCalculatePositions();
	}
}

maptool.reCalculatePositions = function() {
	
	for (var i=0; i<maptool.map.positions.length; i++) {
		var xMargin = (maptool.map.positions[i].x / 100) * $('#map_img').width();
		var yMargin = (maptool.map.positions[i].y / 100) * $('#map_img').height();
		//Reposition marker
		$('#pos-' + maptool.map.positions[i].id).css({
			left: xMargin + 'px',
			top: yMargin + 'px'
		});
	}
	
	var arrow = $('#focus_arrow');
	if (arrow.length > 0 && arrow.is(':visible')) {
		
		var ml = parseInt($('#pos-' + arrow.data('position')).css('left')) - 4;
		var mt = parseInt($('#pos-' + arrow.data('position')).css('top')) - 50;
		//Reposition arrow
		arrow.css({
			left: ml + 'px',
			top: mt + 'px'
		});
	}
}

maptool.centerOn = function(e, previousWidth, previousHeight, dir) {
	
	//maptool.clearMarkers();
	
	if (!e.originalEvent.offsetX) { //firefox
		x = e.originalEvent.layerX;
		y = e.originalEvent.layerY;
	} else {
		x = e.originalEvent.offsetX;
		y = e.originalEvent.offsetY;
	}
	
	if (!e.srcElement) { //firefox
		var img = {
			width: e.target.clientWidth,
			height: e.target.clientHeight
		};
	} else {
		var img = {
			width: e.srcElement.clientWidth,
			height: e.srcElement.clientHeight
		};
	}
	
	var current = {
		left: $("#mapHolder").scrollLeft(),
		top: $("#mapHolder").scrollTop()
	};
	
	var xFactor = x / previousWidth;
	var yFactor = y / previousHeight;
	
	var scrollX = (img.width * xFactor) - ($('#mapHolder').width() / 2);
	var scrollY = (img.height * yFactor) - ($('#mapHolder').height() / 2);
	
	$('#mapHolder').scrollLeft(scrollX);
	$('#mapHolder').scrollTop(scrollY);
	
}

//Focus map on given marker
maptool.focusOn = function(position) {
	
	$('#focus_arrow').remove();
	
	positionObject = null;
	for (var i=0; i<maptool.map.positions.length; i++) {
		if (maptool.map.positions[i].id == position) {
			positionObject = maptool.map.positions[i];
			break;
		}
	}

	if (maptool.map.zoomlevel < 2) {
		maptool.zoomToLevel(positionObject, 2);
	}
	
	var currentWidth = $('#map #map_img').width();
	var currentHeight = $('#map #map_img').height();
	
	var xPos = (positionObject.x / 100) * currentWidth;
	var yPos = (positionObject.y / 100)  *currentHeight;
	
	var scrollX = xPos - ($('#mapHolder').width() / 2);
	var scrollY = yPos - ($('#mapHolder').height() / 2);
	
	$('#mapHolder').scrollLeft(scrollX);
	$('#mapHolder').scrollTop(scrollY);
	
	//maptool.placeMarkers();
	
	var img = $('<img src="images/icons/arrow.png" id="focus_arrow"/>');
	img.data('position', positionObject.id);
	
	var ml = parseInt($('#pos-' + positionObject.id).css('left')) - 4;
	var mt = parseInt($('#pos-' + positionObject.id).css('top')) - 50;
	
	img.css({
		position: 'absolute',
		left: ml + 'px',
		top: mt + 'px'
	});
	$('#mapHolder #map').prepend(img);
	
	
}

//Adjust position of focus arrow
maptool.placeFocusArrow = function() {
	var arrow = $('#focus_arrow');
	if (arrow.is(":visible")) {
		
		var marker = $("#pos-" + arrow.data("position"));
		
		var ml = parseInt(marker.css('left')) - 4;
		var mt = parseInt(marker.css('top')) - 50;
		
		arrow.css({
			left: mt,
			top: ml
		});
		
	}
}

maptool.adjustZoomMarker = function() {
	
	if (maptool.map.zoomlevel == 1) {
		tm = 190;
	} else {
		var steps = (config.maxZoom - 1) / config.zoomStep;
		var currentStep = (maptool.map.zoomlevel-1) / config.zoomStep;
		
		var slideHeight = $('#zoombar').height() - $('#zoombar #in').height() - $('#zoombar #out').height();
		var tm = (slideHeight / steps) * currentStep;
		tm = $('#zoombar').height() - $('#zoombar #in').height() - tm;
	}
	$('#zoombar img').css({
		marginTop: tm + 'px'
	});
}

//Map panning
maptool.pan = function(dir) {

	var current = {
		left: $("#mapHolder").scrollLeft(),
		top: $("#mapHolder").scrollTop()
	};

	maptool.clearMarkers();

	if (dir == 'left') {
		var scroll = {scrollLeft: current.left - config.panMovement + 'px'}
	} else if (dir == 'right') {
		var scroll = {scrollLeft: current.left + config.panMovement + 'px'}
	} else if (dir == 'up') {
		var scroll = {scrollTop: current.top - config.panMovement + 'px'}
	} else if (dir == 'down') {
		var scroll = {scrollTop: current.top + config.panMovement + 'px'}
	}

	$("#mapHolder").animate(scroll, config.panSpeed, function() {
		maptool.placeMarkers();
	});

}

//Switch to full screen mode
maptool.fullScreenOn = function() {

	fullscreen = true;
	$('#fullscreen').show();

	$('#mapHolder').css({
		width:'100%'
	});
	$("#map #map_img").css({
		maxWidth: 'none',
		maxHeight: 'none'
	});

	var largeSrc = $('#mapHolder #map').attr('src').replace('.png', '_large.png');
	$('#map').attr("src", largeSrc);

	var screenImage = $("#image");

	var theImage = new Image();
	theImage.src = maptool.map.large_image;

	setTimeout(function() {
		var largeWidth = theImage.width;
		var largeHeight = theImage.height;

		//move create button
		$('#create_position').prependTo('#fullscreen_controls');

		//Move mapHolder to fullscreen and adjust it
		$('#mapHolder').appendTo('#fullscreen');
		$('#mapHolder').css({
			width: '100%',
			height:'100%',
			overflow:'auto',
			marginTop:'60px'
		});

		//adjust size of map
		$('#mapHolder #map').css({
			minWidth: '100%',
			minHeight:'100%'
		});

		//Raise z-index of markers
		$('.marker').css({
			zIndex:'1000'
		});

		maptool.reload();

	},1000)

}

maptool.reload = function() {
	canvasOriginalWidth = null;
	maptool.init(maptool.map.id);
}

maptool.update = function(posId) {
	if (update === true) {
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'init=' + maptool.map.id,
			success: function(result) {
				updated = JSON.parse(result);
				maptool.map.positions = updated.positions;
				maptool.placeMarkers();
				maptool.populateList();
				updateTimer = setTimeout('maptool.update()', config.markerUpdateTime * 1000);
				preHover(posId);
			}
		});
	}

}

maptool.pauseUpdate = function() {
	updateTimer = null;
	update = false;
}

maptool.resumeUpdate = function() {
	update = true;
	maptool.update();
}

maptool.ownsMap = function() {

	if (maptool.map.userlevel != 2)
		return true;

	var hit = false;
	for (var i=0; i<accessibleMaps.length; i++) {
		if (maptool.map.id == accessibleMaps[i]) {
			hit = true;
			break;
		}
	}
	return hit;
}

//Initiate maptool, setting up on a specified map
maptool.init = function(mapId) {
	$.ajax({
		url: 'ajax/maptool.php',
		type: 'POST',
		data: 'init=' + mapId,
		success: function(result) {
			maptool.map = JSON.parse(result);
			maptool.map.zoomlevel = 1;
			maptool.map.canvasWidth = $("#mapHolder").width();
			maptool.map.canvasHeight = $("#mapHolder").height();
			maptool.map.canvasOffset = $("#mapHolder").offset();
			$('#spots_total').text(maptool.map.positions.length);
			
			if (canvasOriginalWidth === null) {
				canvasOriginalWidth = $("#mapHolder").width();
				canvasOriginalHeight = $("#mapHolder").height();
				canvasOriginalOffset = $("#mapHolder").offset();
			}

			$("#map #map_img").attr("src", maptool.map.image);
			
			var holderHeight = $(document).height() - $('#header').height() -48;
			var listHeight = holderHeight - $('#right_sidebar div:first-child').height() - $('#right_sidebar .pre_list').height() - 84;
			
			$("#map #map_img").load(function() {
				
				$('#mapHolder').css({
					width: '100%',
					height: holderHeight + 'px'
				});
				$('#right_sidebar ul').css({
					height: listHeight + 'px'
				});
				$("#map #map_img").css({
					width: '100%',
					height: 'auto',
					display: 'inline'
				});
				
				maptool.map.canvasWidth = $("#mapHolder").width();
				maptool.map.canvasHeight = $("#mapHolder").height();
				maptool.map.canvasOffset = $("#mapHolder").offset();
				maptool.placeMarkers();
				maptool.populateList();
				
			});

			// Refresh the markers even if the image is already loaded.
			maptool.placeMarkers();
			maptool.populateList();

		}
	});

	$(".dialogue").prepend('<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue"/>');
	$(".closeDialogue").click(function() {
		maptool.closeDialogues();
	});
	
	if (!isNaN(prePosId)) {
		setTimeout(function() {
			maptool.focusOn(prePosId);
			
			if (!isNaN(reserveId)) {
				var object = null;
				for (var i=0; i<maptool.map.positions.length; i++) {
					if (maptool.map.positions[i].id == reserveId) {
						object = maptool.map.positions[i];
						break;
					}
				}
				maptool.markPositionAsBeingEdited(object);
				maptool.reservePosition(object);
				$.ajax({
					url: 'ajax/maptool.php',
					type: 'POST',
					data: 'getPreliminary=' + reserveId,
					success: function(res) {
						result = JSON.parse(res);
						$('#reserve_commodity_input').val(result.commodity);
						$('#reserve_message_input').val(result.arranger_message);
						$('#reserve_user_input option[value="' + result.id + '"]').attr("selected", "selected");
						for (var i=0; i<result.categories.length; i++) {
							$('#reserve_category_input option[value="' + result.categories[i] + '"]').attr("selected", "selected");
						}
					}
				});
			}
			
		}, 1000);
	}

}

//Event handlers
$(document).ready(function() {
	
	$('.order').click(function() {
		var sel = $(this).val();
		$('#book_user_input option, #reserve_user_input option').each(function() {
			if (!$(this).hasClass(sel)) {
				$(this).hide();
			} else {
				$(this).show();
			}
		});

	});
	$('#category_filter').change(function() {
		categoryFilter = $(this).val();
		maptool.placeMarkers();
		maptool.populateList();
	});
	$('#closeFullscreen').click(function() {
		document.location.href = document.location.protocol + '//' + document.location.host + '/mapTool/map/' + maptool.map.fair + '/none/' + maptool.map.id;
	});
	$("#zoomcontrols #full").click(function() {
		maptool.fullScreenOn();
	});
	$("#zoombar #in").click(function(e) {
		maptool.zoomIn(e);
	});
	$("#zoombar #out").click(function(e) {
		maptool.zoomOut(e);
	});
	$("#mapHolder").bind('DOMMouseScroll mousewheel', function(e, delta) {
		e.preventDefault();
		if ('wheelDelta' in e.originalEvent) {
			var delta = e.originalEvent.wheelDelta;
		} else {
			var delta = -40 * e.originalEvent.detail;
		}
		
		if (delta > 0) {
			maptool.zoomIn(e);
		} else {
			maptool.zoomOut(e);
		}
	});
	$("#map_nav li").click(function() {
		$("#map_nav li").removeClass("current");
		$(this).addClass("current");
		var map = parseInt($(this).attr("id").replace("map_link_", ""));
		maptool.init(map);
	});
	$("#map_select").change(function() {
		maptool.init(parseInt($(this).val()));
	});
	$("#panup, #pandown, #panleft, #panright").click(function() {
		var direction = $(this).attr("id").replace("pan", "");
		maptool.pan(direction);
	});
	$(document).click(function(e) {
		if (!$(e.target).hasClass("marker")) {
			maptool.hideContextMenu();
			$('.marker_tooltip').hide();
		}
	});
	$("#create_position").click(function(e) {
		if (hasRights && maptool.ownsMap()) {
			maptool.addPosition(e);
		} else {
			alert(lang.noPlaceRights);
		}
	});
	$(document).keydown(function(e) {
		if (e.keyCode == 27)
			maptool.closeDialogues();

	});
	$('#search_filter').keyup(function() {
		maptool.populateList();
	});
	$(window).resize(function() {
		maptool.reload();
	});
	
	//Scroll map by dragging
	$("#map #map_img").on("mousedown", function(e) {
		
		var start = {};
		start.x = e.pageX;
		start.y = e.pageY;
		
		$(this).on("mousemove", function(e) {
			maptool.map.beingDragged = true;
			$(this).css('cursor', 'move');
			
			e.preventDefault();
			e.stopPropagation();
			
			var viewport = $('#mapHolder');
			
			var xDiff = e.pageX - start.x;
			var yDiff = e.pageY - start.y;
			
			scrollX = viewport.scrollLeft() - xDiff;
			scrollY = viewport.scrollTop() - yDiff;
			
			viewport.scrollLeft(scrollX);
			viewport.scrollTop(scrollY);
			
			start.x = e.pageX;
			start.y = e.pageY;
			
		});
		return false;
	});
	
	$(window).on('orientationchange', function() {
		maptool.reCalculatePositions();
	});
	
	//Dragging stopped, clean up
	$(document).on("mouseup", function(e) {
		$("#map #map_img").off("mousemove");
		$("#map #map_img").css('cursor', 'default');
		$("#zoombar").off("mousemove");
		if (maptool.map.beingDragged) {
			maptool.map.beingDragged = false;
		}
	});
	
	$('#zoombar img').on('dragstart', function(e) {
		e.preventDefault();
	});
	
	$('#zoombar img').on("mousedown", function(e) {
		var curr = e.pageY;
		$('#zoombar').on("mousemove", function(e) {
			if (e.pageY == curr)
				return;
			
			if (e.pageY > curr) {
				maptool.zoomOut(e);
			} else {
				maptool.zoomIn(e);
			}
			curr = e.pageY;
		});
	});

	// Pause update
	$('.marker').mouseenter(function(){
		maptool.pauseUpdate();
	});
	// Resume update
	$('.marker').mouseleave(function(){
		maptool.resumeUpdate()
	});

	// Start automatic updating
	setTimeout('maptool.update()', config.markerUpdateTime * 1000);
});
