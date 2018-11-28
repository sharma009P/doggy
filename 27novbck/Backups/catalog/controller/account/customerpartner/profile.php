<?php
class ControllerAccountCustomerpartnerProfile extends Controller {

	private $error = array();

	public function index() {

		$data = array();
		$data = array_merge($data, $this->load->language('account/customerpartner/profile'));

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/profile', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/customerpartner');

		$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

		if(!$data['chkIsPartner'] || (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode']))
			$this->response->redirect($this->url->link('account/account', '', true));

		$this->document->setTitle($data['heading_title']);

		$this->document->addScript('catalog/view/javascript/wk_summernote/summernote.js');
		$this->document->addStyle('catalog/view/javascript/wk_summernote/summernote.css');
		$this->document->addStyle('catalog/view/theme/default/stylesheet/MP/sell.css');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->load->model('account/customerpartner');
			$this->model_account_customerpartner->updateProfile($this->request->post);
			$this->session->data['success'] = $data['text_success'];
			$this->response->redirect($this->url->link('account/customerpartner/profile', '', true));
		}

		$partner = $this->model_account_customerpartner->getProfile();
		if($partner) {

			$this->load->model('tool/image');

			if (isset($this->request->post['avatar'])) {

				if ($this->request->post['avatar']) {
					$partner['avatar_img'] = $this->request->post['avatar'];
			        $partner['avatar'] = $this->model_tool_image->resize($this->request->post['avatar'], 100, 100);
				}else{
                    $partner['avatar_img'] = '';
			        $partner['avatar'] = '';
				}
			}elseif ($partner['avatar'] && file_exists(DIR_IMAGE . $partner['avatar'])) {
			    $partner['avatar_img'] = $partner['avatar'];
				$partner['avatar'] = $this->model_tool_image->resize($partner['avatar'], 100, 100);
			} else if($this->config->get('marketplace_default_image_name') && file_exists(DIR_IMAGE . $this->config->get('marketplace_default_image_name'))) {
				$partner['avatar'] = $this->model_tool_image->resize($this->config->get('marketplace_default_image_name'), 100, 100);
				$partner['avatar_img'] = '';
			} else {
				$partner['avatar_img'] = '';
				$partner['avatar'] = '';
			}

			if (isset($this->request->post['companybanner'])) {
				if ($this->request->post['companybanner']) {
					$partner['companybanner_img'] = $this->request->post['companybanner'];
			        $partner['companybanner'] = $this->model_tool_image->resize($this->request->post['companybanner'], 100, 100);
				}else{
                    $partner['companybanner_img'] = '';
			        $partner['companybanner'] = '';
				}
			}elseif ($partner['companybanner'] && file_exists(DIR_IMAGE . $partner['companybanner'])) {
			    $partner['companybanner_img'] = $partner['companybanner'];
				$partner['companybanner'] = $this->model_tool_image->resize($partner['companybanner'], 100, 100);
			} else if($this->config->get('marketplace_default_image_name') && file_exists(DIR_IMAGE . $this->config->get('marketplace_default_image_name'))) {
                $partner['companybanner'] = $this->model_tool_image->resize($this->config->get('marketplace_default_image_name'), 100, 100);
                $partner['companybanner_img'] = '';
			} else {
				$partner['companybanner_img'] = '';
				$partner['companybanner'] = '';
			}

			if (isset($this->request->post['companylogo'])) {
				if ($this->request->post['companylogo']) {
					$partner['companylogo_img'] = $this->request->post['companylogo'];
			        $partner['companylogo'] = $this->model_tool_image->resize($this->request->post['companylogo'], 100, 100);
				}else{
                    $partner['companylogo_img'] = '';
			        $partner['companylogo'] = '';
				}
			}elseif ($partner['companylogo'] && file_exists(DIR_IMAGE . $partner['companylogo'])){
				$partner['companylogo_img'] = $partner['companylogo'];
				$partner['companylogo'] = $this->model_tool_image->resize($partner['companylogo'], 100, 100);
			} else if($this->config->get('marketplace_default_image_name') && file_exists(DIR_IMAGE . $this->config->get('marketplace_default_image_name'))) {
				$partner['companylogo'] = $this->model_tool_image->resize($this->config->get('marketplace_default_image_name'), 100, 100);
				$partner['companylogo_img'] = '';
			} else {
				$partner['companylogo_img'] = '';
				$partner['companylogo'] = '';
			}

			$partner['countrylogo'] = $partner['countrylogo'];
			$data['storeurl'] =$this->url->link('customerpartner/profile&id='.$this->customer->getId(),'',true);
		}

		if($this->config->get('module_wk_seller_group_status')) {
			$this->load->model('account/customer_group');
			$isMember = $this->model_account_customer_group->getSellerMembershipGroup($this->customer->getId());
			if($isMember) {
				$allowedAccountMenu = $this->model_account_customer_group->getprofileOption($isMember['gid']);
				if($allowedAccountMenu['value']) {
					$accountMenu = explode(',',$allowedAccountMenu['value']);
					if($accountMenu) {
						foreach ($accountMenu as $key => $value) {
							$values = explode(':',$value);
							$data['allowed'][$values[0]] = $values[1];
						}
					}
				}
			}
		} else if($this->config->get('marketplace_allowedprofilecolumn')) {
			$data['allowed']  = $this->config->get('marketplace_allowedprofilecolumn');
		}

		$data['partner'] = $partner;

		if (!$data['partner']['country']) {
			$data['partner']['country'] = 'af';

			$address_id = $this->customer->getAddressId();

			if ($address_id) {
			  $this->load->model('account/address');

			  $address_data = $this->model_account_address->getAddress($address_id);

			  if (isset($address_data['iso_code_2']) && $address_data['iso_code_2']) {
			    $data['partner']['country'] = $address_data['iso_code_2'];
			  }
			}
		}

		$data['countries'] = $this->model_account_customerpartner->getCountry();

      	$data['breadcrumbs'] = array();

      	$data['breadcrumbs'][] = array(
        	'text'      => $data['text_home'],
			'href'      => $this->url->link('common/home', '', true),
        	'separator' => false
      	);

      	$data['breadcrumbs'][] = array(
        	'text'      => $data['text_account'],
			'href'      => $this->url->link('account/account', '', true),
        	'separator' => false
      	);

      	$data['breadcrumbs'][] = array(
        	'text'      => $data['heading_title'],
			'href'      => $this->url->link('account/customerpartner/profile', '', true),
        	'separator' => false
      	);

		$data['customer_details'] = array(
			'firstname' => $this->customer->getFirstName(),
			'lastname' => $this->customer->getLastName(),
			'email' => $this->customer->getEmail()
		);

		if (isset($this->request->post['paypalfirst'])) {
		  $data['partner']['paypalfirst'] = $this->request->post['paypalfirst'];
		} elseif (isset($partner['paypalfirstname']) ) {
		  $data['partner']['paypalfirst'] = $partner['paypalfirstname'];
		} else {
		  $data['partner']['paypalfirst'] = '';
		}

		if (isset($this->request->post['paypallast'])) {
		  $data['partner']['paypallast'] = $this->request->post['paypallast'];
		} elseif (isset($partner['paypallastname']) ) {
		  $data['partner']['paypallast'] = $partner['paypallastname'];
		} else {
		  $data['partner']['paypallast'] = '';
		}

		if (isset($this->request->post['paypalid'])) {
		  $data['partner']['paypalid'] = $this->request->post['paypalid'];
		} elseif (isset($partner['paypalid']) ) {
		  $data['partner']['paypalid'] = $partner['paypalid'];
		} else {
		  $data['partner']['paypalid'] = '';
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['screenname_error'])) {
			$data['screenname_error'] = $this->error['screenname_error'];
		} else {
			$data['screenname_error'] = '';
		}

