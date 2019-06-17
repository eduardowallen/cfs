<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title><?php echo htmlspecialchars($position->get('name')) . ': ' . htmlspecialchars($exhibitor->get('company'));?></title>
		<base href="<?php echo BASE_URL; ?>" />
		<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet" />
		<link rel="stylesheet" href="css/generic.css" />
		<link rel="stylesheet" href="css/main.css" />
		<link rel="stylesheet" href="css/map.css" />
	</head>

	<body>
<style>
body {
	font-size: 14px;
}
.standSpaceName {
    font-weight: 600;
    color:#000;
}
.dialogue {
	width: -webkit-fill-available;
	top: auto;
	left: auto;
	margin-top: 0;
	margin-left: 0;
	max-width: auto;
}

</style>
<?php if (isset($error)): ?>
		<p><?php echo $error; ?></p>

<?php else: ?>
		<div id="presentation_dialogue" class="dialogue" style="display: block;">
			<h3 class="standSpaceName" style="margin-top:-3.5em;"><?php echo htmlspecialchars($position->get('name')) . ': ' . htmlspecialchars($exhibitor->get('company'));?></h3>
			<div class="info" style="margin-top:0.5em">
				<p>
					<strong><?php echo $label_status; ?>:</strong> <?php echo uh($translator->{$position->get('statusText')}); ?><br />
					<strong><?php echo $label_area; ?>:</strong> <?php echo $position->get('area'); ?>
				</p>
<?php	if ($position->get('status') == 1): ?>
				<p>
					<strong><?php echo $label_reserved_until; ?>:</strong> <?php echo $position->get('expires'); ?>
				</p>
<?php	endif; ?>
				<p>
					<strong><?php echo $label_commodity; ?>:</strong> <?php echo $exhibitor->get('commodity'); ?>
				</p>
				<p>
					<strong><?php echo $label_categories; ?>:</strong> <?php echo $category_names; ?>
				</p>
			<?php if ($option_texts): ?>
				<p>
					<strong><?php echo $label_options; ?>:</strong> <?php echo $option_texts; ?>
				</p>
			<?php endif; ?>
			</div>

			<h4 style="margin-top: 1em;"><?php echo $label_presentation; ?></h4>

			<div class="presentation" style="max-height: auto; overflow: visible;">
<?php	if (strlen($exhibitor->get('presentation')) < 1): ?>
				<?php echo $label_no_presentation_text; ?>
<?php	else: ?>
				<?php echo $exhibitor->get('presentation'); ?>
<?php	endif; ?>
			</div>

			<p class="website_link"><?php if ($exhibitor->get('website') != '') {
				$website = $exhibitor->get('website');
				if (strpos($website, 'http://') !== 0) {
					$website = 'http://' . $website;
				}

				echo '<strong>' . $label_website . ':</strong> <a href="' . $website . '" target="_blank">' . $exhibitor->get('website') . '</a>';
			} ?></p>
		</div>

		<script>
			window.print();
		</script>
<?php endif; ?>

	</body>

</html>