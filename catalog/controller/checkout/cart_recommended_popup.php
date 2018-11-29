<?php
class ControllerCheckoutCartRecommendedPopup extends Controller {
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
                
                $_images = array();

    			$results_images = $this->model_catalog_product->getProductImages($result['product_id']);
                $countit=1;
    			foreach ($results_images as $result_image) {
    				$_images[] = array(
    					'popup' => $this->config->get('config_url').'image/'.$result_image['image'], 
    					'countit' => $countit,
    					'thumb' => $this->config->get('config_url').'image/'.$result_image['image'], 
    				); $countit++;
    			}
                
                $seller_id = $this->model_customerpartner_master->getPartnerIdBasedonProduct($result['product_id']);
                
                $partner = '';
                $companylogo = '';
            	$sizeguide = $this->config->get('config_url').'image/catalog/update-size-guide-1.jpg';
            	$favourite = $this->config->get('config_url').'image/catalog/bone-new.png';
            	$ruler = $this->config->get('config_url').'image/catalog/icon-ruler.png';
            	$companylocality = '';
            	$country =  '';
                
                if(!empty($seller_id)){
                    $seller_id = $seller_id['id'];
                    $partner = $this->model_customerpartner_master->getProfile($seller_id);
                    $companylogo = $this->model_tool_image->resize($partner['companylogo'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
                	$companylocality = $partner['companylocality'];
                	$country =  $partner['country'];    
                }
                
                
                $partners = array(
    				'companyname'       => $partner['companyname'],			
    				'companylogo'       => $companylogo,		
    				'sizeguide'       => $sizeguide,		
    				'favourite'       => $favourite,		
    				'ruler'       => $ruler,		
    				'citystate'       => $companylocality.', '.$country,		
    				'href'        => $this->url->link('customerpartner/profile', '&id=' . $partner['customer_id'] ,true)
    			);
                
                $data['product_id_arr'] = array('69');
            
                $cart_recommended = array();
                $matching_leather = array();
                $matching_leather['thumb_image'] = '';
                $matching_leather['n_price'] = '';
                $product_currency = $this->session->data['currency'];
                if(!empty($product_info['cart_recommended'])){
                    $cart_recommended = $product_info['cart_recommended'];
                    $matching_leather_id = '78'; 
                    $matching_leather = $this->model_catalog_product->getProduct($matching_leather_id);
                    $matching_leather['thumb_image'] = $this->config->get('config_url').'image/'.$matching_leather['image'];
                    $matching_leather['n_price'] = $this->currency->format($this->tax->calculate($matching_leather['price'], $matching_leather['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                }
                
                
                $options = array();

    			foreach ($this->model_catalog_product->getProductOptions($result['product_id']) as $option) {
    				$product_option_value_data = array();
    
    				foreach ($option['product_option_value'] as $option_value) {
    					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
    						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
    							$n_option_price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
    						} else {
    							$n_option_price = false;
    						}
    
    						$product_option_value_data[] = array(
    							'product_option_value_id' => $option_value['product_option_value_id'],
    							'option_value_id'         => $option_value['option_value_id'],
    							'name'                    => $option_value['name'],
    							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
    							'price'                   => $n_option_price,
    							'price_prefix'            => $option_value['price_prefix']
    						);
    					}
    				}
                    $option_price = '';
                    if(!empty($option['price']) || $option['price'] != '0.0000'){
                        $option_price = $this->currency->format($this->tax->calculate($option['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);    
                    }
                    
    				$options[] = array(
    					'product_option_id'    => $option['product_option_id'],
    					'product_option_value' => $product_option_value_data,
    					'option_id'            => $option['option_id'],
    					'name'                 => $option['name'],
    					'type'                 => $option['type'],
    					'value'                => $option['value'],
                        'price'                => $option_price,
    					'required'             => $option['required']
    				);
    			}
                
                $_data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
                    'heading_title'=> $result['name'],
                    'images'      => $_images,  
                    'partner'     => $partners,     
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
                    'cart_recommended'=> $cart_recommended,
                    'matching_leather'=>$matching_leather,
                    'size_guide'=>$product_info['size_guide'],
                    'product_currency'=>$product_currency,
                    'product_images'=>($result['images'])?$this->model_tool_image->resize($result['images'][0]['image'], $setting['width'], $setting['height'],true):'',
                    'options'    => $options,//$result['options'],
				);
			}
            return $this->load->view('checkout/cart_recommended_popup', $_data);
		}
	}
}
