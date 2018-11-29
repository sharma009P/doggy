<?php

/**
* @version 2.2.0.0
* @copyright Webkul Software Pvt Ltd
*/

class ControllerCustomerpartnerProfile extends Controller {

	/**
	 * [$error description] Array to contain all errors
	 * @var array
	 */
	private $error = array();

	public function index() {
		if(!isset($this->request->get['id']))
			$this->request->get['id'] = 0;

		$seller_id = (int)$this->request->get['id'];

		if ($this->config->get('marketplace_seller_info_hide') && $seller_id != $this->customer->getId()) {
				$this->response->redirect($this->url->link('common/home', '', 'SSL'));
		}

		$this->load->model('tool/image');

		$this->load->model('customerpartner/master');

		$this->load->language('customerpartner/profile');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->language('customerpartner/feedback');

		$this->load->language('extension/module/marketplace');

		$data['logged'] = $this->customer->isLogged();
		$data['send_mail'] = $this->url->link('account/customerpartner/sendmail','',true);
		$data['mail_for'] = '&contact_seller=true';

		$this->document->addStyle('catalog/view/theme/default/stylesheet/MP/profile.css');

      	if(isset($this->request->get['collection'])) {
      		$data['showCollection'] = true;
      	} else {
      		$data['showCollection'] = false;
      	}

      	$data['breadcrumbs'] = array();

              //updated for seller buyer communication
              if($this->config->get('module_wk_communication_status') && 		$this->customer->isLogged()) {
                  $data['module_wk_communication_status'] = $this->config->get('module_wk_communication_status');
                  $this->load->language('customerpartner/contact');
                  $data['text_contact'] = $this->language->get('text_contact');
                  $data['customerlogged'] = "log";
                  $data['contact'] = $this->url->link('customerpartner/profile/contact&id='.$this->request->get['id'],'',true);
                }
            

      	$data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', '', true),
        	'separator' => false
      	);

      	/**
      	 * Code for marketplace membership if module is installed the only it will work
      	 */
		if($this->config->get('module_wk_seller_group_status')) {
			$this->load->model('account/customer_group');
			$isMember = $this->model_account_customer_group->getSellerMembershipGroup($seller_id);
			if($isMember) {
				$allowedAccountMenu = $this->model_account_customer_group->getpublicSellerProfile($isMember['gid'], $seller_id);
				if($allowedAccountMenu['value']) {
					$accountMenu = explode(',',$allowedAccountMenu['value']);
					if($accountMenu) {
						foreach ($accountMenu as $key => $value) {
							$values = explode(':',$value);
							$data['public_seller_profile'][$values[0]] = $values[1];
						}
					}
				}
			}
		} else if($this->config->get('marketplace_allowed_public_seller_profile')) {
			$data['public_seller_profile'] = $this->config->get('marketplace_allowed_public_seller_profile');
		}

		$partner = $this->model_customerpartner_master->getProfile($seller_id);

		if(!$partner)
			$this->response->redirect($this->url->link('error/not_found', '', true));

		if ($partner['companybanner'] && file_exists(DIR_IMAGE . $partner['companybanner'])) {
			$partner['companybanner'] = HTTP_SERVER.'image/'.$partner['companybanner'];
		} else {
			if($partner['companybanner'] != 'removed' && $this->config->get('marketplace_default_image_name')) {
				$partner['companybanner'] = HTTP_SERVER.'image/'.$this->config->get('marketplace_default_image_name');
			} else {
				$partner['companybanner'] = '';
			}
		}

		if ($partner['companylogo'] && file_exists(DIR_IMAGE . $partner['companylogo'])) {
			$partner['companylogo'] = $this->model_tool_image->resize($partner['companylogo'], 300, 80 );
		} else if($this->config->get('marketplace_default_image_name') && file_exists(DIR_IMAGE . $this->config->get('marketplace_default_image_name'))) {
			if($partner['companylogo'] != 'removed') {
				$partner['companylogo'] = $this->model_tool_image->resize($this->config->get('marketplace_default_image_name'), 300, 80 );
			} else {
				$partner['companylogo'] = '';
			}
		}

		if ($partner['avatar'] && file_exists(DIR_IMAGE . $partner['avatar'])) {
			$partner['avatar'] = $this->model_tool_image->resize($partner['avatar'], 120, 120);
		} else if($this->config->get('marketplace_default_image_name') && file_exists(DIR_IMAGE . $this->config->get('marketplace_default_image_name'))) {
			if($partner['avatar'] != 'removed') {
				$partner['avatar'] = $this->model_tool_image->resize($this->config->get('marketplace_default_image_name'), 100, 100);
			} else {
				$partner['avatar'] = '';
			}
		}

