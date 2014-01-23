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

<?php if (isset($error)): ?>
		<p><?php echo $error; ?></p>

<?php else: ?>
		<div id="more_info_dialogue" class="dialogue" style="display: block; top: 0; margin-top: 0;">
			<h3><?php echo htmlspecialchars($position->get('name')) . ': ' . htmlspecialchars($exhibitor->get('company'));?></h3>
			<div class="info">
				<p>
					<strong><?php echo $label_status; ?>:</strong> <?php echo $position->get('statusText'); ?><br />
					<strong><?php echo $label_area; ?> (m<sup>2</sup>):</strong> <?php echo $position->get('area'); ?>
				</p>
				<p>
					<strong><?php echo ucfirst($position->get('statusText')); ?> <?php echo $label_by; ?>:</strong> <?php echo htmlspecialchars($exhibitor->get('company')); ?>
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
			</div>

			<h4><?php echo $label_presentation; ?></h4>

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