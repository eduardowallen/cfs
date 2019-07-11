<?php
  global $translator;
?>
<script type="text/javascript">
/*
	$(document).ready(function() {

		// Bind rules så att info blir obligatorisk
		var check = setInterval(function(){
			if(strcmp(tinyMCE.get('rules'), "undefined")){
				bindMce();
			}
		}, 1);

		function strcmp(a, b)
		{   
		    return (a<b?-1:(a>b?1:0));  
		}
		function bindMce(){
			clearInterval(check);
			tinyMCE.get('rules').onKeyUp.add(function(ed, e) {
				$('#rules').html(tinyMCE.get('rules').getContent());
			});
		}
	});
*/
</script>
<style>
	.mceEditor > table {
	width:560px !important;
	height:100px !important;
}
.mceEditor td.mceIframeContainer iframe {
	height: 500px !important;
	width: 1024px !important;
}

.defaultSkin * {
max-width:1024px !important;
}

label{
max-width:100% !important;
}
</style>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>
<br />
<h1><?php echo $rules_headline; ?> <?php echo $fair->get('name'); ?></h1>
<form action="fair/rules/<?php echo $edit_id; ?>" method="post" enctype="multipart/form-data">
		<?php tiny_mce(); ?>
		<div><label class="inline-block" for="rules"><?php echo $rules_label; ?></label>
		<img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'This is the information which will be shown to visitors and exhibitors when they press the "Rules" button on the map-view.'}); ?>" /></div>
		<textarea name="rules" id="rules"><?php echo $fair->get('rules'); ?></textarea>
		<input type="submit" style="margin-left: 0;" name="save" value="<?php echo $save_label; ?>" class="greenbutton bigbutton" />
</form>