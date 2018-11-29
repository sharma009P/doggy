<?php
class ModelExtensionModuleWkmpfollowseller extends Model {

    /**
     * [getHighratedSells get high rated sellers]
     * @return [type] [array]
     */
	public function getHighratedSellers(){
		  $this->load->model('customerpartner/master');

		  $sum = 0;

	      $total_seller=$this->db->query("SELECT * FROM `" . DB_PREFIX . "customerpartner_to_customer` WHERE is_partner=1")->rows;

	      if (!empty($total_seller)) {

		      	foreach ($total_seller as $value) {
					$avg[$value['customer_id']] = $this->model_customerpartner_master->getAverageFeedback($value['customer_id']);
		      	}

		      	arsort($avg);

				foreach($avg as $x=>$x_value){

				      $sort_avg[$x] = $x_value;
				}

				$flag = 0;
				foreach ($sort_avg as $seller_id => $value) {

				    	if ($flag == 3) {

				    		break ;
				    	}else{

                            if ($value) {

                            	$this->load->model('customerpartner/master');
				    		    $partner[$seller_id] = $this->model_customerpartner_master->getProfile($seller_id);
				    		    $partner[$seller_id]['rating'] = $value;
                            }

				    		$flag++;
				    	}
				}

				if (isset($partner) && $partner) {

				    return $partner;

				}else{

					 return false;

				}


	      }

    }

    /**
     * [getHighratedProducts get high rated products]
     * @return [type] [array]
     */
    public function getHighratedProducts($customer_id){

          $review = $this->db->query("SELECT DISTINCT op.product_id,op.tax_class_id,pd.name,op.model,op.image,op.price, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r WHERE r.product_id = op.product_id AND r.status = '1' GROUP BY r.product_id) AS rating FROM `" . DB_PREFIX . "product` op LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (pd.product_id = op.product_id) RIGHT JOIN `" . DB_PREFIX . "review` r ON (r.product_id = op.product_id) WHERE op.product_id IN (SELECT product_id FROM `" . DB_PREFIX . "customerpartner_to_product` WHERE customer_id IN (SELECT seller_id FROM `" . DB_PREFIX . "wk_followseller` WHERE customer_id='". (int)$this->db->escape($customer_id) ."')) AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'")->rows;


          return $review;

    }

    /**
     * [highFollowedPartners get high followed sellers]
     * @return [type] [array]
     */
    public function highFollowedPartners(){

          $total_seller=$this->db->query("SELECT * FROM `" . DB_PREFIX . "customerpartner_to_customer` WHERE is_partner=1")->rows;

         if (!empty($total_seller)) {

         	 foreach ($total_seller as $key => $seller) {

         	 	$seller_row[$seller['customer_id']] = $this->db->query("SELECT count(*) AS total FROM `" . DB_PREFIX . "wk_followseller` WHERE seller_id = '".$this->db->escape($seller['customer_id'])."'")->row;
            }



          foreach ($seller_row as $key => $value) {

          	  $for_sorting[$key] = $value['total'];
          }

          arsort($for_sorting);

		foreach($for_sorting as $x=>$x_value){

		      $sorted_sellers[$x] = $x_value;
		    }

            $flag = 0;

			foreach ($sorted_sellers as $seller_id => $value) {

			    	if ($flag == 3) {

			    		break ;
			    	}else{

                        if ($value) {

                        	$this->load->model('customerpartner/master');
			    		    $partner[$seller_id] = $this->model_customerpartner_master->getProfile($seller_id);
			    		    $partner[$seller_id]['followers'] = $value;
                        }

			    		$flag++;
			    	}
			}

			if (isset($partner) && $partner) {

				 return $partner;
			}else{

				 return false;

			}

	    }

   }

   /**
    * [similarProducts get similar products of same category]
    * @param  [type] $product_id [product id]
    * @return [type]             [array]
    */
   public function similarProducts($product_id){

     $result = $this->db->query("SELECT DISTINCT oc.product_id, oc.tax_class_id, opd.name, oc.model, oc.image, oc.price, (SELECT AVG(rating) FROM " . DB_PREFIX . "review r WHERE r.product_id = oc.product_id AND r.status = '1' GROUP BY r.product_id) AS rating FROM `" . DB_PREFIX . "product_to_category` pc LEFT JOIN `" . DB_PREFIX . "product` oc ON (pc.product_id = oc.product_id) LEFT JOIN `" . DB_PREFIX . "product_description` opd ON (opd.product_id = oc.product_id) RIGHT JOIN `" . DB_PREFIX . "review` r ON (r.product_id = opd.product_id) WHERE pc.category_id IN (SELECT category_id FROM `" . DB_PREFIX . "product_to_category` WHERE product_id='". $this->db->escape($product_id) ."') AND pc.product_id != '".$this->db->escape($product_id)."' ")->rows;

     return $result;

   }


