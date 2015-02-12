		<h1><?php echo $label_headline; ?></h1>


		<form action="user/terms?next=<?php echo $next; ?>" method="POST">
			<div class="terms-scrollbox">
				<?php echo $terms_content; ?>
			</div>
			<p>
				<button type="submit" name="approve" value="1"><?php echo $label_approve; ?></button>
				<button type="submit" name="decline" value="1"><?php echo $label_decline; ?></button>
			</p>
		</form>
