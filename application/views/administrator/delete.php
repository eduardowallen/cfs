<h1><?php echo $headline; ?></h1>

<p><?php echo $warning; ?></p>

<?php if ($from == 'mine'): ?>
<p><a class="button add" href="administrator/delete/<?php echo $admin_id; ?>/confirmed/mine"><?php echo $yes; ?></a>
<a class="button delete" href="administrator/mine"><?php echo $no; ?></a></p>
<?php else: ?>
<p><a class="button add" href="administrator/delete/<?php echo $admin_id; ?>/confirmed"><?php echo $yes; ?></a>
<a class="button delete" href="administrator/overview/<?php echo $_SESSION['user_fair'] ?>"><?php echo $no; ?></a></p>
<?php endif;