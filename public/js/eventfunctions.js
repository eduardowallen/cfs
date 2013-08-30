/***
	Ursprungligen skriven av : Andréas Forsbom
	Kontakta : andreas@trinax.se vid frågor
	2013-08-20

	Denna fil innehåller främst funktioner för skapning och redigering av nya event/mässor
**/

$().ready(function(){
	$('a.addfees').click(function(){showDialogue('add_custom_fee_dialogue')});
	$('.currency').change(function(){
		changeCurrency();
	});
});

/***
	Denna funktion visar en popupruta för Custom Fee's vid
	skapning av nytt event.
**/
function showDialogue(dialogue){
	$('#overlay').show();
	$('.'+dialogue).show();

	$('.'+dialogue+' > input').val = '';
	$('.'+dialogue+' > button').click(function(){
		var unique = 1;
		var name = $('.custom_fee_name').val();

		$('#custom_fees').children().each(function(){
			if($(this).children('.name').text() == $('.custom_fee_name').val()+':'){
				unique = 0;
			}
		});
		
		if(name.length > 0 && unique == 1){
			$(this).off('click');
			var divName = name.replace(/ /g,'');
			var arrname = name.replace(/ /g,'%');
			var rand = Math.floor(Math.random() * 10000000);
			var divStr = '' 
			+	'<div style="float:left;" class="'+rand+divName+'">'
			+	'<p style="font-weight:bold; margin:3px 0 0 0;" class="name">'+name+':</p>'
			+ 	'<input style="float:left;" class="inp'+rand+divName+'" name="custom_fee[\''+arrname+'\']" type="text" />'
			+	'<input type="hidden" class="id'+rand+divName+'" name="custom_fee_id[\''+arrname+'\']" value="new" type="text" />'
			+	'<p style="float:left; margin:5px 0px 0px 5px;" class="value">'+defaultvalue+'</p>'
			+	'<p style="float:left; margin:0px 5px; width:20px; font-size:9px;">edit<img class="edit" onclick="editPrice(\''+dialogue+'\', \''+rand+divName+'\', \''+name+'\')"  src="images/icons/pencil.png" alt="" /></p>'
			+	'<p style="float:left; margin:0px 5px; width:20px; font-size:9px;">delete<img class="delete" onclick="removePrice(\''+rand+divName+'\')" src="images/icons/delete.png" alt="" /></p>'
			+	'</div>';
			$('#custom_fees').append(divStr);
			hideDialogue(dialogue);
		} else {
			alert(error_custom_fee_name);
			hideDialogue(dialogue);
		}
	});
}


/***
	Denna funktion tar bort en 'Custom fee' då man skapar en mässa
**/
function removePrice(div){
	$('.'+div).remove();
}



/***
	Denna funktion tar fram en popup för att redigera en 'Custom fee' om man trycker på 'edit'  på en custom fee
**/
function editPrice(dialogue, row, value){
	$('#overlay').show();
	$('.'+dialogue).show();

	$('.'+dialogue+' > button').click(function(){
		$(this).off('click');

		var divName = $('.custom_fee_name').val();
		var dname = $('.custom_fee_name').val().replace(/ /g,'');
		var arrname = $('.custom_fee_name').val().replace(/ /g,'%');
		var unique = 1;
		var rand = Math.floor(Math.random() * 10000000);

		$('#custom_fees').children().each(function(){
			if($(this).children('.name').text() == $('.custom_fee_name').val()+':'){
				unique = 0;
			}
		});

		if(divName.length > 0 && unique == 1){
			$('.'+row+' .name').text($('.custom_fee_name').val()+':');

			$('.inp'+row).attr('name', 'custom_fee[\''+arrname+'\']');
			$('.inp'+row).attr('class', 'inp'+rand+dname);
			
			$('.id'+row).attr('name', 'custom_fee_id[\''+arrname+'\']');
			$('.id'+row).attr('class', 'id'+rand+dname);

			$('.'+row+' .edit').attr('onclick', $('.'+row+' .edit').attr('onclick').replace('\''+value+'\'',  '\''+divName+'\''));
			$('.'+row+' .edit').attr('onclick', $('.'+row+' .edit').attr('onclick').replace('\''+row+'\'', '\''+rand+dname+'\''));
			$('.'+row+' .delete').attr('onclick', $('.'+row+' .delete').attr('onclick').replace('\''+row+'\'', '\''+rand+dname+'\''));
			$('div.'+row).attr('class', rand+dname);
			hideDialogue(dialogue);
		} else {
			alert(error_custom_fee_name);
			hideDialogue(dialogue);
		}
	});
}



/***
	Denna funktion döljer laddnings-gif'en 
**/
function hideDialogue(dialogue){
	$('#overlay').hide();
	$('.'+dialogue).hide();

}



/***
	Denna funktion visar en GIF-bild med ett meddelande för att visa att något laddar.
**/
function showLoader(message){
	var divstr = ''
	+'<div class="loader" style="position:fixed; width:100%; height:100%; top:0px; left:0px;">'
	+'	<div class="box" style="border-radius:10px; margin:200px auto 0 auto; text-align:center; height:200px; background-color: #efefef; border-radius: 10px,;width:200px; background-image:url(\'images/ajax-loader.gif\'); background-repeat: no-repeat;background-position: center;">'
	+'		<p style="float:left; font-weight:bold; margin-top:160px; margin-left:20px;">'+message+'</p>'
	+'	</div>'
	+'</div>';
	$('body').append(divstr);
}

function hideLoader(){
	$('.loader').remove();
}




/***
	Denna funktion anropas från formuläret då man skapar en mässa, den tar upp de fält som kan innehålla en valuta
	Den anropar sedan calculate.php och skickar med data i en POST-request till den filen och får sedan tillbaks det
	omvandlade värdet.
**/
function changeCurrency(){
	var oldval = defaultvalue;
	var customValues = $('#custom_fees').children().length;
	var fairPrice = $('#pricekvm').val();

	defaultvalue = $('.currency').val();
	$('.value').text(defaultvalue);

	if(customValues > 0){ // Om det finns några 'Custom Fee's' Inmatade
		$('#custom_fees').children().each(function(){
			var price = $(this).children('input').val();
			var elem = $(this).children('input');
			price.replace(' ', '');

			if(price.length > 0){
				showLoader(update_values_msg);
				$.ajax({
					url: 'public/ajax/calculate.php',
					type: 'POST',
					data: 'getConverted=1&from='+oldval+'&to='+defaultvalue+'&amount='+price
				}).success(function(response){
					elem.val(response);
						hideLoader();
				});
			}
		});
	}

	if(fairPrice > 0){ // Om det finns något pris för mässan inmatat.
		var elem = $('#pricekvm');
		var price = elem.val();
		price.replace(' ', '');
		if(price.length > 0){
			showLoader(update_values_msg);
			$.ajax({
				url: 'public/ajax/calculate.php',
				type: 'POST',
				data: 'getConverted=1&from='+oldval+'&to='+defaultvalue+'&amount='+price
			}).success(function(response){
				elem.val(response);
				hideLoader();
			});
		}
	}
}
