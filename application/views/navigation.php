			<ul>
				<?php if (userLevel() == 4): ?>
				
				<li><a href="user/accountSettings"><img src="images/icons/icon_logga_in.png" alt=""/><?php echo $translator->{'My profile'} ?></a>
					<ul>
						<li><a href="user/changePassword"><?php echo $translator->{'Change password'} ?></a></li>
						<li><a href="user/logout"><?php echo $translator->{'Log out'} ?></a></li>
					</ul>
				</li>
				<li><span><img src="images/icons/icon_registrera.png" alt=""/><?php echo $translator->{'Users'} ?></span>
					<ul>
						<li><a href="user/overview/4"><?php echo $translator->{'Masters'} ?></a></li>
						<li><a href="arranger/overview"><?php echo $translator->{'Organizers'} ?></a></li>
						<li><a href="administrator/all"><?php echo $translator->{'Administrators'} ?></a></li>
						<li><a href="exhibitor/all"><?php echo $translator->{'Exhibitors'} ?></a></li>
					</ul>
				</li>
				<li><a href="fair/overview"><img src="images/icons/icon_events.png" alt=""/><?php echo $translator->{'Fairs'} ?></a>
					<ul>
						<li><a href="fair/overview/new"><?php echo $translator->{'New fairs'} ?><?php echo $fairCount; ?></a></li>
					</ul>
				</li>
				<li><span><img src="images/icons/icon_help.png" alt=""/><?php echo $translator->{'Support'} ?></span>
					<ul>
						<li><a href="page/edit"><?php echo $translator->{'Pages'} ?></a></li>
						<li><a href="translate/all"><?php echo $translator->{'Translate'} ?></a></li>
					</ul>
				</li>
				
	
				<?php elseif (userLevel() == 3): ?>
	
				<li><a href="user/accountSettings"><img src="images/icons/icon_logga_in.png" alt=""/><?php echo $translator->{'My profile'} ?></a>
					<ul>
						<li><a href="fair/overview"><?php echo $translator->{'My events'} ?></a></li>
						<li><a href="administrator/newReservations"><?php echo $translator->{'New reservations'} ?><?php echo $bookCount; ?></a></li>
						<li><a href="user/changePassword"><?php echo $translator->{'Change password'} ?></a></li>
						<li><a href="user/logout"><?php echo $translator->{'Log out'} ?></a></li>
					</ul>
				</li>
				<li><a href="administrator/mine"><img src="images/icons/icon_registrera.png" alt=""/><?php echo $translator->{'Administrators'} ?></a></li>
				<li><a class="helpOrgLink"><img src="images/icons/icon_help.png" alt=""/><?php echo $translator->{'Help'} ?></a></li>
				<li><a class="contactLink"><img src="images/icons/icon_contact.png" alt=""/><?php echo $translator->{'Contact us'} ?></a></li>
	
				<?php elseif (userLevel() == 2): ?>
				
				<li><a href="user/accountSettings"><img src="images/icons/icon_logga_in.png" alt=""/><?php echo $translator->{'My profile'} ?></a>
					<ul>
						<li><a href="administrator/newReservations"><?php echo $translator->{'New reservations'} ?><?php echo $bookCount; ?></a></li>
						<li><a href="exhibitor/forFair"><?php echo $translator->{'Exhibitors'} ?></a></li>
						<li><a href="user/changePassword"><?php echo $translator->{'Change password'} ?></a></li>
						<li><a href="user/logout"><?php echo $translator->{'Log out'} ?></a></li>
					</ul>
				</li>
				<li><a href="mapTool/map/<?php echo $_SESSION['user_fair']; ?>"><img src="images/icons/icon_globe.png" alt=""/><?php echo $translator->{'Map tool'} ?></a></li>
				<li><span><img src="images/icons/icon_events.png" alt=""/><?php echo $translator->{'Fairs'} ?></span>
					<ul>
						<?php echo $opts ?>
					</ul>
				</li>
				<li><span><img src="images/icons/icon_help.png" alt=""/><?php echo $translator->{'Support'} ?></span>
					<ul>
						<li><a class="helpOrgLink"><?php echo $translator->{'Help'} ?></a></li>
						<li><a class="contactLink"><?php echo $translator->{'Contact us'} ?></a></li>
					</ul>
				</li>
				
				<!--<li><a href="administrator/exhibitors"><?php echo $translator->{'Exhibitors'} ?></a></li>-->
	
				<?php elseif (userLevel() == 1): ?>
				
				<li><a href="user/accountSettings"><img src="images/icons/icon_logga_in.png" alt=""/><?php echo $translator->{'My profile'} ?></a>
					<ul>
						<li><a href="exhibitor/myBookings"><?php echo $translator->{'My bookings'} ?></a></li>
						<li><a href="user/changePassword"><?php echo $translator->{'Change password'} ?></a></li>
						<li><a href="user/logout"><?php echo $translator->{'Log out'} ?></a></li>
					</ul>
				</li>
				<li><a href="mapTool/map/<?php echo $_SESSION['user_fair']; ?>"><img src="images/icons/icon_globe.png" alt=""/><?php echo $translator->{'View map'} ?></a></li>
				<li><a class="helpLink"> <img src="images/icons/icon_help.png" alt=""/><?php echo $translator->{"Here's how"} ?></a></li>
				<li><span><img src="images/icons/icon_events.png" alt=""/><?php echo $translator->{'Fairs'} ?></span>
					<ul>
						<?php echo $opts ?>
					</ul>
				</li>
				<!--<li><a href="page/contact/<?php echo $_SESSION['user_fair'] ?>"><?php echo $translator->{'Contact us'} ?></a></li>-->
				<!--<li><a href="exhibitor/exhibitors"><?php echo $translator->{'Show exhibitors'} ?></a></li>-->
				
				
	
				<?php else: ?>
	
					<?php if (isset($_SESSION['outside_fair_url'])): ?>
					
					<?php global $url; if ($url != '' && $url != 'user/login/' &&  $url != 'user/login'): ?>
					
					<li><a class="loginlink" href="user/login/<?php echo $_SESSION['outside_fair_url'] ?>"><img src="images/icons/icon_logga_in.png" alt=""/><?php echo $translator->{'Sign in'} ?></a></li>
					<li><a class="registerlink" href="user/register/<?php echo $_SESSION['outside_fair_url'] ?>"><img src="images/icons/icon_registrera.png" alt=""/><?php echo $translator->{'Register'} ?></a></li>					
					<li><a class="helpLink"><img src="images/icons/icon_help.png" alt=""/><?php echo $translator->{"Here's how"} ?></a></li>
					<li><a class="contactLink <?php echo $_SESSION['outside_fair_url']?>"><img src="images/icons/icon_contact.png" alt=""/><?php echo $translator->{'Contact us'} ?></a></li>
					<!--<li><a href="exhibitor/exhibitors"><?php echo $translator->{'Exhibitors'} ?></a></li>-->
					
					<?php endif; ?>
	
					<?php else: ?>
	
					<!--<li><a href="#"></a></li>-->
	
					<?php endif; ?>
	
				<?php endif; ?>
			</ul>
