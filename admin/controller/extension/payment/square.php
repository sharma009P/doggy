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
	
	public function index() {
		$data = array(
			'type'			=> $this->type,
			'name'			=> $this->name,
			'autobackup'	=> false,
			'save_type'		=> 'keepediting',
			'permission'	=> $this->hasPermission('modify'),
		);
		
		$this->loadSettings($data);
		
		// extension-specific
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "square_customer` (
				`customer_id` int(11) NOT NULL,
				`square_customer_id` varchar(255) NOT NULL,
				`transaction_mode` varchar(7) NOT NULL DEFAULT 'live',
				PRIMARY KEY (`customer_id`, `square_customer_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
		");
		
		//------------------------------------------------------------------------------
		// Data Arrays
		//------------------------------------------------------------------------------
		$data['language_array'] = array($this->config->get('config_language') => '');
		$data['language_flags'] = array();
		$this->load->model('localisation/language');
		foreach ($this->model_localisation_language->getLanguages() as $language) {
			$data['language_array'][$language['code']] = $language['name'];
			$data['language_flags'][$language['code']] = (version_compare(VERSION, '2.2', '<')) ? 'view/image/flags/' . $language['image'] : 'language/' . $language['code'] . '/' . $language['code'] . '.png';
		}
		
		$data['order_status_array'] = array(0 => $data['text_ignore']);
		$this->load->model('localisation/order_status');
		foreach ($this->model_localisation_order_status->getOrderStatuses() as $order_status) {
			$data['order_status_array'][$order_status['order_status_id']] = $order_status['name'];
		}
		
		$data['customer_group_array'] = array(0 => $data['text_guests']);
		$this->load->model((version_compare(VERSION, '2.1', '<') ? 'sale' : 'customer') . '/customer_group');
		foreach ($this->{'model_' . (version_compare(VERSION, '2.1', '<') ? 'sale' : 'customer') . '_customer_group'}->getCustomerGroups() as $customer_group) {
			$data['customer_group_array'][$customer_group['customer_group_id']] = $customer_group['name'];
		}
		
		$data['geo_zone_array'] = array(0 => $data['text_everywhere_else']);
		$this->load->model('localisation/geo_zone');
		foreach ($this->model_localisation_geo_zone->getGeoZones() as $geo_zone) {
			$data['geo_zone_array'][$geo_zone['geo_zone_id']] = $geo_zone['name'];
		}
		
		$data['store_array'] = array(0 => $this->config->get('config_name'));
		$store_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "store ORDER BY name");
		foreach ($store_query->rows as $store) {
			$data['store_array'][$store['store_id']] = $store['name'];
		}
		
		$data['currency_array'] = array($this->config->get('config_currency') => '');
		$this->load->model('localisation/currency');
		foreach ($this->model_localisation_currency->getCurrencies() as $currency) {
			$data['currency_array'][$currency['code']] = $currency['code'];
		}
		
		//------------------------------------------------------------------------------
		// Extensions Settings
		//------------------------------------------------------------------------------
		$data['settings'] = array();
		$data['settings'][] = array(
			'type'		=> 'tabs',
			'tabs'		=> array('extension_settings', 'order_statuses', 'restrictions', 'square_settings'),
		);
		$data['settings'][] = array(
			'key'		=> 'extension_settings',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'key'		=> 'status',
			'type'		=> 'select',
			'options'	=> array(1 => $data['text_enabled'], 0 => $data['text_disabled']),
			'default'	=> 1,
		);
		$data['settings'][] = array(
			'key'		=> 'sort_order',
			'type'		=> 'text',
			'default'	=> 1,
			'class'		=> 'short',
		);
		$data['settings'][] = array(
			'key'		=> 'title',
			'type'		=> 'multilingual_text',
			'default'	=> 'Credit / Debit Card',
		);
		$data['settings'][] = array(
			'key'		=> 'button_text',
			'type'		=> 'multilingual_text',
			'default'	=> 'Confirm Order',
		);
		$data['settings'][] = array(
			'key'		=> 'button_class',
			'type'		=> 'text',
			'default'	=> 'btn btn-primary',
		);
		$data['settings'][] = array(
			'key'		=> 'button_styling',
			'type'		=> 'text',
		);
		
		// Payment Page Text
		$data['settings'][] = array(
			'key'		=> 'payment_page_text',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center pad-bottom-sm">' . $data['help_payment_page_text'] . '</div>',
		);
		$data['settings'][] = array(
			'key'		=> 'text_card_details',
			'type'		=> 'multilingual_text',
			'default'	=> 'Card Details',
		);
		$data['settings'][] = array(
			'key'		=> 'text_use_your_stored_card',
			'type'		=> 'multilingual_text',
			'default'	=> 'Use Your Stored Card:',
		);
		$data['settings'][] = array(
			'key'		=> 'text_ending_in',
			'type'		=> 'multilingual_text',
			'default'	=> 'ending in',
		);
		$data['settings'][] = array(
			'key'		=> 'text_delete_this_card',
			'type'		=> 'multilingual_text',
			'default'	=> 'Delete this card',
		);
		$data['settings'][] = array(
			'key'		=> 'text_confirm',
			'type'		=> 'multilingual_text',
			'default'	=> 'This operation cannot be done. Continue?',
		);
		$data['settings'][] = array(
			'key'		=> 'text_success',
			'type'		=> 'multilingual_text',
			'default'	=> 'Success!',
		);
		$data['settings'][] = array(
			'key'		=> 'text_use_a_new_card',
			'type'		=> 'multilingual_text',
			'default'	=> '--- Use a New Card ---',
		);
		$data['settings'][] = array(
			'key'		=> 'text_card_name',
			'type'		=> 'multilingual_text',
			'default'	=> 'Name on Card:',
		);
		$data['settings'][] = array(
			'key'		=> 'text_card_number',
			'type'		=> 'multilingual_text',
			'default'	=> 'Card Number:',
		);
		$data['settings'][] = array(
			'key'		=> 'text_card_type',
			'type'		=> 'multilingual_text',
			'default'	=> 'Card Type:',
		);
		$data['settings'][] = array(
			'key'		=> 'text_card_expiry',
			'type'		=> 'multilingual_text',
			'default'	=> 'Card Expiry (MM/YY):',
		);
		$data['settings'][] = array(
			'key'		=> 'text_card_security',
			'type'		=> 'multilingual_text',
			'default'	=> 'Card Security Code (CVC):',
		);
		$data['settings'][] = array(
			'key'		=> 'text_card_postcode',
			'type'		=> 'multilingual_text',
			'default'	=> 'Card Postcode:',
		);
		$data['settings'][] = array(
			'key'		=> 'text_store_card',
			'type'		=> 'multilingual_text',
			'default'	=> 'Store Card for Future Use:',
		);
		$data['settings'][] = array(
			'key'		=> 'text_please_wait',
			'type'		=> 'multilingual_text',
			'default'	=> 'Please wait...',
		);
		
		// Square Error Codes
		$data['settings'][] = array(
			'key'		=> 'square_error_codes',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center pad-bottom-sm">' . $data['help_square_error_codes'] . '</div>',
		);
		$square_errors = array(
			'cardNumber',
			'expirationDate',
			'postalCode',
			'cvv',
			'missing_card_data',
			'unknown',
			'verify_cvv_failure',
			'verify_avs_failure',
			'invalid_expiration_year',
			'invalid_expiration_date',
			'invalid_card',
		);
		foreach ($square_errors as $square_error) {
			$data['settings'][] = array(
				'key'		=> 'error_' . $square_error,
				'type'		=> 'multilingual_text',
				'class'		=> 'long',
			);
		}
		
		//------------------------------------------------------------------------------
		// Order Statuses
		//------------------------------------------------------------------------------
		$data['settings'][] = array(
			'key'		=> 'order_statuses',
			'type'		=> 'tab',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center pad-bottom-sm">' . $data['help_order_statuses'] . '</div>',
		);
		$data['settings'][] = array(
			'key'		=> 'order_statuses',
			'type'		=> 'heading',
		);
		
		$processing_status_id = $this->config->get('config_processing_status');
		$processing_status_id = $processing_status_id[0];
		
		$data['settings'][] = array(
			'key'		=> 'success_status_id',
			'type'		=> 'select',
			'options'	=> $data['order_status_array'],
			'default'	=> $processing_status_id,
		);
		$data['settings'][] = array(
			'key'		=> 'authorize_status_id',
			'type'		=> 'select',
			'options'	=> $data['order_status_array'],
			'default'	=> $processing_status_id,
		);
		
		foreach (array('error', 'refund', 'partial') as $order_status) {
			$default_status = ($order_status == 'error') ? 10 : 0;
			$data['settings'][] = array(
				'key'		=> $order_status . '_status_id',
				'type'		=> 'select',
				'options'	=> $data['order_status_array'],
				'default'	=> $default_status,
			);
		}
		
		//------------------------------------------------------------------------------
		// Restrictions
		//------------------------------------------------------------------------------
		$data['settings'][] = array(
			'key'		=> 'restrictions',
			'type'		=> 'tab',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center pad-bottom-sm">' . $data['help_restrictions'] . '</div>',
		);
		$data['settings'][] = array(
			'key'		=> 'restrictions',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'key'		=> 'min_total',
			'type'		=> 'text',
			'attributes'=> array('style' => 'width: 50px !important'),
			'default'	=> '0.50',
		);
		$data['settings'][] = array(
			'key'		=> 'max_total',
			'type'		=> 'text',
			'attributes'=> array('style' => 'width: 50px !important'),
		);
		$data['settings'][] = array(
			'key'		=> 'stores',
			'type'		=> 'checkboxes',
			'options'	=> $data['store_array'],
			'default'	=> array_keys($data['store_array']),
		);
		$data['settings'][] = array(
			'key'		=> 'geo_zones',
			'type'		=> 'checkboxes',
			'options'	=> $data['geo_zone_array'],
			'default'	=> array_keys($data['geo_zone_array']),
		);
		$data['settings'][] = array(
			'key'		=> 'customer_groups',
			'type'		=> 'checkboxes',
			'options'	=> $data['customer_group_array'],
			'default'	=> array_keys($data['customer_group_array']),
		);
		
		// Currency Settings
		$data['settings'][] = array(
			'key'		=> 'currency_settings',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center pad-bottom">' . $data['help_currency_settings'] . '</div>',
		);
		foreach ($data['currency_array'] as $code => $title) {
			$data['settings'][] = array(
				'key'		=> 'currencies_' . $code,
				'title'		=> str_replace('[currency]', $code, $data['entry_currencies']),
				'type'		=> 'select',
				'options'	=> array_merge(array(0 => $data['text_currency_disabled']), $data['currency_array']),
				'default'	=> $this->config->get('config_currency'),
			);
		}
		
		//------------------------------------------------------------------------------
		// Square Settings
		//------------------------------------------------------------------------------
		$data['settings'][] = array(
			'key'		=> 'square_settings',
			'type'		=> 'tab',
		);
		$data['settings'][] = array(
			'type'		=> 'html',
			'content'	=> '<div class="text-info text-center pad-bottom-sm">' . $data['help_square_settings'] . '</div>',
		);
		$data['settings'][] = array(
			'key'		=> 'square_settings',
			'type'		=> 'heading',
		);
		$data['settings'][] = array(
			'key'		=> 'sandbox_application_id',
			'type'		=> 'text',
			'attributes'=> array('onchange' => '$(this).val($(this).val().trim())', 'style' => 'width: 300px !important'),
		);
		$data['settings'][] = array(
			'key'		=> 'sandbox_access_token',
			'type'		=> 'text',
			'attributes'=> array('onchange' => '$(this).val($(this).val().trim())', 'style' => 'width: 300px !important'),
		);
		
		if (empty($data['saved']['sandbox_access_token'])) {
			$location_options = array('' => '(Reload the page after entering your Access Token)');
		} else {
			$location_options = array();
			$response = $this->curlRequest('GET', 'locations', 'sandbox');
			if (!empty($response['error'])) {
				echo '<div class="alert alert-danger">' . $response['error'] . '</div>';
			} else {
				foreach ($response['locations'] as $location) {
					$location_options[$location['id']] = $location['name'];
				}
			}
		}
		$data['settings'][] = array(
			'key'		=> 'sandbox_location_id',
			'type'		=> 'select',
			'options'	=> $location_options,
		);
		
		$data['settings'][] = array(
			'key'		=> 'live_application_id',
			'type'		=> 'text',
			'attributes'=> array('onchange' => '$(this).val($(this).val().trim())', 'style' => 'width: 300px !important'),
		);
		$data['settings'][] = array(
			'key'		=> 'live_access_token',
			'type'		=> 'text',
			'attributes'=> array('onchange' => '$(this).val($(this).val().trim())', 'style' => 'width: 300px !important'),
		);
		
		if (empty($data['saved']['live_access_token'])) {
			$location_options = array('' => '(Reload the page after entering your Access Token)');
		} else {
			$location_options = array();
			$response = $this->curlRequest('GET', 'locations', 'live');
			if (!empty($response['error'])) {
				echo '<div class="alert alert-danger">' . $response['error'] . '</div>';
			} else {
				foreach ($response['locations'] as $location) {
					$location_options[$location['id']] = $location['name'];
				}
			}
		}
		$data['settings'][] = array(
			'key'		=> 'live_location_id',
			'type'		=> 'select',
			'options'	=> $location_options,
		);
		
		$data['settings'][] = array(
			'key'		=> 'transaction_mode',
			'type'		=> 'select',
			'options'	=> array('sandbox' => $data['text_sandbox'], 'live' => $data['text_live']),
		);
		$data['settings'][] = array(
			'key'		=> 'charge_mode',
			'type'		=> 'select',
			'options'	=> array('authorize' => $data['text_authorize'], 'capture' => $data['text_capture'], 'fraud' => $data['text_fraud_authorize']),
			'default'	=> 'capture',
		);
		$data['settings'][] = array(
			'key'		=> 'allow_stored_cards',
			'type'		=> 'select',
			'options'	=> array(1 => $data['text_yes'], 0 => $data['text_no']),
			'default'	=> 0,
		);
		
		//------------------------------------------------------------------------------
		// end settings
		//------------------------------------------------------------------------------
		
		$this->document->setTitle($data['heading_title']);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$template_file = DIR_TEMPLATE . 'extension/' . $this->type . '/' . $this->name . '.twig';
		
		if (is_file($template_file)) {
			extract($data);
			
			ob_start();
			require(class_exists('VQMod') ? VQMod::modCheck(modification($template_file)) : modification($template_file));
			$output = ob_get_clean();
			
			if (version_compare(VERSION, '3.0', '>=')) {
				$output = str_replace('&token=', '&user_token=', $output);
			}
			
			echo $output;
		} else {
			echo 'Error loading template file';
		}
	}
	
	//==============================================================================
	// Helper functions
	//==============================================================================
	private function hasPermission($permission) {
		return ($this->user->hasPermission($permission, $this->type . '/' . $this->name) || $this->user->hasPermission($permission, 'extension/' . $this->type . '/' . $this->name));
	}
	
	private function loadLanguage($path) {
		$_ = array();
		$language = array();
		$admin_language = (version_compare(VERSION, '2.2', '<')) ? $this->db->query("SELECT * FROM " . DB_PREFIX . "language WHERE `code` = '" . $this->db->escape($this->config->get('config_admin_language')) . "'")->row['directory'] : $this->config->get('config_admin_language');
		foreach (array('english', 'en-gb', $admin_language) as $directory) {
			$file = DIR_LANGUAGE . $directory . '/' . $directory . '.php';
			if (file_exists($file)) require($file);
			$file = DIR_LANGUAGE . $directory . '/default.php';
			if (file_exists($file)) require($file);
			$file = DIR_LANGUAGE . $directory . '/' . $path . '.php';
			if (file_exists($file)) require($file);
			$file = DIR_LANGUAGE . $directory . '/extension/' . $path . '.php';
			if (file_exists($file)) require($file);
			$language = array_merge($language, $_);
		}
		return $language;
	}
	
	private function getTableRowNumbers(&$data, $table, $sorting) {
		$groups = array();
		$rules = array();
		
		foreach ($data['saved'] as $key => $setting) {
			if (preg_match('/' . $table . '_(\d+)_' . $sorting . '/', $key, $matches)) {
				$groups[$setting][] = $matches[1];
			}
			if (preg_match('/' . $table . '_(\d+)_rule_(\d+)_type/', $key, $matches)) {
				$rules[$matches[1]][] = $matches[2];
			}
		}
		
		if (empty($groups)) $groups = array('' => array('1'));
		ksort($groups, defined('SORT_NATURAL') ? SORT_NATURAL : SORT_REGULAR);
		
		foreach ($rules as $key => $rule) {
			ksort($rules[$key], defined('SORT_NATURAL') ? SORT_NATURAL : SORT_REGULAR);
		}
		
		$data['used_rows'][$table] = array();
		$rows = array();
		foreach ($groups as $group) {
			foreach ($group as $num) {
				$data['used_rows'][preg_replace('/module_(\d+)_/', '', $table)][] = $num;
				$rows[$num] = (empty($rules[$num])) ? array() : $rules[$num];
			}
		}
		sort($data['used_rows'][$table]);
		
		return $rows;
	}
	
	//==============================================================================
	// Setting functions
	//==============================================================================
	private $encryption_key = '';
	
	public function loadSettings(&$data) {
		$backup_type = (empty($data)) ? 'manual' : 'auto';
		if ($backup_type == 'manual' && !$this->hasPermission('modify')) {
			return;
		}
		
		$this->cache->delete($this->name);
		unset($this->session->data[$this->name]);
		$code = (version_compare(VERSION, '3.0', '<') ? '' : $this->type . '_') . $this->name;
		
		// Set exit URL
		$data['token'] = $this->session->data[version_compare(VERSION, '3.0', '<') ? 'token' : 'user_token'];
		$data['exit'] = $this->url->link((version_compare(VERSION, '3.0', '<') ? 'extension' : 'marketplace') . '/' . (version_compare(VERSION, '2.3', '<') ? '' : 'extension&type=') . $this->type . '&token=' . $data['token'], '', 'SSL');
		
		// Load saved settings
		$data['saved'] = array();
		$settings_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `code` = '" . $this->db->escape($code) . "' ORDER BY `key` ASC");
		
		foreach ($settings_query->rows as $setting) {
			$key = str_replace($code . '_', '', $setting['key']);
			$value = $setting['value'];
			if ($setting['serialized']) {
				$value = (version_compare(VERSION, '2.1', '<')) ? unserialize($setting['value']) : json_decode($setting['value'], true);
			}
			
			$data['saved'][$key] = $value;
			
			if (is_array($value)) {
				foreach ($value as $num => $value_array) {
					foreach ($value_array as $k => $v) {
						$data['saved'][$key . '_' . $num . '_' . $k] = $v;
					}
				}
			}
		}
		
		// Load language and run standard checks
		$data = array_merge($data, $this->loadLanguage($this->type . '/' . $this->name));
		
		if (ini_get('max_input_vars') && ((ini_get('max_input_vars') - count($data['saved'])) < 50)) {
			$data['warning'] = $data['standard_max_input_vars'];
		}
		
		// Modify files according to OpenCart version
		if ($this->type == 'total' && version_compare(VERSION, '2.2', '<')) {
			file_put_contents(DIR_CATALOG . 'model/' . $this->type . '/' . $this->name . '.php', str_replace('public function getTotal($total) {', 'public function getTotal(&$total_data, &$order_total, &$taxes) {' . "\n\t\t" . '$total = array("totals" => &$total_data, "total" => &$order_total, "taxes" => &$taxes);', file_get_contents(DIR_CATALOG . 'model/' . $this->type . '/' . $this->name . '.php')));
		}
		
		if (version_compare(VERSION, '2.3', '>=')) {
			$filepaths = array(
				DIR_APPLICATION . 'controller/' . $this->type . '/' . $this->name . '.php',
				DIR_CATALOG . 'controller/' . $this->type . '/' . $this->name . '.php',
				DIR_CATALOG . 'model/' . $this->type . '/' . $this->name . '.php',
			);
			foreach ($filepaths as $filepath) {
				if (file_exists($filepath)) {
					rename($filepath, str_replace('.php', '.php-OLD', $filepath));
				}
			}
		}
		
		// Set save type and skip auto-backup if not needed
		if (!empty($data['saved']['autosave'])) {
			$data['save_type'] = 'auto';
		}
		
		if ($backup_type == 'auto' && empty($data['autobackup'])) {
			return;
		}
		
		// Create settings auto-backup file
		$manual_filepath = DIR_LOGS . $this->name . $this->encryption_key . '.backup';
		$auto_filepath = DIR_LOGS . $this->name . $this->encryption_key . '.autobackup';
		$filepath = ($backup_type == 'auto') ? $auto_filepath : $manual_filepath;
		if (file_exists($filepath)) unlink($filepath);
		
		file_put_contents($filepath, 'SETTING	NUMBER	SUB-SETTING	SUB-NUMBER	SUB-SUB-SETTING	VALUE' . "\n", FILE_APPEND|LOCK_EX);
		
		foreach ($data['saved'] as $key => $value) {
			if (is_array($value)) continue;
			
			$parts = explode('|', preg_replace(array('/_(\d+)_/', '/_(\d+)/'), array('|$1|', '|$1'), $key));
			
			$line = '';
			for ($i = 0; $i < 5; $i++) {
				$line .= (isset($parts[$i]) ? $parts[$i] : '') . "\t";
			}
			$line .= str_replace(array("\t", "\n"), array('    ', '\n'), $value) . "\n";
			
			file_put_contents($filepath, $line, FILE_APPEND|LOCK_EX);
		}
		
		$data['autobackup_time'] = date('Y-M-d @ g:i a');
		$data['backup_time'] = (file_exists($manual_filepath)) ? date('Y-M-d @ g:i a', filemtime($manual_filepath)) : '';
		
		if ($backup_type == 'manual') {
			echo $data['autobackup_time'];
		}
	}
	
	public function saveSettings() {
		if (!$this->hasPermission('modify')) {
			echo 'PermissionError';
			return;
		}
		
		$this->cache->delete($this->name);
		unset($this->session->data[$this->name]);
		$code = (version_compare(VERSION, '3.0', '<') ? '' : $this->type . '_') . $this->name;
		
		if ($this->request->get['saving'] == 'manual') {
			$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `code` = '" . $this->db->escape($code) . "' AND `key` != '" . $this->db->escape($this->name . '_module') . "'");
		}
		
		$module_id = 0;
		$modules = array();
		$module_instance = false;
		
		foreach ($this->request->post as $key => $value) {
			if (strpos($key, 'module_') === 0) {
				$parts = explode('_', $key, 3);
				$module_id = $parts[1];
				$modules[$parts[1]][$parts[2]] = $value;
				if ($parts[2] == 'module_id') $module_instance = true;
			} else {
				$key = (version_compare(VERSION, '3.0', '<') ? '' : $this->type . '_') . $this->name . '_' . $key;
				
				if ($this->request->get['saving'] == 'auto') {
					$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "'");
				}
				
				$this->db->query("
					INSERT INTO " . DB_PREFIX . "setting SET
					`store_id` = 0,
					`code` = '" . $this->db->escape($code) . "',
					`key` = '" . $this->db->escape($key) . "',
					`value` = '" . $this->db->escape(stripslashes(is_array($value) ? implode(';', $value) : $value)) . "',
					`serialized` = 0
				");
			}
		}
		
		foreach ($modules as $module_id => $module) {
			if (!$module_id) {
				$this->db->query("
					INSERT INTO " . DB_PREFIX . "module SET
					`name` = '" . $this->db->escape($module['name']) . "',
					`code` = '" . $this->db->escape($this->name) . "',
					`setting` = ''
				");
				$module_id = $this->db->getLastId();
				$module['module_id'] = $module_id;
			}
			$module_settings = (version_compare(VERSION, '2.1', '<')) ? serialize($module) : json_encode($module);
			$this->db->query("
				UPDATE " . DB_PREFIX . "module SET
				`name` = '" . $this->db->escape($module['name']) . "',
				`code` = '" . $this->db->escape($this->name) . "',
				`setting` = '" . $this->db->escape($module_settings) . "'
				WHERE module_id = " . (int)$module_id . "
			");
		}
	}
	
	public function deleteSetting() {
		if (!$this->hasPermission('modify')) {
			echo 'PermissionError';
			return;
		}
		$prefix = (version_compare(VERSION, '3.0', '<')) ? '' : $this->type . '_';
		$this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE `code` = '" . $this->db->escape($prefix . $this->name) . "' AND `key` = '" . $this->db->escape($prefix . $this->name . '_' . str_replace('[]', '', $this->request->get['setting'])) . "'");
	}
	
	//==============================================================================
	// capture()
	//==============================================================================
	public function capture() {
		$capture_response = $this->curlRequest('POST', 'transactions/' . $this->request->get['transaction_id'] . '/capture');
		if (!empty($capture_response['error'])) {
			echo 'Error: ' . $capture_response['error'];
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "order_history SET `comment` = REPLACE(`comment`, '<span>No &nbsp;</span> <a style=\"cursor: pointer\" onclick=\"capture($(this), \'" . $this->db->escape($this->request->get['transaction_id']) . "\')\">(Capture)</a>', 'Yes') WHERE `comment` LIKE '%capture($(this), \'" . $this->db->escape($this->request->get['transaction_id']) . "\')%'");
		}
	}
	
	//==============================================================================
	// refund()
	//==============================================================================
	public function refund() {
		$data = array(
			'idempotency_key'	=> uniqid(),
			'tender_id'			=> $this->request->get['tender_id'],
			'amount_money'		=> array(
				'amount' 	=> (int)($this->request->get['amount'] * 100),
				'currency'	=> $this->request->get['currency'],
			)
		);
		
		$refund_response = $this->curlRequest('POST', 'transactions/' . $this->request->get['transaction_id'] . '/refund', $data);
		
		if (!empty($refund_response['error'])) {
			echo 'Error: ' . $refund_response['error'];
			return;
		}
		
		$order_id = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_history WHERE `comment` LIKE '%" . $this->db->escape($this->request->get['transaction_id']) . "%'")->row['order_id'];
		$order_info = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = " . (int)$order_id)->row;
		
		$prefix = (version_compare(VERSION, '3.0', '<')) ? '' : $this->type . '_';
		
		if ($this->request->get['amount'] < $order_info['total']) {
			$order_status_id = $this->config->get($prefix . $this->name . '_partial_status_id');
		} else {
			$order_status_id = $this->config->get($prefix . $this->name . '_refund_status_id');
		}
		
		if (empty($order_status_id)) {
			$order_status_id = $order_info['order_status_id'];
		}
		
		$strong = '<strong style="display: inline-block; width: 135px; padding: 2px 5px">';
		$comment = $strong . 'Refund ID:</strong> ' . $refund_response['refund']['id'] . '<br />' . $strong . 'Refunded Amount:</strong> ' . $this->currency->format($this->request->get['amount'], $this->request->get['currency']);
		
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = " . (int)$order_status_id . ", date_modified = NOW() WHERE order_id = " . (int)$order_id);
		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = " . (int)$order_id . ", order_status_id = " . (int)$order_status_id . ", notify = 0, `comment` = '" . $this->db->escape($comment) . "', date_added = NOW()");
	}
	
	//==============================================================================
	// curlRequest()
	//==============================================================================
	private function curlRequest($request = 'GET', $api = '', $data = array()) {
		$prefix = (version_compare(VERSION, '3.0', '<')) ? '' : $this->type . '_';
		
		if ($api == 'locations') {
			$transaction_mode = $data;
			$data = array();
		} else {
			$transaction_mode = $this->config->get($prefix . $this->name . '_transaction_mode');
		}
		
		$access_token = $this->config->get($prefix . $this->name . '_' . $transaction_mode . '_access_token');
		$location_id = $this->config->get($prefix . $this->name . '_' . $transaction_mode . '_location_id');
		
		$url = 'https://connect.squareup.com/v2/';
		$url .= (strpos($api, 'customers') === 0 || strpos($api, 'locations') === 0) ? $api : 'locations/' . $location_id . '/' . $api;
		
		if ($request == 'GET') {
			$curl = curl_init($url . '?' . http_build_query($data));
		} else {
			$curl = curl_init($url);
			if ($request != 'DELETE' && $data) {
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
			}
			if ($request == 'POST') {
				curl_setopt($curl, CURLOPT_POST, true);
			} else {
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request);
			}
		}
		
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , 'Authorization: Bearer ' . $access_token));
		
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		
		$response = json_decode(curl_exec($curl), true);
		
		if (curl_error($curl)) {
			$response['error'] = 'CURL ERROR: ' . curl_errno($curl) . '::' . curl_error($curl);
		}
		curl_close($curl);
		
		if (!empty($response['errors'])) {
			$response['error'] = array();
			foreach ($response['errors'] as $error) {
				$response['error'][] = $error['detail'];
			}
			$response['error'] = implode('<br />', $response['error']);
		}
		
		return $response;
	}
}
?>