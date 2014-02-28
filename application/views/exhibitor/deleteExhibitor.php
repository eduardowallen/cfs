<h1>Test</h1>
<h1><?php echo $headline; ?></h1>

<p><?php echo $warning . ' ' . $exhibitor->get('name'); ?>?</p>

<p><a class="button add" href="exhibitor/deleteAccount/<?php echo $exhibitor_id; ?>"><?php echo $yes; ?></a>
<a class="button delete" href="exhibitor/<?php echo $from; ?>"><?php echo $no; ?></a></p>