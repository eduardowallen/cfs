
<style>
	#content{max-width:1280px;}
	form, .std_table { clear: both; }
	.squaredFour{width:1.416em; height:1.416em;}
	.squaredFour:before{left:0.33em;top:0.33em;}
	.scrolltable-wrap{margin:0.5em 0em 1em 0em;}
	.no-search{max-height: 18em;}
	#review_list_div{max-height: 48em;}

.RDtextinput8 {
	width: 7em !important;
}
.RDtextinput10 {
	width: 7em !important;
}
.RDtextinput15 {
	width: 10em !important;
}
.RDtextinput30 {
	width: 20em !important;
}
.RDdiv {
	display:inline-block;
	margin: 0.5em;
}
</style>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>
<br />
<h1><?php echo $headline; ?> <?php echo $fair->get('name'); ?></h1>

<form action="fair/RDsettings/<?php echo $fairId; ?>" method="post">

	<div class="RDdiv">
	<label for="part_one"><?php echo $part_one_label; ?></label>
	<input type="text" maxlength="10" class="RDtextinput10" name="part_one" id="part_one" value="<?php echo $rd->get('part_one'); ?>"/>
	</div>
	<div class="RDdiv">
	<label for="part_two"><?php echo $part_two_label; ?></label>
	<input type="text" maxlength="10" class="RDtextinput10" name="part_two" id="part_two" value="<?php echo $rd->get('part_two'); ?>"/>
	</div>
	<div class="RDdiv">
	<label for="part_three"><?php echo $part_three_label; ?></label>
	<input type="text" maxlength="10" class="RDtextinput10" name="part_three" id="part_three" value="<?php echo $rd->get('part_three'); ?>"/>
	</div>
	<div class="RDdiv">
	<label for="part_four"><?php echo $part_four_label; ?></label>
	<input type="text" maxlength="10" class="RDtextinput10" name="part_four" id="part_four" value="<?php echo $rd->get('part_four'); ?>"/>
	</div>
	<div class="RDdiv">
	<label for="part_five"><?php echo $part_five_label; ?></label>
	<input type="text" maxlength="10" class="RDtextinput10" name="part_five" id="part_five" value="<?php echo $rd->get('part_five'); ?>"/>
	</div>
	<div class="RDdiv">
	<label for="part_six"><?php echo $part_six_label; ?></label>
	<input type="text" maxlength="10" class="RDtextinput10" name="part_six" id="part_six" value="<?php echo $rd->get('part_six'); ?>"/>
	</div>
	<div class="RDdiv">
	<label for="part_seven"><?php echo $part_seven_label; ?></label>
	<input type="text" maxlength="10" class="RDtextinput10" name="part_seven" id="part_seven" value="<?php echo $rd->get('part_seven'); ?>"/>
	</div>
	<div class="RDdiv">
	<label for="part_eight"><?php echo $part_eight_label; ?></label>
	<input type="text" maxlength="10" class="RDtextinput10" name="part_eight" id="part_eight" value="<?php echo $rd->get('part_eight'); ?>"/>
	</div>
	<div class="RDdiv">
	<label for="accrualkey"><?php echo $accrualkey_label; ?></label>
	<input type="text" maxlength="10" class="RDtextinput10" name="accrualkey" id="accrualkey" value="<?php echo $rd->get('fair'); ?>"/>
	</div>
	<div class="RDdiv">
	<input type="submit" class="greenbutton mediumbutton" name="save" value="<?php echo $save_label; ?>" />
	</div>
</form>



<?php if (count($extractions) > 0): ?>


<h2 class="tblsite"><?php echo $extractions_headline; ?></h2>
<table class="std_table use-scrolltable">
	<thead>
		<tr>
			<th><?php echo $tr_time; ?></th>
			<th><?php echo $tr_amount; ?></th>
			<th><?php echo $tr_view; ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($extractions as $extraction): ?>
		<tr>
			<td class="center"><?php echo date('d-m-Y H:i', $extraction['time']); ?></td>
			<td class="center"><?php echo $extraction['amount']; ?></td>
			<td class="center">
				<a href="raindance/extractions/<?php echo $extraction['id']; ?>">
					<img style="width:1.833em;" src="<?php echo BASE_URL; ?>images/icons/invoice.png" class="icon_img" />
				</a>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>


<?php endif; ?>