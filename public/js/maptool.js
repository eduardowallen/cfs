var copiedExhibitor = null;
var copiedFairRegistration = null;
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
var grid = null;
var grid_frame = null;
var map_canvas = null;

//Some settings
var config = {
	maxZoom: 2, //maximum size of map, X * original
	zoomStep: 0.1, //how many times to increase/decrease map size on zoom
	panMovement: 200, //pixels, distance to pan
	panSpeed: 500, //animation speed for panning map
	iconOffset: 7.5, //pixels to adjust icon position (half the width/height of the icon)
	markerUpdateTime: 30, //marker update interval in seconds
	positionTopOffset: 30, //amount of pixels to separate the cursor from the tooltips for stand spaces
};

//Prepare maptool object
var maptool = {};
maptool.map = {};
var updateTimer = null;
var update = true;

// If you come from My bookings with a id you want to hover over that position
function preHover(id){
	//if( ! isNaN(id) ){
	$(document).ready(function() {
		setTimeout(function() {
			$('#info-' + id).show();
		}, 2000);
	});
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
//Open multiform
maptool.openForm = function(id) {
	$('input#search_user_input').val("");
	$('.exhibitorNotFound').css('display', 'none');
	$("#overlay").show(0, function() {
		$(this).css({
			height: $(document).height() + 'px'
		});
		$("#" + id).show();
	});

}

//Close any open multiforms
maptool.closeForms = function() {
	if (userIsEditing > 0) {
		maptool.markPositionAsNotBeingEdited();

	} else if (movingMarker !== null) {
		maptool.endMovePosition();
	}

	$(".form:visible").last().hide(0, function() {
		// Hide the overlay if no more forms are visible
		if ($(".form:visible").length === 0) {
			$("#overlay").fadeOut();
		}
	});


}

//Open dialogue
maptool.openDialogue = function(id) {
	$('input#search_user_input').val("");
	$('.exhibitorNotFound').css('display', 'none');
	$("#overlay").show(0, function() {
		$(this).css({
			height: $(document).height() + 'px'
		});
		$("#" + id).show();
		positionDialogue(id);
	});

}

//Close any open dialogues
maptool.closeDialogues = function() {
	if (userIsEditing > 0) {
		maptool.markPositionAsNotBeingEdited();

	} else if (movingMarker !== null) {
		maptool.endMovePosition();
	}

	// Hide the last visible dialog
	$(".dialogue:visible").last().hide(0, function() {

		// Hide the overlay if no more dialogs are visible
		if ($(".dialogue:visible").length === 0) {
			$("#overlay").fadeOut();
		}

		$("#popupform").remove();
		$("#popupform_help").remove();		
		$("#popupform_register").remove();
		$('#popupformTwo').remove();
		$("#newMarkerIcon").remove();
		$("#apply_category_scrollbox").css("border-color", "");
		$("#nouser_dialogue").remove();
	});
	$(".booking_dialogue:visible").last().hide(0, function() {
		if ($(".booking_dialogue:visible").length === 0) {
			$("#overlay").fadeOut();
		}
	});
}

//Populate list of exhibitors
maptool.populateList = function() {
	var searchString = $('#search_filter').val();
	var prevSelectedId = -1;
	var filtered;

	if ($('#right_sidebar ol li.selected:first').length != 0) {
		prevSelectedId = $('#right_sidebar ol li.selected:first').attr("id").replace("map-li-", "");
		$('#right_sidebar ol li.selected:first #list_commodity').show();
	}

	//Filter out elements that do not have a company name
	filtered = maptool.map.positions.filter(function (e) {
		return !!(e.exhibitor && e.exhibitor.company);
	});

	//Sort filtered list of companies
	filtered.sort(function (a, b) {
		return alphanum(a.name, b.name);
	});

	$("#right_sidebar ol").html('');
	for (var i=0; i<filtered.length; i++) {
		if (filtered[i].exhibitor !== null) {
			var hide = false;
			if (categoryFilter > 0) {
				if (!filtered[i].exhibitor) {
					hide = true;
				} else {
					var catMatched = false;
					for (var j=0; j<filtered[i].exhibitor.categories.length; j++) {
						if (filtered[i].exhibitor.categories[j].category_id == categoryFilter) {
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
				
				if (filtered[i].exhibitor.company && filtered[i].exhibitor.company.toLowerCase().indexOf(str) > -1) {
					matched = true;
				}
				if (filtered[i].exhibitor.spot_commodity && filtered[i].exhibitor.spot_commodity.toLowerCase().indexOf(str) > -1) {
					matched = true;
				}
				if (filtered[i].name && filtered[i].name.toLowerCase().indexOf(str) > -1) {
					matched = true;
				}
				if (!matched) {
					hide = true;
				}
			}
			if (!hide) {
				var item = $('<li id="map-li-' + filtered[i].id + '"><p id="list_position_name">' + filtered[i].name + '</p>' + filtered[i].exhibitor.company + '<p id="list_commodity">' + filtered[i].exhibitor.spot_commodity + '</p></li>');
				item.children('#list_commodity').hide();
				item.click(function() {
					$('#right_sidebar ol li').removeClass('selected');
					$('#right_sidebar ol li #list_commodity').hide();
					$(this).addClass('selected');
					$(this).children('#list_commodity').show();
					var index = $(this).attr("id").replace("map-li-", "");
					//maptool.positionInfo(filtered[index]);
					maptool.focusOn(index);
				});
				if (filtered[i].id == prevSelectedId) {
					item.addClass('selected');
				}
				$("#right_sidebar ol").append(item);
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
		if (maptool.map.userlevel > 0) {
			var marker = $('<img src="images/icons/marker_' + maptool.map.positions[i].statusText + '.png" alt="" class="marker" id="' + markerId + '"/>');
		} else {
			if (maptool.map.positions[i].status == 2) {
				var marker = $('<img src="images/icons/marker_booked.png" alt="" class="marker" id="' + markerId + '"/>');
			} else if (maptool.map.positions[i].status == 1) {
				var marker = $('<img src="images/icons/marker_reserved.png" alt="" class="marker" id="' + markerId + '"/>');
			} else {
				var marker = $('<img src="images/icons/marker_open.png" alt="" class="marker" id="' + markerId + '"/>');
			}
		}

		var tooltip = '<div class="marker_tooltip" id="info-' + maptool.map.positions[i].id + '">';
		//Tooltip content
		if (maptool.map.userlevel > 0) {
			if (!hasRights) {
			tooltip += '<h3>'+ maptool.map.positions[i].name + ' </h3><p><strong>' + lang.status + ': </strong>' + lang.StatusText(maptool.map.positions[i].statusText) + '<br/><strong>' + lang.area + ': </strong>';
			} else {
				if (maptool.map.positions[i].exhibitor && maptool.map.positions[i].exhibitor.clone > 0) {
						tooltip += '<h3>'+maptool.map.positions[i].name + ' </h3><p><strong>' + lang.status + ': </strong>' + lang.StatusText(maptool.map.positions[i].statusText) + ' <strong>' + lang.cloned + '</strong><br/><strong>' + lang.area + ': </strong>';
				} else {
					tooltip += '<h3>'+maptool.map.positions[i].name + ' </h3><p><strong>' + lang.status + ': </strong>' + lang.StatusText(maptool.map.positions[i].statusText) + '<br/><strong>' + lang.area + ': </strong>';
				}
			}
		} else {
			if (maptool.map.positions[i].status == 2) {
				tooltip += '<h3>'+maptool.map.positions[i].name + ' </h3><p><strong>' + lang.status + ': </strong>' + lang.StatusText(maptool.map.positions[i].statusText) + '<br/><strong>' + lang.area + ': </strong>';
			} else {
				tooltip += '<h3>'+maptool.map.positions[i].name + ' </h3><p><strong>' + lang.status + ': </strong>' + lang.StatusText(maptool.map.positions[i].statusText) + '<br/><strong>' + lang.area + ': </strong>';
			}
		}		
		//tooltip += '<h3>'+maptool.map.positions[i].name + ' </h3><p><strong>' + lang.status + ': </strong>' + lang.StatusText(maptool.map.positions[i].statusText) + '<br/><strong>' + lang.area + ': </strong> ';
		//Tooltip content
		if (maptool.map.positions[i].area != '') {
			tooltip += maptool.map.positions[i].area + '<br/>';
		} else {
			tooltip += lang.info_missing + '<br>';
		}

		if (hasRights) {
				tooltip += '<strong>' + lang.price + ': </strong>' + maptool.map.positions[i].price + ' ' + maptool.map.currency + '</p>';
		} else {
			if (maptool.map.positions[i].status == 0 && maptool.map.userlevel > 0) {
				if (maptool.map.positions[i].price != 0) {
					tooltip += '<strong>' + lang.price + ': </strong>' + maptool.map.positions[i].price + ' ' + maptool.map.currency + '</p>';
				} else {
					tooltip += '<strong>' + lang.price + ': </strong>' + lang.info_missing + '</p>';
				}
			}
		}

		if (maptool.map.positions[i].status > 0 && maptool.map.positions[i].exhibitor && maptool.map.positions[i].status != 2) { 
			tooltip += '<p><strong>' + lang.StatusText(maptool.map.positions[i].statusText).charAt(0).toUpperCase() + lang.StatusText(maptool.map.positions[i].statusText).substr(1) + ' ' + lang.by + ': </strong>' + maptool.map.positions[i].exhibitor.company + '</p>';
			if (maptool.map.positions[i].status == 1) {
				tooltip += '<p><strong>' + lang.reservedUntil + ': </strong>' + maptool.map.positions[i].expires + '</p>';
			}
			tooltip += '<p><strong>' + lang.commodity_label + ': </strong>';
			tooltip += maptool.map.positions[i].exhibitor.commodity + '</p>';
		} else if (maptool.map.positions[i].status == 2) {
			tooltip += '<p><strong>' + lang.bookedBy + ': </strong>' + maptool.map.positions[i].exhibitor.company + '</p>';
			tooltip += '<p><strong>' + lang.commodity_label + ': </strong>';
			if (maptool.map.positions[i].exhibitor.commodity != '') {
				tooltip += maptool.map.positions[i].exhibitor.commodity + '</p>';
			} else {
				tooltip += lang.no_commodity + '</p>';
			}
		} else {
			var info =  maptool.map.positions[i].information;
			info = info.substr(0, 100);
			if(info.length == 100){
				info += "...";			
			}
			tooltip+= '<p id="tooltip_assortment">';
			tooltip+=info;
			tooltip+='</p>';

			if (maptool.map.userlevel > 0) {
				tooltip += '<p style="margin-top: 0.25em;"><strong>' + lang.clickToReserveStandSpace + '</strong></p>';
			} 
			freeSpots++;
		}

		if(maptool.map.userlevel == 0){
			tooltip += '<p><strong>' + lang.loginToViewMoreInfo + '</strong></p>';
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

		if (maptool.map.positions[i].being_edited > 0 && maptool.map.positions[i].being_edited != maptool.map.user_id) {
			marker.attr('src', 'images/icons/marker_busy.png').addClass('busy');
		}
		if (maptool.map.positions[i].exhibitor && hasRights) {
			if (maptool.map.positions[i].exhibitor.clone > 0) {
				marker.attr('src', 'images/icons/Reserverad-gray.png');
			}
		}
		// Add HTML to blob.
		markerHTML += marker[0].outerHTML;
		tooltipHTML += tooltip;
	}	
	$("#mapHolder #map").prepend(markerHTML);
	$("#mapHolder").prepend(tooltipHTML);


	//Display tooltip on hover
	$(".marker", mapContext).hover(function(e) {

		var tooltip = $("#info-" + $(this).attr("id").replace("pos-", ""));
		var marker = $(this);
		// Fix tooltip when too close to map canvas margin
		if (!tooltip.is(":visible")) {

			// Upper margin
			if ((tooltip.height() > marker.offset().top/1.2) && (tooltip.width() < marker.offset().left*2)) {
				tooltip.addClass('marker_tooltip_flipped'); 
				tooltip.css({
					left: marker.offset().left,
					top: marker.offset().top + 20 - config.positionTopOffset
				});
				if(jQuery.browser.mobile){
					tooltip.css({
						left: marker.offset().left,
						top: marker.offset().top + 20 - config.positionTopOffset
					});
				}
			}
			// Upper left margin
			else if ((tooltip.width() > marker.offset().left*2) && (tooltip.height() > marker.offset().top)){
				tooltip.addClass('marker_tooltip_flipped');
				tooltip.css({
					left: marker.offset().left + tooltip.width()/2,
					top: marker.offset().top + 15 - config.positionTopOffset
				});
				if(jQuery.browser.mobile){
					tooltip.css({
						left: marker.offset().left + tooltip.width()/2,
						top: marker.offset().top + 15 - config.positionTopOffset
					});
				}
			}
			

			// Left lower margin & left margin
			else if ((tooltip.width() > marker.offset().left*2) && (tooltip.height() < marker.offset().top)){
				tooltip.addClass('marker_tooltip_flipped');
				tooltip.css({
					left: marker.offset().left + tooltip.width()/2 + 30,
					top: marker.offset().top - tooltip.height() - 15 - config.positionTopOffset
				});
				if(jQuery.browser.mobile){
					tooltip.css({
						left: marker.offset().left + tooltip.width()/2 + 30,
						top: marker.offset().top - tooltip.height() - 15 - config.positionTopOffset
					});
				}
			}
			// Lower margin
			else if ((tooltip.height() < marker.offset().top) ) {
				tooltip.removeClass('marker_tooltip_flipped');
				tooltip.css({
					left: marker.offset().left,
					top: marker.offset().top - tooltip.height() - 20 - config.positionTopOffset
				});
				if(jQuery.browser.mobile){
					tooltip.css({
						left: marker.offset().left + 70,
						top: marker.offset().top - tooltip.height() - 20 - config.positionTopOffset
					});
				}
			}
			
			tooltip.css('display', 'inline');

			// Right lower margin & right margin
			if ((tooltip.offset().left + 300) > $('#mapHolder').width() && tooltip.height() < marker.offset().top) {
				tooltip.css({
					left: marker.offset().left - tooltip.width() + 100,
					top: marker.offset().top - tooltip.height() + 50 - config.positionTopOffset
				});
				if(jQuery.browser.mobile){
					tooltip.css({
						left: marker.offset().left - tooltip.width() + 120,
						top: marker.offset().top - tooltip.height() + 50 - config.positionTopOffset
					});
				}
			}
			// Upper right margin
			else if ((marker.offset().left + 300) > $('#mapHolder').width() && tooltip.height() > marker.offset().top) {
				tooltip.addClass('marker_tooltip_flipped');
				tooltip.css({
					left: marker.offset().left - tooltip.width()/2,
					top: marker.offset().top + 15 - config.positionTopOffset
				});
				if(jQuery.browser.mobile){
					tooltip.css({
						left: marker.offset().left - tooltip.width()/2,
						top: marker.offset().top + 15 - config.positionTopOffset
					});
				}
			}
/*			Kod för att ta reda på avstånd osv.
			var leftinfo = tooltip.offset().left;
			var mapholderinfo = $('#mapHolder').width();
			console.log(mapholderinfo);
			console.log(leftinfo);
			console.log(marker.offset().left + 300);
			console.log('mapHolder width: ' + $('#mapHolder').width());
			console.log('Tooltip height: ' + tooltip.height());
			console.log('Marker offset top: ' + marker.offset().top);			
*/

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
	if (jQuery.browser.mobile) {
		$(".marker", mapContext).bind("touch doubletap", function() {
			maptool.showContextMenu($(this).attr("id").replace('pos-', ''), $(this));
		});
	} else {
		// Display dialogue on marker click (or touch, for iDevices)
		$(".marker", mapContext).bind("click", function() {
			maptool.showContextMenu($(this).attr("id").replace('pos-', ''), $(this));
		});
	}

	maptool.placeFocusArrow();
	

	$('#spots_free').text(freeSpots);
	
	for (var i=0; i<maptool.map.positions.length; i++) {
		var markerId = "pos-"+maptool.map.positions[i].id;
		var markerImg = document.getElementById(markerId);
		if (categoryFilter > 0) {
			if(maptool.map.positions[i].exhibitor != null){
				if(maptool.map.positions[i].exhibitor.categories.length > 0){
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

maptool.updateBusyStatus = function(position_id, callback) {
	$.ajax({
		url: 'ajax/maptool.php',
		method: 'GET',
		data: 'getBusyStatus=' + position_id,
		success: function(response) {
			var marker = $('#pos-' + position_id);
			var busy;

			if (maptool.map.userlevel > 0) {
				if (response.being_edited > 0 && response.being_edited != maptool.map.user_id) {
					marker.attr('src', 'images/icons/marker_busy.png').addClass('busy');
					busy = true;
				} else if (response.exhibitor) {
					if (response.exhibitor.clone > 0) {
						marker.attr('src', 'images/icons/Reserverad-gray.png');
					} else {					
						marker.attr('src', 'images/icons/marker_' + response.statusText + '.png').removeClass('busy');
						busy = false;
					}
				}
			} else {
				busy = false;
			}
			callback(busy);
		}
	});
};

//Create context menu for markers
maptool.showContextMenu = function(position, marker) {
	maptool.updateBusyStatus(position, function(is_busy) {
		if (is_busy)
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
			if (copiedExhibitor || copiedFairRegistration) {
				contextMenu.append('<li id="cm_paste">' + lang.pasteExhibitor + '</li>');
			}
		} else if (maptool.map.positions[objIndex].status == 0 && maptool.map.userlevel == 1 && !maptool.map.positions[objIndex].applied && maptool.ownsMap()) {
			contextMenu.append('<li id="cm_apply">' + lang.preliminaryBookStandSpace + '</li>');
		} else if (maptool.map.positions[objIndex].status == 0 && maptool.map.userlevel == 1 && maptool.map.positions[objIndex].applied && maptool.ownsMap()) {
			contextMenu.append('<li id="cm_cancel">' + lang.cancelPreliminaryBooking + '</li>');
		}
		
		if (maptool.map.userlevel > 1 && hasRights && maptool.ownsMap()) {
			contextMenu.append('<li id="cm_edit">' + lang.editStandSpace + '</li><li id="cm_move">' + lang.moveStandSpace + '</li><li id="cm_delete">' + lang.deleteStandSpace + '</li>');
		}
		
		if(((maptool.map.userlevel == 2 && hasRights) || maptool.map.userlevel > 2) && maptool.map.positions[objIndex].status > 0){
			contextMenu.append('<li id="cm_note">' + lang.notes + '</li>');
		}

		if ((maptool.map.positions[objIndex].applied) && maptool.map.userlevel == 1){
			contextMenu.append('<li id="cm_more">' + lang.viewBooking + '</li>');
		} else {
			contextMenu.append('<li id="cm_more">' + lang.moreInfo + '</li>');
		}

		if (maptool.map.positions[objIndex].status > 0 && maptool.map.userlevel > 1 && hasRights && maptool.ownsMap()) {
			contextMenu.append('<li id="cm_edit_booking">' + lang.editBooking + '</li>');
			contextMenu.append('<li id="cm_cancel_booking">' + lang.cancelBooking + '</li>');
			if (maptool.map.positions[objIndex].status == 1) {
				contextMenu.append('<li id="cm_book">' + lang.bookStandSpace + '</li>');
			} else if (maptool.map.positions[objIndex].status == 2) {
				contextMenu.append('<li id="cm_reserve">' + lang.reserveStandSpace + '</li>');
			}
		} else if (maptool.map.positions[objIndex].applied > 0 && maptool.map.userlevel > 1 && hasRights) {
			contextMenu.append('<li id="cm_show_preliminary_bookings">' + lang.showPreliminaryBookings + '</li>');
		}

		if ($("li", contextMenu).length > 0) {
			

			contextMenu.css({
				left: $("#pos-" + position).offset().left + config.iconOffset,
				top: $("#pos-" + position).offset().top + config.iconOffset + 10
			}).show();

		
			//click handlers for context menu
			if(maptool.map.userlevel > 0 && (maptool.map.userlevel == 1 || (hasRights || maptool.map.userlevel == 4))) {
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
					} else if(e.target.id == 'cm_show_preliminary_bookings') {
						maptool.showPreliminaryBookings(maptool.map.positions[objIndex]);
					}
				}); 		
			} else if (maptool.map.positions[objIndex].status == 0 && maptool.map.userlevel == 0) {
				return;
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
	});
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
	if (copiedExhibitor) {
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'pasteExhibitor=' + positionObject.id,
			success: function(res) {
				copiedExhibitor = null;
				maptool.reload();
			}
		});

	} else {
		window.pasteOnPosition = positionObject;
		maptool.openDialogue('fair_registration_paste_type_dialogue');
	}
}

maptool.pasteFairRegistration = function(e) {
	e.preventDefault();

	$('#fair_registration_paste_type_dialogue').hide();
	maptool.markPositionAsBeingEdited(window.pasteOnPosition);

	var type = $('#paste_fair_registration_type').val();
	var prefix = '';
	var categories = copiedFairRegistration.categories.split('|');
	var options = copiedFairRegistration.options.split('|');
	var articles = copiedFairRegistration.articles.split('|');
	var artamount = copiedFairRegistration.amount.split('|');

	$('.standSpaceName').html("");
	if (type == 0) {
		prefix = 'book';
		dialogue = '#book_position_form ';
		$('#book_position_form .standSpaceName').text(lang.bookStandSpace + ': ' + pasteOnPosition.name);

	} else if (type == 1) {
		prefix = 'reserve';
		dialogue = '#reserve_position_form ';
		$('#reserve_position_form .standSpaceName').text(lang.reserveStandSpace + ': ' + pasteOnPosition.name);
	}
	$('.ssinfo').html("");
	$('.ssinfo').html('<label>' + lang.area +  ': </label><p>' + pasteOnPosition.area + '</p><br/><label>' + lang.price +  ': </label><p>' + pasteOnPosition.price + ' ' + maptool.map.currency + '</p><br/><label>' + lang.info + ': </label><p>' + pasteOnPosition.information) + '</p>';
	$('#' + prefix + '_category_input').css('border-color', '#666');
	$('#' + prefix + '_category_scrollbox').css('border-color', '#000000');
	$('#' + prefix + '_category_scrollbox > tbody > tr > td > input').prop('checked', false);
	$('#' + prefix + '_option_scrollbox > tbody > tr > td > input').prop('checked', false);
	$('#' + prefix + '_article_scrollbox > tbody > tr > td > div > input').val(0);

	$.each(categories, function(index, category) {
		$('#' + prefix + '_category_scrollbox > tbody > tr > td > input[value=' + category + ']').prop('checked', true);

	});

	if (options != "") {
		$.each(options, function(index, option) {
			$('#' + prefix + '_option_scrollbox > tbody > tr > td > input[value=' + option + ']').prop('checked', true);
		});
	}

	for (var i = 0; i < articles.length; i++){
				
		//var oInput = document.getElementById(articles[i]);
		
			$('#' + prefix + '_article_scrollbox > tbody > tr > td > div').each(function() {
				if($(this).children().attr('id') == articles[i]) {
					$(this).children().val(artamount[i]);
				}
		});
	}		

	$('#' + prefix + '_commodity_input').val(copiedFairRegistration.commodity);
	$('#' + prefix + '_message_input').val(copiedFairRegistration.arranger_message);
	$('#' + prefix + '_user_input').val(copiedFairRegistration.user);

	maptool.openForm(prefix + '_position_form');
	positionDialogue(prefix + '_position_form');

	$('#' + prefix + '_review').click(function(e) {
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

		

		$('#' + prefix + '_category_scrollbox > tbody > tr > td').each(function(){
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

		$('#' + prefix + '_option_scrollbox > tbody > tr > td').each(function(){
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

		$('#' + prefix + '_article_scrollbox > tbody > tr > td > div').each(function(){
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
		html += '<tr style="height:1em"></tr>;<tr><td></td><td class="left"><b>' + lang.space + '</b></td><td></td><td></td></tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + pasteOnPosition.name + '</td>';
			html += '<td class="left price">' + pasteOnPosition.price + '</td>';
			html += '<td class="amount">1</td>';
			if (pasteOnPosition.vat) {
				html += '<td class="moms">' + pasteOnPosition.vat + '%</td>';
			} else {
				html += '<td class="moms">0%</td>';
			}
			html += '<td class="total">' + parseFloat(pasteOnPosition.price).toFixed(2) + '</td>';
		html += '</tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + pasteOnPosition.information + '</td>';
			html += '<td class="left price"></td>';
			html += '<td class="amount"></td>';
			html += '<td class="moms"></td>';
			html += '<td class="total"></td>';
		html += '</tr>';
		if (pasteOnPosition.price) {
			if (parseFloat(pasteOnPosition.vat) == 25) {
				excludeVatPrice25 += parseFloat(pasteOnPosition.price);
			} else if (parseFloat(pasteOnPosition.vat) == 18) {
				excludeVatPrice18 += parseFloat(pasteOnPosition.price);
			} else {
				excludeVatPrice0 += parseFloat(pasteOnPosition.price);
			}
		}

		if (optname != "") {
			html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.options + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.articles + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr>';
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

		html2 += '<tr style="height:1em">';					
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
			html2 += '<td class="totalprice">' + maptool.map.currency + ' ' + lang.to_pay + '&nbsp;&nbsp;' + parseFloat(totalPriceRounded).toFixed(2) + '</td>';
		html2 += '</tr>';

			$(dialogue + '#review_list').append(html);
			$(dialogue + '#review_list2').append(html2);

	$(dialogue + '#review_commodity_input').html("");
	$(dialogue + '#review_commodity_input').append($("#" + prefix + "_commodity_input").val());
		if($(dialogue + '#review_commodity_input').html().length == 0) {
			$(dialogue + '#review_commodity_input').append(lang.no_commodity);
		}	
	$(dialogue + '#review_message').html("");
	$(dialogue + '#review_message').append($("#" + prefix + "_message_input").val());
		if($(dialogue + '#review_message').html().length == 0) {
			$(dialogue + '#review_message').append(lang.no_message);
		}
		$(dialogue + '#review_user').html("");

		$(dialogue + '#review_user').append($('#' + prefix + '_user_input').find(":selected").text());

	});

	$('#' + prefix + '_post').click(function(e) {
		e.preventDefault();
		var cats = [];
		var options = [];
		var articles = [];
		var artamount = [];
		var count = 0;

		$('#' + prefix + '_category_scrollbox > tbody > tr > td').each(function(){
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

		count = 0;

		$('#' + prefix + '_option_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				options[count] = val;
				count = count+1;
			}
		});

		var optStr = '';


		for (var j=0; j<options.length; j++) {
			if(options[j] != undefined){
				optStr += '&options[]=' + options[j];
			}
		}

		count = 0;

		$('#' + prefix + '_article_scrollbox > tbody > tr > td > div').each(function() {
			var val = $(this).children().val();
			var artid = $(this).children().attr("id");
				if (val > 0) {
					articles[count] = artid;
					artamount[count] = val;
					count++;
				}
		});
		
		var artStr = '';
		var amountStr = '';

		for (var j = 0; j < articles.length; j++) {
			if (articles[j] != 0) {
				artStr += '&articles[]=' + articles[j];
				amountStr += '&artamount[]=' + artamount[j];
				
			}
		}

		var dataString = prefix + 'Position=' + window.pasteOnPosition.id
				   + '&commodity=' + encodeURIComponent($('#' + prefix + '_commodity_input').val())
				   + '&message=' + encodeURIComponent($('#' + prefix + '_message_input').val())
				   + '&map=' + maptool.map.id
				   + catStr
				   + optStr
				   + artStr
				   + amountStr
				   + '&delete_copied_fairreg=' + copiedFairRegistration.id;

		if (maptool.map.userlevel > 1) {
			dataString += '&user=' + encodeURIComponent($('#' + prefix + '_user_input').val());
		}

		if (prefix == 'reserve') {
			dataString += '&expires=' + $('#reserve_expires_input').val();
			if ($("#reserve_expires_input").val().match(/^\d\d-\d\d-\d\d\d\d \d\d:\d\d$/)) {
				var dateParts = $("#reserve_expires_input").val().split('-');
				dt = new Date(parseInt(dateParts[2], 10), parseInt(dateParts[1], 10)-1, parseInt(dateParts[0], 10));
				// Add one day, since it should be up to and including.
				dt.setDate(dt.getDate(+1));
				if (dt < new Date()) {
					$("#reserve_expires_input").css('border-color', 'red');
					return;
				}
			} else {
				$("#reserve_expires_input").css('border-color', 'red');
				return;
			}				
		}

		
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: dataString,
			success: function(response) {
				maptool.markPositionAsNotBeingEdited();
				maptool.update();
				maptool.closeDialogues();
				maptool.closeForms();
				window.pasteOnPosition = null;
				copiedFairRegistration = null;

				$('#' + prefix + '_position_form input[type="text"], #' + prefix + '_position_form textarea').val('');
			}
		});

	});
};

//Create new position
maptool.addPosition = function(clickEvent) {
	$("#position_name_input, #position_area_input, #position_price_input, #position_info_input").val("");

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
			$('#edit_position_dialogue .standSpaceName').text(lang.newStandSpace);
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

	$(document).one('keyup', function(e) {
		if (e.keyCode === 27) {
			maptool.endMovePosition();
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
					}
				});
				movingMarker.remove();
				$(document).off('mousemove', 'body', maptool.traceMouse);
			}
			movingMarker = null;	
		}
	});
};

// End moving position
maptool.endMovePosition = function() {
	movingMarker.remove();
	movingMarker = null;
	maptool.resumeUpdate();
	$(document).off('mousemove', 'body', maptool.traceMouse);
};

//Edit position
maptool.editPosition = function(positionObject) {
	//$("#edit_position_dialogue .closeDialogue").show();
	$("#post_position").off("click");

	$("#position_id_input").val(positionObject.id);
	$("#position_name_input").val(positionObject.name);
	$("#position_area_input").val(positionObject.area);
	$("#position_price_input").val(positionObject.price);
	$("#position_info_input").val(positionObject.information);
	$('.standSpaceName').html("");
	$('#edit_position_dialogue .standSpaceName').text(lang.editStandSpace + ': ' + positionObject.name);
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

maptool.bookPosition = function(positionObject) {
		dialogue = '#book_position_form ';
		var sel = $('#book_user_input');
		var opts_list = sel.find('option');
		opts_list.sort(function(a, b) { return $(a).text().toLowerCase() > $(b).text().toLowerCase() ? 1 : -1; });
		sel.html(opts_list);
		$('#book_category_scrollbox').css('border-color', '#000000');
		$('#book_category_scrollbox > tbody > tr > td > input').prop('checked', false);
		$('#book_option_scrollbox > tbody > tr > td > input').prop('checked', false);
		$('#book_article_scrollbox > tbody > tr > td > div > input').val(0);
		$("#book_commodity_input, #book_message_input").val("");

	if (maptool.map.userlevel < 2) {
		$('#book_user_input, label[for="book_user_input"]').hide();
	}

	if (positionObject.status < 2 && positionObject.exhibitor) {
		$("#book_commodity_input").val(positionObject.exhibitor.commodity);
		$("#book_message_input").val(positionObject.exhibitor.arranger_message);
		$('#book_user_input option[value="' + positionObject.exhibitor.user + '"]').prop("selected", true);

		var categories = positionObject.exhibitor.categories, 
			options = positionObject.exhibitor.options, 
			articles = positionObject.exhibitor.articles, 
			amount = positionObject.exhibitor.amount, 
			i;

	// Categories
		for(i = 0; i < categories.length; i++){
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
		for(i = 0; i < options.length; i++){
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
			if($(this).children().attr('id') == articles[i].article_id) {
				$(this).children().val(articles[i].amount);
			}
		});
	}

	} else {
		$('#book_category_scrollbox').css('border-color', '#000000');
		$('#book_category_scrollbox > tbody > tr > td > input').prop('checked', false);
		$('#book_option_scrollbox > tbody > tr > td > input').prop('checked', false);
		$('#book_article_scrollbox > tbody > tr > td > div > input').val(0);
		$("#book_commodity_input, #book_message_input").val("");
	}

	maptool.openForm('book_position_form');
	positionDialogue('book_position_form');
	$('#book_position_form ul#progressbar li').removeClass('active');
	$('#book_position_form fieldset').css({
		'transform': 'scale(1)',
		'display': 'none',
		'opacity': '0',
	});				
	$('#book_position_form ul#progressbar li:first-child').attr('class', 'active');
	$('#book_position_form fieldset:first-of-type').css({
		'transform': 'scale(1)',
		'display': 'block',
		'opacity': '1',
	});
	$('.standSpaceName').html("");
	$('#book_position_form .standSpaceName').text(lang.bookStandSpace + ': ' + positionObject.name);
	$('.ssinfo').html("");
	$('.ssinfo').html('<label>' + lang.area +  ': </label><p>' + positionObject.area + '</p><br/><label>' + lang.price +  ': </label><p>' + positionObject.price + ' ' + maptool.map.currency + '</p><br/><label>' + lang.info + ': </label><p>' + positionObject.information) + '</p>';

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

	$('#book_position_form > fieldset > div > #search_user_input').unbind('keyup');
	$('#book_position_form > fieldset > div > #search_user_input').val('');
	$('#book_position_form > fieldset > div > #search_user_input').keyup(function(e) {
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
		html += '<tr style="height:1em"></tr>;<tr><td></td><td class="left"><b>' + lang.space + '</b></td><td></td><td></td></tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + positionObject.name + '</td>';
			html += '<td class="left price">' + positionObject.price + '</td>';
			html += '<td class="amount">1</td>';
			if (positionObject.vat) {
				html += '<td class="moms">' + positionObject.vat + '%</td>';
			} else {
				html += '<td class="moms">0%</td>';
			}
			html += '<td class="total">' + parseFloat(positionObject.price).toFixed(2) + '</td>';
		html += '</tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + positionObject.information + '</td>';
			html += '<td class="left price"></td>';
			html += '<td class="amount"></td>';
			html += '<td class="moms"></td>';
			html += '<td class="total"></td>';
		html += '</tr>';		

		if (positionObject.price) {
			if (parseFloat(positionObject.vat) == 25) {
				excludeVatPrice25 += parseFloat(positionObject.price);
			} else if (parseFloat(positionObject.vat) == 18) {
				excludeVatPrice18 += parseFloat(positionObject.price);
			} else {
				excludeVatPrice0 += parseFloat(positionObject.price);
			}
		}

		if (optname != "") {
			html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.options + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.articles + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr>';
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

		html2 += '<tr style="height:1em">';					
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
			html2 += '<td class="totalprice">' + maptool.map.currency + ' ' + lang.to_pay + '&nbsp;&nbsp;' + parseFloat(totalPriceRounded).toFixed(2) + '</td>';
		html2 += '</tr>';

			$(dialogue + '#review_list').append(html);
			$(dialogue + '#review_list2').append(html2);

	$(dialogue + '#review_commodity_input').html("");
	$(dialogue + '#review_commodity_input').append($("#book_commodity_input").val());
		if($(dialogue + '#review_commodity_input').html().length == 0) {
			$(dialogue + '#review_commodity_input').append(lang.no_commodity);
		}	
	$(dialogue + '#review_message').html("");
	$(dialogue + '#review_message').append($("#book_message_input").val());
		if($(dialogue + '#review_message').html().length == 0) {
			$(dialogue + '#review_message').append(lang.no_message);
		}
		$(dialogue + '#review_user').html("");

		$(dialogue + '#review_user').append($('#book_user_input').find(":selected").text());

	});

	$('#book_post').unbind('keyup');
	$('#book_post').unbind('keydown');
	$('#book_post').unbind('click');
	$('#book_position_form').on('keyup keypress', function(e) {
	  var code = e.keyCode || e.which;
	  if (code == 13) { 
	    e.preventDefault();
	    return false;
	  }
	});

	$("#book_post").click(function(e) {
		e.preventDefault();
		var cats = [];
		var options = [];
		var articles = [];
		var artamount = [];
		var count = 0;

		$('#book_category_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				cats[count] = val;
				count = count+1;
			}
		});

		if (count == 0) {
			$('#book_category_scrollbox').css('border', '0.166em solid #f00');
			return;
		}

		var catStr = '';

		for (var j=0; j<cats.length; j++) {
			if(cats[j] != undefined){
				catStr += '&categories[]=' + cats[j];
			}
		}

		count = 0;

		$('#book_option_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				options[count] = val;
				count = count+1;
			}
		});

		var optStr = '';


		for (var j=0; j<options.length; j++) {
			if(options[j] != undefined){
				optStr += '&options[]=' + options[j];
			}
		}

		count = 0;

		$('#book_article_scrollbox > tbody > tr > td > div').each(function() {
			var val = $(this).children().val();
			var artid = $(this).children().attr("id");
				if (val > 0) {
					articles[count] = artid;
					artamount[count] = val;
					count++;
				}
		});
		
		var artStr = '';
		var amountStr = '';

		for (var j = 0; j < articles.length; j++) {
			if (articles[j] != 0) {
				artStr += '&articles[]=' + articles[j];
				amountStr += '&artamount[]=' + artamount[j];
				
			}
		}
		
		var dataString = 'bookPosition=' + positionObject.id
				   + '&commodity=' + encodeURIComponent($("#book_commodity_input").val())
				   + '&message=' + encodeURIComponent($("#book_message_input").val())
				   + '&map=' + maptool.map.id
				   + catStr
				   + optStr
				   + artStr
				   + amountStr;

		if (maptool.map.userlevel > 1) {
			dataString += '&user=' + encodeURIComponent($("#book_user_input").val());
		}

		if (positionObject.exhibitor && positionObject.exhibitor.preliminary_booking) {
			dataString += '&prel_booking=' + positionObject.exhibitor.preliminary_booking;
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
					maptool.closeForms();
					$('#book_position_form input[type="text"], #book_position_form textarea').val("");
					$('.ssinfo').html('');
				}
			});
		} else {
			$('#book_category_scrollbox_div').css('border', '0.166em solid #f00');
		}
	});
}

maptool.applyForFair = function() {
	dialogue = '#fair_registration_form ';
	$('#registration_category_input').css('border', '1px solid #666');
	$('#registration_category_input, #registration_commodity_input').css('border-color', '#B09D9D');
	$('#fair_registration_form textarea, #fair_registration_form select').val("");
	$('.standSpaceName').html("");
	$('.standSpaceName').text(lang.applyForFair);

	maptool.openForm('fair_registration_form');
	positionDialogue('fair_registration_form');
	$('#fair_registration_form ul#progressbar li').removeClass('active');
	$('#fair_registration_form fieldset').css({
		'transform': 'scale(1)',
		'display': 'none',
		'opacity': '0',
	});				
	$('#fair_registration_form ul#progressbar li:first-child').attr('class', 'active');
	$('#fair_registration_form fieldset:first-of-type').css({
		'transform': 'scale(1)',
		'display': 'block',
		'opacity': '1',
	});	
	$('#registration_commodity_input').change(function() {
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'getUserCommodity=1&userId=' + maptool.map.user_id,
			success: function(response) {
				if (response) {
					r = JSON.parse(response);
					$('#registration_commodity_input').val(r.commodity);
				}
			}
		});
	});
	
	$('#registration_commodity_input').change();
	$('#registration_commodity_input').unbind('change');
	$('#registration_confirm').unbind('click');

	$('#fair_registration_form').on('keyup keypress', function(e) {
	  var code = e.keyCode || e.which;
	  if (code == 13) { 
	    e.preventDefault();
	    return false;
	  }
	});	

	$('#registration_review').click(function(e) {
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

		

		$('#registration_category_scrollbox > tbody > tr > td').each(function(){
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

		$('#registration_option_scrollbox > tbody > tr > td').each(function(){
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

		$('#registration_article_scrollbox > tbody > tr > td > div').each(function(){
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

		if (optname != "") {
			html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.options + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.articles + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr>';
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

		html2 += '<tr style="height:1em">';					
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
			html2 += '<td class="totalprice">' + lang.preliminary_amount + '&nbsp;&nbsp;' + parseFloat(totalPriceRounded).toFixed(2) + '</td>';
		html2 += '</tr>';

		html2 += '<tr>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td></td>';
			html2 += '<td class="preliminary_totalprice">' + lang.amount_no_standspace + '</td>';
		html2 += '</tr>';
			$(dialogue + '#review_list').append(html);
			$(dialogue + '#review_list2').append(html2);

	$(dialogue + '#review_commodity_input').html("");
	$(dialogue + '#review_commodity_input').append($("#registration_commodity_input").val());
		if($(dialogue + '#review_commodity_input').html().length == 0) {
			$(dialogue + '#review_commodity_input').append(lang.no_commodity);
		}	
	$(dialogue + '#review_message').html("");
	$(dialogue + '#review_message').append($("#registration_message_input").val());
		if($(dialogue + '#review_message').html().length == 0) {
			$(dialogue + '#review_message').append(lang.no_message);
		}
		$(dialogue + '#review_registration_area').html("");
		$(dialogue + '#review_registration_area').append($("#registration_area_input").val());


	});

	$('#registration_confirm').click(function(e) {
		e.preventDefault();
		if ($("#registration_commodity_input").val() == "") {
			$('#registration_commodity_input').css('border-color', 'red');
			return;
		}
		var cats = [];
		var options = [];
		var articles = [];
		var artamount = [];
		var count = 0;

		$('#registration_category_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				cats[count] = val;
				count = count+1;
			}
		});

		if (count == 0) {
			$('#registration_category_scrollbox').css('border', '0.166em solid #f00');
			return;
		}

		var catStr = '';

		for (var j=0; j<cats.length; j++) {
			if(cats[j] != undefined){
				catStr += '&categories[]=' + cats[j];
			}
		}

		count = 0;

		$('#registration_option_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				options[count] = val;
				count = count+1;
			}
		});

		var optStr = '';

		for (var j=0; j<options.length; j++) {
			if(options[j] != undefined){
				optStr += '&options[]=' + options[j];
			}
		}

		count = 0;

		$('#registration_article_scrollbox > tbody > tr > td > div').each(function() {
			var val = $(this).children().val();
			var artid = $(this).children().attr("id");
				if (val > 0) {
					articles[count] = artid;
					artamount[count] = val;
					count++;
				}
		});

		var artStr = '';
		var amountStr = '';

		for (var j = 0; j < articles.length; j++) {
			if (articles[j] != 0) {
				artStr += '&articles[]=' + articles[j];
				amountStr += '&artamount[]=' + artamount[j];
				
			}
		}

		var dataString = 'fairRegistration='
				   + '&commodity=' + encodeURIComponent($('#registration_commodity_input').val())
				   + '&message=' + encodeURIComponent($('#registration_message_input').val())
				   + '&area=' + encodeURIComponent($('#registration_area_input').val())
				   + catStr
				   + optStr
				   + artStr
				   + amountStr;
		
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: dataString,
			success: function(response) {
				maptool.update();
				maptool.closeDialogues();
				maptool.closeForms();
				$('#fair_registration_form input[type="text"], #fair_registration_form textarea').val("");
				maptool.openDialogue("fairRegistrationConfirm");
				positionDialogue("fairRegistrationConfirm", 0);
			}
		});
	});
	
}

maptool.markForApplication = function(positionObject) {
	dialogue = '#apply_mark_form ';
	$('#apply_category_input').css('border', '1px solid #666');
	$('#apply_category_input, #apply_commodity_input').css('border-color', '#B09D9D');
	$('#apply_mark_form textarea, #apply_mark_form select').val("");
	$('.standSpaceName').html("");
	$('.standSpaceName').text(lang.preliminaryBookStandSpace + ': ' + positionObject.name);
	$('.ssinfo').html("");
	$('.ssinfo').html('<label>' + lang.area +  ': </label><p>' + positionObject.area + '</p><br/><label>' + lang.price +  ': </label><p>' + positionObject.price + ' ' + maptool.map.currency + '</p><br/><label>' + lang.info + ': </label><p>' + positionObject.information) + '</p>';

	maptool.openForm('apply_mark_form');
	positionDialogue('apply_mark_form');
	$('#apply_mark_form ul#progressbar li').removeClass('active');
	$('#apply_mark_form fieldset').css({
		'transform': 'scale(1)',
		'display': 'none',
		'opacity': '0',
	});				
	$('#apply_mark_form ul#progressbar li:first-child').attr('class', 'active');
	$('#apply_mark_form fieldset:first-of-type').css({
		'transform': 'scale(1)',
		'display': 'block',
		'opacity': '1',
	});	
	$('#apply_commodity_input').change(function() {
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'getUserCommodity=1&userId=' + maptool.map.user_id,
			success: function(response) {
				if (response) {
					r = JSON.parse(response);
					$('#apply_commodity_input').val(r.commodity);
				}
			}
		});
	});
	
	$('#apply_commodity_input').change();
	$('#apply_commodity_input').unbind('change');
	$('#apply_confirm').unbind('click');

	$('#apply_mark_form').on('keyup keypress', function(e) {
	  var code = e.keyCode || e.which;
	  if (code == 13) { 
	    e.preventDefault();
	    return false;
	  }
	});	

	$('#apply_review').click(function(e) {
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

		

		$('#apply_category_scrollbox > tbody > tr > td').each(function(){
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

		$('#apply_option_scrollbox > tbody > tr > td').each(function(){
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

		$('#apply_article_scrollbox > tbody > tr > td > div').each(function(){
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
		html += '<tr style="height:1em"></tr>;<tr><td></td><td class="left"><b>' + lang.space + '</b></td><td></td><td></td></tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + positionObject.name + '</td>';
			html += '<td class="left price">' + positionObject.price + '</td>';
			html += '<td class="amount">1</td>';
			if (positionObject.vat) {
				html += '<td class="moms">' + positionObject.vat + '%</td>';
			} else {
				html += '<td class="moms">0%</td>';
			}
			html += '<td class="total">' + parseFloat(positionObject.price).toFixed(2) + '</td>';
		html += '</tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + positionObject.information + '</td>';
			html += '<td class="left price"></td>';
			html += '<td class="amount"></td>';
			html += '<td class="moms"></td>';
			html += '<td class="total"></td>';
		html += '</tr>';				

		if (positionObject.price) {
			if (parseFloat(positionObject.vat) == 25) {
				excludeVatPrice25 += parseFloat(positionObject.price);
			} else if (parseFloat(positionObject.vat) == 18) {
				excludeVatPrice18 += parseFloat(positionObject.price);
			} else {
				excludeVatPrice0 += parseFloat(positionObject.price);
			}
		}

		if (optname != "") {
			html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.options + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.articles + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr>';
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

		html2 += '<tr style="height:1em">';					
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
			html2 += '<td class="totalprice">' + maptool.map.currency + ' ' + lang.to_pay + '&nbsp;&nbsp;' + parseFloat(totalPriceRounded).toFixed(2) + '</td>';
		html2 += '</tr>';

			$(dialogue + '#review_list').append(html);
			$(dialogue + '#review_list2').append(html2);

	$(dialogue + '#review_commodity_input').html("");
	$(dialogue + '#review_commodity_input').append($("#apply_commodity_input").val());
		if($(dialogue + '#review_commodity_input').html().length == 0) {
			$(dialogue + '#review_commodity_input').append(lang.no_commodity);
		}	
	$(dialogue + '#review_message').html("");
	$(dialogue + '#review_message').append($("#apply_message_input").val());
		if($(dialogue + '#review_message').html().length == 0) {
			$(dialogue + '#review_message').append(lang.no_message);
		}

	});

	$('#apply_confirm').click(function(e) {
		e.preventDefault();
		if ($("#apply_commodity_input").val() == "") {
			$('#apply_commodity_input').css('border-color', 'red');
			return;
		}
		var cats = [];
		var options = [];
		var articles = [];
		var artamount = [];
		var count = 0;

		$('#apply_category_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				cats[count] = val;
				count = count+1;
			}
		});

		if (count == 0) {
			$('#apply_category_scrollbox').css('border', '0.166em solid #f00');
			return;
		}

		var catStr = '';

		for (var j=0; j<cats.length; j++) {
			if(cats[j] != undefined){
				catStr += '&categories[]=' + cats[j];
			}
		}

		count = 0;

		$('#apply_option_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				options[count] = val;
				count = count+1;
			}
		});

		if (count == 0) {
			$('#apply_option_scrollbox').css('border', '0.166em solid #f00');
			return;
		}

		var optStr = '';

		for (var j=0; j<options.length; j++) {
			if(options[j] != undefined){
				optStr += '&options[]=' + options[j];
			}
		}

		count = 0;

		$('#apply_article_scrollbox > tbody > tr > td > div').each(function() {
			var val = $(this).children().val();
			var artid = $(this).children().attr("id");
				if (val > 0) {
					articles[count] = artid;
					artamount[count] = val;
					count++;
				}
		});

		var artStr = '';
		var amountStr = '';

		for (var j = 0; j < articles.length; j++) {
			if (articles[j] != 0) {
				artStr += '&articles[]=' + articles[j];
				amountStr += '&artamount[]=' + artamount[j];
				
			}
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
			positionObject.user_options = options;

			markedAsBooked.push(positionObject);
		}


		var dataString = 'preliminary=' + positionObject.id
				   + '&commodity=' + encodeURIComponent($('#apply_commodity_input').val())
				   + '&message=' + encodeURIComponent($('#apply_message_input').val())
				   + catStr
				   + optStr
				   + artStr
				   + amountStr;
		
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: dataString,
			success: function(response) {
				markedAsBooked.push(positionObject);
				maptool.update();
				maptool.closeDialogues();
				maptool.closeForms();
				$('#apply_mark_form input[type="text"], #apply_mark_form textarea').val("");
				maptool.openDialogue("preliminaryConfirm");
				positionDialogue("preliminaryConfirm", 0);
			}
		});
	});
	
}

maptool.applyForPosition = function(positionObject) {
	$('#apply_category_input').css('border', '1px solid #666');
	$('.ssinfo').html("");
	$('.ssinfo').html('<strong>' + lang.space + ' ' + positionObject.name + '<br/>' + lang.area + ': </strong>' + positionObject.area + '<br/><strong>' + lang.price + ': </strong>' + positionObject.price + '<br/><strong>' + lang.info + ': </strong>' + positionObject.information);
	
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
			$('#apply_category_scrollbox').css('border', '0.166em solid #f00');
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


	$('.standSpaceName').html("");
	if (positionObject.status == 2 || positionObject.status == 0) {
		//booked
		var prefix = 'book';
		dialogue = '#book_position_form ';
		$('#' + prefix + '_position_form .standSpaceName').text(lang.editBookedStandSpace + ': ' + positionObject.name);	
	} else if (positionObject.status == 1) {
		//reserved
		var prefix = 'reserve';
		dialogue = '#reserve_position_form ';
		$('#' + prefix + '_position_form .standSpaceName').text(lang.editReservedStandSpace + ': ' + positionObject.name);			
		$('#' + prefix + '_expires_input').val(positionObject.expires);
	}

	$('#' + prefix + '_category_input').css('border', '1px solid #666');
	$('.ssinfo').html("");
	$('.ssinfo').html('<label>' + lang.area +  ': </label><p>' + positionObject.area + '</p><br/><label>' + lang.price +  ': </label><p>' + positionObject.price + ' ' + maptool.map.currency + '</p><br/><label>' + lang.info + ': </label><p>' + positionObject.information) + '</p>';
	
	var categories = positionObject.exhibitor.categories;
	var options = positionObject.exhibitor.options;
	var articles = positionObject.exhibitor.articles;

	$('#' + prefix + '_category_scrollbox > tbody > tr > td > input').prop('checked', false);
	$('#' + prefix + '_option_scrollbox > tbody > tr > td > input').prop('checked', false);
	$('#' + prefix + '_article_scrollbox > tbody > tr > td > div > input').val(0);
// Categories
	for(var i = 0; i < categories.length; i++){
		$('#' + prefix + '_category_scrollbox > tbody > tr > td').each(function(){
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
		$('#' + prefix + '_option_scrollbox > tbody > tr > td').each(function(){
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
		$('#' + prefix + '_article_scrollbox > tbody > tr > td > div').each(function() {
			if($(this).children().attr('id') == articles[i].article_id) {
				$(this).children().val(articles[i].amount);
			}
		});
	}

// Get commodity from user input
	$('#' + prefix + '_user_input').unbind('change');
	$('#' + prefix + '_user_input').change(function() {
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'getUserCommodity=1&userId=' + $('#' + prefix + '_user_input').val(),
			success: function(response) {
				if (response) {
					r = JSON.parse(response);
					$('#' + prefix + '_commodity_input').val(r.commodity);
				}
			}
		});
	});

// Search for user	
	$('#' + prefix + '_position_form > fieldset > div > #search_user_input').unbind('keyup');
	$('#' + prefix + '_position_form > fieldset > div > #search_user_input').val('');
	$('#' + prefix + '_position_form > fieldset > div > #search_user_input').keyup(function(e) {
		if (e.keyCode == 13) {
			$('#' + prefix + '_user_input').change();
		} else {
			var query = $(this).val().toLowerCase();
			var selectedFirst = false;
			if (query == "") {
				$('#' + prefix + '_user_input > option').show();
			} else {
				$('#' + prefix + '_user_input > option').each(function() {
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
	

	maptool.openForm(prefix + '_position_form');
	positionDialogue(prefix + '_position_form');
	$('#' + prefix + '_position_form ul#progressbar li').removeClass('active');
	$('#' + prefix + '_position_form fieldset').css({
		'transform': 'scale(1)',
		'display': 'none',
		'opacity': '0',
	});				
	$('#' + prefix + '_position_form ul#progressbar li:first-child').attr('class', 'active');
	$('#' + prefix + '_position_form fieldset:first-of-type').css({
		'transform': 'scale(1)',
		'display': 'block',
		'opacity': '1',
	});	
	$('#' + prefix + '_commodity_input').val(positionObject.exhibitor.commodity);
	$('#' + prefix + '_message_input').val(positionObject.exhibitor.arranger_message);
	$('#' + prefix + '_user_input option[value="' + positionObject.exhibitor.user + '"]').prop('selected', true);


	$("#" + prefix + "_post").unbind("click");
	$('#' + prefix + '_position_form').on('keyup keypress', function(e) {
	  var code = e.keyCode || e.which;
	  if (code == 13) { 
	    e.preventDefault();
	    return false;
	  }
	});	
	$('#' + prefix + '_review').click(function(e) {
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

		

		$('#' + prefix + '_category_scrollbox > tbody > tr > td').each(function(){
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

		$('#' + prefix + '_option_scrollbox > tbody > tr > td').each(function(){
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

		$('#' + prefix + '_article_scrollbox > tbody > tr > td > div').each(function(){
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
		html += '<tr style="height:1em"></tr>;<tr><td></td><td class="left"><b>' + lang.space + '</b></td><td></td><td></td></tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + positionObject.name + '</td>';
			html += '<td class="left price">' + positionObject.price + '</td>';
			html += '<td class="amount">1</td>';
			if (positionObject.vat) {
				html += '<td class="moms">' + positionObject.vat + '%</td>';
			} else {
				html += '<td class="moms">0%</td>';
			}
			html += '<td class="total">' + parseFloat(positionObject.price).toFixed(2) + '</td>';
		html += '</tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + positionObject.information + '</td>';
			html += '<td class="left price"></td>';
			html += '<td class="amount"></td>';
			html += '<td class="moms"></td>';
			html += '<td class="total"></td>';
		html += '</tr>';		
		if (positionObject.price) {
			if (parseFloat(positionObject.vat) == 25) {
				excludeVatPrice25 += parseFloat(positionObject.price);
			} else if (parseFloat(positionObject.vat) == 18) {
				excludeVatPrice18 += parseFloat(positionObject.price);
			} else {
				excludeVatPrice0 += parseFloat(positionObject.price);
			}
		}

		if (optname != "") {
			html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.options + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.articles + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr>';
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

		html2 += '<tr style="height:1em">';					
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
			html2 += '<td class="totalprice">' + maptool.map.currency + ' ' + lang.to_pay + '&nbsp;&nbsp;' + parseFloat(totalPriceRounded).toFixed(2) + '</td>';
		html2 += '</tr>';

			$(dialogue + '#review_list').append(html);
			$(dialogue + '#review_list2').append(html2);

	$(dialogue + '#review_commodity_input').html("");
	$(dialogue + '#review_commodity_input').append($(dialogue + '#' + prefix + '_commodity_input').val());
		if($(dialogue + '#review_commodity_input').html().length == 0) {
			$(dialogue + '#review_commodity_input').append(lang.no_commodity);
		}	
	$(dialogue + '#review_message').html("");
	$(dialogue + '#review_message').append($(dialogue + '#' + prefix + '_message_input').val());
		if($(dialogue + '#review_message').html().length == 0) {
			$(dialogue + '#review_message').append(lang.no_message);
		}
		$(dialogue + '#review_user').html("");

		$(dialogue + '#review_user').append($('#' + prefix + '_user_input').find(":selected").text());

	});
	$("#" + prefix + "_post").click(function(e) {
		e.preventDefault();
		var cats = [];
		var options = [];
		var articles = [];
		var artamount = [];
		var count = 0;


		$('#' + prefix + '_category_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				cats[count] = val;
				count = count+1;
			}
		});

		if (count == 0) {
			$('#' + prefix + '_category_scrollbox').css('border', '0.166em solid #f00');
			return;
		}

		var catStr = '';

		for (var j=0; j<cats.length; j++) {
			if(cats[j] != undefined){
				catStr += '&categories[]=' + cats[j];
			}
		}
		count = 0;

		$('#' + prefix + '_option_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				options[count] = val;
				count = count+1;
			}
		});

		if (count == 0) {
			$('#' + prefix + '_option_scrollbox').css('border', '0.166em solid #f00');
			return;
		}

		var optStr = '';


		for (var j=0; j<options.length; j++) {
			if(options[j] != undefined){
				optStr += '&options[]=' + options[j];
			}
		}
		count = 0;

		$('#' + prefix + '_article_scrollbox > tbody > tr > td > div').each(function(){
			var val = $(this).children().val();
			var artid = $(this).children().attr("id");

				if (val > 0) {
					articles[count] = artid;
					artamount[count] = val;
					count++;
				}
		});
		
		var artStr = '';
		var amountStr = '';

		for (var j = 0; j < articles.length; j++) {
			if (articles[j] != 0) {
				artStr += '&articles[]=' + articles[j];
				amountStr += '&artamount[]=' + artamount[j];
				
			}
		}


		var dataString = 'editBooking=' + positionObject.id
				   + '&commodity=' + $("#" + prefix + "_commodity_input").val()
				   + '&message=' + $("#" + prefix + "_message_input").val()
				   + '&exhibitor_id=' + positionObject.exhibitor.exhibitor_id
				   + '&map=' + maptool.map.id
				   + catStr
				   + optStr
				   + artStr
				   + amountStr;

		if (maptool.map.userlevel > 1) {
			dataString += '&user=' + $("#" + prefix + "_user_input").val();
		}
		
		if (positionObject.status == 1) {
			dataString += '&expires=' + $("#" + prefix + "_expires_input").val();
			if ($("#" + prefix + "_expires_input").val().match(/^\d\d-\d\d-\d\d\d\d \d\d:\d\d$/)) {
				var dateParts = $("#" + prefix + "_expires_input").val().split('-');
				dt = new Date(parseInt(dateParts[2], 10), parseInt(dateParts[1], 10)-1, parseInt(dateParts[0], 10));
				// Add one day, since it should be up to and including.
				dt.setDate(dt.getDate(+1));
				if (dt < new Date()) {
					$("#" + prefix + "_expires_input").css('border-color', 'red');
					return;
				}
			} else {
				$("#" + prefix + "_expires_input").css('border-color', 'red');
				return;
			}			
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
         			maptool.closeForms();
					$('#' + prefix + '_position_form input[type="text"], #' + prefix + '_position_form textarea').val("");
					$('.ssinfo').html('');
				}
			});
		} else {
			$('#' + prefix + '_category_scrollbox_div').css('border', '0.166em solid #f00');
		}
	});
	
}

maptool.cancelBooking = function(positionObject) {
    $.confirm({
        title: ' ',
        content: deletion + '<textarea style="margin-top: 0.5em" cols="50" rows="5" placeholder="' + lang.deletion_comment_placeholder + '"></textarea>',
        confirm: function(){
        	var message = this.$content.find('textarea').val();
          $.confirm({
			title: ' ',
			content: lang.cancel_booking_confirm_text + ' ' + positionObject.name + '?',
			confirm: function(){
				$.ajax({
					url: 'ajax/maptool.php',
					type: 'POST',
					data: 'cancelBooking=' + positionObject.id + '&comment=' + message,
					success: function(response) {
						maptool.update();
					}
				});
			},
			cancel: function() {
			}
          });
        },
        cancel: function() {
        }
    });
}

//Reserve open position
maptool.reservePosition = function(positionObject) {
		dialogue = '#reserve_position_form ';
	$('#reserve_category_scrollbox').css('border-color', '#000000');
	$('#reserve_category_scrollbox > tbody > tr > td > input').prop('checked', false);
	$('#reserve_option_scrollbox > tbody > tr > td > input').prop('checked', false);
	$('#reserve_article_scrollbox > tbody > tr > td > div > input').val(0);
	$("#reserve_commodity_input, #reserve_message_input").val("");
		var sel = $('#reserve_user_input');
		var opts_list = sel.find('option');
		opts_list.sort(function(a, b) { return $(a).text().toLowerCase() > $(b).text().toLowerCase() ? 1 : -1; });
		sel.html(opts_list);
		
	if (maptool.map.userlevel < 2) {
		$('#reserve_user_input, label[for="reserve_user_input"]').hide();
	}
	if (positionObject.status == 2 && positionObject.exhibitor) {
		$("#reserve_commodity_input").val(positionObject.exhibitor.commodity);
		$("#reserve_message_input").val(positionObject.exhibitor.arranger_message);
		$('#reserve_user_input option[value="' + positionObject.exhibitor.user + '"]').prop("selected", true);

		var categories = positionObject.exhibitor.categories, 
			options = positionObject.exhibitor.options, 
			articles = positionObject.exhibitor.articles, 
			amount = positionObject.exhibitor.amount, 
			i;

	// Categories
		for(i = 0; i < categories.length; i++){
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
		for(i = 0; i < options.length; i++){
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
			if($(this).children().attr('id') == articles[i].article_id) {
				$(this).children().val(articles[i].amount);
			}
		});
	}
	}
	maptool.openForm('reserve_position_form');
	positionDialogue('reserve_position_form');
	$('#reserve_position_form ul#progressbar li').removeClass('active');
	$('#reserve_position_form fieldset').css({
		'transform': 'scale(1)',
		'display': 'none',
		'opacity': '0',
	});				
	$('#reserve_position_form ul#progressbar li:first-child').attr('class', 'active');
	$('#reserve_position_form fieldset:first-of-type').css({
		'transform': 'scale(1)',
		'display': 'block',
		'opacity': '1',
	});
	$('.standSpaceName').html("");
	$('#reserve_position_form .standSpaceName').text(lang.reserveStandSpace + ': ' + positionObject.name);
	$('.ssinfo').html("");
	$('.ssinfo').html('<label>' + lang.area +  ': </label><p>' + positionObject.area + '</p><br/><label>' + lang.price +  ': </label><p>' + positionObject.price + ' ' + maptool.map.currency + '</p><br/><label>' + lang.info + ': </label><p>' + positionObject.information) + '</p>';

	$('#reserve_user_input').unbind('change');
	$('#reserve_user_input').change(function() {
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'getUserCommodity=1&userId=' + $('#reserve_user_input').val(),
			success: function(response) {
				if (response) {
					r = JSON.parse(response);
					$('#reserve_commodity_input').val(r.commodity);
				}
			}
		});
	});

	$('#reserve_position_form > fieldset > div > #search_user_input').unbind('keyup');
	$('#reserve_position_form > fieldset > div > #search_user_input').val('');
	$('#reserve_position_form > fieldset > div > #search_user_input').keyup(function(e) {
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
		html += '<tr style="height:1em"></tr>;<tr><td></td><td class="left"><b>' + lang.space + '</b></td><td></td><td></td></tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + positionObject.name + '</td>';
			html += '<td class="left price">' + positionObject.price + '</td>';
			html += '<td class="amount">1</td>';
			if (positionObject.vat) {
				html += '<td class="moms">' + positionObject.vat + '%</td>';
			} else {
				html += '<td class="moms">0%</td>';
			}
			html += '<td class="total">' + parseFloat(positionObject.price).toFixed(2) + '</td>';
		html += '</tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + positionObject.information + '</td>';
			html += '<td class="left price"></td>';
			html += '<td class="amount"></td>';
			html += '<td class="moms"></td>';
			html += '<td class="total"></td>';
		html += '</tr>';		
		if (positionObject.price) {
			if (parseFloat(positionObject.vat) == 25) {
				excludeVatPrice25 += parseFloat(positionObject.price);
			} else if (parseFloat(positionObject.vat) == 18) {
				excludeVatPrice18 += parseFloat(positionObject.price);
			} else {
				excludeVatPrice0 += parseFloat(positionObject.price);
			}
		}

		if (optname != "") {
			html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.options + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.articles + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr>';
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

		html2 += '<tr style="height:1em">';					
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
			html2 += '<td class="totalprice">' + maptool.map.currency + ' ' + lang.to_pay + '&nbsp;&nbsp;' + parseFloat(totalPriceRounded).toFixed(2) + '</td>';
		html2 += '</tr>';

			$(dialogue + '#review_list').append(html);
			$(dialogue + '#review_list2').append(html2);

	$(dialogue + '#review_commodity_input').html("");
	$(dialogue + '#review_commodity_input').append($("#reserve_commodity_input").val());
		if($(dialogue + '#review_commodity_input').html().length == 0) {
			$(dialogue + '#review_commodity_input').append(lang.no_commodity);
		}	
	$(dialogue + '#review_message').html("");
	$(dialogue + '#review_message').append($("#reserve_message_input").val());
		if($(dialogue + '#review_message').html().length == 0) {
			$(dialogue + '#review_message').append(lang.no_message);
		}
		$(dialogue + '#review_user').html("");

		$(dialogue + '#review_user').append($('#reserve_user_input').find(":selected").text());

	});

	$('#reserve_post').unbind('keyup');
	$('#reserve_post').unbind('keydown');
	$('#reserve_post').unbind('click');
	$('#reserve_position_form').on('keyup keypress', function(e) {
	  var code = e.keyCode || e.which;
	  if (code == 13) { 
	    e.preventDefault();
	    return false;
	  }
	});	
	$("#reserve_post").click(function(e) {
		e.preventDefault();
		var cats = [];
		var options = [];
		var articles = [];
		var artamount = [];
		var count = 0;

		$('#reserve_category_scrollbox > tbody > tr > td').each(function(){
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

		count = 0;

		$('#reserve_option_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				options[count] = val;
				count = count+1;
			}
		});

		if (count == 0) {
			$('#reserve_option_scrollbox').css('border', '0.166em solid #f00');
			return;
		}

		var optStr = '';


		for (var j=0; j<options.length; j++) {
			if(options[j] != undefined){
				optStr += '&options[]=' + options[j];
			}
		}

		count = 0;
		
		$('#reserve_article_scrollbox > tbody > tr > td > div').each(function() {
			var val = $(this).children().val();
			var artid = $(this).children().attr("id");
				if (val > 0) {
					articles[count] = artid;
					artamount[count] = val;
					count++;
				}
		});
		
		var artStr = '';
		var amountStr = '';

		for (var j = 0; j < articles.length; j++) {
			if (articles[j] != 0) {
				artStr += '&articles[]=' + articles[j];
				amountStr += '&artamount[]=' + artamount[j];
				
			}
		}
		
		var dataString = 'reservePosition=' + positionObject.id
				   + '&commodity=' + encodeURIComponent($("#reserve_commodity_input").val())
				   + '&message=' + encodeURIComponent($("#reserve_message_input").val())
		           + '&expires=' + encodeURIComponent($("#reserve_expires_input").val())
				   + '&map=' + maptool.map.id
				   + catStr
				   + optStr
				   + artStr
				   + amountStr;

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
					maptool.closeDialogues();
					maptool.closeForms();
					$('#reserve_position_form input[type="text"], #reserve_position_form textarea').val("");
					$('.ssinfo').html('');
				}
			});
		} else {
			$('#reserve_category_scrollbox_div').css('border', '0.166em solid #f00');
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
				   + '&price=' + encodeURIComponent($("#position_price_input").val())
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
				maptool.update();
			}
		});
	}
}

//View more information about a certain position
maptool.positionInfo = function(positionObject) {
	var preliminary,
		i;

	if (positionObject.preliminaries) {
		for (i = 0; i < positionObject.preliminaries.length; i++) {
			if (positionObject.preliminaries[i].position == positionObject.id) {
				preliminary = positionObject.preliminaries[i];
				break;
			}
		}
	}
	var language = $('#languages > a.selected > img:nth-child(1)').attr("alt");
	$("#more_info_dialogue h3").text("");
	$('#more_info_dialogue h4').text("");
	$('#more_info_dialogue h4').css('display', 'block');
	$("#more_info_dialogue #column .info #area").text("");
	$("#more_info_dialogue #column .info #price").text("");
	$("#more_info_dialogue #column .presentation").html("");
	$("#more_info_dialogue #column .info #commodity").html("");
	$("#more_info_dialogue #column .info #categories").html("");
	$("#more_info_dialogue #column .info #options").html("");
	$("#more_info_dialogue #column .website_link").html("");
	$("#more_info_dialogue #more_info_print").html("");
	$("#more_info_dialogue #ex_logo").attr("src", "../images/images/no_logo_"+language+".png");


	var tt = '';
	var info = $("#more_info_dialogue .igfdogskd");
	var mid_standSpaceName = $("#more_info_dialogue .standSpaceName");
	var mid_area = $("#more_info_dialogue #area");
	var mid_price = $("#more_info_dialogue #price");
	var mid_status = $("#more_info_dialogue #status");
	var mid_commodity = $("#more_info_dialogue #commodity");
	var mid_categories = $("#more_info_dialogue #categories");
	var mid_options = $("#more_info_dialogue #options");
//	var mid_website = $("#more_info_dialogue #website");
	var mid_ex_logo = $("#more_info_dialogue #ex_logo");
//	var mid_presentation = $("#more_info_dialogue #presentation");
	
	var i_area = '<h3>' + lang.area + '</h3>';
	var i_price = '<h3>' + lang.price + '</h3>';

	if (positionObject.area != '') {
		i_area += '<p>' + positionObject.area + '</p>';
	} else {
		i_area += '<p>' + lang.info_missing + '</p>';
	}

	if (positionObject.price != 0) {
		i_price += '<p>' + positionObject.price + ' ' + ' ' + maptool.map.currency + '</p>';
	} else {
		i_price += '<p>' + lang.info_missing + '</p>';
	}

	var i_status = '<h3>' + lang.status + '</h3> <p style="font-weight: 600; color:#337ab7;">' + lang.StatusText(positionObject.statusText) + '</p>';
	//var i_ex_logo = 

	mid_area.html(i_area);
	mid_status.html(i_status);
//	mid_categories.html(i_categories);
//	mid_website.html(i_website);
	

//	mid_presentation.html(i_presentation);
	mid_standSpaceName.append(positionObject.name);

	if (positionObject.status == 1) {
		mid_status.append('<p>(' + positionObject.expires + ')</p>');
	}
	if (positionObject.exhibitor) {
		mid_price.css('display', 'none');
		if (hasRights) {
		mid_area.css('padding', '2em 0 1em 0');
		mid_status.css('padding', '2em 0 1em 0');		
		} else {
			mid_area.css('display', 'none');
			mid_status.css('display', 'none');
		}
		mid_ex_logo.css('display', 'block');
		var folder = '../images/exhibitors/' + positionObject.exhibitor.user + '/';

		$.ajax({
		    url : folder,
		    success: function (data) {
		        $(data).find("a").attr("href", function (i, val) {
		            if( val.match(/\.jpg|\.png|\.gif/) ) { 
		               mid_ex_logo.attr('src', folder + val);
		            }
		        });
		    }
		});


		if (hasRights)
			mid_standSpaceName.append(' - ' + '<a href="exhibitor/profile/' + positionObject.exhibitor.user + '" class="showProfileLink" style="font-weight: 600;">' + positionObject.exhibitor.company + '</a>');
		if (!hasRights)
			mid_standSpaceName.append(' - ' + positionObject.exhibitor.company);
	
		var categories = [],
			options = [],
			i;

		for (i = 0; i < positionObject.exhibitor.categories.length; i++) {
			categories.push(positionObject.exhibitor.categories[i].name);
		}

		if (hasRights) {
			for (i = 0; i < positionObject.exhibitor.options.length; i++) {
				options.push(positionObject.exhibitor.options[i].text);
				$("#more_info_dialogue .presentation").css('max-height', 17 + i + 'em');
			}
		}

		var i_commodity = '<h3>' + lang.commodity_label + '</h3><p> ' + positionObject.exhibitor.commodity + '</p>';
		var i_categories = '<h3>' + lang.category + '</h3><p> ' + categories.join(', ') + '</p>';
		var i_options = '<h3>' + lang.extra_options + '</h3><p> ' + options.join('<br/> ') + '</p>';

		$('#more_info_dialogue h4').text(lang.ex_presentation);

		mid_commodity.html(i_commodity);
		mid_categories.html(i_categories);
		if (positionObject.exhibitor.commodity == '') {
			mid_commodity.append(lang.no_commodity);
		}		
		$('#more_info_dialogue #options').css('display', 'none');
		if (hasRights) {
			mid_options.html(i_options);
			$('#more_info_dialogue #options').css('display', 'inline-block');
			if (positionObject.exhibitor.options.length === 0) {
				mid_options.append(lang.no_options);
			}
		}

		$("#more_info_dialogue .presentation").empty();
		$('#more_info_dialogue .presentation').css('display', 'block');
		if(positionObject.exhibitor.presentation.length < 1){
			$("#more_info_dialogue .presentation").append(lang.noPresentationText);
		} else {
			$("#more_info_dialogue .presentation").append(positionObject.exhibitor.presentation);
		}
		
		if (positionObject.exhibitor.website != '') {
			var website = positionObject.exhibitor.website;
			if (website.indexOf("http://") == -1) {
				website = "http://" + website;
			}
			$("#more_info_dialogue div.website_link").html('<h3>' + lang.website_label + '</h3><a href="' + website + '" target="_blank">' + website + '</a>');
		} else {
			$("#more_info_dialogue div.website_link").html('');
		}
		
	} else if (preliminary) {
			mid_price.css('display', 'none');
			mid_area.css({
				'padding': '2em 0 1em 0',
				'display': 'inline-block',
			});
			mid_status.css('display', 'inline-block');
			mid_status.css('padding', '2em 0 1em 0');
			mid_ex_logo.css('display', 'block');
			mid_standSpaceName.append(' - ' + preliminary.company);
			var folder = '../images/exhibitors/' + preliminary.user + '/';

		$.ajax({
		    url : folder,
		    success: function (data) {
		        $(data).find("a").attr("href", function (i, val) {
		            if( val.match(/\.jpg|\.png|\.gif/) ) { 
		               mid_ex_logo.attr('src', folder + val);
		            }
		        });
		    }
		});
		var categories = [],
			options = [],
			i;

		if (preliminary.category_list.length) {
			categories.push(preliminary.category_list.join('<br />'));
		}

		if (preliminary.option_list.length) {
			options.push(preliminary.option_list.join('<br />'));
		}

		$("#more_info_dialogue .presentation").css('max-height', 17 + i + 'em');

		var i_commodity = '<h3>' + lang.commodity_label + '</h3><p> ' + preliminary.commodity + '</p>';
		var i_categories = '<h3>' + lang.category + '</h3><p> ' + categories + '</p>';
		var i_options = '<h3>' + lang.extra_options + '</h3><p> ' + options + '</p>';

		$('#more_info_dialogue h4').text(lang.ex_presentation);

		mid_commodity.html(i_commodity);
		mid_categories.html(i_categories);
		if (preliminary.commodity == '') {
			mid_commodity.append(lang.no_commodity);
		}		
		$('#more_info_dialogue #options').css('display', 'none');
		if (hasRights) {
			mid_options.html(i_options);
			$('#more_info_dialogue #options').css('display', 'inline-block');
			if (preliminary.option_list.length === 0) {
				mid_options.append(lang.no_options);
			}
		}

		$("#more_info_dialogue .presentation").empty();
		$('#more_info_dialogue .presentation').css('display', 'block');
		
		if (preliminary.presentation.length) {
			$("#more_info_dialogue .presentation").append(preliminary.presentation);
		} else {
			$("#more_info_dialogue .presentation").append(lang.noPresentationText);
		}		

		if (preliminary.website !== null && preliminary.website !== "") {
			var website = preliminary.website;

			if (website.indexOf("http://") == -1) {
				website = "http://" + website;
			}
			$("#more_info_dialogue div.website_link").html('<h3>' + lang.website_label + '</h3><a href="' + website + '" target="_blank">' + website + '</a>');
		} else {
			$("#more_info_dialogue div.website_link").html('');
		}

	} else {
		mid_price.html(i_price);
		mid_ex_logo.css('display', 'none');
		mid_area.css({
			'padding': '0',
			'padding-left': '1.5em',
			'display': 'inline-block'
		});
		mid_price.css('display', 'block');
		mid_status.css('display', 'inline-block');
		mid_status.css('padding', '0');
		if(positionObject.information.length < 1){
			$("#more_info_dialogue h4").css('display', 'none');
			$('#more_info_dialogue .presentation').css('display', 'none');
		} else {
			$("#more_info_dialogue h4").css('display', 'block');
			$("#more_info_dialogue h4").html(lang.standSpaceInformation);
			$('#more_info_dialogue .presentation').css('display', 'block');
			$("#more_info_dialogue .presentation").html(positionObject.information.replace(/\n/g, '<br/>'));
		}
	}

	if (positionObject.exhibitor) {
		$('#printLink').remove();
		$("#more_info_dialogue #more_info_print").append('<a href="/mapTool/print_position/' + maptool.map.id + '/' + positionObject.id + '" target="_blank" class="link-button greenbutton mediumbutton" id="printLink"><img src="images/icons/print.png" id="print_img"/>' + ' ' + lang.print + '</a>');

		if (positionObject.exhibitor.facebook)
			$("#more_info_dialogue #more_info_print").append('<a href="' + positionObject.exhibitor.facebook + '" target="_blank" ><img src="images/icons/facebook.png" class="socialicon_map" title="' + lang.visit_us_facebook + '" /></a>');
		if (positionObject.exhibitor.twitter)
			$("#more_info_dialogue #more_info_print").append('<a href="' + positionObject.exhibitor.twitter + '" target="_blank" ><img src="images/icons/twitter.png" class="socialicon_map" title="' + lang.visit_us_twitter + '" /></a>');
		if (positionObject.exhibitor.google_plus)
			$("#more_info_dialogue #more_info_print").append('<a href="' + positionObject.exhibitor.google_plus + '" target="_blank" ><img src="images/icons/google.png" class="socialicon_map" title="' + lang.visit_us_google + '" /></a>');
		if (positionObject.exhibitor.youtube)
			$("#more_info_dialogue #more_info_print").append('<a href="' + positionObject.exhibitor.youtube + '" target="_blank" ><img src="images/icons/youtube.png" class="socialicon_map" title="' + lang.visit_us_youtube + '" /></a>');		
	}

	maptool.openDialogue('more_info_dialogue');
	positionDialogue("more_info_dialogue");
}

maptool.makeNote = function(positionObject) {
	Comments.showDialog({
		user_id: positionObject.exhibitor.user,
		fair_id: maptool.map.fair,
		position_id: positionObject.id,
		close_dialog_after: false,
		collection_view_selector: '#note_dialogue .commentList',
		template: 'comment_item'
	});
};

maptool.showPreliminaryBookings = function(position_data) {
	var dialogue = $('#preliminary_bookings_dialogue'),
		tbody = $('tbody', dialogue);

	tbody.html('');
	$('.standSpaceName').html("");
	$('#preliminary_bookings_dialogue .standSpaceName').text(lang.showPreliminaryBookings);
	maptool.openDialogue('preliminary_bookings_dialogue');

	$.ajax({
		url: 'ajax/maptool.php',
		type: 'GET',
		data: 'prel_bookings_list=1&position=' + position_data.id,
		success: function(response) {
			var i;

			for (i = 0; i < response.length; i++) {
				tbody.append('<tr data-id="' + response[i].id + '"><td>'
							+ response[i].standSpace.name +
						'</td><td>'
							+ response[i].standSpace.area + 
						'</td><td><a href="/exhibitor/profile/'
							+ response[i].user
						+ '" class="showProfileLink">'
							+ response[i].company +
						'</a></td><td>'
							+ response[i].commodity +
						'</td><td style="min-width:10em;">'
							+ response[i].booking_time +
						'</td><td style="display: none"></td><td class="center" title=\'' + response[i].arranger_message.replace("'", '&#039;') + '\'>'
							+ (response[i].arranger_message.length > 0 ? '<a href="administrator/arrangerMessage/preliminary/' + response[i].id + '" class="open-arranger-message">'
								+ '<img src="images/icons/script.png" class="icon_img" alt="' + lang.messageFromExhibitor + '" />'
							+ '</a>' : '') +
						'</td><td style="display: none">' + response[i].categories + '</td>' + 
						'<td class="center"><a style="cursor: pointer;" onclick="denyPrepPosition(\''
							+ response[i].denyUrl + '\', \'' + response[i].standSpace.name + '\', \'Preliminary Booking\', this)"' +
						'</a><img src="'
							+ response[i].denyImgUrl + 
						'" class="icon_img" /></td><td class="approve" style="display:none;">' + response[i].baseUrl + 'administrator/newReservations/approve/</td>'
						+ '<td class="center"><a style="cursor: pointer" class="open-approve-form" data-index="' + i + '"><img src="images/icons/add.png" class="icon_img"' + 
						' alt="approve" /></a></td><td class="center"><a href="#" class="open-reservation-form" data-index="'
							+ i
							+ '"><img src="images/icons/reserve.png" class="icon_img" alt="+" /></a></td></tr>'
				);
			}

			// Save this list data for later use, in reservePreliminaryBooking()

			maptool.prel_bookings_data = response;

			dialogue.on('click', '.open-reservation-form', function(e) {
				e.preventDefault();
				maptool.reservePreliminaryBooking(position_data, maptool.prel_bookings_data[$(this).data('index')]);
				dialogue.hide();
			});
			dialogue.on('click', '.open-approve-form', function(e) {
				e.preventDefault();
				maptool.bookPreliminaryBooking(position_data, maptool.prel_bookings_data[$(this).data('index')]);
				dialogue.hide();
			});
		}
	});
};

//Book preliminary booking
maptool.bookPreliminaryBooking = function(position_data, prel_booking_data) {
	dialogue = '#book_position_form ';
	$('#book_commodity_input').val(prel_booking_data.commodity);
	$('#book_message_input').val(prel_booking_data.arranger_message);
	$('#book_user_input').val(prel_booking_data.user);
	$('#book_category_scrollbox > tbody > tr > td > input').prop('checked', false);
	$('#book_option_scrollbox > tbody > tr > td > input').prop('checked', false);
	$('#book_article_scrollbox > tbody > tr > td > div > input').val(0);

	var categories = prel_booking_data.categories.split('|'),
		options = prel_booking_data.options.split('|'),
		articles = prel_booking_data.articles.split('|'),
		amount = prel_booking_data.amount.split('|'),
		i;

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

/*
	for (i = 0; i < categories.length; i++) {
		$('#book_category_scrollbox input[value=' + categories[i] + ']').prop('checked', true);
	}

	for (i = 0; i < options.length; i++) {
		$('#book_option_scrollbox input[value=' + options[i] + ']').prop('checked', true);
	}
*/
	$('#book_position_form').show();
	$('#book_position_form ul#progressbar li').removeClass('active');
	$('#book_position_form fieldset').css({
		'transform': 'scale(1)',
		'display': 'none',
		'opacity': '0',
	});				
	$('#book_position_form ul#progressbar li:first-child').attr('class', 'active');
	$('#book_position_form fieldset:first-of-type').css({
		'transform': 'scale(1)',
		'display': 'block',
		'opacity': '1',
	});
	$('#book_position_form .standSpaceName').text(lang.bookPrelStandSpace + ': ' + position_data.name);
	$('.ssinfo').html("");
	$('.ssinfo').html('<label>' + lang.area +  ': </label><p>' + position_data.area + '</p><br/><label>' + lang.price +  ': </label><p>' + position_data.price + ' ' + maptool.map.currency + '</p><br/><label>' + lang.info + ': </label><p>' + position_data.information) + '</p>';

	$('#book_user_input').unbind('change');
	$('#book_user_input').change(function() {
		$.ajax({
			url: 'ajax/maptool.php',
			type: 'POST',
			data: 'getUserCommodity=1&userId=' + encodeURIComponent($('#book_user_input').val()),
			success: function(response) {
				if (response) {
					r = JSON.parse(response);
					$('#book_commodity_input').val(r.commodity);
				}
			}
		});
	});

	$('#book_position_form > fieldset > div > #search_user_input').unbind('keyup');
	$('#book_position_form > fieldset > div > #search_user_input').val('');
	$('#book_position_form > fieldset > div > #search_user_input').keyup(function(e) {
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
		html += '<tr style="height:1em"></tr>;<tr><td></td><td class="left"><b>' + lang.space + '</b></td><td></td><td></td></tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + position_data.name + '</td>';
			html += '<td class="left price">' + position_data.price + '</td>';
			html += '<td class="amount">1</td>';
			if (position_data.vat) {
				html += '<td class="moms">' + position_data.vat + '%</td>';
			} else {
				html += '<td class="moms">0%</td>';
			}
			html += '<td class="total">' + parseFloat(position_data.price).toFixed(2) + '</td>';
		html += '</tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + position_data.information + '</td>';
			html += '<td class="left price"></td>';
			html += '<td class="amount"></td>';
			html += '<td class="moms"></td>';
			html += '<td class="total"></td>';
		html += '</tr>';		
		if (position_data.price) {
			if (parseFloat(position_data.vat) == 25) {
				excludeVatPrice25 += parseFloat(position_data.price);
			} else if (parseFloat(position_data.vat) == 18) {
				excludeVatPrice18 += parseFloat(position_data.price);
			} else {
				excludeVatPrice0 += parseFloat(position_data.price);
			}
		}

		if (optname != "") {
			html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.options + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.articles + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr>';
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

		html2 += '<tr style="height:1em">';					
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
			html2 += '<td class="totalprice">' + maptool.map.currency + ' ' + lang.to_pay + '&nbsp;&nbsp;' + parseFloat(totalPriceRounded).toFixed(2) + '</td>';
		html2 += '</tr>';

			$(dialogue + '#review_list').append(html);
			$(dialogue + '#review_list2').append(html2);

	$(dialogue + '#review_commodity_input').html("");
	$(dialogue + '#review_commodity_input').append($("#book_commodity_input").val());
		if($(dialogue + '#review_commodity_input').html().length == 0) {
			$(dialogue + '#review_commodity_input').append(lang.no_commodity);
		}	
	$(dialogue + '#review_message').html("");
	$(dialogue + '#review_message').append($("#book_message_input").val());
		if($(dialogue + '#review_message').html().length == 0) {
			$(dialogue + '#review_message').append(lang.no_message);
		}
		$(dialogue + '#review_user').html("");

		$(dialogue + '#review_user').append($('#book_user_input').find(":selected").text());

	});
	$('#book_post').unbind('keyup');
	$('#book_post').unbind('keydown');
	$('#book_post').unbind('click');
	$('#book_position_form').on('keyup keypress', function(e) {
	  var code = e.keyCode || e.which;
	  if (code == 13) { 
	    e.preventDefault();
	    return false;
	  }
	});	
	$("#book_post").click(function(e) {
		e.preventDefault();
		var cats = [];
		var options = [];
		var articles = [];
		var artamount = [];
		var count = 0;

		$('#book_category_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				cats[count] = val;
				count = count+1;
			}
		});

		if (count == 0) {
			$('#book_category_scrollbox').css('border', '0.166em solid #f00');
			return;
		}

		var catStr = '';

		for (var j=0; j<cats.length; j++) {
			if(cats[j] != undefined){
				catStr += '&categories[]=' + cats[j];
			}
		}

		count = 0;

		$('#book_option_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				options[count] = val;
				count = count+1;
			}
		});

		var optStr = '';


		for (var j=0; j<options.length; j++) {
			if(options[j] != undefined){
				optStr += '&options[]=' + options[j];
			}
		}

		count = 0;
		
		$('#book_article_scrollbox > tbody > tr > td > div').each(function() {
			var val = $(this).children().val();
			var artid = $(this).children().attr("id");
				if (val > 0) {
					articles[count] = artid;
					artamount[count] = val;
					count++;
				}
		});
		
		var artStr = '';
		var amountStr = '';

		for (var j = 0; j < articles.length; j++) {
			if (articles[j] != 0) {
				artStr += '&articles[]=' + articles[j];
				amountStr += '&artamount[]=' + artamount[j];
				
			}
		}
		
		var dataString = 'book_preliminary=' + prel_booking_data.id
				   + '&commodity=' + encodeURIComponent($("#book_commodity_input").val())
				   + '&message=' + encodeURIComponent($("#book_message_input").val())
				   + '&map=' + maptool.map.id
				   + catStr
				   + optStr
				   + artStr
				   + amountStr;

		if (maptool.map.userlevel > 1) {
			dataString += '&user=' + encodeURIComponent($("#book_user_input").val());
		}

		if (position_data.exhibitor && position_data.exhibitor.preliminary_booking) {
			dataString += '&prel_booking=' + position_data.exhibitor.preliminary_booking;
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
					maptool.closeForms();
					$('#book_position_form input[type="text"], #book_position_form textarea').val("");
					$('.ssinfo').html('');
				}
			});
		} else {
			$('#book_category_scrollbox_div').css('border', '0.166em solid #f00');
		}
	});
};

