<?php
$parts = explode('/', dirname(dirname(__FILE__)));
$parts = array_slice($parts, 0, -1);
define('ROOT', implode('/', $parts).'/');

session_start();
require_once ROOT.'config/config.php';
require_once ROOT.'lib/functions.php';

$globalDB = new Database;
global $globalDB;

spl_autoload_register(function ($className) {
	if (file_exists(ROOT.'lib/classes/'.$className.'.php')) {
		require_once(ROOT.'lib/classes/'.$className.'.php');
		
	} else if (file_exists(ROOT.'application/models/'.$className.'.php')) {
		require_once(ROOT.'application/models/'.$className.'.php');
	}
});

if (isset($_POST['newGroup'])) {
	
	$fairGroup = new FairGroup();
	$fairGroup->set('name', $_POST['groupname']);
	$fairGroup->set('invoice_no', $_POST['invoice_no']);
	$fairGroup->set('owner', $_SESSION['user_id']);
	$fairGroupId = $fairGroup->save();

	if (isset($_POST['selected_fairs']) && isset($_POST['share_invoice']) && !empty($_POST['selected_fairs']) && !empty($_POST['share_invoice'])) {
		foreach (array_combine($_POST['selected_fairs'], $_POST['share_invoice']) as $fairId => $share_invoice) {
			$fairGroupRel = new FairGroupRel();
			$fairGroupRel->set('group', $fairGroupId);
			$fairGroupRel->set('fair', $fairId);
			$fairGroupRel->set('share_invoice', $share_invoice);
			$fairGroupRel->save();
		}
	}
}
if (isset($_POST['editGroup'])) {
	
	$fairGroup = new FairGroup();
	$fairGroup->load($_POST['editGroup'], 'id');
	if ($fairGroup->wasLoaded()) {
		$fairGroup->set('name', $_POST['groupname']);
		$fairGroup->set('invoice_no', $_POST['invoice_no']);
		if (userLevel() < 4) {
			$fairGroup->set('owner', $_SESSION['user_id']);
		} else {
			$fairGroup->set('owner', $_POST['user_id']);
		}
		$fairGroup->save();
		// DELETE THE RELATED FAIRS BEFORE SAVING NEW ONES
		$stmt = $fairGroup->db->prepare("DELETE FROM fair_group_rel WHERE `group` = ?");
		$stmt->execute(array($fairGroup->get('id')));

		if (isset($_POST['selected_fairs']) && isset($_POST['share_invoice']) && !empty($_POST['selected_fairs']) && !empty($_POST['share_invoice'])) {
			foreach (array_combine($_POST['selected_fairs'], $_POST['share_invoice']) as $fairId => $share_invoice) {
				$fairGroupRel = new FairGroupRel();
				$fairGroupRel->set('group', $fairGroup->get('id'));
				$fairGroupRel->set('fair', $fairId);
				$fairGroupRel->set('share_invoice', $share_invoice);
				$fairGroupRel->save();
			}
		}
	}
}
?>