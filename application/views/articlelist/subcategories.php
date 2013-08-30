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
		+'	<td style="text-align:center;" onclick="alert(\'<?php echo $save_first?>\')"><img src=images/icons/subarticles.png></img></td>'
		+'	<td><p name="subcategory['+rand+'][name]"  type="text"></p></td>'
		+'	<td><img src="images/icons/pencil.png" onclick="editrow('+rand+')"></img></td>'
		+'	<td><img src="images/icons/delete.png" onclick="deleterow('+rand+')"></img></td>'
		+'</tr>';

		$('tbody').append(formrow);
		editrow(rand);
	}
</script>
<form action="articlelist/subcategories/<?php echo $fair?>/<?php echo $headlistid?>" method="POST">
	<table class="std_table" style="width:100%;">
		<thead>
			<tr>
				<th><?php echo $th_catNumber?></th>
				<th style="width:70%;"><?php echo $th_catName?></th>
				<th><?php echo $th_catEdit?></th>
				<th><?php echo $th_catDelete?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($subcategories as $category) : ?>
			<?php if(empty($category['id']) || empty($category['name'])) :?>
				<tr style="color:#f00;" id="<?php echo $category['id']?>">
			<?php else: ?>
				<tr id="<?php echo $category['id']?>">
			<?php endif;?>	
				<td style="text-align:center;"><a href="article/create/<?php echo $fair?>/<?php echo $category['parentcategory']?>/<?php echo $category['id']?>"><img src=images/icons/subarticles.png></img></a></td>
				<td><p name="subcategory[<?php echo $category['id']?>][name]"><?php echo $category['name']?></p></td>
				<td class="skip" style="display:none"><p name="subcategory[<?php echo $category['id']?>][category]"><?php echo $category['id']?></input></td>
				<td><a onclick="editrow('<?php echo $category['id']?>')" href="javascript:void(0)"><img src="images/icons/pencil.png" alt="" /></a></td>
				<td><a href="articlelist/subcategories/<?php echo $fair?>/<?php echo $category['parentcategory'].'/'.$category['id']?>/delete/"><img src="images/icons/delete.png" alt="" /></a></td>
			</tr>
			<?php endforeach?>
		</tbody>
	</table>

	<input type="submit" name="save" class="save" value="<?php echo $button_save?>" />
</form>
<a href="articlelist/overview/<?php echo $fair?>/<?php echo $list?>"><button onclick="javascript:void(0)" class="td_button"><?php echo $button_back?></button></a>
