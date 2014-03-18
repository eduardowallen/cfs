var copiedExhibitor = null;
var canvasOriginalWidth = null;
var canvasOriginalHeight = null;
var canvasOriginalOffset = null;
var fullscreen = false;
var userIsEditing = 0;
var categoryFilter = 0;
var markedAsBooked = new Array;
var scrollTimeout = null;
var deltaSteps = 0;
var movingMarker = null;
var marker = null;
var _mapId = 0;
var start = {};
var isMoving = false;

//Some settings
var config = {
	maxZoom: 2, //maximum size of map, X * original
	zoomStep: 0.1, //how many times to increase/decrease map size on zoom
	panMovement: 200, //pixels, distance to pan
	panSpeed: 500, //animation speed for panning map
	iconOffset: 7.5, //pixels to adjust icon position (half the width/height of the icon)
	markerUpdateTime: 30, //marker update interval in seconds
	positionTopOffset: 0
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

function scrollbarWidth() { 
	var scrollDiv = document.createElement("div"); 
	document.body.appendChild(scrollDiv);
	scrollDiv.style.position = "absolute";
	scrollDiv.style.top = "50px";
	scrollDiv.style.overflow = "scroll";
	var scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth;
	document.body.removeChild(scrollDiv);

	return scrollbarWidth; 
}

(function($) {
    $.fn.hasScrollBar = function() {
        return this.get(0).scrollHeight > this.height();
    }
})(jQuery);

//Check if a set of coordinates (from a click for example) is on the map or not
maptool.isOnMap = function(x, y) {
	var mapHolder = $('#mapHolder');
	var scrollSize = scrollbarWidth();

	if($('#mapHolder').hasScrollBar()){
		if (x < maptool.map.canvasOffset.left ||
			x > maptool.map.canvasOffset.left + maptool.map.canvasWidth - scrollSize) {
			return false;
		}
		if (y < maptool.map.canvasOffset.top ||
			y > maptool.map.canvasOffset.top + maptool.map.canvasHeight - scrollSize) {
			return false;
		}
	} else {
		if (x < maptool.map.canvasOffset.left ||
			x > maptool.map.canvasOffset.left + maptool.map.canvasWidth) {
			return false;
		}
		if (y < maptool.map.canvasOffset.top ||
			y > maptool.map.canvasOffset.top + maptool.map.canvasHeight) {
			return false;
		}
	}

	return true;
}

//Open dialogue
maptool.openDialogue = function(id) {
	$('input#search_user_input').val("");
	$('input#search_user_input').css('border-color', '#666666');		
	$('.exhibitorNotFound').css('display', 'none');
	$("#overlay").show(0, function() {
		$(this).css({
			height: $(document).height() + 'px'
		});
		$("#" + id).show();
	});

	$('input, textarea').placeholder();
}

//Close any open dialogues
maptool.closeDialogues = function() {
	if (userIsEditing > 0) {
		maptool.markPositionAsNotBeingEdited();
	}			
	$(".dialogue").hide(0, function() {
		$("#overlay").hide();
		$("#popupform").remove();
		$("#popupform_register").remove();
		$('#popupformTwo').remove();
		$("#newMarkerIcon").remove();
	});
}

//Populate list of exhibitors
maptool.populateList = function() {
	var searchString = $('#search_filter').val();
	var prevSelectedId = -1;

	if ($('#right_sidebar ul li.selected:first').length != 0) {
		prevSelectedId = $('#right_sidebar ul li.selected:first').attr("id").replace("map-li-", "");
		$('#right_sidebar ul li.selected:first #list_commodity').show();
	}

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
			if (searchString != '') {
				var str = searchString.toLowerCase();
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
				var item = $('<li id="map-li-' + maptool.map.positions[i].id + '">' + maptool.map.positions[i].exhibitor.company + '<p id="list_commodity">' + maptool.map.positions[i].exhibitor.spot_commodity + '</p></li>');
				item.children('#list_commodity').hide();
				item.click(function() {
					$('#right_sidebar ul li').removeClass('selected');
					$('#right_sidebar ul li #list_commodity').hide();
					$(this).addClass('selected');
					$(this).children('#list_commodity').show();
					var index = $(this).attr("id").replace("map-li-", "");
					//maptool.positionInfo(maptool.map.positions[index]);
					maptool.focusOn(index);
				});
				if (maptool.map.positions[i].id == prevSelectedId) {
					item.addClass('selected');
				}
				$("#right_sidebar ul").append(item);
				$('#list_commodity').hide();
			}
		}
	}
	
}

//Place pre-fetched markers on map
maptool.placeMarkers = function() {
	//Remove all markers before placing new ones
	maptool.clearMarkers();
	var freeSpots = 0;
	var markerHTML = "";
	var tooltipHTML = "";
	var map_img = $("#map #map_img");
	var mapHolderContext = $("#mapHolder");
	var mapContext = $('#map', mapHolderContext);
	
	for (var i=0; i<maptool.map.positions.length; i++) {
		
		if (maptool.map.positions[i].applied) {
			if(maptool.map.positions[i].statusText == "booked" || maptool.map.positions[i].statusText == "reserved"){

			} else {
				maptool.map.positions[i].statusText = 'applied';
			}
		}
		//Prepare HTML
		var markerId = 'pos-' + maptool.map.positions[i].id;
		if (movingMarker != null && movingMarker.attr('id') == markerId) {
			continue;
		}
		var marker = $('<img src="images/icons/marker_' + maptool.map.positions[i].statusText + '.png" alt="" class="marker" id="' + markerId + '"/>');
		var tooltip = '<div class="marker_tooltip" id="info-' + maptool.map.positions[i].id + '">';

		//Tooltip content
		tooltip += '<h3>'+maptool.map.positions[i].name + ' </h3><p><strong>' + lang.status + ': </strong>' + lang.StatusText(maptool.map.positions[i].statusText) + '<br/><strong>' + lang.area + ' (m<sup>2</sup>):</strong> ' + maptool.map.positions[i].area + '</p>';
		
		if (maptool.map.positions[i].status > 0 && maptool.map.positions[i].exhibitor) { 
			tooltip += '<p><strong>' + lang.StatusText(maptool.map.positions[i].statusText).charAt(0).toUpperCase() + lang.StatusText(maptool.map.positions[i].statusText).substr(1) + ' ' + lang.by + ':</strong> ' + maptool.map.positions[i].exhibitor.company + '</p>';
			if (maptool.map.positions[i].status == 1) {
				tooltip += '<p><strong>' + lang.reservedUntil + ':</strong> ' + maptool.map.positions[i].expires + '</p>';
			}
			tooltip += '<strong>' + lang.commodity_label + ':</strong> <span class="info">';
			tooltip += maptool.map.positions[i].exhibitor.commodity + '</span>';
		} else {
			var info =  maptool.map.positions[i].information;
			info = info.substr(0, 100);
			if(info.length == 100){
				info += "...";			
			}
			tooltip+= '<p style="width:210px; overflow-y:hidden; line-height:14px; word-wrap:break-word;">';
			tooltip+=info;
			tooltip+='</p>';

			if (maptool.map.userlevel > 0) {
				tooltip += '<p><strong>' + lang.clickToReserveStandSpace + '</strong></p>';
			} 
			freeSpots++;
		}

		if(maptool.map.userlevel == 0){
			tooltip += '<p><strong>' + lang.clickToViewMoreInfo + '</strong></p>';
		}
		tooltip += '</div>';

		//Calculate position on map
		var xMargin = ((maptool.map.positions[i].x / 100) * map_img.width()) - config.iconOffset;
		var yMargin = ((maptool.map.positions[i].y / 100) * map_img.height())  - config.iconOffset;
		
		//Set marker and tooltip margin
		marker.css({
			left: xMargin + 'px',
			top: yMargin + 'px'
		});
		
		var d = new Date();
		if (maptool.map.positions[i].being_edited > 0 && maptool.map.positions[i].being_edited != maptool.map.user_id && ((Math.round(d.getTime() / 1000) - maptool.map.positions[i].edit_started) < 60*20)) {
			marker.attr('src', 'images/icons/marker_busy.png').addClass('busy');
		}

		// Add HTML to blob.
		markerHTML += marker[0].outerHTML;
		tooltipHTML += tooltip;
	}	
	$("#mapHolder #map").prepend(markerHTML);
	$("#mapHolder").prepend(tooltipHTML);

	// Pause update
	/*
	$(".marker", mapContext).hover(function() {
		maptool.pauseUpdate();
	}, function() {
		maptool.resumeUpdate();
	});
	*/

	//Display tooltip on hover
	$(".marker", mapContext).hover(function(e) {

		var tooltip = $("#info-" + $(this).attr("id").replace("pos-", ""));
		var marker = $(this);

		if (!tooltip.is(":visible")) {
			// Övre kant
			if ((tooltip.height() > marker.offset().top) && (tooltip.width() < marker.offset().left*2)) {
				tooltip.addClass('marker_tooltip_flipped'); 
				tooltip.css({
					left: marker.offset().left,
					top: marker.offset().top + 20 - config.positionTopOffset
				});
			}
			// Vänster övre kant
			else if ((tooltip.width() > marker.offset().left*2) && (tooltip.height() > marker.offset().top)){
				tooltip.addClass('marker_tooltip_flipped');
				tooltip.css({
					left: marker.offset().left + tooltip.width()/2,
					top: marker.offset().top + 15 - config.positionTopOffset
				});
			}
			// Vänster undre kant && Vänster kant
			else if ((tooltip.width() > marker.offset().left*2) && (tooltip.height() < marker.offset().top)){
				tooltip.addClass('marker_tooltip_flipped');
				tooltip.css({
					left: marker.offset().left + tooltip.width()/2,
					top: marker.offset().top - tooltip.height() - 15 - config.positionTopOffset
				});
			}
			// Under kant
			else if ((tooltip.height() < marker.offset().top) ) {
				tooltip.removeClass('marker_tooltip_flipped');
				tooltip.css({
					left: marker.offset().left,
					top: marker.offset().top - tooltip.height() - 20 - config.positionTopOffset
				});
			}
			//$(".marker_tooltip", mapHolderContext).hide();
			tooltip.css('display', 'inline');

			var infoText = tooltip.children('.info');
			var textHeight = tooltip.children('.info').height();
			if(textHeight > 41){
				infoText.html(infoText.html().replace('...', '').substr(0, 70) + '...');
			}
		}
	}, function() {
		var tooltip = $("#info-" + $(this).attr("id").replace("pos-", ""));
		tooltip.css('display', 'none');
	});
	
	//display dialogue on marker click (or touch, for iDevices)
	$(".marker", mapContext).bind("click touch", function() {
		maptool.showContextMenu($(this).attr("id").replace('pos-', ''), $(this));
	});
	
	maptool.placeFocusArrow();
	if ($('#spots_free').text() == "") {
		$('#spots_free').text(freeSpots);
	}
	
	for (var i=0; i<maptool.map.positions.length; i++) {
		var markerId = "pos-"+maptool.map.positions[i].id;
		var markerImg = document.getElementById(markerId);
		if (categoryFilter > 0) {
			if(maptool.map.positions[i].exhibitor != null){
				if(maptool.map.positions[i].exhibitor.categories.length > 0){
				//console.log(maptool.map.positions[i].exhibitor.categories);
				var ct = 0;
				$(maptool.map.positions[i].exhibitor.categories).each(function(){
					var markerCatId = this.category_id;					
					if (markerCatId == categoryFilter) {
						ct +=1;
					}
					if(ct > 0){
						markerImg.style.display = "inline";
					} else {
						markerImg.style.display = "none";
					}
					
				});
				} else {
					markerImg.style.display = "none";
				}
			} else {
				markerImg.style.display = "none";
			} 
		} else {
			markerImg.style.display = "inline";
		}
	}
}

