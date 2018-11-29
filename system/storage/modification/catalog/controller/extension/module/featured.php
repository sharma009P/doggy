<?php
class ControllerExtensionModuleFeatured extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/featured');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');
        
        $this->load->model('account/customerpartner');
		$this->load->model('customerpartner/master');                


				$data['module_wgtm_status'] = $this->config->get('module_wgtm_status');
				$this->load->model('catalog/category');
				$data['wcurencysymbol'] = $this->session->data['currency'];
				if(isset($setting['name'])){
					$data['wmodulename'] = $setting['name'];
				}else{
					$data['wmodulename'] = '';
				}
				$data['wgtms'] = array();
			
		$data['products'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}

		if (!empty($setting['product'])) {
			$products = array_slice($setting['product'], 0, (int)$setting['limit']);

			foreach ($products as $product_id) {
				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info) {
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if ((float)$product_info['special']) {
						$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$special = false;
					}

					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $product_info['rating'];
					} else {
						$rating = false;
					}
                    
                    $seller_id = $this->model_customerpartner_master->getPartnerIdBasedonProduct($product_info['product_id'])['id'];

		//print_r($seller_id);
        //die;        
		
				$partner = $this->model_customerpartner_master->getProfile($seller_id);
                

				if(isset($result) && isset($result['product_id'])) {
					$wgtm = $result;
				}elseif (isset($product_info) && isset($product_info['product_id'])) {
					$wgtm = $product_info;
				}
				if(isset($wgtm)){
					$wprice = $this->currency->format($wgtm['price'], $this->session->data['currency'], '', false);
					$getcategories = $this->model_catalog_product->getCategories($wgtm['product_id']);
					$categories = array();
					if($getcategories){
						foreach($getcategories as $cate){
							$getcategory = $this->model_catalog_category->getCategory($cate['category_id']);
							$categories[] = isset($getcategory['name']) ? $getcategory['name'] : '';
						}
					}
					$categories = implode(", ", $categories);

					$data['wgtms'][] = array(
						'manufacturer' => $wgtm['manufacturer'],
						'categories'   => $categories,
						'price'	  	   => $wprice,
						'product_id'   => $wgtm['product_id'],
						'name'         => $wgtm['name']
					);
				}
			
					$data['products'][] = array(
						'product_id'  => $product_info['product_id'],
						'thumb'       => $image,
						'name'        => $product_info['name'],
						'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                        'sellername'  => $partner['companyname'],
					    'sellerhref'  => $this->url->link('customerpartner/profile', '&id=' . $partner['customer_id'] ,true),
					);
				}
			}
		}

		if ($data['products']) {
			return $this->load->view('extension/module/featured', $data);
		}
	}
}