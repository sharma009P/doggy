<?php
class ControllerCheckoutSuccesspaypal extends Controller {
	public function index() {
		$this->load->language('checkout/success');
		$this->load->language('account/order');
		$this->load->language('account/register');
		


    $redirect = '';


		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$redirect = $this->url->link('checkout/cart');

				break;
			}
		}

		// if (!$redirect) {
		// 	$order_data = array();

		// 	$totals = array();
		// 	$taxes = $this->cart->getTaxes();
		// 	$total = 0;

			
		// 	$total_data = array(
		// 		'totals' => &$totals,
		// 		'taxes'  => &$taxes,
		// 		'total'  => &$total
		// 	);

		// 	$this->load->model('setting/extension');

		// 	$sort_order = array();

		// 	$results = $this->model_setting_extension->getExtensions('total');

		// 	foreach ($results as $key => $value) {
		// 		$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
		// 	}

		// 	array_multisort($sort_order, SORT_ASC, $results);

		// 	foreach ($results as $result) {
		// 		if ($this->config->get('total_' . $result['code'] . '_status')) {
		// 			$this->load->model('extension/total/' . $result['code']);

		// 			$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
		// 		}
		// 	}

		// 	$sort_order = array();

		// 	foreach ($totals as $key => $value) {
		// 		$sort_order[$key] = $value['sort_order'];
		// 	}

		// 	array_multisort($sort_order, SORT_ASC, $totals);

		// 	$order_data['totals'] = $totals;

		// 	$this->load->language('checkout/checkout');

		// 	$order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
		// 	$order_data['store_id'] = $this->config->get('config_store_id');
		// 	$order_data['store_name'] = $this->config->get('config_name');

		// 	if ($order_data['store_id']) {
		// 		$order_data['store_url'] = $this->config->get('config_url');
		// 	} else {
		// 		if ($this->request->server['HTTPS']) {
		// 			$order_data['store_url'] = HTTPS_SERVER;
		// 		} else {
		// 			$order_data['store_url'] = HTTP_SERVER;
		// 		}
		// 	}
			
		// 	$this->load->model('account/customer');


		// 		$order_data['customer_id'] = 0;
		// 		$order_data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
		// 		$order_data['firstname'] = $this->request->post['first_name'];                  
  //           $order_data['email'] = $this->request->post['payer_email'];
		// 	$order_data['payment_firstname'] = $this->request->post['first_name'];
		// 	$order_data['payment_lastname'] = $this->request->post['last_name'];
		// 	$order_data['payment_company'] = '';
		// 	$order_data['payment_address_1'] = $this->request->post['address_street'];
		// 	$order_data['payment_address_2'] = '';
		// 	$order_data['payment_city'] = $this->request->post['address_city'];
		// 	$order_data['payment_postcode'] = $this->request->post['address_zip'];
		// 	$order_data['payment_zone'] = $this->request->post['address_state'];
		// 	$order_data['payment_zone_id'] = '';
		// 	$order_data['payment_country'] = $this->request->post['residence_country'];;
		// 	$order_data['payment_country_id'] = '';
		// 	$order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
		// 	$order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array());

		// 	$order_data['payment_method'] = 'Paypal'; 
		// 	$order_data['payment_code'] = 'p_code';
    

		// 	if ($this->cart->hasShipping()) {
		// 		$order_data['shipping_firstname'] =  $this->request->post['first_name'];
		// 		$order_data['shipping_lastname'] =  $this->request->post['last_name'];;
		// 		$order_data['shipping_company'] = '';
		// 		$order_data['shipping_address_1'] = $this->request->post['address_street'];
		// 		$order_data['shipping_address_2'] = '';
		// 		$order_data['shipping_city'] = $this->request->post['address_city'];
		// 		$order_data['shipping_postcode'] = $this->request->post['address_zip'];
		// 		$order_data['shipping_zone'] = $this->request->post['address_state'];
		// 		$order_data['shipping_zone_id'] = '';
		// 		$order_data['shipping_country'] = $this->request->post['residence_country'];
		// 		$order_data['shipping_country_id'] = '';
		// 		$order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
		// 		$order_data['shipping_custom_field'] = (isset($this->session->data['shipping_address']['custom_field']) ? $this->session->data['shipping_address']['custom_field'] : array());

		// 		if (isset($this->session->data['shipping_method']['title'])) {
		// 			$order_data['shipping_method'] = $this->session->data['shipping_method']['title'];
		// 		} else {
		// 			$order_data['shipping_method'] = '';
		// 		}

		// 		if (isset($this->session->data['shipping_method']['code'])) {
		// 			$order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
		// 		} else {
		// 			$order_data['shipping_code'] = '';
		// 		}
		// 	} else {
		// 		$order_data['shipping_firstname'] = '';
		// 		$order_data['shipping_lastname'] = '';
		// 		$order_data['shipping_company'] = '';
		// 		$order_data['shipping_address_1'] = '';
		// 		$order_data['shipping_address_2'] = '';
		// 		$order_data['shipping_city'] = '';
		// 		$order_data['shipping_postcode'] = '';
		// 		$order_data['shipping_zone'] = '';
		// 		$order_data['shipping_zone_id'] = '';
		// 		$order_data['shipping_country'] = '';
		// 		$order_data['shipping_country_id'] = '';
		// 		$order_data['shipping_address_format'] = '';
		// 		$order_data['shipping_custom_field'] = array();
		// 		$order_data['shipping_method'] = '';
		// 		$order_data['shipping_code'] = '';
		// 	}

		// 	$order_data['products'] = array();

		// 	foreach ($this->cart->getProducts() as $product) {
		// 		$option_data = array();

		// 		foreach ($product['option'] as $option) {
		// 			$option_data[] = array(
		// 				'product_option_id'       => $option['product_option_id'],
		// 				'product_option_value_id' => $option['product_option_value_id'],
		// 				'option_id'               => $option['option_id'],
		// 				'option_value_id'         => $option['option_value_id'],
		// 				'name'                    => $option['name'],
		// 				'value'                   => $option['value'],
		// 				'type'                    => $option['type']
		// 			);
		// 		}

		// 		$order_data['products'][] = array(
		// 			'product_id' => $product['product_id'],
		// 			'name'       => $product['name'],
		// 			'model'      => $product['model'],
		// 			'option'     => $option_data,
		// 			'download'   => $product['download'],
		// 			'quantity'   => $product['quantity'],
		// 			'subtract'   => $product['subtract'],
		// 			'price'      => $product['price'],
		// 			'total'      => $product['total'],
		// 			'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
		// 			'reward'     => $product['reward']
		// 		);
		// 	}

		// 	$order_data['vouchers'] = array();

		// 	if (!empty($this->session->data['vouchers'])) {
		// 		foreach ($this->session->data['vouchers'] as $voucher) {
		// 			$order_data['vouchers'][] = array(
		// 				'description'      => $voucher['description'],
		// 				'code'             => token(10),
		// 				'to_name'          => $voucher['to_name'],
		// 				'to_email'         => $voucher['to_email'],
		// 				'from_name'        => $voucher['from_name'],
		// 				'from_email'       => $voucher['from_email'],
		// 				'voucher_theme_id' => $voucher['voucher_theme_id'],
		// 				'message'          => $voucher['message'],
		// 				'amount'           => $voucher['amount']
		// 			);
		// 		}
		// 	}

		// 	$order_data['comment'] = $this->session->data['comment'];
		// 	$order_data['total'] = $total_data['total'];

		// 	if (isset($this->request->cookie['tracking'])) {
		// 		$order_data['tracking'] = $this->request->cookie['tracking'];

		// 		$subtotal = $this->cart->getSubTotal();

			
		// 		$affiliate_info = $this->model_account_customer->getAffiliateByTracking($this->request->cookie['tracking']);

		// 		if ($affiliate_info) {
		// 			$order_data['affiliate_id'] = $affiliate_info['customer_id'];
		// 			$order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
		// 		} else {
		// 			$order_data['affiliate_id'] = 0;
		// 			$order_data['commission'] = 0;
		// 		}

				
		// 		$this->load->model('checkout/marketing');

		// 		$marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

		// 		if ($marketing_info) {
		// 			$order_data['marketing_id'] = $marketing_info['marketing_id'];
		// 		} else {
		// 			$order_data['marketing_id'] = 0;
		// 		}
		// 	} else {
		// 		$order_data['affiliate_id'] = 0;
		// 		$order_data['commission'] = 0;
		// 		$order_data['marketing_id'] = 0;
		// 		$order_data['tracking'] = '';
		// 	}

		// 	$order_data['language_id'] = $this->config->get('config_language_id');
		// 	$order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
		// 	$order_data['currency_code'] = $this->session->data['currency'];
		// 	$order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
		// 	$order_data['ip'] = $this->request->server['REMOTE_ADDR'];

		// 	if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
		// 		$order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
		// 	} elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
		// 		$order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
		// 	} else {
		// 		$order_data['forwarded_ip'] = '';
		// 	}

		// 	if (isset($this->request->server['HTTP_USER_AGENT'])) {
		// 		$order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
		// 	} else {
		// 		$order_data['user_agent'] = '';
		// 	}

		// 	if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
		// 		$order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
		// 	} else {
		// 		$order_data['accept_language'] = '';
		// 	}

		// 	$this->load->model('checkout/order');
			
		// }

   //  if(isset($_POST['txn_id'])){
			// $this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);
		
			// $order_id = $this->session->data['order_id'];
		
			// $order_status_id = $this->config->get('config_order_status_id');
		
			// $this->model_checkout_order->addOrderHistory($order_id, $order_status_id);
   //  }
