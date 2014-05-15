<script type="text/javascript" src="js/tablesearch.js"></script>

<h1><?php echo $headline; ?></h1>
<p><br> </br></p>
<div class="scrolltbl onlythirteen">
	<table class="std_table">
		<thead>
			<tr>
				<th><?php echo $th_company ?></th>
				<th><?php echo $th_orgnr ?></th>
				<th><?php echo $th_name ?></th>
				<th><?php echo $th_phone ?></th>
				<th><?php echo uh($translator->{'Commodity'}); ?></th>
				<th><?php echo $th_request ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($users as $user): ?>
			<tr>
				<td><a href="exhibitor/profile/<?php echo $user->get('id'); ?>" class="showProfileLink" data-id="<?php echo $user->get('id'); ?>"><?php echo $user->get('company'); ?></a></td>
				<td><?php echo $user->get('orgnr'); ?></td>
				<td><?php echo $user->get('name'); ?></a></td>
				<td><?php echo $user->get('phone1'); ?></td>
				<td class="center" title="<?php echo $user->get('commodity');?>"><?php echo $user->get('commodity');?></td>
				<td class="center"><a href="requestconnection/<?php echo $user->get('id') ?>"><img src="images/icons/connect_request.png" alt="" title="<?php echo uh($translator->{'Request connection'}); ?>"/></a></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
