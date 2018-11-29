<?php
class ControllerAccountCustomerpartnerWkmpfollowsellercustomer extends Controller {

	private $error = array();

	public function index(){
		$this->load->model('extension/module/wk_mpfollowseller');

		$data = $this->load->language('account/customerpartner/wk_mpfollowseller_customer');

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'cfd.name';
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

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateseller()) {
			$this->load->language('account/customerpartner/wk_mpfollowseller_customer');

			foreach ($this->request->post['selected'] as $value) {
			/**
			 * unfollow seller one by one
			 */
			$this->model_extension_module_wk_mpfollowseller->unFollowSeller($value);
			}
			$this->session->data['success'] = $this->language->get('success');
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
			'text'      => $this->language->get('text_followseller_customer'),
			'href'      => $this->url->link('extension/module/wk_mpfollowseller_customer', '', true),
			'separator' => $this->language->get('text_separator')
		);

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		/**
		 * [$followed_sellers fetch customer following sellers details]
		 * @var [array]
		 */
			$followed_sellers = $this->model_extension_module_wk_mpfollowseller->getFollowedSeller($filter_data);

		/**
		 * [$total_followedseller count total following seller]
		 * @var [integer]
		 */
		$total_followedseller = $this->model_extension_module_wk_mpfollowseller->getTotalFollowedSeller();

		if (!empty($followed_sellers)) {
			foreach ($followed_sellers as $followed_seller) {
				$data['followed_sellers'][] = array(
					'followseller_id' => $followed_seller['followseller_id'],
					'seller_id' => $followed_seller['customer_id'],
					'name' => $followed_seller['firstname'].' '.$followed_seller['lastname'],
					'email' => $followed_seller['email'],
				);
			}
		} else {
			$data['followed_sellers'] = '';
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

		if (isset($this->error['warning_select_seller'])) {
			$data['error_warning'] = $this->error['warning_select_seller'];
		} else {
			$data['error_warning']='';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$data['sort_name'] = $this->url->link('account/customerpartner/wk_mpfollowseller_customer', '&sort=oc.firstname' . $url, true);
		$data['sort_email'] = $this->url->link('account/customerpartner/wk_mpfollowseller_customer', '&sort=oc.email' . $url, true);

		$data['sort'] = $sort;
		$data['order'] = $order;

		$pagination = new Pagination();
		$pagination->total = $total_followedseller;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('account/customerpartner/wk_mpfollowseller_customer', $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($total_followedseller) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($total_followedseller - $this->config->get('config_limit_admin'))) ? $total_followedseller : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $total_followedseller, ceil($total_followedseller / $this->config->get('config_limit_admin')));


		$data['cancel'] = $this->url->link('account/account', '', true);
		$data['action'] = $this->url->link('account/customerpartner/wk_mpfollowseller_customer', '', true);
		$data['unfollow_seller'] = $this->url->link('account/customerpartner/wk_mpfollowseller_customer', '',true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/customerpartner/wk_mpfollowseller_customer', $data));
	}

    /**
     * [validateseller check whether sellel has been selected]
     * @return [type] [error array]
     */
	public function validateseller(){
		$this->load->language('account/customerpartner/wk_mpfollowseller_customer');

		if (empty($this->request->post)) {
			$this->error['warning_select_seller'] = $this->language->get('warning_select_seller');
		}

		return !$this->error;
	}
}
