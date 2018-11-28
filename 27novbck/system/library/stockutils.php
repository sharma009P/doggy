<?php

class StockModuleConstants {
	
	public static $MODULE_CODE = 'module_stock';
	
	public static $DASHBOARD_ROUTE = 'common/dashboard';
	public static $STOCK_MODULE_SETTINGS_ROUTE = 'extension/module/stock';
	public static $STOCK_MODULE_ROUTE = 'extension/stock';
	public static $STOCK_MODULE_COMBINATIONS_ROUTE = 'extension/stock/combinations';
	public static $STOCK_MODULE_UPDATE_COMBINATIONS_ROUTE = 'extension/stock/update_combinations';
	public static $STOCK_MODULE_AUTOCOMPLETE_ROUTE = 'extension/stock/autocomplete';
	public static $STOCK_MODULE_AUTOCOMPLETE_OPTIONS_ROUTE = 'extension/stock/autocomplete_options';
	public static $STOCK_MODULE_REPORT_ROUTE = 'extension/stock/report';
	public static $STOCK_MODULE_XL_REPORT_ROUTE = 'extension/stock/xl_report';
	public static $STOCK_MODULE_UPDATE_ROUTE = 'extension/stock/update';
	public static $MODULES_ROUTE = 'marketplace/extension&type=module';
	
	public static $COMMON_HEADER_ROUTE = 'common/header';
	public static $COMMON_COLUMN_LEFT_ROUTE = 'common/column_left';
	public static $COMMON_FOOTER_ROUTE = 'common/footer';
	
	public static $SETTINGS_TEMPLATE = 'extension/module/stock';
	public static $STOCK_COMBINATIONS_TEMPLATE = 'extension/stock/stock_list_combinations';
	public static $STOCK_PRODUCTS_TEMPLATE = 'extension/stock/stock_list';
	public static $STOCK_FORM_TEMPLATE = 'extension/stock/stock_form';
	public static $STOCK_REPORT_TEMPLATE = 'extension/stock/stock_report';
	
	public static $SETTINGS_LANGUAGE_FILE = 'extension/module/stock';
	public static $STOCK_LANGUAGE_FILE = 'extension/stock/stock';
	
	public static $TOKEN = 'user_token';
	
	public static $STOCK_MODULE_SETTINGS_PERMISSION = 'extension/module/stock';
	public static $STOCK_MODULE_PERMISSION = 'extension/stock';
	
	public static $JS_COLOR_SCRIPT = 'view/javascript/jscolor/jscolor.js';
	public static $CSS_FILE =  "view/theme/default/stylesheet/stock-module.css";
	
	public static $OC_SETTINGS_MODEL = 'setting/setting';
	public static $STOCK_MODEL = 'extension/stock/stock';
}

class StockModuleConfig {
	public static $ENABLED = 'module_stock_status';
	public static $REPORT_LIMIT = 'module_stock_report_limit';
	public static $SHOW_CART_QUANTITIES = 'module_stock_show_cart_quantities';
	public static $CHECK_CART_QUANTITIES = 'module_stock_check_cart_quantities';
	public static $CHANGE_PRODUCT_CSS = 'module_stock_change_product_css';
	public static $PRODUCT_COLOR_IN_STOCK = 'module_stock_product_color_instock';
	public static $PRODUCT_COLOR_OUT_OF_STOCK = 'module_stock_product_color_outofstock';
	public static $UPDATE_STOCK_DISPLAY = 'module_stock_update_stock_display';
	public static $UPDATE_STOCK_DISPLAY_BEHAVIOR = 'module_stock_update_stock_display_behaviour';
	public static $COMBINATION_COLOR_IN_STOCK = 'module_stock_combination_color_instock';
	public static $COMBINATION_COLOR_OUT_OF_STOCK = 'module_stock_combination_color_outofstock';
	public static $REPLACE_EXPRESSION = 'module_stock_replace_expression';
	public static $DECORATE_EXPRESSION = 'module_stock_decorate_expression';
	public static $REMOVE_EXPRESSION = 'module_stock_remove_expression';
	public static $STOCK_EXPRESSION = 'module_stock_stock_expression';
}


class StockController extends Controller {
	
	protected function setFieldValue(&$data, $field_name, $default_value = 0, $new_field_name = null) {
		$data_field_name = !is_null($new_field_name) ? $new_field_name : $field_name;
		if (isset($this->request->post[$field_name])) {
			$data[$data_field_name] = $this->request->post[$field_name];
		} elseif (!is_null($this->config->get($field_name))) { 
			$data[$data_field_name] = $this->config->get($field_name);
		}	else {
			$data[$data_field_name] = $default_value;
		}
	}

		protected function get($field, $default = '') {
		$result = $default;
		if (isset($this->request->get[$field])) {
			$result = $this->request->get[$field];
		}
		return $result;
	}
	
