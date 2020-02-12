<?php
global $translator;
?>

<style>
  #content{max-width:1280px;}
  .std_table { clear: both; }
  .squaredFour{width:15px; height:15px;}
  .squaredFour:before{left:3px;top:3px;}
  .nav{max-width:100em;}
  .icon_img{cursor:pointer;}
</style>
<script type="text/javascript">

    function confirmCreditInvoice(link, posname, company) {
      confirmBoxNewTab('<?php echo $confirm_credit_invoice; ?> ' + posname +' (' + company + ')?', link);
    }
    function confirmCancelInvoice(link, posname, company) {
      confirmBoxNewTab('<?php echo $confirm_cancel_invoice; ?> ' + posname +' (' + company + ')?', link);
    }
    function confirmMarkAsSent(link, posname, company, id) {
      confirmBoxNoTab('<?php echo $confirm_mark_as_sent; ?> ' + posname +' (' + company + ')?', link, id);
    }
    $body = $("body");
$(document).on({
    ajaxStart: function() { $body.addClass("loading");    },
     ajaxStop: function() { $body.removeClass("loading"); }
});
$(document.body).on('click', '.open-credit-invoices', creditInvoices);
$(document.body).on('click', '.open-send-invoices', sendInvoices);
$(document.body).on('click', '.open-delete-invoices', deleteInvoices);
</script>

<?php if (userLevel() == 4): ?>
  <script type="text/javascript">
function deleteInvoices(e) {
  e.preventDefault();
  var button = $(e.target);
  var table_form = $(button.prop('form'));
  invoices_to_delete = 0;
	invoices_left = 1;
  what_to_delete = '<p>';
  $('input[name*=rows]:checked', table_form).each(function(index, input) {
      if ($(input).data('id'))
          what_to_delete += $(input).data('id')+'-'+$(input).data('invoicecompany')+'.pdf<br>';
          invoices_to_delete += $(input).length;
  });
  what_to_delete += '</p>';
  if (invoices_to_delete != 0) {
    $.confirm({
        title: '<?php echo $confirm_delete_invoices; ?>',
        content: '<?php echo uh($translator->{"This will remove the selected invoices PERMANENTLY"}); ?>'+what_to_delete,
        confirm: function(){
            $body.addClass("progress");
            $body.removeClass("loading");
            $('input[name*=rows]:checked', table_form).each(function(index, input) {
                $body.removeClass("loading");
                $.ajax({
                    url: 'administrator/deleteInvoice',
                    method: 'POST',
                    data: 'row_id=' + $(input).data('row_id'),
                    success: function(){
                        $('progress').val(invoices_left / invoices_to_delete * 100);
                        invoices_left++;
                        $('#invoice_deletion_progress').text(invoices_left + '/' + invoices_to_delete);
                        console.log($(input).val());
                    }
                });
            });

            $(document).on({
                ajaxStop: function() { 
                    $body.removeClass("progress");
                    $.alert({
                        content: '<?php echo uh($translator->{"The invoices were successfully deleted."}); ?>',
                        confirm: function() {
                            document.location.reload();
                        }
                    });
                }
            });
        },
        cancel: function(){}
    });
  }
}
</script>
<?php endif; ?>

<script type="text/javascript">
function sendInvoices(e) {
  e.preventDefault();

  var button = $(e.target);
  var table_form = $(button.prop('form'));
  var html = '<label for="send_invoices_text"><?php echo uh($translator->{"Add message to this mailing"}); ?></label> <textarea name="send_invoices_text" id="send_invoices_text" value="" rows="5"/>';
  invoices_to_send = 0;
  invoices_left = 1;
  $('input[name*=rows]:checked', table_form).each(function(index, input) {
    invoices_to_send += $(input).length;
  });
  
  if (invoices_to_send != 0) {
    $.confirm({
      title: '<?php echo $confirm_send_invoices?>',
      content: html,
        confirm: function(){
            $body.addClass("progress");
            $body.removeClass("progress2");
            $body.removeClass("loading");
            var msg = this.$content.find('textarea').val(); // get the input value.
            msg = msg.replace(/\r?\n/g, '<br>');
            $('input[name*=rows]:checked', table_form).each(function(index, input) {
              setTimeout(function() {       
              $body.removeClass("loading");
              console.log(msg);
              $.ajax({
                url: 'administrator/sendInvoices',
                method: 'POST',
                data: 'invoice_id=' + $(input).data('row_id') + '&msg=' + encodeURIComponent(msg),
                success: function(){
                  console.log(msg);
                  $('progress').val(invoices_left / invoices_to_send * 100);
                  invoices_left++;
                  $('#invoice_send_progress').text(invoices_left + '/' + invoices_to_send);
                }
              });
              }, 3000);
            });
            $(document).on({
              ajaxStop: function() { 
                $body.removeClass("progress");
                $.alert({
                    content: '<?php echo uh($translator->{"The invoices were successfully sent."}); ?>',
                    confirm: function() {
                      document.location.reload();
                    }
                });
              }
            });
        },
        cancel: function(){}
      });
  }
}