//Remove all markers
maptool.clearMarkers = function() {
	$(".marker", "#mapHolder > #map").remove();
	$(".marker_tooltip", "#mapHolder").remove();
}

//Display tooltip for marker
maptool.tooltip = function(index) {
	$("#info-" + index).show();
}

//Create context menu for markers
maptool.showContextMenu = function(position, marker) {
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
	var todayDt = $('#todayDt').attr('td');
	var closeDt = $('#closeDt').attr('td');
	var publishDt = $('#publishDt').attr('td');
	if (maptool.map.positions[objIndex].status == 0 && maptool.map.userlevel > 1 && hasRights && maptool.ownsMap()) {
		contextMenu.append('<li id="cm_book">' + lang.bookStandSpace + '</li><li id="cm_reserve">' + lang.reserveStandSpace + '</li>');
		if (copiedExhibitor) {
			contextMenu.append('<li id="cm_paste">' + lang.pasteExhibitor + '</li>');
		}
	} else if (((todayDt > publishDt) && (todayDt < closeDt)) && maptool.map.positions[objIndex].status == 0 && maptool.map.userlevel == 1 && !maptool.map.positions[objIndex].applied && hasRights && maptool.ownsMap()) {
		contextMenu.append('<li id="cm_apply">' + lang.preliminaryBookStandSpace + '</li>');
	} else if (((todayDt > publishDt) && (todayDt < closeDt)) && maptool.map.positions[objIndex].status == 0 && maptool.map.userlevel == 1 && maptool.map.positions[objIndex].applied && hasRights && maptool.ownsMap()) {
		contextMenu.append('<li id="cm_cancel">' + lang.cancelPreliminaryBooking + '</li>');
	}
	
	if (maptool.map.userlevel > 1 && hasRights && maptool.ownsMap()) {
		contextMenu.append('<li id="cm_edit">' + lang.editStandSpace + '</li><li id="cm_move">' + lang.moveStandSpace + '</li><li id="cm_delete">' + lang.deleteStandSpace + '</li>');
	}
	
	if(((maptool.map.userlevel == 2 && hasRights) || maptool.map.userlevel > 2) && maptool.map.positions[objIndex].status > 0){
		contextMenu.append('<li id="cm_note">' + lang.notes + '</li>');
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
		

		contextMenu.css({
			left: $("#pos-" + position).offset().left + config.iconOffset,
			top: $("#pos-" + position).offset().top + config.iconOffset - 30
		}).show();

		//Clear click events
		//$(".contextmenu li").off("click");
		
		//click handlers for context menu
		if(maptool.map.userlevel > 0){
			$("#mapHolder").prepend(contextMenu);
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
				} else if(e.target.id == 'cm_note') {
					maptool.makeNote(maptool.map.positions[objIndex]);
				}
			}); 		
			} else {
				maptool.positionInfo(maptool.map.positions[objIndex]);
			}
	}

	var map = $('#mapHolder');
	if(map.height()-contextMenu.height() < marker.offset().top){
		contextMenu.css({
			top : marker.offset().top - (contextMenu.height() - 5),
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

	marker = $("#newMarkerIcon").css({
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
			$('#position_name_input').focus();
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

	var originalPositionX = positionObject.x;
	var originalPositionY = positionObject.y;


	$(".marker_tooltip").remove();
	marker = $("#pos-" + positionObject.id);

	movingMarker = marker;
	marker.off("click");

	var mapHolderContext = $("#mapHolder");
	var mapContext = $('#map', mapHolderContext);

	$('.marker').off("mouseenter mouseleave");
	marker.prependTo('body');
	var canAjax = true;

	$(document).on('mousemove', 'body', maptool.traceMouse);

	$(document).keyup(function(e) {
		if (e.keyCode == 27) {
			$.ajax({
				url: 'ajax/maptool.php',
				type: 'POST',
				data: 'movePosition=' + positionObject.id + '&x=' + originalPositionX + '&y=' + originalPositionY,
				success: function(res) {
					maptool.markPositionAsNotBeingEdited();
					maptool.resumeUpdate();
					maptool.update();
				}
			});

			$(document).off('mousemove', 'body', maptool.traceMouse);

			movingMarker.remove();
			movingMarker = null;
		}
	});
	
	marker.click(function(e) {
		if (maptool.isOnMap(e.clientX, e.clientY)) {
			marker.off("click");
			$(document).off('mousemove', 'body', maptool.traceMouse);

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
						maptool.update();
					}
				});
				movingMarker.remove();
			}
			movingMarker = null;	
		}
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
	$('#position_name_input').focus();
	//$("#post_position").click(function() {
	$("#post_position").on("click", function() {
		if ($("#position_name_input").val() != '') {
			maptool.savePosition();
			maptool.update();
			$('label[for="position_name_input"]').css("color", "#000000");
		} else {
			$('label[for="position_name_input"]').css("color", "red");
		}
	});

}

//Trace mouse movements with marker
maptool.traceMouse = function(e) {

	var top = e.pageY, 
		left = e.pageX,
		snapState = maptool.Grid.getSnapState();
		
	if (snapState.x) {
		top = maptool.Grid.snapY(top);
	}
	if (snapState.y) {
		left = maptool.Grid.snapX(left);
	}

	marker.css({
		top: top - config.iconOffset + 'px',
		left: left - config.iconOffset + 'px'
	});
}

//Book open position
maptool.bookPosition = function(positionObject) {
	$('#book_category_input').css('border-color', '#666');
	$('#book_category_scrollbox').css('border-color', '#000000');

	if (maptool.map.userlevel < 2) {
		$('#book_user_input, label[for="book_user_input"]').hide();
	}
	
	if (positionObject.status == 1) {
		$("#book_commodity_input").val(positionObject.exhibitor.commodity);
		$("#book_message_input").val(positionObject.exhibitor.arranger_message);
		$('#book_user_input option[value="' + positionObject.exhibitor.user + '"]').prop("selected", true);
		var categories = positionObject.exhibitor.categories;
		$('#book_category_scrollbox > p > input').prop('checked', false);
		for(var i = 0; i < categories.length; i++){
			$('#book_category_scrollbox > p').each(function(){
				var value = $(this).children().val();
				if(value == categories[i].category_id){
					$(this).children().prop('checked', true);
				}
			});
		}
	} else {
		$("#book_commodity_input, #book_message_input").val("");
	}
	
	maptool.openDialogue('book_position_dialogue');

	$('#book_position_dialogue h3 .standSpaceName').text(positionObject.name);
	$('.ssinfo').html("");
	$('.ssinfo').html('<strong>'+lang.area +  ' </strong>: ' + positionObject.area + '<br/><strong>' + lang.info + ': </strong>' + positionObject.information);

	$('#book_user_input').unbind('change');
	$('#book_user_input').change(function() {
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'getUserCommodity=1&userId=' + $('#book_user_input').val(),
			success: function(response) {
				if (response) {
					r = JSON.parse(response);
					$('#book_commodity_input').val(r.commodity);
				}
			}
		});
	});

	$('#book_position_dialogue > #search_user_input').unbind('keyup');
	$('#book_position_dialogue > #search_user_input').val('');
	$('#book_position_dialogue > #search_user_input').keyup(function(e) {
		if (e.keyCode == 13) {
			$('#book_user_input').change();
		} else {
			var query = $(this).val().toLowerCase();
			var selectedFirst = false;
			if (query == "") {
				$('#book_user_input > option').show();
			} else {
				$('#book_user_input > option').each(function() {
					if ($(this).text().toLowerCase().indexOf(query) == -1) {
						$(this).prop('selected', false);
						$(this).hide();
					} else {
						if (!selectedFirst) {
							$(this).prop("selected", true);
							selectedFirst = true;
						}
						$(this).show();
					}
				});
			}
		}
	});
	//var sBoxTop = $('#book_position_dialogue > #search_user_input').offset().top;
	//var sBoxLeft = $('#book_position_dialogue > #search_user_input').offset().left;
	//var sRes = $('#hiddenExhibitorList_d');
	//sRes.css('position', 'absolute');
	//sRes.css('top', '254px');
	//sRes.css('left', '25px');

	/*$('#book_position_dialogue > #search_user_input').unbind('keyup');
	$('#book_position_dialogue > #search_user_input').keyup(function(e) {
		if($('li.selected').text() != $('#book_position_dialogue > #search_user_input').val()){
			$('.exhibitorNotFound').css('display', 'block');
			$('.exhibitorNotFound').text('Exhibitor "'+$('#book_position_dialogue > #search_user_input').val()+'" does not exist.');
			$('#book_position_dialogue > #search_user_input').css('border-color', '#FF0000');
		}
		if (e.keyCode == 13) {
			if($('#book_position_dialogue > #search_user_input').val().indexOf($('li.selected').text()) == -1 || $('#book_position_dialogue > #search_user_input').val() == $('li.selected').text()){
				$('.exhibitorNotFound').css('display','none');
				$('#book_position_dialogue > #search_user_input').css('border-color', '#00FF00');
				$('#book_position_dialogue > #search_user_input').val($('li.selected').text());
			} 
			sRes.css('display', 'none');
		} else if(e.keyCode == 38){
			var elem = $('li.selected').prevAll(":visible:first");
			var elm = $('li.selected').nextAll(":visible:first");			
			if(elem.text() != ""){
				$('li.selected').removeClass('selected');
				elem.addClass('selected');
				var scroll = $('#hiddenExhibitorList_d').scrollTop() - elm.outerHeight();
				$('#hiddenExhibitorList_d').scrollTop(scroll);
			}
		} else if (e.keyCode == 40){	
			var elem = $('li.selected').nextAll(":visible:first");
			var elm = $('li.selected').prevAll(":visible:first");
			if(elem.text() != ""){
				$('li.selected').removeClass('selected');
				elem.addClass('selected');
				var scroll = $('#hiddenExhibitorList_d').scrollTop() + elm.outerHeight();
				$('#hiddenExhibitorList_d').scrollTop(scroll);
			}
		} else {
			var term = $('#book_position_dialogue > #search_user_input').val();
			maptool.searchForExhibitor(term, 'book');
			sRes.css('display', 'block');

			$('#overlay').mouseover(function(){
				$('#book_position_dialogue').off('click');
				$('#overlay').off('click');
				$('#hiddenExhibitorList_d').css('display', 'none');	
			});

			$('#hiddenExhibitorList').mouseleave(function(){
				$('#book_position_dialogue').off('click');
				$('#overlay').off('click');
				$('#hiddenExhibitorList_d').css('display', 'none');	
			});

			$('#book_position_dialogue').click(function(){
				$('#book_position_dialogue').off('click');
				$('#overlay').off('click');
				$('#hiddenExhibitorList_d').css('display', 'none');	
			});
		}
	});
	*/

	$('#book_post').unbind('click');
	$("#book_post").click(function() {
		var cats = new Array();
		var count = 0;
		$('#book_category_scrollbox > p').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				cats[count] = val;
				count = count+1;
			}
		});
		var catStr = '';

		for (var j=0; j<cats.length; j++) {
			if(cats[j] != undefined){
				catStr += '&categories[]=' + cats[j];
			}
		}
		
		var dataString = 'bookPosition=' + positionObject.id
				   + '&commodity=' + encodeURIComponent($("#book_commodity_input").val())
				   + '&message=' + encodeURIComponent($("#book_message_input").val())
				   + '&map=' + maptool.map.id
				   + catStr;

		if (maptool.map.userlevel > 1) {
			dataString += '&user=' + encodeURIComponent($("#book_user_input").val());
		}

		if(catStr.length != 0){
			$.ajax({
				url: 'ajax/maptool.php',
				type: 'POST',
				data: dataString,
				success: function(response) {
					maptool.markPositionAsNotBeingEdited();
					maptool.update();
					maptool.closeDialogues();
					$('#book_position_dialogue input[type="text"], #book_position_dialogue textarea').val("");
					$('.ssinfo').html('');
				}
			});
		} else {
			$('#book_category_scrollbox').css('border', '1px solid #f00');
		}
	});
}