	protected function slink($url, $args = '', $token = true, $secure = true) {
		$arg = '';
		if ( $token ) {
			$arg = StockUtils::tokenParam($this->session);
			
			if (!empty($args)) {
				if (is_array($args)) {
					$args[] = $arg;
				} else {
					$args .= '&' . $arg;
				}
			} else {
				$args = $arg;
			}
		}
		return $this->url->link($url, $args, $secure);
	}
	
	protected function createBreadcrumb($texts = array()) {
		$breadcrumb = array();
		
		foreach($texts as $text => $link) {
			$breadcrumb[] = array('text' => $this->language->get($text), 'href' => $this->slink($link) );
		}
		return $breadcrumb;
	}
	
	protected function writeCssFile($cssFile, $adata) {
		$change_product_color = isset($adata[StockModuleConfig::$CHANGE_PRODUCT_CSS]) && $adata[StockModuleConfig::$CHANGE_PRODUCT_CSS];
		$change_combination_color = isset($adata[StockModuleConfig::$UPDATE_STOCK_DISPLAY]) && $adata[StockModuleConfig::$UPDATE_STOCK_DISPLAY];
		if ($change_product_color || $change_combination_color) {
			$css = "" ;
			if (isset($adata[StockModuleConfig::$PRODUCT_COLOR_IN_STOCK])) {
				$css .= ".sm-product-instock { color: #" . $adata[StockModuleConfig::$PRODUCT_COLOR_IN_STOCK] . "; }\n";
			}
			if (isset($adata[StockModuleConfig::$PRODUCT_COLOR_OUT_OF_STOCK])) {
				$css .= ".sm-product-outofstock { color: #" . $adata[StockModuleConfig::$PRODUCT_COLOR_OUT_OF_STOCK] . "; }\n";
			}
			if (isset($adata[StockModuleConfig::$COMBINATION_COLOR_IN_STOCK])) {
				$css .= ".sm-comb-instock { color: #" . $adata[StockModuleConfig::$COMBINATION_COLOR_IN_STOCK] . "; }\n";
			}
			if (isset($adata[StockModuleConfig::$COMBINATION_COLOR_OUT_OF_STOCK])) {
				$css .= ".sm-comb-outofstock { color: #" . $adata[StockModuleConfig::$COMBINATION_COLOR_OUT_OF_STOCK] . "; }\n" ;
			}
			$handle = fopen($cssFile, "w");
			fwrite($handle, $css);
			fclose($handle);
		}
	}