function creditInvoices(e) {
  e.preventDefault();

  var button = $(e.target);
  var table_form = $(button.prop('form'));
  invoices_to_create = 0;
  invoices_to_credit = [];
  invoices_left = 1;
  $('input[name*=rows]:checked', table_form).each(function(index, input) {
    invoices_to_create += $(input).length;
    invoices_to_credit += $(input).data('id') + '<br>';
    console.log('exhibitor_id: '+$(input).data('exhibitor'));
    console.log('row_id: '+$(input).data('row_id'));
  });
  var html_invoices_to_credit = '<p>' + invoices_to_credit + '</p>';
  $.confirm({
    title: '<?php echo $confirm_credit_invoices; ?>',
    content: '<?php echo uh($translator->{"This will credit the following invoices"}); ?>:' + html_invoices_to_credit,
    confirm: function(){
      $body.addClass("progress2");
      $body.removeClass("progress");
      $body.removeClass("loading");
      $('input[name*=rows]:checked', table_form).each(function(index, input) {
        $body.removeClass("loading");
        $.ajax({
          url: 'administrator/creditInvoicePDF/' + $(input).data('exhibitor') + '/' + $(input).data('row_id'),
          method: 'POST',
          success: function(){
            $('progress').val(invoices_left / invoices_to_create * 100);
            invoices_left++;
            $('#invoice_credit_progress').text(invoices_left + '/' + invoices_to_create);
          }
        });
      });

      $(document).on({
        ajaxStop: function() { 
          $body.removeClass("progress2");
          $.alert({
              content: '<?php echo uh($translator->{"The invoices were successfully created."}); ?>',
              confirm: function() {
                document.location.reload();
              }
          });
        }
      });
    },
    cancel: function(){}
  });
}
</script>
<?php if (!empty($mail_errors)): ?>
  <script>
  showInfoDialog('<?php echo implode('<br>', $mail_errors); ?>', '<?php echo $error_title; ?>');
  </script>
<?php endif; ?>
<?php if (isset($event_locked)): ?>
  <script>
  showInfoDialog('<?php echo $event_locked_content; ?>', '<?php echo $event_locked_title; ?>');
  </script>
<?php endif; ?>
<script type="text/javascript" src="js/tablesearch.js<?php echo $unique?>"></script>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>start/home'"><?php echo uh($translator->{'Go back'}); ?></button>
<h1><?php echo $fair->get('name'); ?> - <?php echo $headline; ?></h1>

<?php if (isset($fairs_admin)): // If a list of accessible fairs is found, display a drop-down list to choose from ?>
  <label class="inline-block"><?php echo uh($translator->{'Switch to event: '}); ?>&nbsp</label>
  <select onchange="if(this.value) document.location.href=this.value;">
  <?php
    $own = false;
    $options = '';
    foreach($fairs_admin as $fa) {
      $active = $fair->get('id') == $fa['id'];
      $own = $own || $active;
      $options .= '<option value="'.BASE_URL.'administrator/invoicesChangeFair/'.$fa['id'].'"'.($active?" selected":"").'>'.$fa['name'].'</option>';
    }
    
    if(!$own) :
  ?>
    <option value selected><?php echo $fair->get('name'); ?></option>
  <?php
    endif;
    echo $options;
  ?>
  </select>
  <br class="clear">
  <br class="clear">
