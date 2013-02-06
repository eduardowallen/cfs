<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $headline; ?></h1>

<?php if (userLevel() > 2): ?>

<input type="button" value="<?php echo $export_button ?>" class="floatright" onclick="document.location.href='exhibitor/export/<?php echo $fair->get('id') ?>'"/>

<?php endif; ?>

<table class="std_table">
	<thead>
		<tr>
			<th><?php echo $th_status; ?></th>
			<th><?php echo $th_name; ?></th>
			<th><?php echo $th_company; ?></th>
			<th><?php echo $th_branch; ?></th>
			<th><?php echo $th_phone; ?></th>
			<th><?php echo $th_contact; ?></th>
			<th><?php echo $th_email; ?></th>
			<th><?php echo $th_website; ?></th>
			<th><?php echo $th_view; ?></th>
			<?php if (userLevel() > 0): ?>
			<th><?php echo $th_profile; ?></th>
			<?php endif; ?>
		</tr>
	</thead>
	<tbody>
		<?php if( is_array($fair->get('maps')) ) : ?>
		<?php foreach ($fair->get('maps') as $map): ?>
		<?php foreach ($map->get('positions') as $pos): ?>

		<?php

		$fair = new Fair;
		if ($pos->get('exhibitor')) {
			$fair->load($pos->get('exhibitor')->get('fair'), 'id');
			$maps = $fair->get('maps');
			$maps = $maps[0]->get('positions');
		}


		?>
		
		<?php if ($pos->get('exhibitor')): ?>
		<tr>
			<td><?php echo $pos->get('statusText'); ?></td>
			<td class="center"><?php echo @$pos->get('name'); ?></td>
			<td class="center"><?php echo @$pos->get('exhibitor')->get('company'); ?></td>
			<td class="center">
				<?php
				$commodity = $pos->get('exhibitor')->get('commodity');
				echo ( empty( $commodity ) ) ? $pos->get('user')->get('commodity') : $pos->get('exhibitor')->get('commodity') ;
				?>
			</td>
			<td class="center"><?php echo @$pos->get('exhibitor')->get('phone1'); ?></td>
			<td class="center"><?php echo @$pos->get('exhibitor')->get('name'); ?></td>
			<td class="center"><?php echo @$pos->get('exhibitor')->get('email'); ?></td>
			<td class="center"><a target="_blank" href="<?php echo $pos->get('exhibitor')->get('website'); ?>"><?php echo @$pos->get('exhibitor')->get('website'); ?></a></td>
			<td class="center"><a href="mapTool/map/<?php echo $pos->get('exhibitor')->get('fair').'/'.$pos->get('exhibitor')->get('position').'/'.$maps[0]->map; ?>"><img src="images/icons/map_go.png" alt=""/></a></td>
			<?php if (userLevel() > 0): ?>
			<td class="center"><a href="exhibitor/profile/<?php echo $pos->get('exhibitor')->get('id'); ?>"><img src="images/icons/user.png" alt=""/></a></td>
			<?php endif; ?>
		</tr>
		<?php endif; ?>
		<?php endforeach; ?>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
