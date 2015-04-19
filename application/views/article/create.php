<script type="text/javascript">
	var numrows = 1;
	function add_row(){
		if(numrows == 1){
			$('input#article_submit').val($('input#article_submit').val() + 's');
			
		}
		numrows++;
		$('#numrows').val(numrows);
		
		var formrow = $('<div class="article_row"><p> <?php echo $input_name?> </p>'
		+'<p class="pricetag"><?php echo $input_price." SEK"?></p>'
		+'<p class="pricetag"><?php echo $input_price." USD"?></p>'
		+'<p class="pricetag"><?php echo $input_price." EUR"?></p>'
		+'<p><?php echo $input_id?></p>'
		+'<p><?php echo $input_category?></p>'
		+'<p><input id="article_name_'+numrows+'" name="article_name_'+numrows+'" type="text" /></p>'
		+'<p class="pricetag"><input id="article_price_sek_'+numrows+'" name="article_price_sek_'+numrows+'" type="text" /></p>'
		+'<p class="pricetag"><input id="article_price_usd_'+numrows+'" name="article_price_usd_'+numrows+'" type="text" /></p>'
		+'<p class="pricetag"><input id="article_price_eur_'+numrows+'" name="article_price_eur_'+numrows+'" type="text" /></p>'
		+'<p><input id="article_id_'+numrows+'" name="article_id_'+numrows+'" type="text" />'
		+'<input id="article_category_'+numrows+'"  name="article_category_'+numrows+'" type="text" /></p></div>');
		$('#numrows').before(formrow);
	}
</script>

<h2><?php echo $headline?></h2>
<p><a class="button add" onclick="add_row()"><?php echo $create_link; ?></a></p>

<form id="articles" method="post" enctype="multipart/form-data" action="article/create/<?php echo $fair?>">
	<div class="article_row">
		<p> <?php echo $input_name?> </p>
		<p class="pricetag"> <?php echo $input_price." SEK"?> </p>
		<p class="pricetag"> <?php echo $input_price." USD"?> </p>
		<p class="pricetag"> <?php echo $input_price." EUR"?> </p>
		<p> <?php echo $input_id?> </p>
		<p> <?php echo $input_category?> </p>
		<p><input name="article_name_1" id="article_name" type="text" /></p>
		<p class="pricetag"><input name="article_price_sek_1" id="article_price_sek_1" type="text" /></p>
		<p class="pricetag"><input name="article_price_usd_1" id="article_price_usd_1" type="text" /></p>
		<p class="pricetag"><input name="article_price_eur_1" id="article_price_eur_1" type="text" /></p>
		<p><input name="article_id_1" id="article_id" type="text" /></p>
		<p><input name="article_category_1" id="article_category" type="text" /></p>
	</div>
	<input type="hidden" name="numrows" id="numrows" value="1"/>
	<input id="article_submit" name="article_submit" type="submit" value="<?php echo $input_submit?>" /></p>
</form>

