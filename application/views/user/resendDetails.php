<?php

if (isset($error_message)) {
	echo '<p class="error">'.$error_message.'</p>';
}

if (isset($user_message)) {
	echo '<p class="ok">'.$user_message.'</p>';
}