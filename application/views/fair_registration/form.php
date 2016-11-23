<style>
.scrolltable-wrap{max-height:20em !important;}
#confirm_yes {margin-top: 10px;}
#content {max-width: 1280px;}
</style>

<form class="form" action="fairRegistration/form/<?php echo $fair->get('id'); ?>" method="POST">
	<h1><?php echo uh(sprintf($label_headline, $fair->get('name'))); ?></h1>

	<label for="apply_area"><?php echo uh($label_area); ?> *</label>
	<input type="text" name="area" id="apply_area" />
	
	<label class="label_medium" for="apply_commodity_input"><?php echo uh($translator->{'Commodity'}); ?> *</label>
	<textarea name="commodity" class="commodity_big" id="apply_commodity_input"><?php echo $me->get('commodity')?></textarea>

	<!-- Div för att välja kategori -->
	<label class="table_header" for="apply_category_scrollbox"><?php echo uh($translator->{'Categories'}); ?> *</label>
		<table class="std_table use-scrolltable" id="apply_category_scrollbox">
			<thead>
				<tr>
					<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
					<th data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($fair->get('categories') as $cat){ ?>
				<tr>
					<td class="left"><?php echo $cat->get('name') ?></td>
					<td>
						<input type="checkbox" id="<?php echo $cat->get('id') ?>" name="categories[]" value="<?php echo $cat->get('id') ?>" />
						<label class="squaredFour" for="<?php echo $cat->get('id') ?>" />
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>

<?php if ($fair->get('extraOptions')): ?>
		<!--  Extra tillval -->
	<label class="table_header" for="apply_option_scrollbox"><?php echo uh($translator->{'Extra options'}); ?></label>
		<table class="std_table use-scrolltable" id="apply_option_scrollbox">
			<thead>
				<tr>
					<th>ID</th>
					<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
					<th><?php echo uh($translator->{'Price'}); ?></th>
					<th data-sorter="false"><?php echo uh($translator->{'Choose'}); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($fair->get('extraOptions') as $extraOption) { ?>
					<?php if ($extraOption->get('required') == 1): ?>
					<tr>
						<td><?php echo $extraOption->get('custom_id') ?></td>
						<td class="left"><?php echo $extraOption->get('text') ?>*</td>
						<td><?php echo $extraOption->get('price') ?></td>
						<td>
							<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" name="options[]" value="<?php echo $extraOption->get('id') ?>" checked="checked" />
							<input type="checkbox" disabled="disabled" checked>
							<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
						</td>
					</tr>
				<?php else : ?>
					<tr>
						<td><?php echo $extraOption->get('custom_id') ?></td>
						<td class="left"><?php echo $extraOption->get('text') ?></td>
						<td><?php echo $extraOption->get('price') ?></td>
						<td>
							<input type="checkbox" id="<?php echo $extraOption->get('id') ?>" name="options[]" value="<?php echo $extraOption->get('id') ?>"/>
							<label class="squaredFour" for="<?php echo $extraOption->get('id') ?>" />
						</td>
					</tr>
					<?php endif; ?>
				<?php } ?>
			</tbody>
		</table>
<?php endif; ?>

<?php if ($fair->get('articles')): ?>
		<!--  Artiklar  -->
	<label class="table_header" for="apply_article_scrollbox"><?php echo uh($translator->{"Articles"}); ?></label>
		<table class="std_table use-scrolltable" id="apply_article_scrollbox">
			<thead>
				<tr>
					<th>ID</th>
					<th class="left"><?php echo uh($translator->{'Description'}); ?></th>
					<th><?php echo uh($translator->{'Price'}); ?></th>
					<th style="text-indent:-47px;" data-sorter="false"><?php echo uh($translator->{'Amount'}); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($fair->get('articles') as $article) { ?>
				<tr>
					<td><?php echo $article->get('custom_id') ?></td>
					<td class="left"><?php echo $article->get('text') ?></td>
					<td><?php echo $article->get('price') ?></td>
					<td class="td-number-span">
						<input type="text" class="form-control bfh-number" min="0" value="0" name="artamount[]" id="<?php echo $article->get('id') ?>" />
						<input type="checkbox" style="display:none;" value="<?php echo $article->get('id') ?>" name="articles[]" />
					</td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
<?php endif; ?>
<br />

	<label class="label_long" for="apply_message_input"><?php echo uh($translator->{'Message to organizer'}); ?></label>
	<textarea name="arranger_message" class="msg_to_organizer" id="apply_message_input"></textarea>
	<br />

	<p>
		<input type="submit" name="save" class="greenbutton bigbutton" id="confirm_yes" value="<?php echo uh($label_confirm); ?>"/>
	</p>
</form>

<script>
	$("form").submit(function(e) {
		$('#apply_article_scrollbox > tbody > tr > td > div').each(function() {
			if ($(this).children().val() > 0) {
				$(this).siblings().prop('checked', true);
			} else {
				$(this).siblings().prop('checked', false);
			}
		});
	});
</script>