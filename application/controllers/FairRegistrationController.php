<?php
class FairRegistrationController extends Controller {

	public function __construct($model, $controller, $action) {
		parent::__construct($model, $controller, $action);

		setAuthLevel(1);
	}

	public function form($id) {

		/// KONTROLLERAD MAILMALL
		
		$fair = new Fair();
		$fair->load($id, 'id');

		$user = new User();
		$user->load($_SESSION['user_id'], 'id');

		$organizer = new User();
		$organizer->load($fair->get('created_by'), 'id');

		if ($fair->get('allow_registrations') == 0) {
			header('Location: /');
			return;
		}

		if ($fair->wasLoaded() && $user->wasLoaded()) {
			if (isset($_POST['save'])) {

				$category_ids = '';
				$categories = array();
				if (isset($_POST['categories']) && is_array($_POST['categories'])) {
					foreach ($_POST['categories'] as $cat) {
						$category = new ExhibitorCategory();
						$category->load($cat, 'id');
						if ($category->wasLoaded()) {
							$categories[] = $category->get('name');
						}
					}
					$category_ids = implode('|', $_POST['categories']);
				}

				$option_ids = '';
				$options = array();
				if (isset($_POST['options']) && is_array($_POST['options'])) {
					foreach ($_POST['options'] as $opt) {
						$ex_option = new FairExtraOption();
						$ex_option->load($opt, 'id');
						if ($ex_option->wasLoaded()) {
							$option_id[] = $ex_option->get('custom_id');
							$option_text[] = $ex_option->get('text');
							$option_price[] = $ex_option->get('price');
							$option_vat[] = $ex_option->get('vat');
						}
					}
					$options = array($option_id, $option_text, $option_price, $option_vat);
					$option_ids = implode('|', $_POST['options']);
				}

				$article_ids = '';
				$article_amounts = '';
				$articles = array();
				if (!empty($_POST['articles']) && !empty($_POST['artamount'])) {
						$arts = $_POST['articles'];
						$amounts = $_POST['artamount'];

						foreach (array_combine($arts, $amounts) as $art => $amount) {
							$arts = new FairArticle();
							$arts->load($art, 'id');
							if ($arts->wasLoaded()) {
								$art_id[] = $arts->get('custom_id');
								$art_text[] = $arts->get('text');
								$art_amount[] = $amount;
								$art_price[] = $arts->get('price');
								$art_vat[] = $arts->get('vat');
							}								
						}
						$articles = array($art_id, $art_text, $art_price, $art_amount, $art_vat);
						$article_ids = implode('|', $_POST['articles']);
						$article_amounts = implode('|', $_POST['artamount']);				
				}			

				$this->FairRegistration->set('user', $user->get('id'));
				$this->FairRegistration->set('fair', $fair->get('id'));
				$this->FairRegistration->set('categories', $category_ids);
				$this->FairRegistration->set('options', $option_ids);
				$this->FairRegistration->set('articles', $article_ids);
				$this->FairRegistration->set('amount', $article_amounts);
				$this->FairRegistration->set('commodity', $_POST['commodity']);
				$this->FairRegistration->set('arranger_message', $_POST['arranger_message']);
				$this->FairRegistration->set('area', $_POST['area']);
				$this->FairRegistration->set('booking_time', time());
				$this->FairRegistration->save();

				// Connect user to fair
				if (!userIsConnectedTo($fair->get('id'))) {
					$stmt = $this->db->prepare("INSERT INTO fair_user_relation (`fair`, `user`, `connected_time`) VALUES (?, ?, ?)");
					$stmt->execute(array($fair->get('id'), $user->get('id'), time()));
				}

				$htmlcategoryNames = implode('<br>', $categories);

				/*****************************************************************************************/
				/*****************************************************************************************/
				/************************				PREPARE MAIL START			  *************************/
				/*****************************************************************************************/
				/*****************************************************************************************/

				/*********************************************************************************/
				/*********************************************************************************/
				/********************************       LABELS       *****************************/
				/*********************************************************************************/
				/*********************************************************************************/
				$name_label = $this->translate->{'Name'};
				$price_label = $this->translate->{'Price'};
				$amount_label = $this->translate->{'Amount'};
				$vat_label = $this->translate->{'Vat'};
				$sum_label = $this->translate->{'Sum'};
				$booked_space_label = $this->translate->{'Stand'};
				$options_label = $this->translate->{'Options'};
				$articles_label = $this->translate->{'Articles'};
				$tax_label = $this->translate->{'Tax'};
				$parttotal_label = $this->translate->{'Subtotal'};
				$net_label = $this->translate->{'Net'};
				$rounding_label = $this->translate->{'Rounding'};
				$to_pay_label = $this->translate->{'to pay:'};
				$estimated_label = $this->translate->{'Estimated'};
				$st_label = $this->translate->{'st'};
				$nothing_selected_label = $this->translate->{'No articles or options selected.'};
				$not_including_position_price = $this->translate->{'not including position price'};


				/*************************************************************/
				/*************************************************************/
				/*****************     PRICES AND AMOUNTS        *************/
				/*************************************************************/
				/*************************************************************/ 

				$totalPrice = 0;
				$totalNetPrice = 0;
				$VatPrice0 = 0;
				$VatPrice12 = 0;
				$VatPrice18 = 0;
				$VatPrice25 = 0;
				$excludeVatPrice0 = 0;
				$excludeVatPrice12 = 0;
				$excludeVatPrice18 = 0;
				$excludeVatPrice25 = 0;
				$currency = $fair->get('currency');

				/*********************************************************************************************/
				/*********************************************************************************************/
				/**********************					MAIL BOOKING TABLE START			  ***********************/
				/*********************************************************************************************/
				/*********************************************************************************************/
		$html = '<!-- SIX COLUMN HEADERS -->
					<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;border-top-color:rgb(234, 234, 234);border-top-width:1px;border-top-style:solid;padding:10px 0 0 0;">
					 <!-- ID -->
					 <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
					     ID
					   </p>
					 </td>
					 <!-- NAME -->
					 <td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
					     '.$name_label.'
					   </p>
					 </td>
					 <!-- PRICE -->
					 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
					     '.$price_label.'
					   </p>
					 </td>
					 <!-- AMOUNT -->
					 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
					     '.$amount_label.'
					   </p>
					 </td>
					 <!-- VAT % -->
					 <td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal align=center style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
					     '.$vat_label.'
					   </p>
					 </td>
					 <!-- SUM -->
					 <td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
					   <p class=MsoNormal align=right style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
					     '.$sum_label.'
					   </p>
					 </td>
					</tr>
					<!-- SPACER ROW -->
					<tr style="mso-yfti-irow:1;height:11.1pt">
					</tr>';
		$html_sum = '<!-- TWO COLUMN VAT PRICE AND NET SUMMATION -->
						<tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:13.3pt;">
							<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
								<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								</p>
							</td>
							<td width="50%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
								<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
								</p>
							</td>
						</tr>';
		if (!empty($_POST['options']) && is_array($_POST['options'])) {
			$html .= '<!-- SIX COLUMNS -->
		               <tr style="mso-yfti-irow:1;height:25.1pt">
		                	<!-- ID -->
		                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
		                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
		                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
		                  		</span>
		                  	</p>
		                  </td>
		                  <!-- NAME -->
								<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										'.$options_label.'
										</span>
									</p>
								</td>
								<!-- PRICE -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- AMOUNT -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- VAT % -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- SUM -->
								<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
							</tr>';

			for ($row=0; $row<count($options[1]); $row++) {
				$html .= '<!-- SIX COLUMNS -->
			               <tr style="mso-yfti-irow:1;height:25.1pt">
			                	<!-- ID -->
			                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
			                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
			                  		'.$options[0][$row].'
			                  		</span>
			                  	</p>
			                  </td>
			                  <!-- NAME -->
									<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$options[1][$row].'
											</span>
										</p>
									</td>
									<!-- PRICE -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$options[2][$row].'
											</span>
										</p>
									</td>
									<!-- AMOUNT -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											1'.$st_label.'
											</span>
										</p>
									</td>
									<!-- VAT % -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$options[3][$row].'%
											</span>
										</p>
									</td>
									<!-- SUM -->
									<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.str_replace('.', ',', number_format($options[2][$row], 2, ',', ' ')).'
											</span>
										</p>
									</td>
								</tr>';
			}
		}

		if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
			$html .= '<!-- SIX COLUMNS -->
		               <tr style="mso-yfti-irow:1;height:25.1pt">
		                	<!-- ID -->
		                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
		                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
		                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
		                  		</span>
		                  	</p>
		                  </td>
		                  <!-- NAME -->
								<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										'.$articles_label.'
										</span>
									</p>
								</td>
								<!-- PRICE -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- AMOUNT -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- VAT % -->
								<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
								<!-- SUM -->
								<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
										<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
										</span>
									</p>
								</td>
							</tr>';

			for ($row=0; $row<count($articles[1]); $row++) {
				$html .= '<!-- SIX COLUMNS -->
			               <tr style="mso-yfti-irow:1;height:25.1pt">
			                	<!-- ID -->
			                  <td width=60 valign=top style="width:45pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
			                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
			                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
			                  		'.$articles[0][$row].'
			                  		</span>
			                  	</p>
			                  </td>
			                  <!-- NAME -->
									<td width=140 valign=top style="width:105pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$articles[1][$row].'
											</span>
										</p>
									</td>
									<!-- PRICE -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=right style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.str_replace('.', ',', $articles[2][$row]).'
											</span>
										</p>
									</td>
									<!-- AMOUNT -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$articles[3][$row].' '.$st_label.'
											</span>
										</p>
									</td>
									<!-- VAT % -->
									<td width=50 valign=top style="width:37.5pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal align=center style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:center;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.$articles[4][$row].'%
											</span>
										</p>
									</td>
									<!-- SUM -->
									<td width=80 valign=top style="width:60pt;padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
										<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;text-align:right;line-height:normal">
											<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
											'.str_replace('.', ',', number_format(($articles[2][$row] * $articles[3][$row]), 2, ',', ' ')).'
											</span>
										</p>
									</td>
								</tr>';
		    }
		}


		if (!empty($_POST['options']) && is_array($_POST['options'])) {
			for ($row=0; $row<count($options[1]); $row++) {

				if ($options[3][$row] == 25) {
					$excludeVatPrice25 += $options[2][$row];
				}
				if ($options[3][$row] == 18) {
					$excludeVatPrice18 += $options[2][$row];
				}
				if ($options[3][$row] == 12) {
					$excludeVatPrice12 += $options[2][$row];
				}
				if ($options[3][$row] == 0) {
					$excludeVatPrice0 += $options[2][$row];
				}
			}
		}

		if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
			for ($row=0; $row<count($articles[1]); $row++) {

				if ($articles[4][$row] == 25) {
					$excludeVatPrice25 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
				}
				if ($articles[4][$row] == 18) {
					$excludeVatPrice18 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
				}
				if ($articles[4][$row] == 12) {
					$excludeVatPrice12 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
				}
				if ($articles[4][$row] == 0) {
					$excludeVatPrice0 += (($articles[3][$row]>=0?$articles[3][$row]:0) * $articles[2][$row]);
				}
			}
		}

		$VatPrice0 = $excludeVatPrice0;
		$VatPrice12 = $excludeVatPrice12*0.12;
		$VatPrice18 = $excludeVatPrice18*0.18;
		$VatPrice25 = $excludeVatPrice25*0.25;
		$totalPrice += $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25 + $VatPrice12 + $VatPrice18 + $VatPrice25 + $VatPrice0;
		$totalNetPrice += $excludeVatPrice0 + $excludeVatPrice12 + $excludeVatPrice18 + $excludeVatPrice25;

		$totalPriceRounded = round($totalPrice);
		$pennys = ($totalPriceRounded - $totalPrice);

		if (!empty($excludeVatPrice12) && !empty($VatPrice12)) {
			$excludeVatPrice12 = number_format($excludeVatPrice12, 2, ',', ' ');
			$VatPrice12 = number_format($VatPrice12, 2, ',', ' ');

			$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$tax_label.' (12%)
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.str_replace('.', ',', $VatPrice12).'
									</p>
								</td>
							</tr>';

		}
		if (!empty($excludeVatPrice18) && !empty($VatPrice18)) {
			$excludeVatPrice18 = number_format($excludeVatPrice18, 2, ',', ' ');
			$VatPrice18 = number_format($VatPrice18, 2, ',', ' ');
			$html_sum  .='<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$tax_label.' (18%)
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.str_replace('.', ',', $VatPrice18).'
									</p>
								</td>
							</tr>';
		}
		if (!empty($excludeVatPrice25) && !empty($VatPrice25)) {
			$excludeVatPrice25 = number_format($excludeVatPrice25, 2, ',', ' ');
			$VatPrice25 = number_format($VatPrice25, 2, ',', ' ');
			$html_sum  .=   '<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$tax_label.' (25%)
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.str_replace('.', ',', $VatPrice25).'
									</p>
								</td>
							</tr>';
		}
		if (empty($excludeVatPrice25) && empty($VatPrice25) && empty($excludeVatPrice18) && empty($VatPrice18) && empty($excludeVatPrice12) && empty($VatPrice12)) {
			$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$tax_label.'
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										0,00
									</p>
								</td>
							</tr>
							<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$net_label.'
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.str_replace('.', ',', $totalNetPrice).'
									</p>
								</td>
							</tr>
							<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									</p>
								</td>
								<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
									</p>
								</td>
							</tr>
							<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="30%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									</p>
								</td>
								<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										<strong>'.$estimated_label.' '.$currency.' '.$to_pay_label.'&nbsp;&nbsp;</strong>0,00<br>('.$not_including_position_price.')
									</p>
								</td>
							</tr>';
		} else {
			$html_sum .='<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="50%" align=left valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;font-weight:600;">
									<p class=MsoNormal align=left style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.$net_label.'
									</p>
								</td>
								<td width="50%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										'.str_replace('.', ',', number_format($totalNetPrice, 2, ',', ' ')).'
									</p>
								</td>
							</tr>
							<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									</p>
								</td>
								<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										<strong>'.$rounding_label.':&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($pennys, 2, ',', ' ')).'
									</p>
								</td>
							</tr>
							<tr style="mso-yfti-irow:0;height:13.3pt">
								<td width="30%" valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal align=left style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
									</p>
								</td>
								<td width="70%" align=right valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:13.3pt;">
									<p class=MsoNormal style="font-family:Helvetica, Arial, sans-serif;margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
										<strong>'.$estimated_label.' '.$currency.' '.$to_pay_label.'&nbsp;&nbsp;</strong>'.str_replace('.', ',', number_format($totalPriceRounded, 2, ',', ' ')).'<br>('.$not_including_position_price.')
									</p>
								</td>
							</tr>';
		}

