<?php
class ControllerExtensionModuleWkmpfollowseller extends Controller {

    private $error = array();
    /**
     * [install function runs at the time installation]
     * @return [type] [no]
     */
	public function install() {
		$this->load->model('extension/module/wk_mpfollowseller');
		$this->model_extension_module_wk_mpfollowseller->createFollowSellerTable();

        $this->load->model('user/user_group');
        $controllers = array(
            'extension/module/wk_mpfollowseller',
            'wk_mpfollowseller/wk_mpfollowseller',

        );

        foreach ($controllers as $key => $controller) {
            $this->model_user_user_group->addPermission($this->user->getId(),'access',$controller);
            $this->model_user_user_group->addPermission($this->user->getId(),'modify',$controller);
        }
	}


	public function index() {

        /**
         * [$language load languages from wk_followseller file]
         * @var [array]
         */
        $data = array();
        $data = array_merge($data, $this->load->language('extension/module/wk_mpfollowseller'));
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

      $this->load->model('customerpartner/htmlfilter');

      $this->request->post['wk_mpfollowseller_newsletter_message'] = htmlentities($this->model_customerpartner_htmlfilter->HTMLFilter(html_entity_decode($this->request->post['wk_mpfollowseller_newsletter_message']),'',true));
      $this->request->post['wk_mpfollowseller_followed_mail_message'] = htmlentities($this->model_customerpartner_htmlfilter->HTMLFilter(html_entity_decode($this->request->post['wk_mpfollowseller_followed_mail_message']),'',true));
      $this->request->post['wk_mpfollowseller_followed_customer_mail_message'] = htmlentities($this->model_customerpartner_htmlfilter->HTMLFilter(html_entity_decode($this->request->post['wk_mpfollowseller_followed_customer_mail_message']),'',true));

			$this->model_setting_setting->editSetting('module_wk_mpfollowseller', $this->request->post);
      $this->model_setting_setting->editSetting('wk_mpfollowseller', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=module', true));
		}

        /**
         * breadcrumbs
         */
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/wk_mpfollowseller', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

        $this->load->model('customer/customer_group');
        $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

        $config = array(
        	'module_wk_mpfollowseller_status',
        	'wk_mpfollowseller_seller_dashboard_status',
        	'wk_mpfollowseller_customer_newsletter_set_status',
        	'wk_mpfollowseller_seller_newsletter_status',
        	'wk_mpfollowseller_customer_followseller_dashboard_status',
        	'wk_mpfollowseller_customergroup_id',
        	'wk_mpfollowseller_newsletter_subject',
        	'wk_mpfollowseller_newsletter_message',
        	'wk_mpfollowseller_followed_mail_status',
        	'wk_mpfollowseller_followed_mail_subject',
        	'wk_mpfollowseller_followed_mail_message',
        	'wk_mpfollowseller_followed_customer_mail_status',
        	'wk_mpfollowseller_followed_customer_mail_subject',
        	'wk_mpfollowseller_followed_customer_mail_message',
          'wk_mpfollowseller_other_option_id',
        );

        foreach ($config as $value) {
            if(isset($this->request->post[$value])){
                $data[$value] = $this->request->post[$value];
            }else{
                $data[$value] = $this->config->get($value);
            }
        }

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // die();

        if (isset($this->error['warning'])) {
          $data['error_warning'] = $this->error['warning'];
        } else {
          $data['error_warning'] = '';
        }

        $data['other_options'][] = $data['highrated_seller_status'];
        $data['other_options'][] = $data['highrated_products_seller_status'];
        $data['other_options'][] = $data['similar_products_seller_status'];
        $data['other_options'][] = $data['High_followed_seller_status'];

    		$data['mail_help'][] = $data['customer_first_name'];
    		$data['mail_help'][] = $data['customer_last_name'];
    		$data['mail_help'][] = $data['seller_name'];
    		$data['mail_help'][] = $data['High_viewed_products'];

        $data['action'] = $this->url->link('extension/module/wk_mpfollowseller', 'user_token=' . $this->session->data['user_token'], 'SSL');
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=module', true);

    		$data['header'] = $this->load->controller('common/header');
    		$data['footer'] = $this->load->controller('common/footer');
    		$data['column_left'] = $this->load->controller('common/column_left');

        $this->response->setOutput($this->load->view('extension/module/wk_mpfollowseller', $data));
    }

    protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/wk_mpfollowseller')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
}
