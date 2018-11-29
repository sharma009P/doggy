<?php
class ModelCustomerpartnerPartner extends Model {

	private $data = array();

	public function getCustomer($customer_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");

		return $query->row;
	}


	public function getAllCustomers() {

		$sql = $this->db->query("SELECT c.email FROM " . DB_PREFIX . "customerpartner_to_customer c2c LEFT JOIN " . DB_PREFIX . "customer c ON (c2c.customer_id = c.customer_id) WHERE c2c.is_partner = '1'");

		return $sql->rows;

	}

	public function getCustomers($data = array()) {

		if(isset($data['filter_all']) AND $data['filter_all'] == '1'){
			$add = '';
		}elseif(isset($data['filter_all']) AND $data['filter_all'] == '2'){
			$add = ' c2c.is_partner = 0 AND';
		}else{
			$add = ' c2c.is_partner = 1 AND';
		}

		$sql = "SELECT *,c.status, CONCAT(c.firstname, ' ', c.lastname) AS name,c.customer_id AS customer_id, c2c.is_partner,cgd.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) LEFT JOIN ". DB_PREFIX . "customerpartner_to_customer c2c ON (c2c.customer_id = c.customer_id) WHERE ". $add ." cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$implode = array();

		if (!empty($data['filter_name'])) {
			$implode[] = "LCASE(CONCAT(c.firstname, ' ', c.lastname)) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "LCASE(c.email) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_email'])) . "%'";
		}

		if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
			$implode[] = "c.newsletter = '" . (int)$data['filter_newsletter'] . "'";
		}

		if (isset($data['filter_customer_group_id']) && !empty($data['filter_customer_group_id'])) {
			$implode[] = "c.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
		}

		if (isset($data['filter_ip']) && !empty($data['filter_ip'])) {
			$implode[] = "c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "c2c.is_partner = '" . (int)$data['filter_approved'] . "'";
		}