		if ($totalPriceRounded == 0 && empty($_POST['articles']) && empty($_POST['options'])) {
			$html = '<!-- ONE COLUMN -->
		               <tr style="mso-yfti-irow:1;height:25.1pt">
		                  <td width=100% valign=top style="padding:0cm 5.4pt 0cm 5.4pt;height:25.1pt" align=center>
		                  	<p class=MsoNormal style="margin-bottom:0cm;margin-bottom:.0001pt;line-height:normal">
		                  		<span style="font-family:Helvetica, Arial, sans-serif;color:#333333">
		                  		'.$nothing_selected_label.'
		                  		</span>
		                  	</p>
		                  </td>
							</tr>';
			$html_sum = '';
		}

		/*********************************************************************************************/
		/*********************************************************************************************/
		/**********************					MAIL BOOKING TABLE END				  ***********************/
		/*********************************************************************************************/
		/*********************************************************************************************/

				$arranger_message = $_POST['arranger_message'];
				if ($arranger_message == '')
					$arranger_message = $this->translate->{'No message was given.'};
				
				$exhibitor_commodity = $_POST['commodity'];
				if ($exhibitor_commodity == '')
					$exhibitor_commodity = $this->translate->{'No commodity was entered.'};

					$time_now = date('Y-m-d H:i');

