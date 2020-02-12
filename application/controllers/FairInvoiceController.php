<?php
class FairInvoiceController extends Controller {

	function delete_invoice($row_id) {
		setAuthLevel(4);
        $invoice_to_delete = new FairInvoice();
        $invoice_to_delete->load($row_id, 'row_id');
        if ($invoice_to_delete->wasLoaded())
            error_log('Invoice was loaded in FairInvoiceController.php on line 8.');
            $invoice_to_delete->perm_delete();
    }
    
}
?>