maptool.markForApplication = function(positionObject) {
	$('#apply_category_input').css('border', '1px solid #666');
	$('#apply_category_input, #apply_commodity_input').css('border-color', '#000000');
	$('#apply_mark_dialogue textarea, #apply_mark_dialogue select').val("");
	$('.ssinfo').html("");
	$('.ssinfo').html('<strong>' + lang.space + ' ' + positionObject.name + '<br/>' + lang.area + ':</strong> ' + positionObject.area + '<br/><strong>' + lang.info + ': </strong>' + positionObject.information);

	maptool.openDialogue('apply_mark_dialogue');
	
	$("#apply_choose_more").unbind('click');
	$('#apply_choose_more').click(function() {
		if ($("#apply_commodity_input").val() == "") {
			$('#apply_commodity_input').css('border-color', 'red');
			return;
		}
		
		var cats = new Array();
		var count = 0;

		$('#apply_category_scrollbox > p').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				cats[count] = val;
				count = count+1;
			}
		});

		var catStr = '';
		for (var j=0; j<cats.length; j++) {
			if(cats[j] != undefined){
				catStr += '&categories[]=' + cats[j];
			}
		}

		if (cats.length == 0) {
			$('#apply_category_scrollbox').css('border', '1px solid #f00');
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
			positionObject.user_categories = catStr;
			markedAsBooked.push(positionObject);
		}
		maptool.closeDialogues();
	});
	
	$('#apply_confirm').unbind('click');
	$('#apply_confirm').click(function() {
		if ($("#apply_commodity_input").val() == "") {
			$('#apply_commodity_input').css('border-color', 'red');
			return;
		}
		var cats = new Array();
		var count = 0;
		$('#apply_category_scrollbox > p').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != null){
				cats[count] = val;
				count = count+1;
			}
		});

		if (count == 0) {
			$('#apply_category_scrollbox').css('border', '1px solid #f00');
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
			positionObject.user_categories = cats;
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
	
	$('.mssinfo').html('<strong>' + lang.space + ' ' + obj.name + '<br/>' + lang.area + ':</strong> ' + obj.area + '<br/><strong>' + lang.info + ': </strong>' + obj.information);
	maptool.openDialogue('apply_mark_dialogue');
	
}

maptool.applyForPositions = function() {
	if (markedAsBooked.length < 1)
		return;
	
	var html = '';
	for (var i=0; i<markedAsBooked.length; i++) {
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
				maptool.update();
				maptool.closeDialogues();
				$('#apply_position_dialogue input[type="text"], #apply_position_dialogue textarea').val("");
				markedAsBooked = new Array;
			}
		});
	}
	
	
}

maptool.applyForPosition = function(positionObject) {
	$('#apply_category_input').css('border', '1px solid #666');
	$('.ssinfo').html("");
	$('.ssinfo').html('<strong>' + lang.space + ' ' + positionObject.name + '<br/>' + lang.area + ':</strong> ' + positionObject.area + '<br/><strong>' + lang.info + ': </strong>' + positionObject.information);
	
	maptool.openDialogue('apply_position_dialogue');
	$("#apply_post").click(function() {
		var cats = new Array();
		var count = 0;
		$('#apply_category_scrollbox > p').each(function(){
			var val = $(this).children('input:checked').val();
			
			if(val != "undefined"){
				cats[count] = val;
				count = count+1;
			}
		});
		var catStr = '';
		for (var j=0; j<cats.length; j++) {
			if(cats[j] != undefined){
				catStr += '&categories[]=' + cats[j];
			}
		}
	
		var dataString = 'preliminary=' + positionObject.id
				   + '&commodity=' + $("#apply_commodity_input").val()
				   + '&message=' + $("#apply_message_input").val()
				   + '&map=' + maptool.map.id
				   + catStr;

		if(catStr.length != 0){
			$.ajax({
				url: 'ajax/maptool.php',
				type: 'POST',
				data: dataString,
				success: function(response) {
					maptool.update();
					maptool.closeDialogues();
					$('#apply_position_dialogue input[type="text"], #apply_position_dialogue textarea').val("");
				}
			});
		}  else {
			$('#apply_category_scrollbox').css('border', '1px solid #f00');
		}
	});
}

