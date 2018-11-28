<?php
class ControllerExtensionModuleWkmpfollowseller extends Controller {

    public function index(){
    	$data = $this->language->load('extension/module/marketplace');
        $data = array_merge($data, $this->language->load('extension/module/wk_mpfollowseller'));

        $data['partners'] = $data['latest'] = $data['Top_Products_Of_Followed_Seller_value'] = $data['Top_Seller_value'] =  $data['Similar_Top_Products_value'] = array();

        $Top_Seller_value = $Top_Followed_Seller_value = array();

        $this->load->model('customerpartner/master');
        $this->load->model('extension/module/wk_mpfollowseller');
        $this->load->model('tool/image');

        $config = array(
        	'module_wk_mpfollowseller_status',
        	'wk_mpfollowseller_customergroup_id',
        	'wk_mpfollowseller_newsletter_subject',
        	'wk_mpfollowseller_newsletter_message',
        	'wk_mpfollowseller_followed_mail_status',
        	'wk_mpfollowseller_followed_mail_subject',
        	'wk_mpfollowseller_followed_mail_message',
        	'wk_mpfollowseller_followed_customer_mail_status',
        	'wk_mpfollowseller_followed_customer_mail_subject',
        	'wk_mpfollowseller_followed_customer_mail_message',
          'wk_mpfollowseller_other_option_id',
        );

        foreach ($config as $value) {
        	$data[$value] = $this->config->get($value);
        }

        /**
         * [$patnner get all followed sellers]
         * @var [array]
         */
        $Top_Seller_value= $this->model_extension_module_wk_mpfollowseller->getHighratedSellers();

       	$visibilityOfMod = false;
        if (!empty($Top_Seller_value)) {
        	foreach ($Top_Seller_value as $key => $partner) {
		    	if($this->config->get('marketplace_product_name_display')) {
					if($this->config->get('marketplace_product_name_display') == 'sn') {
						$Top_Seller_value[$key]['displayName'] = $partner['firstname']." ".$partner['lastname'];
					} else if($this->config->get('marketplace_product_name_display') == 'cn') {
						$Top_Seller_value[$key]['displayName'] = $partner['companyname'];
					} else {
						$Top_Seller_value[$key]['displayName'] = $partner['companyname']." (".$partner['firstname']." ".$partner['lastname'].")";
					}
				}

				if($this->config->get('marketplace_product_image_display')){
					$show_image = $this->config->get('marketplace_product_image_display');
					if(($show_image == 'avatar') && $partner['avatar']) {
						$thumb = $this->model_tool_image->resize($partner['avatar'], 120, 120);
					}elseif (($show_image == 'companylogo') && $partner['companylogo']) {
						$thumb = $this->model_tool_image->resize($partner['companylogo'], 120, 120);
					}elseif (($show_image == 'companybanner') && $partner['companybanner']) {
						$thumb = $this->model_tool_image->resize($partner['companylogo'], 120, 120);
					}elseif($this->config->get('marketplace_default_image_name')){
						$thumb = $this->model_tool_image->resize($this->config->get('marketplace_default_image_name'), 120, 120);
					}else{
						$thumb = $this->model_tool_image->resize('no_image.png', 120, 120);
					}
				}

				$Top_Seller_value[$key]['thumb'] = $thumb;

				$Top_Seller_value[$key]['sellerHref'] = $this->url->link('customerpartner/profile&id='.$partner['customer_id'],'','SSL');

				$Top_Seller_value[$key]['collectionHref'] = $this->url->link('customerpartner/profile&id='.$partner['customer_id'],'&collection','SSL');

				$Top_Seller_value[$key]['name'] = $partner['firstname'].' '.$partner['lastname'];

				$Top_Seller_value[$key]['total_products'] = $this->model_customerpartner_master->getPartnerCollectionCount($partner['customer_id']);


				if ($this->customer->getId()) {
			         /**
			         * [$checkCustomerFollow check whether this customer follow this seller or not ]
			         * @var [array]
			         */
			        $checkCustomerFollow[$partner['customer_id']] = $this->model_extension_module_wk_mpfollowseller->checkCustomerFollow($this->customer->getId(),$partner['customer_id']);

			        if (!empty($checkCustomerFollow[$partner['customer_id']])) {
			        	$customerfollows[$partner['customer_id']] = 1;
			        }else{
			             $customerfollows[$partner['customer_id']] = 0;
			        }

			        $data['customerfollows'] = $customerfollows;
			        $data['customer_id'] = $this->customer->getId();
		        }
            }

            if($Top_Seller_value) {
            	$visibilityOfMod = true;
            }

            $data['Top_Seller_value']= $Top_Seller_value;
        }

        if ($this->customer->getId()) {
        	/**
        	 * [$reviews fetch all high rated products]
        	 * @var [array]
        	 */
        	$Top_Products_Of_Followed_Seller_value = $this->model_extension_module_wk_mpfollowseller->getHighratedProducts($this->customer->getId());

        	if (!empty($Top_Products_Of_Followed_Seller_value)) {
				foreach($Top_Products_Of_Followed_Seller_value as $key => $result){

					if ($result['rating']) {
						if ($result['image']) {
							$image = $this->model_tool_image->resize($result['image'], 120, 120);
						} else {
							$image = $this->model_tool_image->resize('no_image.png', 120, 120);
						}

						if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
							$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')),$this->session->data['currency']);
						} else {
							$price = false;
						}


						if ($this->config->get('config_tax')) {
							$tax = $this->currency->format($result['price'],$this->session->data['currency']);
						} else {
							$tax = false;
						}

						if ($this->config->get('config_review_status')) {
							$rating = (int)$result['rating'];
						} else {
							$rating = false;
						}

						$data['Top_Products_Of_Followed_Seller_value'][] = array(
							'product_id'  => $result['product_id'],
							'thumb'       => $image,
							'name'        => $result['name'],
							'price'       => $price,
							'tax'         => $tax,
							'rating'      => $result['rating'],
							'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
						);
				    }
				}

				if($data['Top_Products_Of_Followed_Seller_value']) {
					$visibilityOfMod = true;
				}
        	}
        }


