<?php
class ControllerExtensionModuleAccount extends Controller {
	public function index() {
		$this->load->language('extension/module/account');

		$data['logged'] = $this->customer->isLogged();
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['forgotten'] = $this->url->link('account/forgotten', '', true);
		$data['account'] = $this->url->link('account/account', '', true);
		$data['edit'] = $this->url->link('account/edit', '', true);
		$data['password'] = $this->url->link('account/password', '', true);
		$data['address'] = $this->url->link('account/address', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist');
		$data['order'] = $this->url->link('account/order', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['reward'] = $this->url->link('account/reward', '', true);
		$data['return'] = $this->url->link('account/return', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);
		$data['recurring'] = $this->url->link('account/recurring', '', true);

                  /*
                  mp follow seller code starts
                   */
                   $data['text_followseller_customer'] = $this->language->get('text_followseller_customer');
                   if ($this->config->get('module_wk_mpfollowseller_status') && $this->config-> get('wk_mpfollowseller_customer_followseller_dashboard_status') ) {
                       $data['followseller_customer'] = $this->url->link('account/customerpartner/wk_mpfollowseller_customer', '', 'SSL');

                       $data['wk_mpfollowseller_customer_followseller_dashboard_status'] = $this->config->get('wk_mpfollowseller_customer_followseller_dashboard_status');
                   }
                  /*
                  end here
                   */
                

               if($this->config->get('module_wk_communication_status')) {
                  $data['loggedCheck'] = $this->customer->isLogged();
                  $this->load->language('account/wk_communication');
                  $data['text_communication_history'] = $this->language->get('text_communication_history');
                  $data['communication'] = $this->url->link('account/wk_communication', '', true);
                }
            

		return $this->load->view('extension/module/account', $data);
	}
}