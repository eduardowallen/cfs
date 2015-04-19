<?php
$export_columns = array(
	$translator->{'Notes'} => array(
		'exhibitor_name' => ujs($label_exhibitor),
		'comment' => ujs($label_note),
		'date' => ujs($label_note_time),
		'author' => ujs($translator->{'Author'}),
		'fair_name' => ujs($translator->{'Fair'}),
		'position_name' => ujs($translator->{'Stand space'}),
		'type' => ujs($translator->{'Type of comment'})
	)
);
//$export_columns = array_merge($export_columns, $general_column_info);
?>
<style>
	#content{max-width:1280px;}
	form, .std_table { clear: both; }
</style>
		<script src="js/tablesearch.js"></script>
		<script>
		$(function() {
			$('#filter_fair').on('change', function(e) {
				location.href = '<?php echo BASE_URL; ?>comment/index/' + e.target.value;
			});
		});

		var export_fields = {
			comments: <?php echo json_encode($export_columns); ?>
		};
		</script>

		<h1><?php echo uh($label_headline); ?></h1>

		<p>
			<strong><?php echo uh($label_select_fair); ?>:</strong>
			<select id="filter_fair">
				<option value="0"><?php echo uh($label_all_fairs); ?></option>
<?php foreach ($fairs as $fair): ?>
				<option value="<?php echo $fair->id; ?>"<?php if ($filter_fair == $fair->id) echo ' selected="selected"'; ?>><?php echo uh($fair->name); ?></option>
<?php endforeach; ?>
			</select>
		</p>

		<p>
			<a href="" class="js-show-comment-dialog button add" data-view="#comment_collection" data-close="true" data-template="comment_row"><?php echo uh($label_add_comment); ?></a>
		</p>

		<form action="comment/excel/<?php echo uh($filter_fair); ?>" method="POST">
			<div class="floatright right">
				<button type="submit" class="open-excel-export" name="export_excel" data-for="comments"><?php echo uh($label_export_excel); ?></button>
			</div>

			<table class="std_table use-scrolltable">
				<thead>
					<tr>
						<th><?php echo uh($label_author); ?></th>
						<th><?php echo uh($label_exhibitor); ?></th>
						<th><?php echo uh($label_note); ?></th>
						<th><?php echo uh($label_type); ?></th>
						<th><?php echo uh($label_note_time); ?></th>
						<th data-sorter="false"><?php echo uh($label_headline); ?></th>
						<th data-sorter="false"><?php echo uh($label_edit); ?></th>
						<th data-sorter="false"><?php echo uh($label_delete); ?></th>
						<th data-sorter="false"><input type="checkbox" class="check-all" data-group="comment-rows" /></th>
					</tr>
				</thead>
				<tbody id="comment_collection">
<?php foreach ($comments as $comment):
		require 'comment_row.php';
	  endforeach; ?>
				</tbody>
			</table>
		</form>
