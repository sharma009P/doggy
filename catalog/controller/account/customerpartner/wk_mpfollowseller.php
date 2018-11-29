<?php
class ControllerAccountCustomerpartnerWkmpfollowseller extends Controller {
	/**
	 * [$error a variable to hold errors]
	 * @var array
	 */
	private $error = array();

	public function index() {
		$data = $this->load->language('account/customerpartner/wk_mpfollowseller');

		$config = array(
			'module_wk_mpfollowseller_status',
			'wk_mpfollowseller_seller_dashboard_status',
			'wk_mpfollowseller_customer_newsletter_set_status',
			'wk_mpfollowseller_seller_newsletter_status',
			'wk_mpfollowseller_customer_newsletter_status',
			'wk_mpfollowseller_show_highratedseller_status',
			'wk_mpfollowseller_customergroup_id',
			'wk_mpfollowseller_newsletter_subject',
			'wk_mpfollowseller_newsletter_message',
			'wk_mpfollowseller_followed_mail_status',
			'wk_mpfollowseller_followed_mail_subject',
			'wk_mpfollowseller_followed_mail_message',
			'wk_mpfollowseller_followed_customer_mail_status',
			'wk_mpfollowseller_followed_customer_mail_subject',
			'wk_mpfollowseller_followed_customer_mail_message',
		);

		foreach ($config as $value) {
			$data[$value] = $this->config->get($value);
		}

		if (!isset($data['wk_mpfollowseller_customergroup_id'])) {
			$data['wk_mpfollowseller_customergroup_id'] = array();
		}

		/**
		 * Check whether customer is logged or not
		 */
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/customerpartner/wk_mpfollowseller', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('extension/module/wk_mpfollowseller');

		$this->load->model('account/customerpartner');

		/**
		 * Check whether this customer is partner of marketplace
		 */
		$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();

		if(!$data['chkIsPartner']){
			$this->response->redirect($this->url->link('account/account'));
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'oc.firstname';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validatesendnewsletter()) {
			foreach ($this->request->post['selected'] as $value) {
				/**
				 * send newsletter to customer one by one
				 */
				$this->model_extension_module_wk_mpfollowseller->sendNewsletter($value);
			}

			$this->session->data['success'] = $this->language->get('text_newslettersuccess');
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->error['warning_select_customer'])) {
			$data['error_warning'] = $this->error['warning_select_customer'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account'),
			'separator' => $this->language->get('text_separator')
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_followseller'),
			'href'      => $this->url->link('account/customerpartner/wk_mpfollowseller', '', true),
			'separator' => $this->language->get('text_separator')
		);

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		/**
		 * get all customer group id
		 */
		$data['seller_customer_groupid'] = $this->model_extension_module_wk_mpfollowseller->getCustomerGroupId();

		/**
		 * [$followers get all followers of this seller]
		 * @var [array]
		 */
		$followers = $this->model_extension_module_wk_mpfollowseller->getFollowers($filter_data);

		/**
		 * [$total_followseller count total follow seller]
		 * @var [integer]
		 */
		$total_followseller = $this->model_extension_module_wk_mpfollowseller->getTotalFollowSeller();
		$data['selected'] = '';

		if (!empty($followers)) {
			foreach ($followers as $follower) {
				$data['followers'][] = array(
					'customer_id' 	=> $follower['customer_id'],
					'name' 			=> $follower['firstname'].' '.$follower['lastname'],
					'email' 		=> $follower['email'],
				);
			}
		} else {
			$data['followers'] = array();
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $total_followseller;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('account/customerpartner/wk_mpfollowseller', $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($total_followseller) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($total_followseller - $this->config->get('config_limit_admin'))) ? $total_followseller : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $total_followseller, ceil($total_followseller / $this->config->get('config_limit_admin')));

		$data['sort_name'] = $this->url->link('account/customerpartner/wk_mpfollowseller', 'sort=oc.firstname' . $url, true);
		$data['sort_email'] = $this->url->link('account/customerpartner/wk_mpfollowseller', 'sort=oc.email' . $url, true);

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['cancel'] = $this->url->link('account/account', '', true);
		$data['add_newsletter'] = $this->url->link('account/customerpartner/wk_mpfollowseller/addNewsletter', '', true);
		$data['action'] = $this->url->link('account/customerpartner/wk_mpfollowseller', '', true);
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/customerpartner/wk_mpfollowseller' , $data));
	}

	public function addNewsletter() {
		$data = $this->load->language('account/customerpartner/wk_mpfollowseller');

		$this->load->model('extension/module/wk_mpfollowseller');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validatenewsletter()) {
			$this->load->model('customerpartner/htmlfilter');

			$this->request->post['wk_mpfollowseller_newsletter_message'] = htmlentities($this->model_customerpartner_htmlfilter->HTMLFilter(html_entity_decode($this->request->post['wk_mpfollowseller_newsletter_message']),'',true));

			/**
			 * add seller newsletter
			 */
			$this->model_extension_module_wk_mpfollowseller->addnewsletter($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->error['newsletter_subject'])) {
			$data['error_warning'] = $this->error['newsletter_subject'];
		} else {
			$data['error_warning'] = '';
		}

		$config = array(
			'wk_mpfollowseller_newsletter_subject',
			'wk_mpfollowseller_newsletter_message'
		);

		foreach ($config as $value) {

			$data[$value] = $this->config->get('$value');
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account'),
			'separator' => $this->language->get('text_separator')
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_followseller'),
			'href'      => $this->url->link('account/customerpartner/wk_mpfollowseller'),
			'separator' => $this->language->get('text_separator')
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_followseller_newsletter'),
			'href'      => $this->url->link('account/customerpartner/wk_mpfollowseller/addNewsletter', '', true),
			'separator' => $this->language->get('text_separator')
		);

		/**
		 * [$newsletter get seller newsletter]
		 * @var [array]
		 */
		$newsletter = $this->model_extension_module_wk_mpfollowseller->getnewsletter();

		if (!empty($newsletter)) {
			$data['wk_mpfollowseller_newsletter_subject']= $newsletter['subject'];
			$data['wk_mpfollowseller_newsletter_message'] = $newsletter['message'];
		}

		$this->document->addScript('admin/view/javascript/summernote/summernote.js');
		$this->document->addStyle('admin/view/javascript/summernote/summernote.css');
		$this->document->addStyle('catalog/view/theme/default/stylesheet/MP/sell.css');

		$data['cancel'] = $this->url->link('account/customerpartner/wk_mpfollowseller', '' , true);
		$data['add_newsletter'] = $this->url->link('account/customerpartner/wk_mpfollowseller/addNewsletter', '' , true);
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/customerpartner/wk_mpfollowseller_newsletter' , $data));
	}

	/**
	 * [validatenewsletter check whether seller has been selected]
	 * @return [type] [error array]
	 */
	public function validatenewsletter(){
		$this->load->language('account/customerpartner/wk_mpfollowsellers');

		if (empty($this->request->post['wk_mpfollowseller_newsletter_subject'])) {
			$this->error['newsletter_subject'] = $this->language->get('warning_newsletter_subject');
		}
		return !$this->error;
	}

	/**
	 * [validatesendnewsletter check whether customers has been selected]
	 * @return [type] [error array]
	 */
	public function validatesendnewsletter() {
		$this->load->language('account/customerpartner/wk_mpfollowseller');

		if (empty($this->request->post)) {
			$this->error['warning_select_customer'] = $this->language->get('warning_select_customer');
		}
		return !$this->error;
	}
}
