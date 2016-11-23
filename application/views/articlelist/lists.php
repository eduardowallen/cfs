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
					$(this).replaceWith('<input type="text" name="'+$(this).attr('name')+'" value="'+$(this).text()+'"></input>');
				}
			});
		});
	});

	function editrow(id){
		$('#'+id).children('td:first-child').each(function(){
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
						$(this).parent().children().children('input:first-child').each(function(){
							if($(this).attr('class') == 'mandatory'){
								var value = "No";
								var check = $(this).prop('checked');
								if(check == true){value="Yes"}
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
		+'	<td><p name="list['+rand+'][name]" type="text"></p></td>'
		+'	<td><a onclick="alert(\'<?php echo $save_first?>\')" href="javascript:void(0)"><img src="images/icons/add.png" alt=""></a></td>'
		+'	<td><input class="newRadio" type="radio" name="list['+rand+'][active]" class="mandatory"></input></td>'
		+'	<td style="text-align:center;"><a onclick="alert(\'<?php echo $save_first?>\')" href="javascript:void(0)"><img src="images/icons/subarticles.png" alt=""></a></td>'
		+'	<td><img src="images/icons/pencil.png" onclick="editrow('+rand+')"></img></td>'
		+'	<td><img src="images/icons/delete.png" onclick="deleterow('+rand+')"></img></td>'
		+'</tr>';

		$('tbody').append(formrow);
		editrow(rand);

	}
</script>
<form action="articlelist/lists/<?php echo $fair?>" method="POST">
	<table class="std_table" style="width:100%;">
		<thead>
			<tr>
				<th style="width:70%;"><?php echo $th_name?></th>
				<th><?php echo $th_duplicate?></th>
				<th><?php echo $th_use?></th>
				<th><?php echo $th_categories?></th>
				<th><?php echo $th_edit?></th>
				<th><?php echo $th_delete?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($lists as $list):?>
			<tr id="<?php echo $list['id']?>"> 
				<td><p name="list[<?php echo $list['id']?>][name]"><?php echo $list['name']?></p></td>
				<td class="skip" style="display:none"><p name="list[<?php echo $list['id']?>][id]"><?php echo $list['id']?></input></td>
				<td><a href="articlelist/lists/<?php echo $list['id']?>/duplicate"><img src="images/icons/add.png" alt=""></img></a></td>
				<td><input type="radio" name="list_active" value="<?php echo $list['id']?>" class="mandatory" <?php echo $d = ($list['active'] == 1) ? 'checked' : ''?>></input></td>
				<td style="text-align:center;"><a href="articlelist/overview/<?php echo $fair?>/<?php echo $list['id']?>"><img src=images/icons/subarticles.png></img></a></td>
				<td><a onclick="editrow('<?php echo $list['id']?>')" href="javascript:void(0)"><img src="images/icons/pencil.png" alt="" /></a></td>
				<td><a href="articlelist/deleteList/<?php echo $fair.'/'.$list['id']?>"><img src="images/icons/delete.png" alt="" /></a></td>
			</tr>
			<?php endforeach?>
		</tbody>
	</table>

	<input type="submit" name="save" class="save" value="<?php echo $button_save?>" />
</form>
<a href="fair/edit/<?php echo $fair?>"><button onclick="javascript:void(0)" class="td_button"><?php echo $button_back?></button></a>