//Reserve preliminary booking
maptool.reservePreliminaryBooking = function(position_data, prel_booking_data) {
	dialogue = '#reserve_position_form ';
	$('#reserve_commodity_input').val(prel_booking_data.commodity);
	$('#reserve_message_input').val(prel_booking_data.arranger_message);
	$('#reserve_user_input').val(prel_booking_data.user);
	$('#reserve_category_scrollbox > tbody > tr > td > input').prop('checked', false);
	$('#reserve_option_scrollbox > tbody > tr > td > input').prop('checked', false);
	$('#reserve_article_scrollbox > tbody > tr > td > div > input').val(0);

	var categories = prel_booking_data.categories.split('|'),
		options = prel_booking_data.options.split('|'),
		articles = prel_booking_data.articles.split('|'),
		amount = prel_booking_data.amount.split('|'),
		i;

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

/*
	for (i = 0; i < categories.length; i++) {
		$('#reserve_category_scrollbox input[value=' + categories[i] + ']').prop('checked', true);
	}

	for (i = 0; i < options.length; i++) {
		$('#reserve_option_scrollbox input[value=' + options[i] + ']').prop('checked', true);
	}
*/
	$('#reserve_position_form').show();
	$('#reserve_position_form ul#progressbar li').removeClass('active');
	$('#reserve_position_form fieldset').css({
		'transform': 'scale(1)',
		'display': 'none',
		'opacity': '0',
	});				
	$('#reserve_position_form ul#progressbar li:first-child').attr('class', 'active');
	$('#reserve_position_form fieldset:first-of-type').css({
		'transform': 'scale(1)',
		'display': 'block',
		'opacity': '1',
	});
	$('#reserve_position_form .standSpaceName').text(lang.reservePrelStandSpace + ': ' + position_data.name);
	$('.ssinfo').html("");
	$('.ssinfo').html('<label>' + lang.area +  ': </label><p>' + position_data.area + '</p><br/><label>' + lang.price +  ': </label><p>' + position_data.price + ' ' + maptool.map.currency + '</p><br/><label>' + lang.info + ': </label><p>' + position_data.information) + '</p>';

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

	$('#reserve_position_form > fieldset > div > #search_user_input').unbind('keyup');
	$('#reserve_position_form > fieldset > div > #search_user_input').val('');
	$('#reserve_position_form > fieldset > div > #search_user_input').keyup(function(e) {
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
		html += '<tr style="height:1em"></tr>;<tr><td></td><td class="left"><b>' + lang.space + '</b></td><td></td><td></td></tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + position_data.name + '</td>';
			html += '<td class="left price">' + position_data.price + '</td>';
			html += '<td class="amount">1</td>';
			if (position_data.vat) {
				html += '<td class="moms">' + position_data.vat + '%</td>';
			} else {
				html += '<td class="moms">0%</td>';
			}
			html += '<td class="total">' + parseFloat(position_data.price).toFixed(2) + '</td>';
		html += '</tr>';
		html += '<tr>';
			html += '<td class="id"></td>';
			html += '<td class="left name">' + position_data.information + '</td>';
			html += '<td class="left price"></td>';
			html += '<td class="amount"></td>';
			html += '<td class="moms"></td>';
			html += '<td class="total"></td>';
		html += '</tr>';		
		if (position_data.price) {
			if (parseFloat(position_data.vat) == 25) {
				excludeVatPrice25 += parseFloat(position_data.price);
			} else if (parseFloat(position_data.vat) == 18) {
				excludeVatPrice18 += parseFloat(position_data.price);
			} else {
				excludeVatPrice0 += parseFloat(position_data.price);
			}
		}

		if (optname != "") {
			html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.options + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr><tr><td></td><td class="left"><b>' + lang.articles + '</b></td><td></td><td></td></tr>';
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
		html += '<tr style="height:1em"></tr>';
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

		html2 += '<tr style="height:1em">';					
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
			html2 += '<td class="totalprice">' + maptool.map.currency + ' ' + lang.to_pay + '&nbsp;&nbsp;' + parseFloat(totalPriceRounded).toFixed(2) + '</td>';
		html2 += '</tr>';

			$(dialogue + '#review_list').append(html);
			$(dialogue + '#review_list2').append(html2);

	$(dialogue + '#review_commodity_input').html("");
	$(dialogue + '#review_commodity_input').append($("#reserve_commodity_input").val());
		if($(dialogue + '#review_commodity_input').html().length == 0) {
			$(dialogue + '#review_commodity_input').append(lang.no_commodity);
		}	
	$(dialogue + '#review_message').html("");
	$(dialogue + '#review_message').append($("#reserve_message_input").val());
		if($(dialogue + '#review_message').html().length == 0) {
			$(dialogue + '#review_message').append(lang.no_message);
		}
		$(dialogue + '#review_user').html("");

		$(dialogue + '#review_user').append($('#reserve_user_input').find(":selected").text());

	});

	$('#reserve_post').unbind('keyup');
	$('#reserve_post').unbind('keydown');
	$('#reserve_post').unbind('click');
	$('#reserve_position_form').on('keyup keypress', function(e) {
	  var code = e.keyCode || e.which;
	  if (code == 13) { 
	    e.preventDefault();
	    return false;
	  }
	});	
	$("#reserve_post").click(function(e) {
		e.preventDefault();
		var cats = [];
		var options = [];
		var articles = [];
		var artamount = [];
		var count = 0;

		$('#reserve_category_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				cats[count] = val;
				count = count+1;
			}
		});

		if (count == 0) {
			$('#reserve_category_scrollbox').css('border', '0.166em solid #f00');
			return;
		}

		var catStr = '';

		for (var j=0; j<cats.length; j++) {
			if(cats[j] != undefined){
				catStr += '&categories[]=' + cats[j];
			}
		}

		count = 0;

		$('#reserve_option_scrollbox > tbody > tr > td').each(function(){
			var val = $(this).children('input:checked').val();
			if(val != "undefined"){
				options[count] = val;
				count = count+1;
			}
		});

		if (count == 0) {
			$('#reserve_option_scrollbox').css('border', '0.166em solid #f00');
			return;
		}

		var optStr = '';


		for (var j=0; j<options.length; j++) {
			if(options[j] != undefined){
				optStr += '&options[]=' + options[j];
			}
		}

		count = 0;
		
		$('#reserve_article_scrollbox > tbody > tr > td > div').each(function() {
			var val = $(this).children().val();
			var artid = $(this).children().attr("id");
				if (val > 0) {
					articles[count] = artid;
					artamount[count] = val;
					count++;
				}
		});
		
		var artStr = '';
		var amountStr = '';

		for (var j = 0; j < articles.length; j++) {
			if (articles[j] != 0) {
				artStr += '&articles[]=' + articles[j];
				amountStr += '&artamount[]=' + artamount[j];
				
			}
		}
		
		var dataString = 'reserve_preliminary=' + prel_booking_data.id
				   + '&commodity=' + encodeURIComponent($("#reserve_commodity_input").val())
				   + '&message=' + encodeURIComponent($("#reserve_message_input").val())
				   + '&expires=' + encodeURIComponent($("#reserve_expires_input").val())
				   + '&map=' + maptool.map.id
				   + catStr
				   + optStr
				   + artStr
				   + amountStr;

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
					maptool.closeDialogues();
					maptool.closeForms();
					$('#reserve_position_form input[type="text"], #reserve_position_form textarea').val("");
					$('.ssinfo').html('');
				}
			});
		} else {
			$('#reserve_category_scrollbox_div').css('border', '0.166em solid #f00');
		}
	});
};

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

	oldWidth = $("#map #map_img").width();
	oldHeight = $("#map #map_img").height();

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

	var	grid_generation_timer = null,
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
		height: changeDimensions
	};

	// Generates the grid as HTML
	function generateGrid() {
		var html = '', 
			num_cols = Math.ceil((grid.width() + settings.width * 2) / settings.width), 
			num_rows = Math.ceil((grid.height() + settings.height * 2) / settings.height), 
			num_cells = num_cols * num_rows, 
			i;

		if (settings.activated) {
			for (i = 0; i < num_cells; i++) {
				html += '<div class="grid-cell"></div>';
			}
		}

		grid_frame.html(html);
	}

	// Save grid settings to database
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

	// Fetch grid settings from database
	function getGridSettings() {
		if (hasRights){
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
			maptoolbox.style.top = 0;
			maptoolbox.style.left = "2em";

			maptoolboxHeader.off("mousedown", toolboxStartMove);

			window.setTimeout(function () {
				minimize.setAttribute("title", "Maximize");
			}, 500);
		} else {
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

	function toggleButtonWhite() {
		if ($('#maptool_grid_white').prop('checked') === false) {
			$('#maptool_grid_white').prop('checked', true);
			$("#maptool_grid_white")[0];
			$("#maptool_grid_white2").val(lang.black_grid);
			toggleWhite();
		} else if ($('#maptool_grid_white').prop('checked') === true) {
			$('#maptool_grid_white').prop('checked', false);
			$("#maptool_grid_white2").val(lang.white_grid);
			$("#maptool_grid_white")[1];
			toggleWhite();
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

	function toggleButtonActivated() {
		if ($('#maptool_grid_activated').prop('checked') === false) {
			$('#maptool_grid_activated').prop('checked', true);
			$("#maptool_grid_activated")[0];
			toggleActivated();
		} else if ($('#maptool_grid_activated').prop('checked') === true) {
			$('#maptool_grid_activated').prop('checked', false);
			$("#maptool_grid_activated")[1];
			toggleActivated();
		}		
	}

	function toggleActivated() {
		settings.activated = $("#maptool_grid_activated")[0].checked;
		generateGrid();			
	}

	function resetGrid() {
		$('#maptool_grid_width').val(20).trigger('change');
		$('#maptool_grid_height').val(20).trigger('change');
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
		$("#maptool_grid_snap_markers_x")[0].checked = settings.snap_markers.x;
		$("#maptool_grid_snap_markers_y")[0].checked = settings.snap_markers.y;

		//Set is moving
		$("#maptool_grid_is_moving")[0].checked = settings.is_moving;
		toggleIsMoving();

		//Set coords
		$("#maptool_grid_coord_x").val(settings.coords.x);
		$("#maptool_grid_coord_y").val(settings.coords.y);
		changeCoords();

		//Set dimensions
		$("#maptool_grid_width").val(settings.width);
		$("#maptool_grid_height").val(settings.height);
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
			getGridSettings();

			$('.spinner').spinner({
				spin: function(e, ui) {
					$(this).val(ui.value);
					$(this).trigger('change');
				}
			});

			//Only run if tool box exists
			if (maptoolbox) {

				// Toolbox events
				for (var property in setting_listeners) {
					if (setting_listeners.hasOwnProperty(property)) {
						$('#maptool_grid_' + property).on('change', setting_listeners[property]);
						setting_listeners[property]();
					}
				}
			}

			$('#maptoolbox_minimize').on('click', toggleToolbox);
			$('#maptool_grid_reset').on('click', resetGrid);
			$('#maptool_grid_save').on('click', setGridSettings);
			$("#maptoolbox_header").on("mousedown", toolboxStartMove);
			$('#maptool_grid_activated2').on('click', toggleButtonActivated);
			$('#maptool_grid_white2').on('click', toggleButtonWhite);

			// Grid movement events
			grid.on('mousemove', mouseMoved);
			grid_frame.on('mousedown', startMove);
			grid_frame.on('mouseup', stopMove);

			//Toolbox movement events
			maptoolboxHeader.on("mousedown", toolboxStartMove);
			maptoolboxHeader.on("mouseup", toolboxStopMove);

			//Only run if tool box exists
			if (maptoolbox) {
				setMaptoolboxPosition();
			}

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
		tm = 67;
	} else {
		var steps = (config.maxZoom - 1) / config.zoomStep;
		var currentStep = (zoomLevel - 1) / config.zoomStep;
		var slideWidth = $('#zoombar').width() - 138;
		var tm = (slideWidth / steps) * currentStep;
		tm = $('#zoombar #in').width()  + $('#zoombar #out').width() + tm;
	}
	$('#zoombar img').css({
		marginLeft: tm + 'px'
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
	config.positionTopOffset = $("#header").outerHeight();

	// Quick fix for map reloading without id sometimes.
	if (typeof mapId == 'undefined') {
		return;
	}

	_mapId = mapId;

	maptool.Grid.init();

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
			var h = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
			//var holderHeight = h - $('#header').height() -10;

			var isiPad = /ipad/i.test(navigator.userAgent.toLowerCase());

			if(jQuery.browser.mobile){
			$('#mapHolder').css({
				width: '100%',
				height: h + 'px'
			});				
			} else if (isiPad) {
				var h = $(window).height();
				var w = $(window).width();
				if (w > h) {
					$('#mapHolder').css({
						width: '100%',
						height: 768 + 'px'
					});
				} else {
					$('#mapHolder').css({
						width: '100%',
						height: 1024 + 'px'
					});					
				}
			} else {
				$('#mapHolder').css({
					width: '100%',
					height: h + 'px'
				});				
			}			
			var mapHeight = $('#mapHolder').height();

			var sidebars = $('#right_sidebar div.pre_list').height() + ($('#right_sidebar hr').css('marginTop').replace('px', '')*1) + $('#right_sidebar div h2#exh2').height() + $('#right_sidebar div').height();

			sidebarsheight = sidebars + 57;


			if (maptool.map.userlevel > 0) {

				if(jQuery.browser.mobile){
					$('#right_sidebar ol').css({
						height: mapHeight + $('#header').height() - sidebarsheight + 'px'
					});
				} else {
					if (mapHeight > 665) {
						$('#right_sidebar ol').css({
							height: mapHeight - sidebarsheight + 'px'
						});
					} else {
						$('#right_sidebar ol').css({
							height: mapHeight - sidebarsheight + 7 + 'px'
						});
					}
					$('#new_header').css({
						height: mapHeight + 1 + 'px'
					});
				}
			} else {

				if(jQuery.browser.mobile){
					$('#right_sidebar ol').css({
						height: mapHeight + $('#header').height() - sidebarsheight - 1 + 'px'
					});
					console.log('mobile');
				} else {
					if (mapHeight > 665) {
						$('#right_sidebar ol').css({
							height: mapHeight - sidebarsheight - 1 + 'px'
						});
					} else {
						$('#right_sidebar ol').css({
							height: mapHeight - sidebarsheight + 7 + 'px'
						});
					}
					$('#new_header').css({
						height: mapHeight + 1 + 'px'
					});
				}
			}

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
		maptool.closeForms();
	});
	$(".cancelbutton").click(function() {
		maptool.closeForms();
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
	
		$("#right_sidebar_show").click(function() {
			var mapHeight = $('#mapHolder').height();
			var sidebars = $('#right_sidebar div.pre_list').height() + ($('#right_sidebar hr').css('marginTop').replace('px', '')*1) + $('#right_sidebar div h2#exh2').height() + $('#right_sidebar div').height();
			sidebarsheight = sidebars + 37;

			if (maptool.map.userlevel > 0) {
					$('#right_sidebar ol').css({
						height: mapHeight - sidebarsheight + 'px'
					});
			} else {
					$('#right_sidebar ol').css({
						height: mapHeight - sidebarsheight - 1 + 'px'
					});				
			}
		});
		
		$("#overlay").click(function() {
			if (maptool.map.userlevel < 1) {
			maptool.closeDialogues();
			}
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

	// ESC-key press listener
	$(document).keydown(function(e) {
		if (e.keyCode == 27 && ($("#nouser_dialogue:visible").length === 0))
			maptool.closeDialogues();
		if (e.keyCode == 27 && ($("#nouser_dialogue:visible").length === 0))
			maptool.closeForms();
	});

	$('#search_filter').keyup(function() {
		maptool.populateList();
	});

	$(window).resize(function() {
		var isiPad = /ipad/i.test(navigator.userAgent.toLowerCase());
		if(jQuery.browser.mobile){
			maptool.placeMarkers();
		} else if (isiPad) {
			maptool.placeMarkers();
		} else {
			maptool.placeMarkers();
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

	$('#paste_fair_registration').on('click', maptool.pasteFairRegistration);
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
	$('input#search_user_input').val(text);
	$('input#reserve_user_input').val(id);
	$('#hiddenExhibitorList').hide();
}

function chooseThisBook(thisd){
	var text = $(thisd).text();
	var id = $(thisd).val();
	$('.exhibitorNotFound').css('display', 'none');
	$('input#search_user_input').val(text);
	$('input#book_user_input').val(id);
	$('#hiddenExhibitorList').hide();
}

function approveClick(clicked) {
	$('.dialogue').hide();
	showPopup('book', clicked);
}
