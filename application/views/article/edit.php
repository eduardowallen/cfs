<h2><?php echo $headline?></h2>
<form id="articles" method="post" enctype="multipart/form-data" action="article/edit/<?php echo $fair?>/<?php echo $art_id?>">
	<div class="article_row">
		<p> <?php echo $input_name?> </p>
		<p> <?php echo $input_price?> </p>
		<p> <?php echo $input_id?> </p>
		<p> <?php echo $input_category?> </p>
		<?php echo $article_name;?>
		<p><input name="article_name_1" id="article_name" type="text" value="<?php echo $art_name?>" /></p>
		<p><input name="article_price_1" id="article_price" type="text" value="<?php echo $art_price?>" /></p>
		<p><input name="article_id_1" id="article_id" type="text" value="<?php echo $art_id?>" /></p>
		<p><input name="article_category_1" id="article_category" type="text" value="<?php echo $art_cat?>" /></p>
	</div>
	<input id="article_submit" name="article_submit" type="submit" value="<?php echo $input_submit?>" /></p>
</form>

