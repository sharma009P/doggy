<?php
class ControllerAccountCustomerpartnerNotification extends Controller {

	public function index() {

		$data = array();

		$data = array_merge($data, $this->load->language('account/customerpartner/notification'));

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/notification', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/customerpartner');

		$this->load->model('account/notification');

		$this->load->model('mp_localisation/order_status');

		$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

		if(!$data['chkIsPartner'] || (isset($this->session->data['marketplace_seller_mode']) && !$this->session->data['marketplace_seller_mode']))
			$this->response->redirect($this->url->link('account/account', '', true));

		$this->document->setTitle($data['text_notifications']);

		if($this->config->get('module_wk_seller_group_status')) {
			$this->load->model('account/customer_group');

			$isMember = $this->model_account_customer_group->getSellerMembershipGroup($this->customer->getId());

			if($isMember) {
				$allowedAccountMenu = $this->model_account_customer_group->getprofileOption($isMember['gid']);

				if($allowedAccountMenu['value']) {
					$accountMenu = explode(',',$allowedAccountMenu['value']);

					if($accountMenu) {
						foreach ($accountMenu as $key => $value) {
							$values = explode(':',$value);
							$data['allowed'][$values[0]] = $values[1];
						}
					}
				}
			}
		}

		$data['breadcrumbs'] = array();

  	$data['breadcrumbs'][] = array(
    	'text'      => $data['text_home'],
			'href'      => $this->url->link('common/home', '', true),
    	'separator' => false
  	);

  	$data['breadcrumbs'][] = array(
    	'text'      => $data['text_account'],
			'href'      => $this->url->link('account/account', '', true),
    	'separator' => false
  	);

  	$data['breadcrumbs'][] = array(
    	'text'      => $data['text_notifications'],
			'href'      => $this->url->link('account/customerpartner/notification', '', true),
    	'separator' => false
  	);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['isMember'] = true;

		if($this->config->get('module_wk_seller_group_status')) {
    	$data['module_wk_seller_group_status'] = true;

			$this->load->model('account/customer_group');

			$isMember = $this->model_account_customer_group->getSellerMembershipGroup($this->customer->getId());

			if($isMember) {
				$allowedAccountMenu = $this->model_account_customer_group->getaccountMenu($isMember['gid']);

				if($allowedAccountMenu['value']) {
					$accountMenu = explode(',',$allowedAccountMenu['value']);

					if($accountMenu && !in_array('notification:notification', $accountMenu)) {
						$data['isMember'] = false;
					}
				}
			} else {
				$data['isMember'] = false;
			}
    } else {
    	if(!is_array($this->config->get('marketplace_allowed_account_menu')) || !in_array('notification', $this->config->get('marketplace_allowed_account_menu'))) {
      	$this->response->redirect($this->url->link('account/account','', true));
      }
  	}

		$data['order_statuses'] = $this->model_mp_localisation_order_status->getOrderStatuses();

		foreach ($data['order_statuses'] as $key => $value) {
		  $data['order_statuses'][$key]['total'] = $this->model_account_notification->getTotalSellerActivity(array($value['order_status_id']));
		}

		$data['notification_filter'] = $this->config->get('marketplace_notification_filter');

		$data['return_total'] = $this->model_account_notification->getTotalSellerActivity(array('return'));

		$data['selected'] = array();

		if (isset($this->request->get['options']) && $this->request->get['options']) {
				$data['selected'] = explode(',',$this->request->get['options']);

				foreach ($data['selected'] as $key => $value) {
					$data['selected'][$key] = $this->db->escape($value);
				}
		}

		$data['all_notifications'] = $this->model_account_notification->getSellerActivityCount();

		$seller_notifications_total = $this->model_account_notification->getTotalSellerActivity($data['selected']);

		$data['seller_notifications'] = array();

		$seller_notifications = $this->model_account_notification->getSellerActivity($data['selected']);

