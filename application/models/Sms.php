<?php
class Sms extends Model {

	protected $recipients = array();
	protected $fair = null;
	protected $author = null;

	private $stmt_insert_rec = null;

	public function load($key, $by) {
		if (parent::load($key, $by)) {
			$stmt_rec = $this->db->prepare("SELECT rec.*, user.name, user.company FROM sms_recipient AS rec LEFT JOIN user ON user.id = rec_user_id WHERE sms_id = ?");
			$stmt_rec->execute(array($this->id));
			$this->recipients = $stmt_rec->fetchAll(PDO::FETCH_OBJ);

			$this->fair = new Fair();
			$this->fair->load($this->fair_id, 'id');

			$this->author = new User();
			$this->author->load($this->author_user_id, 'id');
		}
	}

	public function save() {
		$this->id = parent::save();

		return $this->id;
	}

	public function addRecipient($user_id, $phone, $sent_status) {
		if (!$this->id) {
			throw new Exception('Tried to add recipients before SMS was stored in database.', 20);
		}

		// Create a prepared statement if not already made
		// It's good to not create SAME prepared statements over and over and over again...
		if ($this->stmt_insert_rec === null) {
			// Field `delivery_status` is reserved for future use with SMS API "sms_receipt".
			$this->stmt_insert_rec = $this->db->prepare("INSERT INTO sms_recipient (sms_id, rec_user_id, phone, sent_status, delivery_status) VALUES (?, ?, ?, ?, 0)");
		}

		return $this->stmt_insert_rec->execute(array($this->id, $user_id, $phone, $sent_status));
	}
}
?>