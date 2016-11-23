/***
	Ursprungligen skriven av : Andréas Forsbom
	Kontakta : andreas@trinax.se vid frågor
	2013-08-20

**/


/***
	Kör detta när har laddats!
**/
$().ready(function(){

	// Kör funktionen calculate om x och y är satt
	$('p.area > input.x').keyup(function(){
		var x = $('p.area > input.x').val();
		var y = $('p.area > input.y').val();

		if(x > 0 && y > 0){
			calculate(x, y);
		}
	});

	$('p.area > input.y').keyup(function(){
		var x = $('p.area > input.x').val();
		var y = $('p.area > input.y').val();

		if(x > 0 && y > 0){
			calculate(x, y);
		}
	});
});

function calculate(x, y){
	$.ajax({
		url: 'public/ajax/calculate.php',
		type: 'POST',
		data: 'getKvm=1&x='+x+'&y='+y
	}).success(function(response){
		$('.area > input.ans').val(response);
	});
}

function calcProductPrice(antal, pris, obj){
	$.ajax({
		url: 'public/ajax/calculate.php',
		type: 'POST',
		data: 'multi=1&antal='+antal+'&pris='+pris
	}).success(function(response){
		$(obj).parent().parent().parent().children('.totprice').text(response);	
		tot();
	});
}

function calcAdd(tal1, tal2){
	var ans = 0;
	$.ajax({
		url: 'public/ajax/calculate.php',
		type: 'POST',
		data: 'sumi=1&tal1='+tal1+'&tal2='+tal2
	}).success(function(response){
		ans = response;
	});
	return response;
}

function calcAreaPrice(x, y, price){
	$.ajax({
		url: 'public/ajax/calculate.php',
		type: 'POST',
		data: 'getKvmPrice=1&x='+x+'&y='+y+'&price='+price
	}).success(function(response){
		$('.area > input.ans').val(response);
	});
}

function convertValue(from, to, amount, updateWho, type, extra){
	$.ajax({
		url: 'public/ajax/calculate.php',
		type: 'POST',
		data: 'getConverted=1&from='+from+'&to='+to+'&amount='+amount
	}).success(function(response){
		if(type == 'text'){
			updateWho.text(response);
			if(extra == "custom"){
				copyPriceOfCustom();
			}	
		}
		if(type == 'val'){
			updateWho.val(response);
			if(extra == "custom"){
				copyPriceOfCustom();
			}
		}
	});
}