		if ($seller_notifications) {
			foreach ($seller_notifications as $key => $seller_notification) {

				$date_diff = (array)(new DateTime($seller_notification['date_added']))->diff(new DateTime());

				if (isset($date_diff['y']) && $date_diff['y']) {
				  $seller_notification['date_added'] = $date_diff['y'].' '.$this->language->get('text_years');
				} elseif (isset($date_diff['m']) && $date_diff['m']) {
				  $seller_notification['date_added'] = $date_diff['m'].' '.$this->language->get('text_months');
				} elseif (isset($date_diff['d']) && $date_diff['d']) {
				  $seller_notification['date_added'] = $date_diff['d'].' '.$this->language->get('text_days');
				} elseif (isset($date_diff['h']) && $date_diff['h']) {
				  $seller_notification['date_added'] = $date_diff['h'].' '.$this->language->get('text_hours');
				} elseif (isset($date_diff['i']) && $date_diff['i']) {
				  $seller_notification['date_added'] = $date_diff['i'].' '.$this->language->get('text_minutes');
				}else {
				  $seller_notification['date_added'] = $date_diff['s'].' '.$this->language->get('text_seconds');
				}

				if ($seller_notification['key'] == 'order_account') {
					$seller_notification['data'] = json_decode($seller_notification['data'],1);

					$data['seller_notifications'][] = sprintf($this->language->get('text_order_add'),$seller_notification['data']['order_id'],$seller_notification['data']['order_id'],$seller_notification['data']['name'],$seller_notification['date_added']);
				} elseif ($seller_notification['key'] == 'return_account') {
					$seller_notification['data'] = json_decode($seller_notification['data'],1);

					$order_id = $this->model_account_notification->getReturnOrderId($seller_notification['data']['return_id']);

					$data['seller_notifications'][] = sprintf($this->language->get('text_order_return'),$seller_notification['data']['name'],$order_id['order_id'],$seller_notification['data']['return_id'],$order_id['product'],$seller_notification['date_added']);
				} elseif ($seller_notification['key'] == 'order_status') {
					$seller_notification['data'] = json_decode($seller_notification['data'],1);

					$status = $this->model_mp_localisation_order_status->getOrderStatus($seller_notification['data']['status']);
					if ($status) {
						if (empty($data['selected']) || in_array('all',$data['selected'])) {
						  $data['seller_notifications'][] = sprintf($this->language->get('text_order_status'),$seller_notification['data']['order_id'],$seller_notification['data']['order_id'],$status['name'],$seller_notification['date_added']);
						} else {
						  if (is_array($data['notification_filter']) && $data['notification_filter']) {
						    foreach ($data['notification_filter'] as $key => $value) {
						      if (in_array($value, $data['selected']) && $seller_notification['data']['status'] == $value) {
						        $data['seller_notifications'][] = sprintf($this->language->get('text_order_status'),$seller_notification['data']['order_id'],$seller_notification['data']['order_id'],$status['name'],$seller_notification['date_added']);
						      }
						    }
						  }
						}
					}
				}
			}
		}

		$seller_product_reviews = $this->model_account_notification->getSellerProductActivity();

		$seller_product_reviews_total = $this->model_account_notification->getSellerProductActivityTotal();

		if ($seller_product_reviews) {
			foreach ($seller_product_reviews as $key => $seller_product_review) {

				$date_diff = (array)(new DateTime($seller_product_review['date_added']))->diff(new DateTime());

				if (isset($date_diff['y']) && $date_diff['y']) {
				  $seller_product_review['date_added'] = $date_diff['y'].' '.$this->language->get('text_years');
				} elseif (isset($date_diff['m']) && $date_diff['m']) {
				  $seller_product_review['date_added'] = $date_diff['m'].' '.$this->language->get('text_months');
				} elseif (isset($date_diff['d']) && $date_diff['d']) {
				  $seller_product_review['date_added'] = $date_diff['d'].' '.$this->language->get('text_days');
				} elseif (isset($date_diff['h']) && $date_diff['h']) {
				  $seller_product_review['date_added'] = $date_diff['h'].' '.$this->language->get('text_hours');
				} elseif (isset($date_diff['i']) && $date_diff['i']) {
				  $seller_product_review['date_added'] = $date_diff['i'].' '.$this->language->get('text_minutes');
				}else {
				  $seller_product_review['date_added'] = $date_diff['s'].' '.$this->language->get('text_seconds');
				}

				$seller_product_review['data'] = json_decode($seller_product_review['data'],1);
				if ($seller_product_review['key'] == 'product_review') {
					$data['seller_product_reviews'][] = sprintf($this->language->get('text_product_review'),$seller_product_review['id'],$seller_product_review['data']['author'],$seller_product_review['data']['product_id'],$seller_product_review['data']['product_name'],$seller_product_review['date_added']);
				} elseif($seller_product_review['key'] == 'product_stock') {
					$data['seller_product_reviews'][] = sprintf($this->language->get('text_product_stock'),$seller_product_review['data']['product_id'],$seller_product_review['data']['product_name'],$seller_product_review['date_added']);
				} elseif ($seller_product_review['key'] == 'product_approve') {
					$data['seller_product_reviews'][] = sprintf($this->language->get('text_product_approve'),$seller_product_review['data']['product_id'],$seller_product_review['data']['product_name'],$seller_product_review['date_added']);
				}
			}
		}

