<script type="text/javascript" src="js/tablesearch.js"></script>
<h1><?php echo $headline; ?></h1>

<p><a class="button add" href="administrator/newExhibitor"><?php echo $create_link; ?></a></p>

<h2><?php echo $fair->get('name'); ?></h2>
<table class="std_table">
	<thead>
		<tr>
			<th><?php echo $th_status; ?></th>
			<th><?php echo $th_name; ?></th>
			<th><?php echo $th_company; ?></th>
			<th><?php echo $th_phone; ?></th>
			<th><?php echo $th_contact; ?></th>
			<th><?php echo $th_email; ?></th>
			<th><?php echo $th_website; ?></th>
			<th><?php echo $th_profile; ?></th>
		</tr>
	</thead>
<tbody>
		<?php foreach ($fair->get('maps') as $map): ?>
		<?php foreach ($map->get('positions') as $pos): ?>
		<?php if ($pos->get('exhibitor')): ?>
		<tr>
			<td><?php echo $pos->get('statusText'); ?></td>
			<td class="center"><?php echo $pos->get('name'); ?></td>
			<td class="center"><?php echo $pos->get('exhibitor')->get('company'); ?></td>
			<td class="center"><?php echo $pos->get('exhibitor')->get('phone1'); ?></td>
			<td class="center"><?php echo $pos->get('exhibitor')->get('name'); ?></td>
			<td class="center"><?php echo $pos->get('exhibitor')->get('email'); ?></td>
			<td class="center"><a target="_blank" href="<?php echo $pos->get('exhibitor')->get('website'); ?>"><?php echo $pos->get('exhibitor')->get('website'); ?></a></td>
			<td class="center"><a href="exhibitor/profile/<?php echo $pos->get('exhibitor')->get('id'); ?>"><img src="images/icons/user.png" alt=""/></a></td>
		</tr>
		<?php endif; ?>
		<?php endforeach; ?>
		<?php endforeach; ?>
	</tbody>
</table>
