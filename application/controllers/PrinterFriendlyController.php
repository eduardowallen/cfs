<?php

class PrinterFriendlyController extends Controller {
	
	public function exhibitor($id) {
		
		$ex = new Exhibitor;
		
		$ex->load($id, 'id');
		if ($ex->wasLoaded()) {
			
			$pos = new FairMapPosition;
			$pos->load($ex->get('position'), 'id');
			
			$this->setNoTranslate('headline', $ex->get('company'));
			
			$this->set('space', 'Space');
			$this->set('status', 'Status');
			$this->set('area', 'Area');
			$this->set('company', 'Company');
			$this->set('commodity', 'Commodity');
			$this->set('website', 'Website');
			$this->set('presentation', 'Presentation');
			$this->setNoTranslate('exhibitor', $ex);
			$this->setNoTranslate('position', $pos);
			
		} else {
			exit;
		}
		
	}
	
}

?>