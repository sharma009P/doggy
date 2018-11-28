<?php
class ControllerCommonHeader extends Controller {
	public function index() {

        $data['facebook_pixel_id_FAE'] =
          $this->fbevents['facebook_pixel_id_FAE'];
        $data['facebook_pixel_pii_FAE'] =
          $this->fbevents['facebook_pixel_pii_FAE'];
        $data['facebook_pixel_params_FAE'] =
          $this->fbevents['facebook_pixel_params_FAE'];
        $data['facebook_pixel_params_FAE'] =
          $this->fbevents['facebook_pixel_params_FAE'];
        $data['facebook_pixel_event_params_FAE'] =
          $this->fbevents['facebook_pixel_event_params_FAE'];

        // remove away the facebook_pixel_event_params_FAE in session data
        // to avoid duplicate firing after the 1st fire
        unset($this->session->data['facebook_pixel_event_params_FAE']);
      
		// Analytics
		$this->load->model('setting/extension');

		$data['analytics'] = array();

		$analytics = $this->model_setting_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
			}
		}

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
		}


               $data['quickview'] = $this->load->controller('extension/module/pavquickview');
            


            $this->document->addStyle( 'catalog/view/javascript/pavobuilder/dist/pavobuilder.min.css' );
            $this->document->addScript( 'catalog/view/javascript/pavobuilder/dist/pavobuilder.min.js' );
        

            $data['pavothemer'] = $this->load->controller('extension/module/pavothemer');
            $id = $this->config->get( 'pavothemer_header_blockbuilder' );
            if( $id ){
                $data['pavoheader'] = '';
                $this->load->model( 'setting/module' );
                $setting_info = $this->model_setting_module->getModule( $id );

                if ($setting_info && $setting_info['status']) {
                    $data['pavoheader'] = $this->load->controller('extension/module/pavoheader' , $setting_info);
                }
            }

       
		$data['title'] = $this->document->getTitle();

		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts('header');
		$data['lang'] = $this->language->get('code');

          $data['pixel'] = $this->document->getPixel();
      
		$data['direction'] = $this->language->get('direction');
// 		$data['country_code'] = $this->ip_info("Visitor", "Country Code"); // IN
  //       if($data['country_code'] == 'US'){
  //       	$this->request->post['code'] = 'USD';
  //       }else{
  //       	$this->request->post['code'] = 'GBP';
  //       }
        
  //     if (isset($this->request->post['code'])) {
		// $this->session->data['currency'] = $this->request->post['code'];
	 //  }
	 
		
		$data['name'] = 'Doggy';//$this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}


        $data['notification'] = '';
        $data['sellmenu'] =  $this->load->controller('extension/module/marketplace/sellmenu');
        if($this->config->get('module_marketplace_status')){
    				$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();
    				$data['marketplace_seller_mode'] = isset($this->session->data['marketplace_seller_mode']) ? $this->session->data['marketplace_seller_mode'] : 1;
    				if ($data['chkIsPartner'] && $data['marketplace_seller_mode']) {
    					$data['notification'] = $this->load->controller('account/customerpartner/notification/notifications');

    					$data['notification_total'] = $this->model_account_notification->getTotalSellerActivity() + $this->model_account_notification->getSellerProductActivityTotal() + $this->model_account_notification->getSellerReviewsTotal()-$this->model_account_notification->getViewedNotifications();
    				}
    		}
      
		$this->load->language('common/header');

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));
		
		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['logged'] = $this->customer->isLogged();
		$data['account'] = $this->url->link('account/account', '', true);
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['contact'] = $this->url->link('information/contact');
		$data['telephone'] = $this->config->get('config_telephone');

                $data['facebook_app_id'] = $this->config->get('pavoblog_facebook_appid');
            
		
		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');
		$data['cart'] = $this->load->controller('common/cart');
		$data['menu'] = $this->load->controller('common/menu');

                //Call Pav megamenu module to display
                $data['pavmegamenu'] = $this->load->controller('extension/module/pavmegamenu');
            
        $data['action_currency'] = $this->url->link('common/currency/currency', '', $this->request->server['HTTPS']);
        if (!isset($this->request->get['route'])) {
			$data['redirect'] = $this->url->link('common/home');
		} else {
			$url_data = $this->request->get;

			unset($url_data['_route_']);

			$route = $url_data['route'];

			unset($url_data['route']);

			$url = '';

			if ($url_data) {
				$url = '&' . urldecode(http_build_query($url_data, '', '&'));
			}

			$data['redirect'] = $this->url->link($route, $url, $this->request->server['HTTPS']);
		}
		


				$data['module_wgtm_gtmcode'] = $this->config->get('module_wgtm_gtmcode');
				$data['module_wgtm_status'] = $this->config->get('module_wgtm_status');
			
		
        $data['body_class'] = $this->getBodyClass();
        // pavothemer header layout file

        return $this->load->view('common/header', $data);
    }

    /**
     * Get body class
     */
    public function getBodyClass() {
        $route = ! empty( $this->request->get['route'] ) ? $this->request->get['route'] : 'common/home';

        $class = "" ;
        if( $this->config->get( 'pavothemer_keepheader' ) ){
            $class = ' has-header-sticky';
        }

        return 'page-' . implode( '-', explode( '/', $route ) ) . $class;
            
	}

}
