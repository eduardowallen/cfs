<?php

class FairMapController extends Controller {


	public function index() {

		$this->set('headline', 'Maps');

	}

	public function create($fairId) {

		setAuthLevel(3);

		$f = new Fair;
		$f->load($fairId, 'id');
		if (userLevel() == 3) {
			if (!$f->wasLoaded() || $f->get('created_by') != $_SESSION['user_id'])
				toLogin();
		}

		if (isset($_POST['create'])) {
			$this->FairMap->set('fair', $fairId);
			$this->FairMap->set('name', $_POST['name']);

			if (is_uploaded_file($_FILES['image']['tmp_name'])) {
				$this->FairMap->set("file_name", $_FILES["image"]["name"]);
				$mId = $this->FairMap->save();

				$im = new ImageMagick;
				
				$ext = end(explode('.', $_FILES['image']['name']));
				if (strtolower($ext) == 'pdf') {
					$now = time();
					move_uploaded_file($_FILES['image']['tmp_name'], ROOT.'public/images/tmp/'.$now.'.pdf');
					chmod(ROOT.'public/images/tmp/'.$now.'.pdf', 0775);
					$im->pdf2img(ROOT.'public/images/tmp/'.$now.'.pdf', ROOT.'public/images/fairs/'.$fairId.'/maps/'.$mId.'_large.jpg');
					
					$im->constrain(ROOT.'public/images/fairs/'.$fairId.'/maps/'.$mId.'_large.jpg', ROOT.'public/images/fairs/'.$fairId.'/maps/'.$mId.'_large.jpg', 100000, 100000);
					unlink(ROOT.'public/images/tmp/'.$now.'.pdf');
					
				} else {
					$im->constrain($_FILES['image']['tmp_name'], ROOT.'public/images/fairs/'.$fairId.'/maps/'.$mId.'_large.jpg', 100000, 100000);
				}
				
				$im->constrain(ROOT.'public/images/fairs/'.$fairId.'/maps/'.$mId.'_large.jpg', ROOT.'public/images/fairs/'.$fairId.'/maps/'.$mId.'.jpg', 920, 1500);
				chmod(ROOT.'public/images/fairs/'.$fairId.'/maps/'.$mId.'_large.jpg', 0775);
				chmod(ROOT.'public/images/fairs/'.$fairId.'/maps/'.$mId.'.jpg', 0775);
			}

			header("Location: ".BASE_URL."fair/maps/".$fairId);
			exit;
		}

		$this->set('headline', 'Create map for');
		$this->setNoTranslate('fairId', $fairId);
		$this->setNoTranslate('fair', $f);
		$this->set('name_label', 'Name');
		$this->set('save_label', 'Save');
		$this->set('image_label', 'Image');
		$this->set('f_hide_label', 'Hide fair for unauthorized accounts');
		$this->set('f_show', 'Show');
		$this->set('f_hide', 'Hide');

	}
	
	public function edit($map_id, $fair_id) {
		setAuthLevel(3);
		
		//echo 'whoami:<p>'.system('whoami', $res).'</p>'.$res;
		$f = new Fair;
		$f->load($fair_id, 'id');

		if (userLevel() == 3) {
	
			if (!$f->wasLoaded() || $f->get('created_by') != $_SESSION['user_id']) {
				toLogin();
			}

		}
		
		if (isset($_POST['save'])) {
			$this->FairMap->load($map_id, 'id');
			$this->FairMap->set('name', $_POST['name']);
			$mId = $map_id;
			
			if (is_uploaded_file($_FILES['image']['tmp_name'])) {
				$this->FairMap->set("file_name", $_FILES["image"]["name"]);

				$im = new ImageMagick;
				$name_parts = explode('.', $_FILES['image']['name']);
				$ext = end($name_parts);
				$now = time();
				if (strtolower($ext) == 'pdf') {
					move_uploaded_file($_FILES['image']['tmp_name'], ROOT.'public/images/tmp/'.$now.'.pdf');
					chmod(ROOT.'public/images/tmp/'.$now.'.pdf', 0775);
					$im->pdf2img(ROOT.'public/images/tmp/'.$now.'.pdf', ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'_large.jpg');
					
					$im->constrain(ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'_large.jpg', ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'_large.jpg', 100000, 100000);
					unlink(ROOT.'public/images/tmp/'.$now.'.pdf');
					
				} else {
					move_uploaded_file($_FILES['image']['tmp_name'], ROOT.'public/images/tmp/'.$now.'.jpg');
					chmod(ROOT.'public/images/tmp/'.$now.'.jpg', 0775);
					$im->constrain(ROOT.'public/images/tmp/'.$now.'.jpg', ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'_large.jpg', 100000, 100000);
					chmod(ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'_large.jpg', 0775);
					unlink(ROOT.'public/images/tmp/'.$now.'.jpg');
				}
				
				$im->constrain(ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'_large.jpg', ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'.jpg', 920, 1500);
				chmod(ROOT.'public/images/fairs/'.$fair_id.'/maps/'.$mId.'.jpg', 0775);
				
			}

			$this->FairMap->save();

			header("Location: ".BASE_URL."fair/maps/".$fair_id);
			exit;
		}
		
		$this->FairMap->load($map_id, 'id');
		
		$this->set('headline', 'Edit map for');
		$this->setNoTranslate('map_id', $map_id);
		$this->setNoTranslate('fair', $f);
		$this->setNoTranslate('mo', $this->FairMap);
		$this->setNoTranslate('fair_id', $fair_id);
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
