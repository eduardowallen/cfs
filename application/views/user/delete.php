<h1><?php echo $headline; ?></h1>

<p><?php echo $warning . " " . $user->get("name"); ?></p>

<p><a class="button add" href="user/delete/<?php echo $user_id; ?>/confirmed"><?php echo $yes; ?></a>
<a class="button delete" href="user/overview"><?php echo $no; ?></a></p>