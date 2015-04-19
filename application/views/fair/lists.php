<script type="text/javascript">
	function printExcel(){
		document.location.href='<?php echo BASE_URL."fair/exportExcel/".$fair->get('id');?>';
	}
</script>
<h1><?php echo $headline; ?></h1>
<p><a class="button add" href="<?php echo BASE_URL?>article/create/<?php echo $fair->get('id'); ?>"><?php echo $create_link; ?></a></p>
<p><a class="button" onclick="printExcel()" ><?php echo $print_link; ?></a></p>
<p><a class="button" href="<?php echo BASE_URL."fair/exportPDF/".$fair->get('id');?>" ><?php echo $print_link; ?></a></p>
<form>
<input type="checkbox" /> SEK
<input type="checkbox" /> EUR
<input type="checkbox" /> USD
</form>
<table class="std_table">
	<thead>
		<tr>
			<th><?php echo $th_id; ?></th>
			<th><?php echo $th_name; ?></th>
			<th><?php echo $th_category; ?></th>
			<th><?php echo $th_price; ?></th>
			<th><?php echo $th_edit; ?></th>
			<th><?php echo $th_delete; ?></th>
		</tr>
	</thead>
	<tbody style="text-align:center;">
		<?php foreach($fair->get('list') as $list): ?>
		<tr>
			<td><?php echo $list->get('id');?></td>
			<td><?php echo $list->get('name');?></td>
			<td><?php echo $list->get('kategori');?></td>
			<td><?php echo $list->get('price');?></td>
			<td><a href="<?php echo BASE_URL?>article/edit/<?php echo $fair->get('id'); ?>/<?php echo $list->get('id');?>"><img src="<?php echo BASE_URL.'public/images/icons/pencil.png'?>"></img></a></td>
			<td><a href="<?php echo BASE_URL?>article/delete/<?php echo $fair->get('id'); ?>/<?php echo $list->get('id');?>"><img src="<?php echo BASE_URL.'public/images/icons/delete.png'?>"></img></a></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
