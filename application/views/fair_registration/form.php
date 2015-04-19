			<style>
			.scrollbox {
				overflow-y: scroll;
				overflow-x: hidden;
				width: 300px;
				height: 100px;
				background-color: #eee;
				border: 1px solid #ccc;
			}

			.scrollbox label {
				padding: 0;
				font-weight: 400;
			}
			</style>

			<form action="fairRegistration/form/<?php echo $fair->get('id'); ?>" method="POST">
				<h1><?php echo uh(sprintf($label_headline, $fair->get('name'))); ?></h1>

				<label for="apply_category_scrollbox"><?php echo uh($label_category); ?> *</label>
				<div id="apply_category_scrollbox" class="scrollbox">
<?php foreach ($fair->get('categories') as $cat): ?>
					<label>
						<input type="checkbox" name="categories[]" value="<?php echo $cat->get('id'); ?>" />
						<?php echo uh($cat->get('name')); ?>
					</label>
<?php endforeach; ?>
				</div>

				<label for="apply_option_input"><?php echo uh($label_options); ?></label>
				<div id="apply_option_scrollbox" class="scrollbox">
<?php foreach ($fair->get('options') as $option): ?>
					<label>
						<input type="checkbox" name="options[]" value="<?php echo $option->get('id'); ?>" />
						<?php echo uh($option->get('text')); ?>
					</label>
<?php endforeach; ?>
				</div>

				<label for="apply_commodity_input"><?php echo uh($label_commodity); ?> *</label>
				<textarea name="commodity" id="apply_commodity_input" cols="50" rows="3"><?php echo uh($me->get('commodity')); ?></textarea>

				<label for="apply_message_input"><?php echo uh($label_message_organizer); ?></label>
				<textarea name="arranger_message" id="apply_message_input"></textarea>

				<label for="apply_area"><?php echo uh($label_area); ?></label>
				<input type="text" name="area" id="apply_area" />

				<p>
					<input type="submit" name="save" id="confirm_yes" value="<?php echo uh($label_confirm); ?>"/>
				</p>
			</form>