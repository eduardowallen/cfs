
<style>
label {
	max-width:100% !important;
	/*padding: 1em 0 0 3em !important;*/
}
input[type="radio"] {
	margin: 4px 12px 0;
	transform: scale(1.4);
}
</style>
		<h1><?php echo $label_headline; ?></h1>


		<form action="user/terms?next=<?php echo $next; ?>" method="POST">
			<div class="terms-scrollbox">
				<?php echo $terms_content; ?>
			</div>
			<br>
			<label for="newsletter" style="padding:0 !important;"><?php echo uh($translator->{'I want to subscribe to monthly newsletters from Chartbooking which contains important updates about upcoming events.'}); ?>*</label>
			<input type="radio" name="newsletter" value="1" required/><?php echo uh($translator->{'Yes'}); ?>
			<input type="radio" name="newsletter" value="0"/><?php echo uh($translator->{'No thanks'}); ?>
			<p>
				<button class="greenbutton mediumbutton" type="submit" name="approve" value="1"><?php echo $label_approve; ?></button>
				<button class="redbutton mediumbutton" type="submit" name="decline" value="1"><?php echo $label_decline; ?></button>
			</p>
		</form>
