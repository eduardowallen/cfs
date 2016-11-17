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
					<button id="right_sidebar_show"><?php echo uh($translator->{"Show sidebar"}); ?></button>
						<div id="right_sidebar">
							<button id="right_sidebar_hide">Hide</button>
							<div>
							<?php if (userLevel() > 1 && $hasRights): ?>
								<label style="display:inline;">
									<input type="button" class="greenbutton" value="<?php echo uh($translator->{"Create position"}); ?>" id="create_position" href="javascript:void(0);"/>
								 </label>
								 <hr>
							<?php endif; ?>
							<?php if (userLevel() < 1): ?>
								<br/>
							<?php endif; ?>
							<?php if (userLevel() == 1 && $f->get('allow_registrations') == 1): ?>
								<label style="display:inline">
									<a href="fairRegistration/form/<?php echo $f->get('id'); ?>"><input type="button" class="greenbutton" id="fair_register_map" value="<?php echo uh($translator->{"Apply for stand space"}); ?>" id="fair_registration" /></a>

								 </label>
								 <hr>
							<?php endif; ?>
								<h2><?php echo uh($translator->{'Choose map'}); ?></h2>
								<img src="/images/navigate-maps_new.png" alt="" id="navigate-maps"/>
								<div id="selectmap">
									<select name="maps" id="map_select">
										<?php foreach ($f->get('maps') as $map): ?>
										<option value="<?php echo $map->get('id'); ?>"><?php echo $map->get('name'); ?></option>
									<?php endforeach; ?>
									</select>
								</div>
								<hr>
							</div>
								<div style="padding-left: 10px; padding-right: 10px;">
									<h2 id="exh2"><?php echo uh($translator->{'Exhibitor list'}); ?></h2>
									<div class="pre_list">
											<select id="category_filter">
												<option value="0"><?php echo uh($translator->{'Filter by category'}); ?></option>
												<?php echo makeOptions($f->db, 'exhibitor_category', 0, 'fair='.$f->get('id')); ?>
											</select>
					
										<p style="margin-top:1em;"><label id="search_label" for="search_filter"><?php echo uh($translator->{'Search exhibitor'}); ?></label>

										<input type="text" name="search_filter" id="search_filter"/></p>

										<p><?php echo uh($translator->{'Spots'}); ?>: <span id="spots_total"></span> <?php echo uh($translator->{'Available spots'}); ?>: <span id="spots_free"></span></p>
									</div>
									<!--<hr style="margin:0 -10px 10px;">-->
									<ol id="exhibitor_scroll_list"></ol>
								</div>
							</div>
				
				<?php endif?>		
				</div>
			</div>
<script>
$('#fair_register_map').click(function(e) {
	e.preventDefault();
	maptool.applyForFair();
});
$("#right_sidebar_show").click(function () {
	$( "#right_sidebar" ).show("slide", { direction: "right" }, 500);
	$( "#right_sidebar_hide" ).delay("slow").fadeIn();
	$( "#right_sidebar" ).css( "display", "block" );
	$("#right_sidebar_show").hide();
});
$("#right_sidebar_hide").click(function () {
	  $( "#right_sidebar" ).hide("slide", { direction: "right" }, 500);
	  $( "#right_sidebar_show" ).delay("slow").fadeIn();
	  $("#right_sidebar_hide").hide();
});


</script>			
		</div><!-- end content-->
	</div><!-- end wrapper-->
<!--	<div id="footer"></div>
	<div id="maintenance-message"><?php echo uh($translator->{'Meddelande för driftinformation.'}); ?></div> -->
</body>
</html>
