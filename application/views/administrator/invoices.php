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
</script>
<script type="text/javascript" src="js/tablesearch.js<?php echo $unique?>"></script>
<button class="go_back" onclick="location.href='<?php echo BASE_URL; ?>start/home'"><?php echo uh($translator->{'Go back'}); ?></button>
<h1><?php echo $fair->get('name'); ?></h1>

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

<?php if ($hasRights): ?>

  <div role="tabpanel">

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="javascript:void(0)" id="iactive" class="tabs-tab" aria-controls="home" role="tab" data-toggle="tab"><img src="images/icons/invoice.png" class="icon_img" /> <?php echo uh($translator->{'Active invoices tab'}); ?> (<?php echo count($ainvoices); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="ipaid" class="tabs-tab" aria-controls="profile" role="tab" data-toggle="tab"><img src="images/icons/add.png" class="icon_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Paid invoices tab'}); ?> (<?php echo count($pinvoices); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="icredited" class="tabs-tab" aria-controls="profile" role="tab" data-toggle="tab"><img src="images/icons/invoice_credit.png" class="icon_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Credited invoices tab'}); ?> (<?php echo count($cinvoices); ?>)</a></li>
    <li role="presentation"><a href="javascript:void(0)" id="icancelled" class="tabs-tab" aria-controls="messages" role="tab" data-toggle="tab"><img src="images/icons/invoice_cancel.png" class="icon_img" style="vertical-align:top;" /> <?php echo uh($translator->{'Cancelled invoices tab'}); ?> (<?php echo count($minvoices); ?>)</a></li>
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

  $('#iactiveform').submit(function(e) {
    if (typeof validate === 'undefined' || validate === null) {
      e.preventDefault();
    var confirmDialogue = "<?php echo $confirm_send_invoices?>";
    var mail = "<?php echo $send_invoice_comment?>";
      $.confirm({
          title: ' ',
          content: confirmDialogue,
          confirm: function(){
            $.confirm({
                title: ' ',
                content: mail + '<textarea cols="50" rows="5"></textarea>',  
              confirm: function(){
                var comment = this.$content.find('textarea').val();
                document.getElementById("invoice_mail_comment").value = comment;
                validate = true;
                console.log(comment);
                 $('#iactiveform').submit();
              },
              cancel: function() {
              }
            });
          },
          cancel: function(){
          }
      });
    } else {

    }
  });
});

  </script>

        <div id="iactive" style="display:none" class="tab-div tab-div-hidden">


