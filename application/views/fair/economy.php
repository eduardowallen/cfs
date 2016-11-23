
<style>
	#content{max-width:1280px;}
	form, .std_table { clear: both; }
	.squaredFour{width:1.416em; height:1.416em;}
	.squaredFour:before{left:0.33em;top:0.33em;}
	.scrolltable-wrap{margin:0.5em 0em 1em 0em;}
	.no-search{max-height: 18em;}

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
#ui-datepicker-div{z-index:1001 !important;}
</style>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>
<br />
<h1><?php echo $headline; ?> <?php echo $fair->get('name'); ?></h1>

<form action="fair/economy/<?php echo $fairId; ?>" method="post">

	<div class="RDdiv">
	<label for="invoicestatus"><?php echo $invoice_type; ?></label>
	<select name="invoicestatus" id="invoicestatus">
		<option value="1" <?php if (isset($_POST['invoicestatus']) && $_POST['invoicestatus'] == 1) echo ' selected="selected"'; ?>><?php echo $active; ?></option>
		<option value="2"<?php if (isset($_POST['invoicestatus']) && $_POST['invoicestatus'] == 2) echo ' selected="selected"'; ?>><?php echo $payed; ?></option>
		<option value="3"<?php if (isset($_POST['invoicestatus']) && $_POST['invoicestatus'] == 3) echo ' selected="selected"'; ?>><?php echo $credited; ?></option>
		<option value="4"<?php if (isset($_POST['invoicestatus']) && $_POST['invoicestatus'] == 4) echo ' selected="selected"'; ?>><?php echo $debased; ?></option>
	</select>
	</div>

	<div class="RDdiv">
	<label for="expires_from"><?php echo $expires_from; ?> (DD-MM-YYYY)</label>
		<input type="text" class="dialogueInput date datepicker" name="expires_from" id="expires_from" value="<?php if (isset($_POST['expires_from'])): echo $_POST['expires_from']; else: echo date('01-01-Y'); endif; ?>"/>
	</div>
	<div class="RDdiv">
	<label for="expires_to"><?php echo $expires_to; ?> (DD-MM-YYYY)</label>
		<input type="text" class="dialogueInput date datepicker" name="expires_to" id="expires_to" value="<?php if (isset($_POST['expires_to'])): echo $_POST['expires_to']; else: echo date('31-12-Y'); endif; ?>"/>
	</div>
	<div class="RDdiv">
	<input type="submit" class="greenbutton mediumbutton" name="submit" value="<?php echo $show_label; ?>" />
	</div>
</form>


<?php if (isset($_POST['submit'])): ?>
	<?php if (isset($positions)) { ?>
<h2 class="tblsite"><?php echo $result_headline; ?></h2>
<table class="std_table use-scrolltable">
	<thead>
		<tr>
			<th><?php echo $tr_type; ?></th>
			<th class="left"><?php echo $tr_name; ?></th>
			<th><?php echo $tr_amount; ?></th>
			<th><?php echo $tr_sum; ?> (<?php echo $fair->get("currency"); ?>)</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="center"><?php echo $tr_position; ?></td>
			<td class="left"><?php echo $tr_allpositions; ?></td>
			<td class="center"><?php echo $invoiceposamount; ?></td>
			<td class="center"><?php echo $invoiceposprice; ?></td>
		</tr>
		<?php if (isset($options) && count($options) > 0) { ?>
			<?php foreach ($options as $option): ?>
				<?php foreach ($option as $opt): ?>
					<?php if ($opt['COUNT(amount)'] > 0) { ?>
					<tr>
						<td class="center"><?php echo $tr_option; ?></td>
						<td class="left"><?php echo $opt['text']; ?></td>
						<td class="center"><?php echo $opt['COUNT(amount)']; ?></td>
						<td class="center"><?php echo $opt['COUNT(amount)']*$opt['price']; ?></td>
					</tr>
					<?php } ?>
				<?php endforeach; ?>
			<?php endforeach; ?>
		<?php } ?>
		<!--<?php echo var_dump($invoiceposprice); ?>-->
		<?php if (isset($articles) && $articles[0][0]['COUNT(amount)'] > 0) { ?>
			<?php foreach ($articles as $article): ?>
				<?php foreach ($article as $art): ?>
					<?php if ($art['COUNT(amount)'] > 0) { ?>
					<tr>
						<td class="center"><?php echo $tr_article; ?></td>
						<td class="left"><?php echo $art['text']; ?></td>
						<td class="center"><?php echo $articleamount; ?></td>
						<td class="center"><?php echo $articleamount*$art['price']; ?></td>
					</tr>
					<?php } ?>
				<?php endforeach; ?>
			<?php endforeach; ?>
		<?php } ?>
	</tbody>
</table>

<h2 class="tblsite"><?php echo $result_invoices_headline; ?></h2>
<table class="std_table use-scrolltable">
	<thead>
		<tr>
			<th><?php echo $tr_id; ?></th>
			<th><?php echo $tr_position; ?></th>
			<th><?php echo $tr_exhibitor; ?></th>
			<th><?php echo $tr_view; ?></th>
		</tr>
	</thead>
	<tbody>
		<!--<?php echo var_dump($invoices); ?>-->
		<?php foreach ($invoices as $invoice): ?>

		<tr>
			<td class="center"><?php echo $invoice['id']; ?></td>
			<td class="center"><?php echo $invoice['invoiceposname']; ?></td>
			<td class="center"><?php echo $invoice['r_name']; ?></td>
			<td class="center">
				<a href="<?php echo BASE_URL.'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$invoice['exhibitor'].'/'.str_replace('/', '-', $invoice['r_name']) . '-' . $invoice['invoiceposname'] . '-' . $invoice['id'] . '.pdf'?>" target="_blank" title="<?php echo $tr_viewinvoice; ?>">
					<img style="width:1.833em;" src="<?php echo BASE_URL; ?>images/icons/invoice.png" class="icon_img" />
				</a>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php } else { ?>
<?php echo $no_result_found; ?>
<?php } ?>
<?php endif; ?>