<?php endif; ?>
<?php if ($hasRights && $fair->wasLoaded()): ?>

  <div role="tabpanel">

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="javascript:void(0)" id="iactive" class="tabs-tab" aria-controls="home" role="tab" data-toggle="tab"><img src="images/icons/invoice.png" class="icon_img" /> <?php echo uh($translator->{'Active invoices tab'}); ?> (<?php echo count($active_invoices); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="ipaid" class="tabs-tab" aria-controls="profile" role="tab" data-toggle="tab"><img src="images/icons/add.png" class="icon_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Paid invoices tab'}); ?> (<?php echo count($paid_invoices); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="icredited" class="tabs-tab" aria-controls="profile" role="tab" data-toggle="tab"><img src="images/icons/invoice_credit.png" class="icon_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Credited invoices tab'}); ?> (<?php echo (count($credited_invoices) + count($old_credited_invoices)); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="icancelled" class="tabs-tab" aria-controls="messages" role="tab" data-toggle="tab"><img src="images/icons/invoice_cancel.png" class="icon_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Cancelled invoices tab'}); ?> (<?php echo (count($cancelled_invoices) + count($old_cancelled_invoices)); ?>)</a></li>
  </ul>

  <!-- Tab panes -->
    <div class="tab-content">
      <div role="tabpanel" class="tab-pane active" id="iactive">
  <script>

  $(document).ready(function() {
      // go to the latest tab, if it exists:
      var lastTab2 = localStorage.getItem('lastTab2');

      if (lastTab2) {
        var selected = lastTab2;
        var div = 'div#' + selected;
        $('.tab-div').css('display', 'none');
        $('li').removeClass('active');
        $(this).parent().attr('class', 'active');
        $(div).css('display', 'block');
        if (!$(div + ' table').hasClass('scrolltable')) {
          useScrolltable($(div + ' table'));
        }
        $(selected).floatThead('reflow');
        $(selected).floatThead('getSizingRow');
        $('[id="' + lastTab2 + '"]').tab('show');

      } else {
        var selected = 'booked';
        var div = 'div#' + selected;
        $('.tab-div').css('display', 'none');
        $('li').removeClass('active');
        $(this).parent().attr('class', 'active');
        $(div).css('display', 'block');
        if (!$(div + ' table').hasClass('scrolltable')) {
          useScrolltable($(div + ' table'));
        }
      }
  $('.tabs-tab').on("click", function() {
    var selected = $(this).attr('id');
    var div = 'div#' + selected;
    $('.tab-div').css('display', 'none');
    $('li').removeClass('active');
    $(this).parent().attr('class', 'active');
    $(div).css('display', 'block');
    if (!$(div + ' table').hasClass('scrolltable')) {
      useScrolltable($(div + ' table'));
    }
    localStorage.setItem('lastTab2', $(this).attr('id'));
  });
});

  </script>

        <div id="iactive" style="display:none" class="tab-div tab-div-hidden">


