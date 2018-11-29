<?php
class ModelWkmpfollowsellerWkmpfollowseller extends Model {

    /**
     * [getFollowedseller get all followed seller details]
     * @return [type] [array]
     */
	public function getFollowedseller(){

        $seller = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` oc LEFT JOIN  `" . DB_PREFIX . "customerpartner_to_customer` cc ON (oc.customer_id = cc.customer_id) WHERE cc.is_partner=1");

        foreach ($seller->rows as $value) {

			$query = $this->db->query("SELECT count(*) AS followers FROM `" . DB_PREFIX . "wk_followseller` WHERE seller_id = '".$this->db->escape($value['customer_id'])."'")->row;

			if ($query['followers']) {

				$followers[] = array(

                       'seller_id' => $value['customer_id'],
                       'name'      => $value['firstname'] ." ".$value['lastname'],
                       'followers' => $query['followers'],
                       'view'    => $this->url->link('wk_mpfollowseller/wk_mpfollowseller/view', 'user_token=' . $this->session->data['user_token'] . '&seller_id=' . $value['customer_id'], 'SSL')
					);
			}


        }

        if (isset($followers)) {

        	return $followers;
        }else{

        	return false;
        }

	}

    /**
     * [getFollowingcustomer get all following customer]
     * @param  [type] $seller_id [seller id]
     * @return [type]            [array]
     */
	public function getFollowingcustomer($seller_id){

		 $customer = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` oc LEFT JOIN  `" . DB_PREFIX . "wk_followseller` wf ON (oc.customer_id = wf.customer_id) WHERE wf.seller_id='".$this->db->escape($seller_id)."'");

	    return $customer->rows;
	}

    /**
     * [deletefollowseller delete followed seller]
     * @param  [type] $seller_id [seller id]
     * @return [type]            [array]
     */
	public function deletefollowseller($seller_id){

     $this->db->query("DELETE FROM `" . DB_PREFIX . "wk_followseller` WHERE seller_id = '".$this->db->escape($seller_id)."'");

	}

    /**
     * [deletefollowingcustomer delete following customer]
     * @param  [type] $customer_id [customer id]
     * @param  [type] $seller_id   [seller id]
     * @return [type]              [no]
     */
	public function deletefollowingcustomer($customer_id,$seller_id){

      $this->db->query("DELETE FROM `" . DB_PREFIX . "wk_followseller` WHERE seller_id = '".$this->db->escape($seller_id)."' AND customer_id = '".$this->db->escape($customer_id)."'");
	}

    /**
     * [getTotalFollowedseller get total follow seller]
     * @return [type] [integer]
     */
	public function getTotalFollowedseller(){

		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "wk_followseller`");

		return $query->row['total'];
	}

    /**
     * [getTotalFollowingcustomer get total following customer]
     * @param  [type] $seller_id [seller id]
     * @return [type]            [integer]
     */
	public function getTotalFollowingcustomer($seller_id){

		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "wk_followseller` WHERE seller_id = '".$this->db->escape($seller_id)."'");

		return $query->row['total'];
	}

}
