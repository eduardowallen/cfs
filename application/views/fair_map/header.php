<?php
	$unique = substr(md5(date('YmdHis')), -10);
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="../../css/generic.css?u=<?php echo $unique?>" />
		<link rel="stylesheet" type="text/css" href="../../css/main.css?u=<?php echo $unique?>" />
	</head>
<body>
	<div id="iframeContent">
