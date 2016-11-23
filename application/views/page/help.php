<h1 style="text-align:center;"><?php echo $headline; ?></h1>
<img src="images/icons/close_dialogue.png" alt="" class="closeDialogue" />
<!--<input type="text" id="search-criteria"/>
<input type="button" id="search" value="search"/>-->

<div id="help_content">

<script type="text/javascript">
$(document).ready(function(e) {
jQuery( function( $ ) {
        //$('h2').nextUntil('h2').hide();
        $('h2').click( function() { $(this).nextUntil('h2').toggle(400); } );
});
});
</script>
<?php echo $content; ?>
<!--
<script>
$('h2:p').hide();
$('#search').click(function(){
    $('h2:p').hide();
   var txt = $('#search-criteria').val();
   $('p:contains("'+txt+'")').show();
});
</script>-->
</div>