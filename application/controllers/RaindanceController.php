<?php
class RaindanceController extends Controller {
	public function index() {
		
	}
	public function extractions($id) {
		setAuthLevel(2);

		if (userLevel() == 3) {
			$fair = new Fair();
			$fair->loadsimple($id, 'id');
			if ($fair->wasLoaded() && $fair->get('created_by') != $_SESSION['user_id']) {
				toLogin();
			}
		}

		if (userLevel() == 2) {
			$stmt = $this->db->prepare('SELECT * FROM fair_user_relation WHERE user=? AND fair=?');
			$stmt->execute(array($_SESSION['user_id'], $id));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (!$result) {
				$this->setNoTranslate('hasRights', false);
				return;
			}
		}
		$rd_export = new RaindanceExport();
		$rd_export->load($id, 'id');

		if ($rd_export->wasLoaded()) {
			$stmt = $this->db->prepare('SELECT invoice FROM raindance_export_invoices WHERE rdid = ?');
			$stmt->execute(array($id));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

			foreach ($result as $invoiceid) {
				$stmt = $this->db->prepare("SELECT * FROM `exhibitor_invoice` WHERE id = ? AND fair = ? ORDER BY id ASC");
				$stmt->execute(array($invoiceid['invoice'], $rd_export->get('fair')));
				$invoices[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}

			$this->setNoTranslate('invoices', $invoices);
		}
	}
	/*
	public function RDsettings($id) {

		setAuthLevel(2);

		if (userLevel() == 3) {
			$fair = new Fair();
			$fair->loadsimple($id, 'id');
			if ($fair->wasLoaded() && $fair->get('created_by') != $_SESSION['user_id']) {
				toLogin();
			}
		}

		if (userLevel() == 2) {
			$stmt = $this->db->prepare('SELECT * FROM fair_user_relation WHERE user=? AND fair=?');
			$stmt->execute(array($_SESSION['user_id'], $id));
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (!$result) {
				$this->setNoTranslate('hasRights', false);
				return;
			}
		}

		$this->Raindance->load($id, 'fair');

		
			$this->set('rd', $this->Raindance);
			$this->set('fairId', $id);
			$this->set('headline', 'Raindance settings');
			$this->set('part_one_label', 'Code part 1');
			$this->set('part_two_label', 'Code part 2');
			$this->set('part_three_label', 'Code part 3');
			$this->set('part_four_label', 'Code part 4');
			$this->set('part_five_label', 'Code part 5');
			$this->set('part_six_label', 'Code part 6');
			$this->set('part_seven_label', 'Code part 7');
			$this->set('part_eight_label', 'Code part 8');
			$this->set('accrualkey_label', 'Accrual Key');
			$this->set('save_label', 'Save');

		$this->setNoTranslate('hasRights', true);

		if (isset($_POST['save'])) {
			if ($this->Raindance->wasLoaded()) {

			} else {
				$this->Raindance->set('fair', $id);
			}

			$this->Raindance->set('part_one', $_POST['part_one']);
			$this->Raindance->set('part_two', $_POST['part_two']);
			$this->Raindance->set('part_three', $_POST['part_three']);
			$this->Raindance->set('part_four', $_POST['part_four']);
			$this->Raindance->set('part_five', $_POST['part_five']);
			$this->Raindance->set('part_six', $_POST['part_six']);
			$this->Raindance->set('part_seven', $_POST['part_seven']);
			$this->Raindance->set('part_eight', $_POST['part_eight']);
			$this->Raindance->set('accrualkey', $_POST['accrualkey']);
			$this->Raindance->save();
		}
	}
*/

}
?>