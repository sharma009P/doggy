<?php
class ControllerCommonColumnLeft extends Controller {
	public function index() {

        $token = (isset($this->session->data['user_token']))
          ? $this->session->data['user_token']
          : $this->session->data['token'];
        $tokenString = 'token=' . $token . '&user_token=' . $token;
        $facebookAdsExtension = array();
        $facebookAdsExtension[] = array(
          'name'     => 'Facebook Ads Extension',
          'href'     => $this->url->link('extension/facebookadsextension', $tokenString, true),
          'children' => array()
        );

        $data['menus'][] = array(
          'id'       => 'menu-facebook-ads-extension',
          'icon'     => 'fa-facebook-square',
          'name'     => 'Facebook Ads Extension',
          'href'     => '',
          'children' => $facebookAdsExtension
        );
      
		if (isset($this->request->get['user_token']) && isset($this->session->data['user_token']) && ($this->request->get['user_token'] == $this->session->data['user_token'])) {
			$this->load->language('common/column_left');

			// Create a 3 level menu array
			// Level 2 can not have children
			
			// Menu
			$data['menus'][] = array(
				'id'       => 'menu-dashboard',
				'icon'	   => 'fa-dashboard',
				'name'	   => $this->language->get('text_dashboard'),
				'href'     => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
				'children' => array()
			);
			

        // marketplace code starts here

        $marketplace = $this->load->controller('extension/module/marketplace/adminmenu');

        if ($marketplace) {
						$data['menus'][] = array(
								'id'       => 'menu-marketplace',
								'icon'     => 'fa-users',
								'name'     => $this->language->get('text_marketplace'),
								'href'     => '',
								'children' => $marketplace
						);
				}
        // marketplace code ends here
      

                //Communication of buyer seller starts here
                if($this->config->get('module_wk_communication_status') && $this->user->hasPermission('access', 'communication/wk_communication')) {
                        $data['menus'][] = array(
                            'id'       => 'menu-wk_communication',
                            'icon'     => 'fa fa-commenting-o',
                            'name'     => $this->language->get('text_communication'),
                            'href'     => $this->url->link('communication/wk_communication', 'user_token=' . $this->session->data['user_token'], 'true'),
                            'children' => array()
                        );
                }
                

                // pavoblog language file
                $this->load->language('extension/module/pavoblog');

                // PavoBlog Settings menu
                $settings = array();

                // access posts
                if ( $this->user->hasPermission( 'access', 'extension/module/pavoblog/posts' ) ) {
                    $settings[] = array(
                        'name'     => $this->language->get('menu_posts_text'),
                        'href'     => $this->url->link('extension/module/pavoblog/index', 'user_token=' . $this->session->data['user_token'], true),
                        'children' => array()
                    );
                }

                // modify posts
                if ( $this->user->hasPermission( 'modify', 'extension/module/pavoblog/post' ) ) {
                    $settings[] = array(
                        'name'     => $this->language->get('menu_post_add_new_text'),
                        'href'     => $this->url->link('extension/module/pavoblog/post', 'user_token=' . $this->session->data['user_token'], true),
                        'children' => array()
                    );
                }

                // access categories
                if ( $this->user->hasPermission( 'access', 'extension/module/pavoblog/categories' ) ) {
                    $settings[] = array(
                        'name'     => $this->language->get('menu_categories_text'),
                        'href'     => $this->url->link('extension/module/pavoblog/categories', 'user_token=' . $this->session->data['user_token'], true),
                        'children' => array()
                    );
                }

                // access comments
                if ( $this->user->hasPermission( 'access', 'extension/module/pavoblog/comments' ) ) {
                    $settings[] = array(
                        'name'     => $this->language->get('menu_comments_text'),
                        'href'     => $this->url->link('extension/module/pavoblog/comments', 'user_token=' . $this->session->data['user_token'], true),
                        'children' => array()
                    );
                }
                // view settings panel
                if ( $this->user->hasPermission( 'access', 'extension/module/pavoblog/settings' ) ) {
                    $settings[] = array(
                        'name'     => $this->language->get('menu_settings_text'),
                        'href'     => $this->url->link('extension/module/pavoblog/settings', 'user_token=' . $this->session->data['user_token'], true),
                        'children' => array()
                    );
                }

                if ( $settings ) {
                    $data['menus'][] = array(
                        'id'       => 'pavoblog',
                        'icon'     => 'fa fa-tasks fw',
                        'name'     => $this->language->get( 'heading_title' ),
                        'href'     => '',
                        'children' => $settings
                    );
                }
                // End PavoBlog Settings menu
            

                // pavmegamenu language file
                $this->load->language('extension/module/pavmegamenu');

                // pavmegamenu Settings menu
                $settings = array();
                // view settings panel
                if ( $this->user->hasPermission( 'access', 'extension/module/pavmegamenu' ) ) {
                    $settings[] = array(
                        'name'     => $this->language->get('text_megamenu_menus'),
                        'href'     => $this->url->link('extension/module/pavmegamenu', 'user_token=' . $this->session->data['user_token'], true),
                        'children' => array()
                    );
                }

                if ( $settings ) {
                    $data['menus'][] = array(
                        'id'       => 'pavmegamenu',
                        'icon'     => 'fa fa-bars',
                        'name'     => $this->language->get( 'text_megamenu_menus' ),
                        'href'     => '',
                        'children' => $settings
                    );
                }
                // End pavmegamenu Settings menu
            

                // pavothemer language file
                $this->load->language('extension/module/pavothemer');

                // PavoThemer Settings menu
                $settings = array();
                // view settings panel
                if ( $this->user->hasPermission( 'access', 'extension/module/pavothemer/edit' ) ) {
                    $settings[] = array(
                        'name'     => $this->language->get('menu_settings_text'),
                        'href'     => $this->url->link('extension/module/pavothemer/edit', 'user_token=' . $this->session->data['user_token'], true),
                        'children' => array()
                    );
                }
                $settings[] = array(
                    'name'     => $this->language->get('menu_theme_text'),
                    'href'     => $this->url->link('extension/theme/'.$this->config->get('config_theme'), 'store_id=0&user_token=' . $this->session->data['user_token'], true),
                    'children' => array()
                );

                // access customize
                if ( $this->user->hasPermission( 'access', 'extension/module/pavothemer/customize' ) ) {
                    $settings[] = array(
                       'name'     => $this->language->get('menu_customize_text'),
                        'href'     => $this->url->link('extension/module/pavothemer/customize', 'user_token=' . $this->session->data['user_token'], true),
                        'children' => array()
                    );
                }

                // view settings panel
                $theme = $this->config->get( 'config_theme' );
                if ( $this->user->hasPermission( 'access', 'extension/module/pavothemer/tools' ) || $this->user->hasPermission( 'access', 'extension/module/pavothemer/tools' ) ) {
                    $settings[] = array(
                        'name'     => $this->language->get('menu_tool_text'),
                        'href'     => $this->url->link('extension/module/pavothemer/tools', 'user_token=' . $this->session->data['user_token'], true),
                        'children' => array()
                    );
                }

                // theme management
                if ( $this->user->hasPermission( 'access', 'extension/module/pavothemer/management' ) || $this->user->hasPermission( 'access', 'extension/module/pavothemer/management' ) ) {
                    $settings[] = array(
                        'name'     => $this->language->get('menu_management_text'),
                        'href'     => $this->url->link('extension/module/pavothemer/management', 'user_token=' . $this->session->data['user_token'], true),
                        'children' => array()
                    );
                }

                // view homebuilder
                if ( $this->user->hasPermission( 'access', 'extension/module/pavobuilder' ) || $this->user->hasPermission( 'access', 'extension/module/pavobuilder' ) ) {
                    $settings[] = array(
                        'name'     => $this->language->get('menu_builder_text'),
                        'href'     => $this->url->link('extension/module/pavobuilder', 'user_token=' . $this->session->data['user_token'], true),
                        'children' => array()
                    );
                }

                // view header builder
                if ( $this->user->hasPermission( 'access', 'extension/module/pavoheader' ) || $this->user->hasPermission( 'access', 'extension/module/pavoheader' ) ) {
                    $settings[] = array(
                        'name'     => $this->language->get('menu_header_builder_text'),
                        'href'     => $this->url->link('extension/module/pavoheader', 'user_token=' . $this->session->data['user_token'], true),
                        'children' => array()
                    );
                }

                if ( $settings ) {
                    $data['menus'][] = array(
                        'id'       => 'pavothemer',
                        'icon'     => 'fa fa-cog fw',
                        'name'     => $this->language->get( 'heading_title' ),
                        'href'     => '',
                        'children' => $settings
                    );
                }
                // End PavoThemer Settings menu
            
			// Catalog
			$catalog = array();
			
			if ($this->user->hasPermission('access', 'catalog/category')) {
				$catalog[] = array(
					'name'	   => $this->language->get('text_category'),
					'href'     => $this->url->link('catalog/category', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'catalog/product')) {
				$catalog[] = array(
					'name'	   => $this->language->get('text_product'),
					'href'     => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
    
			$this->load->library('stockutils');
			if ($this->user->hasPermission('access', StockModuleConstants::$STOCK_MODULE_PERMISSION) && $this->config->get(StockModuleConfig::$ENABLED)) {
				$lang = $this->load->language(StockModuleConstants::$STOCK_LANGUAGE_FILE);
				$catalog[] = array(
					'name'	   => $lang['sm_text_menu_stock'],
					'href'     => $this->url->link(StockModuleConstants::$STOCK_MODULE_ROUTE, StockUtils::tokenParam($this->session), true),
					'children' => array()		
				);
			}      	
      
			if ($this->user->hasPermission('access', 'catalog/recurring')) {
				$catalog[] = array(
					'name'	   => $this->language->get('text_recurring'),
					'href'     => $this->url->link('catalog/recurring', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'catalog/filter')) {
				$catalog[] = array(
					'name'	   => $this->language->get('text_filter'),
					'href'     => $this->url->link('catalog/filter', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			// Attributes
			$attribute = array();
			
			if ($this->user->hasPermission('access', 'catalog/attribute')) {
				$attribute[] = array(
					'name'     => $this->language->get('text_attribute'),
					'href'     => $this->url->link('catalog/attribute', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()	
				);
			}
			
			if ($this->user->hasPermission('access', 'catalog/attribute_group')) {
				$attribute[] = array(
					'name'	   => $this->language->get('text_attribute_group'),
					'href'     => $this->url->link('catalog/attribute_group', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($attribute) {
				$catalog[] = array(
					'name'	   => $this->language->get('text_attribute'),
					'href'     => '',
					'children' => $attribute
				);
			}
			
			if ($this->user->hasPermission('access', 'catalog/option')) {
				$catalog[] = array(
					'name'	   => $this->language->get('text_option'),
					'href'     => $this->url->link('catalog/option', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'catalog/manufacturer')) {
				$catalog[] = array(
					'name'	   => $this->language->get('text_manufacturer'),
					'href'     => $this->url->link('catalog/manufacturer', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'catalog/download')) {
				$catalog[] = array(
					'name'	   => $this->language->get('text_download'),
					'href'     => $this->url->link('catalog/download', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'catalog/review')) {		
				$catalog[] = array(
					'name'	   => $this->language->get('text_review'),
					'href'     => $this->url->link('catalog/review', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);		
			}
			
			if ($this->user->hasPermission('access', 'catalog/information')) {		
				$catalog[] = array(
					'name'	   => $this->language->get('text_information'),
					'href'     => $this->url->link('catalog/information', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);					
			}
			
			if ($catalog) {
				$data['menus'][] = array(
					'id'       => 'menu-catalog',
					'icon'	   => 'fa-tags', 
					'name'	   => $this->language->get('text_catalog'),
					'href'     => '',
					'children' => $catalog
				);		
			}
			
			// Extension

				// checkout
				$checkout = array();
				if ($this->user->hasPermission('access', 'extension/onepagecheckout')) {
					$checkout[] = array(
						'name'	   => 'Checkout',
						'href'     => $this->url->link('extension/onepagecheckout', 'user_token=' . $this->session->data['user_token'], true),
						'children' => array()	
					);
				}
				if ($this->user->hasPermission('access', 'extension/order_success_page')) {
					$checkout[] = array(
						'name'	   => 'Order Success Page',
						'href'     => $this->url->link('extension/order_success_page', 'user_token=' . $this->session->data['user_token'], true),
						'children' => array()	
					);
				}
				
				if ($checkout) {
					$data['menus'][] = array(
						'id'       => 'menu-sale',
						'icon'	   => 'fa-shopping-cart', 
						'name'	   => 'Onepage Checkout',
						'href'     => '',
						'children' => $checkout
					);
				}
			
			$marketplace = array();
			
			if ($this->user->hasPermission('access', 'marketplace/marketplace')) {		
				$marketplace[] = array(
					'name'	   => $this->language->get('text_marketplace'),
					'href'     => $this->url->link('marketplace/marketplace', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);					
			}
			
			if ($this->user->hasPermission('access', 'marketplace/installer')) {		
				$marketplace[] = array(
					'name'	   => $this->language->get('text_installer'),
					'href'     => $this->url->link('marketplace/installer', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);					
			}	
			
			if ($this->user->hasPermission('access', 'marketplace/extension')) {		
				$marketplace[] = array(
					'name'	   => $this->language->get('text_extension'),
					'href'     => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}
								
			if ($this->user->hasPermission('access', 'marketplace/modification')) {
				$marketplace[] = array(
					'name'	   => $this->language->get('text_modification'),
					'href'     => $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'marketplace/event')) {
				$marketplace[] = array(
					'name'	   => $this->language->get('text_event'),
					'href'     => $this->url->link('marketplace/event', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
					
			if ($marketplace) {					
				$data['menus'][] = array(
					'id'       => 'menu-extension',
					'icon'	   => 'fa-puzzle-piece', 
					'name'	   => $this->language->get('text_extension'),
					'href'     => '',
					'children' => $marketplace
				);		
			}
			
			// Design
			$design = array();
			
			if ($this->user->hasPermission('access', 'design/layout')) {
				$design[] = array(
					'name'	   => $this->language->get('text_layout'),
					'href'     => $this->url->link('design/layout', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);	
			}
			
			if ($this->user->hasPermission('access', 'design/theme')) {	
				$design[] = array(
					'name'	   => $this->language->get('text_theme'),
					'href'     => $this->url->link('design/theme', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
			
			if ($this->user->hasPermission('access', 'design/translation')) {
				$design[] = array(
					'name'	   => $this->language->get('text_language_editor'),
					'href'     => $this->url->link('design/translation', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
						
			if ($this->user->hasPermission('access', 'design/banner')) {
				$design[] = array(
					'name'	   => $this->language->get('text_banner'),
					'href'     => $this->url->link('design/banner', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'design/seo_url')) {
				$design[] = array(
					'name'	   => $this->language->get('text_seo_url'),
					'href'     => $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
						
			if ($design) {
				$data['menus'][] = array(
					'id'       => 'menu-design',
					'icon'	   => 'fa-television', 
					'name'	   => $this->language->get('text_design'),
					'href'     => '',
					'children' => $design
				);	
			}
			
			// Sales
			$sale = array();
			
			if ($this->user->hasPermission('access', 'sale/order')) {
				$sale[] = array(
					'name'	   => $this->language->get('text_order'),
					'href'     => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
			
			if ($this->user->hasPermission('access', 'sale/recurring')) {	
				$sale[] = array(
					'name'	   => $this->language->get('text_recurring'),
					'href'     => $this->url->link('sale/recurring', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
			
			if ($this->user->hasPermission('access', 'sale/return')) {
				$sale[] = array(
					'name'	   => $this->language->get('text_return'),
					'href'     => $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
			
			// Voucher
			$voucher = array();
			
			if ($this->user->hasPermission('access', 'sale/voucher')) {
				$voucher[] = array(
					'name'	   => $this->language->get('text_voucher'),
					'href'     => $this->url->link('sale/voucher', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
			
			if ($this->user->hasPermission('access', 'sale/voucher_theme')) {
				$voucher[] = array(
					'name'	   => $this->language->get('text_voucher_theme'),
					'href'     => $this->url->link('sale/voucher_theme', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
			
			if ($voucher) {
				$sale[] = array(
					'name'	   => $this->language->get('text_voucher'),
					'href'     => '',
					'children' => $voucher		
				);		
			}
			
			if ($sale) {
				$data['menus'][] = array(
					'id'       => 'menu-sale',
					'icon'	   => 'fa-shopping-cart', 
					'name'	   => $this->language->get('text_sale'),
					'href'     => '',
					'children' => $sale
				);
			}
			
			// Customer
			$customer = array();
			
			if ($this->user->hasPermission('access', 'customer/customer')) {
				$customer[] = array(
					'name'	   => $this->language->get('text_customer'),
					'href'     => $this->url->link('customer/customer', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
			
			if ($this->user->hasPermission('access', 'customer/customer_group')) {
				$customer[] = array(
					'name'	   => $this->language->get('text_customer_group'),
					'href'     => $this->url->link('customer/customer_group', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
				
			if ($this->user->hasPermission('access', 'customer/customer_approval')) {
				$customer[] = array(
					'name'	   => $this->language->get('text_customer_approval'),
					'href'     => $this->url->link('customer/customer_approval', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
						
			if ($this->user->hasPermission('access', 'customer/custom_field')) {		
				$customer[] = array(
					'name'	   => $this->language->get('text_custom_field'),
					'href'     => $this->url->link('customer/custom_field', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
			
			if ($customer) {
				$data['menus'][] = array(
					'id'       => 'menu-customer',
					'icon'	   => 'fa-user', 
					'name'	   => $this->language->get('text_customer'),
					'href'     => '',
					'children' => $customer
				);	
			}
			
			// Marketing
			$marketing = array();
			
			if ($this->user->hasPermission('access', 'marketing/marketing')) {
				$marketing[] = array(
					'name'	   => $this->language->get('text_marketing'),
					'href'     => $this->url->link('marketing/marketing', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
			
			if ($this->user->hasPermission('access', 'marketing/coupon')) {	
				$marketing[] = array(
					'name'	   => $this->language->get('text_coupon'),
					'href'     => $this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
			
			if ($this->user->hasPermission('access', 'marketing/contact')) {
				$marketing[] = array(
					'name'	   => $this->language->get('text_contact'),
					'href'     => $this->url->link('marketing/contact', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($marketing) {
				$data['menus'][] = array(
					'id'       => 'menu-marketing',
					'icon'	   => 'fa-share-alt', 
					'name'	   => $this->language->get('text_marketing'),
					'href'     => '',
					'children' => $marketing
				);	
			}
			
			// System
			$system = array();
			
			if ($this->user->hasPermission('access', 'setting/setting')) {
				$system[] = array(
					'name'	   => $this->language->get('text_setting'),
					'href'     => $this->url->link('setting/store', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
		
			// Users
			$user = array();
			
			if ($this->user->hasPermission('access', 'user/user')) {
				$user[] = array(
					'name'	   => $this->language->get('text_users'),
					'href'     => $this->url->link('user/user', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
			
			if ($this->user->hasPermission('access', 'user/user_permission')) {	
				$user[] = array(
					'name'	   => $this->language->get('text_user_group'),
					'href'     => $this->url->link('user/user_permission', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
			
			if ($this->user->hasPermission('access', 'user/api')) {		
				$user[] = array(
					'name'	   => $this->language->get('text_api'),
					'href'     => $this->url->link('user/api', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
			
			if ($user) {
				$system[] = array(
					'name'	   => $this->language->get('text_users'),
					'href'     => '',
					'children' => $user		
				);
			}
			
			// Localisation
			$localisation = array();
			
			if ($this->user->hasPermission('access', 'localisation/location')) {
				$localisation[] = array(
					'name'	   => $this->language->get('text_location'),
					'href'     => $this->url->link('localisation/location', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
			
			if ($this->user->hasPermission('access', 'localisation/language')) {
				$localisation[] = array(
					'name'	   => $this->language->get('text_language'),
					'href'     => $this->url->link('localisation/language', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'localisation/currency')) {
				$localisation[] = array(
					'name'	   => $this->language->get('text_currency'),
					'href'     => $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'localisation/stock_status')) {
				$localisation[] = array(
					'name'	   => $this->language->get('text_stock_status'),
					'href'     => $this->url->link('localisation/stock_status', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'localisation/order_status')) {
				$localisation[] = array(
					'name'	   => $this->language->get('text_order_status'),
					'href'     => $this->url->link('localisation/order_status', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			// Returns
			$return = array();
			
			if ($this->user->hasPermission('access', 'localisation/return_status')) {
				$return[] = array(
					'name'	   => $this->language->get('text_return_status'),
					'href'     => $this->url->link('localisation/return_status', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'localisation/return_action')) {
				$return[] = array(
					'name'	   => $this->language->get('text_return_action'),
					'href'     => $this->url->link('localisation/return_action', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);		
			}
			
			if ($this->user->hasPermission('access', 'localisation/return_reason')) {
				$return[] = array(
					'name'	   => $this->language->get('text_return_reason'),
					'href'     => $this->url->link('localisation/return_reason', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($return) {	
				$localisation[] = array(
					'name'	   => $this->language->get('text_return'),
					'href'     => '',
					'children' => $return		
				);
			}
			
			if ($this->user->hasPermission('access', 'localisation/country')) {
				$localisation[] = array(
					'name'	   => $this->language->get('text_country'),
					'href'     => $this->url->link('localisation/country', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'localisation/zone')) {
				$localisation[] = array(
					'name'	   => $this->language->get('text_zone'),
					'href'     => $this->url->link('localisation/zone', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'localisation/geo_zone')) {
				$localisation[] = array(
					'name'	   => $this->language->get('text_geo_zone'),
					'href'     => $this->url->link('localisation/geo_zone', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}
			
			// Tax		
			$tax = array();
			
			if ($this->user->hasPermission('access', 'localisation/tax_class')) {
				$tax[] = array(
					'name'	   => $this->language->get('text_tax_class'),
					'href'     => $this->url->link('localisation/tax_class', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}
			
			if ($this->user->hasPermission('access', 'localisation/tax_rate')) {
				$tax[] = array(
					'name'	   => $this->language->get('text_tax_rate'),
					'href'     => $this->url->link('localisation/tax_rate', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}
			
			if ($tax) {	
				$localisation[] = array(
					'name'	   => $this->language->get('text_tax'),
					'href'     => '',
					'children' => $tax		
				);
			}
			
			if ($this->user->hasPermission('access', 'localisation/length_class')) {
				$localisation[] = array(
					'name'	   => $this->language->get('text_length_class'),
					'href'     => $this->url->link('localisation/length_class', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}
			
			if ($this->user->hasPermission('access', 'localisation/weight_class')) {
				$localisation[] = array(
					'name'	   => $this->language->get('text_weight_class'),
					'href'     => $this->url->link('localisation/weight_class', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}
			
			if ($localisation) {																
				$system[] = array(
					'name'	   => $this->language->get('text_localisation'),
					'href'     => '',
					'children' => $localisation	
				);
			}
			
			// Tools	
			$maintenance = array();
				
			if ($this->user->hasPermission('access', 'tool/backup')) {
				$maintenance[] = array(
					'name'	   => $this->language->get('text_backup'),
					'href'     => $this->url->link('tool/backup', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
					
			if ($this->user->hasPermission('access', 'tool/upload')) {
				$maintenance[] = array(
					'name'	   => $this->language->get('text_upload'),
					'href'     => $this->url->link('tool/upload', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);	
			}
						
			if ($this->user->hasPermission('access', 'tool/log')) {
				$maintenance[] = array(
					'name'	   => $this->language->get('text_log'),
					'href'     => $this->url->link('tool/log', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
		
			if ($maintenance) {
				$system[] = array(
					'id'       => 'menu-maintenance',
					'icon'	   => 'fa-cog', 
					'name'	   => $this->language->get('text_maintenance'),
					'href'     => '',
					'children' => $maintenance
				);
			}		
		
		
			if ($system) {
				$data['menus'][] = array(
					'id'       => 'menu-system',
					'icon'	   => 'fa-cog', 
					'name'	   => $this->language->get('text_system'),
					'href'     => '',
					'children' => $system
				);
			}
			
			$report = array();
							
			if ($this->user->hasPermission('access', 'report/report')) {
				$report[] = array(
					'name'	   => $this->language->get('text_reports'),
					'href'     => $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
					
			if ($this->user->hasPermission('access', 'report/online')) {
				$report[] = array(
					'name'	   => $this->language->get('text_online'),
					'href'     => $this->url->link('report/online', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
											
			if ($this->user->hasPermission('access', 'report/statistics')) {
				$report[] = array(
					'name'	   => $this->language->get('text_statistics'),
					'href'     => $this->url->link('report/statistics', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}	
			
			$data['menus'][] = array(
				'id'       => 'menu-report',
				'icon'	   => 'fa-bar-chart-o', 
				'name'	   => $this->language->get('text_reports'),
				'href'     => '',
				'children' => $report
			);	
			
			// Stats
			$this->load->model('sale/order');
	
			$order_total = $this->model_sale_order->getTotalOrders();
			
			$this->load->model('report/statistics');
			
			$complete_total = $this->model_report_statistics->getValue('order_complete');
			
			if ((float)$complete_total && $order_total) {
				$data['complete_status'] = round(($complete_total / $order_total) * 100);
			} else {
				$data['complete_status'] = 0;
			}

			$processing_total = $this->model_report_statistics->getValue('order_processing');
	
			if ((float)$processing_total && $order_total) {
				$data['processing_status'] = round(($processing_total / $order_total) * 100);
			} else {
				$data['processing_status'] = 0;
			}
	
			$other_total = $this->model_report_statistics->getValue('order_other');
	
			if ((float)$other_total && $order_total) {
				$data['other_status'] = round(($other_total / $order_total) * 100);
			} else {
				$data['other_status'] = 0;
			}
			
			return $this->load->view('common/column_left', $data);
		}
	}
}