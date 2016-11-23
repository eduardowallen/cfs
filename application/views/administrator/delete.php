<h1><?php echo $headline; ?></h1>

<p><?php echo $warning . ' ' . $administrator->get('name'); ?>?</p>

<p><a class="button add" href="administrator/delete/<?php echo $admin_id; ?>/confirmed/<?php echo $from; ?>"><?php echo $yes; ?></a>
<a class="button delete" href="administrator/<?php echo $from; ?>/<?php echo $_SESSION['user_fair'] ?>"><?php echo $no; ?></a></p>