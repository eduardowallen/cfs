<style>
	#content{max-width:1280px;}
	form, .std_table { clear: both; }
	.squaredFour{width:1.416em; height:1.416em;}
	.squaredFour:before{left:0.33em;top:0.33em;}
	.scrolltable-wrap{margin:0.5em 0em 1em 0em;}
	.no-search{max-height: 18em;}
	#review_list_div{max-height: 48em;}
</style>


<?php if (count($invoices) > 0): ?>


<h2 class="tblsite"><?php echo $invoices_headline; ?></h2>
<table class="std_table use-scrolltable">
	<thead>
		<tr>
			<th><?php echo $tr_id; ?></th>
			<th><?php echo $tr_exhibitorname; ?></th>
			<th><?php echo $tr_position; ?></th>
			<th><?php echo $tr_created; ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($invoices as $invoice): ?>
		<tr>
			<td class="center"><?php echo date('d-m-Y H:i', $invoice->get('time')); ?></td>
			<td class="center"><?php echo $invoice['r_name']; ?></td>
			<td class="center"><?php echo $invoice['posname']; ?></td>
			<td class="center">
				<a href="<?php echo BASE_URL.'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$invoice['exhibitor'].'/'.$invoice['r_name'] . '-' . $invoice['posname'] . '-' . $invoice['id'] . '.pdf'?>" target="_blank">
					<img style="width:1.833em;" src="<?php echo BASE_URL; ?>images/icons/invoice.png" class="icon_img" />
				</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>


<?php endif; ?>