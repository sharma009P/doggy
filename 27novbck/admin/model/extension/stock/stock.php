<?php
class ModelExtensionStockStock extends Model {

	public function getStockEnabledProduct($product_id) {
		$query = $this->db->query("SELECT DISTINCT p.*, pd.name FROM " . DB_PREFIX . "product p INNER JOIN `" . DB_PREFIX . "product_option` po ON (p.product_id = po.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND po.stock_enabled = 1 AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		return $query->row;
	}

	public function getProductStockCombinations($product_id) {
		$product_stock = array();
		
		$query = $this->db->query("SELECT s.*, pov.product_option_id, so.product_option_value_id "
				. " FROM `" . DB_PREFIX . "stock` s "
				. " INNER JOIN `" . DB_PREFIX . "stock_option` so ON (s.combination_id = so.combination_id) " 
				. " INNER JOIN `" . DB_PREFIX . "product_option_value` pov ON (so.product_option_value_id = pov.product_option_value_id) " 
				. " WHERE s.product_id = '" . (int)$product_id . "' ORDER BY s.combination_id, pov.product_option_id, so.product_option_value_id ");
		
		$combination = NULL;
		$combination_id = 0;
		$option_values = NULL;
		foreach ($query->rows as $row) {
			if ($row['combination_id'] != $combination_id) {
				$combination_id = $row['combination_id'];
				$combination = array(
						'combination_id'					=> $row['combination_id'],
						'product_id'						=> $row['product_id'],
						'quantity'							=> $row['quantity'],
						'sku'								=> $row['sku']
				);	
				$combination['product_option_values'] = array();
				$option_values = &$combination['product_option_values']; // get reference
				$product_stock[] = $combination;
			}
			
			$option_values[$row['product_option_id']] = $row['product_option_value_id'];
		}		
		
		return $product_stock;
	}

	public function isProductStockEnabled($product_id) {
		$sql = $this->db->query("SELECT count(*) as stock_enabled FROM `" . DB_PREFIX . "product` p INNER JOIN `"  . DB_PREFIX . "product_option` po ON (p.product_id = po.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND po.stock_enabled = 1");
		$stock_enabled_options = (int) $sql->row['stock_enabled'];
		return ($stock_enabled_options >= 1);
	}
	
	public function isStockValid($product_id) {
		return ( $this->hasCombinations($product_id) && $this->hasValidCombinations($product_id) && ( ! $this->hasDuplicateCombinations($product_id) ) );	
	}

	public function getStockStatus($product_id) {
		if (! $this->hasCombinations($product_id) ) {
			return 1;
		}

		if (! $this->hasValidCombinations($product_id) ) {
			return 2;
		}

		if ( $this->hasDuplicateCombinations($product_id) ) {
			return 3;
		}
		
		return 0;	
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

	
	public function getDuplicateCombinationNames($combinations, $translations = TRUE, $firstOnly = TRUE, $separator = '-') {
		$combinations_used = array();
		$double_combination_names = array();
		foreach ($combinations as $combination) {
			$option_values = $combination['product_option_values'];
			array_multisort($option_values, SORT_NUMERIC);
			$key = implode('-', $option_values);
			if (isset($combinations_used[$key])) {
					
				// combination is entered twice
				$combination_label = '1';
				if ($translations) {
					$combination_label = $this->getCombinationName($option_values, $separator);
				}
				
				$double_combination_names[] = $combination_label;
				if ($firstOnly) {
					break;
				}
			}	else {
				$combinations_used[$key] = $key;
			}
		}
		
		return $double_combination_names;
	}

	public function getProductOptionIds($product_id) {
		$options = array();
		
		$product_option_query = $this->db->query("SELECT po.product_option_id, pov.product_option_value_id " 
				. " FROM `" . DB_PREFIX . "product_option` po "
				. " INNER JOIN `" . DB_PREFIX . "product_option_value` pov ON (po.product_option_id = pov.product_option_id) "
				. " WHERE po.product_id = '" . (int)$product_id . "' AND po.stock_enabled = 1 ORDER BY po.product_option_id, pov.product_option_value_id");
		
		$product_option = NULL;
		$product_option_id = 0;
		$product_option_values = NULL;
		foreach ($product_option_query->rows as $row) {
			if ($row['product_option_id'] != $product_option_id) {
				$product_option_id = $row['product_option_id'];
				$options[$product_option_id] = array();
				$option_values = &$options[$product_option_id]; // by reference
			}
			$option_values[] = $row['product_option_value_id'];
		}
		return $options;
	}
	
	
	
	private function combinationsQueryWhereClause($sql, $data) {
		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}
		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}
		if (!empty($data['filter_quantity'])) {
			$sql .= " AND p.quantity <= '" . $this->db->escape($data['filter_quantity']) . "'";
		}
		if (!empty($data['filter_option'])) {
			$sql .= " AND ovd.name LIKE '" . $this->db->escape($data['filter_option']) . "%'";
		}
		if (!empty($data['filter_comb_quantity'])) {
			$sql .= " AND s.quantity <= '" . $this->db->escape($data['filter_comb_quantity']) . "'";
		}

		
		return $sql;
	}
	
