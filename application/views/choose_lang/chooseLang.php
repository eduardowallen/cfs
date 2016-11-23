<style>
	#content {
		background: none;
		border: none;
	}
	#header, #languages, #logo, #new_header, #new_header_show, #new_header_hide, #dl-menu, .dl-menuwrapper{
		display: none !important;
	}
	.chooseLang {
		width: 15vw;
		max-width:14em;
	}
</style>

<div class="languages">
	<img id="logoBig" src="images/logo_chartbooker_white_8227.png" alt="Chartbooker International Fair System" />
	<h1><?php echo $heading; ?></h1>
	<div class="languageGroup">
		<p class="language">
			<a href="translate/language/eng"<?php if (LANGUAGE == 'eng') { echo ' class="selected"'; } ?>><img src="images/flag_en_177.png" class="chooseLang" alt="English"/><span>English</span></a>
		</p>
		<p class="language">
			<a href="translate/language/sv"<?php if (LANGUAGE == 'sv') { echo ' class="selected"'; } ?>><img src="images/flag_sv_177.png" class="chooseLang" alt="Svenska"/><span>Svenska</span></a>
		</p>
	</div>
	<div class="languageGroup">
		<p class="language">
			<a href="translate/language/de"<?php if (LANGUAGE == 'de') { echo ' class="selected"'; } ?>><img src="images/flag_de_177.png" class="chooseLang" alt="Deutsch"/><span>Deutsch</span></a>
		</p>
		<p class="language">
			<a href="translate/language/es"<?php if (LANGUAGE == 'es') { echo ' class="selected"'; } ?>><img src="images/flag_es_177.png" class="chooseLang" alt="Espanol"/><span>Español</span></a>
		</p>
	</div>
</div>
