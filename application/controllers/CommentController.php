<?php
class CommentController extends Controller {

	private $me;
	private $me_owner;

	public function __construct($model, $controller, $action) {
		parent::__construct($model, $controller, $action);

		setAuthLevel(2);

		$this->me = new User();
		$this->me->load($_SESSION['user_id'], 'id');
		$this->me_owner = ($this->me->get('level') == 2 ? $this->me->get('owner') : $this->me->get('id'));

		if ($this->me->get('level') == 4) {
			$this->me_owner = 0;
		}
	}

	private function setLabels() {
		$this->set('label_fair', 'Fair');
		$this->set('label_position', 'Position');
		$this->set('label_generic', 'Generic');
		$this->set('label_comment_type', 'Type');
		$this->set('label_comment_negative', 'Negative');
		$this->set('label_comment_neutral', 'Neutral');
		$this->set('label_comment_positive', 'Positive');
		$this->set('label_delete', 'Delete');
		$this->set('label_edit', 'Edit');
	}

	public function index($filter_fair = 0) {
		$this->setNoTranslate('filter_fair', $filter_fair);
		$this->setNoTranslate('fairs', getMyFairs());

		$comments = Comment::fetchAll(($filter_fair == 0 ? 4 : 2), $this->me->get('id'), $this->me_owner, 0, $filter_fair);
		$this->setNoTranslate('comments', $comments);

		// Labels
		$this->set('label_headline', 'Notes');
		$this->set('label_select_fair', 'Show notes for the following fairs');
		$this->set('label_all_fairs', 'All fairs');
		$this->set('label_add_comment', 'Create new note');
		$this->set('label_export_excel', 'Export to Excel');
		$this->set('label_exhibitor', 'Exhibitor');
		$this->set('label_author', 'Author');
		$this->set('label_note', 'Note');
		$this->set('label_type', 'Type');
		$this->set('label_note_time', 'Time for note');
		$this->set('label_edit', 'Edit');
		$this->set('label_delete', 'Delete');
		$this->set('label_comment_negative', 'Negative');
		$this->set('label_comment_neutral', 'Neutral');
		$this->set('label_comment_positive', 'Positive');
	}