					//Check mail settings and send only if setting is set
					$errors = array();
					$mail_errors = array();
					$email = $fair->get("url") . EMAIL_FROM_DOMAIN;
					$from = array($email => $fair->get("windowtitle"));
					$mailSettings = json_decode($fair->get("mail_settings"));

					if($fair->get('contact_name')) {
						$from = array($email => $fair->get('contact_name'));
					}

					try {
						if ($user->get('contact_email') == '')
							$recipients = array($user->get('email') => $user->get('company'));
						else
							$recipients = array($user->get('contact_email') => $user->get('name'));

						$mail_user = new Mail();
						$mail_user->setTemplate('registration_created_receipt');
						$mail_user->setPlainTemplate('registration_created_receipt');
						$mail_user->setFrom($from);
						$mail_user->addReplyTo($fair->get('name'), $fair->get('contact_email'));
						$mail_user->setRecipients($recipients);
							$mail_user->setMailVar('booking_table', $html);
							$mail_user->setMailVar('booking_sum', $html_sum);
							$mail_user->setMailVar('exhibitor_company_name', $user->get('company'));
							$mail_user->setMailvar('exhibitor_name', $user->get('name'));
							$mail_user->setMailVar('event_name', $fair->get('windowtitle'));
							$mail_user->setMailVar('event_contact', $fair->get('contact_name'));
							$mail_user->setMailVar('event_email', $fair->get('contact_email'));
							$mail_user->setMailVar('event_phone', $fair->get('contact_phone'));
							$mail_user->setMailVar('event_website', $fair->get('website'));
							$mail_user->setMailVar('event_url', BASE_URL . $fair->get('url'));
							$mail_user->setMailVar('arranger_message', $arranger_message);
							$mail_user->setMailVar('commodity', $exhibitor_commodity);
							$mail_user->setMailVar('html_categories', $htmlcategoryNames);
							$mail_user->setMailVar('booking_time', $time_now);
							$mail_user->setMailVar('area', $_POST['area']);
						
						if(!$mail_user->send()) {
							$errors[] = $user->get('company');
						}

					} catch(Swift_RfcComplianceException $ex) {
						// Felaktig epost-adress
						$errors[] = $user->get('company');
						$mail_errors[] = $ex->getMessage();

					} catch(Exception $ex) {
						// Okänt fel
						$errors[] = $user->get('company');
						$mail_errors[] = $ex->getMessage();
					}

