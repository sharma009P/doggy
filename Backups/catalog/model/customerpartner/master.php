<?php
class ModelCustomerpartnerMaster extends Model {


	public function getPartnerIdBasedonProduct($productid){

		$product_status = 1;

        if (($this->config->get('module_marketplace_status') && isset($this->request->get['user_token']) && isset($this->session->data['user_token']) && isset($this->session->data['user_id']) && $this->request->get['user_token'] == $this->session->data['user_token']) || ($this->config->get('module_marketplace_status') && isset($this->request->get['product_token']) && isset($this->session->data['product_token']) && $this->request->get['product_token'] == $this->session->data['product_token'])){
            $product_status = $this->db->query("SELECT status FROM ".DB_PREFIX."product WHERE product_id = ". (int)$productid)->row['status'];
            if (!$product_status) {
                $this->db->query("UPDATE ".DB_PREFIX."product SET status = '1' WHERE product_id = " . (int)$productid);
            }
        }

		$query = $this->db->query("SELECT c2p.customer_id as id FROM " . DB_PREFIX . "customerpartner_to_product c2p LEFT JOIN ".DB_PREFIX."product p ON(c2p.product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_to_store p2s ON (p.product_id = p2s.product_id) WHERE c2p.product_id = '".(int)$productid."' AND p.status = 1 AND p2s.store_id = '".$this->config->get('config_store_id')."' ORDER BY c2p.id ASC ")->row;

		if (!$product_status) {
	        $this->db->query("UPDATE ".DB_PREFIX."product SET status = '0' WHERE product_id = " . (int)$productid);
	    }

		return $query;
	}

	public function getLatest($data = array()) {
		$sql = "SELECT p.product_id,pd.description,p.image,p.price,p.minimum,p.tax_class_id,pd.name,c2c.screenname,c2c.avatar,c2c.backgroundcolor,c.customer_id,CONCAT(c.firstname ,' ',c.lastname) seller_name,co.name country, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM " . DB_PREFIX . "customerpartner_to_product c2p LEFT JOIN ".DB_PREFIX ."product p ON (c2p.product_id = p.product_id) LEFT JOIN ".DB_PREFIX ."product_description pd ON (pd.product_id = p.product_id) LEFT JOIN ".DB_PREFIX ."customerpartner_to_customer c2c ON (c2c.customer_id = c2p.customer_id) LEFT JOIN ".DB_PREFIX ."customer c ON (c2c.customer_id = c.customer_id) LEFT JOIN ".DB_PREFIX ."country co ON (c2c.country = co.iso_code_2) LEFT JOIN ".DB_PREFIX."product_to_store p2s ON (p.product_id = p2s.product_id) WHERE c2c.is_partner = '1' AND p.status = '1' AND p.date_available <= NOW() AND pd.language_id = '".(int)$this->config->get('config_language_id')."' AND p2s.store_id = '".$this->config->get('config_store_id')."'";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.quantity',
			'p.price',
			'rating',
			'c2p.product_id',
			'p.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
				$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
			} elseif ($data['sort'] == 'p.price') {
				$sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
			} else {
				$sql .= " ORDER BY " . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY c2p.product_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC, LCASE(pd.name) DESC";
		} else {
			$sql .= " ASC, LCASE(pd.name) ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			if ((int)$this->config->get('marketplace_seller_product_list_limit') && ($data['limit'] > (int)$this->config->get('marketplace_seller_product_list_limit'))) {
				$data['limit'] = (int)$this->config->get('marketplace_seller_product_list_limit');
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		$products = array();
		foreach ($query->rows as $key => $value) {
			$products[$value['product_id']] = $value;
		}

		return $products;
	}

	public function getTotalLatest($data = array()) {
		$sql = "SELECT p.product_id,pd.description,p.image,p.price,p.tax_class_id,pd.name,c2c.avatar,c2c.backgroundcolor,c.customer_id,CONCAT(c.firstname ,' ',c.lastname) seller_name,co.name country, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special FROM " . DB_PREFIX . "customerpartner_to_product c2p LEFT JOIN ".DB_PREFIX ."product p ON (c2p.product_id = p.product_id) LEFT JOIN ".DB_PREFIX ."product_description pd ON (pd.product_id = p.product_id) LEFT JOIN ".DB_PREFIX ."customerpartner_to_customer c2c ON (c2c.customer_id = c2p.customer_id) LEFT JOIN ".DB_PREFIX ."customer c ON (c2c.customer_id = c.customer_id) LEFT JOIN ".DB_PREFIX ."country co ON (c2c.country = co.iso_code_2) LEFT JOIN ".DB_PREFIX."product_to_store p2s ON (p.product_id = p2s.product_id) WHERE c2c.is_partner = '1' AND p.status = '1' AND p.date_available <= NOW() AND pd.language_id = '".(int)$this->config->get('config_language_id')."' AND p2s.store_id = '".$this->config->get('config_store_id')."'";

		if ((int)$this->config->get('marketplace_seller_product_list_limit')) {
			$sql .= 'ORDER BY c2p.product_id DESC limit '.(int)$this->config->get('marketplace_seller_product_list_limit').'';
		}

		$query = $this->db->query($sql);

		$products = array();
		foreach ($query->rows as $key => $value) {
			$products[$value['product_id']] = $value;
		}

		return count($products);
	}

