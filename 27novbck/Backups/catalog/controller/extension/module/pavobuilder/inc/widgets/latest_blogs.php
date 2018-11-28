<?php
/******************************************************
 * @package Pavo Blog Popup Module for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright   Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
*******************************************************/
class PA_Widget_Latest_Blogs extends PA_Widgets {

    public function fields() {
        $this->load->language('extension/module/pavolatestblogs');
        return array(
            'mask'		=> array(
                'icon'	=> 'fa fa-pencil-square-o',
                'label'	=> $this->language->get( 'entry_latest_blogs' )
            ),
            'tabs'	=> array(
                'general'		=> array(
                    'label'		=> $this->language->get( 'entry_general_text' ),
                    'fields'	=> array(
                        array(
                            'type'  => 'hidden',
                            'name'  => 'uniqid_id',
                            'label' => $this->language->get( 'entry_row_id_text' ),
                            'desc'  => $this->language->get( 'entry_column_desc_text' )
                        ),
                        array(
                            'type'  => 'select',
                            'name'  => 'layout',
                            'label' => $this->language->get( 'entry_layout_text' ),
                            'default' => 'pa_latest_blogs',
                            'options'   => $this->getLayoutsOptions(),
                            'none'  => false
                        ),
                        array(
                            'type'  => 'text',
                            'name'  => 'extra_class',
                            'label' => $this->language->get( 'entry_extra_class_text' ),
                            'desc'  => $this->language->get( 'entry_extra_class_desc_text' ),
                        ),
                        array(
                            'type'	=> 'select',
                            'name'	=> 'title',
                            'label'	=> $this->language->get( 'entry_title' ),
                            'default' => 'enabled',
                            'options'	=> array(
                                array(
                                    'value'	=> 'enabled',
                                    'label'	=> 'Enabled'
                                ),
                                array(
                                    'value'	=> 'disabled',
                                    'label'	=> 'Disabled'
                                )
                            )
                        ),
                        array(
                            'type'	=> 'text',
                            'name'	=> 'title_blogs',
                            'label'	=> $this->language->get( 'entry_extra_blogs_title_text' ),
                            'desc'	=> $this->language->get( 'entry_extra_blogs_title_desc_text' ),
                            'language'  => true
                        ),
                        array(
                            'type'		=> 'editor',
                            'name'		=> 'description',
                            'label'		=> $this->language->get( 'entry_description_text' ),
                            'language'  => true,
                            'default'	=> ''
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
                            'type'	=> 'number',
                            'name'	=> 'view',
                            'label'	=> $this->language->get( 'entry_view_text' ),
                            'desc'  => $this->language->get( 'entry_view_desc_text' ),
                            'default' => 4
                        ),
                        array(
                            'type'	=> 'number',
                            'name'	=> 'limit',
                            'label'	=> $this->language->get( 'entry_limit_text' ),
                            'default' => 7
                        ),
                        array(
                            'type'	=> 'number',
                            'name'	=> 'rows',
                            'label'	=> $this->language->get( 'entry_rows_text' ),
                            'default' => 1
                        ),
                        array(
                            'type'	=> 'select',
                            'name'	=> 'loop',
                            'label'	=> $this->language->get( 'entry_loop' ),
                            'desc'	=> $this->language->get( 'entry_loop_desc' ),
                            'default' => 'true',
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
                            'type'	=> 'select',
                            'name'	=> 'auto_play',
                            'label'	=> $this->language->get( 'entry_auto_play' ),
                            'default' => 'enabled',
                            'options'	=> array(
                                array(
                                    'value'	=> 'enabled',
                                    'label'	=> 'Enabled'
                                ),
                                array(
                                    'value'	=> 'disabled',
                                    'label'	=> 'Disabled'
                                )
                            )
                        ),
                        array(
                            'type'	=> 'number',
                            'name'	=> 'interval',
                            'label'	=> $this->language->get( 'entry_interval' ),
                            'default' => 8000
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
        $date_format = array(
                $this->config->get( 'pavoblog_date_format' ),
                $this->config->get( 'pavoblog_time_format' ),
            );
        $date_format = implode( ' ', $date_format );

        $this->load->model( 'extension/pavoblog/post' );
        $this->load->model( 'extension/pavoblog/category' );
        $this->load->model( 'catalog/product' );
        $this->load->model( 'tool/image' );
        $this->load->language( 'extension/module/pavolatestblogs' );
        $this->document->addStyle( 'catalog/view/javascript/jquery/swiper/css/swiper.min.css' );
        $this->document->addStyle( 'catalog/view/javascript/jquery/swiper/css/opencart.css' );
        $this->document->addScript( 'catalog/view/javascript/jquery/swiper/js/swiper.min.js' );
        

        $settings['description'] = ! empty( $settings['description'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['description'] ), ENT_QUOTES, 'UTF-8' ) : '';
        $settings['posts'] = array();

        $filter_data = array(
            'date_added'	=> date( 'Y-m-d'),
            'date_modified'	=> date( 'Y-m-d'),
            'start'			=> 0,
            'orderby'       => 'date_added',
            'order'         => 'DESC',
            'limit'         => $settings['limit'] ? $settings['limit'] : 6
        );

        $posts = $this->model_extension_pavoblog_post->getLastestPosts( $filter_data );
        $server = $this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER;
        foreach( $posts as $post ) {
            if ( ! empty( $post['image'] ) ) {
                $settings['image_size'] = strtolower( $settings['image_size'] );
                $src = empty( $settings['image_size'] ) || $settings['image_size'] == 'full' ? $server . 'image/' . $post['image'] : false;
                if ( $src === false && strpos( $settings['image_size'], 'x' ) ) {
                    $src = $this->getImageLink($post['image'], $settings['image_size']);
                }

                $post['image'] = $src ? $src : $post['image'];
            }

            $settings['posts'][] = array(
                'name'          => html_entity_decode( $post['name'], ENT_QUOTES, 'UTF-8' ),
                'description'   => html_entity_decode( $post['description'], ENT_QUOTES, 'UTF-8' ),
                'content'       => html_entity_decode( $post['content'], ENT_QUOTES, 'UTF-8' ),
                'thumb'         => $post['image'],
                'link'          => $this->url->link( 'extension/pavoblog/single', 'pavo_post_id='.$post['post_id'] ),
                'date_added'    => isset( $post['date_added'] ) ? date( $date_format, strtotime( $post['date_added'] ) ) : '',
                'date_modified' => isset( $post['date_modified'] ) ? $post['date_modified']:'',
                'username'      => isset( $post['username'] ) ? $post['username'] : ''
            );

        }

        if (!empty($settings['layout'])) {
            $args = $this->renderLayout($settings['layout']);
        } else {
            $args = 'extension/module/pavobuilder/pa_latest_blogs/pa_latest_blogs';
        }
        
        return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
    }

}