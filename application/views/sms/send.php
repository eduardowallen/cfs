		<h1>SMS-debug</h1>

<?php if (isset($error)): ?>
		<p>
			Fel: <?php echo $error; ?><br />
			Kod: <?php echo $code; ?>
		</p>
<?php else: ?>
		<p>
			Resultat: <?php echo $send_result; ?>
		</p>
<?php endif; ?>