		if ($this->config->get('marketplace_profile_email')) {
			$data['email'] = 1;
		} else {
			$data['email'] = 0;
		}

		if ($this->config->get('marketplace_profile_telephone')) {
			$data['telephone'] = 1;
		} else {
			$data['telephone'] = 0;
		}

		$this->load->model('customerpartner/information');

		$data['informations'] = array();

		if (isset($this->request->get['id']) && $this->request->get['id']) {
		  $informations = $this->model_customerpartner_information->getSellerInformations($this->request->get['id']);

		  if ($informations) {
		    foreach ($informations as $result) {
		      $data['informations'][] = array(
		        'title' => $result['title'],
		        'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
		      );
		    }
		  }
		}

		$data['customer_id'] = $this->customer->getId();

		$data['partner'] = $partner;
		

		$data['partner']['shortprofile'] = html_entity_decode($data['partner']['shortprofile']);
		$data['partner']['companydescription'] = html_entity_decode($data['partner']['companydescription']);

		$data['feedback_total'] = $this->model_customerpartner_master->getAverageFeedback($seller_id);
		$data['seller_total_products'] = $this->model_customerpartner_master->getPartnerCollectionCount($seller_id);

		$data['loadLocation'] = $this->url->link('customerpartner/profile/loadLocation&location='.$partner['companylocality'],'',true);
		$data['feedback'] = $this->url->link('customerpartner/profile/feedback&id='.$seller_id,'',true);
		$data['writeFeedback'] = $this->url->link('customerpartner/profile/writeFeedback&id='.$seller_id,'',true);
		$data['product_feedback'] = $this->url->link('customerpartner/profile/productFeedback&id='.$seller_id,'',true);
		$data['collection'] = $this->url->link('customerpartner/profile/collection&id='.$seller_id,'',true);

		$data['product_feedback_total'] = $this->model_customerpartner_master->getTotalProductFeedbackList($seller_id);
		$data['collection_total'] = $this->model_customerpartner_master->getPartnerCollectionCount($seller_id);

		$this->session->data['redirect'] = $this->url->link('customerpartner/profile&id='.$seller_id,'',true);
		$data['login'] = $this->url->link('account/login','',true);
		$data['seller_id'] = $seller_id;
		$data['isLogged'] = $this->customer->isLogged();

		$data['marketplace_customercontactseller'] = $this->config->get('marketplace_customercontactseller');

		$data['give_review'] = true;
		if ($this->config->get('marketplace_review_only_order')) {

			if ($this->customer->isLogged() && ($seller_id != $this->customer->getId())) {
				$check_customer = $this->model_customerpartner_master->checkCustomerBought($seller_id);

				if (!$check_customer && isset($data['public_seller_profile']['review'])) {
                	$data['give_review'] = false;
                }
			}else{

                if (isset($data['public_seller_profile']['review'])) {
                	$data['give_review'] = false;
                }
			}
		}
        $data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['review_fields'] = $this->model_customerpartner_master->getAllReviewFields();

