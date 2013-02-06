<script type="text/javascript" src="js/jquery-ui-1.8.20.custom.min.js"></script>
<script type="text/javascript" src="js/maptool.js"></script>
<script type="text/javascript">
	$(document).ready(function() {	
		maptool.init(<?php echo reset($fair->get('maps'))->get('id'); ?>);
	});
</script>

<ul id="map_nav">
	<?php foreach ($fair->get('maps') as $map): ?>
		<li id="map_link_<?php echo $map->get('id'); ?>"><?php echo $map->get('name'); ?></li>
	<?php endforeach; ?>
</ul>

<p id="zoomcontrols">
	<span id="in">+</span>
	<span id="out">-</span>
</p>

<div id="pancontrols">
	<img src="images/icons/pan_up.png" id="panup" alt=""/>
	<img src="images/icons/pan_right.png" id="panright" alt=""/>
	<img src="images/icons/pan_down.png" id="pandown" alt=""/>
	<img src="images/icons/pan_left.png" id="panleft" alt=""/>
</div>

<div id="mapHolder">
	<img src="<?php echo reset($fair->get('maps'))->get('image'); ?>" alt="" id="map"/>
</div>