/***
	Ursprungligen skriven av : Andréas Forsbom
	Kontakta : andreas@trinax.se vid frågor
	2013-08-22

	Denna fil innehåller främst funktioner för att hantera uppladdning och hantering av nya kartor som binds till ett event/en mässa.
**/

/***
	Denna funktionen öppnar en IFRAME med uppladdningssidan
**/
function newMap(id){
	var iframe = '<iframe src="fairMap/create/'+id+'"></iframe>';
	$('#iframeholder').append(iframe);
}

/***

**/
function uploadMap(){
	var name = $('#name').val();
	var image = $('#file').val();
	$('.uploadMap').css('display', 'none');
}
