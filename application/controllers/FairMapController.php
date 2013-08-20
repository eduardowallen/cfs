<?php

class FairMapController extends Controller {


	public function index() {

		$this->set('headline', 'Maps');

	}

	public function create($fair) {

		setAuthLevel(3);

		if (userLevel() == 3) {
			$f = new Fair;
			$f->load($fair, 'id');
			if (!$f->wasLoaded() || $f->get('created_by') != $_SESSION['user_id'])
				toLogin();

		}

		if (isset($_POST['create'])) {
			$this->FairMap->set('fair', $fair);
			$this->FairMap->set('name', $_POST['name']);
			$mId = $this->FairMap->save();

			if (is_uploaded_file($_FILES['image']['tmp_name'])) {
				
				$im = new ImageMagick;
				
				$ext = end(explode('.', $_FILES['image']['name']));
				if (strtolower($ext) == 'pdf') {
					$now = time();
					move_uploaded_file($_FILES['image']['tmp_name'], ROOT.'public/images/tmp/'.$now.'.pdf');
					chmod(ROOT.'public/images/tmp/'.$now.'.pdf', 0775);
					$im->pdf2img(ROOT.'public/images/tmp/'.$now.'.pdf', ROOT.'public/images/fairs/'.$fair.'/maps/'.$mId.'_large.jpg');
					
					$im->constrain(ROOT.'public/images/fairs/'.$fair.'/maps/'.$mId.'_large.jpg', ROOT.'public/images/fairs/'.$fair.'/maps/'.$mId.'_large.jpg', 100000, 100000);
					unlink(ROOT.'public/images/tmp/'.$now.'.pdf');
					
				} else {
					$im->constrain($_FILES['image']['tmp_name'], ROOT.'public/images/fairs/'.$fair.'/maps/'.$mId.'_large.jpg', 100000, 100000);
				}
				
				$im->constrain(ROOT.'public/images/fairs/'.$fair.'/maps/'.$mId.'_large.jpg', ROOT.'public/images/fairs/'.$fair.'/maps/'.$mId.'.jpg', 920, 1500);
				chmod(ROOT.'public/images/fairs/'.$fair.'/maps/'.$mId.'_large.jpg', 0775);
				chmod(ROOT.'public/images/fairs/'.$fair.'/maps/'.$mId.'.jpg', 0775);
			}

			header("Location: ".BASE_URL."fair/maps/".$fair);
			exit;
		}

		$this->set('headline', 'Create map');
		$this->set('fair', $fair);
		$this->set('name_label', 'Name');
		$this->set('save_label', 'Save');
		$this->set('image_label', 'Image');

	}
	
	public function edit($map_id, $fair_id) {
		setAuthLevel(3);
		
		//echo 'whoami:<p>'.system('whoami', $res).'</p>'.$res;

		if (userLevel() == 3) {
			$f = new Fair;
			$f->load($fair_id, 'id');
			
			if (!$f->wasLoaded() || $f->get('created_by') != $_SESSION['user_id']) {
				toLogin();
			}

		}
		
		if (isset($_POST['save'])) {
			$this->FairMap->load($map_id, 'id');
			$this->FairMap->set('name', $_POST['name']);
			$this->FairMap->save();
			$mId = $map_id;
			
			if (is_uploaded_file($_FILES['image']['tmp_name'])) {
				
				$im = new ImageMagick;
				
				$ext = end(explode('.', $_FILES['image']['name']));
				if (strtolower($ext) == 'pdf') {
					$now = time();
					move_uploaded_file($_FILES['image']['tmp_name'], ROOT.'public/images/tmp/'.$now.'.pdf');
					chmod(ROOT.'public/images/tmp/'.$now.'.pdf', 0775);
					$im->pdf2img(ROOT.'public/images/tmp/'.$now.'.pdf', ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'_large.jpg');
					
					$im->constrain(ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'_large.jpg', ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'_large.jpg', 100000, 100000);
					unlink(ROOT.'public/images/tmp/'.$now.'.pdf');
					
				} else {
					$im->constrain($_FILES['image']['tmp_name'], ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'_large.jpg', 100000, 100000);
				}
				
				$im->constrain(ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'_large.jpg', ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'.jpg', 920, 1500);
				chmod(ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'_large.jpg', 0775);
				chmod(ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'.jpg', 0775);
				
			}

			header("Location: ".BASE_URL."fair/maps/".$fair_id);
			exit;
		}
		
		$this->FairMap->load($map_id, 'id');
		
		$this->set('headline', 'Edit map');
		$this->set('map_id', $map_id);
		$this->set('mo', $this->FairMap);
		$this->set('fair_id', $fair_id);
		$this->set('name_label', 'Name');
		$this->set('save_label', 'Save');
		$this->set('image_label', 'Image');
	}

	public function delete($fair, $id) {

		setAuthLevel(3);

		$map = new FairMap($id);
		$map->load($id, 'id');
		$map->delete();

		header("Location: ".BASE_URL."fair/maps/".$fair);
		exit;

	}


}

?>