	public function getPartnerCollectionCount($customerid){
		return count($this->db->query("SELECT DISTINCT p.product_id FROM " . DB_PREFIX . "customerpartner_to_product c2p LEFT JOIN ".DB_PREFIX ."product p ON (c2p.product_id = p.product_id) LEFT JOIN ".DB_PREFIX."product_to_store p2s ON (p.product_id = p2s.product_id) WHERE c2p.customer_id='" . (int)$customerid."' AND p.status='1' AND p.date_available <= NOW() AND p2s.store_id = '".$this->config->get('config_store_id')."' ORDER BY c2p.product_id ")->rows);
	}

	public function getOldPartner(){

		$limit = (int)$this->config->get('marketplace_seller_list_limit') ? (int)$this->config->get('marketplace_seller_list_limit') : 4;

	    return $this->db->query("SELECT *,co.name as country,companylocality FROM " . DB_PREFIX . "customerpartner_to_customer c2c LEFT JOIN ".DB_PREFIX ."customer c ON (c2c.customer_id = c.customer_id) LEFT JOIN ".DB_PREFIX ."country co ON (c2c.country = co.iso_code_2) WHERE is_partner = 1 AND c.status = '1' ORDER BY c2c.customer_id ASC LIMIT ". $limit ."")->rows;
	}