		if (isset($data['filter_date_added']) && !empty($data['filter_date_added'])) {
			$implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (isset($data['filter_category']) && !empty($data['filter_category'])) {
			$implode[] = "c.customer_id NOT IN (SELECT seller_id FROM " . DB_PREFIX . "customerpartner_to_category)";
		}

		if ($implode) {
			$sql .= " AND " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'c.customer_id',
			'name',
			'c.email',
			'customer_group',
			'c.status',
			'c.approved',
			'c.ip',
			'c.date_added'
		);


		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY c.customer_id";
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

	public function getTotalCustomers($data = array()) {

      	if(isset($data['filter_all']) AND $data['filter_all'] == '1'){
			$add = '';
		}elseif(isset($data['filter_all']) AND $data['filter_all'] == '2'){
			$add = ' c2c.is_partner = 0 AND';
		}else{
			$add = ' c2c.is_partner = 1 AND';
		}

		$sql = "SELECT *,c.status, CONCAT(c.firstname, ' ', c.lastname) AS name,c.customer_id AS customer_id, c2c.is_partner,cgd.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id) LEFT JOIN ". DB_PREFIX . "customerpartner_to_customer c2c ON (c2c.customer_id = c.customer_id) WHERE ". $add ." cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$implode = array();

		$implode = array();

		if (!empty($data['filter_name'])) {
			$implode[] = "LCASE(CONCAT(c.firstname, ' ', c.lastname)) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "LCASE(c.email) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_email'])) . "%'";
		}

		if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
			$implode[] = "c.newsletter = '" . (int)$data['filter_newsletter'] . "'";
		}

		if (!empty($data['filter_customer_group_id'])) {
			$implode[] = "c.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
		}

		if (!empty($data['filter_ip'])) {
			$implode[] = "c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "c2c.is_partner = '" . (int)$data['filter_approved'] . "'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " AND " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'c.customer_id',
			'name',
			'c.email',
			'customer_group',
			'c.status',
			'c.approved',
			'c.ip',
			'c.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY c.customer_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		$query = $this->db->query($sql);

		return count($query->rows);
	}

	public function approve($customer_id,$setstatus = 1) {

		$customer_info = $this->getCustomer($customer_id);

		if ($customer_info) {

			$commission = (int)$this->config->get('marketplace_commission') ? (int)$this->config->get('marketplace_commission') : 0;

			$seller_info = $this->getPartner($customer_id);

			if($seller_info){
				$this->db->query("UPDATE " . DB_PREFIX . "customerpartner_to_customer SET is_partner='".(int)$setstatus."',commission = '".(float)$commission."' WHERE customer_id = '" . (int)$customer_id . "'");

				// membership modification after partner
	        	if($this->config->get('module_wk_seller_group_status') && !$this->db->query("SELECT * FROM `" . DB_PREFIX . "seller_group_customer_seller_group` WHERE seller_id = '" . (int)$customer_id . "'")->num_rows) {
			        $this->load->model('customerpartner/sellergroup');
			        $freeMembership = $this->db->query("SELECT * FROM ".DB_PREFIX."seller_group WHERE autoApprove = 1 ")->row;

			        if($freeMembership && isset($freeMembership['product_id']) && $freeMembership['product_id']) {
								$group_id = $freeMembership['groupid'];
								$this->model_customerpartner_sellergroup->update_customer_quantity($customer_id,$group_id,'unpaid',true);
					}
				}

			}else{
				$name = $customer_info['firstname'].'-'.$customer_info['lastname'].'-'. md5(mt_rand());

				$this->db->query("INSERT INTO " . DB_PREFIX . "customerpartner_to_customer SET customer_id = '" . (int)$customer_id . "', is_partner='".(int)$setstatus."', commission = '".(float)$commission."', screenname = '" . $name . "', companyname = '" . $name . "'");

				// membership modification after partner
	        	if($this->config->get('module_wk_seller_group_status') && !$this->db->query("SELECT * FROM `" . DB_PREFIX . "seller_group_customer_seller_group` WHERE seller_id = '" . (int)$customer_id . "'")->num_rows) {
			        $this->load->model('customerpartner/sellergroup');
			        $freeMembership = $this->db->query("SELECT * FROM ".DB_PREFIX."seller_group WHERE autoApprove = 1 ")->row;

			        if($freeMembership && isset($freeMembership['product_id']) && $freeMembership['product_id']) {
								$group_id = $freeMembership['groupid'];
								$this->model_customerpartner_sellergroup->update_customer_quantity($customer_id,$group_id,'unpaid',true);
					}
				}

			}

			if(!$this->config->get('marketplace_mail_partner_approve'))
				return;

			$data = array_merge($customer_info,$seller_info);

			//send mail to Customer after request for Partnership
			if(!$data['is_partner'] ) {
				$data['mail_id'] = $this->config->get('marketplace_mail_partner_approve');
				$data['mail_from'] = $this->config->get('marketplace_adminmail');
				$data['mail_to'] = $data['email'];
				$data['seller_id'] = $customer_id;
				$value_index = array(
		      'commission' => (int)$this->config->get('marketplace_commission') ? (int)$this->config->get('marketplace_commission') : 0,
					'seller_id' => $customer_id
		    );

				$this->load->model('customerpartner/mail');

				$this->model_customerpartner_mail->mail($data,$value_index);
			}

	  }

 }

	//for get commission
	public function getPartner($partner_id){
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX ."customerpartner_to_customer where customer_id='".(int)$partner_id."'");
		return($query->row);
	}

