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

class ModelExtensionPaymentSquare extends Model {		
	private $type = 'payment';
	private $name = 'square';
	
	//==============================================================================
	// getMethod()
	//==============================================================================
	public function getMethod($address, $total = 0) {
		$settings = $this->getSettings();
		
		$current_geozones = array();
		$geozones = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '0' OR zone_id = '" . (int)$address['zone_id'] . "')");
		foreach ($geozones->rows as $geozone) $current_geozones[] = $geozone['geo_zone_id'];
		if (empty($current_geozones)) $current_geozones = array(0);
		
		$language = (isset($this->session->data['language'])) ? $this->session->data['language'] : $this->config->get('config_language');
		
		if (!$settings['status'] ||
			($settings['min_total'] && (float)$settings['min_total'] > $total) ||
			($settings['max_total'] && (float)$settings['max_total'] < $total) ||
			!array_intersect(array($this->config->get('config_store_id')), explode(';', $settings['stores'])) ||
			!array_intersect($current_geozones, explode(';', $settings['geo_zones'])) ||
			!array_intersect(array((int)$this->customer->getGroupId()), explode(';', $settings['customer_groups'])) ||
			empty($settings['currencies_' . $this->session->data['currency']])
		) {
			return array();
		} else {
			return array(
				'code'			=> $this->name,
				'sort_order'	=> $settings['sort_order'],
				'terms'			=> '',
				'title'			=> html_entity_decode($settings['title_' . $language], ENT_QUOTES, 'UTF-8'),
			);
		}
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
}
?>