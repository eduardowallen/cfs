		<h1><?php echo uh($label_sms_stats); ?></h1>

		<table class="std_table">
			<thead>
				<tr>
					<th><?php echo uh($label_fair); ?></th>
					<th><?php echo uh($label_from); ?></th>
					<th><?php echo uh($label_sms); ?></th>
					<th><?php echo uh($label_num_recipients); ?></th>
					<th><?php echo uh($label_num_texts); ?></th>
					<th><?php echo uh($label_sent_time); ?></th>
					<th><?php echo uh($label_details); ?></th>
				</tr>
			</thead>
			<tbody>
<?php foreach ($sent_sms as $sms): ?>
				<tr>
					<td><?php echo uh($sms->fair_name); ?></td>
					<td><a href="exhibitor/profile/<?php echo $sms->author_user_id; ?>" class="showProfileLink"><?php echo uh($sms->author_name); ?></a></td>
					<td><?php echo uh(mb_substr($sms->text, 0, 160, 'UTF-8')); ?></td>
					<td><?php echo $sms->num_recipients; ?></td>
					<td><?php echo $sms->num_texts; ?></td>
					<td><?php echo date('d-m-Y H:i:s', $sms->sent_time); ?></td>
					<td class="center"><a href="sms/details/<?php echo $sms->id; ?>"><img src="images/icons/script.png" alt="<?php echo uh($label_details); ?>" /></a></td>
				</tr>
<?php endforeach; ?>
			</tbody>
		</table>
