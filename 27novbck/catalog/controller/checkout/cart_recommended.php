<?php
class ControllerCheckoutCartRecommended extends Controller {
	public function index() {
	    $this->load->language('checkout/cart');
       
		$setting['limit'] = '5';
        $setting['width'] = '200';
        $setting['height'] = '200';
        
		$this->load->model('catalog/product');

		$this->load->model('tool/image');
        
        $this->load->model('account/customerpartner');
		$this->load->model('customerpartner/master');                

		$data['products'] = array();

		$results = $this->model_catalog_product->getCartRecommended($setting['limit']);
        
        if ($results) {
			foreach ($results as $result) {
                
                if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}
                
                
                $seller_id = $this->model_customerpartner_master->getPartnerIdBasedonProduct($result['product_id'])['id'];
                
                $partner = $this->model_customerpartner_master->getProfile($seller_id);
                
                $product_info = $this->model_catalog_product->getProduct($result['product_id']);
                $rec_file_upload_1 = '';
                if(!empty($result['rec_file_upload_1'])){
                    $rec_file_upload_1 = $this->model_tool_image->resize_recommend_img($result['product_id'],$result['rec_file_upload_1'], '200', '200');
                }
                
                $rec_file_upload_2 = '';
                if(!empty($result['rec_file_upload_2'])){
                    $rec_file_upload_2 = $this->model_tool_image->resize_recommend_img($result['product_id'],$result['rec_file_upload_2'], '200', '200');
                }
                $_data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $rating,
                    'reviews'     => $result['reviews'],
                    'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id']),
                    'sellername'  => $partner['companyname'],
					'sellerhref'  => $this->url->link('customerpartner/profile', '&id=' . $partner['customer_id'] ,true),
                    'best_seller' => $result['best_seller'],
                    'free_shipping'=> $result['free_shipping'],
                    'cart_recommended' => $result['cart_recommended'],
                    'rec_file_upload_1' => $rec_file_upload_1,
                    'rec_file_upload_2' => $rec_file_upload_2,
                    'front_company_name'=>$product_info['front_company_name'],
                    'product_images'=>($result['images'])?$this->model_tool_image->resize($result['images'][0]['image'], $setting['width'], $setting['height'],true):'',
                    'options'    => $result['options'],
				);
			}
            
            return $this->load->view('checkout/cart_recommended', $_data);
		}
	}
}
