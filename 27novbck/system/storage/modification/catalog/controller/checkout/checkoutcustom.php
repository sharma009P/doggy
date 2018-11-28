<?php
class ControllerCheckoutCheckoutcustom extends Controller {
	public function index() {

	// Validate cart has products and has stock.
    
    //     echo '<pre>';
    // print_r($_POST);
    // die('qwweqe');
    		// Validate minimum quantity requirements.
    		$products = $this->cart->getProducts();

    		foreach ($products as $product) {
    			$product_total = 0;
    
    			foreach ($products as $product_2) {
    				if ($product_2['product_id'] == $product['product_id']) {
    					$product_total += $product_2['quantity'];
    				}
    			}
                
    // 			if ($product['minimum'] > $product_total) {
    // 				$this->response->redirect($this->url->link('checkout/cart'));
    // 			}
    		}
    		    		
        if ($this->request->server['HTTPS']) {		
            $server = $this->config->get('config_ssl');	
        } 
        else {		
            $server = $this->config->get('config_url');	
        }  
            
        if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {		
            $data['logo'] = $server . 'image/' . $this->config->get('config_logo');		
        } 
        else {		
            $data['logo'] = '';	
        }

	$data['name'] = 'Doggy';//$this->config->get('config_name');
    		$this->load->language('checkout/checkout');
    
    		$this->document->setTitle($this->language->get('heading_title'));
    
    		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
    		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
    		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
    		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
    
    		// Required by klarna
    		if ($this->config->get('payment_klarna_account') || $this->config->get('payment_klarna_invoice')) {
    			$this->document->addScript('http://cdn.klarna.com/public/kitt/toc/v1.0/js/klarna.terms.min.js');
    		}
    
    		$data['breadcrumbs'] = array();
    
    		$data['breadcrumbs'][] = array(
    			'text' => $this->language->get('text_home'),
    			'href' => $this->url->link('common/home')
    		);
    
    		$data['breadcrumbs'][] = array(
    			'text' => $this->language->get('text_cart'),
    			'href' => $this->url->link('checkout/cart')
    		);
    
    		$data['breadcrumbs'][] = array(
    			'text' => $this->language->get('heading_title'),
    			'href' => $this->url->link('checkout/cart', '', true)
    		);
    
    		$data['text_checkout_option'] = sprintf($this->language->get('text_checkout_option'), 1);
    		$data['text_checkout_account'] = sprintf($this->language->get('text_checkout_account'), 2);
    		$data['text_checkout_payment_address'] = sprintf($this->language->get('text_checkout_payment_address'), 2);
    		$data['text_checkout_shipping_address'] = sprintf($this->language->get('text_checkout_shipping_address'), 3);
    		$data['text_checkout_shipping_method'] = sprintf($this->language->get('text_checkout_shipping_method'), 4);
    		
    		if ($this->cart->hasShipping()) {
    			$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 5);
    			$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 6);
    		} else {
    			$data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 3);
    			$data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 4);	
    		}
    
    		if (isset($this->session->data['error'])) {
    			$data['error_warning'] = $this->session->data['error'];
    			unset($this->session->data['error']);
    		} else {
    			$data['error_warning'] = '';
    		}
    
    		$data['logged'] = $this->customer->isLogged();
    
    		if (isset($this->session->data['account'])) {
    			$data['account'] = $this->session->data['account'];
    		} else {
    			$data['account'] = '';
    		}
    		$data['settings'] = $settings = $this->getSettings();
    		$data['shipping_address_field'] = $this->session->data['shipping_address']['country'].', '.$this->session->data['shipping_address']['address_1'].' '.$this->session->data['shipping_address']['address_2'];
        	$data['settings'] = $settings = $this->getSettings();
            $data['language'] = $this->session->data['language'];
            $data['text_card_number'] = html_entity_decode($data['settings']['text_card_number_' . $data['language']], ENT_QUOTES, 'UTF-8');
            $data['text_card_details'] = html_entity_decode($data['settings']['text_card_details_' . $data['language']], ENT_QUOTES, 'UTF-8');
            $data['text_card_expiry'] = html_entity_decode($settings['text_card_expiry_' . $data['language']], ENT_QUOTES, 'UTF-8');
            $data['text_card_postcode'] = html_entity_decode($settings['text_card_postcode_' . $data['language']], ENT_QUOTES, 'UTF-8');
             $data['shipping_address_field'] = $this->session->data['shipping_address']['country'].', '.$this->session->data['shipping_address']['address_1'].' '.$this->session->data['shipping_address']['address_2'];
             	$data['postal_code'] = $this->session->data['shipping_address']['postcode'];
            
             $transaction_mode = $data['settings'][$data['settings']['transaction_mode'] . '_application_id'];
    	
    	
    		$data['checkout_success'] = $this->url->link('checkout/successquare', '', 'SSL');
    
    		$data['shipping_required'] = '';
    
    		$data['column_left'] = $this->load->controller('common/column_left');
    		$data['column_right'] = $this->load->controller('common/column_right');
    		$data['content_top'] = $this->load->controller('common/content_top');
    		$data['content_bottom'] = $this->load->controller('common/content_bottom');
    		$data['footer'] = $this->load->controller('common/footer');
    		$data['header'] = $this->load->controller('common/header');


				$data['module_wgtm_status'] = $this->config->get('module_wgtm_status');
				$products = $this->cart->getProducts();
				$data['wcartpro'] = array();
				$this->load->model('catalog/product');
				$this->load->model('catalog/category');
				$data['wcurencysymbol'] = $this->session->data['currency'];
				foreach($products as $product){
					$product_info = $this->model_catalog_product->getProduct($product['product_id']);
					$wprice = $this->currency->format($product['price'], $this->session->data['currency'], '', false);
					$getcategories = $this->model_catalog_product->getCategories($product['product_id']);
					$categories = array();
					if($getcategories){
						foreach($getcategories as $cate){
							$getcategory = $this->model_catalog_category->getCategory($cate['category_id']);
							$categories[] = isset($getcategory['name']) ? $getcategory['name'] : '';
						}
					}
					$categories = implode(", ", $categories);
					$data['wcartpro'][] = array(
						'product_id'	=> $product['product_id'],
						'name'			=> $product['name'],
						'quantity'		=> $product['quantity'],
						'price'			=> $wprice,
						'categories'	=> $categories,
						'manufacturer'	=> $product_info['manufacturer'],
					);
				}
			
    		$this->response->setOutput($this->load->view('checkout/checkoutcustom', $data));
	    
	}
	
	private function getSettings() {
	    	$type = 'payment';
	        $name = 'square';
		$code = (version_compare(VERSION, '3.0', '<') ? '' : $type . '_') . $name;
		
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
	

		public function shipping() {
		$json = array();
		echo '<pre>';print_r($_REQUEST);
		die('dffd');

// 		$this->load->model('localisation/country');

// 		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

// 		if ($country_info) {
// 			$this->load->model('localisation/zone');

// 			$json = array(
// 				'country_id'        => $country_info['country_id'],
// 				'name'              => $country_info['name'],
// 				'iso_code_2'        => $country_info['iso_code_2'],
// 				'iso_code_3'        => $country_info['iso_code_3'],
// 				'address_format'    => $country_info['address_format'],
// 				'postcode_required' => $country_info['postcode_required'],
// 				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
// 				'status'            => $country_info['status']
// 			);
// 		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function customfield() {
		$json = array();

		$this->load->model('account/custom_field');

		// Customer Group
		if (isset($this->request->get['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->get['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->get['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

		foreach ($custom_fields as $custom_field) {
			$json[] = array(
				'custom_field_id' => $custom_field['custom_field_id'],
				'required'        => $custom_field['required']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}