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
				$(this).replaceWith('<input type="text" name="'+$(this).attr('name')+'" value="'+$(this).text()+'"></input>');
			});
		});
	});

	function editrow(id){
		$('#'+id).children('td').each(function(){
			if($(this).children('p').attr('class') == 'mandatory'){
				var value = "";
				var check = $(this).text();
				if(check == 'Yes'){value="checked"}
				$(this).children('p').replaceWith('<input type="checkbox" class="mandatory" name="'+$(this).children().attr('name')+'" '+value+'></input>');
			} else {
				$(this).children('p').replaceWith('<input type="text" name="'+$(this).children('p').attr('name')+'" value="'+$(this).children('p').text()+'"></input>');
			}
			$(this).bind('keypress', function(e) {
				var value = $(this).children('input').val();
				if(e.keyCode == 13){

					$(this).parent().children().children('input').each(function(){
						if($(this).attr('class') == 'mandatory'){
							var value = "No";
							var check = $(this).prop('checked');
							if(check == true){value="Yes"}
							$(this).replaceWith('<p class="mandatory" name="'+$(this).attr('name')+'">'+value+'</p>');
						} else {
							$(this).replaceWith('<p name="'+$(this).attr('name')+'">'+$(this).val()+'</p>');
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
		+'<tr id="'+rand+'">'
		+'	<td><p name="article['+rand+'][ArticleNum]" type="text"></p></td>'
		+'	<td><p name="article['+rand+'][ArticleName]"  type="text"></p></td>'
		+'	<td><p name="article['+rand+'][ArticlePrice]" type="text">0</p></td>'
		+'	<td><img src="images/icons/pencil.png" onclick="editrow('+rand+')"></img></td>'
		+'	<td><img src="images/icons/delete.png" onclick="deleterow('+rand+')"></img></td>'
		+'</tr>';

		$('tbody').append(formrow);
		editrow(rand);
	}
</script>
<select id="value"><option value="SEK">SEK</option><option value="EUR">EUR</option><option value="USD">USD</option><option value="PEN">PEN</option></select>
<form id="form" action="article/create/<?php echo $fair?>/<?php echo $list?>/<?php echo $sublist?>" method="POST">
	<table class="std_table" style="width:100%;">
		<thead>
			<tr>
				<th><?php echo $th_articlenr?></th>
				<th style="width:70%;"><?php echo $th_name?></th>
				<th><?php echo $th_price?></th>
				<th><?php echo $th_edit?></th>
				<th><?php echo $th_delete?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($articles as $article) : ?>
			<?php if(empty($article['ArticleId']) || empty($article['ArticleNum']) || empty($article['ArticleName']) || empty($article['ArticlePrice'])) :?>
			<tr style="color:#f00;"id="<?php echo $article['ArticleId']?>">
			<?php else :?>
			<tr  id="<?php echo $article['ArticleId']?>">
			<?php endif;?>
				<td><p name="article[<?php echo $article['ArticleId']?>][ArticleNum]"><?php echo $article['ArticleNum']?></p></td>
				<td><p name="article[<?php echo $article['ArticleId']?>][ArticleName]"><?php echo $article['ArticleName']?></p></td>
				<td><p name="article[<?php echo $article['ArticleId']?>][ArticlePrice]"><?php echo $article['ArticlePrice']?></p></td>
				<td style="display:none;" class="skip"><p name="article[<?php echo $article['ArticleId']?>][ArticleId]"><?php echo $article['ArticleId']?></p></td>
				<td><a onclick="editrow('<?php echo $article['ArticleId']?>')" href="javascript:void(0)"><img src="images/icons/pencil.png" alt="" /></a></td>
				<td><a href="article/delete/<?php echo $fair.'/'.$list.'/'.$sublist.'/'.$article['ArticleId']?>"><img src="images/icons/delete.png" alt="" /></a></td>
			</tr>
			<?php endforeach?>
		</tbody>
	</table>
	<input type="submit" name="save" class="save" value="<?php echo $button_save?>" />
</form>
<a href="articlelist/subcategories/<?php echo $fair?>/<?php echo $list?>"><button onclick="javascript:void(0)" class="td_button"><?php echo $button_back?></button></a>