maptool.cancelApplication = function(positionObject) {
	$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'cancelPreliminary=' + positionObject.id,
			success: function(response) {
				maptool.update();
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

	$('#'+prefix+'_category_input').css('border', '1px solid #666');
	$('.ssinfo').html("");
	$('.ssinfo').html('<strong>' + lang.space + ' ' + positionObject.name + '<br/>' + lang.area + ':</strong> ' + positionObject.area + '<br/><strong>' + lang.info + ': </strong>' + positionObject.information);
	var categories = positionObject.exhibitor.categories;
	$('#'+prefix+'_category_scrollbox > p > input').prop('checked', false);
	for(var i = 0; i < categories.length; i++){
		$('#'+prefix+'_category_scrollbox > p').each(function(){
			var value = $(this).children().val();
			
			if(value == categories[i].category_id){
					$(this).children().prop('checked', true);
			}
		});
	}

	$('#'+prefix+'_user_input').unbind('change');
	$('#'+prefix+'_user_input').change(function() {
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'getUserCommodity=1&userId=' + $('#'+prefix+'_user_input').val(),
			success: function(response) {
				if (response) {
					r = JSON.parse(response);
					$('#'+prefix+'_commodity_input').val(r.commodity);
				}
			}
		});
	});
	
	$('#' + prefix + '_position_dialogue > #search_user_input').unbind('keyup');
	$('#' + prefix + '_position_dialogue > #search_user_input').val('');
	$('#' + prefix + '_position_dialogue > #search_user_input').keyup(function(e) {
		if (e.keyCode == 13) {
			$('#'+prefix+'_user_input').change();
		} else {
			var query = $(this).val().toLowerCase();
			var selectedFirst = false;
			if (query == "") {
				$('#'+prefix+'_user_input > option').show();
			} else {
				$('#'+prefix+'_user_input > option').each(function() {
					if ($(this).text().toLowerCase().indexOf(query) == -1) {
						$(this).prop('selected', false);
						$(this).hide();
					} else {
						if (!selectedFirst) {
							$(this).prop("selected", true);
							selectedFirst = true;
						}
						$(this).show();
					}
				});
			}
		}
	});
	
	/*
	var sBoxTop = $('#' + prefix + '_position_dialogue > #search_user_input').offset().top;
	var sBoxLeft = $('#' + prefix + '_position_dialogue > #search_user_input').offset().left;
	
	
	if(prefix == "book"){
		var sRes = $('#hiddenExhibitorList_d');
		sRes.css('position', 'absolute');
		sRes.css('top', '222px');
		sRes.css('left', '25px');
	} else {
		var sRes = $('#hiddenExhibitorList');
		sRes.css('position', 'absolute');
		sRes.css('top', '269px');
		sRes.css('left', '25px');
	}	

	$('#' + prefix + '_position_dialogue > #search_user_input').unbind('keyup');
	$('#' + prefix + '_position_dialogue > #search_user_input').keyup(function(e) {
		var term = $('#' + prefix + '_position_dialogue > #search_user_input').val();
		
		if (e.keyCode == 13) {
			$('#' + prefix + '_position_dialogue > #search_user_input').css('border-color', '#00FF00');
			sRes.css('display', 'none');
		} else if (e.keyCode == 38){
			var elem = $('li.selected').prevAll(":visible:first");
			if(elem.text() != ""){
				$('li.selected').removeClass('selected');
				elem.addClass('selected');
				var scroll = $('#hiddenExhibitorList_d').scrollTop() - 26;
				$('#hiddenExhibitorList_d').scrollTop(scroll);
			}
		} else if (e.keyCode == 40){
			var elem = $('li.selected').nextAll(":visible:first");
			if(elem.text() != ""){
				$('li.selected').removeClass('selected');
				elem.addClass('selected');
				var scroll = $('#hiddenExhibitorList_d').scrollTop() + 26;
				$('#hiddenExhibitorList_d').scrollTop(scroll);
			}
		} else {
			
			maptool.searchForExhibitor(term, prefix);
			sRes.css('display', 'block');
		
			$('#overlay').mouseover(function(){
				$('#' + prefix + '_position_dialogue').off('click');
				$('#overlay').off('click');
				$('#hiddenExhibitorList_d').css('display', 'none');	
			});
			
			$('#hiddenExhibitorList').mouseleave(function(){
				$('#' + prefix + '_position_dialogue').off('click');
				$('#overlay').off('click');
				$('#hiddenExhibitorList_d').css('display', 'none');	
			});

			$('#' + prefix + '_position_dialogue').click(function(){
				$('#' + prefix + '_position_dialogue').off('click');
				$('#overlay').off('click');
				$('#hiddenExhibitorList_d').css('display', 'none');	
			});
		}
	});
	*/

	maptool.openDialogue(prefix + '_position_dialogue');
	$('#' + prefix + '_commodity_input').val(positionObject.exhibitor.commodity);
	$('#' + prefix + '_message_input').val(positionObject.exhibitor.arranger_message);
	$('#' + prefix + '_user_input option[value="' + positionObject.exhibitor.user + '"]').prop('selected', true);
	
	$('#' + prefix + '_category_input option').prop("selected", false);
	var categories = positionObject.exhibitor.categories;
	$('#' + prefix + '_category_scrollbox > p > input').prop('checked', false);
	for (var i=0; i<positionObject.exhibitor.categories.length; i++) {
		$('#'+prefix+'_category_scrollbox').children().each(function(j){
			if(positionObject.exhibitor.categories[i].category_id == $(this).children('input').val()){
				$(this).children('input').prop('checked', true);
			}
		});
	}
	
	$("#" + prefix + "_post").unbind("click");
	$("#" + prefix + "_post").click(function() {
		
		var cats = new Array();
		var count = 0;

		$('#'+prefix+'_category_scrollbox > p').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				cats[count] = val;
				count = count+1;
			}
		});

		var catStr = '';
		for (var j=0; j<cats.length; j++) {
			if(cats[j] != undefined){
				catStr += '&categories[]=' + cats[j];
			}
		}

		var dataString = 'editBooking=' + positionObject.id
				   + '&commodity=' + $("#" + prefix + "_commodity_input").val()
				   + '&message=' + $("#" + prefix + "_message_input").val()
				   + '&exhibitor_id=' + positionObject.exhibitor.exhibitor_id
				   + '&map=' + maptool.map.id
				   + catStr;

		if (maptool.map.userlevel > 1) {
			dataString += '&user=' + $("#" + prefix + "_user_input").val();
		}
		
		if (positionObject.status == 1) {
			dataString += '&expires=' + $("#" + prefix + "_expires_input").val();
		}

		if(catStr.length > 0){
			$.ajax({
				url: 'ajax/maptool.php',
				type: 'POST',
				data: dataString,
				success: function(response) {
					maptool.markPositionAsNotBeingEdited();
					maptool.update();
					maptool.closeDialogues();
					$('#' + prefix + 'book_position_dialogue input[type="text"], #' + prefix + '_position_dialogue textarea').val("");
					$('.ssinfo').html('');
				}
			});
		} else {
			$('#'+prefix+'_category_scrollbox').css('border', '1px solid #f00');
		}
	});
	
}

