<?php
class ControllerCommunicationWkCommunication extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('communication/wk_communication');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('communication/wk_communication');

		$this->load->model('communication/wk_communication');

		$this->getlist();
	}
	public function getlist() {
		$this->load->language('communication/wk_communication');
		$url = '';
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('communication/wk_communication', 'user_token=' . $this->session->data['user_token'], true)
		);
		$this->document->setTitle($this->language->get('heading_title'));

		if (isset($this->request->get['filter_from'])) {
			$filter_from = $this->request->get['filter_from'];
		} else {
			$filter_from = null;
		}
		if (isset($this->request->get['filter_to'])) {
			$filter_to = $this->request->get['filter_to'];
		} else {
			$filter_to = null;
		}
		if (isset($this->request->get['filter_subject'])) {
			$filter_subject = $this->request->get['filter_subject'];
		} else {
			$filter_subject = null;
		}
		if (isset($this->request->get['filter_date'])) {
			$filter_date = $this->request->get['filter_date'];
		} else {
			$filter_date = null;
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
			// $url .= '&page=' . $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->session->data['error_warning'])) {
			$data['error_warning'] = $this->session->data['error_warning'];
			unset($this->session->data['error_warning']);
			} else {
			$data['error_warning'] = '';
			}
		$filterData = array(
			'filter_from'	  => $filter_from,
			'filter_to'	  => $filter_to,
			'filter_subject'	  => $filter_subject,
			'filter_date'	  => $filter_date,
			'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'           => $this->config->get('config_limit_admin')
		);
		foreach ($filterData as  $key=>$value) {
			$data[$key] = $value;
		}

		$data['messageses'] =array();
		$flag='';
		$this->load->model('communication/wk_communication');
		$data['messages'] = array();
		$messages = $this->model_communication_wk_communication->getMessages($filterData);
		$total = $this->model_communication_wk_communication->getTotalMessages($filterData);
		foreach ($messages as $message) {
			$flag = $this->model_communication_wk_communication->adminInfo($message['message_id']);
			if($flag){
				$data['messageses'][]=$message;
			}
		}
		foreach ($data['messageses'] as $message) {
			$data['messages'][]= array(
				'message_id' => $message['message_id'],
				'from' => $message['message_from'],
				'to' => $message['message_to'],
				'subject' => $message['message_subject'],
				'date' => $message['message_date'],
				'action' => $this->url->link('communication/wk_communication/getThreads', 'user_token=' . $this->session->data['user_token'] . '&message_id=' . $message['message_id'] , true)
				);
		}
		$data['total_threads']=array();
		foreach ($data['messageses']  as $message) {
			$data['total_threads'][] = $this->model_communication_wk_communication->countThreads($message['message_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['delete'] = $this->url->link('communication/wk_communication/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$pagination = new Pagination();

		$pagination->total = $total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('communication/wk_communication', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($total- $this->config->get('config_limit_admin'))) ? $total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $total, ceil($total / $this->config->get('config_limit_admin')));

		$data['heading_title'] = $this->language->get('heading_title');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		if(version_compare(VERSION, '2.2.0.0', '>='))
			$this->response->setOutput($this->load->view('communication/wk_communication', $data));
		else
			$this->response->setOutput($this->load->view('communication/wk_communication', $data));
	}

	public function getThreads() {
		if (isset($this->request->get['message_id'])) {
			$message_id = $this->request->get['message_id'];
		} else {
			$message_id = null;
		}

		$this->load->language('communication/wk_communication');
		$this->document->setTitle($this->language->get('heading_title'));
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('communication/wk_communication', 'user_token=' . $this->session->data['user_token'], true)
		);

		$lang = array(
			'text_history'
			);
		foreach ($lang as $value) {
			$data[$value] = $this->language->get($value);
		}
		$extension = $this->config->get('wk_communication_type');
		$extensions = explode(',', $extension);
		$data['extension'] = $extensions;
		$data['max'] =$this->config->get('wk_communication_max');
		$data['size'] =$this->config->get('wk_communication_size');
		$data['type'] =explode(",", $this->config->get('wk_communication_type'));
		$data['cancel'] = $this->url->link('communication/wk_communication','user_token='.$this->session->data['user_token'],true);
		$this->load->model('communication/wk_communication');
		$data['message_info'] = $this->model_communication_wk_communication->getMessageInfo($message_id);
		$thread_info = $this->model_communication_wk_communication->getThreads($message_id);
		$data['thread_info'] = array();
		$data['attachments_info']=array();
		if(!empty($thread_info))
		foreach ($thread_info as $thread) {
			$data['thread_info'][] = $this->model_communication_wk_communication->getMessageInfo($thread['message_id']);
			$data['attachments_info'][] = $this->model_communication_wk_communication->getAttachment($thread['message_id']);
		}
		//Attachment of the parent message
		$attachment = $this->model_communication_wk_communication->getAttachment($message_id);
		$data['attachments'] = array();

		if(!empty($attachment)) {
		$data['attachments'] = $attachment;
		}
		$data['reply'] = array(
			'from' => $data['message_info']['message_from'],
			'to'  => $data['message_info']['message_to'],
			'both' =>$this->language->get("text_both")
			);
		$data['user_token'] = $this->session->data['user_token'];
		$data['heading_title'] = $this->language->get('heading_title');
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$data['action'] = $this->url->link('communication/wk_communication/sendMail', 'user_token=' . $this->session->data['user_token'] . '&message_id=' . $message_id , true);
		if(isset($this->session->data['errors']) && $this->session->data['errors']) {
			$data['errors'] = $this->session->data['errors'];
			unset($this->session->data['errors']);
		}
		if(version_compare(VERSION, '2.2.0.0', '>='))
			$this->response->setOutput($this->load->view('communication/wk_communication_form', $data));
		else
			$this->response->setOutput($this->load->view('communication/wk_communication_form', $data));
	}

	public function sendMail(){
		$secure=0;
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {

			require_once DIR_SYSTEM."library/class.phpmailer.php";
			require_once DIR_SYSTEM."library/class.pop3.php";
			require_once DIR_SYSTEM."library/class.smtp.php";
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->SMTPAuth = true;
			//$mail->SMTPDebug=2;
			$mail->Host = $this->config->get('config_mail_smtp_hostname');
			$mail->Username = $this->config->get("config_mail_smtp_username");
			$mail->Password =html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->Port =$this->config->get('config_mail_smtp_port');
			$mail->SMTPSecure = 'tls';
			$mail->AllowEmpty = false;
			$mail->setFrom($this->config->get('config_email'));
			$mail->FromName = 'Admin';
			$mail->addAddress($this->request->post['to']);
			$mail->IsHTML(true);
			$mail->Subject = $this->request->post['subject'];
			$mail->Body    = $this->request->post['message'];
			$mail->WordWrap = 50;
		    $mail->msgHTML(html_entity_decode(trim($this->request->post['message']), ENT_QUOTES, 'UTF-8'));
			$attachment = array();
			if(isset($this->request->files) && $this->request->files['file']) {
				foreach ($this->request->files['file']['name'] as $key => $value) {
					if($this->request->files['file']['tmp_name'][$key] && $this->request->files['file']['name'][$key] ){
					$mail->AddAttachment($this->request->files['file']['tmp_name'][$key],$this->request->files['file']['name'][$key]);
					}
				}
			}
			if(!$mail->send())
			{
				$this->load->language('communication/wk_communication');
				$this->session->data['error_warning']=$this->language->get('error_message');
			  $this->response->redirect($this->url->link('communication/wk_communication', 'user_token=' . $this->session->data['user_token'], true));
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
				$message = html_entity_decode(trim($this->request->post['message']), ENT_QUOTES, 'UTF-8');
				$keywords = explode(',',$this->config->get('wk_communication_keywords'));
				$searchin='  ';
				if($this->config->get('wk_communication_search')) {
					if(in_array('message', $this->config->get('wk_communication_search'))){
					$searchin .= $message;
					}
					if(in_array('subject', $this->config->get('wk_communication_search'))){
						$searchin .= $this->request->post['subject'];
					}
				}
				if(!empty($keywords)) {
					foreach ($keywords as $key) {
						if(!empty($key)){
						$key = trim($key);
						if(strpos($searchin, $key)!=false ){
							$secure=1;
							}
					}
					}
				}
				if(isset($uploaded))
				$message_id = $this->communication->updateQuery($this->request->post['subject'],$message,$this->request->post['to'],$this->config->get('config_email'),$secure,$uploaded);
				else
				$message_id = $this->communication->updateQuery($this->request->post['subject'],$message,$this->request->post['to'],$this->config->get('config_email'),$secure);
				$this->communication->updatePlaceholderDetails($this->request->post['to'],$this->config->get('config_email'),$message_id);
				$this->communication->updateReply($message_id,$this->request->post['parent_message']);
				$this->response->redirect($this->url->link('communication/wk_communication', 'user_token=' . $this->session->data['user_token'], true));
			}
		} else {
			$this->response->redirect($this->url->link('communication/wk_communication/getThreads&message_id='.$this->request->get['message_id'], 'user_token=' . $this->session->data['user_token'], true));
		}
	}
	public function download() {

		$this->load->model('communication/wk_communication');
		if (isset($this->request->get['attachment_id'])) {
			$attachment_id = $this->request->get['attachment_id'];
		} else {
			$attachment_id = 0;
		}

		$download_info = $this->model_communication_wk_communication->getDownload($attachment_id);

		if ($download_info) {
			$file = DIR_DOWNLOAD .'attachment/'. $download_info['filename'];
			$mask = basename($download_info['maskname']);

			if (!headers_sent()) {
				if (file_exists($file)) {
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize($file));

					if (ob_get_level()) {
						ob_end_clean();
					}

					readfile($file, 'rb');

					exit();
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}
		} else {
			$this->response->redirect($this->url->link('account/wk_communication', '', true));
		}
	}

		public function delete() {
		$this->load->language('communication/wk_communication');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('communication/wk_communication');
		if (isset($this->request->post['selected'])) {
			foreach ($this->request->post['selected'] as $message_id) {
				$this->model_communication_wk_communication->deleteMessage($message_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			if (isset($this->request->get['filter_from'])) {
			$url .= '&filter_from=' . urlencode(html_entity_decode($this->request->get['filter_from'], ENT_QUOTES, 'UTF-8'));
			}
			if (isset($this->request->get['filter_to'])) {
			$url .= '&filter_to=' . urlencode(html_entity_decode($this->request->get['filter_to'], ENT_QUOTES, 'UTF-8'));
			}
			if (isset($this->request->get['filter_subject'])) {
			$url .= '&filter_subject=' . urlencode(html_entity_decode($this->request->get['filter_subject'], ENT_QUOTES, 'UTF-8'));
			}
			if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . urlencode(html_entity_decode($this->request->get['filter_date'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['page'])) {
			$url .= '&page=' . urlencode(html_entity_decode($this->request->get['page'], ENT_QUOTES, 'UTF-8'));
			}

			$this->response->redirect($this->url->link('communication/wk_communication', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}
	public function upload() {

		if ($this->request->files['file']['name']) {
            if (!$this->request->files['file']['error']) {
                $name = md5(rand(100, 200));
                $ext = explode('.', $this->request->files['file']['name']);
                $filename = $name . '.' . $ext[1];
                if(is_dir(DIR_IMAGE .'attachment/')){
	                $destination =DIR_IMAGE .'attachment/' . $filename;
	                $location = $this->request->files["file"]["tmp_name"];
	                move_uploaded_file($location, $destination);
	                echo HTTP_CATALOG .'image/attachment/' . $filename;
	            }else{
	            	mkdir(DIR_IMAGE .'attachment/');
	                $destination = DIR_IMAGE .'attachment/' . $filename;
	                $location = $this->request->files["file"]["tmp_name"];
	                move_uploaded_file($location, $destination);
	                echo HTTP_CATALOG. 'image/attachment/' . $filename;
	            }
            }
            else
            {
              echo  $message = 'Ooops!  Your upload triggered the following error:  '.$this->request->files['file']['error'];
            }
        }
	}
	public function validate() {
		$this->load->language('communication/wk_communication');
		if(!isset($this->request->post['to']) || !$this->request->post['to']) {
			$this->session->data['errors'][] = $this->language->get('error_something_wrong');
		}
		if(!isset($this->request->post['subject']) || !$this->request->post['subject']) {
			$this->session->data['errors'][] = $this->language->get('error_subject_empty');
		}
		if(!isset($this->request->post['message']) || !$this->request->post['message']) {
			$this->session->data['errors'][] = $this->language->get('error_message_empty');
		}
		if(isset($this->session->data['errors'])) {
			return false;
		} else {
			return true;
		}
	}
}
