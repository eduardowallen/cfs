<?php


class FairGroupController extends Controller {

	public function groups($param='') {
		setAuthLevel(3);
		if (userLevel() < 4) { 
			$owner = new Arranger();
			$owner->loadid($_SESSION['user_id'], 'id');
		}
		$this->set('headline', 'My event groups');
		$this->set('th_group', 'Group');
		$this->set('th_events', 'Events');
		$this->set('th_invoice', 'Invoice no.');
		$this->set('th_edit', 'Edit');
		$this->set('th_delete', 'Delete');
		$this->set('create_link', 'Create group');

		$my_groups = array();
		if ($owner->wasLoaded()) {
			$stmt = $owner->db->prepare('SELECT `id` FROM fair_group WHERE `owner` = ?');
			$stmt->execute(array($owner->get('id')));
			$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if (count($groups) > 0) {
				foreach ($groups as $groupId) {
					$group = new FairGroup();
					$group->load($groupId['id'], 'id');
					$my_groups[] = $group;
				}
			}

			$this->setNoTranslate('groups', $my_groups);

		}
	}

	public function create($param='') {
		setAuthLevel(3);
		if (userLevel() < 4) {
			$owner = new Arranger();
			$owner->loadsimple($_SESSION['user_id'], 'id');
			$this->setNoTranslate('owner', $owner);
			if ($owner->wasLoaded()) {
				$this->setNoTranslate('owner', $owner);
				$this->setNoTranslate('hasRights', true);
				// Get group IDs to find out which ones belong to the owner
				$stmt = $this->db->prepare("SELECT `id` FROM fair_group WHERE `owner` = ?");
				$stmt->execute(array($owner->get('id')));
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				if (count($result) > 0) {
					$groupIds = array();
					foreach ($result as $group) {
						$groupIds[] = $group['id'];
					}
					// Get all fair IDs that are part of any group owned by the owner
					$stmt = $this->db->prepare("SELECT `fair` FROM fair_group_rel WHERE `group` IN(".implode(',', $groupIds).")");
					$stmt->execute();
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
					if (count($result) > 0) {
						$fairs_in_group = array();
						foreach ($result as $fig) {
							$fairs_in_group[] = $fig['fair'];
						}
						// Get all fairs that are not part of any group owned by the owner
						$stmt = $this->db->prepare("SELECT `id` FROM fair WHERE `created_by` = ? AND `id` NOT IN(".implode(',', $fairs_in_group).")");
						$stmt->execute(array($owner->get('id')));
						$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
						if (count($result) > 0) {
							$fairs = array();
							foreach ($result as $fairId) {
								$fair = new Fair();
								$fair->loadsimple($fairId['id'], 'id');
								$fairs[] = $fair;
							}
							$this->setNoTranslate('fairs_available', $fairs);
						} else {
							$this->setNoTranslate('fairs_available', false);
						}
					} else {
						$this->setNoTranslate('fairs_available', $owner->get('fairs'));
					}
				} else {
					$this->setNoTranslate('fairs_available', $owner->get('fairs'));
				}
			} else {
				$this->setNoTranslate('hasRights', false);
			}
		}
		$this->set('headline', 'New event group');
		$this->set('group_headline', 'Events in group');
		$this->set('events_headline', 'Events available');
		$this->set('events_in_group', 'Events in this group');
		$this->set('already_grouped', 'Events is already in a group');
		$this->set('name_label', 'Group name');
		$this->set('invoice_no_label', 'Group invoice number');
		$this->set('invoice_label', 'Invoice number for this group');
		$this->set('invoice_explanation_text', 'Invoice number must be higher or equal to the highest invoice number for the events in this group.');
		$this->set('invoice_explanation_title', 'Invalid invoice number');
		$this->set('th_event_name', 'Event name');
		$this->set('th_invoice_no', 'Invoice no.');
		$this->set('th_share_invoice_no', 'Share invoice no.');
		$this->set('save_label', 'Save');
		$this->set('cancel_label', 'Cancel');
	}