	public function getProfile($customerid){
		$sql = "SELECT c2c.*, c.*,c.firstname,c.lastname,co.name as country, a.address_1, a.address_2, a.city, a.postcode, a.country_id, a.zone_id, a.custom_field FROM " . DB_PREFIX . "customerpartner_to_customer c2c LEFT JOIN ".DB_PREFIX ."customer c ON (c2c.customer_id = c.customer_id) LEFT JOIN ".DB_PREFIX ."address a ON (c.address_id = a.address_id) LEFT JOIN ".DB_PREFIX ."country co ON (c2c.country = co.iso_code_2) WHERE c2c.customer_id = '".(int)$customerid."' AND c2c.is_partner = '1' AND c.status = '1'";

		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getFeedbackList($customerid) {
		$sql = "SELECT c2f.* FROM " . DB_PREFIX . "customerpartner_to_feedback c2f LEFT JOIN ".DB_PREFIX ."customer c ON (c2f.customer_id = c.customer_id) LEFT JOIN ".DB_PREFIX ."customerpartner_to_customer cpc ON (cpc.customer_id = c.customer_id) where c2f.seller_id = '".(int)$customerid."' AND c2f.status = '1'";
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getTotalFeedback($customerid){
		$query = $this->db->query("SELECT id FROM " . DB_PREFIX . "customerpartner_to_feedback c2f where c2f.seller_id='".(int)$customerid."' AND c2f.status = '1'");
		return count($query->rows);
	}

	public function getAverageFeedback($customerid, $field_id = 0){

	  $sql = "SELECT round(AVG(field_value)) avg  FROM `" . DB_PREFIX . "wk_feedback_attribute_values` WHERE feedback_id IN (SELECT id FROM `".DB_PREFIX."customerpartner_to_feedback` WHERE seller_id='".(int)$customerid."' AND status = '1')";

	  if ($field_id) {
	    $sql .= " AND field_id = ".(int)$field_id;
	  }

	  $avg = $this->db->query($sql)->row;

	  if (isset($avg['avg'])) {
	    return $avg['avg'];
	  }

	  return 0;
	}

	public function getProductFeedbackList($customerid) {
		$query = $this->db->query("SELECT r.*,pd.name,r.text FROM " . DB_PREFIX . "customerpartner_to_product c2p INNER JOIN ".DB_PREFIX ."review r ON (c2p.product_id = r.product_id) LEFT JOIN ".DB_PREFIX."product_description pd ON (pd.product_id = c2p.product_id) WHERE c2p.customer_id = '".(int)$customerid."' AND pd.language_id = '".(int)$this->config->get('config_language_id')."' AND r.status = 1 ");
		return $query->rows;
	}

	public function getTotalProductFeedbackList($customerid){
		$query = $this->db->query("SELECT r.* FROM " . DB_PREFIX . "customerpartner_to_product c2p INNER JOIN ".DB_PREFIX ."review r ON (c2p.product_id = r.product_id) WHERE c2p.customer_id = '".(int)$customerid."' AND r.status = 1 ");
		return count($query->rows);
	}


	public function saveFeedback($data,$seller_id){

		$feedback_id = 0;

		$result = $this->db->query("SELECT id FROM ".DB_PREFIX ."customerpartner_to_feedback WHERE customer_id = ".(int)$this->customer->getId()." AND seller_id = '".(int)$seller_id."'")->row;

		if(!$result){
			$this->db->query("INSERT INTO ".DB_PREFIX ."customerpartner_to_feedback SET customer_id = '".(int)$this->customer->getId()."',seller_id = '".(int)$seller_id."', nickname = '".$this->db->escape($data['name'])."',  review = '".$this->db->escape($data['text'])."', createdate = NOW(), status = '0'");
			$feedback_id = $this->db->getLastId();
		}else{
			$this->db->query("UPDATE ".DB_PREFIX ."customerpartner_to_feedback set nickname='".$this->db->escape($data['name'])."', review='".$this->db->escape($data['text'])."',createdate = NOW(), status = '0' WHERE id = '".$result['id']."'");
			$feedback_id = $result['id'];
		}

		if ($feedback_id && isset($data['review_attributes']) && is_array($data['review_attributes']) && !empty($data['review_attributes'])) {
			foreach ($data['review_attributes'] as $key => $value) {
				if ($this->db->query("SELECT * FROM ".DB_PREFIX."wk_feedback_attribute WHERE field_id=".$key)->row) {
					$this->db->query("DELETE FROM " . DB_PREFIX . "wk_feedback_attribute_values WHERE feedback_id = '" . (int)$feedback_id . "' AND field_id=".$key);

					$this->db->query("INSERT INTO `" . DB_PREFIX . "wk_feedback_attribute_values` SET `feedback_id` = '" . (int)$this->db->escape($feedback_id) . "', `field_id` = '" . (int)$this->db->escape($key) . "',  field_value = '" . $this->db->escape($value) . "'");
				}
			}
		}
	}

	public function getShopData($shop){
		$sql = $this->db->query("SELECT * FROM " . DB_PREFIX . "customerpartner_to_customer where companyname = '" .$this->db->escape($shop)."'")->row;
		if($sql)
			return $sql;
		return false;
	}

	public function checkCustomerBought($seller_id){

         $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "customerpartner_to_order c2o ON (o.order_id = c2o.order_id) where  o.customer_id = '" .$this->db->escape((int)$this->customer->getId())."' AND c2o.customer_id = '" . (int)$this->db->escape($seller_id) . "'")->row;

         return $query;
	}

    /**
     * [getAllAverageFeedback uses to fetch average feedprice, feedvalue, feedquality of the seller]
     * @return [type] [array]
     */
		 public function getAllAverageFeedback($seller_id,$field_id = 0){
	 			$avg = $this->db->query("SELECT round(AVG(field_value)) avg  FROM `" . DB_PREFIX . "wk_feedback_attribute_values` WHERE field_id	= ". (int)$field_id ." AND feedback_id IN (SELECT id FROM `".DB_PREFIX."customerpartner_to_feedback` WHERE seller_id='".(int)$seller_id."' AND status = '1')")->row;

	 		 if (isset($avg['avg'])) {
	 			 return $avg['avg'];
	 		 }

	 		 return 0;
	 	}

	public function getReviewField($reviewfield_id) {

	  $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "wk_feedback_attribute WHERE field_id = '" . (int)$reviewfield_id . "'");

	  return $query->row;
	}

	public function getReviewFieldByName($reviewfield_name) {

	  $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "wk_feedback_attribute WHERE field_name = '" . $this->db->escape($reviewfield_name) . "'");

	  return $query->row;
	}

	public function getAllReviewFields() {
	  $sql = "SELECT * FROM " . DB_PREFIX . "wk_feedback_attribute WHERE field_status = 1";

	  $query = $this->db->query($sql);

	  return $query->rows;
	}

	public function getReviewAttributeValue($feedback_id = 0, $field_id = 0) {
	  $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "wk_feedback_attribute_values WHERE feedback_id = '" . (int)$feedback_id . "' AND field_id = ".(int)$field_id);

	  return $query->row;
	}

}
?>
