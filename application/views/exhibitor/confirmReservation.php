<?php if (userLevel() == 1): ?>
	<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>exhibitor/myBookings'"><?php echo uh($translator->{'Go back'}); ?></button>
<?php else: ?>
	<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>start/home'"><?php echo uh($translator->{'Go back'}); ?></button>
<?php endif; ?>

<br>
<p style="font-size:1.5em">
<?php if ($type == 'accept') { ?>

<?php echo $accepted, ' ', $position, ' ', uh($translator->{'on the event'}), ' ', $fairname; ?>

<?php } else if ($type == 'deny'){ ?>

<?php echo $denied, ' ', $position, ' ', uh($translator->{'on the event'}), ' ', $fairname; ?>

<?php } else if ($type == 'linkused') { ?>

<?php echo $linkused; ?>

<?php } else { ?>

<?php echo $error; ?>

<?php } ?>
</p>