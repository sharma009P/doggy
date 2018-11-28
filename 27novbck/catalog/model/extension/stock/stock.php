<?php
class ModelExtensionStockStock extends Model {

	public function isStockCombinationAvailable($product_id, $selected_options, $quantity = 1) {
		$combination = $this->getStockCombination($product_id, $selected_options);
		return ( !empty($combination) && ($combination['quantity'] >= $quantity) );
	}

	protected function getStockCombination($product_id, $selected_options) {
		$query = $this->db->query("SELECT s.combination_id, s.quantity, so.product_option_value_id "
				. " FROM `" . DB_PREFIX . "stock` s "
				. " INNER JOIN `" . DB_PREFIX . "stock_option` so ON (s.combination_id = so.combination_id) " 
				. " WHERE s.product_id = '" . (int)$product_id . "' ORDER BY s.combination_id, so.product_option_value_id ");

		$result = NULL;
		
		$combinations = array();
		$combination_id = 0;
		foreach($query->rows as $row) {
			if ($row['combination_id'] != $combination_id) {
				$combination_id = $row['combination_id'];
				$combinations[$combination_id] = array();
				$combinations[$combination_id]['combination_id'] = $combination_id;
				$combinations[$combination_id]['quantity'] = $row['quantity'];
				$combinations[$combination_id]['option_values'] = array();
			}
			$combinations[$combination_id]['option_values'][] = $row['product_option_value_id'];
		}
		
		//$this->log->write('Combinations: ' . var_export($combinations, true));
		//$this->log->write('Product Option values: ' . var_export($selected_options, true));
		
		$selected_options_length = count($selected_options);
		
		foreach($combinations as $key => $combination) {
			$option_values = $combination['option_values'];
			if (count($option_values) == $selected_options_length) {
				//$this->log->write('Checking Combination option values: ' . var_export($option_values, true));
				$combination_found = TRUE;
				foreach($selected_options as $selected_option) {
					if (!in_array($selected_option, $option_values)) {
						$combination_found = FALSE;
						//$this->log->write('selected_option not found: ' . var_export($selected_option, true));
						break;	
					} 
					//else {
					//	$this->log->write('selected_option found: ' . var_export($selected_option, true));
					//}
				}
				
				if ($combination_found) {
					//$this->log->write('Combination found');
					//if ($combination['quantity'] > 0) {
						$result = $combination;
					//}
					break;
				}
			}
		}
		
		return $result;
	}

	public function chargeOrderOptionsStock($order_id, $order_products, $reclaim=false) {
		//$this->log->write('order products: ' . var_export($order_products, true));
		foreach ($order_products as $order_product) {
			$stock_enabled_options = array();
		
			$order_option_query = $this->db->query("SELECT oo.*, po.stock_enabled FROM " . DB_PREFIX . "order_option oo INNER JOIN `" . DB_PREFIX .  "product_option` po ON (oo.product_option_id = po.product_option_id) WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product['order_product_id'] . "'");
			foreach ($order_option_query->rows as $option) {
				if ($option['stock_enabled'] == 1) {
					$stock_enabled_options[] = $option['product_option_value_id'];
				}
			}
			
			if (!empty($stock_enabled_options)) {
				$this->updateProductStock($order_product['product_id'], $stock_enabled_options, $order_product['quantity'], $reclaim);
			}
    }
	}

	protected function updateProductStock($product_id, $selected_options, $quantity, $reclaim=false) {
		//$this->log->write('Checking selected options: ' . var_export($selected_options, true) . ' Quantity: ' . $quantity . ' Product: ' . $product_id);
		$combination = $this->getStockCombination($product_id, $selected_options);
		//$this->log->write('Combination found: ' . var_export($combination, true) );
		if (!empty($combination)) {
			$id = $combination['combination_id'];
			$sign = ($reclaim ? ' + ' : ' - ');
			$this->db->query("UPDATE `" . DB_PREFIX . "stock` s SET quantity = (quantity " . $sign . (int) $quantity . ") WHERE s.combination_id = '" . (int)$id . "'");
			
			$this->updateProductQuantitiesFromStock($product_id);
		}	
	}
	
	protected function updateProductQuantitiesFromStock($product_id) {
		// update quantities for product's options
		$this->db->query("UPDATE `" . DB_PREFIX . "product_option_value` pov "
			. " INNER JOIN `" . DB_PREFIX . "product_option` po ON (pov.product_option_id = po.product_option_id) "
			. " LEFT JOIN ("
			. " 	SELECT so.product_option_value_id, sum(s.quantity) as quantity "
			. " 	FROM `" . DB_PREFIX . "stock` s "
			. "		INNER JOIN `" . DB_PREFIX . "stock_option` so on (s.combination_id = so.combination_id) "
			. " 	WHERE s.product_id = '" . (int)$product_id . "'"
			. "		GROUP BY so.product_option_value_id "
			. ") sq on sq.product_option_value_id = pov.product_option_value_id "
			. " SET pov.subtract = 1, "
			. " pov.quantity = IFNULL(sq.quantity, 0) "
			. " WHERE po.product_id = '" . (int)$product_id . "' AND po.stock_enabled = 1");
		
		// update product's total quantity
		$this->db->query("UPDATE `"  . DB_PREFIX . "product` SET subtract = 1, quantity = (SELECT sum(s.quantity) FROM `"  . DB_PREFIX . "stock` s WHERE s.product_id = '" . (int) $product_id . "') WHERE product_id = '" . (int) $product_id . "'");				
	}


	/**
	 * Returns the name of a combination in the configured admin language using the separator to concatenate 
	 * each option's value e.g. if we have options for Color and Size a specific combination could be 'Red-Small'
	 */
	public function getCombinationName($option_values, $separator = '-') {
		$option_values_ids = implode(',', $option_values);
		$option_names = $this->getProductOptionValueNames($option_values_ids);
					
		$option_names_only = array();
		foreach($option_names as $option_name) {
			$option_names_only[] = $option_name['name'];
		}
		$combination_label = implode('-', $option_names_only);
		return $combination_label;
	}

	protected function getProductOptionValueNames($options_ids) {
		$query = $this->db->query("SELECT ovd.name FROM `" . DB_PREFIX . "product_option_value` pov INNER JOIN `" . DB_PREFIX . "option_value` ov ON (pov.option_value_id = ov.option_value_id) INNER JOIN `" . DB_PREFIX . "option` o ON (ov.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id in (" . $options_ids . ") AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");
		return $query->rows;		
	}

}
?>
