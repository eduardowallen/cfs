<?php
$export_columns = array(
	$translator->{'Notes'} => array(
		'exhibitor_name' => uh($label_exhibitor),
		'comment' => uh($label_note),
		'date' => uh($label_note_time),
		'author' => uh($translator->{'Author'}),
		'fair_name' => uh($translator->{'Fair'}),
		'position_name' => uh($translator->{'Stand space'}),
		'type' => uh($translator->{'Type of comment'})
	)
);
//$export_columns = array_merge($export_columns, $general_column_info);
?>
<style>
	#content{max-width:1280px;}
	form, .std_table { clear: both; }
</style>
		<script src="js/tablesearch.js<?php echo $unique?>"></script>
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
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>start/home'"><?php echo uh($translator->{'Go back'}); ?></button>
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
			<a href="" class="js-show-comment-dialog button" style="background-image: url('../images/icons/new_comment.png'); padding-left: 3.33em; background-size: 2em;" data-view="#comment_collection" data-close="true" data-template="comment_row"><?php echo uh($label_add_comment); ?></a>
		</p>

		<form action="comment/excel/<?php echo uh($filter_fair); ?>" method="POST">
			<div class="floatright right">
				<button type="submit" class="open-excel-export" title="<?php echo uh($label_export_excel); ?>" name="export_excel" data-for="comments"></button>
			</div>

			<table class="std_table use-scrolltable">
				<thead>
					<tr>
						<th class="left"><?php echo uh($label_exhibitor); ?></th>
						<th class="left"><?php echo uh($label_note); ?></th>
						<th class="left"><?php echo uh($label_author); ?></th>
						<th><?php echo uh($label_type); ?></th>
						<th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo uh($label_note_time); ?></th>
						<th data-sorter="false"><?php echo uh($label_headline); ?></th>
						<th data-sorter="false"><?php echo uh($label_edit); ?></th>
						<th data-sorter="false"><?php echo uh($label_delete); ?></th>
						<th data-sorter="false"><input type="checkbox" id="check-all-comments" class="check-all" data-group="comment-rows" />
							<label class="squaredFour" for="check-all-comments" />
						</th>
					</tr>
				</thead>
				<tbody id="comment_collection">
<?php foreach ($comments as $comment):
		require 'comment_row.php';
	  endforeach; ?>
				</tbody>
			</table>
		</form>