<?php if (count($active_invoices) > 0) { ?>

  <form action="" method="post">
      <div class="floatright right">
        <button type="submit" class="open-send-invoices greenbutton mediumbutton" title="<?php echo uh($translator->{'Send invoices for the selected rows'}); ?>" name="send_invoices" data-for="iactive"><?php echo uh($translator->{'Send invoices'}); ?></button>
        <!--<button type="submit" class="open-credit-invoices greenbutton mediumbutton" title="<?php echo uh($translator->{'Credit invoices for the selected rows'}); ?>" name="credit_invoices" data-for="iactive"><?php echo uh($translator->{'Credit invoices'}); ?></button>-->
        <button type="submit" class="greenbutton mediumbutton zip-invoices" title="<?php echo uh($translator->{'Export checked invoices and download as zip'}); ?>" data-for="iactive"><?php echo uh($translator->{'Download invoices'}); ?></button>
        <?php if (userLevel() == 4): ?>
          <button type="submit" class="open-delete-invoices greenbutton mediumbutton" title="<?php echo uh($translator->{'Delete invoices for the selected rows'}); ?>" name="delete_invoices" data-for="iactive"><?php echo uh($translator->{'Delete invoices'}); ?></button>
        <?php endif; ?>
      </div>
      <table class="std_table use-scrolltable" id="iactive">
        <thead>
          <tr>
            <th class="left"><?php echo $tr_id; ?></th>
            <th class="left"><?php echo $tr_posname; ?></th>
            <th class="left"><?php echo $tr_company; ?></th>
            <th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_created; ?></th>
            <th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_sent; ?></th>
            <th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_expires; ?></th>
            <th data-sorter="false"><?php echo $tr_view; ?></th>
            <th data-sorter="false"><?php echo $tr_credit; ?></th>
          <?php if (!isset($event_locked)) { ?>
            <th data-sorter="false"><?php echo $tr_cancel;   ?></th>
          <?php } ?>
            <th class="last" data-sorter="false">
              <input type="checkbox" id="check-all-iactive" class="check-all" data-group="rows-1" />
              <label class="squaredFour" for="check-all-iactive" />
            </th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($active_invoices as $invoice):?>
          <tr
            data-id="<?php echo $invoice['id']; ?>"
            data-row_id="<?php echo $invoice['row_id']; ?>"
            data-exhibitor="<?php echo $invoice['exhibitor']; ?>"
            data-posname="<?php echo $invoice['posname']; ?>"
            data-crediturl="<?php echo BASE_URL.'administrator/invoices/creditInvoice/'; ?>"
          >
            <td class="left"><?php echo $invoice['id']; ?></td>
            <td class="left"><?php echo $invoice['posname']; ?></td>
            <td class="left"><a href="exhibitor/profile/<?php echo $invoice['ex_user']; ?>" class="showProfileLink"><?php echo $invoice['r_name']; ?></a></td>
            <td><?php echo date('d-m-Y H:i:s', $invoice['created']); ?></td>
            <td><?php echo ($invoice['sent'] > 0 ? date('d-m-Y H:i:s', $invoice['sent']) : '-'); ?></td>
            <td><?php echo date('d-m-Y', strtotime($invoice['expires'])); ?></td>
            <td class="center">

<?php
$replace_chars2 = array(
	'/' => '-',
	'"' => '&quot;',
	':' => '_'
);
$replace_chars = array(
	'/' => '-',
	"'" => '\u0027',
	'"' => '&quot;'
	//':' => '_'
);
$exhibitor_id = strtr($invoice['exhibitor'], $replace_chars);
$r_name = strtr($invoice['r_name'], $replace_chars);
$r_name2 = strtr($invoice['r_name'], $replace_chars2);
$posname = strtr($invoice['posname'], $replace_chars);
?>
              <a href="<?php echo BASE_URL.'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$exhibitor_id.'/' . $r_name2 . '-' . $posname . '-' . $invoice['id'] . '.pdf'?>" target="_blank">
                <img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
              </a>
            </td>
            <td class="center">
              <a onclick="confirmCreditInvoice('<?php echo BASE_URL.'administrator/creditInvoicePDF/'.$exhibitor_id.'/'.$invoice['row_id']?>', '<?php echo $posname?>', '<?php echo $r_name?>')" title="<?php echo $tr_credit; ?>">
                <img  src="<?php echo BASE_URL; ?>images/icons/invoice_credit.png" class="icon_img" alt="<?php echo $tr_credit; ?>" />
              </a>
            </td>
            <?php if (!isset($event_locked)) { ?>
            <td class="center">
              <a onclick="confirmCancelInvoice('<?php echo BASE_URL.'administrator/cancelInvoicePDF/'.$exhibitor_id.'/'.$invoice['row_id']?>', '<?php echo $posname?>', '<?php echo $r_name?>')" title="<?php echo $tr_cancel; ?>">
                <img  src="<?php echo BASE_URL; ?>images/icons/invoice_cancel.png" class="icon_img" alt="<?php echo $tr_cancel; ?>" />
              </a>
            </td>
            <?php } ?>
            <td class="last"><input type="checkbox" name="rows[]" value="<?php echo $invoice['id']; ?>" data-ziplink="<?php echo 'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$exhibitor_id.'/'.str_replace('/', '-', $r_name) . '-' . $posname . '-' . $invoice['id'] . '.pdf'; ?>" data-id="<?php echo $invoice['id']; ?>" data-row_id="<?php echo $invoice['row_id']; ?>" data-invoicecompany="<?php echo $r_name2; ?>" data-exhibitor="<?php echo $invoice['exhibitor']; ?>" data-posname="<?php echo $invoice['posname']; ?>" data-userid="<?php echo $invoice['ex_user']; ?>" class="rows-1" /><label class="squaredFour" for="<?php echo $invoice['id']; ?>" /></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </form>

<?php } else { ?>
  <p><?php echo uh($translator->{'There are currently no active invoices.'}); ?></p>
<?php } ?>
        </div>
      </div>

      <div role="tabpanel" class="tab-pane" id="ipaid">

  <div id="ipaid" style="display:none" class="tab-div tab-div-hidden">


