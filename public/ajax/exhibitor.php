<?php
/*$parts = explode('/', dirname(dirname(__FILE__)));
$parts = array_slice($parts, 0, -1);
define('ROOT', implode('/', $parts).'/');

session_start();
require_once ROOT.'config/config.php';
require_once ROOT.'lib/functions.php';
require_once ROOT.'lib/classes/Translator.php';

function __autoload($className) {
	if (file_exists(ROOT.'lib/classes/'.$className.'.php')) {
		require_once(ROOT.'lib/classes/'.$className.'.php');
		
	} else if (file_exists(ROOT.'application/models/'.$className.'.php')) {
		require_once(ROOT.'application/models/'.$className.'.php');
	}
}

$globalDB = new Database;
global $globalDB;

$lang = (isset($_COOKIE['language'])) ? $_COOKIE['language'] : 'eng';
define('LANGUAGE', $lang);
$translator = new Translator($lang);

if (isset($_GET["getProfile"])) {
	$u = new User();
	$u->load($_GET["getProfile"], "id");

	//Masters get the full list of positions, lower levels get the ones for their fair
	if (userLevel() == 4) {
		$stmt = $u->db->prepare("SELECT * FROM exhibitor WHERE user = ?");
		$stmt->execute(array($u->get('id')));
	} else {
		$stmt = $u->db->prepare("SELECT * FROM exhibitor WHERE user = ? AND fair = ?");
		$stmt->execute(array($u->get('id'), $_SESSION['user_fair']));
	}
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

	echo '
		<img src="images/icons/close_dialogue.png" class="closeDialogue" />
		<div class="form_column">
			<h3>' . $translator->{"Company"} . '</h3>
    
      <label for="orgnr">' . $translator->{"Organization number"} . ' *</label>
			<div type="text" name="orgnr" id="orgnr">' . $u->get('orgnr') . '</div>
    
      <label for="company">' . $translator->{"Company"} . ' *</label>
			<div type="text" name="company" id="company">' . $u->get('company') . '</div>
    
      <label for="commodity">' . $translator->{"Commodity"} . '</label>
			<div rows="3" style="width:250px;" name="commodity" id="commodity">' . $u->get('commodity') . '</div>
    
      <label for="address">' . $translator->{"Address"} . ' *</label>
			<div type="text" name="address" id="address"  >' . $u->get('address') . '</div>
    
      <label for="zipcode">' . $translator->{"Zip code"} . ' *</label>
			<div type="text" name="zipcode" id="zipcode"  >' . $u->get('zipcode') . '</div>
    
      <label for="city">' . $translator->{"City"} . ' *</label>
			<div type="text" name="city" id="city">' . $u->get('city') . '</div>
    
      <label for="country">' . $translator->{"Country"} . ' *</label>
      <div name="country" id="country" style="width:258px;">'
      	. $u->get('country') . '&nbsp;
      </div>

      <label for="phone1">' . $translator->{"Phone 1"} . ' *</label>
			<div type="text" name="phone1" id="phone1">' . $u->get('phone1') . '</div>
    
      <label for="phone2">' . $translator->{"Phone 2"} . '</label>
			<div type="text" name="phone2" id="phone2">' . $u->get('phone2') . '</div>
    
      <label for="email">' . $translator->{"E-mail"} . ' *</label>
			<div type="text" name="email" id="email">' . $u->get('email') . '</div>
    
      <label for="website">' . $translator->{"Website"} . '</label>
			<div type="text" name="website" id="website">' . $u->get('website') . '</div>
    </div>

      
    <div class="form_column">
      <h3>' . $translator->{"Billing address"} . '</h3>
      <label for="invoice_company">' . $translator->{"Company"} . ' *</label>
			<div type="text" name="invoice_company" id="invoice_company">' . $u->get('invoice_company') . '</div>

      <label for="invoice_address">' . $translator->{"Address"} . ' *</label>
			<div type="text" name="invoice_address" id="invoice_address">' . $u->get('invoice_address') . '</div>

      <label for="invoice_zipcode">' . $translator->{"Zip code"} . ' *</label>
			<div type="text" name="invoice_zipcode" id="invoice_zipcode">' . $u->get('invoice_zipcode') . '</div>

      <label for="invoice_city">' . $translator->{"City"} . ' *</label>
			<div type="text" name="invoice_city" id="invoice_city">' . $u->get('invoice_city') . '</div>

      <label for="invoice_country">' . $translator->{"Country"} . ' *</label>
      <div name="invoice_country" id="invoice_country" style="width:258px;">'
        . $u->get('invoice_country') . '&nbsp;
      </div>

      <label for="invoice_email">' . $translator->{"Email"} . ' *</label>
			<div type="text" name="invoice_email" id="invoice_email">' . $u->get('invoice_email') . '</div>
    </div>
      

    <div class="form_column">
      <h3>' . $translator->{"Contact"} . '</h3>

      <label for="name">' . $translator->{"Contact person"} . ' *</label>
			<div type="text" name="name" id="name">' . $u->get('name') . '</div>

      <label for="phone3">' . $translator->{"Contact Phone"} . ' *</label>
			<div type="text" name="phone3" id="phone3">' . $u->get('contact_phone') . '</div>

      <label for="phone4">' . $translator->{"Contact Phone 2"} . '</label>
			<div type="text" name="phone4" id="phone4">' . $u->get('contact_phone2') . '</div>

      <label for="contact_email">' . $translator->{"Contact Email"} . ' *</label>
			<div type="text" name="contact_email" id="contact_email">' . $u->get('contact_email') . '</div>

    </div>';

		echo '</form>
			<h3>' . $translator->{"Bookings"} . '</h3>';

			$hasHead = false;
			//Need to loop to prevent memory allocation error
			for ($i = 0; $i < 2; $i++) {
				$positions = array();

				if ($i === 1) {
					foreach($u->getPreliminaries() as $prel) {
						$pos = new FairMapPosition;
						$pos->load($prel['position'], 'id');
						$pos->set('exhibitor_id', $prel['id']);
						$pos->set('preliminary', true);
						$pos->set('company', $u->get('company'));
						$pos->set('booking_time', $prel['booking_time']);
						$pos->set('commodity', $prel['commodity']);
						$pos->set('arranger_message', $prel['arranger_message']);
				    $fairmap = new FairMap;
				    $fairmap->load($pos->get('map'), 'id');
				    $pos->set('map', $fairmap);
						$positions[] = $pos;
					}
				} else {
					foreach ($result as $res) {
						$pos = new FairMapPosition;
						$pos->load($res['position'], 'id');
						$pos->set('exhibitor_id', $res['id']);
						$pos->set('preliminary', false);
						$pos->set('commodity', $res['commodity']);
						$pos->set('arranger_message', $res['arranger_message']);
						$pos->set('company', $u->get('company'));
						$pos->set('booking_time', $res['booking_time']);
				    $fairmap = new FairMap;
				    $fairmap->load($pos->get('map'), 'id');
				    $pos->set('map', $fairmap);
						$positions[] = $pos;
					}
				}
				if (count($positions) > 0) {
				 if (!$hasHead) {
				 		$hasHead = true;
						echo '<div class="scrolltbl onlyfive">
						  <table class="std_table">
						  <thead>
						  	<tr>
						  		<th>' . $translator->{"Fair"} . '</th>
						  		<th>' . $translator->{"Stand space"} . '</th>
						  		<th>' . $translator->{"Area"} . '(m<sup>2</sup>)</th>
						  		<th>' . $translator->{"Booked by"} . '</th>
						  		<th>' . $translator->{"Trade"} . '</th>
						  		<th>' . $translator->{"Time of booking"} . '</th>
						  		<th>' . $translator->{"Message to organizer"} . '</th>
						  	</tr>
						  </thead>
						  <tbody>';
					}
				  foreach($positions as $pos) {
				  	$bookingTime = ($pos->get('booking_time') != "") ? date('d-m-Y H:i:s', $pos->get('booking_time')) : "";
					  echo '<tr>
				  		<td><a target="_blank" href="/mapTool/map/' . $pos->map->get('fair') . '/' . $pos->get('id') . '/' . $pos->map->get('id') . '">' . $pos->map->get('name') . '</a></td>
				  		<td>' . $pos->get('name') . '</td>
				  		<td class="center">' . $pos->get('area') . '</td>
				  		<td class="center">' . $pos->get('company') . '</td>
				  		<td class="center">' . $pos->get('commodity') . '</td>
				  		<td>' . $bookingTime . '</td>
				  		<td class="center" title="' . htmlspecialchars($pos->get('arranger_message')) . '">';
				  
					  if (strlen($pos->get('arranger_message')) > 0):
					  	$url = $pos->get("preliminary") ? "preliminary" : "exhibitor";
							echo '<a href="administrator/arrangerMessage/' . $url . '/' . $pos->get("exhibitor_id") . '" class="open-arranger-message">
								<img src="' . BASE_URL . 'images/icons/script.png" alt="' . $translator->{"Message to organizer"} . '" />
							</a>';
					  endif;
					  echo '
				  		</td>
				  	</tr>';
				  }
				}
			}

			if (!$hasHead) {
				echo '<p>' . $translator->{"This exhibitor has not made any bookings yet."} . '</p>';
			} else {
				echo '
				  </tbody>
				  </table>
				</div>';
			}
			unset($result);
			unset($positions);

//echo $html;

	if (isset($_POST['ban_save'])) {
		
		$stmt = $u->db->prepare("DELETE FROM user_ban WHERE user = ? AND organizer = ?");
		$stmt->execute(array($u->get('id'), $_SESSION['user_id']));
		
		$ban = new UserBan;
		$ban->set('user', $u->get('id'));
		$ban->set('organizer', $_SESSION['user_id']);
		$ban->set('reason', $_POST['ban_msg']);
		$ban->save();
		
	}
	*/
  
	/*$this->setNoTranslate('user', $u);
	$this->setNoTranslate('positions', $positions);
	$this->set('headline', 'Exhibitor profile');

	$this->set('company_section', 'Company');
  $this->set('orgnr_label', 'Organization number');
  $this->set('company_label', 'Company');
  $this->set('commodity_label', 'Commodity');
  $this->set('address_label', 'Address');
  $this->set('zipcode_label', 'Zip code');
  $this->set('city_label', 'City');
  $this->set('country_label', 'Country');
  $this->set('phone1_label', 'Phone 1');
  $this->set('phone2_label', 'Phone 2');
  $this->set('email_label', 'E-mail');
  $this->set('website_label', 'Website');
  
	$this->set('invoice_section', 'Billing address');
  $this->set('copy_label', 'Copy from company details');
  $this->set('invoice_company_label', 'Company');
  $this->set('invoice_address_label', 'Address');
  $this->set('invoice_zipcode_label', 'Zip code');
  $this->set('invoice_city_label', 'City');
  $this->set('invoice_email_label', 'E-mail');
  $this->set('presentation_label', 'Presentation');
  
	$this->set('contact_section', 'Contact person');
  $this->set('alias_label', 'Alias');
  $this->set('contact_label', 'Contact person');
  $this->set('phone3_label', 'Contact Phone');
  $this->set('phone4_label', 'Contact Phone 2');
  $this->set('contact_email', 'Contact Email');
  $this->set('contact_country', 'Contact Country');

  $this->set('password_label', 'Password');
  $this->set('password_repeat_label', 'Password again (repeat to confirm)');
  //$this->set('save_label', 'Save');
	//$this->set('save_label', 'Save');

	//$this->set('ban_section_header', 'Ban user');
	//$this->set('ban_msg_label', 'Reason for ban');
	//$this->set('ban_save', 'Save');

	$this->set('bookings_section', 'Bookings');
	$this->set('tr_event', 'Fair');
	$this->set('tr_pos', 'Stand space');
	$this->set('tr_area', 'Area');
	$this->set('tr_booker', 'Booked by');
	$this->set('tr_field', 'Trade');
	$this->set('tr_time', 'Time of booking');
	$this->set('tr_message', 'Message to organizer');
	$this->set('ok_label', 'OK');
	$this->set('no_bookings_label', 'This exhibitor has not made any bookings yet.');
	*/
}