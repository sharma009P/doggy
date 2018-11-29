<?php
class ControllerExtensionModuleReload extends Controller {
	public function index() {
		$data = array();
		
		//error_log('Request: ' . print_r($this->request->post, true));
		if (isset($this->request->post)) {
			// hold a copy before iteration since we are going to overwrite this array for each controller
			$post = $this->request->post;
			
			// resolve each url and call its corresponding controller
			foreach($post as $moduleName => $module_params) {
				if (isset($module_params['module_route'])) {
					$url = $module_params['module_route'];
					$this->request->post = $module_params;
					$output = $this->load->controller($url);
					if (!$output) {
						$output = "{}";	
					}
					$data[$moduleName] = json_decode($output);
				}
			}
		}
		$result = json_encode($data);
		$this->response->setOutput($result);
		return $result;
	}
}