<?php if (count($paid_invoices) > 0) { ?>
    <form id="ipaidform" method="post" accept-charset="utf-8">
      <div class="floatright right">
        <button type="submit" class="greenbutton mediumbutton zip-invoices" title="<?php echo uh($translator->{'Export checked invoices and download as zip'}); ?>" data-for="ipaid"><?php echo uh($translator->{'Download invoices'}); ?></button>
      </div>
      <table class="std_table use-scrolltable" id="ipaid">
        <thead>
          <tr>
            <th class="left"><?php echo $tr_id; ?></th>
            <th class="left"><?php echo $tr_posname; ?></th>
            <th class="left"><?php echo $tr_company; ?></th>
            <th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_created; ?></th>
            <th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_sent; ?></th>
            <th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_expires; ?></th>
            <th data-sorter="false"><?php echo $tr_view; ?></th>
          <?php if (!isset($event_locked)) { ?>
            <th data-sorter="false"><?php echo $tr_credit; ?></th>
            <th data-sorter="false"><?php echo $tr_cancel; ?></th>
          <?php } ?>
            <th class="last" data-sorter="false">
              <input type="checkbox" id="check-all-ipaid" class="check-all" data-group="rows-2" />
              <label class="squaredFour" for="check-all-ipaid" />
            </th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($paid_invoices as $invoice):?>
          <tr
            data-id="<?php echo $invoice['id']; ?>"
            data-posname="<?php echo $invoice['posname']; ?>"
            data-crediturl="<?php echo BASE_URL.'administrator/invoices/creditInvoice/'; ?>"
          >
            <td class="left"><?php echo $invoice['id']; ?></td>
            <td class="left"><?php echo $invoice['posname']; ?></td>
            <td class="left"><a href="exhibitor/profile/<?php echo $invoice['ex_user']; ?>" class="showProfileLink"><?php echo $invoice['r_name']; ?></a></td>
            <td><?php echo date('d-m-Y H:i:s', $invoice['created']); ?></td>
            <td><?php echo ($invoice['sent'] > 0 ? date('d-m-Y H:i:s', $invoice['sent']) : '-'); ?></td>
            <td><?php echo date('d-m-Y', strtotime($invoice['expires'])); ?></td>
            <td class="center">
<?php
$replace_chars2 = array(
	'/' => '-',
	'"' => '&quot;',
	':' => '_'
);
$replace_chars = array(
	'/' => '-',
	"'" => '\u0027',
	'"' => '&quot;'
	//':' => '_'
);
$exhibitor_id = strtr($invoice['exhibitor'], $replace_chars);
$r_name = strtr($invoice['r_name'], $replace_chars);
$r_name2 = strtr($invoice['r_name'], $replace_chars2);
$posname = strtr($invoice['posname'], $replace_chars);
?>
              <a href="<?php echo BASE_URL.'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$exhibitor_id.'/' . $r_name2 . '-' . $posname . '-' . $invoice['id'] . '.pdf'?>" target="_blank">
                <img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
              </a>
            </td>
          <?php if (!isset($event_locked)) { ?>
            <td class="center">
              <a onclick="confirmCreditInvoice('<?php echo BASE_URL.'administrator/creditInvoicePDF/'.$exhibitor_id.'/'.$invoice['row_id']?>', '<?php echo $posname?>', '<?php echo $r_name?>')" title="<?php echo $tr_credit; ?>">
                <img  src="<?php echo BASE_URL; ?>images/icons/invoice_credit.png" class="icon_img" alt="<?php echo $tr_credit; ?>" />
              </a>
            </td>
            <td class="center">
              <a onclick="confirmCancelInvoice('<?php echo BASE_URL.'administrator/cancelInvoicePDF/'.$exhibitor_id.'/'.$invoice['row_id']?>', '<?php echo $posname?>', '<?php echo $r_name?>')" title="<?php echo $tr_cancel; ?>">
                <img  src="<?php echo BASE_URL; ?>images/icons/invoice_cancel.png" class="icon_img" alt="<?php echo $tr_cancel; ?>" />
              </a>
            </td>
          <?php } ?>
            <td class="last"><input type="checkbox" name="rows[]" value="<?php echo $invoice['id']; ?>" data-ziplink="<?php echo 'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$exhibitor_id.'/'.str_replace('/', '-', $r_name) . '-' . $posname . '-' . $invoice['id'] . '.pdf'; ?>" data-userid="<?php echo $invoice['ex_user']; ?>" class="rows-2" /><label class="squaredFour" for="<?php echo $invoice['id']; ?>" /></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </form>
<?php } else { ?>
  <p><?php echo uh($translator->{'There are currently no paid invoices.'}); ?></p>
<?php } ?>
        </div>
      </div>

      <div role="tabpanel" class="tab-pane" id="icredited">

  <div id="icredited" style="display:none" class="tab-div tab-div-hidden">


