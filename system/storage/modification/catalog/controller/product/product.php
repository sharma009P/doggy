<?php
class ControllerProductProduct extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('product/product');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$this->load->model('catalog/category');
     	$this->load->model('account/customerpartner');
		$this->load->model('customerpartner/master');
		$this->load->model('tool/image');
		
		

		if (isset($this->request->get['path'])) {
			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path)
					);
				}
			}

			// Set the last category breadcrumb
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$url = '';

				if (isset($this->request->get['sort'])) {
					$url .= '&sort=' . $this->request->get['sort'];
				}

				if (isset($this->request->get['order'])) {
					$url .= '&order=' . $this->request->get['order'];
				}

				if (isset($this->request->get['page'])) {
					$url .= '&page=' . $this->request->get['page'];
				}

				if (isset($this->request->get['limit'])) {
					$url .= '&limit=' . $this->request->get['limit'];
				}

				$data['breadcrumbs'][] = array(
					'text' => $category_info['name'],
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url)
				);
			}
		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->get['manufacturer_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_brand'),
				'href' => $this->url->link('product/manufacturer')
			);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

			if ($manufacturer_info) {
				$data['breadcrumbs'][] = array(
					'text' => $manufacturer_info['name'],
					'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
				);
			}
		}

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_search'),
				'href' => $this->url->link('product/search', $url)
			);
		}

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');


			$data['advancedoption_qty_status'] =  $this->config->get('module_advancedoption_qty_status');
			$data['advancedoption_status'] =  $this->config->get('module_advancedoption_status');
			$data['advancedoption_description_status'] =  $this->config->get('module_advancedoption_description_status');
			$data['advancedoption_image_status'] =  $this->config->get('module_advancedoption_image_status');
			$data['advancedoption_live_change_status'] =  $this->config->get('module_advancedoption_live_change_status');
			$data['advancedoption_sku_status'] =  $this->config->get('module_advancedoption_sku_status');
		
		$product_info = $this->model_catalog_product->getProduct($product_id);

		
		

			$data['advancedoption_qty_status'] =  $this->config->get('module_advancedoption_qty_status');
			$data['advancedoption_status'] =  $this->config->get('module_advancedoption_status');
			$data['advancedoption_description_status'] =  $this->config->get('module_advancedoption_description_status');
			$data['advancedoption_image_status'] =  $this->config->get('module_advancedoption_image_status');
			$data['advancedoption_live_change_status'] =  $this->config->get('module_advancedoption_live_change_status');
			$data['advancedoption_sku_status'] =  $this->config->get('module_advancedoption_sku_status');
		
			$product_info = $this->model_catalog_product->getProduct($product_id);

		
		
		$seller_id = $this->model_customerpartner_master->getPartnerIdBasedonProduct($product_id)['id'];

		//print_r($seller_id);
		
				$partner = $this->model_customerpartner_master->getProfile($seller_id);
        $page = 1;
		$limit = 5;
		$url = '';

		$filter_data = array(
			'customer_id'		 => $seller_id,					
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit,
			'filter_store' 		 => $this->config->get('config_store_id'),
			'filter_status'		 => 1
		);	
		$filter_datas = array(
			'customer_id'		 => $seller_id,					
			
			'filter_store' 		 => $this->config->get('config_store_id'),
			'filter_status'		 => 1
		);
		
		$this->load->model('catalog/product');

		$results = $this->model_account_customerpartner->getProductsSeller($filter_data);
		$resultscount = $this->model_account_customerpartner->getProductsSeller($filter_datas);
	//echo "<pre>";	print_r($results);
		
		$data['productseller'] = array();

