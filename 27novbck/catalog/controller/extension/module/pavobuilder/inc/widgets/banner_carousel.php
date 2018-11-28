<?php
class PA_Widget_Banner_Carousel extends PA_Widgets {

	public function fields() {
		
		$this->load->model('design/banner');
		$get_banners = $this->model_design_banner->getBanners();
		$categories = array();
		foreach ($get_banners as $cat_id) {
			if ($cat_id['status'] == 1) {
				$categories[] = array(
					'value'	=> $cat_id['banner_id'],
					'label'	=> $cat_id['name']
				);
			}
		}
		return array( 
			'mask'		=> array(
				'icon'	=> 'fa fa-reply-all',
				'label'	=> $this->language->get( 'entry_banner_carousel' )
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
							'type'		=> 'select',
							'name'		=> 'banner',
							'label'		=> $this->language->get( 'entry_banner' ),
							'default' 	=> '',
							'options'	=> $categories,
						),
						array(
							'type'	=> 'text',
							'name'	=> 'image_size',
							'label'	=> $this->language->get( 'entry_image_size_text' ),
							'desc'	=> $this->language->get( 'entry_image_size_desc' ),
							'default'		=> 'full',
							'placeholder'	=> '200x400'
						),
						array(
							'type'	  => 'number',
							'name'    => 'item',
							'label'	  => $this->language->get( 'entry_item_text' ),
							'desc'    => $this->language->get( 'entry_item_desc_text' ),
							'default' => 4
						),
						array(
							'type'	  => 'number',
							'name'    => 'table',
							'label'	  => $this->language->get( 'entry_table_screens' ),
							'default' => 2
						),
						array(
							'type'	  => 'number',
							'name'    => 'mobile',
							'label'	  => $this->language->get( 'entry_mobile_screens' ),
							'default' => 1
						),
						array(
							'type'		=> 'number',
							'name'		=> 'rows',
							'label'		=> $this->language->get( 'entry_rows_text' ),
							'default'	=> 1
						),
						array(
							'type'		=> 'select',
							'name'		=> 'pagination',
							'label'		=> $this->language->get( 'entry_pagination_text' ),
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

		$this->load->model('design/banner');
		$this->load->model('tool/image');
		
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.min.js');

		if (!empty($settings['banner'])) {
			$settings['get_banners'] = array();
			if( defined("IMAGE_URL")){
	            $server =  IMAGE_URL;
	        } else  {
	            $server = ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER).'image/';
	        }
			$results = $this->model_design_banner->getBanner($settings['banner']);
			foreach ($results as $result) {
				if ( ! empty( $result['image'] ) ) {
					$settings['image_size'] = strtolower( $settings['image_size'] );
						$srcs = empty( $settings['image_size'] ) || $settings['image_size'] == 'full' ? $server . $result['image'] : false;
						if (strpos( $settings['image_size'], 'x' ) ) {
							$srcs = $this->getImageLink($result['image'], $settings['image_size']);
						}
					$result['image'] = $srcs ? $srcs : $result['image'];
				}

				$settings['get_banners'][] = array(
					'title' => isset($result['title']) ? $result['title'] : '',
					'link'  => isset($result['link']) ? $result['link'] : '',
					'image' => isset($result['image']) ? $result['image'] : '',
				);
			} 
		}
		$args = 'extension/module/pavobuilder/pa_banner_carousel/pa_banner_carousel';
		return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
	}

}