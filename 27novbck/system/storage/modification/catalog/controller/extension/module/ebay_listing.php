<?php
class ControllerExtensionModuleEbayListing extends Controller {
	public function index() {
		if ($this->config->get('ebay_status') == 1) {
			$this->load->language('extension/module/ebay');
			
			$this->load->model('tool/image');
			$this->load->model('extension/openbay/ebay_product');

			$data['heading_title'] = $this->language->get('heading_title');


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

			$products = $this->cache->get('ebay_listing.' . md5(serialize($products)));

			if (!$products) {
				$products = $this->model_extension_openbay_ebay_product->getDisplayProducts();
				
				$this->cache->set('ebay_listing.' . md5(serialize($products)), $products);
			}

			foreach($products['products'] as $product) {
				if (isset($product['pictures'][0])) {
					$image = $this->model_extension_openbay_ebay_product->resize($product['pictures'][0], $this->config->get('ebay_listing_width'), $this->config->get('ebay_listing_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('ebay_listing_width'), $this->config->get('ebay_listing_height'));
				}


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
					'thumb' => $image, 
					'name'  => base64_decode($product['Title']), 
					'price' => $this->currency->format($product['priceGross'], $this->session->data['currency']), 
					'href'  => (string)$product['link']
				);
			}

			$data['tracking_pixel'] = $products['tracking_pixel'];

			return $this->load->view('extension/module/ebay', $data);
		}
	}
}