		$data['seller_reviews'] = array();

		$seller_reviews = $this->model_account_notification->getSellerReviews();

		$seller_reviews_total = $this->model_account_notification->getSellerReviewsTotal();

		if ($seller_reviews) {
			foreach ($seller_reviews as $key => $seller_review) {

				$date_diff = (array)(new DateTime($seller_review['createdate']))->diff(new DateTime());

				if (isset($date_diff['y']) && $date_diff['y']) {
					$seller_review['createdate'] = $date_diff['y'].' '.$this->language->get('text_years');
				} elseif (isset($date_diff['m']) && $date_diff['m']) {
					$seller_review['createdate'] = $date_diff['m'].' '.$this->language->get('text_months');
				} elseif (isset($date_diff['d']) && $date_diff['d']) {
					$seller_review['createdate'] = $date_diff['d'].' '.$this->language->get('text_days');
				} elseif (isset($date_diff['h']) && $date_diff['h']) {
					$seller_review['createdate'] = $date_diff['h'].' '.$this->language->get('text_hours');
				} elseif (isset($date_diff['i']) && $date_diff['i']) {
					$seller_review['createdate'] = $date_diff['i'].' '.$this->language->get('text_minutes');
				}else {
					$seller_review['createdate'] = $date_diff['s'].' '.$this->language->get('text_seconds');
				}

					$data['seller_reviews'][] = sprintf($this->language->get('text_seller_review'),$seller_review['id'],$seller_review['customer_id'],$seller_review['name'],$seller_review['createdate']);
			}
		}

		$categories = $this->model_account_notification->getSellerCategoryActivity();

		$categories_total = $this->model_account_notification->getSellerCategoryActivityTotal();

