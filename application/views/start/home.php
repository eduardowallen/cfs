<style>
	#content {
		background: none;
		border: none;
		width: auto;
		margin: auto;
	}
	#HPLogo {
		width: 15vw;
		max-width: 15vw;
	}
	.HPBigIcon {
		display: inline;
		padding: 1em 0em;
		max-width: 5vw;
	}
	.HPMediumIcon {
		padding: 1em 0em;
		max-width: 3vw;
	}
	.HPSmallIcon {
		max-width: 3vw;
	}
	hr {
		margin: 1em 0em;
		border-top: 1px solid #128913;
	}
	#HPMainDiv {
		max-width: 80em;
	}
	#HP2ndDiv {
		max-width: 70%;
		clear: both;
	}
	#column {
		padding-right: 3vw;
		width: 40%;
	}
	.inline {
		display:inline;
	}
	.HPh1 {
		font-size: 2em;
		word-break: break-word;
		display: inline-block;
		max-width: 10em;
		margin-left:0.5em;
		vertical-align: -webkit-baseline-middle;
		margin-bottom: 0.3em;
		padding: 0;
	}
	.HPh1Map {
		font-size: 2em;
		margin-bottom: 0.3em;
		padding: 0;
	}
	.HPTop {
		display:inline;
	}
	h2 {
		font-weight: 600;
	}
	h3 {
		font-size: 1.2em;
		font-weight: 600;
		margin: 0;
		color: black;
	}
	h4 {
		font-size: 1.2em;
		font-weight: 600;
	}
	a:hover {
		text-decoration: none;
		cursor:pointer;
	}

</style>
<div style="max-width: 80%">
	<img id="HPLogo" src="images/button_icons/Chartbooker%20Fair%20System.png" />
<?php if (userLevel() > 0) { ?>
	<a class="inline" style="margin-top:1em; float:right; text-align:center; margin-left: 1em;" href="user/logout"><img class="HPSmallIcon" src="images/logout.png" />
	<p style="margin:0;"><?php echo uh($translator->{'Logout'}); ?></p></a>
	<a class="inline" style="margin-top:1em; float:right; text-align:center;" href="user/changePassword"><img class="HPSmallIcon" src="images/password.png" />
	<p style="margin:0;"><?php echo uh($translator->{'Change password'}); ?></p></a>
<?php } ?>
	<hr>
</div>

<div style="max-width:80%; display:block;">
	<div style="float:left;">
		<p style="font-size:1.2em; margin:0; display:inline-block;"><?php echo $heading; ?></p>
		<h2><?php echo $name; ?></h2>
	</div>
	<div style="float:right;">
		<?php if (isset($currentfair)): ?>
		<p style="font-size:1.2em; margin:0; display:inline-block;"><?php echo $heading_fair; ?></p>
		<h2><?php echo $currentfair; ?></h2>
		<?php endif; ?>
	</div>
</div>
<?php if (userLevel() == 1) { ?>

	<div id="HPMainDiv">
		<div id="HP2ndDiv">

		</div>
		<div id="column">
			<a href="mapTool/map/<?php echo $_SESSION['user_fair']; ?>">
			<img class="HPBigIcon" src="images/event_map.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'Go to map'}); ?></h1></a>
			<p><?php echo uh($translator->{'View and apply for stand spaces for the last visited event.'}); ?></p>
			<br/>

			<a href="user/accountSettings" class="HPTop">
			<img class="HPBigIcon" src="images/my_profile.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'My account'}); ?></h1></a>
			<p><?php echo uh($translator->{'View and edit your company details, presentation, upload your logo, etc.'}); ?></p>
			<br/>
			<img class="HPBigIcon" src="images/invoice.png" />
			<h1 class="HPh1"><?php echo ($translator->{'My invoices <br/>(development in progress)'}); ?></h1>
			<p><?php echo uh($translator->{'We are currently deveoloping this soon-to-be function for you exhibitors to view all your invoices for the events that you have or will attend to.'}); ?></p>
		</div>

		<div id="column">
			<a href="fair/search" class="HPTop">
			<img class="HPBigIcon" src="images/search_event.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'Search for events'}); ?></h1></a>
			<p><?php echo uh($translator->{'Find all the events that are currently accepting applications from exhibitors.'}); ?></p>
			<br/>

			<a href="exhibitor/myBookings" class="HPTop">
			<img class="HPBigIcon" src="images/my_bookings.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'My bookings'}); ?></h1></a>
			<p><?php echo uh($translator->{'View all your bookings, reservations and applications that you have made so far. You can view detailed information about each and every type of booking.'}); ?></p>
			<br/>

		</div>
	<hr style="max-width:80%;">
		<h4 style="display:inline-block;"><?php echo uh($translator->{'CFS Customer Support'}); ?></h4>
		<br/>

		<a style="margin:3em 2em 0 0;" class="helpLink inline">
		<img class="HPMediumIcon inline" src="images/help.png" />
		<h4 class="inline" style="margin-left: 1em"><?php echo uh($translator->{'FAQ'}); ?></h4></a>

		<!--<h3 style="padding: 0"><?php echo uh($translator->{'Contact support'}); ?></h3>-->

		<a style="margin:3em 2em 0 0;" href="mailto:info@chartbooker.com" target="_top" class="inline">
		<img class="HPMediumIcon inline" src="images/mail.png" />
		<h4 class="inline">info@chartbooker.com</h4></a>

		<img class="HPMediumIcon inline" src="images/phone.png" />
		<h4 class="inline">0760508882</h4>
	</div>
<?php } ?>

