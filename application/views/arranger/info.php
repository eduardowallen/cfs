<?php

global $mylabels;
$mylabels = $labels;

function showLabel($label) {
	global $mylabels;
//	$label = 'label_' . $label;
	$debug = '';

	if (!isset($mylabels[$label])) $mylabels[$label] = '(((' . $label . $debug . ')))';

//	echo '<label for="' . $label . '">' . $labels[$label] . '</label>: ';
	echo $mylabels[$label] . ': ';
}
?>
<h1><?php showLabel('header'); ?> <?php echo $user->get('name'); ?></h1>
<div id="arranger-info">
	<div class="float-left">
		<p><?php echo '<strong>'.$label_customer_nr.'</strong>: '.$user->get('customer_nr'); ?></p>
		<p><?php echo '<strong>'.$label_num_events.'</strong>: '.$num_events; ?></p>
	</div>
	<div class="float-left">
		<p><?php echo '<strong>'.$label_company.'</strong>: '.$user->get('company'); ?></p>
		<p><?php echo '<strong>'.$label_total_booked.'</strong>: '.$total_booked; ?></p>
	</div>
	<div class="float-left">
		<p><?php echo '<strong>'.$label_last_login.'</strong>: '; echo date('d-m-Y H:i:s', $user->get('last_login')); ?></p>
		<p><?php echo '<strong>'.$label_total_free.'</strong>: '; echo $total_free; ?></p>
	</div>
</div>

<div id="arranger-details">
<?php echo '<strong>'.$label_alias.'</strong>: '.$user->get('alias'); ?><br />
<?php echo '<strong>'.$label_company.'</strong>: '.$user->get('company'); ?><br />
<?php echo '<strong>'.$label_name.':</strong> '.$user->get('name'); ?><br />
<?php echo '<strong>'.$label_orgnr.':</strong> '.$user->get('orgnr'); ?><br />
<?php echo '<strong>'.$label_address.':</strong> '.$user->get('address'); ?><br />
<?php echo '<strong>'.$label_zipcode.':</strong> '.$user->get('zipcode'); ?><br />
<?php echo '<strong>'.$label_city.':</strong> '.$user->get('city'); ?><br />
<?php echo '<strong>'.$label_country.':</strong> '.$user->get('country'); ?><br />
<?php echo '<strong>'.$label_phone1.':</strong> '.$user->get('phone1'); ?><br />
<?php echo '<strong>'.$label_phone2.':</strong> '.$user->get('phone2'); ?><br />
<?php echo '<strong>'.$label_phone3.':</strong> '.$user->get('contact_phone'); ?><br />
<?php echo '<strong>'.$label_fax.':</strong> '.$user->get('fax'); ?><br />
<?php echo '<strong>'.$label_website.':</strong> <a target="_blank" href="' . $user->get('website') . '">' . $user->get('website') . '</a>'; ?><br />
<?php echo '<strong>'.$label_email.':</strong> <a href="mailto:' . $user->get('email') . '">' . $user->get('email') . '</a>'; ?><br />

</div>

<?php
	foreach ($fairs as $fair):
?>

<?php
if ($fair['approved'] == 2) {
	$app = $approved_locked;
} else if ($fair['approved'] == 1) {
	$app = $approved_active;
} else {
	$app = $approved_inactive;
}
?>

	<div class="fair-info">
<?php echo '<strong>'.$label_fair_name; ?></strong> <?php echo $fair['name']; ?><br />
<?php echo '<strong>'.$label_fair_approved.':</strong> '.$app; ?><br />
<?php echo '<strong>'.$label_fair_url.'</strong>: <a href="'.$fair['url'].'">'.$fair['url'].'</a><br />'; ?>
<?php echo '<strong>'.$label_fair_page_views.'</strong>: '.$fair['page_views']; ?><br />
<?php echo '<strong>'.$label_fair_occupied_spaces.'</strong>: '.$fair['occupied_spaces']; ?><br />
<?php echo '<strong>'.$label_fair_free_spaces.'</strong>: '.$fair['free_spaces']; ?><br />
<?php echo '<strong>'.$label_fair_creation_time.'</strong>: '; if ($fair['creation_time']) echo date('d-m-Y H:i:s', $fair['creation_time']); ?><br />
<?php echo '<strong>'.$auto_publish.'</strong>: '; echo date('d-m-Y H:i:s', $fair['auto_publish']); ?><br />
<?php echo '<strong>'.$auto_close.'</strong>: '; echo date('d-m-Y H:i:s', $fair['auto_close']); ?><br />
<?php echo '<strong>'.$label_fair_closing_time.'</strong>: '; if ($fair['closing_time']) echo date('d-m-Y H:i:s', $fair['closing_time']); ?><br />
	</div>
<?php
	endforeach;
?>