		if ($categories) {
		  foreach ($categories as $key => $category) {

		    $date_diff = (array)(new DateTime($category['date_added']))->diff(new DateTime());

		    if (isset($date_diff['y']) && $date_diff['y']) {
		      $category['date_added'] = $date_diff['y'].' year(s)';
		    } elseif (isset($date_diff['m']) && $date_diff['m']) {
		      $category['date_added'] = $date_diff['m'].' month(s)';
		    } elseif (isset($date_diff['d']) && $date_diff['d']) {
		      $category['date_added'] = $date_diff['d'].' day(s)';
		    } elseif (isset($date_diff['h']) && $date_diff['h']) {
		      $category['date_added'] = $date_diff['h'].' hour(s)';
		    } elseif (isset($date_diff['i']) && $date_diff['i']) {
		      $category['date_added'] = $date_diff['i'].' minute(s)';
		    }else {
		      $category['date_added'] = $date_diff['s'].' second(s)';
		    }
		    $category['data'] = json_decode($category['data'],1);

		    if (isset($category['data']['category_name']) && $category['data']['category_name']) {
		      $data['seller_reviews'][] = sprintf($this->language->get('text_category_approve'),$category['data']['category_name'], $category['data']['category_name'],$category['date_added']);
		    }
		  }
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['page_product'])) {
			$page_product = $this->request->get['page_product'];
		} else {
			$page_product = 1;
		}

		if (isset($this->request->get['page_seller'])) {
			$page_seller = $this->request->get['page_seller'];
		} else {
			$page_seller = 1;
		}

		$data['page'] = $page;

		$url = '';

		if (isset($this->request->get['options'])) {
			$url = '&options='.$this->request->get['options'];
		}

		//Pagination For Order Tab
		$pagination = new Pagination();
		$pagination->total = $seller_notifications_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('account/customerpartner/notification',$url.'&page={page}&page_product='.$page_product.'&page_seller='.$page_seller, true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($seller_notifications_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($seller_notifications_total - 10)) ? $seller_notifications_total : ((($page - 1) * 10) + 10), $seller_notifications_total, ceil($seller_notifications_total / 10));

		//Pagination For Product Tab
		$pagination_product = new Pagination();
		$pagination_product->total = $seller_product_reviews_total;
		$pagination_product->page = $page_product;
		$pagination_product->limit = 10;
		$pagination_product->url = $this->url->link('account/customerpartner/notification',$url.'&page_product={page}&page='.$page.'&page_seller='.$page_seller.'tab=product', true);

		$data['pagination_product'] = $pagination_product->render();

		$data['results_product'] = sprintf($this->language->get('text_pagination'), ($seller_product_reviews_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($seller_product_reviews_total - 10)) ? $seller_product_reviews_total : ((($page - 1) * 10) + 10), $seller_product_reviews_total, ceil($seller_product_reviews_total / 10));

		$seller_reviews_total = $seller_reviews_total + $categories_total;

		//Pagination For Seller Tab
		$pagination_seller = new Pagination();
		$pagination_seller->total = $seller_reviews_total;
		$pagination_seller->page = $page_seller;
		$pagination_seller->limit = 10;
		$pagination_seller->url = $this->url->link('account/customerpartner/notification',$url.'&page_seller={page}&page='.$page.'&page_product='.$page_product.'tab=seller', true);

		$data['pagination_seller'] = $pagination_seller->render();

		$data['results_seller'] = sprintf($this->language->get('text_pagination'), ($seller_reviews_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($seller_reviews_total - 10)) ? $seller_reviews_total : ((($page - 1) * 10) + 10), $seller_reviews_total, ceil($seller_reviews_total / 10));

		$data['back'] = $this->url->link('account/account','', true);

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

		if (isset($this->request->get['tab']) && $this->request->get['tab']) {
			$data['tab'] = $this->request->get['tab'];
		} else {
			$data['tab'] = 'order';
		}

		$marketplace_notification_viewed = $data['all_notifications'] + $seller_product_reviews_total + $seller_reviews_total;

		$this->model_account_notification->addViewedNotification($marketplace_notification_viewed);

		$this->response->setOutput($this->load->view('account/customerpartner/notification', $data));
	}

	public function notifications(){
		if ($this->customer->getId()) {

			$this->load->model('account/notification');

			$this->load->model('account/customerpartner');

			$this->load->model('mp_localisation/order_status');

			$data = array_merge($this->load->language('account/customerpartner/notification'));

			if (!isset($this->request->get['json_notification'])) {
				$data['sellerProfile'] = $this->model_account_customerpartner->getProfile();
			}

			$data['processing_status_total'] = $this->model_account_notification->getTotalSellerActivity(array('2'));

			$data['complete_status_total'] = $this->model_account_notification->getTotalSellerActivity(array('5'));

			$data['return_total'] = $this->model_account_notification->getTotalSellerActivity(array('return'));

			$data['view_all'] = $this->url->link('account/customerpartner/notification','',true);

			$data['notification_total'] = 0;

			$data['notification_total'] = $this->model_account_notification->getTotalSellerActivity() + $this->model_account_notification->getSellerProductActivityTotal() + $this->model_account_notification->getSellerReviewsTotal()-$this->model_account_notification->getViewedNotifications();

			if ($data['notification_total'] < 0) {
			  $data['notification_total'] = 0;
			}

			$data['seller_notifications'] = array();

			$seller_notifications = $this->model_account_notification->getSellerActivity(array(),3);

			if ($seller_notifications) {
				foreach ($seller_notifications as $key => $seller_notification) {

					$date_diff = (array)(new DateTime($seller_notification['date_added']))->diff(new DateTime());

					if (isset($date_diff['y']) && $date_diff['y']) {
					  $seller_notification['date_added'] = $date_diff['y'].' '.$this->language->get('text_years');
					} elseif (isset($date_diff['m']) && $date_diff['m']) {
					  $seller_notification['date_added'] = $date_diff['m'].' '.$this->language->get('text_months');
					} elseif (isset($date_diff['d']) && $date_diff['d']) {
					  $seller_notification['date_added'] = $date_diff['d'].' '.$this->language->get('text_days');
					} elseif (isset($date_diff['h']) && $date_diff['h']) {
					  $seller_notification['date_added'] = $date_diff['h'].' '.$this->language->get('text_hours');
					} elseif (isset($date_diff['i']) && $date_diff['i']) {
					  $seller_notification['date_added'] = $date_diff['i'].' '.$this->language->get('text_minutes');
					}else {
					  $seller_notification['date_added'] = $date_diff['s'].' '.$this->language->get('text_seconds');
					}

					if ($seller_notification['key'] == 'order_account') {
						$seller_notification['data'] = json_decode($seller_notification['data'],1);
						$data['seller_notifications'][] = sprintf($this->language->get('text_order_add_mp'),$seller_notification['data']['order_id'],$seller_notification['data']['order_id'],$seller_notification['data']['name'],$seller_notification['date_added']);
					} elseif ($seller_notification['key'] == 'return_account') {
						$seller_notification['data'] = json_decode($seller_notification['data'],1);
						$order_id = $this->model_account_notification->getReturnOrderId($seller_notification['data']['return_id']);
						$data['seller_notifications'][] = sprintf($this->language->get('text_order_return_mp'),$seller_notification['data']['name'],$order_id['order_id'],$seller_notification['data']['return_id'],$order_id['product'],$seller_notification['date_added']);
					} elseif ($seller_notification['key'] == 'order_status') {
						$seller_notification['data'] = json_decode($seller_notification['data'],1);
						$status = $this->model_mp_localisation_order_status->getOrderStatus($seller_notification['data']['status']);
						if ($status) {
							$data['seller_notifications'][] = sprintf($this->language->get('text_order_status_mp'),$seller_notification['data']['order_id'],$seller_notification['data']['order_id'],$status['name'],$seller_notification['date_added']);
						}
					}
				}
			}

			$data['seller_product_reviews'] = array();

			$data['product_stock_total'] = $this->model_account_notification->getProductStockTotal();

			$data['review_total'] = $this->model_account_notification->getReviewTotal();

			$data['approval_total'] = $this->model_account_notification->getApprovalTotal();

			$seller_product_reviews = $this->model_account_notification->getSellerProductActivity(array(),3);

			$data['product_review_total'] = $this->model_account_notification->getSellerProductActivityTotal();

			if ($seller_product_reviews) {
				foreach ($seller_product_reviews as $key => $seller_product_review) {
					$date_diff = (array)(new DateTime($seller_product_review['date_added']))->diff(new DateTime());

					if (isset($date_diff['y']) && $date_diff['y']) {
					  $seller_product_review['date_added'] = $date_diff['y'].' '.$this->language->get('text_years');
					} elseif (isset($date_diff['m']) && $date_diff['m']) {
					  $seller_product_review['date_added'] = $date_diff['m'].' '.$this->language->get('text_months');
					} elseif (isset($date_diff['d']) && $date_diff['d']) {
					  $seller_product_review['date_added'] = $date_diff['d'].' '.$this->language->get('text_days');
					} elseif (isset($date_diff['h']) && $date_diff['h']) {
					  $seller_product_review['date_added'] = $date_diff['h'].' '.$this->language->get('text_hours');
					} elseif (isset($date_diff['i']) && $date_diff['i']) {
					  $seller_product_review['date_added'] = $date_diff['i'].' '.$this->language->get('text_minutes');
					}else {
					  $seller_product_review['date_added'] = $date_diff['s'].' '.$this->language->get('text_seconds');
					}

					$seller_product_review['data'] = json_decode($seller_product_review['data'],1);
					if ($seller_product_review['key'] == 'product_review') {
						$data['seller_product_reviews'][] = sprintf($this->language->get('text_product_review'),$seller_product_review['id'],$seller_product_review['data']['author'],$seller_product_review['data']['product_id'],$seller_product_review['data']['product_name'],$seller_product_review['date_added']);
					} elseif($seller_product_review['key'] == 'product_stock') {
						$data['seller_product_reviews'][] = sprintf($this->language->get('text_product_stock'),$seller_product_review['data']['product_id'],$seller_product_review['data']['product_name'],$seller_product_review['date_added']);
					} elseif ($seller_product_review['key'] == 'product_approve') {
						$data['seller_product_reviews'][] = sprintf($this->language->get('text_product_approve'),$seller_product_review['data']['product_id'],$seller_product_review['data']['product_name'],$seller_product_review['date_added']);
					}
				}
			}

			$data['seller_reviews'] = array();

			$seller_reviews = $this->model_account_notification->getSellerReviews(array(),3);

			$data['seller_review_total'] = $this->model_account_notification->getSellerReviewsTotal();

			if ($seller_reviews) {
				foreach ($seller_reviews as $key => $seller_review) {
					if ($seller_review) {
						$date_diff = (array)(new DateTime($seller_review['createdate']))->diff(new DateTime());

						if (isset($date_diff['y']) && $date_diff['y']) {
						  $seller_review['createdate'] = $date_diff['y'].' '.$this->language->get('text_years');
						} elseif (isset($date_diff['m']) && $date_diff['m']) {
						  $seller_review['createdate'] = $date_diff['m'].' '.$this->language->get('text_months');
						} elseif (isset($date_diff['d']) && $date_diff['d']) {
						  $seller_review['createdate'] = $date_diff['d'].' '.$this->language->get('text_days');
						} elseif (isset($date_diff['h']) && $date_diff['h']) {
						  $seller_review['createdate'] = $date_diff['h'].' '.$this->language->get('text_hours');
						} elseif (isset($date_diff['i']) && $date_diff['i']) {
						  $seller_review['createdate'] = $date_diff['i'].' '.$this->language->get('text_minutes');
						}else {
						  $seller_review['createdate'] = $date_diff['s'].' '.$this->language->get('text_seconds');
						}

						$data['seller_reviews'][] = sprintf($this->language->get('text_seller_review_mp'),$seller_review['id'],$seller_review['customer_id'],$seller_review['name'],$seller_review['createdate']);
					}
				}
			}

			$categories = $this->model_account_notification->getSellerCategoryActivity();

			$categories_total = $this->model_account_notification->getSellerCategoryActivityTotal();

			if ($categories) {
			  foreach ($categories as $key => $category) {

			    $date_diff = (array)(new DateTime($category['date_added']))->diff(new DateTime());

			    if (isset($date_diff['y']) && $date_diff['y']) {
			      $category['date_added'] = $date_diff['y'].' year(s)';
			    } elseif (isset($date_diff['m']) && $date_diff['m']) {
			      $category['date_added'] = $date_diff['m'].' month(s)';
			    } elseif (isset($date_diff['d']) && $date_diff['d']) {
			      $category['date_added'] = $date_diff['d'].' day(s)';
			    } elseif (isset($date_diff['h']) && $date_diff['h']) {
			      $category['date_added'] = $date_diff['h'].' hour(s)';
			    } elseif (isset($date_diff['i']) && $date_diff['i']) {
			      $category['date_added'] = $date_diff['i'].' minute(s)';
			    }else {
			      $category['date_added'] = $date_diff['s'].' second(s)';
			    }
			    $category['data'] = json_decode($category['data'],1);

			    if (isset($category['data']['category_name']) && $category['data']['category_name']) {
			      $data['seller_reviews'][] = sprintf($this->language->get('text_category_approve'),$category['data']['category_name'], $category['data']['category_name'],$category['date_added']);
			    }
			  }
			}

			if (!isset($this->session->data['temp_separate_view']) && !(isset($this->request->get['json_notification']) && $this->request->get['json_notification'])) {
			  if (preg_match('/route=account\/customerpartner/',$this->request->server['QUERY_STRING'])) {
			    $this->session->data['temp_separate_view'] = true;
			  } else {
			    $this->session->data['temp_separate_view'] = false;
			  }
			}

			$data['separate_view'] = false;

			if ($this->config->get('marketplace_separate_view') && isset($this->session->data['marketplace_separate_view']) && $this->session->data['marketplace_separate_view'] == 'separate' && isset($this->session->data['temp_separate_view']) && $this->session->data['temp_separate_view']) {
			  $data['separate_view'] = true;

			}

			if (isset($this->request->get['json_notification'])) {
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($data));
			} else {
				return $this->load->view('account/customerpartner/notification_header', $data);
			}
		}
	}
}
?>