	public function edit($id) {
		setAuthLevel(3);
		$group = new FairGroup();
		$group->load($id, 'id');
		if ($group->wasLoaded()) {
			if (userLevel() < 4) { 
				if ($group->get('owner') == $_SESSION['user_id']) {
					$owner = new Arranger();
					$owner->loadsimple($group->get('owner'), 'id');
					if ($owner->wasLoaded()) {
						$this->setNoTranslate('owner', $owner);
						$this->setNoTranslate('hasRights', true);
						$this->setNoTranslate('group', $group);
						// Get group IDs to find out which ones belong to the owner
						$stmt = $this->db->prepare("SELECT `id` FROM fair_group WHERE `owner` = ?");
						$stmt->execute(array($owner->get('id')));
						$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
						if (count($result) > 0) {
							$groupIds = array();
							foreach ($result as $group) {
								$groupIds[] = $group['id'];
							}
						}
						// Get all fair IDs that are part of any group owned by the owner
						$stmt = $this->db->prepare("SELECT `fair` FROM fair_group_rel WHERE `group` IN(".implode(',', $groupIds).")");
						$stmt->execute();
						$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
						if (count($result) > 0) {
							$fairs_in_group = array();
							foreach ($result as $fig) {
								$fairs_in_group[] = $fig['fair'];
							}
							$stmt = $this->db->prepare("SELECT `id` FROM fair WHERE `created_by` = ? AND `id` NOT IN(".implode(',', $fairs_in_group).")");
							$stmt->execute(array($owner->get('id')));
							$fairs_available = $stmt->fetchAll(PDO::FETCH_ASSOC);
							$fairs = array();
							foreach ($fairs_available as $fairId) {
								$fair = new Fair();
								$fair->loadsimple($fairId['id'], 'id');
								$fairs[] = $fair;
							}
							$this->setNoTranslate('fairs_available', $fairs);
						} else {
							$this->setNoTranslate('fairs_available', false);
						}
					} else {
						$this->setNoTranslate('hasRights', false);
					}
				} else {
					$this->setNoTranslate('hasRights', false);
				}
				//header('Location: '.BASE_URL.'fairGroup/groups');
			} else if (userLevel() == 4) {
				$owner = new Arranger();
				$owner->load($group->get('owner'), 'id');
				if ($owner->wasLoaded()) {
					$this->setNoTranslate('owner', $owner);
				}
				$this->setNoTranslate('group', $group);
				$this->setNoTranslate('hasRights', true);
			}
		} else {
			$this->setNoTranslate('group_notfound', true);
		}

		$this->set('headline', 'Edit event group');
		$this->set('group_headline', 'Events in group');
		$this->set('events_headline', 'Events available');
		$this->set('events_in_group', 'Events in this group');
		$this->set('already_grouped', 'Events is already in a group');
		$this->set('name_label', 'Group name');
		$this->set('invoice_no_label', 'Group invoice number');
		$this->set('invoice_label', 'Invoice number for this group');
		$this->set('invoice_explanation_text', 'Invoice number must be higher or equal to the highest invoice number for the events in this group.');
		$this->set('invoice_explanation_title', 'Invalid invoice number');
		$this->set('th_event_name', 'Event name');
		$this->set('th_invoice_no', 'Invoice no.');
		$this->set('th_share_invoice_no', 'Share invoice no.');
		$this->set('save_label', 'Save changes');
		$this->set('cancel_label', 'Cancel');
	}

	public function delete($id) {
		setAuthLevel(3);
		$group = new FairGroup();
		$group->load($id, 'id');
		if (userLevel() < 4) { 
			if ($group->get('owner') == $_SESSION['user_id']) {
				$group->delete();
			}
			header('Location: '.BASE_URL.'fairGroup/groups');
		} else if (userLevel() == 4) {
			$group->delete();
			header('Location: '.BASE_URL.'fairGroup/groups');
		}
	}
}



?>