<?php
	if (isset($_SESSION['user_fair'])) {
				foreach(glob(ROOT.'public/images/fairs/'.$_SESSION['user_fair'].'/logotype/*') as $filename) {
					echo('<img src="'.BASE_URL.'public/images/fairs/'.$_SESSION['user_fair'].'/logotype/'.basename($filename).'" id="header_fair_logo">');
				}
		$activefair = new Fair();
		$activefair->loadsimple($_SESSION['user_fair'], 'id');
	}
?>
	<p id="languages">
		<a alt="eng" href="translate/language/eng"<?php if (LANGUAGE == 'eng') { echo ' class="selected"'; } ?>><img height="20" width="30" src="images/flag_english.png" alt="English"/></a>
		<a alt="sv" href="translate/language/sv"<?php if (LANGUAGE == 'sv') { echo ' class="selected"'; } ?>><img height="20" width="30" src="images/flag_swedish.png" alt="Svenska"/></a>
		<a alt="es" href="translate/language/es"<?php if (LANGUAGE == 'es') { echo ' class="selected"'; } ?>><img height="20" width="30" src="images/flag_spanish.png" alt="Espanol"/></a>
	</p>


<?php /* Main menu for Masters */ ?>
			<ul>
				<?php if (userLevel() == 4): ?>
				<li><a href="start/home"><?php echo uh($translator->{'Start'});?></a></li>
				<li class="parent"><img src="images/icons/mainmenu_arrow.png" class="sub-menu-arrow"/><a class="inline-i" href="user/accountSettings"><?php echo uh($translator->{'My profile'}); ?></a>
					<div class="menu-wrapper">
						<ul>
							<li><a href="user/changePassword"><?php echo uh($translator->{'Change password'}); ?></a></li>
							<li><a href="user/logout"><?php echo uh($translator->{'Log out'}); ?></a></li>
						</ul>
					</div>
				</li>

				<li><a href="exhibitor/forFair"><?php echo uh($translator->{'Exhibitors'}); ?></a></li>
				<li><a href="comment"><?php echo uh($translator->{'Notes'}); ?></a></li>
				<li><a href="administrator/newReservations"><?php echo uh($translator->{'New reservations'}); ?></a></li>

				
				<li><a href="administrator/invoices"><?php echo uh($translator->{'Invoices'}); ?></a></li>
				<li><a href="fair/exportToRainDance/<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($translator->{'Export to RainDance'}); ?></a></li>
				<?php if(isset($fairmodulesettings) && isset($economyMod) && $economyMod === 'active') { ?>
				<li><a href="fair/economy/<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($translator->{'Economy'}); ?></a></li>
				<?php } ?>
				<li class="parent"><img src="images/icons/mainmenu_arrow.png" class="sub-menu-arrow"/><a class="inline-i"><?php echo uh($translator->{'Users'}); ?></a>
					<div class="menu-wrapper">
						<ul>
							<li><a href="user/overview/4"><?php echo uh($translator->{'Masters'}); ?></a></li>
							<li><a href="arranger/overview"><?php echo uh($translator->{'Organizers'}); ?></a></li>
							<li><a href="administrator/all"><?php echo uh($translator->{'Administrators'}); ?></a></li>
							<li><a href="exhibitor/all"><?php echo uh($translator->{'Exhibitors'}); ?></a></li>
							<li><a href="user/overview/0"><?php echo uh($translator->{'All users'}); ?></a></li>
						</ul>
					</div>
				</li>
				<li class="parent"><img src="images/icons/mainmenu_arrow.png" class="sub-menu-arrow"/><a href="fair/overview" class="inline-i"><?php echo uh($translator->{'Events'}); ?> <?php echo $fairCount; ?></a>
					<div class="menu-wrapper">
						<ul>
							<?php echo $fairGroups; ?>
							<?php echo $opts; ?>
						</ul>
					</div>
				</li>
				<li><a href="mapTool/map/<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($translator->{'Map tool'}); ?></a></li>
				<li><a href="sms"><?php echo uh($translator->{'SMS'}); ?></a></li>
				<li><a href="mail/edit"><?php echo uh($translator->{'Html Mails'}); ?></a></li>
				<li><a href="mail/editPlain"><?php echo uh($translator->{'Plain text Mails'}); ?></a></li>
				<li><a href="page/edit"><?php echo uh($translator->{'Pages'}); ?></a></li>
				<li><a href="translate/all"><?php echo uh($translator->{'Translate'}); ?></a></li>
				
				
	<?php /* Main menu for Organizers */ ?>
				<?php elseif (userLevel() == 3): ?>
	
				<li><a href="start/home"><?php echo uh($translator->{'Start'});?></a></li>
				<li><a href="user/accountSettings"><?php echo uh($translator->{'My profile'}); ?></a></li>
				<li><a href="fair/overview"><?php echo uh($translator->{'My events'}); ?></a></li>
				<li><a href="fairGroup/groups"><?php echo uh($translator->{'My groups'}); ?></a></li>
				<li><a href="administrator/mine"><?php echo uh($translator->{'Administrators'}); ?></a></li>
				<li><a href="exhibitor/forFair"><?php echo uh($translator->{'Exhibitors'}); ?></a></li>
				<li><a href="comment"><?php echo uh($translator->{'Notes'}); ?></a></li>
				<li><a href="administrator/newReservations"><?php echo uh($translator->{'New reservations'}); ?></a></li>
				<li><a href="sms"><?php echo uh($translator->{'SMS'}); ?></a></li>
				<?php if(isset($fairmodulesettings) && isset($invoiceMod) && $invoiceMod === 'active') { ?>
				<li><a href="administrator/invoices"><?php echo uh($translator->{'Invoices'}); ?></a></li>
				<?php } ?>
				<?php if(isset($fairmodulesettings) && isset($raindanceMod) && $raindanceMod === 'active') { ?>
				<!--<li><a href="fair/exportToRainDance/<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($translator->{'Export to RainDance'}); ?></a></li>-->
				<?php } ?>
				<?php if(isset($fairmodulesettings) && isset($economyMod) && $economyMod === 'active') { ?>
				<li><a href="fair/economy/<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($translator->{'Economy'}); ?></a></li>
				<?php } ?>
				<li><a href="mapTool/map/<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($translator->{'Map tool'}); ?></a></li>
				<li class="parent"><img src="images/icons/mainmenu_arrow.png" class="sub-menu-arrow"/><a class="inline-i"><?php echo uh($translator->{'Events - Quick access'}); ?></a>
					<div class="menu-wrapper">
						<ul>
						<?php echo $fairGroups; ?>
						<?php echo $opts; ?>
					</ul>
				</li>
				<li class="parent"><img src="images/icons/mainmenu_arrow.png" class="sub-menu-arrow"/><a class="inline-i"><?php echo uh($translator->{'Support'}); ?></a>
					<div class="menu-wrapper">
						<ul>
							<li><a class="helpOrgLink"><?php echo uh($translator->{"Here's how"}); ?></a></li>
							<li><a class="contactLink"><?php echo uh($translator->{'Contact us'}); ?></a></li>
						</ul>
					</div>
				</li>
				<li><a href="user/changePassword"><?php echo uh($translator->{'Change password'}); ?></a></li>
				<li><a href="user/logout"><?php echo uh($translator->{'Log out'}); ?></a></li>				
	
	<?php /* Main menu for Administrators */ ?>
				<?php elseif (userLevel() == 2): ?>
	
				<li><a href="start/home"><?php echo uh($translator->{'Start'});?></a></li>
				<li><a href="user/accountSettings"><?php echo uh($translator->{'My profile'}); ?></a></li>
				<li><a href="administrator/newReservations"><?php echo uh($translator->{'New reservations'}); ?></a></li>
				<li><a href="sms"><?php echo uh($translator->{'SMS'}); ?></a></li>
				<?php if($activefair->get('modules') === '{"invoiceFunction":["1"]}') { ?>
				<li><a href="administrator/invoices"><?php echo uh($translator->{'Invoices'}); ?></a></li>
				<?php } ?>
				<li><a href="exhibitor/forFair"><?php echo uh($translator->{'Exhibitors'}); ?></a></li>
				<li><a href="comment"><?php echo uh($translator->{'Notes'}); ?></a></li>
				<li><a href="mapTool/map/<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($translator->{'Map tool'}); ?></a></li>
				<li class="parent"><img src="images/icons/mainmenu_arrow.png" class="sub-menu-arrow"/><a class="inline-i"><?php echo uh($translator->{'Events - Quick access'}); ?></a>
					<div class="menu-wrapper">
						<ul>
						<?php echo $fairGroups; ?>
						<?php echo $opts; ?>
						</ul>
					</div>
				</li>
				<li class="parent"><img src="images/icons/mainmenu_arrow.png" class="sub-menu-arrow"/><a class="inline-i"><?php echo uh($translator->{'Support'}); ?></a>
					<div class="menu-wrapper">
						<ul>
							<li><a class="helpOrgLink"><?php echo uh($translator->{"Here's how"}); ?></a></li>
							<li><a class="contactLink"><?php echo uh($translator->{'Contact us'}); ?></a></li>
						</ul>
					</div>
				</li>
				<li><a href="user/changePassword"><?php echo uh($translator->{'Change password'}); ?></a></li>
				<li><a href="user/logout"><?php echo uh($translator->{'Log out'}); ?></a></li>

	<?php /* Main menu for Exhibitors */ ?>
				<?php elseif (userLevel() == 1): ?>
				
				<li><a href="start/home"><?php echo uh($translator->{'Start'});?></a></li>
				<li><a href="user/accountSettings"><?php echo uh($translator->{'My profile'}); ?></a></li>
				<li><a href="user/changePassword"><?php echo uh($translator->{'Change password'}); ?></a></li>
				<li><a href="exhibitor/myBookings"><?php echo uh($translator->{'My bookings'}); ?></a></li>
				<li><a href="fair/search"><?php echo uh($translator->{'Eventsearch'}); ?></a></li>				
				<li><a href="mapTool/map/<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($translator->{'View map'}); ?></a></li>
				<li class="parent"><img src="images/icons/mainmenu_arrow.png" class="sub-menu-arrow"/><a class="inline-i"><?php echo uh($translator->{'Choose event'}); ?></a>
					<div class="menu-wrapper">
						<ul>
							<?php echo $fairGroups; ?>
							<?php echo $opts; ?>
						</ul>
					</div>
				</li>
				<li><a class="helpLink"><?php echo uh($translator->{'Help'}); ?></a></li>
				<li><a class="contactLink <?php echo $_SESSION['user_fair'] ?>"><?php echo uh($translator->{'Contact us'}); ?></a></li>
				<li><a href="user/logout"><?php echo uh($translator->{'Log out'}); ?></a></li>
				
	<?php /* Main menu for users who are not logged in */ ?>
				<?php else: ?>
	
					<?php if (isset($_SESSION['outside_fair_url'])): ?>
					
					<?php global $url; if ($url != '' && $url != 'user/login/' &&  $url != 'user/login'): ?>
					<li><a href="fair/search"><?php echo uh($translator->{'Eventsearch'}); ?></a></li>
					<li><a class="loginlink" href="user/login/<?php echo $_SESSION['outside_fair_url'] ?>"><?php echo uh($translator->{'Sign in'}); ?></a></li>
					<li><a class="registerlink" href="user/register/<?php echo $_SESSION['outside_fair_url'] ?>"><?php echo uh($translator->{'Register'}); ?></a></li>
					<li><a class="helpLink"><?php echo uh($translator->{"Here's how"}); ?></a></li>
					<li><a class="contactLink <?php echo $_SESSION['outside_fair_url']?>"><?php echo uh($translator->{'Contact us'}); ?></a></li>
					<?php endif; ?>
					<?php else: ?>
					<li><a class="registerlink" href="user/register"><?php echo uh($translator->{'Register'}); ?></a></li>
					<li><a href="fair/search"><?php echo uh($translator->{'Eventsearch'}); ?></a></li>
					<?php endif; ?>
	
				<?php endif; ?>
			</ul>


	<a href="http://www.chartbooker.com/" target="_blank"><img src="images/logo/chartbooking_logo_small.png" alt="Chartbooker International" id="header_logo"/></a>

	<script type="text/javascript">

$(document).ready(function() {

	if (!$('#header_fair_logo').length)
		$('#new_header p:first-child').css('padding-top', '12vh');

$(function() {
  // whenever we hover over a menu item that has a submenu
  $('li.parent').on('mouseover', function() {
    var $menuItem = $(this),
        $submenuWrapper = $('> .menu-wrapper', $menuItem);
    
    // grab the menu item's position relative to its positioned parent
    var menuItemPos = $menuItem.position();
    // place the submenu in the correct position relevant to the menu item
    $submenuWrapper.css({
      top: menuItemPos.top,
      left: menuItemPos.left + Math.round($menuItem.outerWidth())
    });
  });
});
});
</script>