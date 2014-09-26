		<h1><?php echo uh($label_details); ?></h1>

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

		<table class="std_table">
			<thead>
				<tr>
					<th><?php echo uh($label_recipient); ?></th>
					<th><?php echo uh($label_phone); ?></th>
					<th><?php echo uh($label_status); ?></th>
				</tr>
			</thead>
			<tbody>
<?php foreach ($sms->get('recipients') as $recipient): ?>
				<tr>
					<td><a href="exhibitor/profile/<?php echo $recipient->rec_user_id; ?>" class="showProfileLink"><?php echo uh($recipient->name . ' (' . $recipient->company . ')'); ?></a></td>
					<td><?php echo uh($recipient->phone); ?></td>
					<td><?php echo $recipient->sent_status; ?></td>
				</tr>
<?php endforeach; ?>
			</tbody>
		</table>
