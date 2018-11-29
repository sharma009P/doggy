<?php
class ControllerAccountCustomerpartnerHeader extends Controller {
	public function index() {
		$data['title'] = $this->document->getTitle();

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = 'admin/view/image/logo.png';
		}
		$data['name'] = $this->config->get('config_name');

		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data = array_merge($data, $this->load->language('account/customerpartner/header'));

		$data['home'] = $this->url->link('common/home', '', true);

		$data['account'] = $this->url->link('account/account', '', true);

		$data['send_mail'] = $this->url->link('account/customerpartner/sendmail','',true);

		$data['mail_for'] = '&contact_admin=true';

		$data['default_view'] = $server . 'index.php?' . str_replace(array('&amp;view=separate', '&amp;view=default', '&view=separate', '&view=default'), '', $this->request->server['QUERY_STRING']) . '&view=default';

		if (preg_match('/route=account\/customerpartner/',$this->request->server['QUERY_STRING'])) {
  $data['separate_view'] = $server . 'index.php?' . str_replace(array('&amp;view=separate', '&amp;view=default', '&view=separate', '&view=default'), '', $this->request->server['QUERY_STRING']) . '&view=separate';
} else {
  $data['separate_view'] = $this->url->link('account/customerpartner/dashboard', 'view=separate', true);
}

		if (isset($this->request->get['view']) && $this->request->get['view']) {
			$this->session->data['marketplace_separate_view'] = $this->request->get['view'];
		}

		if (!$this->customer->isLogged()) {
			$data['logged'] = '';
		} else {
			$data['logged'] = true;

			$data['logout'] = $this->url->link('account/logout', '', true);

			if ($this->config->get('module_marketplace_status') && is_array($this->config->get('marketplace_allowed_account_menu')) && $this->config->get('marketplace_allowed_account_menu')) {
				if (in_array('notification', $this->config->get('marketplace_allowed_account_menu'))) {
					$data['notification'] = $this->load->controller('account/customerpartner/notification/notifications');
				}

				if (in_array('asktoadmin', $this->config->get('marketplace_allowed_account_menu'))) {
					$data['asktoadmin'] = 1;
				}

			}
		}

		return $this->load->view('account/customerpartner/header', $data);
	}
}
