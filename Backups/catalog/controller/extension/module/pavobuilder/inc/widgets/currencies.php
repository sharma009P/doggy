<?php
/**
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright	Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
 */

class PA_Widget_Currencies extends PA_Widgets {

	public $header = true;

	public function fields(){
		return array(
			'mask'		=> array(
				'icon'	=> 'fa fa-file-text',
				'label'	=> $this->language->get( 'entry_currency_block' ),
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
							'type'	=> 'text',
							'name'	=> 'extra_class',
							'label'	=> $this->language->get( 'entry_extra_class_text' ),
							'desc'	=> $this->language->get( 'entry_extra_class_desc_text' ),
							'mask'	=> true
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
				),
				
				'custom'			=> array(
					'label'			=> $this->language->get( 'entry_custom_text' ),
					'fields'		=> array(
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'text_link_color',
							'label'	=> $this->language->get( 'entry_text_link_text' ),
							'selector' => 'span',
							'css_attr'	=> 'color'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'icon_color',
							'label'	=> $this->language->get( 'entry_icon_color' ),
							'selector' => '.fa, strong',
							'css_attr'	=> 'color'
						),
						array(
							'type'	=> 'colorpicker',
							'name'	=> 'button_color',
							'label'	=> $this->language->get( 'entry_button_color' ),
							'selector' => 'button',
							'css_attr'	=> 'color'
						),
					)
				),
			)
		);
	}

	public function render( $settings = array(), $content = '' ) {
		

		$settings['currency'] = $this->load->controller('common/currency');

		return $this->load->view( 'extension/module/pavoheader/currencies', array( 'settings' => $settings ) );
	}

	/**
	 * s fields
	 */
	public function validate( $settings = array() ) {
		$language_id = $this->config->get('config_language_id');
		$this->load->model( 'localisation/language' );
		$language = $this->model_localisation_language->getLanguage( $language_id );
		$code = ! empty( $language['code'] ) ? $language['code'] : $this->config->get('config_language');

		if ( ! empty( $settings[$code] ) && ! empty( $settings[$code]['content'] ) ) {
			$settings[$code]['content'] = html_entity_decode( $settings[$code]['content'], ENT_QUOTES, 'UTF-8' );
		}
		return $settings;
	}

}