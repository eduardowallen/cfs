<h2><?php echo $headline?></h2>
<p><a href="article/create/<?php echo $fair?>"class="button add""><?php echo $create_link; ?></a></p>
<form>
	<label for="name"><?php echo $th_catName?></label>
	<input id="name" name="categoryName" type="text"> </input>
	<label for="num"><?php echo $th_catNumber?></label>
	<input id="num" name="num" type="text"> </input>
	<label for="chk"><?php echo $ch_mandatory?></label>
	<input id="chk" name="chk" type="checkbox"> </input>

<table class="std_table">
	<thead>
		<th><?php echo $th_catNumber?></th>
		<th><?php echo $th_catName?></th>
		<th><?php echo $th_catOptional?></th>
		<th><?php echo $th_catEdit?></th>
		<th><?php echo $th_catDelete?></th>
	</thead>
</table>
	<input type="submit" value="<?php echo $button_save?>">
</form>
<form action="articlelist/overview/<?php echo $fair?>">
	<input type="submit" value="<?php echo $button_back?>"/>
</form>
