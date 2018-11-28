<?php
class ControllerAccountCustomerpartnerOrderinfo extends Controller {

	private $data = array();

	public function index() {

		if (!$this->customer->isLogged()) {
			if(isset($this->request->get['order_id'])){
				$this->session->data['redirect'] = $this->url->link('account/customerpartner/orderinfo&order_id='.$this->request->get['order_id'], '', true);
			}
			$this->response->redirect($this->url->link('account/login', '', true));
		} else {
			if(!isset($this->request->get['order_id']) || empty($this->request->get['order_id'])){
				$this->response->redirect($this->url->link('account/customerpartner/orderlist', '', true));
			}
		}

		$this->load->model('account/customerpartner');

		$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

		if(!$data['chkIsPartner'] || (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode']))
			$this->response->redirect($this->url->link('account/account', '', true));

		$this->load->language('account/customerpartner/orderinfo');

		if (isset($this->request->get['order_id'])) {
			$order_id = (int)$this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

      	if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			if($order_id){

				if(isset($this->request->post['tracking'])){
					$count = $this->model_account_customerpartner->addOdrTracking($order_id,$this->request->post['tracking']);
					if ($count) {
							$this->session->data['success'] = $this->language->get('text_success');
					}
				}

		 		$this->response->redirect($this->url->link('account/customerpartner/orderinfo&order_id='.$order_id, '', true));
			}

		}

		if(isset($this->session->data['success'])){
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		}else{
			$data['success'] = '';
		}

		$data['order_id'] = $order_id;

		$this->load->model('account/order');

		$order_info = $this->model_account_customerpartner->getOrder($order_id);

		$this->document->setTitle($this->language->get('text_order'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', true),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', true),
			'separator' => $this->language->get('text_separator')
		);

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/customerpartner/orderlist', $url, true),
			'separator' => $this->language->get('text_separator')
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_order'),
			'href'      => $this->url->link('account/customerpartner/orderinfo', 'order_id=' . $order_id . $url, true),
			'separator' => $this->language->get('text_separator')
		);

		$data['errorPage'] = false;

		if($this->config->get('marketplace_available_order_status')) {
  			$data['marketplace_available_order_status'] = $this->config->get('marketplace_available_order_status');
  			$data['marketplace_order_status_sequence'] = $this->config->get('marketplace_order_status_sequence');
      	}

	    if ($this->config->get('marketplace_cancel_order_status') &&$this->config->get('marketplace_available_order_status')) {

  			$data['marketplace_cancel_order_status'] = $this->config->get('marketplace_cancel_order_status');

  			$cancel_order_statusId_key_available =  array_search($this->config->get('marketplace_cancel_order_status'),$data['marketplace_available_order_status'],true);

  			if ($cancel_order_statusId_key_available === 0 || $cancel_order_statusId_key_available) {

  			    unset($data['marketplace_available_order_status'][$cancel_order_statusId_key_available]);
  			    unset($data['marketplace_order_status_sequence'][$this->config->get('marketplace_cancel_order_status')]);

  			}

  			foreach ($data['marketplace_order_status_sequence'] as $key => $value) {

               if ($value['order_status_id'] == $this->config->get('marketplace_cancel_order_status')) {

               	   unset($data['marketplace_order_status_sequence'][$key]);

               }
  			}

  		}else{
  			$data['marketplace_cancel_order_status'] = '';
  		}

		if ($order_info) {

			$data['wksellerorderstatus'] = $this->config->get('marketplace_sellerorderstatus');

			if ($order_info['invoice_no']) {
				$data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$data['invoice_no'] = '';
			}

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

			$data['products'] = array();

			$products = $this->model_account_customerpartner->getSellerOrderProducts($order_id);

			// Uploaded files
			$this->load->model('tool/upload');

      		foreach ($products as $product) {
				$option_data = array();

				$options = $this->model_account_order->getOrderOptions($order_id, $product['order_product_id']);

         		 // code changes due to download file error
         		foreach ($options as $option) {
          			if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $option['value'],
							'type'  => $option['type']
						);
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);
						if ($upload_info) {
							$option_data[] = array(
								'name'  => $option['name'],
								'value' => $upload_info['name'],
								'type'  => $option['type'],
								'href'  => $this->url->link('account/customerpartner/orderinfo/download','&code=' . $upload_info['code'], true)
							);
						}
					}
        		}

        		$product_tracking = $this->model_account_customerpartner->getOdrTracking($data['order_id'],$product['product_id'],$this->customer->getId());

        		if($product['paid_status'] == 1) {
        			$paid_status = $this->language->get('text_paid');
        		} else {
        			$paid_status = $this->language->get('text_not_paid');
        		}

        		$data['products'][] = array(
          			'product_id'     => $product['product_id'],
          			'name'     => $product['name'],
          			'model'    => $product['model'],
          			'option'   => $option_data,
          			'tracking' => isset($product_tracking['tracking']) ? $product_tracking['tracking'] : '',
          			'quantity' => $product['quantity'],
          			'paid_status' => $paid_status,
          			'price'    => $this->currency->format($product['c2oprice'], $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'order_product_status' => $product['order_product_status'],
        		);
      		}

			// Voucher
			$data['vouchers'] = array();

			$vouchers = $this->model_account_order->getOrderVouchers($order_id);

			foreach ($vouchers as $voucher) {
				$data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}

      		$data['totals'] = array();

					$totals = $this->model_account_customerpartner->getOrderTotals($order_id);

					if ($totals) {

					  if (isset($totals[0]['shipping_applied']) && $totals[0]['shipping_applied']) {
					    $data['totals'][] = array(
					      'title' => $totals[0]['shipping'],
					      'text'  => $this->currency->format($totals[0]['shipping_applied'], $order_info['currency_code'], $order_info['currency_value']),
					    );
					  }

					  if (isset($totals[0]['total'])) {
					    $data['totals'][] = array(
					      'title' => $this->language->get('column_total'),
					      'text'  => $this->currency->format($totals[0]['total'], $order_info['currency_code'], $order_info['currency_value']),
					    );
					  }
					}

			$data['comment'] = nl2br($order_info['comment']);

			$data['histories'] = array();

			$results = $this->model_account_customerpartner->getOrderHistories($order_id);

      		foreach ($results as $result) {
        		$data['histories'][] = array(
          			'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
          			'status'     => $result['status'],
          			'comment'    => nl2br($result['comment'])
        		);
      		}

      		//list of status

      		$this->load->model('mp_localisation/order_status');

      		$data['order_statuses'] = $this->model_mp_localisation_order_status->getOrderStatuses();

      		$data['marketplace_complete_order_status'] = $this->config->get('marketplace_complete_order_status');

      		$data['order_status_id'] = $order_info['order_status_id'];

      	}else{
      		$data['errorPage'] = true;
      		$data['order_status_id'] = '';
      	}