    /**
     * [addFollowSeller add customer followed seller]
     * @param [type] $data [customer's id and seller's id]
     */
	public function addFollowSeller($data){

		 $this->db->query("INSERT INTO `" . DB_PREFIX . "wk_followseller` SET customer_id= '" . (int)$this->db->escape($data['customer_id']) . "',seller_id = '" . (int)$this->db->escape($data['seller_id']) . "'");

         if ($this->config->get('wk_mpfollowseller_followed_mail_status') && $this->config->get('wk_mpfollowseller_followed_mail_subject')) {
         	 $this->sellermail($data);
         }

         if ($this->config->get('wk_mpfollowseller_followed_customer_mail_status') && $this->config->get('wk_mpfollowseller_followed_customer_mail_subject')) {
         	 $this->customermail($data);
         }
		 return 'done';
	}

    /**
     * [checkCustomerFollow check whether this customer follow this seller or not]
     * @param  [type] $customer_id [customer id]
     * @param  [type] $seller_id   [seller id]
     * @return [type]              [array]
     */
	public function checkCustomerFollow($customer_id,$seller_id){

		$query=$this->db->query("SELECT * FROM `" . DB_PREFIX . "wk_followseller` WHERE customer_id= '" . (int)$this->db->escape($customer_id) . "' AND seller_id= '" . (int)$this->db->escape($seller_id) . "'");

		return $query->row;
	}

    /**
     * [unFollowSeller unfollow seller]
     * @param  [type] $followseller_id [followseller_id of table wk_followseller]
     * @return [type]                  [success message]
     */
	public function unFollowSeller($followseller_id){

		$this->db->query("DELETE FROM `" . DB_PREFIX . "wk_followseller` WHERE followseller_id = '" . (int)$this->db->escape($followseller_id) . "'");

		return 'done';
	}

    /**
     * [getCustomerDetails get customer details]
     * @param  [type] $customer_id [customer's id]
     * @return [type]              [array]
     */
	public function getCustomerDetails($customer_id){

         return $this->db->query("SELECT * FROM " .DB_PREFIX ."customer WHERE customer_id = '".$this->db->escape($customer_id)."'")->row;
	}

    /**
     * [getHighRatedProductDetails get high rated product details]
     * @param  [type] $seller_id [seller id]
     * @return [type]            [array]
     */
	public function getHighRatedProductDetails($seller_id){

		$result = $this->db->query("SELECT * FROM " .DB_PREFIX ."customerpartner_to_product cp LEFT JOIN " .DB_PREFIX ."product op ON(cp.product_id = op.product_id) LEFT JOIN " .DB_PREFIX ."product_description opd ON(cp.product_id = opd.product_id) WHERE cp.customer_id = '".$this->db->escape($seller_id)."' ORDER BY op.viewed DESC LIMIT 4")->rows;

		return $result;
	}

    /**
     * [sellermail Seller mail]
     * @param  [type] $data [details]
     * @return [type]       [no]
     */
	public function sellermail($data) {

        $this->load->model('customerpartner/master');

        /**
         * [$partner get seller profile details]
         * @var [array]
         */
		$partner = $this->model_customerpartner_master->getProfile($data['seller_id']);

        /**
         * [$customer get customers details for mail]
         * @var [array]
         */
		$customer = $this->getCustomerDetails($data['customer_id']);

            $subject=$this->config->get('wk_mpfollowseller_followed_mail_subject');

            $sellermessage=$this->config->get('wk_mpfollowseller_followed_mail_message');

 			$find=array(
 				'{seller_name}',
 				'{customer_first_name}',
 				'{customer_last_name}'
 				);

 			$replace= array(
 				'seller_name'=>$partner['firstname'].' '.$partner['lastname'],
 				'customer_first_name'=>$customer['firstname'],
 				'customer_last_name'=>$customer['lastname'],
 				);

 			$html = str_replace($find, $replace,$sellermessage);
			$admin_mail = $this->config->get('marketplace_adminmail');

			if(VERSION == '2.0.1.1') {
				$mail = new Mail($this->config->get('config_mail'));
			}else{
				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
			}
				$mail->setFrom($admin_mail);
				$mail->setTo($partner['email']);
				$mail->setSender($admin_mail);
				$mail->setSubject($subject);
				$mail->setHtml(html_entity_decode($html));
				$mail->send();
		}