$countp = count($resultscount);

		foreach ($results as $result) {


			$product_infos = $this->model_catalog_product->getProduct($result['product_id']);

			/* if (isset($product_info['price']) && $product_info['price']) {
			  $result['price'] = $product_info['price'];                                  
			}
 */
			if ($result['image'] && is_file(DIR_IMAGE.$result['image'])) {				
				$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
			} else {
				
				 $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
			}

		 	if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$price = false;
			} 

		/* 	if ((float)$result['special']) {
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
				$rating = (int)$result['rating'];
			} else {
				$rating = false;
			}
 */
			$data['productseller'][] = array(
				'thumb'       => $image,
				'countp'        => $countp,
				'href'        => $this->url->link('product/product', '&product_id=' . $result['product_id'] ,true)
			); 
			
			
	
	}
	
	$data['countp'] =  $countp;
	$companylogo = $this->model_tool_image->resize($partner['companylogo'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
	$sizeguide = $this->config->get('config_url').'image/catalog/update-size-guide.png';
	$favourite = $this->config->get('config_url').'image/catalog/bone-new.png';
	$ruler = $this->config->get('config_url').'image/catalog/icon-ruler.png';
	$companylocality = $partner['companylocality'];
	$country =  $partner['country'];
	$data['logged'] = $this->customer->isLogged();
	$data['partner'] = array(
				'companyname'       => $partner['companyname'],			
				'companylogo'       => $companylogo,		
				'sizeguide'       => $sizeguide,		
				'favourite'       => $favourite,		
				'ruler'       => $ruler,		
				'citystate'       => $companylocality.', '.$country,		
				'href'        => $this->url->link('customerpartner/profile', '&id=' . $partner['customer_id'] ,true)
			); 
	
	//echo "<pre>"; print_r($data['productseller']);
	//echo "<pre>"; print_r($partner);
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		if ($product_info) {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $product_info['name'],
				'href' => $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id'])
			);

			$this->document->setTitle($product_info['meta_title']);
			$this->document->setDescription($product_info['meta_description']);

    	  if (file_exists(DIR_TEMPLATE . 'default/stylesheet/stock-module.css')) {
    	    $this->document->addStyle('catalog/view/theme/default/stylesheet/stock-module.css');
    	  }
	      if (file_exists(DIR_TEMPLATE . 'default/stylesheet/stock-module-theme.css')) {
					$this->document->addStyle('catalog/view/theme/default/stylesheet/stock-module-theme.css');
	      }    	
	      $this->document->addScript('catalog/view/javascript/open4devutils.js');
    	  $this->document->addScript('catalog/view/javascript/stockmodule.js');
					
			$this->document->setKeywords($product_info['meta_keyword']);

  		$pixel = "fbq('track', 'ViewContent', {
        content_ids: ['".$this->request->get['product_id']."'],
        content_type: 'product',
        value: ".$product_info['price'].",
        currency: '".$this->session->data['currency']."'
        });";

      $this->document->setPixel($pixel);
  		
			$this->document->addLink($this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');
			$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

			$data['heading_title'] = $product_info['name'];

			$data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
			$data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

			$this->load->model('catalog/review');

			$data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

			$data['product_id'] = (int)$this->request->get['product_id'];
			$data['manufacturer'] = $product_info['manufacturer'];
			$data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
			$data['model'] = $product_info['model'];
			$data['size_guide'] = $product_info['size_guide'];
			$data['shipping_day_avail'] = $product_info['shipping_day_avail'];
            
            $data['product_id_arr'] = array('69');
            
            $data['cart_recommended'] = '';
            $data['matching_leather'] = '';
            $data['matching_leather']['thumb_image'] = '';
            $data['matching_leather']['n_price'] = '';
            $data['product_currency'] = $this->session->data['currency'];
            if(!empty($product_info['cart_recommended'])){
                $data['cart_recommended'] = $product_info['cart_recommended'];
                $matching_leather_id = '78'; 
                $data['matching_leather'] = $this->model_catalog_product->getProduct($matching_leather_id);
                $data['matching_leather']['thumb_image'] = $this->config->get('config_url').'image/'.$data['matching_leather']['image'];
                $data['matching_leather']['n_price'] = $this->currency->format($this->tax->calculate($data['matching_leather']['price'], $data['matching_leather']['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            }
            
            $data['rec_file_upload_1'] = '';
            if(!empty($product_info['rec_file_upload_1'])){
                
                //$data['rec_file_upload_1'] = $this->model_tool_image->resize_recommend_img($data['product_id'],$product_info['rec_file_upload_1'], '200', '200');
                $data['rec_file_upload_1'] = HTTP_IMAGE.'recommended_product/'.$data['product_id'].'/'.$product_info['rec_file_upload_1'];
            }
            
            $data['rec_file_upload_2'] = '';
            if(!empty($product_info['rec_file_upload_2'])){
                //$data['rec_file_upload_2'] = $this->model_tool_image->resize_recommend_img($data['product_id'],$product_info['rec_file_upload_2'], '200', '200');
                $data['rec_file_upload_2'] = HTTP_IMAGE.'recommended_product/'.$data['product_id'].'/'.$product_info['rec_file_upload_2'];
            }
            
           
			$shipping_day = $product_info['shipping_day_avail']; 
			
			$days = array('1'=> 'Monday','2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday','5' => 'Friday', '6' => 'Saturday', '7'=> 'Sunday');
			$date = date('N');
		
			$date_today = date('Y/m/d');
			// var_dump($date);
			$left_day = array('6','7');

			 $time = date("h:i:s a");

			 if($time > '12:00:00 pm'){
			 	$shipping_day = $shipping_day+1;
			 }
               $day345 = date('h:i:s a', strtotime('+5 hours', strtotime($time)));
			 // var_dump($day345);
			
			if($date == '6'){
				$shipping_day = $shipping_day+2;
			}
			if($date == '7'){
				$shipping_day = $shipping_day+1;
			}

			$day_add = '+'.$shipping_day.' day';
			// var_dump($day_add);
			$day = date('N', strtotime($day_add, strtotime($date_today)));
  
			if($day == '6'){
				$shipping_day = $shipping_day+2;
				$day = '1';
			}
			if($day == '7'){
				$shipping_day = $shipping_day+1;
				$day = '1';
			}
			$day_add = '+'.$shipping_day.' day';
			$del_date = date('d M', strtotime($day_add, strtotime($date_today)));
			
			
			
 // var_dump($del_date);
        $data['shipping_text'] = 'Want this item for <strong id="change-date">'.$days[$day].' '.$del_date.'</strong><input type="hidden" value="'.$days[$day].' '.$del_date.'" id="shipping-deleiver-date">';
			$deleivered_day = $days[$day];
				// var_dump($deleivered_day);
				// var_dump($deleivered_day);
			// $today = date("N");
			// var_dump($today);
			$data['qty_p_enable'] = $product_info['qty_p_enable'];
			
			$data['reward'] = $product_info['reward'];
			$data['points'] = $product_info['points'];
			$data['full_description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');
			$data['overview'] = html_entity_decode($product_info['overview'], ENT_QUOTES, 'UTF-8');
			$data['shipping'] = html_entity_decode($product_info['shipping'], ENT_QUOTES, 'UTF-8');
            
            $data['description'] = substr(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'),0,550);


      $data['text_instock'] = $this->language->get('text_instock');
      $data['text_outofstock'] = $product_info['stock_status'];
      $data['stock_available'] = ($product_info['quantity'] > 0);
						
			if ($product_info['quantity'] <= 0) {
				$data['stock'] = $product_info['stock_status'];				$data['stockstatus'] = "Out of Stock";								
			} elseif ($this->config->get('config_stock_display')) {				$data['stock'] = $product_info['quantity'];					if ($product_info['quantity'] <= 10) {$data['stock'] = $product_info['quantity'];
				$data['stockstatus'] = "Almost gone. There's only ".$product_info['quantity'].' Left' ;									}else{						$data['stockstatus'] = $product_info['quantity'].' Left' ;						$data['stock'] = $product_info['quantity'];											}
			} else {$data['stockstatus'] = $product_info['quantity'].' Left' ;
				$data['stock'] = $this->language->get('text_instock');
			}

			$this->load->model('tool/image');

			if ($product_info['image']) {
				$data['popup'] = $this->config->get('config_url').'image/'.$product_info['image'];
			} else {
				$data['popup'] = '';
			}

			if ($product_info['image']) {
				$data['thumb'] = $this->config->get('config_url').'image/'.$product_info['image'];
			} else {
				$data['thumb'] = '';
			}

			$data['images'] = array();

			$results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
   $countit=1;
			foreach ($results as $result) {
				$data['images'][] = array(
					'popup' => $this->config->get('config_url').'image/'.$result['image'], 
					'countit' => $countit,
					'thumb' => $this->config->get('config_url').'image/'.$result['image'], 
				); $countit++;
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['price'] = false;
			}

			if ((float)$product_info['special']) {
				$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['special'] = false;
			}

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}


      if(!$data['special']){
        $data['fbprice'] = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
      }else{
        $data['fbprice'] = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
      }
       $data['fbcurrency'] = $this->session->data['currency'];
      
			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);

			$data['discounts'] = array();

			foreach ($discounts as $discount) {
				$data['discounts'][] = array(
					'quantity' => $discount['quantity'],
					'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
			}


			$optionsimages=array();
			foreach ($this->model_catalog_product->getimagesdata($this->request->get['product_id']) as $optionimg){
				if($optionimg){
					if($optionimg['image']){
							$popup = $this->model_tool_image->resize($optionimg['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
					} else {
							$popup = '';
					}
					
					
					if ($optionimg['image']) {
						$thumb = $this->model_tool_image->resize($optionimg['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
					} else {
						$thumb = '';
					}
					
					$optionsimages[$optionimg['product_option_value_id']]=array(
						'thumb'										=> $thumb,
						'popup'										=> $popup,
					);
				}
			}
		
			$data['optionsimages'] = json_encode($optionsimages);
		
			$data['options'] = array();


			if (!isset($data['text_stock'])) {
				$data['text_stock'] = $this->language->get('text_stock');
			}
			
      $data['product_stock_enabled'] = false;
			
			$this->load->library('stockutils');
            
      $search = array("%text_stock%", "%stock%");
			$replace = array($data['text_stock'], $data['stock']);
			$eReplace = $this->config->get(StockModuleConfig::$REPLACE_EXPRESSION);
			$eRemove = $this->config->get(StockModuleConfig::$REMOVE_EXPRESSION);
			$eDecorate = $this->config->get(StockModuleConfig::$DECORATE_EXPRESSION);
			$eStock = $this->config->get(StockModuleConfig::$STOCK_EXPRESSION);

			$replace_expression = isset($eReplace) ? addslashes(html_entity_decode($eReplace)) : "";
			$remove_expression = isset($eRemove) ? addslashes(html_entity_decode($eRemove)) : "";
			$stock_expression = isset($eStock) ? addslashes(html_entity_decode($eStock)) : "";
			$decorate_expression = isset($eDecorate) ? addslashes(html_entity_decode($eDecorate)) : "";
			
      $data['config_stock_module'] = array(
        'enabled' 										=> StockUtils::isStockModuleEnabled($this->config),
      	'update_stock_display'				=> $this->config->get(StockModuleConfig::$UPDATE_STOCK_DISPLAY) == 1,
      	'update_stock_display_method'	=> $this->config->get(StockModuleConfig::$UPDATE_STOCK_DISPLAY_BEHAVIOR),
      	'show_cart_quantities'				=> $this->config->get(StockModuleConfig::$SHOW_CART_QUANTITIES) == 1,
      	'change_css'									=> $this->config->get(StockModuleConfig::$CHANGE_PRODUCT_CSS) == 1,
      	'replace_expression'          => str_replace($search, $replace, $replace_expression),
      	'remove_expression'           => str_replace($search, $replace, $remove_expression),
      	'stock_expression'            => str_replace($search, $replace, $stock_expression),
      	'decorate_expression'         => str_replace($search, $replace, $decorate_expression)
      );
      $data['config_stock_display'] = $this->config->get('config_stock_display');
      $stock_enabled_options = 0;
						
			foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
				$product_option_value_data = array();


                if ( StockUtils::isStockModuleEnabled($this->config) && $option['stock_enabled']) {
                  $stock_enabled_options ++;
                }
						
				foreach ($option['product_option_value'] as $option_value) {
					
      	$show_all_options = isset($product_info['allow_preorder']) && ($product_info['allow_preorder'] == '1' || $product_info['allow_preorder'] == 2);
      	if (!$option_value['subtract'] || ($option_value['quantity'] > 0) || ($show_all_options)) {
						
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
						} else {
							$price = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
'sku'            		  => $option_value['sku'],
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}
                $option_price = '';
                if(!empty($option['price']) || $option['price'] != '0.0000'){
                    $option_price = $this->currency->format($this->tax->calculate($option['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);    
                }

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
'description'          => html_entity_decode($option['description'], ENT_QUOTES, 'UTF-8'),
		'description_status'          => strip_tags($option['description']),
		
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
                    'price'                => $option_price,
					'stock_enabled'        => $option['stock_enabled'],
					'required'             => $option['required']
				);
			}


            if ($stock_enabled_options > 0) {
              $data['product_stock_enabled'] = true;
            }
						
			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

			$data['review_status'] = $this->config->get('config_review_status');

			if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
				$data['review_guest'] = true;
			} else {
				$data['review_guest'] = false;
			}

			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}

			$data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
			$data['rating'] = (int)$product_info['rating'];

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			$data['share'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);

			$data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);


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

			$results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
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
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}
$seller_id = $this->model_customerpartner_master->getPartnerIdBasedonProduct($result['product_id'])['id'];

		//print_r($seller_id);
		
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
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,
					'sellername'      => $partner['companyname'],
					'sellerhref'        => $this->url->link('customerpartner/profile', '&id=' . $partner['customer_id'] ,true),
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}


                $this->load->model('account/customerpartner');

          			$checkSellerOwnProduct = $this->model_account_customerpartner->checkSellerOwnProduct($this->request->get['product_id']);

          			if ($checkSellerOwnProduct && !$this->config->get('marketplace_sellerbuyproduct')) {
          					$data['allowedProductBuy'] = false;
                    $this->load->language('extension/module/marketplace');
                    $data['error_own_product'] = $this->language->get('error_own_product');
          			}else{
          					$data['allowedProductBuy'] = true;
          			}
                $data['sellerprofile'] = $this->load->controller('extension/module/marketplace/sellerprofile');

                if ($this->config->get('shipping_wk_custom_shipping_status') && isset($this->session->data['shipping_address']['postcode']) && isset($this->request->get['product_id']) && $this->request->get['product_id']) {

        	          $check_seller = $this->model_account_customerpartner->getProductSellerDetails($this->request->get['product_id']);

        	          $seller_id = 0;

        	          if (isset($check_seller['customer_id']) && $check_seller['customer_id']) {
        	              $seller_id = $check_seller['customer_id'];
        	          }

        	          $weight = 0;

        	          if (isset($product_info['weight']) && $product_info['weight']) {
        	              $weight = $product_info['weight'];
        	          }

        	          $max_days = $this->model_account_customerpartner->getMinDays($seller_id,$this->session->data['shipping_address']['postcode'],$weight);

        	          if (isset($max_days['max_days']) && $max_days['max_days']) {
        	              $date = new DateTime(date('Y-m-d', strtotime("+".$max_days['max_days']." days")));

        	              $data['delivery_date'] = $date->format('Y-m-d');
        	              $data['text_delivery_date'] = $this->language->get('text_delivery_date');
        	          }
        	      }
              
			$data['tags'] = array();

			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'tag=' . trim($tag))
					);
				}
			}

			$data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

			$this->model_catalog_product->updateViewed($this->request->get['product_id']);
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
            
            $data['_dog_name'] = '';
            if(isset($this->request->get['dog_name'])){
                $data['_dog_name'] = substr($this->request->get['dog_name'], 0, 8);
            }
            
            $data['selected_color'] = '';
            if(!empty($product_info['cart_recommended'])){
                if($this->request->get['product_id'] == '77'){
                    $data['selected_color'] = 'Black';
                }
                if($this->request->get['product_id'] == '73'){
                    $data['selected_color'] = 'Blue';
                }
                if($this->request->get['product_id'] == '75'){
                    $data['selected_color'] = 'Pink';
                }
                if($this->request->get['product_id'] == '76'){
                    $data['selected_color'] = 'White';
                }
                if($this->request->get['product_id'] == '74'){
                    $data['selected_color'] = 'Red';
                }
            }


				$data['module_wgtm_status'] = $this->config->get('module_wgtm_status');
				$this->load->model('catalog/category');
				$data['wcurencysymbol'] = $this->session->data['currency'];
				$wprice = $this->currency->format($product_info['price'], $this->session->data['currency'], '', false);
				$getcategories = $this->model_catalog_product->getCategories($product_info['product_id']);
				$categories = array();
				if($getcategories){
					foreach($getcategories as $cate){
						$getcategory = $this->model_catalog_category->getCategory($cate['category_id']);
						$categories[] = isset($getcategory['name']) ? $getcategory['name'] : '';
					}
				}
				$categories = implode(", ", $categories);

				$data['wgtms_product'] = array(
					'manufacturer' => $product_info['manufacturer'],
					'categories'   => $categories,
					'price'	  	   => $wprice,
					'product_id'   => $product_info['product_id'],
					'name'         => $product_info['name']
				);
			
			
                // pavothemer product layout file
                $layout = $this->config->get( 'pavothemer_product_layout' );
                if ( ! $layout ) {
                    $this->response->setOutput($this->load->view('product/product', $data));
                } else {
                    $this->response->setOutput($this->load->view('product/' . $layout, $data));
                }
            
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/product', $url . '&product_id=' . $product_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

 
  public function getStockAvailable() {
    if (isset($this->request->post['product_id'])) {
      $product_id = $this->request->post['product_id'];
    } else {
      $product_id = 0;
    }
    
    if (isset($this->request->post['option'])) {
      $options = $this->request->post['option'];
    } else {
      $options = array();
    }
    if (!$product_id || count($options) === 0) {
			$json['success'] = false;
	    $result = json_encode($json);
	    $this->response->setOutput($result);
	    return $result;
		}
    
    $options = array_values($options);
    
    //$this->log->write("Product id: " . $product_id . " Options: " . var_export($options, TRUE));
    $this->load->library('stockutils');
    $stock = StockUtils::getCombinationAvailableStock($this->db, $product_id, $options);
    
    $json = array();
    $json['success'] = true;
    $json['stock'] = $stock;
    $result = json_encode($json);
    $this->response->setOutput($result);
    return $result;

  }
						
	public function review() {
		$this->load->language('product/product');

		$this->load->model('catalog/review');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['reviews'] = array();
$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
		
		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);
$this->load->model('tool/image');
		$results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);
		$thumb = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
//echo '<pre>';print_r($product_info);echo '</pre>';
        
        $ratings = 0;
        $total_users_rating = count($results);
		foreach ($results as $result) {		  
          $_old_rating = (int)$result['rating'];
	
			$data['reviews'][] = array(
				'author'     => $result['author'],
				'thumb'     => $thumb,
				'text'       => nl2br($result['text']),
				'texthead'       => substr(nl2br($result['text']),0,25),
				'rating'     => (int)$result['rating'],
				'date_added' => date("d F, Y", strtotime($result['date_added']))
			);
            
            $ratings = $_old_rating+$ratings;
		}
        $data['total_ratings'] = ((($ratings > 0) && ($total_users_rating > 0))?$ratings/$total_users_rating:0);
        
		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

		$this->response->setOutput($this->load->view('product/review', $data));
	}

	public function write() {
		$this->load->language('product/product');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error'] = $this->language->get('error_text');
			}

			if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
				$json['error'] = $this->language->get('error_rating');
			}

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

				if ($captcha) {
					$json['error'] = $captcha;
				}
			}

			if (!isset($json['error'])) {
				$this->load->model('catalog/review');

				$this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getRecurringDescription() {
		$this->load->language('product/product');
		$this->load->model('catalog/product');

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->post['recurring_id'])) {
			$recurring_id = $this->request->post['recurring_id'];
		} else {
			$recurring_id = 0;
		}

		if (isset($this->request->post['quantity'])) {
			$quantity = $this->request->post['quantity'];
		} else {
			$quantity = 1;
		}


			$data['advancedoption_qty_status'] =  $this->config->get('module_advancedoption_qty_status');
			$data['advancedoption_status'] =  $this->config->get('module_advancedoption_status');
			$data['advancedoption_description_status'] =  $this->config->get('module_advancedoption_description_status');
			$data['advancedoption_image_status'] =  $this->config->get('module_advancedoption_image_status');
			$data['advancedoption_live_change_status'] =  $this->config->get('module_advancedoption_live_change_status');
			$data['advancedoption_sku_status'] =  $this->config->get('module_advancedoption_sku_status');
		
		$product_info = $this->model_catalog_product->getProduct($product_id);
		
		$recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

		$json = array();

		if ($product_info && $recurring_info) {
			if (!$json) {
				$frequencies = array(
					'day'        => $this->language->get('text_day'),
					'week'       => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month'      => $this->language->get('text_month'),
					'year'       => $this->language->get('text_year'),
				);

				if ($recurring_info['trial_status'] == 1) {
					$price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
				} else {
					$trial_text = '';
				}

				$price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				if ($recurring_info['duration']) {
					$text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				} else {
					$text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				}

				$json['success'] = $text;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