  		$data['action'] = $this->url->link('account/customerpartner/orderinfo&order_id='.$order_id, '', true);
  		$data['continue'] = $this->url->link('account/customerpartner/orderlist', '', true);
  		$data['order_invoice'] = $this->url->link('account/customerpartner/soldinvoice&order_id='.$order_id, '', true);

  		/*
  		Access according to membership plan
  		 */
  		$data['isMember'] = true;
		if($this->config->get('module_wk_seller_group_status')) {
      		$data['module_wk_seller_group_status'] = true;
      		$this->load->model('account/customer_group');
			$isMember = $this->model_account_customer_group->getSellerMembershipGroup($this->customer->getId());
			if($isMember) {
				$allowedAccountMenu = $this->model_account_customer_group->getaccountMenu($isMember['gid']);
				if($allowedAccountMenu['value']) {
					$accountMenu = explode(',',$allowedAccountMenu['value']);
					if($accountMenu && !in_array('orderhistory:orderhistory', $accountMenu)) {
						$data['isMember'] = false;
					}
				}
			} else {
				$data['isMember'] = false;
			}
      	} else {
      		if(!is_array($this->config->get('marketplace_allowed_account_menu')) || !in_array('orderhistory', $this->config->get('marketplace_allowed_account_menu'))) {
      			$this->response->redirect($this->url->link('account/account','', true));
      		}
      	}

      	/*
      	end here
      	 */
  		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

$data['separate_view'] = false;

$data['separate_column_left'] = '';

if ($this->config->get('marketplace_separate_view') && isset($this->session->data['marketplace_separate_view']) && $this->session->data['marketplace_separate_view'] == 'separate') {
  $data['separate_view'] = true;
  $data['column_left'] = '';
  $data['column_right'] = '';
  $data['content_top'] = '';
  $data['content_bottom'] = '';
  $data['separate_column_left'] = $this->load->controller('account/customerpartner/column_left');

  $data['footer'] = $this->load->controller('account/customerpartner/footer');
  $data['header'] = $this->load->controller('account/customerpartner/header');
}

