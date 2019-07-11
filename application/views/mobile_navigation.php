<div id="dl-menu" class="dl-menuwrapper">
	<button class="dl-trigger" title="<?php echo uh($translator->{'Open Menu'}); ?>">Open Menu</button>
	<ul class="dl-menu">

<?php /* Main menu for Masters */ ?>
			<ul>
				<?php if (userLevel() == 4): ?>
				
	<?php /* The first button in the main menu */ ?>
				<li><a href="#"><?php echo uh($translator->{'My profile'}); ?></a>
					<ul class="dl-submenu">
						<li><a href="user/accountSettings"><?php echo uh($translator->{'My account'}); ?></a>
						<li><a href="user/changePassword"><?php echo uh($translator->{'Change password'}); ?></a></li>
						<li><a href="exhibitor/forFair"><?php echo uh($translator->{'Exhibitors'}); ?></a></li>
						<li><a href="comment"><?php echo uh($translator->{'Notes'}); ?></a></li>
						<li><a href="administrator/newReservations"><?php echo uh($translator->{'New reservations'}); ?></a></li>
						<?php if(isset($fairmodulesettings) && isset($invoiceMod) && $invoiceMod === 'active') { ?>
						<li><a href="administrator/invoices"><?php echo uh($translator->{'Invoices'}); ?></a></li>
						<?php } ?>
						<?php if(isset($fairmodulesettings) && isset($raindanceMod) && $raindanceMod === 'active') { ?>
						<!--<li><a href="fair/exportToRainDance/<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($translator->{'Export to RainDance'}); ?></a></li>-->
						<?php } ?>
						<?php if(isset($fairmodulesettings) && isset($economyMod) && $economyMod === 'active') { ?>
						<li><a href="fair/economy/<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($translator->{'Economy'}); ?></a></li>
						<?php } ?>
						<li><a href="user/logout"><?php echo uh($translator->{'Log out'}); ?></a></li>
					</ul>
				</li>
				
	<?php /* The second button in the main menu */ ?>
				<li><a href="#"><?php echo uh($translator->{'Users'}); ?></a>
					<ul class="dl-submenu">
						<li><a href="user/overview/4"><?php echo uh($translator->{'Masters'}); ?></a></li>
						<li><a href="arranger/overview"><?php echo uh($translator->{'Organizers'}); ?></a></li>
						<li><a href="administrator/all"><?php echo uh($translator->{'Administrators'}); ?></a></li>
						<li><a href="exhibitor/all"><?php echo uh($translator->{'Exhibitors'}); ?></a></li>
						<li><a href="user/overview/0"><?php echo uh($translator->{'All users'}); ?></a></li>
					</ul>
				</li>
				
	<?php /* The third button in the main menu */ ?>
				<li><a href="#"><?php echo uh($translator->{'Event management'}); ?></a>
					<ul class="dl-submenu">
						<li><a href="fair/overview"><?php echo uh($translator->{'Fairs'}); ?></a></li>
						<li><a href="mapTool/map/<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($translator->{'Map tool'}); ?></a></li>
						<li><a href="sms"><?php echo uh($translator->{'SMS'}); ?></a></li>
					</ul>
				</li>
				
	<?php /* The fourth button in the main menu */ ?>
				<li><a href="#"><?php echo uh($translator->{'Support'}); ?></a>
					<ul class="dl-submenu">
						<li><a href="mail/edit"><?php echo uh($translator->{'Mails'}); ?></a></li>
						<li><a href="page/edit"><?php echo uh($translator->{'Pages'}); ?></a></li>
						<li><a href="translate/all"><?php echo uh($translator->{'Translate'}); ?></a></li>
					</ul>
				</li>
				<li><a href="#"><?php echo uh($translator->{'Change language'}); ?></a>
					<ul class="dl-submenu">
						<li><a href="translate/language/eng"><img height="20" width="30" src="images/flag_english.png" alt="English"/> &nbsp; English</a></li>
						<li><a href="translate/language/sv"><img height="20" width="30" src="images/flag_swedish.png" alt="Svenska"/> &nbsp; Svenska</a></li>
						<li><a href="translate/language/de"><img height="20" width="30" src="images/flag_german.png" alt="Deutsch"/> &nbsp; Deutsch</a></li>
						<li><a href="translate/language/es"><img height="20" width="30" src="images/flag_spanish.png" alt="Espanol"/> &nbsp; Español</a></li>
					</ul>
				</li>
				
	<?php /* Main menu for Organizers */ ?>
				<?php elseif (userLevel() == 3): ?>
	
	<?php /* The first button in the main menu */ ?>
				<li><a href="#"><?php echo uh($translator->{'My profile'}); ?></a>
					<ul class="dl-submenu">
						<li><a href="user/accountSettings"><?php echo uh($translator->{'My account'}); ?></a>
						<li><a href="fair/overview"><?php echo uh($translator->{'My events'}); ?></a></li>
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
						<li><a href="user/changePassword"><?php echo uh($translator->{'Change password'}); ?></a></li>
						<li><a href="user/logout"><?php echo uh($translator->{'Log out'}); ?></a></li>
					</ul>
				</li>
				
	<?php /* The second button in the main menu */ ?>
				<li><a href="mapTool/map/<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($translator->{'Map tool'}); ?></a></li>
	
	<?php /* The third button in the main menu */ ?>
				<li><a href="#"><?php echo uh($translator->{'Fairs'}); ?></a>
					<ul class="dl-submenu">
						<?php echo $opts ?>
					</ul>
				</li>
				
	<?php /* The fourth button in the main menu */ ?>
				<li><a href="#"><?php echo uh($translator->{'Support'}); ?></a>
					<ul class="dl-submenu">
						<li><a class="helpOrgLink"><?php echo uh($translator->{"Here's how"}); ?></a></li>
						<li><a class="contactLink"><?php echo uh($translator->{'Contact us'}); ?></a></li>
					</ul>
				</li>
				<li><a href="#"><?php echo uh($translator->{'Change language'}); ?></a>
					<ul class="dl-submenu">
						<li><a href="translate/language/eng"><img height="20" width="30" src="images/flag_english.png" alt="English"/> &nbsp; English</a></li>
						<li><a href="translate/language/sv"><img height="20" width="30" src="images/flag_swedish.png" alt="Svenska"/> &nbsp; Svenska</a></li>
						<li><a href="translate/language/de"><img height="20" width="30" src="images/flag_german.png" alt="Deutsch"/> &nbsp; Deutsch</a></li>
						<li><a href="translate/language/es"><img height="20" width="30" src="images/flag_spanish.png" alt="Espanol"/> &nbsp; Español</a></li>
					</ul>
				</li>	
	<?php /* Main menu for Administrators */ ?>
				<?php elseif (userLevel() == 2): ?>
	
	<?php /* The first button in the main menu */ ?>
				<li><a href="#"><?php echo uh($translator->{'My profile'}); ?></a>
					<ul class="dl-submenu">
						<li><a href="user/accountSettings"><?php echo uh($translator->{'My account'}); ?></a>
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
						<li><a href="exhibitor/forFair"><?php echo uh($translator->{'Exhibitors'}); ?></a></li>
						<li><a href="comment"><?php echo uh($translator->{'Notes'}); ?></a></li>
						<li><a href="user/changePassword"><?php echo uh($translator->{'Change password'}); ?></a></li>
						<li><a href="user/logout"><?php echo uh($translator->{'Log out'}); ?></a></li>
					</ul>
				</li>
	
	<?php /* The second button in the main menu */ ?>
				<li><a href="mapTool/map/<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($translator->{'Map tool'}); ?></a></li>
				
	<?php /* The third button in the main menu */ ?>
				<li><a href="#"><?php echo uh($translator->{'Fairs'}); ?></a>
					<ul class="dl-submenu">
						<?php echo $opts ?>
					</ul>
				</li>
				
	<?php /* The fourth button in the main menu */ ?>
				<li><a href="#"><?php echo uh($translator->{'Support'}); ?></a>
					<ul class="dl-submenu">
						<li><a class="helpOrgLink"><?php echo uh($translator->{'Help'}); ?></a></li>
						<li><a class="contactLink"><?php echo uh($translator->{'Contact us'}); ?></a></li>
					</ul>
				</li>
				<li><a href="#"><?php echo uh($translator->{'Change language'}); ?></a>
					<ul class="dl-submenu">
						<li><a href="translate/language/eng"><img height="20" width="30" src="images/flag_english.png" alt="English"/> &nbsp; English</a></li>
						<li><a href="translate/language/sv"><img height="20" width="30" src="images/flag_swedish.png" alt="Svenska"/> &nbsp; Svenska</a></li>
						<li><a href="translate/language/de"><img height="20" width="30" src="images/flag_german.png" alt="Deutsch"/> &nbsp; Deutsch</a></li>
						<li><a href="translate/language/es"><img height="20" width="30" src="images/flag_spanish.png" alt="Espanol"/> &nbsp; Español</a></li>
					</ul>
				</li>				

	<?php /* Main menu for Exhibitors */ ?>
				<?php elseif (userLevel() == 1): ?>
				
	<?php /* The first button in the main menu */ ?>
				<li><a href="#"><?php echo uh($translator->{'My profile'}); ?></a>
					<ul class="dl-submenu">
						<li><a href="user/accountSettings"><?php echo uh($translator->{'My account'}); ?></a>
						<li><a href="exhibitor/myBookings"><?php echo uh($translator->{'My bookings'}); ?></a></li>
						<li><a href="fair/search"><?php echo uh($translator->{'Eventsearch'}); ?></a></li>
						<li><a href="user/changePassword"><?php echo uh($translator->{'Change password'}); ?></a></li>
						<li><a href="user/logout"><?php echo uh($translator->{'Log out'}); ?></a></li>
					</ul>
				</li>
				
	<?php /* The second button in the main menu */ ?>
				<li><a href="mapTool/map/<?php echo $_SESSION['user_fair']; ?>"><?php echo uh($translator->{'View map'}); ?></a></li>
				
	<?php /* The third button in the main menu */ ?>
				<li><a href="#"><?php echo uh($translator->{'Fairs'}); ?></a>
					<ul class="dl-submenu">
						<?php echo $opts ?>
					</ul>
				</li>
				
	<?php /* The fourth button in the main menu */ ?>
				<li><a href="#"><?php echo uh($translator->{'Support'}); ?></a>
					<ul class="dl-submenu">
						<li><a class="helpLink"><?php echo uh($translator->{'Help'}); ?></a></li>
						<li><a class="contactLink <?php echo $_SESSION['user_fair'] ?>"><?php echo uh($translator->{'Contact us'}); ?></a></li>
					</ul>
				</li>

				<li><a href="#"><?php echo uh($translator->{'Change language'}); ?></a>
					<ul class="dl-submenu">
						<li><a href="translate/language/eng"><img height="20" width="30" src="images/flag_english.png" alt="English"/> &nbsp; English</a></li>
						<li><a href="translate/language/sv"><img height="20" width="30" src="images/flag_swedish.png" alt="Svenska"/> &nbsp; Svenska</a></li>
						<li><a href="translate/language/de"><img height="20" width="30" src="images/flag_german.png" alt="Deutsch"/> &nbsp; Deutsch</a></li>
						<li><a href="translate/language/es"><img height="20" width="30" src="images/flag_spanish.png" alt="Espanol"/> &nbsp; Español</a></li>
					</ul>
				</li>
				
	<?php /* Main menu for users who are not logged in */ ?>
				<?php else: ?>
	
					<?php if (isset($_SESSION['outside_fair_url'])): ?>
					
					<?php global $url; if ($url != '' && $url != 'user/login/' &&  $url != 'user/login'): ?>
					
					<li><a class="loginlink" href="user/login/<?php echo $_SESSION['outside_fair_url'] ?>"><?php echo uh($translator->{'Sign in'}); ?></a></li>
					<li><a class="registerlink" href="user/register/<?php echo $_SESSION['outside_fair_url'] ?>"><?php echo uh($translator->{'Register'}); ?></a></li>					
					<li><a class="helpLink"> <?php echo uh($translator->{"Here's how"}); ?></a></li>
					<li><a class="contactLink <?php echo $_SESSION['outside_fair_url']?>"><?php echo uh($translator->{'Contact us'}); ?></a></li>
					<li><a href="#"><?php echo uh($translator->{'Change language'}); ?></a>
						<ul class="dl-submenu">
							<li><a href="translate/language/eng"><img height="20" width="30" src="images/flag_english.png" alt="English"/> &nbsp; English</a></li>
							<li><a href="translate/language/sv"><img height="20" width="30" src="images/flag_swedish.png" alt="Svenska"/> &nbsp; Svenska</a></li>
							<li><a href="translate/language/de"><img height="20" width="30" src="images/flag_german.png" alt="Deutsch"/> &nbsp; Deutsch</a></li>
							<li><a href="translate/language/es"><img height="20" width="30" src="images/flag_spanish.png" alt="Espanol"/> &nbsp; Español</a></li>
						</ul>
					</li>
					
					<?php endif; ?>
	
					<?php else: ?>
	
					<?php endif; ?>
	
				<?php endif; ?>
			</ul>
	</ul>
</div>
		
		<script type="text/javascript" src="js/jquery.dlmenu.js<?php echo $unique?>"></script>
		<script>
			$(function() {
				$( '#dl-menu' ).dlmenu();
			});
		</script>
