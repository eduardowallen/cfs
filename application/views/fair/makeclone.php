<script type="text/javascript">
	$(document).ready(function() {
		var submit_allowed = false;

		$("#name").keyup(function() {
			var val = $(this).val();
			$.ajax({
				url: 'ajax/fair.php',
				type: 'POST',
				data: 'checkName=' + val,
				success: function(response) {
					res = JSON.parse(response);
					$("#name_preview span").text(res.url);
					if (res.status == 'ok') {
						$("#name_preview span").css("color", "green");
					} else {
						$("#name_preview span").css("color", "red");
					}
				}
			});
		});

		// Bind contact_info så att info blir obligatorisk
		var check = setInterval(function(){
			if(strcmp(tinyMCE.get('contact_info'), "undefined")){
				bindMce();
			}
		}, 1);

		function strcmp(a, b)
		{   
		    return (a<b?-1:(a>b?1:0));  
		}
		

		function bindMce(){
			clearInterval(check);
			tinyMCE.get('contact_info').onKeyUp.add(function(ed, e) {
				$('#contact_info').html(tinyMCE.get('contact_info').getContent());
			});
		}

		$('#fair_clone_form').on('submit', function(e) {
			var form = $(this);

			if (!submit_allowed && form.data('valid')) {
				confirmBox(e, '<?php echo $dialog_clone_complete_info; ?>', function() {
					submit_allowed = true;
					form.submit();
				});
			}
		});
	});
</script>
<?php tiny_mce(); ?>

<h1><?php echo $clone_headline; ?></h1>

<form action="fair/makeclone/<?php echo $edit_id; ?>" method="post" id="fair_clone_form">
	<div class="form_column">
		<label for="name"><?php echo $name_label; ?> *</label>
		<input autocomplete="off" type="text" name="name" id="name" />
		<label style="font-style:italic; width:400px;" id="name_preview"><?php echo BASE_URL ?><span></span></label>
		
		<label for="windowtitle"><?php echo $window_title_label; ?> *</label>
		<input type="text" name="windowtitle" id="windowtitle" value="<?php echo $fair->get('windowtitle'); ?>"/>

		<label for="auto_publish"><?php echo $auto_publish_label; ?> (DD-MM-YYYY HH:MM GMT+1) *</label>
		<input class="datetime datepicker" type="text" name="auto_publish" id="auto_publish" value="<?php echo date('d-m-Y', $fair->get('auto_publish')); ?>"/>
		
		<label for="auto_close"><?php echo $auto_close_label; ?> (DD-MM-YYYY HH:MM GMT+1) *</label>
		<input class="datetime datepicker" type="text" name="auto_close" id="auto_close" value="<?php echo date('d-m-Y', $fair->get('auto_close')); ?>"/>

		<label for="auto_close_reserved"><?php echo $auto_close_reserved_label; ?> (DD-MM-YYYY HH:MM GMT+1) *</label>
		<input class="datetime datepicker" type="text" name="auto_close_reserved" id="auto_close_reserved" />

		<label for="contact_info"><?php echo $contact_label; ?> *</label>
		<textarea name="contact_info" id="contact_info"><?php echo $fair->get('contact_info'); ?></textarea>

		<p><input type="submit" name="save" value="<?php echo $clone_label; ?>"/></p>
	</div>
</form>