maptool.cancelBooking = function(positionObject) {
	$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'cancelBooking=' + positionObject.id,
			success: function(response) {
				maptool.update();
			}
		});
}
var count;
/*
maptool.searchForExhibitor = function(searchTerm, box){
	var box;
	var exhbList;
	if(box == "reserve"){
		prefix = '#reserve';
		exhbList = 'hiddenExhibitorList';
	} else if(box == "book"){
		prefix = '#book';
		exhbList = 'hiddenExhibitorList_d';
	}
	var list = $('#'+exhbList+' > ul');
	$(list).children().css('display', 'none');
	$(list).children().removeClass('selected');
	count = 1;
	$(list).children().each(function(){
		var listItem = $(this).text();
		
		$(this).click(function(){
			$(this).off('click');
			$(prefix + ' > #search_user_input').text = $(this).text();
		});
		if(listItem.indexOf(searchTerm) !== -1){
			$(this).css('display', 'block');
			if(count == 1){
				$(this).addClass('selected');
				if(listItem == searchTerm){
					$('#search_user_input').css('border-color', '#00FF00');
					$('.exhibitorNotFound').css('display','none');
				} else {
					$('#search_user_input').css('border-color', '#FF0000');
				}
			}
			if(count > 6){
				$('#'+exhbList).css('max-height', '100px');
				$('#'+exhbList).css('overflow-y', 'scroll');
			}	

			$(this).mouseover(function(){$(this).parent().children().removeClass('selected'); $(this).addClass('selected');});
			$(this).mouseout(function(){$(this).removeClass('selected');});
			count +=1;
		} else {
			
			$(this).removeClass('selected');
			$(this).css('display', 'none');
		}
		
	});
}
*/
//Reserve open position
maptool.reservePosition = function(positionObject) {
	$('#reserve_category_input').css('border', '1px solid #666');
	
	if (maptool.map.userlevel < 2) {
		$('#reserve_user_input, label[for="reserve_user_input"]').hide();
	}

	if (positionObject.status == 2) {
		$("#reserve_commodity_input").val(positionObject.exhibitor.commodity);
		$("#reserve_message_input").val(positionObject.exhibitor.arranger_message);
		$('#reserve_user_input option[value="' + positionObject.exhibitor.user + '"]').prop("selected", true);
		var categories = positionObject.exhibitor.categories;
		for(var i = 0; i < categories.length; i++){
			$('#reserve_category_scrollbox > p').each(function(){
				var value = $(this).children().val();
				if(value == categories[i].category_id){
					$(this).children().attr('checked', 'checked');
				}
			});
		}
	} else {
		$("#reserve_commodity_input, #reserve_message_input, #reserve_expires_input").val("");
	}
	
	maptool.openDialogue('reserve_position_dialogue');

	$('#reserve_position_dialogue h3 .standSpaceName').text(positionObject.name);
	$('.ssinfo').html("");
	$('.ssinfo').html('<strong>' + lang.space + ' ' + positionObject.name + '<br/>' + lang.area + ':</strong> ' + positionObject.area + '<br/><strong>' + lang.info + ': </strong>' + positionObject.information);

	$('#reserve_user_input').unbind('change');
	$('#reserve_user_input').change(function() {
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'getUserCommodity=1&userId=' + encodeURIComponent($('#reserve_user_input').val()),
			success: function(response) {
				if (response) {
					r = JSON.parse(response);
					$('#reserve_commodity_input').val(r.commodity);
				}
			}
		});
	});

	$('#reserve_position_dialogue > #search_user_input').unbind('keyup');
	$('#reserve_position_dialogue > #search_user_input').val('');
	$('#reserve_position_dialogue > #search_user_input').keyup(function(e) {
		if (e.keyCode == 13) {
			$('#reserve_user_input').change();
		} else {
			var query = $(this).val().toLowerCase();
			var selectedFirst = false;
			if (query == "") {
				$('#reserve_user_input > option').show();
			} else {
				$('#reserve_user_input > option').each(function() {
					if ($(this).text().toLowerCase().indexOf(query) == -1) {
						$(this).prop('selected', false);
						$(this).hide();
					} else {
						if (!selectedFirst) {
							$(this).prop("selected", true);
							selectedFirst = true;
						}
						$(this).show();
					}
				});
			}
		}
	});
	/*
	var sBoxTop = $('#reserve_position_dialogue > #search_user_input').offset().top;
	var sBoxLeft = $('#reserve_position_dialogue > #search_user_input').offset().left;
	var sRes = $('#hiddenExhibitorList');

	sRes.css('position', 'absolute');
	sRes.css('top', '254px');
	sRes.css('left', '25px');

	$('#reserve_position_dialogue > #search_user_input').unbind('keyup');
	$('#reserve_position_dialogue > #search_user_input').keyup(function(e) {
		var term = $('#reserve_position_dialogue > #search_user_input').val();
		if($('li.selected').text() != $('#reserve_position_dialogue > #search_user_input').val()){
			$('.exhibitorNotFound').css('display', 'block');
			$('.exhibitorNotFound').text('Exhibitor "'+$('#reserve_position_dialogue > #search_user_input').val()+'" does not exist.');
			$('#reserve_position_dialogue > #search_user_input').css('border-color', '#FF0000');
		}

		if (e.keyCode == 13) {
			if($('#reserve_position_dialogue > #search_user_input').val().indexOf($('li.selected').text()) == -1 || $('#reserve_position_dialogue > #search_user_input').val() == $('li.selected').text()){
				$('.exhibitorNotFound').css('display','none');
				$('#reserve_position_dialogue > #search_user_input').css('border-color', '#00FF00');
				$('#reserve_position_dialogue > #search_user_input').val($('li.selected').text());
			}
			sRes.css('display', 'none');
		} else if(e.keyCode == 38){
			var elem = $('li.selected').prevAll(":visible:first");
			var elm = $('li.selected').nextAll(":visible:first");			
			if(elem.text() != ""){
				$('li.selected').removeClass('selected');
				elem.addClass('selected');
				var scroll = $('#hiddenExhibitorList').scrollTop() - elm.outerHeight();
				$('#hiddenExhibitorList').scrollTop(scroll);
			}
		} else if (e.keyCode == 40){	
			var elem = $('li.selected').nextAll(":visible:first");
			var elm = $('li.selected').prevAll(":visible:first");
			if(elem.text() != ""){
				$('li.selected').removeClass('selected');
				elem.addClass('selected');
				var scroll = $('#hiddenExhibitorList').scrollTop() + elm.outerHeight();
				$('#hiddenExhibitorList').scrollTop(scroll);
			}
		} else {
			maptool.searchForExhibitor(term, 'reserve');
			sRes.css('display', 'block');

			$('#overlay').mouseover(function(){
				$('#reserve_position_dialogue').off('click');
				$('#overlay').off('click');
				$('#hiddenExhibitorList').css('display', 'none');	
			});
			
			$('#hiddenExhibitorList').mouseleave(function(){
				$('#reserve_position_dialogue').off('click');
				$('#overlay').off('click');
				$('#hiddenExhibitorList').css('display', 'none');	
			});

			$('#reserve_position_dialogue').click(function(){
				$('#reserve_position_dialogue').off('click');
				$('#overlay').off('click');
				$('#hiddenExhibitorList').css('display', 'none');	
			});
		}
		
	});
	*/
	$("#reserve_post").unbind("click");
	$("#reserve_post").click(function() {
		var cats = new Array();


		var count = 0;
		$('#reserve_category_scrollbox > p').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				cats[count] = val;
				count = count+1;
			}
		});

		/*
		if (cats === null) {
			$('#reserve_category_scrollbox').css('border-color', 'red');
			return;
		} else {
			$('#reserve_category_scrollbox').css('border-color', '#000000');
		}*/

		if ($("#reserve_expires_input").val().match(/^\d\d-\d\d-\d\d\d\d \d\d:\d\d$/)) {
			var dateParts = $("#reserve_expires_input").val().split('-');
			dt = new Date(parseInt(dateParts[2], 10), parseInt(dateParts[1], 10)-1, parseInt(dateParts[0], 10));
			// Add one day, since it should be up to and including.
			dt.setDate(dt.getDate()+1);
			if (dt < new Date()) {
				$("#reserve_expires_input").css('border-color', 'red');
				return;
			}
		} else {
			$("#reserve_expires_input").css('border-color', 'red');
			return;
		}	

		var catStr = '';
		for (var j=0; j<cats.length; j++) {
			if(cats[j] != undefined){
				catStr += '&categories[]=' + cats[j];
			}
		}

		var dataString = 'reservePosition=' + positionObject.id
				   + '&commodity=' + encodeURIComponent($("#reserve_commodity_input").val())
				   + '&message=' + encodeURIComponent($("#reserve_message_input").val())
				   + '&expires=' + encodeURIComponent($("#reserve_expires_input").val())
				   + '&map=' + maptool.map.id
				   + catStr;
		
		if (maptool.map.userlevel > 1) {
			dataString += '&user=' + encodeURIComponent($("#reserve_user_input").val());
		}
		
		if(catStr.length != 0){
			$.ajax({
				url: 'ajax/maptool.php',
				type: 'POST',
				data: dataString,
				success: function(response) {
					maptool.markPositionAsNotBeingEdited();
					maptool.update();
					//document.location = document.location.replace('reserve', '');
					reserveId = false;					
					maptool.closeDialogues();
					$('#reserve_position_dialogue input[type="text"], #reserve_position_dialogue textarea').val("");
					$('.ssinfo').html('');
				}
			});
		}  else {
			$('#reserve_category_scrollbox').css('border', '1px solid #f00');
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
	
	var dataString = 'savePosition=' + encodeURIComponent($("#position_id_input").val())
				   + '&name=' + encodeURIComponent($("#position_name_input").val())
				   + '&area=' + encodeURIComponent($("#position_area_input").val())
				   + '&information=' + encodeURIComponent($("#position_info_input").val())
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
			maptool.update();
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
	$("#more_info_dialogue h3").text("");
	$('#more_info_dialogue h4').text("");
	$("#more_info_dialogue p.presentation").html("");
	$("#more_info_dialogue p.website_link").html("");

	if (positionObject.exhibitor)
		$("#more_info_dialogue h3").text(positionObject.name + ': ' + positionObject.exhibitor.company);
	else
		$("#more_info_dialogue h3").text(positionObject.name);

	var tt = '<p><strong>' + lang.status + ': </strong>' + lang.StatusText(positionObject.statusText) + '<br/><strong>' + lang.area + ' (m<sup>2</sup>):</strong> ' + positionObject.area + '</p>';
	var info = $("#more_info_dialogue .info");

	info.html(tt);

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
		
		$('#more_info_dialogue h4').text(lang.presentation + ":");
		info.append('<p><strong>' + lang.commodity_label + ':</strong> ' + positionObject.exhibitor.commodity + '</p><p><strong>' + lang.category + ':</strong> ' + categoryString + '</p>');
		$("#more_info_dialogue .presentation").empty();
		$('#more_info_dialogue .presentation').css('display', 'block');
		if(positionObject.exhibitor.presentation.length < 1){
			
			$("#more_info_dialogue .presentation").append(lang.noPresentationText);
		}else{
			$("#more_info_dialogue .presentation").append(positionObject.exhibitor.presentation);
		}
		
		if (positionObject.exhibitor.website != '') {
			var website = positionObject.exhibitor.website;
			if (website.indexOf("http://") == -1) {
				website = "http://" + website;
			}
			$("#more_info_dialogue p.website_link").html('<strong>' + lang.website + ':</strong> <a href="' + website + '" target="_blank">' + positionObject.exhibitor.website + '</a>');
		} else
			$("#more_info_dialogue p.website_link").html('');
		
	} else {
		if(positionObject.information.length < 1){
			$('#more_info_dialogue .presentation').css('display', 'none');
		} else {
			$('#more_info_dialogue .presentation').css('display', 'block');
			$("#more_info_dialogue .presentation").html(positionObject.information.replace(/\n/g, '<br/>')); //replace(/ /g, '&nbsp;')
		}
	}

	if (positionObject.exhibitor) {
		$('#printLink').remove();
		info.parent().append('<a href="/mapTool/print_position/' + maptool.map.id + '/' + positionObject.id + '" target="_blank" class="link-button" id="printLink">' + lang.print + '</a>');
	}

	maptool.openDialogue('more_info_dialogue');

}

