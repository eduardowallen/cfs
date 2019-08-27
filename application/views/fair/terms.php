<?php
  global $translator;
?>
<script type="text/javascript">
/*
	$(document).ready(function() {

		// Bind terms så att info blir obligatorisk
		var check = setInterval(function(){
			if(strcmp(tinyMCE.get('terms'), "undefined")){
				bindMce();
			}
		}, 1);

		function strcmp(a, b)
		{   
		    return (a<b?-1:(a>b?1:0));  
		}
		function bindMce(){
			clearInterval(check);
			tinyMCE.get('terms').onKeyUp.add(function(ed, e) {
				$('#terms').html(tinyMCE.get('terms').getContent());
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
<h1><?php echo $terms_headline; ?> <?php echo $fair->get('name'); ?></h1>
<form action="fair/terms/<?php echo $edit_id; ?>" method="post" enctype="multipart/form-data">
		<?php tiny_mce(); ?>
		<div><label class="inline-block" for="terms"><?php echo $terms_label; ?></label>
		<img src="/images/icons/icon_help.png" class="helpicon" title="<?php echo uh($translator->{'This is the information which will be shown to visitors and exhibitors when they press the "terms" button on the map-view.'}); ?>" /></div>
		<textarea name="terms" id="terms"><?php echo $fair->get('terms'); ?></textarea>
		<input type="submit" style="margin-left: 0;" name="save" value="<?php echo $save_label; ?>" class="greenbutton bigbutton" />
</form>