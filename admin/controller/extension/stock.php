<?php 

require_once DIR_SYSTEM . "library/stockutils.php";

class ControllerExtensionStock extends StockController {
	private $error = array();
	 
	public function index() {
		
		$this->language->load(StockModuleConstants::$STOCK_LANGUAGE_FILE);
		 
		$this->document->setTitle($this->language->get('sm_heading_title'));

		$this->load->model(StockModuleConstants::$STOCK_MODEL);

		$this->getList();
	}

	public function update() {
		
		if (! $this->config->get(StockModuleConfig::$ENABLED) ) {
			$this->response->redirect( $this->slink('error/not_found') );
			return;
		}
		$lang = $this->language->load(StockModuleConstants::$STOCK_LANGUAGE_FILE);
		$model = $this->loadStockModel();
		
		$this->document->setTitle($lang['sm_heading_title']);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$product_disabled = $model->editStock($this->request->get['product_id'], $this->request->post);

			if ($product_disabled) {
				$this->session->data['error_attention'] = $lang['sm_information_product_disabled'];
			} else {
				$this->session->data['success'] = $lang['sm_text_success'];
			}
			
			$url = $this->createFilterParams('');

			$this->response->redirect($this->slink(StockModuleConstants::$STOCK_MODULE_ROUTE, $url));
		} else {
			$this->getForm();
		}
	}

	public function combinations() {
		if (! $this->config->get(StockModuleConfig::$ENABLED)) {
			$this->response->redirect( $this->slink('error/not_found') );
		}
		
		$lang = $this->language->load(StockModuleConstants::$STOCK_LANGUAGE_FILE);
		$model = $this->loadStockModel();
		
		$data = array();
		
		$this->document->setTitle($lang['sm_heading_combinations_title']);
		
		// prepare javascript links
		$link_prefix = 'index.php?route=';
		$link_suffix = '&' . StockModuleConstants::$TOKEN . '=' . $this->session->data[StockModuleConstants::$TOKEN];
		$data['update_combinations_link'] = $link_prefix . StockModuleConstants::$STOCK_MODULE_UPDATE_COMBINATIONS_ROUTE . $link_suffix;
		$data['combinations_link'] = $link_prefix . StockModuleConstants::$STOCK_MODULE_COMBINATIONS_ROUTE . $link_suffix;
		$data['autocomplete_link'] = $link_prefix . StockModuleConstants::$STOCK_MODULE_AUTOCOMPLETE_ROUTE . $link_suffix;
		$data['autocomplete_options_link'] = $link_prefix . StockModuleConstants::$STOCK_MODULE_AUTOCOMPLETE_OPTIONS_ROUTE . $link_suffix;
		
		$url = $this->createFilterParams('');

		$data['breadcrumbs'] = $this->createBreadcrumb( array(
				'text_home' 									=> StockModuleConstants::$DASHBOARD_ROUTE, 
				'sm_heading_title' 							=> StockModuleConstants::$STOCK_MODULE_ROUTE, 
				'sm_heading_combinations_title' 	=> StockModuleConstants::$STOCK_MODULE_COMBINATIONS_ROUTE
		) );

		$filter_name = $this->get('filter_name', null);
		$filter_model = $this->get('filter_model', null);
		$filter_quantity = $this->get('filter_quantity', null);
		$filter_comb_quantity = $this->get('filter_comb_quantity', null);
		$filter_option = $this->get('filter_option', null);
		
		$page = $this->get('page', 1);	
		$sort = $this->get('sort', 'pd.name');
		$order = $this->get('order', 'ASC');
			
		$filter_data = array(
			'filter_name'	  				=> $filter_name,
			'filter_model'	  			=> $filter_model,
			'filter_quantity' 			=> $filter_quantity,
			'filter_comb_quantity' 	=> $filter_comb_quantity,
			'filter_option'	  			=> $filter_option,
			'sort'            			=> $sort,
			'order'           			=> $order,
			'start'           			=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'           			=> $this->config->get('config_limit_admin')
		);
		
		$total = $model->getTotalCombinations($filter_data);
		$data['products'] = $model->getCombinations($filter_data);
		
		// populate attention/error/success messages
		$this->populateMessages($data);
		
		$url = $this->createFilterParams('', TRUE, TRUE); // add page, add new sort order
		$data['sort_name'] = $this->slink(StockModuleConstants::$STOCK_MODULE_COMBINATIONS_ROUTE, '&sort=pd.name' . $url);
		$data['sort_model'] = $this->slink(StockModuleConstants::$STOCK_MODULE_COMBINATIONS_ROUTE, '&sort=p.model' . $url);
		$data['sort_quantity'] = $this->slink(StockModuleConstants::$STOCK_MODULE_COMBINATIONS_ROUTE, '&sort=p.quantity' . $url);
		$data['filter_name'] = $filter_name;
		$data['filter_model'] = $filter_model;
		$data['filter_quantity'] = $filter_quantity;
		$data['filter_comb_quantity'] = $filter_comb_quantity;
		$data['filter_option'] = $filter_option;
		$data['sort'] = $sort;
		$data['order'] = $order;
		
		// add pagination info to $data (data is used by reference)
		$this->createPagination($total, $page, StockModuleConstants::$STOCK_MODULE_COMBINATIONS_ROUTE, $data);

		// process header, left and footer controllers
		$data['header'] = $this->load->controller(StockModuleConstants::$COMMON_HEADER_ROUTE);
		$data['column_left'] = $this->load->controller(StockModuleConstants::$COMMON_COLUMN_LEFT_ROUTE);
		$data['footer'] = $this->load->controller(StockModuleConstants::$COMMON_FOOTER_ROUTE);

		$this->response->setOutput($this->load->view(StockModuleConstants::$STOCK_COMBINATIONS_TEMPLATE, $data));
	}

	// this function is meant to be called via ajax
	public function update_combinations() {
		$data = array();
		$lang = $this->language->load(StockModuleConstants::$STOCK_LANGUAGE_FILE);
		$model = $this->loadStockModel();
		
		$product_id = $this->request->post['product_id'];
		
		// check if user has permission to access this page
		if (!$this->user->hasPermission('modify', StockModuleConstants::$STOCK_MODULE_PERMISSION)) {
			$this->error['warning'] = $lang['sm_error_permission'];
		}
			
		// check that the quantity values are valid numbers
		if (!$this->error && isset($this->request->post['product_combinations'])) {
			$combinations = $this->request->post['product_combinations'];
			
			$row = 0;
			foreach ($combinations as $combination) {
				$quantity = $combination['quantity'];
				if (!is_numeric($quantity)) {
					$this->error['quantity'][$row] = true; 
				}	else if ($quantity < 0) {
					$this->error['quantity'][$row] = true; 
				}
				$row++;
			}
			if (isset($this->error['quantity'])) {
				$this->error['warning'] = $lang['sm_error_quantity'];
			}
		}
		if (!$this->error && !$model->isProductStockEnabled($product_id)) {
			$this->error['warning'] = $lang['sm_error_stale_data'];
		}
		
		if (isset($this->error['warning'])) {
			$data['error'] = $this->error['warning'];
			if (isset($this->error['quantity'])) {
				$data['error_quantity'] = array();
				foreach($this->error['quantity'] as $key => $value) {
					$data['error_quantity'][] = $key;
				}
			}
		} else {
			if (isset($this->request->post['product_combinations']) && isset($this->request->post['product_id']) && count($this->request->post['product_combinations']) > 0) { 
				$combinations = $this->request->post['product_combinations'];
				$total_quantity = $model->editStockCombinations($product_id, $combinations);
				
				if ($total_quantity == -1) {
					$data['error'] = $lang['sm_error_stale_data'];
				} else {
					$data['product_quantity'] = $total_quantity;
				}
			}
		}		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput( json_encode($data) );
	}

	protected function getForm() {

		$model = $this->getStockModel();
	
		$product_info = $model->getStockEnabledProduct($this->request->get['product_id']);
			
		$data['product_info'] = $product_info;
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['quantity'])) {
			$data['error_quantity'] = $this->error['quantity'];
		} else {
			$data['error_name'] = array();
		}

		$url = $this->createFilterParams('');

		$data['breadcrumbs'] = $this->createBreadcrumb( array(
				'text_home' 		=> StockModuleConstants::$DASHBOARD_ROUTE, 
				'sm_heading_title' => StockModuleConstants::$STOCK_MODULE_ROUTE
		) );
		$data['action'] = $this->slink(StockModuleConstants::$STOCK_MODULE_UPDATE_ROUTE, '&product_id=' . $this->request->get['product_id']);
		$data['cancel'] = $this->slink(StockModuleConstants::$STOCK_MODULE_ROUTE);

		$data[StockModuleConstants::$TOKEN] = $this->session->data[StockModuleConstants::$TOKEN];

		if (isset($this->request->post['product_option'])) {
			$product_options = $this->request->post['product_option'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_options = $model->getProductOptions($this->request->get['product_id']);
		} else {
			$product_options = array();
		}

		if (isset($this->request->post['product_combinations'])) {
			$product_combinations = $this->request->post['product_combinations'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_combinations = $model->getProductStockCombinations($this->request->get['product_id']);
		} else {
			$product_combinations = array();
		}
			
		$data['product_combinations'] = $product_combinations;
			
		$data['product_options'] = array();

		foreach ($product_options as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
				$product_option_value_data = array();
					
				foreach ($product_option['product_option_value'] as $product_option_value) {
					$product_option_value_data[] = array(
							'product_option_value_id' => $product_option_value['product_option_value_id'],
							'name'         						=> $product_option_value['name']
					);
				}
					
				$data['product_options'][] = array(
						'product_option_id'    => $product_option['product_option_id'],
						'product_option_value' => $product_option_value_data,
						'name'                 => $product_option['name']
				);
			}
		}
			
		// process header, left and footer controllers
		$data['header'] = $this->load->controller(StockModuleConstants::$COMMON_HEADER_ROUTE);
		$data['column_left'] = $this->load->controller(StockModuleConstants::$COMMON_COLUMN_LEFT_ROUTE);
		$data['footer'] = $this->load->controller(StockModuleConstants::$COMMON_FOOTER_ROUTE);

		$this->response->setOutput($this->load->view(StockModuleConstants::$STOCK_FORM_TEMPLATE, $data));			
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', StockModuleConstants::$STOCK_MODULE_PERMISSION)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
			
		// check that the quantity values are valid numbers
		if (!$this->error && isset($this->request->post['product_combinations'])) {
			$combinations = $this->request->post['product_combinations'];
			
			$row = 0;
			foreach ($combinations as $combination) {
				$quantity = $combination['quantity'];
				if (!is_numeric($quantity)) {
					$this->error['quantity'][$row] = true; 
				}	else if ($quantity < 0) {
					$this->error['quantity'][$row] = true; 
				}
				$row++;
			}
		}
		if (isset($this->error['quantity'])) {
			$this->error['warning'] = $this->language->get('sm_error_quantity');
		}
			
		// check that no combination is repeated. If such combinations exist, return the name of the first one
		if (!$this->error && isset($this->request->post['product_combinations'])) {
			$duplicates = $this->model_extension_stock_stock->getDuplicateCombinationNames($this->request->post['product_combinations'], TRUE, TRUE, "-");
			if (count($duplicates) > 0) {
				$this->error['warning'] = sprintf($this->language->get('sm_error_combination'), $duplicates[0]);
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
			
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	public function xl_report() {
		$this->report(TRUE);
	}
	
	public function report($xls = FALSE) {
		
		if (! $this->config->get(StockModuleConfig::$ENABLED)) {
			$this->response->redirect( $this->slink('error/not_found') );
		}

		$lang = $this->language->load(StockModuleConstants::$STOCK_LANGUAGE_FILE);
		$model = $this->loadStockModel();

		$limit = $this->config->get(StockModuleConfig::$REPORT_LIMIT);
		if (isset($this->request->post[StockModuleConfig::$REPORT_LIMIT])) {
			$stock_limit = $this->request->post[StockModuleConfig::$REPORT_LIMIT];
			if (is_numeric($stock_limit)) {
				$limit = $stock_limit;
			}
		} 
		if (!isset($limit)) {
			$limit = 10;
		}

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$data[StockModuleConfig::$REPORT_LIMIT] = $limit;
		$data['lang'] = $this->language->get('code');
		$data['sm_text_stock_available'] = sprintf($this->language->get('sm_text_stock_available'), $limit);
		$data['stock_module_report_limit'] = $limit;
		$data['stock_module_report_xl_link'] = $this->slink(StockModuleConstants::$STOCK_MODULE_XL_REPORT_ROUTE);
			
		$products = $model->getStockReport($limit);
		
		$data['products'] = $products;

		if ($xls) {
			$xl = new Excel_XML('UTF-8', true, 'Stock report');
			
			$xl_array = array();
			
			foreach ($data['products'] as $p) {
				$xl_array[] = array('combination' => $p['product_id'] . " - " . $p['name'] . " - " . $p['model'], 'sku' => $lang['sm_column_sku'], 'quantity' => $lang['sm_column_quantity']);
				$combinations = $p['combinations'];
				foreach ($combinations as $combination) {
					$combination_name = implode('-', $combination['option_value_names']); 
					//$xl_array[] = array($combination_name, $combination['quantity']);
					$xl_array[] = array(
						'combination' => $combination_name,
						'sku' 				=> $combination['sku'],
						'quantity'		=> $combination['quantity']
					);
				}
				//$xl_array[] = array("", "");
				$xl_array[] = array(
					'combination' => '',
					'sku' 				=> '',
					'quantity'		=> ''
				);
				
			}
			
			$xl->addArray($xl_array);
			$xl->generateXML(date('Y-m-d_H-i-s', time()) . '-stock-report-less-than-' . $limit);
		} else {
			$this->response->setOutput($this->load->view(StockModuleConstants::$STOCK_REPORT_TEMPLATE, $data));
		}
	} 

	protected function getList() {

		if (! $this->config->get(StockModuleConfig::$ENABLED)) {
			$this->response->redirect( $this->slink('error/not_found') );
		}

		$filter_name = $this->get('filter_name', null);
		$filter_model = $this->get('filter_model', null);
		$sort = $this->get('sort', 'pd.name');
		$order = $this->get('order', 'ASC');
		$page = $this->get('page', 1);
			
		$url = $this->createFilterParams('');

		$data['breadcrumbs'] = $this->createBreadcrumb( array(
				'text_home' 				=> StockModuleConstants::$DASHBOARD_ROUTE, 
				'sm_heading_title' 	=> StockModuleConstants::$STOCK_MODULE_ROUTE
		) );

		$data['stock_report'] = $this->slink(StockModuleConstants::$STOCK_MODULE_REPORT_ROUTE, $url);
		$data['stock_combinations'] = $this->slink(StockModuleConstants::$STOCK_MODULE_COMBINATIONS_ROUTE, $url);

		$data['products'] = array();
		$filter_data = array(
			'filter_name'	  => $filter_name,
			'filter_model'	=> $filter_model,
			'sort'          => $sort,
			'order'         => $order,
			'start'         => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'         => $this->config->get('config_limit_admin')
		);

		$this->load->model('tool/image');
			
		$model = $this->getStockModel();
			
		$product_total = $model->getTotalStockEnabledProducts($filter_data);
		$results = $model->getStockEnabledProducts($filter_data);
		
		$lang = $this->language->load(StockModuleConstants::$STOCK_LANGUAGE_FILE);

		foreach ($results as $result) {
			if ($result['image'] && file_exists(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.jpg', 40, 40);
			}
				
			$data['products'][] = array(
					'product_id' => $result['product_id'],
					'name'       => $result['name'],
					'model'      => $result['model'],
					'quantity'   => $result['quantity'],
					'status'     => ($result['status'] ? $lang['text_enabled'] : $lang['text_disabled']),
					'image'      => $image,
					'action'     => $this->slink(StockModuleConstants::$STOCK_MODULE_UPDATE_ROUTE, '&product_id=' . $result['product_id'] . $url)
			);
		}

		//$data[StockModuleConstants::$TOKEN] = $this->session->data[StockModuleConstants::$TOKEN];
		// prepare javascript autocomplete links
		$link_prefix = 'index.php?route=';
		$link_suffix = '&' . StockModuleConstants::$TOKEN . '=' . $this->session->data[StockModuleConstants::$TOKEN];
		$data['autocomplete_link'] = $link_prefix . StockModuleConstants::$STOCK_MODULE_AUTOCOMPLETE_ROUTE . $link_suffix;
		$data['stock_link'] = $link_prefix . StockModuleConstants::$STOCK_MODULE_ROUTE . $link_suffix;
			
		// set from request post or from config 
		$this->setFieldValue($data, StockModuleConfig::$REPORT_LIMIT, 5); 

		// populate attention/warning/error messages if any
		$this->populateMessages($data);

		$url = $this->createFilterParams('', TRUE, TRUE); // add page, add new sort order

		$data['sort_name'] = $this->slink(StockModuleConstants::$STOCK_MODULE_ROUTE, '&sort=pd.name' . $url);
		$data['sort_model'] = $this->slink(StockModuleConstants::$STOCK_MODULE_ROUTE, '&sort=p.model' . $url);
		$data['sort_quantity'] = $this->slink(StockModuleConstants::$STOCK_MODULE_ROUTE, '&sort=p.quantity' . $url);
			
		$this->createPagination($product_total, $page, StockModuleConstants::$STOCK_MODULE_ROUTE, $data);

		$data['filter_name'] = $filter_name;
		$data['filter_model'] = $filter_model;		
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller(StockModuleConstants::$COMMON_HEADER_ROUTE);
		$data['column_left'] = $this->load->controller(StockModuleConstants::$COMMON_COLUMN_LEFT_ROUTE);
		$data['footer'] = $this->load->controller(StockModuleConstants::$COMMON_FOOTER_ROUTE);

		$this->response->setOutput($this->load->view(StockModuleConstants::$STOCK_PRODUCTS_TEMPLATE, $data));
	}

	public function autocomplete_options() {
		$json = array();
			
		if (isset($this->request->get['filter_option']) ) {
			$model = $this->loadStockModel();

			$filter_option = $this->get('filter_option', '');
			$limit = $this->get('limit', 20);
				
			$data = array(
					'filter_option'  => $filter_option,
					'start'        => 0,
					'limit'        => $limit
			);

			$results = $model->getCombinationOptionValueNames($data);
			
			foreach ($results as $name) {
				$json[] = array(
						'name' => strip_tags(html_entity_decode($name, ENT_QUOTES, 'UTF-8')),
				);
			}
		}

		$this->response->setOutput(json_encode($json));
	}

	public function autocomplete() {
		$json = array();
			
		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model']) ) {
			$model = $this->loadStockModel();

			$filter_name = $this->get('filter_name', '');
			$filter_model = $this->get('filter_model', '');
			$limit = $this->get('limit', 20);
				
			$data = array(
					'filter_name'  => $filter_name,
					'filter_model' => $filter_model,
					'start'        => 0,
					'limit'        => $limit
			);

			$results = $model->getStockEnabledProducts($data);
			
			foreach ($results as $result) {

				$json[] = array(
						'product_id' => $result['product_id'],
						'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
						'model'      => $result['model'],
						'price'      => $result['price']
				);
			}
		}

		$this->response->setOutput(json_encode($json));
	}
}