<?php
class Controllerwkmpfollowsellerwkmpfollowseller extends Controller {

	private $error = array();

	public function index() {

		$this->load->language('wk_mpfollowseller/wk_mpfollowseller');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->getlist();

	}

	public function getlist(){

			$data = array();
	    $data = array_merge($data, $this->load->language('wk_mpfollowseller/wk_mpfollowseller'));

			//error checking

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

			if (isset($this->request->post['selected'])) {
				$data['selected'] = (array)$this->request->post['selected'];
			} else {
				$data['selected'] = array();
			}

      /**
			 * breadcrumbs
			 */

			$breadcrumbs=array();

			$breadcrumbs[]=array(
				'text'=>$data['text_home'],
				'href'=>$this->url->link('common/dashboard','user_token='.$this->session->data['user_token'])
				);

			$breadcrumbs[]=array(
				'text'=>$data['text_heading'],
				'href'=>$this->url->link('wk_mpfolloseller/wk_mpfolloseller','user_token='.$this->session->data['user_token']),
				);

			$data['breadcrumbs']=$breadcrumbs;


			$data['followseller'] = array();


           $this->load->model('wk_mpfollowseller/wk_mpfollowseller');

           /**
            * get all followed seller detail
            */
		   $data['followed_seller']=$this->model_wk_mpfollowseller_wk_mpfollowseller->getFollowedseller();

           /**
            * [$totalfollowedseller get total followed seller for pagination]
            * @var [integer]
            */
		   $totalfollowedseller = $this->model_wk_mpfollowseller_wk_mpfollowseller->getTotalFollowedseller();

            $url = '';

	        if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}
		    $pagination = new Pagination();
			$pagination->total = $totalfollowedseller;
			$pagination->page = $page;
			$pagination->limit = $this->config->get('config_limit_admin');
			$pagination->url = $this->url->link('warehouse/managewarehouse', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', 'SSL');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($totalfollowedseller) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($totalfollowedseller - $this->config->get('config_limit_admin'))) ? $totalfollowedseller : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $totalfollowedseller, ceil($totalfollowedseller / $this->config->get('config_limit_admin')));

		   $data['delete'] = $this->url->link('wk_mpfollowseller/wk_mpfollowseller/delete', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL');
		   $data['header'] = $this->load->controller('common/header');
           $data['footer'] = $this->load->controller('common/footer');
           $data['column_left'] = $this->load->controller('common/column_left');

		   $this->response->setOutput($this->load->view('wk_mpfollowseller/wk_mpfollowseller',$data));


	}

	public function view(){

		    $data = array();
	        $data = array_merge($data,$this->load->language('wk_mpfollowseller/wk_mpfollowseller'));

			//error checking

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

			if (isset($this->session->data['warning_customer_select'])) {
				$data['error_warning'] = $this->session->data['warning_customer_select'];

				unset($this->session->data['warning_customer_select']);
			} else {
				$data['error_warning'] = '';
			}

			if (isset($this->request->post['selected'])) {
				$data['selected'] = (array)$this->request->post['selected'];
			} else {
				$data['selected'] = array();
			}


             /**
			 * breadcrumbs
			 */

			$breadcrumbs=array();

			$breadcrumbs[]=array(
				'text'=>$data['text_home'],
				'href'=>$this->url->link('common/dashboard','user_token='.$this->session->data['user_token'])
				);

			$breadcrumbs[]=array(
				'text'=>$data['text_heading'],
				'href'=>$this->url->link('wk_mpfollowseller/wk_mpfollowseller','user_token='.$this->session->data['user_token']),
				);

			$breadcrumbs[]=array(
				'text'=>$data['text_heading'],
				'href'=>$this->url->link('wk_mpfollowseller/wk_mpfollowseller/view', 'user_token=' . $this->session->data['user_token'] .'&seller_id=' . $this->request->get['seller_id'], 'SSL'),
				);

			$data['breadcrumbs']=$breadcrumbs;


			$data['followseller'] = array();


           $this->load->model('wk_mpfollowseller/wk_mpfollowseller');

           /**
            * get all following customer details
            */
		   $data['following_customer']=$this->model_wk_mpfollowseller_wk_mpfollowseller->getFollowingcustomer($this->request->get['seller_id']);

           /**
            * [$totalfollowingcustomer get total following customer for pagination]
            * @var [integer]
            */
		   $totalfollowingcustomer = $this->model_wk_mpfollowseller_wk_mpfollowseller->getTotalFollowingcustomer($this->request->get['seller_id']);

		    $url = '';

	        if (isset($this->request->get['page'])) {
				$page = $this->request->get['page'];
			} else {
				$page = 1;
			}


		    $pagination = new Pagination();
			$pagination->total = $totalfollowingcustomer;
			$pagination->page = $page;
			$pagination->limit = $this->config->get('config_limit_admin');
			$pagination->url = $this->url->link('warehouse/managewarehouse', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', 'SSL');

			$data['pagination'] = $pagination->render();

			$data['results'] = sprintf($this->language->get('text_pagination'), ($totalfollowingcustomer) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($totalfollowingcustomer - $this->config->get('config_limit_admin'))) ? $totalfollowingcustomer : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $totalfollowingcustomer, ceil($totalfollowingcustomer / $this->config->get('config_limit_admin')));


		   $data['delete'] = $this->url->link('wk_mpfollowseller/wk_mpfollowseller/deletefollowingcustomer', 'user_token=' . $this->session->data['user_token'] .'&seller_id=' . $this->request->get['seller_id'], 'SSL');
		   $data['header'] = $this->load->controller('common/header');
           $data['footer'] = $this->load->controller('common/footer');
           $data['column_left'] = $this->load->controller('common/column_left');
		   $this->response->setOutput($this->load->view('wk_mpfollowseller/wk_mpfollowseller_view',$data));

	}

	public function delete() {

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateDelete()) {

            $this->load->model('wk_mpfollowseller/wk_mpfollowseller');
			foreach ($this->request->post['selected'] as $seller_id) {

				/**
				 * Delete followed seller
				 */
				$this->model_wk_mpfollowseller_wk_mpfollowseller->deletefollowseller($seller_id);
			}

			$this->session->data['success'] = $this->language->get('text_unfollow_seller_success');

			$this->response->redirect($this->url->link('wk_mpfollowseller/wk_mpfollowseller', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}

		$this->getlist();
	}

	public function deletefollowingcustomer(){

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateCustomerDelete()) {

            $this->load->model('wk_mpfollowseller/wk_mpfollowseller');
			foreach ($this->request->post['selected'] as $customer_id) {

				/**
				 * Delete following seller
				 */
				$this->model_wk_mpfollowseller_wk_mpfollowseller->deletefollowingcustomer($customer_id,$this->request->get['seller_id']);
			}

			$this->session->data['success'] = $this->language->get('text_unfollow_customer_success');

			$this->response->redirect($this->url->link('wk_mpfollowseller/wk_mpfollowseller/view', 'user_token=' . $this->session->data['user_token'] .'&seller_id=' . $this->request->get['seller_id'], 'SSL'));
		}

		 $this->session->data['warning_customer_select'] = $this->error['warning'];

		 $this->response->redirect($this->url->link('wk_mpfollowseller/wk_mpfollowseller/view', 'user_token=' . $this->session->data['user_token'] .'&seller_id=' . $this->request->get['seller_id'], 'SSL'));
	}

	public function validateDelete(){

		$this->load->language('wk_mpfollowseller/wk_mpfollowseller');

		if (!$this->user->hasPermission('modify', 'wk_mpfollowseller/wk_mpfollowseller')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!isset($this->request->post['selected'])) {
			$this->error['warning'] = $this->language->get('warning_select');
		}

		return !$this->error;


	}

	public function validateCustomerDelete(){

		$this->load->language('wk_mpfollowseller/wk_mpfollowseller');

		if (!$this->user->hasPermission('modify', 'wk_mpfollowseller/wk_mpfollowseller')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!isset($this->request->post['selected'])) {
			$this->error['warning'] = $this->language->get('warning_customer_select');
		}

		return !$this->error;
	}
}
