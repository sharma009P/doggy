<?php
class ControllerCheckoutSuccessquare extends Controller {
	public function index() {
		$this->load->language('checkout/success');
		$this->load->language('account/order');
		$this->load->language('account/register');

		if (isset($this->session->data['order_id']))
	{
	 $this->cart->clear(); 
	$this->load->model('checkout/order');
	$data['orderDetails'] = $this->model_checkout_order->getOrder($this->session->data['order_id']);
/* 	 echo '<pre>';
	print_r($data['orderDetails']);
	echo '</pre>';  */
	$this->load->model('account/order');
	
	$data['action'] = $this->url->link('account/register', '', true);
	
	//print_r($data); die;
		

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
		/* if ($product_info)
			{
			$reorder = $this->url->link('account/order/reorder', 'order_id=' . $order_id . '&order_product_id=' . $product['order_product_id'], true);
			}
		  else
			{
			$reorder = '';
			} */
   
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
			unset($this->session->data['payment_address']);
			unset($this->session->data['shipping_address']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);  
		}
		
		$this->session->data['payment_address']['country_id'] = $this->config->get('config_country_id');
			$this->session->data['payment_address']['zone_id'] = $this->config->get('config_zone_id');

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
	
		$this->session->data['payment_address']['zone_id'] = '';
        $this->session->data['payment_address']['country_id'] = '';

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

		$this->response->setOutput($this->load->view('common/successquare', $data));
	}
}