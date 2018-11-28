<?php
class ModelExtensionModuleWkcontact extends Model {
	/**
	 * [insertQuery the messages into the tables and update the placeholder]
	 * @param  [String] $subject    [Subject of the message]
	 * @param  [String] $body       [Body of the message]
	 * @param  [String] $to         [Reciver name of the message]
	 * @param  [String] $from       [Sender name of the message]
	 * @param  [Int] $seller_id  [Seller id of reciver of the message]
	 */
	public function insertQuery($subject,$body,$to,$from,$seller_id,$uploaded = array()) {
		$secure=0;
		$message = html_entity_decode(trim($body), ENT_QUOTES, 'UTF-8');
				$keywords = explode(',',$this->config->get('wk_communication_keywords'));
				$searchin = ' ';
				if($this->config->get('wk_communication_search') ) {
						if(in_array('message', $this->config->get('wk_communication_search'))){
						$searchin .= $message;
						}
						if(in_array('subject', $this->config->get('wk_communication_search'))){
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

		$this->db->query("INSERT INTO " .DB_PREFIX. "wk_communication_message VALUES('','".$subject."','".$body."',NOW(),'".$to."','".$from."','".$secure."' )");
		$message_id = $this->db->getLastId();
		$seller_name = $this->db->query("SELECT firstname FROM ".DB_PREFIX."customer WHERE customer_id ='".$seller_id."'")->row;
		$this->db->query("INSERT INTO " .DB_PREFIX."wk_communication_placeholder VALUES('','".$this->customer->getId()."','".$this->customer->getFirstName()."',2,'Sent','".$message_id."',1)");
		$this->db->query("INSERT INTO " .DB_PREFIX."wk_communication_placeholder VALUES('','".$seller_id."','".$seller_name['firstname']."',1,'Inbox','".$message_id."',1)");
		$this->db->query("INSERT INTO " .DB_PREFIX."wk_communication_placeholder VALUES('','-1','Admin',1,'Inbox','".$message_id."',1)");
		if(!empty($uploaded))
		foreach ($uploaded['filename'] as $key => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "wk_communication_attachment SET message_id = '".$message_id."', filename = '" . $this->db->escape($uploaded['filename'][$key]) . "', maskname = '" . $this->db->escape($uploaded['mask'][$key]) . "', date_added = NOW()");
		}
	}



	public function getQuery($id,$placeholder_id) {
		$result = $this->db->query("SELECT m.message_id,m.message_subject,m.message_body,m.message_date,m.message_to,m.message_from,p.user_name,p.placeholder_name,p.placeholder_id FROM " .DB_PREFIX ."wk_communication_message m, ".DB_PREFIX."wk_communication_placeholder p  WHERE  m.message_id=p.message_id AND p.user_id='".$id."' AND p.placeholder_id='".$placeholder_id."'AND m.secure!=1")->rows;
		if(!empty($result)) {
			foreach ($result as $key => $value) {
				if($placeholder_id == 1)
					$res = $this->db->query("SELECT firstname,lastname FROM ".DB_PREFIX."customer WHERE email = '".$result[$key]['message_from']."'")->row;
				if($placeholder_id == 2){
					$res = $this->db->query("SELECT firstname,lastname FROM ".DB_PREFIX."customer WHERE email = '".$result[$key]['message_to']."'")->row;
					if(empty($res))
						$res['firstname'] = $res['lastname'] = "Admin";
				}
				if(isset($res) && !empty($res))
					$result[$key]['user_name'] = $res['firstname'].' '.$res['lastname'];
			}
		}
				return $result;
	}

	public function getQueryinfo($message_id) {
		$result = $this->db->query("SELECT * FROM ".DB_PREFIX."wk_communication_message WHERE message_id='".$message_id."' AND secure!=1");
		return $result->row;
	}

	public function getAttachment($message_id) {
		$results = $this->db->query("SELECT attachment_id,maskname FROM ".DB_PREFIX."wk_communication_attachment WHERE message_id = '".$message_id."'")->rows;
		return $results;
	}

	public function getDownload($attachment_id) {
		$results = $this->db->query("SELECT * FROM ".DB_PREFIX."wk_communication_attachment WHERE attachment_id = '".$attachment_id."'")->row;
		return $results;
	}

	public function getThreadMessages($message_id) {
		$results = $this->db->query("SELECT * FROM ".DB_PREFIX."wk_communication_thread wct LEFT JOIN ".DB_PREFIX."wk_communication_message wm ON(wct.message_id=wm.message_id) WHERE parent_message_id='".$message_id."' AND wm.secure!=1")->rows;
		return $results;
	}
	public function deleteQuery($message_id,$customer_id) {
		$result = $this->db->query("SELECT * FROM ".DB_PREFIX."wk_communication_placeholder WHERE message_id='".$message_id."' AND user_id='".$customer_id."'")->row;
		if($result['placeholder_id']==0){
		$this->db->query("UPDATE ".DB_PREFIX."wk_communication_placeholder set status=0,placeholder_name='delete From trash',placeholder_id=-1 WHERE user_id='".$customer_id."' AND message_id='".$message_id."'");
		} else {
		$this->db->query("UPDATE ".DB_PREFIX."wk_communication_placeholder set status=0,placeholder_name='delete',placeholder_id=0 WHERE user_id='".$customer_id."' AND message_id='".$message_id."'");
		}
	}

	public function getTotal($customer_id) {
		$result['inbox'] = count($this->getQuery($customer_id,1));
		$result['sent'] = count($this->getQuery($customer_id,2));
		$result['trash'] = count($this->getQuery($customer_id,0));
			return $result;

	}
	public function countThreads($message_id){
		$count =  $this->db->query("SELECT COUNT(*) AS total FROM ".DB_PREFIX."wk_communication_thread wkt LEFT JOIN ".DB_PREFIX."wk_communication_message wm ON (wkt.message_id=wm.message_id) WHERE wkt.parent_message_id='".$message_id."' AND wm.secure!=1");
		return $count->row['total'];
	}
}
