			</div><!-- end map_content-->	
					<?php
						$f = new Fair;
						if (userLevel() > 0) {
							$f->load($_SESSION['user_fair'], 'id');
						} else {
							$f->load($_SESSION['outside_fair_url'], 'url');
						}
					?>

				
					<?php if($f->get('hidden') == 1 && userIsConnectedTo($f->get('id')) || ($f->get('hidden') == 1 && userLevel() > 1) || $f->get('hidden') == 0) :?>
						<div id="right_sidebar">
								<div>
								
									<?php if (userLevel() > 1 && $hasRights): ?><p><a href="javascript:void(0);" class="" id="create_position"><?php echo $create_position; ?></a></p> <?php endif; ?>
									<?php if (userLevel() < 2): ?><img src="/images/navigate-maps.png" alt="" id="navigate-maps"/><h2><?php echo uh($translator->{'Maps'}); ?></h2><?php endif; ?>
									
									<select name="maps" id="map_select">
										<?php foreach ($f->get('maps') as $map): ?>
										<option value="<?php echo $map->get('id'); ?>"><?php echo $map->get('name'); ?></option>
									<?php endforeach; ?>
									</select>
								</div>
								<div>
									<div class="pre_list">
										<h2 id="exh2"><img src="images/icons/icon_utstallarlista.png" alt=""/> <?php echo uh($translator->{'Exhibitor list'}); ?></h2>
											<p><?php echo uh($translator->{'Spots'}); ?>: <span id="spots_total"></span> <?php echo uh($translator->{'Available spots'}); ?>: <span id="spots_free"></span></p>
					
											<select id="category_filter">
												<option value="0"><?php echo uh($translator->{'Filter by category'}); ?></option>
												<?php echo makeOptions($f->db, 'exhibitor_category', 0, 'fair='.$f->get('id')); ?>
											</select>
					
										<p><label id="search_label" for="search_filter"><?php echo uh($translator->{'Search exhibitor'}); ?></label>

										<input type="text" name="search_filter" id="search_filter"/></p>
									</div>
									<ul></ul>
								</div>
							</div>
				
				<?php endif?>		
				</div>
			</div>
		</div><!-- end content-->
	</div><!-- end wrapper-->
<!--	<div id="footer"></div>
<<<<<<< HEAD
	<div id="maintenance-message"><?php echo uh($translator->{'Meddelande för driftinformation.'}); ?></div> -->
=======
	<div id="maintenance-message"><?php echo uh($translator->{'Vi upplever problem med serverns prestanda och därför kan systemet upplevas vara långsamt. Vi ber om ursäkt för detta och försöker åtgärda problemet så snart vi kan.'}); ?></div> -->
>>>>>>> 980f404875926bfcc97d750f6b936ab3a0b2c217
</body>
</html>