maptool.getNotes = function(positionObject){
	$('#note_dialogue > textarea').text ="";
	var lblTitle = $('#note_dialogue h3');
	lblTitle.text(lblTitle.data('text') + positionObject.name);
	var fairId = maptool.map.fair;
	var exhibitorId = positionObject.exhibitor.user;
	var positionId = positionObject.id;
	
	if(exhibitorId != "undefined"){	
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST', 
			data: 'getComment=1&fair='+fairId+'&exhibitor='+exhibitorId+'&position='+positionId
		}).success(function(response){
			$('.commentList').html(response);
		});
	} 
}

maptool.makeNote = function(positionObject){
	$('#note_dialogue > button').off('click');
	var lblTitle = $('#note_dialogue h3');
	lblTitle.text(lblTitle.data('text') + positionObject.name);
	$('#note_dialogue > textarea').val = "";
	var fairId = maptool.map.fair;
	var exhibitorId = positionObject.exhibitor.user;
	var positionId = positionObject.id;
	var who = $('#noteName').text();


	maptool.getNotes(positionObject);
	$('#note_dialogue > button').click(function(){
		var text = $('#note_dialogue > textarea').val();
		
		var place;

		if($('#commentOnSpace').val() == '1'){
			place = 0;
		} else {
			place = positionId;
		}

		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST', 
			data: 'makeComment=1&fair='+fairId+'&exhibitor='+exhibitorId+'&position='+place+'&text='+text
		}).success(function(response){
			maptool.getNotes(positionObject);
		});
	});
	maptool.openDialogue('note_dialogue');
}

//Zoom to 0
maptool.zoomZero = function() {
	while (maptool.map.zoomlevel > 1) {
		maptool.zoomOut();
	}
}