	public function getPartnerCustomerInfo($partner_id){
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX ."customerpartner_to_customer c2c LEFT JOIN ".DB_PREFIX."customer c ON (c2c.customer_id = c.customer_id) where c.customer_id='".(int)$partner_id."'");
		return($query->row);
	}

	



	public function getPartnerAmount($partner_id){
		$total = $this->db->query("SELECT SUM(c2o.quantity) quantity,SUM(c2o.price) total,SUM(c2o.admin) admin,SUM(c2o.customer) customer FROM ".DB_PREFIX ."customerpartner_to_order c2o WHERE c2o.customer_id ='".(int)$partner_id."'")->row;

		$paid = $this->db->query("SELECT SUM(c2t.amount) total FROM ".DB_PREFIX ."customerpartner_to_transaction c2t WHERE c2t.customer_id ='".(int)$partner_id."'")->row;

		$total['paid'] = $paid['total'];

		return($total);
	}

	public function getPartnerTotal($partner_id,$filter_data = array() ) {

		$sub_query = "(SELECT SUM(c2t.amount) as total FROM ".DB_PREFIX ."customerpartner_to_transaction c2t WHERE c2t.customer_id ='".(int)$partner_id."'";

		if(isset($filter_data['date_added_from']) || isset($filter_data['date_added_to'])) {
			if($filter_data['date_added_from'] && $filter_data['date_added_to']){
				$sub_query .= " AND c2t.date_added >= '".$filter_data['date_added_from']."' && c2t.date_added <= '".$filter_data['date_added_to']."' ";
			} else if($filter_data['date_added_from'] && !$filter_data['date_added_to']) {
				$sub_query .= " AND c2t.date_added >= '".$filter_data['date_added_from']."' ";
			} else if(!$filter_data['date_added_from'] && $filter_data['date_added_to']) {
				$sub_query .= " AND c2t.date_added <= '".$filter_data['date_added_to']."' ";
			}
		}

		if(isset($filter_data['paid_to_seller_from']) || isset($filter_data['paid_to_seller_to']) ) {
			if($filter_data['paid_to_seller_from'] && $filter_data['paid_to_seller_to']) {
				$sub_query .= " HAVING SUM(c2t.amount) > ".$filter_data['paid_to_seller_from']." && SUM(c2t.amount) < ".$filter_data['paid_to_seller_to']." )";
			} else if($filter_data['paid_to_seller_from'] && !$filter_data['paid_to_seller_to']) {
				$sub_query .= " HAVING SUM(c2t.amount) > ".$filter_data['paid_to_seller_from']." )";
			} else if(!$filter_data['paid_to_seller_from'] && $filter_data['paid_to_seller_to']) {
				$sub_query .= " HAVING SUM(c2t.amount) > ".$filter_data['paid_to_seller_to']." )";
			} else {
				$sub_query .= " )";
			}
		} else {
			$sub_query .= " )";
		}

		$sql = "SELECT SUM(c2o.quantity) quantity,(SUM(c2o.customer) + SUM(c2o.admin)) as total,SUM(c2o.admin) admin,SUM(c2o.customer) as customer, ".$sub_query." as paid FROM ".DB_PREFIX ."customerpartner_to_order c2o WHERE c2o.customer_id ='".(int)$partner_id."' ";

		if ($this->config->get('marketplace_complete_order_status')) {
		  $sql .= " AND c2o.order_id IN (SELECT DISTINCT order_id FROM `" . DB_PREFIX . "order` o LEFT JOIN `" . DB_PREFIX . "order_status` os ON (o.order_status_id = os.order_status_id) WHERE os.order_status_id = '". $this->config->get('marketplace_complete_order_status') ."') ";
		}

		if(isset($filter_data['date_added_from']) || isset($filter_data['date_added_to'])) {
			if($filter_data['date_added_from'] && $filter_data['date_added_to']){
				$sql .= " AND c2o.date_added >= '".$filter_data['date_added_from']."' && c2o.date_added <= '".$filter_data['date_added_to']."' ";
			} else if($filter_data['date_added_from'] && !$filter_data['date_added_to']) {
				$sql .= " AND c2o.date_added >= '".$filter_data['date_added_from']."' ";
			} else if(!$filter_data['date_added_from'] && $filter_data['date_added_to']) {
				$sql .= " AND c2o.date_added <= '".$filter_data['date_added_to']."' ";
			}
		}

		$sql .= " HAVING SUM(c2o.quantity) >= 0 ";

		if(isset($filter_data['total_amount_from']) || isset($filter_data['total_amount_to']) ) {
			if($filter_data['total_amount_from'] && $filter_data['total_amount_to']){
				$sql .= " AND (SUM(c2o.customer) + SUM(c2o.admin)) > ".$filter_data['total_amount_from']." && (SUM(c2o.customer) + SUM(c2o.admin)) < ".$filter_data['total_amount_to']." ";
			} else if($filter_data['total_amount_from'] && !$filter_data['total_amount_to']) {
				$sql .= " AND (SUM(c2o.customer) + SUM(c2o.admin)) > ".$filter_data['total_amount_from']." ";
			} else if(!$filter_data['total_amount_from'] && $filter_data['total_amount_to']) {
				$sql .= " AND (SUM(c2o.customer) + SUM(c2o.admin)) < ".$filter_data['total_amount_to']."";
			}
		}

		if(isset($filter_data['seller_amount_from']) || isset($filter_data['seller_amount_to']) ) {
			if($filter_data['seller_amount_from'] && $filter_data['seller_amount_to']){
				$sql .= " AND SUM(c2o.customer) > ".$filter_data['seller_amount_from']." && SUM(c2o.customer) < ".$filter_data['seller_amount_to']." ";
			} else if($filter_data['seller_amount_from'] && !$filter_data['seller_amount_to']) {
				$sql .= " AND SUM(c2o.customer) > ".$filter_data['seller_amount_from']." ";
			} else if(!$filter_data['seller_amount_from'] && $filter_data['seller_amount_to']) {
				$sql .= " AND SUM(c2o.customer) < ".$filter_data['seller_amount_to']."";
			}
		}

		if(isset($filter_data['admin_amount_from']) || isset($filter_data['admin_amount_to']) ) {
			if($filter_data['admin_amount_from'] && $filter_data['admin_amount_to']){
				$sql .= " AND SUM(c2o.admin) > ".$filter_data['admin_amount_from']." && SUM(c2o.admin) < ".$filter_data['admin_amount_to']." ";
			} else if($filter_data['admin_amount_from'] && !$filter_data['admin_amount_to']) {
				$sql .= " AND SUM(c2o.admin) > ".$filter_data['admin_amount_from']." ";
			} else if(!$filter_data['admin_amount_from'] && $filter_data['admin_amount_to']) {
				$sql .= " AND SUM(c2o.admin) < ".$filter_data['admin_amount_to']."";
			}
		}

		// echo $sql;
		$total = $this->db->query($sql)->row;

		return($total);
	}

	public function getPartnerAmountTotal($partner_id,$filter_data = array() ){
		$sql = "SELECT SUM(c2o.quantity) quantity,SUM(c2o.price) total,SUM(c2o.admin) admin,SUM(c2o.customer) customer FROM ".DB_PREFIX ."customerpartner_to_order c2o WHERE c2o.customer_id ='".(int)$partner_id."' ";
		echo $sql;

		if($filter_data['commission_from'] && $filter_data['commission_to']){
			$url .= '';
		} else if($filter_data['commission_from'] && !$filter_data['commission_to']) {
			$url .= '';
		} else if(!$filter_data['commission_from'] && $filter_data['commission_to']) {
			$url .= '';
		}

		$total = $this->db->query($sql)->row;

		$paid = $this->db->query("SELECT SUM(c2t.amount) total FROM ".DB_PREFIX ."customerpartner_to_transaction c2t WHERE c2t.customer_id ='".(int)$partner_id."'")->row;

		$total['paid'] = $paid['total'];

		return(count($total));
	}

	public function getSellerOrdersListPaypal($seller_id){

		if ($this->config->get('marketplace_complete_order_status')) {
			$complete_order_status = $this->config->get('marketplace_complete_order_status');
		} else {
			$complete_order_status = '5';
		}

		$sql = "SELECT DISTINCT op.order_id,c2o.paid_status,c2o.commission_applied,c2o.customer as need_to_pay,o.date_added, CONCAT(o.firstname ,' ',o.lastname) name ,os.order_status_id orderstatus,os.name orderstatus_name,op.*, (SELECT group_concat( concat( value) SEPARATOR ', ') FROM ".DB_PREFIX."order_option oo WHERE oo.order_product_id=c2o.order_product_id ) as value  FROM " . DB_PREFIX ."order_status os LEFT JOIN `".DB_PREFIX ."order` o ON (os.order_status_id = o.order_status_id) LEFT JOIN ".DB_PREFIX ."customerpartner_to_order c2o ON (o.order_id = c2o.order_id) LEFT JOIN ".DB_PREFIX."order_product op ON op.order_product_id=c2o.order_product_id WHERE c2o.customer_id = '".(int)$seller_id."' AND os.language_id = '".$this->config->get('config_language_id')."' AND c2o.paid_status = 0 AND os.order_status_id = '".$complete_order_status."'";

		$result = $this->db->query($sql);

		return($result->rows);
	}

	public function getSellerOrdersList($seller_id,$filter_data){

		$sql = "SELECT DISTINCT op.order_id,c2o.paid_status,c2o.commission_applied,c2o.customer as need_to_pay,o.date_added, CONCAT(o.firstname ,' ',o.lastname) name ,os.order_status_id orderstatus,os.name orderstatus_name,op.*, (SELECT group_concat( concat( value) SEPARATOR ', ') FROM ".DB_PREFIX."order_option oo WHERE oo.order_product_id=c2o.order_product_id ) as value  FROM " . DB_PREFIX ."order_status os LEFT JOIN `".DB_PREFIX ."order` o ON (os.order_status_id = o.order_status_id) LEFT JOIN ".DB_PREFIX ."customerpartner_to_order c2o ON (o.order_id = c2o.order_id) LEFT JOIN ".DB_PREFIX."order_product op ON op.order_product_id=c2o.order_product_id WHERE c2o.customer_id = '".(int)$seller_id."' AND os.language_id = '".$this->config->get('config_language_id')."' ";

		if ($this->config->get('marketplace_complete_order_status')) {
		  $sql .= " AND os.order_status_id = '" . $this->config->get('marketplace_complete_order_status') . "' ";
		}

		if(isset($filter_data['date_added_from']) || isset($filter_data['date_added_to'])) {
			if($filter_data['date_added_from'] && $filter_data['date_added_to']){
				$sql .= " AND o.date_added >= '".$filter_data['date_added_from']."' && o.date_added <= '".$filter_data['date_added_to']."' ";
			} else if($filter_data['date_added_from'] && !$filter_data['date_added_to']) {
				$sql .= " AND o.date_added >= '".$filter_data['date_added_from']."' ";
			} else if(!$filter_data['date_added_from'] && $filter_data['date_added_to']) {
				$sql .= " AND o.date_added <= '".$filter_data['date_added_to']."' ";
			}
		}

		if($filter_data['order_id']) {
			$sql .= " AND op.order_id = '".$filter_data['order_id']."' " ;
		}

		if($filter_data['payable_amount']) {
			$sql .= " AND c2o.customer = ".(float)$filter_data['payable_amount']." " ;
		}

		if($filter_data['quantity']) {
			$sql .= " AND op.quantity = '".$filter_data['quantity']."' " ;
		}

		if($filter_data['order_status']) {
			$sql .= " AND os.name = '".$filter_data['order_status']."' " ;
		}

		if($filter_data['paid_status']) {
			if($filter_data['paid_status'] == 'paid')
				$filter_data['paid_status'] = 1;
			else
				$filter_data['paid_status'] = 0;
			$sql .= " AND c2o.paid_status = '".$filter_data['paid_status']."' " ;
		}
		if($filter_data['order_by'] && $filter_data['sort_by']) {
			$sql .= "ORDER BY ".$filter_data['order_by']." ".$filter_data['sort_by']." LIMIT ".$filter_data['start'].", ".$filter_data['limit']."";
		} else {
			$sql .= "ORDER BY o.order_id asc LIMIT ".$filter_data['start'].", ".$filter_data['limit']."";
		}
		$result = $this->db->query($sql);
		return($result->rows);
	}

	public function getProductOptions($order_product_id){
		return $this->db->query("SELECT oo.value FROM ".DB_PREFIX."order_option oo WHERE oo.order_product_id = '".(int)$order_product_id."'")->rows;
	}

	public function getSellerOrders($seller_id){

		$sql = $this->db->query("SELECT DISTINCT o.order_id ,o.date_added, CONCAT(o.firstname ,' ',o.lastname) name ,os.name orderstatus  FROM " . DB_PREFIX ."order_status os LEFT JOIN `".DB_PREFIX ."order` o ON (os.order_status_id = o.order_status_id) LEFT JOIN ".DB_PREFIX ."customerpartner_to_order c2o ON (o.order_id = c2o.order_id) WHERE c2o.customer_id = '".(int)$seller_id."' AND os.language_id = '".$this->config->get('config_language_id')."' ORDER BY o.order_id DESC ");

		return($sql->rows);
	}

	public function getTotalSellerOrders($seller_id,$filter_data){

		$sql = "SELECT DISTINCT op.order_id,c2o.paid_status,c2o.customer as need_to_pay,o.date_added, CONCAT(o.firstname ,' ',o.lastname) name ,os.name orderstatus,op.*, (SELECT group_concat( concat( value) SEPARATOR ', ') FROM ".DB_PREFIX."order_option oo WHERE oo.order_product_id=c2o.order_product_id ) as value  FROM " . DB_PREFIX ."order_status os LEFT JOIN `".DB_PREFIX ."order` o ON (os.order_status_id = o.order_status_id) LEFT JOIN ".DB_PREFIX ."customerpartner_to_order c2o ON (o.order_id = c2o.order_id) LEFT JOIN ".DB_PREFIX."order_product op ON op.order_product_id=c2o.order_product_id WHERE c2o.customer_id = '".(int)$seller_id."' AND os.language_id = '".$this->config->get('config_language_id')."' ";

		if ($this->config->get('marketplace_complete_order_status')) {
		  $sql .= " AND os.order_status_id = '" . $this->config->get('marketplace_complete_order_status') . "' ";
		}

		if(isset($filter_data['date_added_from']) || isset($filter_data['date_added_to'])) {
			if($filter_data['date_added_from'] && $filter_data['date_added_to']){
				$sql .= " AND o.date_added >= '".$filter_data['date_added_from']."' && o.date_added <= '".$filter_data['date_added_to']."' ";
			} else if($filter_data['date_added_from'] && !$filter_data['date_added_to']) {
				$sql .= " AND o.date_added >= '".$filter_data['date_added_from']."' ";
			} else if(!$filter_data['date_added_from'] && $filter_data['date_added_to']) {
				$sql .= " AND o.date_added <= '".$filter_data['date_added_to']."' ";
			}
		}

		if($filter_data['order_id']) {
			$sql .= " AND op.order_id = '".$filter_data['order_id']."' " ;
		}

		if($filter_data['payable_amount']) {
			$sql .= " AND c2o.customer = '".$filter_data['payable_amount']."' " ;
		}

		if($filter_data['quantity']) {
			$sql .= " AND op.quantity = '".$filter_data['quantity']."' " ;
		}

		if($filter_data['order_status']) {
			$sql .= " AND os.name = '".$filter_data['order_status']."' " ;
		}

		if($filter_data['paid_status']) {
			$sql .= " AND c2o.paid_status = '".$filter_data['paid_status']."' " ;
		}

		$result = $this->db->query($sql);

		return(count($result->rows));
	}

	public function getSellerOrderProducts($order_id){

		$sql = $this->db->query("SELECT op.*,c2o.price c2oprice FROM " . DB_PREFIX ."customerpartner_to_order c2o LEFT JOIN " . DB_PREFIX . "order_product op ON (c2o.order_product_id = op.order_product_id AND c2o.order_id = op.order_id) WHERE c2o.order_id = '".(int)$order_id."' ORDER BY op.product_id ");

		return($sql->rows);
	}

	public function getCategories($data = array(), $allowed_categories){

		if (isset($this->request->get['category_mapping']) && $this->request->get['category_mapping']) {
			$categories = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "wk_category_attribute_mapping")->rows;
			if ($categories) {
				$category_ids = array();

				foreach ($categories as $key => $category) {
					$category_ids[] = $category['category_id'];
				}

				$allowed_categories = implode(',',$category_ids);
			}
		}

		if ($allowed_categories) {

			$sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, c1.parent_id, c1.sort_order FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cp.category_id NOT IN (" . $allowed_categories . ")";

		}else{

            $sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, c1.parent_id, c1.sort_order FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sql .= " GROUP BY cp.category_id";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getAdminProducts($seller_id,$data){

		$marketplace_allowed_categories = '';

		$seller_category = $this->db->query("SELECT category_id FROM ".DB_PREFIX."customerpartner_to_category WHERE seller_id = ".(int)$seller_id)->row;

		if (isset($seller_category['category_id'])) {
		  $marketplace_allowed_categories = $seller_category['category_id'];
		} elseif (!$this->config->get('marketplace_allowed_seller_category_type') && $this->config->get('marketplace_allowed_categories')) {
		  foreach ($this->config->get('marketplace_allowed_categories') as $key => $categories) {
		    $marketplace_allowed_categories .= ','. $key;
		  }
		  if ($marketplace_allowed_categories) {
		    $marketplace_allowed_categories = ltrim($marketplace_allowed_categories, ',');
		  }
		}

		$sub_query = "";

		if ($marketplace_allowed_categories) {
		  $sub_query = " AND p.product_id IN (SELECT DISTINCT product_id FROM ".DB_PREFIX."product_to_category WHERE category_id IN (" . $marketplace_allowed_categories . "))";
		}

		if (isset($data['filter_name']) && $data['filter_name']) {
		  $sub_query .= " AND pd.name LIKE '%" . $data['filter_name'] . "%'";
		}

		$sql = "SELECT DISTINCT p.product_id,p.*,pd.name FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.product_id NOT IN (SELECT product_id FROM ".DB_PREFIX."customerpartner_to_product) " . $sub_query . " GROUP BY p.product_id ORDER BY pd.name";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql)->rows;

		return $query;
	}

	public function getAdminProductsTotal($seller_id, $data = array()){

		$marketplace_allowed_categories = '';

		$seller_category = $this->db->query("SELECT category_id FROM ".DB_PREFIX."customerpartner_to_category WHERE seller_id = ".(int)$seller_id)->row;

		if (isset($seller_category['category_id'])) {
		  $marketplace_allowed_categories = $seller_category['category_id'];
		} elseif (!$this->config->get('marketplace_allowed_seller_category_type') && $this->config->get('marketplace_allowed_categories')) {
		  foreach ($this->config->get('marketplace_allowed_categories') as $key => $categories) {
		    $marketplace_allowed_categories .= ','. $key;
		  }
		  if ($marketplace_allowed_categories) {
		    $marketplace_allowed_categories = ltrim($marketplace_allowed_categories, ',');
		  }
		}

		$sub_query = "";

		if ($marketplace_allowed_categories) {
		  $sub_query = " AND p.product_id IN (SELECT DISTINCT product_id FROM ".DB_PREFIX."product_to_category WHERE category_id IN (" . $marketplace_allowed_categories . "))";
		}

		if (isset($data['filter_name']) && $data['filter_name']) {
		  $sub_query .= " AND pd.name LIKE '%" . $data['filter_name'] . "%'";
		}

		$sql = "SELECT DISTINCT p.product_id,p.*,pd.name FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.product_id NOT IN (SELECT product_id FROM ".DB_PREFIX."customerpartner_to_product) " . $sub_query . " GROUP BY p.product_id ORDER BY pd.name";

		$query = $this->db->query($sql)->num_rows;

		return $query;
	}

	public function checkComanyNameExists($companyname){
		$sql = $this->db->query("SELECT * FROM " . DB_PREFIX . "customerpartner_to_customer where companyname = '" . $this->db->escape($companyname) . "'")->row;
		if($sql)
			return $sql;
		return false;
	}

	public function addCategory($categories){

	  foreach ($categories as $key => $value) {
	    $category_id = $key;
	    while(1){
				$parent_category = $this->db->query("SELECT c.parent_id, (SELECT cd.name FROM " . DB_PREFIX . "category_description cd WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd.category_id = c.parent_id) AS name FROM " . DB_PREFIX . "category c WHERE c.category_id = " . (int)$category_id)->row;

	      if (isset($parent_category['parent_id']) && $parent_category['parent_id']) {
	        $categories[$parent_category['parent_id']] = $parent_category['name'];
	        $category_id = $parent_category['parent_id'];
	      } else {
	        break;
	      }
	    }
	  }
		return $categories;
	}
	public function deleteCustomerActivity($customer_id) {
		$customer_activities = $this->db->query("SELECT activity_id FROM " . DB_PREFIX . "seller_activity WHERE seller_id = " . (int)$customer_id . "")->rows;
		if ($customer_activities) {
			foreach ($customer_activities as $activity) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "mp_customer_activity WHERE customer_activity_id = " . (int)$activity['activity_id'] . "");
			}
		}
		$this->db->query("DELETE FROM " . DB_PREFIX . "seller_activity WHERE seller_id = " . (int)$customer_id . "");
	}
}
?>
