<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>fair/overview'"><?php echo uh($translator->{'Go back'}); ?></button>
<br />
<h1><?php echo $headline; ?></h1>

<p><?php echo $warning; ?></p>

<p><a class="button add" href="fair/delete/<?php echo $fair_id ?>/confirmed"><?php echo $yes; ?></a>
<a class="button delete" href="fair/overview"><?php echo $no; ?></a></p>