//Zoom in on map to a certain level
maptool.zoomToLevel = function(e, level) {
	maptool.hideContextMenu();
	$(".marker_tooltip").hide();
	var currentWidth = $('#map #map_img').width();
	var currentHeight = $('#map #map_img').height();

	if (level > config.maxZoom) {
		level = config.maxZoom;
	} else if (level < 1) {
		level = 1;
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

maptool.zoomAdjust = function(e, factor) {

	if (factor > config.maxZoom) {
		factor = config.maxZoom;
	} else if (factor < 1) {
		factor = 1;
	}

	var factorDiff = factor - maptool.map.zoomlevel

	var offsetLeft = $("#mapHolder").offset().left;
	var offsetTop = $("#mapHolder").offset().top;
	var offsetX = e.originalEvent.pageX - offsetLeft;
	var offsetY = e.originalEvent.pageY - offsetTop;
	//var scrollX = $("#mapHolder").scrollLeft();
	//var scrollY = $("#mapHolder").scrollTop();

	oldWidth = $("#map #map_img").width();
	oldHeight = $("#map #map_img").height();
	//var newWidth = maptool.map.canvasWidth * factor;
	$("#map #map_img").css({
		maxWidth: 'none',
		maxHeight: 'none',
		width: (factor*100)+"%"
	});
	newWidth = $("#map #map_img").width();
	newHeight = $("#map #map_img").height();

	var scrollX = $("#mapHolder").scrollLeft();
	var scrollY = $("#mapHolder").scrollTop();

	var newScrollX = scrollX + (newWidth - oldWidth)/2 + (offsetX - $("#mapHolder").width()/2) * (factorDiff);
	var newScrollY = scrollY + (newHeight - oldHeight)/newHeight*(scrollY+offsetY) - ($("#mapHolder").height()/2 - offsetY) * (factorDiff)/newHeight*(scrollY+offsetY);

	//newScrollX += (offsetX - $("#mapHolder").width()/2) * (factorDiff);
	//newScrollY -= ($("#mapHolder").height()/2 - offsetY) * (factorDiff);

	$("#mapHolder").scrollLeft(newScrollX);
	$("#mapHolder").scrollTop(newScrollY);

	maptool.map.zoomlevel = factor;
}

//Zoom in on map
maptool.zoomIn = function(e) {
	maptool.hideContextMenu();
	$(".marker_tooltip").hide();
	var currentWidth = $('#map #map_img').width();
	var currentHeight = $('#map #map_img').height();
	
	if (maptool.map.zoomlevel < config.maxZoom) {
		maptool.zoomAdjust(e, maptool.map.zoomlevel + config.zoomStep);
		maptool.adjustZoomMarker();
		maptool.reCalculatePositions();
	}
	
}

//Zoom out
maptool.zoomOut = function(e) {
	maptool.hideContextMenu();
	$(".marker_tooltip").hide();
	var currentWidth = $('#map #map_img').width();
	var currentHeight = $('#map #map_img').height();
	if (maptool.map.zoomlevel > 1) {
		maptool.zoomAdjust(e, maptool.map.zoomlevel - config.zoomStep);
		maptool.adjustZoomMarker();
		maptool.reCalculatePositions();
	}
}

/**
 * Map tool grid
 */
maptool.Grid = (function() {

	var grid = null,
	grid_frame = null,
	map_canvas = null,
	grid_generation_timer = null,
	supports_transform = (typeof document.createElement('div').style.transform !== 'undefined'),
	maptoolboxHeader = null,
	maptoolbox = null,

	gridmove = {
		started: false,
		start_x: null,
		start_y: null,
		element_start_x: null,
		element_start_y: null
	},

	toolboxmove = {
		started: false,
		start_x: null,
		start_y: null,
		element_start_x: 20,
		element_start_y: 60
	},

	settings = {
		activated: false,
		visible: {
			x: false,
			y: false
		},
		opacity: 100,
		white: false,
		snap_markers: {
			x: false,
			y: false
		},
		is_moving: false,

		coords: {
			x: 0,
			y: 0
		},

		width: 20,
		height: 20
	},

	setting_listeners = {
		activated: toggleActivated,
		visible_x: toggleVisibility,
		visible_y: toggleVisibility,
		opacity: changeOpacitySlide,
		opacity_num: changeOpacityNum,
		white: toggleWhite,
		snap_markers_x: toggleSnapMarkers,
		snap_markers_y: toggleSnapMarkers,
		is_moving: toggleIsMoving,
		coord_x: changeCoords,
		coord_y: changeCoords,
		width: changeDimensions,
		height: changeDimensions,
		width_rat: changeDimensionsChained,
		height_rat: changeDimensionsChained
	};

	/**
	 * Generates the grid as HTML
	 */
	function generateGrid() {
		var html = '', 
			num_cols = Math.ceil((grid.width() + settings.width * 2) / settings.width), 
			num_rows = Math.ceil((grid.height() + settings.height * 2) / settings.height), 
			num_cells = num_cols * num_rows, 
			i;

		if ($("#maptool_grid_activated")[0].checked) {
			for (i = 0; i < num_cells; i++) {
				html += '<div class="grid-cell"></div>';
			}
		}

		grid_frame.html(html);
	}

	/**
	 * Save grid settings to database
	 */
	function setGridSettings() {
		$.ajax({
			url: "ajax/maptool.php",
			type: "POST",
			data: {
				"setGridSettings": _mapId,
				"gridSettings": JSON.stringify(settings)
			}
		});
	}

	/**
	 * Fetch grid settings from database
	*/
	function getGridSettings() {
		$.ajax({
			url: "ajax/maptool.php",
			type: "POST",
			data: {
				"getGridSettings": _mapId
			},
			success: function (response) {
				if (response) {
					settings = JSON.parse(response);
					
					setSettings();
				}
			}
		});
	}

	/**
	 * Call this to request a new grid generation, but not directly.
	 * This is useful to call when a setting changes frequently.
	 */
	function requestGeneration() {
		if (grid_generation_timer !== null) {
			clearTimeout(grid_generation_timer);
			grid_generation_timer = null;
		}

		setTimeout(generateGrid, 1000);
	}

	function updateCSS() {
		var style_css = '.grid-cell {' +
				'width: ' + (settings.width - 1) + 'px;' +
				'height: ' + (settings.height - 1) + 'px;' +
			'}';

		try {
			$('#maptool_grid_style').html(style_css);
		} catch (error) {
			$('#maptool_grid_style')[0].styleSheet.cssText = style_css;
		}

		grid_frame.css({
			width: grid.width() + settings.width * 2 + 'px',
			height: grid.height() + settings.height * 2 + 'px'
		});

		var top = (settings.height * -1) + settings.coords.y + 'px',
			left = (settings.width * -1) + settings.coords.x + 'px';

		if (supports_transform) {
			grid_frame.css('transform', 'translate(' + left + ', ' + top + ')');
		} else {
			grid_frame.css({
				top: top,
				left: left
			});
		}
	}

	function updateCoords(x, y) {
		settings.coords.x = x;
		settings.coords.y = y;

		setTimeout(function() {
			$('#maptool_grid_coord_x').val(settings.coords.x);
			$('#maptool_grid_coord_y').val(settings.coords.y);
		}, 200);

		updateCSS();
	}

	function validateCoordsAndSet(x, y) {
		var delta_x = settings.width - Math.abs(x),
			delta_y = settings.height - Math.abs(y);

		if (delta_x < 0) {
			x = settings.coords.x;
		}

		if (delta_y < 0) {
			y = settings.coords.y;
		}

		updateCoords(x, y);
	}

	function toggleToolbox(e) {
		if (e) {
			e.preventDefault();
		}

		var $maptoolbox = $(maptoolbox),
			minimize = document.getElementById("maptoolbox_minimize");

		$maptoolbox.toggleClass('minimized');

		if ($maptoolbox.hasClass("minimized")) {
			maptoolbox.style.top = "";
			maptoolbox.style.bottom = 0;
			maptoolbox.style.left = "20px";

			maptoolboxHeader.off("mousedown", toolboxStartMove);

			window.setTimeout(function () {
				minimize.setAttribute("title", "Maximize");
			}, 500);
		} else {
			maptoolbox.style.bottom = "";
			maptoolbox.style.left = toolboxmove.element_start_x + "px";
			maptoolbox.style.top = toolboxmove.element_start_y + "px";

			maptoolboxHeader.on("mousedown", toolboxStartMove);

			window.setTimeout(function () {
				minimize.setAttribute("title", "Minimize");
			}, 500);
		}

		saveToolboxPosition();
	}

	function toggleVisibility() {
		settings.visible = {
			x: $('#maptool_grid_visible_x').prop('checked'),
			y: $('#maptool_grid_visible_y').prop('checked')
		};

		if (settings.visible.x) {
			grid.addClass("gridBorderBottom");
		} else {
			grid.removeClass("gridBorderBottom");
		}

		if (settings.visible.y) {
			grid.addClass("gridBorderRight");
		} else {
			grid.removeClass("gridBorderRight");
		}
	}

	function changeOpacity(value) {
		if (value) {
			settings.opacity = value;
		}

		grid.css('opacity', settings.opacity / 100);
	}

	function changeOpacitySlide() {
		changeOpacity(parseInt($('#maptool_grid_opacity').val(), 10));
		$('#maptool_grid_opacity_num').val(settings.opacity);
	}

	function changeOpacityNum() {
		changeOpacity(parseInt($('#maptool_grid_opacity_num').val(), 10));
		$('#maptool_grid_opacity').val(settings.opacity);
	}

	function toggleWhite() {
		settings.white = $('#maptool_grid_white').prop('checked');

		if (settings.white) {
			grid.addClass('white');
		} else {
			grid.removeClass('white');
		}
	}

	function toggleSnapMarkers() {
		settings.snap_markers = {
			x: $('#maptool_grid_snap_markers_x').prop('checked'),
			y: $('#maptool_grid_snap_markers_y').prop('checked')
		};
	}

	function toggleIsMoving() {
		settings.is_moving = $('#maptool_grid_is_moving').prop('checked');

		if (settings.is_moving) {
			grid.addClass('moving');
		} else {
			grid.removeClass('moving');
		}
	}

	function changeCoords() {
		var x = parseInt($('#maptool_grid_coord_x').val(), 10),
			y = parseInt($('#maptool_grid_coord_y').val(), 10);

		validateCoordsAndSet(x, y);
	}

	function changeDimensions() {
		settings.width = Math.max(10, parseInt($('#maptool_grid_width').val(), 10));
		settings.height = Math.max(10, parseInt($('#maptool_grid_height').val(), 10));

		updateCSS();
		requestGeneration();
	}

	function changeDimensionsChained(e) {
		if (e) {
			var value = parseInt($(this).val(), 10);

			$('#maptool_grid_width, #maptool_grid_height, #maptool_grid_width_rat, #maptool_grid_height_rat').val(value);

			changeDimensions();
		}
	}

	function toggleActivated() {
		settings.activated = $("#maptool_grid_activated")[0].checked;

		generateGrid();
	}

	function resetGrid() {
		$('#maptool_grid_width_rat').val(20).trigger('change');
		updateCoords(0, 0);
	}

	function setSettings() {
		//Set active
		$("#maptool_grid_activated")[0].checked = settings.activated;
		toggleActivated();
		//Set visibility
		$("#maptool_grid_visible_x")[0].checked = settings.visible.x;
		$("#maptool_grid_visible_y")[0].checked = settings.visible.y;
		toggleVisibility();

		//Set opacity
		$("#maptool_grid_opacity").val(settings.opacity);
		$("#maptool_grid_opacity_num").val(settings.opacity);
		changeOpacity();

		//Set grid color
		$("#maptool_grid_white")[0].checked = settings.white;
		toggleWhite();

		//Set snap to grid
		$("#maptool_grid_snap_markers_x")[0].checked = settings.snap_markers_x;
		$("#maptool_grid_snap_markers_y")[0].checked = settings.snap_markers_y;

		//Set is moving
		$("#maptool_grid_is_moving")[0].checked = settings.is_moving;
		toggleIsMoving();

		//Set coords
		$("#maptool_grid_coord_x").val(settings.coords.x);
		$("#maptool_grid_coord_y").val(settings.coords.y);
		changeCoords();

		//Set dimensions
		$("#maptool_grid_width").val(settings.width);
		$("#maptool_grid_width_rat").val(settings.width);
		$("#maptool_grid_height").val(settings.height);
		$("#maptool_grid_height_rat").val(settings.height);
		changeDimensions();
	}

	function windowSizeChanged() {
		var offset = map_canvas.offset();

		grid.css({
			top: offset.top,
			left: offset.left,
			width: map_canvas.width() - scrollbarWidth(),
			height: map_canvas.height()
		});

		updateCSS();
		requestGeneration();
	}

	function mouseMoved(e) {
		if (gridmove.started) {
			var x = e.pageX - gridmove.start_x - map_canvas.offset().left + gridmove.element_start_x, 
				y = e.pageY - gridmove.start_y - map_canvas.offset().top + gridmove.element_start_y;

			validateCoordsAndSet(x, y);
		} else if (isMoving) {
			moveMap(e);
		}
	}

	function toolboxMove(e) {
		var x = e.pageX - toolboxmove.start_x + toolboxmove.element_start_x, 
				y = e.pageY - toolboxmove.start_y + toolboxmove.element_start_y - config.positionTopOffset;

		maptoolbox.style.left = x + "px";
		maptoolbox.style.top = y + "px";
	}

	function startMove(e) {
		e.preventDefault();

		if (settings.is_moving) {
			gridmove.started = true;
			gridmove.start_x = e.pageX - map_canvas.offset().left;
			gridmove.start_y = e.pageY - map_canvas.offset().top;
			gridmove.element_start_x = settings.coords.x;
			gridmove.element_start_y = settings.coords.y;
		} else {
			start.x = e.pageX;
			start.y = e.pageY;
			isMoving = true;
		}
	}

	function toolboxStartMove(e) {
		var offset = $(maptoolbox).offset();

		e.preventDefault();

		if (e.target.id !== "maptoolbox_minimize") {
			toolboxmove.started = true;
			toolboxmove.start_x = e.pageX;
			toolboxmove.start_y = e.pageY;
			toolboxmove.element_start_x = offset.left;
			toolboxmove.element_start_y = offset.top;
			$(document.body).on("mousemove", toolboxMove);
		}
	}

	function stopMove() {
		gridmove.started = false;
		isMoving = false;
	}

	function toolboxStopMove(e) {
		if (toolboxmove.started) {
			toolboxmove.started = false;
			$(document.body).off("mousemove", toolboxMove);

			saveToolboxPosition();
		}
	}

	function saveToolboxPosition() {
		var $maptoolbox = $(maptoolbox);
		var offset = {};

		if ($maptoolbox.hasClass("minimized")) {
			//If minimized, save the maximized position
			offset.left = toolboxmove.element_start_x;
			offset.top = toolboxmove.element_start_y;
			offset.minimized = true;
		} else {
			offset = $maptoolbox.offset();
			offset.top -= $("#header").outerHeight();
		}

		offset = JSON.stringify(offset);

		$.ajax({
			url: "ajax/maptool.php",
			type: "POST",
			data: {
				saveToolboxPosition: offset,
			}
		});
	}

	function setMaptoolboxPosition() {
		$.ajax({
			url: "ajax/maptool.php",
			type: "POST",
			dataType: "JSON",
			data: {
				getToolboxPosition: ""
			},
			success: function (response) {
				if (response) {
					toolboxmove.element_start_x = response.left;
					toolboxmove.element_start_y = response.top;

					if (response.minimized) {
						toggleToolbox();
					} else {
						maptoolbox.style.left = response.left + "px";
						maptoolbox.style.top = response.top + "px";
					}
				}
			}
		});
	}

	function init() {
		// Don't init if we already init'ed
		if (grid === null) {
			grid = $('#maptool_grid');
			grid_frame = $('#maptool_grid_frame');
			map_canvas = $('#mapHolder');
			maptoolboxHeader = $("#maptoolbox_header");
			maptoolbox = $("#maptoolbox")[0];

			config.positionTopOffset = $("#header").outerHeight();

			getGridSettings();

			$('.spinner').spinner({
				spin: function(e, ui) {
					$(this).val(ui.value);
					$(this).trigger('change');
				}
			});

			// Toolbox events
			for (var property in setting_listeners) {
				if (setting_listeners.hasOwnProperty(property)) {
					$('#maptool_grid_' + property).on('change', setting_listeners[property]);
					setting_listeners[property]();
				}
			}

			$('#maptoolbox_minimize').on('click', toggleToolbox);
			$('#maptool_grid_reset').on('click', resetGrid);
			$('#maptool_grid_save').on('click', setGridSettings);
			$("#maptoolbox_header").on("mousedown", toolboxStartMove);

			// Grid movement events
			grid.on('mousemove', mouseMoved);
			grid_frame.on('mousedown', startMove);
			grid_frame.on('mouseup', stopMove);

			//Toolbox movement events
			maptoolboxHeader.on("mousedown", toolboxStartMove);
			maptoolboxHeader.on("mouseup", toolboxStopMove);

			setMaptoolboxPosition();

			// Window resize events
			$(window).on('resize', windowSizeChanged);
			windowSizeChanged();
		}
	}

	function getSnapState() {
		return settings.snap_markers;
	}

	function snap(value, property) {
		return settings[property] * Math.round(value / settings[property]);
	}

	function snapX(x) {
		return snap(x - map_canvas.offset().left - settings.coords.x, 'width') + map_canvas.offset().left + settings.coords.x;
	}

	function snapY(y) {
		return snap(y - map_canvas.offset().top - settings.coords.y, 'height') + map_canvas.offset().top + settings.coords.y;
	}

	// Public API
	return {
		'init': init,
		'getSnapState': getSnapState,
		'snapX': snapX,
		'snapY': snapY,
		'canvasChanged': windowSizeChanged
	};
}());

maptool.reCalculatePositions = function() {
	
	for (var i=0; i<maptool.map.positions.length; i++) {
		var xMargin = (maptool.map.positions[i].x / 100) * $('#map_img').width() - config.iconOffset;
		var yMargin = (maptool.map.positions[i].y / 100) * $('#map_img').height() - config.iconOffset;
		//Reposition marker
		$('#pos-' + maptool.map.positions[i].id).css({
			left: xMargin + 'px',
			top: yMargin + 'px'
		});
	}
	
	var arrow = $('#focus_arrow');
	if (arrow.length > 0 && arrow.is(':visible')) {
		var ml = parseInt($('#pos-' + positionObject.id).css('left')) + 8;
		var mt = parseInt($('#pos-' + positionObject.id).css('top')) - 32;
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

	if (maptool.map.zoomlevel < 1+(config.maxZoom-1)/2) {
		maptool.zoomToLevel(positionObject, 1+(config.maxZoom-1)/2);
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
	
	var img = $('<img src="images/icons/crosshair.png" id="focus_arrow"/>');
	img.data('position', positionObject.id);
	
	var ml = parseInt($('#pos-' + positionObject.id).css('left')) + 8;
	var mt = parseInt($('#pos-' + positionObject.id).css('top')) - 32;
	
	img.css({
		"z-index": 997,
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
		
		var ml = parseInt($('#pos-' + positionObject.id).css('left')) + 8;
		var mt = parseInt($('#pos-' + positionObject.id).css('top')) - 32;
		
		arrow.css({
			position: 'absolute',
			left: ml,
			top: mt
		});
	}
}

maptool.adjustZoomMarker = function(zoomLevel) {
	if (typeof zoomLevel == 'undefined') {
		zoomLevel = maptool.map.zoomlevel;
	}

	if (zoomLevel > config.maxZoom) {
		zoomLevel = config.maxZoom;
	} else if (zoomLevel < 1) {
		zoomLevel = 1;
	}
	
	if (zoomLevel == 1) {
		tm = 124;
	} else {
		var steps = (config.maxZoom - 1) / config.zoomStep;
		var currentStep = (zoomLevel-1) / config.zoomStep;
		
		var slideHeight = $('#zoombar').height() - $('#zoombar #in').height() - $('#zoombar #out').height() - $('#zoombar img').height();
		var tm = (slideHeight / steps) * currentStep;
		tm = $('#zoombar #in').height() + slideHeight - tm;
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
	$('#map').attr("src", largeSrc+ new Date().getTime());

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
				if (update === true) {
					updated = JSON.parse(result);
					maptool.map.positions = updated.positions;
					maptool.placeMarkers();

					maptool.populateList();
					maptool.placeFocusArrow();
					updateTimer = setTimeout(maptool.update, config.markerUpdateTime * 30000);
					preHover(posId);
				}
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

	// Quick fix for map reloading without id sometimes.
	if (typeof mapId == 'undefined') {
		return;
	}

	_mapId = mapId;

	if ($('#maptoolbox').length) {
		maptool.Grid.init();
	}

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

			$("#map > #map_img").attr("src", maptool.map.image+"?date="+ new Date().getTime());
			
			var holderHeight = $(document).height() - $('#header').height() -48;
			var listHeight = holderHeight - $('#right_sidebar div:first-child').height() - $('#right_sidebar .pre_list').height() - 84;

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
			
			$("#map #map_img").load(function() {
				
				maptool.map.canvasWidth = $("#mapHolder").width();
				maptool.map.canvasHeight = $("#mapHolder").height();
				maptool.map.canvasOffset = $("#mapHolder").offset();
				maptool.placeMarkers();
				maptool.populateList();
				maptool.Grid.canvasChanged();
				
			});
			// Refresh the markers even if the image is already loaded.
			maptool.placeMarkers();
			maptool.populateList();

		}
	});

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
						$('#reserve_user_input option[value="' + result.id + '"]').prop("selected", true);
						for (var i=0; i<result.categories.length; i++) {
							$('#reserve_category_input option[value="' + result.categories[i] + '"]').prop("selected", true);
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

		if (scrollTimeout != null) {
			clearTimeout(scrollTimeout);
		}
		if ('wheelDelta' in e.originalEvent) {
			deltaSteps += e.originalEvent.wheelDelta/Math.abs(e.originalEvent.wheelDelta);
		} else {
			deltaSteps += -40 * e.originalEvent.detail/Math.abs(-40 * e.originalEvent.detail);
		}
		maptool.adjustZoomMarker(maptool.map.zoomlevel + config.zoomStep*deltaSteps);
		scrollTimeout = setTimeout(function() {
			if (deltaSteps != 0) {
				maptool.zoomAdjust(e, maptool.map.zoomlevel + config.zoomStep*deltaSteps);
				maptool.adjustZoomMarker();
				maptool.reCalculatePositions();
			}
			clearTimeout(scrollTimeout);
			deltaSteps = 0;
		}, 200);
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
	
	$("#connect").click(function(e) {
		if (!$(this).hasClass("loginlink")) {
			$.ajax({
				url: 'ajax/maptool.php',
				type: 'POST',
				data: 'connectToFair=1&fairId=' + maptool.map.fair,
				success: function(response) {
					res = JSON.parse(response);
					alert(res.message);
					if (res.success) {
						$("#connect")[0].remove();
					}
				}
			});
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
		var isiPad = /ipad/i.test(navigator.userAgent.toLowerCase());
		if(jQuery.browser.mobile){

		} else if (isiPad) {

		} else {
			maptool.reload();
		}
	});
	
	//Scroll map by dragging
	$("#map #map_img").on("mousedown", function(e) {
		start.x = e.pageX;
		start.y = e.pageY;

		$(this).on("mousemove", moveMap);
		return false;
	});
	
	$(window).on('orientationchange', function() {
		maptool.reCalculatePositions();
	});
	
	//Dragging stopped, clean up
	$(document).on("mouseup", function(e) {
		$("#map #map_img").off("mousemove");
		$("#map #map_img").css('cursor', 'default');
		$("#maptool_grid_frame").css("cursor", "default");
		$("#zoombar").off("mousemove");
		if (maptool.map.beingDragged) {
			maptool.map.beingDragged = false;
		}
		isMoving = false;
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

	// Start automatic updating
	setTimeout(maptool.update, config.markerUpdateTime * 1000);
});

function moveMap(e) {
	var $map = $("#map_img");

	maptool.map.beingDragged = true;
	$map.css('cursor', 'move');
	$("#maptool_grid_frame").css("cursor", "move");
	
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
}

function chooseThis(thisd){
	var text = $(thisd).text();
	var id = $(thisd).val();
	$('.exhibitorNotFound').css('display', 'none');
	$('input#search_user_input').css('border-color','#00FF00');
	$('input#search_user_input').val(text);
	$('input#reserve_user_input').val(id);
	$('#hiddenExhibitorList').hide();
}

function chooseThisBook(thisd){
	var text = $(thisd).text();
	var id = $(thisd).val();
	$('.exhibitorNotFound').css('display', 'none');
	$('input#search_user_input').val(text);
	$('input#search_user_input').css('border-color','#00FF00');
	$('input#book_user_input').val(id);
	$('#hiddenExhibitorList').hide();
}