$data['order_id'] = '';
				$data['shipping_method'] = '';
				$data['total'] = '';
				$data['productss'] = array();
				$data['currency_code'] = '';
				$data['tax'] = '';
				$data['coupon'] = '';
				$data['shipping'] = '';
				$data['store_name'] = $this->config->get('config_name'); 

		if (isset($this->session->data['order_id']))
	{

      $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $prduct_ids = array();
          foreach ($this->cart->getProducts() as $prducts) {
            $prduct_ids[] = $prducts['product_id'];
          }

        $pixel = "fbq('track', 'Purchase', {
        content_ids: [". implode(',', $prduct_ids) ."],
        content_type: 'product',
        value: '". $order_info['total'] ."',
        currency: '". $this->session->data['currency'] ."'
        });";

      $this->document->setPixel($pixel);
      
	 $this->cart->clear(); 
	 $data['module_wgtm_status'] = $this->config->get('module_wgtm_status');
				$myorder_id = $this->session->data['order_id'];
				$data['order_id'] = $this->session->data['order_id'];
				$this->load->model('catalog/product');
				$this->load->model('catalog/category');
				$this->load->model('account/order');
				$order_info = $this->model_account_order->getOrder($myorder_id);
				$data['shipping_method'] = $order_info['shipping_method'];
				$data['total'] = $this->cart->getTotal();
				$data['tax'] = 0;
				$taxes = $this->cart->getTaxes();
				foreach ($taxes as $key => $value) {
					if ($value > 0) {
						$data['tax'] += $value;
					}
				}
				if (isset($this->request->post['coupon'])) {
					$data['coupon'] = $this->request->post['coupon'];
				} else {
					$data['coupon'] = '';
				}
				$data['shipping'] = isset($this->session->data['shipping_method']['cost']) ? $this->session->data['shipping_method']['cost'] : '';
				$data['currency_code'] = $order_info['currency_code'];
				$data['productss'] = array();
				$products = $this->model_account_order->getOrderProducts($myorder_id);
				foreach ($products as $product) {
					$product_info = $this->model_catalog_product->getProduct($product['product_id']);
					if ($product_info) {
						$reorder = $this->url->link('account/order/reorder', 'order_id=' . $myorder_id . '&order_product_id=' . $product['order_product_id'], true);
					} else {
						$reorder = '';
					}
					
					$getcategories = $this->model_catalog_product->getCategories($product['product_id']);
					$categories = array();
					if($getcategories){
						foreach($getcategories as $cate){
							$getcategory = $this->model_catalog_category->getCategory($cate['category_id']);
							$categories[] = isset($getcategory['name']) ? $getcategory['name'] : '';
						}
					}
					$categories = implode(", ", $categories);
					$data['productss'][] = array(
						'id'       => $product['product_id'],
						'name'     => $product['name'],
						'model'    => $product['model'],
						'categories'    => $categories,
						'manufacturer'    => $product_info['manufacturer'],
						'quantity' => $product['quantity'],
						'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value'], false),
					);
				}
	$this->load->model('checkout/order');
	$data['orderDetails'] = $this->model_checkout_order->getOrder($this->session->data['order_id']);

	$this->load->model('account/order');
	
	$data['action'] = $this->url->link('account/register', '', true);
	

		

		$order_info = $this->model_account_order->getOrder($this->session->data['order_id']);

	
	//$products = $this->model_account_order->getOrderProducts($this->session->data['order_id']);
	$this->load->model('catalog/product'); // Products
	$data['products'] = array();
	$products = $this->model_account_order->getOrderProducts($this->session->data['order_id']);
	foreach($products as $product)
		{
		$option_data = array();
		$options = $this->model_account_order->getOrderOptions($this->session->data['order_id'], $product['order_product_id']);
		foreach($options as $option)
			{
			if ($option['type'] != 'file')
				{
				$value = $option['value'];
				}
			  else
				{
				$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);
				if ($upload_info)
					{
					$value = $upload_info['name'];
					}
				  else
					{
					$value = '';
					}
				}

			$option_data[] = array(
				'name' => $option['name'],
				'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
			);
			}

		$product_info = $this->model_catalog_product->getProduct($product['product_id']);
	
   
		$data['products'][] = array(
	'name' => $product['name'],
	'model' => $product['model'],
	'option' => $option_data,
	'quantity' => $product['quantity'],
	'price' => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0) , $order_info['currency_code'], $order_info['currency_value']) ,
	'total' => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0) , $order_info['currency_code'], $order_info['currency_value']) ,
	);
		}
		$data['totals'] = array();



			$totals = $this->model_account_order->getOrderTotals($this->session->data['order_id']);



			foreach ($totals as $total) {

				$data['totals'][] = array(

					'title' => $total['title'],

					'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']),

				);

			}
	$data['vouchers'] = array();



			$vouchers = $this->model_account_order->getOrderVouchers($this->session->data['order_id']);



			foreach ($vouchers as $voucher) {

				$data['vouchers'][] = array(

					'description' => $voucher['description'],

					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])

				);

			}
