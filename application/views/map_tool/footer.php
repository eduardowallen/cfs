			</div><!-- end map_content-->
			<div id="right_sidebar">
				<?php
				$f = new Fair;
				if (userLevel() > 0) {
					$f->load($_SESSION['user_fair'], 'id');
				} else {
					$f->load($_SESSION['outside_fair_url'], 'url');
				}
				?>
				<div>
					<h2><?php echo $translator->{'Maps'} ?></h2>
					<?php if (userLevel() > 1 && $hasRights): ?><p><a href="javascript:void(0);" class="" id="create_position"><?php echo $create_position; ?></a></p><?php endif; ?>
					<select name="maps" id="map_select" data-role="none">
						<?php foreach ($f->get('maps') as $map): ?>
						<option value="<?php echo $map->get('id'); ?>"><?php echo $map->get('name'); ?></option>
					<?php endforeach; ?>
					</select>
				</div>
				<div>
					<div class="pre_list">
					<h2 id="exh2"><img src="images/icons/icon_utstallarlista.png" alt=""/> <?php echo $translator->{'Exhibitor list'} ?></h2>
					
					<p><?php echo $translator->{'Spots'} ?>: <span id="spots_total"></span> <?php echo $translator->{'Available spots'} ?>: <span id="spots_free"></span></p>
					
					<select id="category_filter" data-role="none">
						<option value="0"><?php echo $translator->{'Filter by category'} ?></option>
						<?php echo makeOptions($f->db, 'exhibitor_category', 0, 'fair='.$f->get('id')); ?>
					</select>
					
					<p><label id="search_label" for="search_filter"><?php echo $translator->{'Search'} ?></label>
					<input type="text" name="search_filter" id="search_filter" data-role="none"/></p>
					
					</div>
					<ul></ul>
				</div>
			</div>
		</div><!-- end content-->
	</div><!-- end wrapper-->
	<div id="footer">
		<?php if (DEV): ?>
		<p style="color: #AAAAAA; position: absolute;"><?php echo REVISION; ?></p>
		<?php endif; ?>
	</div>
	
</body>
</html>