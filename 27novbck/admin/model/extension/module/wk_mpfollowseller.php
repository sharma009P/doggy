<?php
class ModelExtensionModuleWkmpfollowseller extends Model {

	public function createFollowSellerTable(){

		//Table structure for table `wk_followseller`
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX ."wk_followseller` (
		  `followseller_id` int(11) NOT NULL AUTO_INCREMENT,
		  `customer_id` int(11),
		  `seller_id` int(11),  
		  PRIMARY KEY (`followseller_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1") ;

		//Table structure for table `wk_followseller_newsletter`
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX ."wk_followseller_newsletter` (
		  `newsletter_id` int(11) NOT NULL AUTO_INCREMENT,
		  `seller_id` int(11),
		  `subject` TEXT, 
		  `message` TEXT, 
		  PRIMARY KEY (`newsletter_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1") ;

	}
}