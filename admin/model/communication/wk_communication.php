<?php
class ModelCommunicationWkCommunication extends Model {
	public function getMessages($data=array()){
		$sql = "SELECT  wm.message_id,wm.message_subject,wm.message_body,wm.message_to,wm.message_from,wm.message_date FROM ".DB_PREFIX."wk_communication_message wm LEFT JOIN ".DB_PREFIX."wk_communication_thread wt ON wm.message_id = wt.message_id  WHERE wt.message_id IS NULL AND wm.secure!=1";
		if (isset($data['filter_from']) && !is_null($data['filter_from'])) {
				$sql .= " AND wm.message_from = '" .$data['filter_from'] . "'";
			}
		if (isset($data['filter_to']) && !is_null($data['filter_to'])) {
				$sql .= " AND wm.message_to = '" .$data['filter_to'] . "'";
			}
		if (isset($data['filter_subject']) && !is_null($data['filter_subject'])) {
				$sql .= " AND wm.message_subject = '" .$data['filter_subject'] . "'";
			}
		if (isset($data['filter_date']) && !is_null($data['filter_date'])) {
				$sql .= " AND wm.message_date >= '" .$data['filter_date'].' 00:00:00' . "'";
				$sql .= " AND wm.message_date <= '" .$data['filter_date'].' 23:59:59' . "'";
			}
			if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$results = $this->db->query($sql)->rows;
		return $results;
	}

	public function adminInfo($message_id){
		$result = $this->db->query("SELECT * FROM ".DB_PREFIX."wk_communication_placeholder WHERE message_id='".$message_id."' AND placeholder_id = -2")->row;
		if(empty($result))
			return true;
		else
			return false;
	}
	public function getMessageInfo($message_id) {
		$result =  $this->db->query("SELECT * FROM ".DB_PREFIX."wk_communication_message WHERE message_id = '".$message_id."' AND secure!=1")->row;
		return $result;
	}
	public function getThreads($message_id) {
	$results =  $this->db->query("SELECT wkt.message_id FROM ".DB_PREFIX."wk_communication_thread wkt LEFT JOIN ".DB_PREFIX."wk_communication_message wm ON (wkt.message_id=wm.message_id) WHERE parent_message_id = '".$message_id."'AND wm.secure!=1")->rows;
		return $results;	
	}
	public function getAttachment($message_id) {
		$results = $this->db->query("SELECT attachment_id,maskname FROM ".DB_PREFIX."wk_communication_attachment WHERE message_id = '".$message_id."'")->rows;
		return $results;
	}
	public function getDownload($attachment_id) {
		$results = $this->db->query("SELECT * FROM ".DB_PREFIX."wk_communication_attachment WHERE attachment_id = '".$attachment_id."'")->row;
		return $results;
	}
	public function countThreads($message_id){
		$count =  $this->db->query("SELECT COUNT(*) AS total FROM ".DB_PREFIX."wk_communication_thread wkt LEFT JOIN ".DB_PREFIX."wk_communication_message wm ON (wkt.message_id=wm.message_id) WHERE wkt.parent_message_id='".$message_id."' AND wm.secure!=1");
		return $count->row['total'];
	}
	public function deleteMessage($message_id){
		$this->db->query("UPDATE ".DB_PREFIX."wk_communication_placeholder set status=0,placeholder_name='delete For Admin',placeholder_id=-2 WHERE user_id=-1 AND message_id='".$message_id."'");	
	}

	public function getTotalMessages($data = array()) {
		//$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
		$sql = "SELECT COUNT(DISTINCT wm.message_id) AS total  FROM ".DB_PREFIX."wk_communication_message wm LEFT JOIN ".DB_PREFIX."wk_communication_thread wt ON wm.message_id = wt.message_id  WHERE wt.message_id IS NULL AND wm.secure!=1";
		if (isset($data['filter_from']) && !is_null($data['filter_from'])) {
				$sql .= " AND wm.message_from = '" .$data['filter_from'] . "'";
			}
		if (isset($data['filter_to']) && !is_null($data['filter_to'])) {
				$sql .= " AND wm.message_to = '" .$data['filter_to'] . "'";
			}
		if (isset($data['filter_subject']) && !is_null($data['filter_subject'])) {
				$sql .= " AND wm.message_subject = '" .$data['filter_subject'] . "'";
			}
		if (isset($data['filter_date']) && !is_null($data['filter_date'])) {
				$sql .= " AND wm.message_date = '" .$data['filter_date'] . "'";
			}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
}