    $data['header'] = $this->load->controller('common/header');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('customerpartner/profile', $data));

	}

	/**
	 * [loadLocation To load the location of store entered by seller on the google map]
	 * @return [map|string] [It will return google map with the location if location found else no location entered by seller string]
	 */
	public function loadLocation(){
		if($this->request->get['location']){
			$location = '<iframe id="seller-location" width="100%" height="200" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q='.$this->request->get['location'].'&output=embed&z=15"></iframe>';
		}else{
			$location = '<iframe id="seller-location" width="100%" height="200" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=noida&output=embed&z=15"></iframe>';
			// $this->load->language('customerpartner/profile');
			// $this->response->setOutput($this->language->get('text_no_location_added'));
		}

		$this->response->setOutput($location);
	}

	/**
	 * [feedback to load all the feedbacks about the seller]
	 * @return [html] [it will return html file]
	 */
	public function feedback(){

		if(!isset($this->request->get['id']))
			$this->request->get['id'] = 0;

		$seller_id = (int)$this->request->get['id'];

		$page = 1;

		$this->load->language('customerpartner/feedback');

		$data['action'] = $this->url->link('customerpartner/profile/feedback','&id='.$seller_id,true);

		$this->load->model('customerpartner/master');

		$feedbacks = $this->model_customerpartner_master->getFeedbackList($seller_id);

		echo '<script>
		 $(document).ready(function () {
		 createCookie("time_diff");
		 });

		 function createCookie(name) {
			var rightNow = new Date();
			var jan1 = new Date(rightNow.getFullYear(), 0, 1, 0, 0, 0, 0);
			var temp = jan1.toGMTString();
			var jan2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
			var std_time_offset = (jan1 - jan2) / (1000 * 60 * 60);
		  	document.cookie = escape(name) + "=" + std_time_offset + "; path=/";
		 }
		</script>';

		if (isset($_COOKIE['time_diff'])) {
			$time_diff = $_COOKIE['time_diff'] * 3600;
		}

		$data['feedbacks'] = array();

		if ($feedbacks) {
			$review_fields = $this->model_customerpartner_master->getAllReviewFields();
			$data['review_fields'] = $review_fields;
			foreach ($feedbacks as $key => $feedback) {

				$review_attributes = array();

				if ($review_fields) {
					foreach ($review_fields as $key => $value) {
						$attribute_value = $this->model_customerpartner_master->getReviewAttributeValue($feedback['id'],$value['field_id']);
						if (isset($attribute_value['field_value']) && $attribute_value['field_value']) {
							$review_attributes[$value['field_id']] = $attribute_value['field_value'];
						}
					}
				}

				$date = strtotime($feedback['createdate']);
				if (isset($time_diff) && $time_diff) {
					$date = $date + $time_diff;
				}
				$data['feedbacks'][] = array(
		            'id' => $feedback['id'],
		            'customer_id' => $feedback['customer_id'],
		            'seller_id' => $feedback['seller_id'],
		            'nickname' => $feedback['nickname'],
		            'summary' => $feedback['summary'],
		            'review' => $feedback['review'],
		            'createdate' => date('F j, Y', $date),
								'review_attributes'	=> $review_attributes
				);
			}
		}

		$feedback_total = $this->model_customerpartner_master->getTotalFeedback($seller_id);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($feedback_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($feedback_total - 5)) ? $feedback_total : ((($page - 1) * 5) + 5), $feedback_total, ceil($feedback_total / 5));

		$this->response->setOutput($this->load->view('customerpartner/feedback', $data));
	}

	/**
	* [productFeedback to get feedback on seller's product]
	* @return [html] [It will return html file]
	*/
	public function productFeedback() {

		if(!isset($this->request->get['id']))
			$this->request->get['id'] = 0;

		$seller_id = (int)$this->request->get['id'];

		$page = 1;

		$this->load->language('customerpartner/feedback');
		$this->load->model('catalog/product');
		$this->load->model('customerpartner/master');

		$reviews = $this->model_customerpartner_master->getProductFeedbackList($seller_id);

		$data['reviews'] = array();
		if($reviews) {
			foreach ($reviews as $key => $review) {
					$product_infos = $this->model_catalog_product->getProduct($review['product_id']);
					//print_r($product_infos);
				$thumb = $this->config->get('config_url').'image/'.$product_infos['image'];
				$d = date_create($review['date_added']);
				$data['reviews'][] = array(
					'author' => $review['author'],
					'thumb' => $thumb,
					'name' => $review['name'],
					'href' => $this->url->link('product/product', 'product_id=' . $review['product_id'],true),
					'text' => $review['text'],
					'rating' => $review['rating'],
					'date_added' => date_format($d, 'F j, Y'),
				);
			}
		}

		$product_feedback_total = $this->model_customerpartner_master->getTotalProductFeedbackList($seller_id);

		$data['pagination'] = '';

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_feedback_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($product_feedback_total - 5)) ? $product_feedback_total : ((($page - 1) * 5) + 5), $product_feedback_total, ceil($product_feedback_total / 5));
        $this->response->setOutput($this->load->view('customerpartner/review', $data));

	}


                /**
                 * [Contact to load the languages and information about the sender and reciver]
                 * @return   [html]  [it will return html file]
                 */
                 public function contact() {
                   if(!isset($this->request->get['id']))
                    $this->request->get['id'] = 0;
 	                 $this->load->language('customerpartner/contact');
                   //allowed extension
                   $extension = $this->config->get('module_wk_communication_type');
                   $extensions = explode(',', $extension);
                   $data['extension'] = $extensions;
                   $data['max'] =$this->config->get('module_wk_communication_max');
                   $data['size'] =$this->config->get('module_wk_communication_size');
                   $data['type'] =explode(",", $this->config->get('module_wk_communication_type'));
                   $data['id'] = $this->request->get['id'];
                   $data['max'] = $this->config->get('module_wk_communication_max');

                   $data['from'] = $this->customer->getEmail();
                   $data['to'] = $this->request->post['email'];
                   $data['action'] = $this->url->link('account/customerpartner/sendquery','',true);
                  $this->response->setOutput($this->load->view('customerpartner/contact', $data));

                 }
          
	/**
	 * [writeFeedback to store customers feedbacks]
	 * @return [json] [string containing successful/unsuccessful message]
	 */
	public function writeFeedback() {

		$this->load->language('customerpartner/feedback');

		$this->load->model('customerpartner/master');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			if ((utf8_strlen(trim($this->request->post['name'])) < 3) || (utf8_strlen(trim($this->request->post['name'])) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((utf8_strlen(trim($this->request->post['text'])) < 25) || (utf8_strlen(trim($this->request->post['text']) > 1000))) {
				$json['error'] = $this->language->get('error_text');
			}

			$attribute_fields = $this->model_customerpartner_master->getAllReviewFields();

			if ($attribute_fields) {
				foreach ($attribute_fields as $key => $value) {
					if (!isset($this->request->post['review_attributes'][$value['field_id']]) || $this->request->post['review_attributes'][$value['field_id']] < 0 || $this->request->post['review_attributes'][$value['field_id']] > 5) {
						$json['error'] = $this->language->get('error_attribute');
					}
				}
			}

			if (!isset($json['error'])) {
				$this->load->model('customerpartner/master');
				$this->model_customerpartner_master->saveFeedback($this->request->post,(int)$this->request->get['id']);
				$json['success'] = $this->language->get('text_success');
			}
		}

		$this->response->addHeader('Content-Type: application/json');

		$this->response->setOutput(json_encode($json));
	}

	/**
	* [collection to get seller's product's collection]
	* @return [html] [It will return html file containing seller's products]
	*/
	public function collection() {

		if(!isset($this->request->get['id']))
			$this->request->get['id'] = 0;

		$seller_id = (int)$this->request->get['id'];

		$this->load->model('tool/image');

		$this->load->model('catalog/category');

		$this->load->model('account/customerpartner');

		$this->load->model('customerpartner/master');

		$this->load->language('customerpartner/collection');

		$this->load->language('product/category');

		$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

		$partner = $this->model_customerpartner_master->getProfile($seller_id);

		if(!$partner)
			$this->response->redirect($this->url->link('error/not_found', '', true));

		$data['compare'] = $this->url->link('product/compare' , '' . true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		$url = "&id=".$seller_id;


		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = 10;
		}

		$filter_data = array(
			'customer_id'		 => $seller_id,
			'filter_category_id' => 0,
			'sort'               => $sort,
			'order'              => $order,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit,
			'filter_store' 		 => $this->config->get('config_store_id'),
			'filter_status'		 => 1
		);

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			$children_data = array();

			$children = $this->model_catalog_category->getCategories($category['category_id']);

			foreach ($children as $child) {

				$filter_data ['filter_category_id']  = $child['category_id'];

				$products_in_category = $this->model_account_customerpartner->getTotalProductsSeller($filter_data);

				if($products_in_category)
					$children_data[] = array(
						'category_id' => $child['category_id'],
						'name'        => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $products_in_category . ')' : ''),
						'href'        => $this->url->link('customerpartner/profile/collection', 'path=' . $category['category_id'] . '_' . $child['category_id'].$url,true)
					);
			}

			$filter_data ['filter_category_id']  = $category['category_id'];

			$products_in_category = $this->model_account_customerpartner->getTotalProductsSeller($filter_data);

			if($products_in_category){
				$data['categories'][] = array(
					'category_id' => $category['category_id'],
					'name'        => $category['name'] . ($this->config->get('config_product_count') ? ' (' . $products_in_category . ')' : ''),
					'children'    => $children_data,
					'href'        => $this->url->link('customerpartner/profile/collection', 'path=' . $category['category_id'].$url,true)
				);
			}elseif ($children_data) {
				$data['categories'][] = array(
					'category_id' => $category['category_id'],
					'name'        => $category['name'] . ($this->config->get('config_product_count') ? ' (' . count($children_data) . ')' : ''),
					'children'    => $children_data,
					'href'        => $this->url->link('customerpartner/profile/collection', 'path=' . $category['category_id'].$url,true)
				);
			}
		}

		$data['collection_url'] = $this->url->link('customerpartner/profile/collection', 'id=' . $seller_id, true);

		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		if (isset($parts[0])) {
			$data['category_id'] = $category_id = $parts[0];
		} else {
			$data['category_id'] = $category_id = 0;
		}

		if (isset($parts[1])) {
			$data['child_id'] = $category_id = $parts[1];
		} else {
			$data['child_id'] = 0;
		}

		$filter_data ['filter_category_id']  = $category_id;

		if (isset($this->request->get['path'])) {
			$url .= '&path=' . $this->request->get['path'];
		}

		$this->load->model('catalog/product');

		$results = $this->model_account_customerpartner->getProductsSeller($filter_data);

		$product_total = $this->model_account_customerpartner->getTotalProductsSeller($filter_data);

		$data['products'] = array();
