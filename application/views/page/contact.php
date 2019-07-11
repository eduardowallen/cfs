<h1 style="text-align:left;"><?php echo $headline; ?></h1>
<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" />
<?php if (!$no_event) { ?>
	<h1><?php echo $eventName; ?></h1>
	<span class="rulesSpan"><?php echo $dates, ': ';?><?php echo (strftime("%e", $eventstart) == strftime("%e", $eventstop)) ? strftime("%e", $eventstart) : strftime("%e", $eventstart).'-'.strftime("%e", $eventstop); ?><?php echo ' ', strftime("%B", $eventstart); ?></span><span class="rulesSpan" style="margin-left: 2em;"><?php echo $openinghours, ': ', date('H:i', $eventstart), ' - ', date('H:i', $eventstop); ?></span>
<?php  } ?>
<div id="contact_content" style="margin-top:1em; overflow:auto; max-height:70vh">
	<?php echo $content; ?>
</div>