		$this->response->setOutput($this->load->view('account/customerpartner/orderinfo' , $data));

	}

	public function history() {
		$this->load->language('account/customerpartner/orderinfo');
		$this->load->model('account/customerpartner');

		$json = array();

		if ($this->config->get('marketplace_cancel_order_status')) {
			$marketplace_cancel_order_status = $this->config->get('marketplace_cancel_order_status');
		}else{
			$marketplace_cancel_order_status = '';
		}

        if (isset($this->request->post['comment']) && !empty($this->request->post['comment']) && empty($this->request->post['product_ids'])) {

        	$getOrderStatusId = $this->model_account_customerpartner->getOrderStatusId((int)$this->request->get['order_id']);

        	$this->request->post['order_status_id'] = $getOrderStatusId['order_status_id'];

        	$this->model_account_customerpartner->addOrderHistory((int)$this->request->get['order_id'],$this->request->post);

        	$this->model_account_customerpartner->addSellerOrderStatus((int)$this->request->get['order_id'],'',$this->request->post);

        	$json['success'] = $this->language->get('text_success_history');

        }elseif(isset($this->request->post['product_ids']) && !empty($this->request->post['product_ids'])){

        	$products = explode(",", $this->request->post['product_ids']);

        	$this->load->model('mp_localisation/order_status');
            $order_statuses = $this->model_mp_localisation_order_status->getOrderStatuses();

        	foreach ($order_statuses as $value) {

				if (in_array($this->request->post['order_status_id'], $value)) {

					$seller_change_order_status_name = $value['name'];

				}
			}
					if (isset($seller_change_order_status_name) && $seller_change_order_status_name) {
						if ($this->request->post['order_status_id'] == $marketplace_cancel_order_status) {
							 $this->changeOrderStatus($this->request->get,$this->request->post,$products,$marketplace_cancel_order_status,$seller_change_order_status_name);
						}else{
							 $this->changeOrderStatus($this->request->get,$this->request->post,$products,$marketplace_cancel_order_status,$seller_change_order_status_name);
						}
					}

        	$json['success'] = $this->language->get('text_success_history');

        }else{

        	$json['error'] = $this->language->get('error_product_select');
        }

		$this->response->setOutput(json_encode($json));
  	}

  	private function changeOrderStatus($get,$post,$products,$marketplace_cancel_order_status,$seller_change_order_status_name){


         /**
          * First step - Add seller changing status for selected products
          */
	     $this->model_account_customerpartner->addsellerorderproductstatus($get['order_id'],$post['order_status_id'],$products);


	     // Second Step - add comment for each selected products
		 $this->model_account_customerpartner->addSellerOrderStatus($get['order_id'],$post['order_status_id'],$post,$products,$seller_change_order_status_name);

         // Thired Step - Get status Id that will be the whole order status id after changed the order product status by seller
		 $getWholeOrderStatus = $this->model_account_customerpartner->getWholeOrderStatus($get['order_id'],$marketplace_cancel_order_status);


         // Fourth Step - add comment in order_history table and send mails to admin(If admin notify is enable) and customer
		 $this->model_account_customerpartner->addOrderHistory($get['order_id'],$post,$seller_change_order_status_name);


         // Fifth Step - Update whole order status in order table
         if ($getWholeOrderStatus) {
             $this->model_account_customerpartner->changeWholeOrderStatus($get['order_id'],$getWholeOrderStatus);
         }

  	}

  	// file download code
  	public function download() {
		$this->load->model('tool/upload');

		if (isset($this->request->get['code'])) {
			$code = $this->request->get['code'];
		} else {
			$code = 0;
		}

		$upload_info = $this->model_tool_upload->getUploadByCode($code);

		if ($upload_info) {
			$file = DIR_UPLOAD . $upload_info['filename'];
			$mask = basename($upload_info['name']);

			if (!headers_sent()) {
				if (is_file($file)) {
					header('Content-Type: application/octet-stream');
					header('Content-Description: File Transfer');
					header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
					header('Content-Transfer-Encoding: binary');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize($file));

					readfile($file, 'rb');
					exit;
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}
		} else {
			$this->load->language('error/not_found');

			$this->document->setTitle($this->language->get('heading_title'));

			$data['heading_title'] = $this->language->get('heading_title');

			$data['error_warning_authenticate'] = $this->language->get('error_warning_authenticate');

			$data['text_not_found'] = $this->language->get('text_not_found');

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('error/not_found', 'user_token=' . $this->session->data['user_token'], true)
			);

			$data['header'] = $this->load->controller('common/header');

$data['separate_view'] = false;

$data['separate_column_left'] = '';

if ($this->config->get('marketplace_separate_view') && isset($this->session->data['marketplace_separate_view']) && $this->session->data['marketplace_separate_view'] == 'separate') {
  $data['separate_view'] = true;
  $data['column_left'] = '';
  $data['column_right'] = '';
  $data['content_top'] = '';
  $data['content_bottom'] = '';
  $data['separate_column_left'] = $this->load->controller('account/customerpartner/column_left');

  $data['footer'] = $this->load->controller('account/customerpartner/footer');
  $data['header'] = $this->load->controller('account/customerpartner/header');
}
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('error/not_found.twig', $data));
		}
	}
}
?>