	public function excel($filter_fair = 0) {
		$this->setNoTranslate('noView', true);

		if (isset($_POST['rows'], $_POST['field']) && is_array($_POST['rows']) && is_array($_POST['field'])) {
			$comments = Comment::fetchAll(($filter_fair == 0 ? 4 : 2), $this->me->get('id'), $this->me_owner, 0, $filter_fair);

			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); 
			header('Content-Type: application/force-download');
			header('Content-Type: application/octet-stream');
			header('Content-Type: application/download');
			header('Content-Disposition: attachment;filename=FairNotes.xlsx');
			header('Content-Transfer-Encoding: binary');

			require_once ROOT . 'lib/PHPExcel-1.7.8/Classes/PHPExcel.php';

			$xls = new PHPExcel();
			$xls->setActiveSheetIndex(0);

			$alpha = range('A', 'Z');

			$column_names = array(
				'author_name' => $this->translate->{'Author'},
				'exhibitor_name' => $this->translate->{'Exhibitor'},
				'comment' => $this->translate->{'Note'},
				'date' => $this->translate->{'Time for note'},
				'author' => $this->translate->{'Author'},
				'fair_name' => $this->translate->{'Fair'},
				'position_name' => $this->translate->{'Stand space'},
				'type' => $this->translate->{'Type of comment'}
			);

			$label_type = array(
				-1 => $this->translate->{'Negative'},
				0 => $this->translate->{'Neutral'},
				1 => $this->translate->{'Positive'}
			);

			$i = 0;
			foreach ($_POST['field'] as $fieldname => $humbug) {
				$xls->getActiveSheet()->SetCellValue($alpha[$i] . '1', $column_names[$fieldname]);
				++$i;
			}

			// Row 1 in the sheet is now done, continue with data on row 2
			$row_idx = 2;

			// Start outputing the actual booking data into the spreadsheet
			foreach ($comments as $row) {
				$i = 0;

				foreach ($_POST['field'] as $fieldname => $humbug) {
					if ($fieldname == 'type') {
						$value = $label_type[$row->type];
					} else {
						$value = $row->$fieldname;
					}

					$xls->getActiveSheet()->SetCellValue($alpha[$i] . $row_idx, $value);
					++$i;
				}

				// Next row in spreadsheet
				++$row_idx;
			}

			$xls->getActiveSheet()->getStyle('A1:AZ1')->applyFromArray(array(
				'font' => array('bold' => true)
			));
			
			$objWriter = new PHPExcel_Writer_Excel2007($xls);
			$objWriter->save('php://output');
		}
	}

	public function dialog($user_id = 0, $fair_id = 0, $position_id = 0) {
		try {
			$position_name = '';
			$this->setNoTranslate('label_headline_name', '');

			if ($user_id > 0) {
				$user = new User();
				$user->load($user_id, 'id');

				if (!$user->wasLoaded()) {
					throw new Exception('Exhibitor not found.', 1);
				}

				$mode = 1; // Generic
				$this->setNoTranslate('user', $user);
			}

			if ($fair_id > 0) {
				$fair = new Fair();
				$fair->load($fair_id, 'id');

				if (!$fair->wasLoaded()) {
					throw new Exception('Fair not found.', 2);
				}

				$mode = 2; // Fair
				$map = 0;
				$this->setNoTranslate('label_headline_name', $fair->get('name'));
				$this->setNoTranslate('fair', $fair);

				if ($position_id > 0) {
					$position = new FairMapPosition();
					$position->load($position_id, 'id');

					if (!$position->wasLoaded()) {
						throw new Exception('Position not found.', 3);
					}

					$mode = 3; // Position
					$map = $position->get('map');
					$this->setNoTranslate('label_headline_name', $position->get('name'));
					$this->setNoTranslate('position', $position);
					$position_name = $position->get('name');
				}
				if ($position_id == -1) {

					$mode = 3; // Position
				}
				// Check if current user has access to fair
				if (!userCanAdminFair($fair->get('id'), $map)) {
					throw new Exception('You have no access to this fair or map.', 4);
				}
			}

			// Labels
			$this->set('label_headline', 'Notes for %s');
			$this->set('label_current_exhibitor', 'Current exhibitor');
			$this->set('label_no_comments', 'There are no comments made yet for this exhibitor');
			$this->set('label_comment_add_headline', 'New comment');
			$this->set('label_comment_type_of', 'Type of comment');
			$this->set('label_comment_valid_for', 'This comment will be valid for');
			$this->set('label_all_exhibitor_fairs', 'All exhibitor\'s fairs');
			$this->set('label_exhibitor', 'Select Exhibitor');
			$this->set('label_comment_pos_only', 'This position only');
			$this->set('label_comment_fair_only', 'This fair only');
			$this->set('label_comment_all_fairs', 'All fairs');
			$this->set('label_comment_add', 'Add comment');
			$this->setLabels();

			if ($this->is_ajax) {
				$this->setNoTranslate('onlyContent', true);
			}

			if (isset($_POST['save'])) {
				if (!isset($_POST['comment']) || $_POST['comment'] == '') {
					throw new Exception('You must provide a comment text.', 5);
				}

				switch ($_POST['validfor']) {
					case 0:
						$this->Comment->set('fair', $fair_id);
						$this->Comment->set('position', $position_id);
						$this->Comment->set('position_name', $position_name);
						break;

					case 1:
						$this->Comment->set('fair', $fair_id);
						$this->Comment->set('position', 0);
						$this->Comment->set('position_name', '');
						break;

					case 2:
						$this->Comment->set('fair', 0);
						$this->Comment->set('position', 0);
						$this->Comment->set('position_name', '');
						break;

					case 3:
						$this->Comment->set('fair', $_POST['fair']);
						$this->Comment->set('position', 0);
						$this->Comment->set('position_name', '');
						break;
				}

				if (isset($user)) {
					$this->Comment->set('exhibitor', $user_id);
					$this->Comment->exhibitor_name = $user->get('company');
				} else if (isset($_POST['exhibitor']) && $_POST['exhibitor'] > 0) {
					$this->Comment->set('exhibitor', $_POST['exhibitor']);

					$user = new User();
					$user->load($_POST['exhibitor'], 'id');
					$this->Comment->exhibitor_name = $user->get('company');
				} else {
					throw new Exception('You must provide an exhibitor to comment.', 7);
				}

				$this->Comment->set('date', date('Y-m-d H:i:s'));
				$this->Comment->set('author', $this->me->get('name'));
				$this->Comment->set('author_id', $this->me->get('id'));
				$this->Comment->set('author_owner', $this->me_owner);
				$this->Comment->set('comment', $_POST['comment']);
				$this->Comment->set('type', $_POST['type']);
				$id = $this->Comment->save();

				if ($this->Comment->get('id') > 0) {
					if ($this->is_ajax) {
						$this->_template->setAction((isset($_POST['template']) ? $_POST['template'] : 'comment_item'));
						$this->setNoTranslate('comment', $this->Comment);
						return;
					}

				} else {
					throw new Exception('Error when saving to database.', 6);
				}
			}

			if (isset($mode)) {
				// Retrieve all comments
				$comments = Comment::fetchAll($mode, $this->me->get('id'), $this->me_owner, $user_id, $fair_id, $position_id);
				$this->setNoTranslate('comments', $comments);
			}

			// If no exhibitor was given, make a list of possible exhibitors
			if (!isset($user)) {
				if (userLevel() == 4) {
					$this->setNoTranslate('exhibitors', Exhibitor::fetchAll());
				} else if (isset($fair)) {
					$this->setNoTranslate('exhibitors', Exhibitor::getExhibitorsForFair($fair->get('id')));
				} else {
					$this->setNoTranslate('exhibitors', Exhibitor::getExhibitorsForMyFairs());
				}
			}

			// If no fair was given, make a list of possible fairs
			if (!isset($fair)) {
				$this->setNoTranslate('fairs', getMyFairs());
			}

			$this->setNoTranslate('params', $user_id . '/' . $fair_id . '/' . $position_id);

		} catch (Exception $ex) {
			if ($this->is_ajax) {
				$this->createJsonResponse();
			}

			$this->set('error', $ex->getMessage());
			$this->setNoTranslate('code', $ex->getCode());
		}
	}

	private function getComment($id) {
		$this->Comment->load($id, 'id');
		if ($this->Comment->wasLoaded()) {
			$this->setNoTranslate('comment', $this->Comment);

			// Check if current user has access to fair
			if (!userCanAdminFair($this->Comment->get('fair'), 0) && $this->me_owner != $this->Comment->get('author_owner')) {
				throw new Exception('You have no access to this fair.', 8);
			}

			$user = new User();
			$user->load($this->Comment->get('exhibitor'), 'id');

			if ($user->wasLoaded()) {
				$this->setNoTranslate('user', $user);
			}

		} else {
			throw new Exception('Comment not found', 404);
		}
	}

	public function view($id = 0) {
		if ($id > 0) {
			$this->getComment($id);

			$this->setLabels();
			$this->_template->setAction('comment_item');
		}
	}

	public function edit($id = 0) {
		if ($id > 0) {
			try {
				$this->getComment($id);

				$this->setLabels();

				if (isset($_POST['save'])) {
					if (!isset($_POST['comment']) || $_POST['comment'] == '') {
						throw new Exception('You must provide a comment text.', 5);
					}

					$this->Comment->set('comment', $_POST['comment']);
					$this->Comment->set('type', $_POST['type']);
					$this->Comment->save();

					if ($this->is_ajax) {
						$this->createJsonResponse();
						$this->setNoTranslate('saved', true);

						switch ($this->Comment->get('type')) {
							case -1:
								$this->Comment->type_html = '<span class="comment-negative">' . uh($this->translate->{'Negative'}) . '</span>';
								break;

							case 0:
								$this->Comment->type_html = '<span class="comment-neutral">' . uh($this->translate->{'Neutral'}) . '</span>';
								break;

							case 1:
								$this->Comment->type_html = '<span class="comment-positive">' . uh($this->translate->{'Positive'}) . '</span>';
								break;
						}

						$this->setNoTranslate('model', $this->Comment);
						return;
					}
				}

				// Labels
				$this->set('label_headline', 'Edit note');
				$this->set('label_current_exhibitor', 'Current exhibitor');
				$this->set('label_comment_type', 'Type of comment');
				$this->set('label_comment_valid_for', 'This note will be valid for');
				$this->set('label_comment_pos_only', 'This position only');
				$this->set('label_comment_fair_only', 'This fair only');
				$this->set('label_comment_all_fairs', 'All fairs');
				$this->set('label_comment_save', 'Save note');

				$this->setNoTranslate('onlyContent', true);
			} catch (Exception $ex) {
				if ($this->is_ajax) {
					$this->createJsonResponse();
					$this->set('error', $ex->getMessage());
					$this->setNoTranslate('code', $ex->getCode());
				} else {
					throw $ex;
				}
			}
		}
	}

	public function delete($id = 0) {
		if ($id > 0) {
			try {
				$this->getComment($id);

				if (isset($_POST['save'])) {
					$this->Comment->delete();

					if ($this->is_ajax) {
						$this->createJsonResponse();
						$this->setNoTranslate('deleted', true);
						return;
					}
				}

				// Labels
				$this->set('label_headline', 'Delete note');
				$this->set('label_delete_question', 'Do you want to delete the following note?');
				$this->set('label_yes', 'Yes');
				$this->set('label_no', 'No');

				$this->setNoTranslate('onlyContent', true);
			} catch (Exception $ex) {
				if ($this->is_ajax) {
					$this->createJsonResponse();
					$this->set('error', $ex->getMessage());
					$this->setNoTranslate('code', $ex->getCode());
				} else {
					throw $ex;
				}
			}
		}
	}
}
?>