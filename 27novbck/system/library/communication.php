<?php
class Communication extends Mail{
	public $config;
	static $object;
	public function __construct($registry=null) {
		if(!is_null($registry)) {
			$this->config= $registry->get('config');
			$this->db = $registry->get('db');
			$this->request = $registry->get('request');
			$this->session = $registry->get('session');
			Communication::$object = $this;
		}
	}
	public function saveData($data) {
		$this->config = Communication::$object->config;
		$this->db 	= Communication::$object->db;
		if($this->config->get('module_wk_communication_status')) {
			$subject = $data->subject;
			$to = $data->to;
			$message='';
			if (!$data->html) {
			$message .= $data->text . PHP_EOL;
			} else {
				$message .= $data->html . PHP_EOL;
			}
			$from = $data->from;
			$secure=0;
			$keywords =array();
			$keywords = explode(',',$this->config->get('module_wk_communication_keywords'));
			$searchin='';
			if($this->config->get('module_wk_communication_search')) {
				if(in_array('message', $this->config->get('module_wk_communication_search'))){
					$searchin .= $message;
				}
				if(in_array('subject', $this->config->get('module_wk_communication_search'))){
					$searchin .= $subject;
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
			$message_id=$this->updateQuery($subject,$message,$to,$from,$secure);
			$to = $data->to;
			$from = $data->from;
		    $this->updatePlaceholderDetails($to,$from,$message_id);
		}
	}

	public function updatePlaceholderDetails($to,$from,$message_id,$secure='') {
		if($to==$this->config->get('config_email') && $from == $this->config->get('config_email')){
		   $this->db->query("INSERT INTO ".DB_PREFIX."wk_communication_placeholder VALUES('','-1','Admin',1,'Inbox','".$message_id."',1)");
		   $this->db->query("INSERT INTO " .DB_PREFIX ."wk_communication_placeholder VALUES('','-1','Admin',2,'Sent','".$message_id."',1)");
		}
		//To check seller->admin or buyer->admin
		else if($to==$this->config->get('config_email')) {
			$this->db->query("INSERT INTO ".DB_PREFIX."wk_communication_placeholder VALUES('','-1','Admin',1,'Inbox','".$message_id."',1)");
			$from_detail = $this->db->query("SELECT firstname,lastname,customer_id FROM ".DB_PREFIX."customer WHERE email='".$from."'")->row;
			if(isset($from_detail['firstname'])) {
				$from_name = $from_detail['firstname'].' '.$from_detail['lastname'];
				$this->db->query("INSERT INTO " .DB_PREFIX ."wk_communication_placeholder VALUES('','".$from_detail['customer_id']."','".$from_name."',2,'Sent','".$message_id."',1)");
			}
		}


		//To check admin->seller or admin->buyer
		else if($from==$this->config->get('config_email')) {
			$this->db->query("INSERT INTO ".DB_PREFIX."wk_communication_placeholder VALUES('','-1','Admin',2,'Sent','".$message_id."',1)");
			$to_detail = $this->db->query("SELECT firstname,lastname,customer_id FROM ".DB_PREFIX."customer WHERE email='".$to."'")->row;
			if(isset($to_detail['firstname'])) {
				$to_name = $to_detail['firstname'].' '.$to_detail['lastname'];
				$this->db->query("INSERT INTO " .DB_PREFIX ."wk_communication_placeholder VALUES('','".$to_detail['customer_id']."','".$to_name."',1,'Inbox','".$message_id."',1)");
			}
		}




		//[Admin is not in flow of Communication]
		else {
				$this->db->query("INSERT INTO ".DB_PREFIX."wk_communication_placeholder VALUES('','-1','Admin',1,'Inbox','".$message_id."',1)");

				$from_detail = $this->db->query("SELECT firstname,lastname,customer_id FROM ".DB_PREFIX."customer WHERE email='".$from."'")->row;
				if(isset($from_detail['firstname'])) {
					$from_name = $from_detail['firstname'].' '.$from_detail['lastname'];
					$this->db->query("INSERT INTO " .DB_PREFIX ."wk_communication_placeholder VALUES('','".$from_detail['customer_id']."','".$from_name."',2,'Sent','".$message_id."',1)");
				}
				$to_detail = $this->db->query("SELECT firstname,lastname,customer_id FROM ".DB_PREFIX."customer WHERE email='".$to."'")->row;
				if(isset($to_detail['firstname'])) {
					$to_name = $to_detail['firstname'].' '.$to_detail['lastname'];
					$this->db->query("INSERT INTO " .DB_PREFIX ."wk_communication_placeholder VALUES('','".$to_detail['customer_id']."','".$to_name."',1,'Inbox','".$message_id."',1)");
				}
			}
	}

	public function updateQuery($subject,$message,$to,$from,$secure,$uploaded=array()){
		$this->db->query("INSERT INTO " . DB_PREFIX . "wk_communication_message VALUES('','".$subject."','".$message."',NOW(),'".$to."','".$from."','".$secure."' )");
		$message_id = $this->db->getLastId();
		if(!empty($uploaded))
		foreach ($uploaded['filename'] as $key => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "wk_communication_attachment SET message_id = '".$message_id."', filename = '" . $this->db->escape($uploaded['filename'][$key]) . "', maskname = '" . $this->db->escape($uploaded['mask'][$key]) . "', date_added = NOW()");
		}
		return $message_id;
	}

	public function updateReply($message_id,$parent_message_id){
		$this->db->query("INSERT INTO ".DB_PREFIX."wk_communication_thread VALUES('','".$message_id."','".$parent_message_id."', NOW())");
	}
}