<?php if (userLevel() == 2) { ?>

	<div id="HPMainDiv">
		<div id="HP2ndDiv">

		</div>
		<div id="column">

			<a href="mapTool/map/<?php echo $_SESSION['user_fair']; ?>">
			<img class="HPBigIcon" src="images/event_map.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'Go to map'}); ?></h1></a>
			<p><?php echo uh($translator->{'View the event map for this event.'}); ?></p>
			<br/>

			<a href="administrator/newReservations" class="HPTop">
			<img class="HPBigIcon" src="images/my_bookings.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'New reservations'}); ?></h1></a>
			<p><?php echo uh($translator->{'View bookings, reservations and applications for this event.'}); ?></p>
			<br/>

			<a href="comment" class="HPTop">
			<img class="HPBigIcon" src="images/comments.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'Comments'}); ?></h1></a>
			<p><?php echo uh($translator->{'View all comments that you have made for exhibitors.'}); ?></p>
			<br/>
		</div>

		<div id="column">

			<a href="exhibitor/forFair" class="HPTop">
			<img class="HPBigIcon" src="images/administrators.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'Exhibitors'}); ?></h1></a>
			<p><?php echo uh($translator->{'View the exhibitors of this event.'}); ?></p>
			<br/>

			<a href="administrator/invoices" class="HPTop">
			<img class="HPBigIcon" src="images/invoice.png" />
			<h1 class="HPh1"><?php echo ($translator->{'Invoices'}); ?></h1></a>
			<p><?php echo uh($translator->{'View invoices that you have created for this event.'}); ?></p>
			<br/>

			<a href="sms" class="HPTop">
			<img class="HPBigIcon" src="images/sms.png" />
			<h1 class="HPh1"><?php echo ($translator->{'SMS'}); ?></h1></a>
			<p><?php echo uh($translator->{'View sms that you have sent.'}); ?></p>
			<br/>
		</div>
	<hr style="max-width:80%;">
		<h4 style="display:inline-block;"><?php echo uh($translator->{'CFS Customer Support'}); ?></h4>
		<br/>

		<a style="margin:3em 2em 0 0;" class="helpLink inline"><img class="HPMediumIcon inline" src="images/help.png" />
		<h4 class="inline" style="margin-left: 1em"><?php echo uh($translator->{'FAQ'}); ?></h4></a>

		<!--<h3 style="padding: 0"><?php echo uh($translator->{'Contact support'}); ?></h3>-->

		<a style="margin:3em 2em 0 0;" href="mailto:info@chartbooker.com" target="_top" class="inline"><img class="HPMediumIcon inline" src="images/mail.png" />
		<h4 class="inline">info@chartbooker.com</h4></a>

		<img class="HPMediumIcon inline" src="images/phone.png" />
		<h4 class="inline">0760508882</h4>
	</div>
<?php } ?>