if ($order_info['invoice_no']) {

				$data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];

			} else {

				$data['invoice_no'] = '';

			}



			$data['order_id'] = $this->session->data['order_id'];

			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));



			if ($order_info['payment_address_format']) {

				$format = $order_info['payment_address_format'];

			} else {

				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';

			}



			$find = array(

				'{firstname}',

				'{lastname}',

				'{company}',

				'{address_1}',

				'{address_2}',

				'{city}',

				'{postcode}',

				'{zone}',

				'{zone_code}',

				'{country}'

			);



			$replace = array(

				'firstname' => $order_info['payment_firstname'],

				'lastname'  => $order_info['payment_lastname'],

				'company'   => $order_info['payment_company'],

				'address_1' => $order_info['payment_address_1'],

				'address_2' => $order_info['payment_address_2'],

				'city'      => $order_info['payment_city'],

				'postcode'  => $order_info['payment_postcode'],

				'zone'      => $order_info['payment_zone'],

				'zone_code' => $order_info['payment_zone_code'],

				'country'   => $order_info['payment_country']

			);



			$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));



			$data['payment_method'] = $order_info['payment_method'];



			if ($order_info['shipping_address_format']) {

				$format = $order_info['shipping_address_format'];

			} else {

				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';

			}



			$find = array(

				'{firstname}',

				'{lastname}',

				'{company}',

				'{address_1}',

				'{address_2}',

				'{city}',

				'{postcode}',

				'{zone}',

				'{zone_code}',

				'{country}'

			);



			$replace = array(

				'firstname' => $order_info['shipping_firstname'],

				'lastname'  => $order_info['shipping_lastname'],

				'company'   => $order_info['shipping_company'],

				'address_1' => $order_info['shipping_address_1'],

				'address_2' => $order_info['shipping_address_2'],

				'city'      => $order_info['shipping_city'],

				'postcode'  => $order_info['shipping_postcode'],

				'zone'      => $order_info['shipping_zone'],

				'zone_code' => $order_info['shipping_zone_code'],

				'country'   => $order_info['shipping_country']

			);



			$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));



			$data['shipping_method'] = $order_info['shipping_method'];


			$data['comment'] = nl2br($order_info['comment']);

$this->load->language('checkout/checkout');

		
		//echo '<pre>';print_r($products);echo '</pre>';//echo '<pre>';print_r($data['products']);echo '</pre>';
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);  
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_basket'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_checkout'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('checkout/success')
		);
		if ($this->request->server['HTTPS']) {

			$server = $this->config->get('config_ssl');

		} else {

			$server = $this->config->get('config_url');

		}
	$data['base'] = $server;

		if ($this->customer->isLogged()) {
			$data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', true), $this->url->link('account/order', '', true), $this->url->link('account/download', '', true), $this->url->link('information/contact'));
		} else {
			$data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
		}

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

			
		$this->response->setOutput($this->load->view('common/successpaypal', $data));
	}
}