<?php if (count($credited_invoices) > 0 || count($old_credited_invoices) > 0) { ?>
    <form id="icredited" method="post" accept-charset="utf-8">
      <div class="floatright right">
        <button type="submit" class="greenbutton mediumbutton zip-invoices" title="<?php echo uh($translator->{'Export checked invoices and download as zip'}); ?>" data-for="icredited"><?php echo uh($translator->{'Download invoices'}); ?></button>
      </div>
      <table class="std_table use-scrolltable" id="icredited">
        <thead>
          <tr>
            <th class="left"><?php echo $tr_id; ?></th>
            <th class="left"><?php echo $tr_posname; ?></th>
            <th class="left"><?php echo $tr_company; ?></th>
            <th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_created; ?></th>
            <th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_sent; ?></th>
            <th data-sorter="false"><?php echo $tr_view; ?></th>
            <th class="last" data-sorter="false">
              <input type="checkbox" id="check-all-icredited" class="check-all" data-group="rows-3" />
              <label class="squaredFour" for="check-all-icredited" />
            </th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($credited_invoices as $invoice):?>
          <tr>
            <td class="left"><?php echo $invoice['id']; ?></td>
            <td class="left"><?php echo $invoice['posname']; ?></td>
            <td class="left"><a href="exhibitor/profile/<?php echo $invoice['ex_user']; ?>" class="showProfileLink"><?php echo $invoice['r_name']; ?></a></td>
            <td><?php echo date('d-m-Y H:i:s', $invoice['created']); ?></td>
            <td><?php echo ($invoice['sent'] > 0 ? date('d-m-Y H:i:s', $invoice['sent']) : '-'); ?></td>
            <td class="center">
<?php
$replace_chars2 = array(
	'/' => '-',
	'"' => '&quot;',
	':' => '_'
);
$replace_chars = array(
	'/' => '-',
	"'" => '\u0027',
	'"' => '&quot;'
	//':' => '_'
);
$exhibitor_id = strtr($invoice['exhibitor'], $replace_chars);
$r_name = strtr($invoice['r_name'], $replace_chars);
$r_name2 = strtr($invoice['r_name'], $replace_chars2);
$posname = strtr($invoice['posname'], $replace_chars);
?>
              <a href="<?php echo BASE_URL.'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$exhibitor_id.'/' . $r_name2 . '-' . $posname . '-' . $invoice['id'] . '_credited.pdf'?>" target="_blank">
                <img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
              </a>
            </td>
            <td class="last"><input type="checkbox" name="rows[]" value="<?php echo $invoice['id']; ?>" data-ziplink="<?php echo 'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$exhibitor_id.'/'.str_replace('/', '-', $r_name) . '-' . $posname . '-' . $invoice['id'] . '_credited.pdf'; ?>" data-userid="<?php echo $invoice['ex_user']; ?>" class="rows-3" /><label class="squaredFour" for="<?php echo $invoice['id']; ?>" /></td>
          </tr>
        <?php endforeach; ?>
        <?php foreach($old_credited_invoices as $invoice):?>
          <tr>
            <td class="left"><?php echo $invoice['cid']; ?></td>
            <td class="left"><?php echo $invoice['posname']; ?></td>
            <td class="left"><a href="exhibitor/profile/<?php echo $invoice['ex_user']; ?>" class="showProfileLink"><?php echo $invoice['r_name']; ?></a></td>
            <td><?php echo date('d-m-Y H:i:s', $invoice['created']); ?></td>
            <td><?php echo ($invoice['sent'] > 0 ? date('d-m-Y H:i:s', $invoice['sent']) : '-'); ?></td>
            <td class="center">
<?php
$replace_chars2 = array(
	'/' => '-',
	'"' => '&quot;',
	':' => '_'
);
$replace_chars = array(
	'/' => '-',
	"'" => '\u0027',
	'"' => '&quot;'
	//':' => '_'
);
$exhibitor_id = strtr($invoice['exhibitor'], $replace_chars);
$r_name = strtr($invoice['r_name'], $replace_chars);
$r_name2 = strtr($invoice['r_name'], $replace_chars2);
$posname = strtr($invoice['posname'], $replace_chars);
?>
              <a href="<?php echo BASE_URL.'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$exhibitor_id.'/' . $r_name2 . '-' . $posname . '-' . $invoice['cid'] . '_credited.pdf'?>" target="_blank">
                <img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
              </a>
            </td>
            <td class="last"><input type="checkbox" name="rows[]" value="<?php echo $invoice['cid']; ?>" data-ziplink="<?php echo 'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$exhibitor_id.'/'.str_replace('/', '-', $r_name) . '-' . $posname . '-' . $invoice['cid'] . '_credited.pdf'; ?>" data-userid="<?php echo $invoice['ex_user']; ?>" class="rows-3" /><label class="squaredFour" for="<?php echo $invoice['cid']; ?>" /></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </form>
<?php } else { ?>
  <p><?php echo uh($translator->{'There are currently no credited invoices.'}); ?></p>
<?php } ?>
        </div>
      </div>


      <div role="tabpanel" class="tab-pane" id="icancelled">
        <div id="icancelled" style="display:none" class="tab-div tab-div-hidden">