        /**
         * [customermail customer mail]
         * @param  [type] $data [detail]
         * @return [type]       [no]
         */
		public function customermail($data) {

        $this->load->model('customerpartner/master');

        /**
         * [$partner get seller profile details]
         * @var [array]
         */
		$partner = $this->model_customerpartner_master->getProfile($data['seller_id']);

        /**
         * [$customer get customers details for mail]
         * @var [array]
         */
		$customer = $this->getCustomerDetails($data['customer_id']);

        /**
         * [$highratedproducts get high rated products of seller]
         * @var [array]
         */
		$highratedproducts = $this->getHighRatedProductDetails($data['seller_id']);

        $this->load->model('tool/image');
		foreach ($highratedproducts as $key => $value) {

	        if ($value['image']) {
				$highratedproducts[$key]['image'] = $this->model_tool_image->resize($value['image'], 120, 120);
			} else {
				$highratedproducts[$key]['image'] = $this->model_tool_image->resize('no_image.png', 120, 120);
			}
		}
		$data['highratedproducts'] = $highratedproducts;

		$highratedproductshtml = $this->response->setOutput($this->load->view('extension/module/wk_mpfollowseller_customer_mail', $data));

            $subject=$this->config->get('wk_mpfollowseller_followed_customer_mail_subject');

            $customer_message = $this->config->get('wk_mpfollowseller_followed_customer_mail_message');

 			$find=array(
 				'{seller_name}',
 				'{customer_first_name}',
 				'{customer_last_name}',
 				'{High_viewed_products}',
 				);

 			$replace= array(
 				'seller_name'=>$partner['firstname'].' '.$partner['lastname'],
 				'customer_first_name'=>$customer['firstname'],
 				'customer_last_name'=>$customer['lastname'],
 				'high_viewed_products' => $highratedproductshtml,
 				);

 			$html = str_replace($find, $replace,$customer_message);

			$admin_mail=$this->config->get('marketplace_adminmail');

			if(VERSION == '2.0.1.1') {
				$mail = new Mail($this->config->get('config_mail'));
			}else{
				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
			}
				$mail->setFrom($admin_mail);
				$mail->setTo($customer['email']);
				$mail->setSender($admin_mail);
				$mail->setSubject($subject);
				$mail->setHtml(html_entity_decode($html));
				$mail->send();
		}

