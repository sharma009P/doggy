<?php

class ControllerAccountCustomerpartnerSendquery extends Controller {
	private $error = array();

	public function index() {

		$this->load->language('account/wk_communication');

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
			require_once DIR_SYSTEM."library/class.phpmailer.php";
			require_once DIR_SYSTEM."library/class.pop3.php";
			require_once DIR_SYSTEM."library/class.smtp.php";
			$mail = new PHPMailer(true);
			// 	$mail->IsSMTP();
			// 	$mail->SMTPAuth = false;
			// $mail->SMTPSecure = false;
			// // $mail->SMTPDebug=1;
			//
			// $mail->Host ='tls://'.$this->config->get('config_mail_smtp_hostname');
			// $mail->Username = $this->config->get("config_mail_smtp_username");
			// $mail->Password =html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			// $mail->Port =$this->config->get('config_mail_smtp_port');
			// $mail->SMTPSecure = 'tls';
			// $mail->AllowEmpty = false;
			// $mail->SMTPDebug = 2;
			$mail->setFrom($this->request->post['from']);
			$mail->FromName = $this->customer->getFirstName().' '.$this->customer->getLastName();
			$mail->addAddress($this->request->post['to']);

			$mail->IsHTML(true);
			$mail->Subject = $this->request->post['subjectContact'];
			$mail->Body    = $this->request->post['messageContact'];

			if($this->config->get('module_wk_communication_bcc'))
			$mail->AddBCC($this->config->get('module_wk_communication_bcc'));
			$mail->WordWrap = 50;
			if($this->config->get('config_email'))
		     $mail->addCC($this->config->get('config_email'),'Admin');

		  	$mail->msgHTML(html_entity_decode(trim($this->request->post['messageContact']), ENT_QUOTES, 'UTF-8'));
			if($this->config->get('config_email'))
			$mail->addCC($this->config->get('config_email'),'Admin');
		 	$mail->msgHTML(html_entity_decode(trim($this->request->post['messageContact']), ENT_QUOTES, 'UTF-8'));

			$attachment = array();

			if(isset($this->request->files) && $this->request->files['file']) {

				foreach ($this->request->files['file']['name'] as $key => $value) {
					if($this->request->files['file']['tmp_name'][$key] && $this->request->files['file']['name'][$key] ){
					$mail->AddAttachment($this->request->files['file']['tmp_name'][$key],$this->request->files['file']['name'][$key]);
					}
				}

			}

			try{
//echo $mail->send(); die;
			if(!$mail->send())
			{

				$this->error['error_warning'] = $this->language->get("error_message");
				$this->response->addHeader('Content-Type: application/json');
			 $this->response->setOutput(json_encode($this->error));
			} else {

				$demo = is_dir(DIR_DOWNLOAD .'attachment') ?  '' : mkdir(DIR_DOWNLOAD .'attachment');
				if(isset($this->request->files) && $this->request->files['file']) {
					foreach ($this->request->files['file']['name'] as $key => $value) {
						if($this->request->files['file']['tmp_name'][$key] && $this->request->files['file']['name'][$key] ){
							$file = $this->request->files['file']['name'][$key]. '.' . token(32);
							move_uploaded_file($this->request->files['file']['tmp_name'][$key], DIR_DOWNLOAD .'attachment/'. $file);
							$uploaded['filename'][] = $file;
							$uploaded['mask'][] = $this->request->files['file']['name'][$key];
						}
					}
				}

				$message = html_entity_decode(trim($this->request->post['messageContact']), ENT_QUOTES, 'UTF-8');
				$this->load->model('account/customer');
				$seller = $this->model_account_customer->getCustomer($this->request->post['id']);
				$seller_name = $seller['firstname'].' '.$seller['lastname'];
				$this->load->model('extension/module/wk_contact');
				if(isset($uploaded) && $uploaded){
					$this->model_extension_module_wk_contact->insertQuery($this->request->post['subjectContact'],$message,$this->request->post['to'],$this->request->post['from'],$this->request->post['id'],$uploaded);
				} else {
					$this->model_extension_module_wk_contact->insertQuery($this->request->post['subjectContact'],$message,$this->request->post['to'],$this->request->post['from'],$this->request->post['id']);
				}
				$this->error['success'] = $this->language->get('text_success');
				unset($this->error['error']);
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($this->error));
			}

			} catch(Exception $e) {

				$this->load->language('account/wk_communication');
				$this->error['error'][] = $this->language->get("error_message");
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($this->error));
			}
		} else {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($this->error));
		}
	}

	public function validate() {
		$this->load->language('account/wk_communication');
		$this->error['error'] = array();

		if(!isset($this->request->post['from']) || !$this->request->post['from']) {
			$this->error['error'][] = $this->language->get('error_something_wrong');
		}
		if(!isset($this->request->post['to']) || !$this->request->post['to']) {
			$this->error['error'][] = $this->language->get('error_something_wrong');
		}
		if(!isset($this->request->post['subjectContact']) || !$this->request->post['subjectContact']) {
			$this->error['error'][] = $this->language->get('error_subject_empty');
		}
		if(!isset($this->request->post['messageContact']) || !$this->request->post['messageContact']) {
			$this->error['error'][] = $this->language->get('error_message_empty');
		}
		if(!isset($this->request->post['id']) || !$this->request->post['id']) {
			$this->error['error'][] = $this->language->get('error_something_wrong');
		}
		return !$this->error['error'];
	}
}
