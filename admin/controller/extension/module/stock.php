<?php

require_once DIR_SYSTEM . "library/stockutils.php";

class ControllerExtensionModuleStock extends StockController {
	private $error = array(); 
	
	public function index() {   
	
		$lang = $this->language->load(StockModuleConstants::$SETTINGS_LANGUAGE_FILE);

		$this->document->setTitle($lang['heading_title']);
		$this->document->addScript(StockModuleConstants::$JS_COLOR_SCRIPT);
		
		$this->load->model(StockModuleConstants::$OC_SETTINGS_MODEL);
				
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting(StockModuleConstants::$MODULE_CODE, $this->request->post);		
					
			$css = DIR_CATALOG . StockModuleConstants::$CSS_FILE; 
			$this->writeCssFile($css, $this->request->post);
			$this->session->data['success'] = $lang['text_success'];
			$this->response->redirect($this->slink(StockModuleConstants::$MODULES_ROUTE));
		}
				
		$data = array();
	
		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		
		$data['breadcrumbs'] = $this->createBreadcrumb( array(
				'text_home' 		=> StockModuleConstants::$DASHBOARD_ROUTE, 
				'text_module' 	=> StockModuleConstants::$MODULES_ROUTE, 
				'heading_title' => StockModuleConstants::$STOCK_MODULE_SETTINGS_ROUTE
		) );
		$data['action'] = $this->slink(StockModuleConstants::$STOCK_MODULE_SETTINGS_ROUTE);
		$data['cancel'] = $this->slink(StockModuleConstants::$MODULES_ROUTE);

		$this->setFieldValue($data, StockModuleConfig::$ENABLED, 0);
		$this->setFieldValue($data, StockModuleConfig::$REPORT_LIMIT, 5);
		$this->setFieldValue($data, StockModuleConfig::$SHOW_CART_QUANTITIES, 0);
		$this->setFieldValue($data, StockModuleConfig::$CHECK_CART_QUANTITIES, 0);

		$this->setFieldValue($data, StockModuleConfig::$CHANGE_PRODUCT_CSS, 0);
		$this->setFieldValue($data, StockModuleConfig::$PRODUCT_COLOR_IN_STOCK, '339965');
		$this->setFieldValue($data, StockModuleConfig::$PRODUCT_COLOR_OUT_OF_STOCK, 'FF0000');

		$this->setFieldValue($data, StockModuleConfig::$UPDATE_STOCK_DISPLAY, 0);
		$this->setFieldValue($data, StockModuleConfig::$UPDATE_STOCK_DISPLAY_BEHAVIOR, 0);
		$this->setFieldValue($data, StockModuleConfig::$COMBINATION_COLOR_IN_STOCK, '00DD00');
		$this->setFieldValue($data, StockModuleConfig::$COMBINATION_COLOR_OUT_OF_STOCK, 'FF0000');
		$this->setFieldValue($data, StockModuleConfig::$REPLACE_EXPRESSION, htmlentities('//li[contains(., "%text_stock%")]'));
		$this->setFieldValue($data, StockModuleConfig::$DECORATE_EXPRESSION, htmlentities('<li>%text_stock% <div id="stock" style="display:inline;">%stock%</div></li>'));
		$this->setFieldValue($data, StockModuleConfig::$REMOVE_EXPRESSION, '');
		$this->setFieldValue($data, StockModuleConfig::$STOCK_EXPRESSION, htmlentities('%stock% (%stock_value%)'));
		
		$data['header'] = $this->load->controller(StockModuleConstants::$COMMON_HEADER_ROUTE);
		$data['column_left'] = $this->load->controller(StockModuleConstants::$COMMON_COLUMN_LEFT_ROUTE);
		$data['footer'] = $this->load->controller(StockModuleConstants::$COMMON_FOOTER_ROUTE);

		$this->response->setOutput($this->load->view(StockModuleConstants::$SETTINGS_TEMPLATE, $data));
	}
	
	public function install() {
		$query = "ALTER TABLE `" . DB_PREFIX . "product_option` ADD COLUMN `stock_enabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `required`;";
		$this->db->query($query);

		$query = "ALTER TABLE `" . DB_PREFIX . "product` ADD COLUMN `allow_preorder` tinyint(1) NOT NULL DEFAULT 0 AFTER `stock_status_id`;";
		$this->db->query($query);
		
		$query = "CREATE TABLE `" . DB_PREFIX . "stock` ( " .
  					 "	`combination_id` int(11) NOT NULL AUTO_INCREMENT, " .
  					 "	`product_id` int(11) NOT NULL, " .
  					 " 	`quantity` int(4) DEFAULT NULL, " .
  					 " 	`sku` varchar(64) DEFAULT NULL, " .
  					 "	PRIMARY KEY (`combination_id`) " .
						 ") ENGINE=MyISAM;";
		$this->db->query($query);

		$query = "CREATE TABLE `" . DB_PREFIX . "stock_option` ( " .
  					 "	`combination_id` int(11) NOT NULL, " .
  					 "	`product_option_value_id` int(11) NOT NULL, " .
  					 "	PRIMARY KEY (`combination_id`, `product_option_value_id`) " .
						 ") ENGINE=MyISAM;";
		$this->db->query($query);
	}
	
	
	public function uninstall() {
		$query = "ALTER TABLE `" . DB_PREFIX . "product` DROP COLUMN `allow_preorder`;";
		$this->db->query($query);
		
		$query = "ALTER TABLE `" . DB_PREFIX . "product_option` DROP COLUMN `stock_enabled`;";
		$this->db->query($query);
		
		$query = "DROP TABLE IF EXISTS `" . DB_PREFIX . "stock_option`;";
		$this->db->query($query);

		$query = "DROP TABLE IF EXISTS `" . DB_PREFIX . "stock`;";
		$this->db->query($query);
	}
	
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', StockModuleConstants::$STOCK_MODULE_SETTINGS_PERMISSION)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!isset($this->request->post[StockModuleConfig::$REPORT_LIMIT]) || empty($this->request->post[StockModuleConfig::$REPORT_LIMIT])) {
			$this->error['warning'] = $this->language->get('error_limit_required');
		} else if ( !is_numeric( $this->request->post[StockModuleConfig::$REPORT_LIMIT]) || $this->request->post[StockModuleConfig::$REPORT_LIMIT] <= 0 ) {
			$this->error['warning'] = $this->language->get('error_limit_positive');
		}
		return !$this->error;
	}
}