	private function combinationsQueryOrderClause($sql, $data, $add_limit = TRUE) {
		$sort_data = array(
			'pd.name',
			'p.model',
			'p.quantity'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY pd.name";	
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		
		if (isset($data['sort'])) {
			$sql .= " ,s.product_id, s.combination_id, so.product_option_value_id";
		} else {
			$sql .= "ORDER BY s.product_id, s.combination_id, so.product_option_value_id";
		}
		
		if ($add_limit) {
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}				
	
				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}	
				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}
		}
		return $sql;
	}
	
	/**
	 * Used by the Stock combinations list page to decide total items for paging purposes. 
	 * @param $data: 
	 *        filtering options to take into account
	 * @return 
	 *        an int value for the total number of combinations found
	 */
	public function getTotalCombinations($data = array()) {
		$lang_id = (int)$this->config->get('config_language_id');
		
		$sql_ids = "SELECT count(distinct s.combination_id) as total FROM `" . DB_PREFIX . "stock` s "
			." INNER JOIN `" . DB_PREFIX . "product` p ON (p.product_id = s.product_id) "
			." LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id) "
			." INNER JOIN `" . DB_PREFIX . "stock_option` so ON (so.combination_id = s.combination_id) "
			." INNER JOIN `" . DB_PREFIX . "product_option_value` pov ON (pov.product_option_value_id = so.product_option_value_id) "
			." LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ovd.option_value_id = pov.option_value_id) "
			." WHERE pd.language_id = '" . (int) $lang_id . "' "
			." AND ovd.language_id = '" . (int) $lang_id . "' ";
		
		$sql_ids = $this->combinationsQueryWhereClause($sql_ids, $data);
		
		$query = $this->db->query($sql_ids);
		return $query->row['total'];
	}

	/**
	 * Used by the Stock combinations list page to fetch the combinations for current page. 
	 * @param $data: 
	 *        filtering, sorting and paging options to take into account
	 * @return 
	 *        an array with entries of type: { 
	 *              'product_id', 'model', 'name', 'quantity'
     *              'combinations' => { 
     *                      'combination_id', 'quantity', 'sku', 
     *                      'option_value_names' => {raw strings for option value names for each combination e.g. ['Red', 'Small']} 
     *              },
     *              'options' => {raw strings for option names e.g. ['Color', 'Size']}
     *        }
	 */
	public function getCombinations($data = array()) {
		$products = array();
		$lang_id = (int)$this->config->get('config_language_id');
		
		// get the ids of the combinations that we need to show that satisfy filtering and paging criteria
		$sql_ids = "SELECT s.combination_id FROM `" . DB_PREFIX . "stock` s "
			." INNER JOIN `" . DB_PREFIX . "product` p ON (p.product_id = s.product_id) "
			." LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id) "
			." INNER JOIN `" . DB_PREFIX . "stock_option` so ON (so.combination_id = s.combination_id) "
			." INNER JOIN `" . DB_PREFIX . "product_option_value` pov ON (pov.product_option_value_id = so.product_option_value_id) "
			." LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ovd.option_value_id = pov.option_value_id) "
			." WHERE pd.language_id = '" . (int) $lang_id . "' "
			." AND ovd.language_id = '" . (int) $lang_id . "' ";
		
		$sql_ids = $this->combinationsQueryWhereClause($sql_ids, $data);
		$sql_ids .= " GROUP BY s.combination_id ";
		$sql_ids = $this->combinationsQueryOrderClause($sql_ids, $data);
		$id_query = $this->db->query($sql_ids);
		$ids = array();
		foreach ($id_query->rows as $row) {
			$ids[] = $row['combination_id'];
		}
		
		if (empty($ids)) {
			return $ids;	
		}
		
		// now get the information of the selected ids
		$sql = "SELECT s.product_id, p.model, p.quantity as product_quantity, s.combination_id, s.quantity, s.sku, pd.name as product_name, so.product_option_value_id, ovd.name as option_value_name, pov.product_option_id, od.name as option_name "
			. "FROM `" . DB_PREFIX . "stock` s "
			. "LEFT JOIN `" . DB_PREFIX . "product_description` pd on  s.product_id = pd.product_id "
			. "INNER JOIN `" . DB_PREFIX . "product` p on p.product_id = pd.product_id "
			. "INNER JOIN `" . DB_PREFIX . "stock_option` so on so.combination_id = s.combination_id "
			. "INNER JOIN `" . DB_PREFIX . "product_option_value` pov on pov.product_option_value_id = so.product_option_value_id "
			. "LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd on ovd.option_value_id = pov.option_value_id "
			. "LEFT JOIN `" . DB_PREFIX . "option_description` od on od.option_id = pov.option_id "
			. "WHERE pd.language_id = '" . $lang_id . "' "
			. "AND s.combination_id IN (" . implode(',', $ids) . ") "
			. "AND ovd.language_id = '" . $lang_id . "' "
			. "AND od.language_id = '" . $lang_id . "' ";
		
		$sql = $this->combinationsQueryOrderClause($sql, $data, FALSE);	// do not add limit information since we have already applied that condition when we selected the ids		
							
		$query = $this->db->query($sql);
							
		$product_id = 0;
		$combination_id = 0;
		$combinations = NULL;
		$option_values = NULL;
		$options = NULL;
		$product = NULL;
		$combination = NULL;
		foreach ($query->rows as $row) {
			if ($product_id != $row['product_id']) {
				$product_id = $row['product_id'];
				$product = array(
					'product_id'	=> $row['product_id'],
					'model'			=> $row['model'],
					'name'			=> $row['product_name'],
					'quantity'		=> $row['product_quantity'],
					'combinations' 	=> array(),
					'options'	 	=> array()
				);
				$combinations = &$product['combinations'];	// by reference
				$options = &$product['options'];
				$products[] = $product;
			}
			
			if ( !in_array($row['option_name'], $options) ) {
				$options[] = $row['option_name'];
			}
			
			if ($combination_id != $row['combination_id']) {
				
				$combination_id = $row['combination_id'];
				$combination = array(
						'combination_id'		=> $combination_id, 
						'quantity' 				=> $row['quantity'], 
						'sku' 					=> $row['sku'], 
						'option_value_names' 	=> array()
				);
					
				$option_values = &$combination['option_value_names'];	// by reference
				$combinations[] = $combination;
			}
			
			$option_values[] = $row['option_value_name'];
		}
		
		//$this->log->write("Products:" . var_export($products, TRUE));
		return $products;	
	}
	
    /**
     * Used by the Stock combinations list page to update the quantities and sku values for combinations of a specific product
     * Note that the combinations of the product might not be all the combinations available for that product (it is possible that the user has filtered out some values).
     * Also note that this function will not change the option values of each combination thus it cannot affect the status of the product, or create duplicated entries etc.
     * After the combinations update, product's quantities are updated too.
     *
     * @param $product_id:
     *        the product where the combinations belong to
     * @param $combinations:
     *        array with entries of type: { 'combination_id', 'sku', 'quantity' } 
     * @return 
     *        returns the product's total quantity after the update. The total is retrieved from the product's quantity attribute after it 
     *        has been updated to reflect the changes in the combinations' quantities
     *        if a combination was not found then -1 is returned to signal an error 
     */
	public function editStockCombinations($product_id, $combinations) {
		foreach($combinations as $combination) {
			$combination_id = $combination['combination_id'];
			
			$combination_query = $this->db->query("SELECT count(*) as total FROM `" . DB_PREFIX . "stock` s WHERE s.combination_id = '" . (int)$combination_id . "'");
			
			if ($combination_query->row['total']) {
				$this->db->query("UPDATE `" . DB_PREFIX . "stock` SET sku = '" . $combination['sku']. "', quantity = '" . (int) $combination['quantity'] ."' WHERE combination_id = '" . (int) $combination_id . "'");
			} else {
				return -1;
			}
		}
		$this->updateProductQuantitiesFromStock($product_id);
		
		$query = $this->db->query("SELECT p.quantity FROM `" . DB_PREFIX . "product` p WHERE p.product_id = '" . (int) $product_id . "'");
		$total = $query->row['quantity'];
		return $total;
	}	
	
	/**
     * Used by the Stock combinations list page to provide the autocomplete feature for filtering criteria based on the option value name
     *
     * @param $data:
     *        array with filtering criteria 
     * @return 
     *        array of raw strings with option value names that are part of at least one combination (e.g. the names that correspond to option values in the stock_option database table)
     */
	public function getCombinationOptionValueNames($data = array()) {
		
		$sql = "SELECT distinct ovd.name FROM `" . DB_PREFIX . "stock_option` so "
			. " INNER JOIN `" . DB_PREFIX . "product_option_value` pov ON (pov.product_option_value_id = so.product_option_value_id) "
			. " LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ovd.option_value_id = pov.option_value_id) "
			. " WHERE ovd.language_id = '" . (int) $this->config->get('config_language_id') . "'";
		
		if (!empty($data['filter_option'])) {
			$sql .= " AND ovd.name LIKE '" . $this->db->escape($data['filter_option']) . "%'";
		}
		
		$sql .= " ORDER BY ovd.name ";
	
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}				

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		
		$query = $this->db->query($sql);
		
		$results = array();
		if ($query->rows) {
			foreach($query->rows as $row) {
				$results[] = $row['name'];
			}
		}
		return $results;
	}
	
	public function getStockReport($limit = 5) {
		$products = array();
		$lang_id = (int)$this->config->get('config_language_id');
		$products_query = $this->db->query("SELECT s.product_id, pr.model, s.combination_id, s.quantity, s.sku, pd.name as product_name, so.product_option_value_id, ovd.name as option_value_name "
							. "FROM `" . DB_PREFIX . "stock` s "
							. "LEFT JOIN `" . DB_PREFIX . "product_description` pd on  s.product_id = pd.product_id "
							. "INNER JOIN `" . DB_PREFIX . "product` pr on pr.product_id = pd.product_id "
							. "INNER JOIN `" . DB_PREFIX . "stock_option` so on so.combination_id = s.combination_id "
							. "INNER JOIN `" . DB_PREFIX . "product_option_value` pov on pov.product_option_value_id = so.product_option_value_id "
							. "LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd on ovd.option_value_id = pov.option_value_id "
							. "WHERE pd.language_id = '" . $lang_id . "' "
							. "AND ovd.language_id = '" . $lang_id . "' "
							. "AND s.quantity < '" . $limit . "' "
							. "ORDER BY s.product_id, s.combination_id, so.product_option_value_id");
		
		$product_id = 0;
		$combination_id = 0;
		$combinations = NULL;
		$option_values = NULL;
		$product = NULL;
		$combination = NULL;
		foreach ($products_query->rows as $row) {
				if ($product_id != $row['product_id']) {
					$product_id = $row['product_id'];
					$product = array(
						'product_id'	=> $row['product_id'],
						'model'				=> $row['model'],
						'name'				=> $row['product_name'],
						'combinations'	=> array()
					);
					$combinations = &$product['combinations'];	// by reference
					$products[] = $product;
				}
				
				if ($combination_id != $row['combination_id']) {
					
					$combination_id = $row['combination_id'];
					$combination = array(
							'combination_id'			=> $combination_id, 
							'quantity' 					=> $row['quantity'], 
							'sku' 						=> $row['sku'], 
							'option_value_names' 	=> array()
					);
						
					$option_values = &$combination['option_value_names'];	// by reference
					$combinations[] = $combination;
				}
				
				$option_values[] = $row['option_value_name'];
				
		}
		
		//$this->log->write("Products:" . var_export($products, TRUE));
		return $products;	
	}
	
	
	public function getProductOptions($product_id) {
		$product_option_data = array();
		$lang_id = (int)$this->config->get('config_language_id');		
		$product_option_query = $this->db->query("SELECT po.product_option_id, od.name as option_name, o.type, pov.product_option_value_id, ovd.name as option_value_name "
				. " FROM `" . DB_PREFIX . "product_option` po "
				. " INNER JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) "
				. " LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) "
				. " INNER JOIN `" . DB_PREFIX . "product_option_value` pov ON (po.product_option_id = pov.product_option_id) "
				. " INNER JOIN `" . DB_PREFIX . "option_value` ov ON (pov.option_value_id = ov.option_value_id) "
				. " LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ov.option_value_id = ovd.option_value_id) "
				. " WHERE po.product_id = '" . (int)$product_id . "' AND po.stock_enabled = 1 AND od.language_id = '" . $lang_id . "' AND ovd.language_id = '" . $lang_id . "' ORDER BY po.product_option_id,o.sort_order, ov.sort_order");
		
		$product_option_id = 0;
		$product_option = NULL;
		$product_option_values = NULL;
		foreach ($product_option_query->rows as $row) {
			if ($row['product_option_id'] != $product_option_id) {
					$product_option_id = $row['product_option_id'];
					$product_option = array(
							'product_option_id'		 => $product_option_id,
							'name'								 => $row['option_name'],
							'type'                 => $row['type'],			
							'product_option_value' => array()
					);			
					$product_option_values = &$product_option['product_option_value']; // by reference
					$product_option_data[] = $product_option;
			}
			
			$product_option_values[] = array(
				'product_option_value_id' => $row['product_option_value_id'],
				'name'										=> $row['option_value_name']
			);
		}
		return $product_option_data;
	}
	
	public function getStockEnabledProducts($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) INNER JOIN "  . DB_PREFIX . "product_option po ON (p.product_id = po.product_id) WHERE po.stock_enabled = 1 AND pd.language_id ='" . (int) $this->config->get('config_language_id') . "'";
		
		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}
		
		$sql .= " GROUP BY p.product_id";
					
		$sort_data = array(
			'pd.name',
			'p.model',
			'p.quantity',
			'p.sort_order'
		);	
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY pd.name";	
		}
		
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
	
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}				

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
		
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}	
		
		$query = $this->db->query($sql);
	
		return $query->rows;
	}
	
	public function getTotalStockEnabledProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) INNER JOIN "  . DB_PREFIX . "product_option po ON (p.product_id = po.product_id) WHERE po.stock_enabled = 1 ";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}
		
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}	
	
	protected function hasCombinations($product_id) {
		$query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "stock` s " . "WHERE s.product_id = '" . (int)$product_id . "'");
		$combinations = (int) $query->row['total'];
		return ($combinations > 0);	
	}

	protected function hasValidCombinations($product_id) {
		
		$areAllValid = TRUE;
		
		// get all option ids for product for stock enabled options
		$query = $this->db->query("SELECT po.product_option_id FROM `" . DB_PREFIX . "product_option` po WHERE po.product_id = '" . (int)$product_id . "' AND po.stock_enabled = 1 ORDER BY po.product_option_id");
		$product_option_ids = array();
		foreach($query->rows as $product_option_id) {
			$product_option_ids[] = $product_option_id['product_option_id'];
		}
		//$this->log->write('Product option ids: ' . var_export($product_option_ids, TRUE));
		
		// get pairs of (combination id, product_option_id) for all combinations
		$query = $this->db->query("SELECT s.combination_id, pov.product_option_id FROM `" . DB_PREFIX . "stock` s INNER JOIN `" . DB_PREFIX .  "stock_option` so on (so.combination_id = s.combination_id) LEFT JOIN `" . DB_PREFIX . "product_option_value` pov on (so.product_option_value_id = pov.product_option_value_id) WHERE s.product_id = '" . (int)$product_id . "' ORDER BY s.combination_id, pov.product_option_id");		
		$combination_id = 0;
		$combination = NULL;
		$combinations = array();
		$combination_option_ids = NULL;
		foreach ($query->rows as $row) {
			if ($row['combination_id'] != $combination_id) {
				$combination = array();	
				$combination_id = $row['combination_id'];
				$combination['combination_id'] = $combination_id;	
				$combination['options_ids'] = array();
				$combination_option_ids = &$combination['options_ids']; // by reference so we assign at the array directly and not its local copy
				$combinations[] = $combination;					
			}
			// null option id means that the combination contains a value for an option that no
			// longer exists in product. This can be the case if a stock-enabled product option is deleted
			if ( is_null($row['product_option_id']) ) {
				$areAllValid = FALSE;
				break;
			}
			
			$combination_option_ids[] = $row['product_option_id'];
		}
		
		// iterate over all combinations and make sure that every product option id exists in combination
		if ($areAllValid) {
			//$this->log->write('Checking combinations: ' . var_export($combinations, TRUE));
			foreach ($combinations as $comb) {
				foreach ($product_option_ids as $product_option_id) {
					//$this->log->write('Checking if product option id: ' . $product_option_id . ' exists in combination ' . $comb['combination_id']);
					if (! in_array($product_option_id, $comb['options_ids']) ) {
						//$this->log->write('product option id not found');
						$areAllValid = FALSE;
						break;
					} 
				}
				if (!$areAllValid) {
					break;
				}
			}
		}
		return $areAllValid;	
	}


	protected function hasDuplicateCombinations($product_id) {
		$combinations = $this->getProductStockCombinations($product_id);
		$double_combinations = $this->getDuplicateCombinationNames($combinations, FALSE);
		return (count($double_combinations) > 0);	
	}

	protected function getProductOptionValueNames($options_ids) {
		$query = $this->db->query("SELECT ovd.name FROM `" . DB_PREFIX . "product_option_value` pov INNER JOIN `" . DB_PREFIX . "option_value` ov ON (pov.option_value_id = ov.option_value_id) INNER JOIN `" . DB_PREFIX . "option` o ON (ov.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id in (" . $options_ids . ") AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");
		return $query->rows;		
	}
	
  protected function getOptionsStatus($product_id) {
		$option_query = $this->db->query("SELECT po.product_option_id, po.stock_enabled FROM `" . DB_PREFIX . "product_option` po WHERE product_id = '" . (int)$product_id . "'");
		$options = array();
		if ($option_query->rows) {
			foreach ($option_query->rows as $option) {
				$options[$option['product_option_id']] = $option['stock_enabled'];
			}	
		}
		//$this->log->write('Options status for stock_enabled property: ' . var_export($options, true));
  	return $options;
  }

	public function chargeOrderOptionsStock($order_id, $order_products, $reclaim=false) {
		//$this->log->write('Checking order products (reclaim=' . ($reclaim ? 'true' : 'false') . ') ' . var_export($order_products, true) );
		
		foreach ($order_products as $order_product) {
			
			$order_product_id = $order_product['order_product_id'];
			$product_id = $order_product['product_id'];
			//$this->log->write('Checking order product id: ' . $order_product_id . ' reclaim ? ' . ($reclaim ? 'true' : 'false'));
			
			$stock_enabled_options = array();
			
			// we have two different cases:
			// one is the case when the order and the order options already exist in database. This is the case with addOrder, deleteOrder, and editOrder (only when re-stock)
			// the second case is in editOrder when subtracting quantities again we receive only the data submitted (the order options have been deleted). In this case
			// we have to issue an additional query to decide the stock-enabled property
			if (isset($order_product['order_option'])) {
				//$this->log->write('Options are set similar to a new order. Getting status...');
				$options_status = $this->getOptionsStatus($product_id);
				
				foreach ($order_product['order_option'] as $order_option) { 
					$option_id = $order_option['product_option_id'];
					$stock_enabled = $options_status[$option_id];
					if ($stock_enabled) {
						$stock_enabled_options[] = $order_option['product_option_value_id'];
					}
				}
			} else {
				//$this->log->write('Options are not set. Quering order options...');
				// get the order options along with the stock enabled field
				$order_option_query = $this->db->query("SELECT oo.*, po.stock_enabled FROM " . DB_PREFIX . "order_option oo INNER JOIN `" . DB_PREFIX .  "product_option` po ON (oo.product_option_id = po.product_option_id) WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");
				if ($order_option_query->rows) {
					foreach($order_option_query->rows as $order_option) {
						if ($order_option['stock_enabled'] == 1) {
							$stock_enabled_options[] = $order_option['product_option_value_id'];
						} 
					}
				}
			}
			
			if (!empty($stock_enabled_options)) {
				$this->updateProductStock($product_id, $stock_enabled_options, $order_product['quantity'], $reclaim);
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
	public function updateProductQuantitiesFromStock($product_id) {
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

	public function editStock($product_id, $data) {

		$this->db->query("DELETE FROM `" . DB_PREFIX . "stock_option` WHERE combination_id IN (SELECT DISTINCT combination_id FROM `" . DB_PREFIX . "stock` s WHERE s.product_id = '" . (int) $product_id . "')");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "stock` WHERE product_id = '" . (int) $product_id . "'");

		$added = FALSE;
		if (isset($data['product_combinations'])) {
			foreach ($data['product_combinations'] as $combination) {
				
				$quantity = (int) $combination['quantity'];
				if ($quantity < 0) {
					$quantity = 0;
				}
				
				if ($quantity >= 0) {
					$combination_id = (isset($combination['combination_id']) ? $combination['combination_id'] : '');
					
					$this->db->query("INSERT INTO `" . DB_PREFIX . "stock` SET combination_id = '" . (int)$combination_id . "', product_id = '" . (int) $product_id . "', quantity = '" . (int) $quantity . "', sku = '" . $combination['sku'] . "'");			
					$added = TRUE;
					$combination_id = $this->db->getLastId();
					foreach ($combination['product_option_values'] as $option_value_id) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "stock_option` SET combination_id = '" . (int) $combination_id . "', product_option_value_id = '" . (int) $option_value_id . "'");
					}
				}
			}
		}
		$this->updateProductQuantitiesFromStock($product_id);

		$product_disabled = FALSE;		
		if ( !$added ) {
			$product_disabled = $this->disableProduct($product_id);
		}
		return $product_disabled;
	}
	
	public function deleteStock($product_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "stock_option` WHERE combination_id IN (SELECT DISTINCT combination_id FROM `" . DB_PREFIX . "stock` s WHERE s.product_id = '" . (int) $product_id . "')");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "stock` WHERE product_id = '" . (int) $product_id . "'");
	}
	
	public function disableProduct($product_id) {
		// if no combinations were inserted then the product's stock is empty. Check if we have to update the product's status
		$this->db->query("UPDATE `" . DB_PREFIX . "product` SET status = 0 WHERE product_id = '" . (int) $product_id . "' AND status = 1");			
		$affected = $this->db->countAffected();
		return ($affected > 0);
	}
	
	public function modifyProductStock($product_id, $old_product_options, $new_product_options) {
		$product_disabled = FALSE;

		//$this->log->write('Old options: ' . var_export($old_product_options, TRUE));
		//$this->log->write('New options: ' . var_export($new_product_options, TRUE));

		$old_option_ids = array_keys($old_product_options);
		$new_option_ids = array_keys($new_product_options);
		
		//$this->log->write('Old options ids: ' . var_export($old_option_ids, TRUE));
		//$this->log->write('New options ids: ' . var_export($new_option_ids, TRUE));
		
		$removed_option_ids = array_diff($old_option_ids, $new_option_ids);
		$added_option_ids = array_diff($new_option_ids, $old_option_ids);
		$existed_option_ids = array_diff($new_option_ids, $added_option_ids);
		
		//$this->log->write('Removed options: ' . var_export($removed_option_ids, TRUE));
		//$this->log->write('Added options: ' . var_export($added_option_ids, TRUE));
		//$this->log->write('Existed options: ' . var_export($existed_option_ids, TRUE));

		foreach ($removed_option_ids as $removed_option_id) {
			$this->removeAllOptionValuesFromStock($old_product_options[$removed_option_id], $product_id);
		}

		foreach ($existed_option_ids as $existed_option_id) {
			
			//$this->log->write('Checking unchanged option id: ' . $existed_option_id);
			
			$old_option_values = $old_product_options[$existed_option_id];
			$new_option_values = $new_product_options[$existed_option_id];
			
			//$this->log->write('Old option values: ' . var_export($old_option_values, TRUE));
			//$this->log->write('New option values: ' . var_export($new_option_values, TRUE));
			
			$removed_option_value_ids = array_diff($old_option_values, $new_option_values);
			
			//$this->log->write('Removed option value ids: ' . var_export($removed_option_value_ids, TRUE));
			$this->removeAllOptionValuesFromStock($removed_option_value_ids, $product_id);
		}
    
    // after option deletion it can be the case that the product is no more stock-enabled
		if ( $this->isProductStockEnabled($product_id) && (! $this->isStockValid($product_id)) ) {
			$product_disabled = $this->disableProduct($product_id);					
		}
		
		return $product_disabled;
	}
	
	protected function removeAllOptionValuesFromStock($option_value_ids, $product_id) {
		if (!empty($option_value_ids)) { 
			$ids = implode(',', $option_value_ids);
			$this->db->query("DELETE so.* FROM `" . DB_PREFIX . "stock_option` so INNER JOIN `" . DB_PREFIX . "stock` s ON (so.combination_id = s.combination_id) WHERE s.product_id = '" . (int)$product_id . "' AND so.product_option_value_id IN (" . $ids . ")");		
			$this->db->query("DELETE s.* FROM `" . DB_PREFIX . "stock` s WHERE s.product_id = '" . (int)$product_id . "' AND (SELECT count(*) FROM `" . DB_PREFIX . "stock_option` so where so.combination_id = s.combination_id) = 0");		
		}
	}
	
	private function filterOptionValueIds($data) {
		$filtered = array();
		if ( !empty($data) ) {
			foreach ($data as $option_value) {
				if (isset($option_value['option_value_id'])) {
					$filtered[] = $option_value['option_value_id'];
				}
  			}
		}
		return $filtered;
	}
	
	public function getOptionValuesUsed($option_id, $old_option_values, $new_option_values) {
		
		$old_ids = $this->filterOptionValueIds($old_option_values);
		$new_ids = $this->filterOptionValueIds($new_option_values);
		
		$removed_ids = array_diff($old_ids, $new_ids);
		
		$result = TRUE;
  	    $total = 0;
    	foreach ($removed_ids as $option_value) {
    		$total = $this->countProductsWithOptionAndOptionValue($option_id, $option_value);
    		if ($total > 0) {
    			$result = FALSE;
    			break;
    		}
    	}
    	return $total;
	}
	
	private function countProductsWithOptionAndOptionValue($option_id, $option_value_id) {
	    $query = $this->db->query("SELECT count(distinct s.product_id) as total  "
			. " FROM `" . DB_PREFIX . "stock` s "
			. " INNER JOIN `" . DB_PREFIX . "stock_option` so on (s.combination_id = so.combination_id) "
			. " INNER JOIN `" . DB_PREFIX . "product_option_value` pov on (pov.product_option_value_id = so.product_option_value_id and pov.product_id = s.product_id) "
			. " WHERE pov.option_value_id = '" . (int)$option_value_id . "' AND pov.option_id = '" . (int)$option_id . "'");	
		
		$total = (int) $query->row['total'];
		return $total;
	}
}
?>
