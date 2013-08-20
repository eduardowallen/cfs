<script type="text/javascript">
	window.onload = function(e) {
		//window.print();
	}
</script>
<h1><?php echo $headline; ?></h1>

<p>
	<strong><?php echo $space ?>:</strong> <?php echo $exhibitor->get('position') ?><br/>
	<strong><?php echo $status ?>:</strong> <?php echo $translator->{$position->get('statusText')} ?><br/>
	<strong><?php echo $area ?>:</strong> <?php echo $position->get('area') ?>
</p>
<p>
	<strong><?php echo $company ?>:</strong> <?php echo $exhibitor->get('company') ?><br/>
	<strong><?php echo $website ?>:</strong> <?php echo $exhibitor->get('website') ?><br/>
	<strong><?php echo $commodity ?>: <?php echo $exhibitor->get('commodity') ?></strong>
</p>
<p>
	<strong><?php echo $presentation ?>:</strong><br/>
	<?php echo $exhibitor->get('presentation') ?>
</p>
