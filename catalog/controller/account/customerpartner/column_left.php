<?php
class ControllerAccountCustomerpartnerColumnLeft extends Controller {
	public function index() {

		$this->load->language('account/customerpartner/column_left');

		$this->load->model('tool/image');

		$data['firstname'] = '';
		$data['lastname'] = '';
		$data['screenname'] = '';
		$data['image'] = '';
		$data['version'] = VERSION;

		$data['menus'] = array();

		if ($this->config->get('module_marketplace_status') && is_array($this->config->get('marketplace_allowed_account_menu')) && $this->config->get('marketplace_allowed_account_menu')) {

			$this->load->model('account/customerpartner');

			$sellerProfile = $this->model_account_customerpartner->getProfile();

			if (isset($sellerProfile['firstname']) && $sellerProfile['firstname']) {
				$data['firstname'] = $sellerProfile['firstname'];
			}

			if (isset($sellerProfile['lastname']) && $sellerProfile['lastname']) {
				$data['lastname'] = $sellerProfile['lastname'];
			}

			if (isset($sellerProfile['screenname']) && $sellerProfile['screenname']) {
				$data['screenname'] = $sellerProfile['screenname'];
			}

			if (isset($sellerProfile['avatar']) && $sellerProfile['avatar'] && is_file(DIR_IMAGE . $sellerProfile['avatar'])) {
				$data['image'] = $this->model_tool_image->resize($sellerProfile['avatar'], 45, 45);
			} else {
				$data['image'] = '';
			}

			if (in_array('dashboard', $this->config->get('marketplace_allowed_account_menu'))) {
				$data['menus'][] = array(
					'id'       => 'menu-dashboard',
					'icon'	   => 'fa-dashboard',
					'name'	   => $this->language->get('text_dashboard'),
					'href'     => $this->url->link('account/customerpartner/dashboard', '', true),
					'children' => array()
				);
			}

			if (in_array('profile', $this->config->get('marketplace_allowed_account_menu'))) {
				$data['menus'][] = array(
					'id'       => 'menu-profile',
					'icon'	   => 'fa-user-plus',
					'name'	   => $this->language->get('text_profile'),
					'href'     => $this->url->link('account/customerpartner/profile', '', true),
					'children' => array()
				);
	    }

			if (in_array('category', $this->config->get('marketplace_allowed_account_menu'))) {
			  $data['menus'][] = array(
			    'id'       => 'menu-category',
			    'icon'	   => 'fa-tags',
			    'name'	   => $this->language->get('text_category'),
			    'href'     => $this->url->link('account/customerpartner/category', '', true),
			    'children' => array()
			  );
			}

			$product = array();

			if (in_array('productlist', $this->config->get('marketplace_allowed_account_menu'))) {
				$product[] = array(
					'name'	   => $this->language->get('text_productlist'),
					'href'     => $this->url->link('account/customerpartner/productlist', '', true),
					'children' => array()
				);
	    }

			if (in_array('addproduct', $this->config->get('marketplace_allowed_account_menu'))) {
				$product[] = array(
					'name'	   => $this->language->get('text_addproduct'),
					'href'     => $this->url->link('account/customerpartner/addproduct', '', true),
					'children' => array()
				);
	    }


			if ($product) {
				$data['menus'][] = array(
					'id'       => 'menu-product',
					'icon'	   => 'fa-building',
					'name'	   => $this->language->get('text_product'),
					'href'     => '',
					'children' => $product
				);
			}

			if (in_array('orderhistory', $this->config->get('marketplace_allowed_account_menu'))) {
				$data['menus'][] = array(
					'id'       => 'menu-orderhistory',
					'icon'	   => 'fa-shopping-cart',
					'name'	   => $this->language->get('text_orderhistory'),
					'href'     => $this->url->link('account/customerpartner/orderlist', '', true),
					'children' => array()
				);
	    }

			if (in_array('transaction', $this->config->get('marketplace_allowed_account_menu'))) {
				$data['menus'][] = array(
				  'id'       => 'menu-transaction',
				  'icon'	   => 'fa-credit-card',
				  'name'	   => $this->language->get('text_transaction'),
				  'href'     => $this->url->link('account/customerpartner/transaction', '', true),
				  'children' => array()
				);
	    }

			if (in_array('downloads', $this->config->get('marketplace_allowed_account_menu'))) {
				$data['menus'][] = array(
				  'id'       => 'menu-downloads',
				  'icon'	   => 'fa-download',
				  'name'	   => $this->language->get('text_downloads'),
				  'href'     => $this->url->link('account/customerpartner/download', '', true),
				  'children' => array()
				);
	    }

			if (in_array('manageshipping', $this->config->get('marketplace_allowed_account_menu'))) {
				$data['menus'][] = array(
				  'id'       => 'menu-manageshipping',
				  'icon'	   => 'fa-truck',
				  'name'	   => $this->language->get('text_manageshipping'),
				  'href'     => $this->url->link('account/customerpartner/add_shipping_mod', '', true),
				  'children' => array()
				);
	    }

			if (in_array('review', $this->config->get('marketplace_allowed_account_menu'))) {
			  $data['menus'][] = array(
			    'id'       => 'menu-review',
			    'icon'	   => 'fa-comments-o',
			    'name'	   => $this->language->get('text_review'),
			    'href'     => $this->url->link('account/customerpartner/review', '', true),
			    'children' => array()
			  );
			}

			if (in_array('information', $this->config->get('marketplace_allowed_account_menu'))) {
			  $data['menus'][] = array(
			    'id'       => 'menu-information',
			    'icon'	   => 'fa-info-circle',
			    'name'	   => $this->language->get('text_information'),
			    'href'     => $this->url->link('account/customerpartner/information', '', true),
			    'children' => array()
			  );
			}

			if (in_array('notification', $this->config->get('marketplace_allowed_account_menu'))) {
				$data['menus'][] = array(
				  'id'       => 'menu-notification',
				  'icon'	   => 'fa-bell-o',
				  'name'	   => $this->language->get('text_notification'),
				  'href'     => $this->url->link('account/customerpartner/notification', '', true),
				  'children' => array()
				);
	    }
		}
		return $this->load->view('account/customerpartner/column_left', $data);
	}
}
