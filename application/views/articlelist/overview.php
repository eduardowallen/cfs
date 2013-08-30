<h1><?php echo $headline?></h1>
<p><a class="button add" href="javascript:void(0)" onclick="add_row()"><?php echo $create_link; ?></a></p>
<h3><?php echo $desc?></h3>
<style>
	table tbody tr td input[type=text]{width:100%; padding:0; margin:0;}
	table tbody tr td p{margin:0;}
</style>
<script type="text/javascript">
	$().ready(function(){
		$('.save').bind('click', function(){
			$('tbody td').children('p').each(function(){
				if($(this).parent('td').attr('class') == "new" || $(this).parent('td').attr('class') == "edited"){
					if($(this).attr('class') == 'mandatory'){
						var value = "";
						var check = $(this).text();
						if(check == 'Yes'){value="checked"}
						$(this).replaceWith('<input type="checkbox" class="mandatory" name="'+$(this).attr('name')+'" '+value+'></input>');
					} else {
						$(this).replaceWith('<input type="text" name="'+$(this).attr('name')+'" value="'+$(this).text()+'"></input>');
					}
				}
			});
		});
	});

	function editrow(id){
		$('#'+id).children('td').each(function(){
				$(this).attr('class', 'edited');
				if($(this).children('p').attr('class') == 'mandatory'){
					var value = "";
					var check = $(this).text();
					if(check == 'Yes'){value="checked"}
					$(this).children('p').replaceWith('<input type="checkbox" class="mandatory" name="'+$(this).children().attr('name')+'" '+value+'></input>');
				} else {
					$(this).children('p').replaceWith('<input type="text" name="'+$(this).children('p').attr('name')+'" value="'+$(this).children('p').text()+'"></input>');
				}
				$(this).bind('keypress', function(e) {
					if(e.keyCode == 13){
						$(this).parent().children().children('input').each(function(){
							if($(this).attr('class') == 'mandatory'){
								var value = "No";
								var check = $(this).prop('checked');
								if(check == true){value="Yes"}
								$(this).replaceWith('<p class="mandatory" name="'+$(this).attr('name')+'">'+value+'</p>');
							} else {
								if($(this).attr('class') == "skip"){
									
								} else {
									$(this).replaceWith('<p name="'+$(this).attr('name')+'">'+$(this).val()+'</p>');
								}
							}
						});
					}
					$('.save').off('keypress');
				});
		});
	}
	function add_row(){
		var rand = Math.floor(Math.random() * 50000);

		var formrow = ''
		+'<tr id="'+rand+'" class="new">'
		+'	<td><p name="category['+rand+'][CategoryNum]" type="text"></p></td>'
		+'	<td><p name="category['+rand+'][CategoryName]"  type="text"></p></td>'
		+'	<td><p name="category['+rand+'][CategoryOptional]" class="mandatory" type="text">No</p></td>'
		+'	<td><a onclick="alert(\'<?php echo $save_first?>\')" href="javascript:void(0)"><img src="images/icons/icon_utstallarlista.png" alt=""></a></td>'
		+'	<td><img src="images/icons/pencil.png" onclick="editrow('+rand+')"></img></td>'
		+'	<td><img src="images/icons/delete.png" onclick="deleterow('+rand+')"></img></td>'
		+'</tr>';

		$('tbody').append(formrow);
		editrow(rand);
	}
</script>
<form action="articlelist/overview/<?php echo $fair?>/<?php echo $list?>" method="POST">
	<table class="std_table" style="width:100%;">
		<thead>
			<tr>
				<th><?php echo $th_catNumber?></th>
				<th style="width:70%;"><?php echo $th_catName?></th>
				<th><?php echo $th_catOptional?></th>
				<th><?php echo $th_subCategories?></th>
				<th><?php echo $th_catEdit?></th>
				<th><?php echo $th_catDelete?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($categories as $category):?>
			<?php if(empty($category['CategoryName']) || empty($category['CategoryNum'])) :?>
				<tr style="color:#f00;" id="<?php echo $category['CategoryId']?>">
			<?php else:?>
				<tr id="<?php echo $category['CategoryId']?>">
			<?php endif; ?>

				<td><p name="category[<?php echo $category['CategoryId']?>][CategoryNum]"><?php echo $category['CategoryNum']?></p></td>
				<td><p name="category[<?php echo $category['CategoryId']?>][CategoryName]"><?php echo $category['CategoryName']?></p></td>
				<td><p name="category[<?php echo $category['CategoryId']?>][CategoryOptional]" class="mandatory"><?php echo $d = ($category['CategoryOptional'] == 1) ? $Yes : $No?></p></td>
				<td class="skip" style="display:none"><p name="category[<?php echo $category['CategoryId']?>][CategoryId]"><?php echo $category['CategoryId']?></input></td>
				<td><a href="articlelist/subcategories/<?php echo $category['CategoryFair'].'/'.$category['CategoryId']?>/null/null/<?php echo $list?>"><img src="images/icons/subarticles.png" alt="" /></a></td>
				<td><a onclick="editrow('<?php echo $category['CategoryId']?>')" href="javascript:void(0)"><img src="images/icons/pencil.png" alt="" /></a></td>
				<td><a href="articlelist/delete/<?php echo $category['CategoryFair'].'/'.$category['CategoryId']?>"><img src="images/icons/delete.png" alt="" /></a></td>
			</tr>
			<?php endforeach?>
		</tbody>
	</table>
	<button type="submit" class="save" name="save" class="td_button"><?php echo $save_label?></button>
</form>
<a href="articlelist/lists/<?php echo $fair?>"><button onclick="javascript:void(0)" class="td_button"><?php echo $button_back?></button></a>