        /**
         * [getFollowers get all followers details]
         * @param  array  $data [filter array]
         * @return [type]       [array]
         */
		public function getFollowers($data = array()){

			$sql = "SELECT * FROM ".DB_PREFIX ."wk_followseller wfs LEFT JOIN ".DB_PREFIX ."customer oc ON (oc.customer_id = wfs.customer_id) WHERE wfs. seller_id = '" . (int)$this->customer->getId() . "'";

			if (!empty($data['filter_name'])) {
			  $sql .= " AND oc.firstname LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		    }

			$sort_data = array(

				'oc.firstname',
				'oc.email',

			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY oc.firstname";
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

			}

			$query = $this->db->query($sql);

			return $query->rows;

	}

    /**
     * [getFollowedSeller get all following seller]
     * @param  array  $data [filter array]
     * @return [type]       [array]
     */
	public function getFollowedSeller($data = array()){

		$sql = "SELECT * FROM ".DB_PREFIX ."wk_followseller wfs LEFT JOIN ".DB_PREFIX ."customer oc ON (oc.customer_id = wfs.seller_id) WHERE wfs. customer_id = '" . (int)$this->customer->getId() . "'";

			if (!empty($data['filter_name'])) {
			  $sql .= " AND oc.firstname LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		    }

			$sort_data = array(

				'oc.firstname',
				'oc.email',

			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY oc.firstname";
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

			}

			$query = $this->db->query($sql);

			return $query->rows;


	}

    /**
     * [addnewsletter add seller newsletter]
     * @param  [type] $data [newsletter details]
     * @return [type]       [array]
     */
	public function addnewsletter($data){

		$check = $this->db->query("SELECT * FROM `" . DB_PREFIX . "wk_followseller_newsletter` WHERE seller_id= '" . (int)$this->customer->getId() . "'")->row;

		if (empty($check)) {

			$this->db->query("INSERT INTO `" . DB_PREFIX . "wk_followseller_newsletter` SET seller_id= '" . (int)$this->customer->getId() . "',subject = '" .$this->db->escape($data['wk_mpfollowseller_newsletter_subject']) . "',message = '".$this->db->escape($data['wk_mpfollowseller_newsletter_message'])."'");
		}else{

           $this->db->query("UPDATE " . DB_PREFIX . "wk_followseller_newsletter SET subject = '" .$this->db->escape($data['wk_mpfollowseller_newsletter_subject']) . "',message = '".$this->db->escape($data['wk_mpfollowseller_newsletter_message'])."' WHERE seller_id = '" .(int)$this->customer->getId() . "'");

		}


	}

    /**
     * [getnewsletter get seller newsletter]
     * @return [type] [array]
     */
	public function getnewsletter(){

		$result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "wk_followseller_newsletter` WHERE seller_id= '" . (int)$this->customer->getId() . "'")->row;

		return $result;
	}

    /**
     * [getCustomerGroupId get customer group id]
     * @return [type] [array]
     */
	public function getCustomerGroupId(){

         $result = $this->db->query("SELECT customer_group_id FROM `" . DB_PREFIX . "customer` WHERE customer_id= '" . (int)$this->customer->getId() . "'")->row;

		return $result;

	}

    /**
     * [sendNewsletter send newsletter to customer one by one]
     * @param  [type] $customer_id [customer id]
     * @return [type]              [no]
     */
	public function sendNewsletter($customer_id){

        if ($this->config->get('wk_mpfollowseller_customer_newsletter_set_status')) {

        	$sellernewsletter = $this->getnewsletter();

        	$seller_details = $this->getCustomerDetails($this->customer->getId());

        	$customer_details = $this->getCustomerDetails($customer_id);

        	$find=array(
 				'{customer_first_name}',
 				'{customer_last_name}',
 				);

 			$replace= array(
 				'customer_first_name'=>$customer_details['firstname'],
 				'customer_last_name'=>$customer_details['lastname'],
 				);

 			$html=str_replace($find, $replace,$sellernewsletter['message']);
			$admin_mail=$this->config->get('marketplace_adminmail');

        	if(VERSION == '2.0.1.1') {
				$mail = new Mail($this->config->get('config_mail'));
			}else{
				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
			}
			$mail->setFrom($seller_details['email']);
			$mail->setTo($customer_details['email']);
			$mail->setSender($seller_details['email']);
			$mail->setSubject($sellernewsletter['subject']);
			$mail->setHtml(html_entity_decode($html));
			$mail->send();


        }else if($this->config->get('wk_mpfollowseller_newsletter_subject') && $this->config->get('wk_mpfollowseller_newsletter_message')){

        	$customer_details = $this->getCustomerDetails($customer_id);

        	$subject = $this->config->get('wk_mpfollowseller_newsletter_subject');
        	$message = $this->config->get('wk_mpfollowseller_newsletter_message');

        	$find=array(
 				'{customer_first_name}',
 				'{customer_last_name}',
 				);

 			$replace= array(
 				'customer_first_name'=>$customer_details['firstname'],
 				'customer_last_name'=>$customer_details['lastname'],
 				);

 			$html=str_replace($find, $replace,$message);
			$admin_mail=$this->config->get('marketplace_adminmail');

			if(VERSION == '2.0.1.1') {
				$mail = new Mail($this->config->get('config_mail'));
			}else{
				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
			}
			$mail->setFrom($admin_mail);
			$mail->setTo($customer_details['email']);
			$mail->setSender($admin_mail);
			$mail->setSubject($subject);
			$mail->setHtml(html_entity_decode($html));
			$mail->send();

        }
	}

    /**
     * [getTotalFollowedSeller get total number of following seller]
     * @return [type] [integer]
     */
	public function getTotalFollowedSeller(){

	    $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "wk_followseller` WHERE customer_id = '".(int)$this->customer->getId()."'");

		return $query->row['total'];
	}

    /**
     * [getTotalFollowSeller get total number of follow seller]
     * @return [type] [integer]
     */
	public function getTotalFollowSeller(){

	    $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "wk_followseller` WHERE seller_id = '".(int)$this->customer->getId()."'");

		return $query->row['total'];
	}
}
