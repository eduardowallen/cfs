<style>
	#languages {
		display: none;
	}
</style>

<h1><?php echo $heading; ?></h1>

<div class="languages">
	<p>
		<a href="translate/language/eng"<?php if (LANGUAGE == 'eng') { echo ' class="selected"'; } ?>><img src="images/flag_gb.png" alt="English"/>&nbsp;English</a>
	</p>
	<p>
		<a href="translate/language/sv"<?php if (LANGUAGE == 'sv') { echo ' class="selected"'; } ?>><img src="images/flag_swe.png" alt="Svenska"/>&nbsp;Svenska</a>
	</p>
	<p>
		<a href="translate/language/de"<?php if (LANGUAGE == 'de') { echo ' class="selected"'; } ?>><img src="images/flag_ger.png" alt="Deutsch"/>&nbsp;Deutsch</a>
	</p>
	<p>
		<a href="translate/language/es"<?php if (LANGUAGE == 'es') { echo ' class="selected"'; } ?>><img src="images/flag_esp.png" alt="Espanol"/>&nbsp;Español</a>
	</p>
</div>