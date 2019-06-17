<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>
<br />
<h1><?php echo $headline; ?></h1>

<table class="std_table">
	<thead>
		<tr>
			<th><?php echo $th_name; ?></th>
			<th><?php echo $th_view; ?></th>
			<th><?php echo $th_edit; ?></th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($reserved as $pos): ?>
		<tr>
			<td><?php echo $pos['expires']; ?></td>
			<td class="center"><img src="images/icons/map_go.png" alt="" title="<?php echo $th_view; ?>" /></td>
			<td class="center"><img src="images/icons/pencil.png" alt="" title="<?php echo $th_edit; ?>" /></td>
		</tr>
<?php endforeach; ?>
	</tbody>
</table>
