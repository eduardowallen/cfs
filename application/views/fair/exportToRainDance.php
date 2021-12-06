
<style>


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
<br/>
<?php if (isset($invoiceids)) { ?>
<form action="fair/exportToRainDance/<?php echo $fairId; ?>" method="post">

		<div class="RDdiv">
		<label for="pos_part_one"><?php echo $part_one_label; ?></label>
		<input type="text" maxlength="10" class="RDtextinput10" name="pos_part_one" id="pos_part_one"/>
		</div>
		<div class="RDdiv">
		<label for="pos_part_two"><?php echo $part_two_label; ?></label>
		<input type="text" maxlength="10" class="RDtextinput10" name="pos_part_two" id="pos_part_two"/>
		</div>
		<div class="RDdiv">
		<label for="pos_part_three"><?php echo $part_three_label; ?></label>
		<input type="text" maxlength="10" class="RDtextinput10" name="pos_part_three" id="pos_part_three"/>
		</div>
		<div class="RDdiv">
		<label for="pos_part_four"><?php echo $part_four_label; ?></label>
		<input type="text" maxlength="10" class="RDtextinput10" name="pos_part_four" id="pos_part_four"/>
		</div>
		<div class="RDdiv">
		<label for="pos_part_five"><?php echo $part_five_label; ?></label>
		<input type="text" maxlength="10" class="RDtextinput10" name="pos_part_five" id="pos_part_five"/>
		</div>
		<div class="RDdiv">
		<label for="pos_part_six"><?php echo $part_six_label; ?></label>
		<input type="text" maxlength="10" class="RDtextinput10" name="pos_part_six" id="pos_part_six"/>
		</div>
		<div class="RDdiv">
		<label for="pos_part_seven"><?php echo $part_seven_label; ?></label>
		<input type="text" maxlength="10" class="RDtextinput10" name="pos_part_seven" id="pos_part_seven"/>
		</div>
		<div class="RDdiv">
		<label for="pos_part_eight"><?php echo $part_eight_label; ?></label>
		<input type="text" maxlength="10" class="RDtextinput10" name="pos_part_eight" id="pos_part_eight"/>
		</div>
		<div class="RDdiv">
		<label for="pos_amount"><?php echo $amount_label; ?></label>
		<input type="text" maxlength="15" class="RDtextinput15" name="pos_amount" id="pos_amount" value="<?php echo $invoiceposprice; ?>"/>
		</div>
		<div class="RDdiv">
		<label for="pos_accountingtext"><?php echo $accountingtext_label; ?></label>
		<input type="text" maxlength="30" class="RDtextinput30" name="pos_accountingtext" id="pos_accountingtext" value="<?php echo $positions_label; ?>"/>
		</div>
		<div class="RDdiv">
		<label for="pos_accountingdate"><?php echo $accountingdate_label; ?></label>
		<input type="text" maxlength="8" class="RDtextinput8" name="pos_accountingdate" id="pos_accountingdate" value="<?php echo $time_now; ?>"/>
		</div>
		<div class="RDdiv">
		<label for="pos_accrualkey"><?php echo $accrualkey_label; ?></label>
		<input type="text" maxlength="10" class="RDtextinput10" name="pos_accrualkey" id="pos_accrualkey"/>
		</div>
		<br/>
	<?php 

	if (isset($options)) {
		for($i = 0; $i < count($options); $i++) {

			foreach ($options[$i] as $option) {
				if ($option['COUNT(amount)'] > 0) {
					//echo ('<p style="margin:0;">'); echo $i; echo (' st * '); echo $option['price']; echo ('kr</p>');
		?>
					<div class="RDdiv">
					<label for="opt_part_one<?php echo $i; ?>"><?php echo $part_one_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="opt_part_one<?php echo $i; ?>" id="opt_part_one<?php echo $i; ?>" value="<?php echo $rd->get('opt_part_one'); ?>"/>
					</div>
					<div class="RDdiv">
					<label for="opt_part_two<?php echo $i; ?>"><?php echo $part_two_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="opt_part_two<?php echo $i; ?>" id="opt_part_two<?php echo $i; ?>" value="<?php echo $rd->get('opt_part_two'); ?>"/>
					</div>
					<div class="RDdiv">
					<label for="opt_part_three<?php echo $i; ?>"><?php echo $part_three_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="opt_part_three<?php echo $i; ?>" id="opt_part_three<?php echo $i; ?>" value="<?php echo $rd->get('opt_part_three'); ?>"/>
					</div>
					<div class="RDdiv">
					<label for="opt_part_four<?php echo $i; ?>"><?php echo $part_four_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="opt_part_four<?php echo $i; ?>" id="opt_part_four<?php echo $i; ?>" value="<?php echo $rd->get('opt_part_four'); ?>"/>
					</div>
					<div class="RDdiv">
					<label for="opt_part_five<?php echo $i; ?>"><?php echo $part_five_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="opt_part_five<?php echo $i; ?>" id="opt_part_five<?php echo $i; ?>" value="<?php echo $rd->get('opt_part_five'); ?>"/>
					</div>
					<div class="RDdiv">
					<label for="opt_part_six<?php echo $i; ?>"><?php echo $part_six_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="opt_part_six<?php echo $i; ?>" id="opt_part_six<?php echo $i; ?>" value="<?php echo $rd->get('opt_part_six'); ?>"/>
					</div>
					<div class="RDdiv">
					<label for="opt_part_seven<?php echo $i; ?>"><?php echo $part_seven_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="opt_part_seven<?php echo $i; ?>" id="opt_part_seven<?php echo $i; ?>" value="<?php echo $rd->get('opt_part_seven'); ?>"/>
					</div>
					<div class="RDdiv">
					<label for="opt_part_eight<?php echo $i; ?>"><?php echo $part_eight_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="opt_part_eight<?php echo $i; ?>" id="opt_part_eight<?php echo $i; ?>" value="<?php echo $rd->get('opt_part_eight'); ?>"/>
					</div>
					<div class="RDdiv">
					<label for="opt_amount<?php echo $i; ?>"><?php echo $amount_label; ?></label>
					<input type="text" maxlength="15" class="RDtextinput15" name="opt_amount<?php echo $i; ?>" id="opt_amount<?php echo $i; ?>" value="<?php echo $option['price']*$i; ?>"/>
					</div>
					<div class="RDdiv">
					<label for="opt_accountingtext<?php echo $i; ?>"><?php echo $accountingtext_label; ?></label>
					<input type="text" maxlength="30" class="RDtextinput30" name="opt_accountingtext<?php echo $i; ?>" id="opt_accountingtext<?php echo $i; ?>" value="<?php echo $option['text']; ?>"/>
					</div>
					<div class="RDdiv">
					<label for="opt_accountingdate<?php echo $i; ?>"><?php echo $accountingdate_label; ?></label>
					<input type="text" maxlength="8" class="RDtextinput8" name="opt_accountingdate<?php echo $i; ?>" id="opt_accountingdate<?php echo $i; ?>" value="<?php echo $time_now; ?>"/>
					</div>
					<div class="RDdiv">
					<label for="opt_accrualkey<?php echo $i; ?>"><?php echo $accrualkey_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="opt_accrualkey<?php echo $i; ?>" id="opt_accrualkey<?php echo $i; ?>"/>
					</div>
					<br/>
	<?php 
				}
			}
		} 
	}

	if (isset($articles)) {
		for($i = 0; $i < count($articles); $i++) {

			foreach ($articles[$i] as $article) {
				if ($article['COUNT(amount)'] > 0) {
					//echo ('<p style="margin:0;">'); echo $article['COUNT(amount)']; echo (' st * '); echo $article['price']; echo ('kr</p>');
	?>
					<div class="RDdiv">
					<label for="art_part_one<?php echo $i; ?>"><?php echo $part_one_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="art_part_one<?php echo $i; ?>" id="art_part_one<?php echo $i; ?>"/>
					</div>
					<div class="RDdiv">
					<label for="art_part_two<?php echo $i; ?>"><?php echo $part_two_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="art_part_two<?php echo $i; ?>" id="art_part_two<?php echo $i; ?>"/>
					</div>
					<div class="RDdiv">
					<label for="art_part_three<?php echo $i; ?>"><?php echo $part_three_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="art_part_three<?php echo $i; ?>" id="art_part_three<?php echo $i; ?>"/>
					</div>
					<div class="RDdiv">
					<label for="art_part_four<?php echo $i; ?>"><?php echo $part_four_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="art_part_four<?php echo $i; ?>" id="art_part_four<?php echo $i; ?>"/>
					</div>
					<div class="RDdiv">
					<label for="art_part_five<?php echo $i; ?>"><?php echo $part_five_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="art_part_five<?php echo $i; ?>" id="art_part_five<?php echo $i; ?>"/>
					</div>
					<div class="RDdiv">
					<label for="art_part_six<?php echo $i; ?>"><?php echo $part_six_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="art_part_six<?php echo $i; ?>" id="art_part_six<?php echo $i; ?>"/>
					</div>
					<div class="RDdiv">
					<label for="art_part_seven<?php echo $i; ?>"><?php echo $part_seven_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="art_part_seven<?php echo $i; ?>" id="art_part_seven<?php echo $i; ?>"/>
					</div>
					<div class="RDdiv">
					<label for="art_part_eight<?php echo $i; ?>"><?php echo $part_eight_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="art_part_eight<?php echo $i; ?>" id="art_part_eight<?php echo $i; ?>"/>
					</div>
					<div class="RDdiv">
					<label for="art_amount<?php echo $i; ?>"><?php echo $amount_label; ?></label>
					<input type="text" maxlength="15" class="RDtextinput15" name="art_amount<?php echo $i; ?>" id="art_amount<?php echo $i; ?>" value="<?php echo $article['price']*$article['COUNT(amount)']; ?>"/>
					</div>
					<div class="RDdiv">
					<label for="art_accountingtext<?php echo $i; ?>"><?php echo $accountingtext_label; ?></label>
					<input type="text" maxlength="30" class="RDtextinput30" name="art_accountingtext<?php echo $i; ?>" id="art_accountingtext<?php echo $i; ?>" value="<?php echo $article['text']; ?>"/>
					</div>
					<div class="RDdiv">
					<label for="art_accountingdate<?php echo $i; ?>"><?php echo $accountingdate_label; ?></label>
					<input type="text" maxlength="8" class="RDtextinput8" name="art_accountingdate<?php echo $i; ?>" id="art_accountingdate<?php echo $i; ?>" value="<?php echo $time_now; ?>"/>
					</div>
					<div class="RDdiv">
					<label for="art_accrualkey<?php echo $i; ?>"><?php echo $accrualkey_label; ?></label>
					<input type="text" maxlength="10" class="RDtextinput10" name="art_accrualkey<?php echo $i; ?>" id="art_accrualkey<?php echo $i; ?>"/>
					</div>
					<br/>
	<?php 
				}
			}
		}
	}

?>

<input style="float:right" type="submit" class="greenbutton mediumbutton" name="submit" value="<?php echo uh($translator->{'Export as Raindance file'}); ?>" />
</form>
<?php } else { ?>
<p> <?php echo $invoices_notfound?> </p>
<?php 
}
?>