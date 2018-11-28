<?php
//==============================================================================
// Square Payment Gateway v302.2
// 
// Author: Clear Thinking, LLC
// E-mail: johnathan@getclearthinking.com
// Website: http://www.getclearthinking.com
// 
// All code within this file is copyright Clear Thinking, LLC.
// You may not copy or reuse code within this file without written permission.
//==============================================================================

class ControllerExtensionPaymentSquare extends Controller {
	private $type = 'payment';
	private $name = 'square';
	
	public function logFatalErrors() {
		$error = error_get_last();
		if ($error['type'] === E_ERROR) { 
			$this->log->write(strtoupper($this->name) . ': Order could not be completed due to the following fatal error:');
			$this->log->write('PHP Fatal Error:  ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']);
		}
	}
	
	//==============================================================================
	// index()
	//==============================================================================
	public function index() {
		register_shutdown_function(array($this, 'logFatalErrors'));
		
		$data['type'] = $this->type;
		$data['name'] = $this->name;
		
		$data['settings'] = $settings = $this->getSettings();
		$data['language'] = $this->session->data['language'];
		$data['checkout_success'] = $this->url->link('checkout/successquare', '', 'SSL');
		$data['order_id'] = (isset($this->session->data['order_id'])) ? $this->session->data['order_id'] : '';
	//	echo '<pre>';print_r($this->session->data);die('fsfsf');
		$data['postal_code'] = $this->session->data['shipping_address']['postcode'];
	    //$data['shipping_address_field'] = $this->session->data['shipping_address']['country'].', '.$this->session->data['shipping_address']['address_1'].' '.$this->session->data['shipping_address']['address_2'];
	  // 68 Stevens Road, Rm8 2ql
	    $data['shipping_address_field'] = $this->session->data['shipping_address']['address_1'].', '.$this->session->data['shipping_address']['postcode'];
		$this->load->model('checkout/order');
		$data['order_info'] = $this->model_checkout_order->getOrder($data['order_id']);
		
		$data['customer_cards'] = array();
		$data['logged_in'] = $this->customer->isLogged();
		
		if ($this->customer->isLogged()) {
			$customer_id_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "square_customer WHERE customer_id = " . (int)$this->customer->getId() . " AND transaction_mode = '" . $this->db->escape($settings['transaction_mode']) . "'");
			if ($customer_id_query->num_rows) {
				$customer_response = $this->curlRequest('customers/' . $customer_id_query->row['square_customer_id']);
				if (!empty($customer_response['error'])) {
					$this->db->query("DELETE FROM " . DB_PREFIX . "square_customer WHERE square_customer_id = '" . $this->db->escape($customer_id_query->row['square_customer_id']) . "'");
				} elseif ($data['settings']['allow_stored_cards'] && !empty($customer_response['customer']['cards'])) {
					$data['customer_cards'] = $customer_response['customer']['cards'];
				}
			}
		}
		
		$data['square_errors'] = array(
			'cardNumber',
			'expirationDate',
			'postalCode',
			'cvv',
			'missing_card_data',
			'unknown',
		);
		
		// Render
		$theme = (version_compare(VERSION, '2.2', '<')) ? $this->config->get('config_template') : str_replace('theme_', '', $this->config->get('config_theme'));
		$template = (file_exists(DIR_TEMPLATE . $theme . '/template/extension/' . $this->type . '/' . $this->name . '.twig')) ? $theme : 'default';
		$template_file = DIR_TEMPLATE . $template . '/template/extension/' . $this->type . '/' . $this->name . '.twig';
		
		if (version_compare(VERSION, '3.0', '>=')) {
			$override_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "theme WHERE theme = '" . $this->db->escape($theme) . "' AND route = 'extension/" . $this->type . "/" . $this->name . "'");
			if ($override_query->num_rows) {
				$cache_file = DIR_CACHE . $this->name . '.twig.' . strtotime($override_query->row['date_added']);
				
				if (!file_exists($cache_file)) {
					$old_files = glob(DIR_CACHE . $this->name . '.twig.*');
					foreach ($old_files as $old_file) unlink($old_file);
					file_put_contents($cache_file, html_entity_decode($override_query->row['code'], ENT_QUOTES, 'UTF-8'));
				}
				
				$template_file = $cache_file;
			}
		}
		
		if (is_file($template_file)) {
			extract($data);
			
			ob_start();
			require(class_exists('VQMod') ? VQMod::modCheck(modification($template_file)) : modification($template_file));
			$output = ob_get_clean();
			
			return $output;
		} else {
			return 'Error loading template file';
		}
	}
	
	//==============================================================================
	// deleteCard()
	//==============================================================================
	public function deleteCard() {
		$settings = $this->getSettings();
		$customer_id_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "square_customer WHERE customer_id = " . (int)$this->customer->getId() . " AND transaction_mode = '" . $this->db->escape($settings['transaction_mode']) . "'");
		if (!$customer_id_query->num_rows) {
			echo 'No such card ID';
		} else {
			$delete_response = $this->curlRequest('customers/' . $customer_id_query->row['square_customer_id'] . '/cards/' . $this->request->get['id'], array(), 'DELETE');
			if (!empty($delete_response['error'])) {
				echo $delete_response['error'];
			}
		}
	}
	
	//==============================================================================
	// createCharge()
	//==============================================================================
	public function createCharge() {
		register_shutdown_function(array($this, 'logFatalErrors'));
		unset($this->session->data[$this->name . '_order_error']);
		
		$settings = $this->getSettings();
	
		$this->session->data['card-data'] = $this->request->post['cardData'];
		$this->session->data['cardname'] = $this->request->post['cardname'];
    	$billing_address_set =	$this->request->post['billing_address_set'];
// 	if($billing_address_set == '1'){
		    
		    
// 		    $this->session->data['payment_address']['firstname'] = $this->session->data['shipping_address']['firstname'];
// 			$this->session->data['payment_address']['lastname'] = $this->session->data['shipping_address']['lastname'];
// 			$this->session->data['payment_address']['company'] = $this->session->data['shipping_address']['company'];
// 			$this->session->data['payment_address']['address_1'] = $this->session->data['shipping_address']['address_1'];
// 			$this->session->data['payment_address']['address_2'] = $this->session->data['shipping_address']['address_2'];
// 			$this->session->data['payment_address']['postcode'] = $this->session->data['shipping_address']['postcode']; 
// 			$this->session->data['payment_address']['city'] = $this->session->data['shipping_address']['city'];
// 			$this->session->data['payment_address']['country_id'] = $this->session->data['shipping_address']['country_id'];
// 			$this->session->data['payment_address']['zone_id'] = $this->session->data['shipping_address']['zone_id'];

// 			$this->load->model('localisation/country');

// 			$country_info = $this->model_localisation_country->getCountry($this->session->data['shipping_address']['country_id']);

// 			if ($country_info) {
// 				$this->session->data['payment_address']['country'] = $country_info['name'];
// 				$this->session->data['payment_address']['iso_code_2'] = $country_info['iso_code_2'];
// 				$this->session->data['payment_address']['iso_code_3'] = $country_info['iso_code_3'];
// 				$this->session->data['payment_address']['address_format'] = $country_info['address_format'];
// 			} else {
// 				$this->session->data['payment_address']['country'] = '';
// 				$this->session->data['payment_address']['iso_code_2'] = '';
// 				$this->session->data['payment_address']['iso_code_3'] = '';
// 				$this->session->data['payment_address']['address_format'] = '';
// 			}


		    
		    
		    
// 		}

// 		    unset($this->session->data['shipping_address_data']);
        // 		echo '<pre>';print_r($this->session);die('sfsdfsf');
		// Set needed variables
		$language_data = $this->load->language('total/total');
		$language = (isset($this->session->data['language'])) ? $this->session->data['language'] : $this->config->get('config_language');
		$currency = $this->session->data['currency'];
		$main_currency = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` = 'config_currency' AND store_id = 0")->row['value'];
		
		$this->load->model('checkout/order');
		$order_id = $this->session->data['order_id'];
		$order_info = $this->model_checkout_order->getOrder($order_id);
	
		$data = array(
			'idempotency_key' => uniqid(),
		);
		
		// Get square_customer_id
		$square_customer_id = '';
		if ($this->customer->isLogged()) {
			$customer_id_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "square_customer WHERE customer_id = " . (int)$this->customer->getId() . " AND transaction_mode = '" . $this->db->escape($settings['transaction_mode']) . "'");
			if ($customer_id_query->num_rows) {
				$square_customer_id = $customer_id_query->row['square_customer_id'];
			}
		}
		if ($square_customer_id) {
			$data['customer_id'] = $square_customer_id;
		}
		
		// Create or update customer
		if (!empty($this->request->post['card_id'])) {
			$data['customer_card_id'] = $this->request->post['card_id'];
		} else {
			if ($this->request->post['store_card'] == 'false') {
				$data['card_nonce'] = $this->request->post['nonce'];
			} else {
				$billing_address = array(
					'address_line_1'					=> $order_info['payment_address_1'],
					'address_line_2'					=> $order_info['payment_address_2'],
					'locality'							=> $order_info['payment_city'],
					'administrative_district_level_1'	=> $order_info['payment_zone'],
					'postal_code'						=> $order_info['payment_postcode'],
					'country'							=> $order_info['payment_iso_code_2'],
				);
				
				$customer_data = array(
					'given_name'	=> $order_info['payment_firstname'],
					'family_name'	=> $order_info['payment_lastname'],
					'company_name'	=> $order_info['payment_company'],
					'email_address'	=> $order_info['email'],
					'address'		=> $billing_address,
					'reference_id'	=> $order_info['customer_id'],
				);
				
				$telephone = preg_replace('/\D/', '', $order_info['telephone']);
				if ($telephone) {
					$customer_data['phone_number'] = $telephone;
				}
				
				if ($square_customer_id) {
					$customer_response = $this->curlRequest('customers/' . $square_customer_id, $customer_data, 'PUT');
				} else {
					$customer_response = $this->curlRequest('customers', $customer_data, 'POST');
				}
				
				if (!empty($customer_response['error'])) {
					echo $customer_response['error'];
					return;
				} else {
					$data['customer_id'] = $customer_response['customer']['id'];
					if (!$square_customer_id) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "square_customer SET customer_id = " . (int)$this->customer->getId() . ", square_customer_id = '" . $this->db->escape($customer_response['customer']['id']) . "', transaction_mode = '" . $this->db->escape($settings['transaction_mode']) . "'");
					}
				}
				
				$card_data = array(
					'card_nonce'		=> $this->request->post['nonce'],
					'billing_address'	=> array('postal_code' => $this->request->post['postcode']),
					'cardholder_name'	=> $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'],
				);
				
				$card_response = $this->curlRequest('customers/' . $customer_response['customer']['id'] . '/cards', $card_data, 'POST');
				
				if (!empty($card_response['error'])) {
					echo $card_response['error'];
					return;
				} else {
					$data['customer_card_id'] = $card_response['card']['id'];
				}
			}
		}
		
		// Check order total
		if ($order_info['total'] < 1) {
			echo 'Amount is less than 1.00';
			return;
		}
		
		// Check fraud data
		$order_status_id = $settings['success_status_id'];
		
		$data['delay_capture'] = ($settings['charge_mode'] == 'authorize') ? true : false;
		
		if ($settings['charge_mode'] == 'fraud') {
			if (version_compare(VERSION, '2.0.3', '<')) {
				if ($this->config->get('config_fraud_detection')) {
					$this->load->model('checkout/fraud');
					if ($this->model_checkout_fraud->getFraudScore($order_info) > $this->config->get('config_fraud_score')) {
						$data['delay_capture'] = true;
					}
				}
			} else {
				$this->load->model('account/customer');
				$customer_info = $this->model_account_customer->getCustomer($order_info['customer_id']);
				
				if (empty($customer_info['safe'])) {
					$fraud_extensions = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = 'fraud' ORDER BY `code` ASC")->rows;
					
					foreach ($fraud_extensions as $extension) {
						$prefix = (version_compare(VERSION, '3.0', '<')) ? '' : 'fraud_';
						if (!$this->config->get($prefix . $extension['code'] . '_status')) continue;
						
						if (version_compare(VERSION, '2.3', '<')) {
							$this->load->model('fraud/' . $extension['code']);
							$fraud_status_id = $this->{'model_fraud_' . $extension['code']}->check($order_info);
						} else {
							$this->load->model('extension/fraud/' . $extension['code']);
							$fraud_status_id = $this->{'model_extension_fraud_' . $extension['code']}->check($order_info);
						}
						
						if ($fraud_status_id) {
							$data['delay_capture'] = true;
						}
					}
				}
			}
		}
		
		if ($data['delay_capture']) {
			$order_status_id = $settings['authorize_status_id'];
		}
		
		// Set up other charge data
		$data['amount_money'] = array(
			'amount'	=> round(($settings['currencies_' . $currency] == 'JPY' ? 1 : 100) * $this->currency->convert($order_info['total'], $main_currency, $settings['currencies_' . $currency])),
			'currency'	=> $settings['currencies_' . $currency],
		);
		
		$data['reference_id'] = 'order_id:' . $order_id;
		
		/*
		$product_names = array();
		foreach ($this->cart->getProducts() as $product) {
			$options = array();
			foreach ($product['option'] as $option) $options[] = $option['name'] . ': ' . (version_compare(VERSION, '2.0', '<') ? $option['option_value'] : $option['value']);
			$product_names[] = $product['name'] . ($options ? ' (' . implode(', ', $options) . ')' : '');
		}
		$product_names = implode(', ', $product_names);
		$data['note'] = (strlen($product_names > 60)) ? substr($product_names, 0, 57) . '...' : $product_names;
		*/
		
		$data['billing_address'] = array(
			'address_line_1'					=> $order_info['payment_address_1'],
			'address_line_2'					=> $order_info['payment_address_2'],
			'locality'							=> $order_info['payment_city'],
			'administrative_district_level_1'	=> $order_info['payment_zone'],
			'postal_code'						=> $order_info['payment_postcode'],
			'country'							=> $order_info['payment_iso_code_2'],
		);
		
		if (isset($this->session->data['shipping_method'])) {
			$data['shipping_address'] = array(
				'address_line_1'					=> $order_info['shipping_address_1'],
				'address_line_2'					=> $order_info['shipping_address_2'],
				'locality'							=> $order_info['shipping_city'],
				'administrative_district_level_1'	=> $order_info['shipping_zone'],
				'postal_code'						=> $order_info['shipping_postcode'],
				'country'							=> $order_info['shipping_iso_code_2'],
			);
		}
		
		$data['buyer_email_address'] = $order_info['email'];
		//echo '<pre>';print_r($data);die('sdfsdf');
			$this->session->data['payment_data'] = $data;
	//	$charge_response = $this->curlRequest('transactions', $data, 'POST');
			$charge_response = $this->curlRequest('checkouts', $data, 'POST');
	//	echo '<pre>';print_r($charge_response);die('sdfsdfs');
		if (!empty($charge_response['error']) && $charge_response['error'] != 'Expected an array.') {
			echo $charge_response['error'];
			return;
		}
		
	//	echo '<pre>';print_r($charge_response);die('dfd');
		$charge_response = '';
		// Create comment data
		$strong = '<strong style="display: inline-block; width: 135px; padding: 2px 5px">';
		
		$comment = '';
		if (!empty($charge_response)) {
			$comment .= '<script type="text/javascript" src="view/javascript/square.js"></script>';
			$comment .= $strong . 'Square Charge ID:</strong>' . $charge_response['transaction']['id'] . '<br />';
			
			foreach ($charge_response['transaction']['tenders'] as $tender) {
				$charge_amount = $tender['amount_money']['amount'] / ($tender['amount_money']['currency'] == 'JPY' ? 1 : 100);
				$comment .= $strong . 'Charge Amount:</strong>' . $this->currency->format($charge_amount, $tender['amount_money']['currency'], 1) . '<br />';
				$comment .= $strong . 'Captured:</strong>' . ($tender['card_details']['status'] == 'CAPTURED' ? 'Yes' : '<span>No &nbsp;</span> <a style="cursor: pointer" onclick="squareCapture($(this), \'' . $charge_response['transaction']['id'] . '\')">(Capture)</a>') . '<br />';
				$comment .= $strong . 'Card Brand:</strong> ' . $tender['card_details']['card']['card_brand'] . '<br />';
				$comment .= $strong . 'Card Number:</strong>•••• •••• •••• ' . $tender['card_details']['card']['last_4'] . '<br />';
				$comment .= $strong . 'Refund:</strong><a style="cursor: pointer" onclick="squareRefund($(this), \'' . number_format($charge_amount, 2, '.', '') . '\', \'' . $tender['amount_money']['currency'] . '\', \'' . $charge_response['transaction']['id'] . '\', \'' . $tender['id'] . '\')">(Refund)</a>';
			}
		}
		
		// Add order history
// 		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = " . (int)$order_id . ", order_status_id = '" . (int)$order_status_id . "', notify = 0, comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
		
		$this->session->data[$this->name . '_order_id'] = $order_id;
		$this->session->data[$this->name . '_order_status_id'] = $order_status_id;
	}
	
	

	
	//==============================================================================
	// completeOrder()
	//==============================================================================
	public function completeOrder1() {
	    $json = '';
	    echo '<pre>';print_r($this->session->data);die('sdfsf');
	    die('dsfsfs');
		if (empty($this->session->data[$this->name . '_order_id'])) {
			echo 'No order data';
			return;
		}
		
		$order_id = $this->session->data[$this->name . '_order_id'];
		$order_status_id = $this->session->data[$this->name . '_order_status_id'];
		
		unset($this->session->data[$this->name . '_order_id']);
		unset($this->session->data[$this->name . '_order_status_id']);
		
		$this->session->data[$this->name . '_order_error'] = $order_id;
		
		$this->load->model('checkout/order');
		$this->model_checkout_order->addOrderHistory($order_id, $order_status_id);
			$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	//==============================================================================
	// completeWithError()
	//==============================================================================
	
	
	
	public function completeWithError() {
		if (empty($this->session->data[$this->name . '_order_error'])) {
			echo 'Payment was not processed';
			return;
		}
		
		$settings = $this->getSettings();
		
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = " . (int)$settings['error_status_id'] . ", date_modified = NOW() WHERE order_id = " . (int)$this->session->data[$this->name . '_order_error']);
		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = " . (int)$this->session->data[$this->name . '_order_error'] . ", order_status_id = '" . (int)$settings['error_status_id'] . "', notify = 0, comment = 'The order could not be completed normally due to the following error:<br /><br /><em>" . $this->db->escape($this->request->post['error_message']) . "</em><br /><br />Double-check your SMTP settings in System > Settings > Mail, and then try disabling or uninstalling any modifications that affect customer orders (i.e. the /catalog/model/checkout/order.php file). One of those is usually the cause of errors like this.', date_added = NOW()");
		
		unset($this->session->data[$this->name . '_order_error']);
	}
	
	//==============================================================================
	// Private functions
	//==============================================================================
	private function getSettings() {
		$code = (version_compare(VERSION, '3.0', '<') ? '' : $this->type . '_') . $this->name;
		
		$settings = array();
		$settings_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `code` = '" . $this->db->escape($code) . "' ORDER BY `key` ASC");
		
		foreach ($settings_query->rows as $setting) {
			$value = $setting['value'];
			if ($setting['serialized']) {
				$value = (version_compare(VERSION, '2.1', '<')) ? unserialize($setting['value']) : json_decode($setting['value'], true);
			}
			$split_key = preg_split('/_(\d+)_?/', str_replace($code . '_', '', $setting['key']), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			
				if (count($split_key) == 1)	$settings[$split_key[0]] = $value;
			elseif (count($split_key) == 2)	$settings[$split_key[0]][$split_key[1]] = $value;
			elseif (count($split_key) == 3)	$settings[$split_key[0]][$split_key[1]][$split_key[2]] = $value;
			elseif (count($split_key) == 4)	$settings[$split_key[0]][$split_key[1]][$split_key[2]][$split_key[3]] = $value;
			else 							$settings[$split_key[0]][$split_key[1]][$split_key[2]][$split_key[3]][$split_key[4]] = $value;
		}
		
		return $settings;
	}
	
	public function paysqare(){
	    $country_id = $this->config->get('config_country_id');
	    
	    $this->load->model('localisation/country');

			$country_info = $this->model_localisation_country->getCountry($$country_id);
			
	   
	    $json = '';
	  $this->session->data['payment_data']['billing_address']['country'] =  $country_info['name'];
	   	$data = $this->session->data['payment_data'];
	   	
	   	$charge_response = $this->curlRequest('transactions', $data, 'POST');
	  

		if (!empty($charge_response['error']) && $charge_response['error'] != 'Expected an array.') {
			echo $charge_response['error'];
			return;
		}
		
		// Create comment data
		$strong = '<strong style="display: inline-block; width: 135px; padding: 2px 5px">';
		
		$comment = '';
		if (!empty($charge_response)) {
			$comment .= '<script type="text/javascript" src="view/javascript/square.js"></script>';
			$comment .= $strong . 'Square Charge ID:</strong>' . $charge_response['transaction']['id'] . '<br />';
			
			foreach ($charge_response['transaction']['tenders'] as $tender) {
				$charge_amount = $tender['amount_money']['amount'] / ($tender['amount_money']['currency'] == 'JPY' ? 1 : 100);
				$comment .= $strong . 'Charge Amount:</strong>' . $this->currency->format($charge_amount, $tender['amount_money']['currency'], 1) . '<br />';
				$comment .= $strong . 'Captured:</strong>' . ($tender['card_details']['status'] == 'CAPTURED' ? 'Yes' : '<span>No &nbsp;</span> <a style="cursor: pointer" onclick="squareCapture($(this), \'' . $charge_response['transaction']['id'] . '\')">(Capture)</a>') . '<br />';
				$comment .= $strong . 'Card Brand:</strong> ' . $tender['card_details']['card']['card_brand'] . '<br />';
				$comment .= $strong . 'Card Number:</strong>•••• •••• •••• ' . $tender['card_details']['card']['last_4'] . '<br />';
				if($this->session->data['cardname']){
				$comment .= $strong . 'Name on card:</strong> ' . $this->session->data['cardname'] . '<br />';
				}
				$comment .= $strong . 'Refund:</strong><a style="cursor: pointer" onclick="squareRefund($(this), \'' . number_format($charge_amount, 2, '.', '') . '\', \'' . $tender['amount_money']['currency'] . '\', \'' . $charge_response['transaction']['id'] . '\', \'' . $tender['id'] . '\')">(Refund)</a>';
			}
		}
		
	
		
	   
	   
	   if (empty($this->session->data[$this->name . '_order_id'])) {
			echo 'No order data';
			return;
		}
		
			// Add order history
		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = " . (int)$order_id . ", order_status_id = '" . (int)$order_status_id . "', notify = 0, comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
		
		//echo '<pre>';print_r($this->session->data);die('dfgd');
		$order_id = $this->session->data[$this->name . '_order_id'];
		$order_status_id = $this->session->data[$this->name . '_order_status_id'];
		
		unset($this->session->data[$this->name . '_order_id']);
		unset($this->session->data[$this->name . '_order_status_id']);
		
		$this->session->data[$this->name . '_order_error'] = $order_id;
		
		$this->load->model('checkout/order');
		$this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment);
		
		// for complete payment
	
			
			$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	   
	        
	}
	
	public function change_address(){
	    $order_id = $this->session->data['order_id'];
	   // echo '<pre>';print_r($this->request->post);die();
	    $this->load->model('checkout/order');
	    	$this->load->model('localisation/country');
	    
	    $country_info = $this->model_localisation_country->getCountry($this->request->post['add_country']);
   
	   // echo '<pre>';print_r($country_info);
	    $json['full_name']  = $this->request->post['f_name'];
	    $json['address_1']  = $this->request->post['add_1'];
	    $json['address_2']  = $this->request->post['add_2'];
	    $json['country']   = $country_info['name'];
	    $json['country_id']   = $this->request->post['add_country'];
	      $this->load->model('checkout/order');
	     $this->model_checkout_order->update_shipping($order_id, $json);
	   // $country_id = $this->request->post[''];
	   
	   	$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	  
	 
	}

	public function change_billing_address(){
	    $order_id = $this->session->data['order_id'];
	    // echo '<pre>';print_r($this->session->data);die('sdfds');
	   // echo '<pre>';print_r($this->request->post);die();


$this->session->data['payment_address']['firstname'] = $this->request->post['f_name'];
$this->session->data['payment_address']['address_1'] = $this->request->post['add_1'];
$this->session->data['payment_address']['address_2'] = $this->request->post['add_2'];
$this->session->data['payment_address']['postcode'] = $this->request->post['postcode'];
$this->session->data['payment_address']['city'] = $this->request->post['city'];
$this->session->data['payment_data']['billing_address']['address_line_1'] = $this->request->post['add_1'];
$this->session->data['payment_data']['billing_address']['address_line_2'] = $this->request->post['add_2'];
$this->session->data['payment_data']['billing_address']['postal_code'] = $this->request->post['postcode'];


	    $this->load->model('checkout/order');
	    	$this->load->model('localisation/country');
	    
	    $country_info = $this->model_localisation_country->getCountry($this->request->post['add_country']);
   
	   // echo '<pre>';print_r($country_info);
	    $json['full_name']  = $this->request->post['f_name'];
	    $json['address_1']  = $this->request->post['add_1'];
	    $json['address_2']  = $this->request->post['add_2'];
	    $json['postcode']  = $this->request->post['postcode'];
	    $json['country']   = $country_info['name'];
	    $json['city']   = $this->request->post['city'];
	    $json['country_id']   = $this->request->post['add_country'];


	      $this->load->model('checkout/order');
	     $this->model_checkout_order->update_billing($order_id, $json);
	   // $country_id = $this->request->post[''];
	   	$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	  
	 
	}
	
	public function change_ship_email(){
	    $order_id = $this->session->data['order_id'];
	   // echo '<pre>';print_r($this->request->post);die();
	    $this->load->model('checkout/order');
	    $this->load->model('localisation/country');
	    
	   
   
	   // echo '<pre>';print_r($country_info);
	    $json['email']  = $this->request->post['s_email'];
	  
	  
	      $this->load->model('checkout/order');
	     $this->model_checkout_order->update_shipping_email($order_id, $json);
	   // $country_id = $this->request->post[''];
	   
	   	$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	  
	 
	}
	
	private function curlRequest($api = '', $data = array(), $type = 'GET') {
// 	    var_dump($type);
// 	echo '<pre>';print_r($api);
//	echo '<pre>';print_r($data);
// 	$data['buyer_email_address'] = '';

$data['order'] = array(
    'line_items' =>  array(
        '0' =>array(
           'name' => 'sassdfsg',
           'base_price_money' => $data['amount_money']['amount'],
           'quantity' => '1',
           ),
        '2' =>array(
           'name' => 'sassdfsg',
           'base_price_money' => $data['amount_money']['amount'],
           'quantity' => '1',
           ),
        ),
    'location_id' => $settings[$settings['transaction_mode'] . '_location_id'],
    'reference_id' => $data['reference_id'],
    'total_discount_money' => '',
    'total_money' => $data['amount_money']['amount'],
    'total_tax_money' => '',
    
    
    );

		$settings = $this->getSettings();
		
		$url = 'https://connect.squareup.com/v2/';
		if ($api) {
			$url .= (strpos($api, 'customers') === 0) ? $api : 'locations/' . $settings[$settings['transaction_mode'] . '_location_id'] . '/' . $api;
		}
		
	
		
		if ($type == 'GET') {
			$curl = curl_init($url . '?' . http_build_query($data));
		} else {
			$curl = curl_init($url);
			if ($type != 'DELETE' && $data) {
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
			}
			if ($type == 'POST') {
				curl_setopt($curl, CURLOPT_POST, true);
			} else {
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
			}
		}
		
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , 'Authorization: Bearer ' . $settings[$settings['transaction_mode'] . '_access_token']));
		
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		
		$response = json_decode(curl_exec($curl), true);
	//	echo '<pre>';print_r($response);die('sdfsf');
		if (curl_error($curl)) {
			$response['error'] = 'CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl);
		}
		curl_close($curl);
		
		if (!empty($response['errors'])) {
			$response['error'] = array();
			foreach ($response['errors'] as $error) {
			   
			    if($error['code'] != 'Expected an array'){
				if (!empty($settings['error_' . strtolower($error['code']) . '_' . $this->session->data['language']])) {
					$response['error'][] = $settings['error_' . strtolower($error['code']) . '_' . $this->session->data['language']];
				} else {
					$response['error'][] = $error['detail'];
				}
				$this->log->write(strtoupper($this->name) . ' ERROR (' . $error['code'] . '): ' . $error['detail']);
			}
			}
			$response['error'] = implode('<br />', $response['error']);
		}
	
		return $response;
	}
}
?>