		if (isset($this->error['companyname_error'])) {
			$data['companyname_error'] = $this->error['companyname_error'];
		} else {
			$data['companyname_error'] = '';
		}

		if (isset($this->error['paypal_error'])) {
			$data['paypal_error'] = $this->error['paypal_error'];
		} else {
			$data['paypal_error'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['action'] = $this->url->link('account/customerpartner/profile', '', true);
		$data['back'] = $this->url->link('account/account', '', true);
		$data['view_profile'] = $this->url->link('customerpartner/profile&id='.$this->customer->getId(), '', true);

		$data['isMember'] = true;
		if($this->config->get('module_wk_seller_group_status')) {
      		$data['module_wk_seller_group_status'] = true;
      		$this->load->model('account/customer_group');
			$isMember = $this->model_account_customer_group->getSellerMembershipGroup($this->customer->getId());
			if($isMember) {
				$allowedAccountMenu = $this->model_account_customer_group->getaccountMenu($isMember['gid']);
				if($allowedAccountMenu['value']) {
					$accountMenu = explode(',',$allowedAccountMenu['value']);
					if($accountMenu && !in_array('profile:profile', $accountMenu)) {
						$data['isMember'] = false;
					}
				}
			} else {
				$data['isMember'] = false;
			}
  	} else {
  		if(!is_array($this->config->get('marketplace_allowed_account_menu')) || !in_array('profile', $this->config->get('marketplace_allowed_account_menu'))) {
  			$this->response->redirect($this->url->link('account/account','', true));
  		}
  	}

		$post_array = array('screenName','shortProfile','companyName','twitterId','facebookId','companyLocality','companyDescription','otherpayment','taxinfo');

		foreach ($post_array as $key => $value) {
			if (isset($this->request->post[$value])) {
			  $data['partner'][strtolower($value)] = $this->request->post[$value];
			}
		}

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

$data['separate_view'] = false;

$data['separate_column_left'] = '';

if ($this->config->get('marketplace_separate_view') && isset($this->session->data['marketplace_separate_view']) && $this->session->data['marketplace_separate_view'] == 'separate') {
  $data['separate_view'] = true;
  $data['column_left'] = '';
  $data['column_right'] = '';
  $data['content_top'] = '';
  $data['content_bottom'] = '';
  $data['separate_column_left'] = $this->load->controller('account/customerpartner/column_left');

  $data['footer'] = $this->load->controller('account/customerpartner/footer');
  $data['header'] = $this->load->controller('account/customerpartner/header');
}

		$this->response->setOutput($this->load->view('account/customerpartner/profile', $data));

	}

	public function validateForm() {
		$error = false;
		$this->load->language('account/customerpartner/profile');
		if(strlen(trim($this->request->post['screenName'])) < 1) {
			$this->request->post['screenName'] = '';
			$this->error['screenname_error'] = $this->language->get('error_seo_keyword');
			$this->error['warning'] = $this->language->get('error_check_form');
			$error = true;
		}
		$profile = $this->model_account_customerpartner->getProfile();

		if (isset($this->request->post['paypalid']) && $this->request->post['paypalid'] && isset($this->request->post['paypalfirst']) && $this->request->post['paypalfirst'] && isset($this->request->post['paypallast']) && $this->request->post['paypallast']) {
			if(!filter_var($this->request->post['paypalid'], FILTER_VALIDATE_EMAIL)) {
				$this->error['paypal_error'] = $this->language->get('error_paypal');
				$this->error['warning'] = $this->language->get('error_check_form');
				$error = true;
			} else {

				$API_UserName = $this->config->get('marketplace_paypal_user');

				$API_Password = $this->config->get('marketplace_paypal_password');

				$API_Signature = $this->config->get('marketplace_paypal_signature');

				$API_RequestFormat = "NV";

				$API_ResponseFormat = "NV";

				$API_EMAIL = $this->request->post['paypalid'];

				$bodyparams = array(
					"matchCriteria" => "NAME",
					"emailAddress" =>$this->request->post['paypalid'],
					"firstName" => $this->request->post['paypalfirst'],
					"lastName" => $this->request->post['paypallast']
				);

				if ($this->config->get('marketplace_paypal_mode')) {

					$API_AppID = "APP-80W284485P519543T";

					$curl_url = trim("https://svcs.sandbox.paypal.com/AdaptiveAccounts/GetVerifiedStatus");

					$header = array(
						"X-PAYPAL-SECURITY-USERID: " . $API_UserName ,
						"X-PAYPAL-SECURITY-SIGNATURE: " . $API_Signature ,
						"X-PAYPAL-SECURITY-PASSWORD: " . $API_Password ,
						"X-PAYPAL-APPLICATION-ID: " . $API_AppID ,
						"X-PAYPAL-REQUEST-DATA-FORMAT: " . $API_RequestFormat ,
						"X-PAYPAL-RESPONSE-DATA-FORMAT:" . $API_ResponseFormat ,
						"X-PAYPAL-SANDBOX-EMAIL-ADDRESS:" . $API_EMAIL ,
					);
				} else {

					$API_AppID = $this->config->get('marketplace_paypal_appid');

					$curl_url = trim("https://svcs.paypal.com/AdaptiveAccounts/GetVerifiedStatus");

					$header = array(
						"X-PAYPAL-SECURITY-USERID: " . $API_UserName ,
						"X-PAYPAL-SECURITY-SIGNATURE: " . $API_Signature ,
						"X-PAYPAL-SECURITY-PASSWORD: " . $API_Password ,
						"X-PAYPAL-APPLICATION-ID: " . $API_AppID ,
						"X-PAYPAL-REQUEST-DATA-FORMAT: " . $API_RequestFormat ,
						"X-PAYPAL-RESPONSE-DATA-FORMAT:" . $API_ResponseFormat ,
						"X-PAYPAL-EMAIL-ADDRESS:" . $API_EMAIL ,
					);
				}

				$body_data = http_build_query($bodyparams, "", chr(38));

				$curl = curl_init();

				curl_setopt($curl, CURLOPT_URL, $curl_url);

				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

				curl_setopt($curl, CURLOPT_POSTFIELDS, $body_data);


				curl_setopt($curl, CURLOPT_HTTPHEADER,$header);

				$response = strtolower(explode("=",explode('&', curl_exec($curl))[1])[1]);

				if ($response != 'success') {
					$this->error['paypal_error'] = $this->language->get('error_paypal');
					$this->error['warning'] = $this->language->get('error_check_form');
					$error = true;
				}
			}
		} else {
			// $this->request->post['paypalfirst'] = isset($profile['paypalfirstname']) && $profile['paypalfirstname'] ? $profile['paypalfirstname'] : '';
			// $this->request->post['paypallast'] = isset($profile['paypallastname']) && $profile['paypallastname'] ? $profile['paypallastname'] : '';
			// $this->request->post['paypalid'] = isset($profile['paypalid']) && $profile['paypalid'] ? $profile['paypalid'] : '';
		}

		if(strlen(trim($this->request->post['companyName'])) < 1) {
			$this->request->post['companyName'] = '';
			$this->error['companyname_error'] = $this->language->get('error_company_name');
			$this->error['warning'] = $this->language->get('error_check_form');
			$error = true;
		}else{
			$this->load->model('customerpartner/master');
			$check_companyname = $this->model_customerpartner_master->getShopData($this->request->post['companyName']);
			if ($check_companyname && $check_companyname['customer_id'] != $this->customer->getId()) {
				$this->error['companyname_error'] = $this->language->get('error_company_name_exists');
				$this->error['warning'] = $this->language->get('error_check_form');
				$error = true;
			}
		}

		if($error) {
			return false;
		} else {
			return true;
		}

	}

}
?>