        if (isset($this->request->get['product_id'])) {
        	/**
        	 * [$similar_product fetch similar products of followed sellers]
        	 * @var [array]
        	 */
        	$Similar_Top_Products_value = $this->model_extension_module_wk_mpfollowseller->similarProducts($this->request->get['product_id']);
        	if (!empty($Similar_Top_Products_value)) {

						foreach($Similar_Top_Products_value as $key => $result){

                            if ($result['rating']) {

                            	if ($result['image']) {
								$image = $this->model_tool_image->resize($result['image'], 120, 120);
								} else {
									$image = $this->model_tool_image->resize('no_image.png', 120, 120);
								}

								if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
									$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')),$this->session->data['currency']);
								} else {
									$price = false;
								}


								if ($this->config->get('config_tax')) {
									$tax = $this->currency->format($result['price'],$this->session->data['currency']);
								} else {
									$tax = false;
								}

								if ($this->config->get('config_review_status')) {
									$rating = (int)$result['rating'];
								} else {
									$rating = false;
								}

								$data['Similar_Top_Products_value'][] = array(
									'product_id'  => $result['product_id'],
									'thumb'       => $image,
									'name'        => $result['name'],
									'price'       => $price,
									'tax'         => $tax,
									'rating'      => $result['rating'],
									'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
								);

                            }

						}
				if($data['Similar_Top_Products_value']) {
					$visibilityOfMod = true;
				}

        	}
        }

        /**
         * [$followed_partners fetch high followed partners]
         * @var [array]
         */
        $Top_Followed_Seller_value = $this->model_extension_module_wk_mpfollowseller->highFollowedPartners();
        $data['Top_Followed_Seller_value'] = array();
        if (!empty($Top_Followed_Seller_value)) {
        	foreach ($Top_Followed_Seller_value as $key => $partner) {

	        	if($this->config->get('marketplace_product_name_display')) {
					if($this->config->get('marketplace_product_name_display') == 'sn') {
						$Top_Followed_Seller_value[$key]['displayName'] = $partner['firstname']." ".$partner['lastname'];
					} else if($this->config->get('marketplace_product_name_display') == 'cn') {
						$Top_Followed_Seller_value[$key]['displayName'] = $partner['companyname'];
					} else {
						$Top_Followed_Seller_value[$key]['displayName'] = $partner['companyname']." (".$partner['firstname']." ".$partner['lastname'].")";
					}
			    }

			   if($this->config->get('marketplace_product_image_display')){
					$show_image = $this->config->get('marketplace_product_image_display');
					if(($show_image == 'avatar') && $partner['avatar']) {
						$thumb = $this->model_tool_image->resize($partner['avatar'], 120, 120);
					}elseif (($show_image == 'companylogo') && $partner['companylogo']) {
						$thumb = $this->model_tool_image->resize($partner['companylogo'], 120, 120);
					}elseif (($show_image == 'companybanner') && $partner['companybanner']) {
						$thumb = $this->model_tool_image->resize($partner['companylogo'], 120, 120);
					}elseif($this->config->get('marketplace_default_image_name')){
						$thumb = $this->model_tool_image->resize($this->config->get('marketplace_default_image_name'), 120, 120);
					}else{
						$thumb = $this->model_tool_image->resize('no_image.png', 120, 120);
					}
				}

				$Top_Followed_Seller_value[$key]['thumb'] = $thumb;

				$Top_Followed_Seller_value[$key]['sellerHref'] = $this->url->link('customerpartner/profile&id='.$partner['customer_id'],'','SSL');
				$Top_Followed_Seller_value[$key]['collectionHref'] = $this->url->link('customerpartner/profile&id='.$partner['customer_id'],'&collection','SSL');
				$Top_Followed_Seller_value[$key]['name'] = $partner['firstname'].' '.$partner['lastname'];
				$Top_Followed_Seller_value[$key]['total_products'] = $this->model_customerpartner_master->getPartnerCollectionCount($partner['customer_id']);
				if ($partner['followers']) {

					$Top_Followed_Seller_value[$key]['total_followers'] = $partner['followers'];
				}else{

					$Top_Followed_Seller_value[$key]['total_followers'] = '';
				}

				if ($this->customer->getId()) {
				         /**
				         * [$checkCustomerFollow check whether this customer follow this seller or not ]
				         * @var [array]
				         */
				        $checkCustomerFollow[$partner['customer_id']] = $this->model_extension_module_wk_mpfollowseller->checkCustomerFollow($this->customer->getId(),$partner['customer_id']);

				        if (!empty($checkCustomerFollow[$partner['customer_id']])) {
				        	$customerfollows[$partner['customer_id']] = 1;
				        }else{
				             $customerfollows[$partner['customer_id']] = 0;
				        }

				        $data['high_followed_customerfollows'] = $customerfollows;
				        $data['customer_id'] = $this->customer->getId();
		        }

            }
            if($Top_Followed_Seller_value) {
            	$visibilityOfMod = true;
            }
            $data['Top_Followed_Seller_value']= $Top_Followed_Seller_value;
        }
     	$data['visibilityOfMod'] = $visibilityOfMod;
  		$data['follow_seller'] = $this->url->link('extension/module/wk_mpfollowseller/followseller','','SSL');

      return $this->load->view('extension/module/wk_mpfollowseller', $data);
    }

	public function followseller(){

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			$this->load->model('extension/module/wk_mpfollowseller');

            /**
             * [$result check whether this customer follow this seller or not]
             * @var [array]
             */
			$result = $this->model_extension_module_wk_mpfollowseller->checkCustomerFollow($this->request->post['customer_id'],$this->request->post['seller_id']);

		    if (empty($result)) {
		    	/**
		    	 * [$result add new follower]
		    	 * @var [string]
		    	 */
		    	$result = $this->model_extension_module_wk_mpfollowseller->addFollowSeller($this->request->post);
		    }else{

                /**
                 * [$result unfollow seller]
                 * @var [string]
                 */
		    	$result = $this->model_extension_module_wk_mpfollowseller->unFollowSeller($result['followseller_id']);
		    }

			$json['success'] = $result;

			$this->response->setOutput(json_encode($json));

		}
	}
}