<?php if (count($old_cancelled_invoices) > 0 || count($cancelled_invoices) > 0) { ?>
    <form id="minvoices" method="post" accept-charset="utf-8">
      <div class="floatright right">
        <button type="submit" class="greenbutton mediumbutton zip-invoices" title="<?php echo uh($translator->{'Export checked invoices and download as zip'}); ?>" data-for="minvoices"><?php echo uh($translator->{'Download invoices'}); ?></button>
      </div>
      <table class="std_table use-scrolltable" id="icancelled">
        <thead>
          <tr>
            <th class="left"><?php echo $tr_id; ?></th>
            <th class="left"><?php echo $tr_posname; ?></th>
            <th class="left"><?php echo $tr_company; ?></th>
            <th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_created; ?></th>
            <th class="sorter-shortDate dateFormat-ddmmyyyy"><?php echo $tr_sent; ?></th>
            <th data-sorter="false"><?php echo $tr_view; ?></th>
            <th class="last" data-sorter="false">
              <input type="checkbox" id="check-all-icancelled" class="check-all" data-group="rows-4" />
              <label class="squaredFour" for="check-all-icancelled" />
            </th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($cancelled_invoices as $invoice):?>
          <tr
            data-id="<?php echo $invoice['id']; ?>"
            data-posname="<?php echo $invoice['posname']; ?>"
          >
            <td class="left"><?php echo $invoice['id']; ?></td>
            <td class="left"><?php echo $invoice['posname']; ?></td>
            <td class="left"><a href="exhibitor/profile/<?php echo $invoice['ex_user']; ?>" class="showProfileLink"><?php echo $invoice['r_name']; ?></a></td>
            <td><?php echo date('d-m-Y H:i:s', $invoice['created']); ?></td>
            <td><?php echo ($invoice['sent'] > 0 ? date('d-m-Y H:i:s', $invoice['sent']) : '-'); ?></td>
            <td class="center">
<?php
$replace_chars2 = array(
	'/' => '-',
	'"' => '&quot;',
	':' => '_'
);
$replace_chars = array(
	'/' => '-',
	"'" => '\u0027',
	'"' => '&quot;'
	//':' => '_'
);
$exhibitor_id = strtr($invoice['exhibitor'], $replace_chars);
$r_name = strtr($invoice['r_name'], $replace_chars);
$r_name2 = strtr($invoice['r_name'], $replace_chars2);
$posname = strtr($invoice['posname'], $replace_chars);
?>
              <a href="<?php echo BASE_URL.'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$exhibitor_id.'/' . $r_name2 . '-' . $posname . '-' . $invoice['id'] . '_cancelled.pdf'?>" target="_blank">
                <img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
              </a>
            </td>
            <td class="last"><input type="checkbox" name="rows[]" value="<?php echo $invoice['id']; ?>" data-ziplink="<?php echo 'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$exhibitor_id.'/'.str_replace('/', '-', $r_name) . '-' . $posname . '-' . $invoice['id'] . '_cancelled.pdf'; ?>" data-userid="<?php echo $invoice['ex_user']; ?>" class="rows-4" /><label class="squaredFour" for="<?php echo $invoice['id']; ?>" /></td>
          </tr>
        <?php endforeach; ?>
        <?php foreach($old_cancelled_invoices as $invoice):?>
          <tr
            data-id="<?php echo $invoice['id']; ?>"
            data-posname="<?php echo $invoice['posname']; ?>"
          >
            <td class="left"><?php echo $invoice['id']; ?></td>
            <td class="left"><?php echo $invoice['posname']; ?></td>
            <td class="left"><a href="exhibitor/profile/<?php echo $invoice['ex_user']; ?>" class="showProfileLink"><?php echo $invoice['r_name']; ?></a></td>
            <td><?php echo date('d-m-Y H:i:s', $invoice['created']); ?></td>
            <td><?php echo ($invoice['sent'] > 0 ? date('d-m-Y H:i:s', $invoice['sent']) : '-'); ?></td>
            <td class="center">
<?php
$replace_chars2 = array(
	'/' => '-',
	'"' => '&quot;',
	':' => '_'
);
$replace_chars = array(
	'/' => '-',
	"'" => '\u0027',
	'"' => '&quot;'
	//':' => '_'
);
$exhibitor_id = strtr($invoice['exhibitor'], $replace_chars);
$r_name = strtr($invoice['r_name'], $replace_chars);
$r_name2 = strtr($invoice['r_name'], $replace_chars2);
$posname = strtr($invoice['posname'], $replace_chars);
?>
              <a href="<?php echo BASE_URL.'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$exhibitor_id.'/' . $r_name2 . '-' . $posname . '-' . $invoice['id'] . '_cancelled.pdf'?>" target="_blank">
                <img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
              </a>
            </td>
            <td class="last"><input type="checkbox" name="rows[]" value="<?php echo $invoice['id']; ?>" data-ziplink="<?php echo 'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$exhibitor_id.'/'.str_replace('/', '-', $r_name) . '-' . $posname . '-' . $invoice['id'] . '_cancelled.pdf'; ?>" data-userid="<?php echo $invoice['ex_user']; ?>" class="rows-4" /><label class="squaredFour" for="<?php echo $invoice['id']; ?>" /></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </form>
<?php } else { ?>
  <p><?php echo uh($translator->{'There are currently no cancelled invoices.'}); ?></p>
<?php } ?>
      </div>
    </div>
  </div>
