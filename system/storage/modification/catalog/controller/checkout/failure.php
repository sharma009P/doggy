<?php
class ControllerCheckoutFailure extends Controller {
	public function index() {
		$this->load->language('checkout/failure');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_basket'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_checkout'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_failure'),
			'href' => $this->url->link('checkout/failure')
		);

		$data['text_message'] = sprintf($this->language->get('text_message'), $this->url->link('information/contact'));

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');


				$data['module_wgtm_status'] = $this->config->get('module_wgtm_status');
				$products = $this->cart->getProducts();
				$data['wcartpro'] = array();
				$this->load->model('catalog/product');
				$this->load->model('catalog/category');
				$data['wcurencysymbol'] = $this->session->data['currency'];
				foreach($products as $product){
					$product_info = $this->model_catalog_product->getProduct($product['product_id']);
					$wprice = $this->currency->format($product['price'], $this->session->data['currency'], '', false);
					$getcategories = $this->model_catalog_product->getCategories($product['product_id']);
					$categories = array();
					if($getcategories){
						foreach($getcategories as $cate){
							$getcategory = $this->model_catalog_category->getCategory($cate['category_id']);
							$categories[] = isset($getcategory['name']) ? $getcategory['name'] : '';
						}
					}
					$categories = implode(", ", $categories);
					$data['wcartpro'][] = array(
						'product_id'	=> $product['product_id'],
						'name'			=> $product['name'],
						'quantity'		=> $product['quantity'],
						'price'			=> $wprice,
						'categories'	=> $categories,
						'manufacturer'	=> $product_info['manufacturer'],
					);
				}
			
		$this->response->setOutput($this->load->view('common/success', $data));
	}
}