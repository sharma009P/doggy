<?php
class PA_Widget_Tabs_Productcarousel extends PA_Widgets {

	public function fields() {
		return array( 
			'mask'		=> array(
				'icon'	=> 'fa fa-product-hunt',
				'label'	=> $this->language->get( 'entry_tabs_productcarousel' )
			), 
			'tabs'	=> array(
				'general'		=> array(
					'label'		=> $this->language->get( 'entry_general_text' ),
					'fields'	=> array(
						array(
							'type'	=> 'hidden',
							'name'	=> 'uniqid_id',
							'label'	=> $this->language->get( 'entry_row_id_text' ),
							'desc'	=> $this->language->get( 'entry_column_desc_text' )
						),
						array(
                            'type'  => 'text',
                            'name'  => 'extra_class',
                            'label' => $this->language->get( 'entry_extra_class_text' ),
                            'default' => '',
                            'desc'  => $this->language->get( 'entry_extra_class_desc_text' )
                        ),
						array(
							'type'		  => 'text',
							'name'		  => 'product_size',
							'label'		  => $this->language->get( 'entry_product_size_text' ),
							'desc'		  => $this->language->get( 'entry_image_size_desc' ),
							'default'	  => 'full',
							'placeholder' => '200x400'
						),
						array(
							'type'		=> 'select',
							'name'		=> 'tabs',
							'label'		=> $this->language->get( 'entry_product_group' ),
							'default' 	=> 'best_seller',
							'options'	=> array(
								array(
									'value'	=> 'latest',
									'label'	=> 'Latest'
								),
								array(
									'value'	=> 'best_seller',
									'label'	=> 'Best Seller'
								),
								array(
									'value'	=> 'special',
									'label'	=> 'Special'
								),
								array(
									'value'	=> 'most_viewed',
									'label'	=> 'Most Viewed'
								),
								array(
									'value'	=> 'top_rating',
									'label'	=> 'Top Rating'
								)
							),
							'multiple'	=> true
						),
						array(
							'type'	  => 'number',
							'name'    => 'item',
							'label'	  => $this->language->get( 'entry_item_text' ),
							'desc'    => $this->language->get( 'entry_item_desc_text' ),
							'default' => 4
						),
						array(
							'type'		=> 'number',
							'name'		=> 'rows',
							'label'		=> $this->language->get( 'entry_rows_text' ),
							'default'	=> 1
						),
						array(
							'type'		=> 'number',
							'name'		=> 'limit',
							'label'		=> $this->language->get( 'entry_limit_text' ),
							'default'	=> 7
						),
						array(
							'type'		=> 'select',
							'name'		=> 'loop',
							'label'		=> $this->language->get( 'entry_loop' ),
							'desc'		=> $this->language->get( 'entry_loop_desc' ),
							'default' 	=> 'false',
							'options'	=> array(
								array(
									'value'	=> 'true',
									'label'	=> 'Enabled'
								),
								array(
									'value'	=> 'false',
									'label'	=> 'Disabled'
								)
							)
						),
						array(
							'type'		=> 'select',
							'name'		=> 'auto_play',
							'label'		=> $this->language->get( 'entry_auto_play' ),
							'default' 	=> 'false',
							'options'	=> array(
								array(
									'value'	=> 'true',
									'label'	=> 'Enabled'
								),
								array(
									'value'	=> 'false',
									'label'	=> 'Disabled'
								)
							)
						),
						array(
							'type'	  => 'number',
							'name'	  => 'auto_play_time',
							'label'	  => $this->language->get( 'entry_auto_play_time' ),
							'default' => 5000
						)
					)
				),
				'style'				=> array(
					'label'			=> $this->language->get( 'entry_styles_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'layout-onion',
							'name'	=> 'layout_onion',
							'label'	=> 'entry_box_text'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'color',
							'label'	=> 'entry_color_text'
						)
					)
				)
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		$this->load->language('extension/module/pavobuilder');
		$this->load->model( 'tool/image' );
		$this->load->model('extension/module/pavothemer');
		$this->load->model('catalog/product');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.min.js');

		$filter_data = array(
			'start' => 0,
            'limit' => isset($settings['limit']) ? $settings['limit'] : 10
        );
        $settings['producttabs'] = array (
        	'latest' 	 => array(
				'title' => $this->language->get( 'text_latest' ),
				'products' => array()
			),
			'bestseller' => array(
				'title' => $this->language->get( 'text_bestseller' ),
				'products' => array()
			),
			'special'    => array(
				'title' => $this->language->get( 'text_special' ),
				'products' => array()
			),
			'mostviewed' => array(
				'title' => $this->language->get( 'text_mostviewed' ),
				'products' => array()
			),
			'toprating' => array(
				'title' => $this->language->get( 'text_toprating' ),
				'products' => array()
			)
        );
		$filter_categories = array( );
 		foreach ($settings['tabs'] as $value) {
 			$rating 	= $this->model_extension_module_pavothemer->getTopRatingProducts( $filter_data['limit'], $filter_categories );
 			$latest 	= $this->model_extension_module_pavothemer->getLatestProducts( $filter_data['limit'], $filter_categories );
 			$bestseller = $this->model_extension_module_pavothemer->getBestSellerProducts( $filter_data['limit'], $filter_categories );
 			$special 	= $this->model_extension_module_pavothemer->getProductSpecials( $filter_data, $filter_categories );
 			$mostviewed = $this->model_extension_module_pavothemer->getMostviewedProducts( $filter_data['limit'], $filter_categories );

			switch (  $value ) {
				case "top_rating":
					$settings['producttabs']['toprating']['products'] 	= $this->getProducts( $rating , $settings );
					break; 
				case "latest": 
					$settings['producttabs']['latest']['products'] 		= $this->getProducts( $latest , $settings );
					break; 
				case "best_seller":
					$settings['producttabs']['bestseller']['products'] 	= $this->getProducts( $bestseller , $settings );
					break; 
				case "special":
					$settings['producttabs']['special']['products'] 	= $this->getProducts( $special , $settings );
					break; 
				case "most_viewed":
					$settings['producttabs']['mostviewed']['products'] 	= $this->getProducts( $mostviewed , $settings );
					break;  
				default: 
					$settings['producttabs']['latest']['products'] 		= $this->getProducts( $latest , $settings );
			}
		}	

		
		$settings['product_grid_layout'] = ''; 
		$file = DIR_APPLICATION . 'view/theme/'. $this->config->get('config_theme').'/template/product/layout/'. $this->config->get( 'pavothemer_product_grid_layout' ) .'.twig'; 
	  	if( file_exists( $file ) ) {  
			$settings['product_grid_layout'] = $this->config->get('config_theme').'/template/product/layout/'. $this->config->get( 'pavothemer_product_grid_layout' ) .'.twig'; 
	  	} 
	  	
		$args = 'extension/module/pavobuilder/pa_tabs_productcarousel/pa_tabs_productcarousel';
		return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
	}

	private function getProducts( $results, $setting ){
		$this->load->model( 'tool/image' );
		$this->load->model( 'catalog/product' );
        $products = array();
        $tooltip_width = isset($setting['tooltip_width'])?(int)$setting['tooltip_width']:200;
        $tooltip_height = isset($setting['tooltip_height'])?(int)$setting['tooltip_height']:200;

        if( defined("IMAGE_URL")){
            $server =  IMAGE_URL;
        } else  {
            $server = ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER).'image/';
        }

        if (empty($results)) {
        	$results = array ();
        }
        foreach ($results as $result) {
            if ( ! empty( $result['image'] ) ) {
				$setting['product_size'] = strtolower( $setting['product_size'] );
				$src = empty( $setting['product_size'] ) || $setting['product_size'] == 'full' ? $server . $result['image'] : false;
				if ( strpos( $setting['product_size'], 'x' ) ) {
					$sizes = explode( 'x', $setting['product_size'] );
					if ( ! empty( $sizes[0] ) && ! empty( $sizes[1] ) ) {
						$src = $this->model_tool_image->resize( $result['image'], $sizes[0], $sizes[1] );
					}
				}

				$result['image'] = $src ? $src : $result['image'];
			}

            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $price = false;
            }

            if ((float)$result['special']) {
                $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                $discount = floor((($result['price']-$result['special'])/$result['price'])*100);
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
            $images = $this->model_catalog_product->getProductImages( $result['product_id'] );
            $output = array();
            if( $images ){
                foreach( $images as $timage ){
                    if ($timage['image']) {
                    	$tmp = $this->getImageLink( $timage['image'], $setting['product_size'] );                  
                    } else {
                    	$tmp = $this->getImageLink( 'placeholder.png', $setting['product_size'] );                        
                    }   
                    $output[] = $tmp;
                }
            }

            $products[] = array(
            	'images'      => $output,
                'product_id' => $result['product_id'],
                'thumb'   	 => $result['image'],
                'name'    	 => $result['name'],
                'date_added' => $result['date_added'],
                'discount'   => isset($discount)?'-'.$discount.'%':'',
                'price'   	 => $price,
                'special' 	 => $special,
                'rating'     => $rating,
                'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                'reviews'    => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                'href'    	 => $this->url->link('product/product', 'product_id=' . $result['product_id']),
            );
        }
        return $products;
    }

}