</div>

<?php else: ?>
  <p><?php echo uh($translator->{'You are not authorized to administer this fair.'}); ?></p>
<?php endif; ?>
<div class="modal">
  <div style="margin-top: 50vh;">
  <img src="../images/ajax-loader.gif" style="margin-bottom: 0.5em;">
  <p><?php echo uh($translator->{'Loading...'}); ?></p>
  <!-- Place at bottom of page -->
  </div>
</div>
<div class="modal-2">
  <div style="margin-top: 50vh;">
  <p><?php echo uh($translator->{'Sending invoices...'}); ?></p>
  <progress max="100" value="0"></progress>
  <p id="invoice_send_progress"></p>
  <!-- Place at bottom of page -->
  </div>
</div>
<div class="modal-3">
  <div style="margin-top: 50vh;">
  <p><?php echo uh($translator->{'Crediting invoices...'}); ?></p>
  <progress max="100" value="0"></progress>
  <p id="invoice_credit_progress"></p>
  <!-- Place at bottom of page -->
  </div>
</div>
<div class="modal-4">
  <div style="margin-top: 50vh;">
  <p><?php echo uh($translator->{'Deleting invoices...'}); ?></p>
  <progress max="100" value="0"></progress>
  <p id="invoice_deletion_progress"></p>
  <!-- Place at bottom of page -->
  </div>
</div>