<?php if (userLevel() == 3) { ?>

	<div id="HPMainDiv">
		<div id="HP2ndDiv">

		</div>
		<div id="column">

			<a href="mapTool/map/<?php echo $_SESSION['user_fair']; ?>">
			<img class="HPBigIcon" src="images/event_map.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'Go to map'}); ?></h1></a>
			<p><?php echo uh($translator->{'View the event map for this event.'}); ?></p>
			<br/>

			<a href="administrator/newReservations" class="HPTop">
			<img class="HPBigIcon" src="images/my_bookings.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'New reservations'}); ?></h1></a>
			<p><?php echo uh($translator->{'View bookings, reservations and applications for this event.'}); ?></p>
			<br/>

			<a href="comment" class="HPTop">
			<img class="HPBigIcon" src="images/comments.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'Comments'}); ?></h1></a>
			<p><?php echo uh($translator->{'View all comments that you have made for exhibitors.'}); ?></p>
			<br/>

			<a href="administrator/mine" class="HPTop">
			<img class="HPBigIcon" src="images/administrators.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'Administrators'}); ?></h1></a>
			<p><?php echo uh($translator->{'View and edit the administrators of your events.'}); ?></p>
			<br/>
		</div>

		<div id="column">
			<a href="fair/overview" class="HPTop">
			<img class="HPBigIcon" src="images/my_events.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'My events'}); ?></h1></a>
			<p><?php echo uh($translator->{'Overview for all your events.'}); ?></p>
			<br/>

			<a href="administrator/invoices" class="HPTop">
			<img class="HPBigIcon" src="images/invoice.png" />
			<h1 class="HPh1"><?php echo ($translator->{'Invoices'}); ?></h1></a>
			<p><?php echo uh($translator->{'View invoices that you have created for this event.'}); ?></p>
			<br/>

			<a href="sms" class="HPTop">
			<img class="HPBigIcon" style="padding-top:2.3em;" src="images/sms.png" />
			<h1 class="HPh1"><?php echo ($translator->{'SMS'}); ?></h1></a>
			<p><?php echo uh($translator->{'View sms that you have sent.'}); ?></p>
			<br/>

			<a href="exhibitor/forFair" class="HPTop">
			<img class="HPBigIcon" src="images/exhibitors.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'Exhibitors'}); ?></h1></a>
			<p><?php echo uh($translator->{'View the exhibitors of this event.'}); ?></p>
			<br/>
		</div>
	<hr style="max-width:80%;">
		<h4 style="display:inline-block;"><?php echo uh($translator->{'CFS Customer Support'}); ?></h4>
		<br/>

		<a style="margin:3em 2em 0 0;" class="helpLink inline"><img class="HPMediumIcon inline" src="images/help.png" />
		<h4 class="inline" style="margin-left: 1em"><?php echo uh($translator->{'FAQ'}); ?></h4></a>

		<!--<h3 style="padding: 0"><?php echo uh($translator->{'Contact support'}); ?></h3>-->

		<a style="margin:3em 2em 0 0;" href="mailto:info@chartbooker.com" target="_top" class="inline"><img class="HPMediumIcon inline" src="images/mail.png" />
		<h4 class="inline">info@chartbooker.com</h4></a>

		<img class="HPMediumIcon inline" src="images/phone.png" />
		<h4 class="inline">0760508882</h4>
	</div>
<?php } ?>

<?php if (userLevel() == 4) { ?>

	<div id="HPMainDiv">
		<div id="HP2ndDiv">

		</div>
		<div id="column">

			<a href="mapTool/map/<?php echo $_SESSION['user_fair']; ?>">
			<img class="HPBigIcon" src="images/event_map.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'Go to map'}); ?></h1></a>
			<p><?php echo uh($translator->{'View the event map for this event.'}); ?></p>
			<br/>

			<a href="user/accountSettings" class="HPTop">
			<img class="HPBigIcon" src="images/my_profile.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'My account'}); ?></h1></a>
			<p><?php echo uh($translator->{'Click here to view and edit your account details.'}); ?></p>
			<br/>

			<a href="administrator/newReservations" class="HPTop">
			<img class="HPBigIcon" src="images/my_bookings.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'New reservations'}); ?></h1></a>
			<p><?php echo uh($translator->{'Click here to view bookings, reservations and applications for this event.'}); ?></p>
			<br/>
		</div>

		<div id="column">
			<a href="fair/overview" class="HPTop">
			<img class="HPBigIcon" src="images/my_events.png" />
			<h1 class="HPh1"><?php echo uh($translator->{'All events'}); ?></h1></a>
			<p><?php echo uh($translator->{'Click here for a overview for all your events.'}); ?></p>
			<br/>

			<a href="administrator/invoices" class="HPTop">
			<img class="HPBigIcon" src="images/invoice.png" />
			<h1 class="HPh1"><?php echo ($translator->{'Invoices'}); ?></h1></a>
			<p><?php echo uh($translator->{'Click here to view invoices that are created for this event.'}); ?></p>
			<br/>

			<a href="sms" class="HPTop">
			<img class="HPBigIcon" src="images/sms.png" />
			<h1 class="HPh1"><?php echo ($translator->{'SMS'}); ?></h1></a>
			<p><?php echo uh($translator->{'View sms that Organizers have sent.'}); ?></p>
			<br/>
		</div>
	<hr style="max-width:80%;">
		<h4 style="display:inline-block;"><?php echo uh($translator->{'CFS Customer Support'}); ?></h4>
		<br/>

		<a style="margin:3em 2em 0 0;" class="helpLink inline"><img class="HPMediumIcon inline" src="images/help.png" />
		<h4 class="inline" style="margin-left: 1em"><?php echo uh($translator->{'FAQ'}); ?></h4></a>

		<!--<h3 style="padding: 0"><?php echo uh($translator->{'Contact support'}); ?></h3>-->

		<a style="margin:3em 2em 0 0;" href="mailto:info@chartbooker.com" target="_top" class="inline"><img class="HPMediumIcon inline" src="images/mail.png" />
		<h4 class="inline">info@chartbooker.com</h4></a>

		<img class="HPMediumIcon inline" src="images/phone.png" />
		<h4 class="inline">0760508882</h4>
	</div>
<?php } ?>