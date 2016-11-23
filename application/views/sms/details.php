<script type="text/javascript" src="js/tablesearch.js<?php echo $unique?>"></script>

<style>
	#content{max-width:1280px;}
	form, .std_table { clear: both; }
	.squaredFour{width:1.416em; height:1.416em;}
	.squaredFour:before{left:0.33em;top:0.33em;}
	.scrolltable-wrap{margin:0.5em 0em 1em 0em;}
	.no-search{max-height: 18em;}
	#review_list_div{max-height: 48em;}
</style>

<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>sms'"><?php echo uh($translator->{'Go back'}); ?></button>
<br />
		<h1><?php echo uh($label_details); ?></h1>

		<?php if (userlevel() == 4): ?>
		<p>
			Felkodsöversättning ser ut på följande vis:<br/>
			<strong>0</strong>  Inget fel. SMS skickat korrekt.<br/>
			<strong>1</strong>  Inte skickat med USERNAME, PASSWORD, NR, TYPE eller DATA<br/>
			<strong>2</strong>  USERNAME eller PASSWORD är fel.<br/>
			<strong>3</strong>  Inte tillräckligt med pengar på ditt MO-SMS konto för att skicka detta anrop.<br/>
			<strong>4</strong>  Felaktig TYPE (skall vara "text" eller "wap").<br/>
			<strong>5</strong>  Kan inte komma åt filen/URL:en. Gäller endast om type=”wap”.<br/>
			<strong>6</strong>  Ingen filändelse i urlen. Gäller endast om type=”wap”.<br/>
			<strong>7</strong>  Mottagarnummer i fel format (Korrekt: ”0701234567”).<br/>
		</p>
	<?php endif; ?>
		<p>
			<?php echo uh($label_fair); ?>: <strong><?php echo uh($sms->get('fair')->get('name')); ?></strong><br />
			<?php echo uh($label_from); ?>: <strong><?php echo uh($sms->get('author')->get('name') . ' (' . $sms->get('author')->get('company') . ')'); ?></strong><br />
			<?php echo uh($label_num_recipients); ?>: <strong><?php echo count($sms->get('recipients')); ?></strong><br />
			<?php echo uh($label_num_texts); ?>: <strong><?php echo uh($sms->get('num_texts')); ?></strong><br />
			<?php echo uh($label_sent_time); ?>: <strong><?php echo date('d-m-Y H:i:s', $sms->get('sent_time')); ?></strong>
		</p>

		<p>
			<?php echo nl2br(uh($sms->get('text'))); ?>
		</p>

		<table class="std_table use-scrolltable">
			<thead>
				<tr>
					<th class="left"><?php echo uh($label_recipient_company); ?></th>
					<th class="left"><?php echo uh($label_recipient_name); ?></th>
					<th><?php echo uh($label_phone); ?></th>
					<?php if (userlevel() == 4): ?>
					<th><?php echo uh($label_status); ?></th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
<?php foreach ($sms->get('recipients') as $recipient): ?>
				<tr>
					<td class="left"><a href="exhibitor/profile/<?php echo $recipient->rec_user_id; ?>" class="showProfileLink"><?php echo uh($recipient->company); ?></a></td>
					<td class="left"><?php echo uh($recipient->name); ?></td>
					<td><?php echo uh($recipient->phone); ?></td>
					<?php if (userlevel() == 4): ?>
					<td><?php echo $recipient->sent_status; ?></td>
				<?php endif; ?>
				</tr>
<?php endforeach; ?>
			</tbody>
		</table>