	protected function populateMessages(&$data) {
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
				
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->session->data['error_attention'])) {
			$data['error_attention'] = $this->session->data['error_attention'];
				
			unset($this->session->data['error_attention']);
		} else {
			$data['error_attention'] = '';
		}
	}
		
	// Helper function to create the current url that includes filter related fields, and sort/page fields
	protected function createFilterParams($url = '', $include_page = TRUE, $adjust_sort_order = FALSE) {
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
			
		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . urlencode(html_entity_decode($this->request->get['filter_quantity'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_option'])) {
			$url .= '&filter_option=' . urlencode(html_entity_decode($this->request->get['filter_option'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_comb_quantity'])) {
			$url .= '&filter_comb_quantity=' . urlencode(html_entity_decode($this->request->get['filter_comb_quantity'], ENT_QUOTES, 'UTF-8'));
		}

		if ($adjust_sort_order) {
			if (isset($this->request->get['order'])) {
				$order = $this->request->get['order'];
			} else {
				$order = 'ASC';
			}
			if ($order == 'ASC') {
				$url .= '&order=DESC';
			} else {
				$url .= '&order=ASC';
			}
		} else {
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
		}

    if ($include_page) {
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
		}
		return $url;		
	}
	
	protected function createPagination($total, $page, $link, &$data) { // data is used by reference
		$url = $this->createFilterParams('', FALSE);
		$pagination = new Pagination();
		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->slink($link, $url . '&page={page}');
		
		$rpp = $this->config->get('config_limit_admin');
		
		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($total) ? (($page - 1) * $rpp) + 1 : 0, ((($page - 1) * $rpp) > ($total - $rpp)) ? $total : ((($page - 1) * $rpp) + $rpp), $total, ceil($total / $rpp));
	}
	
	protected function loadStockModel() {
		return StockUtils::loadModel($this, StockModuleConstants::$STOCK_MODEL);
	}
	
	protected function getStockModel() {
		$model_name = StockUtils::getModelName(StockModuleConstants::$STOCK_MODEL);
		return $this->{$model_name};
	}
	
	

}

class StockUtils {
	
	public static function loadModel($controller, $route) {
		$controller->load->model($route);
		$model_name = self::getModelName($route);
		return $controller->{$model_name};
	}
	
	public static function getModelName($route) {
		$model_name = 'model_' . str_replace('/', '_', (string)$route);
		return $model_name;
	}
	
	public static function tokenParam($session) {
		return StockModuleConstants::$TOKEN . '=' . $session->data[StockModuleConstants::$TOKEN];
	}
	
	public static function isStockModuleEnabled($config) {
		return $config->get(StockModuleConfig::$ENABLED) == 1;
	}
	
	/*
   * error values:
   *
   * 1: all stock-enabled should be also required
   * 2: all option values should be set to subtract
   */
  public static function getOptionsInfo($product_options) {
      $result = array();
      $result['error'] = 0;
      $stock_enabled_options = 0;
      foreach ($product_options as $product_option) {
          // check if there are any option values. If not, options are not stored thus no point in including them in the validation check
          if (isset($product_option['product_option_value']) && count($product_option['product_option_value']) > 0 ) {
              if (isset($product_option['stock_enabled']) && ($product_option['stock_enabled'] == 1) ) {
                  $stock_enabled_options ++;
                  if ($product_option['required'] == 0) {
                      // error: all stock-enbaled options should be required
                      $result['error'] = 1;
                      break;
                  }
                  
                  // check that all option values are set to 'subtract'
                  foreach ($product_option['product_option_value'] as $option_value) {
                      if ( $option_value['subtract'] == 0 ) {
                          $result['error'] = 2;
                          break;
                      }
                  }
              }
          }
      }   
      $result['stock_enabled_options'] = $stock_enabled_options;
      //if (($result['error'] == 0) && ($stock_enabled_options == 1)) {
      //    $result['error'] = 3;
      //}
      //$this->log->write('Options info: ' . var_export($result, true));
      return $result;      
  }
	
	public static function getInvalidStockMessage($language, $error_status, $link) {
    
    $reason = $language->get('sm_invalid_stock_reason_unknown');
    switch($error_status) {
      case 1: 
        $reason = $language->get('sm_invalid_stock_reason_no_entries');
        break;
      case 2: 
        $reason = $language->get('sm_invalid_stock_reason_incomplete_entries');
        break;
      case 3: 
        $reason = $language->get('sm_invalid_stock_reason_duplicate_entries');
        break;
    }
    $error = sprintf($language->get('sm_attention_invalid_stock'), $link, $reason);
    return $error;
  }
	
	public static function getCartQuantity($db, $sessionId, $customerId, $product_id, $option = array(), $recurring_id = 0) {
		$cart_product_quantity = 0;
		$query = $db->query("SELECT quantity FROM " . DB_PREFIX . "cart WHERE customer_id = '" . (int)$customerId . "' AND session_id = '" . $db->escape($sessionId) . "' AND product_id = '" . (int)$product_id . "' AND recurring_id = '" . (int)$recurring_id . "' AND `option` = '" . $db->escape(json_encode($option)) . "'");	
    if (isset($query->row['quantity'])) {
    	$cart_product_quantity = (int) $query->row['quantity'];
    }		
		return $cart_product_quantity;
	}
	
	public static function getCombinationAvailableStock($db, $product_id, $selected_options) {
    //$this->log->write('Checking stock combination for product id: ' . $product_id . ' options: ' . var_export($selected_options, true) );
  
		//return TRUE;
		$query = $db->query("SELECT s.combination_id, s.quantity, so.product_option_value_id "
				. " FROM `" . DB_PREFIX . "stock` s "
				. " INNER JOIN `" . DB_PREFIX . "stock_option` so ON (s.combination_id = so.combination_id) " 
				. " WHERE s.product_id = '" . (int)$product_id . "' ORDER BY s.combination_id, so.product_option_value_id ");

		$found = 0; // FALSE;

		$combinations = array();
		$combination_id = 0;
		foreach($query->rows as $row) {
			if ($row['combination_id'] != $combination_id) {
				$combination_id = $row['combination_id'];
				$combinations[$combination_id] = array();
				$combinations[$combination_id]['combination_id'] = $combination_id;
				$combinations[$combination_id]['quantity'] = $row['quantity'];
				$combinations[$combination_id]['option_values'] = array();
			}
			$combinations[$combination_id]['option_values'][] = $row['product_option_value_id'];
		}
		
		$selected_options_length = count($selected_options);
		
		foreach($combinations as $key => $combination) {
			$option_values = $combination['option_values'];
      //$this->log->write('Checking combination option values: ' . var_export($option_values, true));
			if (count($option_values) == $selected_options_length) {
				$combination_found = TRUE;
				foreach($selected_options as $selected_option) {
          			//$this->log->write('Checking selected option: ' . $selected_option . ' if exists in combination...');
					if (!in_array($selected_option, $option_values)) {
            			//$this->log->write('Option not found');
						$combination_found = FALSE;
						break;	
					} 
				}
				if ($combination_found) {
					$found = $combination['quantity'];
					break;
				}
			}
		}
    //$this->log->write('Stock available ? ' . $found);
		return $found;
	}				
}

/**
 * Simple excel generating from PHP5
 *
 * @package Utilities
 * @license http://www.opensource.org/licenses/mit-license.php
 * @author Oliver Schwarz <oliver.schwarz@gmail.com>
 * @version 1.0
 */

/**
 * Generating excel documents on-the-fly from PHP5
 * 
 * Uses the excel XML-specification to generate a native
 * XML document, readable/processable by excel.
 * 
 * @package Utilities
 * @subpackage Excel
 * @author Oliver Schwarz <oliver.schwarz@vaicon.de>
 * @version 1.1
 * 
 * @todo Issue #4: Internet Explorer 7 does not work well with the given header
 * @todo Add option to give out first line as header (bold text)
 * @todo Add option to give out last line as footer (bold text)
 * @todo Add option to write to file
 */
class Excel_XML
{

	/**
	 * Header (of document)
	 * @var string
	 */
        private $header = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">";

        /**
         * Footer (of document)
         * @var string
         */
        private $footer = "</Workbook>";

        /**
         * Lines to output in the excel document
         * @var array
         */
        private $lines = array();

        /**
         * Used encoding
         * @var string
         */
        private $sEncoding;
        
        /**
         * Convert variable types
         * @var boolean
         */
        private $bConvertTypes;
        
        /**
         * Worksheet title
         * @var string
         */
        private $sWorksheetTitle;

        /**
         * Constructor
         * 
         * The constructor allows the setting of some additional
         * parameters so that the library may be configured to
         * one's needs.
         * 
         * On converting types:
         * When set to true, the library tries to identify the type of
         * the variable value and set the field specification for Excel
         * accordingly. Be careful with article numbers or postcodes
         * starting with a '0' (zero)!
         * 
         * @param string $sEncoding Encoding to be used (defaults to UTF-8)
         * @param boolean $bConvertTypes Convert variables to field specification
         * @param string $sWorksheetTitle Title for the worksheet
         */
        public function __construct($sEncoding = 'UTF-8', $bConvertTypes = false, $sWorksheetTitle = 'Table1')
        {
                $this->bConvertTypes = $bConvertTypes;
        	$this->setEncoding($sEncoding);
        	$this->setWorksheetTitle($sWorksheetTitle);
        }
        
        /**
         * Set encoding
         * @param string Encoding type to set
         */
        public function setEncoding($sEncoding)
        {
        	$this->sEncoding = $sEncoding;
        }

        /**
         * Set worksheet title
         * 
         * Strips out not allowed characters and trims the
         * title to a maximum length of 31.
         * 
         * @param string $title Title for worksheet
         */
        public function setWorksheetTitle ($title)
        {
                $title = preg_replace ("/[\\\|:|\/|\?|\*|\[|\]]/", "", $title);
                $title = substr ($title, 0, 31);
                $this->sWorksheetTitle = $title;
        }

        /**
         * Add row
         * 
         * Adds a single row to the document. If set to true, self::bConvertTypes
         * checks the type of variable and returns the specific field settings
         * for the cell.
         * 
         * @param array $array One-dimensional array with row content
         */
        private function addRow ($array)
        {
        	$cells = "";
                foreach ($array as $k => $v):
                        $type = 'String';
                        if ($this->bConvertTypes === true && is_numeric($v)):
                                $type = 'Number';
                        endif;
                        $v = htmlentities($v, ENT_COMPAT, $this->sEncoding);
                        $cells .= "<Cell><Data ss:Type=\"$type\">" . $v . "</Data></Cell>\n"; 
                endforeach;
                $this->lines[] = "<Row>\n" . $cells . "</Row>\n";
        }

        /**
         * Add an array to the document
         * @param array 2-dimensional array
         */
        public function addArray ($array)
        {
                foreach ($array as $k => $v)
                        $this->addRow ($v);
        }


        /**
         * Generate the excel file
         * @param string $filename Name of excel file to generate (...xls)
         */
        public function generateXML ($filename = 'excel-export')
        {
                // correct/validate filename
                $filename = preg_replace('/[^aA-zZ0-9\_\-]/', '', $filename);
    	
                // deliver header (as recommended in php manual)
                header("Content-Type: application/vnd.ms-excel; charset=" . $this->sEncoding);
                header("Content-Disposition: inline; filename=\"" . $filename . ".xls\"");

                // print out document to the browser
                // need to use stripslashes for the damn ">"
                echo stripslashes (sprintf($this->header, $this->sEncoding));
                echo "\n<Worksheet ss:Name=\"" . $this->sWorksheetTitle . "\">\n<Table>\n";
                foreach ($this->lines as $line)
                        echo $line;

                echo "</Table>\n</Worksheet>\n";
                echo $this->footer;
        }

}
