<?php

$to = array('email' => 'eduardo.wallen@chartbooker.com', 'name' => 'Eduardo Wallén');
//$reply_to = array('email' => 'eduardo.wallen@hotmail.com', 'name' => 'Eduardo Wallén');
$subject = 'Testmail';
$template = 'send_invoice';
$mj = new Mail('v3.1');

$mj->sendMessage('eduardo@chartbookerdemo.com', 'Eduardo Wallén', $to, '/var/www/public/invoices/fairs/141/exhibitors/16975/Capeco-A14-55152.pdf');
