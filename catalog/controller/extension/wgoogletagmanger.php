<?php
class ControllerExtensionWgoogletagmanger extends Controller {
	public function index() {
		$json=array();
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		if(isset($this->request->get['product_id'])){
			$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
			$json['name'] = $product_info['name'];
			$json['price'] = $this->currency->format($product_info['price'], $this->session->data['currency'], '', false);
			$getcategories = $this->model_catalog_product->getCategories($this->request->get['product_id']);
			$categories = '';
			if($getcategories){
				foreach($getcategories as $cate){
					$getcategory = $this->model_catalog_category->getCategory($cate['category_id']);
					$categories[] = $getcategory['name'];
				}
			}
			$categories = implode(", ", $categories);
			$json['categories'] = $categories;
			$json['manufacturer'] = $product_info['manufacturer'];
			$json['currencycode'] = $this->session->data['currency'];
		}
		print_r(json_encode($json));
	}
}