<?php if (count($ainvoices) > 0) { ?>

  <form id="iactiveform" action="administrator/sendInvoices/1" method="post" accept-charset="utf-8">
    <input id="invoice_mail_comment" type="hidden" name="invoice_mail_comment" />
      <div class="floatright right">
        <button type="submit" id="send_invoices" class="greenbutton mediumbutton" title="<?php echo uh($translator->{'Sends checked invoices to the exhibitors'}); ?>" data-for="iactive"><?php echo uh($translator->{'Send invoices'}); ?></button>
        <button type="submit" class="greenbutton mediumbutton zip-invoices" title="<?php echo uh($translator->{'Export checked invoices and download as zip'}); ?>" data-for="iactive"><?php echo uh($translator->{'Download invoices'}); ?></button>
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
            <th data-sorter="false"><?php echo $tr_cancel ?></th>
            <th class="last" data-sorter="false">
              <input type="checkbox" id="check-all-iactive" class="check-all" data-group="rows-1" />
              <label class="squaredFour" for="check-all-iactive" />
            </th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($ainvoices as $invoice):?>
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
$replace_chars = array(
	'/' => '-',
	':' => '_',
);
$r_name = strtr($invoice['r_name'], $replace_chars);
$posname = strtr($invoice['posname'], $replace_chars);
?>
              <a href="<?php echo BASE_URL.'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$invoice['exhibitor'].'/' . $r_name . '-' . $posname . '-' . $invoice['id'] . '.pdf'?>" target="_blank">
                <img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
              </a>
            </td>
            <td class="center">
              <a onclick="confirmCreditInvoice('<?php echo BASE_URL.'administrator/creditInvoicePDF/'.$invoice['exhibitor']?>', '<?php echo $invoice["posname"]?>', '<?php echo $invoice["r_name"]?>')" title="<?php echo $tr_credit; ?>">
                <img  src="<?php echo BASE_URL; ?>images/icons/invoice_credit.png" class="icon_img" alt="<?php echo $tr_credit; ?>" />
              </a>
            </td>
            <td class="center">
              <a onclick="confirmCancelInvoice('<?php echo BASE_URL.'administrator/cancelInvoicePDF/'.$invoice['exhibitor']?>', '<?php echo $invoice["posname"]?>', '<?php echo $invoice["r_name"]?>')" title="<?php echo $tr_cancel; ?>">
                <img  src="<?php echo BASE_URL; ?>images/icons/invoice_cancel.png" class="icon_img" alt="<?php echo $tr_cancel; ?>" />
              </a>
            </td>
            <td class="last"><input type="checkbox" name="rows[]" value="<?php echo $invoice['id']; ?>" data-ziplink="<?php echo 'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$invoice['exhibitor'].'/'.str_replace('/', '-', $invoice['r_name']) . '-' . $invoice['posname'] . '-' . $invoice['id'] . '.pdf'; ?>" data-userid="<?php echo $invoice['ex_user']; ?>" class="rows-1" /><label class="squaredFour" for="<?php echo $invoice['id']; ?>" /></td>
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


<?php if (count($pinvoices) > 0) { ?>
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
            <th data-sorter="false"><?php echo $tr_credit; ?></th>
            <th data-sorter="false"><?php echo $tr_cancel ?></th>
            <th class="last" data-sorter="false">
              <input type="checkbox" id="check-all-ipaid" class="check-all" data-group="rows-2" />
              <label class="squaredFour" for="check-all-ipaid" />
            </th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($pinvoices as $invoice):?>
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
$replace_chars = array(
	'/' => '-',
	':' => '_',
);
$r_name = strtr($invoice['r_name'], $replace_chars);
$posname = strtr($invoice['posname'], $replace_chars);
?>
              <a href="<?php echo BASE_URL.'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$invoice['exhibitor'].'/' . $r_name . '-' . $posname . '-' . $invoice['id'] . '.pdf'?>" target="_blank">
                <img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
              </a>
            </td>
            <td class="center">
              <a onclick="confirmCreditInvoice('<?php echo BASE_URL.'administrator/creditInvoicePDF/'.$invoice['exhibitor']?>', '<?php echo $invoice["posname"]?>', '<?php echo $invoice["r_name"]?>')" title="<?php echo $tr_credit; ?>">
                <img  src="<?php echo BASE_URL; ?>images/icons/invoice_credit.png" class="icon_img" alt="<?php echo $tr_credit; ?>" />
              </a>
            </td>
            <td class="center">
              <a onclick="confirmCancelInvoice('<?php echo BASE_URL.'administrator/cancelInvoicePDF/'.$invoice['exhibitor']?>', '<?php echo $invoice["posname"]?>', '<?php echo $invoice["r_name"]?>')" title="<?php echo $tr_cancel; ?>">
                <img  src="<?php echo BASE_URL; ?>images/icons/invoice_cancel.png" class="icon_img" alt="<?php echo $tr_cancel; ?>" />
              </a>
            </td>
            <td class="last"><input type="checkbox" name="rows[]" value="<?php echo $invoice['id']; ?>" data-ziplink="<?php echo 'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$invoice['exhibitor'].'/'.str_replace('/', '-', $invoice['r_name']) . '-' . $invoice['posname'] . '-' . $invoice['id'] . '.pdf'; ?>" data-userid="<?php echo $invoice['ex_user']; ?>" class="rows-2" /><label class="squaredFour" for="<?php echo $invoice['id']; ?>" /></td>
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


<?php if (count($cinvoices) > 0) { ?>
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
            <th data-sorter="false"><?php echo $tr_cancel ?></th>
            <th class="last" data-sorter="false">
              <input type="checkbox" id="check-all-icredited" class="check-all" data-group="rows-3" />
              <label class="squaredFour" for="check-all-icredited" />
            </th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($cinvoices as $invoice):?>
          <tr
            data-id="<?php echo $invoice['id']; ?>"
            data-cid="<?php echo $invoice['cid']; ?>"
            data-cidcreated="<?php echo $invoice['cidcreated']; ?>"
            data-posname="<?php echo $invoice['posname']; ?>"
          >
            <td class="left"><?php echo $invoice['id']; ?></td>
            <td class="left"><?php echo $invoice['posname']; ?></td>
            <td class="left"><a href="exhibitor/profile/<?php echo $invoice['ex_user']; ?>" class="showProfileLink"><?php echo $invoice['r_name']; ?></a></td>
            <td><?php echo date('d-m-Y H:i:s', $invoice['created']); ?></td>
            <td><?php echo ($invoice['sent'] > 0 ? date('d-m-Y H:i:s', $invoice['sent']) : '-'); ?></td>
            <td class="center">
<?php
$replace_chars = array(
	'/' => '-',
	':' => '_',
);
$r_name = strtr($invoice['r_name'], $replace_chars);
$posname = strtr($invoice['posname'], $replace_chars);
?>
              <a href="<?php echo BASE_URL.'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$invoice['exhibitor'].'/' . $r_name . '-' . $posname . '-' . $invoice['cid'] . '_credited.pdf'?>" target="_blank">
                <img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
              </a>
            </td>
            <td class="center">
              <a onclick="confirmCancelInvoice('<?php echo BASE_URL.'administrator/cancelInvoicePDF/'.$invoice['exhibitor']?>', '<?php echo $invoice["posname"]?>', '<?php echo $invoice["r_name"]?>')" title="<?php echo $tr_cancel; ?>">
                <img  src="<?php echo BASE_URL; ?>images/icons/invoice_cancel.png" class="icon_img" alt="<?php echo $tr_cancel; ?>" />
              </a>
            </td>
            <td class="last"><input type="checkbox" name="rows[]" value="<?php echo $invoice['cid']; ?>" data-ziplink="<?php echo 'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$invoice['exhibitor'].'/'.str_replace('/', '-', $invoice['r_name']) . '-' . $invoice['posname'] . '-' . $invoice['cid'] . '_credited.pdf'; ?>" data-userid="<?php echo $invoice['ex_user']; ?>" class="rows-3" /><label class="squaredFour" for="<?php echo $invoice['cid']; ?>" /></td>
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


<?php if (count($minvoices) > 0) { ?>
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
        <?php foreach($minvoices as $invoice):?>
          <tr
            data-id="<?php echo $invoice['id']; ?>"
            data-cid="<?php echo $invoice['cid']; ?>"
            data-cidcreated="<?php echo $invoice['cidcreated']; ?>"
            data-posname="<?php echo $invoice['posname']; ?>"
          >
            <td class="left"><?php echo $invoice['id']; ?></td>
            <td class="left"><?php echo $invoice['posname']; ?></td>
            <td class="left"><a href="exhibitor/profile/<?php echo $invoice['ex_user']; ?>" class="showProfileLink"><?php echo $invoice['r_name']; ?></a></td>
            <td><?php echo date('d-m-Y H:i:s', $invoice['cidcreated']); ?></td>
            <td><?php echo ($invoice['sent'] > 0 ? date('d-m-Y H:i:s', $invoice['sent']) : '-'); ?></td>
            <td class="center">
<?php
$replace_chars = array(
	'/' => '-',
	':' => '_',
);
$r_name = strtr($invoice['r_name'], $replace_chars);
$posname = strtr($invoice['posname'], $replace_chars);
?>
              <a href="<?php echo BASE_URL.'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$invoice['exhibitor'].'/' . $r_name . '-' . $posname . '-' . $invoice['id'] . '_cancelled.pdf'?>" target="_blank">
                <img src="<?php echo BASE_URL; ?>images/icons/review.png" class="icon_img" alt="<?php echo $tr_view; ?>" />
              </a>
            </td>
            <td class="last"><input type="checkbox" name="rows[]" value="<?php echo $invoice['id']; ?>" data-ziplink="<?php echo 'invoices/fairs/'.$fair->get('id').'/exhibitors/'.$invoice['exhibitor'].'/'.str_replace('/', '-', $invoice['r_name']) . '-' . $invoice['posname'] . '-' . $invoice['id'] . '_cancelled.pdf'; ?>" data-userid="<?php echo $invoice['ex_user']; ?>" class="rows-4" /><label class="squaredFour" for="<?php echo $invoice['id']; ?>" /></td>
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
