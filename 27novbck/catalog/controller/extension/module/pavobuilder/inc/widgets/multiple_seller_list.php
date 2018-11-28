<?php

class PA_Widget_Multiple_Seller_List extends PA_Widgets {



	public function fields() {
		$this->load->model('customerpartner/partner');  
    	$results = $this->model_customerpartner_partner->getCustomers();		
		$categories = array();	

		foreach ($results as $cat_id) {

			$categories[] = array(

				'value'	=> $cat_id['customer_id'],

				'label'	=> $cat_id['firstname'].' '.$cat_id['lastname']

			);

		} 

		return array( 

			'mask'		=> array(

				'icon'	=> 'fa fa-product-hunt',

				'label'	=> 'Featured Seller'

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

							'type'	=> 'select',

							'name'	=> 'layout',

							'label'	=> $this->language->get( 'entry_layout_text' ),

							'default' => 'product_list',

							'options'	=> $this->getLayoutsOptions(),

							'none' 	=> false

						),

						array(

							'type'		=> 'select',

							'name'		=> 'title',

							'label'		=> $this->language->get( 'entry_title' ),

							'default' 	=> 'true',

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

							'type'		=> 'text',

							'name'		=> 'title_product',

							'label'		=> $this->language->get( 'entry_title_text' ),

							'desc'		=> $this->language->get( 'entry_title_desc_text' ),

							'default'	  => '',

							'language'  => true

						),

						array(

							'type'		=> 'editor',

							'name'		=> 'subtitle_product',

							'label'		=> $this->language->get( 'entry_subtitle_text' ),

							'default'	  => '',

							'language'  => true

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

							'name'		=> 'category',

							'label'		=> 'Seller',

							'default' 	=> 'all',

							'options'	=> $categories,

							'multiple' => true

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

					)

				)

			)

		);

	}



	public function render( $settings = array(), $content = '' ) {



		$this->load->model( 'tool/image' );

		$this->load->model('extension/module/pavothemer');

		$this->load->model('catalog/product');

		$this->load->model('customerpartner/partner'); 
		
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');

		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');

		
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.min.js');



		$settings['subtitle_product'] = ! empty( $settings ) && ! empty( $settings['subtitle_product'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['subtitle_product'] ), ENT_QUOTES, 'UTF-8' ) : '';

		$settings['products'] = array ();

		$filter_data = array(

			'start' => 0,

            'limit' => isset($settings['limit']) ? $settings['limit'] : 10

        );
 

		$filter_categories = array( );
      

        $sellerList = array();

       foreach($settings['category'] as $seller_id){

       	$temp = array();

       	$partner =$this->model_customerpartner_partner->getPartnerCustomerInfo($seller_id);     
     
		     if ($partner['avatar'] && file_exists(DIR_IMAGE . $partner['avatar'])) {
					$partner['avatar'] = $this->model_tool_image->resize($partner['avatar'], 235, 236);
			 } else if($this->config->get('marketplace_default_image_name') && file_exists(DIR_IMAGE . $this->config->get('marketplace_default_image_name'))) {
					if($partner['avatar'] != 'removed') {
						$partner['avatar'] = $this->model_tool_image->resize($this->config->get('marketplace_default_image_name'), 235, 236);
					} else {
						$partner['avatar'] = '';
					}
				}
	  $partner['shortprofile'] = html_entity_decode($partner['shortprofile']);
     
      $shoplink = $this->url->link('customerpartner/profile', 'id=' . $partner['customer_id']);
     

     $address_data = array();
        if ($partner['address_id']) {
			  $this->load->model('account/address');
              
			  $address_data = $this->model_account_address->getAddress($partner['address_id']);

			  if (isset($address_data['iso_code_2']) && $address_data['iso_code_2']) {
			     $partner['country'] = $address_data['iso_code_2'];
			  }
			}

      $temp['seller'] = $partner;
      $temp['shoplink'] = $shoplink;
	  $temp['address'] =  $address_data;

      $temp['products'] =  $this->getSellerProducts($seller_id, $settings);
      $sellerList[] = $temp;
       }
 		
       $settings['sellerlists']= $sellerList;  

     
		$settings['product_grid_layout']= ''; 

		$file = DIR_APPLICATION . 'view/theme/'. $this->config->get('config_theme').'/template/product/layout/'. $this->config->get( 'pavothemer_product_grid_layout' ) .'.twig'; 

	  	if( file_exists( $file ) ){  

			$settings['product_grid_layout'] = $this->config->get('config_theme').'/template/product/layout/'. $this->config->get( 'pavothemer_product_grid_layout' ) .'.twig'; 

	  	}



	  	if (!empty($settings['layout'])) {

			$args = $this->renderLayout($settings['layout']);

		} else {

			$args = 'extension/module/pavobuilder/pa_multiple_seller_list/multiple_seller_list';

		} 

	 



     
      return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );

	}


  function getSellerProducts($seller_id,  $setting){ 
        $this->load->model('tool/image');
		$this->load->model('catalog/category');
		$this->load->model('account/customerpartner');
		$this->load->model('customerpartner/master');
		$this->load->language('customerpartner/collection');
		$this->load->language('product/category');		
		$partner = $this->model_customerpartner_master->getProfile($seller_id);
        $page = 1;
		$limit = 4;
		$url = '';

		$filter_data = array(
			'customer_id'		 => $seller_id,						
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit,
			'filter_store' 		 => $this->config->get('config_store_id'),
			'filter_status'		 => 1
		);		
	
		$this->load->model('catalog/product');

		$results = $this->model_account_customerpartner->getProductsSeller($filter_data);

		$data['products'] = array();



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
	return 	$data['products'];
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

        

        foreach ($results as $result) {

            if ( ! empty( $result['image'] ) ) {

				$setting['product_size'] = strtolower( $setting['product_size'] );

				$src = empty( $setting['product_size'] ) || $setting['product_size'] == 'full' ? $server. $result['image'] : false;

				if ( strpos( $setting['product_size'], 'x' ) ) {

					$src = $this->getImageLink($result['image'], $setting['product_size']);

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