$favourite = $this->config->get('config_url').'image/catalog/bone-new.png';
		
		foreach ($results as $result) {

			$product_info = $this->model_catalog_product->getProduct($result['product_id']);

			if (isset($product_info['price']) && $product_info['price']) {
			  $result['price'] = $product_info['price'];
			}

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

			$data['products'][] = array(
				'product_id'  => $result['product_id'],
				'thumb'       => $image,
				'favourite'       => $favourite,
				'name'        => $result['name'],
				'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
				'price'       => $price,
				'special'     => $special,
				'minimum'     => $result['minimum'],
				'tax'         => $tax,
				'rating'      => $result['rating'],
				'href'        => $this->url->link('product/product', '&product_id=' . $result['product_id'] ,true)
			);
		}

		$data['sorts'] = array();

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_default'),
			'value' => 'p.sort_order-ASC',
			'href'  => $this->url->link('customerpartner/profile/collection', '&sort=p.sort_order&order=ASC' . $url,true)
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_name_asc'),
			'value' => 'pd.name-ASC',
			'href'  => $this->url->link('customerpartner/profile/collection','&sort=pd.name&order=ASC' . $url,true)
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_name_desc'),
			'value' => 'pd.name-DESC',
			'href'  => $this->url->link('customerpartner/profile/collection', '&sort=pd.name&order=DESC' . $url,true)
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_price_asc'),
			'value' => 'p.price-ASC',
			'href'  => $this->url->link('customerpartner/profile/collection','&sort=p.price&order=ASC' . $url,true)
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_price_desc'),
			'value' => 'p.price-DESC',
			'href'  => $this->url->link('customerpartner/profile/collection', '&sort=p.price&order=DESC' . $url,true)
		);

		if ($this->config->get('config_review_status')) {
			$data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_desc'),
				'value' => 'rating-DESC',
				'href'  => $this->url->link('customerpartner/profile/collection', '&sort=rating&order=DESC' . $url,true)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_asc'),
				'value' => 'rating-ASC',
				'href'  => $this->url->link('customerpartner/profile/collection', '&sort=rating&order=ASC' . $url,true)
			);
		}

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_model_asc'),
			'value' => 'p.model-ASC',
			'href'  => $this->url->link('customerpartner/profile/collection','&sort=p.model&order=ASC' . $url,true)
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_model_desc'),
			'value' => 'p.model-DESC',
			'href'  => $this->url->link('customerpartner/profile/collection','&sort=p.model&order=DESC' . $url,true)
		);

		$url = "id=".$seller_id;

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['path'])) {
			$url .= '&path=' . $this->request->get['path'];
		}

		$data['limits'] = array();

		$limits = array_unique(array(10, 25, 50, 75, 100));

		sort($limits);

		foreach($limits as $value) {
			$data['limits'][] = array(
				'text'  => $value,
				'value' => $value,
				'href'  => $this->url->link('customerpartner/profile/collection', $url . '&limit=' . $value,true)
			);
		}

		$url = "id=".$seller_id;

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}

		if (isset($this->request->get['path'])) {
			$url .= '&path=' . $this->request->get['path'];
		}

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('customerpartner/profile/collection' , $url . '&page={page}',true);

		$data['pagination'] = $pagination->render();

		$this->document->addLink($this->url->link('customerpartner/profile/collection', $url . '&page=' . $pagination->page,true), 'canonical');

		if ($pagination->limit && ceil($pagination->total / $pagination->limit) > $pagination->page) {
			$this->document->addLink($this->url->link('customerpartner/profile/collection', $url . '&page=' . ($pagination->page + 1),true), 'next');
		}

		if ($pagination->page > 1) {
			$this->document->addLink($this->url->link('customerpartner/profile/collection', $url . '&page=' . ($pagination->page - 1),true), 'prev');
		}

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;

		$this->response->setOutput($this->load->view('customerpartner/collection', $data));
	}

}
?>