					if (is_array($mailSettings->recieveRegistration)) {

						if (in_array("0", $mailSettings->recieveRegistration)) {
							try {
								if ($organizer->get('contact_email') == '')
									$recipients = array($organizer->get('email') => $organizer->get('company'));
								else
									$recipients = array($organizer->get('contact_email') => $organizer->get('name'));

								$mail_organizer = new Mail();
								$mail_organizer->setTemplate('registration_created_confirm');
								$mail_organizer->setPlainTemplate('registration_created_confirm');
								$mail_organizer->setFrom($from);
								$mail_organizer->addReplyTo($fair->get('windowtitle'), $fair->get('contact_email'));
								$mail_organizer->setRecipients($recipients);
									$mail_organizer->setMailVar('booking_table', $html);
									$mail_organizer->setMailVar('booking_sum', $html_sum);
									$mail_organizer->setMailVar('exhibitor_company_name', $user->get('company'));
									$mail_organizer->setMailvar('exhibitor_name', $user->get('name'));
									$mail_organizer->setMailVar('event_name', $fair->get('windowtitle'));
									$mail_organizer->setMailVar('event_url', BASE_URL . $fair->get('url'));
									$mail_organizer->setMailVar('arranger_message', $arranger_message);
									$mail_organizer->setMailVar('commodity', $exhibitor_commodity);
									$mail_organizer->setMailVar('html_categories', $htmlcategoryNames);
									$mail_organizer->setMailVar('booking_time', $time_now);
									$mail_organizer->setMailVar('area', $_POST['area']);
								if(!$mail_organizer->send()) {
									$errors[] = $organizer->get('company');
								}

							} catch(Swift_RfcComplianceException $ex) {
								// Felaktig epost-adress
								$errors[] = $organizer->get('company');
								$mail_errors[] = $ex->getMessage();

							} catch(Exception $ex) {
								// Okänt fel
								$errors[] = $organizer->get('company');
								$mail_errors[] = $ex->getMessage();
							}
						}
					}
					if ($errors) {
						$_SESSION['mail_errors'] = $mail_errors;
					}
				header('Location: /fairRegistration/success');
				return;
			}
		}

		$this->setNoTranslate('fair', $fair);
		$this->setNoTranslate('me', $user);

		// Labels
		$this->set('label_headline', 'Register for fair %s');
		$this->set('label_category', 'Category');
		$this->set('label_options', 'Extra options');
		$this->set('label_articles', 'Articles');
		$this->set('label_commodity', 'Commodity');
		$this->set('label_message_organizer', 'Message to organizer');
		$this->set('label_area', 'Requested area');
		$this->set('label_confirm', 'Confirm');
	}

	public function success() {
		$this->set('label_thanks', 'Thank you for your registration!');
		$this->set('label_ok', 'OK');
	}
}
?>